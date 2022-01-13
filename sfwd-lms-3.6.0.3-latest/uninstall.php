<?php
/**
 * Functions for uninstall LearnDash
 *
 * @since 2.5.0
 *
 * @package LearnDash
 */

/*
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Remove our Multisite support file(s) to the /wp-content/mu-plugins directory.
 */
$wpmu_plugin_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
if ( is_writable( $wpmu_plugin_dir ) ) {
	$dest_file = trailingslashit( $wpmu_plugin_dir ) . 'learndash-multisite.php';
	unlink( $dest_file );
}

/**
 * Fires on plugin uninstall.
 */
do_action( 'learndash_uninstall' );
