<?php
declare(strict_types=1);
/**
 * Alternância tema claro/escuro (ícones sol e lua). Usa classes em sidebar.css.
 * Opcional: $theme_mode_toggle_sheet = true (offcanvas / cards com fundo de superfície).
 */
$sheet = !empty($theme_mode_toggle_sheet);
$rowClass = 'app-sidebar-theme-row' . ($sheet ? ' app-sidebar-theme-row--sheet' : '');
?>
<div class="<?= htmlspecialchars($rowClass, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('sidebar.appearance'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="app-sidebar-theme-seg" role="group" aria-label="<?= htmlspecialchars(__('sidebar.theme_group_aria'), ENT_QUOTES, 'UTF-8') ?>">
        <button type="button" class="app-sidebar-theme-opt js-theme-mode-btn" data-theme="light"
            aria-pressed="false" aria-label="<?= htmlspecialchars(__('sidebar.theme_light_aria'), ENT_QUOTES, 'UTF-8') ?>">
            <span class="material-icons" aria-hidden="true">light_mode</span>
        </button>
        <button type="button" class="app-sidebar-theme-opt js-theme-mode-btn" data-theme="dark"
            aria-pressed="false" aria-label="<?= htmlspecialchars(__('sidebar.theme_dark_aria'), ENT_QUOTES, 'UTF-8') ?>">
            <span class="material-icons" aria-hidden="true">dark_mode</span>
        </button>
    </div>
</div>
