<?php
require_once __DIR__ . '/../Controllers/QuestionarioController.php';

// Inicialização do controlador e preparação dos dados
$session = new SimulationSession();
$controller = new QuestionarioController($session);

// Valida o estado (redireciona se a sessão expirou ou tempo acabou)
$controller->validateState();

// Obtém os dados formatados para a View
$viewData = $controller->getViewData();

// Extração de variáveis para facilitar o uso no HTML
$nome_materia = $controller->getMateriaNome($session->get('materia') ?? 'Geral');
$questao = $viewData['questao'];       // Objeto da classe Question
$indiceAtual = $viewData['indiceAtual'];   // Número da questão atual (0, 1, 2...)
$totalQuestoes = $viewData['totalQuestoes']; // Quantidade total de questões
$respostas = $viewData['respostas'];     // Respostas já dadas pelo usuário
$modo = $viewData['modo'];          // 'estudo' ou 'exame'
$feedback = $viewData['feedback'];      // Feedback da questão atual (se houver)
$tempoRestante = $viewData['tempoRestante']; // Segundos que faltam (no modo exame)

// Para o Mapa de Questões, precisamos de todas as questões e feedbacks da sessão
$todasQuestoes = (array) ($session->get('questoes') ?? []);
$todosFeedbacks = (array) ($session->get('feedback') ?? []);

