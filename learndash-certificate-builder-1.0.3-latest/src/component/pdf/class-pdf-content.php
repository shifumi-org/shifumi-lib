<?php
/**
 * The main file for building PDF style and html content.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Pdf;

use LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks\Fallback;
use LearnDash_Certificate_Builder\Component\Pdf\Builder\Html_Builder;
use LearnDash_Certificate_Builder\Component\Pdf\Builder\Style_Builder;
use Mpdf\Mpdf;

/**
 * Class Pdf_Content
 *
 * @package LearnDash_Certificate_Builder\Component\Pdf
 */
class Pdf_Content {

	/**
	 * The certificate POST ID
	 *
	 * @var int
	 */
	public $id;
	/**
	 * The background image
	 *
	 * @var string
	 */
	public $background_image;
	/**
	 * The font name
	 *
	 * @var string
	 */
	public $font;
	/**
	 * If we use the custom font or core font
	 *
	 * @var bool
	 */
	public $use_font = false;
	/**
	 * The margin in rem
	 *
	 * @var float
	 */
	public $spacing = 1;
	/**
	 * The container height in pixel, we use this for re-calculate
	 *
	 * @var int
	 */
	public $page_height = 0;

	/**
	 * The container width in pixel
	 *
	 * @var int
	 */
	public $page_width = 0;

	/** The container width, in percent
	 *
	 * @var int
	 */
	public $container_width = 70;
	/**
	 * PDF size
	 *
	 * @var string
	 */
	public $page_size = 'Letter';
	/**
	 * PDF orientation
	 *
	 * @var string
	 */
	public $page_orientation = 'L';

	/**
	 * The PDF page height
	 *
	 * @var Mpdf
	 */
	public $mpdf;

	/**
	 * The styler builder
	 *
	 * @var Style_Builder
	 */
	public $style_builder;

	/**
	 * The html styler
	 *
	 * @var Html_Builder
	 */
	public $html_builder;

	/**
	 * The root block
	 *
	 * @var array
	 */
	private $root;

	/**
	 * The course ID, when in view mode
	 *
	 * @var int
	 */
	public $course_id;

	/**
	 * Contain the default colors
	 *
	 * @var array
	 */
	public $default_colors_palette = array();

	/**
	 * Pdf_Content constructor.
	 *
	 * @param array $blocks The root block.
	 * @param Mpdf  $mpdf The MPDF instance.
	 */
	public function __construct( $blocks, $mpdf ) {
		$root                = array_shift( $blocks );
		$this->style_builder = new Style_Builder();
		$this->html_builder  = new Html_Builder();
		$this->extract_info( $root );
		$this->mpdf = $mpdf;
		$this->mpdf->_setPageSize( $this->page_size, $this->page_orientation );
		$root = $this->fix_block_position( $root );
		unset( $root['attrs']['positions'] );
		$this->root = $root;
		$this->style_builder->add( 'body', 'background-image', 'url("' . $this->background_image . '")' );
		$this->style_builder->add( 'body', 'background-position', 'top left' );
		$this->style_builder->add( 'body', 'background-repeat', 'no-repeat' );
		$this->style_builder->add( 'body', 'background-image-resize', '6' );
		$this->style_builder->add( 'body', 'background-image-resolution', 'from-image' );
		$this->style_builder->add( 'body', 'font-size', '18px' );
		$this->style_builder->add( 'body', 'line-height', '1' );
		if ( $this->use_font ) {
			$this->style_builder->add( 'body', 'font-family', "'{$this->font}'" );
		} else {
			$this->style_builder->add( 'body', 'font-family', "'freeserif'" );
		}
		if ( isset( $root['attrs']['rtl'] ) && true === $root['attrs']['rtl'] ) {
			$this->mpdf->SetDirectionality( 'rtl' );
		}
		// init the style for the wrapper too.
		$this->style_builder->add( 'wrap', 'width', $this->container_width . '%' );
		$this->style_builder->add( 'wrap', 'margin', '0px auto' );
		$this->style_builder->add( 'wrap', 'position', 'relative' );
		$this->style_builder->add( '.cb-block', 'margin-bottom', $this->spacing . 'rem' );
		$this->style_builder->add( '.learndash-block-inner', 'margin-bottom', $this->spacing . 'rem' );
	}

