* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)

Create an Ajax communication.
```php
$myAjax = new WE\Ajax( 'My Ajax' ); // Make a new Ajax connection.
```
In this case, the ajax key is ```my_ajax```, and security nonce is ```wp_create_nonce( 'my_ajax', 'security' )```.

# Server Callback
Get the Ajax request by setting a callback function or method.
```php
$AjaxTest->serverCallback = 'MyServerCallback';

function MyServerCallback() {
	var_dump( $_POST );
}
```

# Form
It automatically creates a javascript function ```weAjax( ajax_key )``` which send and receive an Ajax request. It triggers automatically if you assign a form's HTML ID.
```php
$AjaxTest->form = 'my-ajax-form';

// <-- Example of HTML form
$AdminPage = new WE\AdminPage( 'My Admin Page' );
$AdminPage->template = 'myAdminPage';
function myAdminPage() {
	?>
	<form id="my-ajax-form">
		<input name="text-value" />
		<button onclick="weAjax( 'My Ajax' ); return false;">Click</button>
	</form>
	<?php
}
// Example of HTML form -->
```
This send an Ajax data to the serverCallback when the user clicks the button.

# Client Callback
Set the return value from the Ajax. It must be written by javascript, and ```response``` variable contains the Ajax respond.
```php
$AjaxTest->clientCallback = 'MyClientCallback';

function MyClientCallback() {
	?>
	console.log( response );
	<?php
}
```

# Manual javascript
```js
jQuery( '#my-ajax-form button' ).click( function( e ) {
	e.preventDefault();

	var data = {	// Ajax key and nonce contain the variables
		'action': we_ajax_keys[ 'My Ajax' ],
		'security' : we_ajax_nonces[ 'My Ajax' ],
		'text-value' : jQuery( 'input[name="text-value"]' ).val()
	};

	jQuery.post( we_ajax_url, data, function( response ) {
		// TODO : Do something
	});
});
```
