<?php
/**
 * Learndash ProPanel Progress Chart
 */
?>

<div class="clearfix propanel-admin-row">
	
	<div class="col-1-2 ld-propanel-progress-chart-progress-distribution">
		<div class="title"><span><?php _e('Progress Distribution', 'ld_propanel'); ?></span></div>
		<div class="canvas-wrap">
			<div class="proPanelDefaultMessage" id="proPanelProgressAllDefaultMessage"><strong><?php _e('No All-Progress items found', 'ld_propanel') ?></strong></div>
			<canvas id="proPanelProgressAll" width="400" height="400"></canvas>
		</div>
	</div>
	
	<div class="col-1-2 ld-propanel-progress-chart-progress-breakdown">
		<div class="title"><span><?php _e('In Progress Breakdown', 'ld_propanel'); ?></span></div>
		<div class="canvas-wrap">
			<div class="proPanelDefaultMessage" id="proPanelProgressInMotionDefaultMessage"><strong><?php _e('No In-Progress items found', 'ld_propanel') ?></strong></div>
			<canvas id="proPanelProgressInMotion" width="400" height="400"></canvas>
		</div>
	</div>
	
</div>