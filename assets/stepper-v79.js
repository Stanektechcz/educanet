/* EDUCANET v79 · krokové postupy – jen vylepšení (bez JS vše funguje přes server): kontrola polí aktuálního kroku před odesláním
   (chyba u pole + souhrn nahoře s aria-live a odkazy), fokus na souhrn chyb nebo nadpis kroku po přechodu, ochrana před dvojím odesláním.
   Texty chyb dodává server (data-sp79-msg). Bez innerHTML, bez sítě a úložiště prohlížeče. */
(function () {
  'use strict';
  var summary = document.getElementById('sp79-errors');
  var list = summary ? summary.querySelector('ul') : null;

  function sameRadios(el) {
    return Array.prototype.filter.call(el.form ? el.form.elements : [], function (x) { return x.type === 'radio' && x.name === el.name; });
  }
  function message(el) {
    var src = el.type === 'radio' ? sameRadios(el).filter(function (x) { return x.hasAttribute('data-sp79-msg'); })[0] : el;
    return (src && src.getAttribute('data-sp79-msg')) || el.validationMessage;
  }
  function fields(form) {
    var root = form.querySelector('.sp79-panel:not([hidden])') || form;
    return Array.prototype.filter.call(root.querySelectorAll('input, select, textarea'), function (el) {
      return el.type !== 'hidden' && !el.disabled && el.willValidate;
    });
  }
  function clear(form) {
    Array.prototype.forEach.call(form.querySelectorAll('[data-sp79-jerr]'), function (n) { n.parentNode.removeChild(n); });
    Array.prototype.forEach.call(form.querySelectorAll('[data-sp79-jinv]'), function (el) {
      el.removeAttribute('aria-invalid');
      el.removeAttribute('data-sp79-jinv');
      var d = (el.getAttribute('aria-describedby') || '').split(' ').filter(function (id) { return id && id !== el.id + '-jerr'; }).join(' ');
      if (d) el.setAttribute('aria-describedby', d); else el.removeAttribute('aria-describedby');
    });
  }
  function collect(form) {
    var out = [], seen = {};
    fields(form).forEach(function (el) {
      if (el.checkValidity()) return;
      var target = el.type === 'radio' ? sameRadios(el)[0] : el;
      if (!target.id || seen[target.id]) return;
      seen[target.id] = true;
      out.push({ el: target, msg: message(el) });
    });
    return out;
  }
  function show(form, errs) {
    clear(form);
    while (list.firstChild) list.removeChild(list.firstChild);
    errs.forEach(function (x) {
      var li = document.createElement('li'), a = document.createElement('a');
      a.href = '#' + x.el.id;
      a.textContent = x.msg;
      li.appendChild(a);
      list.appendChild(li);
      var wrap = document.createElement('div'), p = document.createElement('p');
      wrap.className = 'ds78 sp79-ferr';
      wrap.setAttribute('data-sp79-jerr', '');
      p.className = 'ds-error';
      p.id = x.el.id + '-jerr';
      p.textContent = x.msg;
      wrap.appendChild(p);
      var group = /radio|checkbox/.test(x.el.type) ? x.el.closest('fieldset') : null;
      var anchor = group || x.el.closest('label') || x.el;
      anchor.parentNode.insertBefore(wrap, anchor.nextSibling);
      x.el.setAttribute('aria-invalid', 'true');
      x.el.setAttribute('data-sp79-jinv', '');
      x.el.setAttribute('aria-describedby', ((x.el.getAttribute('aria-describedby') || '') + ' ' + p.id).trim());
    });
    summary.hidden = false;
    summary.focus();
  }

  Array.prototype.forEach.call(document.querySelectorAll('form[data-sp79]'), function (form) {
    form.noValidate = true;
    form.addEventListener('submit', function (e) {
      if (form.getAttribute('data-sp79-busy')) { e.preventDefault(); return; }
      var btn = e.submitter;
      var check = btn ? btn.hasAttribute('data-sp79-validate') : true;
      if (check && summary && list) {
        var errs = collect(form);
        if (errs.length) { e.preventDefault(); show(form, errs); return; }
      }
      form.setAttribute('data-sp79-busy', '1');
    });
  });
  window.addEventListener('pageshow', function () {
    Array.prototype.forEach.call(document.querySelectorAll('form[data-sp79-busy]'), function (f) { f.removeAttribute('data-sp79-busy'); });
  });
  if (summary) {
    summary.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null;
      var el = a ? document.getElementById(a.getAttribute('href').slice(1)) : null;
      if (el) { e.preventDefault(); el.focus(); }
    });
  }
  if (summary && !summary.hidden) {
    summary.focus();
  } else if (/[?&]krok=\d/.test(location.search)) {
    var h = document.querySelector('.sp79-panel:not([hidden]) [data-sp79-h]');
    if (h) h.focus();
  }
})();
