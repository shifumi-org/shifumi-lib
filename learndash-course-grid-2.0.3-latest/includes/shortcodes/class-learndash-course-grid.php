<?php
namespace LearnDash\Course_Grid\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

use WP_Query;
use LearnDash\Course_Grid\Utilities;

class LearnDash_Course_Grid
{
    public $tag;

    public function __construct()
    {
        $this->tag = 'learndash_course_grid';
        $this->register();
    }

    public function register()
    {
        add_shortcode( $this->tag, [ $this, 'render' ] );
    }

    public function get_default_atts()
    {
        return apply_filters( 'learndash_course_grid_default_shortcode_attributes', [
            // Query
            'post_type' => 'sfwd-courses',
            'per_page'  => 9,
            'orderby'   => 'ID',
            'order'     => 'DESC',
            'taxonomies' => '',
            'enrollment_status' => '',
            'progress_status' => '',
            // Elements
            'thumbnail' => true,
            'thumbnail_size' => 'thumbnail',
            'ribbon' => true,
            'video' => false,
            /**
             * Content includes title, description and button
             */
            'content' => true,
            'title' => true,
            'title_clickable' => true,
            'description' => true,
            'description_char_max' => 120,
            'button' => true,
            'filter' => true,
            /**
             * Accepts:
             * 
             * 'button'   : Load more button
             * 'infinite' : Infinite scrolling 
             * 'pages'    : Normal AJAX pagination with number 1, 2, 3, and so on
             * 'false'    : Doesn't have pagination
             */
            'pagination' => 'button',
            'grid_height_equal' => false,
            'progress_bar' => false,
            'meta' => true,
            // Template
            /**
             * Accepts: 
             * 
             * All values available in templates/skins 
             */
            'skin' => 'grid',
            'card' => 'grid-1',
            /**
             * Only used in certain skin such as 'grid' and 'masonry'
             */
            'columns' => 3,
            'min_column_width' => 250,
            /**
             * Only used in certain skin such as 'carousel'
             */
            'items_per_row' => 3,
            // Styles
            'font_family_title' => '',
            'font_family_description' => '',
            'font_size_title' => '',
            'font_size_description' => '',
            'font_color_title' => '',
            'font_color_description' => '',
            'background_color_title' => '',
            'background_color_description' => '',
            // Misc
            'class_name' => '',
            /**
             * Random unique ID for CSS styling purpose
             */
            'id' => '',
            // Filter
            'filter_search' => true,
            'filter_taxonomies' => 'category, post_tag',
            'filter_price' => true,
            'filter_price_min' => 0,
            'filter_price_max' => 1000,
        ] );
    }

    public function render( $atts = [], $content = '' )
    {
        $atts = shortcode_atts( $this->get_default_atts(), $atts, $this->tag );

        if ( empty( $atts['per_page'] ) ) {
            $atts['per_page'] = -1;
        }

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

        $query_args = apply_filters( 'learndash_course_grid_query_args', [
            'post_type' => sanitize_text_field( $atts['post_type'] ),
            'posts_per_page' => intval( $atts['per_page'] ),
            'post_status' => 'publish',
            'orderby' => sanitize_text_field( $atts['orderby'] ),
            'order' => sanitize_text_field( $atts['order'] ),
            'tax_query' => $tax_query,
            'post__in' => $post__in,
        ], $atts, $filter = null );

        // Query the posts
        $query = new WP_Query( $query_args );

        $posts = $query->get_posts();
        $max_num_pages = $query->max_num_pages;

        if ( $max_num_pages > 1 ) {
            $has_pagination = true;
        } else {
            $has_pagination = false;
        }
        
        $empty_id = false;
        if ( empty( $atts['id'] ) ) {
            $empty_id = true;
            $atts['id'] = Utilities::generate_random_id();
        }

        $filter = filter_var( $atts['filter'], FILTER_VALIDATE_BOOLEAN );

        // Get the template file
        $template = Utilities::get_skin_layout( $atts['skin'] );
        $pagination_template = Utilities::get_pagination_template( $atts['pagination'] );

        // Include the template file
        ob_start();

        if ( $empty_id ) {
            echo sprintf( '<!-- %s -->', __( 'The LearnDash course grid element below doesn\'t have "id" attribute set in the shortcode so it\'s generated randomyly each time it renders. Please set the "id" attribute in the shortcode so it will be consistent each time it renders. If it\'s not consistent, custom CSS rules in the site that uses the element particular ID may not work.', 'learndash-course-grid' ) );    
        }
        
        echo '<div id="' . esc_attr( $atts['id'] ) . '" class="learndash-course-grid ' . esc_attr( $atts['class_name'] ) . '" data-page="1" ' . $this->process_attributes_as_html_attributes( $atts ) . '>';
        
        if ( $filter === true ) {
            echo '<button class="toggle-filter">' . __( 'Filter', 'learndash-course-grid' ) . '</button>';

            echo do_shortcode( '[learndash_course_grid_filter course_grid_id="' . esc_attr( $atts['id'] ) . '" search="' . esc_attr( $atts['filter_search'] ) . '" taxonomies="' . esc_attr( $atts['filter_taxonomies'] ) . '" price="' . esc_attr( $atts['filter_price'] ) . '" price_min="' . esc_attr( $atts['filter_price_min'] ) . '" price_max="' . esc_attr( $atts['filter_price_max'] ) . '"]' );
        }

        if ( $template ) {
            include $template;
        }

        if ( $pagination_template && $has_pagination ) {
            include $pagination_template;
        }

        echo '</div>';
        
        // Return the template HTML string
        return ob_get_clean();
    }

    public function process_attributes_as_html_attributes( $atts = [] )
    {
        $attributes = '';

        foreach ( $atts as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( ',', $value );
            }

            $attributes .= ' data-' . $key . '="' . $value . '"';    
        }

        return $attributes;
    }
}
