<div class="item grid-3">
	<article id="post-<?php echo esc_attr( $post->ID ); ?>" <?php post_class( 'post', $post->ID ); ?>>
        <?php if ( $atts['thumbnail'] == true ) : ?>
            <div class="thumbnail">
                <?php if ( $video == true && ! empty( $video_embed_code ) ) : ?>
                    <div class="video">
                        <?php echo wp_kses( $video_embed_code, 'learndash_course_grid_embed_code' ); ?>
                    </div>
                <?php elseif( has_post_thumbnail( $post->ID ) ) : ?>
                    <div class="thumbnail">
                        <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                            <?php echo get_the_post_thumbnail( $post->ID, $atts['thumbnail_size'] ); ?>
                        </a>
                    </div>
                <?php elseif( ! has_post_thumbnail( $post->ID ) ) : ?>
                    <div class="thumbnail">
                        <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                            <img alt="" src="<?php echo LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'img/thumbnail.jpg'; ?>"/>
                        </a>
                    </div>
                <?php endif;?>
            </div>
        <?php endif; ?>
		<?php if ( $atts['content'] == true ) : ?>
			<div class="content">
                <?php if ( $atts['title'] == true ) : ?>
                    <h3 class="entry-title">
                        <?php if ( $atts['title_clickable'] == true ) : ?>
                            <a href="<?php echo esc_url( $button_link ); ?>">
                        <?php endif; ?>
                            <?php echo esc_html( $title ); ?>
                        <?php if ( $atts['title_clickable'] == true ) : ?>
                            </a>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>
                <?php if ( $atts['meta'] ) : ?>
                    <div class="meta">
                        <?php if ( $author ) : ?>
                            <div class="author">
                                <img src="<?php echo esc_url( $author['avatar'] ); ?>" alt="<?php echo esc_attr( $author['name'] ); ?>">
                                <span><?php printf( __( 'By %s' ), '<span class="name">' . $author['name'] . '</span>' ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $categories ) : ?>
                            <div class="categories">
                                <?php echo esc_html( $categories ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ( $atts['description'] == true && ! empty( $description ) ) : ?>
                    <div class="entry-content">
                        <?php echo wp_kses( $description, 'post' ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( $atts['meta'] == true ) : ?>
                    <div class="meta price-wrapper">
                        <div class="trial">
                            <?php if ( $trial_price && $trial_duration ) : ?>
                                <span><?php printf( _x( '%s for %s then', 'Price X for X duration', 'learndash-course-grid' ), $currency . $trial_price, $trial_duration ); ?></span>
                            <?php else: ?>
                                <span><?php _e( 'Price', 'learndash-course-grid' ); ?></span>
                            <?php endif; ?>
                            </div>
                        <?php if ( $price_text ) : ?>
                            <div class="price">
                                <?php echo esc_html( $price_text ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ( $atts['progress_bar'] == true && defined( 'LEARNDASH_VERSION' ) ) : ?>
					<?php if ( $post->post_type == 'sfwd-courses' ) : ?>
						<?php echo do_shortcode( '[learndash_course_progress course_id="' . $post->ID . '" user_id="' . $user_id . '"]' ); ?>
					<?php elseif ( $post->post_type == 'groups' ) : ?>
						<div class="learndash-wrapper learndash-widget">
						<?php $progress = learndash_get_user_group_progress( $post->ID, $user_id ); ?>
						<?php learndash_get_template_part(
							'modules/progress-group.php',
							array(
								'context'   => 'group',
								'user_id'   => $user_id,
								'group_id'  => $post->ID,
							),
							true
						); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
                <?php if ( $atts['button'] == true ) : ?>
					<div class="button">
						<a role="button" href="<?php echo esc_url( $button_link ); ?>" rel="bookmark"><?php echo esc_attr( $button_text ); ?></a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</article>
</div>