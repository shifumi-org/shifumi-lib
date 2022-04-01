<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Table Settings widget
 * @author Alexander Gilmanov
 * @since 13.10.2016
 */
?>

<div class="card wdt-table-settings">

    <?php
    // set connection if $connection is not set with GET parameter
    if ($connection === null) {
        if (Connection::enabledSeparate()) {
            foreach (Connection::getAll() as $wdtSeparateConnection) {
                if ($wdtSeparateConnection['default']) {
                    $connection = $wdtSeparateConnection['id'];
                }
            }
        } else {
            $connection === '';
        }
    }
    ?>

    <input type="hidden" id="wdt-table-connection" value="<?php echo $connection; ?>"/>

    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->

    <div class="card-header wdt-admin-card-header ch-alt ">
        <img id="wpdt-inline-logo"
             src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
        <h2 class="pull-left">
            <div class="fg-line wdt-table-name">
                <input type="text" class="form-control input-sm" value="New wpDataTable" id="wdt-table-title-edit">
                <i class="wpdt-icon-pen"></i>
            </div>

            <small class="m-t-5 m-l-5"><?php _e('wpDataTable name, click to edit', 'wpdatatables'); ?></small>
        </h2>
        <button class="btn hidden" id="wdt-table-id">[wpdatatable id=23]
        </button>
        <div class="clear"></div>
        <ul id="wdt-tour-actions" class="actions p-t-5">
            <li>
                <button class="btn wdt-collapse-table-settings <?php if (isset($_GET['collapsed'])) { ?>collapsed <?php } else { ?>expanded <?php } ?>">
                    <?php _e('Settings', 'wpdatatables'); ?>
                    <i style="color: #008CFF;"
                       class="wpdt-icon-angle-<?php if (isset($_GET['collapsed'])) { ?>down <?php } else { ?>up <?php } ?>"></i>
                </button>
            </li>
            <li>
                <button class="btn wdt-backend-close">
                    <?php _e('Cancel', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button class="btn wdt-reset-customize-options" style="display: none"
                        title="<?php _e('Reset customize options to default', 'wpdatatables'); ?>" data-toggle="tooltip">
                    <?php _e('Reset options', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button disabled="disabled"
                        class="btn btn-primary wdt-apply"
                        title="<?php _e('Save Changes', 'wpdatatables'); ?>" data-toggle="tooltip">
                    <i class="wpdt-icon-save"></i><?php _e('Save Changes', 'wpdatatables'); ?>
                </button>
            </li>
        </ul>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding" <?php if (isset($_GET['collapsed'])) { ?> style="display: none" <?php } ?>>
        <div role="tabpanel">
            <ul class="tab-nav wdt-main-menu" role="tablist">
                <li class="active main-table-settings-tab">
                    <a href="#main-table-settings" aria-controls="main-table-settings" role="tab"
                       data-toggle="tab"><?php _e('Data source', 'wpdatatables'); ?></a>
                </li>
                <li class="display-settings-tab hidden">
                    <a href="#display-settings" aria-controls="display-settings" role="tab"
                       data-toggle="tab"><?php _e('Display', 'wpdatatables'); ?></a>
                </li>
                <li class="table-sorting-filtering-settings-tab hidden">
                    <a href="#table-sorting-filtering-settings" aria-controls="table-sorting-filtering-settings"
                       role="tab" data-toggle="tab"><?php _e('Sorting and filtering', 'wpdatatables'); ?></a>
                </li>
                <li class="editing-settings-tab hidden">
                    <a href="#editing-settings" aria-controls="editing-settings" role="tab"
                       data-toggle="tab"><?php _e('Editing', 'wpdatatables'); ?></a>
                </li>
                <li class="table-tools-settings-tab hidden">
                    <a href="#table-tools-settings" aria-controls="table-tools-settings" role="tab"
                       data-toggle="tab"><?php _e('Table Tools', 'wpdatatables'); ?></a>
                </li>
                <li class="placeholders-settings-tab hidden">
                    <a href="#placeholders-settings" aria-controls="placeholders-settings" role="tab"
                       data-toggle="tab"><?php _e('Placeholders', 'wpdatatables'); ?></a>
                </li>
                <li class="customize-table-settings-tab hidden">
                    <a href="#customize-table-settings" aria-controls="customize-table-settings" role="tab"
                       data-toggle="tab"><strong
                                style="color: #ef8137"><?php _e('NEW!', 'wpdatatables'); ?></strong> <?php _e(' Customize', 'wpdatatables'); ?>
                    </a>
                </li>

                <?php do_action('wdt_add_table_configuration_tab'); ?>

            </ul>
            <!-- /ul .tab-nav -->

            <div class="tab-content">
                <!-- Main table settings -->
                <div role="tabpanel" class="tab-pane active" id="main-table-settings">

                    <div class="row">

                        <div class="col-sm-6 wdt-input-data-source-type">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Input data source type', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Please choose a type of the input data source - it can be a MySQL query, a file, or an URL. Only MySQL query-based tables can use server-side processing', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input source type selection -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-table-type">
                                            <option value=""><?php _e('Select a data source type', 'wpdatatables'); ?></option>
                                            <option value="mysql"><?php _e('SQL query', 'wpdatatables'); ?></option>
                                            <option value="csv"><?php _e('CSV file', 'wpdatatables'); ?></option>
                                            <option value="xls"><?php _e('Excel file', 'wpdatatables'); ?></option>
                                            <option value="google_spreadsheet"><?php _e('Google Spreadsheet', 'wpdatatables'); ?></option>
                                            <option value="xml"><?php _e('XML file', 'wpdatatables'); ?></option>
                                            <option value="json"><?php _e('JSON file', 'wpdatatables'); ?></option>
                                            <option value="serialized"><?php _e('Serialized PHP array', 'wpdatatables'); ?></option>
                                            <?php do_action('wdt_add_table_type_option'); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /input source type selection -->
                        </div>

                        <div class="col-sm-6 input-path-block hidden">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Input file path or URL', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Upload your file or provide the full URL here. For CSV or Excel input sources only URLs or paths from same domain are supported. For Google Spreadsheets: please do not forget to publish the spreadsheet before pasting the URL.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input URL or path -->
                            <div class="form-group">
                                <div class="fg-line col-sm-9 p-0">
                                    <input type="text" id="wdt-input-url" class="form-control input-sm"
                                           placeholder="<?php _e('Paste URL or path, or click Browse to choose', 'wpdatatables'); ?>">
                                </div>
                                <div class="col-sm-3">
                                    <button class="btn btn-primary" id="wdt-browse-button">
                                        <?php _e('Browse...', 'wpdatatables'); ?>
                                    </button>
                                </div>
                            </div>
                            <!-- /input URL or path -->
                        </div>

                        <?php do_action('wdt_add_data_source_elements'); ?>

                        <div class="col-sm-6 hidden wdt-server-side-processing">
                            <!-- Server side processing toggle -->
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Server-side processing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('If it is turned on, all sorting, filtering, pagination and other data interaction will be done by MySQL server. This feature is recommended if you have more than 2000-3000 rows. Mandatory for editable tables.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-server-side" class="wdt-server-side" type="checkbox" checked="checked">
                                <label for="wdt-server-side"
                                       class="ts-label"><?php _e('Enable server-side processing', 'wpdatatables'); ?></label>
                            </div>
                            <!-- /Server side processing toggle -->
                        </div>


                    </div>
                    <!-- /.row -->

                    <div class="row">
                        <div class="col-sm-6 hidden mysql-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('SQL Query', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Enter the text of your SQL query here - please make sure it returns actual data first. You can use a number of placeholders to make the dataset in the table flexible and be able to return different sets of data by calling it with different shortcodes.', 'wpdatatables'); ?>"></i>
                                <div class="" data-placement="top"
                                     style="color: gray; float: right;"><?php echo Connection::enabledSeparate() ? Connection::getName($connection) : '' ?>
                                </div>
                            </h4>
                            <pre id="wdt-mysql-query" style="width: 100%; height: 250px"></pre>
                        </div>
                        <div class="col-sm-6 hidden wdt-auto-refresh">

                            <h4 class="c-title-color m-b-2 m-t-20">
                                <?php _e('Auto-refresh', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('If you enter a non-zero value, table will auto-refresh to show actual data with a given interval of seconds. Leave zero or empty not to use auto-refresh.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line wdt-custom-number-input">
                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                            data-type="minus" data-field="wdt-auto-refresh">
                                        <i class="wpdt-icon-minus"></i>
                                    </button>
                                    <input type="text" name="wdt-auto-refresh" min="0"
                                           class="form-control input-sm input-number"
                                           id="wdt-auto-refresh"
                                           placeholder="<?php _e('Auto-refresh interval in seconds (zero or blank to disable)', 'wpdatatables'); ?>">
                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                            data-type="plus" data-field="wdt-auto-refresh">
                                        <i class="wpdt-icon-plus-full"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Main table settings -->

                <!-- Table display settings -->
                <div role="tabpanel" class="tab-pane fade" id="display-settings">

                    <div class="row">
                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Table title', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-title-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-title-hint">
                                <div class="popover-heading">
                                    <?php _e('Show table title', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/table_title.png"/>
                                    </div>
                                    <?php _e('Enable this to show the table title in a h3 block above the table, disable to hide.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-title" type="checkbox" checked="checked">
                                <label for="wdt-show-title"
                                       class="ts-label"><?php _e('Show table title on the page', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-responsive-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Responsiveness', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-responsive-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-responsive-hint">
                                <div class="popover-heading">
                                    <?php _e('Responsive design', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/responsive.png"/>
                                    </div>
                                    <?php _e('Enable this to allow responsiveness in the table.', 'wpdatatables'); ?>
                                    <strong><?php _e('Please do not forget to define which columns will be hidden on mobiles and tablets in the column settings!', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-responsive" type="checkbox" checked="checked">
                                <label for="wdt-responsive"
                                       class="ts-label"><?php _e('Allow collapsing on mobiles and tablets', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-hide-until-load-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Hide until loaded', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Enable to make whole table hidden until it is initialized to prevent unformatted data flashing', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-hide-until-loaded" type="checkbox" checked="checked">
                                <label for="wdt-hide-until-loaded"
                                       class="ts-label"><?php _e('Hide the table before it is fully loaded', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-4 wdt-default-rows-per-page">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Default rows per page', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#rows-per-page-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="rows-per-page-hint">
                                <div class="popover-heading">
                                    <?php _e('Rows per page', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/rows_per_page.png"/>
                                    </div>
                                    <?php _e('How many rows to show per page by default?', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <!-- Rows per page selection -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="form-control selectpicker" id="wdt-rows-per-page">
                                            <option value="1">1</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="-1"><?php _e('All', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /rows per page selection -->

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-rows-per-page-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Rows per page', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#show-rows-per-page-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="show-rows-per-page-hint">
                                <div class="popover-heading">
                                    <?php _e('Show X entries', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/rows_per_page.png"/>
                                    </div>
                                    <?php _e('Enable/disable this to show/hide "Show X entries" per page dropdown on the frontend.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-rows-per-page" type="checkbox" checked="checked">
                                <label for="wdt-show-rows-per-page"
                                       class="ts-label"><?php _e('Show "Show X entries" dropdown', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-scrollable-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Scrollable', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#scrollable-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="scrollable-hint">
                                <div class="popover-heading">
                                    <?php _e('Scrollable table', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/scrollable.png"/>
                                    </div>
                                    <?php _e('Enable this to enable a horizontal scrollbar below the table.', 'wpdatatables'); ?>
                                    <strong><?php _e('This should be turned off if you want to set columns width manually.', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-scrollable" type="checkbox">
                                <label for="wdt-scrollable"
                                       class="ts-label"><?php _e('Show a horizontal scrollbar', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Info block', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#info-block-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="info-block-hint">
                                <div class="popover-heading">
                                    <?php _e('Info block', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/info_block.png"/>
                                    </div>
                                    <?php _e('Enable to show a block of information about the number of records below the table.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-info-block" type="checkbox" checked="checked">
                                <label for="wdt-info-block"
                                       class="ts-label"><?php _e('Show information block below the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 limit-table-width-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Limit table width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#limit-width-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="limit-width-hint">
                                <div class="popover-heading">
                                    <?php _e('Limit table width', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/limit_width.png"/>
                                    </div>
                                    <?php _e('Enable this to restrict table width to page width.', 'wpdatatables'); ?>
                                    <strong><?php _e('This should be turned on if you want to set columns width manually. Should be on to use word wrapping.', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-limit-layout" type="checkbox">
                                <label for="wdt-limit-layout"
                                       class="ts-label"><?php _e('Limit table width to page width', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 word-wrap-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Word wrap', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#word-wrap-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="word-wrap-hint">
                                <div class="popover-heading">
                                    <?php _e('Word wrap', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/word_wrap.png"/>
                                    </div>
                                    <?php _e('Enable this to wrap long strings into multiple lines and stretch the cells height.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-word-wrap" type="checkbox">
                                <label for="wdt-word-wrap"
                                       class="ts-label"><?php _e('Wrap words to newlines', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Pagination', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination">
                                <div class="popover-heading">
                                    <?php _e('Pagination', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e('Enable to show a pagination', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-pagination" type="checkbox" checked="checked">
                                <label for="wdt-pagination"
                                       class="ts-label"><?php _e('Show pagination block below the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 pagination-align-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Pagination Alignment', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-align"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-align">
                                <div class="popover-heading">
                                    <?php _e('Pagination Alignment', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e('Here you can set pagination position: right, center or left.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pagination-align">
                                    <option value="right"><?php _e('Right', 'wpdatatables'); ?></option>
                                    <option value="center"><?php _e('Center', 'wpdatatables'); ?></option>
                                    <option value="left"><?php _e('Left', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 pagination-layout-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Pagination Layout', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-layout"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-layout">
                                <div class="popover-heading">
                                    <?php _e('Pagination Layout', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e('Here you can choose between different pagination layout.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pagination-layout">
                                    <option value="full_numbers"><?php _e('"First", "Previous", "Next" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="simple"><?php _e('"Previous" and "Next" buttons only', 'wpdatatables'); ?></option>
                                    <option value="simple_numbers"><?php _e('"Previous" and "Next" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="full"><?php _e('"First", "Previous", "Next" and "Last" buttons', 'wpdatatables'); ?></option>
                                    <option value="numbers"><?php _e('Page number buttons only', 'wpdatatables'); ?></option>
                                    <option value="first_last_numbers"><?php _e('"First" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table display settings -->

                <!-- Table sorting and filtering settings -->
                <div role="tabpanel" class="tab-pane fade" id="table-sorting-filtering-settings">
                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Advanced column filters', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#advanced-filter-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="advanced-filter-hint">
                                <div class="popover-heading">
                                    <?php _e('Advanced filter', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/advanced_filter.png"/>
                                    </div>
                                    <?php _e('Enable to show an advanced filter for each of the columns, filters can be shown in table footer, header or in a separate form.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-advanced-filter" type="checkbox" checked="checked">
                                <label for="wdt-advanced-filter"
                                       class="ts-label"><?php _e('Enable advanced column filters', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Sorting', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#sorting-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="sorting-hint">
                                <div class="popover-heading">
                                    <?php _e('Sorting', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/sorting.png"/>
                                    </div>
                                    <?php _e('If this is enabled, each column header will be clickable; clicking will sort the whole table by the content of this column cells ascending or descending.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-global-sorting" type="checkbox" checked="checked">
                                <label for="wdt-global-sorting"
                                       class="ts-label"><?php _e('Allow sorting for the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Main search block', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#global-search-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="global-search-hint">
                                <div class="popover-heading">
                                    <?php _e('Global search', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/global_search.png"/>
                                    </div>
                                    <?php _e('If this is enabled, a search block will be displayed on the top right of the table, allowing to search through whole table with a single input.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-global-search" type="checkbox">
                                <label for="wdt-global-search"
                                       class="ts-label"><?php _e('Enable search block', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16 filtering-form-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Filters in a form', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#filter-in-form-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>
                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="filter-in-form-hint">
                                <div class="popover-heading">
                                    <?php _e('Filter in form', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/filter_in_form.png"/>
                                    </div>
                                    <?php _e('Enable to show the advanced column filter in a form above the table, instead of showing in the table footer/header.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-filter-in-form" type="checkbox">
                                <label for="wdt-filter-in-form"
                                       class="ts-label"><?php _e('Show filters in a form above the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-clear-filters-block filtering-form-block">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Clear filters button', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#wdt-clear-filters-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>
                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="wdt-clear-filters-hint">
                                <div class="popover-heading">
                                    <?php _e('Clear filters', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e('Enable to show the clear filters button.', 'wpdatatables'); ?><br/><br/>
                                    <?php _e('If filter in form is enabled, clear button will be rendered after the last filter.', 'wpdatatables'); ?>
                                    <br/>
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/clear_filters_1.png"/>
                                    </div>
                                    <?php _e('Otherwise, clear filter button will be rendered above the table next to "Table Tools" buttons.', 'wpdatatables'); ?>
                                    <br/><br/>
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/clear_filters_2.png"/>
                                    </div>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-clear-filters" type="checkbox">
                                <label for="wdt-clear-filters"
                                       class="ts-label"><?php _e('Show clear filters button', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <?php do_action('wdt_add_sorting_and_filtering_element'); ?>

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table sorting and filtering settings -->

                <!-- Table editing settings -->
                <div role="tabpanel" class="tab-pane fade" id="editing-settings">

                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Allow editing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#front-end-editing-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="front-end-editing-hint">
                                <div class="popover-heading">
                                    <?php _e('Front-end editing', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/front_end_editing.png"/>
                                    </div>
                                    <?php _e('Allow editing the table from the front-end.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-editable" type="checkbox">
                                <label for="wdt-editable"
                                       class="ts-label"><?php _e('Allow front-end editing', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Popover edit block', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#popover-tools-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="popover-tools-hint">
                                <div class="popover-heading">
                                    <?php _e('Popover tools', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/popover_tools_hint.png"/>
                                    </div>
                                    <?php _e('If this is enabled, the New, Edit and Delete buttons will appear in a popover when you click on any row, instead of Table Tools block above the table.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-popover-tools" type="checkbox">
                                <label for="wdt-popover-tools"
                                       class="ts-label"><?php _e('Editing buttons in a popover', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('In-line editing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#inline-editing-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="inline-editing-hint">
                                <div class="popover-heading">
                                    <?php _e('In-line editing', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/inline_editing_hint.png"/>
                                    </div>
                                    <?php _e('If this is enabled, front-end users will be able to edit cells by double-clicking them, not only with the editor dialog.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-inline-editable" type="checkbox">
                                <label for="wdt-inline-editable"
                                       class="ts-label"><?php _e('Allow in-line editing', 'wpdatatables'); ?></label>
                            </div>

                        </div>


                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('MySQL table name for editing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="bottom"
                                   title="<?php _e('Name of the MySQL table which will be updated when edited from front-end.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line">
                                <input type="text" class="form-control"
                                       placeholder="<?php _e('MySQL table name', 'wpdatatables'); ?>"
                                       id="wdt-mysql-table-name">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('ID column for editing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose the column values from which will be used as row identifiers. MUST be a unique auto-increment integer on MySQL side so insert/edit/delete would work correctly! wpDataTables will guess the correct column if it is called "id" or "ID" on MySQL side.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" id="wdt-id-editing-column">

                                </select>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Editor roles', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('If you want only specific user roles to be able to edit the table, choose in this dropdown. Leave unchecked to allow editing for everyone.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        title="<?php _e('Everyone', 'wpdatatables'); ?>" id="wdt-editor-roles">
                                    <?php foreach ($wdtUserRoles as $wdtUserRole) {
                                        /** @noinspection $wdtUserRoles */ ?>
                                        <option value="<?php echo $wdtUserRole['name'] ?>"><?php echo $wdtUserRole['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Users see and edit only own data', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#own-rows-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="own-rows-hint">
                                <div class="popover-heading">
                                    <?php _e('Users see and edit only their own data', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/own_rows_hint.png"/>
                                    </div>
                                    <?php _e('If this is enabled, users will see and edit only the rows that are related to them or were created by them (associated using the User ID column).', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-edit-only-own-rows" type="checkbox">
                                <label for="wdt-edit-only-own-rows"
                                       class="ts-label"><?php _e('Limit editing to own data only', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 own-rows-editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('User ID column', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose the column values from which will be used as User identifiers. References the ID from WordPress Users table (wp_users), MUST be defined as an integer on MySQL side.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" id="wdt-user-id-column">

                                </select>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 show-all-rows-editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Show all rows in back-end', 'wpdatatables'); ?>
                                <i class="wpdt-icon-info-circle-thin" data-popover-content="#show-all-rows-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="show-all-rows-hint">
                                <div class="popover-heading">
                                    <?php _e('Show all rows in back-end', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e('If this is enabled, users will see all data for this table in admin area.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-all-rows" type="checkbox">
                                <label for="wdt-show-all-rows"
                                       class="ts-label"><?php _e('Show all rows for this table in admin area', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <!-- .row -->
                        <div class="col-sm-4 m-b-16 editing-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Edit buttons to be displayed on the front-end', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin"  data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('If you want to include only certain editing buttons on the front-end, select them from this dropdown. Leave unchecked to show all buttons.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        title="<?php _e('All', 'wpdatatables'); ?>" id="wdt-edit-buttons-displayed">
                                   <?php $wdtEditButtonsDisplayed = array('New Entry', 'Edit', 'Delete');
                                   if (isset($tableData)) {
                                       if ($tableData->table->enableDuplicateButton &&
                                           !in_array('duplicate', $tableData->table->editButtonsDisplayed)) {
                                           $wdtEditButtonsDisplayed[] = 'Duplicate';
                                       }
                                   }
                                   foreach ($wdtEditButtonsDisplayed as $wdtEditButtonDisplayed) {
                                        /** @noinspection $wdtEditButtonsDisplayed */ ?>
                                    <option value="<?php echo (str_replace(' ','_',strtolower($wdtEditButtonDisplayed))) ?>"><?php echo $wdtEditButtonDisplayed ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>

                        <?php if (isset($tableData)) {
                            if (!($tableData->table->table_type === 'gravity') ||
                                version_compare(WDT_GF_VERSION, "1.6.3", '>=')) {?>
                                <div class="col-sm-4 m-b-16 editing-settings-block">
                                    <h4 class="c-title-color m-b-4">
                                        <?php _e('Show duplicate button', 'wpdatatables'); ?>
                                        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                           title="<?php _e('Enable the duplicate button in Editing buttons', 'wpdatatables'); ?>"></i>
                                    </h4>
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-enable-duplicate-button" type="checkbox">
                                        <label for="wdt-enable-duplicate-button"
                                               class="ts-label"><?php _e('Enable duplicate button', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            <?php }} ?>


                        <!-- /.row -->

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table editing settings -->

                <!-- Table tools settings -->
                <div role="tabpanel" class="tab-pane fade" id="table-tools-settings">

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Table Tools', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-tools-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-tools-hint">
                                <div class="popover-heading">
                                    <?php _e('Table tools', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/table_tools_hint.png"/>
                                    </div>
                                    <?php _e('If this is enabled, a toolbar with useful tools will be shown above the table', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-table-tools" type="checkbox">
                                <label for="wdt-table-tools"
                                       class="ts-label"><?php _e('Enable Table Tools', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 table-tools-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Buttons', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose which buttons to show in the Table Tools block.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        id="wdt-table-tools-config">
                                    <option value="columns"><?php _e('Columns visibility', 'wpdatatables'); ?></option>
                                    <option value="print"><?php _e('Print', 'wpdatatables'); ?></option>
                                    <option value="excel"><?php _e('Excel', 'wpdatatables'); ?></option>
                                    <option value="csv"><?php _e('CSV', 'wpdatatables'); ?></option>
                                    <option value="copy"><?php _e('Copy', 'wpdatatables'); ?></option>
                                    <option value="pdf"><?php _e('PDF', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 table-tools-include-html-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Include HTML', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-tools-html-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-tools-html-hint">
                                <div class="popover-heading">
                                    <?php _e('Include HTML', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php _e(' If this is enabled, columns that contain HTML (such as link, image, email, attachment, or HTML) will be rendered as HTML in the exported files of the CSV, Excel, Print and Copy options.<br><br>NOTICE: Please note that you should check this functionality on the front-end since if you use this option in the back-end the buttons in headers will be rendered as HTML as well.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-table-tools-include-html" type="checkbox">
                                <label for="wdt-table-tools-include-html"
                                       class="ts-label"><?php _e('Enable HTML in export files (CSV, Excel, Print and Copy)', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row pdf-export-options hidden">
                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('PDF Paper Size', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title=" <?php _e('Choose between different paper sizes for the created PDF.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pdf-paper-size">
                                    <option value="A3"><?php _e('A3', 'wpdatatables'); ?></option>
                                    <option value="A4" selected><?php _e('A4', 'wpdatatables'); ?></option>
                                    <option value="A5"><?php _e('A5', 'wpdatatables'); ?></option>
                                    <option value="LEGAL"><?php _e('Legal', 'wpdatatables'); ?></option>
                                    <option value="LETTER"><?php _e('Letter', 'wpdatatables'); ?></option>
                                    <option value="TABLOID"><?php _e('Tabloid', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php _e('Page orientation', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title=" <?php _e('Choose a paper orientation for the created PDF.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pdf-page-orientation">
                                    <option value="portrait" selected><?php _e('Portrait', 'wpdatatables'); ?></option>
                                    <option value="landscape"><?php _e('Landscape', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table tools settings -->

                <!-- Placeholders settings -->
                <div role="tabpanel" class="tab-pane fade" id="placeholders-settings">

                    <div class="row">
                        <div class="col-md-12 m-b-16">
                            <small><?php _e('Placeholders can be understood as predefined ‘search and replace‘ templates; that will be replaced with some actual values at the execution time; usually this is used for MySQL queries, but you can use it for filtering and editing for manual tables and only filtering for tables created from XML, JSON, Excel, CSV, Google Spreadsheet and PHP Serialized array.', 'wpdatatables'); ?></small>
                        </div>
                    </div>

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %VAR1%
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with any value that you will provide in a shortcode. Provide a default value here that will be used for table generation and when a different one is not defined in the shortcode.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <input id="wdt-var1-placeholder" type="text" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %VAR2%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with any value that you will provide in a shortcode. Provide a default value here that will be used for table generation and when a different one is not defined in the shortcode.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group  m-b-0">
                                <input id="wdt-var2-placeholder" type="text" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %VAR3%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with any value that you will provide in a shortcode. Provide a default value here that will be used for table generation and when a different one is not defined in the shortcode.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <input id="wdt-var3-placeholder" type="text" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_USER_ID%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the ID of currently logged in user. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <input id="wdt-user-id-placeholder" type="text"
                                       value="<?php echo get_current_user_id(); ?>" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_USER_LOGIN%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the login of currently logged in user. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_user = wp_get_current_user(); ?>
                                <input id="wdt-user-login-placeholder" type="text"
                                       value="<?php echo $wdt_current_user->user_login; ?>"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_USER_EMAIL%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the Email of currently logged in user. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_user = wp_get_current_user(); ?>
                                <input id="wdt-user-email-placeholder" type="text"
                                       value="<?php echo $wdt_current_user->user_email; ?>"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_POST_ID%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the ID of current post. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <input id="wdt-post-id-placeholder" type="text" value="" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_USER_FIRST_NAME%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the First Name of currently logged in user. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_user = wp_get_current_user(); ?>
                                <input id="wdt-user-first-name-placeholder" type="text"
                                       value="<?php echo $wdt_current_user->first_name; ?>"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_USER_LAST_NAME%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the Last Name of currently logged in user. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_user = wp_get_current_user(); ?>
                                <input id="wdt-user-last-name-placeholder" type="text"
                                       value="<?php echo $wdt_current_user->last_name; ?>" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_DATE%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with current date.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_date = current_time(get_option('wdtDateFormat'));
                                ?>
                                <input id="wdt-date-placeholder" type="text"
                                       value="<?php echo $wdt_current_date ?>" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_DATETIME%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with current datetime.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_date = current_time(get_option('wdtDateFormat'));
                                $wdt_current_time = current_time(get_option('wdtTimeFormat'));
                                ?>
                                <input id="wdt-datetime-placeholder" type="text"
                                       value="<?php echo $wdt_current_date . ' ' . $wdt_current_time ?>"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %CURRENT_TIME%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with current time.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php $wdt_current_time = current_time(get_option('wdtTimeFormat'));
                                ?>
                                <input id="wdt-time-placeholder" type="text"
                                       value="<?php echo $wdt_current_time ?>" class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                %WPDB%
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('This placeholder will be replaced with the current prefix of WordPress database. Provide a value here to be used for table generation', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="fg-line form-group m-b-0">
                                <?php global $wpdb; ?>
                                <input id="wdt-wpdb-placeholder" type="text" value="<?php echo $wpdb->prefix; ?>"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Default for table generation', 'wpdatatables'); ?>">
                            </div>

                        </div>

                    </div>
                </div>
                <!-- /Placeholders settings -->

                <!-- Customize table settings -->
                <div role="tabpanel" class="tab-pane fade" id="customize-table-settings">

                    <div role="tabpanel">
                        <p style="font-size: 15px"><?php _e('Now you can customize each table with different skin, font, background , colors and lot more. Checkout new table customize settings below.', 'wpdatatables'); ?></p>
                        <ul class="tab-nav" role="tablist">
                            <li class="active main-customize-table-settings-tab">
                                <a href="#main-customize-table-settings" aria-controls="main-customize-table-settings"
                                   role="tab"
                                   data-toggle="tab"><?php _e('Main', 'wpdatatables'); ?></a>
                            </li>
                            <li class="font-settings-tab">
                                <a href="#font-settings" aria-controls="font-settings" role="tab"
                                   data-toggle="tab"><?php _e('Font', 'wpdatatables'); ?></a>
                            </li>
                            <li class="header-settings-tab">
                                <a href="#header-settings" aria-controls="header-settings" role="tab"
                                   data-toggle="tab"><?php _e('Header', 'wpdatatables'); ?></a>
                            </li>
                            <li class="table-border-settings-tab">
                                <a href="#table-border-settings" aria-controls="table-border-settings" role="tab"
                                   data-toggle="tab"><?php _e('Table border', 'wpdatatables'); ?></a>
                            </li>
                            <li class="row-color-settings-tab">
                                <a href="#row-color-settings" aria-controls="row-color-settings" role="tab"
                                   data-toggle="tab"><?php _e('Row color', 'wpdatatables'); ?></a>
                            </li>
                            <li class="cell-color-settings-tab">
                                <a href="#cell-color-settings" aria-controls="cell-color-settings" role="tab"
                                   data-toggle="tab"><?php _e('Cell color', 'wpdatatables'); ?></a>
                            </li>
                            <li class="pagination-settings-tab">
                                <a href="#pagination-settings" aria-controls="pagination-settings" role="tab"
                                   data-toggle="tab"><?php _e('Pagination', 'wpdatatables'); ?></a>
                            </li>
                            <li class="custom-css-settings-tab">
                                <a href="#custom-css-settings" aria-controls="custom-css-settings" role="tab"
                                   data-toggle="tab"><?php _e('Custom CSS', 'wpdatatables'); ?></a>
                            </li>


                            <?php do_action('wdt_add_customize_table_configuration_tab'); ?>

                        </ul>
                        <!-- /ul .tab-nav -->

                        <div class="tab-content">
                            <!-- Main table settings -->
                            <div role="tabpanel" class="tab-pane active" id="main-customize-table-settings">

                                <div class="row">
                                    <div class="col-sm-4 table-interface-language">
                                        <h4 class="c-title-color m-b-2">
                                            <?php _e('Interface language', 'wpdatatables'); ?>
                                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('Pick the language which will be used in tables interface.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select class="selectpicker" id="wdt-table-interface-language">
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
                                    <div class="col-sm-4 wdt-table-base-skin">
                                        <h4 class="c-title-color m-b-2">
                                            <?php _e('Base skin', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('Choose the base skin for the plugin.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select class="selectpicker" name="wdt-table-base-skin"
                                                            id="wdt-table-base-skin">
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
                                </div>

                            </div>
                            <!-- /Main table settings -->

                            <!-- Font settings -->
                            <div role="tabpanel" class="tab-pane fade" id="font-settings">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Font', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This font will be used in rendered tables. Leave blank not to override default theme settings', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select id="wdt-table-font" data-name="wdtTableFont"
                                                            class="selectpicker"
                                                            title="Choose font for the table">
                                                        <option value=""></option>
                                                        <?php foreach (WDTSettingsController::wdtGetSystemFonts() as $font) { ?>
                                                            <option value="<?php echo $font ?>"><?php echo $font ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Font size', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('Define the font size', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="fg-line wdt-custom-number-input">
                                                            <button type="button"
                                                                    class="btn btn-default wdt-btn-number wdt-button-minus"
                                                                    data-type="minus" data-field="wdt-table-font-size">
                                                                <i class="wpdt-icon-minus"></i>
                                                            </button>
                                                            <input type="text" name="wdt-table-font-size" min="8"
                                                                   value="" class="form-control cp-value input-sm input-number"
                                                                   data-name="wdtTableFontSize"
                                                                   id="wdt-table-font-size">
                                                            <button type="button"
                                                                    class="btn btn-default wdt-btn-number wdt-button-plus"
                                                                    data-type="plus" data-field="wdt-table-font-size">
                                                                <i class="wpdt-icon-plus-full"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Font color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the main font in table cells.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-font-color"
                                                               data-name="wdtTableFontColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Font settings -->

                            <!-- Header settings -->
                            <div role="tabpanel" class="tab-pane fade" id="header-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('The color is used for background of the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-base-color"
                                                               data-name="wdtTableHeaderBaseColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Border color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the border in the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-border-color"
                                                               data-name="wdtTableHeaderBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Font color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the font in the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-font-color"
                                                               data-name="wdtTableHeaderFontColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Active and hover color	', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used when you hover the mouse above the table header, or when you choose a column.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-active-color"
                                                               data-name="wdtTableHeaderActiveColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Header settings -->

                            <!-- Table border settings -->
                            <div role="tabpanel" class="tab-pane fade" id="table-border-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Inner border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the inner border in the table between cells.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-inner-border-color"
                                                               data-name="wdtTableInnerBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Outer border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the outer border of the whole table body.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-outer-border-color"
                                                               data-name="wdtTableOuterBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-remove-borders">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Table borders', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('When this is checked, borders in table will be removed ', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input type="checkbox" name="wdt-table-remove-borders"
                                                   id="wdt-table-remove-borders"/>
                                            <label for="wdt-table-remove-borders"
                                                   class="ts-label"><?php _e('Remove borders in table', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-remove-borders-header">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Header border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('When this is checked,borders in header will be removed  ', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input type="checkbox" name="wdt-table-remove-borders-header"
                                                   id="wdt-table-remove-borders-header"/>
                                            <label for="wdt-table-remove-borders-header"
                                                   class="ts-label"><?php _e('Remove borders in table header', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Table border settings -->

                            <!-- Row color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="row-color-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Even row background', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for for background in even rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-even-row-color"
                                                               data-name="wdtTableEvenRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Odd row background', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for for background in odd rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-odd-row-color"
                                                               data-name="wdtTableOddRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Hover row', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for to highlight the row when you hover your mouse above it.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-hover-row-color"
                                                               data-name="wdtTableHoverRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Background for selected rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for background in selected rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-selected-row-color"
                                                               data-name="wdtTableSelectedRowColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Row color settings -->

                            <!-- Cell color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="cell-color-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Sorted columns, even rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for background in cells which are in the active columns (columns used for sorting) in even rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-active-even-cell-color"
                                                               data-name="wdtTableActiveEvenCellColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Sorted columns, odd rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for background in cells which are in the active columns (columns used for sorting) in odd rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-active-odd-cell-color"
                                                               data-name="wdtTableActiveOddCellColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Cell color settings -->

                            <!-- Pagination color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="pagination-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the background of the pagination', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-background-color"
                                                               data-name="wdtTablePaginationBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the color of the links in the pagination.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-color"
                                                               data-name="wdtTablePaginationColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Current page background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('The color is used for background of the current page', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text"
                                                               id="wdt-table-pagination-current-background-color"
                                                               data-name="wdtTablePaginationCurrentBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Current page color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used for the color of the current page.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-current-color"
                                                               data-name="wdtTablePaginationCurrentColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Other pages hover background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This background color is used when you hover the mouse above the other pages', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text"
                                                               id="wdt-table-pagination-hover-background-color"
                                                               data-name="wdtTablePaginationHoverBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php _e('Other pages hover color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This color is used when you hover the mouse above the other pages.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-hover-color"
                                                               data-name="wdtTablePaginationHoverColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Pagination color settings -->

                            <!-- Custom CSS settings -->
                            <div role="tabpanel" class="tab-pane fade" id="custom-css-settings">
                                <div class="row">
                                    <div class="col-sm-6 custom-css">
                                        <h4 class="c-title-color m-b-2">
                                            <?php _e('Custom wpDataTables CSS', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php _e('This CSS will be inserted as an inline style block on every page that has this wpDataTable.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <pre id="wdt-table-custom-css" class="m-0" style="width: 100%; height: 300px"></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Custom CSS settings -->


                            <?php do_action('wdt_add_customize_table_configuration_tabpanel'); ?>

                        </div>
                        <!-- /.tab-content - end of table settings tabs -->

                    </div>

                </div>
                <!-- /Customize table settings -->

                <?php do_action('wdt_add_table_configuration_tabpanel'); ?>

            </div>
            <!-- /.tab-content - end of table settings tabs -->

            <div class="row m-l-0 m-r-0">

                <div class="col-md-12 p-l-0  wdt-table-settings-col">
                    <button class="btn btn-default btn-icon-text wdt-documentation wdt-table-settings"
                            data-doc-page="table_settings">
                        <i class="wpdt-icon-file-thin"></i><?php _e('View Documentation', 'wpdatatables'); ?>
                    </button>
                </div>
                <!-- /.col-md-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!--/div role="tabpanel" -->
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card /.wdt-table-settings -->
