<?php

/**
 * Example custom post type.
 *
 */

register_post_type( 'joist_example', [
	'labels'          => [
		'name'               => __( 'Examples', 'joist' ),
		'singular_name'      => __( 'Example', 'joist' ),
		'add_new_item'       => __( 'Add example', 'joist' ),
		'view_item'          => __( 'View example', 'joist' ),
		'edit_item'          => __( 'Edit example', 'joist' ),
		'new_item'           => __( 'New example', 'joist' ),
		'search_items'       => __( 'Search examples', 'joist' ),
		'not_found'          => __( 'No examples found.', 'joist' ),
		'not_found_in_trash' => __( 'No examples found in trash.', 'joist' ),
	],
	'public'          => true,
	'show_ui'         => true,
	'capability_type' => 'post',
	'menu_icon'       => 'dashicons-media-text',
	'rewrite'         => [
		'slug' => 'examples',
	],
	'supports'        => [
		'title',
		'editor',
		'thumbnail',
	],
] );
