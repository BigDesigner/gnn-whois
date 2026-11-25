# ADR 0001: Unified GNN GitHub Updater Standard

- **Status**: Accepted
- **Date**: 2026-11-21
- **Supersedes**: None
- **Superseded By**: None

## Context
`gnn-whois` had two structural issues and several updater standard divergences:
- `gnn-whois.php` wrapped `require_once plugin_dir_path(__FILE__) . 'inc/updater.php'` inside `if (is_admin())`, preventing WP-Cron and WP-CLI from running background plugin updates.
- `gnn-whois/inc/updater.php` (`GNN_Whois_Updater`) checked `is_plugin_active()` inside `after_install()` (after WordPress had already deactivated the plugin for upgrade) instead of capturing state in `upgrader_pre_install`.
- Lacked `wp_remote_head` + `expanded_assets` fallback, semver validation, `*.githubusercontent.com` / `release-assets.githubusercontent.com` SSRF allowlist entries, `wp_normalize_path` + `wp_clean_plugins_cache(true)` in `after_install`, and proper `wp_die(..., '', ['response' => 403])` signature in `handle_manual_check`.
- `check_for_update` did not unset `$transient->response` when up to date and did not set `id` on transient entries.

## Decision
1. **Unconditional Updater Loading**: Removed `if (is_admin())` around loading `inc/updater.php` in `gnn-whois/gnn-whois.php` so WP-Cron and WP-CLI update checks execute properly.
2. **Standardized `GNN_Whois_Updater`**:
   - Added `upgrader_pre_install` (`capture_active_state`) to record `$this->was_active` before deactivation, and restored activation in `after_install` after `wp_clean_plugins_cache(true)`.
   - Added `wp_remote_head` + `/releases/expanded_assets/{tag}` fallback, semver validation, SSRF allowlist (`sanitize_github_url`), and `(object)['failed' => true]` 300-second negative cache.
   - Normalized `check_for_update` (`response` vs `no_update` mutual exclusion, `id` field) and `after_install` (`wp_normalize_path`, `$wp_filesystem->exists($destination)`).

## Consequences
- Background updates via WP-Cron and WP-CLI now function without requiring an admin request.
- Plugin active state is reliably preserved across updates.
- Update checks remain functional when the GitHub API is rate-limited.


## Amendment (2026-11-25): Disk-First Local Version Detection
- **Root Cause:** Reading in-memory version constants (`GNN_*_VERSION` / `$this->version`) first inside `get_local_version()` returned the pre-upgrade version when WordPress fired `pre_set_site_transient_update_plugins` within the same PHP request right after overwriting plugin files, causing the update banner to persist until a second update run.
- **Resolution:** `get_local_version()` now reads the live `Version` header directly from `WP_PLUGIN_DIR . '/' . $this->plugin_basename` via `clearstatcache( true, $main_file )` and `get_file_data()` first, falling back to in-memory constants only if the file is unavailable.
