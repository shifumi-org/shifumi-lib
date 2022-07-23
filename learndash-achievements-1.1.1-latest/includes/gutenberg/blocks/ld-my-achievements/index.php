<?php
/**
 * Handles all server side logic for the ld-my-achievements Gutenberg Block. This block is functionally the same
 * as the ld_my_achievements shortcode used within LearnDash.
 *
 * @package LearnDash
 * @since 2.5.9
 */

if ( ! class_exists( 'LearnDash_Gutenberg_Block' ) ) {
	return;
}

if ( ! class_exists( 'LearnDash_Gutenberg_Block_My_Achievements' ) ) {
	/**
	 * Class for handling LearnDash Achievements Leaderboard Block
	 */
	class LearnDash_Gutenberg_Block_My_Achievements extends LearnDash_Gutenberg_Block {

		/**
		 * Object constructor
		 */
		public function __construct() {
			$this->shortcode_slug   = 'ld_my_achievements';
			$this->block_slug       = 'ld-my-achievements';
			$this->block_attributes = array(
				'preview_show' => array(
					'type' => 'boolean',
				),
				'show_title'   => array(
					'type' => 'boolean',
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
		public function render_block( $block_attributes = array(), $block_content = '', WP_block $block = null ) {

			if ( is_user_logged_in() ) {

				$block_attributes           = apply_filters( 'learndash_block_markers_shortcode_atts', $block_attributes, $this->shortcode_slug, $this->block_slug, '' );
				$shortcode_params_str = $this->prepare_course_list_atts_to_param( $block_attributes );
				$shortcode_params_str = '[' . $this->shortcode_slug . ' ' . $shortcode_params_str . ']';
				$shortcode_out        = do_shortcode( $shortcode_params_str );

				// This is mainly to protect against emty returns with the Gutenberg ServerSideRender function.
				return $this->render_block_wrap( $shortcode_out );
			}
		}

		/**
		 * Called from the LD function learndash_convert_block_markers_shortcode() when parsing the block content.
		 *
		 * @since 2.5.9
		 *
		 * @param array  $attributes The array of attributes parse from the block content.
		 * @param string $shortcode_slug This will match the related LD shortcode ld_profile, ld_my_achievements, etc.
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
			}

			return $attributes;
		}
	} // End class
} // Endif

new LearnDash_Gutenberg_Block_My_Achievements();
