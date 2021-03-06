<?php

/**
 * Integration with Carbon Fields
 *
 * @package Timber
 */

namespace Timber\Integrations;

class CarbonFields {

	public function __construct() {
		add_filter( 'timber_post_get_meta_field', [ $this, 'post_get_meta_field' ], 10, 3 );
		add_filter( 'timber/term/meta/field', [ $this, 'term_get_meta_field' ], 10, 3 );
		add_filter( 'timber_user_get_meta_field', [ $this, 'user_get_meta_field' ], 10, 3 );
		add_filter( 'timber_comment_get_meta_field', [ $this, 'comment_get_meta_field' ], 10, 3 );
	}

	public function comment_get_meta_field( $value, $id, $field_name ) {
		return carbon_get_comment_meta( $id, $field_name );
	}

	public function post_get_meta_field( $value, $id, $field_name ) {
		return carbon_get_post_meta( $id, $field_name );
	}

	public function term_get_meta_field( $value, $id, $field_name ) {
		return carbon_get_term_meta( $id, $field_name );
	}

	public function user_get_meta_field( $value, $id, $field_name ) {
		return carbon_get_user_meta( $id, $field_name );
	}
}
