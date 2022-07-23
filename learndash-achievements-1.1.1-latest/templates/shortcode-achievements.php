<div class="ld-achievements">
	<?php use LearnDash\Achievements\Settings;

	if ( $atts['show_title'] ) : ?>
		<h2><?php esc_html_e( 'My Achievements', 'learndash-achievements' ); ?></h2>
	<?php endif; ?>

	<?php
	$settings         = get_option( 'learndash_achievements_settings_badge', Settings::get_default_value() );
	$size             = isset( $settings['size'] ) ? absint( $settings['size'] ) : 40;
	$tooltip_fontsize = isset( $settings['tooltip_font_size'] ) ? absint( $settings['tooltip_font_size'] ) : 12;
	if ( $tooltip_fontsize ) {
		?>
			<style type="text/css">
				.ld-achievement-tooltip .ld-achievement-tooltiptext{
					font-size: <?php echo absint( $tooltip_fontsize ) ?>px;
				}
			</style>
		<?php
	}
	?>
	<?php foreach ( $achievements as $achievement ) : ?>

		<?php

		$post = get_post( $achievement->post_id );
		if ( ! is_object( $post ) || 'publish' !== $post->post_status ) {
			continue;
		}
		$image   = get_post_meta( $achievement->post_id, 'image', true );
		$title   = $post->post_title;
		$content = $post->post_content;

		?>
		<span class="ld-achievement-image ld-achievement-tooltip">
					<span class="ld-achievement-tooltiptext">
					<?php
					echo esc_html( $title );
					?>
						</span>
					<img width="<?php echo absint( $size ); ?>px" src="<?php echo esc_attr( $image ); ?>" alt="<?php echo esc_attr( $content ); ?>">
		<?php echo $achievement->c > 1 ? ' x' . esc_html( $achievement->c ) . '' : ''; ?>
				</span>
	<?php endforeach; ?>
</div>
