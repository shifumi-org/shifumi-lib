<?php
/**
 * Everything should be init here
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder;

use LearnDash_Certificate_Builder\Controller\Certificate_Builder;

/**
 * Class Bootstrap
 *
 * @package LearnDash_Certificate_Builder
 */
class Bootstrap {

	/**
	 * Contain the controller stack, so we don't need to init again
	 *
	 * @var array
	 */
	public $pool = array();

	/**
	 * Initial function, everything should be start here
	 */
	public function init() {
		if ( class_exists( '\SFWD_LMS' ) ) {
			$this->pool[ Certificate_Builder::class ] = new Certificate_Builder();
		} else {
			// going to add an admin notice.
			add_action( 'admin_notices', array( $this, 'requirement_notice' ) );
		}
	}

	/**
	 * Register all the assets that will be use in this plugin, the controllers will enqueue it later
	 */
	public function register_assets() {
		if ( ! class_exists( '\SFWD_LMS' ) ) {
			return;
		}
		$this->get_controller( Certificate_Builder::class )->register_block();
	}

	/**
	 * If the learndash core doesn't activated, then we show this message.
	 */
	public function requirement_notice() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
			<?php
				// translators: Learndash website.
				printf( esc_html__( 'LearnDash LMS - Certificate Builder Add-on requires the following plugin(s) to be active: %s', 'learndash-certificate-builder' ), '<a target="_blank" href="https://www.learndash.com/">LearnDash LMS</a>' );
			?>
			</p>
		</div>
		<?php
	}

	/**
	 * Load text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'learndash-certificate-builder', false, basename( dirname( __DIR__ ) ) . '/languages/' );
	}

	/**
	 * This function will check if a controller class has been initialed in the pool or not, if not, init and return
	 *
	 * @param string $name Should be the class name.
	 *
	 * @return false|mixed
	 */
	private function get_controller( $name ) {
		if ( isset( $this->pool[ $name ] ) ) {
			return $this->pool[ $name ];
		}

		if ( class_exists( $name ) ) {
			$this->pool[ $name ] = new $name();

			return $this->pool[ $name ];
		}

		return false;
	}
}
