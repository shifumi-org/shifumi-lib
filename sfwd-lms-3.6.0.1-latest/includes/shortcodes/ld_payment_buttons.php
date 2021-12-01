<?php
/**
 * LearnDash `[learndash_payment_buttons]` shortcode processing.
 *
 * @since 2.1.0
 *
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[learndash_payment_buttons]` shortcode output.
 *
 * @since 2.1.0
 *
 * @global boolean $learndash_shortcode_used
 *
 * @param array  $attr {
 *    An array of shortcode attributes.
 *
 *    @type int $course_id Course ID. Default 0.
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @return string Returns the `learndash_payment_buttons` shortcode output.
 */
function learndash_payment_buttons_shortcode( $attr = array(), $content = '' ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;

	$shortcode_atts = shortcode_atts( array( 'course_id' => 0 ), $attr );
	if ( empty( $shortcode_atts['course_id'] ) ) {
		$course_id = learndash_get_course_id();
		if ( empty( $course_id ) ) {
			return '';
		}
		$shortcode_atts['course_id'] = intval( $course_id );
	}

	return learndash_payment_buttons( $shortcode_atts['course_id'] );
}
add_shortcode( 'learndash_payment_buttons', 'learndash_payment_buttons_shortcode', 10, 2 );
