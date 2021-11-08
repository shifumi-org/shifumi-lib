<?php
/**
 * LearnDash Settings Page Data Upgrades.
 *
 * @since 2.6.0
 * @package LearnDash\Settings\Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Page' ) ) && ( ! class_exists( 'LearnDash_Settings_Page_Data_Upgrades' ) ) ) {
	/**
	 * Class LearnDash Settings Page Data Upgrades.
	 *
	 * @since 2.6.0
	 */
	class LearnDash_Settings_Page_Data_Upgrades extends LearnDash_Settings_Page {
		/**
		 * Private flag for when admin notices have been
		 * show. This prevent multiple admin notices.
		 *
		 * @var boolean $admin_notice_shown
		 */
		private static $admin_notice_shown = false;

		/**
		 * Public constructor for class
		 *
		 * @since 2.6.0
		 */
		public function __construct() {
			$this->parent_menu_page_url  = 'admin.php?page=learndash_lms_settings';
			$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id      = 'learndash_data_upgrades';
			$this->settings_page_title   = esc_html__( 'Data Upgrades', 'learndash' );
			$this->settings_tab_title    = $this->settings_page_title;
			$this->settings_tab_priority = 30;
			$this->settings_columns      = 1;
			$this->show_submit_meta      = false;
			$this->show_quick_links_meta = false;

			parent::__construct();
		}

		/**
		 * Action function called when Add-ons page is loaded.
		 *
		 * @since 2.6.0
		 */
		public function load_settings_page() {
			global $learndash_assets_loaded;

			parent::load_settings_page();

			wp_enqueue_style(
				'learndash-admin-style',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-style' . learndash_min_asset() . '.css',
				array(),
				LEARNDASH_SCRIPT_VERSION_TOKEN
			);
			wp_style_add_data( 'learndash-admin-style', 'rtl', 'replace' );
			$learndash_assets_loaded['styles']['learndash-admin-style'] = __FUNCTION__;

			wp_enqueue_script(
				'learndash-admin-settings-data-upgrades-script',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-settings-data-upgrades' . learndash_min_asset() . '.js',
				array( 'jquery' ),
				LEARNDASH_SCRIPT_VERSION_TOKEN,
				true
			);

			$learndash_assets_loaded['scripts']['learndash-admin-settings-data-upgrades-script'] = __FUNCTION__;

			add_action( 'admin_notices', array( $this, 'show_upgrade_admin_notice' ) );
		}

		/**
		 * Shows Data Upgrade admin notice.
		 *
		 * @version 3.2.0
		 */
		public function show_upgrade_admin_notice() {
			if ( true !== self::$admin_notice_shown ) {
				self::$admin_notice_shown = true;

				?>
				<div class="notice notice-error is-dismissible">
					<p>
					<?php
					echo esc_html__( 'The Data Upgrades should only be run if prompted or advised by LearnDash Support. There is no need to re-run the Data Upgrades every time you update LearnDash core or one of the add-ons. Re-running the data upgrades when not needed can result in data corruption.', 'learndash' );
					?>
					</p>
				</div>
				<?php
			}
		}

		// End of functions.
	}
}
add_action(
	'learndash_settings_pages_init',
	function() {
		LearnDash_Settings_Page_Data_Upgrades::add_page_instance();
	}
);



