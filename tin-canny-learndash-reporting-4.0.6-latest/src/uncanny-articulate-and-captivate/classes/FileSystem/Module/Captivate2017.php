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

class Captivate2017 extends \TINCANNYSNC\FileSystem\absModule {
	const TINCAN_XML = '<?xml version="1.0" encoding="utf-8" ?>
<tincan xmlns="http://projecttincan.com/tincan.xsd">
	<activities>
		<activity id="http://sujinc.com" type="http://adlnet.gov/expapi/activities/course">
			<name>E-Learning Course</name>
			<description lang="en-US">Course Description.</description>
			<launch lang="en-US">index_TINCAN.html</launch>
		</activity>
	</activities>
</tincan>
';

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Captivate2017' );
	}

	// implement
	protected function get_registering_url() {
		$has_multiscreen = $entryName = false;

		$target    = $this->get_target_dir();
		$file_list = $this->get_dir_contents();

		if ( ! file_exists( $target . '/tincan.xml' ) )
			return false;

		$contents = file_get_contents( $target . '/tincan.xml' );
		$xml = simplexml_load_string( $contents );

		if ( ! $xml->activities->activity->launch )
			return false;

		return $this->get_target_url() . '/' . $xml->activities->activity->launch;
	}

	protected function add_tincan_support() {
		$this->add_nonce_block_code();

		if ( $this->get_subtype() == 'web' )
			return true;

		$target = $this->get_target_dir();

		if ( file_exists( $target . '/tincan.xml' ) )
			return false;

		// XML
		$file = $target . '/tincan.xml';

		file_put_contents( $file, self::TINCAN_XML );

		// Delete scormdriver.js
		$file = $target . '/scormdriver.js';
		unlink( $file );

		// Copy scormdriver.js
		copy( SnC_PLUGIN_DIR . 'assets/scripts/module_supports/scormdriver.js', $file );

		if ( file_exists( $target . '/tc-config.js' ) )
			return true;

		// Copy tc-config.js
		copy( SnC_PLUGIN_DIR . 'assets/scripts/module_supports/tc-config.js', $target . '/tc-config.js' );

		file_put_contents( $target . '/NOTE_FORCE_TO_TINCAN', ' ' );

		return true;
	}

	public function add_nonce_block_code() {
		$target = $this->get_target_dir();

		// TinCan
		$tcconfig_js = $target . '/tc-config.js';
		if ( file_exists( $tcconfig_js ) ) {
			$contents = file_get_contents( $tcconfig_js );
			$contents = self::NONCE_BLOCK . $contents;
			file_put_contents( $tcconfig_js, $contents );
		} else {
			$standard_js = $target . '/standard.js';
			if ( file_exists( $standard_js ) ) {
				$contents = file_get_contents( $standard_js );
				$contents = self::NONCE_BLOCK . $contents;
				file_put_contents( $standard_js, $contents );
			}
		}
	}

	public function replace_nonce_block_code() {
		$target = $this->get_target_dir();

		// TinCan
		$tcconfig_js = $target . '/tc-config.js';
		if ( file_exists( $tcconfig_js ) ) {
			$contents = file_get_contents( $tcconfig_js );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $tcconfig_js, $contents );
		} else {
			$standard_js = $target . '/standard.js';
			if ( file_exists( $standard_js ) ) {
				$contents = file_get_contents( $standard_js );
				$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
				file_put_contents( $standard_js, $contents );
			}
		}
	}

	private function has_CPLibraryAll_css() {
		$target       = $this->get_target_dir();
		$file_objects = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $target ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach( $file_objects as $key => $object ) {
			if( $object->getFilename() === 'CPLibraryAll.css' ) {
				return true;
			}
		}

		return false;
	}
}
