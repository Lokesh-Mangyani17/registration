<?php
/**
 * Plugin Name: HCP Registration
 * Description: Healthcare Professional registration with admin approval workflow.
 * Version:     1.0.0
 * Author:      Developer
 * Text Domain: hcp-registration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'HCP_REG_VERSION' ) ) {
    define( 'HCP_REG_VERSION', '1.1.0' );
}
if ( ! defined( 'HCP_REG_PLUGIN_DIR' ) ) {
    define( 'HCP_REG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'HCP_REG_PLUGIN_URL' ) ) {
    define( 'HCP_REG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-db.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-form.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-admin.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-email.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-ghl.php';

require_once HCP_REG_PLUGIN_DIR . 'includes/class-trade-form.php';

/**
 * Run on plugin activation.
 */
function hcp_reg_activate() {
    HCP_DB::create_table();
    HCP_DB::register_role();
    HCP_DB::create_trade_table();
    HCP_DB::register_trade_role();
}
register_activation_hook( __FILE__, 'hcp_reg_activate' );

/**
 * Initialise front-end and admin components.
 */
function hcp_reg_init() {
    HCP_Form::init();
    Trade_Form::init();
    if ( is_admin() ) {
        HCP_Admin::init();
    }
}
add_action( 'init', 'hcp_reg_init' );

/**
 * Register GHL settings on admin_init.
 */
function hcp_reg_ghl_register_settings() {
    HCP_GHL::register_settings();
}
add_action( 'admin_init', 'hcp_reg_ghl_register_settings' );

/**
 * Add GHL settings sub-menu page.
 */
function hcp_reg_ghl_admin_menu() {
    add_submenu_page(
        'hcp-registrations',
        __( 'GHL Settings', 'hcp-registration' ),
        __( 'GHL Settings', 'hcp-registration' ),
        'manage_options',
        'hcp-ghl-settings',
        array( 'HCP_GHL', 'render_settings_page' )
    );
}
add_action( 'admin_menu', 'hcp_reg_ghl_admin_menu' );

/**
 * Ensure trade table and role exist (runs once per version upgrade).
 */
function hcp_reg_check_trade_upgrade() {
    $installed_version = get_option( 'hcp_reg_db_version', '1.0.0' );
    if ( version_compare( $installed_version, '1.3.0', '<' ) ) {
        HCP_DB::create_table();
        HCP_DB::create_trade_table();
        HCP_DB::register_trade_role();
        update_option( 'hcp_reg_db_version', '1.3.0' );
    }
}
add_action( 'admin_init', 'hcp_reg_check_trade_upgrade' );
