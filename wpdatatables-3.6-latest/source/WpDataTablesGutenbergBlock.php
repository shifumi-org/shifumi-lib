<?php


defined('ABSPATH') or die('Access denied.');

/**
 * Class WpDataTablesGutenbergBlock
 *
 */
class WpDataTablesGutenbergBlock extends GutenbergBlock
{
    /**
     * Register wpDataTables block for Gutenberg
     */
    public static function registerBlockType()
    {

        wp_enqueue_script(
            'wpdatatables-gutenberg-block',
            WDT_ROOT_URL . 'assets/js/gutenberg/wpdatatables-gutenberg-block.js',
            array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor')
        );

        wp_localize_script(
            'wpdatatables-gutenberg-block',
            'wpdatatables',
            array(
                'title' => 'wpDataTables',
                'description' => __('Choose the table that you’ve just created in the dropdown below, and the shortcode will be inserted automatically. You are able to provide values for placeholders and also for Export file name.','wpdatatables'),
                'data' => self::wdtGetAllTablesForGutenberg()
            )
        );

        register_block_type(
            'wpdatatables/wpdatatables-gutenberg-block',
            array('editor_script' => 'wpdatatables-gutenberg-block')
        );

    }

    public static function wdtGetAllTablesForGutenberg() {

        global $wpdb;
        $returnTables = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatatables ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);

        foreach ($allTables as $table) {
            $returnTables[] = [
                'name' => $table['title'],
                'id' => $table['id'],
            ];

        }

        return $returnTables;
    }
}