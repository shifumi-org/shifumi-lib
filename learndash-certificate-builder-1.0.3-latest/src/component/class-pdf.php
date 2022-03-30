<?php
/**
 * This class will init the mPDF and do ser the output, though the PDF content will be
 * processed in \LearnDash_Certificate_Builder\Component\Pdf\Content_Builder.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component;

use Certificate_Builder\Traits\IO;
use LearnDash_Certificate_Builder\Component\Pdf\Pdf_Content;
use LearnDash_Certificate_Builder\Controller\Fonts_Manager;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Class PDF
 *
 * @package LearnDash_Certificate_Builder\Component
 */
class PDF {

	use IO;

	/**
	 * The MDPF instance
	 *
	 * @var Mpdf
	 */
	private $mpdf;
	/**
	 * Enable debug for element border
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * Course ID, use in generating
	 *
	 * @var int
	 */
	private $course_id = 0;

	/**
	 * PDF init.
	 *
	 * @throws \Mpdf\MpdfException Throw error if Mpdf cant not be init, should never be here.
	 */
	public function init() {
		if ( $this->mpdf instanceof Mpdf ) {
			return $this->mpdf;
		}
		$this->mpdf = new Mpdf(
			array(
				'tempDir'       => $this->get_working_path(),
				// by default, as it will be auto override when we define custom font.
				'default_font'  => 'freeserif',
				'margin_left'   => 0,
				'margin_right'  => 0,
				'margin_top'    => 0,
				'margin_bottom' => 0,
				'debug'         => $this->debug,
				'fontdata'      => array(
					'freesans'  => array(
						'R'      => 'FreeSans.ttf',
						'B'      => 'FreeSansBold.ttf',
						'I'      => 'FreeSansOblique.ttf',
						'BI'     => 'FreeSansBoldOblique.ttf',
						'useOTL' => 0xFF,
					),
					'freeserif' => array(
						'R'          => 'FreeSerif.ttf',
						'B'          => 'FreeSerifBold.ttf',
						'I'          => 'FreeSerifItalic.ttf',
						'BI'         => 'FreeSerifBoldItalic.ttf',
						'useOTL'     => 0xFF,
						'useKashida' => 75,
					),
					'freemono'  => array(
						'R'  => 'FreeMono.ttf',
						'B'  => 'FreeMonoBold.ttf',
						'I'  => 'FreeMonoOblique.ttf',
						'BI' => 'FreeMonoBoldOblique.ttf',
					),
				),
			)
		);
		// fallback to freesans if any font corrupt.
		$this->mpdf->backupSubsFont = array( 'freesans' );
		$user_fonts                 = get_option( Fonts_Manager::OPTION_NAME, array() );
		foreach ( $user_fonts as $key => $font ) {
			foreach ( $font as $k => &$url ) {
				if ( in_array( $k, array( 'R', 'B', 'I', 'BI' ), true ) ) {
					$url                              = pathinfo( $url, PATHINFO_BASENAME );
					$this->mpdf->available_unifonts[] = $key . trim( $k, 'R' );
				} else {
					unset( $font[ $k ] );
				}
			}
			$this->mpdf->fontdata[ $key ] = $font;
		}
		$this->mpdf->default_available_fonts = $this->mpdf->available_unifonts;
		$this->mpdf->AddFontDirectory( $this->get_user_font_path() );
	}

	/**
	 * Get all the fonts that supported
	 *
	 * @return array
	 */
	public function get_fonts() {
		$configs    = ( new FontVariables() )->getDefaults();
		$font_data  = $configs['fontdata'];
		$dictionary = $this->get_mpdf_fonts();
		foreach ( $font_data as $key => &$data ) {
			if ( ! isset( $dictionary[ $key ] ) ) {
				unset( $font_data[ $key ] );
				continue;
			}
			$name         = isset( $dictionary[ $key ] ) ? $dictionary[ $key ] : $key;
			$data['name'] = $name;
		}
		$user_fonts = get_option( Fonts_Manager::OPTION_NAME, array() );
		$font_data  = array_merge( $font_data, $user_fonts );
		// move the freeserif to top.
		$free_serif = $font_data['freeserif'];
		unset( $font_data['freeserif'] );

		return array_merge( array( 'freeserif' => $free_serif ), $font_data );
	}

