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

class UnknownType extends \TINCANNYSNC\FileSystem\absModule {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Unknown' );
	}

	protected function get_registering_url() {}
	protected function add_tincan_support() {}

	public function add_nonce_block_code() {
		$target = $this->get_target_dir();
		$target = explode( '/wp-content/uploads', $target );
		$entry = $target[0] . $this->get_url();

		$is_html = substr( $entry, (- strlen( '.html' ) ) ) === '.html' || substr( $entry, (- strlen( '.html5' ) ) ) === '.html5';

		if ( file_exists( $entry ) && $is_html ) {
			$contents = file_get_contents( $entry );
			$contents = $contents . '<script>' . self::NONCE_BLOCK . '</script>';

			file_put_contents( $entry, $contents );
		}
	}
}
