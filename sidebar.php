<?php
/**
 * The Template for the sidebar containing the main widget area
 *
 * @package  WordPress
 * @subpackage  Timber
 */

$templates = [
	'sidebar.twig',
];

$context = Timber::get_context();

Timber::render( $templates, $context );
