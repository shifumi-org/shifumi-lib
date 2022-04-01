/**
 * Main jQuery elements controller for the plugin settings page
 *
 * Binds the jQuery control elements for manipulating the config object, binds jQuery plugins
 *
 * @author Miljko Milosevic
 * @since 23.11.2016
 */

(function ($) {
    $(function () {

        // Handle Activation Settings
        handleActivationSettings();

        // Handle callback from Envato when activating the plugin
        authenticateEnvatoOAuthCallback();

        /**
         * Toggle Separate MySQL Connection
         */
        $('#wdt-separate-connection').change(function (e) {
            wpdatatable_plugin_config.setSeparateConnection($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Change language on select change - "Interface language"
         */
        $('#wdt-interface-language').change(function (e) {
            wpdatatable_plugin_config.setLanguage($(this).val());
        });

        /**
         * Change date format - "Date format"
         */
        $('#wdt-date-format').change(function (e) {
            wpdatatable_plugin_config.setDateFormat($(this).val());
        });

        /**
         * Number of tables on admin page - "Tables per admin page"
         */
        $('#wdt-tables-per-page').change(function (e) {
            wpdatatable_plugin_config.setTablesAdmin($(this).val());
        });

        /**
         * Change time format - "Date time"
         */
        $('#wdt-time-format').change(function (e) {
            wpdatatable_plugin_config.setTimeFormat($(this).val());
        });

        /**
         * Change base skin - "Base skin"
         */
        $('#wdt-base-skin').change(function (e) {
            wpdatatable_plugin_config.setBaseSkin($(this).val());
        });

        /**
         * Change number format - "Number format"
         */
        $('#wdt-number-format').change(function (e) {
            wpdatatable_plugin_config.setNumberFormat($(this).val());
        });

        /**
         * Change CSV delimiter - "CSV delimiter"
         */
        $('#wdt-csv-delimiter').change(function (e) {
            wpdatatable_plugin_config.setCSVDelimiter($(this).val());
        });

        /**
         * Change Table sorting direction on Browse pages
         */
        $('#wdt-sorting-order-browse-tables').change(function(e){
            wpdatatable_plugin_config.setSortingOrderBrowseTables( $(this).val() );
        });

        /**
         * Change position of advance filter - "Render advanced filter"
         */
        $('#wp-render-filter').change(function (e) {
            wpdatatable_plugin_config.setRenderPosition($(this).val());
        });

        /**
         * Set number of decimal places - "Decimal places"
         */
        $('#wdt-decimal-places').change(function (e) {
            wpdatatable_plugin_config.setDecimalPlaces($(this).val());
        });

        /**
         * Set Tablet width - "Tablet width"
         */
        $('#wdt-tablet-width').change(function (e) {
            wpdatatable_plugin_config.setTabletWidth($(this).val());
        });

        /**
         * Set Mobile width - "Tablet width"
         */
        $('#wdt-mobile-width').change(function (e) {
            wpdatatable_plugin_config.setMobileWidth($(this).val());
        });

        /**
         * Set Timepicker step in minutes - "Timepicker step"
         */
        $('#wdt-timepicker-range').change(function (e) {
            wpdatatable_plugin_config.setTimepickerStep($(this).val());
        });

        /**
         * Set Lite vs Premium Page status
         */
        $('#wdt-lite-vs-premium-page-status').change(function (e) {
            wpdatatable_plugin_config.setLiteVSPremiumPageStatus($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Getting Started Page status
         */
        $('#wdt-getting-started-page-status').change(function (e) {
            wpdatatable_plugin_config.setGettingStartedPageStatus($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Include Bootstrap
         */
        $('#wdt-include-bootstrap').change(function (e) {
            wpdatatable_plugin_config.setIncludeBootstrap($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Include Bootstrap on back-end
         */
        $('#wdt-include-bootstrap-back-end').change(function (e) {
            wpdatatable_plugin_config.setIncludeBootstrapBackEnd($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set Prevent deleting tables in database
         */
        $('#wdt-prevent-deleting-tables').change(function (e) {
            wpdatatable_plugin_config.setPreventDeletingTables($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Set SUM functions label
         */
        $('#wdt-sum-function-label').change(function (e) {
            wpdatatable_plugin_config.setSumFunctionsLabel($(this).val());
        });

        /**
         * Set AVG functions label
         */
        $('#wdt-avg-function-label').change(function (e) {
            wpdatatable_plugin_config.setAvgFunctionsLabel($(this).val());
        });

        /**
         * Set MIN functions label
         */
        $('#wdt-min-function-label').change(function (e) {
            wpdatatable_plugin_config.setMinFunctionsLabel($(this).val());
        });

        /**
         * Set MAX functions label
         */
        $('#wdt-max-function-label').change(function (e) {
            wpdatatable_plugin_config.setMaxFunctionsLabel($(this).val());
        });

        /**
         * Toggle Parse shortcodes in strings
         */
        $('#wdt-parse-shortcodes').change(function (e) {
            wpdatatable_plugin_config.setParseShortcodes($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Toggle Align numbers
         */
        $('#wdt-numbers-align').change(function (e) {
            wpdatatable_plugin_config.setAlignNumber($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Change table font
         */
        $('#wdt-table-font').change(function (e) {
            wpdatatable_plugin_config.setColorFontSetting($(this).data('name'), $(this).val());
        });

        /**
         * Change table font size
         */
        $('#wdt-font-size').change(function (e) {
            wpdatatable_plugin_config.setColorFontSetting($(this).data('name'), $(this).val());

        });

        /**
         * Change table font color
         */
        $('.wdt-color-picker').change(function (e) {
            wpdatatable_plugin_config.setColorFontSetting($(this).find('.cp-value').data('name'), $(this).find('input').val());
        });

        /**
         * Change border input radius
         */
        $('#wdt-border-input-radius').change(function (e) {
            wpdatatable_plugin_config.setColorFontSetting($(this).prop('id'), $(this).val());
        });

        /**
         * Remove borders from table
         */
        $('#wdt-remove-borders').change(function (e) {
            wpdatatable_plugin_config.setBorderRemoval($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Remove borders from header
         */
        $('#wdt-remove-borders-header').change(function (e) {
            wpdatatable_plugin_config.setBorderRemovalHeader($(this).is(':checked') ? 1 : 0);
        });
        /**
         * Set Custom Js - "Custom wpDataTables JS"
         */
        $('#wdt-custom-js').change(function (e) {
            wpdatatable_plugin_config.setCustomJs($(this).val());
        });

        /**
         * Set Custom CSS - "Custom wpDataTables CSS"
         */
        $('#wdt-custom-css').change(function (e) {
            wpdatatable_plugin_config.setCustomCss($(this).val());
        });

        /**
         * Toggle minified JS - "Use minified wpDataTables Javascript"
         */
        $('#wdt-minified-js').change(function (e) {
            wpdatatable_plugin_config.setMinifiedJs($(this).is(':checked') ? 1 : 0);
        });

        $('#wdt-activate-plugin').on('click', function () {
            if (wdt_current_config.wdtActivated == 0) {
                activatePlugin()
            } else {
                deactivatePlugin()
            }
        });

        $('#wdt-envato-activation-wpdatatables').on('click', function () {
            authenticateEnvatoOAuth()
        });

        $('#wdt-envato-deactivation-wpdatatables').on('click', function () {
            deactivatePlugin()
        });

        /**
         * Load current config on load
         */
        wpdatatable_plugin_config.setSeparateConnection(wdt_current_config.wdtUseSeparateCon == 1 ? 1 : 0);
        wpdatatable_plugin_config.setLanguage(wdt_current_config.wdtInterfaceLanguage);
        wpdatatable_plugin_config.setDateFormat(wdt_current_config.wdtDateFormat);
        wpdatatable_plugin_config.setTablesAdmin(wdt_current_config.wdtTablesPerPage);
        wpdatatable_plugin_config.setTimeFormat(wdt_current_config.wdtTimeFormat);
        wpdatatable_plugin_config.setBaseSkin(wdt_current_config.wdtBaseSkin);
        wpdatatable_plugin_config.setNumberFormat(wdt_current_config.wdtNumberFormat);
        wpdatatable_plugin_config.setCSVDelimiter(wdt_current_config.wdtCSVDelimiter);
        wpdatatable_plugin_config.setSortingOrderBrowseTables( wdt_current_config.wdtSortingOrderBrowseTables );
        wpdatatable_plugin_config.setRenderPosition(wdt_current_config.wdtRenderFilter);
        wpdatatable_plugin_config.setDecimalPlaces(wdt_current_config.wdtDecimalPlaces);
        wpdatatable_plugin_config.setTabletWidth(wdt_current_config.wdtTabletWidth);
        wpdatatable_plugin_config.setMobileWidth(wdt_current_config.wdtMobileWidth);
        wpdatatable_plugin_config.setGettingStartedPageStatus(wdt_current_config.wdtGettingStartedPageStatus== 1 ? 1 : 0);
        wpdatatable_plugin_config.setLiteVSPremiumPageStatus(wdt_current_config.wdtLiteVSPremiumPageStatus== 1 ? 1 : 0);
        wpdatatable_plugin_config.setIncludeBootstrap(wdt_current_config.wdtIncludeBootstrap == 1 ? 1 : 0);
        wpdatatable_plugin_config.setIncludeBootstrapBackEnd(wdt_current_config.wdtIncludeBootstrapBackEnd == 1 ? 1 : 0);
        wpdatatable_plugin_config.setPreventDeletingTables(wdt_current_config.wdtPreventDeletingTables == 1 ? 1 : 0);
        wpdatatable_plugin_config.setParseShortcodes(wdt_current_config.wdtParseShortcodes == 1 ? 1 : 0);
        wpdatatable_plugin_config.setAlignNumber(wdt_current_config.wdtNumbersAlign == 1 ? 1 : 0);
        wpdatatable_plugin_config.setCustomCss(wdt_current_config.wdtCustomCss);
        wpdatatable_plugin_config.setCustomJs(wdt_current_config.wdtCustomJs);
        wpdatatable_plugin_config.setMinifiedJs(wdt_current_config.wdtMinifiedJs == 1 ? 1 : 0);
        wpdatatable_plugin_config.setSumFunctionsLabel(wdt_current_config.wdtSumFunctionsLabel);
        wpdatatable_plugin_config.setAvgFunctionsLabel(wdt_current_config.wdtAvgFunctionsLabel);
        wpdatatable_plugin_config.setMinFunctionsLabel(wdt_current_config.wdtMinFunctionsLabel);
        wpdatatable_plugin_config.setMaxFunctionsLabel(wdt_current_config.wdtMaxFunctionsLabel);
        wpdatatable_plugin_config.setBorderRemoval(wdt_current_config.wdtBorderRemoval == 1 ? 1 : 0);
        wpdatatable_plugin_config.setBorderRemovalHeader(wdt_current_config.wdtBorderRemovalHeader == 1 ? 1 : 0);
        wpdatatable_plugin_config.setPurchaseCodeStore(wdt_current_config.wdtPurchaseCodeStore);

        for (var value in wdt_current_config.wdtFontColorSettings) {
            wpdatatable_plugin_config.setColorFontSetting(value, wdt_current_config.wdtFontColorSettings[value]);
        }

        /**
         * Show "Reset colors and fonts to default" when "Color and font settings" tab is active
         */
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if (target == '#color-and-font-settings') {
                $('.reset-color-settings').show();
            } else {
                $('.reset-color-settings').hide();
            }
        });

        var hash = window.location.hash;
        hash && $('.wdt-datatables-admin-wrap .plugin-settings ul.tab-nav:not(.mysql-serverside-settings-block) a[href="' + hash + '"]').tab('show');

        $('.wdt-datatables-admin-wrap .plugin-settings .tab-nav:not(.mysql-serverside-settings-block) a').click(function (e) {
            $(this).tab('show');
            var scrollmem = $('body').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });

        // Change tab on hashchange
        window.addEventListener('hashchange', function() {
            var changedHash = window.location.hash;
            changedHash && $('.wdt-datatables-admin-wrap .plugin-settings ul.tab-nav:not(.mysql-serverside-settings-block) a[href="' + changedHash + '"]').tab('show');
        }, false);

        /**
         * Reset color settings
         */
        $('.reset-color-settings').click(function (e) {
            e.preventDefault();
            $('#color-and-font-settings input.cp-value').val('').change();
            $('#color-and-font-settings .wpcolorpicker-icon i').css('background','#fff');
            wdt_current_config.wdtFontColorSettings = _.mapObject(
                wdt_current_config.wdtFontColorSettings,
                function (color) {
                    return '';
                }
            );
            $('#color-and-font-settings .selectpicker').selectpicker('val', '');
            $('input#wdt-border-input-radius').val('');
            $('input#wdt-font-size').val('');
            $('#wdt-remove-borders').prop( 'checked', false ).change();
            $('#wdt-remove-borders-header').prop( 'checked', false ).change();
        });

        /**
         * Test Separate connection settings
         */
        $('#separate-connection').find(".wdt-my-sql-test").click(function () {
            testConnections([
                getConnectionData(
                    $(this).closest(".tab-pane")
                )
            ], null);
        });

        /**
         * Switch tabs in plugin settings
         */
        $('.wdt-datatables-admin-wrap .plugin-settings .tab-nav:not(.mysql-serverside-settings-block) a').click(function (e) {
            $(this).tab('show');
        });

        /**
         * Save settings on Apply button
         */
        $(document).on('click', 'button.wdt-apply', function (e) {

            $('.wdt-preload-layer').animateFadeIn();

            if (wdt_current_config.wdtUseSeparateCon) {
                var connections = getAllConnectionData();

                if (!areConnectionsValid(connections)) {
                    return;
                }

                testConnections(connections, function () {
                    savePluginSettings(connections);
                });
            } else {
                savePluginSettings(null);
            }
        });

        /**
         * Save Google settings on Apply button
         */
        $(document).on('click', '#wdt-save-google-settings', function (e) {
            var credentials =  $('#wdt-google-sheet-settings').val();

            if (credentials != '') {
                $('.wdt-preload-layer').animateFadeIn();
                saveGoogleAccountSettings(credentials);
            } else {
                $('#wdt-error-modal .modal-body').html("Google service account data can not be empty!");
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
                return false;
            }
        });


        /**
         * Delete Google settings
         */
        $(document).on('click', '#wdt-delete-google-settings', function (e) {
            $('.wdt-preload-layer').animateFadeIn();
            deleteGoogleAccountSettings();
        });

        /**
         * Add Connection
         */
        $('#wp-my-sql-add').click(function () {
            addNewConnection();
        });

        /**
         * Add ace editor on Global custom CSS
         */
        createAceEditor('wdt-custom-css');

        /**
         * Add ace editor on Global custom JS
         */
        createAceEditor('wdt-custom-js');

        /**
         * Change connection default status
         */
        function changeDefaultConnection(element) {
            var checked = $(element).is(':checked') ? 1 : 0;

            if (checked) {
                $(".wdt-my-sql-default-checkbox").prop('checked', false);
            }

            $(element).prop('checked', checked);
        }

        $(".wdt-my-sql-default-checkbox").change(function (e) {
            changeDefaultConnection(this);
        });

        /**
         * Name the connection
         */
        $("#separate-connection").find("input[name='wdt-my-sql-name']").on('input', function (e) {
            changeConnectionName(this.value);
        });

        /**
         * Delete the connection
         */
        $(".wdt-my-sql-delete").click(function (e) {
            deleteConnection(this);
        });

        /**
         * Change connection name
         */
        function changeConnectionName(value) {
            if (value.match(/[^a-zA-Z0-9 ]/g)) {
                value = value.replace(/[^a-zA-Z0-9 ]/g, '');
            }

            $("#separate-connection").find(".tab-nav .active").find("a").text(value ? value : 'New Connection');
        }

        /**
         * Add new connection
         */
        function addNewConnection() {
            var element = $("#separate-connection");

            var count = parseInt(element.attr("data-count") ? element.attr("data-count") : "0");
            element.attr("data-count", count + 1);

            // Navigation
            var navigation = element.find(".tab-nav");
            navigation.find("a").parent().removeClass("active");

            var newConnectionNav = $('<li class="active"><a href="#connection' + count + '" aria-controls="connection-' + count + '" role="tab" data-toggle="tab" style="text-transform: none;">New Connection</a></li>');
            navigation.append(newConnectionNav);

            // Content
            element.find(".tab-content").children().removeClass("active");
            var content = $("#separate-connection-form").find(".tab-pane");

            var newConnectionContent = content.clone(false);

            newConnectionContent.attr("id", "connection" + count);
            newConnectionContent.find("input").attr("value", "");
            newConnectionContent.addClass("active");

            newConnectionContent.find(".select-vendor").html('<select class="selectpicker wdt-my-sql-vendor" name="wdt-my-sql-vendor">' +
                '<option value="" disabled selected></option>' +
                '<option value="mysql">MySQL</option>' +
                '<option value="mssql">MSSQL</option>' +
                '<option value="postgresql">PostgreSQL</option>' +
                '</select>');

            newConnectionContent.find(".select-vendor").find("select").selectpicker();

            newConnectionContent.find(".select-driver").html('<select class="selectpicker wdt-sql-driver" name="wdt-sql-driver">' +
              '<option value="" disabled selected></option>' +
              '<option value="dblib">DBLIB</option>' +
              '<option value="sqlsrv">SQLSRV</option>' +
              '<option value="odbc">ODBC</option>' +
              '</select>');

            newConnectionContent.find(".select-driver").find("select").selectpicker();

            newConnectionContent.find("select option[value='']").prop("selected", true);
            newConnectionContent.find(".wdt-my-sql-default-checkbox").attr("id", "wdt-my-sql-default-" + count);
            newConnectionContent.find(".wdt-my-sql-default-checkbox").prop('checked', false);
            newConnectionContent.find(".wdt-my-sql-default-checkbox").change(function (e) {
                changeDefaultConnection(this);
            });

            newConnectionContent.find(".wdt-my-sql-default-label").attr("for", "wdt-my-sql-default-" + count);
            newConnectionContent.find(".wdt-my-sql-test").click(function () {
                testConnections([
                    getConnectionData(
                        $(this).closest(".tab-pane")
                    )
                ], null);
            });
            newConnectionContent.find(".wdt-my-sql-delete").click(function () {
                deleteConnection(this);
            });
            newConnectionContent.find("input[name='wdt-my-sql-name']").on('input', function (e) {
                changeConnectionName(this.value);
            });

            var connections = getAllConnectionData();
            var connectionIds = []

            for (var i = 0; i < connections.length; i++) {
                connectionIds.push(connections[i].id)
            }

            while ((id = Math.random().toString(36).substr(2, 16)) && !(connectionIds.indexOf(id) === -1)) {
                id = Math.random().toString(36).substr(2, 16);
            }

            newConnectionContent.find("input[name='wdt-my-sql-id']").val(id);

            element.find(".tab-content").append(newConnectionContent);

            changeConnectionVendor(newConnectionContent)
        }

        function changeConnectionVendor(element) {
            var selectVendorElement = $(element).find('.wdt-my-sql-vendor');

            selectVendorElement.change(function (e) {
                var vendor = selectVendorElement.find(":selected").val();
                var defaultPort = '';

                if (vendor === "mysql")
                    defaultPort = '3306';
                else if (vendor === "mssql")
                    defaultPort = '1433';
                else if (vendor === "postgresql")
                    defaultPort = '5432';

                $(element).find("input[name='wdt-my-sql-port']").val(defaultPort);

                $(element).find('.wpdt-icon-info-circle-thin.connection-port').attr('title', 'Port for the connection' + (defaultPort ? ' (default: ' + defaultPort + ')' : '')).tooltip('fixTitle');

                setTimeout(function () {
                    $(element).tooltip({
                        selector: '[data-toggle="tooltip"]'
                    });
                }, 500);
            });
        }

        $(".separate-connection").each(function (index, connectionContent) {
            changeConnectionVendor(connectionContent)
        })

        function deleteConnection(element) {
            $('#wdt-delete-modal').modal('show');

            var confirmButton = $('#wdt-delete-modal').find('#wdt-browse-delete-button');

            confirmButton.unbind("click");
            confirmButton.click(function () {
                $("#separate-connection").find(".tab-nav .active").remove();

                $(element).closest(".tab-pane").remove();

                $('#wdt-delete-modal').modal('hide');

                savePluginSettings(getAllConnectionData());
            });
        }

        function getConnectionData(tab) {
            return {
                host: tab.find("input[name='wdt-my-sql-host']").val(),
                database: tab.find("input[name='wdt-my-sql-db']").val(),
                user: tab.find("input[name='wdt-my-sql-user']").val(),
                password: tab.find("input[name='wdtMySqlPwd']").val(),
                port: tab.find("input[name='wdt-my-sql-port']").val(),
                vendor: tab.find("select[name='wdt-my-sql-vendor'] option:selected").val(),
                driver: tab.find("select[name='wdt-sql-driver'] option:selected").val(),
                name: tab.find("input[name='wdt-my-sql-name']").val(),
                id: tab.find("input[name='wdt-my-sql-id']").val(),
                default: tab.find(".wdt-my-sql-default-checkbox").is(':checked') ? 1 : 0
            };
        }

        function getAllConnectionData() {
            var connections = [];

            $("#separate-connection").find(".tab-pane").each(function (index) {
                connections.push(getConnectionData(
                    $(this).closest(".tab-pane"))
                );
            });

            return connections;
        }

        function testConnections(connections, callback) {
            $('.wdt-preload-layer').animateFadeIn();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpdatatables_test_separate_connection_settings',
                    wdtSeparateCon: connections,
                    wdtNonce: $('#wdtNonce').val()

                },
                success: function (data) {
                    $('.wdt-preload-layer').animateFadeOut();
                    if (data.errors.length > 0) {
                        var errorMessage = '';
                        for (var i in data.errors) {
                            errorMessage += data.errors[i] + '<br/>';
                        }
                        // Show error if returned
                        $('#wdt-error-modal .modal-body').html(errorMessage);
                        $('#wdt-error-modal').modal('show');
                        return;

                    } else if (data.success.length > 0) {
                        var successMessage = '';
                        for (var i in data.success) {
                            successMessage += data.success[i] + '<br/>';
                        }
                        if (callback !== null) {
                            callback();
                        }
                        // Show success message
                        wdtNotify(
                            wpdatatables_edit_strings.success,
                            successMessage,
                            'success'
                        );
                    }
                }
            });
        }

        function areConnectionsValid(connections) {
            // check if connections have duplicate names
            var connectionsNames = [];

            for (var i = 0; i < connections.length; i++) {
                connectionsNames.push(connections[i]['name'].toLowerCase());
            }

            connectionsNames = connectionsNames.sort();

            for (var i = 0; i < connectionsNames.length - 1; i++) {
                if (connectionsNames[i + 1] === connectionsNames[i]) {
                    $('#wdt-error-modal .modal-body').html("Connections can't have same names!");
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                    return false;
                }
            }

            // check if connections have all parameters
            for (var i = 0; i < connections.length; i++) {
                if (connections[i]['name'].trim() === '' ||
                    connections[i]['database'].trim() === '' ||
                    connections[i]['host'].trim() === '' ||
                    connections[i]['port'].trim() === '' ||
                    connections[i]['user'].trim() === '' ||
                    connections[i]['password'].trim() === '' ||
                    connections[i]['vendor'].trim() === '' ||
                    connections[i]['driver'].trim() === ''
                ) {
                    $("#separate-connection").find(".tab-pane").each(function (index) {
                        var tab = $(this).closest(".tab-pane");

                        if (tab.find("input[name='wdt-my-sql-name']").val() === connections[i]['name']) {
                            var connectionTab = $('a[href$="' + tab.attr('id') + '"]:first');

                            $(connectionTab).parent().parent().children().removeClass('active');
                            $(connectionTab).parent().addClass('active');

                            $(".separate-connection-tab").parent().children().removeClass('active');
                            $(".separate-connection-tab").addClass('active');

                            $('.tab-pane').each(function (index) {
                                $(this).removeClass('active');
                            });

                            $("#" + tab.attr('id')).addClass('active');
                            $("#separate-connection").addClass('active');
                        }
                    });

                    $('#wdt-error-modal .modal-body').html("Please insert connection parameters!");
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                    return false;
                }
            }

            return true;
        }

        function saveGoogleAccountSettings(credentials) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpdatatables_save_google_settings',
                    settings: credentials,
                    wdtNonce: $('#wdtNonce').val()
                },
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.error){
                        $('#wdt-error-modal .modal-body').html('There was an error while trying to save google settings!\n ' + data.error);
                        $('#wdt-error-modal').modal('show');
                        $('.wdt-preload-layer').animateFadeOut();
                    } else {
                        window.location = data.link;
                        window.location.reload();
                    }

                },
                error: function (){
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to save google settings!');
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            });
        }

        function deleteGoogleAccountSettings() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpdatatables_delete_google_settings',
                    wdtNonce: $('#wdtNonce').val()
                },
                success: function (link) {
                    window.location = link;
                    window.location.reload();
                },
                error: function (data){
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to delete google settings! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            });
        }

        function savePluginSettings(connections) {
            if (connections !== null) {
                wdt_current_config.wdtSeparateCon = JSON.stringify(connections);
            }
            var wdt_temp_config = wdt_current_config,
                wdtRemovePurchaseCodeProp = ['wdtPurchaseCodeStore',
                    'wdtPurchaseCodeStorePowerful', 'wdtPurchaseCodeStoreGravity',
                    'wdtPurchaseCodeStoreFormidable', 'wdtPurchaseCodeStoreMasterDetail',
                    'wdtPurchaseCodeStoreReport'];
            wdt_current_config = _.omit(wdt_current_config, wdtRemovePurchaseCodeProp);

            $.ajax({
                url: ajaxurl,
                dataType: 'text',
                method: 'POST',
                data: {
                    action: 'wpdatatables_save_plugin_settings',
                    settings: wdt_current_config,
                    wdtNonce: $('#wdtNonce').val()
                },
                success: function () {
                    $('.wdt-preload-layer').animateFadeOut();
                    wdtNotify(
                        wpdatatables_edit_strings.success,
                        wpdatatables_edit_strings.settings_saved_successful,
                        'success'
                    );
                    wdt_current_config = wdt_temp_config;
                },
                error: function (){
                    $('.wdt-preload-layer').animateFadeOut();
                    wdtNotify(
                        wpdatatablesSettingsStrings.error,
                        wpdatatablesSettingsStrings.settings_saved_error,
                        'danger'
                    );
                    wdt_current_config = wdt_temp_config;
                }
            });
        }

        function activatePlugin() {
            $('#wdt-activate-plugin').html('<i class="wpdt-icon-spinner9"></i>Loading...');

            let domain    = location.hostname;
            let subdomain = location.hostname;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_activate_plugin',
                    purchaseCodeStore: $('#wdt-purchase-code-store').val().trim(),
                    wdtNonce: $('#wdtNonce').val(),
                    slug: 'wpdatatables',
                    domain: domain,
                    subdomain: subdomain
                },
                success: function (response) {
                    let valid = JSON.parse(response).valid;
                    let domainRegistered = JSON.parse(response).domainRegistered;

                    if (valid === true && domainRegistered === true) {
                        wdt_current_config.wdtActivated = 1;
                        wdt_current_config.wdtPurchaseCodeStore = 1;
                        wdtNotify(wpdatatablesSettingsStrings.success, wpdatatablesSettingsStrings.pluginActivated, 'success');
                        $('#wdt-purchase-code-store').val('');
                        $('.wdt-purchase-code-store-wrapper').hide();
                        $('.wdt-purchase-code .wdt-security-massage-wrapper').removeClass('hidden');
                        $('#wdt-activate-plugin').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                        $('.wdt-envato-activation-wpdatatables').hide()
                    } else if (valid === false) {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.purchaseCodeInvalid, 'danger');
                        $('#wdt-activate-plugin').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
                    } else {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
                        $('#wdt-activate-plugin').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
                    }
                },
                error: function () {
                    wdt_current_config.wdtActivated = 0;
                    wdtNotify(wpdatatablesSettingsStrings.error, 'Unable to activate the plugin. Please try again.', 'danger');
                    $('#wdt-activate-plugin').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
                }
            });
        }

        function deactivatePlugin() {
            $('#wdt-activate-plugin').html('<i class="wpdt-icon-spinner9"></i>Loading...');
            $('#wdt-envato-deactivation-wpdatatables').html('<i class="fad fa-spinner"></i>Loading...');

            let domain    = location.hostname;
            let subdomain = location.hostname;

            let params = {
                action: 'wpdatatables_deactivate_plugin',
                wdtNonce: $('#wdtNonce').val(),
                domain: domain,
                subdomain: subdomain,
                slug: 'wpdatatables',
            };

            if (wdt_current_config.wdtPurchaseCodeStore) {
                params.type = 'code';
                params.envatoTokenEmail = '';
            } else if (wdt_current_config.wdtEnvatoTokenEmail) {
                params.type = 'envato';
                params.envatoTokenEmail = wdt_current_config.wdtEnvatoTokenEmail;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: params,
                success: function (response) {
                    var parsedResponse = JSON.parse(response);
                    if (parsedResponse.deactivated === true) {
                        wdt_current_config.wdtEnvatoTokenEmail = '';
                        wdt_current_config.wdtActivated = 0;
                        wdt_current_config.wdtPurchaseCodeStore = 0;
                        $('#wdt-purchase-code-store').val('');
                        $('.wdt-purchase-code-store-wrapper').show();
                        $('.wdt-purchase-code .wdt-security-massage-wrapper').addClass('hidden');
                        $('#wdt-activate-plugin').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
                        $('.wdt-envato-activation-wpdatatables').show()
                        $('.wdt-preload-layer').animateFadeOut();
                        $('#wdt-envato-activation-wpdatatables span').text(wpdatatablesSettingsStrings.activateWithEnvato);
                        $('#wdt-envato-activation-wpdatatables').prop('disabled', '');
                        $('#wdt-envato-deactivation-wpdatatables').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate').hide();
                        $('.wdt-purchase-code').show();
                    } else {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.unable_to_deactivate_plugin, 'danger');
                        $('#wdt-activate-plugin').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                        $('#wdt-envato-deactivation-wpdatatables').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                    }
                }
            });
        }

        function authenticateEnvatoOAuth() {
            let domain    = location.hostname;
            let subdomain = location.hostname;
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_parse_server_name',
                    wdtNonce: $('#wdtNonce').val(),
                    domain: domain,
                    subdomain: subdomain
                },
                success: function (response) {
                    let serverName = JSON.parse(response);
                    let domain = serverName.domain;
                    let subdomain = serverName.subdomain;
                    window.location.replace(
                        wdtStore.url + 'activation/envato?slug=wpdatatables&domain=' + domain + '&subdomain=' + subdomain + '&redirectUrl=' + wdtStore.redirectUrl + '/wp-admin/admin.php?page=wpdatatables-settings'
                    )

                }

            });
        }

        function authenticateEnvatoOAuthCallback() {
            // Get value of valid query parameter
            var valid = searchQueryString('valid');
            var domainRegistered = searchQueryString('domainRegistered');
            var slug = searchQueryString('slug');

            if (valid !== null && slug === 'wpdatatables') {

                // Remove query parameters sent back from TMS Store
                let redirectURL = this.removeURLParameter(window.location.href, 'valid');
                redirectURL = this.removeURLParameter(redirectURL, 'slug');
                redirectURL = this.removeURLParameter(redirectURL, 'domainRegistered');

                $('.tab-nav a[href="#wdt-activation"]').tab('show');

                if (valid === 'true' && domainRegistered === 'true' && searchQueryString('envatoTokenEmail')) {
                    // Set refresh token
                    wdt_current_config.wdtEnvatoTokenEmail = searchQueryString('envatoTokenEmail');
                    // Set activated
                    wdt_current_config.wdtActivated = 1;

                    // Change button text and disable it
                    $('#wdt-envato-activation-wpdatatables span').text(wpdatatablesSettingsStrings.envato_api_activated);
                    $('#wdt-envato-activation-wpdatatables').prop('disabled', 'disabled');
                    $('.wdt-purchase-code').hide();
                    $('#wdt-envato-deactivation-wpdatatables').show();

                    // Save plugin settings
                    $.ajax({
                        url: ajaxurl,
                        dataType: 'json',
                        method: 'POST',
                        data: {
                            action: 'wpdatatables_save_plugin_settings',
                            settings: wdt_current_config,
                            wdtNonce: $('#wdtNonce').val()
                        },
                        success: function () {
                            $('.wdt-preload-layer').animateFadeOut();
                            wdtNotify(wpdatatables_edit_strings.success, wpdatatables_edit_strings.pluginActivated, 'success');
                        }
                    });

                    redirectURL = this.removeURLParameter(redirectURL, 'envatoTokenEmail')
                } else if (valid === 'false') {
                    wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_envato_failed, 'danger');
                } else if (domainRegistered === 'false') {
                    wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
                }

                window.history.pushState(null, null, redirectURL)
            }
        }

        function handleActivationSettings() {
            var activeTab = searchQueryString('activeTab');
            var wdtConvertPurchaseCodeInt = ['wdtPurchaseCodeStore',
                'wdtPurchaseCodeStorePowerful', 'wdtPurchaseCodeStoreGravity',
                'wdtPurchaseCodeStoreFormidable', 'wdtPurchaseCodeStoreMasterDetail',
                'wdtPurchaseCodeStoreReport'];

            for (var configName in wdt_current_config) {
                if (wdtConvertPurchaseCodeInt.includes(configName) && wdt_current_config.hasOwnProperty(configName)) {
                    wdt_current_config[configName] = parseInt(wdt_current_config[configName])
                }
            }

            if (activeTab === 'activation') {
                $('.tab-nav a[href="#wdt-activation"]').tab('show');
            }

            if (wdt_current_config.wdtActivated == 1) {
                if (wdt_current_config.wdtEnvatoTokenEmail) {
                    // Change button text and disable it
                    $('#wdt-envato-activation-wpdatatables span').text(wpdatatablesSettingsStrings.envato_api_activated);
                    $('#wdt-envato-activation-wpdatatables').prop('disabled', 'disabled');
                    $('#wdt-envato-deactivation-wpdatatables').show()
                    $('.wdt-purchase-code').hide()
                } else {
                    $('.wdt-envato-activation-wpdatatables').hide()
                }
            }
        }
    });
})(jQuery);

