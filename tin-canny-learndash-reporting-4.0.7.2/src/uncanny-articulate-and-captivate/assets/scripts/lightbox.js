/**
 * @package storyline-and-captivate
 * @author Uncanny Owl
 * @version 0.0.1
 */

// Medea Embed Information
jQuery( document ).ready( function($) {
  $( '.colorbox_iframe' ).each( function() {
    var option = {};
    option.iframe = true;

    if ( $(this).attr( 'data-scroll' ) === 'false' ) {
      option.scrolling = false;
    }

    if ( $(this).attr( 'data-width' ) ) {
      option.width = $(this).attr( 'data-width' );
    }
    if ( $(this).attr( 'data-height' ) ) {
      option.height = $(this).attr( 'data-height' );
    }
    if ( $(this).attr( 'data-transition' ) ) {
      option.transition = $(this).attr( 'data-transition' );
    }
    if ( $(this).attr( 'data-lightbox_title' ) ) {
      option.title = $(this).attr( 'data-lightbox_title' );
    }

    $(this).colorbox( option );
  });

  $( 'a.nivo_iframe' ).each( function() {
    var option = {};
    option.theme = 'default';

    if ( $(this).attr( 'data-transition' ) ) {
      option.effect = $(this).attr( 'data-transition' );
    }

    $(this).nivoLightbox( option );
  });
});
