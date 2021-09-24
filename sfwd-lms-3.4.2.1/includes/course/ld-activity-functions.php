<?php
/**
 * Activity Functions
 *
 * @since 3.4.0
 *
 * @package LearnDash\Activity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Updates the user activity.
 *
 * @since 2.3.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $args {
 *    An array of user activity arguments. Default empty array.
 *
 *    @type int    $activity_id        Optional. Activity ID. Default 0.
 *    @type int    $course_id          Optional. Course ID. Default 0.
 *    @type int    $post_id            Optional. Post ID. Default 0.
 *    @type int    $user_id            Optional. User ID. Default 0.
 *    @type string $activity_type      Optional. Type of the activity. Default empty.
 *    @type string $activity_status    Optional. The status of the activity. Default empty.
 *    @type string $activity_started   Optional. The timestamp of when the activity started. Default empty.
 *    @type string $activity_completed Optional. The timestamp of when the activity got completed. Default empty.
 *    @type string $activity_updated   Optional. The timestamp of when the activity was last updated. Default empty.
 *    @type string $activity_action    Optional. The action of the activity. Value can be 'update', 'insert', or 'delete'. Default 'update'.
 *    @type string $activity_meta      Optional. The activity meta. Default empty.
 * }
 *
 * @return int The ID of the updated activity.
 */
function learndash_update_user_activity( $args = array() ) {

	global $wpdb;

	$default_args = array(
		// Can be passed in if we are updating a specific existing activity row.
		'activity_id'        => 0,

		// Required. This is the ID of the Course. Unique key part 1/4
		'course_id'          => 0,

		// Required. This is the ID of the Course, Lesson, Topic, Quiz item. Unique key part 2/4
		'post_id'            => 0,

		// Optional. Will use get_current_user_id() if left 0. Unique key part 3/4
		'user_id'            => 0,

		// Will be the token stats that described the status_times array (next argument) Can be most anything.
		// From 'course', 'lesson', 'topic', 'access' or 'expired'. Unique key part 4/4.
		'activity_type'      => '',

		// true if the lesson, topic, course, quiz is complete. False if not complete. null if not started
		'activity_status'    => '',

		// Should be the timstamp when the 'status' started
		'activity_started'   => '',

		// Should be the timstamp when the 'status' completed
		'activity_completed' => '',

		// Should be the timstamp when the activity record was last updated. Used as a sort column for ProPanel and other queries
		'activity_updated'   => '',

		// Flag to indicate what we are 'update', 'insert', 'delete'. The default action 'update' will cause this function
		// to check for an existing record to update (if found)
		'activity_action'    => 'update',
		'activity_meta'      => '',
	);

	$args = wp_parse_args( $args, $default_args );
	if ( empty( $args['activity_id'] ) ) {
		if ( ( empty( $args['post_id'] ) ) || ( empty( $args['activity_type'] ) ) ) {
			return;
		}
	}

	if ( empty( $args['user_id'] ) ) {
		// If we don't have a user_id passed via args
		if ( ! is_user_logged_in() ) {
			return; // If not logged in, abort
		}

		// Else use the logged in user ID as the args user_id
		$args['user_id'] = get_current_user_id();
	}

	// End of args processing. Finally after we have applied all the logic we go out for filters.
	/**
	 * Filters user activity arguments.
	 *
	 * @param array $args An array of user activity arguments.
	 */
	$args = apply_filters( 'learndash_update_user_activity_args', $args );
	if ( empty( $args ) ) {
		return;
	}

	$values_array = array(
		'user_id'       => $args['user_id'],
		'course_id'     => $args['course_id'],
		'post_id'       => $args['post_id'],
		'activity_type' => $args['activity_type'],
	);

	$types_array = array(
		'%d', // user_id
		'%d', // course_id
		'%d', // post_id
		'%s', // activity_type
	);

	if ( ( true === (bool) $args['activity_status'] ) || ( false === (bool) $args['activity_status'] ) ) {
		$values_array['activity_status'] = $args['activity_status'];
		$types_array[]                   = '%d';
	}

	if ( '' !== $args['activity_completed'] ) {
		$values_array['activity_completed'] = $args['activity_completed'];
		$types_array[]                      = '%d';
	}

	if ( '' !== $args['activity_started'] ) {
		$values_array['activity_started'] = $args['activity_started'];
		$types_array[]                    = '%d';
	}

	if ( '' !== $args['activity_updated'] ) {
		$values_array['activity_updated'] = $args['activity_updated'];
		$types_array[]                    = '%d';
	} else {
		if ( ( empty( $args['activity_started'] ) ) && ( empty( $args['activity_completed'] ) ) ) {
			if ( ! isset( $args['data_upgrade'] ) ) {
				$values_array['activity_updated'] = time();
				$types_array[]                    = '%d';
			}
		} elseif ( $args['activity_started'] == $args['activity_completed'] ) {
			$values_array['activity_updated'] = $args['activity_completed'];
			$types_array[]                    = '%d';
		} else {
			if ( $args['activity_started'] > $args['activity_completed'] ) {
				$values_array['activity_updated'] = $args['activity_started'];
				$types_array[]                    = '%d';
			} elseif ( $args['activity_completed'] > $args['activity_started'] ) {
				$values_array['activity_updated'] = $args['activity_completed'];
				$types_array[]                    = '%d';
			}
		}
	}

	$update_ret = false;

	if ( 'update' === $args['activity_action'] ) {

		if ( empty( $args['activity_id'] ) ) {
			$activity = learndash_get_user_activity( $args );
			if ( null !== $activity ) {

				$args['activity_id'] = $activity->activity_id;
			}
		}

		if ( ! empty( $args['activity_id'] ) ) {

			$update_values_array = $values_array;
			$update_types_array  = $types_array;

			$update_ret = $wpdb->update(
				LDLMS_DB::get_table_name( 'user_activity' ),
				$update_values_array,
				array(
					'activity_id' => $args['activity_id'],
				),
				$update_types_array,
				array(
					'%d', // activity_id
				)
			);

		} else {
			$args['activity_action'] = 'insert';
		}
	}

	if ( 'insert' === $args['activity_action'] ) {

		$values_array['activity_updated'] = time();
		$types_array[]                    = '%d';

		$insert_ret = $wpdb->insert(
			LDLMS_DB::get_table_name( 'user_activity' ),
			$values_array,
			$types_array
		);

		if ( false !== (bool) $insert_ret ) {
			$args['activity_id'] = $wpdb->insert_id;
		}
	}

	// Finally for the course we update the activity meta
	if ( ( ! empty( $args['activity_id'] ) ) && ( ! empty( $args['activity_meta'] ) ) ) {
		foreach ( $args['activity_meta'] as $meta_key => $meta_value ) {
			learndash_update_user_activity_meta( $args['activity_id'], $meta_key, $meta_value );
		}
	}

	/**
	 * Fires after updating user activity.
	 *
	 * @param array $args An array of activity arguments.
	 */
	do_action( 'learndash_update_user_activity', $args );

	return $args['activity_id'];
}

