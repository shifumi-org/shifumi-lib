<?php
/**
 *
 * WE\PostType Class
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

namespace WE_TINCANNY;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Ajax extends Extensions\Abs {
	private $ajax_key, $form, $serverCallback, $clientCallback;

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		$this->ajax_key = str_replace( '-', '_', $this->key );

		add_action( 'wp_head', array( $this, 'EnqueueScript' ) );
		add_action( 'admin_head', array( $this, 'EnqueueScript' ) );

		add_action( 'wp_ajax_' . $this->ajax_key, array( $this, 'AjaxCallBack' ) );
	}

	public function __set( $name, $value ) {
		switch( $name ) {
			case 'form' :
			case 'serverCallback' :
			case 'clientCallback' :
				$this->{$name} = $value;
				break;
		}
	}

	public function AjaxCallBack() {
		check_ajax_referer( $this->key, 'security' );

		if ( is_array( $this->serverCallback ) ) {
			if ( method_exists( $this->serverCallback[0], $this->serverCallback[1] ) ) {
				call_user_func( $this->serverCallback );
			}
		} else {
			if ( function_exists( $this->serverCallback ) ) {
				call_user_func( $this->serverCallback );
			}
		}

		wp_die();
	}

	public function EnqueueScript() {
		?>
		<script>
			<?php if ( !isset( $GLOBALS[ 'WE_AJAX_URL' ] ) ) : ?>
				var we_ajax_nonces = {};
				var we_forms = {};
				var we_ajax_keys = {};

				var we_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

				function weAjax( ajaxKey ) {
					var dataObject = {};
					var dataArray = jQuery( "#" + we_forms[ ajaxKey ]  ).serializeArray();

					dataArray.forEach( function( value ) {
						dataObject[ value.name ] = value.value;
					});

					var data = {
						'action': we_ajax_keys[ ajaxKey ],
						'security' : we_ajax_nonces[ ajaxKey ]
					};

					data = jQuery.extend( dataObject, data );

					jQuery.post( we_ajax_url, data, function( response ) {
						eval(	'weAjaxClientCallback_' + we_ajax_keys[ ajaxKey ] + '( response );' );
					});

				}
			<?php endif; ?>

			function weAjaxClientCallback_<?php echo $this->ajax_key ?>( response ) {
				<?php
				if ( is_array( $this->clientCallback ) ) {
					if ( method_exists( $this->clientCallback[0], $this->clientCallback[1] ) ) {
						call_user_func( $this->clientCallback );
					}
				} else {
					if ( function_exists( $this->clientCallback ) ) {
						call_user_func( $this->clientCallback );
					}
				}
				?>
			}

			we_ajax_nonces[ '<?php echo $this->name ?>' ] = '<?php echo wp_create_nonce( $this->key, 'security' ) ?>';
			we_forms[ '<?php echo $this->name ?>' ] = '<?php echo $this->form ?>';
			we_ajax_keys[ '<?php echo $this->name ?>' ] = '<?php echo $this->ajax_key ?>';
		</script>
		<?php

		$GLOBALS[ 'WE_AJAX_URL' ] = true;
	}
}
