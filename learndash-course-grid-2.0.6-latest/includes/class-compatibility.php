<?php
namespace LearnDash\Course_Grid;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

use LearnDash\Course_Grid\Utilities;

class Compatibility
{
    public function __construct()
    {
        add_filter( 'learndash_template', [ $this, 'load_v1_template' ], 100, 5 );

        // Elementor
        add_action( 'elementor/preview/enqueue_styles', [ $this, 'elementor_preview_enqueue_styles' ], 100 );
        add_filter( 'learndash_course_grid_post_extra_course_grids', [ $this, 'elementor_post_extra_course_grids' ], 10, 2 );
        add_action( 'learndash_course_grid_assets_loaded', [ $this, 'elementor_assets_loaded' ] );
    }
    
    public function load_v1_template( $filepath, $name, $args, $echo, $return_file_path )
    {
        if (
            $name === 'course_list_template' 
            && defined( 'LEARNDASH_LMS_PLUGIN_DIR' ) && strpos( $filepath, LEARNDASH_LMS_PLUGIN_DIR ) !== false 
        ) {     
            if ( 
                filter_var( $args['shortcode_atts']['course_grid'], FILTER_VALIDATE_BOOLEAN ) === false 
                || ! isset( $args['shortcode_atts']['course_grid'] ) 
            ) {
                return $filepath;
            }

            $template = Utilities::get_skin_item( 'legacy-v1' );
    
            return apply_filters( 'learndash_course_grid_template', $template, $filepath, $name, $args, $return_file_path );
        }
    
        return $filepath;
    }

    /**
     * Elementor
     */

    public function elementor_preview_enqueue_styles()
    {
        \LearnDash\course_grid()->skins->enqueue_editor_skin_assets();        
    }

    public function elementor_post_extra_course_grids( $course_grids, $post )
    {
        $is_elementor = get_post_meta( $post->ID, '_elementor_edit_mode', true );

        if ( $is_elementor ) {
            global $learndash_course_grid_post_elementor_enabled;
            $learndash_course_grid_post_elementor_enabled = true;

            $elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
            if ( $elementor_data ) {
                $elementor_data = json_decode( $elementor_data, true );
                $elements = Utilities::associative_list_pluck( $elementor_data, 'elements' );

                foreach ( $elements as $element ) {
                    if ( isset( $element['widgetType'] ) ) {
                        switch ( $element['widgetType'] ) {
                            case 'tabs':
                                foreach ( $element['settings']['tabs'] as $tab ) {
                                    $tags = \LearnDash\course_grid()->skins->parse_content_shortcodes( $tab['tab_content'], [] );

                                    $course_grids[] = $tags;
                                }
                                break;
                        }
                    }
                }
            }
        }

        return $course_grids;
    }

    public function elementor_assets_loaded()
    {
        global $learndash_course_grid_post_elementor_enabled;

        if ( $learndash_course_grid_post_elementor_enabled ) {
            wp_enqueue_script( 'learndash-course-grid-elementor-compatibility', LEARNDASH_COURSE_GRID_PLUGIN_URL . '/assets/js/elementor.js', [], LEARNDASH_COURSE_GRID_VERSION, true );
        }
    }

    public function parse_elementor_data( $data )
    {
        $elements = Utilities::associative_list_pluck( $data, 'elements' );
    }
}
