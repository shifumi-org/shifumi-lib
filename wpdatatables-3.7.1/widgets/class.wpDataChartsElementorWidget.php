<?php

namespace Elementor;

class WPDataCharts_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'wpdatacharts';
    }

    public function get_title() {
        return 'wpDataCharts';
    }

    public function get_icon() {
        return 'wpdt-chart-logo';
    }

    public function get_categories() {
        return [ 'wpdatatables-elementor' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'wpdatacharts_section',
            [
                'label' => __( 'wpDataChart content', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-chart-id',
            [
                'label' => __( 'Select wpDataChart:', 'wpdatatables' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => self::wdt_get_all_charts(),
                'default' => self::wdt_return_first_chart(),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings_for_display();
        $chartShortcodeParams = '[wpdatachart id=' . $settings['wpdt-chart-id'] . ']';

        $chartShortcodeParams = apply_filters('wpdatatables_filter_elementor_chart_shortcode', $chartShortcodeParams);

        echo $settings['wpdt-chart-id'] != '' ?  $chartShortcodeParams : self::wdt_create_chart_notice();

    }


    protected function _content_template() {

    }

    public static function wdt_get_all_charts() {

        global $wpdb;
        $returnCharts = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatacharts ORDER BY id";

        $allCharts = $wpdb->get_results($query, ARRAY_A);

        if ($allCharts != null ) {
            foreach ($allCharts as $chart) {
                $returnCharts[$chart['id']] = $chart['title']  . ' (id: ' . $chart['id'] . ')';;
            }
        } else {
            $returnCharts = [];
        }

        return $returnCharts;

    }

    public static function wdt_return_first_chart() {

        $allCharts = self::wdt_get_all_charts();
        if ($allCharts != [] ) {
            reset($allCharts);
            return key($allCharts);
        } else {
            return '';
        }

    }

    public static function wdt_create_chart_notice() {

        return 'Please create wpDataChart first. You can check how to do that on this <a target="_blank" href="https://wpdatatables.com/documentation/wpdatacharts/creating-charts-wordpress-wpdatachart-wizard/">link</a>.';

    }

}



