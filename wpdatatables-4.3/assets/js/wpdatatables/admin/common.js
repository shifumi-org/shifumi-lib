/**
 * Controller for common methods in the admin section
 *
 * @author Alexander Gilmanov
 * @since 18.10.2016
 */

/**
 * Hide tooltip on button click or on mouseout event
 */
var wdtHideTooltip = function () {
    jQuery('[data-toggle="tooltip"]').click(function () {
        jQuery(this).wdtBootstrapTooltip('hide');
    });

    jQuery('[data-toggle="tooltip"]').mouseout(function (event) {
        var e = event.toElement || event.relatedTarget;
        if (e != null && (e.parentNode == this || e == this)) {
            return;
        }
        jQuery(this).wdtBootstrapTooltip('hide');
    });
};

/**
  * Extend jQuery to use our custom function for tooltip
  */
jQuery.fn.wdtBootstrapTooltip = jQuery.fn.tooltip;
/**

/**
 * Extend jQuery to use AnimateCSS
 */
jQuery.fn.extend({
    animateCss: function (animationName, onEnd) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        jQuery(this).addClass('animated ' + animationName).one(animationEnd, function () {
            jQuery(this).removeClass('animated ' + animationName);
            if (typeof onEnd == 'function') {
                onEnd();
            }
        });
    },
    fadeInDown: function () {
        jQuery(this)
            .removeClass('hidden')
            .show()
            .animateCss('fadeInDown');
    },
    fadeInRight: function (onEnd) {
        jQuery(this)
            .removeClass('hidden')
            .show()
            .animateCss('fadeInRight');
        if (typeof onEnd == 'function') {
            onEnd();
        }
    },
    fadeOutDown: function () {
        var $this = jQuery(this);
        jQuery(this).animateCss('fadeOutDown', function () {
            $this
                .addClass('hidden')
                .hide();
        });
    },
    fadeOutRight: function () {
        var $this = jQuery(this);
        jQuery(this).animateCss('fadeOutRight', function () {
            $this
                .addClass('hidden')
                .hide();
        });
    },
    animateFadeIn: function () {
        var $this = jQuery(this);
        jQuery(this)
            .removeClass('hidden')
            .show()
            .removeClass('fadeOut')
            .animateCss('fadeIn', function () {
                $this
                    .removeClass('fadeIn')
                    .removeClass('hidden')
                    .show()
            });
    },
    animateFadeOut: function (onEnd) {
        var $this = jQuery(this);
        jQuery(this)
            .removeClass('fadeIn')
            .animateCss('fadeOut', function () {
                $this
                    .addClass('hidden')
                    .removeClass('fadeOut')
                    .hide();
                if (typeof onEnd == 'function') {
                    onEnd();
                }
            });
    }
});

/**
 * Helper method to insert at textarea cursor position
 */
jQuery.fn.extend({
    insertAtCaret: function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        });
    }
});

