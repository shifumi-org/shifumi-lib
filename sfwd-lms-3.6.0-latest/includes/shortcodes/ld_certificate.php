<?php
/**
 * LearnDash `[ld_certificate]` shortcode processing.
 *
 * @since 3.1.4
 * @package LearnDash\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `[ld_certificate]` shortcode output.
 *
 * @global boolean $learndash_shortcode_used
 *
 * @since 3.1.4
 *
 * @param array  $atts {
 *    An array of shortcode attributes.
 *
 *    @type int      $course_id Course ID. Default 0.
 *    @type int      $quiz_id   Quiz ID. Default 0.
 *    @type int      $user_id   User ID. Default current user ID.
 *    @type string   $label     Certificate label. Default translatable 'Certificate' string.
 *    @type string   $class     Certificate CSS class. Default 'button'.
 *    @type string   $content   Shortcode context. Default empty.
 *    @type callable $callback  Callback for certificate button HTML output. Default empty.
 * }
 * @param string $content The shortcode content. Default empty.
 *
 * @return string The `ld_certificate` shortcode output.
 */
function ld_certificate_shortcode( $atts = array(), $content = '' ) {
	global $learndash_shortcode_used;

	if ( ! is_array( $atts ) ) {
		$atts = array();
	}

	$defaults = array(
		'course_id' => 0,
		'quiz_id'   => 0,
		'user_id'   => get_current_user_id(),
		'label'     => esc_html__( 'Certificate', 'learndash' ),
		'class'     => 'button',
		'context'   => '', // User defined value.
	);
	$atts     = shortcode_atts( $defaults, $atts );

	$atts['course_id'] = absint( $atts['course_id'] );
	$atts['quiz_id']   = absint( $atts['quiz_id'] );
	$atts['user_id']   = absint( $atts['user_id'] );

	if ( empty( $atts['course_id'] ) ) {
		$atts['course_id'] = learndash_get_course_id();
	}
	if ( empty( $atts['quiz_id'] ) ) {
		$atts['quiz_id'] = learndash_get_quiz_id();
	}
	/**
	 * Filters `ld_certificate` shortcode attributes.
	 *
	 * @since 3.1.4
	 *
	 * @param array $atts An array of shortcode attributes.
	 */
	$atts = apply_filters( 'ld_certificate_shortcode_values', $atts );

	$atts['cert_url'] = '';

	if ( ! empty( $atts['user_id'] ) ) {
		if ( ( ! empty( $atts['course_id'] ) ) || ( ! empty( $atts['quiz_id'] ) ) ) {
			$learndash_shortcode_used = true;
			$cert_button_html         = '';
			if ( ! empty( $atts['quiz_id'] ) ) {
				// Ensure the user passed the Quiz.
				if ( learndash_is_quiz_complete( $atts['user_id'], $atts['quiz_id'], $atts['course_id'] ) ) {
					$cert_details = learndash_certificate_details( $atts['quiz_id'], $atts['user_id'] );
					if ( ( isset( $cert_details['certificateLink'] ) ) && ( ! empty( $cert_details['certificateLink'] ) ) ) {
						$atts['cert_url'] = $cert_details['certificateLink'];
					}
				}
			} elseif ( ! empty( $atts['course_id'] ) ) {
				// Ensure the user completed the Course.
				if ( 'completed' === learndash_course_status( $atts['course_id'], $atts['user_id'], true ) ) {
					$atts['cert_url'] = learndash_get_course_certificate_link( $atts['course_id'], $atts['user_id'] );
				}
			}

			if ( ! empty( $atts['cert_url'] ) ) {
				/**
				 * Filters `ld_certificate` shortcode certificate URL.
				 *
				 * @since 3.1.4
				 *
				 * @param string $cert_url URL for Certificate.
				 */
				$atts['cert_url'] = apply_filters( 'ld_certificate_shortcode_cert_url', $atts['cert_url'] );

				$cert_button_html = '<a href="' . esc_url( $atts['cert_url'] ) . '"' .
				( ! empty( $atts['class'] ) ? ' class="' . esc_attr( $atts['class'] ) : '' ) . '"' .
				( ! empty( $atts['id'] ) ? ' id="' . esc_attr( $atts['id'] ) . '"' : '' ) .
				'>';

				if ( ! empty( $atts['label'] ) ) {
					$cert_button_html .= do_shortcode( $atts['label'] );
				}

				$cert_button_html .= '</a>';
			}

			/**
			 * Filters certificate button HTML output for `ld_certificate` shortcode.
			 *
			 * @since 3.1.4
			 * @deprecated 3.2.0 Use the {@see 'learndash_certificate_html'} filter instead.
			 *
			 * @param string $cert_button_html The HTML output of generated button element.
			 * @param array  $atts             An array of shortcode attributes used to generate $cert_button_html element.
			 * @param string $content          Shortcode additional content passed into handler function.
			 */
			if ( has_filter( 'learndash_ld_certificate_html' ) ) {
				$cert_button_html = apply_filters_deprecated( 'learndash_ld_certificate_html', array( $cert_button_html, $atts, $content ), '3.2.0', 'learndash_certificate_html' );
			}

			/**
			 * Filters certificate button HTML output for `ld_certificate` shortcode.
			 *
			 * @since 3.1.4
			 *
			 * @param string $cert_button_html The HTML output of generated button element.
			 * @param array  $atts             An array of shortcode attributes used to generate $cert_button_html element.
			 * @param string $content          Shortcode additional content passed into handler function.
			 */
			$cert_button_html = apply_filters( 'learndash_certificate_html', $cert_button_html, $atts, $content );
			if ( ! empty( $cert_button_html ) ) {
				$content .= $cert_button_html;
			}
		}
	}

	return $content;
}
add_shortcode( 'ld_certificate', 'ld_certificate_shortcode', 10, 2 );