/**
 * Gets the user activity.
 *
 * @since 2.3.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $args {
 *    An array of user activity arguments. Default empty array.
 *
 *    @type int    $course_id     Optional. Course ID. Default 0.
 *    @type string $activity_type Type of the activity.
 * }
 *
 * @return object Activity object.
 */
function learndash_get_user_activity( $args = array() ) {
	global $wpdb;

	if ( ! isset( $args['course_id'] ) ) {
		$args['course_id'] = 0;
	}

	if ( 'quiz' === $args['activity_type'] ) {
		$data_settings_quizzes = learndash_data_upgrades_setting( 'user-meta-quizzes' );
		if ( version_compare( $data_settings_quizzes['version'], '2.5', '>=' ) ) {
			$sql_str = $wpdb->prepare( 'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity' ) ) . ' WHERE user_id=%d AND course_id=%d AND post_id=%d AND activity_type=%s AND activity_completed=%d LIMIT 1', $args['user_id'], $args['course_id'], $args['post_id'], $args['activity_type'], $args['activity_completed'] );
		} else {
			$sql_str = $wpdb->prepare( 'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity' ) ) . ' WHERE user_id=%d AND post_id=%d AND activity_type=%s AND activity_completed=%d LIMIT 1', $args['user_id'], $args['post_id'], $args['activity_type'], $args['activity_completed'] );
		}
	} else {
		$data_settings_courses = learndash_data_upgrades_setting( 'user-meta-courses' );
		if ( version_compare( $data_settings_courses['version'], '2.5', '>=' ) ) {
			$sql_str = $wpdb->prepare( 'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity' ) ) . ' WHERE user_id=%d AND course_id=%d AND post_id=%d AND activity_type=%s LIMIT 1', $args['user_id'], $args['course_id'], $args['post_id'], $args['activity_type'] );
		} else {
			$sql_str = $wpdb->prepare( 'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity' ) ) . ' WHERE user_id=%d AND post_id=%d AND activity_type=%s LIMIT 1', $args['user_id'], $args['post_id'], $args['activity_type'] );
		}
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql_str prepared in previous lines
	$activity = $wpdb->get_row( $sql_str );
	if ( $activity ) {
		if ( property_exists( $activity, 'activity_status' ) ) {
			if ( true === (bool) $activity->activity_status ) {
				$activity->activity_status = true;
			} elseif ( false === (bool) $activity->activity_status ) {
				$activity->activity_status = false;
			}
		}
	}

	/**
	 * Filter for learndash_get_user_activity.
	 *
	 * @since 3.2.3
	 * @param array $activity Array of activity.
	 * @param array $args     Array of args used for activity query.
	 */
	return apply_filters( 'learndash_get_user_activity', $activity, $args );
}


