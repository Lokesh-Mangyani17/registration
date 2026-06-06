<?php
/* Alter product price according to unleashed data */
if ( ! function_exists( 'nubu_alter_product_price' ) ) {
    function nubu_alter_product_price( $price, $product ) {
        if ( ! is_user_logged_in() || is_cart() || is_checkout() ) {
            return $price;
		}
		
        $quantity                   = 1;
        $current_user               = wp_get_current_user();
        $default_price              = $product->get_meta('_default_price');
        $unleashed_customer_guid    = $current_user->_unleashed_customer_guid;
        $unleashed_customer_tier    = $current_user->_sell_price_tier_reference;
        $customer_specific_price    = INF;
    
        if ( ! empty( $product->get_meta('_customer_prices') ) ) {
            $customer_prices = json_decode( $product->get_meta('_customer_prices'), true);
    
            $applicable_prices = [];
            if ( ! empty( $unleashed_customer_guid ) && array_key_exists( $unleashed_customer_guid, $customer_prices ) ) {
                $applicable_prices = array_merge( $applicable_prices, $customer_prices[$unleashed_customer_guid] );
            }
    
            // There may not be any point checking 'general' if the customer
            // specific price exists. Would only serve to protect against
            // some misentered data; current site only shows either
            // the customer specific quanrity arrays or general
            if ( array_key_exists( 'general', $customer_prices) ) {
                $applicable_prices = array_merge( $applicable_prices, $customer_prices['general'] );
            }
    
            foreach ( $applicable_prices as $applicable_price ) {
                if ( $applicable_price['MinQty'] <= $quantity ) {
                    $customer_specific_price = min( $customer_specific_price, $applicable_price['Price'] );
                }
            }
        }
    
        $customer_tier_price = INF;
        if ( ! empty( $unleashed_customer_tier ) ) {
            $product_tier_price     = $product->get_meta( '_' . strtolower( $unleashed_customer_tier ) );
            $customer_tier_price    = $product_tier_price && '' !== $product_tier_price ? $product_tier_price : INF;
        }
        
        // Need to check sell price tier -> How does that get on the user?
        $sell_price_tier_price = INF;
        $price = min( $default_price, $customer_specific_price, $customer_tier_price, $sell_price_tier_price );
        //$price = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>'.$final_price.'</bdi></span>';
        
        return $price;
    }
    add_filter( 'woocommerce_product_get_price', 'nubu_alter_product_price', 10, 2 );
}

function nubu_alter_cart_product_price( $price, $product, $quantity = 1 ) {
    if ( ! is_user_logged_in() ) {
        return $price;
    }
    $current_user               = wp_get_current_user();
    $default_price              = $product->get_meta('_default_price');
    $unleashed_customer_guid    = $current_user->_unleashed_customer_guid;
    $unleashed_customer_tier    = $current_user->_sell_price_tier_reference;
    $customer_specific_price    = INF;

    if ( ! empty( $product->get_meta('_customer_prices') ) ) {
        $customer_prices = json_decode( $product->get_meta('_customer_prices'), true);

        $applicable_prices = [];
        if ( ! empty( $unleashed_customer_guid ) && array_key_exists( $unleashed_customer_guid, $customer_prices ) ) {
            $applicable_prices = array_merge( $applicable_prices, $customer_prices[$unleashed_customer_guid] );
        }

        // There may not be any point checking 'general' if the customer
        // specific price exists. Would only serve to protect against
        // some misentered data; current site only shows either
        // the customer specific quanrity arrays or general
        if ( array_key_exists( 'general', $customer_prices) ) {
            $applicable_prices = array_merge( $applicable_prices, $customer_prices['general'] );
        }

        foreach ( $applicable_prices as $applicable_price ) {
            if ( $applicable_price['MinQty'] <= $quantity ) {
                $customer_specific_price = min( $customer_specific_price, $applicable_price['Price'] );
            }
        }
    }

    $customer_tier_price = INF;
    if ( ! empty( $unleashed_customer_tier ) ) {
        $product_tier_price     = $product->get_meta( '_' . strtolower( $unleashed_customer_tier ) );
        $customer_tier_price    = $product_tier_price && '' !== $product_tier_price ? $product_tier_price : INF;
    }
    
    // Need to check sell price tier -> How does that get on the user?
    $sell_price_tier_price = INF;
    $price = min( $default_price, $customer_specific_price, $customer_tier_price, $sell_price_tier_price );
    //$price = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>'.$final_price.'</bdi></span>';
    
    return $price;
}

