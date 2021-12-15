<?php
/**
 * Database\Admin
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

class Admin extends \UCTINCAN\Database {
	// Storing Filters
	private $filters = array();

	/**
	 * Setter
	 *
	 * @access public
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function __set( $name, $value ) {
		$this->filters[ $name ] = $value;
	}

	/**
	 * Create Query String based on Search Fields
	 *
	 * @access private
	 * @return string
	 * @since  1.0.0
	 */
	private function get_query_string( $columns = 'id' ) {
		global $wpdb;

		$where = [];

		if ( ! empty( $this->filters['group'] ) ) {
			$where[] = "reporting.`group_id` = {$this->filters[ 'group' ]}";
		}

		if ( ! empty( $this->filters['actor'] ) ) {
			$where[] = "( user.`user_nicename` LIKE '%{$this->filters[ 'actor' ]}%' OR user.`user_email` LIKE '%{$this->filters[ 'actor' ]}%' OR user.`display_name` LIKE '%{$this->filters[ 'actor' ]}%' )";
		}

		if ( ! empty( $this->filters['user_id'] ) ) {
			$where[] = "reporting.`user_id` = {$this->filters[ 'user_id' ]}";
		}

		if ( ! empty( $this->filters['course'] ) ) {
			$where[] = "reporting.`course_id` = {$this->filters[ 'course' ]}";
		}

		if ( ! empty( $this->filters['lesson'] ) ) {
			$where[] = "reporting.`lesson_id` = {$this->filters[ 'lesson' ]}";
		}

		if ( ! empty( $this->filters['module'] ) ) {
			$where[] = "reporting.`module` = '{$this->filters[ 'module' ]}'";
		}

		if ( ! empty( $this->filters['verb'] ) ) {
			$where[] = "reporting.`verb` LIKE '%{$this->filters[ 'verb' ]}%'";
		}

		if ( ! empty( $this->filters['dateStart'] ) ) {
			$where[] = "reporting.`xstored` >= '{$this->filters[ 'dateStart' ]}'";
		}

		if ( ! empty( $this->filters['dateEnd'] ) ) {
			$where[] = "reporting.`xstored` <= '{$this->filters[ 'dateEnd' ]}'";
		}

		$where = ( ! empty( $where ) ) ? implode( ' AND ', $where ) : '1 = 1';
		$where .= ' AND (' . $this->get_group_leader_groups_query_string() . ')';

		$query = sprintf( "SELECT %s
			FROM %s%s reporting
			LEFT OUTER JOIN %s xgrouping ON xgrouping.ID = reporting.group_id
			LEFT OUTER JOIN %s user     ON user.ID     = reporting.user_id
			LEFT OUTER JOIN %s course   ON course.ID   = reporting.course_id
			LEFT OUTER JOIN %s lesson   ON lesson.ID   = reporting.lesson_id

			WHERE %s",
			$columns,
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$wpdb->posts,
			$wpdb->users,
			$wpdb->posts,
			$wpdb->posts,
			$where
		);

		//$query .= " GROUP BY (case when reporting.verb = 'passed' then reporting.user_id, reporting.course_id, reporting.module, reporting.verb, DATE_FORMAT(reporting.xstored,'%Y-%m-%d %H:%i')  end) ";
		$query .= " GROUP BY
		(
    	    case when reporting.verb = 'passed' then reporting.verb else reporting.verb end
		),(
		    case when reporting.verb = 'passed' then ROUND((CEILING(UNIX_TIMESTAMP(reporting.xstored) / 10) * 10)) else reporting.xstored end
		), (
		    case when reporting.verb = 'passed' then reporting.user_id else reporting.user_id end
		), (
		    case when reporting.verb = 'passed' then reporting.module else reporting.module end
		), (
		    case when reporting.verb = 'passed' then reporting.course_id else reporting.course_id end
		) ";

		$query = apply_filters( 'tc_tincan_report_query', $query, $this->filters, self::TABLE_REPORTING, $columns, $where );
		
		return $query;
	}

	/**
	 * Return TinCan Data
	 *
	 * @access public
	 *
	 * @param int    $per_page optional default 0
	 * @param string $mode     optional default ''
	 *
	 * @return bool|array
	 * @since  1.0.0
	 */
	public function get_data( $per_page = 0, $mode = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		if ( $mode == 'csv' ) {
			$query = $this->get_query_string( '
				xgrouping.post_title as "Group Name",
				user.display_name as "User Name",
				course.post_title as "Course Name",
				reporting.module as "Module URL",
				reporting.module_name as "Module Name",
				reporting.target_name as "Target Name",
				reporting.verb as "Action",
				case
					when ( reporting.minimum = 100 ) then CONCAT( reporting.result, " / 100")
					else reporting.result
				end as "Result",
				case
					when ( reporting.completion = 1 ) then "Yes"
					when ( reporting.completion = 0 ) then "No"
					else ""
				end as "Completion",
				reporting.xstored as "Timestamp"'
			);
		} else {
			$query = $this->get_query_string( 'reporting.*, xgrouping.post_title as group_name, course.post_title as course_name, lesson.post_title as lesson_name, user.display_name as user_name' );
		}

		$orderby = '';

		if ( ! empty( $this->filters['orderby'] ) ) {
			$orderby = " ORDER BY reporting.`{$this->filters[ 'orderby' ]}` ";

			if ( ! empty( $this->filters['order'] ) ) {
				$orderby .= " {$this->filters[ 'order' ]} ";
			}
		}

		if ( $per_page !== 0 ) {
			if ( ! $this->filters['paged'] ) {
				$this->filters['paged'] = 1;
			}

			$limit   = ( $this->filters['paged'] - 1 ) * $per_page;
			$orderby .= "LIMIT {$limit}, {$per_page}";
		}

		$query = $wpdb->get_results( $query . $orderby, 'ARRAY_A' );

		return $query;
	}

	/**
	 * Create XAPI Query String based on Search Fields
	 *
	 * @access private
	 * @return string
	 * @since  3.2.0
	 */
	private function get_xapi_query_string( $columns = 'id' ) {
		global $wpdb;

		$where = [];

		if ( ! empty( $this->filters['group'] ) ) {
			$where[] = "reporting.`group_id` = {$this->filters[ 'group' ]}";
		}

		if ( ! empty( $this->filters['actor'] ) ) {
			$where[] = "( user.`user_nicename` LIKE '%{$this->filters[ 'actor' ]}%' OR user.`user_email` LIKE '%{$this->filters[ 'actor' ]}%' OR user.`display_name` LIKE '%{$this->filters[ 'actor' ]}%' )";
		}

		if ( ! empty( $this->filters['user_id'] ) ) {
			$where[] = "reporting.`user_id` = {$this->filters[ 'user_id' ]}";
		}

		if ( ! empty( $this->filters['course'] ) ) {
			$where[] = "reporting.`course_id` = {$this->filters[ 'course' ]}";
		}

		if ( ! empty( $this->filters['lesson'] ) ) {
			$where[] = "reporting.`lesson_id` = {$this->filters[ 'lesson' ]}";
		}

		if ( ! empty( $this->filters['module'] ) ) {
			$where[] = "reporting.`module` = '{$this->filters[ 'module' ]}'";
		}

		if ( ! empty( $this->filters['question'] ) ) {
			$where[] = " REPLACE( REPLACE( reporting.`activity_name`,'\t', ''), '\n', '') LIKE '%{$this->filters[ 'question' ]}%'";
		}

		if ( ! empty( $this->filters['results'] ) ) {
			if ( $this->filters['results'] === '1' ) {
				$where[] = "reporting.`result` > '0' ";
			} elseif ( $this->filters['results'] === '-1' ) {
				$where[] = " ( reporting.`result` = 0 OR reporting.`result` IS NULL ) ";
			}

		}

		if ( ! empty( $this->filters['dateStart'] ) ) {
			$where[] = "reporting.`xstored` >= '{$this->filters[ 'dateStart' ]}'";
		}

		if ( ! empty( $this->filters['dateEnd'] ) ) {
			$where[] = "reporting.`xstored` <= '{$this->filters[ 'dateEnd' ]}'";
		}

		$where = ( ! empty( $where ) ) ? implode( ' AND ', $where ) : '1 = 1';
		$where .= ' AND (' . $this->get_group_leader_groups_query_string() . ')';

		$query = sprintf( "SELECT %s
			FROM %s%s reporting
			LEFT OUTER JOIN %s xgrouping ON xgrouping.ID = reporting.group_id
			LEFT OUTER JOIN %s user     ON user.ID     = reporting.user_id
			LEFT OUTER JOIN %s course   ON course.ID   = reporting.course_id
			LEFT OUTER JOIN %s lesson   ON lesson.ID   = reporting.lesson_id

			WHERE %s",
			$columns,
			$wpdb->prefix,
			self::TABLE_QUIZ,
			$wpdb->posts,
			$wpdb->users,
			$wpdb->posts,
			$wpdb->posts,
			$where
		);

		$query = apply_filters( 'tc_xapi_report_query', $query, $this->filters, self::TABLE_QUIZ, $columns, $where );

		return $query;
	}

	/**
	 * Return XAPI QUIZ Data
	 *
	 * @access public
	 *
	 * @param int    $per_page optional default 0
	 * @param string $mode     optional default ''
	 *
	 * @return bool|array
	 * @since  3.2.0
	 */
	public function get_xapi_data( $per_page = 0, $mode = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		if ( $mode == 'csv-xapi' ) {
			$query = $this->get_xapi_query_string( '
				xgrouping.post_title as "Group Name",
				user.display_name as "User Name",
				course.post_title as "Course Name",
				reporting.module as "Module URL",
				reporting.module_name as "Module Name",
				reporting.activity_name as "Question",
				reporting.available_responses as "Choices",
				reporting.correct_response as "Correct answer",
				reporting.user_response as "User\'s answer",
				case
					when ( reporting.result > 0 ) then "Correct"
					else "Incorrect"
				end as "Result",
				reporting.xstored as "Timestamp",
				reporting.raw_score as "Score" '
			);
		} else {
			$query = $this->get_xapi_query_string( 'reporting.*, xgrouping.post_title as group_name, course.post_title as course_name, lesson.post_title as lesson_name, user.display_name as user_name' );
		}

		$orderby = '';

		if ( ! empty( $this->filters['orderby'] ) ) {
			$orderby = " ORDER BY reporting.`{$this->filters[ 'orderby' ]}` ";

			if ( ! empty( $this->filters['order'] ) ) {
				$orderby .= " {$this->filters[ 'order' ]} ";
			}
		}

		if ( $per_page !== 0 ) {
			if ( ! $this->filters['paged'] ) {
				$this->filters['paged'] = 1;
			}

			$limit   = ( $this->filters['paged'] - 1 ) * $per_page;
			$orderby .= "LIMIT {$limit}, {$per_page}";
		}

		$query = $wpdb->get_results( $query . $orderby, 'ARRAY_A' );

		return $query;
	}
	/**
	 * Return TinCan Data Row Count
	 *
	 * @access public
	 * @return int
	 * @since  1.0.0
	 */
	public function get_count() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		$query = $wpdb->get_results( $this->get_query_string( 'COUNT(reporting.id), reporting.verb' ) );

		return $wpdb->num_rows;
	}

	/**
	 * Return TinCan Data Row Count
	 *
	 * @access public
	 * @return int
	 * @since  1.0.0
	 */
	public function get_count_xapi() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		$num_query = $wpdb->get_var( $this->get_xapi_query_string( 'COUNT(reporting.id)' ) );

		return $num_query;
	}

	/**
	 * Return List of Group
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_groups( $table_type = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		$table_name = self::TABLE_REPORTING;
		if ( $table_type === 'quiz' ) {
			$table_name = self::TABLE_QUIZ;
		}
		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.group_id, xgrouping.post_title as group_name
				FROM %s%s reporting
					INNER JOIN %s xgrouping ON xgrouping.ID = reporting.group_id
				WHERE %s
				GROUP BY reporting.group_id",
			$wpdb->prefix,
			$table_name,
			$wpdb->posts,
			$where

		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Course
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_courses( $table_type = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		$table_name = self::TABLE_REPORTING;
		if ( $table_type === 'quiz' ) {
			$table_name = self::TABLE_QUIZ;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.course_id, course.post_title as course_name
				FROM %s%s reporting
					INNER JOIN %s course ON course.ID = reporting.course_id
				WHERE %s
				GROUP BY reporting.course_id",
			$wpdb->prefix,
			$table_name,
			$wpdb->posts,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Lesson
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_lessons( $table_type = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		$table_name = self::TABLE_REPORTING;
		if ( $table_type === 'quiz' ) {
			$table_name = self::TABLE_QUIZ;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.lesson_id, lesson.post_title as lesson_name
				FROM %s%s reporting
					INNER JOIN %s lesson ON lesson.ID = reporting.lesson_id
				WHERE %s
				GROUP BY reporting.lesson_id",
			$wpdb->prefix,
			$table_name,
			$wpdb->posts,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Module
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_modules( $table_type = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		$table_name = self::TABLE_REPORTING;
		if ( $table_type === 'quiz' ) {
			$table_name = self::TABLE_QUIZ;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.module, reporting.module_name
				FROM %s%s reporting
				WHERE %s
				GROUP BY reporting.module",
			$wpdb->prefix,
			$table_name,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Verb
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_actions() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.verb FROM %s%s reporting
				WHERE %s
				GROUP BY reporting.verb",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Verb
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_questions( $q = '') {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();

		if ( ! empty( $q ) ) {
			$where = ( ! empty( $where ) ) ? "(" . $where . ") AND ( reporting.activity_name LIKE '%" . $q . "%' ) " : $where;
		}

		$query = sprintf( "
			SELECT reporting.activity_name FROM %s%s reporting
				WHERE %s
				GROUP BY reporting.activity_name",
			$wpdb->prefix,
			self::TABLE_QUIZ,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}
	/**
	 * Return List of Date
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function get_dates() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT DATE(reporting.xstored) as date
				FROM %s%s reporting
				WHERE %s
				GROUP BY DATE(reporting.xstored)",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$where
		);

		return $wpdb->get_results( $query, 'ARRAY_A' );
	}

	/**
	 * Return List of Module for Course
	 *
	 * @access private
	 *
	 * @param  int $course_id
	 *
	 * @return WP query result
	 * @since  1.0.0
	 */
	private function get_modules_by_course( $course_id, $table_type = '' ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		if ( ! $course_id ) {
			return array();
		}

		$table_name = self::TABLE_REPORTING;
		if ( $table_type === 'quiz' ) {
			$table_name = self::TABLE_QUIZ;
		}

		global $wpdb;

		$where = $this->get_group_leader_groups_query_string();
		$query = sprintf( "
			SELECT reporting.module, reporting.module_name
				FROM %s%s reporting
				WHERE reporting.course_id = %s AND (%s)
				GROUP BY reporting.module",
			$wpdb->prefix,
			$table_name,
			$course_id,
			$where
		);

		return $wpdb->get_results( $query );
	}

	/**
	 * Print Module <option>s from Filter
	 *
	 * @access private
	 *
	 * @param  int $course_id
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function print_modules_form_from_URL_parameter( $type = '') {

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return false;
		}

		$selected_course = ( ! empty( $_GET['tc_filter_course'] ) ) ? $_GET['tc_filter_course'] : ( ! empty( $_POST['tc_filter_course'] ) ? $_POST['tc_filter_course'] : false );
		$selected_module = ( ! empty( $_GET['tc_filter_module'] ) ) ? $_GET['tc_filter_module'] : ( ! empty( $_POST['tc_filter_module'] ) ? $_POST['tc_filter_module'] : false );
		$type            = ( ! empty( $_GET['type'] ) ) ? $_GET['type'] : ( ! empty( $_POST['type'] ) ? $_POST['type'] : $type );
		$modules         = $this->get_modules_by_course( $selected_course, $type );

		foreach ( $modules as $module ) {
			printf( '<option value="%s" %s>%s</option>', $module->module, ( $selected_module == $module->module ) ? 'selected="selected"' : '', $module->module_name );
		}
	}

	/**
	 * Delete User's Data
	 *
	 * @access public
	 *
	 * @param  int $user_id
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function delete_by_user( $user_id ) {
		global $wpdb;

		$query = sprintf( "
			DELETE FROM %s%s
				WHERE `user_id` = %s;
			",
			$wpdb->prefix,
			self::TABLE_REPORTING,
			$user_id
		);

		$wpdb->query( $query );

		$query = sprintf( "
			DELETE FROM %s%s
				WHERE `user_id` = %s;
			",
			$wpdb->prefix,
			self::TABLE_QUIZ,
			$user_id
		);

		$wpdb->query( $query );


		$query = sprintf( "
			DELETE FROM %s%s
				WHERE `user_id` = %s;
			",
			$wpdb->prefix,
			self::TABLE_RESUME,
			$user_id
		);

		$wpdb->query( $query );


	}

	/**
	 * Reset Data
	 *
	 * @access public
	 * @return bool
	 * @since  1.0.0
	 */
	public function reset() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		global $wpdb;
		$query = sprintf( "TRUNCATE TABLE %s%s;",
			$wpdb->prefix,
			self::TABLE_REPORTING
		);
		$wpdb->query( $query );

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Reset Data
	 *
	 * @access public
	 * @return bool
	 * @since  1.0.0
	 */
	public function reset_quiz() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		global $wpdb;
		$query = sprintf( "TRUNCATE TABLE %s%s;",
			$wpdb->prefix,
			self::TABLE_QUIZ
		);
		$wpdb->query( $query );

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Reset Data
	 *
	 * @access public
	 *
	 * @param $user_id
	 * @peram $type
	 *
	 * @return
	 * @since  1.0.0
	 */
	public function reset_user( $user_id = 0, $type = [] ) {

		if ( ! $this->is_table_exists() || 0 === $user_id ) {
			return;
		}

		if ( in_array( 'reporting', $type ) ) {
			global $wpdb;
			$query = sprintf( 'DELETE FROM %s%s WHERE user_id = %d',
				$wpdb->prefix,
				self::TABLE_REPORTING,
				$user_id
			);

			$wpdb->query( $query );

			// reseting quiz data too...
			$query = sprintf( 'DELETE FROM %s%s WHERE user_id = %d',
				$wpdb->prefix,
				self::TABLE_QUIZ,
				$user_id
			);

			$wpdb->query( $query );
		}


		if ( in_array( 'resume', $type ) ) {
			global $wpdb;
			$query = sprintf( 'DELETE FROM %s%s WHERE user_id = %d',
				$wpdb->prefix,
				self::TABLE_RESUME,
				$user_id
			);

			$wpdb->query( $query );
		}


		return;
	}

	/**
	 * Reset Data
	 *
	 * @access public
	 * @return bool
	 * @since  1.0.0
	 */
	public function reset_bookmark_data() {
		if ( ! $this->is_table_exists() ) {
			return false;
		}

		global $wpdb;
		$query = sprintf( "TRUNCATE TABLE %s%s;",
			$wpdb->prefix,
			self::TABLE_RESUME
		);
		$wpdb->query( $query );

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Check the Current User is a Group Leader and Return Assigned Groups Query String
	 *
	 * @access private
	 * @return bool
	 * @since  1.0.0
	 */
	private function get_group_leader_groups_query_string() {
		if ( ! learndash_is_group_leader_user( get_current_user_id() ) ) {
			return ' 1=1 ';
		}

		$groups = learndash_get_administrators_group_ids( get_current_user_id() );
		$where  = array();

		if( empty( $groups ) ) {
			return ' 1=1 ';
		}

		foreach ( $groups as $group ) {
			$where[] = "reporting.group_id = {$group}";
		}

		return implode( ' OR ', $where );
	}

}
