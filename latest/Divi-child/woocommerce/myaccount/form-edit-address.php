<?php
/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) { ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php } else {
	$customer_id = get_current_user_id();
	if( 'billing' === $load_address || 'shipping' === $load_address ) {
		?>
		<div class="nubu_woocommerce_my_account_endpoint_header">
			<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
				<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( $page_title, 'divi' ); ?></h4>
			</div>
		</div>
		<div class="nubu_woocommerce_new_address_wrapper">
			<form method="post">
				<div class="woocommerce-address-fields">
					<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

					<div class="woocommerce-address-fields__field-wrapper">
						<?php
						add_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
						foreach ( $address as $key => $field ) {
							woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
						}
						remove_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
						?>
					</div>

					<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

					<p>
						<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
						<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
						<input type="hidden" name="action" value="edit_address" />
					</p>
				</div>

			</form>
		</div>
		<?php
	} else {
		$addresses = get_user_meta( $customer_id, '_multiple_addresses', true );
		$addresses = is_array($addresses) ? $addresses : [];
		//print_r($addresses);
		if ( ! empty( $addresses[$load_address] ) ) {
			$country = get_user_meta( get_current_user_id(), 'billing_country', true );
			if ( ! $country ) {
				$country = 'NZ';
			}

			$allowed_countries 	= WC()->countries->get_allowed_countries();
			if ( ! array_key_exists( $country, $allowed_countries ) ) {
				$country = current( array_keys( $allowed_countries ) );
			}

			$address = WC()->countries->get_address_fields( $country, 'billing_' );

			// Enqueue scripts.
			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'wc-address-i18n' );

			// Prepare values.
			foreach ( $address as $key => $field ) {
				$our_key = str_replace( 'billing_', '', $key );
				$value = $addresses[$load_address][$our_key];
				if ( ! $value ) {
					switch ( $key ) {
						case 'email':
							$value = $current_user->user_email;
							break;
					}
				}
				$address[ $key ]['value'] = $value;
			}
			?>
			<div class="nubu_woocommerce_my_account_endpoint_header">
				<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
					<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php echo esc_html( ucwords( str_replace( '-', ' ', $load_address ) ) ); ?></h4>
				</div>
			</div>
			<div class="nubu_woocommerce_new_address_wrapper">
				<form method="post">
					<div class="woocommerce-address-fields">
						<div class="woocommerce-address-fields__field-wrapper">
							<?php
							//add_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
							foreach ( $address as $key => $field ) {
								woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
							}
							//remove_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
							?>
						</div>

						<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

						<p>
							<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="nubu_woocommerce_save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
							<?php wp_nonce_field( 'nubu-woocommerce-edit_address', 'nubu-woocommerce-edit-address-nonce' ); ?>
							<input type="hidden" name="address_action" value="edit_address" />
							<input type="hidden" name="billing_address_name" value="<?php echo esc_attr( $load_address ); ?>" />
						</p>
					</div>
				</form>
			</div>
			<?php
		}
	}
}
?>
<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
