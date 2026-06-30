# Plugins

Everything here is **free** — total recurring software cost is **€0/year**.

## Custom (in this repository)

| Plugin/Theme | Folder | Role |
|---|---|---|
| **MRCK Archive** (plugin) | `plugins/mrck-archive` | `oeuvre` + `chapitre` post types, taxonomies, fields, admin tooling, REST filter, importers, SEO/JSON-LD |
| **MRCK Theme** | `themes/mrck-theme` | Front-end: templates, styles, motion layer |

## Required third-party

| Plugin | Source | Why |
|---|---|---|
| **Secure Custom Fields** | wordpress.org (`secure-custom-fields`) | Provides the ACF field API the plugin uses (gallery + custom fields). Free fork of ACF by Automattic. |

Install: **Plugins → Add New → search "Secure Custom Fields" → Install → Activate.**

## Optional (recommended, all free)

| Plugin | Source | Adds |
|---|---|---|
| **Relevanssi** | wordpress.org (`relevanssi`) | Better full-text search incl. custom fields, as the archive grows past ~500 works. |
| **Rank Math** | wordpress.org (`seo-by-rank-math`) | A UI for the editor to override SEO titles/descriptions per page. The site already emits meta tags, Open Graph and JSON-LD without it — add only if the team wants editable SEO. |
| **Polylang** | wordpress.org (`polylang`) | If/when an English version is needed. The templates are already language-agnostic. |

> Sitemaps are handled by **WordPress core** (`/wp-sitemap.xml`) — no plugin needed.
