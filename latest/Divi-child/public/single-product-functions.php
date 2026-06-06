<?php
/* Shows Popup when product is out of stock */
if ( ! function_exists( 'nubu_out_of_stock_product_popup' ) ) {
	function nubu_out_of_stock_product_popup() {
		if ( is_singular( 'product' ) ) {
			global $product;
			$alternate_product_id 	= carbon_get_the_post_meta('alternate_product_id');
			$available_quantity		= intval( $product->get_meta( '_unleashed_available_qty' ) );
			$minimum_order_quantity = intval( $product->get_meta( 'unleashed_minimum_order_quantity' ) );
			if ( ( $available_quantity < 1 || $available_quantity < $minimum_order_quantity ) && ! empty( $alternate_product_id ) ) {
				if ( $product->is_on_backorder() ) {
					$status = 'on backorder';
				} else {
					$status = 'out of stock';
				}
				?>
				<div class="nubu_out_of_stock_product_modal_popup">
					<div class="nubu_out_of_stock_product_modal_popup_inner">
						<span class="nubu_out_of_stock_product_modal_popup_close_icon">&#10006;</span>
						<div class="nubu_alternate_product_wrapper">
								<h4>This product is <?php echo $status; ?>. Here’s our recommended alternative:</h4>
								<h2><a href="<?php echo get_permalink( $alternate_product_id ); ?>"><?php echo get_the_title( $alternate_product_id ); ?></a></h2>
							<div class="nubu_alternate_product_image">
								<a href="<?php echo get_permalink( $alternate_product_id ); ?>"><?php echo get_the_post_thumbnail( $alternate_product_id, 'medium'); ?></a>
							</div>
							<a class="nubu_alternate_product_button" href="<?php echo get_permalink( $alternate_product_id ); ?>">View Here</a>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}
	add_action('wp_footer', 'nubu_out_of_stock_product_popup');
}

/* Returns if product is public product */
if ( ! function_exists( 'nubu_is_public_product' ) ) {
	function nubu_is_public_product( $product_id ) {
		$product_categories = get_the_terms( $product_id, 'product_cat' );
		if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
			foreach ( $product_categories as $product_category ) {
				if ( $product_category->slug === 'public-product' ) {
					return true;
				}
			}
		}
		return false;
	}
}

/* Returns term id from term object */
if ( ! function_exists( 'nubu_return_term_id' ) ) {
    function nubu_return_term_id( $term ) {
        return $term->term_id;
    }
}

/* Renders Strength table on product page */
if ( ! function_exists( 'nubu_product_strength' ) ) {
    function nubu_product_strength( $atts ) {
        if ( is_singular( 'product' ) ) {
			/*
            $terms = get_terms( array(
                'taxonomy'      => 'strength',
                'hide_empty'    => false,
                'meta_key'      => '_priority',
                'orderby'       => 'meta_value',
                'order'         => 'ASC',
            ) );
            
            $product        = wc_get_product();
            $product_id     = $product->get_id();
            $strengths      = get_the_terms( $product_id, 'strength' );
            if ( ! is_wp_error( $strengths ) && ! empty( $strengths ) ) {
                $selected_strengths = array_map( 'nubu_return_term_id', $strengths );
                
                $html  = '<div class="nubu_product_strengths">';
                $html .= '<div class="nubu_product_strength_header">THC and CBD ratio</div>';
                foreach( $terms as $term ) {
                    $active = in_array( $term->term_id, $selected_strengths ) ? ' active' : '';
                    $html .= '<div class="nubu_product_strength'. esc_attr( $active ) .'">'. esc_html( $term->name ) .'</div>';
                }
                $html .= '</div>';
          
                return $html;
            }
			*/
			$product        = wc_get_product();
            $product_id     = $product->get_id();
            $strengths      = get_the_terms( $product_id, 'strength' );
			if ( ! is_wp_error( $strengths ) && ! empty( $strengths ) ) {
				$html  = '<div class="nubu_product_strengths">';
				foreach( $strengths as $strength ) {
					$html .= '<div class="nubu_product_strength '. esc_attr( $strength->slug ) .'">'. esc_html( $strength->name ) .'</div>';
				}
				$html .= '</div>';
				return $html;
			}
        }
    
        return '';
    }
    add_shortcode( 'product_strength', 'nubu_product_strength' );
}

/* Renders product specifications on product page */
if ( ! function_exists( 'nubu_product_specifications' ) ) {
    function nubu_product_specifications( $atts ) {
        if ( is_singular( 'product' ) ) {
            $product    = wc_get_product();
            $fields     = array(
                array('_dosage_form', 'Dosage form'),
                array('_pack_size', 'Pack size'),
                array('_pharmacode', 'Pharmacode'),
                array('_composition_percentage', 'Composition (% w/w)'),
                array('_composition_mgg', 'Composition (mg/g)'),
                array('_composition_mgml', 'Composition (mg/mL)'),
                array('_total_active_ingredients' , 'Total active ingredients'),
                array('_type', 'Type'),
                array('_terpenes', 'Dominant terpenes'),
                array('_shelf_life', 'Shelf life'),
                array('_storage_conditions', 'Storage conditions'),
                array('_verified_for', 'Verified for'),
                array('_available_through', 'Available through'),
                array('_ratio', 'Ratio'),
                array('_composition', 'Composition'),
				array('_barcode', 'Barcode'),
				array('_country_of_cultivation', 'Country of cultivation' ),
				array('_country_of_origin', 'Country of origin'),
				array('_carrier_oil', 'Carrier oil'),
				array('_rrp', 'RRP (inc gst)'),
				array('_lineage', 'Lineage'),
            );
            
            $html = '';
            foreach ( $fields as list($field, $label) ) {
                $value = $product->get_meta( sanitize_text_field( $field ) );
                if ( ! empty( $value ) ) {
                    $html .= '<div class="nubu_product_specification">';
                    $html .= sprintf(
                        '<div class="nubu_product_specification_label">%1$s</div>',
                        esc_html( $label )
                    );
                    $html .= sprintf(
                        '<div class="nubu_product_specification_value">%1$s</div>',
                        wp_kses_post( $value )
                    );
                    $html .= '</div>';
                }
            }
            
            $additional_attributes = carbon_get_the_post_meta('additional_attributes');
            if ( !empty( $additional_attributes ) ) {
                foreach ( $additional_attributes as $attribute) {
                    if ( ! empty( $attribute['label'] ) && ! empty( $attribute['output'] ) ) {
                        $html .= '<div class="nubu_product_specification">';
                        $html .= sprintf(
                            '<div class="nubu_product_specification_label">%1$s</div>',
                            esc_html( $attribute['label'] )
                        );
                        $html .= sprintf(
                            '<div class="nubu_product_specification_value">%1$s</div>',
                            esc_html( $attribute['output'] )
                        );
                        $html .= '</div>';
                    }
                }
            }
            
            return $html;
        }
        
        return '';
        
    }
    add_shortcode( 'product_specifications', 'nubu_product_specifications' );
}

/* Renders downloads on product page */
if ( ! function_exists( 'nubu_product_downloads' ) ) {
    function nubu_product_downloads($atts) {
        if ( is_singular( 'product' ) ) {
            $product    = wc_get_product();
            $product_id = $product->get_id();
            $downloads  = carbon_get_post_meta( $product_id, 'downloads' );
            if ( ! empty( $downloads ) ) {
                $html  = '<div class="nubu_product_downloads">';
                $html .= '<div class="nubu_product_downloads_header">Downloads (pdf)</div>';
                foreach ( $downloads as $download ) {
                    //$file_url = wp_get_attachment_url( $download['file'] );
					$file_url = '/view-pdf/?file=' . intval($download['file']);
                    $html .= sprintf(
                        '<div style="margin-top: 10px;"><span class="et-waypoint et_pb_animation_off et_pb_animation_off_tablet et_pb_animation_off_phone et-pb-icon et-animated" style="font-family: FontAwesome !important; font-size: 18px; margin-right: 10px;"></span> <a target="_blank" rel="noopener" href="%1$s">%2$s</a></div>',
                        esc_url( $file_url ),
                        esc_html( $download['label'] )
                    );
                }
                $html .= '</div>';
                return $html;
            }
        }
        return '';
    }
    add_shortcode( 'product_downloads', 'nubu_product_downloads' );
}

/* Renders brand link on product page */
if ( ! function_exists( 'nubu_brand_link' ) ) {
    function nubu_brand_link( $atts ) {
        if ( is_singular( 'product' ) ) {
            $product        = wc_get_product();
            $product_id     = $product->get_id();
            $brand_terms    = get_the_terms( $product_id, 'brand' );
            if ( ! is_wp_error( $brand_terms ) && ! empty( $brand_terms ) ) {
                $brand      = $brand_terms[0];
                $brand_link = get_term_link( $brand, 'brand' );
	            $brand_img 	= carbon_get_term_meta( $brand->term_id, 'term_image' );
                $html       = sprintf(
                    '<a href="%1$s" class="nubu_brand_link">About the brand%2$s</a>',
                    esc_url( $brand_link ),
					$brand_img ? wp_get_attachment_image( $brand_img, 'medium' ) : ''
                );
                return $html;
            }
        }
    
        return '';
    }
    add_shortcode( 'product_brand_link', 'nubu_brand_link' );
}

/* Renders product title on product page */
if ( ! function_exists( 'nubu_product_title' ) ) {
    function nubu_product_title( $atts ) {
        if ( is_singular( 'product' ) ) {
            $product        = wc_get_product();
            $product_id     = $product->get_id();
            $category_terms = get_the_terms( $product_id, 'product_cat' );
            
            if ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) {
                $category           = $category_terms[0];
                $category_link      = get_term_link( $category, 'product_cat' );
                $category_page_link = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url( $category_link ),
                    esc_html( $category->name )
                );
            } else {
                $category_page_link = '';
            }
            
            $products_page_link = home_url('/products');
            
            $html = sprintf(
                '<h1 class="nubu_product_title">
                    %1$s
                </h1>',
                esc_html( get_the_title( $product_id ) )
            );
            
            return $html;
        }
    
        return '';
    }
    add_shortcode( 'product_title', 'nubu_product_title' );
}

