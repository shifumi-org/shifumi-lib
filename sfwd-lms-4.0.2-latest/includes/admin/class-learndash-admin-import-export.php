<?php
/**
 * LearnDash Settings Page Add-ons.
 *
 * @since 2.6.0
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Learndash_Admin_Import_Export' ) ) {

	/**
	 * Class to create Addons list table.
	 *
	 * @since 2.6.0
	 */
	class Learndash_Admin_Import_Export {

		/**
		 * List table constructor.
		 *
		 * @since 2.6.0
		 */
		public function __construct() {
		}

		/**
		 * Show the Import/Export module UI.
		 *
		 * @since 2.6.0
		 */
		public function show() {
		}
		// End of functions.
	}
	// End of Class.
}
