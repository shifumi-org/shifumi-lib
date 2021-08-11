(function ($) {
    $.fn.columnFilter = function (options) {

        // Array of column Indexes for custom searches (All range filters)
        var customSearchIndexes = [];

        // Default properties
        var properties = {
            sPlaceHolder: "foot",
            sRangeSeparator: "|",
            aoColumns: null,
            sRangeFormat: "From {from} to {to}"
        };

        $.extend(properties, options);

        var oTable = this, columnIndex, sColumnLabel, th, tr, aoFilterCells;
        var serverSide = oTable.fnSettings().oFeatures.bServerSide;
        //Array of the functions that will override sSearch_ parameters
        var afnSearch_ = [];

        return this.each(function () {

            // If "Render advanced filter" is "In the header"
            if (properties.sPlaceHolder === 'head:before') {
                tr = $("tr:first", oTable.fnSettings().nTHead).detach();
                tr.appendTo($(oTable.fnSettings().nTHead));
                aoFilterCells = oTable.fnSettings().aoHeader[0];
            } else {
                aoFilterCells = oTable.fnSettings().aoFooter[0];
            }

            // Go through all table filter cells
            $(aoFilterCells).each(function (index) {

                columnIndex = index;

                var aoColumn = {
                    type: "text",
                    bRegex: false,
                    bSmart: true,
                    iMaxLength: -1,
                    iFilterLength: 0
                };

                sColumnLabel = $($(this)[0].cell).text();

                if (properties.aoColumns !== null) {
                    if (properties.aoColumns.length < columnIndex || properties.aoColumns[columnIndex] === null)
                        return;
                    aoColumn = properties.aoColumns[columnIndex];
                }

                if (typeof aoColumn.sSelector === 'undefined') {
                    th = $($(this)[0].cell);
                } else {
                    th = $(aoColumn.sSelector);
                }

                th.addClass('column-' + aoColumn.origHeader.toString().toLowerCase().replace(/\ /g, '-'));

                if (typeof aoColumn.sRangeFormat !== 'undefined')
                    sRangeFormat = aoColumn.sRangeFormat;
                else
                    sRangeFormat = properties.sRangeFormat;

                if (aoColumn !== null) {
                    switch (aoColumn.type) {
                        case 'null':
                            break;
                        case 'text':
                        case 'number':
                            wdtCreateInput(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide);
                            break;
                        case 'number-range':
                            wdtCreateNumberRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide);
                            break;
                        case 'date-range':
                            wdtCreateDateRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide);
                            break;
                        case 'datetime-range':
                            wdtCreateDateTimeRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide);
                            break;
                        case 'time-range':
                            wdtCreateTimeRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide);
                            break;
                        case 'select':
                            wdtCreateSelectbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide);
                            break;
                        case 'multiselect':
                            wdtCreateMultiSelectbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide);
                            break;
                        case 'checkbox':
                            wdtCreateCheckbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide);
                            break;
                        case 'text':
                        default:
                            wdtCreateInput(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide);
                            break;
                    }
                }
            });

            for (var j = 0; j < customSearchIndexes.length; j++) {
                var fnSearch_ = function () {
                    var id = oTable.attr("id");
                    if ((typeof $("#" + id + "_range_from_" + customSearchIndexes[j]).val() === 'undefined')
                        || (typeof $("#" + id + "_range_to_" + customSearchIndexes[j]).val() === 'undefined')) {
                        if (jQuery('#' + id).closest('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').hasClass('wdt-filter-number-range-slider')) {
                            return jQuery('#' + id).closest('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').find('#wdt-number-range-slider')[0].noUiSlider.get()[0] + properties.sRangeSeparator + jQuery('#' + id).closest('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').find('#wdt-number-range-slider')[0].noUiSlider.get()[1];
                        }
                        if(jQuery('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').hasClass('wdt-filter-number-range-slider')){
                            return jQuery('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').find('#wdt-number-range-slider')[0].noUiSlider.get()[0] + properties.sRangeSeparator + jQuery('.wpdt-c').find('.filter_column[data-index=' + customSearchIndexes[j] + ']').find('#wdt-number-range-slider')[0].noUiSlider.get()[1];
                        }
                        else {
                            return properties.sRangeSeparator;
                        }
                    }
                    return $("#" + id + "_range_from_" + customSearchIndexes[j]).val() + properties.sRangeSeparator + $("#" + id + "_range_to_" + customSearchIndexes[j]).val();
                };
                afnSearch_.push(fnSearch_);
            }

            if (oTable.fnSettings().oFeatures.bServerSide) {

                if (typeof oTable.fnSettings().ajax.data !== 'undefined') {
                    var currentDataMethod = oTable.fnSettings().ajax.data;
                }

                oTable.fnSettings().ajax = {
                    url: oTable.fnSettings().ajax.url,
                    type: 'POST',
                    data: function (d) {
                        if (typeof currentDataMethod !== 'undefined') {
                            currentDataMethod(d);
                        }
                        for (j = 0; j < customSearchIndexes.length; j++) {
                            var index = customSearchIndexes[j];
                            d.columns[index].search.value = afnSearch_[j]();
                        }
                        d.sRangeSeparator = properties.sRangeSeparator;
                    }
                };

            }

            wdtClearFilters();

        });

    };

})(jQuery);

var sRangeFormat = wpdatatables_frontend_strings.from + " {from} " + wpdatatables_frontend_strings.to + " {to}";
var fnOnFiltered = function () {
};

/**
 * Creates "Text" and "Number" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 */
