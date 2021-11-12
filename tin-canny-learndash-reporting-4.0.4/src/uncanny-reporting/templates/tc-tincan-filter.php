<?php
namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="reporting-tincan-filters">
	<form action="<?php echo remove_query_arg( 'paged' ) ?>" id="tincan-filters-top">
		<div class="reporting-metabox">
			<div class="reporting-dashboard-col-heading" id="coursesOverviewTableHeading">
				<?php _e( 'Filters', 'uncanny-learndash-reporting' ); ?>
			</div>
			<div class="reporting-dashboard-col-content">
				<input type="hidden" name="page"
					   value="<?php echo ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 1 ?>"/>
				<input type="hidden" name="tc_filter_mode" value="list"/>
				<input type="hidden" name="tab" value="tin-can"/>

				<input type="hidden" name="orderby"
					   value="<?php echo ! empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'date-time' ?>"/>
				<input type="hidden" name="order"
					   value="<?php echo ! empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc' ?>"/>

				<div class="reporting-tincan-filters-columns">
					<div class="reporting-tincan-filters-col reporting-tincan-filters-col--1">
						<div class="reporting-tincan-section__title">
							<?php _e( 'User & Group', 'uncanny-learndash-reporting' ); ?>
						</div>
						<div class="reporting-tincan-section__content">
							<div class="reporting-tincan-section__field">

								<label for="tc_filter_group"><?php _e( 'Group', 'uncanny-learndash-reporting' ); ?></label>
								<select name="tc_filter_group" id="tc_filter_group">
									<option value=""><?php _e( 'All Groups', 'uncanny-learndash-reporting' ); ?></option>
									<?php foreach ( $ld_groups as $group ) { ?>
										<option value="<?php echo $group['group_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_group'] ) && $_GET['tc_filter_group'] == $group['group_id'] ) ? 'selected="selected"' : '' ?>><?php echo $group['group_name'] ?></option>
									<?php } // foreach( $ld_groups ) ?>
								</select>

							</div>

							<div class="reporting-tincan-section__field">
								<label for="tc_filter_user"><?php _e( 'User', 'uncanny-learndash-reporting' ); ?></label>
								<input name="tc_filter_user" id="tc_filter_user"
									   placeholder="<?php _e( 'User', 'uncanny-learndash-reporting' ); ?>"
									   value="<?php echo ! empty( $_GET['tc_filter_user'] ) ? sanitize_text_field( $_GET['tc_filter_user'] ) : '' ?>"/>
							</div>
						</div>
					</div>

					<div class="reporting-tincan-filters-col reporting-tincan-filters-col--2">

						<div class="reporting-tincan-section__title">
							<?php _e( 'Content', 'uncanny-learndash-reporting' ); ?>
						</div>
						<div class="reporting-tincan-section__content">
							<div class="reporting-tincan-section__field">
								<label for="tc_filter_course"><?php echo \LearnDash_Custom_Label::get_label( 'course' ); ?></label>
								<select name="tc_filter_course" id="tc_filter_course">
									<option value=""><?php echo sprintf( __( 'All %s', 'uncanny-learndash-reporting' ), \LearnDash_Custom_Label::get_label( 'courses' ) ); ?></option>
									<?php foreach ( $ld_courses as $course ) { ?>
										<option value="<?php echo $course['course_id'] ?>" <?php echo ( ! empty( $_GET['tc_filter_course'] ) && $_GET['tc_filter_course'] == $course['course_id'] ) ? 'selected="selected"' : '' ?>><?php echo $course['course_name'] ?></option>
									<?php } // foreach( $ld_courses ) ?>
								</select>
							</div>
							<div class="reporting-tincan-section__field">
								<label for="tc_filter_module"><?php _e( 'Module', 'uncanny-learndash-reporting' ); ?></label>
								<select name="tc_filter_module" id="tc_filter_module">
									<option value=""><?php _e( 'All Modules', 'uncanny-learndash-reporting' ); ?></option>
									<?php self::$tincan_database->print_modules_form_from_URL_parameter() ?>
								</select>
							</div>
						</div>

					</div>

					<div class="reporting-tincan-filters-col reporting-tincan-filters-col--3">

						<div class="reporting-tincan-section__title">
							<?php _e( 'Activity', 'uncanny-learndash-reporting' ); ?>
						</div>
						<div class="reporting-tincan-section__content">
							<div class="reporting-tincan-section__field">
								<label for="tc_filter_action"><?php _e( 'Action', 'uncanny-learndash-reporting' ); ?></label>
								<select name="tc_filter_action" id="tc_filter_action">
									<option value=""><?php _e( 'All Actions', 'uncanny-learndash-reporting' ); ?></option>
									<?php foreach ( $ld_actions as $action ) { ?>
										<option value="<?php echo $action['verb'] ?>" <?php echo ( ! empty( $_GET['tc_filter_action'] ) && strtolower( $_GET['tc_filter_action'] ) == $action['verb'] ) ? 'selected="selected"' : '' ?>><?php echo ucfirst( $action['verb'] ) ?></option>
									<?php } // foreach( $ld_groups ) ?>
								</select>
							</div>
						</div>

					</div>

					<div class="reporting-tincan-filters-col reporting-tincan-filters-col--4">
						<div class="reporting-tincan-section__title">
							<?php _e( 'Date Range', 'uncanny-learndash-reporting' ); ?>
						</div>
						<div class="reporting-tincan-section__content">
							<div class="reporting-tincan-section__field">
								<label>
									<input name="tc_filter_date_range" value="last"
										   type="radio" <?php echo ( empty( $_GET['tc_filter_date_range'] ) || sanitize_text_field( $_GET['tc_filter_date_range'] ) == 'last' ) ? 'checked="checked"' : '' ?> />
									<?php _e( 'View', 'uncanny-learndash-reporting' ); ?>
								</label>

								<select name="tc_filter_date_range_last" id="tc_filter_date_range_last">
									<option value="all" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'all' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'All Dates', 'uncanny-learndash-reporting' ); ?>
									</option>
									<option value="week" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'week' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'Last Week', 'uncanny-learndash-reporting' ); ?>
									</option>
									<option value="month" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == 'month' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'Last Month', 'uncanny-learndash-reporting' ); ?>
									</option>
									<option value="90days" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '90days' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'Last 90 Days', 'uncanny-learndash-reporting' ); ?>
									</option>
									<option value="3months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '3months' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'Last 3 Months', 'uncanny-learndash-reporting' ); ?>
									</option>
									<option value="6months" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range_last'] == '6months' ) ? 'selected="selected"' : '' ?>>
										<?php _e( 'Last 6 Months', 'uncanny-learndash-reporting' ); ?>
									</option>
								</select>
							</div>

							<div class="reporting-tincan-section__field">
								<label>
									<input name="tc_filter_date_range" value="from"
										   type="radio" <?php echo ( ! empty( $_GET['tc_filter_date_range'] ) && $_GET['tc_filter_date_range'] == 'from' ) ? 'checked="checked"' : '' ?> />
									<?php _e( 'From', 'uncanny-learndash-reporting' ); ?>
								</label>

								<input class="datepicker" name="tc_filter_start"
									   placeholder="<?php _e( 'Start Date', 'uncanny-learndash-reporting' ); ?>"
									   value="<?php echo ( ! empty( $_GET['tc_filter_start'] ) ) ? sanitize_text_field( $_GET['tc_filter_start'] ) : '' ?>"/>


								<input class="datepicker" name="tc_filter_end"
									   placeholder="<?php _e( 'End Date', 'uncanny-learndash-reporting' ); ?>"
									   value="<?php echo ( ! empty( $_GET['tc_filter_end'] ) ) ? sanitize_text_field( $_GET['tc_filter_end'] ) : '' ?>"/>
							</div>
						</div>
					</div>

				</div>

				<div class="reporting-tincan-footer">
					<?php submit_button( __( 'Search' ), 'primary', '', false, [
						'id'  => "do_tc_filter",
						'tab' => 'tin-can',
					] ); ?>

					<?php

					$reset_link = remove_query_arg( [
						'paged',
						'tc_filter_mode',
						'tc_filter_group',
						'tc_filter_user',
						'tc_filter_course',
						'tc_filter_lesson',
						'tc_filter_module',
						'tc_filter_action',
						'tc_filter_date_range',
						'tc_filter_date_range_last',
						'tc_filter_start',
						'tc_filter_end',
						'orderby',
						'order',
					] );

					if ( false === strpos( $reset_link, 'tab' ) ) {
						$reset_link .= '&tab=tin-can';
					}

					?>
					<a href="<?php echo $reset_link; ?>"
					   class="tclr-reporting-button"><?php _e( 'Reset', 'uncanny-learndash-reporting' ); ?></a>
				</div>
			</div>
		</div>
	</form>
</div>
