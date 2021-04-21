<?php
/**
 * Template Name: Front Page
 *
 * @package joist
 */

$joist_templates = [
	'index.twig',
];

$joist_context = Timber::get_context();

if ( is_front_page() ) {
	$joist_context['is_front_page'] = true;

	array_unshift( $joist_templates, 'pages/front.twig' );
}


$joist_context['post'] = new Timber\Post();

Timber::render( $joist_templates, $joist_context );
