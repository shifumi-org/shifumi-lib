<?php

include("../../../../../wp-load.php");

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//php details

$directaccesskey = "arm999repute";

$directaccess = isset($_REQUEST['da']) ? $_REQUEST['da'] : '';

if ( is_user_logged_in() || $directaccesskey==$directaccess) 
{
	
}
else
{
	$redirect_to = user_admin_url();
	wp_safe_redirect($redirect_to);
}

$geoiploaded = "";

if(!(extension_loaded('geoip'))) {
	$geoiploaded = "No";
}
else
{
	$geoiploaded = "Yes";
}

$ziploaded = "";

if(!(extension_loaded('zip'))) {
	$ziploaded = "No";
} else {
	$ziploaded = "Yes";
}

$php_version = phpversion();

$server_ip = $_SERVER['SERVER_ADDR'];

$servername = $_SERVER['SERVER_NAME'];

//$server_user = $_ENV["USER"];

$upload_max_filesize = ini_get('upload_max_filesize');

$post_max_size = ini_get('post_max_size');

$short_open_tag = ini_get('short_open_tag');

$max_input_vars = ini_get('max_input_vars');

if($short_open_tag==1)
{
	$short_open_tag = "Yes";
}
else
{
	$short_open_tag = "No";
}
if(ini_get('safe_mode'))
{
	$safe_mode = "On";
}
else
{
	$safe_mode = "Off";
}

$memory_limit = ini_get('memory_limit');

$apache_version = "";

if(function_exists('apache_get_version'))
{
	$apache_version = apache_get_version();
}
else
{
	$apache_version = $_SERVER['SERVER_SOFTWARE']."( ".$_SERVER['SERVER_SIGNATURE']." )";	
}

$system_info = php_uname();

//$mysql_server_version = mysqli_get_server_info();
global $wpdb;
$mysql_server_version = $wpdb->db_version();

//wordpress details

$wordpress_version = get_bloginfo('version');

$wordpress_sitename = get_bloginfo('name');

$wordpress_sitedesc = get_bloginfo('description');

$wordpress_wpurl = site_url();

$wordpress_url = home_url();

$wordpress_admin_email = get_bloginfo('admin_email');

$wordpress_language = get_bloginfo('language');

//$wordpress_templateurl = wp_get_theme();

$my_theme = wp_get_theme();
$wordpress_templateurl = $my_theme->get( 'Name' );
$wordpress_templateurl_version = $my_theme->get( 'Version' );


$wordpress_charset = get_bloginfo('charset');

$wordpress_debug  = WP_DEBUG;

if($wordpress_debug==true)
{
	$wordpress_debug = "On";
}
else
{
	$wordpress_debug = "Off";
}

if ( is_multisite() ) { $wordpress_multisite = 'Yes'; }else( $wordpress_multisite = "No");

$plugin_dir_path = WP_PLUGIN_DIR;
$upload_dir_path = wp_upload_dir();
$armember_active = "Deactive";
$armember_version = "";
if ( is_plugin_active( 'armember/armember.php' ) ) 
{
  	$armember_active = "Active";
	$armember_version = get_option("arm_version");
}

$folderpermission = substr(sprintf('%o', fileperms($upload_dir_path["basedir"])), -4);

$folderlogpermission = substr(sprintf('%o', fileperms($plugin_dir_path.'/armember/log/')), -4);

$folderlogfilepermission = substr(sprintf('%o', fileperms($plugin_dir_path.'/armember/log/response.txt')), -4);

$plugin_list = get_plugins();
$plugin = array();
$active_plugins = get_option( 'active_plugins' );

foreach ($plugin_list as $key => $plugin) {
            $is_active = in_array($key, $active_plugins);

            
        	//filter for only gravityforms ones, may get some others if using our naming convention
        	if ( $is_active == 1){
        		$name = substr($key, 0, strpos($key,"/"));
				$plugins[] = array("name" => $plugin["Name"], "version" => $plugin["Version"], "is_active" => $is_active);
        	}
        }
        
?>

<style type="text/css">
table
{
	border:2px solid #cccccc;
	width:900px;
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
}
.title
{
	border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; font-weight:bold;
}
.leftrowtitle
{
	border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px; background-color:#333333; color:#FFFFFF; font-weight:bold;
}
.rightrowtitle
{
	border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px; background-color:#333333;  color:#FFFFFF; font-weight:bold;
}
.leftrowdetails
{
	border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px;
}
.rightrowdetails
{
	border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px;
}	
</style>


<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" class="title">Php Details</td>
</tr>
<tr>
	<td class="leftrowtitle">Variable Name</td>
    <td class="rightrowtitle">Details</td>
</tr>
<tr>
	<td class="leftrowdetails">PHP Version</td>
    <td class="rightrowdetails"><?php echo $php_version;?></td>
