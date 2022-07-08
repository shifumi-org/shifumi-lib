<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .wdt-remove-column-modal -->
<div class="modal fade" id="wdt-remove-column-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">

    <!-- .modal-dialog -->
    <div class="modal-dialog">

        <!-- .modal-content -->
        <div class="modal-content">

            <!-- Preloader -->
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
            <!-- /Preloader -->

            <!-- .modal-header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Remove Column', 'wpdatatable'); ?></h4>
            </div>
            <!--/ .modal-header -->

            <!-- .modal-body -->
            <div class="modal-body">

                <div class="wdt-remove-column-modal-block">

                    <div class="row">

                        <!-- .form-group -->
                        <div class="form-group col-xs-12">
                            <label for="wdtDeleteColumnSelect"
                                   class="col-sm-12 p-l-0 c-title-color f-500 p-r-0 control-label"><?php esc_html_e('Remove Column', 'wpdatatable'); ?></label>
                            <div class="col-sm-12 p-l-0 p-r-0">
                                <div class="fg-line">
                                    <div class="select">
                                        <select id="wdtDeleteColumnSelect" class="selectpicker">
                                            <?php if (isset($tableData)){
                                                foreach ($tableData->table->columns as $column) { ?>
                                                <?php if ($column->orig_header == 'wdt_ID' || $column->orig_header == 'wdt_id') {
                                                    continue;
                                                } ?>
                                                <option value="<?php echo esc_attr($column->orig_header); ?>"><?php echo esc_html($column->display_header); ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ .form-group -->

                        <!-- .form-group -->
                        <div class="form-group col-xs-12 wdt-remove-column-confirmation">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Confirm', 'wpdatatables'); ?>
                            </h4>
                            <div class="toggle-switch" data-ts-color="blue">
                                <input type="checkbox" id="wdt-remove-column-confirm" name="wdt-remove-column-confirm"
                                       value="" title="">

                                <label for="wdt-remove-column-confirm" class="control-label">
                                    <?php esc_html_e('Are you sure? There is no undo!', 'wpdatatables'); ?>
                                </label>
                            </div>
                        </div>
                        <!--/ .form-group -->
                    </div>

                </div>

            </div>
            <!--/ .modal-body -->

            <!-- .modal-footer -->
            <div class="modal-footer">
                <hr>
                <button class="btn btn-danger btn-icon-text" data-toggle="modal"
                        data-target="#wdt-remove-column-modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button class="btn btn-icon-text" id="wdt-remove-column-submit">
                    <i class="wpdt-icon-trash"></i>
                    <?php esc_html_e('Remove', 'wpdatatables'); ?>
                </button>
            </div>
            <!--/ .modal-footer -->
        </div>
        <!--/ .modal-content -->
    </div>
    <!--/ .modal-dialog -->
</div>
<!--/ .wdt-remove-column-modal -->
