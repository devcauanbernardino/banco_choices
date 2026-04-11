<?php

declare(strict_types=1);

/**
 * URL path to a file inside public/ (works when entry script is in public/ or public/controllers/).
 */
if (!function_exists('public_asset_url')) {
    function public_asset_url(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptDir = dirname(str_replace('\\', '/', $scriptName));
        if (str_ends_with($scriptDir, '/controllers')) {
            $base = dirname($scriptDir);
        } else {
            $base = $scriptDir;
        }
        $base = rtrim($base, '/');
        if ($base === '' || $base === '.' || $base === '/') {
            return '/' . $path;
        }

        return $base . '/' . $path;
    }
}

/**
 * Caminho base da app em relação ao host (ex.: /banco_choices/public quando o projeto está num subdiretório).
 */
if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
        $dir = dirname($script);
        if (str_ends_with($dir, '/controllers')) {
            $dir = dirname($dir);
        }
        $dir = rtrim($dir, '/');
        if ($dir === '' || $dir === '.' || $dir === '/') {
            return '';
        }

        return $dir;
    }
}

/**
 * URL interna absoluta no caminho (inclui query string). Use em redirects e links.
 */
if (!function_exists('app_url')) {
    function app_url(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $base = app_base_path();
        if ($base === '') {
            return '/' . $path;
        }

        return $base . '/' . $path;
    }
}

require_once __DIR__ . '/locale.php';
require_once __DIR__ . '/csrf.php';
locale_bootstrap();
