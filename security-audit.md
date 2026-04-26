# Security Audit: GNN Whois

## Threat Models & Mitigations

| Threat | Mitigation Strategy | Status |
|--------|---------------------|--------|
| **SQL Injection** | Not applicable (no custom SQL used), but all interactions with WordPress options would use `$wpdb->prepare()` if required. | ✅ Pass |
| **Cross-Site Scripting (XSS)** | All user inputs and WHOIS server responses are escaped with `esc_html()` before output. | ✅ Pass |
| **Cross-Site Request Forgery (CSRF)** | Form submissions are simple POST requests, but we use `sanitize_text_field` and logic checks. Manual updates in the admin use WordPress nonces. | ✅ Pass |
| **Unauthorized Access** | Admin-side updater functions are restricted to users with `update_plugins` capability. | ✅ Pass |
| **Information Disclosure** | `ABSPATH` guard prevents direct file access across all PHP components. | ✅ Pass |
| **Socket Security** | Domain names are sanitized before being used in socket operations to prevent command injection or malformed requests. | ✅ Pass |

## Constraints & Rules
1. **Input Sanitization:** Use `sanitize_text_field()` for all domain entries.
2. **Strict Escaping:** Every output statement MUST use an escaping function (e.g., `esc_html()`).
3. **No `eval()` or `shell_exec()`:** Strictly forbidden.
4. **Socket Handling:** Ensure `fsockopen` has a reasonable timeout to prevent hanging the server.
5. **Direct Access Protection:** Maintain the `ABSPATH` check at the top of every PHP file.
