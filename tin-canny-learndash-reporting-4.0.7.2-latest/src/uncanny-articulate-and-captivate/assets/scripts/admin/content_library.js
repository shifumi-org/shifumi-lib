/* eslint-disable no-undef, func-names */
jQuery(document).ready(($) => {
  const contentLibraryTable = '#snc-content_library_wrap table';

  $(`${contentLibraryTable} a.content_title, ${contentLibraryTable} a.show`).click(function (e) {
    e.preventDefault();
    const id = $(this).attr('data-item_id');

    $('.embed_information').each(function () {
      const theId = $(this).attr('data-item_id');

      if (id !== theId) {
        $(this).hide();
      } else {
        $(this).toggle();
      }
    });
  });
});