function wdtCreateInput(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide) {
    var bIsNumber = aoColumn.type === 'number';
    var sCSSClass = aoColumn.type === 'number' ? 'number_filter' : 'text_filter';

    sColumnLabel = sColumnLabel.replace(/(^\s*)|(\s*$)/g, "");

    var placeholder = aoColumn.filterLabel ? aoColumn.filterLabel : sColumnLabel;

    var input = jQuery('<input type="' + aoColumn.type + '" class="form-control wdt-filter-control ' + sCSSClass + '" placeholder="' + placeholder + '" />');

    th.html(input);

    if (bIsNumber)
        th.wrapInner('<span class="filter_column wdt-filter-number" data-filter_type="number" data-index="' + columnIndex + '"/>');
    else
        th.wrapInner('<span class="filter_column wdt-filter-text" data-filter_type="text" data-index="' + columnIndex + '"/>');

    input.on('keyup input',_.debounce(function (e) {
        inputSearch(this.value, e.keyCode);
    }, 500)
    )

    function inputSearch(value, keyCode) {
        if (typeof keyCode !== 'undefined' && jQuery.inArray(keyCode, [16, 37, 38, 39, 40]) !== -1) {
            return;
        }

        var tableId = oTable.attr('id');
        var search = '';

        if (aoColumn.exactFiltering) {
            search = serverSide ? value : "^" + value + "$";
            oTable.api().column(columnIndex).search(value ? search : '', true, false);
        } else {
            if (bIsNumber && !serverSide) {
                search = "^";
                for (var i = 0; i < value.length; i++) {
                    search += value[i] + '(\\.|,)?';
                }
            } else {
                search = value;
            }
            oTable.api().column(columnIndex).search(search, bIsNumber, false);
        }

        if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
            oTable.api().draw();
        }

        fnOnFiltered();
    }

    if (aoColumn.defaultValue) {
        var defaultValue = '';
        if (typeof aoColumn.defaultValue === 'object') {
            defaultValue = aoColumn.defaultValue['value'];
        } else if (jQuery.isArray(aoColumn.defaultValue)) {
            defaultValue = aoColumn.defaultValue[0];
        } else {
            defaultValue = aoColumn.defaultValue;
        }
        jQuery(input).val(defaultValue);
        oTable.api().column(columnIndex).search(defaultValue, bIsNumber, false);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(input).trigger('keyup');
            });
        }
    }

}

/**
 * Creates "Number range" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param customSearchIndexes
 */
function wdtCreateNumberRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide) {
    var tableId = oTable.attr('id');
    var fromDefaultValue = '', toDefaultValue = '', defaultValue = aoColumn.defaultValue;
    var tableDescription = JSON.parse(jQuery('#' + oTable.data('described-by')).val());
    var numberFormat = (typeof tableDescription.number_format !== 'undefined') ? parseInt(tableDescription.number_format) : 1;
    var replaceFormat = numberFormat === 1 ? /\./g : /,/g;

    if (defaultValue !== '') {
        fromDefaultValue = defaultValue[0];
        toDefaultValue = defaultValue[1];
    }

    th.html('');

    if (aoColumn.rangeSlider) {
        if( (tableDescription.tableType === 'gravity' && serverSide) || tableDescription.cascadeFiltering === 1 ) {
            var sFromId = oTable.attr("id") + '_range_from_' + columnIndex;
            var from = jQuery('<input type="number" class="form-control wdt-filter-control number-range-filter" id="' + sFromId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.from + '" />');
            th.append(from);

            var sToId = oTable.attr("id") + '_range_to_' + columnIndex;
            var to = jQuery('<input type="number" class="form-control wdt-filter-control number-range-filter" id="' + sToId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.to + '" />');
            th.append(to);

            th.wrapInner('<span class="filter_column wdt-filter-number-range" data-filter_type="number range" data-index="' + columnIndex + '"/>');
            customSearchIndexes.push(columnIndex);

            oTable.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                    if (oTable.attr("id") !== oSettings.sTableId)
                        return true;

                    // Try to handle missing nodes more gracefully
                    if (document.getElementById(sFromId) == null)
                        return true;

                    var iMin = document.getElementById(sFromId).value.replace(replaceFormat, '');
                    var iMax = document.getElementById(sToId).value.replace(replaceFormat, '');
                    var iValue = aData[columnIndex] == "-" ? '0' : aData[columnIndex].replace(replaceFormat, '');

                    if (numberFormat === 1) {
                        iMin = iMin.replace(/,/g, '.');
                        iMax = iMax.replace(/,/g, '.');
                        iValue = iValue.replace(/,/g, '.');
                    }

                    if (iMin !== '') {
                        iMin = iMin * 1;
                    }

                    if (iMax !== '') {
                        iMax = iMax * 1;
                    }

                    iValue = iValue * 1;

                    return (iMin === "" && iMax === "") ||
                        (iMin === "" && iValue <= iMax) ||
                        (iMin <= iValue && "" === iMax) ||
                        (iMin <= iValue && iValue <= iMax);


                }
            );

            jQuery('#' + sFromId + ', #' + sToId, th).keyup(function () {
                numberRangeSearch();
            });

            if (fromDefaultValue) {
                jQuery(from).val(fromDefaultValue);
                if (!serverSide) {
                    jQuery(document).ready(function () {
                        jQuery(from).keyup();
                    });
                }
            }

            if (toDefaultValue) {
                jQuery(to).val(toDefaultValue);
                if (!serverSide) {
                    jQuery(document).ready(function () {
                        jQuery(to).keyup();
                    });
                }
            }

            function numberRangeSearch() {
                var iMin = document.getElementById(sFromId).value * 1;
                var iMax = document.getElementById(sToId).value * 1;
                if (iMin != 0 && iMax != 0 && iMin > iMax)
                    return;

                if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
                    oTable.api().draw();
                }

                fnOnFiltered();
            }

        } else {
            th.append('<div id="wdt-number-range-slider"></div>');
            th.wrapInner('<span class="filter_column wdt-filter-number-range wdt-filter-number-range-slider" data-filter_type="number range" data-index="' + columnIndex + '"/>');

            var slider = th.find('#wdt-number-range-slider')[0];
            customSearchIndexes.push(columnIndex);

            var numberArray = oTable.api().column(columnIndex).data();
            var formattedNumber = numberArray.map(function (numberArray) {
                if (numberFormat === 1) {
                    return parseFloat(numberArray.replace(/\./g, '').replace(',', '.'));
                }
                return parseFloat(numberArray.replace(/,/g, ''))
            })
            var minValue = serverSide === false ? Math.min.apply(Math, formattedNumber) : aoColumn.minValue
            var maxValue = serverSide === false ? Math.max.apply(Math, formattedNumber) : aoColumn.maxValue

            noUiSlider.create(slider, {
                start: [fromDefaultValue ? fromDefaultValue : minValue, toDefaultValue ? toDefaultValue : maxValue],
                tooltips: true,
                connect: true,
                behaviour: 'drag',
                range: {
                    'min': minValue,
                    'max': maxValue
                },
                format: wNumb({
                    decimals: aoColumn.columnType === 'int' ? 0 : aoColumn.numberOfDecimalPlaces,
                    thousand: numberFormat === 1 ? aoColumn.columnType === 'float' ? aoColumn.thousandsSeparator : '.' : ',',
                    mark: numberFormat === 1 ? ',' : '.'
                })
            });

            slider.noUiSlider.on('end', function () {
                    slider.value = slider.noUiSlider.get();
                    numberRangeSliderSearch();
                }
            );

            slider.noUiSlider.on('set', function () {
                    slider.value = slider.noUiSlider.get();
                    numberRangeSliderSearch();
                }
            );

            function numberRangeSliderSearch() {
                if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
                    oTable.api().draw();
                }

                oTable.dataTableExt.afnFiltering.push(
                    function (oSettings, aData, iDataIndex) {
                        if (oTable.attr("id") !== oSettings.sTableId)
                            return true;

                        var iMin = numberFormat === 1 ? parseFloat(slider.value[0].replace(/\./g, '').replace(',', '.')) : parseFloat(slider.value[0].replace(/,/g, ''))
                        var iMax = numberFormat === 1 ? parseFloat(slider.value[1].replace(/\./g, '').replace(',', '.')) : parseFloat(slider.value[1].replace(/,/g, ''))
                        var iValue = aData[columnIndex] == "-" ? '0' : aData[columnIndex];

                        iValue = numberFormat === 1 ? parseFloat(iValue.replace(/\./g, '').replace(',', '.')) : parseFloat(aData[columnIndex].replace(/,/g, ''));

                        return iMin <= iValue && iValue <= iMax;
                    }
                );

                fnOnFiltered();
            }

            if (fromDefaultValue) {
                if (!serverSide) {
                    slider.value = slider.noUiSlider.get();
                    numberRangeSliderSearch();
                }
            }

            if (toDefaultValue) {
                if (!serverSide) {
                    slider.value = slider.noUiSlider.get();
                    numberRangeSliderSearch();
                }
            }

        }
    } else {
        var sFromId = oTable.attr("id") + '_range_from_' + columnIndex;
        var from = jQuery('<input type="number" class="form-control wdt-filter-control number-range-filter" id="' + sFromId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.from + '" />');
        th.append(from);

        var sToId = oTable.attr("id") + '_range_to_' + columnIndex;
        var to = jQuery('<input type="number" class="form-control wdt-filter-control number-range-filter" id="' + sToId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.to + '" />');
        th.append(to);

        th.wrapInner('<span class="filter_column wdt-filter-number-range" data-filter_type="number range" data-index="' + columnIndex + '"/>');
        customSearchIndexes.push(columnIndex);

        oTable.dataTableExt.afnFiltering.push(
            function (oSettings, aData, iDataIndex) {
                if (oTable.attr("id") !== oSettings.sTableId)
                    return true;

                // Try to handle missing nodes more gracefully
                if (document.getElementById(sFromId) == null)
                    return true;

                var iMin = document.getElementById(sFromId).value.replace(replaceFormat, '');
                var iMax = document.getElementById(sToId).value.replace(replaceFormat, '');
                var iValue = aData[columnIndex] == "-" ? '0' : aData[columnIndex].replace(replaceFormat, '');

                if (numberFormat === 1) {
                    iMin = iMin.replace(/,/g, '.');
                    iMax = iMax.replace(/,/g, '.');
                    iValue = iValue.replace(/,/g, '.');
                }

                if (iMin !== '') {
                    iMin = iMin * 1;
                }

                if (iMax !== '') {
                    iMax = iMax * 1;
                }

                iValue = iValue * 1;

                return (iMin === "" && iMax === "") ||
                    (iMin === "" && iValue <= iMax) ||
                    (iMin <= iValue && "" === iMax) ||
                    (iMin <= iValue && iValue <= iMax);


            }
        );

        jQuery('#' + sFromId + ', #' + sToId, th).keyup(function () {
            numberRangeSearch();
        });

        if (fromDefaultValue) {
            jQuery(from).val(fromDefaultValue);
            if (!serverSide) {
                jQuery(document).ready(function () {
                    jQuery(from).keyup();
                });
            }
        }

        if (toDefaultValue) {
            jQuery(to).val(toDefaultValue);
            if (!serverSide) {
                jQuery(document).ready(function () {
                    jQuery(to).keyup();
                });
            }
        }

        function numberRangeSearch() {
            var iMin = document.getElementById(sFromId).value * 1;
            var iMax = document.getElementById(sToId).value * 1;
            if (iMin != 0 && iMax != 0 && iMin > iMax)
                return;

            if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
                oTable.api().draw();
            }

            fnOnFiltered();
        }
    }

}

/**
 * Creates "Date range" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param customSearchIndexes
 */
function wdtCreateDateRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide) {
    var tableId = oTable.attr('id');
    var fromDefaultValue = '', toDefaultValue = '', defaultValue = aoColumn.defaultValue;
    var dateFormat = getMomentWdtDateFormat();

    if (defaultValue !== '') {
        fromDefaultValue = defaultValue[0];
        toDefaultValue = defaultValue[1];
    }

    th.html('');
    var sFromId = oTable.attr("id") + '_range_from_' + columnIndex;
    var from = jQuery('<input type="text" class="form-control wdt-filter-control date-range-filter wdt-datepicker" id="' + sFromId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.from + '" />');

    var sToId = oTable.attr("id") + '_range_to_' + columnIndex;
    var to = jQuery('<input type="text" class="form-control wdt-filter-control date-range-filter wdt-datepicker" id="' + sToId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.to + '" />');

    th.append(from).append(to);

    th.wrapInner('<span class="filter_column wdt-filter-date-range" data-filter_type="date range" data-index="' + columnIndex + '"/>');
    customSearchIndexes.push(columnIndex);

    oTable.dataTableExt.afnFiltering.push(
        function (oSettings, aData, iDataIndex) {
            if (oTable.attr("id") != oSettings.sTableId)
                return true;

            var dStartDate = moment(from.val(), dateFormat).toDate();
            var dEndDate = moment(to.val(), dateFormat).toDate();

            if (isNaN(dStartDate.getTime()) && isNaN(dEndDate.getTime())) {
                return true;
            }

            var dCellDate = null;

            try {
                if (aData[columnIndex] === null || aData[columnIndex] === "")
                    return false;
                dCellDate = moment(aData[columnIndex], dateFormat).toDate();
            } catch (ex) {
                return false;

            }

            if (isNaN(dCellDate.getTime()))
                return false;

            return (isNaN(dStartDate.getTime()) && dCellDate <= dEndDate) ||
                (dStartDate <= dCellDate && isNaN(dEndDate.getTime())) ||
                (dStartDate <= dCellDate && dCellDate <= dEndDate);

        }
    );

    jQuery('#' + sFromId + ', #' + sToId, th).on('blur', function (e) {

        if ((typeof wpDataTables[tableId].drawTable === 'undefined') || wpDataTables[tableId].drawTable === true) {
            oTable.api().draw();
        }

        fnOnFiltered();
    });

    if (fromDefaultValue) {
        jQuery(from).val(fromDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(from).trigger('blur');
            });
        }
    }

    if (toDefaultValue) {
        jQuery(to).val(toDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(to).trigger('blur');
            });
        }
    }
}

