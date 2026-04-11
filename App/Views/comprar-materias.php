<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/signup_flow.php';
require_once __DIR__ . '/../Models/Usuario.php';

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$uid = (int) $_SESSION['usuario']['id'];
$conexao = new Conexao();
$pdo = $conexao->conectar();
$usuarioModel = new Usuario($pdo);
$ownedRows = $usuarioModel->buscarMateriasDoUsuario($uid);
$ownedIds = [];
foreach ($ownedRows as $row) {
    $mid = (int) ($row['id'] ?? 0);
    if ($mid > 0) {
        $ownedIds[$mid] = true;
    }
}

$allMaterias = $pdo->query('SELECT id, nome FROM materias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
$disponiveis = [];
foreach ($allMaterias as $m) {
    $id = (int) $m['id'];
    if ($id > 0 && !isset($ownedIds[$id])) {
        $disponiveis[] = $m;
    }
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        header('Location: ' . app_url('comprar-materias.php'));
        exit;
    }
    $raw = $_POST['materias'] ?? [];
    if (!is_array($raw)) {
        $raw = [];
    }
    $allowedSet = [];
    foreach ($disponiveis as $d) {
        $allowedSet[(int) $d['id']] = true;
    }
    $picked = [];
    foreach ($raw as $v) {
        $id = (int) $v;
        if ($id > 0 && isset($allowedSet[$id])) {
            $picked[$id] = $id;
        }
    }
    $picked = array_values($picked);
    if ($picked === []) {
        $error = __('addon.select_min');
    } else {
        $_SESSION['addon_materias'] = $picked;
        $ultimoPlanoId = $usuarioModel->buscarUltimoPlanoIdParaUsuarioId($uid);
        $_SESSION['addon_plan'] = addon_resolve_plan_for_extra_materias($ultimoPlanoId);
        header('Location: ' . app_url('checkout-addon.php'));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(__('addon.page_title_materias')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/sidebar.css')) ?>">
</head>

<body class="app-private-body">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <?php
    $app_toolbar_mode = 'mobile';
    $app_toolbar_title = (string) __('nav.buy_subjects');
    require __DIR__ . '/includes/app-private-toolbar.php';
    unset($app_toolbar_mode);
    ?>

    <main class="app-main px-4 pb-4 pt-0">
        <?php
        $app_toolbar_mode = 'desktop';
        require __DIR__ . '/includes/app-private-toolbar.php';
        unset($app_toolbar_mode);
        ?>

        <div class="app-page-header mb-4">
            <h2 class="fw-bold mb-1"><?= htmlspecialchars(__('nav.buy_subjects')) ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars(__('addon.intro')) ?></p>
        </div>

        <?php if ($disponiveis === []): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <span class="material-icons text-muted mb-2" style="font-size: 3rem;">check_circle</span>
                    <h3 class="h5 fw-bold"><?= htmlspecialchars(__('addon.empty')) ?></h3>
                    <p class="text-muted mb-0"><?= htmlspecialchars(__('addon.empty_hint')) ?></p>
                    <a href="<?= htmlspecialchars(app_url('dashboard.php')) ?>"
                        class="btn btn-primary mt-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-2"><?= htmlspecialchars(__('nav.dashboard')) ?></a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($error !== null): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <?= csrf_field() ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush rounded-3 border">
                            <?php foreach ($disponiveis as $m): ?>
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                                    <input class="form-check-input flex-shrink-0 mt-0" type="checkbox" name="materias[]"
                                        value="<?= (int) $m['id'] ?>">
                                    <span class="material-icons text-primary flex-shrink-0">menu_book</span>
                                    <span class="fw-semibold"><?= htmlspecialchars((string) $m['nome']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column-reverse flex-sm-row gap-2 justify-content-sm-end align-items-stretch align-items-sm-center">
                    <a href="<?= htmlspecialchars(app_url('dashboard.php')) ?>"
                        class="btn btn-outline-primary btn-lg w-100 w-sm-auto d-inline-flex align-items-center justify-content-center"><?= htmlspecialchars(__('nav.dashboard')) ?></a>
                    <button type="submit"
                        class="btn btn-primary btn-lg w-100 w-sm-auto"><?= htmlspecialchars(__('addon.continue_checkout')) ?></button>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/includes/private-footer-scripts.php'; ?>
</body>

</html>