</tr>
<tr>
	<td class="leftrowdetails">System</td>
    <td class="rightrowdetails"><?php echo $system_info;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Apache Version</td>
    <td class="rightrowdetails"><?php echo $apache_version;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Server Ip</td>
    <td class="rightrowdetails"><?php echo $server_ip;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Server Name</td>
    <td class="rightrowdetails"><?php echo $servername;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Upload Max Filesize</td>
    <td class="rightrowdetails"><?php echo $upload_max_filesize;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Post Max Size</td>
    <td class="rightrowdetails"><?php echo $post_max_size;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Max Input Vars</td>
    <td class="rightrowdetails"><?php echo $max_input_vars;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Short Tag</td>
    <td class="rightrowdetails"><?php echo $short_open_tag;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Safe Mode</td>
    <td class="rightrowdetails"><?php echo $safe_mode;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Memory Limit</td>
    <td class="rightrowdetails"><?php echo $memory_limit;?></td>
</tr>
<tr>
	<td class="leftrowdetails">MySql Version</td>
    <td class="rightrowdetails"><?php echo $mysql_server_version;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Geo IP</td>
    <td class="rightrowdetails"><?php echo $geoiploaded;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Zip</td>
    <td class="rightrowdetails"><?php echo $ziploaded;?></td>
</tr>
<tr>
	<td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
</tr>
<tr>
	<td colspan="2" class="title">WordPress Details</td>
</tr>
<tr>
	<td class="leftrowtitle">Variable Name</td>
    <td class="rightrowtitle">Details</td>
</tr>
<tr>
	<td class="leftrowdetails">Site Title</td>
    <td class="rightrowdetails"><?php echo $wordpress_sitename;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Tagline</td>
    <td class="rightrowdetails"><?php echo $wordpress_sitedesc;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Version</td>
    <td class="rightrowdetails"><?php echo $wordpress_version;?></td>
</tr>
<tr>
	<td class="leftrowdetails">WordPress address (URL)</td>
    <td class="rightrowdetails"><?php echo $wordpress_wpurl;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Site address (URL)</td>
    <td class="rightrowdetails"><?php echo $wordpress_url;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Admin Email</td>
    <td class="rightrowdetails"><?php echo $wordpress_admin_email;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Charset</td>
    <td class="rightrowdetails"><?php echo $wordpress_charset;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Language</td>
    <td class="rightrowdetails"><?php echo $wordpress_language;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Active theme</td>
    <td class="rightrowdetails"><?php echo $wordpress_templateurl." (".$wordpress_templateurl_version.")" ; ?></td>
</tr>
<tr>
	<td class="leftrowdetails">Debug Mode</td>
    <td class="rightrowdetails"><?php echo $wordpress_debug;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Multisite Enable</td>
    <td class="rightrowdetails"><?php echo $wordpress_multisite;?></td>
</tr>
<tr>
	<td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
</tr>
<tr>
	<td colspan="2" class="title">ARMember Details</td>
</tr>
<tr>
	<td class="leftrowtitle">Variable Name</td>
    <td class="rightrowtitle">Details</td>
</tr>
<tr>
	<td class="leftrowdetails">ARMember Status</td>
    <td class="rightrowdetails"><?php echo $armember_active;?></td>
</tr>
<tr>
	<td class="leftrowdetails">ARMember Version</td>
    <td class="rightrowdetails"><?php echo $armember_version;?></td>
</tr>
<tr>
	<td class="leftrowdetails">Upload Basedir</td>
    <td class="rightrowdetails"><?php echo $upload_dir_path["basedir"];?></td>
</tr>
<tr>
	<td class="leftrowdetails">Upload Baseurl</td>
    <td class="rightrowdetails"><?php echo $upload_dir_path["baseurl"];?></td>
</tr>
<tr>
	<td class="leftrowdetails">Upload Folder Permission</td>
    <td class="rightrowdetails"><?php echo $folderpermission;?></td>
</tr>
<tr>
	<td class="leftrowdetails">ARMember Log Folder Permission</td>
    <td class="rightrowdetails"><?php echo $folderlogpermission;?></td>
</tr>
<tr>
	<td class="leftrowdetails">ARMember Log File Permission</td>
    <td class="rightrowdetails"><?php echo $folderlogfilepermission;?></td>
</tr>

<tr>
	<td colspan="2" class="title">Active Plugin List</td>
</tr>
   
<?php
	foreach($plugins as $myplugin)
	{
	?>
    <tr>
        <td class="leftrowdetails"><?php echo $myplugin['name']; ?></td>
        <td class="rightrowdetails"><?php if($myplugin['is_active'] == 1) {echo "ACTIVE";} else {echo "INACTIVE";}  ?><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".$myplugin['version'].")"; ?></td>
    </tr>
    <?php
	}
?>    
    
    
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
</table>
