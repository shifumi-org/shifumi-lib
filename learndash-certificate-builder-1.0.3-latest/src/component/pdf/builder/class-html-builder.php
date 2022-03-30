<?php
/**
 * Store and output the html.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Pdf\Builder;

/**
 * Class Html_Builder
 *
 * @package LearnDash_Certificate_Builder\Component\Pdf\Builder
 */
class Html_Builder implements Builder {

	/**
	 * Contain the html blocks
	 *
	 * @var array
	 */
	protected $html = array();

	/**
	 * The width of this view port
	 *
	 * @var float
	 */
	public $width = 0;

	/**
	 * Add the html element into the pool.
	 *
	 * @param string $element The element html.
	 */
	public function add( $element ) {
		$this->html[] = $element;
	}

	/**
	 * Output the content
	 *
	 * @return string
	 */
	public function output() {
		$html = '';
		foreach ( $this->html as $line ) {
			$html .= $line . PHP_EOL;
		}

		return $html;
	}
}
