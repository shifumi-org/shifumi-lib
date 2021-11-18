<div class="item grid-2">
	<article id="post-<?php echo esc_attr( $post->ID ); ?>" <?php post_class( 'post', $post->ID ); ?>>
		<?php if ( $atts['thumbnail'] == true ) : ?>
            <div class="thumbnail">
                <?php if ( $atts['ribbon'] == true && ! empty( $ribbon_text ) ) : ?>
                    <div class="<?php echo esc_attr( $ribbon_class ); ?>">
                        <?php echo esc_html( $ribbon_text ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( $video == true ) : ?>
                    <div class="video">
                        <?php echo htmlspecialchars_decode( $video_embed_code ); ?>
                    </div>
                <?php elseif( has_post_thumbnail( $post->ID ) ) : ?>
                    <div class="image">
                        <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                            <?php echo get_the_post_thumbnail( $post->ID, $atts['thumbnail_size'] ); ?>
                        </a>
                    </div>
                <?php elseif( ! has_post_thumbnail( $post->ID ) ) : ?>
                    <div class="image">
                        <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                            <img alt="" src="<?php echo LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'img/thumbnail.jpg'; ?>"/>
                        </a>
                    </div>
                <?php endif;?>

                <div class="arrow"><a href="<?php echo esc_url( $button_link ); ?>"><span class="icon dashicons dashicons-arrow-right-alt2"></span></a></div>
            </div>
		<?php endif; ?>
		<?php if ( $atts['content'] == true ) : ?>
			<div class="content">
                <?php if ( $atts['meta'] == true ) : ?>
                    <div class="meta">
                        <?php if ( $author ) : ?>
                            <div class="author"> 
                                <img class="avatar" src="<?php echo esc_url( $author['avatar'] ) ; ?>" alt="<?php echo $author['name'] ?>">
                                <div class="wrapper">
                                    <span class="name"><?php echo esc_html( $author['name'] ); ?></span>
                                    <span class="inner-wrapper">
                                        <?php if ( $duration ) : ?>
                                            <span class="duration"><?php printf( __( 'Duration: %s', 'learndash-course-grid' ), $duration ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $lessons ) : ?>
                                            <span class="lessons"><?php printf( __( '%d Lessons', 'learndash-course-grid' ), $lessons['count'] ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
                <?php if ( $atts['description'] == true && ! empty( $description ) ) : ?>
                    <div class="entry-content">
                        <?php echo wp_kses( $description, 'post' ); ?>
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