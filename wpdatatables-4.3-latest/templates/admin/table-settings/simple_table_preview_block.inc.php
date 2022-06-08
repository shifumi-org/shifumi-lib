<?php defined('ABSPATH') or die('Access denied.');
$showAlertMessage = get_option('wdtSimpleTableAlert');if ($showAlertMessage){
    ?>
    <div class="alert alert-warning alert-dismissible wdt-simple-table-alert" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <p><?php esc_html_e('When working with this table type please note that at the moment:', 'wpdatatables'); ?></p>
        <ul style="list-style-type: disc;padding-inline-start: 40px;margin-top: 5px;">
            <li> <?php esc_html_e('There is no data functionality like front-end editing, sorting, filtering, search, export or pagination in this table type.', 'wpdatatables'); ?></li>
            <li> <?php esc_html_e('Creating charts from this table type is not yet possible.', 'wpdatatables'); ?></li>
            <li> <?php esc_html_e('You can’t use foreign keys, placeholders or conditional formatting, but each cell can be formatted separately.', 'wpdatatables'); ?></li>
            <li> <?php esc_html_e('Add-ons can’t be used with Simple tables.', 'wpdatatables'); ?></li>
            <li> <?php esc_html_e('If you need those features, please consider creating tables with any other options from table wizard.', 'wpdatatables'); ?>
            <li> <?php esc_html_e('To remove this notice, please click on X in top right corner.', 'wpdatatables'); ?>
            </li>
        </ul>
        <?php esc_html_e('Depends of users requests, we will add it in our road map and implement it based on priority. Thank you for understanding.', 'wpdatatables'); ?>
    </div>
<?php } ?>

<div class="card column-settings ">

    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->

    <div class="card-header wdt-admin-card-header ch-alt">
        <div class="col-sm-8 p-l-0 p-t-5">
            <h2><?php esc_html_e('Table preview', 'wpdatatables'); ?></h2>
        </div>

        <div class="clear"></div>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding">
        <div id="wpdt-views">
            <ul class="nav nav-pills">
                <li class="nav-item active" data-view="desktop">
                    <a class="nav-link" ><?php esc_html_e('Desktop', 'wpdatatables'); ?></a>
                </li>
                <li class="nav-item" data-view="tablet">
                    <a class="nav-link"><?php esc_html_e('Tablet', 'wpdatatables'); ?></a>
                </li>
                <li class="nav-item" data-view="mobile">
                    <a class="nav-link"><?php esc_html_e('Mobile', 'wpdatatables'); ?></a>
                </li>
            </ul>
        </div>
        <div id="wpdt-view-container">
            <?php if(isset($tableData)) {?>
            <div class="wpdt-c wpDataTablesSimpleWrapper-<?php echo $tableData->tableID;?>">
                <?php echo $tableData->wdtHtml; ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card /.column-settings -->
