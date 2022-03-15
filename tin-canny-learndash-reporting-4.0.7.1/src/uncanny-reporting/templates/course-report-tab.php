<?php
namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="uo-admin-reporting-tab-single" id="courseReportTab" style="display: <?php echo $current_tab == 'courseReportTab' ? 'block' : 'none'; ?>">

	<div id="reporting-course-navigation" class="reporting-breadcrumbs">
		<ul class="reporting-breadcrumbs-items">
			<li class="reporting-breadcrumbs-item reporting-breadcrumbs-item--guide">
				<span><?php _e( 'Tin Canny Reports', 'uncanny-learndash-reporting' ); ?></span>
			</li>
			<li id="course-navigate-link" class="reporting-breadcrumbs-item reporting-breadcrumbs-item--visible reporting-breadcrumbs-item--current">
				<span>
					<?php printf( _x( '%s Report', '%s is the "Course" label', 'uncanny-learndash-reporting' ), esc_html( self::$template_data['labels']['course'] ) ); ?>
				</span>
			</li>
			<li id="courseSingleTitle" class="reporting-breadcrumbs-item"></li>
		</ul>
	</div>

	<div id="coursesOverviewContainer">
		<h2 id="coursesOverviewGraphHeading" style="display: none"><?php /* esc_html_e( 'Overview', 'uncanny-learndash-reporting' ); */ ?></h2>
		
		<div id="coursesOverviewGraph"></div>

		<div class="reporting-section">
			<div class="reporting-metabox">
		        <div class="reporting-dashboard-col-heading" id="coursesOverviewTableHeading">
		        	<?php echo esc_html( self::$template_data['labels']['courses'] ); ?>
		        </div>
		        <div class="reporting-dashboard-col-content reporting-dashboard-col-content--no-padding">
		            <table id="coursesOverviewTable" class="display responsive reporting-table reporting-table-selectable" width="100%">
						<tbody>
							<tr>
								<td class="reporting-table__loading-cell">
									<div class="reporting-dashboard-status reporting-dashboard-status--loading">
										<div class="reporting-dashboard-status__icon"></div>
										<div class="reporting-dashboard-status__text">
											<?php _e( 'Loading', 'uncanny-learndash-reporting' ); ?>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
		        </div>
			</div>
		</div>
	</div>
	<div id="courseSingleContainer" style="display: none;">

		<div id="courseSingleNav"></div>

		<div class="reporting-section reporting-section-course-individual">
			<div class="reporting-section-course-individual-left">
				<div class="reporting-metabox" id="courseSingleOverviewContainer">
			        <div class="reporting-dashboard-col-heading">
			        	<?php echo esc_html(
							/* Translators: 1:Course label*/
							sprintf( __( '%1$s Completions', 'uncanny-learndash-reporting' ), self::$template_data['labels']['course'] )
						); ?>
			        </div>
			        <div class="reporting-dashboard-col-content">
			        	<div id="courseSingleActivitiesGraph">
			        		<div class="reporting-dashboard-status reporting-dashboard-status--loading">
                                <div class="reporting-dashboard-status__icon"></div>
                                <div class="reporting-dashboard-status__text">
                                    <?php _e( 'Loading', 'uncanny-learndash-reporting' ); ?>
                                </div>
                            </div>
			        	</div>
			        </div>
			    </div>
			</div>
			<div class="reporting-section-course-individual-right">

				<div class="reporting-metabox" id="courseSingleOverviewContainer">
			        <div class="reporting-dashboard-col-heading">
			        	<?php echo esc_html(
							sprintf( _x( '%s Overview', '%s is the "Course" label', 'uncanny-learndash-reporting' ), self::$template_data['labels']['course'] )
						); ?>
			        </div>
			        <div class="reporting-dashboard-col-content">
			            <div class="reporting-metabox--hide-table-top reporting-metabox--table-one-result">
							<table id="courseSingleOverviewSummaryTable" class="reporting-table display responsive" width="100%">
								<tbody>
									<tr>
										<td class="reporting-table__loading-cell">
											<div class="reporting-dashboard-status reporting-dashboard-status--loading">
												<div class="reporting-dashboard-status__icon"></div>
												<div class="reporting-dashboard-status__text">
													<?php _e( 'Loading', 'uncanny-learndash-reporting' ); ?>
												</div>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
			        </div>
				</div>

				<div class="reporting-metabox" id="courseSingleOverviewPieChartContainer">
			        <div class="reporting-dashboard-col-heading">
			        	<?php echo esc_html(
							sprintf( _x( '%s Status', '%s is the "Course" label', 'uncanny-learndash-reporting' ), self::$template_data['labels']['course'] )
						); ?>
			        </div>
			        <div class="reporting-dashboard-col-content reporting-dashboard-col-content--no-padding reporting-dashboard-col-content--no-min-height">
			            <div class="reporting-metabox--hide-table-top reporting-metabox--table-one-result">
							<div id="courseSingleOverviewPieChart"></div>
						</div>
			        </div>
				</div>

			</div>

		</div>
	
		<div class="reporting-section">
			<div class="reporting-metabox">
		        <div class="reporting-dashboard-col-heading" id="courseSingleTableHeading">
		        	<?php esc_html_e( 'Enrolled Users', 'uncanny-learndash-reporting' ); ?>
		        </div>
		        <div class="reporting-dashboard-col-content reporting-dashboard-col-content--no-padding reporting-dashboard-col-content--no-min-height">
		            <table id="courseSingleTable" class="display responsive reporting-table reporting-table-selectable" width="100%">
						<tbody>
							<tr>
								<td class="reporting-table__loading-cell">
									<div class="reporting-dashboard-status reporting-dashboard-status--loading">
										<div class="reporting-dashboard-status__icon"></div>
										<div class="reporting-dashboard-status__text">
											<?php _e( 'Loading', 'uncanny-learndash-reporting' ); ?>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
		        </div>
			</div>
		</div>

	</div>

</div>