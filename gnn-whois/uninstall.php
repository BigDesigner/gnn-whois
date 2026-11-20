<?php
/**
 * Uninstall handler for GNN Whois.
 *
 * Removes cached WHOIS lookup transients, rate-limit transients,
 * and GitHub updater transients when the plugin is deleted.
 *
 * @package GNN_Whois
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

delete_transient('gnn_whois_github_update_check');

$like_transient = $wpdb->esc_like('_transient_gnn_whois_') . '%';
$like_timeout   = $wpdb->esc_like('_transient_timeout_gnn_whois_') . '%';

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $like_transient,
        $like_timeout
    )
);
