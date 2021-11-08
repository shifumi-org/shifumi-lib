<?php
/**
 * LearnDash Shortcode Section for Payment Buttons [learndash_payment_buttons].
 *
 * @since 2.4.0
 * @package LearnDash\Settings\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Shortcodes_Section' ) ) && ( ! class_exists( 'LearnDash_Shortcodes_Section_learndash_payment_buttons' ) ) ) {
	/**
	 * Class LearnDash Shortcode Section for Payment Buttons [learndash_payment_buttons].
	 *
	 * @since 2.4.0
	 */
	//phpcs:ignore PEAR.NamingConventions.ValidClassName.Invalid
	class LearnDash_Shortcodes_Section_learndash_payment_buttons extends LearnDash_Shortcodes_Section {

		/**
		 * Public constructor for class.
		 *
		 * @since 2.4.0
		 *
		 * @param array $fields_args Field Args.
		 */
		public function __construct( $fields_args = array() ) {
			$this->fields_args = $fields_args;

			$this->shortcodes_section_key         = 'learndash_payment_buttons';
			$this->shortcodes_section_title       = esc_html__( 'Payment Buttons', 'learndash' );
			$this->shortcodes_section_type        = 1;
			$this->shortcodes_section_description = esc_html__( 'This shortcode can show the payment buttons on any page.', 'learndash' );

			parent::__construct();
		}

		/**
		 * Initialize the shortcode fields.
		 *
		 * @since 2.4.0
		 */
		public function init_shortcodes_section_fields() {
			$this->shortcodes_option_fields = array(
				'course_id' => array(
					'id'        => $this->shortcodes_section_key . '_course_id',
					'name'      => 'course_id',
					'type'      => 'number',
					'label'     => sprintf(
						// translators: placeholder: Course.
						esc_html_x( '%s ID', 'placeholder: Course', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' )
					),
					'help_text' => sprintf(
						// translators: placeholder: Course.
						esc_html_x( 'Enter single %s ID', 'placeholders: Course', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' )
					),
					'value'     => '',
					'class'     => 'small-text',
					'required'  => 'required',
				),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->shortcodes_option_fields = apply_filters( 'learndash_settings_fields', $this->shortcodes_option_fields, $this->shortcodes_section_key );

			parent::init_shortcodes_section_fields();
		}
	}
}
