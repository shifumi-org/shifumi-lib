<?php
/**
 * Shortcode
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Shortcode {
	static public $nivo_transitions = array(
		'Fade'        => 'fade',
		'Fade Scale'  => 'fadeScale',
		'Slide Left'  => 'slideLeft',
		'Slide Right' => 'slideRight',
		'Slide Up'    => 'slideUp',
		'Slide Down'  => 'slideDown',
		'Fall'        => 'fall',
	);

	static private $OPTION = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	function __construct() {

		add_shortcode( 'vc_snc', array( $this, 'shortcode' ) );

		add_filter( 'learndash_mark_complete', [ $this, 'wrap_complete_button' ], 99, 2 );

		self::$OPTION = get_option( SnC_TEXTDOMAIN );

		if (
			! isset( self::$OPTION['autocompleLessonsTopicsTincanny'] ) ||
			( isset( self::$OPTION['autocompleLessonsTopicsTincanny'] ) && self::$OPTION['autocompleLessonsTopicsTincanny'] !== '1' )
		) {
			add_action( 'template_redirect', [ $this, 'maybe_kill_pro_autocomplete' ] );
		}
		
		/**
		 * For supporting Uncanny Toolkit Pro single page course
		 * @since 3.6
		*/
		add_action( 'wp', [ $this, 'single_step_course_override'], 1000 );

	}

	public function maybe_kill_pro_autocomplete() {

		global $post;

		if ( is_a( $post, 'WP_Post' ) &&
			 (
				 has_shortcode( $post->post_content, 'h5p' ) ||
				 has_shortcode( $post->post_content, 'vc_snc' ) ||
				 $this->has_tincanny_block()
			 )
		) {

			// remove add_filter( 'learndash_show_next_link', array( __CLASS__, 'learndash_show_next_link_progression' ), 10, 3 );
			remove_filter( 'learndash_show_next_link', array(
				'uncanny_pro_toolkit\LessonTopicAutoComplete',
				'learndash_show_next_link_progression',
			), 10 );

			// remove add_filter( 'learndash_mark_complete', array( __CLASS__, 'remove_mark_complete_button' ), 99, 2 );
			remove_filter( 'learndash_mark_complete', array(
				'uncanny_pro_toolkit\LessonTopicAutoComplete',
				'remove_mark_complete_button',
			), 99 );

			// remove add_action( 'shutdown', array( __CLASS__, 'auto_complete_module' ), 10 );
			remove_action( 'shutdown', array(
				'uncanny_pro_toolkit\LessonTopicAutoComplete',
				'auto_complete_module',
			), 10 );
		}
	}

	/**
	 * Is TinCan Module Exists
	 *
	 * @access  private
	 * @return  bool
	 * @since   1.0.0
	 */
	private function check_tincan() {
		return (
			class_exists( '\\UCTINCAN\\Init' ) &&
			! empty( \UCTINCAN\Init::$TinCan ) &&
			is_object( \UCTINCAN\Init::$TinCan ) &&
			get_class( \UCTINCAN\Init::$TinCan ) == 'TinCan\RemoteLRS'
		);
	}

	private function has_tincanny_block() {

		global $post;

		// Check if the Tin Canny Gutenberg Block is being used
		// First create variable where we're going to save the result
		$tincanny_gutenberg_block_is_being_used = false;

		// Check if Gutenberg exists (just in case the user
		// is using WP < 5.0 )
		if ( function_exists( 'has_blocks' ) && function_exists( 'parse_blocks' ) ) {
			// Check if the post content has blocks
			if ( has_blocks( $post->post_content ) ) {
				// Get all the blocks
				$blocks = parse_blocks( $post->post_content );
				// Iterate all the blocks
				$tincanny_gutenberg_block_is_being_used = self::detect_inner_block( $blocks, 'tincanny/content' );
			}
		}

		return $tincanny_gutenberg_block_is_being_used;

	}
	
	public static function detect_inner_block( $blocks, $block_code ) {
		$block_is_on_page = false;
		foreach ( $blocks as $block ) {
			if ( $block_code === $block['blockName'] ) {
				$block_is_on_page = true;
			}
			if ( ! $block_is_on_page && ! empty( $block['innerBlocks'] ) ) {
				$block_is_on_page = self::detect_inner_block( $block['innerBlocks'], $block_code );
			}
		}
		
		return $block_is_on_page;
	}
	
	/**
	 * Wrap the LearnDash complete button
	 */

	public function wrap_complete_button( $button, $post ) {

		// If "Capture Tin Can and SCORM data" is disabled
		$is_capture_enabled = get_option( 'show_tincan_reporting_tables', 'yes' );
		if( $is_capture_enabled == 'no' ) {
			return $button;
		}

		if ( is_a( $post, 'WP_Post' ) &&
			 (
				 has_shortcode( $post->post_content, 'h5p' ) ||
				 has_shortcode( $post->post_content, 'vc_snc' ) ||
				 $this->has_tincanny_block()
			 )
		) {

			// Create the variable we're going to use to know if we have to hide
			// or not the "mark as complete" button on load
			$hide_mark_as_complete = true;

			// Get the value of the "Restrict Mark Complete" field
			$post_option = ( is_object( $post ) ) ? get_post_meta( $post->ID, '_WE-meta_', true ) : [];

			if ( empty( $post_option ) || ! is_array( $post_option ) ) {
				$post_option = [];
			}

			// If it isn't defined, use the default value
			if ( ! is_array( $post_option ) || ! isset( $post_option['restrict-mark-complete'] ) ) {
				$post_option['restrict-mark-complete'] = 'Use Global Setting';
			}

			switch ( $post_option['restrict-mark-complete'] ) {
				case 'Use Global Setting':
					// Get the global value
					$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );

					// Check if we don't have to hide it
					if ( $global_option == 'no' ) {
						// Don't hide the button
						$hide_mark_as_complete = false;
					}
					break;

				case 'No':
					// Don't hide the button
					$hide_mark_as_complete = false;
					break;
			}

			// Create variable with the button
			// Return the same HMTL, but add a wrapper
			// If we have to hide the button on load, add inline CSS
			if ( $hide_mark_as_complete ) {
				$tincanny_button = '<div class="tclr-mark-complete-button" style="display: none">' . $button . '</div>';
			} // Otherwise, just add the wrapper
			else {
				$tincanny_button = '<div class="tclr-mark-complete-button">' . $button . '</div>';
			}

			return $tincanny_button;
		} // Otherwise, just add the wrapper
		else {
			// Return the button
			return $button;
		}


	}

	/**
	 * Shortcode Main
	 *
	 * @trigger add_shortcode( 'vc_snc' )
	 * @access  public
	 * @return  string
	 * @since   1.0.0
	 */
	public function shortcode( $atts ) {
		extract( shortcode_atts( array(
			'embed_type'          => 'iframe',
			'item_id'             => '',
			'item_name'           => '',
			'title'               => '',
			'button'              => '',
			'user_button_type'    => '',
			'button_text'         => '',
			'button_image'        => '',
			'nivo_transition'     => '',
			'colorbox_theme'      => '',
			'colorbox_transition' => '',
			'colorbox_scrollbar'  => '',
			'width'               => '',
			'width_type'          => '',
			'height'              => '',
			'height_type'         => '',
			'wrapper'             => '1'
		), $atts, 'vc_snc' ) );

		if ( ! $item_id ) {
			return '';
		}

		// Define dimensions
		// Create an array with the list of supported CSS units for the dimensions
		$supported_css_units = [ 'pr', '%', 'px', 'vw', 'vh' ];

		// Check if our values already have an unit
		// Width
		preg_match( '/([A-Za-z\%]{1,2})/', $width, $width_type_match );
		if ( isset( $width_type_match[0] ) ) {
			if ( empty( $width_type ) ) {
				$width_type = $width_type_match[0];
			}

			$width = str_replace( $width_type_match[0], '', $width );
		}
		// Height
		preg_match( '/([A-Za-z\%]{1,2})/', $height, $height_type_match );
		if ( isset( $height_type_match[0] ) ) {
			if ( empty( $height_type ) ) {
				$height_type = $height_type_match[0];
			}

			$height = str_replace( $height_type_match[0], '', $height );
		}

		// Set default units
		// Width
		if ( ! in_array( $width_type, $supported_css_units ) ) {
			$width_type = 'px';
		}
		// Height
		if ( ! in_array( $height_type, $supported_css_units ) ) {
			$height_type = 'px';
		}

		// Check if we have to change "pr" for %
		$width_type  = $width_type == 'pr' ? '%' : $width_type;
		$height_type = $height_type == 'pr' ? '%' : $height_type;

		// Now that we know we have valid information, define
		// final values for $width and $height
		$width  = $width . $width_type;
		$height = $height . $height_type;

		// Define variable $open_with
		// We will use this to decide if we have to show a button, image or link
		$open_with = 'button';

		global $post;
		$Module   = Module::get_module( $item_id );
		$postmeta = get_post_meta( $post->ID, '_WE-meta_', true );

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

		if ( $protection === 'Yes' && ! is_user_logged_in() ) {
			return '';
		}

		if ( ! empty( $Module ) && $Module->is_available() ) {
			$src  = $Module->get_url();
			$User = wp_get_current_user();

			if ( $this->check_tincan() ) {
				$user_name  = @( $User->data->display_name ) ? $User->data->display_name : 'Unknown';
				$user_email = @( $User->data->user_email ) ? $User->data->user_email : 'Unknown@anonymous.com';
				$course_id  = 0;
				if ( function_exists( 'learndash_get_course_id' ) ) {
					$course_id = learndash_get_course_id( $post->ID );
				}

				global $post;

				$args = array(
					'endpoint'    => \UCTINCAN\Init::$endpint_url . '/',
					'auth'        => 'LearnDashId' . $post->ID,
					'course_id'   => $course_id,
					'actor'       => rawurlencode( sprintf( '{"name": ["%s"], "mbox": ["mailto:%s"]}', $user_name, $user_email ) ),
					'activity_id' => $src,
					'client'      => $Module->get_type(),
					'base_url'    => get_option( 'home' ),
					'nonce'       => wp_create_nonce( 'tincanny-module' ),
				);

				$src = add_query_arg( $args, $src );
			}
		} else if ( empty( $Module ) ) {
			return __( 'The module you are trying to access does not exist.', 'uncanny-learndash-reporting' );

		} else {
			return false;
		}

		// Check if the user selected to show a link
		if ( $button == 'text' && $button_text ) {
			$open_with = 'link';
		} // Check if the user selected to show an image
		else if ( ( $button == 'image' || $button == 'url' ) && $button_image ) {
			$open_with = 'image';
		} // Otherwise use a button
		else {
			$open_with = 'button';

			if ( ! empty( $button_text ) ) {
				$button_image = '';
			}
		}

		if ( empty( $postmeta ) || ! is_array( $postmeta ) ) {
			$postmeta = [];
		}

		if ( ! isset( $postmeta['restrict-mark-complete'] ) ) {
			$postmeta['restrict-mark-complete'] = 'Use Global Setting';
		}

		$global_mark_complete_setting = get_option( 'disable_mark_complete_for_tincan', 'yes' );
		switch ( $postmeta['restrict-mark-complete'] ) {
			case 'Use Global Setting' :
				$mark_complete_settings = $global_mark_complete_setting;
				break;
			default:
				$mark_complete_settings = $postmeta['restrict-mark-complete'];
				break;
		}

		switch ( $embed_type ) {
			case 'iframe':
				$width  = ( ! $width ) ? '100%' : $width;
				$height = ( ! $height ) ? '600px' : $height;

				return $this->return_iframe( array(
					'width'   => $width,
					'height'  => $height,
					'src'     => $src,
					'wrapper' => $wrapper
				) );
				break;

			case '_blank':
			case '_self':
				return $this->return_link( array(
					'open_with'   => $open_with,
					'link_text'   => $button_text,
					'button'      => $button_image,
					'button_size' => $button,
					'target'      => $embed_type,
					'href'        => $src,
				) );
				break;

			case 'lightbox' :
				$slider_script = 'nivo';
				$theme         = '';
				$transition    = $nivo_transition;

				$default_options = Admin\Options::get_options();

				$width  = ( $width ) ? $width : $default_options['width'] . $default_options['width_type'];
				$height = ( $height ) ? $height : $default_options['height'] . $default_options['height_type'];

				return $this->return_lightbox( array(
					'title'         => $title,
					'open_with'     => $open_with,
					'link_text'     => $button_text,
					'button'        => $button_image,
					'button_size'   => $button,
					'scrollbar'     => $colorbox_scrollbar,
					'width'         => $width,
					'height'        => $height,
					'slider_script' => $slider_script,
					'href'          => $src,
					'theme'         => $theme,
					'transition'    => $transition,
				) );
				break;
		}
	}

	/**
	 * Shortcode Return [lightbox]
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function return_lightbox( $atts ) {
		extract( shortcode_atts( array(
			'title'        => '',
			'open_with'    => 'button',
			'link_text'    => '',
			'button'       => '',
			'button_size'  => 'medium',
			'scrollbar'    => '',
			'size_optiton' => '',
			'width'        => '',
			'height'       => '',
			'href'         => '',
			'theme'        => '',
			'transition'   => ''
		), $atts, 'link' ) );
		if ( ! $href ) {
			return '';
		}

		$options = Admin\Options::get_options();

		if ( $open_with == 'button' ) {
			if ( empty( $link_text ) ) {
				$link_text = __( 'Launch', 'uncanny-learndash-reporting' );
			}

			$button_css_classes   = [ 'uo-tclr-open-content-button' ];
			$button_css_classes[] = sprintf( 'uo-tclr-open-content-button--%s', $button_size );

			$text = sprintf( '<span class="%s"><span class="uo-tclr-open-content-button__text">%s</span><span class="uo-tclr-open-content-button__icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm113.9 231L234.4 103.5c-9.4-9.4-24.6-9.4-33.9 0l-17 17c-9.4 9.4-9.4 24.6 0 33.9L285.1 256 183.5 357.6c-9.4 9.4-9.4 24.6 0 33.9l17 17c9.4 9.4 24.6 9.4 33.9 0L369.9 273c9.4-9.4 9.4-24.6 0-34z"></path></svg></span></span>', implode( ' ', $button_css_classes ), $link_text );
		} elseif ( $open_with == 'link' ) {
			$text = sprintf( '<span class="uo-tclr-open-content-link">%s</span>', $link_text );
		} elseif ( $open_with == 'image' ) {
			$text = sprintf( '<span class="uo-tclr-open-content-image"><img class="launch_presentation" src="%s" title="launch_presentation" /></span>', $button );
		}

		$title      = ( $title ) ? sprintf( ' title="%s"', $title ) : '';
		$scrolling  = ( $scrollbar == 'no' ) ? ' data-scroll="false"' : '';
		$width      = ( $width ) ? ' data-width="' . $width . '"' : '';
		$height     = ( $height ) ? ' data-height="' . $height . '"' : '';
		$transition = ( $transition ) ? ' data-transition="' . $transition . '"' : '';

		wp_enqueue_script( 'nivo-lightbox', SnC_ASSET_URL . 'venders/nivo-lightbox/nivo-lightbox.min.js', array( 'jquery' ), '1.3.1', true );
		wp_enqueue_style( 'nivo-lightbox', SnC_ASSET_URL . 'venders/nivo-lightbox/nivo-lightbox.css' );
		wp_enqueue_style( 'nivo-lightbox-default', SnC_ASSET_URL . 'venders/nivo-lightbox/themes/default/default.css' );

		return sprintf( '<a class="nivo_iframe" data-lightbox-type="iframe" href="%s" %s %s %s %s %s>%s</a>', $href, $title, $scrolling, $width, $height, $transition, $text );
	}

	/**
	 * Shortcode Return [iframe]
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function return_iframe( $atts ) {
		extract( shortcode_atts( array(
			'width'  => '',
			'height' => '',
			'src'    => '',
			'wrapper' => '1'
		), $atts, 'link' ) );
		if ( ! $src ) {
			return '';
		}

		$wrapper = $wrapper == '1' ? true : false;

		// Start output
		ob_start();

		?>

		<?php if ( $wrapper ){ ?> 

			<div class="uo-tincanny-content">

		<?php } ?> 

		
			<?php printf( '<iframe class="AnC-iFrame" data-src="%s" style="width: %s; height: %s;" frameborder="0"></iframe>', $src, $width, $height ); ?>

		<?php if ( $wrapper ){ ?> 

			</div>

		<?php } ?>

		<?php

		// Return output
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Shortcode Return [link]
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function return_link( $atts ) {
		extract( shortcode_atts( array(
			'open_with'   => 'button',
			'link_text'   => '',
			'button'      => '',
			'button_size' => 'medium',
			'target'      => '_blank',
			'href'        => '',
		), $atts, 'link' ) );
		if ( ! $href ) {
			return '';
		}

		$options = Admin\Options::get_options();

		if ( $open_with == 'button' ) {
			if ( empty( $link_text ) ) {
				$link_text = __( 'Launch', 'uncanny-learndash-reporting' );
			}

			$button_css_classes   = [ 'uo-tclr-open-content-button' ];
			$button_css_classes[] = sprintf( 'uo-tclr-open-content-button--%s', $button_size );

			$text = sprintf( '<span class="%s"><span class="uo-tclr-open-content-button__text">%s</span><span class="uo-tclr-open-content-button__icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm113.9 231L234.4 103.5c-9.4-9.4-24.6-9.4-33.9 0l-17 17c-9.4 9.4-9.4 24.6 0 33.9L285.1 256 183.5 357.6c-9.4 9.4-9.4 24.6 0 33.9l17 17c9.4 9.4 24.6 9.4 33.9 0L369.9 273c9.4-9.4 9.4-24.6 0-34z"></path></svg></span></span>', implode( ' ', $button_css_classes ), $link_text );
		} elseif ( $open_with == 'link' ) {
			$text = sprintf( '<span class="uo-tclr-open-content-link">%s</span>', $link_text );
		} elseif ( $open_with == 'image' ) {
			$text = sprintf( '<span class="uo-tclr-open-content-image"><img class="launch_presentation" src="%s" title="launch_presentation" /></span>', $button );
		}

		return sprintf( '<a class="uo-tclr-open-content AnC-Link" href="%s" target="%s">%s</a>', $href, $target, $text );
	}

	/**
	 * Shortcode Generator with $_POST data
	 *
	 * @since  0.0.1
	 * @access public
	 */
	static public function generate_shortcode( $data ) {
		if ( ! $data['id'] ) {
			return false;
		}

		$default_options = Admin\Options::get_options();
		$Module          = Module::get_module( $data['id'] );
		$url             = false;

		if ( $Module->is_available() ) {
			$url = $Module->get_url();
		}

		if ( ! $url ) {
			return false;
		}

		$embed_type = $lightbox_title_text = $link_text = $btn_src = $scrollbar = $size_option = $width = $height = $slider = $theme = $transition = '';
		$embed_type = $data['insert_type'];

		// Modifying Name : TODO
		$data['title'] = Database::ChangeNameFromId( $data['id'], $data['title'] );

		$shortcode = sprintf( '[vc_snc embed_type="%s" item_id="%s" item_name="%s"', $embed_type, $data['id'], $data['title'] );

		switch ( $embed_type ) {
			case 'iframe' :
				$width  = ( ! $data['iframe_width'] ) ? ' width="100%"' : ' width="' . $data['iframe_width'] . $data['iframe_width_type'] . '"';
				$height = ( ! $data['iframe_height'] ) ? ' height="600px"' : ' height="' . $data['iframe_height'] . $data['iframe_height_type'] . '"';

				return sprintf( $shortcode . '%s%s frameborder="0" src="%s"]', $width, $height, $url );
				break;

			case 'lightbox' :
				// Title
				$lightbox_title_text = ( $data['lightbox_title'] == 'With Title' && $data['lightbox_title_text'] ) ? ' title="' . $data['lightbox_title_text'] . '"' : '';

				// Button
				switch ( $data['lightbox_button'] ) {
					case 'url' :
						$btn_src = ' button_image="' . $data['lightbox_button_url'] . '"';
						break;

					case 'text' :
					case 'small' :
					case 'medium' :
					case 'large' :
						if ( $data['lightbox_button_text'] ) {
							$link_text = ' button_text="' . $data['lightbox_button_text'] . '"';
						}
						break;

					case 'image' :
						if ( ! $_FILES['lightbox_button_custom_file']['error'] && $_FILES['lightbox_button_custom_file']['name'] ) {
							$file    = self::insert_attachment( $_FILES['lightbox_button_custom_file'] );
							$btn_src = ' button_image="' . $file['url'] . '"';
						}
						break;
				}

				// Scrollbar
				$scrollbar = ( isset( $data['scrollbar'] ) && $data['scrollbar'] == 'false' ) ? ' colorbox_scrollbar="no"' : '';

				$data['width']  = ( isset( $data['width'] ) && $data['width'] ) ? $data['width'] : $default_options['width'];
				$data['height'] = ( isset( $data['height'] ) && $data['height'] ) ? $data['height'] : $default_options['height'];

				$width  = ' width="' . $data['width'] . $data['width_type'] . '"';
				$height = ' height="' . $data['height'] . $data['height_type'] . '"';

				// Slider
				$slider = ' slider_script="nivo"';

				$transition = ' nivo_transition="' . $data['nivo_transition'] . '"';

				return sprintf( $shortcode . ' button="%s"%s%s%s%s%s%s%s%s%s href="%s"]', $data['lightbox_button'], $lightbox_title_text, $link_text, $btn_src, $scrollbar, $width, $height, $slider, $theme, $transition, $url );
				break;

			case '_blank' :
				switch ( $data['_blank'] ) {
					case 'url' :
						$btn_src = ' button_image="' . $data['_blank_url'] . '"';
						break;

					case 'text' :
					case 'small' :
					case 'medium' :
					case 'large' :
						if ( $data['_blank_text'] ) {
							$link_text = ' button_text="' . $data['_blank_text'] . '"';
						}
						break;

					case 'image' :
						if ( ! $_FILES['upload_blank_lightbox_custom_button']['error'] && $_FILES['upload_blank_lightbox_custom_button']['name'] ) {
							$file    = self::insert_attachment( $_FILES['upload_blank_lightbox_custom_button'] );
							$btn_src = ' button_image="' . $file['url'] . '"';
						}
						break;
				}

				return sprintf( $shortcode . ' button="%s"%s%s href="%s"]', $data['_blank'], $link_text, $btn_src, $url );
				break;

			case '_self' :
				switch ( $data['_self'] ) {
					case 'url' :
						$btn_src = ' button_image="' . $data['_self_url'] . '"';
						break;

					case 'text' :
					case 'small' :
					case 'medium' :
					case 'large' :
						if ( $data['_self_text'] ) {
							$link_text = ' button_text="' . $data['_self_text'] . '"';
						}
						break;

					case 'image' :
						if ( ! $_FILES['upload_self_lightbox_custom_button']['error'] && $_FILES['upload_self_lightbox_custom_button']['name'] ) {
							$file    = self::insert_attachment( $_FILES['upload_self_lightbox_custom_button'] );
							$btn_src = ' button_image="' . $file['url'] . '"';
						}
						break;
				}

				return sprintf( $shortcode . ' button="%s"%s%s href="%s"]', $data['_self'], $link_text, $btn_src, $url );
				break;
		}
	}

	static public function insert_attachment( $file ) {
		$name      = $file['name'];
		$temp_name = $file['tmp_name'];

		$error         = new \WP_Error();
		$wp_upload_dir = wp_upload_dir();
		$uploadfile    = "{$wp_upload_dir['path']}/{$name}";

		if ( move_uploaded_file( $temp_name, $uploadfile ) ) {
			$wp_filetype = wp_check_filetype( basename( $name ), null );

			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $name ),
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $uploadfile );

			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploadfile );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return array(
				'ID'   => $attach_id,
				'post' => $attachment,
				'url'  => $wp_upload_dir['url'] . '/' . basename( $name ),
			);
		} else {
			$error->add( 'File_upload', __( 'Failed to upload a file.', "uncanny-learndash-reporting" ) );

			return $error;
		}
	}
	
	/**
	 * Override Single Page Course default settings.
	 * It removes completion button and also removes auto completion
	 * @since 3.6
	 */
	public function single_step_course_override() {
		global $post;
		if ( class_exists( '\uncanny_pro_toolkit\OnePageCourseStep' ) ) {
			$uncanny_active_classes = get_option( 'uncanny_toolkit_active_classes', array() );
			if ( ! empty( $uncanny_active_classes ) && is_array( $uncanny_active_classes ) && key_exists( 'uncanny_pro_toolkit\OnePageCourseStep', $uncanny_active_classes ) ) {
				
				if ( ! empty( $post ) && $post->post_type === 'sfwd-courses' ) {
					$single_page_tutorial = get_post_meta( $post->ID, 'uo_single_page_course', TRUE );
					if ( $single_page_tutorial ) {
						
						if ( is_a( $post, 'WP_Post' )
						     && (
							     has_shortcode( $post->post_content, 'h5p' )
							     || has_shortcode( $post->post_content, 'vc_snc' )
							     || $this->has_tincanny_block()
						     )
						) {
							// If "Capture Tin Can and SCORM data" is disabled
							$is_capture_enabled = get_option( 'show_tincan_reporting_tables', 'yes' );
							if( $is_capture_enabled == 'no' ) {
								return;
							}
							// Remove autocomplete.
							remove_action( 'shutdown', [ 'uncanny_pro_toolkit\OnePageCourseStep', 'auto_complete_module' ], 10 );
							remove_action( 'learndash-course-after', [ 'uncanny_pro_toolkit\OnePageCourseStep', 'uo_mark_complete_form' ], 10 );
							// Add own custom button
							add_action( 'learndash-course-after', [ __CLASS__, 'uo_mark_complete_form' ], 10, 3 );
						}
					}
				}
			}
		}
	}
	/**
	 * Override Single Page Course default completion button
	 * @since 3.6
	 */
	public static function uo_mark_complete_form() {
		global $post;
		$single_page_tutorial = get_post_meta( $post->ID, 'uo_single_page_course', TRUE );
		if ( $single_page_tutorial ) {
			$autocomplete_course = get_post_meta( $post->ID, 'uo_autocomplete_course', TRUE );
			$has_access          = sfwd_lms_has_access( $post->ID );
			// Create the variable we're going to use to know if we have to hide
			// or not the "mark as complete" button on load
			$hide_mark_as_complete = TRUE;
			
			// Get the value of the "Restrict Mark Complete" field
			$post_option = ( is_object( $post ) ) ? get_post_meta( $post->ID, '_WE-meta_', TRUE ) : [];
			
			if ( empty( $post_option ) || ! is_array( $post_option ) ) {
				$post_option = [];
			}
			
			// If it isn't defined, use the default value
			if ( ! is_array( $post_option ) || ! isset( $post_option['restrict-mark-complete'] ) ) {
				$post_option['restrict-mark-complete'] = 'Use Global Setting';
			}
			
			switch ( $post_option['restrict-mark-complete'] ) {
				case 'Use Global Setting':
					// Get the global value
					$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );
					
					// Check if we don't have to hide it
					if ( $global_option == 'no' ) {
						// Don't hide the button
						$hide_mark_as_complete = FALSE;
					}
					break;
				
				case 'No':
					// Don't hide the button
					$hide_mark_as_complete = FALSE;
					break;
			}
			
			// Create variable with the button
			// Return the same HMTL, but add a wrapper
			// If we have to hide the button on load, add inline CSS
			if ( $hide_mark_as_complete ) {
				$tincanny_button = 'class="tclr-mark-complete-button" style="display: none"';
			} // Otherwise, just add the wrapper
			else {
				$tincanny_button = 'class="tclr-mark-complete-button"';
			}
			?>
			<style>
				.ld-item-list.ld-lesson-list {
					display: none !important;
				}
			</style>
			<?php
			if ( $has_access && ! $autocomplete_course ) :
				if ( ! learndash_course_completed( get_current_user_id(), $post->ID ) ) :?>
					<div <?php echo $tincanny_button; ?>>
						<form id="sfwd-mark-complete" method="post" action="">
							<input type="hidden" value="<?php echo $post->ID; ?>" name="post"/>
							<input type="hidden" value="<?php echo wp_create_nonce( 'sfwd_mark_complete_' . get_current_user_id() . '_' . $post->ID ); ?>" name="pec_sfwd_mark_complete"/>
							<input type="submit" value="<?php echo esc_html( \LearnDash_Custom_Label::get_label( 'button_mark_complete' ) ); ?>" id="learndash_mark_complete_button"/>
						</form>
					</div>
				<?php endif;
			endif;
		}
	}
}
