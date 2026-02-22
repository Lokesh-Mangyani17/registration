<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Front-end Trade Application form (shortcode + AJAX handler).
 */
class Trade_Form {

    public static function init() {
        add_shortcode( 'trade_application_form', array( __CLASS__, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_trade_register', array( __CLASS__, 'handle_submission' ) );
        add_action( 'wp_ajax_nopriv_trade_register', array( __CLASS__, 'handle_submission' ) );
    }

    /**
     * Enqueue front-end CSS and JS for the trade form.
     */
    public static function enqueue_assets() {
        wp_enqueue_style(
            'hcp-form-css',
            HCP_REG_PLUGIN_URL . 'assets/css/hcp-form.css',
            array(),
            HCP_REG_VERSION
        );

        wp_enqueue_script(
            'trade-form-js',
            HCP_REG_PLUGIN_URL . 'assets/js/trade-form.js',
            array( 'jquery' ),
            HCP_REG_VERSION,
            true
        );

        wp_localize_script( 'trade-form-js', 'tradeReg', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'trade_register_nonce' ),
        ) );
    }

    /**
     * Render the trade application form via shortcode.
     *
     * When the user is logged in, pre-fill personal and HCP fields from their profile.
     */
    public static function render_shortcode() {
        $prefill = array(
            'first_name'     => '',
            'last_name'      => '',
            'phone'          => '',
            'email'          => '',
            'practice_name'  => '',
            'hcp_type'       => '',
            'hcp_reg_number' => '',
        );

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $prefill['first_name']     = $user->first_name;
            $prefill['last_name']      = $user->last_name;
            $prefill['email']          = $user->user_email;
            $prefill['phone']          = get_user_meta( $user->ID, 'hcp_phone', true );
            $prefill['practice_name']  = get_user_meta( $user->ID, 'hcp_practice_name', true );
            $prefill['hcp_type']       = get_user_meta( $user->ID, 'hcp_type', true );
            $prefill['hcp_reg_number'] = get_user_meta( $user->ID, 'hcp_reg_number', true );
        }

