<?php

namespace LearnDash\Achievements\Settings;

use LearnDash_Settings_Page;

if ( class_exists( 'LearnDash_Settings_Page' ) ) :
	/**
	 * Register the setting page.
	 */
	class Page extends LearnDash_Settings_Page {
		/**
		 * Page constructor.
		 */
		public function __construct() {
			$this->parent_menu_page_url  = 'edit.php?post_type=ld-achievement';
			$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id      = 'ld-achievements-settings';
			$this->settings_page_title   = __( 'LearnDash Achievements Settings', 'learndash-achievements' );
			$this->settings_tab_title    = __( 'Settings', 'learndash-achievements' );
			$this->settings_tab_priority = 3;

			parent::__construct();
		}
	}

	add_action(
		'learndash_settings_pages_init',
		function() {
			Page::add_page_instance();
		}
	);

endif;
