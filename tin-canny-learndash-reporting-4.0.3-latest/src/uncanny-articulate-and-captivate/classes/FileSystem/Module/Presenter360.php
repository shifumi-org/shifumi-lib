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

class Presenter360 extends \TINCANNYSNC\FileSystem\absModule {
	private static $storyline_files = array( 'index_lms.html', 'presentation.html' );
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Presenter360' );
	}

	protected function get_registering_url() {
		if ( $return_file = $this->in_array_search( self::$storyline_files, $this->get_dir_contents() ) )
			return $this->get_target_url() . '/' . $return_file;
		
		return false;
	}
	protected function add_tincan_support() {
		$this->add_nonce_block_code();
	}

	public function add_nonce_block_code() {
		$target = $this->get_target_dir();
/*
		$target = explode( '/wp-content/uploads', $target );
		$entry = $target[0] . $this->get_url();

		$is_html = substr( $entry, (- strlen( '.html' ) ) ) === '.html' || substr( $entry, (- strlen( '.html5' ) ) ) === '.html5';

		if ( file_exists( $entry ) && $is_html ) {
			$contents = file_get_contents( $entry );
			$contents = $contents . '<script>' . self::NONCE_BLOCK . '</script>';

			file_put_contents( $entry, $contents );
		}
	}
*/
		// index_lms.html
		$file       = $target . '/index_lms.html';
		if( file_exists( $file ) ) {
			$contents = file_get_contents( $target . '/index_lms.html' );
			$contents = preg_replace( '(tinCanPresent\s?:\s?false)', 'tinCanPresent: true', $contents );
			file_put_contents( $target . '/index_lms.html', $contents );
		}
		
		// index_lms_html5.html
		$file       = $target . '/index_lms_html5.html';
		if( file_exists( $file ) ) {
			$contents = file_get_contents( $target . '/index_lms_html5.html' );
			$contents = preg_replace( '(tinCanPresent\s?:\s?false)', 'tinCanPresent: true', $contents );
			file_put_contents( $target . '/index_lms_html5.html', $contents );
		}
		$scormdriver = $target . '/presentation.html';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( '<head>', '<head><script>' . self::NONCE_BLOCK . '</script>', $contents );
			file_put_contents( $scormdriver, $contents );
		}

	}
}
