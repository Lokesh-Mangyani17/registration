<?php
defined( 'ABSPATH' ) || exit;

wc_print_notices(); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

<div class="custom-cart-wrapper">
    
    <!-- LEFT: Products -->
    <div class="cart-left">
        <?php do_action( 'woocommerce_before_cart_table' ); ?>

        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = $cart_item['product_id'];

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 ) :
                $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
        ?>
        <div class="custom-cart-card">

            <div class="cart-item-left">
                <a href="<?php echo esc_url( $product_permalink ); ?>">
                    <?php echo $_product->get_image(); ?>
                </a>
            </div>

            <div class="cart-item-right">
                <h3 class="cart-item-title">
                    <?php echo $_product->get_name(); ?>
                </h3>

                <div class="cart-item-meta">
                    <span class="price">
                        <?php echo WC()->cart->get_product_price( $_product ); ?>
                    </span>                   
                </div>

                <div class="remove">
                    <?php
                        echo apply_filters( 'woocommerce_cart_item_remove_link',
                            sprintf(
                                '<a href="%s" class="remove-item">×</a>',
                                esc_url( wc_get_cart_remove_url( $cart_item_key ) )
                            ),
                            $cart_item_key
                        );
                    ?>
                </div>
            </div>
			
			<div class="cart-item-footer">
    <div class="quantity">
        <?php
            woocommerce_quantity_input( array(
                'input_name'  => "cart[{$cart_item_key}][qty]",
                'input_value' => $cart_item['quantity'],
                'min_value'   => '1',
                'max_value'   => $_product->get_max_purchase_quantity(),
            ), $_product );
        ?>
    </div>
</div>

        </div>
        <?php endif; endforeach; ?>

        <?php do_action( 'woocommerce_after_cart_table' ); ?>
    </div>

    <!-- RIGHT: Cart Totals + Coupon -->
    <div class="cart-right">
        <?php do_action( 'woocommerce_cart_collaterals' ); ?>

        <div class="cart-coupon">
            <?php if ( wc_coupons_enabled() ) { ?>
                <label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
                <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
                <button type="submit" class="button" name="apply_coupon"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
            <?php } ?>
        </div>

        <?php woocommerce_cart_totals(); ?>

        <button type="submit" class="button checkout-button" name="update_cart">
            <?php esc_html_e( 'Update Cart', 'woocommerce' ); ?>
        </button>
    </div>

</div>
<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
</form>
