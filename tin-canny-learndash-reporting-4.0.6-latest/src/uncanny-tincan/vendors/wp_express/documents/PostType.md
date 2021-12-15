* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)

# PostType
To add an empty page, create a reference.
```php
$PostType = new WE\PostType( 'My Art' );
```

## Settings
You can set initializing parameters.
See [https://codex.wordpress.org/Function_Reference/register_post_type] for more information.
```php
$PostType->public = false;
$PostType->description = 'This is my new Post Type';
$PostType->supports = 'author';
$PostType->supports = 'excerpt';
$PostType->supports = 'trackbacks';
$PostType->supports = [ 'comments', 'revisions' ];
```

## Post Meta
Post Meta is same with AdminOption and Taxonomy Meta. "Section" represents a meta-box. ```version``` attribute is same as well.
```php
$PostType->version = '1.0.0';

$PostType->section = 'Post Meta Set 1';
$PostType->setting = "Image";
$PostType->setting->type = 'file';

$PostType->section = 'Post Meta Set 2';
$PostType->setting = "Image Size";
$PostType->set = 'Width';
$PostType->set->type = 'number';
$PostType->set = 'Height';
$PostType->set->type = 'number';
```

[See the Settings Document](https://github.com/sujin2f/wp_express/blob/master/documents/Settings.md)

## Taxonomy
```php
$PostType->taxonomy = 'My Tag';
```

Or you can add \WE\Taxonomy.

```php
$tax = new WE\Taxonomy( 'My Tag' );
$PostType->taxonomy = $tax;
```
[See the \WE\Taxonomy Document](https://github.com/sujin2f/wp_express/blob/master/documents/Taxonomy.md) for more information.

## Manage List Table
Use ```column``` attribute to add a column to the list table. Keywords can be the name of Post Meta and Taxonomy name. The special keyword ```Thumbnail``` shows the post thumbnail.
```php
$PostType->column = 'Thumbnail';
$PostType->column = 'Image';
```

If you want to put the column at the left side of table, use ```column_before``` attribute.
```php
$PostType->column_before = 'Thumbnail';
$PostType->column_before = 'Image';
```