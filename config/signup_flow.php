<?php

declare(strict_types=1);

if (!function_exists('__')) {
    require_once __DIR__ . '/locale.php';
}

/**
 * Planos do fluxo público de cadastro — textos via lang/*.php (locale atual).
 */
function signup_plan_definitions(): array
{
    return [
        'monthly' => [
            'id' => 'monthly',
            'durationDays' => 30,
            'price' => 29.90,
            'name_key' => 'signup.plan.monthly.name',
            'duration_key' => 'signup.plan.monthly.duration',
            'description_key' => 'signup.plan.monthly.desc',
            'feature_keys' => [
                'signup.plan.monthly.f1',
                'signup.plan.monthly.f2',
                'signup.plan.monthly.f3',
                'signup.plan.monthly.f4',
            ],
            'badge_key' => null,
            'popular' => false,
        ],
        'semester' => [
            'id' => 'semester',
            'durationDays' => 180,
            'price' => 119.90,
            'name_key' => 'signup.plan.semester.name',
            'duration_key' => 'signup.plan.semester.duration',
            'description_key' => 'signup.plan.semester.desc',
            'feature_keys' => [
                'signup.plan.semester.f1',
                'signup.plan.semester.f2',
                'signup.plan.semester.f3',
                'signup.plan.semester.f4',
                'signup.plan.semester.f5',
            ],
            'badge_key' => 'signup.plan.semester.badge',
            'popular' => true,
        ],
        'annual' => [
            'id' => 'annual',
            'durationDays' => 365,
            'price' => 199.90,
            'name_key' => 'signup.plan.annual.name',
            'duration_key' => 'signup.plan.annual.duration',
            'description_key' => 'signup.plan.annual.desc',
            'feature_keys' => [
                'signup.plan.annual.f1',
                'signup.plan.annual.f2',
                'signup.plan.annual.f3',
                'signup.plan.annual.f4',
                'signup.plan.annual.f5',
                'signup.plan.annual.f6',
            ],
            'badge_key' => 'signup.plan.annual.badge',
            'popular' => false,
        ],
    ];
}

/**
 * @return array<string, mixed>|null
 */
function signup_plan_for_display_by_id(string $id): ?array
{
    $defs = signup_plan_definitions();
    $id = strtolower(trim($id));
    if (!isset($defs[$id])) {
        return null;
    }
    $def = $defs[$id];

    return [
        'id' => $def['id'],
        'name' => __($def['name_key']),
        'duration' => __($def['duration_key']),
        'durationDays' => $def['durationDays'],
        'price' => $def['price'],
        'description' => __($def['description_key']),
        'features' => array_map(static fn (string $k): string => __($k), $def['feature_keys']),
        'badge' => $def['badge_key'] !== null ? __($def['badge_key']) : null,
        'popular' => $def['popular'],
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function signup_plans_for_display(): array
{
    $order = ['monthly', 'semester', 'annual'];
    $out = [];
    foreach ($order as $id) {
        $p = signup_plan_for_display_by_id($id);
        if ($p !== null) {
            $out[] = $p;
        }
    }

    return $out;
}

/**
 * Plano padrão para matérias extra quando não há histórico em pedidos_itens
 * (ex.: primeira conta sem compra registada ainda — o cadastro público já inclui plano).
 */
function addon_plan_fallback_id(): string
{
    return 'semester';
}

/**
 * Preço cobrado por cada matéria nova (fluxo autenticado).
 * Independente do valor “pacote” mensal/semestral/anual do cadastro.
 * Opcional: variável de ambiente ADDON_PRICE_PER_MATERIA (ex.: 29.90).
 */
function addon_price_per_materia(): float
{
    $env = getenv('ADDON_PRICE_PER_MATERIA');
    if ($env !== false && $env !== '') {
        $v = str_replace(',', '.', trim((string) $env));
        if (is_numeric($v)) {
            $f = (float) $v;
            if ($f > 0) {
                return $f;
            }
        }
    }

    return 29.90;
}

/**
 * Resolve o plano usado no checkout de matérias extra: último plan_id em pedidos_itens
 * ou o fallback. Não há ecrã de escolha de plano neste fluxo.
 *
 * @return array<string, mixed>
 */
function addon_resolve_plan_for_extra_materias(?string $planoIdDoUltimoPedido): array
{
    if ($planoIdDoUltimoPedido !== null && $planoIdDoUltimoPedido !== '') {
        $p = signup_plan_for_display_by_id($planoIdDoUltimoPedido);
        if ($p !== null) {
            return $p;
        }
    }

    $fb = signup_plan_for_display_by_id(addon_plan_fallback_id());
    if ($fb === null) {
        throw new RuntimeException('addon_plan_fallback_id inválido');
    }

    return $fb;
}
