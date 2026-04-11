(function () {
  var KEY = 'bancochoices-sidebar-collapsed';
  var html = document.documentElement;

  function forEachSidebarTooltipTarget(sidebar, fn) {
    if (!sidebar) return;
    var sel = [
      'a.app-sidebar-brand-link',
      'a.app-sidebar-link',
      'button.app-sidebar-collapse-btn.js-sidebar-toggle',
      '.app-sidebar-section--lang .bc-lang-selector > button.dropdown-toggle',
    ].join(', ');
    sidebar.querySelectorAll(sel).forEach(fn);
  }

  function disposeSidebarTooltips() {
    var sidebar = document.getElementById('appSidebarDesktop');
    if (!sidebar || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
    forEachSidebarTooltipTarget(sidebar, function (el) {
      var inst = bootstrap.Tooltip.getInstance(el);
      if (inst) inst.dispose();
    });
  }

  function refreshSidebarTooltips() {
    disposeSidebarTooltips();
    if (!html.classList.contains('sidebar-collapsed')) return;
    var sidebar = document.getElementById('appSidebarDesktop');
    if (!sidebar || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
    forEachSidebarTooltipTarget(sidebar, function (el) {
      var fromTitle = (el.getAttribute('title') || '').trim();
      var fromAria = (el.getAttribute('aria-label') || '').trim();
      var text = fromTitle || fromAria;
      if (!text) return;
      var opts = {
        placement: 'right',
        container: document.body,
        customClass: 'app-sidebar-collapsed-tooltip',
      };
      if (!fromTitle && fromAria) {
        opts.title = fromAria;
      }
      new bootstrap.Tooltip(el, opts);
    });
  }

  function apply(collapsed) {
    html.classList.toggle('sidebar-collapsed', collapsed);
    try {
      localStorage.setItem(KEY, collapsed ? '1' : '0');
    } catch (e) {
      /* ignore */
    }
    document.querySelectorAll('.js-sidebar-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      btn.setAttribute('title', collapsed ? 'Expandir painel lateral' : 'Recolher painel lateral');
      var icon = btn.querySelector('.app-sidebar-collapse-ico');
      if (icon) {
        icon.textContent = collapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
      }
      var label = btn.querySelector('.app-sidebar-collapse-label');
      if (label) {
        label.textContent = collapsed ? 'Expandir painel' : 'Recolher painel';
      }
    });
    refreshSidebarTooltips();
  }

  function init() {
    var collapsed = false;
    try {
      collapsed = localStorage.getItem(KEY) === '1';
    } catch (e) {
      /* ignore */
    }
    apply(collapsed);

    document.querySelectorAll('.js-sidebar-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        apply(!html.classList.contains('sidebar-collapsed'));
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
