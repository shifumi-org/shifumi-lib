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

class Xapi extends \TINCANNYSNC\FileSystem\absModule {
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Tincan' );
	}
	
	// implement
	protected function get_registering_url() {
		//check in tincan.xml exist then get launch file from it.
		$target = $this->get_target_dir();
		
		if ( file_exists( $target . '/tincan.xml' ) ) {
			$contents    = file_get_contents( $target . '/tincan.xml' );
			$xml         = simplexml_load_string( $contents );
			
			$launch_file = '';
			if ( ! empty( $xml->activities->activity->launch ) ) {
				$launch_file = (string) $xml->activities->activity->launch;
			}
			if( empty( $launch_file ) ) {
				foreach ( $xml->children() as $key => $node ) {
					if ( $key === 'resources' ) {
						foreach ( $node->children() as $nkey => $resource ) {
							if ( isset( $resource['href'] ) ) {
								$launch_file = (string) $resource['href'];
							}
							if ( ! empty( $launch_file ) ) {
								break;
							}
						}
					}
				}
			}
			
			if ( ! empty( $launch_file ) ) {
				return $this->get_target_url() . '/' . $launch_file;
			}
		}
		
		// force index_lms.html before finding other files. On some OS directory list is not sorted
		if ( $return_file = $this->in_array_search( 'index.html', $this->get_dir_contents() ) ) {
			return $this->get_target_url() . '/' . $return_file;
		}
		if ( $return_file = $this->in_array_search( 'story.html', $this->get_dir_contents() ) ) {
			return $this->get_target_url() . '/' . $return_file;
		}
		if ( $return_file = $this->in_array_search( 'player.html', $this->get_dir_contents() ) ) {
			return $this->get_target_url() . '/' . $return_file;
		}
		if ( $return_file = $this->in_array_search( 'presentation.html', $this->get_dir_contents() ) ) {
			return $this->get_target_url() . '/' . $return_file;
		}
		
		return false;
	}
	
	protected function add_tincan_support() {
		$target = $this->get_target_dir();
		
		if ( file_exists( $target . '/tincan.xml' ) )
			return false;
		
		return;
	}
}
