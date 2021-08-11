var wpDataTablesChartJS = function(){

    var obj = {
        container: '#chart-js-container',
        canvas: '#chart-js-canvas',
        columnIndexes: [],
        connectedWPDataTable: null,
        renderCallback: null,
        chart: null,
        pieCharts: ['chartjs_polar_area_chart', 'chartjs_pie_chart', 'chartjs_doughnut_chart'],
        setContainer: function(container){
            this.container = container;
        },
        setCanvas: function(canvas){
            this.canvas = canvas;
        },
        setContainerOptions: function(options){
            if(options.container.width == 0){
                this.container.style.width = null;
            } else {
                this.container.style.width = options.container.width + 'px';
            }
            this.container.style.height = options.container.height + 'px';
            this.canvas.style.backgroundColor = options.canvas.backgroundColor;
            this.canvas.style.border = options.canvas.borderWidth + 'px solid ' + options.canvas.borderColor;
            this.canvas.style.borderRadius = options.canvas.borderRadius + 'px';
        },
        setRenderCallback: function( callback ){
            this.renderCallback = callback;
        },
        globalOptions: {

        },
        options: {
            data:{

            },
            options: {
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                          display: true
                        },
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                },
                title: {
                    text: '',
                    display: true,
                    position: 'top',
                    fontSize: 16,
                    fontColor: '#666'
                },
                legend: {
                    display: true,
                    labels: {
                        fontColor: '#666',
                        fontSize: 12,
                        boxWidth: 40
                    }
                },
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFontSize: 12,
                    titleFontStyle: 'bold',
                    titleFontColor: '#fff',
                    bodyFontSize: 12,
                    bodyFontStyle: 'normal',
                    caretSize: 5,
                    cornerRadius: 6
                },
                maintainAspectRatio: false
            }
        },
        setOptions: function( options ){
            for( var property in options ){
                for (var option in options[property]) {
                    if(option) {
                        this.options['options'][property][option] = options[property][option];
                    }
                }
            }
        },
        setGlobalOptions: function( globalOptions ){
            for( var property in globalOptions ){
                if(globalOptions[property]){
                    this.globalOptions[property] = globalOptions[property];
                }
            }
        },
        setData: function( data ){
            for( var property in data ){
                    this.options['data'][property] = data[property];
            }
        },
        getOptions: function(){
            return this.options;
        },
        render: function(){
            if( this.renderCallback !== null ){
                this.renderCallback( this );
            }
            for( var property in this.globalOptions ){
                Chart.defaults.global[property] = this.globalOptions[property];
            }
            this.chart = new Chart(this.canvas, this.options);
        },
        setType: function( type ){
            switch( type ){
                case 'chartjs_line_chart':
                    Chart.defaults.global.elements.line.fill = false;
                    this.options.type = 'line';
                    break;
                case 'chartjs_area_chart':
                    Chart.defaults.global.elements.line.fill = true;
                    this.options.type = 'line';
                    break;
                case 'chartjs_stacked_area_chart':
                    Chart.defaults.global.elements.line.fill = true;
                    this.options.type = 'line';
                    this.options.options.scales.yAxes[0].stacked = true;
                    break;
                case 'chartjs_column_chart':
                    Chart.defaults.global.elements.line.fill = true;
                    this.options.type = 'bar';
                    break;
                case 'chartjs_radar_chart':
                    Chart.defaults.global.elements.line.fill = true;
                    this.options.type = 'radar';
                    break;
                case 'chartjs_polar_area_chart':
                    this.options.type = 'polarArea';
                    break;
                case 'chartjs_pie_chart':
                    this.options.type = 'pie';
                    break;
                case 'chartjs_doughnut_chart':
                    this.options.type = 'doughnut';
                    break;
                case 'chartjs_bubble_chart':
                    this.options.type = 'bubble';
                    break;
            }
        },
        setColumnIndexes: function( columnIndexes ){
            this.columnIndexes = columnIndexes;
        },
        setConnectedWPDataTable: function( wpDataTable ){
            this.connectedWPDataTable = wpDataTable;
        },
        setGrouping: function( group_chart ){
            this.group_chart = group_chart;
        },
        setChartConfig: function(chartConfig) {
            // Chart
            if (chartConfig.responsive_width == 1) {
                this.container.style.width = null;
            } else {
                this.container.style.width = chartConfig.width + 'px';
                this.options.options.maintainAspectRatio = false;
            }
            chartConfig.height ? this.container.style.height = chartConfig.height + 'px' : null;
            this.canvas.style.backgroundColor = chartConfig.background_color;
            this.canvas.style.border = chartConfig.border_width + 'px solid ' + chartConfig.border_color;
            chartConfig.border_radius ? this.canvas.style.borderRadius = chartConfig.border_radius + 'px' : null;
            chartConfig.font_size ? this.globalOptions.defaultFontSize = chartConfig.font_size : null;
            chartConfig.font_name ? this.globalOptions.defaultFontFamily = chartConfig.font_name : null;
            chartConfig.font_style ? this.globalOptions.defaultFontStyle = chartConfig.font_style : null;
            chartConfig.font_color ? this.globalOptions.defaultFontColor = chartConfig.font_color :  '#666';
            // Series
            if ( this.pieCharts.indexOf(chartConfig.chart_type) == -1 && chartConfig.chart_type != 'chartjs_bubble_chart') {
                var j = 0;
                for (var i in chartConfig.series_data) {
                    this.options.data.datasets[j].label = chartConfig.series_data[i].label;
                    chartConfig.series_data[i].color ? this.options.data.datasets[j].borderColor = chartConfig.series_data[i].color : null;
                    chartConfig.series_data[i].color ? this.options.data.datasets[j].backgroundColor = "rgba(" + hexToRgb(chartConfig.series_data[i].color).r + ", " + hexToRgb(chartConfig.series_data[i].color).g + ", " + hexToRgb(chartConfig.series_data[i].color).b + ", 0.2)" : null;
                    j++;
                }
                if (chartConfig.curve_type == 1) {
                    for (i in this.options.data.datasets) {
                        this.options.data.datasets[i].lineTension = 0.4;
                    }
                } else {
                    for (i in this.options.data.datasets) {
                        this.options.data.datasets[i].lineTension = 0;
                    }
                }
            }
            // Axes
            if (chartConfig.show_grid == 0) {
                this.options.options.scales.xAxes[0].display = false;
                this.options.options.scales.yAxes[0].display = false;
            } else {
                this.options.options.scales.xAxes[0].display = true;
                this.options.options.scales.yAxes[0].display = true;
            }
            chartConfig.horizontal_axis_label ? this.options.options.scales.xAxes[0].scaleLabel.labelString = chartConfig.horizontal_axis_label : null;
            chartConfig.vertical_axis_label ? this.options.options.scales.yAxes[0].scaleLabel.labelString = chartConfig.vertical_axis_label : null;
            chartConfig.vertical_axis_min != '' ? this.options.options.scales.yAxes[0].ticks.min = parseInt(chartConfig.vertical_axis_min) : this.options.options.scales.yAxes[0].ticks.beginAtZero = delete this.options.options.scales.yAxes[0].ticks.min;
            chartConfig.vertical_axis_max != '' ? this.options.options.scales.yAxes[0].ticks.max = parseInt(chartConfig.vertical_axis_max) : delete this.options.options.scales.yAxes[0].ticks.max;
            // Title
            if (chartConfig.show_title == 1) {
                this.options.options.title.display = true;
                this.options.options.title.text = chartConfig.chart_title
            } else {
                this.options.options.title.display = false;
            }
            chartConfig.title_position ? this.options.options.title.position = chartConfig.title_position : null;
            chartConfig.title_font_name ? this.options.options.title.fontFamily = chartConfig.title_font_name : null;
            chartConfig.title_font_style ? this.options.options.title.fontStyle = chartConfig.title_font_style : null;
            chartConfig.title_font_color ? this.options.options.title.fontColor = chartConfig.title_font_color : this.options.options.title.fontColor = '#666';
            // Tooltip
            chartConfig.tooltip_enabled == 1 ? this.options.options.tooltips.enabled = true : this.options.options.tooltips.enabled = false;
            chartConfig.tooltip_background_color ? this.options.options.tooltips.backgroundColor = chartConfig.tooltip_background_color : this.options.options.tooltips.backgroundColor = 'rgba(0,0,0,0.8)';
            chartConfig.tooltip_border_radius ? this.options.options.tooltips.cornerRadius = parseInt(chartConfig.tooltip_border_radius) : null;
            chartConfig.tooltip_shared == 1 ? this.options.options.tooltips.mode = 'label' : this.options.options.tooltips.mode = 'single';
            // Legend
            chartConfig.show_legend == 1 ? this.options.options.legend.display = true : this.options.options.legend.display = false;
            chartConfig.legend_position_cjs ? this.options.options.legend.position = chartConfig.legend_position_cjs : null;
        },
        enableFollowFiltering: function(){
            if( this.connectedWPDataTable == null ){ return; }
            this.numberFormat = JSON.parse( jQuery( '#'+this.connectedWPDataTable.data('described-by') ).val() ).number_format;
            this.connectedWPDataTable.fnSettings().aoDrawCallback.push({
                sName: 'chart_filter_follow',
                fn: function( oSettings ){
                    obj.options.data.labels = [];
                    var labels = [];
                    var serieIndex = 0;
                    var filteredData = obj.connectedWPDataTable._('tr', {"filter": "applied"}).toArray();
                    var colors = [
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
                        '#BFEB54'
                    ];

                    if (obj.options.type === 'bubble') {
                        obj.options.data.datasets = [];
                        for( var j in filteredData ){
                            obj.options.data.labels.push(filteredData[j][obj.columnIndexes[0]]);
                                labels = obj.options.data.labels;
                                    if( obj.numberFormat == 1 ){
                                        seriesDataEntry = {
                                            label: labels[j],
                                            backgroundColor :colors[j],
                                            hoverBackgroundColor: colors[j],
                                            data:[{
                                                x: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1]], '.', ',', true)),
                                                y: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1] + 1], '.', ',', true)),
                                                r: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1] + 2], '.', ',', true))
                                            }]
                                        };
                                    }else{
                                        seriesDataEntry = {
                                            label: labels[j],
                                            backgroundColor :colors[j],
                                            hoverBackgroundColor: colors[j],
                                            data:[{
                                                x: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1]], ',', '.', true)),
                                                y: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1] + 1], ',', '.', true)),
                                                r: parseFloat(wdtUnformatNumber(filteredData[j][obj.columnIndexes[1] + 2], ',', '.', true))
                                            }]
                                        };
                                    }

                            obj.options.data.datasets.push(seriesDataEntry);

                        }

                        if ( obj.group_chart == 1 ){
                            var output_labels = [];
                            var output_values = [];
                            for (var i in labels) {
                                if (typeof output_labels !== 'undefined' && output_labels.length > 0) {
                                    var value_key = 'none';
                                    for(var j in output_labels){
                                        if(value_key === 'none'){
                                            if(output_labels[j] == labels[i]){
                                                value_key = j;
                                            }
                                        }
                                    }
                                    if (value_key === 'none') {
                                        output_labels.push(labels[i]);
                                        output_values.push(obj.options.data.datasets[i]);
                                    } else {
                                        output_values[value_key]["data"][0]["x"] += obj.options.data.datasets[i]["data"][0]["x"];
                                        output_values[value_key]["data"][0]["y"] += obj.options.data.datasets[i]["data"][0]["y"];
                                        output_values[value_key]["data"][0]["r"] += obj.options.data.datasets[i]["data"][0]["r"];
                                    }
                                } else {
                                    output_labels.push(labels[i]);
                                    output_values.push(obj.options.data.datasets[i]);
                                }
                            }

                            obj.options.data.labels = output_labels;
                            obj.options.data.datasets = output_values;
                        }

                    } else {
                        for( var j in obj.columnIndexes ){
                            var seriesDataEntry = [];
                            if( ( obj.columnIndexes.length > 0 ) && ( j == 0 ) ) {
                                for (var i in filteredData) {
                                    obj.options.data.labels.push(filteredData[i][obj.columnIndexes[j]]);
                                }
                                labels = obj.options.data.labels;
                            } else {
                                for( var i in filteredData ){
                                    var entry = filteredData[i][obj.columnIndexes[j]];
                                    if( obj.numberFormat == 1 ){
                                        seriesDataEntry.push( parseFloat( wdtUnformatNumber(entry, '.', ',', true) ) );
                                    }else{
                                        seriesDataEntry.push(  parseFloat( wdtUnformatNumber(entry, ',', '.', true) ) );
                                    }
                                }

                                if ( obj.group_chart == 1 ){
                                    var output_labels = [];
                                    var output_values = [];
                                    for (var i in labels) {
                                        if (typeof output_labels !== 'undefined' && output_labels.length > 0) {
                                            var value_key = 'none';
                                            for(var j in output_labels){
                                                if(value_key === 'none'){
                                                    if(output_labels[j] == labels[i]){
                                                        value_key = j;
                                                    }
                                                }
                                            }
                                            if (value_key === 'none') {
                                                output_labels.push(labels[i]);
                                                output_values.push(seriesDataEntry[i]);
                                            } else {
                                                output_values[value_key] += seriesDataEntry[i];
                                            }
                                        } else {
                                            output_labels.push(labels[i]);
                                            output_values.push(seriesDataEntry[i]);
                                        }
                                    }

                                    obj.options.data.labels = output_labels;
                                    seriesDataEntry = output_values;

                                }

                                obj.options.data.datasets[serieIndex].data = seriesDataEntry;
                                serieIndex++;
                            }
                        }
                    }

                    if( obj.chart !== null ){
                        obj.chart.destroy();
                    }

                    if( obj.renderCallback !== null ){
                        obj.renderCallback( obj );
                    }

                    obj.chart = new Chart(obj.canvas, obj.options);

                }
            });
        }
    };

    return obj;

};
