<?php
if ( ( !class_exists( 'LearnDash_ProPanel_Reporting_Filter_Courses' ) ) && ( class_exists( 'LearnDash_ProPanel_Filtering' ) ) ) {
	class LearnDash_ProPanel_Reporting_Filter_Courses extends LearnDash_ProPanel_Filtering {

		public function __construct() {
			$this->filter_key = 'courses';
			$this->filter_search_placeholder = __( 'Search Users', 'ld_propanel' );
			$this->filter_template_table = 'reporting-filters/courses/ld-propanel-reporting-filter-course-table.php';
			$this->filter_template_row = 'reporting-filters/courses/ld-propanel-reporting-filter-course-row.php';
			
			add_filter( 'ld_propanel_filtering_register_filters', array( $this, 'filter_register' ), 20 );

			add_filter( 'ld_propanel_reporting_post_args', array( $this, 'filter_post_args' ), 20, 2 );
			add_filter( 'ld_propanel_reporting_activity_args', array( $this, 'filter_activity_args' ), 20, 3 );
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
						$activity_args['post_ids'] = $post_data['filters'][$this->filter_key];
					}
				} else if ( ( !isset( $activity_args['post_ids'] ) ) || ( empty( $activity_args['post_ids'] ) ) ) {
					if ( learndash_is_admin_user( get_current_user_id() ) ) {
						if ( ( empty( $activity_args['post_ids'] ) ) && ( !empty( $post_data['filters']['users'] ) ) ) {
							$users_course_ids = array();
							foreach( $post_data['filters']['users'] as $user_id ) {
								$course_ids = learndash_user_get_enrolled_courses( $user_id, true );
								if ( !empty( $course_ids ) ) {
									$users_course_ids = array_merge( $users_course_ids, $course_ids );
								}
							}
							if ( !empty( $users_course_ids ) ) {
								$activity_args['post_ids'] = $users_course_ids;
							} else {
								$activity_args = array();
							}							
						} 
					} else if ( learndash_is_group_leader_user( get_current_user_id() ) ) {
						// Check if the this groups leader is a leader of this course
						$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
						if ( !empty( $group_ids ) ) {
							// Now if we have a filtered user we filter the courses further to only inlcude the courses 
							if ( ! empty( $post_data['filters']['users'] ) )  {
								$user_group_ids = array();
								foreach( $post_data['filters']['users'] as $user_id ) {
									$user_group_ids = array_merge( $user_group_ids, learndash_get_users_group_ids( $user_id ) );
								}

								if ( ! empty( $user_group_ids ) ) {
									$group_ids = array_intersect( $group_ids, $user_group_ids );
								} else {
									$group_ids = array();
								}
							}
							if ( ! empty( $group_ids ) ) {
								$course_ids = array();
								foreach ( $group_ids as $group_id ) {
									$group_course_ids = learndash_group_enrolled_courses( $group_id );
									if ( ! empty( $group_course_ids ) ) {
										$course_ids = array_merge( $course_ids, $group_course_ids );
									}
								}
							}

							if ( ! empty( $course_ids ) ) {
								$activity_args['post_ids'] = $course_ids;
							} else {
								$activity_args = array();
							}
						}
						
					} else {
						$course_ids = learndash_user_get_enrolled_courses( get_current_user_id() );
						if ( !empty( $course_ids ) ) {
							$activity_args['post_ids'] = $course_ids;
						} else {
							$activity_args = array();
						}
					}
				}
			}
			
			return $activity_args;
		}


		public function filter_display() {
			return '<select class="filter-courses select2" data-ajax--cache="true" data-allow-clear="true" data-placeholder="'. sprintf(
				// translators: All Courses
				esc_html_x( 'All %s', 'All Courses', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'courses' )
			) .'"><option value="">'. sprintf( 
				// translators: All Courses
				esc_html_x( 'All %s', 'All Courses', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'courses' ) ) .'</option></select>';
		}

		function filter_search() {

			$courses_data = array(
				'total'	=>	0,
				'items'	=>	array()
			);

			$course_query_args = array(
				'post_type' => 'sfwd-courses',
				'post_status' => 'publish',
				'orderby' => 'post_title',
				'order' => 'ASC',
				//'s' => esc_attr( $_GET['search'] ),
				'posts_per_page' => 10,
				'offset' => ( intval( $_GET['page'] ) - 1 ) * 10,
				'paged' => intval( $_GET['page'] ),
			);

			if ( ( isset( $_GET['search'] ) ) && ( !empty( $_GET['search'] ) ) ) {
				$course_query_args['s'] = esc_attr( $_GET['search'] );
			}
			
			if ( learndash_is_admin_user( get_current_user_id() ) ) {
				$groups_course_ids = array();
				if ( ( isset( $this->post_data['filters']['groups'] ) ) && ( !empty( $this->post_data['filters']['groups'] ) ) ) {
					foreach ( $this->post_data['filters']['groups'] as $group_id ) {
						$course_ids = learndash_group_enrolled_courses( $group_id );
						if ( !empty( $course_ids ) ) {
							$groups_course_ids = array_merge( $groups_course_ids, $course_ids );
						}
					}
					if ( empty( $groups_course_ids ) ) {
						$course_query_args = array();
					}
				}
				
				$users_course_ids = array();
				if ( ( isset( $this->post_data['filters']['users'] ) ) && ( !empty( $this->post_data['filters']['users'] ) ) ) {
					foreach ( $this->post_data['filters']['users'] as $user_id ) {
						$course_ids = learndash_user_get_enrolled_courses( $user_id );
						if ( !empty( $course_ids ) ) {
							$users_course_ids = array_merge( $users_course_ids, $course_ids );
						}
						if ( empty( $users_course_ids ) ) {
							$course_query_args = array();
						}
					}
				}
				
				if ( ( !empty( $groups_course_ids ) ) && ( !empty( $users_course_ids ) ) ) {
					$course_query_args['post__in'] = array_intersect( $groups_course_ids, $users_course_ids );
				} else if ( !empty( $groups_course_ids ) ) {
					$course_query_args['post__in'] = $groups_course_ids;
				} else if ( !empty( $users_course_ids ) ) {
					$course_query_args['post__in'] = $users_course_ids;
				} 
				
			} else if ( learndash_is_group_leader_user( get_current_user_id() ) ) {

				$groups_course_ids = array();
				if ( ( isset( $this->post_data['filters']['groups'] ) ) && ( !empty( $this->post_data['filters']['groups'] ) ) ) {
					foreach ( $this->post_data['filters']['groups'] as $group_id ) {
						$course_ids = learndash_group_enrolled_courses( $group_id );
						if ( !empty( $course_ids ) ) {
							$groups_course_ids = array_merge( $groups_course_ids, $course_ids );
						}
					}
					if ( empty( $groups_course_ids ) ) {
						$course_query_args = array();
					}
				}
				
				$users_course_ids = array();
				if ( ( isset( $this->post_data['filters']['users'] ) ) && ( !empty( $this->post_data['filters']['users'] ) ) ) {
					foreach ( $this->post_data['filters']['users'] as $user_id ) {
						$course_ids = learndash_user_get_enrolled_courses( $user_id );
						if ( !empty( $course_ids ) ) {
							$users_course_ids = array_merge( $users_course_ids, $course_ids );
						}
					}
					if ( empty( $users_course_ids ) ) {
						$course_query_args = array();
					}
				}
				
				if ( ( !empty( $groups_course_ids ) ) && ( !empty( $users_course_ids ) ) ) {
					$course_query_args['post__in'] = array_intersect( $groups_course_ids, $users_course_ids );
				} else if ( !empty( $groups_course_ids ) ) {
					$course_query_args['post__in'] = $groups_course_ids;
				} else if ( !empty( $users_course_ids ) ) {
					$course_query_args['post__in'] = $users_course_ids;
				} else {
					// If we don't have any filtered by group courses. Then grab all the courses the GL can manage. 
					$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
					if ( !empty( $group_ids ) ) {
						$course_ids = learndash_get_groups_courses_ids( get_current_user_id(), $group_ids );	
						if ( !empty( $course_ids ) ) {
							$course_query_args['post__in'] = $course_ids;
						} else {
							$course_query_args = array();
						}
					} else {
						$course_query_args = array();
					}
				}
			} else {
				$user_course_ids = learndash_user_get_enrolled_courses( get_current_user_id() );
				if ( !empty( $user_course_ids ) ) 
					$course_query_args['post__in'] = $user_course_ids;
				else
					$course_query_args = array();
			}
			
			if ( !empty( $course_query_args ) ) {
				$course_query = new WP_Query( $course_query_args );
				if ( $course_query->have_posts() ) {
					$courses_data['total'] = intval( $course_query->found_posts );
					
					foreach ( $course_query->posts as $course ) {
						$courses_data['items'][] = array(
							'id' => $course->ID,
							'text' => strip_tags( $course->post_title ),
						);
					}
				}
			}

			/**
			 * Filter courses returned in search
			 */
			return apply_filters( 'ld_propanel_filter_search', $courses_data, $this->filter_key, $course_query_args );
		}
		
		function filter_build_table() {
			$this->filter_table_headers();
			$container_type = $_GET['container_type'];

			ob_start();
			include ld_propanel_get_template( $this->filter_template_table );
			return ob_get_clean();
		}

		function filter_table_headers() {
			if ( 'widget' == $this->post_data['container_type'] ) {
				$this->filter_headers = array( 
					'checkbox'	=>	__( 'Checkbox', 'ld_propanel' ), 
					'user'		=>	__( 'User', 'ld_propanel' ), 
					'progress'	=>	__( 'Progress', 'ld_propanel' ) 
				);

			} else if ( 'full' == $this->post_data['container_type'] ) {
				$this->filter_headers = array( 
					'checkbox'		=>	__( 'Checkbox', 'ld_propanel' ), 
					'user_id'		=>	__( 'User ID', 'ld_propanel' ), 
					'user'			=>	__( 'User', 'ld_propanel' ), 
					'progress'		=>	__( 'Progress', 'ld_propanel' ), 
					'last_update'	=>	__( 'Completed On', 'ld_propanel' ) 
				);

			} else if ( 'shortcode' == $this->post_data['container_type'] ) {
				$this->filter_headers = array( 
					'user'		=>	__( 'User', 'ld_propanel' ), 
					'progress'	=>	__( 'Progress', 'ld_propanel' ) 
				);

			}
			
			return apply_filters('ld-propanel-reporting-headers', $this->filter_headers, $this->filter_key );
		}
		
		function filter_result_rows( $course_id ) {

			// Set the initial response. In case all following queries fail. 
			$response = array(
				'total_rows' => 0, 
				'rows_html' => '',
				//'user_ids' => array(),
				'total_users' => 0
			);
			
			$this->filter_table_headers();
			
			$activity_query_defaults = array(
				'post_types' 		=> 	'sfwd-courses',
				'activity_types'	=>	'course',
				'activity_status'	=>	'',
				'orderby_order'		=>	'users.display_name, posts.post_title',
//				'date_format'		=>	'F j, Y H:i:s',
			);
			
			$this->activity_query_args = wp_parse_args( $this->activity_query_args, $activity_query_defaults );
						
			$this->activity_query_args = ld_propanel_load_activity_query_args( $this->activity_query_args, $this->post_data );
			
			if ( !empty( $this->activity_query_args ) ) {
				
				$this->activity_query_args = ld_propanel_adjust_admin_users( $this->activity_query_args );				
				$this->activity_query_args = ld_propanel_convert_fewer_users( $this->activity_query_args );
								
				/**
				 * Get the goodies
				 */
				//error_log('course: activity_query_args<pre>'. print_r($this->activity_query_args, true) .'</pre>');
				$activities = learndash_reports_get_activity( $this->activity_query_args );
				//error_log('course: activities<pre>'. print_r($activities, true) .'</pre>');
				
				if ( ( isset( $activities['results'] ) ) && ( !empty( $activities['results'] ) ) ) {

					if ( ( isset( $activities['pager'] ) ) && ( !empty( $activities['pager'] ) ) ) {
						$response['total_rows'] = $activities['pager']['total_items'];
						$response['total_users'] = $activities['pager']['total_items'];
						
						$activities['pager']['current_page'] = $this->activity_query_args['paged'];
						$response[ 'pager' ] = $activities['pager'];
					}

					foreach ( $activities['results'] as $activity ) {
						//$response['user_ids'][$activity->user_id] = $activity->user_id;

						$row = array();
						$row_html = '<tr>';

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
			
			if ( !empty( $response['user_ids'] ) ) 
				$response['user_ids'] = array_values( $response['user_ids'] );
			
			return $response;
		}

		// End of functions 
	}
}

add_action( 'learndash_propanel_filtering_init', function() {
	new LearnDash_ProPanel_Reporting_Filter_Courses();
});
