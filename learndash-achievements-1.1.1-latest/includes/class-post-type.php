<?php

namespace LearnDash\Achievements;

use LearnDash\Achievements\Achievement;

/**
 * Post_Type class
 */
class Post_Type {


	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_notices', array( $this, 'error_notice' ) );
		add_filter( 'manage_ld-achievement_posts_columns', array( $this, 'posts_columns' ) );
		add_action( 'manage_ld-achievement_posts_custom_column', array( $this, 'posts_custom_column' ), 10, 2 );
	}

	/**
	 * Registers a new post type
	 *
	 * @uses $wp_post_types Inserts new post type object into the list
	 *
	 * @param string  Post type key, must not exceed 20 characters.
	 * @param array|string  See optional args description above.
	 *
	 * @return object|WP_Error the registered post type object, or an error object
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Achievements', 'learndash-achievements' ),
			'singular_name'      => __( 'Achievement', 'learndash-achievements' ),
			'add_new'            => _x( 'Add New Achievement', 'learndash-achievements', 'learndash-achievements' ),
			'add_new_item'       => __( 'Add New Achievement', 'learndash-achievements' ),
			'edit_item'          => __( 'Edit Achievement', 'learndash-achievements' ),
			'new_item'           => __( 'New Achievement', 'learndash-achievements' ),
			'view_item'          => __( 'View Achievement', 'learndash-achievements' ),
			'search_items'       => __( 'Search Achievements', 'learndash-achievements' ),
			'not_found'          => __( 'No Achievement found', 'learndash-achievements' ),
			'not_found_in_trash' => __( 'No Achievement found in Trash', 'learndash-achievements' ),
			'parent_item_colon'  => __( 'Parent Achievement:', 'learndash-achievements' ),
			'menu_name'          => __( 'Achievements', 'learndash-achievements' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => __( 'Achievements created for LearnDash.', 'learndash-achievements' ),
			'taxonomies'          => array(),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'menu_icon'           => null,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'capabilities'        => array(),
			'map_meta_cap'        => true,
			'supports'            => array(
				'title',
			),
		);

		register_post_type( 'ld-achievement', $args );
	}

	/**
	 * Change achievement updated messages
	 *
	 * @param  array $messages Existing messages.
	 * @return array            New updated messages
	 */
	public function post_updated_messages( $messages ) {
		$messages['ld-achievement'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Achievement updated.' ),
			2  => __( 'Custom field updated.' ),
			3  => __( 'Custom field deleted.' ),
			4  => __( 'Achievement updated.' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Achievement restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Achievement published.' ),
			7  => __( 'Achievement saved.' ),
			8  => __( 'Achievement submitted.' ),
			9  => '',
			10 => __( 'Achievement draft updated.' ),
		);

		return $messages;
	}

	public function error_notice() {
		$error = get_transient( 'ld_achievements_error' );
		if ( $error ) {
			?>

			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_attr( $error->get_error_message() ); ?></p>
			</div>

			<?php
		}
	}

	/**
	 * Add custom columns to ld-achievements listing screen
	 *
	 * @param  array $columns Existing columns.
	 * @return array          New columns.
	 */
	public function posts_columns( $columns ) {
		$new_columns = array(
			'trigger'     => __( 'Trigger', 'learndash-achievements' ),
			'points'      => __( 'Points Awarded', 'learndash-achievements' ),
			'occurrences' => __( 'Occurrences', 'learndash-achievements' ),
			'image'       => __( 'Image', 'learndash-achievements' ),
		);

		$columns = array_slice( $columns, 0, 2 ) + $new_columns + array_slice( $columns, 2 );

		return $columns;
	}

	/**
	 * @param $column_name
	 * @param $post_id
	 */
	public function posts_custom_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'trigger':
				$triggers = Achievement::get_triggers();

				$new_triggers = array_merge( $triggers['WordPress'], $triggers['LearnDash'] );
				$trigger_key  = get_post_meta( $post_id, 'trigger', true );
				$section      = in_array( $trigger_key, array_keys( $triggers['WordPress'] ) ) ? '(WordPress)' : '(LearnDash)';
				echo $new_triggers[ $trigger_key ] . ' ' . $section;
				break;

			case 'points':
				echo get_post_meta( $post_id, 'points', true );
				break;

			case 'occurrences':
				$triggered = Achievement::get_occurrences( $post_id );
				$allowed   = get_post_meta( $post_id, 'occurrences', true );
				$allowed   = $allowed > 0 ? $allowed : __( 'Unlimited', 'learndash-achievements' );

				echo $triggered;
				break;

			case 'image':
				printf( '<img class="listing-image" src="%s">', get_post_meta( $post_id, 'image', true ) );
				break;
		}
	}
}

new Post_Type();
