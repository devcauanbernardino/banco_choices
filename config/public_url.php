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