/**
 * Creates "DateTime range" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param customSearchIndexes
 */
function wdtCreateDateTimeRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide) {
    var tableId = oTable.attr('id');
    var fromDefaultValue = '', toDefaultValue = '', defaultValue = aoColumn.defaultValue;
    var dateFormat = getMomentWdtDateFormat();
    var timeFormat = getMomentWdtTimeFormat();

    if (defaultValue !== '') {
        fromDefaultValue = defaultValue[0];
        toDefaultValue = defaultValue[1];
    }

    th.html('');

    var sFromId = oTable.attr("id") + '_range_from_' + columnIndex;
    var fromHTML = '<input type="text" class="form-control wdt-filter-control date-time-range-filter wdt-datetimepicker" id="' + sFromId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.from + '" />';
    var from = jQuery(fromHTML);

    var sToId = oTable.attr("id") + '_range_to_' + columnIndex;
    var toHTML = '<input type="text" class="form-control wdt-filter-control date-time-range-filter wdt-datetimepicker" id="' + sToId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.to + '" />';
    var to = jQuery(toHTML);

    th.append(from).append(to);


    th.wrapInner('<span class="filter_column wdt-filter-date-time-range" data-filter_type="datetime range" data-index="' + columnIndex + '"/>');
    customSearchIndexes.push(columnIndex);

    oTable.dataTableExt.afnFiltering.push(
        function (oSettings, aData, iDataIndex) {
            if (oTable.attr("id") != oSettings.sTableId)
                return true;

            var dStartDate = moment(from.val(), dateFormat + ' ' + timeFormat).toDate();
            var dEndDate = moment(to.val(), dateFormat + ' ' + timeFormat).toDate();

            if (isNaN(dStartDate.getTime()) && isNaN(dEndDate.getTime())) {
                return true;
            }

            var dCellDate = null;

            try {
                if (aData[columnIndex] === null || aData[columnIndex] === '')
                    return false;
                dCellDate = moment(aData[columnIndex], dateFormat + ' ' + timeFormat).toDate();
            } catch (ex) {
                return false;
            }

            if (isNaN(dCellDate.getTime()))
                return false;

            return (isNaN(dStartDate.getTime()) && dCellDate <= dEndDate) ||
                (dStartDate <= dCellDate && isNaN(dEndDate.getTime())) ||
                (dStartDate <= dCellDate && dCellDate <= dEndDate);
        }
    );

    jQuery('#' + sFromId + ', #' + sToId, th).on('blur', function (e) {

        if (((typeof wpDataTables[tableId].drawTable === 'undefined') || wpDataTables[tableId].drawTable === true) && e.oldDate !== null) {
            oTable.api().draw();
        }

        fnOnFiltered();
    });

    if (fromDefaultValue) {
        jQuery(from).val(fromDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(to).trigger('blur');
            });
        }
    }

    if (toDefaultValue) {
        jQuery(to).val(toDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(to).trigger('blur');
            });
        }
    }
}

/**
 * Creates "Time range" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param customSearchIndexes
 */
function wdtCreateTimeRangeInput(oTable, aoColumn, columnIndex, sColumnLabel, th, customSearchIndexes, serverSide) {
    var tableId = oTable.attr('id');
    var fromDefaultValue = '', toDefaultValue = '', defaultValue = aoColumn.defaultValue;
    var timeFormat = getMomentWdtTimeFormat();

    if (defaultValue !== '') {
        fromDefaultValue = defaultValue[0];
        toDefaultValue = defaultValue[1];
    }

    th.html('');

    var sFromId = oTable.attr("id") + '_range_from_' + columnIndex;
    var fromHTML = '<input type="text" class="form-control wdt-filter-control time-range-filter wdt-timepicker" id="' + sFromId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.from + '" />';
    var from = jQuery(fromHTML);

    var sToId = oTable.attr("id") + '_range_to_' + columnIndex;
    var toHTML = '<input type="text" class="form-control wdt-filter-control time-range-filter wdt-timepicker" id="' + sToId + '" rel="' + columnIndex + '" placeholder="' + wpdatatables_frontend_strings.to + '" />';
    var to = jQuery(toHTML);

    th.append(from).append(to);

    th.wrapInner('<span class="filter_column filter_date_range" data-filter_type="time range" data-index="' + columnIndex + '"/>');
    customSearchIndexes.push(columnIndex);

    oTable.dataTableExt.afnFiltering.push(
        function (oSettings, aData, iDataIndex) {
            if (oTable.attr("id") != oSettings.sTableId)
                return true;

            var dStartTime = moment(from.val(), timeFormat).toDate();
            var dEndTime = moment(to.val(), timeFormat).toDate();

            if (isNaN(dStartTime.getTime()) && isNaN(dEndTime.getTime())) {
                return true;
            }

            var dCellTime = null;

            try {
                if (aData[columnIndex] === null || aData[columnIndex] === '')
                    return false;
                dCellTime = moment(aData[columnIndex], timeFormat).toDate();
            } catch (ex) {
                return false;
            }

            if (isNaN(dCellTime.getTime()))
                return false;

            return (isNaN(dStartTime.getTime()) && dCellTime <= dEndTime) ||
                (dStartTime <= dCellTime && isNaN(dEndTime.getTime())) ||
                (dStartTime <= dCellTime && dCellTime <= dEndTime);
        }
    );

    jQuery('#' + sFromId + ', #' + sToId, th).on('blur', function (e) {

        if (((typeof wpDataTables[tableId].drawTable === 'undefined') || wpDataTables[tableId].drawTable === true) && e.oldDate !== null) {
            oTable.api().draw();
        }

        fnOnFiltered();
    });

    if (fromDefaultValue) {
        jQuery(from).val(fromDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(to).trigger('blur');
            });
        }
    }

    if (toDefaultValue) {
        jQuery(to).val(toDefaultValue);
        if (!serverSide) {
            jQuery(document).ready(function () {
                jQuery(to).trigger('blur');
            });
        }
    }
}

