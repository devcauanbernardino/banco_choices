<?php
declare(strict_types=1);
/**
 * Sidebar desktop + barra inferior mobile + painel "Mais" (tema / sair).
 */
require_once __DIR__ . '/../../../config/public_url.php';
require_once __DIR__ . '/../../../config/test_users.php';
require_once __DIR__ . '/sidebar-nav-config.php';

if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['usuario']['id'])) {
    require_once __DIR__ . '/../../../config/conexao.php';
    require_once __DIR__ . '/../../Models/Usuario.php';
    static $materiasSessaoSincronizadas = false;
    if (!$materiasSessaoSincronizadas) {
        $materiasSessaoSincronizadas = true;
        $cx = new Conexao();
        $pdo = $cx->conectar();
        $um = new Usuario($pdo);
        $sessEmail = (string) ($_SESSION['usuario']['email'] ?? '');
        if (!test_user_skips_default_materias($sessEmail)) {
            $um->garantirMateriasParaUsuario((int) $_SESSION['usuario']['id'], [1, 2]);
        }
        $_SESSION['usuario']['materias'] = $um->buscarMateriasDoUsuario((int) $_SESSION['usuario']['id']);
    }
}

$pagina_atual = basename($_SERVER['PHP_SELF'] ?? '');
$main_links = sidebar_nav_links();
$logoUrl = public_asset_url('img/logo-bd-transparente.png');
?>

<!-- Sidebar desktop -->
<aside class="app-sidebar d-none d-lg-flex flex-column" id="appSidebarDesktop" aria-label="<?= htmlspecialchars(__('nav.menu_aria')) ?>">
    <div class="app-sidebar-brand">
        <a class="app-sidebar-brand-link text-decoration-none" href="<?= htmlspecialchars(app_url('dashboard.php')) ?>"
            title="<?= htmlspecialchars(__('nav.dashboard')) ?>">
            <span class="app-sidebar-logo-wrap" aria-hidden="true">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="" width="40" height="40" class="app-sidebar-logo">
            </span>
            <div class="app-sidebar-brand-text">
                <div class="app-sidebar-title">Banco de Choices</div>
                <div class="app-sidebar-sub"><?= htmlspecialchars(__('sidebar.subtitle')) ?></div>
            </div>
        </a>
    </div>
    <?php require __DIR__ . '/sidebar-nav-links.php'; ?>
    <div class="app-sidebar-section app-sidebar-section--lang pb-3 mt-auto pt-3">
        <span class="app-sidebar-section-label"><?= htmlspecialchars(__('lang.selector_label')) ?></span>
        <div class="mt-2">
            <?php
            $bc_lang_menu_landing = true;
            $bc_lang_compact_btn = true;
            $bc_lang_selector_btn_class = 'btn btn-navbar-lang btn-navbar-lang--sidebar-desktop dropdown-toggle d-inline-flex align-items-center gap-2 w-100';
            $bc_lang_popper_fixed = true;
            require __DIR__ . '/language-selector.php';
            ?>
        </div>
    </div>
</aside>

<!-- Painel mobile: idioma + sair -->
<div class="offcanvas offcanvas-bottom app-offcanvas-more" tabindex="-1" id="sidebarMobile"
    aria-labelledby="sidebarMobileLabel">
    <div class="app-offcanvas-more-handle" aria-hidden="true"></div>
    <div class="offcanvas-header app-offcanvas-more__header border-0">
        <div class="d-flex align-items-center gap-2" id="sidebarMobileLabel">
            <span class="app-offcanvas-more__title"><?= htmlspecialchars(__('sidebar.more_options')) ?></span>
        </div>
        <button type="button" class="btn-close app-offcanvas-more__close" data-bs-dismiss="offcanvas"
            aria-label="<?= htmlspecialchars(__('sidebar.close')) ?>"></button>
    </div>
    <div class="offcanvas-body app-offcanvas-more__body">
        <div class="app-more-panel" role="group" aria-label="<?= htmlspecialchars(__('sidebar.more_options')) ?>">
            <div class="app-more-panel__row">
                <span class="app-more-panel__row-label" id="sidebarMobileLangLabel"><?= htmlspecialchars(__('lang.selector_label')) ?></span>
                <div class="app-more-panel__row-control" aria-labelledby="sidebarMobileLangLabel">
                    <?php
                    $bc_lang_menu_landing = true;
                    $bc_lang_compact_btn = true;
                    $bc_lang_selector_btn_class = 'btn btn-navbar-lang btn-navbar-lang--more-sheet dropdown-toggle d-inline-flex align-items-center gap-2';
                    $bc_lang_popper_fixed = true;
                    require __DIR__ . '/language-selector.php';
                    ?>
                </div>
            </div>
            <div class="app-more-panel__divider" aria-hidden="true"></div>
            <a class="app-more-panel__logout" href="<?= htmlspecialchars(app_url('logout.php')) ?>"
                title="<?= htmlspecialchars(__('sidebar.logout')) ?>">
                <span class="material-icons" aria-hidden="true">logout</span>
                <span><?= htmlspecialchars(__('sidebar.logout')) ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Barra de navegação inferior (mobile) -->
<nav class="app-mobile-bottom d-lg-none" aria-label="<?= htmlspecialchars(__('nav.menu_aria')) ?>">
    <div class="app-mobile-bottom-inner">
        <?php foreach ($main_links as $link): ?>
            <?php
            $active = $pagina_atual === $link['page'];
            $href = app_url($link['page']);
            $short = $link['short'] ?? $link['label'];
            ?>
            <a class="app-mobile-bottom-item<?= $active ? ' active' : '' ?>" href="<?= htmlspecialchars($href) ?>"
                <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="material-icons" aria-hidden="true"><?= htmlspecialchars($link['icon']) ?></span>
                <span class="app-mobile-bottom-label"><?= htmlspecialchars($short) ?></span>
            </a>
        <?php endforeach; ?>
        <a class="app-mobile-bottom-item<?= $pagina_atual === 'perfil.php' ? ' active' : '' ?>"
            href="<?= htmlspecialchars(app_url('perfil.php')) ?>"
            <?= $pagina_atual === 'perfil.php' ? 'aria-current="page"' : '' ?>>
            <span class="material-icons" aria-hidden="true">person</span>
            <span class="app-mobile-bottom-label"><?= htmlspecialchars(__('nav.profile')) ?></span>
        </a>
        <button type="button" class="app-mobile-bottom-item app-mobile-bottom-item--btn" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarMobile" aria-controls="sidebarMobile" aria-label="<?= htmlspecialchars(__('sidebar.more_aria')) ?>">
            <span class="material-icons" aria-hidden="true">more_horiz</span>
            <span class="app-mobile-bottom-label"><?= htmlspecialchars(__('sidebar.more')) ?></span>
        </button>
    </div>
</nav>
