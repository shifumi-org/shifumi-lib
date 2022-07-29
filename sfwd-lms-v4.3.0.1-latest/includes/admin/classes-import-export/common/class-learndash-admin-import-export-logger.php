<?php
/**
 * LearnDash Admin Import/Export Logger.
 *
 * @since 4.3.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Learndash_Admin_Import_Export_Logger' ) ) {
	/**
	 * Class LearnDash Admin Import/Export Logger.
	 *
	 * @since 4.3.0
	 */
	class Learndash_Admin_Import_Export_Logger {
		const FILE_EXTENSION = '.log';

		/**
		 * Log file stream.
		 *
		 * @since 4.3.0
		 *
		 * @var resource|null
		 */
		private $log_stream = null;

		/**
		 * Initializes the logger.
		 *
		 * @since 4.3.0
		 *
		 * @param string $log_path        Log file path.
		 * @param string $initial_message Initial message.
		 *
		 * @return void
		 */
		public function init( string $log_path, string $initial_message = '' ): void {
			// Delete *.log files.
			$log_files = glob(
				dirname( $log_path ) . DIRECTORY_SEPARATOR . '*' . self::FILE_EXTENSION
			);
			if ( is_array( $log_files ) && ! empty( $log_files ) ) {
				array_map( 'unlink', $log_files );
			}

			try {
				$this->log_stream = fopen( $log_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
			} catch ( Exception $e ) {
				return;
			}

			if ( ! is_resource( $this->log_stream ) ) {
				return;
			}

			$this->log( $initial_message );
		}

		/**
		 * Writes to the log file and closes the log.
		 *
		 * @since 4.3.0
		 *
		 * @param string $final_message Final message.
		 *
		 * @return void
		 */
		public function finalize( string $final_message = '' ): void {
			$this->log( $final_message );

			if ( is_resource( $this->log_stream ) ) {
				fclose( $this->log_stream ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			}
		}

		/**
		 * Maps options and writes to the log file.
		 *
		 * @since 4.3.0
		 *
		 * @param array $options Options.
		 *
		 * @return void
		 */
		public function log_options( array $options ): void {
			$lines = array(
				'Options:',
			);

			foreach ( $options as $key => $values ) {
				$lines[] = "$key: " . wp_json_encode( $values );
			}

			$this->log(
				implode( PHP_EOL, $lines )
			);
		}

		/**
		 * Maps class name with object vars and writes to the log file.
		 *
		 * @since 4.3.0
		 *
		 * @param string $classname      Class name.
		 * @param array  $object_vars    Object properties.
		 * @param string $message_before Message before.
		 * @param string $message_after  Message after.
		 *
		 * @return void
		 */
		public function log_object(
			string $classname,
			array $object_vars,
			string $message_before = '',
			string $message_after = ''
		): void {
			$object_vars = array_filter(
				$object_vars,
				function ( $value, $key ) {
					return ! is_object( $value ) && ! in_array(
						$key,
						array( 'offset_rows', 'offset_media' ),
						true
					);
				},
				ARRAY_FILTER_USE_BOTH
			);

			$additional_info = '';
			if ( ! empty( $object_vars ) ) {
				$additional_info .= ' with properties' . PHP_EOL;
				$additional_info .= implode(
					' & ',
					array_map(
						function ( string $key, $value ) {
							return "$key: " . wp_json_encode( $value );
						},
						array_keys( $object_vars ),
						array_values( $object_vars )
					)
				);
			}

			if ( ! empty( $message_before ) ) {
				$message_before = $message_before . PHP_EOL;
			}
			if ( ! empty( $message_after ) ) {
				$message_after = PHP_EOL . $message_after;
			}

			$this->log( $message_before . $classname . $additional_info . $message_after );
		}

		/**
		 * Writes to the log file.
		 *
		 * @since 4.3.0
		 *
		 * @param string $message Message.
		 *
		 * @return void
		 */
		public function log( string $message ): void {
			if ( ! is_resource( $this->log_stream ) ) {
				return;
			}

			$formatted_message = gmdate( 'Y-m-d H:i:s: ' ) . $message . PHP_EOL . PHP_EOL;

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
			fwrite( $this->log_stream, $formatted_message );
		}
	}
}
