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

class Storyline extends \TINCANNYSNC\FileSystem\absModule {
	private static $storyline_files = array( 'player.html', 'index_lms.html', 'story.html', 'engage.html', 'quiz.html', 'presentation.html', 'interaction.html', 'index.html' );
	const TINCAN_XML = '<?xml version="1.0" encoding="utf-8" ?>
<tincan xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://projecttincan.com/tincan.xsd">
	<activities>
		<activity id="http://6fg3yLjGkSP_course_id/68BX45jec11/6NeSgMp6swC" type="http://adlnet.gov/expapi/activities/cmi.interaction">
			<name lang="und">What is the correct answer to this question?</name>
			<description lang="und">What is the correct answer to this question?</description>
		</activity>
	</activities>
</tincan>';

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Storyline' );
	}

	// implement
	protected function get_registering_url() {
		//check in tincan.xml exist then get launch file from it.
		$target = $this->get_target_dir();

		if ( file_exists( $target . '/tincan.xml' ) ){
			$contents = file_get_contents( $target . '/tincan.xml' );
			$xml = simplexml_load_string( $contents );
			
			if ( $xml->activities->activity->launch ) {
				return $this->get_target_url() . '/' . $xml->activities->activity->launch;
			}
		}
		
		// force index_lms.html before finding other files. On some OS directory list is not sorted
		if ( $return_file = $this->in_array_search( 'index_lms.html', $this->get_dir_contents() ) ) {
			
			return $this->get_target_url() . '/' . $return_file;
		}
		
		if ( $return_file = $this->in_array_search( self::$storyline_files, $this->get_dir_contents() ) ) {
			
			return $this->get_target_url() . '/' . $return_file;
		}
		return false;
	}

	protected function add_tincan_support() {
		$target = $this->get_target_dir();

		$this->add_nonce_block_code();
		$this->add_360_SCORM_support();

		if ( file_exists( $target . '/tincan.xml' ) )
			return false;

		// XML
		$this->generate_tincan_xml();

		// story.html
		$file       = $target . '/story.html';
		$story_html = file_get_contents( $file );
		$story_html = preg_replace( '(g_bLMS\s?=\s?true)', 'g_bLMS = false', $story_html );
		$story_html = preg_replace( '(g_bTinCan\s?=\s?false)', 'g_bTinCan = true', $story_html );

		file_put_contents( $file, $story_html );
		
		// index_lms.html
		$file       = $target . '/index_lms.html';
		if( file_exists( $file ) ) {
			$story_html = file_get_contents( $file );
			$story_html = preg_replace( '(g_bLMS\s?=\s?true)', 'g_bLMS = false', $story_html );
			$story_html = preg_replace( '(g_bTinCan\s?=\s?false)', 'g_bTinCan = true', $story_html );
			
			file_put_contents( $file, $story_html );
		}
		
		// index_lms_html5.html
		$file       = $target . '/index_lms_html5.html';
		if( file_exists( $file ) ) {
			$story_html = file_get_contents( $file );
			$story_html = preg_replace( '(g_bLMS\s?=\s?true)', 'g_bLMS = false', $story_html );
			$story_html = preg_replace( '(g_bTinCan\s?=\s?false)', 'g_bTinCan = true', $story_html );
			
			file_put_contents( $file, $story_html );
		}
	}

	private function add_360_SCORM_support() {
		$target = $this->get_target_dir();

		if ( !file_exists( $target . '/imsmanifest.xml' ) )
			return false;
		
		// index_lms.html
		$file       = $target . '/index_lms.html';
		if( file_exists( $file ) ) {
			$story_html = file_get_contents( $target . '/index_lms.html' );
			$story_html = preg_replace( '(tinCanPresent\s?:\s?false)', 'tinCanPresent: true', $story_html );
			file_put_contents( $target . '/index_lms.html', $story_html );
		}
		
		// index_lms_html5.html
		$file       = $target . '/index_lms_html5.html';
		if( file_exists( $file ) ) {
			$story_html = file_get_contents( $target . '/index_lms_html5.html' );
			$story_html = preg_replace( '(tinCanPresent\s?:\s?false)', 'tinCanPresent: true', $story_html );
			file_put_contents( $target . '/index_lms_html5.html', $story_html );
		}
		
		if ( !file_exists( $target . '/story_html5.html' ) )
			return false;

		$story_html = file_get_contents( $target . '/story_html5.html' );
		$story_html = preg_replace( '(tinCanPresent\s?:\s?false)', 'tinCanPresent: true', $story_html );

		file_put_contents( $target . '/story_html5.html', $story_html );

		// Flash Version
		if ( !file_exists( $target . '/story_flash.html' ) )
			return false;

		$story_html = file_get_contents( $target . '/story_flash.html' );
		$story_html = preg_replace( '(g_bTinCan\s?=\s?false)', 'g_bTinCan = true', $story_html );

		file_put_contents( $target . '/story_flash.html', $story_html );
	}

	public function add_nonce_block_code() {
		$target = $this->get_target_dir();

		// TinCan
		$story_js = $target . '/story_content/user.js';
		if ( file_exists( $story_js ) ) {
			$contents = file_get_contents( $story_js );
			$contents = self::NONCE_BLOCK . $contents;
			file_put_contents( $story_js, $contents );
		}
	}

	public function replace_nonce_block_code() {
		$target = $this->get_target_dir();

		// TinCan
		$story_js = $target . '/story_content/user.js';
		if ( file_exists( $story_js ) ) {
			$contents = file_get_contents( $story_js );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $story_js, $contents );
		}
	}

	private function generate_tincan_xml() {
		$target = $this->get_target_dir();

		if ( file_exists( $target . '/story_content/frame.xml' ) ) {
			$content = $this->generate_tincan_xml_from_frame_xml( $target . '/story_content/frame.xml' );
		} else {
			$content = $this->generate_tincan_xml_in_general();
		}

		$file = $target . '/tincan.xml';
		file_put_contents( $file, $content );

	}

	private function generate_tincan_xml_from_frame_xml() {
		$target    = $this->get_target_dir();
		$frame_xml = $target . '/story_content/frame.xml';

		$tincan_xml = '<?xml version="1.0" encoding="utf-8"?><tincan xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://projecttincan.com/tincan.xsd"><activities></activities></tincan>';
		$tincan_xml = simplexml_load_string( $tincan_xml );

		$frame_xml = file_get_contents( $frame_xml );
		$frame_xml = simplexml_load_string( $frame_xml );

		if ( !$frame_xml->nav_data->outline->links )
			return ;

		foreach( $frame_xml->nav_data->outline->links as $v ) {
			foreach( $v->children() as $slidelink ) {
				$this->add_activity( $tincan_xml, $slidelink );

				foreach( $slidelink->links->slidelink as $child_slidelink ) {
					$this->add_activity( $tincan_xml, $child_slidelink );
				}
			}
		}

		return $tincan_xml->asXML();
	}

	private function add_activity( &$tincan, $frame ) {
		$activity = $tincan->activities->addChild( 'activity' );
		$id       = $frame->attributes()['slideid'];
		$id       = substr( $id, -11 );

		$activity->addAttribute( 'id', $id );
		$name = $activity->addChild( 'name', $frame->attributes()['displaytext'] );
	}

	private function generate_tincan_xml_in_general() {
		return self::TINCAN_XML;
	}
}
