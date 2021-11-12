<?php
/**
 * TinCan Module
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

# Constants
if ( !defined( "UCTINCAN_PLUGIN_NAME" ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), "/" );
	if ( !is_dir( WP_PLUGIN_DIR . "/" . $basename ) ) {
		$basename = explode( "/", $basename );
		$basename = array_pop( $basename );
	}

	define( "UCTINCAN_PLUGIN_NAME", $basename );
}

if ( !defined( "UCTINCAN_PLUGIN_FILE_NAME" ) )
	define( "UCTINCAN_PLUGIN_FILE_NAME", basename(__FILE__) );

if ( !defined( "UCTINCAN_PLUGIN_DIR" ) )
	define( "UCTINCAN_PLUGIN_DIR", WP_PLUGIN_DIR . "/" . UCTINCAN_PLUGIN_NAME . "/" );

if ( !defined( "UCTINCAN_PLUGIN_URL" ) )
	define( "UCTINCAN_PLUGIN_URL", plugins_url() . "/" . UCTINCAN_PLUGIN_NAME . "/" );

# Initialize
include_once( UCTINCAN_PLUGIN_DIR . "autoload.php");
new UCTINCAN\Init();
