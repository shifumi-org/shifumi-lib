<?php
/**
 * Modules : H5P and Storyline
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

trait Modules {
	protected $modules     = array( 'H5P', 'Storyline', 'Captivate', 'Captivate2017', 'iSpring', 'ArticulateRise', 'ArticulateRise2017', 'Presenter360', 'Lectora', 'Scorm', 'Tincan' );
	protected $module_info = array();
	protected $available   = array(
		'H5P' => false,
		'SnC' => false,
	);

	protected static $PATTERN_SHORTCODE_ID = "/\[([0-9a-zA-Z_\-]+)[^\]]*id(\s|)=(\"|\'|\s)([0-9]+)/";
	protected static $PATTERN_SLIDE_ID     = "/\/uncanny-snc\/([0-9]+)\//";

	protected static $TABLE_H5P_CONTENTS   = 'h5p_contents';
	protected static $TABLE_H5P_LIBRARY    = 'h5p_libraries';

	protected function prepare_modules() {
		if ( ! $this->check_modules() )
			return false;

		foreach( $this->modules as $value ) {
			$this->module_info[ $value ] = array(
				'contents' => array(),
				'complete' => array(),
			);
		}

		return true;
	}

	// Check H5P xAPI connection => $this->h5p_tincan
	// Check Using Uncanny AnC
	private function check_modules() {
		$available = false;

		// H5P xAPI
		if ( get_option( "h5pxapi_endpoint_url" ) ) {
			$this->available[ 'H5P' ] = true;
			$available = true;
		}

		// Uncanny AnC
		if ( class_exists( '\\TINCANNYSNC\\Init' ) ) {
			$this->available[ 'SnC' ] = true;
			$available = true;
		}

		return $available;
	}

	protected function get_slide_id_from_url( $url ) {
		preg_match( self::$PATTERN_SLIDE_ID, $url, $matches );

		return $matches;
	}

	protected function get_lesson_id_from_url( $url ) {
		$lesson_id = false;
		parse_str( $url, $decoded );

		if ( $decoded[ 'auth' ] )
			$lesson_id = substr( $decoded[ 'auth' ], 11 );

		return $lesson_id;
	}
}
