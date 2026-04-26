# Project Standards & Linting Protocols

## 1. Naming Conventions
- **Files:** `lowercase-kebab-case.php`
- **Classes:** `PascalCase` (prefixed with `GNN_Whois_`)
- **Functions:** `snake_case` (prefixed with `gnn_whois_`)
- **Variables:** `snake_case`
- **CSS Classes:** BEM methodology (`gnn-whois__element--modifier`)
- **Text Domain:** `gnn-whois`

## 2. Code Quality
- **PHP:** PSR-12 compliance.
- **JS:** Standard JS with ES6+ features.
- **CSS:** Vanilla CSS with custom properties (CSS variables). **Priority:** Zero-conflict theme compatibility.

## 3. Documentation
- All functions MUST have PHPDoc blocks.
- Major logic blocks require inline comments explaining the "why", not just the "how".
- WHOIS server mappings and socket logic must be clearly documented.

## 4. Plugin Architecture
- **Safety:** All files must include `if ( ! defined( 'ABSPATH' ) ) exit;` guard.
- **Hooks:** Use appropriate hooks (`init`, `wp_enqueue_scripts`, etc.).
- **Global Namespace:** Avoid polluting the global namespace; use prefixes for EVERYTHING.
- **Deactivation/Uninstall:** Clean up any options or transients on uninstall.

## 5. Settings & UX Standards
- **UI/UX:** The lookup form should be clean, responsive, and follow modern design principles.
- **Form Handling:** Secure POST handling with proper sanitization and escaping.

## 6. Security & Documentation Research
- **Official Sources:** The WordPress Developer Resources (Plugin Handbook) are the primary sources of truth.
- **Security Protocols:**
    - All domain inputs must be sanitized using `sanitize_text_field()`.
    - All output must be escaped using `esc_html()` or `esc_attr()`.
    - Nonces should be used for administrative actions (like manual update checks).
    - Permission checks (`current_user_can()`) must be performed before any admin action.

## 7. Consultative & Mentorship Approach
- **Proactive Suggestions:** Evaluate if a better, more modern, or more user-friendly way exists.
- **Educational Context:** Explain "why" for WordPress plugin development best practices.

## 8. Performance & Optimization
- **Socket Efficiency:** Use `fsockopen` with reasonable timeouts.
- **Transients:** Plan to implement caching for WHOIS responses to reduce redundant network requests.
- **No Dependencies:** Avoid 3rd party libraries unless absolutely necessary.

## 9. Development Integrity & Verification
- **Verification Methods:** PHP lint (`php -l`), CSS validation, and functional testing.
- **Atomic Commits:** Each commit must represent a single, verified change.

## 10. Localization (i18n)
- All user-facing strings MUST be translatable using `__()`, `_e()`, etc.
- Text domain `gnn-whois` must be used consistently.

## 11. Changelog Management
- **Format:** Follow the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) standard.
- **Update Rule:** Every version bump in the main plugin file header MUST have a corresponding entry in `CHANGELOG.md`.
