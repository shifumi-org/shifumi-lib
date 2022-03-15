<?php
/**
 * Initializing
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 * @todo       activation
 */

namespace TINCANNYSNC;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Init {
	const TABLE_VERSION_KEY = 'UncannyOwl TinCanny SnC DB Version';

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

		if ( is_admin() ) {

			new Admin\Options();
			new Admin\MediaPopup();
			new Admin\ManageContent();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_script' ), 100 );
			add_action( 'admin_init', array( $this, 'load_plugin_admin' ) );
			add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
		} else {
			add_action( 'wp', array( $this, 'load_plugin_front' ) );
		}
		add_action( 'wp_ajax_vc_snc_data', array( $this, 'get_contents_list' ) );
		add_action( 'wp_ajax_nopriv_vc_snc_data', array( $this, 'get_contents_list' ) );
		add_action( 'media_buttons', array( 'TINCANNYSNC\Admin\MediaButton', 'media_button' ), 100 );
		$this->check_upgrade();
	}


	/**
	 * Custom Upload mimes
	 *
	 * @since  2.9.9
	 */
	function custom_upload_mimes( $existing_mimes = array() ) {
		// add your extension to the mimes array as below
		$existing_mimes['zip'] = 'application/zip';

		//$existing_mimes['gz'] = 'application/x-gzip';
		return $existing_mimes;
	}

	/**
	 * Content list API
	 *
	 * @since  2.9.9
	 */
	function get_contents_list() {
		check_ajax_referer( 'vc-snc-data-nonce', 'security' );
		$posts = Database::get_modules();
		echo json_encode( $posts );
		die;
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
			$database    = new Database();
			$db_upgraded = $database->upgrade();

			$file_system   = new FileSystem();
			$file_upgraded = $file_system->upgrade( get_option( self::TABLE_VERSION_KEY ) );

			if ( ! $db_upgraded || ! $file_upgraded ) {
				if ( ! is_admin() ) {
					return;
				}
				add_action( 'wp_enqueue_scripts', array( $this, 'tin_canny_upgrade_error' ) );

				return;
			}

			update_option( self::TABLE_VERSION_KEY, UNCANNY_REPORTING_VERSION );
		}

		self::$done_upgraded = true;
	}

	/**
	 * Print error message if upgrade not complete
	 *
	 * @trigger wp
	 * @access  public
	 * @return  void
	 * @since   2.1.4
	 */
	public function tin_canny_upgrade_error() {
		$message = 'An update is required for your Tin Canny modules. Refresh the page to complete the update.';
		wp_add_inline_script( 'tin-canny-upgrade-error', 'console.error(' . $message . ');' );
	}

	/**
	 * Load Front Site
	 *
	 * @trigger wp
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_plugin_front() {

		if ( is_admin() || is_archive() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ), 100 );

		new Shortcode();
		new VisualComposer();
	}

	/**
	 * Front Script
	 *
	 * @trigger wp_enqueue_scripts
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function enqueue_script() {
		wp_enqueue_style( 'snc-style', SnC_ASSET_URL . 'css/style.min.css', array(), UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'snc-script', SnC_ASSET_URL . 'scripts/script.js', array( 'jquery' ), UNCANNY_REPORTING_VERSION, true );
	}

	/**
	 * Load Admin Site
	 *
	 * @trigger admin_init
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_plugin_admin() {
		global $pagenow;
		if ( $pagenow !== 'post-new.php' && $pagenow !== 'post.php' ) {
			return;
		}

		new Shortcode();
		new VisualComposer();
		//new Admin\MediaButton();
	}

	/**
	 * Front Script
	 *
	 * @trigger wp_enqueue_scripts
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function enqueue_admin_script() {
		wp_enqueue_style( 'storyline-and-captivate-admin', SnC_ASSET_URL . 'css/min/admin.min.css', array(), UNCANNY_REPORTING_VERSION );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-form', SnC_ASSET_URL . 'scripts/jquery.form.js', array( 'jquery' ), UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'tclr-content-library-fusejs', SnC_ASSET_URL . 'scripts/fuse.js', array(), UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'storyline-and-captivate-admin', SnC_ASSET_URL . 'scripts/min/admin.min.js', array(
			'jquery',
			'jquery-form',
			'tclr-content-library-fusejs'
		), UNCANNY_REPORTING_VERSION );
	}
}
