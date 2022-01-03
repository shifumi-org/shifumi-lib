<?php
if (!class_exists('ARM_email_settings'))
{
	class ARM_email_settings
	{
		var $templates;
		var $isOptInsFeature;
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;
			$is_opt_ins_feature = get_option('arm_is_opt_ins_feature', 0);
			$this->isOptInsFeature = ($is_opt_ins_feature == '1') ? true : false;
			
			add_action('wp_ajax_arm_submit_email_template', array($this, 'arm_submit_email_template'));
			add_action('wp_ajax_arm_edit_template_data', array($this, 'arm_edit_template_data'));
			add_action('wp_ajax_arm_update_email_template_status', array($this, 'arm_update_email_template_status'));
			add_action('wp_ajax_arm_refresh_aweber', array($this, 'arm_refresh_aweber'));
			add_action('wp_ajax_arm_verify_mailchimp', array($this, 'arm_verify_mailchimp'));
			add_action('wp_ajax_arm_verify_sendinblue', array($this, 'arm_verify_sendinblue'));
			add_action('wp_ajax_arm_verify_mailerlite', array($this, 'arm_verify_mailerlite'));
			add_action('wp_ajax_arm_verify_constant', array($this, 'arm_verify_constant'));
			add_action('wp_ajax_arm_verify_getresponse', array($this, 'arm_verify_getresponse'));
			add_action('wp_ajax_arm_verify_madmimi', array($this, 'arm_verify_madmimi'));
			add_action('wp_ajax_arm_delete_mail_config', array($this, 'arm_delete_mail_config'));
			add_action('wp_ajax_arm_update_opt_ins_settings', array($this, 'arm_update_opt_ins_settings'));

			add_action('wp_ajax_arm_aweber_redirect_url', array($this, 'arm_get_aweber_redirect_url'));
			
			$this->templates = new stdClass;
			$this->templates->new_reg_user_admin = 'new-reg-user-admin';
			$this->templates->new_reg_user_with_payment = 'new-reg-user-with-payment';
			$this->templates->new_reg_user_without_payment = 'new-reg-user-without-payment';
			$this->templates->email_verify_user = 'email-verify-user';			
			$this->templates->account_verified_user = 'account-verified-user';
			$this->templates->change_password_user = 'change-password-user';	
			$this->templates->forgot_passowrd_user = 'forgot-passowrd-user';
			$this->templates->profile_updated_user = 'profile-updated-user';
                        $this->templates->profile_updated_notification_to_admin = 'profile-updated-notification-admin';
			$this->templates->grace_failed_payment = 'grace-failed-payment';
			$this->templates->grace_eot = 'grace-eot';
			$this->templates->failed_payment_admin = 'failed-payment-admin';
                        $this->templates->on_menual_activation = 'on-menual-activation';
		}


		function arm_redirect_aweber_url()
		{
			if(!empty($_REQUEST['arm_redirect_aweber']) && $_REQUEST['arm_redirect_aweber'] == 1)
			{
				global $ARMember, $arm_capabilities_global;
				$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_transactions'], '1');

				require_once( MEMBERSHIP_LIBRARY_DIR .'/aweber/aweber_api.php');
				global $wpdb, $ARMember, $arm_slugs;
				$email_settings_unser = get_option('arm_email_settings');
				$email_setttings = maybe_unserialize($email_settings_unser);
				$email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();

				$arm_return_data['url'] = "";
				$arm_return_data['authorized'] = 0;

				$consumerKey = MEMBERSHIP_AWEBER_CONSUMER_KEY;
				$consumerSecret = MEMBERSHIP_AWEBER_CONSUMER_SECRET;
				$aweber = new AWeberAPI($consumerKey, $consumerSecret);
				if (empty($_COOKIE['accessToken']) || empty($_GET['oauth_token'])) {
				    if (empty($_GET['oauth_token'])) {
				        $callbackUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
				        list($requestToken, $requestTokenSecret) = $aweber->getRequestToken($callbackUrl);
				        setcookie('requestTokenSecret', $requestTokenSecret);
				        setcookie('callbackUrl', $callbackUrl);
				        header("Location: {$aweber->getAuthorizeUrl()}");
				        exit();
				    }
				    $aweber->user->tokenSecret = $_COOKIE['requestTokenSecret'];
				    $aweber->user->requestToken = $_GET['oauth_token'];
				    $aweber->user->verifier = $_GET['oauth_verifier'];
				    list($accessToken, $accessTokenSecret) = $aweber->getAccessToken();
				    setcookie('accessToken', $accessToken);
				    setcookie('accessTokenSecret', $accessTokenSecret);
				    header('Location: '.$_COOKIE['callbackUrl']);
				    exit();
				}

				# set this to true to view the actual api request and response
				$aweber->adapter->debug = false;
				$account = $aweber->getAccount($_COOKIE['accessToken'], $_COOKIE['accessTokenSecret']);
				// $account_lists_data = $account->lists->data;
				// $account_lists_entries = $account_lists_data['entries'];
				$HTTP_METHOD = 'GET';
				$URL = $account->url."/lists";
				$PARAMETERS = array('ws.start'=>0, 'ws.size'=>100);
				$RETURN_FORMAT = array();
				$account_lists_entries = array();
				get_more_pagination :
				$entries = $aweber->adapter->request($HTTP_METHOD, $URL, $PARAMETERS, $RETURN_FORMAT);
				if(isset($entries['entries']) && count($entries['entries']) > 0) {
					foreach ($entries['entries'] as $entry) {
						array_push($account_lists_entries, $entry);
					}
					$PARAMETERS['ws.start'] = $PARAMETERS['ws.start'] + $PARAMETERS['ws.size'];
					if(isset($entries['next_collection_link'])) {
						goto get_more_pagination; 
					}
				}

				$aweberLists = array();
				$i = 0;

				if (!empty($account_lists_entries)) {
					foreach ($account_lists_entries as $offset => $list) {
						if (!empty($list['id'])) {
							$aweberLists[$i]['id'] = $list['id'];
							$aweberLists[$i]['name'] = $list['name'];
							$i++;
						}
					}
				}
				if ($consumerKey != "" && $consumerSecret != "" && $_COOKIE['accessToken'] != "" && $_COOKIE['accessTokenSecret'] != "" && $account->id != "") {
					$temp = array('accessToken' => $_COOKIE['accessToken'], 'accessTokenSecret' => $_COOKIE['accessTokenSecret'], 'acc_id' => $account->id);
					$temp_data = serialize($temp);
					$email_tools['aweber'] = array(
						'consumer_key' => $consumerKey,
						'consumer_secret' => $consumerSecret,
						'temp' => $temp,
						'status' => 1,
						'list' => $aweberLists,
						'list_id' => '',
					);
					$email_setttings['arm_email_tools'] = $email_tools;
					update_option('arm_email_settings', $email_setttings);
				}

				echo "<script>window.opener.location.replace('".admin_url('admin.php?page=' . $arm_slugs->general_settings.'&action=opt_ins_options')."');</script>";
				echo '<script>window.close();</script>';
				exit;
			}			
		}

		function arm_get_email_template($temp_slug)
		{
			global $wpdb,$ARMember;
			$res = $wpdb->get_row("SELECT * FROM `".$ARMember->tbl_arm_email_templates."` WHERE `arm_template_slug`='{$temp_slug}'");
			if (!empty($res)) {
				$res->arm_template_subject = isset($res->arm_template_subject) ? stripslashes($res->arm_template_subject) : '';
				$res->arm_template_content = isset($res->arm_template_content) ? stripslashes($res->arm_template_content) : '';
				return $res;
			}
			return false;
		}
		function arm_update_email_settings()
		{
			$arm_email_from_name = isset($_POST['arm_email_from_name']) ? sanitize_text_field($_POST['arm_email_from_name']) : '';
			$arm_email_from_email = isset($_POST['arm_email_from_email']) ? sanitize_email($_POST['arm_email_from_email']) : '';
			$arm_email_admin_email = isset($_POST['arm_email_admin_email']) ? sanitize_text_field($_POST['arm_email_admin_email']) : '';
			$server = isset($_POST['arm_email_server']) ? sanitize_text_field($_POST['arm_email_server']) : '';

			$arm_mail_authentication = isset($_POST['arm_mail_authentication']) ? $_POST['arm_mail_authentication'] : '0';
			$smtp_mail_server = isset($_POST['arm_mail_server']) ? sanitize_text_field($_POST['arm_mail_server']) : '';
			$smtp_mail_port = isset($_POST['arm_mail_port']) ? sanitize_text_field($_POST['arm_mail_port']) : '';
			$smtp_mail_login_name = isset($_POST['arm_mail_login_name']) ? sanitize_text_field($_POST['arm_mail_login_name']) : '';
			$smtp_mail_password = isset($_POST['arm_mail_password']) ? $_POST['arm_mail_password'] : '';
			$smtp_mail_enc = isset($_POST['arm_smtp_enc']) ? sanitize_text_field($_POST['arm_smtp_enc']) : 'none';
			$old_settings = $this->arm_get_all_email_settings();
			$email_tools = (isset($old_settings['arm_email_tools'])) ? $old_settings['arm_email_tools'] : array();
			$email_tools['aweber']['consumer_key'] = '';
			$email_tools['aweber']['consumer_secret'] = '';
			$email_settings = array(
				'arm_email_from_name' => $arm_email_from_name,
				'arm_email_from_email' => $arm_email_from_email,
                                'arm_email_admin_email' => $arm_email_admin_email,
				'arm_email_server' => $server,
				'arm_mail_server' => $smtp_mail_server,
				'arm_mail_port' => $smtp_mail_port,
				'arm_mail_login_name' => $smtp_mail_login_name,
				'arm_mail_password' => $smtp_mail_password,
				'arm_smtp_enc' => $smtp_mail_enc,
				'arm_email_tools' => $email_tools,
				'arm_mail_authentication' => $arm_mail_authentication,
			);
			update_option('arm_email_settings', $email_settings);
		}
		function arm_update_opt_ins_settings()
		{
			global $ARMember, $arm_capabilities_global;
			$response = array('type' => 'error', 'msg' => __('There is a error while updating opt-ins settings, please try again.', 'ARMember'));
			if (isset($_POST['action']) && $_POST['action'] == 'arm_update_opt_ins_settings') {
				$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
				$email_settings = $this->arm_get_all_email_settings();
				$email_tools = (isset($_POST['arm_email_tools'])) ? $_POST['arm_email_tools'] : array();
				$old_email_tools = (isset($email_settings['arm_email_tools'])) ? $email_settings['arm_email_tools'] : array();
				if (isset($email_tools['aweber'])) {
					$email_tools['aweber']['temp'] = isset($old_email_tools['aweber']['temp']) ? $old_email_tools['aweber']['temp'] : array();
					$email_tools['aweber']['list'] = isset($old_email_tools['aweber']['list']) ? $old_email_tools['aweber']['list'] : array();
					$email_tools['aweber']['consumer_key'] = '';
					$email_tools['aweber']['consumer_secret'] = '';
				}
				if (isset($email_tools['mailchimp'])) {
					$email_tools['mailchimp']['list'] = isset($old_email_tools['mailchimp']['list']) ? $old_email_tools['mailchimp']['list'] : array();
				}
				if (isset($email_tools['constant'])) {
					$email_tools['constant']['list'] = isset($old_email_tools['constant']['list']) ? $old_email_tools['constant']['list'] : array();
				}
                if (isset($email_tools['getresponse'])) {
					$email_tools['getresponse']['list'] = isset($old_email_tools['getresponse']['list']) ? $old_email_tools['getresponse']['list'] : array();
				}
				if (isset($email_tools['madmimi'])) {
					$email_tools['madmimi']['list'] = isset($old_email_tools['madmimi']['list']) ? $old_email_tools['madmimi']['list'] : array();
				}
				if (isset($email_tools['mailerlite'])) {
					$email_tools['mailerlite']['list'] = isset($old_email_tools['mailerlite']['list']) ? $old_email_tools['mailerlite']['list'] : array();
				}
				if (isset($email_tools['sendinblue'])) {
					$email_tools['sendinblue']['list'] = isset($old_email_tools['sendinblue']['list']) ? $old_email_tools['sendinblue']['list'] : array();
				}
				$email_tools = apply_filters('arm_change_optin_settings_before_save', $email_tools);
				
				$email_settings['arm_email_tools'] = arm_array_map($email_tools);
				update_option('arm_email_settings', $email_settings);
                                
                                do_action('arm_update_add_on_opt_in_settings', $_POST);
				$response = array('type' => 'success', 'msg' => __('Opt-ins Settings Saved Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			die();
		}
		function arm_refresh_aweber($consumer_key = '', $consumer_secret = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_settings = $this->arm_get_all_email_settings();
			$email_tools = (isset($email_settings['arm_email_tools'])) ? $email_settings['arm_email_tools'] : array();
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$aweberListHtml = '';
			//$consumer_key = (isset($_POST['consumer_key'])) ? sanitize_text_field($_POST['consumer_key']) : $consumer_key;
			//$consumer_secret = (isset($_POST['consumer_secret'])) ? sanitize_text_field($_POST['consumer_secret']) : $consumer_secret;
			$consumer_key = MEMBERSHIP_AWEBER_CONSUMER_KEY;
			$consumer_secret = MEMBERSHIP_AWEBER_CONSUMER_SECRET;
			if (!empty($consumer_key) && !empty($consumer_secret)) {
				require_once(MEMBERSHIP_LIBRARY_DIR . '/aweber/aweber_api.php');
				
				$tempData = isset($email_tools['aweber']['temp']) ? $email_tools['aweber']['temp'] : array('accessToken' => '', 'accessTokenSecret' => '', 'acc_id' => '');
				$acc_id = $tempData['acc_id'];
				$accessToken = $tempData['accessToken'];
				$accessTokenSecret = $tempData['accessTokenSecret'];
				$aweber = new AWeberAPI($consumer_key, $consumer_secret);
				$aweber->adapter->debug = false;
				$account = $aweber->getAccount($accessToken, $accessTokenSecret);
				$account_lists_data = $account->lists->data;
				$account_lists_entries = $account_lists_data['entries'];
				$aweberLists = array();
				$i = 0;
				if (!empty($account_lists_entries)) {
					foreach ($account_lists_entries as $offset => $list) {
						if (!empty($list['id'])) {
							$aweberLists[$i]['id'] = $list['id'];
							$aweberLists[$i]['name'] = $list['name'];
							$i++;
						}
					}
				}
				if (!empty($aweberLists)) {
					$email_tools['aweber'] = array(
						'consumer_key' => $consumer_key,
						'consumer_secret' => $consumer_secret,
						'temp' => $tempData,
						'status' => 1,
						'list' => $aweberLists,
						'list_id' => '',
					);
					$email_setttings['arm_email_tools'] = arm_array_map($email_tools);
					
					update_option('arm_email_settings', $email_setttings);
					$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
					foreach ($aweberLists as $list) {
						$aweberListHtml .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
					}
				} else {
					$statusRes = array('type' => 'error', 'msg' => __('Aweber List Not Found.', 'ARMember'));
				}
			}
			$statusRes['list'] = $aweberListHtml;
			if (isset($_POST['action']) && $_POST['action'] == 'arm_refresh_aweber') {
				echo json_encode($statusRes);
				exit;
			} else {
				return $statusRes;
			}
		}
		function arm_verify_mailchimp($api_key = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_setttings = $this->arm_get_all_email_settings();
			$email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
			$mailchimpList = '';
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
			if (!empty($api_key)) {
				$mailchimpResp = $this->arm_get_mailchimp_list($api_key);

				do_action('arm_general_log_entry', 'mailchimp', 'verify MailChimp list response', 'armember', $mailchimpResp);

				if ($mailchimpResp['type'] == 'error') {
					$statusRes = array('type' => 'error', 'msg' => $mailchimpResp['message']);
				} else {
					$lists = $mailchimpResp['list'];
					if (count($lists) > 0) {
						$email_tools['mailchimp'] = array(
							'api_key' => $api_key,
							'status' => 1,
							'list' => $lists,
							'list_id' => $lists[0]['id'],
						);
						$email_setttings['arm_email_tools'] = arm_array_map($email_tools);
						update_option('arm_email_settings', $email_setttings);
						$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
						foreach ($lists as $list) {
							$mailchimpList .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
						}
					} else {
						$statusRes = array('type' => 'error', 'msg' => __('Mailchimp List Not Found.', 'ARMember'));
					}
				}
			}
			$statusRes['list'] = $mailchimpList;

			if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_mailchimp') {
				echo json_encode($statusRes);
				exit;
			} else {
				return $statusRes;
			}
		}
		function arm_get_mailchimp_list($api_key = '')
		{
			global $wpdb, $ARMember,$arm_global_settings, $arm_mcapi_version;
			$mailchimpList = array();
			$results = array('type' => 'error', 'message' => __('API Key is not valid.', 'ARMember'));
			if (!empty($api_key)) {
				$arm_mailchimp_dc = substr($api_key,strpos($api_key,'-')+1);
				$mailchimp_url = 'https://'.$arm_mailchimp_dc.'.api.mailchimp.com/'.$arm_mcapi_version.'/lists';
				$mailchimp_url = $arm_global_settings->add_query_arg('apikey', $api_key, $mailchimp_url);
				$mailchimp_url = $arm_global_settings->add_query_arg('count', '500', $mailchimp_url);

				$arm_mailchimp_response = wp_remote_get($mailchimp_url,array(
	                'timeout' => '5000'
	            ));
	            if( is_wp_error($arm_mailchimp_response) ){

	            } else {
	            	$arm_mailchimp_response_list = json_decode($arm_mailchimp_response['body'],true);
	            	$mailchimpLists = $arm_mailchimp_response_list['lists'];

	            	if (count($mailchimpLists) > 0) {
	            		$i = 0;
	            		foreach ($mailchimpLists as $list) {
	            			$mailchimpList[$i]['id'] = $list['id'];
	            			$mailchimpList[$i]['name'] = $list['name'];
	            			$i++;
	            		}
	            		$results = array('type' => 'success', 'message' => '');
	            	}
	            }
			}

			$results['list'] = $mailchimpList;
			return $results;
		}
		function arm_verify_constant($api_key = '', $access_token = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_setttings = $this->arm_get_all_email_settings();
			$email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
			$constantList = '';
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
			$access_token = (isset($_POST['access_token'])) ? sanitize_text_field($_POST['access_token']) : $access_token;
			if (!empty($api_key) && !empty($access_token)) {
				$lists = $this->arm_get_constant_list($api_key, $access_token);

				do_action('arm_general_log_entry', 'constant', 'verify constant list response', 'armember', $lists);

				if (count($lists) > 0) {

					$email_tools['constant'] = array(
						'api_key' => $api_key,
						'access_token' => $access_token,
						'status' => 1,
						'list' => $lists,
						'list_id' => $lists[0]['id'],
					);
					$email_setttings['arm_email_tools'] = arm_array_map($email_tools);
					update_option('arm_email_settings', $email_setttings);
					$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
					foreach ($lists as $list) {
						$constantList .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
					}
				} else {
					$statusRes = array('type' => 'error', 'msg' => __('Constant Contact List Not Found.', 'ARMember'));
				}
			}
			$statusRes['list'] = $constantList;
			if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_constant') {
				echo json_encode($statusRes);
				exit;
			} else {
				return $statusRes;
			}
		}
		function arm_get_constant_list($api_key = '', $access_token = '')
		{
			global $wpdb, $ARMember;
			$constantList = array();
			if (!empty($api_key) && !empty($access_token)) {
				require_once(MEMBERSHIP_LIBRARY_DIR . '/constant_contact/list_contact.php');
				$lists = $cc->getLists($access_token);
				if (count($lists) > 0) {
					$i = 0;
					foreach ($lists as $list) {
						if(!empty($list->id)){
							$constantList[$i]['id'] = $list->id;
							$constantList[$i]['name'] = $list->name;
							$constantList[$i]['status'] = $list->status;
							$constantList[$i]['contact_count'] = $list->contact_count;
						}
						$i++;
					}
				}
			}
			return $constantList;
		}
		function arm_verify_getresponse($api_key = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_setttings = $this->arm_get_all_email_settings();
			$email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
			$getresponseList = '';
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
			if (!empty($api_key) ) {
				$lists = $this->arm_get_getresponse_list($api_key);

				do_action('arm_general_log_entry', 'getresponse', 'verify GetResponse list response', 'armember', $lists);

				if (count($lists) > 0) {
					$email_tools['getresponse'] = array(
						'api_key' => $api_key,
						'status' => 1,
						'list' => $lists,
						'list_id' => $lists[0]['id'],
					);
					$email_setttings['arm_email_tools'] = $email_tools;
					update_option('arm_email_settings', $email_setttings);
					$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
					foreach ($lists as $list) {
						$getresponseList .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
					}
				} else {
					$statusRes = array('type' => 'error', 'msg' => __('GetResponse Contact List Not Found.', 'ARMember'));
				}
			}
			$statusRes['list'] = $getresponseList;
			if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_getresponse') {
				echo json_encode($statusRes);
				exit;
			} else {
				return $statusRes;
			}
		}
        function arm_get_getresponse_list($api_key = '')
        {
        	global $wpdb, $ARMember;
        	$getresponseList = array();
        	$arm_get_response_api_url = "https://api.getresponse.com/v3/campaigns";

        	$arm_get_response_header = array(
        		"X-Auth-Token" => "api-key ".$api_key,
        	);

        	$arm_get_response_body_params = array(
        		'timeout' => 15,
        		'headers' => $arm_get_response_header,
        	);

        	$arm_get_response_data = wp_remote_get($arm_get_response_api_url, $arm_get_response_body_params);

        	if(!is_wp_error($arm_get_response_data))
        	{
        		$arm_get_response_body_data = json_decode($arm_get_response_data['body']);
        		foreach($arm_get_response_body_data as $arm_get_response_body_key => $arm_get_response_body_val)
        		{
        			$arm_tmp_get_response_arr = array(
        				'id'   => $arm_get_response_body_val->name,
        				'name' => $arm_get_response_body_val->name,
        			);
        			array_push($getresponseList, $arm_tmp_get_response_arr);
        		}
        	}

        	return $getresponseList;
        }
        function arm_verify_madmimi($madmimi_email = '', $api_key = '')
        {
        	
            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $email_setttings = $this->arm_get_all_email_settings();

            $email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
            $madmimiList = '';
            $statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $madmimi_email = (isset($_POST['madmimi_email'])) ? sanitize_email($_POST['madmimi_email']) : sanitize_email($madmimi_email);
            $api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
           
            if (!empty($api_key) && !empty($madmimi_email)) {

                $lists = $this->arm_get_madmimi_list($madmimi_email, $api_key);

                do_action('arm_general_log_entry', 'madmimi', 'verify MadMimi list response', 'armember', $lists);

                if (count($lists) > 0) {
                    $email_tools['madmimi'] = array(
                        'api_key' => $api_key,
                        'email' => $madmimi_email,
                        'status' => 1,
                        'list' => $lists,
                        'list_id' => $lists[0]['id'],
                    );
                    $email_setttings['arm_email_tools'] = arm_array_map($email_tools);
                    update_option('arm_email_settings', $email_setttings);
                    $statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
                    foreach ($lists as $list) {
                        $madmimiList .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
                    }
                } else {
                    $statusRes = array('type' => 'error', 'msg' => __('Madmimi List Not Found.', 'ARMember'));
                }
            }
            $statusRes['list'] = $madmimiList;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_madmimi') {
                echo json_encode($statusRes);
                exit;
            } else {
                return $statusRes;
            }
        }
        function arm_get_madmimi_list($madmimi_email = '', $api_key = '')
        {
            global $wpdb, $ARMember;
            $madmimiList = array();
            if (!empty($api_key) && !empty($madmimi_email)) {

                require_once(MEMBERSHIP_LIBRARY_DIR . '/madmimi/MadMimi.class.php');

                $mailer = new ARM_MadMimi($madmimi_email, $api_key);

                $string = $mailer->Lists(false);

                $xml = simplexml_load_string($string);
                
                $xml_array = $this->object2array($xml);
                
                foreach ($xml_array['list'] as $key => $value) {
                    $madmimiList[$key]['name'] = $value['@attributes']['name'];
                    $madmimiList[$key]['id'] = $value['@attributes']['id'];
                }

                
            }
            return $madmimiList;
        }
        function object2array($object) {

		    return @json_decode(@json_encode($object), 1);
		}
		function arm_verify_mailerlite($api_key = '')
        {
        	
            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $email_setttings = $this->arm_get_all_email_settings();

            $email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
            $mailerlitegroups_list = '';
            $statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
           
            if (!empty($api_key)) {

                $mailerlitegroups = $this->arm_get_mailerlite_groups($api_key);

                do_action('arm_general_log_entry', 'mailerlite', 'verify MailerLite Group response', 'armember', $mailerlitegroups);

                if (count($mailerlitegroups) > 0) {
                    $email_tools['mailerlite'] = array(
                        'api_key' => $api_key,
                        'status' => 1,
                        'list' => $mailerlitegroups,
                        'list_id' => $mailerlitegroups[0]['id'],
                    );
                    $email_setttings['arm_email_tools'] = arm_array_map($email_tools);
                    update_option('arm_email_settings', $email_setttings);
                    $statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
                    
                    foreach ($mailerlitegroups as $mailerlitegroup) {

                        $mailerlitegroups_list .= '<li data-label="' . $mailerlitegroup['name'] . '" data-value="' . $mailerlitegroup['id'] . '">' . $mailerlitegroup['name'] . '</li>';
                    }

                } else {
                    $statusRes = array('type' => 'error', 'msg' => __('Mailerlite Group Not Found.', 'ARMember'));
                }
            }
            $statusRes['list'] = $mailerlitegroups_list;
            
            if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_mailerlite') {
                echo json_encode($statusRes);
                exit;
            } else {
                return $statusRes;
            }
        }
        
        function arm_get_mailerlite_groups($api_key = '')
        {
            global $wpdb, $ARMember;
            $mailerliteGroupsList = array();
            if (!empty($api_key)) {
            	
                require_once(MEMBERSHIP_LIBRARY_DIR . '/mailerlite/mailerlite_group_contact.php');

                $mailerlitegroups = $mailerlitegroupsApi->get();

				if (count($mailerlitegroups) > 0) {
					$i = 0;
					foreach ($mailerlitegroups as $mailerlitegroupslist) {
						if(!empty($mailerlitegroupslist->id)){
							$mailerliteGroupsList[$i]['id'] = $mailerlitegroupslist->id;
							$mailerliteGroupsList[$i]['name'] = $mailerlitegroupslist->name;
							$mailerliteGroupsList[$i]['active'] = $mailerlitegroupslist->active;
							$mailerliteGroupsList[$i]['total'] = $mailerlitegroupslist->total;
						}
						$i++;
					}
				}
                
            }
            return $mailerliteGroupsList;
        }
        function arm_verify_sendinblue($api_key = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_setttings = $this->arm_get_all_email_settings();

			$email_tools = (isset($email_setttings['arm_email_tools'])) ? $email_setttings['arm_email_tools'] : array();
			$sendinblueList = '';
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : $api_key;
					
			if (!empty($api_key)) {
				$sendinblueResp = $this->arm_get_sendinblue_list($api_key);

				do_action('arm_general_log_entry', 'sendinblue', 'verify Sendinblue List response', 'armember', $sendinblueResp);

				if ($sendinblueResp['type'] == 'error') {
					$statusRes = array('type' => 'error', 'msg' => $sendinblueResp['message']);
				} else {
					$lists = $sendinblueResp['list'];
					if (count($lists) > 0) {
						$email_tools['sendinblue'] = array(
							'api_key' => $api_key,
							'status' => 1,
							'list' => $lists,
							'list_id' => $lists[0]['id'],
						);
						$email_setttings['arm_email_tools'] = arm_array_map($email_tools);
						update_option('arm_email_settings', $email_setttings);
						$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
						foreach ($lists as $list) {
							$sendinblueList .= '<li data-label="' . $list['name'] . '" data-value="' . $list['id'] . '">' . $list['name'] . '</li>';
						}
					} else {
						$statusRes = array('type' => 'error', 'msg' => __('Sendinblue List Not Found.', 'ARMember'));
					}
				}
			}
			$statusRes['list'] = $sendinblueList;
			if (isset($_POST['action']) && $_POST['action'] == 'arm_verify_sendinblue') {
				echo json_encode($statusRes);
				exit;
			} else {
				return $statusRes;
			}
		}

		function arm_get_sendinblue_list($api_key = '')
		{
			global $wpdb, $ARMember,$arm_global_settings, $arm_sbapi_version;
			$sendinblueList = array();
	     	$results = array('type' => 'error', 'message' => __('API Key is not valid.', 'ARMember'));
		
			if (!empty($api_key)) {
				$sendinblue_url = 'https://api.sendinblue.com/'.$arm_sbapi_version.'/contacts/lists';
				$arg =array(
						'timeout' => '5000',          
		                'headers' => array(
		                    'Content-Type' => 'application/json',
		                    'api-key' => $api_key,
		                )
		             );

				$arm_sendinblue_response = wp_remote_get($sendinblue_url,$arg);

	            if( !is_wp_error($arm_sendinblue_response)) {
	            
	            	$arm_sendinblue_response_list = json_decode($arm_sendinblue_response['body'],true);
	            	$sendinblueLists = !empty($arm_sendinblue_response_list['lists']) ? $arm_sendinblue_response_list['lists'] : array();
	            	if(!empty($sendinblueLists) && is_array($sendinblueLists))
	            	{
		            	if (count($sendinblueLists) > 0) 
		            	{
		            		$i = 0;
		            		foreach ($sendinblueLists as $list) {
		            			$sendinblueList[$i]['id'] = $list['id'];
		            			$sendinblueList[$i]['name'] = $list['name'];
		            			$i++;
		            		}
		            		$results = array('type' => 'success', 'message' => '');
		            	}
		            	else
			            {
			            	$results['message'] = __('Please create atleast one Sendinblue List.', 'ARMember');
			            }
		            }
	            }
			}

			$results['list'] = $sendinblueList;
			return $results;
		}

		function arm_delete_mail_config($id = '')
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$email_settings = $this->arm_get_all_email_settings();
			$email_tools = (isset($email_settings['arm_email_tools'])) ? $email_settings['arm_email_tools'] : array();
			$statusRes = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$id = (isset($_POST['id'])) ? $_POST['id'] : $id;
			if (!empty($id)) {
				if ($id == 'aweber') {
					$email_tools['aweber'] = array(
						'consumer_key' => '',
						'consumer_secret' => '',
						'temp' => array(),
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				if ($id == 'mailchimp') {
					$email_tools['mailchimp'] = array(
						'api_key' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				if ($id == 'constant') {
					$email_tools['constant'] = array(
						'api_key' => '',
						'access_token' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
                                if ($id == 'getresponse') {
					$email_tools['getresponse'] = array(
						'api_key' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				if ($id == 'madmimi') {
					$email_tools['madmimi'] = array(
						'api_key' => '',
						'email' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				if ($id == 'mailerlite') {
					$email_tools['mailerlite'] = array(
						'api_key' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				if ($id == 'sendinblue') {
					$email_tools['sendinblue'] = array(
						'api_key' => '',
						'status' => 0,
						'list' => '',
						'list_id' => '',
					);
				}
				$email_settings['arm_email_tools'] = arm_array_map($email_tools);
				update_option('arm_email_settings', $email_settings);
				$statusRes = array('type' => 'success', 'msg' => __('Settings has been verified.', 'ARMember'));
			}
			die();
		}
		function arm_get_optin_settings()
		{
			global $wpdb, $ARMember;
			$emailTools = array();
			if ($this->isOptInsFeature)
			{
				$email_settings = $this->arm_get_all_email_settings();
				if (isset($email_settings['arm_email_tools']) && !empty($email_settings['arm_email_tools'])) {
					$all_email_tools = $email_settings['arm_email_tools'];
					foreach ($all_email_tools as $tool => $et) {
						if (isset($et['status']) && $et['status'] == '1') {
							$emailTools[$tool] = $et;
						}
					}
				}
				$emailTools = apply_filters('arm_get_optin_settings', $emailTools, $email_settings);
			}
			return $emailTools;
		}
		function arm_get_all_email_settings()
		{
			global $wpdb;
			$email_settings_unser = get_option('arm_email_settings');
			$all_email_settings = maybe_unserialize($email_settings_unser);
			$all_email_settings = apply_filters('arm_get_all_email_settings', $all_email_settings);
			return $all_email_settings;
		}
		function arm_get_single_email_template($template_id, $fields = array())
		{
			global $wpdb, $ARMember;
			if ($template_id == '') {
				return false;
			}
			$select_fields = "*";
			if (is_array($fields) && !empty($fields)) {
				$select_fields = implode(',', $fields);
			}
			$res = $wpdb->get_row("SELECT $select_fields FROM `".$ARMember->tbl_arm_email_templates."` WHERE  `arm_template_id`='$template_id'");
			if (!empty($res)) {
				if (!empty($res->arm_template_subject)) {
					$res->arm_template_subject = stripslashes($res->arm_template_subject);
				}
				if (!empty($res->arm_template_content)) {
					$res->arm_template_content = stripslashes($res->arm_template_content);
				}
				return $res;
			}
			return false;
		}
		function arm_get_all_email_template($field = array())
		{
			global $wpdb, $ARMember;
			if (is_array($field) && !empty($field)) {
				$field_name = implode(',', $field);
				$sql = "SELECT " . $field_name . " FROM `".$ARMember->tbl_arm_email_templates."` ORDER BY `arm_template_id` ASC ";
			} else {
				$sql = "SELECT * FROM `".$ARMember->tbl_arm_email_templates."` ORDER BY `arm_template_id` ASC ";
			}
			$results = $wpdb->get_results($sql);
			if (!empty($results->arm_template_subject)) {
				$results->arm_template_subject = stripslashes($results->arm_template_subject);
			}
			if (!empty($results->arm_template_content)) {
				$results->arm_template_content = stripslashes($results->arm_template_content);
			}
			return $results;
		}
		function arm_edit_template_data()
		{
			global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_manage_communication, $arm_capabilities_global;
			$return = array('status' => 'error');
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
			if (isset($_REQUEST['action']) && isset($_REQUEST['temp_id']) && $_REQUEST['temp_id'] != '') {
				$template_id = intval($_REQUEST['temp_id']);
				$temp_detail = $arm_email_settings->arm_get_single_email_template($template_id);
				if (!empty($temp_detail)) {
					$return = array(
						'status' => 'success',
						'id' => $template_id,
						'popup_heading' => esc_html(stripslashes($temp_detail->arm_template_name)),
						'arm_template_slug' => $temp_detail->arm_template_slug,
						'arm_template_subject' => esc_html(stripslashes($temp_detail->arm_template_subject)),
						'arm_template_content' => stripslashes($temp_detail->arm_template_content),
						'arm_template_status' => $temp_detail->arm_template_status,
					);
					$return = apply_filters('arm_email_attachment_file_outside',$return);
				}
			}
			echo json_encode($return);
			exit;
		}
		function arm_submit_email_template()
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
			$response = array('type'=>'error', 'msg'=>__('Sorry, Something went wrong. Please try again.', 'ARMember'));
			if (!empty($_POST['arm_template_id']) && $_POST['arm_template_id'] != 0)
			{
				$template_id = intval($_POST['arm_template_id']);
				$arm_email_template_subject = (!empty($_POST['arm_template_subject'])) ? sanitize_text_field($_POST['arm_template_subject']) : '';
				$arm_email_template_content = (!empty($_POST['arm_template_content'])) ? $_POST['arm_template_content'] : '';
				$arm_email_template_status = (!empty($_POST['arm_template_status'])) ? intval($_POST['arm_template_status']) : 0;
				$temp_data = array(
					'arm_template_subject' => $arm_email_template_subject,
					'arm_template_content' => $arm_email_template_content,
					'arm_template_status' => $arm_email_template_status
				);
				$temp_data=apply_filters('arm_email_template_save_before',$temp_data,$_POST);
				$update_temp = $wpdb->update($ARMember->tbl_arm_email_templates, $temp_data, array('arm_template_id' => $template_id));
				$response = array('type'=>'success', 'msg'=>__('Email Template Updated Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			exit;
		}
		function arm_update_email_template_status($posted_data=array())
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
			$response = array('type'=>'error', 'msg'=>__('Sorry, Something went wrong. Please try again.', 'ARMember'));
			if (!empty($_POST['arm_template_id']) && $_POST['arm_template_id'] != 0)
			{
				$template_id = intval($_POST['arm_template_id']);
				$arm_email_template_status = (!empty($_POST['arm_template_status'])) ? intval($_POST['arm_template_status']) : 0;
				$temp_data = array(
					'arm_template_status' => $arm_email_template_status,
				);
				$update_temp = $wpdb->update($ARMember->tbl_arm_email_templates, $temp_data, array('arm_template_id' => $template_id));
				$response = array('type'=>'success', 'msg'=>__('Email Template Updated Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			exit;
		}
		function arm_insert_default_email_templates()
		{
			global $wpdb, $ARMember;
			$default_email_template = $this->arm_default_email_templates();
			if (!empty($default_email_template)) {
				foreach ($default_email_template as $slug => $email_template) {
                    $oldTemp = $this->arm_get_email_template($slug);
                    if (!empty($oldTemp)) {
                        continue;
                    } else {
                        $email_template['arm_template_slug'] = $slug;
                        $email_template['arm_template_status'] = '1';
                        $ins = $wpdb->insert($ARMember->tbl_arm_email_templates, $email_template);
                    }
				}
			}
		}
		function arm_default_email_templates()
		{
			$temp_slugs = $this->templates;
			$email_templates = array(
				$temp_slugs->new_reg_user_admin => array(
					'arm_template_name' => 'Signup Completed Notification To Admin',
					'arm_template_subject' => 'New user registration at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>A new user has just registered at {ARM_BLOGNAME}. Here are some basic details of that newly registered user.</p><br><p>Firstname: {ARM_FIRST_NAME}</p><br><p>Lastname: {ARM_LAST_NAME}</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><p>To check further details of this user, please click on the following link:</p><br><p>{ARM_PROFILE_LINK}</p><br><br><p>Thank You</p><br><p>{ARM_BLOGNAME}</p>',
				),
				$temp_slugs->new_reg_user_with_payment => array(
					'arm_template_name' => 'Signup Completed (With Payment) Notification To User',
					'arm_template_subject' => 'Confirmation of your membership at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Thank you for subscribing to the {ARM_PLAN} at {ARM_BLOGNAME}.</p><br><p>You can review and edit your membership details here:</p><br><p>{ARM_PROFILE_LINK}</p><br><p>Here is your latest payment information:</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Plan Name: {ARM_PLAN}</p><br><p>Plan Type: {ARM_PAYMENT_TYPE}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Transaction Id: {ARM_TRANSACTION_ID}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->new_reg_user_without_payment => array(
					'arm_template_name' => 'Signup Completed (Without Payment) Notification To User',
					'arm_template_subject' => 'Confirmation of your membership at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Thank you for subscribing to the {ARM_PLAN} at {ARM_BLOGNAME}.</p><br><p>You can review and edit your membership details here:</p><br><p>{ARM_PROFILE_LINK}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->email_verify_user => array(
					'arm_template_name' => 'Email Verification',
					'arm_template_subject' => 'Email verification at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>You must confirm/validate your email account before logging in.</p><br><p>Please click on the following link to  activate your account:</p><br><p>{ARM_VALIDATE_URL}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->account_verified_user => array(
					'arm_template_name' => 'Email Verified',
					'arm_template_subject' => 'Email verified successfully at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your account is now verified at {ARM_BLOGNAME}.</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->change_password_user => array(
					'arm_template_name' => 'Change Password',
					'arm_template_subject' => 'Your password has been changed at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Password has been changed.</p><br><p>To login please fill out your credentials on:</p><br><p>{ARM_LOGIN_URL}</p><br><p>Your Username: {ARM_USERNAME}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->forgot_passowrd_user => array(
					'arm_template_name' => 'Forgot Password',
					'arm_template_subject' => 'Reset password request at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Someone requested that the password be reset for the following account: {ARM_BLOG_URL}</p><br><p>Username: {ARM_USERNAME},</p><br><p>If this was a mistake, just ignore this email and nothing will happen.</p><br><p>To reset your password, visit the following address:{ARM_RESET_PASSWORD_LINK}</p><br><p>If you have any problems, please contact us at {ARM_ADMIN_EMAIL}.</p>',
				),
				$temp_slugs->profile_updated_user => array(
					'arm_template_name' => 'Profile Updated',
					'arm_template_subject' => 'Your account has been updated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your account has been updated.</p><br><p>To visit your profile page follow the next link:</p><br>
<p>{ARM_PROFILE_LINK}</p><br><p>Have a nice day!</p>',
				),
                                $temp_slugs->profile_updated_notification_to_admin => array(
					'arm_template_name' => 'Profile Updated Notification To Admin',
					'arm_template_subject' => 'Account of {ARM_USERNAME} has been updated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>An account has been updated at {ARM_BLOGNAME}. Here are some basic details of that updated user.</p><br><p>Firstname: {ARM_FIRST_NAME}</p><br><p>Lastname: {ARM_LAST_NAME}</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><br><p>Thank You</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->grace_failed_payment => array(
					'arm_template_name' => 'Grace Period For Failed Payment',
					'arm_template_subject' => 'Reminder for failed payment at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Unfortunately your recurring payment for {ARM_PLAN} at {ARM_BLOGNAME} has not succeeded for some reason.</p><br><p>Here are some payment details:</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Please contact the payment service provider about this.</p><br><p><strong>Note: </strong>If you do not take appropriate action within {ARM_GRACE_PERIOD_DAYS} days, than any current membership may lapse.</p><br><p>If you have any further queries, feel free to contact us at {ARM_BLOGNAME}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->grace_eot => array(
					'arm_template_name' => 'User Enters Grace Period Notification',
					'arm_template_subject' => 'Reminder for membership expiration at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your {ARM_PLAN} membership has just expired.</p><br><p>But you can still access our website without any problem,</p><br><p>If you want to renew/update your membership plan, than please click on the following link:</p><br><p>{ARM_BLOG_URL}</p><br><p><strong>Note: </strong>If you do not renew/change your membership within {ARM_GRACE_PERIOD_DAYS} days, than the relevant action will be performed by system.</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->failed_payment_admin => array(
					'arm_template_name' => 'Failed Payment Notification To Admin',
					'arm_template_subject' => 'Reminder for failed payment at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>This is a reminder that the following member\'s recurring payment for {ARM_PLAN} membership has failed for some reason at {ARM_BLOGNAME}</p><br><p>Here are some details.</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Please take appropriate action.</p><br><p>Thank You.</p><br>',
				),
                                $temp_slugs->on_menual_activation => array(
					'arm_template_name' => 'Manual User Activation',
					'arm_template_subject' => 'Your account has been activated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Account has been activated.</p><br><p> Please click on the following link:</p><br><p>{ARM_BLOG_URL}</p><br><p>Have a nice day!</p>',
				),
			);
			$email_templates = apply_filters('arm_default_email_templates', $email_templates);
			return $email_templates;
		}
	}
}
global $arm_email_settings;
$arm_email_settings = new ARM_email_settings();