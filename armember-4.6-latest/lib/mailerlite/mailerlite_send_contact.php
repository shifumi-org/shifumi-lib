<?php
require_once 'vendor/autoload.php';

global $wpdb, $ARMember, $armemail, $armfname, $armlname, $form_id, $arm_social_feature, $arm_is_social_signup;
$armemail_settings_unser = get_option('arm_email_settings');
$arm_optins_email_settings = maybe_unserialize($armemail_settings_unser);

$mailerliteOpt = (isset($arm_optins_email_settings['arm_email_tools']['mailerlite'])) ? $arm_optins_email_settings['arm_email_tools']['mailerlite'] : array();

$api_key = (isset($mailerliteOpt['api_key'])) ? $mailerliteOpt['api_key'] : '';
$list_id = (isset($mailerliteOpt['list_id'])) ? $mailerliteOpt['list_id'] : '';
$responder_list_id = '';

if($arm_is_social_signup){
    $social_settings = $arm_social_feature->arm_get_social_settings();
    if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'mailerlite') {
        $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
        $status = 1;
        $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id;
    }
}
else
{
    $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
    $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
    $status = (isset($form_settings['email']['mailerlite']['status'])) ? $form_settings['email']['mailerlite']['status'] : 0;

    $responder_list_id = (isset($form_settings['email']['mailerlite']['list_id'])) ? $form_settings['email']['mailerlite']['list_id'] : $list_id;
}


if (!empty($responder_list_id) && !empty($api_key)) {
	
	if ($status == '1' && !empty($responder_list_id))
	{
		
		if (isset($armemail) && strlen($armemail) > 1) 
		{
			
			try 
			{
					$mailerlitegroupsApi = (new \MailerLiteApi\MailerLite($api_key))->groups();
					$mailerlitesubscribersApi = (new \MailerLiteApi\MailerLite($api_key))->subscribers();
					$response = $mailerlitesubscribersApi->search($armemail);

					if(empty($response))
					{

						$arm_member_subscriber = array('email' => $armemail,
				  					 'name' => $armfname,
				  					 'fields' => array( 'last_name' => $armlname)
									);
        				do_action('arm_general_log_entry', 'mailerlite', 'subscriber parameters', 'armember', $arm_member_subscriber);
						
						$arm_addedSubscriber = $mailerlitegroupsApi->addSubscriber($responder_list_id, $arm_member_subscriber);

				        do_action('arm_general_log_entry', 'mailerlite', 'subscriber add response', 'armember', $arm_addedSubscriber);

					}
					else
					{
						
						$arm_member_subscriberData = array( 'fields' => array( 'name' => $armfname, 'last_name' => $armlname ) );

        				do_action('arm_general_log_entry', 'mailerlite', 'update subscriber parameters', 'armember', $arm_member_subscriberData);

						$arm_updsubscriber = $mailerlitesubscribersApi->update($armemail, $arm_member_subscriberData);
				        do_action('arm_general_log_entry', 'mailerlite', 'subscriber update response', 'armember', $arm_updsubscriber);
					}
			}
			catch (Exception $e) 
			{
					
			
			}	

		}
	}
}
?>