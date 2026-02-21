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
     * @return bool
     */
    public static function update_status( $id, $status ) {
        global $wpdb;

        return (bool) $wpdb->update(
            self::table_name(),
            array(
                'status'      => $status,
                'reviewed_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $id ),
            array( '%s', '%s' ),
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
}
