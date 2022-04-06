<?php


class DIVI_wpDataTable extends ET_Builder_Module
{

    public $slug       = 'DIVI_wpDataTable';
    public $vb_support = 'on';

    private $_allTables;

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

    protected $module_credits = array(
        'module_uri' => '',
        'author'     => '',
        'author_uri' => '',
    );

    public function init()
    {
        $this->name = esc_html__('wpDataTable', 'wpdatatables');
        $this->setAllTables(WDTConfigController::getAllTablesAndChartsForPageBuilders('divi', 'tables'));
    }

    /**
     * Advanced Fields Config
     *
     * @return array
     */
    public function get_advanced_fields_config()
    {
        return array(
            'button' => false,
            'link_options' => false
        );
    }

    public function get_fields()
    {
        return array(
            'id' => array(
                'label' => __('Choose a wpDataTable', 'wpdatatables'),
                'type' => 'select',
                'default_on_front' => $this->getAllTables()[0],
                'options' => $this->getAllTables()
            ),
            'view' => array(
                'label' => __('Choose table view', 'wpdatatables'),
                'type' => 'select',
                'default_on_front' => __('regular', 'wpdatatables'),
                'options' => ['regular', 'excel-like']
            ),
            'var1' => array(
                'label' => __('Insert the %VAR1% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var2' => array(
                'label' => __('Insert the %VAR2% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var3' => array(
                'label' => __('Insert the %VAR3% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'export_file_name' => array(
                'label' => __( 'Set the name for the export file', 'wpdatatables' ),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'table_array_length' => array(
                'type' => 'text',
                'default_on_front' => count($this->getAllTables()),
                'show_if' => array(
                    'id' => -1
                    )
            )
        );
    }

    public function render($attrs, $content = null, $render_slug = null)
    {
        $shortcode = '[wpdatatable ';
        $tableId =  $this->props['id'];
        $view = $this->props['view'];
        $var1 = $this->props['var1'];
        $var2 = $this->props['var2'];
        $var3 = $this->props['var3'];
        $export_file_name = $this->props['export_file_name'];

        if (count($this->getAllTables()) == 1) {
            return __(WDTConfigController::wdt_create_table_notice());
        }
        if (!(int)$tableId) {
            return __(WDTConfigController::wdt_select_table_notice());
        }

        $shortcode .= 'id=' . $tableId;
        $shortcode .= $view == 'excel-like' ? ' table_view=excel' : ' table_view=regular';

        if($var1) {
            $shortcode .= ' var1 =' . $var1;
        }
        if($var2) {
            $shortcode .= ' var2 =' . $var2;
        }
        if($var3) {
            $shortcode .= ' var3 =' . $var3;
        }
        if($export_file_name) {
            $shortcode .= ' export_file_name=' . $export_file_name;
        }
        $shortcode .= ']';

        return do_shortcode($shortcode);
    }
}

new DIVI_wpDataTable;