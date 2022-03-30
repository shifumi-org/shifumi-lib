<?php
/**
 * Handling the classic or builder route.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Controller;

/**
 * Handling the switcher buttons and all the logic
 * Class Switcher
 *
 * @package LearnDash_Certificate_Builder\Component
 */
class Switcher {
	const KEY         = 'ld_certificate_builder_on';
	const OLD_CONTENT = 'ld_certificate_builder_old_content';
	/**
	 * The certificate post type slug
	 *
	 * @var string
	 */
	private $post_type_slug;

	/**
	 * Switcher constructor.
	 *
	 * @param string $post_type_slug The certificate post type slug.
	 */
	public function __construct( $post_type_slug ) {
		$this->post_type_slug = $post_type_slug;
		// Add a button to switch the builder and classic.
		add_action( 'edit_form_after_title', array( $this, 'add_switch_button' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'wp_ajax_use_certificate_builder', array( $this, 'switch_to_builder' ) );
	}

	/**
	 * Ajax endpoint
	 */
	public function switch_to_builder() {
		// phpcs:ignore
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : false;
		if ( false === $nonce || ! wp_verify_nonce( $nonce, 'use_certificate_builder' ) ) {
			wp_send_json_error();
		}

		// add a flag.
		$post_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : false;
		if ( false === $post_id ) {
			wp_send_json_error();
		}
		if ( 0 === $post_id ) {
			// this mean autosave is off.
			$post_id = wp_insert_post(
				array(
					'post_status' => 'draft',
					'post_type'   => learndash_get_post_type_slug( 'certificate' ),
				)
			);
		}
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			wp_send_json_error();
		}
		// cache the old content first.
		update_post_meta( $post_id, self::OLD_CONTENT, $post->post_content );
		// gather some data.
		$block_attrs = wp_json_encode(
			array(
				'font' => 'dejavusanscondensed',
			)
		);

		// add the template.
		$post->post_content = '<!-- wp:learndash/ld-certificate-builder ' . $block_attrs . ' -->
<div class="wp-block-learndash-ld-certificate-builder alignfull"></div>
<!-- /wp:learndash/ld-certificate-builder -->';
		wp_update_post( $post );
		update_post_meta( $post_id, self::KEY, 1 );
		wp_send_json_success(
			array(
				'url' => get_edit_post_link( $post_id, false ),
			)
		);
	}

	/**
	 * Add a button to switch to the builder.
	 * Doesn't check the flag here as it always untouch if the switch is on.
	 */
	public function add_switch_button() {
		global $current_screen;
		if ( $current_screen->id === $this->post_type_slug ) {
			$string = $this->maybe_gutenberg_disabled();
			if ( is_null( $string ) ) {
				?>
				<button type="button" id="switch-to-builder" class="button">
					<?php esc_html_e( 'Use Certificate Builder', 'learndash-certificate-builder' ); ?>
				</button>
				<?php
			} else {
				// we should show a notice for tellig them to enable gutenberg.
				// at this stage, just output the html directly.
				?>
				<div class="notice notice-warning settings-error is-dismissible">
					<p><?php echo esc_html( $string ); ?></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Check if the gutenberg can be enable
	 *
	 * @return bool
	 */
	protected function maybe_gutenberg_disabled() {
		/**
		 * For now, tu complete turn off gutenberg, we have to disable and remove many hooks for frontend,
		 * so mostly need to check if the plugin disable gutenberg enabled
		 */
		$conflict_list = array(
			'disable-gutenberg/disable-gutenberg.php',
			'classic-editor/classic-editor.php',
		);
		// translators: Plugin name.
		$string = esc_html__( 'Leanrdash LMS: Certificate Builder require Gutenberg for functioning. We\'ve detected a conflict with the following plugin(s): %s. Please disable those and return to this page to continue.' );
		$catch  = array();
		foreach ( $conflict_list as $cp ) {
			if ( is_plugin_active( $cp ) ) {
				$info = get_plugin_data( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $cp );
				if ( is_array( $info ) && count( $info ) ) {
					$catch[] = $info['Name'];
				}
			}
		}

		if ( count( $catch ) ) {
			return sprintf( $string, implode( ', ', $catch ) );
		}

		return null;
	}

	/**
	 * Enqueue the script to switch from classic editor into builder.
	 */
	public function enqueue_script() {
		global $current_screen;
		if ( ! is_object( $current_screen ) ) {
			return;
		}
		if ( $this->post_type_slug !== $current_screen->id ) {
			return;
		}

		if ( $this->maybe_gutenberg_disabled() ) {
			// this mean the gutenberg is turn off somehow.
			return;
		}

		wp_register_script(
			'learndash-certificate-builder-switcher',
			learndash_certificate_builder_asset_url( '/scripts/switcher.js' ),
			array( 'jquery' ),
			time(),
			true
		);
		wp_localize_script(
			'learndash-certificate-builder-switcher',
			'ld_certificate_builder_switcher',
			array(
				'nonce' => wp_create_nonce( 'use_certificate_builder' ),
			)
		);
		wp_enqueue_script( 'learndash-certificate-builder-switcher' );
	}
}
