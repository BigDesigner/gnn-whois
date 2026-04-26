# Changelog

All notable changes to the **GNN Whois** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to Semantic Versioning.

## [1.1.0] - 2026-04-26
### Added
- **Performance:** Implemented Transient Caching (1 hour) for WHOIS responses.
- **TLD Expansion:** Added support for major international extensions (.io, .ai, .dev, .app, etc.).
- **Premium UI:** Introduced "Glassmorphism" design with universal theme compatibility and Dark Mode support.
- **UX:** Added "Copy Results" button with interactive feedback.
- **Admin:** Added "Donate" and "Check Updates" links to the WordPress Plugins page.

## [1.0.1] - 2026-04-26
### Added
- Initial project structure for GNN Whois plugin.
- Implemented core WHOIS lookup logic using fsockopen.
- Added `[gnn_whois]` shortcode for the lookup form.
- Multi-TLD support for various domain extensions.
- Integrated GitHub Updater for automated releases.
- Basic responsive CSS for the lookup form and results.
