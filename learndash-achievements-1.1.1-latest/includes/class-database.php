<?php

namespace LearnDash\Achievements;

/**
 * DB Class
 */
class Database {

	/**
	 * @var string
	 */
	public static $table_name;

	/**
	 * @var string
	 */
	public static $db_version = '1.0';

	/**
	 * Initial function.
	 */
	public static function init() {
		global $wpdb;

		self::$table_name = "{$wpdb->prefix}ld_achievements";

		// Hooks.
		register_activation_hook( LEARNDASH_ACHIEVEMENTS_FILE, array( __CLASS__, 'create_achievements_table' ) );
		add_action( 'admin_init', array( __CLASS__, 'create_achievements_table' ) );
	}


	public static function create_achievements_table() {
		$current_version = get_option( 'ld_achievements_db_version' );

		if ( version_compare( $current_version, self::$db_version, '==' ) ) {
			return;
		}

		$table_name = self::$table_name;

		global $wpdb;

		$sql = "CREATE TABLE {$table_name} (
			`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`user_id` INT UNSIGNED NOT NULL,
			`post_id` INT UNSIGNED NOT NULL,
			`trigger` VARCHAR(30) NOT NULL,
			`points` INT NOT NULL DEFAULT 0,
			`created_at` TIMESTAMP NOT NULL
		) COLLATE {$wpdb->collate}";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'ld_achievements_db_version', self::$db_version );
	}

	/**
	 * @param $user_id
	 *
	 * @return int
	 */
	public static function get_user_points( $user_id ) {
		global $wpdb;

		$table_name = self::$table_name;

		$sql = $wpdb->prepare(
			"SELECT SUM(points) FROM {$table_name} WHERE `user_id` = %d AND `post_id` IN (SELECT ID FROM wp_posts WHERE `post_status` = 'publish' AND `post_type` = 'ld-achievement') GROUP BY `user_id`",
			$user_id
		);

		$points      = $wpdb->get_var( $sql );
		$points      = absint( $points );
		$used_points = get_user_meta( $user_id, 'achievements_points_used', true );
		if ( empty( $used_points ) ) {
			$used_points = 0;
		}
		$used_points = absint( $used_points );
		$points      = $points - $used_points;

		$extra_points = get_user_meta( $user_id, 'learndash_achievements_extra_points', true );
		$extra_points = absint( $extra_points );

		return $points + $extra_points;
	}

	/**
	 * Getting the achievements belong to a user.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return array
	 */
	public static function get_user_achievements( int $user_id ) {
		global $wpdb;

		$table_name = self::$table_name;

		$sql = $wpdb->prepare(
			"SELECT `id`,`user_id`, `post_id`, `points`, COUNT(*) c,  GROUP_CONCAT(id) ids FROM {$table_name} WHERE `user_id` = %d AND `post_id` IN (SELECT ID FROM wp_posts WHERE `post_status` = 'publish' AND `post_type` = 'ld-achievement') GROUP BY `post_id`",
			$user_id
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Delete the badge
	 *
	 * @param int $id the Badge ID.
	 */
	public static function delete_badge( $id ) {
		global $wpdb;

		$table_name = self::$table_name;
		$wpdb->delete( $table_name, array( 'id' => absint( $id ) ), array( 'id' => '%d' ) );
	}

	/**
	 * Delete all the achievements by badges
	 *
	 * @param int $id The achievement ID.
	 */
	public static function delete_badges_by_achievement_id( $id ) {
		global $wpdb;
		$wpdb->delete( self::$table_name, array( 'post_id' => $id ), array( 'id' => '%d' ) );
	}
}

Database::init();
