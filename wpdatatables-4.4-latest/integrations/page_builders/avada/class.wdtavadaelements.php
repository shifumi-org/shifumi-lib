<?php

defined('ABSPATH') or die('Access denied.');

class WPDataTables_Fusion_Elements
{

    public function __construct()
    {
        $this->add_wpdatatables_fusion_element();
        $this->add_wpdatacharts_fusion_element();
        add_action( 'fusion_builder_before_init', [$this,'add_wpdatatables_fusion_element'] );
        add_action( 'fusion_builder_before_init', [$this,'add_wpdatacharts_fusion_element'] );
        if (fusion_is_builder_frame()) {
            add_action('wp_enqueue_scripts', [$this,'elements_frontend_css'], 999);
        }
    }

    /**
     * Include CSS for wpDataTables elements
     */
    function elements_frontend_css() {
        wp_enqueue_style(
            'wpdatatable_avada_frontend_css',
            WDT_INTEGRATIONS_URL . 'page_builders/avada/assets/css/style.css'
        );
    }

    /**
     * Add wpDataTables Fusion element
     */
    public function add_wpdatatables_fusion_element()
    {
        fusion_builder_map(
            array(
                'name'              => esc_attr__( 'wpDataTable', 'wpdatatables' ),
                'shortcode'         => 'wpdatatable',
                'icon'              => 'wpdatatable-fusion-icon',
                'admin_enqueue_css' => WDT_INTEGRATIONS_URL . 'page_builders/avada/assets/css/style.css',
                'preview'           => WDT_ROOT_PATH . 'integrations/page_builders/avada/includes/wpdatatable_preview.inc.php',
                'preview_id'        => 'fusion_builder_block_wpdatatable_preview_template',
                'params'            => array(
                    array(
                        'type'        => 'select',
                        'heading'     => __('Choose a wpDataTable:', 'wpdatatables'),
                        'description' => __('Select the wpDataTable ID to display on the page.', 'wpdatatables'),
                        'param_name'  => 'id',
                        'value'       => WDTConfigController::getAllTablesAndChartsForPageBuilders('avada', 'tables')
                    ),
                    array(
                        'type'        => 'select',
                        'heading'     => __('Table view:', 'wpdatatables'),
                        'description' => __('Choose table view.', 'wpdatatables'),
                        'param_name'  => 'table_view',
                        'value'       => array(
                            'regular' => 'Regular',
                            'excel'   => 'Excel-like'
                        )
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR1%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR1% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var1',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR2%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR2% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var2',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR3%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR3% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var3',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR4%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR4% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var4',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR5%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR5% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var5',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR6%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR6% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var6',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR7%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR7% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var7',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR8%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR8% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var8',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Set placeholder %VAR9%:', 'wpdatatables'),
                        'description' => __('If you use the %VAR9% placeholder, you can assign a value here.', 'wpdatatables'),
                        'param_name'  => 'var9',
                        'value'       => '',
                        'group'       => esc_attr__('Placeholders', 'wpdatatables'),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => __('Export file name', 'wpdatatables'),
                        'description' => __('Set the name for the export file.', 'wpdatatables'),
                        'param_name'  => 'export_file_name',
                        'value'       => '',
                        'group'       => esc_attr__('File', 'wpdatatables'),
                    ),
                )
            )
        );
    }

    /**
     * ADD wpDataCharts Fusion element
     */
    public function add_wpdatacharts_fusion_element()
    {
        fusion_builder_map(
            array(
                'name'              => esc_attr__( 'wpDataChart', 'wpdatatables' ),
                'shortcode'         => 'wpdatachart',
                'icon'              => 'wpdatachart-fusion-icon',
                'allow_generator'   => true,
                'inline_editor'     => true,
                'admin_enqueue_css' => WDT_INTEGRATIONS_URL . 'page_builders/avada/assets/css/style.css',
                'preview'           => WDT_ROOT_PATH . 'integrations/page_builders/avada/includes/wpdatachart_preview.inc.php',
                'preview_id'        => 'fusion_builder_block_wpdatachart_preview_template',
                'params'            => array(
                    array(
                        'type'        => 'select',
                        'heading'     => __('Choose a wpDataChart:', 'wpdatatables'),
                        'description' => __('Select the wpDataChart ID to display on the page.', 'wpdatatables'),
                        'param_name'  => 'id',
                        'value'       => WDTConfigController::getAllTablesAndChartsForPageBuilders('avada', 'charts')
                    ),
                )
            )
        );
    }

