<?php
/* Add custom columns to the products admin area */
if ( ! function_exists('nubu_product_columns') ) {
    function nubu_product_columns($columns) {
        // Add new columns
        //$columns['unleashed_product_code'] = __('Product Code');
        //$columns['default_price'] = __('Default Price');
        //$columns['unleashed_product_group'] = __('Product Group');
        //$columns['unleashed_sellable'] = __('Sellable');
        //$columns['legend'] = "<- UL || WP ->";
        $columns['visible'] = __('Visible');
        //$columns['catalog'] = __('Catalog');
        //$columns['brand'] = __('Brands');
        
        // Remove existing columns
        unset($columns['date']); 
        unset($columns['is_arc']);
        unset($columns['product_tag']);
        unset($columns['featured']);
		unset($columns['catalog']);
        unset($columns['brand']);
        // Remove the 'date' column

        return $columns;
    }
    add_filter('manage_product_posts_columns', 'nubu_product_columns', 20, 1 );
}

/* Displays custom columns values in the products admin area */
if ( ! function_exists( 'nubu_product_custom_columns' ) ) {
    function nubu_product_custom_columns($column, $post_id) {
        switch ($column) {
            case 'unleashed_product_code':
                $product_code = get_post_meta($post_id, '_unleashed_product_code', true);
                echo esc_html($product_code);
                break;
    		/*
            case 'default_price':
                $default_price = get_post_meta($post_id, '_default_price', true);
                echo esc_html($default_price);
                break;
    		
            case 'unleashed_product_group':
                $product_group = get_post_meta($post_id, '_unleashed_product_group', true);
                echo esc_html($product_group);
                break;
            
            case 'unleashed_sellable':
                $sellable = get_post_meta($post_id, '_unleashed_sellable', true);
                echo esc_html($sellable == 1 ? "yes": "no");
                break;
            case 'legend':
                echo "";
                break;
			*/
            case 'visible':
                $visible = get_post_meta($post_id, '_visible', true);
                echo esc_html($visible ? $visible : "no");
                break;
            
    		/*
            case 'catalog':
                $terms = wp_get_post_terms($post_id, 'catalog', array("fields" => "names"));
                echo esc_html(implode(', ', $terms));
                break;
    
            case 'brand':
                $terms = wp_get_post_terms($post_id, 'brand', array("fields" => "names"));
                echo esc_html(implode(', ', $terms));
                break;
			*/
    
        }
    }
    add_action('manage_product_posts_custom_column', 'nubu_product_custom_columns', 10, 2);
}

/* Adds prefix to the order number */
if ( ! function_exists( 'nubu_order_number_prefix' ) ) {
    function nubu_order_number_prefix( $order_id, $order ) {
       return esc_html( 'NUBU-' . $order_id );
    }
    add_filter( 'woocommerce_order_number', 'nubu_order_number_prefix', 10, 2 );   
}

