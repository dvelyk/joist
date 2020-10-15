<?php
/**
 * Utilities to process and augment text content.
 *
 * @package joist
 */

function joist_make_crumb( $text, $href = null ) {
	return [
		'href' => $href,
		'text' => $text,
	];
}

function joist_get_custom_archive_crumb( $post ) {
	// If post is a custom post type.
	$post_type = get_post_type( $post );

	// If it is a custom post type display name and link.
	if ( 'post' !== $post_type ) {

		$post_type_object  = get_post_type_object( $post_type );
		$post_type_archive_link = get_post_type_archive_link( $post_type );

		return joist_make_crumb(
			$post_type_object->labels->name,
			$post_type_archive_link
		);
	}

	return null;
}

/**
 * Returns an array of ancestor pages for the current post.
 *
 * @param WP_Post $post The current page.
 * @param bool    $link_current Whether to link the last breadcrumb.
 * @return [WP_Post] An array of ancestor pages.
 */
function joist_get_breadcrumbs( $post, $link_current = false, $paged = true ) {
	$paged = $paged ? get_query_var( 'paged' ) : false;

	$link_current = $link_current || ( $paged && 1 !== $paged );

	$crumbs = [];

	if ( empty( $post ) ) {
		if ( is_archive() && ! is_tax() && ! is_category() && ! is_tag() ) {

			$crumbs[] = joist_make_crumb( post_type_archive_title( '', false ) );

		} elseif ( is_archive() && is_tax() && ! is_category() && ! is_tag() ) {

			$archive_crumb = joist_get_custom_archive_crumb( $post );

			if ( is_array( $archive_crumb ) ) {
				$crumbs[] = $archive_crumb;
			}

			$custom_tax_name = get_queried_object()->name;

			$crumbs[] = joist_make_crumb( $custom_tax_name );

		} elseif ( is_category() ) {

			$crumbs = joist_get_breadcrumbs(
				get_post( get_option( 'page_for_posts' ) ),
				true,
				false
			);

			$crumbs[] = joist_make_crumb(
				sprintf(
					// translators: name of category.
					__( 'Category: %s', 'joist' ),
					single_cat_title( '', false )
				),
				$paged ? get_category_link( get_queried_object() ) : null
			);

		} elseif ( is_tag() ) {

			$terms = get_terms(
				'post_tag',
				'include=' . get_query_var( 'tag_id' )
			);

			$crumbs[] = joist_make_crumb( $terms[0]->name );

		} elseif ( is_day() ) {

			$year  = get_the_time( 'Y', $post );
			$month = get_the_time( 'm', $post );

			$crumbs[] = joist_make_crumb( $year, get_year_link( $year ) );

			$crumbs[] = joist_make_crumb(
				get_the_time( 'M', $post ),
				get_month_link( $year, $month )
			);

			$crumbs[] = joist_make_crumb( get_the_time( 'jS', $post ) );

		} elseif ( is_month() ) {

			$year  = get_the_time( 'Y', $post );
			$month = get_the_time( 'm', $post );

			$crumbs[] = joist_make_crumb( $year, get_year_link( $year ) );

			$crumbs[] = joist_make_crumb( get_the_time( 'M', $post ) );

		} elseif ( is_year() ) {

			$crumbs[] = joist_make_crumb( get_the_time( 'Y', $post ) );

		} elseif ( is_author() ) {

			global $author;
			$userdata = get_userdata( $author );

			$crumbs[] = joist_make_crumb( 'Author: ' . $userdata->display_name );

		} elseif ( is_search() ) {

			$crumbs[] = joist_make_crumb(
				'Search results for: ' . get_search_query()
			);

		} elseif ( is_404() ) {

			$crumbs[] = joist_make_crumb( '404: Not Found' );

		}

	} elseif ( 'page' === $post->post_type ) {

		$ancestor_ids = array_reverse( get_post_ancestors( $post->ID ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$crumbs[] = joist_make_crumb(
				get_the_title( $ancestor_id ),
				get_permalink( $ancestor_id )
			);
		}

		$crumbs[] = joist_make_crumb(
			$post->post_title,
			$link_current ? get_permalink( $post->ID ) : null
		);

	} else {
		switch ( $post->post_type ) {
			default:
				$archive_crumb = joist_get_custom_archive_crumb( $post );

				if ( is_array( $archive_crumb ) ) {
					$crumbs[] = $archive_crumb;
				}
		}

		$crumbs[] = joist_make_crumb(
			$post->post_title,
			$link_current ? get_the_permalink( $post->ID ) : null
		);

		// TODO: Add category info?
	}

	if ( $paged && '1' !== $paged ) {

		$crumbs[] = joist_make_crumb( 'Page ' . $paged );

	}

	return $crumbs;
}
