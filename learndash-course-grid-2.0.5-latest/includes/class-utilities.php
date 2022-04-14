<?php
namespace LearnDash\Course_Grid;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class Utilities
{
    public static function get_template( $template )
    {
        $template_file = $template . '.php';
        
        $template = locate_template( 'learndash/course-grid/' .  $template_file );

        $template_in_allowed_directory = (
            ( is_string( realpath( $template ) ) && is_string( realpath( STYLESHEETPATH ) ) && 0 === strpos( realpath( $template ), realpath( STYLESHEETPATH ) ) )
            || ( is_string( realpath( $template ) ) && is_string( realpath( TEMPLATEPATH ) ) && 0 === strpos( realpath( $template ), realpath( TEMPLATEPATH ) ) )
            || ( is_string( realpath( $template ) ) && is_string( ABSPATH . WPINC . '/theme-compat/' ) && 0 === strpos( realpath( $template ), ABSPATH . WPINC . '/theme-compat/' ) )
        );

        if ( $template && $template_in_allowed_directory ) {
            return $template;
        } elseif ( file_exists( LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_PATH . $template_file ) ) {
            return LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_PATH . $template_file;
        } else {
            return false;
        }
    }

    public static function get_template_url( $template_file )
    {
        $template = locate_template( 'learndash/course-grid/' .  $template_file );

        $template_in_allowed_directory = (
            ( is_string( realpath( $template ) ) && is_string( realpath( STYLESHEETPATH ) ) && 0 === strpos( realpath( $template ), realpath( STYLESHEETPATH ) ) )
            || ( is_string( realpath( $template ) ) && is_string( realpath( TEMPLATEPATH ) ) && 0 === strpos( realpath( $template ), realpath( TEMPLATEPATH ) ) )
            || ( is_string( realpath( $template ) ) && is_string( ABSPATH . WPINC . '/theme-compat/' ) && 0 === strpos( realpath( $template ), ABSPATH . WPINC . '/theme-compat/' ) )
        );

        if ( $template && $template_in_allowed_directory ) {
            return get_stylesheet_directory_uri() . '/learndash/course-grid/' . $template_file;
        } elseif ( file_exists( LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_PATH . $template_file ) ) {
            return LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_URL . $template_file;
        } else {
            return false;
        }
    }

    public static function get_pagination_template( $type )
    {
        return self::get_template( 'pagination/' . $type );
    }

    public static function get_pagination_style()
    {
        return self::get_template_url( 'pagination/style.css' );
    }

    public static function get_pagination_script()
    {
        return self::get_template_url( 'pagination/script.js' );
    }

    public static function get_skin_layout( $skin )
    {
        return self::get_template( 'skins/' . $skin . '/layout' );
    }

    public static function get_skin_item( $skin )
    {
        return self::get_template( 'skins/' . $skin . '/item' );
    }

    public static function get_card_layout( $card )
    {
        return self::get_template( 'cards/' . $card . '/layout' );
    }

    public static function get_skin_style( $skin )
    {
        return self::get_template_url( 'skins/' . $skin . '/style.css' );
    }

    public static function get_skin_script( $skin )
    {
        return self::get_template_url( 'skins/' . $skin . '/script.js' );
    }

    public static function get_card_style( $card )
    {
        return self::get_template_url( 'cards/' . $card . '/style.css' );
    }

    public static function get_card_script( $card )
    {
        return self::get_template_url( 'cards/' . $card . '/script.js' );
    }

    public static function parse_taxonomies( $taxonomies )
    {
        $taxonomies = ! empty( $taxonomies ) ? array_filter( explode( ';', sanitize_text_field( $taxonomies ) ) ) : [];

        $results = [];
        foreach ( $taxonomies as $taxonomy_entry ) {
            $taxonomy_parts = explode( ':', $taxonomy_entry );

            if ( empty( $taxonomy_parts[0] ) || empty( $taxonomy_parts[1] ) ) {
                continue;
            }

            $taxonomy = trim( $taxonomy_parts[0] );
            $terms = array_map( 'trim', explode( ',', $taxonomy_parts[1] ) );

            if ( ! empty( $taxonomy ) && ! empty( $terms ) ) {
                $results[ $taxonomy ] = [
                    'terms' => $terms,
                ];
            }
        }
        
        return $results;
    }

