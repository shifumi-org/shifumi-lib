<?php
/**
 * Handles all server side logic for the ld-quizinfo Gutenberg Block. This block is functionally the same
 * as the quizinfo shortcode used within LearnDash.
 *
 * @package LearnDash
 * @since 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Gutenberg_Block' ) ) && ( ! class_exists( 'LearnDash_Gutenberg_Block_Quizinfo' ) ) ) {
	/**
	 * Class for handling LearnDash Quizinfo Block
	 */
	class LearnDash_Gutenberg_Block_Quizinfo extends LearnDash_Gutenberg_Block {

		/**
		 * Object constructor
		 */
		public function __construct() {
			$this->shortcode_slug   = 'quizinfo';
			$this->block_slug       = 'ld-quizinfo';
			$this->block_attributes = array(
				'show'            => array(
					'type' => 'string',
				),
				'quiz_id'         => array(
					'type' => 'string',
				),
				'user_id'         => array(
					'type' => 'string',
				),
				'format'          => array(
					'type' => 'string',
				),
				'field_id'        => array(
					'type' => 'string',
				),
				'seconds_format'  => array(
					'type' => 'string',
				),
				'decimals'        => array(
					'type' => 'string',
				),
				'preview_show'    => array(
					'type' => 'boolean',
				),
				'preview_quiz_id' => array(
					'type' => 'string',
				),
				'preview_user_id' => array(
					'type' => 'string',
				),
				'meta'            => array(
					'type' => 'object',
				),
			);
			$this->self_closing     = true;

			$this->init();
		}

		/**
		 * Render Block
		 *
		 * This function is called per the register_block_type() function above. This function will output
		 * the block rendered content. In the case of this function the rendered output will be for the
		 * [ld_profile] shortcode.
		 *
		 * @since 2.5.9
		 *
		 * @param array $attributes Shortcode attrbutes.
		 * @return none The output is echoed.
		 */
		public function render_block( $attributes = array() ) {
			$attributes = $this->preprocess_block_attributes( $attributes );

			$attributes_meta = array();
			if ( isset( $attributes['meta'] ) ) {
				$attributes_meta = $attributes['meta'];
				unset( $attributes['meta'] );
			}

			if ( is_user_logged_in() ) {

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

				if ( ( ! isset( $attributes['quiz_id'] ) ) || ( empty( $attributes['quiz_id'] ) ) ) {
					if ( ( ! isset( $attributes_meta['quiz_id'] ) ) || ( empty( $attributes_meta['quiz_id'] ) ) ) {
						return $this->render_block_wrap(
							'<span class="learndash-block-error-message">' . sprintf(
							// translators: placeholder: Quiz, Quiz, Certificate.
								_x( '%1$s ID is required when not used within a %2$s or %3$s.', 'placeholder: Quiz, Quiz, Certificate', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' ),
								LearnDash_Custom_Label::get_label( 'quiz' ),
								'Certificate'
							) . '</span>'
						);
					} else {
						$attributes['quiz_id'] = (int) $attributes_meta['quiz_id'];
					}
				} else {
					$quiz_post = get_post( (int) $attributes['quiz_id'] );
					if ( ( ! is_a( $quiz_post, 'WP_Post' ) ) || ( learndash_get_post_type_slug( 'quiz' ) !== $quiz_post->post_type ) ) {
						return $this->render_block_wrap(
							'<span class="learndash-block-error-message">' . sprintf(
							// translators: placeholder: Quiz.
								_x( 'Invalid %1$s ID.', 'placeholder: Quiz', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' )
							) . '</span>'
						);
					}
				}

				/** This filter is documented in includes/gutenberg/blocks/ld-course-list/index.php */
				$attributes = apply_filters( 'learndash_block_markers_shortcode_atts', $attributes, $this->shortcode_slug, $this->block_slug, '' );

				$shortcode_params_str = '';
				foreach ( $attributes as $key => $val ) {
					if ( ( empty( $key ) ) || ( is_null( $val ) ) ) {
						continue;
					}

					if ( 'preview_show' === $key ) {
						continue;
					} elseif ( 'preview_user_id' === $key ) {
						if ( ( ! isset( $attributes['user_id'] ) ) && ( 'preview_user_id' === $key ) && ( '' !== $val ) ) {
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
					} elseif ( empty( $val ) ) {
						continue;
					}

					$shortcode_params_str .= ' ' . $key . '="' . esc_attr( $val ) . '"';
				}

				$shortcode_params_str = '[' . $this->shortcode_slug . $shortcode_params_str . ']';

				$shortcode_out = do_shortcode( $shortcode_params_str );
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
		 * @since 2.5.9
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
				if ( isset( $attributes['quiz_id'] ) ) {
					$attributes['quiz'] = $attributes['quiz_id'];
					unset( $attributes['quiz_id'] );
				}
				if ( isset( $attributes['preview_show'] ) ) {
					unset( $attributes['preview_show'] );
				}
				if ( isset( $attributes['preview_user_id'] ) ) {
					unset( $attributes['preview_user_id'] );
				}
			}
			return $attributes;
		}

		// End of functions.
	}
}
new LearnDash_Gutenberg_Block_Quizinfo();
