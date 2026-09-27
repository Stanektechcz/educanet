(() => {
  const current = document.querySelector('.v504-now');
  if (current && location.hash === '#goal-now') current.scrollIntoView({block:'center'});
  document.querySelectorAll('.v504-goal-choice button').forEach(btn => {
    btn.addEventListener('click', () => btn.setAttribute('aria-busy','true'), {once:true});
  });
})();
