<?php
/**
 * WP_UserProfile
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN\Admin;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class WP_UserProfile {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'edit_user_profile_update', array( $this, 'learndash_delete_user_data_link' ), 15 );
		add_action( 'personal_options_update',  array( $this, 'learndash_delete_user_data_link' ), 15 );
		add_action( 'delete_user', array( $this, 'learndash_delete_user_data' ), 15 );
	}

	/**
	 * Delete User's TinCan Data
	 *
	 * @access public
	 * @param  int $user_id
	 * @return void
	 * @since  1.0.0
	 */
	public function learndash_delete_user_data_link( $user_id ) {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		if ( ! empty( $user_id ) && ! empty( $_POST['learndash_delete_user_data'] ) && $user_id == $_POST['learndash_delete_user_data'] )
			\UCTINCAN\Database\Admin::delete_by_user( $user_id );
	}
	
	/**
	 * Delete User's TinCan Data on user delete
	 *
	 * @access public
	 * @param  int $user_id
	 * @return void
	 * @since  3.4.0
	 */
	public function learndash_delete_user_data( $user_id ) {
		
		if ( ! empty( $user_id ) )
			\UCTINCAN\Database\Admin::delete_by_user( $user_id );
	}
}
