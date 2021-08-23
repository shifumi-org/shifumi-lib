<?php
/**
 * Admin Manage Content Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      3.0.0
 */

namespace TINCANNYSNC\Admin;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class ManageContent {

	private static $tincan_database;
	private static $tincan_per_pages;

	/**
	 * initialize
	 *
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		add_action( 'wp_ajax_SnC_Content_Delete', array( $this, 'ajax_delete' ) );
		add_action( 'wp_ajax_SnC_Content_Bookmark_Delete', array( $this, 'ajax_delete_bookmarks_only' ) );
		add_action( 'wp_ajax_SnC_Content_Delete_All', array( $this, 'ajax_delete_all_data' ) );
	}

	/**
	 * Register Admin Menu
	 *
	 * @trigger admin_menu Action
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	public function register_admin_menu() {

		add_submenu_page( 'uncanny-learnDash-reporting', 'Manage Content', 'Manage Content', apply_filters( 'tc_manage_content_cap', 'manage_options' ), 'manage-content', array(
			$this,
			'view_content_page'
		) );
	}

	/**
	 * admin_menu Page
	 *
	 * @trigger view_content_page
	 * @access  public
	 * @return  void
	 * @since   3.0.0
	 */
	public function view_content_page() {

		include_once( dirname( UO_REPORTING_FILE ) . '/src/includes/TinCan_Content_List_Table.php' );
		$TinCan_Content_List_Table = new \TinCan_Content_List_Table();

		$columns = [
			'ID'      => __( 'ID', 'uncanny-learndash-reporting' ),
			'content' => __( 'Content', 'uncanny-learndash-reporting' ),
			'type'    => __( 'Type', 'uncanny-learndash-reporting' ),
			'actions' => __( 'Actions', 'uncanny-learndash-reporting' ),
		];

		$TinCan_Content_List_Table->column = $columns;
		unset( $columns['actions'] );
		$TinCan_Content_List_Table->sortable_columns = $columns;
		$TinCan_Content_List_Table->prepare_items();
		$TinCan_Content_List_Table->views();

		include_once( SnC_PLUGIN_DIR . 'views/manage_content.php' );
	}

	/**
	 *
	 */
	public function ajax_delete() {
		if ( $_POST['mode'] !== 'vc' ) {
			check_ajax_referer( 'snc-media_enbed_form', 'security' );
		}

		$Module = \TINCANNYSNC\Module::get_module( $_POST['item_id'] );
		$Module->delete();

		die;
	}

	/**
	 *
	 */
	public function ajax_delete_bookmarks_only() {
		if ( $_POST['mode'] !== 'vc' ) {
			check_ajax_referer( 'snc-media_enbed_form', 'security' );
		}

		$Module = \TINCANNYSNC\Module::get_module( $_POST['item_id'] );
		$url    = str_replace( site_url(), '', $Module->get_url() );

		\UCTINCAN\Database::delete_bookmarks( $_POST['item_id'], $url );

		die;
	}

	/**
	 *
	 */
	public function ajax_delete_all_data() {
		if ( $_POST['mode'] !== 'vc' ) {
			check_ajax_referer( 'snc-media_enbed_form', 'security' );
		}
		$Module = \TINCANNYSNC\Module::get_module( $_POST['item_id'] );
		$url    = str_replace( site_url(), '', $Module->get_url() );

		\UCTINCAN\Database::delete_all_data( $_POST['item_id'], $url );

		die;
	}
}
