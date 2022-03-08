'use strict';

/* eslint-disable no-undef, func-names */
jQuery(document).ready(function ($) {
  var contentLibraryTable = '#snc-content_library_wrap table';

  $(contentLibraryTable + ' a.content_title, ' + contentLibraryTable + ' a.show').click(function (e) {
    e.preventDefault();
    var id = $(this).attr('data-item_id');

    $('.embed_information').each(function () {
      var theId = $(this).attr('data-item_id');

      if (id !== theId) {
        $(this).hide();
      } else {
        $(this).toggle();
      }
    });
  });
});
'use strict';

/* eslint-disable no-undef, no-restricted-globals, no-alert, func-names */
function deleteSncFromTableContent(itemId, mode) {
  if (confirm('Do you really want to delete this?')) {
    var data = {
      action: 'SnC_Content_Delete',
      item_id: itemId,
      mode: mode,
      security: jQuery('#snc-content_library_wrap input[name="security"]').val()
    };

    jQuery.post(ajaxurl, data, function () {
      if (mode === 'media library' || mode === 'vc') {
        jQuery('#snc-content_library_wrap table tr[data-item_id="' + itemId + '"]').remove();
      } else {
        location.reload();
      }
    });
  }
}

jQuery(document).ready(function ($) {
  // <-- Delete
  $('#snc-content_library_wrap table a.delete').click(function (e) {
    e.preventDefault();
    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var itemId = $(this).attr('data-item_id');

    deleteSncFromTableContent(itemId, mode);
  });
  // Delete -->

  // Replace content pop-up settings!!
  var itemId = '';
  $('#snc-content_library_wrap table a.snc_replace_confirm').click(function (e) {
    e.preventDefault();
    var $itemId = $(this).data('item_id');

    // Add ID to the buttons
    $('#snc-delete-book-only').data('item_id', $itemId);
    $('#snc-delete-all-data').data('item_id', $itemId);

    $('#replace_placeholder').attr('href', 'media-upload.php?content_id=' + $itemId + '&type=snc&tab=upload&min-height=400&no_tab=1&TB_iframe=true').attr('data-item_id', $itemId);
  });

  //
  $('.tclr-replace-content__task-btn').on('click', function () {
    // Get button
    var $button = $(this);

    // Get task
    var task = $button.data('task');

    // Hide step 1 container, and show the step 2 container
    $('.tclr-replace-content__step-1').hide();
    $('.tclr-replace-content__step-2').show();

    // Hide the container of both tasks
    $('#bookmark-confirmation, #all-confirmation').hide();

    // Check the task
    if (task === 'remove-bookmark') {
      // Show container
      $('#bookmark-confirmation').show();
    } else if (task === 'remove-all-data') {
      $('#all-confirmation').show();
    }
  });

  //
  $('.tclr-replace-content__cancel-2-step-btn').on('click', function () {
    // Hide the container of both tasks
    $('#bookmark-confirmation, #all-confirmation').hide();

    // Show the container of the first step and hide the container of the second one
    $('.tclr-replace-content__step-1').show();
    $('.tclr-replace-content__step-2').hide();
  });

  //
  $('#snc-delete-book-only').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id');
    $button.addClass('tclr-btn--loading');

    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var data1 = {
      action: 'SnC_Content_Bookmark_Delete',
      item_id: itemId,
      mode: mode,
      security: $('#snc-content_library_wrap input[name="security"]').val()
    };

    $.post(ajaxurl, data1).done(function () {
      $button.removeClass('tclr-btn--loading');
      $('.tclr-replace-content__step-1').show();
      $('.tclr-replace-content__step-2').hide();

      $('#TB_closeWindowButton').trigger('click');
      setTimeout(function () {
        $('#replace_placeholder').trigger('click');
      }, 1000);
    });
    // }
  });

  //
  $('#snc-delete-all-data').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id');
    $button.addClass('tclr-btn--loading');

    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var data2 = {
      action: 'SnC_Content_Delete_All',
      item_id: itemId,
      mode: mode,
      security: $('#snc-content_library_wrap input[name="security"]').val()
    };

    $.post(ajaxurl, data2).done(function () {
      $button.removeClass('tclr-btn--loading');
      $('.tclr-replace-content__step-1').show();
      $('.tclr-replace-content__step-2').hide();
      $('#TB_closeWindowButton').trigger('click');
      setTimeout(function () {
        $('#replace_placeholder').trigger('click');
      }, 1000);
    });
    // }
  });

  /* ES5 */
  var TinCannyModulesSearch = {
    init: function init() {
      // Get elements
      this.getElements();

      // Get required data
      this.getData();

      // Create Fuse instance
      this.initFuse();

      // Search
      this.listenSearchField();
    },
    getElements: function getElements() {
      this.$elements = {
        searchField: $('#tclr-classic-editor-content-library-search'),
        tableContainer: $('.tclr-classic-editor-content-library__list'),
        tableItems: $('.tclr-classic-editor-content-library__list tbody tr.tclr-classic-editor-content-library__item')
      };
    },
    getData: function getData() {
      // Create an array of objects with the Tin Canny content items
      var items = [];

      // Iterate DOM elements (tr)
      $.each(this.$elements.tableItems, function (index, tr) {
        var $tr = $(tr);

        // Get row data
        var rowData = {
          title: $tr.data('item_name'),
          $element: $tr
        };

        // Add the row to the main items array
        items.push(rowData);
      });

      this.items = items;
    },
    initFuse: function initFuse() {
      this.Fuse = new Fuse(this.items, {
        keys: ['title'],
        threshold: 0,
        ignoreLocation: true
      });
    },
    listenSearchField: function listenSearchField() {
      // Reference to this instance
      var thisRef = this;

      this.$elements.searchField.on('input', function () {
        // Get the value of the search field
        var searchFieldValue = thisRef.$elements.searchField.val();

        // Check if the search field has a value
        if (searchFieldValue !== '') {
          // Search
          thisRef.search(searchFieldValue);
        } else {
          // Otherwise, show all
          thisRef.showAll();
        }
      });
    },
    search: function search(searchQuery) {
      // Perform search
      var filteredItems = this.Fuse.search(searchQuery);

      // Enable search mode
      // We're adding a class to the container to hide all the rows
      // and we're going to iterate through the ones we have to show
      // to add a class to them. We're doing this to reduce the number
      // of DOM modifications
      this.$elements.tableContainer.addClass('tclr-classic-editor-content-library__list--search-mode');

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass('tclr-classic-editor-content-library__item--visible');

      // Check if there are results
      if (filteredItems.length > 0) {
        // Hide the "no results" row
        this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--no-results');

        // Iterate the results
        filteredItems.forEach(function (row) {
          // Add class to the item to show it
          row.item.$element.addClass('tclr-classic-editor-content-library__item--visible');
        });
      } else {
        // Show the "no results" row
        this.$elements.tableContainer.addClass('tclr-classic-editor-content-library__list--no-results');
      }
    },
    showAll: function showAll() {
      // Disable search mode
      this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--search-mode');

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass('tclr-classic-editor-content-library__item--visible');

      // Hide the "no results" row
      this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--no-results');
    }
  };

  TinCannyModulesSearch.init();
});
'use strict';

