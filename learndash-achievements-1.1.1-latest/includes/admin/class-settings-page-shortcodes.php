<?php

namespace LearnDash\Achievements\Settings;

use LearnDash_Settings_Page;

if ( class_exists( 'LearnDash_Settings_Page' ) ) :

	/**
	 * Class to create the settings page.
	 */
	class Shortcodes extends LearnDash_Settings_Page {

		/**
		 * Public constructor for class
		 */
		public function __construct() {
			$this->parent_menu_page_url  = 'edit.php?post_type=ld-achievement';
			$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id      = 'ld-achievements-shortcodes';
			$this->settings_page_title   = esc_html__( 'Shortcodes', 'learndash-achievements' );
			$this->settings_tab_priority = 4;

			parent::__construct();
		}

		/**
		 * Custom function to show settings page output
		 *
		 * @since 2.4.0
		 */
		public function show_settings_page() {
			?>
		<div  id="achievement-shortcodes"  class="wrap">
			<h2><?php esc_html_e( 'Achievements Shortcodes', 'learndash-achievements' ); ?></h2>
			<div class='sfwd_options_wrapper sfwd_settings_left'>
				<div class='postbox ' id='ld-achievement_metabox'>
					<div class="inside" style="margin: 11px 0; padding: 0 12px 12px;">
					<?php
					echo wp_kses_post(
						'<b>' . __( 'Shortcodes Options', 'learndash-achievements' ) . '</b>
                        <p>' . __( 'You may use shortcodes to display achievements widgets. Provided is a built-in shortcode for displaying user achievements.', 'learndash-achievements' ) . '
                        </p>
                        <br />
                        <p  class="ld-shortcode-header">[ld_achievements_leaderboard]</p>
                        <p>' . __( 'This shortcode takes a parameter named number, which is the total users to be displayed in an achievement leaderboard.', 'learndash-achievements' ) . '
                        </p>
                        <p>' . __( 'Example:', 'learndash-achievements' ) . ' <b>[ld_achievements_leaderboard number="20"]</b> ' . __( 'would display achievement leaderboard of top 20 users.', 'learndash-achievements' ) . '
                        </p>
                        <br />
                        <p class="ld-shortcode-header">[ld_my_achievements]</p>
                        <p>' . __( 'This shortcode displays a list of a logged in user achievements, including achievements icon and its title on icon hover.', 'learndash-achievements' ) . '
                        </p>'
					);
					?>
					</div>
				</div>
			</div>
		</div>
			<?php
		}
	}

endif; // If class_exists check.

add_action(
	'learndash_settings_pages_init',
	function() {
		Shortcodes::add_page_instance();
	}
);
