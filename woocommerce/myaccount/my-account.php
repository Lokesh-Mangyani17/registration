<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<?php
global $wp;
if ( ! empty( $wp->query_vars ) ) {
	if ( $wp->query_vars['pagename'] === 'my-account' ) {
		if ( is_user_logged_in() && ! current_user_can( 'trade_account' ) && ! current_user_can( 'administrator' ) && ! current_user_can( 'customer' ) ) {
			$trade_url = carbon_get_theme_option('trade_account_form_url');
			?>
			<div class="nubu_trade_account_signup_wrapper">
				<p class="nubu_trade_account_signup_inner_wrap">
					<span class="nubu_trade_account_signup_label">Create a trade account</span>
					<a href="<?php echo esc_url( $trade_url ) ?>" class="nubu_myaccount_trade_account_signup_link" target="_blank">Sign up for trade account</a>
				</p>
			</div>
			<?php
		}
	}
}
?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>

