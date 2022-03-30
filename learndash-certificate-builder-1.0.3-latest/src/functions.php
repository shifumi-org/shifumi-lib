<?php
/**
 * Contains set of helper function.
 *
 * @file
 * @package Learndas_Certificate_Builder
 */

use LearnDash_Certificate_Builder\Controller\Fonts_Manager;

/**
 * Return the plugin asset URL
 *
 * @param string $path relative path to the assets.
 *
 * @return string
 */
function learndash_certificate_builder_asset_url( $path ) {
	$base_url = plugin_dir_url( dirname( __FILE__ ) );

	return untrailingslashit( $base_url ) . $path;
}

/**
 * Return the plugin asset path
 *
 * @param string $path relative path to the assets.
 *
 * @return string
 */
function learndash_certificate_builder_path( $path ) {
	$base_path = plugin_dir_path( dirname( __FILE__ ) );

	return $base_path . $path;
}

/**
 * Activate gutenberg for this post type
 *
 * @param array $args The big array that contains all the CPT define.
 *
 * @return mixed
 */
function learndash_certificate_builder_enable_show_in_rest( $args ) {
	if ( ! function_exists( 'learndash_get_post_type_slug' ) ) {
		return;
	}
	$post_type = learndash_get_post_type_slug( 'certificate' );
	if ( isset( $args[ $post_type ] ) ) {
		$args[ $post_type ]['cpt_options']['show_in_rest'] = true;
	}

	return $args;
}

add_filter( 'learndash_post_args', 'learndash_certificate_builder_enable_show_in_rest' );

// register the Font Manager in Certificates section.
add_action(
	'learndash_settings_pages_init',
	function () {
		Fonts_Manager::add_page_instance();
	}
);

add_action( 'enqueue_block_editor_assets', 'learndash_certificate_builder_enqueue_block_extender' );
/**
 * Enqueue the extender js to apply new functions to ld block. This need to add before the core block queue.
 */
function learndash_certificate_builder_enqueue_block_extender() {
	if ( ! function_exists( 'learndash_get_post_type_slug' ) ) {
		return;
	}
	// only load inside certificate.
	global $current_screen;
	if ( ! is_object( $current_screen ) ) {
		return;
	}

	if ( learndash_get_post_type_slug( 'certificate' ) === $current_screen->id ) {
		wp_enqueue_script(
			'learndash-certificate-builder-extender',
			learndash_certificate_builder_asset_url( '/build/certificate-builder-extender.js' ),
			array(
				'wp-hooks',
				'wp-polyfill',
			),
			time(),
			false
		);
	}
}
