<?php
/**
 * A trait helper for manipulating files.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace Certificate_Builder\Traits;

/**
 * Trait IO
 *
 * @package Certificate_Builder\Traits
 */
trait IO {

	/**
	 * A simple function to create & return the folder that we can use to write tmp files
	 *
	 * @return string
	 */
	protected function get_tmp_path() {
		$upload_dir = wp_upload_dir()['basedir'];
		$tmp_dir    = $upload_dir . DIRECTORY_SEPARATOR . 'learndash-certificate-builder';
		if ( ! is_dir( $tmp_dir ) ) {
			wp_mkdir_p( $tmp_dir );
		}

		if ( ! is_file( $tmp_dir . DIRECTORY_SEPARATOR . 'index.php' ) ) {
			file_put_contents( $tmp_dir . DIRECTORY_SEPARATOR . 'index.php', '' );
		}

		return $tmp_dir;
	}

	/**
	 * Create the folder that is contains user fonts
	 *
	 * @return string
	 */
	protected function get_user_font_path() {
		$path = $this->get_tmp_path() . DIRECTORY_SEPARATOR . 'user_fonts';
		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}

		return $path;
	}

	/**
	 * The mpdf working path
	 *
	 * @return string
	 */
	public function get_working_path() {
		$working_path = $this->get_tmp_path() . DIRECTORY_SEPARATOR . 'mpdf';
		if ( ! is_dir( $working_path ) ) {
			wp_mkdir_p( $working_path );
		}

		return $working_path;
	}

	/**
	 * Return the log path
	 *
	 * @param string $file_name the filename.
	 *
	 * @return string
	 */
	public function get_log_path( $file_name = '' ) {
		$file = empty( $file_name ) ? 'certificate-builder.log' : $file_name;

		return $this->get_tmp_path() . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Delete a folder with every content inside
	 *
	 * @param string $dir The path that should be deleted.
	 *
	 * @return bool
	 */
	public function delete_dir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$it    = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new \RecursiveIteratorIterator(
			$it,
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				$ret = rmdir( $file->getPathname() );
			} else {
				$ret = unlink( $file->getPathname() );
			}
			if ( false === $ret ) {
				return false;
			}
		}
		rmdir( $dir );

		return true;
	}
}
