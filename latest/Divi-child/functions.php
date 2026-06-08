<?php
/**
 * NUBU Pharma - Complete Refactored Functions
 * 
 * This file contains ALL functionality from the original functions.php
 * fully refactored with proper documentation and organization.
 *
 * @package NUBU_Pharma
 * @author  NUBU Development Team  
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================================
// CONSTANTS & CONFIGURATION..
// ============================================================================

/**
 * Database version tracking constants
 */
define( 'NUBU_DOWNLOADS_DB_OPTION', 'nubu_downloads_db_version' );
define( 'NUBU_DOWNLOADS_DB_VERSION', '1.0.1' );

/**
 * HCP Registration paths
 * These must be defined before loading plugin files
 */
define( 'HCP_REG_DIR', get_stylesheet_directory() . '/hcp-registration/' );
define( 'HCP_REG_URL', get_stylesheet_directory_uri() . '/hcp-registration/' );

// ============================================================================
// VENDOR & DEPENDENCIES
// ============================================================================

/**
 * Load all vendor dependencies
 * 
 * Initializes Carbon Fields and other third-party libraries.
 * Hooked to 'after_setup_theme' to ensure proper loading order.
 *
 * @since 1.0.0
 * @return void
 */
if ( ! function_exists( 'nubu_load_vendors' ) ) {
    function nubu_load_vendors() {
        require_once 'vendor/autoload.php';
        \Carbon_Fields\Carbon_Fields::boot();
    }
    add_action( 'after_setup_theme', 'nubu_load_vendors' );
}

// ============================================================================
// INCLUDES - Load modular functionality
// ============================================================================

require_once HCP_REG_DIR . 'hcp-registration.php';
require_once 'admin/custom-posts.php';
require_once 'admin/custom-fields.php';
require_once 'admin/custom-taxonomies.php';
require_once 'admin/filters.php';
require_once 'unleashed/api.php';
require_once 'unleashed/sync.php';
require_once 'public/filters.php';
require_once 'public/single-product-functions.php';
require_once 'my-account/hooks.php';

// ============================================================================
// DATABASE SETUP & MIGRATIONS
// ============================================================================

/**
 * Initialize PDF tracking database tables
 * 
 * Creates necessary tables for tracking PDF views both globally
 * and per-user. Uses dbDelta for safe schema updates.
 * Runs on 'wp_loaded' with version checking.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_wp_loaded() {
    global $wpdb;
    
    $db_version = get_option( NUBU_DOWNLOADS_DB_OPTION, '0.0.0' );

    // Only run if database needs updating
    if ( version_compare( $db_version, NUBU_DOWNLOADS_DB_VERSION, '<' ) ) {
        $counts_table    = $wpdb->prefix . 'pdf_view_counts';
        $users_table     = $wpdb->prefix . 'pdf_user_views';
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = array();
        
        // Global PDF view counts table
        $sql[] = "CREATE TABLE $counts_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            pdf_id BIGINT UNSIGNED NOT NULL,
            view_count BIGINT UNSIGNED DEFAULT 0,
            last_viewed DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY pdf_unique (pdf_id)
        ) $charset_collate;";

        // Per-user PDF view tracking table
        $sql[] = "CREATE TABLE $users_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            pdf_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            view_count INT UNSIGNED DEFAULT 1,
            last_viewed DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY pdf_user_unique (pdf_id, user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        
        // Only update version if no database errors occurred
        if ( empty( $wpdb->last_error ) ) {
            update_option( NUBU_DOWNLOADS_DB_OPTION, NUBU_DOWNLOADS_DB_VERSION );
        }
    }
}
add_action( 'wp_loaded', 'nubu_wp_loaded' );

/**
 * Setup HCP Registration tables and roles
 * 
 * Ensures Healthcare Professional registration tables are created
 * and kept up to date. Runs on admin_init with version checking.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_hcp_setup() {
    if ( get_option( 'hcp_reg_version' ) !== HCP_REG_VERSION ) {
        HCP_DB::create_table();
        HCP_DB::create_trade_table();
        update_option( 'hcp_reg_version', HCP_REG_VERSION );
    }
}
add_action( 'admin_init', 'nubu_hcp_setup' );

// ============================================================================
// CUSTOM USER ROLES
// ============================================================================

/**
 * Register custom user roles for the platform
 * 
 * Creates specialized roles for different user types:
 * - pending_approval: Users awaiting approval (no capabilities)
 * - trade_account: Trade customers (inherits WooCommerce customer caps)
 * - healthcare_professional: HCP users (inherits subscriber caps)
 *
 * @since 1.0.0
 * @return void
 */
function nubu_add_custom_role_below_subscriber() {
    // Remove roles first to ensure clean updates
    remove_role( 'pending_approval' );
    remove_role( 'patients' );
    remove_role( 'healthcare_professional' );

    // Add custom roles with appropriate capabilities
    add_role( 'pending_approval', 'Pending Approval', array() );
    $customer_role    = get_role( 'customer' );
    $subscriber_role  = get_role( 'subscriber' );
    add_role( 'trade_account', 'Trade Account', $customer_role ? $customer_role->capabilities : array( 'read' => true ) );
    add_role( 'healthcare_professional', 'Healthcare Professionals', $subscriber_role ? $subscriber_role->capabilities : array( 'read' => true ) );
}
add_action( 'init', 'nubu_add_custom_role_below_subscriber' );

// ============================================================================
// SCRIPTS & STYLES
// ============================================================================

/**
 * Enqueue frontend scripts and styles
 * 
 * Registers and enqueues all required JavaScript and CSS files
 * for the frontend. Includes localization for AJAX functionality.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_enqueue_scripts() {
    // Register third-party libraries
    wp_register_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.3/gsap.min.js', null, '1.0.0', true );
    wp_register_script( 'animation', get_stylesheet_directory_uri() . '/js/animation.js', array(), '1.0.0', true );
    wp_register_script( 'cookie', get_stylesheet_directory_uri() . '/js/cookie.js', array( 'jquery', 'gsap' ), '1.0.2', true );
    wp_register_script( 'chart', get_stylesheet_directory_uri() . '/js/chart.min.js', array(), '4.5.0', true );
    
    // Enqueue main theme script with dependencies
    wp_enqueue_script( 'nubu', get_stylesheet_directory_uri() . '/js/script.js', array( 'jquery', 'chart', 'swiper' ), '0.0.11', true );
    
    // Localize script for AJAX requests
    wp_localize_script( 'nubu', 'nubuData', array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'ajaxnonce' => wp_create_nonce( 'nubupharma-nonce' ),
        'post_id'   => intval( get_queried_object_id() )
    ) );
    
    // Enqueue cookie consent script
    wp_enqueue_script( 'cookie' );
}
add_action( 'wp_enqueue_scripts', 'nubu_enqueue_scripts' );

/**
 * Enqueue admin scripts and styles
 * 
 * Conditionally loads admin assets based on the current screen.
 * Includes Select2, color picker, and custom admin functionality.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_admin_enqueue_scripts() {
    // Always load admin stylesheet
    wp_enqueue_style( 'admin', get_stylesheet_directory_uri() . '/css/admin.css', array( 'wp-color-picker' ), '0.0.2' );
    
    $screen = get_current_screen();
    
    // Product edit screen assets
    if ( $screen && 'product' === $screen->post_type ) {
        wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '0.0.1' );
        wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '0.0.1', true );
        wp_enqueue_script( 'admin', get_stylesheet_directory_uri() . '/js/admin.js', array( 'jquery', 'select2', 'wp-color-picker' ), '0.0.1', true );
    }
    
    // Settings page assets
    $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
    if ( 'nubu-product-tag-settings' === $current_page ) {
        wp_enqueue_script( 'admin', get_stylesheet_directory_uri() . '/js/admin.js', array( 'jquery', 'wp-color-picker' ), '0.0.1', true );
        wp_enqueue_style( 'admin', get_stylesheet_directory_uri() . '/css/admin.css', array( 'wp-color-picker' ), '0.0.1' );
    }
}
add_action( 'admin_enqueue_scripts', 'nubu_admin_enqueue_scripts' );

// ============================================================================
// ADMIN MENUS & PAGES
// ============================================================================

/**
 * Register custom admin menu pages
 * 
 * Creates the NUBU admin panel with various sub-pages for:
 * - Sync Management
 * - User Documents
 * - PDF View Tracking
 * - Product Tag Settings
 *
 * @since 1.0.0
 * @return void
 */
function nubu_add_admin_pages() {
    // Main NUBU menu
    add_menu_page(
        'NUBU',
        'NUBU',
        'manage_options',
        'nubu-panel',
        '__return_null',
        'dashicons-admin-generic',
        120
    );
    
    // Sync Management submenu
    add_submenu_page(
        'nubu-panel',
        'Sync Management',
        'Sync Management',
        'manage_options',
        'sync-management',
        'sync_management_callback'
    );
    
    // User Documents submenu
    add_submenu_page(
        'nubu-panel',
        'User Documents',
        'User Documents',
        'manage_options',
        'user-documents',
        'nubu_render_user_documents_admin_page'
    );
    
    // PDF View Users submenu
    add_submenu_page(
        null,
        'PDF View Users',
        'PDF View Users',
        'manage_options',
        'pdf-views-users',
        'nubu_pdf_users_admin_page'
    );
    
    // PDF Views submenu
    add_submenu_page(
        'nubu-panel',
        'PDF Views',
        'PDF Views',
        'manage_options',
        'pdf-views',
        'nubu_pdf_views_admin_page'
    );

    // Product Tag Settings (under Products menu)
    add_submenu_page(
        'edit.php?post_type=product',
        __( 'Custom Tag', 'woocommerce' ),
        __( 'Custom Tag', 'woocommerce' ),
        'manage_options',
        'nubu-product-tag-settings',
        'nubu_render_product_settings_page'
    );
}
add_action( 'admin_menu', 'nubu_add_admin_pages' );

/**
 * Display admin notices for HCP registration actions
 * 
 * Shows success/warning messages after approving or rejecting
 * healthcare professional registration requests.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_hcp_admin_notices() {
    // Only show on HCP registrations page
    if ( ! isset( $_GET['page'] ) || 'hcp-registrations' !== $_GET['page'] ) {
        return;
    }

    if ( ! isset( $_GET['message'] ) ) {
        return;
    }

    $msg = sanitize_text_field( wp_unslash( $_GET['message'] ) );

    // Approval notice
    if ( 'approved' === $msg ) {
        echo '<div class="notice notice-success is-dismissible hcp-admin-notice"><p>';
        esc_html_e( 'Registration approved. The user account has been created and an email with login instructions has been sent.', 'hcp-registration' );
        echo '</p></div>';
    } 
    // Rejection notice
    elseif ( 'rejected' === $msg ) {
        echo '<div class="notice notice-warning is-dismissible hcp-admin-notice"><p>';
        esc_html_e( 'Registration request has been rejected. The applicant has been notified.', 'hcp-registration' );
        echo '</p></div>';
    }
}
add_action( 'admin_notices', 'nubu_hcp_admin_notices' );

// ============================================================================
// PDF VIEW TRACKING - ADMIN PAGES
// ============================================================================

/**
 * Display PDF global views admin page
 * 
 * Shows paginated list of all PDFs with their total view counts,
 * last viewed timestamp, and link to per-user view details.
 *
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object
 * @return void
 */
function nubu_pdf_views_admin_page() {
    global $wpdb;

    $table       = $wpdb->prefix . 'pdf_view_counts';
    $per_page    = 20;
    $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $offset      = ( $current_page - 1 ) * $per_page;

    // Get total count for pagination
    $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

    // Get paginated results
    $pdfs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table ORDER BY view_count DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );
    
    echo '<div class="wrap"><h1>PDF Global Views</h1>';
    
    if ( ! empty( $pdfs ) && is_array( $pdfs ) ) {
        echo '<table class="widefat striped"><thead><tr>
                <th>PDF</th><th>Views</th><th>Last Viewed</th><th>Users</th>
              </tr></thead><tbody>';

        foreach ( $pdfs as $pdf ) {
            $file_url   = wp_get_attachment_url( intval( $pdf->pdf_id ) );
            $file_name  = get_the_title( intval( $pdf->pdf_id ) );
            $users_link = admin_url( 'admin.php?page=pdf-views-users&pdf=' . intval( $pdf->pdf_id ) );
            
            echo '<tr>
                    <td><a href="' . esc_url( $file_url ) . '" target="_blank">' . esc_html( $file_name ) . '</a></td>
                    <td>' . esc_html( $pdf->view_count ) . '</td>
                    <td>' . esc_html( $pdf->last_viewed ) . '</td>
                    <td><a href="' . esc_url( $users_link ) . '">View Users</a></td>
                  </tr>';
        }

        echo '</tbody></table>';

        // Pagination controls
        $total_pages = ceil( $total_items / $per_page );

        if ( $total_pages > 1 ) {
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links( array(
                'base'      => add_query_arg( 'paged', '%#%' ),
                'format'    => '',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => '«',
                'next_text' => '»'
            ) );
            echo '</div></div>';
        }
    }

    echo '</div>';
}

/**
 * Display per-user PDF views admin page
 * 
 * Shows which users have viewed a specific PDF file,
 * with view counts and timestamps for each user.
 *
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object
 * @return void
 */
function nubu_pdf_users_admin_page() {
    // Require PDF ID parameter
    if ( ! isset( $_GET['pdf'] ) ) {
        echo '<div class="wrap"><h2>No PDF selected</h2></div>';
        return;
    }

    $pdf_id = intval( $_GET['pdf'] );

    global $wpdb;
    $table       = $wpdb->prefix . 'pdf_user_views';
    $per_page    = 20;
    $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $offset      = ( $current_page - 1 ) * $per_page;

    // Get total count for this PDF
    $total_items = $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE pdf_id = %d", $pdf_id )
    );

    // Get paginated user views
    $views = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE pdf_id = %d
             ORDER BY view_count DESC LIMIT %d OFFSET %d",
            $pdf_id,
            $per_page,
            $offset
        )
    );
    
    if ( ! empty( $views ) && is_array( $views ) ) {
        echo '<div class="wrap">';
        echo '<h1>Users who viewed: ' . esc_html( get_the_title( $pdf_id ) ) . '</h1>';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=pdf-views' ) ) . '">← Back</a><br><br>';

        echo '<table class="widefat striped"><thead><tr>
                <th>User</th><th>Email</th><th>Views</th><th>Last Viewed</th>
              </tr></thead><tbody>';

        foreach ( $views as $v ) {
            $user = get_userdata( $v->user_id );
            if ( ! $user ) {
                continue;
            }

            echo '<tr>
                    <td>' . esc_html( $user->display_name ) . '</td>
                    <td>' . esc_html( $user->user_email ) . '</td>
                    <td>' . esc_html( $v->view_count ) . '</td>
                    <td>' . esc_html( $v->last_viewed ) . '</td>
                  </tr>';
        }

        echo '</tbody></table>';

        // Pagination
        $total_pages = ceil( $total_items / $per_page );

        if ( $total_pages > 1 ) {
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links( array(
                'base'      => add_query_arg( 'paged', '%#%' ),
                'format'    => '',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => '«',
                'next_text' => '»'
            ) );
            echo '</div></div>';
        }

        echo '</div>';
    }
}

/**
 * Handle PDF downloads with view tracking
 * 
 * Intercepts PDF file requests to track views both globally
 * and per-user. Prevents double-counting using transients.
 * Serves the file inline for browser viewing.
 *
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object
 * @return void
 */
