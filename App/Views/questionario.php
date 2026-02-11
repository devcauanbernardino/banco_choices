<?php require_once __DIR__ . '/../Controllers/QuestionarioController.php';?>
<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <title>Simulador | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #f6f6f8;
        }

        .option-card:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, .05);
            cursor: pointer;
        }

        .question-map button {
            width: 38px;
            height: 38px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <header class="bg-white border-bottom sticky-top">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white p-2 rounded">
                    <span class="material-icons">biotech</span>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-primary">BancoChoices</h6>
                    <small class="text-muted">Examen de Residencia Médica</small>
                </div>
            </div>

            <div class="text-end">
                <small class="text-muted d-block">Tiempo restante</small>
                <strong class="text-primary fs-5">
                    <span class="material-icons fs-6">timer</span> 01:42:15
                </strong>
            </div>
        </div>
    </header>

    <main class="container my-5">
        <div class="row g-4">

            <!-- ================= QUESTION ================= -->
            <div class="col-lg-8">
                <div class="card shadow-sm">

                    <form method="post" action="../Controllers/ProcessaController.php">

                        <input type="hidden" name="indice" value="<?= $indiceAtual ?>">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <span class="badge bg-primary-subtle text-primary">Microbiología</span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <?= htmlspecialchars($questao['pergunta']) ?>
                            </h5>

                            <div class="vstack gap-3">
                                <?php foreach ($questao['opcoes'] as $opcao): ?>
                                    <label class="border rounded p-3 d-flex gap-3 option-card">
                                        <input type="radio" name="resposta" value="<?= $opcao['letra'] ?>"
                                            class="form-check-input mt-1" <?= isset($respostas[$indiceAtual]) && $respostas[$indiceAtual] === $opcao['letra'] ? 'checked' : '' ?>>
                                        <strong><?= $opcao['letra'] ?></strong>
                                        <span><?= htmlspecialchars($opcao['texto']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <button name="acao" value="anterior" class="btn btn-outline-secondary" <?= $indiceAtual == 0 ? 'disabled' : '' ?>>
                                <span class="material-icons">chevron_left</span> Anterior
                            </button>

                            <button name="acao" value="proximo" class="btn btn-primary">
                                Siguiente <span class="material-icons">chevron_right</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- ================= MAPA ================= -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3">
                            <span class="material-icons text-primary fs-6">grid_view</span>
                            MAPA DE PREGUNTAS
                        </h6>

                        <form method="post" action="../Controllers/ProcessaController.php">
                            <div class="d-flex flex-wrap gap-2 question-map mb-4">
                                <?php foreach ($questoes as $i => $q):

                                    $classe = 'btn-light';
                                    if ($i == $indiceAtual)
                                        $classe = 'btn-primary';
                                    elseif (isset($respostas[$i]))
                                        $classe = 'btn-success';
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

</body>

</html>