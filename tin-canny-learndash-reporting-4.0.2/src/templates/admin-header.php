<?php

namespace uncanny_learndash_reporting;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Variables:
 * $tab_active   The ID of the active tab
 */

$tabs = [];

$manage_content_cap = apply_filters( 'tc_manage_content_cap', 'manage_options' );
$capability         = apply_filters( 'tincanny_can_get_data', 'manage_options' );


// Restrict endpoint to only users who have the manage_options capability.
if ( current_user_can( $capability ) ) {
	$tabs = [
		(object) [
			'id'   => 'uncanny-learnDash-reporting',
			'href' => menu_page_url( 'uncanny-learnDash-reporting', false ),
			'name' => __( 'Reports', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'manage-content',
			'href' => menu_page_url( 'manage-content', false ),
			'name' => __( 'Manage Content', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'snc_options',
			'href' => menu_page_url( 'snc_options', false ),
			'name' => __( 'Settings', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'uncanny-tincanny-kb',
			'href' => menu_page_url( 'uncanny-tincanny-kb', false ),
			'name' => __( 'Help', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'uncanny-tincanny-plugins',
			'href' => menu_page_url( 'uncanny-tincanny-plugins', false ),
			'name' => __( 'LearnDash Plugins', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'uncanny-reporting-license-activation',
			'href' => menu_page_url( 'uncanny-reporting-license-activation', false ),
			'name' => __( 'License Activation', 'uncanny-learndash-reporting' )
		],
		(object) [
			'id'   => 'uncanny-tincanny-site-check',
			'href' => menu_page_url( 'uncanny-tincanny-site-check', false ),
			'name' => __( 'Site Check', 'uncanny-learndash-reporting' )
		]
	];
}elseif ( ! current_user_can( $manage_content_cap ) ) {
	$tabs = [
		(object) [
			'id'   => 'manage-content',
			'href' => menu_page_url( 'manage-content', false ),
			'name' => __( 'Manage Content', 'uncanny-learndash-reporting' )
		]
	];
}

?>

<div class="tclr-header">
	<div class="tclr-header-top">
		<div class="tclr-header-top__title">
			<?php _e( 'Tin Canny Reporting for LearnDash', 'uncanny-learndash-reporting' ); ?>
		</div>
		<div class="tclr-header-top__author">
			<span><?php _e( 'by', 'uncanny-learndash-reporting' ); ?></span>
			<a href="https://uncannyowl.com" target="_blank" class="tclr-header-top__logo">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1927.77 400.27">
					<path fill="#2b2b2b"
						  d="M1628.79,32.09A245.08,245.08,0,0,0,1636.11,0a284,284,0,0,1-1.55,68.56c-4.88,34.27-16.57,68.53-39,95.42-17.81,21.61-42.59,37-69.43,44.63a197.25,197.25,0,0,1-45.66,7.4,91.14,91.14,0,0,1,40.9,28.58c13.11,16.35,20,37.27,20.33,58.15.48,17-3,34.25-11.06,49.32a88.46,88.46,0,0,1-42.39,39.66c-16.81,7.76-35.83,9.79-54.11,7.81a93,93,0,0,1-55.92-26.88c-12.83-13-21.18-30.17-24.39-48.11-3.44-19.84-1.65-40.77,6.32-59.36-27.56,28.28-45.81,64.8-55.24,102.93a261.71,261.71,0,0,1,5-71.94c6.71-31.8,20.36-63,43.44-86.31,22.19-22.81,52.61-36.28,83.8-41,19.58-3.51,39.68-1.63,59.2-5.58a152.43,152.43,0,0,0,80.91-41.57C1602.21,97.19,1618.77,65.3,1628.79,32.09Zm-189.28,199a68.91,68.91,0,0,0-45.68,23.21c-22.94,26.19-24.58,68-4.63,96.37a68.88,68.88,0,0,0,38,26.89c18.61,4.9,39.36,3,56-7a69.82,69.82,0,0,0,27.94-30.93c7.73-16.34,9.35-35.31,5.39-52.88-4.12-18-14.92-34.76-30.61-44.81C1472.37,232.93,1455.62,229.55,1439.51,231.08Z"/>
					<path fill="#2b2b2b"
						  d="M435.89,214a99,99,0,0,1,58.77,2.19,66.42,66.42,0,0,1,31.42,22.84c-6.25,4.55-12.54,9.06-18.81,13.59-10.3-14.1-27.78-21.95-45.06-21.88a62.81,62.81,0,0,0-33.66,9.1A72,72,0,0,0,399,274.52a82.65,82.65,0,0,0,2.33,67.91,67.78,67.78,0,0,0,29.83,30.48c14,7.37,30.59,8.74,45.95,5.69,14.83-3,28-12,37.3-23.72,5.4,4.55,10.85,9,16.22,13.62A74.9,74.9,0,0,1,494.88,395a102.42,102.42,0,0,1-55.72,2.6A90.32,90.32,0,0,1,393.94,371a94,94,0,0,1-23.67-54.23c-2-20.19,1.32-41.13,11.07-59.05C392.52,236.46,412.86,220.51,435.89,214Z"/>
					<path fill="#2b2b2b"
						  d="M0,215.69q10.76,0,21.5,0,0,53,0,106c0,14.07,1.78,28.94,9.84,40.88a39.5,39.5,0,0,0,27.73,16.91c13.44,1.74,28.44.28,39.08-8.93,12.68-10.62,16.44-28,16.93-43.8.06-37,0-74,0-111.07,7.21,0,14.42,0,21.64,0-.05,38,0,76,0,114-.46,16.1-3.91,32.79-13.52,46.05-9.83,13.85-26.23,22.13-42.93,23.85-17,1.75-35.33.46-50.26-8.72-12.07-7-20.78-18.89-25.17-32A97.47,97.47,0,0,1,0,329.74Z"/>
					<path fill="#2b2b2b"
						  d="M181.25,215.68c9.05,0,18.1.07,27.15-.05,35.28,49.82,70,100,105.41,149.79.16-49.91,0-99.82.07-149.74,7.2,0,14.41,0,21.62,0v180c-9.11,0-18.22,0-27.32,0q-47.68-67.77-95.25-135.62c-3.35-4.65-6.46-9.47-10-14-.15,49.85,0,99.72-.06,149.58-7.21,0-14.42,0-21.63,0Z"/>
					<path fill="#2b2b2b"
						  d="M619.55,215.7q10.22,0,20.44,0,38.52,90,77.06,179.92-12.67.15-25.36,0c-6-14.64-12.21-29.22-18.31-43.82-30.22.05-60.44-.12-90.66.08-6.06,14.65-12.35,29.2-18.54,43.79-8.16,0-16.32,0-24.47,0Q579.61,305.69,619.55,215.7Zm-.76,51.38q-13.72,32.3-27.45,64.59,36.88,0,73.78,0-17.88-44.3-35.83-88.57C625.29,250.84,622.36,259.13,618.79,267.08Z"/>
					<path fill="#2b2b2b"
						  d="M741.25,215.68c9.07,0,18.14,0,27.2,0,35.21,49.87,70.06,100,105.35,149.81.18-49.93,0-99.85.08-149.78,7.2,0,14.41,0,21.62,0v180c-9.12,0-18.23,0-27.35,0-35.1-49.88-70-99.9-105.21-149.7-.14,49.89,0,99.79-.06,149.68-7.21,0-14.42,0-21.63,0Z"/>
					<path fill="#2b2b2b"
						  d="M940.25,215.68c9.06,0,18.12.06,27.18,0,35.24,49.85,70.05,100,105.37,149.81.17-49.92,0-99.84.08-149.77,7.2,0,14.41,0,21.62,0v180c-9.11,0-18.21,0-27.31,0-35.16-49.85-70-99.91-105.25-149.68-.14,49.88,0,99.76-.06,149.64-7.21,0-14.42,0-21.63,0Z"/>
					<path fill="#2b2b2b"
						  d="M1114.86,215.69c9.16,0,18.34,0,27.51,0,17,27,33.91,54.16,50.91,81.21q26.19-40.58,52.35-81.16,13-.06,26.07,0c-22.38,34.05-45,68-67.24,102.09-.8,5.52-.11,11.27-.33,16.88q0,30.49,0,61-10.83,0-21.63,0c0-25.74.11-51.49-.08-77.22C1160.2,284,1137.31,250,1114.86,215.69Z"/>
					<path fill="#2b2b2b"
						  d="M1555.71,215.69c8,0,16-.11,24,.07q20.52,74.82,41.28,149.57c15.17-49.76,29.84-99.7,44.68-149.58,8.1-.16,16.2,0,24.31-.06,14.84,49.91,29.45,99.89,44.47,149.75,14.19-49.87,28.07-99.83,42.12-149.74,7.57,0,15.13,0,22.71,0q-26.6,90-53.23,180c-7.84,0-15.68.05-23.51,0q-22.44-76.45-44.94-152.89c-15.2,50.94-30.13,101.95-45.24,152.91-7.92,0-15.83.06-23.75,0Q1582.28,305.64,1555.71,215.69Z"/>
					<path fill="#2b2b2b"
						  d="M1823.25,215.68c7.21,0,14.41,0,21.63,0q0,80,0,160c27.63,0,55.26,0,82.89,0-.09,6.68.09,13.35-.1,20-34.8-.14-69.61,0-104.42-.06Z"/>
					<path fill="#f9ba0f"
						  d="M1439.51,231.08c16.11-1.53,32.86,1.85,46.43,10.88,15.69,10,26.49,26.77,30.61,44.81,4,17.57,2.34,36.54-5.39,52.88a69.82,69.82,0,0,1-27.94,30.93c-16.67,9.94-37.42,11.87-56,7a68.88,68.88,0,0,1-38-26.89c-20-28.35-18.31-70.18,4.63-96.37A68.91,68.91,0,0,1,1439.51,231.08Zm12.14,25.22-.32-.06-.31.4c-16.91-.9-34,7.31-43.48,21.39a48.88,48.88,0,0,0-2.3,51.64,49.54,49.54,0,0,0,87.49-2.58,49,49,0,0,0-4.37-50.12l.07-.22c1.44-7.68-1.14-16.08-7.21-21.13C1473.09,248.28,1459.38,248.48,1451.65,256.3Z"/>
					<path fill="#fef4d8"
						  d="M1451.65,256.3c7.73-7.82,21.44-8,29.57-.68,6.07,5.05,8.65,13.45,7.21,21.13-5.07-6.09-11-11.64-18.24-15C1464.41,258.72,1458,257.62,1451.65,256.3Z"/>
					<path d="M1407.54,278c9.49-14.08,26.57-22.29,43.48-21.39-2.71,3.19-5.07,6.82-5.82,11a21.86,21.86,0,0,0,8.15,21.85,22.23,22.23,0,0,0,25,1.29A22.83,22.83,0,0,0,1488.36,277a49,49,0,0,1,4.37,50.12,49.54,49.54,0,0,1-87.49,2.58A48.88,48.88,0,0,1,1407.54,278Z"/>
					<path fill="#d6d6d6"
						  d="M1451,256.64l.31-.4.32.06c6.3,1.32,12.76,2.42,18.54,5.42,7.24,3.39,13.17,8.94,18.24,15l-.07.22a22.83,22.83,0,0,1-10.06,13.81,22.23,22.23,0,0,1-25-1.29,21.86,21.86,0,0,1-8.15-21.85C1446,263.46,1448.31,259.83,1451,256.64Z"/>
				</svg>
			</a>
		</div>
	</div>

	<nav class="nav-tab-wrapper">
		<div class="tclr-admin-nav">
    		<div class="tclr-admin-nav-items">
				<?php foreach ( $tabs as $tab ) { ?>

					<?php

					// Define the extra CSS classes of the tab
					$css_classes = [];

					// Check if it's the current tab
					if ( $tab_active == $tab->id ) {
						$css_classes[] = 'nav-tab-active';
					}

					?>

					<a href="<?php echo $tab->href; ?>" class="nav-tab <?php echo implode( ' ', $css_classes ); ?>">
						<?php echo $tab->name; ?>
					</a>

				<?php } ?>
			</div>
			<div class="tclr-admin-nav-social-icons">
				<a href="https://www.facebook.com/UncannyOwl/" target="_blank" class="tclr-admin-nav-social-icon tclr-admin-nav-social-icon--facebook" tclr-tooltip-admin="<?php _e( 'Follow us on Facebook', 'uncanny-learndash-reporting' ); ?>">
					<span class="tincanny-icon tincanny-icon-facebook"></span>
				</a>
				<a href="https://twitter.com/UncannyOwl" target="_blank" class="tclr-admin-nav-social-icon tclr-admin-nav-social-icon--twitter" tclr-tooltip-admin="<?php _e( 'Follow us on Twitter', 'uncanny-learndash-reporting' ); ?>">
					<span class="tincanny-icon tincanny-icon-twitter"></span>
				</a>
				<a href="https://www.linkedin.com/company/uncannyowl" target="_blank" class="tclr-admin-nav-social-icon tclr-admin-nav-social-icon--linkedin" tclr-tooltip-admin="<?php _e( 'Follow us on LinkedIn', 'uncanny-learndash-reporting' ); ?>">
					<span class="tincanny-icon tincanny-icon-linkedin"></span>
				</a>
			</div>
		</div>
	</nav>
</div>