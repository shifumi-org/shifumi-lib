<?php
namespace LearnDash\Course_Grid;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

use LearnDash\Course_Grid\Utilities;

class Blocks
{
    public function __construct()
    {
        add_action( 'plugins_loaded', [ $this, 'init_blocks' ] );
    }

    public function init_blocks()
	{
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ], 20 );

		$blocks = [
			'learndash_course_grid' => 'LearnDash_Course_Grid',
			'learndash_course_grid_filter' => 'LearnDash_Course_Grid_Filter',
		];

		foreach ( $blocks as $id => $class ) {
			$classname = '\\LearnDash\\Course_Grid\\Gutenberg\\Blocks\\' . $class;
			$this->$id = new $classname();
		}
	}

	public function enqueue_block_editor_assets()
	{
		$asset_file = include LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'includes/gutenberg/assets/js/index.asset.php';

		wp_register_script( 'learndash-course-grid-block-editor-helper', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'includes/gutenberg/assets/js/editor.js', [], LEARNDASH_COURSE_GRID_VERSION );
		
		wp_enqueue_script( 'learndash-course-grid-block-editor', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'includes/gutenberg/assets/js/index.js', array_merge( $asset_file['dependencies'], [ 'learndash-course-grid-block-editor-helper' ] ), $asset_file['version'] );

		wp_enqueue_style( 'learndash-course-grid-block-editor', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'includes/gutenberg/assets/css/editor.css', [], LEARNDASH_COURSE_GRID_VERSION );

		wp_localize_script( 
			'learndash-course-grid-block-editor', 
			'LearnDash_Course_Grid_Block_Editor', 
			[
				'post_types' => Utilities::get_post_types_for_block_editor(),
				'skins' => \LearnDash\course_grid()->skins->get_skins(),
				'cards' => \LearnDash\course_grid()->skins->get_cards(),
				'editor_fields' => \LearnDash\course_grid()->skins->get_default_editor_fields(),
				'image_sizes' => Utilities::get_image_sizes_for_block_editor(),
				'orderby' => Utilities::get_orderby_for_block_editor(),
				'taxonomies' => Utilities::get_taxonomies_for_block_editor(),
				'paginations' => Utilities::get_paginations_for_block_editor(),
			]
		);
	}
}