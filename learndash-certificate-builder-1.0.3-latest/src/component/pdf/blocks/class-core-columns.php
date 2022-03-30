<?php
/**
 * Process the columns block.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

use LearnDash_Certificate_Builder\Component\Pdf\Builder\Html_Builder;
use simplehtmldom\HtmlDocument;

/**
 * Class Core_Columns
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Core_Columns extends Block {
	/**
	 * Trigger
	 */
	public function run() {
		$inner_html = '';
		$convert    = 3.7795275591;
		$padding    = 32;
		if ( isset( $this->block['attrs']['backgroundColor'] ) || isset( $this->block['attrs']['style']['color']['gradient'] ) ) {
			// going to add a padding.
			$this->style->add( $this->block['id'], 'padding-top', '1rem' );
			if ( $this->is_nested ) {
				$this->style->add( $this->block['id'], 'margin-bottom', 0 );
			}
			$this->style->add( $this->block['id'], 'padding-bottom', '1rem' );
			$this->style->add( $this->block['id'], 'padding-left', '40px' );
			$padding += 40;
		}
		// the pdf width.
		$is_nested = false;
		if ( 0 === $this->html->width ) {
			$w = absint( $this->caller->mpdf->w * $convert );
		} else {
			$w         = $this->html->width;
			$is_nested = true;
		}
		foreach ( $this->block['innerBlocks'] as $key => $column ) {
			$html_builder = new Html_Builder();
			$width        = isset( $column['attrs']['width'] ) ? $column['attrs']['width'] : round( 100 / count( $this->block['innerBlocks'] ) ) . '%';
			if ( false === $is_nested ) {
				$width = ( ( $w * ( $this->caller->container_width / 100 ) ) - ( $padding * ( count( $this->block['innerBlocks'] ) - 1 ) ) ) * ( absint( $width ) / 100 );
			} else {
				// this is use in nested.
				$width = ( $w - ( $padding * ( count( $this->block['innerBlocks'] ) - 1 ) ) ) * ( absint( $width ) / 100 );
			}
			$html_builder->width = $width;
			$this->style->add( $column['id'], 'width', absint( $width ) );
			$this->style->add( $column['id'], 'float', 'left' );
			if ( $key > 0 ) {
				$this->style->add( $column['id'], 'margin-left', '31px' );
			}

			if ( count( $column['innerBlocks'] ) ) {
				$this->caller->build_html_structure( $this->style, $html_builder, $column );
				$inner_html .= '<div id="' . $column['id'] . '">' . $html_builder->output() . '</div>';
			} else {
				$inner_html .= '<div id="' . $column['id'] . '"><div style="height:1px;"></div></div>';
			}
		}
		$client = new HtmlDocument();
		$client->load( $this->block['innerHTML'] );
		$client->lastChild()->innertext = $inner_html;
		$html                           = $client->save();
		$this->html->add( $html );
	}
}
