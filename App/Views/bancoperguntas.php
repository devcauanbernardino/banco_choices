<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /banco_choices/public/login.php');
    exit;
}

$materiasCompradas = $_SESSION['usuario']['materias'] ?? [];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Configurar Simulado | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <style>
        :root {
            --primary-color: #6a0392;
            --primary-light: #f3e5f5;
            --bg-body: #f6f6f8;
            --card-radius: 16px;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .sidebar-space {
            margin-left: 260px;
        }

        /* Estilo do Card Principal */
        .setup-card {
            border: none;
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .setup-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #a342cd 100%);
            padding: 2.5rem 2rem;
            color: white;
            text-align: center;
        }

        .setup-body {
            padding: 2.5rem;
            background: white;
        }

        /* Estilização de Inputs e Selects */
        .form-label {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 0.6rem;
        }

        .form-select,
        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            background-color: #fcfcfc;
            transition: all 0.2s ease;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(106, 3, 146, 0.1);
            background-color: #fff;
        }

        /* Estilo dos Rádios (Modos) */
        .mode-option {
            border: 2px solid #f0f0f0;
            border-radius: 14px;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: block;
            margin-bottom: 1rem;
            position: relative;
        }

        .mode-option:hover {
            border-color: var(--primary-light);
            background-color: #fafafa;
        }

        .form-check-input:checked+.mode-option {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .form-check-input {
            display: none;
        }

        .mode-title {
            display: block;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .mode-desc {
            font-size: 0.85rem;
            color: #777;
        }

        /* Botão Iniciar */
        .btn-start {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(106, 3, 146, 0.3);
        }

        .btn-start:hover {
            background: #5a027c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 3, 146, 0.4);
            color: white;
        }

        .btn-start:active {
            transform: translateY(0);
        }

        /* Alerta de Exame */
        .exam-warning {
            background-color: #fff4e5;
            border-left: 4px solid #ffa000;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            display: none;
            /* Escondido por padrão */
        }

        @media (max-width: 992px) {
            .sidebar-space {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <?php require_once './includes/sidebar.php'; ?>

    <main class="sidebar-space p-4">

        <div class="container py-4" style="max-width: 750px;">

            <div class="setup-card card shadow-sm">
                <!-- Cabeçalho com Gradiente -->
                <div class="setup-header">
                    <div class="mb-2">
                        <span class="material-icons fs-1">psychology</span>
                    </div>
                    <h2 class="fw-bold mb-1">Configurar Simulado</h2>
                    <p class="opacity-75 mb-0">Prepare sua mente para o próximo desafio</p>
                </div>

                <!-- Corpo do Formulário -->
                <div class="setup-body">
                    <form action="../Controllers/CriarController.php" method="post">

                        <!-- Seleção de Matéria -->
                        <select class="form-select form-select-lg" name="materia" required>
                            <option value="" selected disabled>Selecione uma disciplina...</option>

                            <?php if (empty($materiasCompradas)): ?>
                                <option disabled>Você não possui matérias ativas</option>
                            <?php else: ?>
                                <?php foreach ($materiasCompradas as $materia): ?>
                                    <option value="<?= (int) $materia['id'] ?>">
                                        <?= htmlspecialchars($materia['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>


                        <div class="row g-4 mb-4">
                            <!-- Quantidade de Questões -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Número de Questões</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-3"
                                        style="border-radius: 12px 0 0 12px;">
                                        <span class="material-icons text-muted">format_list_numbered</span>
                                    </span>
                                    <input type="number" class="form-control border-start-0" name="quantidade" min="5"
                                        max="100" value="20" style="border-radius: 0 12px 12px 0;">
                                </div>
                                <div class="form-text">Recomendado: 20 a 50 questões.</div>
                            </div>

                            <!-- Tempo Estimado -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempo Disponível</label>
                                <div class="d-flex align-items-center p-2 px-3 bg-light rounded-3"
                                    style="height: 48px;">
                                    <span class="material-icons text-primary me-2">schedule</span>
                                    <span class="fw-bold text-dark">60 Minutos</span>
                                </div>
                                <div class="form-text">Tempo padrão para o modo exame.</div>
                            </div>
                        </div>

                        <!-- Seleção de Modo (Cards) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Modo de Aplicação</label>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" name="modo" value="estudo" id="radioEstudo" checked>
                                    <label for="radioEstudo" class="mode-option">
                                        <span class="mode-title">Modo Estudo</span>
                                        <span class="mode-desc">Veja a resposta correta e o comentário após cada
                                            questão.</span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" name="modo" value="exame" id="radioExame">
                                    <label for="radioExame" class="mode-option">
                                        <span class="mode-title">Modo Exame</span>
                                        <span class="mode-desc">Simulação real. Resultado e revisão apenas ao
                                            finalizar.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Alerta Dinâmico para Modo Exame -->
                        <div id="examWarning" class="exam-warning mb-4">
                            <div class="d-flex gap-3">
                                <span class="material-icons text-warning">warning</span>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Atenção ao Modo Exame</h6>
                                    <ul class="mb-0 small text-muted ps-3">
                                        <li>O cronômetro não pode ser pausado.</li>
                                        <li>As respostas certas não serão exibidas durante o teste.</li>
                                        <li>Certifique-se de ter uma conexão estável.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de Ação -->
                        <button type="submit" class="btn btn-start w-100 mt-2">
                            <span class="material-icons">rocket_launch</span>
                            INICIAR SIMULADO AGORA
                        </button>

                    </form>
                </div>

                <div class="card-footer bg-light border-0 py-3 text-center">
                    <small class="text-muted">© 2026 Banco de Choices — Preparação Médica de Elite</small>
                </div>
            </div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lógica para mostrar/esconder o alerta de exame
        const radioEstudo = document.getElementById('radioEstudo');
        const radioExame = document.getElementById('radioExame');
        const examWarning = document.getElementById('examWarning');

        function toggleWarning() {
            if (radioExame.checked) {
                examWarning.style.display = 'block';
            } else {
                examWarning.style.display = 'none';
            }
        }

        radioEstudo.addEventListener('change', toggleWarning);
        radioExame.addEventListener('change', toggleWarning);
    </script>

</body>

</html>