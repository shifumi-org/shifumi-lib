<?php
/**
 * Processing Request : Captivate
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

class Captivate extends \UCTINCAN\TinCanRequest\Slides {
	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $decoded
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $decoded ) {
		if ( !$decoded )
			return false;

		if ( ! $this->init_tincan_objects( $decoded ) )
			return;

		$this->set_slides();
		$this->save_captivate();
	}

	/**
	 * Save Data
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function save_captivate() {
		$result_save = $this->save();

		if ( ! $result_save )
			return;

		extract( $result_save );
		$database = new \UCTINCAN\Database\Completion();

		if ( ! $database->get_completion_by_URL( $module, $course_id, $lesson_id ) ) {
			$wp_upload_dir = wp_upload_dir();
			$tincan_xml = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME . '/' . $this->content_id . '/project.txt';

			if ( file_exists( $tincan_xml ) ) {
				$json_data = file_get_contents( $tincan_xml );
				$json_data = json_decode($json_data, true);

				$num_slides = $json_data[ 'metadata' ][ 'totalSlides' ];

				$last_slide = array_pop( $json_data[ 'toc' ] );

				$last_slide_id_1 = urldecode( $this->TC_Actitity->getId() );
				$last_slide_id_2 = str_replace( '_', ' ', $last_slide_id_1 );

				if ( strstr( $last_slide_id_1, $last_slide[ 'title' ] ) !== false || strstr( $last_slide_id_2, $last_slide[ 'title' ] ) !== false ) {
					$database->set_report( $group_id, $course_id, $lesson_id, $module, $module_name, '', '', 'completed', $result, $maximum, $completion, $user_id );
				}
			}
		}
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

		if ( count( $this->TC_Context->getContextActivities()->getParent() ) > 1 )
			$target_name = urldecode( array_pop( $this->TC_Actitity->getDefinition()->getName()->_map ) );
		else
			$target_name = $this->get_target_from_activity_definition( $target_name );

		if ( !$target_name )
			$target = $target_name = '';

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}
}
