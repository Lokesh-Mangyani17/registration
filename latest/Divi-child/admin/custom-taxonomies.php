<?php
if ( ! function_exists( 'nubu_custom_taxonomies' ) ) {

    // Register Custom Taxonomy
    function nubu_custom_taxonomies() {
    
    	$labels = array(
    		'name'                       => _x( 'Brands', 'Taxonomy General Name', 'divi' ),
    		'singular_name'              => _x( 'Brand', 'Taxonomy Singular Name', 'divi' ),
    		'menu_name'                  => __( 'Brands', 'divi' ),
    		'all_items'                  => __( 'All Brands', 'divi' ),
    		'parent_item'                => __( 'Parent Brand', 'divi' ),
    		'parent_item_colon'          => __( 'Parent Brand:', 'divi' ),
    		'new_item_name'              => __( 'New Brand', 'divi' ),
    		'add_new_item'               => __( 'Add New Brand', 'divi' ),
    		'edit_item'                  => __( 'Edit Brand', 'divi' ),
    		'update_item'                => __( 'Update Brand', 'divi' ),
    		'view_item'                  => __( 'View Brand', 'divi' ),
    		'separate_items_with_commas' => __( 'Separate brands with commas', 'divi' ),
    		'add_or_remove_items'        => __( 'Add or remove brands', 'divi' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'divi' ),
    		'popular_items'              => __( 'Popular Brands', 'divi' ),
    		'search_items'               => __( 'Search Brands', 'divi' ),
    		'not_found'                  => __( 'Not Found', 'divi' ),
    		'no_terms'                   => __( 'No brands', 'divi' ),
    		'items_list'                 => __( 'Brands list', 'divi' ),
    		'items_list_navigation'      => __( 'Brands list navigation', 'divi' ),
    	);
    	$args = array(
    		'labels'                     => $labels,
    		'hierarchical'               => true,
    		'public'                     => true,
    		'show_ui'                    => true,
    		'show_admin_column'          => true,
    		'show_in_nav_menus'          => true,
    		'show_tagcloud'              => true,
    	);
    	register_taxonomy( 'brand', array( 'product' ), $args );
    	
    	$labels = array(
    		'name'                       => _x( 'Catalogs', 'Taxonomy General Name', 'divi' ),
    		'singular_name'              => _x( 'Catalog', 'Taxonomy Singular Name', 'divi' ),
    		'menu_name'                  => __( 'Catalogs', 'divi' ),
    		'all_items'                  => __( 'All Catalogs', 'divi' ),
    		'parent_item'                => __( 'Parent Catalog', 'divi' ),
    		'parent_item_colon'          => __( 'Parent Catalog:', 'divi' ),
    		'new_item_name'              => __( 'New Catalog', 'divi' ),
    		'add_new_item'               => __( 'Add New Catalog', 'divi' ),
    		'edit_item'                  => __( 'Edit Catalog', 'divi' ),
    		'update_item'                => __( 'Update Catalog', 'divi' ),
    		'view_item'                  => __( 'View Catalog', 'divi' ),
    		'separate_items_with_commas' => __( 'Separate catalogs with commas', 'divi' ),
    		'add_or_remove_items'        => __( 'Add or remove catalogs', 'divi' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'divi' ),
    		'popular_items'              => __( 'Popular Catalogs', 'divi' ),
    		'search_items'               => __( 'Search Catalogs', 'divi' ),
    		'not_found'                  => __( 'Not Found', 'divi' ),
    		'no_terms'                   => __( 'No catalogs', 'divi' ),
    		'items_list'                 => __( 'Catalogs list', 'divi' ),
    		'items_list_navigation'      => __( 'Catalogs list navigation', 'divi' ),
    	);
    	$args = array(
    		'labels'                     => $labels,
    		'hierarchical'               => true,
    		'public'                     => true,
    		'show_ui'                    => true,
    		'show_admin_column'          => true,
    		'show_in_nav_menus'          => true,
    		'show_tagcloud'              => true,
    	);
    	register_taxonomy( 'catalog', array( 'product' ), $args );
    	
    	$labels = array(
    		'name'                       => _x( 'Strengths', 'Taxonomy General Name', 'divi' ),
    		'singular_name'              => _x( 'Strength', 'Taxonomy Singular Name', 'divi' ),
    		'menu_name'                  => __( 'Strengths', 'divi' ),
    		'all_items'                  => __( 'All Strengths', 'divi' ),
    		'parent_item'                => __( 'Parent Strength', 'divi' ),
    		'parent_item_colon'          => __( 'Parent Strength:', 'divi' ),
    		'new_item_name'              => __( 'New Strength', 'divi' ),
    		'add_new_item'               => __( 'Add New Strength', 'divi' ),
    		'edit_item'                  => __( 'Edit Strength', 'divi' ),
    		'update_item'                => __( 'Update Strength', 'divi' ),
    		'view_item'                  => __( 'View Strength', 'divi' ),
    		'separate_items_with_commas' => __( 'Separate strengths with commas', 'divi' ),
    		'add_or_remove_items'        => __( 'Add or remove strengths', 'divi' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'divi' ),
    		'popular_items'              => __( 'Popular Strengths', 'divi' ),
    		'search_items'               => __( 'Search Strengths', 'divi' ),
    		'not_found'                  => __( 'Not Found', 'divi' ),
    		'no_terms'                   => __( 'No strengths', 'divi' ),
    		'items_list'                 => __( 'Strengths list', 'divi' ),
    		'items_list_navigation'      => __( 'Strengths list navigation', 'divi' ),
    	);
    	$args = array(
    		'labels'                     => $labels,
    		'hierarchical'               => true,
    		'public'                     => true,
    		'show_ui'                    => true,
    		'show_admin_column'          => true,
    		'show_in_nav_menus'          => true,
    		'show_tagcloud'              => true,
    	);
    	register_taxonomy( 'strength', array( 'product' ), $args );
    	
    	$labels = array(
    		'name'                       => _x( 'Education Categories', 'Taxonomy General Name', 'divi' ),
    		'singular_name'              => _x( 'Education Category', 'Taxonomy Singular Name', 'divi' ),
    		'menu_name'                  => __( 'Education Categories', 'divi' ),
    		'all_items'                  => __( 'All Education Categories', 'divi' ),
    		'parent_item'                => __( 'Parent Education Category', 'divi' ),
    		'parent_item_colon'          => __( 'Parent Education Category:', 'divi' ),
    		'new_item_name'              => __( 'New Education Category', 'divi' ),
    		'add_new_item'               => __( 'Add New Education Category', 'divi' ),
    		'edit_item'                  => __( 'Edit Education Category', 'divi' ),
    		'update_item'                => __( 'Update Education Category', 'divi' ),
    		'view_item'                  => __( 'View Education Category', 'divi' ),
    		'separate_items_with_commas' => __( 'Separate education Categories with commas', 'divi' ),
    		'add_or_remove_items'        => __( 'Add or remove education categories', 'divi' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'divi' ),
    		'popular_items'              => __( 'Popular Education Categories', 'divi' ),
    		'search_items'               => __( 'Search Education Categories', 'divi' ),
    		'not_found'                  => __( 'Not Found', 'divi' ),
    		'no_terms'                   => __( 'No education categories', 'divi' ),
    		'items_list'                 => __( 'Education Categories list', 'divi' ),
    		'items_list_navigation'      => __( 'Education Categories list navigation', 'divi' ),
    	);
    	$args = array(
    		'labels'                     => $labels,
    		'hierarchical'               => true,
    		'public'                     => true,
    		'show_ui'                    => true,
    		'show_admin_column'          => true,
    		'show_in_nav_menus'          => true,
    		'show_tagcloud'              => true,
    	);
    	register_taxonomy( 'education-category', array( 'education-resource' ), $args );
		
		$labels = array(
			'name'                       => _x( 'Categories', 'Taxonomy General Name', 'divi' ),
			'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'divi' ),
			'menu_name'                  => __( 'Category', 'divi' ),
			'all_items'                  => __( 'All Categories', 'divi' ),
			'parent_item'                => __( 'Parent Category', 'divi' ),
			'parent_item_colon'          => __( 'Parent Category:', 'divi' ),
			'new_item_name'              => __( 'New Category Name', 'divi' ),
			'add_new_item'               => __( 'Add New Category', 'divi' ),
			'edit_item'                  => __( 'Edit Category', 'divi' ),
			'update_item'                => __( 'Update Category', 'divi' ),
			'view_item'                  => __( 'View Category', 'divi' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'divi' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'divi' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'divi' ),
			'popular_items'              => __( 'Popular Categories', 'divi' ),
			'search_items'               => __( 'Search Categories', 'divi' ),
			'not_found'                  => __( 'Not Found', 'divi' ),
			'no_terms'                   => __( 'No categories', 'divi' ),
			'items_list'                 => __( 'Categories list', 'divi' ),
			'items_list_navigation'      => __( 'Categories list navigation', 'divi' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'announcement-category', array( 'announcement' ), $args );
    
    }
    add_action( 'init', 'nubu_custom_taxonomies', 0 );

}