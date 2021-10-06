<?php

namespace uncanny_learndash_reporting;

/**
 * Class ReportingApi
 * @package uncanny_learndash_reporting
 */
class ReportingApi extends Config {

	/**
	 * @var null
	 */
	private static $user_role = null;

	/**
	 * @var null
	 */
	private static $group_leaders_group_ids = null;

	/**
	 * @var int
	 */
	private static $isolated_group_id = 0;

	/**
	 * @var null
	 */
	private static $course_list = null;

	/**
	 * @var null
	 */
	private static $courses_user_access = null;

	/**
	 * @var string
	 */
	private static $rest_path = 'uncanny_reporting/v1';

	/**
	 * @var string
	 */
	private static $temp_table = 'tbl_reporting_api_user_id';

	/**
	 * Class constructor
	 */
	public function __construct() {
		//register api class
		add_action( 'rest_api_init', array( __CLASS__, 'reporting_api' ) );
	}

	/**
	 * @param $key
	 * @param $data
	 */
	public static function create_cache( $key, $data ) {
		wp_cache_set( $key, $data, 'tincanny', 600 );
	}

	/**
	 * @param $key
	 *
	 * @return array|false|mixed
	 */
	public static function get_cache( $key ) {
		if ( true === apply_filters( 'uo_tincanny_reporting_disable_cache', false ) ) {
			return array();
		}

		return wp_cache_get( $key, 'tincanny' );
	}

	/**
	 *
	 */
	public static function reporting_api() {

		if ( isset( $_GET['group_id'] ) ) {
			self::$isolated_group_id = absint( $_GET['group_id'] );
		}

		//dashboard_data
		register_rest_route( self::$rest_path, '/dashboard_data/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_dashboard_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Call get all courses and general user data
		register_rest_route( self::$rest_path, '/courses_overview/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_courses_overview' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
		) );

		// Call get all courses and general user data
		register_rest_route( self::$rest_path, '/table_data/', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'get_table_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
		) );

