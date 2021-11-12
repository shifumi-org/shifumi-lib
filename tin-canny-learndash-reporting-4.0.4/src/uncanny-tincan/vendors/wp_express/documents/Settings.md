* [Back to Top](https://github.com/sujin2f/wp_express/blob/master/README.md)

Setting options and meta data has the same structure.

# Settings
```php
$AdminPage->section = "Setting Section"; // Make Section (Optional)

$AdminPage->setting = "Thumnail Size"; // Make New Input Field

$AdminPage->setting = "Image Size"; // Make New Input Field Named Image Size. It has to be unique within a page. ( Default : text )
$AdminPage->setting->type = "number"; // Set the type of Image Size as number
$AdminPage->setting->default = "200"; // Set Default Value
$AdminPage->setting->description = "Image Size MUST be less than 1000";
$AdminPage->setting->class = "large-text"; // The class attribute of input tag ( Default : regular-text )

$AdminPage->setting = "HTML 01"; // The name won't appear if the type is html, so just make it unique.
$AdminPage->setting->type = "html";
$AdminPage->setting->html = "<p>This is HTML</p>";
```

## Supported Type
* text (default)
* number
* file
* checkbox
* radio
* select
* html
* textarea
* editor

## Option Attribute
Some types need option attribute. For radio and select, option is required, and for checkbox, it's optional.
```php
$AdminPage->setting = "Radio Example";
$AdminPage->setting->type = "radio"; // Make New Radio Field
$AdminPage->setting->option = "Radio Button 1"; // Put the first radio button
$AdminPage->setting->option = "Radio Button 2"; // Put the second radio button
$AdminPage->setting->option = [ 32, "Radio Button 3" ]; // You can add an array to set value and text
```

# Setting Groups (set)
You can make set input fields. It appears on one row.

```php
$AdminPage->setting = "Image Size Set";

$AdminPage->set = 'Width'; // New Field. This ID will be image-size-width.
$AdminPage->set->type = 'number';
$AdminPage->set->default = 370;
$AdminPage->set->class = 'small-text';

$AdminPage->set = 'Height';
$AdminPage->set->type = 'number';
$AdminPage->set->default = 250;
$AdminPage->set->class = 'small-text';
```
