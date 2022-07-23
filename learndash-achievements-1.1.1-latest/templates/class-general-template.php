<?php

namespace LearnDash\Achievements\Template;

/**
 * Class General_Template
 *
 * @package LearnDash\Achievements\Template
 */
class General_Template {
	/**
	 * Render the achievements list.
	 *
	 * @param array $achievements The achievements data.
	 * @param bool  $with_tooltip On/off the tooltip.
	 */
	public static function render_badges( $achievements, $with_tooltip = false ) {
		?>
		<div class="ld-achievements">
			<?php foreach ( $achievements as $achievement ) : ?>
				<?php
				$post    = get_post( $achievement->post_id );
				$image   = get_post_meta( $achievement->post_id, 'image', true );
				$title   = $post->post_title;
				$content = $post->post_content;
				?>
				<span class="ld-achievement-image ld-achievement-tooltip">
					<?php if ( $with_tooltip ) : ?>
						<span class="ld-achievement-tooltiptext"><?php echo esc_html( $title ); ?></span>
					<?php endif; ?>
					<img src="<?php echo esc_attr( $image ); ?>" title="<?php echo esc_attr( $title ); ?>" alt="<?php echo esc_attr( $content ); ?>">
				</span>

			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render the leaderboard table
	 *
	 * @param array $leaders      The leaders data.
	 * @param false $with_tooltip Toggle the tooltip on/off.
	 */
	public static function render_leaderboard( $leaders, $with_tooltip = false ) {
		?>
		<div class="ld-achievements-leaderboard">
			<table>
				<thead>
				<tr>
					<th><?php esc_html_e( 'User', 'learndash-achievements' ); ?></th>
					<th><?php esc_html_e( 'Badges Earned', 'learndash-achievements' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( empty( $leaders ) ) : ?>
					<tr style="">
						<td colspan="3">No data available</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $leaders as $key => $leader ) : ?>
						<?php
						$user = get_user_by( 'id', $leader['user_id'] );
						if ( ! is_object( $user ) ) {
							continue;
						}
						?>
						<tr <?php echo 0 !== $key % 2 ? 'class="odd"' : null; ?>>
							<td><?php echo esc_html( $user->display_name ); ?></td>
							<td>
								<?php foreach ( $leader['post_ids'] as $post_id ) : ?>
									<?php
									$image   = get_post_meta( $post_id, 'image', true );
									$post    = get_post( $post_id );
									$title   = $post->post_title;
									$content = $post->post_content;
									?>
									<span class="ld-achievement-tooltip">
									<?php if ( $with_tooltip ) : ?>
										<span class="ld-achievement-tooltiptext"><?php echo esc_html( $title ); ?></span>
									<?php endif; ?>
									<img width="40" src="<?php echo esc_url_raw( $image ); ?>" alt="<?php esc_attr( $content ); ?>"/>
								</span>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php
	}

	/**
	 * Render the achievement as a table, use in user-profile.php page.
	 *
	 * @param array $achievements The achievements data.
	 * @param bool  $show_remove  Show the remove ability.
	 */
	public static function render_badges_table( $achievements, $show_remove = true ) {
		?>
		<div class="ld-achievements-table">
			<?php foreach ( $achievements as $achievement ) : ?>
				<?php
				$post  = get_post( $achievement->post_id );
				$image = get_post_meta( $achievement->post_id, 'image', true );
				if ( ! is_object( $post ) ) {
					continue;
				}

				$title   = $post->post_title;
				$message = get_post_meta( $post->ID, 'achievement_message', true );
				?>
				<div class="inner-item">
					<span class="ld-achievement-tooltip">
						<img src="<?php echo esc_attr( $image ); ?>"/>
						<span class="ld-achievement-tooltiptext"><?php echo esc_html( $message ); ?></span>
					</span>
					<div class="actions">
						<span>
						<?php
						echo esc_html( $title );
						echo $achievement->c > 1 ? ' (' . esc_html( $achievement->c ) . ')' : ''
						?>
							</span>
						<?php if ( $show_remove ) : ?>
							<a href="#" class="learndash-achievement-delete" data-id="<?php echo esc_attr( $achievement->ids ); ?>">
								<?php esc_html_e( 'Remove', 'learndash-achievements' ); ?>
							</a>
							<?php
							wp_nonce_field(
								'learndash_achievements_remove_user_badge_' . $achievement->ids,
								'learndash_achievement_nonce'
							);
							?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			<?php if ( $show_remove ) : ?>
				<script type="text/javascript">
					jQuery('.learndash-achievement-delete').on('click', function (e) {
						e.preventDefault();
						let that = jQuery(this)
						let parent = that.closest('.inner-item');
						jQuery.ajax({
							url: ajaxurl,
							method: 'POST',
							beforeSend: function () {
								that.attr('disabled', 'disabled')
							},
							data: {
								id: that.data('id'),
								action: 'learndash_achievements_remove_user_badge',
								nonce: parent.find('input[name="learndash_achievement_nonce"]').val()
							}
						}).done(function () {
							parent.remove()
						})
					})
				</script>
			<?php endif ?>
		</div>
		<?php
	}
}
