<?php
/**
 * Process the paragraph block.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Core_Paragraph
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Core_Paragraph extends Core_Heading {
	/**
	 * Loop through the attributes and building css.
	 *
	 * @param array $attrs The block attributes.
	 */
	protected function parse_css( $attrs ) {
		parent::parse_css( $attrs );

		if ( ! isset( $this->block['attrs']['style']['typography']['lineHeight'] ) ) {
			$this->style->add( $this->block['id'], 'line-height', '1.4' );
		}
	}
}
