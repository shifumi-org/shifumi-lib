<?php

namespace uncanny_learndash_reporting;

use ReflectionClass;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class AdminMenu
 *
 * @package uncanny_custom_reporting
 */
class ReportingAdminMenu extends Boot {

	private static $tincan_database;
	private static $tincan_opt_per_pages;
	private static $template_data = [];
	private static $tincan_show = '';
	private static $groups_query;
	private static $isolated_group = 0;
	private static $xapi_report_columns = [];

	/**
	 * class constructor
	 */
	public function __construct() {
		self::$xapi_report_columns = [
			'group'            => [
				'label'   => __( 'Group', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'user'             => [
				'label'   => __( 'User', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'course'           => [
				'label'   => __( 'Course', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'module'           => [
				'label'   => __( 'Module', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'question'         => [
				'label'   => __( 'Question', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'result'           => [
				'label'   => __( 'Result', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'score'            => [
				'label'   => __( 'Score', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'date_time'        => [
				'label'   => __( 'Date Time', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => true,
				'value'   => true,
			],
			'choices'          => [
				'label'   => __( 'Choices', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			],
			'correct_response' => [
				'label'   => __( 'Correct Response', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			],
			'user_response'    => [
				'label'   => __( 'User Response', 'uncanny-learndash-reporting' ),
				'type'    => 'checkbox',
				'default' => false,
				'value'   => false,
			],
		];
		// Setup Theme Options Page Menu in Admin
		if ( is_admin() ) {
			// TinCan CSV
			self::csv_export();
			add_action( 'admin_init', [ __CLASS__, 'tincan_change_per_page' ] );

			add_action( 'admin_menu', [ __CLASS__, 'register_options_menu_page' ], 10 );
			add_action( 'admin_init', [ __CLASS__, 'register_options_menu_page_settings' ] );
			add_action( 'admin_enqueue_scripts', [ __CLASS__, 'scripts' ], 99999 );
			// load columns settings from user meta.
			add_filter( 'screen_options_show_screen', '__return_true' );
			add_filter( 'screen_settings', [ $this, 'filter__screen_settings' ], 10, 2 );

			if ( ! empty( $_GET['page'] ) && 'uncanny-learnDash-reporting' === $_GET['page'] ) {
				add_filter( 'set_screen_option_xapi_report_columns', [ $this, 'filter__set_screen_option' ], 10, 3 );
			}

		} else {
			self::csv_export();
			add_action( 'init', [ __CLASS__, 'tincan_change_per_page' ] );
			add_shortcode( 'tincanny', [ __CLASS__, 'frontend_tincanny' ] );
			add_action( 'wp_enqueue_scripts', [ __CLASS__, 'scripts' ] );
			add_action( 'init', [ __CLASS__, 'render_callback' ] );

			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) and $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
		}
	}

	private static function csv_export() {
		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'csv' ) {
			add_action( 'init', [ __CLASS__, 'execute_csv_export' ] );
		}
		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'csv-xapi' ) {
			add_action( 'init', [ __CLASS__, 'execute_csv_export_xapi' ] );
		}
	}

	public static function render_callback() {
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type( 'tincanny-learndash-reporting/frontend-course-reports', [
				'render_callback' => [ __CLASS__, 'frontend_tincanny_block' ],
			] );
		}
	}

	/*
	* Render to Screen Options string.
	*/

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = esc_html__( 'Tin Canny Reporting for LearnDash', 'uncanny-learndash-reporting', 'uncanny-learndash-reporting' );
		$menu_title = esc_html__( 'Tin Canny Reporting', 'uncanny-learndash-reporting', 'uncanny-learndash-reporting' );

		$capability = apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' );

		$menu_slug  = 'uncanny-learnDash-reporting';
		$function   = [ __CLASS__, 'options_menu_page_output' ];

		$icon_url
			= 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDU4MSA2NDAiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDU4MSA2NDAiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTUyNi40IDM0LjFjMC42IDUgMSAxMC4xIDEuMyAxNS4xIDAuNSAxMC4zIDEuMiAyMC42IDAuOCAzMC45LTAuNSAxMS41LTEgMjMtMi4xIDM0LjQtMi42IDI2LjctNy44IDUzLjMtMTYuNSA3OC43LTcuMyAyMS4zLTE3LjEgNDEuOC0yOS45IDYwLjQtMTIgMTcuNS0yNi44IDMzLTQzLjggNDUuOS0xNy4yIDEzLTM2LjcgMjMtNTcuMSAyOS45LTI1LjEgOC41LTUxLjUgMTIuNy03Ny45IDEzLjggNzAuMyAyNS4zIDEwNi45IDEwMi44IDgxLjYgMTczLjEtMTguOSA1Mi42LTY4LjEgODguMS0xMjQgODkuNWgtNi4xYy0xMS4xLTAuMi0yMi4xLTEuOC0zMi45LTQuNy0yOS40LTcuOS01NS45LTI2LjMtNzMuNy01MC45LTI5LjItNDAuMi0zNC4xLTkzLjEtMTIuNi0xMzgtMjUgMjUuMS00NC41IDU1LjMtNTkuMSA4Ny40LTguOCAxOS43LTE2LjEgNDAuMS0yMC44IDYxLjEtMS4yLTE0LjMtMS4yLTI4LjYtMC42LTQyLjkgMS4zLTI2LjYgNS4xLTUzLjIgMTIuMi03OC45IDUuOC0yMS4yIDEzLjktNDEuOCAyNC43LTYwLjlzMjQuNC0zNi42IDQwLjYtNTEuM2MxNy4zLTE1LjcgMzcuMy0yOC4xIDU5LjEtMzYuOCAyNC41LTkuOSA1MC42LTE1LjIgNzYuOC0xNy4yIDEzLjMtMS4xIDI2LjctMC44IDQwLjEtMi4zIDI0LjUtMi40IDQ4LjgtOC40IDcxLjMtMTguMyAyMS05LjIgNDAuNC0yMS44IDU3LjUtMzcuMiAxNi41LTE0LjkgMzAuOC0zMi4xIDQyLjgtNTAuOCAxMy0yMC4yIDIzLjQtNDIuMSAzMS42LTY0LjcgNy42LTIxLjEgMTMuNC00Mi45IDE2LjctNjUuM3ptLTI3OS40IDMyOS41Yy0xOC42IDEuOC0zNi4yIDguOC01MC45IDIwLjQtMTcuMSAxMy40LTI5LjggMzIuMi0zNi4yIDUyLjktNy40IDIzLjktNi44IDQ5LjUgMS43IDczIDcuMSAxOS42IDE5LjkgMzcuMiAzNi44IDQ5LjYgMTQuMSAxMC41IDMwLjkgMTYuOSA0OC40IDE4LjZzMzUuMi0xLjYgNTEtOS40YzEzLjUtNi43IDI1LjQtMTYuMyAzNC44LTI4LjEgMTAuNi0xMy40IDE3LjktMjkgMjEuNS00NS43IDQuOC0yMi40IDIuOC00NS43LTUuOC02Ni45LTguMS0yMC0yMi4yLTM3LjYtNDAuMy00OS4zLTE4LTExLjctMzkuNS0xNy02MS0xNS4xeiIgZmlsbD0iIzgyODc4QyIvPjxwYXRoIGQ9Im0yNDIuNiA0MDIuNmM2LjItMS4zIDEyLjYtMS44IDE4LjktMS41LTExLjQgMTEuNC0xMi4yIDI5LjctMS44IDQyIDExLjIgMTMuMyAzMS4xIDE1LjEgNDQuNCAzLjkgNS4zLTQuNCA4LjktMTAuNCAxMC41LTE3LjEgMTIuNCAxNi44IDE2LjYgMzkuNCAxMSA1OS41LTUgMTguNS0xOCAzNC42LTM1IDQzLjUtMzQuNSAxOC4yLTc3LjMgNS4xLTk1LjUtMjkuNS0xLTItMi00LTIuOS02LjEtOC4xLTE5LjYtNi41LTQzIDQuMi02MS4zIDEwLTE3IDI2LjgtMjkuMiA0Ni4yLTMzLjR6IiBmaWxsPSIjODI4NzhDIi8+PC9zdmc+';

		$position = 81; // 81 - Above Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	}

	/*
	* Validate Screen Option on update.
	*/

	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_reporting-group', 'uncanny_reporting_active_classes' );
	}

	/*
	 * Whitelisted Options that are saved on the page
	 */

	/**
	 * Populates an array of classes in internal and external file in the classes folder
	 *
	 * @param mixed (Array || false) $external_classes
	 *
	 * @return array
	 */
	public static function get_available_classes() {

		$class_details = [];

		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';

		$files = scandir( $path );

		$internal_details = self::get_class_details( $path, $files, __NAMESPACE__ );

		$class_details = array_merge( $class_details, $internal_details );

		return $class_details;
	}

	private static function get_class_details( $path, $files, $name_space ) {

		$details = [];

		foreach ( $files as $file ) {
			if ( is_dir( $path . $file ) || '..' === $file || '.' === $file ) {
				continue;
			}

			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = $name_space . '\\' . str_replace( ' ', '', $class_name );

			// test for required functions
			$class = new ReflectionClass( $class_name );
			if ( $class->implementsInterface( 'uncanny_learndash_reporting\RequiredFunctions' ) ) {
				$details[ $class_name ] = $class_name::get_details();
			} else {
				$details[ $class_name ] = false;
			}
		}

		return $details;

	}

	/*
	 * get_class_details
	 * @param string $path
	 * @param array $files
	 * @param string $namespace
	 *
	 * @return array $details
	 */

	public static function scripts( $hook ) {

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			return;
		}
		if ( is_admin() ) {
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
			} else {
				$screen = '';
			}
		}

		if ( ! wp_script_is( 'wp-hooks', $list = 'enqueued' ) ) {
			wp_enqueue_script( 'wp-hooks' );
		}

		global $post;

		$page_title = sanitize_title(
			esc_html__( 'Tin Canny Reporting', 'uncanny-learndash-reporting', 'uncanny-learndash-reporting' )
		);

		$page_is_to_load_scripts = ( 'toplevel_page_uncanny-learnDash-reporting' === $hook || $page_title . '_page_snc_options' === $hook
									 || ( ! is_admin()
										  && $post instanceof \WP_Post
										  && has_shortcode( $post->post_content, 'tincanny' )
									 )
		);

		$page_is_to_load_scripts = apply_filters( 'tc_page_is_to_load_scripts', $page_is_to_load_scripts, $hook, $post );

		if ( $page_is_to_load_scripts ) {
			// Admin Page Reporting UI Files

			// Admin CSS
			// TODO add debug and minify
			wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), [], UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), [], UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'data-tables', Config::get_admin_css( 'datatables.min.css' ), [], UNCANNY_REPORTING_VERSION );

			wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), [], UNCANNY_REPORTING_VERSION );

			$dynamic_css = self::get_dynamic_css();
			wp_add_inline_style( 'reporting-admin', $dynamic_css );

			wp_enqueue_style( 'reporting-admin' );

			// Admin JS
			wp_register_script( 'reporting_js_handle', Config::get_admin_js( 'reporting' ), [ 'jquery', 'wp-hooks', 'wp-i18n' ], UNCANNY_REPORTING_VERSION, true );

			// Add custom colors to use them in the JS
			$ui = self::get_ui_data();
			wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

			// Add Tin Canny data
			wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );

			if ( isset( $_GET['group_id'] ) ) {
				$isolated_group_id = absint( $_GET['group_id'] );
			} else {
				$isolated_group_id = 0;
			}

			// Get Tin Canny settings
			$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

			// API data
			$reporting_api_setup = [
				'root'                          => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
				'nonce'                         => \wp_create_nonce( 'wp_rest' ),
				'learnDashLabels'               => ReportingApi::get_labels(),
				'isolated_group_id'             => $isolated_group_id,
				'isAdmin'                       => is_admin(),
				'editUsers'                     => current_user_can( 'edit_users' ),
				'localizedStrings'              => self::get_js_localized_strings(),
				'optimized_build'               => '1',
				'page'                          => 'reporting',
				'showTinCanTab'                 => ( isset( $tincanny_settings['tinCanActivation'] ) && $tincanny_settings['tinCanActivation'] == 1 ) ? '1' : '0',
				'disablePerformanceEnhancments' => ( isset( $tincanny_settings['disablePerformanceEnhancments'] ) && $tincanny_settings['disablePerformanceEnhancments'] == 1 ) ? '1' : '0',
				'userIdentifierDisplayName'     => ( isset( $tincanny_settings['userIdentifierDisplayName'] ) && $tincanny_settings['userIdentifierDisplayName'] == 1 ) ? '1' : '0',
				'userIdentifierFirstName'       => ( isset( $tincanny_settings['userIdentifierFirstName'] ) && $tincanny_settings['userIdentifierFirstName'] == 1 ) ? '1' : '0',
				'userIdentifierLastName'        => ( isset( $tincanny_settings['userIdentifierLastName'] ) && $tincanny_settings['userIdentifierLastName'] == 1 ) ? '1' : '0',
				'userIdentifierUsername'        => ( isset( $tincanny_settings['userIdentifierUsername'] ) && $tincanny_settings['userIdentifierUsername'] == 1 ) ? '1' : '0',
				'userIdentifierEmail'           => ( isset( $tincanny_settings['userIdentifierEmail'] ) && $tincanny_settings['userIdentifierEmail'] == 1 ) ? '1' : '0',
				'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
			];

			wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
			wp_enqueue_script( 'reporting_js_handle' );

			// TinCan
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

			wp_enqueue_style( 'tincanny-admin-tincanny-report-tab', Config::get_gulp_css( 'admin.tincan.report.tab' ), [], UNCANNY_REPORTING_VERSION );
			wp_enqueue_script( 'tincanny-admin-tincanny-report-tab-js', Config::get_gulp_js( 'admin.tincan.report.tab' ), ['jquery', 'wp-hooks', 'wp-i18n'], UNCANNY_REPORTING_VERSION, false );

		} else if ( $page_title . '_page_manage-content' === $hook || $page_title . '_page_uncanny-reporting-license-activation' === $hook ) {
			wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), [], UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), [], UNCANNY_REPORTING_VERSION );

			wp_register_style( 'tclr-backend', Config::get_admin_css( 'admin-style.css' ), [], UNCANNY_REPORTING_VERSION );

			$dynamic_css = self::get_dynamic_css();
			wp_add_inline_style( 'tclr-backend', $dynamic_css );

			wp_enqueue_style( 'tclr-backend' );

		} elseif ( ! empty( $screen ) && 'dashboard' === $screen->id ) {

			$disable_dash_widget = get_option( 'tincanny_disableDashWidget', 'no' );

			if ( 'no' === $disable_dash_widget ) {
				// WP Dashboard Reporting UI Files

				// Load Styles for WP Dashboard Reporting  page located in general plugin styles
				wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), [], UNCANNY_REPORTING_VERSION );
				wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), [], UNCANNY_REPORTING_VERSION );

				wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), [], UNCANNY_REPORTING_VERSION );

				$dynamic_css = self::get_dynamic_css();
				wp_add_inline_style( 'reporting-admin', $dynamic_css );

				wp_enqueue_style( 'reporting-admin' );

				// Get Tin Canny settings
				$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

				wp_register_script( 'reporting_js_handle', Config::get_admin_js( 'tc-reporting-dashboard' ), [ 'jquery', 'wp-hooks', 'wp-i18n' ], UNCANNY_REPORTING_VERSION, true );
				$reporting_api_setup = [
					'root'             => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
					'nonce'            => \wp_create_nonce( 'wp_rest' ),
					'learnDashLabels'  => ReportingApi::get_labels(),
					'page'             => 'dashboard',
					'localizedStrings' => self::get_js_localized_strings(),
					'optimized_build'  => '1',
					'showTinCanTab'    => $tincanny_settings['tinCanActivation'] == 1 ? '1' : '0',
				];

				// Add custom colors to use them in the JS
				$ui = self::get_ui_data();
				wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

				// Add Tin Canny data
				wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );

				wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
				wp_enqueue_script( 'reporting_js_handle' );
			}
		}

		//wp_enqueue_script( 'tincanny-report-block', Config::get_gulp_js( 'tincanny-block' ), array('wp-blocks', 'wp-i18n', 'wp-element',), UNCANNY_REPORTING_VERSION );

	}

	/*
	 * Load Scripts
	 * @paras string $hook Admin page being loaded
	 */

	private static function get_dynamic_css() {
		// Get colors
		$ui = self::get_ui_data();

		// Start output
		ob_start();

		?>

		/* Main font */

		.tclr.wrap,
		.tclr-select2 .select2-dropdown {
		font-family: <?php echo $ui['mainFont']; ?>
		}

		/* Primary color */

		.reporting-dashboard-status--loading .reporting-dashboard-status__icon {
		background: <?php echo $ui['colors']['primary']; ?>;
		}

		.reporting-datatable__search .dataTables_filter input:focus {
		border-color: <?php echo $ui['colors']['primary']; ?>;
		}

		.reporting-table-see-details,
		.reporting-breadcrumbs-item__link {
		color: <?php echo $ui['colors']['primary']; ?>;
		}

		.reporting-single-course-progress-tabs__item.reporting-single-course-progress-tabs__item--selected {
		box-shadow: inset 3px 0 0 0 <?php echo $ui['colors']['primary']; ?>;
		}

		/* Secondary color */

		.reporting-dashboard-quick-links__icon {
		color: <?php echo $ui['colors']['secondary']; ?>;
		}

		/* Notice */

		.reporting-dashboard-status--warning .reporting-dashboard-status__icon {
		background: <?php echo $ui['colors']['notice'];; ?>;
		}

		<?php

		// Get output
		$dynamic_css = ob_get_clean();

		// Return output
		return $dynamic_css;
	}

	private static function get_script_data() {

		$roles = [];
		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$roles = ( array ) $user->roles;
		}

		$administrator_view = false;
		if ( in_array( 'administrator', $roles ) ) {
			$administrator_view = true;
		}

		return [
			'url'               => [
				'updateData' => admin_url( 'admin.php?page=learndash_data_upgrades' )
			],
			'i18n'              => self::get_i18n_strings(),
			'administratorView' => $administrator_view
		];
	}

	private static function get_i18n_strings() {
		return [
			'dataNotRight'    => __( 'Data not right?', 'uncanny-learndash-reporting' ),
			'tryRunning'      => __( 'Try running the %s.', 'uncanny-learndash-reporting' ),
			'updatesLinkText' => __( 'LearnDash Data Upgrades', 'uncanny-learndash-reporting' )
		];
	}

	private static function get_ui_data() {
		// Define default font
		$font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

		// Get colors from DB
		$primary   = '';
		$secondary = '';
		$notice    = '';

		$not_started = '';
		$in_progress = '';
		$completed   = '';

		// Define default values
		$primary   = empty( $primary ) ? '#0290c2' : $primary;
		$secondary = empty( $secondary ) ? '#d52c82' : $secondary;
		$notice    = empty( $notice ) ? '#f5ba05' : $notice;

		$completed   = empty( $completed ) ? '#02c219' : $completed;
		$in_progress = empty( $in_progress ) ? '#FF9E01' : $in_progress;
		$not_started = empty( $not_started ) ? '#e3e3e3' : $not_started;

		return apply_filters( 'tincanny_ui', [
			'mainFont' => $font,
			'colors'   => [
				'primary'   => $primary,
				'secondary' => $secondary,
				'notice'    => $notice,
				'status'    => [
					'completed'  => $completed,
					'inProgress' => $in_progress,
					'notStarted' => $not_started,
				],
			],
			'show'     => [
				'tinCanData' => get_option( 'show_tincan_reporting_tables' ) != 'no',
			],
		] );
	}

	private static function get_js_localized_strings() {

		/**
		 * listed as they appear in file order under \plugins\tin-canny-learndash-reporting\src\assets\admin\js\scripts\components\*.js
		 */
		return [
			// charts.js
			'In Progress'                                                                           => __( 'In Progress', 'uncanny-learndash-reporting' ),
			'Completed'                                                                             => __( 'Completed', 'uncanny-learndash-reporting' ),
			'Not Started'                                                                           => __( 'Not Started', 'uncanny-learndash-reporting' ),
			'No Data'                                                                               => __( 'No Data', 'uncanny-learndash-reporting' ),
			// config.js
			'Are you sure you want to permanently delete all Tin Can data? This cannot be undone.'  => __( 'Are you sure you want to permanently delete all Tin Can data? This cannot be undone.', 'uncanny-learndash-reporting' ),
			'Are you sure you want to permanently delete all Bookmark data? This cannot be undone.' => __( 'Are you sure you want to permanently delete all Bookmark data? This cannot be undone.', 'uncanny-learndash-reporting' ),
			// data-object.js
			'%'                                                                                     => __( '%', 'uncanny-learndash-reporting' ),
			// query-string.js
			'Please select your criteria to filter the Tin Can data.'                               => __( 'Please select your criteria to filter the Tin Can data.', 'uncanny-learndash-reporting' ),
			'Please select your criteria to filter the xAPI Quiz data.'                             => __( 'Please select your criteria to filter the xAPI Quiz data.', 'uncanny-learndash-reporting' ),
			// tables.js
			'Enrolled'                                                                              => __( 'Enrolled', 'uncanny-learndash-reporting' ),
			'Avg Time to Complete'                                                                  => __( 'Avg Time to Complete', 'uncanny-learndash-reporting' ),
			'Avg Time Spent'                                                                        => __( 'Avg Time Spent', 'uncanny-learndash-reporting' ),
			'% Complete'                                                                            => __( '% Complete', 'uncanny-learndash-reporting' ),
			'Certificate Link'                                                                      => __( 'Certificate Link', 'uncanny-learndash-reporting' ),
			'Users Enrolled'                                                                        => __( 'Users Enrolled', 'uncanny-learndash-reporting' ),
			'Average Time to Complete'                                                              => __( 'Average Time to Complete', 'uncanny-learndash-reporting' ),
			'Average '                                                                              => __( 'Average ', 'uncanny-learndash-reporting' ),
			'Display Name'                                                                          => __( 'Name', 'uncanny-learndash-reporting' ),
			'First Name'                                                                            => __( 'First Name', 'uncanny-learndash-reporting' ),
			'Last Name'                                                                             => __( 'Last Name', 'uncanny-learndash-reporting' ),
			'Username'                                                                              => __( 'Username', 'uncanny-learndash-reporting' ),
			'Email Address'                                                                         => __( 'Email Address', 'uncanny-learndash-reporting' ),
			'Quiz Average'                                                                          => sprintf( __( '%s Average', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'Completion Date'                                                                       => __( 'Completion Date', 'uncanny-learndash-reporting' ),
			'Time to Complete'                                                                      => __( 'Time to Complete', 'uncanny-learndash-reporting' ),
			'Time Spent'                                                                            => __( 'Time Spent', 'uncanny-learndash-reporting' ),
			'Status'                                                                                => __( 'Status', 'uncanny-learndash-reporting' ),
			'Associated Lesson'                                                                     => sprintf( __( 'Associated %s', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'lesson' ) ),
			' Name'                                                                                 => __( ' Name', 'uncanny-learndash-reporting' ),
			'Date Completed'                                                                        => __( 'Date Completed', 'uncanny-learndash-reporting' ),
			'Assignment Name'                                                                       => __( 'Assignment Name', 'uncanny-learndash-reporting' ),
			'Approval'                                                                              => __( 'Approval', 'uncanny-learndash-reporting' ),
			'Submitted On'                                                                          => __( 'Submitted On', 'uncanny-learndash-reporting' ),
			'Module'                                                                                => __( 'Module', 'uncanny-learndash-reporting' ),
			'Target'                                                                                => __( 'Target', 'uncanny-learndash-reporting' ),
			'Action'                                                                                => __( 'Action', 'uncanny-learndash-reporting' ),
			'Result'                                                                                => __( 'Result', 'uncanny-learndash-reporting' ),
			'Date'                                                                                  => __( 'Date', 'uncanny-learndash-reporting' ),
			'There are no activities to report.'                                                    => __( 'There are no activities to report.', 'uncanny-learndash-reporting' ),
			'CSV Export'                                                                            => __( 'CSV Export', 'uncanny-learndash-reporting' ),
			'Excel Export'                                                                          => __( 'Excel Export', 'uncanny-learndash-reporting' ),
			'missingTitleCourseId'                                                                  => _x( '%s ID: ', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'Not Complete'                                                                          => __( 'Not Complete', 'uncanny-learndash-reporting' ),
			'Not Approved'                                                                          => __( 'Not Approved', 'uncanny-learndash-reporting' ),
			'Approved'                                                                              => __( 'Approved', 'uncanny-learndash-reporting' ),
			'Page'                                                                                  => __( 'Page', 'uncanny-learndash-reporting' ),
			'View'                                                                                  => __( 'View', 'uncanny-learndash-reporting' ),
			// tabs.js
			' Summary'                                                                              => __( ' Summary', 'uncanny-learndash-reporting' ),
			'< Users Overview'                                                                      => __( '< Users Overview', 'uncanny-learndash-reporting' ),
			'< User Overview'                                                                       => __( '< User Overview', 'uncanny-learndash-reporting' ),
			'< Users '                                                                              => __( '< User\'s ', 'uncanny-learndash-reporting' ),
			' Overview'                                                                             => __( ' Overview', 'uncanny-learndash-reporting' ),
			'Showing _START_ to _END_ of _TOTAL_ entries'                                           => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'uncanny-learndash-reporting' ),
			'Showing 0 to 0 of 0 entries'                                                           => __( 'Showing 0 to 0 of 0 entries', 'uncanny-learndash-reporting' ),
			'(filtered from _MAX_ total entries)'                                                   => __( '(filtered from _MAX_ total entries)', 'uncanny-learndash-reporting' ),
			'Show _MENU_ entries'                                                                   => __( 'Show _MENU_ entries', 'uncanny-learndash-reporting' ),
			'Loading...'                                                                            => __( 'Loading...', 'uncanny-learndash-reporting' ),
			'Processing...'                                                                         => __( 'Processing...', 'uncanny-learndash-reporting' ),
			'Search:'                                                                               => __( 'Search:', 'uncanny-learndash-reporting' ),
			'No matching records found'                                                             => __( 'No matching records found', 'uncanny-learndash-reporting' ),
			'First'                                                                                 => __( 'First', 'uncanny-learndash-reporting' ),
			'Last'                                                                                  => __( 'Last', 'uncanny-learndash-reporting' ),
			'Next'                                                                                  => __( 'Next', 'uncanny-learndash-reporting' ),
			'Previous'                                                                              => __( 'Previous', 'uncanny-learndash-reporting' ),
			': activate to sort column ascending'                                                   => __( ': activate to sort column ascending', 'uncanny-learndash-reporting' ),
			': activate to sort column descending'                                                  => __( ': activate to sort column descending', 'uncanny-learndash-reporting' ),
			'Detailed Report'                                                                       => __( 'Detailed Report', 'uncanny-learndash-reporting' ),
			'Score'                                                                                 => __( 'Score', 'uncanny-learndash-reporting' ),
			'Order'                                                                                 => __( 'Order', 'uncanny-learndash-reporting' ),

			'tablesColumnsAvgQuizScore'    => _x( 'Avg %s Score', '%s is the "Quiz" label', 'uncanny-learndash-reporting' ),
			'tablesColumnsCoursesEnrolled' => _x( '%s Enrolled', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'tablesColumnsLessonName'      => _x( '%s Name', '%s is the "Lesson" label', 'uncanny-learndash-reporting' ),
			'tablesColumnsTopicName'       => _x( '%s Name', '%s is the "Topic" label', 'uncanny-learndash-reporting' ),
			'tablesColumnsQuizName'        => _x( '%s Name', '%s is the "Quiz" label', 'uncanny-learndash-reporting' ),

			'tablesColumnsDetails'    => __( 'Details', 'uncanny-learndash-reporting' ),
			'tablesButtonSeeDetails'  => __( 'See details', 'uncanny-learndash-reporting' ),
			'tablesSearchPlaceholder' => __( 'Search...', 'uncanny-learndash-reporting' ),

			'overviewGoToCourseOverview'   => _x( '%s Overview', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'overviewGoToCourseUserReport' => __( 'User Report', 'uncanny-learndash-reporting' ),
			'overviewUsers'                => __( 'Users', 'uncanny-learndash-reporting' ),

			'overviewUserCardId' => __( 'ID: %s', 'uncanny-learndash-reporting' ),

			'overviewBoxesTitleRecentActivities'          => __( 'Recent Activities', 'uncanny-learndash-reporting' ),
			'overviewBoxesTitleReports'                   => __( 'Reports', 'uncanny-learndash-reporting' ),
			'overviewBoxesTitleCompletedCourses'          => _x( 'Most Completed %s', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCourseReportTitle'       => _x( '%s Report', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCourseReportDescription' => __( 'A summary-level overview of LearnDash courses and user progress', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsUserReportTitle'         => __( 'User Report', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsUserReportDescription'   => __( 'Monitor progress for individual users enrolled in LearnDash courses', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsTinCanReportTitle'       => __( 'Tin Can Report', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsTinCanReportDescription' => __( 'Detailed records of user activity in H5P and uploaded modules', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCoursesCompletionSeeAll' => _x( 'See all %s', '%s is the "Courses" label', 'uncanny-learndash-reporting' ),
			'overviewBoxesReportsCoursesCompletionNoData' => __( 'No completions registered', 'uncanny-learndash-reporting' ),
			'overviewLoading'                             => __( 'Loading', 'uncanny-learndash-reporting' ),

			'graphNoActivity'         => __( 'No activity registered', 'uncanny-learndash-reporting' ),
			'graphNoEnrolledUsers'    => __( 'No enrolled users', 'uncanny-learndash-reporting' ),
			'graphCourseCompletions'  => _x( '%s Completions', '%s is the "Course" label', 'uncanny-learndash-reporting' ),
			'graphTinCanStatements'   => __( 'Tin Can Statements', 'uncanny-learndash-reporting' ),
			'graphTooltipCompletions' => _x( '%s completion(s)', '%s is a number', 'uncanny-learndash-reporting' ),
			'graphTooltipStatements'  => _x( '%s statement(s)', '%s is a number', 'uncanny-learndash-reporting' ),
			'customizeColumns'        => _x( 'Customize columns', 'Customize columns', 'uncanny-learndash-reporting' ),
			'hideCustomizeColumns'    => _x( 'Hide customize columns', 'Customize columns', 'uncanny-learndash-reporting' ),

			'showAll'				  => __( 'Show all', 'uncanny-learndash-reporting' ),
		];
	}

	/**
	 * Add tincany via shortcode on the frontend
	 *
	 * @return string
	 */
	public static function frontend_tincanny() {
		ob_start();

		self::options_menu_page_output();

		$output = ob_get_clean();

		return str_replace( "id='module'", '', $output );
	}

	/**
	 * Create Theme Options page
	 */
	public static function options_menu_page_output() {

		if ( ! is_user_logged_in() ) {
			echo esc_html__( 'You must be logged in to view this report.', 'uncanny-learndash-reporting' );

			return;
		}

		if ( ! current_user_can( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) ) ) {
			echo esc_html__( 'You do not have access to this report', 'uncanny-learndash-reporting' );

			return;
		}

		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

		// Check if the parameter exists and has a valid value
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], [ 'userReportTab', 'tin-can', 'xapi-tincan' ] ) ) {
			// Set current tab
			$current_tab = $_GET['tab'];
		} else {
			// If the tab parameter is not defined, or it is, but it's not one of the values in
			// the in_array function, then use the default tab
			$current_tab = 'courseReportTab';
		}

		self::$template_data['labels'] = [
			'course'      => \LearnDash_Custom_Label::get_label( 'course' ),
			'courses'     => \LearnDash_Custom_Label::get_label( 'courses' ),
			'lessons'     => \LearnDash_Custom_Label::get_label( 'lessons' ),
			'topics'      => \LearnDash_Custom_Label::get_label( 'topics' ),
			'quizzes'     => \LearnDash_Custom_Label::get_label( 'quizzes' ),
			'assignments' => __( 'Assignments', 'learndash' ),
		];

		// Options - Restrict for group leader
		$show_tincan = get_option( 'show_tincan_reporting_tables', 'yes' );

		if ( 'no' === $show_tincan ) {
			self::$tincan_show = 'style="display: none;"';
		}

		$user_can_view_all_reports = apply_filters( 'tincanny_view_all_reports_permission', current_user_can( 'manage_options' ) );

		if ( $user_can_view_all_reports ) {
			$group_ids = [];
		} elseif ( learndash_is_group_leader_user( get_current_user_id() ) ) {
			$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
		} else {
			echo __( 'This report is only accessible by group leaders and administrators.', 'uncanny-learndash-reporting' );
			return;
		}

		if
		( empty( $group_ids ) && ! $user_can_view_all_reports
		) {
			echo __( 'Group Leader has no groups assigned.', 'uncanny-learndash-reporting' );
			return;
		} else {
			$groups = get_posts(
				[
					'numberposts' => 500,
					'include'     => $group_ids,
					'post_type'   => 'groups',
				]
			);
		}

		self::$groups_query = $groups;

		if ( isset( $_GET['group_id'] ) ) {
			self::$isolated_group = absint( $_GET['group_id'] );
		}

		// Get context
		// Values:
		// - dashboard:  WP Admin main page
		// - plugin:     The Tin Canny Dashboard page
		// - frontend:   Frontend
		$context = 'frontend';

		if ( is_admin() ) {
			if ( $_GET['page'] == 'uncanny-learnDash-reporting' ) {
				$context = 'plugin';
			} else {
				$context = 'dashboard';
			}
		}
		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

		// Add CSS classes to main container
		$css_classes   = [];
		$css_classes[] = sprintf( 'uo-reporting--%s', $context );

		?>

		<div class="tclr wrap <?php echo implode( ' ', $css_classes ); ?>" id="tincanny-reporting"
			 data-context="<?php echo $context; ?>">
			<div id="ld_course_info" class="uo-tclr-admin uo-admin-reporting">

				<?php


				if (
					( is_admin() && defined( 'LEARNDASH_LMS_PLUGIN_URL' ) )
					|| (
						! is_admin() && defined( 'LEARNDASH_LMS_PLUGIN_URL' ) && function_exists( 'learndash_is_active_theme' )
						&& learndash_is_active_theme( 'ld30' )
					)
				) {
					$icon = LEARNDASH_LMS_PLUGIN_URL . 'themes/legacy/templates/images/statistics-icon-small.png';
					?>

					<style>
						.statistic_icon {
							background: url(<?php echo $icon; ?>) no-repeat scroll 0 0 transparent;
							width: 23px;
							height: 23px;
							margin: auto;
							background-size: 23px;
						}
					</style>

					<?php
				}

				$filepath = \SFWD_LMS::get_template( 'learndash_template_script.js', null, null, true );

				if ( ! empty( $filepath ) ) {

					wp_register_script( 'uo_learndash_template_script_js', learndash_template_url_from_path( $filepath ), [], '1.0', true );
					$learndash_assets_loaded['scripts']['learndash_template_script_js'] = __FUNCTION__;

					$data            = [];
					$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
					$data            = [ 'json' => json_encode( $data ) ];
					wp_localize_script( 'uo_learndash_template_script_js', 'sfwd_data', $data );

					wp_enqueue_script( 'uo_learndash_template_script_js' );
				}

				\LD_QuizPro::showModalWindow();

				// Add admin header and tabs
				include self::get_part( 'header.php' );

				?>

				<div class="uo-tclr-admin__content">

					<script>

						// Move the statistics container just before the closing body tag
						jQuery( document ).on( 'click', 'a.user_statistic', function ( event ){
							// Move user overlay
							jQuery( '#wpProQuiz_user_overlay' ).appendTo( jQuery( 'body' ) );
						} );

					</script>

					<h3 id="failed-response"></h3>

					<?php include self::get_part( 'groups-drop-down.php' ); ?>

					<section id="first-tab-group" class="uo-admin-reporting-tabgroup">

						<?php include self::get_part( 'course-report-tab.php' ); ?>

						<?php include self::get_part( 'user-report-tab.php' ); ?>

						<?php if ( is_admin() ) { ?>

							<div class="uo-admin-reporting-tab-single" id="tin-can"
								 style="display: <?php echo $current_tab == 'tin-can' ? 'block' : 'none'; ?>">
								<?php self::show_tincan_list_table( 'tin-can' ); ?>
							</div>
							<div class="uo-admin-reporting-tab-single" id="xapi-tincan"
								 style="display: <?php echo $current_tab == 'xapi-tincan' ? 'block' : 'none'; ?>">
								<?php self::show_tincan_list_table( 'xapi-tincan' ); ?>
							</div>

						<?php } else {
							if ( isset( $tincanny_settings['enableTinCanReportFrontEnd'] ) ) {
								if ( $tincanny_settings['enableTinCanReportFrontEnd'] == 1 ) {
									?>

									<div class="uo-admin-reporting-tab-single" id="tin-can"
										 style="display: <?php echo $current_tab == 'tin-can' ? 'block' : 'none'; ?>">
										<div class="reporting-datatable__table">
											<?php self::show_tincan_list_table( 'tin-can' ); ?>
										</div>
									</div>

									<?php
								}
							}
							if ( isset( $tincanny_settings['enablexapiReportFrontEnd'] ) ) {
								if ( $tincanny_settings['enablexapiReportFrontEnd'] == 1 ) {
									?>
									<div class="uo-admin-reporting-tab-single" id="xapi-tincan"
										 style="display: <?php echo $current_tab == 'xapi-tincan' ? 'block' : 'none'; ?>">
										<div class="reporting-datatable__table">
											<?php self::show_tincan_list_table( 'xapi-tincan' ); ?>
										</div>
									</div>
									<?php
								}
							}
						} ?>
					</section>
				</div>

			</div>
		</div>

		<?php
	}

	/*
	 * Add add-ons to options page
	 *
	 * @param Array() $classes_available
	 * @param Array() $active_classes
	 *
	 */

	/**
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 *
	 * @return string
	 */
	public static function get_part( $file_name ) {

		$asset_uri = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'uncanny-reporting' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/*
	 * Check for Adds that are located in other UO plugins
	 *@param string $uo_plugin
	 *
	 *return mixed(false || String)
	*/

	private static function show_tincan_list_table( $current_tab = 'tin-can', $column_settings = [] ) {

		self::$tincan_database      = new \UCTINCAN\Database\Admin();
		self::$tincan_opt_per_pages = get_user_meta( get_current_user_id(), 'ucTinCan_per_page', true );
		self::$tincan_opt_per_pages = ( self::$tincan_opt_per_pages ) ? self::$tincan_opt_per_pages : 25;

		if ( ! is_admin() ) {

			if ( file_exists( dirname( __FILE__ ) . '/includes/TinCan_List_Table.php' ) ) {
				//echo '<script>console.log("I ecist")</script>';
				//echo'<script>console.log("'.ABSPATH . 'wp-admin/includes/class-wp-list-table.php'.'")</script>';
			}
			global $hook_suffix;
			$hook_suffix = '';
			if ( isset( $page_hook ) ) {
				$hook_suffix = $page_hook;
			} else if ( isset( $plugin_page ) ) {
				$hook_suffix = $plugin_page;
			} else if ( isset( $pagenow ) ) {
				$hook_suffix = $pagenow;
			}

			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			require_once( ABSPATH . 'wp-admin/includes/screen.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
			require_once( ABSPATH . 'wp-admin/includes/template.php' );

		}


		include_once( dirname( __FILE__ ) . '/includes/TinCan_List_Table.php' );

		$TinCan_List_Table = new \TinCan_List_Table();

		if ( $current_tab == 'xapi-tincan' ) {
			$user_settings   = get_user_meta( get_current_user_id(), 'xapi_report_columns', true );
			$column_settings = wp_parse_args( $user_settings, self::$xapi_report_columns );
			$coulmns         = [];
			foreach ( $column_settings as $key => $column ) {
				if ( $column['value'] == true ) {
					$coulmns[] = $column['label'];
				}
			}

		} else {
			$coulmns = [
				' Group ',
				' User ',
				' Course ',
				' Module ',
				' Target ',
				' Action ',
				' Result ',
				' Success ',
				' Date Time ',
			];
		}

		$TinCan_List_Table->sortable_columns = $coulmns;
		$TinCan_List_Table->column           = $coulmns;

		if ( $current_tab == 'xapi-tincan' ) {
			$TinCan_List_Table->data           = [ __CLASS__, 'patchData_xapi' ];
			$TinCan_List_Table->count          = [ __CLASS__, 'patchNumRows_xapi' ];
			$TinCan_List_Table->per_page       = self::$tincan_opt_per_pages;
			$TinCan_List_Table->extra_tablenav = [ __CLASS__, 'ExtraTableNav_xapi' ];
		} else {
			$TinCan_List_Table->data           = [ __CLASS__, 'patchData' ];
			$TinCan_List_Table->count          = [ __CLASS__, 'patchNumRows' ];
			$TinCan_List_Table->per_page       = self::$tincan_opt_per_pages;
			$TinCan_List_Table->extra_tablenav = [ __CLASS__, 'ExtraTableNav' ];
		}

		$TinCan_List_Table->prepare_items();
		$TinCan_List_Table->views();

		$TinCan_List_Table->display();
	}

	/**
	 * Add tincany via shortcode on the frontend
	 *
	 * @return string
	 */
	public static function frontend_tincanny_block() {
		// Admin Page Reporting UI Files

		// Admin CSS
		// TODO add debug and minify
		wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), [], UNCANNY_REPORTING_VERSION );
		wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), [], UNCANNY_REPORTING_VERSION );
		wp_enqueue_style( 'data-tables', Config::get_admin_css( 'datatables.min.css' ), [], UNCANNY_REPORTING_VERSION );

		wp_register_style( 'reporting-admin', Config::get_admin_css( 'admin-style.css' ), [], UNCANNY_REPORTING_VERSION );

		$dynamic_css = self::get_dynamic_css();
		wp_add_inline_style( 'reporting-admin', $dynamic_css );

		wp_enqueue_style( 'reporting-admin' );

		// Admin JS
		wp_register_script( 'reporting_js_handle', Config::get_admin_js( 'reporting' ), [ 'jquery', 'wp-hooks', 'wp-i18n' ], UNCANNY_REPORTING_VERSION, false );

		// Add custom colors to use them in the JS
		$ui = self::get_ui_data();
		wp_localize_script( 'reporting_js_handle', 'TincannyUI', $ui );

		// Add Tin Canny data
		wp_localize_script( 'reporting_js_handle', 'TincannyData', self::get_script_data() );

		if ( isset( $_GET['group_id'] ) ) {
			$isolated_group_id = absint( $_GET['group_id'] );
		} else {
			$isolated_group_id = 0;
		}

		// Get Tin Canny settings
		$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();

		// API data
		$reporting_api_setup = [
			'root'              => esc_url_raw( rest_url() . 'uncanny_reporting/v1/' ),
			'nonce'             => \wp_create_nonce( 'wp_rest' ),
			'learnDashLabels'   => ReportingApi::get_labels(),
			'isolated_group_id' => $isolated_group_id,
			'isAdmin'           => is_admin(),
			'editUsers'         => current_user_can( 'edit_users' ),
			'localizedStrings'  => self::get_js_localized_strings(),
			'page'              => 'frontend',
			'optimized_build'   => '1',
			'showTinCanTab'     => $tincanny_settings['tinCanActivation'] == 1 ? '1' : '0',
			'userIdentifierDisplayName'     => ( isset( $tincanny_settings['userIdentifierDisplayName'] ) && $tincanny_settings['userIdentifierDisplayName'] == 1 ) ? '1' : '0',
			'userIdentifierFirstName'       => ( isset( $tincanny_settings['userIdentifierFirstName'] ) && $tincanny_settings['userIdentifierFirstName'] == 1 ) ? '1' : '0',
			'userIdentifierLastName'        => ( isset( $tincanny_settings['userIdentifierLastName'] ) && $tincanny_settings['userIdentifierLastName'] == 1 ) ? '1' : '0',
			'userIdentifierUsername'        => ( isset( $tincanny_settings['userIdentifierUsername'] ) && $tincanny_settings['userIdentifierUsername'] == 1 ) ? '1' : '0',
			'userIdentifierEmail'           => ( isset( $tincanny_settings['userIdentifierEmail'] ) && $tincanny_settings['userIdentifierEmail'] == 1 ) ? '1' : '0',
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
		];

		wp_localize_script( 'reporting_js_handle', 'reportingApiSetup', $reporting_api_setup );
		wp_enqueue_script( 'reporting_js_handle' );

		// TinCan
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

		wp_enqueue_style( 'tincanny-admin-tincanny-report-tab', Config::get_gulp_css( 'admin.tincan.report.tab' ), [], UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'tincanny-admin-tincanny-report-tab-js', Config::get_gulp_js( 'admin.tincan.report.tab' ), [], UNCANNY_REPORTING_VERSION );
		// TODO remove if no issues
		//wp_enqueue_script( 'unTinCanAdmin', UCTINCAN_PLUGIN_URL . 'assets/dist/admin.table-min.js' );
		ob_start();

		self::options_menu_page_output();

		return ob_get_clean();
	}

	public static function create_features( $classes_available, $active_classes ) {

		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		if ( function_exists( 'get_magic_quotes_gpc' ) ) {
			if ( get_magic_quotes_gpc() ) {
				//strip slashes from all keys in array
				$active_classes = Config::stripslashes_deep( $active_classes );
			}
		}


		// Sort add ons alphabetically by title
		$add_on_titles = [];
		foreach ( $classes_available as $key => $row ) {
			$add_on_titles[ $key ] = $row['title'];
		}
		array_multisort( $add_on_titles, SORT_ASC, $classes_available );

		foreach ( $classes_available as $key => $class ) {

			// skip sample classes
			if ( 'uncanny_learndash_reporting\Sample' === $key || 'uncanny_custom_reporting\Sample' === $key || 'uncanny_pro_reporting\Sample' === $key ) {
				continue;
			}

			if ( false === $class ) {
				?>
				<div class="uo_feature">
					<div class="uo_feature_title"><?php echo esc_html( $key ) ?></div>
					<div class="uo_feature_description"><?php
						esc_html_e( 'This class is not configured properly. Contact Support for assistance.', 'uncanny-learndash-reporting' );
						?></div>
				</div>
				<?php
				continue;
			}

			$dependants_exist = $class['dependants_exist'];

			$is_activated = 'uo_feature_deactivated';
			$class_name   = $key;
			if ( isset( $active_classes[ $class_name ] ) ) {
				$is_activated = 'uo_feature_activated';
			}
			if ( true !== $dependants_exist ) {
				$is_activated = 'uo_feature_needs_dependants';
			}

			$icon = '<div class="uo_icon"></div>';
			if ( $class['icon'] ) {
				$icon = $class['icon'];
			}


			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				$class['settings']['modal'] = '';
				$class['settings']['link']  = '';
			}


			?>

			<?php // Setting Modal Popup
			echo $class['settings']['modal']; ?>

			<div class="uo_feature">

				<?php // Settings Modal Popup trigger
				echo $class['settings']['link']; ?>

				<div class="uo_feature_title">

					<?php echo $class['title']; ?>

					<?php
					// Link to KB for Feature
					if ( null !== $class['kb_link'] ) {
						?>
						<a class="uo_feature_more_info" href="<?php echo $class['kb_link']; ?>" target="_blank">
							<i class="fa fa-question-circle"></i>
						</a>
					<?php } ?>

				</div>

				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_icon_container"><?php echo $icon; ?></div>
				<div class="uo_feature_button <?php echo $is_activated; ?>">

					<?php
					if ( true !== $dependants_exist ) {
						echo '<div><strong>' . esc_html( $dependants_exist ) . '</strong>' . esc_html__( ' is needed for this add-on', 'uncanny-learndash-reporting' ) . '</div>';
					} else {
						?>
						<div class="uo_feature_button_toggle"></div>
						<label class="uo_feature_label" for="<?php echo esc_attr( $class_name ) ?>">
							<?php echo( esc_html__( 'Activate ', 'uncanny-learndash-reporting' ) . $class['title'] ); ?>
						</label>
						<input class="uo_feature_checkbox" type="checkbox" id="<?php echo esc_attr( $class_name ); ?>"
							   name="uncanny_reporting_active_classes[<?php echo esc_attr( $class_name ) ?>]"
							   value="<?php echo esc_attr( $class_name ) ?>" <?php
						if ( array_key_exists( $class_name, $active_classes ) ) {
							// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values with stripslashes
							checked( stripslashes( $active_classes[ $class_name ] ), stripslashes( $class_name ), true );
						}
						?>
						/>
					<?php } ?>
				</div>
			</div>
			<?php
		}
	}

	public static function execute_csv_export() {
		include_once( dirname( __FILE__ ) . '/uncanny-tincan/uncanny-tincan.php' );

		self::$tincan_database = new \UCTINCAN\Database\Admin();

		self::SetTcFilters();
		self::SetOrder();

		$data = self::$tincan_database->get_data( 0, 'csv' );

		new \UCTINCAN\Admin\CSV( $data );
	}

	private static function SetTcFilters() {
		// Group
		if ( isset( $_GET['tc_filter_group'] ) && ! empty( $_GET['tc_filter_group'] ) ) {
			self::$tincan_database->group = sanitize_text_field( $_GET['tc_filter_group'] );
		}

		// Actor
		if ( isset( $_GET['tc_filter_user'] ) && ! empty( $_GET['tc_filter_user'] ) ) {
			self::$tincan_database->actor = sanitize_text_field( $_GET['tc_filter_user'] );
		}

		// Course
		if ( isset( $_GET['tc_filter_course'] ) && ! empty( $_GET['tc_filter_course'] ) ) {
			self::$tincan_database->course = sanitize_text_field( $_GET['tc_filter_course'] );
		}

		// Lesson
		if ( isset( $_GET['tc_filter_lesson'] ) && ! empty( $_GET['tc_filter_lesson'] ) ) {
			self::$tincan_database->lesson = sanitize_text_field( $_GET['tc_filter_lesson'] );
		}

		// Module
		if ( isset( $_GET['tc_filter_module'] ) && ! empty( $_GET['tc_filter_module'] ) ) {
			self::$tincan_database->module = sanitize_text_field( $_GET['tc_filter_module'] );
		}

		// Verb
		if ( isset( $_GET['tc_filter_action'] ) && ! empty( $_GET['tc_filter_action'] ) ) {
			self::$tincan_database->verb = strtolower( sanitize_text_field( $_GET['tc_filter_action'] ) );
		}

		// Questions
		if ( isset( $_GET['tc_filter_quiz'] ) && ! empty( $_GET['tc_filter_quiz'] ) ) {
			self::$tincan_database->question = strtolower( sanitize_text_field( $_GET['tc_filter_quiz'] ) );
		}

		// result
		if ( isset( $_GET['tc_filter_results'] ) && ! empty( $_GET['tc_filter_results'] ) ) {
			self::$tincan_database->results = strtolower( sanitize_text_field( $_GET['tc_filter_results'] ) );
		}

		// Date
		if ( isset( $_GET['tc_filter_date_range'] ) && ! empty( $_GET['tc_filter_date_range'] ) ) {
			switch ( sanitize_text_field( $_GET['tc_filter_date_range'] ) ) {
				case 'last' :
					if ( isset( $_GET['tc_filter_date_range_last'] ) && ! empty( $_GET['tc_filter_date_range_last'] ) ) {
						$current_time = current_time( 'timestamp' );

						switch ( sanitize_text_field( $_GET['tc_filter_date_range_last'] ) ) {
							case 'week' :
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( 'last week', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;

								break;
							case 'month' :
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( 'first day of last month', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;

							case '90days' :
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-90 days', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
							case '3months' :
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-3 months', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
							case '6months' :
								self::$tincan_database->dateEnd   = date( 'Y-m-d ', $current_time ) . '23:59:59';
								$dateStart                        = strtotime( '-6 months', $current_time );
								self::$tincan_database->dateStart = date( 'Y-m-d ', $dateStart );
								break;
						}
					}
					break;
				case 'from' :
					if ( isset( $_GET['tc_filter_start'] ) && ! empty( $_GET['tc_filter_start'] ) ) {
						self::$tincan_database->dateStart = sanitize_text_field( $_GET['tc_filter_start'] );
					}

					if ( isset( $_GET['tc_filter_end'] ) && ! empty( $_GET['tc_filter_end'] ) ) {
						self::$tincan_database->dateEnd = sanitize_text_field( $_GET['tc_filter_end'] ) . ' 23:59:59';
					}
					break;
			}
		}
	}

	private static function SetOrder() {
		self::$tincan_database->orderby = "xstored";
		self::$tincan_database->order   = 'desc';

		if ( ! empty( $_GET["orderby"] ) ) {
			switch ( sanitize_text_field( $_GET["orderby"] ) ) {
				case 'group' :
				case 'user' :
				case 'course' :
					self::$tincan_database->orderby = sanitize_text_field( $_GET["orderby"] ) . '_id';
					break;

				case 'action' :
					self::$tincan_database->orderby = 'verb';
					break;

				case 'date-time' :
					self::$tincan_database->orderby = 'xstored';
					break;
			}

			self::$tincan_database->order = ( ! empty( $_GET["order"] ) ) ? sanitize_text_field( $_GET["order"] ) : 'desc';
		}
	}

	public static function execute_csv_export_xapi() {
		include_once( dirname( __FILE__ ) . '/uncanny-tincan/uncanny-tincan.php' );

		self::$tincan_database = new \UCTINCAN\Database\Admin();

		self::SetTcFilters();
		self::SetOrder();

		$data = self::$tincan_database->get_xapi_data( 0, 'csv-xapi' );

		new \UCTINCAN\Admin\CSV( $data );
	}

	public static function tincan_change_per_page() {
		if ( isset( $_GET['per_page'] ) && ! empty( $_GET['per_page'] ) ) {
			update_user_meta( get_current_user_id(), 'ucTinCan_per_page', sanitize_text_field( $_GET['per_page'] ) );
		}
	}

	// Number of Data

	public static function patchData() {
		$data = [];
		if ( ! isset( $_GET['tab'] ) || sanitize_text_field( $_GET['tab'] ) !== 'tin-can' ) {
			return $data;
		}
		if ( ! is_admin() ) {
			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) and $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
			self::$tincan_database->paged = isset( $_REQUEST["paged"] ) ? sanitize_text_field( $_REQUEST["paged"] ) : 1;
		} else {
			self::$tincan_database->paged = isset( $_GET["paged"] ) ? sanitize_text_field( $_GET["paged"] ) : 1;
		}
		$tincan_post_types = [
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-certificates',
			'sfwd-assignment',
			'groups',
		];

		self::SetOrder();


		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && sanitize_text_field( $_GET['tc_filter_mode'] ) == 'list' ) {
			self::SetTcFilters();

			$data = self::$tincan_database->get_data( self::$tincan_opt_per_pages );
		}

		foreach ( $data as &$row ) {
			$lesson = get_post( $row['lesson_id'] );

			if ( in_array( $lesson->post_type, $tincan_post_types ) ) {
				$group_link = admin_url( "post.php?post={$row[ 'group_id' ]}&action=edit" );
				$group_name = $row['group_name'];
				$group      = sprintf( '<a href="%s">%s</a>', $group_link, $group_name );

				$course_link = admin_url( "post.php?post={$row[ 'course_id' ]}&action=edit" );
				$course_name = $row['course_name'];
				$course      = sprintf( '<a href="%s">%s</a>', $course_link, $course_name );
			} else {
				$group  = __( 'n/a', 'uncanny-learndash-reporting' );
				$course = __( 'n/a', 'uncanny-learndash-reporting' );
			}

			$row[' Group ']  = $group;
			$row[' User ']   = sprintf( '<a href="%s">%s</a>', admin_url( "user-edit.php?user_id={$row[ 'user_id' ]}" ), $row['user_name'] );
			$row[' Course '] = $course;
			$row[' Module '] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['module'], site_url() ), $row['module_name'] );
			$row[' Target '] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['target'], site_url() ), $row['target_name'] );
			$row[' Action '] = ucfirst( $row['verb'] );

			$result = $row['result'];

			if ( ! is_null( $row['result'] ) && $row['minimum'] ) {
				$result = $row['result'] . ' / ' . $row['minimum'];
			}

			$completion = false;

			if ( ! is_null( $row['completion'] ) ) {
				$completion = ( $row['completion'] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';
			}

			$row[' Result ']    = '<span class="tclr-reporting-datatable__no-wrap">' . $result . '</span>';
			$row[' Success ']   = $completion;
			$row[' Date Time '] = $row['xstored'];
			$row                = apply_filters( 'tincanny_row_data', $row );
		}

		return $data;
	}

	// Number of Data

	private static function make_absolute( $url, $base ) {
		// Return base if no url
		if ( ! $url ) {
			return $base;
		}

		// Return if already absolute URL
		if ( parse_url( $url, PHP_URL_SCHEME ) != '' ) {
			return $url;
		}

		// Urls only containing query or anchor
		if ( $url[0] == '#' || $url[0] == '?' ) {
			return $base . $url;
		}

		// Parse base URL and convert to local variables: $scheme, $host, $path
		extract( parse_url( $base ) );

		// If no path, use /
		if ( ! isset( $path ) ) {
			$path = '/';
		}

		// Remove non-directory element from path
		//$path = preg_replace('#/[^/]*$#', '', $path);

		// Destroy path if relative url points to root
		//if($url[0] == '/') $path = '';

		// Dirty absolute URL
		$abs = "$host$path/$url";

		// Replace '//' or '/./' or '/foo/../' with '/'
		$re = [ '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' ];
		for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, - 1, $n ) ) {
		}

		// Absolute URL is ready!
		return $scheme . '://' . $abs;
	}

	public static function patchData_xapi() {
		$data = [];
		if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'xapi-tincan' ) {
			return $data;
		}
		if ( ! is_admin() ) {
			if ( ! isset( $_REQUEST['paged'] ) ) {
				$_REQUEST['paged'] = explode( '/page/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 );
				if ( isset( $_REQUEST['paged'][1] ) ) {
					list( $_REQUEST['paged'], ) = explode( '/', sanitize_text_field( $_REQUEST['paged'][1] ), 2 );
				}
				if ( isset( $_REQUEST['paged'] ) and $_REQUEST['paged'] != '' ) {
					$_REQUEST['paged'] = intval( $_REQUEST['paged'] );
					if ( $_REQUEST['paged'] < 2 ) {
						$_REQUEST['paged'] = '';
					}
				} else {
					$_REQUEST['paged'] = '';
				}
			}
			self::$tincan_database->paged = isset( $_REQUEST["paged"] ) ? sanitize_text_field( $_REQUEST["paged"] ) : 1;
		} else {
			self::$tincan_database->paged = isset( $_GET["paged"] ) ? sanitize_text_field( $_GET["paged"] ) : 1;
		}
		$tincan_post_types = [
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-certificates',
			'sfwd-assignment',
			'groups',
		];

		self::SetOrder();

		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'list' ) {
			self::SetTcFilters();

			$data = self::$tincan_database->get_xapi_data( self::$tincan_opt_per_pages );
		}

		foreach ( $data as &$row ) {
			$lesson = get_post( $row['lesson_id'] );

			if ( in_array( $lesson->post_type, $tincan_post_types ) ) {
				$group_link = admin_url( "post.php?post={$row[ 'group_id' ]}&action=edit" );
				$group_name = $row['group_name'];
				$group      = sprintf( '<a href="%s">%s</a>', $group_link, $group_name );

				$course_link = admin_url( "post.php?post={$row[ 'course_id' ]}&action=edit" );
				$course_name = $row['course_name'];
				$course      = sprintf( '<a href="%s">%s</a>', $course_link, $course_name );
			} else {
				$group  = __( 'n/a', 'uncanny-learndash-reporting' );
				$course = __( 'n/a', 'uncanny-learndash-reporting' );
			}

			$row['Group']        = $group;
			$row[ __( 'User' ) ] = sprintf( '<a href="%s">%s</a>', admin_url( "user-edit.php?user_id={$row[ 'user_id' ]}" ), $row['user_name'] );
			$row['Course']       = $course;
			$row['Module']       = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['module'], site_url() ), $row['module_name'] );
			//$row[' Target '] = sprintf( '<a href="%s">%s</a>', self::make_absolute( $row['target'], site_url() ), $row['target_name'] );
			$row['Question'] = ucfirst( $row['activity_name'] );

			$result     = $row['result'];
			$completion = false;

			if ( ! is_null( $row['result'] ) ) {
				$completion = ( $row['result'] > 0 ) ? 'Correct' : 'Incorrect';
			} else {
				$completion = 'Incorrect';
			}

			$row['Result']           = $completion;
			$row['Success']          = $completion;
			$row['Score']            = is_null( $row['raw_score'] ) ? '' : (int) $row['raw_score'];
			$row['More Info']        = '<a href="javascript::void(0);" onclick="jQuery(\'#other_details_' . $row['id'] . '\').show();">Show details</a><p style="display: none" id="other_details_' . $row['id'] . '"><strong>Choices:</strong> ' . $row['available_responses'] . '<br/><strong>Correct Answer:</strong> ' . $row['correct_response'] . '<br/><strong>User\'s Answer:</strong> ' . $row['user_response'] . '</p>';
			$row['Date Time']        = $row['xstored'];
			$row['Choices']          = $row['available_responses'];
			$row['Correct Response'] = $row['correct_response'];
			$row['User Response']    = $row['user_response'];
			$row                     = apply_filters( 'tincanny_row_data', $row );
		}

		return $data;
	}

	public static function patchNumRows() {
		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'list' ) {
			return self::$tincan_database->get_count();
		}

		return 0;
	}

	public static function patchNumRows_xapi() {
		if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'list' ) {
			return self::$tincan_database->get_count_xapi();
		}

		return 0;
	}

	public static function ExtraTableNav( $which ) {
		switch ( $which ) {
			case 'top' :
				self::ExtraTableNavTop();
				break;
			case 'bottom' :
				self::ExtraTableNavBottom();
				break;
		}
	}

	//! Search Box

	private static function ExtraTableNavTop() {
		$ld_groups  = [];
		$ld_courses = [];
		if ( ! is_admin() ) {
			$group_leader_id = get_current_user_id();
			$user_group_ids  = learndash_get_administrators_group_ids( $group_leader_id, true );
			$args            = [
				'numberposts' => 9999,
				'include'     => array_map( 'intval', $user_group_ids ),
				'post_type'   => 'groups',
				'orderby'     => 'title',
				'order'       => 'ASC',
			];

			$ld_groups_user = get_posts( $args );
			if ( ! empty( $ld_groups_user ) ) {
				foreach ( $ld_groups_user as $ld_group ) {
					$ld_groups[] = [ 'group_id' => $ld_group->ID, 'group_name' => $ld_group->post_title ];
				}
			}
			// Courses
			if ( isset( $_GET['tc_filter_group'] ) && ! empty( $_GET['tc_filter_group'] ) ) {

				// check is user group
				if ( in_array( $_GET['tc_filter_group'], $user_group_ids ) ) {
					$group_id = absint( $_GET['tc_filter_group'] );
					$courses  = learndash_group_enrolled_courses( $group_id );
					$args     = [
						'numberposts' => 9999,
						'include'     => array_map( 'intval', $courses ),
						'post_type'   => 'sfwd-courses',
						'orderby'     => 'title',
						'order'       => 'ASC',
					];

					$courses = get_posts( $args );
					foreach ( $courses as $course ) {
						$ld_courses[] = [ 'course_id' => $course->ID, 'course_name' => $course->post_title ];
					}
				}
			}
			// Actions
			$ld_actions = self::$tincan_database->get_actions();

		} else {

			// Group
			$ld_groups = self::$tincan_database->get_groups();

			// Courses
			$ld_courses = self::$tincan_database->get_courses();

			// Actions
			$ld_actions = self::$tincan_database->get_actions();
		}
		?>

		<div class="reporting-tincan-filters">
			<form action="<?php echo remove_query_arg( 'paged' ) ?>" id="tincan-filters-top">
				<div class="reporting-metabox">
					<div class="reporting-dashboard-col-heading" id="coursesOverviewTableHeading">
						<?php _e( 'Filters', 'uncanny-learndash-reporting' ); ?>
					</div>
					<div class="reporting-dashboard-col-content">
						<input type="hidden" name="page"
							   value="<?php echo ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 1 ?>"/>
						<input type="hidden" name="tc_filter_mode" value="list"/>
						<input type="hidden" name="tab" value="tin-can"/>

						<input type="hidden" name="orderby"
							   value="<?php echo ! empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'date-time' ?>"/>
						<input type="hidden" name="order"
							   value="<?php echo ! empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc' ?>"/>

						<div class="reporting-tincan-filters-columns">
							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--1">
								<div class="reporting-tincan-section__title">
									<?php _e( 'User & Group', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">

										<label for="tc_filter_group"><?php _e( 'Group', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_group" id="tc_filter_group">
											<option value=""><?php _e( 'All Groups', 'uncanny-learndash-reporting' ); ?></option>
											<?php foreach ( $ld_groups as $group ) { ?>
												<option value="<?php echo $group['group_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_group'] ) && $_GET['tc_filter_group'] == $group['group_id'] ) ? 'selected="selected"' : '' ?>><?php echo $group['group_name'] ?></option>
											<?php } // foreach( $ld_groups ) ?>
										</select>

									</div>

									<div class="reporting-tincan-section__field">
										<label for="tc_filter_user"><?php _e( 'User', 'uncanny-learndash-reporting' ); ?></label>
										<input name="tc_filter_user" id="tc_filter_user"
											   placeholder="<?php _e( 'User', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ! empty( $_GET['tc_filter_user'] ) ? sanitize_text_field( $_GET['tc_filter_user'] ) : '' ?>"/>
									</div>
								</div>
							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--2">

								<div class="reporting-tincan-section__title">
									<?php _e( 'Content', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label for="tc_filter_course"><?php echo \LearnDash_Custom_Label::get_label( 'course' ); ?></label>
										<select name="tc_filter_course" id="tc_filter_course">
											<option value=""><?php echo sprintf( __( 'All %s', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'courses' ) ); ?></option>
											<?php foreach ( $ld_courses as $course ) { ?>
												<option value="<?php echo $course['course_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_course'] ) && $_GET['tc_filter_course'] == $course['course_id'] ) ? 'selected="selected"' : '' ?>><?php echo $course['course_name'] ?></option>
											<?php } // foreach( $ld_courses ) ?>
										</select>
									</div>
									<div class="reporting-tincan-section__field">
										<label for="tc_filter_module"><?php _e( 'Module', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_module" id="tc_filter_module">
											<option value=""><?php _e( 'All Modules', 'uncanny-learndash-reporting' ); ?></option>
											<?php self::$tincan_database->print_modules_form_from_URL_parameter() ?>
										</select>
									</div>
								</div>

							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--3">

								<div class="reporting-tincan-section__title">
									<?php _e( 'Activity', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label for="tc_filter_action"><?php _e( 'Action', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_action" id="tc_filter_action">
											<option value=""><?php _e( 'All Actions', 'uncanny-learndash-reporting' ); ?></option>
											<?php foreach ( $ld_actions as $action ) { ?>
												<option value="<?php echo $action['verb'] ?>" <?php echo ( ! empty( $_GET['tc_filter_action'] ) && strtolower( $_GET['tc_filter_action'] ) == $action['verb'] ) ? 'selected="selected"' : '' ?>><?php echo ucfirst( $action['verb'] ) ?></option>
											<?php } // foreach( $ld_groups ) ?>
										</select>
									</div>
								</div>

							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--4">
								<div class="reporting-tincan-section__title">
									<?php _e( 'Date Range', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label>
											<input name="tc_filter_date_range" value="last"
												   type="radio" <?php echo ( empty( $_GET['tc_filter_date_range'] ) || sanitize_text_field( $_GET['tc_filter_date_range'] ) == 'last' ) ? 'checked="checked"' : '' ?> />
											<?php _e( 'View', 'uncanny-learndash-reporting' ); ?>
										</label>

										<select name="tc_filter_date_range_last" id="tc_filter_date_range_last">
											<option value="all" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'all' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'All Dates', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="week" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'week' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last Week', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="month" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'month' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last Month', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="90days" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '90days' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 90 Days', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="3months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '3months' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 3 Months', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="6months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '6months' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 6 Months', 'uncanny-learndash-reporting' ); ?>
											</option>
										</select>
									</div>

									<div class="reporting-tincan-section__field">
										<label>
											<input name="tc_filter_date_range" value="from"
												   type="radio" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range'] == 'from' ) ? 'checked="checked"' : '' ?> />
											<?php _e( 'From', 'uncanny-learndash-reporting' ); ?>
										</label>

										<input class="datepicker" name="tc_filter_start"
											   placeholder="<?php _e( 'Start Date', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ( ! empty( $_GET['tc_filter_start'] ) ) ? sanitize_text_field( $_GET['tc_filter_start'] ) : '' ?>"/>


										<input class="datepicker" name="tc_filter_end"
											   placeholder="<?php _e( 'End Date', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ( ! empty( $_GET['tc_filter_end'] ) ) ? sanitize_text_field( $_GET['tc_filter_end'] ) : '' ?>"/>
									</div>
								</div>
							</div>

						</div>

						<div class="reporting-tincan-footer">
							<?php submit_button( __( 'Search' ), 'primary', '', false, [
								'id'  => "do_tc_filter",
								'tab' => 'tin-can',
							] ); ?>

							<?php

							$reset_link = remove_query_arg( [
								'paged',
								'tc_filter_mode',
								'tc_filter_group',
								'tc_filter_user',
								'tc_filter_course',
								'tc_filter_lesson',
								'tc_filter_module',
								'tc_filter_action',
								'tc_filter_date_range',
								'tc_filter_date_range_last',
								'tc_filter_start',
								'tc_filter_end',
								'orderby',
								'order',
							] );

							if ( false === strpos( $reset_link, 'tab' ) ) {
								$reset_link .= '&tab=tin-can';
							}

							?>
							<a href="<?php echo $reset_link; ?>"
							   class="tclr-reporting-button"><?php _e( 'Reset', 'uncanny-learndash-reporting' ); ?></a>
						</div>
					</div>
				</div>
			</form>
		</div>

		<script>
			jQuery( document ).ready( function ( $ ){
				$( '.datepicker' ).datepicker( {
					'dateFormat': 'yy-mm-dd'
				} );

				$( '.dashicons-calendar-alt' ).click( function (){
					$( this ).prev().focus();
				} );
			} );
		</script>
		<?php
	}

	private static function ExtraTableNavBottom() {
		$per_pages = [
			10,
			25,
			50,
			100,
			200,
			500,
			self::$tincan_opt_per_pages,
		];

		$per_pages = array_unique( $per_pages );
		asort( $per_pages );

		?>
		<div id="tincan-filters-per_page">
			<select>
				<?php foreach ( $per_pages as $per_page ) { ?>
					<option value="<?php echo $per_page ?>" <?php echo ( self::$tincan_opt_per_pages == $per_page ) ? 'selected="selected"' : '' ?>><?php echo $per_page ?></option>
				<?php } // foreach( $ld_groups ) ?>
			</select>

			<?php _e( 'Per Page', 'uncanny-learndash-reporting' ); ?>
		</div>

		<div id="tincan-filters-export">
			<form action="<?php echo remove_query_arg( [
				'paged',
				'tc_filter_mode',
				'tc_filter_group',
				'tc_filter_user',
				'tc_filter_course',
				'tc_filter_lesson',
				'tc_filter_module',
				'tc_filter_action',
				'tc_filter_date_range',
				'tc_filter_date_range_last',
				'tc_filter_start',
				'tc_filter_end',
				'orderby',
				'order',
			] ) ?>" method="get" id="tincan-filters-bottom">
				<input type="hidden" name="tc_filter_mode" value="csv"/>
				<input type="hidden" name="tab" value="tin-can"/>

				<input type="hidden" name="tc_filter_group"
					   value="<?php echo ( ! empty( $_GET['tc_filter_group'] ) ) ? sanitize_text_field( $_GET['tc_filter_group'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_user"
					   value="<?php echo ( ! empty( $_GET['tc_filter_user'] ) ) ? sanitize_text_field( $_GET['tc_filter_user'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_course"
					   value="<?php echo ( ! empty( $_GET['tc_filter_course'] ) ) ? sanitize_text_field( $_GET['tc_filter_course'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_lesson"
					   value="<?php echo ( ! empty( $_GET['tc_filter_lesson'] ) ) ? sanitize_text_field( $_GET['tc_filter_lesson'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_module"
					   value="<?php echo ( ! empty( $_GET['tc_filter_module'] ) ) ? sanitize_text_field( $_GET['tc_filter_module'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_action"
					   value="<?php echo ( ! empty( $_GET['tc_filter_action'] ) ) ? sanitize_text_field( $_GET['tc_filter_action'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_date_range"
					   value="<?php echo ( ! empty( $_GET['tc_filter_date_range'] ) ) ? sanitize_text_field( $_GET['tc_filter_date_range'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_date_range_last"
					   value="<?php echo ( ! empty( $_GET['tc_filter_date_range_last'] ) ) ? sanitize_text_field( $_GET['tc_filter_date_range_last'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_start"
					   value="<?php echo ( ! empty( $_GET['tc_filter_start'] ) ) ? sanitize_text_field( $_GET['tc_filter_start'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_end"
					   value="<?php echo ( ! empty( $_GET['tc_filter_end'] ) ) ? sanitize_text_field( $_GET['tc_filter_end'] ) : '' ?>"/>
				<input type="hidden" name="orderby"
					   value="<?php echo ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( $_GET['orderby'] ) : '' ?>"/>
				<input type="hidden" name="order"
					   value="<?php echo ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( $_GET['order'] ) : '' ?>"/>

				<?php submit_button( __( 'Export To CSV', 'uncanny-learndash-reporting' ), 'action', '', false, [ 'id' => "do_tc_export_csv" ] ); ?>
			</form>
		</div>

		<?php
	}

	//! Search Box XAPI

	public static function ExtraTableNav_xapi( $which ) {
		switch ( $which ) {
			case 'top' :
				self::ExtraTableNavTop_xapi();
				break;
			case 'bottom' :
				self::ExtraTableNavBottom_xapi();
				break;
		}
	}

	private static function ExtraTableNavTop_xapi() {
		$ld_groups  = [];
		$ld_courses = [];
		if ( ! is_admin() ) {
			$group_leader_id = get_current_user_id();
			$user_group_ids  = learndash_get_administrators_group_ids( $group_leader_id, true );
			$args            = [
				'numberposts' => 9999,
				'include'     => array_map( 'intval', $user_group_ids ),
				'post_type'   => 'groups',
				'orderby'     => 'title',
				'order'       => 'ASC',
			];

			$ld_groups_user = get_posts( $args );
			if ( ! empty( $ld_groups_user ) ) {
				foreach ( $ld_groups_user as $ld_group ) {
					$ld_groups[] = [ 'group_id' => $ld_group->ID, 'group_name' => $ld_group->post_title ];
				}
			}
			// Courses
			if ( isset( $_GET['tc_filter_group'] ) && ! empty( $_GET['tc_filter_group'] ) ) {

				// check is user group
				if ( in_array( $_GET['tc_filter_group'], $user_group_ids ) ) {
					$group_id = absint( $_GET['tc_filter_group'] );
					$courses  = learndash_group_enrolled_courses( $group_id );
					$args     = [
						'numberposts' => 9999,
						'include'     => array_map( 'intval', $courses ),
						'post_type'   => 'sfwd-courses',
						'orderby'     => 'title',
						'order'       => 'ASC',
					];

					$courses = get_posts( $args );
					foreach ( $courses as $course ) {
						$ld_courses[] = [ 'course_id' => $course->ID, 'course_name' => $course->post_title ];
					}
				}
			}


		} else {

			// Group
			$ld_groups = self::$tincan_database->get_groups( 'quiz' );

			// Courses
			$ld_courses = self::$tincan_database->get_courses( 'quiz' );

			// Actions
			//$ld_actions = self::$tincan_database->get_questions();
		}
		?>

		<div class="reporting-tincan-filters">
			<form action="<?php echo remove_query_arg( 'paged' ) ?>" id="xapi-filters-top">
				<div class="reporting-metabox">
					<div class="reporting-dashboard-col-heading" id="coursesOverviewTableHeading">
						<?php _e( 'Filters', 'uncanny-learndash-reporting' ); ?>
					</div>
					<div class="reporting-dashboard-col-content">
						<input type="hidden" name="page"
							   value="<?php echo ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 1 ?>"/>
						<input type="hidden" name="tc_filter_mode" value="list"/>
						<input type="hidden" name="tab" value="xapi-tincan"/>

						<input type="hidden" name="orderby"
							   value="<?php echo ! empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'date-time' ?>"/>
						<input type="hidden" name="order"
							   value="<?php echo ! empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc' ?>"/>

						<div class="reporting-tincan-filters-columns">
							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--1">
								<div class="reporting-tincan-section__title">
									<?php _e( 'User & Group', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">

										<label for="tcx_filter_group"><?php _e( 'Group', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_group" id="tcx_filter_group">
											<option value=""><?php _e( 'All Groups', 'uncanny-learndash-reporting' ); ?></option>
											<?php foreach ( $ld_groups as $group ) { ?>
												<option value="<?php echo $group['group_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_group'] ) && sanitize_text_field( $_GET['tc_filter_group'] ) == $group['group_id'] ) ? 'selected="selected"' : '' ?>><?php echo $group['group_name'] ?></option>
											<?php } // foreach( $ld_groups ) ?>
										</select>

									</div>

									<div class="reporting-tincan-section__field">
										<label for="tcx_filter_user"><?php _e( 'User', 'uncanny-learndash-reporting' ); ?></label>
										<input name="tc_filter_user" id="tcx_filter_user"
											   placeholder="<?php _e( 'User', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ! empty( $_GET['tc_filter_user'] ) ? sanitize_text_field( $_GET['tc_filter_user'] ) : '' ?>"/>
									</div>
								</div>
							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--2">

								<div class="reporting-tincan-section__title">
									<?php _e( 'Content', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label for="tcx_filter_course"><?php echo \LearnDash_Custom_Label::get_label( 'course' ); ?></label>
										<select name="tc_filter_course" id="tcx_filter_course">
											<option value=""><?php echo sprintf( __( 'All %s', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'courses' ) ); ?></option>
											<?php foreach ( $ld_courses as $course ) { ?>
												<option value="<?php echo $course['course_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_course'] ) && sanitize_text_field( $_GET['tc_filter_course'] ) == $course['course_id'] ) ? 'selected="selected"' : '' ?>><?php echo $course['course_name'] ?></option>
											<?php } // foreach( $ld_courses ) ?>
										</select>
									</div>
									<div class="reporting-tincan-section__field">
										<label for="tcx_filter_module"><?php _e( 'Module', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_module" id="tcx_filter_module">
											<option value=""><?php _e( 'All Modules', 'uncanny-learndash-reporting' ); ?></option>
											<?php self::$tincan_database->print_modules_form_from_URL_parameter( 'quiz' ) ?>
										</select>
									</div>
								</div>

							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--3">

								<div class="reporting-tincan-section__title">
									<?php _e( 'Quiz', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label for="tc_filter_quiz"><?php _e( 'Question', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_quiz" id="tc_filter_quiz">

											<?php if ( ! empty( $_GET['tc_filter_quiz'] ) ) { ?>
												<option value="<?php echo sanitize_text_field( $_GET['tc_filter_quiz'] ) ?>"
														selected="selected"><?php echo ucfirst( self::limit_text( sanitize_text_field( $_GET['tc_filter_quiz'] ), 8 ) ) ?></option>
											<?php } // foreach( $ld_groups ) ?>
										</select>
									</div>
								</div>

								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label for="tc_filter_results"><?php _e( 'Result', 'uncanny-learndash-reporting' ); ?></label>
										<select name="tc_filter_results" id="tc_filter_results">
											<option value="" <?php echo ( ! empty( $_GET['tc_filter_results'] ) && strtolower( $_GET['tc_filter_results'] ) == '' ) ? 'selected="selected"' : '' ?>><?php _e( 'All Results', 'uncanny-learndash-reporting' ); ?></option>
											<option value="1" <?php echo ( ! empty( $_GET['tc_filter_results'] ) && strtolower( $_GET['tc_filter_results'] ) == 1 ) ? 'selected="selected"' : '' ?>><?php _e( 'Correct', 'uncanny-learndash-reporting' ); ?></option>
											<option value="-1" <?php echo ( ! empty( $_GET['tc_filter_results'] ) && strtolower( $_GET['tc_filter_results'] ) == '-1' ) ? 'selected="selected"' : '' ?>><?php _e( 'Incorrect', 'uncanny-learndash-reporting' ); ?></option>
										</select>
									</div>
								</div>

							</div>

							<div class="reporting-tincan-filters-col reporting-tincan-filters-col--4">
								<div class="reporting-tincan-section__title">
									<?php _e( 'Date Range', 'uncanny-learndash-reporting' ); ?>
								</div>
								<div class="reporting-tincan-section__content">
									<div class="reporting-tincan-section__field">
										<label>
											<input name="tc_filter_date_range" value="last"
												   type="radio" <?php echo ( empty( $_GET['tc_filter_date_range'] ) || $_GET['tc_filter_date_range'] == 'last' ) ? 'checked="checked"' : '' ?> />
											<?php _e( 'View', 'uncanny-learndash-reporting' ); ?>
										</label>

										<select name="tc_filter_date_range_last" id="tcx_filter_date_range_last">
											<option value="all" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'all' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'All Dates', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="week" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'week' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last Week', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="month" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'month' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last Month', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="90days" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '90days' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 90 Days', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="3months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '3months' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 3 Months', 'uncanny-learndash-reporting' ); ?>
											</option>
											<option value="6months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '6months' ) ? 'selected="selected"' : '' ?>>
												<?php _e( 'Last 6 Months', 'uncanny-learndash-reporting' ); ?>
											</option>
										</select>
									</div>

									<div class="reporting-tincan-section__field">
										<label>
											<input name="tc_filter_date_range" value="from"
												   type="radio" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range'] == 'from' ) ? 'checked="checked"' : '' ?> />
											<?php _e( 'From', 'uncanny-learndash-reporting' ); ?>
										</label>

										<input class="datepicker" name="tc_filter_start"
											   placeholder="<?php _e( 'Start Date', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ( ! empty( $_GET['tc_filter_start'] ) ) ? sanitize_text_field( $_GET['tc_filter_start'] ) : '' ?>"/>


										<input class="datepicker" name="tc_filter_end"
											   placeholder="<?php _e( 'End Date', 'uncanny-learndash-reporting' ); ?>"
											   value="<?php echo ( ! empty( $_GET['tc_filter_end'] ) ) ? sanitize_text_field( $_GET['tc_filter_end'] ) : '' ?>"/>
									</div>
								</div>
							</div>

						</div>

						<div class="reporting-tincan-footer">
							<?php submit_button( __( 'Search' ), 'primary', '', false, [
								'id'  => "do_tcx_filter",
								'tab' => 'tin-can',
							] ); ?>

							<?php

							$reset_link = remove_query_arg( [
								'paged',
								'tc_filter_mode',
								'tc_filter_group',
								'tc_filter_user',
								'tc_filter_course',
								'tc_filter_lesson',
								'tc_filter_module',
								'tc_filter_action',
								'tc_filter_quiz',
								'tc_filter_results',
								'tc_filter_date_range',
								'tc_filter_date_range_last',
								'tc_filter_start',
								'tc_filter_end',
								'orderby',
								'order',
							] );

							if ( false === strpos( $reset_link, 'tab' ) ) {
								$reset_link .= '&tab=xapi-tincan';
							}

							?>
							<a href="<?php echo $reset_link; ?>"
							   class="tclr-reporting-button"><?php _e( 'Reset', 'uncanny-learndash-reporting' ); ?></a>
						</div>
					</div>
				</div>
			</form>
		</div>

		<?php if ( isset( $_GET['tc_filter_mode'] ) && ! empty( $_GET['tc_filter_mode'] ) && $_GET['tc_filter_mode'] == 'list' ) {
			if ( is_admin() ) {
				?>

				<div class="reporting-table-info">
					<?php _e( 'To customize the columns that are displayed, use the Screen Options tab in the top right.', 'uncanny-learndash-reporting' ); ?>
				</div>

			<?php }
		} ?>

		<script>
			jQuery( document ).ready( function ( $ ){
				$( '.datepicker' ).datepicker( {
					'dateFormat': 'yy-mm-dd'
				} );

				$( '.dashicons-calendar-alt' ).click( function (){
					$( this ).prev().focus();
				} );
			} );
		</script>
		<?php
	}

	public static function limit_text( $text, $limit ) {
		if ( str_word_count( $text, 0 ) > $limit ) {
			$words = str_word_count( $text, 2 );
			$pos   = array_keys( $words );
			$text  = substr( $text, 0, $pos[ $limit ] ) . '...';
		}

		return $text;
	}

	private static function ExtraTableNavBottom_xapi() {
		$per_pages = [
			10,
			25,
			50,
			100,
			200,
			500,
			self::$tincan_opt_per_pages,
		];

		$per_pages = array_unique( $per_pages );
		asort( $per_pages );

		?>
		<div id="tincan-filters-per_page">
			<select>
				<?php foreach ( $per_pages as $per_page ) { ?>
					<option value="<?php echo $per_page ?>" <?php echo ( self::$tincan_opt_per_pages == $per_page ) ? 'selected="selected"' : '' ?>><?php echo $per_page ?></option>
				<?php } // foreach( $ld_groups ) ?>
			</select>

			<?php _e( 'Per Page', 'uncanny-learndash-reporting' ); ?>
		</div>

		<div id="tincan-filters-export">
			<form action="<?php echo remove_query_arg( [
				'paged',
				'tc_filter_mode',
				'tc_filter_group',
				'tc_filter_user',
				'tc_filter_course',
				'tc_filter_lesson',
				'tc_filter_module',
				'tc_filter_action',
				'tc_filter_quiz',
				'tc_filter_results',
				'tc_filter_date_range',
				'tc_filter_date_range_last',
				'tc_filter_start',
				'tc_filter_end',
				'orderby',
				'order',
			] ) ?>" method="get" id="xapi-filters-bottom">
				<input type="hidden" name="tc_filter_mode" value="csv-xapi"/>

				<input type="hidden" name="tc_filter_group"
					   value="<?php echo ( ! empty( $_GET['tc_filter_group'] ) ) ? sanitize_text_field( $_GET['tc_filter_group'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_user"
					   value="<?php echo ( ! empty( $_GET['tc_filter_user'] ) ) ? sanitize_text_field( $_GET['tc_filter_user'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_course"
					   value="<?php echo ( ! empty( $_GET['tc_filter_course'] ) ) ? sanitize_text_field( $_GET['tc_filter_course'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_lesson"
					   value="<?php echo ( ! empty( $_GET['tc_filter_lesson'] ) ) ? sanitize_text_field( $_GET['tc_filter_lesson'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_module"
					   value="<?php echo ( ! empty( $_GET['tc_filter_module'] ) ) ? sanitize_text_field( $_GET['tc_filter_module'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_action"
					   value="<?php echo ( ! empty( $_GET['tc_filter_action'] ) ) ? sanitize_text_field( $_GET['tc_filter_action'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_quiz"
					   value="<?php echo ( ! empty( $_GET['tc_filter_quiz'] ) ) ? sanitize_text_field( $_GET['tc_filter_quiz'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_results"
					   value="<?php echo ( ! empty( $_GET['tc_filter_results'] ) ) ? sanitize_text_field( $_GET['tc_filter_results'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_date_range"
					   value="<?php echo ( ! empty( $_GET['tc_filter_date_range'] ) ) ? sanitize_text_field( $_GET['tc_filter_date_range'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_date_range_last"
					   value="<?php echo ( ! empty( $_GET['tc_filter_date_range_last'] ) ) ? sanitize_text_field( $_GET['tc_filter_date_range_last'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_start"
					   value="<?php echo ( ! empty( $_GET['tc_filter_start'] ) ) ? sanitize_text_field( $_GET['tc_filter_start'] ) : '' ?>"/>
				<input type="hidden" name="tc_filter_end"
					   value="<?php echo ( ! empty( $_GET['tc_filter_end'] ) ) ? sanitize_text_field( $_GET['tc_filter_end'] ) : '' ?>"/>
				<input type="hidden" name="orderby"
					   value="<?php echo ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( $_GET['orderby'] ) : '' ?>"/>
				<input type="hidden" name="order"
					   value="<?php echo ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( $_GET['order'] ) : '' ?>"/>

				<?php submit_button( __( 'Export To CSV', 'uncanny-learndash-reporting' ), 'action', '', false, [ 'id' => "do_tc_export_csv_xapi" ] ); ?>
			</form>
		</div>

		<?php
	}

	private static function check_for_other_uo_plugin_classes( $uo_plugin ) {

		// plugins dir
		$directory_contents = scandir( WP_PLUGIN_DIR );

		// loop through all contents
		foreach ( $directory_contents as $content ) {

			// exclude parent directories
			if ( $content !== '.' or $content !== '..' ) {

				// create absolute path
				$plugin_dir = WP_PLUGIN_DIR . '/' . $content;

				if ( is_dir( $plugin_dir ) ) {

					if ( 'pro' === $uo_plugin ) {
						if ( 'uo-plugin-pro' === $content || 'uncanny-reporting-pro' === $content ) {
							// Check if plugin is active
							if ( is_plugin_active( $content . '/uncanny-reporting-pro.php' ) ) {
								return $plugin_dir . '/src/classes/';
							}
						}
					}

					if ( 'custom' === $uo_plugin ) {

						$explode_directory = explode( '-', $content );
						if ( 3 === count( $explode_directory ) ) {
							// custom plugin directory is may be prefixed with client name
							// check suffix uo-custom-plugin
							if ( in_array( 'uo', $explode_directory ) && in_array( 'custom', $explode_directory ) && in_array( 'plugin', $explode_directory ) ) {

								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-reporting-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}

							}

							if ( 'uncanny-reporting-custom' === $content ) {

								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-reporting-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}

							}
						}

					}

				}

			}
		}

		return false;

	}

	public function filter__screen_settings( $screen_settings, $screen ) {
		if ( ! ( $screen->parent_base === 'uncanny-learnDash-reporting' ) ) {
			return $screen_settings;
		}
		$user_settings = get_user_meta( get_current_user_id(), 'xapi_report_columns', true );

		self::$xapi_report_columns = wp_parse_args( $user_settings, self::$xapi_report_columns );

		$out = '';
		foreach ( self::$xapi_report_columns as $option => $args ) {
			$label = $args['label'] or $label = $option;
			$default = $args['default'];
			$type    = $args['type'];
			switch ( $type ) {
				default:
					$value = self::$xapi_report_columns[ $option ]['value'];
					if ( is_null( $value ) ) {
						$value = $default;
					}
					$out .= sprintf( '<label for="%1$s"> <input id="%1$s" name="wp_screen_options[value][%1$s]" value="1" type="checkbox" class="screen-per-page" %3$s />%2$s</label>', esc_attr( $option ), $label, ( $value == true ? 'checked="checked"' : '' ) );
			}
		}
		if ( $out ) {
			$screen_settings .= sprintf( '
				<fieldset class="metabox-prefs">
				<legend>xAPI Quiz Report</legend>
				<input type="hidden" name="wp_screen_options[option]" value="%s" />
				%s%s</fieldset>',
				'xapi_report_columns',
				$out,
				get_submit_button( __( 'Apply' ), 'button', 'screen-options-apply', false )
			);
		}

		return $screen_settings;
	}

	public function filter__set_screen_option( $status, $option, $values ) {
		if ( $option === 'xapi_report_columns' ) {
			// This class owns the option.
			if ( is_array( $values ) ) {
				foreach ( self::$xapi_report_columns as $option => $details ) {
					self::$xapi_report_columns[ $option ]['value'] = false;
					if ( isset( $values[ $option ] ) ) {
						self::$xapi_report_columns[ $option ]['value'] = true;
					}
				}

				return self::$xapi_report_columns;
			}
		}

		return $status;
	}

}
