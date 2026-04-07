<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bootstrap_env.php';
loadProjectEnv();

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/public_url.php';

$conexao = new Conexao();
$pdo = $conexao->conectar();

$materias = $pdo->query('SELECT id, nome FROM materias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedMaterias = $_POST['materias'] ?? [];

    if ($selectedMaterias === []) {
        $error = __('signup.err.min_materias');
    } else {
        $_SESSION['selected_materias'] = array_map('intval', $selectedMaterias);
        header('Location: selecionar-plano.php');
        exit;
    }
}

$selectedMaterias = $_SESSION['selected_materias'] ?? [];

/**
 * Ícone por nombre de materia (didáctico y visual).
 *
 * @return array{icon: string, hint_key: string}
 */
function materia_visual_meta(string $nome): array
{
    $n = function_exists('mb_strtolower') ? mb_strtolower($nome, 'UTF-8') : strtolower($nome);
    if (str_contains($n, 'micro')) {
        return ['icon' => 'bi bi-bug-fill', 'hint_key' => 'signup.materia.hint.micro'];
    }
    if (str_contains($n, 'celular') || str_contains($n, 'biolog')) {
        return ['icon' => 'bi bi-droplet', 'hint_key' => 'signup.materia.hint.bio'];
    }
    if (str_contains($n, 'anatom') || str_contains($n, 'fisio')) {
        return ['icon' => 'bi bi-heart-pulse-fill', 'hint_key' => 'signup.materia.hint.anat'];
    }

    return ['icon' => 'bi bi-journal-medical', 'hint_key' => 'signup.materia.hint.default'];
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars(__('signup.page_title.materias')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/top-bar-brand.css')) ?>" />
    <?php require_once __DIR__ . '/../config/favicon_links.php'; ?>

    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: #6a03928e;
            --accent-purple-lighter: #6a039220;
            --accent-purple-soft: rgba(106, 3, 146, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            overflow-x: hidden;
            min-height: 100vh;
            background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 45%, #460161 100%);
            background-size: 160% 160%;
            animation: floatBg 16s ease-in-out infinite;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 40%, rgba(106, 3, 146, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(0, 33, 71, 0.12) 0%, transparent 42%);
            pointer-events: none;
            z-index: 0;
        }

        @keyframes floatBg {

            0%,
            100% {
                background-position: 0% 40%;
            }

            50% {
                background-position: 100% 60%;
            }
        }

        main {
            position: relative;
            z-index: 1;
        }

        .container-custom {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.75rem 1rem 3rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            opacity: 0.95;
            transition: gap 0.2s ease, opacity 0.2s ease;
        }

        .back-link:hover {
            color: #fff;
            gap: 0.65rem;
            opacity: 1;
        }

        .top-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .header-section {
            text-align: center;
            margin-bottom: 1.75rem;
            color: #fff;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }

        .header-section h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: clamp(1.65rem, 4vw, 2.45rem);
            margin: 0 0 0.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.18);
        }

        .header-section .lead {
            font-size: 1.05rem;
            opacity: 0.92;
            max-width: 36rem;
            margin: 0 auto;
            line-height: 1.55;
        }

        /* Pasos — mismo estilo que selecionar-plano.php */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.22);
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.45rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-purple);
            border-color: #fff;
            box-shadow: 0 0 0 0.28rem var(--accent-purple-lighter);
        }

        .step-label {
            font-size: 0.68rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.72);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .step.active .step-label {
            color: #fff;
        }

        /* Panel didáctico */
        .flow-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 22px 48px rgba(0, 0, 0, 0.14);
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .flow-card-top {
            padding: 1.35rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(0, 33, 71, 0.06);
            background: linear-gradient(135deg, rgba(106, 3, 146, 0.06) 0%, rgba(0, 33, 71, 0.03) 100%);
        }

        .flow-card-top h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--navy-primary);
            margin: 0 0 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .flow-card-top h2 i {
            color: var(--accent-purple);
        }

        .flow-card-top p {
            margin: 0;
            font-size: 0.92rem;
            color: #5b6570;
            line-height: 1.55;
        }

        .how-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .how-step {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .how-step-num {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--accent-purple-soft);
            color: var(--accent-purple);
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .how-step h3 {
            margin: 0 0 0.2rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--navy-primary);
        }

        .how-step p {
            margin: 0;
            font-size: 0.8rem;
            color: #6b7280;
            line-height: 1.45;
        }

        .info-callout {
            margin: 0 1.5rem 1.5rem;
            padding: 1rem 1.1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(0, 33, 71, 0.06), rgba(106, 3, 146, 0.06));
            border-left: 4px solid var(--accent-purple);
            font-size: 0.88rem;
            color: #374151;
            line-height: 1.5;
        }

        .info-callout strong {
            color: var(--navy-primary);
        }

        .info-callout .bi {
            color: var(--accent-purple);
        }

        .form-section {
            padding: 0 1.5rem 1.5rem;
        }

        .section-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: 0.75rem;
        }

        .materias-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
        }

        .materia-card {
            border: 2px solid #e8ecf0;
            border-radius: 16px;
            padding: 1.15rem 1.2rem;
            cursor: pointer;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease, background 0.25s ease;
            background: #fff;
            position: relative;
        }

        .materia-card:hover {
            border-color: rgba(106, 3, 146, 0.45);
            box-shadow: 0 10px 28px rgba(106, 3, 146, 0.12);
            transform: translateY(-2px);
        }

        .materia-card.selected {
            border-color: var(--accent-purple);
            background: linear-gradient(180deg, rgba(106, 3, 146, 0.07) 0%, #fff 55%);
            box-shadow: 0 12px 32px rgba(106, 3, 146, 0.15);
        }

        .materia-card:focus-within {
            outline: 2px solid var(--accent-purple);
            outline-offset: 2px;
        }

        .materia-card input[type="checkbox"] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .materia-card-content {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            min-width: 0;
        }

        .materia-icon-wrap {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            box-shadow: 0 6px 16px rgba(106, 3, 146, 0.25);
        }

        .materia-card.selected .materia-icon-wrap {
            box-shadow: 0 8px 22px rgba(106, 3, 146, 0.35);
        }

        .materia-checkbox {
            width: 22px;
            height: 22px;
            margin-top: 4px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            flex-shrink: 0;
            position: relative;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .materia-card input[type="checkbox"]:checked + .materia-card-content .materia-checkbox {
            background: var(--accent-purple);
            border-color: var(--accent-purple);
        }

        .materia-card input[type="checkbox"]:checked + .materia-card-content .materia-checkbox::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 45%;
            width: 5px;
            height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -50%) rotate(45deg);
        }

        .materia-main {
            flex: 1;
            min-width: 0;
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
        }

        .materia-text {
            flex: 1;
            min-width: 0;
        }

        .materia-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            color: var(--navy-primary);
            margin: 0 0 0.25rem;
            line-height: 1.3;
        }

        .materia-card.selected .materia-name {
            color: var(--accent-purple);
        }

        .materia-hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.4;
        }

        .form-footer {
            padding: 1.25rem 1.5rem 1.5rem;
            border-top: 1px solid rgba(0, 33, 71, 0.06);
            background: #fafbfc;
        }

        .selected-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .selected-count {
            font-size: 0.92rem;
            color: #5b6570;
        }

        .selected-count strong {
            color: var(--accent-purple);
            font-weight: 800;
        }

        .next-hint {
            font-size: 0.82rem;
            color: #9ca3af;
            max-width: 22rem;
            line-height: 1.45;
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 0.9rem 1.1rem;
            margin: 0 1.5rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1.5rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--accent-purple);
            opacity: 0.5;
            margin-bottom: 0.75rem;
        }

        @media (max-width: 900px) {
            .how-steps {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .step-indicator::before {
                display: none;
            }

            .step-indicator {
                flex-wrap: wrap;
                gap: 0.75rem;
                justify-content: center;
            }

            .step {
                flex: 0 0 auto;
                min-width: 72px;
            }
        }
    </style>
</head>

<body>
    <main class="container-custom">
        <div class="top-bar animate__animated animate__fadeIn">
            <a href="index.php" class="brand-mark" aria-label="Banco de Choices — inicio">
                <span class="brand-mark-img-wrap">
                    <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="Banco de Choices" width="180" height="40" />
                </span>
            </a>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto justify-content-end">
                <div class="navbar-actions navbar-actions--landing flex-shrink-0">
                    <div class="navbar-actions__inner">
                        <?php
                        $bc_lang_menu_landing = true;
                        $bc_lang_selector_btn_class = 'btn btn-navbar-lang dropdown-toggle d-inline-flex align-items-center gap-2';
                        require_once __DIR__ . '/../App/Views/includes/language-selector.php';
                        unset($bc_lang_menu_landing, $bc_lang_selector_btn_class);
                        ?>
                    </div>
                </div>
                <a href="index.php" class="back-link mb-0">
                    <i class="bi bi-arrow-left-short" style="font-size:1.35rem;line-height:0"></i>
                    <?= htmlspecialchars(__('signup.back_home')) ?>
                </a>
            </div>
        </div>

        <header class="header-section animate__animated animate__fadeInDown">
            <span class="header-badge"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars(__('signup.step_badge')) ?></span>
            <h1><?= htmlspecialchars(__('signup.materias.h1')) ?></h1>
            <p class="lead">
                <?= htmlspecialchars(__('signup.materias.lead_before')) ?>
                <strong><?= htmlspecialchars(__('signup.materias.lead_strong')) ?></strong>
                <?= htmlspecialchars(__('signup.materias.lead_after')) ?>
            </p>
        </header>

        <div class="step-indicator animate__animated animate__fadeIn" aria-hidden="true">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label"><?= htmlspecialchars(__('signup.step.materias')) ?></div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label"><?= htmlspecialchars(__('signup.step.plan')) ?></div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label"><?= htmlspecialchars(__('signup.step.pago')) ?></div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label"><?= htmlspecialchars(__('signup.step.confirmacion')) ?></div>
            </div>
        </div>

        <div class="flow-card animate__animated animate__fadeInUp">
            <div class="flow-card-top">
                <h2><i class="bi bi-lightbulb"></i> <?= htmlspecialchars(__('signup.materias.what_title')) ?></h2>
                <p><?= htmlspecialchars(__('signup.materias.what_p')) ?></p>
            </div>

            <div class="how-steps">
                <div class="how-step">
                    <span class="how-step-num">1</span>
                    <div>
                        <h3><?= htmlspecialchars(__('signup.how1.title')) ?></h3>
                        <p><?= htmlspecialchars(__('signup.how1.p')) ?></p>
                    </div>
                </div>
                <div class="how-step">
                    <span class="how-step-num">2</span>
                    <div>
                        <h3><?= htmlspecialchars(__('signup.how2.title')) ?></h3>
                        <p><?= htmlspecialchars(__('signup.how2.p')) ?></p>
                    </div>
                </div>
                <div class="how-step">
                    <span class="how-step-num">3</span>
                    <div>
                        <h3><?= htmlspecialchars(__('signup.how3.title')) ?></h3>
                        <p><?= htmlspecialchars(__('signup.how3.p')) ?></p>
                    </div>
                </div>
            </div>

            <div class="info-callout">
                <i class="bi bi-info-circle-fill me-1" aria-hidden="true"></i>
                <?= htmlspecialchars(__('signup.materias.tip')) ?>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert-custom" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($materias === []): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p><strong><?= htmlspecialchars(__('signup.empty.title')) ?></strong></p>
                    <p class="small mb-0"><?= htmlspecialchars(__('signup.empty.p')) ?></p>
                    <a href="index.php" class="btn btn-outline-primary btn-lg py-3 fw-bold shadow-sm mt-3 rounded-pill px-4"><?= htmlspecialchars(__('signup.empty.btn')) ?></a>
                </div>
            <?php else: ?>
                <form method="post" id="materiasForm" novalidate>
                    <div class="form-section">
                        <div class="section-label"><?= htmlspecialchars(__('signup.section.available')) ?></div>
                        <div class="materias-container">
                            <?php foreach ($materias as $materia): ?>
                                <?php
                                $isSelected = in_array((int) $materia['id'], $selectedMaterias, true);
                                $meta = materia_visual_meta((string) $materia['nome']);
                                ?>
                                <label class="materia-card<?= $isSelected ? ' selected' : '' ?>">
                                    <input type="checkbox" name="materias[]" value="<?= (int) $materia['id'] ?>"
                                        <?= $isSelected ? 'checked' : '' ?> />
                                    <div class="materia-card-content">
                                        <div class="materia-main">
                                            <div class="materia-icon-wrap" aria-hidden="true">
                                                <i class="<?= htmlspecialchars($meta['icon']) ?>"></i>
                                            </div>
                                            <div class="materia-text">
                                                <p class="materia-name"><?= htmlspecialchars($materia['nome']) ?></p>
                                                <p class="materia-hint"><?= htmlspecialchars(__($meta['hint_key'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="materia-checkbox" aria-hidden="true"></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-footer">
                        <div class="selected-summary">
                            <p class="selected-count mb-0">
                                <?= htmlspecialchars(__('signup.selected.label')) ?> <strong id="selectedCount">0</strong>
                                <span id="selectedWord"><?= htmlspecialchars(__('signup.word.subject_plural')) ?></span>
                            </p>
                            <p class="next-hint mb-0">
                                <?= htmlspecialchars(__('signup.selected.next_hint')) ?>
                            </p>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100" id="btnContinue">
                            <?= htmlspecialchars(__('signup.btn.continue_plan')) ?>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <p class="text-center mt-3 mb-0 small" style="color: rgba(255,255,255,0.75);">
            <?= htmlspecialchars(__('signup.footer.account')) ?> <a href="login.php" class="text-white fw-semibold"><?= htmlspecialchars(__('signup.footer.login')) ?></a>
        </p>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const BC_SIGNUP = {
                wordSingular: <?= json_encode(__('signup.word.subject_singular'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
                wordPlural: <?= json_encode(__('signup.word.subject_plural'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
                alertMin: <?= json_encode(__('signup.alert.min_materias'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
            };
            const form = document.getElementById('materiasForm');
            if (!form) return;

            const checkboxes = form.querySelectorAll('input[name="materias[]"]');
            const selectedCountSpan = document.getElementById('selectedCount');
            const selectedWord = document.getElementById('selectedWord');
            const btnContinue = document.getElementById('btnContinue');

            function updateCount() {
                const n = form.querySelectorAll('input[name="materias[]"]:checked').length;
                selectedCountSpan.textContent = String(n);
                if (selectedWord) {
                    selectedWord.textContent = n === 1 ? BC_SIGNUP.wordSingular : BC_SIGNUP.wordPlural;
                }
                if (btnContinue) {
                    btnContinue.disabled = n === 0;
                }
            }

            function updateCardStyle(checkbox) {
                const card = checkbox.closest('.materia-card');
                if (!card) return;
                card.classList.toggle('selected', checkbox.checked);
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    updateCardStyle(this);
                    updateCount();
                });
                updateCardStyle(checkbox);
            });

            form.addEventListener('submit', function (e) {
                if (form.querySelectorAll('input[name="materias[]"]:checked').length === 0) {
                    e.preventDefault();
                    alert(BC_SIGNUP.alertMin);
                }
            });

            updateCount();
        })();
    </script>
</body>

</html>