        ob_start();
        include HCP_REG_PLUGIN_DIR . 'templates/trade-application-form.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for trade form submissions.
     */
    public static function handle_submission() {
        check_ajax_referer( 'trade_register_nonce', 'nonce' );

        $fields = array(
            'first_name'              => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
            'last_name'               => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
            'phone'                   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
            'email'                   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
            'practice_name'           => sanitize_text_field( wp_unslash( $_POST['practice_name'] ?? '' ) ),
            'hcp_type'                => sanitize_text_field( wp_unslash( $_POST['hcp_type'] ?? '' ) ),
            'hcp_reg_number'          => sanitize_text_field( wp_unslash( $_POST['hcp_reg_number'] ?? '' ) ),
            'company_number'          => sanitize_text_field( wp_unslash( $_POST['company_number'] ?? '' ) ),
            'nz_business_number'      => sanitize_text_field( wp_unslash( $_POST['nz_business_number'] ?? '' ) ),
            'legal_entity_number'     => sanitize_text_field( wp_unslash( $_POST['legal_entity_number'] ?? '' ) ),
            'acts_as_trustee'         => sanitize_text_field( wp_unslash( $_POST['acts_as_trustee'] ?? 'no' ) ),
            'trust_name'              => sanitize_text_field( wp_unslash( $_POST['trust_name'] ?? '' ) ),
            'trading_name'            => sanitize_text_field( wp_unslash( $_POST['trading_name'] ?? '' ) ),
            'physical_address'        => '', // built from sub-fields below
            'postal_same_as_physical' => sanitize_text_field( wp_unslash( $_POST['postal_same_as_physical'] ?? 'yes' ) ),
            'postal_address'          => '', // built from sub-fields below
            'business_email'          => sanitize_email( wp_unslash( $_POST['business_email'] ?? '' ) ),
            'accounts_payable_contact' => sanitize_text_field( wp_unslash( $_POST['accounts_payable_contact'] ?? '' ) ),
            'delivery_contact'        => sanitize_text_field( wp_unslash( $_POST['delivery_contact'] ?? '' ) ),
            'nature_of_business'      => sanitize_text_field( wp_unslash( $_POST['nature_of_business'] ?? '' ) ),
            'date_of_incorporation'   => sanitize_text_field( wp_unslash( $_POST['date_of_incorporation'] ?? '' ) ),
            'ird_number'              => sanitize_text_field( wp_unslash( $_POST['ird_number'] ?? '' ) ),
            'credit_limit_over_5000'  => sanitize_text_field( wp_unslash( $_POST['credit_limit_over_5000'] ?? 'no' ) ),
            'media_upload'            => '', // handled separately below
            'trade_reference'         => sanitize_textarea_field( wp_unslash( $_POST['trade_reference'] ?? '' ) ),
            'signature'               => '', // handled separately below
        );

        // Build physical address JSON from sub-fields.
        $physical = array(
            'street_address' => sanitize_text_field( wp_unslash( $_POST['physical_street_address'] ?? '' ) ),
            'suburb'         => sanitize_text_field( wp_unslash( $_POST['physical_suburb'] ?? '' ) ),
            'city'           => sanitize_text_field( wp_unslash( $_POST['physical_city'] ?? '' ) ),
            'state'          => sanitize_text_field( wp_unslash( $_POST['physical_state'] ?? '' ) ),
            'postal_code'    => sanitize_text_field( wp_unslash( $_POST['physical_postal_code'] ?? '' ) ),
            'country'        => sanitize_text_field( wp_unslash( $_POST['physical_country'] ?? '' ) ),
            'phone'          => sanitize_text_field( wp_unslash( $_POST['physical_phone'] ?? '' ) ),
            'fax'            => sanitize_text_field( wp_unslash( $_POST['physical_fax'] ?? '' ) ),
        );
        $fields['physical_address'] = wp_json_encode( $physical );

        // Build postal address JSON.
        if ( 'yes' === $fields['postal_same_as_physical'] ) {
            $fields['postal_address'] = $fields['physical_address'];
        } else {
            $postal = array(
                'postal_address' => sanitize_text_field( wp_unslash( $_POST['postal_address_line'] ?? '' ) ),
                'suburb'         => sanitize_text_field( wp_unslash( $_POST['postal_suburb'] ?? '' ) ),
                'country'        => sanitize_text_field( wp_unslash( $_POST['postal_country'] ?? '' ) ),
            );
            $fields['postal_address'] = wp_json_encode( $postal );
        }

        $terms = $_POST['terms'] ?? '';

        // Required fields for trade application.
        $required = array(
            'first_name', 'last_name', 'phone', 'email',
            'trading_name',
        );
        foreach ( $required as $key ) {
            if ( empty( $fields[ $key ] ) ) {
                wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'hcp-registration' ) ) );
            }
        }

        // Validate physical address street is filled.
        if ( empty( $physical['street_address'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'hcp-registration' ) ) );
        }

        // Validate terms acceptance.
        if ( empty( $terms ) ) {
            wp_send_json_error( array( 'message' => __( 'You must accept the terms and conditions.', 'hcp-registration' ) ) );
        }

        // Validate email format.
        if ( ! is_email( $fields['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'hcp-registration' ) ) );
        }

        // Validate business email format if provided.
        if ( ! empty( $fields['business_email'] ) && ! is_email( $fields['business_email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid business email address.', 'hcp-registration' ) ) );
        }

        // Check for duplicate pending/approved trade application.
        if ( HCP_DB::trade_email_exists_in_requests( $fields['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'A trade application with this email is already pending or approved.', 'hcp-registration' ) ) );
        }

        // Handle media upload.
        if ( ! empty( $_FILES['media_upload'] ) && ! empty( $_FILES['media_upload']['name'] ) ) {
            $upload = self::handle_file_upload( 'media_upload' );
            if ( is_wp_error( $upload ) ) {
                wp_send_json_error( array( 'message' => $upload->get_error_message() ) );
            }
            $fields['media_upload'] = $upload;
        }

        // Handle signature (base64 data URL from canvas).
        if ( ! empty( $_POST['signature'] ) ) {
            $fields['signature'] = sanitize_text_field( wp_unslash( $_POST['signature'] ) );
        }

        $insert_id = HCP_DB::insert_trade_request( $fields );

        if ( ! $insert_id ) {
            wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again.', 'hcp-registration' ) ) );
        }

        // Notify site admin about new trade request.
        HCP_Email::notify_admin_new_trade_request( $fields );

        wp_send_json_success( array(
            'message' => __( 'Your trade application has been submitted successfully. You will receive an email once it is reviewed.', 'hcp-registration' ),
        ) );
    }

    /**
     * Handle a file upload and return the URL.
     *
     * @param string $field_name The $_FILES key.
     * @return string|WP_Error File URL or error.
     */
    private static function handle_file_upload( $field_name ) {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $file = $_FILES[ $field_name ];

        // Validate file type.
        $allowed = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx' );
        $ext     = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $ext, $allowed, true ) ) {
            return new WP_Error( 'invalid_type', __( 'Invalid file type. Allowed: jpg, png, gif, pdf, doc, docx.', 'hcp-registration' ) );
        }

        // Max 5 MB.
        if ( $file['size'] > 5 * 1024 * 1024 ) {
            return new WP_Error( 'too_large', __( 'File is too large. Maximum size is 5 MB.', 'hcp-registration' ) );
        }

        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'] );
        }

        return esc_url_raw( $upload['url'] );
    }
}
