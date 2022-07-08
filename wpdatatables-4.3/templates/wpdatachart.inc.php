<?php defined('ABSPATH') or die('Access denied.'); ?>

<script type="text/javascript">if(typeof(wpDataCharts)=='undefined'){wpDataCharts = {};}; wpDataCharts[<?php echo (int)$chart_id; ?>] = {render_data: <?php echo $json_chart_render_data; ?>, engine: "<?php echo esc_html($this->_engine);?>", type: "<?php echo esc_html($this->_type); ?>", title: "<?php echo esc_html($this->_title); ?>", container: "wpDataChart_<?php echo (int)$chart_id?>", follow_filtering: <?php echo (int)$this->_follow_filtering; ?>, wpdatatable_id: <?php echo (int)$this->_wpdatatable_id ?>, group_chart: <?php echo esc_html($this->_group_chart) ?>}</script>

<?php if ($this->_engine == 'google' || $this->_engine == 'highcharts' || $this->_engine == 'apexcharts' ) : ?>
    <div id="wpDataChart_<?php echo (int)$chart_id?>" class="<?php echo esc_attr($this->_type)?>" style="width: 100%"></div>
<?php elseif ($this->_engine == 'chartjs') : ?>
    <div id="chartJSContainer_<?php echo (int)$chart_id?>">
        <canvas id="chartJSCanvas_<?php echo (int)$chart_id?>"></canvas>
    </div>
<?php endif; ?>