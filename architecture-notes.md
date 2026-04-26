# Architecture Notes: GNN Whois

## System Design
GNN Whois is a modular, lightweight WordPress plugin designed to perform domain WHOIS lookups. It follows a functional programming approach, utilizing native PHP socket connections and WordPress APIs to ensure compatibility and performance.

## Data Flow
1. **Initiation:** A user visits a page containing the `[gnn_whois]` shortcode.
2. **Shortcode Execution:** The `gnn_whois_shortcode()` function is triggered, rendering the lookup form.
3. **User Input:** The user enters a domain name and submits the form.
4. **Data Retrieval:**
    - The `gnn_whois_lookup()` function identifies the correct WHOIS server based on the domain's TLD.
    - A socket connection is established to the WHOIS server on port 43.
    - The domain query is sent, and the server response is collected.
5. **Data Processing:**
    - The response is sanitized and formatted for display.
6. **Output Rendering:** The WHOIS data is output within a pre-formatted container below the form.

## Key Components
- **Shortcode API:** Provides the `[gnn_whois]` tag to embed the lookup form and results.
- **WHOIS Lookup Engine:** A custom logic to map TLDs to their respective WHOIS servers and handle socket communications.
- **Styles:** Custom CSS for a clean, responsive lookup interface.
- **Localization:** Full support for internationalization (i18n) via `.po`/`.mo` files.
- **GitHub Updater:** Integrates with the WordPress update system to provide automated updates from GitHub Releases.

## Core Principles
1. **Direct Communication:** Uses fsockopen for direct communication with WHOIS servers, avoiding 3rd-party API dependencies.
2. **Native Integration:** Hooks into standard WordPress actions and filters for script enqueuing and shortcode registration.
3. **Security First:** Strict sanitization of user-submitted domain names and escaping of all output data.
4. **Lightweight:** Minimal overhead, ensuring fast performance and compatibility with any WordPress theme.
