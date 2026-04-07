<?php

require_once dirname(__DIR__, 2) . '/config/bootstrap_env.php';
loadProjectEnv();

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/PaymentEmailHelper.php';

use MercadoPago\Resources\Payment;

/**
 * Liberação de acesso e registro de pedido após consulta do pagamento na API do Mercado Pago.
 * Idempotência por tabela mp_payment_processed (criada automaticamente se possível).
 */
class PaymentFulfillmentService
{
    public static function ensureIdempotencyTable(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS mp_payment_processed (
            mp_payment_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
            created_at DATETIME NOT NULL,
            payment_status VARCHAR(32) NOT NULL,
            external_reference VARCHAR(128) NULL,
            INDEX idx_ext (external_reference)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $pdo->exec($sql);
        } catch (Throwable $e) {
            error_log('PaymentFulfillmentService: não foi possível criar mp_payment_processed: ' . $e->getMessage());
        }
    }

    public static function isPaymentProcessed(PDO $pdo, int $mpPaymentId): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM mp_payment_processed WHERE mp_payment_id = ? LIMIT 1');
        $stmt->execute([$mpPaymentId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function markPaymentProcessed(PDO $pdo, int $mpPaymentId, string $paymentStatus, ?string $externalReference): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO mp_payment_processed (mp_payment_id, created_at, payment_status, external_reference)
             VALUES (?, NOW(), ?, ?)'
        );
        $stmt->execute([$mpPaymentId, $paymentStatus, $externalReference]);
    }

    /**
     * @return array{handled:bool, detail:string}
     */
    public static function processPaymentNotification(PDO $pdo, Payment $payment): array
    {
        self::ensureIdempotencyTable($pdo);

        $mpId = (int) ($payment->id ?? 0);
        if ($mpId <= 0) {
            return ['handled' => false, 'detail' => 'payment_id_invalid'];
        }

        if (self::isPaymentProcessed($pdo, $mpId)) {
            return ['handled' => true, 'detail' => 'already_processed'];
        }

        $status = (string) ($payment->status ?? '');
        $extRef = (string) ($payment->external_reference ?? '');
        $meta = self::metadataToArray($payment->metadata);

        self::mpLog("process_payment id={$mpId} status={$status} ext_ref={$extRef}");

        if ($status === 'approved') {
            return self::handleApproved($pdo, $payment, $mpId, $extRef, $meta);
        }

        if (in_array($status, ['pending', 'in_process', 'authorized'], true)) {
            self::updatePedidoNonFinal($pdo, $extRef, 'pending_mp');
            return ['handled' => true, 'detail' => 'pending_recorded'];
        }

        if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back', 'in_mediation'], true)) {
            self::updatePedidoNonFinal($pdo, $extRef, 'rejected');
            try {
                self::markPaymentProcessed($pdo, $mpId, $status, $extRef !== '' ? $extRef : null);
            } catch (\PDOException $e) {
                if (!self::isDuplicateKey($e)) {
                    throw $e;
                }
            }
            return ['handled' => true, 'detail' => 'terminal_non_approved'];
        }

