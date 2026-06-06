<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

function nubu_fetch_products() {
	$args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );

    $product_ids = get_posts($args);
    $products = array();

    // Add the "Please select" option at the beginning
    $products[''] = 'Please select a product';

    foreach ($product_ids as $product_id) {
        $title = get_the_title($product_id);
        $products[$product_id] = $title;
    }

    return $products;
}

function nubu_product_custom_fields() {
	
	Container::make( 'theme_options', __( 'NUBU Panel' ) )
	->set_page_parent('nubu-panel')      // place under parent 
    ->set_page_file('nubu-panel')
	->set_page_menu_position( 80 )
    ->add_fields([
		Field::make( 'text', 'hcp_ghl_api_key', __( 'GHL API KEY' ) ),
        Field::make( 'text', 'unleashed_api_id', __( 'Unleashed API ID' ) ),
        Field::make( 'text', 'unleashed_api_key', __( 'Unleashed API Key' ) ),
        Field::make( 'text', 'unleashed_max_products', __( 'Unleased Max Products' ) ),
        Field::make('text', 'unleashed_out_of_stock_message', 'Out of Stock Message'),
        Field::make( 'text', 'unleashed_supported_product_types', __( 'Unleashed Supported Product Groups' ) )
        ->help_text("Comma seperated list of product groups to import import from Unleashed. Case insensitive"),
        
        //Field::make( 'text', 'prospect_api_key', __( 'Prospect API Key'))
        //->help_text("DEPRECATED. This is the API key that will be used to authenticate with Prospect. Obtained <a href='https://crm.prospect365.com/view/User/CB/Security'>here</a> whilst logged in."),
        
        Field::make('text', 'mailchimp_api_key', __("Mailchimp API Key")),
        Field::make('text', 'mailchimp_server_prefix', __("Mailchimp Server Prefix"))->set_width(33),
        Field::make('text', 'mailchimp_list_id', __("Mailchimp List ID"))->set_width(33),
        Field::make( 'select', 'mailchimp_initial_status', 'Mailchimp Initial Status' )
            ->set_options([
                'subscribed' => 'Subscribed',
                'pending' => 'Pending',
                'transactional' => 'Transactional',
	        ])->set_width(33),
        
        Field::make( 'text', 'registration_form_id', __( 'Registration Form ID')),
        
        Field::make('text', 'trade_account_form_url', 'Trade Account Application Form URL')
        ->help_text("This is the URL of the form that non unleashed users see instead of buy now"),
        
        Field::make('association', 'default_catalog', 'Default Catalog')
        ->set_types([[
            'type' => 'term',
            'taxonomy' => 'catalog',
        ]])
        //->set_min(1)
        ->set_max(1)
        ->help_text("This is the default catalog that will be assigned to all users when they register."),  
    ]);
	
	Container::make('post_meta', __('Education Resource Fields'))
    ->where('post_type', "=", "education-resource")
    ->add_fields([
        Field::make('image', 'icon', 'Icon')->set_value_type( 'id' )->set_width(100)
    ]);
	
	Container::make('post_meta', __('Section 29'))
    ->where('post_type', "=", "product")
    ->add_fields([
        Field::make('checkbox', 'section_29_required', 'Section 29 required')->set_width(100)
    ]);

    Container::make('post_meta', __('Pricing Fields'))
    ->where('post_type', "=", "product")
    ->add_fields([
        Field::make('text', 'default_price', 'Default Price')->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier1', 'Price Tier 1')->set_width(20),
        Field::make('text', 'sellpricetier2', 'Price Tier 2')->set_width(20),
        Field::make('text', 'sellpricetier3', 'Price Tier 3')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier4', 'Price Tier 4')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier5', 'Price Tier 5')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier6', 'Price Tier 6')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier7', 'Price Tier 7')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier8', 'Price Tier 8')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier9', 'Price Tier 9')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'sellpricetier10', 'Price Tier 10')->set_width(20)->set_attribute('readOnly', true),
        Field::make('text', 'customer_prices', 'Customer Prices')->set_attribute('readOnly', true), /** @todo Hide this from the page somehow? */
    ]);
    
    Container::make('post_meta', __('Unleashed Fields'))
    ->where('post_type', "=", "product")
    ->add_fields([
        Field::make('text', 'unleashed_product_code', 'Product Code')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_guid', 'Product Guid')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_barcode', 'Barcode')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_product_group', 'Product Group')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_product_description', 'Product Description')->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_image_url', 'Image URL')->set_attribute('readOnly', true),
        Field::make('textarea', 'unleashed_notes', 'Notes')->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_sellable', 'Sellable')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_minimum_sale_quantity', 'Minimum Sale Quantity')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_minimum_order_quantity', 'Minimum Order Quantity')->set_width(25)->set_attribute('readOnly', true),
        Field::make('text', 'unleashed_available_qty', 'Available Quantity')->set_width(25)->set_attribute('readOnly', true),
    ]);
	
	Container::make('post_meta', __('Custom Unleashed Fields'))
    ->where('post_type', "=", "product")
    ->add_fields([
		Field::make('text', 'single_product_id', 'Single Product ID')->set_width(33),
        Field::make('text', 'lowest_threshold', 'Lowest Threshold')->set_width(33),
        Field::make('text', 'average_threshold', 'Average Threshold')->set_width(33),
		Field::make( 'select', 'alternate_product_id', 'Alternate Product' )->add_options( 'nubu_fetch_products' )->set_classes( 'nubu_alternate_products' )->set_width(100)
    ]);

    $dropdown_help = "Can be converted to a drop down with pre defined options";
    Container::make('post_meta', __('Overwrites'))
    ->where('post_type', "=", "product")
    ->add_fields([
        Field::make('checkbox', 'visible', 'Visible')->set_width(33),
        Field::make( 'media_gallery', 'gallery', 'Media Gallery')
        ->help_text("If no images are defined in gallery, gallery will fall back to featured image. If no featured image defined, image will fall back to the unleashed imported Image URL"),
        Field::make('textarea', 'dosage_form', 'Dosage')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'pack_size', 'Pack Size')->set_width(33)
        ->help_text("Optional"),
        Field::make('textarea', 'pharmacode', 'Pharmacode')->set_width(33)
        ->help_text("Optional. this can be populated from unleashed if we know the field"),
        Field::make('textarea', 'composition_percentage', 'Composition (% w/w)')->set_width(33)
        ->help_text("Optional."),
        Field::make('textarea', 'composition_mgml', 'Composition (mg/ml)')->set_width(33)
        ->help_text("Optional."),
        Field::make('textarea', 'composition_mgg', 'Composition (mg/g)')->set_width(33)
        ->help_text("Optional."),
        Field::make('textarea', 'type', 'Type')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'ratio', 'Ratio')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'storage_conditions', 'Storage Conditions')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'shelf_life', 'Shelf Life')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'verified_for', 'Verified For')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'terpenes', 'Dominant Terpenes')->set_width(33)
        ->help_text("Optional. $dropdown_help"),
        Field::make('textarea', 'total_active_ingredients', 'Total Active Ingredients')->set_width(33)
        ->help_text("Optional."),
        Field::make('textarea', 'available_through', 'Available Through')->set_width(33)
        ->help_text("Optional."),
        Field::make('textarea', 'composition', 'Composition')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'barcode', 'Barcode')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'country_of_origin', 'Country of origin')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'country_of_cultivation', 'Country of cultivation')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'carrier_oil', 'Carrier oil')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'rrp', 'RRP (inc gst)')->set_width(33)
        ->help_text("Optional."),
		Field::make('textarea', 'lineage', 'Lineage')->set_width(33)
        ->help_text("Optional."),
        Field::make('complex', 'additional_attributes', 'Additional Attributes')
                ->add_fields([
                    Field::make('text', 'label', 'Label')->set_width(25),
                    Field::make('textarea', 'output', 'Value')->set_width(50),
                ])
                ->set_layout('tabbed-vertical')
                ->set_header_template( '
                    <%- $_index %><% if (label) { %>
                    : <%- label %>
                    <% } %>
                ' ),
        Field::make('complex', 'downloads', 'Downloads')
            ->add_fields([
                Field::make('file', 'file', 'Value')->set_width(30),
                Field::make('text', 'label', 'Label')->set_width(70)
            ])
            ->set_layout('tabbed-vertical')
            ->set_header_template( '
                    <%- $_index %><% if (label) { %>: <%- label %><% } %>
                ' ),
    ]);
	
	$default_catalog = carbon_get_theme_option('default_catalog');
    
    Container::make( 'user_meta', 'Nubu data' )
	->add_fields([
		Field::make('checkbox', 'show_prescription_form', 'Show Prescription Form'),
        Field::make('text', 'registration_mobile', 'Registration Mobile Number'),
        Field::make('text', 'registration_number', 'Registration Professional Number'),
        Field::make('text', 'registration_role', 'Registration Role'),
        Field::make('text', 'registration_clinic', 'Registration Clinic'),
        Field::make('text', 'unleashed_customer_code', 'Unleashed Customer Code'),
        Field::make('text', 'unleashed_customer_guid', 'Unleashed Customer Guid'),
        Field::make('text', 'mailchimp_user_id', 'Mailchimp User ID'),
        Field::make('text', 'sell_price_tier_reference', 'Sell Price Tier'),
        Field::make('association', 'catalog', 'Catalog')
        ->set_types([[
            'type' => 'term',
            'taxonomy' => 'catalog',
        ]])->set_default_value( $default_catalog )
        
    ] );
	
	Container::make( 'user_meta', 'Products Minimum Quantity' )
	->add_fields([
        Field::make('complex', 'products_min_quantity', 'Products Minimum Quantity')
            ->add_fields([
                Field::make('association', 'product_id', 'Product')->set_types( array(
        array(
            'type'      => 'post',
            'post_type' => 'product',
        )
    ) )->set_width(100),
                Field::make('text', 'quantity', 'Quantity')->set_width(100),
            ])
            ->set_layout('tabbed-vertical'),
    ]);

    Container::make( 'term_meta', 'Image' )
        ->where( 'term_taxonomy', '=', 'brand' )
        ->add_fields([
            Field::make( 'image', 'term_image', 'Image' )->help_text("Select a '.png' format image with a transparent background.")
        ]);

    Container::make( 'term_meta', 'Storefront Feature' )
        ->where( 'term_taxonomy', '=', 'product_cat' )
        ->add_fields([
            Field::make('checkbox', 'featured', 'Feature')->set_width(50)->help_text("Feature this category on the StoreFront"),
            Field::make( 'select', 'priority', 'Priority' )->help_text("The lower the number, the higher the priority. 0 is the highest priority.")->set_width(50)
            ->set_options([
                '0' => 0,
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
            ]),
            Field::make( 'image', 'term_image', 'Archive Grid Image' ),
            Field::make( 'image', 'term_logo', 'Archive Logo Image' ),
            Field::make( 'select', 'archive_priority', 'Archive Priority' )->help_text("The lower the number, the higher the priority. 0 is the highest priority.")
            ->set_options([
                '0' => 0,
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
            ]),
        ]);
    
    Container::make( 'term_meta', 'Strength Table' )
        ->where( 'term_taxonomy', '=', 'strength' )
        ->add_fields([
            Field::make('text', 'ratio', 'Ratio'),
            Field::make( 'select', 'priority', 'Priority' )
            ->help_text("The lower the number, the higher the priority. 0 is the highest priority.")->set_width(50)
            ->set_options([
                '0' => 0,
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
	        ])
        ]);
	
	Container::make( 'term_meta', 'Color Scheme' )
        ->where( 'term_taxonomy', '=', 'announcement-category' )
        ->add_fields([
            Field::make( 'select', 'color_scheme', 'Color Scheme' )
            ->help_text("Select the color scheme.")->set_width(100)
            ->set_options([
                'red' => 'Red',
				'green' => 'Green',
				'blue' => 'Blue',
	        ])
        ]);

}
add_action( 'carbon_fields_register_fields', 'nubu_product_custom_fields' );

