<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/public_url.php';

$code = isset($_GET['locale']) ? (string) $_GET['locale'] : (isset($_POST['locale']) ? (string) $_POST['locale'] : '');
if (!in_array($code, locale_supported(), true)) {
    $code = LOCALE_DEFAULT;
}

locale_set($code);

$return = isset($_GET['return']) ? (string) $_GET['return'] : (isset($_POST['return']) ? (string) $_POST['return'] : '');
if ($return === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $return)) {
    header('Location: ' . app_url('index.php'), true, 302);
    exit;
}

$path = $return;
if (str_contains($path, '?')) {
    $path = explode('?', $path, 2)[0];
}
$base = app_base_path();
if ($base !== '' && str_starts_with($path, $base . '/')) {
    $path = substr($path, strlen($base)) ?: '/';
} elseif ($base !== '' && $path === $base) {
    $path = '/';
}
$path = ltrim($path, '/');
$query = '';
if (str_contains($return, '?')) {
    $query = '?' . explode('?', $return, 2)[1];
}

if ($path === '' || $path === '/') {
    $target = app_url('index.php') . $query;
} else {
    $target = app_url($path) . $query;
}

header('Location: ' . $target, true, 302);
exit;
