<?php
/**
 * LearnDash LD30 Displays the breadcrumbs
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires before the breadcrumbs.
 *
 * @since 3.0.0
 */
do_action( 'learndash-breadcrumbs-before' ); ?>

<div class="ld-breadcrumbs-segments">
	<?php
	$breadcrumbs = learndash_get_breadcrumbs();

	/**
	 * Filter Breadcrumb keys
	 *
	 * @since 3.0.0
	 *
	 * @param array $keys Array of post type keys.
	 */
	$keys = apply_filters(
		'learndash_breadcrumbs_keys',
		array(
			'course',
			'lesson',
			'topic',
			'current',
		)
	);

	foreach ( $keys as $key ) :
		if ( isset( $breadcrumbs[ $key ] ) ) :
			?>
			<span><a href="<?php echo esc_url( $breadcrumbs[ $key ]['permalink'] ); ?>"><?php echo esc_html( wp_strip_all_tags( $breadcrumbs[ $key ]['title'] ) ); ?></a> </span>
			<?php
		endif;
	endforeach;
	?>
</div> <!--/.ld-breadcrumbs-segments-->

<?php
/**
 * Fires after the breadcrumbs.
 *
 * @since 3.0.0
 */
do_action( 'learndash-breadcrumbs-after' ); ?>
