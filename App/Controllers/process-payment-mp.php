<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/payment_errors.log');

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/mercadopago.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/checkout_session.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

/**
 * Registra eventos da integração MP (criação de preferência, erros).
 */
function mp_integration_log(string $line): void
{
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'mp_payment.log';
    @file_put_contents($path, '[' . date('c') . '] [preference] ' . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$mpCfg = mercadopago_config();
$siteBase = rtrim($mpCfg['site_url'], '/');
$checkoutRedirectSignup = $siteBase . '/checkout-mercadopago.php';
$checkoutRedirectAddon = $siteBase . '/checkout-addon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $checkoutRedirectSignup);
    exit;
}

if (!csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
    mp_integration_log('csrf_invalido');
    $_SESSION['error'] = 'Sessão expirada ou pedido inválido. Atualize a página e tente novamente.';
    header('Location: ' . $checkoutRedirectSignup);
    exit;
}

$checkoutKind = trim((string) ($_POST['checkout_kind'] ?? 'signup'));
$isAddon = ($checkoutKind === 'addon');
$checkoutRedirect = $isAddon ? $checkoutRedirectAddon : $checkoutRedirectSignup;

if ($isAddon) {
    if (empty($_SESSION['usuario']['id'])) {
        mp_integration_log('addon_sem_sessao');
        $_SESSION['error'] = 'Inicie sessão para concluir a compra.';
        header('Location: ' . app_url('login.php'));
        exit;
    }
    $draftCheck = addon_checkout_draft_validate_post($_POST);
} else {
    $draftCheck = checkout_draft_validate_post($_POST);
}

if (!$draftCheck['ok']) {
    mp_integration_log('checkout_draft_' . ($draftCheck['reason'] ?? 'unknown') . ' kind=' . ($isAddon ? 'addon' : 'signup'));
    $_SESSION['error'] = 'Dados do pedido inválidos ou expirados. Refaça o checkout.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

if ($isAddon) {
    $email = trim((string) ($_SESSION['usuario']['email'] ?? ''));
    $name = trim((string) ($_SESSION['usuario']['nome'] ?? ''));
} else {
    $email = isset($_POST['email']) ? trim((string) $_POST['email']) : null;
    $name = isset($_POST['name']) ? trim((string) $_POST['name']) : null;
}

$country = isset($_POST['country']) ? trim((string) $_POST['country']) : null;
$postal = isset($_POST['postal']) ? trim((string) $_POST['postal']) : null;
$orderId = isset($_POST['order_id']) ? trim((string) $_POST['order_id']) : null;
$totalPrice = floatval($_POST['total_price'] ?? 0);
$planId = isset($_POST['plan_id']) ? (string) $_POST['plan_id'] : null;
$planDurationDays = (int) ($_POST['plan_duration_days'] ?? 0);
$materiasRaw = array_values(array_filter(array_map('trim', explode(',', (string) ($_POST['materias'] ?? '')))));

if (!$email || !$name || !$orderId || $totalPrice <= 0 || $materiasRaw === [] || !$planId || $planDurationDays <= 0) {
    $errorMsg = 'Validação falhou no checkout.';
    mp_integration_log($errorMsg);
    error_log($errorMsg);
    $_SESSION['error'] = 'Dados incompletos. Por favor, tente novamente.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

$cfg = $mpCfg;
if ($cfg['access_token'] === '') {
    mp_integration_log('MP_ACCESS_TOKEN ausente');
    $_SESSION['error'] = 'Configuração do Mercado Pago não disponível.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

$conexao = new Conexao();
$pdo = $conexao->conectar();

$materiasIds = array_map('intval', $materiasRaw);
$placeholders = implode(',', array_fill(0, count($materiasIds), '?'));
$stmt = $pdo->prepare("SELECT id, nome FROM materias WHERE id IN ($placeholders)");
$stmt->execute($materiasIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) !== count($materiasIds)) {
    mp_integration_log('materias_db_count_mismatch');
    $_SESSION['error'] = 'Matérias inválidas.';
    header('Location: ' . $checkoutRedirect);
    exit;
}
$nomesMaterias = array_column($rows, 'nome');
$itemTitle = 'Banco de Choices — ' . (count($nomesMaterias) ? implode(', ', $nomesMaterias) : 'Materias');

try {
    $stmt = $pdo->prepare(
        "INSERT INTO pedidos (email, nome, valor_total, status, stripe_payment_id, data_criacao)
         VALUES (:email, :nome, :valor, 'awaiting_payment', :oref, NOW())"
    );
    $stmt->execute([
        ':email' => $email,
        ':nome' => $name,
        ':valor' => $totalPrice,
        ':oref' => $orderId,
    ]);
} catch (Throwable $e) {
    mp_integration_log('insert_pedido ' . $e->getMessage());
    $_SESSION['error'] = 'Não foi possível registrar o pedido.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

mercadopago_bootstrap_sdk();

$siteUrl = $cfg['site_url'];
$notificationUrl = $siteUrl . '/webhook-mercadopago.php';

$failureBack = $isAddon
    ? $siteUrl . '/checkout-addon.php?error=payment_failed'
    : $siteUrl . '/checkout-mercadopago.php?error=payment_failed';

$preferenceData = [
    'items' => [
        [
            'title' => $itemTitle,
            'quantity' => 1,
            'unit_price' => $totalPrice,
            'currency_id' => $cfg['currency_id'],
        ],
    ],
    'payer' => [
        'email' => $email,
        'name' => $name,
        'address' => [
            'zip_code' => $postal,
            'street_name' => $country,
        ],
    ],
    'back_urls' => [
        'success' => $siteUrl . '/payment-success.php?status=approved&order_id=' . rawurlencode($orderId),
        'failure' => $failureBack,
        'pending' => $siteUrl . '/payment-success.php?status=pending&order_id=' . rawurlencode($orderId),
    ],
    'auto_return' => 'approved',
    'notification_url' => $notificationUrl,
    'external_reference' => $orderId,
    'metadata' => [
        'email' => $email,
        'name' => $name,
        'plan_id' => (string) $planId,
        'plan_duration_days' => (string) $planDurationDays,
        'materias' => implode(',', $materiasIds),
    ],
];

$preferenceClient = new PreferenceClient();

try {
    $preference = $preferenceClient->create($preferenceData);
} catch (MPApiException $e) {
    mp_integration_log('MPApiException: ' . $e->getMessage());
    error_log('Mercado Pago preference: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao criar preferência de pagamento.';
    header('Location: ' . $checkoutRedirect);
    exit;
} catch (Throwable $e) {
    mp_integration_log('preference_error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao processar pagamento.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

$initPoint = $preference->init_point ?: $preference->sandbox_init_point;
if (!$initPoint) {
    mp_integration_log('preference_sem_init_point id=' . ($preference->id ?? ''));
    $_SESSION['error'] = 'Resposta inválida do Mercado Pago.';
    header('Location: ' . $checkoutRedirect);
    exit;
}

mp_integration_log('preference_ok id=' . ($preference->id ?? '') . ' order=' . $orderId . ' kind=' . ($isAddon ? 'addon' : 'signup'));

if ($isAddon) {
    addon_checkout_draft_clear();
} else {
    checkout_draft_clear();
}

$_SESSION['pending_order'] = [
    'order_id' => $orderId,
    'email' => $email,
    'name' => $name,
    'total_price' => $totalPrice,
    'plan_id' => $planId,
    'plan_duration_days' => $planDurationDays,
    'materias' => $materiasIds,
    'preference_id' => $preference->id ?? '',
    'created_at' => date('Y-m-d H:i:s'),
    'checkout_kind' => $isAddon ? 'addon' : 'signup',
];

header('Location: ' . $initPoint);
exit;
