# Setup 

1. Include (require_once 'path/to/class-auto-plugin-install.php') the main file anywhere in your file. 
2. Hook the class method create_ajax in `admin_init` action or similar.

```php
<?php
add_action( 'admin_init', 'sample_callback', 99 );

function sample_callback() {
    $one_click_install = new \uncanny_one_click_installer\Auto_Plugin_Install();
    $one_click_install->create_ajax();
}

```

3. Then somewhere in your plugin 'view' or 'template', call 'button' method to display the button.

```php
<div id="my-section">
<?php
$one_click_install = new \uncanny_one_click_installer\Auto_Plugin_Install();
// This will render the button
echo $one_click_install->button( 'uncanny-automator' );

// This will render the button that will redirect after installation
echo $one_click_install->button( 'uncanny-automator', admin_url( 'post-new.php?post_type=uo-recipe' ) );
?>
</div>

```

4. Check if its working. That's it!

## Filters

#### Adding new html class attribute to the button
```php
<?php
add_filter( 'uncanny_one_click_install_button_class', 'sample_cb', 10, 2 );

function sample_cb( $classes, $plugin_info ) {
	if ( 'woocommerce' === $plugin_info->slug ) {
		$classes[] = 'woocommerce-button';
		$classes[] = 'any-class-here';
	}
	return $classes;
}
?>
```

#### Changing the button plugin installation text

```php
<?php

add_filter( 'uncanny_one_click_install_plugin_initial_text', 'sample_cb', 10, 2 );

function sample_cb( $text, $plugin_info ) {
	if ( 'hello-dolly' === $plugin_info->slug ) {
		return 'Install Hello Dolly plugin'; // Do not translate plugin name.
	}

	return $text;
}
?>

```
#### Changing the button text when plugin is installed but not activated
```php
<?php
add_filter( 'uncanny_one_click_install_plugin_installed_text', 'sample_cb', 10, 2 );

function sample_cb( $text, $plugin_info ) {

	if ( 'hello-dolly' === $plugin_info->slug ) {
		return 'Activate Hello Dolly plugin'; // Do not translate plugin name.
	}

	return $text;

}
?>
```
#### Changing the button text when plugin is active 
```php
<?php
add_filter( 'uncanny_one_click_install_plugin_active_text', 'sample_cb', 10, 2 );

function sample_cb( $text, $plugin_info ) {
	if ( 'akismet' === $plugin_info->slug ) {
		return 'Akismet is Active'; // Do not translate plugin name.
	}

	return $text;
}

