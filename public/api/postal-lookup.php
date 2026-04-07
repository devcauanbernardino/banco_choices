<?php

declare(strict_types=1);

/**
 * Consulta global de código postal: Brasil (ViaCEP), demais países (Zippopotam + Nominatim).
 */
header('Content-Type: application/json; charset=utf-8');

$country = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($_GET['country'] ?? '')));
$postalRaw = trim((string) ($_GET['postal'] ?? ''));

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
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => "User-Agent: BancoChoices/1.0 (postal-lookup)\r\nAccept: application/json\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    $status = 200;
    if (!empty($http_response_header[0]) && preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }

    return ['status' => $status, 'raw' => $raw !== false ? $raw : '', 'json' => is_string($raw) && $raw !== '' ? json_decode($raw, true) : null];
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

// --- Nominatim (fallback) ---
$postalForNom = trim(preg_replace('/\s+/u', ' ', $postalRaw) ?? '');
if ($postalForNom === '') {
    respond_fail('not_found');
}

$query = http_build_query([
    'postalcode' => $postalForNom,
    'countrycodes' => $cc,
    'format' => 'json',
    'limit' => 1,
    'addressdetails' => 1,
], '', '&', PHP_QUERY_RFC3986);

$nomUrl = 'https://nominatim.openstreetmap.org/search?' . $query;
$nom = http_get($nomUrl);
if (($nom['status'] ?? 0) !== 200 || !is_array($nom['json']) || $nom['json'] === []) {
    respond_fail('not_found');
}

$item = $nom['json'][0];
if (!is_array($item)) {
    respond_fail('not_found');
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

respond_ok([
    'source' => 'nominatim',
    'country' => $country,
    'postal_formatted' => $pc !== '' ? $pc : $postalForNom,
    'place_name' => $place,
    'region' => $state,
    'region_code' => (string) ($addr['ISO3166-2-lvl4'] ?? ''),
    'label' => $label,
]);
