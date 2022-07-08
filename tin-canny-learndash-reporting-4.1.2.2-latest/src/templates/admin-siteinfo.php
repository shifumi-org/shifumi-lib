<?php

global $wpdb,
	   $wp_version;

// Functions to add elements globally
/**
 * @param $meets_requirements
 * @param $data
 *
 * @return string
 */
function uo_item_meets_requirements( $meets_requirements, $data ) {
	$output = '<div style="color: green;">' . $data . '</div>';

	if ( ! $meets_requirements ) {
		$output = '<div style="color: red;">' . $data . '</div>';
	}

	return $output;
}

/**
 * @return array|string[]
 */
function uo_get_server_database_version() {
	if ( class_exists( 'LDLMS_DB' ) ) {
		$db_server_info = LDLMS_DB::get_db_server_info();
	} else {
		global $wpdb;

		$db_server_info = array(
			'mysqldb_active'      => false,
			'mysqldb_version_min' => 5.6,
			'mariadb_actve'       => false,
			'mariadb_version_min' => 10.3,
			'db_version_found'    => '',
		);

		$db_server_version = $wpdb->get_results( "SHOW VARIABLES WHERE `Variable_name` IN ( 'version_comment', 'version' )", OBJECT_K );

		if ( ! empty( $db_server_version ) ) {
			foreach ( $db_server_version as $field_key => $field_set ) {

				switch ( $field_key ) {
					case 'version_comment':
						if ( ( is_object( $field_set ) ) && ( property_exists( $field_set, 'Value' ) ) ) {
							if ( stristr( $field_set->Value, 'mariadb' ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
								$db_server_info['mariadb_actve'] = true;
							} else {
								$db_server_info['mysqldb_active'] = true;
							}
						}
						break;

					case 'version':
						if ( ( is_object( $field_set ) ) && ( property_exists( $field_set, 'Value' ) ) ) {
							$db_server_info['db_version_found'] = $field_set->Value; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						}
						break;

				}
			}
		}
	}
	$server_info = '';
	$db_type     = '';
	if ( $db_server_info['mysqldb_active'] ) {
		$db_version      = $db_server_info['db_version_found'];
		$db_type         = __( 'MySQL Version', 'uncanny-learndash-reporting');
		$version_compare = version_compare( $db_version, $db_server_info['mysqldb_version_min'], '>' );
		$server_info     = uo_item_meets_requirements( $version_compare, $db_version );
	} elseif ( $db_server_info['mariadb_active'] ) {
		$db_type         = __( 'MariaDB Version', 'uncanny-learndash-reporting');
		$db_version      = $db_server_info['db_version_found'];
		$version_compare = version_compare( $db_version, $db_server_info['mariadb_version_min'], '>' );
		$server_info     = uo_item_meets_requirements( $version_compare, $db_version );
	}

	return array(
		'db_type' => $db_type,
		'string'  => $server_info,
		'number'  => preg_replace( '/([^\d.]+).*/', '', $server_info ),
	);
}

// Create array where we're going to save all our tables
$tables = array();

// WordPress Minimum Requirements
$min_requirements = ' <a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Minimum Requirements', 'uncanny-learndash-reporting') . '</a>';

/**
 * "Enviroment" table
 */

$table_enviroment = (object) array(
	'title'   => __( 'Environment', 'uncanny-learndash-reporting'),
	'heading' => array(
		__( 'Setting', 'uncanny-learndash-reporting'),
		__( 'Value', 'uncanny-learndash-reporting'),
	),
	'rows'    => array(),
);

$table_enviroment->rows[] = array(
	__( 'Server info', 'uncanny-learndash-reporting'),
	isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
);

/**
 * PHP Version
 */

$php_version            = phpversion();
$meets_min_requirements = version_compare( $php_version, '7.0', '>' );
$php_version            = uo_item_meets_requirements( $meets_min_requirements, $php_version );

//if ( defined( 'PHP_OS' ) ) {
//	$php_version .= __( ' PHP_OS: ', 'uncanny-learndash-reporting') . PHP_OS;
//}
//
//if ( defined( 'PHP_OS_FAMILY' ) ) {
//	$php_version .= __( ' PHP_OS_FAMILY: ', 'uncanny-learndash-reporting') . PHP_OS_FAMILY;
//}

if ( ! $meets_min_requirements ) {
	$php_version .= $min_requirements;
}

$table_enviroment->rows[] = array(
	__( 'PHP Version', 'uncanny-learndash-reporting'),
	$php_version,
);

/**
 * MySQL Version
 */
$database_version = uo_get_server_database_version();

$table_enviroment->rows[] = array(
	$database_version['db_type'],
	$database_version['string'],
);
//}
//}

// Add "Enviroment" to the tables array
$tables[] = $table_enviroment;

/**
 * WordPress Settings table
 */

$table_wordpress_settings = (object) array(
	'title'   => __( 'WordPress Settings', 'uncanny-learndash-reporting'),
	'heading' => array(
		__( 'Setting', 'uncanny-learndash-reporting'),
		__( 'Value', 'uncanny-learndash-reporting'),
	),
	'rows'    => array(),
);

$table_wordpress_settings->rows[] = array(
	__( 'WordPress address (URL)', 'uncanny-learndash-reporting'),
	get_option( 'home' ),
);
$table_wordpress_settings->rows[] = array(
	__( 'Site address', 'uncanny-learndash-reporting'),
	get_option( 'siteurl' ),
);

// WordPress version
$table_wordpress_settings->rows[] = array(
	__( 'WordPress Version', 'uncanny-learndash-reporting'),
	$wp_version,
);

