<?php

declare(strict_types=1);

if (!function_exists('__')) {
    require_once __DIR__ . '/../../../config/public_url.php';
}

/**
 * Links principais da sidebar (desktop + barra inferior mobile).
 */
function sidebar_nav_links(): array
{
    return [
        ['page' => 'dashboard.php', 'icon' => 'dashboard', 'label' => __('nav.dashboard'), 'short' => __('nav.dashboard')],
        ['page' => 'estatisticas.php', 'icon' => 'bar_chart', 'label' => __('nav.stats'), 'short' => __('nav.stats')],
        ['page' => 'bancoperguntas.php', 'icon' => 'quiz', 'label' => __('nav.bank'), 'short' => __('nav.bank')],
        ['page' => 'comprar-materias.php', 'icon' => 'add_shopping_cart', 'label' => __('nav.buy_subjects'), 'short' => __('nav.buy_subjects_short')],
        ['page' => 'simulados.php', 'icon' => 'assignment', 'label' => __('nav.simulados'), 'short' => __('nav.simulados')],
    ];
}