(function ($) {

    $(function () {

        /**
         * Show WordPress warnings before wpDataTables data
         */
        $('.card-header:eq(0) > *').not('img, h2, ul.actions, button#wdt-table-id, .clear').prependTo('div.wdt-datatables-admin-wrap');


        /**
         * Attach tooltips
         */
        $('[data-toggle="tooltip"]').tooltip();

        wdtHideTooltip();

        /**
         * Attach HTML Popovers (Hints with images)
         */
        $('[data-toggle="html-popover"]').popover({
            html: true,
            content: function () {
                var content = $(this).attr("data-popover-content");
                return $(content).children(".popover-body").html();
            },
            title: function () {
                var title = $(this).attr("data-popover-content");
                return $(title).children(".popover-heading").html();
            }
        });

        /**
         * Apply selectpicker
         */
        $('select.selectpicker').selectpicker();

        /**
         * Hide modal dialog on Esc button
         */
        $(document).on('keyup', '.modal', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (e.which == 27) {
                $('.modal').modal('hide');
            }
        });

        $(document).on('click', 'button.wdt-backend-close', function () {
            $('#wdt-backend-close-modal').modal('show');

            $('#wdt-backend-close-button').click(function () {
                $(location).attr('href', wdtWpDataTablesPage.browseTablesUrl);
            });
        });

        $(document).on('click', 'button.wdt-backend-chart-close', function () {
            $('#wdt-backend-close-modal').modal('show');

            $('#wdt-backend-close-button').click(function () {
                $(location).attr('href', wdtWpDataTablesPage.browseChartsUrl);
            });
        });

        $(".wpdt-c .wdt-datatables-admin-wrap div.toggle-switch input[hidden='hidden']").each(function (index, value) {
            $(this).removeAttr("hidden")
        });


        /**
         * Get only text when copy shortcode from browse
         */

        $('.wpdt-c').on('click', '.wdt-copy-shortcode-browse', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $temp = $("<input>");
            $($temp).insertAfter($(this));
            $temp.val($(this).data('shortcode')).select();
            document.execCommand("copy");
            $temp.remove();
            wdtNotify(
                wpdatatables_edit_strings.success,
                wpdatatables_edit_strings.shortcodeSaved,
                'success'
            );
        });

        /**
         * Logic for plus and minus button on number input field
         */
        $('.wdt-btn-number').on("click", function (e) {
            e.preventDefault();

            var fieldName = $(this).attr('data-field');
            var type = $(this).attr('data-type');
            var input = $("input[name='" + fieldName + "']");
            var currentVal = parseInt(input.val());
            var fontSizesArr = ['font-size','wdt-font-size','wdt-table-font-size'];
            if (!isNaN(currentVal)) {
                if (type == 'minus') {

                    if (currentVal > input.attr('min')) {
                        input.val(currentVal - 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('min')) {
                        $(this).attr('disabled', true);
                    }

                } else if (type == 'plus') {
                    input.val(currentVal + 1).change();
                    $('.wdt-button-minus').attr('disabled', false);
                }
            } else {
                if (fontSizesArr.includes(fieldName)){
                    input.val(parseInt(input.attr('min')));
                } else {
                    input.val(1).change();
                }

            }
        });
        $(".input-number").on("change", function (e) {
            var inputValue = $(this).val();
            if (isNaN(inputValue)) {
                e.preventDefault();
            }
        });

        /**
         * Get only text when copy shortcode
         */
        $('.wpdt-c').on('click', '.wdt-copy-shortcode', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $temp = $("<input>");
            var $shortcodeType = $(this).data('shortcode-type');
            $($temp).insertAfter($(this));
            $temp.val($('#wdt-' + $shortcodeType + '-shortcode-id').text()).select();
            document.execCommand("copy");
            $temp.remove();
            wdtNotify(
                wpdatatables_edit_strings.success,
                wpdatatables_edit_strings.shortcodeSaved,
                'success'
            );
        });

        /**
         * Input underline animations
         */
        $(".collapse")[0] && ($(".collapse").on("show.bs.collapse", function (e) {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }), $(".collapse").on("hide.bs.collapse", function (e) {
            $(this).closest(".panel").find(".panel-heading").removeClass("active")
        }), $(".collapse.in").each(function () {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }));

        $(".fg-line")[0] && ($("body").on("focus", ".fg-line .form-control:not(.bootstrap-select)", function () {
            $(this).closest(".fg-line").addClass("fg-toggled")
        }));

        $("body").on("blur", ".form-control", function () {
            var p = $(this).closest(".form-group, .input-group")
                , i = p.find(".form-control").val();
            p.hasClass("fg-float") ? 0 == i.length && $(this).closest(".fg-line").removeClass("fg-toggled") : $(this).closest(".fg-line").removeClass("fg-toggled")
        });

        $(".fg-float")[0] && $(".fg-float .form-control").each(function () {
            var i = $(this).val();
            0 == !i.length && $(this).closest(".fg-line").addClass("fg-toggled")
        })

        $('#wpdt-views .nav-item').on('click', function (e) {
            e.preventDefault()
            e.stopImmediatePropagation()
            var view = $(this).data('view'),
                viewContainer = $('#wpdt-view-container');
            if (wpdatatable_config.table_type == 'simple') {
                switch (view) {
                    case 'desktop':
                        viewContainer.width('100%');
                        $('.wpDataTableContainerSimpleTable .wpdtSimpleTable').each(function (i) {
                            var tempID = '#' + $(this)[i].id;
                            if (wpdatatable_config.simpleResponsive) {
                                if ($(tempID).data('basictable'))
                                    $(tempID).basictable('destroy');
                            }
                        })
                        break;
                    case 'tablet':
                        viewContainer.width('1024').css('margin', '0 auto');
                        if (wpdatatable_config.simpleResponsive) {
                            $('.wpDataTableContainerSimpleTable .wpdtSimpleTable').each(function (i) {
                                var tempID = '#' + $(this)[i].id;
                                $(tempID).basictable({
                                    containerBreakpoint: 1024,
                                    tableWrap: true,
                                    header: !!$(tempID + ' thead').length
                                });
                            })
                        }
                        break;
                    case 'mobile':
                        viewContainer.width('400').css('margin', '0 auto');
                        if (wpdatatable_config.simpleResponsive) {
                            $('.wpDataTableContainerSimpleTable .wpdtSimpleTable').each(function (i) {
                                var tempID = '#' + $(this)[i].id;
                                $(tempID).basictable({
                                    containerBreakpoint: 400,
                                    tableWrap: true,
                                    showEmptyCells: true,
                                    header: !!$(tempID + ' thead').length
                                });
                            })
                        }
                        break;
                    default:
                        viewContainer.width('100%');
                        break;
                }
            }

            $(this).addClass('active').siblings().removeClass('active');
        })



    });

})(jQuery);

