<?php
/**
 * LearnDash ProPanel Widget Base
 *
 * @package LearnDash_ProPanel
 * @since 2.0
 */

if ( !class_exists( 'LearnDash_ProPanel_Widget' ) ) {
	class LearnDash_ProPanel_Widget {

		/**
		 * @var LearnDash_ProPanel_Overview The reference to *Singleton* instance of this class
		 */
		private static $instance;

		protected $post_data = array();
		protected $activity_query_args = array();
		
		protected $registered_filters = array();
		protected $filter_key;
		protected $filter_search_placeholder;
		protected $filter_headers = array();
		protected $filter_template_table;
		protected $filter_template_row;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return LearnDash_ProPanel_Widget The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * LearnDash_ProPanel_Overview constructor.
		 */
		public function __construct() {
			add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
			add_action( 'wp_ajax_learndash_propanel_template', array( $this, 'load_template' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 1000 );
			add_action( 'enqueue_scripts', array( $this, 'scripts' ), 1000 );
		}

		/**
		 *
		 */
		function scripts() {
			if ( is_admin() ) {
				$screen = get_current_screen();

				if ( in_array( $screen->id, array( 'dashboard', 'dashboard_page_propanel-reporting' ) ) ) {
					$menu_user_cap = '';
				
					if ( learndash_is_admin_user() ) 
						$menu_user_cap = LEARNDASH_ADMIN_CAPABILITY_CHECK;
					else if ( learndash_is_group_leader_user() ) 
						$menu_user_cap = LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK;
					else if (current_user_can( 'propanel_widgets' ) ) {
						$menu_user_cap = 'propanel_widgets';
					}
				
					if ( !empty( $menu_user_cap ) ) {
						// Specific code to deregister the BadgeOS version of select JS libs. This seems to 
						// cause a conflict with the version needed for PP on the Dashboard. 
						wp_deregister_script( 'badgeos-select2' );
						wp_deregister_style( 'badgeos-select2-css' );
				
						wp_enqueue_script( 'ld-propanel-select2-script' );
						wp_enqueue_script( 'ld-propanel-chart-script' );

						wp_enqueue_style( 'ld-propanel-select2-style' );

						wp_localize_script( 'ld-propanel-script', 'ld_propanel_reporting', array(
							/**
							 * Filter CSV Export File Name
							 */
							'filename' => apply_filters( 'ld_propanel_export_filename', 'learndash-report-' . current_time( 'Y-m-d' ) ) . '.csv',
							'ajax_email_error' => esc_html__( 'ProPanel Email: AJAX submission could not complete, please try again.', 'ld_propanel' ),
						) );
					} else {
						wp_deregister_script( 'ld-propanel-select2-script' );
						wp_deregister_script( 'ld-propanel-chart-script' );
						wp_deregister_style( 'ld-propanel-select2-style' );
					}
				}
			} else {
				wp_enqueue_style( 'ld-propanel-select2-style' );
			}
		}

		/**
		 * Register Widget
		 */
		function register_widget() {
			// Only show the ProPanel widgets for admin and group leaders
			if ( ( learndash_is_group_leader_user() ) || ( learndash_is_admin_user() ) || ( current_user_can( 'propanel_widgets' ) ) ) { 
				$is_dashboard = true;
			} else {
				$is_dashboard = false;
			}

			/** This filter is documented in includes/class-ld-propanel.php */
			$is_dashboard = apply_filters( 'ld_propanel_dashboard_show_widgets', $is_dashboard );
			if ( true === $is_dashboard ) {
				wp_add_dashboard_widget( 'learndash-propanel-' . $this->name, $this->label, array( $this, 'initial_template' ) );
			}
		}

		/**
		 * Initial Template
		 */
		function initial_template() {}

		/**
		 * Load Template(s)
		 */
		function load_template() {
			check_ajax_referer( 'ld-propanel', 'nonce' );

			if ( isset( $_GET['template'] ) && ! empty( $_GET['template'] ) ) {
				$output = apply_filters( 'learndash_propanel_template_ajax', '', $_GET['template'] );
				wp_send_json_success( array( 'output' => $output ) );
			}
			die();
		}
	}
}