<?php
/**
 * Utilities to help convert ACF postmeta to Carbon Fields.
 *
 * @package joist
 */

/**
 * Inserts terms into a given taxonomy if they don't already exist.
 *
 * @param [string] $taxonomy Name of the taxonomy.
 * @param [array]  $terms Associative array of unique term slugs and names.
 * @return void
 */
function joist_insert_unique_terms( $taxonomy, $terms ) {
	// Get a list of all existing terms by slug for this taxonomy.
	$existing_terms = array_map(
		function( $term ) {
			return $term->slug;
		},
		get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		)
	);

	// Insert terms that do not yet exist.
	foreach ( $terms as $new_term_slug => $new_term_name ) {
		if ( ! in_array( $existing_terms, $new_term_slug, true ) ) {
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
 * @param [string] $old_post_type The old post type.
 * @param [string] $new_post_type The new post type.
 * @return [int|false] The number of rows updated, or false on error.
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
 * Converts multiple meta_keys for a given post_type from one value to another.
 *
 * @param [string] $post_type The post type.
 * @param [array]  $meta_key_map An associative array of old => new meta keys.
 * @return [array] An array of query results for each updated key.
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
 * @param [string] $post_type The post type.
 * @param [array]  $meta_keys One or more meta_keys.
 * @return [int|false] The number of rows deleted, or false on error.
 */
function joist_delete_postmeta( $post_type, $meta_keys ) {
	global $wpdb;

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
