/**
 * Created by Milos Roksandic on 12.1.16..
 */

(function(Handsontable, $){
    //this is used for setting default values in new rows for every cell that has it.
    var baseBeginEditing = Handsontable.editors.BaseEditor.prototype.beginEditing;
    Handsontable.editors.BaseEditor.prototype.beginEditing = function(initialValue, event) {
        var settings = this.instance.getSettings();

        if ( settings.wpDataTablesExcelPlugin ) {
            if (typeof initialValue == 'undefined' && !settings.readOnly) {
                var idColProp = settings.idColumnKey;
                var idColVal = this.instance.getDataAtRowProp(this.row, idColProp);
                var defaultValue = this.cellProperties.defaultValue;

                if (!idColVal && defaultValue) {
                    initialValue = defaultValue;
                }
            }
        }

        baseBeginEditing.apply(this, arguments);
    };

    //this is fix for valid cells that had invalid background after cancelling edition
    var baseCancelChanges = Handsontable.editors.BaseEditor.prototype.cancelChanges;
    Handsontable.editors.BaseEditor.prototype.cancelChanges = function() {
        var settings = this.instance.getCellMeta(this.row, this.col);

        if ( settings.wpDataTablesExcelPlugin ) {
            //removing class that indicates that this editor has invalid value, also removing title tag because of tooltip.
            var $editorElement = $(document.activeElement).closest('#' + this.instance.rootElement.id +' > div.invalid_editor_value')
                .removeClass('invalid_editor_value');

            //closing tooltip if previously not closed.
            if( $editorElement.length > 0 && $editorElement.next('div.tooltip:visible').length ) {
                $editorElement.tooltip('destroy');
            }

            var validator = this.instance.getCellValidator(this.row, this.col);
            var value = this.instance.getDataAtCell(this.row, this.col);

            //getting cell validator and making sure that value is actually valid, and if so css class that marks table cell as invalid is removed.
            if ( validator ) {
                var isValid = false;
                if (typeof validator == 'function') {
                    validator.call(settings, value, function(val){
                        return isValid = val;
                    });
                } else if (validator instanceof RegExp) {
                    isValid = validator.test(value);
                }

                if (isValid) {
                    var TD = this.instance.getCell(this.row, this.col);
                    var $td = $(TD);

                    var invalidCellClassName = settings.invalidCellClassName;

                    if ($td.hasClass(invalidCellClassName)) {
                        $td.removeClass(invalidCellClassName);
                    }
                }
            }
        }


        baseCancelChanges.apply(this, arguments);
    };

    /**
     * TEXT MULTILINE EDITOR DEFINITION.
     */
    function initWdtMultilineEditor() {
        var ExcelMultilineEditor = Handsontable.editors.TextEditor.prototype.extend();

        ExcelMultilineEditor.prototype.bindEvents = function() {
            //Preventing default editing behaviour when Enter Key pressed, allowing new line character.
            this.eventManager.addEventListener(this.TEXTAREA, 'keydown', function(event) {
                if ( event.which == Handsontable.helper.KEY_CODES.ENTER ) {
                    Handsontable.Dom.stopImmediatePropagation(event);
                }
            });
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelMultilineEditor = ExcelMultilineEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.text_multiline', ExcelMultilineEditor);
    }

    /**
     * CUSTOM DATE EDITOR DEFINITION.
     */
    function initWdtDateEditor() {
        var ExcelDateEditor = Handsontable.editors.TextEditor.prototype.extend();

        ExcelDateEditor.prototype.init = function() {
            Handsontable.editors.TextEditor.prototype.init.apply(this, arguments);

            var table_settings = this.instance.getSettings();
            this.time_format = table_settings.timeFormat;
            var $__9 = this;
            this.instance.addHook('afterDestroy', (function() {
                $__9.parentDestroyed = true;
                $__9.destroyElements();
            }));
        };

        ExcelDateEditor.prototype.createElements = function(){
            Handsontable.editors.TextEditor.prototype.createElements.apply(this, arguments);
             // Create password input and update relevant properties
            this.TEXTAREA = document.createElement('input');
            this.TEXTAREA.className = 'wdt-datepicker';
            this.textareaStyle = this.TEXTAREA.style;
            this.textareaStyle.width = 0;
            this.textareaStyle.height = 0;

            // Replace textarea with password input
            Handsontable.Dom.empty(this.TEXTAREA_PARENT);
            this.TEXTAREA_PARENT.appendChild(this.TEXTAREA);
        };

        //this event is needed because of handsontable bug, when entered invalid data, escape was not calling finish editing function
        ExcelDateEditor.prototype.onBeforeKeyDown = function(event) {
            switch (event.keyCode) {
                case Handsontable.helper.KEY_CODES.ESCAPE:
                    var editor = this.getActiveEditor();
                    editor.finishEditing( true );
                    break;
                case Handsontable.helper.KEY_CODES.ARROW_DOWN:
                case Handsontable.helper.KEY_CODES.ARROW_UP:
                    break;
            }
        };

        ExcelDateEditor.prototype.beginEditing = function(e) {
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        //this is a fix for bug that when invalid time is tried to be saved, date picker was unable to appear.
        ExcelDateEditor.prototype.discardEditor = function(validationResult) {
            Handsontable.editors.TextEditor.prototype.discardEditor.apply(this, arguments);

            var hotInstance = this.instance;
            var allowInvalid = hotInstance.getCellMeta(this.row, this.col).allowInvalid;

            if( !allowInvalid && !validationResult ) {
                if( !this.$timePicker.$node.is(':visible') ) {
                    this.$timePicker.open();
                }
            }
        };

        ExcelDateEditor.prototype.open = function() {
            this.instance.addHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.open.apply(this, arguments);
        };

        ExcelDateEditor.prototype.beginEditing = function(){
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        ExcelDateEditor.prototype.focus = function( event ){
            Handsontable.editors.DateEditor.prototype.focus.apply(this, arguments);
        }

        ExcelDateEditor.prototype.close = function() {
            this.instance.removeHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.close.apply(this, arguments);
        };

        ExcelDateEditor.prototype.finishEditing = function() {
            var isCancelled = arguments[0] !== (void 0) ? arguments[0] : false;
            var ctrlDown = arguments[1] !== (void 0) ? arguments[1] : false;
            if (isCancelled) {
                var value = this.originalValue;
                if (value !== void 0) {
                    this.setValue(value);
                }
            }
            Handsontable.editors.TextEditor.prototype.finishEditing.apply(this, arguments);
        };

        ExcelDateEditor.prototype.prepare = function( row, col, prop, td, originalValue, cellProperties ) {
            if( !originalValue || originalValue == '0000-00-00' ) {
                originalValue = '';
            }

            if ( originalValue && !this.same_display_and_edit_format ) {
                var table_settings = this.instance.getSettings();
                //values are converted from database format to desired display format
                originalValue = moment(originalValue, table_settings.dataSourceDateFormat).format(table_settings.displayDateFormat);
            }

            // Remember to invoke parent's method
            Handsontable.editors.DateEditor.prototype.prepare.apply(this, arguments);
        };

        ExcelDateEditor.prototype.saveValue = function( val, ctrlDown ) {
                        if ( val[0][0] && !this.same_display_and_edit_format ) {
                var table_settings = this.instance.getSettings();
                //values are converted from desired display format to database format in order to save properly
                val[0][0] = moment(val[0][0], table_settings.displayDateFormat).format(table_settings.dataSourceDateFormat);
            }

            // Remember to invoke parent's method
            Handsontable.editors.DateEditor.prototype.saveValue.apply(this, arguments);
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelTimeEditor = ExcelDateEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.date', ExcelDateEditor);
    }

    /**
     * DATETIME EDITOR DEFINITION
     */
    function initWdtDateTimeEditor() {
        var ExcelDateTimeEditor = Handsontable.editors.TextEditor.prototype.extend();

        ExcelDateTimeEditor.prototype.init = function() {
            Handsontable.editors.TextEditor.prototype.init.apply(this, arguments);

            var table_settings = this.instance.getSettings();
            this.time_format = table_settings.timeFormat;
            var $__9 = this;
            this.instance.addHook('afterDestroy', (function() {
                $__9.parentDestroyed = true;
                $__9.destroyElements();
            }));
        };

        ExcelDateTimeEditor.prototype.createElements = function(){
            Handsontable.editors.TextEditor.prototype.createElements.apply(this, arguments);
             // Create password input and update relevant properties
            this.TEXTAREA = document.createElement('input');
            this.TEXTAREA.className = 'wdt-datetimepicker';
            this.textareaStyle = this.TEXTAREA.style;
            this.textareaStyle.width = 0;
            this.textareaStyle.height = 0;

            // Replace textarea with password input
            Handsontable.Dom.empty(this.TEXTAREA_PARENT);
            this.TEXTAREA_PARENT.appendChild(this.TEXTAREA);
        };

        //this event is needed because of handsontable bug, when entered invalid data, escape was not calling finish editing function
        ExcelDateTimeEditor.prototype.onBeforeKeyDown = function(event) {
            switch (event.keyCode) {
                case Handsontable.helper.KEY_CODES.ESCAPE:
                    var editor = this.getActiveEditor();
                    editor.finishEditing( true );
                    break;
                case Handsontable.helper.KEY_CODES.ARROW_DOWN:
                case Handsontable.helper.KEY_CODES.ARROW_UP:
                    break;
            }
        };

        ExcelDateTimeEditor.prototype.beginEditing = function(e) {
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        //this is a fix for bug that when invalid time is tried to be saved, date picker was unable to appear.
        ExcelDateTimeEditor.prototype.discardEditor = function(validationResult) {
            Handsontable.editors.TextEditor.prototype.discardEditor.apply(this, arguments);

            var hotInstance = this.instance;
            var allowInvalid = hotInstance.getCellMeta(this.row, this.col).allowInvalid;

            if( !allowInvalid && !validationResult ) {
                if( !this.$timePicker.$node.is(':visible') ) {
                    this.$timePicker.open();
                }
            }
        };

        ExcelDateTimeEditor.prototype.open = function() {
            this.instance.addHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.open.apply(this, arguments);
        };

        ExcelDateTimeEditor.prototype.beginEditing = function(){
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        ExcelDateTimeEditor.prototype.focus = function( event ){
            Handsontable.editors.DateEditor.prototype.focus.apply(this, arguments);
        }

        ExcelDateTimeEditor.prototype.close = function() {
            this.instance.removeHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.close.apply(this, arguments);
        };

        ExcelDateTimeEditor.prototype.finishEditing = function() {
            var isCancelled = arguments[0] !== (void 0) ? arguments[0] : false;
            var ctrlDown = arguments[1] !== (void 0) ? arguments[1] : false;
            if (isCancelled) {
                var value = this.originalValue;
                if (value !== void 0) {
                    this.setValue(value);
                }
            }
            Handsontable.editors.TextEditor.prototype.finishEditing.apply(this, arguments);
        };

        ExcelDateTimeEditor.prototype.prepare = function( row, col, prop, td, originalValue, cellProperties ) {
            if( !originalValue || originalValue == '0000-00-00 00:00:00' ) {
                originalValue = '';
            }

            if ( originalValue && !this.same_display_and_edit_format ) {
                var table_settings = this.instance.getSettings();
                //values are converted from database format to desired display format
                originalValue = moment( originalValue, table_settings.dataSourceDateFormat+' hh:mm:ss')
                    .format( table_settings.displayDateFormat+' '+table_settings.momentTimeFormat );
            }

            // Remember to invoke parent's method
            Handsontable.editors.DateEditor.prototype.prepare.apply(this, arguments);
        };

        ExcelDateTimeEditor.prototype.saveValue = function( val, ctrlDown ) {
            if ( val[0][0] && !this.same_display_and_edit_format ) {
                var table_settings = this.instance.getSettings();
                //values are converted from desired display format to database format in order to save properly
                val[0][0] = moment(val[0][0], table_settings.displayDateFormat+' '+table_settings.momentTimeFormat)
                    .format(table_settings.dataSourceDateFormat+' H:mm:ss');
            }
            // Remember to invoke parent's method
            Handsontable.editors.DateEditor.prototype.saveValue.apply(this, arguments);
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelTimeEditor = ExcelDateTimeEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.datetime', ExcelDateTimeEditor);
    }

    /**
     * TIME EDITOR DEFINITION
     */
    function initWdtTimeEditor() {
        var ExcelTimeEditor = Handsontable.editors.TextEditor.prototype.extend();

        ExcelTimeEditor.prototype.init = function() {
            Handsontable.editors.TextEditor.prototype.init.apply(this, arguments);

            var table_settings = this.instance.getSettings();
            this.time_format = table_settings.timeFormat;
            var $__9 = this;
            this.instance.addHook('afterDestroy', (function() {
                $__9.parentDestroyed = true;
                $__9.destroyElements();
            }));
        };

        ExcelTimeEditor.prototype.createElements = function(){
            Handsontable.editors.TextEditor.prototype.createElements.apply(this, arguments);
             // Create password input and update relevant properties
            this.TEXTAREA = document.createElement('input');
            this.TEXTAREA.className = 'wdt-timepicker';
            this.textareaStyle = this.TEXTAREA.style;
            this.textareaStyle.width = 0;
            this.textareaStyle.height = 0;

            // Replace textarea with password input
            Handsontable.Dom.empty(this.TEXTAREA_PARENT);
            this.TEXTAREA_PARENT.appendChild(this.TEXTAREA);
        };

        //this event is needed because of handsontable bug, when entered invalid data, escape was not calling finish editing function
        ExcelTimeEditor.prototype.onBeforeKeyDown = function(event) {
            switch (event.keyCode) {
                case Handsontable.helper.KEY_CODES.ESCAPE:
                    var editor = this.getActiveEditor();
                    editor.finishEditing( true );
                    break;
                case Handsontable.helper.KEY_CODES.ARROW_DOWN:
                case Handsontable.helper.KEY_CODES.ARROW_UP:
                    break;
            }
        };

        ExcelTimeEditor.prototype.beginEditing = function(e) {
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        //this is a fix for bug that when invalid time is tried to be saved, date picker was unable to appear.
        ExcelTimeEditor.prototype.discardEditor = function(validationResult) {
            Handsontable.editors.TextEditor.prototype.discardEditor.apply(this, arguments);

            var hotInstance = this.instance;
            var allowInvalid = hotInstance.getCellMeta(this.row, this.col).allowInvalid;

            if( !allowInvalid && !validationResult ) {
                if( !this.$timePicker.$node.is(':visible') ) {
                    this.$timePicker.open();
                }
            }
        };

        ExcelTimeEditor.prototype.open = function() {
            this.instance.addHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.open.apply(this, arguments);
        };

        ExcelTimeEditor.prototype.beginEditing = function(){
            Handsontable.editors.TextEditor.prototype.beginEditing.apply(this, arguments);
        }

        ExcelTimeEditor.prototype.focus = function( event ){
            Handsontable.editors.DateEditor.prototype.focus.apply(this, arguments);
        }

        ExcelTimeEditor.prototype.close = function() {
            this.instance.removeHook('beforeKeyDown', this.onBeforeKeyDown);
            Handsontable.editors.TextEditor.prototype.close.apply(this, arguments);
        };

        ExcelTimeEditor.prototype.finishEditing = function() {
            var isCancelled = arguments[0] !== (void 0) ? arguments[0] : false;
            var ctrlDown = arguments[1] !== (void 0) ? arguments[1] : false;
            if (isCancelled) {
                var value = this.originalValue;
                if (value !== void 0) {
                    this.setValue(value);
                }
            }
            Handsontable.editors.TextEditor.prototype.finishEditing.apply(this, arguments);
        };

        ExcelTimeEditor.prototype.prepare = function( row, col, prop, td, originalValue, cellProperties ) {
            if( !originalValue ) {
                originalValue = '';
            }else{
                var table_settings = this.instance.getSettings();
                originalValue = moment( originalValue, 'H:mm:ss')
                                    .format( table_settings.momentTimeFormat );
            }

            // Remember to invoke parent's method
            Handsontable.editors.TextEditor.prototype.prepare.apply(this, arguments);
        };

        ExcelTimeEditor.prototype.saveValue = function( val, ctrlDown ) {
            // Format time back to MySQL format
            if( val[0][0] ){
                var table_settings = this.instance.getSettings();
                val[0][0] = moment( val[0][0], table_settings.momentTimeFormat )
                                    .format('H:mm:ss');
            }

            // Remember to invoke parent's method
            Handsontable.editors.TextEditor.prototype.saveValue.apply(this, arguments);
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelTimeEditor = ExcelTimeEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.time', ExcelTimeEditor);
    }

    /**
     * TinyMCE EDITOR DEFINITION
     */

    /**
     * ATTACHMENT EDITOR DEFINITION.
     */
    function initWdtAttachmentEditor() {
        var ExcelAttachmentEditor = Handsontable.editors.BaseEditor.prototype.extend();

        ExcelAttachmentEditor.prototype.init = function() {
            //this is wordpress media library for handling file uploads.
            this.wdtCustomUploader = wp.media({
                title: wpdatatables_frontend_strings.select_upload_file,
                button: {
                    text: wpdatatables_frontend_strings.choose_file
                },
                multiple: false
            });

            var editor = this;
            var htInstance = this.instance;
            this.wdtCustomUploader.on('select', function () {
                var attachment = editor.wdtCustomUploader.state().get('selection').first().toJSON();
                var cellRenderer = htInstance.getCellRenderer(editor.row,editor.col).name;
                var value = attachment.url;

                //if file is for image column(we presume it is image file), there is a check if thumbnail exists and with and height properties are saved as a query string part of image url.
                if( cellRenderer == 'wdtImageRenderer' ) {
                    if( attachment.type != 'image' ) {
                        value = '';
                    } else {
                        var imgUri = new URI( value );
                        imgUri.setQuery({ img_width: attachment.width, img_height: attachment.height});

                        value = imgUri.toString();

                        if( attachment.sizes.thumbnail ) {
                            var thumbUri = new URI( attachment.sizes.thumbnail.url );
                            thumbUri.setQuery({ img_width: attachment.sizes.thumbnail.width, img_height: attachment.sizes.thumbnail.height});
                            value = thumbUri.toString() + '||' + value;
                        }
                    }
                }

                //saving value
                htInstance.setDataAtCell( editor.row, editor.col, value );
            });

            $browseButton = $('<button class="button-primary">' + wpdatatables_frontend_strings.browse_file + '</button>');
            $($browseButton).click(function (e) {
                e.preventDefault();

                // Open the uploader dialog
                htInstance.getActiveEditor().wdtCustomUploader.open();
            });

            this.BROWSE_BUTTON = $browseButton[0];
            this.INPUT_FILE_URL = $('<input type="hidden" />')[0];
            this.UPLOADED_FILES_ELEMENTS = document.createElement('p');


            $editorCnt = $('<div class="wdtAttachmentEditorHolder" ></div>')
                .append( this.UPLOADED_FILES_ELEMENTS, this.BROWSE_BUTTON, this.INPUT_FILE_URL )
                .hide();

            this.EDITOR_CONTENT = $editorCnt[0];
            this.editorContainerStyle = this.EDITOR_CONTENT.style;

            this.instance.rootElement.appendChild(this.EDITOR_CONTENT);
        };

        ExcelAttachmentEditor.prototype.getValue = function() {
            return this.INPUT_FILE_URL.value;
        };

        ExcelAttachmentEditor.prototype.setValue = function( newValue ) {
            this.INPUT_FILE_URL.value = newValue;

            var uploaded_file = '';
            var htInstance = this.instance;

            if ( newValue != '' ) {
                var link = newValue.split('||')[0];
                var uploaded_file;
                //if( !/\.(jpg|jpeg|png|gif)$/i.test( link ) ) {
                //    //TODO: add empty file icon if not image
                //}

                if ( htInstance.getCellRenderer(this.row,this.col).name == 'wdtImageRenderer' ) {
                    uploaded_file = $('<img src="'+link+'" />').css({'max-width':'100px', 'max-height':'50px'})[0].outerHTML;
                } else {
                    //only file name is displayed
                    uploaded_file = newValue.split('/').pop();
                }

                uploaded_file += '<span class="delete_file">[<a href="#">' + wpdatatables_frontend_strings.detach_file + '</a>]</span>';
            }

            $(this.UPLOADED_FILES_ELEMENTS).html( uploaded_file );

            $( this.UPLOADED_FILES_ELEMENTS ).on('click', 'a', function( e ) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var editor = htInstance.getActiveEditor();
                editor.setValue('', false);
                editor.finishEditing(false, false);
            });
        };

        ExcelAttachmentEditor.prototype.open = function() {
            this.refreshDimensions();
        };

        ExcelAttachmentEditor.prototype.close = function() {
            this.editorContainerStyle.display = 'none';
        };

        ExcelAttachmentEditor.prototype.focus = function() {
            this.BROWSE_BUTTON.focus();
        };

        //this is taken from TextEditor.prototype.getEditedCell and modified
        //this is probably needed because of fixed headers, columns, rows, etc...
        ExcelAttachmentEditor.prototype.getEditedCell = function() {
            var editorSection = this.checkEditorSection(),
                editedCell;
            switch (editorSection) {
                case 'top':
                    editedCell = this.instance.view.wt.wtOverlays.topOverlay.clone.wtTable.getCell({
                        row: this.row,
                        col: this.col
                    });
                    this.editorContainerStyle.zIndex = 101;
                    break;
                case 'top-left-corner':
                    editedCell = this.instance.view.wt.wtOverlays.topLeftCornerOverlay.clone.wtTable.getCell({
                        row: this.row,
                        col: this.col
                    });
                    this.editorContainerStyle.zIndex = 103;
                    break;
                case 'bottom-left-corner':
                    editedCell = this.instance.view.wt.wtOverlays.bottomLeftCornerOverlay.clone.wtTable.getCell({
                        row: this.row,
                        col: this.col
                    });
                    this.editorContainerStyle.zIndex = 103;
                    break;
                case 'left':
                    editedCell = this.instance.view.wt.wtOverlays.leftOverlay.clone.wtTable.getCell({
                        row: this.row,
                        col: this.col
                    });
                    this.editorContainerStyle.zIndex = 102;
                    break;
                case 'bottom':
                    editedCell = this.instance.view.wt.wtOverlays.bottomOverlay.clone.wtTable.getCell({
                        row: this.row,
                        col: this.col
                    });
                    this.editorContainerStyle.zIndex = 102;
                    break;
                default:
                    editedCell = this.instance.getCell(this.row, this.col);
                    this.editorContainerStyle.zIndex = '';
                    break;
            }
            return editedCell != -1 && editedCell != -2 ? editedCell : void 0;
        };

        //this is taken from TextEditor.prototype.refreshDimensions and modified
        //this is probably needed because of fixed headers, columns, rows, etc...
        ExcelAttachmentEditor.prototype.refreshDimensions = function() {
            if (this.state !== Handsontable.EditorState.EDITING) {
                return;
            }
            this.TD = this.getEditedCell();
            if (!this.TD) {
                this.close(true);
                return;
            }
            var currentOffset = Handsontable.Dom.offset(this.TD),
                containerOffset = Handsontable.Dom.offset(this.instance.rootElement),
                scrollableContainer = Handsontable.Dom.getScrollableElement(this.TD),
                totalRowsCount = this.instance.countRows(),
                editTop = currentOffset.top - containerOffset.top - 1 - (scrollableContainer.scrollTop || 0),
                editLeft = currentOffset.left - containerOffset.left - 1 - (scrollableContainer.scrollLeft || 0),
                settings = this.instance.getSettings(),
                colHeadersCount = settings.colHeaders ? 1 : 0,
                editorSection = this.checkEditorSection(),
                //backgroundColor = this.TD.style.backgroundColor,
                cssTransformOffset;
            switch (editorSection) {
                case 'top':
                    cssTransformOffset = Handsontable.Dom.getCssTransform(this.instance.view.wt.wtOverlays.topOverlay.clone.wtTable.holder.parentNode);
                    break;
                case 'left':
                    cssTransformOffset = Handsontable.Dom.getCssTransform(this.instance.view.wt.wtOverlays.leftOverlay.clone.wtTable.holder.parentNode);
                    break;
                case 'top-left-corner':
                    cssTransformOffset = Handsontable.Dom.getCssTransform(this.instance.view.wt.wtOverlays.topLeftCornerOverlay.clone.wtTable.holder.parentNode);
                    break;
                case 'bottom-left-corner':
                    cssTransformOffset = Handsontable.Dom.getCssTransform(this.instance.view.wt.wtOverlays.bottomLeftCornerOverlay.clone.wtTable.holder.parentNode);
                    break;
                case 'bottom':
                    cssTransformOffset = Handsontable.Dom.getCssTransform(this.instance.view.wt.wtOverlays.bottomOverlay.clone.wtTable.holder.parentNode);
                    break;
            }
            if (colHeadersCount && this.instance.getSelected()[0] === 0 || (settings.fixedRowsBottom && this.instance.getSelected()[0] === totalRowsCount - settings.fixedRowsBottom)) {
                editTop += 1;
            }
            if (this.instance.getSelected()[1] === 0) {
                editLeft += 1;
            }
            if (cssTransformOffset && cssTransformOffset != -1) {
                this.editorContainerStyle[cssTransformOffset[0]] = cssTransformOffset[1];
            } else {
                Handsontable.Dom.resetCssTransform(this.EDITOR_CONTENT);
            }
            this.editorContainerStyle.top = editTop + 'px';
            this.editorContainerStyle.left = editLeft + 'px';
            var cellTopOffset = this.TD.offsetTop - this.instance.view.wt.wtOverlays.topOverlay.getScrollPosition(),
                cellLeftOffset = this.TD.offsetLeft - this.instance.view.wt.wtOverlays.leftOverlay.getScrollPosition();
            var width = Handsontable.Dom.innerWidth(this.TD) - 8;//?
            var maxWidth = this.instance.view.maximumVisibleElementWidth(cellLeftOffset) - 9;//?
            var height = this.TD.scrollHeight + 1;//?
            //var maxHeight = Math.max(this.instance.view.maximumVisibleElementHeight(cellTopOffset) - 2, 23);
            //this.editorContainerStyle.backgroundColor = backgroundColor ? backgroundColor : Handsontable.Dom.getComputedStyle(this.EDITOR_CONTENT).backgroundColor;

            //this.editorContainerStyle.height = height + 'px';
            this.editorContainerStyle.width = width + 'px';
            this.editorContainerStyle.maxWidth = maxWidth + 'px';

            this.editorContainerStyle.display = 'block';
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelAttachmentEditor = ExcelAttachmentEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.attachment', ExcelAttachmentEditor);
    }

    /**
     * CUSTOM DROPDOWN EDITOR DEFINITION.
     */
    function initWdtDropdownEditor() {
        var ExcelDropdownEditor = Handsontable.editors.DropdownEditor.prototype.extend();

        ExcelDropdownEditor.confirmWdtDropdownCellValidate = function (isValid, value, row, prop, source){
            var col_index = this.propToCol( prop );
            var cell_meta = this.getCellMeta( row, col_index);

            //for wdt.dropdown column type, validator is defined by default that checks if selected value is in possible values list.
            //also this plugin has functionality that additional validator also can be defined via 'cell_validator' property.
            //it is automatically assigned depending of the renderer type(e.g. numeric or link).
            //this validator is called here for checking if data is in good format.
            if( isValid && typeof cell_meta.cell_validator == 'function') {
                var valid_value = false;
                cell_meta.cell_validator( value, function(valid){return valid_value = valid;});

                return valid_value;
            } else {
                return isValid;
            }
        };

        ExcelDropdownEditor.prototype.open = function() {
            Handsontable.editors.DropdownEditor.prototype.open.apply(this, arguments);

            this.instance.addHook('afterValidate', ExcelDropdownEditor.confirmWdtDropdownCellValidate);
        };

        ExcelDropdownEditor.prototype.close = function() {
            Handsontable.editors.DropdownEditor.prototype.close.apply(this, arguments);

            this.instance.removeHook('afterValidate', ExcelDropdownEditor.confirmWdtDropdownCellValidate);
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelDropdownEditor = ExcelDropdownEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.dropdown', ExcelDropdownEditor);
    }

    /**
     * CUSTOM MULTI-SELECT DROPDOWN EDITOR DEFINITION.
     */
    function initWdtMultipleSelectEditor() {
        var ExcelMultiSelectEditor = Handsontable.editors.ExcelDropdownEditor.prototype.extend();

        ExcelMultiSelectEditor.onBeforeKeyDown = function(event) {
            //on ENTER we want to select dropdown item
            switch (event.keyCode) {
                case Handsontable.helper.KEY_CODES.ENTER:
                    var editor = this.getActiveEditor();
                    var choicesListHot = editor.htEditor.getInstance();
                    var selectedCellsRange = choicesListHot.getSelected();

                    if( selectedCellsRange ) {
                        var selectedTD = choicesListHot.getCell( selectedCellsRange[0], selectedCellsRange[1] );
                        ExcelMultiSelectEditor.selectDropdownItem( selectedTD, choicesListHot ,editor );
                    }

                    event.stopImmediatePropagation(); // prevent EditorManager from processing this event
                    event.preventDefault(); // prevent browser from scrolling the page up
                    break;
            }
        };

        //marking selected values in dropdown and add them in textarea.
        ExcelMultiSelectEditor.selectDropdownItem = function(selectedTD, choicesListHot ,editor) {
            var $td = $(selectedTD);
            var cell_value = $td.text();
            if($td.children('strong:first-child').length) {
                $td.html( cell_value );
            } else {
                $td.html( '<strong>' + cell_value + '</strong>' );
            }

            var value = [];
            $(choicesListHot.rootElement).find('td strong:first-child').each(function(){
                value.push($(this).text());
            });

            editor.setValue(value.join());
        };

        ExcelMultiSelectEditor.prototype.open = function() {
            // register listener
            this.instance.addHook('beforeKeyDown', ExcelMultiSelectEditor.onBeforeKeyDown);

            Handsontable.editors.ExcelDropdownEditor.prototype.open.apply(this, arguments);

            var choicesListHot = this.htEditor.getInstance();
            var editor = this;
            var choicesListHotSettings = choicesListHot.getSettings();

            //Overriding previous inner handsontable object functionality
            //this was the only way to get afterRenderer function
            choicesListHot.removeHook('afterRenderer', choicesListHot.pluginHookBucket.afterRenderer.slice(-1)[0]);
            choicesListHot.removeHook('afterOnCellMouseDown', choicesListHotSettings.afterOnCellMouseDown);

            choicesListHot.updateSettings({
                afterRenderer: function(TD, row, col, prop, value) {
                    //taken from autocmplete editor and modified
                    //marking values that are entered in textarea.
                    var caseSensitive = this.getCellMeta(row, col).filteringCaseSensitive === true,
                        indexOfMatch,
                        value = Handsontable.helper.stringify(value);
                    if (value) {
                        indexOfMatch = caseSensitive ? $.inArray(value, editor.query.split(',')) : $.inArray(value.toLowerCase(), editor.query.toLowerCase().split(','));
                        if (indexOfMatch != -1) {
                            TD.innerHTML = value.replace(value, '<strong>' + value + '</strong>');
                        }
                    }
                },
                afterOnCellMouseDown: function(event, coords, TD) {
                    //taken from handsontable editor and modified
                    //on mouse click on a select box handsontable element
                    var cell_value = this.getValue();
                    if (cell_value !== void 0) {
                        ExcelMultiSelectEditor.selectDropdownItem( TD, choicesListHot ,editor );
                    }

                    editor.focus();
                }
            });
        };

        //overriding from handsontable editor because it was taking only one selected item
        ExcelMultiSelectEditor.prototype.finishEditing = function(isCancelled, ctrlDown) {
            if (this.htEditor && this.htEditor.isListening()) {
                this.instance.listen();
            }

            if (this.htEditor) {
                var value = [];
                $(this.htEditor.getInstance().rootElement).find('td strong:first-child').each(function(){
                    value.push($(this).text());
                });

                //this.setValue(value.join());
                this.setValue(this.TEXTAREA.value);
            }

            return Handsontable.editors.TextEditor.prototype.finishEditing.apply(this, arguments);
        };

        ExcelMultiSelectEditor.prototype.close = function() {
            Handsontable.editors.ExcelDropdownEditor.prototype.close.apply(this, arguments);
            // remove listener
            this.instance.removeHook('beforeKeyDown', ExcelMultiSelectEditor.onBeforeKeyDown);
        };

        // Put editor in dedicated namespace
        Handsontable.editors.ExcelMultiSelectEditor = ExcelMultiSelectEditor;

        // Register alias
        Handsontable.editors.registerEditor('wdt.multi-select', ExcelMultiSelectEditor);
    }

    /**
     * LINK RENDERER DEFINITION.
     */
    function wdtLinkRenderer( instance, td, row, col, prop, value, cellProperties ) {
        //check if link is empty
        //also check if editor type is attachment
        Handsontable.TextRenderer.apply(this, arguments);

        if( !value ) {
            return td;
        }

        var link_parts = value.split('||');
        var link = link_parts[0];

        if( link.length == 0 ) {
            return td;
        }

        var content = ( link_parts.length > 1 )? link_parts[1]: link;
        var table_settings = instance.getSettings();
        var class_name = 'wdt_link';
        var title_attr = '';

        if( !table_settings.readOnly ) {
            class_name += '_editable';
            title_attr = 'title="ctrl+click to open hyperlink:'+ link +'"';
        }

        $(td).html('<a class="'+class_name+'" href="'+ link +'" target="_blank" '+ title_attr +'>'+ content +'</a>');
    }

    /**
     * E-MAIL RENDERER DEFINITION.
     */
    function wdtEmailRenderer( instance, td, row, col, prop, value, cellProperties ) {
        Handsontable.TextRenderer.apply(this, arguments);

        if( !value ) {
            return td;
        }

        var email_parts = value.split('||');
        var email = email_parts[0];

        if( email.length == 0 ) {
            return td;
        }

        var content = ( email_parts.length > 1 )? email_parts[1]: email;
        var table_settings = instance.getSettings();
        var class_name = 'wdt_email';
        var title_attr = '';

        if( !table_settings.readOnly ) {
            class_name += '_editable';
            title_attr = 'title="ctrl+click to send email to:'+ email +'"';
        }

        $(td).html('<a class="'+class_name+'" href="mailto:'+ email +'" '+ title_attr +'>'+ content +'</a>');
    }

    /**
     * IMAGE RENDERER DEFINITION.
     */
    function wdtImageRenderer( instance, td, row, col, prop, value, cellProperties ) {
        Handsontable.TextRenderer.apply(this, arguments);

        if( !value ) {
            return td;
        }

        var image_parts = value.split('||');
        var image_url = image_parts[0];

        if( image_url.length == 0 ) {
            return td;
        }

        //image width and height data are saved with image url(in editing cell process) as query params ('img_width' and 'img_height') in order to fast retrieve them.
        //without presetting image dimensions, handsontable had troubles to render table dimensions properly if image column is present.
        var imageUri = new URI( image_url );
        var imageQuery = imageUri.query( true );
        var img_width = ( imageUri.hasQuery( 'img_width' ) )? imageQuery.img_width: 0;
        var img_height = ( imageUri.hasQuery( 'img_height' ) )? imageQuery.img_height: 0;

        var img_style = '';

        if( img_width > 0 ) {
            img_style += 'width:' + img_width + 'px; '
        }

        if( img_height > 0 ) {
            img_style += 'height:' + img_height + 'px;'
        }

        if( img_style.length > 0 ) {
            img_style = 'style="' + img_style + '"';
        }

        //if( !/\.(jpg|jpeg|png|gif)$/i.test( link ) ) {
        //    //TODO: add empty file icon if not image
        //}

        var html = '';

        if( image_parts.length > 1 && image_parts[1] != '' ) {
            var tableSettings = instance.getSettings();
            var class_name = 'wdt_link';
            var title_attr = '';

            var fullSizeImgUri = new URI( image_parts[1] );
            fullSizeImgUri.removeQuery( ['img_width', 'img_height'] );
            var full_size_image = fullSizeImgUri.toString();

            if( !tableSettings.readOnly ) {
                class_name += '_editable';
                title_attr = 'title="ctrl+click to open hyperlink:'+ full_size_image +'"';
            }

            var image_html = '<img class="wpdt-thumb" src="' + image_url + '" ' + img_style + ' />';

            html = '<a class="'+class_name+'" href="'+ full_size_image +'" target="_blank" '+ title_attr +'>' + image_html + '</a>';
        } else {
            html = '<img class="wpdt-thumb" src="' + image_url + '" ' + img_style + ' />';
        }

        td.innerHTML = html;
    }

    /**
     * CUSTOM TEXT RENDERER DEFINITION.
     */
    function wdtTextRenderer(instance, td, row, col, prop, value, cellProperties) {
        var escaped = Handsontable.helper.stringify(value);
        escaped = wdtStripTags(escaped, '<br/><br><b><strong><h1><h2><h3><a><i><em><ol><ul><li><img><blockquote><div><hr><p><span><select><option><sup><sub>'); //be sure you only allow certain HTML tags to avoid XSS threats (you should also remove unwanted HTML attributes)
        td.innerHTML = escaped;
        Handsontable.SearchCellDecorator.apply(this, arguments);
        return td;
    }

    /**
     * CUSTOM DATE RENDERER DEFINITION.
     */
    function wdtDateRenderer(instance, td, row, col, prop, value, cellProperties) {
        if( !value || value == '0000-00-00' ) {
            value = '';
            $(td).html( value );
            Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
            return td;
        }

        var table_settings = instance.getSettings();

        if (table_settings.dataSourceDateFormat != table_settings.displayDateFormat) {
            //values are converted from database format to desired display format
            value = moment(value, table_settings.dataSourceDateFormat).format(table_settings.displayDateFormat);
        }

        Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
    }

    /**
     * Custom DateTime renderer
     */
    function wdtDateTimeRenderer( instance, td, row, col, prop, value, cellProperties ){
        if( !value || value == '0000-00-00 00:00:00' ) {
            value = '';
            $(td).html( value );
            Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
            return td;
        }

        var table_settings = instance.getSettings();

        if (table_settings.dataSourceDateFormat != table_settings.displayDateFormat) {
            //values are converted from database format to desired display format
            value = moment(
                value,
                table_settings.dataSourceDateFormat+' HH:mm:ss'
            ).format(
                table_settings.displayDateFormat + ' ' + table_settings.momentTimeFormat
            );
        }else{
            value = moment(
                value,
                table_settings.dataSourceDateFormat+' HH:mm:ss'
            ).format(
                table_settings.dataSourceDateFormat + ' ' + table_settings.momentTimeFormat
            );
        }

        Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
    }

    /**
     * Custom Time renderer
     */
    function wdtTimeRenderer( instance, td, row, col, prop, value, cellProperties ){
        if( !value ) {
            value = '';
            $(td).html( value );
            Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
            return td;
        }

        var table_settings = instance.getSettings();

        value = moment( value, 'HH:mm:ss').format( table_settings.momentTimeFormat );

        Handsontable.AutocompleteRenderer(instance, td, row, col, prop, value, cellProperties);
    }


    //registers custom renderers
    function registerRenderers() {
        Handsontable.renderers.registerRenderer('text', wdtTextRenderer);
        Handsontable.renderers.registerRenderer('wdt.date', wdtDateRenderer);
        Handsontable.renderers.registerRenderer('wdt.datetime', wdtDateTimeRenderer);
        Handsontable.renderers.registerRenderer('wdt.time', wdtTimeRenderer);
        Handsontable.renderers.registerRenderer('wdt.link', wdtLinkRenderer);
        Handsontable.renderers.registerRenderer('wdt.email', wdtEmailRenderer);
        Handsontable.renderers.registerRenderer('wdt.image', wdtImageRenderer);
    }

    //initializes and registers custom editors
    function registerEditors() {
        initWdtMultilineEditor();
        initWdtDateEditor();
        initWdtDateTimeEditor();
        initWdtTimeEditor();
        initWdtAttachmentEditor();
        initWdtDropdownEditor();
        initWdtMultipleSelectEditor();
    }

    //defines and registers custom cell types
    function registerCellTypes() {
        Handsontable.wdtDateCell = {
            editor: 'wdt.date',
            validator: Handsontable.wdtDateValidator,
            renderer: 'wdt.date'
        };

        Handsontable.wdtTextCell = {
            editor: 'text',
            renderer: 'text'
        };

        Handsontable.wdtDateTimeCell = {
            editor: 'wdt.datetime',
            validator: Handsontable.wdtDateTimeValidator,
            renderer: 'wdt.datetime'
        };

        Handsontable.wdtTimeCell = {
            editor: 'wdt.time',
            validator: Handsontable.wdtTimeValidator,
            renderer: 'wdt.time'
        };

        Handsontable.wdtMultiSelectCell = {
            editor: 'wdt.multi-select',
            validator: Handsontable.wdtMultiSelectValidator,
            renderer: 'text'
        };

        Handsontable.cellTypes['text'] = Handsontable.wdtTextCell;
        Handsontable.cellTypes['wdt.date'] = Handsontable.wdtDateCell;
        Handsontable.cellTypes['wdt.datetime'] = Handsontable.wdtDateTimeCell;
        Handsontable.cellTypes['wdt.time'] = Handsontable.wdtTimeCell;
        Handsontable.cellTypes['wdt.multi-select'] = Handsontable.wdtMultiSelectCell;
    }

    /**
     * LINK VALIDATOR DEFINITION.
     */
    Handsontable.wdtLinkValidator = function(value, callback) {
        if (value === null) {
            value = '';
        }

        var is_valid = false;

        if( value == '' ) {
            is_valid = true;
        } else {
            var regex = /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?(||.*)?$/i;

            is_valid = regex.test(value);
        }

        callback( is_valid );
    };

    /**
     * E-MAIL VALIDATOR DEFINITION.
     */
    Handsontable.wdtEmailValidator = function(value, callback) {
        if (value === null) {
            value = '';
        }

        var is_valid = false;

        if( value == '' ) {
            is_valid = true;
        } else {
            //took from wdtValidateEmail, in wpdatatables.func.js, maybe move these helper functions somewhere that every library can use them
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+(||.*)?$/;

            is_valid = regex.test(value);
        }

        callback(is_valid);
    };

    /**
     * CUSTOM DATE VALIDATOR DEFINITION.
     */
    Handsontable.wdtDateValidator = function(value, callback) {
        if (value === null) {
            value = '';
        }

        if( value == '' ) {
            callback(true);
        } else {
            Handsontable.DateValidator.call(this, value, callback);
        }
    };

    /**
     * CUSTOM DATETIME VALIDATOR DEFINITION
     */
    Handsontable.wdtDateTimeValidator = function( value, callback ){
        if (value === null) {
            value = '';
        }

        var table_settings = this.instance.getSettings();

        var isValidDate = moment(new Date(value)).isValid();
        var isValidFormat = moment(
                                value,
                                table_settings.dataSourceDateFormat+' H:mm:ss',
                                true
                            ).isValid();

        if( value == '' ) {
            callback(true);
        } else {
            callback( isValidDate && isValidFormat );
        }
    };

    /**
     * CUSTOM TIME VALIDATOR DEFINITION
     */
    Handsontable.wdtTimeValidator = function( value, callback ){
        if (value === null) {
            value = '';
        }

        var table_settings = this.instance.getSettings();

        var isValidTime = moment(
            value,
            'H:mm:ss',
            true
        ).isValid();

        if( value == '' ) {
            callback(true);
        } else {
            callback( isValidTime );
        }
    };

    /**
     * MULTI-SELECT VALIDATOR DEFINITION.
     * Checking if entered values are in possible values.
     */
    Handsontable.wdtMultiSelectValidator = function(value, callback) {
        var isValid = true;

        if (value === null) {
            value = '';
        }

        if( this.source ) {
            var selected = value.toLowerCase().split(',');
            var choices = this.source.join(',').toLowerCase().split(',');

            for( var i = 0; i < selected.length; i++ ) {
                if( choices.indexOf( selected[i] ) == -1 ) {
                    isValid = false;
                    break;
                }
            }
        } else if( value ) {
            //if there is no select box and value is not empty
            isValid = false;
        }

        callback( isValid );
    };

    registerEditors();
    registerRenderers();
    registerCellTypes();

})(Handsontable, jQuery);

if( typeof wpDataTablesExcelOptions == 'undefined' ) {
    window.wpDtExcelTables = {};
}

(function ($) {
    $(function () {
        $('div.wpExcelTable').each( function (){
            $(this).handsontable({
                wpDataTablesExcelPlugin: true
            });
        })
    })
})(jQuery);

function wdtStripTags(input, allowed) {
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

    // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');

    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}