function nubu_add_product_metabox() {
	add_meta_box(
		'nubu_product_tag_metabox',
		__( 'Product Tag', 'woocommerce' ),
		'nubu_render_product_metabox',
		'product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'nubu_add_product_metabox' );

function nubu_render_product_metabox( $post ) {
	wp_nonce_field( 'nubu_product_tag_save', 'nubu_product_tag_nonce' );

	$tag_text = get_post_meta( $post->ID, '_nubu_tag_text', true );
	$tag_color = get_post_meta( $post->ID, '_nubu_tag_color', true );
	// to make tag enabled initially.
	if ( '' === get_post_meta( $post->ID, '_nubu_tag_switch', true ) ) {
		update_post_meta( $post->ID, '_nubu_tag_switch', 'yes' );
	}
	$tag_switch = get_post_meta( $post->ID, '_nubu_tag_switch', true );

	?>
	<label for="nubu_tag_switch"><strong><?php esc_html_e( 'Enable Tag', 'woocommmerce' ); ?></strong></label>
	<label class="nubu_cust_switch">
		<input name="nubu_tag_switch" type="checkbox" <?php echo( 'yes' === $tag_switch  ? 'checked' : '');?> class="nubu_tag_switcher" id="nubu_tag_switch">
		<span class="nubu_cust_slider"></span>
	</label>
	<p>
		<label for="nubu_tag_text"><strong><?php esc_html_e( 'Tag Text', 'woocommerce' ); ?></strong></label>
		<input type="text" id="nubu_tag_text" name="nubu_tag_text" value="<?php echo esc_attr( $tag_text ); ?>" style="width:100%;" />
		<small class="description"><?php esc_html_e( 'Enter tag text to show on this product (leave empty to hide).', 'woocommerce' ); ?></small>
	</p>
	<p>
		<label for="nubu_tag_color"><strong><?php esc_html_e( 'Tag Background Color', 'gh-product-tag' ); ?></strong></label>
		<input type="text" id="nubu_tag_color" class="nubu-color-field" name="nubu_tag_color" value="<?php echo esc_attr( $tag_color ); ?>" style="width:100%;" />
		<small class="description"><?php esc_html_e( 'Optional: per-product color overrides global color.', 'woocommerce' ); ?></small>
	</p>
	<?php
}

function nubu_save_product_meta( $post_id, $post ) {
	// verify nonce
	if ( ! isset( $_POST['nubu_product_tag_nonce'] ) || ! wp_verify_nonce( $_POST['nubu_product_tag_nonce'], 'nubu_product_tag_save' ) ) {
		return;
	}
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['nubu_tag_text'] ) ) {
		$text = sanitize_text_field( wp_unslash( $_POST['nubu_tag_text'] ) );
		update_post_meta( $post_id, '_nubu_tag_text', $text );
	} else {
		delete_post_meta( $post_id, '_nubu_tag_text' );
	}
	
	if ( isset( $_POST['nubu_tag_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['nubu_tag_color'] ) );
		if ( $color ) update_post_meta( $post_id, '_nubu_tag_color', $color );
		else delete_post_meta( $post_id, '_nubu_tag_color' );
	}
	
	if ( isset( $_POST['nubu_tag_switch'] ) ) {
		update_post_meta( $post_id, '_nubu_tag_switch', 'yes' );
	} else {
		update_post_meta( $post_id, '_nubu_tag_switch', 'no' );
	}
}
add_action( 'save_post_product', 'nubu_save_product_meta', 10, 2 );
