<?php
/**
 * Processing Request : H5P
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.3.0
 */

namespace UCTINCAN\TinCanRequest;

use UCTINCAN\Services;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class H5P extends \UCTINCAN\TinCanRequest {
	private $decoded;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $decoded
	 * @return void
	 * @since  1.0.0
	 */
	function __construct( $decoded ) {
		if ( !$decoded )
			return false;

		$this->decoded = $decoded;

		if ( ! $this->init_tincan_objects( $decoded ) )
			return;

		$this->set_H5P();
		$this->save();
	}

	/**
	 * Set Modules
	 *
	 * @access protected
	 * @return void
	 * @since  1.0.0
	 */
	private function set_H5P() {
		$pattern = '/&id=([0-9]+)/';
		preg_match( $pattern, $this->TC_Actitity->getId(), $result_id );
		$this->content_id = $result_id[1];
	}

	/**
	 * Return Moldule and Target Data
	 *
	 * @access protected
	 * @return void
	 * @since  1.0.0
	 */
	protected function get_module_and_target() {
		global $wpdb;
		$module = $target = $module_name = $target_name = '';

		$module = admin_url( 'admin-ajax.php?action=h5p_embed&id=' . $this->content_id );

		$module_name_query = sprintf( "
			SELECT `title` FROM %s%s
				WHERE `id` = '%s'
			",
			$wpdb->prefix,
			self::$TABLE_H5P_CONTENTS,
			$this->content_id
		);

		$module_name = $wpdb->get_var( $module_name_query );
		$target_name = urldecode( array_pop( $this->TC_Actitity->getDefinition()->getName()->_map ) );

		if ( !$target_name )
			$target = $target_name = '';

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}

	public function get_completion() {
		$service    = new Services();
		$completion = $service->check_h5p_completion_( $this->content_id, $this->lesson_id, $this->user_id );
		return $completion;
	}
}
