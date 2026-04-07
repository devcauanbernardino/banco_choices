<?php
/**
 * ARQUIVO: simulados.php
 * OBJETIVO: Listar o histórico completo de simulados do usuário com filtros.
 */

session_start();

// 1. Carregamento de dependências
require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';

// 2. Verificação de Sessão
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$materiasFiltro = array_values(array_filter(array_map(static function ($m) {
    return $m['nome'] ?? '';
}, $_SESSION['usuario']['materias'] ?? [])));

// 3. Inicialização do Controlador
$objConexao = new Conexao();
$db = $objConexao->conectar();
$dashboard = new DashboardController($db, $_SESSION['usuario']['id']);

// 4. Captura de Filtros (via GET)
$filtroMateria = $_GET['materia'] ?? '';
$filtroStatus = $_GET['status'] ?? '';

// 5. Busca de Dados
$historico = $dashboard->getHistoricoCompleto($filtroMateria, $filtroStatus);
$stats = $dashboard->getStats();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Simulados | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>

    <!-- Bootstrap & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/sidebar.css')) ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6a0392;
            --bg-body: #f6f6f8;
            --card-radius: 16px;
        }

        body.app-private-body {
            font-family: 'Segoe UI', sans-serif;
        }

        /* Cabeçalho da Página */
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: var(--card-radius);
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        /* Filtros */
        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--card-radius);
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .form-select, .form-control {
            border-radius: 10px;
            border: 1px solid #eee;
            padding: 0.6rem 1rem;
        }

        /* Tabela de Histórico */
        .history-card {
            background: white;
            border-radius: var(--card-radius);
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .table thead {
            background-color: #fcfcfc;
            border-bottom: 2px solid #f6f6f8;
        }

        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            padding: 1.25rem;
            font-weight: 700;
        }

        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            color: #444;
            font-size: 0.9rem;
        }

        /* Badges de Status */
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-success { background-color: #e8f5e9; color: #2e7d32; }
        .badge-danger { background-color: #ffebee; color: #c62828; }

        /* Botão de Ação */
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: 1px solid #eee;
            color: #666;
            text-decoration: none;
        }

        .btn-action:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .simulados-main {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                padding-top: 1rem !important;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch !important;
                gap: 1rem;
                padding: 1.25rem !important;
                margin-bottom: 1.25rem !important;
            }

            .page-header .btn {
                width: 100%;
            }

            .filter-card {
                padding: 1.1rem;
                margin-bottom: 1.25rem;
            }

            .empty-state {
                padding: 2rem 1rem;
            }
        }

    </style>
</head>
<body class="app-private-body">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <header class="app-mobile-topbar d-lg-none justify-content-center">
        <span class="fw-bold">Meus simulados</span>
    </header>

    <main class="app-main p-4 simulados-main">
        
        <!-- Cabeçalho Dinâmico -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Meus Simulados</h3>
                <p class="text-muted mb-0">Você já realizou <strong><?= $stats['total_simulados'] ?></strong> simulados no total.</p>
            </div>
            <a href="<?= htmlspecialchars(app_url('bancoperguntas.php')) ?>" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm px-4 rounded-pill">
                Novo Simulado
            </a>
        </div>

        <!-- Barra de Filtros -->
        <div class="filter-card">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filtrar por Matéria</label>
                    <select name="materia" class="form-select">
                        <option value="">Todas as matérias</option>
                        <?php foreach ($materiasFiltro as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $filtroMateria === $m ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($m)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="aprovado" <?= $filtroStatus === 'aprovado' ? 'selected' : '' ?>>Aprovado (>= 70%)</option>
                        <option value="reprovado" <?= $filtroStatus === 'reprovado' ? 'selected' : '' ?>>Reprovado (< 70%)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100 rounded-3">
                        <span class="material-icons align-middle fs-5 me-1">filter_alt</span> Filtrar
                    </button>
                </div>
                <?php if (!empty($filtroMateria) || !empty($filtroStatus)): ?>
                <div class="col-md-2">
                    <a href="<?= htmlspecialchars(app_url('simulados.php')) ?>" class="btn btn-link text-muted w-100 text-decoration-none small">Limpar Filtros</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Histórico -->
        <div class="history-card shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Data e Hora</th>
                            <th>Matéria</th>
                            <th>Desempenho</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historico)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <span class="material-icons fs-1 text-muted mb-3">history_toggle_off</span>
                                    <h5 class="text-dark">Nenhum simulado encontrado</h5>
                                    <p class="text-muted">Tente ajustar seus filtros ou inicie um novo desafio.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historico as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?= $item['data'] ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark p-2 px-3 rounded-pill border">
                                            <?= $item['materia'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold"><?= $item['porcentagem'] ?></span>
                                            <small class="text-muted">(<?= $item['pontuacao'] ?>)</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= $item['classe'] ?>">
                                            <?= $item['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= htmlspecialchars(app_url('resultado.php?id=' . (int) $item['id'])) ?>" class="btn-action" title="Ver Revisão">
                                            <span class="material-icons fs-5">visibility</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php require_once __DIR__ . '/includes/private-footer-scripts.php'; ?>
</body>
</html>