// Display custom shipping field in order admin page
function display_custom_shipping_field_in_admin_order( $order ) {
    $suburb = $order->get_meta( 'suburb', true );
	$delivery_instructions = $order->get_meta( 'delivery_instructions', true );

    if ( ! empty( $suburb ) ) {
        echo '<p><strong>' . __('Suburb', 'woocommerce') . ':</strong> ' . esc_html( $suburb ) . '</p>';
    }
	if ( ! empty( $delivery_instructions ) ) {
        echo '<p><strong>' . __('Delivery Instructions', 'woocommerce') . ':</strong> ' . esc_html( $delivery_instructions ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'display_custom_shipping_field_in_admin_order' );

/* Displays custom order meta on the order page */
function nubu_display_order_meta($order) {
    $user_id = $order->get_user_id();
    echo '<p><strong>'.__('GUID').':</strong><br/>' . $order->get_meta( 'order_guid', true ) . '</p>';
    echo '<p><strong>'.__('Customer Code').':</strong><br/>' . get_user_meta( $user_id, 'unleashed_customer_code', true ) . '</p>';
    echo '<p><strong>'.__('Delivery Instructions').':</strong><br/>' . $order->get_meta( 'shipping_delivery_instructions', true ) . '</p>';
}
//add_action( 'woocommerce_admin_order_data_after_billing_address', 'nubu_display_order_meta', 10 );

function nubu_add_catalog_row_action( $actions, $tag ) {
	$action_url = wp_nonce_url( add_query_arg( [
		'term_id'  => $tag->term_id,
		'taxonomy' => $tag->taxonomy,
		'action'   => 'nubu_duplicate_term',
	], admin_url( 'admin-post.php' ) ), 'nubu-duplicate-term' );
	$actions['duplicate'] = sprintf( '<a href="%s">%s</a>', esc_url( $action_url ), 'Duplicate' );
	return $actions;
}
add_filter( 'catalog_row_actions', 'nubu_add_catalog_row_action', 10, 2 );

function nubu_duplicate_term() {
	check_admin_referer( 'nubu-duplicate-term' );
	$term_id  = absint( wp_unslash( $_REQUEST['term_id'] ) );
	$taxonomy = sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) );
	
	$new_term = nubu_duplicate_catalog_with_products( $term_id );
	if ( is_wp_error( $new_term ) ) {
		wp_die( 'Error duplicating catalog: ' . $new_term->get_error_message() );
	} else {
		// Redirect back to the catalog list
		wp_redirect( admin_url('edit-tags.php?taxonomy=catalog&post_type=product') );
		exit;
	}
}
add_action( 'admin_post_nubu_duplicate_term', 'nubu_duplicate_term' );

function nubu_duplicate_catalog_with_products( $term_id ) {
    $term = get_term( $term_id, 'catalog' );
    if ( ! $term || is_wp_error( $term ) ) {
        return new WP_Error( 'invalid_catalog', 'Catalog not found.' );
    }

    // Create new catalog
    $new_catalog = wp_insert_term( $term->name . ' - Copy', 'catalog');
    if ( is_wp_error( $new_catalog ) ) {
        return $new_catalog;
    }

    $new_catalog_id = $new_catalog['term_id'];

    // Get products assigned to the original catalog
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'catalog',
                'field' => 'term_id',
                'terms' => $term_id,
            ]
        ]
    ]);

    // Assign products to the new catalog
    foreach ( $products as $product ) {
        wp_set_object_terms( $product->ID, $new_catalog_id, 'catalog', true);
    }

    return $new_catalog_id;
}

function nubu_add_custom_taxonomy_filter_to_admin() {
    global $typenow;
    if ( $typenow == 'product' ) {
        $taxonomy = 'catalog';

        // Get all terms of the custom taxonomy
        $terms = get_terms(array(
            'taxonomy' => sanitize_text_field( $taxonomy ),
            'orderby' => 'name',
            'hide_empty' => false,
        ));

        // Display the dropdown filter
        if ( ! empty($terms) && ! is_wp_error($terms) ) {
            echo '<select name="' . esc_attr( $taxonomy ) . '" id="' . esc_attr( $taxonomy ) . '" class="postform">';
            echo '<option value="">' . __('Filter by ' . esc_attr( ucfirst( $taxonomy ) ), 'divi') . '</option>';
            foreach ($terms as $term) {
                // Set selected term if any
                $selected = (isset($_GET[$taxonomy]) && $_GET[$taxonomy] == $term->slug) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( $term->name ) . '</option>';
            }
            echo '</select>';
        }
    }
}
add_action('restrict_manage_posts', 'nubu_add_custom_taxonomy_filter_to_admin');

function nubu_remove_yoast_seo_posts_filter() {
    global $wpseo_meta_columns;

    if ( $wpseo_meta_columns ) {
        remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns, 'posts_filter_dropdown' ) );
		remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns, 'posts_filter_dropdown_readability' ) );
    }
}
add_action( 'admin_init', 'nubu_remove_yoast_seo_posts_filter', 20 );

function nubu_stop_processing_email_admin( $email_class ) {
    //remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_cancelled_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
	remove_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
}
add_action( 'woocommerce_email', 'nubu_stop_processing_email_admin' );