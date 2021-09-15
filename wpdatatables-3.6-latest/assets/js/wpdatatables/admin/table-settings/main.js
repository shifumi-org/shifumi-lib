/**
 * Main jQuery elements controller for the table settings page
 *
 * Binds the jQuery control elements for manipulating the config object, binds jQuery plugins
 *
 * @author Alexander Gilmanov
 * @since 16.09.2016
 */

(function ($) {
    $(function () {

        /**
         * Change table type
         */
        $('#wdt-table-type').change(function (e) {
            wpdatatable_config.setTableType($(this).val());
        });

        /**
         * Toggle server-side processing
         */
        $('.wdt-server-side').change(function (e) {
            wpdatatable_config.setServerSide($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Change table name
         */
        $('#wdt-table-title-edit').change(function (e) {
            wpdatatable_config.setTitle($(this).val());
        });

        /**
         * Change auto-refresh
         */
        $('#wdt-auto-refresh').change(function (e) {
            wpdatatable_config.setAutoRefresh($(this).val());
        });

        /**
         * Change URL
         */
        $('#wdt-input-url').bind('input change', function (e) {
            wpdatatable_config.setContent($(this).val());
        });

        /**
         * Toggle Table Tools selection dropdown
         */
        $('#wdt-table-tools').change(function (e) {
            wpdatatable_config.setShowTableTools($(this).is(':checked') ? 1 : 0, {});
        });

        /**
         * Toggle Table Tools Include HTML option
         */
        $('#wdt-table-tools-include-html').change(function (e) {
            wpdatatable_config.setTableToolsIncludeHTML($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Responsiveness
         */
        $('#wdt-responsive').change(function (e) {
            wpdatatable_config.setResponsive($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Scrollable
         */
        $('#wdt-scrollable').change(function (e) {
            wpdatatable_config.setScrollable($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Vertical scroll
         */
        $('#wdt-vertical-scroll').change(function (e) {
            wpdatatable_config.setVerticalScroll($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Hide until loaded
         */
        $('#wdt-hide-until-loaded').change(function (e) {
            wpdatatable_config.setHideBeforeLoad($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Limit Width
         */
        $('#wdt-limit-layout').change(function (e) {
            wpdatatable_config.setLimitLayout($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Word Wrap
         */
        $('#wdt-word-wrap').change(function (e) {
            wpdatatable_config.setWordWrap($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle show title
         */
        $('#wdt-show-title').change(function (e) {
            wpdatatable_config.setShowTitle($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Change display length
         */
        $('#wdt-rows-per-page').change(function (e) {
            wpdatatable_config.setDisplayLength($(this).val());
        });

        /**
         * Toggle "Show X entries"
         */
        $('#wdt-show-rows-per-page').change(function (e) {
            wpdatatable_config.setShowRowsPerPage($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Change display info block
         */
        $('#wdt-info-block').change(function (e) {
            wpdatatable_config.setInfoBlock($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Enable pagination
         */
        $('#wdt-pagination').change(function (e) {
            wpdatatable_config.setPagination($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set pagination alignment
         */
        $('#wdt-pagination-align').change(function (e) {
            wpdatatable_config.setPaginationAlign($(this).val());
        });

        /**
         * Set pagination layout
         */
        $('#wdt-pagination-layout').change(function (e) {
            wpdatatable_config.setPaginationLayout($(this).val());
        });

        /**
         * Toggle simple responsive
         */
        $('#wdt-simple-responsive').change(function (e) {
            wpdatatable_config.setSimpleResponsive($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle simple header
         */
        $('#wdt-simple-header').change(function (e) {
            wpdatatable_config.setSimpleHeader($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle stripe table
         */
        $('#wdt-stripe-table').change(function (e) {
            wpdatatable_config.setStripeTable($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set cell padding for simple table
         */
        $('#wdt-cell-padding').change(function (e) {
            wpdatatable_config.setCellPadding($(this).val());
        });

        /**
         * Set cell padding for simple table
         */
        $('#wdt-vertical-scroll-height').change(function (e) {
            wpdatatable_config.setVerticalScrollHeight($(this).val());
        });

        /**
         * Toggle remove borders for simple table
         */
        $('#wdt-remove-borders').change(function (e) {
            wpdatatable_config.setRemoveBorders($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set border collapse for simple table
         */
        $('#wdt-border-collapse').change(function (e) {
            wpdatatable_config.setBorderCollapse($(this).val());
        });

        /**
         * Set border spacing for simple table
         */
        $('#wdt-border-spacing').change(function (e) {
            wpdatatable_config.setBorderSpacing($(this).val());
        });

        /**
         * Toggle Advanced filter
         */
        $('#wdt-advanced-filter').change(function (e) {
            wpdatatable_config.setAdvancedFiltering($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Sorting
         */
        $('#wdt-global-sorting').change(function (e) {
            wpdatatable_config.setSorting($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Global Search
         */
        $('#wdt-global-search').change(function (e) {
            wpdatatable_config.setGlobalSearch($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Filters in form
         */
        $('#wdt-filter-in-form').change(function (e) {
            wpdatatable_config.setFilteringForm($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Clear Filters
         */
        $('#wdt-clear-filters').change(function (e) {
            wpdatatable_config.setClearFilters($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Editable
         */
        $('#wdt-editable').change(function (e) {
            wpdatatable_config.setEditable($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Popover Tools
         */
        $('#wdt-popover-tools').change(function (e) {
            wpdatatable_config.setPopoverTools($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle inline editing
         */
        $('#wdt-inline-editable').change(function (e) {
            wpdatatable_config.setInlineEditing($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set MySQL table name
         */
        $('#wdt-mysql-table-name').change(function (e) {
            wpdatatable_config.setMySQLTableName($(this).val());
        });

        /**
         * Set ID editing column
         */
        $('#wdt-id-editing-column').change(function (e) {
            wpdatatable_config.setIdEditingColumn($(this).val());
        });

        /**
         * Set editor roles
         */
        $('#wdt-editor-roles').change(function (e) {
            wpdatatable_config.setEditorRoles($(this).val());
        });

        /**
         * Set buttons displayed on front-end
         */
        $('#wdt-edit-buttons-displayed').change(function (e) {
           wpdatatable_config.setEditButtonsDisplayed($(this).val());
        });

        /**
         * Toggle edit only own rows
         */
        $('#wdt-edit-only-own-rows').change(function (e) {
            wpdatatable_config.setEditOwnRows($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set User ID Column
         */
        $('#wdt-user-id-column').change(function (e) {
            wpdatatable_config.setUserIdColumn($(this).val());
        });

        /**
         * Toggle Show All rows
         */
        $('#wdt-show-all-rows').change(function (e) {
            wpdatatable_config.setShowAllRows($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Table Tools config
         */
        $('#wdt-table-tools-config').change(function (e) {
            var tableToolsConfig = {};
            var tableToolsSelection = $(this).val();
            for (var i in tableToolsSelection) {
                tableToolsConfig[tableToolsSelection[i]] = 1;
            }

            // Show/hide PDF export options
            if (tableToolsSelection.includes('pdf')) {
                if( !$('div.pdf-export-options').is(":visible")){
                    $('div.pdf-export-options').animateFadeIn();
                }
            } else {
                $('div.pdf-export-options').animateFadeOut();
                wpdatatable_config.setPdfPaperSize('A4');
                wpdatatable_config.setPdfPageOrientation('portrait');
            }

            if ($(this).val() == null) {
                wpdatatable_config.setShowTableTools(0, []);
            }
            wpdatatable_config.setTableToolsConfig(tableToolsConfig);
        });

        /**
         * Set PDF export paper size
         */
        $('#wdt-pdf-paper-size').change(function (e) {
            wpdatatable_config.setPdfPaperSize($(this).val());
        });

        /**
         * Set PDF export page orientation
         */
        $('#wdt-pdf-page-orientation').change(function (e) {
            wpdatatable_config.setPdfPageOrientation($(this).val());
        });


        /**
         * Set Placeholder VAR 1
         */
        $('#wdt-var1-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderVar1($(this).val());
        });

        /**
         * Set Placeholder VAR 2
         */
        $('#wdt-var2-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderVar2($(this).val());
        });

        /**
         * Set Placeholder VAR 3
         */
        $('#wdt-var3-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderVar3($(this).val());
        });

        /**
         * Set Placeholder Current User ID
         */
        $('#wdt-user-id-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentUserId($(this).val());
        });

        /**
         * Set Placeholder Current User Login
         */
        $('#wdt-user-login-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentUserLogin($(this).val());
        });

        /**
         * Set Placeholder Current Post ID
         */
        $('#wdt-post-id-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentPostId($(this).val());
        });

        /**
         * Set Placeholder wpdb
         */
        $('#wdt-wpdb-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderWpdb($(this).val());
        });

        /**
         * Set Placeholder Current User First Name
         */
        $('#wdt-user-first-name-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentUserFirstName($(this).val());
        });

        /**
         * Set Placeholder Current User Last Name
         */
        $('#wdt-user-last-name-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentUserLastName($(this).val());
        });

        /**
         * Set Placeholder Current User Email
         */
        $('#wdt-user-email-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentUserEmail($(this).val());
        });
        /**
         * Set Placeholder Current Date
         */
        $('#wdt-date-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentDate($(this).val());
        });
        /**
         * Set Placeholder Current DateTime
         */
        $('#wdt-datetime-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentDateTime($(this).val());
        });
        /**
         * Set Placeholder Current Time
         */
        $('#wdt-time-placeholder').change(function (e) {
            wpdatatable_config.setPlaceholderCurrentTime($(this).val());
        });

        /**
         * Change language on select change for table - "Interface language"
         */
        $('#wdt-table-interface-language').change(function (e) {
            wpdatatable_config.setLanguage($(this).val());
        });

        /**
         * Change table skin
         */
        $('#wdt-table-base-skin').change(function (e) {
            wpdatatable_config.setTableSkin($(this).val());
        });

        /**
         * Change table font
         */
        $('#wdt-table-font').change(function (e) {
            wpdatatable_config.setTableFontColorSettings($(this).data('name'), $(this).val());
        });

        /**
         * Change table font size
         */
        $('#wdt-table-font-size').change(function (e) {
            wpdatatable_config.setTableFontColorSettings($(this).data('name'), $(this).val());

        });

        /**
         * Change table font color
         */
        $('.wdt-color-picker').change(function (e) {
            wpdatatable_config.setTableFontColorSettings($(this).find('.cp-value').data('name'), $(this).find('input').val());
        });

        /**
         * Change table inner border size
         */
        $('#wdt-table-inner-border-size').change(function (e) {
            wpdatatable_config.setTableFontColorSettings($(this).data('name'), $(this).val());

        });

        /**
         * Change table outer border size
         */
        $('#wdt-table-outer-border-size').change(function (e) {
            wpdatatable_config.setTableFontColorSettings($(this).data('name'), $(this).val());

        });

        /**
         * Remove borders from table
         */
        $('#wdt-table-remove-borders').change(function (e) {
            wpdatatable_config.setTableBorderRemoval($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Remove borders from header
         */
        $('#wdt-table-remove-borders-header').change(function (e) {
            wpdatatable_config.setTableBorderRemovalHeader($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Custom CSS - "Custom wpDataTables CSS"
         */
        $('#wdt-table-custom-css').change(function (e) {
            if (aceEditorTableCSS.getValue().length > 0) {
                wpdatatable_config.setTableCustomCss(aceEditorTableCSS.getValue());
            } else {
                wpdatatable_config.setTableCustomCss('');
            }

        });

        /**
         * Remove decimal place if value is negative or 0 for int
         * and if value is negative for formula
         */
        $('#wdt-column-decimal-places').change(function (e) {
            var comparisonNumber = wpdatatable_config.currentOpenColumn.type == 'formula' ? -1 : 0;
            if ($('#wdt-column-decimal-places').val() <= comparisonNumber) {
                $('#wdt-column-decimal-places').val('');
            }
        });

        /**
         * Apply syntax highlighter for SQL
         */
        createAceEditor('wdt-mysql-query');

        /**
         * Apply syntax highlighter for custom table CSS
         */
        createAceEditor('wdt-table-custom-css');

        /**
         * Show "Reset options" when "Customize settings" tab is active
         */
        $('.wdt-main-menu a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if (target == '#customize-table-settings') {
                $('.wdt-reset-customize-options').show();
            } else {
                $('.wdt-reset-customize-options').hide();
            }
        });

        /**
         * Reset customize options
         */
        $('.wdt-reset-customize-options').click(function (e) {
            e.preventDefault();
            $('#customize-table-settings input.cp-value').val('').change();
            $('#customize-table-settings .wpcolorpicker-icon i').css('background','#fff');
            wpdatatable_config.tableFontColorSettings = _.mapObject(
                wpdatatable_config.tableFontColorSettings,
                function (color) {
                    return '';
                }
            );
            wpdatatable_config.setTableCustomCss('');
            wpdatatable_config.setLanguage('');
            wpdatatable_config.setTableSkin('');
            $('#wdt-table-remove-borders').prop( 'checked', false ).change();
            $('#wdt-table-remove-borders-header').prop( 'checked', false ).change();
        });

        /**
         * Close column settings
         */
        $('button.wdt-cancel-column-settings').click(function (e) {
            e.preventDefault();
            wpdatatable_config.currentOpenColumn.hide();
        });

        /**
         * Collapse and expand the Table Settings widget
         */
        $('button.wdt-collapse-table-settings').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if ($(this).hasClass('expanded')) {
                $(this).find('i').removeClass('wpdt-icon-angle-up').addClass('wpdt-icon-angle-down');
                $(this).closest('.card').find('div.card-body').slideUp();
                $(this).removeClass('expanded').addClass('collapsed');
            } else {
                $(this).find('i').addClass('wpdt-icon-angle-up').removeClass('wpdt-icon-angle-down');
                $(this).closest('.card').find('div.card-body').slideDown();
                $(this).addClass('expanded').removeClass('collapsed');
            }
        });

        /**
         * Reset column values
         */
        $('#wdt-column-values-reset').click(function (e) {
            e.preventDefault();
            $('#wdt-column-values-list')
                .tagsinput('removeAll');
        });

        /**
         * Configure column values
         */
        $('#wdt-column-values').change(function (e) {
            e.preventDefault();

            $('select#wdt-column-editor-input-type').find('option')
                .prop('disabled', false);

            $('#wdt-possible-values-ajax')
                .prop('disabled', false);

            if ($(this).val() === 'read') {
                $('div.wdt-manual-list-enter-block').hide();
                $('div.wdt-foreign-key-block').hide();
                $('.wdt-possible-values-ajax-block').show();
                $('.wdt-possible-values-foreign-keys-block').hide();
            } else if ($(this).val() === 'list') {
                $('div.wdt-manual-list-enter-block').show();
                $('div.wdt-foreign-key-block').hide();
                $('.wdt-possible-values-ajax-block').show();
                $('.wdt-possible-values-foreign-keys-block').hide();
            } else if ($(this).val() === 'foreignkey') {
                if ($.inArray(wpdatatable_config.currentOpenColumn.filter_type, ['select', 'checkbox']) !== -1) {
                    $('#wdt-column-exact-filtering').prop('checked', 1).change();
                    $('#wdt-column-range-slider').prop('checked',1).change();
                }
                $('div.wdt-manual-list-enter-block').hide();
                $('div.wdt-foreign-key-block').show();
                $('div.wdt-foreign-rule-display').show();
                $('.wdt-possible-values-ajax-block').hide();
                if(wpdatatable_config.edit_only_own_rows == 1) {
                    $('.wdt-possible-values-foreign-keys-block').show();
                } else {
                    $('.wdt-possible-values-foreign-keys-block').hide();
                }

                $('select#wdt-column-editor-input-type').find('option')
                    .not('[value=selectbox]')
                    .not('[value=none]')
                    .prop('disabled', true);
                if ($('select#wdt-column-editor-input-type').val() == null) {
                    $('select#wdt-column-editor-input-type').selectpicker('val', 'none');
                }
            } else {
                $('div.wdt-possible-values-options-block').hide();
            }

            $('select#wdt-column-editor-input-type').selectpicker('refresh');

            if (wpdatatable_config.currentOpenColumn.foreignKeyRule == null) {
                $('#wdt-column-foreign-table').selectpicker('val', 0).trigger('change');

                $('.wdt-foreign-rule-display #wdt-connected-table-name').text('-');
                $('.wdt-foreign-rule-display #wdt-connected-table-show-column').text('-');
                $('.wdt-foreign-rule-display #wdt-connected-table-value-column').text('-');
            }

        });

        /**
         * Show columns list modal
         */
        $('#wdt-open-columns-list').click(function (e) {
            e.preventDefault();
            $('#wdt-columns-list-modal').modal('show');
        });

        /**
         * Show Foreign Key config modal
         */
        $('#wdt-foreign-key-open').click(function (e) {
            e.preventDefault();
            $('#wdt-configure-foreign-key-modal').modal('show');
            if (wpdatatable_config.currentOpenColumn.foreignKeyRule) {
                $('#wdt-column-foreign-table')
                    .selectpicker('val', wpdatatable_config.currentOpenColumn.foreignKeyRule.tableId).trigger('change');
            }
        });

        /**
         * Apply Dragula within columns list modal
         */
        dragula([$('div.wdt-columns-container').get(0)])
            .on('dragend', function (el) {
                $('#wdt-columns-list-modal div.wdt-column-block').each(function () {
                    wpdatatable_config
                        .getColumnByHeader($(this).data('orig_header'))
                        .setPos($(this).index())
                });
            });

        /**
         * Open formula editor
         */
        $('button.wdt-add-formula-column,button.wdt-open-formula-editor').click(function (e) {
            e.preventDefault();
            if (wpdatatable_config.currentOpenColumn !== null) {
                $('#wdt-formula-editor-modal textarea').val(wpdatatable_config.currentOpenColumn.formula);
            } else {
                $('#wdt-formula-editor-modal textarea').val('');
            }
            $('div.wdt-formula-result-preview').addClass('hidden');
            $('#wdt-formula-editor-modal').modal('show');
        });

        /*
         * Open add column modal
         */
        $('button.wdt-add-column').click(function (e) {
            e.preventDefault();
            $('#wdt-add-column-modal').modal('show');
        });

        /*
         * Open remove column modal
         */
        $('button.wdt-remove-column').click(function (e) {
            e.preventDefault();
            $('#wdt-remove-column-modal').modal('show');
        });

        /**
         * Delete conditional formatting rule
         */
        $(document).on('click', 'button.wdt-delete-conditional-formatting-rule', function (e) {
            var $block = $(this).closest('div.wdt-conditional-formatting-rule');
            $block.remove();
        });


        $('#wdt-column-type').change(function (e) {

            /**
             * Remove foreign key rules for non-string columns
             */
            if ($(this).val() !== 'string' && wpdatatable_config.currentOpenColumn.foreignKeyRule) {
                delete wpdatatable_config.currentOpenColumn.foreignKeyRule;
            }


            /**
             * Show/hide different settings for different column types
             */
            if ($(this).val() == 'formula') {
                $('div.wdt-possible-values-type-block').hide();
                $('div.wdt-possible-values-options-block').hide();
                $('div.wdt-formula-column-block').show();
                $('div.wdt-skip-thousands-separator-block').hide();
                $('div.wdt-numeric-column-block').hide();
                $('div.wdt-float-column-block').show();
                $('div.wdt-date-input-format-block').hide();
                $('div.wdt-link-target-attribute-block').hide();
                $('div.wdt-link-nofollow-attribute-block').hide();
                $('div.wdt-link-button-attribute-block').hide();
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            } else if (['int', 'float'].indexOf($(this).val()) !== -1) {
                $('div.wdt-possible-values-type-block').hide();
                $('div.wdt-possible-values-options-block').hide();
                $('div.wdt-formula-column-block').hide();
                $('div.wdt-numeric-column-block').show();
                $('div.wdt-date-input-format-block').hide();
                if ($(this).val() == 'float') {
                    $('div.wdt-skip-thousands-separator-block').hide();
                    $('div.wdt-float-column-block').show();
                } else {
                    $('div.wdt-float-column-block').hide();
                    $('div.wdt-skip-thousands-separator-block').show();
                }
                $('div.wdt-link-target-attribute-block').hide();
                $('div.wdt-link-nofollow-attribute-block').hide();
                $('div.wdt-link-button-attribute-block').hide();
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            } else if ($(this).val() == 'string') {
                $('div.wdt-possible-values-type-block').show();
                $('div.wdt-possible-values-options-block').show();
                $('div.wdt-numeric-column-block').hide();
                $('div.wdt-formula-column-block').hide();
                $('div.wdt-date-input-format-block').hide();
                $('div.wdt-link-target-attribute-block').hide();
                $('div.wdt-link-nofollow-attribute-block').hide();
                $('div.wdt-link-button-attribute-block').hide();
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            } else if (['date', 'datetime'].indexOf($(this).val()) !== -1
                && $.inArray(wpdatatable_config.table_type, ['xls', 'csv', 'google_spreadsheet', 'json', 'xml', 'serialized']) !== -1) {
                $('div.wdt-date-input-format-block').show();
                $('div.wdt-possible-values-type-block').hide();
                $('div.wdt-possible-values-options-block').hide();
                $('div.wdt-numeric-column-block').hide();
                $('div.wdt-formula-column-block').hide();
                $('div.wdt-link-target-attribute-block').hide();
                $('div.wdt-link-nofollow-attribute-block').hide();
                $('div.wdt-link-button-attribute-block').hide();
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            } else if ($(this).val() == 'link') {
                $('div.wdt-possible-values-type-block').hide();
                $('div.wdt-possible-values-options-block').hide();
                $('div.wdt-numeric-column-block').hide();
                $('div.wdt-formula-column-block').hide();
                $('div.wdt-date-input-format-block').hide();
                $('div.wdt-link-target-attribute-block').show();
                $('div.wdt-link-nofollow-attribute-block').show();
                if ($('#wdt-link-button-attribute').is(':checked')) {
                    $('div.wdt-link-button-label-block').show();
                    $('div.wdt-link-button-class-block').show();
                }
                $('div.wdt-link-button-attribute-block').show();
            } else {
                $('div.wdt-possible-values-type-block').hide();
                $('div.wdt-possible-values-options-block').hide();
                $('div.wdt-numeric-column-block').hide();
                $('div.wdt-formula-column-block').hide();
                $('div.wdt-date-input-format-block').hide();
                $('div.wdt-link-target-attribute-block').hide();
                $('div.wdt-link-nofollow-attribute-block').hide();
                $('div.wdt-link-button-attribute-block').hide();
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            }
        });

        /**
         * Show different Default Value(s) input(s) for different filter types
         */
        $('#wdt-column-filter-type').change(function (e) {
            var filterType = $(this).val();
            var $filterInput = $('#wdt-filter-default-value');
            var $filterInputBlock = $('.wdt-filter-default-value-block');
            var $filterInputFrom = $('#wdt-filter-default-value-from');
            var $filterInputFromBlock = $('.wdt-filter-default-value-from-block');
            var $filterInputTo = $('#wdt-filter-default-value-to');
            var $filterInputToBlock = $('.wdt-filter-default-value-to-block');
            var $filterInputSelectpicker = $('#wdt-filter-default-value-selectpicker');
            var $filterInputSelectpickerBlock = $('.wdt-filter-default-value-selectpicker-block');
            var $renderCheckboxesInModal = $('.wdt-checkboxes-in-modal-block #wdt-checkboxes-in-modal');
            var $renderSearchInSelectBox = $('.wdt-search-in-selectbox-block #wdt-search-in-selectbox');
            var $renderCheckboxesInModalBlock = $('.wdt-checkboxes-in-modal-block');
            var $renderSearchInSelectBoxBlock = $('.wdt-search-in-selectbox-block');
            var typeAttr = 'text';

            $renderCheckboxesInModal.prop('checked', 0);
            if ($.inArray(filterType, ['text', 'number']) != -1) {
                $('div.wdt-exact-filtering-block').show();
                $('div.wdt-number-range-slider').hide();
                if (filterType === 'number')
                    typeAttr = 'number';
                $filterInput.attr('type', typeAttr);
                $filterInputBlock.show();
                $filterInputFromBlock.hide();
                $filterInputToBlock.hide();
                $filterInputSelectpickerBlock.hide();
                $filterInputFrom.val('');
                $filterInputTo.val('');
                $filterInputSelectpicker.selectpicker('deselectAll');
                $renderCheckboxesInModalBlock.hide();
                $renderSearchInSelectBoxBlock.hide();
            } else if ($.inArray(filterType, ['number-range', 'date-range', 'datetime-range', 'time-range']) != -1) {
                $('div.wdt-exact-filtering-block').hide();
                $('div.wdt-number-range-slider').hide();
                $filterInputBlock.hide();
                $filterInputFromBlock.show();
                $filterInputToBlock.show();
                $filterInputSelectpickerBlock.hide();
                $filterInputFrom.val('');
                $filterInputTo.val('');
                $filterInputSelectpicker.selectpicker('deselectAll');
                $renderCheckboxesInModalBlock.hide();
                $renderSearchInSelectBoxBlock.hide();

                if ($filterInputFrom.data('DateTimePicker') != undefined)
                    $filterInputFrom.data('DateTimePicker').destroy();

                if ($filterInputTo.data('DateTimePicker') != undefined)
                    $filterInputTo.data('DateTimePicker').destroy();

                if (filterType == 'number-range'){
                  $('div.wdt-number-range-slider').show();
                  typeAttr = 'number';
                }


                $filterInputFrom
                    .attr('type', typeAttr)
                    .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');
                $filterInputTo
                    .attr('type', typeAttr)
                    .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');

                if (filterType != 'number-range') {
                    $filterInputFrom
                        .addClass('wdt-' + filterType.substring(0, filterType.indexOf('-')) + 'picker');
                    $filterInputTo
                        .addClass('wdt-' + filterType.substring(0, filterType.indexOf('-')) + 'picker')
                }
            } else {
                $('div.wdt-exact-filtering-block').show();
                $('div.wdt-number-range-slider').hide();
                $filterInputBlock.hide();
                $filterInputFromBlock.hide();
                $filterInputToBlock.hide();
                $filterInputSelectpickerBlock.show();
                $renderCheckboxesInModalBlock.hide();
                $renderSearchInSelectBoxBlock.hide();

                // Must recreate selectpicker block because Ajax Selectpicker
                $filterInputSelectpickerBlock.html('<div class="fg-line"><div class="select"><select class="selectpicker" id="wdt-filter-default-value-selectpicker" data-none-selected-text="' + wpdatatables_frontend_strings.nothingSelected + '" data-live-search="true" title="' + wpdatatables_frontend_strings.nothingSelected + '"></select></div></div>');
                $filterInputSelectpicker = $('#wdt-filter-default-value-selectpicker');
                $filterInputSelectpicker.html('');

                if (filterType === 'checkbox' || filterType === 'multiselect') {
                    $filterInputSelectpicker.attr('multiple', 'multiple');
                } else {
                    // $filterInputSelectpicker.prepend('<option value="" data-empty="true" selected="selected"></option>');
                    $filterInputSelectpicker.removeAttr('multiple');
                }

                if (filterType === 'checkbox' && wpdatatable_config.filtering_form) {
                    $renderCheckboxesInModalBlock.show();
                }
                if (filterType === 'multiselect' || filterType === 'select') {
                    $renderSearchInSelectBoxBlock.show();
                    if (wpdatatable_config.currentOpenColumn.possibleValuesAjax === -1) {
                        $renderSearchInSelectBox.prop('checked', 0);
                    } else {
                        $renderSearchInSelectBox.prop('checked', 1);
                    }
                }

                if (wpdatatable_config.currentOpenColumn.possibleValuesType === 'foreignkey') {
                    $('#wdt-column-exact-filtering').prop('checked', 1).change();
                    $('#wdt-column-range-slider').prop('checked',1).change();
                }

                var options = '';

                if (wpdatatable_config.currentOpenColumn.possibleValuesType === 'read' || wpdatatable_config.currentOpenColumn.possibleValuesType === 'foreignkey') {
                    if (wpdatatable_config.currentOpenColumn.filterDefaultValue) {
                        if (wpdatatable_config.currentOpenColumn.possibleValuesType === 'read') {
                            var defaultValues = wpdatatable_config.currentOpenColumn.filterDefaultValue.split('|');

                            $.each(defaultValues, function (index, value) {
                                if (value) {
                                    options += '<option selected value="' + value + '">' + value + '</option>'
                                }
                            });
                        } else {
                            if ($.isArray(wpdatatable_config.currentOpenColumn.filterDefaultValue)) {
                                $.each(wpdatatable_config.currentOpenColumn.filterDefaultValue, function (index, value) {
                                    if (value) {
                                        options += '<option selected value="' + value.value + '">' + value.text + '</option>'
                                    }
                                });
                            } else {
                                options += '<option selected value="' + wpdatatable_config.currentOpenColumn.filterDefaultValue.value + '">' + wpdatatable_config.currentOpenColumn.filterDefaultValue.text + '</option>'
                            }
                        }

                        $filterInputSelectpicker.append(options);
                    }

                    $filterInputSelectpicker.selectpicker('destroy').selectpicker('render')
                        .ajaxSelectPicker({
                            ajax: {
                                url: ajaxurl,
                                method: 'POST',
                                data: {
                                    wdtNonce: $('#wdtNonce').val(),
                                    action: 'wpdatatables_get_column_possible_values',
                                    tableId: wpdatatable_config.id,
                                    originalHeader: wpdatatable_config.currentOpenColumn.orig_header
                                }
                            },
                            cache: false,
                            preprocessData: function (data) {
                                if ($filterInputSelectpicker.attr('multiple') !== 'multiple')
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

                    $filterInputSelectpicker.trigger('change').data('AjaxBootstrapSelect').list.cache = {};
                    $filterInputSelectpicker.on('show.bs.select', function (e) {
                        $filterInputSelectpickerBlock.find('.bs-searchbox .form-control').val('').trigger('keyup');
                    });
                } else {
                    if (wpdatatable_config.currentOpenColumn.valuesList !== null && !$.isArray(wpdatatable_config.currentOpenColumn.valuesList)) {
                        var defaultValuesData = wpdatatable_config.currentOpenColumn.valuesList.split('|');
                    }

                    if ($filterInputSelectpicker.attr('multiple') !== 'multiple')
                        options += '<option value="" data-empty="true" selected="selected"></option>'

                    $.each(defaultValuesData, function (index, value) {
                        if (value) {
                            options += '<option value="' + value + '">' + value + '</option>'
                        }
                    });

                    $filterInputSelectpicker.append(options);
                    $filterInputSelectpicker.selectpicker('destroy').selectpicker('render')
                }
            }

        });

        /**
         * Show/hide sorting direction block on 'default sorting' switch toggle
         */
        $('#wdt-column-default-sort').change(function (e) {
            if ($(this).is(':checked')) {
                $('div.wdt-column-default-sorting-direction-block').show();
            } else {
                $('div.wdt-column-default-sorting-direction-block').hide();
            }
        });

        /**
         * Show/hide editing blocks when 'none' is selected as editor input type.
         * Change 'Predefined value(s)' input type based on editor input type.
         */
        $('#wdt-column-editor-input-type').change(function (e) {

            $('div.wdt-editing-enabled-block').show();
            var editorInputType = $(this).val();
            var $defaultValueInput = $('#wdt-editing-default-value');
            var $defaultValueInputBlock = $('.wdt-editing-default-value-block');
            var $defaultValueSelectpicker = $('#wdt-editing-default-value-selectpicker');
            var $defaultValueSelectpickerBlock = $('.wdt-editing-default-value-selectpicker-block');
            var $searchInSelectBoxEditingBlock = $('.wdt-search-in-selectbox-editing-block');
            var $searchInSelectBoxEditing = $('#wdt-search-in-selectbox-editing');

            if ($.inArray(editorInputType, ['text', 'textarea', 'mce-editor', 'link', 'email', 'attachment']) != -1) {
                $defaultValueInputBlock.show();
                $defaultValueSelectpickerBlock.hide();
                $searchInSelectBoxEditingBlock.hide();

                if ($defaultValueInput.data('DateTimePicker') != undefined)
                    $defaultValueInput.data('DateTimePicker').destroy();

                $defaultValueInput
                    .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');

            } else if ($.inArray(editorInputType, ['date', 'datetime', 'time']) != -1) {
                $defaultValueInputBlock.show();
                $defaultValueSelectpickerBlock.hide();
                $searchInSelectBoxEditingBlock.hide();

                if ($defaultValueInput.data('DateTimePicker') != undefined)
                    $defaultValueInput.data('DateTimePicker').destroy();

                $defaultValueInput
                    .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');

                $defaultValueInput
                    .addClass('wdt-' + editorInputType + 'picker');

            } else if ($.inArray(editorInputType, ['selectbox', 'multi-selectbox']) != -1) {
                $defaultValueInputBlock.hide();
                $defaultValueSelectpickerBlock.show();
                $searchInSelectBoxEditingBlock.show();
                if (wpdatatable_config.currentOpenColumn.possibleValuesAjax === -1) {
                    $searchInSelectBoxEditing.prop('checked', 0);
                } else {
                    $searchInSelectBoxEditing.prop('checked', 1);
                }
                $defaultValueInput.val('');

                $defaultValueSelectpickerBlock.html('<div class="fg-line"><div class="select"><select class="selectpicker" id="wdt-editing-default-value-selectpicker" data-none-selected-text="' + wpdatatables_frontend_strings.nothingSelected + '" data-live-search="true"></select></div></div>');
                $defaultValueSelectpicker = $('#wdt-editing-default-value-selectpicker');
                $defaultValueSelectpicker.html('');

                if (editorInputType === 'multi-selectbox') {
                    $defaultValueSelectpicker.attr('multiple', 'multiple');
                } else {
                    $defaultValueSelectpicker.removeAttr('multiple');
                }

                var options = '';
                if (wpdatatable_config.currentOpenColumn.editingDefaultValue) {
                    if (wpdatatable_config.currentOpenColumn.possibleValuesType === 'read' || wpdatatable_config.currentOpenColumn.possibleValuesType === 'list') {
                        if (!$.isArray(wpdatatable_config.currentOpenColumn.editingDefaultValue))
                            var defaultValues = wpdatatable_config.currentOpenColumn.editingDefaultValue.split('|');

                        $.each(defaultValues, function (index, value) {
                            if (value) {
                                options += '<option selected value="' + value + '">' + value + '</option>'
                            }
                        });
                    } else if (wpdatatable_config.currentOpenColumn.possibleValuesType === 'foreignkey') {
                        if ($.isArray(wpdatatable_config.currentOpenColumn.editingDefaultValue)) {
                            $.each(wpdatatable_config.currentOpenColumn.editingDefaultValue, function (index, value) {
                                if (value) {
                                    options += '<option selected value="' + value.value + '">' + value.text + '</option>'
                                }
                            });
                        } else {
                            options += '<option selected value="' + wpdatatable_config.currentOpenColumn.editingDefaultValue.value + '">' + wpdatatable_config.currentOpenColumn.editingDefaultValue.text + '</option>'
                        }
                    }
                }

                $defaultValueSelectpicker.append(options);

                $defaultValueSelectpicker.selectpicker('destroy').selectpicker('render')
                    .ajaxSelectPicker({
                        ajax: {
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                wdtNonce: $('#wdtNonce').val(),
                                action: 'wpdatatables_get_column_possible_values',
                                tableId: wpdatatable_config.id,
                                originalHeader: wpdatatable_config.currentOpenColumn.orig_header
                            }
                        },
                        cache: false,
                        preprocessData: function (data) {
                            if ($defaultValueSelectpicker.attr('multiple') !== 'multiple')
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

                $defaultValueSelectpicker.trigger('change').data('AjaxBootstrapSelect').list.cache = {};
                $defaultValueSelectpicker.on('show.bs.select', function (e) {
                    $defaultValueSelectpickerBlock.find('.bs-searchbox .form-control').val('').trigger('keyup');
                });

            } else {
                $defaultValueInput.val('');
                $('div.wdt-editing-enabled-block').hide();
            }

        });

        /**
         * Add a conditional formatting rule
         */
        $('button.wdt-column-add-conditional-formatting-rule').click(function (e) {
            if (wpdatatable_config.currentOpenColumn == null) {
                return;
            }
            wpdatatable_config.currentOpenColumn.renderConditionalFormattingBlock({
                ifClause: 'eq',
                cellVal: '',
                action: 'setCellColor',
                setVal: ''
            });
        });

        /**
         * Show/hide the filter selection block on 'enable filtering' switch toggle
         */
        $('#wdt-column-enable-filter').change(function (e) {
            if ($(this).is(':checked')) {
                $('div.wdt-filtering-enabled-block').show();
                $('#wdt-column-filter-type').trigger('change');
            } else {
                $('div.wdt-filtering-enabled-block').hide();
            }
        });

        /**
         * Show/hide all filtering settings and enable/disable filtering on 'global search'  toggle
         */
        $('#wdt-column-enable-global-search').change(function (e) {
            let columnEnableFilter = $('#wdt-column-enable-filter');
            if (!$(this).is(':checked')) {
                columnEnableFilter.prop('checked', 0).change().attr('disabled', true);
            } else {
                columnEnableFilter.attr('disabled', false).prop('checked', 1).change();
            }
        });

        /**
         * Show/hide button label in Url link column
         */
        $('#wdt-link-button-attribute').change(function (e) {
            if ($(this).is(':checked')) {
                $('div.wdt-link-button-label-block').show();
                $('div.wdt-link-button-class-block').show();

            } else {
                $('div.wdt-link-button-label-block').hide();
                $('div.wdt-link-button-class-block').hide();
            }
        });


        /**
         * Apply all column changes on "Apply" click in column block
         */
        $('button.wdt-column-apply').click(function (e) {
            e.preventDefault();
            wpdatatable_config.currentOpenColumn.applyChanges();
            // Trigger table's Apply action
            $('button.wdt-apply:eq(0)').click();
        });

        /**
         * Apply all changes on "Apply" click
         */
        $(document).on('click', 'button.wdt-apply', function () {

            if (wpdatatable_config.table_type == 'gravity' ||
                wpdatatable_config.table_type == 'formidable' ) return;

            // Validation for valid URL link of Google spreadsheet
            if (wpdatatable_config.table_type == 'google_spreadsheet' && wpdatatable_config.content.indexOf("2PACX") != -1) {
                $('#wdt-error-modal .modal-body').html('URL from Google spreadsheet publish modal(popup) is not valid for wpDataTables. Please provide a valid URL link that you get from the browser address bar. More info in our documentation on this <a href="https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-google-spreadsheets/" target="_blank">link</a>.');
                $('#wdt-error-modal').modal('show');
                return;
            }
            if (!wpdatatable_config.title) {
                wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.tableNameEmpty, 'danger');
                return;
            }

            if (wpdatatable_config.editable) {
                if ($('#wdt-mysql-table-name').val() == '') {
                    $('#wdt-error-modal .modal-body').html('MySQL table name for front-end editing is not set!');
                    $('#wdt-error-modal').modal('show');
                    return;
                }
            }
            if (wpdatatable_config.server_side == 1) {
                for (var i in wpdatatable_config.columns) {
                    wpdatatable_config.columns[i].groupColumn = 0;
                    if (wpdatatable_config.columns[i].type == 'formula') {
                        wpdatatable_config.columns[i].calculateTotal = 0;
                        wpdatatable_config.columns[i].calculateAvg = 0;
                        wpdatatable_config.columns[i].calculateMin = 0;
                        wpdatatable_config.columns[i].calculateMax = 0;
                    }
                    if (wpdatatable_config.columns[i].possibleValuesType == 'foreignkey') {
                        wpdatatable_config.columns[i].possibleValuesAjax = -1;
                    }
                }
            }
            $('.wdt-preload-layer').animateFadeIn();

            wpdatatable_config.connection = $('#wdt-table-connection').val();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    wdtNonce: $('#wdtNonce').val(),
                    action: 'wpdatatables_save_table_config',
                    table: JSON.stringify(wpdatatable_config.getJSON())
                },
                success: function (data) {

                    if (typeof data.error != 'undefined') {
                        // Show error if returned
                        $('#wdt-error-modal .modal-body').html(data.error);
                        $('#wdt-error-modal').modal('show');
                        $('.wdt-preload-layer').animateFadeOut();
                    } else {
                        // Reinitialize table with returned data
                        wpdatatable_config.initFromJSON(data.table);
                        wpdatatable_config.setTableHtml(data.wdtHtml);
                        wpdatatable_config.setDataTableConfig(data.wdtJsonConfig);
                        if ($('.wpExcelTable').length) {
                            location.reload();
                        } else {
                            wpdatatable_config.renderTable();
                        }
                        // Show editing tab
                        if ((data.table.table_type == 'mysql' || data.table.table_type == 'manual')
                            && !jQuery('.editing-settings-tab').is(':visible')) {
                            $('.editing-settings-tab').animateFadeIn();
                        }
                        // Add url to "Switch View" buttons for MySQL tables
                        if (data.table.table_type == 'mysql') {
                            $('.wdt-edit-buttons a')
                                .attr('href', window.location.pathname + '?page=wpdatatables-constructor&source&table_id=' + data.table.id + '&table_view=excel')
                        }
                        // Show success message
                        wdtNotify(
                            wpdatatables_edit_strings.success,
                            wpdatatables_edit_strings.tableSaved,
                            'success'
                        );
                    }
                    if (window.location.href.indexOf("table_id=") === -1) {
                        window.history.replaceState(null, null, window.location.pathname + "?page=wpdatatables-constructor&source&table_id=" + data.table.id);
                    }

                },
                error: function (data) {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to save the table! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            });
        });

        /**
         * Switch tabs in table and column settings
         */
        $('.wdt-datatables-admin-wrap .wdt-table-settings .tab-nav a, .wdt-datatables-admin-wrap .column-settings-panel .tab-nav a').click(function (e) {
            $(this).tab('show');
        });

        /**
         * Fill possible values list on "Read From Table" button
         */
        $('button#wdt-column-values-read-from-table').click(function (e) {
            e.preventDefault();

            if (wpdatatable_config.table_type == 'mysql' || wpdatatable_config.table_type == 'manual') {
                var data = {};
                data.action = 'wpdatatable_get_column_distinct_values';
                data.tableId = wpdatatable_config.id;
                data.columnId = wpdatatable_config.currentOpenColumn.id;

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    success: function (distValues) {
                        if (!(distValues instanceof Array)) {
                            return;
                        }
                        wdtFillPossibleValuesList(distValues);
                    }
                });
            } else {
                var distValues = wpdatatable_config.dataTable.columns(wpdatatable_config.currentOpenColumn.pos).data().eq(0).sort().unique().toArray();
                wdtFillPossibleValuesList(distValues);
            }

        });

        /**
         * Apply foreign key rule and save for current column
         */
        $('button.wdt-save-foreign-key-rule').click(function (e) {
            $('#wdt-configure-foreign-key-modal').modal('hide');
            if (wpdatatable_config.currentOpenColumn == null) {
                return;
            }
            wpdatatable_config.currentOpenColumn.foreignKeyRule = {
                tableId: $('#wdt-column-foreign-table').val(),
                tableName: $('#wdt-column-foreign-table').selectpicker('val') != 0 ? $('#wdt-column-foreign-table option:selected').html() : '',
                displayColumnId: $('#wdt-foreign-column-display-value').val(),
                displayColumnName: $('#wdt-foreign-column-display-value option:selected').data('orignal_header'),
                storeColumnId: $('#wdt-foreign-column-store-value').val(),
                storeColumnName: $('#wdt-foreign-column-store-value option:selected').data('orignal_header'),
                allowAllPossibleValuesForeignKey: $('#wdt-possible-values-foreign-keys').val()
            };
            if (wpdatatable_config.currentOpenColumn.foreignKeyRule.tableId != 0) {
                $('.wdt-foreign-rule-display #wdt-connected-table-name').text($('#wdt-column-foreign-table option:selected').html());
                $('.wdt-foreign-rule-display #wdt-connected-table-show-column').text($('#wdt-foreign-column-display-value option:selected').html());
                $('.wdt-foreign-rule-display #wdt-connected-table-value-column').text($('#wdt-foreign-column-store-value option:selected').html());
            } else {
                $('.wdt-foreign-rule-display #wdt-connected-table-name').text('-');
                $('.wdt-foreign-rule-display #wdt-connected-table-show-column').text('-');
                $('.wdt-foreign-rule-display #wdt-connected-table-value-column').text('-');
            }
        });

        /**
         * Replace span with input for column name and vice versa
         */
        $(document).on('keyup', '.wdt-column-display-header-edit', function (e) {
            e.preventDefault();
            wpdatatable_config
                .getColumnByHeader($(this).closest('.wdt-column-block').data('orig_header'))
                .setDisplayHeader($(this).val());
        });

        /**
         * Apply changes for column settings on enter
         */
        $(document).on('keyup', '#column-display-settings input[type="text"]', function (e) {
            if (e.which == 13) {
                $('button.wdt-column-apply:eq(0)').click();
            }
        });

        /**
         * Load the tables for foreign key when a table is chosen in dropdown
         */
        $('#wdt-column-foreign-table').change(function (e) {
            if ($(this).val() != '') {
                wpdatatable_config.getForeignColumns(
                    $(this).val(),
                    wpdatatable_config.currentOpenColumn.foreignKeyRule ? wpdatatable_config.currentOpenColumn.foreignKeyRule.displayColumnId : null,
                    wpdatatable_config.currentOpenColumn.foreignKeyRule ? wpdatatable_config.currentOpenColumn.foreignKeyRule.storeColumnId : null
                )
            }
        });

        /**
         * Apply the changes to column in the quickaccess modal
         */
        $('#wdt-apply-columns-list').click(function (e) {
            $('#wdt-columns-list-modal').modal('hide');
            $('button.wdt-apply:eq(0)').click();
        });

        /**
         * Add column to formula on click
         */
        $(document).on('click', '#wdt-formula-editor-modal div.wdt-column-block', function (e) {
            $('#wdt-formula-editor-modal textarea').insertAtCaret($(this).data('orig_header'));
        });

        /**
         * Add operator to formula on click
         */
        $(document).on('click', '#wdt-formula-editor-modal div.wdt-formula-operators button', function (e) {
            $('#wdt-formula-editor-modal textarea').insertAtCaret($(this).text());
        });

        /**
         * Preview formula result
         */
        $('button.wdt-preview-formula').click(function (e) {
            $('#wdt-formula-editor-modal .wdt-preload-layer').animateFadeIn();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpdatatables_preview_formula_result',
                    table_id: wpdatatable_config.id,
                    formula: $('#wdt-formula-editor-modal textarea').val()
                },
                success: function (data) {
                    $('div.wdt-formula-result-preview').html(data);
                    if (!$('div.wdt-formula-result-preview').is(':visible')) {
                        $('div.wdt-formula-result-preview').fadeInDown();
                    }
                    $('#wdt-formula-editor-modal .wdt-preload-layer').animateFadeOut();
                }
            })
        });

        /**
         * Save formula
         */
        $('button.wdt-save-formula').click(function (e) {
            if (wpdatatable_config.currentOpenColumn == null) {
                // Adding a new column
                var columnName = wpdatatable_config.generateFormulaName();
                wpdatatable_config.addColumn(
                    new WDTColumn(
                        {
                            type: 'formula',
                            orig_header: columnName,
                            display_header: columnName,
                            pos: wpdatatable_config.columns.length,
                            formula: $('#wdt-formula-editor-modal textarea').val(),
                            parent_table: wpdatatable_config
                        }
                    )
                );
                $('button.wdt-apply:eq(0)').click();
            } else {
                //Updating existing column
                wpdatatable_config.currentOpenColumn.formula = $('#wdt-formula-editor-modal textarea').val();
            }
            $('#wdt-formula-editor-modal').modal('hide');
        });

        /**
         * Load the table for edit page
         */
        if (typeof wpdatatable_init_config !== 'undefined') {
            wpdatatable_config.initFromJSON(wpdatatable_init_config);
            wpdatatable_config.dataTable = $('#wpdatatable-preview-container table.wpDataTable').DataTable();
            $('div.column-settings').removeClass('hidden');
            wpdatatable_config.drawColumnSettingsButtons($('#wpdatatable-preview-container table'));
        }

        /**
         * Open WordPress media uploader
         */
        $('#wdt-browse-button').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.preventDefault();

            var mediaType;

            if ($('#wdt-table-type').val() == 'xls') {
                mediaType = 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            } else if ($('#wdt-table-type').val() == 'csv') {
                mediaType = 'text/csv';
            }

            // Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: wpdatatables_edit_strings.selectExcelCsv,
                button: {
                    text: wpdatatables_edit_strings.choose_file
                },
                multiple: false,
                library: {
                    type: mediaType
                }
            });

            // When a file is selected, grab the URL and set it as the text field value
            custom_uploader.on('select', function () {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#wdt-input-url').val(attachment.url).trigger('change');
            });

            //Open the uploader dialog
            custom_uploader.open();

        });

        /**
         * Apply tagsinput
         */
        $('#wdt-column-values-list').tagsinput({
            delimiterRegex: '|',
            tagClass: 'label label-primary'
        });
    });
})(jQuery);
