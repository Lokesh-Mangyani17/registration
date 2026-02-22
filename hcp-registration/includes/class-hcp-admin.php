<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin dashboard for reviewing HCP registration requests and Trade Applications.
 */
class HCP_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_post_hcp_approve', array( __CLASS__, 'handle_approve' ) );
        add_action( 'admin_post_hcp_reject', array( __CLASS__, 'handle_reject' ) );
        add_action( 'admin_post_trade_approve', array( __CLASS__, 'handle_trade_approve' ) );
        add_action( 'admin_post_trade_reject', array( __CLASS__, 'handle_trade_reject' ) );
        add_action( 'show_user_profile', array( __CLASS__, 'render_user_profile_fields' ) );
        add_action( 'edit_user_profile', array( __CLASS__, 'render_user_profile_fields' ) );
        add_action( 'personal_options_update', array( __CLASS__, 'save_user_profile_fields' ) );
        add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_profile_fields' ) );
    }

    /**
     * Register the admin menu item (visible to administrators only).
     */
    public static function add_menu_page() {
        $hcp_pending   = count( HCP_DB::get_requests( 'pending' ) );
        $trade_pending = count( HCP_DB::get_trade_requests( 'pending' ) );
        $total_pending = $hcp_pending + $trade_pending;
        $badge         = $total_pending ? " <span class='awaiting-mod'>{$total_pending}</span>" : '';

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
     * Render the admin page with tabs for HCP and Trade.
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'hcp-registration' ) );
        }

        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'hcp';

        if ( 'trade' === $tab ) {
            self::render_trade_page();
        } else {
            self::render_hcp_page();
        }
    }

    /* ======================================================================
       HCP Registration Tab
       ====================================================================== */

    /**
     * Render the HCP registrations page (list or detail).
     */
    private static function render_hcp_page() {
        // Single request detail view.
        if ( isset( $_GET['view'] ) ) {
            $request = HCP_DB::get_request( absint( $_GET['view'] ) );
            if ( ! $request ) {
                echo '<div class="wrap"><p>' . esc_html__( 'Request not found.', 'hcp-registration' ) . '</p></div>';
                return;
            }
            self::render_tabs( 'hcp' );
            self::render_hcp_detail( $request );
            return;
        }

        // Listing view.
        $status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'pending';
        $requests = HCP_DB::get_requests( $status );

        self::render_tabs( 'hcp' );
        self::render_hcp_list( $requests, $status );
    }

    /**
     * Render the primary tab navigation.
     */
    private static function render_tabs( $current_tab ) {
        $page_url       = admin_url( 'admin.php?page=hcp-registrations' );
        $hcp_pending    = count( HCP_DB::get_requests( 'pending' ) );
        $trade_pending  = count( HCP_DB::get_trade_requests( 'pending' ) );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Registration Dashboard', 'hcp-registration' ); ?></h1>
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'hcp', $page_url ) ); ?>"
                   class="nav-tab <?php echo 'hcp' === $current_tab ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'HCP Registrations', 'hcp-registration' ); ?>
                    <?php if ( $hcp_pending ) : ?>
                        <span class="awaiting-mod"><?php echo esc_html( $hcp_pending ); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'trade', $page_url ) ); ?>"
                   class="nav-tab <?php echo 'trade' === $current_tab ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Trade Applications', 'hcp-registration' ); ?>
                    <?php if ( $trade_pending ) : ?>
                        <span class="awaiting-mod"><?php echo esc_html( $trade_pending ); ?></span>
                    <?php endif; ?>
                </a>
            </nav>
        <?php
    }

    /**
     * Render the HCP list table.
     */
    private static function render_hcp_list( $requests, $current_status ) {
        $page_url = admin_url( 'admin.php?page=hcp-registrations&tab=hcp' );
        ?>
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
                                    <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'hcp', 'view' => $r->id ), admin_url( 'admin.php?page=hcp-registrations' ) ) ); ?>" class="button button-small">
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
     * Render a single HCP request detail view.
     */
    private static function render_hcp_detail( $r ) {
        $page_url = admin_url( 'admin.php?page=hcp-registrations&tab=hcp' );
        ?>
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

    /* ======================================================================
       Trade Applications Tab
       ====================================================================== */

    /**
     * Render the Trade Applications page (list or detail).
     */
    private static function render_trade_page() {
        // Single request detail view.
        if ( isset( $_GET['trade_view'] ) ) {
            $request = HCP_DB::get_trade_request( absint( $_GET['trade_view'] ) );
            if ( ! $request ) {
                echo '<div class="wrap"><p>' . esc_html__( 'Application not found.', 'hcp-registration' ) . '</p></div>';
                return;
            }
            self::render_tabs( 'trade' );
            self::render_trade_detail( $request );
            return;
        }

        $status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'pending';
        $requests = HCP_DB::get_trade_requests( $status );

        self::render_tabs( 'trade' );
        self::render_trade_list( $requests, $status );
    }

    /**
     * Render the Trade Applications list table.
     */
    private static function render_trade_list( $requests, $current_status ) {
        $page_url = admin_url( 'admin.php?page=hcp-registrations&tab=trade' );
        ?>
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
                    $count   = count( HCP_DB::get_trade_requests( $key ) );
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
                        <th><?php esc_html_e( 'Trading Name', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Existing Account', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Submitted', 'hcp-registration' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'hcp-registration' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $requests ) ) : ?>
                        <tr><td colspan="6"><?php esc_html_e( 'No applications found.', 'hcp-registration' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $requests as $r ) : ?>
                            <?php $existing_user = get_user_by( 'email', $r->email ); ?>
                            <tr>
                                <td><?php echo esc_html( $r->first_name . ' ' . $r->last_name ); ?></td>
                                <td><?php echo esc_html( $r->email ); ?></td>
                                <td><?php echo esc_html( $r->trading_name ); ?></td>
                                <td>
                                    <?php if ( $existing_user ) : ?>
                                        <span class="hcp-status hcp-status-approved"><?php echo esc_html( implode( ', ', $existing_user->roles ) ); ?></span>
                                    <?php else : ?>
                                        <span style="color:#999;"><?php esc_html_e( 'None', 'hcp-registration' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $r->submitted_at ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'trade', 'trade_view' => $r->id ), admin_url( 'admin.php?page=hcp-registrations' ) ) ); ?>" class="button button-small">
                                        <?php esc_html_e( 'View', 'hcp-registration' ); ?>
                                    </a>
                                    <?php if ( 'pending' === $r->status ) : ?>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=trade_approve&id=' . $r->id ), 'trade_action_' . $r->id ) ); ?>" class="button button-small button-primary">
                                            <?php esc_html_e( 'Approve', 'hcp-registration' ); ?>
                                        </a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=trade_reject&id=' . $r->id ), 'trade_action_' . $r->id ) ); ?>" class="button button-small hcp-reject-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reject this application?', 'hcp-registration' ); ?>');">
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
     * Render a single trade application detail view.
     */
    private static function render_trade_detail( $r ) {
        $page_url      = admin_url( 'admin.php?page=hcp-registrations&tab=trade' );
        $existing_user = get_user_by( 'email', $r->email );
        ?>
            <a href="<?php echo esc_url( $page_url ); ?>">&larr; <?php esc_html_e( 'Back to list', 'hcp-registration' ); ?></a>

            <?php if ( $existing_user ) : ?>
                <div class="notice notice-info" style="margin:16px 0;">
                    <p>
                        <?php
                        printf(
                            /* translators: 1: user login, 2: roles */
                            esc_html__( 'This applicant already has an account: %1$s (Roles: %2$s). Approving will upgrade their role to Trade Account.', 'hcp-registration' ),
                            '<strong>' . esc_html( $existing_user->user_login ) . '</strong>',
                            '<strong>' . esc_html( implode( ', ', $existing_user->roles ) ) . '</strong>'
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <h3><?php esc_html_e( 'Personal Details', 'hcp-registration' ); ?></h3>
            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'First Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->first_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Last Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->last_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Phone', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->phone ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Email', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->email ); ?></td></tr>
            </table>

            <h3><?php esc_html_e( 'Healthcare Professional Details', 'hcp-registration' ); ?></h3>
            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'Practice / Clinic Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->practice_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Healthcare Professional Type', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->hcp_type ); ?></td></tr>
                <tr><th><?php esc_html_e( 'HCP Registration Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->hcp_reg_number ); ?></td></tr>
            </table>

            <h3><?php esc_html_e( 'Business Details', 'hcp-registration' ); ?></h3>
            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'Company Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->company_number ); ?></td></tr>
                <tr><th><?php esc_html_e( 'NZ Business Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->nz_business_number ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Legal Entity Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->legal_entity_number ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Acts as Trustee', 'hcp-registration' ); ?></th><td><?php echo esc_html( ucfirst( $r->acts_as_trustee ) ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Trading Name', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->trading_name ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Physical Address', 'hcp-registration' ); ?></th><td><?php echo nl2br( esc_html( $r->physical_address ) ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Postal Address', 'hcp-registration' ); ?></th><td><?php echo nl2br( esc_html( $r->postal_address ) ); ?></td></tr>
            </table>

            <h3><?php esc_html_e( 'Contact & Operations', 'hcp-registration' ); ?></h3>
            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'Business Email', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->business_email ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Accounts Payable Contact', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->accounts_payable_contact ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Delivery Contact', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->delivery_contact ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Nature of Business', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->nature_of_business ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Date of Incorporation', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->date_of_incorporation ); ?></td></tr>
                <tr><th><?php esc_html_e( 'IRD Number', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->ird_number ); ?></td></tr>
            </table>

            <h3><?php esc_html_e( 'Financial & Documents', 'hcp-registration' ); ?></h3>
            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'Credit Limit Over $5,000', 'hcp-registration' ); ?></th><td><?php echo esc_html( ucfirst( $r->credit_limit_over_5000 ) ); ?></td></tr>
                <tr>
                    <th><?php esc_html_e( 'Media Upload', 'hcp-registration' ); ?></th>
                    <td>
                        <?php if ( ! empty( $r->media_upload ) ) : ?>
                            <a href="<?php echo esc_url( $r->media_upload ); ?>" target="_blank"><?php esc_html_e( 'View File', 'hcp-registration' ); ?></a>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th><?php esc_html_e( 'Trade Reference', 'hcp-registration' ); ?></th><td><?php echo nl2br( esc_html( $r->trade_reference ) ); ?></td></tr>
                <tr>
                    <th><?php esc_html_e( 'Signature', 'hcp-registration' ); ?></th>
                    <td>
                        <?php if ( ! empty( $r->signature ) ) : ?>
                            <img src="<?php echo esc_attr( $r->signature ); ?>" alt="<?php esc_attr_e( 'Signature', 'hcp-registration' ); ?>" style="max-width:400px;border:1px solid #ddd;padding:4px;">
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <table class="form-table hcp-detail-table">
                <tr><th><?php esc_html_e( 'Status', 'hcp-registration' ); ?></th><td><span class="hcp-status hcp-status-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( ucfirst( $r->status ) ); ?></span></td></tr>
                <tr><th><?php esc_html_e( 'Submitted At', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->submitted_at ); ?></td></tr>
                <?php if ( $r->reviewed_at ) : ?>
                    <tr><th><?php esc_html_e( 'Reviewed At', 'hcp-registration' ); ?></th><td><?php echo esc_html( $r->reviewed_at ); ?></td></tr>
                <?php endif; ?>
            </table>

            <?php if ( 'pending' === $r->status ) : ?>
                <div class="hcp-detail-actions">
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=trade_approve&id=' . $r->id ), 'trade_action_' . $r->id ) ); ?>" class="button button-primary button-large">
                        <?php esc_html_e( 'Approve', 'hcp-registration' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=trade_reject&id=' . $r->id ), 'trade_action_' . $r->id ) ); ?>" class="button button-large hcp-reject-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reject this application?', 'hcp-registration' ); ?>');">
                        <?php esc_html_e( 'Reject', 'hcp-registration' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /* ======================================================================
       HCP Approve / Reject Handlers (unchanged logic)
       ====================================================================== */

    /**
     * Handle the HCP approve action.
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
                'tab'     => 'hcp',
                'status'  => 'pending',
                'message' => 'approved',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Handle the HCP reject action.
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
                'tab'     => 'hcp',
                'status'  => 'pending',
                'message' => 'rejected',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /* ======================================================================
       Trade Approve / Reject Handlers
       ====================================================================== */

    /**
     * Handle trade application approval.
     *
     * If the user already has a WordPress account (e.g. HCP), upgrade their
     * role to trade_account without creating a new user or resetting the
     * password. Otherwise create a new user just like HCP approval.
     */
    public static function handle_trade_approve() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'hcp-registration' ) );
        }

        $id = absint( $_GET['id'] ?? 0 );
        check_admin_referer( 'trade_action_' . $id );

        $request = HCP_DB::get_trade_request( $id );
        if ( ! $request || 'pending' !== $request->status ) {
            wp_die( esc_html__( 'Invalid application.', 'hcp-registration' ) );
        }

        $existing_user = get_user_by( 'email', $request->email );
        $is_existing   = false;

        if ( $existing_user ) {
            // Existing user – upgrade role.
            $user_id     = $existing_user->ID;
            $is_existing = true;
            $existing_user->add_role( 'trade_account' );
        } else {
            // New user – create account.
            $username = self::generate_username( $request->first_name, $request->last_name );
            $password = wp_generate_password( 20, true, true );

            $user_id = wp_insert_user( array(
                'user_login' => $username,
                'user_email' => $request->email,
                'user_pass'  => $password,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'role'       => 'trade_account',
            ) );

            if ( is_wp_error( $user_id ) ) {
                wp_die( esc_html( $user_id->get_error_message() ) );
            }
        }

        // Store trade-specific profile fields as user meta (separate from HCP).
        update_user_meta( $user_id, 'trade_practice_name', $request->practice_name );
        update_user_meta( $user_id, 'trade_hcp_type', $request->hcp_type );
        update_user_meta( $user_id, 'trade_hcp_reg_number', $request->hcp_reg_number );
        update_user_meta( $user_id, 'trade_company_number', $request->company_number );
        update_user_meta( $user_id, 'trade_nz_business_number', $request->nz_business_number );
        update_user_meta( $user_id, 'trade_legal_entity_number', $request->legal_entity_number );
        update_user_meta( $user_id, 'trade_acts_as_trustee', $request->acts_as_trustee );
        update_user_meta( $user_id, 'trade_trading_name', $request->trading_name );
        update_user_meta( $user_id, 'trade_physical_address', $request->physical_address );
        update_user_meta( $user_id, 'trade_postal_address', $request->postal_address );
        update_user_meta( $user_id, 'trade_business_email', $request->business_email );
        update_user_meta( $user_id, 'trade_accounts_payable_contact', $request->accounts_payable_contact );
        update_user_meta( $user_id, 'trade_delivery_contact', $request->delivery_contact );
        update_user_meta( $user_id, 'trade_nature_of_business', $request->nature_of_business );
        update_user_meta( $user_id, 'trade_date_of_incorporation', $request->date_of_incorporation );
        update_user_meta( $user_id, 'trade_ird_number', $request->ird_number );
        update_user_meta( $user_id, 'trade_credit_limit_over_5000', $request->credit_limit_over_5000 );
        update_user_meta( $user_id, 'trade_media_upload', $request->media_upload );
        update_user_meta( $user_id, 'trade_trade_reference', $request->trade_reference );
        update_user_meta( $user_id, 'trade_signature', $request->signature );

        HCP_DB::update_trade_status( $id, 'approved' );

        // Send appropriate approval email.
        HCP_Email::send_trade_approval_email( $user_id, $request, $is_existing );

        wp_safe_redirect( add_query_arg(
            array(
                'page'    => 'hcp-registrations',
                'tab'     => 'trade',
                'status'  => 'pending',
                'message' => 'approved',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Handle trade application rejection.
     */
    public static function handle_trade_reject() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'hcp-registration' ) );
        }

        $id = absint( $_GET['id'] ?? 0 );
        check_admin_referer( 'trade_action_' . $id );

        $request = HCP_DB::get_trade_request( $id );
        if ( ! $request || 'pending' !== $request->status ) {
            wp_die( esc_html__( 'Invalid application.', 'hcp-registration' ) );
        }

        HCP_DB::update_trade_status( $id, 'rejected' );

        // Notify the applicant about rejection.
        HCP_Email::send_trade_rejection_email( $request );

        wp_safe_redirect( add_query_arg(
            array(
                'page'    => 'hcp-registrations',
                'tab'     => 'trade',
                'status'  => 'pending',
                'message' => 'rejected',
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /* ======================================================================
       Shared Helpers
       ====================================================================== */

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

    /* ======================================================================
       User Profile Fields
       ====================================================================== */

    /**
     * Display HCP and Trade custom fields on the WordPress user profile page.
     *
     * @param WP_User $user The user object.
     */
    public static function render_user_profile_fields( $user ) {
        $user_roles = $user->roles;

        // ---- HCP Fields ----
        $phone          = get_user_meta( $user->ID, 'hcp_phone', true );
        $practice_name  = get_user_meta( $user->ID, 'hcp_practice_name', true );
        $hcp_type       = get_user_meta( $user->ID, 'hcp_type', true );
        $hcp_reg_number = get_user_meta( $user->ID, 'hcp_reg_number', true );

        if ( in_array( 'healthcare_professional', $user_roles, true ) || $phone || $practice_name || $hcp_type || $hcp_reg_number ) {
            ?>
            <h3><?php esc_html_e( 'Healthcare Professional Information', 'hcp-registration' ); ?></h3>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="hcp_phone"><?php esc_html_e( 'Phone', 'hcp-registration' ); ?></label></th>
                    <td><input type="text" name="hcp_phone" id="hcp_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="hcp_practice_name"><?php esc_html_e( 'Practice/Clinic Name', 'hcp-registration' ); ?></label></th>
                    <td><input type="text" name="hcp_practice_name" id="hcp_practice_name" value="<?php echo esc_attr( $practice_name ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="hcp_type"><?php esc_html_e( 'Healthcare Professional Type', 'hcp-registration' ); ?></label></th>
                    <td><input type="text" name="hcp_type" id="hcp_type" value="<?php echo esc_attr( $hcp_type ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="hcp_reg_number"><?php esc_html_e( 'HCP Registration Number', 'hcp-registration' ); ?></label></th>
                    <td><input type="text" name="hcp_reg_number" id="hcp_reg_number" value="<?php echo esc_attr( $hcp_reg_number ); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php
        }

        // ---- Trade Fields ----
        $trade_trading_name = get_user_meta( $user->ID, 'trade_trading_name', true );

        if ( in_array( 'trade_account', $user_roles, true ) || $trade_trading_name ) {
            $trade_fields = array(
                'trade_practice_name'          => __( 'Practice/Clinic Name', 'hcp-registration' ),
                'trade_hcp_type'               => __( 'Healthcare Professional Type', 'hcp-registration' ),
                'trade_hcp_reg_number'         => __( 'HCP Registration Number', 'hcp-registration' ),
                'trade_company_number'         => __( 'Company Number', 'hcp-registration' ),
                'trade_nz_business_number'     => __( 'NZ Business Number', 'hcp-registration' ),
                'trade_legal_entity_number'    => __( 'Legal Entity Number', 'hcp-registration' ),
                'trade_acts_as_trustee'        => __( 'Acts as Trustee', 'hcp-registration' ),
                'trade_trading_name'           => __( 'Trading Name', 'hcp-registration' ),
                'trade_physical_address'       => __( 'Physical Address', 'hcp-registration' ),
                'trade_postal_address'         => __( 'Postal Address', 'hcp-registration' ),
                'trade_business_email'         => __( 'Business Email', 'hcp-registration' ),
                'trade_accounts_payable_contact' => __( 'Accounts Payable Contact', 'hcp-registration' ),
                'trade_delivery_contact'       => __( 'Delivery Contact', 'hcp-registration' ),
                'trade_nature_of_business'     => __( 'Nature of Business', 'hcp-registration' ),
                'trade_date_of_incorporation'  => __( 'Date of Incorporation', 'hcp-registration' ),
                'trade_ird_number'             => __( 'IRD Number', 'hcp-registration' ),
                'trade_credit_limit_over_5000' => __( 'Credit Limit Over $5,000', 'hcp-registration' ),
            );
            ?>
            <h3><?php esc_html_e( 'Trade Account Information', 'hcp-registration' ); ?></h3>
            <table class="form-table" role="presentation">
                <?php foreach ( $trade_fields as $meta_key => $label ) : ?>
                    <tr>
                        <th><label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $label ); ?></label></th>
                        <td><input type="text" name="<?php echo esc_attr( $meta_key ); ?>" id="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $meta_key, true ) ); ?>" class="regular-text" /></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php
        }
    }

    /**
     * Save HCP and Trade custom fields when the user profile is updated.
     *
     * @param int $user_id The user ID.
     */
    public static function save_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        check_admin_referer( 'update-user_' . $user_id );

        // HCP fields.
        $hcp_meta = array( 'hcp_phone', 'hcp_practice_name', 'hcp_type', 'hcp_reg_number' );
        foreach ( $hcp_meta as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_user_meta( $user_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        // Trade fields.
        $trade_meta = array(
            'trade_practice_name', 'trade_hcp_type', 'trade_hcp_reg_number',
            'trade_company_number', 'trade_nz_business_number', 'trade_legal_entity_number',
            'trade_acts_as_trustee', 'trade_trading_name', 'trade_physical_address',
            'trade_postal_address', 'trade_business_email', 'trade_accounts_payable_contact',
            'trade_delivery_contact', 'trade_nature_of_business', 'trade_date_of_incorporation',
            'trade_ird_number', 'trade_credit_limit_over_5000',
        );
        foreach ( $trade_meta as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_user_meta( $user_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
    }
}
