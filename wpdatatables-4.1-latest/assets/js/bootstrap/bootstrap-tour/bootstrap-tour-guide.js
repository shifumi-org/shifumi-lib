(function ($) {
    $(function () {
        var $demo0, $demo1, $demo2, $demo3, $demo4, $demo5, $demo6, tour0, tour1, tour2, tour3, tour4, tour5, tour6;

        $demo0 = $("#wdt-tutorial-simple-table");
        $demo1 = $("#wdt-tutorial-data-source");
        $demo2 = $("#wdt-tutorial-create-manual");
        $demo3 = $("#wdt-tutorial-data-import");
        $demo4 = $("#wdt-tutorial-wordpress-database");
        $demo5 = $("#wdt-tutorial-mysql-database");
        $demo6 = $("#wdt-tutorial-create-charts");

        var invalidStep = -1;

        function validateStepInput(tour) {
            var currentStep = tour.getCurrentStep();
            var stepName = tour._options.name;
            switch (stepName) {
                case 'create-table-data-source':
                    var inputURL = $('#wdt-input-url');
                    var inputQuery = $('#wdt-mysql-query')
                    var inputQueryData = $('#wdt-mysql-query .ace_identifier')
                    var selectBox =  $('.wdt-input-data-source-type .bootstrap-select ul.dropdown-menu li.selected:not([data-original-index="0"])')


                    if (inputURL.is(":visible") && inputURL.val() === '' && currentStep === 6) {
                        invalidStep = tour.getCurrentStep();
                    }

                    if (!selectBox.length) {
                        invalidStep = tour.getCurrentStep();
                    }

                    if (inputQuery.is(":visible") && !inputQueryData.length && currentStep === 7) {
                        invalidStep = tour.getCurrentStep();
                    }
                    break;
                case 'create-table-import-data':
                    var inputImportURL = $('#wdt-constructor-input-url');

                    if (inputImportURL.is(":visible") && inputImportURL.val() === '') {
                        invalidStep = tour.getCurrentStep();
                    }
                    break;
                case 'create-table-from-mysql-database':
                    var inputMySQLColumns = $('#wdt-constructor-mysql-columns-selected-table tr')

                    if (!inputMySQLColumns.length) {
                        invalidStep = tour.getCurrentStep();
                    }
                    break;
                case 'create-table-from-wordpress-database':
                    var inputPostColumns = $('#wdt-constructor-post-columns-selected-table tr')

                    if (!inputPostColumns.length) {
                        invalidStep = tour.getCurrentStep();
                    }
                    break;
                case 'create-chart':
                    var disabledNextButton = $('#wdt-chart-wizard-next-step');
                    var selectedChartType = $('.wdt-chart-wizard-chart-selecter-block .card.selected');
                    var googleCharts = $('.charts-type.google-charts-type');
                    var HighCharts = $('.charts-type.highcharts-charts-type');
                    var ChartsJS = $('.charts-type.chartjs-charts-type');
                    var ApexCharts = $('.charts-type.apex-charts-type');

                    if ((!selectedChartType.length && disabledNextButton.is(":disabled") && googleCharts.is(":visible") && currentStep === 7) ||
                        (!selectedChartType.length && disabledNextButton.is(":disabled") && HighCharts.is(":visible") && currentStep === 8) ||
                        (!selectedChartType.length && disabledNextButton.is(":disabled") && ApexCharts.is(":visible") && currentStep === 9) ||
                        (!selectedChartType.length && disabledNextButton.is(":disabled") && ChartsJS.is(":visible") && currentStep === 10)) {
                        invalidStep = tour.getCurrentStep();
                    }
                    if (!selectedChartType.length && !disabledNextButton.is(":disabled") && googleCharts.is(":visible") ||
                        !selectedChartType.length && !disabledNextButton.is(":disabled") && HighCharts.is(":visible") ||
                        !selectedChartType.length && !disabledNextButton.is(":disabled") && ChartsJS.is(":visible") ||
                        !selectedChartType.length && !disabledNextButton.is(":disabled") && ApexCharts.is(":visible")) {
                        invalidStep = tour.getCurrentStep();
                    }
                    if (disabledNextButton.is(":disabled") && currentStep === 16) {
                        invalidStep = tour.getCurrentStep();
                    }
                    break;
            }


            return invalidStep === -1;
        }

        function checkPreviousStepValid(tour) {

            // .goTo only seems to work in the onShown step event so I had to put this check
            // on the next step's onShown event in order to redisplay the previous step with
            // the error
            if (invalidStep > -1) {
                var tempStep = invalidStep;
                var currentStep = tour.getCurrentStep();
                var stepName = tour._options.name;
                var errorMessage = '';
                invalidStep = -1;
                tour.goTo(tempStep);
                switch (stepName) {
                    case 'create-chart':
                        if (currentStep === 11) {
                            errorMessage = wpdtTutorialStrings.cannot_be_empty_chart_type;
                        } else if (currentStep === 14) {
                            errorMessage = wpdtTutorialStrings.cannot_be_empty_chart_table;
                        } else if (currentStep === 17) {
                            errorMessage = wpdtTutorialStrings.cannot_be_empty_chart_table_columns;
                        }
                        break;
                    default:
                        errorMessage = wpdtTutorialStrings.cannot_be_empty_field;
                        break;

                }
                wdtNotify(
                    errorMessage,
                    '',
                    'danger'
                )
            }
        }

        tour0 = new Tour({
            name: "create-simple-table",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step0.title,
                    content: wpdtTutorialStrings.tour0.step0.content,
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour0.step1.title,
                    content: wpdtTutorialStrings.tour0.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-first-row .wdt-constructor-type-selecter-block:nth-child(1) .card",
                    placement: "right",
                    title: wpdtTutorialStrings.tour0.step2.title,
                    content: wpdtTutorialStrings.tour0.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="simple"])').addClass('disabled');
                        window.localStorage.removeItem('create-simple-table_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour0.step3.title,
                    content: wpdtTutorialStrings.tour0.step3.content,
                    reflex: true,
                    backdrop: true,
                    backdropPadding: 5,
                    duration: 3000,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="simple"])').addClass('disabled');
                    },
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                    }
                }, {
                    // step 4
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step4.title,
                    content: wpdtTutorialStrings.tour0.step4.content,
                }, {
                    // step 5
                    element: "#wdt-constructor-simple-table-name",
                    placement: "top",
                    title: wpdtTutorialStrings.tour0.step5.title,
                    content: wpdtTutorialStrings.tour0.step5.content,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 6
                    element: "#wdt-simple-table-number-of-columns",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step6.title,
                    content: wpdtTutorialStrings.tour0.step6.content,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 7
                    element: "#wdt-simple-table-number-of-rows",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step7.title,
                    content: wpdtTutorialStrings.tour0.step7.content,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 8
                    element: "#wdt-simple-table-constructor",
                    placement: "left",
                    title: wpdtTutorialStrings.tour0.step8.title,
                    content: wpdtTutorialStrings.tour0.step8.content,
                    reflex: true,
                    backdrop: true,
                    redirect:false,
                    backdropContainer: '#wdt-tour-actions',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 9
                    redirect:false,
                    orphan: true,
                    element: "#edit-table-settings",
                    placement: "top",
                    title: wpdtTutorialStrings.tour0.step9.title,
                    content: wpdtTutorialStrings.tour0.step9.content,

                }, {
                    // step 10
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour0.step10.title,
                    content: wpdtTutorialStrings.tour0.step10.content,

                }, {
                    // step 11
                    element: "#wpdt-table-editor",
                    placement: "top",
                    title: wpdtTutorialStrings.tour0.step11.title,
                    content: wpdtTutorialStrings.tour0.step11.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wpdt-table-editor').css("z-index", "1101");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wpdt-table-editor').css("z-index", "10");
                    }
                }, {
                    // step 11
                    element: "#wpdt-cell-action-buttons",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step12.title,
                    content: wpdtTutorialStrings.tour0.step12.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                }, {
                    // step 12
                    element: "#wpdt-views .nav.nav-pills",
                    placement: "right",
                    title: wpdtTutorialStrings.tour0.step13.title,
                    content: wpdtTutorialStrings.tour0.step13.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 13
                    element: "#wpdt-view-container",
                    placement: "top",
                    title: wpdtTutorialStrings.tour0.step14.title,
                    content: wpdtTutorialStrings.tour0.step14.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 14
                    element: "#wdt-table-id",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour0.step15.title,
                    content: wpdtTutorialStrings.tour0.step15.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                },
            ],
            template: function () {
                var showButtons = '';
                var tour0NextButtonSteps = [4, 5, 6, 7, 9, 10, 11, 12, 13, 14];
                if (typeof tour0 == 'undefined' && localStorage.getItem("create-simple-table_current_step") !== null) {
                    window.localStorage.removeItem('create-simple-table_current_step');
                    window.localStorage.removeItem('create-simple-table_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour0.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour0.getCurrentStep(), tour0NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour0.getCurrentStep() === 15) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-simple-table_current_step');
                window.localStorage.removeItem('create-simple-table_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('.wdt-constructor-type-selecter-block .card:not([data-value="simple"])').removeClass('disabled');
            }
        }).init();

        tour1 = new Tour({
            name: "create-table-data-source",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour1.step0.title,
                    content: wpdtTutorialStrings.tour1.step0.content,
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour1.step1.title,
                    content: wpdtTutorialStrings.tour1.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-first-row .wdt-constructor-type-selecter-block:nth-child(2) .card",
                    placement: "left",
                    title: wpdtTutorialStrings.tour1.step2.title,
                    content: wpdtTutorialStrings.tour1.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="source"])').addClass('disabled');
                        window.localStorage.removeItem('create-table-data-source_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour1.step3.title,
                    content: wpdtTutorialStrings.tour1.step3.content,
                    reflex: true,
                    redirect: false,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="source"])').removeClass('disabled');
                    }
                }, {
                    // step 4
                    redirect: false,
                    element: ".wdt-input-data-source-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour1.step4.title,
                    content: wpdtTutorialStrings.tour1.step4.content,
                    reflex: true,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-input-data-source-type .bootstrap-select').css("background-color", "#FFFFFF");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-input-data-source-type .bootstrap-select').css("background-color", "inherit");
                    }
                }, {
                    // step 5
                    element: "#wdt-table-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour1.step5.title,
                    content: wpdtTutorialStrings.tour1.step5.content,
                    onShown: function (tour) {
                        $('#wdt-browse-button').prop('disabled', true);
                        $('#wdt-input-url').prop('disabled', true);
                    },
                    onHidden: function (tour) {
                        $('#wdt-browse-button').prop('disabled', false);
                        $('#wdt-input-url').prop('disabled', false);
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 6
                    element: ".input-path-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour1.step6.title,
                    content: wpdtTutorialStrings.tour1.step6.content,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('[data-id="wdt-table-type"]').css({
                            'cssText': "cursor:not-allowed;background-color:#eeeeee !important"
                        });
                        $('#wdt-table-type').prop('disabled', true);
                    },
                    onHidden: function (tour) {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 7
                    element: ".mysql-settings-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour1.step7.title,
                    content: wpdtTutorialStrings.tour1.step7.content,
                    backdrop: true,
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-table-type').prop('disabled', true);
                        $('[data-id="wdt-table-type"]').css({
                            'cssText': "cursor:not-allowed;background-color:#eeeeee !important"
                        });
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 8
                    element: ".wdt-table-settings .card-header .btn.wdt-apply",
                    placement: "left",
                    title: wpdtTutorialStrings.tour1.step8.title,
                    content: wpdtTutorialStrings.tour1.step8.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: '#wdt-tour-actions',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        if (typeof wpdatatable_config !== 'undefined'){
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
                                        tour.prev();
                                        wdtNotify(
                                            wpdtTutorialStrings.error_data_source,
                                            '',
                                            'danger'
                                        )
                                    }
                                }
                            });
                        }

                    }
                }, {
                    // step 9
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour1.step9.title,
                    content: wpdtTutorialStrings.tour1.step9.content,
                    onShown: function (tour) {
                        $('#wdt-table-type').prop('disabled', false);
                        $('[data-id="wdt-table-type"]').css({
                            'cssText': "cursor:pointer;background-color:white !important"
                        });
                    }
                }, {
                    // step 10
                    element: "#wdt-table-id",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour1.step10.title,
                    content: wpdtTutorialStrings.tour1.step10.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }
            ],
            template: function () {
                var showButtons = '';
                var tour1NextButtonSteps = [5, 6, 7, 9];
                if (typeof tour1 == 'undefined' && localStorage.getItem("create-table-data-source_current_step") !== null) {
                    window.localStorage.removeItem('create-table-data-source_current_step');
                    window.localStorage.removeItem('create-table-data-source_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour1.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour1.getCurrentStep(), tour1NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour1.getCurrentStep() === 10) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-table-data-source_current_step');
                window.localStorage.removeItem('create-table-data-source_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('.wdt-constructor-type-selecter-block .card:not([data-value="source"])').removeClass('disabled');
                $('#wdt-browse-button').prop('disabled', false);
                $('#wdt-input-url').prop('disabled', false);
                $('#wdt-table-type').prop('disabled', false);
                $('[data-id="wdt-table-type"]').css({
                    'cssText': "cursor:pointer;background-color:white !important"
                });
            }
        }).init();

        tour2 = new Tour({
            name: "create-manual-table",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour2.step0.title,
                    content: wpdtTutorialStrings.tour2.step0.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour2.step1.title,
                    content: wpdtTutorialStrings.tour2.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-second-row .wdt-constructor-type-selecter-block:nth-child(1) .card",
                    placement: "right",
                    title: wpdtTutorialStrings.tour2.step2.title,
                    content: wpdtTutorialStrings.tour2.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="manual"])').addClass('disabled');
                        window.localStorage.removeItem('create-manual-table_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour2.step3.title,
                    content: wpdtTutorialStrings.tour2.step3.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    duration: 3000,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-type-selecter-block .card:not([data-value="manual"])').addClass('disabled');
                    },
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                    }
                }, {
                    // step 4
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour2.step4.title,
                    content: wpdtTutorialStrings.tour2.step4.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 5
                    element: ".wdt-constructor-table-name-and-columns",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour2.step5.title,
                    content: wpdtTutorialStrings.tour2.step5.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 6
                    element: ".wdt-datatables-admin-wrap > div.container > div > div > div.card-body.card-padding > div:nth-child(2) > div.row.wdt-constructor-columns-container",
                    placement: "top",
                    title: wpdtTutorialStrings.tour2.step6.title,
                    content: wpdtTutorialStrings.tour2.step6.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-create-buttons button').prop('disabled', false);
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 7
                    element: "#wpdt-control-buttons > div > button",
                    placement: "left",
                    title: wpdtTutorialStrings.tour2.step7.title,
                    content: wpdtTutorialStrings.tour2.step7.content,
                    backdrop: true,
                    backdropContainer: '.wdt-constructor-create-buttons',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-constructor-previous-step').prop('disabled', false);
                    }
                }

            ],
            template: function () {
                var showButtons = '';
                var tour2SkipButtonSteps = [1, 2, 3];
                if (typeof tour2 == 'undefined' && localStorage.getItem("create-manual-table_current_step") !== null) {
                    window.localStorage.removeItem('create-manual-table_current_step');
                    window.localStorage.removeItem('create-manual-table_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour2.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour2.getCurrentStep(), tour2SkipButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour2.getCurrentStep() === 7) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-manual-table_current_step');
                window.localStorage.removeItem('create-manual-table_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('.wdt-constructor-type-selecter-block .card:not([data-value="manual"])').removeClass('disabled');
                $('.wdt-constructor-create-buttons button').prop('disabled', false);
                $('#wdt-constructor-previous-step').prop('disabled', false);
            }
        }).init();

        tour3 = new Tour({
            name: "create-table-import-data",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour3.step0.title,
                    content: wpdtTutorialStrings.tour3.step0.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour3.step1.title,
                    content: wpdtTutorialStrings.tour3.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-second-row .wdt-constructor-type-selecter-block:nth-child(2) .card",
                    placement: "left",
                    title: wpdtTutorialStrings.tour3.step2.title,
                    content: wpdtTutorialStrings.tour3.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        window.localStorage.removeItem('create-table-import-data_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }

                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour3.step3.title,
                    content: wpdtTutorialStrings.tour3.step3.content,
                    reflex: true,
                    duration: 3000,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 4
                    element: ".input-path-block",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour3.step4.title,
                    content: wpdtTutorialStrings.tour3.step4.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-constructor-next-step').prop('disabled', false);
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 5
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour3.step5.title,
                    content: wpdtTutorialStrings.tour3.step5.content,
                    reflex: true,
                    duration: 3000,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 6
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour3.step6.title,
                    content: wpdtTutorialStrings.tour3.step6.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 7
                    element: ".wdt-constructor-file-table-name-block",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour3.step7.title,
                    content: wpdtTutorialStrings.tour3.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 8
                    element: ".wdt-datatables-admin-wrap > div.container > div > div > div.card-body.card-padding > div:nth-child(17) > div.row.wdt-constructor-columns-container",
                    placement: "top",
                    title: wpdtTutorialStrings.tour3.step8.title,
                    content: wpdtTutorialStrings.tour3.step8.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-create-buttons button').prop('disabled', false)
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 9
                    element: "#wpdt-control-buttons > div > button",
                    placement: "left",
                    title: wpdtTutorialStrings.tour3.step9.title,
                    content: wpdtTutorialStrings.tour3.step9.content,
                    backdrop: true,
                    backdropContainer: '.wdt-constructor-create-buttons',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-constructor-previous-step').prop('disabled', false);
                    }
                }
            ],
            template: function () {
                var showButtons = '';
                var tour3NextButtonSteps = [4, 6, 7, 8];
                if (typeof tour3 == 'undefined' && localStorage.getItem("create-table-import-data_current_step") !== null) {
                    window.localStorage.removeItem('create-table-import-data_current_step');
                    window.localStorage.removeItem('create-table-import-data_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour3.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour3.getCurrentStep(), tour3NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour3.getCurrentStep() === 9) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-table-import-data_current_step');
                window.localStorage.removeItem('create-table-import-data_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('.wdt-constructor-create-buttons button').prop('disabled', false);
                $('#wdt-constructor-previous-step').prop('disabled', false);
            }

        }).init();

        tour4 = new Tour({
            name: "create-table-from-wordpress-database",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour4.step0.title,
                    content: wpdtTutorialStrings.tour4.step0.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour4.step1.title,
                    content: wpdtTutorialStrings.tour4.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-third-row .wdt-constructor-type-selecter-block:nth-child(1) .card",
                    placement: "right",
                    title: wpdtTutorialStrings.tour4.step2.title,
                    content: wpdtTutorialStrings.tour4.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        window.localStorage.removeItem('create-table-from-wordpress-database_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour4.step3.title,
                    content: wpdtTutorialStrings.tour4.step3.content,
                    reflex: true,
                    duration: 3000,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 4
                    element: ".wdt-constructor-wp-query-table-name-block",
                    placement: "right",
                    title: wpdtTutorialStrings.tour4.step4.title,
                    content: wpdtTutorialStrings.tour4.step4.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 5
                    element: ".wdt-constructor-post-types-block",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour4.step5.title,
                    content: wpdtTutorialStrings.tour4.step5.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 6
                    element: ".wdt-constructor-post-types-relationship-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step6.title,
                    content: wpdtTutorialStrings.tour4.step6.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 7
                    element: ".wdt-constructor-post-types-define-relations-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step7.title,
                    content: wpdtTutorialStrings.tour4.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 8
                    element: ".wdt-constructor-post-conditions-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step8.title,
                    content: wpdtTutorialStrings.tour4.step8.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 9
                    element: ".wdt-constructor-post-grouping-rules-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step9.title,
                    content: wpdtTutorialStrings.tour4.step9.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('#wdt-constructor-next-step').prop('disabled', false);
                    }
                }, {
                    // step 10
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour4.step10.title,
                    content: wpdtTutorialStrings.tour4.step10.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    duration: 3000,
                    onNext: function (tour) {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 11
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step11.title,
                    content: wpdtTutorialStrings.tour4.step11.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    }
                }, {
                    // step 12
                    element: "#wdt-constructor-preview-wp-query",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour4.step12.title,
                    content: wpdtTutorialStrings.tour4.step12.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 13
                    element: ".wdt-constructor-preview-wp-table",
                    placement: "top",
                    title: wpdtTutorialStrings.tour4.step13.title,
                    content: wpdtTutorialStrings.tour4.step13.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-create-buttons button').prop('disabled', false)
                    }
                }, {
                    // step 14
                    element: "#wpdt-control-buttons > div > button",
                    placement: "left",
                    title: wpdtTutorialStrings.tour4.step14.title,
                    content: wpdtTutorialStrings.tour4.step14.content,
                    backdrop: true,
                    backdropContainer: '.wdt-constructor-create-buttons',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-constructor-previous-step').prop('disabled', false);
                    }
                }
            ],
            template: function () {
                var showButtons = '';
                var tour4NextButtonSteps = [4, 5, 6, 7, 8, 9, 11, 12, 13];
                if (typeof tour4 == 'undefined' && localStorage.getItem("create-table-from-wordpress-database_current_step") !== null) {
                    window.localStorage.removeItem('create-table-from-wordpress-database_current_step');
                    window.localStorage.removeItem('create-table-from-wordpress-database_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour4.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour4.getCurrentStep(), tour4NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour4.getCurrentStep() === 14) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-table-from-wordpress-database_current_step');
                window.localStorage.removeItem('create-table-from-wordpress-database_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('#wdt-constructor-previous-step').prop('disabled', false);
                $('.wdt-constructor-create-buttons button').prop('disabled', false);
            }
        }).init();

        tour5 = new Tour({
            name: "create-table-from-mysql-database",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour5.step0.title,
                    content: wpdtTutorialStrings.tour5.step0.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour5.step1.title,
                    content: wpdtTutorialStrings.tour5.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(4) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-constructor",
                    element: ".wdt-third-row .wdt-constructor-type-selecter-block:nth-child(2) .card",
                    placement: "left",
                    title: wpdtTutorialStrings.tour5.step2.title,
                    content: wpdtTutorialStrings.tour5.step2.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        window.localStorage.removeItem('create-table-from-mysql-database_redirect_to');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour5.step3.title,
                    content: wpdtTutorialStrings.tour5.step3.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    duration: 3000,
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 4
                    element: ".wdt-constructor-mysql-query-table-name-block",
                    placement: "right",
                    title: wpdtTutorialStrings.tour5.step4.title,
                    content: wpdtTutorialStrings.tour5.step4.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 5
                    element: "#wdt-constructor-mysql-tables-block",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour5.step5.title,
                    content: wpdtTutorialStrings.tour5.step5.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 6
                    element: ".wdt-constructor-mysql-tables-define-relations-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour5.step6.title,
                    content: wpdtTutorialStrings.tour5.step6.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 7
                    element: ".wdt-constructor-mysql-conditions-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour5.step7.title,
                    content: wpdtTutorialStrings.tour5.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 8
                    element: ".wdt-constructor-mysql-grouping-rules-block",
                    placement: "top",
                    title: wpdtTutorialStrings.tour5.step8.title,
                    content: wpdtTutorialStrings.tour5.step8.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-next-step').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('#wdt-constructor-next-step').prop('disabled', false);
                    }
                }, {
                    // step 9
                    element: "#wdt-constructor-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour5.step9.title,
                    content: wpdtTutorialStrings.tour5.step9.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    duration: 3000,
                    onNext: function () {
                        $('#wdt-constructor-next-step').click();
                        $('.wpdt-c.alert.alert-danger').hide();
                    },
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 10
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour5.step10.title,
                    content: wpdtTutorialStrings.tour5.step10.content,
                    onShown: function () {
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    }
                }, {
                    // step 11
                    element: "#wdt-constructor-preview-wp-query",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour5.step11.title,
                    content: wpdtTutorialStrings.tour5.step11.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 12
                    element: ".wdt-constructor-preview-wp-table",
                    placement: "top",
                    title: wpdtTutorialStrings.tour5.step12.title,
                    content: wpdtTutorialStrings.tour5.step12.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('.wdt-constructor-create-buttons button').prop('disabled', true);
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.wdt-constructor-create-buttons button').prop('disabled', false)
                    }
                }, {
                    // step 13
                    element: "#wpdt-control-buttons > div > button",
                    placement: "left",
                    title: wpdtTutorialStrings.tour5.step13.title,
                    content: wpdtTutorialStrings.tour5.step13.content,
                    backdrop: true,
                    backdropContainer: '.wdt-constructor-create-buttons',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-constructor-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-constructor-previous-step').prop('disabled', false);
                    }
                }
            ],
            template: function () {
                var showButtons = '';
                var tour5NextButtonSteps = [4, 5, 6, 7, 8, 10, 11, 12];
                if (typeof tour5 == 'undefined' && localStorage.getItem("create-table-from-mysql-database_current_step") !== null) {
                    window.localStorage.removeItem('create-table-from-mysql-database_current_step');
                    window.localStorage.removeItem('create-table-from-mysql-database_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour5.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour5.getCurrentStep(), tour5NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour5.getCurrentStep() === 13) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + "</button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-table-from-mysql-database_current_step');
                window.localStorage.removeItem('create-table-from-mysql-database_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('#wdt-constructor-previous-step').prop('disabled', false);
                $('.wdt-constructor-create-buttons button').prop('disabled', false);
            }
        }).init();

        tour6 = new Tour({
            name: "create-chart",
            keyboard: false,
            steps: [
                {
                    // step 0
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour6.step0.title,
                    content: wpdtTutorialStrings.tour6.step0.content
                }, {
                    // step 1
                    element: "#toplevel_page_wpdatatables-dashboard ul li:nth-child(6) a",
                    placement: "right",
                    reflex: true,
                    title: wpdtTutorialStrings.tour6.step1.title,
                    content: wpdtTutorialStrings.tour6.step1.content,
                    onShown: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(6) a').css("background-color", "#F88F20");
                    },
                    onHidden: function () {
                        $('#toplevel_page_wpdatatables-dashboard ul li:nth-child(6) a').css("background-color", "inherit");
                    }
                }, {
                    // step 2
                    path: document.location.pathname + "?page=wpdatatables-chart-wizard",
                    element: "#wdt-chart-wizard-body",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour6.step2.title,
                    content: wpdtTutorialStrings.tour6.step2.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        window.localStorage.removeItem('create-chart_redirect_to');
                        $('button[data-id="chart-render-engine"]').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 3
                    element: ".chart-wizard-breadcrumb",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour6.step3.title,
                    content: wpdtTutorialStrings.tour6.step3.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('button[data-id="chart-render-engine"]').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 4
                    element: ".chart-name",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step4.title,
                    content: wpdtTutorialStrings.tour6.step4.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('button[data-id="chart-render-engine"]').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('button[data-id="chart-render-engine"]').prop('disabled', false);
                    }
                }, {
                    // step 5
                    element: ".render-engine",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step5.title,
                    content: wpdtTutorialStrings.tour6.step5.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 6
                    element: "#chart-render-engine",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step6.title,
                    content: wpdtTutorialStrings.tour6.step6.content,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('.charts-type.google-charts-type:hidden').addClass('disabled');
                        $('.charts-type.chartjs-charts-type:hidden').addClass('disabled');
                        $('.charts-type.highcharts-charts-type:hidden').addClass('disabled');
                        $('.charts-type.apexcharts-charts-type:hidden').addClass('disabled');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 7
                    element: ".charts-type.google-charts-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step7.title,
                    content: wpdtTutorialStrings.tour6.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#chart-render-engine').prop('disabled', true);
                        $('[data-id="chart-render-engine"]').prop('disabled', true);
                        $('.charts-type.google-charts-type').removeClass('disabled');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 8
                    element: ".charts-type.highcharts-charts-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step7.title,
                    content: wpdtTutorialStrings.tour6.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#chart-render-engine').prop('disabled', true);
                        $('[data-id="chart-render-engine"]').prop('disabled', true);
                        $('.charts-type.highcharts-charts-type').removeClass('disabled');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 9
                    element: ".charts-type.apexcharts-charts-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step7.title,
                    content: wpdtTutorialStrings.tour6.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#chart-render-engine').prop('disabled', true);
                        $('[data-id="chart-render-engine"]').prop('disabled', true);
                        $('.charts-type.apexcharts-charts-type').removeClass('disabled');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 10
                    element: ".charts-type.chartjs-charts-type",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step7.title,
                    content: wpdtTutorialStrings.tour6.step7.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#chart-render-engine').prop('disabled', true);
                        $('[data-id="chart-render-engine"]').prop('disabled', true);
                        $('.charts-type.chartjs-charts-type').removeClass('disabled');
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 11
                    element: "#wdt-chart-wizard-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step10.title,
                    content: wpdtTutorialStrings.tour6.step10.content,
                    backdrop: true,
                    reflex: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#chart-render-engine').prop('disabled', true);
                        $('[data-id="chart-render-engine"]').prop('disabled', true);
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#chart-render-engine').prop('disabled', false);
                        $('[data-id="chart-render-engine"]').prop('disabled', false);
                    },
                    onNext: function () {
                        $("html, body").animate({scrollTop: 0}, "slow");
                    },
                }, {
                    // step 12
                    element: ".data-source",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step11.title,
                    content: wpdtTutorialStrings.tour6.step11.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 13
                    element: "#wpdatatables-chart-source",
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step12.title,
                    content: wpdtTutorialStrings.tour6.step12.content,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 14
                    element: "#wdt-chart-wizard-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step13.title,
                    content: wpdtTutorialStrings.tour6.step13.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true)
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 15
                    orphan: true,
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour6.step14.title,
                    content: wpdtTutorialStrings.tour6.step14.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true)
                    }
                }, {
                    // step 16
                    element: ".wdt-chart-column-picker-container",
                    placement: "bottom",
                    title: wpdtTutorialStrings.tour6.step15.title,
                    content: wpdtTutorialStrings.tour6.step15.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    },
                    onNext: function (tour) {
                        validateStepInput(tour);
                    }
                }, {
                    // step 17
                    element: "#wdt-chart-wizard-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step13.title,
                    content: wpdtTutorialStrings.tour6.step13.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true)
                        checkPreviousStepValid(tour);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 18
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step17.title,
                    content: wpdtTutorialStrings.tour6.step17.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function (tour) {
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 19
                    element: ".tab-nav.settings",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step18.title,
                    content: wpdtTutorialStrings.tour6.step18.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                    }
                }, {
                    // step 20
                    element: "#chart-container-tabs-1",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 21
                    element: "#chart-container-tabs-2",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 22
                    element: "#chart-container-tabs-3",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 23
                    element: "#chart-container-tabs-4",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 24
                    element: "#chart-container-tabs-5",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 25
                    element: "#chart-container-tabs-6",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 26
                    element: "#chart-container-tabs-7",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 27
                    element: "#chart-container-tabs-8",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 28
                    element: "#chart-container-tabs-9",
                    placement: "right",
                    title: wpdtTutorialStrings.tour6.step19.title,
                    content: wpdtTutorialStrings.tour6.step19.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 29
                    element: ".chart-preview-container",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step27.title,
                    content: wpdtTutorialStrings.tour6.step27.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-next-step').prop('disabled', true);
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                        $('.popover[class*="tour-"]').css("z-index", "1000");
                    },
                    onHidden: function () {
                        $('.popover[class*="tour-"]').css("z-index", "1102");
                        $('.tour-step-background').css("background-color", "inherit");
                        $('#wdt-chart-wizard-next-step').prop('disabled', false)
                    }
                }, {
                    // step 30
                    element: "#wdt-chart-wizard-next-step",
                    placement: "left",
                    title: wpdtTutorialStrings.tour6.step28.title,
                    content: wpdtTutorialStrings.tour6.step28.content,
                    reflex: true,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onNext: function () {
                        $("html, body").animate({scrollTop: 0}, "slow");
                    },
                    onShown: function () {
                        $('.tour-step-background').css("background-color", "rgba(248, 143, 32, 0.5)");
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('.tour-step-background').css("background-color", "inherit");
                    }
                }, {
                    // step 31
                    orphan: true,
                    placement: "top",
                    title: wpdtTutorialStrings.tour6.step29.title,
                    content: wpdtTutorialStrings.tour6.step29.content,
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 5,
                    onShown: function () {
                        $('#wdt-chart-wizard-previous-step').prop('disabled', true);
                    },
                    onHidden: function () {
                        $('#wdt-chart-wizard-previous-step').prop('disabled', false);
                    }
                }
            ],
            template: function () {
                var showButtons = '';
                var tour6NextButtonSteps = [2, 3, 4, 6, 7, 8, 9, 10, 13, 15, 16, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28];
                if (typeof tour6 == 'undefined' && localStorage.getItem("create-chart_current_step") !== null) {
                    window.localStorage.removeItem('create-chart_current_step');
                    window.localStorage.removeItem('create-chart_redirect_to');
                    return "<div class='popover tour'>" +
                        "<div class='arrow'></div>" +
                        "<p>" + wpdtTutorialStrings.cancel_tour + "</p>" +
                        "<div class='popover-navigation d-flex flex-nowrap'>" +
                        "<span class='popover-separator' data-role='separator'> </span>" +
                        "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button></div></div>";
                } else if (tour6.getCurrentStep() === 0) {
                    showButtons = "<button class='btn btn-warning float-left' data-role='end'>" + wpdtTutorialStrings.cancel_button + "</button><button class='btn btn-primary float-right' data-role='next'>" + wpdtTutorialStrings.start_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "</div></div>"
                } else if (jQuery.inArray(tour6.getCurrentStep(), tour6NextButtonSteps) !== -1) {
                    showButtons = "<button class='btn btn-primary' data-role='next'>" + wpdtTutorialStrings.next_button + " <i class='wpdt-icon-chevron-right m-l-5'></i></button>" + "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                } else if (tour6.getCurrentStep() === 30) {
                    showButtons = "<button class='btn btn-primary float-right' data-role='end'><i class='wpdt-icon-trophy m-r-5'></i> " + wpdtTutorialStrings.finish_button + " </button>" + "</div></div>";
                } else {
                    showButtons = "<button class='btn btn-warning' data-role='end'> " + wpdtTutorialStrings.skip_button + " </button>" + "</div></div>";
                }

                return "<div class='popover tour'>" +
                    "<div class='arrow'></div>" +
                    "<h3 class='popover-title'></h3>" +
                    "<div class='popover-content'></div>" +
                    "<div class='popover-navigation d-flex flex-nowrap'>" +
                    "<span class='popover-separator' data-role='separator'> </span>" +
                    showButtons;

            },
            onStart: function () {
                $demo0.addClass("disabled");
                $demo1.addClass("disabled");
                $demo2.addClass("disabled");
                $demo3.addClass("disabled");
                $demo4.addClass("disabled");
                $demo5.addClass("disabled");
                $demo6.addClass("disabled");
            },
            onEnd: function () {
                window.localStorage.removeItem('create-chart_current_step');
                window.localStorage.removeItem('create-chart_redirect_to');
                $demo0.removeClass("disabled");
                $demo1.removeClass("disabled");
                $demo2.removeClass("disabled");
                $demo3.removeClass("disabled");
                $demo4.removeClass("disabled");
                $demo5.removeClass("disabled");
                $demo6.removeClass("disabled");
                $('button[data-id="chart-render-engine"]').prop('disabled', false);
                $('.charts-type.google-charts-type:hidden').removeClass('disabled');
                $('.charts-type.chartjs-charts-type:hidden').removeClass('disabled');
                $('.charts-type.highcharts-charts-type:hidden').removeClass('disabled');
                $('.charts-type.apexcharts-charts-type:hidden').removeClass('disabled');
                $('#chart-render-engine').prop('disabled', false);
                $('[data-id="chart-render-engine"]').prop('disabled', false);
                $('#wdt-chart-wizard-previous-step').prop('disabled', false);
            }
        }).init();

        $(document).on("click", "#wdt-tutorial-simple-table", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour0.restart();
        });
        $(document).on("click", "#wdt-tutorial-data-source", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour1.restart();
        });
        $(document).on("click", "#wdt-tutorial-create-manual", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour2.restart();
        });

        $(document).on("click", "#wdt-tutorial-data-import", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour3.restart();
        });
        $(document).on("click", "#wdt-tutorial-wordpress-database", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour4.restart();
        });
        $(document).on("click", "#wdt-tutorial-mysql-database", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour5.restart();
        });
        $(document).on("click", "#wdt-tutorial-create-charts", function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour6.restart();
        });
        if (!(window.location.href.includes('wpdatatables-constructor') ||
            window.location.href.includes('wpdatatables-getting-started') ||
            window.location.href.includes('wpdatatables-chart-wizard')) &&
            (localStorage.getItem("create-table-data-source_redirect_to") !== null ||
                localStorage.getItem("create-table-data-source_current_step") !== null ||
                localStorage.getItem("create-simple-table_current_step") !== null ||
                localStorage.getItem("create-simple-table_redirect_to") !== null ||
                localStorage.getItem("create-manual-table_redirect_to") !== null ||
                localStorage.getItem("create-manual-table_current_step") !== null ||
                localStorage.getItem("create-table-import-data_redirect_to") !== null ||
                localStorage.getItem("create-table-import-data_current_step") !== null ||
                localStorage.getItem("create-table-from-mysql-database_redirect_to") !== null ||
                localStorage.getItem("create-table-from-mysql-database_current_step") !== null ||
                localStorage.getItem("create-table-from-wordpress-database_redirect_to") !== null ||
                localStorage.getItem("create-table-from-wordpress-database_current_step") !== null ||
                localStorage.getItem("create-chart_redirect_to") !== null ||
                localStorage.getItem("create-chart_current_step") !== null)) {
            window.localStorage.removeItem('create-table-data-source_current_step');
            window.localStorage.removeItem('create-table-data-source_redirect_to');
            window.localStorage.removeItem('create-simple-table_current_step');
            window.localStorage.removeItem('create-simple-table_redirect_to');
            window.localStorage.removeItem('create-manual-table_current_step');
            window.localStorage.removeItem('create-manual-table_redirect_to');
            window.localStorage.removeItem('create-table-import-data_current_step');
            window.localStorage.removeItem('create-table-import-data_redirect_to');
            window.localStorage.removeItem('create-table-from-wordpress-database_current_step');
            window.localStorage.removeItem('create-table-from-wordpress-database_redirect_to');
            window.localStorage.removeItem('create-table-from-mysql-database_current_step');
            window.localStorage.removeItem('create-table-from-mysql-database_redirect_to');
            window.localStorage.removeItem('create-chart_current_step');
            window.localStorage.removeItem('create-chart_redirect_to');
        }
        if (localStorage.getItem("create-chart_current_step") !== null) {
            $('#wdt-chart-wizard-next-step').on('click', function () {
                if (localStorage.getItem("create-chart_current_step") == 7 && !$('.wdt-chart-wizard-chart-selecter-block .card.selected').length ||
                    localStorage.getItem("create-chart_current_step") == 8 && !$('.wdt-chart-wizard-chart-selecter-block .card.selected').length ||
                    localStorage.getItem("create-chart_current_step") == 9 && !$('.wdt-chart-wizard-chart-selecter-block .card.selected').length ||
                    localStorage.getItem("create-chart_current_step") == 7 && $('.wdt-chart-wizard-chart-selecter-block .card.selected').length ||
                    localStorage.getItem("create-chart_current_step") == 8 && $('.wdt-chart-wizard-chart-selecter-block .card.selected').length ||
                    localStorage.getItem("create-chart_current_step") == 9 && $('.wdt-chart-wizard-chart-selecter-block .card.selected').length ) {
                    $('#wdt-chart-wizard-previous-step').click();
                    $("html, body").animate({scrollTop: 0}, "slow");
                } else if (localStorage.getItem("create-chart_current_step") == 12) {
                    $('#wdt-chart-wizard-previous-step').click();
                    $('#wpdatatables-chart-source').val('').selectpicker('refresh');
                } else if (localStorage.getItem("create-chart_current_step") == 15) {
                    var observerChart = new MutationObserver(function (mutations) {
                        if ($("#wdt-chart-wizard-previous-step").length) {
                            $('#wdt-chart-wizard-previous-step').click();
                            observerChart.disconnect();
                            //We can disconnect observer once the element exist if we dont want observe more changes in the DOM
                        }
                    });
                    // Start observing
                    observerChart.observe(document.body, { //document.body is node target to observe
                        childList: true, //This is a must have for the observer with subtree
                        subtree: true //Set to true if changes must also be observed in descendants.
                    });
                }
            });
        }
    });

})(jQuery);