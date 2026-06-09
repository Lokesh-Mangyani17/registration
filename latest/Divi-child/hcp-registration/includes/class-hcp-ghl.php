<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GoHighLevel (GHL) CRM integration for HCP Registration.
 *
 * Creates or updates contacts in GHL at each registration stage:
 * - HCP / Trade form submission (create or update contact)
 * - HCP / Trade approval or rejection (update existing contact)
 *
 * The API key must be stored in the WordPress option 'hcp_ghl_api_key'
 * or defined as the constant HCP_GHL_API_KEY (e.g. in wp-config.php).
 */
class HCP_GHL {

    /**
     * GHL API v1 base URL.
     */
    const API_BASE = 'https://rest.gohighlevel.com/v1';
    const FIELD_HCP_APPROVED = 'hcp_approved';
    const FIELD_HCP_TRADE_APPROVED = 'hcp_trade_approved';

    /**
     * Return the configured API key.
     *
     * @return string
     */
    private static function api_key() {
        if ( defined( 'HCP_GHL_API_KEY' ) && HCP_GHL_API_KEY ) {
            return HCP_GHL_API_KEY;
        }
        return carbon_get_theme_option( 'hcp_ghl_api_key' );
    }

    /**
     * Check whether the integration is configured.
     *
     * @return bool
     */
    public static function is_configured() {
        return (bool) self::api_key();
    }

    /* ==================================================================
       Contact operations
       ================================================================== */

    /**
     * Create or update a GHL contact when an HCP request is submitted.
     *
     * @param array $fields Sanitized form data.
     */
    public static function on_hcp_submission( $fields ) {
        if ( ! self::is_configured() ) {
            return;
        }

        $contact_data = array(
            'email'       => self::normalize_email( $fields['email'] ),
            'firstName'   => $fields['first_name'],
            'lastName'    => $fields['last_name'],
            'phone'       => $fields['phone'],
            'companyName' => $fields['pharmacy_name'],
            'tags'        => array( 'HCP Request', 'HCP Pending' ),
            'customField' => array(
                array( 'key' => 'practice_clinic_name',    'field_value' => $fields['pharmacy_name'] ),
                array( 'key' => 'hcp_registration_number', 'field_value' => $fields['hcp_reg_number'] ),
                array( 'key' => 'role_of_contact',         'field_value' => $fields['hcp_type'] ),
                self::custom_field_value( self::FIELD_HCP_APPROVED, '' ),
            ),
        );

        self::create_or_update_contact( $contact_data );
    }