function nubu_download_pdf() {
    // Only for logged-in users
    if ( ! is_user_logged_in() ) {
        return;
    }

    // Require file parameter
    if ( ! isset( $_GET['file'] ) ) {
        return;
    }

    $file_id  = intval( $_GET['file'] );
    $file_url = wp_get_attachment_url( $file_id );
    
    // Only handle PDF files
    if ( ! str_ends_with( $file_url, '.pdf' ) ) {
        return;
    }
    
    global $wpdb;
    $counts_table = $wpdb->prefix . 'pdf_view_counts';
    $users_table  = $wpdb->prefix . 'pdf_user_views';
    $user_id      = get_current_user_id();

    // Prevent double counting (PDF viewers make duplicate requests)
    $lock = 'pdf_lock_' . md5( $file_id . '_' . $user_id );
    if ( get_transient( $lock ) ) {
        header( 'Content-Type: application/pdf' );
        readfile( $file_url );
        exit;
    }
    set_transient( $lock, 1, 5 ); // Lock for 5 seconds

    // Update global view count
    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO $counts_table (pdf_id, view_count, last_viewed)
             VALUES (%d, 1, NOW())
             ON DUPLICATE KEY UPDATE
                view_count = view_count + 1,
                last_viewed = NOW()",
            $file_id
        )
    );

    // Update per-user tracking
    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO $users_table (pdf_id, user_id, view_count, last_viewed)
             VALUES (%d, %d, 1, NOW())
             ON DUPLICATE KEY UPDATE
                view_count = view_count + 1,
                last_viewed = NOW()",
            $file_id,
            $user_id
        )
    );
    
    // Serve the PDF file
    $mime_type = mime_content_type( $file_url );
    header( 'Content-Type: ' . $mime_type );
    header( 'Content-Disposition: inline; filename="' . basename( $file_url ) . '"' );
    header( 'Content-Length: ' . filesize( $file_url ) );
    flush();
    readfile( $file_url );
    exit;
}
add_action( 'template_redirect', 'nubu_download_pdf' );

// ============================================================================
// USER DOCUMENTS ADMIN PAGE
// ============================================================================

/**
 * Render user documents admin page
 * 
 * Allows administrators to select a user and view their uploaded documents
 * including Section 29 data and pharmacy licenses with expiry dates.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_render_user_documents_admin_page() {
    // Get all users for dropdown
    $users = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );

    // Get selected user from query parameter
    $selected_user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;

    // Enqueue Select2 for better user selection
    echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
    echo '<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

    // User selection form
    echo '<h1>View User Documents</h1>';
    echo '<form method="GET" action="">';
    echo '<input type="hidden" name="page" value="user-documents" />';
    echo '<label for="user_id">Select User:</label>';
    echo '<select id="user_id" name="user_id" style="width: 300px;">';
    echo '<option value="">-- Select User --</option>';
    
    foreach ( $users as $user ) {
        $selected = selected( $selected_user_id, $user->ID, false );
        echo '<option value="' . esc_attr( $user->ID ) . '" ' . $selected . '>' . 
             esc_html( $user->display_name ) . ' (ID: ' . esc_html( $user->ID ) . ')</option>';
    }
    
    echo '</select>';
    echo '<button type="submit" class="button button-primary">View Documents</button>';
    echo '</form>';

    // Initialize Select2
    echo '<script>jQuery(function($){ $("#user_id").select2(); });</script>';

    // Display documents if user selected
    if ( $selected_user_id ) {
        $user = get_user_by( 'ID', $selected_user_id );
        
        // Section 29 Documents
        $section_29_folder = WP_CONTENT_DIR . "/uploads/user-documents/{$selected_user_id}/section-29/";
        
        if ( ! is_dir( $section_29_folder ) ) {
            echo '<hr><p><em>No Section 29 documents found for ' . esc_html( $user->display_name ) . '.</em></p>';
        } else {
            echo '<hr><h2>Section 29 documents for ' . esc_html( $user->display_name ) . '</h2>';
            
            foreach ( scandir( $section_29_folder ) as $month ) {
                if ( in_array( $month, array( '.', '..' ), true ) ) {
                    continue;
                }
                
                $month_label = ucwords( str_replace( '-', ' ', $month ) );
                echo '<strong>' . esc_html( $month_label ) . '</strong><ul>';
                
                $month_path = "{$section_29_folder}/{$month}";
                foreach ( scandir( $month_path ) as $file ) {
                    if ( in_array( $file, array( '.', '..' ), true ) ) {
                        continue;
                    }

                    $link = site_url( "/section-29-data?user={$selected_user_id}&month={$month}&file={$file}" );
                    echo '<li><a href="' . esc_url( $link ) . '" target="_blank">' . esc_html( $file ) . '</a></li>';
                }
                
                echo '</ul>';
            }
        }

        // Pharmacy Licence
        $pharmacy_folder = WP_CONTENT_DIR . "/uploads/user-documents/{$selected_user_id}/pharmacy-licence/";
        
        if ( ! is_dir( $pharmacy_folder ) ) {
            echo '<hr><p><em>No pharmacy licence found for ' . esc_html( $user->display_name ) . '.</em></p>';
        } else {
            $license_expiry = get_user_meta( $selected_user_id, 'pharmacy_licence_expiry', true );
            
            echo '<hr><h2>Pharmacy Licence of ' . esc_html( $user->display_name ) . '</h2>';
            
            $link = site_url( "/pharmacy-licence?uid={$selected_user_id}" );
            echo '<p><strong>Pharmacy Licence: </strong><a href="' . esc_url( $link ) . '" target="_blank">View Licence</a></p>';
            
            if ( $license_expiry ) {
                $license_expiry = date( 'd/m/Y', strtotime( $license_expiry ) );
                echo '<p><strong>Licence Expiry Date: </strong>' . esc_html( $license_expiry ) . '</p>';    
            }
        }
        
        return;
    }
}



// ============================================================================
// DIVI SEARCH OVERRIDES
// ============================================================================

/**
 * Replace Divi's default search handler
 * 
 * Removes Divi's built-in search query hook and registers the custom
 * search logic used for role-aware product and education searches.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_remove_default_et_pb_custom_search() {
  remove_action( 'pre_get_posts', 'et_pb_custom_search' );
  add_action( 'pre_get_posts', 'nubu_custom_search' );
}
add_action( 'wp_loaded', 'nubu_remove_default_et_pb_custom_search' );

/**
 * Customize Divi and public search queries
 * 
 * Extends Divi search results for logged-in trade users and hides
 * restricted post types from public visitors.
 *
 * @since 1.0.0
 * @param WP_Query|false $query Query object being filtered.
 * @return void
 */
function nubu_custom_search( $query = false ) {
 
    if ( is_admin() || ! is_a( $query, 'WP_Query' ) || ! $query->is_search ) {
        return;
    }

    if ( isset( $_GET['et_pb_searchform_submit'] ) ) {
        $postTypes = array();
          
        if ( ! isset($_GET['et_pb_include_posts'] ) && ! isset( $_GET['et_pb_include_pages'] ) ) {
            $postTypes = array( 'post' );
        }

        if ( isset( $_GET['et_pb_include_pages'] ) ) {
            $postTypes = array( 'page' );
        }

        if ( isset( $_GET['et_pb_include_posts'] ) ) {
            $postTypes[] = 'post';
        } 

        /* BEGIN Add custom post types */
        if ( is_user_logged_in() ) {
            if ( current_user_can('trade_account') ) {
                $postTypes[] = 'product';
            }
            $postTypes[] = 'education-resource';
        }
        /* END Add custom post types */

        $query->set( 'post_type', $postTypes );

        if ( ! empty( $_GET['et_pb_search_cat'] ) ) {
            $categories_array = explode( ',', $_GET['et_pb_search_cat'] );
            $query->set( 'category__not_in', $categories_array );
        }

        if ( isset( $_GET['et-posts-count'] ) ) {
            $query->set( 'posts_per_page', (int) $_GET['et-posts-count'] );
        }
    }
	
	 if ( ! is_user_logged_in() && ! is_admin() && $query->is_search ) {
        $post_type = $query->get( 'post_type' );

        if ( $post_type === 'any' || empty( $post_type ) ) {
            // Get all public post types
            $post_types = get_post_types( [ 'public' => true ], 'names' );

            // Exclude 'product'
            unset( $post_types['product'] );
            unset( $post_types['education-resource'] );

            // Set modified post types back to the query
            $query->set( 'post_type', array_values( $post_types ) );
        }
    }
}

// ============================================================================
// PRODUCT TAG SETTINGS
// ============================================================================

/**
 * Render product tag settings page
 * 
 * Admin page for configuring custom product tags appearance
 * (position, shape, colors, etc.)
 *
 * @since 1.0.0
 * @return void
 */
function nubu_render_product_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Custom Tag', 'woocommerce' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'nubu_product_tag_options' );
            do_settings_sections( 'nubu_product_tag_options' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register product tag settings
 * 
 * Registers all settings fields for custom product tags
 * including position, shape, and color options.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_register_custom_tag_settings() {
    register_setting( 'nubu_product_tag_options', 'nubu_product_tag_options', 'nubu_sanitize_options' );
    
    add_settings_section( 'nubu_pt_general_section', '', '', 'nubu_product_tag_options' );
    
    add_settings_field( 'tag_position', __( 'Tag Position', 'woocommerce' ), 'nubu_field_tag_position', 'nubu_product_tag_options', 'nubu_pt_general_section' );
    add_settings_field( 'tag_shape', __( 'Tag Shape', 'woocommerce' ), 'nubu_field_tag_shape', 'nubu_product_tag_options', 'nubu_pt_general_section' );
    add_settings_field( 'tag_bg_color', __( 'Tag Background Color', 'woocommerce' ), 'nubu_field_tag_bg_color', 'nubu_product_tag_options', 'nubu_pt_general_section' );
    add_settings_field( 'tag_text_color', __( 'Tag Text Color', 'woocommerce' ), 'nubu_field_tag_text_color', 'nubu_product_tag_options', 'nubu_pt_general_section' );
}
add_action( 'admin_init', 'nubu_register_custom_tag_settings' );

/**
 * Sanitize product tag options
 * 
 * Validates and sanitizes all product tag settings before saving.
 *
 * @since 1.0.0
 * @param array $input Raw input data from settings form
 * @return array Sanitized settings
 */
function nubu_sanitize_options( $input ) {
    $out = array();
    
    // Validate position (whitelist)
    $out['tag_position'] = in_array( $input['tag_position'] ?? '', array( 'top-left', 'top-right' ), true ) 
        ? $input['tag_position'] 
        : 'top-right';
    
    // Validate shape (whitelist)
    $out['tag_shape'] = in_array( $input['tag_shape'] ?? '', array( 'circle', 'rectangle' ), true ) 
        ? $input['tag_shape'] 
        : 'rectangle';
    
    // Sanitize colors
    $out['tag_bg_color']   = sanitize_hex_color( $input['tag_bg_color'] ?? '' ) ?: '#ff0000';
    $out['tag_text_color'] = sanitize_hex_color( $input['tag_text_color'] ?? '' ) ?: '';

    return $out;
}

/**
 * Render tag position field
 *
 * @since 1.0.0
 * @return void
 */
function nubu_field_tag_position() {
    $opts = get_option( 'nubu_product_tag_options', array() );
    $val  = $opts['tag_position'] ?? 'top-right';
    ?>
    <select name="nubu_product_tag_options[tag_position]">
        <option value="top-left" <?php selected( $val, 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'gh-product-tag' ); ?></option>
        <option value="top-right" <?php selected( $val, 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'gh-product-tag' ); ?></option>
    </select>
    <?php
}

/**
 * Render tag shape field
 *
 * @since 1.0.0
 * @return void
 */
function nubu_field_tag_shape() {
    $opts = get_option( 'nubu_product_tag_options', array() );
    $val  = $opts['tag_shape'] ?? 'rectangle';
    ?>
    <select name="nubu_product_tag_options[tag_shape]">
        <option value="rectangle" <?php selected( $val, 'rectangle' ); ?>><?php esc_html_e( 'Rectangle', 'gh-product-tag' ); ?></option>
        <option value="circle" <?php selected( $val, 'circle' ); ?>><?php esc_html_e( 'Circular', 'gh-product-tag' ); ?></option>
    </select>
    <?php
}

/**
 * Render tag background color field
 *
 * @since 1.0.0
 * @return void
 */
function nubu_field_tag_bg_color() {
    $opts = get_option( 'nubu_product_tag_options', array() );
    $val  = $opts['tag_bg_color'] ?? '#ff0000';
    ?>
    <input type="text" class="nubu-color-field" name="nubu_product_tag_options[tag_bg_color]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * Render tag text color field
 *
 * @since 1.0.0
 * @return void
 */
function nubu_field_tag_text_color() {
    $opts = get_option( 'nubu_product_tag_options', array() );
    $val  = $opts['tag_text_color'] ?? '';
    ?>
    <input type="text" class="nubu-color-field" name="nubu_product_tag_options[tag_text_color]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

// ============================================================================
// REDIRECT FUNCTIONS
// ============================================================================

/**
 * Redirect users based on authentication status
 * 
 * Non-logged-in users: Redirect to login with return URL
 * Logged-in users on login page: Redirect to portal landing
 *
 * @since 1.0.0
 * @return void
 */
function nubu_redirect_to_main_site() {
    // Skip during logout to prevent conflicts
    if ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] ) {
        return;
    }
    
    // Skip AJAX requests
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    
    // Skip cron jobs
    if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
        return;
    }
    
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri  = $_SERVER['REQUEST_URI'] ?? '';
    
    // Remove www prefix for comparison
    $host = str_replace( 'www.', '', $host );
    
    // Only run on order subdomain
    if ( 'order.nubu.co.nz' !== $host ) {
        return;
    }
    
    // Allow admin area
    if ( is_admin() ) {
        return;
    }
    
    // Clean URI for checking (remove query strings)
    $uri_path = strtok( $uri, '?' );
    $uri_path = rtrim( $uri_path, '/' ); // Remove trailing slash for consistent matching
    
    // Allow WordPress core pages
    if ( 
        strpos( $uri, 'wp-login.php' ) !== false ||
        strpos( $uri, 'wp-register.php' ) !== false ||
        strpos( $uri, 'wp-signup.php' ) !== false ||
        strpos( $uri, 'wp-json/' ) !== false ||
        strpos( $uri, 'xmlrpc.php' ) !== false
    ) {
        return;
    }
    
    // NEW: If logged-in user tries to access login page, redirect to portal
    if ( is_user_logged_in() ) {
        // Check if they're on the login page
        if ( $uri_path === '/login' || strpos( $uri_path, '/login/' ) === 0 ) {
			$target = home_url( '/portal-landing/' );

            if ( ! empty( $_REQUEST['redirect_to'] ) ) {
                $target = wp_validate_redirect(
                    wp_unslash( $_REQUEST['redirect_to'] ),
                    $target
                );
            }

            nocache_headers();
            wp_safe_redirect( $target );
			exit;
		}
        // Allow logged-in users to access everything else
        return;
    }
    
    // Pages that non-logged-in users CAN access (login & password recovery)
    $allowed_pages = array(
		'/trade-application-form',
        '/login',
        '/my-account' // WooCommerce login form
    );
    
    foreach ( $allowed_pages as $page ) {
        // Check if URI starts with the allowed page path
        if ( $uri_path === $page || strpos( $uri_path, $page . '/' ) === 0 ) {
            return;
        }
    }
    
    // Allow WooCommerce password recovery endpoints
    $allowed_endpoints = array(
        'lost-password',
        'reset-password'
    );
    
    foreach ( $allowed_endpoints as $endpoint ) {
        if ( strpos( $uri, $endpoint ) !== false ) {
            return;
        }
    }
    
    // NEW: Redirect non-logged-in users to login with return URL
    $current_url = 'https://order.nubu.co.nz' . $uri;
    $login_url   = add_query_arg( 
        'redirect_to', 
        urlencode( $current_url ), 
        'https://order.nubu.co.nz/login/' 
    );
    
    nocache_headers();
    wp_safe_redirect( $login_url );
    exit;
}
// Use 'init' hook with priority 1 to run before auth checks
add_action( 'init', 'nubu_redirect_to_main_site', 1 );

