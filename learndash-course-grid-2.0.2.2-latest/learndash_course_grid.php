<?php
namespace LearnDash;

/**
 * Plugin Name: LearnDash LMS - Course Grid
 * Plugin URI: https://www.learndash.com/
 * Description: Build LearnDash course grid easily.
 * Version: 2.0.2.2
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-course-grid
 * Doman Path: languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use LearnDash\Course_Grid\Admin\Meta_Boxes;
use LearnDash\Course_Grid\Security;
use LearnDash\Course_Grid\Skins;
use LearnDash\Course_Grid\AJAX;
use LearnDash\Course_Grid\Shortcodes;
use LearnDash\Course_Grid\Blocks;
use LearnDash\Course_Grid\Compatibility;

class Course_Grid {
	private static $instance;

	public $shortcodes;

	public $blocks;

	public $skins;

	public $posts;

	public static function instance()
	{
		if ( ! isset( self::$instance ) || ! self::$instance instanceof self ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}

	public function __construct()
	{
		$this->define_constants();

		spl_autoload_register( [ $this, 'autoload' ] );

		$this->load_translations();

		$this->security   = new Security();
		$this->skins      = new Skins();
		$this->ajax       = new AJAX();
		$this->shortcodes = new Shortcodes();
		$this->blocks     = new Blocks();
		$this->compatibility = new Compatibility();

		// Include files manually
		include_once LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'includes/functions.php';

		// Admin
		$this->admin = new \stdClass();
		$this->admin->meta_boxes = new Meta_Boxes();
	}

	public function define_constants()
	{
		if ( ! defined( 'LEARNDASH_COURSE_GRID_VERSION' ) ) {
			define( 'LEARNDASH_COURSE_GRID_VERSION', '2.0.2.2' );
		}
		
		if ( ! defined( 'LEARNDASH_COURSE_GRID_FILE' ) ) {
			define( 'LEARNDASH_COURSE_GRID_FILE', __FILE__ );
		}		
		
		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}
		
		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_PATH' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_PATH', LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'templates/' );
		}

		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_URL' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_TEMPLATE_URL', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'templates/' );
		}

		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_ASSET_PATH' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_ASSET_PATH', LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'assets/' );
		}

		if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL' ) ) {
			define( 'LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'assets/' );
		}
		
		// Added for backward compatibility
		if ( ! defined( 'LEARNDASH_COURSE_GRID_COLUMNS' ) ) {
			define( 'LEARNDASH_COURSE_GRID_COLUMNS', 3 );
		}	
	}

	public function autoload( $class )
	{
		$class_components = explode( '\\', $class );
		$class_file = str_replace( '_', '-', strtolower( $class_components[ count( $class_components ) - 1 ] ) );
		$filename = $class_file . '.php';
		
		$file = false;

		if ( strpos( $class, 'LearnDash\\Course_Grid\\Shortcodes\\' ) !== false ) {
			$file = 'includes/shortcodes/class-' . $filename;
		} elseif ( strpos( $class, 'LearnDash\\Course_Grid\\Gutenberg\\Blocks\\' ) !== false ) {
			$file = 'includes/gutenberg/blocks/' . $class_file . '/index.php';
		} elseif ( strpos( $class, 'LearnDash\\Course_Grid\\Admin\\' ) !== false ) {
			$file = 'includes/admin/class-' . $filename;
		} elseif ( strpos( $class, 'LearnDash\\Course_Grid\\Lib' ) !== false ) {
			$file = 'includes/lib/class-' . $filename;
		} elseif ( strpos( $class, 'LearnDash\\Course_Grid\\' ) !== false ) {
			$file = 'includes/class-' . $filename;	
		}

		if ( $file && file_exists( LEARNDASH_COURSE_GRID_PLUGIN_PATH . $file ) ) {
			include_once LEARNDASH_COURSE_GRID_PLUGIN_PATH . $file;
		}
	}

	public function load_translations()
	{
		$locale = apply_filters( 'plugin_locale', get_locale(), 'learndash-course-grid' );
		load_textdomain( 'learndash-course-grid', WP_LANG_DIR . '/plugins/learndash-course-grid-' . $locale . '.mo' );
		load_plugin_textdomain( 'learndash-course-grid', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );                

		include LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'includes/class-translations.php';
	}
}

function course_grid()
{
	return Course_Grid::instance();
}

course_grid();