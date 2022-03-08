<?php
if ( !function_exists( 'format_bytes' ) ) {
	function format_bytes( $size, $precision = 2 ) {
		$base = log( $size, 1024 );
		$suffixes = array( '', 'kB', 'MB', 'GB', 'TB' );
		$number = number_format( round( pow( 1024, $base - floor( $base ) ), $precision ) );

		return $number . $suffixes[floor( $base )];
	}
}

if ( !function_exists( 'delete_tree' ) ) {
	function delete_tree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? delete_tree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
}

if ( !function_exists( 'in_array_search' ) ) {
	function in_array_search( $needle, $haystack ) {
//		if ( !in_array( $needle, $haystack ) ) return false;

		if ( !is_array( $needle ) ) $needle = array( $needle );
		foreach( $haystack as $value ) {
			if ( in_array( $value, $needle ) ) return $value;
		}

		return false;
	}
}










