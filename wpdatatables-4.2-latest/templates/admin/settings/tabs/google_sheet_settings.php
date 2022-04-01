<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
$googleSettings = get_option('wdtGoogleSettings');
?>

<div role="tabpanel" class="tab-pane" id="google-sheet-api-settings">
    <?php if (!$googleSettings) { ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-info alert-dismissible" role="alert">
                    <i class="wpdt-icon-info-circle-full"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true"><i
                                    class="wpdt-icon-times-full"></i></span></button>
                    <span class="wdt-alert-title f-600">
                       <?php esc_html_e('Detail instruction how to enable Google API\'s and create your service account you can find on this', 'wpdatatables'); ?>
                        <a href="https://wpdatatables.com/documentation/connect-wordpress-tables-with-google-sheets-api/" target="_blank"><?php esc_html_e('link', 'wpdatatables'); ?></a>.
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 wdt-service-acount">
                <h4 class="c-title-color m-b-2">
                    <?php esc_html_e('Google service account data.', 'wpdatatables'); ?>
                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php esc_attr_e('Here you will paste private key data from downloaded JSON file from Google service account.', 'wpdatatables'); ?>"></i>
                </h4>
                <div class="form-group">
                    <div class="fg-line">
                        <textarea class="form-control" name="wdt-google-sheet-settings" id="wdt-google-sheet-settings"
                                  rows="10"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <button id="wdt-save-google-settings"
                    class="btn m-l-15 btn-primary"><?php esc_html_e('Validate & Save', 'wpdatatables'); ?></button>
        </div>
    <?php } else { ?>

        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-info alert-dismissible" role="alert">
                    <i class="wpdt-icon-filled-check"></i>
                    <span class="wdt-alert-subtitle p-l-5 f-600"> <?php esc_html_e('Your Google service account:', 'wpdatatables'); ?></span>
                    <span class="wdt-service-account wdt-alert-title p-l-5"><?php echo esc_html($googleSettings['client_email']) ?></span>
                    <span class="wdt-alert-subtitle d-block">
                        <ul class="m-l-20" style="list-style: disc;">
                        <li class="m-t-15 m-b-0"><?php esc_html_e('Now all your already created, published and shared Google Spreadsheets that you are using in wpDataTables will be automatically synchronised and when you update data in your Google Sheet it will be instantly shown in wpDataTables as well. No more cache issues.', 'wpdatatables'); ?></li>
                         <li class="m-t-15 m-b-0"><?php esc_html_e('If you need to show data from Private Sheets (not published on the web or share it with everyone), please copy your Google service account and then share it with those Private Google spreadsheets that you will use in wpDataTables. You can check out detail instruction how to do that on this', 'wpdatatables'); ?>
                            <a href="https://wpdatatables.com/documentation/connect-wordpress-tables-with-google-sheets-api"
                               target="_blank"><?php esc_html_e('link', 'wpdatatables'); ?></a>.
                        </li>
                        </ul>
                    </span>
                </div>
            </div>
        </div>
        <button id="wdt-delete-google-settings"
                class="btn btn-danger"><?php esc_html_e('Remove account', 'wpdatatables'); ?></button>
    <?php } ?>
</div>

<!-- Error modal -->
<?php include WDT_TEMPLATE_PATH . 'admin/common/error_modal.inc.php'; ?>
<!-- /Error modal -->