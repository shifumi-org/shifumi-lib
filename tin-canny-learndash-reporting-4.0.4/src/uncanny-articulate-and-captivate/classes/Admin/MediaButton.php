<?php
/**
 * Media Button
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\Admin;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class MediaButton {
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		//add_action( 'media_buttons', array( $this, 'media_button' ),100 );
	}

	/**
	 * @return mixed|void
	 */
	public static function show_media_button() {
		$is_post_edit_page = in_array( basename( $_SERVER['PHP_SELF'] ), array(
			'post.php',
			'page.php',
			'page-new.php',
			'post-new.php',
			'customize.php',
			'admin-ajax.php',
		) );

		$display_add_tin_canny_media = apply_filters( 'uo_display_add_tin_canny_media_button', $is_post_edit_page );

		return $display_add_tin_canny_media;
	}

	/**
	 * Print Media Button
	 *
	 * @trigger media_buttons action
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public static function media_button() {

		$is_add_media_button = self::show_media_button();
		if ( ! $is_add_media_button ) {
			return;
		}

		/**
		 * We don't add any spaces before the <a> because .button makes the element inline,
		 * and that space adds two pixels between the buttons. We don't want that.
		 * Please keep the ?> and <a> without spaces in the middle
		 */
		?><a href="media-upload.php?type=snc&tab=upload&TB_iframe=true" class="button thickbox">
		<?php _e( 'Add Tin Canny Media', 'uncanny-learndash-reporting' ); ?>
		</a> <?php
	}
}

if ( class_exists( 'ET_Builder_Plugin_Compat_Base' ) ) {

	/**
	 * Plugin compatibility for Tin Canny Reporting for LearnDash
	 *
	 * @since 3.4
	 */
	class ET_Builder_Plugin_Compat_Uo_Tin_Canny extends \ET_Builder_Plugin_Compat_Base {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->plugin_id = 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php';
			$this->init_hooks();
		}

		/**
		 * Hook methods to WordPress
		 *
		 * @return void
		 */
		public function init_hooks() {
			$is_bfb = et_()->array_get( $_GET, 'et_bfb' );

			// Load Add Tin Canny Media button in BFB
			if ( $is_bfb ) {
				add_filter( 'uo_display_add_tin_canny_media_button', '__return_true' );
			}
		}
	}

	new ET_Builder_Plugin_Compat_Uo_Tin_Canny();
}

