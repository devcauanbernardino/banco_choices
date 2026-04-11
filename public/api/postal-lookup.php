<?php

declare(strict_types=1);

/**
 * Consulta global de código postal: Brasil (ViaCEP), demais países (Zippopotam + Nominatim).
 */
header('Content-Type: application/json; charset=utf-8');

$country = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($_GET['country'] ?? '')));
$postalRaw = trim((string) ($_GET['postal'] ?? ''));
$hint = trim((string) ($_GET['hint'] ?? ''));
if (strlen($hint) > 96) {
    $hint = '';
}

if (strlen($country) !== 2 || $postalRaw === '') {
    echo json_encode(['ok' => false, 'error' => 'invalid_input'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($postalRaw) > 32) {
    echo json_encode(['ok' => false, 'error' => 'postal_too_long'], JSON_UNESCAPED_UNICODE);
    exit;
}

function http_get(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_USERAGENT => 'BancoChoices/1.0 (postal-lookup; +https://www.openstreetmap.org/copyright)',
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
            'header' => "User-Agent: BancoChoices/1.0 (postal-lookup)\r\nAccept: application/json\r\n",
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

function respond_ok(array $payload): void
{
    $payload['ok'] = true;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function respond_fail(string $code): void
{
    echo json_encode(['ok' => false, 'error' => $code], JSON_UNESCAPED_UNICODE);
    exit;
}

function build_label(?string $place, ?string $region, ?string $countryName): string
{
    $parts = array_filter([trim((string) $place), trim((string) $region), trim((string) $countryName)]);
    $label = implode(', ', $parts);
    if (strlen($label) > 140) {
        $label = substr($label, 0, 137) . '…';
    }

    return $label;
}

/** @return list<string> */
function postal_variants(string $iso, string $raw): array
{
    $raw = trim($raw);
    $out = [$raw];
    $compact = preg_replace('/\s+/u', '', $raw) ?? '';
    if ($compact !== '' && $compact !== $raw) {
        $out[] = $compact;
    }
    $upper = strtoupper($compact);
    if ($upper !== '' && $upper !== $compact) {
        $out[] = $upper;
    } elseif ($upper !== '' && $upper !== $raw) {
        $out[] = $upper;
    }

    if ($iso === 'US') {
        $d = preg_replace('/\D/', '', $raw) ?? '';
        if (strlen($d) === 9) {
            $out[] = substr($d, 0, 5) . '-' . substr($d, 5);
            $out[] = substr($d, 0, 5);
        } elseif (strlen($d) === 5) {
            $out[] = $d;
        }
    }

    if ($iso === 'GB') {
        $alnum = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $raw) ?? '');
        if (strlen($alnum) >= 5 && $alnum !== str_replace(' ', '', $raw)) {
            $out[] = $alnum;
        }
        if (strlen($alnum) >= 5) {
            $in = strlen($alnum) > 4 ? substr($alnum, 0, -3) . ' ' . substr($alnum, -3) : $alnum;
            if ($in !== $raw) {
                $out[] = $in;
            }
        }
    }

    $unique = [];
    foreach ($out as $v) {
        $v = trim((string) $v);
        if ($v !== '') {
            $unique[$v] = true;
        }
    }

    return array_keys($unique);
}

/**
 * @param list<mixed> $items
 * @return array<string, mixed>|null
 */
function nominatim_pick_item(array $items, string $postalForNom): ?array
{
    if ($items === []) {
        return null;
    }
    $norm = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $postalForNom));
    $fallback = null;
    foreach ($items as $it) {
        if (!is_array($it)) {
            continue;
        }
        if ($fallback === null) {
            $fallback = $it;
        }
        $addr = isset($it['address']) && is_array($it['address']) ? $it['address'] : [];
        $pc = (string) ($addr['postcode'] ?? '');
        if ($pc === '') {
            continue;
        }
        $pcn = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $pc));
        if ($norm !== '' && $pcn !== '' && ($norm === $pcn || strpos($pcn, $norm) === 0 || strpos($norm, $pcn) === 0)) {
            return $it;
        }
    }

    return is_array($fallback) ? $fallback : null;
}

/**
 * @return array<string, mixed>|null
 */
