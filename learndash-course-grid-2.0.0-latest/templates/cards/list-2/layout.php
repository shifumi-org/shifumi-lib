<div class="item <?php echo esc_attr( $atts['card'] ); ?>">
	<article id="post-<?php echo esc_attr( $post->ID ); ?>" <?php post_class( 'post', $post->ID ); ?>>
		<?php if ( $atts['thumbnail'] == true ) : ?>
            <div class="thumbnail">
            <?php if ( $video == true && ! empty( $video_embed_code ) ) : ?>
                <div class="video">
                    <?php echo wp_kses( $video_embed_code, 'learndash_course_grid_embed_code' ); ?>
                </div>
            <?php elseif ( has_post_thumbnail( $post->ID ) ) : ?>
                <div class="image">
                    <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                        <?php echo get_the_post_thumbnail( $post->ID, $atts['thumbnail_size'] ); ?>
                    </a>
                </div>
            <?php elseif ( ! has_post_thumbnail( $post->ID ) ) : ?>
                <div class="image">
                    <a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
                        <img alt="" src="<?php echo LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'img/thumbnail.jpg'; ?>"/>
                    </a>
                </div>
            <?php endif;?>
            </div>
		<?php endif; ?>
        <div class="content">
            <div class="wrapper">
                <div class="title-wrapper">
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
                    <div class="meta top-meta">
                        <?php if ( $atts['meta'] == true && ! empty( $author ) ) : ?>
                            <div class="author">
                                <span class="name"><?php echo esc_html( $author['name'] ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $atts['meta'] == true && ! empty( $categories ) ) : ?>
                            <div class="categories">
                                <?php echo esc_html( $categories ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ( $atts['ribbon'] == true && ! empty( $ribbon_text ) ) : ?>
                    <div class="ribbon">
                        <?php echo esc_html( $ribbon_text ); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ( $atts['description'] == true && ! empty( $description ) ) : ?>
                <div class="entry-content">
                    <?php echo wp_kses( $description, 'post' ); ?>
                </div>
            <?php endif; ?>
            <?php if ( $atts['meta'] == true ) : ?>
                <div class="meta bottom-meta">
                    <?php if ( $duration ) : ?>
                        <div class="duration">
                            <span class="icon dashicons dashicons-clock"></span>
                            <span><?php echo esc_html( $duration ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $lessons ) : ?>
                        <div class="lessons">
                            <span class="icon dashicons dashicons-text-page"></span>
                            <span><?php printf( _nx( '%s Lesson', '%s Lessons', $lessons['count'], 'Total lessons', 'learndash-course-grid' ), $lessons['count'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $students ) : ?>
                        <div class="students">
                            <span class="icon dashicons dashicons-groups"></span>
                            <span><?php printf( _nx( '%s Student', '%s Students', $students['count'], 'Total students', 'learndash-course-grid' ), $students['count'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $reviews ) : ?>
                        <?php 
                            $reviews_floored = floor( $reviews );
                            $reviews_ceil    = ceil( $reviews );
                            $reviews_number  = number_format( $reviews, 1 );
                            $reviews_max = 5;
                        ?>
                        <div class="reviews">
                            <span class="label"><?php printf( '%s', $reviews_number ); ?></span>
                            <span class="stars">
                                <?php for ( $i = 1; $i <= $reviews_max; $i++ ) : ?> 
                                    <?php
                                        if ( $i <= $reviews_floored ) {
                                            $star_class = 'star-filled';
                                        } elseif ( $i > $reviews_floored && floatval( $reviews_number ) > $reviews_floored && $i <= $reviews_ceil ) {
                                            $star_class = 'star-half';
                                        } else {
                                            $star_class = 'star-empty';
                                        }
                                    ?>
                                    <span class="icon dashicons dashicons-<?php echo esc_attr( $star_class ); ?>"></span>
                                <?php endfor; ?>
                            </span>
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
        </div>
	</article>
</div>