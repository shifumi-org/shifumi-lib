<?php
/**
 * Database\Completion
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.3.0
 */

namespace UCTINCAN\Database;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Resume extends \UCTINCAN\Database {
	use \UCTINCAN\Modules;

	public function get_resume( $url ) {
		global $wpdb;

		if ( ! $this->is_table_exists() ) {
			return false;
		}
		$table_name = $wpdb->prefix . self::TABLE_RESUME;
		
		$module_id = $this->get_slide_id_from_url( $url );

		$query = $wpdb->prepare( "
			SELECT `value` FROM {$table_name}
				WHERE
					`user_id`   = %s AND
					`module_id` = %s
			LIMIT 1
			",
			self::$user_id,
			$module_id[1]
		);

		$value = $wpdb->get_var( $query );

		return $value;
	}

	public function save_resume( $url, $content ) {
		/**
		 * v3.4-If user is not logged in, return
		 */
		if ( ! is_user_logged_in() ) {
			return;
		}

		global $wpdb;

		if ( ! $this->is_table_exists() ) {
			return;
		}

		/**
		 * v3.4-If user id not numeric, return
		 */
		if ( ! is_numeric( self::$user_id ) ) {
			return;
		}

		if ( ! $content ) {
			return;
		}

		if ( $this->get_resume( $url ) ) {
			$this->delete_resume( $url );
		}

		$module_id = $this->get_slide_id_from_url( $url );

		$query = $wpdb->prepare( " INSERT INTO %s%s ( `user_id`, `module_id`, `value` ) VALUES ( %s, %s, '%s' ); ",
			$wpdb->prefix,
			self::TABLE_RESUME,
			self::$user_id,
			$module_id[1],
			$content
		);

		$query = $wpdb->query( $query );
	}

	private function delete_resume( $url ) {
		global $wpdb;

		$module_id = $this->get_slide_id_from_url( $url );
		$table_name = $wpdb->prefix . self::TABLE_RESUME;
		
		$query = $wpdb->prepare( "
			DELETE FROM {$table_name} WHERE user_id = %s AND module_id = %s
		", self::$user_id, $module_id[1] );

		$query = $wpdb->query( $query );
	}
}
