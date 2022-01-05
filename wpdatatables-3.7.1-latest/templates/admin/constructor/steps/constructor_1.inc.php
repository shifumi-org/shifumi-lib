<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row wdt-constructor-step bg-white" data-step="1">
    <h4 class="c-title-color m-b-20 m-t-0 p-l-0 f-15">
        <?php _e('Choose what kind of table would you like to construct', 'wpdatatables'); ?>
    </h4>

    <?php if (Connection::enabledSeparate()) { ?>
    <div class="col-sm-2 p-l-0">
        <!-- Separate connection -->
        <h4 class="c-title-color m-b-2 f-15">
            <?php _e('Connection', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Use separate connection', 'wpdatatables'); ?>"></i>
        </h4>
        <!-- input source type selection -->
        <div class="form-group">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" id="wdt-constructor-table-connection">
                        <option value="">WP Connection</option>
                        <?php foreach (Connection::getAll() as $key => $wdtSeparateConnection) { ?>
                            <option data-vendor='<?php echo $wdtSeparateConnection['vendor'] ?>' value="<?php echo $wdtSeparateConnection['id'] ?>" <?php echo $wdtSeparateConnection['default'] ? 'selected' : '' ?>><?php echo $wdtSeparateConnection['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <!-- /Server side processing toggle -->
    </div>
    <?php
    } else {
    ?>
        <input type="hidden" id="wdt-constructor-table-connection" value="">
    <?php
    }
    ?>

    <div class="col-sm-12 p-0">

        <div class="row wpdt-flex wdt-first-row">
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="simple">
                    <div class="ribbon"><span>NEW</span></div>
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo WDT_ASSETS_PATH ?>img/constructor/create-simple-table.svg">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Create a simple table from scratch', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Create a simple table with any data, merged cells, styling, star rating and a lot more.<br> You get full control of formatting, but no sorting, filtering, pagination or database connection like in data tables.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="source">
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo WDT_ASSETS_PATH ?>img/constructor/add-from-data-source.png">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Create a data table linked to an existing data source', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Excel, CSV, Google Spreadsheet, SQL query, XML, JSON, serialized PHP array. Data will be read from the source every time on page load. Only SQL-based tables can be made editable.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="row wpdt-flex wdt-second-row">
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="manual">
                    <div class="card-header">
                        <img class="img-responsive" src="<?php echo WDT_ASSETS_PATH ?>img/constructor/manual.png">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Create a data table manually', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Define the number and type of columns, and fill in the data manually in WP admin. Table will be stored in the database and can be edited from WP admin, or made front-end editable.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="file">
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo WDT_ASSETS_PATH ?>img/constructor/import-data-from-data-source.png">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Create a data table by importing data from a data source', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Excel, CSV, Google Spreadsheet. Data will be imported to the database, the table can be edited in WP admin, or made front-end editable.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="row wpdt-flex wdt-third-row">
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="wp">
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo WDT_ASSETS_PATH ?>img/constructor/generate-query-to-wordpress-database.png">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Generate a query to the WordPress database', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Create a MySQL-query-based data table by generating a query to the WordPress database (posts, taxonomies, postmeta) with a GUI tool.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>
            <div class="wdt-constructor-type-selecter-block col-sm-6">
                <div class="card" data-value="mysql">
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo WDT_ASSETS_PATH ?>img/constructor/generate-query-to-mysql-database.png">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14"><?php _e('Generate a query to the MySQL database', 'wpdatatables'); ?>.</h4>
                        <span><?php _e('Create a SQL-query-based data table by generating a query to any custom SQL database with a GUI tool.', 'wpdatatables'); ?></span>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>