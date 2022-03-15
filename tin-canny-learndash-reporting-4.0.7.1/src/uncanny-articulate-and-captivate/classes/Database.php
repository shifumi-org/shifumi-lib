<?php
/**
 * Database Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Database {
	// Constants
	const TBL_MODULES       = 'snc_file_info';
	const TBL_POST_RELATION = 'snc_post_relationship';

	private static $tbl_modules;
	private static $tbl_post_relation;

	static private function set_table_string() {
		if ( self::$tbl_modules && self::$tbl_post_relation )
			return;

		global $wpdb;

		self::$tbl_modules       = $wpdb->prefix . self::TBL_MODULES;
		self::$tbl_post_relation = $wpdb->prefix . self::TBL_POST_RELATION;
	}

	static private function get_tables_if_exists() {
		foreach ( self::query_table_exists() as $key => $result ) {
			if ( $result == false )
				return false;
		}

		return self::get_table_name();
	}

	static private function get_table_name() {
		if ( ! self::$tbl_modules || ! self::$tbl_post_relation )
			self::set_table_string();

		return array(
			self::TBL_MODULES       => self::$tbl_modules,
			self::TBL_POST_RELATION => self::$tbl_post_relation,
		);
	}

	/**
	 * Check Exists
	 *
	 * @since  1.3.7
	 * @access private
	 */
	static private function query_table_exists() {
		global $wpdb;
		$tables = get_table_name();

		$query_module        = $wpdb->query( sprintf( "show tables like '%s%s';", $tables[ self::TBL_MODULES ] ) );
		$query_post_relation = $wpdb->query( sprintf( "show tables like '%s%s';", $tables[ self::TBL_POST_RELATION ] ) );

		if ( ! $query_module || ! $query_post_relation ) {
			self::upgrade();

			$query_module        = $wpdb->query( sprintf( "show tables like '%s%s';", $tables[ self::TBL_MODULES ] ) );
			$query_post_relation = $wpdb->query( sprintf( "show tables like '%s%s';", $tables[ self::TBL_POST_RELATION ] ) );
		}

		return array(
			self::TBL_MODULES       => $query_module,
			self::TBL_POST_RELATION => $query_post_relation,
		);
	}




	public function upgrade() {
		$tables  = self::get_table_name();
		$results = true;

		// Create Not Existed Tables
		foreach( $tables as $key => $_ ) {
			$created  = call_user_func( array( __CLASS__, 'create_table_' . $key ) );
			$upgraded = call_user_func( array( __CLASS__, 'upgrade_table_' . $key ) );

			if ( ! $created || ! $upgraded )
				$results = false;
		}

		return $results;
	}

	/**
	 * Create Table
	 *
	 * @since 0.0.1
	 * @access public
	 */
	static private function create_table_snc_file_info() {
		global $wpdb;

		$sql = sprintf( "CREATE TABLE IF NOT EXISTS `%s` (
			`ID`        bigint(20)   NOT NULL AUTO_INCREMENT,
			`file_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`type`      varchar(15)  COLLATE utf8_unicode_ci NOT NULL,
			`url`       varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;", self::$tbl_modules );

		$wpdb->query( $sql );

		if ( $wpdb->last_error )
			return false;

		return true;
	}
	static private function create_table_snc_post_relationship() {
		global $wpdb;
		
		$results = $wpdb->get_results( "SELECT TABLE_NAME, ENGINE FROM information_schema. TABLES WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND TABLE_NAME = '" . $wpdb->posts . "' " );
		if ( $wpdb->last_error ) {
			return false;
		}
		
		if ( ! empty( $results ) && isset( $results[0]->ENGINE ) && strtolower( $results[0]->ENGINE ) !== strtolower( 'InnoDB' ) ) {
			$sql = "ALTER TABLE " . $wpdb->posts . " ENGINE=INNODB";
			$wpdb->query( $sql );
			if ( $wpdb->last_error ) {
				return false;
			}
		}

		$sql = sprintf( "CREATE TABLE IF NOT EXISTS `%s` (
			`ID`      bigint(20) NOT NULL AUTO_INCREMENT,
			`snc_id`  bigint(20) NOT NULL,
			`post_id` bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (`ID`),

			CONSTRAINT `{$wpdb->prefix}_fk_TinCanny_Module_Relation_Module` FOREIGN KEY(`snc_id`)  REFERENCES %s(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
			CONSTRAINT `{$wpdb->prefix}_fk_TinCanny_Module_Relation_Post`   FOREIGN KEY(`post_id`) REFERENCES %s(`ID`) ON UPDATE CASCADE ON DELETE CASCADE
		) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;", self::$tbl_post_relation, self::$tbl_modules, $wpdb->posts );

		$wpdb->query( $sql );

		if ( $wpdb->last_error )
			return false;

		return true;
	}

	/**
	 * Update Table 1.3.7
	 *
	 * @since  1.3.7
	 * @access private
	 */
	static private function upgrade_table_snc_file_info() {
		global $wpdb;
		$result = true;

		// Add subtype
		$has_subtype = sprintf( "SHOW COLUMNS FROM `%s` LIKE 'subtype';", self::$tbl_modules );

		if ( ! $wpdb->query( $has_subtype ) ) {
			$query = sprintf( "ALTER TABLE %s ADD `subtype` varchar(15) COLLATE utf8_unicode_ci", self::$tbl_modules );
			$wpdb->query( $query );

			if ( $wpdb->last_error )
				$result = false;
		}

		// Add version
		$has_subtype = sprintf( "SHOW COLUMNS FROM `%s` LIKE 'version';", self::$tbl_modules );

		if ( ! $wpdb->query( $has_subtype ) ) {
			$query = sprintf( "ALTER TABLE %s ADD `version` varchar(15) COLLATE utf8_unicode_ci", self::$tbl_modules );
			$wpdb->query( $query );

			if ( $wpdb->last_error )
				$result = false;
		}

		return $result;
	}
	static private function upgrade_table_snc_post_relationship() {
		return true;
	}




	static public function add_item( $name ) {
		self::set_table_string();
		global $wpdb;
		$wpdb->insert( self::$tbl_modules, array( 'file_name' => $name ) );
		return $wpdb->insert_id;
	}

	/**
	 * Add Detail to ID
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @changed 1.3.7 Add Subtype
	 */
	static public function add_detail( $item_id, $type, $url, $subtype, $version = UNCANNY_REPORTING_VERSION ) {
		self::set_table_string();

		global $wpdb;
		$wpdb->update(
			self::$tbl_modules,
			array(
				'type'    => $type,
				'subtype' => $subtype,
				'url'     => $url,
				'version' => $version,
			),
			array( 'ID' => $item_id )
		);
	}

	static public function delete( $id ) {
		self::set_table_string();
		global $wpdb;
		$wpdb->delete( self::$tbl_modules, array( 'ID' => $id ));
	}

	static public function get_modules( $where = '' ) {
		self::set_table_string();
		global $wpdb;
		return $wpdb->get_results( sprintf( "SELECT * FROM %s %s ORDER BY `ID` DESC", self::$tbl_modules, $where ), OBJECT );
	}

	static public function get_item( $item_id ) {
		if ( ! $item_id )
			return false;

		self::set_table_string();
		global $wpdb;
		$table_name = self::$tbl_modules;
		return $wpdb->get_row( $wpdb->prepare(  "SELECT * FROM {$table_name} WHERE ID = %s;", $item_id ), ARRAY_A );
	}

	// TODO
	static public function ChangeNameFromId( $id, $title ) {
		self::set_table_string();
		global $wpdb;
		$table_name = self::$tbl_modules;
		$title_from_db = $wpdb->get_var( $wpdb->prepare( "SELECT file_name FROM {$table_name} WHERE id = %s", $id ) );

		if ( $title_from_db != $title ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$table_name} SET file_name = '%s' WHERE id = %s", $title, $id ) );
		}

		return $title;
	}

	static public function get_contents( $search = '' , $limit = '', $order_by = '') {
		self::set_table_string();
		global $wpdb;
		$table_name = self::$tbl_modules;
		$where = '';

		if( !empty( $search ) ){
			$where = " WHERE file_name LIKE '%{$search}%' OR type LIKE '%{$search}%' ";
		}

		if( empty( $order_by ) ){
			$order_by = "ORDER BY `ID` DESC";
		}

		return $wpdb->get_results( sprintf( "SELECT ID,file_name as content, type, url FROM {$table_name} %s %s %s ", $where, $order_by, $limit ), OBJECT );
	}

	static public function get_contents_count( $search = '' ) {
		self::set_table_string();
		global $wpdb;
		$table_name = self::$tbl_modules;
		$where = '';

		if( !empty( $search ) ){
			$where = " WHERE file_name LIKE '%{$search}%' OR type LIKE '%{$search}%' ";
		}

		return $wpdb->get_var( sprintf( "SELECT COUNT(*) FROM {$table_name} %s ", $where) );
	}

	/**
	 * Update item title by ID
	 *
	 * @since 3.2
	 * @access public
	 *
	 */
	static public function update_item_title( $item_id, $title, $version = UNCANNY_REPORTING_VERSION ) {
		self::set_table_string();

		global $wpdb;
		$wpdb->update(
			self::$tbl_modules,
			array(
				'file_name'    => $title,
				'version' => $version,
			),
			array( 'ID' => $item_id )
		);
	}
}
