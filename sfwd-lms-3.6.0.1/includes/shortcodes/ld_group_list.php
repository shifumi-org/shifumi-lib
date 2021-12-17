<?php
/**
 * LearnDash `[ld_group_list]` shortcode processing.
 *
 * @since 2.1.0
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_group_list]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 3.1.7
 *
 * @param array  $attr {
 *    An array of shortcode attributes.
 *
 *    Default empty array {@see 'ld_course_list'}.
 * }.
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The `ld_topic_list` shortcode output.
 */
function ld_group_list( $attr = array(), $content = '' ) {
	global $learndash_shortcode_used;
	$learndash_shortcode_used = true;

	if ( ! is_array( $attr ) ) {
		$attr = array();
	}

	$attr['post_type'] = learndash_get_post_type_slug( 'group' );

	return ld_course_list( $attr );

}

add_shortcode( 'ld_group_list', 'ld_group_list', 10, 2 );
