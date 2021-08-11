<?php
/**
 * Learndash ProPanel Activity Template
 */
?>
<?php 
//$activity_steps_completed = absint( LearnDash_ProPanel_Activity::get_activity_steps_completed( $activity ) ); 
//$activity_steps_total  = absint( LearnDash_ProPanel_Activity::get_activity_steps_total( $activity ) );

if ( current_user_can( 'edit_user', $activity->user_id ) ) { 
	$user_link = get_edit_user_link( $activity->user_id ) ."#ld_course_info";
} else {
	$user_link = "#";
}

if ( ( !empty( $activity->activity_completed ) ) && ( !empty( $activity->activity_started ) ) ) { 
	$activity_diff_completed = learndash_get_activity_human_time_diff( $activity->activity_started, $activity->activity_completed, 1 );
} else {
	$activity_diff_completed = 0;
}

if ( !empty( $activity_diff_completed ) ) {
	$activity_abbr_label_completed = __('Completed Date (Duration)', 'ld_propanel');
} else {
	$activity_abbr_label_completed = __('Completed Date', 'ld_propanel');
}

if ( !empty( $activity->activity_started ) ) { 
	$activity_diff_started = learndash_get_activity_human_time_diff( $activity->activity_started, time(), 1 );
} else {
	$activity_diff_started = 0;
}

if ( !empty( $activity_diff_started ) ) {
	$activity_abbr_label_started = __('Started Date (Duration)', 'ld_propanel');
} else {
	$activity_abbr_label_started = __('Started Date', 'ld_propanel');
}

