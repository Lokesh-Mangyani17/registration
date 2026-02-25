<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GoHighLevel (GHL) CRM integration for HCP Registration plugin.
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

    /**
     * Return the configured API key.
     *
     * @return string
     */
    private static function api_key() {
        if ( defined( 'HCP_GHL_API_KEY' ) && HCP_GHL_API_KEY ) {
            return HCP_GHL_API_KEY;
        }
        return get_option( 'hcp_ghl_api_key', '' );
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
       Settings (admin UI for the API key)
       ================================================================== */

    /**
     * Register the settings field.
     */
    public static function register_settings() {
        register_setting( 'hcp_ghl_settings', 'hcp_ghl_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );
    }

    /**
     * Render a minimal settings section inside the existing admin page.
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'GoHighLevel Settings', 'hcp-registration' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'hcp_ghl_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hcp_ghl_api_key"><?php esc_html_e( 'GHL API Key', 'hcp-registration' ); ?></label>
                        </th>
                        <td>
                            <?php if ( defined( 'HCP_GHL_API_KEY' ) && HCP_GHL_API_KEY ) : ?>
                                <p class="description">
                                    <?php esc_html_e( 'The API key is defined via the HCP_GHL_API_KEY constant. Remove the constant to use this field instead.', 'hcp-registration' ); ?>
                                </p>
                            <?php else : ?>
                                <input type="text" id="hcp_ghl_api_key" name="hcp_ghl_api_key"
                                       value="<?php echo esc_attr( get_option( 'hcp_ghl_api_key', '' ) ); ?>"
                                       class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e( 'Enter your GoHighLevel location-level API key.', 'hcp-registration' ); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
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
            'email'       => $fields['email'],
            'firstName'   => $fields['first_name'],
            'lastName'    => $fields['last_name'],
            'phone'       => $fields['phone'],
            'companyName' => $fields['practice_name'],
            'tags'        => array( 'HCP Request', 'HCP Pending' ),
            'customField' => array(
                array( 'key' => 'practice_clinic_name', 'field_value' => $fields['practice_name'] ),
                array( 'key' => 'hcp_registration_number', 'field_value' => $fields['hcp_reg_number'] ),
                array( 'key' => 'role_of_contact', 'field_value' => $fields['hcp_type'] ),
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

        $contact_id = self::lookup_contact_id( $request->email );
        if ( ! $contact_id ) {
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'HCP Request', 'HCP Approved' ),
            'customField' => array(
                array( 'key' => 'hcp_approved', 'field_value' => 'Approved' ),
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

        $contact_id = self::lookup_contact_id( $request->email );
        if ( ! $contact_id ) {
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'HCP Request', 'HCP Rejected' ),
            'customField' => array(
                array( 'key' => 'hcp_approved', 'field_value' => 'Declined' ),
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

        $contact_data = array(
            'email'       => $fields['email'],
            'firstName'   => $fields['first_name'],
            'lastName'    => $fields['last_name'],
            'phone'       => $fields['phone'],
            'companyName' => $fields['trading_name'],
            'tags'        => array( 'Trade Request', 'Trade Pending' ),
            'customField' => array(
                array( 'key' => 'role_of_contact', 'field_value' => $fields['hcp_type'] ),
                array( 'key' => 'hcp_registration_number', 'field_value' => $fields['hcp_reg_number'] ),
                array( 'key' => 'trading_name', 'field_value' => $fields['trading_name'] ),
                array( 'key' => 'nature_of_business', 'field_value' => $fields['nature_of_business'] ),
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

        $contact_id = self::lookup_contact_id( $request->email );
        if ( ! $contact_id ) {
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'Trade Request', 'Trade Approved' ),
            'customField' => array(
                array( 'key' => 'hcp_trade_approved', 'field_value' => 'Yes' ),
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

        $contact_id = self::lookup_contact_id( $request->email );
        if ( ! $contact_id ) {
            return;
        }

        self::update_contact( $contact_id, array(
            'tags'        => array( 'Trade Request', 'Trade Rejected' ),
            'customField' => array(
                array( 'key' => 'hcp_trade_approved', 'field_value' => 'No' ),
            ),
        ) );
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
        $response = wp_remote_get(
            add_query_arg( 'email', rawurlencode( $email ), self::API_BASE . '/contacts/lookup' ),
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
