<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) || !WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	status_header( 404 );
	exit;
}
	
$roles = get_editable_roles();
if ( !empty( $roles ) ) {
	foreach ( $roles as $role_name => $role_info ) {
		$role = get_role( $role_name );
		if ( ( $role ) && ( $role instanceof WP_Role ) ) {
			$role->remove_cap( 'propanel_widgets' ); 
		}
	}
}
