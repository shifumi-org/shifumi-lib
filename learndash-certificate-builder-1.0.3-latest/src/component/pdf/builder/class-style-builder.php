<?php
/**
 * Store and output the css.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Pdf\Builder;

/**
 * Class Style_Builder
 *
 * @package LearnDash_Certificate_Builder\Component\Pdf\Builder
 */
class Style_Builder implements Builder {
	/**
	 * Contains all the css.
	 *
	 * @var array
	 */
	protected $styles = array();


	/**
	 * Output the content
	 *
	 * @return string
	 */
	public function output() {
		$string = '';
		foreach ( $this->styles as $id => $style ) {
			$el = '#' . $id . ' {';
			if ( 'body' === $id || strpos( $id, '.' ) === 0 ) {
				$el = $id . ' {';
			}
			foreach ( $style as $key => $val ) {
				$el .= $key . ': ' . $val . ';' . PHP_EOL;
			}
			$el     .= '}';
			$string .= $el . PHP_EOL;
		}

		return $string;
	}

	/**
	 * Adding a style to an element
	 *
	 * @param string $element The element id or class.
	 * @param string $key The css name.
	 * @param string $value The css value.
	 *
	 * @return Builder
	 */
	public function add( $element, $key = null, $value = null ) {
		if ( ! isset( $this->styles[ $element ] ) ) {
			$this->styles[ $element ] = array();
		}

		$this->styles[ $element ][ $key ] = $value;

		return $this;
	}
}
