<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * User: Miljko Milosevic
 * Date: 1/20/17
 * Time: 1:08 PM
 */
?>

<div role="tabpanel" class="tab-pane" id="separate-mysql-connection">
    <div class="row">
        <div class="col-sm-6 col-md-6 separate-connection">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Separate MySQL connection', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('If this checkbox is checked, wpDataTables will use its own connection to MySQL bases. In other case it will use the main WordPress MySQL connection.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch p-b-16" data-ts-color="blue">
                <input id="wdt-separate-connection" type="checkbox">
                <label for="wdt-separate-connection"
                       class="ts-label"><?php _e('Use separate MySQL connection', 'wpdatatables'); ?></label>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 hidden mysql-serverside-settings-block">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Test connection', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Click this button to test if wpDataTables is able to connect to the MySQL server with the details you provided.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <button class="btn btn-primary" id="wp-my-sql-test">Test MySQL settings</button>
            </div>
        </div>
    </div>

    <div class="row hidden mysql-serverside-settings-block">
        <div class="col-sm-6 col-md-6">
            <h4 class="c-title-color m-b-2 m-t-20">
                <?php esc_html_e('MySQL host', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('MySQL host address.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-host" id="wdt-my-sql-host"
                       placeholder="<?php _e('MySQL host address', 'wpdatatables'); ?>" value="">
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <h4 class="c-title-color m-b-2 m-t-20">
                <?php esc_html_e('MySQL database', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('MySQL database name.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-db" id="wdt-my-sql-db"
                       placeholder="<?php _e('MySQL database name', 'wpdatatables'); ?>" value="">
            </div>
        </div>
    </div>

    <div class="row hidden mysql-serverside-settings-block">
        <div class="col-sm-6 col-md-6">
            <h4 class="c-title-color m-b-2 m-t-20">
                <?php esc_html_e('MySQL user', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('MySQL username for the connection.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-user" id="wdt-my-sql-user"
                       placeholder="<?php _e('MySQL user', 'wpdatatables'); ?>" value="">
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <h4 class="c-title-color m-b-2 m-t-20">
                <?php esc_html_e('MySQL password', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('MySQL password for the provided user.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="password" class="form-control" placeholder="<?php _e('MySQL password', 'wpdatatables'); ?>"
                       value="" name="wdtMySqlPwd" id="wdtMySqlPwd">
            </div>
        </div>
    </div>

    <div class="row hidden mysql-serverside-settings-block">
        <div class="col-sm-6 col-md-6">
            <h4 class="c-title-color m-b-2 m-t-20">
                <?php esc_html_e('MySQL port', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('MySQL port for the connection (default: 3306).', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-port" id="wdt-my-sql-port"
                       placeholder="<?php _e('MySQL port', 'wpdatatables'); ?>" value="">
            </div>
        </div>
    </div>
</div>
