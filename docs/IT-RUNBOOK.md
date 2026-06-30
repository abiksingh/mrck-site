# IT Runbook — deploying the MRCK archive

For the institute's system administrator. Your scope: server, domain, security,
backups, deployment. The application (theme + plugin) is delivered ready to install.

## 1. Requirements

- WordPress **6.5+**
- PHP **8.1+** (8.2 recommended — matches the development environment)
- MySQL **5.7+** / MariaDB **10.4+**
- HTTPS (Let's Encrypt or IONOS-provided)

## 2. Install (fresh WordPress)

1. Install WordPress normally on the IONOS VPS.
2. **Theme:** Appearance → Themes → Add New → Upload `mrck-theme.zip` → Activate.
3. **Plugins:**
   - Plugins → Add New → Upload `mrck-archive.zip` → Activate.
   - Plugins → Add New → search **Secure Custom Fields** → Install → Activate.
   - (Optional extras: see `PLUGINS.md`.)
4. **Permalinks:** Settings → Permalinks → **Post name** → Save (creates `/oeuvres/…`, `/vie/…`).
5. **Reading:** Settings → Reading → "Your homepage displays → A static page" → choose the **Accueil** page.
6. **Menu:** Appearance → Menus → assign the primary menu (Accueil / La vie / Œuvres).
7. **Language:** Settings → General → Site Language → **Français**.

## 3. Move the content from the delivered snapshot

The delivered database holds 232 catalogued works, the biography and all media.
Two supported methods:

- **Easiest — All-in-One WP Migration:** install the plugin on both sides, import
  the delivered `.wpress` package. (Free up to the import size limit; raise the
  PHP `upload_max_filesize` / `post_max_size` if needed.)
- **Manual:** import the delivered `database.sql` (`wp db import database.sql` or
  phpMyAdmin), copy `wp-content/uploads/` into place, run
  `wp search-replace 'https://OLD' 'https://NEW'` for the domain, then
  `wp rewrite flush`.

Re-importing from source data instead (CSV/JSON) is documented in `scripts/`.

## 4. Security hardening (checklist)

- [ ] Keep core, theme and plugins updated; test on a staging copy first.
- [ ] Least-privilege users — give the curator an **Editor** account, not Admin.
- [ ] `define('DISALLOW_FILE_EDIT', true);` in `wp-config.php`.
- [ ] Strong passwords + **2FA**; limit login attempts (server or plugin).
- [ ] HTTPS + **HSTS**; security headers (CSP, X-Content-Type-Options, Referrer-Policy).
- [ ] WAF / Fail2ban; disable XML-RPC if unused.
- [ ] Correct file permissions (dirs 755, files 644; `wp-config.php` 640).
- [ ] Move secrets/salts; restrict DB user privileges.

## 5. Performance (server-side)

The application is already optimised (server-rendered HTML, responsive `srcset`,
lazy-loading, deferred ES-module JS, system fonts, ~60 kB gz JS). Add at the server:

- [ ] **Page cache** — LiteSpeed Cache / Nginx FastCGI cache / W3TC (all free).
- [ ] **Object cache** — Redis or Memcached (matters once the catalogue is large).
- [ ] **AVIF/WebP** — server module or a free plugin (WordPress can't auto-convert).
- [ ] **CDN** + brotli/gzip; HTTP/2 or HTTP/3.

## 6. Accessibility (RGAA)

Code-level accessibility is built in (semantic landmarks, skip link, keyboard
focus styles, alt text, `lang="fr"`, reduced-motion support, labelled controls).
For a public body you should still: run a **formal RGAA audit**, fix any findings,
and publish an **accessibility statement** page (a legal requirement in France).

## 7. Backups

- [ ] Automated **daily** DB + `wp-content` backups, stored **off-site**.
- [ ] Test a restore before go-live.
- [ ] Keep a **staging** copy for updates and edits.

## 8. Local development (optional, for future changes)

See `README.md` — `npm run setup && npm run wp:start` (Docker + Node). Rebuild
front-end assets with `npm run build` before zipping a new release
(`scripts/package.sh`).
