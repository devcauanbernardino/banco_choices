<?php

declare(strict_types=1);

/**
 * Retorna país aproximado pelo IP (ip-api.com).
 * Usado no checkout para preencher o campo País automaticamente.
 */
header('Content-Type: application/json; charset=utf-8');

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($ip === '' || $ip === '::1' || $ip === '127.0.0.1') {
    echo json_encode([
        'ok' => true,
        'country' => 'Argentina',
        'country_code' => 'AR',
        'note' => 'local',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode';
$ctx = stream_context_create([
    'http' => [
        'timeout' => 3,
        'ignore_errors' => true,
    ],
]);

$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    echo json_encode([
        'ok' => false,
        'country' => 'Argentina',
        'country_code' => 'AR',
        'note' => 'fallback',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($raw, true);
if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
    echo json_encode([
        'ok' => false,
        'country' => 'Argentina',
        'country_code' => 'AR',
        'note' => 'fallback',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok' => true,
    'country' => (string) ($data['country'] ?? 'Argentina'),
    'country_code' => (string) ($data['countryCode'] ?? 'AR'),
], JSON_UNESCAPED_UNICODE);
