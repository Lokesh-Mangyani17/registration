<?php
if ( ! function_exists('nubu_create_post_type') ) {
    // Register Custom Post Type
    function nubu_create_post_type() {
    
    	$labels = array(
    		'name'                  => _x( 'Education Resources', 'Post Type General Name', 'divi' ),
    		'singular_name'         => _x( 'Education Resource', 'Post Type Singular Name', 'divi' ),
    		'menu_name'             => __( 'Education Resources', 'divi' ),
    		'name_admin_bar'        => __( 'Education Resource', 'divi' ),
    		'archives'              => __( 'Education Resources Archives', 'divi' ),
    		'attributes'            => __( 'Education Resources Attributes', 'divi' ),
    		'parent_item_colon'     => __( 'Parent Education Resource:', 'divi' ),
    		'all_items'             => __( 'All Education Resources', 'divi' ),
    		'add_new_item'          => __( 'Add New Education Resource', 'divi' ),
    		'add_new'               => __( 'Add New', 'divi' ),
    		'new_item'              => __( 'New Education Resource', 'divi' ),
    		'edit_item'             => __( 'Edit Education Resource', 'divi' ),
    		'update_item'           => __( 'Update Education Resource', 'divi' ),
    		'view_item'             => __( 'View Education Resource', 'divi' ),
    		'view_items'            => __( 'View Education Resources', 'divi' ),
    		'search_items'          => __( 'Search Education Resource', 'divi' ),
    		'not_found'             => __( 'Not found', 'divi' ),
    		'not_found_in_trash'    => __( 'Not found in Trash', 'divi' ),
    		'featured_image'        => __( 'Featured Image', 'divi' ),
    		'set_featured_image'    => __( 'Set featured image', 'divi' ),
    		'remove_featured_image' => __( 'Remove featured image', 'divi' ),
    		'use_featured_image'    => __( 'Use as featured image', 'divi' ),
    		'insert_into_item'      => __( 'Insert into education resources', 'divi' ),
    		'uploaded_to_this_item' => __( 'Uploaded to this education resources', 'divi' ),
    		'items_list'            => __( 'Education Resources list', 'divi' ),
    		'items_list_navigation' => __( 'Education Resources list navigation', 'divi' ),
    		'filter_items_list'     => __( 'Filter education resources list', 'divi' ),
    	);
    	$rewrite = array(
    		'slug'                  => 'cannabis-education',
    		'with_front'            => true,
    		'pages'                 => true,
    		'feeds'                 => true,
    	);
    	$args = array(
    		'label'                 => __( 'Education Resource', 'divi' ),
    		'labels'                => $labels,
    		'supports'              => array( 'title', 'editor', 'revisions', 'excerpt' ),
    		'taxonomies'            => array( 'education-category' ),
    		'hierarchical'          => false,
    		'public'                => true,
    		'show_ui'               => true,
    		'show_in_menu'          => true,
    		'menu_position'         => 5,
    		'menu_icon'             => 'dashicons-welcome-learn-more',
    		'show_in_admin_bar'     => true,
    		'show_in_nav_menus'     => true,
    		'can_export'            => true,
    		'has_archive'           => true,
    		'exclude_from_search'   => false,
    		'publicly_queryable'    => true,
    		'rewrite'               => $rewrite,
    		'capability_type'       => 'page',
    	);
    	register_post_type( 'education-resource', $args );
		
		$labels = array(
			'name'                  => _x( 'Announcements', 'Post Type General Name', 'divi' ),
			'singular_name'         => _x( 'Announcement', 'Post Type Singular Name', 'divi' ),
			'menu_name'             => __( 'Announcements', 'divi' ),
			'name_admin_bar'        => __( 'Announcement', 'divi' ),
			'archives'              => __( 'Announcement Archives', 'divi' ),
			'attributes'            => __( 'Announcement Attributes', 'divi' ),
			'parent_item_colon'     => __( 'Parent Announcement:', 'divi' ),
			'all_items'             => __( 'All Announcements', 'divi' ),
			'add_new_item'          => __( 'Add New Announcement', 'divi' ),
			'add_new'               => __( 'Add New', 'divi' ),
			'new_item'              => __( 'New Announcement', 'divi' ),
			'edit_item'             => __( 'Edit Announcement', 'divi' ),
			'update_item'           => __( 'Update Announcement', 'divi' ),
			'view_item'             => __( 'View Announcement', 'divi' ),
			'view_items'            => __( 'View Announcements', 'divi' ),
			'search_items'          => __( 'Search Announcement', 'divi' ),
			'not_found'             => __( 'Not found', 'divi' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'divi' ),
			'featured_image'        => __( 'Featured Image', 'divi' ),
			'set_featured_image'    => __( 'Set featured image', 'divi' ),
			'remove_featured_image' => __( 'Remove featured image', 'divi' ),
			'use_featured_image'    => __( 'Use as featured image', 'divi' ),
			'insert_into_item'      => __( 'Insert into announcement', 'divi' ),
			'uploaded_to_this_item' => __( 'Uploaded to this announcement', 'divi' ),
			'items_list'            => __( 'Announcements list', 'divi' ),
			'items_list_navigation' => __( 'Announcements list navigation', 'divi' ),
			'filter_items_list'     => __( 'Filter announcements list', 'divi' ),
		);
		$args = array(
			'label'                 => __( 'Announcement', 'divi' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor' ),
			'taxonomies'            => array( 'announcement-category' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-megaphone',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => [
				'create_posts' => 'manage_options', // only admins
			],
		);
		register_post_type( 'announcement', $args );
    
    }
    add_action( 'init', 'nubu_create_post_type', 0 );
}