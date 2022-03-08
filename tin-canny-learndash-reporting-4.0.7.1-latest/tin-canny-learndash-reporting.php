<?php
/*
 * Plugin Name:         Tin Canny Reporting for LearnDash
 * Description:         Add a Tin Can xAPI Learning Record Store (LRS) inside WordPress with powerful reporting tools for LearnDash and Tin Can statements.
 * Author:              Uncanny Owl
 * Author URI:          https://www.uncannyowl.com
 * Plugin URI:          https://www.uncannyowl.com/tin-can-lrs-learndash-report-toolkit/
 * Text Domain:         uncanny-learndash-reporting
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Version:             4.0.7.1
 * Requires at least:   5.2
 * Requires PHP:        7.2
*/

// All Class instance are store in Global Variable $uncanny_learndash_reporting
global $uncanny_learndash_reporting;
if ( defined( 'ABSPATH' ) ) {
	if ( ! defined( 'UO_ABS_PATH' ) ) {
		define( 'UO_ABS_PATH', ABSPATH );
	}
} elseif ( defined( 'WP_CONTENT_DIR' ) ) {
	if ( ! defined( 'UO_ABS_PATH' ) ) {
		define( 'UO_ABS_PATH', str_replace( 'wp-content', '', WP_CONTENT_DIR ) );
	}
}

// Define version
if ( ! defined( 'UNCANNY_REPORTING_VERSION' ) ) {
	define( 'UNCANNY_REPORTING_VERSION', '4.0.7.1' );
}

if ( ! defined( 'UO_REPORTING_FILE' ) ) {
	define( 'UO_REPORTING_FILE', __FILE__ );
}

if ( ! defined( 'UO_REPORTING_DEBUG' ) ) {
	define( 'UO_REPORTING_DEBUG', true );
}

// Change the name of the stored column in the reporting table
global $wpdb;

$table_name = $wpdb->prefix . 'uotincan_reporting';

if ( 'yes' !== get_option( 'tincanny_table_column_changed', 'no' ) ) {


	if ( '1' === $wpdb->get_var( "SELECT COUNT(1) FROM information_schema.tables WHERE table_name='$table_name'" ) ) {
		if ( '1' === $wpdb->get_var( "SELECT COUNT(1) FROM information_schema.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'stored'" ) ) {
			// Not using DB Delta because during column name change queries it leaves a blank column
			$wpdb->query( "ALTER TABLE {$table_name} CHANGE `stored` `xstored` DATETIME NULL DEFAULT NULL;" );
		}
	}

	update_option( 'tincanny_table_column_changed', 'yes', true );
}

add_filter( 'tc-optimized-build', '__return_true' );

// Show admin notices for minimum versions of PHP, WordPress, and LearnDash
add_action( 'admin_notices', 'uo_reporting_learndash_version_notice' );

