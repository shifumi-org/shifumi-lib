<?php

namespace uncanny_learndash_reporting;

class Boot extends Config {

	/**
	 * class constructor
	 */
	public function __construct() {

		global $uncanny_learndash_reporting;


		if ( ! isset( $uncanny_learndash_reporting ) ) {
			$uncanny_learndash_reporting = new \stdClass();
		}

		// We need to check if spl auto loading is available when activating plugin
		// Plugin will not activate if SPL extension is not enabled by throwing error
		if ( ! extension_loaded( 'SPL' ) ) {
			$spl_error = esc_html__( 'Please contact your hosting company to update to php version 5.3+ and enable spl extensions.', 'uncanny-learndash-reporting' );
			trigger_error( $spl_error, E_USER_ERROR );
		}

		spl_autoload_register( array( __CLASS__, 'auto_loader' ) );

		$uncanny_learndash_reporting->admin_menu          = new ReportingAdminMenu;
		$uncanny_learndash_reporting->reporting_api       = new ReportingApi;
		$uncanny_learndash_reporting->quiz_module_reports = new QuizModuleReports;

		// URL of store powering the plugin
		define( 'UO_REPORTING_STORE_URL', 'https://www.uncannyowl.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

		// Store download name/title
		define( 'UO_REPORTING_ITEM_NAME', 'Tin Canny LearnDash Reporting' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file


		add_action( 'admin_init', array( __CLASS__, 'uo_reporting_register_option' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_help_submenu' ), 31 );
		add_action( 'admin_menu', array( __CLASS__, 'add_checkpage_submenu' ), 33 );
		add_action( 'admin_menu', array( __CLASS__, 'add_uncanny_plugins_page' ), 32 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_external_scripts' ) );
		add_action( 'admin_init', array( __CLASS__, 'uo_admin_help_process' ) );
		/* Licensing */
		// Setup menu and page options in admin
		if ( is_admin() ) {

			// Licensing is not autoloaded, load manually
			include_once( self::get_include( 'licensing.php' ) );

			// Create a new instance of EDD Liccensing
			$licensing = new Licensing();

			// Create sub-page for EDD licensing
			$licensing->page_name   = 'Uncanny Reporting License Activation';
			$licensing->page_slug   = 'uncanny-reporting-license-activation';
			$licensing->parent_slug = 'uncanny-learnDash-reporting';
			$licensing->store_url   = UO_REPORTING_STORE_URL;
			$licensing->item_name   = UO_REPORTING_ITEM_NAME;
			$licensing->author      = 'Uncanny Owl';
			$licensing->add_licensing();

		}

		// Check if the protection is enabled
		if ( get_option( 'tincanny_nonce_protection', 'yes' ) == 'yes' ){
			self::create_protection_htaccess();
		}
	}

	public static function create_protection_htaccess(){
		// Check if the constant with the name of the Tin Canny folder is defined
		if ( ! defined( 'SnC_UPLOAD_DIR_NAME' ) ){
			// If it's not, then define it
			define( 'SnC_UPLOAD_DIR_NAME', 'uncanny-snc' );
		}

		$wp_upload_dir = wp_upload_dir();
		$upload_dir    = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME;

		if ( file_exists( $upload_dir ) ) {
			if ( ! file_exists( $upload_dir . '/.htaccess' ) ) {
				if ( defined( 'UO_ABS_PATH' ) ) {

					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					global $wp_filesystem;
					\WP_Filesystem();

					$slashed_home = trailingslashit( get_option( 'home' ) );
					$base         = parse_url( $slashed_home, PHP_URL_PATH );

					$htaccess_file = <<<EOF
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$base}
RewriteRule ^index\.php$ - [L]
RewriteRule ^(?:|(?:\/|\\\\))([0-9]{1,})((?:.*(?:\/|\\\\))|.*\.(?:(?:html|htm)(?:|.*)))$ {$base}index.php?tincanny_content_id=$1&tincanny_file_path=$2 [QSA,L,NE]
</IfModule>
EOF;

					$wp_filesystem->put_contents( $upload_dir . '/.htaccess', $htaccess_file );
				}
			}
		}
	}

	public static function delete_protection_htaccess(){
		// Check if the constant with the name of the Tin Canny folder is defined
		if ( ! defined( 'SnC_UPLOAD_DIR_NAME' ) ){
			// If it's not, then define it
			define( 'SnC_UPLOAD_DIR_NAME', 'uncanny-snc' );
		}

		// Get the upload directory (uncanny-snc folder)
		$wp_upload_dir = wp_upload_dir();
		$upload_dir    = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME;

		// Check if the folder exists
		if ( file_exists( $upload_dir ) ) {
			// Check if the .htaccess was created in the uncanny-snc folder
			if ( file_exists( $upload_dir . '/.htaccess' ) ) {
				// Require file.php. Use require_once to avoid including it again
				// if it's already there
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				// Get global wp_filesystem
				global $wp_filesystem;
				// Create instance of WP_Filesystem
				\WP_Filesystem();

				// Remove the file
				$wp_filesystem->delete( $upload_dir . '/.htaccess' );
			}
		}
	}

	/**
	 * Add "Help" submenu
	 */
	public static function add_help_submenu() {
		add_submenu_page(
			'uncanny-learnDash-reporting',
			__( 'Tin Canny Reporting for LearnDash Support', 'uncanny-learndash-reporting' ),
			__( 'Help', 'uncanny-learndash-reporting' ),
			'manage_options',
			'uncanny-tincanny-kb',
			array( __CLASS__, 'include_help_page' )
		);
	}

	/**
	 * Create "Uncanny Plugins" submenu
	 */
	public static function add_uncanny_plugins_page() {
		add_submenu_page(
			'uncanny-learnDash-reporting',
			__( 'Uncanny LearnDash Plugins', 'uncanny-learndash-reporting' ),
			__( 'LearnDash Plugins', 'uncanny-learndash-reporting' ),
			'manage_options',
			'uncanny-tincanny-plugins',
			array( __CLASS__, 'include_learndash_plugins_page' )
		);
	}
	
	/**
	 * Add "Check Page" submenu
	 */
	public static function add_checkpage_submenu() {
		add_submenu_page(
			'uncanny-learnDash-reporting',
			__( 'Tin Canny Reporting for LearnDash Support', 'uncanny-learndash-reporting' ),
			__( 'Site Check', 'uncanny-learndash-reporting' ),
			'manage_options',
			'uncanny-tincanny-site-check',
			array( __CLASS__, 'include_site_check_page' )
		);
	}

	/**
	 * Include "Help" template
	 */
	public static function include_help_page() {
		include( 'templates/admin-help.php' );
	}
	
	/**
	 * Include "Help" template
	 */
	public static function include_site_check_page() {
		include( 'templates/admin-site-check.php' );
	}

	/**
	 * Include "LearnDash Plugins" template
	 */
	public static function include_learndash_plugins_page() {
		include( 'templates/admin-learndash-plugins.php' );
	}

	/**
	 * Enqueue external scripts from uncannyowl.com
	 */
	public static function enqueue_external_scripts() {
		$pages_to_include = [ 'uncanny-tincanny-plugins', 'uncanny-tincanny-kb', 'uncanny-tincanny-site-check' ];

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages_to_include ) ) {
			wp_enqueue_style( 'uncannyowl-core', 'https://uncannyowl.com/wp-content/mu-plugins/uncanny-plugins-core/dist/bundle.min.css', array(), Config::get_version() );
			wp_enqueue_script( 'uncannyowl-core', 'https://uncannyowl.com/wp-content/mu-plugins/uncanny-plugins-core/dist/bundle.min.js', array( 'jquery' ), Config::get_version() );

			wp_enqueue_style( 'tclr-icons', Config::get_admin_css( 'icons.css' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-select2', Config::get_admin_css( 'select2.min.css' ), array(), UNCANNY_REPORTING_VERSION );
			wp_enqueue_style( 'tclr-backend', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_REPORTING_VERSION );
		}
		
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'uncanny-tincanny-site-check' ) {
			// Get Tin Canny settings
			$tincanny_settings = \TINCANNYSNC\Admin\Options::get_options();
			
			// API data
			$reporting_api_setup = array(
				'root'              => site_url(),
				'nonce'             => \wp_create_nonce( 'tincanny-module' ),
				'isAdmin'           => is_admin(),
				'editUsers'         => current_user_can( 'edit_users' ),
				'optimized_build'   => '1',
				'test_user_email'    => wp_get_current_user()->user_email,
				'page'              => 'reporting',
				'showTinCanTab'     => $tincanny_settings['tinCanActivation'] == 1 ? '1' : '0'
			);
			
			wp_localize_script( 'uncannyowl-core', 'reportingApiSetup', $reporting_api_setup );
			wp_enqueue_script( 'uncannyowl-core' );
		}
	}

	/**
	 * Submit ticket
	 */
	public static function uo_admin_help_process() {
		if ( isset( $_POST['tclr-send-ticket'] ) && check_admin_referer( 'uncanny0w1', 'tclr-send-ticket' ) ) {
			$name     = esc_html( $_POST['fullname'] );
			$email    = esc_html( $_POST['email'] );
			$website  = esc_html( $_POST['website'] );
			$message  = esc_html( $_POST['message'] );
			$siteinfo = stripslashes( $_POST['siteinfo'] );
			if ( isset( $_POST['site-data'] ) && 'yes' === $_POST['site-data'] ) {
				$message = "<h3>Message:</h3><p>{$message}</p><br /><hr /><h3>User Site Information:</h3>{$siteinfo}";
			}

			$to        = 'support.41077.bb1dda3d33afb598@helpscout.net';
			$subject   = esc_html( $_POST['subject'] );
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$headers[] = 'From: ' . $name . ' <' . $email . '>';
			$headers[] = 'Reply-To:' . $name . ' <' . $email . '>';
			wp_mail( $to, $subject, $message, $headers );
			if ( isset( $_POST['page'] ) ) {
				$url = admin_url( 'admin.php' ) . '?page=' . esc_html( $_POST['page'] ) . '&send-ticket=true&sent=true&wpnonce=' . wp_create_nonce();
				wp_safe_redirect( $url );
				exit;
			}
		}
	}


	public static function uo_reporting_register_option() {
		// creates our settings in the options table
		register_setting( 'uo_reporting_license', 'uo_reporting_license_key', array(
			__CLASS__,
			'uo_reporting_sanitize_license'
		) );
	}


	public static function uo_reporting_sanitize_license( $new ) {
		$old = get_option( 'uo_reporting_license_key' );
		if ( $old && $old != $new ) {
			delete_option( 'uo_reporting_license_status' ); // new license has been entered, so must reactivate
		}

		return $new;
	}


	/************************************
	 * this illustrates how to check if
	 * a license key is still valid
	 * the updater does this for you,
	 * so this is only needed if you
	 * want to do something custom
	 *************************************/

	public static function uo_reporting_check_license() {

		global $wp_version;

		$license = trim( get_option( 'uo_reporting_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( UO_REPORTING_ITEM_NAME ),
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( UO_REPORTING_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ) {
			echo 'valid';
			exit;
			// this license is still valid
		} else {
			echo 'invalid';
			exit;
			// this license is no longer valid
		}
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $class
	 */
	public static function auto_loader( $class ) {

		// Remove Class's namespace eg: my_namespace/MyClassName to MyClassName
		$class = str_replace( self::get_namespace(), '', $class );
		$class = str_replace( '\\', '', $class );

		// First Character of class name to lowercase eg: MyClassName to myClassName
		$class_to_filename = lcfirst( $class );

		// Split class name on upper case letter eg: myClassName to array( 'my', 'Class', 'Name')
		$split_class_to_filename = preg_split( '#([A-Z][^A-Z]*)#', $class_to_filename, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		if ( 1 <= count( $split_class_to_filename ) ) {
			// Split class name to hyphenated name eg: array( 'my', 'Class', 'Name') to my-Class-Name
			$class_to_filename = implode( '-', $split_class_to_filename );
		}

		// Create file name that will be loaded from the classes directory eg: my-Class-Name to my-class-name.php
		$file_name = 'uncanny-reporting/' . strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include_once $file_name;
		}

		$file_name = strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include_once $file_name;
		}

	}
}





