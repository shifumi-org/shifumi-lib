<?php
/**
 *
 * WE\Setting Class
 *
 * @author	Sujin 수진 Choi
 * @package	wp-express
 * @version	4.5.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

namespace WE_TINCANNY\Extensions;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Setting extends Abs {
	private $allowed_type = [ 'file', 'text', 'number', 'checkbox', 'radio', 'select', 'html', 'textarea','editor', 'set' ];
	protected $defaultName = 'New Setting';
	private $option = [];
	private $isSet = false;

	private $default, $placeHolder;
	public $description, $html, $class;
	public $type = 'text';

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		$this->isSet = ( func_num_args() > 1 && func_get_arg(1) !== false ) ? func_get_arg(1) : false;

		parent::__construct( $name );
	}

	public function __set( $name, $value ) {
		parent::__set( $name, $value );

		switch( $name ) {
			case 'type' :
				if ( !in_array( strtolower( $value ), $this->allowed_type ) ) return;
				$this->type = $value;
				break;

			case 'default' :
				if ( empty( $this->values ) ) $this->values = $value;
				$this->default = $value;
				break;

			case 'placeholder' :
			case 'placeHolder' :
				$this->placeHolder = $value;
				break;

			case 'option' :
				$this->option[] = $value;
				break;
		}
	}

	public function printSettingsField() {
		$style = ( $this->isSet ) ? [ 'float:left', 'margin-bottom:8px', 'margin-right:5px' ] : [];
		if ( $this->isSet && !$this->placeHolder ) $this->placeHolder = $this->isSet;

		switch ( $this->type ) {
			case 'file' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = ( $this->isSet ) ? 'small-text' : 'regular-text';
				}

				$upload_link = get_upload_iframe_src();
				$media_arr = wp_get_attachment_image_src( $this->value );

				$img = $this->value ? '<img src="' . $media_arr[0] . '" width="150" />' : '';

				?>
				<div id="<?php echo $this->key ?>-custom-img-container"><?php echo $img ?></div>
				<a id="<?php echo $this->key ?>-upload-custom-img" class="<?php if ( $img ) { echo 'hidden'; } ?>" href="<?php echo $upload_link ?>"><?php _e( 'Set custom image' ) ?></a>
				<a id="<?php echo $this->key ?>-delete-custom-img" class="<?php if ( !$img ) { echo 'hidden'; } ?>" href="#"><?php _e( 'Remove this image' ) ?></a>

				<input id="<?php echo $this->key ?>-custom-img-id" name="<?php echo $this->key ?>" type="hidden" value="<?php echo esc_attr( $this->value ); ?>" />
				<?php

				add_action( 'admin_footer', array( $this, 'printMediaUploadScript' ) );
				wp_enqueue_media();
			break;

			case 'text' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = ( $this->isSet ) ? 'small-text' : 'regular-text';
				}

				?>
				<input type="text" name="<?php echo $this->key ?>" id="<?php echo $this->key ?>" value="<?php echo $this->value ?>" class="<?php echo $class ?>" placeholder="<?php echo $this->placeHolder ?>" style="<?php echo implode( '; ', $style) ?>" />
				<?php
			break;

			case 'number' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = ( $this->isSet ) ? 'small-text' : 'regular-text';
				}

				?>
				<input type="number" name="<?php echo $this->key ?>" id="<?php echo $this->key ?>" value="<?php echo $this->value ?>" class="<?php echo $class ?>" placeholder="<?php echo $this->placeHolder ?>" style="<?php echo implode( '; ', $style) ?>" />
				<?php
			break;

			case 'radio' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = ( $this->isSet ) ? 'small-text' : 'regular-text';
				}

				$style[] = ' margin-right:8px';

				if ( !empty( $this->option ) ) {
					foreach( $this->option as $key => $option ) {
						if ( !is_array( $option ) ) $option = [ $option, $option ];

						printf( '<label for="%s[%s]" class="%s" style="%s"><input type="radio" id="%s[%s]" name="%s" value="%s" %s />%s</label>', $this->key, $key, $class, implode( '; ', $style ), $this->key, $key, $this->key, $option[0], ( $option[0] == $this->value ) ? 'checked="checked"' : '', $option[1] );
					}
				}
			break;

			case 'checkbox' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = 'regular-text';
				}

				if ( $this->option ) {
					$text = $this->option[0];
				} else if ( $this->isSet ) {
					$text = $this->isSet;
				} else {
					$text = $this->name;
				}

				?>
				<label for="<?php echo $this->key ?>" class="<?php echo $class ?>" style="<?php echo implode( '; ', $style) ?>">
					<input type="checkbox" name="<?php echo $this->key ?>" id="<?php echo $this->key ?>" <?php if ( $this->value ) echo 'checked="checked"'; ?> />
					<?php echo $text ?>
				</label>
				<?php
			break;

			case 'select' :
				if ( !empty( $this->class ) ) {
					$class = $this->class;
				} else {
					$class = ( $this->isSet ) ? 'small-text' : 'regular-text';
				}

				?>
				<select name="<?php echo $this->key ?>" id="<?php echo $this->key ?>" class="<?php echo $class ?>" style="<?php echo implode( '; ', $style) ?>">
					<?php
					if ( !empty( $this->option ) ) {
						foreach( $this->option as $option ) {
							if ( !is_array( $option ) ) $option = [ $option, $option ];

							printf( '<option value="%s" %s>%s</option>', $option[0], ( $option[0] == $this->value) ? 'selected="selected"': '', $option[1] );
						}
					}
					?>
				</select>
				<?php
			break;

			case 'html' :
				echo $this->html;
			break;

			case 'textarea' :
				$class = ( !empty( $this->class ) ) ? $this->class : 'large-text';
				?>
				<textarea  name="<?php echo $this->key ?>" id="<?php echo $this->key ?>" class="<?php echo $class ?>" rows="8"><?php echo $this->value ?></textarea>
				<?php
			break;

			case 'editor' :
				wp_editor( stripcslashes( $this->value), $this->key );
			break;

		}

		if ( !empty( $this->description ) ) {
			printf( '<p class="description" %s>%s</p>', ( $this->isSet ) ? 'style="position:absolute; margin-top:33px"' : '',  $this->description );
		}
	}

	public function printMediaUploadScript() {

		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				var frame;
				$( '#<?php echo $this->key ?>-upload-custom-img' ).click( function( e ) {
					e.preventDefault();

					if( !frame ) {
						frame = wp.media({
							title: 'Select or Upload Media Of Your Chosen Persuasion',
							button: { text: 'Use this media' },
							multiple: false
						});

						frame.on( 'select', function() {
							var attachment = frame.state().get('selection').first().toJSON();
							$( '#<?php echo $this->key ?>-custom-img-container' ).append( '<img src="' + attachment.url + '" width="150" />' );
							$( '#<?php echo $this->key ?>-custom-img-id' ).val( attachment.id );

							$( '#<?php echo $this->key ?>-upload-custom-img' ).addClass( 'hidden' );
							$( '#<?php echo $this->key ?>-delete-custom-img' ).removeClass( 'hidden' );
						});
					}

					frame.open();
				});

				$( '#<?php echo $this->key ?>-delete-custom-img' ).on( 'click', function( e ){
					e.preventDefault();

					$( '#<?php echo $this->key ?>-custom-img-container' ).html( '' );
					$( '#<?php echo $this->key ?>-custom-img-id' ).val( '' );

					$( '#<?php echo $this->key ?>-upload-custom-img' ).removeClass( 'hidden' );
					$( '#<?php echo $this->key ?>-delete-custom-img' ).addClass( 'hidden' );
				});
			});
		</script>
		<?php
	}

	public function PrintColumnVaue( $value ) {
		switch ( $this->type ) {
			case 'file' :
				echo wp_get_attachment_image( $value, array( 50, 50 ) );
			break;

			case 'text' :
			case 'number' :
				echo $value;
			break;

			case 'radio' :
			case 'select' :
				if ( !empty( $this->option ) ) {
					foreach( $this->option as $key => $option ) {
						if ( !is_array( $option ) ) $option = [ $option, $option ];

						if ( $option[0] == $value ) {
							echo $option[1];
							break;
						}
					}
				}
			break;

			case 'checkbox' :
				if ( $this->option ) {
					$text = $this->option[0];
				} else if ( $this->isSet ) {
					$text = $this->isSet;
				} else {
					$text = $this->name;
				}

				echo $text;
			break;

			case 'html' :
				echo $this->html;
			break;

			case 'textarea' :
			case 'editor' :
				echo $value;
			break;
		}
	}
}