<?php
/**
 * HCP Registration – Healthcare Professional registration with admin approval workflow.
 *
 * @package HCP_Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'HCP_REG_VERSION' ) ) {
    define( 'HCP_REG_VERSION', '1.2.0' );
}

require_once HCP_REG_DIR . 'includes/class-hcp-db.php';
require_once HCP_REG_DIR . 'includes/class-hcp-form.php';
require_once HCP_REG_DIR . 'includes/class-hcp-admin.php';
require_once HCP_REG_DIR . 'includes/class-hcp-email.php';
require_once HCP_REG_DIR . 'includes/class-hcp-ghl.php';

require_once HCP_REG_DIR . 'includes/class-trade-form.php';

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
