<?php

/**
 * Additional functionality should be added to files in the include/* folder.
 *
 * See include/class-joistsite.php for more information.
 *
 */

if ( ! function_exists( 'is_login_page' ) ) {
	function is_login_page() {
		$abspath = str_replace(
			[ '\\', '/' ],
			DIRECTORY_SEPARATOR,
			ABSPATH
		);

		return (
			'wp-login.php' === $GLOBALS['pagenow'] ||
			'/wp-login.php' === $_SERVER['PHP_SELF'] ||  // phpcs:ignore
			in_array( $abspath . 'wp-login.php', get_included_files(), true )
		);
	}
}

add_action( 'after_setup_theme', 'joist_load' );
function joist_load() {
	// Ensure Composer installed dependencies
	$autoload_dir = __DIR__ . '/vendor/autoload.php';

	if ( ! is_readable( $autoload_dir ) ) {
		$error = __( '<div class="error">Please run <code>composer install</code> to download and install the theme dependencies.</div>', 'joist' );
		if ( is_admin() || is_login_page() ) {
			add_action( 'admin_notices', function() use ( $error ) {
				echo $error;  // phpcs:ignore
			});

			return;
		}

		wp_die( $error );  // phpcs:ignore
	}
	require_once( $autoload_dir );

	// Setup class autoloader for this theme
	require_once(
		__DIR__ . DIRECTORY_SEPARATOR
		. implode( DIRECTORY_SEPARATOR, [ 'lib', 'class-joistautoload.php' ] )
	);

	// Configure Timber
	$timber = new \Timber\Timber();

	if ( ! class_exists( 'Timber' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		});

		add_filter('template_include', function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		});

		return;
	}

	Timber::$dirname = [ 'templates', 'views' ];

	// Continue configuring the theme
	new JoistSite();
}
