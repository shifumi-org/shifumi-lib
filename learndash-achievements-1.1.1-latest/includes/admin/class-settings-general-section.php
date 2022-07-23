<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Settings\Section\Popup;

if ( class_exists( 'LearnDash_Settings_Section' ) ) :

	class General extends \LearnDash_Settings_Section {
		public function __construct() {
			$this->settings_page_id = 'ld-achievements-settings';
			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_achievements_settings_general';
			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_achievements_settings_general';
			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_achievements_settings_general';
			// Section label/header.
			$this->settings_section_label = __( 'General Settings', 'learndash-achievements' );

			parent::__construct();
		}

		function load_settings_values() {
			parent::load_settings_values();

			if ( $this->setting_option_values === false ) {
				$this->setting_option_values = array();
			}

			$this->setting_option_values = wp_parse_args(
				$this->setting_option_values,
				array(
					'redeem_course_by_point' => '0',
				)
			);
		}

		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'redeem_course_by_point' => array(
					'name'      => 'redeem_course_by_point',
					'type'      => 'checkbox-switch',
					'label'     => __( 'Allow redeeming courses by points', 'learndash-achievements' ),
					'help_text' => __( 'Enable this to allow the users to redeem the courses by achievements points.',
					                   'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['redeem_course_by_point'] ) ? $this->setting_option_values['redeem_course_by_point'] : 0,
					'options'   => array(
						'0' => __( 'Disable', 'learndash-achievements' ),
						'1' => __( 'Enable', 'learndash-achievements' ),
					),
				),
			);

			$this->setting_option_fields = apply_filters( 'learndash_achievements_settings_fields',
			                                              $this->setting_option_fields,
			                                              $this->settings_section_key );

			parent::load_settings_fields();
		}
	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			General::add_section_instance();
		},9
	);
endif;