function nubu_cart_item_price_filter( $price, $cart_item, $cart_item_key ) {
    $quantity = $cart_item['quantity'];
    $product = wc_get_product( $cart_item['product_id'] );
    $price = nubu_alter_cart_product_price( $price, $product, $quantity );
    return wc_price( $price );
}
add_filter( 'woocommerce_cart_item_price', 'nubu_cart_item_price_filter', 10, 3 );


function nubu_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
    $product = $cart_item['data'];
    $quantity = $cart_item['quantity'];
    
    $product_price  = $product->get_price();
    $product_price  = nubu_alter_cart_product_price( $product_price, $product, $quantity );
    $row_price      = (float) $product_price * (float) $quantity;
    return wc_price( $row_price );
}
add_filter( 'woocommerce_cart_item_subtotal', 'nubu_cart_item_subtotal', 10, 3 );


// Modify product price based on quantity in the cart
function custom_change_product_price_based_on_quantity() {
    // Loop through each cart item
    
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    
    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;
    
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        // Get the product object from the cart item
        $product = $cart_item['data'];
        
        // Get the quantity of the product in the cart
        $quantity = $cart_item['quantity'];
        
        $product_price  = $product->get_price();
        $new_price  	= nubu_alter_cart_product_price( $product_price, $product, $quantity );
        $product->set_price( $new_price );
    }
    
}
add_action( 'woocommerce_before_calculate_totals', 'custom_change_product_price_based_on_quantity' );

/* Add suffix to the price */
if ( ! function_exists( 'nubu_price_suffix' ) ) {
    function nubu_price_suffix( $price, $product ) {
        $price .= '<span class="nubu_price_suffix"> excl tax.</span>';
        return $price;
    }
    //add_filter( 'woocommerce_price_format', 'nubu_price_suffix', 10, 2 );
}

add_filter( 'woocommerce_get_price_suffix', function( $suffix, $product ) {
    if ( is_shop() || is_product_category() || is_product_tag() || is_archive() ) {
        return ''; // remove suffix
    }
    return $suffix; // keep suffix elsewhere
}, 10, 2 );

