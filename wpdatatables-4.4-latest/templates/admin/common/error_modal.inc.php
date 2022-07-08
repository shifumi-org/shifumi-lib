<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Error messages popup/modal
 * @author Alexander Gilmanov
 * @since 01.01.2017
 */
?>
<!-- #wdt-error-modal -->
<div class="modal fade in" data-modal-color="red" id="wdt-error-modal" data-backdrop="static" data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true"><i class="wpdt-icon-times-full"></i></span></button>
                <h4 class="modal-title"><i class="wpdt-icon-exclamation-triangle"></i><?php esc_html_e('Error', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <hr>
                <button type="button" class="btn btn-link"
                        data-dismiss="modal"><?php esc_html_e('Close', 'wpdatatables'); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- /#wdt-error-modal -->