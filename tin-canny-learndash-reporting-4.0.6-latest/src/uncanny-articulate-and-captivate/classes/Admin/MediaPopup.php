<?php
/**
 * Media Popup
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 * @todo       Button
 */

namespace TINCANNYSNC\Admin;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class MediaPopup {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		// Media Upload Window
		add_action( 'media_upload_snc', array( $this, 'media_upload' ), 100 );

		// File Upload Ajax
		add_action( 'wp_ajax_SnC_Media_Upload', array( $this, 'ajax_upload' ) );
		// Generate Shortcode Ajax
		add_action( 'wp_ajax_SnC_Media_Embed', array( $this, 'ajax_embed' ) );
		// Delete Ajax
		add_action( 'wp_ajax_SnC_Media_Delete', array( $this, 'ajax_delete' ) );

		// Link File Path
		add_action( 'wp_ajax_SnC_Link_File_Path', array( $this, 'ajax_link_file_path' ) );
	}

	/**
	 * Thickbox Media Upload
	 *
	 * @trigger media_upload_snc action
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function media_upload() {
		// Modify Modal Uploader Tabs
		add_filter( 'media_upload_tabs', array( $this, 'media_upload_tab' ),100 );

		if ( isset( $_REQUEST[ 'tab' ] ) && strstr( $_REQUEST[ 'tab' ], 'snc-library' ) ) {
			wp_iframe( array( $this, 'media_upload_library' ) );
		} else {
			wp_iframe( array( $this, 'media_upload_form' ) );
		}
	}

	/**
	 * Thickbox Media Upload Form
	 *
	 * @trigger wp_iframe()
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function media_upload_form() {
		$post = false;

		$nivo_transitions = \TINCANNYSNC\Shortcode::$nivo_transitions;

		$options = Options::get_options();
		if( !isset( $_GET['no_tab'] ) ){
			media_upload_header();
		}

		include_once( SnC_PLUGIN_DIR . 'views/media_upload_file.php' );
		if( !isset( $_GET['no_tab'] ) ) {
			include_once( SnC_PLUGIN_DIR . 'views/embed_information.php' );
		}
	}

	/**
	 * Thickbox Media Upload Library
	 *
	 * @trigger wp_iframe()
	 * @since 0.0.1
	 * @access public
	 */
	public function media_upload_library() {
		$posts = \TINCANNYSNC\Database::get_modules();

		$nivo_transitions = \TINCANNYSNC\Shortcode::$nivo_transitions;

		$options = Options::get_options();

		media_upload_header();
		include_once( SnC_PLUGIN_DIR . 'views/content_library.php' );
	}

	/**
	 * Thickbox Media Upload Tab
	 *
	 * @trigger media_upload_tabs filter
	 * @since 0.0.1
	 * @access public
	 */
	public function media_upload_tab( $tabs ) {
		return array(
			'upload' => 'Upload File',
			'snc-library' => 'Content Library',
		);
	}

	/**
	 * Ajax Request for Media Upload
	 *
	 * @trigger wp_ajax_SnC_Media_Upload Action
	 * @since 0.0.1
	 * @access public
	 todo : insert DB add to file system
	 */
	public function ajax_upload() {
		check_ajax_referer( 'snc-media_upload_form', 'security' );

		$file = $_FILES['media_upload_file']['tmp_name'];

		// get name & extension
		$title = trim( $_FILES['media_upload_file']['name'] );
		$title = explode( '.', $title );
		$extension = array_pop( $title );
		$title = implode( '.', $title );

		if ( $extension == 'zip' ) {
			$item_id = false;
			if ( isset( $_POST['content_id'] ) && ! empty( $_POST['content_id'] ) ) {
				$item_id = $_POST['content_id'];
				\TINCANNYSNC\Database::update_item_title( $item_id, $title );
				$item_id .= '-temp';
			} else {
				$item_id = \TINCANNYSNC\Database::add_item( $title );
			}
			if ( $item_id ) {
				$new_file = new \TINCANNYSNC\FileSystem\NewFile( $item_id, $file );

				if ( $new_file->get_upload_error() ) { // Uploading Error is set
					$message = json_encode( array(
						'id'      => 'error',
						'message' => __( $new_file->get_upload_error(), "uncanny-learndash-reporting" ),
					));

				} else if ( ! $new_file->get_type() ) { // Not Supported File
					$message = json_encode( array(
						'id'        => 'not_supported',
						'message'   => __( 'This file type is not supported.', "uncanny-learndash-reporting" ),
						'ajaxPath'  => admin_url( 'admin-ajax.php' ),
						'nonce'     => wp_create_nonce( "snc-link-file-path-form" ),
						'title'     => $title,
						'structure' => json_encode( $new_file->get_structure() ),
					));

				} else if ( $new_file->get_uploaded() ) { // Success
					$message = $new_file->get_result_json( $title );


				} else { // Something Wrong
					$message = json_encode( array(
						'id'      => 'error',
						'message' => __( 'Something went wrong.', "uncanny-learndash-reporting" ),
					));
				}

			} else { // Database Failure
				$message = json_encode( array(
					'id'      => 'error',
					'message' => __( 'Something went wrong when setting up your database.', "uncanny-learndash-reporting" ),
				));
			}

		} else { // Not Zip
			$message = json_encode( array(
				'id'      => 'error',
				'message' => __( 'File extension must be .zip.', "uncanny-learndash-reporting" ),
			));
		}

		echo $message;
		die;
	}

	/**
	 * Ajax Request for Shortcode Generate
	 *
	 * @trigger wp_ajax_SnC_Media_Embed Action
	 * @since 0.0.1
	 * @access public
	 */
	public function ajax_embed() {
		check_ajax_referer( 'snc-media_enbed_form', 'security' );
		$shortcode = \TINCANNYSNC\Shortcode::generate_shortcode( $_POST );

		if ( !$shortcode ) return false;

		echo json_encode( array(
			'shortcode' => $shortcode
		));

		die;
	}

	public function ajax_delete() {
		if ( $_POST['mode'] !== 'vc' )
			check_ajax_referer( 'snc-media_enbed_form', 'security' );

		$Module = \TINCANNYSNC\Module::get_module( $_POST['item_id'] );
		$Module->delete();

		die;
	}

	public function ajax_link_file_path() {
		check_ajax_referer( 'snc-link-file-path-form', 'security' );

		$title    = $_POST['title'];
		$filePath = $_POST['filePath'];

		$item_id = \TINCANNYSNC\Database::add_item( $title );
		$new_file = new \TINCANNYSNC\FileSystem\NewFile( $item_id, null, $filePath );

		$new_module = new \TINCANNYSNC\FileSystem\Module\UnknownType( $item_id );
		$db_data = \TINCANNYSNC\Database::get_item( $item_id );
		$new_module->set_url( $db_data['url'] );
		$new_module->add_nonce_block_code();

		$message = $new_file->get_result_json( $title );

		echo $message;
		die;
	}

	private function format_bytes( $size, $precision = 2 ) {
		$base = log( $size, 1024 );
		$suffixes = array( '', 'kB', 'MB', 'GB', 'TB' );
		$number = number_format( round( pow( 1024, $base - floor( $base ) ), $precision ) );

		return $number . $suffixes[floor( $base )];
	}
}
