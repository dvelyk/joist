<?php
/**
 * Utilities to process and augment text content.
 *
 * @package pvn
 */

/**
 * Returns an array of ancestor pages for the current post.
 *
 * @param WP_Post $post The current page.
 * @return [WP_Post] An array of ancestor pages.
 */
function joist_get_breadcrumbs( $post ) {
	$crumbs = [];

	function make_crumb( $text, $href = null ) {
		return [
			'href'  => $href,
			'text' => $text,
		];
	}

	function get_custom_archive_crumb( $post ) {
		// If post is a custom post type.
		$post_type = get_post_type( $post );

		// If it is a custom post type display name and link.
		if ( 'post' !== $post_type ) {

			$post_type_object  = get_post_type_object( $post_type );
			$post_type_archive_link = get_post_type_archive_link( $post_type );

			return make_crumb(
				$post_type_object->labels->name,
				$post_type_archive_link
			);
		}

		return null;
	}

	if ( is_archive() && ! is_tax() && ! is_category() && ! is_tag() ) {

		$crumbs[] = make_crumb( post_type_archive_title( '', false ) );

	} elseif ( is_archive() && is_tax() && ! is_category() && ! is_tag() ) {

		$archive_crumb = get_custom_archive_crumb( $post );

		if ( is_array( $archive_crumb ) ) {
			$crumbs[] = $archive_crumb;
		}

		$custom_tax_name = get_queried_object()->name;

		$crumbs[] = make_crumb( $custom_tax_name );

	} elseif ( is_single( $post ) ) {

		$archive_crumb = get_custom_archive_crumb( $post );

		if ( is_array( $archive_crumb ) ) {
			$crumbs[] = $archive_crumb;
		}

		$crumbs[] = make_crumb( $post->post_title );

		// TODO: Add category info?

	} elseif ( is_category() ) {

		$crumbs[] = make_crumb( single_cat_title( '', false ) );

	} elseif ( is_home() || is_page( $post ) ) {

		$ancestor_ids = array_reverse( get_post_ancestors( $post->ID ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$crumbs[] = make_crumb(
				get_the_title( $ancestor_id ),
				get_permalink( $ancestor_id )
			);
		}

		$crumbs[] = make_crumb( $post->post_title );

	} elseif ( is_tag() ) {

		$terms = get_terms(
			'post_tag',
			'include=' . get_query_var( 'tag_id' )
		);

		$crumbs[] = make_crumb( $terms[0]->name );

	} elseif ( is_day() ) {

		$year  = get_the_time( 'Y', $post );
		$month = get_the_time( 'm', $post );

		$crumbs[] = make_crumb( $year, get_year_link( $year ) );

		$crumbs[] = make_crumb(
			get_the_time( 'M', $post ),
			get_month_link( $year, $month )
		);

		$crumbs[] = make_crumb( get_the_time( 'jS', $post ) );

	} elseif ( is_month() ) {

		$year  = get_the_time( 'Y', $post );
		$month = get_the_time( 'm', $post );

		$crumbs[] = make_crumb( $year, get_year_link( $year ) );

		$crumbs[] = make_crumb( get_the_time( 'M', $post ) );

	} elseif ( is_year() ) {

		$crumbs[] = make_crumb( get_the_time( 'Y', $post ) );

	} elseif ( is_author() ) {

		global $author;
		$userdata = get_userdata( $author );

		$crumbs[] = make_crumb( 'Author: ' . $userdata->display_name );

	} elseif ( get_query_var( 'paged' ) ) {

		$crumbs[] = make_crumb( 'Page ' . get_query_var( 'paged' ) );

	} elseif ( is_search() ) {

		$crumbs[] = make_crumb( 'Search results for: ' . get_search_query() );

	} elseif ( is_404() ) {

		$crumbs[] = make_crumb( '404: Not Found' );
	}

	return $crumbs;
}
