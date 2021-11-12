<?php
/**
 * Database
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 *
 * @wp_option  UncannyOwl TinCanny DB Version
 */

namespace UCTINCAN;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Database {

	// Constants
	const TABLE_REPORTING = 'uotincan_reporting';
	const TABLE_QUIZ = 'uotincan_quiz';
	const TABLE_RESUME = 'uotincan_resume';

	// Static Values
	private static $table_exists = false; // Yes / No
	protected static $user_id;

	// Upgraded
	public static $upgraded = false;

	/**
	 * Check Table Exists
	 *
	 * @access protected
	 * @return boolean
	 * @since  1.0.0
	 */
	protected function is_table_exists() {
		if ( ! self::$user_id ) {
			self::$user_id = get_current_user_id();
		}

		return true;
	}

	public function upgrade() {
		$table_exists  = $this->query_table_exists();
		$query_results = array();

		// Create Not Existed Tables
		foreach ( $table_exists as $key => $result ) {
			if ( false == $result ) {
				if ( false == call_user_func( array( $this, 'create_table_' . $key ) ) ) {
					$query_results[ $key ] = true;
				}
			}
		}

		if ( empty( $query_results ) ) {
			update_option( Init::TABLE_VERSION_KEY, UNCANNY_REPORTING_VERSION );
			self::$table_exists == true;
		}
	}

