<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the table footer HTML
 * @author: Milos Timotic
 * @since: 22.4.17.
 */

/** @var string $advancedFilterPosition */
/** @var WDTColumn $dataColumn */
?>
<?php
$sumColumns = $this->getSumColumns();
$avgColumns = $this->getAvgColumns();
$minColumns = $this->getMinColumns();
$maxColumns = $this->getMaxColumns();
$sumFooterColumns = $this->getSumFooterColumns();
$avgFooterColumns = $this->getAvgFooterColumns();
$minFooterColumns = $this->getMinFooterColumns();
$maxFooterColumns = $this->getMaxFooterColumns();

if (($this->advancedFilterEnabled() && (get_option('wdtRenderFilter') == 'footer'))
    || !empty($sumColumns) || !empty($avgColumns)
    || !empty($minColumns) || !empty($maxColumns)
) {
    ?>
    <tfoot>
    <?php do_action('wpdatatables_before_footer', $this->getWpId()); ?>
    <?php if ($this->advancedFilterEnabled() && (get_option('wdtRenderFilter') == 'footer')) { ?>
        <tr <?php if ($this->getFilteringForm()) { ?>style="display: none"<?php } ?>>
            <?php foreach ($this->getColumns() as $dataColumn) { ?>
                <td
                class="wdtheader <?php if ($dataColumn->getSorting()) { ?>sort<?php } ?> <?php echo esc_attr($dataColumn->getCSSClasses()); ?>"
                style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>"><?php if ($dataColumn->getFilterType() != 'null') {
                    echo esc_html($dataColumn->getTitle());
                } ?></td><?php } ?>
        </tr>
    <?php } ?>
    <?php if (!empty($sumFooterColumns)) { ?>
        <tr class="wdt-sum-row">
            <?php foreach ($this->getColumnsByHeaders() as $dataColumnHeader => $dataColumn) { ?>
                <td class="wdt-sum-cell" data-column_header="<?php echo esc_attr($dataColumnHeader); ?>"
                    style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>">
                    <?php if (in_array($dataColumnHeader, $this->getSumFooterColumns())) {
                        echo esc_html(((isset($wdtSumFunctionsLabel) && $wdtSumFunctionsLabel != '') ? $wdtSumFunctionsLabel : '&#8721; = ') . ' ' . $this->returnCellValue($this->getColumnsAggregateFuncsResult($dataColumnHeader, 'sum'), $dataColumnHeader));
                    } ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if (!empty($avgFooterColumns)) { ?>
        <tr class="wdt-avg-row">
            <?php foreach ($this->getColumnsByHeaders() as $dataColumnHeader => $dataColumn) { ?>
                <td class="wdt-avg-cell" data-column_header="<?php echo esc_attr($dataColumnHeader); ?>"
                    style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>">
                    <?php if (in_array($dataColumnHeader, $this->getAvgFooterColumns())) {
                        require_once(WDT_ROOT_PATH . 'source/class.float.wpdatacolumn.php');
                        $floatCol = new FloatWDTColumn();
                        $floatCol->setParentTable($this);
                        $floatCol->setDecimalPlaces($dataColumn->getDecimalPlaces());
                        echo esc_html(((isset($wdtAvgFunctionsLabel) && $wdtAvgFunctionsLabel != '') ? $wdtAvgFunctionsLabel : 'Avg = ') . ' ' . $floatCol->prepareCellOutput($this->getColumnsAggregateFuncsResult($dataColumnHeader, 'avg')));
                    } ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if (!empty($minFooterColumns)) { ?>
        <tr class="wdt-min-row">
            <?php foreach ($this->getColumnsByHeaders() as $dataColumnHeader => $dataColumn) { ?>
                <td class="wdt-min-cell" data-column_header="<?php echo esc_attr($dataColumnHeader); ?>"
                    style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>">
                    <?php if (in_array($dataColumnHeader, $this->getMinFooterColumns())) {
                        echo esc_html(((isset($wdtMinFunctionsLabel) && $wdtMinFunctionsLabel != '') ? $wdtMinFunctionsLabel : 'Min = ') . ' ' . $this->returnCellValue($this->getColumnsAggregateFuncsResult($dataColumnHeader, 'min'), $dataColumnHeader));
                    } ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if (!empty($maxFooterColumns)) { ?>
        <tr class="wdt-max-row">
            <?php foreach ($this->getColumnsByHeaders() as $dataColumnHeader => $dataColumn) { ?>
                <td class="wdt-max-cell" data-column_header="<?php echo esc_attr($dataColumnHeader); ?>"
                    style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>">
                    <?php if (in_array($dataColumnHeader, $this->getMaxFooterColumns())) {
                        echo esc_html(((isset($wdtMaxFunctionsLabel) && $wdtMaxFunctionsLabel != '') ? $wdtMaxFunctionsLabel : 'Max = ') . ' ' . $this->returnCellValue($this->getColumnsAggregateFuncsResult($dataColumnHeader, 'max'), $dataColumnHeader));
                    } ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php do_action('wpdatatables_after_footer', $this->getWpId()); ?>
    </tfoot>
<?php }