<?php

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Question.php';
require_once __DIR__ . '/../Models/HistoricoModel.php';
require_once __DIR__ . '/../Controllers/QuestionarioController.php';

$session = new SimulationSession();

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$usuario = $_SESSION['usuario'];
$usuarioId = (int) $usuario['id'];

$historicoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$fromHistorico = false;
$historicoData = null;
$dataResultadoLabel = '';

if ($historicoId > 0) {
    $pdoHist = (new Conexao())->conectar();
    $historicoModel = new HistoricoModel($pdoHist);
    $historicoData = $historicoModel->buscarPorIdUsuario($historicoId, $usuarioId);
    if ($historicoData === null) {
        header('Location: ' . app_url('simulados.php'));
        exit;
    }
    $fromHistorico = true;
}

if (!$fromHistorico) {
    if (!$session->isActive()) {
        header('Location: ' . app_url('dashboard.php'));
        exit;
    }

    $materiaCheck = $session->get('materia');
    if (is_numeric($materiaCheck)) {
        require_once __DIR__ . '/../Models/Usuario.php';
        $pdoCheck = (new Conexao())->conectar();
        $usuarioCheck = new Usuario($pdoCheck);
        if (!$usuarioCheck->usuarioPossuiMateria($usuarioId, (int) $materiaCheck)) {
            $session->clear();
            header('Location: ' . app_url('bancoperguntas.php'));
            exit;
        }
    }
}

if ($fromHistorico) {
    $acertos = (int) $historicoData['acertos'];
    $total = (int) $historicoData['total_questoes'];
    $erros = max(0, $total - $acertos);
    $nome_materia = ucfirst((string) $historicoData['materia_nome']);
    $porcentagem = $total > 0 ? (int) round(($acertos / $total) * 100) : 0;
    $detalhes = [];
    $tempoGasto = '—';
    $inicio = 0;
    $dataResultadoLabel = date('d/m/Y H:i', strtotime((string) $historicoData['data_realizacao']));

    if (!empty($historicoData['detalhes_json'])) {
        try {
            $decoded = json_decode((string) $historicoData['detalhes_json'], true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded) && isset($decoded['detalhes']) && is_array($decoded['detalhes'])) {
                $detalhes = $decoded['detalhes'];
            }
            if (is_array($decoded) && isset($decoded['tempo_segundos']) && is_numeric($decoded['tempo_segundos'])) {
                $s = (int) $decoded['tempo_segundos'];
                $tempoGasto = sprintf('%02d:%02d', (int) floor($s / 60), $s % 60);
            }
        } catch (Throwable $e) {
            error_log('resultado.php: detalhes_json: ' . $e->getMessage());
        }
    }
} else {
    $controller = new QuestionarioController($session);

    $materia = $session->get('materia') ?? 'Geral';
    $nome_materia = $controller->getMateriaNome($materia);
    $questoes = $session->get('questoes') ?? [];
    $respostas = $session->get('respostas') ?? [];
    $inicio = $session->get('inicio') ?? 0;

    $total = count($questoes);

    $acertos = 0;
    $erros = 0;
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
            'numero' => $index + 1,
            'pergunta' => $dadosQuestao['pergunta'] ?? 'Pergunta não encontrada',
            'resposta_usuario' => $respostaUsuario,
            'resposta_correta' => $correta,
            'acertou' => $acertou,
        ];
    }

    if (!$session->get('simulado_salvo') && isset($usuario['id'])) {
        try {
            $conexao = new Conexao();
            $pdo = $conexao->conectar();

            $historico = new HistoricoModel($pdo);

            $tempoSegundos = ($inicio > 0) ? max(0, time() - (int) $inicio) : null;

            $salvo = $historico->salvarResultado(
                $usuario['id'],
                $materia,
                $acertos,
                $total,
                $detalhes,
                $tempoSegundos
            );

            if ($salvo) {
                $session->set('simulado_salvo', true);
            }
        } catch (Throwable $e) {
            error_log('Erro ao salvar resultado: ' . $e->getMessage());
        }
    }

    $porcentagem = $total > 0 ? (int) round(($acertos / $total) * 100) : 0;

    $tempoGasto = '—';
    if ($inicio > 0) {
        $segundos = time() - $inicio;
        $tempoGasto = sprintf('%02d:%02d', (int) floor($segundos / 60), (int) ($segundos % 60));
    }
}

