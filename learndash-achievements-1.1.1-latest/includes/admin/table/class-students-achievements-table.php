<?php

namespace LearnDash\Achievements\Settings\Table;

use LearnDash\Achievements\Database;
use LearnDash\Achievements\Settings;
use LearnDash\Achievements\Template\General_Template;

/**
 * Display the students achievements
 */
class Students_Achievements_Table extends \WP_List_Table {
	/**
	 * @var array
	 */
	protected $groups;

	/**
	 * @var array
	 */
	protected $courses;

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'user'         => __( 'User', 'learndash-achievements' ),
			'groups'       => __( 'Group(s)', 'learndash-achievements' ),
			'achievements' => __( 'Achievements', 'learndash-achievements' ),
		);
	}

	/**
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		$g_id = '';
		if ( isset( $_GET['group'] ) ) {
			$g_id = absint( $_GET['group'] );
		}
		$c_id = '';
		if ( isset( $_GET['course'] ) ) {
			$c_id = absint( $_GET['course'] );
		}
		?>
		<div class="alignleft actions">
			<form method="get" action="<?php echo admin_url( 'admin.php?page=learndash-achievements-students' ); ?>">
				<input type="hidden" name="page" value="learndash-achievements-students"/>
				<select name="group" id="group">
					<option value=""><?php _e( 'All Groups' ); ?></option>
					<?php foreach ( $this->groups as $group ) : ?>
						<option
								<?php
								selected(
									$g_id,
									$group->ID
								);
								?>
								value="<?php echo $group->ID; ?>"><?php echo esc_html( $group->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="course" id="course">
					<option value=""><?php _e( 'All Courses' ); ?></option>
					<?php foreach ( $this->courses as $course ) : ?>
						<option
								<?php
								selected(
									$c_id,
									$course->ID
								);
								?>
								value="<?php echo $course->ID; ?>"><?php echo esc_html( $course->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button">Filter</button>
			</form>
		</div>
		<?php
	}

	/**
	 * Display use name
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	public function column_user( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		return $user->user_login;
	}

	/**
	 * Render all the courses that this user attended.
	 *
	 * @param int $user_id
	 */
	public function column_groups( $user_id ) {
		$data = array();
		foreach ( $this->groups as $group ) {
			if ( learndash_is_user_in_group( $user_id, $group->ID ) ) {
				$data[] = $group->post_title;
			}
		}

		return implode( ', ', $data );
	}

	/**
	 * Show the achievements.
	 *
	 * @param int $user_id
	 */
	public function column_achievements( $user_id ) {
		$achievements = Database::get_user_achievements( $user_id );
		ob_start();
		General_Template::render_badges_table( $achievements, false );

		return ob_get_clean();
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
		foreach ( $group_ids as $group_id ) {
			$this->groups[ $group_id ] = get_post( $group_id );
		}
		$course_ids = learndash_get_group_leader_groups_courses( get_current_user_id() );

		foreach ( $course_ids as $course_id ) {
			$this->courses[ $course_id ] = get_post( $course_id );
		}

		$columns  = $this->get_columns();
		$user_ids = learndash_get_group_leader_groups_users( get_current_user_id() );
		if ( isset( $_GET['group'] ) && ! empty( $_GET['group'] ) ) {
			$g_id = absint( $_GET['group'] );
			// filter out.
			foreach ( $user_ids as $key => $user_id ) {
				if ( ! learndash_is_user_in_group( $user_id, $g_id ) ) {
					unset( $user_ids[ $key ] );
				}
			}
		}

		if ( isset( $_GET['course'] ) && ! empty( $_GET['course'] ) ) {
			$c_id = absint( $_GET['course'] );
			foreach ( $user_ids as $key => $user_id ) {
				$list = learndash_user_get_enrolled_courses( $user_id );
				if ( ! in_array( $c_id, $list, true ) ) {
					unset( $user_ids[ $key ] );
				}
			}
		}
		$paged       = $this->get_pagenum();
		$per_page    = 50;
		$this->items = array_slice( $user_ids, ( $paged - 1 ), $per_page );
		$this->set_pagination_args(
			array(
				'per_page'    => $per_page,
				'total_items' => count( $user_ids ),
			)
		);
		$this->_column_headers = array( $columns, array(), array() );
	}
}
