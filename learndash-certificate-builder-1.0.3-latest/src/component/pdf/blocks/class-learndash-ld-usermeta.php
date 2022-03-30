<?php
/**
 * Process the block.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Learndash_Ld_Usermeta
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Learndash_Ld_Usermeta extends Block {

	/**
	 * Trigger
	 */
	public function run() {
		$el = sprintf( '<div id="%s">' . render_block( $this->block ) . '</div>', $this->block['id'] );
		$this->html->add( $el );
		$this->build_style();
	}

	/**
	 * Loop through attributes and build the style.
	 */
	protected function build_style() {
		foreach ( $this->block['attrs'] as $key => $val ) {
			switch ( $key ) {
				case 'font':
					if ( isset( $attrs['useFont'] ) && true === $attrs['useFont'] ) {
						$this->style->add( $this->block['id'], 'font-family', "'$val'" );
					}
					break;
				case 'fontSize':
					if ( filter_var( $val, FILTER_VALIDATE_INT ) || filter_var( $val, FILTER_VALIDATE_FLOAT ) ) {
						$this->style->add( $this->block['id'], 'font-size', "{$val}px" );
					} else {
						$this->style->add( $this->block['id'], 'font-size', "{$val}" );
					}

					break;
				case 'textColor':
					$this->style->add( $this->block['id'], 'color', "{$val}" );
					break;
				case 'backgroundColor':
					$this->style->add( $this->block['id'], 'background-color', "{$val}" );
					break;
				default:
					$key = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $key ) );
					$this->style->add( $this->block['id'], $key, $val );
					break;
			}
		}
		if ( isset( $this->block['attrs']['useFont'] ) && true === $this->block['attrs']['useFont'] ) {
			$this->style->add( $this->block['id'], 'font-family', $this->block['attrs']['font'] );
		}
		if ( ! isset( $this->block['attrs']['fontSize'] ) ) {
			$this->style->add( $this->block['id'], 'font-size', '20px' );
		}
	}
}
