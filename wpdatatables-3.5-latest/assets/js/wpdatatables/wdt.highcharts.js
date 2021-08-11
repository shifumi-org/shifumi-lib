var wpDataTablesHighchart = function(){

    var obj = {
        container: '#wdtHighChartContainer',
        columnIndexes: [],
        numberFormat: 1,
        connectedWPDataTable: null,
        renderCallback: null,
        chart: null,
        setContainer: function(container){
            this.container = container;
            this.options.chart.renderTo = container.replace('#','');
        },
        getContainer: function(){
            return this.container;
        },
        setWidth: function( width ){
            if( isNaN( width ) || width == null || width == 0 ){
                delete this.options.chart.width;
            }else{
                this.options.chart.width = parseInt( width );
            }
        },
        getWidth: function(){
            return this.options.chart.width;
        },
        setHeight: function( height ){
            this.options.chart.height = parseInt( height );
        },
        getHeight: function(){
            return this.options.chart.height;
        },
        setRenderCallback: function( callback ){
            this.renderCallback = callback;
        },
        options: {
            chart: {
                backgroundColor: '#FFFFFF',
                borderColor: '#4572A7',
                borderRadius: 0,
                borderWidth: 0,
                height: 400,
                inverted: false,
                panning: false,
                panKey: 'shift',
                plotBackgroundColor: 'undefined',
                plotBackgroundImage: 'undefined',
                plotBorderColor: 'undefined',
                plotBorderWidth: 0,
                type: 'line',
                zoomType: 'undefined'
            },
            credits: {
                enabled: true,
                href: 'http://www.highcharts.com',
                text: 'Highcharts.com'
            },
            exporting: {
                buttons: {
                    contextButton: {
                        align: 'right',
                        symbolStroke: '#666',
                        text: null,
                        verticalAlign: 'top'
                    }
                },
                enabled: true,
                chartOptions: {
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: false
                            }
                        }
                    }
                },
                filename: 'Chart',
                width: 'undefined'
            },
            legend: {
                backgroundColor: '#FFFFFF',
                title: {
                    'text': ''
                },
                layout: 'horizontal',
                align: 'right',
                verticalAlign: 'bottom',
                borderWidth: 0,
                borderColor: '#909090'
            },
            plotOptions: {

            },
            series: [

            ],
            subtitle: {
                align: 'center',
                floating: false,
                text: 'undefined'
            },
            title: {
                align: 'center',
                floating: false,
                text: ''
            },
            tooltip:{
                backgroundColor: 'rgba(255, 255, 255, 0.85)',
                borderColor: null,
                borderRadius: 3,
                borderWidth: 1,
                enabled: true,
                shared: false,
                valuePrefix: 'undefined',
                valueSuffix: 'undefined'
            },
            xAxis: {
                crosshair: false
            },
            yAxis: {
                crosshair: false,
                gridLineDashStyle: 'Solid',
                title: {
                    align: 'align',
                    text: ''
                },
                plotLines: [
                    {
                        "value": 0,
                        "width": 1,
                        "color": "#808080"
                    }
                ]
            }
        },
        setOptions: function( options ){
            for( var property in options ){
                this.options[property] = options[property];
            }
            Highcharts.setOptions({
                lang: {
                    decimalPoint: this.getNumberFormat() === '1' ?  ',' : '.',
                    thousandsSep: this.getNumberFormat() === '1' ?  '.' : ','
                }
            });
        },
        setNumberFormat: function( numberFormat ){
            this.numberFormat = numberFormat;
        },
        getNumberFormat:function(){
            return this.numberFormat;
        },
        getOptions: function(){
            return this.options;
        },
        render: function(){
            if( this.renderCallback !== null ){
                this.renderCallback( this );
            }
            this.chart = new Highcharts.Chart( this.options );
        },
        setType: function( type ){
            switch( type ){
                case 'highcharts_basic_area_chart':
                    this.options.chart.type = 'area';
                    break;
                case 'highcharts_stacked_area_chart':
                    this.options.chart.type = 'area';
                    this.options.plotOptions = { area: { stacking: 'normal' } };
                    break;
                case 'highcharts_basic_bar_chart':
                    this.options.chart.type = 'bar';
                    break;
                case 'highcharts_scatter_plot':
                    this.options.chart.type = 'scatter';
                    break;
                case 'highcharts_stacked_bar_chart':
                    this.options.chart.type = 'bar';
                    this.options.plotOptions = { series: { stacking: 'normal' } };
                    break;
                case 'highcharts_basic_column_chart':
                    this.options.chart.type = 'column';
                    break;
                case 'highcharts_3d_column_chart':
                    this.options.chart.type = 'column';
                    this.options.chart.margin = 75;
                    this.options.chart.options3d = {
                        enabled: true,
                        alpha: 15,
                        beta: 15,
                        viewDistance: 25,
                        depth: 40
                    };
                    this.options.plotOptions = { column: { depth: 25 } };
                    break;
                case 'highcharts_stacked_column_chart':
                    this.options.chart.type = 'column';
                    this.options.plotOptions = { column: { stacking: 'normal' } };
                    break;
                case 'highcharts_pie_chart':
                    this.options.chart.type = 'pie';
                    this.options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    };
                    this.options.tooltip.pointFormat = '{series.name}: <b>{point.percentage:.1f}%</b>'
                    break;
                case 'highcharts_3d_pie_chart':
                    this.options.chart.type = 'pie';
                    this.options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            depth: 40
                        }
                    };
                    this.options.tooltip = {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    };
                    this.options.chart.options3d = {
                        enabled: true,
                        alpha: 45,
                        beta: 0,
                        viewDistance: 25,
                        depth: 40
                    };
                    break;
                case 'highcharts_pie_with_gradient_chart':
                    this.options.chart.type = 'pie';
                    // Radialize the colors
                    Highcharts.getOptions().colors = Highcharts.getOptions().colors.map( function (color) {
                        return {
                            radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
                            stops: [
                                [0, color],
                                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                            ]
                        };
                    });
                    this.options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    };
                    this.options.tooltip = {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    };
                    break;
                case 'highcharts_donut_chart':
                    this.options.chart.type = 'pie';
                    this.options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    };
                    this.options.series[0].innerSize = '80%';
                    this.options.tooltip = {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    };
                    break;
                case 'highcharts_3d_donut_chart':
                    this.options.chart.type = 'pie';
                    this.options.chart.options3d = {
                        enabled: true,
                        alpha: 45,
                        beta: 0,
                        viewDistance: 25,
                        depth: 40
                    };
                    this.options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            depth: 40
                        }
                    };
                    this.options.series[0].innerSize = '80%';
                    this.options.tooltip = {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    };
                    break;
                case 'highcharts_gauge_chart':
                    this.options.chart.type = 'gauge';
                    break;
                case 'highcharts_treemap_chart':
                    this.options.chart.type = 'treemap';
                    break;
                case 'highcharts_treemap_level_chart_':
                    this.options.chart.type = 'treemap';
                    break;
                case 'highcharts_polar_chart':
                    this.options.chart.polar = true;
                    break;
                case 'highcharts_spiderweb_chart':
                    this.options.chart.type = 'line';
                    this.options.chart.polar = true;
                    this.options.yAxis =  {
                        gridLineInterpolation: 'polygon',
                        lineWidth: 0,
                        minorTickLength: 0,
                        tickLength: 0,
                    };
                    break;
                case 'highcharts_spline_chart':
                    this.options.chart.type = 'spline';
                    break;
                case 'highcharts_line_chart':
                default:
                    this.options.chart.type = 'line';
                    break;
            }
        },
        refresh: function(){
            this.chart.redraw();
        },
        setColumnIndexes: function( columnIndexes ){
            this.columnIndexes = columnIndexes;
        },
        setMultiplyYaxis: function( chartConfig ){
            if (chartConfig.type == 'highcharts_spline_chart' ||
                chartConfig.type == 'highcharts_line_chart' ||
                chartConfig.type == 'highcharts_basic_column_chart' ||
                chartConfig.type == 'highcharts_basic_area_chart' ||
                chartConfig.type == 'highcharts_basic_bar_chart') {
                var j = 0;

                    Array.isArray(this.options.yAxis) ? this.options.yAxis.splice(1) : '';
                    for (var i in chartConfig.options.series) {
                        this.options.series[j].name = chartConfig.options.series[i].label;
                        this.options.series[j].color = chartConfig.options.series[i].color;
                        if (chartConfig.options.series[i].type)
                            this.options.series[j].type = chartConfig.options.series[i].type;
                            if (Array.isArray(this.options.yAxis)) {
                                if (chartConfig.options.series[i].yAxis) {
                                    this.options.yAxis.push({
                                        title: {
                                            text: chartConfig.options.series[i].label
                                        },
                                        opposite: true
                                    });
                                    this.options.series[j].yAxis = this.options.yAxis.length - 1;
                                } else {
                                    if (this.options.series[j].yAxis !== 'undefined')
                                        delete this.options.series[j].yAxis
                                }

                                j++;
                            }

                    }
            }
        },
        setChartConfig: function( chartConfig ){
            // Chart
            this.setWidth(chartConfig.width);
            chartConfig.height ? this.options.chart.height = chartConfig.height : null;
            this.options.chart.backgroundColor = chartConfig.background_color;
            chartConfig.border_width ?  this.options.chart.borderWidth = chartConfig.border_width : null;
            this.options.chart.borderColor = chartConfig.border_color;
            chartConfig.border_radius ?  this.options.chart.borderRadius = chartConfig.border_radius : null;
            chartConfig.zoom_type ?  this.options.chart.zoomType = chartConfig.zoom_type : null;
            chartConfig.panning ?  this.options.chart.panning = chartConfig.panning : null;
            chartConfig.pan_key ?  this.options.chart.panKey = chartConfig.pan_key : null;
            this.options.chart.plotBackgroundColor = chartConfig.plot_background_color;
            this.options.chart.plotBackgroundImage = chartConfig.plot_background_image;
            chartConfig.plot_border_width ?  this.options.chart.plotBorderWidth = chartConfig.plot_border_width : null;
            this.options.chart.plotBorderColor = chartConfig.plot_border_color;
            // Series
            if ( this.options.chart.type != 'pie' ) {
                var j = 0;

                  Array.isArray(this.options.yAxis) ? this.options.yAxis.splice(1) : '';
                  for (var i in chartConfig.series_data) {
                      this.options.series[j].name = chartConfig.series_data[i].label;
                      this.options.series[j].color = chartConfig.series_data[i].color;
                      if (chartConfig.series_data[i].type)
                          this.options.series[j].type = chartConfig.series_data[i].type;
                      if (Array.isArray(this.options.yAxis)) {
                          if (chartConfig.series_data[i].yAxis) {
                              this.options.yAxis.push({
                                  title: {
                                      text: chartConfig.series_data[i].label
                                  },
                                  opposite: true
                              });
                              this.options.series[j].yAxis = this.options.yAxis.length - 1;
                          } else {
                              if (this.options.series[j].yAxis !== 'undefined')
                                  delete this.options.series[j].yAxis
                          }
                      }

                      j++;
                  }
            }
            // Axes
            if (chartConfig.show_grid == 0) {
                this.options.xAxis.lineWidth = 0;
                this.options.xAxis.minorGridLineWidth = 0;
                this.options.xAxis.lineColor = 'transparent';
                this.options.xAxis.minorTickLength = 0;
                this.options.xAxis.tickLength = 0;
                if (Array.isArray(this.options.yAxis)) {
                    this.options.yAxis[0].lineWidth = 0;
                    this.options.yAxis[0].gridLineWidth = 0;
                    this.options.yAxis[0].minorGridLineWidth = 0;
                    this.options.yAxis[0].lineColor = 'transparent';
                    this.options.yAxis[0].labels = {
                        enabled: false
                    };
                    this.options.yAxis[0].minorTickLength = 0;
                    this.options.yAxis[0].tickLength = 0;
                } else {
                    this.options.yAxis.lineWidth = 0;
                    this.options.yAxis.gridLineWidth = 0;
                    this.options.yAxis.minorGridLineWidth = 0;
                    this.options.yAxis.lineColor = 'transparent';
                    this.options.yAxis.labels = {
                        enabled: false
                    };
                    this.options.yAxis.minorTickLength = 0;
                    this.options.yAxis.tickLength = 0;
                }
            } else {
                delete this.options.xAxis.lineWidth;
                delete this.options.xAxis.minorGridLineWidth;
                delete this.options.xAxis.lineColor;
                delete this.options.xAxis.minorTickLength;
                delete this.options.xAxis.tickLength;
                if (Array.isArray(this.options.yAxis)) {
                    delete this.options.yAxis[0].lineWidth;
                    delete this.options.yAxis[0].gridLineWidth;
                    delete this.options.yAxis[0].minorGridLineWidth;
                    delete this.options.yAxis[0].lineColor;
                    this.options.yAxis[0].labels = {
                        enabled: true
                    };
                    this.options.yAxis[0].minorTickLength = 0;
                    this.options.yAxis[0].tickLength = 0;
                } else {
                    delete this.options.yAxis.lineWidth;
                    delete this.options.yAxis.gridLineWidth;
                    delete this.options.yAxis.minorGridLineWidth;
                    delete this.options.yAxis.lineColor;
                    this.options.yAxis.labels = {
                        enabled: true
                    };
                    this.options.yAxis.minorTickLength = 0;
                    this.options.yAxis.tickLength = 0;
                }
            }

            if ( chartConfig.chart_type == 'highcharts_spiderweb_chart' ) {
                this.options.xAxis.tickmarkPlacement = 'on';
                this.options.xAxis.lineWidth = 0;
                this.options.yAxis.gridLineInterpolation = 'polygon';
                this.options.yAxis.lineWidth = 0;
                this.options.yAxis.min = 0;
            }
            if (Array.isArray(this.options.yAxis)) {
                chartConfig.highcharts_line_dash_style ? this.options.yAxis[0].gridLineDashStyle = chartConfig.highcharts_line_dash_style : null;
                chartConfig.vertical_axis_crosshair == 1 ? this.options.yAxis[0].crosshair = true : this.options.yAxis[0].crosshair = false;
                chartConfig.horizontal_axis_label ? this.options.yAxis[0].title = { text: chartConfig.vertical_axis_label } : null;
                chartConfig.vertical_axis_min ? this.options.yAxis[0].min = chartConfig.vertical_axis_min : this.options.yAxis[0].min = undefined;
                chartConfig.vertical_axis_max ? this.options.yAxis[0].max = chartConfig.vertical_axis_max : this.options.yAxis[0].max = undefined;
            } else {
                chartConfig.highcharts_line_dash_style ? this.options.yAxis.gridLineDashStyle = chartConfig.highcharts_line_dash_style : null;
                chartConfig.vertical_axis_crosshair == 1 ? this.options.yAxis.crosshair = true : this.options.yAxis.crosshair = false;
                chartConfig.horizontal_axis_label ? this.options.yAxis.title = { text: chartConfig.vertical_axis_label } : null;
                chartConfig.vertical_axis_min ? this.options.yAxis.min = chartConfig.vertical_axis_min : this.options.yAxis.min = undefined;
                chartConfig.vertical_axis_max ? this.options.yAxis.max = chartConfig.vertical_axis_max : this.options.yAxis.max = undefined;
            }
            chartConfig.vertical_axis_label ? this.options.xAxis.title = { text: chartConfig.horizontal_axis_label } : null;
            chartConfig.horizontal_axis_crosshair == 1 ? this.options.xAxis.crosshair = true : this.options.xAxis.crosshair = false;
            chartConfig.inverted == 1 ? this.options.chart.inverted = true : this.options.chart.inverted = false;
            // Title
            chartConfig.show_title == 1 ? this.options.title.text = chartConfig.chart_title : this.options.title.text = '';
            chartConfig.title_floating == 1 ? this.options.title.floating = true : this.options.title.floating = false;
            chartConfig.title_align ? this.options.title.align = chartConfig.title_align : null;
            chartConfig.subtitle ? this.options.subtitle.text = chartConfig.subtitle : this.options.subtitle.text = null;
            chartConfig.subtitle_align ? this.options.subtitle.align = chartConfig.subtitle_align : null;
            // Tooltip
            chartConfig.tooltip_enabled == 1 ? this.options.tooltip.enabled = true : this.options.tooltip.enabled = false;
            this.options.tooltip.backgroundColor = chartConfig.tooltip_background_color ? chartConfig.tooltip_background_color : 'rgba(247,247,247,0.85)';
            chartConfig.tooltip_border_width ? this.options.tooltip.borderWidth = chartConfig.tooltip_border_width : null;
            this.options.tooltip.borderColor = chartConfig.tooltip_border_color;
            chartConfig.tooltip_border_radius ? this.options.tooltip.borderRadius = chartConfig.tooltip_border_radius : null;
            chartConfig.tooltip_shared == 1 ? this.options.tooltip.shared = true : this.options.tooltip.shared = false;
            this.options.tooltip.valuePrefix = chartConfig.tooltip_value_prefix;
            this.options.tooltip.valueSuffix = chartConfig.tooltip_value_suffix;
            // Legend
            if (!(chartConfig.chart_type == 'highcharts_treemap_level_chart')) {
                chartConfig.show_legend == 1 ? this.options.legend.enabled = true : this.options.legend.enabled = false;
                this.options.legend.backgroundColor = chartConfig.legend_background_color;
                chartConfig.legend_title ? this.options.legend.title.text = chartConfig.legend_title : null;
                chartConfig.legend_layout ? this.options.legend.layout = chartConfig.legend_layout : null;
                chartConfig.legend_align ? this.options.legend.align = chartConfig.legend_align : null;
                chartConfig.legend_vertical_align ? this.options.legend.verticalAlign = chartConfig.legend_vertical_align : null;
                chartConfig.legend_border_width ? this.options.legend.borderWidth = chartConfig.legend_border_width : null;
                this.options.legend.borderColor = chartConfig.legend_border_color;
                chartConfig.legend_border_radius ? this.options.legend.borderRadius = chartConfig.legend_border_radius : null;
            }
            // Exporting
            chartConfig.exporting == 1 ? this.options.exporting.enabled = true : this.options.exporting.enabled = false;
            chartConfig.exporting_data_labels == 1 ? this.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled = true : this.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled = false;
            chartConfig.exporting_file_name ? this.options.exporting.filename = chartConfig.exporting_file_name : null;
            chartConfig.exporting_width ? this.options.exporting.width = chartConfig.exporting_width : null;
            chartConfig.exporting_button_align ? this.options.exporting.buttons.contextButton.align = chartConfig.exporting_button_align : null;
            chartConfig.exporting_button_vertical_align ? this.options.exporting.buttons.contextButton.verticalAlign = chartConfig.exporting_button_vertical_align : null;
            this.options.exporting.buttons.contextButton.symbolStroke = chartConfig.exporting_button_color ? chartConfig.exporting_button_color : '#666666';
            chartConfig.exporting_button_text ? this.options.exporting.buttons.contextButton.text = chartConfig.exporting_button_text : null;
            // Credits
            chartConfig.credits == 1 ? this.options.credits.enabled = true : this.options.credits.enabled = false;
            chartConfig.credits_href ? this.options.credits.href = chartConfig.credits_href: null;
            chartConfig.credits_text ? this.options.credits.text = chartConfig.credits_text: null;

        },
        getColumnIndexes: function(){
            return this.columnIndexes;
        },
        setConnectedWPDataTable: function( wpDataTable ){
            this.connectedWPDataTable = wpDataTable;
        },
        getConnectedWPDataTable: function(){
            return this.connectedWPDataTable;
        },
        setGrouping: function( group_chart ){
            this.group_chart = group_chart;
        },
        getGrouping: function(){
            return this.group_chart;
        },
        enableFollowFiltering: function(){
            if( this.connectedWPDataTable == null ){ return; }
            if (typeof(this.options.plotOptions.series) != "undefined") {
                this.options.plotOptions.series.animation = false;
            } else {
                this.options.plotOptions.series = {animation: false};
            }
            this.numberFormat = JSON.parse( jQuery( '#'+this.connectedWPDataTable.data('described-by') ).val() ).number_format;
            this.connectedWPDataTable.fnSettings().aoDrawCallback.push({
                sName: 'chart_filter_follow',
                fn: function( oSettings ){
                    obj.options.xAxis.categories = [];
                    var serieIndex = 0;
                    var filteredData = obj.connectedWPDataTable._('tr', {"filter": "applied"}).toArray();
                    for( var j in obj.columnIndexes ){
                        var seriesDataEntry = [];
                        if( ( obj.columnIndexes.length > 0 )
                            && ( j == 0 ) ){
                            for( var i in filteredData ){
                                obj.options.xAxis.categories.push( filteredData[i][obj.columnIndexes[j]] );
                            }
                        }else{
                            for( var i in filteredData ){
                                var entry = filteredData[i][obj.columnIndexes[j]];
                                if( obj.options.chart.type == 'pie' ){
                                    if( obj.numberFormat == 1 ){
                                        seriesDataEntry.push({
                                            name: obj.options.xAxis.categories[i],
                                            y: parseFloat( wdtUnformatNumber(entry, '.', ',', true) )
                                        });
                                    }else{
                                        seriesDataEntry.push({
                                            name: obj.options.xAxis.categories[i],
                                            y: parseFloat( wdtUnformatNumber(entry, ',', '.', true) )
                                        });
                                    }
                                }else if (obj.options.chart.type == 'treemap'){
                                    if (obj.numberFormat == 1) {
                                        seriesDataEntry.push({
                                            colorValue: parseFloat(wdtUnformatNumber(entry, '.', ',', true)),
                                            name: obj.options.xAxis.categories[i],
                                            value: parseFloat(wdtUnformatNumber(entry, '.', ',', true))
                                        });
                                    }else{
                                        seriesDataEntry.push({
                                            colorValue: parseFloat(wdtUnformatNumber(entry, ',', '.', true)),
                                            name: obj.options.xAxis.categories[i],
                                            value: parseFloat(wdtUnformatNumber(entry, ',', '.', true))
                                        });
                                    }
                                }else{
                                    if( obj.numberFormat == 1 ){
                                        seriesDataEntry.push({
                                            name: obj.options.xAxis.categories[i],
                                            y: parseFloat( wdtUnformatNumber(entry, '.', ',', true) )
                                        });
                                    }else{
                                        seriesDataEntry.push({
                                            name: obj.options.xAxis.categories[i],
                                            y: parseFloat( wdtUnformatNumber(entry, ',', '.', true) )
                                        });
                                    }
                                }
                            }

                            if ( obj.group_chart == 1 ){
                                var output = [];
                                for (var i in seriesDataEntry) {
                                    if (typeof output !== 'undefined' && output.length > 0) {
                                        var value_key = 'none';
                                        for(var j in output){
                                            if(value_key === 'none'){
                                                if(output[j]['name'] == seriesDataEntry[i]['name']){
                                                    value_key = j;
                                                }
                                            }
                                        }
                                        if (value_key === 'none') {
                                            output.push(seriesDataEntry[i]);
                                        } else {
                                            for(var n in seriesDataEntry[i]) {
                                                if( n != 'name'){
                                                    output[value_key][n] += seriesDataEntry[i][n];
                                                }
                                            }
                                        }
                                    } else {
                                        output.push(seriesDataEntry[i]);
                                    }
                                }

                                seriesDataEntry = output;

                            }

                            obj.options.series[serieIndex].data = seriesDataEntry;
                            serieIndex++;
                        }
                    }
                    if ( obj.group_chart == 1 ) {
                        obj.options.xAxis.categories = obj.options.xAxis.categories.filter(function(itm,i,a){
                            return i==a.indexOf(itm);
                        });
                    }

                    if( obj.chart !== null ){
                        obj.chart.destroy();
                    }
                    if( obj.renderCallback !== null ){
                        obj.renderCallback( obj );
                    }
                    obj.chart = new Highcharts.Chart( obj.options );
                    Highcharts.setOptions({
                        lang: {
                            decimalPoint: obj.getNumberFormat() === 1 ?  ',' : '.',
                            thousandsSep: obj.getNumberFormat() === 1 ?  '.' : ','
                        }
                    });
                }
            });
        }
    };

    return obj;

};
