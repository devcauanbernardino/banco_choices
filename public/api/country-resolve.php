<?php

declare(strict_types=1);

/**
 * Resolve nome de país (ou código ISO2) para alpha-2, para lookup postal global.
 * Usa cache em disco + Nominatim (featuretype=country).
 */
header('Content-Type: application/json; charset=utf-8');

$q = trim((string) ($_GET['q'] ?? ''));
if ($q === '') {
    echo json_encode(['ok' => false, 'error' => 'empty'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($q) > 80) {
    echo json_encode(['ok' => false, 'error' => 'too_long'], JSON_UNESCAPED_UNICODE);
    exit;
}

function http_get_json(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_USERAGENT => 'BancoChoices/1.0 (country-resolve)',
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
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => "User-Agent: BancoChoices/1.0 (country-resolve)\r\nAccept: application/json\r\n",
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

if (preg_match('/^[A-Za-z]{2}$/', $q)) {
    echo json_encode(['ok' => true, 'iso' => strtoupper($q)], JSON_UNESCAPED_UNICODE);
    exit;
}

$cacheDir = dirname(__DIR__, 2) . '/var/cache/country-resolve';
$cacheKey = hash('sha256', strtolower($q));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    $cached = json_decode((string) file_get_contents($cacheFile), true);
    if (is_array($cached) && !empty($cached['iso']) && strlen((string) $cached['iso']) === 2) {
        echo json_encode(['ok' => true, 'iso' => strtoupper((string) $cached['iso']), 'source' => 'cache'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$query = http_build_query([
    'q' => $q,
    'featuretype' => 'country',
    'format' => 'json',
    'limit' => 2,
    'addressdetails' => 1,
], '', '&', PHP_QUERY_RFC3986);

$url = 'https://nominatim.openstreetmap.org/search?' . $query;
$res = http_get_json($url);
if (($res['status'] ?? 0) !== 200 || !is_array($res['json']) || $res['json'] === []) {
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$item = $res['json'][0];
if (!is_array($item)) {
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$addr = isset($item['address']) && is_array($item['address']) ? $item['address'] : [];
$cc = strtoupper((string) preg_replace('/[^A-Za-z]/', '', (string) ($addr['country_code'] ?? '')));
if (strlen($cc) !== 2) {
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}
@file_put_contents($cacheFile, json_encode(['iso' => $cc], JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'iso' => $cc, 'source' => 'nominatim'], JSON_UNESCAPED_UNICODE);
