<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Chart engine of wpDataTables plugin
 */
class WPDataChart {

    private $_id = NULL;
    private $_wpdatatable_id = NULL;
    private $_engine = '';
    private $_type = '';
    private $_range_type = 'all';
    private $_selected_columns = array();
    private $_row_range = array();
    private $_follow_filtering = false;
    private $_wpdatatable = NULL;
    // Chart
    private $_width = 400;
    private $_responsiveWidth = false;
    private $_height = 400;
    private $_group_chart = false;
    private $_enable_animation = false;
    private $_show_data_labels = false;
    private $_start_angle = 0;
    private $_end_angle = 360;
    private $_background_color = '#FFFFFF';
    private $_border_width = 0;
    private $_border_color = '#4572A7';
    private $_border_radius = 0;
    private $_zoom_type = 'undefined';
    private $_panning = false;
    private $_pan_key = 'shift';
    private $_plot_background_color = 'undefined';
    private $_plot_background_image = 'undefined';
    private $_line_background_image = array();
    private $_plot_border_width = 0;
    private $_plot_border_color = '#C0C0C0';
    private $_font_size = NULL;
    private $_font_name = 'Arial';
    private $_font_style = 'normal';
    private $_font_color = '#666';
    private $_three_d = false;
    private $_monochrome = false;
    private $_monochrome_color = '#255aee';
    private $_enable_color_palette = false;
    private $_color_palette = 'palette1';
    private $_enable_dropshadow = false;
    private $_dropshadow_blur = 3;
    private $_dropshadow_opacity = 35;
    private $_dropshadow_color = '#000000';
    private $_dropshadow_top = 5;
    private $_dropshadow_left = 5;
    private $_text_color = '#373d3f';
    // Series
    private $_series = array();
    private $_curve_type = 'none';
    private $_series_type = '';
    // Axes
    private $_show_grid = true;
    private $_grid_color = '#000000';
    private $_grid_stroke = 1;
    private $_grid_position = 'back';
    private $_grid_axes = array();
    private $_highcharts_line_dash_style = 'Solid';
    private $_horizontal_axis_crosshair = false;
    private $_horizontal_axis_direction = 1;
    private $_vertical_axis_crosshair = false;
    private $_vertical_axis_direction = 1;
    private $_marker_size = 0;
    private $_stroke_width = 2;
    private $_vertical_axis_min;
    private $_vertical_axis_max;
    private $_tick_amount;
    private $_axes = array(
        'major' => array(
            'label' => ''
        ),
        'minor' => array(
            'label' => ''
        )
    );
    private $_inverted = false;
    private $_reversed = false;
    // Title
    private $_title = '';
    private $_show_title = true;
    private $_title_floating = false;
    private $_title_align = 'center';
    private $_title_position = 'top';
    private $_title_font_name = 'Arial';
    private $_title_font_style = 'bold';
    private $_title_font_color = '#666';
    private $_subtitle = 'undefined';
    private $_subtitle_align = 'center';
    // Tooltip
    private $_tooltip_enabled = true;
    private $_tooltip_background_color = 'rgba(255, 255, 255, 0.85)';
    private $_tooltip_border_width = 1;
    private $_tooltip_border_color = null;
    private $_tooltip_border_radius = 3;
    private $_tooltip_shared = false;
    private $_tooltip_value_prefix = 'undefined';
    private $_tooltip_value_suffix = 'undefined';
    private $_follow_cursor = false;
    private $_fill_series_color = false;
    // Legend
    private $_show_legend = true;
    private $_legend_position = 'right';
    private $_legend_background_color = '#FFFFFF';
    private $_legend_title = '';
    private $_legend_layout = 'horizontal';
    private $_legend_align = 'center';
    private $_legend_vertical_align = 'bottom';
    private $_legend_border_width = 0;
    private $_legend_border_color = '#909090';
    private $_legend_border_radius = 0;
    private $_legend_position_cjs = 'top';
    // Exporting
    private $_exporting = true;
    private $_exporting_data_labels = false;
    private $_exporting_file_name = 'Chart';
    private $_exporting_width = 'undefined';
    private $_exporting_button_align = 'right';
    private $_exporting_button_vertical_align = 'top';
    private $_exporting_button_color = '#666';
    private $_exporting_button_text = null;
    // Credits
    private $_credits = true;
    private $_credits_href = 'http://www.highcharts.com';
    private $_credits_text = 'Highcharts.com';

    private $_user_defined_series_data = array();
    private $_render_data = NULL;
    private $_highcharts_render_data = NULL;
    private $_chartjs_render_data = NULL;
    private $_apexcharts_render_data = NULL;
    //Toolbar
    private $_show_toolbar = false;
    private $_toolbar_buttons = array();
    private $_apex_exporting_file_name = 'Chart';

    private $_type_counters;

    public function __construct() {

    }

    public function setId($id) {
        $this->_id = $id;
    }

    public function getId() {
        return $this->_id;
    }

    // Chart

    public function setWidth($width) {
        $this->_width = $width;
    }

    public function getWidth() {
        return $this->_width;
    }

    /**
     * @return boolean
     */
    public function isResponsiveWidth() {
        return $this->_responsiveWidth;
    }

    /**
     * @param boolean $responsiveWidth
     */
    public function setResponsiveWidth($responsiveWidth) {
        $this->_responsiveWidth = $responsiveWidth;
    }

    public function setHeight($height) {
        $this->_height = $height;
    }

    public function getHeight() {
        return $this->_height;
    }

    /**
     * @param boolean $group_chart
     */
    public function setGroupChart($group_chart) {
        $this->_group_chart = $group_chart;
    }

    /**
     * @return boolean
     */
    public function isGroupChart() {
        return $this->_group_chart;
    }

    /**
     * @return bool
     */
    public function isEnableAnimation()
    {
        return $this->_enable_animation;
    }

    /**
     * @param bool $enable_animation
     */
    public function setEnableAnimation($enable_animation)
    {
        $this->_enable_animation = (bool)$enable_animation;
    }

    /**
     * @return int
     */
    public function getStartAngle()
    {
        return $this->_start_angle;
    }

    /**
     * @param int $start_angle
     */
    public function setStartAngle($start_angle)
    {
        $this->_start_angle = $start_angle;
    }

    /**
     * @return int
     */
    public function getEndAngle()
    {
        return $this->_end_angle;
    }

    /**
     * @param int $end_angle
     */
    public function setEndAngle($end_angle)
    {
        $this->_end_angle = $end_angle;
    }

    /**
     * @param $background_color
     */
    public function setBackgroundColor($background_color) {
        $this->_background_color = $background_color;
    }

    /**
     * @return string
     */
    public function getBackgroundColor() {
        return $this->_background_color;
    }

    /**
     * @param $border_width
     */
    public function setBorderWidth($border_width) {
        $this->_border_width = $border_width;
    }

    /**
     * @return int
     */
    public function getBorderWidth() {
        return $this->_border_width;
    }

    /**
     * @param $border_color
     */
    public function setBorderColor($border_color) {
        $this->_border_color = $border_color;
    }

    /**
     * @return string
     */
    public function getBorderColor() {
        return $this->_border_color;
    }

    /**
     * @param $border_radius
     */
    public function setBorderRadius($border_radius) {
        $this->_border_radius = $border_radius;
    }

    /**
     * @return int
     */
    public function getBorderRadius() {
        return $this->_border_radius;
    }

    /**
     * @param $zoom_type
     */
    public function setZoomType($zoom_type) {
        $this->_zoom_type = $zoom_type;
    }

    /**
     * @return string
     */
    public function getZoomType() {
        return $this->_zoom_type;
    }


    /**
     * @param $panning
     */
    public function setPanning($panning) {
        $this->_panning = (bool)$panning;
    }

    /**
     * @return bool
     */
    public function getPanning() {
        return $this->_panning;
    }

    /**
     * @param $pan_key
     */
    public function setPanKey($pan_key) {
        $this->_pan_key = $pan_key;
    }

    /**
     * @return string
     */
    public function getPanKey() {
        return $this->_pan_key;
    }

    /**
     * @param $plot_background_color
     */
    public function setPlotBackgroundColor($plot_background_color) {
        $this->_plot_background_color = $plot_background_color;
    }

    /**
     * @return string
     */
    public function getPlotBackgroundColor() {
        return $this->_plot_background_color;
    }

    /**
     * @param $plot_background_image
     */
    public function setPlotBackgroundImage($plot_background_image) {
        $this->_plot_background_image = $plot_background_image;
    }

    /**
     * @return string
     */
    public function getPlotBackgroundImage() {
        return $this->_plot_background_image;
    }

    /**
     * @param $line_background_image
     */
    public function setLineBackgroundImage($line_background_image) {
        $this->_line_background_image = $line_background_image;
    }

    /**
     * @return array
     */
    public function getLineBackgroundImage() {
        return $this->_line_background_image;
    }

    /**
     * @param $plot_border_width
     */
    public function setPlotBorderWidth($plot_border_width) {
        $this->_plot_border_width = $plot_border_width;
    }

    /**
     * @return int
     */
    public function getPlotBorderWidth() {
        return $this->_plot_border_width;
    }

    /**
     * @param $plot_border_color
     */
    public function setPlotBorderColor($plot_border_color) {
        $this->_plot_border_color = $plot_border_color;
    }

    /**
     * @return string
     */
    public function getPlotBorderColor() {
        return $this->_plot_border_color;
    }

    /**
     * @param $credits
     */
    public function setCredits($credits) {
        $this->_credits = (bool)$credits;
    }

    /**
     * @return bool
     */
    public function getCredits() {
        return $this->_credits;
    }

    /**
     * @return null
     */
    public function getFontSize() {
        return $this->_font_size;
    }

    /**
     * @param null $font_size
     */
    public function setFontSize($font_size) {
        $this->_font_size = $font_size;
    }

    /**
     * @return string
     */
    public function getFontName() {
        return $this->_font_name;
    }

    /**
     * @param string $font_name
     */
    public function setFontName($font_name) {
        $this->_font_name = $font_name;
    }

    /**
     * @return string
     */
    public function getFontStyle() {
        return $this->_font_style;
    }

    /**
     * @param string $font_style
     */
    public function setFontStyle($font_style) {
        $this->_font_style = $font_style;
    }

    /**
     * @return string
     */
    public function getFontColor() {
        return $this->_font_color;
    }

    /**
     * @param string $font_color
     */
    public function setFontColor($font_color) {
        $this->_font_color = $font_color;
    }

    /**
     * @param boolean $three_d
     */
    public function setThreeD($three_d) {
        $this->_three_d = (bool)$three_d;
    }

    /**
     * @return boolean
     */
    public function isThreeD() {
        return $this->_three_d;
    }

    /**
     * @return bool
     */
    public function isMonochrome()
    {
        return $this->_monochrome;
    }

    /**
     * @param $monochrome
     */
    public function setMonochrome($monochrome)
    {
        $this->_monochrome = (bool)$monochrome;
    }

    /**
     * @return string
     */
    public function getMonochromeColor()
    {
        return $this->_monochrome_color;
    }

    /**
     * @param string $monochrome_color
     */
    public function setMonochromeColor($monochrome_color)
    {
        $this->_monochrome_color = $monochrome_color;
    }

    /**
     * @param  $enable_color_palette
     */
    public function setEnableColorPalette($enable_color_palette) {
        $this->_enable_color_palette = (bool)$enable_color_palette;
    }

    /**
     * @return bool
     */
    public function isEnableColorPalette() {
        return $this->_enable_color_palette;
    }

    /**
     * @return string
     */
    public function getColorPalette()
    {
        return $this->_color_palette;
    }

    /**
     * @param string $color_palette
     */
    public function setColorPalette($color_palette)
    {
        $this->_color_palette = $color_palette;
    }

    /**
     * @return bool
     */
    public function isShowDataLabels()
    {
        return $this->_show_data_labels;
    }

    /**
     * @param bool $show_data_labels
     */
    public function setShowDataLabels($show_data_labels)
    {
        $this->_show_data_labels = (bool)$show_data_labels;
    }

    /**
     * @param  $enable_dropshadow
     */
    public function setEnableDropshadow($enable_dropshadow) {
        $this->_enable_dropshadow = (bool)$enable_dropshadow;
    }

    /**
     * @return bool
     */
    public function isEnableDropshadow() {
        return $this->_enable_dropshadow;
    }

    /**
     * @return int
     */
    public function getDropshadowBlur()
    {
        return $this->_dropshadow_blur;
    }

    /**
     * @param int $dropshadow_blur
     */
    public function setDropshadowBlur($dropshadow_blur)
    {
        $this->_dropshadow_blur = $dropshadow_blur;
    }

    /**
     * @return float
     */
    public function getDropshadowOpacity()
    {
        return $this->_dropshadow_opacity;
    }

    /**
     * @param float $dropshadow_opacity
     */
    public function setDropshadowOpacity($dropshadow_opacity)
    {
        $this->_dropshadow_opacity = $dropshadow_opacity;
    }

    /**
     * @return string
     */
    public function getDropshadowColor()
    {
        return $this->_dropshadow_color;
    }

    /**
     * @param string $dropshadow_color
     */
    public function setDropshadowColor($dropshadow_color)
    {
        $this->_dropshadow_color = $dropshadow_color;
    }

    /**
     * @return int
     */
    public function getDropshadowTop()
    {
        return $this->_dropshadow_top;
    }

    /**
     * @param int $dropshadow_top
     */
    public function setDropshadowTop($dropshadow_top)
    {
        $this->_dropshadow_top = $dropshadow_top;
    }

    /**
     * @return int
     */
    public function getDropshadowLeft()
    {
        return $this->_dropshadow_left;
    }

    /**
     * @param int $dropshadow_left
     */
    public function setDropshadowLeft($dropshadow_left)
    {
        $this->_dropshadow_left = $dropshadow_left;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->_text_color;
    }

    /**
     * @param string $text_color
     */
    public function setTextColor($text_color)
    {
        $this->_text_color = $text_color;
    }
    // Series

    /**
     * @param string $curve_type
     */
    public function setCurveType($curve_type) {
        $this->_curve_type = (bool)$curve_type;
    }

    /**
     * @return string
     */
    public function getCurveType() {
        return $this->_curve_type;
    }

    /**
     * @return string
     */
    public function getSeriesType() {
        return $this->_series_type;
    }
    /**
     *  @param string $series_type
     */
    public function setSeriesType($series_type) {
        $this->_series_type = $series_type;
    }

    // Axes

    /**
     * @param $show_grid
     */
    public function setShowGrid($show_grid) {
        $this->_show_grid = (bool)$show_grid;
    }

    /**
     * @return bool
     */
    public function getShowGrid() {
        return $this->_show_grid;
    }


    /**
     * @return string
     */
    public function getGridColor()
    {
        return $this->_grid_color;
    }

    /**
     * @param string $grid_color
     */
    public function setGridColor($grid_color)
    {
        $this->_grid_color = $grid_color;
    }

    /**
     * @return int
     */
    public function getGridStroke()
    {
        return $this->_grid_stroke;
    }

    /**
     * @param int $grid_stroke
     */
    public function setGridStroke($grid_stroke)
    {
        $this->_grid_stroke = $grid_stroke;
    }

    /**
     * @return string
     */
    public function getGridPosition()
    {
        return $this->_grid_position;
    }

    /**
     * @param string $grid_position
     */
    public function setGridPosition($grid_position)
    {
        $this->_grid_position = $grid_position;
    }

    /**
     * @return array
     */
    public function getGridAxes()
    {
        return $this->_grid_axes;
    }

    /**
     * @param array $grid_axes
     */
    public function setGridAxes($grid_axes)
    {
        $this->_grid_axes = $grid_axes;
    }

    /**
     * @param string $highcharts_line_dash_style
     */
    public function setHighchartsLineDashStyle($highcharts_line_dash_style) {
        $this->_highcharts_line_dash_style = $highcharts_line_dash_style;
    }

    /**
     * @return string
     */
    public function getHighchartsLineDashStyle() {
        return $this->_highcharts_line_dash_style;
    }

    /**
     * @param $label
     */
    public function setMajorAxisLabel($label) {
        $this->_axes['major']['label'] = $label;
    }

    /**
     * @return mixed
     */
    public function getMajorAxisLabel() {
        return $this->_axes['major']['label'];
    }

    /**
     * @param boolean $horizontal_axis_crosshair
     */
    public function setHorizontalAxisCrosshair($horizontal_axis_crosshair) {
        $this->_horizontal_axis_crosshair = (bool)$horizontal_axis_crosshair;
    }

    /**
     * @return boolean
     */
    public function isHorizontalAxisCrosshair() {
        return $this->_horizontal_axis_crosshair;
    }

    /**
     * @param int $horizontal_axis_direction
     */
    public function setHorizontalAxisDirection($horizontal_axis_direction) {
        $this->_horizontal_axis_direction = $horizontal_axis_direction;
    }

    /**
     * @return int
     */
    public function getHorizontalAxisDirection() {
        return $this->_horizontal_axis_direction;
    }

    /**
     * @param $label
     */
    public function setMinorAxisLabel($label) {
        $this->_axes['minor']['label'] = $label;
    }

    /**
     * @return mixed
     */
    public function getMinorAxisLabel() {
        return $this->_axes['minor']['label'];
    }

    /**
     * @param boolean $vertical_axis_crosshair
     */
    public function setVerticalAxisCrosshair($vertical_axis_crosshair) {
        $this->_vertical_axis_crosshair = (bool)$vertical_axis_crosshair;
    }

    /**
     * @return boolean
     */
    public function isVerticalAxisCrosshair() {
        return $this->_vertical_axis_crosshair;
    }

    /**
     * @param int $vertical_axis_direction
     */
    public function setVerticalAxisDirection($vertical_axis_direction) {
        $this->_vertical_axis_direction = $vertical_axis_direction;
    }

