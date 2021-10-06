<?php
/**
 * LearnDash `[course_content]` shortcode processing.
 *
 * @since 2.1.0
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[course_content]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 2.1.0
 *
 * @param array $atts {
 *    The shortcode attributes.
 *
 *    @type int         $course_id The ID of the course. Default 0.
 *    @type boolean|int $num       Unused Default false.
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The output of the shortcode.
 */
function learndash_course_content_shortcode( $atts = array(), $content = '' ) {

	global $learndash_shortcode_used;

	$atts_defaults = array(
		'course_id' => 0,
		'num'       => false,
	);
	$atts          = shortcode_atts( $atts_defaults, $atts );

	if ( empty( $atts['course_id'] ) ) {
		$course_id = learndash_get_course_id();
		if ( empty( $course_id ) ) {
			return '';
		}
		$atts['course_id'] = intval( $course_id );
	}

	if ( isset( $_GET['ld-courseinfo-lesson-page'] ) ) {
		$atts['paged'] = intval( $_GET['ld-courseinfo-lesson-page'] );
	}

	$course_id = intval( $atts['course_id'] );

	$course = $post = get_post( $course_id );

	// if ( ! is_singular() || $post->post_type != 'sfwd-courses' ) {
	// return '';
	// }

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$user_id = 0;
	}

	$logged_in                  = ! empty( $user_id );
	$lesson_progression_enabled = false;

	$course_settings            = learndash_get_setting( $course );
	$lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );
	$courses_options            = learndash_get_option( 'sfwd-courses' );
	$lessons_options            = learndash_get_option( 'sfwd-lessons' );
	$quizzes_options            = learndash_get_option( 'sfwd-quiz' );
	$course_status              = learndash_course_status( $course_id, null );
	$has_access                 = sfwd_lms_has_access( $course_id, $user_id );

	$lessons            = learndash_get_course_lessons_list( $course, $user_id, $atts );
	$quizzes            = learndash_get_course_quiz_list( $course );
	$has_course_content = ( ! empty( $lessons ) || ! empty( $quizzes ) );

	$has_topics = false;

	if ( ! empty( $lessons ) ) {
		foreach ( $lessons as $lesson ) {
			$lesson_topics[ $lesson['post']->ID ] = learndash_topic_dots( $lesson['post']->ID, false, 'array', $user_id, $course_id );
			if ( ! empty( $lesson_topics[ $lesson['post']->ID ] ) ) {
				$has_topics = true;
			}
		}
	}

	$level = ob_get_level();
	ob_start();
	$template_file = SFWD_LMS::get_template( 'course_content_shortcode', null, null, true );
	if ( ! empty( $template_file ) ) {
		include $template_file;
	}

	$content         = learndash_ob_get_clean( $level );
	$content         = str_replace( array( "\n", "\r" ), ' ', $content );
	$user_has_access = $has_access ? 'user_has_access' : 'user_has_no_access';

	$learndash_shortcode_used = true;

	// Prevent the shortcoce page from showing when used on a course (sfwd-courses) single page
	// as it will conflict with pager from the templates/course.php output.
	$queried_object = get_queried_object();
	if ( ( is_a( $queried_object, 'WP_Post' ) ) && ( 'sfwd-courses' === $queried_object->post_type ) ) {
		global $course_pager_results;
		$course_pager_results = null;
	}

	/** This filter is documented in includes/class-ld-cpt-instance.php */
	return '<div class="learndash ' . $user_has_access . '" id="learndash_post_' . $course_id . '">' . apply_filters( 'learndash_content', $content, $post ) . '</div>';
}
add_shortcode( 'course_content', 'learndash_course_content_shortcode', 10, 2 );
