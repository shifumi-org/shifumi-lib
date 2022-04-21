jQuery( document ).ready( function( $ ) {
    var pricingPane = $( '#woocommerce-product-data' ),
        productType = $( 'select#product-type' ).val();
    if( pricingPane.length ){
        pricingPane.find( '.pricing' ).addClass( 'show_if_course' ).end()
            .find( '.inventory_tab' ).addClass( 'hide_if_course' ).end()
            .find( '.shipping_tab' ).addClass( 'hide_if_course' ).end()
            .find( '.attributes_tab' ).addClass( 'hide_if_course' )
        ;

        if ( productType === 'course' ) {
            pricingPane.find( '.pricing' ).show();
        }
    }

    // Make tax fields visible on course type
    var $tax_field_group = $( '._tax_status_field' ).parent( '.options_group' );

    $tax_field_group.addClass( 'show_if_course' );

    $( window ).on( 'load', function( e ) {
        e.preventDefault();
        if ( $( '#product-type' ).val() == 'course' ) {
            $tax_field_group.show();
        }
    });
});