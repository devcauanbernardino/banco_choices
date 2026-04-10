<?php
declare(strict_types=1);
/**
 * Um botão alterna claro/escuro: ícone sol (claro) ou lua (escuro).
 * Opcional: $theme_mode_toggle_sheet = true (cartões / superfície clara).
 * Opcional: $theme_mode_toggle_header = true (barra superior compacta).
 */
$ariaToDark = htmlspecialchars(__('sidebar.theme_switch_to_dark'), ENT_QUOTES, 'UTF-8');
$ariaToLight = htmlspecialchars(__('sidebar.theme_switch_to_light'), ENT_QUOTES, 'UTF-8');
$appearanceTitle = htmlspecialchars(__('sidebar.appearance'), ENT_QUOTES, 'UTF-8');

if (!empty($theme_mode_toggle_header)) {
    ?>
<div class="app-header-theme">
    <button type="button"
        class="app-theme-toggle-single app-theme-toggle-single--compact js-theme-single-toggle"
        data-aria-to-dark="<?= $ariaToDark ?>"
        data-aria-to-light="<?= $ariaToLight ?>"
        aria-label="<?= $ariaToDark ?>"
        aria-pressed="false"
        title="<?= $appearanceTitle ?>">
        <span class="material-icons js-theme-single-icon" aria-hidden="true">light_mode</span>
    </button>
</div>
    <?php
} else {
    $sheet = !empty($theme_mode_toggle_sheet);
    $rowClass = 'app-sidebar-theme-row' . ($sheet ? ' app-sidebar-theme-row--sheet' : '');
    ?>
<div class="<?= htmlspecialchars($rowClass, ENT_QUOTES, 'UTF-8') ?>" title="<?= $appearanceTitle ?>">
    <button type="button"
        class="app-theme-toggle-single app-theme-toggle-single--wide js-theme-single-toggle"
        data-aria-to-dark="<?= $ariaToDark ?>"
        data-aria-to-light="<?= $ariaToLight ?>"
        aria-label="<?= $ariaToDark ?>"
        aria-pressed="false"
        title="<?= $appearanceTitle ?>">
        <span class="material-icons js-theme-single-icon" aria-hidden="true">light_mode</span>
    </button>
</div>
    <?php
}
