<?php

namespace LearnDash\Achievements\Settings;

use LearnDash\Achievements\Settings\Table\Students_Achievements_Table;

if ( class_exists( 'LearnDash_Settings_Page' ) ) :
	/**
	 * Create an admin page for group leader can see their students achievements
	 */
	class Students_Achievements extends \LearnDash_Settings_Page {
		/**
		 *
		 */
		public function __construct() {
			$this->parent_menu_page_url  = 'learndash-lms';
			$this->menu_page_capability  = LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK;
			$this->settings_page_id      = 'learndash-achievements-students';
			$this->settings_page_title   = __( 'Achievements', 'learndash-achievements' );
			$this->settings_tab_title    = __( 'Achievements', 'learndash-achievements' );
			$this->show_submit_meta      = false;
			$this->show_quick_links_meta = false;
			parent::__construct();
		}

		/**
		 * Display the content
		 */
		public function show_settings_page() {
			if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
				require_once LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/table/class-students-achievements-table.php';
				$table = new Students_Achievements_Table();
				$table->prepare_items();
				?>
				<div class="wrap">

					<?php echo $table->display(); ?>
				</div>
				<?php
			}
		}
	}

	add_action(
		'learndash_settings_pages_init',
		function () {
			Students_Achievements::add_page_instance();
		}
	);

endif;
