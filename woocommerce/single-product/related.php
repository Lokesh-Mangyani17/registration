<?php
global $product;
$product_id = $product->get_id();

$cats_array = apply_filters( 'woocommerce_product_related_posts_relate_by_category', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_cat_terms', wc_get_product_term_ids( $product_id, 'product_cat' ), $product_id ) : array();
$tags_array = apply_filters( 'woocommerce_product_related_posts_relate_by_tag', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_tag_terms', wc_get_product_term_ids( $product_id, 'product_tag' ), $product_id ) : array();

// Custom related products query
$args = array(
    'posts_per_page' => 4, // Number of related products
    'orderby' => 'rand', // Order by random
    'post__not_in' => array( $product_id ),
);

if ( ! empty( $cats_array ) ) {
	if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field' => 'term_id',
			'terms' => $cats_array,
			'operator' => 'IN'
		);
	} else {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $cats_array,
				'operator' => 'IN'
			)
		);
	}
}

if ( ! empty( $tags_array ) ) {
	if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_tag',
			'field' => 'term_id',
			'terms' => $tags_array,
			'operator' => 'IN'
		);
	} else {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_tag',
				'field' => 'term_id',
				'terms' => $tags_array,
				'operator' => 'IN'
			)
		);
	}
}

$catalog_query = nubu_get_catalog_tax_query();
if ( empty( $catalog_query ) ) {
	$args['posts_per_page'] = 0;
} else {
	if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
		$args['tax_query'][] = $catalog_query;
	} else {
		$args['tax_query'] = array( $catalog_query );
	}
}

if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
	$args['tax_query']['relation'] = 'AND';
}


// Query the related products
$related_products = new WP_Query( $args );

if ( $related_products->have_posts() ) : ?>
    <section class="related products">
        <?php
		$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

		if ( $heading ) :
			?>
			<h2><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		
        <ul class="products">
            <?php while ( $related_products->have_posts() ) : $related_products->the_post(); ?>
                
                    <?php
                        // Display the product thumbnail, title, etc.
                        wc_get_template_part( 'content', 'product' );
                    ?>
                
            <?php endwhile; ?>
        </ul>
    </section>
<?php endif;

wp_reset_postdata(); // Reset the query
?>