$progresso = (($indiceAtual + 1) / $totalQuestoes) * 100;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Simulador | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6a0392;
            --primary-light: #8e24aa;
            --primary-dark: #4a0072;
            --secondary-color: #26a69a;
            --bg-gradient: linear-gradient(-45deg, #6a0392, #4a0072, #2c003e, #1a0026);
            --glass-bg: rgba(255, 255, 255, 0.95);

        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f6f6f8;
            background-size: 400% 400%;
            animation: floatBg 15s ease infinite;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
        }

        .main-card {
            background: var(--glass-bg);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .question-header {
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
            margin-bottom: 0;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 10px;
        }

        .bg-question {
            background-color: var(--primary-light);
            color: white;
        }

        .option-card {
            border: 2px solid #edf2f7;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .option-card:hover:not(.disabled) {
            border-color: var(--primary-color);
            background-color: rgba(106, 3, 146, 0.03);
            transform: translateX(5px);
        }

        .option-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(106, 3, 146, 0.05);
        }

        .option-card input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
        }

        /* Cores de Feedback */
        .option-card.correct {
            border-color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .option-card.incorrect {
            border-color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .option-card.disabled {
            cursor: default;
            opacity: 0.8;
        }

        .btn-custom {
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
         .btn-primary-custom:hover {
            background-color: white;
            border-color: 2px var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-custom {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-custom:hover:not(:disabled) {
            background-color: var(--primary-color);
            color: white;
        }

        .map-card {
            background: var(--glass-bg);
            border-radius: 20px;
            border: none;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .question-map-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 8px;
            padding: 5px;
        }

        .map-btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
        }

        .map-btn-current {
            background-color: var(--primary-color) !important;
            color: white !important;
            box-shadow: 0 0 0 3px rgba(106, 3, 146, 0.2);
        }

        .map-btn-correct {
            background-color: #28a745;
            color: white;
        }

        .map-btn-incorrect {
            background-color: #dc3545;
            color: white;
        }

        .map-btn-answered {
            background-color: var(--primary-light);
            color: white;
        }

        .map-btn-pending {
            background-color: #e9ecef;
            color: #6c757d;
        }

        .map-btn:hover {
            transform: scale(1.1);
            z-index: 1;
        }
        .timer-box {
            background: rgba(106, 3, 146, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .feedback-alert {
            border-radius: 15px;
            border: none;
            padding: 1.5rem;
        }

        /* Custom Scrollbar para o Mapa */
        .map-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .map-container::-webkit-scrollbar {
            width: 5px;
        }

        .map-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .map-container::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        @media (max-width: 991px) {
            .sticky-top {
                position: relative !important;
                top: 0 !important;
            }
        }
    </style>
</head>

<body <?= $modo === 'exame' ? 'modo-exame' : 'modo-estudo' ?>>
    <nav class="navbar navbar-custom sticky-top mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Simulado:
                        <?= htmlspecialchars($nome_materia)?>
                    </h6>
                    <small class="text-muted"><?= $modo === 'estudo' ? 'Modo Estudo' : 'Modo Exame' ?></small>
                </div>
            </div>

            <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
                <div class="timer-box d-flex align-items-center gap-2">
                    <span class="material-icons fs-5">timer</span>
                    <span id="timer">00:00:00</span>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row g-4">

            <!-- ================= QUESTÃO ================= -->
            <div class="col-lg-8">
                <div class="main-card shadow-sm">
                    <div class="question-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-question px-3 py-2 rounded-pill fw-bold">
                                QUESTÃO <?= $indiceAtual + 1 ?> DE <?= $totalQuestoes ?>
                            </span>
                            <span class="text-muted small fw-bold"><?= round($progresso) ?>% Concluído</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?= $progresso ?>%"></div>
                        </div>
                    </div>

                    <!-- FORMULÁRIO: Envia a resposta para o ProcessaController toda vez que o rádio muda -->
                    <form id="formResposta" method="post" action="../Controllers/ProcessaController.php">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="fw-bold mb-4 lh-base">
                                <?= htmlspecialchars($questao->getData()['pergunta'] ?? $questao->getData()['texto'] ?? 'Questão sem texto') ?>
                            </h4>

                            <div class="options-list">
                                <?php
                                $opcoes = (array) ($questao->getData()['opcoes'] ?? []);
                                foreach ($opcoes as $opcao):
                                    $letra = $opcao['letra'];
                                    $texto = $opcao['texto'];
                                    $respondida = isset($respostas[$indiceAtual]) && $respostas[$indiceAtual] === $letra;

                                    $classe = 'option-card';
                                    if ($respondida)
                                        $classe .= ' selected';

                                    if ($modo === 'estudo' && $feedback) {
                                        if ($letra === $feedback['resposta_correta']) {
                                            $classe .= ' correct';
                                        } elseif ($letra === $feedback['resposta_usuario']) {
                                            $classe .= ' incorrect';
                                        }
                                        $classe .= ' disabled';
                                    }
                                    ?>
                                    <label class="<?= $classe ?>">
                                        <input type="radio" name="resposta" value="<?= $letra ?>" class="form-check-input"
                                            <?= $respondida ? 'checked' : '' ?>     <?= $feedback ? 'disabled' : '' ?>
                                            onchange="this.form.submit()">
                                        <div class="d-flex align-items-start gap-2">
                                            <strong><?= $letra ?>)</strong>
                                            <span class="text-dark"><?= htmlspecialchars($texto) ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($modo === 'estudo' && $feedback): ?>
                                <div
                                    class="feedback-alert mt-4 animate__animated animate__fadeInUp <?= $feedback['acertou'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="material-icons fs-3">
                                            <?= $feedback['acertou'] ? 'check_circle' : 'cancel' ?>
                                        </span>
                                        <h5 class="mb-0 fw-bold">
                                            <?= $feedback['acertou'] ? 'Parabéns! Você acertou.' : 'Não foi dessa vez.' ?>
                                        </h5>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-50 rounded-3">
                                        <p class="mb-0">
                                            <strong class="d-block mb-1">Explicação Técnica:</strong>
                                            <?= htmlspecialchars($feedback['feedback']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="card-footer bg-light p-4 d-flex justify-content-between align-items-center">
                        <form action="../Controllers/ProcessaController.php" method="post">
                            <button name="voltar" value="1" class="btn btn-outline-custom btn-custom" <?= $indiceAtual == 0 ? 'disabled' : '' ?>>
                                <span class="material-icons">arrow_back</span> Anterior
                            </button>
                        </form>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <button class="btn btn-primary-custom btn-custom" name="avancar" value="1">
                                <span><?= ($indiceAtual + 1 === $totalQuestoes) ? 'Finalizar Simulado' : 'Próxima Questão' ?></span>
                                <span class="material-icons">arrow_forward</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MAPA DE QUESTOES -->
            <div class="col-lg-4">
                <div class="map-card sticky-top animate__animated animate__fadeInRight" style="top: 100px;">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="material-icons">apps</span>
                        <h6 class="mb-0 fw-bold">MAPA DE QUESTÕES</h6>
                    </div>

                    <div class="map-container">
                        <form method="post" action="../Controllers/ProcessaController.php">
                            <div class="question-map-grid">
                                <?php foreach ($todasQuestoes as $i => $q):
                                    $classeBotao = 'map-btn-pending';
                                    if ($i == $indiceAtual) {
                                        $classeBotao = 'map-btn-current';
                                    } elseif (isset($todosFeedbacks[$i])) {
                                        $classeBotao = $todosFeedbacks[$i]['acertou'] ? 'map-btn-correct' : 'map-btn-incorrect';
                                    } elseif (isset($respostas[$i])) {
                                        $classeBotao = 'map-btn-answered';
                                    }
                                    ?>
                                    <button name="ir" value="<?= $i ?>" class="map-btn <?= $classeBotao ?>">
                                        <?= $i + 1 ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-2 small text-muted">
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-success"
                                    style="width:10px; height:10px;"></span> Correta
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-danger"
                                    style="width:10px; height:10px;"></span> Incorreta
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle"
                                    style="width:10px; height:10px; background: var(--primary-light);"></span>
                                Respondida
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-secondary-subtle"
                                    style="width:10px; height:10px;"></span> Pendente
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Script do Timer (se aplicável)
        <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
            let seconds = <?= (int) $tempoRestante ?>;
            const timerEl = document.getElementById('timer');

            function updateTimer() {
                if (seconds <= 0) {
                    window.location.href = '../Controllers/ProcessaController.php?timeout=1';
                    return;
                }
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = seconds % 60;
                timerEl.innerText = [h, m, s].map(v => v.toString().padStart(2, '0')).join(':');
                seconds--;
            }
            setInterval(updateTimer, 1000);
            updateTimer();
        <?php endif; ?>

        // Auto-submit ao selecionar opção (melhor UX)
        document.querySelectorAll('.option-card:not(.disabled)').forEach(card => {
            card.addEventListener('click', function () {
                const radio = this.querySelector('input[type="radio"]');
                if (!radio.disabled) {
                    radio.checked = true;
                    document.getElementById('formResposta').submit();
                }
            });
        });
    </script>
</body>

</html>