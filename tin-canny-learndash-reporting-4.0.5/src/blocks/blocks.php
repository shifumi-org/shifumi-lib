<?php

// Exit if accessed directly.
if ( ! defined( 'UO_ABS_PATH' ) ) {
	exit;
}

// Check if Gutenberg exists
if ( function_exists( 'register_block_type' ) ) {
	// Register Blocks
	add_action( 'init', function () {
		require_once( dirname( __FILE__ ) . '/src/tincanny-content/block.php' );
		require_once( dirname( __FILE__ ) . '/src/tincanny-course-user-reports/block.php' );
		require_once( dirname( __FILE__ ) . '/src/tincanny-individual-quiz-report/block.php' );
		require_once( dirname( __FILE__ ) . '/src/tincanny-group-quiz-report/block.php' );
	} );

	// Enqueue Gutenberg block assets for both frontend + backend.

	add_action( 'enqueue_block_assets', function () {
		wp_enqueue_style(
			'tclr-gutenberg-blocks',
			plugins_url( 'blocks/dist/blocks.style.build.css', dirname( __FILE__ ) ),
			[],
			UNCANNY_REPORTING_VERSION
		);
	} );

	// Enqueue Gutenberg block assets for backend editor.

	add_action( 'enqueue_block_editor_assets', function () {
		wp_enqueue_script(
			'tclr-gutenberg-editor',
			plugins_url( 'blocks/dist/blocks.build.js', dirname( __FILE__ ) ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ],
			UNCANNY_REPORTING_VERSION,
			true
		);

		// Add Tin Canny security data
		$vc_ajax = [
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'   => wp_create_nonce( 'vc-snc-data-nonce' ),
			'ajax_nonce_2' => wp_create_nonce( 'snc-media_upload_form' ),
		];

		wp_localize_script( 'tclr-gutenberg-editor', 'vc_snc_data_obj', $vc_ajax );

		wp_enqueue_style(
			'tclr-gutenberg-editor',
			plugins_url( 'blocks/dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			[ 'wp-edit-blocks' ],
			UNCANNY_REPORTING_VERSION
		);
	} );

	if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
		// Legacy filter
		// Create custom block category
		add_filter( 'block_categories', 'uo_tincanny_block_categories', 10, 2 );
	} else {
		// Create custom block category
		add_filter( 'block_categories_all', 'uo_tincanny_block_categories', 10, 2 );
	}

	// Create custom block category
	/**
	 * @param $categories
	 * @param $post
	 *
	 * @return array
	 */
	function uo_tincanny_block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'uncanny-learndash-reporting',
					'title' => __( 'Tin Canny Reporting for LearnDash', 'uncanny-learndash-reporting' ),
				),
			)
		);
	}
}
