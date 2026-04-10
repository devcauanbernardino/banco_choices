<?php
declare(strict_types=1);
/**
 * Bloco de navegação reutilizado na sidebar desktop e no offcanvas mobile.
 * Espera $pagina_atual (basename da página).
 */
require_once __DIR__ . '/../../../config/public_url.php';
require_once __DIR__ . '/sidebar-nav-config.php';

$main_links = sidebar_nav_links();
?>
<nav class="app-sidebar-nav flex-grow-1" aria-label="<?= htmlspecialchars(__('nav.menu_aria')) ?>">
    <?php foreach ($main_links as $link): ?>
        <?php
        $active = $pagina_atual === $link['page'];
        $href = app_url($link['page']);
        $label = $link['label'];
        ?>
        <a class="app-sidebar-link<?= $active ? ' active' : '' ?>" href="<?= htmlspecialchars($href) ?>"
            title="<?= htmlspecialchars($label) ?>">
            <span class="material-icons" aria-hidden="true"><?= htmlspecialchars($link['icon']) ?></span>
            <span class="app-sidebar-link-text"><?= htmlspecialchars($label) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<div class="app-sidebar-footer">
    <span class="app-sidebar-section-label"><?= htmlspecialchars(__('sidebar.account')) ?></span>
    <a class="app-sidebar-link<?= $pagina_atual === 'perfil.php' ? ' active' : '' ?>" href="<?= htmlspecialchars(app_url('perfil.php')) ?>"
        title="<?= htmlspecialchars(__('sidebar.profile')) ?>">
        <span class="material-icons" aria-hidden="true">person</span>
        <span class="app-sidebar-link-text"><?= htmlspecialchars(__('sidebar.profile')) ?></span>
    </a>
    <a class="app-sidebar-link app-sidebar-link-logout" href="<?= htmlspecialchars(app_url('logout.php')) ?>"
        title="<?= htmlspecialchars(__('sidebar.logout')) ?>">
        <span class="material-icons" aria-hidden="true">logout</span>
        <span class="app-sidebar-link-text"><?= htmlspecialchars(__('sidebar.logout')) ?></span>
    </a>

    <div class="app-sidebar-collapse-wrap d-none d-lg-flex">
        <button type="button" class="app-sidebar-collapse-btn js-sidebar-toggle" aria-expanded="true"
            aria-controls="appSidebarDesktop" title="<?= htmlspecialchars(__('sidebar.collapse_aria')) ?>">
            <span class="material-icons app-sidebar-collapse-ico" aria-hidden="true">keyboard_double_arrow_left</span>
            <span class="app-sidebar-collapse-label"><?= htmlspecialchars(__('sidebar.collapse')) ?></span>
        </button>
    </div>
</div>
