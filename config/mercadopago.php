<?php

require_once __DIR__ . '/bootstrap_env.php';

/**
 * URL pública absoluta (scheme + host + path até public/) para back_urls e webhook.
 * Se SITE_URL tiver só o host (ex.: http://localhost) e a app estiver em subpasta,
 * acrescenta app_base_path() para não dar 404 ao voltar do Mercado Pago.
 */
function mercadopago_site_url(): string
{
    require_once __DIR__ . '/public_url.php';

    $raw = rtrim((string) (getenv('SITE_URL') ?: ''), '/');
    $appPath = app_base_path();

    if ($raw === '') {
        if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $scheme = $https ? 'https' : 'http';
            $host = (string) $_SERVER['HTTP_HOST'];

            return rtrim($scheme . '://' . $host . ($appPath === '' ? '' : $appPath), '/');
        }

        return rtrim('https://bancodechoices.com', '/');
    }

    $parts = parse_url($raw);
    if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
        return $raw;
    }

    $envPath = isset($parts['path']) ? rtrim((string) $parts['path'], '/') : '';
    if ($appPath !== '' && ($envPath === '' || $envPath === '/')) {
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

        return rtrim($parts['scheme'] . '://' . $parts['host'] . $port . $appPath, '/');
    }

    return $raw;
}

/**
 * Configuração centralizada Mercado Pago (credenciais apenas via ambiente).
 *
 * MP_ACCESS_TOKEN — obrigatório para API / SDK
 * MP_PUBLIC_KEY — Checkout Pro em geral só redireciona; útil para Bricks futuros
 * MP_WEBHOOK_SECRET — validação do cabeçalho x-signature (recomendado em produção)
 * MP_CURRENCY_ID — moeda da preferência (ARS para conta Argentina; BRL para Brasil)
 *
 * Compatibilidade: MERCADOPAGO_ACCESS_TOKEN / MERCADOPAGO_PUBLIC_KEY
 */
function mercadopago_config(): array
{
    loadProjectEnv();

    $access = getenv('MP_ACCESS_TOKEN') ?: getenv('MERCADOPAGO_ACCESS_TOKEN') ?: '';
    $public = getenv('MP_PUBLIC_KEY') ?: getenv('MERCADOPAGO_PUBLIC_KEY') ?: '';
    $webhookSecret = getenv('MP_WEBHOOK_SECRET') ?: '';
    $currency = getenv('MP_CURRENCY_ID') ?: '';
    $currency = is_string($currency) ? strtoupper(trim($currency)) : '';
    if ($currency === '') {
        $currency = 'ARS';
    }

    return [
        'access_token' => is_string($access) ? trim($access) : '',
        'public_key' => is_string($public) ? trim($public) : '',
        'webhook_secret' => is_string($webhookSecret) ? trim($webhookSecret) : '',
        'site_url' => mercadopago_site_url(),
        'currency_id' => $currency,
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
