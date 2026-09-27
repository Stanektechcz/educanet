/* EDUCANET v58 · Linux Lab offline – ovládání stránky lab-offline.html. Vyžaduje už načtené
 * assets/linux-core-v58.js a assets/linux-core-cmds-v58.js. DOM se plní jen přes textContent/
 * createElement (žádné innerHTML s daty), žádný vzdálený fetch (jen assets/lab-manual-v58.json
 * ze stejného originu). Nezávislé na assets/linux-v57.js (online terminál). */
(function () {
  'use strict';

  // v59 · OPS-02: samostatný (ne EduI18n) překlad UI téhle offline stránky – žádné assets/i18n-v58.js
  // tady neběží. Jazyk čteme jen z localStorage.edu_lang (zrcadlí ho hlavní appka), katalog stahujeme
  // ze stejného originu (assets/lab-offline-i18n-v59.json, export tools/v59_export_offline_i18n.php).
  // Obsah příručky (summary/synopsis/about/examples z lab-manual-v58.json) se NEPŘEKLÁDÁ – zůstává cs.
  var OFF_LOCALE = 'cs';
  try {
    var storedLang = window.localStorage.getItem('edu_lang');
    if (storedLang === 'en' || storedLang === 'uk') OFF_LOCALE = storedLang;
  } catch (e) { /* soukromý režim / nedostupné úložiště – zůstane čeština */ }
  var OFF_CATALOG = null;
  // Stejné jméno/rozhraní jako window.EduI18n jinde (extrakce msgid v tools/v59_i18n_audit.php hledá
  // přesně EduI18n.tr(…)/EduI18n.trn(…)), ale čte z OFF_CATALOG (JSON staženého ze stejného originu),
  // ne ze scriptovacích bloků edu-tr-json – tahle stránka nemá vlastní PHP request. window.EduI18n
  // tu nikdy nebude nastavené (assets/i18n-v58.js se na offline stránce nenačítá), fallback je vždy použit.
  var EduI18n = window.EduI18n || {
    locale: OFF_LOCALE,
    tr: function (cs, params) {
      var text = cs;
      if (OFF_LOCALE !== 'cs' && OFF_CATALOG && OFF_CATALOG[OFF_LOCALE] && typeof OFF_CATALOG[OFF_LOCALE][cs] === 'string' && OFF_CATALOG[OFF_LOCALE][cs] !== '') {
        text = OFF_CATALOG[OFF_LOCALE][cs];
      }
      if (params) {
        Object.keys(params).forEach(function (k) { text = text.split('{' + k + '}').join(String(params[k])); });
      }
      return text;
    },
    trn: function (forms, n, params) {
      params = params || {};
      if (!('n' in params)) params.n = n;
      var a = Math.abs(parseInt(n, 10) || 0);
      var form = a === 1 ? forms.one : (a >= 2 && a <= 4 ? (forms.few || forms.other) : forms.other);
      return this.tr(form || forms.other, params);
    }
  };
  document.documentElement.lang = OFF_LOCALE;

  // v59 · A11Y-04: statický text stránky (HTML zůstává česky ve zdroji – viz tools/v58_offline_audit.php),
  // přeložíme ho jen v DOM, když je aktivní jazyk en/uk. Obsah příručky příkazů zůstává česky
  // (edu_content_lang_attr() ekvivalent – kontejner dostane lang="cs").
  function setText(id, text) { var node = document.getElementById(id); if (node) node.textContent = text; }
  function setAriaLabel(id, text) { var node = document.getElementById(id); if (node) node.setAttribute('aria-label', text); }
  // Doslovné EduI18n.tr volání s řetězcovým literálem (ne přes proměnnou/slovník) – tools/v59_i18n_audit.php
  // extrahuje msgid jen z řetězcových literálů předaných přímo EduI18n.tr()/EduI18n.trn().
  function applyStaticI18n() {
    if (OFF_LOCALE === 'cs') return;
    setText('lab58off-skip-link', EduI18n.tr('Přeskočit na příkazovou řádku'));
    setText('lab58off-h1', EduI18n.tr('Offline trénink terminálu'));
    setText('lab58off-disclaimer-strong', EduI18n.tr('Offline trénink'));
    setText('lab58off-disclaimer-rest', EduI18n.tr('– postup se do školy neukládá; kódy úloh ověřuje jen online Linux Lab. Pískoviště běží jen v tomto prohlížeči a nic neposílá na server.'));
    setText('lab58off-back-text', EduI18n.tr('Zpět do online Linux Labu'));
    setText('lab58off-terminal-h', EduI18n.tr('Terminál (pískoviště)'));
    setText('lab58off-hint', EduI18n.tr('Šipky ↑ ↓: historie příkazů. Tab: doplnění názvu. Ctrl+L: vymaže obrazovku.'));
    setText('lab58off-input-label', EduI18n.tr('Příkaz pro terminál'));
    setText('lab58off-run-btn', EduI18n.tr('Spustit'));
    setText('lab58off-clear', EduI18n.tr('Vymazat obrazovku'));
    setText('lab58off-reset', EduI18n.tr('Začít znovu'));
    setText('lab58off-manual-h', EduI18n.tr('Příručka příkazů (offline)'));
    setText('lab58off-search-label', EduI18n.tr('Hledat příkaz'));
    setText('lab58off-manual-loading', EduI18n.tr('Příručka se načítá…'));
    setText('lab58off-footer-text', EduI18n.tr('Postup se ukládá jen na tomto zařízení (localStorage tohoto prohlížeče) – žádná data se neposílají na server ani do školy. Verze pískoviště 58.0.'));
    setAriaLabel('lab58off-nav', EduI18n.tr('Návrat do aplikace'));
    setAriaLabel('lab58off-output', EduI18n.tr('Výstup terminálu'));
    document.title = EduI18n.tr('Offline trénink – Linux Lab · EDUCANET');
    var metaDesc = document.getElementById('lab58off-meta-description');
    if (metaDesc) metaDesc.setAttribute('content', EduI18n.tr('Offline pískoviště Linux Labu: terminál a příručka příkazů fungující bez připojení k internetu.'));
    var manualList = document.getElementById('lab58off-manual-list');
    if (manualList) manualList.lang = 'cs';
  }

  var Core = window.LinuxCoreV58;
  var session = Core.load() || Core.createSession();

  var output = document.getElementById('lab58off-output');
  var form = document.getElementById('lab58off-form');
  var input = document.getElementById('lab58off-input');
  var promptEl = document.getElementById('lab58off-prompt');
  var clearBtn = document.getElementById('lab58off-clear');
  var resetBtn = document.getElementById('lab58off-reset');
  var manualList = document.getElementById('lab58off-manual-list');
  var manualStatus = document.getElementById('lab58off-manual-status');
  var manualSearch = document.getElementById('lab58off-search');

  var historyPos = null;
  var draft = '';

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }
  function clearNode(node) { while (node.firstChild) node.removeChild(node.firstChild); }
  function scrollToEnd() { output.scrollTop = output.scrollHeight; }
  function updatePrompt() { promptEl.textContent = Core.prompt(session); }

  // Terminál ----------------------------------------------------------------
  function appendChunk(text, cls) {
    if (!text) return;
    output.appendChild(el('span', cls, text));
  }
  function appendCommandLine(line) {
    output.appendChild(el('div', 'lab58off-line-cmd', Core.prompt(session) + line));
  }
  function appendTips(tips) {
    (tips || []).forEach(function (t) { output.appendChild(el('div', 'lab58off-line-tip', EduI18n.tr('Tip: {tip}', {tip: t}))); });
  }
  function printBanner() {
    output.appendChild(el('div', '', EduI18n.tr('Offline pískoviště Linux Labu. Napiš help pro nápovědu, cat vitej.txt pro úvod.')));
  }

  function runCommand(line) {
    appendCommandLine(line);
    var result = Core.runLine(session, line);
    if (result.clear) clearNode(output);
    result.chunks.forEach(function (chunk) { appendChunk(chunk[1], chunk[0] === 2 ? 'lab58off-line-err' : ''); });
    appendTips(result.tips);
    updatePrompt();
    Core.save(session);
    scrollToEnd();
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var line = input.value;
    input.value = '';
    historyPos = null;
    if (line.trim() === '') { appendCommandLine(''); scrollToEnd(); return; }
    runCommand(line);
  });

  function historyNav(dir) {
    var hist = session.history;
    if (!hist.length) return;
    if (historyPos === null) { draft = input.value; historyPos = hist.length; }
    historyPos += dir;
    if (historyPos < 0) historyPos = 0;
    if (historyPos >= hist.length) { historyPos = hist.length; input.value = draft; return; }
    input.value = hist[historyPos];
    input.setSelectionRange(input.value.length, input.value.length);
  }
  function doClear() { clearNode(output); input.focus(); }

  input.addEventListener('keydown', function (ev) {
    if (ev.key === 'ArrowUp') { ev.preventDefault(); historyNav(-1); return; }
    if (ev.key === 'ArrowDown') { ev.preventDefault(); historyNav(1); return; }
    if (ev.key === 'Tab' && !ev.shiftKey) {
      var res = Core.complete(session, input.value, input.selectionStart || input.value.length);
      var multi = res.list && res.list.length > 1;
      if (res.value !== input.value || multi) {
        ev.preventDefault();
        input.value = res.value;
        input.setSelectionRange(res.caret, res.caret);
        if (multi) { output.appendChild(el('div', '', res.list.join('  '))); scrollToEnd(); }
      }
      return; // beze změny necháme Tab přesunout fokus dál – žádná klávesová past
    }
    if (ev.key === 'l' && ev.ctrlKey) { ev.preventDefault(); doClear(); }
  });

  clearBtn.addEventListener('click', doClear);
  resetBtn.addEventListener('click', function () {
    var ok = window.confirm(EduI18n.tr('Opravdu chceš smazat pískoviště a začít znovu? Vlastní soubory v offline tréninku se ztratí.'));
    if (!ok) return;
    Core.reset(session);
    historyPos = null;
    clearNode(output);
    printBanner();
    updatePrompt();
    input.focus();
  });

  function bootPrompt() {
    applyStaticI18n();
    updatePrompt();
    printBanner();
    input.focus();
  }
  if (OFF_LOCALE === 'cs') {
    bootPrompt();
  } else {
    fetch('assets/lab-offline-i18n-v59.json?v=59.0').then(function (r) {
      if (!r.ok) throw new Error('http ' + r.status);
      return r.json();
    }).then(function (data) { OFF_CATALOG = data; }).catch(function () { /* offline bez katalogu – zůstane čeština */ }).then(bootPrompt);
  }

  // Příručka ------------------------------------------------------------------
  function appendDefinition(dl, term, text) {
    if (!text) return;
    dl.appendChild(el('dt', '', term));
    dl.appendChild(el('dd', '', text));
  }
  function buildCommandDetails(name, cmd) {
    var details = document.createElement('details');
    details.className = 'lab58off-cmd';
    details.dataset.search = (name + ' ' + (cmd.summary || '')).toLowerCase();
    var summary = document.createElement('summary');
    summary.appendChild(el('span', '', name));
    summary.appendChild(el('span', 'lab58off-cmd-summary-text', cmd.summary || ''));
    details.appendChild(summary);
    var body = el('div', 'lab58off-cmd-body');
    var dl = document.createElement('dl');
    appendDefinition(dl, EduI18n.tr('Použití'), cmd.synopsis);
    appendDefinition(dl, EduI18n.tr('Popis'), cmd.about);
    body.appendChild(dl);
    if (cmd.examples && cmd.examples.length) {
      body.appendChild(el('p', '', EduI18n.tr('Příklady:')));
      var ul = document.createElement('ul');
      cmd.examples.forEach(function (pair) {
        var li = document.createElement('li');
        var code = document.createElement('code');
        code.textContent = pair[0];
        li.appendChild(code);
        li.appendChild(document.createTextNode(' – ' + pair[1]));
        ul.appendChild(li);
      });
      body.appendChild(ul);
    }
    if (Core.knownCommandNames().indexOf(name) === -1) {
      body.appendChild(el('p', 'lab58off-hint', EduI18n.tr('Tento příkaz offline pískoviště nepodporuje – slouží jen jako přehled.')));
    }
    details.appendChild(body);
    return details;
  }
  function buildManual(data) {
    Core.setManual(data);
    clearNode(manualList);
    var byCat = {};
    Object.keys(data.commands || {}).forEach(function (name) {
      var cat = (data.commands[name] || {}).cat || '?';
      (byCat[cat] = byCat[cat] || []).push(name);
    });
    var categories = data.categories || {};
    var catKeys = Object.keys(categories).sort(function (a, b) {
      return (categories[a].label || a).localeCompare(categories[b].label || b, 'cs');
    });
    catKeys.forEach(function (catKey) {
      var names = byCat[catKey];
      if (!names || !names.length) return;
      var icon = categories[catKey].icon ? categories[catKey].icon + ' ' : '';
      manualList.appendChild(el('h3', 'lab58off-cat-heading', icon + categories[catKey].label));
      names.sort().forEach(function (name) { manualList.appendChild(buildCommandDetails(name, data.commands[name])); });
    });
  }
  manualSearch.addEventListener('input', function () {
    var q = manualSearch.value.trim().toLowerCase();
    var items = manualList.querySelectorAll('.lab58off-cmd');
    var shown = 0;
    items.forEach(function (item) {
      var match = q === '' || item.dataset.search.indexOf(q) !== -1;
      item.hidden = !match;
      if (match) shown++;
    });
    manualList.querySelectorAll('.lab58off-cat-heading').forEach(function (h) {
      var next = h.nextElementSibling, has = false;
      while (next && !next.classList.contains('lab58off-cat-heading')) { if (!next.hidden) has = true; next = next.nextElementSibling; }
      h.hidden = !has;
    });
    manualStatus.textContent = q === '' ? '' : EduI18n.tr('Nalezeno příkazů: {n}', {n: shown});
  });

  fetch('assets/lab-manual-v58.json?v=58.0').then(function (r) {
    if (!r.ok) throw new Error('http ' + r.status);
    return r.json();
  }).then(buildManual).catch(function () {
    clearNode(manualList);
    manualList.appendChild(el('p', '', EduI18n.tr('Příručku se teď nepodařilo načíst. Až se jednou připojíš k internetu a stránku načteš, příště bude fungovat i offline.')));
  });
})();
