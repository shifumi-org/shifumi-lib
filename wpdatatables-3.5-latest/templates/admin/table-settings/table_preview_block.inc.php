<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Table Preview widget
 * @author Alexander Gilmanov
 * @since 13.10.2016
 */
?>
<!-- div.column-settings -->

<?php do_action('wdt_above_table_alert'); ?>

<div class="card column-settings hidden">

    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->

    <div class="card-header wdt-admin-card-header ch-alt">
        <div class="col-sm-6 p-l-0 p-t-5">
            <h2><?php _e('Table preview and columns setup', 'wpdatatables'); ?><?php echo Connection::enabledSeparate() ? ' (' . Connection::getName($connection) . ')' : ''; ?></h2>
        </div>
        <div class="col-sm-6 p-0 wdt-edit-buttons hidden">
            <span class="pull-right wdt-view-buttons">
                <?php if (isset($_GET['table_view']) && $_GET['table_view'] == 'excel') { ?>
                    <button class="btn wdt-standard-view" title="" data-toggle="tooltip"
                            data-original-title="Swich to Standard table view">
                    <a href="<?php echo admin_url(isset($_GET['table_id']) ? 'admin.php?page=wpdatatables-constructor&source&table_id=' . (int)$_GET['table_id'] : ''); ?>">
                       <?php _e('Standard view', 'wpdatatables'); ?></a></button>
                    <button class="btn pull-right wdt-excel-view-selected" title="">
                     <?php _e('Excel Like', 'wpdatatables'); ?></button>
                <?php } else { ?>
                    <button class="btn wdt-standard-view-selected " title="">
                    <?php _e('Standard view', 'wpdatatables'); ?></button>
                    <button class="btn pull-right wdt-excel-view " title="" data-toggle="tooltip"
                            data-original-title="Swich to Excel-like table view">
                    <a href="<?php echo admin_url(isset($_GET['table_id']) ? 'admin.php?page=wpdatatables-constructor&source&table_id=' . (int)$_GET['table_id'] . '&table_view=excel' : ''); ?>">
                        <?php _e('Excel Like', 'wpdatatables'); ?></a></button>
                <?php } ?>
            </span>
        </div>
        <div class="clearfix"></div>

    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding">

        <div class="wdt-table-action-buttons">
            <span class="pull-right">
                 <button class="btn pull-right"
                         title="<?php _e('Complete column list', 'wpdatatables'); ?>" data-toggle="tooltip"
                         id="wdt-open-columns-list">
               <i class="wpdt-icon-line-columns"></i>
                     <?php _e('Column List', 'wpdatatables'); ?>
            </button>
            </span>
            <span class="pull-left">
              <?php if (isset($tableData) && $tableData->table->table_type === 'manual') { ?>
                  <button class="btn pull-right wdt-remove-column">
                      <i class="wpdt-icon-minus" style="color: #F03E3E;"></i>
                      <?php _e('Remove column', 'wpdatatables'); ?>
                  </button>
              <?php } ?>
                <button class="btn pull-right wdt-add-formula-column">
                <i class="wpdt-icon-function-reg"></i>
                    <?php _e('Add a Formula Column', 'wpdatatables'); ?>
                </button>
              <?php if (isset($tableData) && $tableData->table->table_type === 'manual') { ?>
                  <button class="btn pull-right wdt-add-column">
                      <i class="wpdt-icon-plus-full"></i>
                      <?php _e('Add column', 'wpdatatables'); ?>
                  </button>
              <?php } ?>
           </span>

        </div>
        <div class="clear"></div>
        <div id="wpdt-view-container">
            <div class="row wpDataTableContainer wpDataTables wpDataTablesWrapper" id="wpdatatable-preview-container">
                <?php if (isset($tableData)) {
                    echo $tableData->wdtHtml;
                } ?>
            </div>
            <!-- /.wpDataTableContainer -->
        </div>

    </div>
    <!-- /.card-body -->
    <div class="row wpdt-actions-bottom">

        <div class="col">
            <button class="btn btn-default btn-icon-text wdt-documentation"
                    data-doc-page="table_preview">
                <i class="wpdt-icon-file-thin"></i><?php _e('View Documentation', 'wpdatatables'); ?>
            </button>

            <div class="pull-right">
                <button class="btn btn-danger btn-icon-text wdt-backend-close">
                    <?php _e('Cancel', 'wpdatatables'); ?>
                </button>
                <button class="btn btn-primary btn-icon-text wdt-apply">
                    <i class="wpdt-icon-save"></i> <?php _e('Save Changes', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
        <!-- /.col-md-12 -->
    </div>
    <!-- /.row -->
</div>
<!-- /.card /.column-settings -->
