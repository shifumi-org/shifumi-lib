<?php

namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class QuizModuleReports
 * @package uncanny_learndash_reporting
 */
class QuizModuleReports extends Config {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_shortcode( 'uo_individual_quiz_report', array( $this, 'user_quiz_report' ) );
		add_shortcode( 'uo_group_quiz_report', array( $this, 'group_quiz_report' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'report_scripts' ) );

		//register api class
		add_action( 'rest_api_init', array( $this, 'uo_api' ) );
	}

	/*
	 * Register rest api endpoints
	 *
	 */
	/**
	 *
	 */
	public function uo_api() {

		register_rest_route( 'tincanny/v1', '/get_user_course_data/', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'get_user_course_data' ),
			'permission_callback' => array( __CLASS__, 'tincanny_permissions' ),
		) );

		register_rest_route( 'tincanny/v1', '/get_user_essay/', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'get_user_essay' ),
			'permission_callback' => array( __CLASS__, 'tincanny_essay_permissions' ),
		) );
	}

	/**
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
	 * @return bool|\WP_Error
	 */
	public static function tincanny_essay_permissions() {

		if ( is_user_logged_in() ) {
			return true;
		}

		return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have the capability to view tincanny data.', 'uncanny-learndash-reporting' ) );
	}

	/*
	 * Get group of courses related to a LD Group
	 */
	/**
	 * @return array|string|void
	 */
	public static function get_user_essay() {

		$data                     = $_POST;
		$return_object            = array();
		$return_object['success'] = false;

		// Make sure we receive data
		if ( ! isset( $data['essayUrl'] ) ) {
			$return_object['message']  = __( 'Essay Link Not Found', 'uncanny-learndash-reporting' );
			$return_object['essayUrl'] = $data['essayUrl'];

			return $return_object;
		}

		// Make sure we receive data
		if ( ! isset( $data['userId'] ) ) {
			$return_object['message']  = __( 'User ID Not Found', 'uncanny-learndash-reporting' );
			$return_object['essayUrl'] = $data['essayUrl'];

			return $return_object;
		}

		// validate inputs
		$essay_url     = esc_url( ( $data['essayUrl'] ) );
		$essay_user_id = absint( ( $data['userId'] ) );

		$user_id = get_current_user_id();

		$can_view_file = false;

		$slug = explode( 'essay/', $essay_url );//substr( $essay_url, strrpos( $essay_url, '/' ) + 1 );
		$slug = str_replace( '/', '', $slug[1] );


		global $wpdb;

		$q = "SELECT * FROM $wpdb->posts WHERE post_author = $essay_user_id AND post_name = '$slug'";

		$essay_post = $wpdb->get_row( $q );

		if ( empty( $essay_post ) ) {
			return __( 'Essay answer not found.', 'uncanny-learndash-reporting' );
		}

		if ( ( learndash_is_admin_user( $user_id ) ) || ( $essay_post->post_author == $essay_user_id ) ) {
			$can_view_file = true;
		} else if ( ( learndash_is_group_leader_user( $user_id ) ) && ( learndash_is_group_leader_of_user( $user_id, $essay_user_id ) ) ) {

			$can_view_file = true;
		}


		if ( $can_view_file == true ) {
			$uploaded_file = get_post_meta( $essay_post->ID, 'upload', true );

			if ( ( ! empty( $uploaded_file ) ) && ( ! strstr( $essay_post->post_content, $uploaded_file ) ) ) {
				$essay_post->post_content .= apply_filters( 'learndash-quiz-essay-upload-link', '<p><a target="_blank" href="' . $uploaded_file . '">' . __( 'View uploaded file', 'learndash' ) . '</a></p>' );
			}

			return $essay_post->post_content;

		} else {
			return __( 'Sorry, Access Denied', 'uncanny-learndash-reporting' );
		}

	}

	/**
	 * @return \WP_REST_Response
	 */
	public function get_user_course_data() {
		$course_label = \LearnDash_Custom_Label::get_label( 'course' );

		if ( isset( $_POST['groupId'] ) && absint( $_POST['groupId'] ) ) {
			$group_id = absint( $_POST['groupId'] );


		} else {
			$return['message'] = __( 'Group ID not found.', 'uncanny-learndash-reporting' );
			$return['success'] = false;
			$return['post']    = $_POST;
			$response          = new \WP_REST_Response( $return, 200 );

			return $response;
		}

		if ( isset( $_POST['courseId'] ) && absint( $_POST['courseId'] ) ) {
			$course_id = absint( $_POST['courseId'] );
		} else {
			$return['message'] = sprintf( _x( '%s ID not found.', '%s is the "Course" label', 'uncanny-learndash-reporting' ), $course_label );
			$return['success'] = false;
			$return['post']    = $_POST;
			$response          = new \WP_REST_Response( $return, 200 );

			return $response;
		}

		$data = array();

		$group_users = learndash_get_groups_users( $group_id );

		$user_names = array();

		$group_user_IDs = array();

		$users_meta = $this->get_users_with_meta( array( 'first_name', 'last_name' ) );

		foreach ( $users_meta['results'] as $user ) {

			$first_name = $user['first_name'];
			$last_name  = $user['last_name'];

			if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
				$name = $user['first_name'] . ' ' . $user['last_name'];
			} elseif ( ! empty( $first_name ) ) {
				$name = $user['first_name'];
			} else {
				$name = $user['display_name'];;
			}

			$user_names[ $user['ID'] ] = $name;
		}

		// get all tincanny h5p modules for all user in group ID for course ID

		foreach ( $group_users as $user_data ) {

			$user_id = absint( $user_data->data->ID );

			$group_user_IDs[] = $user_id;

			$user_courses = array( $course_id );

			$usermeta           = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quiz_attempts_meta = empty( $usermeta ) ? false : $usermeta;
			$quiz_attempts      = array();

			if ( ! empty( $quiz_attempts_meta ) ) {

				foreach ( $quiz_attempts_meta as $quiz_attempt ) {
					$c                          = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
					$quiz_attempt['post']       = get_post( $quiz_attempt['quiz'] );
					$quiz_attempt['percentage'] = ! empty( $quiz_attempt['percentage'] ) ? $quiz_attempt['percentage'] : ( ! empty( $quiz_attempt['count'] ) ? $quiz_attempt['score'] * 100 / $quiz_attempt['count'] : 0 );

					if ( $user_id == get_current_user_id() && ! empty( $c['certificateLink'] ) && ( ( isset( $quiz_attempt['percentage'] ) && $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100 ) ) ) {
						$quiz_attempt['certificate'] = $c;
					}

					$course_id = absint( $quiz_attempt['course'] );

					$quiz_attempts[ $course_id ][] = $quiz_attempt;

				}
			}

			$args = array(
				'numberposts' => 500,
				'post_type'   => 'sfwd-courses',
			);

			$all_courses = get_posts( $args );
			$courses     = array();

			foreach ( $all_courses as $course ) {
				$courses[ $course->ID ] = $course;
			}

			if ( ! empty( $user_courses ) ) {

				foreach ( $user_courses as $course_id ) {

					if ( ! empty( $quiz_attempts[ $course_id ] ) ) {

						foreach ( $quiz_attempts[ $course_id ] as $k => $quiz_attempt ) {

							$score      = '';
							$modal_link = '';


							$quiz_title = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];

							if ( ! empty( $quiz_title ) ) {

								if ( ( isset( $quiz_attempt['has_graded'] ) ) && ( true === $quiz_attempt['has_graded'] ) && ( true === \LD_QuizPro::quiz_attempt_has_ungraded_question( $quiz_attempt ) ) ) {
									$score = _x( 'Pending', 'Pending Certificate Status Label', 'learndash' );
								} else {
									$score = round( $quiz_attempt['percentage'], 2 ) . '%';
								}
							}

							if ( ( $user_id == get_current_user_id() ) || ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) {

								if ( ( ! isset( $quiz_attempt['statistic_ref_id'] ) ) || ( empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
									$quiz_attempt['statistic_ref_id'] = learndash_get_quiz_statistics_ref_for_quiz_attempt( $user_id, $quiz_attempt );
								}

								if ( ( isset( $quiz_attempt['statistic_ref_id'] ) ) && ( ! empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
									/**
									 * @since 2.3
									 * See snippet on use of this filter https://bitbucket.org/snippets/learndash/5o78q
									 */
									if ( apply_filters( 'show_user_profile_quiz_statistics',
										get_post_meta( $quiz_attempt['post']->ID, '_viewProfileStatistics', true ), $user_id, $quiz_attempt, basename( __FILE__ ) ) ) {

										$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $quiz_attempt['statistic_ref_id'] . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . $user_id . '"
									     data-quiz_id="' . $quiz_attempt['pro_quizid'] . '"
									     data-ref_id="' . intval( $quiz_attempt['statistic_ref_id'] ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#">';
										$modal_link .= '<div class="statistic_icon"></div>';
										$modal_link .= '</a>';
									}

								}
							}

							$date = learndash_adjust_date_time_display( $quiz_attempt['time'] );

							if ( isset( $data[ sanitize_title( $course_id . $quiz_title . $user_id ) ] ) ) {
								if ( absint( $data[ sanitize_title( $course_id . $quiz_title . $user_id ) ]->quiz_score ) >= absint( $score ) ) {
									continue;
								}
							}

							$data[ sanitize_title( $course_id . $quiz_title . $user_id ) ] = (object) array(
								'user_name'  => $user_names[ (int) $user_id ],
								'user_id'    => $user_id,
								'quiz_name'  => $quiz_title,
								'quiz_score' => $score,
								'quiz_modal' => $modal_link,
								'quiz_date'  => [
									'display'   => $date,
									'timestamp' => $quiz_attempt['time'],
								],
							);

						}
					}
				}
			}
		}

		// get all quiz results for all users in group for the course ID

		global $wpdb;

		$user_ids_string = join( "','", $group_user_IDs );

		$user_id_query = '';
		if ( ! empty( $user_ids_string ) ) {
			$user_id_query = "AND user_id IN ('$user_ids_string')";
		}

		// Get all the H5P modules that have been completed
		$q = "SELECT user_id, module_name, module, result, minimum, xstored
		FROM {$wpdb->prefix}uotincan_reporting
		WHERE course_id = $course_id
		$user_id_query
		AND verb IN ( 'failed','passed', 'completed' )
		AND `result` IS NOT NULL";

		$tc_quizzes = $wpdb->get_results( $q );

		$display_format = apply_filters( 'learndash_date_time_formats', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		// Loop through H5P completed and answered modules
		foreach ( $tc_quizzes as $_quiz ) {

			// Sanity check that the result is valid
			if ( 0 === absint( $_quiz->result ) ) {
				$score = '';
			} else {
				$score = absint( $_quiz->result ) . '%';
			}

			$unique_module_path = preg_replace( '/[^ \w]+/', '', $_quiz->module );

			if ( isset( $data[ $unique_module_path . $_quiz->user_id ] ) ) {
				// Only overwrite data on duplicate H5P modules if the score if high
				if ( absint( $data[ $unique_module_path . $_quiz->user_id ]->quiz_score ) >= absint( $score ) ) {
					continue;
				}
			}

			if ( 0 !== strtotime( $_quiz->xstored ) ) {
				$date_time_display = date_i18n( $display_format, strtotime( $_quiz->xstored ) );
			} else {
				$date_time_display = '';
			}

			$data[ $unique_module_path . $_quiz->user_id ] = (object) array(
				'user_name'  => $user_names[ (int) $_quiz->user_id ],
				'user_id'    => (int) $_quiz->user_id,
				'quiz_name'  => $_quiz->module_name,
				'quiz_score' => $score,
				'quiz_modal' => '',
				'quiz_date'  => [
					'display'   => $date_time_display,
					'timestamp' => strtotime( $_quiz->xstored ),
				],
			);

		}

		$rows = array();
		foreach ( $data as $row ) {
			$rows[] = $row;
		}

		$return['message'] = '';
		$return['success'] = true;
		$return['data']    = $rows;
		//$return['$tc_quizzes']   = $tc_quizzes;
		//$return['$q'] = $q;
		//$return['$data'] = $data;


		$response = new \WP_REST_Response( $return, 200 );

		return $response;

	}

	/**
	 * @return string
	 */
	public function group_quiz_report( $atts ) {

		// Is the user logged in
		if ( ! is_user_logged_in() ) {
			return __( 'Please Log in to view the report.', 'uncanny-learndash-reporting' );
		}

		$atts = shortcode_atts( array(
			'user_report_url' => '',
		), $atts, 'group_quiz_report' );

		$user_report_url = $atts['user_report_url'];

		$site_url = get_bloginfo( 'url' );

		if ( false === strstr( $user_report_url, $site_url ) ) {
			// support relative urls that start with or without a slash at the beginning.
			$user_report_url = implode( '/', array_filter( explode( '/', $user_report_url ) ) );
			$user_report_url = trailingslashit( $site_url ) . $user_report_url;
		}

		$current_user_id = get_current_user_id();

		// Check if user is an administrator
		if ( current_user_can( 'manage_options' ) ) {

			// if user id is set in url the lets view a that group leaders report
			if ( isset( $_GET['user_id'] ) ) {

				$current_user_id = absint( $_GET['user_id'] );

				$current_user = get_user_by( 'ID', $current_user_id );

				if ( ! $current_user ) {
					return __( 'Invalid user set in address bar.', 'uncanny-learndash-reporting' );
				}

			}

		} else {
			$current_user = get_user_by( 'ID', $current_user_id );
		}

		if ( current_user_can( 'manage_options' ) ) {
			$group_ids = array();
		} elseif ( learndash_is_group_leader_user( $current_user ) ) {
			$group_ids = learndash_get_administrators_group_ids( $current_user_id );
		} else {
			return __( 'This report is only accessible by group leader and administrators.', 'uncanny-learndash-reporting' );
		}

		if ( empty( $group_ids ) && ! current_user_can( 'manage_options' ) ) {
			return __( 'Group Leader has no groups assigned.', 'uncanny-learndash-reporting' );
		} else {
			$groups = get_posts(
				array(
					'numberposts' => 500,
					'include'     => $group_ids,
					'post_type'   => 'groups',
				)
			);
		}

		$drop_down = '<option value="">' . __( 'Select a Group', 'uncanny-learndash-reporting' ) . '</option>';

		$group_courses = array();
		foreach ( $groups as $group ) {
			$group_courses[ $group->ID ] = learndash_group_enrolled_courses( $group->ID );
			if ( ! empty( $group_courses[ $group->ID ] ) ) {
				$drop_down .= '<option value="' . $group->ID . '">' . $group->post_title . '</option>';
			}
		}

		$args = array(
			'numberposts' => 500,
			'post_type'   => 'sfwd-courses',
		);

		$all_courses = get_posts( $args );
		$courses     = array();

		foreach ( $all_courses as $course ) {
			$courses[ $course->ID ]['ID']         = $course->ID;
			$courses[ $course->ID ]['post_title'] = $course->post_title;
		}

		$course_label = \LearnDash_Custom_Label::get_label( 'course' );

		$course_drop = '<option value="">' . sprintf( _x( 'Select a %s', '%s is the "Course" label', 'uncanny-learndash-reporting' ), $course_label ) . '</option>';

		if ( 1 === count( $groups ) ) {
			foreach ( $group_courses[ $groups[0]->ID ] as $course ) {
				$course_drop .= '<option value="' . $courses[ (int) $course ]['ID'] . '">' . $courses[ (int) $course ]['post_title'] . '</option>';
			}
		}

		ob_start();

		global $learndash_assets_loaded;

		if ( isset( $learndash_assets_loaded['scripts']['learndash_template_script_js'] ) ) {

			wp_dequeue_script( 'learndash_template_script_js' );
			$filepath = $assets_url = Config::get_admin_js( 'learndash_template_script', '.js' );

			if ( ! empty( $filepath ) ) {

				wp_enqueue_script( 'learndash_template_script_js_2', $filepath, array( 'jquery' ), LEARNDASH_VERSION, true );

				$data                  = array();
				$data['ajaxurl']       = admin_url( 'admin-ajax.php' );
				$data['courses']       = $courses;
				$data['groupCourses']  = $group_courses;
				$data['userReportUrl'] = $user_report_url;
				$data                  = array(
					'json' => json_encode( $data ),
				);
				wp_localize_script( 'learndash_template_script_js_2', 'sfwd_data', $data );
			}
		}

		if ( defined( 'LEARNDASH_LMS_PLUGIN_URL' ) && function_exists( 'learndash_is_active_theme' ) && learndash_is_active_theme( 'ld30' ) ) {
			$icon = LEARNDASH_LMS_PLUGIN_URL . 'themes/legacy/templates/images/statistics-icon-small.png';
			?>
			<style>
				.statistic_icon {
					background: url(<?php echo $icon; ?>) no-repeat scroll 0 0 transparent;
					width: 23px;
					height: 23px;
					margin: auto;
					background-size: 23px;
				}
			</style>
			<?php
		}


		\LD_QuizPro::showModalWindow();
		?>

		<div class="uotc-report" id="uotc-group-report">
			<div class="uotc-report__header">
				<div class="uotc-report__selects">
					<?php if ( 1 === count( $groups ) ) { ?>

						<?php foreach ( $groups as $group ) { ?>

							<div id="uo-group-report-selections"
								 data-group-courses="<?php echo json_encode( $group_courses[ $group->ID ] ); ?>"
								 data-group-id="<?php echo $group->ID; ?>">
								<?php echo $group->post_title; ?>
							</div>

						<?php } ?>

					<?php } else { ?>

						<div id="uo-group-report-selections">
							<select id="uo-group-report-group">
								<?php echo $drop_down; ?>
							</select>
						</div>

					<?php } ?>

					<div id="uo-group-report-course-selections">
						<select id="uo-group-report-courses">
							<?php echo $course_drop; ?>
						</select>
					</div>
				</div>
				<div class="uotc-report__buttons">
					<div class="uotc-report__btn uotc-report__btn--csv">CSV</div>
					<!-- #uotc-group-report-table--hidden .buttons-csv -->
					<div class="uotc-report__btn uotc-report__btn--pdf">PDF</div>
					<!-- #uotc-group-report-table--hidden .buttons-pdf -->
				</div>
			</div>

			<div class="uotc-report__table">
				<table id="uotc-group-report-table" class="display responsive" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th><?php echo __( 'Name', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Activity', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Score', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Detailed Report', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Date', 'uncanny-learndash-reporting' ); ?></th>
					</tr>
					</thead>
				</table>
			</div>

			<div class="uotc-report__table uotc-report__table--hidden" id="uotc-group-report-container--hidden">
				<table id="uotc-group-report-table--hidden" class="display responsive" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th><?php echo __( 'Name', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Activity', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Score', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Date', 'uncanny-learndash-reporting' ); ?></th>
					</tr>
					</thead>
				</table>
			</div>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function user_quiz_report() {

		// Check if user is an administrator
		if ( current_user_can( 'manage_options' ) || learndash_is_group_leader_user() ) {

			// if user id is set in url the lets view a that group leaders report
			if ( isset( $_GET['user_id'] ) ) {

				$user_id = absint( $_GET['user_id'] );

				$user = get_user_by( 'ID', $user_id );

				// Check if non admin has user in a one of the groups they are administrating
				if ( ! current_user_can( 'manage_options' ) ) {
					$group_leaders_users = learndash_get_group_leader_groups_users();

					if ( ! in_array( $user_id, $group_leaders_users ) ) {
						return __( 'Invalid user set in address bar.', 'uncanny-learndash-reporting' );
					}

				}


				if ( ! $user ) {
					return __( 'Invalid user set in address bar.', 'uncanny-learndash-reporting' );
				}
			} else {
				$user_id = get_current_user_id();
			}

		} else {
			$user_id = get_current_user_id();
		}

		// Is the user logged in
		if ( ! $user_id ) {
			return __( 'Please Log in to view the report.', 'uncanny-learndash-reporting' );
		}

		ob_start();

		global $learndash_assets_loaded;

		$filepath = $assets_url = Config::get_admin_js( 'learndash_template_script', '.js' );

		if ( isset( $learndash_assets_loaded['scripts']['learndash_template_script_js'] ) ) {
			if ( ! empty( $filepath ) ) {

				wp_dequeue_script( 'learndash_template_script_js' );
				wp_enqueue_script( 'learndash_template_script_js_2', $filepath, array( 'jquery' ), LEARNDASH_VERSION, true );

				$user_info = get_userdata( $user_id );
				$nicename  = $user_info->user_nicename;

				$data              = array();
				$data['ajaxurl']   = admin_url( 'admin-ajax.php' );
				$data['user_name'] = $nicename;
				$data              = array( 'json' => json_encode( $data ) );
				wp_localize_script( 'learndash_template_script_js_2', 'sfwd_data', $data );
			}
		}

		if ( defined( 'LEARNDASH_LMS_PLUGIN_URL' ) && function_exists( 'learndash_is_active_theme' ) && learndash_is_active_theme( 'ld30' ) ) {
			$icon = LEARNDASH_LMS_PLUGIN_URL . 'themes/legacy/templates/images/statistics-icon-small.png';
			?>
			<style>
				.statistic_icon {
					background: url(<?php echo $icon; ?>) no-repeat scroll 0 0 transparent;
					width: 23px;
					height: 23px;
					margin: auto;
					background-size: 23px;
				}
			</style>
			<?php
		}


		\LD_QuizPro::showModalWindow();

		// maybe limit data if this isn't to complicated
		//$view_profile_statistics = apply_filters( 'show_user_profile_quiz_statistics', get_post_meta( $quiz_attempt['post']->ID, '_viewProfileStatistics', true ), $user_ID, $quiz_attempt, basename( __FILE__ ) );

		if ( learndash_is_group_leader_user() ) {
			$user_courses = learndash_get_group_leader_groups_courses();
		} else {
			$atts         = array(
				'user_id'            => $user_id,
				'order'              => 'DESC',
				'orderby'            => 'ID',
				'course_points_user' => 'yes',
			);
			$user_courses = ld_get_mycourses( $user_id, $atts );
		}

		$usermeta           = get_user_meta( $user_id, '_sfwd-quizzes', true );
		$quiz_attempts_meta = empty( $usermeta ) ? false : $usermeta;
		$quiz_attempts      = array();

		if ( ! empty( $quiz_attempts_meta ) ) {

			foreach ( $quiz_attempts_meta as $quiz_attempt ) {

				$c                          = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
				$quiz_attempt['post']       = get_post( $quiz_attempt['quiz'] );
				$quiz_attempt['percentage'] = ! empty( $quiz_attempt['percentage'] ) ? $quiz_attempt['percentage'] : ( ! empty( $quiz_attempt['count'] ) ? $quiz_attempt['score'] * 100 / $quiz_attempt['count'] : 0 );

				if ( $user_id == get_current_user_id() && ! empty( $c['certificateLink'] ) && ( ( isset( $quiz_attempt['percentage'] ) && $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100 ) ) ) {
					$quiz_attempt['certificate'] = $c;
				}

				$course_id = absint( $quiz_attempt['course'] );

				$quiz_attempts[ $course_id ][] = $quiz_attempt;
			}
		}

		$args = array(
			'numberposts' => 500,
			'post_type'   => 'sfwd-courses',
		);

		$all_courses = get_posts( $args );
		$courses     = array();

		foreach ( $all_courses as $course ) {
			$courses[ $course->ID ] = $course;
		}

		$data = array();

		if ( ! empty( $user_courses ) ) {

			foreach ( $user_courses as $course_id ) {

				if ( ! empty( $quiz_attempts[ $course_id ] ) ) {

					foreach ( $quiz_attempts[ $course_id ] as $k => $quiz_attempt ) {

						$score      = '';
						$modal_link = '';

						$quiz_title = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];

						if ( ! empty( $quiz_title ) ) {

							if ( ( isset( $quiz_attempt['has_graded'] ) ) && ( true === $quiz_attempt['has_graded'] ) && ( true === \LD_QuizPro::quiz_attempt_has_ungraded_question( $quiz_attempt ) ) ) {
								$score = _x( 'Pending', 'Pending Certificate Status Label', 'learndash' );
							} else {
								$score = round( $quiz_attempt['percentage'], 2 ) . '%';
							}

						}

						if ( ( $user_id == get_current_user_id() ) || ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) {

							if ( ( ! isset( $quiz_attempt['statistic_ref_id'] ) ) || ( empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
								$quiz_attempt['statistic_ref_id'] = learndash_get_quiz_statistics_ref_for_quiz_attempt( $user_id, $quiz_attempt );
							}

							if ( ( isset( $quiz_attempt['statistic_ref_id'] ) ) && ( ! empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
								/**
								 * @since 2.3
								 * See snippet on use of this filter https://bitbucket.org/snippets/learndash/5o78q
								 */
								if ( apply_filters( 'show_user_profile_quiz_statistics',
									get_post_meta( $quiz_attempt['post']->ID, '_viewProfileStatistics', true ), $user_id, $quiz_attempt, basename( __FILE__ ) ) ) {

									$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $quiz_attempt['statistic_ref_id'] . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . $user_id . '"
									     data-quiz_id="' . $quiz_attempt['pro_quizid'] . '"
									     data-ref_id="' . intval( $quiz_attempt['statistic_ref_id'] ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#">';
									$modal_link .= '<div class="statistic_icon"></div>';
									$modal_link .= '</a>';
								}

							}
						}

						$date = learndash_adjust_date_time_display( $quiz_attempt['time'] );

						if ( isset( $data[ sanitize_title( $course_id . $quiz_title ) ] ) ) {
							if ( absint( $data[ sanitize_title( $course_id . $quiz_title ) ]->quiz_score ) >= absint( $score ) ) {
								continue;
							}
						}

						$data[ sanitize_title( $course_id . $quiz_title ) ] = (object) array(
							'course_name' => $courses[ $course_id ]->post_title,
							'quiz_name'   => $quiz_title,
							'quiz_score'  => $score,
							'quiz_modal'  => $modal_link,
							'quiz_date'   => $date,
							'u_quiz_date' => $quiz_attempt['time'],
						);

					}
				}
			}
		}

		global $wpdb;

		$course_ids_string = implode( ',', $user_courses );

		$course_id_query = '';
		if ( ! empty( $course_ids_string ) ) {
			$course_id_query = "AND course_id IN ($course_ids_string)";
		}

		// Get all the H5P modules that have been completed
		$q = "SELECT course_id, module_name, module, result, minimum, xstored
		FROM {$wpdb->prefix}uotincan_reporting
		WHERE user_id = $user_id
		$course_id_query
		AND verb IN ( 'failed','passed', 'completed' )
		AND `result` IS NOT NULL";

		$tc_quizzes = $wpdb->get_results( $q );


		$display_format = apply_filters( 'learndash_date_time_formats', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		// Loop through H5P completed and answered modules
		foreach ( $tc_quizzes as $_quiz ) {

			// Sanity check that the result is valid
			if ( 0 === absint( $_quiz->result ) ) {
				$score = '';
			} else {
				$score = (int) $_quiz->result;
			}

			$unique_module_path = preg_replace( "/[^ \w]+/", "", $_quiz->module );

			if ( isset( $data[ $unique_module_path ] ) ) {
				// Only overwrite data on duplicate H5P modules if the score if high
				if ( absint( $data[ $unique_module_path ]->quiz_score ) >= absint( $score ) ) {
					continue;
				}
			}

			if ( 0 !== strtotime( $_quiz->xstored ) ) {
				$date_time_display = date_i18n( $display_format, strtotime( $_quiz->xstored ) );
			} else {
				$date_time_display = '';
			}


			$data[ $unique_module_path ] = (object) array(
				'course_name' => $courses[ $_quiz->course_id ]->post_title,
				'quiz_name'   => $_quiz->module_name,
				'quiz_score'  => $score . '%',
				'quiz_modal'  => '',
				'quiz_date'   => $date_time_display,
				'u_quiz_date' => strtotime( $_quiz->xstored ),
			);
		}
		/**
		 * Ticket 27891. Show non-course associated quizzes
		 * @since 4.0
		 */
		$_activity_tbl      = \LDLMS_DB::get_table_name( 'user_activity' );
		$_activity_meta_tbl = \LDLMS_DB::get_table_name( 'user_activity_meta' );
		$non_associated     = $wpdb->get_results( "SELECT * FROM `$_activity_tbl` WHERE `user_id` = $user_id AND `course_id` = 0" );
		if ( $non_associated ) {
			foreach ( $non_associated as $n_a ) {
				$activity_id       = $n_a->activity_id;
				$_quiz             = $n_a->post_id;
				$_statistic_ref_id = $wpdb->get_var( "SELECT activity_meta_value FROM `$_activity_meta_tbl` WHERE activity_meta_key = 'statistic_ref_id' AND activity_id = $activity_id" );
				$pro_quizid        = $wpdb->get_var( "SELECT activity_meta_value FROM `$_activity_meta_tbl` WHERE activity_meta_key = 'pro_quizid' AND activity_id = $activity_id" );
				$percentage        = $wpdb->get_var( "SELECT activity_meta_value FROM `$_activity_meta_tbl` WHERE activity_meta_key = 'percentage' AND activity_id = $activity_id" );
				$modal_link        = '';
				if ( ! empty( $_statistic_ref_id ) ) {
					$modal_link = '<a class="user_statistic"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $_statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
									     data-user_id="' . $user_id . '"
									     data-quiz_id="' . $pro_quizid . '"
									     data-ref_id="' . intval( $_statistic_ref_id ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#">';
					$modal_link .= '<div class="statistic_icon"></div>';
					$modal_link .= '</a>';
				}
				if ( ! empty( $n_a->activity_completed ) ) {
					$data[] = (object) array(
						'course_name' => 'N/A',
						'quiz_name'   => get_the_title( $_quiz ),
						'quiz_score'  => ! empty( $percentage ) ? "$percentage%" : 'N/A',
						'quiz_modal'  => $modal_link,
						'quiz_date'   => learndash_adjust_date_time_display( $n_a->activity_completed, $display_format ),
						'u_quiz_date' => $n_a->activity_completed,
					);
				}
			}
		}

		$user_info    = get_userdata( $user_id );
		$display_name = $user_info->display_name;
		$first_name   = $user_info->first_name;
		$last_name    = $user_info->last_name;

		if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
			$name = $first_name . ' ' . $last_name;
		} elseif ( ! empty( $first_name ) ) {
			$name = $first_name;
		} else {
			$name = $display_name;
		}

		$course_label = \LearnDash_Custom_Label::get_label( 'course' );
		?>

		<h2><?php echo sprintf( __( 'Activity Report for %s', 'uncanny-learndash-reporting' ), $name ); ?></h2>
		<div class="uotc-report" id="uotc-user-report">
			<div class="uotc-report__header">
				<div class="uotc-report__selects"></div>
				<div class="uotc-report__buttons">
					<div
						class="uotc-report__btn uotc-report__btn--csv"><?php echo __( 'CSV', 'uncanny-learndash-reporting' ); ?></div>
					<div
						class="uotc-report__btn uotc-report__btn--pdf"><?php echo __( 'PDF', 'uncanny-learndash-reporting' ); ?></div>
				</div>
			</div>

			<div class="uotc-report__table">
				<table id="uo-quiz-report-table" class="display responsive no-wrap" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th><?php echo $course_label; ?></th>
						<th><?php echo __( 'Activity', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Score', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Detailed Report', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Date', 'uncanny-learndash-reporting' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $data as $quiz ) { ?>
						<tr>
							<td><?php echo $quiz->course_name; ?></td>
							<td><?php echo $quiz->quiz_name; ?></td>
							<td><?php echo $quiz->quiz_score; ?></td>
							<td><?php echo $quiz->quiz_modal; ?></td>
							<td data-order="<?php echo $quiz->u_quiz_date; ?>"><?php echo $quiz->quiz_date; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>

			<div class="uotc-report__table uotc-report__table--hidden" id="uotc-user-report-container--hidden">
				<table id="uo-quiz-report-table-hidden" class="display responsive no-wrap" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th><?php echo $course_label; ?></th>
						<th><?php echo __( 'Activity', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Score', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Date', 'uncanny-learndash-reporting' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach (
						$data

						as $quiz
					) { ?>
						<tr>
							<td><?php echo $quiz->course_name; ?></td>
							<td><?php echo $quiz->quiz_name; ?></td>
							<td><?php echo $quiz->quiz_score; ?></td>
							<td data-order="<?php echo $quiz->u_quiz_date; ?>"><?php echo $quiz->quiz_date; ?></td>
						</tr>
						<?php

					} ?>
					</tbody>
					<tfoot>
					<tr>
						<th><?php echo $course_label; ?></th>
						<th><?php echo __( 'Activity', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Score', 'uncanny-learndash-reporting' ); ?></th>
						<th><?php echo __( 'Date', 'uncanny-learndash-reporting' ); ?></th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<?php

		return ob_get_clean();

	}

	/**
	 *
	 */
	public function report_scripts() {
		global $post;

		global $post;

		$block_is_on_page = false;
		if ( is_a( $post, 'WP_Post' ) && function_exists( 'parse_blocks' ) ) {
			$blocks = parse_blocks( $post->post_content );
			foreach ( $blocks as $block ) {
				if ( 'tincanny/user-quiz-report' === $block['blockName'] || 'tincanny/group-quiz-report' === $block['blockName'] ) {
					$block_is_on_page = true;
				}
			}
		}

		if ( is_a( $post, 'WP_Post' ) &&
			 (
				 has_shortcode( $post->post_content, 'uo_individual_quiz_report' ) ||
				 has_shortcode( $post->post_content, 'uo_group_quiz_report' ) ||
				 $block_is_on_page
			 )
		) {

			// Front End Questionnaire JS
			wp_register_script( 'quiz-module-reports-js', self::get_admin_js( 'quiz-module-reports', '.js' ), array( 'jquery' ), '1.0', true );

			// Course label
			$course_label  = \LearnDash_Custom_Label::get_label( 'course' );
			$courses_label = \LearnDash_Custom_Label::get_label( 'courses' );

			// Attach API data to custom-toolkit-js
			$api_setup = array(
				'root'        => esc_url_raw( rest_url() . 'tincanny/v1/' ),
				'nonce'       => \wp_create_nonce( 'wp_rest' ),
				'currentUser' => get_current_user_id(),
				'i18n'        => [
					'emptyTable'                       => __( 'No data available in table', 'uncanny-learndash-reporting' ),
					'info'                             => sprintf( _x( 'Showing %1$s to %2$s of %3$s entries', '%1$s is the start number, %2$s is the end number, and %3$s is the total number of entries', 'uncanny-learndash-reporting' ), '_START_', '_END_', '_TOTAL_' ),
					'infoEmpty'                        => __( 'Showing 0 to 0 of 0 entries', 'uncanny-learndash-reporting' ),
					'infoFiltered'                     => sprintf( _x( '(filtered from %s total entries)', '%s is a number', 'uncanny-learndash-reporting' ), '_MAX_' ),
					'loadingRecords'                   => __( 'Loading...', 'uncanny-learndash-reporting' ),
					'processing'                       => __( 'Processing...', 'uncanny-learndash-reporting' ),
					'searchPlaceholder'                => __( 'Search...', 'uncanny-learndash-reporting' ),
					'zeroRecords'                      => __( 'No matching records found', 'uncanny-learndash-reporting' ),
					'paginate'                         => [
						'first'    => __( 'First', 'uncanny-learndash-reporting' ),
						'last'     => __( 'Last', 'uncanny-learndash-reporting' ),
						'next'     => __( 'Next', 'uncanny-learndash-reporting' ),
						'previous' => __( 'Previous', 'uncanny-learndash-reporting' ),
					],
					'aria'                             => [
						'sortAscending'  => sprintf( ': %s', __( 'activate to sort column ascending', 'uncanny-learndash-reporting' ) ),
						'sortDescending' => sprintf( ': %s', __( 'activate to sort column descending', 'uncanny-learndash-reporting' ) ),
					],
					'selectStudentDropdownPlaceholder' => __( 'Select a student', 'uncanny-learndash-reporting' ),
					'selectStudentDropdownNoStudents'  => __( 'No students available', 'uncanny-learndash-reporting' ),
					'selectCourseDropdownPlaceholder'  => sprintf( _x( 'Select a %s', '%s is the "Course" label', 'uncanny-learndash-reporting' ), $course_label ),
					'selectCourseDropdownNoCourses'    => sprintf( _x( 'No %s available', '%s is the "Courses" label', 'uncanny-learndash-reporting' ), $courses_label ),
				],
			);

			wp_localize_script( 'quiz-module-reports-js', 'groupLeaderCourseReportSetup', $api_setup );

			wp_enqueue_script( 'quiz-module-reports-js' );

			wp_enqueue_script( 'datatables-script', self::get_admin_js( 'jquery.dataTables', '.min.js' ), array( 'jquery' ), false, true );
			wp_enqueue_script( 'responsive-datatables-script', self::get_admin_js( 'dataTables.responsive', '.min.js' ), array(
				'jquery',
				'datatables-script',
			), false, true );
			wp_enqueue_script( 'buttons-datatables-script', self::get_admin_js( 'dataTables.buttons', '.min.js' ), array(
				'jquery',
				'datatables-script',
			), false, true );
			wp_enqueue_script( 'buttons-html-datatables-script', self::get_admin_js( 'buttons.html5', '.min.js' ), array(
				'jquery',
				'datatables-script',
				'buttons-datatables-script',
			), false, true );
			wp_enqueue_script( 'buttons-flash-datatables-script', self::get_admin_js( 'buttons.flash', '.min.js' ), array(
				'jquery',
				'datatables-script',
				'buttons-datatables-script',
			), false, true );

			wp_enqueue_script( 'pdfmake', self::get_admin_js( 'pdfmake', '.min.js' ), array(
				'jquery',
				'datatables-script',
				'buttons-datatables-script',
			), false, true );

			wp_enqueue_script( 'jszip', self::get_admin_js( 'jszip', '.min.js' ), array(
				'jquery',
				'datatables-script',
				'buttons-datatables-script',
			), false, true );

			wp_enqueue_script( 'vfs_fonts', self::get_admin_js( 'vfs_fonts', '.js' ), array(
				'jquery',
				'datatables-script',
				'buttons-datatables-script',
			), false, true );

			wp_enqueue_style( 'quiz-module-reports-css', self::get_admin_css( 'quiz-module-reports.css' ) );
			wp_enqueue_style( 'datatables-styles', self::get_admin_css( 'datatables.min.css' ) );
			wp_enqueue_style( 'datatables-responsive-styles', self::get_admin_css( 'responsive.dataTables.min.css' ) );
			wp_enqueue_style( 'buttons-dataTables-min', self::get_admin_css( 'buttons.dataTables.min.css' ) );

		}
	}

	/**
	 * @param array $exact_meta_keys
	 * @param array $fuzzy_meta_keys
	 * @param array $included_user_ids
	 *
	 * @return array
	 */
	public function get_users_with_meta( $exact_meta_keys = array(), $fuzzy_meta_keys = array(), $included_user_ids = array() ) {

		global $wpdb;

		// Collect all possible meta_key values
		$keys = $wpdb->get_col( "SELECT distinct meta_key FROM $wpdb->usermeta" );

		//then prepare the meta keys query as fields which we'll join to the user table fields
		$meta_columns = '';
		foreach ( $keys as $key ) {

			// Collect exact matches
			if ( ! empty( $exact_meta_keys ) ) {
				if ( in_array( $key, $exact_meta_keys ) ) {
					$meta_columns .= " MAX(CASE WHEN um.meta_key = '$key' THEN um.meta_value ELSE '' END) AS '$key', \n";
					continue;
				}
			}

			// Collect fuzzy matches ... ex. "example" would match "example_947"
			// ToDo allow for SQL "LIKE" syntax ... ex "example%947"
			// ToDo allow for regex
			if ( ! empty( $fuzzy_meta_keys ) ) {
				foreach ( $fuzzy_meta_keys as $fuzzy_key ) {
					if ( false !== strpos( $key, $fuzzy_key ) ) {
						$meta_columns .= " MAX(CASE WHEN um.meta_key = '$key' THEN um.meta_value ELSE NULL END) AS '$key', \n";
					}
				}

			}


		}

		$user_ids = '';

		if ( ! empty( $include_user_ids ) ) {
			$user_ids = 'WHERE u.ID IN (\'' . implode( ',', $include_user_ids ) . '\')';
		}
		//then write the main query with all of the regular fields and use a simple left join on user users.ID and usermeta.user_id
		$query = "
SELECT
    u.ID,
    u.user_login,
    u.user_pass,
    u.user_nicename,
    u.user_email,
    u.user_url,
    u.user_registered,
    u.user_activation_key,
    u.user_status,
    u.display_name,
    " . rtrim( $meta_columns, ", \n" ) . "
FROM
    $wpdb->users u
LEFT JOIN
    $wpdb->usermeta um ON (um.user_id = u.ID)
$user_ids
GROUP BY
    u.ID";

		$users = $wpdb->get_results( $query, ARRAY_A );

		return array(
			'query'   => $query,
			'results' => $users,
		);


	}

}
