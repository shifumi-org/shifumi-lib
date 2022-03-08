<?php
/**
 * Admin Options Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\Admin;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Options {

	static private $DEFAULT_OPTION
		= [
			'default-lightbox-style'          => 'colorbox',
			'colorbox-transition'             => 'elastic',
			'colorbox-theme'                  => 'default',
			'nivo-transition'                 => 'fade',
			'height'                          => 800,
			'height_type'                     => 'px',
			'width'                           => 100,
			'width_type'                      => '%',
			'tinCanActivation'                => '1',
			'disableMarkComplete'             => '1',
			'labelMarkComplete'               => '',
			'nonceProtection'                 => '1',
			'disableDashWidget'               => '0',
			'disablePerformanceEnhancments'   => '0',
			'userIdentifierDisplayName'       => '1',
			'userIdentifierFirstName'         => '0',
			'userIdentifierLastName'          => '0',
			'userIdentifierUsername'          => '0',
			'userIdentifierEmail'             => '1',
			'enableTinCanReportFrontEnd'      => '0',
			'enablexapiReportFrontEnd'        => '0',
			'autocompleLessonsTopicsTincanny' => '0',
			'methodMarkCompleteForTincan'     => '0',
		];

	static private $OPTION = array();

	/**
	 * initialize
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 30 );
	}

	/**
	 * Return Options
	 *
	 * @access  public static
	 * @return  void
	 * @since   1.0.0
	 */
	public static function get_options() {
		self::$OPTION = get_option( SnC_TEXTDOMAIN );

		if ( ! self::$OPTION ) {
			// Set Default Option
			self::$OPTION = self::$DEFAULT_OPTION;
			update_option( SnC_TEXTDOMAIN, self::$DEFAULT_OPTION );
		}
		self::$OPTION = shortcode_atts( self::$DEFAULT_OPTION, self::$OPTION );

		return self::$OPTION;
	}

	/**
	 * Register Admin Menu
	 *
	 * @trigger admin_menu Action
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function register_admin_menu() {
		add_submenu_page( 'uncanny-learnDash-reporting', 'Settings', 'Settings', 'manage_options', 'snc_options', array(
			$this,
			'view_options_page'
		) );
	}

	/**
	 * admin_menu Page
	 *
	 * @trigger add_options_page
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function view_options_page() {
		if ( $_POST && wp_verify_nonce( $_POST['security'], 'snc-options' ) ) {

			unset( $_POST['security'] );
			unset( $_POST['submit'] );

			// set unchecked checkboxes
			( ! isset( $_POST['userIdentifierDisplayName'] ) ) ? $_POST['userIdentifierDisplayName'] = '0' : null;
			( ! isset( $_POST['userIdentifierFirstName'] ) ) ? $_POST['userIdentifierFirstName'] = '0' : null;
			( ! isset( $_POST['userIdentifierLastName'] ) ) ? $_POST['userIdentifierLastName'] = '0' : null;
			( ! isset( $_POST['userIdentifierUsername'] ) ) ? $_POST['userIdentifierUsername'] = '0' : null;
			( ! isset( $_POST['userIdentifierEmail'] ) ) ? $_POST['userIdentifierEmail'] = '0' : null;

			// Set default mark complete button options when capture data is off.
			$show_tincan_tables = absint( $_POST['tinCanActivation'] );
			if ( 0 == $show_tincan_tables ) {
				$_POST['disableMarkComplete']             = '0';
				$_POST['autocompleLessonsTopicsTincanny'] = '1';
			}

			self::$OPTION = $_POST;
			update_option( SnC_TEXTDOMAIN, self::$OPTION );

			/////////////
			// Store data for other uses
			////////////

			// Show TinCan Tables
			$show_tincan_tables = absint( $_POST['tinCanActivation'] );

			if ( 1 == $show_tincan_tables ) {
				$value = 'yes';
			}
			if ( 0 == $show_tincan_tables ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'show_tincan_reporting_tables', $value );
			}

			// Disable mark complete
			$disable_mark_complete = absint( $_POST['disableMarkComplete'] );

			if ( 1 == $disable_mark_complete ) {
				$value = 'yes';
			}
			if ( 0 == $disable_mark_complete ) {
				$value = 'no';
			}
			if ( 3 == $disable_mark_complete ) {
				$value = 'hide';
			}
			if ( 4 == $disable_mark_complete ) {
				$value = 'remove';
			}
			if ( 5 == $disable_mark_complete ) {
				$value = 'autoadvance';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'disable_mark_complete_for_tincan', $value );
			}

			// Disable mark complete
			$label_mark_complete = trim( $_POST['labelMarkComplete'] );

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'label_mark_complete_for_tincan', $label_mark_complete );
			}

			// Enable nonce protection
			$nonce_protection = absint( $_POST['nonceProtection'] );

			if ( 1 == $nonce_protection ) {
				$value = 'yes';
			}
			if ( 0 == $nonce_protection ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'tincanny_nonce_protection', $value );

				// Check if the user chose to protect the content.
				if ( $value == 'yes' ) {
					\uncanny_learndash_reporting\Boot::create_protection_htaccess();
				}

				// Check if the user chose not to protect the content.
				if ( $value == 'no' ) {
					\uncanny_learndash_reporting\Boot::delete_protection_htaccess();
				}
			}

			// Autocomplete Lessons and Topics even if Tin Canny content on page (Uncanny Toolkit Pro)
			$autocomple_lessons_topics_tincanny = absint( $_POST['autocompleLessonsTopicsTincanny'] );

			if ( 1 == $autocomple_lessons_topics_tincanny ) {
				$value = 'yes';
			}
			if ( 0 == $autocomple_lessons_topics_tincanny ) {
				$value = 'no';
			}
			
			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'autocomple_lessons_topics_tincanny', $value );
			}

			///////////////////////////////
			// Disable admin dashboard
			$disable_dash_widget = absint( $_POST['disableDashWidget'] );

			if ( 1 == $disable_dash_widget ) {
				$value = 'yes';
			}
			if ( 0 == $disable_dash_widget ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'tincanny_disableDashWidget', $value );
			}

			///////////////////////////////
			// Disable performance enhancments
			$disable_performance_enhancments = absint( $_POST['disablePerformanceEnhancments'] );

			if ( 1 == $disable_performance_enhancments ) {
				$value = 'yes';
			}
			if ( 0 == $disable_performance_enhancments ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'tincanny_disablePerformanceEnhancments', $value );
			}

			///////////////////////////////
			// Disable/enable Front-end reports
			$enableTinCanReportFrontEnd = absint( $_POST['enableTinCanReportFrontEnd'] );

			if ( 1 == $enableTinCanReportFrontEnd ) {
				$value = 'yes';
			}
			if ( 0 == $enableTinCanReportFrontEnd ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'tincanny_enableTinCanReportFrontEnd', $value );
			}

			///////////////////////////////
			// Disable/enable Front-end xAPI reports
			$enablexapiReportFrontEnd = absint( $_POST['enablexapiReportFrontEnd'] );

			if ( 1 == $enablexapiReportFrontEnd ) {
				$value = 'yes';
			}
			if ( 0 == $enablexapiReportFrontEnd ) {
				$value = 'no';
			}

			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'tincanny_enablexapiReportFrontEnd', $value );
			}

			///////////////////////////////
			// Select which user identifier(s) are shown in reports
			if ( current_user_can( 'manage_options' ) ) {
				$userIdentifierDisplayName = ( absint( $_POST['userIdentifierDisplayName'] ) ) ? 'yes' : 'no';
				update_option( 'tincanny_userIdentifierDisplayName', $userIdentifierDisplayName );

				$userIdentifierFirstName = ( absint( $_POST['userIdentifierFirstName'] ) ) ? 'yes' : 'no';
				update_option( 'tincanny_userIdentifierFirstName', $userIdentifierFirstName );

				$userIdentifierLastName = ( absint( $_POST['userIdentifierLastName'] ) ) ? 'yes' : 'no';
				update_option( 'tincanny_userIdentifierLastName', $userIdentifierLastName );

				$userIdentifierUsername = ( absint( $_POST['userIdentifierUsername'] ) ) ? 'yes' : 'no';
				update_option( 'tincanny_userIdentifierUsername', $userIdentifierUsername );

				$userIdentifierEmail = ( absint( $_POST['userIdentifierEmail'] ) ) ? 'yes' : 'no';
				update_option( 'tincanny_userIdentifierEmail', $userIdentifierEmail );
			}
			
			///////////////////////////////
			// Enable compatibility mode 
			$methodMarkCompleteForTincan = absint( $_POST['methodMarkCompleteForTincan'] );
			
			if ( 1 == $methodMarkCompleteForTincan ) {
				$value = 'old';
			}
			if ( 0 == $methodMarkCompleteForTincan ) {
				$value = 'new';
			}
			
			if ( current_user_can( 'manage_options' ) ) {
				update_option( 'method_mark_complete_for_tincan', $value );
			}
		} else {
			self::$OPTION = self::get_options();
		}

		$nivo_transitions = \TINCANNYSNC\Shortcode::$nivo_transitions;

		include_once( SnC_PLUGIN_DIR . 'views/admin_options.php' );
	}
}
