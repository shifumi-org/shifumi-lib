<?php
namespace LearnDash\Achievements;

defined( 'ABSPATH' ) || exit();

/**
 * Gutenberg class
 */
class Gutenberg {

	public static function init() {
		self::hooks();
		self::includes();
	}

	public static function hooks() {
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_blocks_scripts' ] );
	}

	public static function enqueue_blocks_scripts() {
		wp_enqueue_script( 'ld-achievements-gutenberg-blocks', LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'includes/gutenberg/assets/js/index.js', [ 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' ], LEARNDASH_ACHIEVEMENTS_VERSION, false );
	}

	public static function includes() {
		require_once LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/gutenberg/blocks/ld-achievements-leaderboard/index.php';
		require_once LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/gutenberg/blocks/ld-my-achievements/index.php';
	}
}

Gutenberg::init();