function uo_reporting_learndash_version_notice() {

	global $wp_version;

	//Minimum versions
	$wp         = '5.2';
	$php        = '7.0';
	$learn_dash = '3.2';

	// Set LearnDash version
	$learn_dash_version = 0;
	if ( defined( 'LEARNDASH_VERSION' ) ) {
		$learn_dash_version = LEARNDASH_VERSION;
	}

	// Get current screen
	$screen = get_current_screen();

	if ( ! version_compare( PHP_VERSION, '5.3', '>=' ) && ( isset( $screen ) && 'plugins.php' === $screen->parent_file ) ) {

		// Show notice if php version is less than 5.3 and the current admin page is plugins.php
		$version = $php;
		$current = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

		?>
		<div class="notice notice-error">
			<h3><?php echo sprintf(

					esc_html__( 'The %s requires PHP version %s or higher (5.6 or higher is recommended).Because you are using unsupported version of PHP (%s), the Reporting plugin will not initialize. Please contact your hosting company to upgrade to PHP 5.6 or higher.', 'uncanny-learndash-reporting' ),

					'Uncanny LearnDash Reporting', $version, $current ); ?>
			</h3>
		</div>
		<?php

	} elseif ( version_compare( $wp_version, $wp, '<' ) && ( isset( $_REQUEST['page'] ) && 'uncanny-learnDash-reporting' === $_REQUEST['page'] ) ) {

		// Show notice if WP version is less than 4.0 and the current page is the Reporting settings page
		$flag    = 'WordPress';
		$version = $wp;
		$current = $wp_version;

		?>
		<!-- No Notice Style below WordPress -->
		<style>
			.notice-error {
				border-left-color: #dc3232 !important;
			}

			.notice {
				background: #fff;
				border-left: 4px solid #fff;
				-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				margin: 5px 15px 2px;
				padding: 1px 12px;
			}
		</style>
		<div class="notice notice-error">
			<h3><?php echo sprintf(

					esc_html__( 'The %s plugin requires %s version %s or greater. Your current version is %s.', 'uncanny-learndash-reporting' ),

					'Uncanny LearnDash Reporting', $flag, $version, $current ); ?>
			</h3>
		</div>
		<?php

	} elseif ( ! version_compare( $learn_dash_version, $learn_dash, '>=' ) && ( isset( $_REQUEST['page'] ) && 'uncanny-learnDash-reporting' === $_REQUEST['page'] ) ) {

		// Show notice if LearnDash is less than 2.1 and the current page is the Reporting settings page
		if ( 0 !== $learn_dash_version ) {

			?>
			<div class="notice notice-error">
				<h3><?php echo sprintf(

						esc_html__( 'Uncanny LearnDash Reporting requires LearnDash version %s or higher to work properly. Please make sure you have LearnDash version %s or higher installed. Your current version is: %s', 'uncanny-learndash-reporting' ),
						$learn_dash,
						$learn_dash,
						$learn_dash_version ); ?>
				</h3>
			</div>
			<?php

		} elseif ( ! class_exists( 'SFWD_LMS' ) ) {

			?>
			<div class="notice notice-error">
				<h3><?php echo sprintf(

						esc_html__( 'Uncanny LearnDash reporting requires LearnDash version %s or higher to work properly. Please make sure you have LearnDash version %s or higher installed.', 'uncanny-learndash-reporting' ),
						$learn_dash,
						$learn_dash ); ?>
				</h3>
			</div>
			<?php

		}

	}
}

// Allow Translations to be loaded
add_action( 'plugins_loaded', 'uncanny_learndash_reporting_text_domain' );

