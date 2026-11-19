/**
 * gameCrab — entrada principal do front.
 * Swup (navegação SPA-like com transições) + ilhas React carregadas sob demanda.
 */
import Swup from 'swup';
import SwupHeadPlugin from '@swup/head-plugin';
import SwupA11yPlugin from '@swup/a11y-plugin';
import SwupPreloadPlugin from '@swup/preload-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';
import SwupScrollPlugin from '@swup/scroll-plugin';
import SwupProgressPlugin from '@swup/progress-plugin';

import { initTheme } from './js/theme';
import { initHeader, updateNavCurrent } from './js/header';
import { initPage } from './js/page';
import { announce } from './js/util';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---------- Swup ---------- */
const swup = new Swup({
  containers: ['#swup'],
  animationSelector: '[class*="transition-"]',
  native: false,
  ignoreVisit: (url, { el } = {}) => {
    if (el?.closest('[data-no-swup]')) return true;
    if (el?.target === '_blank') return true;
    return /\/wp-(admin|login)|\/feed\/?$|\.(xml|pdf|zip|png|jpe?g|webp|gif|svg)(\?|$)/i.test(url);
  },
  plugins: [
    new SwupHeadPlugin({ persistAssets: true, awaitAssets: false }),
    new SwupA11yPlugin({ headingSelector: 'h1', respectReducedMotion: true }),
    new SwupPreloadPlugin({ preloadHoveredLinks: true, preloadInitialPage: false }),
    new SwupBodyClassPlugin(),
    new SwupScrollPlugin({
      doScrollingRightAway: false,
      animateScroll: { betweenPages: false, samePageWithHash: !reduceMotion, samePage: !reduceMotion },
      offset: 96,
    }),
    new SwupProgressPlugin({ delay: 250 }),
  ],
});
window.gcSwup = swup;

/* ---------- Ciclo de vida das páginas ---------- */
let cleanup = () => {};

function boot() {
  cleanup = initPage(swup);
  updateNavCurrent();
}

swup.hooks.before('content:replace', () => {
  cleanup();
});
swup.hooks.on('page:view', () => {
  boot();
});
// Fecha o menu mobile ao navegar.
swup.hooks.on('visit:start', () => {
  document.dispatchEvent(new CustomEvent('gc:close-menu'));
});

/* ---------- Paleta de busca (React, carregada sob demanda) ---------- */
let palettePromise = null;
function openPalette(query = '') {
  if (!palettePromise) {
    palettePromise = import('./js/react/palette.jsx').then((m) => m.mountPalette(document.getElementById('gc-palette-root'), swup));
  }
  palettePromise.then((api) => api.open(query));
}
window.gcOpenPalette = openPalette;

document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-open-search]');
  if (!trigger) return;
  e.preventDefault();
  openPalette();
});

document.addEventListener('keydown', (e) => {
  const tag = (e.target.tagName || '').toLowerCase();
  const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
  if ((e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey)) {
    e.preventDefault();
    openPalette();
  } else if (e.key === '/' && !typing) {
    e.preventDefault();
    openPalette();
  }
});

/* Pré-carrega a paleta (React) só na intenção de uso: mouse/foco/toque em um gatilho de busca.
   Assim páginas sem explorer (home, arquivos) não baixam nem executam o React no carregamento. */
const warmPalette = (e) => {
  if (!e.target.closest?.('[data-open-search], [data-search-form]')) return;
  import('./js/react/palette.jsx');
  ['pointerover', 'focusin', 'touchstart'].forEach((t) => document.removeEventListener(t, warmPalette));
};
['pointerover', 'focusin', 'touchstart'].forEach((t) => document.addEventListener(t, warmPalette, { passive: true }));

/* ---------- Init ---------- */
initTheme(announce);
initHeader();
boot();
