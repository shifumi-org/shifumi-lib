<?php

defined('ABSPATH') or die('Access denied.');

class WPDataTables_Elementor_Widgets
{

    protected static $instance = null;

    public static function get_instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    protected function __construct()
    {
        $this->includes();
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'widget_styles']);
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);

    }

    public function includes()
    {
        require_once(WDT_ROOT_PATH . 'widgets/class.wpDataTablesElementorWidget.php');
        require_once(WDT_ROOT_PATH . 'widgets/class.wpDataChartsElementorWidget.php');
    }

    public function register_widgets()
    {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor\WPDataTables_Elementor_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor\WPDataCharts_Elementor_Widget());
    }

    public function widget_styles()
    {
        wp_register_style('wpdt-elementor-widget-font', WDT_CSS_PATH . 'elementor/style.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-elementor-widget-font');
    }

    public function register_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'wpdatatables-elementor',
            [
                'title' => 'wpDataTables',
                'icon' => 'wpdt-table-logo',
            ], 1);
    }

}

add_action('init', 'wpdatatables_elementor_widgets_init');
function wpdatatables_elementor_widgets_init()
{
    if (defined('ELEMENTOR_VERSION')) {
        WPDataTables_Elementor_Widgets::get_instance();
    }
}





