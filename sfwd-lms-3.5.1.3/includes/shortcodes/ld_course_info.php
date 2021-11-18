<?php
/**
 * LearnDash `[ld_course_info]` shortcode processing.
 *
 * @since 2.1.0
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_course_info]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 2.1.0
 *
 * @param array $atts {
 *    An array of shortcode attributes. Default empty array.
 *
 *    @type int $user_id User ID.
 *    {@see 'SFWD_LMS::get_course_info'} for other attributes
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The `ld_course_info` shortcode ouput.
 */
function learndash_course_info_shortcode( $atts = array(), $content = '' ) {

	global $learndash_shortcode_used;

	if ( ( isset( $atts['user_id'] ) ) && ( ! empty( $atts['user_id'] ) ) ) {
		$user_id = intval( $atts['user_id'] );
		unset( $atts['user_id'] );
	} else {
		$current_user = wp_get_current_user();

		if ( empty( $current_user->ID ) ) {
			return;
		}

		$user_id = $current_user->ID;
	}

	$learndash_shortcode_used = true;

	return SFWD_LMS::get_course_info( $user_id, $atts );
}
add_shortcode( 'ld_course_info', 'learndash_course_info_shortcode', 10, 2 );
