# Architecture Notes: GNN Whois

## System Design
GNN Whois is a modular, lightweight WordPress plugin designed to perform domain WHOIS lookups. It follows a functional programming approach, utilizing native PHP socket connections and WordPress APIs to ensure compatibility and performance.

## Data Flow
1. **Initiation:** A user visits a page containing the `[gnn_whois]` shortcode.
2. **Shortcode Execution:** The `gnn_whois_shortcode()` function is triggered, rendering the lookup form and handling POST submissions.
3. **Data Retrieval:**
    - The `gnn_whois_lookup()` function first checks the **WordPress Transients API** for a cached response (1-hour expiration).
    - If no cache exists, it identifies the correct WHOIS server via `gnn_get_whois_server()`.
    - A socket connection (`fsockopen`) is established on port 43 to retrieve the raw data.
4. **Data Processing:**
    - The response is sanitized, cached in transients, and formatted for display.
5. **Output Rendering:** The WHOIS data is rendered within a premium "Glassmorphism" container with a "Copy to Clipboard" action.

## Key Components
- **Shortcode API:** Main interface for the frontend lookup form and result display.
- **WHOIS Engine:** TLD-to-server mapping and raw socket communication handler.
- **Adaptive UI:** A universal CSS system that inherits theme colors and applies neutral glassmorphism for light/dark mode compatibility.
- **Caching Layer:** Utilizes WordPress Transients to minimize network load and improve response times for repeat queries.
- **GitHub Updater:** Automated and manual update check system integrated with WordPress Core updates.

## Core Principles
1. **Zero-Conflict CSS:** Uses inheritance (`color: inherit`) and semi-transparent overlays to blend into any theme.
2. **Performance Optimized:** Direct socket connections and aggressive (but safe) caching.
3. **Security First:** Strict input sanitization (`sanitize_text_field`), output escaping (`esc_html`), and nonce verification for admin actions.
4. **Minimalist Dependencies:** No external 3rd-party libraries; purely native PHP and WordPress functions.