// Multisite
$table_wordpress_settings->rows[] = array(
	__( 'Multisite', 'uncanny-learndash-reporting'),
	is_multisite() ? __( 'Yes', 'uncanny-learndash-reporting') : __( 'No', 'uncanny-learndash-reporting'),
);

// Language
$table_wordpress_settings->rows[] = array(
	__( 'Site Language', 'uncanny-learndash-reporting'),
	get_locale(),
);

// WP Constants
$wp_constants = array(
	'DISABLE_WP_CRON',
	'WP_DEBUG',
	'WP_DEBUG_LOG',
	'WP_DEBUG_DISPLAY',
	'SCRIPT_DEBUG',
	'WP_AUTO_UPDATE_CORE',
	'WP_MAX_MEMORY_LIMIT',
	'WP_MEMORY_LIMIT',
	'DB_CHARSET',
	'DB_COLLATE',
);

foreach ( $wp_constants as $wp_constant ) {
	$table_wordpress_settings->rows[] = array(
		$wp_constant,
		defined( $wp_constant ) ? constant( $wp_constant ) : __( 'Not defined', 'uncanny-learndash-reporting'),
	);
}

// Object Cache
$table_wordpress_settings->rows[] = array(
	__( 'Object Cache', 'uncanny-learndash-reporting'),
	wp_using_ext_object_cache() ? __( 'Yes', 'uncanny-learndash-reporting') : __( 'No', 'uncanny-learndash-reporting'),
);

// Add "WordPress Settings" to the tables array
$tables[] = $table_wordpress_settings;

/**
 * WordPress Plugins table
 */

$table_wordpress_plugins = (object) array(
	'title'   => __( 'Active WordPress Plugins', 'uncanny-learndash-reporting'),
	'heading' => array(
		__( 'Plugin', 'uncanny-learndash-reporting'),
		__( 'Version', 'uncanny-learndash-reporting'),
	),
	'rows'    => array(),
);

$active_plugins = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
	$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
}

$active_plugins_data = array();

foreach ( $active_plugins as $plugin ) {
	$data                            = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
	$table_wordpress_plugins->rows[] = array(
		$data['Name'],
		$data['Version'],
	);
}
// Add "WordPress Plugins" to the tables array
$tables[] = $table_wordpress_plugins;

/**
 * Learndash Settings table
 */

$tables[] = (object) array(
	'title'   => __( 'LearnDash Settings', 'uncanny-learndash-reporting'),
	'heading' => array(
		__( 'Setting', 'uncanny-learndash-reporting'),
		__( 'Value', 'uncanny-learndash-reporting'),
	),
	'rows'    => array(
		array(
			__( 'Version', 'uncanny-learndash-reporting'),
			defined( 'LEARNDASH_VERSION' ) ? LEARNDASH_VERSION : __( 'LearnDash not active', 'uncanny-learndash-reporting'),
		),
		array(
			__( 'DB Version', 'uncanny-learndash-reporting'),
			defined( 'LEARNDASH_SETTINGS_DB_VERSION' ) ? LEARNDASH_SETTINGS_DB_VERSION : __( 'LearnDash not active', 'uncanny-learndash-reporting'),
		),
		array(
			__( 'Script Debug', 'uncanny-learndash-reporting'),
			( defined( 'LEARNDASH_SCRIPT_DEBUG' ) ) ? LEARNDASH_SCRIPT_DEBUG : __( 'not defined', 'uncanny-learndash-reporting'),
		),
	),
);

/**
 * PHP Settings
 */

$table_php_settings = (object) array(
	'title'   => __( 'PHP Settings', 'uncanny-learndash-reporting'),
	'heading' => array(
		__( 'Setting', 'uncanny-learndash-reporting'),
		__( 'Value', 'uncanny-learndash-reporting'),
	),
	'rows'    => array(),
);

$php_ini_settings = array(
	'max_execution_time',
	'max_input_time',
	'max_input_vars',
	'post_max_size',
	'max_file_uploads',
	'upload_max_filesize',
);

sort( $php_ini_settings );

foreach ( $php_ini_settings as $ini_key ) {
	$table_php_settings->rows[] = array(
		$ini_key,
		ini_get( $ini_key ),
	);
}

// Add "PHP Settings" to the tables array
$tables[] = $table_php_settings;

?>

<div class="uo-core">
	<div class="uo-core-siteinfo">
		<?php

		foreach ( $tables as $table ) {
			?>

			<div class="uo-core-siteinfo__title">
				<h3><?php echo $table->title; ?></h3>
			</div>

			<div class="uo-core-siteinfo__table">
				<table
					style="border-collapse: collapse; font-size: 13px; text-align: left; width: 100%;box-sizing: border-box; font-family: -apple-system,system-ui,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif;">
					<thead style="border-collapse: collapse; font-size: 13px; text-align: left;">
					<tr>
						<?php $i = 0;
						foreach ( $table->heading as $heading ) { ?>

							<th style="border: 1px solid #d9d9d9; padding: 4px 15px;">
								<?php echo $heading; ?>
							</th>

						<?php } ?>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $table->rows as $row ) { ?>

						<tr style="border-collapse: collapse; font-size: 13px; text-align: left;">

							<?php $i = 0;
							foreach ( $row as $cell ) { ?>

								<td style="border: 1px solid #d9d9d9; padding: 4px 15px; <?php echo 0 === $i % 2 ? ' width: 300px;' : '';
								$i ++; ?>">
									<?php echo $cell; ?>
								</td>

							<?php } ?>

						</tr>

					<?php } ?>

					</tbody>
				</table>
			</div>

			<?php
		}

		?>
	</div>
</div>
