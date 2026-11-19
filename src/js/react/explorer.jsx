/**
 * CheatExplorer — assume a lista de cheats renderizada no servidor e adiciona:
 * busca instantânea, filtro por plataforma, "só confirmados", copiar código
 * e o replay animado das sequências de botões.
 */
import { createRoot } from 'react-dom/client';
import { useEffect, useMemo, useRef, useState, useDeferredValue } from 'react';
import Icon from './Icon.jsx';
import { announce, copyText, normalize, stripTags, storage, prefersReducedMotion } from '../util';

const ALL = '__all';

function Highlight({ text, query }) {
  if (!query) return text;
  const n = normalize(text);
  const q = normalize(query);
  const i = n.indexOf(q);
  if (i < 0) return text;
  return (
    <>
      {text.slice(0, i)}
      <mark className="gc-mark">{text.slice(i, i + q.length)}</mark>
      {text.slice(i + q.length)}
    </>
  );
}

function Sequence({ entry, lit }) {
  const labels = entry.tokens.map((t) => t.l).join(', ');
  return (
    <span className="gc-seq" role="img" aria-label={labels}>
      {entry.tokens.map((t, i) => (
        <kbd
          key={i}
          aria-hidden="true"
          className={`gc-key ${t.k.split(' ').map((k) => `gc-key--${k}`).join(' ')}${lit === i ? ' is-lit' : ''}${lit > i ? ' is-done' : ''}`}
        >
          {t.g}
        </kbd>
      ))}
    </span>
  );
}

function CodeRow({ label, entry, cheatName }) {
  const [copied, setCopied] = useState(false);
  const [lit, setLit] = useState(-1);
  const timer = useRef();

  useEffect(() => () => clearInterval(timer.current), []);

  const onCopy = async () => {
    const ok = await copyText(entry.copy);
    setCopied(ok);
    announce(ok ? `Código de ${cheatName} copiado` : 'Não foi possível copiar');
    window.setTimeout(() => setCopied(false), 1800);
  };

  const onPlay = () => {
    clearInterval(timer.current);
    let i = 0;
    setLit(0);
    const speed = prefersReducedMotion() ? 700 : 380;
    timer.current = window.setInterval(() => {
      i += 1;
      if (i > entry.tokens.length) {
        clearInterval(timer.current);
        setLit(-1);
        return;
      }
      setLit(i);
    }, speed);
  };

  const isButtons = entry.type === 'buttons' && entry.tokens?.length;

  return (
    <div className="gc-cheat__code">
      <dt>{label}</dt>
      <dd>{isButtons ? <Sequence entry={entry} lit={lit} /> : <code className="gc-code">{entry.raw}</code>}</dd>
      <dd className="gc-cheat__actions">
        {isButtons && (
          <button type="button" className="gc-copy" onClick={onPlay} aria-label={`Ver sequência de ${cheatName} passo a passo`}>
            <Icon name="play" /> Ver
          </button>
        )}
        <button type="button" className="gc-copy" onClick={onCopy} data-copied={copied} aria-label={`Copiar código de ${cheatName} (${label})`}>
          <Icon name={copied ? 'check' : 'copy'} />
          {copied ? 'Copiado!' : 'Copiar'}
        </button>
      </dd>
    </div>
  );
}

function Cheat({ cheat, variants, active, query, statuses }) {
  const shown = variants.filter((v) => cheat.rendered[v.key] && (active === ALL || active === v.key));
  return (
    <li className={`gc-cheat gc-cheat--${cheat.status}`} id={cheat.id}>
      <div className="gc-cheat__head">
        <h3 className="gc-cheat__name">
          <a href={`#${cheat.id}`}>
            <Highlight text={cheat.name} query={query} />
          </a>
        </h3>
        <span className={`gc-badge gc-badge--${cheat.status}`}>{statuses[cheat.status]}</span>
      </div>
      {cheat.effect && <p className="gc-cheat__effect" dangerouslySetInnerHTML={{ __html: cheat.effect }} />}
      {shown.length > 0 ? (
        <dl className="gc-cheat__codes">
          {shown.map((v) => (
            <CodeRow key={v.key} label={v.label} entry={cheat.rendered[v.key]} cheatName={cheat.name} />
          ))}
        </dl>
      ) : (
        <p className="gc-cheat__meta">Sem código para esta plataforma.</p>
      )}
      {cheat.unlock && (
        <p className="gc-cheat__meta">
          <strong>Como desbloquear:</strong> <span dangerouslySetInnerHTML={{ __html: cheat.unlock }} />
        </p>
      )}
      {cheat.note && (
        <p className="gc-cheat__meta">
          <strong>Obs.:</strong> <span dangerouslySetInnerHTML={{ __html: cheat.note }} />
        </p>
      )}
    </li>
  );
}

