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

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Completion extends \UCTINCAN\Database {
	use \UCTINCAN\Modules;

	/**
	 * Return H5P Completion by ID and course/ lesson ID
	 *
	 * @access public
	 * @param  int $user_id
	 * @param  int $snc_id
	 * @param  int $course_id
	 * @param  int $lesson_id
	 * @return int
	 * @since  1.0.0
	 */
	public function get_H5P_completion( $snc_id, $course_id, $lesson_id, $user_id = false ) {
		if ( ! $this->is_table_exists() )
			return false;
		
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		self::$user_id = $user_id;
		
		$by_lesson_meta = $this->get_completion_by_lesson_meta( $lesson_id );

		if ( $by_lesson_meta != -1 )
			return $by_lesson_meta;

		global $wpdb;

		if ( $course_id ) {
			$course_where = "= '{$course_id}'";
		} else {
			$course_where = "IS NULL";
		}

		$query = sprintf( "
			SELECT `id` FROM %s%s
				WHERE `user_id` = %s AND
					`course_id` %s AND
					`module` LIKE '%%h5p_embed&id=%s'
				LIMIT 1",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			( $user_id ) ? $user_id : self::$user_id,
			$course_where,
			$snc_id
		);

		return $wpdb->get_var( $query );
	}

	/**
	 * Return Completion by URL
	 *
	 * @access public
	 * @param int $URL
	 * @param int $user_id
	 * @param int $course_id
	 * @param int $lesson_id
	 * @return string
	 * @since  1.0.0
	 * @todo return type
	 */
	public function get_completion_by_URL( $URL, $course_id, $lesson_id ) {
		if ( ! $this->is_table_exists() )
			return false;

		$by_lesson_meta = $this->get_completion_by_lesson_meta( $lesson_id, $URL );

		if ( $by_lesson_meta != -1 )
			return $by_lesson_meta;

		return $this->get_snc_completion_by_default( $URL, $course_id, $lesson_id );
	}

	private function get_snc_completion_by_default( $URL, $course_id, $lesson_id ) {
		if ( ! $this->is_table_exists() )
			return false;

		global $wpdb;

		if ( $course_id ) {
			$course_where = "`course_id` = '{$course_id}' AND";
		} else {
			$course_where = "`course_id` IS NULL AND";
		}

		// Get ID
		$matches = $this->get_slide_id_from_url( $URL );

		if ( ! $matches[0] )
			return false;

		if ( $this->is_slide_published_to_web( $matches[1] ) )
			return true;

		$query = sprintf( "
			SELECT `id` FROM %s%s
				WHERE
					`user_id` = %s AND
					`module` LIKE '%%%s%%' AND
					%s
					( `verb` = 'answered' OR `verb` = 'completed' OR `verb` = 'passed' OR `verb` = 'failed' )
			LIMIT 1
			",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			self::$user_id,
			$matches[0],
			$course_where
		);

		return $wpdb->get_var( $query );
	}

	/**
	 * Return Completion by lesson meta
	 *
	 * @access public
	 * @param  int $lesson_id
	 * @param  int $user_id
	 * @param  int $lesson_id
	 * @return int (-1: no-meta, 0: not-completed, 1: completed)
	 * @since  1.0.0
	 */
	public function get_completion_by_lesson_meta( $lesson_id, $URL = false ) {
		// Get ID
		if ( $URL ) {
			$matches = $this->get_slide_id_from_url( $URL );

			if ( $this->is_slide_published_to_web( $matches[1] ) )
				return true;
		}

		$post_meta = ( $lesson_id ) ? get_post_meta( $lesson_id, '_WE-meta_', true ) : array();

		if ( empty( $post_meta[ 'completion-condition' ] ) )
			return -1;

		$verbs = $post_meta[ 'completion-condition' ];

		$complete = 0;
		// Check && conditions first
		if ( strpos( $verbs, '&&' ) !== FALSE ) {
			$verbs = explode( '&&', $verbs );
			$verbs = array_map( 'trim', $verbs );
			foreach( $verbs as $verb ) {
				if ( strstr( $verb, 'result' ) !== false ) {
					$complete = $this->get_completion_from_result_condition( $complete, $verb, $lesson_id );
				} else {
					$complete = $this->lesson_has_verb( $lesson_id, $verb );
				}
				if( $complete == 0 ){
					break;
				}
			}
			return $complete;
		}
		
		$verbs = explode( ',', $verbs );
		$verbs = array_map( 'trim', $verbs );

		foreach( $verbs as $verb ) {
			if ( strstr( $verb, 'result' ) !== false )
				$complete = $this->get_completion_from_result_condition( $complete, $verb, $lesson_id );

			else if ( $this->lesson_has_verb( $lesson_id, $verb ) )
				$complete = 1;
		}

		return $complete;
	}

	private function is_slide_published_to_web( $module_id ) {
		$module_info = \TINCANNYSNC\Database::get_item( $module_id );

		if ( $module_info[ 'subtype' ] == 'web' )
			return true;

		return false;
	}

	/**
	 * Return Completion from Result Condition
	 *
	 * @access private
	 * @param  bool   $complete
	 * @param  string $verb
	 * @param  int    $lesson_id
	 * @return int    (0: not-completed, 1: completed)
	 * @since  1.0.0
	 */
	private function get_completion_from_result_condition( $complete, $verb, $lesson_id ) {
		preg_match_all( "/result\s?([>=<]+)\s?([0-9]+)/", $verb, $matches ); // result=123

		if ( !$matches[1][0] )
			return $complete;

		$condition = $matches[1][0] . ' ' . $matches[2][0];

		if ( $this->lesson_has_result( $lesson_id, $condition ) )
			$complete = 1;

		return $complete;
	}

	public function lesson_has_verb( $lesson_id, $verb ) {
		if ( ! $this->is_table_exists() )
			return false;

		global $wpdb;

		$verb = strtolower( $verb );

		$query = sprintf( "
			SELECT `id`
				FROM %s%s
				WHERE
					`lesson_id` = %s AND
					`user_id`   = %s AND
					`verb`      = '%s'",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$lesson_id,
			self::$user_id,
			$verb
		);

		return $wpdb->get_var( $query );
	}

	public function lesson_has_result( $lesson_id, $condition ) {
		if ( ! $this->is_table_exists() )
			return false;

		global $wpdb;

		$query = sprintf( "
			SELECT `id`
				FROM %s%s
				WHERE
					`lesson_id` = %s AND
					`user_id`   = %s AND
					`minimum`   = 100 AND
					`result`    %s",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$lesson_id,
			self::$user_id,
			$condition
		);

		return $wpdb->get_var( $query );
	}
}
