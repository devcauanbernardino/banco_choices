<?php

declare(strict_types=1);

$srcPath = dirname(__DIR__) . '/public/checkout-mercadopago.php';
$outPath = dirname(__DIR__) . '/public/assets/js/checkout-postal.js';

$src = file_get_contents($srcPath);
$start = strpos($src, '(function () {');
$end = strrpos($src, '        })();');
if ($start === false || $end === false) {
    fwrite(STDERR, "markers not found\n");
    exit(1);
}
$block = substr($src, $start + strlen('(function () {'), $end - $start - strlen('(function () {'));
$block = trim($block);

$block = str_replace("document.getElementById('postal')", '_postalEl()', $block);
$block = str_replace("document.getElementById('country')", '_countryEl()', $block);
$block = str_replace("showEl('postal-lookup-hint',", 'showHint(cfg.postalHintId,', $block);
$block = str_replace("showEl('country-auto-hint',", 'showHint(cfg.countryHintId,', $block);
$block = str_replace("fetch('api/checkout-geo.php'", 'fetch(cfg.geoUrl', $block);
$block = str_replace("fetch('api/postal-lookup.php'", 'fetch(cfg.lookupUrl', $block);
$block = str_replace(
    "postalEl.placeholder = (iso && postalPlaceholderByIso[iso]) ? postalPlaceholderByIso[iso] : 'Ej. según tu país';",
    "postalEl.placeholder = (iso && postalPlaceholderByIso[iso]) ? postalPlaceholderByIso[iso] : cfg.strings.fallbackPostalPlaceholder;",
    $block
);
$block = str_replace(
    "hint.textContent = j.label ? ('Ubicación: ' + j.label) : 'Código postal reconocido';",
    'hint.textContent = j.label ? (cfg.strings.locationPrefix + j.label) : cfg.strings.postalOk;',
    $block
);
$block = str_replace('function showEl(id, show) {', 'function showHint(id, show) {', $block);
$block = str_replace('showEl(', 'showHint(', $block);
$block = str_replace(
    "var hint = document.getElementById('postal-lookup-hint');",
    'var hint = cfg.postalHintId ? document.getElementById(cfg.postalHintId) : null;',
    $block
);

$block = preg_replace(
    '/function showHint\(id, show\) \{\s*\R\s*var el =/',
    "function showHint(id, show) {\n                if (!id) return;\n                var el =",
    $block,
    1
);

$header = <<<'HDR'
/**
 * País + código postal: geo, formato por país, lookup (api/postal-lookup.php).
 * Defina window.__checkoutPostalOpts antes de carregar, ou chame initCheckoutPostal(opts).
 */
(function (global) {
    'use strict';

    function mergeOpts(o) {
        o = o || {};
        var str = o.strings || {};
        return {
            countryId: o.countryId || 'country',
            postalId: o.postalId || 'postal',
            countryHintId: o.countryHintId || 'country-auto-hint',
            postalHintId: o.postalHintId || 'postal-lookup-hint',
            geoUrl: o.geoUrl || 'api/checkout-geo.php',
            lookupUrl: o.lookupUrl || 'api/postal-lookup.php',
            strings: {
                locationPrefix: str.locationPrefix || 'Ubicación: ',
                postalOk: str.postalOk || 'Código postal reconocido',
                fallbackPostalPlaceholder: str.fallbackPostalPlaceholder || 'Ej. según tu país'
            }
        };
    }

    function initCheckoutPostal(rawOpts) {
        var cfg = mergeOpts(rawOpts);
        function _postalEl() { return document.getElementById(cfg.postalId); }
        function _countryEl() { return document.getElementById(cfg.countryId); }

HDR;

$footer = <<<'FTR'

    }

    global.initCheckoutPostal = initCheckoutPostal;

    function autoRun() {
        initCheckoutPostal(global.__checkoutPostalOpts || {});
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoRun);
    } else {
        autoRun();
    }
})(typeof window !== 'undefined' ? window : this);
FTR;

$out = $header . "\n" . $block . "\n" . $footer;
file_put_contents($outPath, $out);
echo "Wrote " . strlen($out) . " bytes to $outPath\n";
