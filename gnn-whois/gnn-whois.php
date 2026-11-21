<?php
/**
 * Plugin Name:       GNN Whois
 * Description:       A simple WHOIS lookup plugin. <strong>Shortcode:</strong> <code>[gnn_whois]</code>
 * Version:           1.1.1
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            BigDesigner
 * Author URI:        https://github.com/BigDesigner
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gnn-whois
 * Domain Path:       /languages
 */

// Protect the plugin from direct access.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin version.
if (!defined('GNN_WHOIS_VERSION')) {
    define('GNN_WHOIS_VERSION', '1.1.1');
}

/**
 * Load plugin text domain on init (WP 6.7+ JIT translation requirement).
 */
function gnn_whois_load_textdomain()
{
    load_plugin_textdomain('gnn-whois', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'gnn_whois_load_textdomain', 1);

/**
 * Add Donate, Settings, and Check Updates links on the Plugins screen.
 *
 * @param array $links Existing plugin action links.
 * @return array
 */
function gnn_whois_plugin_action_links($links)
{
    $donate_link = '<a href="https://buymeacoffee.com/bigdesigner" target="_blank" rel="noopener noreferrer" style="font-weight:bold; color:#d63638;">' . esc_html__('Donate', 'gnn-whois') . '</a>';

    $settings_url  = admin_url('admin.php?page=gnn-whois');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('Settings', 'gnn-whois') . '</a>';

    $update_url  = wp_nonce_url(admin_url('plugins.php?gnn_whois_check_update=1'), 'gnn_whois_manual_update');
    $update_link = '<a href="' . esc_url($update_url) . '">' . esc_html__('Check Updates', 'gnn-whois') . '</a>';

    array_unshift($links, $donate_link, $settings_link, $update_link);

    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gnn_whois_plugin_action_links');

/**
 * Register admin menu at position '79.106' (GNN plugin band — ADR #9).
 *
 * Position is written as a quoted string literal to avoid PHP float-truncation
 * collisions. Only one top-level entry is registered here; any additional
 * GNN Whois admin screens must be nested via add_submenu_page().
 */
function gnn_whois_register_menu()
{
    add_menu_page(
        __('GNN Whois', 'gnn-whois'),
        __('GNN Whois', 'gnn-whois'),
        'manage_options',
        'gnn-whois',
        'gnn_whois_admin_page',
        'dashicons-admin-site-alt3',
        '79.106'
    );
}
add_action('admin_menu', 'gnn_whois_register_menu');

/**
 * Clear cached WHOIS lookup transients (preserving rate-limit windows).
 *
 * @return int Number of deleted option rows.
 */
function gnn_whois_clear_lookup_cache()
{
    global $wpdb;

    $like_transient     = $wpdb->esc_like('_transient_gnn_whois_') . '%';
    $like_timeout       = $wpdb->esc_like('_transient_timeout_gnn_whois_') . '%';
    $like_rl_transient  = $wpdb->esc_like('_transient_gnn_whois_rl_') . '%';
    $like_rl_timeout    = $wpdb->esc_like('_transient_timeout_gnn_whois_rl_') . '%';

    return (int) $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options}
             WHERE (option_name LIKE %s OR option_name LIKE %s)
               AND option_name NOT LIKE %s
               AND option_name NOT LIKE %s",
            $like_transient,
            $like_timeout,
            $like_rl_transient,
            $like_rl_timeout
        )
    );
}

/**
 * Render the GNN Whois admin page.
 */
function gnn_whois_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gnn-whois'), 403);
    }

    $cache_cleared = false;

    if (
        isset($_SERVER['REQUEST_METHOD']) &&
        'POST' === $_SERVER['REQUEST_METHOD'] &&
        isset($_POST['gnn_whois_clear_cache'])
    ) {
        check_admin_referer('gnn_whois_clear_cache_action', 'gnn_whois_clear_cache_nonce');
        gnn_whois_clear_lookup_cache();
        $cache_cleared = true;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('GNN Whois', 'gnn-whois') . '</h1>';

    if ($cache_cleared) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('WHOIS lookup cache cleared.', 'gnn-whois') . '</p></div>';
    }

    echo '<p>' . esc_html__('Use the shortcode [gnn_whois] to embed the WHOIS lookup form on any page or post.', 'gnn-whois') . '</p>';
    echo '<table class="widefat striped" style="max-width:600px; margin-top:16px;">';
    echo '<tbody>';
    echo '<tr><th scope="row">' . esc_html__('Version', 'gnn-whois') . '</th><td><code>' . esc_html(GNN_WHOIS_VERSION) . '</code></td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Shortcode', 'gnn-whois') . '</th><td><code>[gnn_whois]</code></td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Rate Limit', 'gnn-whois') . '</th><td>' . esc_html__('3 lookups per minute per IP', 'gnn-whois') . '</td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Cache TTL', 'gnn-whois') . '</th><td>' . esc_html__('1 hour (Transients API)', 'gnn-whois') . '</td></tr>';
    echo '</tbody>';
    echo '</table>';

    echo '<form method="post" style="margin-top:20px;">';
    wp_nonce_field('gnn_whois_clear_cache_action', 'gnn_whois_clear_cache_nonce');
    echo '<input type="hidden" name="gnn_whois_clear_cache" value="1">';
    submit_button(__('Clear WHOIS Cache', 'gnn-whois'), 'secondary', 'submit', false);
    echo '</form>';
    echo '</div>';
}

