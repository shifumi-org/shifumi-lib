<?php
/**
 * Module Classes Autoload
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

if ( !function_exists( 'load_tincanny_snc' ) ) {
	function load_tincanny_snc() {
		spl_autoload_register( function( $className ) {
			$namespace = 'TINCANNYSNC\\';
			if ( stripos( $className, $namespace ) === false ) {
		        	return;
			}

			$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
			$fileName  = str_replace( [ $namespace, '\\' ], [ $sourceDir, DIRECTORY_SEPARATOR ], $className ) . '.php';

			if ( is_readable( $fileName ) ) {
				include_once( $fileName );
			}
		});
	}

	load_tincanny_snc();
}
