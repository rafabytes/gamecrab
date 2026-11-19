/**
 * Editor visual da "Ficha de cheats" (meta box do CPT jogo).
 * Mantém o <textarea name="gc_data_json"> sincronizado — é ele que é salvo.
 */

const STATUS = (() => {
  try {
    return JSON.parse(document.getElementById('gc-editor')?.dataset.statuses || '{}');
  } catch {
    return {};
  }
})();

const SCHEMA = [
  { key: 'seo_title', label: 'Título SEO (até ~60 caracteres)', type: 'text', counter: 60 },
  { key: 'seo_description', label: 'Meta description (até ~155 caracteres)', type: 'textarea', counter: 155 },
  { key: 'kicker', label: 'Linha de apoio (ex.: Rockstar Games · 2013)', type: 'text' },
  { key: 'developer', label: 'Desenvolvedora', type: 'text' },
  { key: 'release', label: 'Lançamento', type: 'text' },
  { key: 'accent', label: 'Cor da capa', type: 'color' },
  { key: 'updated', label: 'Atualizado em', type: 'date' },
  {
    key: 'notice',
    label: 'Aviso em destaque',
    type: 'group',
    fields: [
      { key: 'tone', label: 'Tom', type: 'select', options: { info: 'Informativo', warn: 'Atenção' } },
      { key: 'title', label: 'Título', type: 'text' },
      { key: 'text', label: 'Texto', type: 'textarea' },
    ],
  },
  {
    key: 'variants',
    label: 'Plataformas / formatos de código',
    help: 'Chave curta (ex.: ps, xbox, pc, phone). Tipo "botões" renderiza keycaps; "derivar de" converte PlayStation → Xbox automaticamente.',
    type: 'repeater',
    item: 'formato',
    structural: true,
    fields: [
      { key: 'key', label: 'Chave', type: 'text' },
      { key: 'label', label: 'Rótulo', type: 'text' },
      { key: 'type', label: 'Tipo', type: 'select', options: { buttons: 'Botões', text: 'Texto', phone: 'Telefone' } },
      { key: 'derive', label: 'Derivar de (opcional)', type: 'text' },
    ],
  },
  {
    key: 'how_to',
    label: 'Como ativar',
    type: 'repeater',
    item: 'bloco',
    fields: [
      { key: 'title', label: 'Título (ex.: PlayStation)', type: 'text' },
      { key: 'steps', label: 'Passos (um por linha)', type: 'lines' },
    ],
  },
  {
    key: 'sections',
    label: 'Seções de cheats',
    type: 'repeater',
    item: 'seção',
    fields: [
      { key: 'title', label: 'Título da seção', type: 'text' },
      { key: 'intro', label: 'Introdução (opcional)', type: 'textarea' },
      {
        key: 'cheats',
        label: 'Cheats',
        type: 'repeater',
        item: 'cheat',
        compact: true,
        fields: [
          { key: 'name', label: 'Nome', type: 'text' },
          { key: 'status', label: 'Status', type: 'select', options: STATUS },
          { key: 'effect', label: 'Efeito', type: 'textarea' },
          { key: 'codes', label: 'Códigos', type: 'codes' },
          { key: 'unlock', label: 'Como desbloquear (opcional)', type: 'text' },
          { key: 'note', label: 'Observação (opcional)', type: 'text' },
        ],
      },
    ],
  },
  { key: 'tips', label: 'Dicas (uma por linha)', type: 'lines' },
  {
    key: 'faq',
    label: 'Perguntas frequentes (vira FAQPage no schema)',
    type: 'repeater',
    item: 'pergunta',
    fields: [
      { key: 'q', label: 'Pergunta', type: 'text' },
      { key: 'a', label: 'Resposta', type: 'textarea' },
    ],
  },
  {
    key: 'sources',
    label: 'Fontes',
    type: 'repeater',
    item: 'fonte',
    fields: [
      { key: 'label', label: 'Nome', type: 'text' },
      { key: 'url', label: 'URL', type: 'url' },
    ],
  },
];

const root = document.getElementById('gc-editor');
const textarea = document.getElementById('gc-data-json');

