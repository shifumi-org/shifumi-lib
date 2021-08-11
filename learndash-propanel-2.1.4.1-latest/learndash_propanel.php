<?php
/**
 * Plugin Name: LearnDash LMS - ProPanel
 * Plugin URI: http://www.learndash.com
 * Description: Easily manage and view your LearnDash LMS activity.
 * Version: 2.1.4.1
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ld_propanel
 * Domain Path: /languages
 * @package LearnDash_ProPanel
 * @version 2.1.4
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Setup Constants
 */

if ( ! defined( 'LD_PP_VERSION' ) ) {
	define( 'LD_PP_VERSION', '2.1.4.1' );
}

if ( ! defined( 'LD_PP_PLUGIN_DIR' ) ) {
	//define( 'LD_PP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	$WP_PLUGIN_DIR_tmp = str_replace( '\\', '/', WP_PLUGIN_DIR );
	define( 'LD_PP_PLUGIN_DIR', trailingslashit( $WP_PLUGIN_DIR_tmp . '/' . basename( dirname( __FILE__ ) ) ) );
}

if ( ! defined( 'LD_PP_PLUGIN_URL' ) ) {
	//define( 'LD_PP_PLUGIN_URL', plugin_dir_url( __FILE__  ) );
	$url = trailingslashit( WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );
	$url = str_replace( array( 'https://', 'http://' ), array( '//', '//' ), $url );
	define( 'LD_PP_PLUGIN_URL', $url );
}

$learndash_shortcode_used = false;

/**
 * Load ProPanel
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ld-propanel.php';


/**
 * Support for Gutenberg Editor
 */
if ( ( defined( 'LEARNDASH_GUTENBERG' ) ) && ( LEARNDASH_GUTENBERG === true ) ) {
	require_once dirname( __FILE__ ) . '/includes/gutenberg/index.php';
}


add_action(
	'plugins_loaded',
	function() {
		LearnDash_ProPanel::get_instance();
	}
);

function LD_ProPanel() {
	LearnDash_ProPanel::get_instance();
}

require plugin_dir_path( __FILE__ ) . 'includes/class-ld-dependency-check.php';
LearnDash_Dependency_Check_ProPanel::get_instance()->set_dependencies(
	array(
		'sfwd-lms/sfwd_lms.php' => array(
			'label' => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class' => 'SFWD_LMS',
		),
	)
);
LearnDash_Dependency_Check_ProPanel::get_instance()->set_message(
	esc_html__( 'LearnDash ProPanel requires the following plugin(s) be active:', 'ld_propanel' ) 
);

/**
 * Activation
 */
function activate_learndash_propanel() {
	$roles = get_editable_roles();
	if ( ! empty( $roles ) ) {

		if ( ! defined( 'LEARNDASH_ADMIN_CAPABILITY_CHECK' ) ) {
			define( 'LEARNDASH_ADMIN_CAPABILITY_CHECK', 'manage_options' );
		}

		if ( ! defined( 'LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK' ) ) {
			define( 'LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK', 'group_leader' );
		}

		foreach ( $roles as $role_name => $role_info ) {
			$role = get_role( $role_name );
			if ( ( $role ) && ( $role instanceof WP_Role ) ) {
				if ( ! $role->has_cap( 'propanel_widgets' ) ) {
					$cap_enabled = false;
					if ( ( defined( 'LEARNDASH_ADMIN_CAPABILITY_CHECK' ) ) && ( $role->has_cap( LEARNDASH_ADMIN_CAPABILITY_CHECK ) ) ) {
						$cap_enabled = true;
					} elseif ( ( defined( 'LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK' ) )
					 && ( ( $role_name == LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK ) || $role->has_cap( LEARNDASH_GROUP_LEADER_CAPABILITY_CHECK ) ) ) {
						$cap_enabled = true;
					}

					$role->add_cap( 'propanel_widgets', $cap_enabled );
				}
			}
		}
	}
}

/**
 * Deactivation
 */
function deactivate_learndash_propanel() {
}

register_activation_hook( __FILE__, 'activate_learndash_propanel' );
register_deactivation_hook( __FILE__, 'deactivate_learndash_propanel' );

// Load the auto-update class
add_action( 'init', 'nss_plugin_updater_activate_learndash_propanel' );
function nss_plugin_updater_activate_learndash_propanel() {
	//if(!class_exists('nss_plugin_updater'))
	require_once dirname( __FILE__ ) . '/wp_autoupdate_propanel.php';

	$nss_plugin_updater_plugin_remote_path = 'https://support.learndash.com/';
	$nss_plugin_updater_plugin_slug        = plugin_basename( __FILE__ );

	new nss_plugin_updater_learndash_propanel( $nss_plugin_updater_plugin_remote_path, $nss_plugin_updater_plugin_slug );
}

function learndash_propanel_admin_tabs( $admin_tabs ) {
	$admin_tabs['propanel'] = array(
		'link'      => 'admin.php?page=nss_plugin_license-learndash_propanel-settings',
		'name'      => __( 'ProPanel License', 'ld_propanel' ),
		'id'        => 'admin_page_nss_plugin_license-learndash_propanel-settings',
		'menu_link' => 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
	);
	return $admin_tabs;
}

add_filter( 'learndash_admin_tabs', 'learndash_propanel_admin_tabs', 1, 1 );

function learndash_propanel_learndash_admin_tabs_on_page( $admin_tabs_on_page, $admin_tabs, $current_page_id ) {

	if ( empty( $admin_tabs_on_page['admin_page_nss_plugin_license-learndash_propanel-settings'] ) || ! count( $admin_tabs_on_page['admin_page_nss_plugin_license-learndash_propanel-settings'] ) ) {
		$admin_tabs_on_page['admin_page_nss_plugin_license-learndash_propanel-settings'] = array();
	}

	$admin_tabs_on_page['admin_page_nss_plugin_license-learndash_propanel-settings'] = array_merge( $admin_tabs_on_page['sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses'], (array) $admin_tabs_on_page['admin_page_nss_plugin_license-learndash_propanel-settings'] );

	foreach ( $admin_tabs as $key => $value ) {
		if ( $value['id'] == $current_page_id && $value['menu_link'] == 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses' ) {
				$admin_tabs_on_page[ $current_page_id ][] = 'propanel';
				return $admin_tabs_on_page;
		}
	}
	return $admin_tabs_on_page;
}
add_filter( 'learndash_admin_tabs_on_page', 'learndash_propanel_learndash_admin_tabs_on_page', 3, 3 );