/**
 * Redirect to main site after logout (filter)
 * 
 * Modifies the logout redirect URL to always point to the main site.
 *
 * @since 1.0.0
 * @param string  $redirect_to           The redirect destination URL
 * @param string  $requested_redirect_to The requested redirect destination URL
 * @param WP_User $user                  The WP_User object for the user that's logging out
 * @return string Modified redirect URL
 */
function nubu_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
    return 'https://order.nubu.co.nz/login';
}
add_filter( 'logout_redirect', 'nubu_logout_redirect', 9999, 3 );

/**
 * Redirect to main site after logout (action)
 * 
 * Additional logout redirect using wp_logout action hook.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_redirect_after_logout() {
    wp_redirect( 'https://order.nubu.co.nz/login' );
    exit();
}
add_action( 'wp_logout', 'nubu_redirect_after_logout' );

/**
 * Custom logout redirect for WooCommerce customer-logout endpoint
 * 
 * Handles logout specifically from WooCommerce customer-logout page.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_custom_logout_redirect() {
    // Only on customer logout endpoint
    if ( is_wc_endpoint_url( 'customer-logout' ) ) {
        wp_logout();
        wp_safe_redirect( 'https://order.nubu.co.nz/login' );
        exit();
    }
}
//add_action( 'template_redirect', 'nubu_custom_logout_redirect' );

// ============================================================================
// LOGIN PAGE CUSTOMIZATION
// ============================================================================

/**
 * Customize login page styles
 * 
 * Applies custom branding and styling to WordPress login page
 * including logo, colors, and form styling.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_login_styles() {
    ?>
    <style type="text/css">
        body.login {
            background-color: #cddae2;
        }
        body.login form {
            border: none;
            box-shadow: none;
        }
        /* Reset Password Form Styles */
        #resetpassform .button-primary {
            background-color: #03172c;
            border: none;
            border-radius: 0;
        }
        #resetpassform input[type=text],
        #resetpassform input[type=password] {
            border-radius: 0;
            border: none;
            border-bottom: 2px solid #0c2445;
            background: none;
            margin-bottom: 20px;
        }
        #resetpassform {
            background: none;
        }
        #resetpassform label {
            font-weight: 600 !important;
            color: #03172c;
            font-size: 14px;
            line-height: 20px;
        }
        /* Login Form Styles */
        #loginform .button-primary {
            background-color: #03172c;
            border: none;
            border-radius: 0;
        }
        #loginform input[type=text],
        #loginform input[type=password] {
            border-radius: 0;
            border: none;
            border-bottom: 2px solid #0c2445;
            background: none;
            margin-bottom: 20px;
        }
        #loginform {
            background: none;
        }
        #loginform label {
            font-weight: 600 !important;
            color: #03172c;
            font-size: 14px;
            line-height: 20px;
        }
        /* Lost Password Form Styles */
        #lostpasswordform .button-primary {
            background-color: #03172c;
            border: none;
            border-radius: 0;
        }
        #lostpasswordform input[type=text],
        #lostpasswordform input[type=password] {
            border-radius: 0;
            border: none;
            border-bottom: 2px solid #0c2445;
            background: none;
            margin-bottom: 20px;
        }
        #lostpasswordform {
            background: none;
        }
        #lostpasswordform label {
            font-weight: 600 !important;
            color: #03172c;
            font-size: 14px;
            line-height: 20px;
        }
        /* Custom Logo */
        #login h1 a,
        .login h1 a {
            background-image: url(https://order.nubu.co.nz/wp-content/themes/dmcustom/images/nubu-icon.svg);
            width: 200px;
            height: 100px;
            background-size: 200px 100px;
            background-repeat: no-repeat;
        }
        /* Message Styling */
        #login .message {
            background-color: #03172c;
            border: none;
            color: #fff;
        }
    </style>
    <?php
}
add_action( 'login_enqueue_scripts', 'nubu_login_styles' );

/**
 * Change login username label text
 * 
 * Changes "Username or Email Address" to "Email Address or Username"
 * for better clarity on login page.
 *
 * @since 1.0.0
 * @param string $translated_text Translated text
 * @param string $text            Text to translate
 * @param string $domain          Text domain
 * @return string Modified translated text
 */
function nubu_username_label( $translated_text, $text, $domain ) {
    if ( 'Username or Email Address' === $text ) {
        $translated_text = 'Email Address or Username';
    }
    return $translated_text;
}
add_filter( 'gettext', 'nubu_username_label', 20, 3 );

/**
 * Custom login redirect based on user role and redirect_to parameter
 * 
 * Priority order:
 * 1. If redirect_to parameter exists (from protected page), use that
 * 2. If administrator, go to dashboard
 * 3. Everyone else goes to portal landing page
 *
 * @since 1.0.0
 * @param string  $redirect_to          Default redirect URL
 * @param string  $requested_redirect_to Requested redirect URL
 * @param WP_User $user                 User object
 * @return string Modified redirect URL
 */
function nubu_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( ! $user instanceof WP_User ) {
        return $redirect_to;
    }

    // Priority 1: use redirect_to if present
    if ( ! empty( $requested_redirect_to ) ) {
        $redirect_url = urldecode( $requested_redirect_to );
        $parsed_url   = parse_url( $redirect_url );
        $allowed_host = 'order.nubu.co.nz';

        // Allow relative URLs
        if ( ! isset( $parsed_url['host'] ) ) {
            return $redirect_url;
        }

        // Allow your domain
        if ( $parsed_url['host'] === $allowed_host || $parsed_url['host'] === 'www.' . $allowed_host ) {
            return $redirect_url;
        }
    }

    // Priority 2: admins go to dashboard
    if ( in_array( 'administrator', (array) $user->roles, true ) ) {
        return admin_url();
    }

    // Priority 3: everyone else goes to portal landing
    return home_url( '/portal-landing/' );
}
add_filter( 'login_redirect', 'nubu_login_redirect', 10, 3 );

// ============================================================================
// ACCESS CONTROL & USER RESTRICTIONS
// ============================================================================

/**
 * Block public and pending users from restricted content
 * 
 * Restricts access to products, education resources, and other
 * portal content for non-logged-in users and pending approvals.
 * Currently disabled via commented add_action.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_block_public_and_pending() {
    $page_should_be_limited = false;
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Check if current page should be restricted
    if ( 
        is_singular( 'education-resource' ) || 
        is_post_type_archive( 'education-resource' ) ||
        is_singular( 'webinar' ) || 
        is_post_type_archive( 'webinar' ) ||
        is_singular( 'product' ) || 
        is_post_type_archive( 'product' ) ||
        is_page_template( 'archive-product.php' ) ||
        is_page_template( 'flexible-portal-page.php' ) ||
        is_page_template( 'taxonomy-brand.php' ) ||
        is_page_template( 'taxonomy-strength.php' ) ||
        is_page_template( 'taxonomy-catalog.php' ) ||
        is_page_template( 'single-product.php' ) ||
        is_page_template( 'single-education-resource.php' ) ||
        is_page_template( 'taxonomy-product_cat.php' ) ||
        is_tax( 'brand' ) ||
        is_tax( 'catalog' ) ||
        is_tax( 'strength' ) ||
        strpos( $current_url, '/products/' ) !== false
    ) {
        $page_should_be_limited = true;
    }
    
    $current_user = wp_get_current_user();
    
    // Redirect if access should be limited
    if ( $page_should_be_limited ) {
        // Not logged in or pending approval
        if ( 0 === $current_user->ID || in_array( 'pending_approval', $current_user->roles, true ) ) {
            wp_redirect( get_bloginfo( 'url' ) . '/healthcare-professionals/' );
            exit;
        }
        
        // Check product-specific visibility
        if ( is_product() && ! nubu_is_product_visible_for_user() ) {
            wp_redirect( get_bloginfo( 'url' ) );
            exit;
        }
    }
    
    // Redirect from HCP page if already approved
    if ( strpos( $current_url, '/healthcare-professionals/' ) !== false ) {
        if ( 
            0 !== $current_user->ID && 
            ! in_array( 'pending_approval', $current_user->roles, true ) && 
            ! in_array( 'administrator', $current_user->roles, true ) 
        ) {
            wp_redirect( get_bloginfo( 'url' ) . '/portal-landing/' );
            exit;
        }
    }
    
    // Redirect from trade application if not HCP
    if ( strpos( $current_url, '/hcp-trade-application/' ) !== false ) {
        if ( 
            0 !== $current_user->ID && 
            ! in_array( 'healthcare_professional', $current_user->roles, true ) && 
            ! in_array( 'administrator', $current_user->roles, true ) 
        ) {
            wp_redirect( get_bloginfo( 'url' ) . '/portal-landing/' );
            exit;
        }
    }
}
// add_action( 'template_redirect', 'nubu_block_public_and_pending' );

/**
 * Block cart and checkout for non-trade users
 * 
 * Restricts access to cart and checkout pages to administrators
 * and trade account holders only.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_block_cart_checkout_custom() {
    $allowed_roles = array( 'administrator', 'trade_account' );

    // Only check on cart/checkout pages
    if ( is_cart() || is_checkout() ) {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();

            // Redirect if user doesn't have allowed role
            if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
                wp_safe_redirect( '/portal-landing' );
                exit;
            }
        } else {
            // Guests get redirected to main site
            wp_safe_redirect( 'https://order.nubu.co.nz/login' );
            exit;
        }
    }
}
add_action( 'template_redirect', 'nubu_block_cart_checkout_custom' );

/**
 * Add user role class to body
 * 
 * Adds a CSS class to the body tag for healthcare professionals
 * to enable role-specific styling.
 *
 * @since 1.0.0
 * @param array $classes Existing body classes
 * @return array Modified body classes
 */
function nubu_add_user_role_class_to_body( $classes ) {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        
        if ( ! empty( $current_user->roles ) ) {
            if ( is_array( $current_user->roles ) && in_array( 'healthcare_professional', $current_user->roles, true ) ) {
                $classes[] = 'role-healthcare_professional';
            }
        }
    }

    return $classes;
}
add_filter( 'body_class', 'nubu_add_user_role_class_to_body' );

// ============================================================================
// WOOCOMMERCE CUSTOMIZATIONS
// ============================================================================

/**
 * Remove default WooCommerce loop price display
 *
 * @since 1.0.0
 */
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

/**
 * Add custom query parameter to lost password URL
 * 
 * Adds a 'ref' parameter to the lost password URL for tracking purposes.
 *
 * @since 1.0.0
 * @param string $url Lost password URL
 * @return string Modified URL with ref parameter
 */
function nubu_add_custom_query_to_lostpassword_url( $url ) {
    $url = add_query_arg( 'ref', '', $url );
    return $url;
}
add_filter( 'lostpassword_url', 'nubu_add_custom_query_to_lostpassword_url', 10, 1 );

/**
 * Customize "Add to Cart" message
 * 
 * Changes the default "Added to cart" message to include
 * a link to the custom "Your Order" page.
 *
 * @since 1.0.0
 * @param string $message      Original message HTML
 * @param array  $products     Array of products added to cart
 * @param bool   $show_qty     Whether to show quantity
 * @return string Modified message HTML
 */
function nubu_cart_message( $message, $products, $show_qty ) {
    return sprintf(
        'Added to <a href="%1$s">your order</a>!',
        esc_url( home_url( '/your-order/' ) )
    );
}
add_filter( 'wc_add_to_cart_message_html', 'nubu_cart_message', 10, 3 );

/**
 * Change "Add to Cart" text for backordered products
 * 
 * Updates button text to "Order on backorder" when product is backordered.
 *
 * @since 1.0.0
 * @param string     $text    Original button text
 * @param WC_Product $product Product object
 * @return string Modified button text
 */
function nubu_custom_backorder_button_text( $text, $product ) {
    if ( $product->is_on_backorder() ) {
        $text = 'Order on backorder';
    }
    return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'nubu_custom_backorder_button_text', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'nubu_custom_backorder_button_text', 10, 2 );

/**
 * Display "Back order" text on archive pages for backordered products
 * 
 * Shows "Back order" instead of "Add to cart" on product archives
 * when product is on backorder.
 *
 * @since 1.0.0
 * @param string     $text    Button text
 * @param WC_Product $product Product object
 * @return string Modified button text
 */
add_filter( 'woocommerce_product_add_to_cart_text', function( $text, $product ) {
    if ( is_admin() ) {
        return $text;
    }

    // Only on product archives
    if ( ! ( is_shop() || is_post_type_archive( 'product' ) || is_product_taxonomy() ) ) {
        return $text;
    }

    // Skip variable, grouped, external products
    if ( $product->is_type( array( 'variable', 'grouped', 'external' ) ) ) {
        return $text;
    }

    // Check backorder status
    if ( $product->backorders_allowed() && ( 'onbackorder' === $product->get_stock_status() || $product->is_on_backorder( 1 ) ) ) {
        $text = __( 'Back order', 'your-textdomain' );
    }

    return $text;
}, 20, 2 );



// ============================================================================
// CART TEMPLATE OVERRIDES
// ============================================================================

/**
 * Add coupon form below cart totals
 * 
 * Outputs a WooCommerce coupon field in the cart totals table using
 * the site's custom credit-code wording.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_add_coupon_field_below_totals() {
	if ( wc_coupons_enabled() ) { // Check if coupons are enabled
		?>
		<tr class="coupon-form-row">
			<td colspan="2">
				<form class="nubu_apply_coupon" method="post" style="margin-top: 20px;">
					<p class="form-row">
						<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Credit code', 'woocommerce' ); ?>" id="coupon_code_below_totals" value="" />
					</p>
					<p class="form-row">
						<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
					</p>
				</form>
			</td>
		</tr>
		<?php
	}
}
add_action( 'woocommerce_cart_totals_after_order_total', 'nubu_add_coupon_field_below_totals' );

/**
 * Override WooCommerce quantity input template
 * 
 * Loads the child theme quantity input template so custom quantity
 * behaviour and markup are preserved.
 *
 * @since 1.0.0
 * @param string $template      Located template path.
 * @param string $template_name Requested template name.
 * @param array  $args          Template arguments.
 * @param string $template_path Template path override.
 * @param string $default_path  Default WooCommerce template path.
 * @return string Template path.
 */
function nubu_wc_get_template( $template, $template_name, $args, $template_path, $default_path ) {
    if ( 'global/quantity-input.php' === $template_name ) {
        $template = get_stylesheet_directory() . '/woocommerce/global/quantity-input.php';
        return $template;
    }
    return $template;
}
add_filter( 'wc_get_template', 'nubu_wc_get_template', 9999, 5 );

/**
 * Change order status to completed for Payment Express orders
 * 
 * Automatically marks Payment Express orders as completed
 * when they reach processing status.
 *
 * @since 1.0.0
 * @param int $order_id Order ID
 * @return void
 */
function nubu_change_order_status_to_paid( $order_id ) {
    $order = wc_get_order( $order_id );
    
    if ( $order && 'processing' === $order->get_status() && 'payment_express' === $order->get_payment_method() ) {
        $order->update_status( 'completed', 'Order status updated to completed because payment is done.' );
    }
}
add_action( 'woocommerce_order_status_processing', 'nubu_change_order_status_to_paid', 10, 1 );

