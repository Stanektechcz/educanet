/* EDUCANET v58 · Linux Lab – přístupnost terminálu (A11Y-01..03).
 * Samostatný modul: čte/poslouchá DOM a vlastní události linux-v57.js ('lab57:topology', 'lab57:net'),
 * nic v linux-v57.js přímo nevolá. Nastavení jde do localStorage (vždy v try/catch), nikdy citlivá data.
 * Žádné innerHTML s daty – vše přes textContent/createElement. */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  var STORAGE_KEY = 'lab57.a11y.v1';
  var READ_LAST_KEY = 'p'; // Alt+Shift+P – zdokumentováno v nápovědě terminálu (viz linux_v57_views.php)

  function defaultSettings() {
    return { announce: 'all', plain: false, fontSize: 0, contrast: false, dyslexia: false };
  }

  function loadSettings() {
    var out = defaultSettings();
    try {
      var raw = window.localStorage.getItem(STORAGE_KEY);
      if (!raw) return out;
      var parsed = JSON.parse(raw);
      if (parsed && typeof parsed === 'object') {
        if (parsed.announce === 'errors' || parsed.announce === 'off' || parsed.announce === 'all') out.announce = parsed.announce;
        out.plain = !!parsed.plain;
        var fs = parseInt(parsed.fontSize, 10);
        if (fs >= 0 && fs <= 3) out.fontSize = fs;
        out.contrast = !!parsed.contrast;
        out.dyslexia = !!parsed.dyslexia;
      }
    } catch (e) { /* soukromý režim / nedostupné úložiště – použije se výchozí nastavení */ }
    return out;
  }

  function saveSettings(s) {
    try { window.localStorage.setItem(STORAGE_KEY, JSON.stringify(s)); } catch (e) { /* nastavení se jen neuloží pro příště */ }
  }

  function applySettings(root, s) {
    root.classList.toggle('lab57-plain', s.plain);
    root.classList.remove('lab57-fontsize-0', 'lab57-fontsize-1', 'lab57-fontsize-2', 'lab57-fontsize-3');
    root.classList.add('lab57-fontsize-' + s.fontSize);
    root.classList.toggle('lab57-contrast', s.contrast);
    root.classList.toggle('lab57-dyslexia', s.dyslexia);
  }

  function formatRole(role) {
    var s = String(role || '').replace(/[_-]+/g, ' ').trim();
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
  }

  // ------------------------------------------------------------------
  // A11Y-02: textová alternativa topologie (tabulka poslední cesty)
  // ------------------------------------------------------------------

  function deviceLabel(id, nodes) {
    var n = nodes[id];
    if (n && n.label) return String(n.label);
    var names = { pc: EduI18n.tr('Tento počítač'), switch: EduI18n.tr('Switch (místní síť)') };
    return names[id] || String(id);
  }
  function deviceAddr(id, nodes) {
    var n = nodes[id];
    return n && n.ip ? String(n.ip) : '–';
  }
  function appendCell(tr, text, className) {
    var td = document.createElement('td');
    td.textContent = text;
    if (className) td.className = className;
    tr.appendChild(td);
    return td;
  }
  // v59 · status je kód ('ok'/'fail'), ne přeložený text – text a CSS třída se odvozují až při vykreslení.
  function statusText(code) { return code === 'fail' ? EduI18n.tr('Bez odezvy') : EduI18n.tr('V pořádku'); }
  function renderNetTable(tbody, events, nodes) {
    while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
    var ev = events && events.length ? events[events.length - 1] : null;
    if (!ev) return;
    var hops = ev.hops || [];
    var rows = [];
    hops.forEach(function (id, i) {
      var isLastHop = i === hops.length - 1;
      var failedHere = !ev.ok && isLastHop && (!ev.fail || ev.fail === id);
      rows.push({ id: id, status: failedHere ? 'fail' : 'ok' });
    });
    if (!ev.ok && ev.fail && (rows.length === 0 || rows[rows.length - 1].id !== ev.fail)) {
      rows.push({ id: ev.fail, status: 'fail' });
    }
    if (rows.length === 0) {
      var tr0 = document.createElement('tr');
      var td0 = document.createElement('td');
      td0.colSpan = 5;
      td0.textContent = EduI18n.tr('Cestu se nepodařilo zjistit.');
      tr0.appendChild(td0);
      tbody.appendChild(tr0);
      return;
    }
    rows.forEach(function (r, i) {
      var tr = document.createElement('tr');
      appendCell(tr, String(i + 1));
      appendCell(tr, deviceLabel(r.id, nodes));
      appendCell(tr, deviceAddr(r.id, nodes));
      var statusCell = appendCell(tr, statusText(r.status), r.status === 'ok' ? 'lab57-net-ok' : 'lab57-net-fail');
      statusCell.setAttribute('data-status', r.status);
      appendCell(tr, '–');
      tbody.appendChild(tr);
    });
  }

  // ------------------------------------------------------------------
  // Inicializace jedné instance Labu
  // ------------------------------------------------------------------

  function initA11y(root) {
    var settings = loadSettings();
    applySettings(root, settings);

    var out = root.querySelector('[data-lab57-out]');
    var liveEl = root.querySelector('[data-lab57-live]');
    var announceSelect = root.querySelector('[data-lab57-a11y-announce]');
    var readBtn = root.querySelector('[data-lab57-a11y-read-last]');
    var plainChk = root.querySelector('[data-lab57-a11y-plain]');
    var fontSel = root.querySelector('[data-lab57-a11y-fontsize]');
    var contrastChk = root.querySelector('[data-lab57-a11y-contrast]');
    var dyslexiaChk = root.querySelector('[data-lab57-a11y-dyslexia]');

    if (announceSelect) announceSelect.value = settings.announce;
    if (plainChk) plainChk.checked = settings.plain;
    if (fontSel) fontSel.value = String(settings.fontSize);
    if (contrastChk) contrastChk.checked = settings.contrast;
    if (dyslexiaChk) dyslexiaChk.checked = settings.dyslexia;

    function persist() { saveSettings(settings); applySettings(root, settings); }
    if (announceSelect) announceSelect.addEventListener('change', function () { settings.announce = announceSelect.value; persist(); });
    if (plainChk) plainChk.addEventListener('change', function () { settings.plain = plainChk.checked; persist(); });
    if (fontSel) fontSel.addEventListener('change', function () { settings.fontSize = parseInt(fontSel.value, 10) || 0; persist(); });
    if (contrastChk) contrastChk.addEventListener('change', function () { settings.contrast = contrastChk.checked; persist(); });
    if (dyslexiaChk) dyslexiaChk.addEventListener('change', function () { settings.dyslexia = dyslexiaChk.checked; persist(); });

    function blockText(el) { return (el.textContent || '').replace(/\s+/g, ' ').trim(); }
    function isAnnounceable(el) { return !!(el && el.nodeType === 1 && !el.classList.contains('lab57-cmdline')); }
    function lastBlock() {
      if (!out) return null;
      for (var i = out.children.length - 1; i >= 0; i--) if (isAnnounceable(out.children[i])) return out.children[i];
      return null;
    }
    function announceBlock(el) {
      if (!liveEl) return;
      var mode = settings.announce;
      if (mode === 'off') return;
      var isErr = el.classList.contains('is-err');
      if (mode === 'errors' && !isErr) return;
      var text = blockText(el);
      if (text === '') return;
      if (text.length > 220) text = text.slice(0, 220) + '…';
      liveEl.textContent = (isErr ? EduI18n.tr('Chyba:') + ' ' : '') + text;
    }
    function readLast() {
      if (!liveEl) return;
      var el = lastBlock();
      if (!el) { liveEl.textContent = EduI18n.tr('Terminál je zatím prázdný.'); return; }
      var text = blockText(el);
      liveEl.textContent = text ? (text.length > 500 ? text.slice(0, 500) + '…' : text) : EduI18n.tr('Bez textového obsahu.');
    }
    if (readBtn) readBtn.addEventListener('click', readLast);
    root.addEventListener('keydown', function (e) {
      if (e.altKey && e.shiftKey && !e.ctrlKey && !e.metaKey && String(e.key).toLowerCase() === READ_LAST_KEY) {
        e.preventDefault();
        readLast();
      }
    });
    if (out && window.MutationObserver) {
      var mo = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
          Array.prototype.forEach.call(m.addedNodes, function (node) { if (isAnnounceable(node)) announceBlock(node); });
        });
      });
      mo.observe(out, { childList: true });
    }

    // A11Y-01: v terminálu zobraz context.label a roli hráče u týmových kontextů (pokud jsou k dispozici).
    var contextEl = root.querySelector('[data-lab57-term-context]');
    if (contextEl) {
      document.addEventListener('lab57:context', function (e) {
        if (!e.detail || e.detail.root !== root || !e.detail.context) return;
        var ctx = e.detail.context;
        if (!ctx.shared) { contextEl.hidden = true; return; }
        var role = formatRole(ctx.role);
        contextEl.textContent = String(ctx.label || EduI18n.tr('Týmový režim')) + (role ? ' · ' + EduI18n.tr('role: {role}', {role: role}) : '');
        contextEl.hidden = false;
      });
    }
    var hintEl = root.querySelector('[data-lab57-adaptive-hint]');
    var hintTextEl = root.querySelector('[data-lab57-adaptive-hint-text]');
    if (hintEl && hintTextEl) {
      document.addEventListener('lab57:adaptive-hint', function (e) {
        if (!e.detail || e.detail.root !== root || !e.detail.hint || !e.detail.hint.text) return;
        hintTextEl.textContent = String(e.detail.hint.text);
        hintEl.hidden = false;
      });
    }

    // A11Y-02: tabulka poslední síťové cesty.
    var tableBody = root.querySelector('[data-lab57-net-table-body]');
    var topologyNodes = {};
    document.addEventListener('lab57:topology', function (e) {
      if (!e.detail || e.detail.root !== root) return;
      topologyNodes = {};
      (e.detail.topology.nodes || []).forEach(function (n) { topologyNodes[n.id] = n; });
    });
    document.addEventListener('lab57:net', function (e) {
      if (!e.detail || e.detail.root !== root || !tableBody) return;
      renderNetTable(tableBody, e.detail.events, topologyNodes);
    });
  }

  function ready() {
    document.querySelectorAll('[data-lab57]').forEach(initA11y);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
})();
