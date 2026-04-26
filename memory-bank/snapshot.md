# Project Snapshot: GNN Whois

## System Status
- **Plugin Version:** 1.4.0
- **Current Branch:** main
- **Status:** Active / Production Ready
- **Core Principles:** 
    - Zero 3rd-party Dependency (PHP side)
    - Native WHOIS lookup via fsockopen
    - Shortcode-driven interface
    - Performance via direct socket communication

## Active Features
- **Shortcode `[gnn_whois]`:** Renders domain lookup form and displays WHOIS results.
- **Multi-TLD Support:** Supports a wide range of domain extensions.
- **GitHub Updater:** Automated update checking via GitHub Releases.
- **Localization:** Text domain `gnn-whois` configured for translations.
- **Styling:** Basic responsive CSS for form and results display.
- **Security:** Sanitized inputs and escaped output data.

## Module Map
| File | Responsibility |
|------|---------------|
| `gnn-whois.php` | Main plugin file, hooks, shortcode logic, and lookup engine. |
| `inc/updater.php` | GitHub release tracking and WordPress update integration. |
| `styles.css` | Frontend styling for the lookup form and results. |
| `languages/` | Translation files (.po, .mo). |
| `memory-bank/` | Project documentation and state management. |

## Development Status
- **Phase:** Refactoring / Maintenance.
- **Upcoming Goals:**
    - Enhance UI with "GNN Premium" aesthetics (Glassmorphism).
    - Expand TLD support with more WHOIS servers.
    - Implement caching for WHOIS responses using Transients API.
    - Add "Save as PDF" or "Export" functionality for WHOIS reports.

## Environment
- **Platform:** WordPress
- **PHP Version:** >= 7.4
- **Dependencies:** PHP `fsockopen` enabled.
