<?php
declare(strict_types=1);
/**
 * Barra mobile (título + tema + avatar) ou faixa desktop (tema + avatar).
 *
 * Antes de incluir:
 *   $app_toolbar_title (string) — título da página (mobile centralizado; desktop à esquerda da faixa)
 *   $app_toolbar_mode = 'mobile' | 'desktop'
 */
require_once __DIR__ . '/../../../config/public_url.php';

$app_toolbar_mode = $app_toolbar_mode ?? 'mobile';
$app_toolbar_title = isset($app_toolbar_title) ? (string) $app_toolbar_title : '';
$u = $_SESSION['usuario'] ?? null;
$nome = is_array($u) ? (string) ($u['nome'] ?? 'User') : 'User';
$avatarUrl = 'https://ui-avatars.com/api/?name=' . rawurlencode($nome) . '&background=6a0392&color=fff&size=64';

if ($app_toolbar_mode === 'mobile') {
    ?>
<header class="app-mobile-topbar app-mobile-topbar--tools d-lg-none" aria-label="<?= htmlspecialchars(__('nav.menu_aria')) ?>">
    <span class="app-mobile-topbar-title"><?= htmlspecialchars($app_toolbar_title) ?></span>
    <div class="app-mobile-topbar-actions">
        <?php
        $theme_mode_toggle_header = true;
        require __DIR__ . '/theme-mode-toggle.php';
        unset($theme_mode_toggle_header);
        ?>
        <?php if (is_array($u)): ?>
            <a class="app-toolbar-avatar-link" href="<?= htmlspecialchars(app_url('perfil.php')) ?>"
                title="<?= htmlspecialchars(__('sidebar.profile')) ?>">
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="" class="rounded-circle app-toolbar-avatar" width="32" height="32">
            </a>
        <?php endif; ?>
    </div>
</header>
    <?php
} elseif ($app_toolbar_mode === 'desktop') {
    ?>
<div class="app-desktop-toolbar d-none d-lg-flex align-items-center justify-content-between gap-3">
    <div class="app-desktop-toolbar-lead min-w-0">
        <?php if ($app_toolbar_title !== ''): ?>
            <p class="app-desktop-toolbar-title mb-0"><?= htmlspecialchars($app_toolbar_title) ?></p>
        <?php endif; ?>
    </div>
    <div class="app-desktop-toolbar-actions d-flex align-items-center gap-2 flex-shrink-0">
        <?php
        $theme_mode_toggle_header = true;
        require __DIR__ . '/theme-mode-toggle.php';
        unset($theme_mode_toggle_header);
        ?>
        <?php if (is_array($u)): ?>
            <a class="app-toolbar-avatar-link" href="<?= htmlspecialchars(app_url('perfil.php')) ?>"
                title="<?= htmlspecialchars(__('sidebar.profile')) ?>">
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="" class="rounded-circle app-toolbar-avatar" width="36" height="36">
            </a>
        <?php endif; ?>
    </div>
</div>
    <?php
}
