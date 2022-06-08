<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the filter in form
 * @author: Milos Timotic
 * @since: 22.4.2017.
 */

/** @var WPDataTable $this */
/** @var WDTColumn $dataColumn */
?>
<?php do_action('wpdatatables_before_filtering_form', $this->getWpId()); ?>
    <div class="wpDataTables wpDataTablesFilter wpDataTablesWrapper wdt-skin-<?php echo esc_attr($this->getTableSkin()); ?> <?php echo apply_filters('wdt_add_class_to_filter_in_form_element', esc_attr($this->getCSSClasses()), $this->getWpId()) ?>"
         data-wpdatatable_id="<?php echo esc_attr($this->getWpId()); ?>">
        <div id="filterBox_<?php echo esc_attr($this->getId()) ?>" class="wpDataTableFilterBox">
            <?php foreach ($this->getColumns() as $key => $dataColumn) { ?>
                <?php
                /** @var $dataColumn WDTColumn */
                if ($dataColumn->getFilterType() != 'null') {
                    ?>
                    <div class="wpDataTableFilterSection" id="<?php echo esc_attr($this->getId() . '_' . $key . '_filter') ?>_sections">
                        <label><?php echo $dataColumn->getFilterLabel() ? esc_html($dataColumn->getFilterLabel()) : esc_html($dataColumn->getTitle()) ?>:</label>
                        <div id="<?php echo esc_attr($this->getId() . '_' . $key . '_filter') ?>"></div>
                    </div>
                <?php } ?>
            <?php }
            if ($this->isClearFilters()) { ?>
                <div class="wpDataTableFilterSection" id="wdt-clear-filters-button-block">
                    <button class="button btn wdt-clear-filters-button" data-table_id = <?php echo esc_attr($this->getId()) ?>><?php esc_html_e('Clear filters', 'wpdatatables'); ?></button>
                </div>
            <?php } ?>
            <?php
            if (isset($this->showSearchFiltersButton) && isset($this->disableSearchFiltersButton)) {
                do_action('wpdatatables_filtering_form_search_button', $this->showSearchFiltersButton, $this->disableSearchFiltersButton);
            }
            ?>
        </div>
    </div>
<?php do_action('wpdatatables_after_filtering_form', $this->getWpId()); ?>