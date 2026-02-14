<?php
session_start();
require_once __DIR__ . '/auth/AuthController.php';
require_once __DIR__ . '/../Controllers/QuestionarioController.php'; 
require_once __DIR__ . '/../../config/conexao.php';

//Puxa o controlador do Dashboard
require_once __DIR__ . '/../Controllers/DashboardController.php';

// Inicializa a sessão para pegar os dados do usuário e do simulado
$session = new SimulationSession();
$usuario = $_SESSION['usuario'] ?? ['nome' => 'Doutor'];

// 1. Instancia a conexão do seu jeito
$objConexao = new Conexao();
$db = $objConexao->conectar();

// 3. Instancia as classes
$session = new SimulationSession();
$dashboard = new DashboardController($db, $_SESSION['usuario']['id']);

// 3. Puxa os dados reais
$stats = $dashboard->getStats();
$recentes = $dashboard->getRecentSimulados();

$usuario = $_SESSION['usuario'];

// echo '<pre>';
// print_r($usuario);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<div class="content">
    <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top">
        <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
        <h5 class="mb-0 fw-bold">Dashboard</h5>
        <div class="d-flex gap-3">
            <span class="material-icons text-secondary">notifications</span>
            <span class="material-icons text-secondary">help_outline</span>
        </div>
    </header>

    <main class="p-4">
        <!-- Boas-vindas dinâmico -->
        <div class="mb-4">
            <h2 class="fw-bold">Olá, Dr. <?= htmlspecialchars($usuario['nome']) ?></h2>
            <p class="text-muted">Bem-vindo de volta ao seu painel de estudos.</p>
        </div>

        <!-- Estatísticas Dinâmicas -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box bg-success-subtle p-2 rounded">
                                <span class="material-icons text-success">quiz</span>
                            </div>
                        </div>
                        <small class="text-muted">Questões respondidas</small>
                        <h4 class="fw-bold"><?= number_format($stats['questoes_respondidas'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box bg-primary-subtle p-2 rounded">
                                <span class="material-icons text-primary">analytics</span>
                            </div>
                        </div>
                        <small class="text-muted">Aproveitamento</small>
                        <h4 class="fw-bold"><?= $stats['aproveitamento_geral'] ?>%</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box bg-warning-subtle p-2 rounded">
                                <span class="material-icons text-warning">local_fire_department</span>
                            </div>
                        </div>
                        <small class="text-muted">Sequência</small>
                        <h4 class="fw-bold"><?= $stats['sequencia_dias'] ?> dias</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box bg-info-subtle p-2 rounded">
                                <span class="material-icons text-info">military_tech</span>
                            </div>
                        </div>
                        <small class="text-muted">Pontuação</small>
                        <h4 class="fw-bold"><?= number_format($stats['pontuacao_total'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Simulados Recentes Dinâmica -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white fw-bold py-3">
                Simulados Recentes
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Pontuação</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentes as $sim): ?>
                        <tr>
                            <td><?= $sim['data'] ?></td>
                            <td><?= htmlspecialchars($sim['categoria']) ?></td>
                            <td class="fw-bold"><?= $sim['pontuacao'] ?></td>
                            <td><span class="badge bg-<?= $sim['classe'] ?>"><?= $sim['status'] ?></span></td>
                            <td class="text-end">
                                <a href="resultado.php" class="btn btn-sm btn-outline-primary">
                                    <span class="material-icons fs-6">visibility</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chamada para Ação -->
        <div class="card bg-primary text-white shadow-lg border-0">
            <div class="card-body text-center py-5">
                <h3 class="fw-bold">Pronto para um novo desafio?</h3>
                <p class="opacity-75">Inicie um simulado personalizado agora mesmo.</p>
                <a href="bancoperguntas.php" class="btn btn-light btn-lg fw-bold mt-3 px-5">
                    Começar Simulado
                </a>
            </div>
        </div>
    </main>
</div>

</body>
</html>