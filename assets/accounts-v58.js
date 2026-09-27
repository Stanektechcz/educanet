// v58 · tlačítko „Vytisknout“ na stránce kartiček (bez inline skriptu).
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-a58-print]').forEach(function (button) {
    button.addEventListener('click', function () { window.print(); });
  });
});
