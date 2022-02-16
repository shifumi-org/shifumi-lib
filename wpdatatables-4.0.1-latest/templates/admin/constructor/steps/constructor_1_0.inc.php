<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-12 wdt-constructor-step hidden bg-white" data-step="1-0">
    <div id="wpdt-simple-setup-data">
        <div class="row wpdt-custom-center-flex">

            <div class="col-sm-6">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Table name', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>?"></i>
                </h4>
                <div class="form-group">
                    <div class="fg-line">
                        <input type="text" class="form-control input-sm" value="New wpDataTable"
                               id="wdt-constructor-simple-table-name">
                    </div>
                </div>
            </div>
        </div>
        <div class="row wpdt-custom-center-flex">

            <div class="col-sm-3">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Number of columns', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('What is the number columns that you need?', 'wpdatatables'); ?>?"></i>
                </h4>
                <div class="form-group">
                    <div class="fg-line wdt-custom-number-input">
                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus"
                                data-field="wdt-simple-table-number-of-columns">
                            <i class="wpdt-icon-minus"></i>
                        </button>
                        <input type="text" name="wdt-simple-table-number-of-columns" min="1" value="5"
                               class="form-control input-sm input-number" id="wdt-simple-table-number-of-columns">
                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus"
                                data-field="wdt-simple-table-number-of-columns">
                            <i class="wpdt-icon-plus-full"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-sm-3">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Number of rows', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('How many columns table will it have? You can also modify it below with + and x buttons', 'wpdatatables'); ?>."></i>
                </h4>
                <div class="form-group">
                    <div class="fg-line wdt-custom-number-input">
                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus"
                                data-field="wdt-simple-table-number-of-rows">
                            <i class="wpdt-icon-minus"></i>
                        </button>
                        <input type="text" name="wdt-simple-table-number-of-rows" min="1" value="5"
                               class="form-control input-sm input-number" id="wdt-simple-table-number-of-rows">
                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus"
                                data-field="wdt-simple-table-number-of-rows">
                            <i class="wpdt-icon-plus-full"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <div class="row m-t-15 m-b-5 p-l-15 p-r-15">
            <div class="wpdt-custom-center-flex">
                <button class=" btn btn-primary" id="wdt-simple-table-constructor">
                    <?php _e('Generate table', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>

</div>
<div class="clear"></div>