<?php
/**
 * Process the Ld_groupinfo block
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Learndash_Ld_Courseinfo
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Learndash_Ld_Groupinfo extends Learndash_Ld_Usermeta {

	/**
	 * Trigger
	 */
	public function run() {
		$block = new \WP_Block( $this->block );
		$el    = sprintf( '<div id="%s">' . $block->render() . '</div>', $this->block['id'] );
		$this->html->add( $el );
		$this->build_style();
	}
}
