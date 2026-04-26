# Changelog

All notable changes to the **GNN Whois** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to Semantic Versioning.

## [1.4.0] - 2026-04-26
### Changed
- **UX Refinement:** Updated search button label to "Whois" and placeholder to "example.com" for better clarity.
- **Style Polish:** Removed the floating focus animation from the search bar to create a more stable and professional feel.
- **Maintenance:** Updated internal versioning constants.

## [1.3.0] - 2026-04-26
### Added
- **Modern UI Redesign:** Implemented a new "Pill Search" bar with floating focus animations and expanding labels.
- **Natural Result Flow:** Removed the restricted result frame; WHOIS data now flows naturally within the theme's content area.
- **CSS Icons:** Switched to pure CSS search icons to eliminate external image dependencies.
- **Responsive Layout:** Optimized the pill search bar for mobile devices.

## [1.2.0] - 2026-04-26
### Added
- **Deep Inspect UI:** Total CSS rewrite for 100% theme compatibility.
- **Universal Adaptation:** Implemented `all: initial` reset and `color: inherit` logic to match any theme text color perfectly.
- **Improved Isolation:** Added inner container structure for better styling protection.
- **Stability:** Finalized theme-native look and feel.

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
