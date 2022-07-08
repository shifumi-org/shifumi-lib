<?php

namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class AdminMenu
 *
 * This class should only be used to inherit classes
 *
 * @package uncanny_learndash_reporting
 */
class Licensing {

	/**
	 * The name of the licensing page
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $page_name = null;

	/**
	 * The slug of the licensing page
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $page_slug = null;

	/**
	 * The slug of the parent that the licensing page is organized under
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $parent_slug = null;

	/**
	 * The URL of store powering the plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $store_url = null;

	/**
	 * The Author of the Plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $item_name = null;

	/**
	 * The Author of the Plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $author = null;

	/**
	 * Is this a beta version release
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $beta = null;


	/**
	 * Create Plugin admin menus, pages, and sub-pages
	 *
	 * @since 1.0.0
	 */
	public function add_licensing() {

		$this->error = $this->set_defaults();

		if ( true !== $this->error ) {

			// Create an admin notices with the error
			add_action( 'admin_notices', array( $this, 'licensing_setup_error' ) );

		} else {

			// include updater
			$updater = Config::get_include( 'EDD_SL_Plugin_Updater.php' );

			include_once( $updater );

			add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
			add_action( 'admin_menu', array( $this, 'license_menu' ), 60 );
			add_action( 'admin_init', array( $this, 'activate_license' ) );
			add_action( 'admin_init', array( $this, 'deactivate_license' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'admin_notices', array( $this, 'show_expiry_notice' ) );
			add_action( 'admin_notices', array( $this, 'tincanny_remind_to_add_license_notice_func' ) );
			add_action( 'uo_notify_admin_of_license_expiry_tincanny', array( $this, 'admin_notices_for_expiry' ) );
			//Add license notice
			add_action( 'after_plugin_row', array(
				$this,
				'plugin_row',
			), 10, 3 );
		}
	}

	/**
	 *
	 */
	public function admin_notices_for_expiry() {
		$license_data = $this->check_license( true );
	}

	/**
	 *
	 */
	public function show_expiry_notice() {
		$status = get_option( 'uo_reporting_license_status' ); // $license_data->license will be either "valid", "invalid", "expired", "disabled"
		if ( filter_has_var( INPUT_GET, 'page' ) && 'uncanny-reporting-license-activation' === filter_input( INPUT_GET, 'page' ) ) {
			return;
		}
		if ( empty( $status ) ) {
			return;
		}
		if ( 'expired' !== $status ) {
			return;
		}
		?>
		<div class="notice notice-error <?php if ( ! $this->is_uo_plugin_page() ) { ?>is-dismissible<?php } ?>">
			<p>
				<?php
				echo $this->expiry_message();
				?>
			</p>
		</div>
		<?php
	}

	/**
	 *
	 */
	public function tincanny_remind_to_add_license_notice_func() {
		$license_key    = get_option( 'uo_reporting_license_key' );
		$license_status = get_option( 'uo_reporting_license_status' ); // $license_data->license will be either "valid", "invalid", "expired", "disabled"
		if ( filter_has_var( INPUT_GET, 'page' ) && 'uncanny-reporting-license-activation' === filter_input( INPUT_GET, 'page' ) ) {
			return;
		}
		if ( ! empty( $license_key ) && ( 'valid' !== $license_status || 'expired' !== $license_status ) ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				echo $this->expiry_message();
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * @param $plugin_name
	 * @param $plugin_data
	 * @param $status
	 */
	public function plugin_row( $plugin_name, $plugin_data, $status ) {
		if ( $plugin_name !== 'tin-canny-learndash-reporting/tin-canny-learndash-reporting.php' ) {
			return;
		}
		$slug    = 'uncanny-learndash-reporting';
		$message = $this->expiry_message();

		if ( empty( $message ) ) {
			return;
		}
		if ( is_network_admin() ) {
			$active_class = is_plugin_active_for_network( $plugin_name ) ? ' active' : '';
		} else {
			$active_class = is_plugin_active( $plugin_name ) ? ' active' : '';
		}

		// Get the columns for this table so we can calculate the colspan attribute.
		$screen  = get_current_screen();
		$columns = get_column_headers( $screen );

		// If something went wrong with retrieving the columns, default to 3 for colspan.
		$colspan = ! is_countable( $columns ) ? 3 : count( $columns );

		echo '<tr class="plugin-update-tr' . $active_class . '" id="' . $slug . '-update" data-slug="' . $slug . '" data-plugin="' . $plugin_name . '">';
		echo '<td colspan="' . $colspan . '" class="plugin-update colspanchange">';
		echo '<div class="update-message notice inline notice-warning notice-alt">';
		echo '<p>';
		echo $message;
		echo '</p></div></td></tr>';

		// Apply the class "update" to the plugin row to get rid of the ugly border.
		echo "
				<script type='text/javascript'>
					jQuery('#$slug-update').prev('tr').addClass('update');
				</script>
				";
	}

	/**
	 * @return string
	 */
	public function expiry_message() {
		$license_data   = $this->check_license();
		$license_key    = get_option( 'uo_reporting_license_key' );
		$license_status = get_option( 'uo_reporting_license_status' ); // $license_data->license will be either "valid", "invalid", "expired", "disabled"
		$license_expiry = get_option( 'uo_reporting_license_expiry' );
		$message        = '';
		$renew_link     = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			$this->store_url . 'checkout/?edd_license_key=' . $license_key . '&download_id=4113&utm_medium=uo_tincanny&utm_campaign=plugins_page',
			__( 'Renew now', 'uncanny-learndash-reporting' )
		);

		if ( 'expired' === $license_status ) {
			$message .= sprintf(
				_x(
					'Your license for %s has expired on %s. %s to continue to receive updates and support.',
					'License expiry notice',
					'uncanny-learndash-reporting'
				),
				'<strong>Tin Canny Reporting for LearnDash</strong>',
				date( 'F d, Y', strtotime( $license_expiry ) ),
				$renew_link
			);
		} elseif ( empty( $license_key ) || ( 'valid' !== $license_status && 'expired' !== $license_status ) ) {
			$message .= sprintf(
				__( "%s your copy of %s to get access to automatic updates and support. Don't have a license key? Click %s to buy one.", 'uncanny-learndash-reporting' ),
				sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=uncanny-reporting-license-activation' ), __( 'Activate', 'uncanny-learndash-reporting' ) ),
				'<strong>Tin Canny Reporting for LearnDash</strong>',
				sprintf( '<a href="%s" target="_blank">%s</a>', 'https://www.uncannyowl.com/downloads/tin-canny-reporting/?utm_medium=uo_tincanny&utm_campaign=license_page#pricing', __( 'here', 'uncanny-learndash-reporting' ) )
			);
		}

		return $message;
	}

	/**
	 * @return bool
	 */
	public function is_uo_plugin_page() {

		return false;
	}

	/**
	 * Set all the defaults for the plugin licensing
	 *
	 * @return bool|string True if success and error message if not
	 * @since    1.0.0
	 * @access   private
	 *
	 */
	private function set_defaults() {

		if ( null === $this->page_name ) {
			$this->page_name = strtoupper( Config::get_prefix() ) . ' Licensing';
		}

		if ( null === $this->page_slug ) {
			$this->page_slug = Config::get_prefix() . '-licensing';
		}

		if ( null === $this->parent_slug ) {
			$this->parent_slug = false;
		}

		if ( null === $this->store_url ) {
			return 'Error: Licensed plugin store URL not set.';
		}

		if ( null === $this->item_name ) {
			return 'Error: Licensed plugin item name not set';
		}

		if ( null === $this->author ) {
			$this->author = 'Uncanny Owl';
		}

		if ( null === $this->beta ) {
			$this->beta = false;
		}

		return true;

	}

	/**
	 * Admin Notice to notify that the needed licencing variables have not been set
	 *
	 * @since    1.0.0
	 */
	public function licensing_setup_error() {

		?>
		<div class="notice notice-error is-dismissible">
			<p>There may be an issue with the configuration of Tin Canny Reporting for
				LearnDash.<br><?php echo $this->error; ?></p>
		</div>
		<?php

	}

	/**
	 * Calls the EDD SL Class
	 *
	 * @since    1.0.0
	 */
	function plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'uo_reporting_license_key' ) );

