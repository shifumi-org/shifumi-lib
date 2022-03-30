<?php
/**
 * Add a font manager to LearnDash->Certificate for managing custom fonts
 *
 * @file
 * @package LearnDash Certificate Builder
 */

namespace LearnDash_Certificate_Builder\Controller;

use Certificate_Builder\Traits\IO;
use Mpdf\Cache;
use Mpdf\Fonts\FontCache;
use Mpdf\MpdfException;
use Mpdf\TTFontFileAnalysis;

/**
 * Class Fonts_Manager
 *
 * @package LearnDash_Certificate_Builder\Controller
 */
class Fonts_Manager extends \LearnDash_Settings_Page {

	use IO;

	const OPTION_NAME = 'learndash_certificate_builder_fonts';
	/**
	 * Validation errors
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Public constructor for class
	 */
	public function __construct() {
		$this->parent_menu_page_url  = 'edit.php?post_type=sfwd-certificates';
		$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
		$this->settings_page_id      = 'learndash-lms-certificate-fonts-manager';
		$this->settings_page_title   = esc_html__( 'Fonts', 'learndash-certificate-builder' );
		$this->settings_tab_priority = 40;
		parent::__construct();
		add_action( 'wp_loaded', array( $this, 'process_font_uploader' ) );
		add_action( 'wp_loaded', array( $this, 'process_font_remove' ) );
	}

