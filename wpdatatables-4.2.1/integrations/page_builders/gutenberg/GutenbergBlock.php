<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class GutenbergBlock
 */

class GutenbergBlock
{
    /**
     * Register WP Ajax actions.
     */
    public static function init()
    {
        if (is_admin() && function_exists('register_block_type')) {
            if (substr($_SERVER['PHP_SELF'], '-8') == 'post.php' ||
                substr($_SERVER['PHP_SELF'], '-12') == 'post-new.php'
            ) {

                if (self::isGutenbergActive()) {
                    /** @var static $class */
                    $class = get_called_class();
                    add_action( 'enqueue_block_editor_assets', function () use ( $class ) {
                        $class::registerBlockType();
                    });
                }

            }
        }
    }

    /**
     * Check if Block Editor is active.
     *
     * @return bool
     */
    public static function isGutenbergActive()
    {
        // Gutenberg plugin is installed and activated.
        $gutenberg = !(false === has_filter('replace_editor', 'gutenberg_init'));

        // Block editor since 5.0.
        $block_editor = version_compare($GLOBALS['wp_version'], '5.0-beta', '>');

        if (!$gutenberg && !$block_editor) {
            return false;
        }

        if (self::isClassicEditorPluginActive()) {
            $editor_option = get_option('classic-editor-replace');
            $block_editor_active = array('no-replace', 'block');

            return in_array($editor_option, $block_editor_active, true);
        }

        // Fix for conflict with Avada - Fusion builder and gutenberg blocks
        // Fix for Gutenberg blocks when Avada's post/page types are disabled
        if ( class_exists( 'FusionBuilder' ) && !(isset( $_GET['gutenberg-editor']))){
            $postTypes = FusionBuilder::allowed_post_types();
            return count(array_intersect(['page', 'post'], $postTypes)) < 2;
        }

        // Fix for conflict with WooCommerce product page
        if ( class_exists( 'WooCommerce' ) && (isset( $_GET['post_type'])) && ($_GET['post_type']) == "product"){
            return false;
        }

        // Fix for conflict with Disable Gutenberg plugin
        if (class_exists('DisableGutenberg')) {
            return false;
        }

        return true;
    }

    /**
     * Check if Classic Editor plugin is active
     *
     * @return bool
     */
    public static function isClassicEditorPluginActive()
    {

        if (!function_exists('is_plugin_active')) {

            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (is_plugin_active('classic-editor/classic-editor.php')) {

            return true;
        }

        return false;
    }

    /**
     * Register block for gutenberg
     */
    public static function registerBlockType()
    {

    }

}