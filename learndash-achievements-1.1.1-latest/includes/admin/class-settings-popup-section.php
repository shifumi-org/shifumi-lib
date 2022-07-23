<?php

namespace LearnDash\Achievements\Settings\Section;

use LearnDash\Achievements\Settings;
use LearnDash_Settings_Section;

if ( class_exists( 'LearnDash_Settings_Section' ) ) :
	/**
	 * Register the Popup section
	 */
	class Popup extends LearnDash_Settings_Section {

		/**
		 * Popup constructor.
		 */
		public function __construct() {
			$this->settings_page_id = 'ld-achievements-settings';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_achievements_settings_popup';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_achievements_settings_popup';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_achievements_settings_popup';

			// Section label/header.
			$this->settings_section_label = __( 'Popup Settings', 'learndash-achievements' );

			parent::__construct();
		}

		/**
		 * Load the settings.
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			$_init = false;
			if ( false === $this->setting_option_values ) {
				$_init                       = true;
				$this->setting_option_values = array();
			}
			$this->setting_option_values = wp_parse_args(
				$this->setting_option_values,
				Settings::get_default_value()
			);
		}

		/**
		 * Load the settings field.
		 */
		public function load_settings_fields() {

			$this->setting_option_fields = array(
				'popup_time'       => array(
					'name'      => 'popup_time',
					'type'      => 'number',
					'label'     => __( 'Popup Time', 'learndash-achievements' ),
					'help_text' => __( 'Number of second(s) before the popup fades away. Enter 0 to manually click to hide the popup.', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['popup_time'] ) ? $this->setting_option_values['popup_time'] : 0,
					'attrs'     => array(
						'min' => 0,
					),
				),

				'background_color' => array(
					'name'      => 'background_color',
					'type'      => 'text',
					'label'     => __( 'Background Color', 'learndash-achievements' ),
					'help_text' => __( 'Background color of the popup.', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['background_color'] ) ? $this->setting_option_values['background_color'] : '#333333',
					'class'     => 'color-picker',
				),

				'text_color'       => array(
					'name'      => 'text_color',
					'type'      => 'text',
					'label'     => __( 'Text Color', 'learndash-achievements' ),
					'help_text' => __( 'Text color of the popup.', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['text_color'] ) ? $this->setting_option_values['text_color'] : '#ffffff',
					'class'     => 'color-picker',
				),

				'rtl'              => array(
					'name'      => 'rtl',
					'type'      => 'checkbox-switch',
					'label'     => _x( 'RTL', 'Right to left', 'learndash-achievements' ),
					'help_text' => __( 'Check this box to use RTL layout.', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['rtl'] ) ? $this->setting_option_values['rtl'] : 0,
					'options'   => array(
						''  => __( 'Disable', 'learndash-achievements' ),
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
			Popup::add_section_instance();
		}
	);

endif;