    /**
     * @return int
     */
    public function getVerticalAxisDirection() {
        return $this->_vertical_axis_direction;
    }

    /**
     * @return int
     */
    public function getMarkerSize()
    {
        return $this->_marker_size;
    }

    /**
     * @param $marker_size
     */
    public function setMarkerSize($marker_size)
    {
        $this->_marker_size = (int)$marker_size;
    }

    /**
     * @return int
     */
    public function getStrokeWidth()
    {
        return $this->_stroke_width;
    }

    /**
     * @param int $stroke_width
     */
    public function setStrokeWidth($stroke_width)
    {
        $this->_stroke_width = (int)$stroke_width;
    }

    /**
     * @param mixed $vertical_axis_min
     */
    public function setVerticalAxisMin($vertical_axis_min) {
        $this->_vertical_axis_min = $vertical_axis_min;
    }

    /**
     * @return mixed
     */
    public function getVerticalAxisMin() {
        return $this->_vertical_axis_min;
    }

    /**
     * @param mixed $vertical_axis_max
     */
    public function setVerticalAxisMax($vertical_axis_max) {
        $this->_vertical_axis_max = $vertical_axis_max;
    }

    /**
     * @return mixed
     */
    public function getVerticalAxisMax() {
        return $this->_vertical_axis_max;
    }

    /**
     * @param int $tick_amount
     */
    public function setTickAmount($tick_amount) {
        $this->_tick_amount = (int)$tick_amount;
    }

    /**
     * @return int
     */
    public function getTickAmount() {
        return $this->_tick_amount;
    }

    /**
     * @param $inverted
     */
    public function setInverted($inverted) {
        $this->_inverted = (bool)$inverted;
    }

    /**
     * @return bool
     */
    public function isInverted() {
        return $this->_inverted;
    }


    /**
     * @return bool
     */
    public function isReversed()
    {
        return $this->_reversed;
    }

    /**
     * @param $reversed
     */
    public function setReversed($reversed)
    {
        $this->_reversed = (bool)$reversed;
    }

    // Title

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * @param boolean $show_title
     */
    public function setShowTitle($show_title) {
        $this->_show_title = $show_title;
    }

    /**
     * @return boolean
     */
    public function isShowTitle() {
        return $this->_show_title;
    }

    /**
     * @param boolean $title_floating
     */
    public function setTitleFloating($title_floating) {
        $this->_title_floating = (bool)$title_floating;
    }

    /**
     * @return boolean
     */
    public function isTitleFloating() {
        return $this->_title_floating;
    }

    /**
     * @param string $title_align
     */
    public function setTitleAlign($title_align) {
        $this->_title_align = $title_align;
    }

    /**
     * @return string
     */
    public function getTitleAlign() {
        return $this->_title_align;
    }

    /**
     * @return string
     */
    public function getTitlePosition() {
        return $this->_title_position;
    }

    /**
     * @param string $title_position
     */
    public function setTitlePosition($title_position) {
        $this->_title_position = $title_position;
    }

    /**
     * @return string
     */
    public function getTitleFontName() {
        return $this->_title_font_name;
    }

    /**
     * @param string $title_font_name
     */
    public function setTitleFontName($title_font_name) {
        $this->_title_font_name = $title_font_name;
    }

    /**
     * @return string
     */
    public function getTitleFontStyle() {
        return $this->_title_font_style;
    }

    /**
     * @param string $title_font_style
     */
    public function setTitleFontStyle($title_font_style) {
        $this->_title_font_style = $title_font_style;
    }

    /**
     * @return string
     */
    public function getTitleFontColor() {
        return $this->_title_font_color;
    }

    /**
     * @param string $title_font_color
     */
    public function setTitleFontColor($title_font_color) {
        $this->_title_font_color = $title_font_color;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle) {
        $this->_subtitle = $subtitle;
    }

    /**
     * @return string
     */
    public function getSubtitle() {
        return $this->_subtitle;
    }

    /**
     * @param string $subtitle_align
     */
    public function setSubtitleAlign($subtitle_align) {
        $this->_subtitle_align = $subtitle_align;
    }

    /**
     * @return string
     */
    public function getSubtitleAlign() {
        return $this->_subtitle_align;
    }

    // Tooltip

    /**
     * @param boolean $tooltip_enabled
     */
    public function setTooltipEnabled($tooltip_enabled) {
        $this->_tooltip_enabled = (bool)$tooltip_enabled;
    }

    /**
     * @return boolean
     */
    public function isTooltipEnabled() {
        return $this->_tooltip_enabled;
    }

    /**
     * @param string $tooltip_background_color
     */
    public function setTooltipBackgroundColor($tooltip_background_color) {
        $this->_tooltip_background_color = $tooltip_background_color;
    }

    /**
     * @return string
     */
    public function getTooltipBackgroundColor() {
        return $this->_tooltip_background_color;
    }

    /**
     * @param int $tooltip_border_width
     */
    public function setTooltipBorderWidth($tooltip_border_width) {
        $this->_tooltip_border_width = $tooltip_border_width;
    }

    /**
     * @return int
     */
    public function getTooltipBorderWidth() {
        return $this->_tooltip_border_width;
    }

    /**
     * @param null $tooltip_border_color
     */
    public function setTooltipBorderColor($tooltip_border_color) {
        $this->_tooltip_border_color = $tooltip_border_color;
    }

    /**
     * @return null
     */
    public function getTooltipBorderColor() {
        return $this->_tooltip_border_color;
    }

    /**
     * @param int $tooltip_border_radius
     */
    public function setTooltipBorderRadius($tooltip_border_radius) {
        $this->_tooltip_border_radius = (int)$tooltip_border_radius;
    }

    /**
     * @return int
     */
    public function getTooltipBorderRadius() {
        return $this->_tooltip_border_radius;
    }

    /**
     * @param boolean $tooltip_shared
     */
    public function setTooltipShared($tooltip_shared) {
        $this->_tooltip_shared = (bool)$tooltip_shared;
    }

    /**
     * @return boolean
     */
    public function isTooltipShared() {
        return $this->_tooltip_shared;
    }

    /**
     * @param string $tooltip_value_prefix
     */
    public function setTooltipValuePrefix($tooltip_value_prefix) {
        $this->_tooltip_value_prefix = $tooltip_value_prefix;
    }

    /**
     * @return string
     */
    public function getTooltipValuePrefix() {
        return $this->_tooltip_value_prefix;
    }

    /**
     * @param string $tooltip_value_suffix
     */
    public function setTooltipValueSuffix($tooltip_value_suffix) {
        $this->_tooltip_value_suffix = $tooltip_value_suffix;
    }

    /**
     * @return string
     */
    public function getTooltipValueSuffix() {
        return $this->_tooltip_value_suffix;
    }


    /**
     * @return bool
     */
    public function isFollowCursor()
    {
        return $this->_follow_cursor;
    }

    /**
     * @param $follow_cursor
     */
    public function setFollowCursor($follow_cursor)
    {
        $this->_follow_cursor = (bool)$follow_cursor;
    }

    /**
     * @return bool
     */
    public function isFillSeriesColor()
    {
        return $this->_fill_series_color;
    }

    /**
     * @param $fill_series_color
     */
    public function setFillSeriesColor($fill_series_color)
    {
        $this->_fill_series_color = (bool)$fill_series_color;
    }



    // Legend

    public function setShowLegend($show_legend) {
        $this->_show_legend = (bool)$show_legend;
    }

    public function getShowLegend() {
        return $this->_show_legend;
    }

    /**
     * @return string
     */
    public function getLegendPosition() {
        return $this->_legend_position;
    }

    /**
     * @param string $legend_position
     */
    public function setLegendPosition($legend_position) {
        $this->_legend_position = $legend_position;
    }

    public function setLegendBackgroundColor($legend_background_color) {
        $this->_legend_background_color = $legend_background_color;
    }

    public function getLegendBackgroundColor() {
        return $this->_legend_background_color;
    }

    public function setLegendTitle($legend_title) {
        $this->_legend_title = $legend_title;
    }

    public function getLegendTitle() {
        return $this->_legend_title;
    }

    public function setLegendLayout($legend_layout) {
        $this->_legend_layout = $legend_layout;
    }

    public function getLegendLayout() {
        return $this->_legend_layout;
    }

    public function setLegendAlign($legend_align) {
        $this->_legend_align = $legend_align;
    }

    public function getLegendAlign() {
        return $this->_legend_align;
    }

    public function setLegendVerticalAlign($legend_vertical_align) {
        $this->_legend_vertical_align = $legend_vertical_align;
    }

    public function getLegendVerticalAlign() {
        return $this->_legend_vertical_align;
    }

    public function setLegendBorderWidth($legend_border_width) {
        $this->_legend_border_width = $legend_border_width;
    }

    public function getLegendBorderWidth() {
        return $this->_legend_border_width;
    }

    public function setLegendBorderColor($legend_border_color) {
        $this->_legend_border_color = $legend_border_color;
    }

    public function getLegendBorderColor() {
        return $this->_legend_border_color;
    }

    public function setLegendBorderRadius($legend_border_radius) {
        $this->_legend_border_radius = $legend_border_radius;
    }

    public function getLegendBorderRadius() {
        return $this->_legend_border_radius;
    }

    /**
     * @return string
     */
    public function getLegendPositionCjs() {
        return $this->_legend_position_cjs;
    }

    /**
     * @param string $legend_position_cjs
     */
    public function setLegendPositionCjs($legend_position_cjs) {
        $this->_legend_position_cjs = $legend_position_cjs;
    }

    // Exporting

    public function setExporting($exporting) {
        $this->_exporting = (bool)$exporting;
    }

    public function getExporting() {
        return $this->_exporting;
    }

    public function setExportingDataLabels($exporting_data_labels) {
        $this->_exporting_data_labels = (bool)$exporting_data_labels;
    }

    public function getExportingDataLabels() {
        return $this->_exporting_data_labels;
    }

    public function setExportingFileName($exporting_file_name) {
        $this->_exporting_file_name = $exporting_file_name;
    }

    public function getExportingFileName() {
        return $this->_exporting_file_name;
    }

    public function setExportingWidth($exporting_width) {
        $this->_exporting_width = $exporting_width;
    }

    public function getExportingWidth() {
        return $this->_exporting_width;
    }

    public function setExportingButtonAlign($exporting_button_align) {
        $this->_exporting_button_align = $exporting_button_align;
    }

    public function getExportingButtonAlign() {
        return $this->_exporting_button_align;
    }

    public function setExportingButtonVerticalAlign($exporting_button_vertical_align) {
        $this->_exporting_button_vertical_align = $exporting_button_vertical_align;
    }

    public function getExportingButtonVerticalAlign() {
        return $this->_exporting_button_vertical_align;
    }

    public function setExportingButtonColor($exporting_button_color) {
        $this->_exporting_button_color = $exporting_button_color;
    }

    public function getExportingButtonColor() {
        return $this->_exporting_button_color;
    }

    public function setExportingButtonText($exporting_button_text) {
        $this->_exporting_button_text = $exporting_button_text;
    }

    public function getExportingButtonText() {
        return $this->_exporting_button_text;
    }

    // Credits

    /**
     * @return string
     */
    public function getCreditsText() {
        return $this->_credits_text;
    }

    /**
     * @param string $credits_text
     */
    public function setCreditsText($credits_text) {
        $this->_credits_text = $credits_text;
    }

    /**
     * @return string
     */
    public function getCreditsHref() {
        return $this->_credits_href;
    }

    /**
     * @param string $credits_href
     */
    public function setCreditsHref($credits_href) {
        $this->_credits_href = $credits_href;
    }

    /**
     * @param $series_data
     */
    public function setUserDefinedSeriesData($series_data) {
        if (is_array($series_data)) {
            $this->_user_defined_series_data = $series_data;
        }
    }

    /**
     * @return array
     */
    public function getUserDefinedSeriesData() {
        return $this->_user_defined_series_data;
    }

    /**
     * @param $follow_filtering
     */
    public function setFollowFiltering($follow_filtering) {
        $this->_follow_filtering = (bool)$follow_filtering;
    }

    /**
     * @return bool
     */
    public function getFollowFiltering() {
        return $this->_follow_filtering;
    }

    /**
     * @param $engine
     */
    public function setEngine($engine) {
        $this->_engine = $engine;
    }

    /**
     * @return string
     */
    public function getEngine() {
        return $this->_engine;
    }

