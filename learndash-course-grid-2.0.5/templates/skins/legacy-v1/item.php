<?php
/**
 * @package nmbs
 */
$col   = empty( $shortcode_atts['col'] ) ? LEARNDASH_COURSE_GRID_COLUMNS : intval( $shortcode_atts['col'] );
$col   = $col > 6 ? 6 : $col;
$smcol = $col == 1 ? 1 : $col / 2;
$col   = 12 / $col;
$smcol = intval( ceil( 12 / $smcol ) );
$col   = is_float( $col ) ? number_format( $col, 1 ) : $col;
$col   = str_replace( '.', '-', $col );

global $post; $post_id = $post->ID;

$post_atts = learndash_course_grid_prepare_template_post_attributes( $post, $shortcode_atts );

extract( $post_atts );

$post_type = get_post_type( $post->ID );

$course_id = $post_id;
$user_id   = get_current_user_id();

// Retrive oembed HTML if URL provided
if ( preg_match( '/^http/', $video_embed_code ) ) {
	$video_embed_code = wp_oembed_get( $video_embed_code, array( 'height' => 600, 'width' => 400 ) );
}

$course_options = get_post_meta( $post_id, "_sfwd-courses", true );

/**
 * Filter: individual grid class
 * 
 * @param int 	$course_id Course ID
 * @param array $course_options Course options
 * @var string
 */
$grid_class = apply_filters( 'learndash_course_grid_class', '', $course_id, $course_options );

$course_class = apply_filters( 'learndash_course_grid_course_class', '', $course_id, $course_options );

$thumb_size = isset( $shortcode_atts['thumb_size'] ) && ! empty( $shortcode_atts['thumb_size'] ) ? $shortcode_atts['thumb_size'] : 'medium';

ob_start();
?>
<div class="ld_course_grid col-sm-<?php echo $smcol; ?> col-md-<?php echo $col; ?> <?php echo esc_attr( $grid_class ); ?>">
	<article id="post-<?php the_ID(); ?>" <?php post_class( $course_class . ' thumbnail course' ); ?>>
		<?php if ( $shortcode_atts['show_thumbnail'] == 'true' ) : ?>
			<?php if ( ! empty( $ribbon_text ) ) : ?>
			<div class="<?php echo esc_attr( $ribbon_class ); ?>">
				<?php echo wp_kses_post( $ribbon_text ); ?>
			</div>
			<?php endif; ?>

			<?php if ( 1 == $video && ! empty( $video_embed_code ) ) : ?>
			<div class="ld_course_grid_video_embed">
			<?php echo $video_embed_code; ?>
			</div>
			<?php elseif( has_post_thumbnail() ) :?>
			<a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
				<?php the_post_thumbnail( $thumb_size ); ?>
			</a>
			<?php else : ?>
			<a href="<?php echo esc_url( $button_link ); ?>" rel="bookmark">
				<img alt="" src="<?php echo LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'img/thumbnail.jpg'; ?>"/>
			</a>
			<?php endif;?>
		<?php endif; ?>

		<?php if ( $shortcode_atts['show_content'] == 'true' ) : ?>
			<div class="caption">
				<h3 class="entry-title"><?php the_title(); ?></h3>
				<?php if ( ! empty( $description ) ) : ?>
				<div class="entry-content"><?php echo do_shortcode( htmlspecialchars_decode( $description ) ); ?></div>
				<?php endif; ?>
				<div class="ld_course_grid_button"><a class="btn btn-primary" role="button" href="<?php echo esc_url( $button_link ); ?>" rel="bookmark"><?php echo esc_attr( $button_text ); ?></a></div>
				<?php if ( isset( $shortcode_atts['progress_bar'] ) && $shortcode_atts['progress_bar'] == 'true' ) : ?>
					<?php if ( $post_type == 'sfwd-courses' ) : ?>
						<?php echo do_shortcode( '[learndash_course_progress course_id="' . get_the_ID() . '" user_id="' . get_current_user_id() . '"]' ); ?>
					<?php elseif ( $post_type == 'groups' ) : ?>
						<div class="learndash-wrapper learndash-widget">
						<?php $progress = learndash_get_user_group_progress( $post_id, $user_id ); ?>
						<?php learndash_get_template_part(
							'modules/progress-group.php',
							array(
								'context'   => 'group',
								'user_id'   => $user_id,
								'group_id'  => $post_id,
							),
							true
						); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div><!-- .entry-header -->
		<?php endif; ?>
	</article><!-- #post-## -->
</div><!-- .ld_course_grid -->
<?php
/**
 * Tag to detect if v1 course grid exists on a page
 * 
 * Make sure to include this tag when using custom template or modify the template via filter hook.
 */ 
$tag = '<!-- LearnDash Course Grid v1 -->';

/**
 * Filter: course grid HTML output
 *
 * @param string $output         Individual course grid HTML output
 * @param object $post 	         LD course WP_Post object
 * @param array  $shortcode_atts Shortcode attributes used for this course grid output
 * @param int    $user_id        Current user ID this course grid is displayed to
 * @return string 				 Filtered course grid HTML output
 */
echo apply_filters( 'learndash_course_grid_html_output', $tag . ob_get_clean(), $post, $shortcode_atts, $user_id );