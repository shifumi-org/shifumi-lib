<?php

namespace UCTINCAN;

use WP_REST_Server, WP_REST_Controller, WP_Error;

/**
 * RestEndpoint
 */
class RestEndpoint extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'uncanny_reporting/v1';
		$this->rest_base = 'auth';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'check_auth' ),
			'permission_callback' => function () {
				return true;
			}
		) );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public static function check_auth( $request ) {
		if ( ! $request['postId'] ) {
			return true;
		}

		$postmeta          = get_post_meta( $request['postId'], '_WE-meta_', true );
		$global_protection = get_option( 'tincanny_nonce_protection', 'yes' );
		$protection        = 'Yes';

		if ( ! empty( $postmeta['protect-scorm-tin-can-modules'] ) ) {
			switch ( $postmeta['protect-scorm-tin-can-modules'] ) {
				case 'Yes' :
					$protection = 'Yes';
					break;
				case 'No' :
					$protection = 'No';
					break;
				default :
					if ( $global_protection === 'yes' ) {
						$protection = 'Yes';
					}

					if ( $global_protection === 'no' ) {
						$protection = 'No';
					}
					break;
			}
		} else {

			if ( $global_protection === 'no' ) {
				$protection = 'No';
			}
		}


		if ( $protection == 'No' ) {
			return true;
		}

		$request['email'] = str_replace( ' ', '+', $request['email'] );
		$user             = get_user_by( 'email', $request['email'] );

		if ( $user ) {
			wp_set_current_user( $user->ID );
		}

		if ( ! wp_verify_nonce( $request['nonce'], 'tincanny-module' ) ) {
			return false;
		}

		return true;
	}
}
