# GitHub info cards

GitHub cards show repository metadata (stars, description, funding, latest release, and related UI) in the same product family as WordPress.org plugin/theme cards.

## Why GitHub fits this plugin

WP Plugin Info Card’s product thesis is **multi-source info cards**, not “WordPress.org only.” [`readme.txt`](../readme.txt) markets GitHub alongside WP.org plugins/themes, EDD, and custom plugins. Same card/grid UX pattern; different data source.

Technical fit:

| Layer | Behavior |
|-------|----------|
| Data | [`php/GitHub.php`](../php/GitHub.php) hooks `wppic_add_api_parser` with type `github` |
| Cache | Same `wppic_api_parser()` funnel as plugins/themes |
| Feature flag | `enable_github_info_cards` plus a GitHub token, or `github_info_cards_unauthenticated` with no token. Disabled when neither path is available (`Options::is_github_info_cards_enabled()`) |
| Surfaces | Blocks `github-info-card` / `github-info-card-grid`, shortcode `[github-info-card]`, admin tab, lazy-load JS, dedicated SCSS |

GitHub markup does **not** reuse `templates/wppic-template-plugin*.php`; it has its own HTML (largely inlined via shortcode rendering) and [`src/scss/github-cards.scss`](../src/scss/github-cards.scss).

### Key paths

| Concern | Path |
|---------|------|
| API parser | `php/GitHub.php` |
| Options / enablement | `php/Options.php` |
| Admin tab (PHP) | `php/Admin/Tabs/GitHub_Info_Cards.php` |
| Admin UI (React) | `src/react/views/github-info-cards/` |
| Blocks | `src/blocks/GitHubInfoCard/`, `src/blocks/GitHubInfoCardGrid/` |
| Shortcode + HTML + REST callbacks | `php/Shortcodes.php` |
| Block render bridge | `php/Blocks.php` |
| Lazy load | `src/js/github-info-card/github-info-card-lazy-load.js` |

GitHub REST usage: repo metadata, `.github/FUNDING.yml`, releases, and rate-limit checks. Requests send the stored token. With **Use GitHub without a token** enabled and no token stored, those requests omit `Authorization` and use GitHub’s unauthenticated limit (60 requests per hour, public repositories only).

## Spin-off assessment

**Verdict: keep GitHub cards in this plugin for now.** Spin-off is feasible at medium effort, not a clean cut. Prefer modularizing inside the repo before shipping a separate plugin.

### Coupling

| Layer | Coupling | Notes |
|-------|----------|-------|
| Data (`GitHub.php` + parser filter) | Low | Already a separable type |
| Options / admin tab | Medium | Shared `Options` bag and admin shell |
| REST + presentation HTML | High | Embedded in `Shortcodes.php` next to core routes |
| Blocks / webpack / block category | Medium | Shared build and `wp-plugin-info-card/*` names |

### When a spin-off would make sense

- Independent release cadence or licensing from WPPIC
- A much smaller install footprint for sites that only want GitHub cards
- A dedicated product brand — not merely because the data source is GitHub

Differing data sources alone is **not** enough reason to split; that is the core product model.

### Prefer this first

1. Extract GitHub REST callbacks and HTML rendering from `Shortcodes.php` into dedicated PHP classes (still inside WPPIC).
2. Keep the shortcode and blocks as thin wrappers.
3. Revisit a separate plugin only if product needs above appear.

A naive fork would need to reimplement Options/admin patterns, REST registration, block scaffolding, and migration for existing `wp-plugin-info-card/github-info-card*` content.

## Related docs

- [`architecture.md`](architecture.md) — shortcode/block bridge
- [`history-and-debt.md`](history-and-debt.md) — `Shortcodes.php` size and mixed pipelines
- [`rest-api.md`](rest-api.md) — `get_github_data` / `get_github_card_html`
