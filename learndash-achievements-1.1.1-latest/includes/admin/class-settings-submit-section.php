<?php

namespace LearnDash\Achievements\Settings\Section;

use LearnDash_Settings_Section;

if ( class_exists( 'LearnDash_Settings_Section' ) ) :
	/**
	 * Register the submit section.
	 */
	class Submit extends LearnDash_Settings_Section {
		/**
		 * Submit constructor.
		 */
		public function __construct() {
			$this->settings_page_id = 'ld-achievements-settings';
			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'submitdiv';
			// Section label/header.
			$this->settings_section_label = __( 'Save Options', 'learndash-achievements' );

			$this->metabox_context  = 'side';
			$this->metabox_priority = 'high';

			parent::__construct();

			// We override the parent value set for $this->metabox_key because we want the div ID to match the details WordPress
			// value so it will be hidden.
			$this->metabox_key = 'submitdiv';
		}

		/**
		 * Output the meta box
		 */
		public function show_meta_box() {
			?>
		<div id="submitpost" class="submitbox">

			<div id="major-publishing-actions">
				<div id="publishing-action">
					<span class="spinner"></span>
					<?php submit_button( esc_attr( __( 'Save', 'learndash-achievements' ) ), 'primary', 'submit', false ); ?>
				</div>

				<div class="clear"></div>

			</div><!-- #major-publishing-actions -->

		</div><!-- #submitpost -->
			<?php
		}

		/**
		 * This is a requires function.
		 */
		public function load_settings_fields() {

		}
	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			Submit::add_section_instance();
		}
	);

endif;