/* eslint-disable no-undef, no-restricted-globals, no-alert, func-names */
function deleteSncFromTable(itemId, mode) {
  if (confirm('Do you really want to delete this?')) {
    var data = {
      action: 'SnC_Media_Delete',
      item_id: itemId,
      mode: mode,
      security: jQuery('form.snc-media_enbed_form input[name="security"]').val()
    };

    jQuery.post(ajaxurl, data, function () {
      if (mode === 'media library' || mode === 'vc') {
        jQuery('#snc-content_library_wrap table tr[data-item_id="' + itemId + '"]').remove();
      } else {
        location.reload();
      }
    });
  }
}

jQuery(document).ready(function ($) {
  // <-- Lightbox Options
  $('.insert_type input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');

    $('form[data-item_id="' + key + '"] .options').stop().slideUp();
    $('form[data-item_id="' + key + '"] .options[data-item_option="' + $(this).val() + '"]').stop().slideDown();
  });

  $('.lightbox_title input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();

    $('input.text_with_title[data-item_id="' + key + '"]').hide();

    if (val === 'With Title') {
      $('input.text_with_title[data-item_id="' + key + '"]').show().focus();
    }
  });

  $('.lightbox_button input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();

    $('input.lightbox_button_text[data-item_id="' + key + '"]').hide();
    $('div.lightbox_button_text[data-item_id="' + key + '"]').hide();
    $('section.lightbox_button_custom[data-item_id="' + key + '"]').hide();
    $('input.lightbox_button_url[data-item_id="' + key + '"]').hide();

    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $('input.lightbox_button_text[data-item_id="' + key + '"]').show();
      $('div.lightbox_button_text[data-item_id="' + key + '"]').show();
    }

    if (val === 'url') {
      $('input.lightbox_button_url[data-item_id="' + key + '"]').show();
    }

    if (val === 'image') {
      $('.lightbox_button_custom[data-item_id="' + key + '"]').show();
    }
  });

  // <-- New Window Options
  $('.new_window_option input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();

    $('.new_window_option[data-item_id="' + key + '"] input[type="text"]').hide();
    $('div._blank_button_text[data-item_id="' + key + '"]').hide();
    $('.new_window_option[data-item_id="' + key + '"] .file_upload_button').hide();

    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $('.new_window_option[data-item_id="' + key + '"] input._blank_text').show();
      $('div._blank_button_text[data-item_id="' + key + '"]').show();
    }

    if (val === 'image') {
      $('.new_window_option[data-item_id="' + key + '"] .file_upload_button').show();
    }

    if (val === 'url') {
      $('.new_window_option[data-item_id="' + key + '"] input._blank_url').show();
    }
  });
  // New Window Options -->

  // <-- Same Window Options
  $('.same_window_option input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();

    $('.same_window_option[data-item_id="' + key + '"] input[type="text"]').hide();
    $('div._self_button_text[data-item_id="' + key + '"]').hide();
    $('.same_window_option[data-item_id="' + key + '"] .file_upload_button').hide();

    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $('.same_window_option[data-item_id="' + key + '"] input._self_text').show();
      $('div._self_button_text[data-item_id="' + key + '"]').show();
    }

    if (val === 'image') {
      $('.same_window_option[data-item_id="' + key + '"] .file_upload_button').show();
    }

    if (val === 'url') {
      $('.same_window_option[data-item_id="' + key + '"] input._self_url').show();
    }
  });
  // Same Window Options -->

  // <-- Delete
  $('form.snc-media_enbed_form .delete-media, #snc-content_library_wrap table span a.delete').click(function (e) {
    e.preventDefault();

    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var itemId = $(this).attr('data-item_id');

    deleteSncFromTable(itemId, mode);
  });
  // Delete -->
});
'use strict';

