<?php
/**
 * Plugin Name: GNN Whois
 * Description: A simple WHOIS lookup plugin. <strong>Shortcode:</strong> <code>[gnn-whois]</code>
 * Version: 1.1.0
 * Author: BigDesigner
 * Text Domain: gnn-whois
 * Domain Path: /languages
 */

// Protect the plugin from direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin version
if (!defined('GNN_WHOIS_VERSION')) {
    define('GNN_WHOIS_VERSION', '1.1.0');
}

// Load plugin text domain for translations
function gnn_whois_load_textdomain() {
    load_plugin_textdomain('gnn-whois', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'gnn_whois_load_textdomain');

// Add settings and donation links to the plugin page
function gnn_whois_plugin_action_links($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $donate_link = '<a href="https://buymeacoffee.com/bigdesigner" target="_blank" style="font-weight:bold; color:#d63638;">' . esc_html__('Donate', 'gnn-whois') . '</a>';
        
        // Manual Update Check Link
        $update_url = wp_nonce_url(admin_url('plugins.php?gnn_whois_check_update=1'), 'gnn_whois_manual_update');
        $update_link = '<a href="' . esc_url($update_url) . '">' . esc_html__('Check Updates', 'gnn-whois') . '</a>';
        
        array_unshift($links, $donate_link, $update_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'gnn_whois_plugin_action_links', 10, 2);

// Include updater
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'inc/updater.php';
}

// Enqueue custom styles
function gnn_whois_enqueue_styles() {
    wp_enqueue_style('gnn-whois-styles', plugins_url('styles.css', __FILE__), array(), GNN_WHOIS_VERSION);
}
add_action('wp_enqueue_scripts', 'gnn_whois_enqueue_styles');

// Register the shortcode
function gnn_whois_shortcode($atts) {
    $output = '<div class="gnn-whois-form-wrapper">';
    $output .= '<div class="gnn-whois-form">';
    $output .= '<form method="post">';
    $output .= '<input type="text" name="domain" placeholder="' . esc_attr__('Enter domain name', 'gnn-whois') . '">';
    $output .= '<input type="submit" value="' . esc_attr__('Lookup', 'gnn-whois') . '">';
    $output .= '</form>';
    $output .= '</div>';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['domain'])) {
        $domain = sanitize_text_field($_POST['domain']);
        $whois_data = gnn_whois_lookup($domain);
        $output .= '<div class="gnn-whois-result-container">';
        $output .= '<div class="gnn-whois-result-actions">';
        $output .= '<button type="button" class="gnn-whois-copy-btn" onclick="gnnWhoisCopyResult(this)">' . esc_html__('Copy Results', 'gnn-whois') . '</button>';
        $output .= '</div>';
        $output .= '<pre class="gnn-whois-result" id="gnn-whois-result-text">' . esc_html($whois_data) . '</pre>';
        $output .= '</div>';

        // Simple inline script for copy functionality
        $output .= '<script>
        function gnnWhoisCopyResult(btn) {
            const text = document.getElementById("gnn-whois-result-text").innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerText;
                btn.innerText = "' . esc_js(__('Copied!', 'gnn-whois')) . '";
                btn.classList.add("copied");
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove("copied");
                }, 2000);
            });
        }
        </script>';
    }

    $output .= '</div>'; // End wrapper

    return $output;
}
add_shortcode('gnn_whois', 'gnn_whois_shortcode');

// Function to get WHOIS server based on domain extension
function gnn_get_whois_server($domain) {
    $tld = substr(strrchr($domain, '.'), 1);
    $whois_servers = array(
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'info' => 'whois.afilias.net',
        'biz' => 'whois.neulevel.biz',
        'us' => 'whois.nic.us',
        'co.uk' => 'whois.nic.uk',
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

    return isset($whois_servers[$tld]) ? $whois_servers[$tld] : 'whois.iana.org';
}

// Function to perform WHOIS lookup
function gnn_whois_lookup($domain) {
    $cache_key = 'gnn_whois_' . md5($domain);
    $cached_response = get_transient($cache_key);

    if (false !== $cached_response) {
        return $cached_response;
    }

    $server = gnn_get_whois_server($domain);
    $port = 43;
    $timeout = 10;

    $fp = fsockopen($server, $port, $errno, $errstr, $timeout);
    if (!$fp) {
        $response = sprintf(esc_html__('Connection error: %d - %s', 'gnn-whois'), $errno, $errstr);
    } else {
        fwrite($fp, $domain . "\r\n");
        $response = '';
        while (!feof($fp)) {
            $response .= fgets($fp, 128);
        }
        fclose($fp);
    }

    // Cache the response for 1 hour
    if (!empty($response)) {
        set_transient($cache_key, $response, HOUR_IN_SECONDS);
    }

    return $response;
}