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
		$tab_active = 'uncanny-tincanny-kb';
		include Config::get_template( 'admin-header.php' );

		?>

		<div class="ulgm-help">
			<?php

			$kb_category = 'tin-canny-learndash-reporting';
			$json        = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/kb/' . $kb_category . '?wpnonce=' . wp_create_nonce( time() ) );

			if ( ! is_wp_error( $json ) ) {
				if ( 200 === wp_remote_retrieve_response_code( $json ) ) {
					$data = json_decode( $json['body'], true );
					if ( $data ) {
						echo $data;
					}
				}
			}

			?>
		</div>

		<?php
		$show_support_link = apply_filters( 'uo_show_support_link_tincanny', true );
		if ( $license_is_active && $show_support_link) {

			?>

			<a href="<?php echo menu_page_url( 'uncanny-tincanny-kb', false ) . '&send-ticket=true'; ?>">
				<?php _e( "I can't find the answer to my question.", 'uncanny-learndash-reporting' ); ?>
			</a>

		<?php } ?>
	</div>
</div>
