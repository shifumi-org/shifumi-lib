<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .add-column-modal -->
<div class="modal fade" id="wdt-range-picker" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
     aria-hidden="true">

    <!-- .modal-dialog -->
    <div class="modal-dialog modal-lg">

        <!-- .modal-content -->
        <div class="modal-content">

            <!-- .modal-header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Pick Range', 'wpdatatable'); ?></h4>
            </div>
            <!--/ .modal-header -->

            <!-- .modal-body -->
            <div class="modal-body">
                <div id="pick-range-table-container"></div>
            </div>
            <!--/ .modal-body -->

            <!-- .modal-footer -->
            <div class="modal-footer">
                <hr>
                <button class="btn btn-icon-text" data-toggle="modal"
                        data-target="#wdt-range-picker" id="wdt-cancel-range">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button class="btn btn-primary btn-icon-text" id="submit-pick-range">
                    <i class="wpdt-icon-check-full"></i>
                    <?php esc_html_e('Confirm', 'wpdatatables'); ?>
                </button>
            </div>
            <!--/ .modal-footer -->
        </div>
        <!--/ .modal-content -->
    </div>
    <!--/ .modal-dialog -->
</div>
<!--/ .add-column-modal -->