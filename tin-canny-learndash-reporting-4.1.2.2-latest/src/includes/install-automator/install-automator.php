<?php

namespace uncanny_learndash_reporting;

use uncanny_learndash_reporting\Auto_Plugin_Install as Automator_Installer;


class Install_Automator {

	const ROUTE_NAMESPACE = 'uncanny_reporting/v1';
	/**
	 * Property installer.
	 *
	 * @var uncanny_learndash_reporting\Auto_Plugin_Install $installer The installer class.
	 */
	protected $installer;

	public function __construct() {

		add_action( 'admin_head', array( $this, 'add_small_css_and_js' ), 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );

		add_action( 'admin_menu', array( $this, 'menu_uncanny_automator' ), 200 );

		// Evaluate the visibility of the "Try Automator" item
		add_filter( 'ultc_admin_sidebar_try_automator_add', array( $this, 'try_automator_evaluate_visibility' ), 100 );

		// Modify the inner html of the "Try Automator! admin item"
		add_filter( 'ultc_admin_sidebar_try_automator_inner_html', array( $this, 'try_automator_add_x_icon' ) );

		$this->register_one_click_install();

	}

	/**
	 * Loads the one click installer.
	 *
	 * @return void
	 */
	public function register_one_click_install() {

		if ( ! class_exists( 'uncanny_learndash_reporting\Auto_Plugin_Install' ) ) {

			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'one-click-install/class-auto-plugin-install.php';

			$this->installer = new Automator_Installer();

			add_action( 'admin_init', array( $this, 'setup_installer' ), 99 );

		}

	}

	/**
	 * Callback method to `admin_enqueue_scripts`.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		wp_register_style( 'install-automator', plugins_url( 'assets/css/install-automator.css', __FILE__ ), false, '1.0.0' );
		wp_register_style( 'try-automator', plugins_url( 'assets/css/try-automator.css', __FILE__ ), false, '1.1.0' );

		wp_register_script( 'install-automator', plugins_url( 'assets/js/recipe-simulator.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'try-automator', plugins_url( 'src/assets/admin/js/dist/try-automator.js', UO_REPORTING_FILE ), array( 'jquery' ), '1.0.0', true );
	}

	/**
	 * Add UncannyAutomator page.
	 *
	 * @return void
	 * @since 4.4
	 */
	public function menu_uncanny_automator() {

		if ( defined( 'AUTOMATOR_BASE_FILE' ) ) {
			return;
		}

		if ( ! apply_filters( 'ultc_admin_sidebar_try_automator_add', true ) ) {
			return;
		}

		/* translators: Trademarked term */
		$menu_item_text = apply_filters( 'ultc_admin_sidebar_try_automator_text', sprintf( __( 'Try %s!', 'uncanny-learndash-reporting' ), 'Automator' ) );

		$menu_item_html = '<span class="ultc-sidebar-featured-item">' . apply_filters( 'ultc_admin_sidebar_try_automator_inner_html', '<span class="ultc-sidebar-featured-item__text">' . esc_html( $menu_item_text ) . '</span><span class="ultc-sidebar-featured-item__tag">' . __( 'New', 'uncanny-learndash-reporting' ) . '</span>' ) . '</span>';

		add_submenu_page(
			'uncanny-learnDash-reporting',
			$menu_item_text,
			$menu_item_html,
			'manage_options',
			'tincanny-install-automator',
			array(
				$this,
				'admin_page_uncanny_automator',
			)
		);

	}

	/**
	 * Method setup_installer.
	 *
	 * @return void
	 */
	public function setup_installer() {

		if ( $this->get_installer() && $this->get_installer() instanceof \uncanny_learndash_reporting\Auto_Plugin_Install ) {

			$this->get_installer()->create_ajax();

		}

	}

	/**
	 * Method get_installer.
	 *
	 * @return uncanny_learndash_reporting\Auto_Plugin_Install The installer.
	 */
	public function get_installer() {

		return $this->installer;

	}

	/**
	 * Method get_image_url.
	 *
	 * @param string $image the image filename.
	 *
	 * @return string The image url.
	 */
	public function get_image_url( $image ) {

		return esc_url( plugins_url( 'assets/images/' . sanitize_file_name( $image ), __FILE__ ) );

	}

	/**
	 * Method admin_page_uncanny_automator.
	 *
	 * Displays the installer template.
	 *
	 * @return void
	 */
	public function admin_page_uncanny_automator() {

		wp_enqueue_style( 'install-automator' );
		wp_enqueue_style( 'try-automator' );
		// API data
		$api_setup = array(
			'root'  => esc_url_raw( rest_url() . self::ROUTE_NAMESPACE . '/' ),
			'nonce' => \wp_create_nonce( 'wp_rest' ),
		);

		wp_localize_script( 'install-automator', 'ultcRestApiSetup', $api_setup );
		wp_enqueue_script( 'install-automator' );
		wp_enqueue_script( 'try-automator' );

		wp_enqueue_script( 'install-automator-visibility' );

		include_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'views/template-install-automator.php';

	}

	/**
	 * Filters the variable that defines the visibility of the "Try Automator" item
	 *
	 * @since 3.5.4
	 */
	public static function try_automator_evaluate_visibility( $visible ) {

		// Check if the item is visible.
		// If it's not, don't override the variable.
		if ( true === $visible ) {

			// Check if the user clicked the "X" button before.
			$visibility_option = get_option( '_uncanny_tin_canny_try_automator_visibility' );

			// Check if the user chose to hide it.
			if ( 'hide-forever' === $visibility_option ) {
				$visible = false;
			}
		}

		return $visible;

	}

	/**
	 * Filters the inner html of the "Try Automator" admin sidebar item
	 * to add the X icon to hide the item
	 *
	 * @param String $inner_html The inner HTML
	 *
	 * @return String             The inner HTML, modified.
	 * @since 3.5.4
	 */
	public function try_automator_add_x_icon( $inner_html ) {
		// Add the "X" icon
		$inner_html .= '<span class="ultc-sidebar-featured-item__close" id="ultc-sidebar-try-automator-close"><span class="ultc-sidebar-featured-item__close-icon"></span></span>';

		return $inner_html;
	}

	public function add_small_css_and_js() {

		?>
		<style>
			/* General CSS. Must be in every page. */
			.ultc-sidebar-featured-item__tag {
				background: #6ac45a;
				color: #fff;
				font-size: 11px;
				font-weight: 600;
				line-height: 1;
				padding: 1px 3px;
				border-radius: 150px;
			}

			.ultc-sidebar-featured-item__text {
				margin-right: 5px;
			}

			.ultc-sidebar-featured-item-container {
				background: #232323 !important;
			}

			<?php if ( 'tincanny-install-automator' === filter_input( INPUT_GET, 'page' ) ) { ?>
				/* Hide notices in one click installer template. */
				.wp-core-ui .notice,
				.notice#uap-review-banner,
				#wpbody-content > .updated,
				.uo-install-automator__header .error,
				.uo-install-automator__header .notice {
					display: none !important;
				}	
			<?php } ?>
		</style>
		<script>
			jQuery(document).ready(function ($) {
				'use strict';
				$(".tin-canny-reporting_page_tincanny-install-automator .ultc-sidebar-featured-item").parent().addClass("ultc-sidebar-featured-item-container");
			});
		</script>
		<?php

	}
}
