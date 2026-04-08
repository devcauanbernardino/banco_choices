<?php
require_once __DIR__ . '/../../config/public_url.php';
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
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(__('quiz.page_title')) ?></title>
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
            --quiz-primary: #6a0392;
            --quiz-primary-mid: #8e24aa;
            --quiz-surface: rgba(255, 255, 255, 0.98);
            --quiz-border: rgba(15, 23, 42, 0.08);
        }

        .quiz-page {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            color: var(--app-text, #1c1c1f);
            background: linear-gradient(165deg, #f0f2f7 0%, #e8eaf0 50%, #f5f3f9 100%);
            overflow-x: hidden;
        }

        [data-theme="dark"] .quiz-page {
            background: linear-gradient(165deg, #0b0b0f 0%, #12121a 100%);
        }

        /* Acima de .sticky-top (z-index 1020 do Bootstrap) para o mapa não cobrir o header */
        .quiz-nav.sticky-top {
            z-index: 1030;
        }

        .quiz-nav {
            background: var(--quiz-surface);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--quiz-border);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }

        [data-theme="dark"] .quiz-nav {
            background: rgba(20, 20, 28, 0.92);
            border-bottom-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 28px rgba(0, 0, 0, 0.45);
        }

        .quiz-nav-inner {
            border-left: 4px solid var(--quiz-primary);
            padding-left: 1rem;
            margin-left: 0;
        }

        .quiz-nav-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 14px rgba(106, 3, 146, 0.15);
            flex-shrink: 0;
        }

        .quiz-nav-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .quiz-nav-eyebrow {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: var(--quiz-primary);
            margin-bottom: 0.15rem;
        }

        [data-theme="dark"] .quiz-nav-eyebrow {
            color: #c4b5fd;
        }

        .quiz-nav-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--app-text, #1a1a1f);
        }

        .quiz-nav-meta {
            font-size: 0.75rem;
            color: var(--app-muted, #6b7280);
        }

        .quiz-theme-pill {
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.85rem 0.4rem 1rem;
            border-radius: 999px;
            background: rgba(106, 3, 146, 0.08);
            border: 1px solid rgba(106, 3, 146, 0.14);
        }

        [data-theme="dark"] .quiz-theme-pill {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .quiz-theme-pill .material-icons {
            font-size: 1.1rem;
            opacity: 0.85;
            color: var(--quiz-primary);
        }

        [data-theme="dark"] .quiz-theme-pill .material-icons {
            color: #d8b4fe;
        }

        .quiz-theme-pill .form-check-input {
            width: 2.25rem;
            height: 1.15rem;
            cursor: pointer;
        }

        .quiz-timer {
            font-variant-numeric: tabular-nums;
            padding: 0.45rem 0.9rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            background: linear-gradient(135deg, rgba(106, 3, 146, 0.12), rgba(142, 36, 170, 0.1));
            color: var(--quiz-primary);
            border: 1px solid rgba(106, 3, 146, 0.2);
        }

        [data-theme="dark"] .quiz-timer {
            background: rgba(167, 139, 250, 0.12);
            color: #e9d5ff;
            border-color: rgba(167, 139, 250, 0.25);
        }

        .quiz-main-card {
            background: var(--quiz-surface);
            border-radius: 20px;
            border: 1px solid var(--quiz-border);
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        [data-theme="dark"] .quiz-main-card {
            background: var(--app-surface, #14141a);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .quiz-q-header {
            background: linear-gradient(180deg, rgba(106, 3, 146, 0.06) 0%, transparent 100%);
            border-bottom: 1px solid var(--quiz-border);
            padding: 1.35rem 1.5rem;
        }

        [data-theme="dark"] .quiz-q-header {
            background: linear-gradient(180deg, rgba(167, 139, 250, 0.08) 0%, transparent 100%);
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .quiz-badge-q {
            background: linear-gradient(135deg, var(--quiz-primary), var(--quiz-primary-mid)) !important;
            color: #fff !important;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .progress.quiz-progress {
            height: 6px;
            border-radius: 999px;
            background: rgba(106, 3, 146, 0.12);
        }

        [data-theme="dark"] .progress.quiz-progress {
            background: rgba(255, 255, 255, 0.08);
        }

        .progress-bar.quiz-progress-bar {
            background: linear-gradient(90deg, var(--quiz-primary), #a855f7);
            border-radius: 999px;
        }

        .quiz-question-text {
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.55;
            color: var(--app-text, #1c1c1f);
            margin-bottom: 1.75rem;
        }

        .options-list {
            margin-top: 0.25rem;
        }

        .option-card {
            border: 1px solid var(--quiz-border);
            border-radius: 14px;
            padding: 1rem 1.15rem;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 0.65rem;
            background: var(--app-surface, #fff);
        }

        [data-theme="dark"] .option-card {
            background: var(--app-surface-2, #1a1a22);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .option-card:hover:not(.disabled) {
            border-color: var(--quiz-primary);
            box-shadow: 0 4px 20px rgba(106, 3, 146, 0.12);
        }

        .option-card.selected {
            border-color: var(--quiz-primary);
            background: rgba(106, 3, 146, 0.06);
        }

        [data-theme="dark"] .option-card.selected {
            background: rgba(167, 139, 250, 0.1);
        }

        .quiz-option-letter {
            width: 2rem;
            height: 2rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            background: rgba(106, 3, 146, 0.1);
            color: var(--quiz-primary);
            flex-shrink: 0;
        }

        .option-card input[type="radio"] {
            width: 1.15rem;
            height: 1.15rem;
            margin-top: 0.35rem;
            accent-color: var(--quiz-primary);
        }

        .option-card.correct {
            border-color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.12) !important;
        }

        .option-card.incorrect {
            border-color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .option-card.disabled {
            cursor: default;
            opacity: 0.95;
        }

        @keyframes quiz-feedback-in {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .quiz-feedback {
            margin-top: 1.5rem;
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            border: 1px solid transparent;
            animation: quiz-feedback-in 0.35s ease-out;
        }

        .quiz-feedback--success {
            background: rgba(25, 135, 84, 0.1);
            border-color: rgba(25, 135, 84, 0.28);
        }

        .quiz-feedback--error {
            background: rgba(220, 53, 69, 0.09);
            border-color: rgba(220, 53, 69, 0.28);
        }

        .quiz-feedback__header {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin-bottom: 0.9rem;
        }

        .quiz-feedback__icon {
            font-size: 1.75rem !important;
            line-height: 1;
            flex-shrink: 0;
        }

        .quiz-feedback--success .quiz-feedback__icon {
            color: #198754;
        }

        .quiz-feedback--error .quiz-feedback__icon {
            color: #dc3545;
        }

        .quiz-feedback__heading {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.35;
            color: #0f5132;
        }

        .quiz-feedback--error .quiz-feedback__heading {
            color: #842029;
        }

        .quiz-feedback__explain {
            padding: 0.9rem 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .quiz-feedback__label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.5rem;
            color: var(--app-muted, #6b7280);
        }

        .quiz-feedback__text {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.55;
            color: var(--app-text, #1c1c1f);
        }

        [data-theme="dark"] .quiz-feedback--success {
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.35);
        }

        [data-theme="dark"] .quiz-feedback--error {
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.35);
        }

        [data-theme="dark"] .quiz-feedback--success .quiz-feedback__heading {
            color: #86efac;
        }

        [data-theme="dark"] .quiz-feedback--error .quiz-feedback__heading {
            color: #fecaca;
        }

        [data-theme="dark"] .quiz-feedback--success .quiz-feedback__icon {
            color: #4ade80;
        }

        [data-theme="dark"] .quiz-feedback--error .quiz-feedback__icon {
            color: #f87171;
        }

        [data-theme="dark"] .quiz-feedback__explain {
            background: rgba(0, 0, 0, 0.28);
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .quiz-feedback__text {
            color: var(--app-text) !important;
        }

        .quiz-card-footer {
            border-top: 1px solid var(--quiz-border);
            padding: 1.15rem 1.5rem;
            background: rgba(248, 250, 252, 0.85);
        }

        [data-theme="dark"] .quiz-card-footer {
            background: rgba(0, 0, 0, 0.2);
            border-top-color: rgba(255, 255, 255, 0.08);
        }

        .map-card {
            background: var(--quiz-surface);
            border-radius: 16px;
            border: 1px solid var(--quiz-border);
            padding: 1.35rem;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
        }

        [data-theme="dark"] .map-card {
            background: var(--app-surface, #14141a);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .map-card.sticky-top {
            z-index: 1;
        }

        .map-card h6 {
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            color: var(--app-muted, #6b7280);
        }

        .question-map-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 8px;
            padding: 4px;
        }

        .map-btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            transition: transform 0.15s ease;
        }

        .map-btn-current {
            background: linear-gradient(135deg, var(--quiz-primary), var(--quiz-primary-mid)) !important;
            color: white !important;
            box-shadow: 0 4px 14px rgba(106, 3, 146, 0.35);
        }

        .map-btn-correct { background-color: #28a745; color: white; }
        .map-btn-incorrect { background-color: #dc3545; color: white; }
        .map-btn-answered { background-color: var(--quiz-primary-mid); color: white; }
        .map-btn-pending {
            background-color: rgba(106, 3, 146, 0.08);
            color: var(--app-muted, #6b7280);
        }

        [data-theme="dark"] .map-btn-pending {
            background: rgba(255, 255, 255, 0.06);
        }

        .map-btn:hover { transform: scale(1.08); z-index: 1; }

        .map-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .map-container::-webkit-scrollbar { width: 6px; }
        .map-container::-webkit-scrollbar-track { background: transparent; }
        .map-container::-webkit-scrollbar-thumb {
            background: rgba(106, 3, 146, 0.25);
            border-radius: 10px;
        }

        .quiz-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .quiz-legend-dot--correct {
            background: #28a745;
        }

        .quiz-legend-dot--incorrect {
            background: #dc3545;
        }

        .quiz-legend-dot--answered {
            background: var(--quiz-primary-mid);
        }

        [data-theme="dark"] .quiz-legend-dot--answered {
            background: #c084fc;
        }

        .quiz-legend-dot--pending {
            background: rgba(106, 3, 146, 0.14);
            border: 1px solid rgba(106, 3, 146, 0.28);
            box-sizing: border-box;
        }

        [data-theme="dark"] .quiz-legend-dot--pending {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.28);
        }

        @media (max-width: 991px) {
            .map-card.sticky-top { position: relative !important; top: 0 !important; }
        }
    </style>
</head>

<body class="app-private-body quiz-body quiz-page <?= $modo === 'exame' ? 'modo-exame' : 'modo-estudo' ?>">
    <nav class="quiz-nav sticky-top mb-4">
        <div class="container-fluid px-3 px-lg-4">
            <div class="quiz-nav-inner d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                    <div class="quiz-nav-logo">
                        <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <div class="quiz-nav-eyebrow"><?= htmlspecialchars(__('quiz.eyebrow')) ?></div>
                        <div class="quiz-nav-title text-truncate"><?= htmlspecialchars($nome_materia) ?></div>
                        <div class="quiz-nav-meta"><?= $modo === 'estudo' ? htmlspecialchars(__('quiz.meta_study')) : htmlspecialchars(__('quiz.meta_exam')) ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 gap-sm-3 ms-auto">
                    <div class="quiz-theme-pill d-flex">
                        <span class="material-icons" aria-hidden="true">dark_mode</span>
                        <span class="d-none d-sm-inline small fw-semibold text-muted me-1"><?= htmlspecialchars(__('quiz.dark')) ?></span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input js-theme-toggle" type="checkbox" id="quizThemeToggle" aria-label="<?= htmlspecialchars(__('quiz.dark_aria')) ?>">
                        </div>
                    </div>
                    <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
                        <div class="quiz-timer d-flex align-items-center gap-2">
                            <span class="material-icons fs-5" aria-hidden="true">timer</span>
                            <span id="timer">00:00:00</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container pb-5 px-3 px-lg-4" style="max-width: 1200px;">
        <div class="row g-4 g-lg-5">

            <!-- ================= QUESTÃO ================= -->
            <div class="col-lg-8">
                <div class="quiz-main-card">
                    <div class="quiz-q-header">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <span class="badge quiz-badge-q px-3 py-2 rounded-pill">
                                <?= htmlspecialchars(sprintf(__('quiz.q_num'), (int) ($indiceAtual + 1), (int) $totalQuestoes)) ?>
                            </span>
                            <span class="small fw-semibold text-muted"><?= htmlspecialchars(sprintf(__('quiz.progress_done'), (string) (int) round($progresso))) ?></span>
                        </div>
                        <div class="progress quiz-progress">
                            <div class="progress-bar quiz-progress-bar" role="progressbar" style="width: <?= $progresso ?>%" aria-valuenow="<?= (int) round($progresso) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- FORMULÁRIO: Envia a resposta para o ProcessaController toda vez que o rádio muda -->
                    <form id="formResposta" method="post" action="<?= htmlspecialchars(app_url('processa.php')) ?>">
                        <div class="card-body p-4 p-md-5">
                            <p class="quiz-question-text">
                                <?= htmlspecialchars($questao->getData()['pergunta'] ?? $questao->getData()['texto'] ?? __('quiz.no_text')) ?>
                            </p>

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
                                            <?= $respondida ? 'checked' : '' ?> <?= $feedback ? 'disabled' : '' ?>
                                            onchange="this.form.submit()">
                                        <span class="quiz-option-letter"><?= htmlspecialchars($letra) ?></span>
                                        <span class="flex-grow-1 pt-1" style="color: var(--app-text, inherit);"><?= htmlspecialchars($texto) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($modo === 'estudo' && $feedback): ?>
                                <div class="quiz-feedback <?= $feedback['acertou'] ? 'quiz-feedback--success' : 'quiz-feedback--error' ?>">
                                    <div class="quiz-feedback__header">
                                        <span class="material-icons quiz-feedback__icon" aria-hidden="true">
                                            <?= $feedback['acertou'] ? 'check_circle' : 'cancel' ?>
                                        </span>
                                        <h2 class="quiz-feedback__heading">
                                            <?= $feedback['acertou'] ? 'Parabéns! Você acertou.' : 'Não foi dessa vez.' ?>
                                        </h2>
                                    </div>
                                    <div class="quiz-feedback__explain">
                                        <span class="quiz-feedback__label">Explicação técnica</span>
                                        <p class="quiz-feedback__text"><?= htmlspecialchars($feedback['feedback']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="quiz-card-footer d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
                        <form action="<?= htmlspecialchars(app_url('processa.php')) ?>" method="post" class="order-2 order-sm-1">
                            <button type="submit" name="voltar" value="1" class="btn btn-outline-primary btn-lg py-3 fw-bold px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" <?= $indiceAtual == 0 ? 'disabled' : '' ?>>
                                <span class="material-icons">arrow_back</span> Anterior
                            </button>
                        </form>

                        <form method="post" action="<?= htmlspecialchars(app_url('processa.php')) ?>" class="order-1 order-sm-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" name="avancar" value="1">
                                <?= ($indiceAtual + 1 === $totalQuestoes) ? 'Finalizar simulado' : 'Próxima questão' ?>
                                <span class="material-icons">arrow_forward</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MAPA DE QUESTOES -->
            <div class="col-lg-4">
                <div class="map-card sticky-top" style="top: 1rem;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="material-icons text-primary" style="font-size: 1.35rem;">grid_view</span>
                        <h6 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 0.08em;">Mapa de questões</h6>
                    </div>

                    <div class="map-container">
                        <form method="post" action="<?= htmlspecialchars(app_url('processa.php')) ?>">
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
                                <span class="quiz-legend-dot quiz-legend-dot--correct" aria-hidden="true"></span> Correta
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="quiz-legend-dot quiz-legend-dot--incorrect" aria-hidden="true"></span> Incorreta
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="quiz-legend-dot quiz-legend-dot--answered" aria-hidden="true"></span> Respondida
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span class="quiz-legend-dot quiz-legend-dot--pending" aria-hidden="true"></span> Pendente
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars(public_asset_url('assets/js/theme.js')) ?>"></script>
    <script>
        // Script do Timer (se aplicável)
        <?php if ($modo === 'exame' && $tempoRestante !== null): ?>
            let seconds = <?= (int) $tempoRestante ?>;
            const timerEl = document.getElementById('timer');
            const timeoutUrl = <?= json_encode(app_url('processa.php?timeout=1')) ?>;

            function updateTimer() {
                if (seconds <= 0) {
                    window.location.href = timeoutUrl;
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
    </script>
</body>

</html>