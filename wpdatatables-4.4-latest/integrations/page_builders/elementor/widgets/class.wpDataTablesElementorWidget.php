<?php

namespace Elementor;

use WDTConfigController;

class WPDataTables_Elementor_Widget extends Widget_Base {

    private $_allTables;

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

    /**
     * @return mixed
     */
    public function getAllTables()
    {
        return $this->_allTables;
    }

    /**
     * @param mixed $allTables
     */
    public function setAllTables($allTables)
    {
        $this->_allTables = $allTables;
    }

    protected function register_controls() {

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
                'type' => Controls_Manager::SELECT,
                'options' => WDTConfigController::getAllTablesAndChartsForPageBuilders('elementor', 'tables'),
                'default' => 0
            ]
        );

        $this->add_control(
            'wpdt-view',
            [
                'label' => __( 'Choose table view:', 'wpdatatables' ),
                'type' => Controls_Manager::SELECT,
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
            'wpdt-var4',
            [
                'label' => __( 'Set placeholder %VAR4%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR4% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var5',
            [
                'label' => __( 'Set placeholder %VAR5%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR5% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var6',
            [
                'label' => __( 'Set placeholder %VAR6%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR6% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var7',
            [
                'label' => __( 'Set placeholder %VAR7%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR7% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var8',
            [
                'label' => __( 'Set placeholder %VAR8%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR8% placeholder', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-var9',
            [
                'label' => __( 'Set placeholder %VAR9%:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert %VAR9% placeholder', 'wpdatatables' ),
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
        self::setAllTables(WDTConfigController::getAllTablesAndChartsForPageBuilders('elementor', 'tables'));
        $settings = $this->get_settings_for_display();
        $tableShortcodeParams = '[wpdatatable id=' . $settings['wpdt-table-id'];
        $tableShortcodeParams .= $settings['wpdt-view'] == 'regular' ? ' table_view=regular' : ' table_view=excel';
        $tableShortcodeParams .= $settings['wpdt-var1'] != '' ? ' var1=' . $settings['wpdt-var1'] : '';
        $tableShortcodeParams .= $settings['wpdt-var2'] != '' ? ' var2=' . $settings['wpdt-var2'] : '';
        $tableShortcodeParams .= $settings['wpdt-var3'] != '' ? ' var3=' . $settings['wpdt-var3'] : '';
        $tableShortcodeParams .= $settings['wpdt-var4'] != '' ? ' var4=' . $settings['wpdt-var4'] : '';
        $tableShortcodeParams .= $settings['wpdt-var5'] != '' ? ' var5=' . $settings['wpdt-var5'] : '';
        $tableShortcodeParams .= $settings['wpdt-var6'] != '' ? ' var6=' . $settings['wpdt-var6'] : '';
        $tableShortcodeParams .= $settings['wpdt-var7'] != '' ? ' var7=' . $settings['wpdt-var7'] : '';
        $tableShortcodeParams .= $settings['wpdt-var8'] != '' ? ' var8=' . $settings['wpdt-var8'] : '';
        $tableShortcodeParams .= $settings['wpdt-var9'] != '' ? ' var9=' . $settings['wpdt-var9'] : '';
        $tableShortcodeParams .= $settings['wpdt-file-name'] != '' ? ' export_file_name=' . $settings['wpdt-file-name'] : '';
        $tableShortcodeParams .= ']';

        $tableShortcodeParams = apply_filters('wpdatatables_filter_elementor_table_shortcode', $tableShortcodeParams);

        if (count(self::getAllTables()) == 1) {
            $result = WDTConfigController::wdt_create_table_notice();
        } elseif (!(int)$settings['wpdt-table-id']) {
            $result = WDTConfigController::wdt_select_table_notice();
        } else {
            $result = $tableShortcodeParams;
        }
        echo __($result);

    }

}