/* Alters stock status message */
if ( ! function_exists( 'nubu_change_stock_html' ) ) {
    function nubu_change_stock_html( $html, $product ) {
        
        $single_product_id = intval( $product->get_meta( '_single_product_id' ) );
    	if ( 0 === $single_product_id ) {
    		$product_id = $product->get_id();
    	} else {
    		$product_id = $single_product_id;
    	}
    	
    	//$available_quantity     = intval( $product->get_meta( '_unleashed_available_qty' ) );
    	$minimum_order_quantity = intval( $product->get_meta( 'unleashed_minimum_order_quantity' ) );
    	$lowest_threshold       = intval( $product->get_meta( 'lowest_threshold' ) );
    	$average_threshold      = intval( $product->get_meta( 'average_threshold' ) );
		
		$available_quantity = intval( $product->get_stock_quantity() );
    
    	if ( $available_quantity <= $lowest_threshold && $lowest_threshold > 0 ) {
    		$message = '<span class="nubu_low_stock">Very low</span>';
    	} else if ( $available_quantity > $lowest_threshold && $available_quantity <= $average_threshold ) {
    		$message = '<span class="nubu_medium_stock">Getting low</span>';
    	} else if ( $available_quantity > $average_threshold ) {
    		$message = '<span class="nubu_in_stock">In stock</span>';
    	}
    
    	if ( $available_quantity < $minimum_order_quantity || 0 === $available_quantity ) {
    		$message = '<span class="nubu_out_of_stock">Out of stock</span>';
    	}
    	
		if ( ! empty( $message ) ) {
			$html = sprintf(
				'<p class="nubu_product_stock_status">%1$s</p>',
				wp_kses_post( $message )
			);	
		}
    	
    	return $html;
    }
    add_filter( 'woocommerce_get_stock_html', 'nubu_change_stock_html', 10, 2 );
}

