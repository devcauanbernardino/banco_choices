<?php

/**
 * Webhook (IPN) Mercado Pago — liberação de acesso somente após consulta à API.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/payment_errors.log');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../App/Services/MercadoPagoWebhookSignature.php';
require_once __DIR__ . '/../App/Services/PaymentFulfillmentService.php';

use MercadoPago\Client\Payment\PaymentClient;

function mp_webhook_log(string $line): void
{
    $path = __DIR__ . '/../logs/mp_payment.log';
    @file_put_contents($path, '[' . date('c') . '] [webhook] ' . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$rawBody = file_get_contents('php://input') ?: '';
$get = $_GET;

$cfg = mercadopago_config();
$secret = $cfg['webhook_secret'];

if (!MercadoPagoWebhookSignature::validate($rawBody, $_SERVER, $get, $secret)) {
    mp_webhook_log('assinatura_invalida');
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'invalid_signature']);
    exit;
}

$paymentId = resolveMercadoPagoPaymentId($get, $rawBody);
if ($paymentId === null) {
    mp_webhook_log('payment_id_ausente body=' . substr($rawBody, 0, 200));
    http_response_code(200);
    echo json_encode(['ok' => true, 'detail' => 'no_payment_id']);
    exit;
}

mp_webhook_log('notificacao payment_id=' . $paymentId);

try {
    mercadopago_bootstrap_sdk();
} catch (Throwable $e) {
    mp_webhook_log('sdk_erro ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

$client = new PaymentClient();

try {
    $payment = $client->get((int) $paymentId);
} catch (Throwable $e) {
    mp_webhook_log('get_payment_erro id=' . $paymentId . ' ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

$conexao = new Conexao();
$pdo = $conexao->conectar();

$result = PaymentFulfillmentService::processPaymentNotification($pdo, $payment);

mp_webhook_log('resultado ' . json_encode($result, JSON_UNESCAPED_UNICODE));

http_response_code(200);
echo json_encode(['ok' => true, 'result' => $result]);

/**
 * @param array<string, mixed> $get
 */
function resolveMercadoPagoPaymentId(array $get, string $rawBody): ?int
{
    if (isset($get['topic']) && (string) $get['topic'] === 'payment' && isset($get['id'])) {
        return (int) $get['id'];
    }

    if ($rawBody !== '') {
        $json = json_decode($rawBody, true);
        if (is_array($json)) {
            $type = (string) ($json['type'] ?? $json['topic'] ?? '');
            if ($type === 'payment' && isset($json['data']['id'])) {
                return (int) $json['data']['id'];
            }
            if (isset($json['data']['id']) && (isset($json['action']) && str_contains((string) $json['action'], 'payment'))) {
                return (int) $json['data']['id'];
            }
        }
    }

    // PHP converte "data.id" na query string em "data_id"
    if (isset($get['data_id'])) {
        return (int) $get['data_id'];
    }
    if (isset($get['data.id'])) {
        return (int) $get['data.id'];
    }

    return null;
}
