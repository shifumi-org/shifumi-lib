<?php
/**
 * Activity Pagination Shotcode
 */
?>

<div class="activity-item pagination">
	<?php if ( 1 < $paged ) : ?>
		<a href="<?php echo add_query_arg('paged', intval($paged)-1) ?>" class="prev" data-page="<?php echo $paged - 1; ?>"><?php echo sprintf( 
			// translators: activity widget pagnation previous page link
			esc_html_x('&laquo; Previous', 'activity widget pagnation previous page link', 'ld_propanel') 
		); ?></a>
	<?php endif; ?>

	<?php if ( $paged != $activities['pager']['total_pages'] ) : ?>
		<a href="<?php echo add_query_arg('paged', intval($paged)+1) ?>" class="next" data-page="<?php echo $paged + 1; ?>"><?php 
		// translators: activity widget pagnation next page link
		echo esc_html_x( 'Next &raquo;', 'activity widget pagnation next page link', 'ld_propanel' ); ?></a>
	<?php endif; ?>

	<div class="clearfix"></div>
</div>