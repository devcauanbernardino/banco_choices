<?php

declare(strict_types=1);

require_once __DIR__ . '/locale.php';

/**
 * Valores em ARS são os cobrados no Mercado Pago.
 * Aqui só convertemos/formatamos para o idioma (referência aproximada).
 *
 * Opcional: PRICING_DISPLAY_RATES_JSON = {"pt_BR":{"rate":0.0055},...}
 * rate = multiplicador: valor_exibido = ARS * rate
 */
function pricing_display_rates(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $env = getenv('PRICING_DISPLAY_RATES_JSON');
    if (is_string($env) && $env !== '') {
        $d = json_decode($env, true);
        if (is_array($d)) {
            $cache = $d;

            return $cache;
        }
    }
    $cache = [
        'es_AR' => ['code' => 'ARS', 'rate' => 1.0, 'decimals' => 2],
        'pt_BR' => ['code' => 'BRL', 'rate' => 0.0055, 'decimals' => 2],
        'en_US' => ['code' => 'USD', 'rate' => 0.0010, 'decimals' => 2],
    ];

    return $cache;
}

/**
 * @return array{code: string, rate: float, decimals: int}
 */
function pricing_display_profile(): array
{
    $rates = pricing_display_rates();
    $code = locale_code();

    return $rates[$code] ?? $rates['es_AR'];
}

function pricing_convert_from_ars(float $ars): float
{
    $p = pricing_display_profile();

    return $ars * (float) $p['rate'];
}

/** Formatação principal nos ecrãs de checkout / planos (sem nota de rodapé). */
function pricing_format_ars_for_checkout(float $ars): string
{
    $p = pricing_display_profile();
    $v = pricing_convert_from_ars($ars);
    $d = (int) $p['decimals'];
    $lc = locale_code();
    $cur = (string) $p['code'];

    if ($lc === 'en_US') {
        $num = number_format($v, $d, '.', ',');

        return $cur === 'USD' ? ('US$' . $num) : ('$' . $num . ' ' . $cur);
    }

    $num = number_format($v, $d, ',', '.');

    if ($cur === 'BRL') {
        return 'R$ ' . $num;
    }

    return '$ ' . $num . ' ' . $cur;
}

function pricing_display_currency_label(): string
{
    return (string) pricing_display_profile()['code'];
}

/** ARS formatado para texto “cobrança em ARS”. */
function pricing_format_ars_settlement(float $ars): string
{
    return number_format($ars, 2, ',', '.');
}
