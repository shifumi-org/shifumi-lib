* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)
* [Back to Admin Page](https://github.com/sujin2f/wp_express/blob/master/documents/AdminPage.md)

# AdminPage : Options Page
```php
$AdminPage = new WE\AdminPage\Options( 'My Admin Page' );
```
Options Page is the page which provides input fields.

![Options Page Example](https://github.com/sujin2f/wp_express/blob/master/documents/images/AdminOption_001.png "Options Page Example")

## Version
The version is important for the perfomance. When version value isn't declared or declared as '0.0.0', an explanation will be appeared on the admin page. Moreover, Options page calculates your setting whenever users visit the site. When you change the version, the setting will be updated and stored.

## Settings
[See the Settings Document](https://github.com/sujin2f/wp_express/blob/master/documents/Settings.md)

## Template Callback
The first template callback executes before the form, and others execute after the form.
```php
$AdminPage->template = 'templateAdminPage01';
$AdminPage->template = 'templateAdminPage02';
```

## Saving Callback
The saving values are automatically stored into WP options (see the explanation on your Options Page). If you have something to do when the saving action is triggered, this saving callback can be used.
```php
$AdminPage->save = 'saveAdminPage';

function saveAdminPage() {
	// Do Something,
}
```
You can also call a method as well.
```php
class myPluginInit {
	function __construct() {
		include_once( 'wp_express/autoload.php' );
		$AdminPage = new WE\AdminPage( 'My Admin Page' );
		$AdminPage->save = array( $this, 'saveTemplate' );
	}
  
	function saveTemplate() {
		// Do Something,
	}
}
```
