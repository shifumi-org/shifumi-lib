<?php

global $wpdb,
$wp_version;

// Functions to add elements globally
function item_meets_requirements( $meets_requirements, $data ){
	$output = '<div style="color: green;">' . $data . '</div>';

	if ( ! $meets_requirements ){
		$output = '<div style="color: red;">' . $data . '</div>';
	}

	return $output;
}

// Create array where we're going to save all our tables
$tables = [];

// Wordpress Minimum Requirements
$min_requirements = ' <a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Minimum Requirements', 'uncanny-learndash-reporting' ) . '</a>';

/**
 * "Enviroment" table
 */

$table_enviroment = (object) [
	'title'   => __( 'Environment', 'uncanny-learndash-reporting' ),
	'heading' => [
		__( 'Setting', 'uncanny-learndash-reporting' ),
		__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'    => []
];

/**
 * PHP Version
 */

$php_version = phpversion();
$meets_min_requirements = version_compare( $php_version, '7.0', '>' );
$php_version = item_meets_requirements( $meets_min_requirements, $php_version );

if ( defined( 'PHP_OS' ) ) {
	$php_version .= __( ' PHP_OS: ', 'uncanny-learndash-reporting' ) . PHP_OS;
}

if ( defined( 'PHP_OS_FAMILY' ) ) {
	$php_version .= __( ' PHP_OS_FAMILY: ', 'uncanny-learndash-reporting' ) . PHP_OS_FAMILY;
}

if ( ! $meets_min_requirements ){
	$php_version .= $min_requirements;
}

$table_enviroment->rows[] = [
	__( 'PHP Version', 'uncanny-learndash-reporting' ),
	$php_version
];

/**
 * MySQL Version
 */

if ( $wpdb->is_mysql ){
	$mysql_version = $wpdb->db_version();
	$meets_min_requirements = version_compare( $mysql_version, '5.6', '>' );

	$mysql_version = item_meets_requirements( $meets_min_requirements, $mysql_version );

	if ( ! $meets_min_requirements ){
		$mysql_version .= $min_requirements;
	}

	$table_enviroment->rows[] = [
		__( 'MySQL version', 'uncanny-learndash-reporting' ),
		$mysql_version
	];
}

// Add "Enviroment" to the tables array
$tables[] = $table_enviroment;

/**
 * WordPress Settings table
 */

$table_wordpress_settings = (object) [
	'title'   => __( 'WordPress Settings', 'uncanny-learndash-reporting' ),
	'heading' => [
		__( 'Setting', 'uncanny-learndash-reporting' ),
		__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'    => []
];

// WordPress version
$table_wordpress_settings->rows[] = [
	__( 'WordPress Version', 'uncanny-learndash-reporting' ),
	$wp_version
];

// Multisite
$table_wordpress_settings->rows[] = [
	__( 'Multisite', 'uncanny-learndash-reporting' ),
	is_multisite() ? __( 'Yes', 'uncanny-learndash-reporting' ) : __( 'No', 'uncanny-learndash-reporting' )
];

// Language
$table_wordpress_settings->rows[] = [
	__( 'Site Language', 'uncanny-learndash-reporting' ),
	get_locale()
];

// WP Constants
$wp_constants = [ 'DISABLE_WP_CRON', 'WP_DEBUG', 'WP_DEBUG_LOG', 'WP_DEBUG_DISPLAY', 'SCRIPT_DEBUG', 'WP_AUTO_UPDATE_CORE', 'WP_MAX_MEMORY_LIMIT', 'WP_MEMORY_LIMIT', 'DB_CHARSET', 'DB_COLLATE' ];

foreach ( $wp_constants as $wp_constant ){
	$table_wordpress_settings->rows[] = [
		$wp_constant,
		defined( $wp_constant ) ? constant( $wp_constant ) : __( 'Not defined', 'uncanny-learndash-reporting' )
	];
}

// Object Cache
$table_wordpress_settings->rows[] = [
	__( 'Object Cache', 'uncanny-learndash-reporting' ),
	wp_using_ext_object_cache() ? __( 'Yes', 'uncanny-learndash-reporting' ) : __( 'No', 'uncanny-learndash-reporting' )
];

// Add "WordPress Settings" to the tables array
$tables[] = $table_wordpress_settings;

/**
 * WordPress Plugins table
 */

$table_wordpress_plugins = (object) [
	'title'   => __( 'WordPress Plugins', 'uncanny-learndash-reporting' ),
	'heading' => [
		__( 'Plugin', 'uncanny-learndash-reporting' ),
		__( 'Version', 'uncanny-learndash-reporting' )
	],
	'rows'    => []
];

$plugins = get_plugins();

foreach ( $plugins as $plugin ){
	$table_wordpress_plugins->rows[] = [
		$plugin[ 'Title' ],
		$plugin[ 'Version' ]
	];
}

// Add "WordPress Plugins" to the tables array
$tables[] = $table_wordpress_plugins;

/**
 * Learndash Settings table
 */

$tables[] = (object) [
	'title'   => __( 'Learndash Settings', 'uncanny-learndash-reporting' ),
	'heading' => [
		__( 'Setting', 'uncanny-learndash-reporting' ),
		__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'    => [
		[
			__( 'Version', 'uncanny-learndash-reporting' ),
			LEARNDASH_VERSION
		],
		[
			__( 'DB Version', 'uncanny-learndash-reporting' ),
			LEARNDASH_SETTINGS_DB_VERSION
		],
		[
			__( 'Script Debug', 'uncanny-learndash-reporting' ),
			( defined( 'LEARNDASH_SCRIPT_DEBUG' ) ) ? LEARNDASH_SCRIPT_DEBUG : __( 'not defined', 'uncanny-learndash-reporting' )
		]
	]
];

/**
 * PHP Settings
 */

$table_php_settings = (object) [
	'title'   => __( 'PHP Settings', 'uncanny-learndash-reporting' ),
	'heading' => [
		__( 'Setting', 'uncanny-learndash-reporting' ),
		__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'    => []
];

$php_ini_settings = [
	'max_execution_time',
	'max_input_time',
	'max_input_vars',
	'post_max_size',
	'max_file_uploads',
	'upload_max_filesize'
];

sort( $php_ini_settings );

foreach ( $php_ini_settings as $ini_key ){
	$table_php_settings->rows[] = [
		$ini_key,
		ini_get( $ini_key ),
	];
}

// Add "PHP Settings" to the tables array
$tables[] = $table_php_settings;

?>

<div class="uo-core">
	<div class="uo-core-siteinfo">
		<?php 

		foreach ( $tables as $table ){
			?>

			<div class="uo-core-siteinfo__title">
				<?php echo $table->title; ?>
			</div>

			<div class="uo-core-siteinfo__table">
				<table>
					<thead>
						<tr>
							<?php foreach ( $table->heading as $heading ){ ?>

								<th>
									<?php echo $heading; ?>
								</th>

							<?php } ?>
						</tr>
					</thead>
					<tbody>

						<?php foreach ( $table->rows as $row ){ ?>

							<tr>

								<?php foreach ( $row as $cell ){ ?>

									<td>
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