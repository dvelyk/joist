<?php
/**
 * Utilities to help convert ACF postmeta to Carbon Fields.
 *
 * @package joist
 */

/**
 * Inserts terms into a given taxonomy if they don't already exist.
 *
 * @param string $taxonomy Name of the taxonomy.
 * @param array  $terms Associative array of unique term slugs and names.
 * @return void
 */
function joist_insert_unique_terms( $taxonomy, $terms ) {
	// Get a list of all existing terms by slug for this taxonomy.
	$query = new WP_Term_Query(
		[
			'taxonomy'   => $taxonomy,
			'fields'     => 'slugs',
			'hide_empty' => false,
		]
	);

	$existing_terms = $query->terms ? $query->terms : [];

	// Insert terms that do not yet exist.
	foreach ( $terms as $new_term_slug => $new_term_name ) {
		if ( ! in_array( $new_term_slug, $existing_terms, true ) ) {
			wp_insert_term(
				$new_term_name,
				$taxonomy,
				[ 'slug' => $new_term_slug ]
			);
		}
	}
}

/**
 * Converts one post_type to another by updating the database directly.
 *
 * @param string $old_post_type The old post type.
 * @param string $new_post_type The new post type.
 * @return int|false The number of rows updated, or false on error.
 */
function joist_convert_post_type( $old_post_type, $new_post_type ) {
	global $wpdb;

	return $wpdb->update(
		$wpdb->posts,
		[ 'post_type' => $new_post_type ],
		[ 'post_type' => $old_post_type ],
		[ '%s' ],
		[ '%s' ]
	);
}

/**
 * Moves all terms to a new taxonomy, which must be registered first.
 *
 * @param string $old_taxonomy The existing taxonomy name.
 * @param string $new_taxonomy The new taxonomy name.
 * @return int|false The number of rows updated, or false on error.
 */
function joist_move_taxonomy_terms( $old_taxonomy, $new_taxonomy ) {
	global $wpdb;

	return $wpdb->update(
		$wpdb->term_taxonomy,
		[ 'taxonomy' => $new_taxonomy ],
		[ 'taxonomy' => $old_taxonomy ],
		[ '%s' ],
		[ '%s' ]
	);
}

/**
 * Move content from one taxonomy term to another.
 *
 * @param string $post_type The type of content to move.
 * @param string $taxonomy The name of the taxonomy.
 * @param array  $term_map A map of old taxonomy terms and new (existing) ones.
 * @param string $field The type of field the map is using (e.g. id or slug).
 * @return void
 */
function joist_reorganize_content( $post_type, $taxonomy, $term_map,
	$field = 'slug' ) {

	foreach ( $term_map as $old_term => $new_term ) {
		$query = new WP_Query(
			[
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'tax_query'      =>
					[
						[
							'taxonomy' => $taxonomy,
							'field'    => $field,
							'terms'    => $old_term,
						],
					],
			]
		);

		foreach ( $query->posts as $id ) {
			wp_remove_object_terms( $id, $old_term, $taxonomy );
			wp_add_object_terms( $id, $new_term, $taxonomy );
		}
	}
}

/**
 * Deletes the specified terms for the given taxonomy. If no terms are
 * provided, *all* terms will be deleted!
 *
 * @param string     $taxonomy Name of the taxonomy.
 * @param array|null $terms An optional array of term ids or slugs.
 * @return void
 */
function joist_delete_terms( $taxonomy, $terms = null ) {
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			$id = term_exists( $term, $taxonomy );

			if ( is_array( $id ) ) {
				wp_delete_term( $id['term_id'], $taxonomy );
			}
		}
	} else {
		$query = new WP_Term_Query(
			[
				'taxonomy'   => $taxonomy,
				'fields'     => 'ids',
				'hide_empty' => false,
			]
		);

		if ( is_array( $query->terms ) ) {
			foreach ( $query->terms as $id ) {
				wp_delete_term( $id, $taxonomy );
			}
		}
	}
}

/**
 * Converts multiple meta_keys for a given post_type from one value to another.
 *
 * @param string $post_type The post type.
 * @param array  $meta_key_map An associative array of old => new meta keys.
 * @return array An array of query results for each updated key.
 */
function joist_convert_postmeta( $post_type, $meta_key_map ) {
	global $wpdb;

	$sql = <<<SQL
		UPDATE $wpdb->postmeta AS meta
		INNER JOIN $wpdb->posts AS posts ON posts.ID = meta.post_id
		SET meta.meta_key = %s
		WHERE posts.post_type = %s AND meta.meta_key = %s
SQL;

	$results = [];

	foreach ( $meta_key_map as $old_key => $new_key ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( $sql, $new_key, $post_type, $old_key );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results[] = $wpdb->query( $query );
	}

	return $results;
}

/**
 * Deletes postmeta matching the given meta_keys for a particular post_type.
 *
 * @param string $post_type The post type.
 * @param array  $meta_keys One or more meta_keys.
 * @return int|false The number of rows deleted, or false on error.
 */
function joist_delete_postmeta( $post_type, $meta_keys ) {
	global $wpdb;

	if ( '*' === $post_type ) {
		foreach ( $meta_keys as $key ) {
			delete_metadata( 'post', 0, $key, false, true );
		}
	} else {
		// Delete matching meta_keys for one post type only.

		// Create an array of string placeholders for each meta key.
		$placeholders = implode(
			', ',
			array_fill( 0, count( $meta_keys ), '%s' )
		);

		// Assemble the SQL query to prepare.
		$sql = <<<SQL
			DELETE meta FROM $wpdb->postmeta AS meta
			INNER JOIN $wpdb->posts AS posts ON posts.ID = meta.post_id
			WHERE posts.post_type = %s AND meta.meta_key IN ($placeholders)
SQL;

		// Prepare the query.
		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql,
			array_merge( [ $post_type ], $meta_keys )
		);

		// Execute.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $query );
	}
}

/**
 * Converts the format of datetime meta_values for the given meta_key and
 * post_type.
 *
 * @param string $post_type The name of the post type.
 * @param string $meta_key The name of the meta key.
 * @param string $old_format The old MySQL datetime format.
 * @param string $new_format The new MySQL datetime format.
 * @return int|false The number of rows updated, or false on error.
 */
function joist_update_datetime_format( $post_type, $meta_key, $old_format,
	$new_format ) {
	global $wpdb;

	$sql = <<<SQL
		UPDATE $wpdb->postmeta meta
		INNER JOIN $wpdb->posts posts ON posts.ID = meta.post_id
		SET
			meta_value = IF(
				meta_value,
				DATE_FORMAT(STR_TO_DATE(meta_value, %s), %s),
				''
			)
		WHERE posts.post_type = %s AND meta.meta_key = %s
SQL;

	$sql = $wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$sql,
		[ $old_format, $new_format, $post_type, $meta_key ]
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return $wpdb->query( $sql );
}
