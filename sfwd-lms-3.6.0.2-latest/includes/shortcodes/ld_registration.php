<?php
/**
 * LearnDash `[ld_registration]` shortcode processing.
 *
 * @since 3.6.0
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_registration]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 3.6.0
 *
 * @param array  $attr {
 *    @type string $width Width of the registration form. Default empty.
 * }
 *
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The `ld_registration` shortcode ouput.
 */
function learndash_registration( $attr = array(), $content = '' ) {

	global $learndash_shortcode_used;

	$learndash_shortcode_used = true;

	if ( ! is_array( $attr ) ) {
		$attr = array();
	}

	$attr = shortcode_atts(
		array(
			'width' => '',
		),
		$attr
	);

	$level = ob_get_level();
	ob_start();

	learndash_registration_output( $attr );

	$content .= learndash_ob_get_clean( $level );
	return $content;

}

add_shortcode( 'ld_registration', 'learndash_registration', 10, 2 );
