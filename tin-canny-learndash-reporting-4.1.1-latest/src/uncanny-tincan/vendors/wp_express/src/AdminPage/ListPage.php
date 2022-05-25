<?php
/**
 *
 * WP_Admin_Page Class
 *
 * @author	Sujin 수진 Choi
 * @package	wp-hacks
 * @version	4.5.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

namespace WE_TINCANNY\AdminPage;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class ListPage extends \WE_TINCANNY\AdminPage {
	private $columns = [];
	private $sortable_columns = [];
	public $data, $count;
	private $actions;
	private $default_per_page = 25;

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		add_filter( 'set-screen-option', array( $this, 'SetScreenOption' ), 10, 3);
	}

	public function __get( $name ) {
		if ( $value = parent::__get( $name ) ) return $value;
		return $this->get( $name );
	}

	public function __set( $name, $value ) {
		if ( parent::__set( $name, $value ) ) return;

		switch( $name ) {
			case 'column' :
				$this->columns[] = $value;
				break;

			case 'sortable' :
			case 'sortable_column' :
				$this->columns[] = $value;
				$this->sortable_columns[] = $value;
				break;

			case 'actions' :
				$this->actions = $value;
				break;

			case 'default_per_page' :
				$this->default_per_page = $value;
				break;
		}
	}

	public function GetPerPage() {
		$screen = get_current_screen();
		$user = get_current_user_id();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $screen_option, true );

		if ( empty ( $per_page) || $per_page < 1 )
			$per_page = $screen->get_option( 'per_page', 'default' );

		return $per_page;
	}

	public function printTemplate( $contents = '' ) {
		if( !class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}

		$table = new \WE_TINCANNY\Extensions\ListPage();
		$table->column = $this->columns;
		$table->sortable_columns = $this->sortable_columns;
		$table->data = $this->data;
		$table->count = $this->count;
		$table->per_page = $this->GetPerPage();

		$table->extra_tablenav = $this->actions;

		$table->prepare_items();

		ob_start();

		if ( $this->template ) {
			$template = array_shift( $this->template );

			if ( $template ) call_user_func( $template );
		}

		$table->views();
// 		$table->search_box( 'Search', '2' );
		$table->display();

		$contents = ob_get_clean();

		parent::printTemplate( $contents );
	}

	// Screen Option
	public function ScreenOptions() {
		$screen = get_current_screen();

		if( !is_object($screen) || $screen->id != $this->this_page )
			return;

		$key = str_replace( '-', '_', $this->key );
		$args = array(
			'label' => __('Items per Page', $key ),
			'default' => $this->default_per_page,
			'option' => $key . '_per_page'
		);

		add_screen_option( 'per_page', $args );
	}

	public function SetScreenOption( $status, $option, $value ) {
		$key = str_replace( '-', '_', $this->key );

		if ( $option == $key . '_per_page' )
			return $value;
	}
}




