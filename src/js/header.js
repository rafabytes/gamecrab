/** Header: menu mobile acessível, auto-hide no scroll e item ativo. */

export function initHeader() {
  const header = document.querySelector('[data-header]');
  const nav = document.querySelector('[data-nav]');
  const toggle = document.querySelector('[data-menu-toggle]');

  const setOpen = (open) => {
    if (!nav || !toggle) return;
    toggle.setAttribute('aria-expanded', String(open));
    nav.dataset.open = String(open);
    document.documentElement.style.overflow = open ? 'hidden' : '';
    if (open) nav.querySelector('a')?.focus();
  };

  toggle?.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
  document.addEventListener('gc:close-menu', () => setOpen(false));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && toggle?.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
      toggle.focus();
    }
  });
  window.matchMedia('(min-width: 960px)').addEventListener('change', (e) => e.matches && setOpen(false));

  // Esconde o header ao rolar para baixo; mostra ao rolar para cima.
  let lastY = window.scrollY;
  let ticking = false;
  window.addEventListener(
    'scroll',
    () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const y = window.scrollY;
        const menuOpen = toggle?.getAttribute('aria-expanded') === 'true';
        const hide = !menuOpen && y > 240 && y > lastY && !header.contains(document.activeElement);
        header.dataset.hidden = String(hide);
        lastY = y;
        ticking = false;
      });
    },
    { passive: true }
  );
}

export function updateNavCurrent() {
  const here = window.location.origin + window.location.pathname;
  document.querySelectorAll('.gc-nav a').forEach((a) => {
    const href = a.href.split('#')[0];
    const match = href === here || (href !== window.location.origin + '/' && here.startsWith(href) && href.length > window.location.origin.length + 1);
    if (match) a.setAttribute('aria-current', 'page');
    else a.removeAttribute('aria-current');
  });
}
