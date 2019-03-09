<?php

use Carbon_Fields\Widget;
use Carbon_Fields\Field;

class JoistExampleWidget extends Widget {
	function __construct() {
		$this->setup(
			'joist_example_widget',
			'Example',
			'This is an example widget.',
			[
				Field::make( 'text', 'description', __( 'Description', 'joist' ) ),
			]
		);
	}

	function front_end( $args, $instance ) {
		$context = Timber::get_context();

		$context = array_merge( $context, $instance );

		Timber::render( 'views/widgets/example.twig', $context );
	}
}
