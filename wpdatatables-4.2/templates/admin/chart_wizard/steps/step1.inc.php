<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row">

    <!-- .col-sm-4 -->
    <div class="col-sm-6 col-md-6 chart-name">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Chart name', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Please define the title of the chart that you will use to identify it', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <div class="row">
                    <div class="col-sm-12">
                        <input type="text" name="chart-name" id="chart-name" class="form-control input-sm"
                               value="<?php echo empty($chartId) ? esc_attr__('New wpDataTable Chart', 'wpdatatables') : esc_attr($chartObj->getTitle()); ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col-sm-4 -->

    <!-- .col-sm-4 -->
    <div class="col-sm-6 col-md-6 render-engine">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Chart render engine', 'wpdatatables'); ?>

            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Please choose the render engine.', 'wpdatatables'); ?> <?php esc_attr_e(' Please note that HighCharts license from verion 3.0.4 IS included with wpDataTables and you DON\'T NEED to purchase the license separately on', 'wpdatatables'); ?> https://highcharts.com"></i>
        </h4>
        <div class="form-group col-sm-6 col-md-6">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" name="chart-render-engine" id="chart-render-engine">
                        <option value="" <?php echo empty($chartId) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Pick the render engine', 'wpdatatables'); ?></option>
                        <option value="google"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'google')){ ?>selected="selected"<?php } ?> >
                            Google Charts
                        </option>
                        <option value="highcharts"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'highcharts')){ ?>selected="selected"<?php } ?> >
                            HighCharts
                        </option>
                        <option value="chartjs"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'chartjs')){ ?>selected="selected"<?php } ?> >
                            Chart.js
                        </option>
                        <option value="apexcharts"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'apexcharts')){ ?>selected="selected"<?php } ?> >
                            ApexCharts
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col-sm-4 -->

</div>
<!--/.row -->

<!-- .row -->
<div class="row">

    <!-- div.google-charts-type -->
    <div class="charts-type google-charts-type col-sm-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/google_charts.inc.php'; ?>

    </div>
    <!-- /div.google-charts-type -->

    <!-- div.highcharts-charts-type -->
    <div class="charts-type highcharts-charts-type col-sm-12 col-md-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/highcharts.inc.php'; ?>

    </div>
    <!-- /div.highcharts-charts-type -->

    <!-- div.chartjs-charts-type -->
    <div class="charts-type chartjs-charts-type col-sm-12 col-md-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/chartjs.inc.php'; ?>

    </div>
    <!-- /div.chartjs-charts-type -->

    <!-- div.apexcharts-charts-type -->
    <div class="charts-type apexcharts-charts-type col-sm-12 col-md-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/apexcharts.inc.php'; ?>

    </div>
    <!-- /div.apexcharts-charts-type -->

</div>
<!--/.row -->
