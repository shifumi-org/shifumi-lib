<?php

/**
 * Optional Visual Composer integration
 */
if (function_exists('vc_map')) {

    /**
     * Insert wpDataTable button
     */
    vc_map(
        array(
            'name' => 'wpDataTable',
            'base' => 'wpdatatable',
            'description' => __('Interactive Responsive Table', 'wpdatatable'),
            'category' => __('Content'),
            'icon' => plugin_dir_url( dirname(__FILE__) ) . 'wpbakery/assets/img/vc-icon.png',
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'class' => '',
                    'heading' => __('wpDataTable', 'wpdatatables'),
                    'admin_label' => true,
                    'param_name' => 'id',
                    'value' => WDTConfigController::getAllTablesAndChartsForPageBuilders('bakery', 'tables'),
                    'description' => __('Choose the wpDataTable from a dropdown', 'wpdatatables')
                ),
                array(
                    'type' => 'dropdown',
                    'class' => '',
                    'heading' => __('Table view', 'wpdatatables'),
                    'admin_label' => true,
                    'param_name' => 'table_view',
                    'value' => array(
                        __('Regular wpDataTable', 'wpdatatables') => 'regular',
                        __('Excel-like table', 'wpdatatables') => 'excel'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #1', 'wpdatatables'),
                    'param_name' => 'var1',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR1 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #2', 'wpdatatables'),
                    'param_name' => 'var2',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR2 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #3', 'wpdatatables'),
                    'param_name' => 'var3',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR3 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #4', 'wpdatatables'),
                    'param_name' => 'var4',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR4 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #5', 'wpdatatables'),
                    'param_name' => 'var5',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR5 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #6', 'wpdatatables'),
                    'param_name' => 'var6',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR6 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #7', 'wpdatatables'),
                    'param_name' => 'var7',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR7 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #8', 'wpdatatables'),
                    'param_name' => 'var8',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR8 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Variable placeholder #9', 'wpdatatables'),
                    'param_name' => 'var9',
                    'value' => '',
                    'group' => __('Variables', 'wpdatatables'),
                    'description' => __('If you used the VAR9 placeholder you can assign a value to it here', 'wpdatatables')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Export file name', 'wpdatatables'),
                    'param_name' => 'export_file_name',
                    'value' => '',
                    'group' => __('Export file', 'wpdatatables'),
                    'description' => __('If you use export buttons like CSV or Excel, you can set custom export file name here', 'wpdatatables')
                )
            )
        )
    );

    /**
     * Insert wpDataChart button
     */
    vc_map(
        array(
            'name' => 'wpDataChart',
            'base' => 'wpdatachart',
            'description' => __('Google, Chart.js, Highcharts or Apexcharts chart based on a wpDataTable', 'wpdatatable'),
            'category' => __('Content'),
            'icon' => plugin_dir_url( dirname(__FILE__) ) . 'wpbakery/assets/img/vc-charts-icon.png',
            "params" => array(
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __('wpDataChart', 'wpdatatables'),
                    "param_name" => "id",
                    'admin_label' => true,
                    "value" => WDTConfigController::getAllTablesAndChartsForPageBuilders('bakery', 'charts'),
                    "description" => __("Choose one of wpDataCharts from the list", 'wpdatatables')
                )
            )
        )
    );

}
