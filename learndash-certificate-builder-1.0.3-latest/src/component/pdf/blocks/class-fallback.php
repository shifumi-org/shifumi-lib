<?php
/**
 * The default class for processing normal block
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Fallback
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Fallback extends Block {

	/**
	 * Trigger code
	 */
	public function run() {
		$this->html->add( do_shortcode( trim( $this->block['innerHTML'] ) ) );
	}
}
