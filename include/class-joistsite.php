<?php

class JoistSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );

		/**
		 * TODO: This is kind of a hack. We can't render widgets that use
		 * context variables in add_to_context because the context hasn't been
		 * initialized when the widget is being rendered.
		 *
		 * As of Timber 1.7, two apply_filter calls are made, one with an
		 * underscore and one with a forward slash. So we can render the
		 * widgets using the slash filter after the variables are added to the
		 * context with the underscore filter.
		 */
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_widgets_to_context' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Configure Carbon Fields
		\Carbon_Fields\Carbon_Fields::boot();
		new Timber\Integrations\CarbonFields();
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );

		// Configure Timmy
		set_post_thumbnail_size( 0, 0 );
		new Timmy\Timmy();
		add_filter( 'timmy/sizes', array( $this, 'register_image_sizes' ) );

		parent::__construct();
	}

	function add_to_context( $context ) {
		// These values are available on every Timber::get_context() call

		return array_merge( $context, array(
			'menu'    => new Timber\Menu( 'main-menu' ),
			'options' => array(
				/*
				'company_name' => carbon_get_theme_option( 'crb_company_name' ),
				'email'        => carbon_get_theme_option( 'crb_email' ),
				'phone_number' => carbon_get_theme_option( 'crb_phone_number' ),
				'logo'         => carbon_get_theme_option( 'crb_logo' ),
				*/
			),
		) );
	}

	function add_to_twig( $twig ) {
		// Add custom functions to Twig

		$twig->addExtension( new WidontTwigExtension() );

		return $twig;
	}

	function add_widgets_to_context( $context ) {
		// Add widget areas to the default Twig context

		return array_merge( $context, array(
			'example' => Timber::get_widgets( 'joist_example' ),
			// 'footer'  => Timber::get_widgets( 'footer' ),
		) );
	}

	function enqueue_scripts() {
		wp_enqueue_script( 'joist', get_template_directory_uri() . '/static/js/index.js', array( 'jquery' ) );
	}

	function register_fields() {
		include_once( __DIR__ . '/post-meta.php' );
		include_once( __DIR__ . '/theme-options.php' );
	}

	function register_image_sizes( $sizes ) {
		// TODO: Customize for your site
		return array(
			'thumbnail' => array(
				'resize'     => array( 150, 150, 'center' ),
				'name'       => 'Thumbnail',
				'post_types' => array( 'all' ),
			),
			'featured'  => array(
				'resize' => array( 1600 ),
				'srcset' => array( 0.25, 0.5, 1, 1.25, 1.5, 2 ),
				'name'   => 'Featured (Hero)',
			),
		);
	}

	function register_post_types() {
		include_once( __DIR__ . '/post-types.php' );
	}

	function register_shortcodes() {
		include_once( __DIR__ . '/shortcodes.php' );
	}

	function register_taxonomies() {
		include_once( __DIR__ . '/taxonomies.php' );
	}

	function register_widgets() {
		include_once( __DIR__ . '/widget-areas.php' );

		include_once( __DIR__ . '/widgets/register.php' );
	}
}
