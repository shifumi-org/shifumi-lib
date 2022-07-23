<?php

namespace LearnDash\Achievements\Widget;

use LearnDash\Achievements\Database;
use WP_Widget;

/**
 * Leaderboard widget class
 */
class Leaderboard extends WP_Widget {
	/**
	 * Leaderboard constructor.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ld-achievements-leaderboard-widget',
			'description' => __( 'Display LearnDash Achievements points leaderboard.', 'learndash-achievements' ),
		);
		parent::__construct(
			'ld-achievements-leaderboard-widget',
			__( 'LearnDash Achievements Leaderboard', 'learndash-achievements' ),
			$widget_ops
		);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo do_shortcode( '[ld_achievements_leaderboard]' );
		echo $args['after_widget'];
	}

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Leaderboard', 'learndash-achievements' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_attr( 'Title', 'learndash-achievements' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php

	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}
