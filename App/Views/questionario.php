<?php require_once __DIR__ . '/../Controllers/QuestionarioController.php';
?>
<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <title>Simulador | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #6a0392;
            --bs-primary-rgb: 26, 35, 126;
            --bs-secondary: #26a69a;
            --bs-font-sans-serif: "Inter", system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f6f6f8;
        }

        .option-card:hover {
            border-color: #6a0392;
            background-color: rgba(106, 3, 146, .05);
            cursor: pointer;
        }

        .question-map button {
            width: 40px;
            height: 38px;
            font-size: 12px;
            font-weight: bold;
        }

        .question-map-color {
            background-color: #6a0392;
            color: #fff;
        }

        .btn-go {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-go:hover {
            background-color: var(--bs-primary);
            color: white;
        }
    </style>
</head>

<body>
    <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top">
        <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
        <h5 class="mb-0 fw-bold">Questionario</h5>
        <?php if ($modo === 'estudo'): ?>
            <div class="d-flex gap-3">
                <span class="material-icons text-secondary">notifications</span>
                <span class="material-icons text-secondary">help_outline</span>
            </div>
        <?php endif; ?>
        <?php if ($modo === 'exame'): ?>
            <div class="text-end">
                <small class="text-muted d-block">Tiempo restante</small>
                <strong class="text-primary fs-5">
                    <span class="material-icons fs-6">timer</span>
                    <span id="timer">01:42:15</span>
                </strong>
            </div>
        <?php endif; ?>

    </header>
    <main class="container my-5">
        <div class="row g-4">

            <!-- ================= QUESTÃO ================= -->
            <div class="col-lg-8">
                <div class="card shadow-sm">

                    <form method="post" action="../Controllers/ProcessaController.php">

                        <input type="hidden" name="indice" value="<?= $indiceAtual ?>">

                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <?= htmlspecialchars($questao['pergunta']) ?>
                            </h5>

                            <div class="vstack gap-3">
                                <?php foreach ($questao['opcoes'] as $opcao):

                                    $letra = $opcao['letra'];

                                    $classe = 'border rounded p-3 d-flex gap-3 option-card';

                                    // se já respondeu e está no modo estudo
                                    if ($modo === 'estudo' && $feedback) {

                                        // alternativa correta
                                        if ($letra === $feedback['resposta_correta']) {
                                            $classe .= ' border-success bg-success bg-opacity-10';
                                        }

                                        // alternativa errada marcada pelo usuário
                                        elseif ($letra === $feedback['resposta_usuario']) {
                                            $classe .= ' border-danger bg-danger bg-opacity-10';
                                        }
                                    }
                                    ?>
                                    <label class="<?= $classe ?>">
                                        <input type="radio" name="resposta" value="<?= $letra ?>"
                                            class="form-check-input mt-1" <?= isset($respostas[$indiceAtual]) && $respostas[$indiceAtual] === $letra ? 'checked' : '' ?>     <?= $feedback ? 'disabled' : '' ?> onchange="this.form.submit()">
                                        <strong><?= $letra ?></strong>
                                        <span><?= htmlspecialchars($opcao['texto']) ?></span>
                                    </label>
                                <?php endforeach; ?>

                                <?php if ($modo === 'estudo' && $feedback): ?>
                                    <div class="alert mt-4 <?= $feedback['acertou'] ? 'alert-success' : 'alert-danger' ?>">
                                        <strong>
                                            <?= $feedback['acertou'] ? '✔ Resposta correta!' : '✖ Resposta incorreta' ?>
                                        </strong>
                                        <hr>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($feedback['feedback']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>


                            </div>
                        </div>


                    </form>
                    <div class="card-footer d-flex justify-content-between">
                        <form action="../Controllers/ProcessaController.php" method="post">
                            <button name="voltar" value="1"
                                class="btn btn-outline-secondary <?= $indiceAtual == 0 ? 'disabled' : '' ?>  d-flex gap-2 align-items-center">
                                <span class="material-icons">chevron_left</span> Anterior
                            </button>
                        </form>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <button class="btn btn-go d-flex gap-2 align-items-center" name="avancar" value="1">
                                Siguiente <span class="material-icons">chevron_right</span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>

            <!-- ================= MAPA ================= -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top:100px">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3">
                            <span class="material-icons text-primary fs-6">grid_view</span>
                            MAPA DE PREGUNTAS
                        </h6>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <div class="d-flex flex-wrap gap-2 question-map">
                                <?php foreach ($questoes as $i => $q):

                                    $classe = 'btn-light';

                                    if ($i == $indiceAtual) {
                                        $classe = 'question-map-color';
                                    } elseif (isset($simulado['feedback'][$i])) {
                                        $classe = $simulado['feedback'][$i]['acertou'] ? 'btn-success' : 'btn-danger';
                                    }
                                    ?>
                                    <button name="ir" value="<?= $i ?>" class="btn <?= $classe ?>">
                                        <?= $i + 1 ?>
                                    </button>
                                <?php endforeach; ?>

                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php if ($modo === 'exame'): ?>
        <script>
            let tempoRestante = <?= $tempoRestante ?>;

            function formatarTempo(segundos) {
                const h = String(Math.floor(segundos / 3600)).padStart(2, '0');
                const m = String(Math.floor((segundos % 3600) / 60)).padStart(2, '0');
                const s = String(segundos % 60).padStart(2, '0');
                return `${h}:${m}:${s}`;
            }

            function atualizarTimer() {
                if (tempoRestante <= 0) {
                    window.location.href = "resultado.php";
                    return;
                }

                document.getElementById('timer').textContent = formatarTempo(tempoRestante);
                tempoRestante--;
            }

            atualizarTimer();
            setInterval(atualizarTimer, 1000);
        </script>
    <?php endif; ?>


</body>

</html>