<?php
/**
 * LearnDash Settings Metabox for Topic Access Settings.
 *
 * @since 3.0.0
 * @package LearnDash\Settings\Metaboxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Metabox' ) ) && ( ! class_exists( 'LearnDash_Settings_Metabox_Topic_Access_Settings' ) ) ) {
	/**
	 * Class LearnDash Settings Metabox for Topic Access Settings.
	 *
	 * @since 3.0.0
	 */
	class LearnDash_Settings_Metabox_Topic_Access_Settings extends LearnDash_Settings_Metabox {

		/**
		 * Public constructor for class
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			// What screen ID are we showing on.
			$this->settings_screen_id = 'sfwd-topic';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_metabox_key = 'learndash-topic-access-settings';

			// Section label/header.
			$this->settings_section_label = sprintf(
				// translators: placeholder: Topic.
				esc_html_x( '%s Access Settings', 'placeholder: Topic', 'learndash' ),
				learndash_get_custom_label( 'topic' )
			);

			$this->settings_section_description = sprintf(
				// translators: placeholder: topic.
				esc_html_x( 'Controls how, where, and when the %s can be accessed.', 'placeholder: topic', 'learndash' ),
				learndash_get_custom_label( 'topic' )
			);

			add_filter( 'learndash_metabox_save_fields_' . $this->settings_metabox_key, array( $this, 'filter_saved_fields' ), 30, 3 );
			add_filter( 'learndash_show_metabox', array( $this, 'check_show_metabox' ), 50, 2 );

			// Map internal settings field ID to legacy field ID.
			$this->settings_fields_map = array(
				'course' => 'course',
				'lesson' => 'lesson',
			);

			parent::__construct();
		}

		/**
		 * Hook into filter before the metabox is registered in WP.
		 *
		 * @since 3.0.0
		 *
		 * @param boolean $show_metabox True or False to show metabox.
		 * @param string  $settings_metabox_key metabox key.
		 *
		 * @return boolean $show_metabox
		 */
		public function check_show_metabox( $show_metabox, $settings_metabox_key = '' ) {
			if ( $settings_metabox_key === $this->settings_metabox_key ) {
				// IF Course shared Steps is enabled we don't show this metabox.
				if ( learndash_is_course_shared_steps_enabled() ) {
					$show_metabox = false;
				}
			}

			return $show_metabox;
		}
		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();
			if ( true === $this->settings_values_loaded ) {
				if ( ! isset( $this->setting_option_values['course'] ) ) {
					$this->setting_option_values['course'] = '';
				}
				if ( ! isset( $this->setting_option_values['lesson'] ) ) {
					$this->setting_option_values['lesson'] = '';
				}
			}

			// Ensure all settings fields are present.
			foreach ( $this->settings_fields_map as $_internal => $_external ) {
				if ( ! isset( $this->setting_option_values[ $_internal ] ) ) {
					$this->setting_option_values[ $_internal ] = '';
				}
			}
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 3.0.0
		 */
		public function load_settings_fields() {
			global $sfwd_lms;

			$select_course_options         = array();
			$select_course_query_data_json = '';

			if ( learndash_use_select2_lib() ) {
				$select_course_options_default = array(
					'-1' => sprintf(
						// translators: placeholder: course.
						esc_html_x( 'Search or select a %s…', 'placeholder: course', 'learndash' ),
						learndash_get_custom_label( 'course' )
					),
				);

				if ( ! empty( $this->setting_option_values['course'] ) ) {
					$course_post = get_post( absint( $this->setting_option_values['course'] ) );
					if ( ( $course_post ) && ( is_a( $course_post, 'WP_Post' ) ) ) {
						$select_course_options[ $course_post->ID ] = get_the_title( $course_post->ID );
					}
				}

				if ( learndash_use_select2_lib_ajax_fetch() ) {
					$select_course_query_data_json = $this->build_settings_select2_lib_ajax_fetch_json(
						array(
							'query_args'       => array(
								'post_type' => 'sfwd-courses',
							),
							'settings_element' => array(
								'settings_parent_class' => get_parent_class( __CLASS__ ),
								'settings_class'        => __CLASS__,
								'settings_field'        => 'course',
							),
						)
					);
				} else {
					$select_course_options = $sfwd_lms->select_a_course();
				}
				$select_course_options = $select_course_options_default + $select_course_options;
			} else {
				$select_course_options_default = array(
					'' => sprintf(
						// translators: placeholder: course.
						esc_html_x( 'Select %s', 'placeholder: course', 'learndash' ),
						learndash_get_custom_label( 'course' )
					),
				);
				$select_course_options         = $sfwd_lms->select_a_course();
				if ( ( is_array( $select_course_options ) ) && ( ! empty( $select_course_options ) ) ) {
					$select_course_options = $select_course_options_default + $select_course_options;
				} else {
					$select_course_options = $select_course_options_default;
				}
				$select_course_options_default = '';
			}

			$select_lesson_options = array();
			if ( ( isset( $this->setting_option_values['course'] ) ) && ( ! empty( $this->setting_option_values['course'] ) ) ) {
				$select_lesson_options = $sfwd_lms->select_a_lesson( absint( $this->setting_option_values['course'] ) );
			}

			if ( learndash_use_select2_lib() ) {
				$select_lesson_options_default = array(
					'-1' => sprintf(
						// translators: placeholder: Lesson.
						esc_html_x( 'Search or select a %s…', 'placeholder: Lesson', 'learndash' ),
						learndash_get_custom_label( 'lesson' )
					),
				);
			} else {
				$select_lesson_options_default = array(
					'' => sprintf(
						// translators: placeholder: Lesson.
						esc_html_x( 'Select %s', 'placeholder: Lesson', 'learndash' ),
						learndash_get_custom_label( 'lesson' )
					),
				);

				$select_lesson_options = array();
				if ( ( isset( $this->setting_option_values['course'] ) ) && ( ! empty( $this->setting_option_values['course'] ) ) ) {
					$select_lesson_options = $sfwd_lms->select_a_lesson( absint( $this->setting_option_values['course'] ) );
					if ( ( is_array( $select_lesson_options ) ) && ( ! empty( $select_lesson_options ) ) ) {
						if ( isset( $select_lesson_options[0] ) ) {
							unset( $select_lesson_options[0] );
						}

						$select_lesson_options = $select_lesson_options_default + $select_lesson_options;
					} else {
						$select_lesson_options = $select_lesson_options_default;
					}
				} else {
					$select_lesson_options = $select_lesson_options_default;
				}
			}

			$this->setting_option_fields = array(
				'course' => array(
					'name'        => 'course',
					'label'       => sprintf(
						// translators: placeholder: course.
						esc_html_x( 'Associated %s', 'Associated Course Label', 'learndash' ),
						learndash_get_custom_label( 'course' )
					),
					'type'        => 'select',
					'lazy_load'   => true,
					'help_text'   => sprintf(
						// translators: placeholders: Topic, Course.
						esc_html_x( 'Associate this %1$s with a %2$s.', 'placeholder: Topic, Course.', 'learndash' ),
						learndash_get_custom_label( 'topic' ),
						learndash_get_custom_label( 'course' )
					),
					'default'     => '',
					'value'       => $this->setting_option_values['course'],
					'options'     => $select_course_options,
					'placeholder' => $select_course_options_default,
					'attrs'       => array(
						'data-ld_selector_nonce'   => wp_create_nonce( 'sfwd-courses' ),
						'data-ld_selector_default' => '1',
						'data-select2-query-data'  => $select_course_query_data_json,
					),
					'rest'        => array(
						'show_in_rest' => LearnDash_REST_API::enabled(),
						'rest_args'    => array(
							'schema' => array(
								'type'    => 'integer',
								'default' => 0,
							),
						),
					),
				),
				'lesson' => array(
					'name'        => 'lesson',
					'label'       => sprintf(
						// translators: placeholder: Lesson.
						esc_html_x( 'Associated %s', 'placeholder: Lesson', 'learndash' ),
						learndash_get_custom_label( 'lesson' )
					),
					'type'        => 'select',
					'lazy_load'   => true,
					'help_text'   => sprintf(
						// translators: placeholders: Topic, Lesson.
						esc_html_x( 'Associate this %1$s with a %2$s.', 'placeholders: Topic, Lesson', 'learndash' ),
						learndash_get_custom_label( 'topic' ),
						learndash_get_custom_label( 'lesson' )
					),
					'default'     => '',
					'value'       => $this->setting_option_values['lesson'],
					'options'     => $select_lesson_options,
					'placeholder' => $select_lesson_options_default,
					'attrs'       => array(
						'data-ld_selector_nonce'   => wp_create_nonce( 'sfwd-lessons' ),
						'data-ld_selector_default' => '1',
					),
					'rest'        => array(
						'show_in_rest' => LearnDash_REST_API::enabled(),
						'rest_args'    => array(
							'schema' => array(
								'type'    => 'integer',
								'default' => 0,
							),
						),
					),
				),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_metabox_key );

			parent::load_settings_fields();
		}

		/**
		 * Filter settings values for metabox before save to database.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $settings_values Array of settings values.
		 * @param string $settings_metabox_key Metabox key.
		 * @param string $settings_screen_id Screen ID.
		 *
		 * @return array $settings_values.
		 */
		public function filter_saved_fields( $settings_values = array(), $settings_metabox_key = '', $settings_screen_id = '' ) {
			if ( ( $settings_screen_id === $this->settings_screen_id ) && ( $settings_metabox_key === $this->settings_metabox_key ) ) {

				if ( ( ! isset( $settings_values['course'] ) ) || ( '-1' === $settings_values['course'] ) ) {
					$settings_values['course'] = '';
				}

				if ( ( ! isset( $settings_values['lesson'] ) ) || ( '-1' === $settings_values['lesson'] ) ) {
					$settings_values['lesson'] = '';
				}

				if ( learndash_is_course_shared_steps_enabled() ) {
					unset( $settings_values['course'] );
					unset( $settings_values['lesson'] );
				}
			}

			return $settings_values;
		}

		// End of functions.
	}

	add_filter(
		'learndash_post_settings_metaboxes_init_' . learndash_get_post_type_slug( 'topic' ),
		function( $metaboxes = array() ) {
			if ( ( ! isset( $metaboxes['LearnDash_Settings_Metabox_Topic_Access_Settings'] ) ) && ( class_exists( 'LearnDash_Settings_Metabox_Topic_Access_Settings' ) ) ) {
				$metaboxes['LearnDash_Settings_Metabox_Topic_Access_Settings'] = LearnDash_Settings_Metabox_Topic_Access_Settings::add_metabox_instance();
			}

			return $metaboxes;
		},
		50,
		1
	);
}

