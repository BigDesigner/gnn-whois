# Architecture Decision Records (ADR)

## [ADR-001] Memory Bank System
- **Status:** Accepted
- **Context:** Need for persistent context management across AI sessions.
- **Decision:** Implement a structured `/memory-bank/` and support directories.
- **Consequences:** Higher overhead for session starts, but zero context loss.

## [ADR-002] Functional vs Object-Oriented Approach
- **Status:** Accepted
- **Context:** The plugin is currently small and uses functional hooks for its core logic.
- **Decision:** Stick to a functional approach for core logic with strict `gnn_whois_` prefixing.
- **Rationale:** Minimizes complexity for a simple utility plugin.
- **Consequences:** Easy to maintain and debug.

## [ADR-003] Direct Socket Communication
- **Status:** Accepted
- **Context:** WHOIS data is traditionally retrieved via port 43.
- **Decision:** Use PHP `fsockopen` to communicate directly with WHOIS servers.
- **Rationale:** Avoids dependency on 3rd party APIs and provides more flexible data retrieval.
- **Consequences:** Requires the server to have `fsockopen` enabled.

## [ADR-004] Integrated GitHub Updater
- **Status:** Accepted
- **Context:** Need a way to manage updates for a plugin hosted on GitHub.
- **Decision:** Use a custom `GNN_Whois_Updater` class to track releases and integrate with WordPress updates.
- **Rationale:** Provides a seamless update experience for users.
- **Consequences:** Requires consistent GitHub release management.

## [ADR-005] Security & Hardening
- **Status:** Accepted
- **Context:** Ensuring production readiness and protecting against common vulnerabilities.
- **Decision:** Implement strict ABSPATH guards, input sanitization, and output escaping.
- **Rationale:** Follows WordPress security best practices.
- **Consequences:** Secure and reliable plugin operation.