	/**
	 * Init the colors class
	 */
	public function init_colors() {
		global $_wp_theme_features;
		$color_palette = array();
		// now we add the defined colors from theme.
		if ( isset( $_wp_theme_features['editor-color-palette'] ) && is_array( $_wp_theme_features['editor-color-palette'] ) ) {
			$color_palette = $_wp_theme_features['editor-color-palette'];
		}
		$color_palette = array_merge( $color_palette, array( $this->default_colors_palette ) );
		foreach ( $color_palette as $palette ) {
			foreach ( $palette as $color ) {
				$this->style_builder->add( '.has-' . $color['slug'] . '-color', 'color', $color['color'] );
				$this->style_builder->add( '.has-' . $color['slug'] . '-background-color', 'background-color', $color['color'] );
				foreach ( $palette as $c ) {
					if ( $c['slug'] === $color['slug'] ) {
						continue;
					}
					$this->style_builder->add(
						'.has-' . $color['slug'] . '-to-' . $c['slug'] . '-gradient-background',
						'background',
						'linear-gradient(-160deg, ' . $color['color'] . ', ' . $c['color'] . ')'
					);
				}
			}
		}
	}

	/**
	 * Convert the block into HTML ready
	 *
	 * @param Style_Builder $style_builder The style builder class.
	 * @param Html_Builder  $html_builder The html builder class.
	 * @param array         $root The root block.
	 */
	public function build_html_structure( $style_builder = null, $html_builder = null, $root = null ) {
		$is_nested = false;
		if ( is_null( $root ) ) {
			$root = $this->root;
		} else {
			// this is rendering inside a columns.
			$is_nested = true;
		}
		if ( is_null( $style_builder ) ) {
			$style_builder = $this->style_builder;
		}
		if ( is_null( $html_builder ) ) {
			$html_builder = $this->html_builder;
		}
		foreach ( $root['innerBlocks'] as $key => &$block ) {
			// we will call the class with the block name.
			$class_name = str_replace( '/', '_', ucwords( $block['blockName'], '/' ) );
			$class_name = str_replace( '-', '_', ucwords( $class_name, '-' ) );
			$namespace  = 'LearnDash_Certificate_Builder\\Component\\Builder\\Pdf\Blocks\\';
			$class_name = $namespace . $class_name;

			if ( ! class_exists( $class_name ) ) {
				// fallback to render.
				$class = new Fallback( $block, $style_builder, $html_builder, $this );
				$class->run();
				continue;
			}
			$class = new $class_name( $block, $style_builder, $html_builder, $this );
			if ( $is_nested && ( count( $root['innerBlocks'] ) - 1 ) === $key && 'core/columns' !== $block['blockName'] ) {
				$class->style->add( $block['id'], 'margin-bottom', '0' );
			}
			$class->is_nested = $is_nested;
			$class->run();
			do_action( 'learndash_certificate_builder_after_block_processed', $class, $key );
		}
	}

	/**
	 * Extract and filter the block attributes.
	 *
	 * @param array $parent The root block.
	 */
	protected function extract_info( $parent ) {
		$attrs    = $parent['attrs'];
		$attrs    = shortcode_atts(
			array(
				'id'              => 0,
				'backgroundImage' => '',
				'font'            => '',
				'useFont'         => false,
				'pageSize'        => 'Letter',
				'pageOrientation' => 'L',
				'pageHeight'      => 0,
				'pageWidth'       => 0,
				'containerWidth'  => 70,
				'spacing'         => 1,
				'positions'       => array(),
			),
			$attrs
		);
		$this->id = absint( $attrs['id'] );
		// image should be local.
		$this->background_image = wp_get_attachment_image_url( $this->id, 'full' );
		if ( false === $this->background_image ) {
			// false back to URL.
			$this->background_image = $attrs['backgroundImage'];
			if ( empty( $this->background_image ) ) {
				wp_die( esc_html__( 'A background image is required.', 'learndash-certificate-builder' ) );
			}
		}
		$this->font      = esc_html( $attrs['font'] );
		$this->use_font  = filter_var( $attrs['useFont'], FILTER_VALIDATE_BOOLEAN );
		$this->page_size = $attrs['pageSize'];
		if ( ! in_array( $this->page_size, array( 'Letter', 'A4' ), true ) ) {
			$this->page_size = 'Letter';
		}
		$this->page_orientation = $attrs['pageOrientation'];
		if ( ! in_array( $this->page_orientation, array( 'L', 'P' ), true ) ) {
			$this->page_orientation = 'L';
		}
		$this->page_height     = absint( $attrs['pageHeight'] );
		$this->page_width      = absint( $attrs['pageWidth'] );
		$this->container_width = absint( $attrs['containerWidth'] );
		$this->spacing         = floatval( $attrs['spacing'] );
	}

	/**
	 * This will merge the positions into the block
	 *
	 * @param array $root The block.
	 *
	 * @return array
	 */
	protected function fix_block_position( $root ) {
		foreach ( $root['innerBlocks'] as $key => &$block ) {
			// randomly id.
			$el_id       = uniqid( 'block_' );
			$block['id'] = $el_id;
			if ( isset( $block['innerBlocks'] ) && count( $block['innerBlocks'] ) ) {
				// this should be always have positions, it is a must.
				$block = $this->fix_block_position( $block );
			}
		}

		return $root;
	}
}
