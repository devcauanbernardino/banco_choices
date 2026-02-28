<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Question.php';
require_once __DIR__ . '/../Models/HistoricoModel.php';
require_once __DIR__ . '/../Controllers/QuestionarioController.php';


// ==========================
// 1. INICIA A SESSÃO
// ==========================
$session = new SimulationSession();

if (!$session->isActive()) {
    header('Location: dashboard.php');
    exit;
}

// ==========================
// 2. RECUPERA DADOS DA SESSÃO
// ==========================
$controller = new QuestionarioController($session);

$materia   = $session->get('materia') ?? 'Geral';
$nome_materia = $controller->getMateriaNome($materia);
$questoes  = $session->get('questoes') ?? [];
$respostas = $session->get('respostas') ?? [];
$inicio    = $session->get('inicio') ?? 0;
$usuario   = $_SESSION['usuario'] ?? null;

$total = count($questoes);

// ==========================
// 3. CALCULA RESULTADO
// ==========================
$acertos  = 0;
$erros    = 0;
$detalhes = [];

foreach ($questoes as $index => $dadosQuestao) {

    $questao = new Question($dadosQuestao);
    $respostaUsuario = $respostas[$index] ?? null;
    $correta = $questao->getCorrectAnswer();

    $acertou = ($respostaUsuario === $correta);

    if ($acertou) {
        $acertos++;
    } else {
        $erros++;
    }

    $detalhes[] = [
        'numero'            => $index + 1,
        'pergunta'          => $dadosQuestao['pergunta'] ?? 'Pergunta não encontrada',
        'resposta_usuario'  => $respostaUsuario,
        'resposta_correta'  => $correta,
        'acertou'           => $acertou
    ];
}


// ==========================
// 4. SALVA NO BANCO (1 VEZ)
// ==========================
if (!$session->get('simulado_salvo') && $usuario && isset($usuario['id'])) {

    try {
        $conexao = new Conexao();
        $pdo = $conexao->conectar();

        $historico = new HistoricoModel($pdo);

        $salvo = $historico->salvarResultado(
            $usuario['id'],
            $materia,
            $acertos,
            $total
        );

        if ($salvo) {
            $session->set('simulado_salvo', true);
        }

    } catch (Throwable $e) {
        error_log('Erro ao salvar resultado: ' . $e->getMessage());
    }
}

// ==========================
// 5. MÉTRICAS FINAIS
// ==========================
$porcentagem = $total > 0 ? round(($acertos / $total) * 100) : 0;

$tempoGasto = 'N/A';
if ($inicio > 0) {
    $segundos = time() - $inicio;
    $tempoGasto = sprintf('%02d:%02d', floor($segundos / 60), $segundos % 60);
}

// var_dump([
//     'simulado_salvo' => $session->get('simulado_salvo'),
//     'usuario'        => $session->get('usuario'),
//     'usuario_id'     => $session->get('usuario')['id'] ?? null
// ]);
// exit;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Resultados do Simulado | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #f6f6f8;
        }

        /* Círculo de pontuação dinâmico */
        .score-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 10px solid rgba(106, 3, 146, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            /* Criamos uma variável CSS que recebe o valor do PHP */
            --porcentagem:
                <?= $porcentagem ?>
                %;
        }

        .score-circle::after {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            border: 10px solid #6a0392;
            /* Aqui simulamos o progresso do círculo baseado na porcentagem */
            clip-path: polygon(50% 50%, -50% -50%, var(--porcentagem) -50%, 150% 150%, 50% 50%);
            transform: rotate(0deg);
            display: none;
            /* Oculto por padrão, pode ser melhorado com SVG */
        }

        .text-primary {
            color: #6a0392 !important;
        }

        .bg-primary {
            background-color: #6a0392 !important;
        }

        .btn-primary {
            background-color: #6a0392;
            border-color: #6a0392;
        }

        .btn-primary:hover {
            background-color: #520271;
        }
    </style>
</head>

<body>

    <header class="bg-white border-bottom sticky-top">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="text-white p-2">
                    <img src="/assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
                </div>
                <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($nome_materia) ?></h6>
            </div>
        </div>
    </header>

    <main class="container my-5">

        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase">Resultados do Simulado</h2>
            <p class="text-muted">Veja como foi o seu desempenho em <?= htmlspecialchars($nome_materia) ?></p>
        </div>

        <!-- SCORE PRINCIPAL -->
        <div class="card shadow-sm mb-5 border-0">
            <div class="card-body text-center py-5">
                <div class="score-circle mx-auto mb-3">
                    <div>
                        <div class="fs-1 fw-bold text-primary"><?= $porcentagem ?>%</div>
                        <small class="text-muted text-uppercase fw-bold">Aproveitamento</small>
                    </div>
                </div>

                <div class="progress mb-2 mx-auto" style="height: 10px; max-width: 400px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $porcentagem ?>%"></div>
                </div>

                <p class="text-muted mt-3">
                    <?= $porcentagem >= 70 ? 'Excelente desempenho! Continue assim.' : 'Bom esforço! Revise os pontos onde teve dificuldade.' ?>
                </p>
            </div>
        </div>

        <!-- MÉTRICAS DETALHADAS -->
        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-success fs-2">check_circle</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Acertos</small>
                            <div class="fs-4 fw-bold"><?= $acertos ?> <small class="text-muted">/ <?= $total ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-danger fs-2">cancel</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Erros</small>
                            <div class="fs-4 fw-bold"><?= $erros ?> <small class="text-muted">/ <?= $total ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-primary border-4 h-100">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-primary fs-2">timer</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Tempo Gasto</small>
                            <div class="fs-4 fw-bold"><?= $tempoGasto ?> <small class="text-muted">min</small></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- TABELA DE REVISÃO -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 fw-bold d-flex align-items-center gap-2">
                <span class="material-icons text-primary">list_alt</span>
                Detalhamento por Questão
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Pergunta</th>
                            <th>Sua Resposta</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalhes as $item): ?>
                            <tr>
                                <td class="fw-bold"><?= sprintf("%02d", $item['numero']) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 400px;"
                                        title="<?= htmlspecialchars($item['pergunta']) ?>">
                                        <?= htmlspecialchars($item['pergunta']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $item['acertou'] ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                        <?= $item['resposta_usuario'] ?? 'N/A' ?>
                                    </span>
                                </td>
                                <td class="<?= $item['acertou'] ? 'text-success' : 'text-danger' ?> fw-bold">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="material-icons fs-6">
                                            <?= $item['acertou'] ? 'check_circle' : 'cancel' ?>
                                        </span>
                                        <?= $item['acertou'] ? 'Correta' : 'Incorreta' ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-white border-top mt-5">
        <div class="container py-4 d-flex flex-column flex-md-row justify-content-center gap-3">
            <a href="dashboard.php" class="btn btn-outline-secondary px-4 d-flex align-items-center gap-2">
                <span class="material-icons">home</span>
                Início
            </a>
            <a href="bancoperguntas.php" class="btn btn-primary px-4 d-flex align-items-center gap-2">
                <span class="material-icons">refresh</span>
                Novo Simulado
            </a>
        </div>
    </footer>

</body>

</html>