/**
 * Change order status for cheque payments
 * 
 * Moves cheque payment orders from on-hold to processing status.
 *
 * @since 1.0.0
 * @param int $order_id Order ID
 * @return void
 */
function nubu_change_order_status_on_cheque_payment( $order_id ) {
    $order = wc_get_order( $order_id );

    if ( $order && 'on-hold' === $order->get_status() && 'cheque' === $order->get_payment_method() ) {
        $order->update_status( 'processing', 'Order status changed to processing after cheque payment method.' );
    }
}
add_action( 'woocommerce_order_status_on-hold', 'nubu_change_order_status_on_cheque_payment' );



// ============================================================================
// PRODUCT CATALOG FILTERING
// ============================================================================

/**
 * Render product filter controls
 * 
 * Builds brand, category, and strength dropdown filters for product
 * listing views based on the requested filter types.
 *
 * @since 1.0.0
 * @param array $filters Filter types to render.
 * @return string Filter controls HTML.
 */
function nubu_product_filters_html( $filters = array() ) {
    $brand_options      = [];
    $category_options   = [];
    $strength_options   = [];
    $filter             = '';
    
    if ( in_array( 'brand', $filters ) ) {
        $brands = get_terms('brand');
        if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
            foreach( $brands as $brand ) {
                $brand_options[] = [
                    'name' => $brand->name,
                    'id' => $brand->term_id
                ];
            }
        }
    }
    
    if ( in_array( 'category', $filters ) ) {
        $categories = get_terms('product_cat');
        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            foreach( $categories as $category ) {
                if ( 'public-product' === $category->slug ) {
                    continue;
                }
                $category_options[] = [
                    'name' => $category->name,
                    'id' => $category->term_id
                ];
            }
        }
    }
    
    if ( in_array( 'strength', $filters) ) {
        $strengths = get_terms( array(
    'taxonomy'   => 'strength',
    'hide_empty' => false,
) );
        if ( ! is_wp_error( $strengths ) && ! empty( $strengths ) ) {
            foreach( $strengths as $strength ) {
                $strength_options[] = [
                    'name' => $strength->name,
                    'id' => $strength->term_id
                ];
            }
        }
    }
    
    ob_start();
    ?>
    <div class="product-filters-wrap">
        <!-- Brand filter -->
        <?php if ( ! empty( $brand_options ) ) { ?>
            <div class="filter">
                <label for="brand-filter">Brand</label>
                <select name="brand-filter" id="brand-filter" class="product-filter" data-taxonomy="brand"> 
                    <option value="-1">All</option>
                    <?php foreach ($brand_options as $option) { ?>
                        <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
    
        <!-- Category filter -->
        <?php if (!empty($category_options)) { ?>
            <div class="filter">
                <label for="category-filter">Category</label>
                <select name="category-filter" id="category-filter" class="product-filter" data-taxonomy="product_cat"> 
                    <option value="-1">All</option>
                    <?php foreach ($category_options as $option) { ?>
                        <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
    
        <!-- Strength filter -->
        <?php if (!empty($strength_options)) { ?>
        <div class="filter">
            <label for="strength-filter">THC/CBD</label>
            <select name="strength-filter" id="strength-filter" class="product-filter" data-taxonomy="strength"> 
                <option value="-1">All</option>
                <?php foreach ($strength_options as $option) { ?>
                        <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
                    <?php } ?>            
            </select>
        </div>
        <?php } ?>
    </div>
    <?php
    $filter = ob_get_clean();
    
    return $filter;
}


/**
 * Build catalog tax query for the current user
 * 
 * Restricts product queries to catalogs assigned to the logged-in user.
 *
 * @since 1.0.0
 * @return array Catalog taxonomy query arguments.
 */
function nubu_get_catalog_tax_query() {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $user_catalogs = carbon_get_user_meta( $current_user->ID, 'catalog' );

        if ( empty( $user_catalogs ) ) {
            return [];
        }
        
        $catalogs = array_column( $user_catalogs, 'id' );
        
        if ( is_tax('catalog') ) {
            $term = get_queried_object();
            if ( in_array( $term->term_id, $catalogs ) ) {
                $catalog_query = array(
                    'taxonomy' => 'catalog',
                    'field' => 'term_id',
                    'terms' => intval( $term->term_id )
                );
            } else {
                $catalog_query = [];
            }
        } else {
            $catalog_query = array(
                'taxonomy' => 'catalog',
                'field' => 'term_id',
                'terms' => $catalogs
            );
        }
        /*
        foreach( $user_catalogs as $catalog ) {
            array_push( $catalog_query, [
                'taxonomy' => 'catalog',
                'field' => 'term_id',
                'terms' => $catalogs
            ]);
        }
        */
        
        return $catalog_query;
    }
    return [];
}

/**
 * Apply catalog restrictions to search results
 * 
 * Limits front-end search results to products in the current user's
 * assigned catalogs, or hides results when no catalogs are assigned.
 *
 * @since 1.0.0
 * @param WP_Query $query Main search query.
 * @return void
 */
function nubu_modify_search_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_search() ) {
        // Get the current tax_query (if any)
        $current_tax_query = $query->get( 'tax_query' );
        
        $catalog_query = nubu_get_catalog_tax_query();
        if ( empty( $catalog_query ) ) {
            $query->set('posts_per_page', 0);
        } else {

            $new_tax_query = array(
                $catalog_query
            );

            // If there was a previous tax_query, merge it with the new one
            if ( ! empty( $current_tax_query ) ) {
                $merged_tax_query = array_merge( $current_tax_query, $new_tax_query );
            } else {
                $merged_tax_query = $new_tax_query; // If no previous tax_query, just use the new one
            }

            // Set the new merged tax_query
            $query->set( 'tax_query', $merged_tax_query );  
        }
    }
}
add_action( 'pre_get_posts', 'nubu_modify_search_query' );

/**
 * Apply catalog restrictions to WooCommerce shortcode queries
 * 
 * Adds user catalog filtering to shop/product shortcode queries and
 * preserves brand taxonomy filtering when browsing brand archives.
 *
 * @since 1.0.0
 * @param array $query_args WooCommerce product query arguments.
 * @return array Modified query arguments.
 */
function nubu_alter_shop_query( $query_args ) {
    
    $catalog_query = nubu_get_catalog_tax_query();
    if ( empty( $catalog_query ) ) {
        $query_args['posts_per_page'] = 0;
    } else {
        if ( is_array( $query_args['tax_query'] ) ) {
            $query_args['tax_query'][] = $catalog_query;
        } else {
            $query_args['tax_query'] = array( $catalog_query );
        }
    }
    
    if ( is_tax( array('brand') ) ) {
        $term = get_queried_object();
        $tax_query = array(
            'taxonomy' => sanitize_text_field( $term->taxonomy ),
            'field' => 'term_id',
            'terms' => intval( $term->term_id )
        );
        if ( is_array( $query_args['tax_query'] ) ) {
            $query_args['tax_query'][] = $tax_query;
            $query_args['tax_query']['relation'] = 'AND';
        } else {
            $query_args['tax_query'] = array( $tax_query );
        }
    }

    return $query_args;
}
add_filter( 'woocommerce_shortcode_products_query', 'nubu_alter_shop_query' );

/**
 * Apply catalog restrictions to related products
 * 
 * Limits related product output to items within the current user's
 * assigned catalogs.
 *
 * @since 1.0.0
 * @param array $query_args Related products query arguments.
 * @return array Modified query arguments.
 */
function nubu_woocommerce_output_related_products_args( $query_args ) {
    $catalog_query = nubu_get_catalog_tax_query();
    if ( empty( $catalog_query ) ) {
        $query_args['posts_per_page'] = 0;
    } else {
        if ( is_array( $query_args['tax_query'] ) ) {
            $query_args['tax_query'][] = $catalog_query;
        } else {
            $query_args['tax_query'] = array( $catalog_query );
        }
    }
    
    return $query_args;
}
add_filter( 'woocommerce_output_related_products_args', 'nubu_woocommerce_output_related_products_args' );

/**
 * Get minimum product quantities for the current user
 * 
 * Reads user-specific minimum quantity rules from Carbon Fields metadata.
 *
 * @since 1.0.0
 * @return array Product ID to minimum quantity map.
 */
function nubu_get_current_user_quantities() {
    $min_quantities = [];
    if ( is_user_logged_in() ) {
        $current_user   = wp_get_current_user();
        $data           = carbon_get_user_meta( $current_user->ID, 'products_min_quantity' );
        if ( ! empty( $data ) ) {
            foreach ($data as $item) {
                foreach ($item['product_id'] as $product) {
                    $min_quantities[$product['id']] = $item['quantity'];
                }
            }
        }
    }
    return $min_quantities;
}

/**
 * Set minimum quantity on product quantity inputs
 * 
 * Applies per-user minimum quantities to WooCommerce quantity fields.
 *
 * @since 1.0.0
 * @param array      $args    Quantity input arguments.
 * @param WC_Product $product Product object.
 * @return array Modified quantity input arguments.
 */
function nubu_set_min_quantity_per_product($args, $product) {
    
    if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) || current_user_can( 'customer' ) ) ) {
        $quantities = nubu_get_current_user_quantities();
        $product_id = $product->get_id();

        if ( isset( $quantities[$product_id] ) ) {
            $min = intval( $quantities[$product_id] );
            //$args['input_value'] = $min; // default value shown
            $args['min_value'] = $min;   // minimum allowed
            $args['data-min'] = $min;
        }
    }

    return $args;
}
add_filter('woocommerce_quantity_input_args', 'nubu_set_min_quantity_per_product', 20, 2);

/**
 * Set default loop add-to-cart quantity
 * 
 * Uses the user's configured minimum quantity as the default quantity
 * on product loop add-to-cart controls.
 *
 * @since 1.0.0
 * @param array      $args    Add-to-cart arguments.
 * @param WC_Product $product Product object.
 * @return array Modified add-to-cart arguments.
 */
function nubu_woocommerce_loop_add_to_cart_args( $args, $product ) {
    if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) || current_user_can( 'customer' ) ) ) {
        $quantities = nubu_get_current_user_quantities();
        $product_id = $product->get_id();
        if (isset($quantities[$product_id])) {
            $min_qty = intval( $quantities[$product_id] );
            $args['quantity'] = $min_qty;
        }
    }
    return $args;
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'nubu_woocommerce_loop_add_to_cart_args', 10, 2 );

/**
 * Enforce minimum cart item quantities
 * 
 * Raises cart quantities to the current user's configured minimums
 * before WooCommerce recalculates totals.
 *
 * @since 1.0.0
 * @param WC_Cart $cart WooCommerce cart object.
 * @return void
 */
function nubu_set_cart_item_min_qty($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

   if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) || current_user_can( 'customer' ) ) ) {
       $quantities = nubu_get_current_user_quantities();
       foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
           $product_id = $cart_item['product_id'];

           if (isset($quantities[$product_id])) {
               $min_qty = intval( $quantities[$product_id] );
               if ($cart_item['quantity'] < $min_qty) {
                   $cart_item['quantity'] = $min_qty;
               }
           }
       }
   }
}
add_action('woocommerce_before_calculate_totals', 'nubu_set_cart_item_min_qty');

/**
 * Add minimum quantity data to cart quantity inputs
 * 
 * Injects a data-min attribute into cart quantity markup for products
 * with user-specific minimum quantity rules.
 *
 * @since 1.0.0
 * @param string $product_quantity Quantity input HTML.
 * @param string $cart_item_key    Cart item key.
 * @param array  $cart_item        Cart item data.
 * @return string Modified quantity input HTML.
 */
function nubu_woocommerce_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
    if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) || current_user_can( 'customer' ) ) ) {
        $quantities = nubu_get_current_user_quantities();
        $product_id = $cart_item['product_id'];
        if (isset($quantities[$product_id])) {
            $min_qty = intval( $quantities[$product_id] );
            $product_quantity = str_replace('<input', '<input data-min="' . esc_attr($min_qty) . '"', $product_quantity);
        }
    }
    return  $product_quantity;
}
add_filter( 'woocommerce_cart_item_quantity', 'nubu_woocommerce_cart_item_quantity', 10, 3 );

/**
 * Limit cheque payment gateway to trade accounts
 * 
 * Hides the cheque payment method for non-trade account users.
 *
 * @since 1.0.0
 * @param array $available_gateways Available payment gateways
 * @return array Modified gateways array
 */
function nubu_show_cheque_payment_gateway_for_trade_account_users( $available_gateways ) {
    if ( is_user_logged_in() && ! current_user_can( 'trade_account' ) ) {
        if ( isset( $available_gateways['cheque'] ) ) {
            unset( $available_gateways['cheque'] );
        }
    }

    return $available_gateways;
}
// add_filter( 'woocommerce_available_payment_gateways', 'nubu_show_cheque_payment_gateway_for_trade_account_users' );

/**
 * Truncate checkout name fields for Verifone
 * 
 * Limits first and last name fields to 22 characters maximum
 * when using Verifone payment gateway (API requirement).
 *
 * @since 1.0.0
 * @param array $data Checkout posted data
 * @return array Modified checkout data
 */
function nubu_woocommerce_checkout_posted_data( $data ) {
    // Only for Verifone payment method
    if ( empty( $data['payment_method'] ) || 'verifone_hosted_cart' !== $data['payment_method'] ) {
        return $data;
    }
    
    $fields = array(
        'billing_first_name',
        'shipping_first_name',
        'billing_last_name',
        'shipping_last_name'
    );

    foreach ( $fields as $field ) {
        if ( ! empty( $data[ $field ] ) ) {
            // Unicode-safe truncation to 22 characters
            $data[ $field ] = mb_substr( $data[ $field ], 0, 22 );
        }
    }

    return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'nubu_woocommerce_checkout_posted_data' );

// Continue in next part...
// AJAX HANDLERS
// ============================================================================

/**
 * AJAX handler: Filter products
 * 
 * Handles frontend AJAX requests for product filtering by taxonomy.
 * Modifies WooCommerce shortcode query based on selected filters.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_alter_products() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'nubupharma-nonce' ) ) {
        return;
    }
    
    $filters = $_POST['filters'];
    
    if ( ! empty( $filters ) ) {
        $tax_query = array();
        
        foreach ( $filters as $taxonomy => $term_id ) {
            $args = array(
                'taxonomy' => sanitize_text_field( $taxonomy ),
                'field'    => 'id',
                'terms'    => intval( $term_id ),
            );
            array_push( $tax_query, $args );
        }
        
        // Modify WooCommerce query
        add_filter( 'woocommerce_shortcode_products_query', function( $query_args, $atts, $type ) {
            global $tax_query;
            if ( ! empty( $query_args['tax_query'] ) ) {
                $query_args['tax_query'] = array_merge( $query_args['tax_query'], $tax_query );
            }
            return $query_args;
        }, 10, 3 );
    }
}
add_action( 'wp_ajax_nubu_alter_products', 'nubu_alter_products' );
add_action( 'wp_ajax_nopriv_nubu_alter_products', 'nubu_alter_products' );

/**
 * AJAX handler: Send prescription data to Google Sheets
 * 
 * Processes prescription form submissions and sends data to
 * Google Sheets via Google Sheets API for record keeping.
 *
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object
 * @return void
 */