function nominatim_lookup(string $countryIso, string $ccLower, string $postalForNom, string $hint): ?array
{
    $trimHint = trim($hint);
    $tries = [
        ['postalcode' => $postalForNom, 'countrycodes' => $ccLower],
        ['q' => $postalForNom, 'countrycodes' => $ccLower],
    ];
    if ($trimHint !== '') {
        $tries[] = ['q' => $postalForNom . ', ' . $trimHint, 'countrycodes' => $ccLower];
        $tries[] = ['q' => $trimHint . ' ' . $postalForNom, 'countrycodes' => $ccLower];
    }

    $firstTry = true;
    foreach ($tries as $base) {
        if (!$firstTry) {
            usleep(1100000);
        }
        $firstTry = false;
        $params = $base;
        $params['format'] = 'json';
        $params['limit'] = '5';
        $params['addressdetails'] = '1';
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $nom = http_get($url);
        if (($nom['status'] ?? 0) !== 200 || !is_array($nom['json']) || $nom['json'] === []) {
            continue;
        }
        $item = nominatim_pick_item($nom['json'], $postalForNom);
        if (!is_array($item)) {
            continue;
        }
        $addr = isset($item['address']) && is_array($item['address']) ? $item['address'] : [];
        $place = (string) ($addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['suburb'] ?? $addr['municipality'] ?? '');
        $state = (string) ($addr['state'] ?? $addr['region'] ?? '');
        $pc = (string) ($addr['postcode'] ?? $item['name'] ?? $postalForNom);
        $countryName = (string) ($addr['country'] ?? '');
        $display = (string) ($item['display_name'] ?? '');

        if ($place === '' && $display !== '') {
            $parts = explode(',', $display, 2);
            $place = trim($parts[0] ?? '');
        }

        $label = build_label($place, $state, $countryName);
        if ($label === '' && $display !== '') {
            $label = strlen($display) > 140 ? substr($display, 0, 137) . '…' : $display;
        }
        if ($label === '') {
            continue;
        }

        return [
            'source' => 'nominatim',
            'country' => $countryIso,
            'postal_formatted' => $pc !== '' ? $pc : $postalForNom,
            'place_name' => $place,
            'region' => $state,
            'region_code' => (string) ($addr['ISO3166-2-lvl4'] ?? ''),
            'label' => $label,
        ];
    }

    return null;
}

// --- Brasil: ViaCEP ---
if ($country === 'BR') {
    $cep = preg_replace('/\D/', '', $postalRaw) ?? '';
    if (strlen($cep) !== 8) {
        respond_fail('invalid_postal');
    }
    $url = 'https://viacep.com.br/ws/' . $cep . '/json/';
    $res = http_get($url);
    $data = is_array($res['json']) ? $res['json'] : [];
    if (!empty($data['erro']) || ($res['status'] ?? 0) >= 400) {
        respond_fail('not_found');
    }
    $cepFmt = (string) ($data['cep'] ?? (substr($cep, 0, 5) . '-' . substr($cep, 5)));
    $place = (string) ($data['localidade'] ?? '');
    $uf = (string) ($data['uf'] ?? '');
    $detail = trim(implode(', ', array_filter([(string) ($data['logradouro'] ?? ''), (string) ($data['bairro'] ?? '')])), ' ,');
    $label = build_label($place, $uf, 'Brasil');
    respond_ok([
        'source' => 'viacep',
        'country' => 'BR',
        'postal_formatted' => $cepFmt,
        'place_name' => $place,
        'region' => $uf,
        'region_code' => $uf,
        'label' => $label,
        'line_detail' => $detail,
    ]);
}

// --- Zippopotam ---
$cc = strtolower($country);
foreach (postal_variants($country, $postalRaw) as $variant) {
    $variant = (string) $variant;
    $zpUrl = 'https://api.zippopotam.us/' . $cc . '/' . rawurlencode($variant);
    $zp = http_get($zpUrl);
    if (($zp['status'] ?? 0) !== 200) {
        continue;
    }
    $zj = $zp['json'];
    if (!is_array($zj) || empty($zj['places']) || !is_array($zj['places'])) {
        continue;
    }
    $place0 = $zj['places'][0];
    foreach ($zj['places'] as $candidate) {
        if (!is_array($candidate)) {
            continue;
        }
        $lat = trim((string) ($candidate['latitude'] ?? ''));
        if ($lat !== '') {
            $place0 = $candidate;
            break;
        }
    }
    if (!is_array($place0)) {
        continue;
    }
    $placeName = (string) ($place0['place name'] ?? '');
    $state = (string) ($place0['state'] ?? '');
    $stateAbbr = (string) ($place0['state abbreviation'] ?? '');
    $postCode = (string) ($zj['post code'] ?? $variant);
    $countryName = (string) ($zj['country'] ?? '');
    $label = build_label($placeName, $state !== '' ? $state : $stateAbbr, $countryName);
    respond_ok([
        'source' => 'zippopotam',
        'country' => $country,
        'postal_formatted' => $postCode,
        'place_name' => $placeName,
        'region' => $state,
        'region_code' => $stateAbbr,
        'label' => $label,
    ]);
}

// --- Nominatim (fallback, várias estratégias + texto do campo país) ---
$postalForNom = trim(preg_replace('/\s+/u', ' ', $postalRaw) ?? '');
if ($postalForNom === '') {
    respond_fail('not_found');
}

$nomPayload = nominatim_lookup($country, $cc, $postalForNom, $hint);
if ($nomPayload === null) {
    respond_fail('not_found');
}

respond_ok($nomPayload);