// Include updater.
require_once plugin_dir_path(__FILE__) . 'inc/updater.php';

/**
 * Register frontend styles and scripts, and enqueue early when shortcode is present.
 */
function gnn_whois_register_assets()
{
    wp_register_style(
        'gnn-whois-styles',
        plugins_url('styles.css', __FILE__),
        array(),
        GNN_WHOIS_VERSION
    );

    wp_register_script(
        'gnn-whois-script',
        plugins_url('script.js', __FILE__),
        array(),
        GNN_WHOIS_VERSION,
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
    );

    wp_localize_script(
        'gnn-whois-script',
        'gnnWhoisL10n',
        array(
            'copied'     => __('Copied!', 'gnn-whois'),
            'copyFailed' => __('Unable to copy results.', 'gnn-whois'),
        )
    );

    global $post;
    if (is_singular() && $post instanceof WP_Post && has_shortcode($post->post_content, 'gnn_whois')) {
        wp_enqueue_style('gnn-whois-styles');
        wp_enqueue_script('gnn-whois-script');
    }
}
add_action('wp_enqueue_scripts', 'gnn_whois_register_assets');

/**
 * Normalize and validate a user-supplied domain name.
 *
 * Strips optional URL schemes/paths, lowercases, converts IDNs to ASCII Punycode
 * when intl is available, and enforces RFC-compliant hostname syntax to prevent
 * WHOIS protocol/flag injection.
 *
 * @param string $raw_domain Raw user input.
 * @return string Normalized valid domain name, or empty string if invalid.
 */
function gnn_whois_normalize_domain($raw_domain)
{
    $domain = trim((string) $raw_domain);
    if ('' === $domain) {
        return '';
    }

    if (false !== strpos($domain, '://') || false !== strpos($domain, '/')) {
        $to_parse = (false === strpos($domain, '://')) ? 'https://' . $domain : $domain;
        $host     = wp_parse_url($to_parse, PHP_URL_HOST);
        if (!is_string($host) || '' === $host) {
            return '';
        }
        $domain = $host;
    }

    $domain = strtolower(trim($domain, ". \t\n\r\0\x0B"));

    if ('' === $domain) {
        return '';
    }

    if (function_exists('idn_to_ascii') && preg_match('/[^\x20-\x7e]/', $domain)) {
        $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
        $ascii   = idn_to_ascii($domain, 0, $variant);
        if (false === $ascii) {
            return '';
        }
        $domain = strtolower($ascii);
    }

    if (strlen($domain) > 253) {
        return '';
    }

    if (1 !== preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
        return '';
    }

    return $domain;
}

/**
 * Render the [gnn_whois] shortcode.
 *
 * @param array $atts Shortcode attributes (unused).
 * @return string
 */
