/* eslint-disable no-undef, no-restricted-globals, no-alert, func-names */
function deleteSncFromTableContent (itemId, mode) {
  if (confirm('Do you really want to delete this?')) {
    const data = {
      action: 'SnC_Content_Delete',
      item_id: itemId,
      mode,
      security: jQuery('#snc-content_library_wrap input[name="security"]').val(),
    }

    jQuery.post(ajaxurl, data, () => {
      if (mode === 'media library' || mode === 'vc') {
        jQuery(`#snc-content_library_wrap table tr[data-item_id="${itemId}"]`).remove()
      } else {
        location.reload()
      }
    })
  }
}

jQuery(document).ready(($) => {

  // <-- Delete
  $('#snc-content_library_wrap table a.delete').click(function (e) {
    e.preventDefault()
    const mode = ($('#snc-content_library_wrap').length) ? 'media library' : 'upload form'
    const itemId = $(this).attr('data-item_id')

    deleteSncFromTableContent(itemId, mode)
  })
  // Delete -->

  //Replace content pop-up settings!!
  let itemId = ''
  $('#snc-content_library_wrap table a.snc_replace_confirm').click(function (e) {
    e.preventDefault()
    let $itemId = $(this).data('item_id')

    // Add ID to the buttons
    $('#snc-delete-book-only').data('item_id', $itemId)
    $('#snc-delete-all-data').data('item_id', $itemId)

    $('#replace_placeholder')
      .attr('href', 'media-upload.php?content_id=' + $itemId + '&type=snc&tab=upload&min-height=400&no_tab=1&TB_iframe=true')
      .attr('data-item_id', $itemId)
  })

  //
  $( '.tclr-replace-content__task-btn' ).on( 'click', function(){
    // Get button
    let $button = $( this );

    // Get task
    let task = $button.data( 'task' );

    // Hide step 1 container, and show the step 2 container
    $( '.tclr-replace-content__step-1' ).hide();
    $( '.tclr-replace-content__step-2' ).show();

    // Hide the container of both tasks
    $( '#bookmark-confirmation, #all-confirmation' ).hide();

    // Check the task
    if ( task === 'remove-bookmark' ){
      // Show container
      $( '#bookmark-confirmation' ).show();
    }
    else if ( task === 'remove-all-data' ){
      $( '#all-confirmation' ).show();
    }
  });

  //
  $( '.tclr-replace-content__cancel-2-step-btn' ).on( 'click', function(){
    // Hide the container of both tasks
    $( '#bookmark-confirmation, #all-confirmation' ).hide();

    // Show the container of the first step and hide the container of the second one
    $( '.tclr-replace-content__step-1' ).show();
    $( '.tclr-replace-content__step-2' ).hide();
  });

  //
  $('#snc-delete-book-only').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id')
    $button.addClass( 'tclr-btn--loading' );

    const mode = ($('#snc-content_library_wrap').length) ? 'media library' : 'upload form'
    const data1 = {
      action: 'SnC_Content_Bookmark_Delete',
      item_id: itemId,
      mode,
      security: $('#snc-content_library_wrap input[name="security"]').val(),
    }

    $.post(ajaxurl, data1).done(function () {
      $button.removeClass( 'tclr-btn--loading' );
      $( '.tclr-replace-content__step-1' ).show();
      $( '.tclr-replace-content__step-2' ).hide();

      $('#TB_closeWindowButton').trigger('click')
      setTimeout(function () {$('#replace_placeholder').trigger('click')}, 1000)
    })
    //}
  })

  //
  $('#snc-delete-all-data').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id')
    $button.addClass( 'tclr-btn--loading' );

    const mode = ($('#snc-content_library_wrap').length) ? 'media library' : 'upload form'
    const data2 = {
      action: 'SnC_Content_Delete_All',
      item_id: itemId,
      mode,
      security: $('#snc-content_library_wrap input[name="security"]').val(),
    }

    $.post(ajaxurl, data2).done(function () {
      $button.removeClass( 'tclr-btn--loading' );
      $( '.tclr-replace-content__step-1' ).show();
      $( '.tclr-replace-content__step-2' ).hide();
      $('#TB_closeWindowButton').trigger('click')
      setTimeout(function () {$('#replace_placeholder').trigger('click')}, 1000)
    })
    //}
  })

  /* ES5 */
  var TinCannyModulesSearch = {
    init: function(){
      // Get elements
      this.getElements();

      // Get required data
      this.getData();

      // Create Fuse instance
      this.initFuse();

      // Search
      this.listenSearchField();
    },

    getElements: function(){
      this.$elements = {
        searchField: $( '#tclr-classic-editor-content-library-search' ),
        tableContainer: $( '.tclr-classic-editor-content-library__list' ),
        tableItems:  $( '.tclr-classic-editor-content-library__list tbody tr.tclr-classic-editor-content-library__item' )
      }
    },

    getData: function(){
      // Create an array of objects with the Tin Canny content items
      var items = [];

      // Iterate DOM elements (tr)
      $.each( this.$elements.tableItems, function( index, tr ){
        var $tr = $( tr );

        // Get row data
        var rowData = {
          title: $tr.data( 'item_name' ),
          $element: $tr
        }

        // Add the row to the main items array
        items.push( rowData );
      });

      this.items = items;
    },

    initFuse: function(){
      this.Fuse = new Fuse( this.items, {
        keys: [ 'title' ],
        threshold: 0,
        ignoreLocation: true
      });
    },

    listenSearchField: function(){
      // Reference to this instance
      var thisRef = this;

      this.$elements.searchField.on( 'input', function(){
        // Get the value of the search field
        var searchFieldValue = thisRef.$elements.searchField.val();

        // Check if the search field has a value
        if ( searchFieldValue !== '' ){
          // Search
          thisRef.search( searchFieldValue );
        }
        else {
          // Otherwise, show all
          thisRef.showAll();
        }
      });
    },

    search: function( searchQuery ){
      // Perform search
      var filteredItems = this.Fuse.search( searchQuery );

      // Enable search mode
      // We're adding a class to the container to hide all the rows
      // and we're going to iterate through the ones we have to show
      // to add a class to them. We're doing this to reduce the number
      // of DOM modifications
      this.$elements.tableContainer.addClass( 'tclr-classic-editor-content-library__list--search-mode' );

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass( 'tclr-classic-editor-content-library__item--visible' );

      // Check if there are results
      if ( filteredItems.length > 0 ){
        // Hide the "no results" row
        this.$elements.tableContainer.removeClass( 'tclr-classic-editor-content-library__list--no-results' );

        // Iterate the results
        filteredItems.forEach(function( row ){
          // Add class to the item to show it
          row.item.$element.addClass( 'tclr-classic-editor-content-library__item--visible' );
        });
      }
      else {
        // Show the "no results" row
        this.$elements.tableContainer.addClass( 'tclr-classic-editor-content-library__list--no-results' );
      }
    },

    showAll: function(){
      // Disable search mode
      this.$elements.tableContainer.removeClass( 'tclr-classic-editor-content-library__list--search-mode' );

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass( 'tclr-classic-editor-content-library__item--visible' );

      // Hide the "no results" row
      this.$elements.tableContainer.removeClass( 'tclr-classic-editor-content-library__list--no-results' );
    }
  }

  TinCannyModulesSearch.init();
})