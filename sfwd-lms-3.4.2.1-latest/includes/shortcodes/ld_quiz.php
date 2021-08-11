<?php
/**
 * LearnDash `[ld_quiz]` shortcode processing.
 *
 * @since 2.1.0
 *
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_quiz]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 * @global array   $learndash_shortcode_atts
 *
 * @param array   $atts {
 *    An array of shortcode attributes.
 *
 *    @type int  $course_id   Course ID. Default 0.
 *    @type int  $quiz_id     Quiz ID. Default 0.
 *    @type int  $quiz_pro_id Quiz pro ID. Default 0.
 * }
 * @param string  $content        The shortcode content. Default empty.
 * @param boolean $show_materials Whether to show quiz materials. Default false.
 *
 * @return string The `ld_quiz` shortcode output.
 */
function learndash_quiz_shortcode( $atts = array(), $content = '', $show_materials = false ) {

	global $learndash_shortcode_used, $learndash_shortcode_atts;

	$atts = shortcode_atts(
		array(
			'quiz_id'     => 0,
			'course_id'   => 0,
			'quiz_pro_id' => 0,
		),
		$atts
	);

	// Just to ensure compliance.
	$quiz_id     = $atts['quiz_id'] = absint( $atts['quiz_id'] );
	$course_id   = $atts['course_id'] = absint( $atts['course_id'] );
	$quiz_pro_id = $atts['quiz_pro_id'] = absint( $atts['quiz_pro_id'] );

	if ( empty( $atts['quiz_id'] ) ) {
		return $content;
	}
	$quiz_post = get_post( $atts['quiz_id'] );
	if ( ! is_a( $quiz_post, 'WP_Post' ) ) {
		return $content;
	}

	if ( empty( $course_id ) ) {
		if ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) !== 'yes' ) {
			$course_id = learndash_get_setting( $quiz_post, 'course' );
			$course_id = absint( $course_id );
			if ( ! empty( $course_id ) ) {
				$atts['course_id'] = $course_id;
			}
		}
	}
	$learndash_shortcode_atts['ld_quiz'] = $atts;
	// Clear out any previous 'LDAdvQuiz' data.
	if ( isset( $learndash_shortcode_atts['LDAdvQuiz'] ) ) {
		unset( $learndash_shortcode_atts['LDAdvQuiz'] );
	}
	$learndash_shortcode_used = true;

	$lesson_progression_enabled = false;
	if ( ! empty( $atts['course_id'] ) ) {
		$lesson_progression_enabled = learndash_lesson_progression_enabled( $atts['course_id'] );
	}

	$has_access = '';

	$user_id = get_current_user_id();

	$quiz_post = get_post( $atts['quiz_id'] );
	if ( $quiz_post instanceof WP_Post ) {
		$quiz_settings = learndash_get_setting( $atts['quiz_id'] );
		$meta          = SFWD_CPT_Instance::$instances['sfwd-quiz']->get_settings_values( 'sfwd-quiz' );

		$show_content   = ! ( ! empty( $lesson_progression_enabled ) && ! learndash_is_quiz_accessable( $user_id, $quiz_post, false, $course_id ) );
		$attempts_count = 0;
		$repeats        = ( isset( $quiz_settings['repeats'] ) ) ? trim( $quiz_settings['repeats'] ) : '';
		if ( '' === $repeats ) {
			if ( ! empty( $quiz_settings['quiz_pro'] ) ) {
				$quiz_mapper   = new WpProQuiz_Model_QuizMapper();
				$pro_quiz_edit = $quiz_mapper->fetch( $quiz_settings['quiz_pro'] );
				if ( ( $pro_quiz_edit ) && ( is_a( $pro_quiz_edit, 'WpProQuiz_Model_Quiz' ) ) ) {
					if ( ( isset( $atts['quiz_id'] ) ) && ( ! empty( $atts['quiz_id'] ) ) ) {
						$pro_quiz_edit->setPostId( $atts['quiz_id'] );
					}

					if ( $pro_quiz_edit->isQuizRunOnce() ) {
						$repeats = 0;
						// Update for later.
						learndash_update_setting( $quiz_post, 'repeats', $repeats );
					}
				}
			}
		}

		if ( '' !== $repeats ) {

			if ( $user_id ) {
				$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
				$usermeta = maybe_unserialize( $usermeta );

				if ( ! is_array( $usermeta ) ) {
					$usermeta = array();
				}

				if ( ! empty( $usermeta ) ) {
					foreach ( $usermeta as $k => $v ) {
						if ( ( intval( $v['quiz'] ) === $atts['quiz_id'] ) ) {
							if ( ! empty( $atts['course_id'] ) ) {
								if ( ( isset( $v['course'] ) ) && ( ! empty( $v['course'] ) ) && ( absint( $v['course'] ) === absint( $atts['course_id'] ) ) ) {
									// Count the number of time the student has taken the quiz where the course_id matches.
									$attempts_count++;
								}
							} elseif ( empty( $atts['course_id'] ) ) {
								if ( ( isset( $v['course'] ) ) && ( empty( $v['course'] ) ) && ( absint( $v['course'] ) === absint( $atts['course_id'] ) ) ) {
									// Count the number of time the student has taken the quiz where the course_id is zero.
									$attempts_count++;
								}
							}
						}
					}
				}
			} else {
				$quizMapper = new WpProQuiz_Model_QuizMapper();
				$quiz       = $quizMapper->fetch( $atts['quiz_pro_id'] );
				$cookieTime = $quiz->getQuizRunOnceTime();

				$_cookie = stripslashes_deep( $_COOKIE );

				if( isset( $_cookie['wpProQuiz_lock'] ) ) {
					$cookieJson = json_decode( $_cookie['wpProQuiz_lock'], true );
					if ( ( $cookieJson !== false ) && ( isset( $cookieJson[ $atts['quiz_pro_id'] ] ) ) ) {
						$cookie_quiz = $cookieJson[ $atts['quiz_pro_id'] ];
						$cookie_quiz = learndash_quiz_convert_lock_cookie( $cookie_quiz );
						if ( $cookie_quiz['time'] === $cookieTime ) {
							$attempts_count = absint( $cookie_quiz['count'] );
						}
					}
				}
			}
		}

		$attempts_left = ( ( '' === $repeats ) || ( absint( $repeats ) >= absint( $attempts_count ) ) );

		$bypass_course_limits_admin_users = learndash_can_user_bypass( $user_id, 'learndash_course_lesson_access_from', $course_id, $quiz_post );

		// For logged in users to allow an override filter.
		/** This filter is documented in includes/course/ld-course-progress.php */
		$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $course_id, $quiz_post );
		if ( ( true === $bypass_course_limits_admin_users ) && ( ! $attempts_left ) ) {
			$attempts_left = 1;
		}

		/**
		 * Filters the quiz attempts left for a user.
		 *
		 * See example https://developers.learndash.com/hook/learndash_quiz_attempts/
		 *
		 * @since 3.1.0
		 *
		 * @param boolean $attempts_left  Whether any quiz attempts left for a user or not.
		 * @param int     $attempts_count Number of Quiz attemplts already taken.
		 * @param int     $user_id        ID of User taking Quiz.
		 * @param int     $quiz_id        ID of Quiz being taken.
		 */
		$attempts_left = apply_filters( 'learndash_quiz_attempts', $attempts_left, absint( $attempts_count ), absint( $user_id ), absint( $quiz_post->ID ) );
		$attempts_left = absint( $attempts_left );

		if ( ! empty( $lesson_progression_enabled ) && ! learndash_is_quiz_accessable( $user_id, $quiz_post, false, $course_id ) ) {
			add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );
		}

		$materials = '';

		/**
		 * Filters quiz shortcode content access message.
		 *
		 * If not null, message display instead of quiz content.
		 *
		 * @since 2.1.0
		 *
		 * @param string $message The content access message.
		 * @param WP_Post $quiz_post    Quiz WP_Post object.
		 */
		$access_message = apply_filters( 'learndash_content_access', null, $quiz_post );
		if ( ! is_null( $access_message ) ) {
			$quiz_content = $access_message;
		} else {
			if ( true === $show_materials ) {
				if ( ! empty( $quiz_settings['quiz_materials'] ) ) {
					$materials = wp_specialchars_decode( $quiz_settings['quiz_materials'], ENT_QUOTES );
					if ( ! empty( $materials ) ) {
						$materials = do_shortcode( $materials );
					}
				}
			}

			$quiz_content = '';
			if ( ! empty( $quiz_settings['quiz_pro'] ) ) {
				$quiz_settings['lesson'] = 0;
				$quiz_settings['topic']  = 0;

				if ( ( ! empty( $course_id ) ) && ( ! empty( $quiz_id ) ) ) {
					$quiz_settings['topic'] = learndash_course_get_single_parent_step( $course_id, $quiz_id, learndash_get_post_type_slug( 'topic' ) );
					$quiz_settings['topic'] = absint( $quiz_settings['topic'] );

					$quiz_settings['lesson'] = learndash_course_get_single_parent_step( $course_id, $quiz_id, learndash_get_post_type_slug( 'lesson' ) );
					$quiz_settings['lesson'] = absint( $quiz_settings['lesson'] );
				}

				$quiz_content = wptexturize(
					do_shortcode( '[LDAdvQuiz ' . $quiz_settings['quiz_pro'] . ' quiz_pro_id="' . $quiz_settings['quiz_pro'] . '" quiz_id="' . $quiz_post->ID . '" course_id="' . $course_id . '" lesson_id="' . $quiz_settings['lesson'] . '" topic_id="' . $quiz_settings['topic'] . '"]' )
				);
			}

			/**
			 * Filters `ld_quiz` shortcode content.
			 *
			 * @since 2.1.0
			 *
			 * @param string  $quiz_content ld_quiz shortcode content.
			 * @param WP_Post $quiz_post    Quiz WP_Post object.
			 */
			$quiz_content = apply_filters( 'learndash_quiz_content', $quiz_content, $quiz_post );
		}

		$level = ob_get_level();
		ob_start();
		$template_file = SFWD_LMS::get_template( 'quiz', null, null, true );
		if ( ! empty( $template_file ) ) {
			include $template_file;
		}

		$content = learndash_ob_get_clean( $level );

		// Added this defined wrap in v2.1.8 as it was effecting <pre></pre>, <code></code> and other formatting of the content.
		// See wrike https://www.wrike.com/open.htm?id=77352698 as to why this define exists
		if ( ( defined( 'LEARNDASH_NEW_LINE_AND_CR_TO_SPACE' ) ) && ( LEARNDASH_NEW_LINE_AND_CR_TO_SPACE == true ) ) {

			// Why is this here?
			$content = str_replace( array( "\n", "\r" ), ' ', $content );
		}

		$user_has_access = $has_access ? 'user_has_access' : 'user_has_no_access';

			/** This filter is documented in includes/class-ld-cpt-instance.php */
		$content = '<div class="learndash ' . $user_has_access . '"  id="learndash_post_' . $quiz_post->ID . '">' . apply_filters( 'learndash_content', $content, $quiz_post ) . '</div>';
	}

	return $content;
}
add_shortcode( 'ld_quiz', 'learndash_quiz_shortcode', 10, 2 );
