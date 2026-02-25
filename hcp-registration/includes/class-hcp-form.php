<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Front-end registration form (shortcode + AJAX handler).
 */
class HCP_Form {

    public static function init() {
        add_shortcode( 'hcp_registration_form', array( __CLASS__, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_hcp_register', array( __CLASS__, 'handle_submission' ) );
        add_action( 'wp_ajax_nopriv_hcp_register', array( __CLASS__, 'handle_submission' ) );
    }

    /**
     * Enqueue front-end CSS and JS.
     */
    public static function enqueue_assets() {
        wp_enqueue_style(
            'hcp-form-css',
            HCP_REG_PLUGIN_URL . 'assets/css/hcp-form.css',
            array(),
            HCP_REG_VERSION
        );

        wp_enqueue_script(
            'hcp-form-js',
            HCP_REG_PLUGIN_URL . 'assets/js/hcp-form.js',
            array( 'jquery' ),
            HCP_REG_VERSION,
            true
        );

        wp_localize_script( 'hcp-form-js', 'hcpReg', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'hcp_register_nonce' ),
        ) );
    }

    /**
     * Render the registration form via shortcode.
     */
    public static function render_shortcode() {
        ob_start();
        include HCP_REG_PLUGIN_DIR . 'templates/registration-form.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for form submissions.
     */
    public static function handle_submission() {
        check_ajax_referer( 'hcp_register_nonce', 'nonce' );

        $fields = array(
            'first_name'     => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
            'last_name'      => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
            'phone'          => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
            'email'          => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
            'practice_name'  => sanitize_text_field( wp_unslash( $_POST['practice_name'] ?? '' ) ),
            'hcp_type'       => sanitize_text_field( wp_unslash( $_POST['hcp_type'] ?? '' ) ),
            'hcp_reg_number' => sanitize_text_field( wp_unslash( $_POST['hcp_reg_number'] ?? '' ) ),
        );

        $terms = $_POST['terms'] ?? '';

        // Validate required fields.
        foreach ( $fields as $key => $value ) {
            if ( empty( $value ) ) {
                wp_send_json_error( array( 'message' => __( 'All fields are required.', 'hcp-registration' ) ) );
            }
        }

        // Validate terms acceptance.
        if ( empty( $terms ) ) {
            wp_send_json_error( array( 'message' => __( 'You must accept the terms and conditions.', 'hcp-registration' ) ) );
        }

        // Validate email format.
        if ( ! is_email( $fields['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'hcp-registration' ) ) );
        }

        // Check for existing WordPress user.
        if ( email_exists( $fields['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'An account with this email already exists.', 'hcp-registration' ) ) );
        }

        // Check for duplicate pending/approved request.
        if ( HCP_DB::email_exists_in_requests( $fields['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'A registration request with this email is already pending or approved.', 'hcp-registration' ) ) );
        }

        $insert_id = HCP_DB::insert_request( $fields );

        if ( ! $insert_id ) {
            wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again.', 'hcp-registration' ) ) );
        }

        // Notify site admin about new request.
        HCP_Email::notify_admin_new_request( $fields );

        // Sync contact to GoHighLevel CRM.
        HCP_GHL::on_hcp_submission( $fields );

        wp_send_json_success( array(
            'message' => __( 'Your registration request has been submitted successfully. You will receive an email once it is reviewed.', 'hcp-registration' ),
        ) );
    }
}
