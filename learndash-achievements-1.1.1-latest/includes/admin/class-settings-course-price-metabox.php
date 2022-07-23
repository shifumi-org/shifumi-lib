<?php

namespace LearnDash\Achievements;

if ( ( class_exists( 'LearnDash_Settings_Metabox' ) ) && ( ! class_exists( 'LearnDash_Settings_Metabox_Course_Access_Settings' ) ) ) {
	class Course_Price extends \LearnDash_Settings_Metabox {
		/**
		 * Public constructor for class
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			// What screen ID are we showing on.
			$this->settings_screen_id = 'sfwd-courses';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_metabox_key = 'learndash-course-achievements-settings';

			// Section label/header.
			$this->settings_section_label       = esc_html__( 'Achievements Settings', 'learndash' );
			$this->settings_section_description = esc_html__( 'Placeholder' );

			$this->settings_fields_map = array(
				'achievements_buy_course'              => 'achievements_buy_course',
				'achievements_buy_course_course_price' => 'achievements_buy_course_course_price',
			);

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();
			if ( true === $this->settings_values_loaded ) {
				if ( ! isset( $this->setting_option_values['achievements_buy_course'] ) ) {
					$this->setting_option_values['achievements_buy_course'] = '0';
				}

				if ( ! isset( $this->setting_option_values['achievements_buy_course_course_price'] ) ) {
					$this->setting_option_values['achievements_buy_course_course_price'] = 1;
				}
			}
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'achievements_buy_course'              => array(
					'label'               => __(
						'Allow to use achievements points to buy this course',
						'learndash-achievements'
					),
					'name'                => 'achievements_buy_course',
					'type'                => 'checkbox-switch',
					'help_text'           => sprintf(
					// translators: placeholders: course.
						esc_html_x(
							'Allow to use achievements points to purchase this %1$s.',
							'placeholders: course',
							'learndash'
						),
						learndash_get_custom_label_lower( 'course' )
					),
					'child_section_state' => '1' === $this->setting_option_values['achievements_buy_course'] ? 'open' : 'closed',
					'value'               => $this->setting_option_values['achievements_buy_course'],
					'options'             => array(
						'0' => '',
						'1' => '',
					),
				),
				'achievements_buy_course_course_price' => array(
					'name'           => 'achievements_buy_course_course_price',
					'type'           => 'number',
					'class'          => 'small-text',
					'label'          => __( 'Course Price', 'learndash-achievements' ),
					'value'          => $this->setting_option_values['achievements_buy_course_course_price'],
					'default'        => 1,
					'attrs'          => array(
						'step' => 1,
						'min'  => 1,
					),
					'parent_setting' => 'achievements_buy_course',
				),
			);
			parent::load_settings_fields();
		}
	}

	add_filter(
		'learndash_post_settings_metaboxes_init_' . learndash_get_post_type_slug( 'course' ),
		function ( $metaboxes = array() ) {
			if ( ( ! isset( $metaboxes['LearnDash_Achievement_Course_Price_Settings'] ) ) ) {
				$metaboxes['LearnDash_Achievement_Course_Price_Settings'] = Course_Price::add_metabox_instance();
			}

			return $metaboxes;
		},
		50,
		1
	);
}
