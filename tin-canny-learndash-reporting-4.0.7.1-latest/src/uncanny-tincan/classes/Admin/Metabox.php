<?php
/**
 * Metabox
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN\Admin;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
/**
 * Metabox class
 *
 * @since 1.0..0
 */
class Metabox {
	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		require_once UCTINCAN_PLUGIN_DIR . 'vendors/wp_express/autoload.php';

		$args = [
			'public'   => true,
			'_builtin' => false,
		];

		$output   = 'object';
		$operator = 'or';

		$post_types = get_post_types( $args, $output, $operator );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$this->set_metabox( $post_type->name );
			}
		}

		//$this->set_metabox( 'sfwd-lessons' );
		//$this->set_metabox( 'sfwd-topic' );
	}

	/**
	 * Set Metabox
	 *
	 * @param  string $post_type The current post type.
	 *
	 * @since  1.0.0
	 */
	private function set_metabox( $post_type ) {
		$lesson = new \WE_TINCANNY\PostType( $post_type );

		// restricted post types.
		$tc_restricted_types = [ 'sfwd-lessons', 'sfwd-topic' ];
		/**
		 * Supporting Uncanny Toolkit Pro single page course module.
		 * It adds tin canny settings for course.
		 * @since 3.6
		 */
		if ( class_exists( '\uncanny_pro_toolkit\OnePageCourseStep' ) ) {
			$active_classes = get_option( 'uncanny_toolkit_active_classes', array() );
			if ( ! empty( $active_classes ) && is_array( $active_classes ) && key_exists( 'uncanny_pro_toolkit\OnePageCourseStep', $active_classes ) ) {
				$tc_restricted_types = [ 'sfwd-lessons', 'sfwd-topic', 'sfwd-courses' ];
			}
		}

		$args = [
			'public'   => true,
			'_builtin' => true,
		];

		$output     = 'names';
		$operator   = 'or';
		$post_types = get_post_types( $args, $output, $operator );

		$lesson->version = '1.4';

		$option = get_option( SnC_TEXTDOMAIN );

		if ( ! $option) {
			$nonce_protection = '1';
		} elseif ( ! isset( $option['nonceProtection'] ) ) {
			$nonce_protection = '1';
		}else{
			$nonce_protection = $option['nonceProtection'];
		}
		// If "Capture Tin Can and SCORM data" is disabled
		$is_capture_enabled = get_option( 'show_tincan_reporting_tables', 'yes' );
		if( $is_capture_enabled == 'no' && '1' !== $nonce_protection ) {
			return;
		}

		if ( in_array( $post_type, $tc_restricted_types, true ) || ( '1' === $nonce_protection && in_array( $post_type, $post_types, true ) ) ) {
			$lesson->section = __( 'Tin Canny Settings', 'uncanny-learndash-reporting' );
		}
		if ( in_array( $post_type, $tc_restricted_types ) ) {
			$lesson->setting              = 'Restrict Mark Complete';
			$lesson->setting->type        = 'select';
			$lesson->setting->option      = [ 'Use Global Setting', __( 'Use Global Setting', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option      = [ 'No', __( 'Always enabled', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option      = [ 'Yes', __( 'Disabled until complete', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option      = [ 'hide', __( 'Hidden until complete', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option      = [ 'remove', __( 'Hidden and autocomplete', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option      = [ 'autoadvance', __( 'Hidden and autoadvance', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->description = __( 'Choose whether or not the Mark Complete button will be disabled until users complete all Tin Can modules on the page', 'uncanny-learndash-reporting' );

			$lesson->setting              = 'Completion Condition';
			$lesson->setting->type        = 'text';
			$lesson->setting->description = __( 'Comma separated Tin Canny verb(s). For result, you can enter the condition like <code>result > 80.</code>', 'uncanny-learndash-reporting' );
		}

		if ( in_array( $post_type, $post_types, true ) && '1' === $nonce_protection ) {
			$lesson->setting         = 'Protect SCORM/Tin Can Modules?';
			$lesson->setting->type   = 'select';
			$lesson->setting->option = [ 'Use Global Setting', __( 'Use Global Setting', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option = [ 'Yes', __( 'Yes', 'uncanny-learndash-reporting' ) ];
			$lesson->setting->option = [ 'No', __( 'No', 'uncanny-learndash-reporting' ) ];
		}
	}
}
