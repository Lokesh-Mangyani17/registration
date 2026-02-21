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

define( 'HCP_REG_VERSION', '1.0.0' );
define( 'HCP_REG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HCP_REG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-db.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-form.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-admin.php';
require_once HCP_REG_PLUGIN_DIR . 'includes/class-hcp-email.php';

/**
 * Run on plugin activation.
 */
function hcp_reg_activate() {
    HCP_DB::create_table();
    HCP_DB::register_role();
}
register_activation_hook( __FILE__, 'hcp_reg_activate' );

/**
 * Initialise front-end and admin components.
 */
function hcp_reg_init() {
    HCP_Form::init();
    if ( is_admin() ) {
        HCP_Admin::init();
    }
}
add_action( 'init', 'hcp_reg_init' );
