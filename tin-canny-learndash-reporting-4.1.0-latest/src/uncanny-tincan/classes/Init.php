<?php
/**
 * Initializing
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 *
 * @TODO       Admin Table Header RWD
 * @TODO       Mark Complete Hooks
 */

namespace UCTINCAN;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Init {
	// Constants
	const TINCAN_URL_KEY = 'ucTinCan';
	const TABLE_VERSION_KEY = 'UncannyOwl TinCanny DB Version';

	// Instances
	public static $TinCan;

	// Endpoint URL
	public static $endpint_url;

	// Upgraded Commited
	private static $done_upgraded = false;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'reporting_api' ) );

		$this->load_tincan_api();
		$this->set_objects();
		$this->create_hooks();

		$this->check_upgrade();

		$this->purge_user_records();
	}

	/**
	 *
	 */
	public function purge_user_records() {
		add_action( 'show_user_profile', array( $this, 'show_user_profile' ), 10 );
		add_action( 'edit_user_profile', array( $this, 'show_user_profile' ), 10 );
		add_action( 'personal_options_update', array( $this, 'save_user_profile' ), 10 );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile' ), 10 );
	}

	/**
	 *
	 */
	public function show_user_profile( $profileuser ) {

		if ( current_user_can( 'manage_options' ) ) {
			?>
			<h2><?php esc_html_e( 'Permanently Delete Tin Can Data', 'uncanny-learndash-reporting' ); ?></h2>
			<p>
				<input type="checkbox" id="purge_tincanny_records" name="purge_tincanny_records"/>
				<label for="purge_tincanny_records">
					<?php printf( esc_html_x( 'Check and click %1$s to delete all %2$s for this user.', '%1$s is "Update", and %2$s is "tin can data"', 'uncanny-learndash-reporting' ), sprintf( '<em>%s</em>', esc_html__( 'Update', 'uncanny-learndash-reporting' ) ), sprintf( '<strong>%s</strong>', esc_html__( 'tin can data', 'uncanny-learndash-reporting' ) ) ); ?>

					<strong><?php esc_html_e( 'This cannot be undone.', 'uncanny-learndash-reporting' ); ?></strong>
				</label>
			</p>
			<p>
				<input type="checkbox" id="purge_resume_records" name="purge_resume_records"/>
				<label for="purge_resume_records">
					<?php printf( esc_html_x( 'Check and click %1$s to delete all %2$s for this user.', '%1$s is "Update", and %2$s is "bookmark data"', 'uncanny-learndash-reporting' ), sprintf( '<em>%s</em>', esc_html__( 'Update', 'uncanny-learndash-reporting' ) ), sprintf( '<strong>%s</strong>', esc_html__( 'bookmark data', 'uncanny-learndash-reporting' ) ) ); ?>

					<strong><?php esc_html_e( 'This cannot be undone.', 'uncanny-learndash-reporting' ); ?></strong>
				</label>
			</p>
			<?php
			// Action for additional user information.
			do_action( 'tincanny_additional_user_profile', $profileuser );
		}

	}

	/**
	 *
	 */
	public function save_user_profile( $user_id ) {
		if ( current_user_can( 'manage_options' ) ) {

			$type = [];

			if ( isset( $_POST['purge_tincanny_records'] ) && 'on' === $_POST['purge_tincanny_records'] ) {
				$type[] = 'reporting';
			}

			if ( isset( $_POST['purge_resume_records'] ) && 'on' === $_POST['purge_resume_records'] ) {
				$type[] = 'resume';
			}

			if ( ! empty( $type ) ) {

				if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
					$database = new \UCTINCAN\Database\Admin();
					$database->reset_user( $user_id, $type );

					return true;
				}
			}
		}
	}

	/**
	 * Upgrade
	 *
	 * @access private
	 * @return void
	 * @since  1.3.9
	 */
	private function check_upgrade() {
		if ( self::$done_upgraded ) {
			return;
		}

		// If Option doesn't Exists
		if ( get_option( self::TABLE_VERSION_KEY ) != UNCANNY_REPORTING_VERSION ) {
			$database = new Database();
			$database->upgrade();
		}

		self::$done_upgraded = true;
	}

	/**
	 * Load TinCan API
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function load_tincan_api() {
		require_once( UCTINCAN_PLUGIN_DIR . 'vendors/tincan_api/autoload.php' );
	}

	/**
	 * Set Objects
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function set_objects() {
		new Server();
		new Services();

		// For Edge
		if ( ! headers_sent() ) {
			$header = apply_filters( 'tincanny_content_security_policy', "Content-Security-Policy: script-src * 'self' 'unsafe-inline' 'unsafe-eval' wistia.com youtube.com blob:" );
			@header( $header );
		}

		if ( is_admin() ) {
		    // moved to init action for dynamic post types
			//new Admin\Metabox();
			new Admin\WP_UserProfile();
		}
	}

	/**
	 * Create Hooks
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function create_hooks() {
		add_action( "init", array( $this, "set_objects_on_init" ), 100 );
		add_action( "init", array( $this, "activate_h5p_xapi" ), 110 );

		// Admin Ajax
		add_action( 'wp_ajax_GET_Modules', array( $this, 'print_modules_form_from_URL_parameter' ) );
		add_action( 'wp_ajax_GET_Questions', array( $this, 'print_questions_list' ) );
		add_action( 'wp_ajax_GET_Courses', array( $this, 'group_courses' ) );

		// Filter removed because of loading sequence conflict...
        add_action( 'wp_ajax_process-xapi-statement', array( $this, 'process_xapi_statement' ) );
        add_action( 'wp_ajax_nopriv_process-xapi-statement', array( $this, 'process_xapi_statement' ));

	}

	/**
	 * Set Objects on init Hooking Point
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function set_objects_on_init() {
		$permalink = get_option( 'permalink_structure' );
		$pathinfo  = '';

		if ( strstr( $permalink, 'index.php' ) ) {
			$pathinfo = 'index.php/';
		}
		if ( is_admin() ) {
			new Admin\Metabox();
		}
		self::$endpint_url = get_bloginfo( 'wpurl' ) . '/' . $pathinfo . self::TINCAN_URL_KEY;
		self::$TinCan      = new \TinCan\RemoteLRS( self::$endpint_url, '1.0.1', 0, 0 );
	}

	public function reporting_api() {
		$controller = new RestEndpoint();
		$controller->register_routes();
	}

	/**
	 * Set H5P xAPI if doesn't exist
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function activate_h5p_xapi() {
		if ( has_action( 'admin_menu', 'h5pxapi_admin_menu' ) ) {
			return;
		}

		include_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/h5p-xapi/wp-h5p-xapi.php' );
		remove_action( 'admin_menu', 'h5pxapi_admin_menu' );

		$endpoint_url = get_option( "h5pxapi_endpoint_url" );
		$username     = get_option( "h5pxapi_username" );
		$password     = get_option( "h5pxapi_password" );

		if ( $endpoint_url != self::$endpint_url ) {
			update_option( 'h5pxapi_endpoint_url', self::$endpint_url );
		}

		if ( empty( $username ) ) {
			update_option( 'h5pxapi_username', 1 );
		}

		if ( empty( $password ) ) {
			update_option( 'h5pxapi_password', 1 );
		}
	}

	/**
	 * Ajax Callback For Admin Module <option>s
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function print_modules_form_from_URL_parameter() {
		$database = new Database\Admin();
		$database->print_modules_form_from_URL_parameter();

		wp_die();
	}

	/**
	 * Ajax Callback For Admin Questions
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function print_questions_list() {
		$database          = new Database\Admin();
		$q                 = ! empty( $_POST['q'] ) ? $_POST['q'] : FALSE;
		$ld_actions        = $database->get_questions( $q );
		$json              = [ "results" ];
		$json["results"][] = [ 'id' => '', 'text' => __( 'All Questions', 'uncanny-learndash-reporting' ) ];
		if ( ! empty( $ld_actions ) ) {
			foreach ( $ld_actions as $action ) {
				$json["results"][] = [ 'id' => $action['activity_name'], 'text' => $action['activity_name'] ];
			}
		}
		echo json_encode( $json );

		wp_die();
	}

	/**
	 * Ajax Callback For Admin Questions
	 *
	 * @access public
	 * @return void
	 * @since  3.2.3
	 */
	public function process_xapi_statement() {
		$h5pxapi_response_message = null;
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

		$tin_can_h5p = new \UCTINCAN\TinCanRequest\H5P( $statementObject );
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
	}

	/**
	 * Ajax Callback For Admin Module <option>s
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function group_courses() {
		if ( isset( $_POST['tc_filter_group'] ) && ! empty( $_POST['tc_filter_group'] ) ) {
			$group_leader_id = get_current_user_id();
			$user_group_ids  = learndash_get_administrators_group_ids( $group_leader_id, FALSE );
			// check is user group
			if ( in_array( $_POST['tc_filter_group'], $user_group_ids ) ) {
				$group_id = absint( $_POST['tc_filter_group'] );
				$courses  = learndash_group_enrolled_courses( $group_id );
				$args     = [
					'numberposts' => 9999,
					'include'     => array_map( 'intval', $courses ),
					'post_type'   => 'sfwd-courses',
					'orderby'     => 'title',
					'order'       => 'ASC',
				];

				$courses = get_posts( $args );
				foreach ( $courses as $course ) {
					printf( '<option value="%s">%s</option>', $course->ID, $course->post_title );
				}
			}
		}
		wp_die();
	}
}
