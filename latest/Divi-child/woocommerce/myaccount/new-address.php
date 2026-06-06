<?php
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
	$value = get_user_meta( get_current_user_id(), $key, true );
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
<form class="nubu_woocommerce_new_address" method="post">
	<div class="woocommerce-address-fields">
		<div class="woocommerce-address-fields__field-wrapper">
			<p class="form-row form-row-wide validate-required" id="billing_address_name" data-priority="05"><label for="billing_address_name" class="required_field">Address Name&nbsp;<span class="required" aria-hidden="true">*</span></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="billing_address_name" id="billing_address_name" placeholder="" value="" aria-required="true"></span></p>
			<?php
			//add_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
			foreach ( $address as $key => $field ) {
				woocommerce_form_field( $key, $field );
			}
			//remove_filter( 'woocommerce_form_field_tel', 'nubu_woocommerce_remove_phone_field', 10, 4 );
			?>
		</div>
		<p>
			<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="nubu_woocommerce_save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
			<?php wp_nonce_field( 'nubu-woocommerce-save_address', 'nubu-woocommerce-save-address-nonce' ); ?>
			<input type="hidden" name="address_action" value="new_address" />
		</p>
	</div>
</form>
<?php