<?php
/**
 * Handles all server side logic for the ld-certificate Gutenberg Block. This block is functionally the same
 * as the [ld_certificate] shortcode used within LearnDash.
 *
 * @package LearnDash
 * @since 3.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Gutenberg_Block' ) ) && ( ! class_exists( 'LearnDash_Gutenberg_Block_Certificate' ) ) ) {
	/**
	 * Class for handling LearnDash LearnDash_Gutenberg_Block_Certificate Block
	 */
	class LearnDash_Gutenberg_Block_Certificate extends LearnDash_Gutenberg_Block {

		/**
		 * Object constructor
		 */
		public function __construct() {
			$this->shortcode_slug = 'ld_certificate';
			$this->block_slug     = 'ld-certificate';
			$this->self_closing   = false;

			$this->block_attributes = array(
				'course_id'         => array(
					'type' => 'string',
				),
				'quiz_id'           => array(
					'type' => 'string',
				),
				'user_id'           => array(
					'type' => 'string',
				),
				'label'             => array(
					'type' => 'string',
				),
				'class_html'        => array(
					'type' => 'string',
				),
				'context'           => array(
					'type' => 'string',
				),
				'callback'          => array(
					'type' => 'string',
				),
				'preview_show'      => array(
					'type' => 'boolean',
				),
				'preview_course_id' => array(
					'type' => 'string',
				),
				'preview_quiz_id'   => array(
					'type' => 'string',
				),
				'preview_user_id'   => array(
					'type' => 'string',
				),
				'example_show'      => array(
					'type' => 'boolean',
				),
			);

			$this->init();
		}

		/**
		 * Render Block
		 *
		 * This function is called per the register_block_type() function above. This function will output
		 * the block rendered content. In the case of this function the rendered output will be for the
		 * [ld_profile] shortcode.
		 *
		 * @since 3.1.4
		 *
		 * @param array $attributes Shortcode attrbutes.
		 * @return none The output is echoed.
		 */
		public function render_block( $attributes = array() ) {
			$attributes = $this->preprocess_block_attributes( $attributes );

			if ( is_user_logged_in() ) {

				if ( ( isset( $attributes['example_show'] ) ) && ( ! empty( $attributes['example_show'] ) ) ) {
					$attributes['preview_course_id'] = $this->get_example_post_id( learndash_get_post_type_slug( 'course' ) );
					$attributes['preview_user_id']   = $this->get_example_user_id();
					$attributes['preview_show']      = true;
					unset( $attributes['example_show'] );
				}

				if ( ( isset( $attributes['preview_show'] ) ) && ( ! empty( $attributes['preview_show'] ) ) ) {
					unset( $attributes['preview_show'] );
					if ( ( isset( $attributes['preview_course_id'] ) ) && ( ! empty( $attributes['preview_course_id'] ) ) ) {
						$attributes['course_id'] = absint( $attributes['preview_course_id'] );
						unset( $attributes['preview_course_id'] );
					}
					if ( ( isset( $attributes['preview_quiz_id'] ) ) && ( ! empty( $attributes['preview_quiz_id'] ) ) ) {
						$attributes['quiz_id'] = absint( $attributes['preview_quiz_id'] );
						unset( $attributes['preview_quiz_id'] );
					}
					if ( ( isset( $attributes['preview_user_id'] ) ) && ( ! empty( $attributes['preview_user_id'] ) ) ) {
						$attributes['user_id'] = absint( $attributes['preview_user_id'] );
						unset( $attributes['preview_user_id'] );
					}
				}

				if ( ( ( ! isset( $attributes['course_id'] ) ) || ( empty( $attributes['course_id'] ) ) ) && ( ( ! isset( $attributes['quiz_id'] ) ) || ( empty( $attributes['quiz_id'] ) ) ) ) {
					return $this->render_block_wrap(
						'<span class="learndash-block-error-message">' . sprintf(
						// translators: placeholder: Course, Quiz, Course.
							_x( '%1$s ID or %2$s ID is required when not used within a %3$s.', 'placeholder: Course, Quiz, Course', 'learndash' ),
							LearnDash_Custom_Label::get_label( 'course' ),
							LearnDash_Custom_Label::get_label( 'quiz' ),
							LearnDash_Custom_Label::get_label( 'course' )
						) . '</span>'
					);
				}

				if ( isset( $attributes['class_html'] ) ) {
					$attributes['class'] = esc_attr( $attributes['class_html'] );
					unset( $attributes['class_html'] );
				}

				$shortcode_params_str = '';
				foreach ( $attributes as $key => $val ) {
					if ( ( empty( $key ) ) || ( empty( $val ) ) || ( is_null( $val ) ) ) {
						continue;
					}

					if ( ( 'user_id' === $key ) && ( '' !== $val ) ) {
						if ( learndash_is_admin_user( get_current_user_id() ) ) {
							// If admin user they can preview any user_id.
						} elseif ( learndash_is_group_leader_user( get_current_user_id() ) ) {
							// If group leader user we ensure the preview user_id is within their group(s).
							if ( ! learndash_is_group_leader_of_user( get_current_user_id(), $val ) ) {
								continue;
							}
						} else {
							// If neither admin or group leader then we don't see the user_id for the shortcode.
							continue;
						}

						$key = str_replace( 'preview_', '', $key );
						$val = intval( $val );
					}

					if ( ! empty( $shortcode_params_str ) ) {
						$shortcode_params_str .= ' ';
					}
					$shortcode_params_str .= $key . '="' . esc_attr( $val ) . '"';
				}

				$shortcode_params_str = '[' . $this->shortcode_slug . ' ' . $shortcode_params_str . ']';
				$shortcode_out        = do_shortcode( $shortcode_params_str );
				if ( empty( $shortcode_out ) ) {
					$shortcode_out = '[' . $this->shortcode_slug . '] placeholder output.';
				}

				return $this->render_block_wrap( $shortcode_out );
			}
			wp_die();
		}

		/**
		 * Called from the LD function learndash_convert_block_markers_shortcode() when parsing the block content.
		 *
		 * @since 3.1.4
		 *
		 * @param array  $attributes The array of attributes parse from the block content.
		 * @param string $shortcode_slug This will match the related LD shortcode ld_profile, ld_course_list, etc.
		 * @param string $block_slug This is the block token being processed. Normally same as the shortcode but underscore replaced with dash.
		 * @param string $content This is the orignal full content being parsed.
		 *
		 * @return array $attributes.
		 */
		public function learndash_block_markers_shortcode_atts_filter( $attributes = array(), $shortcode_slug = '', $block_slug = '', $content = '' ) {
			if ( $shortcode_slug === $this->shortcode_slug ) {
				if ( isset( $attributes['preview_show'] ) ) {
					unset( $attributes['preview_show'] );
				}
				if ( isset( $attributes['preview_course_id'] ) ) {
					unset( $attributes['preview_course_id'] );
				}
				if ( isset( $attributes['preview_quiz_id'] ) ) {
					unset( $attributes['preview_quiz_id'] );
				}
				if ( isset( $attributes['preview_user_id'] ) ) {
					unset( $attributes['preview_user_id'] );
				}
				if ( isset( $attributes['class_html'] ) ) {
					$attributes['class'] = esc_attr( $attributes['class_html'] );
					unset( $attributes['class_html'] );
				}
			}
			return $attributes;
		}

		// End of functions.
	}
}
new LearnDash_Gutenberg_Block_Certificate();
