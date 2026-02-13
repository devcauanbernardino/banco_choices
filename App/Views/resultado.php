<?php

/**
 * ARQUIVO: resultado.php
 * OBJETIVO: Exibir o desempenho final do usuário no simulado.
 */

// 1. Incluímos as classes necessárias para acessar a sessão
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/QuestionarioController.php';
require_once __DIR__ . '/../Models/HistoricoModel.php';

$session = new SimulationSession();

// 2. SEGURANÇA: Se não houver simulado na sessão, redireciona para o início
if (!$session->isActive()) {
    header('Location: dashboard.php');
    exit;
}

// 3. RECUPERAÇÃO DE DADOS
$materia = (string) ($session->get('materia') ?? 'Geral');
$questoes = (array) ($session->get('questoes') ?? []);
$respostas = (array) ($session->get('respostas') ?? []);
$modo = (string) ($session->get('modo') ?? 'estudo');
$inicio = (int) $session->get('inicio');
$total = count($questoes);

// 4. CÁLCULO DE MÉTRICAS
$acertos = 0;
$erros = 0;
$detalhes = [];

foreach ($questoes as $i => $qData) {
    $questao = new Question($qData);
    $respostaUsuario = $respostas[$i] ?? null;
    $acertou = ($respostaUsuario === $questao->getCorrectAnwser());

    if ($acertou) {
        $acertos++;
    } else {
        $erros++;
    }

    // Guardamos os detalhes para a tabela
    $detalhes[] = [
        'numero' => $i + 1,
        'pergunta' => $qData['pergunta'] ?? $qData['texto'] ?? 'Questão sem título',
        'acertou' => $acertou,
        'resposta_usuario' => $respostaUsuario,
        'resposta_correta' => $questao->getCorrectAnwser()
    ];
}

// 2. Salva no banco se ainda não foi salvo nesta sessão
// 5. SALVAMENTO NO BANCO DE DADOS (Versão Final e Corrigida)
// Usamos uma variável de controle para garantir que salve apenas UMA vez por simulado
if (!isset($_SESSION['simulado_salvo_id']) || $_SESSION['simulado_salvo_id'] !== session_id()) {
    try {
        $objConexao = new Conexao();
        $db = $objConexao->conectar();

        if ($db instanceof PDO) {
            $historico = new HistoricoModel($db);

            // Recupera o ID do usuário logado
            $uid = $_SESSION['usuario']['id'] ?? null;

            if ($uid) {
                $sucesso = $historico->salvarResultado(
                    $uid,
                    $materia,
                    $acertos,
                    $total
                );

                if ($sucesso) {
                    // Marcamos que este simulado específico já foi salvo
                    $_SESSION['simulado_salvo_id'] = session_id();
                }
            }
        }
    } catch (Exception $e) {
        // Silencioso para o usuário, mas registra no log do servidor
        error_log("Erro ao salvar simulado: " . $e->getMessage());
    }
}



// Cálculo da porcentagem de aproveitamento
$porcentagem = ($total > 0) ? round(($acertos / $total) * 100) : 0;

// Cálculo do tempo total gasto (se houver timestamp de início)
$tempoGasto = "N/A";
if ($inicio > 0) {
    $segundosGasto = time() - $inicio;
    $minutos = floor($segundosGasto / 60);
    $segundos = $segundosGasto % 60;
    $tempoGasto = sprintf("%02d:%02d", $minutos, $segundos);
}

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
                    <img src="../assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px;">
                </div>
                <h6 class="fw-bold mb-0 text-primary"><?= ucfirst(htmlspecialchars($materia)) ?></h6>
            </div>
        </div>
    </header>

    <main class="container my-5">

        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase">Resultados do Simulado</h2>
            <p class="text-muted">Veja como foi o seu desempenho em <?= ucfirst(htmlspecialchars($materia)) ?></p>
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