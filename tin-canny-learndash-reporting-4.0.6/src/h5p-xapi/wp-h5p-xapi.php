<?php

require_once __DIR__ . "/src/utils/Template.php";
require_once __DIR__ . "/plugin.php";

use h5pxapi\Template;

/*
Plugin Name: H5P xAPI
Plugin URI: http://github.com/tunapanda/wp-h5p-xapi
GitHub Plugin URI: https://github.com/tunapanda/wp-h5p-xapi
Description: Send H5P achievements to an xAPI repo.
Version: 0.1.5
*/

/**
 * Enqueue scripts and stylesheets.
 */

if ( ! defined( "UCWPH5PxAPI_PLUGIN_NAME" ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), "/" );
	if ( ! is_dir( WP_PLUGIN_DIR . "/" . $basename ) ) {
		$basename = explode( "/", $basename );
		$basename = array_pop( $basename );
	}

	define( "UCWPH5PxAPI_PLUGIN_NAME", $basename );
}
if ( ! defined( "UCWPH5PxAPI_PLUGIN_URL" ) ) {
	define( "UCWPH5PxAPI_PLUGIN_URL", plugins_url() . "/" . UCWPH5PxAPI_PLUGIN_NAME );
}

function h5pxapi_enqueue_scripts() {
	if ( ! isset( $_REQUEST['doing_wp_cron'] ) ){
		// Get the data the script needs
		$settings = h5pxapi_get_auth_settings();
		// Get H5P_XAPI_STATEMENT_URL
		$h5p_xapi_statement_url = null;
		if ( $settings && $settings[ 'endpoint_url' ] ){
			// Check if we have to enable the admin AJAX
			$uo_enable_H5P_admin_ajax = apply_filters( 'uo_enable_H5P_admin_ajax', false );

			if ( $uo_enable_H5P_admin_ajax === true ){
				$h5p_xapi_statement_url = admin_url( 'admin-ajax.php?action=process-xapi-statement' );
			}
			else {
				$h5p_xapi_statement_url = UCWPH5PxAPI_PLUGIN_URL . '/process-xapi-statement.php?security=' . wp_create_nonce( "process-xapi-statement" );
			}
		}
		// Get HP5_XAPI_CONTEXTACTIVITY
		$h5p_xapi_contextactivity = null;
		if ( get_permalink() ){
			$h5p_xapi_contextactivity = [
				'id'           => get_permalink(),
				'definition'   => [
					'name'     => [
						'en'   => wp_title( '|', false ),
					],
					'moreInfo' => get_permalink()
				]
			];
		} 

		// Register and enqueue style
		wp_enqueue_style( 'wp-h5p-xapi', UCWPH5PxAPI_PLUGIN_URL . '/wp-h5p-xapi.css', array(), UNCANNY_REPORTING_VERSION );

		// Register script
		wp_register_script( 'wp-h5p-xapi', UCWPH5PxAPI_PLUGIN_URL . '/wp-h5p-xapi.js', array( "jquery" ), UNCANNY_REPORTING_VERSION );

		// Add inline script
		$h5p_inline_script  = "WP_H5P_XAPI_STATEMENT_URL = '" . $h5p_xapi_statement_url . "';";
		$h5p_inline_script .= "WP_H5P_XAPI_CONTEXTACTIVITY = JSON.parse( '" . json_encode( $h5p_xapi_contextactivity ) . "' );";
		wp_add_inline_script( 'wp-h5p-xapi', $h5p_inline_script, 'before' );

		// Enqueue script
		wp_enqueue_script( "wp-h5p-xapi" );
	}
}

/**
 * Create the admin menu.
 */
function h5pxapi_admin_menu() {
	$settings = apply_filters( "h5p-xapi-auth-settings", null );

	if ( ! $settings ) {
		add_options_page(
			'H5P xAPI',
			'H5P xAPI',
			'manage_options',
			'h5pxapi_settings',
			'h5pxapi_create_settings_page'
		);
	}
}

/**
 * Admin init.
 */
function h5pxapi_admin_init() {
	register_setting( "h5pxapi", "h5pxapi_endpoint_url" );
	register_setting( "h5pxapi", "h5pxapi_username" );
	register_setting( "h5pxapi", "h5pxapi_password" );
}

/**
 * Create settings page.
 */
function h5pxapi_create_settings_page() {
	wp_register_style( "wp-h5p-xapi", UCWPH5PxAPI_PLUGIN_URL . "/wp-h5p-xapi.css", array(), UNCANNY_REPORTING_VERSION );
	wp_enqueue_style( "wp-h5p-xapi" );

	$template = new Template( __DIR__ . "/src/template/settings.tpl.php" );
	$template->show();
}

add_action( 'wp_enqueue_scripts', 'h5pxapi_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'h5pxapi_enqueue_scripts' );
add_action( 'admin_menu', 'h5pxapi_admin_menu' );
add_action( 'admin_init', 'h5pxapi_admin_init' );

function h5pxapi_response_message( $message ) {
	global $h5pxapi_response_message;

	$h5pxapi_response_message = $message;
}