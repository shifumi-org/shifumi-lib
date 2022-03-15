<?php
namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}
global $wpdb,
       $wp_version;

// Functions to add elements globally
function item_meets_requirements( $meets_requirements, $data ) {
	$output = '<div style="color: green;">' . $data . '</div>';

	if ( ! $meets_requirements ) {
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
	'title'       => __( 'HTTP/HTTPS Check', 'uncanny-learndash-reporting' ),
	'description' => sprintf( __( 'Check that your WordPress Address and Site Address in %1$sGeneral Settings%2$s match. 
									If they don\'t match, you may have problems loading content or receiving xAPI statements. 
									Also make sure that if users visit your site using https:// URLs, both values on the Settings 
									page also use https://.', 'uncanny-learndash-reporting' ),
		'<a href="' . get_admin_url( null, 'options-general.php' ) . '" target="_blank">',
		'</a>' ),
	'heading'     => [
		//__( 'HTTP/HTTPS Check', 'uncanny-learndash-reporting' ),
		//__( 'Status', 'uncanny-learndash-reporting' ),
	],
	'rows'        => []
];

/**
 * Scheme Check
 */

$home_url = home_url();
$site_url = site_url();

$home_url_direct = get_option( 'home' );
$site_url_direct = get_option( 'siteurl' );

if (
	! ( $home_url === $site_url ) ||
	! ( $home_url_direct === $site_url_direct ) ||
	! ( $home_url_direct === $home_url ) ||
	! ( $site_url_direct === $site_url ) ||
	! ( $home_url === $site_url_direct ) ||
	! ( $site_url === $home_url_direct )
) {
	$table_enviroment->rows[] = [
		__( 'HTTP/HTTPS Check', 'uncanny-learndash-reporting' ),
		sprintf(
			__(
				'<strong style="color: red;">Failed</strong><br />
				Check that your WordPress Address and Site Address in %1$sGeneral Settings%2$s match. 
									If they don\'t match, you may have problems loading content or receiving xAPI statements. 
									Also make sure that if users visit your site using https:// URLs, both values on the Settings 
									page also use https://.',
				'uncanny-learndash-reporting'
			),
			'<a href="' . get_admin_url( null, 'options-general.php' ) . '" target="_blank">',
			'</a>'
		)
	];
} else {
	$table_enviroment->rows[] = [
		__( 'HTTP/HTTPS Check', 'uncanny-learndash-reporting' ),
		__( '<strong style="color: green;">Passed</strong>', 'uncanny-learndash-reporting' )
	];

}


// Add "Enviroment" to the tables array
$tables[] = $table_enviroment;

/**
 * Permalink Settings table
 */

$table_wordpress_settings = (object) [
	'title'       => __( 'Permalink Settings', 'uncanny-learndash-reporting' ),
	'description' => sprintf( __( 'Tin Canny will not work with %1$syour site\'s permalinks%2$s set to <i>Plain</i>. We recommend <i>Post Name</i>, which is also beneficial for SEO.', 'uncanny-learndash-reporting' ),
		'<a href="' . get_admin_url( null, 'options-permalink.php' ) . '" target="_blank">',
		'</a>' ),
	'heading'     => [
		//__( 'Permalink Settings Check', 'uncanny-learndash-reporting' ),
		//__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'        => []
];
$structure                = get_option( 'permalink_structure' );
// Permalink structure
if ( empty( $structure ) ) {
	$table_wordpress_settings->rows[] = [
		__( 'Permalink Settings Check', 'uncanny-learndash-reporting' ),
		sprintf(
			__(
				'<strong style="color: red;">Failed</strong><br /> 
				Your site\'s permalinks are set to <i>Plain</i>. Change them to <i>Post Name</i> on your site\'s %1$sPermalink%2$s page',
				'uncanny-learndash-reporting'
			),
			'<a href="' . get_admin_url( null, 'options-permalink.php' ) . '" target="_blank">',
			'</a>'
		)
	];
} else {
	$table_wordpress_settings->rows[] = [
		__( 'Permalink Status', 'uncanny-learndash-reporting' ),
		__( '<strong style="color: green;">Passed</strong>', 'uncanny-learndash-reporting' )
	];
}

// Add "Permalink Settings" to the tables array
$tables[] = $table_wordpress_settings;

/**
 * Permalink Settings table
 */

$table_wordpress_settings = (object) [
	'title'       => __( 'Endpoint Availability', 'uncanny-learndash-reporting' ),
	'description' => __( 'Checks that the xAPI endpoint is reachable. If not, xAPI statements may not be recorded.', 'uncanny-learndash-reporting' ),
	'heading'     => [
		//__( 'End Points Access Check', 'uncanny-learndash-reporting' ),
		//__( 'Value', 'uncanny-learndash-reporting' )
	],
	'rows'        => []
];

$table_wordpress_settings->rows[] = [
	__( 'Endpoint Availability Check', 'uncanny-learndash-reporting' ),
	__( '<span id="endpoint_status">Checking...</span> <br /><button id="endpoint_recheck">Re-check</button>    ', 'uncanny-learndash-reporting' )
];


// Add "Permalink Settings" to the tables array
$tables[] = $table_wordpress_settings;

ob_start();
phpinfo( INFO_MODULES );
$contents        = ob_get_clean();
$moduleAvailable = strpos( $contents, 'mod_security' ) !== FALSE;

if( $moduleAvailable ) {
	$table_wordpress_settings = (object) [
		'title'       => __( 'Mod Security Check', 'uncanny-learndash-reporting' ),
		'description' => __( 'Checks security module is allowing endpoints. If not, xAPI statements may not be recorded.', 'uncanny-learndash-reporting' ),
		'heading'     => [
			//__( 'End Points Access Check', 'uncanny-learndash-reporting' ),
			//__( 'Value', 'uncanny-learndash-reporting' )
		],
		'rows'        => []
	];
	
	$table_wordpress_settings->rows[] = [
		__( 'Mod_Security Check', 'uncanny-learndash-reporting' ),
		__( '<span id="modsecurity_status">Checking...</span> <br /><button id="modsecurity_recheck">Re-check</button>    ', 'uncanny-learndash-reporting' )
	];
	
	$tables[] = $table_wordpress_settings;
}
?>
<div class="wrap">
	<div class="tclr">

		<?php

		// Add admin header and tabs
		$tab_active = 'uncanny-tincanny-site-check';
		include Config::get_template( 'admin-header.php' );

		?>

		<div class="tclr-help">
			<div class="uo-core">
				<div class="uo-core-siteinfo">
					<?php

					foreach ( $tables as $table ) {
						?>

						<div class="uo-core-siteinfo__title">
							<?php echo $table->title; ?>
						</div>


						<div class="uo-core-siteinfo__description">
							<p><?php echo $table->description; ?></p>
						</div>

						<div class="uo-core-siteinfo__table">
							<table>
								<thead>
								<tr>
									<?php //foreach ( $table->heading as $heading ) { ?>

									<!--<th>
											<strong><?php /*echo $heading; */ ?></strong>
										</th>-->

									<?php //} ?>
								</tr>
								</thead>
								<tbody>

								<?php foreach ( $table->rows as $row ) { ?>

									<tr>

										<?php foreach ( $row as $k => $cell ) { ?>

											<td>
												<?php echo 0 === $k ? "<strong>$cell</strong>" : $cell; ?>
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
		</div>
	</div>
</div>

<script>
  var actor = 'admin'
  var baseUrl = reportingApiSetup.root
  var nonce = reportingApiSetup.nonce
  var email = reportingApiSetup.test_user_email
  var postId = '0'
  var check_element = document.getElementById('endpoint_status')

  var xhttp = new XMLHttpRequest()
  xhttp.onreadystatechange = function () {

    if (this.readyState == 4 && this.status == 200) {
      // Delete document

      check_element.innerHTML = '<strong style="color: green;">Passed</strong>'

    }

    if (this.readyState == 4 && this.status != 200) {
      check_element.innerHTML = '<?php echo
	  __( '<strong style="color: red;">Failed</strong><br /> Your site\\\'s Tin Canny xAPI endpoint is unreachable. Try temporarily disabling any active maintenance or security plugins then refreshing this page. If the problem persists, contact us and let us know the following URL is unreachable:', 'uncanny-learndash-reporting' )?> ' + baseUrl + '/wp-json/uncanny_reporting/v1/'
    }
  }

  xhttp.open('GET', baseUrl + '/wp-json/uncanny_reporting/v1/auth/?email=' + email + '&nonce=' + nonce + '&postId=' + postId, true)
  xhttp.send()

  document.getElementById('endpoint_recheck').onclick = function () {
    xhttp.open('GET', baseUrl + '/wp-json/uncanny_reporting/v1/auth/?email=' + email + '&nonce=' + nonce + '&postId=' + postId, true)
    xhttp.send()
  }

  <?php if( $moduleAvailable ) { ?>
      var modsecurity_element = document.getElementById('modsecurity_status')
    
      var xhttp = new XMLHttpRequest()
      xhttp.onreadystatechange = function () {
    
          if (this.readyState == 4 && this.status == 200) {
              // Delete document
              modsecurity_element.innerHTML = '<strong style="color: green;">Passed</strong>'
          }
    
          if (this.readyState == 4 && this.status != 200) {
              modsecurity_element.innerHTML = '<?php echo
              __( '<strong style="color: red;">Failed</strong><br /> Your site\\\'s Tin Canny xAPI endpoint is unreachable. Try temporarily disabling any active maintenance or security plugins then refreshing this page. If the problem persists, contact us and let us know the following URL is unreachable:', 'uncanny-learndash-reporting' )?> ' + baseUrl + '/ucTinCan/activities/state?check=%"'
          }
      }
    
      xhttp.open('PUT', baseUrl + '/ucTinCan/activities/state?stateId=check&activityId=agent=%7B"objectType"%3A"Agent"%2C"mbox"%3A"mailto%3A' + email + '"%2C"name"%3A"admin"%7D"&nonce=' + nonce + '&postId=' + postId, true)
      xhttp.send()
    
      document.getElementById('modsecurity_recheck').onclick = function () {
          xhttp.open('PUT', baseUrl + '/ucTinCan/activities/state?stateId=check&activityId=agent=%7B"objectType"%3A"Agent"%2C"mbox"%3A"mailto%3A' + email + '"%2C"name"%3A"admin"%7D"&nonce=' + nonce + '&postId=' + postId, true)
          xhttp.send()
      }
  <?php }?>

</script>