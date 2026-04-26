# Architecture Notes: GNN Whois

## System Design
GNN Whois is a modular, lightweight WordPress plugin designed to perform domain WHOIS lookups. It follows a functional programming approach, utilizing native PHP socket connections and WordPress APIs to ensure compatibility and performance.

## Data Flow
1. **Initiation:** A user visits a page containing the `[gnn_whois]` shortcode.
2. **Shortcode Execution:** The `gnn_whois_shortcode()` function is triggered, rendering the modern pill search bar.
3. **Data Retrieval:**
    - Checks the **WordPress Transients API** for a cached response (1-hour expiration).
    - If no cache exists, identifies the WHOIS server via `gnn_get_whois_server()`.
    - Establishes a socket connection (`fsockopen`) on port 43 to retrieve raw data.
4. **Data Processing:**
    - Raw data is sanitized and cached in transients.
5. **Output Rendering:** The WHOIS results flow naturally within the theme's content area, styled with a monochrome adaptive font.

## Key Components
- **Pill Search UI:** A modern, animated search interface with floating effects and CSS-based icons.
- **Natural Result Flow:** A non-restricted rendering system that lets content flow naturally vertically.
- **WHOIS Engine:** TLD-to-server mapping and raw socket communication handler.
- **Adaptive CSS:** A universal styling system that inherits theme typography and colors.
- **Caching Layer:** Utilizes WordPress Transients to minimize network load.
- **GitHub Updater:** Integrated version management system.

## Core Principles
1. **Seamless Integration:** Results flow like regular page content, respecting the theme's container and spacing.
2. **Performance Optimized:** Pure CSS icons and socket-level communication.
3. **Security First:** Strict input sanitization and output escaping.
4. **Zero-Conflict Asset Loading:** Versioned CSS to prevent browser caching issues.
