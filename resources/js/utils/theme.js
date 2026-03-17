const THEME_KEY = 'pc-theme';

export function getTheme() {
  try {
    return localStorage.getItem(THEME_KEY) || 'light';
  } catch {
    return 'light';
  }
}

export function setTheme(mode) {
  const root = document.documentElement;
  const body = document.body;
  const appEl = document.getElementById('app');
  const isDark = mode === 'dark';
  if (isDark) {
    root.setAttribute('data-pc-theme', 'dark');
    root.classList.add('pc-dark');
    body.setAttribute('data-pc-theme', 'dark');
    if (appEl) {
      appEl.setAttribute('data-pc-theme', 'dark');
      appEl.classList.add('pc-dark');
    }
  } else {
    root.removeAttribute('data-pc-theme');
    root.classList.remove('pc-dark');
    body.removeAttribute('data-pc-theme');
    if (appEl) {
      appEl.removeAttribute('data-pc-theme');
      appEl.classList.remove('pc-dark');
    }
  }
  try {
    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
  } catch (_) {}
}

export function initTheme() {
  const saved = getTheme();
  setTheme(saved);
}