?>
<?php if ( 'quiz' == $activity->activity_type ) : ?>
	<div class="activity-item quiz">
		<div class="header">
			<span class="user"><a href="<?php echo $user_link; ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
			
			<?php if ( !empty( $activity->activity_completed ) ) { ?>
				<abbr class="date" title="<?php echo $activity_abbr_label_completed ?>"><?php 
					echo $activity->activity_completed_formatted; 
					if ( !empty( $activity_diff_completed ) )  {
						?> (<i><?php echo $activity_diff_completed; ?></i>)<?php
					}
				?></abbr>
			<?php } ?>
		</div>
		<div class="content">
			<strong><?php echo sprintf(
				// translators: Quiz completed
				esc_html_x( '%s Completed:', 'Quiz Completed:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></strong><strong><a href="<?php echo learndash_get_step_permalink( $activity->post_id, $activity->activity_course_id ); ?>" class="link"> <?php echo $activity->post_title; ?></a></strong><?php

				edit_post_link(
					sprintf(
						//translators: placeholder: Title of current post
						_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
						get_the_title( $activity->post_id )
					),
					'<span class="ld-propanel-edit-link edit-link">',
					'</span>',
					$activity->post_id
				);
			?><br/>

			<?php if ( $course = $this->get_activity_course( $activity ) ) : ?>
				<strong><?php echo sprintf(
					// translators: Course
					esc_html_x( '%s:', 'Course:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo get_permalink( $course->ID ); ?>" class="link"><?php echo get_the_title( $course->ID ); ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $course->ID )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$course->ID
					);
			?><br/>
			<?php endif; ?>

			<?php if ( $this->quiz_activity_is_pending( $activity ) ) : ?>
				<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php esc_html_e( 'Pending', 'ld_propanel' ); ?><br/>
			<?php else : ?>
				<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php echo ( $this->quiz_activity_is_passing( $activity ) ) ? esc_html__( 'Passed', 'ld_propanel' ) : esc_html__( 'Failed', 'ld_propanel' ); ?><?php 
				$quiz_statistics_link = $this->get_quiz_statistics_link( $activity );
				if ( !empty( $quiz_statistics_link ) ) {
					echo ' '. $quiz_statistics_link; 
				}
			 ?><br/>
			<?php endif; ?>

			<?php /* ?>
			<strong><?php esc_html_e( 'Score:', 'ld_propanel' ); ?> </strong><?php printf( '%d%% (%d/%d)', $this->quiz_activity_score_percentage( $activity ), $this->quiz_activity_awarded_score( $activity ), $this->quiz_activity_total_score( $activity ) ); ?>
			<?php */ ?>
			<strong><?php esc_html_e( 'Points:', 'ld_propanel' ); ?> </strong><?php printf( '%d%% (%d/%d)', $this->quiz_activity_points_percentage( $activity ), $this->quiz_activity_awarded_points( $activity ), $this->quiz_activity_total_points( $activity ) ); ?>
		</div>
	</div>

<?php endif; ?>



<?php if ( 'course' == $activity->activity_type ) : ?>

	<div class="activity-item course">
		<div class="header">
			<span class="user"><a href="<?php echo $user_link ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
			
			<?php if ( !empty( $activity->activity_completed ) ) {?>
				<abbr class="date" title="<?php echo $activity_abbr_label_completed; ?>"><?php 
					echo $activity->activity_completed_formatted; 
					if ( !empty( $activity_diff_completed ) )  {
						?> (<i><?php echo $activity_diff_completed; ?></i>)<?php
					}
				?></abbr>
			<?php } ?>
		</div>

		<div class="content">
			<strong><?php echo sprintf(
				// translators: Course
				esc_html_x( '%s:', 'Course', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo learndash_get_step_permalink( $activity->post_id, $activity->activity_course_id ); ?>" class="link"><?php echo $activity->post_title; ?></a></strong><?php

				edit_post_link(
					sprintf(
						// translators: placeholder: Title of current post
						_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
						get_the_title( $activity->post_id )
					),
					'<span class="ld-propanel-edit-link edit-link">',
					'</span>',
					$activity->post_id
				);
			?><br/>

			<?php /* ?>
			<?php if ( !empty( $activity_steps_total ) ) { ?>
				<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php printf(
					// translators: placeholders: steps completed, steps total.
					esc_html_x( 'Completed %1$d out of %2$d steps', 'placeholders: steps completed, steps total', 'ld_propanel' ),
					$activity_steps_completed, $activity_steps_total 
				); ?>
			<?php } ?>
			<?php */ ?>
		</div>
	</div>

<?php endif; ?>

<?php if ( 'access' == $activity->activity_type ) : ?>

	<div class="activity-item course">
		<div class="header">
			<span class="user"><a href="<?php echo $user_link; ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
			<?php if ( !empty( $activity->activity_completed ) ) {?>
				<abbr class="date" title="<?php echo $activity_abbr_label_completed; ?>"><?php 
					echo $activity->activity_started_formatted; 
					if ( !empty( $activity_diff_completed ) )  {
						?> (<i><?php echo $activity_diff_completed; ?></i>)<?php
					}
				?></abbr>
			<?php } else if ( !empty( $activity->activity_started ) ) { ?>
				<abbr class="date" title="<?php echo $activity_abbr_label_started; ?>"><?php 
					echo $activity->activity_started_formatted; 
					
					if ( !empty( $activity_diff_started ) ) {
						?> (<i><?php echo $activity_diff_started; ?></i>)<?php
					}
				?></abbr>
			<?php } ?>
		</div>

		<div class="content">
			<strong><?php echo sprintf(
				// translators: Gained Course Access
				esc_html_x('Gained %s Access:', 'Gained Course Access', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo learndash_get_step_permalink( $activity->post_id, $activity->activity_course_id ); ?>" class="link"><?php echo $activity->post_title; ?></a></strong><br/>
		</div>
	</div>

<?php endif; ?>



<?php if ( 'lesson' == $activity->activity_type ) : ?>

		<div class="activity-item lesson">
			<div class="header">
				<span class="user"><a href="<?php echo $user_link; ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
				<?php if ( !empty( $activity->activity_completed ) ) {?>
					<abbr class="date" title="<?php echo $activity_abbr_label_completed; ?>"><?php 
						echo $activity->activity_completed_formatted; 
						if ( !empty( $activity_diff_completed ) )  {
							?> (<i><?php echo $activity_diff_completed; ?></i>)<?php
						}
					?></abbr>
				<?php } else if ( !empty( $activity->activity_started ) ) { ?>
					<abbr class="date" title="<?php echo $activity_abbr_label_started; ?>"><?php 
						echo $activity->activity_started_formatted; 
					
						if ( !empty( $activity_diff_started ) ) {
							?> (<i><?php echo $activity_diff_started; ?></i>)<?php
						}
					?></abbr>
				<?php } ?>
			</div>

			<div class="content">
				<strong><?php echo sprintf(
					// translators: Lesson
					esc_html_x( '%s:', 'Lesson:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'lesson' ) ); ?> </strong><strong><a href="<?php echo learndash_get_step_permalink( $activity->post_id, $activity->activity_course_id ); ?>" class="link"><?php echo $activity->post_title; ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $activity->post_id )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$activity->post_id
					);
				?></br>

				<?php if ( $course = $this->get_activity_course( $activity ) ) : ?>
					<strong><?php echo sprintf( 
						// translators: Course
						esc_html_x( '%s:', 'Course', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo get_permalink( $course->ID ); ?>" class="link"><?php echo get_the_title( $course->ID ); ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $course->ID )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$course->ID
					);
				?><br/>
				<?php endif; ?>

				<?php /* ?>
				<?php if ( !empty( $activity_steps_total ) ) { ?>
					<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php printf(
						// translators: placeholders: steps completed, steps total.
						esc_html_x( 'Completed %1$d out of %2$d steps', 'placeholders: steps completed, steps total', 'ld_propanel' ),
						$activity_steps_completed, $activity_steps_total
					); ?>
				<?php } ?>
				<?php */ ?>
			</div>
		</div>

<?php endif; ?>



<?php if ( 'topic' == $activity->activity_type ) : ?>
	<div class="activity-item topic">
		<div class="header">
			<span class="user"><a href="<?php echo $user_link; ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
			<?php if ( !empty( $activity->activity_completed ) ) { ?>
				<abbr class="date" title="<?php echo $activity_abbr_label_completed; ?>"><?php 
					echo $activity->activity_completed_formatted; 
					if ( !empty( $activity_diff_completed ) )  {
						?> (<i><?php echo $activity_diff_completed; ?></i>)<?php
					}
				?></abbr>
			<?php } else if ( !empty( $activity->activity_started ) ) { ?>
				<abbr class="date" title="<?php echo $activity_abbr_label_started; ?>"><?php 
					echo $activity->activity_started_formatted; 
					
					if ( !empty( $activity_diff_started ) ) {
						?> (<i><?php echo $activity_diff_started; ?></i>)<?php
					}
				?></abbr>
			<?php } ?>
		</div>

		<div class="content">
			<strong><?php echo sprintf( 
				// translators: Topic
				esc_html_x('%s:', 'Topic', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'topic' ) ); ?> </strong><strong><a href="<?php echo learndash_get_step_permalink( $activity->post_id, $activity->activity_course_id ); ?>" class="link"><?php echo $activity->post_title; ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $activity->post_id )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$activity->post_id
					);
				?></br>

			<?php if ( $course = $this->get_activity_course( $activity ) ) : ?>
				<strong><?php echo sprintf(
					// translators: Course 
					esc_html_x('%s:', 'Course:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo get_permalink( $course->ID ); ?>" class="link"><?php echo get_the_title( $course->ID ); ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $course->ID )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$course->ID
					);
					
				?><br/>
			<?php endif; ?>

			<?php /* ?>
			<?php if ( !empty( $activity_steps_total ) ) { ?>
				<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php printf(
					// translators: placeholders: steps completed, steps total.
					esc_html_x( 'Completed %1$d out of %2$d steps', 'placeholders: steps completed, steps total', 'ld_propanel' ),
					$activity_steps_completed, $activity_steps_total
					); ?>
			<?php } ?>
			<?php */ ?>
		</div>
	</div>

<?php endif; ?>


<?php if ( is_null( $activity->activity_type ) ) : ?>

	<div class="activity-item not-started">
		<div class="header">
			<span class="user"><a href="<?php echo $user_link; ?>" title="<?php esc_attr_e( 'See User Progress', 'ld_propanel' ); ?>"><?php echo $activity->user_display_name; ?></a></span>
		</div>

		<div class="content">
			<?php if ( $course = $this->get_activity_course( $activity ) ) : ?>
				<strong><?php echo sprintf( _x('%s:', 'Course:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></strong> <strong><a href="<?php echo get_permalink( $course->ID ); ?>" class="link"><?php echo get_the_title( $course->ID ); ?></a></strong><?php
					edit_post_link(
						sprintf(
							// translators: placeholder: Title of current post
							_x( ' (edit<span class="screen-reader-text"> "%s"</span>)', 'placeholder: Title of current post', 'ld_propanel' ),
							get_the_title( $course->ID )
						),
						'<span class="ld-propanel-edit-link edit-link">',
						'</span>',
						$course->ID
					);
				?><br/>
			<?php endif; ?>

			<strong><?php esc_html_e( 'Result:', 'ld_propanel' ); ?> </strong><?php esc_html_e( 'Not Started', 'ld_propanel' ); ?>
		</div>
	</div>

<?php endif; ?>
