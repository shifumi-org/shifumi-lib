<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php if (isset($chartObj)) { ?>
    <script type='text/javascript'>var editing_chart_data = {
            render_data: <?php echo json_encode($chartObj->getRenderData()); ?>,
            highcharts_render_data: <?php echo json_encode($chartObj->getHighchartsRenderData()); ?>,
            chartjs_render_data: <?php echo json_encode($chartObj->getChartJSRenderData()); ?>,
            apexcharts_render_data: <?php echo json_encode($chartObj->getApexchartsRenderData()); ?>,
            engine: "<?php echo esc_html($chartObj->getEngine());?>",
            type: "<?php echo esc_html($chartObj->getType()); ?>",
            selected_columns: <?php echo json_encode($chartObj->getSelectedColumns()) ?>,
            range_type: "<?php echo esc_html(($chartObj->getRangeType())) ?>"<?php if( $chartObj->getRangeType() == 'picked_range' ){ ?>,
            row_range: <?php echo json_encode($chartObj->getRowRange()); } ?>,
            title: "<?php echo esc_html($chartObj->getTitle()); ?>",
            follow_filtering: <?php echo (int)$chartObj->getFollowFiltering(); ?>,
            wpdatatable_id: <?php echo (int)$chartObj->getwpDataTableId(); ?>  };</script>
<?php } ?>

