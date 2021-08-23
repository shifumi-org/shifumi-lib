<?php
/**
 * Storyline Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\FileSystem\Module;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class ArticulateRise extends \TINCANNYSNC\FileSystem\absModule {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'ArticulateRise' );
	}

	// implement
	protected function get_registering_url() {
		return $this->get_target_url() . '/scormdriver/indexAPI.html';
	}

	protected function add_tincan_support() {
		$this->add_nonce_block_code();

		$target = $this->get_target_dir();

		$file_js = 'assets/scripts/module_supports/tc-config.js';
		$file    = $target . '/tc-config.js';
		copy( SnC_PLUGIN_DIR . $file_js, $file );


	}

	// Replace scormdriver.js to a working version
	public function add_nonce_block_code() {
		$target = $this->get_target_dir();

		$scormdriver = $target . '/scormdriver/scormdriver.js';

/*
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = self::NONCE_BLOCK . $contents;
			file_put_contents( $scormdriver, $contents );
		}
*/

		// Delete scormdriver.js
		unlink( $scormdriver );

		// Copy scormdriver.js
		copy( SnC_PLUGIN_DIR . 'assets/scripts/module_supports/scormdriver-rise.js', $scormdriver );
	}

	public function replace_nonce_block_code() {
		$target = $this->get_target_dir();

		$scormdriver = $target . '/scormdriver/scormdriver.js';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $scormdriver, $contents );
		}
	}
}
