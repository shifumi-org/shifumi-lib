<?php
/**
 * LearnDash Admin Export Post Type Settings.
 *
 * @since   4.3.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (
	class_exists( 'Learndash_Admin_Export' ) &&
	trait_exists( 'Learndash_Admin_Import_Export_Post_Type_Settings' ) &&
	! class_exists( 'Learndash_Admin_Export_Post_Type_Settings' )
) {
	/**
	 * Class LearnDash Admin Export Post Type Settings.
	 *
	 * @since 4.3.0
	 */
	class Learndash_Admin_Export_Post_Type_Settings extends Learndash_Admin_Export {
		use Learndash_Admin_Import_Export_Post_Type_Settings;

		const POST_TYPE_SETTING_PAGE = array(
			LDLMS_Post_Types::COURSE      => 'LearnDash_Settings_Page_Courses_Options',
			LDLMS_Post_Types::LESSON      => 'LearnDash_Settings_Page_Lessons_Options',
			LDLMS_Post_Types::TOPIC       => 'LearnDash_Settings_Page_Topics_Options',
			LDLMS_Post_Types::QUIZ        => 'LearnDash_Settings_Page_Quizzes_Options',
			LDLMS_Post_Types::QUESTION    => 'LearnDash_Settings_Page_Questions_Options',
			LDLMS_Post_Types::CERTIFICATE => 'LearnDash_Settings_Page_Certificates_Options',
			LDLMS_Post_Types::GROUP       => 'LearnDash_Settings_Page_Groups_Options',
			LDLMS_Post_Types::ASSIGNMENT  => 'LearnDash_Settings_Page_Assignments_Options',
		);

		/**
		 * Constructor.
		 *
		 * @since 4.3.0
		 *
		 * @param string                               $post_type    Post type.
		 * @param Learndash_Admin_Export_File_Handler  $file_handler File Handler class instance.
		 * @param Learndash_Admin_Import_Export_Logger $logger       Logger class instance.
		 *
		 * @return void
		 */
		public function __construct(
			string $post_type,
			Learndash_Admin_Export_File_Handler $file_handler,
			Learndash_Admin_Import_Export_Logger $logger
		) {
			$this->post_type = $post_type;

			parent::__construct( $file_handler, $logger );
		}

		/**
		 * Returns the list of settings associated with the post type.
		 *
		 * @since 4.3.0
		 *
		 * @return string
		 */
		public function get_data(): string {
			$section_key = LDLMS_Post_Types::get_post_type_key( $this->post_type );

			if ( empty( $section_key ) ) {
				$section_key = $this->post_type;
			}

			$sections = $this->get_sections( $section_key );

			if ( empty( $sections ) ) {
				return wp_json_encode( array() );
			}

			$result = array();

			foreach ( $sections as $section ) {
				$data = array(
					'name'   => $section,
					'fields' => $section::get_settings_all(),
				);

				/**
				 * Filters the post type settings object to export.
				 *
				 * @since 4.3.0
				 *
				 * @param array $data Settings object.
				 *
				 * @return array Settings object.
				 */
				$data = apply_filters( 'learndash_export_post_type_settings_object', $data );

				$result[] = $data;
			}

			return wp_json_encode( $result );
		}

		/**
		 * Returns post type settings sections.
		 *
		 * @since 4.3.0
		 *
		 * @param string $section_key Section Key.
		 *
		 * @return array
		 */
		protected function get_sections( string $section_key ): array {
			if ( ! array_key_exists( $section_key, self::POST_TYPE_SETTING_PAGE ) ) {
				return array();
			}

			$page = self::POST_TYPE_SETTING_PAGE[ $section_key ];

			$sections = array_map(
				function ( LearnDash_Settings_Section $section ) {
					return get_class( $section );
				},
				LearnDash_Settings_Page::get_page_instance( $page )
					->get_settings_sections()
			);

			/**
			 * Filters the list of post type settings sections to export.
			 *
			 * @since 4.3.0
			 *
			 * @param array $sections Post type settings sections.
			 *
			 * @return array Post type settings sections.
			 */
			$sections = apply_filters( 'learndash_export_post_type_settings_sections', $sections );

			return array_filter(
				$sections,
				function ( $section ) {
					return is_subclass_of( $section, 'LearnDash_Settings_Section' );
				}
			);
		}
	}
}
