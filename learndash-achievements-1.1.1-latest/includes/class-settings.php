<?php

namespace LearnDash\Achievements;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Settings class
 *
 * This class is responsible for managing plugin settings.
 *
 * @since 1.0
 */
class Settings {

	/**
	 * Plugin options
	 *
	 * @since 1.0
	 * @var array
	 */
	protected $options;

	/**
	 * Class __construct function
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_learndash_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'learndash_submenu', array( $this, 'submenu_item' ) );
		add_action( 'learndash_admin_tabs_set', array( $this, 'admin_tabs_set' ), 10, 2 );

		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 99, 2 );
	}

	/**
	 * Get default value of learndash_achievements_settings_popup option
	 *
	 * @return array Default setting values
	 */
	public static function get_default_value() {
		return array(
			'popup_time'       => 0,
			'background_color' => '#ffffff',
			'text_color'       => '#333333',
			'rtl'              => 0,
		);
	}

	/**
	 * Check if LearnDash plugin is active
	 */
	public function check_learndash_plugin() {
		if ( ! is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			deactivate_plugins( plugin_basename( LEARNDASH_ACHIEVEMENTS_FILE ) );
		}
	}

	/**
	 * Display admin notice when LearnDash plugin is not activated
	 */
	public function admin_notices() {
		echo '<div class="error"><p>' . esc_html__( 'LearnDash plugin is required to activate LearnDash Achievements add-on plugin. Please activate it first.', 'learndash-achievements' ) . '</p></div>';
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! is_admin() || ( 'ld-achievement' !== $screen->id && 'edit-ld-achievement' !== $screen->id && 'admin_page_ld-achievements-settings' !== $screen->id ) ) {
			return;
		}
		// Enqueue media script.
		wp_enqueue_media();

		// Enqueue color picker.
		wp_enqueue_style( 'wp-color-picker' );

		// Load our admin JS.
		wp_enqueue_script(
			'learndash-achievements-admin-script',
			LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'assets/js/admin-script.js',
			array(
				'jquery',
				'wp-color-picker',
			),
			LEARNDASH_ACHIEVEMENTS_VERSION,
			true
		);

		wp_localize_script(
			'learndash-achievements-admin-script',
			'LD_Achievements_String',
			array(
				'nonce'               => wp_create_nonce( 'ld_achievements_nonce' ),
				'select_lesson'       => __( '-- Select Lesson --', 'learndash-achievements' ),
				'select_topic'        => __( '-- Select Topic --', 'learndash-achievements' ),
				'select_quiz'         => __( '-- Select Quiz --', 'learndash-achievements' ),
				'select_course_first' => __( '-- Select Course First --', 'learndash-achievements' ),
				'select_lesson_first' => __( '-- Select Lesson First --', 'learndash-achievements' ),
				'select_topic_first'  => __( '-- Select Topic First --', 'learndash-achievements' ),
				'select_quiz_first'   => __( '-- Select Quiz First --', 'learndash-achievements' ),
				'all_lessons'         => __( 'All Lessons', 'learndash-achievements' ),
				'all_topics'          => __( 'All Topics', 'learndash-achievements' ),
				'all_quizzes'         => __( 'All Quizzes', 'learndash-achievements' ),
			)
		);

		// Load our style.
		wp_enqueue_style( 'learndash-achievements-admin-style', LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'assets/css/admin-style.css', array(), LEARNDASH_ACHIEVEMENTS_VERSION, 'screen' );
	}

	/**
	 * Add zapier submenu in Learndash menu
	 *
	 * @param array $submenu Existing submenu.
	 *
	 * @return array           New submenu.
	 */
	public function submenu_item( $submenu ) {
		$menu = array(
			'ld-achievements' => array(
				'name' => __( 'Achievements', 'learndash-achievements' ),
				'cap'  => 'manage_options', // @TODO Need to confirm this capability on the menu.
				'link' => 'edit.php?post_type=ld-achievement',
			),
		);

		array_splice( $submenu, 9, 0, $menu );

		return $submenu;
	}

	/**
	 * Add tabs to achievement pages
	 *
	 * @param string $current_screen_parent_file Current screen parent.
	 * @param object $tabs Learndash_Admin_Menus_Tabs object.
	 */
	public function admin_tabs_set( $current_screen_parent_file, $tabs ) {
		$screen = get_current_screen();

		if ( 'learndash-lms' === $current_screen_parent_file && 'ld-achievement' === $screen->post_type ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-achievement',
					'name' => __( 'Add New', 'learndash-achievements' ),
					'id'   => 'ld-achievement',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-achievement',
					'name' => __( 'Achievements', 'learndash-achievements' ),
					'id'   => 'edit-ld-achievement',
				),
				2
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'admin.php?page=ld-achievements-settings',
					'name' => __( 'Settings', 'learndash-achievements' ),
					'id'   => 'ld-achievements-settings',
				),
				3
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'admin.php?page=ld-achievements-shortcodes',
					'name' => __( 'Shortcodes', 'learndash-achievements' ),
					'id'   => 'ld-achievements-shortcodes',
				),
				4
			);

		} elseif ( 'edit.php?post_type=ld-achievement' === $current_screen_parent_file ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-achievement',
					'name' => __( 'Add New', 'learndash-achievements' ),
					'id'   => 'ld-achievement',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-achievement',
					'name' => __( 'Achievements', 'learndash-achievements' ),
					'id'   => 'edit-ld-achievement',
				),
				2
			);
		}
	}

	/**
	 * Filter row actions for ld-achievement post type
	 *
	 * @param array  $actions Post actions array.
	 * @param object $post WP_Post object.
	 *
	 * @return string          Modified $actions.
	 */
	public function row_actions( $actions, $post ) {
		if ( 'ld-achievement' === $post->post_type ) {
			unset( $actions['view'] );
		}

		return $actions;
	}
}

new Settings();
