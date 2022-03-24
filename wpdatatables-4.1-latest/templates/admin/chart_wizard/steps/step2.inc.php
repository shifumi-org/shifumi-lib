<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row">
    <div class="col-sm-6 col-md-6 data-source">
        <h4 class="c-title-color m-b-2">
            <?php _e('wpDataTable Data Source', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Please pick a wpDataTable which will be used as a data source for this chart.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" name="wpdatatables-chart-source" id="wpdatatables-chart-source"
                            data-live-search="true">
                        <option value=""><?php _e('Pick a wpDataTable', 'wpdatatables'); ?></option>
                        <?php foreach (WPDataTable::getAllTablesExceptSimple() as $table) { ?>
                            <option value="<?php echo $table['id'] ?>">
                                <?php echo "{$table['title']} (id: {$table['id']}" . (Connection::enabledSeparate() ? ', connection: ' . Connection::getName($table['connection']) : '') . ')';?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