function nubu_send_prescription_data() {
	$form_type = ! empty( $_POST['form_type'] )
        ? sanitize_key( wp_unslash( $_POST['form_type'] ) )
        : 'optional';

    if ( ! in_array( $form_type, array( 'optional', 'required' ), true ) ) {
        wp_send_json_error( 'Invalid form type' );
    }
	
	$single = ! empty( $_POST['single'] )
        ? sanitize_key( wp_unslash( $_POST['single'] ) )
        : 'no';
	
    $patients = $_POST['patients'];
    $patients = array_map( 'array_values', $patients );
    
    if ( empty( $patients ) || ! is_array( $patients ) ) {
        return;
    }
    
    // Handle regular prescription form
    if ( 'optional' === $form_type ) {
        $order_number  	= $patients[0][1];
        $order_number  	= str_replace( 'NUBU-', '', $order_number );
        $order         	= wc_get_order( $order_number );
        $product_names  = array();
		$product_skus 	= array();
		$product_quants = array();

        // Get product names and SKUs from order
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $terms   = get_the_terms( $product->get_id(), 'product_cat' );
            $product_categories = array();
            
            if ( $terms && ! is_wp_error( $terms ) ) {
                $product_categories = array_column( $terms, 'slug' );
            }
            
            // Skip bundle products
            if ( 'woosb' === $product->get_type() ) {
                continue;
            }
            
            // Skip CSC required products
            if ( carbon_get_post_meta( $product->get_id(), 'section_29_required' ) ) {
                continue;
            }
            
            // Only include specific categories
            if ( in_array( 'flower', $product_categories, true ) || 
                 in_array( 'oils', $product_categories, true ) || 
                 in_array( 'topicals', $product_categories, true ) ) {
                $product_names[] = $product->get_name();
                $product_skus[]  = $product->get_sku();
				$product_quants[] = $item->get_quantity();
            }  else if ( 'yes' === $single ) {
				$product_names[] = $product->get_name(); // new
				$product_skus[] = $product->get_sku(); // new
				$product_quants[] = $item->get_quantity(); // new
			}
        }
		
		if ( 'no' === $single ) {
			// Add SKUs to patient data
			foreach ( $patients as $key => $patient ) {
				$name    = $patient[4]; // product name from patients
				$matches = array_keys( $product_names, $name, true );

				$before = array_slice( $patient, 0, 5, true );
				$after  = array_slice( $patient, 5, null, true );

				if ( ! empty( $matches ) ) {
					$sku = $product_skus[ $matches[0] ];
					$patients[ $key ] = array_merge( $before, array( $sku ), $after );
				} else {
					$patients[ $key ] = array_merge( $before, array( '' ), $after );
				}
			}

			// Add missing products with empty data
			$selected_products = array_column( $patients, 4 );
			$selected_products = array_unique( $selected_products );

			$products   = array();
			$final_skus = array();

			foreach ( $product_names as $i => $name ) {
				if ( ! in_array( $name, $selected_products, true ) ) {
					$products[]   = $name;
					$final_skus[] = $product_skus[ $i ];
				}
			}

			foreach ( $products as $index => $product ) {
				$new_patient = $patients[0];
				$new_patient[4] = $product;

				// Insert SKU at index 5
				array_splice( $new_patient, 5, 0, $final_skus[ $index ] );

				// Clear all values except indices 0, 1, 4, and 5
				foreach ( $new_patient as $key => &$value ) {
					if ( $key !== 0 && $key !== 1 && $key !== 4 && $key !== 5 ) {
						$value = '';
					}
				}
				unset( $value );

				$patients[] = $new_patient;
			}
		} else {
			foreach ( $product_names as $index => $product ) {
				$new_patient = $patients[0]; // Clone the current sub-array
				$new_patient[4] = $product; // Set the 4th index to the unique value

				// Insert SKU at index 5 (shifts everything after 5)
				array_splice( $new_patient, 5, 0, $product_skus[$index] );
				$new_patient[6] = $product_quants[$index];

				// Append the new item to the original array
				$patients[] = $new_patient;
			}
			array_shift($patients);
		}
    } else if ( 'required' === $form_type ) { // Handle CSC required form
        $product_id  = ! empty( $_POST['product_id'] ) ? intval( wp_unslash( $_POST['product_id'] ) ) : 0;
        $product     = wc_get_product( $product_id );
        $product_sku = $product->get_sku();
        
        foreach ( $patients as $key => $patient ) {
            $before = array_slice( $patient, 0, 6, true );
            $after  = array_slice( $patient, 6, null, true );
            $patients[ $key ] = array_merge( $before, array( $product_sku ), $after );
        }
    }
    
    // Send to Google Sheets
    require_once 'google/vendor/autoload.php';

    $credentials = __DIR__ . '/google/credentials.json';
    $client      = new Google_Client();
    
    $client->setAuthConfig( $credentials ); 
    $client->setAccessType( 'offline' );
    $client->setScopes( array( Google_Service_Sheets::SPREADSHEETS ) );

    $service = new Google_Service_Sheets( $client );

    $body = new Google_Service_Sheets_ValueRange( array(
        'values' => $patients
    ) );

    $params = array(
        'valueInputOption' => 'RAW'
    );
    
    // Different sheets for different form types
    if ( 'optional' === $form_type ) {
        $result = $service->spreadsheets_values->append( '1BUOU88FJUxhbKf0O28Bibj3e3sVPrVAa-vg9D9S7avY', 'Sheet1!A1:M1', $body, $params );
    } else if ( 'required' === $form_type ) {
        $result = $service->spreadsheets_values->append( '1BUOU88FJUxhbKf0O28Bibj3e3sVPrVAa-vg9D9S7avY', 'CSCProducts!A1:M1', $body, $params );
    }
}
add_action( 'wp_ajax_nubu_send_prescription_data', 'nubu_send_prescription_data' );
add_action( 'wp_ajax_nopriv_nubu_send_prescription_data', 'nubu_send_prescription_data' );

/**
 * AJAX handler: Send order data to Google Sheets
 * 
 * Sends basic order information to Google Sheets when user
 * indicates they don't have Section 29 data available.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_send_order_data() {
    $order_id = absint( $_POST['order_id'] );
    $order    = wc_get_order( $order_id );
    
    $order_date           = $order->get_date_created();
    $order_number         = $order->get_order_number();
    $formatted_order_date = $order_date ? $order_date->date( 'Y-m-d' ) : 'No date available';
    
    $first_name = $order->get_billing_first_name();
    $last_name  = $order->get_billing_last_name();
    
    $product_names = array();
    $product_skus  = array();
    
    foreach ( $order->get_items() as $item_id => $item ) {
        $product = $item->get_product();
        $terms   = get_the_terms( $product->get_id(), 'product_cat' );
        $product_categories = array();
        
        if ( 'woosb' === $product->get_type() ) {
            continue;
        }
        
        if ( $terms && ! is_wp_error( $terms ) ) {
            $product_categories = array_column( $terms, 'slug' );
        }
        
        if ( carbon_get_post_meta( $product->get_id(), 'section_29_required' ) ) {
            continue;
        }
        
        if ( in_array( 'flower', $product_categories, true ) || 
             in_array( 'oils', $product_categories, true ) || 
             in_array( 'topicals', $product_categories, true ) ) {
            $product_names[] = $product->get_name();
            $product_skus[]  = $product->get_sku();
        }
    }
    
    $current_user        = wp_get_current_user();
    $registration_clinic = $current_user->_registration_clinic;
    
    $data = array();
    
    foreach ( $product_names as $key => $product_name ) {
        $data[] = array(
            $order_number,
            $formatted_order_date,
            $first_name,
            $last_name,
            $product_name,
            $registration_clinic,
            isset( $product_skus[ $key ] ) ? $product_skus[ $key ] : ''
        );
    }
    
    require_once 'google/vendor/autoload.php';

    $credentials = __DIR__ . '/google/credentials.json';
    $client      = new Google_Client();

    $client->setAuthConfig( $credentials ); 
    $client->setAccessType( 'offline' );
    $client->setScopes( array( Google_Service_Sheets::SPREADSHEETS ) );

    $service = new Google_Service_Sheets( $client );
    
    $body = new Google_Service_Sheets_ValueRange( array(
        'values' => $data
    ) );

    $params = array(
        'valueInputOption' => 'RAW'
    );

    $result = $service->spreadsheets_values->append( '1BUOU88FJUxhbKf0O28Bibj3e3sVPrVAa-vg9D9S7avY', 'Sheet2!A1:G1', $body, $params );
}
add_action( 'wp_ajax_nubu_send_order_data', 'nubu_send_order_data' );
add_action( 'wp_ajax_nopriv_nubu_send_order_data', 'nubu_send_order_data' );

/**
 * AJAX handler: Mark all announcements as read
 * 
 * Updates user meta to mark all announcements as read
 * based on current timestamp.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_mark_all_announcement_as_read() {
    check_ajax_referer( 'nubupharma-nonce', 'nonce' );
    $user_id = get_current_user_id();
    
    if ( $user_id ) {
        update_user_meta( $user_id, 'nubu_announcement_last_read', current_time( 'timestamp' ) );
        wp_send_json_success();
    }

    wp_send_json_error();
}
add_action( 'wp_ajax_nubu_mark_all_announcement_as_read', 'nubu_mark_all_announcement_as_read' );

// ============================================================================
// CRON JOBS & SCHEDULED TASKS
// ============================================================================

/**
 * Add custom cron intervals
 * 
 * Registers a 15-minute cron interval for frequent sync operations.
 *
 * @since 1.0.0
 * @param array $schedules Existing schedules
 * @return array Modified schedules array
 */
function nubu_add_cron_intervals( $schedules ) {
    $schedules['fifteen_minute'] = array(
        'interval' => 900,
        'display'  => __( 'Every 15 Minutes' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'nubu_add_cron_intervals' );

/**
 * Schedule Unleashed sync cron job
 * 
 * Schedules recurring product sync with Unleashed API
 * every 15 minutes if not already scheduled.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_schedule_unleashed_sync() {
    if ( ! wp_next_scheduled( 'nubu_do_sync_unleashed' ) ) {
        wp_schedule_event( time(), 'fifteen_minute', 'nubu_do_sync_unleashed' );
    }
}
add_action( 'wp', 'nubu_schedule_unleashed_sync' );

/**
 * Execute Unleashed product sync
 * 
 * Runs the actual sync process to pull product data, prices,
 * and stock levels from Unleashed API.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_sync_unleashed() {
    $unleashedApi = new UnleashedApi();
    $sync_process = new SyncProcess(
        $unleashedApi->get_products(), 
        $unleashedApi->get_product_prices(), 
        $unleashedApi->get_stock()
    );
    $sync_process->run();
}
add_action( 'nubu_do_sync_unleashed', 'nubu_sync_unleashed' );

// ============================================================================
// SHORTCODES
// ============================================================================

/**
 * Trade banner shortcode
 * 
 * Displays trade account application banner if user
 * doesn't have Unleashed customer GUID assigned.
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Banner HTML or empty string
 */
function nubu_trade_banner( $atts ) {
    if ( is_user_logged_in() ) {
        $current_user            = wp_get_current_user();
        $unleashed_customer_guid = $current_user->_unleashed_customer_guid;
        
        if ( ! $unleashed_customer_guid ) {
            return do_shortcode( get_the_content( null, null, '1861' ) );   
        }
    }
    return '';
}
add_shortcode( 'trade_banner', 'nubu_trade_banner' );

/**
 * Product category link shortcode
 * 
 * Displays "Go back to [Category]" link on single product pages.
 *
 * @since 1.0.0
 * @return string Category link HTML
 */
function nubu_display_product_category_link() {
    if ( class_exists( 'WooCommerce' ) && is_product() ) {
        global $post;
        $terms = wp_get_post_terms( $post->ID, 'product_cat' );
        
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $category_links = '';
            foreach ( $terms as $term ) {
                $category_name = $term->name;
                $category_link = get_term_link( $term );
                $category_links .= '<a href="' . esc_url( $category_link ) . '" class="nubu-product-category-link">← Go back to ' . esc_html( $category_name ) . '</a>';
            }
            return $category_links;
        } 
    }
    return '';
}
add_shortcode( 'nubu_product_category_link', 'nubu_display_product_category_link' );

/**
 * Register product category link shortcode
 * 
 * Registers the legacy init-based shortcode hook for the product
 * category back-link shortcode.
 *
 * @since 1.0.0
 * @return void
 */
function register_nubu_product_category_shortcode() {
    add_shortcode( 'nubu_product_category_link', 'nubu_display_product_category_link' );
}
add_action( 'init', 'register_nubu_product_category_shortcode' );


/**
 * Prescription form shortcode
 * 
 * Displays Section 29 prescription data collection form
 * for specified order ID.
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Form HTML
 */
function nubu_get_prescription_form( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    
    $atts = shortcode_atts( array(
        'order_id' => '',
    ), $atts );
    
    $order_id     = absint( $atts['order_id'] );
    $order        = wc_get_order( $order_id );
    $order_number = $order->get_order_number();
    $product_names = array();
    
    if ( $order ) {
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $terms   = get_the_terms( $product->get_id(), 'product_cat' );
            $product_categories = array();
            
            if ( $terms && ! is_wp_error( $terms ) ) {
                $product_categories = array_column( $terms, 'slug' );
            }
            
            if ( 'woosb' === $product->get_type() ) {
                continue;
            }
            
            if ( carbon_get_post_meta( $product->get_id(), 'section_29_required' ) ) {
                continue;
            }
            
            if ( in_array( 'flower', $product_categories, true ) || 
                 in_array( 'oils', $product_categories, true ) || 
                 in_array( 'topicals', $product_categories, true ) ) {
                $product_names[] = $product->get_name();
            }
        }
    }
    
    $current_user        = wp_get_current_user();
    $registration_clinic = $current_user->_registration_clinic;
    
    if ( ! empty( $product_names ) ) {
        ob_start();
        ?>
        <div class="nubu_prescription_form_wrapper">
            <div class="nubu_prescription_form_confirmation_wrapper">
                <h2>Do you have Section 29 Data for this order?</h2>
                <div class="nubu_prescription_form_confirmation_action_wrapper">
                    <button type="button" class="nubu_yes_s29_data nubu_prescription_form_button"><?php esc_html_e( 'Yes', 'Divi' ); ?></button>
                    <button type="button" class="nubu_no_s29_data nubu_prescription_form_button" data-order_id="<?php echo esc_attr( $order_id ); ?>"><?php esc_html_e( 'No', 'Divi' ); ?></button>
                </div>
            </div>
			<form id="nubu_prescription_form" class="nubu_prescription_form">
                <h2><?php echo esc_html__( 'Prescription Details', 'Divi' ); ?></h2>
                <p>Please use the form below to enter patient Section 29 details for this order. Required fields are marked with an asterisk (*). After filling in each field, you will have the option to add another patient’s information.</p>
<p>If a patient has multiple prescribed items, each must be entered separately. The form will close upon submission, so please ensure all patient details are added before submitting.</p>
                <p>Any missing information at the end of the month will be followed up by the NUBU team.</p>
                <p>For further assistance, contact <a href="mailto:hq@nubu.co.nz">hq@nubu.co.nz</a>.</p>
                <div class="nubu_patient_details_wrapper">
					<div class="nubu_patient_order_type">
						<label>Is this order for a single patient or multiple patients?</label>
						<span class="nubu_number_of_patients_wrapper"><input type="radio" id="nubu_single_patient" name="nubu_number_of_patients" value="single" class="nubu_number_of_patients"><label for="nubu_single_patient">Single Patient</label></span>
						<span class="nubu_number_of_patients_wrapper"><input type="radio" id="nubu_multiple_patient" name="nubu_number_of_patients" value="multiple" class="nubu_number_of_patients"><label for="nubu_multiple_patient">Multiple Patients</label></span>
					</div>
                    <div class="nubu_patient_details" id="nubu_patient_1">
                        <div class="nubu_patient_details_header">
                            <div class="nubu_patient_info">
                                <span class="nubu_patient_name"></span>
                                <span class="nubu_product_name"></span>
                            </div>
                            <div class="nubu_patient_action">
                                <span class="nubu_edit_patient" title="Edit Patient">&#x6c;</span>
                                <span class="nubu_save_patient" title="Save Patient">&#xe0e8;</span>
                                <span class="nubu_remove_patient" title="Remove Patient">&#xe07d;</span>
                            </div>
                        </div>
                        <div class="nubu_patient_details_body">
                            <div class="nubu_field_wrap">
                                <div class="nubu_field_inner_wrap">
                                    <label for="patient_first_name_1"><?php esc_html_e( 'Patient\'s Full Name', 'Divi' ); ?>*</label>
                                    <input type="text" name="patient_first_name[]" id="patient_first_name_1" class="nubu_prescription_form_field nubu_patient_first_name" required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'First Name', 'Divi' ); ?></span>
                                </div>
                                <div class="nubu_field_inner_wrap">
                                    <input type="text" name="patient_last_name[]" id="patient_last_name_1" class="nubu_prescription_form_field nubu_patient_last_name" required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Last Name', 'Divi' ); ?></span>
                                </div>
                            </div>
                            <div class="nubu_field_wrap nubu_full_width_field">
                                <div class="nubu_field_inner_wrap">
                                    <label for="date_prescribed_1"><?php esc_html_e( 'Date Prescribed', 'Divi' ); ?>*</label>
                                    <input type="text" name="date_prescribed[]" id="date_prescribed_1" class="nubu_prescription_form_field nubu_prescription_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" readonly required>
                                    <input type="hidden" name="order_number[]" id="order_number_1" class="nubu_prescription_form_field nubu_order_number" value="<?php echo esc_attr( $order_number ); ?>" readonly required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Date', 'Divi' ); ?></span>
                                </div>
                            </div>
                            <div class="nubu_field_wrap">
                                <div class="nubu_field_inner_wrap">
                                    <label for="product_prescribed_1"><?php esc_html_e( 'Product Dispensing', 'Divi' ); ?>*</label>
                                    <select name="product_prescribed[]" id="product_prescribed_1" class="nubu_prescription_form_field nubu_product_prescribed" required>
                                        <option value=""><?php esc_html_e( 'Please Select', 'Divi' ); ?></option>
                                        <?php foreach( $product_names as $product_name ) {
                                            ?><option value="<?php echo esc_attr( $product_name ); ?>"><?php echo esc_html( $product_name ); ?></option><?php
                                        }
                                        ?>
                                    </select>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Select a product for the patient', 'Divi' ); ?></span>
                                </div>
                                <div class="nubu_field_inner_wrap">
                                    <label for="units_prescribed_1"><?php esc_html_e( 'Units Dispensing', 'Divi' ); ?>*</label>
                                    <input type="number" name="units_prescribed[]" id="units_prescribed_1" class="nubu_prescription_form_field nubu_units_prescribed" required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Select how many units prescribed for the patient', 'Divi' ); ?></span>
                                </div>
                            </div>
                            <div class="nubu_field_wrap">
                                <div class="nubu_field_inner_wrap">
                                    <label for="dispensing_pharmacy_1"><?php esc_html_e( 'Dispensing Pharmacy', 'Divi' ); ?>*</label>
                                    <input type="text" name="dispensing_pharmacy[]" id="dispensing_pharmacy_1" class="nubu_dispensing_pharmacy nubu_prescription_form_field" value="<?php echo esc_attr( $registration_clinic ); ?>" required>
                                </div>
                                <div class="nubu_field_inner_wrap">
                                    <label for="prescribing_doctor_clinic_1"><?php esc_html_e( 'Prescribing Doctor Clinic', 'Divi' ); ?></label>
                                    <input type="text" name="prescribing_doctor_clinic[]" id="prescribing_doctor_clinic_1" class="nubu_prescription_form_field">
                                </div>

                            </div>
                            <div class="nubu_field_wrap">
                                <div class="nubu_field_inner_wrap">
                                    <label for="prescribing_doctor_first_name_1"><?php esc_html_e( 'Doctor\'s Full Name', 'Divi' ); ?>*</label>
                                    <input type="text" name="prescribing_doctor_first_name[]" id="prescribing_doctor_first_name_1" class="nubu_prescription_form_field" required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'First Name', 'Divi' ); ?></span>
                                </div>
                                <div class="nubu_field_inner_wrap">
                                    <input type="text" name="prescribing_doctor_last_name[]" id="prescribing_doctor_last_name_1" class="nubu_prescription_form_field" required>
                                    <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Last Name', 'Divi' ); ?></span>
                                </div>
                            </div>
                            <div class="nubu_field_wrap">
                                <div class="nubu_field_inner_wrap">
                                    <label for="indication_prescribed_for_1"><?php esc_html_e( 'Indication Prescribed for', 'Divi' ); ?></label>
                                    <input type="text" name="indication_prescribed_for[]" id="indication_prescribed_for_1" class="nubu_prescription_form_field">
                                </div>
                                <div class="nubu_field_inner_wrap">
                                    <label for="dosage_1"><?php esc_html_e( 'Dosage', 'Divi' ); ?></label>
                                    <input type="text" name="dosage[]" id="dosage_1" class="nubu_prescription_form_field">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nubu_prescription_form_footer">
                    <span class="nubu_form_error"></span>
                    <button type="button" class="nubu_add_patient"><?php esc_html_e( '+ Add Patient', 'Divi' ); ?></button>
                    <button type="submit" class="nubu_prescription_form_submit nubu_prescription_form_button"><?php esc_html_e( 'Submit', 'Divi' ); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    return '';
}
add_shortcode( 'nubu_prescription_form', 'nubu_get_prescription_form' );

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Generate GUID
 * 
 * Generates a unique identifier in GUID format.
 *
 * @since 1.0.0
 * @return string Generated GUID
 */