		// setup the updater
		new EDD_SL_Plugin_Updater( $this->store_url, UO_REPORTING_FILE, array(
				'version'   => UNCANNY_REPORTING_VERSION,
				'license'   => $license_key,
				'item_name' => $this->item_name,
				'author'    => $this->author,
				'beta'      => $this->beta,
			)
		);

	}

	/**
	 * Add Licensing menu and sub-page
	 *
	 * @since    1.0.0
	 */
	public function license_menu() {

		$parent_slug = 'uncanny-learnDash-reporting';
		// Create a menu page if there is no parent slug
		if ( ! $this->parent_slug ) {
			// TODO add_page_menu
		} else {
			//Create a sub menu page
			add_submenu_page( $parent_slug, $this->page_name, 'License activation', 'manage_options', $this->page_slug, array(
				$this,
				'license_page',
			) );
		}

	}

	/**
	 * Sub-page out put
	 *
	 * @since    1.0.0
	 */
	public function license_page() {

		$license_data = $this->check_license( true );

		$license = get_option( 'uo_reporting_license_key' );
		$status  = get_option( 'uo_reporting_license_status' ); // $license_data->license will be either "valid", "invalid", "expired", "disabled"

		// Check license status
		// Check license status
		$license_is_active = ( 'valid' === $status ) ? true : false;

		// CSS Classes
		$license_css_classes = array();

		if ( $license_is_active ) {
			$license_css_classes[] = 'tclr-license--active';
		}

		// Set links.
		$where_to_get_my_license = 'https://www.uncannyowl.com/plugin-frequently-asked-questions/?utm_medium=uo_tincanny&utm_campaign=license_page#licensekey';
		$buy_new_license         = 'https://www.uncannyowl.com/downloads/tin-canny-reporting/?utm_medium=uo_tincanny&utm_campaign=license_page#pricing';
		$knowledge_base          = menu_page_url( 'uncanny-tincanny-kb', false );

		include Config::get_template( 'admin-license.php' );
	}

	/**
	 * API call to activate License
	 *
	 * @since    1.0.0
	 */
	public function activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['uo_reporting_license_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( Config::get_prefix() . '_nonce', Config::get_prefix() . '_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			// Save license key
			$license = $_POST['uo_reporting_license_key'];
			update_option( 'uo_reporting_license_key', $license );

			// retrieve the license from the database
			$license = trim( get_option( 'uo_reporting_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ), // the name of our product in uo
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( $this->store_url, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// make sure the response came back okay
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'uncanny-learndash-reporting' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch ( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.', 'uncanny-learndash-reporting' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.', 'uncanny-learndash-reporting' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.', 'uncanny-learndash-reporting' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.', 'uncanny-learndash-reporting' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'uncanny-learndash-reporting' ), $this->item_name );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.', 'uncanny-learndash-reporting' );
							break;

						default :

							$message = __( 'An error occurred, please try again.', 'uncanny-learndash-reporting' );
							break;
					}

				}

			}


			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'admin.php?page=' . $this->page_slug );
				$redirect = add_query_arg( array(
					'sl_activation' => 'false',
					'message'       => urlencode( $message ),
				), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'uo_reporting_license_status', $license_data->license );
			delete_option( 'uo_reporting_license_last_checked' );

			wp_redirect( admin_url( 'admin.php?page=' . $this->page_slug ) );

			exit();

		}
	}

	/**
	 * API call to de-activate License
	 *
	 * @since    1.0.0
	 */
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['uo_reporting_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( Config::get_prefix() . '_nonce', Config::get_prefix() . '_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'uo_reporting_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ), // the name of our product in uo
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( $this->store_url, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'uncanny-learndash-reporting' );
				}

				$base_url = admin_url( 'admin.php?page=' . $this->page_slug );
				$redirect = add_query_arg( array(
					'sl_activation' => 'false',
					'message'       => urlencode( $message ),
				), $base_url );

				wp_redirect( $redirect );

				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ) {
				delete_option( 'uo_reporting_license_status' );
			}

			wp_redirect( admin_url( 'admin.php?page=' . $this->page_slug ) );
			exit();

		}
	}


	/**
	 * Load Scripts that are specific to the admin page
	 *
	 * @param string $hook Admin page being loaded
	 *
	 * @since 1.0
	 *
	 */
	function admin_scripts( $hook ) {

		/*
		 * Only load styles if the page hook contains the pages slug
		 *
		 * Hook can be either the toplevel_page_{page_slug} if its a parent  page OR
		 * it can be {parent_slug}_pag_{page_slug} if it is a sub page.
		 * Lets just check if our page slug is in the hook.
		 */
		if ( strpos( $hook, $this->page_slug ) !== false ) {
			// Load Styles for Licensing page located in general plugin styles
			wp_enqueue_style( 'tincanny-admin-tincanny-report-tab', Config::get_gulp_css( 'admin.tincan.report.tab' ), array(), UNCANNY_REPORTING_VERSION );
		}
	}


	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 *
	 * @since    1.0.0
	 */
	function admin_notices() {

		if ( isset( $_GET['page'] ) && $this->page_slug == $_GET['page'] ) {

			if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

				switch ( $_GET['sl_activation'] ) {

					case 'false':

						$message = urldecode( $_GET['message'] );

						?>
						<div class="notice notice-error">
							<p><?php echo $message; ?></p>
						</div>
						<?php

						break;

					case 'true':
					default:
						?>
						<div class="notice notice-success">
							<p><?php _e( 'License is activated.', 'uncanny-learndash-reporting' ); ?></p>
						</div>
						<?php
						break;

				}
			}
		}
	}

	/**
	 * API call to check if License key is valid
	 *
	 * The updater class does this for you. This function can be used to do something custom.
	 *
	 * @since    1.0.0
	 */
	public function check_license( $force_check = false ) {
		$last_checked = get_option( 'uo_reporting_license_last_checked' );
		if ( ! empty( $last_checked ) && false === $force_check ) {
			$datediff = time() - $last_checked;
			if ( $datediff < DAY_IN_SECONDS ) {
				return null;
			}
		}

		if ( true === $force_check ) {
			delete_option( 'uo_reporting_license_last_checked' );
		}

		$license = trim( get_option( 'uo_reporting_license_key' ) );
		if ( empty( $license ) ) {
			return new \stdClass();
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( $this->store_url, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ) {
			update_option( 'uo_reporting_license_status', $license_data->license );
			if ( 'lifetime' !== $license_data->expires ) {
				update_option( 'uo_reporting_license_expiry', $license_data->expires );
			} else {
				update_option( 'uo_reporting_license_expiry', date( 'Y-m-d H:i:s', mktime( 12, 59, 59, 12, 31, 2099 ) ) );
			}

			if ( 'lifetime' !== $license_data->expires ) {
				$expire_notification = new \DateTime( $license_data->expires, wp_timezone() );
				update_option( 'uo_reporting_license_expiry_notice', $expire_notification );
				// 1 hour after the license is schedule to expire.
				wp_schedule_single_event( $expire_notification->getTimestamp() + 3600, 'uo_notify_admin_of_license_expiry_tincanny' );

			}
		} else {
			update_option( 'uo_reporting_license_status', $license_data->license );
			// this license is no longer valid
		}
		update_option( 'uo_reporting_license_last_checked', time() );

		return $license_data;
	}

}
