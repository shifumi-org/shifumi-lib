<?php
/**
 * LearnDash Shortcode Section for Certificate [ld_certificate].
 *
 * @since 2.4.0
 * @package LearnDash\Settings\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Shortcodes_Section' ) ) && ( ! class_exists( 'LearnDash_Shortcodes_Section_ld_certificate' ) ) ) {
	/**
	 * Class LearnDash Shortcode Section for Certificate [ld_certificate].
	 *
	 * @since 2.4.0
	 */
	class LearnDash_Shortcodes_Section_ld_certificate extends LearnDash_Shortcodes_Section { //phpcs:ignore PEAR.NamingConventions.ValidClassName.Invalid

		/**
		 * Public constructor for class.
		 *
		 * @since 2.4.0
		 *
		 * @param array $fields_args Field Args.
		 */
		public function __construct( $fields_args = array() ) {
			$this->fields_args = $fields_args;

			$this->shortcodes_section_key         = 'ld_certificate';
			$this->shortcodes_section_title       = esc_html__( 'Certificate', 'learndash' );
			$this->shortcodes_section_type        = 2;
			$this->shortcodes_section_description = esc_html__( 'This shortcode shows a Certificate download link.', 'learndash' );

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
						// translators: placeholders: Course, Course.
						esc_html_x( 'Enter single %1$s ID. Leave blank for current %2$s.', 'placeholders: Course, Course', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' ),
						LearnDash_Custom_Label::get_label( 'course' )
					),
					'value'     => '',
					'class'     => 'small-text',
				),
				'quiz_id'   => array(
					'id'        => $this->shortcodes_section_key . '_quiz_id',
					'name'      => 'quiz_id',
					'type'      => 'number',
					'label'     => sprintf(
						// translators: placeholder: Quiz.
						esc_html_x( '%s ID', 'placeholder: Quiz', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'quiz' )
					),
					'help_text' => sprintf(
						// translators: placeholders: Quiz, Quiz.
						esc_html_x( 'Enter single %1$s ID. Leave blank for current %2$s.', 'placeholders: Quiz, Quiz', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'quiz' ),
						LearnDash_Custom_Label::get_label( 'quiz' )
					),
					'value'     => '',
					'class'     => 'small-text',
				),
				'user_id'   => array(
					'id'        => $this->shortcodes_section_key . '_user_id',
					'name'      => 'user_id',
					'type'      => 'number',
					'label'     => esc_html__( 'User ID', 'learndash' ),
					'help_text' => esc_html__( 'Enter specific User ID. Leave blank for current User.', 'learndash' ),
					'value'     => '',
					'class'     => 'small-text',
				),
				'label'     => array(
					'id'        => $this->shortcodes_section_key . '_label',
					'name'      => 'label',
					'type'      => 'text',
					'label'     => esc_html__( 'Label', 'learndash' ),
					'help_text' => esc_html__( 'Label for link shown to user', 'learndash' ),
					'value'     => '',
				),
				'class'     => array(
					'id'        => $this->shortcodes_section_key . '_class',
					'name'      => 'class',
					'type'      => 'text',
					'label'     => esc_html__( 'HTML Class', 'learndash' ),
					'help_text' => esc_html__( 'HTML class for link element', 'learndash' ),
					'value'     => '',
				),
				'context'   => array(
					'id'        => $this->shortcodes_section_key . '_context',
					'name'      => 'context',
					'type'      => 'text',
					'label'     => esc_html__( 'Context', 'learndash' ),
					'help_text' => esc_html__( 'User defined value to be passed into shortcode handler', 'learndash' ),
					'value'     => '',
				),
				'callback'  => array(
					'id'        => $this->shortcodes_section_key . '_callback',
					'name'      => 'callback',
					'type'      => 'text',
					'label'     => esc_html__( 'Callback', 'learndash' ),
					'help_text' => esc_html__( 'Custom callback function to be used instead of default output', 'learndash' ),
					'value'     => '',
				),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->shortcodes_option_fields = apply_filters( 'learndash_settings_fields', $this->shortcodes_option_fields, $this->shortcodes_section_key );

			parent::init_shortcodes_section_fields();
		}
	}
}
