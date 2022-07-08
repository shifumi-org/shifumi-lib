<?php defined('ABSPATH') or die('Access denied.'); ?>

<div id="wdt-filter-widget" class="wpDataTables wpdt-c <?php do_action('wdt_add_class_to_filter_widget') ?>">
    <h2><?php echo $title ?></h2>
    <button class="button btn wdt-clear-filters-widget-button"
            style="margin-bottom: 10px;"><?php esc_html_e(' Clear all filters', 'wpdatatables'); ?></button>
</div>