if ( ! function_exists( 'nubu_generate_guid' ) ) {
    function nubu_generate_guid() {
        $guid = uniqid( '', true );
        $guid = str_replace( '.', '', $guid );
        $guid = str_pad( $guid, 32, '0', STR_PAD_RIGHT );
        return substr( $guid, 0, 8 ) . '-' . substr( $guid, 8, 4 ) . '-' . substr( $guid, 12, 4 ) . '-' . substr( $guid, 16, 4 ) . '-' . substr( $guid, 20, 12 );
    }   
}

/**
 * Convert array to data attributes
 * 
 * Converts associative array to HTML data attributes string.
 * Useful for passing configuration to JavaScript.
 *
 * @since 1.0.0
 * @param array $array Array to convert
 * @return string HTML data attributes string
 */
function nubu_array_to_data_attributes( $array ) {
    $html = '';
    foreach ( $array as $key => $value ) {
        // Convert boolean to "true"/"false" string
        if ( is_bool( $value ) ) {
            $value = $value ? 'true' : 'false';
        }
        $html .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }
    return $html;
}

/**
 * Sync management callback
 * 
 * Loads the sync management admin page template.
 *
 * @since 1.0.0
 * @return void
 */
function sync_management_callback() {
    include_once get_stylesheet_directory() . '/admin/sync.php';
}

// ============================================================================
// MISC CUSTOMIZATIONS
// ============================================================================

/**
 * Remove default Divi project post type
 *
 * @since 1.0.0
 * @return void
 */
function nubu_remove_project_post_type() {
    unregister_post_type( 'project' );
}
add_action( 'init', 'nubu_remove_project_post_type', 20 );

/**
 * Add noindex, nofollow meta tag
 * 
 * Prevents search engines from indexing the site.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'wp_head', function() {
    echo '<meta name="robots" content="noindex, nofollow">';
} );

/**
 * Display "Add to Cart" button on Divi shop module
 *
 * @since 1.0.0
 * @return void
 */
if ( ! function_exists( 'nubu_add_to_cart_button_on_shop' ) ) {
    function nubu_add_to_cart_button_on_shop() {
        add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );
    }
    add_action( 'init', 'nubu_add_to_cart_button_on_shop' );
}



// ============================================================================
// USER PAYMENT METHOD VISIBILITY
// ============================================================================

/**
 * Render user payment method visibility field
 * 
 * Adds an admin-only profile field for selecting which payment gateway
 * should be available to an individual user.
 *
 * @since 1.0.0
 * @param WP_User $user User profile being edited.
 * @return void
 */
function nubu_custom_payment_field($user) {
    if (!current_user_can('manage_woocommerce')) return;

    $value = get_user_meta($user->ID, 'available_payment_methods', true);
    $payment_gateways = WC_Payment_Gateways::instance()->get_available_payment_gateways();
    ?>
    <h2>Custom Payment Method Visibility</h2>
    <table class="form-table">
        <tr>
            <th><label for="available_payment_methods">Select Payment Method</label></th>
            <td>
                <select name="available_payment_methods" id="available_payment_methods">
                    <option value="all" <?php selected($value, 'all'); ?>>Keep All</option>
                    <?php foreach ($payment_gateways as $gateway_id => $gateway) { ?>
                        <option value="<?php echo esc_attr($gateway_id); ?>" <?php selected($value, $gateway_id); ?>>
                            <?php echo esc_html($gateway->get_title()); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'nubu_custom_payment_field');
add_action('edit_user_profile', 'nubu_custom_payment_field');


/**
 * Save user payment method visibility setting
 * 
 * Persists the selected available payment method from the user profile form.
 *
 * @since 1.0.0
 * @param int $user_id User ID being saved.
 * @return void
 */
function nubu_save_payment_option($user_id) {
    if (!current_user_can('manage_woocommerce')) return;

    if (isset($_POST['available_payment_methods'])) {
        update_user_meta(
            $user_id,
            'available_payment_methods',
            sanitize_text_field($_POST['available_payment_methods'])
        );
    }
}
add_action('personal_options_update', 'nubu_save_payment_option');
add_action('edit_user_profile_update', 'nubu_save_payment_option');


/**
 * Filter payment gateways by user setting
 * 
 * Removes payment gateways that are not enabled for the current user.
 *
 * @since 1.0.0
 * @param array $available_gateways Available WooCommerce gateways.
 * @return array Filtered payment gateways.
 */
function nubu_filter_gateways_by_user($available_gateways) {
    if (!is_user_logged_in() || is_admin()) return $available_gateways;

    $user_id = get_current_user_id();
    $selected_method = get_user_meta($user_id, 'available_payment_methods', true) ?: 'all';

    foreach ($available_gateways as $key => $gateway) {
        if ($key !== $selected_method && 'all' !== $selected_method) {
            unset($available_gateways[$key]);
        }
    }

    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'nubu_filter_gateways_by_user');

/**
 * Add footer modal HTML
 * 
 * Outputs HCP registration/login modal in footer.
 *
 * @since 1.0.0
 * @return void
 */
function nubu_add_footer_modal() {
    ?>
    <div id="hcp-modal">
        <div id="modal-btn-close"></div>
        <div class="login">
            <h2>Login</h2>
            <div class="login-form">
                <?php echo do_shortcode( '[et_pb_section global_module="7877"][/et_pb_section]' ); ?>
            </div>
        </div>
        <div class="register">
            <?php echo do_shortcode( '[hcp_registration_form]' ); ?>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'nubu_add_footer_modal' );

/**
 * Divi Blog image dimension filters
 * 
 * Sets very large dimensions to prevent image cropping.
 *
 * @since 1.0.0
 * @param int $dimension Original dimension
 * @return string Large dimension value
 */
function nubu_blog_image_width( $width ) {
    return '9999';
}
function nubu_blog_image_height( $height ) {
    return '9999';
}
add_filter( 'et_pb_blog_image_width', 'nubu_blog_image_width' );
add_filter( 'et_pb_blog_image_height', 'nubu_blog_image_height' );

/**
 * Enable shortcodes in navigation menu
 *
 * @since 1.0.0
 */
add_filter( 'wp_nav_menu_items', function ( $items, $args ) {
    return do_shortcode( $items );
}, 10, 2 );

add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
    // Replace announcement widget menu item with actual widget
    if ( '[announcement_widget]' === $item->title ) {
        $item_output = do_shortcode( '[announcement_widget]' );
    }
    return $item_output;
}, 10, 4 );

// ============================================================================
// ANNOUNCEMENT SYSTEM
// ============================================================================

/**
 * Announcement slider shortcode
 * 
 * Displays announcements in a Swiper slider with customizable settings.
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Slider HTML
 */
function nubu_announcement_slider( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    
    static $order = 0;
    
    $atts = shortcode_atts( array(
        'slides_per_view'              => '1',
        'slides_per_view_tablet'       => '1',
        'slides_per_view_phone'        => '1',
        'space_between_slides'         => '15',
        'space_between_slides_tablet'  => '15',
        'space_between_slides_phone'   => '15',
        'autoplay'                     => false,
        'loop'                         => true,
        'pause_on_hover'               => false,
        'number_of_posts'              => 10,
        'orderby'                      => 'date',
        'order'                        => 'desc'
    ), $atts, 'announcement_slider' );
    
    $args = array(
        'post_type'      => 'announcement',
        'posts_per_page' => intval( $atts['number_of_posts'] ),
        'orderby'        => sanitize_text_field( $atts['orderby'] ),
        'order'          => sanitize_text_field( $atts['order'] ),
        'post_status'    => 'publish',
    );
    
    $query = new WP_Query( $args );
    
    if ( $query->have_posts() ) {
        $posts = '';
        
        $slider_array = $atts;
        unset( $slider_array['number_of_posts'] );
        unset( $slider_array['orderby'] );
        unset( $slider_array['order'] );
        
        wp_enqueue_script( 'swiper' );
        wp_enqueue_style( 'swiper' );

        $data_atts = nubu_array_to_data_attributes( $slider_array );
        $counter   = 1;
        
        while ( $query->have_posts() ) {
            $query->the_post();
            
            if ( 1 === $counter % 5 ) {
                $posts .= '<div class="swiper-slide">';
            }
            
            $posts .= '<div class="nubu_announcement">';
            $posts .= '<div class="nubu_announcement_icon"><svg xmlns="http://www.w3.org/2000/svg" class="nubu_fa_bell_icon" viewBox="0 0 640 640"><path d="M320 64C306.7 64 296 74.7 296 88L296 97.7C214.6 109.3 152 179.4 152 264L152 278.5C152 316.2 142 353.2 123 385.8L101.1 423.2C97.8 429 96 435.5 96 442.2C96 463.1 112.9 480 133.8 480L506.2 480C527.1 480 544 463.1 544 442.2C544 435.5 542.2 428.9 538.9 423.2L517 385.7C498 353.1 488 316.1 488 278.4L488 263.9C488 179.3 425.4 109.2 344 97.6L344 87.9C344 74.6 333.3 63.9 320 63.9zM488.4 432L151.5 432L164.4 409.9C187.7 370 200 324.6 200 278.5L200 264C200 197.7 253.7 144 320 144C386.3 144 440 197.7 440 264L440 278.5C440 324.7 452.3 370 475.5 409.9L488.4 432zM252.1 528C262 556 288.7 576 320 576C351.3 576 378 556 387.9 528L252.1 528z"/></svg></div>';
            $posts .= '<h3 class="nubu_announcement_title">' . esc_html( get_the_title() ) . '</h3>';
            
            $categories = get_the_terms( get_the_ID(), 'announcement-category' );
            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                $posts .= '<div class="nubu_announcement_categories">';
                foreach ( $categories as $category ) {
                    $color_scheme = ' nubu_' . carbon_get_term_meta( $category->term_id, 'color_scheme' ) . '_color';
                    $posts .= '<span class="nubu_announcement_category' . esc_attr( $color_scheme ) . '">' . esc_html( $category->name ) . '</span>';
                }
                $posts .= '</div>';
            }
            
            $posts .= '<div class="nubu_announcement_content">' . wp_kses_post( get_the_content() ) . '</div>';
            $posts .= '<div class="nubu_announcement_date">' . esc_html( get_the_modified_date() ) . '</div>';
            $posts .= '</div>';
            
            if ( 0 === $counter % 5 ) {
                $posts .= '</div>';
            }
            
            $counter++;
        }
        
        if ( 0 !== ( $counter - 1 ) % 5 ) {
            $posts .= '</div>';
        }
        
        wp_reset_postdata();
    }
    
    if ( ! empty( $posts ) ) {
        $output = '<div class="nubu_announcements nubu_announcement_slider nubu_announcement_slider_' . intval( $order ) . '"' . $data_atts . '><div class="swiper-wrapper">' . $posts . '</div><div class="nubu_swiper_navigation"><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div></div>';
        
        $order++;
        return $output;
    }
    
    $order++;
    return '<p class="nubu_no_announcement_text">Currently there are no announcements.</p>';
}
add_shortcode( 'announcement_slider', 'nubu_announcement_slider' );

