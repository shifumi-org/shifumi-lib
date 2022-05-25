<?php

/**
 * Add:   RewriteRule ^(?:|(?:\/|\\))([0-9]{1,})((?:.*(?:\/|\\))|.*\.(?:(?:html|htm)(?:|.*)))$ index.php?tincanny_content_id=$1&tincanny_file_path=$2 [QSA,L,NE]
 * below: RewriteRule ^index\.php$ - [L]
 */

class TinCannyProtection {

	private $content_data = [];

	/**
	 * TinCannyProtection constructor.
	 */
	public function __construct() {
		// Hook init
		add_action( 'init', [ $this, 'handle_protected_content' ], 1 );
	}

	/**
	 *
	 */
	public function handle_protected_content() {
		// Check if the Tin Canny parameter is defined
		// This will be defined only if the htaccess is setup properly
		if ( isset( $_GET['tincanny_content_id'] ) || isset( $_GET['tincanny_file_path'] ) ) {
			// Get content data
			$this->set_content_data( $_GET['tincanny_content_id'], $_GET['tincanny_file_path'] );

			// Do the validation to check if the user should have access to this content
			// and check if the protection settings is enabled. If it isn't then the following
			// statement will be true, as $this->is_protection_enabled() would be false and
			// ( ! false || ... ) => true
			if ( ! $this->is_protection_enabled() || $this->user_can_access_to_the_content() ) {
				// Show the content
				$this->show_protected_content();
			} else {
				// Check if the user wants to show a custom forbidden page
				$has_custom_forbidden_page = false;
				if ( $has_custom_forbidden_page ) {
					// Load custom forbidden page
					$this->get_custom_forbidden_page();
				} else {
					// Just return 403
					header( 'HTTP/1.0 403 Forbidden' );
				}
			}

			// Don't do anything else
			exit();
		}
	}

	/**
	 * @param $content_id
	 * @param $content_relative_path
	 */
	private function set_content_data( $content_id, $content_relative_path ) {
		// Define default values
		$this->content_data = (object) [];

		// Get all URl parameters
		$url_parameters = $_GET;
		unset( $url_parameters[ 'tincanny_content_id' ] );
		unset( $url_parameters[ 'tincanny_file_path' ] );

		// Get content data
		// We can add more data here, like the course id
		$this->content_data->id            = (int) $content_id;
		$this->content_data->relative_path = $content_relative_path;
		$this->content_data->parameters    = $url_parameters;
		$this->content_data->complete_path = $this->get_complete_file_path();
		$this->content_data->complete_path_with_parameters = $this->content_data->complete_path . '?' . http_build_query( $this->content_data->parameters );
	}

	/**
	 * @return bool
	 */
	private function user_can_access_to_the_content() {
		// Do all the validation
		return is_user_logged_in();
	}

	/**
	 *
	 */
	private function get_custom_forbidden_page() {
		// Get custom forbidden page
	}

	/**
	 *
	 */
	private function show_protected_content() {
		// Set headers
		$this->set_headers();

		// Get file
		$this->load_file();
	}

