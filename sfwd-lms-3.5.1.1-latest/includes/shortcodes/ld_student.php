<?php
/**
 * LearnDash `[student]` shortcode processing.
 *
 * @since 2.1.0
 *
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[student]` shortcode output.
 *
 * Shortcode to display content to users that have access to current course ID.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 2.1.0
 *
 * @param array $atts {
 *     An array of shortcode attributes.
 *
 *    @type int     $course_id Course ID. Default current course ID.
 *    @type int     $user_id   User ID. Default current user ID.
 *    @type string  $content   The shortcode content. Default null.
 *    @type boolean $autop     Whether to replace linebreaks with paragraph elements. Default true.
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @param string|null $content The shortcode content. Default null
 *
 * @return string The `student` shortcode output.
 */
function learndash_student_check_shortcode( $atts = array(), $content = '' ) {
	global $learndash_shortcode_used;

	if ( ( ! empty( $content ) ) && ( is_user_logged_in() ) ) {

		if ( ! is_array( $atts ) ) {
			if ( ! empty( $atts ) ) {
				$atts = array( $atts );
			} else {
				$atts = array();
			}
		}

		$defaults = array(
			'course_id' => learndash_get_course_id(),
			'user_id'   => get_current_user_id(),
			'content'   => $content,
			'autop'     => true,
		);
		$atts     = wp_parse_args( $atts, $defaults );

		if ( ( true === $atts['autop'] ) || ( 'true' === $atts['autop'] ) || ( '1' === $atts['autop'] ) ) {
			$atts['autop'] = true;
		} else {
			$atts['autop'] = false;
		}

		/**
		 * Filters student shortcode attributes.
		 *
		 * @param array $attributes An array of student shortcode attributes.
		 */
		$atts = apply_filters( 'learndash_student_shortcode_atts', $atts );

		if ( ( ! empty( $atts['content'] ) ) && ( ! empty( $atts['user_id'] ) ) && ( ! empty( $atts['course_id'] ) ) && ( get_current_user_id() == $atts['user_id'] ) ) {
			// The reason we are doing this check is because 'sfwd_lms_has_access' will return true if the course does not exist.
			// This needs to be changed to return some other value because true signals the calling function that all is well.
			$course_id = learndash_get_course_id( $atts['course_id'] );
			if ( $course_id == $atts['course_id'] ) {
				if ( sfwd_lms_has_access( $atts['course_id'], $atts['user_id'] ) ) {
					$learndash_shortcode_used = true;
					$atts['content']          = do_shortcode( $atts['content'] );
					return SFWD_LMS::get_template(
						'learndash_course_student_message',
						array(
							'shortcode_atts' => $atts,
						),
						false
					);
				}
			}
		}
	}

	return '';
}
add_shortcode( 'student', 'learndash_student_check_shortcode', 10, 2 );
