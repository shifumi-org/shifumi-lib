<?php
/**
 * LearnDash `[ld_lesson_list]` shortcode processing.
 *
 * @since 2.1.0
 *
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_lesson_list]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 2.1.0
 *
 * @param array $attr {
 *    An array of shortcode attributes.
 *
 *    Default empty array. {@see 'ld_course_list'}
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The `ld_lesson_list` shortcode output.
 */
function ld_lesson_list( $attr = array(), $content = '' ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;

	if ( ! is_array( $attr ) ) {
		$attr = array();
	}

	$attr['post_type'] = learndash_get_post_type_slug( 'lesson' );
	$attr['mycourses'] = false;
	$attr['status']    = false;

	// If we have a course_id. Then we set the orderby to match the items within the course.
	if ( ( isset( $attr['course_id'] ) ) && ( ! empty( $attr['course_id'] ) ) ) {
		$attr['course_id'] = absint( $attr['course_id'] );
		$course_steps      = learndash_course_get_steps_by_type( $attr['course_id'], $attr['post_type'] );
		if ( ! empty( $course_steps ) ) {
			$attr['post__in'] = $course_steps;
		}

		if ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
			if ( ! isset( $attr['order'] ) ) {
				$attr['order'] = 'ASC';
			}
			if ( ! isset( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
		}
	}

	return ld_course_list( $attr );
}
add_shortcode( 'ld_lesson_list', 'ld_lesson_list', 10, 2 );
