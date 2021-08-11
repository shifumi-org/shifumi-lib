<?php

/**
 * Enqueues block editor styles and scripts.
 * 
 * Fires on `enqueue_block_editor_assets` hook.
 */
function learndash_propanel_editor_scripts() {
	// Make paths variables so we don't write em twice ;).
	$learndash_block_path        = '../assets/js/editor.blocks.js';
	$learndash_editor_style_path = '../assets/css/blocks.editor.css';

	// Enqueue the bundled block JS file.
	wp_enqueue_script(
		'ld-propanel-blocks-js',
		plugins_url( $learndash_block_path, __FILE__ ),
		array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' ),
		LEARNDASH_SCRIPT_VERSION_TOKEN
	);

	/**
	 * @TODO: This needs to move to an external JS library since it will be used globally.
	 */
	$ldlms                                       = array(
		'settings' => array(),
	);
	$ldlms_settings['settings']['custom_labels'] = LearnDash_Settings_Section_Custom_Labels::get_section_settings_all();
	if ( ( is_array( $ldlms_settings['settings']['custom_labels'] ) ) && ( ! empty( $ldlms_settings['settings']['custom_labels'] ) ) ) {
		foreach ( $ldlms_settings['settings']['custom_labels'] as $key => $val ) {
			if ( empty( $val ) ) {
				$ldlms_settings['settings']['custom_labels'][ $key ] = LearnDash_Custom_Label::get_label( $key );
				if ( substr( $key, 0, strlen( 'button' ) ) != 'button' ) {
					$ldlms_settings['settings']['custom_labels'][ $key . '_lower' ] = learndash_get_custom_label_lower( $key );
					$ldlms_settings['settings']['custom_labels'][ $key . '_slug' ]  = learndash_get_custom_label_slug( $key );
				}
			}
		}
	}

	$ldlms_settings['settings']['per_page']           = LearnDash_Settings_Section_General_Per_Page::get_section_settings_all();
	
	// Load the MO file translations into wp.i18n script hook.
	//learndash_load_inline_script_locale_data();

	wp_localize_script( 'ld-propanel-blocks-js', 'ld_propanel_settings', $ldlms_settings );

	// Enqueue optional editor only styles.
	wp_enqueue_style(
		'ld-propanel-blocks-editor-css',
		plugins_url( $learndash_editor_style_path, __FILE__ ),
		array(),
		LEARNDASH_SCRIPT_VERSION_TOKEN
	);
	wp_style_add_data( 'ld-propanel-blocks-editor-css', 'rtl', 'replace' );

	// Call our function to load CSS/JS used by the shortcodes.
	//$ld_propanel = LearnDash_ProPanel::get_instance();
	//$ld_propanel->scripts( true );
}
// Hook scripts function into block editor hook.
add_action( 'enqueue_block_editor_assets', 'learndash_propanel_editor_scripts' );

/**
 * Registers a custom block category.
 *
 * Fires on `block_categories` hook.
 *
 * @since 2.6.0
 *
 * @param array         $block_categories Optional. An array of current block categories. Default empty array.
 * @param WP_Post|false $post             Optional. The `WP_Post` instance of post being edited. Default false.
 *
 * @return array An array of block categories.
 */
function learndash_propanel_block_categories( $block_categories = array(), $post = false ) {

	$ld_block_cat_found = false;

	foreach ( $block_categories as $block_cat ) {
		if ( ( isset( $block_cat['slug'] ) ) && ( 'ld-propanel-blocks' === $block_cat['slug'] ) ) {
			$ld_block_cat_found = true;
		}
	}

	if ( false === $ld_block_cat_found ) {
		$block_categories[] = array(
			'slug'  => 'ld-propanel-blocks',
			'title' => esc_html__( 'LearnDash LMS ProPanel Blocks', 'learndash' ),
			'icon'  => false,
		);
	}

	// Always return $default_block_categories.
	return $block_categories;
}
add_filter( 'block_categories', 'learndash_propanel_block_categories', 30, 2 );
