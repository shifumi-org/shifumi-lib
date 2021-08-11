<?php
/**
 * Processing Request : iSpring
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

class iSpring extends \UCTINCAN\TinCanRequest\Slides {
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

		if ( ! $this->init_iSpring_tincan_objects( $decoded ) )
			return;

		$this->set_slides();
		$this->save();
	}

	private function init_iSpring_tincan_objects( $decoded ) {
		$current_user = wp_get_current_user();

		$decoded[ 'actor' ][ 'name' ] = $current_user->data->user_login;
		$decoded[ 'actor' ][ 'mbox' ] = 'mailto:' . $current_user->data->user_email;

		return $this->init_tincan_objects( $decoded );
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
		$target_name = $this->get_target_from_activity_definition( $target_name );

		if ( !$target_name )
			$target = $target_name = '';

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}
}
