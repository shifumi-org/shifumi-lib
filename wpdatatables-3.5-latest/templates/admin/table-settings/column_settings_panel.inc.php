<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Column Settings widget
 * @author Alexander Gilmanov
 * @since 13.10.2016
 */
?>
<!-- .card .column-settings-panel -->
<div class="card column-settings-panel">

    <div class="card-header ch-alt">
        <h2><?php _e('Column settings', 'wpdatatables'); ?>: <span
                    class="label wdtColumnOrigHeader">Position</span>
        </h2>
        <ul class="actions">
            <li>
                <button class="btn wdt-cancel-column-settings">
                    <?php _e('Cancel', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button class="btn btn-primary wdt-column-apply">
                    <i class="wpdt-icon-save"></i><?php _e('Save Changes', 'wpdatatables'); ?>
                </button>
            </li>
        </ul>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding wpdt-column-settings-card">

        <div class="row wpDataTableContainer wpDataTables wpDataTablesWrapper m-l-5">

            <div role="tabpanel">
                <ul class="tab-nav" role="tablist">
                    <li class="active column-display-settings-tab">
                        <a href="#column-display-settings" aria-controls="column-display-settings" role="tab"
                           data-toggle="tab"><?php _e('Display', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-data-settings-tab">
                        <a href="#column-data-settings" aria-controls="column-data-settings" role="tab"
                           data-toggle="tab"><?php _e('Data', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-sorting-settings-tab">
                        <a href="#column-sorting-settings" aria-controls="column-sorting-settings" role="tab"
                           data-toggle="tab"><?php _e('Sorting', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-filtering-settings-tab">
                        <a href="#column-filtering-settings" aria-controls="column-filtering-settings" role="tab"
                           data-toggle="tab"><?php _e('Filtering', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-editing-settings-tab">
                        <a href="#column-editing-settings" aria-controls="column-editing-settings" role="tab"
                           data-toggle="tab"><?php _e('Editing', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-conditional-formatting-settings-tab">
                        <a href="#column-conditional-formatting-settings"
                           aria-controls="column-conditional-formatting-settings" role="tab"
                           data-toggle="tab"><?php _e('Conditional formatting', 'wpdatatables'); ?></a>
                    </li>
                </ul>
                <!-- /ul .tab-nav -->

                <div class="tab-content">
                    <!-- Column display settings -->
                    <div role="tabpanel" class="tab-pane active" id="column-display-settings">
                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Displayed header', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="You can redefine the visible column header here, it will be shown instead of the orignial header."></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value="Column"
                                               id="wdt-column-display-header">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Column position', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="You can redefine the position of the column here. Other columns will automatically re-position if you change it and click Apply."></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-column-position">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="text" name="wdt-column-position" min="0" value="" class="form-control input-sm input-number" id="wdt-column-position">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-column-position">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Cell content prefix', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-cell-prefix-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-cell-prefix-hint">
                                    <div class="popover-heading">
                                        <?php _e('Cell content prefix', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/text_before.png"/>
                                        </div>
                                        <?php _e('Any text or symbol entered here will be shown before the value in every cell inside of this column. E.g.: $, €. Does not influence filtering and sorting. Convenient for prices, percentages, etc.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm"
                                               id="wdt-column-display-text-before" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Cell content suffix', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-cell-suffix-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-cell-suffix-hint">
                                    <div class="popover-heading">
                                        <?php _e('Cell content suffix', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/text_after.png"/>
                                        </div>
                                        <?php _e('Any text or symbol entered here will be shown after the value in every cell inside of this column. E.g.: %, pcs. Does not influence filtering and sorting. Convenient for prices, percentages, etc.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm"
                                               id="wdt-column-display-text-after" value="">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->


                        <div class="row wdt-columns-responsive-block">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Hide on mobiles', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="Disabling this will make the column collapse to an expandable block when table viewed from mobile devices."></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-hide-column-on-mobiles" type="checkbox">
                                        <label for="wdt-hide-column-on-mobiles"
                                               class="ts-label"><?php _e('Collapse column on mobile devices', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Hide on tablets', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="Disabling this will make the column collapse to an expandable block when table viewed from tablet devices."></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-hide-column-on-tablets" type="checkbox">
                                        <label for="wdt-hide-column-on-tablets"
                                               class="ts-label"><?php _e('Collapse column on tablet devices', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row">

                            <div class="col-sm-6 cp-container">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('CSS class(es)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="Here you can define additional CSS classes, which will be applied to all the cells in this column and can be used for customizing the styling."></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" id="wdt-column-css-class"
                                               value="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Visible on front-end', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="Disabling this will hide the column from table front-end. Please note that if column visibility is enabled in Table Tools block, front-end users will be able to access hidden columns as well."></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-visible" type="checkbox">
                                        <label for="wdt-column-visible"
                                               class="ts-label"><?php _e('Show column in front-end', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row">

                            <div class="col-sm-6 wdt-column-width-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Column width', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="Input width for column( in percents with % or pixels without px). Leave a blank if you want to leave auto width."></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" id="wdt-column-width">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 cp-container">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Column color', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-color-hint  "
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-color-hint">
                                    <div class="popover-heading">
                                        <?php _e('Column color', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/column_color.png"/>
                                        </div>
                                        <?php _e('Here you can override the default color for the complete column', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="cp-container">
                                    <div class="form-group">
                                        <div class="fg-line dropdown">
                                            <div id="cp"
                                                 class="input-group wdt-color-picker">
                                                <input type="text" id="wdt-column-color" value=""
                                                       class="form-control cp-value cp-inside wdt-add-picker"/>
                                                <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-group-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Group column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#group-column"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="group-column">
                                    <div class="popover-heading">
                                        <?php _e('Group column', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/group_column.png"/>
                                        </div>
                                        <?php _e('If one column of your table contains similar values for many rows, maybe it makes sense to use it as a “group column”: it means that it will not be rendered as a column, but its values will be used to group the rows. Each group will be marked with one row with joined cells above, containing the group value.', 'wpdatatables'); ?>
                                        <strong><?php _e('Group column will not work with MySQL tables with server-side processing enabled and with manual tables!', 'wpdatatables'); ?></strong>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-group-column" type="checkbox">
                                        <label for="wdt-group-column"
                                               class="ts-label"><?php _e('Toggle column grouping', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <?php do_action('wdt_add_column_display_settings_element'); ?>

                        </div>
                        <!-- /.row -->

                    </div>
                    <!-- /#column-display-settings -->

                    <!-- Column data settings -->
                    <div role="tabpanel" class="tab-pane" id="column-data-settings">
                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Column type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('You can redefine the column data type here. Affects sorting, filtering and display logic. For manually created tables this will change the column type in database as well, which may result in data loss.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-type">
                                                <option value="string"><?php _e('String', 'wpdatatables'); ?></option>
                                                <option value="int"><?php _e('Integer', 'wpdatatables'); ?></option>
                                                <option value="float"><?php _e('Float', 'wpdatatables'); ?></option>
                                                <option value="date"><?php _e('Date', 'wpdatatables'); ?></option>
                                                <option value="datetime"><?php _e('DateTime', 'wpdatatables'); ?></option>
                                                <option value="time"><?php _e('Time', 'wpdatatables'); ?></option>
                                                <option value="link"><?php _e('URL link', 'wpdatatables'); ?></option>
                                                <option value="email"><?php _e('E-mail link', 'wpdatatables'); ?></option>
                                                <option value="image"><?php _e('Image', 'wpdatatables'); ?></option>
                                                <option disabled="disabled"
                                                        value="formula"><?php _e('Formula', 'wpdatatables'); ?></option>

                                                <?php do_action('wpdatatables_add_custom_column_type_option'); ?>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-formula-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Formula for calculation', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('You can create a formula (calculated column) based on other numeric columns (Integer and Float)', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <button class="btn btn-primary wdt-open-formula-editor"><?php _e('Open formula editor', 'wpdatatables'); ?></button>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-numeric-column-block wdt-float-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Decimal places', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('You can redefine the visible decimal places for a float and formula columns here. If you leave this field empty amount of decimal places will be loaded from the settings page. 0 is available just for formula columns ', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-column-decimal-places">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="text" name="wdt-column-decimal-places" min="1" value="" class="form-control input-sm input-number" id="wdt-column-decimal-places">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-column-decimal-places">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-numeric-column-block wdt-skip-thousands-separator-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Skip thousands separator', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Do not show thousands separator for this column (e.g. when showing years, IDs).', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-skip-thousands" type="checkbox">
                                        <label for="wdt-column-skip-thousands"
                                               class="ts-label"><?php _e('Skip thousands separator', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-type-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Possible values for column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="left"
                                       title="<?php _e('Define the logic for fetching the possible values for this column for filtering, and for editing: read from table itself on page load (will always contain only the values that already exist in the table), hard-code the list, or configure a relation with a different wpDataTable (use values in this column as foreign key).', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-values">
                                                <option value="read"><?php _e('Read from table on page load', 'wpdatatables'); ?></option>
                                                <option value="list"><?php _e('Define values list', 'wpdatatables'); ?></option>
                                                <option value="foreignkey"><?php _e('Use values from another wpDataTable (foreign key)', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-date-input-format-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Date input format', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Define date format which is used in the provided data source. Necessary for smooth reading of your dates.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-date-input-format">
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
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row wdt-possible-values-options-block">

                            <div class="col-sm-12 wdt-manual-list-enter-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Values list', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enter the possible values for this column in this list, separate by pressing ENTER or comma. Click "Read from table" to fetch the list from existing column values. Values will be used in selectbox and checkbox filters and editors for the column.', 'wpdatatables'); ?>"></i>
                                    <button class="btn btn-danger btn-xs pull-right"
                                            id="wdt-column-values-reset"><i class="wpdt-icon-exclamation-triangle"></i><?php _e('Reset', 'wpdatatables'); ?></button>
                                    <button class="btn btn-xs pull-right"
                                            id="wdt-column-values-read-from-table"><?php _e('Read from table', 'wpdatatables'); ?></button>
                                    <div class="clear"></div>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input class="form-control input-sm" value="" id="wdt-column-values-list"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 wdt-foreign-key-block" hidden="hidden">

                                <div class="col-sm-6 wdt-foreign-values p-l-0">
                                    <h4 class="c-title-color m-b-2">
                                        <?php _e('Use values from another wpDataTable', 'wpdatatables'); ?>
                                        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="bottom"
                                           title="<?php _e('Configure a relation with a separate wpDataTable: values in this column will be used as a foreign key, and replaced with values of a remote table column (e.g.: user name instead of user ID).', 'wpdatatables'); ?>"></i>
                                    </h4>
                                    <div class="form-group">
                                        <button class="btn btn-primary btn-sm"
                                                id="wdt-foreign-key-open"><?php _e('Configure relation...', 'wpdatatables'); ?></button>
                                    </div>
                                </div>

                                <div class="col-sm-6 wdt-foreign-rule-display p-r-0">
                                    <div class="form-group">
                                        <div class="well">
                                            <strong><?php _e('Table', 'wpdatatables'); ?>:</strong> <span id="wdt-connected-table-name"> -</span>,
                                            <strong><?php _e('Show column', 'wpdatatables'); ?>:</strong> <span
                                                    id="wdt-connected-table-show-column"> -</span>,
                                            <strong><?php _e('Value from column', 'wpdatatables'); ?>:</strong> <span
                                                    id="wdt-connected-table-value-column"> -</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Allow empty value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Add an empty value to the possible values list, to allow empty editing and filtering inputs.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-values-add-empty" type="checkbox">
                                        <label for="wdt-column-values-add-empty"
                                               class="ts-label"><?php _e('Add an empty value to the list', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-foreign-keys-block" >
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Possible values in edit modal for foreign key column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('By turning this on you will show all possible values in edit modal from separate table that is connect with foreign keys when option Users can see and edit own data is enabled', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-possible-values-foreign-keys" type="checkbox">
                                        <label for="wdt-possible-values-foreign-keys"
                                               class="ts-label"><?php _e('Allow all possible values in edit modal', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-ajax-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Number of possible values to load', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Define here how many possible values per page will be loaded in selectbox filters and editor inputs. It is recommended not to be set to All if you have more than 50 possible values for this column. This option is not working when Cascade Filtering option from Powerful Filters add-on is enabled.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-possible-values-ajax">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="-1"><?php _e('All', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row wdt-numeric-column-block">

                            <div class="col-sm-6 wdt-column-calc-total-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Calculate total', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable this to show a total (sum) for all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-total" type="checkbox">
                                        <label for="wdt-column-calc-total"
                                               class="ts-label"><?php _e('Calculate a total for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-total-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-total" title="<?php _e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the total for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-total-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-avg-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Calculate average', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable this to show an average value for all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-avg" type="checkbox">
                                        <label for="wdt-column-calc-avg"
                                               class="ts-label"><?php _e('Calculate average for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-avg-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-avg" title="<?php _e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the average for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-avg-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-min-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Calculate MIN value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable this to show a minimum value within all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-min" type="checkbox">
                                        <label for="wdt-column-calc-min"
                                               class="ts-label"><?php _e('Find a minimum value for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-min-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-min" title="<?php _e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the minimum for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-min-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-max-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Calculate MAX value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable this to show a maximum value within all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-max" type="checkbox">
                                        <label for="wdt-column-calc-max"
                                               class="ts-label"><?php _e('Find a maximum value for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-max-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-max" title="<?php _e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the maximum for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-max-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row wdt-link-column-block">

                            <div class="col-sm-6 wdt-link-target-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('URL target attribute', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Set how to open URL Target', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-target-attribute" type="checkbox">
                                        <label for="wdt-link-target-attribute"
                                               class="ts-label"><?php _e('Open link in the new tab', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-nofollow-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Make NOFOLLOW link', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Set the link to a nofollow link', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-nofollow-attribute" type="checkbox">
                                        <label for="wdt-link-nofollow-attribute"
                                               class="ts-label"><?php _e('Make the link a nofollow link', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-button-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Show link as a button', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('The link will be shown as a button ', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-button-attribute" type="checkbox">
                                        <label for="wdt-link-button-attribute"
                                               class="ts-label"><?php _e('Set the link to appear as a button', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-link-button-label-block" >
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Button text', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="You can set the button display text"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-link-button-label">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-link-button-class-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Button class', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="You can set the button class"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-link-button-class">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                    </div>
                    <!--/#column-data-settings -->

                    <!-- Column sorting settings -->
                    <div role="tabpanel" class="tab-pane" id="column-sorting-settings">
                        <!-- .row -->
                        <div class="row">
                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Allow sorting', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Disable this to disallow sorting for this column.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-allow-sorting" type="checkbox">
                                        <label for="wdt-column-allow-sorting"
                                               class="ts-label"><?php _e('Allow sorting for this column', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-default-sorting-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Use as default sorting column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Sort table by this column on load', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-default-sort" type="checkbox">
                                        <label for="wdt-column-default-sort"
                                               class="ts-label"><?php _e('Sort table by this column on load', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row">
                            <div class="col-sm-6 wdt-column-default-sorting-direction-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Default sorting direction', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Choose whether to sort ascending or descending by default.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-default-sorting-direction">
                                                <option value="1"><?php _e('Ascending', 'wpdatatables'); ?></option>
                                                <option value="2"><?php _e('Descending', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                    </div>
                    <!--/#column-sorting-settings -->

                    <!-- Column filtering settings -->
                    <div role="tabpanel" class="tab-pane" id="column-filtering-settings">
                        <div class="row">

                            <div class="col-sm-6 wdt-column-enable-filter-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Add a filter for this column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-filter-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-filter-hint">
                                    <div class="popover-heading">
                                        <?php _e('Enable filtering for column', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/column_filter.png"/>
                                        </div>
                                        <?php _e('Enabling this switch will add a filter for this column. Disable to remove the filter for this column.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-enable-filter" type="checkbox">
                                        <label for="wdt-column-enable-filter"
                                               class="ts-label"><?php _e('Allow filtering', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-global-search-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Global search', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('If this option is turned off, the column will not appear in your global search results. The filtering option will also be disabled.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-enable-global-search" type="checkbox">
                                        <label for="wdt-column-enable-global-search"
                                               class="ts-label"><?php _e('Enable this column in Global search', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <!-- .row -->
                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Filter type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('You can redefine the filter type here, it will affect the filtering logic.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-filter-type">
                                                <option value="text"><?php _e('Text', 'wpdatatables'); ?></option>
                                                <option value="number"><?php _e('Number', 'wpdatatables'); ?></option>
                                                <option value="number-range"><?php _e('Number range', 'wpdatatables'); ?></option>
                                                <option value="date-range"><?php _e('Date range', 'wpdatatables'); ?></option>
                                                <option value="datetime-range"><?php _e('DateTime range', 'wpdatatables'); ?></option>
                                                <option value="time-range"><?php _e('Time range', 'wpdatatables'); ?></option>
                                                <option value="select"><?php _e('Selectbox', 'wpdatatables'); ?></option>
                                                <option value="multiselect"><?php _e('Multiselectbox', 'wpdatatables'); ?></option>
                                                <option value="checkbox"><?php _e('Checkbox', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Filter label', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#filter-label-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="filter-label-hint">
                                    <div class="popover-heading">
                                        <?php _e('Custom filter label', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/custom_filter_label.png"/>
                                        </div>
                                        <?php _e('You can change the filter label (placeholder) for this column here, if you would like to show some custom text instead of default.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-column-filter-label">
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <!-- .row -->

                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-exact-filtering-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Exact filtering', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable exact search to use exact match logic for filtering, disable to allow partial match.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-exact-filtering" type="checkbox">
                                        <label for="wdt-column-exact-filtering"
                                               class="ts-label"><?php _e('Enable exact filtering', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-number-range-slider">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Range slider', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable number range slider  for filtering', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-range-slider" type="checkbox">
                                        <label for="wdt-column-range-slider"
                                               class="ts-label"><?php _e('Number range slider', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Predefined value(s)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Define value(s) that will be set as default pre-defined filter value(s) on page load.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group wdt-filter-default-value-block">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 p-l-0 wdt-filter-default-value-from-block"
                                     hidden="hidden">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value-from" placeholder="From">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 p-r-0 wdt-filter-default-value-to-block"
                                     hidden="hidden">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value-to" placeholder="To">
                                    </div>
                                </div>
                                <div class="form-group wdt-filter-default-value-selectpicker-block" hidden="hidden">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-filter-default-value-selectpicker" data-live-search="true">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-checkboxes-in-modal-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Render in modal', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Render checkboxes in modal.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-checkboxes-in-modal" type="checkbox">
                                        <label for="wdt-checkboxes-in-modal"
                                               class="ts-label"><?php _e('Render checkboxes in modal', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ .row -->

                    </div>
                    <!--/#column-filtering-settings -->

                    <!-- Column editing settings -->
                    <div role="tabpanel" class="tab-pane" id="column-editing-settings">

                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Editor input type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Choose which kind of editor input to use for this column.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-editor-input-type">
                                                <option value="none"><?php _e('None', 'wpdatatables'); ?></option>
                                                <option value="text"><?php _e('One-line edit', 'wpdatatables'); ?></option>
                                                <option value="textarea"><?php _e('Multi-line edit', 'wpdatatables'); ?></option>
                                                <option value="mce-editor"><?php _e('HTML editor', 'wpdatatables'); ?></option>
                                                <option value="selectbox"><?php _e('Single-value selectbox', 'wpdatatables'); ?></option>
                                                <option value="multi-selectbox"><?php _e('Multi-value selectbox', 'wpdatatables'); ?></option>
                                                <option value="date"><?php _e('Date', 'wpdatatables'); ?></option>
                                                <option value="datetime"><?php _e('Datetime', 'wpdatatables'); ?></option>
                                                <option value="time"><?php _e('Time', 'wpdatatables'); ?></option>
                                                <option value="link"><?php _e('URL link', 'wpdatatables'); ?></option>
                                                <option value="email"><?php _e('E-mail link', 'wpdatatables'); ?></option>
                                                <option value="attachment"><?php _e('Attachment', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-editing-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Column cannot be empty', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('Enable to make this column mandatory. Users will see a warning when trying to save with empty input.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-not-null" type="checkbox">
                                        <label for="wdt-column-not-null"
                                               class="ts-label"><?php _e('Cannot be empty', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-6 wdt-editing-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php _e('Predefined value(s)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php _e('If you would like to have some values pre-defined in editors (i.e. default editor values) please enter these here.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group wdt-editing-default-value-block">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-editing-default-value">
                                    </div>
                                </div>

                                <div class="form-group wdt-editing-default-value-selectpicker-block" hidden="hidden">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-editing-default-value-selectpicker" data-live-search="true">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!--/ .row -->

                    </div>
                    <!--/#column-editing-settings -->

                    <!-- Column conditional formatting settings -->
                    <div role="tabpanel" class="tab-pane" id="column-conditional-formatting-settings">
                        <div class="wdt-conditional-formatting-rules-container">

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-12 p-l-0">
                                <button class="btn pull-left wdt-column-add-conditional-formatting-rule">
                                    <i class="wpdt-icon-plus-thin"></i> <?php _e('Add New Rule', 'wpdatatables'); ?>
                                </button>
                            </div>

                        </div>

                    </div>
                    <!--/#column-conditional-formatting-settings -->

                </div>
                <!-- /.tab-content -->
            </div>
            <!--/.tabpanel -->

        </div>
        <!-- /.wpDataTableContainer -->

        <div class="row">

            <div class="col-md-12 p-l-20 p-r-20" id="wdt-column-settings-buttons">
                <hr>
                <button class="btn btn-default btn-icon-text wdt-documentation"
                        data-doc-page="column_settings">
                    <i class="wpdt-icon-file-thin"></i> <?php _e('View Documentation', 'wpdatatables'); ?>
                </button>

                <div class="pull-right">
                    <button class="btn btn-danger btn-icon-text wdt-cancel-column-settings">
                       <?php _e('Cancel', 'wpdatatables'); ?>
                    </button>
                    <button class="btn btn-primary btn-icon-text wdt-column-apply">
                        <i class="wpdt-icon-save"></i> <?php _e('Save Changes', 'wpdatatables'); ?>
                    </button>
                </div>
            </div>
            <!-- /.col-md-12.p-l-20.p-r-20 -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /.card-body -->

</div>
<!-- /.card  .column-settings-panel -->
