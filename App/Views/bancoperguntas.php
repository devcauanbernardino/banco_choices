<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <title>Configurar Simulado | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/banco.css">

    <style>
        body {
            background-color: #f6f6f8;
        }

        .badge-primary {
            background-color: #6a0392;
        }

        .card-header-primary {
            background-color: #6a0392;
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6a0392;
            box-shadow: 0 0 0 0.2rem #6a03928e;
        }

        .btn-primary {
            background-color: #6a0392;
            border-color: #0f49bd;
        }

        .btn-primary:hover {
            background-color: #6a0392;
        }
    </style>
</head>

<body>

<?php require_once './includes/sidebar.php'; ?>

<div class="content">

    <!-- Header -->
    <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top">
        <h5 class="mb-0 fw-bold">Banco de Perguntas</h5>
    </header>

    <main class="p-4">

        <div class="container" style="max-width: 700px;">

            <!-- Card -->
            <div class="card shadow-sm">

                <div class="card-header card-header-primary">
                    <h4 class="mb-0 fw-bold">Configurar Simulado</h4>
                    <small>Personalize sua sessão antes de começar</small>
                </div>

                <div class="card-body">

                    <form action="../Controllers/CriarController.php" method="post">

                        <!-- Matéria -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Matéria</label>
                            <select class="form-select" name="materia" required>
                                <option value="">Selecione</option>
                                <option value="microbiologia">Microbiologia</option>
                            </select>
                            <small class="text-muted">No momento, apenas Microbiologia</small>
                        </div>

                        <!-- Quantidade -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Quantidade de questões</label>
                                <input type="number" class="form-control" name="quantidade" min="10" max="100" value="50">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempo estimado</label>
                                <div class="form-control bg-light">
                                    <span class="material-icons align-middle text-primary">timer</span>
                                    60 minutos
                                </div>
                            </div>
                        </div>

                        <!-- Modo -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Modo de Simulado</label>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="modo" value="estudo" checked id="estudo">
                                <label class="form-check-label">
                                    <strong>Modo Estudo</strong><br>
                                    <small class="text-muted">Feedback imediato</small>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="modo" value="exame" id="exame">
                                <label class="form-check-label">
                                    <strong>Modo Exame</strong><br>
                                    <small class="text-muted">Resultado apenas no final</small>
                                </label>
                            </div>
                        </div>

                        <!-- Alerta -->
                        <div id="alert-exame" class="alert alert-primary d-flex align-items-start gap-2">
                            <span class="material-icons">info</span>
                            <div>
                                <ul class="mb-0 small">
                                    <li>Tempo máximo: <strong>60 minutos</strong></li>
                                    <li>Modo exame não pode ser pausado</li>
                                    <li>Certifique-se de estar em ambiente estável</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Botão -->
                        <div class="d-grid mt-4">
                            <button class="btn btn-primary btn-lg fw-bold">
                                <span class="material-icons align-middle">play_arrow</span>
                                Iniciar Simulado
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            <p class="text-center text-muted mt-4 small">
                © 2026 BancoChoices — Avaliação Médica
            </p>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modoEstudo = document.getElementById('estudo');
    const modoExame = document.getElementById('exame');
    const alertExame = document.getElementById('alert-exame');

    function atualizarAlerta() {
        if (modoExame.checked) {
            alertExame.classList.add('d-block');
            alertExame.classList.remove('d-none');

        } else {
            alertExame.classList.remove('d-block');
            alertExame.classList.add('d-none');
        }
    }

    modoEstudo.addEventListener('change', atualizarAlerta);
    modoExame.addEventListener('change', atualizarAlerta);

    atualizarAlerta()
</script>
</body>
</html>
