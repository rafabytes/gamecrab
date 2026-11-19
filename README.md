# gameCrab — tema WordPress

Guia de cheats públicos do appMaster: o código certo, por jogo e por plataforma. Identidade inspirada nos discos de cheat dos anos 90, com um caranguejo como mascote.

> Nos textos do site, não citar a origem/cidade do projeto: o foco é o propósito.
**Não é** um programa nem um dispositivo: o site só divulga códigos que os próprios jogos aceitam.

Stack: **WordPress + Parcel + TailwindCSS v4 + Swup.js + React 19**, sem dependência de plugins.

## Desenvolvimento

```bash
npm install
npm run dev     # Parcel em watch → dist/
npm run build   # build de produção (minificado, sem sourcemaps)
```

`dist/` é o que o WordPress carrega. Rode `npm run build` antes de publicar e versione o `dist/`.

| Entrada | Saída | Onde carrega |
|---|---|---|
| `src/main.css` | `dist/main.css` | front (head) |
| `src/main.js` | `dist/main.js` + chunks React | front (defer) |
| `src/admin.js` / `src/admin.css` | `dist/admin.*` | editor do CPT “Jogo” |

## Estrutura

```
functions.php            bootstrap
inc/setup.php            supports, menus, limpeza do <head>, sem comentários/autores
inc/assets.php           enqueue do dist/, script anti-flash do tema dia/noite
inc/post-types.php       CPT "jogo" (/cheats/{slug}/), taxonomias plataforma e franquia
inc/data.php             modelo de dados (_gc_data JSON), status, botões PS→Xbox
inc/template-tags.php    logo, ícones, breadcrumbs, cards "jewel case", keycaps
inc/seo.php              title, description, canonical, OG/Twitter, robots, JSON-LD
inc/rest.php             /wp-json/gamecrab/v1/index (índice da busca ⌘K)
inc/metabox.php          "Ficha de cheats" (editor visual + JSON) e sanitização
inc/seeder.php           importador de data/seed/*.json (Aparência › gameCrab)
src/js/react/explorer.jsx  filtro/busca/copiar/replay dos cheats (ilha React)
src/js/react/palette.jsx   paleta de busca global (⌘K, Ctrl+K, /)
data/seed/games/*.json   conteúdo inicial (1 arquivo por jogo)
bin/make-images.php      gera og-default.png e logo-512.png (GD)
```

## Conteúdo

- **Novo jogo:** Cheats › Adicionar jogo. Preencha título, resumo (excerpt = lead da página), texto editorial (opcional) e a **Ficha de cheats** (SEO, plataformas/formatos, como ativar, seções de cheats, dicas, FAQ e fontes).
- **Formatos de código** (`variants`): `buttons` vira keycaps (use `UP, DOWN, LEFT, RIGHT, X, CIRCLE, SQUARE, TRIANGLE, L1, L2, R1, R2…`). Um formato com `derive: "ps"` gera o Xbox automaticamente (✕→A, ○→B, □→X, △→Y, L1→LB…).
- **Status:** `testado` (equipe testou), `confirmado` (fonte oficial ou vários guias), `a-confirmar` (uma fonte ou fontes divergem), `expirado`.
- **Reimportar o seed:** Aparência › gameCrab › Importar conteúdo (atualiza pelo slug e **sobrescreve** edições feitas no admin desses jogos).

## Produção (performance)

O tema já sai enxuto: CSS crítico único, JS `defer`, React só sob demanda, fontes self-hosted com preload.
O que falta é do **servidor**: HTTPS + HTTP/2, compressão e cache. Em Apache (o XAMPP local vem com `mod_deflate`/`mod_expires` desligados):

```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType image/png "access plus 1 month"
</IfModule>
```

(CSS/JS recebem `?ver=filemtime`, então o cache longo é seguro.)

## SEO e acessibilidade

- HTML 100% renderizado no servidor; o React só melhora a experiência (progressive enhancement).
- `<title>`/description por jogo, canonical, Open Graph, `robots noindex` em busca e 404, sitemap nativo (sem usuários).
- JSON-LD: Organization, WebSite + SearchAction, BreadcrumbList, Article + VideoGame, FAQPage, ItemList.
- Se Yoast/Rank Math/SEOPress/AIOSEO estiver ativo, o tema desliga o próprio SEO.
- Skip link, landmarks, foco visível, `aria-live` (cópia, filtros, navegação Swup), combobox ARIA na busca, `<dialog>` nativo, `prefers-reduced-motion` e contraste AA nos dois temas.
