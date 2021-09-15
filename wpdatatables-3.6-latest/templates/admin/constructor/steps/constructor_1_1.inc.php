<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step hidden bg-white" data-step="1-1">

    <div class="alert alert-info alert-dismissible" role="alert">
        <i class="wpdt-icon-info-circle-full"></i>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <span class="wdt-alert-title f-600"><?php _e('Please provide some initial structure metadata before the table will be created.', 'wpdatatables'); ?>
            <br></span>
        <span class="wdt-alert-subtitle"><?php _e('This constructor will help you to create a table from scratch. You will be able to edit the table content and metadata later manually at any time.', 'wpdatatables'); ?></span>
    </div>

    <div class="row wdt-constructor-table-name-and-columns">

        <div class="col-sm-6">
            <h4 class="c-title-color m-b-2">
                <?php _e('Table name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>?"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="New wpDataTable"
                           id="wdt-constructor-manual-table-name">
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="col-sm-12">
            <h4 class="c-title-color p-l-0 m-b-2 col-sm-12">
                <?php _e('Number of columns', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('How many columns table will it have? You can also modify it below with + and x buttons', 'wpdatatables'); ?>."></i>
            </h4>
            </div>
            <div class="col-sm-4">
            <div class="form-group">
                <div class="fg-line wdt-custom-number-input">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-constructor-number-of-columns">
                        <span class="wpdt-icon-minus"></span>
                    </button>
                    <input type="text" name="wdt-constructor-number-of-columns" min="1" class="form-control input-sm input-number" value="4"
                           id="wdt-constructor-number-of-columns">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-constructor-number-of-columns">
                        <span class="wpdt-icon-plus"></span>
                    </button>
                </div>
            </div>
            </div>
        </div>

    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-sm-11 ">
            <h4 class="c-title-color m-b-16">
                <span class="d-block p-b-2"><?php _e('Column names and types', 'wpdatatables'); ?></span><br>
                <small><?php _e('Drag and drop to reorder columns', 'wpdatatables'); ?>.</small>
            </h4>
        </div>
        <div class="col-sm-1">
            <button class="btn pull-right" id="wdt-constructor-add-column">
                <i class="wpdt-icon-plus"></i> <?php _e('Add column', 'wpdatatables'); ?>
            </button>
        </div>
    </div>
    <!-- /.row -->

    <div class="row wdt-constructor-columns-container">

    </div>

    <div class="row m-b-30">
        <div class="col-sm-1 pull-right">
            <button class="btn pull-right" id="wdt-constructor-add-column">
                <i class="wpdt-icon-plus"></i>  <?php _e('Add column', 'wpdatatables'); ?>
            </button>
        </div>
    </div>

</div>