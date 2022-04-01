<?php
/**
 * Storyline Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\FileSystem;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

abstract class absModule {
	use traitModule;

	const NONCE_BLOCK_B212 = "
// Check if the method includes is defined
if ( ! String.prototype.includes ){
	// Otherwise, define it
    String.prototype.includes = function( search, start ){
        if ( typeof start !== 'number' ){
            start = 0;
        }

        if ( start + search.length > this.length ){
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

// Define function to get parameters from the URL
function getParameterByName( name, url ){
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, \"\\$&\");
    var regex = new RegExp(\"[?&]\" + name + \"(=([^&#]*)|&|#|$)\"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, \" \"));
}

var actor   = JSON.parse( getParameterByName( 'actor' ) );
var baseUrl = getParameterByName( 'base_url' );
var nonce   = getParameterByName( 'nonce' );
var email   = actor.mbox[0].replace( 'mailto:', '' );
";

	const NONCE_BLOCK = "
// Check if the method includes is defined
if ( ! String.prototype.includes ){
	// Otherwise, define it
    String.prototype.includes = function( search, start ){
        if ( typeof start !== 'number' ){
            start = 0;
        }

        if ( start + search.length > this.length ){
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

// Define function to get parameters from the URL
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, \"\\$&\");
    var regex = new RegExp(\"[?&]\" + name + \"(=([^&#]*)|&|#|$)\"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, \" \"));
}

var actor = JSON.parse(getParameterByName('actor'));
var baseUrl = getParameterByName('base_url');
var nonce = getParameterByName('nonce');
var email = actor.mbox[0].replace('mailto:', '');
var postId = getParameterByName('auth').replace('LearnDashId', '');
";

	public function __construct( $item_id ) {
		$this->set_item_id( $item_id );
	}

	protected function get_dir_contents() {
		$target = $this->get_target_dir();

		$myDirectory = opendir( $target );
		$fileList = array();
		while( $entryName = readdir( $myDirectory ) ) { $fileList[] = $entryName; }
		closedir($myDirectory);

		return $fileList;
	}

	public function register() {
		$this->add_tincan_support();

		$url = $this->get_registering_url();

		if ( ! $url ) {
			$this->delete();
			return false;
		}

		$item_id = $this->get_item_id();
		if ( strpos( $item_id, '-temp' ) !== false ) {
			// remove old folder
			$item_id_actual = str_replace('-temp','',$item_id);
			$this->set_item_id($item_id_actual);
			$this->delete_tree( $this->get_target_dir() );
			rename($this->get_target_dir() . '-temp',$this->get_target_dir());
			$url = $this->get_registering_url();
		}

		\TINCANNYSNC\Database::add_detail( $this->get_item_id(), $this->get_type(), $url, $this->get_subtype() );

		return true;
	}

	/**
	 * Delete
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function delete() {
		$target = $this->get_target_dir();

		if ( !file_exists( $target ) )
			return false;

		$this->delete_tree( $target );
 		\TINCANNYSNC\Database::delete( $this->get_item_id() );
	}
	/**
	 * Delete
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function delete_bookmarks() {
 		 \UCTINCAN\Database::delete( $this->get_item_id() );
	}
	/**
	 * Delete
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function delete_all_data() {
 		\TINCANNYSNC\Database::delete( $this->get_item_id() );
	}

	private function delete_tree( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

		foreach ( $files as $file )
			( is_dir( "$dir/$file" ) ) ? $this->delete_tree( "$dir/$file" ) : unlink( "$dir/$file" );

		return rmdir( $dir );
	}

	public function is_available() {
		$target = $this->get_target_dir();
		return file_exists( $target );
	}

	protected abstract function get_registering_url();
	protected abstract function add_tincan_support();
}
