'use strict';

(function () {
  var THEMES  = ['global.css', 'global2.css', 'global3.css'];
  var KEY     = 'fsl-theme';
  var DEFAULT = 'global3.css';

  function getLink() { return document.getElementById('theme-link'); }

  function getSaved() {
    var v = localStorage.getItem(KEY);
    return THEMES.indexOf(v) !== -1 ? v : DEFAULT;
  }

  /* Replace only the CSS filename, keeping any relative path prefix intact */
  function setCssHref(cssFile) {
    var link = getLink();
    if (!link) return;
    link.href = link.href.replace(/global\d*\.css(\?.*)?$/, cssFile);
  }

  function updateTabs(active) {
    document.querySelectorAll('.theme-tab').forEach(function (btn) {
      var isActive = btn.dataset.theme === active;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  /* Public — called by theme-tab click handlers (wired below) */
  window.setTheme = function (cssFile) {
    if (THEMES.indexOf(cssFile) === -1) return;
    localStorage.setItem(KEY, cssFile);
    setCssHref(cssFile);
    updateTabs(cssFile);
  };

  /* Legacy binary toggle — kept so old onclick="toggleTheme()" doesn't throw */
  window.toggleTheme = function () {
    var cur  = getSaved();
    var next = cur === 'global.css' ? 'global2.css' : 'global.css';
    window.setTheme(next);
  };

  /* ── Immediate FOUC prevention (runs synchronously in <head>) ── */
  setCssHref(getSaved());

  /* ── Wire tabs after DOM is ready ─────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    var active = getSaved();
    updateTabs(active);

    document.querySelectorAll('.theme-tab').forEach(function (btn) {
      btn.addEventListener('click', function () {
        window.setTheme(btn.dataset.theme);
      });
    });

    /* ── Mobile sidebar backdrop ─────────────────────────────── */
    var sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    var backdrop = document.createElement('div');
    backdrop.id = 'sidebar-backdrop';
    backdrop.style.cssText = [
      'position:fixed',
      'inset:0',
      'background:rgba(0,0,0,0.42)',
      'z-index:99',
      'display:none',
      'cursor:pointer'
    ].join(';');
    document.body.appendChild(backdrop);

    function syncBackdrop() {
      backdrop.style.display = sidebar.classList.contains('is-open') ? 'block' : 'none';
    }

    backdrop.addEventListener('click', function () {
      sidebar.classList.remove('is-open');
      syncBackdrop();
    });

    /* Watch sidebar class changes (toggle button handled by each page's inline JS) */
    var mo = new MutationObserver(syncBackdrop);
    mo.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
        sidebar.classList.remove('is-open');
        syncBackdrop();
      }
    });
  });
}());
