<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php if ($this->getTableSettingsData() && $this->getTableSettingsData()->show_title && $this->getTableSettingsData()->title) { ?>
    <h2 class="wpdt-c"
        id="wdt-table-title-<?php echo $this->getTableID() ?>"><?php echo $this->getTableSettingsData()->title ?></h2>
<?php } ?>
<div class="wpdt-c row wpDataTableContainerSimpleTable wpDataTables wpDataTablesWrapper
<?php if ($this->getTableSettingsData()->scrollable) echo ' wdtscroll' ?>
<?php if ($this->getTableSettingsData()->verticalScroll) echo ' wdtVerticalScroll' ?>"
    <?php if (is_admin()) { ?>
        id="wpdatatable-simple-preview-container-<?php echo $this->getTableID() ?>"
    <?php } ?>
>
    <table id="wpdtSimpleTable-<?php echo $this->getTableID() ?>"
           class="wpdtSimpleTable wpDataTable"
           data-column="<?php echo $this->getColNumber() ?>"
           data-rows="<?php echo $this->getRowNumber() ?>"
           data-wpID="<?php echo $this->getTableID() ?>"
           data-responsive="<?php echo $this->getTableSettingsData()->simpleResponsive ?>"
           data-has-header="<?php echo $this->getTableSettingsData()->simpleHeader ?>">

        <?php
        $isResponsive = $this->getTableSettingsData()->simpleResponsive;
        $isStripe = $this->getTableSettingsData()->stripeTable;
        $toggleClass = $isStripe ? 'even' : '';
        $header = $this->getTableSettingsData()->simpleHeader;
        $tag = 'td';
        for ($i = 0; $i < $this->getRowNumber(); $i++) {
            if ($isStripe) {
                if ($header && $i == 0) {
                    $toggleClass = '';
                } else {
                    $toggleClass = $toggleClass == 'odd' ? 'even' : 'odd';
                }
            }
            ?>
            <?php if ($i == 0 && $header) {
                $tag = 'th';
                echo '<thead>';
            } else if (($i == 0 && !$header) || ($i == 1 && $header)) {
                $tag = 'td';
                echo '<tbody>';
            }
            ?>
        <tr class="wpdt-cell-row <?php echo $toggleClass ?>" <?php if (isset($this->getRowsData()[$i]->height)) echo 'style="height:' . $this->getRowsData()[$i]->height . 'px;"'; ?>>
            <?php for ($j = 0; $j < $this->getColNumber(); $j++) {
                $cellMetaClasses = $this->getCellClassesByIndexes($this->getRowsData(), $i, $j) ? implode(" ", $this->getCellClassesByIndexes($this->getRowsData(), $i, $j)) : "";
                $cellMetaClasses .= $this->getCellDataByIndexes($this->getRowsData(), $i, $j) == "" ? " wpdt-empty-cell" : "";
                $cellMetaClasses = apply_filters('wpdt_filter_simple_table_cell_meta', $cellMetaClasses, $i, $j, $this->getTableID());

                $hiddenCell = $this->getHiddenCellByIndexes($this->getRowsData(), $i, $j) ? 'hidden' : "";
                $colspanValue = 1;
                $rowspanValue = 1;
                $colspanAttr = "";
                $rowspanAttr = "";
                if (isset($this->getMergeCells()[$j][$i])) {
                    $colspanValue = (int)$this->getMergeCells()[$j][$i]['colspan'];
                    $rowspanValue = (int)$this->getMergeCells()[$j][$i]['rowspan'];
                    $colspanAttr = 'colspan="' . $colspanValue . '"';
                    $rowspanAttr = 'rowspan="' . $rowspanValue . '"';
                    $hiddenCell = "";
                    $cellMetaClasses .= " wpdt-merged-cell ";
                }

                $cellMetaClasses = apply_filters('wpdt_filter_simple_table_cell_meta', $cellMetaClasses, $i, $j, $this->getTableID());

                if (($hiddenCell == 'hidden' && $isResponsive) || $hiddenCell != 'hidden'){
                ?>
                <<?php echo $tag ?> class="wpdt-cell <?php echo $cellMetaClasses ?>"
                <?php echo ' ' . $colspanAttr . ' ' . $hiddenCell . ' ' . $rowspanAttr . ' ' ?>
                data-cell-id="<?php if (isset($this->getColHeaders()[$j])) echo $this->getColHeaders()[$j] . ($i + 1); ?>"
                data-col-index="<?php echo $j ?>"
                data-row-index="<?php echo $i ?>"
                style="<?php if ($i == 0) {
                    if ($this->getTableSettingsData()->simpleResponsive) {
                        if ($colspanValue > 1) {
                            echo ' width:' . (($this->getColWidths()[$j] / array_sum($this->getColWidths())) * 100) * $colspanValue . '%;';
                        } else if ($hiddenCell == "hidden") {
                            echo ' width:0%;';
                        } else {
                            echo ' width:' . (($this->getColWidths()[$j] / array_sum($this->getColWidths())) * 100) . '%;';
                        }

                    } else if ($this->getTableSettingsData()->scrollable) {
                        if ($colspanValue > 1) {
                            echo ' width:' . ($this->getColWidths()[$j]) * $colspanValue . 'px;';
                        } else if ($hiddenCell == "hidden") {
                            echo ' width:0px;';
                        } else {
                            echo ' width:' . ($this->getColWidths()[$j]) . 'px;';
                        }
                    } else {
                        if ($colspanValue > 1) {
                            echo ' width:' . (($this->getColWidths()[$j] / array_sum($this->getColWidths())) * 100) * $colspanValue . '%;';
                        } else if ($hiddenCell == "hidden") {
                            echo ' width:0%;';
                        } else {
                            echo ' width:' . (($this->getColWidths()[$j] / array_sum($this->getColWidths())) * 100) . '%;';
                        }
                    }
                } ?>
                padding:<?php echo $this->getTableSettingsData()->cellPadding; ?>px;"
                >
                <?php
                $cellData = $this->getCellDataByIndexes($this->getRowsData(), $i, $j);
                if (strpos($cellData, 'wpdt-do-shortcode') !== false) {
                    $cellData = substr($cellData, strpos($cellData, '>[') + 1, strpos($cellData, ']<') - strpos($cellData, '>['));
                    $cellData = do_shortcode($cellData);
                }
                $cellData = apply_filters('wpdt_filter_simple_table_cell_data', $cellData, $this->getTableID());
                ?>
                <?php echo $cellData ?>
                </<?php echo $tag ?>>
                <?php } ?>
            <?php } ?>
            </tr>
        <?php } ?>
        <?php if ($i == 0 && $header) {
            echo '</thead>';
        } else if (($i == 0 && !$header) || ($i == 1 && $header)) {
            echo '</tbody>';
        } ?>
    </table>
</div>