function gnn_whois_shortcode($atts)
{
    static $instance = 0;
    $instance++;

    wp_enqueue_style('gnn-whois-styles');
    wp_enqueue_script('gnn-whois-script');

    $input_id  = 'gnn-whois-input-' . $instance;
    $result_id = 'gnn-whois-result-' . $instance;

    $submitted_domain = '';
    $is_post          = isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'];

    if ($is_post && isset($_POST['domain'])) {
        $raw_input        = sanitize_text_field(wp_unslash($_POST['domain']));
        $submitted_domain = $raw_input;
    }

    $output  = '<div class="gnn-whois-form-wrapper">';
    $output .= '<div class="gnn-whois-search">';
    $output .= '<form method="post">';
    $output .= '<label class="gnn-whois-sr-only" for="' . esc_attr($input_id) . '">' . esc_html__('Domain name', 'gnn-whois') . '</label>';
    $output .= '<input type="text" id="' . esc_attr($input_id) . '" name="domain" value="' . esc_attr($submitted_domain) . '" placeholder="' . esc_attr__('example.com', 'gnn-whois') . '" inputmode="url" autocapitalize="none" autocorrect="off" spellcheck="false" autocomplete="off" required>';
    $output .= '<button type="submit">';
    $output .= '<span class="gnn-whois-label">' . esc_html__('Whois', 'gnn-whois') . '</span>';
    $output .= '<span class="gnn-whois-search-icon" aria-hidden="true"></span>';
    $output .= '</button>';
    $output .= '</form>';
    $output .= '</div>';

    if ($is_post && '' !== trim($submitted_domain)) {
        $domain = gnn_whois_normalize_domain($submitted_domain);

        if ('' === $domain) {
            $output .= '<div class="gnn-whois-result-container" role="status" aria-live="polite">';
            $output .= '<pre class="gnn-whois-result">' . esc_html__('Please enter a valid domain name (e.g. example.com).', 'gnn-whois') . '</pre>';
            $output .= '</div>';
            $output .= '</div>';
            return $output;
        }

        if (!gnn_whois_check_rate_limit()) {
            $output .= '<div class="gnn-whois-result-container" role="status" aria-live="polite">';
            $output .= '<pre class="gnn-whois-result">' . esc_html__('Rate limit exceeded. Please wait a minute before trying again (max 3 lookups per minute).', 'gnn-whois') . '</pre>';
            $output .= '</div>';
            $output .= '</div>';
            return $output;
        }

        $whois_data = gnn_whois_lookup($domain);
        $output    .= '<div class="gnn-whois-result-container">';
        $output    .= '<div class="gnn-whois-result-actions">';
        $output    .= '<button type="button" class="gnn-whois-copy-btn" data-target="' . esc_attr($result_id) . '">' . esc_html__('Copy Results', 'gnn-whois') . '</button>';
        $output    .= '<span class="gnn-whois-sr-only gnn-whois-copy-status" aria-live="polite"></span>';
        $output    .= '</div>';
        $output    .= '<pre class="gnn-whois-result" id="' . esc_attr($result_id) . '">' . esc_html($whois_data) . '</pre>';
        $output    .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('gnn_whois', 'gnn_whois_shortcode');

/**
 * Get the authoritative WHOIS server for a normalized domain name.
 *
 * Checks two-level TLDs (such as co.uk) before single-level TLDs so compound
 * suffixes resolve correctly instead of falling back to IANA.
 *
 * @param string $domain Normalized domain name.
 * @return string WHOIS server hostname.
 */
function gnn_whois_get_server($domain)
{
    $whois_servers = array(
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'info' => 'whois.afilias.net',
        'biz' => 'whois.neulevel.biz',
        'us' => 'whois.nic.us',
        'co.uk' => 'whois.nic.uk',
        'uk' => 'whois.nic.uk',
        'ca' => 'whois.cira.ca',
        'de' => 'whois.denic.de',
        'eu' => 'whois.eu',
        'fr' => 'whois.nic.fr',
        'it' => 'whois.nic.it',
        'nl' => 'whois.domain-registry.nl',
        'se' => 'whois.iis.se',
        'nu' => 'whois.nic.nu',
        'asia' => 'whois.nic.asia',
        'mobi' => 'whois.dotmobiregistry.net',
        'cc' => 'whois.nic.cc',
        'tv' => 'whois.nic.tv',
        'edu' => 'whois.educause.edu',
        'gov' => 'whois.nic.gov',
        'int' => 'whois.iana.org',
        'jobs' => 'jobswhois.verisign-grs.com',
        'mil' => 'whois.nic.mil',
        'name' => 'whois.nic.name',
        'pro' => 'whois.dotproregistry.net',
        'aero' => 'whois.aero',
        'coop' => 'whois.nic.coop',
        'museum' => 'whois.museum',
        'travel' => 'whois.nic.travel',
        'cat' => 'whois.nic.cat',
        'tel' => 'whois.nic.tel',
        'xxx' => 'whois.nic.xxx',
        'me' => 'whois.nic.me',
        'in' => 'whois.registry.in',
        'cn' => 'whois.cnnic.cn',
        'jp' => 'whois.jprs.jp',
        'kr' => 'whois.kr',
        'ru' => 'whois.tcinet.ru',
        'br' => 'whois.registro.br',
        'au' => 'whois.auda.org.au',
        'za' => 'whois.registry.net.za',
        'pl' => 'whois.dns.pl',
        'be' => 'whois.dns.be',
        'ch' => 'whois.nic.ch',
        'at' => 'whois.nic.at',
        'es' => 'whois.nic.es',
        'pt' => 'whois.dns.pt',
        'gr' => 'whois.iana.org',
        'hk' => 'whois.hkirc.hk',
        'sg' => 'whois.sgnic.sg',
        'th' => 'whois.thnic.co.th',
        'my' => 'whois.mynic.my',
        'id' => 'whois.pandi.or.id',
        'ph' => 'whois.dot.ph',
        'nz' => 'whois.srs.net.nz',
        'ua' => 'whois.ua',
        'by' => 'whois.cctld.by',
        'kz' => 'whois.nic.kz',
        'tr' => 'whois.nic.tr',
        'ae' => 'whois.aeda.net.ae',
        'qa' => 'whois.registry.qa',
        'sa' => 'whois.nic.net.sa',
        'bn' => 'whois.bn',
        'io' => 'whois.nic.io',
        'co' => 'whois.nic.co',
        'ai' => 'whois.nic.ai',
        'app' => 'whois.nic.google',
        'dev' => 'whois.nic.google',
        'page' => 'whois.nic.google',
        'cloud' => 'whois.nic.cloud',
        'sh' => 'whois.nic.sh',
        'tech' => 'whois.nic.tech',
        'online' => 'whois.nic.online',
        'site' => 'whois.nic.site',
        'store' => 'whois.nic.store',
    );

    $parts = explode('.', strtolower((string) $domain));
    $count = count($parts);

    if ($count >= 3) {
        $two_level = $parts[$count - 2] . '.' . $parts[$count - 1];
        if (isset($whois_servers[$two_level])) {
            return $whois_servers[$two_level];
        }
    }

    if ($count >= 2) {
        $tld = $parts[$count - 1];
        if (isset($whois_servers[$tld])) {
            return $whois_servers[$tld];
        }
    }

    return 'whois.iana.org';
}

/**
 * Rate limit lookups per client IP (max 3 per minute).
 *
 * Uses a fixed 60-second window keyed by the visitor's IP address. Returns
 * true when the request is allowed (and increments the counter), or false
 * when the limit for the current window has already been reached.
 *
 * @return bool
 */
function gnn_whois_check_rate_limit()
{
    $ip        = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $cache_key = 'gnn_whois_rl_' . md5($ip);

    $count = (int) get_transient($cache_key);

    if ($count >= 3) {
        return false;
    }

    set_transient($cache_key, $count + 1, MINUTE_IN_SECONDS);

    return true;
}

/**
 * Perform a WHOIS lookup over port 43 with strict timeouts, size caps, and caching.
 *
 * Transient caching is performed only on valid non-error responses so temporary
 * network failures are never cached for an hour.
 *
 * @param string $domain Normalized domain name.
 * @return string Plain-text WHOIS response or error message.
 */
function gnn_whois_lookup($domain)
{
    $domain = gnn_whois_normalize_domain($domain);
    if ('' === $domain) {
        return __('Please enter a valid domain name (e.g. example.com).', 'gnn-whois');
    }

    $cache_key       = 'gnn_whois_' . md5($domain);
    $cached_response = get_transient($cache_key);

    if (false !== $cached_response) {
        return $cached_response;
    }

    $server    = gnn_whois_get_server($domain);
    $port      = 43;
    $timeout   = 10;
    $max_bytes = 524288; // 512 KB cap to prevent memory exhaustion.

    $errno  = 0;
    $errstr = '';
    $fp     = @fsockopen($server, $port, $errno, $errstr, $timeout);

    if (!$fp) {
        return __('Unable to connect to the WHOIS server. Please try again later.', 'gnn-whois');
    }

    stream_set_timeout($fp, $timeout);
    fwrite($fp, $domain . "\r\n");

    $response  = '';
    $timed_out = false;

    while (!feof($fp)) {
        $chunk = fgets($fp, 4096);
        $meta  = stream_get_meta_data($fp);
        if (!empty($meta['timed_out'])) {
            $timed_out = true;
            break;
        }
        if (false === $chunk) {
            break;
        }
        $response .= $chunk;
        if (strlen($response) >= $max_bytes) {
            $response = substr($response, 0, $max_bytes);
            break;
        }
    }

    fclose($fp);

    if ($timed_out || '' === trim($response)) {
        return __('WHOIS lookup timed out or returned an empty response. Please try again.', 'gnn-whois');
    }

    set_transient($cache_key, $response, HOUR_IN_SECONDS);

    return $response;
}
