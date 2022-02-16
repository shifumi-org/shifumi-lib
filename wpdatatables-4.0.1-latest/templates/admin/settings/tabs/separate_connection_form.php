<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * User: Miljko Milosevic
 * Date: 1/20/17
 * Time: 1:08 PM
 */
?>

<div role="tabpanel" class="tab-pane <?php if(($isDefault && $allDefaultOptions[$key]) || (!$isDefault && $key== 0)) echo 'active'; ?> separate-connection" id="connection<?php echo $key; ?>">
    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Connection Name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Connection Name.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-name"
                       placeholder="<?php _e('Connection Name', 'wpdatatables'); ?>" value="<?php echo $wdtSeparateConnection['name']; ?>">
            </div>
        </div>
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Test connection', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle wdt-my-sql-test" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Click this button to test if wpDataTables is able to connect to the DB server with the details you provided.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <button class="btn btn-primary wdt-my-sql-test">Test DB settings</button>
            </div>
        </div>

        <input type="hidden" name="wdt-my-sql-id" value="<?php echo $wdtSeparateConnection['id']; ?>"/>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Vendor', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the vendor.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <div class="select select-vendor">
                    <select class="selectpicker wdt-my-sql-vendor" name="wdt-my-sql-vendor">
                        <option value="" disabled></option>
                        <option <?php if($wdtSeparateConnection['vendor'] === "mysql") echo 'selected'; ?> value="mysql"><?php _e('MySQL', 'wpdatatables'); ?></option>
                        <option <?php if($wdtSeparateConnection['vendor'] === "mssql") echo 'selected'; ?> value="mssql"><?php _e('MSSQL', 'wpdatatables'); ?></option>
                        <option <?php if($wdtSeparateConnection['vendor'] === "postgresql") echo 'selected'; ?> value="postgresql"><?php _e('PostgreSQL', 'wpdatatables'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Database name.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-db"
                       placeholder="<?php _e('Database name', 'wpdatatables'); ?>" value="<?php echo $wdtSeparateConnection['database']; ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Host', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Host address.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-host"
                       placeholder="<?php _e('Host address', 'wpdatatables'); ?>" value="<?php echo $wdtSeparateConnection['host']; ?>">
            </div>
        </div>
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Port', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle connection-port" data-toggle="tooltip" data-placement="right"
                   title="<?php
                   $defaultPort = '';
                   if($wdtSeparateConnection['vendor'] === "mysql")
                       $defaultPort = '3306';
                   elseif($wdtSeparateConnection['vendor'] === "mssql")
                       $defaultPort = '1433';
                   elseif($wdtSeparateConnection['vendor'] === "postgresql")
                       $defaultPort = '5432';

                   _e('Port for the connection' . ($defaultPort ? ' (default: ' . $defaultPort . ').' : ''), 'wpdatatables');
                   ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-port"
                       placeholder="<?php _e('Port', 'wpdatatables'); ?>" value="<?php echo $wdtSeparateConnection['port']; ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('User', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Username for the connection.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="fg-line">
                <input type="text" class="form-control" name="wdt-my-sql-user"
                       placeholder="<?php _e('User', 'wpdatatables'); ?>" value="<?php echo $wdtSeparateConnection['user']; ?>">
            </div>
        </div>
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Password', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Password for the provided user.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <input type="password" class="form-control" placeholder="<?php _e('Password', 'wpdatatables'); ?>"
                       value="<?php echo $wdtSeparateConnection['password']; ?>" name="wdtMySqlPwd">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Driver', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Pick the driver.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <div class="select select-driver">
                    <select class="selectpicker wdt-sql-driver" name="wdt-sql-driver">
                        <option value="" disabled></option>
                        <option <?php if(isset($wdtSeparateConnection['driver']) && $wdtSeparateConnection['driver'] === "dblib") echo 'selected'; ?> value="dblib"><?php _e('DBLIB', 'wpdatatables'); ?></option>
                        <option <?php if(isset($wdtSeparateConnection['driver']) && $wdtSeparateConnection['driver'] === "sqlsrv") echo 'selected'; ?> value="sqlsrv"><?php _e('SQLSRV', 'wpdatatables'); ?></option>
                        <option <?php if(isset($wdtSeparateConnection['driver']) && $wdtSeparateConnection['driver'] === "odbc") echo 'selected'; ?> value="odbc"><?php _e('ODBC', 'wpdatatables'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Default Connection', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Set this connection as default.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-my-sql-default<?php echo $key; ?>" <?php if($wdtSeparateConnection['default']) echo 'checked'; ?> type="checkbox" class="wdt-my-sql-default-checkbox">
                <label for="wdt-my-sql-default<?php echo $key; ?>"
                       class="ts-label wdt-my-sql-default-label"><?php _e('Set this connection as default', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-6 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php _e('Delete', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Delete this connection.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <button type="button" class="btn btn-danger btn-icon-text wdt-my-sql-delete">
                    <i class="wpdt-icon-trash"></i> <?php _e('Delete', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>