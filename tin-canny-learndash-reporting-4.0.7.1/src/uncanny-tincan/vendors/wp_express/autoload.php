<?php
/**
 *
 * WP Express Autoloader & Redirect
 *
 * @author	Sujin 수진 Choi
 * @package	wp-express
 * @version	4.5.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

if ( !function_exists( 'loadTINCANNYWordpressExpress' ) ) {
	function loadTINCANNYWordpressExpress() {
		spl_autoload_register( function( $className ) {
			$namespace = 'WE_TINCANNY\\';
			if ( stripos( $className, $namespace ) === false ) {
		        	return;
			}

			$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
			$fileName  = str_replace( [ $namespace, '\\' ], [ $sourceDir, DIRECTORY_SEPARATOR ], $className ) . '.php';

			if ( is_readable( $fileName ) ) {
				include $fileName;
			}
		});
	}

	loadTINCANNYWordpressExpress();
}

if ( !class_exists('WE_Redirect' ) ) {
	class WE_Redirect {
		public function __construct() {
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) );
		}

		public function wp_redirect( $location = false ) {
			if ( !$location ) $location = $_SERVER[ 'REQUEST_URI' ];

			if ( headers_sent() ) {
				printf( '<meta http-equiv="refresh" content="0; url=%s">', $location );
				printf( '<script>window.location="%s"</script>', $location );

				die;
			}

			return $location;
		}

	}
	new WE_Redirect;
}
