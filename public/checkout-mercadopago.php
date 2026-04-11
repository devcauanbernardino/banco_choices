<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/public_url.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/signup_flow.php';
require_once __DIR__ . '/../config/checkout_session.php';
require_once __DIR__ . '/../config/pricing_display.php';

// Verificar si plan y materias fueron seleccionados
if (empty($_SESSION['selected_plan']) || empty($_SESSION['selected_materias'])) {
    header('Location: selecionar-materias.php');
    exit;
}

$plan = $_SESSION['selected_plan'];
$planId = (string) ($plan['id'] ?? '');
$planLabels = signup_plan_for_display_by_id($planId);
if ($planLabels !== null) {
    $plan = array_merge($plan, $planLabels);
}
$materiasIds = array_values(
    array_unique(array_map('intval', (array) ($_SESSION['selected_materias'] ?? [])))
);
$materiasIds = array_values(array_filter($materiasIds, static fn (int $id): bool => $id > 0));

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Buscar detalhes das matérias
if (empty($materiasIds)) {
    $_SESSION['error'] = 'Nenhuma matéria selecionada';
    header('Location: selecionar-materias.php');
    exit;
}

// Preparar placeholders com segurança
$placeholders = array_fill(0, count($materiasIds), '?');
$placeholderStr = implode(',', $placeholders);

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM materias WHERE id IN ($placeholderStr)");
    $stmt->execute($materiasIds);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($materias)) {
        $_SESSION['error'] = 'Matérias não encontradas';
        header('Location: selecionar-materias.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Erro ao buscar matérias: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao carregar matérias';
    header('Location: selecionar-materias.php');
    exit;
}

// Calcular total
$totalPrice = $plan['price'] * count($materias);
$totalPriceCents = intval($totalPrice * 100);

// Gerar ID único para o pedido
$orderId = 'ORDER-' . time() . '-' . random_int(1000, 9999);

checkout_draft_save(
    $orderId,
    $planId,
    (int) $plan['durationDays'],
    (float) $plan['price'],
    $materiasIds,
    (float) $totalPrice
);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars(__('signup.page_title.checkout')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <?php require_once __DIR__ . '/../config/favicon_links.php'; ?>

    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: #6a03928e;
            --accent-purple-lighter: #6a039220;
            --bg-light: #f8f9fa;
            --success-green: #10b981;
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

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 2rem 1rem;
        }

        .checkout-form-section,
        .order-summary-section {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--navy-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--accent-purple);
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--navy-primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background-color: #f9fafb;
        }

        .form-control:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.2rem rgba(106, 3, 146, 0.32);
            background-color: #fff;
            outline: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .checkout-terms-wrap {
            margin-bottom: 0;
        }

        .checkout-terms-inner {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.15rem;
            background: rgba(106, 3, 146, 0.07);
            border: 1px solid rgba(106, 3, 146, 0.2);
            border-radius: 14px;
        }

        .checkout-terms-input {
            width: 1.35rem !important;
            height: 1.35rem !important;
            min-width: 1.35rem;
            margin: 0.12rem 0 0 0 !important;
            float: none !important;
            border: 2px solid #c4b0d4;
            border-radius: 0.35rem;
            cursor: pointer;
            flex-shrink: 0;
            --bs-form-check-bg: #fff;
        }

        .checkout-terms-input:checked {
            background-color: var(--accent-purple);
            border-color: var(--accent-purple);
        }

        .checkout-terms-input:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.2rem rgba(106, 3, 146, 0.28);
        }

        .checkout-terms-input:hover:not(:disabled) {
            border-color: var(--accent-purple);
        }

        .checkout-terms-label {
            line-height: 1.5;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--navy-primary);
            cursor: pointer;
            margin: 0;
            padding-top: 0.02rem;
        }

        .checkout-terms-link {
            color: var(--accent-purple);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .checkout-terms-link:hover {
            color: #4a0268;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-name {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .order-item-materia {
            font-weight: 500;
            color: var(--navy-primary);
        }

        .order-item-plan {
            font-size: 0.85rem;
            color: #9ca3af;
        }

        .order-item-price {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--accent-purple);
            font-size: 1.1rem;
        }

        .order-summary-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 1.5rem 0;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .order-total-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--navy-primary);
        }

        .order-total-amount {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .checkout-page-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 1rem;
            margin-bottom: 1.5rem;
        }

        .checkout-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 0;
        }

        .checkout-back-link:hover {
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .security-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 1rem;
        }

        .security-info i {
            color: var(--success-green);
        }

        .mercadopago-info {
            background: var(--accent-purple-lighter);
            border-left: 4px solid var(--accent-purple);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: var(--navy-primary);
        }

        .mercadopago-info i {
            color: var(--accent-purple);
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main>
        <div class="checkout-page-top">
            <a href="selecionar-plano.php" class="checkout-back-link">
                <i class="bi bi-chevron-left"></i>
                <?= htmlspecialchars(__('signup.checkout.back')) ?>
            </a>
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
        </div>

        <div class="checkout-container animate__animated animate__fadeInUp">
            <!-- Formulario de Pago -->
            <div class="checkout-form-section">
                <h2 class="section-title">
                    <i class="bi bi-credit-card"></i>
                    <?= htmlspecialchars(__('signup.checkout.contact_title')) ?>
                </h2>

                <form id="payment-form" method="POST" action="process-payment-mp.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="checkout_kind" value="signup">
                    <div class="form-group">
                        <label class="form-label" for="email"><?= htmlspecialchars(__('signup.checkout.email')) ?></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com" required>
                        <small class="form-text"><?= htmlspecialchars(__('signup.checkout.email_hint')) ?></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name"><?= htmlspecialchars(__('signup.checkout.name')) ?></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Juan Pérez" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="country"><?= htmlspecialchars(__('signup.checkout.country')) ?></label>
                            <input type="text" id="country" name="country" class="form-control" placeholder="<?= htmlspecialchars(__('signup.checkout.country_ph')) ?>" autocomplete="country-name" required>
                            <small id="country-auto-hint" class="form-text" style="display: none; color: var(--text-muted, #64748b); font-size: 0.8rem;"><?= htmlspecialchars(__('signup.checkout.country_detected')) ?></small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="postal"><?= htmlspecialchars(__('signup.checkout.postal')) ?></label>
                            <input type="text" id="postal" name="postal" class="form-control" placeholder="<?= htmlspecialchars(__('signup.checkout.postal_ph')) ?>" autocomplete="postal-code" inputmode="text" required>
                            <small id="postal-lookup-hint" class="form-text" style="display: none; color: var(--success-green, #059669); font-size: 0.8rem;"></small>
                        </div>
                    </div>

                    <div class="mercadopago-info">
                        <i class="bi bi-info-circle"></i>
                        <?= htmlspecialchars(__('signup.checkout.mp_info')) ?>
                    </div>

                    <div class="form-group form-row full checkout-terms-wrap">
                        <div class="checkout-terms-inner">
                            <input type="checkbox" id="terms" name="terms" class="form-check-input checkout-terms-input" required>
                            <label class="checkout-terms-label" for="terms">
                                <?= htmlspecialchars(__('signup.checkout.terms_before')) ?><a href="index.php#terminos" target="_blank" rel="noopener noreferrer" class="checkout-terms-link"><?= htmlspecialchars(__('signup.checkout.terms_link')) ?></a><?= htmlspecialchars(__('signup.checkout.terms_after')) ?>
                            </label>
                        </div>
                    </div>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <input type="hidden" name="total_price" value="<?= $totalPrice ?>">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <input type="hidden" name="plan_duration_days" value="<?= $plan['durationDays'] ?>">
                    <input type="hidden" name="materias" value="<?= implode(',', $materiasIds) ?>">

                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-2" id="submit-btn">
                        <i class="bi bi-lock-fill"></i>
                        <?= htmlspecialchars(sprintf(__('signup.checkout.submit_mp'), pricing_format_ars_for_checkout((float) $totalPrice))) ?>
                    </button>

                    <div class="security-info">
                        <i class="bi bi-shield-check"></i>
                        <?= htmlspecialchars(__('signup.checkout.secure')) ?>
                    </div>
                </form>
            </div>

            <!-- Resumen del Pedido -->
            <div class="order-summary-section">
                <h2 class="section-title">
                    <i class="bi bi-receipt"></i>
                    <?= htmlspecialchars(__('signup.checkout.summary_title')) ?>
                </h2>

                <div>
                    <?php foreach ($materias as $materia): ?>
                        <div class="order-item">
                            <div class="order-item-name">
                                <span class="order-item-materia">
                                    <i class="bi bi-book me-2"></i><?= htmlspecialchars($materia['nome']) ?>
                                </span>
                                <span class="order-item-plan"><?= $plan['name'] ?></span>
                            </div>
                            <span class="order-item-price"><?= htmlspecialchars(pricing_format_ars_for_checkout((float) $plan['price'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary-divider"></div>

                <div class="order-total">
                    <span class="order-total-label"><?= htmlspecialchars(__('signup.checkout.total')) ?></span>
                    <span class="order-total-amount"><?= htmlspecialchars(pricing_format_ars_for_checkout((float) $totalPrice)) ?></span>
                </div>

                <p class="small text-muted mt-2 mb-0" style="font-size: 0.82rem;">
                    <?= htmlspecialchars(sprintf(__('signup.checkout.mp_settlement_ars'), pricing_format_ars_settlement((float) $totalPrice))) ?>
                </p>

                <div style="background: var(--accent-purple-lighter); border-left: 4px solid var(--accent-purple); padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
                    <p style="font-size: 0.85rem; color: var(--navy-primary); margin: 0;">
                        <i class="bi bi-info-circle me-2"></i>
                        <?= htmlspecialchars(sprintf(__('signup.checkout.access_note'), (string) $plan['duration'])) ?>
                    </p>
                </div>

                <div style="background: var(--success-green); background-opacity: 0.1; border-left: 4px solid var(--success-green); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <p style="font-size: 0.85rem; color: #065f46; margin: 0;">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars(__('signup.checkout.after_pay_note')) ?>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.__checkoutPostalOpts = <?= json_encode([
            'geoUrl' => app_url('api/checkout-geo.php'),
            'lookupUrl' => app_url('api/postal-lookup.php'),
            'countryResolveUrl' => app_url('api/country-resolve.php'),
            'inferCountryUrl' => app_url('api/postal-infer-country.php'),
            'strings' => [
                'locationPrefix' => (string) __('signup.checkout.postal_location_prefix'),
                'postalOk' => (string) __('signup.checkout.postal_ok'),
                'fallbackPostalPlaceholder' => (string) __('signup.checkout.postal_ph'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= htmlspecialchars(public_asset_url('assets/js/checkout-postal.js')) ?>" defer></script>
    <script>
        document.getElementById('payment-form').addEventListener('submit', function () {
            var submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> …';
        });
    </script>
</body>

</html>
