<?php

namespace LearnDash\Achievements\Settings\Section;

use LearnDash_Settings_Section;

if ( class_exists( 'LearnDash_Settings_Section' ) ) :
	class Group_Leader extends LearnDash_Settings_Section {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->settings_page_id = 'ld-achievements-settings';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_achievements_settings_group_leader';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_achievements_settings_group_leader';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_achievements_settings_group_leader';

			// Section label/header.
			$this->settings_section_label = __( 'Group Leader Settings', 'learndash-achievements' );

			parent::__construct();
		}

		public function load_settings_values() {
			parent::load_settings_values();

			$this->setting_option_values = wp_parse_args(
				$this->setting_option_values,
				array(
					'allow_delete' => false,
				)
			);
		}

		/**
		 * Add fields.
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'allow_delete' => array(
					'name'      => 'allow_delete',
					'type'      => 'checkbox-switch',
					'label'     => __( 'Allow delete badges', 'learndash-achievements' ),
					'help_text' => __( 'Allow the group leader can deletes the students\' badges', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['allow_delete'] ) ? $this->setting_option_values['allow_delete'] : '0',
					'options'   => array(
						'0' => __( 'Disable', 'learndash-achievements' ),
						'1' => __( 'Enable', 'learndash-achievements' ),
					),
				),
			);

			$this->setting_option_fields = apply_filters( 'learndash_achievements_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}

	}

	add_action(
		'learndash_settings_sections_init',
		function () {
			Group_Leader::add_section_instance();
		}
	);
endif;
