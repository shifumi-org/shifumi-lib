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

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class ArticulateRise2017 extends \TINCANNYSNC\FileSystem\absModule {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'AR2017' );
	}

	// implement
	protected function get_registering_url() {
		return $this->get_target_url() . '/index.html';
	}

	protected function add_tincan_support() {
		$this->add_nonce_block_code();

		/*
				$target = $this->get_target_dir();

				$file_js = 'assets/scripts/module_supports/tc-config.js';
				$file    = $target . '/tc-config.js';
				copy( SnC_PLUGIN_DIR . $file_js, $file );
		*/
	}

	public function add_nonce_block_code() {
		$target = $this->get_target_dir();

		$scormdriver = $target . '/index.html';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( '<head>', '<head><script>' . self::NONCE_BLOCK . '</script>', $contents );
			file_put_contents( $scormdriver, $contents );
		}
	}

	public function replace_nonce_block_code() {
		$target = $this->get_target_dir();

		$scormdriver = $target . '/index.html';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $scormdriver, $contents );
		}
	}
}