/**
 * Announcement widget shortcode
 * 
 * Displays announcement dropdown widget with unread count.
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Widget HTML
 */
function nubu_announcement_widget( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    
    $atts = shortcode_atts( array(
        'number_of_posts' => 10,
        'orderby'         => 'date',
        'order'           => 'desc'
    ), $atts, 'announcement_widget' );
    
    $args = array(
        'post_type'      => 'announcement',
        'posts_per_page' => intval( $atts['number_of_posts'] ),
        'orderby'        => sanitize_text_field( $atts['orderby'] ),
        'order'          => sanitize_text_field( $atts['order'] ),
        'post_status'    => 'publish',
    );
    
    $query = new WP_Query( $args );
    
    $posts        = '';
    $unread_count = 0;
    $user_id      = get_current_user_id();
    $last_read    = (int) get_user_meta( $user_id, 'nubu_announcement_last_read', true );
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            global $post;
            
            $posts .= '<div class="nubu_announcement">';
            $posts .= '<h3 class="nubu_announcement_title">' . esc_html( get_the_title() ) . '</h3>';
            
            $categories = get_the_terms( get_the_ID(), 'announcement-category' );
            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                $posts .= '<div class="nubu_announcement_categories">';
                foreach ( $categories as $category ) {
                    $color_scheme = ' nubu_' . carbon_get_term_meta( $category->term_id, 'color_scheme' ) . '_color';
                    $posts .= '<span class="nubu_announcement_category' . esc_attr( $color_scheme ) . '">' . esc_html( $category->name ) . '</span>';
                }
                $posts .= '</div>';
            }
            
            $posts .= '<div class="nubu_announcement_content">' . wp_kses_post( get_the_content() ) . '</div>';
            $posts .= '<div class="nubu_announcement_date">' . esc_html( get_the_modified_date() ) . '</div>';
            $posts .= '</div>';
            
            $is_unread = ( strtotime( $post->post_modified ) > $last_read );
            if ( $is_unread ) {
                $unread_count++;
            }
        }     
    } else {
        $posts .= '<p class="nubu_no_announcement_text">Currently there are no announcements</p>';
    }
    
    $unread_class = $unread_count > 0 ? ' has_unread_announcement' : '';
    $mark_as_read = $unread_count > 0 ? '<div class="nubu_mark_all_as_read">Mark all as read</div>' : '';
    
    $output = '<div class="nubu_announcement_dropdown_wrapper' . $unread_class . '">
                    <div class="nubu_announcement_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nubu_fa_bell_icon" viewBox="0 0 640 640"><path d="M320 64C306.7 64 296 74.7 296 88L296 97.7C214.6 109.3 152 179.4 152 264L152 278.5C152 316.2 142 353.2 123 385.8L101.1 423.2C97.8 429 96 435.5 96 442.2C96 463.1 112.9 480 133.8 480L506.2 480C527.1 480 544 463.1 544 442.2C544 435.5 542.2 428.9 538.9 423.2L517 385.7C498 353.1 488 316.1 488 278.4L488 263.9C488 179.3 425.4 109.2 344 97.6L344 87.9C344 74.6 333.3 63.9 320 63.9zM488.4 432L151.5 432L164.4 409.9C187.7 370 200 324.6 200 278.5L200 264C200 197.7 253.7 144 320 144C386.3 144 440 197.7 440 264L440 278.5C440 324.7 452.3 370 475.5 409.9L488.4 432zM252.1 528C262 556 288.7 576 320 576C351.3 576 378 556 387.9 528L252.1 528z"/></svg>
                    </div>
                    <div class="nubu_announcement_dropdown">
                    ' . $posts . $mark_as_read . '
                    </div>
                </div>';
    
    return shortcode_unautop( $output );
}
add_shortcode( 'announcement_widget', 'nubu_announcement_widget' );

// ============================================================================
// TERPENE CHARTS
// ============================================================================

/**
 * Add terpenes meta box to products
 *
 * @since 1.0.0
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'nubu_product_terpenes',
        'Terpenes Chart',
        'nubu_render_terpene_meta_box',
        'product',
        'normal',
        'default'
    );
} );

/**
 * Render terpene meta box
 *
 * @since 1.0.0
 * @param WP_Post $post Current post object
 */
function nubu_render_terpene_meta_box( $post ) {
    wp_nonce_field( 'nubu_terpene_save_action', 'nubu_terpene_nonce' );

    $show_terpene_chart = get_post_meta( $post->ID, 'show_terpene_chart', true );
    $terpenes           = get_post_meta( $post->ID, 'terpenes_data', true );
    $title              = get_post_meta( $post->ID, 'terpene_chart_title', true );
    $desc               = get_post_meta( $post->ID, 'terpene_chart_description', true );
    
    if ( ! is_array( $terpenes ) ) {
        $terpenes = array();
    }
    ?>
    <p style="margin-bottom:15px;">
        <label style="font-weight:600;">
            <input type="checkbox" name="show_terpene_chart" value="1" <?php checked( $show_terpene_chart, '1' ); ?>>
            Show Terpene Chart
        </label>
    </p>
    
    <div id="ingredients-wrapper" style="<?php echo ! empty( $show_terpene_chart ) ? 'display: flex;' : 'display:none;'; ?> flex-flow: column nowrap; justify-content: center;">
        <div class="title-container" style="width:25%; display:flex; flex-flow: row nowrap; justify-content: space-between;">
            <label for="chart-title">Chart Title:</label>
            <input type="text" id="chart-title" value="<?php echo $title ? esc_attr( $title ) : ''; ?>" name="terpene_chart_title" placeholder="Chart Title" style="margin-bottom:10px;width:60%;">
        </div>
        <div class="desc-container" style="width:25%; display:flex; flex-flow: row nowrap; justify-content: space-between;">
            <label for="chart-desc">Chart Description:</label>
            <textarea placeholder="Chart Description" id="chart-desc" name="terpene_chart_description" cols="50" style="margin-bottom:10px;width:60%;"><?php echo $desc ? esc_attr( $desc ) : ''; ?></textarea>
        </div>
        <p>Terpenes</p>
        <?php foreach ( $terpenes as $index => $terpene ): ?>
            <div class="ingredient-row" style="margin-bottom:10px;">
                <label>Ingredient <?php echo ( $index + 1 ); ?>:</label>
                <input type="text" name="terpenes[<?php echo $index; ?>][title]" placeholder="Title"
                       value="<?php echo esc_attr( $terpene['title'] ); ?>" style="width:25%; margin-right:10px;" required>
                <input type="text" name="terpenes[<?php echo $index; ?>][description]" placeholder="Description"
                       value="<?php echo esc_attr( $terpene['description'] ); ?>" style="width:25%; margin-right:10px;">
                <input type="color" name="terpenes[<?php echo $index; ?>][color]"
                       value="<?php echo esc_attr( $terpene['color'] ); ?>" style="width:7%; margin-right:10px;">
                <input type="number" name="terpenes[<?php echo $index; ?>][quant]"
                       value="<?php echo esc_attr( $terpene['quant'] ); ?>" style="width:13%; margin-right:10px;" step="0.1" placeholder="Percentage" min="0.1" max="100" required>
                <button type="button" class="remove-ingredient button">Remove</button>
            </div>
        <?php endforeach; ?>
        <button type="button" id="add-ingredient" class="button button-primary" style="margin-top:10px; width:150px;">+ Add Terpene</button>
    </div>
    <?php
}

/**
 * Save terpene meta box data
 *
 * @since 1.0.0
 * @param int $post_id Post ID
 */