<div class="wrap wdt-datatables-admin-wrap">

    <!-- .container -->
    <div class="container">

        <!-- .row -->
        <div class="row">

            <div class="card wdt-chart-wizard">

                <!-- Preloader -->
                <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
                <!-- /Preloader -->

                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
                    <h2>
                        <span style="display: none"><?php esc_html_e('Create a Chart', 'wpdatatables'); ?></span>
                        <?php esc_html_e('Create a Chart', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions p-t-5">
                        <li>
                            <button class="btn wdt-backend-chart-close">
                                <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body card-padding" id="wdt-chart-wizard-body">
                    <?php wp_nonce_field('wdtChartWizardNonce', 'wdtNonce'); ?>
                    <input type="hidden" id="wp-data-chart-id" value="<?php if(isset($chartId)) echo esc_attr($chartId) ?>"/>
                    <input type="hidden" id="wdt-browse-charts-url"
                           value="<?php echo admin_url('admin.php?page=wpdatatables-charts'); ?>"/>

                    <ol class="breadcrumb chart-wizard-breadcrumb">
                        <li class="chart_wizard_breadcrumbs_block  step1 active"
                            id="step1"><?php esc_html_e('Chart title & type', 'wpdatatables'); ?></li>
                        <li class="chart_wizard_breadcrumbs_block  step2"
                            id="step2"><?php esc_html_e('Data source', 'wpdatatables'); ?></li>
                        <li class="chart_wizard_breadcrumbs_block  step3"
                            id="step3"><?php esc_html_e('Data range', 'wpdatatables'); ?></li>
                        <li class="chart_wizard_breadcrumbs_block  step4"
                            id="step4"><?php esc_html_e('Formatting and preview', 'wpdatatables'); ?></li>
                        <li class="chart_wizard_breadcrumbs_block  step5"
                            id="step5"><?php esc_html_e('Save and get shortcode', 'wpdatatables'); ?></li>
                    </ol>

                    <div class="steps m-t-20">

                        <div class="chart-wizard-step step1" data-step="step1">

                            <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/step1.inc.php'; ?>

                        </div>

                        <div class="chart-wizard-step step2" data-step="step2" style="display: none">

                            <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/step2.inc.php'; ?>

                        </div>

                        <div class="chart-wizard-step step3" data-step="step3" style="display: none">

                            <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/step3.inc.php'; ?>

                        </div>

                        <div class="chart-wizard-step step4" data-step="step4" style="display: none">

                            <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/step4.inc.php'; ?>

                        </div>

                        <div class="chart-wizard-step step5" data-step="step5" style="display: none">

                            <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/step5.inc.php'; ?>

                        </div>

                    </div>

                </div>
                <div class="row m-t-15 m-b-5 p-l-15 p-r-15">
                    <button class="btn btn-primary btn-icon-text pull-right m-l-5"
                            style="display:none;" id="finishButton">
                        <?php esc_html_e('Browse charts', 'wpdatatables'); ?>
                    </button>
                    <button class="btn btn-primary btn-icon-text pull-right m-l-5"
                            disabled="disabled"
                            id="wdt-chart-wizard-next-step"><?php esc_html_e('Next ', 'wpdatatables'); ?></button>
                    <button class="btn btn-icon-text pull-right hidden" disabled="disabled"
                            id="wdt-chart-wizard-previous-step"><?php esc_html_e(' Previous', 'wpdatatables'); ?></button>
                    <a class="btn btn-default btn-icon-text wdt-documentation"
                       data-doc-page="chart_wizard">
                        <i class="wpdt-icon-file-thin"></i> <?php esc_html_e(' View Documentation', 'wpdatatables'); ?>
                    </a></div>

            </div>

        </div>
        <!-- /.row -->
    </div>
    <!-- /.container -->

    <!-- Range picker modal -->
    <?php include_once WDT_TEMPLATE_PATH . '/admin/chart_wizard/range_picker_modal.inc.php'; ?>
    <!-- /Range picker modal -->

    <!-- Close modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/close_modal.inc.php'; ?>
    <!-- /Close modal -->

</div>

<script id="wdt-chart-series-setting-block" type="text/x-jsrender">
    {{for series}}
        <div class="chart-series-block" data-orig_header="{{>orig_header}}">
            <h4 class="c-title-color m-b-4 title">
                    <?php esc_html_e('Serie', 'wpdatatables'); ?>: {{>label}}
            </h4>
            <div class="chart-series-label">
                <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Label', 'wpdatatables'); ?>
                </h4>
                <div class="form-group">
                    <div class="fg-line">
                        <div class="row">
                            <div class="col-sm-12">
                                <input type="text" name="font-name" id="series-label-{{:#index}}" value="{{>label}}" class="form-control input-sm series-label" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chart-series-color" id="chart-series-color">
                 <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Color', 'wpdatatables'); ?>
                </h4>
                <div class="cp-container">
                    <div class="form-group">
                        <div class="fg-line dropdown">
                            <div id="cp" class="input-group wdt-color-picker"">
                                <input type="text" id="series-color-{{:#index}}" value="" class="form-control cp-value series-color wdt-add-picker" />
                                <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chart-series-type google highcharts chartjs" id="chart-series-type">
                 <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Type', 'wpdatatables'); ?>
                </h4>
                <div class="cp-container">
                    <div class="form-group">
                         <div class="fg-line">
                              <div class="select">
                                  <select class="selectpicker" name="series-type" id="series-type">
                                      <option selected="selected" value=""></option>
                                      <option value="line">Line</option>
                                      <option value="spline">Spline</option>
                                      <option value="column">Column</option>
                                      <option value="bar">Bar</option>
                                      <option value="area">Area</option>
                                  </select>
                              </div>
                         </div>
                    </div>
                </div>
            </div>
            <div class="apexcharts apex-series-type-container" id="apexchart-series-type">
                <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Type', 'wpdatatables'); ?>
                </h4>
                <div class="cp-container">
                    <div class="form-group">
                         <div class="fg-line">
                              <div class="select">
                                  <select class="selectpicker apex-series-type" name="apex-series-type" id="apex-series-type-{{:#index}}">
                                      <option selected="selected" value=""></option>
                                      <option value="line">Line</option>
                                      <option value="bar">Column</option>
                                      <option value="area">Area</option>
                                  </select>
                              </div>
                         </div>
                    </div>
                </div>
            </div>
            <div class="apexcharts chart-series-image doNotTriggerChange" id="series-image-{{:#index}}-container">
                <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Chart line/area image', 'wpdatatables'); ?>
                </h4>
                <div class="form-group">
                    <div class="fg-line">
                        <div class="row">
                            <div class="col-sm-12" style="display: flex">
                                <input type="text" name="font-name" id="series-image-{{:#index}}" value="" class="form-control input-sm series-image" placeHolder="Upload an image or paste URL" />
                                <button id="wdt-upload-chart-image-{{:#index}}" class="wdt-series-image-button"><span class="wpdt-icon-image"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chart-show-yaxis">
                 <h4 class="c-title-color m-b-4">
                    <?php esc_html_e('Vertical axis', 'wpdatatables'); ?>
                 </h4>
                  <div class="toggle-switch p-b-16" data-ts-color="blue">
                      <input class="show-yaxis" id="show-yaxis-{{:#index}}" type="checkbox">
                      <label for="show-yaxis-{{:#index}}"><?php esc_html_e('Show vertical axis', 'wpdatatables'); ?></label>
                  </div>
            </div>
        </div>
    {{/for}}

</script>

<script id="range-picker-block" type="text/x-jsrender">
    <table class="range-picker-table table">
         <thead>
            <tr>
               <th>
               </th>
               {{for columnHeaders}}
                <th data-column_header="{{:header}}" data-column_id={{:id}}>
                    {{:header}}<br/>
                    <span class="checkbox">
                        <input type="checkbox" class="pick-column-range" {{if checked}}checked="checked"{{/if}} />
                    </span>
                </th>
               {{/for}}
            </tr>
         </thead>
         <tbody>
            {{for tableData}}
            <tr data-index={{:#index}}>
                <td class="pick-row">
                    <span class="checkbox">
                        <input type="checkbox" class="add-row-to-range" {{if rowChecked}}checked="checked"{{/if}}/>
                    </span>
                </td>
                {{props :}}
                <td data-column_header="{{>key}}">{{>prop}}</td>
                {{/props}}
            </tr>
            {{/for}}
        </tbody>
    </table>

</script>
