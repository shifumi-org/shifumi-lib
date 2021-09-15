<?php defined('ABSPATH') or die('Access denied.'); ?>

<script type="text/x-template" id="wdt-column-conditional-formatting-template">

    <div class="row wdt-conditional-formatting-rule">
        <div class="col-sm-1">
            <label class="control-label"><?php _e('If cell value', 'wpdatatables'); ?></label>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <select class="formatting-rule-if-clause selectpicker">
                    <option value="lt" selected="selected">&lt;</option>
                    <option value="lteq">≤</option>
                    <option value="eq">=</option>
                    <option value="gteq">≥</option>
                    <option value="gt">&gt;</option>
                    <option value="neq">≠</option>
                    <option value="contains"><?php _e('contains', 'wpdatatable'); ?></option>
                    <option value="contains_not"><?php _e('does not contain', 'wpdatatable'); ?></option>
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <input placeholder="<?php _e('Choose criteria', 'wpdatatables'); ?>" type="text" class="form-control input-sm formatting-rule-cell-value" value="">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <select class="formatting-rule-action selectpicker">
                    <option value="setCellColor"
                            selected="selected"><?php _e('Set cell color', 'wpdatatables'); ?></option>
                    <option value="defaultCellColor"><?php _e('Reset cell color to default', 'wpdatatables'); ?></option>
                    <option value="setCellContent"><?php _e('Set cell content', 'wpdatatables'); ?></option>
                    <option value="setCellClass"><?php _e('Set cell CSS class', 'wpdatatables'); ?></option>
                    <option value="removeCellClass"><?php _e('Remove cell CSS class', 'wpdatatables'); ?></option>
                    <option value="setRowColor"><?php _e('Set row color', 'wpdatatables'); ?></option>
                    <option value="defaultRowColor"><?php _e('Reset row color to default', 'wpdatatables'); ?></option>
                    <option value="setRowClass"><?php _e('Set row CSS class', 'wpdatatables'); ?></option>
                    <option value="removeRowClass"><?php _e('Remove row CSS class', 'wpdatatables'); ?></option>
                    <option value="setColumnColor"><?php _e('Set column color', 'wpdatatables'); ?></option>
                    <option value="addColumnClass"><?php _e('Add column CSS class', 'wpdatatables'); ?></option>
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <input placeholder="<?php _e('Choose criteria', 'wpdatatables'); ?>"  type="text" class="form-control input-sm formatting-rule-set-value cp-inside" value="">
            </div>
        </div>
        <div class="col-sm-2 wdt-last">
            <button class="btn pull-right wdt-delete-conditional-formatting-rule"
                    title="<?php _e('Remove rule', 'wpdatatables'); ?>" data-toggle="tooltip"><i
                        class="wpdt-icon-trash"></i></button>
        </div>
    </div>

</script>
