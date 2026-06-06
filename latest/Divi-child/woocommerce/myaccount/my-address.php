<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id    = get_current_user_id();
if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}
$addresses = get_user_meta( $customer_id, '_multiple_addresses', true );
$addresses = is_array($addresses) ? $addresses : [];
$address_count = ! empty( $addresses ) ? count( $addresses ) : count( $get_addresses );
if ( isset ($_GET['add_new_address'] ) ) {
	?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( 'Add New Address', 'divi' ); ?></h4>
		</div>
	</div>
	<div class="nubu_woocommerce_new_address_wrapper">
		<?php include 'new-address.php'  ?>
	</div>
	<?php
} else {
	?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( 'Manage Addresses', 'divi' ); ?></h4>
			<span class="nubu_woocommerce_my_account_endpoint_counter"><?php echo wp_kses_post( sprintf( _n( '%1$s Address', '%1$s Addresses', $address_count, 'woocommerce' ), $address_count ) ) ?></span>
		</div>
		<div class="nubu_woocommerce_my_account_endpoint_action">
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) . '?add_new_address="1"' ); ?>" class="nubu_woocommerce_add_address"><?php esc_html_e( '+ Add New Address', 'divi' ); ?></a>
		</div>
	</div>
	<div class="nubu_woocommerce_addresses_wrapper">
		<?php
		if ( ! empty( $addresses ) ) {
			foreach ( $addresses as $id => $address ) {
				$formatted_address = nubu_woocommerce_format_address( $address );
				?>
				<div class="nubu_woocommerce_address_wrapper">
					<div class="nubu_woocommerce_address">
						<div class="nubu_woocommerce_address_labels">
							<span class="nubu_woocommerce_address_label"><?php echo esc_html( ucwords( str_replace( '-', ' ', $id ) ) ); ?></span>
							<?php
							if ( $address['is_default'] ) {
								?><span class="nubu_woocommerce_address_label default"><?php echo esc_html( 'Default' ); ?></span><?php
							}
							?>
						</div>
						<address>
							<?php
								echo $formatted_address ? wp_kses_post( $formatted_address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );
							?>
						</address>
					</div>
					<div class="nubu_woocommerce_address_actions">
						<?php
						if ( ! $address['is_default'] ) {
						?>
							<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) . '?set_default_address="'. esc_attr( $id ) .'"' ); ?>" class="nubu_woocommerce_set_default_address"><?php echo esc_html__( 'Make Default', 'woocommerce' ); ?></a>
						<?php
						}
						?>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $id ) ); ?>" class="nubu_woocommerce_edit_address"><?php echo esc_html__( 'Edit', 'woocommerce' ); ?></a>
						<?php
						if ( ! $address['is_default'] ) {
						?>
							<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) . '?delete_address="'. esc_attr( $id ) .'"' ); ?>" class="nubu_woocommerce_delete_address"><?php echo esc_html__( 'Delete', 'woocommerce' ); ?></a>
						<?php
						}
						?>
					</div>
				</div>
				<?php
			}
		} else {
			foreach ( $get_addresses as $name => $address_title ) {
				$address = wc_get_account_formatted_address( $name );
				?>
				<div class="nubu_woocommerce_address_wrapper">
					<div class="nubu_woocommerce_address">
						<div class="nubu_woocommerce_address_labels">
							<span class="nubu_woocommerce_address_label default"><?php echo esc_html( 'Default' ); ?></span>
						</div>
						<address>
							<?php
								echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );

								/**
								 * Used to output content after core address fields.
								 *
								 * @param string $name Address type.
								 * @since 8.7.0
								 */
								//do_action( 'woocommerce_my_account_after_my_address', $name );
							?>
						</address>
					</div>
					<div class="nubu_woocommerce_address_actions">
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="nubu_woocommerce_edit_address"><?php echo $address ? esc_html__( 'Edit', 'woocommerce' ) : esc_html__( 'Add', 'woocommerce' ); ?></a>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
<?php
}