<?php

class JoistSite extends TimberSite {
	private $front_page_id;

	function __construct() {
		$this->front_page_id = (int) get_option( 'page_on_front' );

		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ] );

		add_filter( 'get_twig', [ $this, 'add_to_twig' ] );

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
		add_filter( 'timber_context', [ $this, 'add_to_context' ] );
		add_filter( 'timber/context', [ $this, 'add_widgets_to_context' ] );

		add_action( 'carbon_fields_register_fields', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_shortcodes' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'body_class', [ $this, 'add_slug_to_body_class' ] );

		// Configure Carbon Fields
		\Carbon_Fields\Carbon_Fields::boot();
		new Timber\Integrations\CarbonFields();
		add_action( 'carbon_fields_register_fields', [ $this, 'register_fields' ] );

		// Configure Timmy
		set_post_thumbnail_size( 0, 0 );
		new Timmy\Timmy();
		add_filter( 'timmy/sizes', [ $this, 'register_image_sizes' ] );
		require_once __DIR__ . '/../lib/admin-thumbnail-crop-settings.php';


		// Configure WooCommerce
		/*
		add_theme_support( 'woocommerce' );
		Timber\Integrations\WooCommerce\WooCommerce::init();

		add_filter( 'wp_setup_nav_menu_item', [ $this, 'add_cart_contents_to_menu' ] );
		*/

		// Hide Jetpack upsells
		add_filter( 'jetpack_just_in_time_msgs', '__return_false' );

		add_filter( 'tiny_mce_before_init', [ $this, 'config_tiny_mce' ] );

		// Add custom roles and capabilities
		$this->register_user_roles();

		parent::__construct();
	}

	/*
	function add_cart_contents_to_menu( $item ) {
		$cart = WC()->cart;

		if ( 'Cart' === $item->title && $cart ) {
			$item->title = 'Cart (' . $cart->get_cart_contents_count() . ')';
		}

		return $item;
	}
	*/

	function add_slug_to_body_class( $classes ) {
		global $post;

		if ( isset( $post ) ) {
			$classes[] = $post->post_type . '-' . $post->post_name;
		}

		return $classes;
	}

	function add_to_context( $context ) {
		// These values are available on every Timber::get_context() call

		return array_merge( $context, [
			'menu'    => new Timber\Menu( 'main-menu' ),
			'options' => [
				/*
				'company_name' => carbon_get_theme_option( 'crb_company_name' ),
				'email'        => carbon_get_theme_option( 'crb_email' ),
				'phone_number' => carbon_get_theme_option( 'crb_phone_number' ),
				'logo'         => carbon_get_theme_option( 'crb_logo' ),
				'social_media' => [
					'facebook'  => carbon_get_theme_option( 'crb_facebook' ),
					'instagram' => carbon_get_theme_option( 'crb_instagram' ),
					'twitter'   => carbon_get_theme_option( 'crb_twitter' ),
				],
				*/
			],
		] );
	}

	function add_to_twig( $twig ) {
		// Add custom functions to Twig

		$twig->addExtension( new WidontTwigExtension() );

		return $twig;
	}

	function add_widgets_to_context( $context ) {
		// Add widget areas to the default Twig context

		return array_merge( $context, [
			'example' => Timber::get_widgets( 'joist_example' ),
			// 'footer'  => Timber::get_widgets( 'footer' ),
		] );
	}

	/**
	 * Remove the h1 tag from the WordPress editor.
	 * From https://gist.github.com/kjbrum/da4eb508be09b9c336a9
	 *
	 * @param  array $settings   The array of editor settings.
	 * @return array             The modified edit settings
	 */
	private function config_tiny_mce( $settings ) {
		$settings['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre;';

		return $settings;
	}

	function enqueue_scripts() {
		wp_enqueue_script( 'joist', get_template_directory_uri() . '/static/js/index.js', [ 'jquery' ], 'version', true );
	}

	function register_fields() {
		include_once( __DIR__ . '/post-meta.php' );
		include_once( __DIR__ . '/theme-options.php' );
	}

	function register_image_sizes( $sizes ) {
		// TODO: Customize for your site
		return [
			'thumbnail' => [
				'resize'     => [ 150, 150, 'center' ],
				'name'       => 'Thumbnail',
				'post_types' => [ 'all' ],
			],
		];
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

	function register_user_roles() {
	}

	function register_widgets() {
		include_once( __DIR__ . '/widget-areas.php' );

		include_once( __DIR__ . '/widgets/register.php' );
	}
}