    /**
     * Update a GHL contact when an HCP request is approved.
     *
     * @param object $request The HCP registration row from the DB.
     */
    public static function on_hcp_approved( $request ) {
        if ( ! self::is_configured() ) {
            return;
        }

        $email = self::normalize_email( $request->email );
        $contact_id = self::lookup_contact_id( $email );
        if ( ! $contact_id ) {
            self::log( 'GHL HCP approve skipped: contact not found for email ' . $email );
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'HCP Request', 'HCP Approved' ),
            'customField' => array(
                self::custom_field_value( self::FIELD_HCP_APPROVED, 'Approved' ),
            ),
        ) );
    }

    /**
     * Update a GHL contact when an HCP request is rejected.
     *
     * @param object $request The HCP registration row from the DB.
     */
    public static function on_hcp_rejected( $request ) {
        if ( ! self::is_configured() ) {
            return;
        }

        $email = self::normalize_email( $request->email );
        $contact_id = self::lookup_contact_id( $email );
        if ( ! $contact_id ) {
            self::log( 'GHL HCP reject skipped: contact not found for email ' . $email );
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'HCP Request', 'HCP Rejected' ),
            'customField' => array(
                self::custom_field_value( self::FIELD_HCP_APPROVED, 'Declined' ),
            ),
        ) );
    }

    /**
     * Create or update a GHL contact when a Trade application is submitted.
     *
     * @param array $fields Sanitized form data.
     */
    public static function on_trade_submission( $fields ) {
        if ( ! self::is_configured() ) {
            return;
        }

        // Decode physical address JSON to get individual address fields.
        $physical = is_array( $fields['physical_address'] )
            ? $fields['physical_address']
            : (array) json_decode( $fields['physical_address'], true );

        $contact_data = array(
            'email'       => self::normalize_email( $fields['email'] ),
            'firstName'   => $fields['first_name'],
            'lastName'    => $fields['last_name'],
            'phone'       => $fields['phone'],
            'companyName' => $fields['trading_name'],
            'address1'    => $physical['address_1'] ?? '',
            'city'        => $physical['city'] ?? '',
            'state'       => $physical['state'] ?? '',
            'postalCode'  => $physical['postcode'] ?? '',
            'country'     => $physical['country'] ?? 'NZ',
            'tags'        => array( 'Trade Request', 'Trade Pending' ),
            'customField' => array(
                array( 'key' => 'practice_clinic_name',     'field_value' => $fields['pharmacy_name'] ),
                array( 'key' => 'role_of_contact',          'field_value' => $fields['hcp_type'] ),
                array( 'key' => 'hcp_registration_number',  'field_value' => $fields['hcp_reg_number'] ),
                array( 'key' => 'trading_name',             'field_value' => $fields['trading_name'] ),
                array( 'key' => 'company_number',           'field_value' => $fields['company_number'] ),
                array( 'key' => 'does_applicant_act_as_trustee', 'field_value' => $fields['acts_as_trustee'] ),
                array( 'key' => 'trust_name',               'field_value' => $fields['trust_name'] ?? '' ),
                array( 'key' => 'nature_of_business',       'field_value' => $fields['nature_of_business'] ),
                array( 'key' => 'date_of_incorporated',     'field_value' => $fields['date_of_incorporation'] ),
                array( 'key' => 'ird_number',               'field_value' => $fields['ird_number'] ),
                array( 'key' => 'business_common_email_for_access', 'field_value' => $fields['business_email'] ),
                array( 'key' => 'account_payable_full_name',        'field_value' => $fields['accounts_payable_contact'] ),
                array( 'key' => 'delivery_contact_name',            'field_value' => $fields['delivery_contact'] ),
                array( 'key' => 'do_you_require_a_credit_limit_over_5000', 'field_value' => $fields['credit_limit_over_5000'] ),
                array( 'key' => 'physical_address_line_2',          'field_value' => $physical['address_2'] ?? '' ),
                array( 'key' => 'fax',                              'field_value' => $physical['fax'] ?? '' ),
                array( 'key' => 'same_as_physical_address',         'field_value' => $fields['postal_same_as_physical'] ),
                array( 'key' => 'postal_address',                   'field_value' => self::format_address( $fields['postal_address'] ) ),
                array( 'key' => 'please_upload_a_copy_of_any_required_licenses_including_those_showing_that_you_are_authorised_to_deal_sell_and_or_prescribe_prescription_medicines_and_or_controlled_drugs_as_per_the_medicines_act_and_regulation_and_misuse_of_drug_act_and_regulation', 'field_value' => $fields['media_upload'] ),
                array( 'key' => 'signature',                        'field_value' => self::save_signature_image( $fields['signature'] ) ),
                self::custom_field_value( self::FIELD_HCP_TRADE_APPROVED, '' ),
            ),
        );

        self::create_or_update_contact( $contact_data );
    }

    /**
     * Update a GHL contact when a Trade application is approved.
     *
     * @param object $request The trade application row from the DB.
     */
    public static function on_trade_approved( $request ) {
        if ( ! self::is_configured() ) {
            return;
        }

        $email = self::normalize_email( $request->email );
        $contact_id = self::lookup_contact_id( $email );
        if ( ! $contact_id ) {
            self::log( 'GHL Trade approve skipped: contact not found for email ' . $email );
            return;
        }

        $physical = (array) json_decode( $request->physical_address, true );

        self::update_contact( $contact_id, array(
            'firstName'   => $request->first_name,
            'lastName'    => $request->last_name,
            'phone'       => $request->phone,
            'companyName' => $request->trading_name,
            'address1'    => $physical['address_1'] ?? '',
            'city'        => $physical['city'] ?? '',
            'state'       => $physical['state'] ?? '',
            'postalCode'  => $physical['postcode'] ?? '',
            'country'     => $physical['country'] ?? 'NZ',
            'tags'        => array( 'Trade Request', 'Trade Approved' ),
            'customField' => array(
                array( 'key' => 'practice_clinic_name',     'field_value' => $request->practice_name ),
                array( 'key' => 'role_of_contact',          'field_value' => $request->hcp_type ),
                array( 'key' => 'hcp_registration_number',  'field_value' => $request->hcp_reg_number ),
                array( 'key' => 'trading_name',             'field_value' => $request->trading_name ),
                array( 'key' => 'company_number',           'field_value' => $request->company_number ),
                array( 'key' => 'does_applicant_act_as_trustee', 'field_value' => $request->acts_as_trustee ),
                array( 'key' => 'trust_name',               'field_value' => $request->trust_name ?? '' ),
                array( 'key' => 'nature_of_business',       'field_value' => $request->nature_of_business ),
                array( 'key' => 'date_of_incorporated',     'field_value' => $request->date_of_incorporation ),
                array( 'key' => 'ird_number',               'field_value' => $request->ird_number ),
                array( 'key' => 'business_common_email_for_access',        'field_value' => $request->business_email ),
                array( 'key' => 'account_payable_full_name',               'field_value' => $request->accounts_payable_contact ),
                array( 'key' => 'delivery_contact_name',                   'field_value' => $request->delivery_contact ),
                array( 'key' => 'do_you_require_a_credit_limit_over_5000', 'field_value' => $request->credit_limit_over_5000 ),
                array( 'key' => 'physical_address_line_2',                 'field_value' => $physical['address_2'] ?? '' ),
                array( 'key' => 'fax',                                     'field_value' => $physical['fax'] ?? '' ),
                array( 'key' => 'same_as_physical_address',                'field_value' => $request->postal_same_as_physical ),
                array( 'key' => 'postal_address',                          'field_value' => self::format_address( $request->postal_address ) ),
                array( 'key' => 'please_upload_a_copy_of_any_required_licenses_including_those_showing_that_you_are_authorised_to_deal_sell_and_or_prescribe_prescription_medicines_and_or_controlled_drugs_as_per_the_medicines_act_and_regulation_and_misuse_of_drug_act_and_regulation', 'field_value' => $request->media_upload ),
                array( 'key' => 'signature',                               'field_value' => self::save_signature_image( $request->signature ) ),
                self::custom_field_value( self::FIELD_HCP_TRADE_APPROVED, 'Yes' ),
            ),
        ) );
    }

    /**
     * Update a GHL contact when a Trade application is rejected.
     *
     * @param object $request The trade application row from the DB.
     */
    public static function on_trade_rejected( $request ) {
        if ( ! self::is_configured() ) {
            return;
        }

        $email = self::normalize_email( $request->email );
        $contact_id = self::lookup_contact_id( $email );
        if ( ! $contact_id ) {
            self::log( 'GHL Trade reject skipped: contact not found for email ' . $email );
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'Trade Request', 'Trade Rejected' ),
            'customField' => array(
                self::custom_field_value( self::FIELD_HCP_TRADE_APPROVED, 'No' ),
            ),
        ) );
    }

    /**
     * Format a JSON-encoded address string into a readable single-line address.
     *
     * @param string $json JSON string or already-decoded array.
     * @return string
     */
    private static function format_address( $json ) {
        $addr = is_array( $json ) ? $json : json_decode( $json, true );
        if ( ! is_array( $addr ) ) {
            return (string) $json;
        }
        $parts = array_filter( array(
            $addr['address_1'] ?? '',
            $addr['address_2'] ?? '',
            $addr['city']      ?? '',
            $addr['state']     ?? '',
            $addr['postcode']  ?? '',
            $addr['country']   ?? '',
        ) );
        return implode( ', ', $parts );
    }

    /**
     * Save a base64 signature data-URL as a PNG file and return its URL.
     * Falls back to returning an empty string if saving fails.
     *
     * @param string $data_url Base64 data-URL from the signature canvas.
     * @return string File URL or empty string.
     */
    private static function save_signature_image( $data_url ) {
        if ( empty( $data_url ) ) {
            return '';
        }
        // Already a URL (previously saved).
        if ( filter_var( $data_url, FILTER_VALIDATE_URL ) ) {
            return $data_url;
        }
        if ( ! preg_match( '/^data:image\/png;base64,(.+)$/i', $data_url, $matches ) ) {
            return '';
        }
        $image_data = base64_decode( $matches[1] );
        if ( ! $image_data ) {
            return '';
        }
        if ( ! function_exists( 'wp_upload_dir' ) ) {
            return '';
        }
        $upload     = wp_upload_dir();
        $filename   = 'signature_' . uniqid() . '.png';
        $file_path  = trailingslashit( $upload['path'] ) . $filename;
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        if ( false === file_put_contents( $file_path, $image_data ) ) {
            return '';
        }
        return trailingslashit( $upload['url'] ) . $filename;
    }

    /**
     * Build a custom field payload in one place for status fields.
     *
     * @param string $key   GHL custom field key.
     * @param string $value Value to set (or blank).
     * @return array
     */
    private static function custom_field_value( $key, $value ) {
        return array(
            'key'         => $key,
            'field_value' => $value,
        );
    }

    /**
     * Normalize emails before syncing with GHL.
     *
     * @param string $email Email address.
     * @return string
     */
    private static function normalize_email( $email ) {
        return strtolower( trim( sanitize_email( (string) $email ) ) );
    }

    /* ==================================================================
       Low-level API helpers
       ================================================================== */

    /**
     * Look up an existing GHL contact by email.
     *
     * @param string $email Email address.
     * @return string|null Contact ID or null.
     */
    private static function lookup_contact_id( $email ) {
        $email = self::normalize_email( $email );
        if ( ! $email ) {
            return null;
        }

        $response = wp_remote_get(
            add_query_arg( 'email', $email, self::API_BASE . '/contacts/lookup' ),
            array(
                'headers' => self::auth_headers(),
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            self::log( 'GHL lookup error: ' . $response->get_error_message() );
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $body['contacts'][0]['id'] ) ) {
            return $body['contacts'][0]['id'];
        }

        return null;
    }

    /**
     * Create or update a contact in GHL.
     *
     * The GHL v1 POST /contacts/ endpoint performs an upsert: if a contact
     * with the same email already exists it will be updated.
     *
     * @param array $data Contact data.
     * @return string|null The GHL contact ID, or null on failure.
     */
    private static function create_or_update_contact( $data ) {
        $email = self::normalize_email( $data['email'] ?? '' );
        if ( ! $email ) {
            self::log( 'GHL create/update skipped: missing email.' );
            return null;
        }

        $data['email'] = $email;

        // Force a single-contact flow by using email as the unique identifier.
        $contact_id = self::lookup_contact_id( $email );
        if ( $contact_id ) {
            $updated = self::update_contact( $contact_id, $data );
            return $updated ? $contact_id : null;
        }

        $response = wp_remote_post(
            self::API_BASE . '/contacts/',
            array(
                'headers' => array_merge( self::auth_headers(), array(
                    'Content-Type' => 'application/json',
                ) ),
                'body'    => wp_json_encode( $data ),
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            self::log( 'GHL create/update error: ' . $response->get_error_message() );
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $body['contact']['id'] ) ) {
            return $body['contact']['id'];
        }

        self::log( 'GHL create/update unexpected response: ' . wp_remote_retrieve_body( $response ) );
        return null;
    }

    /**
     * Update an existing GHL contact by ID.
     *
     * @param string $contact_id GHL contact ID.
     * @param array  $data       Fields to update.
     * @return bool True on success.
     */
    private static function update_contact( $contact_id, $data ) {
        $response = wp_remote_request(
            self::API_BASE . '/contacts/' . $contact_id,
            array(
                'method'  => 'PUT',
                'headers' => array_merge( self::auth_headers(), array(
                    'Content-Type' => 'application/json',
                ) ),
                'body'    => wp_json_encode( $data ),
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            self::log( 'GHL update error: ' . $response->get_error_message() );
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            self::log( 'GHL update HTTP ' . $code . ': ' . wp_remote_retrieve_body( $response ) );
            return false;
        }

        return true;
    }

    /**
     * Build the authorization headers for GHL API requests.
     *
     * @return array
     */
    private static function auth_headers() {
        return array(
            'Authorization' => 'Bearer ' . self::api_key(),
        );
    }

    /**
     * Log a message via the WordPress debug log.
     *
     * @param string $message Log message.
     */
    private static function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[HCP-GHL] ' . $message );
        }
    }
}