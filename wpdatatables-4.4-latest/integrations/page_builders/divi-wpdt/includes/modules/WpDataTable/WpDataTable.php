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
            'var4' => array(
                'label' => __('Insert the %VAR4% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var5' => array(
                'label' => __('Insert the %VAR5% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var6' => array(
                'label' => __('Insert the %VAR6% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var7' => array(
                'label' => __('Insert the %VAR7% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var8' => array(
                'label' => __('Insert the %VAR8% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var9' => array(
                'label' => __('Insert the %VAR9% placeholder', 'wpdatatables'),
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
        $var4 = $this->props['var4'];
        $var5 = $this->props['var5'];
        $var6 = $this->props['var6'];
        $var7 = $this->props['var7'];
        $var8 = $this->props['var8'];
        $var9 = $this->props['var9'];
        $export_file_name = $this->props['export_file_name'];

        //Fix for Divi not recognizing table ID as an int when only one table is created
        if (!is_numeric($tableId)) {
            $tableId = substr($tableId, strrpos($tableId, "(id:") + 4);
            $tableId = substr($tableId, 0,strrpos($tableId, ')') );
            $tableId = (int)$tableId;
        }

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
        if($var4) {
            $shortcode .= ' var4 =' . $var4;
        }
        if($var5) {
            $shortcode .= ' var5 =' . $var5;
        }
        if($var6) {
            $shortcode .= ' var6 =' . $var6;
        }
        if($var7) {
            $shortcode .= ' var7 =' . $var7;
        }
        if($var8) {
            $shortcode .= ' var8 =' . $var8;
        }
        if($var9) {
            $shortcode .= ' var9 =' . $var9;
        }
        if($export_file_name) {
            $shortcode .= ' export_file_name=' . $export_file_name;
        }
        $shortcode .= ']';

        return do_shortcode($shortcode);
    }
}

new DIVI_wpDataTable;