/* eslint-disable no-undef, no-restricted-globals, func-names */
jQuery(document).ready(function ($) {
  var $sncForm = $('.snc-media_enbed_form');

  // Get Code From PHP
  $sncForm.ajaxForm({
    success: function success(response) {
      var data = JSON.parse(response);
      var win = window.dialogArguments || opener || parent || top;
      win.send_to_editor(data.shortcode);
    }
  });
});
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/* eslint-disable no-undef, func-names, no-restricted-globals, no-use-before-define */
function triggerUploadForm($, key) {
  var $sncForm = $('#snc-media_upload_file_form');
  var $sncButton = $('#snc-upload_button');
  var $sncProgress = $('#snc-media_upload_file_wrap .progress');
  var $sncProgressBarWrapper = $('#snc-progress_bar_wrapper');
  var $sncProgressBar = $('#snc-progress_bar');

  var $sncMessage = $('#snc-media_upload_message');

  var enableButton = true;

  // Show Percent and Change Width
  function processLoading(percentComplete) {
    var maxWidth = $sncProgressBarWrapper.width();
    var width = percentComplete < 10 ? maxWidth / 100 * 10 : maxWidth / 100 * percentComplete;

    $sncProgressBar.width(width);
    $sncProgressBar.html(percentComplete + '%');
  }

  // HTML Controll
  function showLoading() {
    enableButton = false;

    $sncProgress.width($sncForm.width());

    $sncProgress.show();
    $sncButton.hide();
  }

  function hideLoading() {
    enableButton = true;
    $sncProgress.hide();
    $sncButton.show();
  }

  function resetLoading() {
    processLoading(0);
  }

  function showErrorMessage(message) {
    $sncMessage.attr('class', 'error');
    $sncMessage.show();
    $sncMessage.html('<p>' + message + '</p>');
  }

  function updateErrorMessage(message) {
    $sncMessage.attr('class', 'updated');
    $sncMessage.show();
    $sncMessage.html('<p>' + message + '</p>');
  }

  function hideErrorMessage() {
    $sncMessage.hide();
  }

  // Media Upload Form
  function setUploadForm() {
    $sncForm.ajaxForm({
      beforeSubmit: function beforeSubmit() {
        resetLoading();
      },
      uploadProgress: function uploadProgress(event, position, total, percentComplete) {
        processLoading(percentComplete);
      },
      success: function success(response) {
        var data = JSON.parse(response);

        if (data.id === 'error') {
          showErrorMessage(data.message);
        } else if (data.id === 'not_supported') {
          filePathMessage(data);
        } else {
          afterUploadSuccess(data);
        }

        hideLoading();
      }
    });
  }

  function afterUploadSuccess(data) {
    updateErrorMessage(data.message);

    $('#snc-media_upload_file_wrap').hide();
    if ($('#snc-media_upload_file_wrap #no_tab').length > 0) {
      if ($('#snc-media_upload_file_wrap #no_refresh').length > 0) {
        // self.parent.window.wp.tccmb_content($('#ele_id').val());
        self.parent.tb_remove();
      } else {
        window.parent.location = window.parent.location.href;
      }
    }

    if (key) {
      $('#vc_properties-panel .vc-snc-trigger input').attr('value', data.id);
      $('#vc_properties-panel .vc-snc-name input').attr('value', data.title);

      trigger_vc_snc_mode();
    } else {
      $('.snc-embed_information').show();
      $('a.delete-media').attr('data-item_id', data.id);
      $('input#item_id').attr('value', data.id);
      $('input#item_title').attr('value', data.title);
    }
  }

  function getFileStructureHTML(structure) {
    var dir = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

    var html = '';

    Object.keys(structure).map(function (id) {
      if (_typeof(structure[id]) === 'object') {
        html += '<li class="structure-dir-btn" data-path="' + dir + '/' + id + '">' + id + '</li>';
        html += '<ul class="structure-dir-container" data-path="' + dir + '/' + id + '">';
        html += getFileStructureHTML(structure[id], dir + '/' + id);
        html += '</ul>';
      } else {
        html += '<li class="file-selector" data-path="' + dir + '/' + structure[id] + '">' + structure[id] + '<li>';
      }

      return true;
    });

    return html;
  }

  function filePathMessage(args) {
    $sncMessage.attr('class', 'uo-tclr-admin-upload-outside-container');
    $sncMessage.show();

    var html = '<div class="uo-tclr-admin"><div class="uo-tclr-bubble uo-tclr-bubble--upload"><div class="uo-tclr-warning uo-tclr-warning--with-icon">Unable to read .zip file. Please re-zip your file and upload it again.</div><div class="uo-tclr-bubble__content"><p>Please note that any xAPI/SCORM statements sent by this module:</p><ul><li>will not be recorded</li><li>may display errors because the module cannot communicate with an LMS or LRS</li></ul><p>To use this module anyway, select the .html file that launches the module using the file browser below:</p></div><div class="uo-tclr-file-manager">';

    html += '<ul class="file-selector">';
    html += getFileStructureHTML(JSON.parse(args.structure));
    html += '</ul>';

    html += '</div>';

    html += '<button class="uo-tclr-btn-media uo-tclr-btn-media--cancel-upload">Cancel and Delete Upload</button>';

    html += '</div></div>';

    $sncMessage.html(html);

    $('.uo-tclr-btn-media--cancel-upload').click(function () {
      location.reload();
    });

    $('.structure-dir-btn').click(function (e) {
      $(e.currentTarget).toggleClass('open');
      var path = $(e.currentTarget).attr('data-path');
      $('.structure-dir-container[data-path="' + path + '"]').show();
    });

    $('.file-selector').click(function (e) {
      var filePath = $(e.currentTarget).attr('data-path');

      if (!filePath) {
        return;
      }

      if (filePath.charAt(0) === '/') {
        filePath = filePath.slice(1);
      }

      var data = {
        action: 'SnC_Link_File_Path',
        security: args.nonce,
        filePath: filePath,
        title: args.title
      };

      jQuery.post(args.ajaxPath, data, function (response) {
        var parsed = JSON.parse(response);
        afterUploadSuccess(parsed);
        hideLoading();
      });
    });
  }

  // Get File Extension
  function getFileExtension(fileName) {
    var file = fileName.split('.');
    return file.pop();
  }

  // in_array (PHP Style)
  function inArray(needle, haystack) {
    var result = false;

    for (var i = 0; i < haystack.length; i += 1) {
      if (haystack[i] === needle) {
        result = true;
        return result;
      }
    }

    return result;
  }

  // Media Upload Button
  $('.file_upload_button').click(function (e) {
    e.preventDefault();

    var id = $(this).attr('data-id');
    hideErrorMessage();

    if (enableButton) {
      $('input[type="file"][data-id="' + id + '"]').click();
    }
  });

  // Media Upload Input
  $('#snc-media_upload_file').change(function () {
    showLoading();

    var file = this.files[0];
    var ext = getFileExtension(file.name).trim().toLowerCase();

    if (!inArray(ext, ['zip'])) {
      showErrorMessage('Only .zip Files are Allowed.');
      hideLoading();

      return;
    }

    if (file.size > $('#snc-max_file_size').val()) {
      showErrorMessage('File is too large to upload.');
      hideLoading();

      return;
    }

    $('#snc-extension').val(ext);
    $sncForm.submit();
  });

  setUploadForm();
}

jQuery(document).ready(function ($) {
  triggerUploadForm($);
});
"use strict";