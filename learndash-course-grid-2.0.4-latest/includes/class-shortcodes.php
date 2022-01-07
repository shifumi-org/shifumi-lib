<?php
namespace LearnDash\Course_Grid;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class Shortcodes
{
    public function __construct()
    {
        add_action( 'init', [ $this, 'init_shortcodes' ] );
    }

    public function init_shortcodes()
	{
		$shortcodes = [
			'learndash_course_grid' => 'LearnDash_Course_Grid',
			'learndash_course_grid_filter' => 'LearnDash_Course_Grid_Filter',
		];

		foreach ( $shortcodes as $tag => $class ) {
			$classname = '\\LearnDash\\Course_Grid\\Shortcodes\\' . $class;
			$this->$tag = new $classname();
		}
	}
}