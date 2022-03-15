<?php

namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wrap">
    <div class="ulgm">

    	<?php

        // Add admin header and tabs
        $tab_active = 'uncanny-tincanny-plugins';
        include Config::get_template( 'admin-header.php' );

        ?>

        <div class="ulgm-learndash-plugins">
			<?php

			$product_id = 4113;
			$json       = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/download/' . $product_id . '?wpnonce=' . wp_create_nonce( time() ) );

			if ( ! is_wp_error( $json ) ) {
				if ( 200 === $json['response']['code'] ) {
					$data = json_decode( $json['body'], TRUE );
					if ( $data ) {
						echo $data;
					}
				}
			}
			?>
		</div>

	</div>
</div>
