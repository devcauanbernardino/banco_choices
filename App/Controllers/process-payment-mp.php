<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/payment_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CARREGAR CONFIGURAÇÕES
// ============================================
$envPath = __DIR__ . '/../../.env';
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

require_once __DIR__ . '/../../config/conexao.php';

// ============================================
// VALIDAR DADOS DO FORMULÁRIO
// ============================================
$email = $_POST['email'] ?? null;
$name = $_POST['name'] ?? null;
$country = $_POST['country'] ?? null;
$postal = $_POST['postal'] ?? null;
$orderId = $_POST['order_id'] ?? null;
$totalPrice = floatval($_POST['total_price'] ?? 0);
$planId = $_POST['plan_id'] ?? null;
$planDurationDays = intval($_POST['plan_duration_days'] ?? 0);
$materias = explode(',', $_POST['materias'] ?? '');


// echo '<pre>';
// print_r($_POST); // DEBUG: Verificar dados recebidos
// echo '</pre>';
// die("Fim do debug");

// Validar dados
if (!$email || !$name || !$orderId || $totalPrice <= 0 || empty($materias)) {
    $errorMsg = "Validação falhou: Email=$email, Name=$name, OrderId=$orderId, TotalPrice=$totalPrice";
    error_log($errorMsg);
    $_SESSION['error'] = 'Dados incompletos. Por favor, tente novamente.';
    
    // Se for AJAX, retornar JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => $errorMsg]);
        exit;
    }
    
    header('Location: /checkout-mercadopago.php');
    exit;
}

// ============================================
// CONFIGURAR MERCADOPAGO
// ============================================
$accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN');

error_log("Access Token carregado: " . (empty($accessToken) ? "NÃO ENCONTRADO" : "OK"));

if (!$accessToken) {
    error_log("Erro crítico: Access Token vazio");
    $_SESSION['error'] = 'Configuração do MercadoPago não disponível';
    header('Location: /checkout-mercadopago.php');
    exit;
}

// ============================================
// CRIAR PREFERÊNCIA DE PAGAMENTO NO MERCADOPAGO
// ============================================

$siteUrl = getenv('SITE_URL') ?: 'http://localhost:8000';

error_log("SITE_URL from env: [" . getenv('SITE_URL') . "]");
error_log("Final siteUrl: [" . $siteUrl . "]");
error_log("siteUrl length: " . strlen($siteUrl));

$siteUrl = trim($siteUrl);

error_log("siteUrl after trim: [" . $siteUrl . "]");

$preference = [
    'items' => [
        [
            'title' => 'Acceso a Materias - ' . $planId,
            'quantity' => 1,
            'unit_price' => $totalPrice,
            'currency_id' => 'BRL'
        ]
    ],
    'payer' => [
        'email' => $email,
        'name' => $name,
        'address' => [
            'zip_code' => $postal,
            'street_name' => $country
        ]
    ],
    'back_urls' => [
        'success' => $siteUrl . '/payment-success.php?status=approved&order_id=' . $orderId,
        'failure' => $siteUrl . '/checkout-mercadopago.php?error=payment_failed',
        'pending' => $siteUrl . '/payment-success.php?status=pending&order_id=' . $orderId
    ],
    'notification_url' => $siteUrl . '/webhook-mercadopago.php',
    'external_reference' => $orderId,
    'metadata' => [
        'email' => $email,
        'name' => $name,
        'plan_id' => $planId,
        'plan_duration_days' => $planDurationDays,
        'materias' => implode(',', $materias)
    ]
];

// ============================================
// ENVIAR REQUISIÇÃO PARA MERCADOPAGO
// ============================================

error_log("Preference JSON: " . json_encode($preference));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

error_log("========== API MERCADOPAGO RESPONSE ==========");
error_log("HTTP Code: $httpCode");
error_log("Curl Error: " . ($curlError ?: "Nenhum"));
error_log("Response: " . substr($response, 0, 500));
error_log("=============================================");

if ($httpCode !== 201) {
    error_log("Erro API: HTTP $httpCode");
    $_SESSION['error'] = 'Erro ao criar preferência de pagamento. HTTP: ' . $httpCode;
    header('Location: /checkout-mercadopago.php');
    exit;
}

$preferenceData = json_decode($response, true);

if (!isset($preferenceData['id'])) {
    error_log("Erro: Resposta inválida - " . json_encode($preferenceData));
    $_SESSION['error'] = 'Erro ao processar pagamento.';
    header('Location: /checkout-mercadopago.php');
    exit;
}

error_log("Sucesso: Preference criada - " . $preferenceData['id']);

// ============================================
// SALVAR DADOS DA SESSÃO PARA DEPOIS DO PAGAMENTO
// ============================================

$_SESSION['pending_order'] = [
    'order_id' => $orderId,
    'email' => $email,
    'name' => $name,
    'total_price' => $totalPrice,
    'plan_id' => $planId,
    'plan_duration_days' => $planDurationDays,
    'materias' => $materias,
    'created_at' => date('Y-m-d H:i:s')
];

// ============================================
// REDIRECIONAR PARA MERCADOPAGO
// ============================================

$initPoint = $preferenceData['init_point'];
error_log("Redirecionando para: " . $initPoint);
header('Location: ' . $initPoint);
exit;
?>
