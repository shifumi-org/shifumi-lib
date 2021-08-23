<?php
/**
 * Processing Request : Storyline
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.3.0
 */

namespace UCTINCAN\TinCanRequest\Slides;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Storyline extends \UCTINCAN\TinCanRequest\Slides {
	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $decoded
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $decoded, $decoded_2 ) {
		if ( !$decoded )
			return false;

		if ( ! $this->init_tincan_objects( $decoded ) )
			return;

		$this->set_slides( $decoded_2 );
		$this->save();
	}

	/**
	 * Return Moldule and Target Data
	 *
	 * @access protected
	 * @return void
	 * @since  1.0.0
	 */
	protected function get_module_and_target() {
		extract( $this->get_module() );

		$target_ = $this->TC_Actitity->getId();
		$target_name = $this->get_target_from_activity_definition( $target_name );

		if ( strlen( $target_ ) > 11 &&  !$target_name ) {
			$pattern = "/story.html\/([a-zA-Z0-9]+)/";
			preg_match( $pattern, $target_, $match );
			$target_ = isset( $match[1] ) ? $match[1] : '';
		}

		if ( strlen( $target_ ) == 11 ) {
			$wp_upload_dir = wp_upload_dir();
			$tincan_xml = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME . '/' . $this->content_id . '/tincan.xml';

			if ( file_exists( $tincan_xml ) ) {
				$xml = simplexml_load_file( $tincan_xml );

				foreach( $xml->activities->activity as $activity ) {
					$activity = (array) $activity;

					if ($activity[ '@attributes' ][ 'id' ] == $target_ ) {
						$target_name = $activity[ 'name' ];
						$target = $module;

						break;
					}
				}
			}
		}

		if ( !$target_name )
			$target = $target_name = '';

		// Parent-Child
		if ( $module != $target ) {
			global $wpdb;

			$query = sprintf( "SELECT module_name FROM %s%s WHERE target = '%s' LIMIT 1;",
				$wpdb->prefix,
				\UCTINCAN\Database::TABLE_REPORTING,
				$module
			);

			if ( $module_name_ = $wpdb->get_var( $query ) )
				$module_name = $module_name_;
		}

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}
}
