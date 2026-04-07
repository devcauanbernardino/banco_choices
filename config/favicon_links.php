<?php

declare(strict_types=1);

/**
 * Ícones do site (páginas públicas e áreas logadas).
 * Incluir uma vez dentro de <head> em cada página HTML.
 */
require_once __DIR__ . '/public_url.php';

/**
 * @param non-empty-string $relativePath caminho relativo a public/ (ex.: assets/img/favicon.svg)
 */
function favicon_href(string $relativePath): string
{
    return htmlspecialchars(public_asset_url($relativePath), ENT_QUOTES, 'UTF-8');
}

$publicDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
$asset = static function (string $relative) use ($publicDir): bool {
    $path = $publicDir . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relative, '/'));

    return is_file($path);
};

$svg = favicon_href('assets/img/favicon.svg');
$png32 = favicon_href('assets/img/favicon-round-32x32.png');
$png192 = favicon_href('assets/img/favicon-round-192x192.png');
$apple = favicon_href('assets/img/apple-touch-icon-round.png');
$manifest = favicon_href('site.webmanifest');

if ($asset('assets/img/favicon.svg')) {
    echo '<link rel="icon" type="image/svg+xml" href="' . $svg . '">' . "\n";
}
if ($asset('assets/img/favicon-round-32x32.png')) {
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $png32 . '">' . "\n";
}
if ($asset('assets/img/favicon-round-192x192.png')) {
    echo '<link rel="icon" type="image/png" sizes="192x192" href="' . $png192 . '">' . "\n";
}
if ($asset('assets/img/apple-touch-icon-round.png')) {
    echo '<link rel="apple-touch-icon" href="' . $apple . '" sizes="180x180">' . "\n";
}
if ($asset('assets/img/favicon.svg')) {
    echo '<link rel="mask-icon" href="' . $svg . '" color="#6a0392">' . "\n";
}
echo '<link rel="manifest" href="' . $manifest . '">' . "\n";
echo '<meta name="theme-color" content="#6a0392">' . "\n";
echo '<meta name="msapplication-TileColor" content="#6a0392">' . "\n";
if ($asset('assets/img/favicon-round-192x192.png')) {
    echo '<meta name="msapplication-TileImage" content="' . $png192 . '">' . "\n";
}
