/* eslint-disable no-undef, no-restricted-globals, func-names */
jQuery(document).ready(($) => {
  const $sncForm = $('.snc-media_enbed_form');

  // Get Code From PHP
  $sncForm.ajaxForm({
    success: (response) => {
      const data = JSON.parse(response);
      const win = window.dialogArguments || opener || parent || top;
      win.send_to_editor(data.shortcode);
    },
  });
});
