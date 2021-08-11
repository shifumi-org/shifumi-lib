<?php
/**
 * LearnDash Gutenberg Posts Controller.
 *
 * @package LearnDash
 * @since 2.5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LD_REST_Posts_Gutenberg_Controller' ) ) {

	/**
	 * LearnDash Gutenberg Posts Controller.
	 *
	 * @since 2.5.8
	 */
	class LD_REST_Posts_Gutenberg_Controller extends WP_REST_Posts_Controller {

		/**
		 * Constructor.
		 *
		 * @since 2.5.8
		 * @param string $post_type Post type.
		 */
		public function __construct( $post_type = '' ) {
			parent::__construct( $post_type );
		}

		/**
		 * Registers the routes for the objects of the controller.
		 *
		 * @since 2.5.8
		 */
		public function register_routes() {
			$namespace     = 'wp/v2';
			$schema        = $this->get_item_schema();
			$get_item_args = array(
				'context' => $this->get_context_param( array( 'default' => 'view' ) ),
			);
			if ( isset( $schema['properties']['password'] ) ) {
				$get_item_args['password'] = array(
					'description' => __( 'The password for the post if it is password protected.', 'learndash' ),
					'type'        => 'string',
				);
			}

			register_rest_route(
				$namespace,
				'/' . $this->post_type . '/(?P<id>[\d]+)',
				array(
					'args'   => array(
						'id' => array(
							'description' => __( 'Unique identifier for the object.', 'learndash' ),
							'type'        => 'integer',
						),
					),
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => $get_item_args,
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_item' ),
						'permission_callback' => array( $this, 'delete_item_permissions_check' ),
						'args'                => array(
							'force' => array(
								'type'        => 'boolean',
								'default'     => false,
								'description' => __( 'Whether to bypass trash and force deletion.', 'learndash' ),
							),
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}

		/**
		 * Function documented in endpoints/class-wp-rest-posts-controller.php
		 *
		 * @since 3.2.0
		 */
		public function get_item_permissions_check( $request ) {
			/**
			 * Logic added to prevent access to the automatic routes created as part of
			 * WP core for Gutenberg enabled custom post types. This new logic will prevent
			 * visibility read access if used is not authenticated or does not have update
			 * capabilities.
			 *
			 * @since 3.2.0
			 */
			if ( ( defined( 'LEARNDASH_BLOCK_WORDPRESS_CPT_ROUTES' ) ) && ( true === LEARNDASH_BLOCK_WORDPRESS_CPT_ROUTES ) ) {
				$post = $this->get_post( $request['id'] );
				if ( is_wp_error( $post ) ) {
					return $post;
				}

				if ( $post && ! $this->check_update_permission( $post ) ) {
					return new WP_Error(
						'rest_forbidden_context',
						__( 'Sorry, you are not allowed to edit this post.', 'learndash' ),
						array( 'status' => rest_authorization_required_code() )
					);
				}

				if ( $post && ! empty( $request['password'] ) ) {
					// Check post password, and return error if invalid.
					if ( ! hash_equals( $post->post_password, $request['password'] ) ) {
						return new WP_Error(
							'rest_post_incorrect_password',
							__( 'Incorrect post password.', 'learndash' ),
							array( 'status' => 403 )
						);
					}
				}

				// Allow access to all password protected posts if the context is edit.
				if ( 'edit' === $request['context'] ) {
					add_filter( 'post_password_required', '__return_false' );
				}

				if ( $post ) {
					return $this->check_read_permission( $post );
				}

				return true;
			} else {
				return parent::get_item_permissions_check( $request );
			}
		}

		// End of functions
	}
}
