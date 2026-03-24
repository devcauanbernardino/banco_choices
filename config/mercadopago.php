<?php

require_once __DIR__ . '/bootstrap_env.php';

/**
 * Configuração centralizada Mercado Pago (credenciais apenas via ambiente).
 *
 * MP_ACCESS_TOKEN — obrigatório para API / SDK
 * MP_PUBLIC_KEY — Checkout Pro em geral só redireciona; útil para Bricks futuros
 * MP_WEBHOOK_SECRET — validação do cabeçalho x-signature (recomendado em produção)
 *
 * Compatibilidade: MERCADOPAGO_ACCESS_TOKEN / MERCADOPAGO_PUBLIC_KEY
 */
function mercadopago_config(): array
{
    loadProjectEnv();

    $access = getenv('MP_ACCESS_TOKEN') ?: getenv('MERCADOPAGO_ACCESS_TOKEN') ?: '';
    $public = getenv('MP_PUBLIC_KEY') ?: getenv('MERCADOPAGO_PUBLIC_KEY') ?: '';
    $webhookSecret = getenv('MP_WEBHOOK_SECRET') ?: '';

    return [
        'access_token' => is_string($access) ? trim($access) : '',
        'public_key' => is_string($public) ? trim($public) : '',
        'webhook_secret' => is_string($webhookSecret) ? trim($webhookSecret) : '',
        'site_url' => rtrim((string) (getenv('SITE_URL') ?: 'https://bancodechoices.com'), '/'),
    ];
}

/**
 * Inicializa o token global usado pelo SDK oficial.
 */
function mercadopago_bootstrap_sdk(): void
{
    require_once dirname(__DIR__) . '/vendor/autoload.php';

    $cfg = mercadopago_config();
    if ($cfg['access_token'] === '') {
        throw new RuntimeException('MP_ACCESS_TOKEN não configurado.');
    }
    MercadoPago\MercadoPagoConfig::setAccessToken($cfg['access_token']);
}
