<?php

if ( ! class_exists( 'JoistAutoload' ) ) {
	class JoistAutoload {
		private static $dirs = [
			[ __DIR__, '..', 'include' ],
			[ __DIR__, '..', 'include', 'widgets' ],
			[ __DIR__ ],
		];

		public static function init() {
			foreach ( self::$dirs as $key => $array ) {
				self::$dirs[ $key ] = implode( DIRECTORY_SEPARATOR, $array );
			}
		}

		public static function autoload( $class_name ) {
			// Prepend "class-" to class name and convert to directory structure
			$class_name = explode( '\\', $class_name );

			$class_name[ count( $class_name ) - 1 ] = 'class-' .
				$class_name[ count( $class_name ) - 1 ];

			$class_name = strtolower( implode( DIRECTORY_SEPARATOR, $class_name ) );

			foreach ( self::$dirs as $dir_path ) {
				$file = $dir_path . DIRECTORY_SEPARATOR . $class_name . '.php';

				if ( file_exists( $file ) ) {
					require_once( $file );
					return;
				}
			}
		}
	}

	JoistAutoload::init();

	spl_autoload_register( 'JoistAutoload::autoload' );
}
