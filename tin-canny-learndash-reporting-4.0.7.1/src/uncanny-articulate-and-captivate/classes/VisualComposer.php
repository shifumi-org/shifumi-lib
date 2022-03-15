<?php
/**
 * Visual Composer
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class VisualComposer {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	/**
	 * Autoload
	 *
	 * @since 0.0.1
	 * @access public
	 */
	function __construct() {
		add_action( 'vc_before_init', array( $this, 'vc_map' ) );
		add_filter( 'vc_edit_form_enqueue_script', array( $this, 'vc_edit_form_enqueue_script' ) );

		add_action( 'wp_ajax_vc-snc', array( $this, 'get_file_selector' ) );
		
	}

	function get_file_selector() {
		$posts = Database::get_modules();
		$content_library_vc_mode = true;

		include_once( SnC_PLUGIN_DIR . 'views/vc_toggle.php' );
		include_once( SnC_PLUGIN_DIR . 'views/media_upload_file.php' );
		include_once( SnC_PLUGIN_DIR . 'views/content_library.php' );
		include_once( SnC_PLUGIN_DIR . 'views/vc_choose_button.php' );

		die;
	}

	function vc_edit_form_enqueue_script( $script ) {
		$script[] = SnC_ASSET_URL . "scripts/visual_composer.js";
		return $script;
	}

	function vc_map() {
		$nivo_transitions = Shortcode::$nivo_transitions;

		vc_map( array(
			"name" => __( "Storyline and Captivate" ),
			"base" => "vc_snc",
			"category" => __( 'Content' ),
			"params" => array(
				// item_id (hidden)
				array(
					"type" => "textfield",
					"edit_field_class" => "hidden vc-snc-trigger",
					"heading" => __( "ID", "uncanny-learndash-reporting" ),
					"param_name" => "item_id"
				),
				// item_name (hidden)
				array(
					"type" => "textfield",
					"edit_field_class" => "hidden vc-snc-name",
					"heading" => __( "Item Name", "uncanny-learndash-reporting" ),
					"param_name" => "item_name"
				),
				// Type
				array(
					"type" => "dropdown",
					"edit_field_class" => "hidden vc-snc-embed",
					"heading" => __( "Embed Type", "uncanny-learndash-reporting" ),
					"param_name" => "embed_type",
					"value" => array(
						'Choose Embed Type' => '',
						'iFrame' => 'iframe',
						'Lightbox' => 'lightbox',
						'New Window' => '_blank',
						'Same Window' => '_self'
					)
				),
				// Common : Title
				array(
					"type" => "textfield",
					"heading" => __( "Title", "uncanny-learndash-reporting" ),
					"param_name" => "title",
					"dependency" => array(
						"element" => "embed_type",
						"value" => array( "lightbox" )
					)
				),
				// Common : Button Type
				array(
					"type" => "dropdown",
					"heading" => __( "Button", "uncanny-learndash-reporting" ),
					"param_name" => "button",
					"value" => array(
						'Choose Button' => '',
						'Link Text' => 'text',
						'Small Size Button' => 'small',
						'Medium Size Button' => 'medium',
						'Large Size Button' => 'large',
						'Use custom image' => 'image'
					),
					"dependency" => array(
						"element" => "embed_type",
						"value" => array( "lightbox", "_blank", "_self" )
					),
				),
				// Common : Button Text
				array(
					"type" => "textfield",
					"heading" => __( "Link / Button Text", "uncanny-learndash-reporting" ),
					"param_name" => "button_text",
					"dependency" => array(
						"element" => "button",
						"value" => array( "text" )
					),
				),
				// Common : Button Image
				array(
					"type" => "attach_image",
					"heading" => __( "Button Image", "uncanny-learndash-reporting" ),
					"param_name" => "button_image",
					"dependency" => array(
						"element" => "button",
						"value" => array( "image" )
					),
				),
				// Lightbox : Nivo : Transition
				array(
					"type" => "dropdown",
					"heading" => __( "Nivo Transition", "uncanny-learndash-reporting" ),
					"param_name" => "nivo_transition",
					"value" => $nivo_transitions,
					"dependency" => array(
						"element" => "embed_type",
						"value" => array( "lightbox" )
					),
				),
				// Common : Width
				array(
					"type" => "textfield",
					"heading" => __( "Width", "uncanny-learndash-reporting" ),
					"param_name" => "width",
					"dependency" => array(
						"element" => "embed_type",
						"value" => array( "iframe", "lightbox" )
					),
					'description' => 'Input number and a unit (px or %)'
				),
				// Common : Height
				array(
					"type" => "textfield",
					"heading" => __( "Height", "uncanny-learndash-reporting" ),
					"param_name" => "height",
					"dependency" => array(
						"element" => "embed_type",
						"value" => array( "iframe", "lightbox" )
					),
					'description' => 'Input number and a unit (px or %)'
				),
			)
		) );
	}
}
