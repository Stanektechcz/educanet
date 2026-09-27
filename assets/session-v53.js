/* EDUCANET v53 · vstup do hodiny: výběr místa a automatický školní e-mail */
(() => {
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const form = document.querySelector('[data-sess53-register]');
  if (!form) return;

  const seatInput = form.querySelector('[data-u51-seat-input]');
  const seatLabel = form.querySelector('[data-u51-seat-label]');
  form.querySelectorAll('[data-u51-seat]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (btn.disabled) return;
      form.querySelectorAll('[data-u51-seat].selected').forEach((b) => b.classList.remove('selected'));
      btn.classList.add('selected');
      seatInput.value = btn.dataset.u51Seat || '';
      if (seatLabel) seatLabel.textContent = btn.dataset.label || '';
    });
  });

  const first = form.querySelector('[data-sess53-first]');
  const last = form.querySelector('[data-sess53-last]');
  const email = form.querySelector('[data-sess53-email]');
  const domain = (email.placeholder.split('@')[1] || 'educanet.cz').trim();
  const slug = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '');
  let touched = false;
  email.addEventListener('input', () => { touched = email.value.trim() !== ''; });
  const sync = () => {
    if (touched) return;
    const f = slug(first.value), l = slug(last.value);
    email.value = f && l ? `${f}.${l}@${domain}` : '';
  };
  [first, last].forEach((el) => el.addEventListener('input', sync));

  form.addEventListener('submit', (e) => {
    if (!seatInput.value) {
      e.preventDefault();
      const room = form.querySelector('.u51-room');
      if (seatLabel) seatLabel.textContent = EduI18n.tr('Vyber prosím své místo');
      room?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
})();
