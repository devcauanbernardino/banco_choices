<?php

declare(strict_types=1);

require_once __DIR__ . '/public_url.php';

$favicon = htmlspecialchars(public_asset_url('assets/img/favicon.svg'), ENT_QUOTES, 'UTF-8');
$manifest = htmlspecialchars(public_asset_url('site.webmanifest'), ENT_QUOTES, 'UTF-8');
?>
<link rel="icon" type="image/svg+xml" href="<?= $favicon ?>">
<link rel="icon" href="<?= $favicon ?>" sizes="any">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $favicon ?>">
<link rel="mask-icon" href="<?= $favicon ?>" color="#6a0392">
<link rel="manifest" href="<?= $manifest ?>">
<meta name="theme-color" content="#6a0392">
<meta name="msapplication-TileColor" content="#6a0392">
