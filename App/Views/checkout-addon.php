<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/signup_flow.php';
require_once __DIR__ . '/../../config/checkout_session.php';
require_once __DIR__ . '/../../config/pricing_display.php';

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

if (empty($_SESSION['addon_plan']) || empty($_SESSION['addon_materias'])) {
    header('Location: ' . app_url('comprar-materias.php'));
    exit;
}

$plan = $_SESSION['addon_plan'];
$planId = (string) ($plan['id'] ?? '');
$planLabels = signup_plan_for_display_by_id($planId);
if ($planLabels !== null) {
    $plan = array_merge($plan, $planLabels);
}

$materiasIds = array_values(
    array_unique(array_map('intval', (array) ($_SESSION['addon_materias'] ?? [])))
);
$materiasIds = array_values(array_filter($materiasIds, static fn (int $id): bool => $id > 0));

$conexao = new Conexao();
$pdo = $conexao->conectar();

if ($materiasIds === []) {
    $_SESSION['error'] = __('addon.select_min');
    header('Location: ' . app_url('comprar-materias.php'));
    exit;
}

$placeholders = implode(',', array_fill(0, count($materiasIds), '?'));
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM materias WHERE id IN ($placeholders)");
    $stmt->execute($materiasIds);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($materias) !== count($materiasIds)) {
        $_SESSION['error'] = 'Matérias inválidas.';
        header('Location: ' . app_url('comprar-materias.php'));
        exit;
    }
} catch (Throwable $e) {
    error_log('checkout-addon materias: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao carregar matérias.';
    header('Location: ' . app_url('comprar-materias.php'));
    exit;
}

$unitPriceMateria = addon_price_per_materia();
$totalPrice = $unitPriceMateria * count($materias);
$orderId = 'ADDON-' . time() . '-' . random_int(1000, 9999);

addon_checkout_draft_save(
    $orderId,
    $planId,
    (int) $plan['durationDays'],
    $unitPriceMateria,
    $materiasIds,
    (float) $totalPrice,
    (int) $_SESSION['usuario']['id']
);

$u = $_SESSION['usuario'];
$checkoutFlash = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$addonCheckoutBackUrl = app_url('comprar-materias.php');
$addonCheckoutBackLabel = __('addon.back_materias');

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(__('addon.page_title_checkout')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../../config/favicon_links.php'; ?>
    <?php require_once __DIR__ . '/includes/theme-head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/sidebar.css')) ?>">
    <style>
        .addon-checkout-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 991.98px) {
            .addon-checkout-grid { grid-template-columns: 1fr; }
        }
        .addon-sum-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--app-border, #e5e7eb);
            font-size: 0.9rem;
        }
        .app-main .form-control:focus {
            border-color: var(--bc-purple, #6a0392);
            box-shadow: 0 0 0 0.2rem rgba(106, 3, 146, 0.28);
            outline: none;
        }
    </style>
</head>

<body class="app-private-body">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <?php
    $app_toolbar_mode = 'mobile';
    $app_toolbar_title = (string) __('nav.buy_subjects');
    require __DIR__ . '/includes/app-private-toolbar.php';
    unset($app_toolbar_mode);
    ?>

    <main class="app-main px-4 pb-4 pt-0">
        <?php
        $app_toolbar_mode = 'desktop';
        require __DIR__ . '/includes/app-private-toolbar.php';
        unset($app_toolbar_mode);
        ?>

        <p class="mb-3">
            <a href="<?= htmlspecialchars($addonCheckoutBackUrl) ?>" class="link-primary text-decoration-none d-inline-flex align-items-center gap-1">
                <i class="bi bi-chevron-left" aria-hidden="true"></i><?= htmlspecialchars($addonCheckoutBackLabel) ?>
            </a>
        </p>

        <?php if ($checkoutFlash !== null && $checkoutFlash !== ''): ?>
            <div class="alert alert-warning"><?= htmlspecialchars((string) $checkoutFlash) ?></div>
        <?php endif; ?>

        <div class="app-page-header mb-4">
            <h2 class="fw-bold mb-1"><?= htmlspecialchars(__('signup.page_title.checkout')) ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars(__('addon.checkout_intro')) ?></p>
        </div>

        <div class="addon-checkout-grid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-credit-card me-2"></i><?= htmlspecialchars(__('signup.checkout.contact_title')) ?></h3>
                    <form method="post" action="<?= htmlspecialchars(app_url('process-payment-mp.php')) ?>" id="addon-pay-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="checkout_kind" value="addon">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
                        <input type="hidden" name="total_price" value="<?= htmlspecialchars((string) $totalPrice) ?>">
                        <input type="hidden" name="plan_id" value="<?= htmlspecialchars((string) $plan['id']) ?>">
                        <input type="hidden" name="plan_duration_days" value="<?= (int) $plan['durationDays'] ?>">
                        <input type="hidden" name="materias" value="<?= htmlspecialchars(implode(',', $materiasIds)) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars((string) ($u['email'] ?? '')) ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars((string) ($u['nome'] ?? '')) ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= htmlspecialchars(__('signup.checkout.email')) ?></label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars((string) ($u['email'] ?? '')) ?>" readonly disabled>
                            <div class="form-text"><?= htmlspecialchars(__('addon.email_note')) ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= htmlspecialchars(__('signup.checkout.name')) ?></label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($u['nome'] ?? '')) ?>" readonly disabled>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="addon-country"><?= htmlspecialchars(__('signup.checkout.country')) ?></label>
                                <input type="text" id="addon-country" name="country" class="form-control" required autocomplete="country-name"
                                    placeholder="<?= htmlspecialchars(__('signup.checkout.country_ph')) ?>">
                                <small id="addon-country-auto-hint" class="form-text text-muted" style="display: none; font-size: 0.8rem;"><?= htmlspecialchars(__('signup.checkout.country_detected')) ?></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="addon-postal"><?= htmlspecialchars(__('signup.checkout.postal')) ?></label>
                                <input type="text" id="addon-postal" name="postal" class="form-control" required autocomplete="postal-code"
                                    placeholder="<?= htmlspecialchars(__('signup.checkout.postal_ph')) ?>">
                                <small id="addon-postal-lookup-hint" class="form-text text-success" style="display: none; font-size: 0.8rem;"></small>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" id="addon-terms" name="terms" class="form-check-input" required>
                            <label class="form-check-label small" for="addon-terms">
                                <?= htmlspecialchars(__('signup.checkout.terms_before')) ?>
                                <a href="<?= htmlspecialchars(app_url('index.php')) ?>#terminos" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(__('signup.checkout.terms_link')) ?></a><?= htmlspecialchars(__('signup.checkout.terms_after')) ?>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="addon-submit">
                            <i class="bi bi-lock-fill me-2"></i>
                            <?= htmlspecialchars(sprintf(__('signup.checkout.submit_mp'), pricing_format_ars_for_checkout((float) $totalPrice))) ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-receipt me-2"></i><?= htmlspecialchars(__('addon.summary')) ?></h3>
                    <?php foreach ($materias as $mat): ?>
                        <div class="addon-sum-row">
                            <span><i class="bi bi-book me-1"></i><?= htmlspecialchars((string) $mat['nome']) ?></span>
                            <span class="text-muted"><?= htmlspecialchars(pricing_format_ars_for_checkout((float) $unitPriceMateria)) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center pt-3 mt-2">
                        <span class="fw-bold"><?= htmlspecialchars(__('signup.checkout.total')) ?></span>
                        <span class="fs-5 fw-bold text-primary"><?= htmlspecialchars(pricing_format_ars_for_checkout((float) $totalPrice)) ?></span>
                    </div>
                    <p class="small text-muted mt-2 mb-0"><?= htmlspecialchars(sprintf(__('signup.checkout.mp_settlement_ars'), pricing_format_ars_settlement((float) $totalPrice))) ?></p>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/private-footer-scripts.php'; ?>
    <script>
        window.__checkoutPostalOpts = <?= json_encode([
            'countryId' => 'addon-country',
            'postalId' => 'addon-postal',
            'countryHintId' => 'addon-country-auto-hint',
            'postalHintId' => 'addon-postal-lookup-hint',
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
    <script src="<?= htmlspecialchars(public_asset_url('assets/js/checkout-postal.js')) ?>"></script>
    <script>
        document.getElementById('addon-pay-form').addEventListener('submit', function () {
            var btn = document.getElementById('addon-submit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> …';
        });
    </script>
</body>

</html>
