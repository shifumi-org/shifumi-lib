<div class="tclr-classic-editor-content-library">
	<div id="snc-content_library_wrap" class="wrap snc_TB">
		<span class="dashicons dashicons-admin-media"></span> <div class="title"><?php _e( 'Content Library', 'uncanny-learndash-reporting' ); ?></div>

		<div class="clear"></div>

		<div class="tclr-classic-editor-content-library__box">
			<div class="tclr-classic-editor-content-library__search">
				<div class="tclr-classic-editor-content-library__search-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
						<path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
					</svg>
				</div>
				<input type="text" class="tclr-classic-editor-content-library__search-input" id="tclr-classic-editor-content-library-search" placeholder="<?php _e( 'Search content', 'uncanny-learndash-reporting' ); ?>">
			</div>
			<div class="tclr-classic-editor-content-library__list">
				<table class="widefat">
				<tbody>
					<tr class="tclr-classic-editor-content-library__item-no-results">
						<td><?php _e( 'No results found', 'uncanny-learndash-reporting' ); ?></td>
					</tr>
					<?php if ( !empty( $posts ) ) : foreach( $posts as $post ) : if ( !empty( $post ) ) : ?>
					<tr class="tclr-classic-editor-content-library__item" id="item_<?php echo $post->ID ?>" data-item_id="<?php echo $post->ID ?>" data-item_name="<?php echo $post->file_name; ?>">
						<td>
							<a class="content_title" href="#" data-item_id="<?php echo $post->ID ?>" data-item_name="<?php echo $post->file_name ?>"><?php echo $post->file_name ?></a>
							<span style="float:right">

								<?php if ( empty( $content_library_vc_mode ) ) : ?>
								<a href="#" class="show" data-item_id="<?php echo $post->ID ?>"><?php _e( 'Show', 'uncanny-learndash-reporting' ); ?></a> |
								<?php else : ?>
								<a href="#" class="choose" data-item_id="<?php echo $post->ID ?>" data-item_name="<?php echo $post->file_name ?>"><?php _e( 'Choose', 'uncanny-learndash-reporting' ); ?></a> |
								<?php endif; ?>
								
								<a href="#" class="delete" data-item_id="<?php echo $post->ID ?>"><?php _e( 'Delete', 'uncanny-learndash-reporting' ); ?></a>
							</span>

							<div class="embed_information" data-item_id="<?php echo $post->ID ?>">
								<?php
									$snc_post = $post;
									if ( ! isset( $content_library_vc_mode ) || !$content_library_vc_mode )
										include( SnC_PLUGIN_DIR . 'views/embed_information.php' );
								?>

							</div>
						</td>
					 </tr>
					<?php endif; endforeach; endif; ?>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>