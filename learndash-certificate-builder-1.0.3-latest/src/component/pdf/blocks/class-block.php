<?php
/**
 * Abstract class, other block must extend from this.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

use LearnDash_Certificate_Builder\Component\Pdf\Builder\Html_Builder;
use LearnDash_Certificate_Builder\Component\Pdf\Builder\Style_Builder;
use LearnDash_Certificate_Builder\Component\Pdf\Pdf_Content;
use simplehtmldom\HtmlDocument;

/**
 * Class Block
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
abstract class Block {
	/**
	 * The class that calling it
	 *
	 * @var Pdf_Content
	 */
	public $caller;

	/**
	 * The style builder class.
	 *
	 * @var Style_Builder
	 */
	public $style;

	/**
	 * /**
	 * The Html builder class
	 *
	 * @var Html_Builder
	 */
	public $html;

	/**
	 * The root block
	 *
	 * @var array
	 */
	public $block;

	/**
	 * A flag to know if this element is inside a column, mostly for deal with margin bottom issue.
	 *
	 * @var bool
	 */
	public $is_nested = false;

	/**
	 * Block constructor.
	 *
	 * @param array         $block The root block.
	 * @param Style_Builder $style The style builder class.
	 * @param Html_Builder  $html The html builder class.
	 * @param Pdf_Content   $caller The caller class, we use this for accessing to parent data.
	 */
	public function __construct( array $block, Style_Builder $style, Html_Builder $html, Pdf_Content $caller ) {
		$this->block  = $block;
		$this->html   = $html;
		$this->style  = $style;
		$this->caller = $caller;
		$this->fix_id();
	}

	/**
	 * Trigger code
	 */
	abstract public function run();

	/**
	 * Append the id into innerHtml
	 */
	protected function fix_id() {
		$client = new HtmlDocument();
		if ( empty( $this->block['innerHTML'] ) ) {
			return;
		}
		$client->load( $this->block['innerHTML'] );
		$element = $client->lastChild();
		if ( is_object( $element ) ) {
			$client->lastChild()->setAttribute( 'id', $this->block['id'] );
			$class = $client->lastChild()->getAttribute( 'class' );
			$client->lastChild()->setAttribute( 'class', $class . ' cb-block' );
			$this->block['innerHTML'] = $client->save();
		}
	}
}
