<?php
/**
 * Enqueue scripts and stylsheets for Blocks
 *
 * @package LearnDash
 * @since 2.5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues block editor styles and scripts.
 *
 * Fires on `enqueue_block_editor_assets` hook.
 *
 * @since 2.5.8
 */
function learndash_editor_scripts() {
	// Make paths variables so we don't write em twice ;).
	$learndash_block_path         = '../assets/js/index.js';
	$learndash_editor_style_path  = '../assets/css/blocks.editor.css';
	$learndash_block_dependencies = include dirname( dirname( __FILE__ ) ) . '/assets/js/index.asset.php';

	// Enqueue the bundled block JS file.
	wp_enqueue_script(
		'ldlms-blocks-js',
		plugins_url( $learndash_block_path, __FILE__ ),
		$learndash_block_dependencies['dependencies'],
		LEARNDASH_SCRIPT_VERSION_TOKEN
	);

	/**
	 * @TODO: This needs to move to an external JS library since it will be used globally.
	 */
	$ldlms                                       = array(
		'settings' => array(),
	);
	$ldlms_settings['settings']['custom_labels'] = LearnDash_Settings_Section_Custom_Labels::get_section_settings_all();
	if ( ( is_array( $ldlms_settings['settings']['custom_labels'] ) ) && ( ! empty( $ldlms_settings['settings']['custom_labels'] ) ) ) {
		foreach ( $ldlms_settings['settings']['custom_labels'] as $key => $val ) {
			if ( empty( $val ) ) {
				$ldlms_settings['settings']['custom_labels'][ $key ] = LearnDash_Custom_Label::get_label( $key );
				if ( substr( $key, 0, strlen( 'button' ) ) != 'button' ) {
					$ldlms_settings['settings']['custom_labels'][ $key . '_lower' ] = learndash_get_custom_label_lower( $key );
					$ldlms_settings['settings']['custom_labels'][ $key . '_slug' ]  = learndash_get_custom_label_slug( $key );
				}
			}
		}
	}

	$ldlms_settings['settings']['per_page']           = LearnDash_Settings_Section_General_Per_Page::get_section_settings_all();
	$ldlms_settings['settings']['courses_taxonomies'] = LearnDash_Settings_Courses_Taxonomies::get_section_settings_all();
	$ldlms_settings['settings']['lessons_taxonomies'] = LearnDash_Settings_Lessons_Taxonomies::get_section_settings_all();
	$ldlms_settings['settings']['topics_taxonomies']  = LearnDash_Settings_Topics_Taxonomies::get_section_settings_all();
	$ldlms_settings['settings']['quizzes_taxonomies'] = LearnDash_Settings_Quizzes_Taxonomies::get_section_settings_all();
	$ldlms_settings['settings']['groups_taxonomies']  = LearnDash_Settings_Groups_Taxonomies::get_section_settings_all();
	$ldlms_settings['settings']['groups_cpt']         = array( 'public' => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Groups_CPT', 'public' ) );

	$ldlms_settings['plugins']['learndash-course-grid']                = array();
	$ldlms_settings['plugins']['learndash-course-grid']['enabled']     = learndash_enqueue_course_grid_scripts();
	$ldlms_settings['plugins']['learndash-course-grid']['col_default'] = 3;
	$ldlms_settings['plugins']['learndash-course-grid']['col_max']     = 12;

	if ( true === $ldlms_settings['plugins']['learndash-course-grid']['enabled'] ) {
		if ( defined( 'LEARNDASH_COURSE_GRID_COLUMNS' ) ) {
			$col_default = intval( LEARNDASH_COURSE_GRID_COLUMNS );
			if ( ( ! empty( $col_default ) ) && ( $col_default > 0 ) ) {
				$ldlms_settings['plugins']['learndash-course-grid']['col_default'] = $col_default;
			}
		}

		if ( defined( 'LEARNDASH_COURSE_GRID_MAX_COLUMNS' ) ) {
			$col_max = intval( LEARNDASH_COURSE_GRID_MAX_COLUMNS );
			if ( ( ! empty( $col_max ) ) && ( $col_max > 0 ) ) {
				$ldlms_settings['plugins']['learndash-course-grid']['col_max'] = $col_max;
			}
		}
	}

	$ldlms_settings['meta']                   = array();
	$ldlms_settings['meta']['posts_per_page'] = get_option( 'posts_per_page' );

	$ldlms_settings['meta']['post']              = array();
	$ldlms_settings['meta']['post']['post_id']   = 0;
	$ldlms_settings['meta']['post']['post_type'] = '';
	$ldlms_settings['meta']['post']['editing']   = '';
	$ldlms_settings['meta']['post']['course_id'] = 0;

	if ( is_admin() ) {
		$current_screen = get_current_screen();
		if ( 'post' === $current_screen->base ) {

			global $post, $post_type, $editing;
			$ldlms_settings['meta']['post'] = array();

			$ldlms_settings['meta']['post']['post_id']   = $post->ID;
			$ldlms_settings['meta']['post']['post_type'] = $post_type;
			$ldlms_settings['meta']['post']['editing']   = $editing;

			$ldlms_settings['meta']['post']['course_id'] = 0;

			if ( ! empty( $post_type ) ) {
				$course_post_types = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' );

				$course_id = 0;
				if ( 'sfwd-courses' === $post_type ) {
					$course_id = $post->ID;
				} elseif ( in_array( $post_type, $course_post_types, true ) ) {
					$course_id = learndash_get_course_id();
				}
				$ldlms_settings['meta']['post']['course_id'] = $course_id;
			}
		}
	}

	// Load the MO file translations into wp.i18n script hook.
	learndash_load_inline_script_locale_data();

	wp_localize_script( 'ldlms-blocks-js', 'ldlms_settings', $ldlms_settings );

	// Enqueue optional editor only styles.
	wp_enqueue_style(
		'ldlms-blocks-editor-css',
		plugins_url( $learndash_editor_style_path, __FILE__ ),
		array(),
		LEARNDASH_SCRIPT_VERSION_TOKEN
	);
	wp_style_add_data( 'ldlms-blocks-editor-css', 'rtl', 'replace' );

	// Call our function to load CSS/JS used by the shortcodes.
	learndash_load_resources();

	$filepath = SFWD_LMS::get_template( 'learndash_pager.css', null, null, true );
	if ( ! empty( $filepath ) ) {
		wp_enqueue_style( 'learndash_pager_css', learndash_template_url_from_path( $filepath ), array(), LEARNDASH_SCRIPT_VERSION_TOKEN );
		wp_style_add_data( 'learndash_pager_css', 'rtl', 'replace' );
		$learndash_assets_loaded['styles']['learndash_pager_css'] = __FUNCTION__;
	}

	$filepath = SFWD_LMS::get_template( 'learndash_pager.js', null, null, true );
	if ( ! empty( $filepath ) ) {
		wp_enqueue_script( 'learndash_pager_js', learndash_template_url_from_path( $filepath ), array( 'jquery' ), LEARNDASH_SCRIPT_VERSION_TOKEN, true );
		$learndash_assets_loaded['scripts']['learndash_pager_js'] = __FUNCTION__;
	}
}
// Hook scripts function into block editor hook.
add_action( 'enqueue_block_editor_assets', 'learndash_editor_scripts' );

/**
 * Enqueues the required styles and scripts for the course grid.
 *
 * @since 2.5.9
 *
 * @return boolean Returns true if the assets are enqueued otherwise false.
 */
function learndash_enqueue_course_grid_scripts() {

	// Check if Course Grid add-on is installed.
	if ( ( defined( 'LEARNDASH_COURSE_GRID_FILE' ) ) && ( file_exists( LEARNDASH_COURSE_GRID_FILE ) ) ) {
		// Newer versions of Coure Grid have a function to load resources.
		if ( function_exists( 'learndash_course_grid_load_resources' ) ) {
			learndash_course_grid_load_resources();
		} else {
			// Handle older versions of Course Grid. 1.4.1 and lower.
			wp_enqueue_style( 'learndash_course_grid_css', plugins_url( 'style.css', LEARNDASH_COURSE_GRID_FILE ) );
			wp_style_add_data( 'learndash_course_grid_css', 'rtl', 'replace' );
			wp_enqueue_script( 'learndash_course_grid_js', plugins_url( 'script.js', LEARNDASH_COURSE_GRID_FILE ), array( 'jquery' ) );
			wp_enqueue_style( 'ld-cga-bootstrap', plugins_url( 'bootstrap.min.css', LEARNDASH_COURSE_GRID_FILE ) );
			wp_style_add_data( 'ld-cga-bootstrap', 'rtl', 'replace' );
		}

		return true;
	}

	return false;
}


/**
 * Registers a custom block category.
 *
 * Fires on `block_categories` hook.
 *
 * @since 2.6.0
 *
 * @param array         $block_categories Optional. An array of current block categories. Default empty array.
 * @param WP_Post|false $post             Optional. The `WP_Post` instance of post being edited. Default false.
 *
 * @return array An array of block categories.
 */
function learndash_block_categories( $block_categories = array(), $post = false ) {
	if ( is_array( $block_categories ) ) {
		if ( ! in_array( 'learndash-blocks', wp_list_pluck( $block_categories, 'slug' ), true ) ) {
			if ( ( $post ) && ( is_a( $post, 'WP_Post' ) ) && ( in_array( $post->post_type, LDLMS_Post_Types::get_post_types(), true ) ) ) {
				$block_categories = array_merge(
					array(
						array(
							'slug'  => 'learndash-blocks',
							'title' => esc_html__( 'LearnDash LMS Blocks', 'learndash' ),
							'icon'  => false,
						),
					),
					$block_categories
				);
			} else {
				$block_categories[] = array(
					'slug'  => 'learndash-blocks',
					'title' => esc_html__( 'LearnDash LMS Blocks', 'learndash' ),
					'icon'  => false,
				);
			}
		}
	}

	// Always return $default_block_categories.
	return $block_categories;
}

/**
 * Registers a custom block category.
 *
 * Fires on `block_categories_all` hook.
 *
 * @since 3.4.2
 *
 * @param array                   $block_categories Optional. An array of current block categories. Default empty array.
 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
 *
 * @return array An array of block categories.
 */
function learndash_block_categories_all( $block_categories, $block_editor_context ) {
	if ( ( is_object( $block_editor_context ) ) && ( property_exists( $block_editor_context, 'post' ) ) && ( is_a( $block_editor_context->post, 'WP_Post' ) ) ) {
		$block_categories = learndash_block_categories( $block_categories, $block_editor_context->post );
	} else {
		$block_categories = learndash_block_categories( $block_categories );
	}

	return $block_categories;
}

add_filter(
	'learndash_init',
	function() {
		global $wp_version;

		if ( version_compare( $wp_version, '5.7.99', '>' ) ) {
			add_filter( 'block_categories_all', 'learndash_block_categories_all', 30, 2 );
		} else {
			add_filter( 'block_categories', 'learndash_block_categories', 30, 2 );
		}
	}
);
