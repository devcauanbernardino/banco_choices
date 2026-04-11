<?php

declare(strict_types=1);

/**
 * Infere ISO2 do país a partir do código postal (Nominatim; atalho para BR 8 dígitos).
 */
header('Content-Type: application/json; charset=utf-8');

$postal = trim((string) ($_GET['postal'] ?? ''));
if (strlen($postal) < 3 || strlen($postal) > 24) {
    echo json_encode(['ok' => false, 'error' => 'invalid'], JSON_UNESCAPED_UNICODE);
    exit;
}

function http_get_infer(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 14,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_USERAGENT => 'BancoChoices/1.0 (postal-infer; +https://www.openstreetmap.org/copyright)',
            ]);
            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (!is_string($raw)) {
                $raw = '';
            }

            return ['status' => $status, 'raw' => $raw, 'json' => $raw !== '' ? json_decode($raw, true) : null];
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 14,
            'ignore_errors' => true,
            'header' => "User-Agent: BancoChoices/1.0 (postal-infer)\r\nAccept: application/json\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (isset($http_response_header) && is_array($http_response_header) && !empty($http_response_header[0]) && preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }
    if ($raw === false) {
        return ['status' => $status, 'raw' => '', 'json' => null];
    }
    if ($status === 0) {
        $status = 200;
    }

    return ['status' => $status, 'raw' => $raw, 'json' => $raw !== '' ? json_decode($raw, true) : null];
}

function norm_pc(string $s): string
{
    return strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $s));
}

$digitsOnly = (string) preg_replace('/\D/', '', $postal);
if (strlen($digitsOnly) === 8 && ctype_digit($digitsOnly)) {
    echo json_encode([
        'ok' => true,
        'country_code' => 'BR',
        'country_name' => 'Brasil',
        'confidence' => 'high',
        'source' => 'br_digits',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$cacheDir = dirname(__DIR__, 2) . '/var/cache/postal-infer';
$cacheKey = hash('sha256', strtolower($postal));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    $cached = json_decode((string) file_get_contents($cacheFile), true);
    if (is_array($cached) && !empty($cached['country_code']) && strlen((string) $cached['country_code']) === 2) {
        echo json_encode($cached, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$postalForNom = trim(preg_replace('/\s+/u', ' ', $postal) ?? '');
$query = http_build_query([
    'postalcode' => $postalForNom,
    'format' => 'json',
    'limit' => '12',
    'addressdetails' => '1',
], '', '&', PHP_QUERY_RFC3986);

$url = 'https://nominatim.openstreetmap.org/search?' . $query;
$res = http_get_infer($url);
if (($res['status'] ?? 0) !== 200 || !is_array($res['json']) || $res['json'] === []) {
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$want = norm_pc($postalForNom);
$byCc = [];

foreach ($res['json'] as $item) {
    if (!is_array($item)) {
        continue;
    }
    $addr = isset($item['address']) && is_array($item['address']) ? $item['address'] : [];
    $ccRaw = (string) ($addr['country_code'] ?? '');
    $cc = strtoupper((string) preg_replace('/[^A-Z]/', '', $ccRaw));
    if (strlen($cc) !== 2) {
        continue;
    }
    $pc = norm_pc((string) ($addr['postcode'] ?? ''));
    $imp = (float) ($item['importance'] ?? 0);
    $match = $want !== '' && $pc !== '' && ($pc === $want || strpos($pc, $want) === 0 || strpos($want, $pc) === 0);
    $pts = $match ? (12.0 + $imp) : (0.3 + $imp * 0.4);
    if (!isset($byCc[$cc])) {
        $byCc[$cc] = ['pts' => 0.0, 'matches' => 0, 'country' => trim((string) ($addr['country'] ?? ''))];
    }
    $byCc[$cc]['pts'] += $pts;
    if ($match) {
        $byCc[$cc]['matches']++;
    }
}

if ($byCc === []) {
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ranked = [];
foreach ($byCc as $cc => $row) {
    $ranked[] = ['cc' => $cc, 'pts' => $row['pts'], 'matches' => $row['matches'], 'country' => $row['country']];
}
usort($ranked, static function (array $a, array $b): int {
    if ($a['pts'] === $b['pts']) {
        return $b['matches'] <=> $a['matches'];
    }

    return $a['pts'] < $b['pts'] ? 1 : -1;
});

$top = $ranked[0];
$second = $ranked[1] ?? null;
$winnerCc = $top['cc'];

if ($second !== null && $top['matches'] >= 1 && $second['matches'] >= 1 && $top['pts'] < $second['pts'] * 1.18) {
    echo json_encode(['ok' => false, 'error' => 'ambiguous'], JSON_UNESCAPED_UNICODE);
    exit;
}

$out = [
    'ok' => true,
    'country_code' => $winnerCc,
    'country_name' => $top['country'] !== '' ? $top['country'] : $winnerCc,
    'confidence' => $top['matches'] >= 1 ? 'high' : 'medium',
    'source' => 'nominatim',
];

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}
@file_put_contents($cacheFile, json_encode($out, JSON_UNESCAPED_UNICODE));

echo json_encode($out, JSON_UNESCAPED_UNICODE);
