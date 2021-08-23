* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)

# Admin Page
Admin Page has three options: Simple, Options, and List.

* [Simple Page](https://github.com/sujin2f/wp_express/blob/master/documents/AdminPage-Simple.md)
* [Options Page](https://github.com/sujin2f/wp_express/blob/master/documents/AdminPage-Options.md)
* [List Page](https://github.com/sujin2f/wp_express/blob/master/documents/AdminPage-List.md)

All type of admin pages can use the attributes below.

## Position
You can simply set by assigning a name of the menu. The default value is 'Settings', so it appears in the Settings.
```php
$AdminPage = new WE\AdminPage( 'My Admin Page' );	// Make new simple admin page.
$AdminPage->position = 'Appearance';
```
You can also set number to put it as a new menu as well.
```php
$AdminPage->position = 24;
```
This means you can apply submenus into your main menu.
```php
$RootPage = new WE\AdminPage( 'Root Page' );
$RootPage->position = 1;

$SubPage = new WE\AdminPage( 'Sub Page' );
$SubPage->position = 'Root Page';
```

## Template Callback
You can add contents by assigning a template callback property.
```php
$AdminPage->template = 'echoAdminPage1';
$AdminPage->template = 'echoAdminPage2';

function echoAdminPage1() {
	echo 'This is your admin page<br />';
}

function echoAdminPage2() {
	echo 'You can add multiple templates as well<br />';
}
```
You can also call a method as well.
```php
class myPluginInit {
	function __construct() {
		include_once( 'wp_express/autoload.php' );
		$AdminPage = new WE\AdminPage( 'My Admin Page' );
		$AdminPage->template = array( $this, 'adminTemplate' );
	}
  
	function adminTemplate() {
		/// Write the Code.
	}
}
```

![Simple Page](https://github.com/sujin2f/wp_express/blob/master/documents/images/AdminSimple_002.png "Simple Page")

## Script and Style
By assigning script and style, include them. It will affect only for the page you created.
```php
$AdminPage->script = 'http://where.is/file.js';

$AdminPage->style = 'http://where.is/file1.css';
$AdminPage->style = 'http://where.is/file2.css';
```

## Version
The version property affects to javascripts and css versions. The default value is ```0.0.0```
```php
$AdminPage->version = '1.0.0';
```

## Plugin
If you assign plugin name, the Setting link will appear on the table of Plugins menu.
```php
$AdminPage->plugin = 'WooCommerce';
```

![Plugin Assignment](https://github.com/sujin2f/wp_express/blob/master/documents/images/AdminSimple_003.png "Plugin Assignment")

## Capability
The default value is 'activate_plugins'.
```php
$AdminPage->capability = 'read';
```

## Key
You can change the page's key which appears in an address bar.
```php
$AdminPage->key = 'my-key';
```