/**
 * Creates "Selectbox" and "Multiselectbox" filters
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param serverSide
 */
function wdtCreateSelectbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide) {
    var tableId = oTable.attr('id'), selected;

    // When server side is disabled, load the values with datatables api
    if (aoColumn.values === null)
        aoColumn.values = getColumnDistinctValues(tableId, columnIndex, false);

    // If "Allow empty value" is enabled, add new value for empty in the values array
    if (aoColumn.possibleValuesAddEmpty === true && !serverSide) {
        aoColumn.values.unshift('possibleValuesAddEmpty');
    }

    // Get the default value if is set
    if (aoColumn.defaultValue !== '') {
        if (jQuery.isArray(aoColumn.defaultValue)) {
            aoColumn.defaultValue = aoColumn.defaultValue[0];
        }
    }

    // Label of the selectbox if "Filter label" option is set
    var selectTitle = aoColumn.filterLabel ? aoColumn.filterLabel : wpdatatables_frontend_strings.nothingSelected;

    // Create selectbox HTML with live search
    var select = '<select class="wdt-select-filter wdt-filter-control selectpicker" title="' + selectTitle + '" data-index="' + columnIndex + '" data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search + '">';

    // Create selectbox based on "Number of possible values to load" option
    if (aoColumn.possibleValuesAjax !== -1) {

        // If default value is set, append it to selectbox HTML
        if (typeof aoColumn.defaultValue === 'object') {
            select += '<option selected value="' + aoColumn.defaultValue['value'] + '">' + aoColumn.defaultValue['text'] + '</option>';
            oTable.api().column(columnIndex).search(aoColumn.defaultValue['value']);
        } else {
            select += '<option selected value="' + aoColumn.defaultValue + '">' + aoColumn.defaultValue + '</option>';
            oTable.api().column(columnIndex).search(aoColumn.defaultValue);
        }

    } else {
        // Add blank option to selectbox
        select += '<option value="">' + ' ' + '</option>';

        // Length of the possible values
        var iLen = aoColumn.values ? aoColumn.values.length : 0;

        // Create option for each value from possible values
        for (var j = 0; j < iLen; j++) {
            selected = '';

            // Add selected attribute if option is predefined value
            if (aoColumn.defaultValue !== '') {
                if (typeof aoColumn.defaultValue === 'object') {

                    if (aoColumn.values[j].value == aoColumn.defaultValue.value) {
                        selected = 'selected="selected" ';
                    }
                } else {
                    if (aoColumn.values[j].value == aoColumn.defaultValue) {
                        selected = 'selected="selected" ';
                    }
                }
            }
            select += '<option ' + selected + 'value="' + encodeURI(aoColumn.values[j].value) + '">' + aoColumn.values[j].label + '</option>';
            if (selected) {
                oTable.api().column(columnIndex).search(aoColumn.values[j].value);
            }

        }
    }

    select = jQuery(select + '</select>');
    th.html(select);
    th.wrapInner('<span class="filter_column filter_select" data-filter_type="selectbox" data-index="' + columnIndex + '"/>');

    // Add event to perform search on selectbox change
    select.on('change.selectChange', function () {
        selectboxSearch.call(jQuery(this));
    });

    // Create selectbox based on "Number of possible values to load" option
    if (aoColumn.possibleValuesAjax !== -1) {

        // Load possible values on modal open
        select.on('show.bs.select', function (e) {
            select.closest('.filter_column').find('.bs-searchbox .form-control').val('').trigger('keyup');
        });

        // Add AJAX to selectbox
        select.selectpicker('refresh')
            .ajaxSelectPicker({
                ajax: {
                    url: wdt_ajax_object.ajaxurl,
                    method: 'POST',
                    data: {
                        wdtNonce: jQuery('#wdtNonce').val(),
                        action: 'wpdatatables_get_column_possible_values',
                        tableId: oTable.data('wpdatatable_id'),
                        originalHeader: aoColumn.origHeader
                    }
                },
                cache: false,
                preprocessData: function (data) {
                    if (aoColumn.possibleValuesAddEmpty === true) {
                        data.unshift({value: 'possibleValuesAddEmpty', text: ' '});
                    }
                    data.unshift({value: ''});
                    return data
                },
                preserveSelected: true,
                emptyRequest: true,
                preserveSelectedPosition: 'before',
                locale: {
                    emptyTitle: wpdatatables_frontend_strings.nothingSelected,
                    statusSearching: wpdatatables_frontend_strings.sLoadingRecords,
                    currentlySelected: wpdatatables_frontend_strings.currentlySelected,
                    errorText: wpdatatables_frontend_strings.errorText,
                    searchPlaceholder: wpdatatables_frontend_strings.search,
                    statusInitialized: wpdatatables_frontend_strings.statusInitialized,
                    statusNoResults: wpdatatables_frontend_strings.statusNoResults,
                    statusTooShort: wpdatatables_frontend_strings.statusTooShort
                }
            });

        // Filter the table if default value is set
        if (aoColumn.defaultValue && !serverSide) {
            // Workaround for AJAX selectbox to be able to have predefined values
            select.trigger('change').data('AjaxBootstrapSelect').list.cache = {};
        }
    } else {
        select.selectpicker('refresh');
        // Hide search in selectbox if possibleValuesAjax is All
        jQuery(th).find('.bs-searchbox').hide();

        // Filter the table if default value is set
        if (aoColumn.defaultValue && !serverSide) {
            oTable.fnFilter(aoColumn.defaultValue, columnIndex);
        }
    }

    function selectboxSearch() {
        if (jQuery(this).val() !== null) {
            var search = '';
            if (jQuery(this).val() === 'possibleValuesAddEmpty' && !serverSide) {
                oTable.api().column(columnIndex).search('^$', true, false);
            } else {
                if (aoColumn.exactFiltering) {
                    search = serverSide ? decodeURIComponent(jQuery(this).val()) : '^' + decodeURIComponent(jQuery(this).val()) + '$';
                    oTable.api().column(columnIndex).search(jQuery(this).val() ? search : '', true, false);
                } else {
                    oTable.api().column(columnIndex).search(decodeURIComponent(jQuery(this).val()), true, false);
                }
            }

            if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
                oTable.api().draw();
            }

            fnOnFiltered();
        }
    }
}

