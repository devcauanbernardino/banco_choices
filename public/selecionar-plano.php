<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bootstrap_env.php';
loadProjectEnv();

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/public_url.php';
require_once __DIR__ . '/../config/signup_flow.php';

// Verificar se matérias foram selecionadas
if (empty($_SESSION['selected_materias'])) {
    header('Location: selecionar-materias.php');
    exit;
}

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Buscar detalhes das matérias selecionadas
$materiasIds = $_SESSION['selected_materias'];
$placeholders = implode(',', $materiasIds);
$materiasDetails = $pdo->query("SELECT id, nome FROM materias WHERE id IN ($placeholders)")->fetchAll(PDO::FETCH_ASSOC);

$plans = signup_plans_for_display();

// Procesar selección de plano
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planId = $_POST['plan_id'] ?? null;
    
    // Encontrar el plan seleccionado
    $selectedPlan = null;
    foreach ($plans as $plan) {
        if ($plan['id'] === $planId) {
            $selectedPlan = $plan;
            break;
        }
    }
    
    if ($selectedPlan) {
        $_SESSION['selected_plan'] = $selectedPlan;
        header('Location: checkout-mercadopago.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars(__('signup.page_title.plano')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            --bg-light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%);
            background-size: 160% 160%;
            animation: floatBg 14s ease-in-out infinite;
            min-height: 100vh;
            position: relative;
        }

        @media (prefers-reduced-motion: reduce) {
            body {
                animation: none;
            }

            .plan-card:hover {
                transform: none;
            }
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(106, 3, 146, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(70, 1, 97, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        main {
            position: relative;
            z-index: 1;
        }

        @keyframes floatBg {
            0% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 0%;
            }
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header-section {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .header-section h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
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
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-purple);
            border-color: white;
            box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .step.active .step-label {
            color: white;
        }

        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            align-items: stretch;
            min-width: 0;
        }

        .plan-form {
            display: flex;
            min-height: 100%;
            min-width: 0;
            margin: 0;
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            border: 2px solid #e5e7eb;
            padding: 1.35rem 1.5rem 1.5rem;
            position: relative;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
            overflow: hidden;
            width: 100%;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            word-break: normal;
            overflow-wrap: normal;
            hyphens: none;
        }

        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
            background: linear-gradient(90deg, var(--accent-purple), var(--accent-purple-light));
            transition: height 0.3s ease;
        }

        .plan-card:hover {
            border-color: var(--accent-purple);
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(106, 3, 146, 0.18);
        }

        .plan-card.popular {
            border-color: var(--accent-purple);
            box-shadow: 0 16px 36px rgba(106, 3, 146, 0.22);
        }

        .plan-card.popular::before {
            height: 6px;
        }

        /* Faixa do badge no fluxo do documento (evita sobreposição e “linhas” estranhas) */
        .plan-badge-slot {
            min-height: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 0 0.75rem;
            flex-shrink: 0;
        }

        .plan-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent-purple), #8b2e9e);
            color: white;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 4px 14px rgba(106, 3, 146, 0.3);
        }

        .plan-card-head {
            margin-bottom: 1rem;
        }

        .plan-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.3;
            color: var(--navy-primary);
            margin: 0 0 0.45rem;
        }

        .plan-description {
            font-size: 0.88rem;
            line-height: 1.5;
            color: #6b7280;
            margin: 0;
        }

        .plan-price-block {
            margin-bottom: 1.15rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 33, 71, 0.07);
        }

        .plan-price {
            margin: 0 0 0.35rem;
            display: flex;
            flex-wrap: nowrap;
            align-items: baseline;
            gap: 0.4rem;
            min-width: 0;
        }

        .plan-price-amount {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(1.55rem, 3.5vw, 2rem);
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .plan-price-currency {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--accent-purple);
            flex-shrink: 0;
        }

        .plan-price-period {
            font-size: 0.86rem;
            line-height: 1.4;
            color: #6b7280;
            margin: 0;
        }

        .plan-features {
            list-style: none;
            margin: 0 0 1.25rem;
            padding: 0;
            flex: 1 1 auto;
            min-width: 0;
        }

        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            padding: 0.35rem 0;
            font-size: 0.875rem;
            line-height: 1.5;
            color: #374151;
        }

        .plan-features li i {
            color: var(--accent-purple);
            font-size: 1rem;
            margin-top: 0.12rem;
            flex-shrink: 0;
        }

        .plan-features li span {
            flex: 1 1 auto;
            min-width: 0;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .plan-card-actions {
            margin-top: auto;
            padding-top: 0.5rem;
        }

        .selected-materias {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .selected-materias h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--navy-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .selected-materias h3 i {
            color: var(--accent-purple);
        }

        .materias-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .materia-tag {
            background: var(--accent-purple-lighter);
            border: 1px solid var(--accent-purple);
            color: var(--navy-primary);
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            max-width: 100%;
            line-height: 1.3;
        }

        .materia-tag .materia-tag-text {
            overflow-wrap: break-word;
            word-break: normal;
        }

        .top-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.25rem;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        .top-bar .back-link {
            margin-bottom: 0;
        }

        .back-link:hover {
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .back-link:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.85);
            outline-offset: 4px;
            border-radius: 8px;
        }

        .brand-mark:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.85);
            outline-offset: 6px;
            border-radius: 10px;
        }

        .back-link .bi {
            font-size: 1.35rem;
            line-height: 0;
        }

        @media (max-width: 768px) {
            .container-custom {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
                padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
            }

            .header-section {
                margin-bottom: 1.75rem;
            }

            .step-indicator {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.75rem 0.5rem;
                margin-bottom: 1.75rem;
            }

            .step-indicator::before {
                display: none;
            }

            .step {
                flex: 0 0 auto;
                min-width: 72px;
            }

            .plans-container {
                grid-template-columns: 1fr;
            }

            .plan-price {
                flex-wrap: wrap;
            }

            .plan-price-amount {
                white-space: normal;
            }

            .header-section h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <main class="container-custom" id="contenido-principal">
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
                <a href="selecionar-materias.php" class="back-link mb-0">
                    <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                    <?= htmlspecialchars(__('signup.back_materias')) ?>
                </a>
            </div>
        </div>

        <!-- Header -->
        <div class="header-section animate__animated animate__fadeInDown">
            <h1><?= htmlspecialchars(__('signup.plano.h1')) ?></h1>
            <p><?= htmlspecialchars(__('signup.plano.lead')) ?></p>
        </div>

        <!-- Indicador de Pasos -->
        <div class="step-indicator" aria-label="<?= htmlspecialchars(__('signup.steps.aria')) ?>">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-label"><?= htmlspecialchars(__('signup.step.materias')) ?></div>
            </div>
            <div class="step active" aria-current="step">
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

        <!-- Matérias Seleccionadas -->
        <div class="selected-materias animate__animated animate__fadeInUp">
            <h3>
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <?= htmlspecialchars(__('signup.plano.selected')) ?>
            </h3>
            <div class="materias-list">
                <?php foreach ($materiasDetails as $materia): ?>
                    <span class="materia-tag">
                        <i class="bi bi-book" aria-hidden="true"></i>
                        <span class="materia-tag-text"><?= htmlspecialchars($materia['nome']) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Planes -->
        <div class="plans-container animate__animated animate__fadeInUp">
            <?php foreach ($plans as $plan): ?>
                <?php $hasBadge = !empty($plan['badge']); ?>
                <form method="POST" class="plan-form">
                    <div class="plan-card<?= $plan['popular'] ? ' popular' : '' ?>">
                        <div class="plan-badge-slot">
                            <?php if ($hasBadge): ?>
                                <span class="plan-badge"><?= htmlspecialchars((string) $plan['badge']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="plan-card-head">
                            <h2 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h2>
                            <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                        </div>

                        <div class="plan-price-block">
                            <div class="plan-price">
                                <span class="plan-price-amount">$ <?= number_format($plan['price'] * count($materiasDetails), 2, ',', '.') ?></span>
                                <span class="plan-price-currency">ARS</span>
                            </div>
                            <p class="plan-price-period">Total · <?= htmlspecialchars($plan['duration']) ?></p>
                        </div>

                        <ul class="plan-features">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li>
                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($feature) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="plan-card-actions">
                            <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan['id']) ?>">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                <?= htmlspecialchars(__('signup.plano.choose')) ?>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
