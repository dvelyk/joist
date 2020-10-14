<?php
/**
 * Helper utilities for Carbon Fields.
 *
 * @package pvn
 */

use Carbon_Fields\Helper\Helper;

/**
 * Returns the key and label for the selected field name, or just labels if
 * labels_only is true.
 *
 * @param int    $post_id The id of the post.
 * @param string $field_name The name of the multiselect field.
 * @param array  $options The map of option key => label.
 * @param bool   $labels_only If true, will return only labels.
 * @return mixed
 */
function joist_get_field_options( $post_id, $field_name, $options,
	$labels_only = false ) {
	$values = carbon_get_post_meta( $post_id, $field_name );

	if ( is_array( $values ) ) {
		return array_map(
			function ( $key ) use ( $options, $labels_only ) {
				if ( $labels_only ) {
					return $options[ $key ];
				}

				return [
					$key => $options[ $key ],
				];
			},
			$values
		);
	}

	return $values;
}

function joist_filter_carbon_fields_options_by_title( $query_args ) {
	$query_args['search_terms'] = $query_args['s'];

	return $query_args;
}
