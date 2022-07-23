<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Database;

/**
 * Shortcode class
 */
class Shortcode {

	/**
	 * Initial
	 */
	public static function init() {
		// Hooks.
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
	}

	/**
	 * Register the shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'ld_achievements_leaderboard', array( __CLASS__, 'ld_achievements_leaderboard_shortcode' ) );
		add_shortcode( 'ld_my_achievements', array( __CLASS__, 'ld_my_achievements_shortcode' ) );
		// add_shortcode( 'ld_achievements', array( __CLASS__, 'ld_achievements_shortcode' ) );
	}

	/**
	 * Output the leader board content.
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content The content.
	 *
	 * @return false|string
	 * @deprecated
	 */
	public static function ld_achievements_leaderboard_shortcode( $atts, $content ) {
		$atts = shortcode_atts(
			array(
				'number' => 10,
			),
			$atts,
			'ld_achievements_leaderboard'
		);

		// Get leaders in points.
		$leaders = wp_cache_get( 'leaderboard_v2_' . $atts['number'], 'learndash_achievements' );

		if ( false === $leaders ) {
			global $wpdb;

			$table_name = Database::$table_name;

			$sql     = $wpdb->prepare(
				"SELECT COUNT(user_id) as total, user_id, GROUP_CONCAT(post_id SEPARATOR ',') as post_ids FROM {$table_name}
GROUP BY user_id ORDER BY COUNT(user_id) DESC LIMIT %d",
				absint( $atts['number'] )
			);
			$leaders = $wpdb->get_results( $sql, ARRAY_A );
			// we got a list of.
			foreach ( $leaders as &$row ) {
				$post_ids = $row['post_ids'];
				$post_ids = explode( ',', $post_ids );
				$post_ids = array_unique( $post_ids );
				$post_ids = array_filter( $post_ids );
				foreach ( $post_ids as $post_id ) {
					$row['images'][] = get_post_meta( $post_id, 'image', true );
				}
			}

			wp_cache_set( 'leaderboard_v2_' . $atts['number'], $leaders, 'learndash_achievements', HOUR_IN_SECONDS );
		}
		// Display leaders in leaderboard.
		ob_start();
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/shortcode-leaderboard.php';

		return ob_get_clean();
	}

	/**
	 * Output for the shortcode [ld_my_achievements]
	 *
	 * @param array  $atts attributes.
	 * @param string $content Content.
	 *
	 * @return false|string
	 */
	public static function ld_my_achievements_shortcode( $atts, $content ) {
		$atts = shortcode_atts(
			array(
				'user_id'    => get_current_user_id(),
				'show_title' => false,
			),
			$atts,
			'ld_my_achievements'
		);

		$achievements = wp_cache_get( 'achievements_' . $atts['user_id'], 'learndash_achievements' );

		if ( false === $achievements ) {
			global $wpdb;

			$table_name = Database::$table_name;

			$sql = $wpdb->prepare(
				"SELECT `user_id`, `post_id`, `points`, COUNT(*) c FROM {$table_name} WHERE `user_id` = %d GROUP BY `post_id`",
				$atts['user_id']
			);

			$achievements = $wpdb->get_results( $sql );

			wp_cache_set( 'achievements_' . $atts['user_id'], $achievements, 'learndash_achievements', HOUR_IN_SECONDS );
		}

		ob_start();
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/shortcode-achievements.php';

		return ob_get_clean();
	}

	/**
	 * Output the points of an user.
	 *
	 * @param array  $atts attributes.
	 * @param string $content Content.
	 *
	 * @return int|mixed|string
	 */
	public static function ld_achievements_shortcode( $atts, $content ) {
		global $wpdb, $post;

		$atts = shortcode_atts(
			array(
				'post_id' => 0,
				'user_id' => 0,
				'show'    => '',
			),
			$atts,
			'ld_achievements'
		);

		if ( empty( $atts['post_id'] ) || empty( $atts['user_id'] ) ) {
			return '';
		}

		switch ( $atts['show'] ) {
			case 'points':
				$points = get_post_meta( $atts['post_id'], 'points', true );
				$result = ! empty( $points ) ? $points : 0;
				break;

			default:
				$result = '';
				break;
		}

		return $result;
	}
}

Shortcode::init();
