<?php
session_start();

require_once __DIR__ . '/auth/AuthController.php';

$usuario = $_SESSION['usuario'];

// echo '<pre>';
// print_r($usuario);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<!-- Content -->
<div class="content">

    <!-- Header -->
    <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top">
        <h5 class="mb-0 fw-bold">Painel Principal</h5>
        <div class="d-flex gap-3">
            <span class="material-icons text-secondary">notifications</span>
            <span class="material-icons text-secondary">help_outline</span>
        </div>
    </header>

    <main class="p-4">

        <!-- Welcome -->
        <div class="mb-4">
            <h2 class="fw-bold">Olá, Dr. <?= $usuario['nome'] ?></h2>
            <p class="text-muted">Você tem 3 simulados pendentes essa semana.</p>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-4">

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box">
                                <span class="material-icons">quiz</span>
                            </div>
                            <span class="badge bg-success-subtle text-success">+12%</span>
                        </div>
                        <small class="text-muted">Questões respondidas</small>
                        <h4 class="fw-bold">1.284</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box">
                                <span class="material-icons">analytics</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">Top 5%</span>
                        </div>
                        <small class="text-muted">Aproveitamento</small>
                        <h4 class="fw-bold">78.5%</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box">
                                <span class="material-icons">local_fire_department</span>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">Ativo</span>
                        </div>
                        <small class="text-muted">Sequência</small>
                        <h4 class="fw-bold">15 dias</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-box">
                                <span class="material-icons">military_tech</span>
                            </div>
                        </div>
                        <small class="text-muted">Pontuação</small>
                        <h4 class="fw-bold">4.520</h4>
                    </div>
                </div>
            </div>

        </div>

        <!-- Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>12/05/2024</td>
                            <td>Ginecologia</td>
                            <td class="fw-bold text-primary">85/100</td>
                            <td><span class="badge bg-success">Aprovado</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">
                                    <span class="material-icons fs-6">visibility</span>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>08/05/2024</td>
                            <td>Cardiologia</td>
                            <td class="fw-bold text-danger">58/100</td>
                            <td><span class="badge bg-danger">Reprovado</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">
                                    <span class="material-icons fs-6">visibility</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="card bg-primary text-white shadow-lg">
            <div class="card-body text-center py-5">
                <h3 class="fw-bold">Pronto para um novo desafio?</h3>
                <p class="opacity-75">Inicie um simulado personalizado agora mesmo.</p>
                <button class="btn btn-light btn-lg fw-bold mt-3">
                    Começar Simulado
                </button>
            </div>
        </div>

    </main>
</div>

</body>
</html>