/**
 * Creates "Multiselectbox" filter
 * @param oTable
 * @param aoColumn
 * @param columnIndex
 * @param sColumnLabel
 * @param th
 * @param serverSide
 */
function wdtCreateMultiSelectbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide) {
    var tableId = oTable.attr('id'), selected;

    // When server side is disabled, load the values with datatables api
    if (aoColumn.values === null)
        aoColumn.values = getColumnDistinctValues(tableId, columnIndex, false);

    if (!jQuery.isArray(aoColumn.defaultValue)) {
        aoColumn.defaultValue = [aoColumn.defaultValue];
    }

    // Label of the selectbox if "Filter label" option is set

    var selectTitle = aoColumn.filterLabel ? aoColumn.filterLabel : wpdatatables_frontend_strings.nothingSelected;;

    // Create selectbox HTML with live search
    var select = '<select class="wdt-multiselect-filter wdt-filter-control selectpicker" title="' + selectTitle + '" data-index="' + columnIndex + '" multiple data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search + '">';

    // Create selectbox based on "Number of possible values to load" option
    if (aoColumn.possibleValuesAjax !== -1) {
        // If default value is set, append it to selectbox HTML
        if (aoColumn.defaultValue[0]) {
            var search = '';
            for (i = 0; i < aoColumn.defaultValue.length; i++) {
                if (typeof aoColumn.defaultValue[i] === 'object') {
                    select += '<option selected value="' + aoColumn.defaultValue[i].value + '">' + aoColumn.defaultValue[i].text + '</option>';
                    search += buildSearchStringForMultiFilters(aoColumn.defaultValue[i].value, aoColumn.exactFiltering);
                    oTable.api().column(columnIndex).search(search.substring(0, search.length - 1));
                } else {
                    select += '<option selected value="' + aoColumn.defaultValue[i] + '">' + aoColumn.defaultValue[i] + '</option>';
                    search += buildSearchStringForMultiFilters(aoColumn.defaultValue[i], aoColumn.exactFiltering);
                    oTable.api().column(columnIndex).search(search.substring(0, search.length - 1));
                }
            }
        }
    } else {
        // Length of the possible values
        var iLen = aoColumn.values ? aoColumn.values.length : 0;

        var search = '';

        // Create option for each value from possible values
        for (var j = 0; j < iLen; j++) {
            if (typeof aoColumn.defaultValue[0] === 'object') {
                jQuery.each(aoColumn.defaultValue, function (index, value) {
                    selected = aoColumn.values[j].value.toString() == value.value ? 'selected="selected" ' : '';
                    if (selected !== '')
                        return false;
                });
            } else {
                selected = jQuery.inArray(aoColumn.values[j].value.toString(), aoColumn.defaultValue) !== -1 ? selected = 'selected="selected" ' : '';
            }
            select += '<option ' + selected + 'value="' + encodeURI(aoColumn.values[j].value) + '">' + aoColumn.values[j].label + '</option>';
            if (selected) {
                search += buildSearchStringForMultiFilters(aoColumn.values[j].value, aoColumn.exactFiltering);
                oTable.api().column(columnIndex).search(search.substring(0, search.length - 1));
            }
        }
    }

    select = jQuery(select + '</select>');
    th.html(select);
    th.wrapInner('<span class="filter_column filter_select" data-filter_type="multiselectbox" data-index="' + columnIndex + '" />');

    // Add event to perform search on selectbox change
    select.change(function (e) {
        multiSelectboxSearch.call(jQuery(this));
    });

    // Create selectbox based on "Number of possible values to load" option
    if (aoColumn.possibleValuesAjax !== -1) {

        // Load possible values on modal open
        select.on('show.bs.select', function (e) {
            select.closest('.filter_column').find('.bs-searchbox .form-control').val('').trigger('keyup');
        });

        // Add AJAX to selectbox
        select.selectpicker('refresh').ajaxSelectPicker({
            ajax: {
                url: wdt_ajax_object.ajaxurl,
                method: 'POST',
                data: {
                    wdtNonce: jQuery('#wdtNonce').val(),
                    action: 'wpdatatables_get_column_possible_values',
                    tableId: oTable.data('wpdatatable_id'),
                    originalHeader: aoColumn.origHeader
                }
            },
            cache: false,
            preprocessData: function (data) {
                return data
            },
            preserveSelected: true,
            emptyRequest: true,
            preserveSelectedPosition: 'before',
            locale: {
                emptyTitle: wpdatatables_frontend_strings.nothingSelected,
                statusSearching: wpdatatables_frontend_strings.sLoadingRecords,
                currentlySelected: wpdatatables_frontend_strings.currentlySelected,
                errorText: wpdatatables_frontend_strings.errorText,
                searchPlaceholder: wpdatatables_frontend_strings.search,
                statusInitialized: wpdatatables_frontend_strings.statusInitialized,
                statusNoResults: wpdatatables_frontend_strings.statusNoResults,
                statusTooShort: wpdatatables_frontend_strings.statusTooShort
            }
        });

        // Filter the table if default value is set
        if (aoColumn.defaultValue[0] && !serverSide) {
            // Workaround for AJAX selectbox to be able to have predefined values
            select.trigger('change').data('AjaxBootstrapSelect').list.cache = {};
        }

    } else {
        select.selectpicker('refresh');
        // Hide search in multi-selectbox if possibleValuesAjax is All
        jQuery(th).find('.bs-searchbox').hide();

        // Filter the table if default value is set
        if (aoColumn.defaultValue[0] && !serverSide) {
            var search = '';
            for (var i = 0; i < aoColumn.defaultValue.length; i++) {
                search += buildSearchStringForMultiFilters(aoColumn.defaultValue[i], aoColumn.exactFiltering);
            }
            oTable.fnFilter(search.substring(0, search.length - 1), columnIndex, true, false);
            fnOnFiltered();
        }
    }

    function multiSelectboxSearch() {
        // Not possible because when you uncheck all predefined values it will not reload the table
        // if (jQuery(this).val() !== null) {
        var tableDescription = JSON.parse(jQuery('#' + oTable.data('described-by')).val());
        var columnType = tableDescription.dataTableParams.columnDefs[columnIndex].wdtType;
        var search = '', selectedOptions;
        selectedOptions = jQuery(this).selectpicker('val');

        jQuery.each(selectedOptions, function (index, value) {
            if (columnType === 'email' && serverSide === false) {
                var startIndex = value.indexOf('mailto:') + 7;
                var endIndex = value.lastIndexOf("%22");
                value = value.substr(startIndex, endIndex - startIndex)
            }
            search += buildSearchStringForMultiFilters(value, aoColumn.exactFiltering);
        });
        oTable.api().column(columnIndex).search(search.substring(0, search.length - 1), true, false);

        if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
            oTable.api().draw();
        }

        fnOnFiltered();

    }

}

