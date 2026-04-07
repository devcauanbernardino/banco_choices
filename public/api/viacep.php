<?php

declare(strict_types=1);

/**
 * Proxy JSON para ViaCEP (evita CORS no navegador).
 */
header('Content-Type: application/json; charset=utf-8');

$cep = isset($_GET['cep']) ? preg_replace('/\D/', '', (string) $_GET['cep']) : '';
if (strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['erro' => true, 'mensagem' => 'CEP inválido']);
    exit;
}

$url = 'https://viacep.com.br/ws/' . $cep . '/json/';
$ctx = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true,
    ],
]);

$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    http_response_code(502);
    echo json_encode(['erro' => true, 'mensagem' => 'Falha ao consultar CEP']);
    exit;
}

echo $raw;
