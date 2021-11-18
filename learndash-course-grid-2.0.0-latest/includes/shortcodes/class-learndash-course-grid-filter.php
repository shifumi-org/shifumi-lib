<?php
namespace LearnDash\Course_Grid\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

use WP_Query;
use LearnDash\Course_Grid\Utilities;

class LearnDash_Course_Grid_Filter
{
    public $tag;

    public function __construct()
    {
        $this->tag = 'learndash_course_grid_filter';
        $this->register();
    }

    public function register()
    {
        add_shortcode( $this->tag, [ $this, 'render' ] );
    }

    public function get_default_atts()
    {
        return apply_filters( 'learndash_course_grid_filter_default_shortcode_attributes', [
            'course_grid_id' => '',
            'search'         => true,
            'taxonomies'     => 'category, post_tag',
            'price'          => true,
            'price_min'      => 0,
            'price_max'      => 1000,
        ] );
    }

    public function render( $atts = [], $content = '' )
    {
        $atts = shortcode_atts( $this->get_default_atts(), $atts, $this->tag );

        if ( empty( $atts['course_grid_id'] ) ) {
            $output = __( 'Missing course_grid_id attribute.', 'learndash-course-grid' );
            return $output;
        }

        // Get the template file
        $template = Utilities::get_template( 'filter/layout' );
 
        // Include the template file
        ob_start();
        
        echo '<div class="learndash-course-grid-filter" data-course_grid_id="' . esc_attr( $atts['course_grid_id'] ) . '" data-taxonomies="' . esc_attr( $atts['taxonomies'] ) . '">';

        $atts['taxonomies'] = array_map( function( $tax ) {
            return trim( $tax );
        }, array_filter( explode( ',', $atts['taxonomies'] ) ) );

        $atts['price_step'] = ( $atts['price_max'] - $atts['price_min'] ) / 2 / 10;
        $atts['price_step'] = ceil( $atts['price_step'] );

        if ( $template ) {
            include $template;
        }

        echo '</div>';
        
        // Return the template HTML string
        return ob_get_clean();
    }
}