if (root && textarea) {
  let state;
  try {
    state = JSON.parse(textarea.value || '{}');
  } catch {
    state = {};
  }

  const sync = () => {
    textarea.value = JSON.stringify(state, null, 2);
  };

  const el = (tag, attrs = {}, ...children) => {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
      if (k === 'class') node.className = v;
      else if (k.startsWith('on')) node.addEventListener(k.slice(2), v);
      else if (v !== undefined && v !== null && v !== false) node.setAttribute(k, v === true ? '' : v);
    });
    children.flat().forEach((c) => c != null && node.append(c));
    return node;
  };

  let uid = 0;
  const field = (def, obj, rerender) => {
    const id = `gc-f-${++uid}`;
    const value = obj[def.key];
    const set = (v) => {
      obj[def.key] = v;
      sync();
    };

    if (def.type === 'group') {
      obj[def.key] = obj[def.key] && typeof obj[def.key] === 'object' && !Array.isArray(obj[def.key]) ? obj[def.key] : {};
      return el('fieldset', { class: 'gc-ed-group' }, el('legend', {}, def.label), def.fields.map((f) => field(f, obj[def.key], rerender)));
    }

    if (def.type === 'repeater') {
      if (!Array.isArray(obj[def.key])) obj[def.key] = [];
      const list = obj[def.key];
      const wrap = el('fieldset', { class: `gc-ed-rep${def.compact ? ' is-compact' : ''}` }, el('legend', {}, `${def.label} (${list.length})`));
      if (def.help) wrap.append(el('p', { class: 'description' }, def.help));
      list.forEach((item, i) => {
        const title = item.name || item.title || item.label || item.q || item.key || `${def.item} ${i + 1}`;
        const row = el(
          'details',
          { class: 'gc-ed-item', open: list.length <= 3 && !def.compact ? true : undefined },
          el(
            'summary',
            {},
            el('span', { class: 'gc-ed-item__title' }, `${i + 1}. ${title}`),
            el(
              'span',
              { class: 'gc-ed-item__tools' },
              el('button', { type: 'button', class: 'button-link', 'aria-label': `Mover ${title} para cima`, disabled: i === 0, onclick: (e) => { e.preventDefault(); [list[i - 1], list[i]] = [list[i], list[i - 1]]; sync(); render(); } }, '↑'),
              el('button', { type: 'button', class: 'button-link', 'aria-label': `Mover ${title} para baixo`, disabled: i === list.length - 1, onclick: (e) => { e.preventDefault(); [list[i + 1], list[i]] = [list[i], list[i + 1]]; sync(); render(); } }, '↓'),
              el('button', { type: 'button', class: 'button-link gc-ed-del', 'aria-label': `Remover ${title}`, onclick: (e) => { e.preventDefault(); if (window.confirm(`Remover "${title}"?`)) { list.splice(i, 1); sync(); render(); } } }, 'Remover')
            )
          ),
          el('div', { class: 'gc-ed-item__body' }, def.fields.map((f) => field(f, item, rerender)))
        );
        wrap.append(row);
      });
      wrap.append(
        el('button', { type: 'button', class: 'button', onclick: () => { list.push(def.key === 'cheats' ? { status: 'confirmado', codes: {} } : {}); sync(); render(); } }, `+ Adicionar ${def.item}`)
      );
      return wrap;
    }

    if (def.type === 'codes') {
      obj.codes = obj.codes && typeof obj.codes === 'object' && !Array.isArray(obj.codes) ? obj.codes : {};
      const variants = Array.isArray(state.variants) ? state.variants : [];
      return el(
        'div',
        { class: 'gc-ed-codes' },
        variants.map((v) => {
          const cid = `gc-f-${++uid}`;
          const placeholder = v.derive ? `vazio = derivado de "${v.derive}"` : v.type === 'buttons' ? 'R1, R2, L1, X, LEFT, DOWN, RIGHT, UP' : '';
          return el(
            'p',
            {},
            el('label', { for: cid }, `${v.label || v.key}`),
            el('input', { id: cid, type: 'text', class: 'widefat code', value: obj.codes[v.key] || '', placeholder, oninput: (e) => { if (e.target.value) obj.codes[v.key] = e.target.value; else delete obj.codes[v.key]; sync(); } })
          );
        })
      );
    }

    let input;
    if (def.type === 'textarea') {
      input = el('textarea', { id, rows: 3, class: 'widefat', oninput: (e) => set(e.target.value) });
      input.value = value || '';
    } else if (def.type === 'lines') {
      input = el('textarea', { id, rows: 4, class: 'widefat', oninput: (e) => set(e.target.value.split('\n').filter((l) => l.trim() !== '')) });
      input.value = Array.isArray(value) ? value.join('\n') : '';
    } else if (def.type === 'select') {
      input = el('select', { id, onchange: (e) => { set(e.target.value); } }, Object.entries(def.options).map(([k, l]) => el('option', { value: k, selected: value === k ? true : undefined }, l)));
      if (value === undefined) set(Object.keys(def.options)[0]);
    } else {
      const type = { color: 'color', date: 'date', url: 'url' }[def.type] || 'text';
      input = el('input', { id, type, class: type === 'color' ? '' : 'widefat', value: value || (type === 'color' ? '#ff4d2e' : ''), oninput: (e) => set(e.target.value) });
    }

    // Mudanças estruturais (ex.: plataformas) re-renderizam os campos de código.
    if (rerender) input.addEventListener('change', () => render());

    const label = el('label', { for: id }, def.label);
    const p = el('p', { class: 'gc-ed-field' }, label, input);
    if (def.counter) {
      const c = el('span', { class: 'gc-ed-counter' });
      const upd = () => {
        const n = (input.value || '').length;
        c.textContent = `${n}/${def.counter}`;
        c.classList.toggle('is-over', n > def.counter);
      };
      input.addEventListener('input', upd);
      upd();
      p.append(c);
    }
    return p;
  };

  function render() {
    const open = [...root.querySelectorAll('details[open] > summary .gc-ed-item__title')].map((s) => s.textContent);
    root.replaceChildren(...SCHEMA.map((def) => field(def, state, def.structural)));
    // Restaura itens abertos
    root.querySelectorAll('details > summary .gc-ed-item__title').forEach((s) => {
      if (open.includes(s.textContent)) s.closest('details').open = true;
    });
  }

  // Se o JSON for editado manualmente, recarrega o editor visual.
  textarea.addEventListener('change', () => {
    try {
      state = JSON.parse(textarea.value);
      render();
    } catch {
      /* JSON inválido: mantém o estado anterior */
    }
  });

  render();
  sync();
}
