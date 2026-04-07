<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CARREGAR CONFIGURAÇÕES
// ============================================
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorar linhas vazias e comentários
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        // Validar formato KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/public_url.php';
require_once __DIR__ . '/../config/signup_flow.php';

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
$materiasIds = $_SESSION['selected_materias'];

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
$orderId = 'ORDER-' . time() . '-' . rand(1000, 9999);
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
            box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter);
            background-color: #fff;
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
                            <input type="text" id="country" name="country" class="form-control" placeholder="Argentina" autocomplete="country-name" required>
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
                        <?= htmlspecialchars(sprintf(__('signup.checkout.submit_mp'), number_format($totalPrice, 2, ',', '.'))) ?>
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
                            <span class="order-item-price">$ <?= number_format($plan['price'], 2, ',', '.') ?> ARS</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary-divider"></div>

                <div class="order-total">
                    <span class="order-total-label"><?= htmlspecialchars(__('signup.checkout.total')) ?></span>
                    <span class="order-total-amount">$ <?= number_format($totalPrice, 2, ',', '.') ?> ARS</span>
                </div>

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
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Redirigiendo...';
        });

        (function () {
            var countryEs = {
                AR: 'Argentina', BR: 'Brasil', CL: 'Chile', UY: 'Uruguay', PY: 'Paraguay',
                BO: 'Bolivia', PE: 'Perú', CO: 'Colombia', EC: 'Ecuador', VE: 'Venezuela',
                MX: 'México', US: 'Estados Unidos', PT: 'Portugal', ES: 'España', FR: 'Francia',
                DE: 'Alemania', IT: 'Italia', GB: 'Reino Unido', CA: 'Canadá'
            };

            var postalPlaceholderByIso = {
                AR: 'Ej. C1425 o 1425',
                BR: 'Ej. 12345-678',
                US: 'Ej. 90210 o 90210-1234',
                MX: 'Ej. 01000',
                CL: 'Ej. 7550000',
                UY: 'Ej. 11000',
                CO: 'Ej. 110111',
                PE: 'Ej. 15001',
                PY: 'Ej. 1536',
                EC: 'Ej. 170135',
                BO: 'Ej. 0000',
                VE: 'Ej. 1010',
                PT: 'Ej. 1000-001',
                ES: 'Ej. 28001',
                FR: 'Ej. 75001',
                DE: 'Ej. 10115',
                IT: 'Ej. 00118',
                GB: 'Ej. SW1A 1AA',
                CA: 'Ej. K1A 0A6',
                AU: 'Ej. 2000',
                NZ: 'Ej. 1010',
                NL: 'Ej. 1012 AB',
                BE: 'Ej. 1000',
                CH: 'Ej. 8001',
                SE: 'Ej. 114 55',
                NO: 'Ej. 0150',
                DK: 'Ej. 2100',
                PL: 'Ej. 00-001',
                IE: 'Ej. D02 AF30',
                JP: 'Ej. 100-0001',
                KR: 'Ej. 03142',
                CN: 'Ej. 100000',
                IN: 'Ej. 110001',
                ZA: 'Ej. 0001'
            };

            var geoIsoCode = null;
            var geoCountryLabel = null;

            function normalizeCountryKey(str) {
                return String(str || '')
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            var nameToIso = (function () {
                var m = {};
                var code;
                function addAlias(key, iso) {
                    if (!key) return;
                    var k = String(key).trim().toLowerCase();
                    m[k] = iso;
                    m[normalizeCountryKey(key)] = iso;
                }
                for (code in countryEs) {
                    if (Object.prototype.hasOwnProperty.call(countryEs, code)) {
                        addAlias(countryEs[code], code);
                    }
                }
                m['brazil'] = 'BR';
                m['argentina'] = 'AR';
                m['chile'] = 'CL';
                m['uruguay'] = 'UY';
                m['paraguay'] = 'PY';
                m['bolivia'] = 'BO';
                m['peru'] = 'PE';
                m['perú'] = 'PE';
                m['colombia'] = 'CO';
                m['ecuador'] = 'EC';
                m['venezuela'] = 'VE';
                m['mexico'] = 'MX';
                m['méxico'] = 'MX';
                m['estados unidos'] = 'US';
                m['united states'] = 'US';
                m['portugal'] = 'PT';
                m['spain'] = 'ES';
                m['españa'] = 'ES';
                m['france'] = 'FR';
                m['francia'] = 'FR';
                m['germany'] = 'DE';
                m['alemania'] = 'DE';
                m['italy'] = 'IT';
                m['italia'] = 'IT';
                m['united kingdom'] = 'GB';
                m['reino unido'] = 'GB';
                m['canada'] = 'CA';
                m['canadá'] = 'CA';
                addAlias('Australia', 'AU');
                addAlias('New Zealand', 'NZ');
                addAlias('Nueva Zelanda', 'NZ');
                addAlias('Netherlands', 'NL');
                addAlias('Países Bajos', 'NL');
                addAlias('Holanda', 'NL');
                addAlias('Belgium', 'BE');
                addAlias('Bélgica', 'BE');
                addAlias('Switzerland', 'CH');
                addAlias('Suiza', 'CH');
                addAlias('Sweden', 'SE');
                addAlias('Suecia', 'SE');
                addAlias('Norway', 'NO');
                addAlias('Noruega', 'NO');
                addAlias('Denmark', 'DK');
                addAlias('Dinamarca', 'DK');
                addAlias('Poland', 'PL');
                addAlias('Polonia', 'PL');
                addAlias('Ireland', 'IE');
                addAlias('Irlanda', 'IE');
                addAlias('Japan', 'JP');
                addAlias('Japón', 'JP');
                addAlias('South Korea', 'KR');
                addAlias('Corea del Sur', 'KR');
                addAlias('China', 'CN');
                addAlias('India', 'IN');
                addAlias('South Africa', 'ZA');
                addAlias('Sudáfrica', 'ZA');
                return m;
            })();

            var countryIso = null;
            var applyingFormat = false;
            var postalLookupTimer = null;
            var postalLookupSeq = 0;

            function showEl(id, show) {
                var el = document.getElementById(id);
                if (el) el.style.display = show ? 'block' : 'none';
            }

            function isoFromCountryName(name) {
                var k = (name || '').trim().toLowerCase();
                if (!k) return null;
                var n = normalizeCountryKey(name);
                return nameToIso[k] || nameToIso[n] || null;
            }

            function setPostalPlaceholder(iso) {
                var postalEl = document.getElementById('postal');
                if (!postalEl) return;
                postalEl.placeholder = (iso && postalPlaceholderByIso[iso]) ? postalPlaceholderByIso[iso] : 'Ej. según tu país';
            }

            function syncIsoFromCountryField(countryEl) {
                var parsed = isoFromCountryName(countryEl.value);
                if (parsed) {
                    countryIso = parsed;
                    setPostalPlaceholder(countryIso);
                    return;
                }
                var v = (countryEl.value || '').trim();
                if (!v) {
                    countryIso = null;
                    geoIsoCode = null;
                    geoCountryLabel = null;
                    setPostalPlaceholder(null);
                    return;
                }
                if (geoIsoCode && normalizeCountryKey(v) === normalizeCountryKey(geoCountryLabel || '')) {
                    countryIso = geoIsoCode;
                    setPostalPlaceholder(countryIso);
                    return;
                }
                countryIso = null;
                setPostalPlaceholder(null);
            }

            function formatPostalValue(iso, raw) {
                if (!iso) return null;
                var d;
                var s;
                switch (iso) {
                    case 'BR':
                        d = String(raw).replace(/\D/g, '').slice(0, 8);
                        if (d.length <= 5) return d;
                        return d.slice(0, 5) + '-' + d.slice(5);
                    case 'US':
                        d = String(raw).replace(/\D/g, '').slice(0, 9);
                        if (d.length <= 5) return d;
                        return d.slice(0, 5) + '-' + d.slice(5);
                    case 'MX':
                    case 'UY':
                    case 'PE':
                        return String(raw).replace(/\D/g, '').slice(0, 5);
                    case 'CL':
                        return String(raw).replace(/\D/g, '').slice(0, 7);
                    case 'CO':
                        return String(raw).replace(/\D/g, '').slice(0, 6);
                    case 'EC':
                        return String(raw).replace(/\D/g, '').slice(0, 6);
                    case 'PY':
                    case 'BO':
                    case 'VE':
                        return String(raw).replace(/\D/g, '').slice(0, 4);
                    case 'PT':
                        d = String(raw).replace(/\D/g, '').slice(0, 7);
                        if (d.length <= 4) return d;
                        return d.slice(0, 4) + '-' + d.slice(4);
                    case 'ES':
                    case 'FR':
                    case 'DE':
                    case 'IT':
                        return String(raw).replace(/\D/g, '').slice(0, 5);
                    case 'AR':
                        s = String(raw).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
                        return s;
                    case 'CA':
                        s = String(raw).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
                        if (s.length <= 3) return s;
                        return s.slice(0, 3) + ' ' + s.slice(3);
                    case 'GB':
                        s = String(raw).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
                        if (s.length <= 4) return s;
                        return s.slice(0, -3) + ' ' + s.slice(-3);
                    case 'AU':
                    case 'NZ':
                        return String(raw).replace(/\D/g, '').slice(0, 4);
                    case 'NL':
                        s = String(raw).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
                        if (s.length <= 4) return s;
                        return s.slice(0, 4) + ' ' + s.slice(4);
                    case 'BE':
                    case 'SE':
                    case 'NO':
                    case 'DK':
                    case 'PL':
                        return String(raw).replace(/\D/g, '').slice(0, 7);
                    case 'IE':
                        s = String(raw).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 7);
                        if (s.length <= 3) return s;
                        return s.slice(0, 3) + ' ' + s.slice(3);
                    case 'CH':
                        return String(raw).replace(/\D/g, '').slice(0, 4);
                    case 'JP':
                        return String(raw).replace(/\D/g, '').slice(0, 8);
                    case 'KR':
                    case 'CN':
                    case 'IN':
                        return String(raw).replace(/\D/g, '').slice(0, 6);
                    case 'ZA':
                        return String(raw).replace(/\D/g, '').slice(0, 4);
                    default:
                        return String(raw).toUpperCase().replace(/[^A-Z0-9\-\s]/g, '').trim().slice(0, 12);
                }
            }

            function refreshPostalFormat(postalEl) {
                if (!postalEl || applyingFormat) return;
                if (!countryIso) return;
                var fmt = formatPostalValue(countryIso, postalEl.value);
                if (fmt !== null && fmt !== postalEl.value) {
                    applyingFormat = true;
                    postalEl.value = fmt;
                    applyingFormat = false;
                }
            }

            function canRunPostalLookup(iso, postalVal) {
                var t = (postalVal || '').trim();
                if (!t) return false;
                if (!iso) {
                    return String(postalVal).replace(/\D/g, '').length === 8;
                }
                var digits = String(postalVal).replace(/\D/g, '');
                if (iso === 'BR') return digits.length >= 8;
                if (iso === 'US') return digits.length >= 5;
                if (iso === 'MX' || iso === 'UY' || iso === 'PE') return digits.length >= 5;
                if (iso === 'CL') return digits.length >= 7;
                if (iso === 'CO' || iso === 'EC') return digits.length >= 6;
                if (iso === 'PY' || iso === 'BO' || iso === 'VE') return digits.length >= 4;
                if (iso === 'AR') return t.replace(/[^A-Za-z0-9]/g, '').length >= 4;
                if (iso === 'CA') return String(postalVal).replace(/[^A-Za-z0-9]/g, '').length >= 6;
                if (iso === 'GB') return t.replace(/[^A-Za-z0-9]/g, '').length >= 5;
                if (iso === 'PT') return digits.length >= 7;
                if (iso === 'ES' || iso === 'FR' || iso === 'DE' || iso === 'IT') return digits.length >= 5;
                if (iso === 'NL' || iso === 'BE') return t.replace(/[^A-Za-z0-9]/g, '').length >= 6;
                if (iso === 'CH' || iso === 'SE' || iso === 'NO' || iso === 'DK' || iso === 'PL' || iso === 'IE') return digits.length >= 4;
                if (iso === 'AU' || iso === 'NZ' || iso === 'ZA') return digits.length >= 4;
                if (iso === 'JP' || iso === 'KR' || iso === 'CN' || iso === 'IN') return digits.length >= 4;
                return t.length >= 4;
            }

            function schedulePostalLookup() {
                clearTimeout(postalLookupTimer);
                postalLookupTimer = setTimeout(function () {
                    runPostalLookup();
                }, 500);
            }

            function runPostalLookup() {
                var postalEl = document.getElementById('postal');
                var countryEl = document.getElementById('country');
                if (!postalEl || !countryEl) return;
                syncIsoFromCountryField(countryEl);
                var isoBeforeFetch = countryIso;
                var iso = countryIso;
                var postal = postalEl.value.trim();
                if (!postal) return;
                if (!iso) {
                    if (String(postal).replace(/\D/g, '').length !== 8) return;
                    iso = 'BR';
                } else if (!canRunPostalLookup(iso, postal)) return;

                var seq = ++postalLookupSeq;
                fetch('api/postal-lookup.php?country=' + encodeURIComponent(iso) + '&postal=' + encodeURIComponent(postal))
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (seq !== postalLookupSeq) return;
                        if (!j || !j.ok) {
                            showEl('postal-lookup-hint', false);
                            return;
                        }
                        var hint = document.getElementById('postal-lookup-hint');
                        if (hint) {
                            hint.textContent = j.label ? ('Ubicación: ' + j.label) : 'Código postal reconocido';
                            hint.style.display = 'block';
                        }
                        if (j.postal_formatted && j.postal_formatted !== postalEl.value) {
                            applyingFormat = true;
                            postalEl.value = j.postal_formatted;
                            applyingFormat = false;
                        }
                        if (j.country === 'BR' && isoBeforeFetch !== 'BR') {
                            var c = document.getElementById('country');
                            if (c) c.value = 'Brasil';
                            geoIsoCode = 'BR';
                            geoCountryLabel = 'Brasil';
                            countryIso = 'BR';
                            setPostalPlaceholder('BR');
                        }
                    })
                    .catch(function () {
                        if (seq !== postalLookupSeq) return;
                        showEl('postal-lookup-hint', false);
                    });
            }

            document.addEventListener('DOMContentLoaded', function () {
                var countryEl = document.getElementById('country');
                var postalEl = document.getElementById('postal');
                if (!countryEl || !postalEl) return;

                syncIsoFromCountryField(countryEl);

                if (!countryEl.value.trim()) {
                    fetch('api/checkout-geo.php', { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (countryEl.value.trim()) return;
                            var name = j.country_code && countryEs[j.country_code] ? countryEs[j.country_code] : (j.country || '');
                            if (name) {
                                countryEl.value = name;
                                geoIsoCode = j.country_code || null;
                                geoCountryLabel = (countryEl.value || '').trim();
                                countryIso = j.country_code || null;
                                setPostalPlaceholder(countryIso);
                                showEl('country-auto-hint', true);
                            }
                        })
                        .catch(function () {});
                }

                countryEl.addEventListener('input', function () {
                    showEl('postal-lookup-hint', false);
                    postalLookupSeq++;
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                });
                countryEl.addEventListener('blur', function () {
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                });
                countryEl.addEventListener('change', function () {
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                });

                postalEl.addEventListener('input', function () {
                    if (applyingFormat) return;
                    clearTimeout(postalLookupTimer);
                    showEl('postal-lookup-hint', false);

                    syncIsoFromCountryField(countryEl);
                    var iso = countryIso;

                    if (iso === 'BR') {
                        var brFmt = formatPostalValue('BR', postalEl.value);
                        if (brFmt !== null && brFmt !== postalEl.value) {
                            applyingFormat = true;
                            postalEl.value = brFmt;
                            applyingFormat = false;
                        }
                        var digitsBr = String(postalEl.value).replace(/\D/g, '');
                        if (digitsBr.length >= 8) schedulePostalLookup();
                        return;
                    }

                    if (iso) {
                        var fmt = formatPostalValue(iso, postalEl.value);
                        if (fmt !== null && fmt !== postalEl.value) {
                            applyingFormat = true;
                            postalEl.value = fmt;
                            applyingFormat = false;
                        }
                        if (canRunPostalLookup(iso, postalEl.value)) schedulePostalLookup();
                        return;
                    }

                    if (iso == null && String(postalEl.value).replace(/\D/g, '').length === 8) {
                        schedulePostalLookup();
                    }
                });
            });
        })();
    </script>
</body>

</html>
