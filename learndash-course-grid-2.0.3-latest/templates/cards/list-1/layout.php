<div class="item list-1">
	<article id="post-<?php echo esc_attr( $post->ID ); ?>" <?php post_class( 'post', $post->ID ); ?>>
		<?php if ( $atts['thumbnail'] == true ) : ?>
            <div class="thumbnail">
            <?php if ( $atts['meta'] == true && ! empty( $reviews ) ) : ?>
                <div class="reviews">
                    <span class="icon dashicons dashicons-star-filled"></span>
                    <span class="number"><?php echo esc_html( $reviews ); ?></span>
                </div>
            <?php endif; ?>

            <?php if ( $video == true && ! empty( $video_embed_code ) ) : ?>
                <div class="video">
                    <?php echo wp_kses( $video_embed_code, 'learndash_course_grid_embed_code' ); ?>
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
            </div>
		<?php endif; ?>
        <div class="content">
            <?php if ( $atts['ribbon'] == true && ! empty( $ribbon_text ) ) : ?>
                <div class="ribbon">
                    <?php echo esc_html( $ribbon_text ); ?>
                </div>
            <?php endif; ?>
            <?php if ( $atts['meta'] == true && ! empty( $categories ) ) : ?>
                <div class="categories">
                    <?php echo esc_html( $categories ); ?>
                </div>
            <?php endif; ?>
            <?php if ( $atts['meta'] == true && ! empty( $author ) ) : ?>
                <div class="author">
                    <img class="avatar" src="<?php echo esc_url( $author['avatar']) ; ?>" alt="<?php echo $author['name'] ?>">
                    <span><?php printf( _x( 'By %s', 'Author name', 'learndash-course-grid' ), '<span class="name">' . $author['name'] . '</span>' ); ?></span>
                </div>
            <?php endif; ?>
            <h3 class="entry-title">
                <?php if ( $atts['title_clickable'] == true ) : ?>
                    <a href="<?php echo esc_url( $button_link ); ?>">
                <?php endif; ?>
                    <?php echo esc_html( $title ); ?>
                <?php if ( $atts['title_clickable'] == true ) : ?>
                    </a>
                <?php endif; ?>
            </h3>
            <?php if ( $atts['meta'] == true ) : ?>
                <div class="meta">
                    <?php if ( $students ) : ?>
                        <div class="students">
                            <span class="icon dashicons dashicons-groups"></span>
                            <span><?php printf( _nx( '%s Student', '%s Students', $students['count'], 'Total students', 'learndash-course-grid' ), $students['count'] ); ?></span>
                        </div>
                    <?php endif; ?>
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
                    <?php if ( $forums ) : ?>
                        <div class="forums">
                         <span class="icon dashicons dashicons-buddicons-forums"></span>
                            <span><?php printf( _nx( '%s Forum', '%s Forums', $forums['count'], 'Total forums', 'learndash-course-grid' ), $forums['count'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $quizzes ) : ?>
                        <div class="quizzes">
                            <span class="icon dashicons dashicons-format-chat"></span>
                            <span><?php printf( _nx( '%s Quiz', '%s Quizzes', $quizzes['count'], 'Total quizzes', 'learndash-course-grid' ), $quizzes['count'] ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
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
                    <div class="arrow"><a href="<?php echo esc_url( $button_link ); ?>"><span class="icon dashicons dashicons-arrow-right-alt"></span></a></div>
                </div>
            <?php endif; ?>
        </div>
	</article>
</div>