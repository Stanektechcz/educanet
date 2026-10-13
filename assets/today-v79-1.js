/* EDUCANET v79.1 · Dnešní hodina: na mobilu (< 768 px) sbalitelné sekce s pamětí stavu v localStorage (try/catch – bez úložiště
   vše rozbalené). Nadpis sekce dostane tlačítko (aria-expanded, aria-controls); na širší obrazovce se vše vrátí do původního stavu.
   Bez innerHTML, bez sítě; bez JS jsou všechny sekce rozbalené. */
(function () {
  'use strict';
  var root = document.querySelector('[data-lt791]');
  if (!root || !window.matchMedia) return;
  var mq = window.matchMedia('(max-width: 767px)');
  var KEY = 'edu.lt791.closed';
  function load() {
    try { var v = JSON.parse(window.localStorage.getItem(KEY) || '[]'); return Array.isArray(v) ? v : []; } catch (e) { return []; }
  }
  function save(list) {
    try { window.localStorage.setItem(KEY, JSON.stringify(list.slice(0, 40))); } catch (e) { /* bez úložiště jen bez paměti */ }
  }
  function parts(sec) {
    var h = sec.querySelector('h2');
    if (!h) return null;
    var card = h.closest('section') || sec;
    var top = h;
    while (top.parentNode && top.parentNode !== card) top = top.parentNode;
    return { h: h, card: card, top: top };
  }
  function enhance(sec, closed) {
    var p = parts(sec);
    if (!p || p.h.querySelector('.lt791-tg')) return;
    var key = sec.getAttribute('data-lt791-sec');
    var body = document.createElement('div');
    body.className = 'lt791-body';
    body.id = 'lt791-b-' + key;
    while (p.top.nextSibling) body.appendChild(p.top.nextSibling);
    p.card.appendChild(body);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'lt791-tg';
    btn.setAttribute('aria-controls', body.id);
    while (p.h.firstChild) btn.appendChild(p.h.firstChild);
    p.h.appendChild(btn);
    var open = closed.indexOf(key) < 0;
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    body.hidden = !open;
    btn.addEventListener('click', function () {
      var nowOpen = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');
      body.hidden = !nowOpen;
      var list = load().filter(function (k) { return k !== key; });
      if (!nowOpen) list.push(key);
      save(list);
    });
  }
  function restore(sec) {
    var p = parts(sec);
    var btn = p && p.h.querySelector('.lt791-tg');
    if (!btn) return;
    var body = p.card.querySelector('.lt791-body');
    while (btn.firstChild) p.h.appendChild(btn.firstChild);
    btn.remove();
    if (body) {
      while (body.firstChild) p.card.appendChild(body.firstChild);
      body.remove();
    }
  }
  function apply() {
    var secs = root.querySelectorAll('[data-lt791-sec]');
    var closed = load();
    for (var i = 0; i < secs.length; i++) { if (mq.matches) enhance(secs[i], closed); else restore(secs[i]); }
  }
  apply();
  if (mq.addEventListener) mq.addEventListener('change', apply); else if (mq.addListener) mq.addListener(apply);
}());
