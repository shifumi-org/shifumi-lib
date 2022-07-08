<?php

/**
 * Copy our Multisite support file(s) to the /wp-content/mu-plugins directory.
 */
if ( is_multisite() ) {
	$wpmu_plugin_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
	if ( is_writable( $wpmu_plugin_dir ) ) {
		$dest_file   = trailingslashit( $wpmu_plugin_dir ) . 'learndash-multisite.php';
		if ( ! file_exists( $dest_file ) ) {
			$source_file = trailingslashit( LEARNDASH_LMS_PLUGIN_DIR ) . 'mu-plugins/learndash-multisite.php';
			if ( file_exists( $source_file ) ) {
				copy( $source_file, $dest_file );
			}
		}
	}
}

/**
 * Install the License Manager.
 */
if ( file_exists( trailingslashit( LEARNDASH_LMS_PLUGIN_DIR ) . 'mu-plugins/learndash-hub.zip' ) ) {
	$hub_unzip_dir = trailingslashit( LEARNDASH_LMS_PLUGIN_DIR ) . 'mu-plugins/_tmp';
	if ( file_exists( $hub_unzip_dir ) ) {
		learndash_recursive_rmdir( $hub_unzip_dir );
	}
	if ( ! file_exists( $hub_unzip_dir ) ) {
		WP_Filesystem();
		$unzip_ret = unzip_file( trailingslashit( LEARNDASH_LMS_PLUGIN_DIR ) . 'mu-plugins/learndash-hub.zip', $hub_unzip_dir );
		if ( true === $unzip_ret ) {
			$install_file = trailingslashit( $hub_unzip_dir ) . 'learndash-hub/install.php';
			if ( file_exists( $install_file ) ) {
				include $install_file;
			}
		}

		learndash_recursive_rmdir( $hub_unzip_dir );
	}
}
