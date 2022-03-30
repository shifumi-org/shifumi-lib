<?php
/**
 * Process the Spacer block.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Process the Spacer block.
 *
 * Class Core_Spacer
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Core_Spacer extends Block {

	/**
	 * Trigger function
	 */
	public function run() {
		$this->html->add( $this->block['innerHTML'] );
	}
}
