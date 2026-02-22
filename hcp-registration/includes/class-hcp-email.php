<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Email notifications for HCP Registration.
 */
class HCP_Email {

    /**
     * Send approval email with a password-setup link.
     *
     * Uses the WordPress built-in password-reset mechanism so the user
     * can click a secure link and choose their own password.
     *
     * @param int    $user_id WP user ID.
     * @param object $request The registration row from the DB.
     */
    public static function send_approval_email( $user_id, $request ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        // Generate a password-reset key (used as a set-password link).
        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            return;
        }

        $reset_url = network_site_url(
            "wp-login.php?action=rp&key={$key}&login=" . rawurlencode( $user->user_login ),
            'login'
        );

        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] Your HCP Registration Has Been Approved', 'hcp-registration' ),
            $site_name
        );

        ob_start();
        include HCP_REG_PLUGIN_DIR . 'templates/email-approved.php';
        $message = ob_get_clean();

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $request->email, $subject, $message, $headers );
    }

    /**
     * Send a rejection notification email to the applicant.
     *
     * @param object $request The registration row.
     */
    public static function send_rejection_email( $request ) {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] Your HCP Registration Request', 'hcp-registration' ),
            $site_name
        );

        $message  = sprintf(
            /* translators: %s: first name */
            __( 'Dear %s,', 'hcp-registration' ),
            esc_html( $request->first_name )
        ) . "\r\n\r\n";
        $message .= __( 'Thank you for your interest. Unfortunately, your Healthcare Professional registration request has not been approved at this time.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= __( 'If you believe this is an error, please contact us.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= sprintf(
            /* translators: %s: site name */
            __( 'Regards, %s', 'hcp-registration' ),
            $site_name
        );

        wp_mail( $request->email, $subject, $message );
    }

    /**
     * Notify site admin about a new registration request.
     *
     * @param array $data Submitted form data.
     */
    public static function notify_admin_new_request( $data ) {
        $admin_email = get_option( 'admin_email' );
        $site_name   = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] New HCP Registration Request', 'hcp-registration' ),
            $site_name
        );

        $message  = __( 'A new Healthcare Professional registration request has been submitted.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= sprintf( __( 'Name: %s %s', 'hcp-registration' ), $data['first_name'], $data['last_name'] ) . "\r\n";
        $message .= sprintf( __( 'Email: %s', 'hcp-registration' ), $data['email'] ) . "\r\n";
        $message .= sprintf( __( 'HCP Type: %s', 'hcp-registration' ), $data['hcp_type'] ) . "\r\n\r\n";
        $message .= __( 'Please review this request in the admin dashboard.', 'hcp-registration' ) . "\r\n";
        $message .= admin_url( 'admin.php?page=hcp-registrations' );

        wp_mail( $admin_email, $subject, $message );
    }

    /* ======================================================================
       Trade Application Emails
       ====================================================================== */

    /**
     * Send trade approval email.
     *
     * If the user is new (password needs to be set), include the password-reset link.
     * If the user already had an account (HCP), just notify them.
     *
     * @param int    $user_id  WP user ID.
     * @param object $request  The trade application row from the DB.
     * @param bool   $is_existing Whether the user already had an account.
     */
    public static function send_trade_approval_email( $user_id, $request, $is_existing = false ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] Your Trade Application Has Been Approved', 'hcp-registration' ),
            $site_name
        );

        if ( $is_existing ) {
            // Existing user – no password reset needed.
            $login_url = wp_login_url();
            ob_start();
            include HCP_REG_PLUGIN_DIR . 'templates/email-trade-approved-existing.php';
            $message = ob_get_clean();
        } else {
            // New user – include password-setup link.
            $key = get_password_reset_key( $user );
            if ( is_wp_error( $key ) ) {
                return;
            }
            $reset_url = network_site_url(
                "wp-login.php?action=rp&key={$key}&login=" . rawurlencode( $user->user_login ),
                'login'
            );
            ob_start();
            include HCP_REG_PLUGIN_DIR . 'templates/email-trade-approved-new.php';
            $message = ob_get_clean();
        }

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $request->email, $subject, $message, $headers );
    }

    /**
     * Send trade rejection email.
     *
     * @param object $request The trade application row.
     */
    public static function send_trade_rejection_email( $request ) {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] Your Trade Application', 'hcp-registration' ),
            $site_name
        );

        $message  = sprintf(
            /* translators: %s: first name */
            __( 'Dear %s,', 'hcp-registration' ),
            esc_html( $request->first_name )
        ) . "\r\n\r\n";
        $message .= __( 'Thank you for your interest. Unfortunately, your Trade Application has not been approved at this time.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= __( 'If you believe this is an error, please contact us.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= sprintf(
            /* translators: %s: site name */
            __( 'Regards, %s', 'hcp-registration' ),
            $site_name
        );

        wp_mail( $request->email, $subject, $message );
    }

    /**
     * Notify site admin about a new trade application.
     *
     * @param array $data Submitted form data.
     */
    public static function notify_admin_new_trade_request( $data ) {
        $admin_email = get_option( 'admin_email' );
        $site_name   = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] New Trade Application', 'hcp-registration' ),
            $site_name
        );

        $message  = __( 'A new Trade Application has been submitted.', 'hcp-registration' ) . "\r\n\r\n";
        $message .= sprintf( __( 'Name: %s %s', 'hcp-registration' ), $data['first_name'], $data['last_name'] ) . "\r\n";
        $message .= sprintf( __( 'Email: %s', 'hcp-registration' ), $data['email'] ) . "\r\n";
        $message .= sprintf( __( 'Trading Name: %s', 'hcp-registration' ), $data['trading_name'] ) . "\r\n\r\n";
        $message .= __( 'Please review this application in the admin dashboard.', 'hcp-registration' ) . "\r\n";
        $message .= admin_url( 'admin.php?page=hcp-registrations&tab=trade' );

        wp_mail( $admin_email, $subject, $message );
    }
}
