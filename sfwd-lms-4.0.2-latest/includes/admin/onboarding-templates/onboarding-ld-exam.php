<?php
/**
 * Onboarding Exam (ld-exam)Template. Displayed when no entities were added to help the user.
 *
 * @since   4.0.0
 *
 * @var string $screen_post_type
 *
 * @package LearnDash
 */

namespace LearnDash\Views\Onboarding;

use LearnDash_Custom_Label;

defined( 'ABSPATH' ) || exit;

?>
<section class="ld-onboarding-screen">
	<div class="ld-onboarding-main">
		<span class="dashicons dashicons-welcome-add-page"></span>

		<h2>
			<?php
			printf(
				// translators: placeholder: exams.
				esc_html_x( 'You don\'t have any %s yet', 'placeholder: exams', 'learndash' ),
				learndash_get_custom_label( 'exams' )
			);
			?>
		</h2>
		<p>
			<?php
			printf(
				// translators: placeholder: Exams, Course, Courses.
				esc_html_x(
					'%1$s are a great way to check if your learners are understanding the %2$s content and allow them to skip %3$s they already know.',
					'placeholder: Exams, Course, Courses.',
					'learndash'
				),
				learndash_get_custom_label( 'exams' ),
				learndash_get_custom_label( 'course' ),
				learndash_get_custom_label( 'courses' )
			);
			?>
		</p>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $screen_post_type ) ); ?>" class="button button-secondary">
			<span class="dashicons dashicons-plus-alt"></span>
			<?php
			printf(
				// translators: placeholder: Exam.
				esc_html_x( 'Add your first %s', 'placeholder: Exam', 'learndash' ),
				learndash_get_custom_label( 'exam' )
			);
			?>
		</a>
	</div> <!-- .ld-onboarding-main -->

</section> <!-- .ld-onboarding-screen -->