/**
 * Hide preloader on window load
 */
jQuery(window).on('load', function () {
    jQuery('.wdt-preload-layer').animateFadeOut();
});

/**
 * Show preloader before leaving the page
 */
window.onbeforeunload = function (e) {
    if (window.reportbuilderobj == 'undifined') jQuery('.wdt-preload-layer').animateFadeIn();
};


/**
 * Growl notification in the right top corner
 * @param title
 * @param message
 * @param type
 */
function wdtNotify(title, message, type) {

    if (typeof title == 'undefined') {
        title = 'info';
    }
    if (typeof message == 'undefined') {
        message = 'info';
    }
    if (typeof type == 'undefined') {
        type = 'info';
    }


    switch (type) {
        case 'danger':
            icon = 'wpdt-icon-exclamation-triangle';
            break;
        case 'success':
        default:
            icon = 'wpdt-icon-check-circle-full';
            break;
    }

    jQuery.growl({
        icon: icon,
        title: ' ' + title + ' ',
        message: message,
        url: ''
    }, {
        element: 'body',
        type: type,
        allow_dismiss: true,
        placement: {
            from: 'top',
            align: 'right'
        },
        offset: {
            x: 20,
            y: 40
        },
        spacing: 10,
        z_index: 100002,
        delay: 2500,
        timer: 1000,
        url_target: '_blank',
        mouse_over: false,
        animate: {
            enter: 'animated fadeIn',
            exit: 'animated fadeOut'
        },
        icon_type: 'class',
        template: '<div data-growl="container" class="wpdt-c alert" role="alert">' +
            '<span class="sr-only">' + wpdatatables_edit_strings.close + '</span>' +
            '</button>' +
            '<span data-growl="icon"></span>' +
            '<span data-growl="title"></span>' +
            '<span data-growl="message"></span>' +
            '<a href="#" data-growl="url"></a>' +
            '</div>'
    });
}
