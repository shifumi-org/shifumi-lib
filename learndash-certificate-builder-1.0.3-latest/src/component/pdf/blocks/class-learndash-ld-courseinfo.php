<?php
/**
 * Process the Ld_usermeta block
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
class Learndash_Ld_Courseinfo extends Learndash_Ld_Usermeta {

	/**
	 * Trigger
	 */
	public function run() {
		if ( ! isset( $this->block['attrs']['course_id'] ) || empty( $this->block['attrs']['course_id'] ) ) {
			// need to convert into string or it will be empty.
			$this->block['attrs']['course_id'] = "{$this->caller->course_id}";
		}
		$block = new \WP_Block( $this->block );
		$el    = sprintf( '<div id="%s">' . $block->render() . '</div>', $this->block['id'] );
		$this->html->add( $el );
		$this->build_style();
	}
}
