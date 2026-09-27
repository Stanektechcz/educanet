/* EDUCANET v55 · studentská vrstva
   - LVL prstenec podle XP
   - zámek stránky při samostatné práci a testu
   - průběžné ukládání rozpracované práce do localStorage
   - odpočet do konce hodiny / do deadlinu bonusu */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  var LOCK_KEY = 'edu55:lock';
  var root = document.body;
  var T = window.EduI18n;

  // -------------------------------------------------------------------
  // Odpočet času
  // -------------------------------------------------------------------
  function pad(n) { return (n < 10 ? '0' : '') + n; }

  function minutesLabel(n) {
    if (T) return EduI18n.trn({ one: '{n} minuta', few: '{n} minuty', other: '{n} minut' }, n);
    return n + ' ' + (n === 1 ? 'minuta' : (n >= 2 && n <= 4 ? 'minuty' : 'minut'));
  }

  // Čtečkám se čas oznamuje jen po celých minutách (ne každou sekundu).
  function clockLiveRegion(node) {
    if (node.__v55Live) return node.__v55Live;
    var out = node.querySelector('b');
    if (out) out.setAttribute('aria-hidden', 'true');
    var live = document.createElement('span');
    live.className = 'v55-sr-only';
    live.setAttribute('role', 'status');
    live.setAttribute('aria-live', 'polite');
    live.setAttribute('aria-atomic', 'true');
    node.appendChild(live);
    node.__v55Live = live;
    node.__v55Minute = null;
    return live;
  }

  function announceClock(node, left) {
    var live = clockLiveRegion(node);
    // Stejné minuty jako na displeji (3:05 → „3 minuty“), poslední minuta se hlásí jako 1.
    var minute = left <= 0 ? 0 : Math.max(1, Math.floor(left / 60));
    if (node.__v55Minute === minute) return;
    node.__v55Minute = minute;
    var label = node.getAttribute('data-v55-clock-label') || (T ? EduI18n.tr('do konce') : 'do konce');
    if (minute === 0) { live.textContent = T ? EduI18n.tr('Čas vypršel.') : 'Čas vypršel.'; return; }
    live.textContent = T ? EduI18n.tr('Zbývá {minutes} {label}.', { minutes: minutesLabel(minute), label: label }) : ('Zbývá ' + minutesLabel(minute) + ' ' + label + '.');
  }

  function tickClocks() {
    var nodes = document.querySelectorAll('[data-v55-clock]');
    if (!nodes.length) return;
    var now = Math.floor(Date.now() / 1000);
    nodes.forEach(function (node) {
      var end = parseInt(node.getAttribute('data-end') || '0', 10);
      var out = node.querySelector('b') || node;
      if (!end) { out.textContent = '—'; return; }
      var left = end - now;
      announceClock(node, left);
      if (left <= 0) { out.textContent = '0:00'; node.classList.add('is-over'); return; }
      out.textContent = Math.floor(left / 60) + ':' + pad(left % 60);
      node.classList.toggle('is-soon', left <= 300);
    });
  }

  // -------------------------------------------------------------------
  // Průběžné ukládání do localStorage
  // -------------------------------------------------------------------
  function storageKey(runner, form) {
    return 'edu55:work:' + (runner.getAttribute('data-session') || 'x') + ':' +
      (runner.getAttribute('data-student') || 'x') + ':' + (form.getAttribute('data-v55-autosave') || 'form');
  }

  function readStore(key) {
    try { return JSON.parse(localStorage.getItem(key) || '{}'); } catch (e) { return {}; }
  }

  function writeStore(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); return true; } catch (e) { return false; }
  }

  function setupAutosave(runner) {
    var forms = runner.querySelectorAll('[data-v55-autosave]');
    forms.forEach(function (form) {
      var key = storageKey(runner, form);
      var saved = readStore(key);
      var note = form.querySelector('[data-v55-autosave-note]');

      // Obnovení: server má přednost, z prohlížeče doplníme jen prázdná pole.
      Object.keys(saved.fields || {}).forEach(function (name) {
        var field = form.querySelector('[name="' + name.replace(/"/g, '\\"') + '"]');
        if (!field || field.type === 'checkbox' || field.type === 'hidden') return;
        if (String(field.value || '').trim() === '') field.value = saved.fields[name];
      });
      (saved.checks || []).forEach(function (value) {
        var box = form.querySelector('input[type="checkbox"][value="' + value + '"]');
        if (box && !box.checked) box.checked = true;
      });

      var save = function () {
        var data = { fields: {}, checks: [], at: Date.now() };
        form.querySelectorAll('textarea, input[type="text"], input:not([type])').forEach(function (field) {
          if (field.name && field.name !== 'csrf') data.fields[field.name] = field.value;
        });
        form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (box) { data.checks.push(box.value); });
        var ok = writeStore(key, data);
        var savedAt = T ? EduI18n.date(Date.now(), { hour: '2-digit', minute: '2-digit' }) : new Date().toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
        if (note) note.textContent = ok
          ? (T ? EduI18n.tr('Uloženo v prohlížeči · {time}', { time: savedAt }) : ('Uloženo v prohlížeči · ' + savedAt))
          : (T ? EduI18n.tr('Prohlížeč neukládá lokálně, spoléhej na tlačítko Uložit.') : 'Prohlížeč neukládá lokálně, spoléhej na tlačítko Uložit.');
      };
      var timer = null;
      form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(save, 600); });
      form.addEventListener('change', save);
      form.addEventListener('submit', function () {
        window.__edu55Submitting = true;
        try { localStorage.removeItem(key); } catch (e) { /* ignore */ }
      });
    });
  }

  // -------------------------------------------------------------------
  // Zámek: během samostatné práce a testu se neodchází pryč
  // -------------------------------------------------------------------
  function buildLockDialog() {
    var wrap = document.createElement('div');
    wrap.className = 'v55-lock-dialog';
    wrap.hidden = true;
    var tTitle = T ? EduI18n.tr('Teď pracuješ na hodině') : 'Teď pracuješ na hodině';
    var tBody = T ? EduI18n.tr('Rozpracovanou práci máš uloženou, ale odchod na jinou stránku tě z rozdělané práce vyhodí. Dokonči krok a odevzdej.') : 'Rozpracovanou práci máš uloženou, ale odchod na jinou stránku tě z rozdělané práce vyhodí. Dokonči krok a odevzdej.';
    var tStay = T ? EduI18n.tr('Zůstat u práce') : 'Zůstat u práce';
    var tLeave = T ? EduI18n.tr('Přesto odejít') : 'Přesto odejít';
    wrap.innerHTML =
      '<div class="v55-lock-card" role="alertdialog" aria-modal="true" aria-labelledby="v55-lock-title">' +
      '<strong id="v55-lock-title"></strong>' +
      '<p></p>' +
      '<div class="v55-lock-actions"><button type="button" class="btn primary" data-v55-stay></button>' +
      '<button type="button" class="btn secondary" data-v55-leave></button></div></div>';
    wrap.querySelector('#v55-lock-title').textContent = tTitle;
    wrap.querySelector('.v55-lock-card p').textContent = tBody;
    wrap.querySelector('[data-v55-stay]').textContent = tStay;
    wrap.querySelector('[data-v55-leave]').textContent = tLeave;
    document.body.appendChild(wrap);
    return wrap;
  }

  function setupLock() {
    if (!root || root.getAttribute('data-v55-lock') !== 'on') return;
    var dialog = buildLockDialog();
    var pending = null;
    var returnFocus = null;

    var open = function (href) {
      pending = href;
      returnFocus = document.activeElement;
      dialog.hidden = false;
      var stay = dialog.querySelector('[data-v55-stay]');
      if (stay) stay.focus();
    };
    var close = function () {
      dialog.hidden = true;
      pending = null;
      // Fokus se vrátí tam, odkud žák dialog otevřel.
      if (returnFocus && typeof returnFocus.focus === 'function' && document.contains(returnFocus)) {
        returnFocus.focus({ preventScroll: true });
      }
      returnFocus = null;
    };

    // Tab zůstává uvnitř alertdialogu, dokud je otevřený.
    dialog.addEventListener('keydown', function (ev) {
      if (ev.key !== 'Tab' || dialog.hidden) return;
      var items = [].slice.call(dialog.querySelectorAll('button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'));
      if (!items.length) return;
      var first = items[0];
      var last = items[items.length - 1];
      if (ev.shiftKey && (document.activeElement === first || !dialog.contains(document.activeElement))) {
        ev.preventDefault();
        last.focus();
      } else if (!ev.shiftKey && (document.activeElement === last || !dialog.contains(document.activeElement))) {
        ev.preventDefault();
        first.focus();
      }
    });

    dialog.addEventListener('click', function (ev) {
      if (ev.target.hasAttribute('data-v55-stay') || ev.target === dialog) { close(); return; }
      if (ev.target.hasAttribute('data-v55-leave')) {
        var href = pending;
        returnFocus = null;
        close();
        window.__edu55Submitting = true;
        if (href) window.location.href = href;
      }
    });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && !dialog.hidden) close(); });

    document.addEventListener('click', function (ev) {
      var link = ev.target.closest ? ev.target.closest('a[href]') : null;
      if (!link) return;
      if (link.hasAttribute('data-v55-allow')) return;
      var href = link.getAttribute('href') || '';
      if (href === '' || href.charAt(0) === '#' || /^(mailto:|tel:)/i.test(href)) return;
      ev.preventDefault();
      open(link.href);
    }, true);

    // Menu se během práce nedá proklikávat.
    document.querySelectorAll('[data-main-menu] a, [data-nav-dropdown]').forEach(function (node) {
      node.classList.add('v55-locked-nav');
      if (node.tagName === 'DETAILS') node.removeAttribute('open');
    });

    window.addEventListener('beforeunload', function (ev) {
      if (window.__edu55Submitting) return undefined;
      ev.preventDefault();
      ev.returnValue = '';
      return '';
    });

    try { sessionStorage.setItem(LOCK_KEY, '1'); } catch (e) { /* ignore */ }
  }

  // -------------------------------------------------------------------
  // Start
  // -------------------------------------------------------------------
  function init() {
    var runner = document.querySelector('[data-v55-runner]');
    if (runner) setupAutosave(runner);
    setupLock();
    tickClocks();
    if (document.querySelector('[data-v55-clock]')) setInterval(tickClocks, 1000);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
