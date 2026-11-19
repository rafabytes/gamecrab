/**
 * Paleta de busca global (⌘K / Ctrl+K / "/").
 * <dialog> nativo (foco preso, Esc, inert) + padrão ARIA combobox/listbox.
 * Índice leve vindo de /wp-json/gamecrab/v1/index.
 */
import { createRoot } from 'react-dom/client';
import { useCallback, useEffect, useMemo, useRef, useState, useDeferredValue } from 'react';
import Icon from './Icon.jsx';
import { normalize } from '../util';

/** "Cheats de The Sims 4" → "S4", "Cheats de GTA San Andreas" → "SA", "Códigos de Dress to Impress" → "DI". */
function initials(title) {
  const words = title.replace(/^(Cheats|Códigos)\s+(de|do|da)\s+/i, '').split(/\s+/);
  const last = words[words.length - 1];
  if (/^\d+$/.test(last) && words.length > 1) return words[words.length - 2][0].toUpperCase() + last;
  const caps = words.filter((w) => /^[A-ZÀ-Ý]/.test(w));
  return (caps.length >= 2 ? caps.slice(-2).map((w) => w[0]).join('') : words[0].slice(0, 2)).toUpperCase();
}

let indexPromise = null;
function loadIndex() {
  if (!indexPromise) {
    const base = window.gcConfig?.rest || '/wp-json/gamecrab/v1/';
    indexPromise = fetch(`${base}index`, { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : []))
      .then((games) =>
        games.map((g) => ({
          ...g,
          _t: normalize(`${g.title} ${g.kicker} ${g.platforms.join(' ')}`),
          cheats: g.cheats.map((c) => ({ ...c, _n: normalize(c.n), _h: normalize(`${c.n} ${c.e} ${c.c} ${g.title}`) })),
        }))
      )
      .catch(() => {
        indexPromise = null;
        return [];
      });
  }
  return indexPromise;
}

function search(games, query) {
  const q = normalize(query.trim());
  if (!q) return { games: games.slice(0, 8), cheats: [] };
  const terms = q.split(/\s+/);
  const gameHits = games.filter((g) => terms.every((t) => g._t.includes(t)));
  const cheatHits = [];
  for (const g of games) {
    for (const c of g.cheats) {
      if (!terms.every((t) => c._h.includes(t))) continue;
      let score = 0;
      if (c._n.startsWith(q)) score += 6;
      if (c._n.includes(q)) score += 3;
      if (normalize(c.c).includes(q)) score += 4;
      if (g._t.includes(terms[0])) score += 1;
      cheatHits.push({ ...c, game: g, score });
    }
  }
  cheatHits.sort((a, b) => b.score - a.score);
  return { games: gameHits.slice(0, 5), cheats: cheatHits.slice(0, 30) };
}

