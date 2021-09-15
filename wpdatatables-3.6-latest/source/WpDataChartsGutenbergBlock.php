<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class WpDataChartsGutenbergBlock
 *
 */
class WpDataChartsGutenbergBlock extends GutenbergBlock
{
    /**
     * Register wpDataCharts block for Gutenberg
     */
    public static function registerBlockType()
    {

        wp_enqueue_script(
            'wpdatacharts-gutenberg-block',
            WDT_ROOT_URL . 'assets/js/gutenberg/wpdatacharts-gutenberg-block.js',
            array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor')
        );

        wp_localize_script(
            'wpdatacharts-gutenberg-block',
            'wpdatacharts',
            array(
                'title' => 'wpDataCharts',
                'description' => __('Choose the chart that you’ve just created in the dropdown below, and the shortcode will be inserted automatically.','wpdatatables'),
                'data' => self::wdtGetAllChartsForGutenberg()
            )
        );

        register_block_type(
            'wpdatatables/wpdatacharts-gutenberg-block',
            array('editor_script' => 'wpdatacharts-gutenberg-block')
        );
    }

    public static function wdtGetAllChartsForGutenberg() {

        global $wpdb;
        $returnCharts = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatacharts ORDER BY id";

        $allCharts = $wpdb->get_results($query, ARRAY_A);

        foreach ($allCharts as $chart) {
            $returnCharts[] = [
                'name' => $chart['title'],
                'id' => $chart['id'],
            ];

        }

        return $returnCharts;
    }


}