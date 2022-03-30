<?php
/**
 * Process the heading block.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

use simplehtmldom\HtmlDocument;

/**
 * Class Core_Heading
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Core_Heading extends Block {
	/**
	 * Trigger
	 */
	public function run() {
		$client = new HtmlDocument();
		$html   = do_shortcode( $this->block['innerHTML'] );
		$client->load( $html );
		$last_child = $client->lastChild();
		$html       = str_replace( $last_child->innertext, str_replace( ' ', '&nbsp;', $last_child->innertext ), $html );

		$this->style->add( $this->block['id'], 'position', 'relative' );
		$this->parse_css( $this->block['attrs'] );
		$this->html->add( $html );
	}

	/**
	 * Loop through the attributes and building css.
	 *
	 * @param array $attrs The block attributes.
	 */
	protected function parse_css( $attrs ) {

		if ( isset( $attrs['fontSize'] ) ) {
			switch ( $attrs['fontSize'] ) {
				case 'small':
					$this->style->add( $this->block['id'], 'font-size', '18px' );
					break;
				case 'extra-small':
					$this->style->add( $this->block['id'], 'font-size', '16px' );
					break;
				case 'normal':
					$this->style->add( $this->block['id'], 'font-size', '20px' );
					break;
				case 'large':
					$this->style->add( $this->block['id'], 'font-size', '24px' );
					break;
				case 'extra-large':
					$this->style->add( $this->block['id'], 'font-size', '40px' );
					break;
				case 'huge':
					$this->style->add( $this->block['id'], 'font-size', '96px' );
					break;
				case 'gigantic':
					$this->style->add( $this->block['id'], 'font-size', '122px' );
					break;
			}
		}
		if ( isset( $attrs['textAlign'] ) ) {
			$this->style->add( $this->block['id'], 'text-align', $attrs['textAlign'] );
		}
		if ( isset( $attrs['textColor'] ) ) {
			if ( strpos( $attrs['textColor'], '#' ) === 0 ) {
				$this->style->add( $this->block['id'], 'color', $attrs['textColor'] );
			}
		}
		if ( isset( $attrs['backgroundColor'] ) ) {
			if ( strpos( $attrs['textColor'], '#' ) === 0 ) {
				$this->style->add( $this->block['id'], 'background-color', $attrs['backgroundColor'] );
			}
		}
		if ( isset( $attrs['useFont'] ) && true === $attrs['useFont'] ) {
			$font = $attrs['font'];
			$this->style->add( $this->block['id'], 'font-family', "'$font'" );
		}

		if ( ! isset( $attrs['style']['typography']['lineHeight'] ) ) {
			$this->style->add( $this->block['id'], 'line-height', '1.4' );
		}
	}

	/**
	 * From color to hex code
	 *
	 * @param string $color The color.
	 *
	 * @return string|null
	 */
	public function color_palette( $color ) {
		$colors = array(
			'accent'            => '#cd2653',
			'primary'           => '#000',
			'secondary'         => '#6d6d6d',
			'subtle-background' => '#dcd7ca',
			'background'        => '#f5efe0',
			'dark-gray'         => '#28303d',
			'green'             => '#d1e4dd',
			'gray'              => '#39414d',
			'blue'              => '#d1dfe4',
			'purple'            => '#d1d1e4',
			'red'               => '#e4d1d1',
			'orange'            => '#e4dad1',
			'yellow'            => '#eeeadd',
		);
		if ( isset( $colors[ $color ] ) ) {
			return $colors[ $color ];
		}

		return null;
	}
}
