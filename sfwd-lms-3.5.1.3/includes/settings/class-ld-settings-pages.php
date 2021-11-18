<?php
/**
 * LearnDash Settings Page Abstract Class.
 *
 * @since 2.4.0
 * @package LearnDash\Settings\Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LearnDash_Settings_Page' ) ) {
	/**
	 * Absract for LearnDash Settings Pages.
	 *
	 * @since 2.4.0
	 */
	abstract class LearnDash_Settings_Page {

		/**
		 * Variable to hold all settings page instances.
		 *
		 * @var array $_instances
		 */
		protected static $_instances = array(); //phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

		/**
		 * Match the parent menu below LearnDash main menu. This will be the URL as in
		 * edit.php?post_type=sfwd-courses, admin.php?page=learndash-lms-reports, admin.php?page=learndash_lms_settings
		 *
		 * @var string $parent_menu_page_url string.
		 */
		protected $parent_menu_page_url = '';

		/**
		 * Match the user capability to view this page
		 *
		 * @var string $menu_page_capability
		 */
		protected $menu_page_capability = LEARNDASH_ADMIN_CAPABILITY_CHECK;

		/**
		 * Match the WP Screen ID. DO NOT SET. This is set when from add_submenu_page().
		 * The value set via WP will be somethiing like 'sfwd-courses_page_courses-options'
		 * for the URL admin.php?page=courses-options because it will reside within the
		 * sfwd-courses submenu.
		 *
		 * @var string $settings_screen_id
		 */
		protected $settings_screen_id = '';

		/**
		 * Match the URL 'page=' parameter value. For example admin.php?page=learndash-lms-reports
		 * value will be 'learndash-lms-reports'
		 *
		 * @var string $settings_page_id
		 */
		protected $settings_page_id = '';

		/**
		 * Title for page <h1></h1> string
		 *
		 * @var string $settings_page_title
		 */
		protected $settings_page_title = '';

		/**
		 * Title for tab string
		 *
		 * @var string $settings_tab_title
		 */
		protected $settings_tab_title = '';

		/**
		 * Priority for tab
		 *
		 * @var integer $settings_tab_priority
		 */
		protected $settings_tab_priority = 30;

		/**
		 * The number of columns to show. Most admin screens will be 2. But we set to 1 for the initial.
		 *
		 * @var integer $settings_columns
		 */
		protected $settings_columns = 2;

		/**
		 * Wether to show the Submit metabox.
		 *
		 * @var boolean $show_submit_meta
		 */
		protected $show_submit_meta = true;

		/**
		 * Wether to show the Quick Links metabox.
		 *
		 * @var boolean $show_quick_links_meta
		 */
		protected $show_quick_links_meta = true;

		/**
		 * Wether to show wrap all settings in a <form></form>.
		 *
		 * @var boolean $settings_form_wrap
		 */
		protected $settings_form_wrap = true;

		/**
		 * Public constructor for class
		 *
		 * @since 2.4.0
		 */
		public function __construct() {
			global $learndash_pages;

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			add_action( 'learndash_admin_tabs_set', array( $this, 'admin_tabs' ), 10 );

			if ( empty( $this->settings_tab_title ) ) {
				$this->settings_tab_title = $this->settings_page_title;
			}

			if ( ( ! empty( $this->settings_page_id ) ) && ( ! isset( $learndash_pages[ $this->settings_page_id ] ) ) ) {
				$learndash_pages[] = $this->settings_page_id;
			}
		}

		/**
		 * Function to get a specific setting page instance.
		 *
		 * @since 2.4.0
		 *
		 * @param string $page_key Page key to get instance of.
		 *
		 * @return object page instance
		 */
		final public static function get_page_instance( $page_key = '' ) {
			if ( ! empty( $page_key ) ) {
				if ( isset( self::$_instances[ $page_key ] ) ) {
					return self::$_instances[ $page_key ];
				}
			}
		}

		/**
		 * Function to set/add setting page to instances array.
		 *
		 * @since 2.4.0
		 */
		final public static function add_page_instance() {
			$section_class = get_called_class();

			if ( ! isset( self::$_instances[ $section_class ] ) ) {
				self::$_instances[ $section_class ] = new $section_class();
			}
		}

		/**
		 * Action hook to handle admin_init processing from WP.
		 *
		 * @since 2.4.0
		 */
		public function admin_init() {

			/**
			 * Fires on LearnDash settings page init.
			 *
			 * @param string $setting_page_id Settings page ID.
			 */
			do_action( 'learndash_settings_page_init', $this->settings_page_id );

			if ( true === $this->show_submit_meta ) {
				$submit_obj = new LearnDash_Settings_Section_Side_Submit(
					array(
						'settings_screen_id' => $this->settings_screen_id,
						'settings_page_id'   => $this->settings_page_id,
					)
				);
			}

			if ( true === $this->show_quick_links_meta ) {
				$ql_obj = new LearnDash_Settings_Section_Side_Quick_Links(
					array(
						'settings_screen_id' => $this->settings_screen_id,
						'settings_page_id'   => $this->settings_page_id,
					)
				);
			}
		}

		/**
		 * Action hook to handle admin_menu processing from WP.
		 *
		 * @since 2.4.0
		 */
		public function admin_menu() {
			if ( ! $this->settings_screen_id ) {
				$this->settings_screen_id = add_submenu_page(
					$this->parent_menu_page_url,
					$this->settings_page_title,
					$this->settings_page_title,
					$this->menu_page_capability,
					$this->settings_page_id,
					array( $this, 'show_settings_page' )
				);
			}
			add_action( 'load-' . $this->settings_screen_id, array( $this, 'load_settings_page' ) );
		}

		/**
		 * Action hook to handle admin_tabs processing from LearnDash.
		 *
		 * @since 2.4.0
		 *
		 * @param string $admin_menu_section Current admin menu section.
		 */
		public function admin_tabs( $admin_menu_section ) {
			if ( $admin_menu_section === $this->parent_menu_page_url ) {
				learndash_add_admin_tab_item(
					$this->parent_menu_page_url,
					array(
						'id'   => $this->settings_screen_id,
						'link' => add_query_arg( array( 'page' => $this->settings_page_id ), 'admin.php' ),
						'cap'  => $this->menu_page_capability,
						'name' => ! empty( $this->settings_tab_title ) ? $this->settings_tab_title : $this->settings_page_title,
					),
					$this->settings_tab_priority
				);
			}
		}

		/**
		 * Action hook to handle current settings page load.
		 *
		 * @since 2.4.0
		 */
		public function load_settings_page() {
			global $learndash_assets_loaded;

			if ( defined( 'LEARNDASH_SETTINGS_SECTION_TYPE' ) && ( 'metabox' === LEARNDASH_SETTINGS_SECTION_TYPE ) ) {
				wp_enqueue_script( 'common' );
				wp_enqueue_script( 'wp-lists' );
				wp_enqueue_script( 'postbox' );

				/**
				 * Fires on adding setting metaboxes.
				 *
				 * @param string $setting_screen_id Settings screen ID.
				 */
				do_action( 'learndash_add_meta_boxes', $this->settings_screen_id );
				add_action( 'admin_footer-' . $this->settings_screen_id, array( $this, 'load_footer_scripts' ) );
				add_filter( 'screen_layout_columns', array( $this, 'screen_layout_column' ), 10, 2 );
			}

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();

			wp_enqueue_style(
				'learndash_style',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/style' . learndash_min_asset() . '.css',
				array(),
				LEARNDASH_SCRIPT_VERSION_TOKEN
			);
			wp_style_add_data( 'learndash_style', 'rtl', 'replace' );
			$learndash_assets_loaded['styles']['learndash_style'] = __FUNCTION__;

			wp_enqueue_style(
				'sfwd-module-style',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/css/sfwd_module' . learndash_min_asset() . '.css',
				array(),
				LEARNDASH_SCRIPT_VERSION_TOKEN
			);
			wp_style_add_data( 'sfwd-module-style', 'rtl', 'replace' );
			$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;

			wp_enqueue_script(
				'sfwd-module-script',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/sfwd_module' . learndash_min_asset() . '.js',
				array( 'jquery' ),
				LEARNDASH_SCRIPT_VERSION_TOKEN,
				true
			);
			$learndash_assets_loaded['scripts']['sfwd-module-script'] = __FUNCTION__;

			wp_localize_script( 'sfwd-module-script', 'sfwd_data', array() );

			learndash_admin_settings_page_assets();

			if ( isset( $_GET['ld_reset_metaboxes'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				delete_user_meta( get_current_user_id(), 'closedpostboxes_' . $this->settings_screen_id );
				delete_user_meta( get_current_user_id(), 'metaboxhidden_' . $this->settings_screen_id );
				delete_user_meta( get_current_user_id(), 'meta-box-order_' . $this->settings_screen_id );
			}

			/**
			 * Fires on LearnDash settings page load.
			 *
			 * @param string $setting_screen_id Settings screen ID.
			 * @param string $setting_page_id   Settings page ID.
			 */
			do_action( 'learndash-settings-page-load', $this->settings_screen_id, $this->settings_page_id );
		}

		/**
		 * Action hook to handle current settings page layout columns.
		 *
		 * @since 2.4.0
		 *
		 * @param integer $columns Number of columns to show.
		 * @param Object  $screen Current screen object.
		 *
		 * @return integer $columns
		 */
		public function screen_layout_column( $columns = false, $screen_id = '' ) {
			if ( $screen_id == $this->settings_screen_id ) {
				$columns[ $screen_id ] = $this->settings_columns;
			}

			/**
			 * Add this filter to override the get user option logic. This is to force
			 * the screen layout option for user who don't have this defined.
			 *
			 * @since 2.6.0
			 */
			add_filter(
				"get_user_option_screen_layout_{$screen_id}",
				function( $option_value = '', $option_key = '' ) {
					if ( "screen_layout_{$this->settings_screen_id}" === $option_key ) {
						$option_value = $this->settings_columns;
					}
					return $option_value;
				},
				1,
				2
			);

			return $columns;
		}

		/**
		 * Action hook to handle footer JS/CSS added footer
		 *
		 * @since 2.4.0
		 */
		public function load_footer_scripts() {
			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery( function($) {
					// toggle
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					postboxes.add_postbox_toggles( '<?php echo esc_attr( $this->settings_screen_id ); ?>' );
					// display spinner
					$('#fx-smb-form').on( 'submit', function() {
						$('#publishing-action .spinner').css('display','inline');
					});
					// confirm before reset
					$('.learndash-settings-page-wrap .submitdelete').on('click', function() {
						var confirm_message = $(this).data('confirm');
						if (typeof confirm_message !== 'undefined') {
							return confirm( confirm_message );
						}
					});

					if ( jQuery( '#side-sortables').length ) {
						if ( jQuery( '#side-sortables div.postbox').length ) {
							jQuery( '#side-sortables').removeClass('empty-container');
						}
					}
				});
				//]]>
			</script>
			<?php

			/**
			 * Fires after settings page footer scripts.
			 *
			 * @param string $setting_screen_id Settings screen ID.
			 * @param string $setting_page_id   Settings page ID.
			 */
			do_action( 'learndash_settings_page_footer_scripts', $this->settings_screen_id, $this->settings_page_id );
		}

		/**
		 * Fucntion to handle showing of Settings page. This is the main function for all visible
		 * output. Extending classes can implement its own function.
		 *
		 * @since 2.4.0
		 */
		public function show_settings_page() {
			if ( defined( 'LEARNDASH_SETTINGS_SECTION_TYPE' ) && ( 'metabox' === LEARNDASH_SETTINGS_SECTION_TYPE ) ) {
				?>
				<div class="wrap learndash-settings-page-wrap">

					<?php settings_errors(); ?>
					<?php
					/**
					 * Fires before the settings page title.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_before_title', $this->settings_screen_id, $this->settings_page_id );
					?>
					<?php echo wp_kses_post( $this->get_admin_page_title() ); ?>
					<?php
					/**
					 * Fires after the settings page title.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_after_title', $this->settings_screen_id, $this->settings_page_id );
					?>

					<?php
					/**
					 * Fires before the settings page form.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_before_form', $this->settings_screen_id, $this->settings_page_id );
					?>
					<?php echo $this->get_admin_page_form( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML. ?>
					<?php
					/**
					 * Fires inside settings page form in the top.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_inside_form_top', $this->settings_screen_id, $this->settings_page_id );
					?>

						<?php settings_fields( $this->settings_page_id ); ?>
						<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
						<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

						<div id="poststuff">
							<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
								<div id="postbox-container-1" class="postbox-container">
									<?php do_meta_boxes( $this->settings_screen_id, 'side', null ); ?>
								</div>
								<div id="postbox-container-2" class="postbox-container">
									<?php
									/**
									 * Fires before the settings page meta boxes.
									 *
									 * @param string $setting_screen_id Settings screen ID.
									 * @param string $setting_page_id   Settings page ID.
									 */
									do_action( 'learndash_settings_page_before_metaboxes', $this->settings_screen_id, $this->settings_page_id );
									?>
									<?php do_meta_boxes( $this->settings_screen_id, 'normal', null ); ?>
									<?php do_meta_boxes( $this->settings_screen_id, 'advanced', null ); ?>
									<?php
									/**
									 * Fires after the settings page meta boxes.
									 *
									 * @param string $setting_screen_id Settings screen ID.
									 * @param string $setting_page_id   Settings page ID.
									 */
									do_action( 'learndash_settings_page_after_metaboxes', $this->settings_screen_id, $this->settings_page_id );
									?>
								</div>
							</div>
							<br class="clear">
						</div>
					<?php
					/**
					 * Fires inside settings page form at the bottom.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_inside_form_bottom', $this->settings_screen_id, $this->settings_page_id );
					?>
					<?php echo $this->get_admin_page_form( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML. ?>
					<?php
					/**
					 * Fires after the settings page form.
					 *
					 * @param string $setting_screen_id Settings screen ID.
					 * @param string $setting_page_id   Settings page ID.
					 */
					do_action( 'learndash_settings_page_after_form', $this->settings_screen_id, $this->settings_page_id );
					?>
				</div>
				<?php

			} else {
				?>
				<div class="wrap learndash-settings-page-wrap">
					<?php settings_errors(); ?>

					<?php echo wp_kses_post( $this->get_admin_page_title() ); ?>

					<?php echo $this->get_admin_page_form( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML. ?>
					<?php
						// This prints out all hidden setting fields.
						settings_fields( $this->settings_page_id );

						do_settings_sections( $this->settings_page_id );
					?>
					<?php submit_button( esc_html__( 'Save Changes', 'learndash' ) ); ?>
					<?php echo $this->get_admin_page_form( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML. ?>
				</div>
				<?php
			}
		}

		/**
		 * Class utility function to return the settings page title.
		 *
		 * @since 2.5.0
		 *
		 * @return string page title.
		 */
		public function get_admin_page_title() {
			/**
			 * Filters whether the admin settings page title should be displayed or not.
			 *
			 * @param array $flag Whether to display page title or not.
			 */
			if ( true === apply_filters( 'learndash_admin_page_title_should_display', false ) ) {
				/**
				 * Filters admin settings page title HTML output.
				 *
				 * @param string $title_output The admin settings title page.
				 */
				return apply_filters( 'learndash_admin_page_title', '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' );
			} else {
				return '<h1 class="learndash-empty-page-title"></h1>';
			}
		}

		/**
		 * Class utility function to return the form wrapper. Supports
		 * the beginning <form> an ending </form>.
		 *
		 * @since 2.5.0
		 *
		 * @param boolean $start Flag to indicate if showing start or end of form.
		 *
		 * @return string form HTML.
		 */
		public function get_admin_page_form( $start = true ) {
			if ( true === $this->settings_form_wrap ) {
				if ( true === $start ) {
					/**
					 * Filters the HTML output for admin page form.
					 *
					 * @param string  $form_output The HTML output for admin page form.
					 * @param boolean $start       A flag to indicate whether it is start or end of the form.
					 */
					return apply_filters( 'learndash_admin_page_form', '<form id="learndash-settings-page-form" method="post" action="options.php">', $start );
				} else {
					/** This filter is documented in includes/settings/class-ld-settings-pages.php  */
					return apply_filters( 'learndash_admin_page_form', '</form>', $start );
				}
			}
		}

		// End of functions.
	}
}

/**
 * Enqueues the settings page styles and scripts.
 *
 * @since 3.0.0
 *
 * @global array $learndash_assets_loaded An array of loaded assets.
 */
function learndash_admin_settings_page_assets() {
	global $learndash_assets_loaded;
	/** This filter is documented in includes/class-ld-lms.php */
	if ( ( defined( 'LEARNDASH_SELECT2_LIB' ) ) && ( true === apply_filters( 'learndash_select2_lib', LEARNDASH_SELECT2_LIB ) ) ) {
		if ( ! isset( $learndash_assets_loaded['styles']['learndash-select2-jquery-style'] ) ) {
			wp_enqueue_style(
				'learndash-select2-jquery-style',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/vendor/select2-jquery/css/select2.min.css',
				array(),
				LEARNDASH_SCRIPT_VERSION_TOKEN
			);
			$learndash_assets_loaded['styles']['learndash-select2-jquery-style'] = __FUNCTION__;
		}

		if ( ! isset( $learndash_assets_loaded['scripts']['learndash-select2-jquery-script'] ) ) {
			wp_enqueue_script(
				'learndash-select2-jquery-script',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/vendor/select2-jquery/js/select2.full.min.js',
				array( 'jquery' ),
				LEARNDASH_SCRIPT_VERSION_TOKEN,
				true
			);
			$learndash_assets_loaded['scripts']['learndash-select2-jquery-script'] = __FUNCTION__;
		}
	}

	if ( ! isset( $learndash_assets_loaded['styles']['learndash-admin-settings-page'] ) ) {
		wp_enqueue_style(
			'learndash-admin-settings-page',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-settings-page' . learndash_min_asset() . '.css',
			array(),
			LEARNDASH_SCRIPT_VERSION_TOKEN
		);
		wp_style_add_data( 'learndash-admin-settings-page', 'rtl', 'replace' );
		$learndash_assets_loaded['styles']['learndash-admin-settings-page'] = __FUNCTION__;
	}

	if ( ! isset( $learndash_assets_loaded['scripts']['learndash-admin-settings-page'] ) ) {
		wp_enqueue_script(
			'learndash-admin-settings-page',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-settings-page' . learndash_min_asset() . '.js',
			array( 'jquery', 'wp-color-picker' ),
			LEARNDASH_SCRIPT_VERSION_TOKEN,
			true
		);
		$learndash_assets_loaded['scripts']['learndash-admin-settings-page'] = __FUNCTION__;

		$script_data = array();

		if ( ! isset( $script_data['ajaxurl'] ) ) {
			$script_data['ajaxurl'] = admin_url( 'admin-ajax.php' );
		}
		if ( ! isset( $script_data['admin_notice_settings_fields_errors'] ) ) {
			$script_data['admin_notice_settings_fields_errors_container'] = '<div id="learndash-settings-fields-notice-errors" class="learndash-settings-fields-notice-errors notice notice-error"><p class="errors-header">' . esc_html__( 'You have errors on the following settings', 'learndash' ) . '</p><ul class="errors-list"></ul></div>';
		}

		if ( ! isset( $script_data['selec2_language'] ) ) {
			$script_data['selec2_language'] = array(
				'loadingMore'    => esc_html__( 'Loading more results…', 'learndash' ),
				'noResults'      => esc_html__( 'No results found', 'learndash' ),
				'searching'      => esc_html__( 'Searching…', 'learndash' ),
				'removeAllItems' => esc_html__( 'Remove all items', 'learndash' ),
				'removeItem'     => esc_html__( 'Remove item', 'learndash' ),
			);
		}

		if ( ! isset( $script_data['settings_fields_errors'] ) ) {
			$script_data['settings_fields_errors'] = array(
				'number' => array(
					'invalid'  => esc_html__( 'Entered value is invalid.', 'learndash' ),
					'empty'    => esc_html__( 'Value cannot be empty.', 'learndash' ),
					'negative' => esc_html__( 'Entered value cannot be negative.', 'learndash' ),
					'decimal'  => esc_html__( 'Entered value cannot contain decimal.', 'learndash' ),
					'minimum'  => esc_html__( 'Entered value less than minimum.', 'learndash' ),
					'maximum'  => esc_html__( 'Entered value greater than maximum.', 'learndash' ),
				),
			);
		}

		/**
		 * Filters admin settings script data.
		 *
		 * @param array $script_data An array of script data to be localized.
		 */
		$script_data = apply_filters( 'learndash_admin_settings_data', $script_data );
		if ( ( empty( $script_data ) ) || ( ! is_array( $script_data ) ) ) {
			$script_data = array();
		}

		$script_data = array( 'json' => wp_json_encode( $script_data ) );
		wp_localize_script( 'learndash-admin-settings-page', 'learndash_admin_settings_data', $script_data );
	}
}

add_action(
	'wp_ajax_learndash_settings_select2_query',
	function() {

		$result_array = array(
			'items'       => array(),
			'total_items' => 0,
			'page'        => 1,
			'total_pages' => 1,
		);

		if ( ! current_user_can( 'read' ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		if ( ( ! isset( $_POST['query_data'] ) ) || ( empty( $_POST['query_data'] ) ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		$post_query_data = $_POST['query_data'];

		if ( ( ! isset( $post_query_data['nonce'] ) ) || ( empty( $post_query_data['nonce'] ) ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		$post_query_data_nonce = $post_query_data['nonce'];
		unset( $post_query_data['nonce'] );

		if ( ( ! isset( $post_query_data['settings_element'] ) ) || ( empty( $post_query_data['settings_element'] ) ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		$post_query_data_json = wp_json_encode( $post_query_data['settings_element'], JSON_FORCE_OBJECT );
		if ( ! wp_verify_nonce( $post_query_data_nonce, $post_query_data_json ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		if ( ( ! isset( $post_query_data['query_args'] ) ) || ( empty( $post_query_data['query_args'] ) ) ) {
			echo wp_json_encode( $result_array );
			wp_die();
		}

		$query_args = $post_query_data['query_args'];

		$query_args['paged'] = 1;
		if ( isset( $_POST['page'] ) ) {
			$query_args['paged'] = absint( $_POST['page'] );
		}
		if ( empty( $query_args['paged'] ) ) {
			$query_args['paged'] = 1;
		}

		if ( ( isset( $_POST['search'] ) ) && ( ! empty( $_POST['search'] ) ) ) {
			$query_args['s'] = esc_attr( $_POST['search'] );
		}

		if ( isset( $query_args['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = absint( $query_args['posts_per_page'] );
		}
		if ( empty( $query_args['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = 10;
		}

		if ( ! isset( $query_args['order'] ) ) {
			$query_args['order'] = 'ASC';
		}
		if ( ! isset( $query_args['orderby'] ) ) {
			$query_args['orderby'] = 'title';
		}

		$query_results = new WP_Query( $query_args );
		if ( ( $query_results ) && ( is_a( $query_results, 'WP_Query' ) ) ) {
			$result_array['items'] = array();
			if ( ( property_exists( $query_results, 'posts' ) ) && ( is_array( $query_results->posts ) ) && ( ! empty( $query_results->posts ) ) ) {
				foreach ( $query_results->posts as $item ) {
					$result_array['items'][] = array(
						'id'   => $item->ID,
						'text' => $item->post_title,
					);
				}
			}
			if ( property_exists( $query_results, 'found_posts' ) ) {
				$result_array['total_items'] = absint( $query_results->found_posts );
			}
			if ( property_exists( $query_results, 'max_num_pages' ) ) {
				$result_array['total_pages'] = absint( $query_results->max_num_pages );
			}
			$result_array['page'] = absint( $query_args['paged'] );
		}

		echo wp_json_encode( $result_array );
		wp_die();
	},
	10
);
