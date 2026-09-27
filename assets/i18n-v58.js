/* v58/v59 · OPS-02 – překlady pro JS.
   Jazyk se čte z <html lang> (edu_html_lang()), NE z JSON dat.
   Staré klíčové katalogy: <script type="application/json" id="edu-i18n"> (edu_i18n_json()) → EduI18n.t(...).
   Nové msgid katalogy (v59): libovolný počet <script type="application/json" class="edu-tr-json" data-edu-tr="doména">
   (edu_tr_json()) se líně slučují do jedné mapy msgid → překlad při prvním použití EduI18n.tr()/trn(); starý
   jednoduchý blok <script type="application/json" id="edu-tr"> (dřívější formát {locale, tr:{...}}) se podporuje dál.
   Použití: window.EduI18n.t('lab.klic', {n: 3, jmeno: 'Eva'}, 'Český záložní text');
            window.EduI18n.tr('Uloženo.'); window.EduI18n.trn({one: '{n} bod', few: '{n} body', other: '{n} bodů'}, n). */
(function () {
  'use strict';
  var locale = (document.documentElement.getAttribute('lang') || 'cs').toLowerCase();
  var intlLocales = { cs: 'cs-CZ', en: 'en-GB', uk: 'uk-UA' };

  try {
    localStorage.setItem('edu_lang', locale);
  } catch (err) { /* soukromý režim / zakázané úložiště – offline stránka zůstane u výchozí čeho má */ }

  var messages = {};
  try {
    var el = document.getElementById('edu-i18n');
    if (el) {
      var parsed = JSON.parse(el.textContent || '{}');
      if (parsed && typeof parsed === 'object') messages = parsed.messages || {};
    }
  } catch (err) { messages = {}; }

  function pluralForm(n) {
    n = Math.abs(parseInt(n, 10) || 0);
    if (locale === 'cs') return n === 1 ? 'one' : (n >= 2 && n <= 4 ? 'few' : 'other');
    if (locale === 'uk') {
      var m10 = n % 10, m100 = n % 100;
      if (m10 === 1 && m100 !== 11) return 'one';
      if (m10 >= 2 && m10 <= 4 && !(m100 >= 12 && m100 <= 14)) return 'few';
      return 'many';
    }
    return n === 1 ? 'one' : 'other';
  }

  function t(key, params, fallback) {
    var value = Object.prototype.hasOwnProperty.call(messages, key) ? messages[key] : (fallback !== undefined ? fallback : key);
    params = params || {};
    if (value && typeof value === 'object') {
      var form = pluralForm(params.n);
      value = value[form] || value.other || value.many || '';
    }
    return String(value).replace(/\{([a-z0-9_]+)\}/gi, function (match, name) {
      return Object.prototype.hasOwnProperty.call(params, name) ? String(params[name]) : match;
    });
  }

  /* v59 · msgid = český text – sloučení všech katalogů proběhne líně (nejvýš jednou), teprve při prvním tr()/trn(). */
  var trMessages = null;
  var trBlocks = document.getElementsByClassName('edu-tr-json'); /* živá kolekce – bloky vložené později se dosloučí */
  var trMerged = 0;

  function mergeNewBlocks() {
    for (; trMerged < trBlocks.length; trMerged++) {
      try {
        var blockParsed = JSON.parse(trBlocks[trMerged].textContent || '{}');
        if (blockParsed && typeof blockParsed === 'object') {
          for (var msgid in blockParsed) if (Object.prototype.hasOwnProperty.call(blockParsed, msgid)) trMessages[msgid] = blockParsed[msgid];
        }
      } catch (err) { /* jeden vadný blok neshodí zbytek */ }
    }
  }

  function loadTrMessages() {
    if (trMessages !== null) { if (trMerged < trBlocks.length) mergeNewBlocks(); return trMessages; }
    trMessages = {};
    try {
      var legacy = document.getElementById('edu-tr');
      if (legacy) {
        var legacyParsed = JSON.parse(legacy.textContent || '{}');
        if (legacyParsed && typeof legacyParsed === 'object' && legacyParsed.tr && typeof legacyParsed.tr === 'object') {
          for (var k in legacyParsed.tr) if (Object.prototype.hasOwnProperty.call(legacyParsed.tr, k)) trMessages[k] = legacyParsed.tr[k];
        }
      }
    } catch (err) { /* poškozený legacy blok se ignoruje, nové bloky se přesto zpracují */ }
    mergeNewBlocks();
    return trMessages;
  }

  function format(value, params) {
    params = params || {};
    return String(value).replace(/\{([a-z0-9_]+)\}/gi, function (match, name) {
      return Object.prototype.hasOwnProperty.call(params, name) ? String(params[name]) : match;
    });
  }

  function tr(cs, params) {
    var catalog = loadTrMessages();
    var hit = Object.prototype.hasOwnProperty.call(catalog, cs) ? catalog[cs] : null;
    /* katalog má pro msgid plurálové tvary (jinde se volá trn()) – tvar podle params.n */
    if (hit && typeof hit === 'object' && params && params.n !== undefined && !isNaN(parseInt(params.n, 10))) {
      var form = hit[pluralForm(params.n)] || hit.other || hit.many;
      if (form) return format(form, params);
    }
    return format(typeof hit === 'string' && hit !== '' ? hit : cs, params);
  }

  /* csForms = {one: '…', few: '…', other: '…'} (česky); msgid = tvar other. */
  function trn(csForms, n, params) {
    params = params || {};
    if (!Object.prototype.hasOwnProperty.call(params, 'n')) params.n = n;
    var msgid = csForms.other || '';
    var catalog = loadTrMessages();
    var hit = Object.prototype.hasOwnProperty.call(catalog, msgid) ? catalog[msgid] : null;
    if (hit && typeof hit === 'object') {
      var value = hit[pluralForm(n)] || hit.other || hit.many;
      if (value) return format(value, params);
    } else if (typeof hit === 'string' && hit !== '') {
      return format(hit, params);
    }
    var csN = Math.abs(parseInt(n, 10) || 0);
    var csForm = csN === 1 ? 'one' : (csN >= 2 && csN <= 4 ? 'few' : 'other');
    return format(csForms[csForm] || msgid, params);
  }

  /* Jazyk obsahu prvku (nejbližší [lang] předek), jinak jazyk stránky – pro logiku, která se má chovat
     jinak uvnitř česky ponechaného výukového obsahu (edu_content_lang_attr()) než v okolním UI. */
  function contentLang(el) {
    var node = el;
    while (node && node.nodeType === 1) {
      var value = node.getAttribute && node.getAttribute('lang');
      if (value) return value;
      node = node.parentNode;
    }
    return locale;
  }

  function dateFmt(ts, opts) {
    try {
      return new Intl.DateTimeFormat(intlLocales[locale] || 'cs-CZ', opts || {}).format(new Date(ts));
    } catch (err) {
      return new Date(ts).toISOString();
    }
  }

  function numberFmt(n, opts) {
    try {
      return new Intl.NumberFormat(intlLocales[locale] || 'cs-CZ', opts || {}).format(n);
    } catch (err) {
      return String(n);
    }
  }

  window.EduI18n = {
    t: t, tr: tr, trn: trn, locale: locale, pluralForm: pluralForm,
    contentLang: contentLang, date: dateFmt, number: numberFmt
  };
})();
