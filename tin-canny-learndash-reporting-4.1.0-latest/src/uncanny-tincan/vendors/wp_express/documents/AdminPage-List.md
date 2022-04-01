* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)
* [Back to Admin Page](https://github.com/sujin2f/wp_express/blob/master/documents/AdminPage.md)

# AdminPage : List Page
List Page makes an admin page which shows a list.
```php
$AdminPage = new WE\AdminPage\ListPage( 'My Admin Page' );
```

![List Page Example](https://github.com/sujin2f/wp_express/blob/master/documents/images/AdminList_001.png "List Page Example")

## Add Columns
```php
$AdminPage->column = 'Number';
$AdminPage->column = 'Link';
```
## Add Sortable Columns
```php
$AdminPage->sortable = 'Name';
```

## Set Data, Number of Items, and Items per Page
```php
$AdminPage->data = 'patchData'; // It has to be a callback
$AdminPage->count = 'patchCount'; // It has to be a callback
$AdminPage->per_page = 10;

// The keys have to be matched with coulumn names.
function patchData() {
	// The number of array has to be same with per_page attribtue unless it is the last page.
	return array(
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
		array( 'Number' => 1, 'Link' => '<a href="#">Link</a>', 'Name' => 'Your Link' ),
	);
}

function patchCount() {
	return 100;
}
```

## Template Callback
The first template callback executes before the table, and others execute after the table.
```php
$AdminPage->template = 'templateAdminPage01';
$AdminPage->template = 'templateAdminPage02';
```
