jQuery.fn.dataTableExt.oPagination.four_button = {
            "fnInit": function (oSettings, nPaging, fnCallbackDraw)
            {
                nFirst = document.createElement('span');
                nPrevious = document.createElement('span');
                var nInput = document.createElement('input');
                var nPage = document.createElement('span');
                var nOf = document.createElement('span');
                nOf.className = "paginate_of";
                nInput.className = "current_page_no";
                nPage.className = "paginate_page";
                nInput.type = "text";
                nInput.style.width = "40px";
                nInput.style.height = "26px";
                nInput.style.display = "inline";
                nPaging.appendChild(nPage);
                nInput.onkeypress = function(e){
                    if (e.which != 46 && e.which > 31 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
                    return true;
                }
                jQuery(nInput).keyup(function (e) {
                    if (e.which != 46 && e.which > 31 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
                    if (e.which == 38 || e.which == 39) {
                        this.value++;
                    } else if ((e.which == 37 || e.which == 40) && this.value > 1) {
                        this.value--;
                    }
                    if (this.value == "" || this.value.match(/[^0-9]/)) {
                        return;
                    }
                    var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                    if (iNewStart > oSettings.fnRecordsDisplay()) {
                        oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                        fnCallbackDraw(oSettings);
                        return;
                    }
                    oSettings._iDisplayStart = iNewStart;
                    fnCallbackDraw(oSettings);
                });
                nNext = document.createElement('span');
                nLast = document.createElement('span');
                var nFirst = document.createElement('span');
                var nPrevious = document.createElement('span');
                var nPage = document.createElement('span');
                var nOf = document.createElement('span');
                nNext.style.backgroundImage = "url('" + __ARMIMAGEURL + "/next_normal-icon.png')";
                nNext.style.backgroundRepeat = "no-repeat";
                nNext.style.backgroundPosition = "center";
                nNext.title = "Next";
                nLast.style.backgroundImage = "url('" + __ARMIMAGEURL + "/last_normal-icon.png')";
                nLast.style.backgroundRepeat = "no-repeat";
                nLast.style.backgroundPosition = "center";
                nLast.title = "Last";
                nFirst.style.backgroundImage = "url('" + __ARMIMAGEURL + "/first_normal-icon.png')";
                nFirst.style.backgroundRepeat = "no-repeat";
                nFirst.style.backgroundPosition = "center";
                nFirst.title = "First";
                nPrevious.style.backgroundImage = "url('" + __ARMIMAGEURL + "/previous_normal-icon.png')";
                nPrevious.style.backgroundRepeat = "no-repeat";
                nPrevious.style.backgroundPosition = "center";
                nPrevious.title = "Previous";
                nFirst.appendChild(document.createTextNode(' '));
                nPrevious.appendChild(document.createTextNode(' '));
                nLast.appendChild(document.createTextNode(' '));
                nNext.appendChild(document.createTextNode(' '));
                nOf.className = "paginate_button nof";
                nPaging.appendChild(nFirst);
                nPaging.appendChild(nPrevious);
                nPaging.appendChild(nInput);
                nPaging.appendChild(nOf);
                nPaging.appendChild(nNext);
                nPaging.appendChild(nLast);
                jQuery(nFirst).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "first");
                    fnCallbackDraw(oSettings);
                });
                jQuery(nPrevious).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "previous");
                    fnCallbackDraw(oSettings);
                });
                jQuery(nNext).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "next");
                    fnCallbackDraw(oSettings);
                });
                jQuery(nLast).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "last");
                    fnCallbackDraw(oSettings);
                });
                jQuery(nFirst).bind('selectstart', function () {
                    return false;
                });
                jQuery(nPrevious).bind('selectstart', function () {
                    return false;
                });
                jQuery('span', nPaging).bind('mousedown', function () {
                    return false;
                });
                jQuery('span', nPaging).bind('selectstart', function () {
                    return false;
                });
                jQuery(nNext).bind('selectstart', function () {
                    return false;
                });
                jQuery(nLast).bind('selectstart', function () {
                    return false;
                });
            },
            "fnUpdate": function (oSettings, fnCallbackDraw)
            {
                if (!oSettings.aanFeatures.p) {
                    return;
                }
                var an = oSettings.aanFeatures.p;
                for (var i = 0, iLen = an.length; i < iLen; i++)
                {
                    var buttons = an[i].getElementsByTagName('span');
                    if (oSettings._iDisplayStart === 0) {
                        buttons[1].className = "paginate_disabled_first armhelptip";
                        buttons[2].className = "paginate_disabled_previous armhelptip";
                    } else {
                        buttons[1].className = "paginate_enabled_first armhelptip";
                        buttons[2].className = "paginate_enabled_previous armhelptip";
                    }
                    if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay()) {
                        buttons[4].className = "paginate_disabled_next armhelptip";
                        buttons[5].className = "paginate_disabled_last armhelptip";
                    } else {
                        buttons[4].className = "paginate_enabled_next armhelptip";
                        buttons[5].className = "paginate_enabled_last armhelptip";
                    }
                    if (!oSettings.aanFeatures.p) {
                        return;
                    }
                    var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
                    var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;
                    if (document.getElementById('of_grid')) {
                        of_grid = document.getElementById('of_grid').value;
                    } else {
                        of_grid = 'of';
                    }
                    var an = oSettings.aanFeatures.p;
                    for (var i = 0, iLen = an.length; i < iLen; i++)
                    {
                        var spans = an[i].getElementsByTagName('span');
                        var inputs = an[i].getElementsByTagName('input');
                        spans[spans.length - 3].innerHTML = " " + of_grid + " " + iPages
                        inputs[0].value = iCurrentPage;
                    }
                }
            }
        }