/* Quantity Based Price Table */
function nubu_quantity_based_text($atts) {
	$variable_prices = nubu_get_variable_prices();
	if ( ! empty( $variable_prices ) ) {
		$text = '<div class="nubu_custom_prices_wrapper">';
		foreach( $variable_prices as $index => $variable_price ) {
			$checked = 0 === $index ? ' checked="checked"' : '';
			$price = wc_price( $variable_price['Price'] );
			$text  .= '<label data-qty="'. intval( $variable_price['MinQty'] ) .'">';
			$text  .= '<div class="nubu_custom_prices_quantity"><input type="radio" name="nubu_min_quantity_radio" class="nubu_min_quantity_radio"'. $checked .'>'. sprintf( _nx( '%s+ unit(s)', '%s+ units', $variable_price['MinQty'], 'number of units', 'divi' ), intval( $variable_price['MinQty'] ) ) .'</div>';
			$text  .= '<div class="nubu_custom_prices_price">'. $price .'<span class="nubu_price_per_unit">per unit</span></div>';
			$text  .= '</label>';
		}
		$text .= '</div>';
		echo $text;
	}
	echo '';
}
add_action( 'woocommerce_after_quantity_input_field', 'nubu_quantity_based_text' );

/* Quantity Based Prices */
function nubu_get_variable_prices() {
	if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) || current_user_can( 'customer' ) ) ) {
		global $product;
		if ( ! $product ) {
			return array();
		}
		$current_user               = wp_get_current_user();
		$unleashed_customer_guid    = $current_user->_unleashed_customer_guid;
		$unleashed_customer_tier    = $current_user->_sell_price_tier_reference;
		$current_price 				= floatval( $product->get_price() );
		if ( ! empty( $product->get_meta('_customer_prices') ) ) {
			$applicable_prices 	= [];
            $customer_prices 	= json_decode( $product->get_meta('_customer_prices'), true);
			
            if ( ! empty( $unleashed_customer_guid ) && array_key_exists( $unleashed_customer_guid, $customer_prices ) ) {
                $applicable_prices = array_merge( $applicable_prices, $customer_prices[$unleashed_customer_guid] );
            }
			
			if ( array_key_exists( 'general', $customer_prices) ) {
                $applicable_prices = array_merge( $applicable_prices, $customer_prices['general'] );
            }
			
			if ( ! empty( $applicable_prices ) ) {
				// Step 1: Filter by price
				$filtered = array_filter($applicable_prices, function($item) use ($current_price) {
					return $item['Price'] < $current_price;
				});

				// Step 2: Find lowest price per MinQty
				$lowestPerMinQty = [];

				foreach ($filtered as $item) {
					$minQty = $item['MinQty'];
					if (!isset($lowestPerMinQty[$minQty]) || $item['Price'] < $lowestPerMinQty[$minQty]['Price']) {
						$lowestPerMinQty[$minQty] = $item;
					}
				}

				// Reset keys
				$variable_prices = array_values($lowestPerMinQty);
				$variable_prices[] = array(
					'MinQty' => 1,
					'Price'  => $current_price
				);
				usort($variable_prices, function($a, $b) {
					return $a['MinQty'] <=> $b['MinQty'];
				});
				
				$count = count( $variable_prices );
				for ($i = 0; $i < $count; $i++) {
					$currentMin = $variable_prices[$i]['MinQty'];

					if ($i === $count - 1) {
						// Last item — use "MinQty+"
						$range = $currentMin . '+';
					} else {
						$nextMin = $variable_prices[$i + 1]['MinQty'];
						$range = $currentMin . '-' . ($nextMin - 1);
					}

					$variable_prices[$i]['Range'] = $range;
				}
				
				return $variable_prices;
			}
		}
	}
	return '';
}

