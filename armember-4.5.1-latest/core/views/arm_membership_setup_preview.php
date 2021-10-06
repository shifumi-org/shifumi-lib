<?php

global $ARMember, $arm_ajaxurl, $arm_membership_setup;

$arm_transient_uniq_id = $_REQUEST['_wpnonce'];

$setupData = get_transient('arm_preview_transient_'.$arm_transient_uniq_id);

$setupData = !empty($setupData) ? maybe_unserialize($setupData) : '';
$setupData = !empty($setupData) ? $setupData['setup_data'] : '';

//$setupData = isset($_REQUEST['setup_data']) ? maybe_serialize($_REQUEST['setup_data']) : '';
$browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);

if (isset($browser_info) and $browser_info != "") 
{
    if ($browser_info['name'] == 'Internet Explorer' || $browser_info['name'] == 'Edge') 
    {
        if(empty($setupData) && isset($_COOKIE['arm_setupform_preview1']) && isset($_COOKIE['arm_setupform_preview2']) && isset($_COOKIE['arm_setupform_preview3']) && isset($_COOKIE['arm_setupform_preview4']) && isset($_COOKIE['arm_setupform_preview5']) )
        {
            $setupdataPreview =  $_COOKIE['arm_setupform_preview1'].$_COOKIE['arm_setupform_preview2'].$_COOKIE['arm_setupform_preview3'].$_COOKIE['arm_setupform_preview4'].$_COOKIE['arm_setupform_preview5'];
            
            parse_str($setupdataPreview,$setupdataPreviewstr);
            
            $setupData = isset($setupdataPreviewstr['setup_data']) ? maybe_serialize($setupdataPreviewstr['setup_data']) : '';
        }
    }
}

$ARMember->set_global_javascript_variables();

$ARMember->set_js();
$ARMember->set_front_css();
$ARMember->enqueue_angular_script();

wp_print_styles('arm_front_css');
wp_print_styles('arm_form_style_css');
wp_print_styles('arm_fontawesome_css');
wp_print_styles('arm_bootstrap_all_css');
?>
<script type='text/javascript'>
/* <![CDATA[ */
var ajaxurl = "<?php echo $arm_ajaxurl;?>";
var armurl = "<?php echo MEMBERSHIP_URL;?>";
var armviewurl = "<?php echo MEMBERSHIP_VIEWS_URL;?>";
var imageurl = "<?php echo MEMBERSHIP_IMAGES_URL;?>";
/* ]]> */
</script>
<?php
wp_print_scripts('jquery'); 
wp_print_scripts('arm_common_js');
wp_print_scripts('arm_admin_file_upload_js');
wp_print_scripts('arm_bootstrap_js');
wp_print_scripts('arm_bootstrap_datepicker_with_locale');
?>

<!--* Angular CSS & JS *-->
<?php
wp_print_styles('arm_angular_material_css');

wp_print_scripts('arm_angular_with_material');
wp_print_scripts('arm_form_angular');
?>
<style type="text/css">
    body{
        padding:0;
        margin:0;
    }
    .arm_setup_form_container{
        height: 500px;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 10px 30px 40px;
        box-sizing: border-box;
    }
    .arm_setup_form_container form{
        margin: 0 auto;
    }
</style>
<?php
echo $arm_membership_setup->arm_setup_shortcode_func(array(
    'preview' => 'true',
    'setup_data' => $setupData,
));
