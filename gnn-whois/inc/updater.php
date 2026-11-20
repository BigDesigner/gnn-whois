<?php
/**
 * GNN GitHub Plugin Updater
 *
 * Checks the GitHub Releases API for new versions and integrates
 * with the WordPress plugin update system.
 *
 * @package GNN_Whois
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GNN_Whois_Updater
 */
class GNN_Whois_Updater
{
    /**
     * GitHub repository owner/name.
     */
    private $repo = 'BigDesigner/gnn-whois';

    /**
     * Plugin slug (directory name/main file).
     */
    private $plugin_slug = 'gnn-whois/gnn-whois.php';

    /**
     * Transient key for caching the GitHub API response.
     */
    private $transient_key = 'gnn_whois_github_update_check';

    /**
     * Sentinel stored in transient when an API request fails.
     */
    private $error_sentinel = 'error';

    /**
     * Cache duration (12 hours).
     */
    private $cache_duration = 43200;

    /**
     * Minimum supported WordPress version.
     */
    private $requires_wp = '6.7';

    /**
     * Tested up to WordPress version.
     */
    private $tested_wp = '7.1.5';

    /**
     * Minimum supported PHP version.
     */
    private $requires_php = '7.4';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

        add_action('admin_init', array($this, 'handle_manual_check'));
        add_action('load-update-core.php', array($this, 'clear_cache'));
    }

    /**
     * Get the current plugin version without triggering early textdomain loading.
     *
     * @return string
     */
    private function get_local_version()
    {
        if (defined('GNN_WHOIS_VERSION')) {
            return GNN_WHOIS_VERSION;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_file = WP_PLUGIN_DIR . '/' . $this->plugin_slug;
        if (!file_exists($plugin_file)) {
            return '0.0.0';
        }

        $plugin_data = get_plugin_data($plugin_file, false, false);
        return isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0.0';
    }

    /**
     * Validate and sanitize a GitHub package or release URL.
     *
     * @param string $url Raw URL from GitHub API.
     * @return string Sanitized HTTPS URL on an allowed GitHub host, or empty string.
     */
    private function validate_github_url($url)
    {
        $clean_url = esc_url_raw((string) $url, array('https'));
        if ('' === $clean_url) {
            return '';
        }

        $parsed = wp_parse_url($clean_url);
        if (empty($parsed['scheme']) || 'https' !== strtolower($parsed['scheme']) || empty($parsed['host'])) {
            return '';
        }

        $allowed_hosts = array(
            'github.com',
            'api.github.com',
            'objects.githubusercontent.com',
            'codeload.github.com',
        );

        if (!in_array(strtolower($parsed['host']), $allowed_hosts, true)) {
            return '';
        }

        return $clean_url;
    }

    /**
     * Fetch the latest release data from GitHub API.
     *
     * @return object|false
     */
    private function get_remote_release()
    {
        $cached = get_transient($this->transient_key);
        if ($this->error_sentinel === $cached) {
            return false;
        }
        if (false !== $cached && is_object($cached) && !empty($cached->version) && !empty($cached->download_url)) {
            return $cached;
        }

        $url  = sprintf('https://api.github.com/repos/%s/releases/latest', $this->repo);
        $args = array(
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
            ),
            'timeout' => 10,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            set_transient($this->transient_key, $this->error_sentinel, 300);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if (JSON_ERROR_NONE !== json_last_error() || empty($body) || !isset($body->tag_name)) {
            set_transient($this->transient_key, $this->error_sentinel, 300);
            return false;
        }

        $remote_version = ltrim(sanitize_text_field((string) $body->tag_name), 'vV');
        if ('' === $remote_version) {
            set_transient($this->transient_key, $this->error_sentinel, 300);
            return false;
        }

        $download_url = '';

        if (!empty($body->assets) && is_array($body->assets)) {
            foreach ($body->assets as $asset) {
                if (
                    !empty($asset->name) &&
                    !empty($asset->browser_download_url) &&
                    '.zip' === strtolower(substr((string) $asset->name, -4))
                ) {
                    $download_url = $this->validate_github_url($asset->browser_download_url);
                    if ('' !== $download_url) {
                        break;
                    }
                }
            }
        }

        if ('' === $download_url && !empty($body->zipball_url)) {
            $download_url = $this->validate_github_url($body->zipball_url);
        }

        if ('' === $download_url) {
            set_transient($this->transient_key, $this->error_sentinel, 300);
            return false;
        }

        $html_url = !empty($body->html_url) ? $this->validate_github_url($body->html_url) : '';

        $release_data = (object) array(
            'version'      => $remote_version,
            'download_url' => $download_url,
            'changelog'    => isset($body->body) ? (string) $body->body : '',
            'published_at' => isset($body->published_at) ? sanitize_text_field((string) $body->published_at) : '',
            'html_url'     => $html_url,
        );

        set_transient($this->transient_key, $release_data, $this->cache_duration);

        return $release_data;
    }

    /**
     * Hook into the plugin update check.
     *
     * @param object $transient Update plugins transient.
     * @return object
     */
    public function check_for_update($transient)
    {
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_remote_release();
        if (false === $release) {
            return $transient;
        }

        $local_version = $this->get_local_version();

        $obj               = new stdClass();
        $obj->slug         = 'gnn-whois';
        $obj->plugin       = $this->plugin_slug;
        $obj->new_version  = $release->version;
        $obj->url          = $release->html_url;
        $obj->package      = $release->download_url;
        $obj->requires     = $this->requires_wp;
        $obj->tested       = $this->tested_wp;
        $obj->requires_php = $this->requires_php;

        if (version_compare($release->version, $local_version, '>')) {
            $transient->response[$this->plugin_slug] = $obj;
            if (isset($transient->no_update[$this->plugin_slug])) {
                unset($transient->no_update[$this->plugin_slug]);
            }
        } else {
            $obj->new_version                         = $local_version;
            $transient->no_update[$this->plugin_slug] = $obj;
        }

        return $transient;
    }

    /**
     * Provide plugin information for the update details popup.
     *
     * @param false|object|array $result Current result.
     * @param string             $action API action.
     * @param object             $args   API arguments.
     * @return false|object|array
     */
    public function plugin_info($result, $action, $args)
    {
        if ('plugin_information' !== $action || empty($args->slug) || 'gnn-whois' !== $args->slug) {
            return $result;
        }

        $release = $this->get_remote_release();
        if (false === $release) {
            return $result;
        }

        return (object) array(
            'name'          => 'GNN Whois',
            'slug'          => 'gnn-whois',
            'version'       => $release->version,
            'author'        => '<a href="https://github.com/BigDesigner">BigDesigner</a>',
            'homepage'      => 'https://github.com/BigDesigner/gnn-whois',
            'requires'      => $this->requires_wp,
            'tested'        => $this->tested_wp,
            'requires_php'  => $this->requires_php,
            'last_updated'  => $release->published_at,
            'download_link' => $release->download_url,
            'sections'      => array(
                'description' => esc_html__('A lightweight WordPress plugin for performing WHOIS lookups directly from your site via the [gnn_whois] shortcode.', 'gnn-whois'),
                'changelog'   => nl2br(esc_html($release->changelog)),
            ),
        );
    }

    /**
     * Fix directory renaming after update and preserve active state.
     *
     * @param bool  $response   Installation response.
     * @param array $hook_extra Extra arguments passed to hooked filters.
     * @param array $result     Installation result data.
     * @return array
     */
    public function after_install($response, $hook_extra, $result)
    {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_slug) {
            return $result;
        }

        global $wp_filesystem;
        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if (!$wp_filesystem) {
            return $result;
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $was_active = is_plugin_active($this->plugin_slug);

        $plugin_dir = WP_PLUGIN_DIR . '/gnn-whois';

        if (!empty($result['destination']) && $result['destination'] !== $plugin_dir) {
            if ($wp_filesystem->move($result['destination'], $plugin_dir, true)) {
                $result['destination'] = $plugin_dir;
            }
        }

        if ($was_active) {
            activate_plugin($this->plugin_slug);
        }

        delete_transient($this->transient_key);
        return $result;
    }

    /**
     * Manual update check handler.
     */
    public function handle_manual_check()
    {
        if (isset($_GET['gnn_whois_check_update']) && '1' === $_GET['gnn_whois_check_update']) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'gnn_whois_manual_update')) {
                wp_die(esc_html__('Security check failed.', 'gnn-whois'), 403);
            }

            if (!current_user_can('update_plugins')) {
                wp_die(esc_html__('You do not have permission to check for updates.', 'gnn-whois'), 403);
            }

            delete_transient($this->transient_key);
            delete_site_transient('update_plugins');

            wp_safe_redirect(admin_url('update-core.php?force-check=1'));
            exit;
        }
    }

    /**
     * Clear cache on core updates page.
     */
    public function clear_cache()
    {
        delete_transient($this->transient_key);
    }
}

new GNN_Whois_Updater();