	/**
	 * Check Table Exists ( Query Part )
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	private function query_table_exists() {
		global $wpdb;

		$query_reporting = sprintf( "show tables like '%s%s';", $wpdb->prefix, self::TABLE_REPORTING );
		$query_resume    = sprintf( "show tables like '%s%s';", $wpdb->prefix, self::TABLE_RESUME );
		$query_quiz      = sprintf( "show tables like '%s%s';", $wpdb->prefix, self::TABLE_QUIZ );

		return array(
			self::TABLE_REPORTING => $wpdb->query( $query_reporting ),
			self::TABLE_RESUME    => $wpdb->query( $query_resume ),
			self::TABLE_QUIZ      => $wpdb->query( $query_quiz ),
		);
	}

	/**
	 * Create Table : TABLE_REPORTING
	 *
	 * @access private
	 * @return bool
	 * @since  1.0.0
	 */
	private function create_table_uotincan_reporting() {
		global $wpdb;

		$query = sprintf( "CREATE TABLE %s%s (
					`id`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					`group_id`    BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`user_id`     BIGINT(20) UNSIGNED NOT NULL,
					`course_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`lesson_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`module`      VARCHAR(255),
					`module_name` VARCHAR(255),
					`target`      VARCHAR(255),
					`target_name` VARCHAR(255),
					`verb`        VARCHAR(50),
					`result`      INT(7),
					`minimum`     INT(7),
					`completion`  BOOL,
					`xstored`      DATETIME,

					PRIMARY KEY (`id`),
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanUser`   FOREIGN KEY(`user_id`)   REFERENCES {$wpdb->users}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanGroup`  FOREIGN KEY(`group_id`)  REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanCourse` FOREIGN KEY(`course_id`) REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanLesson` FOREIGN KEY(`lesson_id`) REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB; ", $wpdb->prefix, self::TABLE_REPORTING );
		$wpdb->query( $query );

		// For Lower Version
		if ( $wpdb->last_error ) {
			$query = sprintf( "CREATE TABLE %s%s (
						`id`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						`group_id`    BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`user_id`     BIGINT(20) UNSIGNED NOT NULL,
						`course_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`lesson_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`module`      VARCHAR(255),
						`module_name` VARCHAR(255),
						`target`      VARCHAR(255),
						`target_name` VARCHAR(255),
						`verb`        VARCHAR(50),
						`result`      INT(7),
						`minimum`     INT(7),
						`completion`  BOOL,
						`xstored`      DATETIME,

						PRIMARY KEY (`id`)
					) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;", $wpdb->prefix, self::TABLE_REPORTING );

			$wpdb->query( $query );
		}

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Create Table : TABLE_RESUME
	 *
	 * @access private
	 * @return bool
	 * @since  1.0.0
	 */
	private function create_table_uotincan_resume() {
		global $wpdb;

		$query = sprintf( "
			CREATE TABLE %s%s (
			    `id`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id`     BIGINT(20) UNSIGNED NOT NULL,
				`course_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
				`lesson_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
				`module_id`   BIGINT(20) NOT NULL,
				`state`       VARCHAR(50),
				`value`       TEXT,

			    PRIMARY KEY (`id`)
			", $wpdb->prefix, self::TABLE_RESUME );

		$foreign_keys = sprintf( "
			CONSTRAINT `{$wpdb->prefix}_fk_Resume_User`   FOREIGN KEY(`user_id`)   REFERENCES %s(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
			CONSTRAINT `{$wpdb->prefix}_fk_Resume_Module` FOREIGN KEY(`module_id`) REFERENCES %s(`ID`) ON UPDATE CASCADE ON DELETE CASCADE
		", $wpdb->users, $wpdb->prefix . SnC_TABLE_NAME );

		// With Foreagn Key
		$wpdb->query( $query . ',' . $foreign_keys . ') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;' );

		// For Lower Version: Without Foreagn Key
		if ( $wpdb->last_error ) {
			$wpdb->query( $query . ') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;' );
		}

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Set Report
	 *
	 * @access public
	 *
	 * @param string $group_id
	 * @param int $user_id
	 * @param int $course_id
	 * @param int $lesson_id
	 * @param string $module
	 * @param string $module_name
	 * @param string $target
	 * @param string $target_name
	 * @param string $verb
	 * @param int $result
	 * @param int $maximum
	 * @param string $completion
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @todo data type of $completion
	 */
	public function set_report( $group_id, $course_id, $lesson_id, $module, $module_name, $target, $target_name, $verb, $result, $maximum, $completion, $user_id = 0 ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}
		// Options - Restrict for group leader
		$show_tincan = get_option( 'show_tincan_reporting_tables', 'yes' );
		if ( 'no' === $show_tincan ) {
			return false;
		}
		global $wpdb;

		if ( ! $group_id ) {
			$group_id = 'NULL';
		} else {
			$group_id = absint( $group_id );
		}

		if ( ! $course_id ) {
			$course_id = 'NULL';
		} else {
			$course_id = absint( $course_id );
			// Even if course id is integer but need a valid post id.
			$course    = get_post( $course_id );
			if( $course === NULL ) {
				$course_id = 'NULL';
			}
		}

		if ( ! $lesson_id ) {
			$lesson_id = 'NULL';
		} else {
			$lesson_id = absint( $lesson_id );
			// Even if lesson id is integer but need a valid post id.
			$lesson    = get_post( $lesson_id );
			if( $lesson === NULL ) {
				$lesson_id = 'NULL';
			}
		}

		if ( $result === false ) {
			$result = 'NULL';
		} else {
			$result = (int) $result;
		}

		if ( $maximum === false ) {
			$maximum = 'NULL';
		} else {
			$maximum = (int) $maximum;
		}

		if ( $completion === false ) {
			$completion = 'NULL';
		} else {
			$completion = (string) $completion;
		}


		$table = self::TABLE_REPORTING;
		$now   = current_time( 'mysql' );

		$query = $wpdb->query( $wpdb->prepare(
			"
		INSERT INTO $wpdb->prefix{$table}
				( `group_id`, `user_id`, `course_id`, `lesson_id`, `module`, `module_name`, `target`, `target_name`, `verb`, `result`, `minimum`, `completion`, `xstored`)
				VALUES ( {$group_id}, %d, {$course_id}, {$lesson_id}, %s, %s, %s, %s, %s, {$result}, {$maximum}, '{$completion}', '{$now}' )
			",
			( $user_id ) ? $user_id : self::$user_id,
			$module,
			$module_name,
			$target,
			$target_name,
			$verb
		) );


		if ( $wpdb->last_error ) {
			if ( ! get_option( $wpdb->prefix . self::TABLE_REPORTING . '_constraints' ) ) {
				self::update_constraints( self::TABLE_REPORTING );
				$query = $wpdb->query( $wpdb->prepare(
					" INSERT INTO $wpdb->prefix{$table}
					( `group_id`, `user_id`, `course_id`, `lesson_id`, `module`, `module_name`, `target`, `target_name`, `verb`, `result`, `minimum`, `completion`, `xstored`)
					VALUES ( {$group_id}, %d, {$course_id}, {$lesson_id}, %s, %s, %s, %s, %s, {$result}, {$maximum}, '{$completion}', '{$now}' )
					",
					( $user_id ) ? $user_id : self::$user_id,
					$module,
					$module_name,
					$target,
					$target_name,
					$verb
				) );

				update_option( $wpdb->prefix . self::TABLE_REPORTING . '_constraints', TRUE );
				if ( $wpdb->last_error ) {
					return false;
				}
			}else{
				return false;
			}
		}

		$user_id = ( $user_id ) ? $user_id : self::$user_id;

		$module_match = $this->get_slide_id_from_module( $module );
		if( isset( $module_match[1] ) ) {
			$module_id = $module_match[1];
			do_action( 'tincanny_module_completed', $module_id, $user_id, $verb );
		}
		return true;
	}

	function get_slide_id_from_module( $url ) {
		preg_match( "/\/uncanny-snc\/([0-9]+)\//", $url, $matches );

		return $matches;
	}

	private function update_table_uotincan_reporting() {
		global $wpdb;

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'user_response'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `user_response` TEXT NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'correct_response'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `correct_response` TEXT NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'available_responses'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `available_responses` TEXT NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'max_score'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `max_score` DECIMAL(4,2) NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'min_score'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `min_score` DECIMAL(4,2) NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'raw_score'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `raw_score` DECIMAL(4,2) NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'scaled_score'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `scaled_score` DECIMAL(4,2) NOT NULL ;" );
		}

		$result = $wpdb->get_row( "SHOW COLUMNS FROM `" . $wpdb->prefix . self::TABLE_REPORTING . "` LIKE 'duration'" );
		$exist  = $result ? true : false;
		if ( ! $exist ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . self::TABLE_REPORTING . "` ADD `duration` INT(8) NOT NULL ;" );
		}
	}

	/**
	 * Create Table : TABLE_REPORTING
	 *
	 * @access private
	 * @return bool
	 * @since  1.0.0
	 */
	private function create_table_uotincan_quiz() {
		global $wpdb;

		$query = sprintf( "CREATE TABLE %s%s (
					`id`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					`group_id`    BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`user_id`     BIGINT(20) UNSIGNED NOT NULL,
					`course_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`lesson_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
					`module`      VARCHAR(255),
					`module_name` VARCHAR(255),
					`activity_id` VARCHAR(255),
					`activity_name` VARCHAR(255),
					`result`      INT(7),
					`max_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
					`min_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
					`raw_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
					`scaled_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
					`correct_response` VARCHAR(255),
					`available_responses` VARCHAR(255),
					`user_response` TEXT,
					`xstored`      DATETIME,
					`duration` INT(8) NOT NULL,

					PRIMARY KEY (`id`),
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizUser`   FOREIGN KEY(`user_id`)   REFERENCES {$wpdb->users}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizGroup`  FOREIGN KEY(`group_id`)  REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizCourse` FOREIGN KEY(`course_id`) REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizLesson` FOREIGN KEY(`lesson_id`) REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB; ", $wpdb->prefix, self::TABLE_QUIZ );
		$wpdb->query( $query );

		// For Lower Version
		if ( $wpdb->last_error ) {
			$query = sprintf( "CREATE TABLE %s%s (
						`id`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						`group_id`    BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`user_id`     BIGINT(20) UNSIGNED NOT NULL,
						`course_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`lesson_id`   BIGINT(20) UNSIGNED NULL DEFAULT NULL,
						`module`      VARCHAR(255),
						`module_name` VARCHAR(255),
						`activity_id` VARCHAR(255),
						`activity_name` VARCHAR(255),
						`result`      INT(7),
						`max_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
						`min_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
						`raw_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
						`scaled_score` DECIMAL(4,2) UNSIGNED NULL DEFAULT 0,
						`correct_response` VARCHAR(255),
						`available_responses` VARCHAR(255),
						`user_response` TEXT,
						`xstored`      DATETIME,
						`duration` INT(8) NOT NULL,

						PRIMARY KEY (`id`)
					) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ENGINE=INNODB;", $wpdb->prefix, self::TABLE_QUIZ );

			$wpdb->query( $query );
		}

		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Set Report
	 *
	 * @access public
	 *
	 * @param string $group_id
	 * @param int $user_id
	 * @param int $course_id
	 * @param int $lesson_id
	 * @param string $module
	 * @param string $module_name
	 * @param string $activity_id
	 * @param string $activity_name
	 * @param int $result
	 * @param int $available_responses
	 * @param string $correct_response
	 * @param string $user_response
	 * @param string $max_score
	 * @param string $min_score
	 * @param string $raw_score
	 * @param string $scaled_score
	 * @param string $duration
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @todo data type of $completion
	 */

	public function set_quiz_data( $group_id, $course_id, $lesson_id, $module, $module_name, $activity, $activity_name, $result, $user_id = 0, $available_responses, $correct_response, $user_response, $max_score, $min_score, $raw_score, $scaled_score, $duration ) {
		if ( ! $this->is_table_exists() ) {
			return false;
		}
		// Options - Restrict for group leader
		$show_tincan = get_option( 'show_tincan_quiz_tables', 'yes' );
		if ( 'no' === $show_tincan ) {
			//return false;
		}
		global $wpdb;

		if ( ! $group_id ) {
			$group_id = 'NULL';
		} else {
			$group_id = absint( $group_id );
		}

		if ( ! $course_id ) {
			$course_id = 'NULL';
		} else {
			$course_id = absint( $course_id );
			// Even if course id is integer but need a valid post id.
			$course    = get_post( $course_id );
			if( $course === NULL ) {
				$course_id = 'NULL';
			}
		}

		if ( ! $lesson_id ) {
			$lesson_id = 'NULL';
		} else {
			$lesson_id = absint( $lesson_id );
			// Even if lesson id is integer but need a valid post id.
			$lesson    = get_post( $lesson_id );
			if( $lesson === NULL ) {
				$lesson_id = 'NULL';
			}
		}

		if ( $result === false ) {
			$result = 'NULL';
		} else {
			$result = (int) $result;
		}

		if ( $min_score === false ) {
			$min_score = 'NULL';
		}

		if ( $max_score === false ) {
			$max_score = 0;
		}

		if ( $scaled_score === false ) {
			$scaled_score = 'NULL';
		}

		if ( $raw_score === false ) {
			$raw_score = 'NULL';
		}

		$table = self::TABLE_QUIZ;
		$now   = current_time( 'mysql' );

		$query = $wpdb->query( $wpdb->prepare(
			"
		INSERT INTO $wpdb->prefix{$table}
				( `group_id`, `user_id`, `course_id`, `lesson_id`, `module`, `module_name`, `activity_id`, `activity_name`, `result`, `xstored`, `user_response`, `correct_response`, `available_responses`, `max_score`, `min_score`, `raw_score`, `scaled_score`, `duration`)
				VALUES ( {$group_id}, %d, {$course_id}, {$lesson_id}, %s, %s, %s, %s, {$result}, '{$now}', %s, %s, %s, %s, %s, %s, %s, %s )
			",
			( $user_id ) ? $user_id : self::$user_id,
			$module,
			$module_name,
			$activity,
			$activity_name,
			$user_response,
			$correct_response,
			$available_responses,
			$max_score,
			$min_score,
			$raw_score,
			$scaled_score,
			$duration
		) );

		if ( $wpdb->last_error ) {
			if ( ! get_option( $wpdb->prefix.$wpdb->prefix . self::TABLE_QUIZ . '_constraints' ) ) {
				self::update_constraints( self::TABLE_QUIZ );
				$query = $wpdb->query( $wpdb->prepare(
					" INSERT INTO $wpdb->prefix{$table}
						( `group_id`, `user_id`, `course_id`, `lesson_id`, `module`, `module_name`, `activity_id`, `activity_name`, `result`, `xstored`, `user_response`, `correct_response`, `available_responses`, `max_score`, `min_score`, `raw_score`, `scaled_score`, `duration`)
						VALUES ( {$group_id}, %d, {$course_id}, {$lesson_id}, %s, %s, %s, %s, {$result}, '{$now}', %s, %s, %s, %s, %s, %s, %s, %s )
					",
					( $user_id ) ? $user_id : self::$user_id,
					$module,
					$module_name,
					$activity,
					$activity_name,
					$user_response,
					$correct_response,
					$available_responses,
					$max_score,
					$min_score,
					$raw_score,
					$scaled_score,
					$duration
				) );

				update_option( $wpdb->prefix . self::TABLE_QUIZ . '_constraints', TRUE );
				if ( $wpdb->last_error ) {
					return FALSE;
				}
			} else {
				return FALSE;
			}

		}

		return true;
	}

	/**
	 * @param $id
	 * @param $module_urls
	 */
	static public function delete_bookmarks( $id, $module_url ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . self::TABLE_RESUME, array( 'module_id' => $id ), array( '%d' ) );
	}

	/**
	 * @param $id
	 * @param $module_url
	 */
	static public function delete_all_data( $id, $module_url ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . self::TABLE_REPORTING, array( 'module' => $module_url ), array( '%s' ) );
		$wpdb->delete( $wpdb->prefix . self::TABLE_RESUME, array( 'module_id' => $id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . self::TABLE_QUIZ, array( 'module' => $module_url ), array( '%s' ) );
	}
	//CONSTRAINT
	public static function update_constraints($table_name){
		global $wpdb;
		$results = $wpdb->get_results( "SELECT TABLE_NAME, ENGINE FROM information_schema. TABLES WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND TABLE_NAME = '" . $wpdb->users . "' " );
		if ( ! empty( $results ) && isset( $results[0]->ENGINE ) && strtolower( $results[0]->ENGINE ) !== strtolower( 'InnoDB' ) ) {
			$sql = "ALTER TABLE " . $wpdb->users . " ENGINE=INNODB";
			$wpdb->query( $sql );
		}

		if ( $table_name === self::TABLE_REPORTING ) {
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `fk_TinCanUser`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanUser`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `fk_TinCanUser`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `{$wpdb->prefix}_fk_TinCanUser`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanUser`   FOREIGN KEY(`user_id`)   REFERENCES {$wpdb->users}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `fk_TinCanGroup`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanGroup`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `fk_TinCanGroup`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `{$wpdb->prefix}_fk_TinCanGroup`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanGroup`   FOREIGN KEY(`group_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `fk_TinCanCourse`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanCourse`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `fk_TinCanCourse`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `{$wpdb->prefix}_fk_TinCanCourse`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanCourse`   FOREIGN KEY(`course_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `fk_TinCanLesson`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanLesson`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `fk_TinCanLesson`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " DROP INDEX `{$wpdb->prefix}_fk_TinCanLesson`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_REPORTING . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanLesson`   FOREIGN KEY(`lesson_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

		} elseif ( $table_name === self::TABLE_RESUME ) {

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP FOREIGN KEY `fk_Resume_User`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_Resume_User`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP INDEX `fk_Resume_User`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP INDEX `{$wpdb->prefix}_fk_Resume_User`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " ADD CONSTRAINT `{$wpdb->prefix}_fk_Resume_User`   FOREIGN KEY(`user_id`)   REFERENCES {$wpdb->users}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP FOREIGN KEY `fk_Resume_Module`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_Resume_Module`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP INDEX `fk_Resume_Module`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " DROP INDEX `{$wpdb->prefix}_fk_Resume_Module`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_RESUME . " ADD CONSTRAINT `{$wpdb->prefix}_fk_Resume_Module`   FOREIGN KEY(`module_id`)   REFERENCES " . $wpdb->prefix . SnC_TABLE_NAME . "(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

		} elseif ( $table_name === self::TABLE_QUIZ ) {

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `fk_TinCanQuizUser`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanQuizUser`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `fk_TinCanQuizUser`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `{$wpdb->prefix}_fk_TinCanQuizUser`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizUser`   FOREIGN KEY(`user_id`)   REFERENCES {$wpdb->users}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `fk_TinCanQuizGroup`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanQuizGroup`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `fk_TinCanQuizGroup`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `{$wpdb->prefix}_fk_TinCanQuizGroup`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizGroup`   FOREIGN KEY(`group_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `fk_TinCanQuizCourse`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanQuizCourse`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `fk_TinCanQuizCourse`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `{$wpdb->prefix}_fk_TinCanQuizCourse`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizCourse`   FOREIGN KEY(`course_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );

			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `fk_TinCanQuizLesson`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP FOREIGN KEY `{$wpdb->prefix}_fk_TinCanQuizLesson`;" );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `fk_TinCanQuizLesson`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " DROP INDEX `{$wpdb->prefix}_fk_TinCanQuizLesson`; " );
			$wpdb->query( " ALTER TABLE " . $wpdb->prefix . self::TABLE_QUIZ . " ADD CONSTRAINT `{$wpdb->prefix}_fk_TinCanQuizLesson`   FOREIGN KEY(`lesson_id`)   REFERENCES {$wpdb->posts}(`ID`) ON UPDATE CASCADE ON DELETE CASCADE" );
		}
	}

	/**
	 * Checks if constraints.
	 */
	public static function check_constraints() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE ( TABLE_NAME = '" . $wpdb->prefix . self::TABLE_REPORTING . "' OR TABLE_NAME = '" . $wpdb->prefix . self::TABLE_QUIZ . "' OR TABLE_NAME = '" . $wpdb->prefix . self::TABLE_RESUME . "' ) AND REFERENCED_TABLE_SCHEMA like '{$wpdb->dbname}' ", 'ARRAY_A' );

		if ( empty( $results ) ) {
			return false;
		}

		$constraints           = [ 'fk_TinCanUser', 'fk_TinCanGroup', 'fk_TinCanCourse', 'fk_TinCanLesson', 'fk_Resume_User', 'fk_Resume_Module', 'fk_TinCanQuizUser', 'fk_TinCanQuizGroup', 'fk_TinCanQuizCourse', 'fk_TinCanQuizLesson' ];
		$available_constraints = [];
		foreach ( $results as $result ) {
			$available_constraints[] = $result['CONSTRAINT_NAME'];
		}
		if ( empty( $available_constraints ) ) {
			return false;
		}
		foreach ( $constraints as $constraint ) {
			if ( ! in_array( $constraint, $available_constraints ) ) {
				return false;
			}
		}

		return true;
	}
}
