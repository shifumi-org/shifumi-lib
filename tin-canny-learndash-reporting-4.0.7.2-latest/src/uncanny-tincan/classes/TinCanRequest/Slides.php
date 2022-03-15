<?php
/**
 * Processing Request
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.3.0
 */

namespace UCTINCAN\TinCanRequest;

use UCTINCAN\Services;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

abstract class Slides extends \UCTINCAN\TinCanRequest {
	/**
	 * SnC Table Name
	 *
	 * @access public
	 * @return string
	 * @since  1.0.0
	 */
	const TABLE_SNC = 'snc_file_info';

	public $module_url = '';

	/**
	 * Set Modules
	 *
	 * @access protected
	 * @return void
	 * @since  1.0.0
	 */
	protected function set_slides( $decoded_2 = false ) {
		// Group and Parent
		parse_str( $_SERVER[ 'HTTP_REFERER' ], $referer );
		parse_str( $_SERVER[ 'QUERY_STRING' ], $request_uri );

		if ( strstr( $_SERVER[ 'HTTP_REFERER'], '&client=' ) !== false ) {
			$auth = $referer[ 'auth' ];
			$activity_id = $referer[ 'activity_id' ];

		} elseif ( strstr( $_SERVER[ 'QUERY_STRING'], '&client=' ) !== false ) {
			$auth = $request_uri[ 'auth' ];
			$activity_id = $request_uri[ 'activity_id' ];

		} else if ( $decoded_2 ) {
			$auth = $decoded_2[ 'Authorization' ];
			$content = json_decode( $decoded_2[ 'content' ], true );
			$activity_id = $content[ 'object' ][ 'id' ];
		}

		$this->module_url = $activity_id . '&auth=' . $auth;

		$grouping = new \TinCan\Activity( array( 'id' => get_bloginfo( 'url' ) . '/?p=' . substr( $auth, 11 ) ) );
		$parent = new \TinCan\Activity( array( 'id' => $activity_id ) );

		$this->TC_Context->getContextActivities()->setGrouping( $grouping );
		$this->TC_Context->getContextActivities()->setParent( $parent );

		// ID
		$matches = $this->get_slide_id_from_url( $activity_id );
		$this->content_id = $matches[1];

	}

	/**
	 * Get Module Information
	 *
	 * @access protected
	 * @return array
	 * @since  1.0.0
	 */
	protected function get_module() {
		global $wpdb;
		$module = $target = $module_name = $target_name = '';

		$query = sprintf( "
			SELECT file_name, url FROM %s%s
				WHERE ID = %s
				LIMIT 1;
			",
			$wpdb->prefix,
			self::TABLE_SNC,
			$this->content_id
		);

		$result = $wpdb->get_row( $query, ARRAY_A );

		$module = $target = $result[ 'url' ];
		$module_name = $result[ 'file_name' ];

		return compact( 'module', 'module_name', 'target', 'target_name' );
	}

	/**
	 * Get Target Name from TinCan Activity Object
	 *
	 * @access protected
	 * @param  string $target
	 * @return string
	 * @since  1.0.0
	 */
	protected function get_target_from_activity_definition( $target_name ) {
		if ( !empty( $this->TC_Actitity->getDefinition() ) ) {
			if ( !empty( $this->TC_Actitity->getDefinition()->getName()->_map ) ) {
				$target_name = urldecode( array_pop( $this->TC_Actitity->getDefinition()->getName()->_map ) );

			} else if ( !empty( $this->TC_Actitity->getDefinition()->getDescription()->_map ) ) {
				$target_name = urldecode( array_pop( $this->TC_Actitity->getDefinition()->getDescription()->_map ) );

			}
		}

		return $target_name;
	}

	public function get_completion() {
		$service = new Services();
		$completion = $service->check_slide_completion( $this->module_url );
		return ! empty( $completion ) ? $completion : false ;
	}
}
