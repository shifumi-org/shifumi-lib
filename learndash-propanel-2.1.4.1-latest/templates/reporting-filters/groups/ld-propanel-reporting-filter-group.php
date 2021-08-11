<?php
if ( ( !class_exists( 'LearnDash_ProPanel_Reporting_Filter_Groups' ) ) && ( class_exists( 'LearnDash_ProPanel_Filtering' ) ) ) {
	class LearnDash_ProPanel_Reporting_Filter_Groups extends LearnDash_ProPanel_Filtering {

		public function __construct() {
			$this->filter_key = 'groups';
			$this->filter_search_placeholder = __( 'Search Groups', 'ld_propanel' );

			// Path relative to the plugin templates directory
			$this->filter_template_table = 'reporting-filters/groups/ld-propanel-reporting-filter-group-table.php';
			$this->filter_template_row = 'reporting-filters/groups/ld-propanel-reporting-filter-group-row.php';

			add_filter( 'ld_propanel_filtering_register_filters', array( $this, 'filter_register' ), 10 );

			add_filter( 'ld_propanel_reporting_post_args', array( $this, 'filter_post_args' ), 10, 2 );
			add_filter( 'ld_propanel_reporting_activity_args', array( $this, 'filter_activity_args' ), 10, 3 );
		}

		public function filter_post_args( $post_args = array(), $_get = array() ) {

			if ( ( isset( $_get['filters'][$this->filter_key] ) ) && ( !empty( $_get['filters'][$this->filter_key] ) ) ) {
				if ( is_string( $_get['filters'][$this->filter_key] ) ) {
					$post_args['filters'][$this->filter_key] = explode(',', $_get['filters'][$this->filter_key] );
				} else {
					$post_args['filters'][$this->filter_key] = $_get['filters'][$this->filter_key];
				}

				$post_args['filters'][$this->filter_key] = array_map( 'intval', $post_args['filters'][$this->filter_key] );
			}

			return $post_args;
		}

		public function filter_activity_args( $activity_args = array(), $post_data = array(), $_get = array() ) {
			if ( !empty( $activity_args ) ) {

				if ( ( isset( $post_data['filters'][$this->filter_key] ) ) && ( !empty( $post_data['filters'][$this->filter_key] ) ) ) {

					if ( ( !isset( $activity_args['post_ids'] ) ) || ( empty( $activity_args['post_ids'] ) ) ) {
						$group_course_ids = array();

						foreach( $post_data['filters'][$this->filter_key] as $group_id ) {
							$course_ids = learndash_group_enrolled_courses( $group_id );
							if ( !empty( $course_ids ) ) {
								$group_course_ids = array_merge( $group_course_ids, $course_ids );
							}
						}

						if ( !empty( $group_course_ids ) ) {

							if ( ( isset( $post_data['filters']['courses'] ) ) && ( !empty( $post_data['filters']['courses'] ) ) ) {
								$activity_args['post_ids'] = array_intersect( $group_course_ids, $post_data['filters']['courses'] );
							} else {
								$activity_args['post_ids'] = $group_course_ids;
							}
						} else {
							// If the group has no courses, abort and return
							$activity_args = array();
							return $activity_args;
						}
					}

					if ( ( !isset( $activity_args['user_ids'] ) ) || ( empty( $activity_args['user_ids'] ) ) ) {
						if ( ( isset( $post_data['filters']['users'] ) ) && ( !empty( $post_data['filters']['users'] ) ) ) {
							$activity_args['user_ids'] = $post_data['filters']['users'];
							$activity_args['user_ids_action'] = 'IN';
						} else {
							$group_user_ids = array();

							foreach( $post_data['filters'][$this->filter_key] as $group_id ) {
								$user_ids = learndash_get_groups_user_ids( $group_id );
								if ( !empty( $user_ids ) ) {
									$group_user_ids = array_merge( $group_user_ids, $user_ids );
								}
							}

							if ( !empty( $group_user_ids ) ) {
								$activity_args['user_ids'] = $group_user_ids;
								$activity_args['user_ids_action'] = 'IN';

							} else {
								// If the group has no users, abort and return
								$activity_args = array();
								return $activity_args;
							}
						}
					}
				}
			}

			return $activity_args;
		}

		public function filter_display() {

			if ( learndash_is_admin_user( get_current_user_id() ) ) {
				if ( !ld_propanel_count_post_type( 'groups' ) )
					return;

			} else if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
				$leader_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
				if ( empty( $leader_group_ids ) )
					return;

			} else {
				$use_group_ids = learndash_get_users_group_ids( get_current_user_id() );
				if ( empty( $use_group_ids ) )
					return;
			}

			return '<select class="filter-groups select2" data-ajax--cache="true" data-allow-clear="true" data-placeholder="'. esc_html__( 'All Groups', 'ld_propanel' ) .'"><option value="">'. esc_html__( 'All Groups', 'ld_propanel' ) .'</option></select>';

		}

		public function filter_search() {

			$groups_data = array(
				'total'	=>	0,
				'items'	=>	array()
			);

			$group_query_args = array(
				'post_type' => 'groups',
				'post_status' => 'publish',
				'orderby' => 'post_title',
				'order' => 'ASC',
				'posts_per_page' => 10,
				'paged' => intval( $_GET['page'] ),
			);

			if ( ( isset( $_GET['search'] ) ) && ( !empty( $_GET['search'] ) ) ) {
				$group_query_args['s'] = esc_attr( $_GET['search'] );
			}

			if ( learndash_is_admin_user( get_current_user_id() ) ) {
				$courses_group_ids = array();
				if ( !empty( $this->post_data['filters']['courses'] ) ) {
					foreach( $this->post_data['filters']['courses'] as $course_id ) {
						$group_ids = learndash_get_course_groups( $course_id );
						if ( !empty( $group_ids ) ) {
							$courses_group_ids = array_merge( $courses_group_ids, $group_ids );
						}
					}
				}

				$users_group_ids = array();
				if ( !empty( $this->post_data['filters']['users'] ) ) {
					foreach( $this->post_data['filters']['users'] as $user_id ) {
						$group_ids = learndash_get_users_group_ids( $user_id, true );
						if ( !empty( $group_ids ) ) {
							$users_group_ids = array_merge( $users_group_ids, $group_ids );
						}
					}
				}

				if ( ( !empty( $this->post_data['filters']['courses'] ) ) && ( !empty( $this->post_data['filters']['users'] ) ) ) {
					if ( ( !empty( $courses_group_ids ) ) && ( !empty( $users_group_ids ) ) ) {
						$group_query_args['post__in'] = array_intersect( $courses_group_ids, $users_group_ids );
					} else {
						$group_query_args = array();
					}
				} else if ( !empty( $this->post_data['filters']['courses'] ) ) {
					if ( !empty( $courses_group_ids ) )  {
						$group_query_args['post__in'] = $courses_group_ids;
					} else {
						$group_query_args = array();
					}
				} else if ( !empty( $this->post_data['filters']['users'] ) ) {
					if ( !empty( $users_group_ids ) )  {
						$group_query_args['post__in'] = $users_group_ids;
					} else {
						$group_query_args = array();
					}
				}

			} else if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
				$admin_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
				if ( !empty( $admin_group_ids ) ) {
					$search_group_ids = array();

					if ( !empty( $this->post_data['filters']['courses'] ) ) {
						foreach( $this->post_data['filters']['courses'] as $course_id ) {
							$group_ids = learndash_get_course_groups( $course_id );
							if ( !empty( $group_ids ) ) {
								$group_ids = array_intersect( $group_ids, $admin_group_ids );
								if ( !empty( $group_ids ) ) {
									$search_group_ids = array_merge( $search_group_ids, $group_ids );
								}
							}
						}
					}

					if ( !empty( $this->post_data['filters']['users'] ) ) {
						foreach( $this->post_data['filters']['users'] as $user_id ) {
							$group_ids = learndash_get_users_group_ids( $user_id, true );
							if ( !empty( $group_ids ) ) {
								$search_group_ids = array_merge( $search_group_ids, $group_ids );
							}
						}
					}

					if ( !empty( $search_group_ids ) )
						$group_query_args['post__in'] = $search_group_ids;
					else
						$group_query_args['post__in'] = $admin_group_ids;

				} else {
					// If group leader and no defined groups then clear out query array so they get no results.
					$group_query_args = array();
				}
			} else {
				$user_group_ids = learndash_get_users_group_ids( get_current_user_id(), true );
				if ( !empty( $user_group_ids ) ) {
					if ( !empty( $this->post_data['filters']['courses'] ) ) {
						$course_group_ids = array();
						foreach( $this->post_data['filters']['courses'] as $course_id ) {
							$group_ids = learndash_get_course_groups( $course_id );
							if ( !empty( $group_ids ) ) {
								$course_group_ids = array_merge( $course_group_ids, $group_ids );
							}
						}

						if ( !empty( $course_group_ids ) ) {
							$user_group_ids = array_intersect( $user_group_ids, $course_group_ids );
						}
					}

					if ( !empty( $user_group_ids ) )
						$group_query_args['post__in'] = $user_group_ids;
					else
						$group_query_args = array();

				} else {
					// If group leader and no defined groups then clear out query array so they get no results.
					$group_query_args = array();
				}
			}

			if ( !empty( $group_query_args ) ) {
				$group_query = new WP_Query( $group_query_args );
				if ( $group_query->have_posts() ) {
					$groups_data['total'] = intval( $group_query->found_posts );

					foreach ( $group_query->posts as $group ) {
						$groups_data['items'][] = array(
							'id' => $group->ID,
							'text' => strip_tags( $group->post_title ),
						);
					}
				}
			}

			/**
			 * Filter courses returned in search
			 */
			return apply_filters( 'ld_propanel_filter_search', $groups_data, $this->filter_key, $group_query_args );
		}

		function filter_build_table() {
			$this->filter_table_headers();
			$container_type = $_GET['container_type'];

			ob_start();
			include ld_propanel_get_template( $this->filter_template_table );
			return ob_get_clean();
		}

		function filter_table_headers() {
			$this->filter_headers = array();

			if ( 'widget' == $this->post_data['container_type'] ) {
				$this->filter_headers =  array(
					'checkbox'		=>	esc_html__( 'Checkbox', 'ld_propanel' ),
					// translators: Course.
					'course' 		=>	sprintf( esc_html_x( '%s', 'Course', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'courses' ) ),
					// translators: Course Progress
					'progress'		=>	esc_html_x( 'Progress', 'Course Progress', 'ld_propanel' ),
				);

			} else if ( 'full' == $this->post_data['container_type'] ) {
				$this->filter_headers = array(
					'checkbox'		=>	esc_html__( 'Checkbox', 'ld_propanel' ),
					// translators: Course ID.
					'course_id'		=>	sprintf( esc_html_x( '%s ID', 'Course ID', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ),
					// translators: Course.
					'course' 		=>	sprintf( esc_html_x( '%s', 'Course', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ),
					// translators: User ID
					'user_id'		=>	esc_html_x( 'U-ID', 'User ID', 'ld_propanel' ),
					'user'			=>	esc_html__( 'User', 'ld_propanel' ),
					// translators: Course Progress
					'progress'		=>	esc_html_x( 'Progress', 'Course Progress', 'ld_propanel' ),
					// translators: Course Progress
					'last_update'	=>	esc_html_x( 'Completed On', 'Course completion data', 'ld_propanel' )
				);
			} else if ( 'shortcode' == $this->post_data['container_type'] ) {
				$this->filter_headers =  array(
					// translators: Course.
					'course' 		=>	sprintf( esc_html_x( '%s', 'Course', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'courses' ) ),
					// translators: Course Progress
					'progress'		=>	esc_html_x( 'Progress', 'Course Progress', 'ld_propanel' ),
				);

			}

			return apply_filters('ld-propanel-reporting-headers', $this->filter_headers, $this->filter_key );
		}

		function filter_result_rows( $group_id = 0 ) {

			// Set the initial response. In case all following queries fail.
			$response = array(
				'rows_html' => '',
				//'total_users' => 0
			);

			$this->filter_table_headers();

			$activity_query_defaults = array(
				'post_types' 		=> 	'sfwd-courses',
				'activity_types'	=>	'course',
				'activity_status'	=>	'',
				'orderby_order'		=>	'posts.post_title, users.display_name',
			);

			$this->activity_query_args = wp_parse_args( $this->activity_query_args, $activity_query_defaults );

			//$this->activity_query_args = apply_filters( 'ld_propanel_reporting_activity_args', $this->activity_query_args, $this->post_data );
			$this->activity_query_args = ld_propanel_load_activity_query_args( $this->activity_query_args, $this->post_data );
			//error_log('activity_query_args<pre>'. print_r($this->activity_query_args, true) .'</pre>');

			if ( !empty( $this->activity_query_args ) ) {

				//if ( !isset( $this->activity_query_args['user_ids_action'] ) ) {
				//	$this->activity_args['user_ids_action'] = 'IN';
				//}
				//$response['total_users'] = count( $this->activity_query_args['user_ids'] );

				$this->activity_query_args = ld_propanel_adjust_admin_users( $this->activity_query_args );

				//$response['total_users'] = count( $this->activity_query_args['user_ids'] );
				//$response['total_users'] = 'all';

				$this->activity_query_args = ld_propanel_convert_fewer_users( $this->activity_query_args );


				/**
				 * Get the goodies
				 */
				//error_log('group: activity_query_args<pre>'. print_r($this->activity_query_args, true) .'</pre>');
				$activities = learndash_reports_get_activity( $this->activity_query_args );
				//error_log('group: activities<pre>'. print_r($activities, true) .'</pre>');
				if ( ( isset( $activities['results'] ) ) && ( !empty( $activities['results'] ) ) ) {

					if ( ( isset( $activities['pager'] ) ) && ( !empty( $activities['pager'] ) ) ) {
						//$response['total_rows'] = $activities['pager']['total_items'];

						$activities['pager']['current_page'] = $this->activity_query_args['paged'];
						$response[ 'pager' ] = $activities['pager'];
					}

					foreach ( $activities['results'] as $idx => $activity ) {
						$row = array();
						$row_html = '<tr id="ld-propanel-tr-'. $idx .'">';

						foreach ( $this->filter_headers as $header_key => $header_label ) {
							ob_start();
							include ld_propanel_get_template( $this->filter_template_row );
							$row = ob_get_clean();
							$row_html .= '<td class="'. apply_filters( 'ld-propanel-column-class', 'ld-propanel-reporting-col-'. $header_key, $this->filter_key, $header_key, $this->post_data['container_type'] ) .'">'. $row .'</td>';
						}
						$row_html .= '</tr>';
						$response['rows_html'] .= $row_html;
					}
				}
			}

			if ( empty( $response['rows_html'] ) ) {
				ob_start();
				include ld_propanel_get_template( 'ld-propanel-reporting-no-results.php' );
				$response['rows_html'] = ob_get_clean();
			}

			return $response;
		}

		// End of functions
	}
}

add_action( 'learndash_propanel_filtering_init', function() {
	new LearnDash_ProPanel_Reporting_Filter_Groups();
});