    public static function build_posts_query_args( $atts = [] )
    {
        if ( empty( $atts['per_page'] ) ) {
            $atts['per_page'] = -1;
        }

        $tax_query = [];

        $taxonomies = ! empty( $atts['taxonomies'] ) ? array_filter( explode( ';', sanitize_text_field( $atts['taxonomies'] ) ) ) : [];

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
        
        $tax_query['relation'] = 'OR';

        $post__in = null;
        if ( in_array( $atts['post_type'], [ 'sfwd-courses', 'groups' ] ) ) {
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
                        } else {
                            // It means course with such progress status doesn't exist, 
                            // we return empty array
                            $post_ids = [];
                        }
                    }
                } elseif ( $atts['post_type'] == 'groups' ) {
                    $post_ids = $group_ids;
                }

                if ( empty( $post_ids ) ) {
                    // Add literal 0 in an array because post__in param 
                    // ignores empty array
                    $post_ids = [ 0 ];
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

        return $query_args;
    }

    public static function get_post_types()
    {
        $post_types = get_post_types( [
            'public' => true,
        ], 'objects' );

        $excluded_post_types = self::get_excluded_post_types();

        $returned_post_types = [];
        foreach ( $post_types as $slug => $post_type ) {
            if ( in_array( $slug, $excluded_post_types ) ) {
                continue;
            }

            $returned_post_types[ $slug ] = $post_type;
        }

        return apply_filters( 'learndash_course_grid_post_types', $returned_post_types );
    }

    public static function get_post_types_slugs()
    {
        $post_types = self::get_post_types();
        $temp_post_types = [];
        foreach ( $post_types as $slug => $post_type ) {
            $temp_post_types[] = $slug;
        }
        $post_types = $temp_post_types;

        return $post_types;
    }

    public static function get_post_types_for_block_editor()
    {
        $post_types = self::get_post_types();

        $returned_post_types = [];
        foreach ( $post_types as $slug => $post_type ) {
            $returned_post_types[] = [
                'label' => $post_type->label,
                'value' => $slug,
            ];
        }

        return apply_filters( 'learndash_course_grid_block_editor_post_types',  $returned_post_types );
    }

    public static function get_excluded_post_types()
    {
        return apply_filters( 'learndash_course_grid_excluded_post_types', 
            [
                'sfwd-transactions', 
                'sfwd-essays', 
                'sfwd-assignment',
                'sfwd-certificates',
                'attachment',
            ] );
    }

    public static function get_image_sizes_for_block_editor()
    {
        $sizes = get_intermediate_image_sizes();

        $image_sizes = [];
        foreach ( $sizes as $size ) {
            $image_sizes[] = [
                'label' => $size,
                'value' => $size,
            ];
        }

        return apply_filters( 'learndash_course_grid_block_editor_image_sizes',  $image_sizes );
    }

    public static function get_orderby_for_block_editor()
    {
        $orderby = [
            [
                'label' => __( 'ID', 'learndash-course-grid' ),
                'value' => 'ID',
            ],
            [
                'label' => __( 'Title', 'learndash-course-grid' ),
                'value' => 'title',
            ],
            [
                'label' => __( 'Published Date', 'learndash-course-grid' ),
                'value' => 'date',
            ],
            [
                'label' => __( 'Modified Date', 'learndash-course-grid' ),
                'value' => 'modified',
            ],
            [
                'label' => __( 'Author', 'learndash-course-grid' ),
                'value' => 'author',
            ],
            [
                'label' => __( 'Menu Order', 'learndash-course-grid' ),
                'value' => 'menu_order',
            ],
        ];

        return apply_filters( 'learndash_course_grid_block_editor_orderby', $orderby );
    }

    public static function get_taxonomies_for_block_editor()
    {
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

        $return = [];
        foreach ( $taxonomies as $tax ) {
            $return[] = [
                'label' => $tax->label,
                'value' => $tax->name,
            ];
        }

        return apply_filters( 'learndash_course_grid_block_editor_taxonomies', $return );
    }

    public static function get_paginations_for_block_editor()
    {
        return apply_filters( 'learndash_course_grid_block_editor_paginations', [
            [
                'label' => __( 'Load More Button', 'learndash-course-grid' ),
                'value' => 'button',
            ],
            [
                'label' => __( 'Infinite Scrolling', 'learndash-course-grid' ),
                'value' => 'infinite',
            ],
            [
                'label' => __( 'Disable', 'learndash-course-grid' ),
                'value' => 'false',
            ],
        ] );
    }

    public static function generate_random_id()
    {
        return substr( uniqid( 'ld-cg-' ) , 0, 16 );
    }

    public static function get_duration( $post_id, $format = 'plain' )
    {
        $duration = get_post_meta( $post_id, '_learndash_course_grid_duration', true );

        if ( ! empty( $duration ) && is_numeric( $duration ) ) {
            switch ( $format ) {
                case 'plain':
                    $duration = $duration;
                    break;

                case 'output':
                    $duration_h = is_numeric( $duration ) ? floor( $duration / HOUR_IN_SECONDS ) : null;
                    $duration_m = is_numeric( $duration ) ? floor( ( $duration % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS ) : null;
                    $duration = sprintf( _x( '%d h %d min', 'Duration, e.g. 1 hour 30 minutes', 'learndash-course-grid' ), $duration_h, $duration_m );
                    break;
                
                default:
                    $duration = false;
                    break;
            }
        }

        return $duration;
    }

    public static function format_price( $price, $format = 'plain' )
    {
        if ( $format == 'output' ) {
            preg_match( '/(((\d+)[,\.]?)*(\d+)([\.,]?\d+)?)/', $price, $matches );

            $price = $matches[1];

            if ( ! empty( $price ) ) {
                $match_comma_decimal = preg_match( '/(?:\d+\.?)*\d+(,\d{1,2})$/', $price, $comma_matches );

                $match_dot_decimal = preg_match( '/(?:\d+,?)*\d+(\.\d{1,2})$/', $price, $dot_matches );

                if ( $match_comma_decimal ) {
                    $has_decimal = ! empty( $comma_matches[1] ) ? true : false;
                    $thousands_separator = '.';
                    $decimal_separator = ',';
                    $price = str_replace( '.', '', $price );
                    $price = str_replace( ',', '.', $price );
                } else {
                    $has_decimal = ! empty( $dot_matches[1] ) ? true : false;
                    $thousands_separator = ',';
                    $decimal_separator = '.';
                    $price = str_replace( ',', '', $price );
                }
                
                $price = floatval( $price );
        
                if ( $has_decimal ) {
                    $price = number_format( $price, 2, $decimal_separator, $thousands_separator );
                } else {
                    $price = number_format( $price, 0, $decimal_separator, $thousands_separator );
                }
            }

            return $price;
        }

        return $price;
    }

    public static function checked_array( $checked, $data, $disabled = false )
    {
        $output = '';

        if ( is_array( $data ) && in_array( $checked, $data ) )  {
            $output .= 'checked="checked"';
            
            if ( $disabled ) {
                $output .= ' disabled="disabled"';
            }
        }

        echo $output;
    }
}