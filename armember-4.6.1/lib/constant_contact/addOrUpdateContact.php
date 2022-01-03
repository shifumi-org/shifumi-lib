<?php
require_once 'src/Ctct/autoload.php';
use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;

global $wpdb, $ARMember, $armemail, $armfname, $armlname, $form_id, $arm_social_feature, $arm_is_social_signup;
$armemail_settings_unser = get_option('arm_email_settings');
$arm_optins_email_settings = maybe_unserialize($armemail_settings_unser);
$constantOpt = (isset($arm_optins_email_settings['arm_email_tools']['constant'])) ? $arm_optins_email_settings['arm_email_tools']['constant'] : array();
$api_key = (isset($constantOpt['api_key'])) ? $constantOpt['api_key'] : '';
$access_token = (isset($constantOpt['access_token'])) ? $constantOpt['access_token'] : '';
$list_id = (isset($constantOpt['list_id'])) ? $constantOpt['list_id'] : '';
$responder_list_id = '';
if($arm_is_social_signup){
    $social_settings = $arm_social_feature->arm_get_social_settings();
    if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'constant') {
        $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
        $status = 1;
        $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id;
    }
}
else
{
    $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
    $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
    $status = (isset($form_settings['email']['constant']['status'])) ? $form_settings['email']['constant']['status'] : 0;
    $responder_list_id = (isset($form_settings['email']['constant']['list_id'])) ? $form_settings['email']['constant']['list_id'] : $list_id;
}
if (!empty($responder_list_id) && !empty($api_key) && !empty($access_token)) {
	if ($status == '1' && !empty($responder_list_id))
	{
		define("APIKEY", $api_key);
		define("ACCESS_TOKEN", $access_token);
		$cc = new ConstantContact(APIKEY);
		/*attempt to fetch lists in the account, catching any exceptions and printing the errors to screen*/
		try {
			$lists = $cc->getLists(ACCESS_TOKEN);
			foreach ($lists as $list) {
				if ($list->id == $list_id1) {
					$list_id = $list->id;
				}
			}
		} catch (CtctException $ex) {
			foreach ($ex->getErrors() as $error) {
				/*print_r($error);*/
			}
		}
		/*check if the form was submitted*/
		if (isset($armemail) && strlen($armemail) > 1) {
			$action = "Getting Contact By Email Address";
			try {
				/*check to see if a contact with the email addess already exists in the account*/
				$response = $cc->getContactByEmail(ACCESS_TOKEN, $armemail);
				/*create a new contact if one does not exist*/
				if (empty($response->results)) {
					$action = "Creating Contact";
					$contact = new Contact();
					$contact->addEmail($armemail);
					$contact->addList($responder_list_id);
					$contact->first_name = $armfname;
					$contact->last_name = $armlname;

					do_action('arm_general_log_entry', 'constant', 'subscriber parameters', 'armember', $contact);

					$returnContact = $cc->addContact(ACCESS_TOKEN, $contact);
					/*update the existing contact if address already existed*/

					do_action('arm_general_log_entry', 'constant', 'subscriber add response', 'armember', $returnContact);
				} else {
					$action = "Updating Contact";
					$contact = $response->results[0];
					$contact->addList($responder_list_id);
					$contact->first_name = $armfname;
					$contact->last_name = $armlname;

					do_action('arm_general_log_entry', 'constant', 'subscriber update parameters', 'armember', $contact);

					$returnContact = $cc->updateContact(ACCESS_TOKEN, $contact);

					do_action('arm_general_log_entry', 'constant', 'subscriber update response', 'armember', $returnContact);
				}
				/*catch any exceptions thrown during the process and print the errors to screen*/
			} catch (CtctException $ex) {
				/* echo '<span class="label label-important">Error '.$action.'</span>';
				  echo '<div class="container alert-error"><pre class="failure-pre">';
				  print_r($ex->getErrors());
				  echo '</pre></div>';
				  die(); */
				  do_action('arm_general_log_entry', 'constant', 'subscriber error response', 'armember', $ex->getErrors());
			}
		}
	}
}
?>