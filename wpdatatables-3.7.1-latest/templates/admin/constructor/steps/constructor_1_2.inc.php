<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row wdt-constructor-step p-l-0 bg-white hidden" data-step="1-2">

    <div class="col-sm-6 input-path-block">

        <h4 class="c-title-color m-b-2 m-t-0">
            <?php _e('Input file path or URL', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title=""
               data-original-title="<?php _e('Upload your file or provide the full URL here. For CSV or Excel input sources only URLs or paths from same servers are supported. For Google Spreadsheets: please do not forget to publish the spreadsheet before pasting the URL.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group">
            <div class="fg-line col-sm-9 p-0">
                <input type="text" id="wdt-constructor-input-url" class="form-control input-sm"
                       placeholder="Paste URL or path, or click Browse to choose">
            </div>
            <div class="col-sm-3">
                <button class="btn bgm-blue" id="wdt-constructor-browse-button">
                    <?php _e('Browse...', 'wpdatatables'); ?>
                </button>
            </div>
        </div>

    </div>

</div>