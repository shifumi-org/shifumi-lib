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
						<?php foreach ( $leader['images'] as $image ) : ?>
							<img width="40" src="<?php echo esc_url_raw( $image ); ?>">
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