function Explorer({ data }) {
  const { variants, sections, statuses } = data;
  const multi = variants.length > 1;

  const [query, setQuery] = useState('');
  const deferred = useDeferredValue(query);
  const [active, setActive] = useState(() => {
    const saved = storage('gc-variant');
    if (variants.some((v) => v.key === saved)) return saved;
    return multi ? variants[0].key : ALL;
  });
  const [onlyConfirmed, setOnlyConfirmed] = useState(false);

  const index = useMemo(
    () =>
      sections.map((s) => ({
        ...s,
        cheats: s.cheats.map((c) => ({
          ...c,
          _hay: normalize([c.name, stripTags(c.effect), stripTags(c.unlock), stripTags(c.note), ...Object.values(c.rendered).map((r) => `${r.raw} ${r.copy}`)].join(' ')),
        })),
      })),
    [sections]
  );

  const filtered = useMemo(() => {
    const q = normalize(deferred.trim());
    const terms = q.split(/\s+/).filter(Boolean);
    return index
      .map((s) => ({
        ...s,
        cheats: s.cheats.filter((c) => {
          if (onlyConfirmed && !['testado', 'confirmado'].includes(c.status)) return false;
          if (active !== ALL && !c.rendered[active]) return false;
          return terms.every((t) => c._hay.includes(t) || normalize(s.title).includes(t));
        }),
      }))
      .filter((s) => s.cheats.length);
  }, [index, deferred, onlyConfirmed, active]);

  const total = filtered.reduce((n, s) => n + s.cheats.length, 0);

  // Anuncia contagem (debounce) para leitores de tela.
  const first = useRef(true);
  useEffect(() => {
    if (first.current) {
      first.current = false;
      return;
    }
    const t = setTimeout(() => announce(`${total} ${total === 1 ? 'código encontrado' : 'códigos encontrados'}`), 450);
    return () => clearTimeout(t);
  }, [total]);

  // Âncora (#cheat) vinda da busca: destaca e rola.
  useEffect(() => {
    const id = decodeURIComponent(window.location.hash.slice(1));
    if (!id) return;
    const el = document.getElementById(id);
    if (el?.classList.contains('gc-cheat')) {
      el.classList.add('is-target');
      el.scrollIntoView({ block: 'center', behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    }
  }, []);

  const choose = (key) => {
    setActive(key);
    if (key !== ALL) storage('gc-variant', key);
  };

  return (
    <>
      <div className="gc-explorer__bar">
        <div className="gc-explorer__search">
          <Icon name="search" />
          <label htmlFor="gc-explorer-q" className="sr-only">
            Filtrar cheats de {data.title}
          </label>
          <input id="gc-explorer-q" type="search" value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Filtrar: dinheiro, tanque, vida…" autoComplete="off" />
        </div>
        {multi && (
          <fieldset className="gc-seg">
            <legend>Plataforma</legend>
            {variants.map((v) => (
              <button key={v.key} type="button" className="gc-chip" aria-pressed={active === v.key} onClick={() => choose(v.key)}>
                {v.label}
              </button>
            ))}
            <button type="button" className="gc-chip" aria-pressed={active === ALL} onClick={() => choose(ALL)}>
              Todas
            </button>
          </fieldset>
        )}
        <div className="gc-explorer__status">
          <span aria-hidden="true">
            {total} {total === 1 ? 'código' : 'códigos'}
          </span>
          <label>
            <input type="checkbox" checked={onlyConfirmed} onChange={(e) => setOnlyConfirmed(e.target.checked)} />
            Só confirmados
          </label>
        </div>
      </div>

      {filtered.length === 0 && (
        <div className="gc-explorer__empty">
          <p>
            Nenhum código para “{query}”{onlyConfirmed ? ' entre os confirmados' : ''}.
          </p>
          <button type="button" className="gc-link-arrow" style={{ marginTop: '1rem' }} onClick={() => { setQuery(''); setOnlyConfirmed(false); choose(ALL); }}>
            Limpar filtros
          </button>
        </div>
      )}

      {filtered.map((s) => (
        <section key={s.id} className="gc-cheat-section" aria-labelledby={s.id}>
          <h2 id={s.id} className="gc-h3">
            {s.title} <span className="gc-count">{s.cheats.length}</span>
          </h2>
          {s.intro && <p className="gc-cheat-section__intro" dangerouslySetInnerHTML={{ __html: s.intro }} />}
          <ul className="gc-cheats">
            {s.cheats.map((c) => (
              <Cheat key={c.id} cheat={c} variants={variants} active={active} query={deferred.trim()} statuses={statuses} />
            ))}
          </ul>
        </section>
      ))}
    </>
  );
}

export function mountExplorer(el, data) {
  const root = createRoot(el);
  root.render(<Explorer data={data} />);
  return root;
}
