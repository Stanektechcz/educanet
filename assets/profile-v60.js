(function () {
  'use strict';

  // v60: progresivní vylepšení – po přechodu na záložku (?tab=…) přesune fokus na nadpis
  // panelu, aby čtečka obrazovky ohlásila novou záložku. Bez ?tab v URL nic nedělá.
  function focusPanelTitle() {
    var params = new URLSearchParams(window.location.search);
    if (!params.has('tab')) return;
    var title = document.getElementById('p60-panel-title');
    if (!title) return;
    title.focus();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', focusPanelTitle);
  } else {
    focusPanelTitle();
  }
})();