/**
 * Creates "Checkbox" filter
 * @param oTable
 * @param aoColumn - filter settings that will be applied on the column
 * @param columnIndex - column index
 * @param sColumnLabel
 * @param th
 */
function wdtCreateCheckbox(oTable, aoColumn, columnIndex, sColumnLabel, th, serverSide) {
    var tableId = oTable.attr('id');

    if (!jQuery.isArray(aoColumn.defaultValue)) {
        aoColumn.defaultValue = [aoColumn.defaultValue];
    }

    if (aoColumn.values === null)
        aoColumn.values = getColumnDistinctValues(tableId, columnIndex, false);

    var r = '', j, iLen = aoColumn.values ? aoColumn.values.length : 0, dialogRender = true;

    if (typeof aoColumn.sSelector !== 'undefined') {
        dialogRender = aoColumn.checkboxesInModal;
    }

    var labelBtn = aoColumn.filterLabel ? aoColumn.filterLabel : sColumnLabel;
    var checkboxesDivId = oTable.attr('id') + '-checkbox-' + columnIndex;

    if (dialogRender) {
        var buttonId = "checkbox-button-" + checkboxesDivId;
        r += '<button id="' + buttonId + '" class="wdt-checkbox-filter btn" > ' + labelBtn + '</button>'; // Filter button which opens the dialog
    }

    r += '<div id="' + checkboxesDivId + '">';

    var search = '';

    for (j = 0; j < iLen; j++) {
        if (aoColumn.values[j] !== null) {
            var value = typeof aoColumn.values[j] !== 'object' ? aoColumn.values[j] : aoColumn.values[j].value;
            var label = typeof aoColumn.values[j] !== 'object' ? aoColumn.values[j] : aoColumn.values[j].label;
            if (jQuery.isArray(aoColumn.defaultValue)) {
                jQuery.each(aoColumn.defaultValue, function (index, value) {
                    if (aoColumn.possibleValuesType === "foreignkey") {
                        checked = aoColumn.values[j].value.toString() == value.value ? 'checked="checked" ' : '';
                    } else {
                        checked = aoColumn.values[j].value.toString() == value ? 'checked="checked" ' : '';
                    }

                    if (checked !== '')
                        return false;
                });
            } else {
                var checked = jQuery.inArray(value.toString(), aoColumn.defaultValue) !== -1 ? 'checked="checked" ' : '';
            }

            r += '<div class="wdt_checkbox_option checkbox">' +
                '<label>' +
                '<input type="checkbox" class="wdt-checkbox-filter wdt-filter-control" value="' + encodeURI(value) + '" ' + checked + '>' +
                 '<span class="wdt-checkbox-label">' + label + '</span>' +
                '</label>' +
                '</div>';
        }
        if (checked) {
            search += buildSearchStringForMultiFilters(encodeURI(value), aoColumn.exactFiltering);
            oTable.api().column(columnIndex).search(search.substring(0, search.length - 1));
        }
    }

    jQuery(th).off('change.checkboxChange').on('change.checkboxChange', '#' + checkboxesDivId + ' input.wdt-checkbox-filter', function () {
        checkboxSearch.call(jQuery(this), columnIndex, checkboxesDivId);
    });

    th.html(r);
    th.wrapInner('<span class="filter_column filter_checkbox" data-filter_type="checkbox" data-index="' + columnIndex + '" />');

    if (aoColumn.defaultValue[0] && !serverSide) {
        var search = '';
        for (var i = 0; i < aoColumn.defaultValue.length; i++) {
            search += buildSearchStringForMultiFilters(aoColumn.defaultValue[i], aoColumn.exactFiltering);
        }
        oTable.fnFilter(search.substring(0, search.length - 1), columnIndex, true, false);
        fnOnFiltered();
    }

    if (dialogRender) {
        var dlg = jQuery('#' + checkboxesDivId).wrap('<div class="wdt-checkbox-modal-wrap ' + checkboxesDivId + '" />').hide();
        var $modal = jQuery('#wdt-frontend-modal');
        var tableDesc =  JSON.parse(jQuery('#' + oTable.data('described-by')).val());

        $modal.on('click', 'button.close', function (e) {
            $modal.fadeOut(300, function () {
                jQuery(this).find('#' + checkboxesDivId).remove();
                $modal.removeClass('wdt-skin-' + tableDesc.tableSkin);
            });
        });

        $modal.on('keydown', function (e) {
            if (e.keyCode === 27) {
                $modal.fadeOut(300, function () {
                    jQuery(this).find('#' + checkboxesDivId).remove();
                    $modal.removeClass('wdt-skin-' + tableDesc.tableSkin);
                });
            }
        });

        jQuery('#' + buttonId).on('click', function (e) {
            e.preventDefault();

            jQuery('#wdt-frontend-modal .modal-title').html(labelBtn);
            jQuery('#wdt-frontend-modal .modal-body').append(dlg.show());
            jQuery('#wdt-frontend-modal .modal-footer').html('<button class="btn btn-danger btn-icon-text" id="wdt-checkbox-filter-reset" href="#">Reset</button><button class="btn btn-success btn-icon-text" id="wdt-checkbox-filter-close" href="#"><i class="wpdt-icon-check-full"></i>OK</button>');

            jQuery('#wdt-frontend-modal input.wdt-checkbox-filter').off('change').on('change', function () {
                checkboxSearch.call(jQuery(this), columnIndex, checkboxesDivId);
            });

            if (typeof wpDataTables[tableId].onRenderCheckboxFilterModal !== 'undefined') {
                for (var i in wpDataTables[tableId].onRenderCheckboxFilterModal) {
                    wpDataTables[tableId].onRenderCheckboxFilterModal[i]($modal, columnIndex);
                }
            }
            $modal.attr('data-current-checkbox-dialog', dlg.attr('id'));
            $modal.addClass('wdt-skin-' + tableDesc.tableSkin);
            $modal.modal('show');
        });

        $modal.on('shown.bs.modal', function () {
            jQuery(this).off('click', '#wdt-checkbox-filter-close').on('click', '#wdt-checkbox-filter-close', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $modal.modal('hide');
                if (jQuery('#' + $modal.attr('data-current-checkbox-dialog')).length) {
                    jQuery('.wdt-checkbox-modal-wrap.' + $modal.attr('data-current-checkbox-dialog')).html(jQuery('#' + $modal.attr('data-current-checkbox-dialog'))).hide();
                }
                $modal.find('.modal-body').html('');
            });

            jQuery(this).off('click', '#wdt-checkbox-filter-reset').on('click.resetCheckboxFilter', '#wdt-checkbox-filter-reset', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (jQuery('#' + $modal.attr('data-current-checkbox-dialog')).length) {
                    jQuery('#' + $modal.attr('data-current-checkbox-dialog')).find(jQuery('input.wdt-checkbox-filter:checkbox:checked')).each(function () {
                        jQuery(this).prop('checked', false).change();
                    });
                }
                oTable.api().column(columnIndex).search('');

                fnOnFiltered();
            });
        });
    }

    function checkboxSearch(columnIndex, checkboxesDivId) {
        var tableDescription = JSON.parse(jQuery('#' + oTable.data('described-by')).val());
        var columnType = tableDescription.dataTableParams.columnDefs[columnIndex].wdtType;
        var search = '', checkedInputs;
        checkedInputs = jQuery(this).closest('#' + checkboxesDivId).find('input:checkbox:checked');

        jQuery.each(checkedInputs, function () {
            if (columnType === 'email' && serverSide === false) {
                var startIndex = jQuery(this).val().indexOf('mailto:') + 7;
                var endIndex = jQuery(this).val().lastIndexOf("%22");
                var value = jQuery(this).val().substr(startIndex, endIndex - startIndex)
            } else {
                value = jQuery(this).val()
            }
            search += buildSearchStringForMultiFilters(value, aoColumn.exactFiltering);
        });
        oTable.api().column(columnIndex).search(search.substring(0, search.length - 1), true, false);

        if (typeof wpDataTables[tableId].drawTable === 'undefined' || wpDataTables[tableId].drawTable === true) {
            oTable.api().draw();
        }

        fnOnFiltered();
    }

}

