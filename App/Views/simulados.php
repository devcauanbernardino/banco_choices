<?php
/**
 * ARQUIVO: simulados.php
 * OBJETIVO: Listar o histórico completo de simulados do usuário com filtros.
 */

session_start();

// 1. Carregamento de dependências
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';

// 2. Verificação de Sessão
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$materiaId = (int) ($_GET['materia'] ?? 0);

$idsPermitidos = array_column(
    $_SESSION['usuario']['materias'],
    'id'
);

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
    
    <!-- Bootstrap & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6a0392;
            --bg-body: #f6f6f8;
            --card-radius: 16px;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar-space { margin-left: 260px; }

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

        @media (max-width: 992px) {
            .sidebar-space { margin-left: 0; padding-bottom: 80px; }
        }
    </style>
</head>
<body>

    <?php require_once './includes/sidebar.php'; ?>

    <main class="sidebar-space p-4">
        
        <!-- Cabeçalho Dinâmico -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Meus Simulados</h3>
                <p class="text-muted mb-0">Você já realizou <strong><?= $stats['total_simulados'] ?></strong> simulados no total.</p>
            </div>
            <a href="bancoperguntas.php" class="btn btn-primary px-4 rounded-pill" style="background-color: var(--primary-color); border: none;">
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
                        <?php foreach ($materias as $m): ?>
                            <option value="<?= $m ?>" <?= $filtroMateria === $m ? 'selected' : '' ?>>
                                <?= ucfirst($m) ?>
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
                    <a href="simulados.php" class="btn btn-link text-muted w-100 text-decoration-none small">Limpar Filtros</a>
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
                                        <a href="resultado.php?id=<?= $item['id'] ?>" class="btn-action" title="Ver Revisão">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