    /**
     * Helper func that render content for Avada Live builder
     */
    public static function get_content_for_avada_live_builder($atts, $type) {
        $elementImage = 'vc-icon.png';
        $elementName = 'wpDataTable';
        $elementMessage = __('Please select wpDataTable ID.', 'wpdatatables');
        if ($type == 'chart'){
            $elementImage = 'vc-charts-icon.png';
            $elementName = 'wpDataChart';
            $elementMessage = __('Please select wpDataChart ID.', 'wpdatatables');
        }

        if ($atts['id'] != ''){
            $shortcode = '';
            $ID = (int)$atts['id'];
            if ($type == 'table'){
                $tableData = WDTConfigController::loadTableFromDB($ID);
                $title = __('Table: ', 'wpdatatables') . $tableData->title . ' (ID:' . $ID . ')';
                $shortcode = 'wpdatatable id=' . $ID;
                if ($atts['table_view'] != ''){
                    $shortcode .= $atts['table_view'] == 'regular' ? ' table_view=regular ' : ' table_view=excel ';
                }
                if ($atts['var1'] != '') $shortcode .= ' var1=' . $atts['var1'];
                if ($atts['var2'] != '') $shortcode .= ' var2=' . $atts['var2'];
                if ($atts['var3'] != '') $shortcode .= ' var3=' . $atts['var3'];
                if ($atts['var4'] != '') $shortcode .= ' var4=' . $atts['var4'];
                if ($atts['var5'] != '') $shortcode .= ' var5=' . $atts['var5'];
                if ($atts['var6'] != '') $shortcode .= ' var6=' . $atts['var6'];
                if ($atts['var7'] != '') $shortcode .= ' var7=' . $atts['var7'];
                if ($atts['var8'] != '') $shortcode .= ' var8=' . $atts['var8'];
                if ($atts['var9'] != '') $shortcode .= ' var9=' . $atts['var9'];
                if ($atts['export_file_name'] != '') $shortcode .= ' export_file_name=' . $atts['export_file_name'];
            } else if ($type == 'chart'){
                $wpDataChart = new WPDataChart();
                $wpDataChart->setId($ID);
                $wpDataChart->loadFromDB();
                $title = __('Chart: ', 'wpdatatables') . $wpDataChart->getTitle() . ' (ID:' . $ID . ')';
                $shortcode ='wpdatachart id=' . $ID;
            }
            $content = '<div class="wpdt-placeholder" style="text-align: center; display:block; margin: 20px auto;" >';
            $content .='<img alt="" src="' . esc_url(WDT_INTEGRATIONS_URL . 'page_builders/avada/assets/img/' . $elementImage) . '"
            style="background: no-repeat scroll center center; border-radius: 2px;">';
            $content .='<span style="font-weight: bold;font-size: 20px;margin-left: 5px;">' . esc_html($elementName) . '</span>';
            $content .='<p style="font-size: 16px;margin-top:10px;margin-bottom: 5px; text-align: center;">' . esc_html($title) . '</p>';
            $content .='<p style="font-size: 16px; text-align: center;"><span>&#91;</span>' . esc_html($shortcode) . '<span>&#93;</span></p></div>';
        } else {
            $content = '<div class="wpdt-placeholder" style="text-align: center; display:block; margin: 20px auto;">';
            $content .='<img alt="" src="' . esc_url(WDT_INTEGRATIONS_URL . 'page_builders/avada/assets/img/' . $elementImage) . '"
            style="background: no-repeat scroll center center; border-radius: 2px;">';
            $content .='<span style="font-weight: bold;font-size: 20px;margin-left: 5px;">' . esc_html($elementName) . '</span>';
            $content .='<p style="font-size: 16px;margin-top:10px; text-align: center;">' . esc_html($elementMessage) . '</p></div>';
        }

        return $content;
    }
}

/**
 * Create elements if Fusion builder is active
 */
function is_fusion_builder_active()
{
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if (is_plugin_active('fusion-builder/fusion-builder.php') &&
        function_exists('fusion_is_element_enabled') &&
        class_exists('Fusion_Element'))
    {
        new WPDataTables_Fusion_Elements;
    }
}
add_action('init', 'is_fusion_builder_active');