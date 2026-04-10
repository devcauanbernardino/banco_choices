(function () {
  var KEY = 'bancochoices-theme';

  function getStored() {
    try {
      return localStorage.getItem(KEY);
    } catch (e) {
      return null;
    }
  }

  function setStored(theme) {
    try {
      localStorage.setItem(KEY, theme);
    } catch (e) {
      /* ignore */
    }
  }

  function applyTheme(theme) {
    if (theme !== 'dark' && theme !== 'light') {
      theme = 'light';
    }
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
    setStored(theme);
    syncControls(theme);
  }

  function syncControls(theme) {
    var dark = theme === 'dark';
    document.querySelectorAll('.js-theme-toggle').forEach(function (el) {
      if (el.type === 'checkbox') {
        el.checked = dark;
      }
    });
    document.querySelectorAll('.js-theme-toggle-btn').forEach(function (btn) {
      btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
      btn.classList.toggle('is-dark', dark);
    });
    document.querySelectorAll('.js-theme-single-toggle').forEach(function (btn) {
      var icon = btn.querySelector('.js-theme-single-icon');
      var toDark = btn.getAttribute('data-aria-to-dark') || '';
      var toLight = btn.getAttribute('data-aria-to-light') || '';
      if (icon) {
        icon.textContent = dark ? 'dark_mode' : 'light_mode';
      }
      btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
      btn.setAttribute('aria-label', dark ? toLight : toDark);
    });
  }

  function initFromStorage() {
    var stored = getStored();
    if (stored === 'dark' || stored === 'light') {
      applyTheme(stored);
    } else {
      applyTheme('light');
    }
  }

  function bindThemeUi() {
    initFromStorage();

    document.querySelectorAll('.js-theme-toggle').forEach(function (el) {
      el.addEventListener('change', function () {
        applyTheme(el.checked ? 'dark' : 'light');
      });
    });

    document.querySelectorAll('.js-theme-toggle-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
      });
    });

    document.querySelectorAll('.js-theme-single-toggle').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var cur = document.documentElement.getAttribute('data-theme') || 'light';
        applyTheme(cur === 'dark' ? 'light' : 'dark');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindThemeUi);
  } else {
    bindThemeUi();
  }
})();
