<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * User: Miljko Milosevic
 * Date: 1/20/17
 * Time: 1:08 PM
 */
?>

<div role="tabpanel" class="tab-pane active" id="main-plugin-settings">
    <div class="row">
        <div class="col-sm-4 interface-language">
            <h4 class="c-title-color m-b-2">
                <?php _e('Interface language', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the language which will be used in tables interface.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wdt-interface-language">
                            <option value=""><?php _e('English (default)', 'wpdatatables'); ?></option>
                            <?php foreach (WDTSettingsController::getInterfaceLanguages() as $language) { ?>
                                <option value="<?php echo $language['file'] ?>">
                                    <?php echo $language['name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 date-format">
            <h4 class="c-title-color m-b-2">
                <?php _e('Date format', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the date format to use in date column type.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" name="wdt-date-format" id="wdt-date-format">
                            <option value="d/m/Y"> 15/07/2005 (d/m/Y)</option>
                            <option value="m/d/Y"> 07/15/2005 (m/d/Y)</option>
                            <option value="Y/m/d"> 2005/15/07 (Y/m/d)</option>
                            <option value="d.m.Y"> 15.07.2005 (d.m.Y)</option>
                            <option value="m.d.Y"> 07.15.2005 (m.d.Y)</option>
                            <option value="Y.m.d"> 2005.07.15 (Y.m.d)</option>
                            <option value="d-m-Y"> 15-07-2005 (d-m-Y)</option>
                            <option value="m-d-Y"> 07-15-2005 (m-d-Y)</option>
                            <option value="Y-m-d"> 2005-07-15 (Y-m-d)</option>
                            <option value="d.m.y"> 15.07.05 (d.m.y)</option>
                            <option value="m.d.y"> 07.15.05 (m.d.y)</option>
                            <option value="d.m">15.07 (d.m)</option>
                            <option value="d-m-y"> 15-07-05 (d-m-y)</option>
                            <option value="m-d-y"> 07-15-05 (m-d-y)</option>
                            <option value="d M Y"> 15 July 2005 (d Mon Y)</option>
                            <option value="m/Y"> 07/2005 (m/Y)</option>
                            <option value="M Y"> Jul 2005 (Mon Y)</option>
                            <option value="F Y"> July 2005 (F Y)</option>
                            <option value="F j, Y"> July 15, 2005 (F j, Y)</option>
                            <option value="j. F Y."> 15. July 2005. (j. F Y.)</option>
                            <option value="Y">2005 (Y)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 time-format">
            <h4 class="c-title-color m-b-2">
                <?php _e('Time format', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the time format to use in datetime and time column type.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" name="wdt-time-format" id="wdt-time-format">
                            <option value="h:i A">1:25 PM (12h)</option>
                            <option value="H:i">13:25 (24h)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 wdt-parse-shortcodes">
            <h4 class="c-title-color m-b-2">
                <?php _e('Parse shortcodes', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('If the option is enabled, you can use shortcodes of other plugins for generating content in table cells', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-parse-shortcodes" id="wdt-parse-shortcodes"/>
                <label for="wdt-parse-shortcodes" class="ts-label"><?php _e('Parse shortcodes in strings', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 base-skin">
            <h4 class="c-title-color m-b-2">
                <?php _e('Base skin', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Choose the base skin for the plugin.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" name="wdt-base-skin" id="wdt-base-skin">
                            <option value="material"><?php _e('Material', 'wpdatatables'); ?></option>
                            <option value="light"><?php _e('Light', 'wpdatatables'); ?></option>
                            <option value="graphite"><?php _e('Graphite', 'wpdatatables'); ?></option>
                            <option value="aqua"><?php _e('Aqua', 'wpdatatables'); ?></option>
                            <option value="purple"><?php _e('Purple', 'wpdatatables'); ?></option>
                            <option value="dark"><?php _e('Dark', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 number-format">
            <h4 class="c-title-color m-b-2">
                <?php _e('Number format', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the number format (thousands and decimals separator)', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wdt-number-format">
                            <option value="1">15.000,00</option>
                            <option value="2">15,000.00</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 filter-position">
            <h4 class="c-title-color m-b-2">
                <?php _e('Render advanced filter', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Choose where you would like to render the advanced filter for tables where enabled.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wp-render-filter">
                            <option value="header"><?php _e('In the header', 'wpdatatables'); ?></option>
                            <option value="footer"><?php _e('In the footer', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 decimal-places">
            <h4 class="c-title-color m-b-2">
                <?php _e('Decimal places', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Define the amount of decimal places for the float numbers.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-decimal-places">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="text" name="wdt-decimal-places" min="1" value="" class="form-control input-sm input-number" id="wdt-decimal-places">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-decimal-places">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 csv-delimiter">
            <h4 class="c-title-color m-b-2">
                <?php _e('CSV delimiter', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the CSV delimiter', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wdt-csv-delimiter">
                            <option value=""></option>
                            <option value=",">,</option>
                            <option value=":">:</option>
                            <option value=";">;</option>
                            <option value="|">|</option>
                            <option value="\t">TAB</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 sorting-browse-tables">
            <h4 class="c-title-color m-b-2">
                <?php _e('Tables sorting direction in admin page', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Here you can set sorting direction by id for browse tables and charts. By default is ascending order.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wdt-sorting-order-browse-tables">
                            <option value="ASC"> <?php _e('Ascending', 'wpdatatables'); ?></option>
                            <option value="DESC"> <?php _e('Descending', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 tablet-width">
            <h4 class="c-title-color m-b-2">
                <?php _e('Tablet width', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Here you can specify width of the screen (in pixels) that will be treated as a tablet. You can set it wider if you want responsive effect on desktops.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-tablet-width">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="text" name="wdt-tablet-width" placeholder="Set tablet width in px" min="0" value="" class="form-control input-sm input-number" id="wdt-tablet-width">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-tablet-width">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 mobile-width">
            <h4 class="c-title-color m-b-2">
                <?php _e('Mobile width', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Here you can specify width (in pixels) will be treated as a mobile..', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-mobile-width">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="text" name="wdt-mobile-width" placeholder="Set mobile width in px" min="0" value="" class="form-control input-sm input-number" id="wdt-mobile-width">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-mobile-width">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 tables-on-browse">
            <h4 class="c-title-color m-b-2">
                <?php _e('Tables per admin page', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('How many tables to show in the browse page.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <select class="selectpicker" id="wdt-tables-per-page">
                            <?php for ($i = 10; $i <= 50; $i += 10) { ?>
                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 align-numbers">
            <h4 class="c-title-color m-b-2">
                <?php _e('Align numbers', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('How Integer and Float column types will be aligned in the cell', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-numbers-align" id="wdt-numbers-align" checked="checked"/>
                <label for="wdt-numbers-align" class="ts-label"><?php _e('Align numbers to the right','wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <!-- SUM and AVG label settings -->
    <div class="row">

        <div class="col-sm-4 wdt-sum-function-label-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Sum functions label', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a label that will be used for Sum functions. If you leave it blank default label will be Σ =', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-sum-function-label" id="wdt-sum-function-label"
                                   class="form-control input-sm" placeholder="Enter the default SUM functions label"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 wdt-avg-function-label-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Average functions label', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a label that will be used for Average functions. If you leave it blank default label will be Avg =', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-avg-function-label" id="wdt-avg-function-label"
                                   class="form-control input-sm" placeholder="Enter the default AVG functions label"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->
    <!-- MIN and MAX label settings -->
    <div class="row">

        <div class="col-sm-4 wdt-min-function-label-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Minimum functions label', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a label that will be used for Minimum functions. If you leave it blank default label will be Min =', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-min-function-label" id="wdt-min-function-label"
                                   class="form-control input-sm" placeholder="Enter the default MIN functions label"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 wdt-max-function-label-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Maximum functions label', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a label that will be used for Maximum functions. If you leave it blank default label will be Max =', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-max-function-label" id="wdt-max-function-label"
                                   class="form-control input-sm" placeholder="Enter the default MAX functions label"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->
    <!-- Include bootstrap on front and back settings -->
    <div class="row">
        <div class="col-sm-4 wdt-include-bootstrap-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Include full bootstrap front-end', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('It is recommended to uncheck this option if bootstrap.js is already included in one of the theme files. Unchecked option means that there is still bootstrap.js included just in noconflict mode which should prevent errors.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-include-bootstrap" id="wdt-include-bootstrap"/>
                <label for="wdt-include-bootstrap" class="ts-label"><?php _e('Include full bootstrap.js on the front-end', 'wpdatatables'); ?></label>
            </div>
        </div>
        <div class="col-sm-4 wdt-include-bootstrap-back-end-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Include full bootstrap back-end', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('It is recommended to uncheck this option if bootstrap.js is already included in one of the theme files. Unchecked option means that there is still bootstrap.js included just in noconflict mode which should prevent errors.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-include-bootstrap-back-end" id="wdt-include-bootstrap-back-end"/>
                <label for="wdt-include-bootstrap-back-end" class="ts-label"><?php _e('Include full bootstrap.js on the back-end', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <!-- /.row -->
    <!-- Prevent deleting tables -->
    <div class="row">
        <div class="col-sm-4 wdt-prevent-deleting-tables-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Prevent deleting tables in database', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('It is recommended to check this option if you want to keep your tables in database after deleting wpDataTables plugin from the plugins page. If you uncheck this option, all the tables will be permanently deleted after deleting the plugin.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-prevent-deleting-tables" id="wdt-prevent-deleting-tables"/>
                <label for="wdt-prevent-deleting-tables" class="ts-label"><?php _e('Prevent deleting tables in database after deleting plugin from Plugins page', 'wpdatatables'); ?></label>
            </div>
        </div>
        <div class="col-sm-4 wdt-getting-started-page-status-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Remove Getting Started page', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Check this option if you want to remove Getting Started page from admin menu.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-getting-started-page-status" id="wdt-getting-started-page-status"/>
                <label for="wdt-getting-started-page-status" class="ts-label"><?php _e('Remove "Getting Started" page from admin menu.', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-sm-4 wdt-lite-vs-premium-page-status-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Remove Lite vs Premium page', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Check this option if you want to remove Lite vs Premium page from admin menu.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-lite-vs-premium-page-status" id="wdt-lite-vs-premium-page-status"/>
                <label for="wdt-lite-vs-premium-page-status" class="ts-label"><?php _e('Remove "Lite vs Premium" page from admin menu.', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
</div>
