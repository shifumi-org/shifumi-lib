/* eslint-disable no-undef, func-names, no-restricted-globals, no-use-before-define */
function triggerUploadForm($, key) {
  const $sncForm = $('#snc-media_upload_file_form');
  const $sncButton = $('#snc-upload_button');
  const $sncProgress = $('#snc-media_upload_file_wrap .progress');
  const $sncProgressBarWrapper = $('#snc-progress_bar_wrapper');
  const $sncProgressBar = $('#snc-progress_bar');

  const $sncMessage = $('#snc-media_upload_message');

  let enableButton = true;

  // Show Percent and Change Width
  function processLoading(percentComplete) {
    const maxWidth = $sncProgressBarWrapper.width();
    const width = (percentComplete < 10)
      ? (maxWidth / 100) * 10
      : (maxWidth / 100) * percentComplete;

    $sncProgressBar.width(width);
    $sncProgressBar.html(`${percentComplete}%`);
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
    $sncMessage.html(`<p>${message}</p>`);
  }

  function updateErrorMessage(message) {
    $sncMessage.attr('class', 'updated');
    $sncMessage.show();
    $sncMessage.html(`<p>${message}</p>`);
  }

  function hideErrorMessage() {
    $sncMessage.hide();
  }

  // Media Upload Form
  function setUploadForm() {
    $sncForm.ajaxForm({
      beforeSubmit: () => {
        resetLoading();
      },
      uploadProgress: (event, position, total, percentComplete) => {
        processLoading(percentComplete);
      },
      success: (response) => {
        const data = JSON.parse(response);

        if (data.id === 'error') {
          showErrorMessage(data.message);
        } else if (data.id === 'not_supported') {
          filePathMessage(data);
        } else {
          afterUploadSuccess(data);
        }

        hideLoading();
      },
    });
  }

  function afterUploadSuccess(data) {
    updateErrorMessage(data.message);

    $('#snc-media_upload_file_wrap').hide();
    if($('#snc-media_upload_file_wrap #no_tab').length > 0){
      if($('#snc-media_upload_file_wrap #no_refresh').length > 0) {
        //self.parent.window.wp.tccmb_content($('#ele_id').val());
        self.parent.tb_remove();
      }else{
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

  function getFileStructureHTML(structure, dir = '') {
    let html = '';

    Object.keys(structure).map((id) => {
      if (typeof structure[id] === 'object') {
        html += `<li class="structure-dir-btn" data-path="${dir}/${id}">${id}</li>`;
        html += `<ul class="structure-dir-container" data-path="${dir}/${id}">`;
        html += getFileStructureHTML(structure[id], `${dir}/${id}`);
        html += '</ul>';
      } else {
        html += `<li class="file-selector" data-path="${dir}/${structure[id]}">${structure[id]}<li>`;
      }

      return true;
    });

    return html;
  }

  function filePathMessage(args) {
    $sncMessage.attr('class', 'uo-tclr-admin-upload-outside-container');
    $sncMessage.show();

    let html = '<div class="uo-tclr-admin"><div class="uo-tclr-bubble uo-tclr-bubble--upload"><div class="uo-tclr-warning uo-tclr-warning--with-icon">Unable to read .zip file. Please re-zip your file and upload it again.</div><div class="uo-tclr-bubble__content"><p>Please note that any xAPI/SCORM statements sent by this module:</p><ul><li>will not be recorded</li><li>may display errors because the module cannot communicate with an LMS or LRS</li></ul><p>To use this module anyway, select the .html file that launches the module using the file browser below:</p></div><div class="uo-tclr-file-manager">';

    html += '<ul class="file-selector">';
    html += getFileStructureHTML(JSON.parse(args.structure));
    html += '</ul>';

    html += '</div>';

    html += '<button class="uo-tclr-btn-media uo-tclr-btn-media--cancel-upload">Cancel and Delete Upload</button>';

    html += '</div></div>';

    $sncMessage.html(html);

    $('.uo-tclr-btn-media--cancel-upload').click(() => {
      location.reload();
    });

    $('.structure-dir-btn').click((e) => {
      $(e.currentTarget).toggleClass('open');
      const path = $(e.currentTarget).attr('data-path');
      $(`.structure-dir-container[data-path="${path}"]`).show();
    });

    $('.file-selector').click((e) => {
      let filePath = $(e.currentTarget).attr('data-path');

      if (!filePath) {
        return;
      }

      if (filePath.charAt(0) === '/') {
        filePath = filePath.slice(1);
      }

      const data = {
        action: 'SnC_Link_File_Path',
        security: args.nonce,
        filePath,
        title: args.title,
      };

      jQuery.post(args.ajaxPath, data, (response) => {
        const parsed = JSON.parse(response);
        afterUploadSuccess(parsed);
        hideLoading();
      });
    });
  }

  // Get File Extension
  function getFileExtension(fileName) {
    const file = fileName.split('.');
    return file.pop();
  }

  // in_array (PHP Style)
  function inArray(needle, haystack) {
    let result = false;

    for (let i = 0; i < haystack.length; i += 1) {
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

    const id = $(this).attr('data-id');
    hideErrorMessage();

    if (enableButton) {
      $(`input[type="file"][data-id="${id}"]`).click();
    }
  });

  // Media Upload Input
  $('#snc-media_upload_file').change(function () {
    showLoading();

    const file = this.files[0];
    const ext = getFileExtension(file.name).trim().toLowerCase();

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

jQuery(document).ready(($) => {
  triggerUploadForm($);
});

