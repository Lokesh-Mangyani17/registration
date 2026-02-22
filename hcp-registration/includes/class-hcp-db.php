<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database helpers for the HCP Registration plugin.
 */
class HCP_DB {

    /**
     * Return the custom table name (with WP prefix).
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'hcp_registrations';
    }

    /**
     * Create the pending-registrations table.
     */
    public static function create_table() {
        global $wpdb;

        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(30) NOT NULL,
            email varchar(200) NOT NULL,
            practice_name varchar(200) NOT NULL,
            hcp_type varchar(100) NOT NULL,
            hcp_reg_number varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_at datetime DEFAULT NULL,
            reviewed_by bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Register the Healthcare Professional role.
     */
    public static function register_role() {
        if ( ! get_role( 'healthcare_professional' ) ) {
            add_role(
                'healthcare_professional',
                __( 'Healthcare Professional', 'hcp-registration' ),
                array(
                    'read' => true,
                )
            );
        }
    }

    /**
     * Insert a new registration request.
     *
     * @param array $data Sanitized form data.
     * @return int|false Insert ID or false on failure.
     */
    public static function insert_request( $data ) {
        global $wpdb;

        $result = $wpdb->insert(
            self::table_name(),
            array(
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'phone'          => $data['phone'],
                'email'          => $data['email'],
                'practice_name'  => $data['practice_name'],
                'hcp_type'       => $data['hcp_type'],
                'hcp_reg_number' => $data['hcp_reg_number'],
                'status'         => 'pending',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get registrations filtered by status.
     *
     * @param string $status pending|approved|rejected or empty for all.
     * @return array
     */
    public static function get_requests( $status = '' ) {
        global $wpdb;
        $table = self::table_name();

        if ( $status ) {
            return $wpdb->get_results(
                $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY submitted_at DESC", $status )
            );
        }

        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY submitted_at DESC" );
    }

    /**
     * Get a single request by ID.
     *
     * @param int $id Row ID.
     * @return object|null
     */
    public static function get_request( $id ) {
        global $wpdb;
        $table = self::table_name();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
        );
    }

    /**
     * Update the status of a request.
     *
     * @param int    $id     Row ID.
     * @param string $status New status.
     * @param int    $admin_id Optional admin user ID who reviewed.
     * @return bool
     */
    public static function update_status( $id, $status, $admin_id = 0 ) {
        global $wpdb;

        $data   = array(
            'status'      => $status,
            'reviewed_at' => current_time( 'mysql' ),
        );
        $format = array( '%s', '%s' );

        if ( $admin_id ) {
            $data['reviewed_by'] = $admin_id;
            $format[]            = '%d';
        }

        return (bool) $wpdb->update(
            self::table_name(),
            $data,
            array( 'id' => $id ),
            $format,
            array( '%d' )
        );
    }

    /**
     * Check whether an email already has a pending or approved request.
     *
     * @param string $email Email address.
     * @return bool
     */
    public static function email_exists_in_requests( $email ) {
        global $wpdb;
        $table = self::table_name();

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE email = %s AND status IN ('pending','approved')",
                $email
            )
        );

        return $count > 0;
    }

    /* ======================================================================
       Trade Applications
       ====================================================================== */

    /**
     * Return the trade applications table name (with WP prefix).
     */
    public static function trade_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'trade_applications';
    }

