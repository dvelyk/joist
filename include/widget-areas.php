<?php

/**
 * Custom widget areas.
 *
 * Documentation: https://codex.wordpress.org/Function_Reference/register_sidebar
 */

register_sidebar( [
	'name' => 'Example',
	'id'   => 'joist_example',
] );

/*  Footer example:

	register_sidebar( [
		'name'          => 'Footer',
		'id'            => 'joist_footer',
		'description'   => 'Main footer area.',
		'before_widget' => '',
		'after_widget'  => '',
	] );
*/
