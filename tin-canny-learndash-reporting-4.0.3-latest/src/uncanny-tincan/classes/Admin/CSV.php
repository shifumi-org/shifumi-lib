<?php
/**
 * CSV
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN\Admin;

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class CSV {
	const FILE_NAME = 'TinCan_data';
	private $data, $output;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $data
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $data ) {
		$this->data = $data;

		if ( !empty( $this->data ) ){
			if ( !headers_sent() ) $this->header();

			$this->output = fopen('php://output', 'w');

			$this->print_table_header();
			$this->print_csv();

			fclose( $this->output );
			die;
		}
	}

	/**
	 * Send Header
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	private function header() {
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( sprintf( 'Content-Disposition: attachment; filename=%s.csv', self::FILE_NAME ) );
	}

	/**
	 * Print Table Header
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	private function print_table_header() {
		$data = array_shift( $this->data );

		$header = array();
		foreach( $data as $key => $value ) {
			$header[] = $key;
		}
		fputcsv( $this->output, $header );
		array_unshift( $this->data, $data );
	}

	/**
	 * Print CSV Body
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	private function print_csv() {
		foreach( $this->data as $value ) {
			fputcsv( $this->output , (array) $value );
		}
	}
}
