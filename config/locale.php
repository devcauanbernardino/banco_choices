<?php

declare(strict_types=1);

require_once __DIR__ . '/app_bootstrap.php';

/**
 * Internacionalização: região/idioma (sessão + cookie).
 */
const LOCALE_DEFAULT = 'es_AR';
const LOCALE_COOKIE = 'bclocale';
const LOCALE_SESSION_KEY = 'locale';

/**
 * @return list<string>
 */
function locale_supported(): array
{
    return ['es_AR', 'pt_BR', 'en_US'];
}

function locale_bootstrap(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    app_ensure_initialized();
    app_session_start();

    $supported = locale_supported();
    $current = LOCALE_DEFAULT;

    if (isset($_SESSION[LOCALE_SESSION_KEY]) && in_array($_SESSION[LOCALE_SESSION_KEY], $supported, true)) {
        $current = $_SESSION[LOCALE_SESSION_KEY];
    } elseif (isset($_COOKIE[LOCALE_COOKIE]) && in_array($_COOKIE[LOCALE_COOKIE], $supported, true)) {
        $current = $_COOKIE[LOCALE_COOKIE];
        $_SESSION[LOCALE_SESSION_KEY] = $current;
    } else {
        $_SESSION[LOCALE_SESSION_KEY] = $current;
    }

    $GLOBALS['__locale'] = $current;
}

function locale_code(): string
{
    locale_bootstrap();

    return isset($GLOBALS['__locale']) && is_string($GLOBALS['__locale'])
        ? $GLOBALS['__locale']
        : LOCALE_DEFAULT;
}

function locale_html_lang(): string
{
    $map = [
        'es_AR' => 'es-AR',
        'pt_BR' => 'pt-BR',
        'en_US' => 'en-US',
    ];
    $code = locale_code();

    return $map[$code] ?? 'es-AR';
}

/**
 * @return array<string, string>
 */
function locale_messages(): array
{
    static $cache = [];
    $locale = locale_code();
    if (isset($cache[$locale])) {
        return $cache[$locale];
    }

    $root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
    $file = $root . $locale . '.php';
    if (!is_readable($file)) {
        $file = $root . LOCALE_DEFAULT . '.php';
    }
    $messages = is_readable($file) ? require $file : [];
    if (!is_array($messages)) {
        $messages = [];
    }
    $cache[$locale] = $messages;

    return $cache[$locale];
}

function __(string $key): string
{
    $messages = locale_messages();

    return $messages[$key] ?? $key;
}

function locale_set(string $code): void
{
    if (!in_array($code, locale_supported(), true)) {
        $code = LOCALE_DEFAULT;
    }
    locale_bootstrap();
    $_SESSION[LOCALE_SESSION_KEY] = $code;
    $GLOBALS['__locale'] = $code;
    $expires = time() + 365 * 86400;
    setcookie(LOCALE_COOKIE, $code, [
        'expires' => $expires,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[LOCALE_COOKIE] = $code;
}
