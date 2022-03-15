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

class iSpring extends \TINCANNYSNC\FileSystem\absModule {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'iSpring' );
	}
	
	// implement
	protected function get_registering_url() {
		$target = $this->get_target_dir();
		
		if ( $this->get_subtype() == 'web' )
			return $this->get_target_url() . '/index.html';
		
		if ( file_exists( $target . '/res/index.html' ) )
			return $this->get_target_url() . '/res/index.html';
		
		return false;
	}
	
	protected function add_tincan_support() {
		$this->add_nonce_block_code();
		
		if ( $this->get_subtype() == 'web' )
			return true;
		
		$target = $this->get_target_dir();
		
		// index.html
		$file         = $target . '/res/index.html';
		$ispring_html = file_get_contents( $file );
		$file_js      = 'assets/scripts/module_supports/iSpring-lms.js';
		
		
		$explode_wp_content_dir        = explode( '/', WP_CONTENT_DIR );
		$maybe_sub_from_wp_content_dir = $explode_wp_content_dir[ count( $explode_wp_content_dir ) - 2 ];
		
		$explode_site_url        = explode( '/', get_site_url() );
		$maybe_sub_from_site_url = array_pop( $explode_site_url );
		
		$explode_abspath        = explode( '/', ABSPATH );
		$maybe_sub_from_abspath = $explode_wp_content_dir[ count( $explode_abspath ) - 2 ];
		
		$subdirectory = '';
		if ( $maybe_sub_from_wp_content_dir === $maybe_sub_from_site_url &&
		     $maybe_sub_from_site_url === $maybe_sub_from_abspath &&
		     $maybe_sub_from_site_url === $maybe_sub_from_wp_content_dir
		) {
			$subdirectory = '/'.$maybe_sub_from_site_url;
		}
		
		//$ispring_html = preg_replace( '/endPoint\s?:\s?"([^"]*)/', 'endPoint: "' . get_bloginfo( 'wpurl' ) . '/ucTinCan/iSpring/', $ispring_html );
		//$ispring_html = preg_replace( '/endPoint\s?:\s?"([^"]*)/', 'endPoint: window.location.protocol + "//" + window.location.hostname + "'.$subdirectory.'/ucTinCan/iSpring/', $ispring_html );
		$ispring_html = preg_replace( '/endPoint\s?:\s?"([^"]*)/', 'endPoint: baseUrl + "'.$subdirectory.'/ucTinCan/iSpring/', $ispring_html );
		
		$ispring_html = preg_replace( '/login\s?:\s?"([^"]*)/', 'login: "1', $ispring_html );
		$ispring_html = preg_replace( '/password\s?:\s?"([^"]*)/', 'password: "1', $ispring_html );
		$ispring_html = preg_replace( '/name\s?:\s?"([^"]*)/', 'name: "1', $ispring_html );
		$ispring_html = preg_replace( '/email\s?:\s?"([^"]*)/', 'email: "1', $ispring_html );
		
		preg_match_all( '/iSpring\.LMS\.create\("([A-Z_]+)", "([\.A-Za-z0-9]+)/', $ispring_html, $match1 );
		preg_match_all( '/iSpring\.quiz\.LMS\.create\("([\.A-Za-z0-9]+)", params/', $ispring_html, $match2 );
		
		// Normal
		if ( !empty( $match1[0] ) ) {
			$ispring_html = preg_replace( '/iSpring\.LMS\.create\("([A-Z_]+)", "([\.A-Za-z0-9]+)/', 'iSpring.LMS.create("${1}", "tincan', $ispring_html );
			
			// Quiz
		} else if ( !empty( $match2[0] ) ) {
			$ispring_html = preg_replace( '/iSpring\.quiz\.LMS\.create\("([\.A-Za-z0-9]+)", params/', 'iSpring.quiz.LMS.create("tincan", params', $ispring_html );
			$file_js      = 'assets/scripts/module_supports/iSpring-lms-quiz.js';
		}
		
		file_put_contents( $file, $ispring_html );
		
		// Replace lms.js
		if ( file_exists( $target . '/tincan.xml' ) )
			return true;
		
		$file = $target . '/res/lms.js';
		unlink( $file );
		
		copy( SnC_PLUGIN_DIR . $file_js, $file );
	}
	
	public function add_nonce_block_code() {
		$target = $this->get_target_dir();
		
		// Web
		$index_html = $target . '/index.html';
		if ( file_exists( $index_html ) ) {
			$contents = file_get_contents( $index_html );
			// add after body tag
			$contents = str_replace( '<body>', '<body><script>' . self::NONCE_BLOCK . '</script>', $contents );
			file_put_contents( $index_html, $contents );
		}
		
		// TinCan
		$index_html = $target . '/res/index.html';
		if ( file_exists( $index_html ) ) {
			$contents = file_get_contents( $index_html );
			// add after body tag
			$contents = str_replace( '<body>', '<body><script>' . self::NONCE_BLOCK . '</script>', $contents );
			file_put_contents( $index_html, $contents );
		}
	}
	
	public function replace_nonce_block_code() {
		$target = $this->get_target_dir();
		
		// Web
		$index_html = $target . '/index.html';
		if ( file_exists( $index_html ) ) {
			$contents = file_get_contents( $index_html );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $index_html, $contents );
		}
		
		// TinCan
		$index_html = $target . '/res/index.html';
		if ( file_exists( $index_html ) ) {
			$contents = file_get_contents( $index_html );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $index_html, $contents );
		}
	}
}
