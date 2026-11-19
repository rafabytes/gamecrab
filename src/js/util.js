/** Utilitários compartilhados. */

export function announce(message) {
  const el = document.querySelector('[data-announcer]');
  if (!el) return;
  el.textContent = '';
  // Pequeno atraso para leitores de tela perceberem a mudança.
  window.setTimeout(() => {
    el.textContent = message;
  }, 60);
}

export async function copyText(text) {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try {
      ok = document.execCommand('copy');
    } catch {
      ok = false;
    }
    ta.remove();
    return ok;
  }
}

export function normalize(str = '') {
  return String(str)
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase();
}

export function stripTags(html = '') {
  return String(html).replace(/<[^>]*>/g, '');
}

export function storage(key, value) {
  try {
    if (value === undefined) return window.localStorage.getItem(key);
    window.localStorage.setItem(key, value);
  } catch {
    /* modo privado / bloqueado */
  }
  return null;
}

export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