		register_rest_route( self::$rest_path, '/user_avatar/', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'get_user_avatar' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
		) );

		//
		register_rest_route( self::$rest_path, '/users_completed_courses/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_users_completed_courses' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/course_modules/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_course_modules' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/assignment_data/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_assignment_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/tincan_data/(?P<user_ID>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_tincan_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Update it the user see the Tin Can tables
		register_rest_route( self::$rest_path, '/show_tincan/(?P<show_tincan>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'show_tincan_tables' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Update it the user see the Tin Can tables
		register_rest_route( self::$rest_path, '/disable_mark_complete/(?P<disable_mark_complete>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'disable_mark_complete' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Update it the user see the Tin Can tables
		register_rest_route( self::$rest_path, '/nonce_protection/(?P<nonce_protection>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'nonce_protection' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Reset Tin Can Data
		register_rest_route( self::$rest_path, '/reset_tincan_data/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'reset_tincan_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		// Reset Quiz Data
		register_rest_route( self::$rest_path, '/reset_quiz_data/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'reset_quiz_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/reset_bookmark_data/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'reset_bookmark_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/purge_experienced/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'purge_experienced' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );

		register_rest_route( self::$rest_path, '/purge_answered/', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'purge_answered' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),

		) );
	}

	/**
	 * This is our callback function that allows access to tincanny data
	 *
	 * @return bool|\WP_Error
	 */
	public static function tincanny_permissions() {
		$capability = apply_filters( 'tincanny_can_get_data', 'manage_options' );

		// Restrict endpoint to only users who have the manage_options capability.
		if ( current_user_can( $capability ) ) {
			return true;
		}

		if ( current_user_can( 'group_leader' ) ) {
			return true;

		}

		return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have the capability to view tincanny data.', 'uncanny-learndash-reporting' ) );
	}

	/**
	 * Get data for the admin dashboard page
	 *
	 * @return array
	 */
	public static function get_dashboard_data() {
		return self::get_courses_overview_data( 'dashboard-only' );
	}

	/**
	 * Collect general user course data and LearnDash Labels
	 *
	 * @return array
	 */
	public static function get_courses_overview() {
		$cache = self::get_cache( 'get_courses_overview' );
		if ( ! empty( $cache ) ) {
			return apply_filters( 'tc_api_get_courses_overview', $cache );
		}

		$json_return                    = array();
		$json_return['learnDashLabels'] = self::get_labels();
		$json_return['links']           = self::get_links();
		$json_return['get']             = self::$isolated_group_id;
		$json_return['message']         = '';
		$json_return['success']         = true;
		$json_return['data']            = self::course_progress_data();

		self::create_cache( 'get_courses_overview', $json_return );

		return apply_filters( 'tc_api_get_courses_overview', $json_return );
	}


	/**
	 * Collect general course data
	 *
	 * @return array|bool
	 */
	private static function course_progress_data() {
		$cache = self::get_cache( 'course_progress_data' );
		if ( ! empty( $cache ) ) {
			return $cache;
		}

		$data = array(
			'userList'   => self::get_courses_overview_data(),
			'courseList' => self::get_course_list(),
			'success'    => true,
		);

		self::create_cache( 'course_progress_data', $data );

		return $data;
	}

	/**
	 * @param string $type
	 *
	 * @return mixed|void
	 */
	public static function get_courses_overview_data( $type = 'both' ) {

		$cache = self::get_cache( 'uo_get_courses_overview_data' );
		if ( ! empty( $cache ) ) {
			return apply_filters( 'uo_get_courses_overview_data', $cache, $type );
		}
		// Get all list of course
		$course_list = self::get_course_list();

		// Get all users from groups
		$groups_list           = self::get_groups_list();
		$course_access_list    = array();
		$course_access_count   = array();
		$all_user_ids          = array();
		$dashboard_data_object = array();

		global $wpdb;
		$access = array();

		if ( $groups_list ) {
			// Get data from group data
			foreach ( $groups_list as $group_id => $group ) {

				if ( isset( $group['groups_user'] ) && isset( $group['groups_course_access'] ) ) {

					foreach ( $group['groups_user'] as $user_id ) {
						if ( 0 !== self::$isolated_group_id ) {
							if ( $group_id == self::$isolated_group_id ) {
								$all_user_ids[ (int) $user_id ] = (int) $user_id;
							}
						} else {
							$all_user_ids[ (int) $user_id ] = (int) $user_id;
						}

					}

					// Get user course access from groups
					foreach ( $group['groups_course_access'] as $course_id ) {
						if ( ! isset( $course_list[ $course_id ] ) ) {
							continue;
						}
						foreach ( $all_user_ids as $user_id ) {
							if ( in_array( $user_id, $group['groups_user'] ) ) {
								$course_access_list[ $course_id ][ (int) $user_id ] = (int) $user_id;
								$access[ $course_id ][ $user_id ]                   = $group_id;
							}
						}
					}
				}
			}
		} else {
			// When there is no any group on site then use all users
			$all_user_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
			$all_user_ids = array_map( 'intval', $all_user_ids );
		}

		if ( 'administrator' === self::get_user_role() && 0 === self::$isolated_group_id ) {
			if ( is_multisite() ) {
				// Get all users
				$site_users = get_users();
				// Create an array with the ID of all the users
				$all_user_ids = array_map( function ( $user ) {
					// Return only the ID
					return $user->ID;
				}, $site_users );
			} else {
				// Get all users
				$all_user_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
				$all_user_ids = array_map( 'intval', $all_user_ids );
			}
		}

		self::create_temp_table();
		self::insert_user_ids_in_temp_table( $all_user_ids );

		// Check all users ID exists
		$join_query_user = " JOIN $wpdb->prefix" . self::$temp_table . " t ON t.user_id = u.ID ";
		if ( ! empty( $all_user_ids ) ) {
			$all_user_ids = $wpdb->get_col( "SELECT u.ID FROM $wpdb->users u $join_query_user GROUP BY u.ID" );
			$all_user_ids = array_map( 'intval', $all_user_ids );
		} else {
			$all_user_ids = array();
		}

		unset( $groups_list );
		$user_data_query = "SELECT u.ID, u.display_name, u.user_email, u.user_login, m1.meta_value AS first_name, m2.meta_value AS last_name
FROM $wpdb->users u
    $join_query_user
LEFT JOIN $wpdb->usermeta m1
ON m1.user_id = t.user_id AND m1.meta_key = 'first_name'
LEFT JOIN $wpdb->usermeta m2
ON m2.user_id = t.user_id AND m2.meta_key = 'last_name'
GROUP BY u.ID";

		// course access list
		if ( 'administrator' === self::get_user_role() && 0 === self::$isolated_group_id ) {
			foreach ( $course_list as $course_id => $course ) {

				if ( ! isset( $course_access_list[ $course_id ] ) ) {
					$course_access_list[ $course_id ] = array();
				}

				if ( ! empty( $course->course_user_access_list ) ) {
					foreach ( $course->course_user_access_list as $user_id ) {

						$course_access_list[ $course_id ][ (int) $user_id ] = (int) $user_id;

						if ( ! isset( $access[ $course_id ][ $user_id ] ) ) {
							$access[ $course_id ][ $user_id ] = 0;
						}
					}
				}
			}

			if ( is_multisite() ) {
				// Get all users
				$site_users = get_users();
				// Create an array with the ID of all the users
				$all_user_ids = array_map( function ( $user ) {
					// Return only the ID
					return $user->ID;
				}, $site_users );
				// Create user data
				$user_data = array_map( function ( $user ) {
					// Return object with ID, display name and user email
					return (object) [
						'ID'           => $user->ID,
						'display_name' => $user->display_name,
						'user_email'   => $user->user_email,
						'user_login'   => $user->user_login,
					];
				}, $site_users );
			} else {
				// Get all users
				//$all_user_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
				//$all_user_ids = array_map( 'intval', $all_user_ids );

				//$user_data_q = $wpdb->get_results( "SELECT u.ID, u.display_name, u.user_email, u.user_login FROM $wpdb->users u $join_query_user " );
				$user_data = $wpdb->get_results( $user_data_query );
//				$user_data   = array();
//				foreach ( $user_data_q as &$user ) {
//					if ( ! empty( $user->user_email ) ) {
//						$user_data[] = (object) [
//							'ID'           => (int) $user->ID,
//							'display_name' => $user->display_name,
//							'user_email'   => $user->user_email,
//							'user_login'   => $user->user_login,
//						];
//					}
//				}
			}
//			unset( $user_data_q );

		} else {
			if ( ! empty( $all_user_ids ) ) {
				//$user_ids    = implode( ',', $all_user_ids );
//				$q_user_data = "SELECT u.ID, u.display_name, u.user_email, u.user_login, m1.meta_value AS first_name, m2.meta_value AS last_name
//FROM $wpdb->users u
//    $join_query_user
//LEFT JOIN $wpdb->usermeta m1
//ON m1.user_id = t.user_id AND m1.meta_key = 'first_name'
//LEFT JOIN $wpdb->usermeta m2
//ON m2.user_id = t.user_id AND m2.meta_key = 'last_name'
//GROUP BY u.ID ";
				$user_data = $wpdb->get_results( $user_data_query );
			} else {
				$user_data = array();
			}

		}

		$all_user_ids_rearranged = array();
		foreach ( $all_user_ids as $user_id ) {
			$all_user_ids_rearranged[ (int) $user_id ] = (int) $user_id;
		}

		unset( $all_user_ids );

		global $wpdb;

		$table_name = $wpdb->prefix . 'tc_course_access';

		if ( 'no' === get_option( 'course_access_table_created', 'no' ) ) {

			$wpdb->query( 'CREATE TABLE IF NOT EXISTS `' . $table_name . '` (
				`ID`        bigint(20) NOT NULL AUTO_INCREMENT,
				`course_id` bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				`user_id`   bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				`group_id`  bigint(20) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`ID`),
				KEY `course_id`(`course_id`),
				KEY `user_id`(`user_id`),
				KEY `group_id`(`group_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;' );

			update_option( 'course_access_table_created', 'yes', true );
		}

		$wpdb->query( "Truncate table {$table_name}" );

		$queries = array();
		$count   = 0;
		$index   = 0;
		foreach ( $access as $course_id => $data ) {
			foreach ( $data as $user_id => $group_id ) {
				$count ++;
				if ( ! isset( $queries[ $index ] ) ) {
					$queries[ $index ] = '';
				}
				$queries[ $index ] .= '(null,' . $course_id . ',' . $user_id . ',' . $group_id . '),';
				if ( 50000 === $count ) {
					$index ++;
					$count = 0;
				}
			}
		}

		unset( $access );

		foreach ( $queries as $query ) {
			$query = substr( $query, 0, - 1 );
			$q     = "INSERT INTO {$table_name}(ID,course_id,user_id,group_id) values $query;";
		}

		unset( $queries );
		unset( $q );

		$all_user_data_rearranged = array();
		foreach ( $user_data as $user ) {
			//$user->first_name                            = get_user_meta( $user->ID, 'first_name', true );//( isset( $first_last_rearranged[ $user->ID ] ) ? $first_last_rearranged[ $user->ID ]->first_name : '' );
			//$user->last_name                             = get_user_meta( $user->ID, 'last_name', true );//( isset( $first_last_rearranged[ $user->ID ] ) ? $first_last_rearranged[ $user->ID ]->last_name : '' );
			$user->enrolled                              = 0;
			$user->in_progress                           = 0;
			$user->completed                             = 0;
			$all_user_data_rearranged[ (int) $user->ID ] = $user;
		}

		unset( $user_data );

		$dashboard_data_object['total_users'] = count( $all_user_ids_rearranged );

		$course_users = array();
		foreach ( $course_access_list as $course_id => $users ) {
			$course_access_count[ $course_id ] = count( $users );
			$course_price_type                 = $course_list[ (int) $course_id ]->course_price_type;

			if ( 'open' === $course_price_type ) {
				foreach ( $all_user_data_rearranged as $user_id => $data ) {
					if ( isset( $all_user_data_rearranged[ (int) $user_id ] ) ) {
						$all_user_data_rearranged[ (int) $user_id ]->enrolled ++;
					}
				}
			}

			$course_users_temp = array();
			foreach ( $users as $user_id => $user_id_ ) {
				if ( isset( $all_user_ids_rearranged[ $user_id ] ) ) {
					$course_users_temp[ $user_id ] = $user_id;
					if ( 'open' !== $course_price_type ) {
						if ( isset( $all_user_data_rearranged[ (int) $user_id ] ) ) {
							$all_user_data_rearranged[ (int) $user_id ]->enrolled ++;
						}
					}
				}
			}
			$course_users[ $course_id ] = $course_users_temp;

		}


		global $wpdb;

		// Completion
		$q_completions = "SELECT a.post_id as course_id, a.user_id, a.activity_completed
							FROM {$wpdb->prefix}learndash_user_activity a
							JOIN $wpdb->prefix" . self::$temp_table . " t
							ON t.user_id = a.user_id
							WHERE a.activity_type = 'course'
							AND a.activity_completed IS NOT NULL
							AND a.activity_completed <> 0";

		$completions                                     = $wpdb->get_results( $q_completions );
		$completions_rearranged                          = array();
		$dashboard_data_object['top_course_completions'] = array();
		$completions_by_date                             = array();
		$completions_by_course                           = array();


		foreach ( $completions as $completion ) {

			if ( ! isset( $course_access_list[ $completion->course_id ] ) ) {
				continue;
			}

			$course_price_type = $course_list[ $completion->course_id ]->course_price_type;

			if ( ! isset( $all_user_ids_rearranged[ (int) $completion->user_id ] ) ) {
				continue;
			}

			if ( isset( $course_access_list[ $completion->course_id ][ $completion->user_id ] ) || 'open' === $course_price_type ) {

				$completed_on_date = date( "Y-m-d", $completion->activity_completed );

				if ( ! isset( $completions_by_course[ $completion->course_id ] ) ) {
					$completions_by_course[ $completion->course_id ] = array();
				}

				$all_user_data_rearranged[ (int) $completion->user_id ]->completed ++;
				$all_user_data_rearranged[ (int) $completion->user_id ]->completed_on[ $completion->course_id ] = [
					"display"   => learndash_adjust_date_time_display( $completion->activity_completed ),
					"timestamp" => (string) $completion->activity_completed,
				];

				if ( ! isset( $completions_by_course[ $completion->course_id ][ $completed_on_date ] ) ) {
					$completions_by_course[ $completion->course_id ][ $completed_on_date ] = [ $completion->user_id ];
				} else {
					$completions_by_course[ $completion->course_id ][ $completed_on_date ][] = $completion->user_id;
				}

				if ( ! isset( $completions_by_date[ $completed_on_date ] ) ) {
					$completions_by_date[ $completed_on_date ] = 1;
				} else {
					$completions_by_date[ $completed_on_date ] ++;
				}

				if ( ! isset( $dashboard_data_object['top_course_completions'][ $completion->course_id ] ) ) {
					$completions_rearranged[ $completion->course_id ]                          = 1;
					$dashboard_data_object['top_course_completions'][ $completion->course_id ] = [
						'post_title'              => $course_list[ $completion->course_id ]->post_title,
						'course_price_type'       => $course_list[ $completion->course_id ]->course_price_type,
						'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : $course_users[ $completion->course_id ],
						'completions'             => 1,
					];
				} else {
					$completions_rearranged[ $completion->course_id ] ++;
					$dashboard_data_object['top_course_completions'][ $completion->course_id ]['completions'] ++;
				}

			} else {
				if ( ! isset( $dashboard_data_object['top_course_completions'][ $completion->course_id ] ) ) {
					$completions_rearranged[ $completion->course_id ]                          = 0;
					$dashboard_data_object['top_course_completions'][ $completion->course_id ] = [
						'post_title'              => $course_list[ $completion->course_id ]->post_title,
						'course_price_type'       => $course_list[ $completion->course_id ]->course_price_type,
						'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : $course_users[ $completion->course_id ],
						'completions'             => 0,
					];
				}
			}
		}

		foreach ( $course_list as $course_id => $course ) {
			if ( ! isset( $dashboard_data_object['top_course_completions'][ $course_id ] ) ) {
				$dashboard_data_object['top_course_completions'][ $course_id ] = [
					'post_title'              => $course->post_title,
					'course_price_type'       => $course->course_price_type,
					'course_user_access_list' => ( 'open' === $course_price_type ) ? $all_user_ids_rearranged : ( isset( $course_users[ $course_id ] ) ? $course_users[ $course_id ] : [] ),
					'completions'             => 0,
				];
			}
		}

		// In-progress
		$q_in_progress = "SELECT a.post_id as course_id, a.user_id
						FROM {$wpdb->prefix}learndash_user_activity a
						JOIN $wpdb->prefix" . self::$temp_table . " t
							ON t.user_id = a.user_id
						WHERE a.activity_type = 'course'
						AND ( a.activity_completed = 0 || a.activity_completed IS NULL)
						AND ( a.activity_started != 0 || a.activity_updated != 0)";

		$in_progress = $wpdb->get_results( $q_in_progress );

		$in_progress_rearranged = array();

		foreach ( $in_progress as $progress ) {

			if (
				isset( $course_access_list[ $progress->course_id ] ) &&
				isset( $course_access_list[ $progress->course_id ][ (int) $progress->user_id ]
				)
				||
				(
					isset( $course_list[ $progress->course_id ] ) &&
					'open' === $course_list[ $progress->course_id ]->course_price_type &&
					isset( $all_user_ids_rearranged[ (int) $progress->user_id ] )
				)
			) {

				if ( ! isset( $in_progress_rearranged[ $progress->course_id ] ) ) {
					$in_progress_rearranged[ $progress->course_id ] = 1;
				} else {
					$in_progress_rearranged[ $progress->course_id ] ++;
				}

				$all_user_data_rearranged[ (int) $progress->user_id ]->in_progress ++;

			}
		}
		//$user_ids_extracted = array_keys( $all_user_data_rearranged );
		unset( $in_progress );
		unset( $course_list );
		$q_quiz_results      = "SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.user_id
			FROM {$wpdb->prefix}learndash_user_activity a
			    JOIN $wpdb->prefix" . self::$temp_table . " t
							ON t.user_id = a.user_id
			LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
			WHERE a.activity_type = 'quiz'
			AND m.activity_meta_key = 'percentage'";
		$quiz_results        = $wpdb->get_results( $q_quiz_results );
		$course_quiz_average = array();
		foreach ( $course_access_list as $course_id => $users ) {
			$course_quiz_average[ $course_id ] = self::get_course_quiz_average( $course_id, $quiz_results, $all_user_ids_rearranged );
		}

		unset( $quiz_results );

		$dashboard_data_object['total_courses'] = count( $course_access_list );

		usort( $dashboard_data_object['top_course_completions'], function ( $a, $b ) {
			return $b['completions'] - $a['completions'];
		} );

		$completions                = array();
		$course_completion_by_dates = array();

		// min max date
		foreach ( $completions_by_date as $date => $amount_completions ) {
			$object              = new \stdClass();
			$object->date        = $date;
			$object->completions = $amount_completions;
			if ( $amount_completions > 0 ) {
				array_push( $completions, $object );
				array_push( $course_completion_by_dates, $object );
			}

		}

		unset( $completions_by_date );

		$course_completion_by_course = array();
		foreach ( $completions_by_course as $course_ID => $data ) {
			$course_completion_by_course[ $course_ID ] = array();
			foreach ( $data as $date => $count ) {

				$object              = new \stdClass();
				$object->date        = $date;
				$object->completions = count( $count );
				if ( count( $count ) > 0 ) {
					array_push( $course_completion_by_course[ $course_ID ], $object );
				}
			}
		}

		unset( $completions_by_course );

		$table = $wpdb->prefix . 'uotincan_reporting';
		//sql min, max, date
		$sql_string        = "SELECT x.xstored, x.user_id, x.course_id FROM $table x JOIN $wpdb->prefix" . self::$temp_table . " t ON t.user_id = x.user_id WHERE x.xstored >= NOW() - INTERVAL 1 MONTH";
		$tin_can_completed = $wpdb->get_results( $sql_string );

		$temp_array = array();
		foreach ( $tin_can_completed as $completion ) {

			if ( 'group_leader' === self::get_user_role() || 0 !== self::$isolated_group_id ) {
				if ( ! isset( $all_user_ids_rearranged[ $completion->user_id ] ) ) {
					continue;
				}
				if ( ! isset( $course_access_list[ $completion->course_id ] ) ) {
					continue;
				}
			}

			$date = date( 'Y-m-d', strtotime( $completion->xstored ) );
			if ( ! isset( $temp_array[ $date ] ) ) {
				$temp_array[ $date ] = 1;
			} else {
				$temp_array[ $date ] ++;
			}
		}

		unset( $tin_can_completed );

		$tin_can_stored = array();
		foreach ( $temp_array as $date => $amount_completions ) {
			$object         = new \stdClass();
			$object->date   = $date;
			$object->tinCan = $amount_completions;
			if ( $amount_completions > 0 ) {
				array_push( $tin_can_stored, $object );
			}

		}

		unset( $temp_array );


		$courses_tincan_completed = array_merge( $tin_can_stored, $completions );

		unset( $tin_can_stored );
		unset( $completions );

		usort( $courses_tincan_completed, function ( $a, $b ) {
			return strtotime( $a->date ) - strtotime( $b->date );
		} );

		$dashboard_data_object['courses_tincan_completed'] = $courses_tincan_completed;
		$dashboard_data_object['report_link']              = admin_url( 'admin.php?page=uncanny-learnDash-reporting' );
		$dashboard_data_object['learnDashLabels']          = self::get_labels();

		// TODO Might no need this
		$dashboard_data_object['localizedStrings'] = array(
			'Loading Dashboard Report' => 'xLoading Dashboard Report',
			'Total Users'              => 'xTotal Users',
		);

		$return = array();

		if ( 'both' === $type ) {
			$return['users_overview']              = $all_user_data_rearranged;
			$return['completions']                 = $completions_rearranged;
			$return['in_progress']                 = $in_progress_rearranged;
			$return['all_user_ids']                = $all_user_ids_rearranged;
			$return['course_access_count']         = $course_access_count;
			$return['course_access_list']          = $course_access_list;
			$return['dashboard_data']              = $dashboard_data_object;
			$return['course_quiz_averages']        = $course_quiz_average;
			$return['course_completion_by_dates']  = $course_completion_by_dates;
			$return['course_completion_by_course'] = $course_completion_by_course;

		}

		if ( 'dashboard-only' === $type ) {
			$return = $dashboard_data_object;

		}

		if ( 'report-only' === $type ) {
			$return['users_overview']              = $all_user_data_rearranged;
			$return['completions']                 = $completions_rearranged;
			$return['in_progress']                 = $in_progress_rearranged;
			$return['all_user_ids']                = $all_user_ids_rearranged;
			$return['course_access_count']         = $course_access_count;
			$return['course_access_list']          = $course_access_list;
			$return['course_quiz_averages']        = $course_quiz_average;
			$return['course_completion_by_dates']  = $course_completion_by_dates;
			$return['course_completion_by_course'] = $course_completion_by_course;
		}

		unset( $course_access_list );
		self::drop_temp_table();

		self::create_cache( 'uo_get_courses_overview_data', $return );

		/**
		 * Filters the course overview data
		 */
		return apply_filters( 'uo_get_courses_overview_data', $return, $type );
	}

	/**
	 * @return array
	 */
	public static function get_users_completed_courses() {
		$json_return            = array();
		$json_return['success'] = false;

		$json_return['message'] = __( 'You do not have permission to access this information', 'uncanny-learndash-reporting' );
		$json_return['data']    = array();

		global $wpdb;

		// check current user if admin or group leader
		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			// Modify custom query to restrict data to group leaders available data
			if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

				// Verify the group leader has groups assigned
				if ( ! count( self::get_administrators_group_ids() ) ) {

					$json_return['message'] = __( 'Group Leader has no groups assigned', 'uncanny-learndash-reporting' );
					$json_return['success'] = false;

					return $json_return;
				}
				$leader_groups = self::get_administrators_group_ids();
				if ( ! empty( $leader_groups ) ) {
					foreach ( $leader_groups as $group_id ) {
						// restrict group leader to a single group it its set
						if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
							$meta_keys[] = "'learndash_group_users_" . $group_id . "'";
						} else {
							$meta_keys[] = "'learndash_group_users_" . $group_id . "'";
						}
					}
					$imploded_meta_keys             = implode( ',', $meta_keys );
					$restrict_group_leader_usermeta = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key IN ($imploded_meta_keys) )";
				} else {
					$restrict_group_leader_usermeta = '';
				}
			} elseif ( self::$isolated_group_id ) {
				$restrict_group_leader_usermeta = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'learndash_group_users_" . self::$isolated_group_id . "' )";
			} else {
				$restrict_group_leader_usermeta = '';
			}

			if ( is_multisite() ) {

				$blog_ID = get_current_blog_id();

				$base_capabilities_key = $wpdb->base_prefix . 'capabilities';
				$site_capabilities_key = $wpdb->base_prefix . $blog_ID . '_capabilities';

				if ( 1 === $blog_ID ) {
					$key = $base_capabilities_key;
				} else {
					$key = $site_capabilities_key;
				}

				$restrict_to_blog = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '{$key}')";

			} else {
				$restrict_to_blog = '';
			}

			// Get all user data
			// Users' Progress
			$sql_string = "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE 'course_completed_%' $restrict_group_leader_usermeta $restrict_to_blog";

			$courses_completed = $wpdb->get_results( $sql_string );

			if ( is_array( $courses_completed ) ) {
				$json_return['message'] = '';
				$json_return['success'] = true;
				foreach ( $courses_completed as $course_completed ) {
					$user_id                           = (int) $course_completed->user_id;
					$course_id                         = explode( '_', $course_completed->meta_key );
					$course_id                         = (int) $course_id[2];
					$time_stamp                        = (int) $course_completed->meta_value;
					$date_format                       = 'Y-m-d';
					$json_return['data'][ $user_id ][] = array( $course_id, date( $date_format, $time_stamp ) );
				}
			}
		}

		return $json_return;
	}

	/**
	 * @return array|null
	 */
	public static function get_course_list() {

		if ( null !== self::$course_list ) {
			return self::$course_list;
		}

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {
			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {

					// restrict group leader to a single group it its set
					if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}

				}
				$imploded_meta_keys             = implode( ',', $meta_keys );
				$restrict_group_leader_post     = "AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ($imploded_meta_keys) )";
				$restrict_group_leader_postmeta = "AND post_id IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ($imploded_meta_keys) )";
				$restrict_group_leader_associated_posts
				                                = "AND meta_value IN (
				    SELECT post_id
				    FROM $wpdb->postmeta
				    WHERE meta_key = '_sfwd-courses'
				    AND post_id IN (
				        SELECT post_id
				        FROM $wpdb->postmeta
				        WHERE meta_key IN ($imploded_meta_keys)
				    )
				)";
			} else {
				$restrict_group_leader_post             = '';
				$restrict_group_leader_postmeta         = '';
				$restrict_group_leader_associated_posts = '';
			}
		} elseif ( self::$isolated_group_id ) {

			$restrict_group_leader_post             = "AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "' )";
			$restrict_group_leader_postmeta         = "AND post_id IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "' )";
			$restrict_group_leader_associated_posts =
				"AND meta_value IN (
				    SELECT post_id
				    FROM $wpdb->postmeta
				    WHERE meta_key = '_sfwd-courses'
				    AND post_id IN (
				        SELECT post_id
				        FROM $wpdb->postmeta
				        WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				    )
				)";
		} else {
			$restrict_group_leader_post             = '';
			$restrict_group_leader_postmeta         = '';
			$restrict_group_leader_associated_posts = '';
		}

		// courses
		$sql_string  = "SELECT ID, post_title, post_name FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-courses' $restrict_group_leader_post";
		$course_list = $wpdb->get_results( $sql_string );

		$rearranged_course_list = array();
		foreach ( $course_list as $course ) {
			$course_id                            = (int) $course->ID;
			$rearranged_course_list[ $course_id ] = $course;
		}

		// Course settings
		$sql_string      = "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_sfwd-courses' $restrict_group_leader_postmeta";
		$course_settings = $wpdb->get_results( $sql_string );

		foreach ( $course_settings as $course_setting ) {

			$course_id = (int) $course_setting->post_id;

			if ( ! array_key_exists( $course_id, $rearranged_course_list ) ) {
				continue;
			}

			$courses_settings_values = maybe_unserialize( $course_setting->meta_value );

			if ( is_array( $courses_settings_values ) ) {

				$rearranged_course_list[ $course_id ]->course_user_access_list = self::course_user_access( $course_id, $course_settings );

				foreach ( $courses_settings_values as $key => $value ) {
					if ( 'sfwd-courses_course_price_type' === $key ) {
						$js_key_converted                                        = 'course_price_type';
						$rearranged_course_list[ $course_id ]->$js_key_converted = $value;
					}
				}
			}
			if ( isset( $rearranged_course_list[ $course_id ] ) && isset( $rearranged_course_list[ $course_id ]->course_user_access_list ) ) {
				$rearranged_course_list[ $course_id ]->enrolled_users = count( $rearranged_course_list[ $course_id ]->course_user_access_list );
			}
			// Default value set if course price type settings not found
			if ( ! isset( $rearranged_course_list[ $course_id ]->course_price_type ) ) {
				$rearranged_course_list[ $course_id ]->course_price_type = 'open';
			}
		}

		// Course associated LearnDash Posts
		// Modify custom query to restrict data to group leaders available data
		$sql_string    = "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'course_id' OR meta_key LIKE 'ld_course_%' $restrict_group_leader_associated_posts";
		$courses_posts = $wpdb->get_results( $sql_string );

		foreach ( $courses_posts as $course_post ) {

			$sub_post_id       = (int) $course_post->post_id;
			$associated_course = (int) $course_post->meta_value;

			if ( ! array_key_exists( $associated_course, $rearranged_course_list ) ) {
				continue;
			}

			// make sure that there is an associate course
			if ( 0 == $associated_course ) {
				continue;
			}
			if ( ! isset( $rearranged_course_list[ $associated_course ]->associatedPosts ) ) {
				$rearranged_course_list[ $associated_course ]->associatedPosts = array();
			}

			array_push( $rearranged_course_list[ $associated_course ]->associatedPosts, $sub_post_id );

		}

		self::$course_list = $rearranged_course_list;

		return $rearranged_course_list;
	}

	/**
	 * @param $course_id
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function has_course_access( $course_id, $user_id ) {

		if ( null !== self::$courses_user_access ) {
			if ( isset( self::$courses_user_access[ $course_id ] ) ) {
				if ( in_array_search( $user_id, self::$courses_user_access[ $course_id ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $course_id
	 * @param $course_settings
	 *
	 * @return array|mixed
	 */
	public static function course_user_access( $course_id, $course_settings ) {

		if ( null !== self::$courses_user_access ) {
			if ( isset( self::$courses_user_access[ $course_id ] ) ) {
				return self::$courses_user_access[ $course_id ];
			}
		}

		$LD_settings           = get_option( 'learndash_data_settings', [] );
		$access_list_converted = false;
		if ( isset( $LD_settings['course-access-lists-convert'] ) ) {
			if ( isset( $LD_settings['course-access-lists-convert']['last_run'] ) && 0 !== absint( isset( $LD_settings['course-access-lists-convert']['last_run'] ) ) ) {
				if ( isset( $LD_settings['course-access-lists-convert']['progress_slug'] ) && 'complete' === $LD_settings['course-access-lists-convert']['progress_slug'] ) {
					$access_list_converted = true;
				}
			}
		}

		if ( ! $access_list_converted ) {
			foreach ( $course_settings as $course_setting ) {
				$courses_settings_values = maybe_unserialize( $course_setting->meta_value );
				if ( is_array( $courses_settings_values ) && isset( $courses_settings_values['sfwd-courses_course_access_list'] ) ) {
					$_course_access_list                                         = is_array( $courses_settings_values['sfwd-courses_course_access_list'] ) ? $courses_settings_values['sfwd-courses_course_access_list'] : explode( ',', $courses_settings_values['sfwd-courses_course_access_list'] );
					self::$courses_user_access[ (int) $course_setting->post_id ] = array_map( 'intval', $_course_access_list );
				}
			}
		} else {
			// Run user meta based access data
			global $wpdb;

			$q = "SELECT user_id, meta_key as course_id FROM $wpdb->usermeta WHERE meta_key LIKE 'course_%_access_from'";

			$user_access = $wpdb->get_results( $q );

			foreach ( $user_access as $access ) {
				self::$courses_user_access[ (int) filter_var( $access->course_id, FILTER_SANITIZE_NUMBER_INT ) ][] = (int) $access->user_id;
			}
		}

		if ( isset( self::$courses_user_access[ $course_id ] ) ) {
			return array_unique( self::$courses_user_access[ $course_id ] );
		}

		return array();
	}

	/**
	 * @return array
	 */
	public static function get_groups_list() {

		global $wpdb;

		$group_ids = false;
		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {

					// restrict group leader to a single group it its set
					if ( 0 === self::$isolated_group_id ) {
						$post_keys[]      = "'learndash_group_users_" . $group_id . "'";
						$post_meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						$group_ids[]      = $group_id;
					} elseif ( $group_id == self::$isolated_group_id ) {
						$post_keys[]      = "'learndash_group_users_" . $group_id . "'";
						$post_meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						$group_ids[]      = $group_id;
					}
				}


				$imploded_post_keys      = implode( ',', $post_keys );
				$imploded_post_meta_keys = implode( ',', $post_meta_keys );

				$restrict_group_leader_post      = "AND postmeta.meta_key IN ($imploded_post_keys)";
				$restrict_group_leader_user_meta = "WHERE meta_key IN ($imploded_post_keys)";
				$restrict_group_leader_postmeta  = "where meta_key IN ($imploded_post_meta_keys)";
			} else {
				$restrict_group_leader_post      = "AND postmeta.meta_key LIKE 'learndash_group_users_%'";
				$restrict_group_leader_user_meta = "WHERE meta_key LIKE 'learndash_group_users_%'";
				$restrict_group_leader_postmeta  = "WHERE meta_key LIKE 'learndash_group_enrolled_%'";
			}
		} elseif ( 0 !== self::$isolated_group_id ) {
			$group_ids[]                     = self::$isolated_group_id;
			$restrict_group_leader_post      = "AND postmeta.meta_key = 'learndash_group_users_" . self::$isolated_group_id . "'";
			$restrict_group_leader_user_meta = "WHERE meta_key = 'learndash_group_users_" . self::$isolated_group_id . "'";
			$restrict_group_leader_postmeta  = "where meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'";
		} else {
			$restrict_group_leader_post      = "AND postmeta.meta_key LIKE 'learndash_group_users_%'";
			$restrict_group_leader_user_meta = "WHERE meta_key LIKE 'learndash_group_users_%'";
			$restrict_group_leader_postmeta  = "WHERE meta_key LIKE 'learndash_group_enrolled_%'";
		}

		if ( false !== $group_ids ) {
			$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND ID IN (" . implode( ',', $group_ids ) . ")";
		} else {
			$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'groups'";

		}

		$default_group_list = $wpdb->get_results( $sql_string );

		$sql_string = "SELECT post.ID, post.post_title, postmeta.meta_value FROM $wpdb->posts post JOIN $wpdb->postmeta postmeta ON post.ID = postmeta.post_id WHERE post.post_status = 'publish' AND post.post_type = 'groups' $restrict_group_leader_post";
		$group_list = $wpdb->get_results( $sql_string );

		$sql_string             = "SELECT post_id, REPLACE(meta_key, 'learndash_group_enrolled_', '') as meta_key FROM $wpdb->postmeta $restrict_group_leader_postmeta";
		$course_groups_enrolled = $wpdb->get_results( $sql_string );

		if ( is_multisite() ) {
			$blog_ID = get_current_blog_id();

			$base_capabilities_key = $wpdb->base_prefix . 'capabilities';
			$site_capabilities_key = $wpdb->base_prefix . $blog_ID . '_capabilities';

			if ( 1 === $blog_ID ) {
				$key = $base_capabilities_key;
			} else {
				$key = $site_capabilities_key;
			}

			$restrict_to_blog = "AND user_id IN (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '{$key}')";
		} else {
			$restrict_to_blog = '';
		}

		$sql_string           = "SELECT user_id, REPLACE(meta_key, 'learndash_group_users_', '') as meta_key FROM $wpdb->usermeta $restrict_group_leader_user_meta AND meta_value != '' $restrict_to_blog";
		$user_groups_enrolled = $wpdb->get_results( $sql_string );

		$rearrange_group_list = array();

		$rearrange_course_groups_enrolled = array();

		foreach ( $course_groups_enrolled as $course_group_relationship ) {

			$group_id  = (int) $course_group_relationship->meta_key;
			$course_id = (int) $course_group_relationship->post_id;

			if ( ! isset( $rearrange_course_groups_enrolled[ $group_id ] ) ) {
				$rearrange_course_groups_enrolled[ $group_id ] = array();
			}

			array_push( $rearrange_course_groups_enrolled[ $group_id ], $course_id );

			if ( ! isset( $rearrange_group_list[ $course_id ] ) ) {
				$rearrange_group_list[ $course_id ] = array();
			}
			array_push( $rearrange_group_list[ $course_id ], $group_id );

		}

		unset( $course_groups_enrolled );

		$group_users = array();

		if ( ! empty( $rearrange_course_groups_enrolled ) ) {
			foreach ( $group_list as $group ) {

				$group_id    = (int) $group->ID;
				$group_title = $group->post_title;

				if ( ! isset( $rearrange_course_groups_enrolled[ $group_id ] ) ) {
					continue;
				}

				//$group_users_enrolled = learndash_get_groups_user_ids( $group_id );
				$group_users_enrolled = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE %s AND meta_value = %d", 'learndash_group_users_' . $group_id, $group_id ) );

				$rearrange_group_list[ $group_id ]['ID']                   = $group_id;
				$rearrange_group_list[ $group_id ]['post_title']           = $group_title;
				$group_users[ $group_id ]                                  = ! empty( $group_users_enrolled ) ? $group_users_enrolled : array();
				$rearrange_group_list[ $group_id ]['groups_course_access'] = $rearrange_course_groups_enrolled[ $group_id ];
			}
		}

		unset( $group_list );

		foreach ( $default_group_list as $group ) {
			$group_id    = (int) $group->ID;
			$group_title = $group->post_title;

			if ( ! isset( $rearrange_group_list[ $group_id ] ) ) {
				$rearrange_group_list[ $group_id ]['ID']          = $group_id;
				$rearrange_group_list[ $group_id ]['post_title']  = $group_title;
				$rearrange_group_list[ $group_id ]['groups_user'] = array();
				if ( isset( $rearrange_course_groups_enrolled[ $group_id ] ) ) {
					$rearrange_group_list[ $group_id ]['groups_course_access'] = $rearrange_course_groups_enrolled[ $group_id ];
				} else {
					$rearrange_group_list[ $group_id ]['groups_course_access'] = array();
				}
			}
		}

		unset( $default_group_list );

		if ( ! empty( $user_groups_enrolled ) ) {
			foreach ( $user_groups_enrolled as $user_group_enrolled ) {

				if ( empty( $user_group_enrolled ) ) {
					continue;
				}

				$group_id = (int) $user_group_enrolled->meta_key;

				if ( ! isset( $rearrange_course_groups_enrolled[ $group_id ] ) ) {
					continue;
				}

				if ( empty( $user_group_enrolled->user_id ) ) {
					continue;
				}

				$group_users[ $group_id ][] = $user_group_enrolled->user_id;

			}
		}

		unset( $user_groups_enrolled );

		unset( $rearrange_course_groups_enrolled );

		if ( ! empty( $rearrange_group_list ) ) {
			foreach ( $rearrange_group_list as $group_id => &$data ) {
				if ( isset( $group_users[ $group_id ] ) ) {
					$data['groups_user'] = $group_users[ $group_id ];
				}
			}

		}

		unset( $group_users );

		return $rearrange_group_list;
	}

	/**
	 * Collect general user course data and LearnDash Labels
	 *
	 * @return array
	 */
	public static function get_table_data() {

		$json_return            = array();
		$json_return['message'] = '';
		$json_return['success'] = true;
		$json_return['data']    = array();

		$table_type = '';
		if ( isset( $_POST['tableType'] ) ) {
			$table_type = $_POST['tableType'];
		}

		switch ( $table_type ) {
			case 'courseSingleTable':
				$json_return['success'] = false;
				$json_return['data']    = $_POST;

				if ( isset( $_POST['courseId'] ) && isset( $_POST['rows'] ) ) {

					$course_id = absint( $_POST['courseId'] );
					$rows      = array();

					if ( is_string( $_POST['rows'] ) ) {
						$post_rows = json_decode( stripslashes( $_POST['rows'] ) );
						foreach ( $post_rows as $row ) {
							$rows[ absint( $row->rowId ) ] = absint( $row->ID );
						}
					} else {
						$post_rows = $_POST['rows'];
						foreach ( $post_rows as $row ) {
							$rows[ absint( $row['rowId'] ) ] = absint( $row['ID'] );
						}
					}

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['data']    = self::get_course_single_overview( $course_id, $rows );

					return apply_filters( 'tc_api_get_courseSingleTable', $json_return, $course_id, $rows );

				} else {
					$json_return['message'] = 'courseId or rowsIds not set';
				}


				return $json_return;
			case 'userSingleCoursesOverviewTable';

				$json_return['message'] = 'userId or rowsIds not set';
				$json_return['success'] = false;
				$json_return['data']    = $_POST;

				if ( isset( $_POST['userId'] ) ) {

					$user_id = absint( $_POST['userId'] );
					$rows    = array();

					if ( is_string( $_POST['rows'] ) ) {
						$post_rows = json_decode( stripslashes( $_POST['rows'] ) );
						foreach ( $post_rows as $row ) {
							$rows[ absint( $row->rowId ) ] = absint( $row->ID );
						}
					} else {
						$post_rows = $_POST['rows'];
						foreach ( $post_rows as $row ) {
							$rows[ absint( $row['rowId'] ) ] = absint( $row['ID'] );
						}
					}

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['user_id'] = $user_id;
					$json_return['data']    = self::get_user_single_overview( $user_id, $rows );

					return apply_filters( 'tc_api_get_userSingleCoursesOverviewTable', $json_return, $user_id, $rows );
				}

				return $json_return;
			case 'userSingleCourseProgressSummaryTable':

				$json_return['message'] = 'userId or courseId not set';
				$json_return['success'] = false;
				$json_return['data']    = $_POST;

				if ( isset( $_POST['userId'] ) && isset( $_POST['courseId'] ) ) {
					$user_id   = absint( $_POST['userId'] );
					$course_id = absint( $_POST['courseId'] );

					$json_return['message'] = '';
					$json_return['success'] = true;
					$json_return['data']    = self::get_user_single_course_overview( $user_id, $course_id );

					return apply_filters( 'tc_api_get_userSingleCourseProgressSummaryTable', $json_return, $user_id, $course_id );
				}

				return $json_return;
			default:
				$json_return['message'] = 'tableType not set';
				$json_return['success'] = false;
				$json_return['data']    = array();

				return $json_return;
		}
	}

	/**
	 * Get user avatar
	 *
	 * @return array
	 */

	public static function get_user_avatar() {

		$response = [
			'message' => '',
			'success' => false,
			'data'    => [],
		];

		// Check if the user id is defined
		if ( isset( $_POST['user_id'] ) ) {
			// Define user id
			$user_id = $_POST['user_id'];

			// Get avatar
			$avatar_url = get_avatar_url( $user_id );

			// Check if it has a valid value
			if ( $avatar_url != false ) {
				// It's valid, save it
				$response['data']['avatar'] = $avatar_url;

				// and change "success" value
				$response['success'] = true;
			} else {
				$response['message']    = __( "We couldn't find an avatar.", 'uncanny-learndash-reporting' );
				$response['error_code'] = 2;
			}
		} else {
			$response['message']    = __( 'Invalid user ID', 'uncanny-learndash-reporting' );
			$response['error_code'] = 1;
		}

		return $response;
	}

	/**
	 * @param $course_id
	 * @param $user_ids
	 *
	 * @return array
	 */
	private static function get_course_single_overview( $course_id, $user_ids ) {

		if( isset( $_POST['tablePage'] ) ) {
			$page   = isset( $_POST['tablePage']['page'] ) ? absint( $_POST['tablePage']['page'] ) : 0;
			$length = isset( $_POST['tablePage']['length'] ) ? absint( $_POST['tablePage']['length'] ) : 10 ;
			$column = isset( $_POST['tablePage']['column'] ) ? absint( $_POST['tablePage']['column'] ) : 0;
		}


		if ( isset( $_POST['tablePage']['order'] ) ) {

			if ( 'desc' === $_POST['tablePage']['order'] ) {
				$order = 'DESC';
			} else {
				$order = 'ASC';
			}

		} else {
			$order = 'ASC';
		}

		$user_ids_rearranged = array();
		foreach ( $user_ids as $row_id => $user_id ) {
			$user_ids_rearranged[ $user_id ]             = array();
			$user_ids_rearranged[ $user_id ]['progress'] = 0;
			$user_ids_rearranged[ $user_id ]['date']     = [ 'display' => '', 'timestamp' => '0' ];
		}

		global $wpdb;

		$complete_key = "course_completed_{$course_id}";
		$q            = "SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_sfwd-course_progress' OR meta_key = '{$complete_key}'";

		$user_data = $wpdb->get_results( $q );

		foreach ( $user_data as $data ) {
			$user_id = $data->user_id;

			if ( ! isset( $user_ids_rearranged[ $user_id ] ) ) {
				continue;
			}

			$meta_key   = $data->meta_key;
			$meta_value = $data->meta_value;

			if ( $complete_key === $meta_key ) {
				if ( absint( $meta_value ) ) {
					$user_ids_rearranged[ $user_id ]['date'] = [
						"display"   => learndash_adjust_date_time_display( $meta_value ),
						"timestamp" => (string) $meta_value,
					];
				}
			} elseif ( '_sfwd-course_progress' === $meta_key ) {
				$progress = unserialize( $meta_value );
				if ( ! empty( $progress ) && ! empty( $progress[ $course_id ] ) && ! empty( $progress[ $course_id ]['total'] ) ) {
					$completed = intVal( $progress[ $course_id ]['completed'] );
					$total     = intVal( $progress[ $course_id ]['total'] );
					if ( $total > 0 ) {
						$percentage                                  = intval( $completed * 100 / $total );
						$percentage                                  = ( $percentage > 100 ) ? 100 : $percentage;
						$user_ids_rearranged[ $user_id ]['progress'] = $percentage;
					}
				}

			}
		}


		$quiz_averages = self::get_course_quiz_average_by_user( $course_id, $user_ids );

		$rows = array();
		foreach ( $user_ids as $row_id => $user_id ) {

			$rows[ $row_id ]['user_id']        = $user_id;
			$rows[ $row_id ]['completed_date'] = $user_ids_rearranged[ $user_id ]['date'];
			$rows[ $row_id ]['progress']       = $user_ids_rearranged[ $user_id ]['progress'];

			if ( isset( $quiz_averages[ $user_id ] ) ) {
				$rows[ $row_id ]['quiz_average'] = $quiz_averages[ $user_id ];
			} else {
				$rows[ $row_id ]['quiz_average'] = '';
			}
		}

		return $rows;
	}

	/**
	 * @param $user_id
	 * @param $course_ids
	 *
	 * @return array
	 */
	private static function get_user_single_overview( $user_id, $course_ids ) {

		$rows = array();

		// quiz scores
		global $wpdb;

		$q = "SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.activity_status
			FROM {$wpdb->prefix}learndash_user_activity a
			LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
			WHERE a.user_id = {$user_id}
			AND a.activity_type = 'quiz'
			AND m.activity_meta_key = 'percentage'";

		$user_activities = $wpdb->get_results( $q );

		$progress              = get_user_meta( $user_id, '_sfwd-course_progress', true );
		$course_ids_rearranged = array();

		foreach ( $course_ids as $row_id => $course_id ) {
			$course_ids_rearranged[ $course_id ] = array();
			if ( ! empty( $progress ) && ! empty( $progress[ $course_id ] ) && ! empty( $progress[ $course_id ]['total'] ) ) {
				$completed = intVal( $progress[ $course_id ]['completed'] );
				$total     = intVal( $progress[ $course_id ]['total'] );
				if ( $total > 0 ) {
					$percentage                                      = intval( $completed * 100 / $total );
					$percentage                                      = ( $percentage > 100 ) ? 100 : $percentage;
					$course_ids_rearranged[ $course_id ]['progress'] = $percentage;
				}
			} else {
				$course_ids_rearranged[ $course_id ]['progress'] = 0;
			}

			$course_ids_rearranged[ $course_id ]['date'] = [ 'display' => '', 'timestamp' => '0' ];;
		}

		$q = "SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE 'course_completed_%' AND user_id = {$user_id}";

		$user_data = $wpdb->get_results( $q );

		foreach ( $user_data as $data ) {
			$x_meta_key = explode( '_', $data->meta_key );
			$course_id  = $x_meta_key[2];

			$meta_value = $data->meta_value;

			$course_ids_rearranged[ $course_id ]['date'] = [
				'display'   => learndash_adjust_date_time_display( $meta_value ),
				'timestamp' => (string) $meta_value,
			];
		}

		foreach ( $course_ids as $row_id => $course_id ) {

			$rows[ $row_id ]['course_id']      = $course_id;
			$rows[ $row_id ]['completed_date'] = $course_ids_rearranged[ $course_id ]['date'];
			$rows[ $row_id ]['progress']       = $course_ids_rearranged[ $course_id ]['progress'];

			// Column Quiz Average
			$course_quiz_average = self::get_avergae_quiz_result( $course_id, $user_activities );

			$avg_score = '';

			if ( $course_quiz_average ) {
				/* Translators: 1. number percentage */
				$avg_score = sprintf( __( '%1$s%%', 'uncanny-learndash-reporting' ), $course_quiz_average );
			}

			$rows[ $row_id ]['avg_score'] = $avg_score;
		}

		return $rows;
	}


	/**
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return array
	 */
	private static function get_user_single_course_overview( $user_id, $course_id ) {

		$status                 = array();
		$status['completed']    = __( 'Completed', 'uncanny-learndash-reporting' );
		$status['notcompleted'] = __( 'Not Completed', 'uncanny-learndash-reporting' );

		// Get Lessons
		$lessons_list       = learndash_get_course_lessons_list( $course_id, $user_id, [ 'per_page' => - 1 ] );
		$course_quiz_list   = array();
		$course_quiz_list[] = learndash_get_course_quiz_list( $course_id );

		$course_label = \LearnDash_Custom_Label::get_label( 'course' );

		$lessons      = array();
		$topics       = array();
		$lesson_names = array();
		$topic_names  = array();
		$quiz_names   = array();

		$lesson_order = 0;
		$topic_order  = 0;
		foreach ( $lessons_list as $lesson ) {

			$lesson_names[ $lesson['post']->ID ] = $lesson['post']->post_title;
			$lessons[ $lesson_order ]            = [
				'name'   => $lesson['post']->post_title,
				'status' => $status[ $lesson['status'] ],
			];

			$course_quiz_list[] = learndash_get_lesson_quiz_list( $lesson['post']->ID, $user_id, $course_id );
			$lesson_topics      = learndash_get_topic_list( $lesson['post']->ID, $course_id );

			foreach ( $lesson_topics as $topic ) {

				$course_quiz_list[] = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );

				$topic_progress = learndash_get_course_progress( $user_id, $topic->ID, $course_id );

				$topic_names[ $topic->ID ] = $topic->post_title;

				$topics[ $topic_order ] = [
					'name'              => $topic->post_title,
					'status'            => $status['notcompleted'],
					'associated_lesson' => $lesson['post']->post_title,
				];

				if ( ( isset( $topic_progress['posts'] ) ) && ( ! empty( $topic_progress['posts'] ) ) ) {
					foreach ( $topic_progress['posts'] as $topic_progress ) {

						if ( $topic->ID !== $topic_progress->ID ) {
							continue;
						}

						if ( 1 === $topic_progress->completed ) {
							$topics[ $topic_order ]['status'] = $status['completed'];
						}
					}
				}
				$topic_order ++;
			}
			$lesson_order ++;
		}

		global $wpdb;

		// Assignments
		$assignments            = array();
		$sql_string             = "
		SELECT post.ID, post.post_title, post.post_date, postmeta.meta_key, postmeta.meta_value
		FROM $wpdb->posts post
		JOIN $wpdb->postmeta postmeta ON post.ID = postmeta.post_id
		WHERE post.post_status = 'publish' AND post.post_type = 'sfwd-assignment'
		AND post.post_author = $user_id
		AND ( postmeta.meta_key = 'approval_status' OR postmeta.meta_key = 'course_id' OR postmeta.meta_key LIKE 'ld_course_%' )";
		$assignment_data_object = $wpdb->get_results( $sql_string );

		foreach ( $assignment_data_object as $assignment ) {

			// Assignment List
			$data               = array();
			$data['ID']         = $assignment->ID;
			$data['post_title'] = $assignment->post_title;

			$assignment_id                                = (int) $assignment->ID;
			$rearranged_assignment_list[ $assignment_id ] = $data;

			// User Assignment Data
			$assignment_id = (int) $assignment->ID;
			$meta_key      = $assignment->meta_key;
			$meta_value    = (int) $assignment->meta_value;

			$date = learndash_adjust_date_time_display( strtotime( $assignment->post_date ) );

			$assignments[ $assignment_id ]['name']           = '<a target="_blank" href="' . get_edit_post_link( $assignment->ID ) . '">' . $assignment->post_title . '</a>';
			$assignments[ $assignment_id ]['completed_date'] = $date;
			$assignments[ $assignment_id ][ $meta_key ]      = $meta_value;

		}

		foreach ( $assignments as $assignment_id => &$assignment ) {
			if ( isset( $assignment['course_id'] ) && $course_id !== (int) $assignment['course_id'] ) {
				unset( $assignments[ $assignment_id ] );
			} else {
				if ( isset( $assignment['approval_status'] ) && 1 == $assignment['approval_status'] ) {
					$assignment['approval_status'] = __( 'Approved', 'uncanny-learndash-reporting' );
				} else {
					$assignment['approval_status'] = __( 'Not Approved', 'uncanny-learndash-reporting' );
				}
			}
		}

		// Quizzes Scores Avg
		global $wpdb;

		$q = "SELECT a.activity_id, a.course_id, a.post_id, a.activity_status, a.activity_completed, m.activity_meta_value as activity_percentage
			FROM {$wpdb->prefix}learndash_user_activity a
			LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
			WHERE a.user_id = {$user_id}
			AND a.course_id = {$course_id}
			AND a.activity_type = 'quiz'
			AND m.activity_meta_key = 'percentage'";

		$user_activities = $wpdb->get_results( $q );

		// Quizzes
		$quizzes = array();

		foreach ( $course_quiz_list as $module_quiz_list ) {
			if ( empty( $module_quiz_list ) ) {
				continue;
			}

			foreach ( $module_quiz_list as $quiz ) {

				if ( isset( $quiz['post'] ) ) {

					$quiz_names[ $quiz['post']->ID ] = $quiz['post']->post_title;
					$certificate_link                = '';
					$certificate                     = learndash_certificate_details( $quiz['post']->ID, $user_id );
					if ( ! empty( $certificate ) && isset( $certificate['certificateLink'] ) ) {
						$certificate_link = $certificate['certificateLink'];
					}

					foreach ( $user_activities as $activity ) {

						if ( $activity->post_id == $quiz['post']->ID ) {

							$pro_quiz_id = learndash_get_user_activity_meta( $activity->activity_id, 'pro_quizid', true );
							if ( empty( $pro_quiz_id ) ) {
								// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
								$pro_quiz_id = absint( get_post_meta( $quiz['post']->ID, 'quiz_pro_id', true ) );
							}

							$statistic_ref_id = learndash_get_user_activity_meta( $activity->activity_id, 'statistic_ref_id', true );
							if ( empty( $statistic_ref_id ) ) {

								if ( class_exists( '\LDLMS_DB' ) ) {
									$pro_quiz_master_table   = \LDLMS_DB::get_table_name( 'quiz_master' );
									$pro_quiz_stat_ref_table = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
								} else {
									$pro_quiz_master_table   = $wpdb->prefix . 'wp_pro_quiz_master';
									$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
								}

								// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
								$sql_str = $wpdb->prepare(
									'SELECT statistic_ref_id FROM ' . $pro_quiz_stat_ref_table . ' as stat
									INNER JOIN ' . $pro_quiz_master_table . ' as master ON stat.quiz_id=master.id
									WHERE  user_id = %d AND quiz_id = %d AND create_time = %d AND master.statistics_on=1 LIMIT 1', $user_id, $pro_quiz_id, $activity->activity_completed
								);

								$statistic_ref_id = $wpdb->get_var( $sql_str );
							}

							$modal_link = '';

							if ( empty( $statistic_ref_id ) || empty( $pro_quiz_id ) ) {
								if ( ! empty( $statistic_ref_id ) ) {
									$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . $user_id . '"
									     data-quiz_id="' . $pro_quiz_id . '"
									     data-ref_id="' . intval( $statistic_ref_id ) . '"
									     data-uo-pro-quiz-id="' . intval( $pro_quiz_id ) . '"
									     data-uo-quiz-id="' . intval( $activity->post_id ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#"> </a>';
								}
							} else {
								if ( ! empty( $statistic_ref_id ) ) {
									$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . $user_id . '"
									     data-quiz_id="' . $pro_quiz_id . '"
									     data-ref_id="' . intval( $statistic_ref_id ) . '"
									     data-uo-pro-quiz-id="' . intval( $pro_quiz_id ) . '"
									     data-uo-quiz-id="' . intval( $activity->post_id ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#">';
									$modal_link .= '<div class="statistic_icon"></div>';
									$modal_link .= '</a>';
								}
							}

							$quizzes[] = [
								'name'             => $quiz['post']->post_title,
								'score'            => $activity->activity_percentage,
								'detailed_report'  => $modal_link,
								'completed_date'   => [
									'display'   => learndash_adjust_date_time_display( $activity->activity_completed ),
									'timestamp' => $activity->activity_completed,
								],
								'certificate_link' => $certificate_link,
							];
							//}
						}
					}
				}
			}
		}

		$progress = learndash_course_progress(
			array(
				'course_id' => $course_id,
				'user_id'   => $user_id,
				'array'     => true,
			)
		);

		$completed_date = '';

		if ( 100 <= $progress['percentage'] ) {
			$progress_percentage = $progress['percentage'];
			$completed_timestamp = learndash_user_get_course_completed_date( $user_id, $course_id );
			if ( absint( $completed_timestamp ) ) {
				$completed_date = learndash_adjust_date_time_display( learndash_user_get_course_completed_date( $user_id, $course_id ) );
				$status         = __( 'Completed', 'uncanny-learndash-reporting' );
			} else {
				$status = __( 'In Progress', 'uncanny-learndash-reporting' );
			}

		} else {
			$progress_percentage = absint( $progress['completed'] / $progress['total'] * 100 );
			$status              = __( 'In Progress', 'uncanny-learndash-reporting' );
		}

		if ( 0 === $progress_percentage ) {
			$progress_percentage = '';
			$status              = __( 'Not Started', 'uncanny-learndash-reporting' );
		} else {
			$progress_percentage = $progress_percentage . __( '%', 'uncanny-learndash-reporting' );

		}

		// Column Quiz Average
		$course_quiz_average = self::get_avergae_quiz_result( $course_id, $user_activities );

		$avg_score = '';

		if ( $course_quiz_average ) {
			/* Translators: 1. number percentage */
			$avg_score = sprintf( __( '%1$s%%', 'uncanny-learndash-reporting' ), $course_quiz_average );
		}

		// TinCanny
		global $wpdb;
		$table           = $wpdb->prefix . 'uotincan_reporting';
		$q_tc_statements = "SELECT lesson_id as post_id, module_name, target_name, verb as action, result, xstored FROM $table WHERE user_id = {$user_id} AND course_id = {$course_id}";
		$statements_list = $wpdb->get_results( $q_tc_statements );
		$statements      = array();
		foreach ( $statements_list as $statement ) {

			if ( isset( $quiz_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $quiz_names[ (int) $statement->post_id ];
			} elseif ( isset( $topic_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $topic_names[ (int) $statement->post_id ];
			} elseif ( isset( $lesson_names[ (int) $statement->post_id ] ) ) {
				$related_post_name = $lesson_names[ (int) $statement->post_id ];
			} elseif ( (int) $statement->post_id === $course_id ) {
				$related_post_name = sprintf( _x( '%s Level', '%s is the "Course" label', 'uncanny-learndash-reporting' ), $course_label );
			} else {
				$related_post_name = __( 'Not Found: ', 'uncanny-learndash-reporting' ) . $statement->post_id;
			}

			$date = $statement->xstored;

			$statements[] = [
				'related_post' => $related_post_name,
				'module'       => $statement->module_name,
				'target'       => $statement->target_name,
				'action'       => $statement->action,
				'result'       => $statement->result,
				'date'         => $date,
			];

		}

		return [
			'completed_date'      => $completed_date,
			'progress_percentage' => $progress_percentage,
			'avg_score'           => $avg_score,
			'status'              => $status,
			'lessons'             => $lessons,
			'topics'              => $topics,
			'quizzes'             => $quizzes,
			'assigments'          => $assignments,
			'statements'          => $statements,
			'course_certificate'  => learndash_get_course_certificate_link( $course_id, $user_id ),
		];
	}

	/**
	 * @param $course_id
	 * @param $user_activities
	 *
	 * @return false|int
	 */
	private static function get_avergae_quiz_result( $course_id, $user_activities ) {

		$quiz_scores = array();

		foreach ( $user_activities as $activity ) {

			if ( $course_id == $activity->course_id ) {

				if ( ! isset( $quiz_scores[ $activity->post_id ] ) ) {

					$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->post_id ] < $activity->activity_percentage ) {

					$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
				}
			}
		}

		if ( 0 !== count( $quiz_scores ) ) {
			$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		} else {
			$average = false;
		}

		return $average;
	}

	/**
	 * @param $course_id
	 * @param $user_activities
	 * @param $user_ids
	 *
	 * @return int|string
	 */
	private static function get_course_quiz_average( $course_id, $user_activities, $user_ids ) {

		$quiz_scores = array();

		foreach ( $user_activities as $activity ) {

			if ( isset( $user_ids[ (int) $activity->user_id ] ) && $course_id == $activity->course_id ) {

				if ( ! isset( $quiz_scores[ $activity->post_id . $activity->user_id ] ) ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->post_id . $activity->user_id ] < $activity->activity_percentage ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				}
			}
		}

		if ( 0 !== count( $quiz_scores ) ) {
			$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		} else {
			$average = 'false';
		}

		return $average;
	}

	/**
	 * @param $course_id
	 * @param $user_ids
	 *
	 * @return array
	 */
	private static function get_course_quiz_average_by_user( $course_id, $user_ids ) {

		global $wpdb;

		$user_ids_rearranged = array();
		foreach ( $user_ids as $user_id ) {
			$user_ids_rearranged[ $user_id ] = $user_id;
		}

		$q_quiz_results = "
			SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.user_id
			FROM {$wpdb->prefix}learndash_user_activity a
			LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
			WHERE a.activity_type = 'quiz'
			AND a.course_id = $course_id
			AND m.activity_meta_key = 'percentage'
		";

		$quiz_results = $wpdb->get_results( $q_quiz_results );

		$quiz_scores = array();

		foreach ( $quiz_results as $activity ) {

			if ( isset( $user_ids_rearranged[ (int) $activity->user_id ] ) ) {

				if ( ! isset( $quiz_scores[ $activity->user_id ] ) ) {
					$quiz_scores[ $activity->user_id ] = array();
				}

				if ( ! isset( $quiz_scores[ $activity->user_id ][ $activity->post_id ] ) ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->user_id ][ $activity->post_id ] < $activity->activity_percentage ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				}
			}
		}

		$averages = array();
		if ( 0 !== count( $quiz_scores ) ) {
			foreach ( $quiz_scores as $user_id => $scores ) {
				$averages[ $user_id ] = absint( array_sum( $scores ) / count( $scores ) );
			}
		}


		return $averages;
	}

	/**
	 * @return array
	 */
	public static function get_course_modules() {

		$course_modules = array();

		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			$course_modules['lessonList'] = self::get_lesson_list();
			$course_modules['topicList']  = self::get_topic_list();
			$course_modules['quizList']   = self::get_quiz_list();

		}

		return $course_modules;
	}

	/**
	 * @return array
	 */
	private static function get_lesson_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}
				}
				$imploded_meta_keys = implode( ',', $meta_keys );
				$restrict_group_leader_post
				                    = "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$rearranged_lesson_list = array();


		$sql_string  = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-lessons' $restrict_group_leader_post";
		$sql_string  = apply_filters( 'get_lesson_list_sql', $sql_string, $restrict_group_leader_post );
		$lesson_list = $wpdb->get_results( $sql_string );

		foreach ( $lesson_list as $lesson ) {
			$lesson_id                            = (int) $lesson->ID;
			$rearranged_lesson_list[ $lesson_id ] = $lesson;
		}

		$rearranged_lesson_list[1] = array();

		return $rearranged_lesson_list;
	}

	/**
	 * @return array
	 */
	private static function get_topic_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}
				}

				$imploded_meta_keys = implode( ',', $meta_keys );
				$restrict_group_leader_post
				                    = "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-topic' $restrict_group_leader_post";
		$topic_list = $wpdb->get_results( $sql_string );


		$rearranged_topic_list = array();
		foreach ( $topic_list as $topic ) {
			$topic_id                           = (int) $topic->ID;
			$rearranged_topic_list[ $topic_id ] = $topic;
		}

		$rearranged_topic_list[1] = array();

		return $rearranged_topic_list;

	}

	/**
	 * @return array
	 */
	private static function get_quiz_list() {

		global $wpdb;

		// Modify custom query to restrict data to group leaders available data
		if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}

				}
				$imploded_meta_keys = implode( ',', $meta_keys );
				$restrict_group_leader_post
				                    = "AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key IN ($imploded_meta_keys)
				)
				)";
			} else {
				$restrict_group_leader_post = '';
			}
		} elseif ( self::$isolated_group_id ) {
			$restrict_group_leader_post =
				"AND ID IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'course_id'
				OR meta_key LIKE 'ld_course_%'
				AND meta_value IN (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "'
				)
				)";
		} else {
			$restrict_group_leader_post = '';
		}

		$sql_string = "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-quiz'";
		$quiz_list  = $wpdb->get_results( $sql_string );


		$rearranged_quiz_list = array();
		foreach ( $quiz_list as $quiz ) {
			$quiz_id                          = (int) $quiz->ID;
			$rearranged_quiz_list[ $quiz_id ] = $quiz;
		}

		$rearranged_quiz_list[1] = array();

		return $rearranged_quiz_list;

	}

	/**
	 * @return array
	 */
	public static function get_assignment_data() {

		global $wpdb;

		$rearranged_assignment_list      = array();
		$merged_approval_assignment_data = array();

		if ( current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {

			// Modify custom query to restrict data to group leaders available data
			if ( 'group_leader' === self::get_user_role() && ! self::$isolated_group_id ) {

				$leader_groups = self::get_administrators_group_ids();
				if ( ! empty( $leader_groups ) ) {
					foreach ( $leader_groups as $group_id ) {
						if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
							$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						} else {
							$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
						}

					}
					$imploded_meta_keys = implode( ',', $meta_keys );
					// TODO CHECK ASSIGNMENT ACTIVITIES
					$restrict_group_leader_post = "AND post.ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ($imploded_meta_keys) )";
				} else {
					$restrict_group_leader_post = '';
				}
			} elseif ( self::$isolated_group_id ) {
				$restrict_group_leader_post = "AND post.ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'learndash_group_enrolled_" . self::$isolated_group_id . "' )";
			} else {
				$restrict_group_leader_post = '';
			}

			$sql_string             = "SELECT post.ID, post.post_author, post.post_title, post.post_date, postmeta.meta_key, postmeta.meta_value FROM $wpdb->posts post JOIN $wpdb->postmeta postmeta ON post.ID = postmeta.post_id WHERE post.post_status = 'publish' AND post.post_type = 'sfwd-assignment' AND ( postmeta.meta_key = 'approval_status' OR postmeta.meta_key = 'course_id' OR postmeta.meta_key LIKE 'ld_course_%' ) $restrict_group_leader_post";
			$assignment_data_object = $wpdb->get_results( $sql_string );


			foreach ( $assignment_data_object as $assignment ) {

				// Assignment List
				$data               = array();
				$data['ID']         = $assignment->ID;
				$data['post_title'] = $assignment->post_title;

				$assignment_id                                = (int) $assignment->ID;
				$rearranged_assignment_list[ $assignment_id ] = $data;

				// User Assignment Data
				$assignment_id      = (int) $assignment->ID;
				$assignment_user_id = (int) $assignment->post_author;
				$meta_key           = $assignment->meta_key;
				$meta_value         = (int) $assignment->meta_value;

				// SQL Time '1970-01-17 05:54:21' exploded to get date only
				$date                                                                                     = explode( ' ', $assignment->post_date );
				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ]['completed_on'] = $date[0];

				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ]['ID'] = $assignment_id;

				$merged_approval_assignment_data[ $assignment_user_id ][ $assignment_id ][ $meta_key ] = $meta_value;

			}

			$rearranged_assignment_list[1] = array();

			$assignment_data['userAssignmentData'] = $merged_approval_assignment_data;
			$assignment_data['assignmentList']     = $rearranged_assignment_list;

		}

		return $assignment_data;
	}

	/**
	 * @return array
	 */
	public static function get_labels() {

		$labels['course']  = \LearnDash_Custom_Label::get_label( 'course' );
		$labels['courses'] = \LearnDash_Custom_Label::get_label( 'courses' );

		$labels['lesson']  = \LearnDash_Custom_Label::get_label( 'lesson' );
		$labels['lessons'] = \LearnDash_Custom_Label::get_label( 'lessons' );

		$labels['topic']  = \LearnDash_Custom_Label::get_label( 'topic' );
		$labels['topics'] = \LearnDash_Custom_Label::get_label( 'topics' );

		$labels['quiz']    = \LearnDash_Custom_Label::get_label( 'quiz' );
		$labels['quizzes'] = \LearnDash_Custom_Label::get_label( 'quizzes' );


		return $labels;
	}

	/**
	 * @return array
	 */
	public static function get_links() {

		$labels = array();

		$labels['profile']    = admin_url( 'user-edit.php', 'admin' );
		$labels['assignment'] = admin_url( 'post.php', 'admin' );;

		return $labels;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public static function get_tincan_data( $data ) {

		$return_object = array();

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			$return_object['message'] = __( 'Current User doesn\'t have permissions to Tin Can report data', 'uncanny-learndash-reporting' );
			$return_object['user_ID'] = get_current_user_id();
		}

		// validate inputs
		$user_ID = absint( $data['user_ID'] );

		// if any of the values are 0 then they didn't validate, storage is not possible
		if ( 0 === $user_ID ) {
			$return_object['message'] = 'invalid user id supplied';
			$return_object['user_ID'] = $data['user_ID'];
		}

		if ( 'group_leader' === self::get_user_role() ) {

			global $wpdb;

			$leader_groups = self::get_administrators_group_ids();
			if ( ! empty( $leader_groups ) ) {
				foreach ( $leader_groups as $group_id ) {
					if ( self::$isolated_group_id && $group_id == self::$isolated_group_id ) {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					} else {
						$meta_keys[] = "'learndash_group_enrolled_" . $group_id . "'";
					}

				}

				$imploded_meta_keys = implode( ',', $meta_keys );

				$restrict_group_leader_post = "AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ($imploded_meta_keys) )";
			} else {
				$restrict_group_leader_post = '';
			}
			// courses
			$sql_string       = "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'sfwd-courses' $restrict_group_leader_post";
			$group_course_ids = $wpdb->get_col( $sql_string );
		}

		$tin_can_data = null;
		if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
			$database          = new \UCTINCAN\Database\Admin();
			$database->user_id = $user_ID;
			$tin_can_data      = $database->get_data();
		}

		if ( null !== $tin_can_data && ! empty( $tin_can_data ) ) {

			$data          = array();
			$sample        = array();
			$sample['All'] = $tin_can_data;
			foreach ( $tin_can_data as $user_single_tin_can_object ) {

				$course_ID = (int) $user_single_tin_can_object['course_id'];
				$lesson_ID = (int) $user_single_tin_can_object['lesson_id'];

				if ( 'group_leader' === self::get_user_role() ) {
					if ( ! in_array( $course_ID, $group_course_ids ) ) {
						continue;
					}
				}

				if ( $user_single_tin_can_object['lesson_id'] && $user_single_tin_can_object['course_id'] ) {

					if ( ! isset( $data[ $course_ID ] ) ) {
						$data[ $course_ID ] = array();
					}
					if ( ! isset( $data[ $course_ID ][ $lesson_ID ] ) ) {
						$data[ $course_ID ][ $lesson_ID ] = array();
					}
					$course_ID = (int) $user_single_tin_can_object['course_id'];
					$lesson_ID = (int) $user_single_tin_can_object['lesson_id'];
					array_push( $data[ $course_ID ][ $lesson_ID ], $user_single_tin_can_object );

				} else {
					continue;
				}

			}

			return [ 'user_ID' => $user_ID, 'tinCanStatements' => $data ];

		} else {
			return array();
		}

	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public static function show_tincan_tables( $data ) {

		$show_tincan_tables = absint( $data['show_tincan'] );

		if ( 1 == $show_tincan_tables ) {
			$value = 'yes';
		}
		if ( 0 == $show_tincan_tables ) {
			$value = 'no';
		}

		if ( current_user_can( 'manage_options' ) ) {
			$updated = update_option( 'show_tincan_reporting_tables', $value );

			return $value;
		} else {
			return 'no permissions';
		}


	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public static function disable_mark_complete( $data ) {

		$disable_mark_complete = absint( $data['disable_mark_complete'] );

		if ( 1 == $disable_mark_complete ) {
			$value = 'yes';
		}
		if ( 0 == $disable_mark_complete ) {
			$value = 'no';
		}
		if ( 3 == $disable_mark_complete ) {
			$value = 'hide';
		}
		if ( 4 == $disable_mark_complete ) {
			$value = 'remove';
		}
		if ( 5 == $disable_mark_complete ) {
			$value = 'autoadvance';
		}

		if ( current_user_can( 'manage_options' ) ) {
			$updated = update_option( 'disable_mark_complete_for_tincan', $value );

			return $value;
		} else {
			return 'no permissions';
		}
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public static function nonce_protection( $data ) {

		$nonce_protection = absint( $data['nonce_protection'] );

		if ( 1 == $nonce_protection ) {
			$value = 'yes';
		}
		if ( 0 == $nonce_protection ) {
			$value = 'no';
		}

		if ( current_user_can( 'manage_options' ) ) {
			$updated = update_option( 'tincanny_nonce_protection', $value );

			// Check if the user chose not to protect the content.
			if ( $value == 'no' ) {
				\uncanny_learndash_reporting\Boot::delete_protection_htaccess();
			}

			return $value;
		} else {
			return 'no permissions';
		}
	}

	/**
	 * @return bool
	 */
	public static function reset_tincan_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset();

				return true;
			}

		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function reset_quiz_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset_quiz();

				return true;
			}

		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function reset_bookmark_data() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$database = new \UCTINCAN\Database\Admin();
				$database->reset_bookmark_data();

				return true;
			}

		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function purge_experienced() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				// Run query
				global $wpdb;

				$q = "DELETE FROM {$wpdb->prefix}uotincan_reporting  WHERE verb = 'experienced'";
				$wpdb->query( $q );

				return true;
			}

		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function purge_answered() {

		if ( current_user_can( 'manage_options' ) ) {

			if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
				// Run query
				global $wpdb;

				$q = "DELETE FROM {$wpdb->prefix}uotincan_reporting  WHERE verb = 'answered'";
				$wpdb->query( $q );

				return true;
			}

		}

		return false;
	}

	/**
	 * @return array|null
	 */
	private static function get_administrators_group_ids() {

		if ( ! self::$group_leaders_group_ids ) {

			$current_user_ID = get_current_user_id();

			if ( 'group_leader' === self::get_user_role() ) {
				self::$group_leaders_group_ids = learndash_get_administrators_group_ids( $current_user_ID );
			}

		}

		return self::$group_leaders_group_ids;

	}

	/**
	 * @return string|null
	 */
	private static function get_user_role() {

		if ( ! self::$user_role ) {

			// Default value
			self::$user_role = 'unknown';

			$current_user_ID = get_current_user_id();

			// is it an administrator
			if ( current_user_can( 'manage_options' ) ) {

				//Set user's role
				self::$user_role = 'administrator';
			} // Is it a group leader
			elseif ( is_group_leader( $current_user_ID ) ) {

				//Set user's role
				self::$user_role = 'group_leader';
			}

		}

		return self::$user_role;
	}

	/**
	 * @return bool|int
	 */
	private static function create_temp_table() {
		global $wpdb;
		$tbl = $wpdb->prefix . self::$temp_table;
		$q   = "CREATE TABLE IF NOT EXISTS `$tbl` (
    `user_id` bigint(20) NOT NULL,
   UNIQUE KEY `user_id` (`user_id`))
             ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $wpdb->query( $q );
	}

	/**
	 * @return bool|int
	 */
	private static function drop_temp_table() {
		global $wpdb;
		$tbl = $wpdb->prefix . self::$temp_table;
		$q   = "DROP TABLE IF EXISTS `$tbl`;";

		return $wpdb->query( $q );
	}

	/**
	 * @param $user_ids
	 */
	private static function insert_user_ids_in_temp_table( $user_ids ) {
		global $wpdb;
		$chunk = 1001;
		//$wpdb->query( "TRUNCATE TABLE $wpdb->prefix" . self::$temp_table );
		self::drop_temp_table();
		self::create_temp_table();
		if ( count( $user_ids ) > $chunk ) {
			$chunks = array_chunk( $user_ids, $chunk );
			if ( $chunks ) {
				$add_user_ids = array();
				foreach ( $chunks as $chunk ) {
					foreach ( $chunk as $user_id ) {
						$add_user_ids[] = "(" . absint( $user_id ) . ")";
					}
					if ( $add_user_ids ) {
						$wpdb->query( "INSERT IGNORE INTO $wpdb->prefix" . self::$temp_table . " (`user_id`) VALUES " . implode( ',', $add_user_ids ) );
					}
					$add_user_ids = array();
				}
			}
		} else {
			$add_user_ids = array();
			if ( $user_ids ) {
				foreach ( $user_ids as $user_id ) {
					$add_user_ids[] = "(" . absint( $user_id ) . ")";
				}
				if ( $add_user_ids ) {
					$wpdb->query( "INSERT IGNORE INTO $wpdb->prefix" . self::$temp_table . " (`user_id`) VALUES " . implode( ',', $add_user_ids ) );
				}
			}
		}
	}
}
