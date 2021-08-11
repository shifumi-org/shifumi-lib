<?php
/**
 * File System Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\FileSystem;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

trait traitModule {
	private static $upload_dir;
	private static $upload_url;

	private $item_id;
	private $name;
	private $url;
	private $type;
	private $subtype;

	static protected function get_dir_path() {
		if ( ! self::$upload_dir ) {
			$wp_upload_dir    = wp_upload_dir();
			self::$upload_dir = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME;

			if ( ! file_exists( self::$upload_dir ) ) {
				self::create_upload_dir();
			}
		}

		return self::$upload_dir;
	}


	static protected function get_url_path() {
		if ( ! self::$upload_url ) {
			$wp_upload_dir    = wp_upload_dir();
			self::$upload_url = $wp_upload_dir['baseurl'] . '/' . SnC_UPLOAD_DIR_NAME;

			// some edge case sites not returning proper http
			self::$upload_url = is_ssl() ? str_replace( 'http://', 'https://', self::$upload_url ) : self::$upload_url;

			self::$upload_url = str_replace( get_site_url(), '', self::$upload_url );
		}

		return self::$upload_url;
	}

	/**
	 * Create Upload Directory
	 *
	 * @since  0.0.1
	 * @access public
	 */
	static private function create_upload_dir() {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		\WP_Filesystem();

		$dir_perms = 0777;
		$wp_filesystem->mkdir( self::$upload_dir, $dir_perms );

		@file_put_contents( self::$upload_dir . "/index.html", "" );
	}

	protected function get_target_dir() {
		if ( ! $this->item_id ) {
			return false;
		}

		return self::get_dir_path() . '/' . $this->item_id;
	}

	protected function get_target_url() {
		if ( ! $this->item_id ) {
			return false;
		}

		return self::get_url_path() . '/' . $this->item_id;
	}

	protected function set_item_id( $item_id ) {
		$this->item_id = $item_id;
	}

	public function set_url( $url ) {
		$this->url = $url;
	}

	protected function set_type( $type ) {
		$this->type = $type;
	}

	public function set_subtype( $subtype ) {
		$this->subtype = $subtype;
	}

	public function set_name( $name ) {
		$this->name = $name;
	}

	protected function get_item_id() {
		return $this->item_id;
	}

	public function get_url() {
		return $this->url;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_subtype() {
		return $this->subtype;
	}

	protected function in_array_search( $needle, $haystack ) {
		if ( ! is_array( $needle ) ) {
			$needle = array( $needle );
		}

		foreach ( $haystack as $value ) {
			if ( in_array( $value, $needle ) ) {
				return $value;
			}
		}

		return false;
	}
}

