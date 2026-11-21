# Changelog

All notable changes to the **GNN Whois** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to Semantic Versioning.

## [1.1.1] - 2026-11-21
### Changed
- **Updater:** Standardized `GNN_Whois_Updater` to the unified GNN GitHub Updater specification and updater loading guard.

## [1.1.0] - 2026-11-20
### Added
- **WordPress & PHP Metadata:** Added `Requires at least: 6.7` and `Requires PHP: 7.4` plugin headers, plus `requires`, `tested` (`7.1.5`), and `requires_php` metadata in `GNN_Whois_Updater`.
- **Admin Screen & Uninstall Cleanup:** Added `Settings` action link (`Donate` -> `Settings` -> `Check Updates`), a nonce-protected WHOIS lookup cache clearing tool on the `GNN Whois` admin page, and `uninstall.php` to clean up plugin transients on deletion.
- **Accessibility & Multi-Shortcode Support:** Added screen-reader `<label>`, `aria-live="polite"` status announcements, `:focus-visible` outlines, iOS auto-zoom font protection (`max(16px, 1.05rem)`), unique per-instance element IDs, and external `script.js` with secure Clipboard fallback.

### Changed
- **WordPress 6.7+ / 7.x i18n Compliance:** Moved `load_plugin_textdomain()` from `plugins_loaded` to `init` (priority 1) and passed `false, false` to `get_plugin_data()` to prevent `_load_textdomain_just_in_time` notices.
- **Asset Loading:** Conditionally enqueue `styles.css` and `script.js` only when `[gnn_whois]` is present.
- **Naming Standard:** Renamed `gnn_get_whois_server()` to `gnn_whois_get_server()` to comply with the `gnn_whois_` prefix standard.
- **Translations:** Updated `languages/gnn-whois.pot` with `Project-Id-Version` and all current plugin strings.

### Fixed
- **Security & Socket Hardening:** Added strict domain normalization and RFC hostname validation (`gnn_whois_normalize_domain()`), `wp_unslash()` on `$_POST['domain']`, `stream_set_timeout()`, a 512 KB response limit, prevented caching of connection/timeout errors, eliminated error double-escaping, and added `rel="noopener noreferrer"` on the external Donate link.
- **Compound TLD Lookup:** Fixed `gnn_whois_get_server()` so two-level TLDs like `.co.uk` resolve to `whois.nic.uk` instead of falling back to IANA.
- **Updater Reliability:** Fixed negative API caching (using an `'error'` sentinel instead of `false`), added `json_last_error()` and HTTPS GitHub host URL validation, populated `no_update`, guarded `plugin_info()` `$args->slug`, ensured `WP_Filesystem` + overwrite + reactivation in `after_install()`, and enforced `wp_die()` on unauthorized manual checks.
- **Release CI:** Stripped CRLF (`\r`) when extracting `Version:` in `.github/workflows/release.yml` and removed the duplicate `token` input on `softprops/action-gh-release@v3`.

## [1.0.3] - 2026-08-05
### Fixed
- **Repo Structure:** Moved `README.md` back to the repository root (GitHub renders it as the repo landing page there, not from inside the plugin subfolder).

## [1.0.2] - 2026-08-05
### Changed
- **Repo Structure:** Moved plugin files into a `gnn-whois/` subfolder so the repository root mirrors the installable plugin package. Simplified the release workflow to zip the folder directly instead of rsync-ing with exclude rules.

## [1.0.1] - 2026-08-05
### Changed
- **CI:** Bumped `actions/checkout` to v7 and `softprops/action-gh-release` to v3 in the release workflow.

## [1.0.0] - 2026-08-02
### Added
- Initial release of GNN Whois plugin.
- Core WHOIS lookup logic using `fsockopen` with multi-TLD support.
- `[gnn_whois]` shortcode for embedding the lookup form on any page or post.
- Rate limiting: maximum 3 lookups per minute per visitor IP.
- Transient caching (1 hour) for WHOIS responses.
- "Copy Results" button with interactive feedback.
- Admin menu entry at position `'79.106'` (GNN plugin band — ADR #9).
- "Donate" and "Check Updates" links on the WordPress Plugins page.
- GitHub Updater integration for automated releases.
- Responsive CSS with glassmorphism design and dark mode support.
