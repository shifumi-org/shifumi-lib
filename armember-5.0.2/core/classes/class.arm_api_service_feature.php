<?php
if (!class_exists('arm_api_service_feature'))
{
	class arm_api_service_feature
	{
		var $isAPIServiceFeature;
		function __construct()
		{
			global $arm_api_common_msg;
			$is_api_service_feature = get_option('arm_is_api_service_feature', 0);
			$this->isAPIServiceFeature = ($is_api_service_feature == '1') ? true : false;

			if(!empty($is_api_service_feature))
			{
				add_action('wp_ajax_arm_generate_api_key', array($this, 'arm_generate_api_key'));
				add_action('rest_api_init', array($this, 'arm_rest_api_endpoint'));
				$arm_api_common_msg = array(
					'arm_default' => __('Sorry, Something went wrong. Please try again.', 'ARMember'),
					'arm_api_enable' => __('This API has been disabled by administrator.', 'ARMember'),
					'arm_authentication' => __('Sorry, you are not allowed to do that.', 'ARMember'),
					'arm_service_enable' => __('This service has been disabled by administrator.', 'ARMember'),
					'arm_no_params' => __('There was not passed parameter\'s value of', 'ARMember'),
					'arm_no_member_id' => __('There was no member found with an id of', 'ARMember'),
					'arm_no_plans' => __('There were no plans found.', 'ARMember'),
					'arm_no_plan' => __('There was no plan found with an member id of', 'ARMember'),
					'arm_no_plan_id' => __('There was no plan found with an id of', 'ARMember'),
					'arm_plan_added' => __('Membership plan has been added successfully.', 'ARMember'),
					'arm_trans_added' => __('Transaction has been added successfully.', 'ARMember'),
					'arm_trans_not_added' => __('Transaction has been not added successfully. Try again later.', 'ARMember'),
					'arm_success' => __('Successfully response result.', 'ARMember'),
				);
			}
		}
		function arm_generate_api_key()
		{
			global $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
			$api_key = '';
			if (function_exists('arm_generate_random_code'))
			{
				$api_key = arm_generate_random_code(30);
			}
			else
			{
				$key_char = array();
				$key_char[] = array('count' => 10, 'char' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
				$key_char[] = array('count' => 6, 'char' => '0123456789');
				$temp_array = array();
				foreach ($key_char as $char_set)
				{
					for ($i = 0; $i < $char_set['count']; $i++)
					{
						$temp_array[] = $char_set['char'][rand(0, strlen($char_set['char']) - 1)];
					}
				}
				shuffle($temp_array);
				$api_key = implode('', $temp_array);
			}
			if (isset($_POST['action']) && $_POST['action'] == 'arm_generate_api_key')
			{
				$response = array('arm_api_key' => $api_key);
				echo json_encode($response);
				die();
			}
			else
			{
				return $api_key;
			}
		}
		function arm_rest_api_endpoint()
		{
			register_rest_route( 'armember/v1', '/arm_memberships',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_memberships'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_membership_details',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_membership_details'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_member_details',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_member_details'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_member_memberships',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_member_memberships'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_member_paid_posts',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_member_paid_posts'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_member_payments',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_member_payments'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_member_paid_post_payments',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_get_member_paid_post_payments'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_check_coupon_code',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_check_coupon_code'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_add_member_membership',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_add_member_membership'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_add_member_transaction',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_add_member_transaction'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_cancel_member_membership',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_cancel_member_membership'),
					'permission_callback' =>  function() { return ''; },
				)
			);
			register_rest_route( 'armember/v1', '/arm_check_member_membership',
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'arm_check_member_membership'),
					'permission_callback' =>  function() { return ''; },
				)
			);
		}
		function arm_api_autontication( $arm_api_request_key )
		{
			global $arm_global_settings, $arm_api_service_feature, $arm_api_common_msg;
			$result = false;
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			if($arm_api_service_feature->isAPIServiceFeature)
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();
				$arm_api_key = !empty($general_settings['arm_api_service_security_key']) ? $general_settings['arm_api_service_security_key'] : '';
				if ($arm_api_key == $arm_api_request_key)
				{
					$result = true;
					$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_authentication'], 'response' => array('result' => $result));
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_api_enable'], 'response' => array('result' => $result));
			}
			return $response;
		}
		function arm_get_memberships( $data )
		{
			global $arm_global_settings, $arm_api_service_feature, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if(!empty($general_settings['arm_list_membership_plans']))
				{
					$form_result = $arm_subscription_plans->arm_get_all_subscription_plans();
					if (!empty($form_result))
					{
						foreach($form_result as $planData)
						{
							$planObj = new ARM_Plan();
							$planObj->init((object) $planData);
							$plan = array();
							$plan['plan_id'] = $planData['arm_subscription_plan_id'];
							$plan['plan_name'] = esc_html(stripslashes($planObj->name));
							$plan['plan_description'] = esc_html(stripslashes($planObj->description));
							$result[] = $plan;
						}
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plans'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_membership_details( $data )
		{
			global $arm_global_settings, $arm_api_service_feature, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status']) {
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_membership_plan_details']))
				{
					if (!empty($_REQUEST['arm_plan_id']))
					{
						$plan_id = $_REQUEST['arm_plan_id'];
						$plan_data = $arm_subscription_plans->arm_get_subscription_plan($plan_id);
						if ($plan_data !== FALSE && !empty($plan_data))
						{
							$result['plan_name'] = esc_html(stripslashes($plan_data['arm_subscription_plan_name']));
							$result['plan_description'] = $plan_data['arm_subscription_plan_description'];
							$result['plan_status'] = $plan_data['arm_subscription_plan_status'];
							$result['plan_role'] = $plan_data['arm_subscription_plan_role'];
							$result['subscription_type'] = $plan_data['arm_subscription_plan_type'];

							if (!empty($plan_data['arm_subscription_plan_options']))
							{
								$plan_options = $plan_data['arm_subscription_plan_options'];
								$plan_options["payment_type"] = !empty($plan_options["payment_type"]) ? $plan_options["payment_type"] : 'one_time';
								$plan_options["recurring"]["type"] = !empty($plan_options["recurring"]["type"]) ? $plan_options["recurring"]["type"] : 'D';
								$plan_options["trial"]["type"] = !empty($plan_options["trial"]["type"]) ? $plan_options["trial"]["type"] : 'D';
								$result['plan_options'] = $plan_options;
							}
							$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
							wp_send_json($response);
						}
						else
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan_id'].' '.$plan_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_plan_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_member_details( $data )
		{
			global $ARMember, $arm_global_settings, $arm_members_class, $arm_member_forms, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_details']))
				{
					if (!empty($_REQUEST['arm_user_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$arm_metakeys = isset($_REQUEST['arm_metakeys']) ? $_REQUEST['arm_metakeys'] : '';
						$date_format = $arm_global_settings->arm_get_wp_date_format();
						$dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
						$user = get_user_by('id', $user_id);
						if (empty($user)) {
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$user_metas = get_user_meta($user_id);
						$result['id'] = $user_id;
						$result['username'] = $user->user_login;
						$result['email'] = $user->user_email;
						$result['display_name'] = $user->display_name;
						$result['first_name'] = $user->first_name;
						$result['last_name'] = $user->last_name;
						$result['status'] = strip_tags($arm_members_class->armGetMemberStatusText($user_id));
						$result['Registretion_date'] = date_i18n($date_format, strtotime($user->user_registered));

						$exclude_keys = array( 'first_name', 'last_name', 'user_login', 'user_email', 'user_pass', 'repeat_pass', 'arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section', 'repeat_email', 'social_fields', 'avatar', 'profile_cover','arm_captcha' );
						if (!empty($dbFormFields) && !empty($arm_metakeys))
						{
							foreach ($dbFormFields as $meta_key => $field)
							{
								$field_options = maybe_unserialize($field);
								$arm_metakeys_arr = explode(',', $arm_metakeys);
								$meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
								$field_id = $meta_key . arm_generate_random_code();
								if (!in_array($meta_key, $exclude_keys) && !in_array($field_options['type'], array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email')) && in_array($meta_key, $arm_metakeys_arr))
								{

									if (!empty($user->$meta_key))
									{
										if ($field_options['type'] == 'file')
										{
											$file_name = basename($user->$meta_key);
											if ($user->$meta_key != '')
											{
												$result[$meta_key] = $user->$meta_key;
											}
										}
										else if (in_array($field_options['type'], array('radio', 'checkbox', 'select')))
										{
											$user_meta_detail = $user->$meta_key;
											$main_array = array();
											$options = $field_options['options'];
											$value_array = array();
											foreach ($options as $arm_key => $arm_val)
											{
												if (strpos($arm_val, ":") != false)
												{
													$exp_val = explode(":", $arm_val);
													$exp_val1 = $exp_val[1];
													$value_array[$exp_val[0]] = $exp_val[1];
												}
												else
												{
													$value_array[$arm_val] = $arm_val;
												}
											}
											$user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
											if (!empty($value_array))
											{
												if (is_array($user_meta_detail))
												{
													foreach ($user_meta_detail as $u)
													{
														foreach ($value_array as $arm_key => $arm_val)
														{
															if ($u == $arm_val)
															{
																array_push($main_array,$arm_key);
															}
														}
													}
													$user_meta_detail = @implode(', ', $main_array);
													$result[$meta_key] = $user_meta_detail;
												}
												else
												{
													$exp_val = array();
													if (!empty($exp_val))
													{
														foreach ($exp_val as $u)
														{
															if (in_array($u, $value_array))
															{
																array_push($main_array,array_search($u,$value_array));
															}
														}
														$user_meta_detail = @implode(', ', $main_array);
														$result[$meta_key] = $user_meta_detail;
													}
													else
													{
														if (in_array($user_meta_detail, $value_array))
														{
															$result[$meta_key] = array_search($user_meta_detail,$value_array);
														}
														else
														{
															$result[$meta_key] = $user_meta_detail;
														}
													}
												}
											}
											else
											{
												if (is_array($user_meta_detail))
												{
													$user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
													$user_meta_detail = @implode(', ', $user_meta_detail);
													$result[$meta_key] = $user_meta_detail;
												}
												else
												{
													$result[$meta_key] = $user_meta_detail;
												}
											}
										}
										else
										{
											$user_meta_detail = $user->$meta_key;

											$pattern = '/^(date\_(.*))/';

											if(preg_match($pattern, $meta_key)){
												$user_meta_detail  =  date_i18n($date_format, strtotime($user_meta_detail));
											}

											if (is_array($user_meta_detail))
											{
												$user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
												$user_meta_detail = @implode(', ', $user_meta_detail);
												$result[$meta_key] = $user_meta_detail;
											}
											else
											{
												$result[$meta_key] = $user_meta_detail;
											}
										}
									}
									else
									{
										$result[$meta_key] = "";
									}
								}
							}
						}
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_user_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_member_memberships( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$arm_page = isset($_REQUEST['arm_page']) ? $_REQUEST['arm_page'] : 1;
			$arm_perpage = isset($_REQUEST['arm_perpage']) ? $_REQUEST['arm_perpage'] : 5;
			$is_paid_post = 0;
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_memberships']))
				{
					if (!empty($_REQUEST['arm_user_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_id_name_array = $arm_subscription_plans->arm_member_memberships($user_id, $is_paid_post, $arm_page, $arm_perpage);
						$result = $plan_id_name_array;
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_user_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_member_paid_posts( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$arm_page = isset($_REQUEST['arm_page']) ? $_REQUEST['arm_page'] : 1;
			$arm_perpage = isset($_REQUEST['arm_perpage']) ? $_REQUEST['arm_perpage'] : 5;
			$is_paid_post = 1;
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_paid_posts']))
				{
					if (!empty($_REQUEST['arm_user_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_id_name_array = $arm_subscription_plans->arm_member_memberships($user_id, $is_paid_post, $arm_page, $arm_perpage);
						$result = $plan_id_name_array;
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_user_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_member_payments( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$arm_page = isset($_REQUEST['arm_page']) ? $_REQUEST['arm_page'] : 1;
			$arm_perpage = isset($_REQUEST['arm_perpage']) ? $_REQUEST['arm_perpage'] : 5;
			$is_paid_post = 0;
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_payments']))
				{
					if (!empty($_REQUEST['arm_user_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_id_name_array = $arm_subscription_plans->arm_member_payments($user_id, $is_paid_post, $arm_page, $arm_perpage);
						$result = $plan_id_name_array;
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_user_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_get_member_paid_post_payments( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$arm_page = isset($_REQUEST['arm_page']) ? $_REQUEST['arm_page'] : 1;
			$arm_perpage = isset($_REQUEST['arm_perpage']) ? $_REQUEST['arm_perpage'] : 5;
			$is_paid_post = 1;
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_paid_post_payments']))
				{
					if (!empty($_REQUEST['arm_user_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_id_name_array = $arm_subscription_plans->arm_member_payments($user_id, $is_paid_post, $arm_page, $arm_perpage);
						$result = $plan_id_name_array;
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' arm_user_id.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_check_coupon_code( $data )
		{
			global $arm_global_settings, $arm_manage_coupons, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_check_coupon_code']))
				{
					if (!empty($_REQUEST['coupon_code']) && !empty($_REQUEST['plan_id']))
					{
						$coupon_apply = $arm_manage_coupons->arm_apply_coupon_code();
						$success = $coupon_apply['status'];
						$message = $coupon_apply['message'];
						unset($coupon_apply['status']);
						unset($coupon_apply['message']);
						$response = array('status' => $success, 'message' => $message, 'response' => array('result' => $coupon_apply));
						wp_send_json($response);
					}
					else
					{
						$parameters = '';
						if (empty($_REQUEST['coupon_code']))
						{
							$parameters .= 'coupon_code';
						}
						if (empty($_REQUEST['plan_id']))
						{
							if (!empty($parameters))
							{
								$parameters .= ',';
							}
							$parameters .= 'plan_id';
						}
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' '.$parameters.'.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_add_member_membership( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_add_membership']))
				{
					if (!empty($_REQUEST['arm_user_id']) && !empty($_REQUEST['arm_plan_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$new_plan_id = $_REQUEST['arm_plan_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_info = new ARM_Plan($new_plan_id);
						if (!$plan_info->exists())
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan_id'].' '.$new_plan_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}

						if ($plan_info->is_recurring())
						{
							$defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
							$userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
							$userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
							$newPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
							$newPlanData['arm_payment_mode'] = 'manual_subscription';

							update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);
						}
						$arm_subscription_plans->arm_update_user_subscription($user_id, $new_plan_id, '', true);

						$result = array(1);
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_plan_added'], 'response' => array('result' => $result));
						wp_send_json($response);
					}
					else
					{
						$parameters = '';
						if (empty($_REQUEST['arm_user_id']))
						{
							$parameters .= 'arm_user_id';
						}
						if (empty($_REQUEST['arm_plan_id']))
						{
							if (!empty($parameters))
							{
								$parameters .= ',';
							}
							$parameters .= 'arm_plan_id';
						}
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' '.$parameters.'.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_add_member_transaction( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg, $arm_transaction, $arm_payment_gateways, $arm_manage_coupons;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_create_transaction']))
				{
					if (!empty($_REQUEST['arm_user_id']) && !empty($_REQUEST['plan_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$plan_id = $_REQUEST['plan_id'];
						$trans_id = !empty($_REQUEST['arm_trans_id']) ? $_REQUEST['arm_trans_id'] : '';
						$gateway = !empty($_REQUEST['gateway']) ? $_REQUEST['gateway'] : 'manual';
						$status = !empty($_REQUEST['arm_status']) ? $_REQUEST['arm_status'] : 'pending';
						$amount = !empty($_REQUEST['arm_amount']) ? $_REQUEST['arm_amount'] : 0;
						$total = !empty($_REQUEST['arm_total']) ? $_REQUEST['arm_total'] : 0;
						$tax_amount = !empty($_REQUEST['arm_tax_amount']) ? $_REQUEST['arm_tax_amount'] : 0;
						$coupon_code = !empty($_REQUEST['coupon_code']) ? $_REQUEST['coupon_code'] : '';
						$is_post_payment = !empty($_REQUEST['is_post_payment']) ? $_REQUEST['is_post_payment'] : '0';
						$is_gift_payment = !empty($_REQUEST['is_gift_payment']) ? $_REQUEST['is_gift_payment'] : '0';
						$paid_post_id = !empty($_REQUEST['arm_post_id']) ? $_REQUEST['arm_post_id'] : '0';
						$currency = $arm_payment_gateways->arm_get_global_currency();
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan = new ARM_Plan($plan_id);
						if (!$plan->exists())
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan_id'].' '.$plan_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$first_name = isset($user->first_name) ? $user->first_name : '';
						$last_name = isset($user->last_name) ? $user->last_name : '';
						$user_email = isset($user->user_email) ? $user->user_email : '';
						$coupon_discount = 0;
						$discount_type = '';
						$arm_coupon_on_each_subscriptions = '0';
						$payment_cycle = 0;
						$recurring_data = $plan->prepare_recurring_data($payment_cycle);
						$arm_is_trial = 0;
						$arm_extra_vars = array();
						$arm_extra_vars['paid_amount'] = $total;
						$arm_extra_vars['tax_amount'] = $tax_amount;
						if (!empty($recurring_data['trial']))
						{
							$arm_is_trial = 1;
							$trial_amount = $recurring_data['trial']['amount'];
							$trial_period = $recurring_data['trial']['period'];
							$trial_interval = $recurring_data['trial']['interval'];

							$arm_extra_vars['trial'] = array(
								'amount' => $trial_amount,
								'period' => $trial_period,
								'interval' => $trial_interval,
							);
						}
						if (!empty($coupon_code))
						{
							$coupon_apply = $arm_manage_coupons->arm_apply_coupon_code();
							if($coupon_apply["status"] == "success")
							{
								$coupon_discount = (isset($coupon_apply['discount']) && !empty($coupon_apply['discount'])) ? $coupon_apply['discount'] : 0;
								$discount_type = (isset($coupon_apply['discount_type']) && $coupon_apply['discount_type'] != 'percentage') ? $currency : "%";
								$arm_coupon_on_each_subscriptions = isset($coupon_apply['arm_coupon_on_each_subscriptions']) ? $coupon_apply['arm_coupon_on_each_subscriptions'] : '0';
								$coupon_amount = isset($coupon_apply['coupon_amt']) ? $coupon_apply['coupon_amt'] : 0;
								$coupon_amount = str_replace(",", "", $coupon_amount);
								$arm_extra_vars['coupon'] = array(
									'coupon_code' => $coupon_code,
									'amount' => $coupon_amount,
								);
							}
							else
							{
								$coupon_code = '';
							}
						}
						$log_data = array(
							'arm_invoice_id' => 0,
							'arm_user_id' => $user_id,
							'arm_first_name' => $first_name,
							'arm_last_name' => $last_name,
							'arm_plan_id' => $plan_id,
							'arm_payment_gateway' => $gateway,
							'arm_payment_type' => $plan->payment_type,
							'arm_token' => '',								// token
							'arm_payer_email' => $user_email,
							'arm_receiver_email' => '',
							'arm_transaction_id' => $trans_id,
							'arm_transaction_payment_type' => $plan->payment_type,
							'arm_transaction_status' => $status,
							'arm_payment_mode' => 'manual_subscription',	// payment mode
							'arm_payment_date' => current_time('mysql'),
							'arm_amount' => $total,
							'arm_currency' => $currency,
							'arm_extra_vars' => maybe_serialize($arm_extra_vars),
							'arm_coupon_code' => $coupon_code,
							'arm_coupon_discount' => $coupon_discount,
							'arm_coupon_discount_type' => $discount_type,
							'arm_is_trial' => $arm_is_trial,
							'arm_created_date' => current_time('mysql'),
							'arm_display_log' => '1',
							'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
							'arm_is_post_payment' => $is_post_payment,
							'arm_is_post_payment' => $is_gift_payment,
							'arm_paid_post_id' => $paid_post_id,
						);
						$payment_log_id = $arm_payment_gateways->arm_save_payment_log($log_data);
						if ($payment_log_id)
						{
							$result = array('payment_log_id' => $payment_log_id);
							$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_trans_added'], 'response' => array('result' => $result));
						}
						else
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_trans_not_added'], 'response' => array('result' => $result));
						}
						wp_send_json($response);
					}
					else
					{
						$parameters = '';
						if (empty($_REQUEST['arm_user_id']))
						{
							$parameters .= 'arm_user_id';
						}
						if (empty($_REQUEST['plan_id']))
						{
							if (!empty($parameters))
							{
								$parameters .= ',';
							}
							$parameters .= 'plan_id';
						}
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' '.$parameters.'.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_cancel_member_membership( $data )
		{
			global $arm_global_settings, $arm_subscription_plans, $arm_api_common_msg;
			$result = array();
			$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_member_cancel_membership']))
				{
					if (!empty($_REQUEST['arm_user_id']) && !empty($_REQUEST['arm_plan_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$plan_id = $_REQUEST['arm_plan_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_info = new ARM_Plan($plan_id);
						if (!$plan_info->exists())
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan_id'].' '.$plan_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
						if (empty($planData))
						{
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$return = $arm_subscription_plans->arm_ajax_stop_user_subscription($user_id, $plan_id);
						$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
						$response['status'] = $return['type'];
						if (!empty($return['type']) && $return['type'] == 'success')
						{
							$response['response'] = array('result' => array(1));
						}
						else
						{
							$response['response'] = array('result' => array(0));
						}
						$response['message'] = $return['msg'];
						wp_send_json($response);
					}
					else
					{
						$parameters = '';
						if (empty($_REQUEST['arm_user_id']))
						{
							$parameters .= 'arm_user_id';
						}
						if (empty($_REQUEST['arm_plan_id']))
						{
							if (!empty($parameters))
							{
								$parameters .= ',';
							}
							$parameters .= 'arm_plan_id';
						}
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' '.$parameters.'.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
		function arm_check_member_membership( $data )
		{
			global $arm_global_settings, $arm_api_common_msg;
			$result = array();
			$response = array('success' => 'error', 'message' => $arm_api_common_msg['arm_default'], 'response' => array('result' => $result));
			$arm_api_request_key = isset($_REQUEST['arm_api_key']) ? $_REQUEST['arm_api_key'] : '';
			$check_api_key = $this->arm_api_autontication($arm_api_request_key);
			if ($check_api_key['status'])
			{
				$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
				$general_settings = isset($all_global_settings['api_service']) ? $all_global_settings['api_service'] : array();

				if (!empty($general_settings['arm_check_member_membership']))
				{
					if (!empty($_REQUEST['arm_user_id']) && !empty($_REQUEST['arm_plan_id']))
					{
						$user_id = $_REQUEST['arm_user_id'];
						$plan_id = $_REQUEST['arm_plan_id'];
						$user = get_user_by('id', $user_id);
						if (empty($user))
						{
							$response = array('success' => 'error', 'message' => $arm_api_common_msg['arm_no_member_id'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$plan_info = new ARM_Plan($plan_id);
						if (!$plan_info->exists())
						{
							$response = array('success' => 'error', 'message' => $arm_api_common_msg['arm_no_plan_id'].' '.$plan_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
						$planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
						if (!empty($planData))
						{
							$result = array('is_plan' => 1);
							$suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
							$suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
							if (!empty($suspended_plan_ids))
							{
								if (in_array($plan_id, $suspended_plan_ids))
								{
									$result['is_suspended'] = 1;
								}
							}
							$response = array('status' => 1, 'message' => $arm_api_common_msg['arm_success'], 'response' => array('result' => $result));
							wp_send_json($response);
						}
						else
						{
							$result = array('is_plan' => 0);
							$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_plan'].' '.$user_id.'.', 'response' => array('result' => $result));
							wp_send_json($response);
						}
					}
					else
					{
						$parameters = '';
						if (empty($_REQUEST['arm_user_id']))
						{
							$parameters .= 'arm_user_id';
						}
						if (empty($_REQUEST['arm_plan_id']))
						{
							if (!empty($parameters))
							{
								$parameters .= ',';
							}
							$parameters .= 'arm_plan_id';
						}
						$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_no_params'].' '.$parameters.'.', 'response' => array('result' => $result));
						wp_send_json($response);
					}
				}
				else
				{
					$response = array('status' => 0, 'message' => $arm_api_common_msg['arm_service_enable'], 'response' => array('result' => $result));
					wp_send_json($response);
				}
			}
			else
			{
				$response = array('status' => 0, 'message' => $check_api_key['message'], 'response' => array('result' => $result));
			}
			wp_send_json($response);
		}
	}
}
global $arm_api_service_feature;
$arm_api_service_feature = new arm_api_service_feature();