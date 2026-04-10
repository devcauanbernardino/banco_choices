<?php

/**
 * ARQUIVO: estatisticas.php
 * OBJETIVO: Exibir o painel de evolução e análise detalhada do aluno.
 */

session_start();

// 1. Carregamos as dependências
require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';

// 2. Inicializamos a conexão e o controlador
$objConexao = new Conexao();
$db = $objConexao->conectar();

// Verificamos se o usuário está logado
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
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
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(__('stats.page_title')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/sidebar.css')) ?>">

    <style>
        .card {
            border: none;
            border-radius: 12px;
        }

        /* Só barras de progresso — evita quebrar bg-primary bg-opacity-10 nos ícones KPI */
        .progress-bar.bg-primary {
            background-color: #6a0392 !important;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-icon .material-icons {
            font-size: 26px;
            line-height: 1;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #eee;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* Altura fixa evita loop de resize do Chart.js (maintainAspectRatio: false) */
        .stats-chart-wrap {
            height: 300px;
            position: relative;
            width: 100%;
        }

        .stats-chart-wrap canvas {
            max-height: none;
        }

    </style>
</head>

<body class="app-private-body">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <?php
    $app_toolbar_mode = 'mobile';
    $app_toolbar_title = (string) __('stats.mobile_title');
    require __DIR__ . '/includes/app-private-toolbar.php';
    unset($app_toolbar_mode);
    ?>

    <main class="app-main p-4">
        <?php
        $app_toolbar_mode = 'desktop';
        require __DIR__ . '/includes/app-private-toolbar.php';
        unset($app_toolbar_mode);
        ?>

        <!-- HEADER -->
        <header class="app-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars(__('stats.heading')) ?></h5>
                <small class="text-muted"><?= htmlspecialchars(__('stats.subhead')) ?></small>
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
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars(__('stats.kpi_total')) ?></small>
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
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars(__('stats.kpi_avg')) ?></small>
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
                        <div class="min-w-0 flex-grow-1">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars(__('stats.kpi_best')) ?></small>
                            <h5 class="fw-bold mb-0 text-break"><?= htmlspecialchars($stats['melhor_materia']) ?></h5>
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
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars(__('stats.kpi_sims')) ?></small>
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
                        <h6 class="fw-bold mb-4"><?= htmlspecialchars(__('stats.chart_title')) ?></h6>
                        <div class="stats-chart-wrap">
                            <canvas id="chartEvolucao"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barras por Matéria -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-4"><?= htmlspecialchars(__('stats.bar_title')) ?></h6>

                        <?php if (empty($materias)): ?>
                            <p class="text-muted text-center py-5"><?= htmlspecialchars(__('stats.no_data')) ?></p>
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
                <?= htmlspecialchars(__('stats.week_title')) ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= htmlspecialchars(__('stats.th_week_start')) ?></th>
                            <th><?= htmlspecialchars(__('stats.th_questions')) ?></th>
                            <th><?= htmlspecialchars(__('stats.th_hits')) ?></th>
                            <th><?= htmlspecialchars(__('stats.th_performance')) ?></th>
                            <th class="text-end"><?= htmlspecialchars(__('stats.th_status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historicoSemanal as $sem):
                            $semTotal = (int) ($sem['total'] ?? 0);
                            $aprov = $semTotal > 0
                                ? round(((int) ($sem['acertos'] ?? 0) / $semTotal) * 100, 1)
                                : 0.0;
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
                                        <?= $aprov >= 70 ? htmlspecialchars(__('stats.badge_goal')) : htmlspecialchars(__('stats.badge_evolving')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        (function () {
            var canvas = document.getElementById('chartEvolucao');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            var tickColor = isDark ? '#9ca3af' : '#6b7280';
            var gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            var ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($evolucao['labels']) ?>,
                    datasets: [{
                        label: <?= json_encode(__('stats.chart_dataset')) ?>,
                        data: <?= json_encode($evolucao['data']) ?>,
                        borderColor: '#a855f7',
                        backgroundColor: isDark ? 'rgba(168, 85, 247, 0.12)' : 'rgba(106, 3, 146, 0.08)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#c084fc',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { color: tickColor },
                            grid: isDark
                                ? { color: gridColor, borderDash: [] }
                                : { display: false }
                        },
                        x: {
                            ticks: { color: tickColor },
                            grid: isDark
                                ? { color: gridColor, borderDash: [] }
                                : { display: false }
                        }
                    }
                }
            });
        })();
    </script>

    <?php require_once __DIR__ . '/includes/private-footer-scripts.php'; ?>
</body>

</html>