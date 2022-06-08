/**
 * Inline cell edit class
 */
var wdtCustomUploader = null;

var inlineEditClass = function (tableDescription, dataTableOptions, $) {
    var obj = {
        params: {
            tableId: tableDescription.tableId,
            currentCell: '',
            editInputId: 'dt_cell_edit',
            editSelector: '#dt_cell_edit',
            validationPopover: '<div class="wpDataTablesPopover editError"></div>',
            value: '',
            table: '',
            cellData: '',
            cellInfo: '',
            rowId: '',
            columnId: '',
            columnType: '',
            inputType: '',
            columnHeader: '',
            notNull: '',
            code: '',
            expandButton: '',
            valid: true,
            additionalValid: true,
            dateFormat: tableDescription.datepickFormat.replace(/y/g, 'yy').replace(/Y/g, 'yyyy').replace(/M/g, 'mmm'),
            timeFormat: tableDescription.timeFormat
        },
        datePicker: '',
        timePicker: '',
        // Remove a text selection on double click
        removeSelection: function () {
            if (document.selection && document.selection.empty) {
                document.selection.empty();
            } else if (window.getSelection) {
                var sel = window.getSelection();
                sel.removeAllRanges();
            }
        },
        // Measure an error popover position
        setErrorPopoverPosition: function (element, popover) {
            var position = '';
            var offset = element.offset();
            var width = element.outerWidth(true);
            var height = element.outerHeight(true);

            var centerX = offset.left + (width / 2) - (popover.width / 2);
            var centerY = offset.top - (popover.height);
            position = {"top": centerY, "left": centerX};

            return position;
        },
        // Parse a link for edit inputs
        parseLink: function () {
            if (obj.params.value !== null) {
                if (obj.params.value.indexOf('<a ') !== -1) {
                    var $link = $(obj.params.value);
                    if ($.inArray(obj.params.columnType, ['link', 'email']) !== -1) {
                        if ($link.attr('href').indexOf($link.html()) === -1) {
                            obj.params.value = $link.attr('href').replace('mailto:', '') + '||' + $link.html();
                        } else {
                            obj.params.value = $link.html();
                        }
                    } else if ($.inArray(obj.params.columnType, ['icon']) !== -1) {
                        var $linkthumb = $($link.html());
                        if ($link.attr('href').indexOf($link.html()) === -1) {
                            obj.params.value = $link.attr('href') + '||' + $linkthumb.attr('src');
                        } else {
                            obj.params.value = $link.html();
                        }
                    }
                } else if (obj.params.value.indexOf('<img ') !== -1) {
                    $link = $(obj.params.value);
                    obj.params.value = $link.attr('src');
                }
            }
        },
        // Save a cell value function
        saveData: function (val, rowId, columnId) {
            $(tableDescription.selector).addClass('overlayed');
            wpDataTables[obj.params.tableId].fnUpdate(val, rowId, columnId, 0, 0);
            var data = wpDataTables[obj.params.tableId].fnGetData(rowId);
            wpDataTablesFunctions[obj.params.tableId].applyData(data);
            wpDataTablesFunctions[obj.params.tableId].saveTableData(true, false, false);
            $(tableDescription.selector).find('td').removeClass('editing');
        },
        // Validate email and url fields
        fieldValidation: function (type, element) {
            if (obj.params.value != '') {
                if (type == 'email') {
                    var field_valid = wdtValidateEmail(obj.params.value);
                    var message = wpdatatables_frontend_strings.invalid_email;
                } else if (type == 'link') {
                    var field_valid = wdtValidateURL(obj.params.value);
                    var message = wpdatatables_frontend_strings.invalid_link;
                }
                if (!field_valid) {
                    if (!element.closest('td').hasClass('error')) {
                        element.closest('td').addClass('error');
                        $('body').prepend(obj.params.validationPopover);
                        $('.wpDataTablesPopover.editError').html(message);
                        var popoverSize = {
                            "width": $('.wpDataTablesPopover.editError').outerWidth(),
                            "height": $('.wpDataTablesPopover.editError').outerHeight() + 7
                        };
                        $('.wpDataTablesPopover.editError').css(obj.setErrorPopoverPosition(element, popoverSize));
                        setTimeout(function () {
                            $('.wpDataTablesPopover.editError').animateFadeOut('300', function () {
                                $(this).remove();
                                element.closest('td').removeClass('error');
                            });
                        }, 2000);
                    }
                    obj.params.valid = false;
                    obj.params.additionalValid = false;
                } else {
                    $(this).closest('td').removeClass('error');
                    obj.params.valid = true;
                    obj.params.additionalValid = true;
                }
            } else {
                obj.params.valid = true;
                obj.params.additionalValid = true;
            }
        },
        // Validate mandatory fields
        mandatoryFieldValidation: function (element) {
            if (obj.params.value == '' || !obj.params.additionalValid) {
                if (!element.closest('td').hasClass('error')) {
                    element.closest('td').addClass('error');
                    $('body').prepend(obj.params.validationPopover);
                    $('.wpDataTablesPopover.editError').html(wpdatatables_frontend_strings.cannot_be_empty);
                    var popoverSize = {
                        "width": $('.wpDataTablesPopover.editError').outerWidth(),
                        "height": $('.wpDataTablesPopover.editError').outerHeight() + 7
                    };
                    $('.wpDataTablesPopover.editError').css(obj.setErrorPopoverPosition(element, popoverSize));
                    setTimeout(function () {
                        $('.wpDataTablesPopover.editError').animateFadeOut('300', function () {
                            $(this).remove();
                            element.closest('td').removeClass('error');
                        });
                    }, 2000);
                    obj.params.valid = false;
                }
            } else {
                element.closest('td').removeClass('error');
                obj.params.valid = true;
            }
        },
        // Merged save and validation function
        validateAndSave: function ($this) {
            // Validation
            var type_array = new Array('email', 'link');
            if ($.inArray(obj.params.inputType, type_array) !== -1 && obj.params.columnType != 'icon') {
                obj.fieldValidation(obj.params.inputType, $this);
                if (obj.params.notNull) {
                    obj.mandatoryFieldValidation($this);
                }
            } else {
                if (obj.params.notNull) {
                    obj.mandatoryFieldValidation($this);
                }
            }

            // Saving
            if (obj.params.valid) {
                $.when(obj.saveData(obj.params.value, obj.params.rowId, obj.params.columnId)).then(obj.params.currentCell.prepend(obj.params.expandButton));
            }
        },
        FieldTypeMethods: {
            noneditableCell: function () {
                obj.params.currentCell.removeClass('editing');
                var $this = obj.params.currentCell;
                var $value = obj.params.currentCell.html();
                obj.params.currentCell.empty().html(wpdatatables_frontend_strings.cannot_be_edit);

                $(document).click(function () {
                    $this.html($value);
                });
            },
            textCell: function () {
                // Parse a link if there exists
                obj.parseLink();
                // Create an input for a selected cell editing
                var entityMap = {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': '&quot;',
                    "'": '&#39;',
                    "/": '&#x2F;'
                };

                function escapeHtml(string) {
                    return String(string).replace(/[&<>"'\/]/g, function (s) {
                        return entityMap[s];
                    });
                }

                var value = obj.params.value == null ? '' : escapeHtml(obj.params.value);

                obj.params.code = '<input type="text" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" value="' + value + '" />';
                // Append a created input to current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to an inserted input
                $(obj.params.editSelector).focus();

                // Saving event
                $(obj.params.editSelector).blur(function () {
                    obj.params.value = $(this).val();

                    obj.validateAndSave($(this));
                })
            },
            textareaCell: function () {
                // Parse a link if there exists
                obj.parseLink();
                // Replace null to '' and 'br' tag to "\n"
                if (obj.params.value == null) {
                    obj.params.value = '';
                } else if (obj.params.value.indexOf('<br/>') != -1) {
                    obj.params.value = (obj.params.value).replace(/<br\/>/g, "\n");
                }
                // Create an input for a selected cell editing
                obj.params.code = '<textarea data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" rows="3" columns="50">' + obj.params.value + '</textarea>';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to inserted input
                $(obj.params.editSelector).focus();

                // Saving event
                $(obj.params.editSelector).blur(function () {
                    obj.params.value = $(this).val();

                    obj.validateAndSave($(this));
                })
            },
            tinymceCell: function () {
                // Parse a link if there exists
                obj.parseLink();
                // Replace 'br' tag to "\n"
                if (obj.params.value.indexOf('<br/>') != -1) {
                    obj.params.value = (obj.params.value).replace(/<br\/>/g, "\n");
                }
                // Create an input for a selected cell editing
                obj.params.code = '<textarea class="wpdt-tiny-mce" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" rows="3" columns="50">' + obj.params.value + '</textarea>';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // TinyMCE initialization
                tinymce.init({
                    selector: obj.params.editSelector,
                    auto_focus: obj.params.editInputId,
                    menubar: false,
                    plugins: 'link image media lists hr colorpicker fullscreen textcolor code',
                    toolbar: 'undo redo formatselect bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | hr fullscreen | link unlink image | forecolor backcolor removeformat | code',
                    init_instance_callback: function (editor) {
                        editor.on('blur', function (e) {
                            tinymce.triggerSave();
                            obj.params.value = $(obj.params.editSelector).val();

                            obj.validateAndSave($(obj.params.editSelector));
                        });
                    }
                });
            },
            linkCell: function () {
                // Parse a link if there exists
                obj.parseLink();

                // Replace null value from input for a selected cell editing to ''
                if (obj.params.value == null) {
                    obj.params.value = '';
                }

                // Create an input for a selected cell editing
                obj.params.code = '<input type="text" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" value="' + obj.params.value + '" />';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to inserted input
                $(obj.params.editSelector).focus();

                // Saving event
                $(obj.params.editSelector).blur(function () {
                    if (obj.params.columnType == 'icon') {
                        obj.params.value = '<img src="' + $(this).val() + '" />';
                    } else {
                        obj.params.value = $(this).val();
                    }

                    obj.validateAndSave($(this));
                })
            },
            dateCell: function () {
                // Create an input for a selected cell editing
                obj.params.code = '<input type="text" class="wdt-datepicker" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" value="' + obj.params.value + '" />';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to inserted input
                $(obj.params.editSelector).focus();

                // Saving event and fix for double ajax call
                var hasFired = false;
                $(obj.params.editSelector).blur(function () {
                    if(!hasFired){
                        hasFired = true;
                        obj.params.value = $(this).val();
                        obj.validateAndSave($(this));
                    }
                })
            },
            timeCell: function () {
                // Create an input for a selected cell editing
                obj.params.code = '<input type="text" class="wdt-timepicker" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" value="' + obj.params.value + '" />';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to inserted input
                $(obj.params.editSelector).focus();

                // Saving event
                var hasFired = false;
                $(obj.params.editSelector).blur(function () {
                    if(!hasFired){
                        hasFired = true;
                        obj.params.value = $(this).val();
                        obj.validateAndSave($(this));
                    }
                })
            },
            datetimeCell: function () {
                // Create an input for a selected cell editing
                obj.params.code = '<input type="text" class="wdt-datetimepicker" data-input_type="' + obj.params.inputType + '" id="' + obj.params.editInputId + '" value="' + obj.params.value + '" />';

                // Append a created input to a current cell
                obj.params.currentCell.empty().append(obj.params.code);

                // Set focus to inserted input
                $(obj.params.editSelector).focus();

                // Saving event
                var hasFired = false;
                $(obj.params.editSelector).blur(function () {
                    if(!hasFired){
                        hasFired = true;
                        obj.params.value = $(this).val();
                        obj.validateAndSave($(this));
                    }
                })
            },
            selectboxCell: function () {
                // Clone a selectbox from appropriate edit modal's field
                obj.params.code = $('#' + tableDescription.tableId + '_' + obj.params.columnHeader).clone();

                // Append a cloned selectbox to a current cell
                obj.params.currentCell.empty().append(obj.params.code.attr('id', obj.params.editInputId).removeClass('selecter-element'));

                // Set a selected options for a cloned selectbox
                if (obj.params.possibleValuesAjax === -1) {
                    if (obj.params.value !== null) {
                        $.each(obj.params.value.split(", "), function (i, e) {
                            $(obj.params.editSelector + ' option[value="' + e + '"]').prop("selected", true);
                        });
                    }
                }

                // Initialize and open selectpicker
                $(obj.params.editSelector).selectpicker();

                // Add AJAX to selectbox
                if (obj.params.possibleValuesAjax !== -1) {

                    $(obj.params.editSelector).html('');

                    $(obj.params.editSelector).selectpicker('refresh').ajaxSelectPicker({
                        ajax: {
                            url: tableDescription.adminAjaxBaseUrl,
                            method: 'POST',
                            data: {
                                wdtNonce: $('#wdtNonce').val(),
                                action: 'wpdatatables_get_column_possible_values',
                                tableId: tableDescription.tableWpId,
                                originalHeader: obj.params.columnHeader
                            }
                        },
                        cache: false,
                        preprocessData: function (data) {
                            if (obj.params.inputType === 'selectbox') {
                                data.unshift({value: ''});
                            }
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

                    // Load possible values on modal open
                    $(obj.params.editSelector).on('show.bs.select', function (e) {
                        setTimeout(function () {
                            $(obj.params.editSelector).trigger('change').data('AjaxBootstrapSelect').list.cache = {};
                            $(obj.params.editSelector).closest('.bootstrap-select').find('.bs-searchbox .form-control').val('').trigger('keyup');
                        }, 500);

                        // If value is set, append options to selectbox HTML
                        if (obj.params.value !== null) {
                            if (obj.params.inputType === 'multi-selectbox') {
                                var selectedValues = !Array.isArray(obj.params.value) ? obj.params.value.split(', ') : obj.params.value;

                                $.each(selectedValues, function (index, value) {
                                    if (value) {
                                        $(obj.params.editSelector).append('<option selected value="' + value + '">' + value + '</option>');
                                    }
                                });
                            } else {
                                $(obj.params.editSelector).append('<option selected value="' + obj.params.value + '">' + obj.params.value + '</option>');
                            }

                            $(obj.params.editSelector).selectpicker('refresh');
                        }
                    });
                }

                obj.params.currentCell.css({'overflow': 'initial'});
                $(obj.params.editSelector).selectpicker('toggle');

                // Saving event
                if (obj.params.possibleValuesAjax === -1) {
                    if (obj.params.inputType === 'selectbox') {
                        $(obj.params.editSelector).on('changed.bs.select', function () {

                            $(obj.params.editSelector).data('changed', true);

                            obj.params.value = $(this).val();
                            if ($.type(obj.params.value) === 'array') {
                                obj.params.value = obj.params.value.join(', ');
                            }

                            obj.validateAndSave($(this));
                        });
                    }
                }

                $(obj.params.editSelector).on('hidden.bs.select', function () {
                    obj.params.value = $('#' + obj.params.editInputId).val();
                    if ($.type(obj.params.value) === 'array') {
                        obj.params.value = obj.params.value.join(', ');
                    }

                    obj.validateAndSave($(this));
                })

            },
            attachmentCell: function () {
                // Clear and create necessary variables
                obj.params.value = '';
                var src = '';

                if (obj.params.currentCell.find('img').length > 0) {
                    if (obj.params.currentCell.find('img').attr('src').length > 0) {
                        src = obj.params.currentCell.find('img').attr('src');
                    }
                } else if (obj.params.currentCell.find('a').length > 0) {
                    if (obj.params.currentCell.find('a').attr('href').length > 0) {
                        src = obj.params.currentCell.find('a').attr('href');
                    }
                } else {
                    src = obj.params.currentCell.html();
                }

                var fileName = src.substring(src.lastIndexOf('/') + 1);

                // Create a control buttons and a file information block
                obj.params.code =
                    '<span class="btn btn-primary m-r-10 fileupload_row_edit_' + tableDescription.tableId + '" ' +
                    'id="row_edit_' + tableDescription.tableId + '_sets_button" ' +
                    'data-column_type="icon" ' +
                    'data-rel_input="row_edit_' + tableDescription.tableId + '_sets">' +
                    '<span class="fileinput-new">' + wpdatatables_frontend_strings.selectFileAttachment + '</span>' +
                    '<span class="fileinput-exists">' + wpdatatables_frontend_strings.changeFileAttachment + '</span>' +
                    '<input type="hidden" ' +
                    'id="row_edit_' + tableDescription.tableId + '_sets" ' +
                    'data-key="sets" ' +
                    'data-input_type="attachment" ' +
                    'value="' + src + '" ' +
                    'class="editDialogInput" ' +
                    '/>' +
                    '</span>' +
                    '<button class="btn btn-primary fileinput-save m-r-10">' + wpdatatables_frontend_strings.saveFileAttachment + '</button>' +
                    '<button class="btn btn-danger fileinput-exists wdt-detach-attachment-file-inline m-r-10" data-dismiss="fileinput">' + wpdatatables_frontend_strings.removeFileAttachment + '</button>' +
                    '<span class="fileinput-filename"></span>';

                // Append a created container to a current cell
                obj.params.currentCell.empty().append(obj.params.code);
                obj.fileUploadInit('row_edit_' + tableDescription.tableId);

                var $inputElement = $('#row_edit_' + tableDescription.tableId + '_sets');
                if (fileName != '') {
                    $inputElement.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                    $inputElement.parent().parent().find('.fileinput-filename').text(fileName);
                } else {
                    $inputElement.parent().parent().removeClass('fileinput-exists').addClass('fileinput-new');
                    $inputElement.parent().parent().find('.fileinput-filename').text('');
                }

                // Saving event
                $('.fileinput-save').click(function () {
                    obj.params.value = $('#row_edit_' + tableDescription.tableId + '_sets').val();
                    obj.params.value = '<a href="' + obj.params.value + '"></a>';
                    $('#row_edit_' + tableDescription.tableId + '_sets').closest('td').empty().html(obj.params.value);

                    obj.validateAndSave($(this));
                });
            }

        },
        // Apply fileuploaders
        fileUploadInit: function (selector) {
            if ($('.fileupload_' + selector).length) {

                var attachment = null;
                // Extend the wp.media object
                wdtCustomUploader = wp.media({
                    title: wpdatatables_frontend_strings.select_upload_file,
                    button: {
                        text: wpdatatables_frontend_strings.choose_file
                    },
                    multiple: false
                });

                $('.fileupload_' + selector).click(function (e) {
                    e.preventDefault();
                    var $button = $(this);
                    var $relInput = $('#' + $button.data('rel_input'));
                    if (obj.params.columnType == 'icon') {
                        wdtCustomUploader = wp.media({
                            title: wpdatatables_frontend_strings.select_upload_file,
                            button: {
                                text: wpdatatables_frontend_strings.choose_file
                            },
                            multiple: false,
                            library: {
                                type: 'image'
                            }
                        });
                        wdtCustomUploader.off('select').on('select', function () {
                            attachment = wdtCustomUploader.state().get('selection').first().toJSON();

                            var val = attachment.url;

                            $relInput.parent().parent().parent().find('.fileinput-preview').html('<img src=' + val + '>');
                            $relInput.parent().parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                            $relInput.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');

                            if (attachment.sizes.thumbnail) {
                                val = attachment.sizes.thumbnail.url + '||' + val;
                            }

                            $relInput.val(val);
                        });
                    } else {
                        // For columns that are not image column type, grab the URL and set it as the text field's value
                        wdtCustomUploader.off('select').on('select', function () {
                            attachment = wdtCustomUploader.state().get('selection').first().toJSON();
                            $relInput.val(attachment.url);
                            $relInput.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                            $relInput.parent().parent().find('.fileinput-filename').text(attachment.filename);
                        });
                    }
                    // Open the uploader dialog
                    wdtCustomUploader.open();

                });

                /**
                 * Detached the chosen attachment
                 */
                $('.wdt-detach-attachment-file-inline').click(function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if ($(this).parent().find('span.fileupload-' + tableDescription.tableId).data('column_type') == 'icon') {
                        $(this).parent().find('input.editDialogInput').val('');
                        $(this).parent().parent().find('.fileinput-preview').html('');
                        $(this).parent().parent().removeClass('fileinput-exists').addClass('fileinput-new');
                        $(this).parent().removeClass('fileinput-exists').addClass('fileinput-new');
                    } else {
                        $(this).parent().find('input.editDialogInput').val('');
                        $(this).parent().find('.fileinput-filename').text('');
                        $(this).parent().removeClass('fileinput-exists').addClass('fileinput-new');
                    }
                });

            }
        },

        // Cell editing on double click event
        bindClickEvent: function () {
            $(tableDescription.selector + ' tbody').on('dblclick', 'td', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                // Prevent click event if current element is input, has invalid value or already is edited
                var target = e.target || e.srcElement;
                var elementName = target.tagName.toLowerCase();
                if (elementName == 'input' || elementName == 'span' || !obj.params.valid || $(this).hasClass('editing')) {
                    return false;
                }

                // Remove a text selection inside a cell
                obj.removeSelection();

                // Add editing class
                if (!$(this).hasClass("dataTables_empty")) {
                    $(this).addClass('editing');
                }

                // Set variables
                obj.params.table = $(tableDescription.selector).DataTable();
                if (wpDataTables[obj.params.tableId].fnSettings().fnRecordsTotal() == 0) {
                    return false;
                }
                obj.params.cellData = obj.params.table.cell(this).data();
                obj.params.cellInfo = obj.params.table.cell(this).index();
                obj.params.columnId = obj.params.cellInfo.column;
                obj.params.columnType = wpDataTables[obj.params.tableId].fnSettings().aoColumns[obj.params.columnId].wdtType;
                obj.params.rowId = obj.params.cellInfo.row;
                obj.params.inputType = dataTableOptions.aoColumnDefs[obj.params.columnId]['InputType'];
                obj.params.columnHeader = dataTableOptions.aoColumnDefs[obj.params.columnId]['origHeader'];
                obj.params.notNull = dataTableOptions.aoColumnDefs[obj.params.columnId]['notNull'];
                obj.params.value = obj.params.cellData;
                obj.params.currentCell = $(this);
                obj.params.possibleValuesAjax = tableDescription.advancedEditingOptions.aoColumns[obj.params.columnId]['possibleValuesAjax'];

                // If a coulumn is resposive than record an expand button to variable
                if ($(this).children('.responsiveExpander') != '') {
                    obj.params.expandButton = $(this).children('.responsiveExpander');
                }

                // Cell editing depending on the type
                switch (obj.params.inputType) {

                    // Cell prohibited for editing
                    case 'none':
                        obj.FieldTypeMethods.noneditableCell();
                        break;

                    // Plain text cell
                    case 'text':
                        obj.FieldTypeMethods.textCell();
                        break;

                    // Multiple lines cell
                    case 'textarea':
                        obj.FieldTypeMethods.textareaCell();
                        break;

                    // TinyMCE
                    case 'mce-editor':
                        obj.FieldTypeMethods.tinymceCell();
                        break;

                    // Email and url cell
                    case 'link':
                    case 'email':
                        obj.FieldTypeMethods.linkCell();
                        break;

                    // Datepicker cell
                    case 'date':
                        obj.FieldTypeMethods.dateCell();
                        break;

                    // Time cell
                    case 'time':
                        obj.FieldTypeMethods.timeCell();
                        break;

                    // Datetime cell
                    case 'datetime':
                        obj.FieldTypeMethods.datetimeCell();
                        break;

                    // Single and multi selectbox cells
                    case 'multi-selectbox':
                    case 'selectbox':
                        obj.FieldTypeMethods.selectboxCell();
                        break;

                    // Attachment cell
                    case 'attachment':
                        obj.FieldTypeMethods.attachmentCell();
                        break;
                }

            });
        }
    };

    obj.bindClickEvent();

};