	/**
	 * Output the pdf to screen
	 *
	 * @param array $blocks The blocks that been added.
	 * @param int   $cert_id Certificate ID.
	 * @param int   $course_id Course ID.
	 */
	public function serve( $blocks, $cert_id = 0, $course_id = 0 ) {
		$this->init();
		$builder                         = new Pdf_Content( $blocks, $this->mpdf );
		$builder->course_id              = $course_id;
		$builder->default_colors_palette = $this->get_default_pallete_colors();
		$builder->init_colors();
		$this->mpdf->setMBencoding( 'UTF-8' );
		$this->mpdf->usingCoreFont = false;
		$this->mpdf->onlyCoreFonts = false;
		$this->mpdf                = apply_filters( 'learndash_certificate_builder_mpdf', $this->mpdf );
		$builder->build_html_structure();
		$gutenberg_style = file_get_contents( learndash_certificate_builder_path( 'src/component/pdf/style.css' ) );
		$this->mpdf->WriteHTML( $gutenberg_style, HTMLParserMode::HEADER_CSS );
		$this->mpdf->WriteHTML( $builder->style_builder->output(), HTMLParserMode::HEADER_CSS );
		$this->mpdf->WriteHTML( sprintf( '<div id="wrap">%s</div>', $builder->html_builder->output() ) );

		$pdf_name    = apply_filters( 'learndash_certificate_builder_pdf_name', $this->build_pdf_file_name( $cert_id, $course_id ), $cert_id, $course_id );
		$destination = apply_filters( 'learndash_certificate_builder_pdf_output_mode', Destination::INLINE, $cert_id, $course_id );
		$this->mpdf->Output( $pdf_name, $destination );
	}

	/**
	 * Build the file name
	 *
	 * @param int $certificate_id The certificate ID.
	 * @param int $course_id The course ID.
	 *
	 * @return string
	 */
	private function build_pdf_file_name( $certificate_id = 0, $course_id = 0 ) {
		if ( 0 !== $course_id && 0 !== $certificate_id ) {
			// we only need post title.
			$cert   = get_post( $certificate_id );
			$course = get_post( $course_id );
			$user   = wp_get_current_user();

			return sanitize_file_name( $user->user_login . ' ' . $course->post_title . ' ' . $cert->post_title . ' ' . get_bloginfo( 'name' ) ) . '.pdf';
		} else {
			return 'preview.pdf';
		}
	}

	/**
	 * The default color palette
	 *
	 * @return array[]
	 */
	public function get_default_pallete_colors() {
		return array(
			array(
				'name'  => __( 'Black', 'learndash-certificate-builder' ),
				'slug'  => 'black',
				'color' => '#000000',
			),
			array(
				'name'  => __( 'Cyan bluish gray', 'learndash-certificate-builder' ),
				'slug'  => 'cyan-bluish-gray',
				'color' => '#abb8c3',
			),
			array(
				'name'  => __( 'White', 'learndash-certificate-builder' ),
				'slug'  => 'white',
				'color' => '#ffffff',
			),
			array(
				'name'  => __( 'Pale pink', 'learndash-certificate-builder' ),
				'slug'  => 'pale-pink',
				'color' => '#f78da7',
			),
			array(
				'name'  => __( 'Vivid red', 'learndash-certificate-builder' ),
				'slug'  => 'vivid-red',
				'color' => '#cf2e2e',
			),
			array(
				'name'  => __( 'Luminous vivid orange', 'learndash-certificate-builder' ),
				'slug'  => 'luminous-vivid-orange',
				'color' => '#ff6900',
			),
			array(
				'name'  => __( 'Luminous vivid amber', 'learndash-certificate-builder' ),
				'slug'  => 'luminous-vivid-amber',
				'color' => '#fcb900',
			),
			array(
				'name'  => __( 'Light green cyan', 'learndash-certificate-builder' ),
				'slug'  => 'light-green-cyan',
				'color' => '#7bdcb5',
			),
			array(
				'name'  => __( 'Vivid green cyan', 'learndash-certificate-builder' ),
				'slug'  => 'vivid-green-cyan',
				'color' => '#00d084',
			),
			array(
				'name'  => __( 'Pale cyan blue', 'learndash-certificate-builder' ),
				'slug'  => 'pale-cyan-blue',
				'color' => '#8ed1fc',
			),
			array(
				'name'  => __( 'Vivid cyan blue', 'learndash-certificate-builder' ),
				'slug'  => 'vivid-green-cyan',
				'color' => '#00d084',
			),
			array(
				'name'  => __( 'Vivid purple', 'learndash-certificate-builder' ),
				'slug'  => 'vivid-purple',
				'color' => '#9b51e0',
			),
		);
	}

	/**
	 * Return a list of native supported fonts
	 *
	 * @return array
	 */
	private function get_mpdf_fonts() {
		return array(
			'freeserif' => 'Free Serif',
			'freesans'  => 'Free Sans',
			'freemono'  => 'Free Mono',
		);
	}
}
