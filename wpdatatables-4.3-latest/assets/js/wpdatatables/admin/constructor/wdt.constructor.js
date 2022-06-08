var constructedTableData = {
    name: '',
    method: '',
    columnCount: 0,
    columns: [],
    connection: ''
};

var defaultPostColumns = [
    'ID',
    'post_date',
    'post_date_gmt',
    'post_author',
    'post_title',
    'title_with_link_to_post',
    'thumbnail_with_link_to_post',
    'post_content',
    'post_content_limited_100_chars',
    'post_excerpt',
    'post_status',
    'comment_status',
    'ping_status',
    'post_password',
    'post_name',
    'to_ping',
    'pinged',
    'post_modified',
    'post_modified_gmt',
    'post_content_filtered',
    'post_parent',
    'guid',
    'menu_order',
    'post_type',
    'post_mime_type',
    'comment_count'
];

var aceEditor = null;

(function ($) {

    var wdtNonce = $('#wdtNonce').val();
    var customUploader;
    var nextStepButton = $('#wdt-constructor-next-step');
    var previousStepButton = $('#wdt-constructor-previous-step');

    constructedTableData.connection = $('#wdt-constructor-table-connection').length !== 0 ? $('#wdt-constructor-table-connection').val() : '';

    /**
     * Default column data
     * @type {{name: *, type: string}}
     */
    var defaultColumnData = {
        'name': wdtConstructorStrings.newColumnName,
        'type': 'input'
    };

    /**
     * Add dragging/reordering for column blocks
     */
    function wdtApplyColumnReordering() {
        dragula({
            isContainer: function (el) {
                return el.classList.contains('wdt-constructor-columns-container');
            }
        });
    }

    /**
     * Add dragging for "All post types" and "Selected post types" cards
     */
    function wdtApplyPostTypesDragging() {
        var drake = dragula([document.querySelector('#wdt-constructor-post-types-all-table'), document.querySelector('#wdt-constructor-post-types-selected-table')]);
        drake.on('drop', function (el, target) {
            if ($(target).is('#wdt-constructor-post-types-all-table')) {
                var postType = $(el).find('td').html();
                $('.wdt-constructor-post-columns-selected .card .card-body tr').each(function () {
                    if ($(this).find('td').html().match('^' + postType + '\\.'))
                        $(this).remove();
                });
            }
            wdtAddPostTypes();
        });
    }

    /**
     * Add dragging for "All post properties" and "Selected post properties" cards
     */
    function wdtApplyPostColumnsDragging() {
        var drake = dragula([document.querySelector('#wdt-constructor-post-columns-all-table'), document.querySelector('#wdt-constructor-post-columns-selected-table')]);
        drake.on('drop', function () {
            wdtAddPostColumns();
        });
    }

    /**
     * Add dragging for "All MySQL tables" and "Selected MySQL tables" cards
     */
    function wdtApplyMySqlTablesDragging() {
        var drake = dragula([document.querySelector('#wdt-constructor-mysql-tables-all-table'), document.querySelector('#wdt-constructor-mysql-tables-selected-table')]);
        drake.on('drop', function (el, target) {
            if ($(target).is('#wdt-constructor-mysql-tables-all-table')) {
                var mySqlTable = $(el).find('td').html();
                $('.wdt-constructor-mysql-columns-selected .card .card-body tr').each(function () {
                    if ($(this).find('td').html().match('^' + mySqlTable + '\\.'))
                        $(this).remove();
                });
            }
            wdtAddMySqlTables();
        });
    }

    /**
     * Add dragging for "All MySQL columns" and "Selected MySQL columns" cards
     */
    function wdtApplyMySqlColumnsDragging() {
        var drake = dragula([document.querySelector('#wdt-constructor-mysql-columns-all-table'), document.querySelector('#wdt-constructor-mysql-columns-selected-table')]);
        drake.on('drop', function () {
            wdtAddMySqlColumns();
        });
    }

    /**
     * Apply selectpicker and taginput to input fields
     */
    function wdtApplyBootstrapElements() {
        $('.wdt-constructor-column-type').selectpicker();
        $('.wdt-constructor-date-input-format').selectpicker();
        $('.wdt-constructor-possible-values').tagsinput({
            tagClass: 'label label-primary'
        });
    }

    $('.wdt-constructor-type-selecter-block .card').on('click', function () {
        $('.wdt-constructor-type-selecter-block .card').removeClass('selected').addClass('not-selected');
        $(this).addClass('selected').removeClass('not-selected');
        nextStepButton.prop('disabled', false);
    });

    /**
     * Next step handler
     */
    nextStepButton.click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $curStepBlock = $('div.wdt-constructor-step:visible:eq(0)');
        var curStep = $curStepBlock.data('step');

        switch (curStep) {
            case 1:
                $curStepBlock.hide();
                previousStepButton.prop('disabled', false);
                var inputMethod = $('.wdt-constructor-type-selecter-block .card.selected').data('value');
                constructedTableData.method = inputMethod;
                switch (inputMethod) {
                    case 'simple':
                        $('div.wdt-constructor-step[data-step="1-0"]').animateFadeIn();
                        nextStepButton.hide();
                        previousStepButton.prop('disabled', false);
                        previousStepButton.hide();
                        $('.wdt-constructor-create-custom-buttons').show();
                        break;
                    case 'source':
                        $('.wdt-preload-layer').animateFadeIn();
                        var connection = (constructedTableData.connection !== '') ? '&connection=' + constructedTableData.connection : '&connection';
                        window.location.replace(window.location.pathname + '?page=wpdatatables-constructor&source' + connection);
                        break;
                    case 'manual':
                        $('div.wdt-constructor-step[data-step="1-1"]').animateFadeIn();
                        $('#wdt-constructor-number-of-columns').change().keyup();
                        $('#wdt-constructor-manual-table-name').change();
                        previousStepButton.animateFadeIn();
                        nextStepButton.prop('disabled', 'disabled');
                        nextStepButton.hide();
                        $('.wdt-constructor-create-buttons').show();
                        wdtApplyBootstrapElements();
                        wdtApplyColumnReordering();
                        break;
                    case 'file':
                        $('div.wdt-constructor-step[data-step="1-2"]').animateFadeIn();
                        break;
                    case 'wp':

                        $('#wdt-constructor-table-connection').find("option:selected").each(function() {
                          // remove data for selected 'wp' option if connection is not WP
                          if ($(this).attr("data-vendor")) {
                            // remove data for selected 'mysql' option
                            $('#wdt-constructor-post-types-selected-table').find('tr').each(function(index, element) {
                              $(element).addClass('selected');
                            });

                            $('.wdt-constructor-remove-post-type').trigger('click');

                            $('#wdt-constructor-post-types-all-table').children().hide();
                          } else {
                            $('#wdt-constructor-post-types-all-table').children().show();
                          }
                        });
                        $('div.wdt-constructor-step[data-step="1-3"]').animateFadeIn();
                        previousStepButton.animateFadeIn();
                        wdtApplyPostTypesDragging();
                        wdtApplyPostColumnsDragging();
                        break;
                    case 'mysql':
                        $('div.wdt-constructor-step[data-step="1-4"]').animateFadeIn();
                        previousStepButton.animateFadeIn();
                        wdtApplyMySqlTablesDragging();
                        wdtApplyMySqlColumnsDragging();
                        break;
                }
                break;
            case '1-2':
                // Validation
                if (!$('#wdt-constructor-input-url').val()) {
                    wdtNotify(wpdatatables_edit_strings.error, wdtConstructorStrings.fileUploadEmptyFile, 'danger');
                    return;
                }
                constructedTableData.file = $('#wdt-constructor-input-url').val();

                // Validation for valid URL link of Google spreadsheet
                if (constructedTableData.file.indexOf("docs.google.com") != -1 && constructedTableData.file.indexOf("2PACX") != -1 ) {
                    $('#wdt-error-modal .modal-body').html('URL from Google spreadsheet publish modal(popup) is not valid for wpDataTables. Please provide a valid URL link that you get from the browser address bar. More info in our documentation on this <a href="https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-google-spreadsheets/" target="_blank">link</a>. ');
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                    $('div.wdt-constructor-step[data-step="1-2"]').animateFadeIn();
                    return;
                }
                $('.wdt-preload-layer').animateFadeIn();
                $curStepBlock.hide();
                previousStepButton.animateFadeIn();
                nextStepButton.hide();
                wdtGenerateAndPreviewFileTable();
                break;
            case '1-3':
                // Validation
                if (!$('#wdt-constructor-post-columns-selected-table tr').length) {
                    wdtNotify(wpdatatables_edit_strings.error, wdtConstructorStrings.columnsEmpty, 'danger');
                    return;
                }

                $('#wdt-constructor-wp-query-table-name').change();

                if (!$('#wdt-constructor-wp-query-table-name').val()) {
                    wdtNotify(wpdatatables_edit_strings.error, wdtConstructorStrings.tableNameEmpty, 'danger');
                    return;
                }
                $curStepBlock.hide();
                previousStepButton.animateFadeIn();
                nextStepButton.hide();
                wdtGenerateAndPreviewWPQuery();
                break;
            case '1-4':
                // Validation
                if (!$('#wdt-constructor-mysql-columns-selected-table tr').length) {
                    wdtNotify(wpdatatables_edit_strings.error, wdtConstructorStrings.columnsEmpty, 'danger');
                    return;
                }

                $('#wdt-constructor-mysql-query-table-name').change();

                if (!$('#wdt-constructor-mysql-query-table-name').val()) {
                    wdtNotify(wpdatatables_edit_strings.error, wdtConstructorStrings.tableNameEmpty, 'danger');
                    return;
                }
                $curStepBlock.hide();
                previousStepButton.animateFadeIn();
                nextStepButton.hide();
                wdtGenerateAndPreviewMySQLQuery();
                break;
        }
    });

    /**
     * Previous step handler
     */
    previousStepButton.click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $curStepBlock = $('div.wdt-constructor-step:visible:eq(0)');
        var curStep = $curStepBlock.data('step');
        $curStepBlock.hide();

        switch (curStep) {
            case '1-1':
                previousStepButton.prop('disabled', 'disabled');
                previousStepButton.hide();
                nextStepButton.prop('disabled', false);
                nextStepButton.animateFadeIn();
                $('.wdt-constructor-columns-container').html('');
                $('#wdt-constructor-number-of-columns').val(4);
                constructedTableData.columnCount = 0;
                $('div.wdt-constructor-step[data-step="1"]').animateFadeIn();
                $('.wdt-constructor-create-buttons').hide();
                break;
            case '1-2':
                previousStepButton.prop('disabled', 'disabled');
                previousStepButton.hide();
                nextStepButton.animateFadeIn();
                $('.wdt-constructor-columns-container').html('');
                $('#wdt-constructor-number-of-columns').val(4);
                $('#wdt-constructor-input-url').val('');
                constructedTableData.columnCount = 0;
                $('div.wdt-constructor-step[data-step="1"]').animateFadeIn();
                break;
            case '1-3':
            case '1-4':
                previousStepButton.prop('disabled', 'disabled');
                previousStepButton.hide();
                nextStepButton.animateFadeIn();
                $('div.wdt-constructor-step[data-step="1"]').animateFadeIn();
                break;
            case '2-2':
                $curStepBlock.hide();
                nextStepButton.prop('disabled', false);
                previousStepButton.hide();
                nextStepButton.animateFadeIn();
                $('div.wdt-constructor-step[data-step="1-2"]').animateFadeIn();
                $('.wdt-constructor-create-buttons').hide();
                break;
            case '2-3':
                if (constructedTableData.method == 'wp') {
                    $('div.wdt-constructor-step[data-step="1-3"]').animateFadeIn();
                } else if (constructedTableData.method == 'mysql') {
                    $('div.wdt-constructor-step[data-step="1-4"]').animateFadeIn();
                }
                $('.wdt-constructor-create-buttons').hide();
                nextStepButton.prop('disabled', false);
                nextStepButton.animateFadeIn();
                break;
        }

    });

    /**
     * Change table name for Simple table
     */
    $('#wdt-constructor-simple-table-name').change(function (e) {
        e.preventDefault();
        constructedTableData.name = $(this).val();
    });

    /**
     * Handler which creates the table
     */
    $('#wdt-simple-table-constructor').click(function (e) {
        e.preventDefault();
        $('.wdt-preload-layer').animateFadeIn();
        if (constructedTableData.method == 'simple'){

            var columns = $('#wdt-simple-table-number-of-columns').val(),
                rows = $('#wdt-simple-table-number-of-rows').val(),
                wdtNonce = $('#wdtNonce').val();

            if (columns == "" || columns == 0) {
                wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.numberOfColumnsError, 'danger');
                $('.wdt-preload-layer').animateFadeOut();
                return;
            }

            if ( rows == "" || rows == 0) {
                wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.numberOfRowsError, 'danger');
                $('.wdt-preload-layer').animateFadeOut();
                return;
            }

            var colWidths = Array(parseInt(columns)).fill(null).map((u, i) => i)

            $('#wdt-constructor-simple-table-name').change();

            constructedTableData.title = constructedTableData.name;
            constructedTableData.table_type = constructedTableData.method;
            constructedTableData.content = {};
            constructedTableData.content.rowNumber = parseInt(rows);
            constructedTableData.content.colNumber = parseInt(columns);
            constructedTableData.content.colHeaders = [];
            constructedTableData.content.mergedCells = [];
            constructedTableData.content.reloadCounter = 0;
            constructedTableData.content.colWidths = colWidths.fill(100,0,parseInt(columns));

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_create_simple_table',
                    tableData: JSON.stringify(constructedTableData),
                    wdtNonce: wdtNonce
                },
                success: function (link) {
                    window.location = link;
                },
                error: function (data) {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to generate the table! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            })

        }

    });

    /**
     * Change column count for manual tables
     */
    $('#wdt-constructor-number-of-columns').bind('change keyup',function (e) {
        e.preventDefault();

        var newColumnCount = parseInt($(this).val());

        isNaN(newColumnCount) ? newColumnCount = 0 : newColumnCount;

        isNaN(constructedTableData.columnCount) ? constructedTableData.columnCount = 0 : constructedTableData.columnCount;

        if (newColumnCount > constructedTableData.columnCount) {
            // We need to add more columns
            for (var i = constructedTableData.columnCount; i < newColumnCount; i++) {
                $(wdtGetColumnHtml(defaultColumnData)).hide().appendTo('.wdt-constructor-step[data-step="' + $('div.wdt-constructor-step:visible:eq(0)').data('step') + '"] .wdt-constructor-columns-container').animateFadeIn();
                wdtApplyBootstrapElements();
            }
        } else if (newColumnCount < constructedTableData.columnCount) {
            // We need to remove some columns
            for (i = constructedTableData.columnCount - 1; i > newColumnCount - 1; i--) {
                $('.wdt-constructor-step[data-step="' + $('div.wdt-constructor-step:visible:eq(0)').data('step') + '"] .wdt-constructor-columns-container div.wdt-constructor-column-block:eq(' + i + ')').remove();
            }
        }

        constructedTableData.columnCount = newColumnCount;
    });

    /**
     * Change table name for manual, wp-query and mysql-query based tables
     */
    $(document).on('change','#wdt-constructor-manual-table-name, #wdt-constructor-wp-query-table-name, #wdt-constructor-mysql-query-table-name', function (e) {
        e.preventDefault();
        constructedTableData.name = $(this).val();
    });

    function disableGroupingOptions (select) {
      $(select).find("option:selected").each(function() {
        if ($(this).attr("data-vendor") === 'mssql' || $(this).attr("data-vendor") === 'postgresql') {
          $('.wdt-constructor-mysql-grouping-rules-block').css('visibility', 'hidden');
        } else {
          $('.wdt-constructor-mysql-grouping-rules-block').css('visibility', 'visible');
        }
      });
    }

    disableGroupingOptions('#wdt-constructor-table-connection');

    /**
     * Change connection
     */
    $('#wdt-constructor-table-connection').change(function (e) {
        e.preventDefault();

        // remove data for selected 'mysql' option
        $('#wdt-constructor-mysql-tables-selected-table').find('tr').each(function(index, element) {
          $(element).addClass('selected');
        });

        $('.wdt-constructor-remove-mysql-table').trigger('click');

        // disable GROUP BY for selected 'mysql' option
        disableGroupingOptions(this);

        constructedTableData.connection = $(this).val();

        var inputMethod = $('.wdt-constructor-type-selecter-block .card.selected').data('value');

        if (typeof inputMethod !== 'undefined') {
          $('#wdt-constructor-next-step').prop('disabled', true);
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpdatatables_get_connection_tables',
                connection: constructedTableData.connection,
                wdtNonce: $('#wdtNonce').val()
            },
            success: function (result) {
                var tables = JSON.parse(result);

                $('#wdt-constructor-mysql-tables-all-table').html('');

                for (var i = 0; i < tables.length; i++) {
                  $('#wdt-constructor-mysql-tables-all-table').append($('<tr>').append($('<td>').text(tables[i])));
                }

                if (typeof inputMethod !== 'undefined') {
                  $('#wdt-constructor-next-step').prop('disabled', false);
                }
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to get connection tables! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();

                if (typeof inputMethod !== 'undefined') {
                  $('#wdt-constructor-next-step').prop('disabled', false);
                }
            }
        })
    });

    /**
     * Change table name for tables imported from files
     */
    $('#wdt-constructor-file-table-name').change(function (e) {
        e.preventDefault();
        constructedTableData.name = $(this).val();
    });

    /**
     * Get HTML for a column block
     */
    var wdtGetColumnHtml = function (columnData) {
        var columnTemplate = $.templates("#wdt-constructor-column-block-template");
        return columnTemplate.render(columnData);
    };

    /**
     * Show the "Possible values" tagger for selectbox-type inputs
     */
    $(document).on('change', 'select.wdt-constructor-column-type', function (e) {
        var $columnBlock = $(this).closest('div.wdt-constructor-column-block');
        var $possibleValuesInput = $columnBlock.find('.wdt-constructor-possible-values');
        var $possibleValuesBlock = $columnBlock.find('.wdt-constructor-possible-values-block');
        var $dateInputBlock = $columnBlock.find('.wdt-constructor-date-input-format-block');
        if ($(this).val() == 'select' || $(this).val() == 'multiselect') {
            $dateInputBlock.hide();
            $possibleValuesBlock.show();
            $columnBlock.find('.wdt-constructor-default-value').selectpicker('destroy');
            $columnBlock.find('.wdt-constructor-default-value')
                .replaceWith('<select class="selectpicker wdt-constructor-default-value"></select>');
            if ($(this).val() == 'multiselect') {
                $columnBlock.find('.wdt-constructor-default-value').attr('multiple', 'multiple');
            } else {
                $columnBlock.find('.wdt-constructor-default-value').prepend('<option value=""></option>').removeAttr('multiple');
            }
            $columnBlock.find('.wdt-constructor-default-value').selectpicker();

            if ($columnBlock.find('.wdt-constructor-possible-values').val() != '') {
                var possibleValues = $columnBlock.find('.wdt-constructor-possible-values').val().split(',');
                $.each(possibleValues, function (index, value) {
                    $columnBlock.find('select.wdt-constructor-default-value').append('<option value="' + value + '">' + value + '</option>');
                });
                $columnBlock.find('select.wdt-constructor-default-value').selectpicker('refresh');
            }

            $possibleValuesInput.on('itemAdded', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $columnBlock.find('select.wdt-constructor-default-value').append('<option value="' + e.item + '">' + e.item + '</option>')
                    .selectpicker('refresh');
            });

            $possibleValuesInput.on('itemRemoved', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $columnBlock.find('.wdt-constructor-default-value option[value="' + e.item + '"]').remove();
                $columnBlock.find('.wdt-constructor-default-value').selectpicker('refresh');
            });
        } else {
            if ($(this).val() == 'date' || $(this).val() == 'datetime') {
                $dateInputBlock.show();
            } else {
                $dateInputBlock.hide();
            }

            $possibleValuesBlock.hide();
            $columnBlock.find('.wdt-constructor-default-value').selectpicker('destroy');
            $columnBlock.find('.wdt-constructor-default-value')
                .replaceWith('<input type="text" class="form-control input-sm wdt-constructor-default-value" value="">');
            $columnBlock.find('.wdt-constructor-default-value')
                .attr('type', 'text');

            if ($.inArray($(this).val(), ['date', 'datetime', 'time']) != -1) {
                $columnBlock.find('.wdt-constructor-default-value')
                    .addClass('wdt-' + $(this).val() + 'picker');
            }

            if ($.inArray($(this).val(), ['int', 'float']) != -1) {
                $columnBlock.find('.wdt-constructor-default-value')
                    .attr('type', 'number');
            }

        }
    });

    /**
     * Add a column with "+"
     */
    $(document).on('click', 'button#wdt-constructor-add-column', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('#wdt-constructor-number-of-columns').val(parseInt($('#wdt-constructor-number-of-columns').val()) + 1).change();
    });

    /**
     * Remove a column with "X"
     */
    $(document).on('click', '.wdt-constructor-remove-column', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.wdt-constructor-column-block').remove();

        $('#wdt-constructor-number-of-columns').val(parseInt($('#wdt-constructor-number-of-columns').val()) - 1);
        constructedTableData.columnCount = parseInt($('#wdt-constructor-number-of-columns').val());
    });

    /**
     * Open WordPress media uploader
     */
    $('#wdt-constructor-browse-button').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        e.preventDefault();

        var attachment = '';
        //If the uploader object has already been created, reopen the dialog
        if (customUploader) {
            customUploader.open();
            return;
        }

        customUploader = wp.media.frames.file_frame = wp.media({
            title: wdtConstructorStrings.selectExcelCsv,
            button: {
                text: wdtConstructorStrings.chooseFile
            },
            multiple: false,
            library: {
                type: 'application/vnd.ms-excel,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            }
        });

        customUploader.on('select', function () {
            attachment = customUploader.state().get('selection').first().toJSON();
            $('#wdt-constructor-input-url').val(attachment.url);
        });

        customUploader.open();

    });

    /**
     * Preview a table based on the file
     */
    function wdtGenerateAndPreviewFileTable() {
        $('.wdt-preload-layer').show();

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'wpdatatables_preview_file_table',
                tableData: constructedTableData,
                wdtNonce: wdtNonce
            },
            type: 'post',
            dataType: 'json',
            success: function (data) {
                if (data.result == 'error') {
                    $('.wdt-preload-layer').hide();
                    $('div.wdt-constructor-step[data-step="1-2"]').animateFadeIn();
                    nextStepButton.prop('disabled', false);
                    $('.wdt-constructor-create-buttons').hide();
                    wdtNotify(wpdatatables_edit_strings.error, data.message, 'danger')
                } else {
                    $('div.wdt-constructor-step[data-step="2-2"] div.wdt-constructor-columns-container').html(data.message);
                    constructedTableData.columnCount = parseInt($('div.wdt-constructor-column-block').length);
                    $('#wdt-constructor-number-of-columns').val(constructedTableData.columnCount);
                    $('div.wdt-constructor-step[data-step="2-2"]').animateFadeIn();
                    $('.wdt-constructor-column-type').change();
                    nextStepButton.prop('disabled', 'disabled');
                    $('.wdt-constructor-create-buttons').show();
                    wdtApplyBootstrapElements();
                    wdtApplyColumnReordering();
                    $('.wdt-preload-layer').hide();
                }
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to generate the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
                $('div.wdt-constructor-step[data-step="1-2"]').animateFadeIn();
            }
        })

    }

    /**
     * Handler which creates the table for manual and file method
     */
    $('#wdt-constructor-create-table, #wdt-constructor-create-table-excel').click(function (e) {
        e.preventDefault();

        var tableView = '';
        if ($(this).prop('id') == 'wdt-constructor-create-table-excel') {
            tableView = '&table_view=excel';
        }


        if (constructedTableData.method == 'manual') {

            if (!$('#wdt-constructor-manual-table-name').val()) {
                wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.tableNameEmpty, 'danger');
                return;
            }

            constructedTableData.columns = [];
            $('div.wdt-constructor-column-block').each(function () {
                var columnType = $(this).find('.wdt-constructor-column-type').selectpicker('val');
                var defaultValue = $.inArray(columnType, ['select', 'multiselect']) != -1 ?
                    $(this).find('.wdt-constructor-default-value').selectpicker('val') :
                    $(this).find('.wdt-constructor-default-value').val();
                if (defaultValue != null && columnType == 'multiselect') {
                    defaultValue.join('|');
                }
                constructedTableData.columns.push({
                    name: $(this).find('.wdt-constructor-column-name').val(),
                    type: columnType,
                    possible_values: $(this).find('.wdt-constructor-possible-values').val().replace(/,/g, '|'),
                    default_value: defaultValue
                });
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_create_manual_table',
                    tableData: constructedTableData,
                    wdtNonce: wdtNonce
                },
                success: function (link) {
                    window.location = link + tableView;
                },
                error: function (data) {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to generate the table! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            })
        } else if (constructedTableData.method == 'file') {

            if (!$('#wdt-constructor-file-table-name').val()) {
                wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.tableNameEmpty, 'danger');
                return;
            }
            // Validation
            var valid = true;
            $('.wdt-constructor-column-name').each(function () {
                if ($(this).val() == '') {
                    $(this).click();
                    valid = false;
                }
            });

            if (valid) {
                $('div.wdt-constructor-step[data-step="2-2"]').hide();
                $('.wdt-preload-layer').show();
                constructedTableData.columns = [];
                $('.wdt-constructor-column-block').each(function () {
                    constructedTableData.columns.push({
                        orig_header: $(this).find('.wdt-constructor-column-name').val(),
                        name: $(this).find('.wdt-constructor-column-name').val(),
                        type: $(this).find('.wdt-constructor-column-type').selectpicker('val'),
                        possible_values: $(this).find('.wdt-constructor-possible-values').val().replace(/,/g, '|'),
                        default_value: $(this).find('.wdt-constructor-default-value').val(),
                        dateInputFormat: typeof $(this).find('.wdt-constructor-date-input-format').val() !== 'undefined' ?
                            $(this).find('.wdt-constructor-date-input-format').selectpicker('val') : ''
                    });
                });

                $('#wdt-constructor-file-table-name').change();

                wdtReadFileDataAndEditTable(tableView);
            }
        } else {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'wpdatatables_constructor_generate_wdt',
                    table_data: constructedTableData,
                    wdtNonce: wdtNonce
                },
                type: 'post',
                dataType: 'json',
                success: function (data) {
                    if (typeof data.error == 'undefined') {
                        window.location = data.link + tableView;
                    } else {
                        $('.wdt-preload-layer').hide();
                        wdtNotify(wdtConstructorStrings.sql_error, data.error, 'danger');
                    }
                },
                error: function (data) {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to generate the table! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            })
        }

    });

    /**
     * Generate the Excel/CSV/Google and open table settings
     * @param tableView
     */
    function wdtReadFileDataAndEditTable(tableView) {
        $.ajax({
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'wpdatatables_constructor_read_file_data',
                tableData: constructedTableData,
                wdtNonce: wdtNonce
            },
            type: 'post',
            success: function (data) {
                if (data.res == 'success') {
                    window.location = data.link + tableView;
                } else {
                    $('div.wdt-constructor-step[data-step="2-2"]').show();
                    $('.wdt-preload-layer').hide();
                    wdtNotify(wpdatatables_edit_strings.error, data.text, 'danger');
                }
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        })
    }

    /**
     * Select a row in cards
     */
    $(document).on('click', '.wdt-constructor-query-data-step .card .card-body tr', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $(this).hasClass('selected') ? $(this).removeClass('selected') : $(this).addClass('selected');

    });

    /**
     * Add post type to "Selected post types" card
     */
    $('.wdt-constructor-add-post-type').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-post-types-all .card .card-body tr.selected').each(function (i) {
            $(this).appendTo('.wdt-constructor-post-types-selected .card .card-body table tbody').removeClass('selected');
        });

        wdtAddPostTypes();

    });

    /**
     * Remove post type from "Selected post types" card
     */
    $('.wdt-constructor-remove-post-type').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-post-types-selected .card .card-body tr.selected').each(function () {
            var postType = $(this).find('td').html();
            $(this).appendTo('.wdt-constructor-post-types-all .card .card-body table tbody').removeClass('selected');
            $('.wdt-constructor-post-columns-selected .card .card-body tr').each(function () {
                if ($(this).find('td').html().match('^' + postType + '\\.'))
                    $(this).remove();
            });
        });

        wdtAddPostTypes();

    });

    /**
     * Add post column to "Selected post properties" card
     */
    $('.wdt-constructor-add-post-column').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-post-columns-all .card .card-body tr.selected').each(function (i) {
            $(this).appendTo('.wdt-constructor-post-columns-selected .card .card-body table tbody').removeClass('selected');
        });

        wdtAddPostColumns();

    });

    /**
     * Remove post column from "Selected post properties" card
     */
    $('.wdt-constructor-remove-post-column').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-post-columns-selected .card .card-body tr.selected').each(function () {
            $(this).appendTo('.wdt-constructor-post-columns-all .card .card-body table tbody').removeClass('selected');
        });

        wdtAddPostColumns();

    });

    /**
     * When post type is added, populate "All post properties" card and show related blocks
     */
    function wdtAddPostTypes() {
        constructedTableData.postTypes = [];
        var availablePostColumns = [];

        $('.wdt-constructor-post-types-selected .card .card-body tr').each(function (i) {
            var postType = $(this).find('td').html();

            // If it is selected 'all' as Post Type option, remove other options
            if (postType == 'all') {
                constructedTableData.postTypes = ['all'];
                availablePostColumns = [];

                $('.wdt-constructor-post-types-selected .card .card-body tr').not(this).each(function (i) {
                    $(this).appendTo('.wdt-constructor-post-types-all .card .card-body table tbody');
                });

                $('.wdt-constructor-post-columns-selected .card .card-body tr').each(function (i) {
                    $(this).remove();
                });

                for (var j in defaultPostColumns) {
                    availablePostColumns.push(constructedTableData.postTypes[0] + '.' + defaultPostColumns[j]);
                }
                return false;
            } else {
                constructedTableData.postTypes.push(postType);

                for (var j in defaultPostColumns) {
                    availablePostColumns.push(constructedTableData.postTypes[i] + '.' + defaultPostColumns[j]);
                }
                if (typeof wdtPostMetaByPostTypes[constructedTableData.postTypes[i]] !== 'undefined') {
                    for (var j in wdtPostMetaByPostTypes[constructedTableData.postTypes[i]]) {
                        availablePostColumns.push(constructedTableData.postTypes[i] + '.meta.' + wdtPostMetaByPostTypes[constructedTableData.postTypes[i]][j]);
                    }
                }
                if (typeof wdtTaxonomiesByPostTypes[constructedTableData.postTypes[i]] !== 'undefined') {
                    for (var j in wdtTaxonomiesByPostTypes[constructedTableData.postTypes[i]]) {
                        availablePostColumns.push(constructedTableData.postTypes[i] + '.taxonomy.' + wdtTaxonomiesByPostTypes[constructedTableData.postTypes[i]][j]);
                    }
                }
            }

        });

        if (constructedTableData.postTypes.length > 1) {

            var curValues = {};
            $('div.wdt-constructor-post-block').each(function (i) {
                var curInitiatorType = $(this).find('select.wdt-constructor-relation-initiator-column').data('post-type');

                curValues[curInitiatorType] = {
                    curInitiatorColumn: $(this).find('select.wdt-constructor-relation-initiator-column').val(),
                    curConnectedColumn: $(this).find('select.wdt-constructor-relation-connected-column').val(),
                    curRelationJoin: $(this).find('#wdt-constructor-relation-inner-join-' + curInitiatorType).is(':checked') ? 1 : 0
                }

            });

            $('.wdt-constructor-post-types-define-relations-block div#wdt-constructor-post-types-relations').html('');
            for (var i in constructedTableData.postTypes) {
                var postTypeBlock = {
                    postType: constructedTableData.postTypes[i],
                    postTypeColumns: [],
                    otherPostTypeColumns: []
                };
                postTypeBlock.postTypeColumns = wdtGetColumnsByPostType(constructedTableData.postTypes[i], false, false);
                for (var j in constructedTableData.postTypes) {
                    if (constructedTableData.postTypes[i] == constructedTableData.postTypes[j]) {
                        continue;
                    }
                    postTypeBlock.otherPostTypeColumns = postTypeBlock.otherPostTypeColumns.concat(wdtGetColumnsByPostType(constructedTableData.postTypes[j], true, true));
                }

                var wpRelationBlockTemplate = $.templates("#wdt-constructor-wp-relation-block-template");
                var wpRelationBlockHtml = wpRelationBlockTemplate.render(postTypeBlock);
                var postType = $($.parseHTML(wpRelationBlockHtml)).closest('.wdt-constructor-post-block').data('post-type')
                $('.wdt-constructor-post-types-define-relations-block div#wdt-constructor-post-types-relations').append(wpRelationBlockHtml);
                if (curValues[postType]) {
                    $('select.wdt-constructor-relation-initiator-column[data-post-type=' + postType + ']').val(curValues[postType]['curInitiatorColumn']);
                    $('select.wdt-constructor-relation-connected-column[data-post-type=' + postType + ']').val(curValues[postType]['curConnectedColumn']);
                    $('.wdt-constructor-post-block[data-post-type=' + postType + '] #wdt-constructor-relation-inner-join-' + postType).prop('checked', curValues[postType]['curRelationJoin']);
                    $('select[data-post-type=' + postType + ']').selectpicker('refresh');
                }
            }

            $('.wdt-constructor-relation-initiator-column').selectpicker();
            $('.wdt-constructor-relation-connected-column').selectpicker();

            if (!$('.wdt-constructor-post-types-relationship-block').is(':visible')) {
                $('.wdt-constructor-post-types-relationship-block').animateFadeIn();
                if ($('#wdt-constructor-post-types-relationship').is(':checked')) {
                    $('.wdt-constructor-post-types-define-relations-block').animateFadeIn();
                }
            }
        } else {
            $('.wdt-constructor-post-types-relationship-block').hide();
            $('.wdt-constructor-post-types-define-relations-block').hide();
        }

        if (constructedTableData.postTypes.length > 0) {
            if (!$('.wdt-constructor-post-conditions-block').is(':visible'))
                $('.wdt-constructor-post-conditions-block').animateFadeIn();
            if (!$('.wdt-constructor-post-grouping-rules-block').is(':visible'))
                $('.wdt-constructor-post-grouping-rules-block').animateFadeIn();

            var postTypeAllColumns = [];
            for (i in constructedTableData.postTypes) {
                postTypeAllColumns = postTypeAllColumns.concat(wdtGetColumnsByPostType(constructedTableData.postTypes[i], true));
            }

            var conditionOptionsTemplate = $.templates("#wdt-constructor-post-columns-options-template");
            var conditionOptionsHtml = conditionOptionsTemplate.render(postTypeAllColumns);
            $('select.wdt-constructor-where-condition-column').each(function () {
                var curVal = $(this).val();
                $(this).html('<option value=""></option>');
                $(conditionOptionsHtml).appendTo(this);
                $(this).val(curVal);
                if ($(this).val() != curVal) {
                    $(this).selectpicker('destroy');
                    $(this).closest('div.wdt-constructor-post-where-block').remove();
                }
            });
            $('.wdt-constructor-where-condition-column').selectpicker('refresh');

        } else {
            $('.wdt-constructor-post-conditions-block').animateFadeOut(300);
            $('.wdt-constructor-post-grouping-rules-block').animateFadeOut(300);
        }

        var postColumnTemplate = $.templates("#wdt-constructor-post-column-template");
        var postColumnsHtml = postColumnTemplate.render({availablePostColumns: availablePostColumns});
        $('#wdt-constructor-post-columns-all-table').html(postColumnsHtml);
    }

    /**
     * When post column is added
     */
    function wdtAddPostColumns() {
        var postTypeColumns = $('#wdt-constructor-post-columns-selected-table tr').map(function () {
            return $(this).find('td').html();
        }).toArray();

        constructedTableData.postColumns = postTypeColumns;

        var groupingRuleOptionsTemplate = $.templates("#wdt-constructor-mysql-columns-options-template");
        var groupingRuleOptionsHtml = groupingRuleOptionsTemplate.render(postTypeColumns);
        $('select.wdt-constructor-grouping-rule-column').each(function () {
            var curVal = $(this).val();
            $(this).html('<option value=""></option>');
            $(groupingRuleOptionsHtml).appendTo(this);
            $(this).val(curVal);
            if ($(this).val() != curVal) {
                $(this).selectpicker('destroy');
                $(this).closest('div.wdt-constructor-post-grouping-rule-block').remove();
            }
        });
        $('.wdt-constructor-grouping-rule-column').selectpicker('refresh');
    }

    /**
     * Show the relations constructor when needed
     */
    $('#wdt-constructor-post-types-relationship').change(function (e) {
        e.preventDefault();
        if ($(this).is(':checked')) {
            $('.wdt-constructor-post-types-define-relations-block').animateFadeIn();
        } else {
            $('.wdt-constructor-post-types-define-relations-block').hide();
        }
    });

    /**
     * Helper function to return array of available columns by post type
     */
    function wdtGetColumnsByPostType(postType, includePostTypeName, includeMetaAndTax) {
        var arr = [];
        if (typeof includePostTypeName == 'undefined') {
            includePostTypeName = false;
        }
        if (typeof includeMetaAndTax == 'undefined') {
            includeMetaAndTax = true
        }
        var prefix = includePostTypeName ? postType + '.' : '';
        for (var j in defaultPostColumns) {
            arr.push(prefix + defaultPostColumns[j]);
        }
        if (includeMetaAndTax) {
            if (typeof wdtPostMetaByPostTypes[postType] !== 'undefined') {
                for (var j in wdtPostMetaByPostTypes[postType]) {
                    arr.push(prefix + 'meta.' + wdtPostMetaByPostTypes[postType][j]);
                }
            }
            if (typeof wdtTaxonomiesByPostTypes[postType] !== 'undefined') {
                for (var j in wdtTaxonomiesByPostTypes[postType]) {
                    arr.push(prefix + 'taxonomy.' + wdtTaxonomiesByPostTypes[postType][j]);
                }
            }
        }
        return arr;
    }

    /**
     * Add a "WHERE" condition to the WP POSTS based table
     */
    $('#wdt-constructor-add-post-condition').click(function (e) {
        e.preventDefault();

        var whereBlock = {postTypeColumns: []};
        for (var i in constructedTableData.postTypes) {
            whereBlock.postTypeColumns = whereBlock.postTypeColumns.concat(wdtGetColumnsByPostType(constructedTableData.postTypes[i], true));
        }
        var whereBlockTemplate = $.templates("#wdt-constructor-post-where-condition-template");
        var whereBlockHtml = whereBlockTemplate.render(whereBlock);
        $(whereBlockHtml).appendTo('.wdt-constructor-post-conditions-block div#wdt-constructor-post-conditions').animateFadeIn();
        $('.wdt-constructor-where-condition-column').selectpicker();
        $('.wdt-constructor-where-operator').selectpicker();
    });

    /**
     * Delete a "WHERE" condition for WP POSTS based table
     */
    $(document).on('click', '#wdt-constructor-delete-post-condition', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.wdt-constructor-post-where-block').remove();
    });

    /**
     * Add a grouping rule to the WP POSTS based table
     */
    $('#wdt-constructor-post-add-grouping-rule').click(function (e) {
        e.preventDefault();

        var groupingRuleBlock = {postTypeColumns: []};

        $('#wdt-constructor-post-columns-selected-table tr').each(function () {
            groupingRuleBlock.postTypeColumns = groupingRuleBlock.postTypeColumns.concat($(this).find('td').html());
        });

        var groupingRuleBlockTemplate = $.templates("#wdt-constructor-post-grouping-rule-template");
        var groupingRuleHtml = groupingRuleBlockTemplate.render(groupingRuleBlock);
        $(groupingRuleHtml).appendTo('.wdt-constructor-post-grouping-rules-block div#wdt-constructor-post-grouping-rules').animateFadeIn();
        $('.wdt-constructor-grouping-rule-column').selectpicker();
    });

    /**
     * Delete a grouping rule
     */
    $(document).on('click', '#wdt-constructor-delete-grouping-rule-post', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.wdt-constructor-post-grouping-rule-block').remove();
    });

    /**
     * Generate a query to WP database and preview it
     */
    function wdtGenerateAndPreviewWPQuery() {
        $('.wdt-preload-layer').show();

        constructedTableData.joinRules = [];
        constructedTableData.whereConditions = [];
        constructedTableData.groupingRules = [];

        constructedTableData.handlePostTypes = $('#wdt-constructor-post-types-relationship').is(':checked') ? 'join' : 'union';
        if (constructedTableData.handlePostTypes == 'join') {

            $('div#wdt-constructor-post-types-relations div.wdt-constructor-post-block').each(function () {
                var joinRule = {};
                joinRule.initiatorPostType = $(this).find('select.wdt-constructor-relation-initiator-column').data('post-type');
                joinRule.initiatorColumn = $(this).find('.wdt-constructor-relation-initiator-column').selectpicker('val');
                joinRule.connectedColumn = $(this).find('.wdt-constructor-relation-connected-column').selectpicker('val');
                joinRule.type = $(this).find('#wdt-constructor-relation-inner-join-' + joinRule.initiatorPostType).is(':checked') ? 'inner' : 'left';
                constructedTableData.joinRules.push(joinRule);
            });

        }

        $('div.wdt-constructor-post-conditions-block div.wdt-constructor-post-where-block').each(function () {
            var whereCondition = {};
            whereCondition.column = $(this).find('.wdt-constructor-where-condition-column').selectpicker('val');
            whereCondition.operator = $(this).find('.wdt-constructor-where-operator').selectpicker('val');
            whereCondition.value = $(this).find('#wdt-constructor-where-value').val();
            constructedTableData.whereConditions.push(whereCondition);
        });

        $('div#wdt-constructor-post-grouping-rules div.wdt-constructor-post-grouping-rule-block').each(function () {
            constructedTableData.groupingRules.push($(this).find('.wdt-constructor-grouping-rule-column').selectpicker('val'));
        });

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'wpdatatables_generate_wp_based_query',
                tableData: constructedTableData,
                wdtNonce: wdtNonce
            },
            type: 'post',
            dataType: 'json',
            success: function (data) {
                aceEditor.setValue(data.query);
                constructedTableData.query = data.query;
                $('div.wdt-constructor-preview-wp-table').html(data.preview);
                $('div.wdt-constructor-step[data-step="2-3"]').animateFadeIn();
                nextStepButton.prop('disabled', 'disabled');
                $('.wdt-constructor-create-buttons').show();
                $('.wdt-preload-layer').hide();
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        })

    }

    /**
     * Add MySQL table to "Selected MySQL tables" card
     */
    $('.wdt-constructor-add-mysql-table').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-mysql-tables-all .card .card-body tr.selected').each(function (i) {
            $(this).appendTo('.wdt-constructor-mysql-tables-selected .card .card-body table tbody').removeClass('selected');
        });

        wdtAddMySqlTables();

    });

    /**
     * Remove MySQL table from "Selected MySQL tables" card
     */
    $('.wdt-constructor-remove-mysql-table').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-mysql-tables-selected .card .card-body tr.selected').each(function () {
            var mySqlTable = $(this).find('td').html();
            $(this).appendTo('.wdt-constructor-mysql-tables-all .card .card-body table tbody').removeClass('selected');
            $('.wdt-constructor-mysql-columns-selected .card .card-body tr').each(function () {
                if ($(this).find('td').html().match('^' + mySqlTable + '\\.'))
                    $(this).remove();
            });
            $('.wdt-constructor-mysql-columns-all .card .card-body tr').each(function () {
                if ($(this).find('td').html().match('^' + mySqlTable + '\\.'))
                    $(this).remove();
            });
        });

        wdtAddMySqlTables();

    });


    /**
     * Add MySQL column to "Selected MySQL columns" card
     */
    $('.wdt-constructor-add-mysql-column').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-mysql-columns-all .card .card-body tr.selected').each(function (i) {
            $(this).appendTo('.wdt-constructor-mysql-columns-selected .card .card-body table tbody').removeClass('selected');
        });

        wdtAddMySqlColumns();

    });

    /**
     * Remove MySQL column from "Selected MySQL columns" card
     */
    $('.wdt-constructor-remove-mysql-column').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('.wdt-constructor-mysql-columns-selected .card .card-body tr.selected').each(function () {
            $(this).appendTo('.wdt-constructor-mysql-columns-all .card .card-body table tbody').removeClass('selected');
        });

        wdtAddMySqlColumns();

    });

    /**
     * When MySQL column is added
     */
    function wdtAddMySqlColumns() {
        var mySqlColumns = $('#wdt-constructor-mysql-columns-selected-table tr').map(function () {
            return $(this).find('td').html();
        }).toArray();

        constructedTableData.mySqlColumns = mySqlColumns;

        var groupingRuleOptionsTemplate = $.templates("#wdt-constructor-mysql-columns-options-template");
        var groupingRuleOptionsHtml = groupingRuleOptionsTemplate.render(mySqlColumns);
        $('select.wdt-constructor-grouping-rule-column').each(function () {
            var curVal = $(this).val();
            $(this).html('<option value=""></option>');
            $(groupingRuleOptionsHtml).appendTo(this);
            $(this).val(curVal);
            if ($(this).val() != curVal) {
                $(this).selectpicker('destroy');
                $(this).closest('div.wdt-constructor-mysql-grouping-rule-block').remove();
            }
        });
        $('.wdt-constructor-grouping-rule-column').selectpicker('refresh');
    }

    /**
     * Get columns for selected tables
     */
    function wdtAddMySqlTables() {
        var tables = [];
        var availableMySqlColumns = [];
        availableMySqlColumns.allColumns = [];
        availableMySqlColumns.sortedColumns = [];

        $('.wdt-constructor-mysql-tables-selected .card .card-body tr').each(function (i) {
            var mySqlTable = $(this).find('td').html();
            tables.push(mySqlTable);
        });

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'wpdatatables_constructor_get_mysql_table_columns',
                tables: tables,
                wdtNonce: wdtNonce,
                connection: constructedTableData.connection
            },
            type: 'post',
            dataType: 'json',
            success: function (availableMySqlColumns) {

                var mySqlColumnTemplate = $.templates("#wdt-constructor-mysql-column-template");
                var mySqlColumnHtml = mySqlColumnTemplate.render({availableMySqlColumns: availableMySqlColumns.allColumns});
                $('#wdt-constructor-mysql-columns-all-table').html(mySqlColumnHtml);
                constructedTableData.allMySqlColumns = availableMySqlColumns.allColumns;

                if (tables.length > 1) {
                    // Generate HTML block for relations
                    $('.wdt-constructor-mysql-tables-define-relations-block div#wdt-constructor-mysql-tables-relations').html('');
                    for (var i in availableMySqlColumns.sortedColumns) {
                        var mysqlTableBlock = {
                            table: i,
                            columns: [],
                            otherTableColumns: []
                        };
                        for (var j in availableMySqlColumns.sortedColumns) {
                            if (i == j) {
                                for (var k in availableMySqlColumns.sortedColumns[i]) {
                                    mysqlTableBlock.columns.push(availableMySqlColumns.sortedColumns[i][k].replace(i + '.', ''));
                                }
                                continue;
                            }
                            for (var k in availableMySqlColumns.sortedColumns[j]) {
                                mysqlTableBlock.otherTableColumns.push(availableMySqlColumns.sortedColumns[j][k]);
                            }
                        }
                        var mySqlRelationBlockTemplate = $.templates("#wdt-constructor-mysql-relation-block-template");
                        var mySqlRelationBlockHtml = mySqlRelationBlockTemplate.render(mysqlTableBlock);
                        $('.wdt-constructor-mysql-tables-define-relations-block div#wdt-constructor-mysql-tables-relations').append(mySqlRelationBlockHtml);
                    }

                    $('.wdt-constructor-relation-initiator-column').selectpicker();
                    $('.wdt-constructor-relation-connected-column').selectpicker();

                    if (!$('.wdt-constructor-mysql-tables-define-relations-block').is(':visible')) {
                        $('.wdt-constructor-mysql-tables-define-relations-block').animateFadeIn();
                    }
                } else {
                    $('.wdt-constructor-mysql-tables-define-relations-block').hide();
                }

                if (tables.length > 0) {
                    if (!$('.wdt-constructor-mysql-conditions-block').is(':visible'))
                        $('.wdt-constructor-mysql-conditions-block').animateFadeIn();
                    if (!$('.wdt-constructor-mysql-grouping-rules-block').is(':visible'))
                        $('.wdt-constructor-mysql-grouping-rules-block').animateFadeIn();

                    var conditionOptionsTemplate = $.templates("#wdt-constructor-mysql-columns-options-template");
                    var conditionOptionsHtml = conditionOptionsTemplate.render(availableMySqlColumns.allColumns);
                    $('select.wdt-constructor-where-condition-column').each(function () {
                        var curVal = $(this).val();
                        $(this).html('<option value=""></option>');
                        $(conditionOptionsHtml).appendTo(this);
                        $(this).val(curVal);
                        if ($(this).val() != curVal) {
                            $(this).selectpicker('destroy');
                            $(this).closest('div.wdt-constructor-mysql-where-block').remove();
                        }
                    });
                    $('.wdt-constructor-where-condition-column').selectpicker('refresh');

                } else {
                    $('.wdt-constructor-mysql-conditions-block').animateFadeOut(300);
                    $('.wdt-constructor-mysql-grouping-rules-block').animateFadeOut(300);
                }

            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        })
    }

    /**
     * Add a "WHERE" condition to the MySQL based table
     */
    $('#wdt-constructor-add-mysql-condition').click(function (e) {
        e.preventDefault();
        var whereBlockTemplate = $.templates("#wdt-constructor-mysql-where-condition-template");
        var whereBlockHtml = whereBlockTemplate.render(constructedTableData);
        $(whereBlockHtml).appendTo('.wdt-constructor-mysql-conditions-block div#wdt-constructor-mysql-conditions').animateFadeIn();
        $('.wdt-constructor-where-condition-column').selectpicker();
        $('.wdt-constructor-where-operator').selectpicker();
    });

    /**
     * Delete a "WHERE" condition for MySQL based table
     */
    $(document).on('click', '#wdt-constructor-delete-mysql-condition', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.wdt-constructor-mysql-where-block').remove();
    });

    /**
     * Add a grouping rule to the MySQL based table
     */
    $('#wdt-constructor-mysql-add-grouping-rule').click(function (e) {
        e.preventDefault();

        var groupingRuleBlock = {
            mySqlColumns: []
        };

        $('#wdt-constructor-mysql-columns-selected-table tr').each(function () {
            groupingRuleBlock.mySqlColumns = groupingRuleBlock.mySqlColumns.concat($(this).find('td').html());
        });

        var groupingRuleBlockTemplate = $.templates("#wdt-constructor-mysql-grouping-rule-template");
        var groupingRuleHtml = groupingRuleBlockTemplate.render(groupingRuleBlock);
        $(groupingRuleHtml).appendTo('.wdt-constructor-mysql-grouping-rules-block div#wdt-constructor-mysql-grouping-rules').animateFadeIn();
        $('.wdt-constructor-grouping-rule-column').selectpicker();
    });

    /**
     * Delete a grouping rule
     */
    $(document).on('click', '#wdt-constructor-delete-grouping-rule-mysql', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.wdt-constructor-mysql-grouping-rule-block').remove();
    });

    /**
     * Generate a query to the MySQL database and preview it
     */
    function wdtGenerateAndPreviewMySQLQuery() {
        $('.wdt-preload-layer').show();

        constructedTableData.joinRules = [];
        constructedTableData.whereConditions = [];
        constructedTableData.groupingRules = [];

        $('div#wdt-constructor-mysql-tables-relations div.wdt-constructor-mysql-block').each(function () {
            var joinRule = {};
            joinRule.initiatorTable = $(this).find('select.wdt-constructor-relation-initiator-column').data('mysql-table');
            joinRule.initiatorColumn = $(this).find('.wdt-constructor-relation-initiator-column').selectpicker('val');
            joinRule.connectedColumn = $(this).find('.wdt-constructor-relation-connected-column').selectpicker('val');
            joinRule.type = $(this).find('#wdt-constructor-relation-inner-join-' + joinRule.initiatorTable).is(':checked') ? 'inner' : 'left';
            constructedTableData.joinRules.push(joinRule);
        });

        $('div.wdt-constructor-mysql-conditions-block div.wdt-constructor-mysql-where-block').each(function () {
            var whereCondition = {};
            whereCondition.column = $(this).find('.wdt-constructor-where-condition-column').selectpicker('val');
            whereCondition.operator = $(this).find('.wdt-constructor-where-operator').selectpicker('val');
            whereCondition.value = $(this).find('#wdt-constructor-where-value').val();
            constructedTableData.whereConditions.push(whereCondition);
        });

        $('div#wdt-constructor-mysql-grouping-rules div.wdt-constructor-mysql-grouping-rule-block').each(function () {
            constructedTableData.groupingRules.push($(this).find('.wdt-constructor-grouping-rule-column').selectpicker('val'));
        });

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'wpdatatables_generate_mysql_based_query',
                tableData: constructedTableData,
                wdtNonce: wdtNonce
            },
            type: 'post',
            dataType: 'json',
            success: function (data) {
                aceEditor.setValue(data.query);
                constructedTableData.query = data.query;
                $('div.wdt-constructor-preview-wp-table').html(data.preview);
                $('div.wdt-constructor-step[data-step="2-3"]').animateFadeIn();
                nextStepButton.prop('disabled', 'disabled');
                $('.wdt-constructor-create-buttons').show();
                $('.wdt-preload-layer').hide();
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        })

    }

    $('.wdt-constructor-refresh-wp-query').click(function (e) {
        e.preventDefault();

        var inputMethod = $('.wdt-constructor-type-selecter-block .card.selected').data('value');

        $('.wdt-preload-layer').animateFadeIn();
        $.ajax({
            url: ajaxurl,
            data: {
                action: 'wpdatatables_refresh_wp_query_preview',
                query: aceEditor.getValue(),
                connection: inputMethod === 'wp' ? null : constructedTableData.connection,
                wdtNonce: wdtNonce
            },
            type: 'post',
            success: function (data) {
                constructedTableData.query = aceEditor.getValue();
                $('div.wdt-constructor-preview-wp-table').html(data);
                $('.wdt-preload-layer').animateFadeOut();
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        })

    });

    /**
     * Apply syntax highlighter
     */
    if ($('#wdt-constructor-preview-wp-query').length) {
        aceEditor = ace.edit('wdt-constructor-preview-wp-query');
        aceEditor.$blockScrolling = Infinity;
        aceEditor.getSession().setMode("ace/mode/sql");
        aceEditor.setTheme("ace/theme/idle_fingers");
    }

})(jQuery);