function uncanny_learndash_reporting_text_domain() {
	load_plugin_textdomain( 'uncanny-learndash-reporting', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'wp', 'tc_last_known_course' );

function tc_last_known_course() {


	$user = wp_get_current_user();

	if ( is_user_logged_in() ) {

		/* declare $post as global so we get the post->ID of the current page / post */
		global $post;

		/* Limit the plugin to LearnDash specific post types */
		$learn_dash_post_types =
			array(
				'sfwd-courses',
				'sfwd-lessons',
				'sfwd-topic',
				'sfwd-quiz',
				'sfwd-assignment',
			);

		if ( is_singular( $learn_dash_post_types ) ) {
			update_user_meta( $user->ID, 'tincan_last_known_ld_module', $post->ID );
			$course_id = learndash_get_course_id( $post );
			update_user_meta( $user->ID, 'tincan_last_known_ld_course', $course_id );
		}
	}
}

// Import Gutenberg Blocks
require_once( dirname( __FILE__ ) . '/src/blocks/blocks.php' );

// PHP version 5.3 and up only
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {

	// On first activation, redirect to reporting settings page if min php version is met
	register_activation_hook( __FILE__, 'uncanny_learndash_reporting_plugin_activate' );
	add_action( 'admin_init', 'uncanny_learndash_reporting_plugin_redirect' );

	function uncanny_learndash_reporting_plugin_activate() {

		/* Checks for LearnDash */
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			$message = '<p style="text-align: center;">Please download and activate LearnDash <br />before activating Tin Canny Reporting for LearnDash. </p>';
			$message .= '<p style="text-align: center;" ><a  href="' . admin_url( 'plugins.php' ) . '">Go Back</a></p>';

			wp_die( $message );
		}

		// Set which roles will need access to reporting
		$set_role_for_reporting = array( 'group_leader', 'administrator' );

		// Loop through all roles that need the reporting capability added
		foreach ( $set_role_for_reporting as $role ) {

			if ( ! $role ) {
				continue;
			}

			// Get the role class instance
			$group_leader_role = get_role( $role );

			if ( ! $group_leader_role ) {
				continue;
			}

			// Add the reporting capability to the role
			$group_leader_role->add_cap( apply_filters( 'uo_tincanny_reporting_capability', 'tincanny_reporting' ) );

		}

		//uo_log('BEFORE', 'BEFORE', true, 'activation');
		do {
			//uo_log( 'BEFORE FUNCTION CALL', 'BEFORE FUNCTION CALL', true, 'activation');
			// Your logic
			$urls = uo_clean_urls();
			//uo_log($urls, 'RETURN', true, 'activation');
		} while ( ! $urls['completed'] && $urls['success'] );

		//uo_log('AFTER', 'BEFOAFTERRE', true, 'activation');

		update_option( 'uncanny_learndash_ reporting_plugin_do_activation_redirect', 'yes' );
		//uo_log('OPTIONS UPDATED', 'OPTIONS UPDATED', true, 'activation');
	}

	/**
	 * This function runs when WordPress completes its upgrade process
	 * It iterates through each plugin updated to see if ours is included
	 *
	 * @param $upgrader_object Array
	 * @param $options         Array
	 */
	function wp_ou_upgrade_completed( $upgrader_object, $options ) {
		// The path to our plugin's main file
		$our_plugin = plugin_basename( __FILE__ );
		// If an update has taken place and the updated type is plugins and the plugins element exists
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
			// Iterate through the plugins being updated and check if ours is there
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin == $our_plugin ) {
					// Set a transient to record that our plugin has just been updated
					set_transient( 'wp_uo_updated', 1 );
				}
			}
		}
	}

	add_action( 'upgrader_process_complete', 'wp_ou_upgrade_completed', 10, 2 );

	/**
	 * Show a notice to anyone who has just updated this plugin
	 * This notice shouldn't display to anyone who has just installed the plugin for the first time
	 */
	function wp_uo_clean_urls() {
		// Check the transient to see if we've just updated the plugin
		if ( get_transient( 'wp_uo_updated' ) ) {
			do {
				$urls = uo_clean_urls();
			} while ( ! $urls['completed'] && $urls['success'] );
			delete_transient( 'wp_uo_updated' );
		}
	}

	add_action( 'admin_init', 'wp_uo_clean_urls' );

	function uo_clean_urls() {

		$sql_url_pattern = "^(http(s)?://.)(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,}";

		$php_url_pattern = "/^(http(s)?:\/\/.)(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,}/";

		global $wpdb;

		$q_url = "SELECT url FROM `{$wpdb->prefix}snc_file_info` WHERE url REGEXP '{$sql_url_pattern}' LIMIT 1";

		//uo_log( $q_url, '$q_url', true, 'activation');

		$url = $wpdb->get_results( $q_url, ARRAY_N );

		//uo_log( $url, '$url', true, 'activation');

		if ( empty( $url ) ) {
			//uo_log( $url, '$url is empty', true, 'activation');
			return array(
				'message'   => 'No full URLs found.',
				'completed' => true,
				'success'   => true,
			);
		}

		if ( ! isset( $url[0] ) && ! isset( $url[0][0] ) ) {
			//uo_log( $url, '$url[0][0] not set', true, 'activation');
			return array(
				'message'   => 'Please contact Uncanny Owl.',
				'completed' => false,
				'success'   => false,
			);
		}

		$matched = preg_match( $php_url_pattern, $url[0][0], $base_url );
		//uo_log( $matched, '$matched', true, 'activation');

		if ( ! $matched ) {
			//uo_log( $matched, '$matched is false', true, 'activation');
			return array(
				'message'   => 'We found and skipped a possible malformed URL in your database. ' . $url[0][0],
				'completed' => false,
				'success'   => false,
			);
		}

		$base_url = $base_url[0];

		//uo_log( $base_url, '$base_url', true, 'activation');

		$q_remove_urls = "UPDATE `{$wpdb->prefix}snc_file_info` SET URL = REPLACE(url, '{$base_url}', '') WHERE URL LIKE '{$url[0][0]}%'";

		//uo_log( $q_remove_urls, '$q_remove_urls', true, 'activation');

		$wpdb->query( $q_remove_urls );

		//uo_log( $wpdb->last_result, 'last_result', true, 'activation');

		//uo_log( $wpdb->last_error, 'last_error', true, 'activation');

		return array(
			'message'   => 'We found and skipped a possible malformed URL in your database. ' . $url[0][0],
			'completed' => false,
			'success'   => true,
		);

	}

	function uo_log( $trace_message = '', $trace_heading = '', $force_log = false, $file_name = 'logs' ) {

		// Only return log if debug mode is on OR if log is forced
		if ( ! $force_log ) {

			if ( ! UO_REPORTING_DEBUG ) {
				return false;
			}
		}

		$timestamp = date( "F j, Y, g:i a" );

		$current_page_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$trace_start = "\n===========================<<<< $timestamp >>>>===========================\n";

		$trace_heading = "* Heading: $trace_heading \n";

		$trace_heading .= "* Current Page: $current_page_link \n";

		$trace_end = "\n===========================<<<< TRACE END >>>>===========================\n\n";

		$trace_message = print_r( $trace_message, true );

		//$file = dirname( self::get_plugin_file() ) . '/uo-' . $file_name . '.log';
		$file = WP_CONTENT_DIR . '/uo-' . $file_name . '.log';

		$error_log = error_log( $trace_start . $trace_heading . $trace_message . $trace_end, 3, $file );

		return $error_log;

	}

	function uncanny_learndash_reporting_plugin_redirect() {

		if ( 'yes' === get_option( 'uncanny_learndash_ reporting_plugin_do_activation_redirect', 'no' ) ) {

			update_option( 'uncanny_learndash_ reporting_plugin_do_activation_redirect', 'no' );

			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( admin_url( 'admin.php?page=uncanny-reporting-license-activation' ) );
				exit();
			}
		}
	}

	// Add settings link on plugin page
	$uncanny_learndash_reporting_plugin_basename = plugin_basename( __FILE__ );

	add_filter( 'plugin_action_links_' . $uncanny_learndash_reporting_plugin_basename, 'uncanny_learndash_reporting_plugin_settings_link' );

	function uncanny_learndash_reporting_plugin_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=uncanny-reporting-license-activation' ) . '">' . __( 'Licensing', 'uncanny-learndash-reporting' ) . '</a>';
		array_unshift( $links, $settings_link );
		$settings_link = '<a href="' . admin_url( 'admin.php?page=snc_options' ) . '">' . __( 'Settings', 'uncanny-learndash-reporting' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	if ( defined( 'LEARNDASH_VERSION' ) ) {
		/* Load Reporting */

		// load protection
		include_once( dirname( __FILE__ ) . '/src/includes/tin-canny-protection.php' );

		// Plugins Configurations File
		include_once( dirname( __FILE__ ) . '/src/config.php' );

		// Load all plugin classes(functionality)
		include_once( dirname( __FILE__ ) . '/src/boot.php' );

		$boot                              = 'uncanny_learndash_reporting\Boot';
		$uncanny_learndash_reporting_class = new $boot;

		/* Load Storyline/Captivate*/
		include_once( dirname( __FILE__ ) . '/src/uncanny-articulate-and-captivate/storyline-and-captivate.php' );

		/* Load Storyline/Captivate*/
		include_once( dirname( __FILE__ ) . '/src/uncanny-tincan/uncanny-tincan.php' );
	} else {

		add_action( 'admin_notices', 'uo_reporting_learndash_not_activated' );


	}
	function uo_reporting_learndash_not_activated() {
		?>
		<div class="notice notice-error">
			<h4>
				<?php echo __( 'Warning: Tin Canny Reporting for LearnDash requires LearnDash. Please install LearnDash before using the plugin.', 'uncanny-learndash-reporting' ); ?>
			</h4>
		</div>
		<?php

	}
}
