<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the plain HTML table
 * wpDataTables Module
 * @author cjbug@ya.ru
 * @since 10.10.2012
 *
 **/

/** @var $this WPDataTable */
/** @var string $advancedFilterPosition */
?>
<?php
if ($this->getFilteringForm()) {
    include WDT_TEMPLATE_PATH . 'frontend/filter_form.inc.php';
}
?>
<?php do_action('wpdatatables_before_table', $this->getWpId()); ?>
<?php wp_nonce_field('wdtFrontendEditTableNonce' . $this->getWpId(), 'wdtNonceFrontendEdit_' . $this->getWpId()); ?>
    <input type="hidden" id="<?php echo $this->getId() ?>_desc" value='<?php echo $this->getJsonDescription(); ?>'/>

    <table id="<?php echo $this->getId() ?>"
           class="<?php if ($this->isScrollable()) { ?>scroll<?php } ?> <?php if ($this->isResponsive()) { ?>responsive<?php } ?> display nowrap <?php echo $this->getCssClassesArr() ?> <?php echo apply_filters('wdt_add_class_to_table_html_element', $this->getCSSClasses(), $this->getWpId()) ?> wpDataTable wpDataTableID-<?php echo $this->getWpId() ?>"
           style="<?php echo $this->getCSSStyle() ?>"
           data-described-by='<?php echo $this->getId() ?>_desc'
           data-wpdatatable_id="<?php echo $this->getWpId(); ?>
">

        <!-- Table header -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_head.inc.php'; ?>
        <!-- /Table header -->

        <!-- Table body -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_body.inc.php'; ?>
        <!-- /Table body -->

        <!-- Table footer -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_footer.inc.php'; ?>
        <!-- /Table footer -->

    </table>
<?php do_action('wpdatatables_after_table', $this->getWpId()); ?>

<?php if ($this->isEditable()) {
    include WDT_TEMPLATE_PATH . 'frontend/edit_dialog.inc.php';
}