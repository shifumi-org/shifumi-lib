* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)

# Taxonomy
To add an empty page, create a reference. Default is adding to post.
```php
$tax = new WE\Taxonomy( 'My Tag' );
```

## Settings
You can set initializing parameters.
See [https://codex.wordpress.org/Function_Reference/register_taxonomy] for more information.
```php
$tax->show_ui = false;
$tax->description = 'This is my new Taxonomy';
$tax->post = 'My Post Type';
```

## Taxonomy Meta
Taxonomy Meta is same with AdminOption and Post Meta. ```version``` attribute is same as well.
```php
$tax->version = '1.0.0';

$tax->section = 'Post Meta Set 1';
$tax->setting = "Image";
$tax->setting->type = 'file';

$tax->section = 'Post Meta Set 2';
$tax->setting = "Image Size";
$tax->set = 'Width';
$tax->set->type = 'number';
$tax->set = 'Height';
$tax->set->type = 'number';
```
[See the Settings Document](https://github.com/sujin2f/wp_express/blob/master/documents/Settings.md)

## Manage List Table
Use ```column``` attribute to add a column to the list table. Keywords can be the name of Taxonomy Meta.
```php
$tax->column = 'Thumbnail';
$tax->column = 'Image';
```

If you want to put the column at the left side of table, use ```column_before``` attribute.
```php
$tax->column_before = 'Thumbnail';
$tax->column_before = 'Image';
```