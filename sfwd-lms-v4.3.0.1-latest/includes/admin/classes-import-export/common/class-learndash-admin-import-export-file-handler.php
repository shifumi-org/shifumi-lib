<?php
/**
 * LearnDash Admin Import/Export Base File Handler.
 *
 * @since 4.3.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Learndash_Admin_Import_Export_File_Handler' ) ) {
	/**
	 * Class LearnDash Admin Import/Export Base File Handler.
	 *
	 * @since 4.3.0
	 */
	abstract class Learndash_Admin_Import_Export_File_Handler {
		const MEDIA_NAME = 'media';

		const FILE_EXTENSION = '.ld';

		/**
		 * Folder path to store the files to be processed.
		 *
		 * @since 4.3.0
		 *
		 * @var string
		 */
		protected $work_dir;

		/**
		 * Array of files to process.
		 *
		 * @since 4.3.0
		 *
		 * @var array
		 */
		protected $files = array();

		/**
		 * Instructions to protect the path against direct access.
		 *
		 * @since 4.3.0.1
		 *
		 * @var string
		 */
		private $protect_instructions = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			// change the wp error handler to process errors properly.
			add_filter( 'wp_die_handler', array( $this, 'handle_import_export_error' ) );

			$base_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->get_logger_directory();

			Learndash_Admin_File_Download_Handler::register_file_path(
				$this->get_logger_path_id(),
				$base_path
			);

			$this->protect_instructions = Learndash_Admin_File_Download_Handler::try_to_protect_file_path(
				$base_path
			);
		}

		/**
		 * Returns the logger directory.
		 *
		 * @since 4.3.0
		 *
		 * @return string The logger directory.
		 */
		abstract protected function get_logger_directory(): string;

		/**
		 * Returns the logger path ID.
		 *
		 * @since 4.3.0.1
		 *
		 * @return string The logger path ID.
		 */
		abstract protected function get_logger_path_id(): string;

		/**
		 * Returns the logger file path.
		 *
		 * @since 4.3.0
		 *
		 * @return string
		 */
		public function get_logger_path(): string {
			$upload_dir = wp_upload_dir();

			$path  = $upload_dir['basedir'] . DIRECTORY_SEPARATOR;
			$path .= $this->get_logger_directory() . DIRECTORY_SEPARATOR;

			return $path . 'log-' . uniqid() . Learndash_Admin_Import_Export_Logger::FILE_EXTENSION;
		}

		/**
		 * Returns the log file URL.
		 *
		 * @since 4.3.0
		 *
		 * @return string
		 */
		public function get_log_file_url(): string {
			$upload_dir = wp_upload_dir();

			$log_directory    = DIRECTORY_SEPARATOR . $this->get_logger_directory() . DIRECTORY_SEPARATOR;
			$log_file_pattern = '*' . Learndash_Admin_Import_Export_Logger::FILE_EXTENSION;

			$log_files = glob(
				$upload_dir['basedir'] . $log_directory . $log_file_pattern
			);

			if ( ! is_array( $log_files ) || empty( $log_files ) ) {
				return '';
			}

			return Learndash_Admin_File_Download_Handler::get_download_url(
				$this->get_logger_path_id(),
				basename( $log_files[0] )
			);
		}

		/**
		 * Returns the protect instructions.
		 *
		 * @since 4.3.0.1
		 *
		 * @return string
		 */
		public function get_protect_instructions(): string {
			return $this->protect_instructions;
		}

		/**
		 * Processes errors for import/export.
		 *
		 * @since 4.3.0
		 *
		 * @return string Error handler.
		 */
		public function handle_import_export_error(): string {
			if ( ! empty( $this->work_dir ) && is_dir( $this->work_dir ) ) {
				$this->remove_directory_recursively( $this->work_dir );
			}

			return '_default_wp_die_handler';
		}

		/**
		 * Returns the file path.
		 *
		 * @since 4.3.0
		 *
		 * @param string $filename File name.
		 *
		 * @return string
		 */
		public function get_file_path_by_name( string $filename ): string {
			$file_name_with_extension = $filename . self::FILE_EXTENSION;

			return $this->files[ $file_name_with_extension ] ?? '';
		}

		/**
		 * Returns the media directory path.
		 *
		 * @since 4.3.0
		 *
		 * @param string $filename File name.
		 *
		 * @return string
		 */
		public function get_media_file_path_by_name( string $filename ): string {
			return $this->work_dir . DIRECTORY_SEPARATOR . self::MEDIA_NAME . DIRECTORY_SEPARATOR . $filename;
		}

		/**
		 * Deletes a directory recursively.
		 *
		 * @since 4.3.0
		 *
		 * @param string $path Directory path.
		 *
		 * @return void
		 */
		public function remove_directory_recursively( string $path ): void {
			if ( empty( $path ) || ! is_dir( $path ) ) {
				return;
			}

			$files = array_diff( scandir( $path ), array( '.', '..' ) );

			foreach ( $files as $file ) {
				if ( is_dir( $path . DIRECTORY_SEPARATOR . $file ) ) {
					$this->remove_directory_recursively( $path . DIRECTORY_SEPARATOR . $file );
				} else {
					unlink( $path . DIRECTORY_SEPARATOR . $file );
				}
			}

			rmdir( $path );
		}

		/**
		 * Creates a directory to store the files.
		 *
		 * @since 4.3.0
		 *
		 * @param string $directory Directory.
		 *
		 * @throws Exception If the directory cannot be created.
		 */
		protected function create_work_directory( string $directory ): void {
			$upload_dir = wp_upload_dir();

			if ( ! empty( $upload_dir['error'] ) ) {
				throw new Exception(
					sprintf(
						// translators: %s: upload path error.
						__( 'Unable to create the files: %s', 'learndash' ),
						$upload_dir['error']
					)
				);
			}
			$base_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $directory;

			$this->work_dir = $base_path . DIRECTORY_SEPARATOR . uniqid();

			$path_status = wp_mkdir_p( $this->work_dir );

			if ( ! $path_status ) {
				throw new Exception( __( 'Unable to create the files.', 'learndash' ) );
			}

			$this->add_index_file( $base_path );
		}

		/**
		 * Add the index.php file to prevent directory listing.
		 *
		 * @param string $path Directory path.
		 *
		 * @since 4.3.0
		 *
		 * @throws Exception If the index file cannot be created.
		 */
		protected function add_index_file( string $path ) {
			$index_file = $path . DIRECTORY_SEPARATOR . 'index.php';

			$index_file_status = file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				$index_file,
				'<?php' . PHP_EOL . '// Silence is golden.'
			);

			if ( ! $index_file_status ) {
				throw new Exception( __( 'Unable to create the index file.', 'learndash' ) );
			}
		}

		/**
		 * Creates a directory to store the media files.
		 *
		 * @since 4.3.0
		 *
		 * @throws Exception If the directory cannot be created.
		 */
		protected function create_media_directory(): void {
			$media_dir_path = $this->work_dir . DIRECTORY_SEPARATOR . self::MEDIA_NAME;

			$path_status = wp_mkdir_p( $media_dir_path );

			if ( ! $path_status ) {
				throw new Exception( __( 'Unable to create the media directory.', 'learndash' ) );
			}
		}
	}
}
