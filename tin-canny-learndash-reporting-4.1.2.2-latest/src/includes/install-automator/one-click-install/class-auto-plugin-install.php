<?php

namespace uncanny_learndash_reporting;

/**
 * Class Auto_Plugin_Install
 *
 * Automatically downloads and installs a plugin via link, button, or install call
 *
 * @since 4.0
 */
class Auto_Plugin_Install {

	/**
	 * @var bool
	 */
	public static $admin_ajax_created = false;

	/**
	 * Class constructor.
	 */
	public function __construct() {
	}

	/**
	 * Wrapper for set_time_limit to see if it is enabled.
	 *
	 * @param int $limit Time limit.
	 */
	public static function set_time_limit( $limit = 0 ) {

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( $limit ); // @codingStandardsIgnoreLine
		}

	}

	/**
	 *
	 */
	public function create_ajax() {

		if ( false === self::$admin_ajax_created ) {

			add_action( 'admin_footer', array( $this, 'admin_footer_auto_plugin_install' ) );

			// Write our JS below here.
			add_action( 'wp_ajax_auto_plugin_install', array( $this, 'wp_ajax_auto_plugin_install' ) );

			self::$admin_ajax_created = true;

		}

	}

	/**
	 *
	 */
	function admin_footer_auto_plugin_install() { ?>
	
		<script type="text/javascript">
			function autoPluginInstall(el) {

				let data = {
					'action': 'auto_plugin_install',
					'data': el.dataset
				};

				el.disabled = true;
				let $button = jQuery(el);

				$button.parent().find('.auto-plugin-install-loader').show();

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php.
				jQuery.post(ajaxurl, data, function (response) {
					$button.parent().find('.auto-plugin-install-loader').hide();
					response = JSON.parse(response);
					$button.parent().find('.auto-plugin-install-message').text(response.message);

					if (response.success) {
						if (el.dataset.redirect) {
							location.href = el.dataset.redirect;
						} else {
							location.reload();
						}
					}
				});
			}
		</script> 
		<?php
	}

	/**
	 *
	 */
	public function wp_ajax_auto_plugin_install() {

		$response = (object) array(
			'success' => null,
			'message' => '',
		);

		$data = $this->get_http_requests_args(
			'data',
			INPUT_POST,
			array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_REQUIRE_ARRAY,
			)
		);

		if ( empty( $data['action'] ) || empty( $data['slug'] ) || empty( $data['name'] ) ) {
			$response->success = false;
			$response->message = esc_html__( 'Please reload the page and try again.', 'uncanny-one-click-installer' );
			echo json_encode( $response );
			wp_die();
		}

		$action = $data['action'];
		if ( 'install' === $action ) {
			$plugin_info = $this->get_plugin_info( $data['slug'] );
			if ( $plugin_info->is_active ) {
				$response->success = false;
				$response->message = sprintf( esc_html__( '%s is already installed and active.', 'uncanny-one-click-installer' ), $plugin_info->name );
				$response->data    = $plugin_info;
				echo json_encode( $response );
				wp_die();
			} elseif ( $plugin_info->is_installed ) {
				$action = 'activate';
			} else {
				$installation = $this->install( $plugin_info->download_link );
				echo json_encode( $installation );
				wp_die();
			}
		}

		if ( 'activate' === $action ) {
			$plugin_info = $this->get_plugin_info( $data['slug'] );
			if ( $plugin_info->is_active ) {
				$response->success = false;
				$response->message = sprintf( esc_html__( '%s is already installed and active.', 'uncanny-one-click-installer' ), $plugin_info->name );
				$response->data    = $plugin_info;
				echo json_encode( $response );
				wp_die();
			} elseif ( false === $plugin_info->is_installed && false === $plugin_info->is_active ) {
				$installation = $this->install( $plugin_info->download_link );
				echo json_encode( $installation );
				wp_die();
			} else {
				$installation = $this->install( $plugin_info->download_link, true, $plugin_info->plugin_basename );
				echo json_encode( $installation );
				wp_die();
			}
		}

		$response->success = false;
		$response->message = esc_html__( 'Please reload the page and try again.', 'uncanny-one-click-installer' );
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Get the requests http args.
	 */
	protected function get_http_requests_args( $variable = null, $type = INPUT_GET, $flags = array() ) {

		if ( empty( $flags ) ) {
			$flags = array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			);
		}
		/*
		 * View input types: https://www.php.net/manual/en/function.filter-input.php
		 * View flags at: https://www.php.net/manual/en/filter.filters.sanitize.php
		 */
		$args = array( $variable => $flags );
		$val  = filter_input_array( $type, $args );

		return isset( $val[ $variable ] ) ? $val[ $variable ] : array();

	}

	/**
	 * @param $plugin_slug
	 *
	 * @return false|object
	 */
	public function get_plugin_info( $plugin_slug ) {

		// Create a empty array with variable name different based on plugin slug.
		$transient_name = 'auto_install_plugin_info_' . $plugin_slug;

		/**
		 * Check if transient with the plugin data exists
		 */
		$org_plugin_info = get_transient( $transient_name );

		if ( empty( $org_plugin_info ) ) {

			/**
			 * Connect to WordPress.org using plugins_api
			 */
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$org_plugin_info = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug ) );

			// Check for errors with the data returned from WordPress.org.
			if ( is_wp_error( $org_plugin_info ) || ! $org_plugin_info ) {
				delete_transient( $transient_name );

				return false;
			}

			// Set a transient with the plugin data.
			// Use Options API with auto update cron job in next version.
			set_transient( $transient_name, $org_plugin_info, 24 * HOUR_IN_SECONDS );

		}

		// clean up the data object so its easier to view.
		$clean_plugin_info = (object) array(
			'name'          => $org_plugin_info->name,
			'slug'          => $org_plugin_info->slug,
			'version'       => $org_plugin_info->version,
			'author'        => $org_plugin_info->author,
			'requires'      => $org_plugin_info->requires,
			'tested'        => $org_plugin_info->tested,
			'requires_php'  => $org_plugin_info->requires_php,
			'download_link' => $org_plugin_info->download_link,
		);

		$is_plugin = $this->is_plugin( $org_plugin_info );

		// Is plugin active.
		$clean_plugin_info->is_active = $is_plugin->active;
		// Is plugin installed.
		$clean_plugin_info->is_installed = $is_plugin->installed;
		// Relative path to plugin.
		$clean_plugin_info->plugin_basename = $is_plugin->plugin_basename;

		return $clean_plugin_info;
	}

	/**
	 * @param $org_plugin_info
	 *
	 * @return false|object
	 */
	public function is_plugin( $org_plugin_info ) {

		$is_plugin = (object) array(
			'active'          => false,
			'installed'       => false,
			'plugin_basename' => '',
		);

		// Get all plugin paths and slugs on site.
		$all_plugins = array();

		$plugin_info = get_site_transient( 'update_plugins' );

		if ( isset( $plugin_info->no_update ) ) {
			foreach ( $plugin_info->no_update as $plugin ) {
				$all_plugins[] = (object) array(
					'slug'   => $plugin->slug,
					'plugin' => $plugin->plugin,
				);
			}
		}

		if ( isset( $plugin_info->response ) ) {
			foreach ( $plugin_info->response as $plugin ) {
				$all_plugins[] = (object) array(
					'slug'   => $plugin->slug,
					'plugin' => $plugin->plugin,
				);
			}
		}

		if ( is_wp_error( $org_plugin_info ) || ! $org_plugin_info ) {
			return false;
		}

		foreach ( $all_plugins as $plugin ) {
			if ( $org_plugin_info->slug === $plugin->slug ) {
				$is_plugin->active = is_plugin_active( $plugin->plugin );
				$plugin_path       = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin->plugin;
				if ( file_exists( $plugin_path ) ) {
					$is_plugin->installed       = true;
					$is_plugin->plugin_basename = $plugin->plugin;
				} else {
					$is_plugin->active = false;
				}
			}
		}

		return $is_plugin;

	}

	/**
	 * @param string $plugin_download_link
	 * @param false  $activate_only
	 * @param string $plugin_basename
	 *
	 * @return object
	 */
	public function install( $plugin_download_link = '', $activate_only = false, $plugin_basename = '' ) {

		// Setup return object.
		$process = (object) array(
			'success' => false,
			'code'    => '',
			'message' => '',
		);

		// get needed classes to run install.
		$require_upgrader = $this->get_file( 'PluginSilentUpgrader.php', 'includes' );
		$require_skin     = $this->get_file( 'PluginSilentUpgraderSkin.php', 'includes' );

		if ( false === $require_upgrader->success ) {
			$process->success = false;
			$process->code    = 'required_files_not_found';
			$process->message = sprintf( esc_html__( 'Could not install plugin. %s', 'uncanny-one-click-installer' ), $require_upgrader->message );

			return $process;
		}

		if ( false === $require_skin->success ) {
			$process->success = false;
			$process->code    = 'required_files_not_found';
			$process->message = sprintf( esc_html__( 'Could not install plugin. %s', 'uncanny-one-click-installer' ), $require_skin->message );

			return $process;
		}

		// Check user capability.
		if ( ! current_user_can( 'install_plugins' ) ) {
			$process->success = false;
			$process->code    = 'permissions_install_plugins';
			$process->message = esc_html__( 'Could not install plugin. You do not have permission to install plugins.', 'uncanny-one-click-installer' );

			return $process;
		}

		// Determine whether file modifications are allowed.
		if ( ! wp_is_file_mod_allowed( __NAMESPACE__ . '_auto_plugin_install_can_install' ) ) {
			$process->success = false;
			$process->code    = 'permissions_file_mod_allowed';
			$process->message = esc_html__( 'Could not install plugin. You do not have permission to modify files.', 'uncanny-one-click-installer' );

			return $process;
		}

		// Check if a plugin url was passed.
		if ( empty( $plugin_download_link ) ) {
			$process->success = false;
			$process->code    = 'empty_$plugin_download_link';
			$process->message = esc_html__( 'Could not install plugin. A plugin url was not received. Please Reload the page.', 'uncanny-one-click-installer' );

			return $process;
		}

		// Check for file system permissions.
		if ( false === $this->has_credentials() ) {
			$process->success = false;
			$process->code    = 'not_has_credentials';
			$process->message = esc_html__( 'Could not install plugin. File system credentials could not be verified. Please Reload the page.', 'uncanny-one-click-installer' );

			return $process;
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		if ( false === $activate_only ) {

			// Create the plugin upgrader with our custom skin.
			$installer = new PluginSilentUpgrader( new PluginSilentUpgraderSkin() );

			// Error check.
			if ( ! method_exists( $installer, 'install' ) ) {
				$process->success = false;
				$process->code    = 'not_method_exits_install';
				$process->message = esc_html__( 'Could not install plugin. The installer failed to initialize.', 'uncanny-one-click-installer' );

				return $process;
			}

			// Sanitize.
			$plugin_download_link = esc_url_raw( $plugin_download_link );
			$installer->install( $plugin_download_link );

			// Flush the cache and return the newly installed plugin basename.
			wp_cache_flush();

			$plugin_basename = $installer->plugin_info();

			if ( empty( $plugin_basename ) ) {
				//
				/* The may happen for several reasons.
				 * 1. The plugin zip url is invalid but this was checked already when creating the button
				 * 2. The plugin is already installed
				 * 3. The plugin is already installed and active
				 */
				$process->success = false;
				$process->code    = 'no_plugin_info';
				$process->message = esc_html__( 'Could not install plugin. The download link failed. Please Reload the page.', 'uncanny-one-click-installer' );

				return $process;
			}
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $plugin_basename, '', false, true );

		if ( is_wp_error( $activated ) ) {
			$process->success = false;
			$process->code    = 'not_activated';
			$process->message = esc_html__( 'Plugin has installed but failed to activate. Please activate manually on the plugins page.', 'uncanny-one-click-installer' );

			return $process;
		}

		if ( false !== $activate_only ) {
			$process->success = true;
			$process->code    = 'activated';
			$process->message = esc_html__( 'Plugin has activated successfully.', 'uncanny-one-click-installer' );

			return $process;
		}

		$process->success = true;
		$process->code    = 'activated_installed';
		$process->message = esc_html__( 'Plugin has installed and activated successfully.', 'uncanny-one-click-installer' );

		return $process;
	}

	/**
	 * Require file once only
	 *
	 * @param bool|string $file_name
	 * @param bool|string $sub_directory
	 * @param bool|string $base_file false or The full path and filename of the file with symlinks resolved.
	 *
	 * @return object
	 */
	public function get_file( $file_name = false, $sub_directory = false, $base_file = false ) {

		$file = (object) array(
			'success' => null,
			'message' => '',
		);
		if ( false === $file_name ) {
			$file->success = false;
			$file->message = __( 'Auto_Plugin_Install::file_require_once() was called <strong>incorrectly</strong>. $file_name was not passed.' );

			return $file;
		}

		if ( false === $sub_directory ) {
			$file->success = false;
			$file->message = __( 'Auto_Plugin_Install::file_require_once() was called <strong>incorrectly</strong>. Directory was not passed.' );

			return $file;
		}

		if ( false === $base_file ) {
			$base_file = __FILE__;
		}

		$asset_uri = dirname( $base_file ) . DIRECTORY_SEPARATOR . $sub_directory . DIRECTORY_SEPARATOR . $file_name;

		if ( ! file_exists( $asset_uri ) ) {
			$file->success = false;
			$file->message = sprintf(
				__( 'Auto_Plugin_Install::file_require_once() did not find <strong>file not found</strong>. %1$s' ),
				$asset_uri
			);

			return $file;
		}

		$file->success = true;

		require_once $asset_uri;

		return $file;
	}

	/**
	 * @return array|bool
	 */
	public function has_credentials() {

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				array(),
				admin_url( 'admin.php' )
			)
		);

		return request_filesystem_credentials( $url, '', false, false, null );

	}

	/**
	 * @param $plugin_slug
	 *
	 * @return false|string
	 */
	public function button( $plugin_slug, $redirect = null ) {

		// Added this check to ensure the plugin won't break accidentally when called outside wp-admin.
		if ( ! is_admin() ) {
			return;
		}

		if ( empty( $plugin_slug ) ) {
			return esc_html__( 'Add a plugin slug', 'uncanny-one-click-installer' );
		}

		$plugin_info = $this->get_plugin_info( $plugin_slug );

		if ( false === $plugin_info ) {
			return esc_html__( 'Plugin not found on wordpress.org', 'uncanny-one-click-installer' );
		}

		$action   = esc_attr( 'install' );
		$disabled = '';

		if ( 'uncanny-automator' === (string) $plugin_slug ) {
			$button_text = esc_html__( 'Install Uncanny Automator', 'uncanny-one-click-installer' );
		} else {
			$button_text = sprintf( esc_html__( 'Install %s', 'uncanny-one-click-installer' ), $plugin_info->name );
		}

		$button_text = apply_filters( 'uncanny_one_click_install_plugin_initial_text', $button_text, $plugin_info );

		if ( $plugin_info->is_active ) {
			$action      = esc_attr( '' );
			$disabled    = esc_attr( 'disabled="disabled"' );
			$button_text = sprintf( esc_html__( '%s is active', 'uncanny-one-click-installer' ), $plugin_info->name );
			$button_text = apply_filters( 'uncanny_one_click_install_plugin_active_text', $button_text, $plugin_info );
		} elseif ( $plugin_info->is_installed ) {
			$action = esc_attr( 'activate' );
			if ( 'uncanny-automator' === (string) $plugin_slug ) {
				$button_text = esc_html__( 'Activate Uncanny Automator', 'uncanny-one-click-installer' );
			} else {
				$button_text = sprintf( esc_html__( 'Activate %s', 'uncanny-one-click-installer' ), $plugin_info->name );
			}
			$button_text = apply_filters( 'uncanny_one_click_install_plugin_installed_text', $button_text, $plugin_info );
		}

		// return button html.
		ob_start();

		?>
		<style>
			/* Center the loader */
			.auto-plugin-install-loader {
				width: 20px;
				height: 20px;
				border: 4px solid white;
				border-radius: 50%;
				border-top: 4px solid #3498db;
				-webkit-animation: spin 2s linear infinite;
				animation: spin 2s linear infinite;
				display: none;
			}

			@-webkit-keyframes spin {
				0% {
					-webkit-transform: rotate(0deg);
				}
				100% {
					-webkit-transform: rotate(360deg);
				}
			}

			@keyframes spin {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}
		</style>

		<div class="auto-plugin-install">
			<?php 
				$button_class = apply_filters(
					'uncanny_one_click_install_button_class',
					array(
						'uoc-generate-button',
						'auto-plugin-install-button'
					),
					$plugin_info
				);
				$button_class = implode(' ', $button_class );
			?>
			<button class="<?php echo esc_attr( $button_class ); ?>"
					onclick="autoPluginInstall(this)"
					data-action="<?php echo $action; ?>"
					data-slug="<?php echo $plugin_slug; ?>"
					data-name="<?php echo $plugin_info->name; ?>"
					data-redirect="<?php echo $redirect; ?>"
				<?php echo $disabled; ?>>
				<?php echo $button_text; ?>
				<div class="auto-plugin-install-loader"></div>
			</button>
			<p class="auto-plugin-install-message"></p>
		</div>

		<?php

		return ob_get_clean();

	}
}
