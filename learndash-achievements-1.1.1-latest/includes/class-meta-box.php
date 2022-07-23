<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Achievement;
use WP_Error;

/**
 * Meta_Box class
 */
class Meta_Box {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( __CLASS__, 'save_data' ), 10, 3 );

		add_action( 'wp_ajax_ld_achievements_get_children_list', array( __CLASS__, 'get_children_list' ) );
	}

	public function add_meta_boxes() {
		add_meta_box( 'description', __( 'Message', 'learndash-achievements' ), array( __CLASS__, 'message_meta_box' ), 'ld-achievement', 'normal', 'high' );

		add_meta_box( 'details', __( 'Details', 'learndash-achievements' ), array( __CLASS__, 'details_meta_box' ), 'ld-achievement', 'normal', 'high' );

		add_meta_box( 'image', __( 'Image', 'learndash-achievements' ), array( __CLASS__, 'image_meta_box' ), 'ld-achievement', 'side', 'high' );
	}

	public static function get_children_list() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_achievements_nonce' ) ) {
			wp_die();
		}

		if ( ! current_user_can( LEARNDASH_ADMIN_CAPABILITY_CHECK ) ) {
			wp_die();
		}

		$course_id   = sanitize_text_field( $_POST['course_id'] );
		$parent_id   = sanitize_text_field( $_POST['parent_id'] );
		$parent_type = sanitize_text_field( $_POST['parent_type'] );
		$step_type   = '';

		switch ( $parent_type ) {
			case 'course':
				$step_type = 'sfwd-lessons';
				break;

			case 'lesson':
				$step_type = 'sfwd-topic';
				break;

			case 'topic':
				$step_type = 'sfwd-quiz';
				break;
		}

		if ( ! empty( $step_type ) ) {
			$return = array();

			if ( $parent_id == 'all' ) {

				if ( $parent_type == 'course' ) {
					$lessons = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1&orderby=title&order=ASC' );

					foreach ( $lessons as $l ) {
						$return[ $l->ID ] = $l->post_title;
					}
				}

				if ( $parent_type == 'lesson' && 'all' == $course_id ) {
					$topics = get_posts( 'post_type=sfwd-topic&posts_per_page=-1&orderby=title&order=ASC' );

					foreach ( $topics as $t ) {
						$return[ $t->ID ] = $t->post_title;
					}
				} elseif ( $parent_type == 'lesson' && is_numeric( $course_id ) ) {
					$children = learndash_course_get_steps_by_type( $course_id, 'sfwd-topic' );

					foreach ( $children as $child_id ) {
						$post                = get_post( $child_id );
						$return[ $child_id ] = $post->post_title;
					}
				}

				if ( $parent_type == 'topic' && 'all' == $course_id ) {
					$quizzes = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1&orderby=title&order=ASC' );

					foreach ( $quizzes as $q ) {
						$return[ $q->ID ] = $q->post_title;
					}
				} elseif ( $parent_type == 'topic' && is_numeric( $course_id ) ) {
					$children = learndash_course_get_steps_by_type( $course_id, 'sfwd-quiz' );

					foreach ( $children as $child_id ) {
						$post                = get_post( $child_id );
						$return[ $child_id ] = $post->post_title;
					}
				}
			} else {

				$children = learndash_course_get_children_of_step( $course_id, $parent_id, $step_type );

				foreach ( $children as $child_id ) {
					$post                = get_post( $child_id );
					$return[ $child_id ] = $post->post_title;
				}
			}

			echo json_encode( $return );
		}

		wp_die();
	}

	public static function save_data( $post_id, $post, $update ) {
		if ( 'ld-achievement' != $post->post_type ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( LEARNDASH_ADMIN_CAPABILITY_CHECK ) ) {
			return;
		}

		if ( ! isset( $_POST['learndash_achievements_nonce'] ) || ! wp_verify_nonce( $_POST['learndash_achievements_nonce'], 'save_data' ) ) {
			return;
		}

		$error = false;

		// Sanitize the data.
		$data                        = array();
		$data['achievement_message'] = wp_kses_post( $_POST['achievement_message'] );
		$data['trigger']             = sanitize_text_field( $_POST['trigger'] );

		$data['group_id']   = sanitize_text_field( $_POST['group_id'] );
		$data['course_id']  = sanitize_text_field( $_POST['course_id'] );
		$data['lesson_id']  = sanitize_text_field( $_POST['lesson_id'] );
		$data['topic_id']   = sanitize_text_field( $_POST['topic_id'] );
		$data['quiz_id']    = sanitize_text_field( $_POST['quiz_id'] );
		$data['percentage'] = sanitize_text_field( $_POST['percentage'] );
		$data['user_group'] = sanitize_text_field( $_POST['user_group'] );

		if ( in_array( $data['trigger'], array( 'enroll_course', 'complete_course', 'course_expires' ) ) ) {
			$data['group_id']  = '';
			$data['course_id'] = $data['course_id'];
			$data['lesson_id'] = '';
			$data['topic_id']  = '';
			$data['quiz_id']   = '';

		} elseif ( in_array( $data['trigger'], array( 'complete_lesson', 'lesson_available' ) ) ) {
			$data['group_id']  = '';
			$data['course_id'] = $data['course_id'];
			$data['lesson_id'] = $data['lesson_id'];
			$data['topic_id']  = '';
			$data['quiz_id']   = '';

		} elseif ( in_array( $data['trigger'], array( 'complete_topic' ) ) ) {
			$data['group_id']  = '';
			$data['course_id'] = $data['course_id'];
			$data['lesson_id'] = $data['lesson_id'];
			$data['topic_id']  = $data['topic_id'];
			$data['quiz_id']   = '';

		} elseif ( in_array( $data['trigger'], array( 'pass_quiz', 'fail_quiz', 'complete_quiz' ) ) ) {
			$data['group_id']  = '';
			$data['course_id'] = $data['course_id'];
			$data['lesson_id'] = $data['lesson_id'];
			$data['topic_id']  = $data['topic_id'];
			$data['quiz_id']   = $data['quiz_id'];

		} elseif ( in_array( $data['trigger'], array( 'enroll_group' ) ) ) {
			$data['group_id']  = $data['group_id'];
			$data['course_id'] = '';
			$data['lesson_id'] = '';
			$data['topic_id']  = '';
			$data['quiz_id']   = '';

		}

		$data['points']      = absint( $_POST['points'] );
		$data['occurrences'] = absint( $_POST['occurrences'] );

		if ( empty( $data['trigger'] ) ) {
			$error = new WP_Error( '1', __( 'Achievement trigger is required.', 'learndash-achievements' ) );
		}

		if ( isset( $_POST['image'] ) && ! empty( $_POST['image'] ) ) {
			$data['image'] = sanitize_text_field( $_POST['image'] );
		} else {
			$data['image'] = '';

			// Bail, return an image required notice.
			// $error = new WP_Error( '1', __( 'Achievement image is required.', 'learndash-achievements' ) );
		}

		// Save data.
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// If there is error, set error message.
		if ( $error ) {
			// Unhook this function so it doesn't loop infinitely.
			remove_action( 'save_post', array( __CLASS__, 'save_data' ) );

			// Update the post, which calls save_post again.
			$post->post_status = 'draft';
			wp_update_post( $post );

			// Re-hook this function.
			add_action( 'save_post', array( __CLASS__, 'save_data' ) );

			// Set error message.
			set_transient( 'ld_achievements_error', $error, 5 );

			// Redirect to edit page.
			add_filter(
				'redirect_post_location',
				function( $location ) {
					$location = remove_query_arg( 'message', $location );
					return $location;
				}
			);
		}
	}

	public static function message_meta_box() {
		$message = @get_post_meta( get_the_ID(), 'achievement_message', true );

		wp_editor(
			$message,
			'achievement_message',
			$settings = array(
				'editor_height' => 5,
				'media_buttons' => false,
				'quicktags'     => true,
			)
		);
	}

	public static function details_meta_box() {
		$settings = self::get_settings();

		wp_nonce_field( 'save_data', 'learndash_achievements_nonce' );

		echo '<div class="sfwd sfwd_options ld_achievements_metabox_settings">';

		foreach ( $settings as $key => $setting ) {
			self::setting_field_output( $key, $setting );
		}

		echo '</div>';
	}

	public static function image_meta_box() {
		$icons    = Achievement::get_icons();
		$selected = get_post_meta( get_the_ID(), 'image', true );

		?>

		<div id="image-preview-holder">
			<img src=""><br>
			<a href="#" id="remove-image-btn"><?php _e( 'Remove', 'learndash-achievements' ); ?></a>
		</div>
		<div class="image-selector-buttons">
			<input type="hidden" name="image" id="image-field" value="<?php echo esc_attr( $selected ); ?>">
			<div class="btn-wrapper select-image-btn-wrapper">
				<a href="#" class="select-image-btn"><?php _e( 'Select Image', 'learndash-achievements' ); ?></a>&nbsp;|&nbsp;
			</div>
			<div class="btn-wrapper">
				<?php submit_button( __( 'Upload Image', 'learndash-achievements' ), 'secondary', 'upload-image', false ); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="icon-selection">
			<div class="icons">
			<?php foreach ( $icons as $icon ) : ?>
				<span>
					<!-- <input type="radio" name="icon" value="<?php echo esc_attr( $icon ); ?>"> -->
					<img src="<?php echo esc_attr( $icon ); ?>" class="radio-btn">
				</span>
			<?php endforeach; ?>
			</div>
			<div class="clear"></div>
		</div>

		<?php
	}

	public static function setting_field_output( $key, $args ) {
		$hide    = isset( $args['hide'] ) && $args['hide'] == 1 ? 'display: none;' : '';
		$hide_on = '';
		if ( isset( $args['hide_on'] ) ) {
			foreach ( $args['hide_on'] as $class ) {
				$hide_on .= ' hide_on hide_on_' . $class . ' ';
			}
		}

		$parent_class = '';
		if ( isset( $args['parent'] ) && ! is_array( $args['parent'] ) ) {
			$parent_class = $args['parent'] ? $args['parent'] . ' child-input' : '';
		} elseif ( isset( $args['parent'] ) && is_array( $args['parent'] ) ) {
			$parent_class = '';
			foreach ( $args['parent'] as $class ) {
				$parent_class .= $class . ' child-input ';
			}
		}

		$class = '';
		if ( isset( $args['class'] ) && ! is_array( $args['class'] ) ) {
			$class = $args['class'] ? $args['class'] . ' ' : ' ';
		} elseif ( isset( $args['class'] ) && is_array( $args['class'] ) ) {
			$class = '';
			foreach ( $args['class'] as $class_name ) {
				$class .= $class_name . ' ';
			}
		}

		$screen   = get_current_screen();
		$disabled = false === stripos( $screen->action, 'add' ) && isset( $args['disable_on_edit'] ) && true === $args['disable_on_edit'] ? ' disabled="disabled" ' : '';

		$input_name      = $key;
		$id              = get_the_ID();
		$key_value       = get_post_meta( $id, $input_name, true );
		$key_value       = is_array( $key_value ) ? maybe_unserialize( $key_value ) : $key_value;
		$trigger         = get_post_meta( $id, 'trigger', true );
		$trigger_post_id = get_post_meta( $id, 'trigger_post_id', true );
		$default         = isset( $args['default'] ) ? $args['default'] : '';
		$hide_delay      = isset( $args['hide_delay'] ) && $args['hide_delay'] == 1 ? 'hide-delay' : '';

		// For backward compatibility for LD < 2.5.
		$input_select_name       = str_replace( '_id', '', $input_name );
		$hide_empty_select_field = ( $args['type'] == 'dropdown' ) && isset( $trigger_post_id ) && ! empty( $trigger_post_id ) && strpos( $trigger, $input_select_name ) === false && empty( $key_value ) ? 'hide-empty-select' : '';

		ob_start();
		?>

		<div class="sfwd_input <?php echo $parent_class; ?> <?php echo $class; ?> <?php echo $hide_delay; ?> <?php echo $hide_on; ?> <?php echo $hide_empty_select_field; ?>" id="<?php echo $key; ?>" style="<?php echo $hide; ?>">
			<span class="sfwd_option_label">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php _e( 'Click for Help!', 'learndash-achievements' ); ?>" onclick="toggleVisibility( 'learndash-achievements_<?php echo $key; ?>_tip' );"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png'; ?>"><label class="sfwd_label textinput"><?php echo $args['title']; ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">

					<?php if ( $args['type'] == 'dropdown' ) : ?>

						<select name="<?php echo $input_name; ?>" <?php echo esc_attr( $disabled ); ?>>

							<?php if ( $key == 'trigger' ) : ?>

								<option value="">-- <?php _e( 'Select Trigger', 'learndash-achievements' ); ?> --</option>

								<?php foreach ( $args['value'] as $section => $options ) : ?>

									<optgroup label="<?php echo esc_attr( $section ) . ' ' . __( 'Triggers', 'learndash-achievements' ); ?>">

									<?php foreach ( $options as $value => $title ) : ?>

										<?php $selected = (string) $value === (string) $key_value ? 'selected="selected"' : ''; ?>

										<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $title . " ($section)"; ?></option>


									<?php endforeach; ?>

									</optgroup>

								<?php endforeach; ?>

							<?php else : ?>

								<?php foreach ( $args['value'] as $value => $title ) : ?>

									<?php
									if ( ! empty( $trigger_post_id ) && strpos( $trigger, $input_select_name ) !== false ) {
										$selected = (string) $value === (string) $trigger_post_id ? 'selected="selected"' : '';
									} else {
										$selected = (string) $value === (string) $key_value ? 'selected="selected"' : '';
									}
									?>

								<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $title; ?></option>

								<?php endforeach; ?>

							<?php endif; ?>

						</select>

					<?php endif; ?>

					<?php if ( $args['type'] == 'text' ) : ?>

						<?php $value = ! empty( $key_value ) ? $key_value : $default; ?>

						<input type="text" size="<?php echo $args['size']; ?>" name="<?php echo $input_name; ?>" value="<?php echo $value; ?>" style="width: initial;">

						<label><?php echo $args['label']; ?></label>

					<?php endif; ?>

					<?php if ( $args['type'] == 'checkbox' ) : ?>

						<?php $cb_input_name = is_array( $args['value'] ) ? $input_name . '[]' : $input_name; ?>

						<?php if ( is_array( $args['value'] ) && count( $args['value'] ) > 1 ) : ?>

							<?php foreach ( $args['value'] as $value => $label ) : ?>

								<?php $checked = in_array( $value, $key_value ) ? 'checked="checked"' : ''; ?>

							<input type="checkbox" name="<?php echo $cb_input_name; ?>" id="<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?>>
							<label for="<?php echo $value; ?>"><?php echo $label; ?></label><br />

							<?php endforeach; ?>

						<?php else : ?>

							<?php $checked = $value == $key_value ? 'checked="checked"' : ''; ?>

							<input type="checkbox" name="<?php echo $cb_input_name; ?>" id="<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?>>
							<label for="<?php echo $value; ?>"><?php echo $label; ?></label><br />

						<?php endif; // End if $args['value'] is array check. ?>

					<?php endif; // End if $args['type'] is checkbox check. ?>

					<?php if ( $args['type'] == 'number' ) : ?>

						<?php $value = ! empty( $key_value ) ? $key_value : $default; ?>

						<input type="number" name="<?php echo $input_name; ?>" value="<?php echo $value; ?>" min="0" style="min-width: 10px; width: 70px;">

						<?php if ( isset( $args['label'] ) ) : ?>
							<label><?php echo $args['label']; ?></label>
						<?php endif; ?>

					<?php endif; ?>

				</div>
				<div class="sfwd_help_text_div" style="display: none;" id="learndash-achievements_<?php echo $key; ?>_tip">
					<label class="sfwd_help_text"><?php echo $args['help_text']; ?></label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>

		<?php
		echo ob_get_clean();
	}

	/**
	 * Get metabox settings
	 *
	 * @return array Metabos settings
	 */
	public static function get_settings() {
		$groups       = get_posts( 'post_type=groups&posts_per_page=-1&orderby=title&order=ASC' );
		$groups_array = array();
		foreach ( $groups as $g ) {
			$groups_array[ $g->ID ] = $g->post_title;
		}

		$courses       = get_posts( 'post_type=sfwd-courses&posts_per_page=-1&orderby=title&order=ASC' );
		$courses_array = array();
		foreach ( $courses as $c ) {
			$courses_array[ $c->ID ] = $c->post_title;
		}

		$lessons       = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1&orderby=title&order=ASC' );
		$lessons_array = array();
		foreach ( $lessons as $l ) {
			$lessons_array[ $l->ID ] = $l->post_title;
		}

		$topics       = get_posts( 'post_type=sfwd-topic&posts_per_page=-1&orderby=title&order=ASC' );
		$topics_array = array();
		foreach ( $topics as $t ) {
			$topics_array[ $t->ID ] = $t->post_title;
		}

		$quizzes       = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1&orderby=title&order=ASC' );
		$quizzes_array = array();
		foreach ( $quizzes as $q ) {
			$quizzes_array[ $q->ID ] = $q->post_title;
		}

		$settings = array(
			'trigger'     => array(
				'type'            => 'dropdown',
				'title'           => __( 'Trigger', 'learndash-achievements' ),
				'help_text'       => __( 'When achievement will be given and displayed.', 'learndash-achievements' ),
				'disable_on_edit' => false,
				'value'           => Achievement::get_triggers(),
			),
			'group_id'    => array(
				'type'            => 'dropdown',
				'title'           => __( 'Group', 'learndash-achievements' ),
				'help_text'       => __( 'Group that the trigger is assigned to.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => true,
				'parent'          => array( 'enroll_group' ),
				'value'           => array(
					''    => __( '-- Select Group --', 'learndash-achievements' ),
					'all' => __( 'All Groups', 'learndash-achievements' ),
				) + $groups_array,
			),
			'course_id'   => array(
				'type'            => 'dropdown',
				'title'           => __( 'Course', 'learndash-achievements' ),
				'help_text'       => __( 'Course that the trigger is assigned to.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => false,
				'class'           => 'parent_field',
				'parent'          => array( 'enroll_course', 'complete_course', 'course_expires', 'not_logged_in', 'complete_lesson', 'lesson_available', 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz', 'quiz_score_above' ),
				'value'           => array(
					''    => __( '-- Select Course --', 'learndash-achievements' ),
					'all' => __( 'All Courses', 'learndash-achievements' ),
				) + $courses_array,
			),
			'lesson_id'   => array(
				'type'            => 'dropdown',
				'title'           => __( 'Lesson', 'learndash-achievements' ),
				'help_text'       => __( 'Lesson that the trigger is assigned to.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => false,
				'class'           => 'parent_field child_field',
				'parent'          => array( 'complete_lesson', 'lesson_available', 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz', 'quiz_score_above' ),
				'value'           => array(
					''    => __( '-- Select Lesson --', 'learndash-achievements' ),
					'all' => __( 'All Lessons', 'learndash-achievements' ),
				) + $lessons_array,
			),
			'topic_id'    => array(
				'type'            => 'dropdown',
				'title'           => __( 'Topic', 'learndash-achievements' ),
				'help_text'       => __( 'Topic that the trigger is assigned to.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => false,
				'class'           => 'parent_field child_field',
				'parent'          => array( 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz', 'quiz_score_above' ),
				'value'           => array(
					''    => __( '-- Select Topic --', 'learndash-achievements' ),
					'all' => __( 'All Topics', 'learndash-achievements' ),
				) + $topics_array,
			),
			'quiz_id'     => array(
				'type'            => 'dropdown',
				'title'           => __( 'Quiz', 'learndash-achievements' ),
				'help_text'       => __( 'Quiz that the trigger is assigned to.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => false,
				'class'           => 'child_field',
				'parent'          => array( 'pass_quiz', 'fail_quiz', 'complete_quiz', 'quiz_score_above' ),
				'value'           => array(
					''    => __( '-- Select Quiz --', 'learndash-achievements' ),
					'all' => __( 'All Quizzes', 'learndash-achievements' ),
				) + $quizzes_array,
			),
			'percentage'  => array(
				'type'      => 'number',
				'title'     => __( 'Percent', 'learndash-achievements' ),
				'help_text' => __( 'Placeholder', 'learndash-achievements' ),
				'hide'      => 1,
				'parent'    => array( 'quiz_score_above' ),
			),
			'user_group'  => array(
				'type'            => 'dropdown',
				'title'           => __( 'User\'s group', 'learndash-achievements' ),
				'help_text'       => __( 'If provided, then the user must be in this group to achieve the badge.', 'learndash-achievements' ),
				'hide'            => 1,
				'disable_on_edit' => false,
				'parent'          => array( 'enroll_course', 'complete_course', 'course_expires', 'not_logged_in', 'complete_lesson', 'lesson_available', 'complete_topic', 'complete_quiz', 'pass_quiz', 'fail_quiz' ),
				'value'           => array(
					'' => __( '-- Select Group --', 'learndash-achievements' ),
				) + $groups_array,
			),
			'points'      => array(
				'type'      => 'number',
				'title'     => __( 'Points', 'learndash-achievements' ),
				'help_text' => __( 'How many points will be awarded for the achievement.', 'learndash-achievements' ),
				'default'   => 0,
			),
			'occurrences' => array(
				'type'      => 'number',
				'title'     => __( 'Occurrences', 'learndash-achievements' ),
				'help_text' => __( 'Maximum number of occurrences the achievement will be given for. Enter 0 for unlimited occurrences.', 'learndash-achievements' ),
				'default'   => 0,
			),
		);

		return apply_filters( 'learndash_achievements_metabox_settings', $settings );
	}
}

new Meta_Box();
