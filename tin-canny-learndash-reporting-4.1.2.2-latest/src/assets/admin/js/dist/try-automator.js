class TryAutomator {
	constructor(){
		// Get the elements
		this.getElements();

		// Check if the element exists
		if ( this.$elements.tryAutomatorXIcon.length == 1 ){
			// Listen clicks to it
			this.handleHide();
		}
	}

	getElements(){
		this.$elements = {
			tryAutomatorXIcon: jQuery( '#ultc-sidebar-try-automator-close' )
		}
	}

	restCall( endPoint = null, data = null, onSuccess = null, onFail = null ){
		// Define the valid endpoints
		let validEndpoints = [ 'try_automator_visibility' ];

		// Check if the endPoint parameter is a valid endpoint
		if ( validEndpoints.includes( endPoint ) ){
			// Do AJAX
			jQuery.ajax({
				method: 'POST',
				url:    ultcRestApiSetup.root + endPoint + '/',
				data:   jQuery.param( data ) + '&' + jQuery.param({ doing_rest: 1 }),

				// Attach Nonce the the header of the request
				beforeSend: function( xhr ){
					xhr.setRequestHeader( 'X-WP-Nonce', ultcRestApiSetup.nonce );
				},

				success: function( response ){
					// Check if onSuccess
					if ( typeof onSuccess !== 'undefined' ){
						// Invoke callback
						onSuccess( response );
					}
				},

				statusCode: {
					403: function(){
						location.reload();
					}
				},

				fail: function ( response ){
					if ( typeof onFail !== 'undefined' ){
						onFail( response );
					}
				},
			});
		}
		else {
			console.error( `The ${ endPoint } endPoint does not exists` );
		}
	}

	handleHide(){
		// Listen clicks to the "X" icon
		this.$elements.tryAutomatorXIcon.on( 'click', ( event ) => {
			// Prevent default
			event.preventDefault();

			// Get the row
			const $itemContainer = jQuery( event.currentTarget ).closest( '.ultc-sidebar-featured-item-container' );

			// Add loading class to the row
			$itemContainer.addClass( 'ultc-sidebar-featured-item-container--loading' );

			// Do a call to hide the row
			this.restCall( 'try_automator_visibility', {
				action: 'hide-forever'
			}, ( response ) => {
				// Check if the task was successful
				if ( response.success ){
					// Hide the row
					$itemContainer.remove();
				}
				else {
					// Remove the loading class to the row
					$itemContainer.removeClass( 'ultc-sidebar-featured-item-container--loading' );
				}
			}, () => {
				// Remove the loading class to the row
				$itemContainer.removeClass( 'ultc-sidebar-featured-item-container--loading' );
			});
		});
	}
}

// Do on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new TryAutomator();
});
