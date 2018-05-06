<?php

/**
 * Custom post fields.
 *
 * Documentation: https://carbonfields.net/docs/containers-post-meta/
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make( 'post_meta', __( 'Example area', 'joist' ) )
	->add_fields( array(
		Field::make( 'text', 'crb_example', 'Example text' ),
	) );
