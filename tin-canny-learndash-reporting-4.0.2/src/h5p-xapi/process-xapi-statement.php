<?php

namespace UCTINCAN\TinCanRequest;

$h5pxapi_response_message = null;
if ( ! defined( 'UO_ABS_PATH' ) ) {
	define( 'UO_ABS_PATH', dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/' );
}

if ( ! is_writeable( UO_ABS_PATH ) ) {
	if ( ! defined( 'WP_CONTENT_DIR' ) ) {
		define( 'WP_CONTENT_DIR', dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/' );
	}
}
/**
 * This file receives the xAPI statement as a http post.
 */
require_once __DIR__ . "/src/utils/Template.php";
require_once __DIR__ . "/src/utils/WpUtil.php";
require_once __DIR__ . "/plugin.php";

use h5pxapi\WpUtil;

require_once WpUtil::getWpLoadPath();

if( ! check_ajax_referer( 'process-xapi-statement', 'security', false ) ) {
	echo json_encode( [
		"ok"      => 1,
		"message" => "false",
		"code"    => 403,
	] );
	
	exit();
}

if ( ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) ) {
	if ( strtolower( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) ) != strtolower( $_SERVER['HTTP_HOST'] ) ) {
		echo json_encode( [
			"ok"      => 1,
			"message" => "false",
			"code"    => 403,
		] );
		
		exit();
	}
}

if( ! is_user_logged_in() ) {
	echo json_encode( [
		"ok"      => 1,
		"message" => "false",
		"code"    => 403,
	] );
	
	exit();
}

$statementObject = json_decode( stripslashes( $_REQUEST["statement"] ), true );
if ( isset( $statementObject["context"]["extensions"] )
     && ! $statementObject["context"]["extensions"]
) {
	unset( $statementObject["context"]["extensions"] );
}

if ( has_filter( "h5p-xapi-pre-save" ) ) {
	$statementObject = apply_filters( "h5p-xapi-pre-save", $statementObject );

	if ( ! $statementObject ) {
		echo json_encode( [
			"ok"      => 1,
			"message" => $h5pxapi_response_message,
		] );
		exit;
	}
}

$tin_can_h5p = new H5P( $statementObject );
$res         = $tin_can_h5p->get_completion();
if ( $res ) {
	$response = [
		"ok"      => 1,
		"message" => "true",
		"code"    => 200,
	];
} else {
	$response = [
		"ok"      => 1,
		"message" => "false",
		"code"    => 200,
	];
}

echo json_encode( $response );
exit();