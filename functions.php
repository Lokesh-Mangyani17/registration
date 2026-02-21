<?php
/**
 * Theme functions for New AI Site.
 *
 * Displays admin notices for HCP Registration approve/reject actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
