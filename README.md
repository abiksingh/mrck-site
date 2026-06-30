# MRCK — Marie-Renée Chevallier-Kervern · Digital Archive

A custom WordPress build for the digital archive & catalogue raisonné of the Breton
painter **Marie-Renée Chevallier-Kervern (1902–1987)**: server-rendered for SEO,
animation-driven (Lenis + GSAP) for a Readymag-like feel, and fully editable by
non-technical staff.

## Architecture

| Part | Folder | Role |
|------|--------|------|
| **Plugin** `mrck-archive` | `plugins/mrck-archive/` | Content backbone — the `oeuvre` post type, taxonomies, fields, admin tooling, REST filtering, importer. Theme-independent so the catalogue survives redesigns. |
| **Theme** `mrck-theme` | `themes/mrck-theme/` | Presentation only — templates, SCSS, the GSAP/Lenis motion layer, Vite build. |

Content structure lives in the **plugin**; look & feel lives in the **theme**.

## Stack

- WordPress (classic theme, server-rendered) + Gutenberg blocks for narrative pages
- **Secure Custom Fields** (free ACF fork) for structured fields
- **Vite** + **SCSS** build pipeline
- **GSAP + ScrollTrigger + Lenis** (smooth scroll); Swup page transitions (next pass)
- **@wordpress/env** for the local Docker dev environment

## Requirements

- Docker Desktop (running)
- Node.js 18+

## Quickstart

```bash
npm run setup       # install root + theme dependencies
npm run wp:start    # boot WordPress in Docker (first run downloads core)
npm run dev         # start the Vite dev server (HMR) in a second terminal
```

- Site:  http://localhost:8888
- Admin: http://localhost:8888/wp-admin  (user `admin`, password `password`)

Production build (also what release zips contain):

```bash
npm run build       # compiles the theme into themes/mrck-theme/dist/
```

Useful:

```bash
npm run wp:cli -- <args>   # run WP-CLI, e.g. `npm run wp:cli -- plugin list`
npm run wp:stop            # stop the environment
npm run wp:clean           # reset the environment
```

## Content model

One `oeuvre` per catalogued work. Browse/filter axes are **taxonomies**
(`technique`, `serie`, `theme_art`, `collection`); descriptive data is in
**fields** (`annee`, dimensions, `support`, `numero_inventaire`, `credit`,
image `galerie`). The cover image is the standard featured image.

## Handover

Run `bash scripts/package.sh` → `release/mrck-theme.zip` + `release/mrck-archive.zip`.
Hand these to the IT team along with:

- **[docs/IT-RUNBOOK.md](docs/IT-RUNBOOK.md)** — install, security hardening, performance, backups
- **[docs/CLIENT-GUIDE.md](docs/CLIENT-GUIDE.md)** — how the editorial team adds works & edits the biography
- **[PLUGINS.md](PLUGINS.md)** — the (all-free) plugin list
- **[scripts/README.md](scripts/README.md)** — the data-extraction / import pipeline
- a content snapshot (database + `wp-content/uploads`, or an All-in-One WP Migration package)

## Status

Complete. Six sections — Accueil (hero + "Œuvres en avant" block), La Vie
(6 biography chapters), Œuvres (232 works, faceted filter + detail pages),
Expositions, Actualités, Publications — with mobile nav, Swup page transitions +
smooth scroll, SEO/JSON-LD, French locale and an accessibility pass. Remaining
items are editorial (curator) touches and a formal RGAA audit — see
[docs/IT-RUNBOOK.md](docs/IT-RUNBOOK.md).
