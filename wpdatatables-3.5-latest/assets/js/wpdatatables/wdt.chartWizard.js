if (typeof constructedChartData == 'undefined') {
    var constructedChartData = {};
}

var wdtChartColumnsData = {};


(function ($) {
    var wdtChartPickerDragStart = 0;
    var wdtChartPickerDragEnd = 0;
    var wdtChartPickerIsDragging = false;
    var wdtChart = null;
    var nextStepButton = $('#wdt-chart-wizard-next-step');
    var previousStepButton = $('#wdt-chart-wizard-previous-step');

    $('.wdt-chart-wizard-chart-selecter-block .card').on('click', function () {
        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').addClass('not-selected');
        $(this).addClass('selected').removeClass('not-selected');
        nextStepButton.prop('disabled', false);
    });

    /**
     * Steps switcher (Next)
     */
    nextStepButton.click(function (e) {
        e.preventDefault();

        var curStep = $('div.chart-wizard-step:visible').data('step');
        $('div.chart-wizard-step').hide();
        $('li.chart_wizard_breadcrumbs_block').removeClass('active');
        $('.wdt-preload-layer').animateFadeIn();

        switch (curStep) {
            case 'step1':
                // Data source
                constructedChartData.chart_type = $('.wdt-chart-wizard-chart-selecter-block .card.selected').data('type');
                constructedChartData.min_columns = parseInt($('.card.selected').data('min_columns'));
                constructedChartData.max_columns = parseInt($('.card.selected').data('max_columns'));
                $('div.chart-wizard-step.step2').show();
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                constructedChartData.chart_title = $('#chart-name').val();
                constructedChartData.engine = $('#chart-render-engine').val();
                if ($('#chart-render-engine').val() == 'google') {

                    $("#chart-js-container").hide();
                    $("#google-chart-container").show();

                    $(".highcharts").hide();
                    $(".chartjs").hide();
                    $(".google").show();

                    $('#curve-type-row').hide();
                    $('#three-d-row').hide();
                    $('#background_color_row').show();
                    $('#border_width_row').show();
                    $('#border_color_row').show();
                    $('#border_radius_row').show();
                    $('#plot_background_color_row').show();
                    $('#plot-border-width-row').show();
                    $('#plot_border_color_row').show();
                    $('#font-size-row').show();
                    $('#font-name-row').show();
                    $('.series').show();
                    $('.axes').show();
                    $('#show-grid-row').show();
                    $('#horizontal-axis-crosshair-row').show();
                    $('#vertical-axis-crosshair-row').show();
                    $('.title').show();
                    $('#title-floating-row').show();
                    $('.tooltip').show();
                    $('.legend').show();
                    $('#inverted-row').show();

                    switch (constructedChartData.chart_type) {
                        case 'google_column_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_histogram':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_bar_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_stacked_bar_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('#inverted-row').hide();
                            break;
                        case 'google_line_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'google_stepped_area_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_pie_chart':
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('#three-d-row').show();
                            $('.axes').hide();
                            $('#title-floating-row').hide();
                            $('.series').hide();
                            break;
                        case 'google_bubble_chart':
                            $('.legend').hide();
                            break;
                        case 'google_donut_chart':
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('.axes').hide();
                            $('#title-floating-row').hide();
                            $('.series').hide();
                            break;
                        case 'google_gauge_chart':
                            $('#background_color_row').hide();
                            $('#border_width_row').hide();
                            $('#border_color_row').hide();
                            $('#border_radius_row').hide();
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('#font-size-row').hide();
                            $('#font-name-row').hide();
                            $('#show-grid-row').hide();
                            $('.axes').hide();
                            $('.title').hide();
                            $('.tooltip').hide();
                            $('.legend').hide();
                            $('.series').hide();
                            break;
                        case 'google_scatter_chart':
                            $('#inverted-row').hide();
                            break;
                        case 'google_candlestick_chart':
                            $('.series').hide();
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('.legend').hide();
                            break;
                        case 'google_waterfall_chart':
                            $('.series').hide();
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('.legend').hide();
                            break;
                    }
                } else if ($('#chart-render-engine').val() == 'highcharts') {

                    $("#chart-js-container").hide();
                    $("#google-chart-container").show();

                    $(".google").hide();
                    $(".chartjs").hide();
                    $(".highcharts").show();

                    $('#border_width_row').show();
                    $('#border_color_row').show();
                    $('#border_radius_row').show();
                    $('#zoom-type-row').show();
                    $('#panning-row').show();
                    $('#pan-key-row').show();
                    $('#plot-border-width-row').show();
                    $('#plot_border_color_row').show();
                    $('.series').show();
                    $('.axes').show();
                    $('.legend').show();

                    if (constructedChartData.chart_type == 'highcharts_pie_chart' ||
                        constructedChartData.chart_type == 'highcharts_pie_with_gradient_chart' ||
                        constructedChartData.chart_type == 'highcharts_3d_pie_chart' ||
                        constructedChartData.chart_type == 'highcharts_donut_chart' ||
                        constructedChartData.chart_type == 'highcharts_3d_donut_chart' ||
                        constructedChartData.chart_type == 'highcharts_treemap_chart' ||
                        constructedChartData.chart_type == 'highcharts_treemap_level_chart'
                    ) {
                        $('#border_width_row').hide();
                        $('#border_color_row').hide();
                        $('#border_radius_row').hide();
                        $('#zoom-type-row').hide();
                        $('#panning-row').hide();
                        $('#pan-key-row').hide();
                        $('#plot-border-width-row').hide();
                        $('#plot_border_color_row').hide();
                        $('.axes').hide();
                        $('.legend').hide();
                        $('.series').hide();
                    }

                    if (constructedChartData.chart_type == 'highcharts_spiderweb_chart' ||
                        constructedChartData.chart_type == 'highcharts_polar_chart') {
                        $('.axes').hide();
                        $('#border_width_row').hide();
                        $('#border_color_row').hide();
                        $('#border_radius_row').hide();
                        $('#zoom-type-row').hide();
                        $('#panning-row').hide();
                        $('#pan-key-row').hide();
                        $('#plot-border-width-row').hide();
                        $('#plot_border_color_row').hide();
                    }
                } else {

                    $("#google-chart-container").hide();
                    $("#chart-js-container").show();

                    $(".google").hide();
                    $(".highcharts").hide();
                    $(".chartjs").show();

                    $('.series').show();
                    $('#curve-type-row').hide();

                    switch (constructedChartData.chart_type) {
                        case 'chartjs_line_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_area_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_stacked_area_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_bubble_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_polar_area_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_pie_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_doughnut_chart':
                            $('.series').hide();
                            break;
                    }
                }

                previousStepButton.prop('disabled', false);
                previousStepButton.animateFadeIn();
                $('#wpdatatables-chart-source').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step2':
                // Data range
                $('.wdt-preload-layer').animateFadeOut();
                applyDragula();
                nextStepButton.prop('disabled', true);
                nextStepButton.hide();
                constructedChartData.wpdatatable_id = $('#wpdatatables-chart-source').val();
                $('div.chart-wizard-step.step3').show();
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'wpdatatables_get_columns_data_by_table_id',
                        table_id: constructedChartData.wpdatatable_id,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    success: function (columns) {
                        wdtChartColumnsData = columns;
                        var columnChartTemplate = $.templates("#wdt-chart-column-block");
                        var columnChartBlockHtml = columnChartTemplate.render({columns: columns});
                        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container').html(columnChartBlockHtml);

                        if ((typeof constructedChartData.selected_columns !== 'undefined')
                            || (typeof editing_chart_data !== 'undefined')) {
                            var columns = (typeof editing_chart_data !== 'undefined') ? editing_chart_data.selected_columns : constructedChartData.selected_columns;
                            $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container .chart-column-block').remove();
                            for (var i in columns) {
                                $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block[data-orig_header="' + columns[i] + '"]')
                                    .appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container');
                            }
                        }
                        $('#wdt-add-chart-columns').click();
                        nextStepButton.show();
                    }
                });
                break;
            case 'step3':
                // Formatting
                constructedChartData.follow_filtering = $('#follow-table-filtering').is(':checked') ? 1 : 0;
                if (typeof constructedChartData.selected_columns == 'undefined') {
                    constructedChartData.selected_columns = {};
                }

                // Move string column on first place
                if ($('div.chosen_columns div.chart-column-block.string,' +
                    'div.chosen_columns div.chart-column-block.date,' +
                    'div.chosen_columns div.chart-column-block.datetime,' +
                    'div.chosen_columns div.chart-column-block.time,' +
                    'div.chosen_columns div.chart-column-block.link').length
                    && (!$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('float')
                        || !$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('int'))) {
                    $('div.chosen_columns div.chart-column-block.string,' +
                        'div.chosen_columns div.chart-column-block.date,' +
                        'div.chosen_columns div.chart-column-block.datetime,' +
                        'div.chosen_columns div.chart-column-block.time,' +
                        'div.chosen_columns div.chart-column-block.link')
                        .eq(0)
                        .prependTo('div.wdt-chart-wizard-chosen-columns-container')
                }

                constructedChartData.selected_columns = {};
                constructedChartData.series_data = {};
                $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
                    constructedChartData.selected_columns[parseInt($(this).index())] = $(this).data('orig_header');
                });

                if (typeof editing_chart_data !== 'undefined') {
                    if (!_.isEqual(constructedChartData.selected_columns, editing_chart_data.selected_columns)) {
                        editing_chart_data.render_data.series = editing_chart_data.render_data.series.filter(function (editColumns) {
                            return Object.values(constructedChartData.selected_columns).indexOf(editColumns.orig_header) !== -1;
                        });
                    }
                }


                // Set initial width for preview
                if (constructedChartData.width == null) {
                    if (typeof editing_chart_data !== 'undefined') {
                        if (editing_chart_data.render_data.options.responsive_width != 1 && editing_chart_data.render_data.options.width == null) {
                            constructedChartData.width = 400;
                        }
                    } else {
                        constructedChartData.width = 400;
                    }
                }

                $('#wdt-chart-row-range-type').change();

                $('#series-settings-container').empty();

                getInputData();
                nextStepButton.hide();
                // Render chart first time in preview
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_show_chart_from_data',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function (data) {
                        $('div.chart-wizard-step.step4').show();
                        $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');

                        //Series
                        var seriesBlockTemplate = $.templates("#wdt-chart-series-setting-block");

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i = 0; i < data.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.render_data.series.length; j++) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header) {
                                            data.series[i].label = data.columns[i + 1].label = editing_chart_data.render_data.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.series});

                        } else if (constructedChartData.engine == 'highcharts') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.highcharts_render_data != null &&
                                constructedChartData.chart_type != 'highcharts_treemap_chart' && constructedChartData.chart_type != 'highcharts_treemap_level_chart_') {
                                for (i = 0; i < data.options.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.highcharts_render_data.options.series.length; j++) {
                                        if (data.options.series[i].orig_header === editing_chart_data.highcharts_render_data.options.series[j].orig_header) {
                                            data.options.series[i].label = editing_chart_data.highcharts_render_data.options.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.series});

                        } else if (constructedChartData.engine == 'chartjs') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.chartjs_render_data != null && constructedChartData.chart_type !== 'chartjs_bubble_chart') {
                                for (i = 0; i < data.options.data.datasets.length; i++) {
                                    for (j = 0; j < editing_chart_data.chartjs_render_data.options.data.datasets.length; j++) {
                                        if (data.options.data.datasets[i].orig_header === editing_chart_data.chartjs_render_data.options.data.datasets[j].orig_header) {
                                            data.options.data.datasets[i].label = editing_chart_data.chartjs_render_data.options.data.datasets[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.data.datasets});
                        }

                        $('#series-settings-container').html(seriesBlockTemplateHtml);

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i in data.series) {
                                    for (j in editing_chart_data.render_data.series) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header &&
                                            typeof (editing_chart_data.render_data.options.series[j]) !== 'undefined') {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.render_data.options.series[j].color);
                                            data.options.series[i] = {
                                                color: editing_chart_data.render_data.options.series[j].color
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.series[i].color);
                                }
                            }
                        } else if (constructedChartData.engine == 'highcharts') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.highcharts_render_data != null &&
                                constructedChartData.chart_type != 'highcharts_treemap_chart' && constructedChartData.chart_type != 'highcharts_treemap_level_chart_') {
                                for (i in data.options.series) {
                                    for (j in editing_chart_data.highcharts_render_data.options.series) {
                                        if (data.options.series[i].orig_header === editing_chart_data.highcharts_render_data.options.series[j].orig_header) {
                                            if (constructedChartData.chart_type == 'highcharts_spline_chart' ||
                                                constructedChartData.chart_type == 'highcharts_line_chart' ||
                                                constructedChartData.chart_type == 'highcharts_basic_column_chart' ||
                                                constructedChartData.chart_type == 'highcharts_basic_area_chart' ||
                                                constructedChartData.chart_type == 'highcharts_basic_bar_chart' ||
                                                constructedChartData.chart_type == 'highcharts_polar_chart' ||
                                                constructedChartData.chart_type == 'highcharts_spiderweb_chart') {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.highcharts_render_data.options.series[j].color;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-type select').val(editing_chart_data.highcharts_render_data.options.series[j].type);
                                                data.options.series[i].type = editing_chart_data.highcharts_render_data.options.series[j].type;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').val(editing_chart_data.highcharts_render_data.options.series[j].yAxis);
                                                data.options.series[i].yAxis = editing_chart_data.highcharts_render_data.options.series[j].yAxis
                                            } else {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.highcharts_render_data.options.series[j].color;
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.series[i].color);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-type select').val(data.options.series[i].type);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').val(data.options.series[i].yAxis);
                                }
                            }
                        } else if (constructedChartData.engine == 'chartjs') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.chartjs_render_data != null && constructedChartData.chart_type !== 'chartjs_bubble_chart') {
                                for (i in data.options.data.datasets) {
                                    for (j in editing_chart_data.chartjs_render_data.options.data.datasets) {
                                        if (data.options.data.datasets[i].orig_header === editing_chart_data.chartjs_render_data.options.data.datasets[j].orig_header) {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.chartjs_render_data.options.data.datasets[j].borderColor);
                                            data.options.data.datasets[i].borderColor = editing_chart_data.chartjs_render_data.options.data.datasets[j].borderColor;
                                            data.options.data.datasets[i].backgroundColor = editing_chart_data.chartjs_render_data.options.data.datasets[j].backgroundColor;
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.data.datasets) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.data.datasets[i].borderColor);
                                }
                            }
                        }

                        $(".chart-series-color .wdt-add-picker").each(function (i) {
                            jQuery(this).addClass('pickr');
                            jQuery(this)
                                .closest('.wdt-color-picker')
                                .find('.wpcolorpicker-icon i')
                                .css("background", this.value);
                        });


                        if (constructedChartData.engine == 'google') {
                            wdtChart = new wpDataTablesGoogleChart();
                            wdtChart.setType(data.type);
                            wdtChart.setColumns(data.columns);
                            wdtChart.setRows(data.rows);
                            wdtChart.setOptions(data.options);
                            wdtChart.setContainer('google-chart-container');
                            wdtChart.setColumnIndexes(data.column_indexes);
                        } else if (constructedChartData.engine == 'highcharts') {
                            wdtChart = new wpDataTablesHighchart();
                            wdtChart.setOptions(data.options);
                            wdtChart.setMultiplyYaxis(data);
                            wdtChart.setType(data.type);
                            wdtChart.setWidth(data.width);
                            wdtChart.setHeight(data.height);
                            wdtChart.setColumnIndexes(data.column_indexes);
                            wdtChart.setContainer('#google-chart-container');
                            wdtChart.setNumberFormat(data.wdtNumberFormat);
                        } else if (constructedChartData.engine == 'chartjs') {
                            if (wdtChart !== null) {
                                wdtChart.chart.destroy();
                            }
                            wdtChart = new wpDataTablesChartJS();
                            var container = document.getElementById("chart-js-container");
                            var canvas = document.getElementById("chart-js-canvas");
                            wdtChart.setData(data.options.data);
                            wdtChart.setOptions(data.options.options);
                            wdtChart.setGlobalOptions(data.options.globalOptions);
                            wdtChart.setType(data.configurations.type);
                            wdtChart.setContainer(container);
                            wdtChart.setCanvas(canvas);
                            wdtChart.setContainerOptions(data.configurations);
                        }
                        wdtChart.render();
                        if (constructedChartData.chart_type == 'google_bubble_chart') {
                            $('.chart-series-color').hide();
                        }
                        $('.selectpicker').selectpicker('refresh');

                        if (constructedChartData.engine == 'google' || constructedChartData.engine == 'chartjs'
                            || constructedChartData.chart_type == 'highcharts_stacked_area_chart'
                            || constructedChartData.chart_type == 'highcharts_scatter_plot'
                            || constructedChartData.chart_type == 'highcharts_stacked_bar_chart'
                            || constructedChartData.chart_type == 'highcharts_3d_column_chart'
                            || constructedChartData.chart_type == 'highcharts_stacked_column_chart'
                            || constructedChartData.chart_type == 'highcharts_gauge_chart'
                        ) {
                            $('div.chart-series-type').hide();
                            $('div.chart-show-yaxis').hide();
                        } else if (constructedChartData.chart_type == 'highcharts_polar_chart'
                            || constructedChartData.chart_type == 'highcharts_spiderweb_chart') {
                            $('div.chart-series-label').hide();
                            $('div.chart-show-yaxis').hide();

                        } else {
                            for (var i in data.options.series) {
                                if (data.options.series[i].yAxis) {
                                    $('#show-yaxis-' + i).prop('checked', 'checked');
                                } else {
                                    $('#show-yaxis-' + i).prop('checked', '');
                                }
                            }
                            wdtChart.render();

                        }

                        var eTop = $('.chart-preview-container').offset().top;
                        var eWidth = $('.chart-preview-container').width();

                        $(window).scroll(function () {
                            if (eTop - $(window).scrollTop() <= 30) {
                                $('.chart-preview-container').css('position', 'fixed').css('right', 48).css('top', 30).css('width', eWidth);
                            } else {
                                eWidth = $('.chart-preview-container').width();
                                $('.chart-preview-container').css('position', 'relative').css('right', '').css('top', '').css('width', '');
                            }
                        });

                        $('#chart-series-color,' +
                            '#background-color-container,' +
                            '#border-color-container,' +
                            '#plot-background-color-container,' +
                            '#plot-border-color-container,' +
                            '#font-color-container,' +
                            '#title-font-color-container input.title-font-color,' +
                            '#tooltip-background-color-container,' +
                            '#tooltip-border-color-container,' +
                            '#legend_background_color,' +
                            '#legend_border_color,' +
                            '#exporting_button_color_container input.exporting-button-color'
                        ).on('change', function (e, ui) {
                            e.stopImmediatePropagation()
                            e.preventDefault()
                            renderChart(false);
                        });

                        // Render chart on changing chart options
                        $('div.step4 input:not(#group-chart), div.step4 select')
                            .on('change', function () {
                                renderChart(false);
                            });

                        $('input#group-chart')
                            .on('change', function () {
                                renderChart(true);
                            });

                        nextStepButton.show().addClass('wdt-save-chart').html('<i class="wpdt-icon-save"></i>' + wpdatatablesEditStrings.saveChart)
                        $('.wdt-preload-layer').animateFadeOut();
                    }
                });
                break;
            case 'step4':
                getInputData();
                // Save and get shortcode
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_save_chart_get_shortcode',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        $('div.chart-wizard-step.step5').show();
                        $('li.chart_wizard_breadcrumbs_block.step5').addClass('active');
                        $('#wdt-chart-shortcode-id').html(data.shortcode);
                        constructedChartData.chart_id = data.id;
                        $('#wp-data-chart-id').val(data.id);
                        $('.wdt-preload-layer').animateFadeOut();
                        nextStepButton.prop('disabled', true);
                        nextStepButton.hide();
                        $('#finishButton').show();
                    }
                });
                break;
        }
    });

    function renderChart(reloadNeeded) {
        if (typeof reloadNeeded == 'undefined') {
            reloadNeeded = true;
        }

        getInputData();

        if (reloadNeeded) {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'wpdatatable_show_chart_from_data',
                    chart_data: constructedChartData,
                    wdtNonce: $('#wdtNonce').val(),
                },
                dataType: 'json',
                type: 'post',
                success: function (data) {
                    if (constructedChartData.engine == 'google') {
                        wdtChart = new wpDataTablesGoogleChart();
                        wdtChart.setType(data.type);
                        wdtChart.setColumns(data.columns);
                        wdtChart.setRows(data.rows);
                        wdtChart.setOptions(data.options);
                        wdtChart.setContainer('google-chart-container');
                        wdtChart.setColumnIndexes(data.column_indexes);
                    } else if (constructedChartData.engine == 'highcharts') {
                        wdtChart = new wpDataTablesHighchart();
                        wdtChart.setOptions(data.options);
                        wdtChart.setMultiplyYaxis(data);
                        wdtChart.setType(data.type);
                        wdtChart.setWidth(data.width);
                        wdtChart.setHeight(data.height);
                        wdtChart.setColumnIndexes(data.column_indexes);
                        wdtChart.setContainer('#google-chart-container');
                    } else if (constructedChartData.engine == 'chartjs') {
                        wdtChart.chart.destroy();
                        wdtChart = new wpDataTablesChartJS();
                        var container = document.getElementById("chart-js-container");
                        var canvas = document.getElementById("chart-js-canvas");
                        wdtChart.setData(data.options.data);
                        wdtChart.setGlobalOptions(data.options.globalOptions);
                        wdtChart.setOptions(data.options.options);
                        wdtChart.setType(data.configurations.type);
                        wdtChart.setContainer(container);
                        wdtChart.setCanvas(canvas);
                        wdtChart.setContainerOptions(data.configurations);
                    }
                    wdtChart.render();
                    $('.wdt-preload-layer').animateFadeOut();

                }
            });
        } else {
            if (constructedChartData.engine == 'chartjs') {
                wdtChart.chart.destroy();
            }
            wdtChart.setChartConfig(constructedChartData);
            wdtChart.render();
        }

    }

    // Get input fields data
    function getInputData() {
        //Chart
        constructedChartData.width = parseInt($('#chart-width').val());
        constructedChartData.responsive_width = $('#chart-responsive-width').is(':checked') ? 1 : 0;
        constructedChartData.height = parseInt($('#chart-height').val());
        constructedChartData.group_chart = $('#group-chart').is(':checked') ? 1 : 0;
        constructedChartData.background_color = $('input.background-color').val();
        constructedChartData.border_width = parseInt($('#border-width').val() ? $('#border-width').val() : 0);
        constructedChartData.border_color = $('input.border_color').val();
        constructedChartData.border_radius = parseInt($('#border-radius').val() ? $('#border-radius').val() : 0);
        constructedChartData.zoom_type = $('#zoom-type').val();
        constructedChartData.panning = $('#panning').is(':checked') ? 1 : 0;
        constructedChartData.pan_key = $('#pan-key').val();
        constructedChartData.plot_background_color = $('input.plot-background-color').val();
        constructedChartData.plot_background_image = $('#plot-background-image').val();
        constructedChartData.plot_border_width = $('#plot-border-width').val();
        constructedChartData.plot_border_color = $('input.plot-border-color').val();
        constructedChartData.font_size = $('#font-size').val();
        constructedChartData.font_name = $('#font-name').val();
        constructedChartData.font_style = $('#font-style').val();
        constructedChartData.font_color = $('input.font-color').val();
        constructedChartData.three_d = $('#three-d').is(':checked') ? 1 : 0;
        // Series
        if (typeof constructedChartData.series_data == 'undefined') {
            constructedChartData.series_data = {};
        }
        if (constructedChartData.engine == 'google' || constructedChartData.engine == 'chartjs'
            || constructedChartData.chart_type == 'highcharts_stacked_area_chart'
            || constructedChartData.chart_type == 'highcharts_scatter_plot'
            || constructedChartData.chart_type == 'highcharts_stacked_bar_chart'
            || constructedChartData.chart_type == 'highcharts_3d_column_chart'
            || constructedChartData.chart_type == 'highcharts_stacked_column_chart'
            || constructedChartData.chart_type == 'highcharts_gauge_chart'
        ) {
            $('div.chart-series-block').each(function (e) {
                constructedChartData.series_data[$(this).data('orig_header')] = {
                    label: $(this).find('input.series-label').val(),
                    color: $(this).find('input.series-color').val()
                }
            });
        } else {
            $('div.chart-series-block').each(function (e) {
                constructedChartData.series_data[$(this).data('orig_header')] = {
                    label: $(this).find('input.series-label').val(),
                    color: $(this).find('input.series-color').val(),
                    type: $(this).find('select#series-type').val(),
                    yAxis: $('input#show-yaxis-' + e).is(':checked') ? 1 : 0
                }
            });
        }

        constructedChartData.curve_type = $('#curve-type').is(':checked') ? 1 : 0;
        // Axes
        constructedChartData.show_grid = $('#show-grid').is(':checked') ? 1 : 0;
        constructedChartData.highcharts_line_dash_style = $('#highcharts-line-dash-style').val();
        constructedChartData.horizontal_axis_label = $('#horizontal-axis-label').val();
        constructedChartData.horizontal_axis_crosshair = $('#horizontal-axis-crosshair').is(':checked') ? 1 : 0;
        constructedChartData.horizontal_axis_direction = $('#horizontal-axis-direction').val();
        constructedChartData.vertical_axis_label = $('#vertical-axis-label').val();
        constructedChartData.vertical_axis_crosshair = $('#vertical-axis-crosshair').is(':checked') ? 1 : 0;
        constructedChartData.vertical_axis_direction = $('#vertical-axis-direction').val();
        constructedChartData.vertical_axis_min = $('#vertical-axis-min').val();
        constructedChartData.vertical_axis_max = $('#vertical-axis-max').val();
        constructedChartData.inverted = $('#inverted').is(':checked') ? 1 : 0;
        // Title
        constructedChartData.show_title = $('#show-chart-title').is(':checked') ? 1 : 0;
        constructedChartData.title_floating = $('#title-floating').is(':checked') ? 1 : 0;
        constructedChartData.title_align = $('#title-align').val();
        constructedChartData.title_position = $('#title-position').val();
        constructedChartData.title_font_size = $('#title_font_size').val();
        constructedChartData.title_font_name = $('#title-font-name').val();
        constructedChartData.title_font_style = $('#title-font-style').val();
        constructedChartData.title_font_color = $('input#title-font-color').val();
        constructedChartData.subtitle = $('#subtitle').val();
        constructedChartData.subtitle_align = $('#subtitle-align').val();
        // Tooltip
        constructedChartData.tooltip_enabled = $('#tooltip-enabled').is(':checked') ? 1 : 0;
        constructedChartData.tooltip_background_color = $('input.tooltip-background-color').val();
        constructedChartData.tooltip_border_width = $('#tooltip-border-width').val();
        constructedChartData.tooltip_border_color = $('input.tooltip-border-color').val();
        constructedChartData.tooltip_border_radius = $('#tooltip-border-radius').val();
        constructedChartData.tooltip_shared = $('#tooltip-shared').is(':checked') ? 1 : 0;
        constructedChartData.tooltip_value_prefix = $('#tooltip-value-prefix').val();
        constructedChartData.tooltip_value_suffix = $('#tooltip-value-suffix').val();
        // Legend
        constructedChartData.show_legend = $('#show-legend').is(':checked') ? 1 : 0;
        constructedChartData.legend_position = $('#legend_position').val();
        constructedChartData.legend_background_color = $('input.legend_background_color').val();
        constructedChartData.legend_title = $('#legend_title').val();
        constructedChartData.legend_layout = $('#legend_layout').val();
        constructedChartData.legend_align = $('#legend_align').val();
        constructedChartData.legend_vertical_align = $('#legend_vertical_align').val();
        constructedChartData.legend_border_width = $('#legend_border_width').val();
        constructedChartData.legend_border_color = $('input.legend_border_color').val();
        constructedChartData.legend_border_radius = $('#legend_border_radius').val();
        constructedChartData.legend_position_cjs = $('#legend-position-cjs').val();

        // Exporting
        constructedChartData.exporting = $('#exporting').is(':checked') ? 1 : 0;
        constructedChartData.exporting_data_labels = $('#exporting-data-labels').is(':checked') ? 1 : 0;
        constructedChartData.exporting_file_name = $('#exporting-file-name').val();
        constructedChartData.exporting_width = $('#exporting-width').val();
        constructedChartData.exporting_button_align = $('#exporting-button-align').val();
        constructedChartData.exporting_button_vertical_align = $('#exporting-button-vertical-align').val();
        constructedChartData.exporting_button_color = $('input.exporting-button-color').val();
        constructedChartData.exporting_button_text = $('#exporting-button-text').val();
        // Credits
        constructedChartData.credits = $('#credits').is(':checked') ? 1 : 0;
        constructedChartData.credits_href = $('#credits-href').val();
        constructedChartData.credits_text = $('#credits-text').val();
    }

    /**
     * Steps switcher (Prev)
     */
    previousStepButton.click(function (e) {
        e.preventDefault();

        $('.wdt-preload-layer').animateFadeIn();
        var curStep = $('div.chart-wizard-step:visible').data('step');

        switch (curStep) {
            case 'step2':
                previousStepButton.prop('disabled', true);
                previousStepButton.hide();
                $('div.chart-wizard-step.step1').show();
                $('div.chart-wizard-step.step2').hide();
                $('li.chart_wizard_breadcrumbs_block.step2').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step1').addClass('active');
                $('#chart-render-engine').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step3':
                $('div.chart-wizard-step.step2').show();
                $('div.chart-wizard-step.step3').hide();
                $('li.chart_wizard_breadcrumbs_block.step3').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step4':
                $('div.chart-wizard-step.step3').show();
                $('div.chart-wizard-step.step4').hide();
                $('li.chart_wizard_breadcrumbs_block.step4').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                nextStepButton.removeClass('wdt-save-chart').html('Next ')
                nextStepButton.prop('disabled', false);
                break;
            case 'step5':
                $('div.chart-wizard-step.step4').show();
                $('div.chart-wizard-step.step5').hide();
                $('li.chart_wizard_breadcrumbs_block.step5').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');
                nextStepButton.prop('disabled', false);
                nextStepButton.show();
                $('#finishButton').hide();
                $('.wdt-preload-layer').animateFadeOut();
                break;
        }
    });

    /**
     * Open chart browser on finish
     */
    $('#finishButton').click(function (e) {
        e.preventDefault();
        window.location = $('#wdt-browse-charts-url').val();
    });

    /**
     * Pick the chart type
     */
    $('#chart-render-engine').change(function (e) {
        e.preventDefault();
        nextStepButton.prop('disabled', true);
        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').removeClass('not-selected');
        $('div.charts-type').hide();
        if ($(this).val() != '') {
            constructedChartData.chart_engine = $(this).val();
            if ($(this).val() == 'google') {
                $('div.google-charts-type').show();
            } else if ($(this).val() == 'highcharts') {
                $('div.highcharts-charts-type').show();
            } else if ($(this).val() == 'chartjs') {
                $('div.chartjs-charts-type').show();
            }
        }
    });

    /**
     * Pick the data type
     */
    $('#wpdatatables-chart-source').change(function (e) {
        e.preventDefault();
        if ($(this).val() == '') {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    });

    /**
     * Responsive width checkbox
     */
    $('#chart-responsive-width').change(function (e) {
        if ($(this).is(':checked')) {
            $('#chart-width').val('0');
            $('#btn-plus-chart-width').prop('disabled', true);
            $('#btn-minus-chart-width').prop('disabled', true);
            $('#chart-width').prop('readonly', 'readonly');
        } else {
            $('#btn-plus-chart-width').prop('disabled', false);
            $('#btn-minus-chart-width').prop('disabled', false);
            $('#chart-width').prop('readonly', '');
            $('#chart-width').val('400');
        }
    });

    /**
     * Select all columns in the column selecter
     */
    $('button.select-all-columns, button.deselect-all-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('select-all-columns')) {
            $(this).closest('.card').find('div.chart-column-block').addClass('selected');
            $(this).text('Deselect All');
        } else {
            $(this).closest('.card').find('div.chart-column-block').removeClass('selected');
            $(this).text('Select All');
        }
        $(this).toggleClass('select-all-columns deselect-all-columns');

    });

    /**
     * Select a column in chart row range picker
     */
    $(document).on('click', 'div.wdt-chart-column-picker-container div.chart-column-block', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });

    /**
     * Check for limit of string columns
     */
    function checkColumnsLimit() {
        // 1 - Checking for string columns
        var string_columns = 0;
        var valid = true;
        $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
            if (
                $(this).hasClass('string')
                || $(this).hasClass('link')
                || $(this).hasClass('email')
                || $(this).hasClass('image')
                || $(this).hasClass('date')
                || $(this).hasClass('datetime')
                || $(this).hasClass('time')
                || $(this).hasClass('masterdetail')
            ) {
                string_columns++;
            }
        });
        if (string_columns > 1) {
            $('div.chosen_columns div.strings-error').show();
            valid = false;
        } else {
            $('div.chosen_columns div.strings-error').hide();
        }
        // 2 - Checking for min and max columns limit
        var totalColumnCount = $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').length;
        if (totalColumnCount < constructedChartData.min_columns) {
            $('div.chosen_columns div.min-columns-error').show();
            $('div.chosen_columns div.min-columns-error span.columns').html(constructedChartData.min_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.min-columns-error').hide();
        }
        if ((constructedChartData.max_columns > 0)
            && (totalColumnCount > constructedChartData.max_columns)) {
            $('div.chosen_columns div.max-columns-error').show();
            $('div.chosen_columns div.max-columns-error span.columns').html(constructedChartData.max_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.max-columns-error').hide();
        }
        if (!valid) {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    }

    /**
     * Add columns to chart
     */
    $('#wdt-add-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container');
        });
        checkColumnsLimit();
    });

    /**
     * Add all columns to chart
     */
    $('#wdt-add-all-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-add-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').removeClass('selected');
        checkColumnsLimit();
    });

    /**
     * Remove columns from chart series
     */
    $('#wdt-remove-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container ');
        });
        checkColumnsLimit();
    });

    /**
     * Remove all columns from chart
     */
    $('#wdt-remove-all-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-remove-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
    });

    /**
     * Change the range type
     */
    $('#wdt-chart-row-range-type').change(function (e) {
        e.preventDefault();
        //e.stopImmediatePropagation();
        if ($(this).val() == 'all_rows') {
            constructedChartData.range_type = 'all_rows';
            $('#range_picked_info span').html('All');
            $('#open-range-picker-btn').hide();
            $('label[for=follow-table-filtering]').removeClass('disabled');
            $('input#follow-table-filtering').removeAttr('disabled');
        } else {
            constructedChartData.range_type = 'picked_range';
            $('#open-range-picker-btn').show();
            if (typeof constructedChartData.range_data == 'undefined') {
                constructedChartData.range_data = [];
            }
            $('label[for=follow-table-filtering]').addClass('disabled');
            $('input#follow-table-filtering').attr('disabled', 'disabled');
        }
    });

    /**
     * Update the picked range
     */
    var wdtUpdateChartRange = function () {
        $('table.range-picker-table td').removeClass('selected');
        $('table.range-picker-table tbody tr').each(function () {
            if ($(this).find('td.pick-row input.add-row-to-range').is(':checked')) {
                $(this).find('td').not('.pick-row').each(function () {
                    if ($('table.range-picker-table thead th:eq(' + $(this).index() + ') input.pick-column-range:checked').length) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                });
            }
        });
    };

    /**
     * Open the range picker
     */
    $('#open-range-picker-btn').click(function (e) {
        e.preventDefault();
        if (typeof constructedChartData.selected_columns == 'undefined') {
            constructedChartData.selected_columns = {};
        }
        $('.wdt-preload-layer').animateFadeIn();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wpdatatables_get_complete_table_json_by_id',
                table_id: constructedChartData.wpdatatable_id,
                wdtNonce: $('#wdtNonce').val(),
            },
            success: function (tableData) {
                // Extract the column headers
                if (tableData.length > 0) {
                    var columnHeaders = [];
                    var selectedRows = constructedChartData.range_data;
                    for (var columnHeader in tableData[0]) {
                        for (var i in wdtChartColumnsData) {
                            if (wdtChartColumnsData[i].orig_header == columnHeader) {
                                var checked = 0;
                                if (typeof constructedChartData.selected_columns[wdtChartColumnsData[i].id] != 'undefined') {
                                    checked = 1;
                                }
                                columnHeaders.push({
                                    header: columnHeader,
                                    id: wdtChartColumnsData[i].id,
                                    checked: checked
                                });
                                break;
                            }
                        }
                    }

                    for (var k = 0; k < tableData.length; k++) {
                        var rowChecked = 0;
                        for (l = 0; l < selectedRows.length; l++) {
                            if (selectedRows[l] == k) {
                                rowChecked = 1;
                                break;
                            }
                        }
                        tableData[k]['rowChecked'] = rowChecked;
                    }

                    var rangePickerTemplate = $.templates("#range-picker-block");
                    var rangePickerHTML = rangePickerTemplate.render({
                        columnHeaders: columnHeaders,
                        tableData: tableData
                    });
                    $('#pick-range-table-container').html(rangePickerHTML);
                    $('.wdt-preload-layer').animateFadeOut();
                    $('#wdt-range-picker').modal('show');
                    wdtUpdateChartRange();
                }
            }
        });
    });

    /**
     * Add/remove row to range
     */
    $(document).on('change', '#wdt-range-picker table input.add-row-to-range, #wdt-range-picker table input.pick-column-range', function (e) {
        e.preventDefault();
        wdtUpdateChartRange();
    });

    $(document).on('click', '#wdt-range-picker table input.add-row-to-range', function (e) {
        e.stopImmediatePropagation();
    });

    function wdtRangePickerMouseDown(e) {
        if (e.target.nodeName == 'INPUT') {
            return;
        }
        if (isRightClick(e)) {
            return false;
        } else {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragStart = allCells.index($(this));
            wdtChartPickerIsDragging = true;

            if (typeof e.preventDefault != 'undefined') {
                e.preventDefault();
            }
            document.documentElement.onselectstart = function () {
                return false;
            };
        }
    }

    function wdtRangePickerMouseUp(e) {
        if (e.target.nodeName == 'INPUT') {
            wdtUpdateChartRange();
            return;
        }
        if (isRightClick(e)) {
            return false;
        } else {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragEnd = allCells.index($(this));

            wdtChartPickerIsDragging = false;
            if (wdtChartPickerDragEnd != 0) {
                wdtRangePickerSelectRange();
            }

            document.documentElement.onselectstart = function () {
                return true;
            };
        }
    }

    function wdtRangePickerMouseMove(e) {
        if (wdtChartPickerIsDragging) {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragEnd = allCells.index($(this));
            wdtRangePickerSelectRange();
        }
    }

    function wdtRangePickerSelectRange() {

        $firstSelected = $("#wdt-range-picker table tbody td").eq(wdtChartPickerDragStart);
        $lastSelected = $("#wdt-range-picker table tbody td").eq(wdtChartPickerDragEnd);
        // Reset all the selected columns and rows
        $('#wdt-range-picker input.pick-column-range').prop('checked', false);
        $('#wdt-range-picker input.add-row-to-range').prop('checked', false);

        // Get the selected columns indexes
        var startColumnIndex = $firstSelected.index();
        var endColumnIndex = $lastSelected.index();

        if (startColumnIndex < endColumnIndex + 1) {
            $('#wdt-range-picker table thead th').slice(startColumnIndex, endColumnIndex + 1).find('input.pick-column-range').prop('checked', true);
        } else {
            $('#wdt-range-picker table thead th').slice(endColumnIndex, startColumnIndex + 1).find('input.pick-column-range').prop('checked', true);
        }

        // Get the selected rows indexes
        var startRowIndex = $firstSelected.parent().index();
        var endRowIndex = $lastSelected.parent().index();

        if (startRowIndex < endRowIndex + 1) {
            $('#wdt-range-picker table tbody tr').slice(startRowIndex, endRowIndex + 1).find('input.add-row-to-range').prop('checked', true);
        } else {
            $('#wdt-range-picker table tbody tr').slice(endRowIndex, startRowIndex + 1).find('input.add-row-to-range').prop('checked', true);
        }

        wdtUpdateChartRange();
    }


    $(document)
        .on('mousedown', '#wdt-range-picker table tbody td', wdtRangePickerMouseDown)
        .on('mouseup', '#wdt-range-picker table tbody td', wdtRangePickerMouseUp)
        .on('mousemove', '#wdt-range-picker table tbody td', wdtRangePickerMouseMove);

    /**
     * Submit the pick range
     */
    $('#submit-pick-range').click(function (e) {
        e.preventDefault();
        // First update the picked columns range
        // Remove all columns
        $('#wdt-remove-all-chart-columns').click();
        // Deselect all columns
        $('div.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
        // Select the columns picked in the range picker
        $('#wdt-range-picker table input.pick-column-range:checked').each(function () {
            var column_id = $(this).closest('th').data('column_id');
            $('div.wdt-chart-wizart-existing-columns-container div.chart-column-block[data-column_id="' + column_id + '"]').addClass('selected');
        });
        // Add the columns
        $('#wdt-add-chart-columns').click();
        // Add the selected row indexes
        var selectedIndexes = [];
        $('#wdt-range-picker table input.add-row-to-range:checked').each(function () {
            selectedIndexes.push($(this).closest('tr').data('index'));
        });
        constructedChartData.range_data = selectedIndexes;
        // Update the counter in the row range data
        $('#range_picked_info span').html(selectedIndexes.length);
        $('#wdt-range-picker').modal('hide');
    });

    /**
     * Load data for editing existing charts
     */
    $(document).ready(function () {
        if ($('#wp-data-chart-id').val() != '') {

            $('#chart-render-engine').change();
            constructedChartData.chart_id = $('#wp-data-chart-id').val();
            constructedChartData.chart_title = editing_chart_data.title;
            // General settings
            $('.charts-type').find("[data-type='" + editing_chart_data.type + "']").click();
            $('#wpdatatables-chart-source').val(editing_chart_data.wpdatatable_id);

            if (editing_chart_data.range_type == 'picked_range') {
                $('#wdt-chart-row-range-type').val('pick_rows').change();
                constructedChartData.range_data = editing_chart_data.row_range;
                constructedChartData.selected_columns = editing_chart_data.selected_columns;
                $('#range_picked_info span').html(constructedChartData.range_data.length);
            }

            if (editing_chart_data.follow_filtering == 1) {
                $('#follow-table-filtering').prop('checked', 'checked');
            } else {
                $('#follow-table-filtering').prop('checked', '');
            }

            // Chart
            if (typeof editing_chart_data.render_data.options.width !== 'undefined') {
                $('#chart-width').val(editing_chart_data.render_data.options.width);
                $('#chart-responsive-width').prop('checked', '');
            } else {
                $('#chart-responsive-width').prop('checked', 'checked');
                $('#chart-width').val(0);
                $('#chart-width').prop('readonly', 'readonly');
            }
            $('#chart-height').val(editing_chart_data.render_data.options.height);

            if (editing_chart_data.render_data.group_chart) {
                $('#group-chart').prop('checked', 'checked');
            } else {
                $('#group-chart').prop('checked', '');
            }

            // Axes
            if (editing_chart_data.render_data.show_grid == null) {
                $('#show-grid').prop('checked', 'checked');
            } else {
                if (editing_chart_data.render_data.show_grid) {
                    $('#show-grid').prop('checked', 'checked');
                } else {
                    $('#show-grid').prop('checked', '');
                }
            }
            $('#horizontal-axis-label').val(editing_chart_data.render_data.options.hAxis.title);
            $('#vertical-axis-label').val(editing_chart_data.render_data.options.vAxis.title);

            // Title
            if (editing_chart_data.render_data.options.title) {
                $('#show-chart-title').prop('checked', 'checked');
            } else {
                $('#show-chart-title').prop('checked', '');
            }

            if (editing_chart_data.engine == 'google') {
                // Chart
                if (editing_chart_data.render_data.options.backgroundColor == null) {
                    $('input.background-color').val('');
                    $('#border-width').val('');
                    $('input.border_color').val('');
                    $('#border-radius').val('');

                } else {
                    if (editing_chart_data.render_data.options.backgroundColor.fill) {
                        $('#background-color-container').colorpicker('setValue', editing_chart_data.render_data.options.backgroundColor.fill);
                    }
                    $('#border-width').val(editing_chart_data.render_data.options.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.backgroundColor.stroke) {
                        $('#border-color-container').colorpicker('setValue', editing_chart_data.render_data.options.backgroundColor.stroke);
                    }
                    $('#border-radius').val(editing_chart_data.render_data.options.backgroundColor.rx);
                }

                if (editing_chart_data.render_data.options.chartArea == null) {
                    $('input.plot-background-color').val('');
                    $('#plot-border-width').val('');
                    $('input.plot-border-color').val('');
                } else {
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.fill) {
                        $('#plot-background-color-container').colorpicker('setValue', editing_chart_data.render_data.options.chartArea.backgroundColor.fill);
                    }
                    $('#plot-border-width').val(editing_chart_data.render_data.options.chartArea.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.stroke) {
                        $('#plot-border-color-container').colorpicker('setValue', editing_chart_data.render_data.options.chartArea.backgroundColor.stroke);
                    }
                }

                if (editing_chart_data.render_data.options.fontSize == null) {
                    $('#font-size').val('');
                } else {
                    $('#font-size').val(editing_chart_data.render_data.options.fontSize);
                }
                if (editing_chart_data.render_data.options.fontName == null) {
                    $('#font-name').val('Arial');
                } else {
                    $('#font-name').val(editing_chart_data.render_data.options.fontName);
                }

                if (editing_chart_data.render_data.options.is3D) {
                    $('#three-d').prop('checked', 'checked');
                } else {
                    $('#three-d').prop('checked', '');
                }

                // Series
                if (editing_chart_data.render_data.options.curveType == 'none') {
                    $('#curve-type').prop('checked', '');
                } else {
                    $('#curve-type').prop('checked', 'checked');
                }

                // Axes
                if (editing_chart_data.render_data.options.crosshair == null) {
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.crosshair.orientation == 'both') {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                        $('#vertical-axis-crosshair').prop('checked', 'checked');
                    } else if (editing_chart_data.render_data.options.crosshair.orientation == 'horizontal') {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                        $('#vertical-axis-crosshair').prop('checked', '');
                    } else if (editing_chart_data.render_data.options.crosshair.orientation == 'vertical') {
                        $('#horizontal-axis-crosshair').prop('checked', '');
                        $('#vertical-axis-crosshair').prop('checked', 'checked');
                    }
                }


                if (editing_chart_data.render_data.options.hAxis.direction == null) {
                    $('#horizontal-axis-direction').val(1);
                } else {
                    $('#horizontal-axis-direction').val(editing_chart_data.render_data.options.hAxis.direction);
                }

                if (editing_chart_data.render_data.options.vAxis.direction == null) {
                    $('#vertical-axis-direction').val(1);
                } else {
                    $('#vertical-axis-direction').val(editing_chart_data.render_data.options.vAxis.direction);
                }

                if (editing_chart_data.render_data.options.vAxis.viewWindow == null) {
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                } else {
                    $('#vertical-axis-min').val(editing_chart_data.render_data.options.vAxis.viewWindow.min);
                    $('#vertical-axis-max').val(editing_chart_data.render_data.options.vAxis.viewWindow.max);
                }

                if (editing_chart_data.render_data.options.orientation == null) {
                    $('#inverted').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.orientation == 'vertical') {
                        $('#inverted').prop('checked', 'checked');
                    }
                }

                // Title
                if (editing_chart_data.render_data.options.titlePosition == null) {
                    $('#title-floating').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.titlePosition == 'in') {
                        $('#title-floating').prop('checked', 'checked');
                    }
                }

                // Tooltip
                if (editing_chart_data.render_data.options.tooltip == null) {
                    $('#tooltip-enabled').prop('checked', 'checked');
                } else {
                    if (editing_chart_data.render_data.options.tooltip.trigger == 'none') {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                }

                // Legend
                if (editing_chart_data.render_data.options.legend == null) {
                    $('#legend_position').val('right');
                    $('#legend_vertical_align').val("bottom");
                } else {
                    $('#legend_position').val(editing_chart_data.render_data.options.legend.position);
                    if (editing_chart_data.render_data.options.legend.alignment == 'end') {
                        $('#legend_vertical_align').val("bottom");
                    } else if (editing_chart_data.render_data.options.legend.alignment == 'center') {
                        $('#legend_vertical_align').val("middle");
                    } else {
                        $('#legend_vertical_align').val("top");
                    }
                }
            } else if (editing_chart_data.engine == 'highcharts') {

                if (editing_chart_data.highcharts_render_data == null) {
                    // Chart
                    $('input.background-color').val('');
                    $('#border-width').val(0);
                    $('input.border_color').val('');
                    $('#border-radius').val(0);
                    $('#zoom-type').val('none');
                    $('#panning').prop('checked', '');
                    $('#pan-key').val('shift');
                    $('input.plot-background-color').val('');
                    $('#plot-background-image').val('');
                    $('#plot-border-width').val(0);
                    $('input.plot-border-color').val('');

                    // Axes
                    $('#highcharts-line-dash-style').val('solid');
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                    $('#inverted').prop('checked', '');

                    // Title
                    $('#title-floating').prop('checked', '');
                    $('#title-align').val('center');
                    $('#subtitle').val('');
                    $('#subtitle-align').val('center');

                    // Tooltip
                    $('#tooltip-enabled').prop('checked', 'checked');
                    $('input.tooltip-background-color').val('');
                    $('#tooltip-border-width').val(1);
                    $('input.tooltip-border-color').val('');
                    $('#tooltip-border-radius').val(3);
                    $('#tooltip-shared').prop('checked', '');
                    $('#tooltip-value-prefix').val('');
                    $('#tooltip-value-suffix').val('');

                    // Legend
                    $('#show-legend').prop('checked', 'checked');
                    $('input.legend_background_color').val('');
                    $('#legend_title').val('');
                    $('#legend_layout').val('horizontal');
                    $('#legend_align').val('center');
                    $('#legend_vertical_align').val('bottom');
                    $('#legend_border_width').val(0);
                    $('input.legend_border_color').val('');
                    $('#legend_border_radius').val(0);

                    // Exporting
                    $('#exporting').prop('checked', 'checked');
                    $('#exporting-data-labels').prop('checked', '');
                    $('#exporting-file-name').val('');
                    $('#exporting-width').val('');
                    $('#exporting-button-align').val('right');
                    $('#exporting-button-vertical-align').val('top');
                    $('input.exporting-button-color').val('');
                    $('#exporting-button-text').val('');

                    // Credits
                    $('#credits').prop('checked', 'checked');
                    $('#credits-href').val('http://www.highcharts.com');
                    $('#credits-text').val('Highcharts.com');

                } else {
                    // Chart
                    if (editing_chart_data.highcharts_render_data.options.chart.backgroundColor) {
                        $('#background-color-container').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.chart.backgroundColor);
                    }
                    $('#border-width').val(editing_chart_data.highcharts_render_data.options.chart.borderWidth);
                    if (editing_chart_data.highcharts_render_data.options.chart.borderColor) {
                        $('input.border_color').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.chart.borderColor);
                    }
                    $('#border-radius').val(editing_chart_data.highcharts_render_data.options.chart.borderRadius);
                    $('#zoom-type').val(editing_chart_data.highcharts_render_data.options.chart.zoomType);
                    if (editing_chart_data.highcharts_render_data.options.chart.panning) {
                        $('#panning').prop('checked', 'checked');
                    } else {
                        $('#panning').prop('checked', '');
                    }
                    $('#pan-key').val(editing_chart_data.highcharts_render_data.options.chart.panKey);
                    if (editing_chart_data.highcharts_render_data.options.chart.plotBackgroundColor) {
                        $('#plot-background-color-container').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.chart.plotBackgroundColor);
                    }
                    $('#plot-background-image').val(editing_chart_data.highcharts_render_data.options.chart.plotBackgroundImage);
                    $('#plot-border-width').val(editing_chart_data.highcharts_render_data.options.chart.plotBorderWidth);
                    if (editing_chart_data.highcharts_render_data.options.chart.plotBorderColor) {
                        $('#plot-border-color-container').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.chart.plotBorderColor);
                    }
                    if (editing_chart_data.highcharts_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }

                    // Axes
                    if (Array.isArray(editing_chart_data.highcharts_render_data.options.yAxis)) {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highcharts_render_data.options.yAxis[0].gridLineDashStyle);
                    } else {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highcharts_render_data.options.yAxis.gridLineDashStyle);
                    }
                    if (editing_chart_data.highcharts_render_data.options.xAxis.crosshair) {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                    } else {
                        $('#horizontal-axis-crosshair').prop('checked', '');
                    }
                    if (Array.isArray(editing_chart_data.highcharts_render_data.options.yAxis)) {
                        if (editing_chart_data.highcharts_render_data.options.yAxis[0].crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highcharts_render_data.options.yAxis[0].min);
                        $('#vertical-axis-max').val(editing_chart_data.highcharts_render_data.options.yAxis[0].max);
                    } else {
                        if (editing_chart_data.highcharts_render_data.options.yAxis.crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highcharts_render_data.options.yAxis.min);
                        $('#vertical-axis-max').val(editing_chart_data.highcharts_render_data.options.yAxis.max);
                    }
                    if (editing_chart_data.highcharts_render_data.options.chart.inverted) {
                        $('#inverted').prop('checked', 'checked');
                    } else {
                        $('#inverted').prop('checked', '');
                    }
                    // Title
                    if (editing_chart_data.highcharts_render_data.options.title.floating) {
                        $('#title-floating').prop('checked', 'checked');
                    } else {
                        $('#title-floating').prop('checked', '');
                    }
                    $('#title-align').val(editing_chart_data.highcharts_render_data.options.title.align);
                    $('#subtitle').val(editing_chart_data.highcharts_render_data.options.subtitle.text);
                    $('#subtitle-align').val(editing_chart_data.highcharts_render_data.options.subtitle.align);

                    // Tooltip
                    if (editing_chart_data.highcharts_render_data.options.tooltip.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.highcharts_render_data.options.tooltip.backgroundColor) {
                        $('#tooltip-background-color-container').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.tooltip.backgroundColor);
                    }
                    $('#tooltip-border-width').val(editing_chart_data.highcharts_render_data.options.tooltip.borderWidth);
                    if (editing_chart_data.highcharts_render_data.options.tooltip.borderColor) {
                        $('#tooltip-border-color-container').colorpicker('setValue', editing_chart_data.highcharts_render_data.options.tooltip.borderColor);
                    }
                    $('#tooltip-border-radius').val(editing_chart_data.highcharts_render_data.options.tooltip.borderRadius);
                    if (editing_chart_data.highcharts_render_data.options.tooltip.shared) {
                        $('#tooltip-shared').prop('checked', 'checked');
                    } else {
                        $('#tooltip-shared').prop('checked', '');
                    }
                    $('#tooltip-value-prefix').val(editing_chart_data.highcharts_render_data.options.tooltip.valuePrefix);
                    $('#tooltip-value-suffix').val(editing_chart_data.highcharts_render_data.options.tooltip.valueSuffix);

                    // Legend
                    if (editing_chart_data.highcharts_render_data.options.legend.enabled) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('input.legend_background_color').val(editing_chart_data.highcharts_render_data.options.legend.backgroundColor);
                    $('#legend_title').val(editing_chart_data.highcharts_render_data.options.legend.title.text);
                    $('#legend_layout').val(editing_chart_data.highcharts_render_data.options.legend.layout);
                    $('#legend_align').val(editing_chart_data.highcharts_render_data.options.legend.align);
                    $('#legend_vertical_align').val(editing_chart_data.highcharts_render_data.options.legend.verticalAlign);
                    $('#legend_border_width').val(editing_chart_data.highcharts_render_data.options.legend.borderWidth);
                    $('input.legend_border_color').val(editing_chart_data.highcharts_render_data.options.legend.borderColor);
                    $('#legend_border_radius').val(editing_chart_data.highcharts_render_data.options.legend.borderRadius);

                    // Exporting
                    if (editing_chart_data.highcharts_render_data.options.exporting.enabled) {
                        $('#exporting').prop('checked', 'checked');
                    } else {
                        $('#exporting').prop('checked', '');
                    }
                    if (editing_chart_data.highcharts_render_data.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled) {
                        $('#exporting-data-labels').prop('checked', 'checked');
                    } else {
                        $('#exporting-data-labels').prop('checked', '');
                    }
                    $('#exporting-file-name').val(editing_chart_data.highcharts_render_data.options.exporting.filename);
                    $('#exporting-width').val(editing_chart_data.highcharts_render_data.options.exporting.width);
                    $('#exporting-button-align').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.align);
                    $('#exporting-button-vertical-align').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.verticalAlign);
                    $('input.exporting-button-color').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.symbolStroke);
                    $('#exporting-button-text').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.text);

                    // Credits
                    if (editing_chart_data.highcharts_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }
                    $('#credits-href').val(editing_chart_data.highcharts_render_data.options.credits.href);
                    $('#credits-text').val(editing_chart_data.highcharts_render_data.options.credits.text);
                }

            } else if (editing_chart_data.engine == 'chartjs') {
                // Chart
                if (editing_chart_data.chartjs_render_data.configurations.canvas.backgroundColor) {
                    $('#background-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.configurations.canvas.backgroundColor);
                }
                $('#border-width').val(editing_chart_data.chartjs_render_data.configurations.canvas.borderWidth);
                if (editing_chart_data.chartjs_render_data.configurations.canvas.borderColor) {
                    $('#border-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.configurations.canvas.borderColor);
                }
                $('#border-radius').val(editing_chart_data.chartjs_render_data.configurations.canvas.borderRadius);
                if (editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontSize == null) {
                    $('#font-size').val('');
                } else {
                    $('#font-size').val(editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontSize);
                }
                $('#font-name').val(editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontFamily);
                $('#font-style').val(editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontStyle);
                if (editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontColor) {
                    $('#font-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.options.globalOptions.defaultFontColor);
                }

                // Series
                if (editing_chart_data.type !== 'chartjs_bubble_chart' && editing_chart_data.chartjs_render_data.options.data.datasets[0].lineTension == 0.4) {
                    $('#curve-type').prop('checked', 'checked');
                } else {
                    $('#curve-type').prop('checked', '');
                }

                // Axes
                $('#horizontal-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.xAxes[0].scaleLabel.labelString);
                $('#vertical-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].scaleLabel.labelString);
                editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.beginAtZero ?
                    $('#vertical-axis-min').val(0) : $('#vertical-axis-min').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.min);
                $('#vertical-axis-max').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.max);

                // Title
                $('#title-position').val(editing_chart_data.chartjs_render_data.options.options.title.position);
                $('#title-font-name').val(editing_chart_data.chartjs_render_data.options.options.title.fontFamily);
                $('#title-font-style').val(editing_chart_data.chartjs_render_data.options.options.title.fontStyle);
                if (editing_chart_data.chartjs_render_data.options.options.title.fontColor) {
                    $('#title-font-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.options.options.title.fontColor);
                }

                // Tooltip
                if (editing_chart_data.chartjs_render_data.options.options.tooltips.enabled) {
                    $('#tooltip-enabled').prop('checked', 'checked');
                } else {
                    $('#tooltip-enabled').prop('checked', '');
                }
                if (editing_chart_data.chartjs_render_data.options.options.title.fontColor) {
                    $('#tooltip-background-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.options.options.tooltips.backgroundColor);
                }
                $('#tooltip-border-radius').val(editing_chart_data.chartjs_render_data.options.options.tooltips.cornerRadius);
                if (editing_chart_data.chartjs_render_data.options.options.tooltips.mode == 'label') {
                    $('#tooltip-shared').prop('checked', 'checked');
                } else {
                    $('#tooltip-shared').prop('checked', '');
                }

                // Legend
                if (editing_chart_data.chartjs_render_data.options.options.legend.display) {
                    $('#show-legend').prop('checked', 'checked');
                } else {
                    $('#show-legend').prop('checked', '');
                }
                $('#legend-position-cjs').val(editing_chart_data.chartjs_render_data.options.options.legend.position);

            }

        }
    });

    function applyDragula() {
        var drake = dragula([document.querySelector('.wdt-chart-wizart-existing-columns-container'), document.querySelector('.wdt-chart-wizard-chosen-columns-container')], {
            invalid: function (el, target) {
                if (el.classList.contains('alert')) {
                    return true;
                }
            }
        });
        drake.on('drop', function () {
            checkColumnsLimit();
        });
    }
})(jQuery);


/**
 * Helper func to check if right mousebutton was clicked
 */
function isRightClick(e) {
    if (e.which) {
        return (e.which == 3);
    } else if (e.button) {
        return (e.button == 2);
    }
    return false;
}

/**
 * Helper func to convert hex to rgb color
 */
function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
