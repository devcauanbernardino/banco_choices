/**
 * País + código postal: geo, inferência de país pelo CEP (api/postal-infer-country.php), lookup (api/postal-lookup.php).
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
            countryResolveUrl: o.countryResolveUrl || 'api/country-resolve.php',
            inferCountryUrl: o.inferCountryUrl || 'api/postal-infer-country.php',
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
                m['espana'] = 'ES';
                m['españa'] = 'ES';
                m['france'] = 'FR';
                m['francia'] = 'FR';
                m['germany'] = 'DE';
                m['deutschland'] = 'DE';
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
                addAlias('República Argentina', 'AR');
                addAlias('Argentine Republic', 'AR');
                addAlias('Argentine', 'AR');
                addAlias('Thailand', 'TH');
                addAlias('Vietnam', 'VN');
                addAlias('Indonesia', 'ID');
                addAlias('Malaysia', 'MY');
                addAlias('Singapore', 'SG');
                addAlias('Philippines', 'PH');
                addAlias('Russia', 'RU');
                addAlias('Russian Federation', 'RU');
                addAlias('Ukraine', 'UA');
                addAlias('Turkey', 'TR');
                addAlias('Türkiye', 'TR');
                addAlias('Czechia', 'CZ');
                addAlias('Czech Republic', 'CZ');
                addAlias('Romania', 'RO');
                addAlias('Hungary', 'HU');
                addAlias('Greece', 'GR');
                addAlias('Austria', 'AT');
                addAlias('Österreich', 'AT');
                addAlias('Finland', 'FI');
                addAlias('Iceland', 'IS');
                addAlias('Croatia', 'HR');
                addAlias('Slovenia', 'SI');
                addAlias('Slovakia', 'SK');
                addAlias('Bulgaria', 'BG');
                addAlias('Serbia', 'RS');
                addAlias('Israel', 'IL');
                addAlias('Saudi Arabia', 'SA');
                addAlias('United Arab Emirates', 'AE');
                addAlias('Egypt', 'EG');
                addAlias('Nigeria', 'NG');
                addAlias('Kenya', 'KE');
                addAlias('Morocco', 'MA');
                addAlias('Tunisia', 'TN');
                addAlias('Algeria', 'DZ');
                addAlias('Pakistan', 'PK');
                addAlias('Bangladesh', 'BD');
                addAlias('Taiwan', 'TW');
                addAlias('Hong Kong', 'HK');
                return m;
            })();

            var countryIso = null;
            var applyingFormat = false;
            var postalLookupTimer = null;
            var postalLookupSeq = 0;
            var resolvedIso = null;
            var resolvedNorm = null;
            var countryResolveTimer = null;
            var inferSeq = 0;
            var postalInferTimer = null;
            var countryResolveFastTimer = null;

            function showHint(id, show) {
                if (!id) return;
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
                var postalEl = _postalEl();
                if (!postalEl) return;
                postalEl.placeholder = (iso && postalPlaceholderByIso[iso]) ? postalPlaceholderByIso[iso] : cfg.strings.fallbackPostalPlaceholder;
            }

            function syncIsoFromCountryField(countryEl) {
                var v = (countryEl.value || '').trim();
                if (!v) {
                    countryIso = null;
                    resolvedIso = null;
                    resolvedNorm = null;
                    geoIsoCode = null;
                    geoCountryLabel = null;
                    countryEl.removeAttribute('data-bc-iso');
                    setPostalPlaceholder(null);
                    return;
                }
                if (/^[A-Za-z]{2}$/.test(v)) {
                    countryIso = v.toUpperCase();
                    resolvedIso = null;
                    resolvedNorm = null;
                    countryEl.setAttribute('data-bc-iso', countryIso);
                    setPostalPlaceholder(countryIso);
                    return;
                }
                var parsed = isoFromCountryName(countryEl.value);
                if (parsed) {
                    countryIso = parsed;
                    resolvedIso = null;
                    resolvedNorm = null;
                    countryEl.setAttribute('data-bc-iso', countryIso);
                    setPostalPlaceholder(countryIso);
                    return;
                }
                var vn = normalizeCountryKey(v);
                if (resolvedIso && resolvedNorm === vn) {
                    countryIso = resolvedIso;
                    countryEl.setAttribute('data-bc-iso', countryIso);
                    setPostalPlaceholder(countryIso);
                    return;
                }
                if (geoIsoCode && normalizeCountryKey(v) === normalizeCountryKey(geoCountryLabel || '')) {
                    countryIso = geoIsoCode;
                    countryEl.setAttribute('data-bc-iso', countryIso);
                    setPostalPlaceholder(countryIso);
                    return;
                }
                var fromAttr = countryEl.getAttribute('data-bc-iso');
                if (fromAttr && /^[A-Za-z]{2}$/.test(fromAttr)) {
                    countryIso = fromAttr.toUpperCase();
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
                return t.replace(/\s/g, '').length >= 3;
            }

            function schedulePostalLookup() {
                clearTimeout(postalLookupTimer);
                postalLookupTimer = setTimeout(function () {
                    runPostalLookup();
                }, 500);
            }

            function runPostalLookup() {
                var postalEl = _postalEl();
                var countryEl = _countryEl();
                if (!postalEl || !countryEl) return;
                var postal = postalEl.value.trim();
                if (!postal) return;
                var countryHint = (countryEl.value || '').trim();
                var seq = ++postalLookupSeq;

                function performFetch(iso, beforeIso) {
                    if (!iso || !canRunPostalLookup(iso, postal)) return;
                    var lookupBase = cfg.lookupUrl;
                    var qsep = lookupBase.indexOf('?') >= 0 ? '&' : '?';
                    var url = lookupBase + qsep + 'country=' + encodeURIComponent(iso) + '&postal=' + encodeURIComponent(postal);
                    if (countryHint) {
                        url += '&hint=' + encodeURIComponent(countryHint);
                    }
                    fetch(url)
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (seq !== postalLookupSeq) return;
                            if (!j || !j.ok) {
                                showHint(cfg.postalHintId, false);
                                return;
                            }
                            if (j.country && /^[A-Za-z]{2}$/.test(String(j.country))) {
                                countryEl.setAttribute('data-bc-iso', String(j.country).toUpperCase());
                            }
                            var hintEl = cfg.postalHintId ? document.getElementById(cfg.postalHintId) : null;
                            if (hintEl) {
                                hintEl.textContent = j.label ? (cfg.strings.locationPrefix + j.label) : cfg.strings.postalOk;
                                hintEl.style.display = 'block';
                            }
                            if (j.postal_formatted && j.postal_formatted !== postalEl.value) {
                                applyingFormat = true;
                                postalEl.value = j.postal_formatted;
                                applyingFormat = false;
                            }
                            if (j.country === 'BR' && beforeIso !== 'BR') {
                                var c = _countryEl();
                                if (c) c.value = 'Brasil';
                                geoIsoCode = 'BR';
                                geoCountryLabel = 'Brasil';
                                countryIso = 'BR';
                                setPostalPlaceholder('BR');
                            }
                        })
                        .catch(function () {
                            if (seq !== postalLookupSeq) return;
                            showHint(cfg.postalHintId, false);
                        });
                }

                syncIsoFromCountryField(countryEl);
                if (countryIso && canRunPostalLookup(countryIso, postal)) {
                    performFetch(countryIso, countryIso);
                    return;
                }

                if (countryHint === '') {
                    if (String(postal).replace(/\D/g, '').length === 8) {
                        performFetch('BR', countryIso || null);
                    }
                    return;
                }

                if (/^[A-Za-z]{2}$/.test(countryHint)) {
                    syncIsoFromCountryField(countryEl);
                    if (countryIso && canRunPostalLookup(countryIso, postal)) {
                        performFetch(countryIso, countryIso);
                    }
                    return;
                }

                if (isoFromCountryName(countryEl.value)) {
                    syncIsoFromCountryField(countryEl);
                    if (countryIso && canRunPostalLookup(countryIso, postal)) {
                        performFetch(countryIso, countryIso);
                    }
                    return;
                }

                var crBase = cfg.countryResolveUrl;
                var crSep = crBase.indexOf('?') >= 0 ? '&' : '?';
                fetch(crBase + crSep + 'q=' + encodeURIComponent(countryHint))
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (seq !== postalLookupSeq) return;
                        if (j && j.ok && j.iso) {
                            resolvedIso = j.iso;
                            resolvedNorm = normalizeCountryKey(countryHint);
                            syncIsoFromCountryField(countryEl);
                            var iso2 = countryIso;
                            if (iso2 && canRunPostalLookup(iso2, postal)) {
                                performFetch(iso2, iso2);
                            }
                            return;
                        }
                        if (String(postal).replace(/\D/g, '').length === 8) {
                            performFetch('BR', countryIso || null);
                        }
                    })
                    .catch(function () {
                        if (seq !== postalLookupSeq) return;
                        if (String(postal).replace(/\D/g, '').length === 8) {
                            performFetch('BR', countryIso || null);
                        }
                    });
            }

            function bindPostalUi() {
                var countryEl = _countryEl();
                var postalEl = _postalEl();
                if (!countryEl || !postalEl) return;
                if (postalEl.getAttribute('data-bc-postal-bound') === '1') return;
                postalEl.setAttribute('data-bc-postal-bound', '1');
                countryEl.setAttribute('data-bc-postal-bound', '1');

                syncIsoFromCountryField(countryEl);

                if (!countryEl.value.trim()) {
                    fetch(cfg.geoUrl, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (countryEl.value.trim()) return;
                            if (!j || !j.country_code) return;
                            var name = String(j.country || '').trim();
                            if (!name && j.country_code && countryEs[j.country_code]) {
                                name = countryEs[j.country_code];
                            }
                            if (!name) {
                                name = j.country_code;
                            }
                            if (name) {
                                countryEl.value = name;
                                geoIsoCode = j.country_code || null;
                                geoCountryLabel = (countryEl.value || '').trim();
                                countryIso = j.country_code || null;
                                if (j.country_code && /^[A-Za-z]{2}$/.test(j.country_code)) {
                                    countryEl.setAttribute('data-bc-iso', String(j.country_code).toUpperCase());
                                }
                                setPostalPlaceholder(countryIso);
                                showHint(cfg.countryHintId, true);
                            }
                        })
                        .catch(function () {});
                }

                function onPostalMaybeLookup() {
                    if (applyingFormat) return;
                    clearTimeout(postalLookupTimer);
                    showHint(cfg.postalHintId, false);

                    var ptrimEarly = (postalEl.value || '').trim();
                    if (ptrimEarly === '') {
                        inferSeq++;
                        postalLookupSeq++;
                        clearTimeout(postalInferTimer);
                        clearTimeout(countryResolveTimer);
                        countryEl.value = '';
                        countryEl.removeAttribute('data-bc-iso');
                        geoIsoCode = null;
                        geoCountryLabel = null;
                        resolvedIso = null;
                        resolvedNorm = null;
                        syncIsoFromCountryField(countryEl);
                        setPostalPlaceholder(null);
                        showHint(cfg.countryHintId, false);
                        return;
                    }

                    if (!(countryEl.value || '').trim()) {
                        var digitsBr = String(postalEl.value || '').replace(/\D/g, '');
                        if (digitsBr.length === 8 && /^\d{8}$/.test(digitsBr)) {
                            countryEl.value = 'Brasil';
                            geoIsoCode = 'BR';
                            geoCountryLabel = 'Brasil';
                            countryEl.setAttribute('data-bc-iso', 'BR');
                        }
                    }

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
                        var fmt2 = formatPostalValue(iso, postalEl.value);
                        if (fmt2 !== null && fmt2 !== postalEl.value) {
                            applyingFormat = true;
                            postalEl.value = fmt2;
                            applyingFormat = false;
                        }
                        if (canRunPostalLookup(iso, postalEl.value)) schedulePostalLookup();
                        return;
                    }

                    var ptrim = (postalEl.value || '').trim();
                    var ctrim = (countryEl.value || '').trim();
                    if (String(ptrim).replace(/\D/g, '').length === 8) {
                        schedulePostalLookup();
                        return;
                    }
                    if (ctrim !== '' && ptrim.replace(/\s/g, '').length >= 3) {
                        schedulePostalLookup();
                    }
                }

                function scheduleInferCountryFromPostal(delayMs) {
                    var d = delayMs == null ? 650 : delayMs;
                    clearTimeout(postalInferTimer);
                    postalInferTimer = setTimeout(function () {
                        var snap = inferSeq;
                        var ctrim = (countryEl.value || '').trim();
                        if (ctrim !== '') return;
                        var ptrim = (postalEl.value || '').trim();
                        if (ptrim.replace(/\s/g, '').length < 3) return;
                        var digitsOnly = ptrim.replace(/\D/g, '');
                        if (digitsOnly.length === 8 && /^\d+$/.test(digitsOnly)) {
                            if (snap !== inferSeq) return;
                            if ((postalEl.value || '').trim() === '') return;
                            countryEl.value = 'Brasil';
                            geoIsoCode = 'BR';
                            geoCountryLabel = 'Brasil';
                            countryEl.setAttribute('data-bc-iso', 'BR');
                            syncIsoFromCountryField(countryEl);
                            setPostalPlaceholder('BR');
                            showHint(cfg.countryHintId, true);
                            refreshPostalFormat(postalEl);
                            onPostalMaybeLookup();
                            return;
                        }
                        var ib = cfg.inferCountryUrl;
                        var ibSep = ib.indexOf('?') >= 0 ? '&' : '?';
                        fetch(ib + ibSep + 'postal=' + encodeURIComponent(ptrim))
                            .then(function (r) { return r.json(); })
                            .then(function (j) {
                                if (snap !== inferSeq) return;
                                if ((postalEl.value || '').trim() === '') return;
                                if ((countryEl.value || '').trim() !== '') return;
                                if (!j || !j.ok || !j.country_code) return;
                                var display = countryEs[j.country_code] ? countryEs[j.country_code] : (j.country_name || j.country_code);
                                countryEl.value = display;
                                geoIsoCode = j.country_code;
                                geoCountryLabel = display.trim();
                                countryEl.setAttribute('data-bc-iso', j.country_code);
                                syncIsoFromCountryField(countryEl);
                                setPostalPlaceholder(countryIso);
                                showHint(cfg.countryHintId, true);
                                refreshPostalFormat(postalEl);
                                onPostalMaybeLookup();
                            })
                            .catch(function () {});
                    }, d);
                }

                function scheduleCountryResolve() {
                    clearTimeout(countryResolveTimer);
                    countryResolveTimer = setTimeout(function () {
                        var v = (countryEl.value || '').trim();
                        if (!v) return;
                        if (/^[A-Za-z]{2}$/.test(v)) return;
                        if (isoFromCountryName(countryEl.value)) return;
                        var vn = normalizeCountryKey(v);
                        var crBase = cfg.countryResolveUrl;
                        var crSep = crBase.indexOf('?') >= 0 ? '&' : '?';
                        fetch(crBase + crSep + 'q=' + encodeURIComponent(v))
                            .then(function (r) { return r.json(); })
                            .then(function (j) {
                                if (!j || !j.ok || !j.iso) return;
                                if (normalizeCountryKey(countryEl.value) !== vn) return;
                                resolvedIso = j.iso;
                                resolvedNorm = vn;
                                countryEl.setAttribute('data-bc-iso', j.iso);
                                syncIsoFromCountryField(countryEl);
                                refreshPostalFormat(postalEl);
                                onPostalMaybeLookup();
                            })
                            .catch(function () {});
                    }, 450);
                }

                function scheduleCountryResolveFast() {
                    clearTimeout(countryResolveFastTimer);
                    countryResolveFastTimer = setTimeout(function () {
                        var v = (countryEl.value || '').trim();
                        var p = (postalEl.value || '').trim();
                        if (!v || !p) return;
                        syncIsoFromCountryField(countryEl);
                        if (countryIso) {
                            onPostalMaybeLookup();
                            return;
                        }
                        if (/^[A-Za-z]{2}$/.test(v)) return;
                        if (isoFromCountryName(countryEl.value)) return;
                        var vn = normalizeCountryKey(v);
                        var crBase = cfg.countryResolveUrl;
                        var crSep = crBase.indexOf('?') >= 0 ? '&' : '?';
                        fetch(crBase + crSep + 'q=' + encodeURIComponent(v))
                            .then(function (r) { return r.json(); })
                            .then(function (j) {
                                if (!j || !j.ok || !j.iso) return;
                                if (normalizeCountryKey(countryEl.value) !== vn) return;
                                if ((postalEl.value || '').trim() === '') return;
                                resolvedIso = j.iso;
                                resolvedNorm = vn;
                                countryEl.setAttribute('data-bc-iso', j.iso);
                                syncIsoFromCountryField(countryEl);
                                refreshPostalFormat(postalEl);
                                onPostalMaybeLookup();
                            })
                            .catch(function () {});
                    }, 100);
                }

                countryEl.addEventListener('input', function () {
                    inferSeq++;
                    countryEl.removeAttribute('data-bc-iso');
                    showHint(cfg.postalHintId, false);
                    postalLookupSeq++;
                    resolvedIso = null;
                    resolvedNorm = null;
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                    scheduleCountryResolve();
                });
                countryEl.addEventListener('blur', function () {
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                    scheduleCountryResolve();
                    onPostalMaybeLookup();
                });
                countryEl.addEventListener('change', function () {
                    countryEl.removeAttribute('data-bc-iso');
                    resolvedIso = null;
                    resolvedNorm = null;
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                    scheduleCountryResolve();
                    onPostalMaybeLookup();
                });

                postalEl.addEventListener('input', function () {
                    inferSeq++;
                    if ((countryEl.value || '').trim()) {
                        scheduleCountryResolveFast();
                    }
                    scheduleInferCountryFromPostal(650);
                    onPostalMaybeLookup();
                });
                postalEl.addEventListener('blur', function () {
                    inferSeq++;
                    syncIsoFromCountryField(countryEl);
                    refreshPostalFormat(postalEl);
                    if ((countryEl.value || '').trim()) {
                        scheduleCountryResolveFast();
                    }
                    if (!(countryEl.value || '').trim()) {
                        scheduleInferCountryFromPostal(120);
                    }
                    onPostalMaybeLookup();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindPostalUi);
            } else {
                bindPostalUi();
            }

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