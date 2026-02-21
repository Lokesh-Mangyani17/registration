<?php
/**
 * Theme functions for New AI Site.
 *
 * Loads the bundled HCP Registration plugin and displays admin notices.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the HCP Registration plugin bundled with this theme.
define( 'HCP_REG_PLUGIN_URL', get_template_directory_uri() . '/hcp-registration/' );
require_once get_template_directory() . '/hcp-registration/hcp-registration.php';

/**
 * Ensure the HCP Registration database table and role exist.
 *
 * The plugin's register_activation_hook does not fire when loaded from a theme,
 * so we run the setup on admin_init with a version check.
 */
function newaisite_hcp_setup() {
    if ( get_option( 'hcp_reg_version' ) !== HCP_REG_VERSION ) {
        HCP_DB::create_table();
        HCP_DB::register_role();
        update_option( 'hcp_reg_version', HCP_REG_VERSION );
    }
}
add_action( 'admin_init', 'newaisite_hcp_setup' );

/**
 * Show admin notices after HCP registration approve/reject.
 */
function newaisite_hcp_admin_notices() {
    if ( ! isset( $_GET['page'] ) || 'hcp-registrations' !== $_GET['page'] ) {
        return;
    }

    if ( ! isset( $_GET['message'] ) ) {
        return;
    }

    $msg = sanitize_text_field( wp_unslash( $_GET['message'] ) );

    if ( 'approved' === $msg ) {
        echo '<div class="notice notice-success is-dismissible hcp-admin-notice"><p>';
        esc_html_e( 'Registration approved. The user account has been created and an email with login instructions has been sent.', 'hcp-registration' );
        echo '</p></div>';
    } elseif ( 'rejected' === $msg ) {
        echo '<div class="notice notice-warning is-dismissible hcp-admin-notice"><p>';
        esc_html_e( 'Registration request has been rejected. The applicant has been notified.', 'hcp-registration' );
        echo '</p></div>';
    }
}
add_action( 'admin_notices', 'newaisite_hcp_admin_notices' );
