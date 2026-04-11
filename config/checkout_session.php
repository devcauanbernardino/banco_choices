<?php

declare(strict_types=1);

require_once __DIR__ . '/app_bootstrap.php';
require_once __DIR__ . '/signup_flow.php';

/**
 * Grava na sessão o rascunho do checkout (valores calculados no servidor) para validar o POST em process-payment-mp.
 *
 * @param list<int> $materiasIds
 */
function checkout_draft_save(
    string $orderId,
    string $planId,
    int $durationDays,
    float $unitPrice,
    array $materiasIds,
    float $expectedTotal
): void {
    app_session_start();
    $ids = array_values(array_unique(array_map('intval', $materiasIds)));
    sort($ids);
    $_SESSION['checkout_draft'] = [
        'order_id' => $orderId,
        'plan_id' => $planId,
        'duration_days' => $durationDays,
        'unit_price' => $unitPrice,
        'materias_ids' => $ids,
        'expected_total' => $expectedTotal,
        'created' => time(),
    ];
}

/**
 * @return array{ok: true}|array{ok: false, reason: string}
 */
function checkout_draft_validate_post(array $post): array
{
    app_session_start();
    $draft = $_SESSION['checkout_draft'] ?? null;
    if (!is_array($draft)) {
        return ['ok' => false, 'reason' => 'no_draft'];
    }
    $created = (int) ($draft['created'] ?? 0);
    if ($created <= 0 || (time() - $created) > 7200) {
        unset($_SESSION['checkout_draft']);

        return ['ok' => false, 'reason' => 'draft_expired'];
    }

    $orderId = trim((string) ($post['order_id'] ?? ''));
    if ($orderId === '' || $orderId !== (string) ($draft['order_id'] ?? '')) {
        return ['ok' => false, 'reason' => 'order_mismatch'];
    }

    $planId = (string) ($post['plan_id'] ?? '');
    $def = signup_plan_for_display_by_id($planId);
    if ($def === null) {
        return ['ok' => false, 'reason' => 'invalid_plan'];
    }

    $duration = (int) ($post['plan_duration_days'] ?? 0);
    if ($duration !== (int) $def['durationDays']) {
        return ['ok' => false, 'reason' => 'duration_mismatch'];
    }

    $materiasRaw = array_values(array_filter(array_map('trim', explode(',', (string) ($post['materias'] ?? '')))));
    $materiasIds = array_values(array_unique(array_map('intval', $materiasRaw)));
    sort($materiasIds);
    $expectedIds = $draft['materias_ids'] ?? [];
    if (!is_array($expectedIds)) {
        return ['ok' => false, 'reason' => 'materias_invalid'];
    }
    $expectedIds = array_values(array_map('intval', $expectedIds));
    sort($expectedIds);
    if ($materiasIds !== $expectedIds) {
        return ['ok' => false, 'reason' => 'materias_mismatch'];
    }

    $unit = (float) $def['price'];
    $expectedTotal = $unit * count($materiasIds);
    $postedTotal = (float) ($post['total_price'] ?? 0);
    if (abs($expectedTotal - $postedTotal) > 0.02) {
        return ['ok' => false, 'reason' => 'price_mismatch'];
    }

    $draftTotal = (float) ($draft['expected_total'] ?? 0);
    if (abs($draftTotal - $postedTotal) > 0.02) {
        return ['ok' => false, 'reason' => 'draft_total_mismatch'];
    }

    if ((string) ($draft['plan_id'] ?? '') !== $planId) {
        return ['ok' => false, 'reason' => 'plan_draft_mismatch'];
    }

    return ['ok' => true];
}

function checkout_draft_clear(): void
{
    unset($_SESSION['checkout_draft']);
}

/* ========== Checkout extra: utilizador autenticado compra mais matérias ========== */

/**
 * @param list<int> $materiasIds
 */
function addon_checkout_draft_save(
    string $orderId,
    string $planId,
    int $durationDays,
    float $unitPrice,
    array $materiasIds,
    float $expectedTotal,
    int $userId
): void {
    app_session_start();
    $ids = array_values(array_unique(array_map('intval', $materiasIds)));
    sort($ids);
    $_SESSION['checkout_draft_addon'] = [
        'order_id' => $orderId,
        'plan_id' => $planId,
        'duration_days' => $durationDays,
        'unit_price' => $unitPrice,
        'materias_ids' => $ids,
        'expected_total' => $expectedTotal,
        'user_id' => $userId,
        'created' => time(),
    ];
}

/**
 * @return array{ok: true}|array{ok: false, reason: string}
 */
function addon_checkout_draft_validate_post(array $post): array
{
    app_session_start();
    $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
    if ($uid <= 0) {
        return ['ok' => false, 'reason' => 'not_logged_in'];
    }

    $draft = $_SESSION['checkout_draft_addon'] ?? null;
    if (!is_array($draft)) {
        return ['ok' => false, 'reason' => 'no_draft'];
    }
    if ((int) ($draft['user_id'] ?? 0) !== $uid) {
        return ['ok' => false, 'reason' => 'user_mismatch'];
    }

    $created = (int) ($draft['created'] ?? 0);
    if ($created <= 0 || (time() - $created) > 7200) {
        unset($_SESSION['checkout_draft_addon']);

        return ['ok' => false, 'reason' => 'draft_expired'];
    }

    $orderId = trim((string) ($post['order_id'] ?? ''));
    if ($orderId === '' || $orderId !== (string) ($draft['order_id'] ?? '')) {
        return ['ok' => false, 'reason' => 'order_mismatch'];
    }

    $planId = (string) ($post['plan_id'] ?? '');
    $def = signup_plan_for_display_by_id($planId);
    if ($def === null) {
        return ['ok' => false, 'reason' => 'invalid_plan'];
    }

    $duration = (int) ($post['plan_duration_days'] ?? 0);
    if ($duration !== (int) $def['durationDays']) {
        return ['ok' => false, 'reason' => 'duration_mismatch'];
    }

    $materiasRaw = array_values(array_filter(array_map('trim', explode(',', (string) ($post['materias'] ?? '')))));
    $materiasIds = array_values(array_unique(array_map('intval', $materiasRaw)));
    sort($materiasIds);
    $expectedIds = $draft['materias_ids'] ?? [];
    if (!is_array($expectedIds)) {
        return ['ok' => false, 'reason' => 'materias_invalid'];
    }
    $expectedIds = array_values(array_map('intval', $expectedIds));
    sort($expectedIds);
    if ($materiasIds !== $expectedIds) {
        return ['ok' => false, 'reason' => 'materias_mismatch'];
    }

    $unit = addon_price_per_materia();
    $draftUnit = (float) ($draft['unit_price'] ?? 0);
    if (abs($draftUnit - $unit) > 0.02) {
        return ['ok' => false, 'reason' => 'unit_price_mismatch'];
    }

    $expectedTotal = $unit * count($materiasIds);
    $postedTotal = (float) ($post['total_price'] ?? 0);
    if (abs($expectedTotal - $postedTotal) > 0.02) {
        return ['ok' => false, 'reason' => 'price_mismatch'];
    }

    $draftTotal = (float) ($draft['expected_total'] ?? 0);
    if (abs($draftTotal - $postedTotal) > 0.02) {
        return ['ok' => false, 'reason' => 'draft_total_mismatch'];
    }

    if ((string) ($draft['plan_id'] ?? '') !== $planId) {
        return ['ok' => false, 'reason' => 'plan_draft_mismatch'];
    }

    return ['ok' => true];
}

function addon_checkout_draft_clear(): void
{
    unset($_SESSION['checkout_draft_addon']);
}