add_action( 'save_post_product', function( $post_id ) {
    if (
        ! isset( $_POST['nubu_terpene_nonce'] ) ||
        ! wp_verify_nonce( $_POST['nubu_terpene_nonce'], 'nubu_terpene_save_action' )
    ) {
        return;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    
    if ( isset( $_POST['show_terpene_chart'] ) ) {
        update_post_meta( $post_id, 'show_terpene_chart', '1' );
    } else {
        delete_post_meta( $post_id, 'show_terpene_chart' );
    }
    
    if ( isset( $_POST['terpene_chart_title'] ) ) {
        $terpene_chart_title = sanitize_text_field( $_POST['terpene_chart_title'] );
        update_post_meta( $post_id, 'terpene_chart_title', $terpene_chart_title );
    } else {
        delete_post_meta( $post_id, 'terpene_chart_title' );
    }
    
    if ( isset( $_POST['terpene_chart_description'] ) ) {
        $terpene_chart_description = sanitize_text_field( $_POST['terpene_chart_description'] );
        update_post_meta( $post_id, 'terpene_chart_description', $terpene_chart_description );
    } else {
        delete_post_meta( $post_id, 'terpene_chart_description' );
    }
    
    if ( isset( $_POST['terpenes'] ) && is_array( $_POST['terpenes'] ) ) {
        $clean_data = array_map( function( $terpenes ) {
            return array(
                'title'       => sanitize_text_field( $terpenes['title'] ),
                'description' => sanitize_text_field( $terpenes['description'] ),
                'color'       => sanitize_hex_color( $terpenes['color'] ),
                'quant'       => floatval( $terpenes['quant'] ),
            );
        }, $_POST['terpenes'] );
        
        $quant = array_column( $clean_data, 'quant' );
        $sum   = floatval( array_sum( $quant ) );
        
        if ( abs( $sum - 100 ) < 0.01 ) {
            update_post_meta( $post_id, 'terpenes_data', $clean_data );
        }
    } else {
        delete_post_meta( $post_id, 'terpenes_data' );
    }
} );

/**
 * Terpenes chart shortcode
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Chart HTML
 */
function nubu_terpenes_chart( $atts ) {
    $atts = shortcode_atts( array(
        'product_id' => get_the_ID(),
    ), $atts, 'terpenes_chart' );

    $product_id             = intval( $atts['product_id'] );
    $show_terpene_chart     = get_post_meta( $product_id, 'show_terpene_chart', true );
    $terpene_chart_title    = get_post_meta( $product_id, 'terpene_chart_title', true );
    $terpene_chart_desc     = get_post_meta( $product_id, 'terpene_chart_description', true );
    $terpenes               = get_post_meta( $product_id, 'terpenes_data', true );
    
    if ( ! empty( $terpenes ) && is_array( $terpenes ) && $show_terpene_chart ) {
        $labels       = array_column( $terpenes, 'title' );
        $colors       = array_column( $terpenes, 'color' );
        $descriptions = array_column( $terpenes, 'description' );
        $percentages  = array_column( $terpenes, 'quant' );
        
        ob_start(); ?>
        <div class="ingredients-chart custom-doughnut-chart-tpr">
            <div id="donut-<?php echo esc_attr( $product_id ); ?>" 
                data-title='<?php echo esc_attr( wp_json_encode( $labels ) ); ?>' 
                data-color='<?php echo esc_attr( wp_json_encode( $colors ) ); ?>' 
                data-description='<?php echo esc_attr( wp_json_encode( $descriptions ) ); ?>' 
                data-quant='<?php echo esc_attr( wp_json_encode( $percentages ) ); ?>'></div>
            <div class="ingredient-item">
                <div>
                    <div class="custom-legend" id="legend-<?php echo esc_attr( $product_id ); ?>"></div>
                    <?php if ( ! empty( $terpene_chart_title ) || ! empty( $terpene_chart_desc ) ) { ?>
                    <div class="title-desc-wrapper">
                        <?php if ( '' !== $terpene_chart_title ) { ?>
                        <div class="chart-title">
                            <h2><?php echo esc_html( $terpene_chart_title ); ?></h2>
                        </div>
                        <?php } ?>
                        <?php if ( '' !== $terpene_chart_desc ) { ?>
                        <div class="chart-desc">
                            <p><?php echo esc_html( $terpene_chart_desc ); ?></p>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <div class="chart-wrap">
                        <canvas width="500" id="donutChart-<?php echo esc_attr( $product_id ); ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    return '';
}
add_shortcode( 'terpenes_chart', 'nubu_terpenes_chart' );

// ============================================================================
// REQUIRED PRESCRIPTION MODAL
// ============================================================================

/**
 * Add required prescription form modal to footer
 *
 * @since 1.0.0
 */
function nubu_required_prescription_form() {
    if ( ! is_user_logged_in() || is_checkout() ) {
        return;
    }
    
    $current_user        = wp_get_current_user();
    $registration_clinic = $current_user->_registration_clinic;
    ?>
    <div id="nubu_prescription_modal" class="nubu_modal">
        <div class="nubu_modal_inner">
            <span class="nubu_modal_popup_close_icon">&#10006;</span>
            <div class="nubu_prescription_form_wrapper">
                <form id="nubu_required_prescription_form" class="nubu_prescription_form" data-product_id="">
                    <h2><?php echo esc_html__( 'CSC Prescription Details', 'Divi' ); ?></h2>
                    <p>Please use the form below to enter patient Section 29 details including CSC Client number for this order. Required fields are marked with an asterisk (*). After filling in each field, you will have the option to add another patient's information.</p>
                    <p>If you are placing an order for this CSC product for more than one patient, please ensure you add another patient and clearly number how many units are for each.</p>
                    <p>This information is a requirement for accessing the NUBU NCAP pricing, including a valid CSC customer client number. You do not need to add the same information again at checkout unless its for a non-NCAP product.</p>
                    <p>For further assistance, contact <a href="mailto:hq@nubu.co.nz">hq@nubu.co.nz</a>.</p>
                    <div class="nubu_patient_details_wrapper">
                        <div class="nubu_patient_details" id="nubu_patient_1">
                            <div class="nubu_patient_details_header">
                                <div class="nubu_patient_info">
                                    <span class="nubu_patient_name"></span>
                                    <span class="nubu_product_name"></span>
                                </div>
                                <div class="nubu_patient_action">
                                    <span class="nubu_edit_patient" title="Edit Patient">&#x6c;</span>
                                    <span class="nubu_save_patient" title="Save Patient">&#xe0e8;</span>
                                    <span class="nubu_remove_patient" title="Remove Patient">&#xe07d;</span>
                                </div>
                            </div>
                            <div class="nubu_patient_details_body">
                                <!-- Form fields here - identical to nubu_get_prescription_form -->
                                <div class="nubu_field_wrap">
                                    <div class="nubu_field_inner_wrap">
                                        <label for="patient_first_name_1"><?php esc_html_e( "Patient's Full Name", 'Divi' ); ?>*</label>
                                        <input type="text" name="patient_first_name[]" id="patient_first_name_1" class="nubu_prescription_form_field nubu_patient_first_name" required>
                                        <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'First Name', 'Divi' ); ?></span>
                                    </div>
                                    <div class="nubu_field_inner_wrap">
                                        <input type="text" name="patient_last_name[]" id="patient_last_name_1" class="nubu_prescription_form_field nubu_patient_last_name" required>
                                        <span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Last Name', 'Divi' ); ?></span>
                                    </div>
                                </div>
                                <div class="nubu_field_wrap nubu_full_width_field">
									<div class="nubu_field_inner_wrap">
										<label for="date_prescribed_1"><?php esc_html_e( 'Date Prescribed', 'Divi' ); ?>*</label>
										<input type="text" name="date_prescribed[]" id="date_prescribed_1" class="nubu_prescription_form_field nubu_prescription_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" readonly required>
										<input type="hidden" name="order_number[]" id="order_number_1" class="nubu_prescription_form_field nubu_order_number" value="0" readonly required>
										<span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Date', 'Divi' ); ?></span>
									</div>
									<div class="nubu_field_inner_wrap">
										<label for="csc_client_number_1"><?php esc_html_e( 'CSC Client/Card Number', 'Divi' ); ?>*</label>
										<input type="text" maxlength="11" pattern="\d{11}" name="csc_client_number[]" id="csc_client_number_1" class="nubu_prescription_form_field nubu_csc_client_number" required>
									</div>
								</div>
								<div class="nubu_field_wrap">
									<div class="nubu_field_inner_wrap">
										<label for="product_prescribed_1"><?php esc_html_e( 'Product Dispensing', 'Divi' ); ?>*</label>
										<input type="text" name="product_prescribed[]" id="product_prescribed_1" class="nubu_prescription_form_field nubu_product_prescribed" value="" required />
										<span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Select a product for the patient', 'Divi' ); ?></span>
									</div>
									<div class="nubu_field_inner_wrap">
										<label for="units_prescribed_1"><?php esc_html_e( 'Units Dispensing', 'Divi' ); ?>*</label>
										<input type="number" name="units_prescribed[]" id="units_prescribed_1" min="1" class="nubu_prescription_form_field nubu_units_prescribed" value="" required>
										<span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Select how many units prescribed for the patient', 'Divi' ); ?></span>
									</div>
								</div>
								<div class="nubu_field_wrap">
									<div class="nubu_field_inner_wrap">
										<label for="dispensing_pharmacy_1"><?php esc_html_e( 'Dispensing Pharmacy', 'Divi' ); ?>*</label>
										<input type="text" name="dispensing_pharmacy[]" id="dispensing_pharmacy_1" class="nubu_dispensing_pharmacy nubu_prescription_form_field" value="<?php echo esc_attr( $registration_clinic ); ?>" required>
									</div>
									<div class="nubu_field_inner_wrap">
										<label for="prescribing_doctor_clinic_1"><?php esc_html_e( 'Prescribing Doctor Clinic', 'Divi' ); ?></label>
										<input type="text" name="prescribing_doctor_clinic[]" id="prescribing_doctor_clinic_1" class="nubu_prescription_form_field">
									</div>

								</div>
								<div class="nubu_field_wrap">
									<div class="nubu_field_inner_wrap">
										<label for="prescribing_doctor_first_name_1"><?php esc_html_e( 'Doctor\'s Full Name', 'Divi' ); ?>*</label>
										<input type="text" name="prescribing_doctor_first_name[]" id="prescribing_doctor_first_name_1" class="nubu_prescription_form_field" required>
										<span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'First Name', 'Divi' ); ?></span>
									</div>
									<div class="nubu_field_inner_wrap">
										<input type="text" name="prescribing_doctor_last_name[]" id="prescribing_doctor_last_name_1" class="nubu_prescription_form_field" required>
										<span class="nubu_prescription_form_field_sublabel"><?php esc_html_e( 'Last Name', 'Divi' ); ?></span>
									</div>
								</div>
								<div class="nubu_field_wrap">
									<div class="nubu_field_inner_wrap">
										<label for="indication_prescribed_for_1"><?php esc_html_e( 'Indication Prescribed for', 'Divi' ); ?></label>
										<input type="text" name="indication_prescribed_for[]" id="indication_prescribed_for_1" class="nubu_prescription_form_field">
									</div>
									<div class="nubu_field_inner_wrap">
										<label for="dosage_1"><?php esc_html_e( 'Dosage', 'Divi' ); ?></label>
										<input type="text" name="dosage[]" id="dosage_1" class="nubu_prescription_form_field">
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                    <div class="nubu_prescription_form_footer">
                        <span class="nubu_form_error"></span>
                        <button type="button" class="nubu_add_patient"><?php esc_html_e( '+ Add Patient', 'Divi' ); ?></button>
                        <button type="submit" class="nubu_prescription_form_submit nubu_prescription_form_button"><?php esc_html_e( 'Submit', 'Divi' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'nubu_required_prescription_form' );

// ============================================================================
// WOOCOMMERCE THANKYOU PAGE CUSTOMIZATION
// ============================================================================

/**
 * Display prescription form on thank you page if required
 *
 * @since 1.0.0
 * @param int $order_id Order ID
 */
function nubu_woocommerce_thankyou( $order_id ) {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    
    $current_user = wp_get_current_user();
    $show_form    = $current_user->_show_prescription_form;
    
    if ( 'yes' === $show_form ) {
        echo '<style>.et_pb_wc_checkout_payment_info .woocommerce-order-overview,
.et_pb_wc_checkout_payment_info .woocommerce-order-details,
.et_pb_wc_checkout_payment_info .woocommerce-customer-details,
.et_pb_wc_checkout_payment_info .et_pb_ab_shop_conversion {
    display: none;
}</style>';
        echo do_shortcode( '[nubu_prescription_form order_id="' . intval( $order_id ) . '"]' );
    }
}
add_action( 'woocommerce_thankyou', 'nubu_woocommerce_thankyou' );

// ============================================================================
// REPLACE ADD TO CART FOR NON-TRADE ACCOUNTS
// ============================================================================

/**
 * Replace Add to Cart button for non-trade users
 *
 * @since 1.0.0
 */
add_filter( 'woocommerce_loop_add_to_cart_link', 'nubu_maybe_replace_loop_add_to_cart_for_viewers', 10, 2 );

function nubu_maybe_replace_loop_add_to_cart_for_viewers( $button, $product ) {
    if ( is_user_logged_in() ) {
        if ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) ) {
            return $button;
        }
    }

    $apply_url  = carbon_get_theme_option( 'trade_account_form_url' );
    $apply_text = 'Apply for Trade Account';

    return sprintf(
        '<a href="%s" class="button apply-for-account">%s</a>',
        esc_url( $apply_url ),
        esc_html( $apply_text )
    );
}

// ============================================================================
// DIVI BLOG MODIFICATIONS  
// ============================================================================

/**
 * Add category fields to Divi blog module
 *
 * @since 1.0.0
 */
function nubu_add_category_fields( $fields_unprocessed ) {
    $fields_unprocessed['include_education_resources_categories'] = array(
        'label'            => esc_html__( 'Categories', 'divi' ),
        'type'             => 'categories',
        'option_category'  => 'basic_option',
        'renderer_options' => array(
            'use_terms'  => true,
            'term_name'  => 'education-category',
            'field_name' => 'nubu_include_education_resources_categories',
        ),
        'show_if'          => array(
            'use_current_loop' => 'off',
            'post_type'        => 'education-resource',
        ),
        'tab_slug'         => 'general',
        'toggle_slug'      => 'main_content',
        'description'      => esc_html__( 'Choose which terms posts you would like to include in the feed.', 'divi' ),
        'priority'         => 20
    );
    
    return $fields_unprocessed;
}
add_filter( 'et_pb_all_fields_unprocessed_et_pb_blog', 'nubu_add_category_fields' );

/**
 * Modify Divi blog query for education resources
 *
 * @since 1.0.0
 */
function nubu_modify_divi_blog_query( $wp_query, $attrs ) {
    if ( is_admin() || ! $wp_query instanceof WP_Query ) {
        return $wp_query;
    }

    $post_types = (array) $wp_query->get( 'post_type' );
    
    if ( ! in_array( 'education-resource', $post_types, true ) ) {
        return $wp_query;
    }

    add_filter( 'the_title', 'nubu_modify_education_resource_title', 10, 2 );
    
    $args       = $wp_query->query_vars;
    $categories = ! empty( $attrs['include_education_resources_categories'] ) ? sanitize_text_field( $attrs['include_education_resources_categories'] ) : '';
    
    if ( '' !== $categories ) {
        $existing_tax_query = (array) $wp_query->get( 'tax_query' );
        
        if ( ! empty( $existing_tax_query ) && ! isset( $existing_tax_query['relation'] ) ) {
            $existing_tax_query['relation'] = 'AND';
        }
        
        $existing_tax_query[] = array(
            'taxonomy' => 'education-category',
            'field'    => 'term_id',
            'terms'    => array_map( 'intval', explode( ',', $categories ) ),
            'operator' => 'IN'
        );
        
        $args['tax_query'] = $existing_tax_query;
    }

    global $wp_query;
    $wp_query = new WP_Query( $args );
    
    $wp_query->education_resource = true;
    
    return $wp_query;
}
add_filter( 'et_builder_blog_query', 'nubu_modify_divi_blog_query', 10, 2 );

/**
 * Modify education resource titles to include icon
 *
 * @since 1.0.0
 */
function nubu_modify_education_resource_title( $title, $post_id ) {
    global $wp_query;

    if (
        is_admin() ||
        empty( $wp_query ) ||
        empty( $wp_query->education_resource )
    ) {
        return $title;
    }

    if ( 'education-resource' !== get_post_type( $post_id ) ) {
        return $title;
    }

    $image_id  = carbon_get_post_meta( $post_id, 'icon' );
    $image_url = wp_get_attachment_url( $image_id );
    
    if ( $image_url ) {
        $icon = sprintf(
            '<img src="%s" alt="%s" class="education-icon" />',
            esc_url( $image_url ),
            esc_html( $title )
        );
        
        return $icon . $title;
    }
    
    return $title;
}


/*
add_filter( 'rest_pre_dispatch', 'uc2u_convert_duplicate_sku_create_to_update', 9, 3 );

function uc2u_convert_duplicate_sku_create_to_update( $result, $server, $request ) {
	if ( null !== $result ) {
		return $result;
	}

	if ( ! function_exists( 'wc_get_product_id_by_sku' ) || ! class_exists( 'WC_REST_Products_Controller' ) ) {
		return $result;
	}

	if ( 'POST' !== $request->get_method() ) {
		return $result;
	}

	$route = untrailingslashit( $request->get_route() );

	if ( '/wc/v3/products' === $route ) {
		return uc2u_handle_single_product_create( $result, $request );
	}

	if ( '/wc/v3/products/batch' === $route ) {
		return uc2u_handle_batch_product_create( $result, $request );
	}

	return $result;
}

function uc2u_handle_single_product_create( $result, WP_REST_Request $request ) {
	$payload = uc2u_get_request_payload( $request );
	$sku     = isset( $payload['sku'] ) ? wc_clean( $payload['sku'] ) : '';

	if ( '' === $sku ) {
		return $result;
	}

	$existing_id = wc_get_product_id_by_sku( $sku );

	if ( ! $existing_id ) {
		return $result;
	}

	return uc2u_update_existing_product_from_payload( $existing_id, $payload, $sku );
}

function uc2u_handle_batch_product_create( $result, WP_REST_Request $request ) {
	$payload = uc2u_get_request_payload( $request );

	if ( empty( $payload['create'] ) || ! is_array( $payload['create'] ) ) {
		return $result;
	}

	$converted_create_items = array();
	$remaining_create_items = array();

	foreach ( $payload['create'] as $item ) {
		$sku = isset( $item['sku'] ) ? wc_clean( $item['sku'] ) : '';

		if ( '' === $sku ) {
			$remaining_create_items[] = $item;
			continue;
		}

		$existing_id = wc_get_product_id_by_sku( $sku );

		if ( ! $existing_id ) {
			$remaining_create_items[] = $item;
			continue;
		}

		$item       = uc2u_filter_update_payload( $item );
		$item['id'] = $existing_id;

		$converted_create_items[] = $item;

		uc2u_log(
			sprintf(
				'Batch create converted to update. SKU: %s, product ID: %d',
				$sku,
				$existing_id
			)
		);
	}

	if ( empty( $converted_create_items ) ) {
		return $result;
	}

	$payload['create'] = array_values( $remaining_create_items );
	$payload['update'] = array_values(
		array_merge(
			isset( $payload['update'] ) && is_array( $payload['update'] ) ? $payload['update'] : array(),
			$converted_create_items
		)
	);

	$controller    = new WC_REST_Products_Controller();
	$batch_request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );

	foreach ( $payload as $key => $value ) {
		$batch_request->set_param( $key, $value );
	}

	$permission = $controller->batch_items_permissions_check( $batch_request );

	if ( is_wp_error( $permission ) ) {
		return $permission;
	}

	if ( true !== $permission ) {
		return new WP_Error(
			'uc2u_batch_permission_denied',
			'The authenticated REST API user cannot update WooCommerce products.',
			array( 'status' => 403 )
		);
	}

	return rest_ensure_response( $controller->batch_items( $batch_request ) );
}

function uc2u_update_existing_product_from_payload( $existing_id, array $payload, $sku ) {
	$product = wc_get_product( $existing_id );

	if ( ! $product ) {
		return new WP_Error(
			'uc2u_existing_product_not_found',
			sprintf( 'SKU "%s" exists, but product ID %d could not be loaded.', $sku, $existing_id ),
			array( 'status' => 409 )
		);
	}

	if ( 'variation' === $product->get_type() ) {
		return new WP_Error(
			'uc2u_sku_belongs_to_variation',
			sprintf( 'SKU "%s" belongs to variation ID %d. Product create was blocked to prevent duplication.', $sku, $existing_id ),
			array( 'status' => 409 )
		);
	}

	$payload = uc2u_filter_update_payload( $payload );

	$controller     = new WC_REST_Products_Controller();
	$update_request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $existing_id );

	$update_request->set_param( 'id', $existing_id );

	foreach ( $payload as $key => $value ) {
		$update_request->set_param( $key, $value );
	}

	$permission = $controller->update_item_permissions_check( $update_request );

	if ( is_wp_error( $permission ) ) {
		return $permission;
	}

	if ( true !== $permission ) {
		return new WP_Error(
			'uc2u_update_permission_denied',
			'The authenticated REST API user cannot update this WooCommerce product.',
			array( 'status' => 403 )
		);
	}

	uc2u_log(
		sprintf(
			'Single create converted to update. SKU: %s, product ID: %d',
			$sku,
			$existing_id
		)
	);

	return $controller->update_item( $update_request );
}

function uc2u_get_request_payload( WP_REST_Request $request ) {
	$json = $request->get_json_params();

	if ( is_array( $json ) && ! empty( $json ) ) {
		return $json;
	}

	$body = $request->get_body_params();

	if ( is_array( $body ) && ! empty( $body ) ) {
		return $body;
	}

	return array();
}

function uc2u_filter_update_payload( array $payload ) {
	$allowed_fields = array(
		'name',
		'status',
		'catalog_visibility',
		'description',
		'short_description',
		'regular_price',
		'sale_price',
		'date_on_sale_from',
		'date_on_sale_to',
		'manage_stock',
		'stock_quantity',
		'stock_status',
		'backorders',
		'weight',
		'dimensions',
		'shipping_class',
		'shipping_class_id',
		'tax_status',
		'tax_class',
		'meta_data',
	);

	$allowed_fields = apply_filters( 'uc2u_allowed_update_fields', $allowed_fields );

	return array_intersect_key( $payload, array_flip( $allowed_fields ) );
}

function uc2u_log( $message ) {
	if ( function_exists( 'wc_get_logger' ) ) {
		wc_get_logger()->info(
			$message,
			array( 'source' => 'unleashed-create-to-update' )
		);
	}
}*/
