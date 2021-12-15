/**
 * @package storyline-and-captivate
 * @author Uncanny Owl
 * @version 0.0.1
 */

// Medea Upload Form
jQuery( document ).ready( function($) {
  $( '.vc-snc-trigger' ).ready(function() {
    var $_vc = $( '.vc-snc-trigger' ).parent();

    var data = {
      action: 'vc-snc'
    };

    $.post( ajaxurl, data, function( response ) {
      $_vc.prepend( response );
      trigger_upload_form( $, 'visual composer' );

      trigger_vc_snc_mode();
      trigger_sidemenu();
      trigger_library();
      trigger_change_file_btn();
    });

    function trigger_change_file_btn() {
      $( '.vc_row .change_file' ).click( function( e ) {
        e.preventDefault();

        trigger_vc_snc_mode( 'file_chooser' );
      });
    }

    function trigger_sidemenu() {
      $( '#vc_properties-panel #sidemenu a, #vc_ui-panel-edit-element #sidemenu a' ).click( function( e ) {
        e.preventDefault();
        var id = $(this).attr( 'data-id' );

        $( '#snc-media_upload_file_wrap' ).hide();
        $( '#snc-content_library_wrap' ).hide();
        $( '.vc_row #sidemenu li' ).removeClass( 'current' );

        switch( id ) {
          case 'upload_file' :
            $( '#snc-media_upload_file_wrap' ).show();
            $( '.vc_row #sidemenu li#tab-upload' ).addClass( 'current' );
          break;
          case 'library' :
            $( '#snc-content_library_wrap' ).show();
            $( '.vc_row #sidemenu li#tab-snc-library' ).addClass( 'current' );
          break;
        }
      });
    }

    function trigger_library() {
      $( '#vc_properties-panel #snc-content_library_wrap table td a.content_title, #vc_properties-panel #snc-content_library_wrap table td a.choose, #vc_ui-panel-edit-element #snc-content_library_wrap table td a.content_title, #vc_ui-panel-edit-element #snc-content_library_wrap table td a.choose' ).click( function( e ) {
        e.preventDefault();
        var id = $(this).attr( 'data-item_id' );
        var name = $(this).attr( 'data-item_name' );

        $( '#vc_properties-panel .vc-snc-trigger input' ).attr( 'value', id );
        $( '#vc_properties-panel .vc-snc-name input' ).attr( 'value', name );

        $( '#vc_ui-panel-edit-element .vc-snc-trigger input' ).attr( 'value', id );
        $( '#vc_ui-panel-edit-element .vc-snc-name input' ).attr( 'value', name );

        trigger_vc_snc_mode();
      });

      $( '#vc_properties-panel #snc-content_library_wrap table td a.delete, #vc_ui-panel-edit-element #snc-content_library_wrap table td a.delete' ).click( function( e ) {
        e.preventDefault();

        var item_id = $( this ).attr( 'data-item_id' );
        delete_snc_from_table( item_id, 'vc' );
      });
    }
  });
});

function trigger_vc_snc_mode( mode ) {
  if ( !mode && jQuery( '.vc_row .vc-snc-trigger input' ).val() ) {
    jQuery( '.vc-snc-embed' ).show();

    jQuery( '#vc_properties-panel #sidemenu, #vc_ui-panel-edit-element #sidemenu' ).hide();
    jQuery( '#vc_properties-panel #snc-media_upload_file_wrap, #vc_ui-panel-edit-element #snc-media_upload_file_wrap' ).hide();
    jQuery( '#vc_properties-panel #snc-content_library_wrap, #vc_ui-panel-edit-element #snc-content_library_wrap' ).hide();

    jQuery( '#vc-snc-choosen-file' ).show();
    jQuery( '.vc_row .vc_shortcode-param[data-param_name="embed_type"]' ).show();

    var title = jQuery( '.vc_row .vc_shortcode-param[data-param_name="item_name"] input' ).val();
    jQuery( '#vc-snc-choosen-file code' ).html( title );

    jQuery( '.vc_shortcode-param' ).removeClass( 'vc-snc-hidden' );

  } else if ( mode === 'file_chooser' ) {
    jQuery( '.vc-snc-embed' ).hide();

    jQuery( '#vc_properties-panel #sidemenu, #vc_ui-panel-edit-element #sidemenu' ).show();
    jQuery( '#vc_properties-panel #snc-media_upload_file_wrap, #vc_ui-panel-edit-element #snc-media_upload_file_wrap' ).show();

    jQuery( '#vc-snc-choosen-file' ).hide();
    jQuery( '.vc_row .vc_shortcode-param[data-param_name="embed_type"]' ).hide();

    jQuery( '.vc_shortcode-param' ).addClass( 'vc-snc-hidden' );
  }
}
