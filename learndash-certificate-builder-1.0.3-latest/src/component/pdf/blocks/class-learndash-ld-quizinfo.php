<?php
/**
 * Process the Ld_groupinfo block
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Learndash_Ld_Quizinfo
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Learndash_Ld_Quizinfo extends Learndash_Ld_Usermeta {

	/**
	 * Trigger
	 */
	public function run() {
		// we have to lookup and fill the quiz id if no value. Only when the user view the certificate of a quiz, if
		// it is from a course, then we have to use the value input.
		//phpcs:ignore
		if ( isset( $_GET['quiz'] ) && ( ! isset( $this->block['attrs']['quiz_id'] ) || empty( $this->block['attrs']['quiz_id'] ) ) ) {
			//phpcs:ignore
			$quiz_id                         = absint( $_GET['quiz'] );
			$this->block['attrs']['quiz_id'] = "$quiz_id";
		}
		if ( ! isset( $this->block['attrs']['show'] ) ) {
			$this->block['attrs']['show'] = 'quiz_title';
		}

		$block = new \WP_Block( $this->block );
		$el    = sprintf( '<div id="%s">' . $block->render() . '</div>', $this->block['id'] );
		$this->html->add( $el );
		$this->build_style();
	}
}
