<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap_env.php';

/**
 * Garante .env carregado e política de erros PHP alinhada ao ambiente (uma vez por request).
 */
function app_ensure_initialized(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    loadProjectEnv();

    $rawEnv = getenv('APP_ENV');
    if ($rawEnv === false || trim((string) $rawEnv) === '') {
        $rawEnv = 'development';
    }
    $env = strtolower(trim((string) $rawEnv));
    $isProd = in_array($env, ['production', 'prod', 'live'], true);

    if ($isProd) {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    } else {
        ini_set('display_errors', '1');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);
    }
}

function app_request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

    return $fwd === 'https';
}

/**
 * Deve ser chamado antes de session_start(). Idempotente.
 */
function app_session_configure(): void
{
    static $configured = false;
    if ($configured) {
        return;
    }
    $configured = true;

    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $secure = app_request_is_https();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function app_session_start(): void
{
    app_ensure_initialized();
    app_session_configure();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
