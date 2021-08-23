<?php

/**
 * Register Tin Canny Content and
 * render it with a callback function
 */

register_block_type( 'tincanny/content', [
	'attributes'           => [
		'status'           => [
			'type'         => 'string',
			'default'      => 'start'
		],
		'contentId'        => [
			'type'         => 'string',
			'default'      => ''
		],
		'contentTitle'     => [
			'type'         => 'string',
			'default'      => ''
		],
		'contentUrl'       => [
			'type'         => 'string',
			'default'      => ''
		],
		'insertAs'         => [
			'type'         => 'string',
			'default'      => 'lightbox'
		],
		'openWith'         => [
			'type'         => 'string',
			'default'      => 'button'
		],
		'iframeSettings'   => [
			'type'         => 'string',
			'default'      => json_encode([
				'widthValue'  => 100,
				'widthUnit'   => '%',
				'heightValue' => 400,
				'heightUnit'  => 'px'
			])
		],
		'lightboxSettings' => [
			'type'         => 'string',
			'default'      => json_encode([
				'title'       => '',
				'widthValue'  => 90,
				'widthUnit'   => 'vw',
				'heightValue' => 90,
				'heightUnit'  => 'vh',
				'effect'      => 'fade'
			])
		],
		'pageSettings'     => [
			'type'         => 'string',
			'default'      => json_encode([
				'target'   => '_blank'
			])
		],
		'buttonSettings'   => [
			'type'         => 'string',
			'default'      => json_encode([
				'text'     => __( 'Open', 'uncanny-learndash-reporting' ),
				'size'     => 'normal'
			])
		],
		'imageSettings'    => [
			'type'         => 'string',
			'default'      => json_encode([
				'id'       => '',
				'title'    => '',
				'sizes'    => [],
				'url'      => '',
				'isLoading' => false
			])
		],
		'linkSettings'     => [
			'type'         => 'string',
			'default'      => json_encode([
				'text'     => __( 'Open', 'uncanny-learndash-reporting' )
			])
		],
	],
	'render_callback' => 'render_tincanny_content'
]);