/**
 * Gets the user activity meta.
 *
 * @since 2.3.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int     $activity_id                     Optional. Activity ID. Default 0.
 * @param string  $activity_meta_key               Optional. The activity meta key to get. Default empty.
 * @param boolean $return_activity_meta_value_only Optional. Whether to return only activity meta value. Default true.
 *
 * @return object Activity meta object.
 */
function learndash_get_user_activity_meta( $activity_id = 0, $activity_meta_key = '', $return_activity_meta_value_only = true ) {

	global $wpdb;

	if ( empty( $activity_id ) ) {
		return;
	}

	if ( ! empty( $activity_meta_key ) ) {
		$activity_meta = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity_meta' ) ) . ' WHERE activity_id=%d AND activity_meta_key=%s',
				$activity_id,
				$activity_meta_key
			)
		);
		if ( ! empty( $activity_meta ) ) {
			if ( true === (bool) $return_activity_meta_value_only ) {
				if ( property_exists( $activity_meta, 'activity_meta_value' ) ) {
					return $activity_meta->activity_meta_value;
				}
			}
		}
		return $activity_meta;
	} else {
		// Here we return ALL meta for the given activity_id
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . esc_sql( LDLMS_DB::get_table_name( 'user_activity_meta' ) ) . ' WHERE activity_id=%d',
				$activity_id
			)
		);
	}
}


/**
 * Updates the user activity meta.
 *
 * @since 2.3.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int         $activity_id Optional. Activity ID. Default 0.
 * @param string      $meta_key    Optional. The activity meta key to get. Default empty.
 * @param string|null $meta_value  Optional. Activity meta value. Default null.
 */
function learndash_update_user_activity_meta( $activity_id = 0, $meta_key = '', $meta_value = null ) {
	global $wpdb;

	if ( ( empty( $activity_id ) ) || ( empty( $meta_key ) ) || ( null === $meta_value ) ) {
		return;
	}

	$activity = learndash_get_user_activity_meta( $activity_id, $meta_key, false );
	if ( null !== $activity ) {
		$wpdb->update(
			LDLMS_DB::get_table_name( 'user_activity_meta' ),
			array(
				'activity_id'         => $activity_id,
				'activity_meta_key'   => $meta_key,
				'activity_meta_value' => maybe_serialize( $meta_value ),
			),
			array(
				'activity_meta_id' => $activity->activity_meta_id,
			),
			array(
				'%d',   // activity_id
				'%s',   // meta_key
				'%s',    // meta_value
			),
			array(
				'%d',    // activity_meta_id
			)
		);

	} else {
		$wpdb->insert(
			LDLMS_DB::get_table_name( 'user_activity_meta' ),
			array(
				'activity_id'         => $activity_id,
				'activity_meta_key'   => $meta_key,
				'activity_meta_value' => maybe_serialize( $meta_value ),
			),
			array(
				'%d',   // activity_id
				'%s',   // meta_key
				'%s',    // meta_value
			)
		);
	}
}

/**
 * Deletes the user activity.
 *
 * @since 2.5.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $activity_id Optional. Activity ID. Default 0.
 */
function learndash_delete_user_activity( $activity_id = 0 ) {
	global $wpdb;

	if ( ! empty( $activity_id ) ) {
		$wpdb->delete(
			LDLMS_DB::get_table_name( 'user_activity' ),
			array( 'activity_id' => $activity_id ),
			array( '%d' )
		);

		$wpdb->delete(
			LDLMS_DB::get_table_name( 'user_activity_meta' ),
			array( 'activity_id' => $activity_id ),
			array( '%d' )
		);
	}
}