    /**
     * @param $type
     */
    public function setType($type) {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @param $row_range
     */
    public function setRowRange($row_range) {
        $this->_row_range = $row_range;
    }

    /**
     * @return array
     */
    public function getRowRange() {
        return $this->_row_range;
    }

    /**
     * @param $selected_columns
     */
    public function setSelectedColumns($selected_columns) {
        $this->_selected_columns = $selected_columns;
    }

    /**
     * @return array
     */
    public function getSelectedColumns() {
        return $this->_selected_columns;
    }

    /**
     * @param $wdt_id
     */
    public function setwpDataTableId($wdt_id) {
        $this->_wpdatatable_id = $wdt_id;
    }

    /**
     * @return null
     */
    public function getwpDataTableId() {
        return $this->_wpdatatable_id;
    }

    /**
     * @param $range_type
     */
    public function setRangeType($range_type) {
        $this->_range_type = $range_type;
    }

    /**
     * @return string
     */
    public function getRangeType() {
        return $this->_range_type;
    }

    //Toolbar

    /**
     * @return bool
     */
    public function isShowToolbar()
    {
        return $this->_show_toolbar;
    }

    /**
     * @param $show_toolbar
     */
    public function setShowToolbar($show_toolbar)
    {
        $this->_show_toolbar = (bool)$show_toolbar;
    }

    /**
     * @return array
     */
    public function getToolbarButtons()
    {
        return $this->_toolbar_buttons;
    }

    /**
     * @param array $toolbar_buttons
     */
    public function setToolbarButtons($toolbar_buttons)
    {
        $this->_toolbar_buttons = $toolbar_buttons;
    }

    public function setApexExportingFileName($apex_exporting_file_name) {
        $this->_apex_exporting_file_name = $apex_exporting_file_name;
    }

    public function getApexExportingFileName() {
        return $this->_apex_exporting_file_name;
    }

    /**
     * @param $constructedChartData
     * @param bool $loadFromDB
     * @return WPDataChart
     */
    public static function factory($constructedChartData, $loadFromDB = true) {
        $chartObj = new self();

        if (isset($constructedChartData['chart_id'])) {
            $chartObj->setId((int)$constructedChartData['chart_id']);
            if ($loadFromDB) {
                $chartObj->loadFromDB();
                $chartObj->prepareData();
                $chartObj->shiftStringColumnUp();
            }
        }

        // Main data (steps 1-3 of chart constructor)
        $chartObj->setwpDataTableId((int)$constructedChartData['wpdatatable_id']);
        $chartObj->setTitle(sanitize_text_field($constructedChartData['chart_title']));
        $chartObj->setEngine(sanitize_text_field($constructedChartData['chart_engine']));
        $chartObj->setType(sanitize_text_field($constructedChartData['chart_type']));
        if (isset($constructedChartData['series_type'])) {
            $chartObj->setSeriesType(sanitize_text_field($constructedChartData['series_type']));
        }
        $chartObj->setSelectedColumns(array_map('sanitize_text_field', $constructedChartData['selected_columns']));
        $chartObj->setRangeType(sanitize_text_field($constructedChartData['range_type']));
        if (isset($constructedChartData['range_data'])) {
            $chartObj->setRowRange(array_map('intval', $constructedChartData['range_data']));
        }
        $chartObj->setFollowFiltering((bool)$constructedChartData['follow_filtering']);

        // Render data (step 4 or chart constructor)
        // Chart
        $chartObj->setWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'width', 0));
        $chartObj->setResponsiveWidth((bool)WDTTools::defineDefaultValue($constructedChartData, 'responsive_width', 0));
        $chartObj->setHeight((int)WDTTools::defineDefaultValue($constructedChartData, 'height', 400));
        $chartObj->setGroupChart(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'group_chart', false)));
        $chartObj->setEnableAnimation(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'enable_animation', false)));
        $chartObj->setStartAngle((int)WDTTools::defineDefaultValue($constructedChartData, 'start_angle', 0));
        $chartObj->setEndAngle((int)WDTTools::defineDefaultValue($constructedChartData, 'end_angle', 360));
        $chartObj->setBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'background_color', '#FFFFFF')));
        $chartObj->setBorderWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'border_width', 0));
        $chartObj->setBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'border_color', '#FFFFFF')));
        $chartObj->setBorderRadius((int)WDTTools::defineDefaultValue($constructedChartData, 'border_radius', 0));
        $chartObj->setZoomType(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'zoom_type', 'undefined')));
        $chartObj->setPanning(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'panning', false)));
        $chartObj->setPanKey(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'pan_key', 'shift')));
        $chartObj->setPlotBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_color', '#FFFFFF')));
        $chartObj->setPlotBackgroundImage(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_image', null)));
        $chartObj->setLineBackgroundImage(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'line_background_image', null)));
        $chartObj->setPlotBorderWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'plot_border_width', 0));
        $chartObj->setPlotBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_border_color', '#C0C0C0')));
        $chartObj->setFontSize(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_size', null)));
        $chartObj->setFontName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_name', 'Arial')));
        $chartObj->setFontStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_style', 'normal')));
        $chartObj->setFontColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_color', '#666')));
        $chartObj->setThreeD(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'three_d', false)));
        $chartObj->setMonochrome(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'monochrome', false)));
        $chartObj->setMonochromeColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'monochrome_color','#255aee' )));
        $chartObj->setEnableColorPalette(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'enable_color_palette', false)));
        $chartObj->setColorPalette(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'color_palette', 'palette1')));
        $chartObj->setShowDataLabels(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_data_labels', false)));
        $chartObj->setEnableDropshadow(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'enable_dropshadow', false)));
        $chartObj->setDropshadowBlur((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_blur', 3));
        $chartObj->setDropshadowOpacity((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_opacity', 35));
        $chartObj->setDropshadowColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_color', '#000000')));
        $chartObj->setDropshadowTop((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_top', 5));
        $chartObj->setDropshadowLeft((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_left', 5));
        $chartObj->setTextColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'text_color', '#373d3f')));

        // Series
        if (!empty($constructedChartData['series_data'])) {
            array_walk_recursive(
                $constructedChartData['series_data'],
                function ($value, $key) {
                    sanitize_text_field($value);
                }
            );
            $chartObj->setUserDefinedSeriesData($constructedChartData['series_data']);
        }

        $chartObj->setCurveType(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'curve_type', 'none')));

        // Axes
        $chartObj->setShowGrid(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_grid', true)));
        $chartObj->setGridColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_color', '#000000')));
        $chartObj->setGridStroke(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_stroke', 1)));
        $chartObj->setGridPosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_position', 'back')));
        $chartObj->setGridAxes((array)WDTTools::defineDefaultValue($constructedChartData, 'grid_axes', []));
        $chartObj->setHighchartsLineDashStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'highcharts_line_dash_style', 'Solid')));
        $chartObj->setMajorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_label', '')));
        $chartObj->setHorizontalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_crosshair', false)));
        $chartObj->setHorizontalAxisDirection(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_direction', 1)));
        $chartObj->setMinorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_label', '')));
        $chartObj->setVerticalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_crosshair', false)));
        $chartObj->setVerticalAxisDirection(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_direction', 1)));
        $chartObj->setMarkerSize(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'marker_size',0)));
        $chartObj->setStrokeWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'stroke_width',2)));
        $chartObj->setVerticalAxisMin(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_min', '')));
        $chartObj->setVerticalAxisMax(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_max', '')));
        $chartObj->setTickAmount(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tick_amount', 0)));
        $chartObj->setInverted(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'inverted', false)));
        $chartObj->setReversed(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'reversed', false)));

        // Title
        $chartObj->setShowTitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_title', true)));
        $chartObj->setTitleFloating(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_floating', false)));
        $chartObj->setTitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_align', 'center')));
        $chartObj->setTitlePosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_position', 'top')));
        $chartObj->setTitleFontName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_name', 'Arial')));
        $chartObj->setTitleFontStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_style', 'bold')));
        $chartObj->setTitleFontColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_color', '#666')));
        $chartObj->setSubtitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle', false)));
        $chartObj->setSubtitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle_align', 'center')));

        // Tooltip
        $chartObj->setTooltipEnabled(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_enabled', true)));
        $chartObj->setTooltipBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_background_color', 'rgba(255, 255, 255, 0.85)')));
        $chartObj->setTooltipBorderWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_width', 1)));
        $chartObj->setTooltipBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_color', null)));
        $chartObj->setTooltipBorderRadius(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_radius', 3)));
        $chartObj->setTooltipShared(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_shared', false)));
        $chartObj->setTooltipValuePrefix(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_value_prefix', false)));
        $chartObj->setTooltipValueSuffix(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_value_suffix', false)));
        $chartObj->setFollowCursor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'follow_cursor', false)));
        $chartObj->setFillSeriesColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'fill_series_color', false)));

        // Legend
        $chartObj->setShowLegend(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_legend', true)));
        $chartObj->setLegendPosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_position', 'right')));
        $chartObj->setLegendBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_background_color', '#FFFFFF')));
        $chartObj->setLegendTitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_title', '')));
        $chartObj->setLegendLayout(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_layout', 'horizontal')));
        $chartObj->setLegendAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_align', 'center')));
        $chartObj->setLegendVerticalAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_vertical_align', 'bottom')));
        $chartObj->setLegendBorderWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_width', 0)));
        $chartObj->setLegendBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_color', '#909090')));
        $chartObj->setLegendBorderRadius(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_radius', 0)));
        $chartObj->setLegendPositionCjs(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_position_cjs', 'top')));

        // Exporting
        $chartObj->setExporting(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting', true)));
        $chartObj->setExportingDataLabels(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_data_labels', false)));
        $chartObj->setExportingFileName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_file_name', 'Chart')));
        $chartObj->setExportingWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_width', 'undefined')));
        $chartObj->setExportingButtonAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_align', 'right')));
        $chartObj->setExportingButtonVerticalAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_vertical_align', 'top')));
        $chartObj->setExportingButtonColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_color', '#666')));
        $chartObj->setExportingButtonText(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_text', null)));

        // Credits
        $chartObj->setCredits(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'credits', true)));
        $chartObj->setCreditsHref(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'credits_href', 'http://www.highcharts.com')));
        $chartObj->setCreditsText(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'credits_text', 'Highcharts.com')));

        //Toolbar
        $chartObj->setShowToolbar(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData,'show_toolbar', false)));
        if (isset($constructedChartData['toolbar_buttons'])) {
            $chartObj->setToolbarButtons(array_map('sanitize_text_field', $constructedChartData['toolbar_buttons']));
        }
        $chartObj->setApexExportingFileName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'apex_exporting_file_name', 'Chart')));

        $chartObj->loadChildWPDataTable();

        return $chartObj;
    }

    public function loadChildWPDataTable() {
        if (empty($this->_wpdatatable_id)) {
            return false;
        }

        $this->_wpdatatable = WPDataTable::loadWpDataTable($this->_wpdatatable_id, null, empty($this->_follow_filtering));
    }

    public function shiftStringColumnUp() {
        /**
         * Check if the string column is not in the beginning and move it up
         */
        if (count($this->_render_data['columns']) > 1) {
            $shiftNeeded = false;
            $shiftIndex = 0;
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                if ($this->_render_data['columns'][$i]['type'] == 'string') {
                    $shiftNeeded = true;
                    $shiftIndex = $i;
                    break;
                }
            }

            if ($shiftNeeded) {
                // Shift columns
                $strColumn = $this->_render_data['columns'][$shiftIndex];
                unset($this->_render_data['columns'][$shiftIndex]);
                array_unshift($this->_render_data['columns'], $strColumn);
                // Shift rows
                for ($j = 0; $j < count($this->_render_data['rows']); $j++) {
                    $strCell = $this->_render_data['rows'][$j][$shiftIndex];
                    unset($this->_render_data['rows'][$j][$shiftIndex]);
                    array_unshift($this->_render_data['rows'][$j], $strCell);
                }
                // Shift column indexes
                if (isset($this->_render_data['column_indexes'])) {
                    $shiftedIndex = $this->_render_data['column_indexes'][$shiftIndex];
                    unset($this->_render_data['column_indexes'][$shiftIndex]);
                    array_unshift($this->_render_data['column_indexes'], $shiftedIndex);
                }
            }
        }

        // Format axes
        $this->_render_data['axes']['major'] = array(
            'type' => $this->_render_data['columns'][0]['type'],
            'label' => !empty($this->_render_data['hAxis']['title']) ?
                $this->_render_data['hAxis']['title'] : $this->_render_data['columns'][0]['label']
        );
        $this->_render_data['axes']['minor'] = array(
            'type' => $this->_render_data['columns'][1]['type'],
            'label' => !empty($this->_render_data['vAxis']['title']) ?
                $this->_render_data['vAxis']['title'] : ''
        );

        // Get all series names
        if (empty($this->_render_data['series'])) {
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                $this->_render_data['series'][] = array(
                    'label' => $this->_render_data['columns'][$i]['label'],
                    'color' => '',
                    'orig_header' => $this->_render_data['columns'][$i]['orig_header']
                );
            }
        }

    }

    public function prepareSeriesData() {
        // Init render data if it is empty
        if (empty($this->_render_data)) {
            $this->_render_data = array(
                'columns' => array(),
                'rows' => array(),
                'axes' => array(),
                'options' => array(
                    'title' => $this->_show_title ? $this->_title : '',
                    'series' => array(),
                    'width' => $this->_width,
                    'height' => $this->_height
                ),
                'vAxis' => array(),
                'hAxis' => array(),
                'errors' => array(),
                'series' => array()
            );
        }

        if ($this->_responsiveWidth) {
            unset($this->_render_data['options']['width']);
            $this->_render_data['options']['responsive_width'] = 1;
        }

        $this->_type_counters = array(
            'date' => 0,
            'datetime' => 0,
            'string' => 0,
            'number' => 0
        );

        // Define columns
        foreach ($this->getSelectedColumns() as $columnKey) {
            $columnType = $this->_wpdatatable->getColumn($columnKey)->getGoogleChartColumnType();
            $this->_render_data['columns'][] = array(
                'type' => $columnType,
                'label' => isset($this->_user_defined_series_data[$columnKey]['label']) ?
                    $this->_user_defined_series_data[$columnKey]['label'] : $this->_wpdatatable->getColumn($columnKey)->getTitle(),
                'orig_header' => $columnKey
            );
            $this->_type_counters[$columnType]++;
        }

        // Define axes titles
        if (isset($this->_axes['major']['label'])) {
            $this->_render_data['options']['hAxis']['title'] = $this->_axes['major']['label'];
        }
        if (isset($this->_axes['minor']['label'])) {
            $this->_render_data['options']['vAxis']['title'] = $this->_axes['minor']['label'];
        }

        // Define series colors,type and yaxis
        if ( $this->_type == 'highcharts_basic_column_chart'||
             $this->_type == 'highcharts_line_chart' ||
             $this->_type == 'highcharts_basic_bar_chart'||
             $this->_type == 'highcharts_spline_chart'||
             $this->_type == 'highcharts_basic_area_chart' ||
             $this->_type == 'highcharts_polar_chart' ||
             $this->_type == 'highcharts_spiderweb_chart') {

            if (!empty($this->_user_defined_series_data)) {
                $seriesIndex = 0;
                $i = 1;
                foreach ($this->_user_defined_series_data as $series_data) {
                    if (!empty($series_data['color']) || !empty($series_data['type']) || isset($series_data['yAxis'])){
                        $this->_render_data['options']['series'][(int)$seriesIndex] = array(
                            'color' => $series_data['color'],
                            'type' => $series_data['type'],
                            'yAxis' =>  $series_data['yAxis'] == 1 ? $i : 0
                        );
                        if ($series_data['yAxis']) {
                            $i++;
                        }
                    }
                    $seriesIndex++;
                }
            }
        } else if (in_array($this->_type, [ 'apexcharts_straight_line_chart',
                        'apexcharts_spline_chart','apexcharts_stepline_chart', 'apexcharts_spline_area_chart',
                        'apexcharts_stepline_area_chart', 'apexcharts_basic_area_chart', 'apexcharts_column_chart'])
                        && !empty($this->_user_defined_series_data)) {
            $seriesIndex = 0;
            $i = 1;
            foreach ($this->_user_defined_series_data as $series_data) {
                $this->_render_data['options']['series'][$seriesIndex] = array(
                    'color' => $series_data['color'],
                    'type' => $series_data['type'],
                    'yAxis' => $series_data['yAxis'] == 1 ? $i : 0,
                    'chart_image' => $series_data['chart_image'],
                );
                if ($series_data['yAxis']) {
                    $i++;
                }
                $seriesIndex++;
            }
        } else if (in_array($this->_type, ['apexcharts_radar_chart','apexcharts_grouped_bar_chart',
                        'apexcharts_stacked_bar_chart', 'apexcharts_100_stacked_bar_chart',
                        'apexcharts_stacked_column_chart', 'apexcharts_100_stacked_column_chart'])) {
            $seriesIndex = 0;
            $i = 1;
            foreach ($this->_user_defined_series_data as $series_data) {
                $this->_render_data['options']['series'][$seriesIndex] = array(
                    'color' => $series_data['color'],
                    'chart_image' => $series_data['chart_image'],
                );
                if ($series_data['yAxis']) {
                    $i++;
                }
                $seriesIndex++;
            }
        } else {
            if (!empty($this->_user_defined_series_data)) {
                $seriesIndex = 0;
                foreach ($this->_user_defined_series_data as $series_data) {
                    if (!empty($series_data['color']) || !empty($series_data['type'])){
                        $this->_render_data['options']['series'][(int)$seriesIndex] = array(
                            'color' => $series_data['color'],
                            'label' => $series_data['label']
                        );
                    }
                    $seriesIndex++;
                }
            }
        }

        // Group chart data
        if ($this->isGroupChart()) {
            $this->_render_data['group_chart'] = true;
        } else {
            $this->_render_data['group_chart'] = false;
        }

        // Define grid settings
        if (!$this->_show_grid) {
            if (!isset($this->_render_data['options']['hAxis'])) {
                $this->_render_data['options']['hAxis'] = array();
            }
            $this->_render_data['options']['hAxis']['gridlines'] = array(
                'color' => 'transparent'
            );
            if (!isset($this->_render_data['options']['vAxis'])) {
                $this->_render_data['options']['vAxis'] = array();
            }
            $this->_render_data['options']['vAxis']['gridlines'] = array(
                'color' => 'transparent'
            );
            $this->_render_data['show_grid'] = false;
        } else {
            $this->_render_data['show_grid'] = true;
        }

        // Detect errors
        if ($this->_type_counters['string'] > 1) {
            $this->_render_data['errors'][] = __('Only one column can be of type String', 'wpdatatables');
        }
        if (($this->_type_counters['number'] > 1) && ($this->_type_counters['date'] > 1)) {
            $this->_render_data['errors'][] = __('You are mixing data types (several date axes and several number)', 'wpdatatables');
        }

        if (empty($this->_highcharts_render_data)) {
            $this->_highcharts_render_data = array();
        }

        if (empty($this->_apexcharts_render_data)) {
            $this->_apexcharts_render_data = array();
        }

    }


    /**
     * Prepares the data for Google charts format
     */
    public function prepareData() {

        // Prepare series and columns
        if (empty($this->_render_data['columns'])) {
            $this->prepareSeriesData();
        }

        $dateFormat = ($this->getEngine() == 'google') ? DateTime::RFC2822 : get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');

        // The data itself
        if (empty($this->_render_data['rows'])) {
            if ($this->getRangeType() == 'all_rows') {
                foreach ($this->_wpdatatable->getDataRows() as $row) {
                    $return_data_row = array();
                    foreach ($this->getSelectedColumns() as $columnKey) {
                        $dataType = $this->_wpdatatable->getColumn($columnKey)->getDataType();
                        $decimalPlaces =$this->_wpdatatable->getColumn($columnKey)->getDecimalPlaces();
                        $thousandsSeparator =$this->_wpdatatable->getColumn($columnKey)->isShowThousandsSeparator();
                        switch ($dataType) {
                            case 'date':
                                $timestamp = is_int($row[$columnKey]) ? $row[$columnKey] : strtotime(str_replace('/', '-', $row[$columnKey]));
                                $return_data_row[] = date(
                                    $dateFormat,
                                    $timestamp
                                );
                                break;
                            case 'datetime':
                                $timestamp = is_int($row[$columnKey]) ? $row[$columnKey] : strtotime(str_replace('/', '-', $row[$columnKey]));
                                if ($this->getEngine() == 'google') {
                                    $return_data_row[] = date(
                                        $dateFormat,
                                        $timestamp
                                    );
                                } else {
                                    $return_data_row[] = date(
                                        $dateFormat . ' ' . $timeFormat,
                                        $timestamp
                                    );
                                }
                                break;
                            case 'time':
                                $timestamp = $row[$columnKey];
                                $return_data_row[] = date(
                                    $timeFormat,
                                    $timestamp
                                );
                                break;
                            case 'int':
                                if (has_filter('wpdatatables_filter_int_cell_data_in_charts')) {
                                    $row[$columnKey] = apply_filters('wpdatatables_filter_int_cell_data_in_charts', $row[$columnKey], $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($row[$columnKey])) {
                                        $return_data_row[] = (float)$row[$columnKey];
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    $return_data_row[] = (float)$row[$columnKey];
                                }
                                break;
                            case 'float':
                                if (has_filter('wpdatatables_filter_float_cell_data_in_charts')) {
                                    $row[$columnKey] = apply_filters('wpdatatables_filter_float_cell_data_in_charts', $row[$columnKey], $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($row[$columnKey])) {
                                        if ($decimalPlaces != -1) {
                                            $return_data_row[] = (float)number_format(
                                                (float)($row[$columnKey]),
                                                $decimalPlaces,
                                                '.',
                                                $thousandsSeparator ? '' : '.');
                                        } else {
                                            $return_data_row[] = (float)$row[$columnKey];
                                        }
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    if ($decimalPlaces != -1){
                                        $return_data_row[] = (float)number_format(
                                            (float)($row[$columnKey]),
                                            $decimalPlaces,
                                            '.',
                                            $thousandsSeparator ? '' : '.');
                                    }else {
                                        $return_data_row[] = (float)$row[$columnKey];
                                    }
                                }
                                break;
                            case 'link':
                                if (!in_array($this->getEngine(),['google','chartjs','apexcharts'])) {
                                    if (strpos($row[$columnKey], '||') !== false) {
                                        list($link, $row[$columnKey]) = explode('||', $row[$columnKey]);
                                        $return_data_row[] = '<a href="' . $link . '">' . $row[$columnKey] . '</a>';
                                    } else {
                                        $return_data_row[] = '<a href="' . $row[$columnKey] . '">' . $row[$columnKey] . '</a>';
                                    }
                                } else {
                                    $return_data_row[] = $row[$columnKey];
                                }
                                break;
                                case 'string':
                            default:
                                if ($this->getEngine() == 'apexcharts') {
                                    $return_data_row[] = $row[$columnKey] === null ? '' : $row[$columnKey];
                                } else {
                                    $return_data_row[] = $row[$columnKey];
                                }
                                break;
                        }
                    }
                    $this->_render_data['rows'][] = $return_data_row;
                }
            } else {
                foreach ($this->_row_range as $rowIndex) {
                    $return_data_row = array();
                    foreach ($this->getSelectedColumns() as $columnKey) {

                        $dataType = $this->_wpdatatable->getColumn($columnKey)->getDataType();
                        $decimalPlaces =$this->_wpdatatable->getColumn($columnKey)->getDecimalPlaces();
                        switch ($dataType) {
                            case 'date':
                                $timestamp = is_int($this->_wpdatatable->getCell($columnKey, $rowIndex)) ?
                                    $this->_wpdatatable->getCell($columnKey, $rowIndex)
                                    : strtotime(str_replace('/', '-', $this->_wpdatatable->getCell($columnKey, $rowIndex)));
                                $return_data_row[] = date(
                                    $dateFormat,
                                    $timestamp
                                );
                                break;
                            case 'datetime':
                                $timestamp = is_int($this->_wpdatatable->getCell($columnKey, $rowIndex)) ?
                                    $this->_wpdatatable->getCell($columnKey, $rowIndex) : strtotime(str_replace('/', '-', $this->_wpdatatable->getCell($columnKey, $rowIndex)));
                                if ($this->getEngine() == 'google') {
                                    $return_data_row[] = date(
                                        $dateFormat,
                                        $timestamp
                                    );
                                } else {
                                    $return_data_row[] = date(
                                        $dateFormat . ' ' . $timeFormat,
                                        $timestamp
                                    );
                                }
                                break;
                            case 'time':
                                $timestamp = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                $return_data_row[] = date(
                                    $timeFormat,
                                    $timestamp
                                );
                                break;
                            case 'int':
                                if (has_filter('wpdatatables_filter_int_cell_data_in_charts')) {
                                    $cellData = apply_filters('wpdatatables_filter_int_cell_data_in_charts', $this->_wpdatatable->getCell($columnKey, $rowIndex), $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($cellData)) {
                                        $return_data_row[] = (float)$cellData;
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                }else {
                                    $return_data_row[] = (float)$this->_wpdatatable->getCell($columnKey, $rowIndex);
                                }
                                break;
                            case 'float':
                                if (has_filter('wpdatatables_filter_float_cell_data_in_charts')) {
                                    $floatNumber = apply_filters('wpdatatables_filter_float_cell_data_in_charts', $this->_wpdatatable->getCell($columnKey, $rowIndex), $columnKey , $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($floatNumber)) {
                                        if ($decimalPlaces != -1) {
                                            $return_data_row[] = (float)number_format($floatNumber, $decimalPlaces);
                                        } else {
                                            $return_data_row[] = $floatNumber;
                                        }
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    $floatNumber= (float)$this->_wpdatatable->getCell($columnKey, $rowIndex);;
                                    if ($decimalPlaces != -1){
                                        $return_data_row[] = (float)number_format ($floatNumber, $decimalPlaces);
                                    } else {
                                        $return_data_row[] = $floatNumber;
                                    }
                                }
                                break;
                            case 'link':
                                $cellData = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                if (!in_array($this->getEngine(),['google','chartjs'])) {
                                     if (strpos($cellData, '||') !== false) {
                                         list($link, $cellData) = explode('||', $cellData);
                                         $return_data_row[] = '<a href="' . $link . '">' . $cellData . '</a>';
                                     } else {
                                         $return_data_row[] = '<a href="' . $cellData . '">' . $cellData . '</a>';
                                     }
                                } else {
                                    $return_data_row[] = $cellData;
                                }
                                break;
                            case 'string':
                            default:
                                if ($this->getEngine() == 'apexcharts') {
                                    $return_data_row[] = $this->_wpdatatable->getCell($columnKey, $rowIndex) === null ? '' : $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                } else {
                                    $return_data_row[] = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                }
                                break;
                        }

                    }
                    $this->_render_data['rows'][] = $return_data_row;
                }
            }

        }

        $this->_render_data['type'] = $this->_type;
        return $this->_render_data;
    }

    public function groupData() {
        if (isset($this->_render_data['group_chart'])) {
            if ($this->isGroupChart() || $this->_render_data['group_chart'] == true) {
                $output = array();
                foreach ($this->_render_data['rows'] as $row) {
                    if (!empty($output)) {
                        $value_key = 'none';
                        foreach ($output as $key => $value) {
                            if ($value_key === 'none') {
                                if ($value[0] == $row[0]) {
                                    $value_key = $key;
                                }
                            }
                        }
                        if ($value_key === 'none') {
                            $output[] = $row;
                        } else {
                            for ($n = 1; $n <= count($row) - 1; $n++) {
                                $output[$value_key][$n] += $row[$n];
                            }
                        }
                    } else {
                        $output[] = $row;
                    }
                }
                $this->_group_chart = 1;
                $this->_render_data['rows'] = $output;
            } else {
                $this->_group_chart = 0;
            }
        } else {
            $this->_group_chart = 0;
        }

    }

    public function prepareGoogleChartsRender() {
        // Chart
        if (!$this->_responsiveWidth) {
            $this->_render_data['width'] = $this->getWidth();
        }
        $this->_render_data['options']['backgroundColor']['fill'] = $this->getBackgroundColor();
        $this->_render_data['options']['backgroundColor']['strokeWidth'] = $this->getBorderWidth();
        $this->_render_data['options']['backgroundColor']['stroke'] = $this->getBorderColor();
        $this->_render_data['options']['backgroundColor']['rx'] = $this->getBorderRadius();
        $this->_render_data['options']['chartArea']['backgroundColor']['fill'] = $this->getPlotBackgroundColor();
        $this->_render_data['options']['chartArea']['backgroundColor']['strokeWidth'] = $this->getPlotBorderWidth();
        $this->_render_data['options']['chartArea']['backgroundColor']['stroke'] = $this->getPlotBorderColor();
        $this->_render_data['options']['fontSize'] = $this->getFontSize();
        $this->_render_data['options']['fontName'] = $this->getFontName();
        if ($this->_type == 'google_pie_chart') {
            $this->_render_data['options']['is3D'] = $this->isThreeD();
        }

        // Series
        if ($this->_type == 'google_line_chart') {
            if ($this->getCurveType()) {
                $this->_render_data['options']['curveType'] = 'function';
            } else {
                $this->_render_data['options']['curveType'] = 'none';
            }
        }

        // Axes
        if ($this->isHorizontalAxisCrosshair() && !$this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'horizontal';
        } elseif (!$this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'vertical';
        } elseif ($this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'both';
        } else {
            $this->_render_data['options']['crosshair']['trigger'] = '';
            $this->_render_data['options']['crosshair']['orientation'] = '';
        }
        $this->_render_data['options']['hAxis']['direction'] = $this->getHorizontalAxisDirection();
        $this->_render_data['options']['vAxis']['direction'] = $this->getVerticalAxisDirection();
        $this->_render_data['options']['vAxis']['viewWindow']['min'] = $this->getVerticalAxisMin();
        $this->_render_data['options']['vAxis']['viewWindow']['max'] = $this->getVerticalAxisMax();
        if ($this->isInverted()) {
            $this->_render_data['options']['orientation'] = 'vertical';
        } else {
            $this->_render_data['options']['orientation'] = 'horizontal';
        }

        // Title
        if ($this->isTitleFloating()) {
            $this->_render_data['options']['titlePosition'] = 'in';
        } else {
            $this->_render_data['options']['titlePosition'] = 'out';
        }

        // Tooltip
        if ($this->isTooltipEnabled()) {
            $this->_render_data['options']['tooltip']['trigger'] = 'focus';
        } else {
            $this->_render_data['options']['tooltip']['trigger'] = 'none';
        }

        // Legend
        $this->_render_data['options']['legend']['position'] = $this->getLegendPosition();
        if ($this->getLegendVerticalAlign() == 'bottom') {
            $this->_render_data['options']['legend']['alignment'] = 'end';
        } elseif ($this->getLegendVerticalAlign() == 'middle') {
            $this->_render_data['options']['legend']['alignment'] = 'center';
        } else {
            $this->_render_data['options']['legend']['alignment'] = 'start';
        }

        $this->_render_data = apply_filters('wpdatatables_filter_google_charts_render_data', $this->_render_data, $this->getId(), $this);

    }

    public function prepareHighchartsRender() {
        $highchartsRender = array(
            'title' => array(
                'text' => $this->_show_title ? $this->getTitle() : ''
            ),
            'series' => array(),
            'xAxis' => array()
        );

        if ($this->_type == 'highcharts_basic_column_chart') {
            $this->setSeriesType('column');
        }else if($this->_type == 'highcharts_line_chart'){
            $this->setSeriesType('line');
        }else if ($this->_type == 'highcharts_basic_bar_chart'){
            $this->setSeriesType('bar');
        }else if ($this->_type == 'highcharts_spline_chart'){
            $this->setSeriesType('spline');
        }else if ($this->_type == 'highcharts_basic_area_chart'){
            $this->setSeriesType('area');
        }

        if ($this->_type == 'highcharts_treemap_chart') {
            $data = [];
            foreach ($this->_render_data['rows'] as $key => $row) {
                $data[] = [
                    'name' => $row[0],
                    'value' => $row[1],
                    'colorValue' => $row[1]
                ];
            };
            $highchartsRender['series'] = array(
                array(
                    'type' => 'treemap',
                    'layoutAlgorithm' => 'squarified',
                    'name' => $this->_render_data['columns'][1]['label'],
                    'data' => $data
                )
            );
            unset($highchartsRender['xAxis']);
            $highchartsRender['colorAxis'] = array(

                'minColor' => '#FFFFBF',
                'maxColor' => '#006837'
            );
        }
        if ($this->_type == 'highcharts_treemap_level_chart') {
            $data = [];

            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {

                foreach ($this->_render_data['rows'] as $key => $row) {
                    if ($i > 0) {
                        if ($this->_render_data['columns'][$i]['type'] == 'number') {
                            $column_name[$i - 1] = $this->_render_data['columns'][$i]['label'];
                        }

                    } else {
                        $helperArr[] = $row[$i];
                    }
                };

                foreach ($helperArr as $helperKey => $helperValue) {
                    if ($i == 0) {
                        $data[] = [
                            'id' => 'id_' . $helperKey,
                            'name' => $helperValue,
                            'color' => '#006837'
                        ];
                    }

                };

            }
            $row_numbers = $this->_render_data['rows'];
            for ($y = 0; $y < count($row_numbers); $y++) {
                for ($z = 0; $z < count($row_numbers[$y]) - 1; $z++) {

                    $data[] = [
                        'id' => 'id_' . $y . '_' . $z,
                        'name' => $column_name[$z],
                        'parent' => 'id_' . $y,
                        'value' => $this->_render_data['rows'][$y][$z + 1],
                        'colorValue' => $this->_render_data['rows'][$y][$z + 1],
                    ];

                }
            }

            $highchartsRender['colorAxis'] = array(
                'minColor' => '#FFFFBF',
                'maxColor' => '#006837'

            );

            $highchartsRender['series'] = array(
                array(
                    'type' => 'treemap',
                    'layoutAlgorithm' => 'squarified',
                    'allowDrillToNode' => true,
                    'animationLimit' => 1000,
                    'dataLabels' => [
                        'enabled' => false
                    ],
                    'drillUpButton' => [
                        'text' => '< Back'
                    ],
                    'levelIsConstant' => false,
                    'levels' => [
                        [
                            'level' => 1,
                            'dataLabels' => [
                                'enabled' => true
                            ],
                            'borderWidth' => 0,
                            'states' => [
                                'hover' => [
                                    'brightness' => 0.1
                                ]
                            ]
                        ],

                    ],
                    'data' => $data,
                )
            );

            unset($highchartsRender['xAxis']);
        }

        if (!in_array(
            $this->_type,
            array(
                'highcharts_pie_chart',
                'highcharts_pie_with_gradient_chart',
                'highcharts_donut_chart',
                'highcharts_3d_pie_chart',
                'highcharts_3d_donut_chart',
                'highcharts_treemap_chart',
                'highcharts_treemap_level_chart'
            )
        )
        ) {
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if ($i == 0) {
                    $highchartsRender['xAxis']['categories'] = array();
                } else {
                    $seriesEntry = array(
                        'type' => isset($this->_render_data['options']['series'][$i - 1]['type']) ?
                            $this->_render_data['options']['series'][$i - 1]['type'] : '',
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'color' => isset($this->_render_data['options']['series'][$i - 1]['color']) ?
                            $this->_render_data['options']['series'][$i - 1]['color'] : '',
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'data' => array()
                    );

                    if ($this->_type == 'highcharts_polar_chart' || $this->_type == 'highcharts_spiderweb_chart') {
                        $seriesEntry['pointPlacement'] = 'on';
                    }

                    if ( $this->getSeriesType() != '') {
                        $seriesEntry['yAxis'] = isset($this->_render_data['options']['series'][$i - 1]['yAxis']) ? $this->_render_data['options']['series'][$i - 1]['yAxis'] : 0;

                        if (isset($this->_render_data['options']['series'][$i - 1]['yAxis']) && $this->_render_data['options']['series'][$i - 1]['yAxis']) {
                            $yAxis = [
                                'title' => [
                                    'text' => $this->_render_data['series'][$i - 1]['label']
                                ],
                                'opposite' => true
                            ];
                        } else {
                            $yAxis = [];
                        }

                        $highchartsRender['yAxis'][] = $yAxis;

                    }
                }
                foreach ($this->_render_data['rows'] as $row) {
                    if ($i == 0) {
                        if ($this->_type != 'highcharts_scatter_plot' || ($this->_type == 'highcharts_scatter_plot' && $this->_render_data['columns'][0]['type'] != 'number') ) {
                            $highchartsRender['xAxis']['categories'][] = $row[$i];
                        }
                    } else {
                         if ($this->_type == 'highcharts_scatter_plot' && ($this->_render_data['columns'][0]['type'] == 'number') ) {
                             $seriesEntry['data'][] = [$row[$i-1],$row[$i]];
                         } else {
                             $seriesEntry['data'][] = $row[$i];
                         }
                    }
                }
                if ($i != 0) {
                    $highchartsRender['series'][] = $seriesEntry;
                }
            }
        } else {
            if (
            in_array(
                $this->_type,
                array(
                    'highcharts_pie_chart',
                    'highcharts_pie_with_gradient_chart',
                    'highcharts_donut_chart',
                    'highcharts_3d_pie_chart',
                    'highcharts_3d_donut_chart'
                )
            )
            ) {
                $seriesEntry = array(
                    'type' => 'pie',
                    'data' => array()
                );
                $highchartsRender['series'] = array(
                    array(
                        'type' => 'pie',
                        'name' => $this->_render_data['columns'][1]['label'],
                        'data' => $this->_render_data['rows']
                    )
                );
                unset($highchartsRender['xAxis']);
            }
        }
        $this->_highcharts_render_data['wdtNumberFormat'] = get_option('wdtNumberFormat');
        $this->_highcharts_render_data['options'] = $highchartsRender;
        $this->_highcharts_render_data['type'] = $this->getType();
        if ($this->_follow_filtering) {
            if (isset($this->_render_data['column_indexes'])) {
                $this->_highcharts_render_data['column_indexes'] = $this->_render_data['column_indexes'];
            }
        }

        // Chart
        if (!$this->_responsiveWidth) {
            $this->_highcharts_render_data['width'] = $this->getWidth();
        }
        $this->_highcharts_render_data['height'] = $this->getHeight();
        $this->_highcharts_render_data['options']['chart']['backgroundColor'] = $this->getBackgroundColor();
        $this->_highcharts_render_data['options']['chart']['borderWidth'] = (int)$this->getBorderWidth();
        $this->_highcharts_render_data['options']['chart']['borderColor'] = $this->getBorderColor();
        $this->_highcharts_render_data['options']['chart']['borderRadius'] = (int)$this->getBorderRadius();
        $this->_highcharts_render_data['options']['chart']['zoomType'] = $this->getZoomType();
        $this->_highcharts_render_data['options']['chart']['panning'] = $this->getPanning();
        $this->_highcharts_render_data['options']['chart']['panKey'] = $this->getPanKey();
        $this->_highcharts_render_data['options']['chart']['plotBackgroundColor'] = $this->getPlotBackgroundColor();
        $this->_highcharts_render_data['options']['chart']['plotBackgroundImage'] = $this->getPlotBackgroundImage();
        $this->_highcharts_render_data['options']['chart']['plotBorderColor'] = $this->getPlotBorderColor();
        $this->_highcharts_render_data['options']['chart']['plotBorderWidth'] = $this->getPlotBorderWidth();

        // Axes
        if ($this->_type == 'highcharts_spiderweb_chart') {
            $this->_highcharts_render_data['options']['xAxis']['tickmarkPlacement'] = 'on';
            $this->_highcharts_render_data['options']['xAxis']['lineWidth'] = 0;
        }

        if (!$this->_show_grid) {
            $this->_highcharts_render_data['options']['xAxis']['lineWidth'] = 0;
            $this->_highcharts_render_data['options']['xAxis']['minorGridLineWidth'] = 0;
            $this->_highcharts_render_data['options']['xAxis']['lineColor'] = 'transparent';
            $this->_highcharts_render_data['options']['xAxis']['minorTickLength'] = 0;
            $this->_highcharts_render_data['options']['xAxis']['tickLength'] = 0;
            if ( $this->getSeriesType() != '') {
                $this->_highcharts_render_data['options']['yAxis'][0]['lineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis'][0]['gridLineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis'][0]['minorGridLineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis'][0]['lineColor'] = 'transparent';
                $this->_highcharts_render_data['options']['yAxis'][0]['labels'] = array('enabled' => false);
                $this->_highcharts_render_data['options']['yAxis'][0]['minorTickLength'] = 0;
                $this->_highcharts_render_data['options']['yAxis'][0]['tickLength'] = 0;
            } else {
                $this->_highcharts_render_data['options']['yAxis']['lineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis']['gridLineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis']['minorGridLineWidth'] = 0;
                $this->_highcharts_render_data['options']['yAxis']['lineColor'] = 'transparent';
                $this->_highcharts_render_data['options']['yAxis']['labels'] = array('enabled' => false);
                $this->_highcharts_render_data['options']['yAxis']['minorTickLength'] = 0;
                $this->_highcharts_render_data['options']['yAxis']['tickLength'] = 0;
            }
        }
        if ($this->getSeriesType() != '') {
            $this->_highcharts_render_data['options']['yAxis'][0]['gridLineDashStyle'] = $this->getHighchartsLineDashStyle();
        } else {
            $this->_highcharts_render_data['options']['yAxis']['gridLineDashStyle'] = $this->getHighchartsLineDashStyle();
        }
        if (!empty($this->_render_data['options']['hAxis']['title'])) {
            $this->_highcharts_render_data['options']['xAxis']['title']['text'] = $this->_render_data['options']['hAxis']['title'];
        }
        $this->_highcharts_render_data['options']['xAxis']['crosshair'] = $this->isHorizontalAxisCrosshair();
        if (!empty($this->_render_data['options']['vAxis']['title'])) {
            if ($this->getSeriesType() != '') {
                $this->_highcharts_render_data['options']['yAxis'][0]['title']['text'] = $this->_render_data['options']['vAxis']['title'];
            } else {
                $this->_highcharts_render_data['options']['yAxis']['title']['text'] = $this->_render_data['options']['vAxis']['title'];
            }
        }
        if ($this->getSeriesType() != '') {
            $this->_highcharts_render_data['options']['yAxis'][0]['crosshair'] = $this->isVerticalAxisCrosshair();
        } else {
            $this->_highcharts_render_data['options']['yAxis']['crosshair'] = $this->isVerticalAxisCrosshair();
        }

        if ($this->getVerticalAxisMin() != '') {
            if ($this->getSeriesType() != '') {
                $this->_highcharts_render_data['options']['yAxis'][0]['min'] = $this->getVerticalAxisMin();
            } else {
                $this->_highcharts_render_data['options']['yAxis']['min'] = $this->getVerticalAxisMin();
            }
        }
        if ($this->getVerticalAxisMax() != '') {
            if ($this->getSeriesType() != '') {
                $this->_highcharts_render_data['options']['yAxis'][0]['max'] = $this->getVerticalAxisMax();
            } else {
                $this->_highcharts_render_data['options']['yAxis']['max'] = $this->getVerticalAxisMax();
            }
        }
        $this->_highcharts_render_data['options']['chart']['inverted'] = $this->isInverted();

        // Title
        if ($this->_show_title) {
            $this->_highcharts_render_data['options']['title']['text'] = $this->getTitle();
        } else {
            $this->_highcharts_render_data['options']['title']['text'] = '';
        }
        $this->_highcharts_render_data['options']['title']['floating'] = $this->isTitleFloating();
        $this->_highcharts_render_data['options']['title']['align'] = $this->getTitleAlign();
        $this->_highcharts_render_data['options']['subtitle']['text'] = $this->getSubtitle();
        $this->_highcharts_render_data['options']['subtitle']['align'] = $this->getSubtitleAlign();

        // Tooltip
        $this->_highcharts_render_data['options']['tooltip']['enabled'] = $this->isTooltipEnabled();
        $this->_highcharts_render_data['options']['tooltip']['backgroundColor'] = ($this->getTooltipBackgroundColor() != '') ? $this->getTooltipBackgroundColor() : 'rgba(255, 255, 255, 0.85)';
        $this->_highcharts_render_data['options']['tooltip']['borderWidth'] = $this->getTooltipBorderWidth();
        $this->_highcharts_render_data['options']['tooltip']['borderColor'] = $this->getTooltipBorderColor();
        $this->_highcharts_render_data['options']['tooltip']['borderRadius'] = $this->getTooltipBorderRadius();
        $this->_highcharts_render_data['options']['tooltip']['shared'] = $this->isTooltipShared();
        $this->_highcharts_render_data['options']['tooltip']['valuePrefix'] = $this->getTooltipValuePrefix();
        $this->_highcharts_render_data['options']['tooltip']['valueSuffix'] = $this->getTooltipValueSuffix();

        // Legend
        if ($this->_highcharts_render_data['type'] == 'highcharts_treemap_level_chart') {
            $this->_highcharts_render_data['options']['legend']['enabled']= false;
        } else {
            $this->_highcharts_render_data['options']['legend']['enabled'] = $this->getShowLegend();
        }
        $this->_highcharts_render_data['options']['legend']['backgroundColor'] = $this->getLegendBackgroundColor();
        $this->_highcharts_render_data['options']['legend']['title']['text'] = $this->getLegendTitle();
        $this->_highcharts_render_data['options']['legend']['layout'] = $this->getLegendLayout();
        $this->_highcharts_render_data['options']['legend']['align'] = $this->getLegendAlign();
        $this->_highcharts_render_data['options']['legend']['verticalAlign'] = $this->getLegendVerticalAlign();
        $this->_highcharts_render_data['options']['legend']['borderWidth'] = $this->getLegendBorderWidth();
        $this->_highcharts_render_data['options']['legend']['borderColor'] = $this->getLegendBorderColor();
        $this->_highcharts_render_data['options']['legend']['borderRadius'] = $this->getLegendBorderRadius();

        // Exporting
        $this->_highcharts_render_data['options']['exporting']['enabled'] = $this->getExporting();
        $this->_highcharts_render_data['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled'] = $this->getExportingDataLabels();
        $this->_highcharts_render_data['options']['exporting']['filename'] = $this->getExportingFileName();
        $this->_highcharts_render_data['options']['exporting']['width'] = $this->getExportingWidth();
        $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['align'] = $this->getExportingButtonAlign();
        $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['verticalAlign'] = $this->getExportingButtonVerticalAlign();
        if ($this->getExportingButtonColor() == '') {
            $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['symbolStroke'] = '#666';
        } else {
            $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['symbolStroke'] = $this->getExportingButtonColor();
        }
        $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['text'] = $this->getExportingButtonText();
        if ( $this->getExporting() && in_array($this->getType(), ['highcharts_treemap_level_chart', 'highcharts_treemap_chart']) ){
            $this->_highcharts_render_data['options']['exporting']['buttons']['contextButton']['menuItems'] = ['viewFullscreen', 'printChart', 'separator', 'downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG'];
        }

        // Credits
        $this->_highcharts_render_data['options']['credits']['enabled'] = $this->getCredits();
        $this->_highcharts_render_data['options']['credits']['href'] = $this->getCreditsHref();
        $this->_highcharts_render_data['options']['credits']['text'] = $this->getCreditsText();

        $this->_highcharts_render_data = apply_filters('wpdatatables_filter_highcharts_render_data', $this->_highcharts_render_data, $this->getId(), $this);

    }

    /**
     * Prepare ChartJS Data and Options
     */
    public function prepareChartJSRender() {

        $seriesEntry = array();

        $chartToSetOptionBeginAtZero = array(
            'chartjs_pie_chart',
            'chartjs_radar_chart',
            'chartjs_doughnut_chart',
            'chartjs_polar_area_chart',
            'chartjs_bubble_chart'
        );

        $colors = array(
            '#ff6384',
            '#36a2eb',
            '#ffce56',
            '#4bc0c0',
            '#9966ff',
            '#ff9f40',
            '#a6cee3',
            '#6a3d9a',
            '#b15928',
            '#fb9a99',
            '#0476e8',
            '#49C172',
            '#EA5E57',
            '#FFF458',
            '#BFEB54',
        );

        if ($this->_render_data['series']) {
            if (!empty($this->_chartjs_render_data['options']['data']['datasets'])) {
                $this->_chartjs_render_data['options']['data']['datasets'] = array();
            }
            if (in_array(
                $this->_type,
                array(
                    'chartjs_line_chart',
                    'chartjs_area_chart',
                    'chartjs_stacked_area_chart',
                    'chartjs_column_chart',
                    'chartjs_radar_chart'
                )
            )) {
                // Series and Categories
                for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                    if ($i == 0) {
                        $this->_chartjs_render_data['options']['data']['labels'] = array();
                    } else {
                        $seriesEntry = array(
                            'label' => $this->_render_data['series'][$i - 1]['label'],
                            'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                            'backgroundColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                WDTTools::hex2rgba($this->_render_data['options']['series'][$i - 1]['color'], 0.2) : WDTTools::hex2rgba($colors[($i - 1) % 10], 0.2),
                            'borderColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                $this->_render_data['options']['series'][$i - 1]['color'] : $colors[($i - 1) % 10],
                            'borderWidth' => 1,
                            'data' => array(),
                            'lineTension' => ($this->getCurveType()) ? 0.4 : 0,
                            'fill' => $this->_type != 'chartjs_line_chart'
                        );
                    }
                    foreach ($this->_render_data['rows'] as $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['labels'][] = $row[$i];
                        } else {
                            $seriesEntry['data'][] = $row[$i];
                        }
                    }
                    if ($i != 0) {
                        $this->_chartjs_render_data['options']['data']['datasets'][] = $seriesEntry;
                    }
                }
            } else if (in_array(
                $this->_type,
                array(
                    'chartjs_polar_area_chart',
                    'chartjs_pie_chart',
                    'chartjs_doughnut_chart'
                )
            )) {
                // Series and Categories
                for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                    if ($i == 0) {
                        $this->_chartjs_render_data['options']['data']['labels'] = array();
                    } else {
                        $seriesEntry = array(
                            'label' => $this->_render_data['series'][$i - 1]['label'],
                            'backgroundColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                $this->_render_data['options']['series'][$i - 1]['color'] : $colors,
                            'borderWidth' => 1,
                            'data' => array()
                        );
                    }
                    foreach ($this->_render_data['rows'] as $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['labels'][] = $row[$i];
                        } else {
                            $seriesEntry['data'][] = $row[$i];
                        }
                    }
                    if ($i != 0) {
                        $this->_chartjs_render_data['options']['data']['datasets'][] = $seriesEntry;
                    }
                }
            } else if ($this->_type = 'chartjs_bubble_chart') {
                // Series and Categories
                for ($i = 0; $i < 2; $i++) {
                    foreach ($this->_render_data['rows'] as $key => $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['datasets'][$key]['label'] = $row[$i];
                        }
                        if ($i == 1) {
                            $seriesEntry = array(
                                'data' => array()
                            );
                            $seriesEntry['backgroundColor'] = $colors[$key  % 10];
                            $seriesEntry['hoverBackgroundColor'] = $colors[$key % 10];
                            $seriesEntry['data'][0]['x'] = $row[$i];
                            $seriesEntry['data'][0]['y'] = $row[$i + 1];
                            $seriesEntry['data'][0]['r'] = $row[$i + 2];
                            $this->_chartjs_render_data['options']['data']['datasets'][$key] = array_merge($this->_chartjs_render_data['options']['data']['datasets'][$key], $seriesEntry);
                        }
                    }
                }
            }
        }

        // Column Indexes
        if ($this->_follow_filtering) {
            if (isset($this->_render_data['column_indexes'])) {
                $this->_chartjs_render_data['column_indexes'] = $this->_render_data['column_indexes'];
            }
        }

        // Chart
        $this->_chartjs_render_data['configurations']['type'] = $this->getType();
        $this->_chartjs_render_data['configurations']['container']['height'] = $this->getHeight();
        if ($this->isResponsiveWidth()) {
            $this->_chartjs_render_data['configurations']['container']['width'] = 0;
            $this->_chartjs_render_data['options']['options']['maintainAspectRatio'] = true;
        } else {
            $this->_chartjs_render_data['configurations']['container']['width'] = $this->getWidth();
            $this->_chartjs_render_data['options']['options']['maintainAspectRatio'] = false;
        }
        $this->_chartjs_render_data['configurations']['canvas']['backgroundColor'] = $this->getBackgroundColor();
        $this->_chartjs_render_data['configurations']['canvas']['borderWidth'] = $this->getBorderWidth();
        $this->_chartjs_render_data['configurations']['canvas']['borderColor'] = $this->getBorderColor();
        $this->_chartjs_render_data['configurations']['canvas']['borderRadius'] = $this->getBorderRadius();
        $this->_chartjs_render_data['options']['globalOptions']['defaultFontSize'] = $this->getFontSize();
        $this->_chartjs_render_data['options']['globalOptions']['defaultFontFamily'] = $this->getFontName();
        $this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle'] = $this->getFontStyle();
        $this->_chartjs_render_data['options']['globalOptions']['defaultFontColor'] = $this->getFontColor();

        // Axes
        if (!$this->_show_grid) {
            $this->_chartjs_render_data['options']['options']['scales']['xAxes'][0]['display'] = false;
            $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['display'] = false;
        }
        $this->_chartjs_render_data['options']['options']['scales']['xAxes'][0]['scaleLabel']['display'] = true;
        $this->_chartjs_render_data['options']['options']['scales']['xAxes'][0]['scaleLabel']['labelString'] = $this->getMajorAxisLabel();
        $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['scaleLabel']['display'] = true;
        $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['scaleLabel']['labelString'] = $this->getMinorAxisLabel();
        if ($this->getVerticalAxisMin() != '') {
            $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['ticks']['min'] = intval($this->getVerticalAxisMin());
        } else {
            if (in_array($this->_type, $chartToSetOptionBeginAtZero)){
                $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['ticks']['beginAtZero'] = true;
                $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['ticks']['min'] = 0;
            } else {
                $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['ticks']['beginAtZero'] = false;
            }
        }
        if ($this->getVerticalAxisMax() != '') {
            $this->_chartjs_render_data['options']['options']['scales']['yAxes'][0]['ticks']['max'] = intval($this->getVerticalAxisMax());
        }

        // Title
        if ($this->_show_title) {
            $this->_chartjs_render_data['options']['options']['title']['display'] = true;
            $this->_chartjs_render_data['options']['options']['title']['text'] = $this->getTitle();
        } else {
            $this->_chartjs_render_data['options']['options']['title']['display'] = false;
        }
        $this->_chartjs_render_data['options']['options']['title']['position'] = $this->getTitlePosition();
        $this->_chartjs_render_data['options']['options']['title']['fontFamily'] = $this->getTitleFontName();
        $this->_chartjs_render_data['options']['options']['title']['fontStyle'] = $this->getTitleFontStyle();
        $this->_chartjs_render_data['options']['options']['title']['fontColor'] = ($this->getTitleFontColor() != '') ? $this->getTitleFontColor() : '#666';

        // Tooltip
        $this->_chartjs_render_data['options']['options']['tooltips']['enabled'] = $this->isTooltipEnabled();
        if ($this->isTooltipShared()) {
            $this->_chartjs_render_data['options']['options']['tooltips']['mode'] = 'label';
        } else {
            $this->_chartjs_render_data['options']['options']['tooltips']['mode'] = 'single';
        }
        $this->_chartjs_render_data['options']['options']['tooltips']['backgroundColor'] = strpos($this->getTooltipBackgroundColor(), 'rgba') !== false ? $this->getTooltipBackgroundColor() : WDTTools::hex2rgba($this->getTooltipBackgroundColor(), 0.8);
        $this->_chartjs_render_data['options']['options']['tooltips']['cornerRadius'] = $this->getTooltipBorderRadius();

        // Legend
        $this->_chartjs_render_data['options']['options']['legend']['display'] = $this->getShowLegend();
        $this->_chartjs_render_data['options']['options']['legend']['position'] = $this->getLegendPositionCjs();

        $this->_chartjs_render_data = apply_filters('wpdatatables_filter_chartjs_render_data', $this->_chartjs_render_data, $this->getId(), $this);

    }

    /**
     * Prepare Apex Charts Data and Options
     */
    public function prepareApexchartsRender()
    {
        $apexchartsRender = array(
            'series' => array(),
            'labels' => array(),
            'title' => array(
                'text' => $this->_show_title ? $this->getTitle() : '',
                'align' => 'center',
            ),
            'xaxis' => array(
              'type' => 'category',
            ),
            'colors' => array(),
            'fill' => array(
              'image' => array(
                  'src' => array(),
              ),
            ),
            'orig_header' => array(),
        );

        $this->_apexcharts_render_data['wdtNumberFormat'] = get_option('wdtNumberFormat');
        $this->_apexcharts_render_data['wdtDecimalPlaces'] = get_option('wdtDecimalPlaces');
        $this->_apexcharts_render_data['type'] = $this->getType();
        if ($this->_follow_filtering) {
            if (isset($this->_render_data['column_indexes'])) {
                $this->_apexcharts_render_data['column_indexes'] = $this->_render_data['column_indexes'];
            }
        }

        //default Apexcharts color palette
        $colors = ['#775DD0', '#008FFB', '#00E396', '#FEB019', '#FF4560', '#B6D05D', '#FB6C00', '#E3004D', '#45FFE4'];
        $colorsNum = count($colors);
        //Data
        if (!in_array(
            $this->_type,
            array(
                'apexcharts_pie_chart',
                'apexcharts_pie_with_gradient_chart',
                'apexcharts_donut_chart',
                'apexcharts_donut_with_gradient_chart',
                'apexcharts_radialbar_chart',
                'apexcharts_radialbar_gauge_chart',
                'apexcharts_radar_chart',
            )
        )
        ) {
            $apexchartsRender['fill']['type'] = array();
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if($i == 0) {
                    $apexchartsRender['xaxis']['categories'] = array();
                    foreach ($this->_render_data['rows'] as $row) {
                        array_push($apexchartsRender['xaxis']['categories'], $row[0]);
                    }
                } else {
                    $seriesColor =  isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    $seriesImage = (isset($this->_render_data['options']['series'][$i - 1]['chart_image']) && $this->_render_data['options']['series'][$i - 1]['chart_image'] !== '') ? $this->_render_data['options']['series'][$i - 1]['chart_image'] : '';
                    $fillType = $seriesImage == '' ? 'solid' : 'image';
                    $seriesEntry = array(
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'data' => array(),
                        'color' => $seriesColor,
                        'chart_image' => $seriesImage,
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                    );

                    if (!in_array(
                        $this->_type,
                        array('apexcharts_grouped_bar_chart', 'apexcharts_stacked_bar_chart', 'apexcharts_100_stacked_bar_chart'))
                    )
                        $seriesEntry['type'] = isset($this->_render_data['options']['series'][$i - 1]['type']) ?
                        $this->_render_data['options']['series'][$i - 1]['type'] : $this->getApexChartType($this->getType());

                    if(isset($this->_render_data['series'][$i - 1]['opposite'])) $seriesEntry['opposite'] = $this->_render_data['series'][$i - 1]['opposite'];

                    foreach ($this->_render_data['rows'] as $row) {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    array_push($apexchartsRender['fill']['type'], $fillType);
                    array_push($apexchartsRender['fill']['image']['src'], $seriesImage);
                    array_push($apexchartsRender['colors'], $seriesColor);
                }
                if ($i != 0) {
                    $apexchartsRender['series'][] = $seriesEntry;
                }
            }
        } else if ($this->_type == 'apexcharts_radar_chart') {
            $apexchartsRender['options']['grid']['show'] = false;
            $apexchartsRender['fill']['type'] = array();
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if($i == 0) {
                    $apexchartsRender['xaxis']['categories'] = array();
                    foreach ($this->_render_data['rows'] as $row) {
                        array_push($apexchartsRender['xaxis']['categories'], $row[0]);
                    }
                } else {
                    $seriesColor =  isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    $seriesImage = (isset($this->_render_data['options']['series'][$i - 1]['chart_image']) && $this->_render_data['options']['series'][$i - 1]['chart_image'] !== '') ? $this->_render_data['options']['series'][$i - 1]['chart_image'] : '';
                    $fillType = $seriesImage == '' ? 'solid' : 'image';
                    $seriesEntry = array(
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'data' => array(),
                        'color' => $seriesColor,
                        'chart_image' => $seriesImage,
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                    );

                    if (isset($this->_render_data['options']['series'][$i - 1]['type'])) $seriesEntry['type'] = $this->_render_data['options']['series'][$i - 1]['type'];

                    foreach ($this->_render_data['rows'] as $row) {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    array_push($apexchartsRender['fill']['type'], $fillType);
                    array_push($apexchartsRender['fill']['image']['src'], $seriesImage);
                    array_push($apexchartsRender['colors'], $seriesColor);
                }
                if ($i != 0) {
                    $apexchartsRender['series'][] = $seriesEntry;
                }
            }
        } else {
            $isRadialType = $this->_type == 'apexcharts_radialbar_chart' || $this->_type == 'apexcharts_radialbar_gauge_chart';
            unset($apexchartsRender['xaxis']);
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                $seriesEntry = array(
                    'orig_header' => $this->_render_data['columns'][$i]['orig_header'],
                    'data' => array(),
                );
                $j = 0;
                foreach ($this->_render_data['rows'] as $row) {
                    if ($i == 0) {
                        $apexchartsRender['labels'][] = $row[$i];
                    } else {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    if ($isRadialType) {
                        $apexchartsRender['colors'][$j] =  $colors[($j + 1) % $colorsNum];
                        $j++;
                    }
                }

                if ($i != 0) {
                    $apexchartsRender['series'] = $seriesEntry['data'];
                    array_push($apexchartsRender['orig_header'], $seriesEntry['orig_header']);
                    $seriesColor =  $this->_render_data['series'][$i - 1]['color'] != '' ? $this->_render_data['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    if(!$isRadialType) {
                        array_push($apexchartsRender['colors'], $seriesColor);
                    }
                }
            }
        }

        $this->_apexcharts_render_data['options'] = $apexchartsRender;

        // Chart
        if (!$this->_responsiveWidth) {
            $this->_apexcharts_render_data['options']['chart']['width'] = $this->getWidth();
        }
        $this->_apexcharts_render_data['options']['chart']['height'] = $this->getHeight();
        $this->_apexcharts_render_data['options']['chart']['animations']['enabled'] = $this->isEnableAnimation();
        $this->_apexcharts_render_data['options']['chart']['background'] = ($this->getPlotBackgroundImage() != 'undefined' && $this->getPlotBackgroundImage() != '') ? $this->getPlotBackgroundImage() : $this->getBackgroundColor();
        if (in_array( $this->_type, array( 'apexcharts_pie_chart', 'apexcharts_pie_with_gradient_chart',
                'apexcharts_donut_chart', 'apexcharts_donut_with_gradient_chart'))) {
            $this->_apexcharts_render_data['options']['theme']['monochrome'] = array();
            $this->_apexcharts_render_data['options']['theme']['monochrome']['enabled'] = $this->isMonochrome();
            $this->_apexcharts_render_data['options']['theme']['monochrome']['color'] = $this->getMonochromeColor();
            if($this->_enable_color_palette) {
                $this->_apexcharts_render_data['options']['theme']['monochrome']['enabled'] = false;
                $this->_apexcharts_render_data['options']['theme']['palette'] = $this->getColorPalette();
            }
        }
        $this->_apexcharts_render_data['options']['plotOptions']['bar'] = array();
        $this->_apexcharts_render_data['options']['plotOptions']['bar']['horizontal'] = false;
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar'] = array();
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['startAngle'] = $this->getStartAngle();
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['endAngle'] = $this->getEndAngle();
        $this->_apexcharts_render_data['options']['chart']['foreColor'] = $this->getTextColor();
        $this->_apexcharts_render_data['options']['chart']['zoom']['type'] = $this->getZoomType();
        if ($this->getLineBackgroundImage() != '') {
            $this->_apexcharts_render_data['options']['fill']['image']['src'] = $this->getLineBackgroundImage();
            $this->_apexcharts_render_data['options']['fill']['type'] = 'image';
        }
        if ($this->isShowDataLabels()) {
            $this->_apexcharts_render_data['options']['dataLabels']['enabled'] = $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['dataLabels']['show'] = true;
            $this->_apexcharts_render_data['options']['plotOptions']['pie'] = array();
            $this->_apexcharts_render_data['options']['plotOptions']['pie']['dataLabels'] = array();
            $this->_apexcharts_render_data['options']['plotOptions']['pie']['dataLabels']['minAngleToShowLabel'] = 0;
        } else {
            $this->_apexcharts_render_data['options']['dataLabels']['enabled'] = $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['dataLabels']['show'] = false;
        }
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['enabled'] = $this->isEnableDropshadow();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['blur'] = $this->getDropshadowBlur();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['opacity'] = $this->getDropshadowOpacity();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['color'] = $this->getDropshadowColor();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['top'] = $this->getDropshadowTop();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['left'] = $this->getDropshadowLeft();

        // Axes
        $this->_apexcharts_render_data['options']['grid']['show'] = !!$this->_show_grid;
        $this->_apexcharts_render_data['options']['grid']['borderColor'] = $this->getGridColor() == '' ? '#000000' : $this->getGridColor();
        $this->_apexcharts_render_data['options']['grid']['strokeDashArray'] = $this->getGridStroke();
        $this->_apexcharts_render_data['options']['grid']['position'] = $this->getGridPosition();
        $this->_apexcharts_render_data['options']['grid']['xaxis'] = $this->_apexcharts_render_data['options']['grid']['yaxis'] = array();
        $gridAxis = $this->getGridAxes();
        $this->_apexcharts_render_data['options']['grid']['xaxis']['lines']['show'] = in_array('xaxis', $gridAxis);
        $this->_apexcharts_render_data['options']['grid']['yaxis']['lines']['show'] = in_array('yaxis', $gridAxis);
        $this->_apexcharts_render_data['options']['xaxis']['crosshairs'] = array();
        $this->_apexcharts_render_data['options']['xaxis']['title'] = array();
        $this->_apexcharts_render_data['options']['xaxis']['crosshairs']['show'] = $this->isHorizontalAxisCrosshair();
        $this->_apexcharts_render_data['options']['xaxis']['title']['text'] = $this->getMajorAxisLabel();
        $i = 0;
        if (!in_array( $this->_type, array('apexcharts_grouped_bar_chart', 'apexcharts_100_stacked_bar_chart',
                'apexcharts_stacked_bar_chart', 'apexcharts_radar_chart', 'apexcharts_pie_chart',
                'apexcharts_pie_with_gradient_chart', 'apexcharts_donut_chart', 'apexcharts_donut_with_gradient_chart',
                'apexcharts_radialbar_chart', 'apexcharts_radialbar_gauge_chart'))) {
            foreach ($apexchartsRender['series'] as $series ) {
                $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $apexchartsRender['series'][0]['name'];
                $this->_apexcharts_render_data['options']['yaxis'][$i]['title']['text'] = $series['label'];
                if ($this->getMinorAxisLabel()) $this->_apexcharts_render_data['options']['yaxis'][0]['title']['text'] = $this->getMinorAxisLabel();
                if($i != 0) $this->_apexcharts_render_data['options']['yaxis'][$i]['show'] = false;
                $this->_apexcharts_render_data['options']['yaxis'][$i]['crosshairs']['show'] = $this->_apexcharts_render_data['options']['yaxis'][$i]['tooltip']['enabled'] = $this->isVerticalAxisCrosshair();
                $this->_apexcharts_render_data['options']['yaxis'][$i]['reversed'] = $this->isReversed();
                if ($this->getVerticalAxisMin() !== '') {
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['min'] = (int)$this->getVerticalAxisMin();
                }
                if ($this->getVerticalAxisMax() !== '') {
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['max'] = (int)$this->getVerticalAxisMax();
                }
                if ($i != 0 && isset($this->_render_data['options']['series'][$i]['yAxis'])) {
                    $showOppositeAxis = (bool)$this->_render_data['options']['series'][$i]['yAxis'];
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['opposite'] = $showOppositeAxis;
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['show'] = $showOppositeAxis;
                    if ($showOppositeAxis && isset($this->_apexcharts_render_data['options']['yaxis'][$i]['orig_header'])) {
                        $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $this->_apexcharts_render_data['options']['yaxis'][$i]['orig_header'];
                    } else if(isset($this->_apexcharts_render_data['options']['yaxis'][0]['seriesName'])) {
                        $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $this->_apexcharts_render_data['options']['yaxis'][0]['seriesName'];
                    }
                }
                $this->_apexcharts_render_data['options']['yaxis'][$i]['tickAmount'] = $this->getTickAmount() ?: 0;
                $i++;
            }
        } else if (in_array( $this->_type, array('apexcharts_grouped_bar_chart','apexcharts_100_stacked_bar_chart',
                'apexcharts_stacked_bar_chart','apexcharts_100_stacked_column_chart', 'apexcharts_stacked_column_chart'))) {
            $this->_apexcharts_render_data['options']['yaxis']['seriesName'] = $apexchartsRender['series'][0]['name'];
            $this->_apexcharts_render_data['options']['yaxis']['title']['text'] = $this->getMinorAxisLabel() ?: $this->_render_data["series"][0]['label'];
            if ($this->getVerticalAxisMin() !== '') {
                $this->_apexcharts_render_data['options']['yaxis']['min'] = (int)$this->getVerticalAxisMin();
            }
            if ($this->getVerticalAxisMax() !== '') {
                $this->_apexcharts_render_data['options']['yaxis']['max'] = (int)$this->getVerticalAxisMax();
            }
            $this->_apexcharts_render_data['options']['yaxis']['reversed'] = $this->isReversed();
            if($this->_type != 'apexcharts_100_stacked_column_chart')
                $this->_apexcharts_render_data['options']['yaxis']['tickAmount'] = $this->getTickAmount() ?: 0;
        }
        $this->_apexcharts_render_data['options']['markers']['size'] = $this->getMarkerSize();
        $this->_apexcharts_render_data['options']['markers']['hover']['sizeOffset'] = 1;
        $this->_apexcharts_render_data['options']['stroke']['width'] = $this->getStrokeWidth();
        // Title
        if ($this->_show_title) {
            $this->_apexcharts_render_data['options']['title']['text'] = $this->getTitle();
        } else {
            $this->_apexcharts_render_data['options']['title']['text'] = '';
        }
        $this->_apexcharts_render_data['options']['title']['floating'] = $this->isTitleFloating();
        $this->_apexcharts_render_data['options']['title']['align'] = $this->getTitleAlign();
        $this->_apexcharts_render_data['options']['title']['style']['fontWeight'] = $this->getTitleFontStyle();
        $this->_apexcharts_render_data['options']['subtitle']['text'] = $this->getSubtitle();
        $this->_apexcharts_render_data['options']['subtitle']['align'] = $this->getSubtitleAlign();

        // Tooltip
        $this->_apexcharts_render_data['options']['tooltip']['enabled'] = $this->isTooltipEnabled();
        $this->_apexcharts_render_data['options']['tooltip']['shared'] = $this->isTooltipEnabled() && $this->getType() !== 'apexcharts_radar_chart';
        $this->_apexcharts_render_data['options']['tooltip']['followCursor'] = $this->isFollowCursor();
        $this->_apexcharts_render_data['options']['tooltip']['fillSeriesColor'] = $this->isFillSeriesColor();
        $this->_apexcharts_render_data['options']['tooltip']['intersect'] = $this->getType() == 'apexcharts_radar_chart';

        //Toolbar
        $this->_apexcharts_render_data['options']['chart']['toolbar']['show'] = $this->isShowToolbar();
        $this->_apexcharts_render_data['options']['chart']['toolbar']['tools'] = array();
        $this->_apexcharts_render_data['options']['chart']['selection'] = array();
        $this->_apexcharts_render_data['options']['chart']['selection']['enabled'] = true;
        $allToolbarButtons = ['download', 'selection', 'zoom', 'zoomin', 'zoomout', 'pan'];
        $notShownToolbarButtons = array_diff( array_values($allToolbarButtons), $this->getToolbarButtons() );
        foreach ($allToolbarButtons as $button) {
            $this->_apexcharts_render_data['options']['chart']['toolbar']['tools'][$button] = !in_array($button, $notShownToolbarButtons);
        }

        $this->_apexcharts_render_data['options']['chart']['toolbar']['tools']['customIcons'] = array();
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['csv']['filename'] =
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['svg']['filename'] =
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['png']['filename'] = $this->getApexExportingFileName();

        // Legend
        $this->_apexcharts_render_data['options']['legend']['showForSingleSeries'] = true;
        $this->_apexcharts_render_data['options']['legend']['show'] = $this->getShowLegend();
        $this->_apexcharts_render_data['options']['legend']['position'] = $this->getLegendPositionCjs() != '' ? $this->getLegendPositionCjs() : 'bottom';

        $this->_apexcharts_render_data = apply_filters('wpdatatables_filter_apexcharts_render_data', $this->_apexcharts_render_data, $this->getId(), $this);
    }

    public function returnGoogleChartData() {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareGoogleChartsRender();
        return $this->_render_data;
    }

    public function returnHighChartsData() {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareHighchartsRender();
        return $this->_highcharts_render_data;
    }

    public function returnChartJSData() {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareChartJSRender();
        return $this->_chartjs_render_data;
    }

    public function returnApexChartsData() {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareApexchartsRender();
        return $this->_apexcharts_render_data;
    }

    public function returnData() {
        if ($this->getEngine() == 'google') {
            return $this->returnGoogleChartData();
        } else if ($this->getEngine() == 'highcharts') {
            return $this->returnHighChartsData();
        } else if ($this->getEngine() == 'chartjs') {
            return $this->returnChartJSData();
        } else if ($this->getEngine() == 'apexcharts') {
            return $this->returnApexChartsData();
        }
    }


    /**
     * Saves the chart data to DB
     * @global WPDB $wpdb
     */
    public function save() {
        global $wpdb;

        $this->prepareSeriesData();
        $this->shiftStringColumnUp();

        if ($this->_engine == 'google') {
            $this->prepareGoogleChartsRender();
        }

        if ($this->_engine == 'highcharts') {
            $this->prepareHighchartsRender();
        }

        if ($this->_engine == 'chartjs') {
            $this->prepareChartJSRender();
        }

        if ($this->_engine == 'apexcharts') {
            $this->prepareApexchartsRender();
        }

        $render_data = array(
            'selected_columns' => $this->getSelectedColumns(),
            'range_type' => $this->getRangeType(),
            'row_range' => $this->getRowRange(),
            'follow_filtering' => $this->getFollowFiltering(),
            'render_data' => $this->_render_data,
            'highcharts_render_data' => $this->_highcharts_render_data,
            'chartjs_render_data' => $this->_chartjs_render_data,
            'apexcharts_render_data' => $this->_apexcharts_render_data,
            'show_grid' => $this->_show_grid,
            'show_title' => $this->_show_title,
            'series_type' => $this->getSeriesType()
        );


        if (empty($this->_id)) {
            // This is a new chart

            $wpdb->insert(
                $wpdb->prefix . "wpdatacharts",
                array(
                    'wpdatatable_id' => $this->_wpdatatable_id,
                    'title' => $this->_title,
                    'engine' => $this->_engine,
                    'type' => $this->_type,
                    'json_render_data' => json_encode($render_data)
                )
            );

            $this->_id = $wpdb->insert_id;

        } else {
            // Updating the chart
            $wpdb->update(
                $wpdb->prefix . "wpdatacharts",
                array(
                    'wpdatatable_id' => $this->_wpdatatable_id,
                    'title' => $this->_title,
                    'engine' => $this->_engine,
                    'type' => $this->_type,
                    'json_render_data' => json_encode($render_data)
                ),
                array(
                    'id' => $this->_id
                )
            );

        }

    }

    public function getColumnIndexes() {
        foreach ($this->getSelectedColumns() as $columnKey) {
            $this->_render_data['column_indexes'][] = $this->_wpdatatable->getColumnHeaderOffset($columnKey);
        }
    }

    /**
     * Returns series type based on selected chart type
     * @return string
     */
    public function getApexChartType($type) {
        switch ($type) {
            case 'apexcharts_spline_area_chart':
            case 'apexcharts_stepline_area_chart':
            case 'apexcharts_basic_area_chart':
                $seriesType = 'area';
                break;
            case 'apexcharts_column_chart':
            case 'apexcharts_grouped_bar_chart':
            case 'apexcharts_stacked_bar_chart':
            case 'apexcharts_100_stacked_bar_chart':
            case 'apexcharts_stacked_column_chart':
            case 'apexcharts_100_stacked_column_chart':
                $seriesType = 'bar';
                break;
            case 'apexcharts_pie_with_gradient_chart':
            case 'apexcharts_pie_chart':
                $seriesType = 'pie';
                break;
            case 'apexcharts_donut_with_gradient_chart':
            case 'apexcharts_donut_chart':
                $seriesType = 'donut';
                break;
            case 'apexcharts_radar_chart':
                $seriesType = 'radar';
                break;
            case 'apexcharts_radialbar_gauge_chart':
            case 'apexcharts_radialbar_chart':
                $seriesType = 'radialBar';
                break;
            case 'apexcharts_polar_area_chart':
                $seriesType = 'polarArea';
                break;
            case 'apexcharts_straight_line_chart':
            case 'apexcharts_spline_chart':
            case 'apexcharts_stepline_chart':
            default:
                $seriesType = 'line';
                break;
        }

        return $seriesType;
    }

    /**
     * Determine if array consist only of null/empty values
     */
    function containsOnlyNull($array)
    {
        return empty(array_filter($array, function ($a) { return $a !== null && $a !== '';}));
    }

    /**
     * Return the shortcode
     */
    public function getShortCode() {
        if (!empty($this->_id)) {
            return '[wpdatachart id=' . $this->_id . ']';
        } else {
            return '';
        }
    }

    /**
     * Load from DB
     * @return bool
     */
    public function loadFromDB() {
        global $wpdb;

        if (empty($this->_id)) {
            return false;
        }

        // Load json data from DB
        $chartQuery = $wpdb->prepare(
            "SELECT * 
                        FROM " . $wpdb->prefix . "wpdatacharts 
                        WHERE id = %d",
            $this->_id
        );
        $chartData = $wpdb->get_row($chartQuery);

        if ($chartData === null) {
            return false;
        }

        $renderData = json_decode($chartData->json_render_data, true);
        $this->_render_data = $renderData['render_data'];
        if ($chartData->engine == 'highcharts') {
            if (!empty($renderData['highcharts_render_data'])) {
                $this->_highcharts_render_data = $renderData['highcharts_render_data'];
            }
        }

        if ($chartData->engine == 'chartjs') {
            if (!empty($renderData['chartjs_render_data'])) {
                $this->_chartjs_render_data = $renderData['chartjs_render_data'];
            }
        }

        if ($chartData->engine == 'apexcharts') {
            if (!empty($renderData['apexcharts_render_data'])) {
                $this->_apexcharts_render_data = $renderData['apexcharts_render_data'];
            }
        }

        $this->setTitle($chartData->title);
        $this->setEngine($chartData->engine);
        $this->setwpDataTableId($chartData->wpdatatable_id);
        $this->setType($chartData->type);
        if (!empty($renderData['series_type'])){
            $this->setSeriesType($renderData['series_type']);
        };
        $this->setSelectedColumns($renderData['selected_columns']);
        $this->setFollowFiltering($renderData['follow_filtering']);
        $this->setRangeType($renderData['range_type']);
        $this->setRowRange($renderData['row_range']);
        $this->setShowGrid(isset($renderData['show_grid']) ? $renderData['show_grid'] : false);
        $this->setShowTitle(isset($renderData['show_title']) ? $renderData['show_title'] : false);
        $this->setResponsiveWidth(isset($renderData['render_data']['options']['responsive_width']) ? (bool)$renderData['render_data']['options']['responsive_width'] : false);
        if (!empty($renderData['render_data']['options']['width'])) {
            $this->setWidth($renderData['render_data']['options']['width']);
        }
        $this->setHeight($renderData['render_data']['options']['height']);

        if ($chartData->engine == 'google') {
            // Chart
            $this->setBackgroundColor(isset($renderData['render_data']['options']['backgroundColor']['fill']) ? $renderData['render_data']['options']['backgroundColor']['fill'] : '');
            $this->setBorderWidth(isset($renderData['render_data']['options']['backgroundColor']['strokeWidth']));
            $this->setBorderColor(isset($renderData['render_data']['options']['backgroundColor']['stroke']));
            $this->setBorderRadius(isset($renderData['render_data']['options']['backgroundColor']['rx']));
            $this->setPlotBackgroundColor(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['fill']));
            $this->setPlotBorderWidth(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['strokeWidth']));
            $this->setPlotBorderColor(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['stroke']) ? $renderData['render_data']['options']['chartArea']['backgroundColor']['stroke'] : '');
            $this->setFontSize(isset($renderData['render_data']['options']['fontSize']));
            $this->setFontName(isset($renderData['render_data']['options']['fontName']));
            if ($this->_type == 'google_pie_chart') {
                $this->setThreeD(isset($renderData['render_data']['options']['is3D']));
            }

            // Series
            if ($this->_type == 'google_line_chart') {
                $this->setCurveType(isset($renderData['render_data']['options']['curveType']));
            }

            // Axes
            if ($this->isHorizontalAxisCrosshair() && !$this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'horizontal';
            } elseif (!$this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'vertical';
            } elseif ($this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'both';
            }
            $this->setHorizontalAxisDirection($renderData['render_data']['options']['hAxis']);
            $this->setVerticalAxisDirection($renderData['render_data']['options']['vAxis']);
            $this->setVerticalAxisMin(isset($renderData['render_data']['options']['vAxis']['viewWindow']['min']));
            $this->setVerticalAxisMax(isset($renderData['render_data']['options']['vAxis']['viewWindow']['max']));
            if ($this->isInverted()) {
                $renderData['render_data']['options']['orientation'] = 'vertical';
            } else {
                $renderData['render_data']['options']['orientation'] = 'horizontal';
            }

            // Title
            if ($this->isTitleFloating()) {
                $renderData['render_data']['options']['titlePosition'] = 'in';
            } else {
                $renderData['render_data']['options']['titlePosition'] = 'out';
            }

            // Tooltip
            if ($this->isTooltipEnabled()) {
                $renderData['render_data']['options']['tooltip']['trigger'] = 'focus';
            } else {
                $renderData['render_data']['options']['tooltip']['trigger'] = 'none';
            }

            // Legend
            $this->setLegendPosition(isset($renderData['render_data']['options']['legend']['position']));
            $this->setLegendVerticalAlign(isset($renderData['render_data']['options']['legend']['alignment']));

        } else if ($chartData->engine == 'highcharts') {
            // Chart
            $this->setBackgroundColor(isset($renderData['highcharts_render_data']['options']['chart']['backgroundColor']) ? $renderData['highcharts_render_data']['options']['chart']['backgroundColor'] : '#FFFFFF');
            $this->setBorderWidth(isset($renderData['highcharts_render_data']['options']['chart']['borderWidth']) ? $renderData['highcharts_render_data']['options']['chart']['borderWidth'] : 0);
            $this->setBorderColor(isset($renderData['highcharts_render_data']['options']['chart']['borderColor']) ? $renderData['highcharts_render_data']['options']['chart']['borderColor'] : '#4572A7');
            $this->setBorderRadius(isset($renderData['highcharts_render_data']['options']['chart']['borderRadius']) ? $renderData['highcharts_render_data']['options']['chart']['borderRadius'] : 0);
            $this->setZoomType(isset($renderData['highcharts_render_data']['options']['chart']['zoomType']) ? $renderData['highcharts_render_data']['options']['chart']['zoomType'] : 'undefined');
            $this->setPanning(isset($renderData['highcharts_render_data']['options']['chart']['panning']) ? $renderData['highcharts_render_data']['options']['chart']['panning'] : false);
            $this->setPanKey(isset($renderData['highcharts_render_data']['options']['chart']['panKey']) ? $renderData['highcharts_render_data']['options']['chart']['panKey'] : 'shift');
            $this->setPlotBackgroundColor(isset($renderData['highcharts_render_data']['options']['chart']['plotBackgroundColor']) ? $renderData['highcharts_render_data']['options']['chart']['plotBackgroundColor'] : '');
            $this->setPlotBackgroundImage(isset($renderData['highcharts_render_data']['options']['chart']['plotBackgroundImage']) ? $renderData['highcharts_render_data']['options']['chart']['plotBackgroundImage'] : '');
            $this->setPlotBorderWidth(isset($renderData['highcharts_render_data']['options']['chart']['plotBorderWidth']) ? $renderData['highcharts_render_data']['options']['chart']['plotBorderWidth'] : 0);
            $this->setPlotBorderColor(isset($renderData['highcharts_render_data']['options']['chart']['plotBorderColor']) ? $renderData['highcharts_render_data']['options']['chart']['plotBorderColor'] : '#C0C0C0');
            // Axes
            if ($this->getSeriesType() != '') {
                $this->setHighchartsLineDashStyle(isset($renderData['highcharts_render_data']['options']['yAxis'][0]['gridLineDashStyle']) ? $renderData['highcharts_render_data']['options']['yAxis'][0]['gridLineDashStyle'] : 'Solid');
                $this->setVerticalAxisCrosshair(isset($renderData['highcharts_render_data']['options']['yAxis'][0]['crosshair']) ? $renderData['highcharts_render_data']['options']['yAxis'][0]['crosshair'] : false);
                $this->setVerticalAxisMin(isset($renderData['highcharts_render_data']['options']['yAxis'][0]['min']) ? $renderData['highcharts_render_data']['options']['yAxis'][0]['min'] : '');
                $this->setVerticalAxisMax(isset($renderData['highcharts_render_data']['options']['yAxis'][0]['max']) ? $renderData['highcharts_render_data']['options']['yAxis'][0]['max'] : '');
            } else {
                $this->setHighchartsLineDashStyle(isset($renderData['highcharts_render_data']['options']['yAxis']['gridLineDashStyle']) ? $renderData['highcharts_render_data']['options']['yAxis']['gridLineDashStyle'] : 'Solid');
                $this->setVerticalAxisCrosshair(isset($renderData['highcharts_render_data']['options']['yAxis']['crosshair']) ? $renderData['highcharts_render_data']['options']['yAxis']['crosshair'] : false);
                $this->setVerticalAxisMin(isset($renderData['highcharts_render_data']['options']['yAxis']['min']) ? $renderData['highcharts_render_data']['options']['yAxis']['min'] : '');
                $this->setVerticalAxisMax(isset($renderData['highcharts_render_data']['options']['yAxis']['max']) ? $renderData['highcharts_render_data']['options']['yAxis']['max'] : '');
            }
            $this->setHorizontalAxisCrosshair(isset($renderData['highcharts_render_data']['options']['xAxis']['crosshair']) ? $renderData['highcharts_render_data']['options']['xAxis']['crosshair'] : false);
            $this->setInverted(isset($renderData['highcharts_render_data']['options']['chart']['inverted']) ? $renderData['highcharts_render_data']['options']['chart']['inverted'] : false);
            // Title
            $this->setTitleFloating(isset($renderData['highcharts_render_data']['options']['title']['floating']) ? $renderData['highcharts_render_data']['options']['title']['floating'] : false);
            $this->setTitleAlign(isset($renderData['highcharts_render_data']['options']['title']['align']) ? $renderData['highcharts_render_data']['options']['title']['align'] : 'center');
            $this->setSubtitle(isset($renderData['highcharts_render_data']['options']['subtitle']['text']) ? $renderData['highcharts_render_data']['options']['subtitle']['text'] : '');
            $this->setSubtitleAlign(isset($renderData['highcharts_render_data']['options']['subtitle']['align']) ? $renderData['highcharts_render_data']['options']['subtitle']['align'] : 'center');
            // Tooltip
            $this->setTooltipEnabled(isset($renderData['highcharts_render_data']['options']['tooltip']['enabled']) ? $renderData['highcharts_render_data']['options']['tooltip']['enabled'] : true);
            $this->setTooltipBackgroundColor(!empty($renderData['highcharts_render_data']['options']['tooltip']['backgroundColor']) ? $renderData['highcharts_render_data']['options']['tooltip']['backgroundColor'] : 'rgba(255, 255, 255, 0.85)');
            $this->setTooltipBorderWidth(isset($renderData['highcharts_render_data']['options']['tooltip']['borderWidth']) ? $renderData['highcharts_render_data']['options']['tooltip']['borderWidth'] : 1);
            $this->setTooltipBorderColor(isset($renderData['highcharts_render_data']['options']['tooltip']['borderColor']) ? $renderData['highcharts_render_data']['options']['tooltip']['borderColor'] : null);
            $this->setTooltipBorderRadius(isset($renderData['highcharts_render_data']['options']['tooltip']['borderRadius']) ? $renderData['highcharts_render_data']['options']['tooltip']['borderRadius'] : 3);
            $this->setTooltipShared(isset($renderData['highcharts_render_data']['options']['tooltip']['shared']) ? $renderData['highcharts_render_data']['options']['tooltip']['shared'] : false);
            $this->setTooltipValuePrefix(isset($renderData['highcharts_render_data']['options']['tooltip']['valuePrefix']) ? $renderData['highcharts_render_data']['options']['tooltip']['valuePrefix'] : '');
            $this->setTooltipValueSuffix(isset($renderData['highcharts_render_data']['options']['tooltip']['valueSuffix']) ? $renderData['highcharts_render_data']['options']['tooltip']['valueSuffix'] : '');
            // Legend
            $this->setShowLegend(isset($renderData['highcharts_render_data']['options']['legend']['enabled']) ? $renderData['highcharts_render_data']['options']['legend']['enabled'] : true);
            $this->setLegendBackgroundColor(isset($renderData['highcharts_render_data']['options']['legend']['backgroundColor']) ? $renderData['highcharts_render_data']['options']['legend']['backgroundColor'] : '#FFFFFF');
            $this->setLegendTitle(isset($renderData['highcharts_render_data']['options']['legend']['title']['text']) ? $renderData['highcharts_render_data']['options']['legend']['title']['text'] : '');
            $this->setLegendLayout(isset($renderData['highcharts_render_data']['options']['legend']['layout']) ? $renderData['highcharts_render_data']['options']['legend']['layout'] : 'horizontal');
            $this->setLegendAlign(isset($renderData['highcharts_render_data']['options']['legend']['align']) ? $renderData['highcharts_render_data']['options']['legend']['align'] : 'right');
            $this->setLegendVerticalAlign(isset($renderData['highcharts_render_data']['options']['legend']['verticalAlign']) ? $renderData['highcharts_render_data']['options']['legend']['verticalAlign'] : 'bottom');
            $this->setLegendBorderWidth(isset($renderData['highcharts_render_data']['options']['legend']['borderWidth']) ? $renderData['highcharts_render_data']['options']['legend']['borderWidth'] : 0);
            $this->setLegendBorderColor(isset($renderData['highcharts_render_data']['options']['legend']['borderColor']) ? $renderData['highcharts_render_data']['options']['legend']['borderColor'] : '#909090');
            $this->setLegendBorderRadius(isset($renderData['highcharts_render_data']['options']['legend']['borderRadius']) ? $renderData['highcharts_render_data']['options']['legend']['borderRadius'] : 0);
            // Exporting
            $this->setExporting(isset($renderData['highcharts_render_data']['options']['exporting']['enabled']) ? $renderData['highcharts_render_data']['options']['exporting']['enabled'] : true);
            $this->setExportingDataLabels(isset($renderData['highcharts_render_data']['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled']) ? $renderData['highcharts_render_data']['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled'] : false);
            $this->setExportingFileName(isset($renderData['highcharts_render_data']['options']['exporting']['filename']) ? $renderData['highcharts_render_data']['options']['exporting']['filename'] : 'Chart');
            $this->setExportingWidth(isset($renderData['highcharts_render_data']['options']['exporting']['width']) ? $renderData['highcharts_render_data']['options']['exporting']['width'] : 'undefined');
            $this->setExportingButtonAlign(isset($renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['align']) ? $renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['align'] : 'right');
            $this->setExportingButtonVerticalAlign(isset($renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['verticalAlign']) ? $renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['verticalAlign'] : 'top');
            $this->setExportingButtonColor(isset($renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['symbolStroke']) ? $renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['symbolStroke'] : '#666');
            $this->setExportingButtonText(isset($renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['text']) ? $renderData['highcharts_render_data']['options']['exporting']['buttons']['contextButton']['text'] : null);
            // Credits
            $this->setCredits(isset($renderData['highcharts_render_data']['options']['credits']['enabled']) ? $renderData['highcharts_render_data']['options']['credits']['enabled'] : true);
            $this->setCreditsHref(isset($renderData['highcharts_render_data']['options']['credits']['href']) ? $renderData['highcharts_render_data']['options']['credits']['href'] : 'http://www.highcharts.com');
            $this->setCreditsText(isset($renderData['highcharts_render_data']['options']['credits']['text']) ? $renderData['highcharts_render_data']['options']['credits']['text'] : 'Highcharts.com');
        } else if ($chartData->engine == 'chartjs') {
            // Chart
            $this->setBackgroundColor($renderData['chartjs_render_data']['configurations']['canvas']['backgroundColor']);
            $this->setBorderWidth($renderData['chartjs_render_data']['configurations']['canvas']['borderWidth']);
            $this->setBorderColor($renderData['chartjs_render_data']['configurations']['canvas']['borderColor']);
            $this->setBorderRadius($renderData['chartjs_render_data']['configurations']['canvas']['borderRadius']);
            $this->setFontSize($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontSize']);
            $this->setFontName($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontFamily']);
            $this->setFontStyle($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontStyle']);
            $this->setFontColor($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontColor']);
            // Series
            isset($renderData['chartjs_render_data']['options']['data']['datasets'][0]['lineTension']) ?
                $this->setCurveType($renderData['chartjs_render_data']['options']['data']['datasets'][0]['lineTension']) : null;
            // Axes
            $this->setMajorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['xAxes'][0]['scaleLabel']['labelString']);
            $this->setMinorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['scaleLabel']['labelString']);
            isset($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['min']) ?
                $this->setVerticalAxisMin($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['min']) : null;
            isset($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['max']) ?
                $this->setVerticalAxisMax($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['max']) : null;
            // Title
            $this->setTitlePosition($renderData['chartjs_render_data']['options']['options']['title']['position']);
            $this->setTitleFontName($renderData['chartjs_render_data']['options']['options']['title']['fontFamily']);
            $this->setTitleFontStyle($renderData['chartjs_render_data']['options']['options']['title']['fontStyle']);
            $this->setTitleFontColor($renderData['chartjs_render_data']['options']['options']['title']['fontColor']);
            // Tooltip
            $this->setTooltipEnabled($renderData['chartjs_render_data']['options']['options']['tooltips']['enabled']);
            if ($renderData['chartjs_render_data']['options']['options']['tooltips']['mode'] == 'single') {
                $this->setTooltipShared(false);
            } else {
                $this->setTooltipShared(true);
            }
            $this->setTooltipBackgroundColor($renderData['chartjs_render_data']['options']['options']['tooltips']['backgroundColor']);
            $this->setTooltipBorderRadius($renderData['chartjs_render_data']['options']['options']['tooltips']['cornerRadius']);
            // Legend
            $this->setShowLegend($renderData['chartjs_render_data']['options']['options']['legend']['display']);
            $this->setLegendPositionCjs($renderData['chartjs_render_data']['options']['options']['legend']['position']);

        } else if ($chartData->engine == 'apexcharts') {
            // Chart
            $isBackgroundAColor = WDTTools::isStringAColor($renderData['apexcharts_render_data']['options']['chart']['background']);
            $this->setBackgroundColor((isset($renderData['apexcharts_render_data']['options']['chart']['background']) && $isBackgroundAColor) ? $renderData['apexcharts_render_data']['options']['chart']['background'] : '');
            $this->setEnableAnimation(isset($renderData['apexcharts_render_data']['options']['chart']['animations']['enabled']) ? $renderData['apexcharts_render_data']['options']['chart']['animations']['enabled'] : false);
            $this->setMonochrome(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['enabled']) ? $renderData['apexcharts_render_data']['options']['theme']['monochrome']['enabled'] : false);
            $this->setMonochromeColor(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) ? $renderData['apexcharts_render_data']['options']['theme']['monochrome']['color'] : '#255aee');
            $this->setEnableColorPalette(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) && WDTTools::isStringAColor($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) && !$this->isMonochrome());
            $this->setColorPalette(isset($renderData['apexcharts_render_data']['options']['theme']['palette'] ) ? $renderData['apexcharts_render_data']['options']['theme']['palette']  : 'palette1');
            $this->setStartAngle(isset($renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['startAngle']) ? $renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['startAngle'] : 0);
            $this->setEndAngle(isset($renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['endAngle']) ? $renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['endAngle'] : 360);
            $this->setTextColor(isset($renderData['apexcharts_render_data']['options']['chart']['foreColor']) ? $renderData['apexcharts_render_data']['options']['chart']['foreColor'] : '#373d3f');
            $this->setZoomType(isset($renderData['apexcharts_render_data']['options']['chart']['zoom']['type']) ? $renderData['apexcharts_render_data']['options']['chart']['zoom']['type'] : 'None');
            $this->setPlotBackgroundImage((isset($renderData['apexcharts_render_data']['options']['chart']['background']) && !$isBackgroundAColor && $renderData['apexcharts_render_data']['options']['chart']['background'] !== '') ? "url(" . $renderData['apexcharts_render_data']['options']['chart']['background'] . ") no-repeat center/cover" : '');
            $this->setLineBackgroundImage((isset($renderData['apexcharts_render_data']['options']['fill']['image']['src']) && !is_array($renderData['apexcharts_render_data']['options']['fill']['image']['src']) ) ? $renderData['apexcharts_render_data']['options']['fill']['image']['src'] : '');
            $this->setShowDataLabels(isset($renderData['apexcharts_render_data']['options']['dataLabels']['enabled']) ? $renderData['apexcharts_render_data']['options']['dataLabels']['enabled'] : false);
            $this->setEnableDropshadow(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['enabled']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['enabled'] : false);
            $this->setDropshadowBlur(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['blur']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['blur'] : 3);
            $this->setDropshadowOpacity(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['opacity']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['opacity'] : 35);
            $this->setDropshadowColor(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['color']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['color'] : '#000');
            $this->setDropshadowTop(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['top']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['top'] : 5);
            $this->setDropshadowLeft(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['left']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['left'] : 5);

            // Axes
            $this->setShowGrid(isset($renderData['apexcharts_render_data']['options']['grid']['show']) ? $renderData['apexcharts_render_data']['options']['grid']['show'] : true);
            $this->setGridColor(isset($renderData['apexcharts_render_data']['options']['grid']['borderColor']) ? $renderData['apexcharts_render_data']['options']['grid']['borderColor'] : '#000000');
            $this->setGridStroke(isset($renderData['apexcharts_render_data']['options']['grid']['strokeDashArray']) ? $renderData['apexcharts_render_data']['options']['grid']['strokeDashArray'] : 1);
            $this->setGridPosition(isset($renderData['apexcharts_render_data']['options']['grid']['position']) ? $renderData['apexcharts_render_data']['options']['grid']['position'] : 'back');
            $gridAxes = array();
            if (isset($renderData['apexcharts_render_data']['options']['grid'])) {
                if ($renderData['apexcharts_render_data']['options']['grid']['xaxis']['lines']['show']) array_push($gridAxes, 'xaxis');
                if ($renderData['apexcharts_render_data']['options']['grid']['yaxis']['lines']['show']) array_push($gridAxes, 'yaxis');
            }
            $this->setGridAxes($gridAxes);
            $this->setMajorAxisLabel(isset($renderData['apexcharts_render_data']['options']['xaxis']['title']['text']) ? $renderData['apexcharts_render_data']['options']['xaxis']['title']['text'] : '');
            if (strpos($renderData['apexcharts_render_data']['type'], 'bar')) {
                $this->setMinorAxisLabel(isset($renderData['apexcharts_render_data']['options']['yaxis']['title']['text']) ? $renderData['apexcharts_render_data']['options']['yaxis']['title']['text'] : '');
                $this->setVerticalAxisMin(isset($renderData['apexcharts_render_data']['options']['yaxis']['min']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['min'] : '');
                $this->setVerticalAxisMax(isset($renderData['apexcharts_render_data']['options']['yaxis']['max']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['max'] : '');
                $this->setTickAmount((isset($renderData['apexcharts_render_data']['options']['yaxis']['tickAmount']) && $renderData['apexcharts_render_data']['options']['yaxis']['tickAmount'] != 0) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['tickAmount'] : 0);
                $this->setReversed(isset($renderData['apexcharts_render_data']['options']['yaxis']['reversed']) ? $renderData['apexcharts_render_data']['options']['yaxis']['reversed'] : false);
            } else {
                $this->setMinorAxisLabel(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['title']['text']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['title']['text'] : '');
                $this->setVerticalAxisMin(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['min']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['min'] : '');
                $this->setVerticalAxisMax(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['max']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['max'] : '');
                $this->setTickAmount((isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount']) && $renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount'] != 0) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount'] : 0);
                $this->setReversed(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['reversed']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['reversed'] : false);

            }
            $this->setMarkerSize(isset($renderData['apexcharts_render_data']['options']['markers']['size']) ? $renderData['apexcharts_render_data']['options']['markers']['size'] : 0);
            $this->setStrokeWidth(isset($renderData['apexcharts_render_data']['options']['stroke']['width']) ? $renderData['apexcharts_render_data']['options']['stroke']['width'] : 2);
            $this->setVerticalAxisCrosshair(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['crosshairs']['show']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['crosshairs']['show'] : false);
            $this->setHorizontalAxisCrosshair(isset($renderData['apexcharts_render_data']['options']['xaxis']['crosshairs']['show']) ? $renderData['apexcharts_render_data']['options']['xaxis']['crosshairs']['show'] : false);

            // Title
            $this->setTitleFloating(isset($renderData['apexcharts_render_data']['options']['title']['floating']) ? $renderData['apexcharts_render_data']['options']['title']['floating'] : false);
            $this->setTitleAlign(isset($renderData['apexcharts_render_data']['options']['title']['align']) ? $renderData['apexcharts_render_data']['options']['title']['align'] : 'center');
            $this->setFontStyle(isset($renderData['apexcharts_render_data']['options']['title']['style']['fontWeight']) ? $renderData['apexcharts_render_data']['options']['title']['style']['fontWeight'] : 'bold');
            $this->setSubtitle(isset($renderData['apexcharts_render_data']['options']['subtitle']['text']) ? $renderData['apexcharts_render_data']['options']['subtitle']['text'] : '');
            $this->setSubtitleAlign(isset($renderData['apexcharts_render_data']['options']['subtitle']['align']) ? $renderData['apexcharts_render_data']['options']['subtitle']['align'] : 'center');

            // Tooltip
            $this->setTooltipEnabled(isset($renderData['apexcharts_render_data']['options']['tooltip']['enabled']) ? $renderData['apexcharts_render_data']['options']['tooltip']['enabled'] : false);
            $this->setFollowCursor(isset($renderData['apexcharts_render_data']['options']['tooltip']['followCursor']) && (bool)$renderData['apexcharts_render_data']['options']['tooltip']['followCursor']);
            $this->setFillSeriesColor(isset($renderData['apexcharts_render_data']['options']['tooltip']['fillSeriesColor']) && $renderData['apexcharts_render_data']['options']['tooltip']['fillSeriesColor']);

            // Legend
            $this->setShowLegend(isset($renderData['apexcharts_render_data']['options']['legend']['show']) ? $renderData['apexcharts_render_data']['options']['legend']['show'] : true );
            $this->setLegendPositionCjs(isset($renderData['apexcharts_render_data']['options']['legend']['position']) ? $renderData['apexcharts_render_data']['options']['legend']['position'] : 'bottom');

            //Toolbar
            $this->setShowToolbar(isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['show']) ? $renderData['apexcharts_render_data']['options']['chart']['toolbar']['show'] : false);

            if(isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['tools'])) {
                $toolbarButtons = array();
                foreach ($renderData['apexcharts_render_data']['options']['chart']['toolbar']['tools'] as $toolbarTool => $show) {
                    if ($toolbarTool != 'customIcons' && $show == true) {
                        array_push($toolbarButtons, $toolbarTool);
                    }
                }
                $this->setToolbarButtons($toolbarButtons);
            } else $this->setToolbarButtons(['download', 'selection', 'zoomin', 'zoomout', 'zoom' , 'pan']);
            $this->setApexExportingFileName((isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename']) && $renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename'] != '') ? $renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename'] : 'Chart');
        }

        $this->loadChildWPDataTable();

    }

    /**
     * Render Chart
     */
    public function renderChart() {
        $minified_js = get_option('wdtMinifiedJs');

        $this->prepareData();
        if ($this->_follow_filtering) {
            $this->getColumnIndexes();
        }

        $this->groupData();

        $this->shiftStringColumnUp();

        $js_ext = $minified_js ? '.min.js' : '.js';

        if ($this->getFollowFiltering()) {
            wp_enqueue_script('wdt-common', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js', array('jquery'), WDT_CURRENT_VERSION, true);
            if ($minified_js) {
                wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wdt.frontend.min.js', array('wdt-common'), WDT_CURRENT_VERSION, true);
                wp_localize_script('wdt-wpdatatables', 'wdt_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
            } else {
                wp_enqueue_script('wdt-datatables', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.min.js', array(), WDT_CURRENT_VERSION, true);
                wp_enqueue_script('wdt-funcs-js', WDT_JS_PATH . 'wpdatatables/wdt.funcs.js', array('jquery', 'wdt-datatables', 'wdt-common'), WDT_CURRENT_VERSION, true);
                wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wpdatatables.js', array('jquery', 'wdt-datatables'), WDT_CURRENT_VERSION, true);
            }

            if (get_option('wdtIncludeBootstrap') == 1) {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
            } else {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
            }
            wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('underscore');
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_settings', WDTTools::getDateTimeSettings());
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings());
        }
        wp_enqueue_script('wpdatatables-render-chart', WDT_JS_PATH . 'wpdatatables/wdt.chartsRender' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);

        if ($this->_engine == 'google') {
            // Google Chart JS
            wp_enqueue_script('wdt-google-charts', '//www.gstatic.com/charts/loader.js', array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wpdatatables-google-chart', WDT_JS_PATH . 'wpdatatables/wdt.googleCharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_render_data);
        } else if ($this->_engine == 'highcharts') {
            $this->prepareHighchartsRender();
            // Highchart JS
            wp_enqueue_script('wdt-highcharts', '//code.highcharts.com/highcharts.js', array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-highcharts-more', '//code.highcharts.com/highcharts-more.js', array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-highcharts3d', '//code.highcharts.com/highcharts-3d.js', array(), WDT_CURRENT_VERSION);
            if ( in_array($this->getType(), ['highcharts_treemap_level_chart', 'highcharts_treemap_chart']) ){
                wp_enqueue_script('wdt-heatmap', '//code.highcharts.com/modules/heatmap.js', array(), WDT_CURRENT_VERSION);
                wp_enqueue_script('wdt-treemap', '//code.highcharts.com/modules/treemap.js', array(), WDT_CURRENT_VERSION);
            }
            if ( $this->getExporting() ) {
                wp_enqueue_script('wdt-exporting', '//code.highcharts.com/modules/exporting.js', array(), WDT_CURRENT_VERSION);
                if (!in_array($this->getType(), ['highcharts_treemap_level_chart', 'highcharts_treemap_chart'])) {
                    wp_enqueue_script('wdt-export-data', '//code.highcharts.com/modules/export-data.js', array(), WDT_CURRENT_VERSION);
                }
            }
            // Highchart wpDataTable JS library
            wp_enqueue_script('wpdatatables-highcharts', WDT_JS_PATH . 'wpdatatables/wdt.highcharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_highcharts_render_data);
        } else if ($this->_engine == 'chartjs') {
            $this->prepareChartJSRender();
            wp_enqueue_script('wdt-chartjs', WDT_JS_PATH . 'chartjs/Chart.js', array(), WDT_CURRENT_VERSION);
            // ChartJS wpDataTable JS library
            wp_enqueue_script('wpdatatables-chartjs', WDT_JS_PATH . 'wpdatatables/wdt.chartJS' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_chartjs_render_data);
        } else if ($this->_engine = 'apexcharts') {
            $this->prepareApexchartsRender();
            wp_enqueue_script('wdt-apexcharts', 'https://cdn.jsdelivr.net/npm/apexcharts', array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wpdatatables-apexcharts', WDT_JS_PATH . 'wpdatatables/wdt.apexcharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_apexcharts_render_data);
        }

        do_action('wdt-enqueue-scripts-after-chart-render');

        $chart_id = $this->_id;
        ob_start();
        include(WDT_TEMPLATE_PATH . 'wpdatachart.inc.php');
        $chart_html = ob_get_contents();
        ob_end_clean();
        return $chart_html;

    }

    /**
     * Return render data
     */
    public function getRenderData() {
        return $this->_render_data;
    }

    /**
     * Return highcharts render data
     * @return array
     */
    public function getHighchartsRenderData() {
        return $this->_highcharts_render_data;
    }

    /**
     * Return ChartJS render data
     * @return null
     */
    public function getChartJSRenderData() {
        return $this->_chartjs_render_data;
    }

    /**
     * Return Apexcharts render data
     * @return null
     */
    public function getApexchartsRenderData() {
        return $this->_apexcharts_render_data;
    }

    /**
     * Delete chart by ID
     * @param $chartId
     * @return bool
     */
    public static function deleteChart($chartId) {
        global $wpdb;

        if (!isset($_REQUEST['wdtNonce']) || empty($chartId) || !current_user_can('manage_options')
            || !wp_verify_nonce($_REQUEST['wdtNonce'], 'wdtDeleteChartNonce')) {
            return false;
        }

        $wpdb->delete(
            $wpdb->prefix . "wpdatacharts",
            array(
                'id' => (int)$chartId
            )
        );

        return true;

    }

    /**
     * Get all charts non-paged for the MCE editor
     * @return array|null|object
     */
    public static function getAllCharts() {
        global $wpdb;
        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatacharts ";
        $allCharts = $wpdb->get_results($query, ARRAY_A);
        return $allCharts;
    }

}

?>