	/**
	 *
	 */
	private function set_headers() {
		// Set content type
		header( 'Content-Type: ' . $this->get_content_type() );

		// Set content length
		if ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) === false ) {
			header( 'Content-Length: ' . filesize( $this->content_data->complete_path ) );
		}

		// Set Cache control
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );

		// Set X-Robots-Tag
		header( 'X-Robots-Tag: none' );

		// Get and set last modified date
		$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $this->content_data->complete_path ) );
		header( 'Last-Modified: ' . $last_modified . ' GMT' );

		// Create eTag using a md5 hash of the last modified date
		$etag = '"' . md5( $last_modified ) . '"';
		header( 'ETag: ' . $etag );

		// Check if it supports xsendfile
		if ( $this->supports_mod_xsendfile() ) {
			header( 'X-Sendfile: ' . $this->content_data->complete_path );
		}

		// Get client eTag
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;

		if ( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
		}

		// Get client last modified
		$client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
		// Get the timestamp
		$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

		// Make a timestamp for our most recent modification
		$modified_timestamp = strtotime( $last_modified );

		// Check if the client data is defined
		$is_client_data_defined = $client_last_modified && $client_etag;
		// Check if the client has a more recent change to the file than the file in the server
		$client_has_more_recent_modification = $client_modified_timestamp >= $modified_timestamp;
		// Check if the client has the same eTag
		$client_has_same_etag = $client_etag == $etag;
		// Compare last modified from the file with the last modified from the client
		if ( $is_client_data_defined ? ( $client_has_more_recent_modification && $client_has_same_etag ) : ( $client_has_more_recent_modification || $client_has_same_etag ) ) {
			status_header( 304 );
			exit;
		}
	}

	/**
	 *
	 */
	private function load_file() {
		if ( ob_get_length() ) {
			ob_clean();
		}

		flush();

		if ( ! $this->supports_mod_xsendfile() ) {
			readfile( $this->content_data->complete_path );
		}

		exit;
	}

	/**
	 * @return bool
	 */
	private function supports_mod_xsendfile() {
		// Check if the apache_get_modules function exists
		// and check if mod_xsendfile is in apache_get_modules()
		return function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules() );
	}

	/**
	 * @return string
	 */
	private function get_content_type() {
		// Define or get content type
		return 'text/html';
	}

	/**
	 * @return string
	 */
	private function get_complete_file_path() {
		// Get info about the uploads folder
		$uploads_folder = wp_get_upload_dir();

		// Check if there was an error
		if ( ! $uploads_folder['error'] ) {
			// Get the URL GET variables, but remove the
			$url_variables = $_GET;

			// If it wasn't, then get the path
			$file = $uploads_folder['basedir'] . '/uncanny-snc/' . $this->content_data->id . $this->content_data->relative_path;

			// Check if it's a directory
			if ( is_dir( $file ) ) {
				// Then try to search for the index file
				if ( file_exists( rtrim( $file, '/' ) . '/index.html' ) ) {
					// Update the file path
					$file .= '/index.html';
				} elseif ( file_exists( rtrim( $file, '/' ) . '/index.htm' ) ) {
					// Update the file path
					$file .= '/index.htm';
				} else {
					// We didn't find any file
					// We can't continue.
					header( 'HTTP/1.0 403 Forbidden' );
					die;
				}
			}
		} else {
			// We wan't continue. Error
			wp_die( __( 'Something went wrong', 'uncanny-learndash-reporting' ) );
		}

		// Return file
		return $file;
	}

	private function is_protection_enabled(){
		// Get global setting
		$global_protection_enabled = strtolower( get_option( 'tincanny_nonce_protection', 'yes' ) ) == 'yes';

		// Get the individual setting.
		// Before starting, we will create a variable with the default value
		$post_protection_setting = 'use-global-setting';

		// Now, to try to get the real value, we have to get the post id first
		// The string is included in the "auth" parameter, inside a string like "LearnDashId999"
		// The parameter could not be in the url though, so we will try to get the string first
		$string_with_ID = isset( $this->content_data->parameters[ 'auth' ] ) ? strtolower( $this->content_data->parameters[ 'auth' ] ) : '';

		// Try to get the LearnDash content ID
		if ( preg_match( '/learndashid([0-9]*)/', $string_with_ID, $matches ) ){
			// Get the ID
			$learndash_post_id = $matches[1];

			// If we do, then get the value of the "Protect SCORM/Tin Can Modules?" field
			$post_meta = get_post_meta( $learndash_post_id, '_WE-meta_' );
			if ( ! empty( $post_meta ) && isset( $post_meta[0] ) ) {
				$post_protection_setting = sanitize_title( strtolower( $post_meta[0]['protect-scorm-tin-can-modules'] ) );
			}
		}

		// Decide if the user has access or not to the content
		// Check if the post is using the global setting
		if ( $post_protection_setting == 'use-global-setting' ){
			$protection_enabled = $global_protection_enabled;
		}
		// Otherwise, use the post setting
		else {
			$protection_enabled = $post_protection_setting == 'yes';
		}

		// Check if the protection settings is enabled
		return $protection_enabled;
	}
}

new TinCannyProtection();
