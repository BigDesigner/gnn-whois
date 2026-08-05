# Changelog

All notable changes to the **GNN Whois** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to Semantic Versioning.

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
