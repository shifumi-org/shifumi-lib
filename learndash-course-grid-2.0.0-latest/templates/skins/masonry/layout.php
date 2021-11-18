<?php 
/**
 * Available variables:
 * 
 * $posts   Array of WP_Post objects, result of the WP_Query->get_posts()
 * $atts    Shortcode/Block editor attributes that call this template
 */
?>
<div class="items-wrapper <?php echo esc_attr( $atts['skin'] ); ?>">
    <?php foreach ( $posts as $post ) : ?>
        <?php learndash_course_grid_load_card_template( $atts, $post ); ?>
    <?php endforeach; ?>
</div>