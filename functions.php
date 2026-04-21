<?php
/**
 * Theme functions for NUBU.
 *
 * Loads HCP Registration and shows helpful admin notices.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set up HCP Registration paths before loading plugin files.
define( 'HCP_REG_DIR', get_template_directory() . '/hcp-registration/' );
define( 'HCP_REG_URL', get_template_directory_uri() . '/hcp-registration/' );
require_once HCP_REG_DIR . 'hcp-registration.php';

/**
 * Make sure HCP Registration tables and roles are ready.
 *
 * Runs on admin_init with a version check so that tables are created
 * on theme activation and kept up to date across upgrades.
 */
function nubu_hcp_setup() {
    if ( get_option( 'hcp_reg_version' ) !== HCP_REG_VERSION ) {
        HCP_DB::create_table();
        HCP_DB::register_role();
        HCP_DB::create_trade_table();
        HCP_DB::register_trade_role();
        update_option( 'hcp_reg_version', HCP_REG_VERSION );
    }
}
add_action( 'admin_init', 'nubu_hcp_setup' );

/**
 * Show admin notices after HCP registration actions.
 */
function nubu_hcp_admin_notices() {
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
add_action( 'admin_notices', 'nubu_hcp_admin_notices' );
