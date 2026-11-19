/** Alternância dia (disco prata) / noite (disco preto). */
import { storage } from './util';

const LABELS = {
  night: 'Ativar modo dia',
  day: 'Ativar modo noite',
};

function apply(theme, toggle) {
  document.documentElement.dataset.theme = theme;
  if (!toggle) return;
  toggle.setAttribute('aria-pressed', theme === 'day' ? 'true' : 'false');
  const label = toggle.querySelector('[data-theme-label]');
  if (label) label.textContent = LABELS[theme];
  toggle.title = LABELS[theme];
}

export function initTheme(announce) {
  const toggle = document.querySelector('[data-theme-toggle]');
  apply(document.documentElement.dataset.theme === 'day' ? 'day' : 'night', toggle);

  toggle?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'day' ? 'night' : 'day';
    const run = () => apply(next, toggle);
    if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.startViewTransition(run);
    } else {
      run();
    }
    storage('gc-theme', next);
    announce(next === 'day' ? 'Modo dia ativado' : 'Modo noite ativado');
  });

  // Acompanha a preferência do sistema enquanto o usuário não escolher.
  window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
    if (!storage('gc-theme')) apply(e.matches ? 'day' : 'night', toggle);
  });
}
