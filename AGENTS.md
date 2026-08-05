# AGENTS.md

Context file for AI coding agents (Claude Code, etc.) working in this repository. `CLAUDE.md` is a symlink to this file.

## What this site is

[The Maker City](https://themakercity.org) — a directory/community site for maker spaces, makers, and maker-related events in Knoxville, TN. Built and maintained by Michael Wender ([Wenmark Digital Solutions](https://wenmarkdigital.com)).

- **Local dev URL:** `https://themakercity.test` (Laravel Valet)
- **Hosting:** DigitalOcean via SpinupWP (`@staging` and `@production` WP-CLI aliases are configured in `wp-cli.yml`)
- **Repo:** `git@github.com:WenderHost/themakercity.git`

## Stack

- **WordPress** on the **[Bedrock](https://roots.io/bedrock/)** framework (Roots) — standard Bedrock layout applies:
  - `web/wp` — WordPress core (not edited directly)
  - `web/app` — the `wp-content` equivalent (themes, plugins, mu-plugins, uploads)
  - `.env` — environment config (DB creds, `WP_HOME`, `WP_ENV`, `GOOGLE_MAPS_API_KEY`, salts). Never commit real secrets; `.env.example` documents the shape.
  - `config/application.php` + `config/environments/` — Bedrock environment-specific config
  - Dependencies managed via **Composer** (`composer.json`/`composer.lock`), pulling from wpackagist.org, a private Wenmark Satispress repo (`packages.wenmarkdigital.com`), the official Elementor Pro composer repo, and ACF PRO's repo. WordPress core itself is pinned via `roots/wordpress` (currently `^7`).
- **Page building:** [Elementor](https://elementor.com/) + Elementor Pro, on the **Hello Elementor** theme as the parent theme.
  - Use the **`elementor-mcp`** skill whenever creating/editing/styling pages or content via Elementor MCP tools (EMCP Tools). Prefer the native widget catalog flow over HTML widgets, and use the global kit / `__globals__` for style consistency.
- **Child theme:** `web/app/themes/themakercity-child` — this is where essentially all custom site logic and presentation lives. See below.
- **Frontend build:** Sass → CSS and a Parcel-bundled JS entrypoint, both scoped to the child theme. See `package.json`:
  - `npm run watch` — watches SCSS (`lib/scss`) and JS (`lib/js/scripts`) during development
  - `npm run build` — production build (compressed CSS to `lib/css-dist`, bundled JS via Parcel)
- **Custom fields:** Advanced Custom Fields PRO (+ ACF Extended PRO). Field group definitions are version-controlled as JSON in `web/app/themes/themakercity-child/acf-json/`.
- **Search/filtering:** FacetWP + Relevanssi (with FacetWP↔Relevanssi and FacetWP↔Elementor integration plugins), used for directory/maker search and filtering.
- **Forms:** Gravity Forms (+ Salesforce CRM Perks integration).
- **Other notable plugins:** Safe SVG, Simple Page Ordering, Limit Login Attempts Reloaded, SMTP2GO (transactional email), Disable Gutenberg (site leans on Elementor, not block editor), WP Security Audit Log, API for HTMX, Enhanced User Search, WP All Export, Export WP Users XML/CSV, WordPress Importer.
- Several plugins are pulled under the `wenmark/*` Composer namespace (private forks/mirrors hosted on Wenmark's Satispress repo) rather than wpackagist — e.g. `wenmark/gravityforms`, `wenmark/facetwp`, `wenmark/advanced-custom-fields-pro`, `wenmark/acf-extended-pro`, `wenmark/analyticswp`, `wenmark/elementor-pro`.

## Domain model

The site's core content types (custom post types, all defined in the child theme, not a CPT plugin):

- **`maker`** — a maker/member profile (the core directory entity). Taxonomies: `maker-category`, `maker-tag`. Has ACF fields including a map location field, consumed by the `[maker-map]` shortcode via a custom REST endpoint (`/makers/v1/locations`, see `lib/fns/rest.maker-spaces.php`) and Google Maps (requires `GOOGLE_MAPS_API_KEY` in `.env`).
- **`maker-link`** — links associated with a maker (e.g. social/profile links used in directory listings). Taxonomies: `maker-link-category`, `maker-link-tag`.
- **`event`** — maker-related events, with a custom `event_date` meta field used for admin sortable columns and custom query ordering (`lib/fns/events.php`).
- ACF option/field groups also cover: Maker Options, User Options, Maker Site Options, Maker Meetings, Maker Space Types (see `acf-json/`).

Custom front-end "pages" that aren't real WP pages — sign-in, sign-up, account, profile-editor, apply, reset-password — are implemented as custom rewrite routes (`lib/fns/routes.php`) that all route through the `dashboard.php` template, rendering different `wp-templates/*.php` partials based on the requested route/auth state. HTMX is used for some of this dashboard/auth UI (`htmx-templates/`, `lib/fns/htmx-api-wp.php`, the `api-for-htmx` plugin).

Transactional email is built from MJML templates (`mjml-templates/`) compiled through Handlebars templates (`hbs-templates/*.hbs` → `hbs-templates/compiled/*.php` via `zordius/lightncandy`), wired up in `lib/fns/emails.php`.

## Child theme structure

`web/app/themes/themakercity-child/`
- `functions.php` — thin bootstrap; just requires everything under `lib/fns/` and `lib/shortcodes/`
- `lib/fns/` — one file per concern: ACF (`acf.php`), admin tweaks (`admins.php`, `adminbar.php`), debugging helpers, Elementor integration, email, emoji, asset enqueues, events, the `maker`/`maker-link` CPTs (+ admin columns), custom routes, template helpers, general utilities, the maker-spaces REST endpoint, user handling, wp-login customization
- `lib/shortcodes/` — `[maker-map]`, `[maker-list]`, `[makercollaborator]`, `[makersocials]`, `[makerstatusalert]`, `[primary_image]`, `[profile_button]`, `[logo]`, `[simplecalendar]`, `[title-and-date]`, and maker-link caption
- `lib/scss/` / `lib/css/` / `lib/css-dist/` — Sass source and compiled output (dev vs. production dist)
- `lib/js/scripts/` / `lib/js/dist/` — JS source and Parcel bundle output
- `wp-templates/` — custom route templates + shared `layout/` partials (`header.php`, `footer.php`, `navbar-top.php`, `sidebar.php`, `head.php`)
- `htmx-templates/` — HTMX-driven partials (login, reset password, maker-related fragments, user data)
- `mjml-templates/` / `hbs-templates/` — transactional email templates
- `acf-json/` — ACF field group JSON (source of truth for field structure; sync via the ACF admin UI's local JSON sync)
- `dashboard.php` — the shared template for all custom-route "pages" (see Domain model above)

## Working conventions

- Custom PHP is namespaced under `TheMakerCity\...` (e.g. `TheMakerCity\routes`, `TheMakerCity\shortcodes`, `TheMakerCity\htmx`) — follow this when adding new files under `lib/`.
- When adding a CSS/JS change, edit the Sass/JS source under `lib/scss` or `lib/js/scripts`, not the compiled `lib/css`, `lib/css-dist`, or `lib/js/dist` output — those are build artifacts.
- When adding/editing ACF fields, prefer doing it through WP Admin with local JSON sync enabled so `acf-json/` stays the source of truth and changes are diffable.
- Elementor is the page-building tool for actual page content/layout — use the `elementor-mcp` skill for that work rather than hand-writing template markup, unless the task specifically requires a new PHP template, shortcode, or CPT/field logic.
- `scripts/` at the repo root holds one-off maintenance/import scripts (e.g. `import-makers.php`, `crop-images.php`, `download-images.php`, `list-makers.php`, `update-maker-logos.php`) plus historical CSV exports of the maker directory — these are ad hoc CLI utilities, not part of the running site.