/* Hide add to cart button */
if ( ! function_exists( 'hide_add_to_cart_button' ) ) {
    function hide_add_to_cart_button() {
		if ( is_singular( 'product' ) ) {
			
			// Show button for public products too
			global $product;
			$flag = false;
			
			
			if ( is_user_logged_in() ) {
				//$current_user               = wp_get_current_user();
				//$unleashed_customer_guid    = $current_user->_unleashed_customer_guid;
				if ( ! current_user_can( 'trade_account' ) && ! current_user_can( 'administrator' ) && ! current_user_can( 'customer' ) ) {
					remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
					add_action( 'woocommerce_simple_add_to_cart', 'nubu_show_apply_for_trade_account', 30 );
				}
			} else {
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				add_action( 'woocommerce_simple_add_to_cart', 'nubu_show_apply_for_trade_account', 30 );
			}
		}		
		
		
    }
    add_action( 'wp_head', 'hide_add_to_cart_button' );
}

/* Show apply fo trade account button instead of add to cart button */
if ( ! function_exists( 'nubu_show_apply_for_trade_account' ) ) {
    function nubu_show_apply_for_trade_account() {
        global $product;
        $trade_url = carbon_get_theme_option('trade_account_form_url');
        $stock_status = wc_get_stock_html( $product );
        printf(
            '%2$s
            <form class="nubu_trade_account" action="" method="post">
                <a target="_blank" href="%1$s" class="button order">Apply For Trade Account</a>
            </form>',
            esc_url( $trade_url ),
            $stock_status
        );
    }
}

