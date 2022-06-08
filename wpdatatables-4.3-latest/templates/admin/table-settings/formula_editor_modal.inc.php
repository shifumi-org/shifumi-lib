<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Formula Editor poopup/modal
 * @author Alexander Gilmanov
 * @since 04.11.2016
 */
?>
<!-- #wdtFormulaEditorModal -->
<div class="modal fade" id="wdt-formula-editor-modal" data-backdrop="static" data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Preloader -->
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
            <!-- /Preloader -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Formula Editor', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-dismissible" role="alert">
                    <i class=" wpdt-icon-info-circle-full"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true"><i class="wpdt-icon-times-full"></i></span></button>
                    <span class="wdt-alert-title"><?php esc_html_e('Use this dialog to construct formulas and see a preview of the result.', 'wpdatatables'); ?>
                        <br></span>
                    <span class="wdt-alert-subtitle"><?php esc_html_e('You can use columns (values for each cell will be inserted), or number values. Only numeric columns allowed (non-numeric will be parsed as 0). Basic math operations and brackets are supported. Example: col1*((col2+2)-col3*sin(col4-3)).', 'wpdatatables'); ?></span>
                </div>
                <div class="row">
                    <div class="col-md-12 formula_col">
                        <p class="title"><?php esc_html_e('Formula', 'wpdatatables'); ?></p>
                        <div class="form-group">
                            <div class="fg-line">
                                <textarea class="form-control" rows="5"
                                          placeholder="<?php esc_attr_e('Type your formula here...', 'wpdatatables'); ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 formula-columns">
                        <p class="title"><?php esc_html_e('Columns to use', 'wpdatatables'); ?></p>
                        <div class="formula-columns-container">
                            <!-- Columns will be added here -->
                        </div>
                    </div>
                    <div class="col-md-5 formula-operators">
                     <p class="title"><?php esc_html_e('Math operators', 'wpdatatables'); ?></p>
                    <div class="wdt-formula-operators">
                        <button class="btn formula_plus">+</button>
                        <button class="btn formula_minus">-</button>
                        <button class="btn formula_mult">*</button>
                        <button class="btn formula_mult formula_div">/</button>
                        <button class="btn formula_mult formula_brackets">()</button>
                        <button class="btn formula_mult formula_sin">sin()</button>
                        <button class="btn formula_mult formula_cos">cos()</button>
                        <button class="btn formula_mult formula_tan">tan()</button>
                        <button class="btn formula_mult formula_tan">cot()</button>
                        <button class="btn formula_mult formula_sec">sec()</button>
                        <button class="btn formula_mult formula_csc">csc()</button>
                    </div>
            </div>
                </div>
                <!--/.row-->

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info hidden wdt-formula-result-preview" role="alert"></div>
                    </div>
                </div>
                <!--/.row-->
            </div>
            <div class="modal-footer">
                <hr>
                <button type="button" class="btn pull-left wdt-preview-formula">
                    <i class="wpdt-icon-eye-full"></i>
                    <?php esc_html_e('Preview', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-danger btn-icon-text" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-icon-text wdt-save-formula">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
<!-- /#wdtFormulaEditorModal -->