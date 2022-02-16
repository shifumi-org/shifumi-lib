<?php

namespace Elementor;

class WPDataTables_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'wpdatatables';
    }

    public function get_title() {
        return 'wpDataTables';
    }

    public function get_icon() {
        return 'wpdt-table-logo';
    }

    public function get_categories() {
        return [ 'wpdatatables-elementor' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'wpdatatables_section',
            [
                'label' => __( 'wpDataTable content', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-table-id',
            [
                'label' => __( 'Select wpDataTable:', 'wpdatatables' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => self::wdt_get_all_tables(),
                'default' => self::wdt_return_first_table(),
            ]
        );

        $this->add_control(
            'wpdt-view',
            [
                'label' => __( 'Choose table view:', 'wpdatatables' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'regular' => __( 'Regular wpDataTable', 'wpdatatables' ),
                    'excel-like' => __( 'Excel-like wpDataTable', 'wpdatatables' ),
                ],
                'default' => 'regular',
            ]
        );

        $this->add_control(
            'wpdt-var1',
            [
                'label' => __( 'Set placeholder %VAR1%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR1% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var2',
            [
                'label' => __( 'Set placeholder %VAR2%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR2% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var3',
            [
                'label' => __( 'Set placeholder %VAR3%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR3% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-file-name',
            [
                'label' => __( 'Set name for export file:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert name for export file', 'wpdatatables' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $tableShortcodeParams = '[wpdatatable id=' . $settings['wpdt-table-id'];
        $tableShortcodeParams .= $settings['wpdt-view'] == 'regular' ? ' table_view=regular' : ' table_view=excel';
        $tableShortcodeParams .= $settings['wpdt-var1'] != '' ? ' var1=' . $settings['wpdt-var1'] : '';
        $tableShortcodeParams .= $settings['wpdt-var2'] != '' ? ' var2=' . $settings['wpdt-var2'] : '';
        $tableShortcodeParams .= $settings['wpdt-var3'] != '' ? ' var3=' . $settings['wpdt-var3'] : '';
        $tableShortcodeParams .= $settings['wpdt-file-name'] != '' ? ' export_file_name=' . $settings['wpdt-file-name'] : '';
        $tableShortcodeParams .= ']';

        $tableShortcodeParams = apply_filters('wpdatatables_filter_elementor_table_shortcode', $tableShortcodeParams);

        echo $settings['wpdt-table-id'] != '' ?  $tableShortcodeParams  : self::wdt_create_table_notice();

    }

    protected function _content_template() {

    }

    public static function wdt_get_all_tables() {

        global $wpdb;
        $returnTables = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatatables ORDER BY id ";

        $allTables = $wpdb->get_results($query, ARRAY_A);

        if ($allTables != null ) {
            foreach ($allTables as $table) {
                $returnTables[$table['id']] = $table['title'] . ' (id: ' . $table['id'] . ')';
            }
        } else {
            $returnTables = [];
        }

        return $returnTables;
    }

    public static function wdt_return_first_table() {

        $allTables = self::wdt_get_all_tables();
        if ($allTables != [] ) {
            reset($allTables);
            return key($allTables);
        } else {
            return '';
        }

    }

    public static function wdt_create_table_notice() {

        return 'Please create wpDataTable first. You can find detail instructions in our docs on this <a target="_blank" href="https://wpdatatables.com/documentation/general/features-overview/">link</a>.';
    }


}