$msgDesempenho = $porcentagem >= 70
    ? 'Excelente desempenho. Continue consolidando esse ritmo.'
    : ($porcentagem >= 50
        ? 'Bom caminho. Revise os tópicos em que errou para subir ainda mais.'
        : 'Use esta revisão para focar nos pontos fracos — cada erro é um guia de estudo.');

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Resultados do simulado | BancoChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/theme-app.css')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --res-primary: #6a0392;
            --res-primary-mid: #8e24aa;
            --res-border: rgba(15, 23, 42, 0.08);
        }

        .result-page {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            color: var(--app-text, #1c1c1f);
            background: linear-gradient(165deg, #f0f2f7 0%, #e8eaf0 50%, #f5f3f9 100%);
            overflow-x: hidden;
        }

        [data-theme="dark"] .result-page {
            background: linear-gradient(165deg, #0b0b0f 0%, #12121a 100%);
        }

        .result-nav.sticky-top {
            z-index: 1030;
        }

        .result-nav {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--res-border);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }

        [data-theme="dark"] .result-nav {
            background: rgba(20, 20, 28, 0.92);
            border-bottom-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 28px rgba(0, 0, 0, 0.45);
        }

        .result-nav-inner {
            border-left: 4px solid var(--res-primary);
            padding-left: 1rem;
        }

        .result-nav-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 14px rgba(106, 3, 146, 0.15);
            flex-shrink: 0;
        }

        .result-nav-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .result-nav-eyebrow {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: var(--res-primary);
        }

        [data-theme="dark"] .result-nav-eyebrow {
            color: #c4b5fd;
        }

        .result-nav-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--app-text, #1a1a1f);
        }

        .result-theme-pill {
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.85rem 0.4rem 1rem;
            border-radius: 999px;
            background: rgba(106, 3, 146, 0.08);
            border: 1px solid rgba(106, 3, 146, 0.14);
        }

        [data-theme="dark"] .result-theme-pill {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .result-theme-pill .form-check-input {
            width: 2.25rem;
            height: 1.15rem;
            cursor: pointer;
        }

        .result-hero {
            text-align: center;
            margin-bottom: 2rem;
        }

        .result-hero h1 {
            font-size: clamp(1.35rem, 3vw, 1.65rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .result-hero p {
            color: var(--app-muted, #6b7280);
            margin-bottom: 0;
            max-width: 32rem;
            margin-left: auto;
            margin-right: auto;
        }

        .result-score-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            border: 1px solid var(--res-border);
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);
            padding: 2.25rem 1.5rem;
            margin-bottom: 2rem;
        }

        [data-theme="dark"] .result-score-card {
            background: var(--app-surface, #14141a);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .result-donut {
            --p: <?= (int) $porcentagem ?>;
            width: 188px;
            height: 188px;
            border-radius: 50%;
            margin: 0 auto 1.25rem;
            position: relative;
            background: conic-gradient(
                var(--res-primary) calc(var(--p) * 3.6deg),
                rgba(106, 3, 146, 0.14) 0
            );
        }

        [data-theme="dark"] .result-donut {
            background: conic-gradient(
                #a855f7 calc(var(--p) * 3.6deg),
                rgba(167, 139, 250, 0.15) 0
            );
        }

        .result-donut__inner {
            position: absolute;
            inset: 16px;
            border-radius: 50%;
            background: var(--app-surface, #fff);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        [data-theme="dark"] .result-donut__inner {
            background: var(--app-surface-2, #1a1a22);
            box-shadow: none;
        }

        .result-donut__pct {
            font-size: 2.35rem;
            font-weight: 700;
            line-height: 1;
            color: var(--res-primary);
            font-variant-numeric: tabular-nums;
        }

        [data-theme="dark"] .result-donut__pct {
            color: #d8b4fe;
        }

        .result-donut__label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--app-muted, #6b7280);
            margin-top: 0.35rem;
        }

        .result-progress {
            height: 8px;
            border-radius: 999px;
            max-width: 420px;
            margin: 0 auto;
            background: rgba(106, 3, 146, 0.12);
        }

        [data-theme="dark"] .result-progress {
            background: rgba(255, 255, 255, 0.08);
        }

        .result-progress .progress-bar {
            border-radius: 999px;
            background: linear-gradient(90deg, var(--res-primary), var(--res-primary-mid));
        }

        .result-score-msg {
            margin-top: 1.25rem;
            font-size: 0.95rem;
            color: var(--app-muted, #6b7280);
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.5;
        }

        .result-stat {
            height: 100%;
            border-radius: 16px;
            border: 1px solid var(--res-border);
            padding: 1.35rem 1.15rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.85rem;
            background: rgba(255, 255, 255, 0.85);
            transition: box-shadow 0.2s, border-color 0.2s;
        }

        [data-theme="dark"] .result-stat {
            background: var(--app-surface, #14141a);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .result-stat:hover {
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
        }

        [data-theme="dark"] .result-stat:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35);
        }

        .result-stat--success {
            border-top: 4px solid #198754;
        }

        .result-stat--danger {
            border-top: 4px solid #dc3545;
        }

        .result-stat--time {
            border-top: 4px solid var(--res-primary);
        }

        .result-stat__icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .result-stat--success .result-stat__icon {
            background: rgba(25, 135, 84, 0.12);
            color: #198754;
        }

        .result-stat--danger .result-stat__icon {
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
        }

        .result-stat--time .result-stat__icon {
            background: rgba(106, 3, 146, 0.12);
            color: var(--res-primary);
        }

        [data-theme="dark"] .result-stat--time .result-stat__icon {
            background: rgba(167, 139, 250, 0.15);
            color: #d8b4fe;
        }

        .result-stat__body {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.15rem;
        }

        .result-stat__k {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--app-muted, #6b7280);
            margin-bottom: 0;
        }

        .result-stat__v {
            font-size: 1.65rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
            color: var(--app-text, #1c1c1f);
        }

        .result-stat__sub {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--app-muted, #6b7280);
        }

        .result-stat__meta {
            margin-top: 0.2rem;
            line-height: 1.35;
            max-width: 14rem;
        }

        .result-table-wrap {
            border-radius: 16px;
            border: 1px solid var(--res-border);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
        }

        [data-theme="dark"] .result-table-wrap {
            background: var(--app-surface, #14141a);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35);
        }

        .result-table-head {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--res-border);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 700;
            font-size: 0.95rem;
        }

        [data-theme="dark"] .result-table-head {
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .result-table-wrap .table {
            margin-bottom: 0;
            --bs-table-bg: transparent;
            --bs-table-color: var(--app-text);
        }

        .result-table-wrap thead th {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--app-muted, #6b7280);
            border-bottom-color: var(--res-border);
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        [data-theme="dark"] .result-table-wrap thead th {
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .result-table-wrap tbody tr:last-child td {
            border-bottom: 0;
        }

        .result-table-wrap tbody td {
            vertical-align: middle;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            border-color: var(--res-border);
        }

        [data-theme="dark"] .result-table-wrap tbody td {
            border-color: rgba(255, 255, 255, 0.06);
        }

        .result-q-preview {
            max-width: min(420px, 38vw);
        }

        @media (max-width: 767px) {
            .result-q-preview {
                max-width: 220px;
            }
        }

        .result-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35em 0.75em;
        }

        .result-footer {
            border-top: 1px solid var(--res-border);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            margin-top: 3rem;
        }

        [data-theme="dark"] .result-footer {
            background: rgba(15, 15, 20, 0.92);
            border-top-color: rgba(255, 255, 255, 0.08);
        }
    </style>
</head>

<body class="app-private-body result-page">

    <nav class="result-nav sticky-top mb-4">
        <div class="container-fluid px-3 px-lg-4" style="max-width: 1200px;">
            <div class="result-nav-inner d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="result-nav-logo">
                        <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <div class="result-nav-eyebrow">Resultado<?= $fromHistorico ? ' · Histórico' : '' ?></div>
                        <div class="result-nav-title text-truncate"><?= htmlspecialchars($nome_materia) ?></div>
                        <?php if ($dataResultadoLabel !== ''): ?>
                            <div class="small text-muted mt-1">Realizado em <?= htmlspecialchars($dataResultadoLabel) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="result-theme-pill d-flex align-items-center ms-auto">
                    <span class="material-icons" style="font-size: 1.1rem; opacity: 0.85; color: var(--res-primary);" aria-hidden="true">dark_mode</span>
                    <span class="d-none d-sm-inline small fw-semibold text-muted me-1">Escuro</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input js-theme-toggle" type="checkbox" id="resultThemeToggle" aria-label="Modo escuro">
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container px-3 px-lg-4 pb-5" style="max-width: 1200px;">

        <div class="result-hero">
            <h1>Desempenho no simulado</h1>
            <p>Resumo do que você acertou em <strong><?= htmlspecialchars($nome_materia) ?></strong><?= $total > 0 ? ' · ' . (int) $total . ' questões' : '' ?>.</p>
        </div>

        <div class="result-score-card">
            <div class="result-donut" role="img" aria-label="Aproveitamento <?= (int) $porcentagem ?> por cento">
                <div class="result-donut__inner">
                    <span class="result-donut__pct"><?= (int) $porcentagem ?>%</span>
                    <span class="result-donut__label">Aproveitamento</span>
                </div>
            </div>

            <div class="progress result-progress mx-auto">
                <div class="progress-bar" role="progressbar" style="width: <?= (int) $porcentagem ?>%" aria-valuenow="<?= (int) $porcentagem ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <p class="result-score-msg mb-0"><?= htmlspecialchars($msgDesempenho) ?></p>
        </div>

        <div class="row g-3 g-md-4 mb-4 mb-lg-5">

            <div class="col-md-4">
                <div class="result-stat result-stat--success">
                    <div class="result-stat__icon">
                        <span class="material-icons" aria-hidden="true">check_circle</span>
                    </div>
                    <div class="result-stat__body">
                        <div class="result-stat__k">Acertos</div>
                        <div class="result-stat__v"><?= (int) $acertos ?><span class="result-stat__sub"> / <?= (int) $total ?></span></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="result-stat result-stat--danger">
                    <div class="result-stat__icon">
                        <span class="material-icons" aria-hidden="true">cancel</span>
                    </div>
                    <div class="result-stat__body">
                        <div class="result-stat__k">Erros</div>
                        <div class="result-stat__v"><?= (int) $erros ?><span class="result-stat__sub"> / <?= (int) $total ?></span></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="result-stat result-stat--time">
                    <div class="result-stat__icon">
                        <span class="material-icons" aria-hidden="true">schedule</span>
                    </div>
                    <div class="result-stat__body">
                        <div class="result-stat__k">Tempo</div>
                        <div class="result-stat__v"><?= htmlspecialchars($tempoGasto) ?></div>
                        <div class="result-stat__meta small text-muted"><?php
                            if ($fromHistorico) {
                                echo $tempoGasto !== '—' ? 'Tempo registrado neste simulado' : 'Tempo não registrado neste histórico';
                            } elseif ($inicio > 0) {
                                echo 'mm:ss desde o início';
                            } else {
                                echo 'sem registro de início';
                            }
                        ?></div>
                    </div>
                </div>
            </div>

        </div>

        <?php if (!empty($detalhes)): ?>
        <div class="result-table-wrap">
            <div class="result-table-head">
                <span class="material-icons text-primary" style="color: var(--res-primary) !important;" aria-hidden="true">fact_check</span>
                Detalhamento por questão
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 72px;">#</th>
                            <th>Enunciado</th>
                            <th style="width: 130px;">Sua resposta</th>
                            <th style="width: 140px;">Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalhes as $item): ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= sprintf('%02d', (int) $item['numero']) ?></td>
                                <td>
                                    <div class="text-truncate result-q-preview" title="<?= htmlspecialchars($item['pergunta']) ?>">
                                        <?= htmlspecialchars($item['pergunta']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item['resposta_usuario'] !== null && $item['resposta_usuario'] !== ''): ?>
                                        <span class="badge rounded-pill result-badge <?= $item['acertou'] ? 'text-bg-success' : 'text-bg-danger' ?>">
                                            <?= htmlspecialchars((string) $item['resposta_usuario']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary rounded-pill result-badge">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-1 small fw-bold <?= $item['acertou'] ? 'text-success' : 'text-danger' ?>">
                                        <span class="material-icons" style="font-size: 1.1rem;" aria-hidden="true"><?= $item['acertou'] ? 'check_circle' : 'cancel' ?></span>
                                        <?= $item['acertou'] ? 'Correta' : 'Incorreta' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-light border result-table-wrap border-opacity-50 mb-0 d-flex gap-3 align-items-start">
            <span class="material-icons text-muted flex-shrink-0" aria-hidden="true">info</span>
            <div class="small mb-0 text-muted">
                <strong class="text-body d-block mb-1">Detalhe por questão indisponível</strong>
                Este registro foi salvo antes da revisão detalhada. Simulados novos passam a guardar cada questão automaticamente.
            </div>
        </div>
        <?php endif; ?>

    </main>

    <footer class="result-footer">
        <div class="container py-4 px-3" style="max-width: 1200px;">
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-stretch align-items-sm-center gap-3">
                <a href="<?= htmlspecialchars(app_url('dashboard.php')) ?>" class="btn btn-outline-primary btn-lg fw-bold px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                    <span class="material-icons" aria-hidden="true">home</span>
                    Início
                </a>
                <a href="<?= htmlspecialchars(app_url('bancoperguntas.php')) ?>" class="btn btn-primary btn-lg fw-bold px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                    <span class="material-icons" aria-hidden="true">refresh</span>
                    Novo simulado
                </a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars(public_asset_url('assets/js/theme.js')) ?>"></script>
</body>

</html>
