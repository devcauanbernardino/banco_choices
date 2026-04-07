<?php

declare(strict_types=1);

if (!function_exists('locale_code')) {
    require_once __DIR__ . '/../../../config/public_url.php';
}

$current = locale_code();
$reqUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$returnParam = rawurlencode($reqUri);
$variants = [
    'es_AR' => ['label' => __('lang.name_es_AR')],
    'pt_BR' => ['label' => __('lang.name_pt_BR')],
    'en_US' => ['label' => __('lang.name_en_US')],
];
$bc_lang_selector_btn_class = $bc_lang_selector_btn_class ?? 'btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1';
$bc_lang_menu_landing = !empty($bc_lang_menu_landing);

$menuClass = 'dropdown-menu dropdown-menu-end shadow-sm';
if ($bc_lang_menu_landing) {
    $menuClass = 'dropdown-menu dropdown-menu-end bc-lang-menu bc-lang-menu--landing shadow';
}

$flags = [
    'es_AR' => '🇦🇷',
    'pt_BR' => '🇧🇷',
    'en_US' => '🇺🇸',
];
?>
<div class="dropdown bc-lang-selector">
    <button class="<?= htmlspecialchars($bc_lang_selector_btn_class, ENT_QUOTES, 'UTF-8') ?>"
        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
        aria-label="<?= htmlspecialchars(__('lang.selector_aria')) ?>">
        <?php if ($bc_lang_menu_landing): ?>
            <svg class="bc-lang-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M2 12H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M12 2C14.5013 4.73835 15.9228 8.29203 16 12C15.9228 15.708 14.5013 19.2616 12 22C9.49872 19.2616 8.07725 15.708 8 12C8.07725 8.29203 9.49872 4.73835 12 2Z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        <?php else: ?>
            <span class="bc-lang-icon" aria-hidden="true" style="font-size: 1.1rem;">🌐</span>
        <?php endif; ?>
        <span class="d-none d-sm-inline"><?= htmlspecialchars(__('lang.selector_label')) ?></span>
    </button>
    <ul class="<?= htmlspecialchars($menuClass) ?>"<?= $bc_lang_menu_landing ? '' : ' style="min-width: 220px;"' ?>>
        <?php if ($bc_lang_menu_landing): ?>
            <li>
                <h6 class="dropdown-header bc-lang-menu__heading mb-0"><?= htmlspecialchars(__('lang.selector_label')) ?></h6>
            </li>
        <?php endif; ?>
        <?php foreach ($variants as $code => $info): ?>
            <li>
                <?php if ($code === $current): ?>
                    <span class="dropdown-item bc-lang-menu__item bc-lang-menu__item--active" aria-current="true">
                        <?php if ($bc_lang_menu_landing): ?>
                            <span class="bc-lang-menu__flag" aria-hidden="true"><?= htmlspecialchars($flags[$code] ?? '') ?></span>
                        <?php endif; ?>
                        <span class="bc-lang-menu__label"><?= htmlspecialchars($info['label']) ?></span>
                        <?php if ($bc_lang_menu_landing): ?>
                            <span class="bc-lang-menu__tick" aria-hidden="true"><span class="bc-lang-menu__check">✓</span></span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <a class="dropdown-item bc-lang-menu__item" href="<?= htmlspecialchars(app_url('set-locale.php?locale=' . rawurlencode($code) . '&return=' . $returnParam)) ?>">
                        <?php if ($bc_lang_menu_landing): ?>
                            <span class="bc-lang-menu__flag" aria-hidden="true"><?= htmlspecialchars($flags[$code] ?? '') ?></span>
                        <?php endif; ?>
                        <span class="bc-lang-menu__label"><?= htmlspecialchars($info['label']) ?></span>
                        <?php if ($bc_lang_menu_landing): ?>
                            <span class="bc-lang-menu__tick" aria-hidden="true"></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
