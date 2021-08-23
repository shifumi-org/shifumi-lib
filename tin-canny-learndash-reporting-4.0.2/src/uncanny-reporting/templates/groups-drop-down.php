<?php
namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! empty( self::$groups_query ) && 1 !== count( self::$groups_query ) ) { ?>
	<div class="reporting-group-selector" id="reporting-group-selector-container">
		<form method="GET" class="reporting-group-selector__form">
			<?php if ( is_admin() ) { ?>
				<input type="hidden" name="page" value="<?php echo htmlspecialchars( $_GET['page'] ); ?>">
				<input id="reporting-group-selector-tab" type="hidden" name="tab" value="<?php echo isset( $_GET[ 'tab' ] ) ? htmlspecialchars( $_GET[ 'tab' ] ) : 'courseReportTab'; ?>">
			<?php } ?>
			<div class="reporting-group-selector__label-container">
				<label for="reporting-group-selector">
					<?php _e( 'Group', 'uncanny-learndash-reporting' ); ?>
				</label>
			</div>
			<div class="reporting-group-selector__select-container">
				<select name="group_id" id="reporting-group-selector" class="reporting-group-selector__select">
					<option value="all"><?php _e( 'All Users', 'uncanny-learndash-reporting' ); ?></option>
					<?php
					foreach ( self::$groups_query as $group ) {
						?>
						<option
							<?php
							if ( $group->ID === self::$isolated_group ) {
								echo 'selected="selected"';
							}
							?>
								value="<?php echo $group->ID; ?>"><?php echo $group->post_title; ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="reporting-group-selector__submit-container">
				<input value="<?php _e( 'Filter', 'uncanny-learndash-reporting' ); ?>" type="submit" id="reporting-group-selector__submit">
			</div>
		</form>
	</div>
	<?php
}
?>