    /**
     * Create the trade-applications table.
     */
    public static function create_trade_table() {
        global $wpdb;

        $table   = self::trade_table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(30) NOT NULL,
            email varchar(200) NOT NULL,
            practice_name varchar(200) DEFAULT '',
            hcp_type varchar(100) DEFAULT '',
            hcp_reg_number varchar(100) DEFAULT '',
            company_number varchar(100) DEFAULT '',
            nz_business_number varchar(100) DEFAULT '',
            legal_entity_number varchar(100) DEFAULT '',
            acts_as_trustee varchar(10) DEFAULT 'no',
            trust_name varchar(200) DEFAULT '',
            trading_name varchar(200) DEFAULT '',
            physical_address text,
            postal_same_as_physical varchar(10) DEFAULT 'no',
            postal_address text,
            business_email varchar(200) DEFAULT '',
            accounts_payable_contact varchar(200) DEFAULT '',
            delivery_contact varchar(200) DEFAULT '',
            nature_of_business varchar(200) DEFAULT '',
            date_of_incorporation varchar(50) DEFAULT '',
            ird_number varchar(50) DEFAULT '',
            credit_limit_over_5000 varchar(10) DEFAULT 'no',
            media_upload varchar(500) DEFAULT '',
            trade_reference text,
            signature text,
            status varchar(20) NOT NULL DEFAULT 'pending',
            submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_at datetime DEFAULT NULL,
            reviewed_by bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY (id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Register the Trade Account role.
     */
    public static function register_trade_role() {
        if ( ! get_role( 'trade_account' ) ) {
            add_role(
                'trade_account',
                __( 'Trade Account', 'hcp-registration' ),
                array(
                    'read' => true,
                )
            );
        }
    }

    /**
     * Insert a new trade application request.
     *
     * @param array $data Sanitized form data.
     * @return int|false Insert ID or false on failure.
     */
    public static function insert_trade_request( $data ) {
        global $wpdb;

        $result = $wpdb->insert(
            self::trade_table_name(),
            array(
                'first_name'             => $data['first_name'],
                'last_name'              => $data['last_name'],
                'phone'                  => $data['phone'],
                'email'                  => $data['email'],
                'practice_name'          => $data['practice_name'],
                'hcp_type'               => $data['hcp_type'],
                'hcp_reg_number'         => $data['hcp_reg_number'],
                'company_number'         => $data['company_number'],
                'nz_business_number'     => $data['nz_business_number'],
                'legal_entity_number'    => $data['legal_entity_number'],
                'acts_as_trustee'        => $data['acts_as_trustee'],
                'trust_name'             => $data['trust_name'],
                'trading_name'           => $data['trading_name'],
                'physical_address'       => $data['physical_address'],
                'postal_same_as_physical' => $data['postal_same_as_physical'],
                'postal_address'         => $data['postal_address'],
                'business_email'         => $data['business_email'],
                'accounts_payable_contact' => $data['accounts_payable_contact'],
                'delivery_contact'       => $data['delivery_contact'],
                'nature_of_business'     => $data['nature_of_business'],
                'date_of_incorporation'  => $data['date_of_incorporation'],
                'ird_number'             => $data['ird_number'],
                'credit_limit_over_5000' => $data['credit_limit_over_5000'],
                'media_upload'           => $data['media_upload'],
                'trade_reference'        => $data['trade_reference'],
                'signature'              => $data['signature'],
                'status'                 => 'pending',
            ),
            array_fill( 0, 27, '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get trade applications filtered by status.
     *
     * @param string $status pending|approved|rejected or empty for all.
     * @return array
     */
    public static function get_trade_requests( $status = '' ) {
        global $wpdb;
        $table = self::trade_table_name();

        if ( $status ) {
            return $wpdb->get_results(
                $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY submitted_at DESC", $status )
            );
        }

        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY submitted_at DESC" );
    }

    /**
     * Get a single trade application by ID.
     *
     * @param int $id Row ID.
     * @return object|null
     */
    public static function get_trade_request( $id ) {
        global $wpdb;
        $table = self::trade_table_name();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
        );
    }

    /**
     * Update the status of a trade application.
     *
     * @param int    $id     Row ID.
     * @param string $status New status.
     * @param int    $admin_id Optional admin user ID who reviewed.
     * @return bool
     */
    public static function update_trade_status( $id, $status, $admin_id = 0 ) {
        global $wpdb;

        $data   = array(
            'status'      => $status,
            'reviewed_at' => current_time( 'mysql' ),
        );
        $format = array( '%s', '%s' );

        if ( $admin_id ) {
            $data['reviewed_by'] = $admin_id;
            $format[]            = '%d';
        }

        return (bool) $wpdb->update(
            self::trade_table_name(),
            $data,
            array( 'id' => $id ),
            $format,
            array( '%d' )
        );
    }

    /**
     * Check whether an email already has a pending or approved trade application.
     *
     * @param string $email Email address.
     * @return bool
     */
    public static function trade_email_exists_in_requests( $email ) {
        global $wpdb;
        $table = self::trade_table_name();

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE email = %s AND status IN ('pending','approved')",
                $email
            )
        );

        return $count > 0;
    }
}