function render_tincanny_content( $attributes ){
	ob_start();

	// Check if the shortcode exists, otherwise do nothing
	if ( shortcode_exists( 'vc_snc' ) ){

		// Decode JSON attributes
		$attributes[ 'iframe' ]   = json_decode( $attributes[ 'iframeSettings' ], true );
		$attributes[ 'lightbox' ] = json_decode( $attributes[ 'lightboxSettings' ], true );
		$attributes[ 'page' ]     = json_decode( $attributes[ 'pageSettings' ], true );
		$attributes[ 'button' ]   = json_decode( $attributes[ 'buttonSettings' ], true );
		$attributes[ 'image' ]    = json_decode( $attributes[ 'imageSettings' ], true );
		$attributes[ 'link' ]     = json_decode( $attributes[ 'linkSettings' ], true );

		// Get attributes
		$status  = $attributes[ 'status' ];

		$content = (object) [
			'id'    => $attributes[ 'contentId' ],
			'title' => $attributes[ 'contentTitle' ],
			'url'   => $attributes[ 'contentUrl' ]
		];

		$settings = (object) [
			'insert_as' => $attributes[ 'insertAs' ],
			'open_with' => $attributes[ 'openWith' ],
			'iframe'    => (object) [
				'width_value'  => isset( $attributes[ 'iframe' ][ 'widthValue' ] ) ? $attributes[ 'iframe' ][ 'widthValue' ] : '',
				'width_unit'   => isset( $attributes[ 'iframe' ][ 'widthUnit' ] ) ? $attributes[ 'iframe' ][ 'widthUnit' ] : '',
				'height_value' => isset( $attributes[ 'iframe' ][ 'heightValue' ] ) ? $attributes[ 'iframe' ][ 'heightValue' ] : '',
				'height_unit'  => isset( $attributes[ 'iframe' ][ 'heightUnit' ] ) ? $attributes[ 'iframe' ][ 'heightUnit' ] : '',
			],
			'lightbox'  => (object) [
				'title'        => isset( $attributes[ 'lightbox' ][ 'title' ] ) ? $attributes[ 'lightbox' ][ 'title' ] : '',
				'width_value'  => isset( $attributes[ 'lightbox' ][ 'widthValue' ] ) ? $attributes[ 'lightbox' ][ 'widthValue' ] : '',
				'width_unit'   => isset( $attributes[ 'lightbox' ][ 'widthUnit' ] ) ? $attributes[ 'lightbox' ][ 'widthUnit' ] : '',
				'height_value' => isset( $attributes[ 'lightbox' ][ 'heightValue' ] ) ? $attributes[ 'lightbox' ][ 'heightValue' ] : '',
				'height_unit'  => isset( $attributes[ 'lightbox' ][ 'heightUnit' ] ) ? $attributes[ 'lightbox' ][ 'heightUnit' ] : '',
				'effect'       => isset( $attributes[ 'lightbox' ][ 'effect' ] ) ? $attributes[ 'lightbox' ][ 'effect' ] : '',
			],
			'page'       => (object) [
				'target'       => isset( $attributes[ 'page' ][ 'target' ] ) ? $attributes[ 'page' ][ 'target' ] : '',
			],
			'button'     => (object) [
				'text'         => isset( $attributes[ 'button' ][ 'text' ] ) ? $attributes[ 'button' ][ 'text' ] : '',
				'size'         => isset( $attributes[ 'button' ][ 'size' ] ) ? $attributes[ 'button' ][ 'size' ] : '',
			],
			'image'      => (object) [
				'id'           => isset( $attributes[ 'image' ][ 'id' ] ) ? $attributes[ 'image' ][ 'id' ] : '',
				'title'        => isset( $attributes[ 'image' ][ 'title' ] ) ? $attributes[ 'image' ][ 'title' ] : '',
				'sizes'        => isset( $attributes[ 'image' ][ 'sizes' ] ) ? $attributes[ 'image' ][ 'sizes' ] : '',
				'url'          => isset( $attributes[ 'image' ][ 'url' ] ) ? $attributes[ 'image' ][ 'url' ] : '',
			],
			'link'       => (object) [
				'text'         => isset( $attributes[ 'link' ][ 'text' ] ) ? $attributes[ 'link' ][ 'text' ] : '',
			],
		];

		// Shortcode Parameters
		// We need to edit the parameters to match the current ones in \TINCANNYSNC\Shortcode
		
		// Global parameters
		$shortcode_parameters = (object) [
			'item_id'    => $content->id,
			'item_name'  => $content->title,
		];

		// Embed type
		// This will define if we have to add an iframe, lightbox or page
		if ( $settings->insert_as == 'page' ){
			// Use the target (_blank or _self)
			$shortcode_parameters->embed_type = $settings->page->target;
		}
		else {
			$shortcode_parameters->embed_type = $settings->insert_as;
		}

		// Content url
		// We have to check if it's an iframe because it uses a different
		// parameter for the url
		if ( $settings->insert_as == 'iframe' ){
			$shortcode_parameters->src  = $content->url;
		}
		elseif ( in_array( $settings->insert_as, [ 'lightbox', 'page' ] ) ){
			$shortcode_parameters->href = $content->url;
		}

		// Dimensions
		// Only if the user is using iframe or lightbox
		if ( in_array( $settings->insert_as, [ 'iframe', 'lightbox' ] ) ){
			if ( $settings->insert_as == 'iframe' ){
				$width  = $settings->iframe->width_value . $settings->iframe->width_unit;
				$height = $settings->iframe->height_value . $settings->iframe->height_unit;
			}
			elseif ( $settings->insert_as == 'lightbox' ){
				$width  = $settings->lightbox->width_value . $settings->lightbox->width_unit;
				$height = $settings->lightbox->height_value . $settings->lightbox->height_unit;
			}

			$shortcode_parameters->width  = $width;
			$shortcode_parameters->height = $height;
		}

		// Frameborder (only for iframes)
		if ( $settings->insert_as == 'iframe' ){
			$shortcode_parameters->frameborder  = '0';
		}

		// Lightbox transition and title
		if ( $settings->insert_as == 'lightbox' ){
			// Set default parameters
			$shortcode_parameters->slider_script      = 'nivo';
			$shortcode_parameters->colorbox_scrollbar = 'no';

			// Check if it has title
			if ( ! empty( $settings->lightbox->title ) ){
				$shortcode_parameters->title = $settings->lightbox->title;
			}

			// Set transition
			$shortcode_parameters->nivo_transition = $settings->lightbox->effect;
		}

		// Add "Open with" parameters
		// We're going to add this only if "Insert as" is "lightbox" or "page"
		if ( in_array( $settings->insert_as, [ 'lightbox', 'page' ] ) ){
			if ( $settings->open_with == 'button' ){
				// Button text
				$shortcode_parameters->button_text = $settings->button->text;

				// Button size
				if ( $settings->button->size == 'small' ){
					$shortcode_parameters->button = 'small';
				}
				elseif ( $settings->button->size == 'normal' ){
					$shortcode_parameters->button = 'medium';
				}
				elseif ( $settings->button->size == 'big' ){
					$shortcode_parameters->button = 'large';
				}
			}
			elseif ( $settings->open_with == 'image' ){
				// Type of button
				$shortcode_parameters->button = 'url';

				// Image
				$shortcode_parameters->button_image = $settings->image->url;
			}
			elseif ( $settings->open_with == 'link' ){
				// Type of button
				$shortcode_parameters->button = 'text';

				// Button text
				$shortcode_parameters->button_text = $settings->link->text;
			}
		}

		// Add the wrapper paratemer
		$shortcode_parameters->wrapper = '0';

		// Create shortcode
		$shortcode_parameters_string = '';
		foreach ( $shortcode_parameters as $key => $value ){
			// Create a pair of key and value but as a string,
			// like: key="value". We will merge these into a single string
			// and then add the string to another one that includes the shortcode id
			$shortcode_parameters_string .= ' ' . $key . '="' . $value . '"';
		}

		$shortcode = "[vc_snc {$shortcode_parameters_string}]";

		// Create array with the container classes
		$css_classes = [ 'uo-tincanny-content' ];

		if ( isset( $attributes[ 'className' ] ) ){
			$css_classes[] = $attributes[ 'className' ];
		}

		?>

		<div class="<?php echo implode( ' ', $css_classes ); ?>">
			<?php echo do_shortcode( $shortcode ); ?>
		</div>

		<?php
	} 

	return ob_get_clean();
}

?>