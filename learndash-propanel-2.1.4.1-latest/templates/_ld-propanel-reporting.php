<?php
/**
 * Learndash ProPanel Reporting
 */
?>
<?php 
	//if ( ( learndash_is_admin_user( get_current_user_id() ) ) || ( learndash_is_group_leader_user( get_current_user_id() ) ) ) { 
	//	$filter_tab_active = '';
	//	$filter_tab_display = '';
	//} else {
		$filter_tab_active = 'active';
		$filter_tab_display = 'display';
	//}
?>
<div class="ld-propanel-filters-wrap">

	<div class="table-actions-wrap">
		<?php if ( is_admin() ) { ?>
		<div class="section-toggles clearfix">
			<a href="#table-filters" title="<?php esc_attr_e( 'Filters', 'ld_propanel' ); ?>" class="button section-toggle <?php echo $filter_tab_active ?>"><?php esc_html_e( 'Filters', 'ld_propanel' ); ?></a>
			
			<?php if ( ( learndash_is_admin_user( get_current_user_id() ) ) || ( learndash_is_group_leader_user( get_current_user_id() ) ) ) { ?>
				<a href="#email" title="<?php esc_attr_e( 'Email', 'ld_propanel' ); ?>" class="button section-toggle email-toggle"><?php esc_html_e( 'Email', 'ld_propanel' ); ?><span class="count"></span></a>
				<a class="button full-page" href="<?php echo admin_url( '?page=propanel-reporting' ); ?>"><?php esc_html_e( 'Full Page', 'ld_propanel' ); ?></a>
				<a class="button dashboard-page" href="<?php echo admin_url( '/' ); ?>"><?php esc_html_e( 'Dashboard', 'ld_propanel' ); ?></a>
			<?php } ?>
		</div>
		<?php } ?>
		<?php include ld_propanel_get_template( 'ld-propanel-reporting-filtering.php' ); ?>

		<?php include ld_propanel_get_template( 'ld-propanel-reporting-emails.php' ); ?>
	</div>
</div>