function Palette({ api, swup }) {
  const dialogRef = useRef(null);
  const inputRef = useRef(null);
  const listRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const deferred = useDeferredValue(query);
  const [games, setGames] = useState(null);
  const [active, setActive] = useState(0);

  // API imperativa para o main.js
  useEffect(() => {
    api.open = (q = '') => {
      setQuery(q);
      setActive(0);
      setOpen(true);
    };
    api.onReady?.();
  }, [api]);

  useEffect(() => {
    const d = dialogRef.current;
    if (!d) return;
    if (open && !d.open) {
      d.showModal();
      requestAnimationFrame(() => {
        const input = inputRef.current;
        input?.focus();
        input?.setSelectionRange(input.value.length, input.value.length);
      });
      loadIndex().then(setGames);
    } else if (!open && d.open) {
      d.close();
    }
  }, [open]);

  const results = useMemo(() => (games ? search(games, deferred) : { games: [], cheats: [] }), [games, deferred]);
  const flat = useMemo(
    () => [
      ...results.games.map((g) => ({ type: 'game', id: `pg-${g.id}`, url: g.url, g })),
      ...results.cheats.map((c, i) => ({ type: 'cheat', id: `pc-${i}`, url: c.u, c })),
    ],
    [results]
  );

  useEffect(() => setActive(0), [deferred]);

  // Mantém a opção ativa visível
  useEffect(() => {
    const el = listRef.current?.querySelector(`[aria-selected="true"]`);
    el?.scrollIntoView({ block: 'nearest' });
  }, [active]);

  const go = useCallback(
    (url) => {
      setOpen(false);
      if (!url) return;
      if (swup) swup.navigate(url);
      else window.location.href = url;
    },
    [swup]
  );

  const onKeyDown = (e) => {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActive((a) => (flat.length ? (a + 1) % flat.length : 0));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActive((a) => (flat.length ? (a - 1 + flat.length) % flat.length : 0));
    } else if (e.key === 'Home' && e.ctrlKey) {
      setActive(0);
    } else if (e.key === 'End' && e.ctrlKey) {
      setActive(flat.length - 1);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (flat[active]) go(flat[active].url);
      else if (query.trim()) go(`${window.gcConfig?.search || '/?s='}${encodeURIComponent(query.trim())}`);
    }
  };

  let i = -1;
  const option = (item, children) => {
    i += 1;
    const idx = i;
    return (
      <div
        key={item.id}
        id={item.id}
        role="option"
        aria-selected={idx === active}
        className="gc-palette__opt"
        onMouseMove={() => idx !== active && setActive(idx)}
        onClick={() => go(item.url)}
      >
        {children}
      </div>
    );
  };

  const count = flat.length;

  return (
    <dialog
      ref={dialogRef}
      className="gc-palette"
      aria-label="Buscar jogos e cheats"
      onClose={() => setOpen(false)}
      onClick={(e) => e.target === dialogRef.current && setOpen(false)}
    >
      <div className="gc-palette__field">
        <Icon name="search" />
        <input
          ref={inputRef}
          type="text"
          role="combobox"
          aria-expanded="true"
          aria-controls="gc-palette-list"
          aria-autocomplete="list"
          aria-activedescendant={flat[active]?.id}
          aria-label="Buscar jogo ou cheat"
          placeholder="Busque um jogo, efeito ou código…"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={onKeyDown}
          autoComplete="off"
          spellCheck="false"
        />
      </div>

      <div ref={listRef} id="gc-palette-list" role="listbox" aria-label="Resultados" className="gc-palette__list">
        {games === null && <p className="gc-palette__empty">Carregando o índice…</p>}
        {games !== null && count === 0 && (
          <p className="gc-palette__empty">
            Nada encontrado para “{query}”. Pressione <kbd>Enter</kbd> para busca completa.
          </p>
        )}
        {results.games.length > 0 && (
          <div role="group" aria-labelledby="pg-label">
            <p id="pg-label" className="gc-palette__group">
              {query.trim() ? 'Jogos' : 'Todos os jogos'}
            </p>
            {results.games.map((g) =>
              option({ id: `pg-${g.id}`, url: g.url }, (
                <>
                  <span className="gc-palette__swatch" style={{ background: g.accent }} aria-hidden="true">
                    <Icon name="pad" />
                  </span>
                  <span>
                    <span className="gc-palette__title">{g.title}</span>
                    <span className="gc-palette__sub" style={{ display: 'block' }}>
                      {g.cheats.length} códigos · {g.platforms.join(', ')}
                    </span>
                  </span>
                  <Icon name="arrow" />
                </>
              ))
            )}
          </div>
        )}
        {results.cheats.length > 0 && (
          <div role="group" aria-labelledby="pc-label">
            <p id="pc-label" className="gc-palette__group">
              Cheats
            </p>
            {results.cheats.map((c, k) =>
              option({ id: `pc-${k}`, url: c.u }, (
                <>
                  <span className="gc-palette__swatch" style={{ background: c.game.accent }} aria-hidden="true">
                    {initials(c.game.title)}
                  </span>
                  <span style={{ minWidth: 0 }}>
                    <span className="gc-palette__title">{c.n}</span>
                    <span className="gc-palette__sub" style={{ display: 'block' }}>
                      {c.game.title}
                      {c.e ? ` — ${c.e}` : ''}
                    </span>
                  </span>
                  <span className="gc-palette__code">{c.c}</span>
                </>
              ))
            )}
          </div>
        )}
      </div>

      <div className="gc-palette__foot" aria-hidden="true">
        <span><kbd>↑</kbd><kbd>↓</kbd> navegar</span>
        <span><kbd>Enter</kbd> abrir</span>
        <span><kbd>Esc</kbd> fechar</span>
      </div>
      <p className="sr-only" role="status" aria-live="polite">
        {games !== null && query.trim() ? `${count} resultados` : ''}
      </p>
    </dialog>
  );
}

export function mountPalette(el, swup) {
  // A API é preenchida no primeiro efeito do componente.
  return new Promise((resolve) => {
    const api = { open: () => {}, onReady: () => resolve(api) };
    createRoot(el).render(<Palette api={api} swup={swup} />);
  });
}
