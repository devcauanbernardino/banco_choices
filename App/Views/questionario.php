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
$questao       = $viewData['questao'];       // Objeto da classe Question
$indiceAtual   = $viewData['indiceAtual'];   // Número da questão atual (0, 1, 2...)
$totalQuestoes = $viewData['totalQuestoes']; // Quantidade total de questões
$respostas     = $viewData['respostas'];     // Respostas já dadas pelo usuário
$modo          = $viewData['modo'];          // 'estudo' ou 'exame'
$feedback      = $viewData['feedback'];      // Feedback da questão atual (se houver)
$tempoRestante = $viewData['tempoRestante']; // Segundos que faltam (no modo exame)

// Para o Mapa de Questões, precisamos de todas as questões e feedbacks da sessão
$todasQuestoes = (array)($session->get('questoes') ?? []);
$todosFeedbacks = (array)($session->get('feedback') ?? []);
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
            --bs-primary: #6a0392;
            --bs-primary-rgb: 106, 3, 146;
            --bs-secondary: #26a69a;
            --bs-font-sans-serif: "Inter", system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f6f6f8;
        }

        .bg {
            background-color: var(--bs-primary);
            color: #fff;
        }

        .option-card:not(.disabled):hover {
            border-color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), .05);
            cursor: pointer;
        }

        .question-map button {
            width: 40px;
            height: 38px;
            font-size: 12px;
            font-weight: bold;
        }

        .question-map-current {
            background-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        .btn-go {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-back {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-back:hover {
            background-color: var(--bs-primary);
            color: white;
        }

        .btn-go:hover {
            background-color: var(--bs-primary);
            color: white;
        }

        .modo-estudo .radio-custom input {
            display: none;
        }

        /* Se quiser que no modo exame ele apareça normalmente */
        .modo-exame .radio-custom input {
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top">
        <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
        <h5 class="mb-0 fw-bold">Simulado: <?= ucfirst(htmlspecialchars((string)$session->get('materia'))) ?></h5>
        
        <?php if ($modo === 'estudo'): ?>
            <div class="d-flex gap-3">
                <span class="badge bg">Modo Estudo</span>
            </div>
        <?php endif; ?>
        
         <!-- Mostra o Timer apenas se estiver no modo exame -->
        <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
            <div class="text-end">
                <small class="text-muted d-block">Tempo restante</small>
                <strong class="text-primary fs-5 d-flex align-items-center gap-1">
                    <span class="material-icons fs-6">timer</span>
                    <span id="timer">00:00:00</span>
                </strong>
            </div>
        <?php endif; ?>
    </header>

    <main class="container my-5">
        <div class="row g-4">

            <!-- ================= QUESTÃO ================= -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <span class="text-muted fw-bold">QUESTÃO <?= $indiceAtual + 1 ?> DE <?= $totalQuestoes ?></span>
                    </div>

                    <!-- FORMULÁRIO: Envia a resposta para o ProcessaController toda vez que o rádio muda -->
                    <form id="formResposta" method="post" action="../Controllers/ProcessaController.php">
                        <div class="card-body p-4">
                            <!-- Título da Pergunta -->
                            <h5 class="fw-bold mb-4">
                                <?= htmlspecialchars($questao->getData()['pergunta'] ?? $questao->getData()['texto'] ?? 'Questão sem texto') ?>
                            </h5>

                            <div class="vstack gap-3">
                                <?php
                                // Percorre as opções (A, B, C...) da questão
                                $opcoes = (array)($questao->getData()['opcoes'] ?? []);
                                foreach ($opcoes as $opcao):
                                    $letra = $opcao['letra'];
                                    $texto = $opcao['texto'];
                                    $respondida = isset($respostas[$indiceAtual]) && $respostas[$indiceAtual] === $letra;
                                    
                                    $modoClasse = ($modo === 'exame') ? 'modo-exame' : 'modo-estudo';
                                    $classe = 'border rounded p-3 d-flex gap-3 option-card';
                                    
                                    // Lógica de cores no modo estudo após responder
                                    if ($modo === 'estudo' && $feedback) {
                                        if ($letra === $feedback['resposta_correta']) {
                                            $classe .= ' border-success bg-success bg-opacity-10'; // Verde se for a correta
                                        } elseif ($letra === $feedback['resposta_usuario']) {
                                            $classe .= ' border-danger bg-danger bg-opacity-10'; // Vermelho se o usuário errou
                                        }
                                        $classe .= ' disabled'; // Desabilita cliques após responder
                                    }
                                ?>
                                    <label class="<?= $classe ?> radio-custom">
                                        <input type="radio" name="resposta" value="<?= $letra ?>"
                                            class="form-check-input mt-1" 
                                            <?= $respondida ? 'checked' : '' ?> 
                                            <?= $feedback ? 'disabled' : '' ?> 
                                            onchange="this.form.submit()"> <!-- Envia o form automaticamente ao clicar -->
                                        <strong><?= $letra ?>)</strong>
                                        <span><?= htmlspecialchars($texto) ?></span>
                                    </label>
                                <?php endforeach; ?>

                                <!-- ALERTA DE FEEDBACK: Aparece apenas após responder no modo estudo -->
                                <?php if ($modo === 'estudo' && $feedback): ?>
                                    <div class="alert mt-4 <?= $feedback['acertou'] ? 'alert-success' : 'alert-danger' ?> border-0 shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="material-icons">
                                                <?= $feedback['acertou'] ? 'check_circle' : 'cancel' ?>
                                            </span>
                                            <strong>
                                                <?= $feedback['acertou'] ? 'Resposta correta!' : 'Resposta incorreta' ?>
                                            </strong>
                                        </div>
                                        <hr>
                                        <p class="mb-0 small">
                                            <strong>Explicação:</strong><br>
                                            <?= htmlspecialchars($feedback['feedback']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <div class="card-footer bg-white d-flex justify-content-between py-3">
                        <form action="../Controllers/ProcessaController.php" method="post">
                            <button name="voltar" value="1" class="btn btn-back d-flex gap-2 align-items-center" <?= $indiceAtual == 0 ? 'disabled' : '' ?>>
                                <span class="material-icons">chevron_left</span> Anterior
                            </button>
                        </form>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <button class="btn btn-go d-flex gap-2 align-items-center" name="avancar" value="1">
                                <?= ($indiceAtual + 1 === $totalQuestoes) ? 'Finalizar' : 'Próxima' ?> 
                                <span class="material-icons">chevron_right</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ================= MAPA DE QUESTÕES ================= -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top:100px">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <span class="material-icons text-primary">grid_view</span>
                            MAPA DE QUESTÕES
                        </h6>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <div class="d-flex flex-wrap gap-2 question-map">
                                <?php foreach ($todasQuestoes as $i => $q):
                                    $classeBotao = 'btn-light text-muted';
                                    
                                    if ($i == $indiceAtual) {
                                        $classeBotao = 'question-map-current';
                                    } elseif (isset($todosFeedbacks[$i])) {
                                        $classeBotao = $todosFeedbacks[$i]['acertou'] ? 'btn-success' : 'btn-danger';
                                    } elseif (isset($respostas[$i])) {
                                        $classeBotao = 'btn-primary'; // Respondida mas sem feedback (modo exame)
                                    }
                                ?>
                                    <button name="ir" value="<?= $i ?>" class="btn shadow-sm <?= $classeBotao ?>">
                                        <?= $i + 1 ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </form>
                        
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-column gap-2 small text-muted">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success" style="width:12px; height:12px; padding:0;">&nbsp;</span> Correta
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger" style="width:12px; height:12px; padding:0;">&nbsp;</span> Incorreta
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary" style="width:12px; height:12px; padding:0;">&nbsp;</span> Respondida
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
        <script>
            let tempoRestante = <?= (int)$tempoRestante ?>;

            function formatarTempo(segundos) {
                const h = String(Math.floor(segundos / 3600)).padStart(2, '0');
                const m = String(Math.floor((segundos % 3600) / 60)).padStart(2, '0');
                const s = String(segundos % 60).padStart(2, '0');
                return `${h}:${m}:${s}`;
            }

            function atualizarTimer() {
                if (tempoRestante <= 0) {
                    window.location.href = "../Views/resultado.php";
                    return;
                }

                const timerElement = document.getElementById('timer');
                if (timerElement) {
                    timerElement.textContent = formatarTempo(tempoRestante);
                    
                    // Alerta visual quando faltar menos de 5 minutos
                    if (tempoRestante < 300) {
                        timerElement.parentElement.classList.remove('text-primary');
                        timerElement.parentElement.classList.add('text-danger');
                    }
                }
                tempoRestante--;
            }

            atualizarTimer();
            setInterval(atualizarTimer, 1000);
        </script>
    <?php endif; ?>
</body>
</html>
