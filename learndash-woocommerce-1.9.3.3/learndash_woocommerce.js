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
});