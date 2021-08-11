<?php
/**
 * Base class for all ProPanel Gutenberg Blocks.
 *
 * @package ProPanel
 * @since 2.1.4
 */

if ( ! class_exists( 'LearnDash_ProPanel_Gutenberg_Block' ) ) {
	/**
	 * Abstract Parent class to hold common functions used by specific LearnDash ProPanel Blocks.
	 */
	class LearnDash_ProPanel_Gutenberg_Block {

		protected $block_base = 'ld-propanel';
		protected $shortcode_slug;
		protected $block_slug;
		protected $block_attributes;
		protected $self_closing;

		/**
		 * Constructor.
		 */
		public function __construct() {
		}

		/**
		 * Initialize the hooks.
		 * 
		 * @since 2.1.4
		 */
		public function init() {
			if ( function_exists( 'register_block_type' ) ) {
				add_action( 'init', array( $this, 'register_blocks' ) );
			}
		}

		/**
		 * Register Block for Gutenberg
		 *
		 * @since 2.1.4
		 */
		public function register_blocks() {
			register_block_type(
				$this->block_base . '/' . $this->block_slug,
				array(
					'render_callback' => array( $this, 'render_block' ),
					'attributes'      => $this->block_attributes,
				)
			);
		}

		/**
		 * Render Block
		 *
		 * This function is called per the register_block_type() function above. This function will output
		 * the block rendered content. This is called from within the admin edit post type page via an
		 * AJAX-type call to the server.
		 *
		 * Each sub-subclassed instance should provide its own version of this function.
		 *
		 * @since 2.1.4
		 *
		 * @param array  $block_attributes Array of block attributes.
		 * @param string $block_content    Block content.
		 * @param object $block            Block instance.
		 *
		 * @return void The output is echoed.
		 */
		public function render_block( $block_attributes = array(), $block_content = '', $block = null ) {
			if ( is_user_logged_in() ) {
				$shortcode_str = '[' . $this->shortcode_slug;
				if ( ! empty( $this->shortcode_widget ) ) {
					$shortcode_str .= ' widget="' . $this->shortcode_widget . '"';
				}
				$shortcode_params_str = $this->block_attributes_to_string( $block_attributes );
				if ( ! empty( $shortcode_params_str ) ) {
					$shortcode_str .= $shortcode_params_str;
				}
				$shortcode_str .= ']';

				$shortcode_out = do_shortcode( $shortcode_str );
				if ( empty( $shortcode_out ) ) {
					$shortcode_out = '[' . $this->shortcode_slug . '] placholder output.';
				}

				$block_content .= $this->render_block_wrap( $shortcode_out, (empty( $block_content ) ? true : false ) );
				return $block_content;
			} else {
				return '';
			}
			//wp_die();
		}

		/**
		 * Convert Block Attributes array to string for shortcode processing.
		 *
		 * @since 2.1.4 
		 *
		 * @param array $block_attributes Array of block attributes.
		 *
		 * @return string
		 */
		protected function block_attributes_to_string( $block_attributes = array() ) {
			$shortcode_params_str = '';

			$block_attributes = $this->preprocess_block_attributes( $block_attributes );
			$block_attributes = $this->process_block_attributes( $block_attributes );

			if ( ! empty( $block_attributes ) ) {
				foreach ( $block_attributes as $key => $val ) {
					$shortcode_params_str .= ' ' . $key . '="' . esc_attr( $val ) . '"';
				}
			}

			return $shortcode_params_str;
		}

		/**
		 * Pre-Process the block attributes before render.
		 *
		 * This function will validate and remove any unrecognized attributes.
		 *
		 * @since 2.1.4
		 *
		 * @param array $block_attributes Array of block attrbutes.
		 *
		 * @return array $attributes
		 */
		protected function preprocess_block_attributes( $block_attributes = array() ) {
			$block_attributes_new = array();

			foreach ( $block_attributes as $key => $val ) {
				if ( ( empty( $key ) ) || ( is_null( $val ) ) ) {
					continue;
				}

				// Ignore any block attributes not part of out set.
				if ( ! isset( $this->block_attributes[ $key ] ) ) {
					continue;
				}

				$block_attributes_new[ $key ] = $val;
			}

			return $block_attributes_new;
		}

		/**
		 * Process the block attributes before render.
		 *
		 * @since 2.1.4
		 *
		 * @param array $block_attributes Array of block attrbutes.
		 *
		 * @return array $block_attributes
		 */
		protected function process_block_attributes( $block_attributes = array() ) {
			return $block_attributes;
		}

		/**
		 * Add wrapper content around content to be returned to server.
		 *
		 * @since 2.1.4
		 *
		 * @param string  $content Content text to be wrapper.
		 * @param boolean $with_inner Flag to control inclusion of inner block div element.
		 *
		 * @return string wrapped content.
		 */
		public function render_block_wrap( $block_content = '', $with_inner = true ) {
			$return_content  = '';
			$return_content .= '<!-- ' . $this->block_slug . ' gutenberg block begin -->';

			if ( true === $with_inner ) {
				$return_content .= '<div class="learndash-block-inner">';
			}

			$return_content .= $block_content;

			if ( true === $with_inner ) {
				$return_content .= '</div>';
			}

			$return_content .= '<!-- ' . $this->block_slug . ' gutenberg block end -->';

			return $return_content;
		}

		// End of functions.
	}
}
