<?php 
/*
  Plugin Name: ARMember - Complete Membership Plugin
  Description: The most powerful membership plugin to handle any complex membership wordpress sites with super ease.
  Version: 5.1
  Plugin URI: https://www.armemberplugin.com
  Author: Repute InfoSystems
  Author URI: https://www.armemberplugin.com
  Text Domain: ARMember
 */

define('MEMBERSHIP_DIR_NAME', 'armember');
define('MEMBERSHIP_DIR', WP_PLUGIN_DIR . '/' . MEMBERSHIP_DIR_NAME);

require_once MEMBERSHIP_DIR.'/autoload.php';