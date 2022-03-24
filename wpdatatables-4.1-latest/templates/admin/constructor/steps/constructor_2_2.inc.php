<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step bg-white hidden" data-step="2-2">

    <div class="alert alert-info alert-dismissible" role="alert">
        <i class="wpdt-icon-info-circle-full"></i>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <span class="wdt-alert-title f-600"><?php _e('Please check which columns would you like to import and make sure that the column types were imported correctly.', 'wpdatatables'); ?></span>
    </div>

    <div class="row wdt-constructor-file-table-name-block">

        <div class="col-sm-6">
            <h4 class="c-title-color m-b-2">
                <?php _e('Table name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>?"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="New wpDataTable"
                           id="wdt-constructor-file-table-name">
                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-sm-11 ">
            <h4 class="c-title-color m-b-16">
                <span class="d-block m-b-5"><?php _e('Column names and types', 'wpdatatables'); ?></span><br>
                <small><?php _e('Drag and drop to reorder columns', 'wpdatatables'); ?>.</small>
            </h4>
        </div>
        <div class="col-sm-1">
            <button class="btn bgm-gray pull-right" id="wdt-constructor-add-column">
                <i class="wpdt-icon-plus"></i><?php _e('Add column', 'wpdatatables'); ?>
            </button>
        </div>
    </div>
    <!-- /.row -->

    <div class="row wdt-constructor-columns-container">

    </div>

    <div class="row m-b-30">
        <div class="col-sm-1 pull-right">
            <button class="btn bgm-gray pull-right" id="wdt-constructor-add-column">
                <i class="wpdt-icon-plus"></i> <?php _e('Add column', 'wpdatatables'); ?>
            </button>
        </div>
    </div>

</div>