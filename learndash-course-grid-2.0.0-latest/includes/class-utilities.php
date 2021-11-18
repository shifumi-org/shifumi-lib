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
            0 === strpos( realpath( $template ), realpath( STYLESHEETPATH ) )
            || 0 === strpos( realpath( $template ), realpath( TEMPLATEPATH ) )
            || 0 === strpos( realpath( $template ), ABSPATH . WPINC . '/theme-compat/' )
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
            0 === strpos( realpath( $template ), realpath( STYLESHEETPATH ) )
            || 0 === strpos( realpath( $template ), realpath( TEMPLATEPATH ) )
            || 0 === strpos( realpath( $template ), ABSPATH . WPINC . '/theme-compat/' )
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
                'label' => __( 'Author' ),
                'value' => 'author',
            ],
            [
                'label' => __( 'Menu Order' ),
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
}