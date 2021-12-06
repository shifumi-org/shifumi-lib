<?php
global $wpdb, $ARMember, $armemail, $armfname, $armlname, $form_id, $arm_social_feature, $arm_is_social_signup;
$armemail_settings_unser = get_option('arm_email_settings');
$arm_optins_email_settings = maybe_unserialize($armemail_settings_unser);
$madmimiOpt = (isset($arm_optins_email_settings['arm_email_tools']['madmimi'])) ? $arm_optins_email_settings['arm_email_tools']['madmimi'] : array();
$api_key = (isset($madmimiOpt['api_key'])) ? $madmimiOpt['api_key'] : '';
$madmimi_email = (isset($madmimiOpt['email'])) ? $madmimiOpt['email'] : '';
$list_id = (isset($madmimiOpt['list_id'])) ? $madmimiOpt['list_id'] : '';
$responder_list_id = '';

if($arm_is_social_signup){
    $social_settings = $arm_social_feature->arm_get_social_settings();
    if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'madmimi') {
        $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
        $status = 1;
        $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id;
    }
}
else
{
    $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
    $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
    $status = (isset($form_settings['email']['madmimi']['status'])) ? $form_settings['email']['madmimi']['status'] : 0;
    $responder_list_id = (isset($form_settings['email']['madmimi']['list_id'])) ? $form_settings['email']['madmimi']['list_id'] : $list_id;
}
if (!empty($responder_list_id) && !empty($api_key) && !empty($madmimi_email)) {
	if ($status == '1' && !empty($responder_list_id))
	{
		if($armemail==""){ return "No email address provided"; }
		if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $armemail)) {return "Email address is invalid";}
		$args = array(
	    'timeout'     => 15,
	    'redirection' => 15,
	    'headers'     => "Accept: application/json",
		);
		$url = "https://api.madmimi.com/audience_lists/$responder_list_id/add?email=$armemail&name=$armfname&username=$madmimi_email&api_key=$api_key";

        do_action('arm_general_log_entry', 'madmimi', 'subscriber parameters', 'armember', $url);

		$response = wp_remote_post( $url, $args );

        do_action('arm_general_log_entry', 'madmimi', 'subscriber add response', 'armember', $response);

		if( is_wp_error( $response ) ) {
		    return false;
		} else {

		    if ( $response['response']['code'] == 200 ) {
		        return true;
		    } else {
		        return false;
		    }
		}
	}
}
?>