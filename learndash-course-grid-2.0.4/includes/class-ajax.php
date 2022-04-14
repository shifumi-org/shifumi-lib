<?php
namespace LearnDash\Course_Grid;

use Error;
use WP_Query;
use LearnDash\Course_Grid\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class AJAX {
    
    public function __construct()
    {
        add_action( 'wp_ajax_ld_cg_load_more', [ $this, 'load_posts' ] );
        add_action( 'wp_ajax_nopriv_ld_cg_load_more', [ $this, 'load_posts' ] );
        
        add_action( 'wp_ajax_ld_cg_apply_filter', [ $this, 'load_posts' ] );
        add_action( 'wp_ajax_nopriv_ld_cg_apply_filter', [ $this, 'load_posts' ] );
    }

    public function load_posts()
    {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'ld_cg_load_posts' ) ) {
            wp_die();
        }

        $action_hook = current_filter();
        $hook = false;
        if ( strpos( $action_hook, 'ld_cg_load_more' ) !== false ) {
            $hook = 'load_more';
        } elseif ( strpos( $action_hook, 'ld_cg_apply_filter' ) !== false ) {
            $hook = 'apply_filter';
        }

        if ( ! $hook ) {
            wp_die();
        }

        $filter = json_decode( stripslashes( $_REQUEST['filter'] ), true );
        $atts   = json_decode( stripslashes( $_REQUEST['course_grid'] ), true );

        $post_type = sanitize_text_field( $atts['post_type'] );
        $page      = intval( $atts['page'] );
        $per_page  = intval( $atts['per_page'] );
        $offset    = $page * $per_page;
        $orderby   = sanitize_text_field( $atts['orderby'] );
        $order     = sanitize_text_field( $atts['order'] );
        $search    = isset( $filter['search'] ) ? sanitize_text_field( $filter['search'] ) : null;
        $pagination = sanitize_text_field( $atts['pagination'] );
        $price_min = isset( $filter['price_min'] ) ? floatval( $filter['price_min'] ) : null;
        $price_max = isset( $filter['price_max'] ) ? floatval( $filter['price_max'] ) : null;

        if ( isset( $filter['search'] ) ) {
            unset( $filter['search'] );
        }

        if ( isset( $filter['price_min'] ) ) {
            unset( $filter['price_min'] );
        }

        if ( isset( $filter['price_max'] ) ) {
            unset( $filter['price_max'] );
        }

        $posts = [];

        $tax_query = [];

        $taxonomies = array_filter( explode( ';', sanitize_text_field( $atts['taxonomies'] ) ) );

        foreach ( $taxonomies as $taxonomy_entry ) {
            $taxonomy_parts = explode( ':', $taxonomy_entry );

            if ( empty( $taxonomy_parts[0] ) || empty( $taxonomy_parts[1] ) ) {
                continue;
            }

            $taxonomy = trim( $taxonomy_parts[0] );
            $terms = array_map( 'trim', explode( ',', $taxonomy_parts[1] ) );

            if ( ! empty( $taxonomy ) && ! empty( $terms ) ) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                ];
            }
        }
        
        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        $post__in = null;
        if ( in_array( $atts['post_type'], [ 'sfwd-courses', 'groups' ] ) && is_user_logged_in() ) {
            $user_id = get_current_user_id();

            if ( isset( $atts['enrollment_status'] ) && $atts['enrollment_status'] == 'enrolled' ) {

                $courses = learndash_user_get_enrolled_courses( $user_id );

                $group_ids = learndash_get_users_group_ids( $user_id );
                $groups_courses = learndash_get_groups_courses_ids( $user_id, $group_ids );

                $course_ids = array_merge( $courses, $groups_courses );

                if ( $atts['post_type'] == 'sfwd-courses' ) {
                    $post_ids = $course_ids;

                    if ( isset( $atts['progress_status'] ) && ! empty( $atts['progress_status'] ) ) {
                        $progress_status = [ strtoupper( $atts['progress_status'] ) ];
    
                        $activity_query_args = [
                            'post_types'      => 'sfwd-courses',
                            'activity_types'  => 'course',
                            'activity_status' => $progress_status,
                            'orderby_order'   => 'users.ID, posts.post_title',
                            'date_format'     => 'F j, Y H:i:s',
                            'per_page'        => '',
                        ];
                        $activity_query_args['user_ids'] = [ $user_id ];
                        $activity_query_args['post_ids'] = $post_ids;
                        
                        $user_courses_reports = learndash_reports_get_activity( $activity_query_args );
        
                        $user_courses_ids = [];
                        if ( ! empty( $user_courses_reports['results'] ) ) {
                            foreach ( $user_courses_reports['results'] as $result ) {
                                if ( in_array( 'COMPLETED', $progress_status, true ) ) {
                                    if ( ! empty( $result->activity_completed ) ) {
                                        $user_courses_ids[] = absint( $result->post_id );
                                    }
                                }
                                if ( in_array( 'IN_PROGRESS', $progress_status, true ) ) {
                                    if ( ( ! empty( $result->activity_started ) ) && ( empty( $result->activity_completed ) ) ) {
                                        $user_courses_ids[] = absint( $result->post_id );
                                    }
                                }
        
                                if ( in_array( 'NOT_STARTED', $progress_status, true ) ) {
                                    if ( empty( $result->activity_started ) ) {
                                        $user_courses_ids[] = absint( $result->post_id );
                                    }
                                }
                            }

                            $post_ids = $user_courses_ids;
                        }
                    }

                } elseif ( $atts['post_type'] == 'groups' ) {
                    $post_ids = $group_ids;
                }

            } elseif ( isset( $atts['enrollment_status'] ) && $atts['enrollment_status'] == 'not-enrolled' ) {

                $price_types = [ 'open', 'free', 'paynow', 'subscribe', 'closed' ];

                $all_posts = [];
                foreach ( $price_types as $price_type ) {
                    $post_ids_by_price_type = learndash_get_posts_by_price_type( $atts['post_type'], $price_type );
                    $all_posts = array_merge( $all_posts, $post_ids_by_price_type );
                }
                
                $courses = learndash_user_get_enrolled_courses( $user_id );

                $group_ids = learndash_get_users_group_ids( $user_id );
                $groups_courses = learndash_get_groups_courses_ids( $user_id, $group_ids );

                $course_ids = array_merge( $courses, $groups_courses );

                if ( $atts['post_type'] == 'sfwd-courses' ) {
                    $post_ids = array_diff( $all_posts, $course_ids );
                } elseif ( $atts['post_type'] == 'groups' ) {
                    $post_ids = array_diff( $all_posts, $group_ids );
                }
            }

            if ( ! empty( $post_ids ) ) {
                $post__in = $post_ids;
            }
        }

        $query_args = [
            'post_type'      => $post_type,
            'posts_per_page' => $per_page,
            'offset'         => 0,
            'post_status'    => 'publish',
            'orderby'        => $orderby,
            'order'          => $order,
            's'              => $search,
            'tax_query'      => $tax_query,
            'post__in'       => $post__in,
        ];

        if ( 
            in_array( $post_type, [ 'sfwd-courses', 'groups' ] ) 
            && ( ! empty( $price_min ) || ! empty( $price_max ) )    
        ) {
            $query_args['posts_per_page'] = -1;
        }

        if ( $hook === 'load_more' ) {
            $offset = $page * $per_page;
            $query_args['offset'] = $offset;

            if ( $per_page == -1 ) {
                echo json_encode( [
                    'status' => 'success',
                    'html' => '',
                    'page' => 'complete',
                ] );

                wp_die();
            }
        } elseif ( $hook === 'apply_filter' ) {
            $offset = 0;
            $query_args['offset'] = $offset;
        }

        $tax_count = 0;
        foreach ( $filter as $taxonomy => $values ) {
            $tax_count++;

            if ( ! empty( $values ) && is_array( $values ) ) {
                $values = array_map( function( $id ) {
                    return intval( $id );
                }, $values );

                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $values,
                ];

                if ( count( $query_args['tax_query'] ) > 1 ) {
                    $query_args['tax_query']['relation'] = 'OR';
                }
            }
        }

        $query_args = apply_filters( 'learndash_course_grid_query_args', $query_args, $atts, $filter );

        $query = new WP_Query( $query_args );

        if ( $query->have_posts() ) {
            $posts = $query->get_posts();
        }

        $html = '';
        $html_pagination = '';
        if ( ! empty( $posts ) ) {
            $card     = sanitize_text_field( $atts['card'] );
            $template = Utilities::get_card_layout( $card );
            $has_pagination = false;

            if ( 
                in_array( $post_type, [ 'sfwd-courses', 'groups' ] ) 
                && ( ! empty( $price_min ) || ! empty( $price_max ) )
                && defined( 'LEARNDASH_VERSION' )
            ) {
                // Filter posts
                $posts = array_filter( $posts, function( $post ) use ( $price_min, $price_max ) {
                    $price = false;
                    if ( $post->post_type == 'sfwd-courses' ) {
                        $price = learndash_get_course_price( $post->ID )['price'];
                    } elseif ( $post->post_type == 'groups' ) {
                        $price = learndash_get_group_price( $post->ID )['price'];
                    }

                    $price_min_check = true;
                    if ( $price_min === 0 || $price_min > 0 ) {
                        $price_min_check = $price >= $price_min;
                    }
                    
                    $price_max_check = true;
                    if ( ! empty( $price_max ) ) {
                        $price_max_check = $price <= $price_max;
                    }
                    
                    if ( $price && $price_min_check && $price_max_check ) {
                        return true;
                    } else {
                        return false;
                    }
                } );

                $total_posts   = count( $posts );
                $max_num_pages = ceil( $total_posts / $per_page );

                $posts = array_slice( $posts, $offset, $per_page );
            } else {
                $posts = $query->get_posts();
                $max_num_pages = $query->max_num_pages;
            }

            if ( $max_num_pages > $page ) {
                $has_pagination = true;
            }

            foreach ( $posts as $post ) {
                ob_start();
                if ( $template ) {
                    extract( learndash_course_grid_prepare_template_post_attributes( $post, $atts ) );
                    include $template;
                }

                $html .= ob_get_clean();
            }

            $pagination_template = Utilities::get_pagination_template( $pagination );

            if ( $pagination_template && $has_pagination ) {
                ob_start();
                include $pagination_template;
                $html_pagination .= ob_get_clean();
            }
        } else {
            if ( $hook === 'apply_filter' ) {
                $html .= '<p style="text-align: center;">' . __( 'No results found.', 'learndash-course-grid' ) . '</p>';
            }
        }

        if ( $hook === 'load_more' ) {
            $page = empty( $html ) || ( $page + 2 ) > $max_num_pages ? 'complete' : $page + 1;
        } elseif ( $hook === 'apply_filter' ) {
            $page = 1;
        }

        echo json_encode( [
            'status' => 'success',
            'html'   => $html,
            'html_pagination' => $html_pagination,
            'page'   => $page,
        ] );

        wp_die();
    }
}