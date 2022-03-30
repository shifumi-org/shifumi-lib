<?php
/**
 * Builder interface
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Pdf\Builder;

interface Builder {
	/**
	 * Output the content
	 *
	 * @return string
	 */
	public function output();
}
