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

class Lectora extends \TINCANNYSNC\FileSystem\absModule {
	
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
		$this->set_type( 'Lectora' );
	}
	
	protected function get_registering_url() {
		return $this->get_target_url() . '/a001index.html';
	}
	
	protected function add_tincan_support() {
		
		$script_tags = '';
		// check if apiwrapper11.js exist then replace it with modified one
		$target      = $this->get_target_dir();
		$api_wrapper = $target . '/apiwrapper11.js';
		if ( file_exists( $api_wrapper ) ) {
			@unlink( $api_wrapper );
			$file_js = 'assets/scripts/module_supports/lectora_apiwrapper.js';
			
			copy( SnC_PLUGIN_DIR . $file_js, $api_wrapper );
			$file_js           = 'assets/scripts/module_supports/lectora_functions.js';
			$lectora_functions = $target . '/lectora_functions.js';
			copy( SnC_PLUGIN_DIR . $file_js, $lectora_functions );
			$script_tags = '<script src="lectora_functions.js"></script>';
		}
		$this->add_nonce_block_code( $script_tags );
	}
	
	public function add_nonce_block_code( $script = '' ) {
		$target = $this->get_target_dir();
		
		$scormdriver = $target . '/a001index.html';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( '</head>', '<script>' . self::NONCE_BLOCK . '</script>' . $script . '</head>', $contents );
			file_put_contents( $scormdriver, $contents );
		}
		
	}
}
