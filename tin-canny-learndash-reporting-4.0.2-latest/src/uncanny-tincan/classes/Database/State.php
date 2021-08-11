<?php
/**
 * Database\Completion
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.3.6
 */

namespace UCTINCAN\Database;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class State extends \UCTINCAN\Database {
	use \UCTINCAN\Modules;

	/**
	 * Get State Data
	 *
	 * @access public
	 * @return string
	 * @since  1.3.6
	 */
	public function get_state( $url, $state_id ) {
		global $wpdb;

		if ( ! $this->is_table_exists() )
			return false;

		// Sync table columns
		$this->add_course_lesson_coulmns();
		// Get course and lesson id from request
		list( $course_id, $lesson_id ) = $this->fetch_course_lesson_ids();

		$module_id  = $this->get_slide_id_from_url( $url );
		$table_name = $wpdb->prefix . self::TABLE_RESUME;
		// SELECT with course and lesson ids
		$query      = $wpdb->prepare( "
			SELECT `value` FROM {$table_name}
				WHERE
					`user_id`   = %s AND
					`module_id` = %s AND
					`course_id` = %s AND
					`lesson_id` = %s AND
					`state`     = '%s'
			LIMIT 1
			", self::$user_id, $module_id[1], $course_id, $lesson_id, $state_id );

		$return = $wpdb->get_var( $query );

		if ( ! empty( $return ) ) {
			return $return;
		}

		$query      = $wpdb->prepare( "
			SELECT `value` FROM {$table_name}
				WHERE
					`user_id`   = %s AND
					`module_id` = %s AND
					`state`     = '%s'
			LIMIT 1
			", self::$user_id, $module_id[1], $state_id );
		$return = $wpdb->get_var( $query );
		if ( ! empty( $return ) ) {
			return $return;
		}
		return null;
	}

	/**
	 * Save State Data
	 *
	 * @access public
	 * @return void
	 * @since  1.3.6
	 */
	public function save_state( $url, $state_id, $content ) {
		global $wpdb;

		if ( ! $this->is_table_exists() )
			return false;
		// Sync table columns
		$this->add_course_lesson_coulmns();

		// Get course and lesson id from request
		list( $course_id, $lesson_id ) = $this->fetch_course_lesson_ids();

		if ( $this->get_state( $url, $state_id ) !== null )
			$this->update_state( $url, $state_id, $content, $course_id, $lesson_id );
		else
			$this->insert_state( $url, $state_id, $content, $course_id, $lesson_id );
	}

	/**
	 * Update State Data
	 *
	 * @access private
	 * @return void
	 * @since  1.3.6
	 */
	private function update_state( $url, $state_id, $content, $course_id = 0 , $lesson_id = 0 ) {
		global $wpdb;
		$module_id = $this->get_slide_id_from_url( $url );
		$table_name = $wpdb->prefix . self::TABLE_RESUME;
		$query = $wpdb->prepare( "
			UPDATE {$table_name}
				SET `value` = '%s'
				WHERE
					`user_id`   = %s AND
					`module_id` = %s AND
					`course_id` = %s AND
					`lesson_id` = %s AND
					`state`     = '%s'
		", $content, self::$user_id, $module_id[1], $course_id, $lesson_id, $state_id );

		$query = $wpdb->query( $query );
		// try for old data
		if( ! $query ) {
			$query = $wpdb->prepare( "
				UPDATE {$table_name}
					SET `value` = '%s',
					`course_id` = '%s',
					`lesson_id` = '%s'
					WHERE
						`user_id`   = %s AND
						`module_id` = %s AND
						`state`     = '%s'
				", $content, $course_id, $lesson_id, self::$user_id, $module_id[1], $state_id );
			$query = $wpdb->query( $query );
		}
	}

	/**
	 * Insert State Data
	 *
	 * @access private
	 * @return void
	 * @since  1.3.6
	 */
	private function insert_state( $url, $state_id, $content, $course_id = 0 , $lesson_id = 0  ) {
		global $wpdb;
		$module_id  = $this->get_slide_id_from_url( $url );
		$table_name = $wpdb->prefix . self::TABLE_RESUME;

		$query = $wpdb->prepare( "INSERT INTO {$table_name} ( `user_id`, `module_id`, `state`, `value`, `course_id`, `lesson_id` ) VALUES ( %s, %s, '%s', '%s', '%s', '%s' ); ",
			self::$user_id,
			$module_id[1],
			$state_id,
			$content,
			$course_id,
			$lesson_id );

		$query = $wpdb->query( $query );
		if ( $wpdb->last_error ) {
			if ( ! get_option( $wpdb->prefix . self::TABLE_RESUME . '_constraints' ) ) {
				self::update_constraints( self::TABLE_RESUME );
				$query = $wpdb->prepare( "INSERT INTO {$table_name} ( `user_id`, `module_id`, `state`, `value`, `course_id`, `lesson_id` ) VALUES ( %s, %s, '%s', '%s', '%s', '%s' ); ",
					self::$user_id,
					$module_id[1],
					$state_id,
					$content,
					$course_id,
					$lesson_id );
				$query = $wpdb->query( $query );
				update_option( $wpdb->prefix . self::TABLE_RESUME . '_constraints', TRUE );
			}
		}
	}

	private function add_course_lesson_coulmns() {
		global $wpdb;
		if ( ! get_option( $wpdb->prefix . self::TABLE_RESUME . '_course_lesson_columns', false ) ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_RESUME . "` ADD `course_id` BIGINT(20) UNSIGNED NULL DEFAULT 0;" );
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_RESUME . "` ADD `lesson_id` BIGINT(20) UNSIGNED NULL DEFAULT 0;" );
			update_option( $wpdb->prefix . self::TABLE_RESUME . '_course_lesson_columns', true );
		}
	}

	private function fetch_course_lesson_ids() {
		// Get course and lesson id from request
		$auth      = null;
		$course_id = 0;
		$lesson_id = 0;
		parse_str( $_SERVER['HTTP_REFERER'], $referer );
		if ( strstr( $_SERVER['HTTP_REFERER'], '&client=' ) !== false ) {
			if ( ! empty( $referer['auth'] ) ) {
				$auth = $referer['auth'];
			}
		}

		if ( empty( $auth ) ) {
			// Try to read all headers first.
			if ( function_exists( 'getallheaders' ) ) {
				$all_headers = getallheaders();
				if ( isset( $all_headers['Authorization'] ) ) {
					$auth = $all_headers['Authorization'];
				}
			}
		}

		if ( empty( $auth ) ) {
			$contents = file_get_contents( 'php://input' );
			$decoded  = json_decode( $contents, true );
			if ( ! is_array( $decoded ) ) {
				parse_str( $contents, $decoded_2 );
			}
			if ( isset( $decoded_2['Authorization'] ) ) {
				$auth = $decoded_2['Authorization'];
			}
		}

		if ( ! empty( $auth ) ) {
			$lesson_id = substr( $auth, 11 );
		}

		if ( empty( $lesson_id ) ) {
			$lesson_id = get_user_meta( self::$user_id, 'tincan_last_known_ld_module', true );
		}

		$this->lesson_id = $lesson_id;
		if ( isset( $_GET['course_id'] ) ) {
			$course_id = $_GET['course_id'];
		}
		if ( empty( $course_id ) ) {
			$course_id = get_user_meta( self::$user_id, 'tincan_last_known_ld_course', true );
		}

		return [ $course_id, $lesson_id ];
	}
}
