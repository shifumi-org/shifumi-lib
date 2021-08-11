<?php
/**
 * Set up LearnDash Dependency Check
 *
 * @package LearnDash
 * @since 2.3
 */

if ( !class_exists( 'LearnDash_Dependency_Check_ProPanel' ) ) {

	final class LearnDash_Dependency_Check_ProPanel {

		private static $instance;
		
		
		/**
		 * The displayed message shown to the user on admin pages. 
		 */
		private $admin_notice_message = '';

		/**
		 * The array of plugin) to check Should be key => label paird. The label can be anything to display
		 */
		private $plugins_to_check = array();

		/**
		 * Array to hold the inactive plugins. This is populated during the 
		 * admin_init action via the function call to check_inactive_plugin_dependency()
		 */
		private $plugins_inactive = array();
		
		
		/**
		 * LearnDash_ProPanel constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );		
		}

		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}
		
		/**
		 * LearnDash_ProPanel constructor.
		 */
		public function check_dependency_results() {
			if ( empty( $this->plugins_inactive ) ) {
				return true;
			}
			
			return false;
		}

		/**
		 * callback function for the admin_init action
		 */
		function plugins_loaded() {
			$this->check_inactive_plugin_dependency();
		}

		/**
		 * Function called during the admin_init process to check if required plugins 
		 * are present and active. Handles regular and Multisite checks. 
		 */
		function check_inactive_plugin_dependency( $set_admin_notice = true ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			//$all_plugins = get_plugins(); 
			//error_log('all_plugins<pre>'. print_r($all_plugins, true) .'</pre>');
			
			//$current_plugins = get_site_transient( 'update_plugins' );
			//error_log('current_plugins<pre>'. print_r($current_plugins, true) .'</pre>');

			if ( !empty( $this->plugins_to_check ) ) {
				if ( !function_exists('is_plugin_active' ) ) {
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				foreach( $this->plugins_to_check as $plugin_key => $plugin_data ) {
					if ( !is_plugin_active( $plugin_key ) ) {
						if ( is_multisite() ) {
							if ( !is_plugin_active_for_network( $plugin_key ) ) {
								$this->plugins_inactive[$plugin_key] = $plugin_data;
							}
						} else {
							$this->plugins_inactive[$plugin_key] = $plugin_data;
						}
					} else {
						if ( ( isset( $plugin_data['class'] ) ) && ( !empty( $plugin_data['class'] ) ) && ( !class_exists( $plugin_data['class'] ) ) ) {
							$this->plugins_inactive[$plugin_key] = $plugin_data;
						} 
					}
				}

				if ( ( !empty( $this->plugins_inactive ) ) && ( $set_admin_notice ) ) {
					add_action( 'admin_notices', array( $this, 'notify_user_learndash_required' ) );
				}
			}

			return $this->plugins_inactive;
		}

		/**
		 * Function to set custom admin motice message
		 */
		public function set_message( $message = '' ) {
			if ( !empty( $message ) ) {
				$this->admin_notice_message = $message;
			}
		}

		public function set_dependencies( $plugins = array() ) {
			if ( is_array( $plugins ) )
				$this->plugins_to_check = $plugins;
		}

		/**
		 * Notify user that LearnDash is required.
		 */
		public function notify_user_learndash_required() {
			if ( ( !empty( $this->admin_notice_message ) ) && ( !empty( $this->plugins_inactive ) ) ) {
				
				$admin_notice_message = sprintf( $this->admin_notice_message . ' %s', implode(', ', wp_list_pluck($this->plugins_inactive, 'label' ) ) );
				if ( !empty( $admin_notice_message ) ) {
					?>
					<div class="notice notice-error ld-notice-error is-dismissible">
						<p><?php echo $admin_notice_message; ?></p>
					</div>
					<?php
				}
			}
		}
	}
}
