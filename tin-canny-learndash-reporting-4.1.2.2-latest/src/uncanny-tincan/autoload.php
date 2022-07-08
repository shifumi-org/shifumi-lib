<?php
/**
 * TinCan Class Autoload
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

if ( !function_exists( 'loadUoTinCan' ) ) {
	function loadUoTinCan() {
		spl_autoload_register( function( $className ) {
			$namespace = 'UCTINCAN\\';
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

	loadUoTinCan();
}