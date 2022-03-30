<?php
/**
 * Plugin Name:     LearnDash LMS - Certificate Builder
 * Description:     LearnDash certificate builder allows you build certificates for your courses using the Gutenberg WordPress block editor
 * Version:         1.0.3
 * Author:          LearnDash
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     learndash-certificate-builder
 */

define( 'LEARNDASH_CERTIFICATE_BUILDER_VERSION', '1.0.3' );
require_once __DIR__ . '/src/classmap.php';
require_once __DIR__ . '/src/constants.php';
require_once __DIR__ . '/src/functions.php';
// need to check if the core available.
$bootstrap = new \LearnDash_Certificate_Builder\Bootstrap();
add_action( 'init', array( $bootstrap, 'init' ) );
add_action( 'plugins_loaded', array( $bootstrap, 'load_plugin_textdomain' ) );
