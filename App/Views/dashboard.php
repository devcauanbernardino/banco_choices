<?php
/**
 * ARQUIVO: dashboard.php
 * OBJETIVO: Painel principal do usuário com resumo de atividades e métricas rápidas.
 */

session_start();

// 1. Carregamos as dependências
require_once __DIR__ . '/auth/AuthController.php';
require_once __DIR__ . '/../Controllers/QuestionarioController.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';
require_once __DIR__ . '/../Models/Usuario.php';

// 2. Inicializamos a conexão e o controlador
$objConexao = new Conexao();
$db = $objConexao->conectar();

// Verificamos se o usuário está logado e pegamos seus dados
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$objUsuario = new Usuario($db);
$dashboard = new DashboardController($db, $usuario['id']);

// 3. Buscamos os dados dinâmicos reais do banco
$stats = $dashboard->getStats();
$recentes = $dashboard->getRecentSimulados();
$materias = $objUsuario->buscarMateriasDoUsuario($usuario['id']);

// echo '<pre>';
// print_r($materias);
// echo '</pre>';

// echo '<hr>';

// echo '<pre>';
// print_r($recentes );
// echo '</pre>';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meu Painel | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sidebar.css">

    <style>
        :root {
            --primary-color: #6a0392;
            --primary-light: rgba(106, 3, 146, 0.1);
            --bg-body: #f6f6f8;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content {
            margin-left: 260px;
            transition: 0.3s;
        }

        /* Header Moderno */
        header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        /* Cards Estilizados */
        .card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .icon-box {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary-soft {
            background-color: var(--primary-light);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #550275;
            border-color: #550275;
        }

        /* Banner de Chamada */
        .cta-banner {
            background: linear-gradient(135deg, #6a0392 0%, #a342cd 100%);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }

        .cta-banner::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        /* Tabela Limpa */
        .table thead th {
            background-color: transparent;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            color: #888;
            border-bottom: 1px solid #eee;
        }

        .table tbody td {
            border-bottom: 1px solid #f8f8f8;
            padding: 15px 10px;
        }

        @media (max-width: 992px) {
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <?php require_once 'includes/sidebar.php'; ?>

    <div class="content">
        <header class="px-4 py-3 d-flex justify-content-between align-items-center sticky-top border-bottom bg-white">
            <button class="btn btn-outline-secondary d-md-none" data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMobile">
                <span class="material-icons">menu</span>
            </button>

            <div class="d-flex align-items-center gap-2">
                <img src="/assets/img/logo-bd-transparente.png" alt="logo" style="width: 35px;">
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="position-relative">
                    <span class="material-icons text-secondary cursor-pointer">notifications</span>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
                <a href="perfil.php">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nome']) ?>&background=6a0392&color=fff"
                        class="rounded-circle" style="width: 35px;" alt="avatar">
                </a>
            </div>
        </header>

        <main class="p-4">
            <!-- Boas-vindas -->
            <div class="mb-4 d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="fw-bold mb-1">Olá, Dr. <?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?> </h2>
                    <p class="text-muted mb-0">Seu progresso está incrível hoje. Continue assim!</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-primary-soft text-primary p-2 px-3 rounded-pill">
                        <i class="material-icons fs-6 align-middle me-1">calendar_today</i>
                        <?= date('d M, Y') ?>
                    </span>
                </div>
            </div>

            <!-- Estatísticas Rápidas -->
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box bg-success-subtle mb-3">
                                <span class="material-icons text-success">task_alt</span>
                            </div>
                            <h3 class="fw-bold mb-0"><?= number_format($stats['questoes_respondidas'], 0, ',', '.') ?>
                            </h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Questões
                                Respondidas</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box bg-primary-soft mb-3">
                                <span class="material-icons text-primary">insights</span>
                            </div>
                            <h3 class="fw-bold mb-0"><?= $stats['aproveitamento_geral'] ?>%</h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Aproveitamento
                                Geral</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box bg-warning-subtle mb-3">
                                <span class="material-icons text-warning">local_fire_department</span>
                            </div>
                            <h3 class="fw-bold mb-0"><?= $stats['sequencia_dias'] ?> dias</h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Sua
                                Sequência</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box bg-info-subtle mb-3">
                                <span class="material-icons text-info">emoji_events</span>
                            </div>
                            <h3 class="fw-bold mb-0"><?= number_format($stats['pontuacao_total'], 0, ',', '.') ?></h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Pontuação
                                Total</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Tabela de Simulados Recentes -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div
                            class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Simulados Recentes</h6>
                            <a href="estatisticas.php" class="text-primary text-decoration-none small fw-bold">Ver
                                tudo</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Matéria</th>
                                        <th>Resultado</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentes)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                Nenhum simulado realizado ainda.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentes as $sim): ?>
                                            <tr>
                                                <td class="text-muted small"><?= $sim['data'] ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($sim['categoria']) ?></td>
                                                <td><span class="fw-bold text-dark"><?= $sim['pontuacao'] ?></span></td>
                                                <td>
                                                    <span class="badge bg-<?= $sim['classe'] ?> rounded-pill px-3">
                                                        <?= $sim['status'] ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card de Ação -->
                <div class="col-lg-4">
                    <div class="card cta-banner text-white h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                            <div class="mb-3">
                                <span class="material-icons" style="font-size: 48px;">psychology</span>
                            </div>
                            <h4 class="fw-bold mb-2">Pronto para o próximo nível?</h4>
                            <p class="opacity-75 small mb-4">Teste seus conhecimentos com um novo simulado personalizado
                                agora mesmo.</p>
                            <a href="bancoperguntas.php"
                                class="btn btn-light btn-lg fw-bold rounded-pill py-3 shadow-sm">
                                Iniciar Simulado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>