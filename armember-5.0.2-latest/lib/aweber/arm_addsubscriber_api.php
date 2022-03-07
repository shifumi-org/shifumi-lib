<?php
require_once('aweber_api.php');
global $wpdb, $ARMember, $armemail, $armfname, $armlname, $form_id, $arm_social_feature, $arm_is_social_signup;;
$email_settings_unser = get_option('arm_email_settings');
$arm_email_settings_data = maybe_unserialize($email_settings_unser);
$aweberOpt = (isset($arm_email_settings_data['arm_email_tools']['aweber'])) ? $arm_email_settings_data['arm_email_tools']['aweber'] : array();
$consumerKey = MEMBERSHIP_AWEBER_CONSUMER_KEY;
$consumerSecret = MEMBERSHIP_AWEBER_CONSUMER_SECRET;
$temp_data = (isset($aweberOpt['temp'])) ? $aweberOpt['temp'] : array();

$list_id = (isset($aweberOpt['list_id'])) ? $aweberOpt['list_id'] : '';
$responder_list_id = '';
if($arm_is_social_signup){
    $social_settings = $arm_social_feature->arm_get_social_settings();
    if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'aweber') {
        $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
        $status = 1;
        $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id ;
    }
}
else
{
    $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
    $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
    $status = (isset($form_settings['email']['aweber']['status'])) ? $form_settings['email']['aweber']['status'] : 0;
    $responder_list_id = (isset($form_settings['email']['aweber']['list_id'])) ? $form_settings['email']['aweber']['list_id'] : $list_id;
}
if (!empty($responder_list_id) && !empty($consumerKey) && !empty($consumerSecret))
{
	if ($status == '1' && !empty($responder_list_id))
	{
		$accessKey = $temp_data['accessToken'];				# put your credentials here
		$accessSecret = $temp_data['accessTokenSecret'];	# put your credentials here
		$account_id = $temp_data['acc_id'];					# put the Account ID here
		$list_id = $responder_list_id;						# put the List ID here
		
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);
		try {
			$account = $aweber->getAccount($accessKey, $accessSecret);
			$listURL = "/accounts/{$account_id}/lists/{$list_id}";
			$list = $account->loadFromUrl($listURL);
			
			# create a subscriber
			$params = array(
				'email' => $armemail,
				'name' => $armfname . " " . $armlname,
			);

			do_action('arm_general_log_entry', 'aweber', 'subscriber parameters', 'armember', $params);

			$subscribers = $list->subscribers;
			$new_subscriber = $subscribers->create($params);

			do_action('arm_general_log_entry', 'aweber', 'subscriber add response', 'armember', $new_subscriber);
			
			# success!
			/*print "A new subscriber was added to the $list->name list!";*/
		} catch (AWeberAPIException $exc) {

			do_action('arm_general_log_entry', 'aweber', 'subscriber error response', 'armember', $exc);
		}
	}
}
?>