function nubu_customize_checkout_fields( $fields ) {
    // Add a custom text field
    
    $fields['shipping']['shipping_suburb'] = array(
        'type'        => 'text',
        'label'       => __('Suburb', 'woocommerce'),
        'placeholder' => __('Suburb', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide', 'address-field'),
        'clear'       => true
    );
    $fields['shipping']['shipping_delivery_instructions'] = array(
        'type'        => 'text',
        'label'       => __('Delivery Instructions', 'woocommerce'),
        'placeholder' => __('Delivery Instructions', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'clear'       => true
    );
	
	$fields['billing']['billing_suburb'] = array(
        'type'        => 'text',
        'label'       => __('Suburb', 'woocommerce'),
        'placeholder' => __('Suburb', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide', 'address-field'),
        'clear'       => true
    );
    $fields['billing']['billing_delivery_instructions'] = array(
        'type'        => 'text',
        'label'       => __('Delivery Instructions', 'woocommerce'),
        'placeholder' => __('Delivery Instructions', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'clear'       => true
    );
    
    if ( isset( $fields['billing'] ) ) {
		unset( $fields['billing']['billing_email'] );
		unset( $fields['billing']['billing_company'] );
		unset( $fields['billing']['billing_phone'] );
    }
    
	/*
    $unset_fields = array(
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
    );
    
    foreach( $unset_fields as $field ) {
        if ( isset( $fields['shipping'][$field] ) ) {
            unset( $fields['shipping'][$field] );
        }
    }
    
    if ( isset( $fields['billing'] ) ) {
        unset( $fields['billing'] );
    }
	*/
    
	$fields['billing']['billing_address_1']['priority'] = 10;
    $fields['billing']['billing_address_2']['priority'] = 20;
    $fields['billing']['billing_suburb']['priority'] = 60;
    $fields['billing']['billing_country']['priority'] = 90;
	
    $fields['shipping']['shipping_address_1']['priority'] = 10;
    $fields['shipping']['shipping_address_2']['priority'] = 20;
    $fields['shipping']['shipping_suburb']['priority'] = 60;
    $fields['shipping']['shipping_country']['priority'] = 90;
	
	if ( is_user_logged_in() ) {
        // Get the current user
        $current_user = wp_get_current_user();
        
        // Prefill the billing fields with the current user's information
        $fields['billing']['billing_first_name']['default'] = $current_user->user_firstname;
        $fields['billing']['billing_last_name']['default'] = $current_user->user_lastname;
		$fields['billing']['billing_address_1']['default'] = get_user_meta( $current_user->ID, 'shipping_address_1', true );
		$fields['billing']['billing_address_2']['default'] = get_user_meta( $current_user->ID, 'shipping_address_2', true );
		$fields['billing']['billing_suburb']['default'] = get_user_meta( $current_user->ID, 'shipping_suburb', true );
		$fields['billing']['billing_city']['default'] = get_user_meta( $current_user->ID, 'shipping_city', true );
		$fields['billing']['billing_state']['default'] = get_user_meta( $current_user->ID, 'shipping_state', true );
		$fields['billing']['billing_postcode']['default'] = get_user_meta( $current_user->ID, 'shipping_postcode', true );
		$fields['billing']['billing_delivery_instructions']['default'] = get_user_meta( $current_user->ID, 'shipping_delivery_instructions', true );
		$fields['billing']['billing_country']['default'] = get_user_meta( $current_user->ID, 'shipping_country', true );
    }
    
    /*    
    $fields['shipping']['shipping_country']['label'] = __('Country', 'woocommerce');
    $fields['shipping']['shipping_city']['label'] = __('City', 'woocommerce');
    $fields['shipping']['shipping_state']['label'] = __('Region', 'woocommerce');
    $fields['shipping']['shipping_postcode']['label'] = __('Postal Code', 'woocommerce');
    */
    
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'nubu_customize_checkout_fields' );


function nubu_woocommerce_default_address_fields( $fields ) {
    $fields['city']['label'] = __('City', 'woocommerce');
    $fields['state']['label'] = __('Region', 'woocommerce');
    $fields['postcode']['label'] = __('Postal Code', 'woocommerce');
    return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'nubu_woocommerce_default_address_fields' );


/* Create a GUID when an order was created */
if ( ! function_exists( 'nubu_woocommerce_checkout_update_order_meta' ) ) {
    function nubu_woocommerce_checkout_update_order_meta( $order_id, $data ) {
        $order = wc_get_order( $order_id );
        if ( ! $order->get_meta( 'order_guid', true ) ) {
            $order->update_meta_data( 'order_guid', sanitize_text_field( nubu_generate_guid() ) );
            //$order->save_meta_data();
        }
        if ( ! empty( $data['shipping_delivery_instructions'] ) ) {
            $order->update_meta_data( 'delivery_instructions', sanitize_text_field( $data['shipping_delivery_instructions'] ) );
        }
        if ( ! empty( $data['shipping_suburb'] ) ) {
            $order->update_meta_data( 'suburb', sanitize_text_field( $data['shipping_suburb'] ) );
        }

		if ( ! empty( $data['billing_delivery_instructions'] ) ) {
            $order->update_meta_data( 'delivery_instructions', sanitize_text_field( $data['billing_delivery_instructions'] ) );
        }
        if ( ! empty( $data['billing_suburb'] ) ) {
            $order->update_meta_data( 'suburb', sanitize_text_field( $data['billing_suburb'] ) );
        }
    }
    add_action( 'woocommerce_checkout_update_order_meta', 'nubu_woocommerce_checkout_update_order_meta', 10, 2 );
}

function nubu_woocommerce_localisation_address_formats($formats) {
   $formats['NZ'] = "{name}\n{company}\n{address_1}\n{address_2}\n{suburb}, {city} {postcode}\n{state}, {country}";
   return $formats;
}
//add_filter( 'woocommerce_localisation_address_formats', 'nubu_woocommerce_localisation_address_formats' );

function nubu_woocommerce_formatted_address_replacements( $replace, $args ) {
    $replace['{suburb}'] = esc_html( $args['suburb'] );
    return $replace;
}
//add_filter( 'woocommerce_formatted_address_replacements', 'nubu_woocommerce_formatted_address_replacements', 10, 2 );


function nubu_term_description( $atts ) {
    if ( is_tax() ) {
        return term_description();
    }
    return '';
}
add_shortcode( 'term_description', 'nubu_term_description' );

function nubu_term_image( $atts ) {
    if ( is_tax() ) {
        $term           = get_queried_object();
        $term_image_id  = carbon_get_term_meta( $term->term_id, 'term_image' );
        return wp_get_attachment_image( $term_image_id, 'large' );
    }
    return '';
}
add_shortcode( 'term_image', 'nubu_term_image' );

function nubu_product_filter( $atts ) {
    $atts = shortcode_atts( array(
		'filters' => '',
	), $atts, 'product_filters' );
	
	$filters = $atts['filters'];
	$filters = explode( ',', $filters );
	
	return nubu_product_filters_html( $filters );
}
add_shortcode( 'product_filters', 'nubu_product_filter' );


/*
add_filter( 'woocommerce_cart_needs_payment', '__return_true' );

add_action( 'woocommerce_checkout_init', 'force_display_shipping_fields' );

function force_display_shipping_fields() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        // Force shipping fields to be displayed
        add_filter( 'woocommerce_cart_shipping_method_full_label', 'add_shipping_fields_forcefully' );
    }
}

function add_shipping_fields_forcefully( $label ) {
    // You can modify the shipping method label here if needed
    return $label;
}
*/

function nubu_add_custom_class_to_products( $classes, $class, $post_id ) {
	$global = nubu_get_product_tag_global_options();
	if ( 'product' === get_post_type( $post_id ) ) {
		$custom_bg = get_post_meta( $post_id, '_nubu_tag_color', true ); 
		$show_tag  = get_post_meta( $post_id, '_nubu_tag_switch', true );
		$classes[] = 'nubu_cust_pos_'.$global['tag_position'];
		$classes[] = 'nubu_cust_shape_'.$global['tag_shape'];
		$classes[] = 'nubu_cust_bgcolor_'.$global['tag_bg_color'];
		$classes[] = 'nubu_cust_color_'.$global['tag_text_color'];
		$classes[] = 'nubu_cust_bgcolorimp_'.$custom_bg;
		$classes[] = 'nubu_cust_size_'.$global['tag_font_size']; 
		$classes[] = 'nubu_cust_show_'.$show_tag; 
	}
	return $classes;
}
//add_filter( 'post_class', 'nubu_add_custom_class_to_products', 20, 3 );

function nubu_add_product_tag_classes( $classes ) {
	global $product;
	if ( ! $product || ! is_object( $product ) ) {
		return array();
	}
	$global = nubu_get_product_tag_global_options();
	$post_id = $product->get_id();
	if ( 'product' === get_post_type( $post_id ) ) {
		$custom_bg = get_post_meta( $post_id, '_nubu_tag_color', true ); 
		$show_tag  = get_post_meta( $post_id, '_nubu_tag_switch', true );
		$classes[] = 'nubu_cust_pos_'.$global['tag_position'];
		$classes[] = 'nubu_cust_shape_'.$global['tag_shape'];
		$classes[] = 'nubu_cust_bgcolor_'.$global['tag_bg_color'];
		$classes[] = 'nubu_cust_color_'.$global['tag_text_color'];
		$classes[] = 'nubu_cust_bgcolorimp_'.$custom_bg;
		//$classes[] = 'nubu_cust_size_'.$global['tag_font_size']; 
		$classes[] = 'nubu_cust_show_'.$show_tag; 
	}
	return $classes;
}
add_filter( 'nubu_product_tag_classes', 'nubu_add_product_tag_classes' );

function nubu_get_product_tag_global_options() {
	$defaults = array(
		'tag_position' => 'top-right',
		'tag_shape' => 'rectangle',
		//'tag_font_size' => 10,
		'tag_bg_color' => '#ff0000',
		'tag_text_color' => ''
	);
	$opts = get_option( 'nubu_product_tag_options', array() );
	return wp_parse_args( $opts, $defaults );
}

function nubu_product_custom_tag( $image, $attachment_id ) {
	global $product;

	if ( ! $product || ! is_object( $product ) ) {
		return $image;
	}

	$custom_text = get_post_meta( $product->get_id(), '_nubu_tag_text', true );
	if ( ! empty( $custom_text ) ) {
		$classes = nubu_add_product_tag_classes( array() );
		if ( is_array( $classes ) ) {
			$classes = implode( ' ', $classes );
		}
		$html = '<div class="nubu_product_tag_wrapper '. esc_attr( $classes ) .'"><span class="nubu_custom_tag_onsale">' . esc_html( $custom_text ) . '</span></div>';
		$image .= $html;	
	}

	return $image;
}
add_filter( 'woocommerce_product_get_image', 'nubu_product_custom_tag', 10, 2 );
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'nubu_product_custom_tag', 10, 2 );
add_filter( 'woocommerce_placeholder_img', 'nubu_product_custom_tag', 10, 2 );

function nubu_product_tag_callback( $atts ) {
	global $product;

	if ( ! $product || ! is_object( $product ) ) {
		return '';
	}

	$custom_text = get_post_meta( $product->get_id(), '_nubu_tag_text', true );
	if ( ! empty( $custom_text ) ) {
		$classes = apply_filters( 'nubu_product_tag_classes', array() );
		if ( is_array( $classes ) ) {
			$classes = implode( ' ', $classes );
		}
		$html = '<div class="nubu_product_tag_wrapper '. esc_attr( $classes ) .'"><span class="nubu_custom_tag_onsale">' . esc_html( $custom_text ) . '</span></div>';
		return $html;
	}

	return '';
}
add_shortcode( 'nubu_product_tag', 'nubu_product_tag_callback' );