<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin dashboard for reviewing HCP registration requests.
 */
class HCP_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_post_hcp_approve', array( __CLASS__, 'handle_approve' ) );
        add_action( 'admin_post_hcp_reject', array( __CLASS__, 'handle_reject' ) );
    }

    /**
     * Register the admin menu item (visible to administrators only).
     */
    public static function add_menu_page() {
        $pending = self::pending_count();
        $badge   = $pending ? " <span class='awaiting-mod'>{$pending}</span>" : '';

        add_menu_page(
            __( 'HCP Registrations', 'hcp-registration' ),
            __( 'HCP Registrations', 'hcp-registration' ) . $badge,
            'manage_options',
            'hcp-registrations',
            array( __CLASS__, 'render_page' ),
            'dashicons-id-alt',
            26
        );
    }

    /**
     * Get pending request count.
     */
    private static function pending_count() {
        $pending = HCP_DB::get_requests( 'pending' );
        return count( $pending );
    }

    /**
     * Enqueue admin-only styles.
     */
    public static function enqueue_assets( $hook ) {
        if ( 'toplevel_page_hcp-registrations' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'hcp-admin-css',
            HCP_REG_PLUGIN_URL . 'assets/css/hcp-admin.css',
            array(),
            HCP_REG_VERSION
        );
    }

    /**
     * Render the admin page: either a single-request detail view or the listing table.
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'hcp-registration' ) );
        }

        // Single request detail view.
        if ( isset( $_GET['view'] ) ) {
            $request = HCP_DB::get_request( absint( $_GET['view'] ) );
            if ( ! $request ) {
                echo '<div class="wrap"><p>' . esc_html__( 'Request not found.', 'hcp-registration' ) . '</p></div>';
                return;
            }
            self::render_detail( $request );
            return;
        }

        // Listing view.
        $status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'pending';
        $requests = HCP_DB::get_requests( $status );

        self::render_list( $requests, $status );
    }

    /**
     * Render the list table.
     */
    private static function render_list( $requests, $current_status ) {
        $page_url = admin_url( 'admin.php?page=hcp-registrations' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'HCP Registration Requests', 'hcp-registration' ); ?></h1>

            <ul class="subsubsub">
                <?php
                $statuses = array(
                    'pending'  => __( 'Pending', 'hcp-registration' ),
                    'approved' => __( 'Approved', 'hcp-registration' ),
                    'rejected' => __( 'Rejected', 'hcp-registration' ),
                );
                $links = array();
                foreach ( $statuses as $key => $label ) {
                    $class   = $current_status === $key ? ' class="current"' : '';
                    $count   = count( HCP_DB::get_requests( $key ) );
                    $links[] = sprintf(
                        '<li><a href="%s"%s>%s <span class="count">(%d)</span></a></li>',
                        esc_url( add_query_arg( 'status', $key, $page_url ) ),
                        $class,
                        esc_html( $label ),
                        $count
                    );
                }
                echo implode( ' | ', $links );
                ?>
            </ul>

            <table class="wp-list-table widefat fixed striped hcp-registrations-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Name', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'HCP Type', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Submitted', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'hcp-registration' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $requests ) ) : ?>
                        <tr><td colspan="5"><?php esc_html_e( 'No requests found.', 'hcp-registration' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $requests as $r ) : ?>
                            <tr>
                                <td><?php echo esc_html( $r->first_name . ' ' . $r->last_name ); ?></td>
                                <td><?php echo esc_html( $r->email ); ?></td>
                                <td><?php echo esc_html( $r->hcp_type ); ?></td>
                                <td><?php echo esc_html( $r->submitted_at ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( add_query_arg( 'view', $r->id, $page_url ) ); ?>" class="button button-small">
                                        <?php esc_html_e( 'View', 'hcp-registration' ); ?>
                                    </a>
                                    <?php if ( 'pending' === $r->status ) : ?>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcp_approve&id=' . $r->id ), 'hcp_action_' . $r->id ) ); ?>" class="button button-small button-primary">
                                            <?php esc_html_e( 'Approve', 'hcp-registration' ); ?>
                                        </a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcp_reject&id=' . $r->id ), 'hcp_action_' . $r->id ) ); ?>" class="button button-small hcp-reject-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reject this request?', 'hcp-registration' ); ?>');">
                                            <?php esc_html_e( 'Reject', 'hcp-registration' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render a single request detail view.
     */
    private static function render_detail( $r ) {
        $page_url = admin_url( 'admin.php?page=hcp-registrations' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Registration Request Detail', 'hcp-registration' ); ?></h1>
            <a href="<?php echo esc_url( $page_url ); ?>">&larr; <?php esc_html_e( 'Back to list', 'hcp-registration' ); ?></a>

            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'First Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->first_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Last Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->last_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Phone', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->phone ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Email', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->email ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Practice / Clinic Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->practice_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Healthcare Professional Type', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->hcp_type ); ?></td></tr>
                <tr><th><?php esc_html_e( 'HCP Registration Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->hcp_reg_number ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Status', 'hcp-registration' ); ?></th><td><span class="hcp-status hcp-status-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( ucfirst( $r->status ) ); ?></span></td></tr>
                <tr><th><?php esc_html_e( 'Submitted At', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->submitted_at ); ?></td></tr>
                <?php if ( $r->reviewed_at ) : ?>
                    <tr><th><?php esc_html_e( 'Reviewed At', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->reviewed_at ); ?></td></tr>
                <?php endif; ?>
            </table>

            <?php if ( 'pending' === $r->status ) : ?>
                <div class="hcp-detail-actions">
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcp_approve&id=' . $r->id ), 'hcp_action_' . $r->id ) ); ?>" class="button button-primary button-large">
                        <?php esc_html_e( 'Approve', 'hcp-registration' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcp_reject&id=' . $r->id ), 'hcp_action_' . $r->id ) ); ?>" class="button button-large hcp-reject-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reject this request?', 'hcp-registration' ); ?>');">
                        <?php esc_html_e( 'Reject', 'hcp-registration' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handle the approve action.
     */
    public static function handle_approve() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'hcp-registration' ) );
        }

        $id = absint( $_GET['id'] ?? 0 );
        check_admin_referer( 'hcp_action_' . $id );

        $request = HCP_DB::get_request( $id );
        if ( ! $request || 'pending' !== $request->status ) {
            wp_die( esc_html__( 'Invalid request.', 'hcp-registration' ) );
        }

        // Create WordPress user account.
        $username = self::generate_username( $request->first_name, $request->last_name );
        $password = wp_generate_password( 20, true, true );

        $user_id = wp_insert_user( array(
            'user_login' => $username,
            'user_email' => $request->email,
            'user_pass'  => $password,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'role'       => 'healthcare_professional',
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_die( esc_html( $user_id->get_error_message() ) );
        }

        // Store additional profile fields as user meta.
        update_user_meta( $user_id, 'hcp_phone', $request->phone );
        update_user_meta( $user_id, 'hcp_practice_name', $request->practice_name );
        update_user_meta( $user_id, 'hcp_type', $request->hcp_type );
        update_user_meta( $user_id, 'hcp_reg_number', $request->hcp_reg_number );

        HCP_DB::update_status( $id, 'approved' );

        // Send approval email with password-reset (set-password) link.
        HCP_Email::send_approval_email( $user_id, $request );

        wp_safe_redirect( add_query_arg(
            array(
                'page'    => 'hcp-registrations',
                'status'  => 'pending',
                'message' => 'approved',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Handle the reject action.
     */
    public static function handle_reject() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'hcp-registration' ) );
        }

        $id = absint( $_GET['id'] ?? 0 );
        check_admin_referer( 'hcp_action_' . $id );

        $request = HCP_DB::get_request( $id );
        if ( ! $request || 'pending' !== $request->status ) {
            wp_die( esc_html__( 'Invalid request.', 'hcp-registration' ) );
        }

        HCP_DB::update_status( $id, 'rejected' );

        // Notify the applicant about rejection.
        HCP_Email::send_rejection_email( $request );

        wp_safe_redirect( add_query_arg(
            array(
                'page'    => 'hcp-registrations',
                'status'  => 'pending',
                'message' => 'rejected',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Generate a unique username from first + last name.
     */
    private static function generate_username( $first, $last ) {
        $base = sanitize_user( strtolower( $first . '.' . $last ), true );
        $username = $base;
        $i = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }
}