        return ['handled' => true, 'detail' => 'status_' . $status];
    }

    private static function handleApproved(PDO $pdo, Payment $payment, int $mpId, string $extRef, array $meta): array
    {
        $pdo->beginTransaction();
        try {
            $pedido = null;
            if ($extRef !== '') {
                $stmt = $pdo->prepare(
                    "SELECT * FROM pedidos WHERE stripe_payment_id = :r ORDER BY id DESC LIMIT 1 FOR UPDATE"
                );
                $stmt->execute([':r' => $extRef]);
                $pedido = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($pedido && ($pedido['status'] ?? '') === 'completed') {
                    $pdo->rollBack();
                    return ['handled' => true, 'detail' => 'pedido_already_completed'];
                }
            }

            $email = $pedido['email'] ?? ($meta['email'] ?? '');
            $nome = $pedido['nome'] ?? ($meta['name'] ?? '');
            $planId = (string) ($meta['plan_id'] ?? '');
            $planDays = (int) ($meta['plan_duration_days'] ?? 0);
            $materiasStr = (string) ($meta['materias'] ?? '');
            $materias = self::parseMaterias($materiasStr);

            if ($email === '' || $nome === '' || $planId === '' || $planDays <= 0 || $materias === []) {
                self::mpLog("approved_missing_meta mp_id={$mpId} email=" . ($email ?: 'empty'));
                $pdo->rollBack();
                return ['handled' => false, 'detail' => 'missing_metadata'];
            }

            $valorTotal = $pedido !== null
                ? (float) $pedido['valor_total']
                : (float) ($payment->transaction_amount ?? 0);

            if (!$pedido) {
                $stmt = $pdo->prepare(
                    "INSERT INTO pedidos (email, nome, valor_total, status, stripe_payment_id, data_criacao)
                     VALUES (:email, :nome, :valor, 'awaiting_payment', :oref, NOW())"
                );
                $stmt->execute([
                    ':email' => $email,
                    ':nome' => $nome,
                    ':valor' => $valorTotal,
                    ':oref' => $extRef !== '' ? $extRef : ('MP-' . $mpId),
                ]);
                $pedidoId = (int) $pdo->lastInsertId();
            } else {
                $pedidoId = (int) $pedido['id'];
            }

            $usuarioModel = new Usuario($pdo);
            $existing = $usuarioModel->buscarPorEmail($email);

            $plainPassword = null;

            if ($existing) {
                $uid = (int) $existing['id'];
                foreach ($materias as $mid) {
                    try {
                        $usuarioModel->vincularMateria($uid, $mid);
                    } catch (Throwable $e) {
                        if (!self::isDuplicateKey($e)) {
                            throw $e;
                        }
                    }
                }
                PaymentEmailHelper::sendAccessGrantedExistingUser($email, $nome, $planId);
            } else {
                $plainPassword = PaymentEmailHelper::generateRandomPassword();
                $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users (nome, email, senha, created_at) VALUES (:nome, :email, :senha, NOW())'
                );
                $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash]);
                $uid = (int) $pdo->lastInsertId();

                foreach ($materias as $mid) {
                    $usuarioModel->vincularMateria($uid, $mid);
                }

                $mailUser = getenv('MAIL_USERNAME');
                $mailPass = getenv('MAIL_PASSWORD');
                if (!empty($mailUser) && !empty($mailPass) && strpos((string) $mailUser, 'seu_email') === false) {
                    PaymentEmailHelper::sendConfirmationEmail($email, $nome, $plainPassword, $valorTotal, $planId);
                } else {
                    self::mpLog('email_skip_config mp_id=' . $mpId);
                }
            }

            self::upsertPedidoItens($pdo, $pedidoId, $materias, $planId, $planDays, $valorTotal);

            // Mantém stripe_payment_id como external_reference (ORDER-...) para suporte e consistência;
            // o id numérico do MP fica em mp_payment_processed.
            $stmt = $pdo->prepare(
                "UPDATE pedidos SET status = 'completed' WHERE id = :id"
            );
            $stmt->execute([':id' => $pedidoId]);

            try {
                self::markPaymentProcessed($pdo, $mpId, 'approved', $extRef !== '' ? $extRef : null);
            } catch (\PDOException $e) {
                if (!self::isDuplicateKey($e)) {
                    throw $e;
                }
            }

            $pdo->commit();
            self::mpLog("approved_fulfilled mp_id={$mpId} pedido_id={$pedidoId}");
            return ['handled' => true, 'detail' => 'fulfilled'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            self::mpLog('approved_error ' . $e->getMessage());
            return ['handled' => false, 'detail' => 'exception'];
        }
    }

    private static function upsertPedidoItens(PDO $pdo, int $pedidoId, array $materias, string $planId, int $planDays, float $valorTotal): void
    {
        $preco = count($materias) > 0 ? $valorTotal / count($materias) : $valorTotal;
        $stmt = $pdo->prepare(
            "INSERT INTO pedidos_itens (pedido_id, materia_id, plano_id, preco, data_expiracao)
             VALUES (:pedido_id, :materia_id, :plano_id, :preco, DATE_ADD(NOW(), INTERVAL :dias DAY))"
        );

        foreach ($materias as $mid) {
            $check = $pdo->prepare(
                'SELECT id FROM pedidos_itens WHERE pedido_id = ? AND materia_id = ? LIMIT 1'
            );
            $check->execute([$pedidoId, $mid]);
            if ($check->fetch()) {
                continue;
            }
            $stmt->execute([
                ':pedido_id' => $pedidoId,
                ':materia_id' => $mid,
                ':plano_id' => $planId,
                ':preco' => $preco,
                ':dias' => $planDays,
            ]);
        }
    }

    private static function updatePedidoNonFinal(PDO $pdo, string $extRef, string $status): void
    {
        if ($extRef === '') {
            return;
        }
        try {
            $stmt = $pdo->prepare(
                "UPDATE pedidos SET status = :st WHERE stripe_payment_id = :r AND status = 'awaiting_payment'"
            );
            $stmt->execute([':st' => $status, ':r' => $extRef]);
        } catch (Throwable $e) {
            self::mpLog('update_pedido_nonfinal ' . $e->getMessage());
        }
    }

    /**
     * @return array<int, int>
     */
    private static function parseMaterias(string $csv): array
    {
        $parts = array_filter(array_map('trim', explode(',', $csv)));
        $out = [];
        foreach ($parts as $p) {
            if (is_numeric($p)) {
                $out[] = (int) $p;
            }
        }
        return $out;
    }

    private static function metadataToArray(null|array|object $metadata): array
    {
        if ($metadata === null) {
            return [];
        }
        if (is_array($metadata)) {
            return $metadata;
        }
        $json = json_encode($metadata);
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function isDuplicateKey(Throwable $e): bool
    {
        if ($e instanceof \PDOException) {
            return $e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate');
        }
        return false;
    }

    private static function mpLog(string $line): void
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'mp_payment.log';
        @file_put_contents($path, '[' . date('c') . '] ' . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
