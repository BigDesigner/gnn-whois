# Task List: GNN Whois

## Phase 1: Foundation & Core Logic ✅
- [x] **INIT-001:** Initialize project structure and `.gitignore`.
- [x] **CORE-001:** Implement WHOIS lookup engine using `fsockopen` on port 43.
- [x] **CORE-002:** Create TLD to WHOIS server mapping logic (`gnn_get_whois_server`).
- [x] **CORE-003:** Implement `[gnn_whois]` shortcode for form and results rendering.
- [x] **UI-001:** Create basic CSS for the lookup form and pre-formatted results.
- [x] **I18N-001:** Setup text domain `gnn-whois` and translation-ready strings.

## Phase 2: Migration & Refactoring (from GNN IPinfo) ✅
- [x] **MIG-001:** Refactor all codebase references from `gnn-ipinfo` to `gnn-whois`.
- [x] **MIG-002:** Rename main plugin file to `gnn-whois.php`.
- [x] **MIG-003:** Rewrite all documentation (`README.md`, `CHANGELOG.md`, `architecture-notes.md`, `security-audit.md`).
- [x] **MIG-004:** Synchronize Memory Bank (`snapshot.md`, `standard.md`, `decisions.md`).
- [x] **MIG-005:** Update AI Agents guardrails (`ai-guardrails.md`).

## Phase 3: DevOps & Release Management ✅
- [x] **OPS-001:** Update GitHub Actions `release.yml` for Whois-specific zip builds.
- [x] **UPD-001:** Implement `GNN_Whois_Updater` class for GitHub-based updates.
- [x] **UPD-002:** Integrate updater include in `gnn-whois.php`.
- [x] **SEC-001:** Conduct security audit for socket safety and output escaping.

## Phase 4: Performance & UI Polish ✅
- [x] **PERF-001:** Implement Transient Caching for WHOIS responses to reduce network load.
- [x] **UI-002:** Design "GNN Premium" Glassmorphism interface for the lookup form.
- [x] **UI-003:** Implement Universal Adaptive CSS (Dark/Light mode support).
- [x] **TLD-001:** Expand TLD mapping to cover all major international extensions.
- [x] **FEAT-001:** Add "Copy Result" button with one-click functionality.
- [x] **ADMIN-001:** Add "Donate" and "Check Updates" links to the Plugins page.

**STATUS: MIGRATION & ENHANCEMENT COMPLETED v1.1.0**

## Backlog
- [ ] **FEAT-002:** Add "Save as PDF" functionality for WHOIS reports.
- [ ] **FEAT-003:** Implement AJAX-based lookup to prevent page refreshes.
- [ ] **FEAT-004:** Add bulk domain lookup capability.