/* Show unleashed image when no featured image available */
if ( ! function_exists( 'nubu_custom_placeholder_image' ) ) {
    function nubu_custom_placeholder_image( $html, $post_thumbnail_id ) {
        if ( is_singular( 'product' ) ) {
            global $product;
        	if ( ! $post_thumbnail_id ) {
        	    $image_url = carbon_get_post_meta( $product->get_id(), 'unleashed_image_url' );
        	    if ( $image_url ) {
        	        $html = sprintf(
            	        '<img src="%s" alt="%s" class="wp-post-image" />',
            	        esc_url( $image_url ),
            	        esc_html( get_the_title( $product->get_id() ) )
            	    );
        	    }
        	}
        }
    	 
    	return $html;
    }
    add_filter( 'woocommerce_single_product_image_thumbnail_html', 'nubu_custom_placeholder_image', 10, 2 );
}

/* Matches user catalog with product catalog */
if ( ! function_exists( 'nubu_user_catalog_matches_product_catalog' ) ) {
	function nubu_user_catalog_matches_product_catalog( $product_catalogs, $user_catalogs ) {
		if ( empty( $product_catalogs ) || empty( $user_catalogs ) ) {
			return false;
		}

		foreach ( $product_catalogs as $product_catalog ) {
			foreach ( $user_catalogs as $user_catalog ) {
				if ( intval( $product_catalog->term_id ) == intval( $user_catalog['id'] ) ) {
					return true;
				}
			}
		}
		return false;
	}
}

/* Checks if product page is visible to user or not */
if ( ! function_exists( 'nubu_is_product_visible_for_user' ) ) {
	function nubu_is_product_visible_for_user() {
		//if ( is_singular( 'product' ) ) {
			//global $product;
			$product_id = get_queried_object_id();
		
			if ( ! is_user_logged_in() ) {
				return nubu_is_public_product( $product_id );
			} else {
				if ( nubu_is_public_product( $product_id ) ) {
					return true;	
				}
				
				$current_user = wp_get_current_user();
				$user_catalogs = carbon_get_user_meta( $current_user->ID, 'catalog' );
				if ( empty( $user_catalogs ) ) {
					return false;
				}
				/*
				if ( $product->_visible != 'yes' ) {
					return false;
				}
				*/

				$product_catalogs = get_the_terms( $product_id, 'catalog' );
				if ( is_wp_error( $product_catalogs ) || empty( $product_catalogs ) || ! nubu_user_catalog_matches_product_catalog( $product_catalogs, $user_catalogs ) ) {
					return false;
				}
			}
		//}
		return true;
	}
}