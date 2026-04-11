<?php
/**
 * ARQUIVO: perfil.php
 * OBJETIVO: Exibir e permitir a edição dos dados do usuário logado.
 */

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';
require_once __DIR__ . '/../Models/Usuario.php';

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$usuario = $_SESSION['usuario'];

$objConexao = new Conexao();
$db = $objConexao->conectar();
$dashboard = new DashboardController($db, $usuario['id']);
$stats = $dashboard->getStats();

$objUsuario = new Usuario($db);
$materiasUsuario = $objUsuario->buscarMateriasDoUsuario((int) $usuario['id']);

$flashOk = isset($_GET['sucesso']) && $_GET['sucesso'] === '1';
$flashErr = $_GET['erro'] ?? '';
$msgErro = [
    'nome_vazio' => __('perfil.err.nome_vazio'),
    'falha_ao_salvar' => __('perfil.err.falha_ao_salvar'),
    'senha_incorreta' => __('perfil.err.senha_incorreta'),
    'senha_curta' => __('perfil.err.senha_curta'),
];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(__('perfil.page_title')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/sidebar.css')) ?>">

    <style>
        :root { --primary-color: #6a0392; }

        .perfil-hero {
            background: linear-gradient(135deg, #4c0d6b 0%, #7c3aed 45%, #a78bfa 100%);
            border-radius: 20px;
            padding: 2rem 2rem 3rem;
            position: relative;
            overflow: hidden;
        }
        .perfil-hero::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .perfil-avatar {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.35);
            object-fit: cover;
            box-shadow: 0 12px 32px rgba(0,0,0,0.2);
        }
        .perfil-stat {
            background: var(--app-surface);
            border: 1px solid var(--app-border);
            border-radius: 14px;
            padding: 1rem 1.1rem;
        }
        .perfil-card {
            border-radius: 16px;
            border: 1px solid var(--app-border);
            background: var(--app-surface);
        }

        .perfil-form-actions {
            border-top: 1px solid var(--app-border);
            margin-top: 1.75rem;
            padding-top: 1.35rem;
        }

        [data-theme="dark"] .perfil-form-actions {
            border-top-color: rgba(255, 255, 255, 0.08);
        }

        .perfil-form-actions .btn {
            min-height: 48px;
            font-weight: 600;
        }
        /* Chips sobre o gradiente: fundo claro + texto escuro (evita .text-white do hero forçar branco) */
        .perfil-hero .materia-chip {
            font-size: 0.8rem;
            border-radius: 999px;
            padding: 0.4rem 0.95rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.96);
            color: #4a0d5c !important;
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        [data-theme="dark"] .perfil-hero .materia-chip {
            background: rgba(22, 22, 30, 0.92);
            color: #ede9fe !important;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
        }
    </style>
</head>
<body class="app-private-body">

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<?php
$app_toolbar_mode = 'mobile';
$app_toolbar_title = (string) __('perfil.mobile_title');
require __DIR__ . '/includes/app-private-toolbar.php';
unset($app_toolbar_mode);
?>

<main class="app-main px-3 px-lg-4 pb-3 pb-lg-4 pt-0">
    <?php
    $app_toolbar_mode = 'desktop';
    require __DIR__ . '/includes/app-private-toolbar.php';
    unset($app_toolbar_mode);
    ?>
    <div class="container-fluid" style="max-width: 1100px;">

        <?php if ($flashOk): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
                <?= htmlspecialchars(__('perfil.flash_ok')) ?>
            </div>
        <?php endif; ?>

        <?php if ($flashErr !== '' && isset($msgErro[$flashErr])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                <?= htmlspecialchars($msgErro[$flashErr]) ?>
            </div>
        <?php elseif ($flashErr !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                <?= htmlspecialchars(__('perfil.flash_generic_err')) ?>
            </div>
        <?php endif; ?>

        <div class="perfil-hero mb-4">
            <div class="row align-items-end g-4 position-relative" style="z-index: 1;">
                <div class="col-md-auto text-center text-md-start">
                    <img class="perfil-avatar"
                        src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nome']) ?>&size=224&background=ffffff&color=6a0392"
                        alt="">
                </div>
                <div class="col-md">
                    <h1 class="h3 fw-bold mb-1 text-white"><?= htmlspecialchars($usuario['nome']) ?></h1>
                    <p class="mb-2 opacity-90 small text-white"><?= htmlspecialchars($usuario['email']) ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (empty($materiasUsuario)): ?>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars(__('perfil.no_materias')) ?></span>
                        <?php else: ?>
                            <?php foreach ($materiasUsuario as $mat): ?>
                                <span class="materia-chip"><?= htmlspecialchars($mat['nome'] ?? '') ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="perfil-card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3"><?= htmlspecialchars(__('perfil.summary')) ?></h2>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="perfil-stat d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><?= htmlspecialchars(__('perfil.stat_sims')) ?></span>
                                <span class="fw-bold fs-5 text-primary"><?= (int) $stats['total_simulados'] ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="perfil-stat d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><?= htmlspecialchars(__('perfil.stat_questions')) ?></span>
                                <span class="fw-bold fs-5 text-primary"><?= number_format((int) $stats['questoes_respondidas'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="perfil-stat d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><?= htmlspecialchars(__('perfil.stat_avg')) ?></span>
                                <span class="fw-bold fs-5 text-primary"><?= htmlspecialchars((string) $stats['aproveitamento_geral']) ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="perfil-card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-4"><?= htmlspecialchars(__('perfil.account_data')) ?></h2>
                    <form action="<?= htmlspecialchars(app_url('processa-perfil.php')) ?>" method="post" autocomplete="off">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted"><?= htmlspecialchars(__('perfil.label_name')) ?></label>
                                <input type="text" class="form-control form-control-lg" name="nome" required
                                    value="<?= htmlspecialchars($usuario['nome']) ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted"><?= htmlspecialchars(__('perfil.label_email')) ?></label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" readonly disabled>
                                <div class="form-text"><?= htmlspecialchars(__('perfil.email_help')) ?></div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <h3 class="h6 fw-bold mb-3"><?= htmlspecialchars(__('perfil.security')) ?></h3>
                        <p class="small text-muted"><?= htmlspecialchars(__('perfil.security_hint')) ?></p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted"><?= htmlspecialchars(__('perfil.label_cur_pass')) ?></label>
                                <input type="password" class="form-control" name="senha_atual" placeholder="••••••••" autocomplete="current-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted"><?= htmlspecialchars(__('perfil.label_new_pass')) ?></label>
                                <input type="password" class="form-control" name="nova_senha" placeholder="<?= htmlspecialchars(__('perfil.placeholder_new')) ?>" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="perfil-form-actions">
                            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-end align-items-center">
                                <a href="<?= htmlspecialchars(app_url('dashboard.php')) ?>"
                                    class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                                    <span class="material-icons" style="font-size: 1.15rem;" aria-hidden="true">arrow_back</span>
                                    <?= htmlspecialchars(__('perfil.back')) ?>
                                </a>
                                <button type="submit"
                                    class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <span class="material-icons" style="font-size: 1.15rem;" aria-hidden="true">save</span>
                                    <?= htmlspecialchars(__('perfil.save')) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="perfil-card shadow-sm p-4 border-danger border-opacity-25">
                    <h2 class="h6 fw-bold mb-2 text-danger"><?= htmlspecialchars(__('perfil.logout_section')) ?></h2>
                    <p class="small text-muted mb-3"><?= htmlspecialchars(__('perfil.logout_hint')) ?></p>
                    <a href="<?= htmlspecialchars(app_url('logout.php')) ?>" class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <span class="material-icons fs-6">logout</span> <?= htmlspecialchars(__('perfil.logout_btn')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/private-footer-scripts.php'; ?>
</body>
</html>