	/**
	 * Remove a font
	 */
	public function process_font_remove() {
		$post = wp_unslash( $_POST );
		if ( ! isset( $post['learndash_certificate_builder_font_remove'] ) || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $post['_wpnonce'] ) || ! wp_verify_nonce( $post['_wpnonce'], 'learndash_certificate_builder_remove_font' ) ) {
			return;
		}
		$key   = isset( $post['learndash_certificate_builder_font_remove'] ) ? $post['learndash_certificate_builder_font_remove'] : false;
		$fonts = $this->get_user_fonts();
		$font  = isset( $fonts[ $key ] ) ? $fonts[ $key ] : false;
		if ( ! $font ) {
			// do nothing.
			return;
		}
		// remove all font files.
		foreach ( $font as $k => $file ) {
			if ( in_array( $k, array( array( 'R', 'B', 'I', 'BI' ) ), true ) ) {
				unlink( $this->get_user_font_path() . DIRECTORY_SEPARATOR . pathinfo( $file, PATHINFO_BASENAME ) );
			}
		}
		unset( $fonts[ $key ] );
		update_option( self::OPTION_NAME, $fonts );
		wp_safe_redirect( admin_url( 'admin.php?page=learndash-lms-certificate-fonts-manager' ) );
		exit;
	}

	/**
	 * Handle the fonts uploading
	 *
	 * @throws MpdfException Catch when font is not valid.
	 */
	public function process_font_uploader() {
		$post = wp_unslash( $_POST );
		// check the condition.
		if ( ! isset( $post['learndash_certificate_builder_font_upload'] ) || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$files = $_FILES;
		if ( ! isset( $post['_wpnonce'] ) || ! wp_verify_nonce( $post['_wpnonce'], 'learndash_certificate_builder_upload_font' ) ) {
			return;
		}

		// validate.
		$label       = isset( $post['label'] ) ? sanitize_text_field( $post['label'] ) : null;
		$regular     = isset( $files['regular'] ) ? $files['regular'] : array();
		$bold        = isset( $files['bold'] ) ? $files['bold'] : array();
		$italic      = isset( $files['italic'] ) ? $files['italic'] : array();
		$bold_italic = isset( $files['bold_italic'] ) ? $files['bold_italic'] : array();
		if ( ! isset( $regular['name'] ) || empty( $regular['name'] ) ) {
			$this->errors[] = __( 'The Regular font is required', 'learndash-certificate-builder' );

			return;
		}

		// now extract the family info and validate regular file.
		$ret = $this->is_file_as_font( $regular['tmp_name'] );
		if ( $ret instanceof \Exception ) {
			$this->errors[] = $ret->getMessage();

			return;
		}
		if ( empty( $label ) ) {
			$label = $ret[0];
		}

		$family_name = sanitize_title( $ret[0] );
		// starting upload.
		// init the user font folder before start upload, as the hook can be nested.
		$this->get_user_font_path();
		foreach (
				array(
					'R'  => $regular,
					'B'  => $bold,
					'I'  => $italic,
					'BI' => $bold_italic,
				) as $key => $font
		) {
			if ( ! isset( $font['name'] ) || empty( $font['name'] ) ) {
				continue;
			}
			$ret = $this->is_file_as_font( $font['tmp_name'] );
			if ( $ret instanceof \Exception ) {
				$this->errors[] = $ret->getMessage();

				return;
			}
			add_filter( 'upload_dir', array( $this, 'correct_upload_path' ) );
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php';
			}
			$result = wp_handle_upload(
				$font,
				array(
					'test_form' => false,
					'test_type' => false,
				)
			);
			if ( isset( $result['file'] ) ) {
				// done, update the family.
				$this->save_font( $label, $family_name, $key, $result['url'] );
			}
			remove_filter( 'upload_dir', array( $this, 'correct_upload_path' ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=learndash-lms-certificate-fonts-manager' ) );
		exit;
	}

	/**
	 * Filters the uploads directory data.
	 *
	 * @param array $uploads Array of information about the upload directory.
	 *
	 * @return array
	 */
	public function correct_upload_path( $uploads ) {
		$rev_dir           = '/learndash-certificate-builder/user_fonts';
		$uploads['path']   = $uploads['basedir'] . $rev_dir;
		$uploads['url']    = $uploads['baseurl'] . $rev_dir;
		$uploads['subdir'] = $rev_dir;

		return $uploads;
	}

	/**
	 * Validate the upload file
	 *
	 * @param string $path Font file.
	 *
	 * @return array|\Exception
	 * @throws MpdfException Catch when the font is not valid format.
	 */
	private function is_file_as_font( $path ) {
		$cache      = new Cache( $this->get_working_path() );
		$font_cache = new FontCache( $cache );
		$ttfont     = new TTFontFileAnalysis( $font_cache, '' );
		try {
			return $ttfont->extractCoreInfo( $path );
		} catch ( \Exception $e ) {
			return $e;
		}
	}

	/**
	 * Custom function to show settings page output
	 */
	public function show_settings_page() {
		$fonts = $this->get_user_fonts();
		?>
		<div id="certificate-builder-fonts-manager" class="wrap">
			<div class="font-uploader">
				<?php if ( ! empty( $this->errors ) ) : ?>
					<div class="notice notice-error settings-error">
						<p><?php echo esc_html( implode( PHP_EOL, $this->errors ) ); ?></p>
					</div>
				<?php endif; ?>
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'learndash_certificate_builder_upload_font' ); ?>
					<input type="hidden" name="learndash_certificate_builder_font_upload" value="1"/>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row">
								<label for="label"><?php esc_html_e( 'Label', 'learndash-certificate-builder' ); ?></label>
							</th>
							<td>
								<input name="label" type="text" id="label" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="regular"><?php esc_html_e( 'Regular (*)', 'learndash-certificate-builder' ); ?></label>
							</th>
							<td>
								<input name="regular" type="file" id="regular">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bold"><?php esc_html_e( 'Bold', 'learndash-certificate-builder' ); ?></label>
							</th>
							<td>
								<input name="bold" type="file" id="bold">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="italic"><?php esc_html_e( 'Italic', 'learndash-certificate-builder' ); ?></label>
							</th>
							<td>
								<input name="italic" type="file" id="italic">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bold_italic"><?php esc_html_e( 'Bold Italic', 'learndash-certificate-builder' ); ?></label>
							</th>
							<td>
								<input name="bold_italic" type="file" id="bold_italic">
							</td>
						</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					</p>
				</form>
			</div>
			<?php if ( ! empty( $fonts ) ) : ?>
				<table class="wp-list-table widefat fixed striped table-view-list">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Label', 'learndash-certificate-builder' ); ?></th>
						<th><?php esc_html_e( 'Regular', 'learndash-certificate-builder' ); ?></th>
						<th><?php esc_html_e( 'Bold', 'learndash-certificate-builder' ); ?></th>
						<th><?php esc_html_e( 'Italic', 'learndash-certificate-builder' ); ?></th>
						<th><?php esc_html_e( 'Bold Italic', 'learndash-certificate-builder' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $fonts as $key => $font ) : ?>
						<tr>
							<td>
								<div><?php echo esc_html( $font['name'] ); ?></div>
								<form method="post" id="frm_<?php echo esc_html( $key ); ?>">
									<input type="hidden" name="learndash_certificate_builder_font_remove" value="<?php echo esc_html( $key ); ?>">
									<?php wp_nonce_field( 'learndash_certificate_builder_remove_font' ); ?>
									<span><a href="javascript:{}" onclick="document.getElementById('frm_<?php echo esc_html( $key ); ?>').submit(); return false;">
										<?php esc_html_e( 'Remove', 'learndash-certificate-builder' ); ?>
										</a>
									</span>
								</form>
							</td>
							<td><?php echo esc_html( pathinfo( $font['R'], PATHINFO_BASENAME ) ); ?></td>
							<td><?php echo isset( $font['B'] ) ? esc_html( pathinfo( $font['B'], PATHINFO_BASENAME ) ) : null; ?></td>
							<td><?php echo isset( $font['I'] ) ? esc_html( pathinfo( $font['I'], PATHINFO_BASENAME ) ) : null; ?></td>
							<td><?php echo isset( $font['BI'] ) ? esc_html( pathinfo( $font['BI'], PATHINFO_BASENAME ) ) : null; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif ?>
		</div>
		<?php
		// reset the error.
		$this->errors = array();
	}

	/**
	 * Save the font meta
	 *
	 * @param string $label User friendly name.
	 * @param string $family Font family.
	 * @param string $type Font type.
	 * @param string $url Font URL.
	 */
	private function save_font( $label, $family, $type, $url ) {
		$fonts = $this->get_user_fonts();
		$info  = array(
			'name'   => $label,
			$type    => $url,
			'custom' => true,
		);
		if ( isset( $fonts[ $family ] ) ) {
			$fonts[ $family ] = array_replace( $fonts[ $family ], $info );
		} else {
			$fonts[ $family ] = $info;
		}
		update_option( self::OPTION_NAME, $fonts );
	}

	/**
	 * Get all the fonts uploaded by the user
	 *
	 * @return false|mixed|void
	 */
	private function get_user_fonts() {
		return get_option( self::OPTION_NAME, array() );
	}
}
