<?php

namespace LearnDash\Achievements\Settings\Section;

use LearnDash_Settings_Section;

if ( class_exists( 'LearnDash_Settings_Section' ) ) :
	class Badge extends LearnDash_Settings_Section {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->settings_page_id = 'ld-achievements-settings';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_achievements_settings_badge';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_achievements_settings_badge';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_achievements_settings_badge';

			// Section label/header.
			$this->settings_section_label = __( 'Badge Settings', 'learndash-achievements' );

			parent::__construct();
		}


		public function load_settings_values() {
			parent::load_settings_values();

			$this->setting_option_values = wp_parse_args(
				$this->setting_option_values,
				array(
					'size'              => 40,
					'tooltip_font_size' => 12,
				)
			);
		}

		/**
		 * Add fields.
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'size'              => array(
					'name'      => 'size',
					'type'      => 'number',
					'label'     => __( 'Badge size (px)', 'learndash-achievements' ),
					'help_text' => __( '', 'learndash-achievements' ),
					'value'     => isset( $this->setting_option_values['size'] ) ? $this->setting_option_values['size'] : 40,
				),
				'tooltip_font_size' => array(
					'name'  => 'tooltip_font_size',
					'type'  => 'number',
					'label' => __( 'Tooltip text font size (px)', 'learndash-achievements' ),
					'value' => isset( $this->setting_option_values['tooltip_font_size'] ) ? $this->setting_option_values['tooltip_font_size'] : 12,
				)
			);

			$this->setting_option_fields = apply_filters( 'learndash_achievements_settings_fields',
			                                              $this->setting_option_fields,
			                                              $this->settings_section_key );

			parent::load_settings_fields();
		}

	}

	add_action(
		'learndash_settings_sections_init',
		function () {
			Badge::add_section_instance();
		}
	);
endif;
