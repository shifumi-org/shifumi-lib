<?php
/**
 * Processing Request : ArticulateRise2017
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

class ArticulateRise2017 extends \UCTINCAN\TinCanRequest\Slides {
	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $decoded
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $decoded, $decoded2 ) {
		if ( !$decoded )
			return false;

		if ( ! $this->init_tincan_objects( $decoded ) )
			return;

		$this->set_slides( $decoded2 );
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

		if ( count( $this->TC_Context->getContextActivities()->getParent() ) > 1 )
			$target_name = urldecode( array_pop( $this->TC_Actitity->getDefinition()->getName()->_map ) );
		else
			$target_name = $this->get_target_from_activity_definition( $target_name );

		if ( !$target_name )
			$target = $target_name = '';

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}
}