/**
 * Function that retrieves column distinct data for non-server-side wpDataTables
 * @param tableId - ID of the table (table_1, table_2...)
 * @param columnIndex - Index of the column
 * @param applySearch - Return values only from the filtered rows
 */
function getColumnDistinctValues(tableId, columnIndex, applySearch) {
    applySearch = applySearch ? 'applied' : 'none';

    var values = wpDataTables[tableId]
        .api()
        .column(columnIndex, {search: applySearch})
        .data()
        .unique()
        .toArray()
        .filter(Boolean)
        .sort();

    var result = [];

    for (var i = 0; i < values.length; i++) {
        result[i] = [];
        result[i]['value'] = values[i];
        result[i]['label'] = values[i];
    }

    return result;
}

/**
 * Build search string and filter the table for "Multiselectbox" and "Checkbox" filters
 * @param index
 * @param value
 * @param valuesLength
 * @param exactFiltering
 */
function buildSearchStringForMultiFilters(value, exactFiltering) {
    var search = '', or = '|';

    if (exactFiltering) {
        search = search + '^' + value.replace(/\+/g, '\\+') + '$' + or;
    } else {
        search = search + value.replace(/\+/g, '\\+') + or;
    }

    return decodeURIComponent(search);
}

/**
 * Function that attach event on clear filters button
 */
function wdtClearFilters() {
    jQuery('.wdt-clear-filters-button, .wdt-clear-filters-widget-button').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        e.preventDefault();

        var button = jQuery(e.target);
        if (button.is('.wdt-clear-filters-widget-button')) {
            jQuery('.filter_column input:not([type="checkbox"])').val('');
            jQuery('.filter_column select').val('').trigger('change');
            jQuery('.filter_column select').selectpicker('val', '');
            jQuery('.filter_column input:checkbox').removeAttr('checked');
            jQuery('.noUi-target').each(function(columnIndex){
                jQuery('.noUi-target')[columnIndex].noUiSlider.reset();
            });

            for (var i in wpDataTables) {
                wpDataTables[i].api().columns().search('').draw();
            }

            jQuery('.filter_column select').find('.filter_column').eq(0).change();
        } else {
            var wpDataTableSelecter = jQuery(this).closest('.wpDataTables');

            wpDataTableSelecter.find('.filter_column input:not([type="checkbox"])').val('');
            wpDataTableSelecter.find('.filter_column select').val('').trigger('change');
            wpDataTableSelecter.find('.filter_column select').selectpicker('val', '');
            wpDataTableSelecter.find('.filter_column input:checkbox').removeAttr('checked');

            wpDataTableSelecter.find('.noUi-target').each(function(columnIndex){
                wpDataTableSelecter.find('.noUi-target')[columnIndex].noUiSlider.reset();
            });

            var tableId = '';
            if (jQuery(this).parent().is('#wdt-clear-filters-button-block')) {
                tableId = jQuery(this).data('table_id');
            } else {
                tableId = jQuery(this).closest('.wpDataTablesWrapper').find('table.wpDataTable').prop('id');
            }

            wpDataTables[tableId].api().columns().search('').draw();

            wpDataTableSelecter.find('.wdt-filter-control').eq(0).change();
        }
    });
}
