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
}
