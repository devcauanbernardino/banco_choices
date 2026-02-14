<?php
/**
 * ARQUIVO: estatisticas.php
 * OBJETIVO: Exibir o painel de evolução e análise detalhada do aluno.
 */

session_start();

// 1. Carregamos as dependências
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';

// 2. Inicializamos a conexão e o controlador
$objConexao = new Conexao();
$db = $objConexao->conectar();

// Verificamos se o usuário está logado
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$dashboard = new DashboardController($db, $_SESSION['usuario']['id']);

// 3. Buscamos os dados dinâmicos
$stats          = $dashboard->getStats();
$evolucao       = $dashboard->getEvolucaoGrafico();
$materias       = $dashboard->getDesempenhoPorMateria();
$historicoSemanal = $dashboard->getHistoricoSemanal();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas de Estudo | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <style>
        body { background-color: #f6f6f8; }
        .sidebar-space { margin-left: 260px; } /* Ajuste conforme seu sidebar */
        
        .card { border: none; border-radius: 12px; }
        .text-primary { color: white !important; }
        .bg-primary { background-color: #6a0392 !important; }
        
        .card-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        
        .progress { height: 8px; border-radius: 10px; background-color: #eee; }
        .progress-bar { border-radius: 10px; }

        @media (max-width: 992px) {
            .sidebar-space { margin-left: 0; }
        }
    </style>
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<main class="sidebar-space p-4">
    
    <!-- HEADER -->
    <header class="bg-white shadow-sm rounded-3 px-4 py-3 d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
            <div>
                <h5 class="mb-0 fw-bold">Estatísticas de Evolução</h5>
                <small class="text-muted">Acompanhe seu progresso detalhado</small>
            </div>
        </div>
    </header>

    <!-- KPIs (Métricas Principais) -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="card-icon bg-primary bg-opacity-10 text-primary">
                        <span class="material-icons">quiz</span>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Total Respondidas</small>
                        <h4 class="fw-bold mb-0"><?= number_format($stats['questoes_respondidas'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="card-icon bg-success bg-opacity-10 text-success">
                        <span class="material-icons">trending_up</span>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Média de Acertos</small>
                        <h4 class="fw-bold text-success mb-0"><?= $stats['aproveitamento_geral'] ?>%</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="card-icon bg-warning bg-opacity-10 text-warning">
                        <span class="material-icons">stars</span>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Melhor Matéria</small>
                        <h5 class="fw-bold mb-0 text-truncate" style="max-width: 120px;"><?= htmlspecialchars($stats['melhor_materia']) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="card-icon bg-info bg-opacity-10 text-info">
                        <span class="material-icons">history</span>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Simulados Feitos</small>
                        <h4 class="fw-bold mb-0"><?= $stats['total_simulados'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO E MATÉRIAS -->
    <div class="row g-4 mb-4">
        <!-- Gráfico de Linha -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4">Evolução de Desempenho (%)</h6>
                    <canvas id="chartEvolucao" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Barras por Matéria -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4">Desempenho por Matéria</h6>
                    
                    <?php if (empty($materias)): ?>
                        <p class="text-muted text-center py-5">Nenhum dado disponível.</p>
                    <?php else: ?>
                        <?php foreach (array_slice($materias, 0, 5) as $m): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold text-secondary"><?= ucfirst(htmlspecialchars($m['nome'])) ?></span>
                                <span class="small fw-bold text-primary"><?= $m['porcentagem'] ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: <?= $m['porcentagem'] ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TABELA SEMANAL -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">
            Resumo Semanal
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Início da Semana</th>
                        <th>Questões</th>
                        <th>Acertos</th>
                        <th>Aproveitamento</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historicoSemanal as $sem): 
                        $aprov = round(($sem['acertos'] / $sem['total']) * 100, 1);
                    ?>
                    <tr>
                        <td class="fw-bold"><?= date('d/m/Y', strtotime($sem['inicio_semana'])) ?></td>
                        <td><?= $sem['total'] ?></td>
                        <td><?= $sem['acertos'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="width: 100px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $aprov ?>%"></div>
                                </div>
                                <small class="fw-bold"><?= $aprov ?>%</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-<?= $aprov >= 70 ? 'success' : 'warning' ?> rounded-pill">
                                <?= $aprov >= 70 ? 'Meta Batida' : 'Em Evolução' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    // Configuração do Gráfico de Evolução
    const ctx = document.getElementById('chartEvolucao').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($evolucao['labels']) ?>,
            datasets: [{
                label: 'Aproveitamento (%)',
                data: <?= json_encode($evolucao['data']) ?>,
                borderColor: '#6a0392',
                backgroundColor: 'rgba(106, 3, 146, 0.05)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6a0392',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>
