<?php defined('ABSPATH') or die('Access denied.'); ?>
<div class="row" id="wdt-chart-settings-and-preview">

    <div class="col-sm-5 col-md-5 col-lg-5 m-b-20">
        <div id="chart-container-tabs" class=" settings">

            <div class="col-sm-3 col-md-3 col-lg-4">
                <ul class="tab-nav settings">
                    <li class="chart-container active"><a href="#chart-container-tabs-1"
                                                          data-toggle="tab"><?php _e('Chart', 'wpdatatables'); ?></a>
                    </li>
                    <li class="chart-container series"><a href="#chart-container-tabs-2" data-toggle="tab"
                                                          class=""><?php _e('Series', 'wpdatatables'); ?></a></li>
                    <li class="chart-container axes"><a href="#chart-container-tabs-3" data-toggle="tab"
                                                        class=""><?php _e('Axes', 'wpdatatables'); ?></a></li>
                    <li class="chart-container title"><a href="#chart-container-tabs-4" data-toggle="tab"
                                                         class=""><?php _e('Title', 'wpdatatables'); ?></a></li>
                    <li class="chart-container tooltips"><a href="#chart-container-tabs-5" data-toggle="tab"
                                                            class=""><?php _e('Tooltip', 'wpdatatables'); ?></a></li>
                    <li class="chart-container legend"><a href="#chart-container-tabs-6" data-toggle="tab"
                                                          class=""><?php _e('Legend', 'wpdatatables'); ?></a></li>
                    <li class="chart-container highcharts"><a href="#chart-container-tabs-7" data-toggle="tab"
                                                              class=""><?php _e('Exporting', 'wpdatatables'); ?></a>
                    </li>
                    <li class="chart-container highcharts"><a href="#chart-container-tabs-8" data-toggle="tab"
                                                              class=""><?php _e('Credits', 'wpdatatables'); ?></a></li>
                    <li class="chart-container apex-toolbar-container"><a href="#chart-container-tabs-9" data-toggle="tab"
                                                              class=""><?php _e('Toolbar', 'wpdatatables'); ?></a></li>
                </ul>
            </div>

            <div class="tab-content p-0">
                <div id="chart-container-tabs-2"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div>
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Series settings', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you want to redefine the series labels and colors you can do it here.', 'wpdatatables'); ?>"></i>
                        </h4>
                    </div>
                    <div>
                        <div id="series-settings-container">

                        </div>
                    </div>
                    <div class="chartjs google" id="curve-type-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Curve type', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Controls the curve of the lines', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="curve-type" name="curve-type" type="checkbox">
                            <label for="curve-type"><?php _e('Check for smoothed lines', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-1"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane active">
                    <div class="chart-width">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Chart width', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The width of the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                        <button type="button" id="btn-minus-chart-width" disabled="disabled" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="chart-width">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="chart-width" min="0" value="0" readonly class="form-control input-sm input-number"
                                               id="chart-width">
                                        <button type="button" id="btn-plus-chart-width" disabled="disabled" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="chart-width">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="responsive-width">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Responsive width', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you tick this chart width will always adjust to 100% width of the container', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="chart-responsive-width" type="checkbox" checked>
                            <label for="chart-responsive-width"><?php _e('Responsive chart width', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                    <div class="chart-height">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Chart height', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The height of the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="chart-height">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="chart-height"  min="0" value="400" class="form-control input-sm input-number"
                                               id="chart-height">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="chart-height">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="group-chart">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Group chart', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you tick this checkbox, the values of the rows with same label will be summed up and rendered as a single series. If you leave it unticked all rows will be rendered as separate series.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="group-chart" type="checkbox" class="doNotTriggerChange">
                            <label for="group-chart"><?php _e('Enable grouping', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                    <div id="enable-animation-container" class="apexcharts">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Enable animation', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you tick this checkbox, the chart will animate when loading. For charts with a lot of data, this could slow down the page', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="enable-animation" type="checkbox">
                            <label for="enable-animation"><?php _e('Enable animation', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                    <div class="row apexcharts">
                        <div class="start-angle-container apexcharts-radialbar col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Start angle', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set the starting angle of the radial bar.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="start-angle">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="start-angle" min="-360" value="0"  class="form-control input-sm input-number"
                                               id="start-angle">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="start-angle">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="end-angle-container apexcharts-radialbar col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('End angle', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set the ending angle of the radial bar.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="end-angle">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="end-angle" min="-360" value="360"  class="form-control input-sm input-number"
                                               id="end-angle">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="end-angle">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="background-color-container" id="background-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Background color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The background color for the outer chart area.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="background-color" value=""
                                               class="form-control cp-value wdt-add-picker background-color"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-text-color col-sm apexcharts" id="chart-text-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Text color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Choose the color of the text color for the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="chart-text-color" value="#373d3f"
                                               class="form-control cp-value wdt-add-picker"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-width border-settings">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Border width', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The pixel width of the outer chart border.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="border-width">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="border-width" min="0" value="0"  class="form-control input-sm input-number"
                                                   id="border-width">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="border-width">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-color-container border-settings" id="border-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Border color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The color of the outer chart border.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="border_color" value=""
                                               class="form-control cp-value wdt-add-picker plot border_color"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-radius border-settings">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Border radius', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The corner radius of the outer chart border.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="border-radius">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="border-radius" min="0" value="0" class="form-control input-sm input-number"
                                                   id="border-radius">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="border-radius">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="highcharts apexcharts" id="zoom-type-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Zoom type', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Decides in what dimensions the user can zoom by dragging the mouse', 'wpdatatables'); ?><?php _e('. For Apexcharts you have to have zoom enabled in the toolbar.' , 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <label for="zoom-type"></label><select class="selectpicker" name="zoom-type" id="zoom-type">
                                        <option selected="selected" value="none">None</option>
                                        <option value="x">X</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="highcharts" id="panning-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Panning', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Allow panning in a chart. Best used with panKey to combine zooming and panning', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="panning" type="checkbox">
                            <label for="panning"><?php _e('Panning', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                    <div class="highcharts" id="pan-key-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Pan key', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Allows setting a key to switch between zooming and panning', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" name="pan-key" id="pan-key">
                                        <option selected="selected" value="shift">Shift</option>
                                        <option value="ctrl">Ctrl</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google highcharts" id="plot-background-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Plot background color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The background color or gradient for the plot area.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="plot-background-color" value=""
                                               class="form-control cp-value wdt-add-picker plot-background-color"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="highcharts apexcharts" id="plot-background-image-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Plot background image', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The URL for an image to use as the plot background.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12" style="display: flex">
                                        <input type="text" name="plot-background-image" id="plot-background-image"
                                               class="form-control input-sm doNotTriggerChange" value=""/>

                                        <button class="btn bgm-blue" id="wdt-plot-image-clear-button">
                                            <span class="wpdt-icon-image"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="apexcharts" id="line-background-image-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Chart area/line image', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The URL for an image to use as the background image of the chart area or line. If you have multiple series, this image is applied to all of them. If you want each series to have a different image property, leave this blank.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12" style="display: flex">
                                        <input type="text" name="line-background-image" id="line-background-image"
                                               class="form-control input-sm doNotTriggerChange" value=""/>

                                            <button class="btn bgm-blue" id="wdt-line-image-clear-button">
                                                <span class="wpdt-icon-image"></span>
                                            </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="apexcharts-pie apexcharts" id="enable-monochrome">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Monochrome', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('A single color will be used as a base and shades for the chart will be generated from that color', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="monochrome" type="checkbox">
                            <label for="monochrome"><?php _e('Enable monochrome color palette', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                    <div class="monochrome-color col-sm-20" style="display: none" id="monochrome-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Monochrome color to use', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Choose the color to use for generating a monochromatic design.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="monochrome-color" value="#255aee"
                                               class="form-control cp-value wdt-add-picker"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="apexcharts-pie apexcharts" id="color-palette-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Color palette', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('When enabled, you can pick a predefined color palette for your chart. Leave this unchecked if you want to use the monochrome design.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="enable-color-palette" type="checkbox">
                            <label for="enable-color-palette"><?php _e('Use a color palette', 'wpdatatables'); ?></label>
                        </div>
                    </div>

                    <div id="color-palette-container" style="display: none">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Palette', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Choose between 10 predefined color palettes.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker"
                                    id="color-palette">
                                <option value="palette1" selected="selected"><?php _e('Palette 1', 'wpdatatables'); ?></option>
                                <option value="palette2" ><?php _e('Palette 2', 'wpdatatables'); ?></option>
                                <option value="palette3" ><?php _e('Palette 3', 'wpdatatables'); ?></option>
                                <option value="palette4"><?php _e('Palette 4', 'wpdatatables'); ?></option>
                                <option value="palette5"><?php _e('Palette 5', 'wpdatatables'); ?></option>
                                <option value="palette6"><?php _e('Palette 6', 'wpdatatables'); ?></option>
                                <option value="palette7" ><?php _e('Palette 7', 'wpdatatables'); ?></option>
                                <option value="palette8" ><?php _e('Palette 8', 'wpdatatables'); ?></option>
                                <option value="palette9" ><?php _e('Palette 9', 'wpdatatables'); ?></option>
                                <option value="palette10" ><?php _e('Palette 10', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div id="marker-size-row" class="apexcharts">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Marker size', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Sets the size of the marker point. For larger amounts of data points, it is recommended to set this to 0 to optimize performance.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="fg-line ">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="marker-size">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="marker-size" min="0" value="0" class="form-control input-sm input-number"
                                                       id="marker-size">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="marker-size">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="apexcharts" id="data-labels-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Data labels', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you leave this checked, data labels will be shown on the chart. For larger amounts of data, it is recommended to keep this option turned off for optimal perfermance.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="data-labels" type="checkbox">
                            <label for="data-labels"><?php _e('Show data labels', 'wpdatatables'); ?></label>
                        </div>
                    </div>

                    <div class="apexcharts">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Drop shadow', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Enable a dropshadow.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="enable-dropshadow" type="checkbox">
                            <label for="enable-dropshadow"><?php _e('Enable dropshadow', 'wpdatatables'); ?></label>
                        </div>
                    </div>

                    <div class="row apexcharts">
                        <div class="dropshadow dropshadow-blur col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Dropshadow blur', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set the blur distance for the shadow. You can set any value starting with 1.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="dropshadow-blur">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="dropshadow-blur" min="1" value="3"  class="form-control input-sm input-number"
                                               id="dropshadow-blur">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="dropshadow-blur">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropshadow dropshadow-opacity col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Dropshadow opacity', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set the opacity of the shadow ranging between 1 and 100.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="dropshadow-opacity">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="dropshadow-opacity" min="1" value="35"  class="form-control input-sm input-number"
                                               id="dropshadow-opacity">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="dropshadow-opacity">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row apexcharts">
                        <div class="dropshadow col-sm-12" style="display: none" id="dropshadow-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Dropshadow color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose the color of the dropshadow.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="dropshadow-color" value="#000000"
                                                   class="form-control cp-value wdt-add-picker"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row apexcharts">
                        <div class="dropshadow dropshadow-top col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Top', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set top offset for the dropshadow.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="dropshadow-top">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="dropshadow-top" value="5" min="-100" class="form-control input-sm input-number"
                                               id="dropshadow-top">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="dropshadow-top">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropshadow dropshadow-left col-sm-6" style="display: none">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Left', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Set left offset for the dropshadow.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="dropshadow-left">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="dropshadow-left" value="5" min="-100" class="form-control input-sm input-number"
                                               id="dropshadow-left">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="dropshadow-left">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google highcharts" id="plot-border-width-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Plot border width', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The corner radius of the outer chart border.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="plot-border-width">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="plot-border-width" min="0" value="" class="form-control input-sm input-number plot-border-width"
                                                   id="plot-border-width">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="plot-border-width">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google highcharts" id="plot-border-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Plot border color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The color of the inner chart or plot area border.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="plot-border-color" value=""
                                               class="form-control cp-value wdt-add-picker plot-border-color"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google chartjs" id="font-size-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Font size', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The default font size, in pixels, of all text in the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="font-size">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="font-size" value="" min="8" class="form-control input-sm input-number"
                                                   id="font-size">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="font-size">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google chartjs" id="font-name-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Font name', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The default font face for all text in the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <input type="text" name="font-name" id="font-name" value="Arial"
                                               class="form-control input-sm"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chartjs" id="font-style-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Font style', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The default font style for all text in the chart', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" name="font-style" id="font-style">
                                        <option value="bold">Bold</option>
                                        <option value="italic">Italic</option>
                                        <option value="bold italic">Bold Italic</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chartjs" id="font-color-container">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Font color', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('The default font color for all text in the chart.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="cp-container">
                            <div class="form-group">
                                <div class="fg-line dropdown">
                                    <div id="cp"
                                         class="input-group wdt-color-picker">
                                        <input type="text" id="font-color" value=""
                                               class="form-control cp-value wdt-add-picker font-color"/>
                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="google" id="three-d-row">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('3D', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Check for 3D pie chart', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="toggle-switch p-b-16" data-ts-color="blue">
                            <input id="three-d" type="checkbox">
                            <label for="three-d"><?php _e('3D', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-3"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div id="show-grid-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Grid', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Controls the curve of the lines', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="show-grid" type="checkbox" checked>
                                <label for="show-grid"><?php _e('Do you want to show grid on the chart?', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="apexcharts grid-style" id="grid-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Grid line color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Color of grid borders / lines', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="grid-color" value=""
                                                   class="form-control cp-value wdt-add-picker"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid-stroke grid-style apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Grid stroke dash', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Creates dashes in the grid. Higher number creates more space between dashes in the border.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="grid-stroke">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="grid-stroke" min="0" value="1" class="form-control input-sm input-number"
                                                       id="grid-stroke">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="grid-stroke">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="apexcharts grid-style">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Grid position', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Whether to place grid behind chart paths of in front.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="title-align" id="grid-position">
                                            <option selected="selected" value="back">Back</option>
                                            <option value="front">Front</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="apexcharts grid-style yaxisOnly">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Axes lines to show', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose which axes lines to show', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        id="grid-axes">
                                    <option value="xaxis">x-axis</option>
                                    <option value="yaxis" selected="selected">y-axis</option>
                                </select>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Grid line style', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The dash or dot style of the grid lines', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="highcharts-line-dash-style"
                                                id="highcharts-line-dash-style">
                                            <option selected="selected" value="solid">Solid</option>
                                            <option value="shortdash">Short Dash</option>
                                            <option value="shortdot">Short Dot</option>
                                            <option value="shortdashdot">Short Dash Dot</option>
                                            <option value="shortdashdotdot">Short Dash Dot Dot</option>
                                            <option value="dot">Dot</option>
                                            <option value="dash">Dash</option>
                                            <option value="longdash">Long Dash</option>
                                            <option value="dashdot">Dash Dot</option>
                                            <option value="dongdashdot">Long Dash Dot</option>
                                            <option value="longdashdotdot">Long Dash Dot Dot</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="horizontal-axis-label-row" class="highcharts google chartjs apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Horizontal axis label', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Name of the horizontal axis.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" id="horizontal-axis-label" value=""
                                                   class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="google highcharts apexcharts" id="horizontal-axis-crosshair-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Horizontal crosshair', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Configure a horizontal crosshair that follows either the mouse pointer or the hovered point lines', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="horizontal-axis-crosshair" type="checkbox">
                                <label for="horizontal-axis-crosshair"><?php _e('Show x-Axis crosshair', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="google" id="horizontal-axis-direction-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Horizontal axis direction', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The direction in which the values along the horizontal axis grow. Specify -1 to reverse the order of the values', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="horizontal-axis-direction"
                                                id="horizontal-axis-direction">
                                            <option selected="selected" value="1">1</option>
                                            <option value="-1">-1</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="vertical-axis-label-row" class="highcharts google chartjs apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical axis label', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Name of the vertical axis.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" id="vertical-axis-label" value=""
                                                   class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="google highcharts apexcharts" id="vertical-axis-crosshair-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical crosshair', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Configure a vertical crosshair that follows either the mouse pointer or the hovered point lines', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="vertical-axis-crosshair" type="checkbox">
                                <label for="vertical-axis-crosshair"><?php _e('Show y-Axis crosshair', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="google" id="vertical-axis-direction-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical axis direction', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The direction in which the values along the vertical axis grow. Specify -1 to reverse the order of the values', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="vertical-axis-direction"
                                                id="vertical-axis-direction">
                                            <option selected="selected" value="1">1</option>
                                            <option value="-1">-1</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="stroke-width-row" class="apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Stroke width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Sets the width of border for svg path. Smaller values correspond to smaller line thickness.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="fg-line ">
                                                <div class="wdt-custom-number-input">
                                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="stroke-width">
                                                        <i class="wpdt-icon-minus"></i>
                                                    </button>
                                                    <input type="number" name="stroke-width" min="1" value="2" class="form-control input-sm input-number"
                                                           id="stroke-width">
                                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="stroke-width">
                                                        <i class="wpdt-icon-plus-full"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="vertical-axis-min-row" class="yaxisOnly">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical axis min value', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The minimum value of the axis.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="fg-line ">
                                                <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="vertical-axis-min">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="vertical-axis-min" min="-10000" class="form-control input-sm input-number"
                                                       id="vertical-axis-min">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="vertical-axis-min">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="vertical-axis-max-row" class="yaxisOnly">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical axis max value', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The maximum value of the axis.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="vertical-axis-max">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="vertical-axis-max" min="-10000" class="form-control input-sm input-number"
                                                   id="vertical-axis-max">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="vertical-axis-max">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tick-amount-row" class="apexcharts yaxisOnly">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Tick amount', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Number of tick intervals to show on the y-axis.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="tick-amount">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="tick-amount" min="0" value="0" class="form-control input-sm input-number"
                                                       id="tick-amount">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="tick-amount">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="google highcharts" id="inverted-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Invert', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Whether to invert the axes so that the x axis is vertical and y axis is horizontal', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="inverted" type="checkbox">
                                <label for="inverted"><?php _e('Invert chart axes', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="apexcharts yaxisOnly" id="reversed-axis">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Reverse y-axis', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Flip the chart upside down making it inversed and draw y-axis from bigger to smaller numbers.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="reversed" type="checkbox">
                                <label for="reversed"><?php _e('Reverse y-axis', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-4"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div id="show-chart-title-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Chart title', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Do you want to show the chart title on the page?', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="show-chart-title" type="checkbox" checked>
                                <label for="show-chart-title"><?php _e('Show title', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="google highcharts apexcharts" id="title-floating-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title floating', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('When the title is floating, the plot area will not move to make space for it', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="title-floating" type="checkbox">
                                <label for="title-floating"><?php _e('Enable floating', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="highcharts apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The horizontal alignment of the title', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="title-align" id="title-align">
                                            <option selected="selected" value="center">Center</option>
                                            <option value="left">Left</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chartjs">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title position', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Position of the title. Possible values are \'top\', \'left\', \'bottom\' and \'right\'', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="title-position" id="title-position">
                                            <option selected="selected" value="top">Top</option>
                                            <option value="left">Left</option>
                                            <option value="bottom">Bottom</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chartjs" id="title-font-name-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title font name', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The default font face for text in the title.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="title-font-name" id="title-font-name" value="Arial"
                                                   class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chartjs" id="title-font-style-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title font style', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The default font style for text in the title', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="title-font-style" id="title-font-style">
                                            <option value="normal">Normal</option>
                                            <option selected="selected" value="bold">Bold</option>
                                            <option value="italic">Italic</option>
                                            <option value="bold italic">Bold Italic</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chartjs" id="title-font-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title font color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The default font color for text in the title.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="title-font-color" value=""
                                                   class="form-control cp-value wdt-add-picker"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Subtitle', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The chart\'s subtitle.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="subtitle" id="subtitle" value=""
                                                   class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Subtitle align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The horizontal alignment of the subtitle', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="subtitle-align" id="subtitle-align">
                                            <option selected="selected" value="center">Center</option>
                                            <option value="left">Left</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-5"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div id="tooltip-enabled-row" class="highcharts chartjs google apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Tooltip', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Enable or disable the tooltip', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="tooltip-enabled" type="checkbox" checked>
                                <label for="tooltip-enabled"><?php _e('Show tooltip', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="highcharts chartjs" id="tooltip-background-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Background color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The background color for the tooltip.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="tooltip-background-color" value=""
                                                   class="form-control cp-value wdt-add-picker tooltip-background-color"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The pixel width of the tooltip border.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="tooltip-border-width">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="tooltip-border-width" value="1" min="1" class="form-control input-sm input-number"
                                                   id="tooltip-border-width">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="tooltip-border-width">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts" id="tooltip-border-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The color of the tooltip border.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="tooltip-border-color" value=""
                                                   class="form-control cp-value wdt-add-picker tooltip-border-color"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts chartjs">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border radius', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The radius of the rounded border corners.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="tooltip-border-radius">
                                                <i class="wpdt-icon-minus"></i>
                                            </button>
                                            <input type="number" name="tooltip-border-radius" min="0" class="form-control input-sm input-number"
                                                   id="tooltip-border-radius" value="3">
                                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="tooltip-border-radius">
                                                <i class="wpdt-icon-plus-full"></i>
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts chartjs">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Shared tooltip', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('When the tooltip is shared, the entire plot area will capture mouse movement or touch events', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="tooltip-shared" type="checkbox">
                                <label for="tooltip-shared"><?php _e('Share tooltip', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Value prefix', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('A string to prepend to each series\' y value', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="tooltip-value-prefix" id="tooltip-value-prefix"
                                                   value="" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Value suffix', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('A string to append to each series\' y value', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="tooltip-value-suffix" id="tooltip-value-suffix"
                                                   value="" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="apexcharts follow-cursor-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Follow cursor', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Follow the cursor position instead of putting tooltip on actual data points', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="follow-cursor" type="checkbox">
                                <label for="follow-cursor"><?php _e('Enable follow cursor', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="apexcharts fill-series-color-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Fill with series color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('If you leave this checked, the tooltip background will be filled with the corresponding series color', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="fill-series-color" type="checkbox">
                                <label for="fill-series-color"><?php _e('Fill with series color', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-6"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div class="highcharts chartjs apexcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Legend', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Enable or disable the legend', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="show-legend" type="checkbox" checked="checked">
                                <label for="show-legend"><?php _e('Show legend', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div class="google" id="legend-position-row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Position', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Position of the legend', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="legend_position" id="legend_position">
                                            <option selected="selected" value="right">Right</option>
                                            <option value="bottom">Bottom</option>
                                            <option value="top">Top</option>
                                            <option value="in">In</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Background color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The background color of the legend.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="legend_background_color" value=""
                                                   class="form-control cp-value wdt-add-picker legend_background_color"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Title', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('A title to be added on top of the legend', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="legend_title" id="legend_title" value=""
                                                   class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Layout', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The layout of the legend items', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="legend_layout" id="legend_layout">
                                            <option selected="selected" value="horizontal">Horizontal</option>
                                            <option value="vertical">Vertical</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The horizontal alignment of the legend box within the chart area', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="legend_align" id="legend_align">
                                            <option selected="selected" value="center">Center</option>
                                            <option value="left">Left</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="google highcharts" id="legend_vertical_align_row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Vertical align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The vertical alignment of the legend box', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="legend_vertical_align"
                                                id="legend_vertical_align">
                                            <option selected="selected" value="bottom">Bottom</option>
                                            <option value="middle">Middle</option>
                                            <option value="top">Top</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The width of the drawn border around the legend.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="legend_border_width">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="legend_border_width" min="0" value="0"  class="form-control input-sm input-number"
                                                       id="legend_border_width">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="legend_border_width">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The color of the drawn border around the legend.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="legend_border_color" value=""
                                                   class="form-control cp-value wdt-add-picker legend_border_color"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="highcharts">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Border radius', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The border corner radius of the legend.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="legend_border_radius">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="legend_border_radius" value="0" min="0" class="form-control input-sm input-number"
                                                       id="legend_border_radius">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="legend_border_radius">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chartjs apexcharts" id="legend_position_row">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Legend position', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Position of the legend. Possible values are \'top\', \'left\', \'bottom\' and \'right\'', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="legend-position-cjs"
                                                id="legend-position-cjs">
                                            <option selected="selected" value="">Nothing selected</option>
                                            <option value="top">Top</option>
                                            <option value="left">Left</option>
                                            <option value="bottom">Bottom</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-7"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Exporting', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Whether to enable the exporting module', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="exporting" type="checkbox">
                                <label for="exporting"><?php _e('Export chart', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Data labels', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Add data labels to improve readability of the exported chart', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="exporting-data-labels" type="checkbox">
                                <label for="exporting-data-labels"><?php _e('Show data labels', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('File name', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The filename, without extension, to use for the exported chart', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="exporting-file-name" id="exporting-file-name"
                                                   value="" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The width of the original chart when exported.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="wdt-custom-number-input">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="exporting-width">
                                                    <i class="wpdt-icon-minus"></i>
                                                </button>
                                                <input type="number" name="exporting-width" min="0" class="form-control input-sm input-number"
                                                       id="exporting-width">
                                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="exporting-width">
                                                    <i class="wpdt-icon-plus-full"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Button align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Alignment for the export button', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="exporting-button-align"
                                                id="exporting-button-align">
                                            <option selected="selected" value="right">Right</option>
                                            <option value="center">Center</option>
                                            <option value="left">Left</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Button vertical align', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The vertical alignment of the export button', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" name="exporting-button-vertical-align"
                                                id="exporting-button-vertical-align">
                                            <option selected="selected" value="top">Top</option>
                                            <option value="middle">Middle</option>
                                            <option value="bottom">Bottom</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Button color', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The color of the symbol\'s stroke or line.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="cp-container">
                                <div class="form-group">
                                    <div class="fg-line dropdown">
                                        <div id="cp"
                                             class="input-group wdt-color-picker">
                                            <input type="text" id="exporting-button-color" value=""
                                                   class="form-control cp-value wdt-add-picker exporting-button-color"/>
                                            <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Button text', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('A text string to add to the individual button', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="exporting-button-text" id="exporting-button-text"
                                                   value="" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart-container-tabs-8"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Credits', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Whether to show the credits text', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="credits" type="checkbox" checked>
                                <label for="credits"><?php _e('Show credits', 'wpdatatables'); ?></label>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Credits href', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The URL for the credits label', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="credits-href" id="credits-href"
                                                   value="http://www.highcharts.com" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Credits text', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The text for the credits label', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="credits-text" id="credits-text"
                                                   value="Highcharts.com" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id="chart-container-tabs-9"
                     class="col-sm-9 col-md-9 col-lg-8 chart-container chart-options-container tab-pane">
                    <div class="inside">
                        <div>
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Toolbar', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Whether to show the toolbar', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch p-b-16" data-ts-color="blue">
                                <input id="show-toolbar" type="checkbox">
                                <label for="show-toolbar"><?php _e('Show toolbar', 'wpdatatables'); ?></label>
                            </div>
                        </div>

                        <div id="toolbar-buttons-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('Buttons in the toolbar', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('Choose which buttons to show in the toolbar', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        id="toolbar-buttons">
                                    <option value="download" selected="selected"><?php _e('Download', 'wpdatatables'); ?></option>
                                    <option value="selection" selected="selected"><?php _e('Selection', 'wpdatatables'); ?></option>
                                    <option value="zoom" selected="selected"><?php _e('Zoom', 'wpdatatables'); ?></option>
                                    <option value="zoomin" selected="selected"><?php _e('Zoom in', 'wpdatatables'); ?></option>
                                    <option value="zoomout" selected="selected"><?php _e('Zoom out', 'wpdatatables'); ?></option>
                                    <option value="pan" selected="selected"><?php _e('Panning', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div id="apex-file-name-container">
                            <h4 class="c-title-color m-b-2">
                                <?php _e('File name', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php _e('The filename, without extension, to use for the exported chart', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="exporting-file-name" id="apex-exporting-file-name"
                                                   value="" class="form-control input-sm"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-7 col-md-7 col-lg-7">
        <div class="chart-preview-container">
            <div id="google-chart-container"></div>
            <div id="chart-js-container">
                <canvas id="chart-js-canvas"></canvas>
            </div>
            <div id="apex-chart-container"></div>
        </div>
    </div>

</div>
