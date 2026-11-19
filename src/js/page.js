/**
 * Comportamentos por página (re-executados a cada navegação do Swup).
 * Retorna uma função de limpeza chamada antes da troca de conteúdo.
 */
import { prefersReducedMotion } from './util';

export function initPage() {
  const disposers = [];

  /* --- Ilha React: explorador de cheats --- */
  const explorer = document.querySelector('[data-cheat-explorer]');
  const dataEl = document.querySelector('[data-cheat-data]');
  if (explorer && dataEl) {
    let root = null;
    let cancelled = false;
    import('./react/explorer.jsx').then((m) => {
      if (cancelled) return;
      root = m.mountExplorer(explorer, JSON.parse(dataEl.textContent));
    });
    disposers.push(() => {
      cancelled = true;
      root?.unmount();
    });
  }

  /* --- Reveal ao rolar --- */
  const revealTargets = document.querySelectorAll('.gc-section .gc-sechead, .gc-grid-cases > *, .gc-platforms li, .gc-seal__list li, .gc-manifesto__title, .gc-manifesto__cols');
  if ('IntersectionObserver' in window && !prefersReducedMotion()) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in');
            io.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -8% 0px' }
    );
    revealTargets.forEach((el, i) => {
      // Só anima o que está fora da tela no carregamento (evita "piscar" o que já está visível).
      if (el.getBoundingClientRect().top > window.innerHeight * 0.92) {
        el.setAttribute('data-reveal', '');
        el.style.transitionDelay = `${(i % 4) * 60}ms`;
        io.observe(el);
      }
    });
    disposers.push(() => io.disconnect());
  }

  /* --- Scrollspy do sumário --- */
  const tocLinks = [...document.querySelectorAll('[data-toc-link]')];
  if (tocLinks.length) {
    // Busca por id a cada frame: robusto a re-renderizações do React.
    const ids = tocLinks.map((a) => decodeURIComponent(a.hash.slice(1)));
    let raf = 0;
    const update = () => {
      raf = 0;
      let current = 0;
      ids.forEach((id, i) => {
        const el = document.getElementById(id);
        if (el && el.getBoundingClientRect().top < 160) current = i;
      });
      tocLinks.forEach((a, i) => (i === current ? a.setAttribute('aria-current', 'true') : a.removeAttribute('aria-current')));
    };
    const onScroll = () => {
      if (!raf) raf = requestAnimationFrame(update);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    update();
    disposers.push(() => window.removeEventListener('scroll', onScroll));
  }

  /* --- Giro do CD (Web Animations API: roda no compositor) ---
     O hover muda só a velocidade (playbackRate), que preserva o ângulo atual:
     acelera/desacelera suavemente, sem "pular". */
  const cd = document.querySelector('[data-cd]');
  const disc = cd?.querySelector('.gc-cd__disc');
  if (disc && !prefersReducedMotion() && typeof disc.animate === 'function') {
    cd.classList.add('is-js');
    const spin = disc.animate([{ transform: 'rotate(0deg)' }, { transform: 'rotate(360deg)' }], {
      duration: 26000,
      iterations: Infinity,
    });
    const FAST = 8; // ~3,3 s por volta
    let raf = 0;
    const rampTo = (target) => {
      cancelAnimationFrame(raf);
      const from = spin.playbackRate;
      const t0 = performance.now();
      const dur = 700;
      const step = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        const eased = 1 - Math.pow(1 - p, 3);
        spin.playbackRate = from + (target - from) * eased;
        if (p < 1) raf = requestAnimationFrame(step);
      };
      raf = requestAnimationFrame(step);
    };
    const fast = () => rampTo(FAST);
    const slow = () => rampTo(1);
    cd.addEventListener('pointerenter', fast);
    cd.addEventListener('pointerleave', slow);
    const vis = new IntersectionObserver(([e]) => (e.isIntersecting ? spin.play() : spin.pause()));
    vis.observe(cd);
    disposers.push(() => {
      cancelAnimationFrame(raf);
      spin.cancel();
      vis.disconnect();
      cd.removeEventListener('pointerenter', fast);
      cd.removeEventListener('pointerleave', slow);
    });
  }

  /* --- Tilt do CD no hero --- */
  if (cd && !prefersReducedMotion() && window.matchMedia('(pointer: fine)').matches) {
    const onMove = (e) => {
      const r = cd.getBoundingClientRect();
      const x = (e.clientX - (r.left + r.width / 2)) / window.innerWidth;
      const y = (e.clientY - (r.top + r.height / 2)) / window.innerHeight;
      cd.style.setProperty('--ry', `${(x * 22).toFixed(2)}deg`);
      cd.style.setProperty('--rx', `${(-y * 22).toFixed(2)}deg`);
    };
    window.addEventListener('pointermove', onMove, { passive: true });
    disposers.push(() => window.removeEventListener('pointermove', onMove));
  }

  /* --- Contadores do hero --- */
  if (!prefersReducedMotion()) {
    document.querySelectorAll('[data-count]').forEach((el) => {
      const end = parseInt(el.dataset.count, 10) || 0;
      const fmt = new Intl.NumberFormat('pt-BR');
      const t0 = performance.now();
      const dur = 1400;
      const tick = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        el.textContent = fmt.format(Math.round(end * (1 - Math.pow(1 - p, 4))));
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }

  /* --- Hero search: digitar abre a paleta instantânea --- */
  document.querySelectorAll('.gc-hero__search input').forEach((input) => {
    const onInput = () => {
      if (input.value.trim().length >= 2 && window.gcOpenPalette) {
        const q = input.value;
        input.value = '';
        window.gcOpenPalette(q);
      }
    };
    input.addEventListener('input', onInput);
    disposers.push(() => input.removeEventListener('input', onInput));
  });

  return () => disposers.forEach((fn) => fn());
}
