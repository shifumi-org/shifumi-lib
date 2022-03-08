<?php
/**
 *
 * WE\HtmlTrait Trait
 *
 * @author	Sujin 수진 Choi
 * @package	wp-hacks
 * @version	3.0.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

namespace WE_TINCANNY\Extensions;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

trait HtmlHelper {
	private function showMessage( $text, $class = 'updated' ) {
		printf( '<div id="message" class="%s"><p>%s</a></p></div>', $class, $text );
	}
}