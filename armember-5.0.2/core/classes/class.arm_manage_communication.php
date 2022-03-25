<?php

if (!class_exists('ARM_manage_communication')) {

    class ARM_manage_communication {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            add_action('wp_ajax_arm_message_operation', array($this, 'arm_message_operation'));
            add_action('wp_ajax_arm_delete_single_communication', array($this, 'arm_delete_single_communication'));
            add_action('wp_ajax_arm_delete_bulk_communication', array($this, 'arm_delete_bulk_communication'));
            add_action('arm_user_plan_status_action_failed_payment', array($this, 'arm_user_plan_status_action_mail'), 10, 2);
            add_action('arm_user_plan_status_action_cancel_payment', array($this, 'arm_user_plan_status_action_mail'), 10, 2);
            add_action('arm_user_plan_status_action_eot', array($this, 'arm_user_plan_status_action_mail'), 10, 2);
            add_action('wp_ajax_arm_update_message_communication_status', array($this, 'arm_update_message_communication_status'));
            add_action('wp_ajax_arm_edit_message_data', array($this, 'arm_edit_message_data'));
            add_action('arm_after_recurring_payment_success_outside', array($this, 'arm_recurring_payment_success_email_notification'), 10, 5);
        }

        function arm_message_operation() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $op_type = $_REQUEST['op_type'];
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
            $msg_type = isset($_POST['arm_message_type']) ? sanitize_text_field($_POST['arm_message_type']) : '';
            $msg_per_unit = isset($_POST['arm_message_period_unit']) ? intval($_POST['arm_message_period_unit']) : 1;
            $msg_per_type = isset($_POST['arm_message_period_type']) ? sanitize_text_field($_POST['arm_message_period_type']) : 'day';
            if ($msg_type == 'manual_subscription_reminder' || $msg_type == 'automatic_subscription_reminder') {

                $msg_per_unit = isset($_POST['arm_message_period_unit_manual_subscription']) ? intval($_POST['arm_message_period_unit_manual_subscription']) : 1;
                $msg_per_type = isset($_POST['arm_message_period_type_manual_subscription']) ? sanitize_text_field($_POST['arm_message_period_type_manual_subscription']) : 'day';
            }
            if ($msg_type == 'before_dripped_content_available') {

                $msg_per_unit = isset($_POST['arm_message_period_unit_dripped_content']) ? intval($_POST['arm_message_period_unit_dripped_content']) : 1;
                $msg_per_type = isset($_POST['arm_message_period_type_dripped_content']) ? sanitize_text_field($_POST['arm_message_period_type_dripped_content']) : 'day';
            }
            $msg_subsc = isset($_POST['arm_message_subscription']) ? $_POST['arm_message_subscription'] : '';
            $msg_subject = isset($_POST['arm_message_subject']) ? sanitize_text_field($_POST['arm_message_subject']) : '';
            $msg_status = isset($_POST['arm_message_status']) ? intval($_POST['arm_message_status']) : 1;
            $msg_content = isset($_POST['arm_message_content']) ? $_POST['arm_message_content'] : '';
            $msg_send_copy_to_admin = (isset($_POST['arm_email_send_to_admin']) && $_POST['arm_email_send_to_admin'] == 'on' ) ? 1 : 0;
            $msg_send_diff_copy_to_admin = (isset($_POST['arm_email_different_content_for_admin']) && $_POST['arm_email_different_content_for_admin'] == 'on' ) ? 1 : 0;
            $msg_admin_message = isset($_POST['arm_admin_message_content']) ? $_POST['arm_admin_message_content'] : '';
           // if ($msg_type != 'before_expire') {
                $where = '';
                if ($op_type == 'edit' && !empty($_REQUEST['edit_id']) && $_REQUEST['edit_id'] != 0) {
                    $where = " AND `arm_message_id` != '" . intval($_REQUEST['edit_id']) . "'";
                }
                $where .= " AND `arm_message_period_unit` = ".$msg_per_unit." AND `arm_message_period_type` = '".$msg_per_type."'";
                $check_res = $wpdb->get_results("SELECT `arm_message_subscription` FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_type`='" . $msg_type . "' AND `arm_message_status`='1' " . $where . " ");
                $check_status = array(-1);
                if (!empty($msg_subsc)) {
                    
                  
                    foreach ($check_res as $cr) {
                        if ($cr->arm_message_subscription != '') {
                            $check_subs = @explode(',', $cr->arm_message_subscription);
                            foreach ($msg_subsc as $ms) {
                                if (in_array($ms, $check_subs)) {
                                    $check_status[] = 1;
                                } else {
                                    $check_status[] = 0;
                                }
                            }
                        } else {
                            $check_status[] = 1;
                        }
                    }
                } else {
                   
                    if (count($check_res) > 0) {
                        $check_status[] = 1;
                    } else {
                        $check_status[] = 0;
                    }
                }
           // }
       
            if (!empty($msg_subsc)) {
                $msg_subsc = trim(@implode(',', $msg_subsc), ',');
            } else {
                $msg_subsc = '';
            }
            $message_values = array(
                'arm_message_type' => $msg_type,
                'arm_message_period_unit' => $msg_per_unit,
                'arm_message_period_type' => $msg_per_type,
                'arm_message_subscription' => $msg_subsc,
                'arm_message_subject' => $msg_subject,
                'arm_message_content' => $msg_content,
                'arm_message_status' => $msg_status,
                'arm_message_send_copy_to_admin' => $msg_send_copy_to_admin,
                'arm_message_send_diff_msg_to_admin' => $msg_send_diff_copy_to_admin,
                'arm_message_admin_message' => $msg_admin_message
            );

            $message_values=apply_filters('arm_automated_email_template_save_before',$message_values,$_POST);
     
            if ($op_type == 'add') {
                //if ($msg_type != 'before_expire') {
                    if (!in_array(1, $check_status)) {
                        $ins = $wpdb->insert($ARMember->tbl_arm_auto_message, $message_values);
                        if ($ins) {
                            $message = __('Message Added Successfully.', 'ARMember');
                            $status = 'success';
                        } else {
                            $message = __('Error Adding Message, Please Try Again.', 'ARMember');
                            $status = 'failed';
                        }
                    } else {
                        $message = __('Could Not Perform The Operation, Because Message With The Same Type And Subscription Plan Already Exists.', 'ARMember');
                        $status = 'failed';
                    }
                // } else {
                //     $ins = $wpdb->insert($ARMember->tbl_arm_auto_message, $message_values);
                //     if ($ins) {
                //         $message = __('Message Added Successfully.', 'ARMember');
                //         $status = 'success';
                //     } else {
                //         $message = __('Error Adding Message, Please Try Again.', 'ARMember');
                //         $status = 'failed';
                //     }
                // }
            } else {
               // if ($msg_type != 'before_expire') {
                    if (!in_array(1, $check_status)) {
                        $mid = intval($_REQUEST['edit_id']);
                        $where = array('arm_message_id' => $mid);
                        $up_message = $wpdb->update($ARMember->tbl_arm_auto_message, $message_values, $where); 
                        $message = __('Message Updated Successfully', 'ARMember');
                        $status = 'success';
                    } else {
                        $message = __('Could Not Perform The Operation, Because Message With The Same Type And Subscription Plan Already Exists.', 'ARMember');
                        $status = 'failed';
                    }
                // } else {
                //     $mid = $_REQUEST['edit_id'];
                //     $where = array('arm_message_id' => $mid);
                //     $up_message = $wpdb->update($ARMember->tbl_arm_auto_message, $message_values, $where);
                //     $message = __('Message Updated Successfully.', 'ARMember');
                //     $status = 'success';
                // }
            }
            $response = array('status' => $status, 'message' => $message);
            if ($status == 'success') {
                $ARMember->arm_set_message($status, $message);
            }
            $redirect_link = admin_url('admin.php?page=' . $arm_slugs->email_notifications);
            $response['redirect_to'] = $redirect_link;
            echo json_encode($response);
            die();
        }

        function arm_update_message_communication_status($posted_data = array()) {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['arm_message_id']) && $_POST['arm_message_id'] != 0) {
                $message_id = intval($_POST['arm_message_id']);
                $msg_status = (!empty($_POST['arm_message_status'])) ? intval($_POST['arm_message_status']) : 0;
                $message_values = array('arm_message_status' => $msg_status);
                $update_temp = $wpdb->update($ARMember->tbl_arm_auto_message, $message_values, array('arm_message_id' => $message_id));
                $response = array('type' => 'success', 'msg' => __('Message Updated Successfully.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }

        function arm_delete_single_communication() {
            global $wpdb, $ARMember, $arm_slugs, $arm_subscription_plans, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
            $action = sanitize_text_field($_POST['act']);
            $id = intval($_POST['id']);
            if ($action == 'delete') {
                if (empty($id)) {
                    $errors[] = __('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_communication')) {
                        $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                    } else {
                        $res_var = $wpdb->delete($ARMember->tbl_arm_auto_message, array('arm_message_id' => $id));
                        if ($res_var) {
                            $message = __('Message has been deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_delete_bulk_communication() {
            if (!isset($_POST)) {
                return;
            }
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
            $bulkaction = $arm_global_settings->get_param('action1');
            if ($bulkaction == -1) {
                $bulkaction = $arm_global_settings->get_param('action2');
            }
            $ids = $arm_global_settings->get_param('item-action', '');
            if (empty($ids)) {
                $errors[] = __('Please select one or more records.', 'ARMember');
            } else {
                if (!current_user_can('arm_manage_communication')) {
                    $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                } else {
                    if (!is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    if (is_array($ids)) {
                        if ($bulkaction == 'delete_communication') {
                            foreach ($ids as $msg_id) {
                                $res_var = $wpdb->delete($ARMember->tbl_arm_auto_message, array('arm_message_id' => $msg_id));
                            }
                            if ($res_var) {
                                $message = __('Message(s) has been deleted successfully.', 'ARMember');
                            }
                        } else {
                            $errors[] = __('Please select valid action.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_user_plan_status_action_mail($args = array(), $plan_obj = array()) {
            global $wpdb, $ARMember, $arm_slugs, $arm_subscription_plans, $arm_global_settings;
            if (!empty($args['action'])) {
                $now = current_time('timestamp');
                $user_id = $args['user_id'];
                $plan_id = $args['plan_id'];
                $alreadysentmsgs = array();
                
                
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
               
                if(!empty($planData)){
                   if(isset($planData['arm_sent_msgs']) && !empty($planData['arm_sent_msgs'])){
                      $alreadysentmsgs = $planData['arm_sent_msgs'];
                   } 
                }
         
                $notification_type = '';
                switch ($args['action']) {
                    case 'on_failed':
                    case 'failed_payment':
                        $notification_type = 'on_failed';
                        break;
                    case 'on_next_payment_failed':
                        $notification_type = 'on_next_payment_failed';
                        break;
                    case 'on_cancel_subscription':
                    case 'on_cancel':
                    case 'cancel_payment':
                    case 'cancel_subscription':
                        $notification_type = 'on_cancel_subscription';
                        break;
                    case 'on_expire':
                    case 'eot':
                        $notification_type = 'on_expire';
                        break;
                    case 'on_new_subscription':
                    case 'new_subscription':
                        $notification_type = 'on_new_subscription';
                        break;
                    case 'on_change_subscription':
                    case 'change_subscription':
                        $notification_type = 'on_change_subscription';
                        break;
                    case 'on_renew_subscription':
                    case 'renew_subscription':
                        $notification_type = 'on_renew_subscription';
                        break;
                    case 'on_success_payment':
                    case 'success_payment':
                        $notification_type = 'on_success_payment';
                        break;
                    case 'on_change_subscription_by_admin':
                        $notification_type = 'on_change_subscription_by_admin';
                        break;
                    case 'before_dripped_content_available':
                        $notification_type = 'before_dripped_content_available';
                        break;
                    case 'on_recurring_subscription':
                        $notification_type = 'on_recurring_subscription';
                        break;
                    case 'on_close_account':
                        $notification_type = 'on_close_account';
                        break;
                    case 'on_login_account':
                        $notification_type = 'on_login_account';
                        break;
                    
                    default:
                        break;
                }
                $notification = $this->membership_communication_mail($notification_type, $user_id, $plan_id);
                if ($notification) {
                    $alreadysentmsgs[$now] = $notification_type;
                    $planData['arm_sent_msgs'] = $alreadysentmsgs;
                    update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                }
                return $notification;
            }
        }

        function membership_communication_mail($message_type = "", $user_id = 0, $user_plan = 0) {
            global $wpdb, $ARMember, $arm_slugs, $arm_subscription_plans, $arm_global_settings, $wp_hasher, $arm_email_settings,$arm_pay_per_post_feature;
            $send_mail = false;
            if (!empty($user_id) && $user_id != 0) {
                $user_plan = (!empty($user_plan)) ? $user_plan : 0;
                $user_info = get_userdata($user_id);
                $user_email = $user_info->user_email;
                $user_login = $user_info->user_login;
                if(isset($user_info->data) && empty($user_email) && empty($user_login)) {
                    $user_email = $user_info->data->user_email;
                    $user_login = $user_info->data->user_login;
                }

                $key = '';
                if ($message_type == 'on_new_subscription' || $message_type == 'on_menual_activation') {

                    

                    if (function_exists('get_password_reset_key')) {
                        remove_all_filters('allow_password_reset');
                        
                        $key = get_password_reset_key($user_info);
                       
                        
                    } else {
                        do_action('retreive_password', $user_login);  /* Misspelled and deprecated */
                        do_action('retrieve_password', $user_login);

                        $allow = apply_filters('allow_password_reset', true, $user_id);

                        if (!$allow) {
                            $key = "";
                        } else if (is_wp_error($allow)) {
                            $key = "";
                        }
                        /* Generate something random for a key... */
                        $key = wp_generate_password(20, false);
                        do_action('retrieve_password_key', $user_login, $key);
                        /* Now insert the new md5 key into the db */
                        if (empty($wp_hasher)) {
                            require_once ABSPATH . WPINC . '/class-phpass.php';
                            $wp_hasher = new PasswordHash(8, true);
                        }
                        $hashed = $wp_hasher->HashPassword($key);
                        $key_saved = $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));
                        if (false === $key_saved) {
                            $key = '';
                        }
                      
                    }
                }


                if (!empty($message_type) && $message_type != 'before_expire') {
			$is_post_plan = 0;
                    if( $arm_pay_per_post_feature->isPayPerPostFeature ){
                        $planResp = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $user_plan );

                        if( !empty( $planResp[0] ) && !empty( $planResp[0]['arm_subscription_plan_post_id']) ){
                            if( in_array( $message_type , array( 'on_new_subscription', 'on_renew_subscription', 'on_recurring_subscription', 'on_cancel_subscription', 'on_expire' ) ) ){
                                $message_type = $message_type .'_post';
                                $is_post_plan = 1;
                            }
                        }
                    }            
                    
                    $message_type = apply_filters('arm_filter_email_message_type', $message_type, $user_plan, $is_post_plan);
                   
                    $messages = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_status`='1' AND `arm_message_type`='" . $message_type . "' AND (FIND_IN_SET(" . $user_plan . ", `arm_message_subscription`) OR (`arm_message_subscription`=''))");
                    if (!empty($messages)) {
                        foreach ($messages as $msg) {
                            $content_subject = $msg->arm_message_subject;
                            $content_description = $msg->arm_message_content;
                            $send_one_copy_to_admin = $msg->arm_message_send_copy_to_admin;
                            $send_diff_copy_to_admin = $msg->arm_message_send_diff_msg_to_admin;
                            $admin_content_description = $msg->arm_message_admin_message;
                            $subject = $this->arm_filter_communication_content($content_subject, $user_id, $user_plan);
                            $message = $this->arm_filter_communication_content($content_description, $user_id, $user_plan, $key);
                            $admin_message = $this->arm_filter_communication_content($admin_content_description, $user_id, $user_plan, $key);
                            $attachment_arr=array();
                            $attachments=apply_filters('arm_automated_message_email_attachment', $attachment_arr,$user_id,$msg,$user_plan);
                            $send_mail = $arm_global_settings->arm_wp_mail('', $user_email, $subject, $message, $attachments); 

                            if ($send_one_copy_to_admin == 1) {
                                if($send_diff_copy_to_admin == 1)
                                {
                                   $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_message,$attachments);
                                }
                                else
                                {                                    
                                   $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $message,$attachments); 
                                }
                            }
                        }
                    }
                }
            }
            return $send_mail;
        }

        function arm_filter_communication_content($content = '', $user_id = 0, $user_plan = 0, $key = '') {
            global $wpdb, $ARMember, $arm_slugs, $arm_subscription_plans, $arm_global_settings, $arm_payment_gateways, $wp_hasher, $arm_email_settings;
            if (!empty($content) && !empty($user_id)) {
                $date_format = $arm_global_settings->arm_get_wp_date_format();
                $currency = $arm_payment_gateways->arm_get_global_currency();
                $user_plan = (!empty($user_plan) && $user_plan != 0) ? $user_plan : 0;
                $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($user_plan);
       
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$user_plan, true); 
                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                $arm_plan_detail = $planData['arm_current_plan_detail'];
                $using_gateway = $planData['arm_user_gateway'];
                $arm_plan_description = !empty($arm_plan_detail['arm_subscription_plan_description']) ? $arm_plan_detail['arm_subscription_plan_description'] : '';
                if( isset( $arm_plan_detail['arm_subscription_plan_type'] ) && $arm_plan_detail['arm_subscription_plan_type'] == 'recurring' )
                {
                    $arm_user_plan_info = new ARM_Plan(0);
                    $arm_user_plan_info->init((object) $arm_plan_detail);
                    $arm_user_payment_cycle = isset($arm_plan_detail['arm_user_selected_payment_cycle']) ? $arm_plan_detail['arm_user_selected_payment_cycle'] : '';
                    $arm_user_plan_data = $arm_user_plan_info->prepare_recurring_data($arm_user_payment_cycle);
                    $plan_amount = isset( $arm_user_plan_data['amount'] ) ? $arm_user_plan_data['amount'] : 0;
                } else {
                    $plan_amount = isset( $arm_plan_detail['arm_subscription_plan_amount'] ) ? $arm_plan_detail['arm_subscription_plan_amount'] : 0;
                }

                $u_payable_amount = 0;
                $u_tax_percentage = '-';
                $u_tax_amount = '-';
                $u_transaction_id = '-';
                $u_payment_date = '-';
                $plan_expire = __('Never Expires', 'ARMember');
                $expire_time = $planData['arm_expire_plan'];
                if (!empty($expire_time)) {
                    $plan_expire = date_i18n($date_format, $expire_time);
                }
                
                $plan_next_due_date = '-';
                $next_due_date = $planData['arm_next_due_payment'];
                if (!empty($next_due_date)) {
                    $plan_next_due_date = date_i18n($date_format, $next_due_date);
                }

                $user_info = get_userdata($user_id);
                $blog_name = get_bloginfo('name');
                $blog_url = ARM_HOME_URL;
                $u_email = $user_info->user_email;
                $u_displayname = $user_info->display_name;
                $u_userurl = $user_info->user_url;
                $u_username = $user_info->user_login;
                $u_fname = $user_info->first_name;
                $u_lname = $user_info->last_name;
                $u_nicename = $user_info->user_nicename;
                $networ_name = get_site_option('site_name');
                $networ_url = get_site_option('siteurl');

               
                if ($key != '' && !empty($key)) {

                    $change_password_page_id = isset($arm_global_settings->global_settings['change_password_page_id']) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
                    if ($change_password_page_id == 0) {
                        $arm_reset_password_link = network_site_url("wp-login.php?action=armrp&key=" . rawurlencode($key) . "&login=" . rawurlencode($u_username), 'login');
                    } else {
                        $arm_change_password_page_url = $arm_global_settings->arm_get_permalink('', $change_password_page_id);
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('action', 'armrp', $arm_change_password_page_url);
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('key', rawurlencode($key), $arm_change_password_page_url);
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('login', rawurlencode($u_username), $arm_change_password_page_url);
                        $arm_reset_password_link = $arm_change_password_page_url;
                    }
                    $content = str_replace('{ARM_MESSAGE_RESET_PASSWORD_LINK}', $arm_reset_password_link, $content);
                } else {

                    $content = str_replace('{ARM_MESSAGE_RESET_PASSWORD_LINK}', '', $content);
                }


                 $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`, `arm_amount`, `arm_is_trial`, `arm_extra_vars`, `arm_coupon_discount`,`arm_coupon_discount_type`,`arm_payment_date`,`arm_coupon_code`';
                $where_bt=''; 
                if ($using_gateway == 'bank_transfer') {
                    /* Change Log Table For Bank Transfer Method */
                    $armLogTable = $ARMember->tbl_arm_payment_log;
                    $where_bt=" AND arm_payment_gateway='bank_transfer'";
                } else {
                    $armLogTable = $ARMember->tbl_arm_payment_log;
                    $selectColumns .= ', `arm_token`';

                }

              
                $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_user_id`='{$user_id}' AND `arm_plan_id`='{$user_plan}' {$where_bt} ORDER BY `arm_log_id` DESC");
                $u_plan_discount = 0;
                $u_trial_amount = 0;
                $u_coupon_code = "";
                if (!empty($log_detail)) {
                    $u_transaction_id = $log_detail->arm_transaction_id;
                    $u_payable_amount = $log_detail->arm_amount;

                    $extravars = maybe_unserialize($log_detail->arm_extra_vars);
                    if (!empty($log_detail->arm_coupon_discount) && $log_detail->arm_coupon_discount > 0) {


                        if ($using_gateway == 'bank_transfer') {
                            if(isset($extravars['coupon'])){
                                $u_plan_discount = isset($extravars['coupon']['amount']) ? $extravars['coupon']['amount'] : 0;
                                $u_coupon_code = isset($extravars['coupon']['coupon_code']) ? $extravars['coupon']['coupon_code'] : "";
                            }
                            else{
                                $u_plan_discount = $log_detail->arm_coupon_discount.$log_detail->arm_coupon_discount_type;
                                $u_coupon_code = $log_detail->arm_coupon_code;
                            }
                        }
                        else{
                            $u_plan_discount = isset($extravars['coupon']['amount']) ? $extravars['coupon']['amount'] : 0;   
                            $u_coupon_code = isset($extravars['coupon']['coupon_code']) ? $extravars['coupon']['coupon_code'] : "";
                        }
                    }

                    if(isset($extravars['tax_percentage'])){
                        $u_tax_percentage = ($extravars['tax_percentage'] != '') ? $extravars['tax_percentage'].'%': '-';
                    }

                    if(isset($extravars['tax_amount'])){
                        $u_tax_amount = ($extravars['tax_amount'] != '') ? $arm_payment_gateways->arm_amount_set_separator($currency, $extravars['tax_amount']): '-';
                    }


                    if (!empty($log_detail->arm_is_trial) && $log_detail->arm_is_trial == 1) {
                        $u_trial_amount= isset($extravars['trial']['amount']) ? $extravars['trial']['amount'] : 0;

                    }

                    if (!empty($log_detail->arm_payment_date)) {
                        $date_format = $arm_global_settings->arm_get_wp_date_format();
                        $u_payment_date = date_i18n($date_format, $log_detail->arm_payment_date);
                    }
                }

                
                $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
                $admin_email = (!empty($all_email_settings['arm_email_admin_email'])) ? $all_email_settings['arm_email_admin_email'] : get_option('admin_email');
                

                $activation_key = get_user_meta($user_id, 'arm_user_activation_key', true);
                $login_page_id = isset($arm_global_settings->global_settings['login_page_id']) ? $arm_global_settings->global_settings['login_page_id'] : 0;
                
                $login_url = $arm_global_settings->arm_get_permalink('', $login_page_id);

                $u_payment_type = '-';
                $u_payment_gateway = '-';
                $planObj = "";
                if (!empty($arm_plan_detail)) {
                    $plan_detail = maybe_unserialize($arm_plan_detail);
                    if (!empty($plan_detail)) {
                        $planObj = new ARM_Plan(0);
                        $planObj->init((object) $plan_detail);
                    } else {
                        $planObj = new ARM_Plan($user_plan);
                    }
                }
                
                if(!empty($planObj)) {
                    if ($planObj->is_paid()) {
                        if ($planObj->is_lifetime()) {
                            $u_payment_type = __('Life Time', 'ARMember');
                        } else {
                            if ($planObj->is_recurring()) {
                                $u_payment_type = __('Subscription', 'ARMember');
                            } else {
                                $u_payment_type = __('One Time', 'ARMember');
                            }
                        }
                    } else {
                        $u_payment_type = __('Free', 'ARMember');
                    }
                }

                
                if (!empty($using_gateway)) {
                    $u_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($using_gateway);
                }


                $profile_link = $arm_global_settings->arm_get_user_profile_url($user_id);
                $content = str_replace('{ARM_MESSAGE_BLOGNAME}', $blog_name, $content);
                $content = str_replace('{ARM_MESSAGE_BLOGURL}', $blog_url, $content);
                $content = str_replace('{ARM_MESSAGE_NETWORKNAME}', $networ_name, $content);
                $content = str_replace('{ARM_MESSAGE_NETWORKURL}', $networ_url, $content);
                $content = str_replace('{ARM_MESSAGE_USERNAME}', $u_username, $content);
                $content = str_replace('{ARM_MESSAGE_USER_ID}', $user_id, $content);
                $content = str_replace('{ARM_MESSAGE_EMAIL}', $u_email, $content);
                $content = str_replace('{ARM_MESSAGE_USERNICENAME}', $u_nicename, $content);
                $content = str_replace('{ARM_MESSAGE_USERDISPLAYNAME}', $u_displayname, $content);
                $content = str_replace('{ARM_MESSAGE_USERFIRSTNAME}', $u_fname, $content);
                $content = str_replace('{ARM_MESSAGE_USERLASTNAME}', $u_lname, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTIONNAME}', $plan_name, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTIONDESCRIPTION}', $arm_plan_description, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}', $plan_amount, $content);
                $content = str_replace('{ARM_MESSAGE_COUPON_DISCOUNT}', $u_plan_discount, $content);
                $content = str_replace('{ARM_MESSAGE_TRIAL_AMOUNT}', $u_trial_amount, $content);
                $content = str_replace('{ARM_MESSAGE_PAYABLE_AMOUNT}', $u_payable_amount, $content);
                $content = str_replace('{ARM_MESSAGE_CURRENCY}', $currency, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}', $plan_expire, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}', $plan_next_due_date, $content);
                 $content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_TAX_PERCENTAGE}', $u_tax_percentage, $content);
                $content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_TAX_AMOUNT}', $u_tax_amount, $content);
                $content = str_replace('{ARM_PROFILE_LINK}', $profile_link, $content);
                $content = str_replace('{ARM_USERMETA_user_url}', $u_userurl, $content);
                $content = str_replace('{ARM_MESSAGE_ADMIN_EMAIL}', $admin_email, $content);
                $content = str_replace('{ARM_MESSAGE_LOGIN_URL}', $login_url, $content);
                $content = str_replace('{ARM_MESSAGE_PAYMENT_TYPE}', $u_payment_type, $content);
                $content = str_replace('{ARM_MESSAGE_PAYMENT_GATEWAY}', $u_payment_gateway, $content);
                $content = str_replace('{ARM_MESSAGE_TRANSACTION_ID}', $u_transaction_id, $content);
                $content = str_replace('{ARM_MESSAGE_PAYMENT_DATE}', $u_payment_date, $content);
                $content = str_replace('{ARM_MESSAGE_COUPON_CODE}', $u_coupon_code, $content);

                /* Content replace for user meta */
                $matches = array();
                preg_match_all("/\b(\w*ARM_USERMETA_\w*)\b/", $content, $matches, PREG_PATTERN_ORDER);
                $matches = $matches[0];
                if (!empty($matches)) {
                    foreach ($matches as $mat_var) {
                        $key = str_replace('ARM_USERMETA_', '', $mat_var);
                        $meta_val = "";
                        if (!empty($key)) {
                            $meta_val = get_user_meta($user_id, $key, TRUE);
                            if(is_array($meta_val))
                            {
                                $replace_val = "";
                                foreach ($meta_val as $key => $value) {
                                    $replace_val .= ($value != '') ? $value."," : "";   
                                }
                                $meta_val = rtrim($replace_val, ",");
                            }
                        }
                        $content = str_replace('{' . $mat_var . '}', $meta_val, $content);
                    }
                }
               
            }
            $content = nl2br($content);
            $content = apply_filters('arm_change_advanced_email_communication_email_notification', $content, $user_id, $user_plan);
            return $content;
        }

        function arm_get_communication_messages_by($field = '', $value = '') {
            global $wpdb, $ARMember, $arm_slugs, $arm_subscription_plans, $arm_global_settings;
            $messages = array();
            if (!empty($field) && !empty($value)) {
                $field_key = $field;
                switch ($field) {
                    case 'id':
                    case 'message_id':
                    case 'arm_message_id':
                        $field_key = 'arm_message_id';
                        break;
                    case 'type':
                    case 'message_type':
                    case 'arm_message_type':
                        $field_key = 'arm_message_type';
                        break;
                    case 'status':
                    case 'message_status':
                    case 'arm_message_status':
                        $field_key = 'arm_message_status';
                        break;
                    default:
                        break;
                }
                $results = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_status`='1' AND `$field_key`='$value'");
                if (!empty($results)) {
                    $messages = $results;
                }
            }
            return $messages;
        }
        function arm_get_communication_messages_sorted($notifications) {
            $sort_arr = array();
            $new_notif_result = array();
            $now = current_time('timestamp');
            if(!empty($notifications)) {

                foreach ($notifications as $key => $notification) {
                    $period_unit = $notification->arm_message_period_unit;
                    $period_type = $notification->arm_message_period_type;
                    $endtime = strtotime("+$period_unit Days", $now);
                    switch (strtolower($period_type)) {
                        case 'd':
                        case 'day':
                        case 'days':
                            //$endtime = strtotime("+$period_unit Days", $now);
                            $notifications[$key]->arm_message_period_unit = $period_unit;
                            $notifications[$key]->arm_message_period_type = "day";
                            break;
                        case 'w':
                        case 'week':
                        case 'weeks':
                            //$endtime = strtotime("+$period_unit Weeks", $now);
                            $notifications[$key]->arm_message_period_unit = $period_unit * 7;
                            $notifications[$key]->arm_message_period_type = "day";
                            break;
                        case 'm':
                        case 'month':
                        case 'months':
                            //$endtime = strtotime("+$period_unit Months", $now);
                            $unit = 0;
                            for($i=1; $i<=$period_unit; $i++) {
                                $new_date = strtotime("+$i Months", $now);
                                $date = date_create(date("Y-m-d",$new_date));
                                $unit += date_format($date,"t");
                            }
                            $notifications[$key]->arm_message_period_unit = $unit;
                            $notifications[$key]->arm_message_period_type = "day";
                            break;
                        case 'y':
                        case 'year':
                        case 'years':
                            //$endtime = strtotime("+$period_unit Years", $now);
                            $unit = 0;
                            $new_date = strtotime("+$period_unit Years", $now);
                            $date1 = date_create(date("Y-m-d",$now));
                            $date2 = date_create(date("Y-m-d",$new_date));
                            $unit = (abs(date_diff($date1,$date2)->days)) ? abs(date_diff($date1,$date2)->days): 0;
                            $notifications[$key]->arm_message_period_unit = $unit;
                            $notifications[$key]->arm_message_period_type = "day";
                            break;
                        default:
                            break;
                    }
                    if($key > 0) {
                        array_push($new_notif_result, $notification);
                        $cnt = count($new_notif_result) - 1;
                        for($j=$cnt; $j>=0; $j--) {
                            if($new_notif_result[$j]->arm_message_period_unit > $notification->arm_message_period_unit) {
                                $obj = $new_notif_result[$j];
                                $new_notif_result[$j] = $notification;
                                $new_notif_result[$j + 1] = $obj;
                            }
                        }
                            
                    } else {
                        array_push($new_notif_result, $notification);
                    }
                }
            }
            return $new_notif_result;
            
        }

        function arm_edit_message_data() {
            global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_manage_communication, $arm_capabilities_global;
            $return = array('status' => 'error');
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
            if (isset($_REQUEST['action']) && isset($_REQUEST['message_id']) && $_REQUEST['message_id'] != '') {
                $form_id = 'arm_edit_message_wrapper_frm';
                $mid = intval($_REQUEST['message_id']);
                $result = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_id`= '" . $_REQUEST['message_id'] . "' ");
                $msg_per_subscription = $result->arm_message_subscription;
                $c_subs = @explode(',', $msg_per_subscription);
                $msge_type = '';
                switch ($result->arm_message_type) {
                    case 'on_new_subscription':
                        $msge_type = __('On New Subscription', 'ARMember');
                        break;
                    case 'on_cancel_subscription':
                        $msge_type = __('On Cancel Membership', 'ARMember');
                        break;
                    case 'on_menual_activation':
                        $msge_type = __('On Manual User Activation', 'ARMember');
                        break;
                    case 'on_change_subscription':
                        $msge_type = __('On Change Subscription', 'ARMember');
                        break;
                    case 'on_renew_subscription':
                        $msge_type = __('On Renew Subscription', 'ARMember');
                        break;
                    case 'on_failed':
                        $msge_type = __('On Failed Payment', 'ARMember');
                        break;
                    case 'on_next_payment_failed':
                        $msge_type = __('On Semi Automatic Subscription Failed Payment', 'ARMember');
                        break;
                    case 'trial_finished':
                        $msge_type = __('Trial Finished', 'ARMember');
                        break;
                    case 'on_expire':
                        $msge_type = __('On Membership Expired', 'ARMember');
                        break;
                    case 'before_expire':
                        $msge_type = __('Before Membership Expired', 'ARMember');
                        break;
                    case 'manual_subscription_reminder':
                        $msge_type = __('Before Semi Automatic Subscription Payment due', 'ARMember');
                        break;
                    case 'automatic_subscription_reminder':
                        $msge_type = __('Before Automatic Subscription Payment due','ARMember');
                        break;
                    case 'on_change_subscription_by_admin':
                        $msge_type = __('On Change Subscription By Admin', 'ARMember');
                        break;
                    case 'before_dripped_content_available':
                        $msge_type = __('Before Dripped Content Available', 'ARMember');
                        break;
                    case 'on_recurring_subscription':
                        $msge_type = __('On Recurring Subscription', 'ARMember');
                        break;
                    case 'on_close_account':
                        $msge_type = __('On Close User Account', 'ARMember');
                        break;
                    case 'on_login_account':
                        $msge_type = __('On User Login', 'ARMember');
                        break;
                    case 'on_new_subscription_post':
                        $msge_type = __('On new paid post purchase', 'ARMember');
                        break;
                    case 'on_recurring_subscription_post':
                        $msge_type = __('On recurring paid post purchase', 'ARMember');
                        break;
                    case 'on_renew_subscription_post':
                        $msge_type = __('On renew paid post purchase', 'ARMember');
                        break;
                    case 'on_cancel_subscription_post':
                        $msge_type = __('On cancel paid post', 'ARMember');
                        break;
                    case 'before_expire_post':
                        $msge_type = __('Before paid post expire', 'ARMember');
                        break;
                    case 'on_expire_post':
                        $msge_type = __('On Expire paid post', 'ARMember');
                        break;
                    default:
                        break;
                }

                $msge_type = apply_filters('arm_filter_edit_email_notification_type', $msge_type, $result->arm_message_type);

                $return = array(
                    'status' => 'success',
                    'id' => $_REQUEST['message_id'],
                    'popup_heading' => $msge_type,
                    'arm_message_type' => $result->arm_message_type,
                    'arm_message_period_unit' => $result->arm_message_period_unit,
                    'arm_message_period_type' => $result->arm_message_period_type,
                    'arm_message_subscription' => $c_subs,
                    'arm_message_subject' => stripslashes_deep($result->arm_message_subject),
                    'arm_message_content' => stripslashes_deep($result->arm_message_content),
                    'arm_message_status' => $result->arm_message_status,
                    'arm_message_send_copy_to_admin' => stripslashes_deep($result->arm_message_send_copy_to_admin),
                    'arm_message_send_diff_copy_to_admin' => $result->arm_message_send_diff_msg_to_admin,
                    'arm_message_admin_message' => stripslashes_deep($result->arm_message_admin_message),
                );
                $return = apply_filters('arm_automated_email_attachment_file_outside',$return);
            }
            echo json_encode($return);
            exit;
        }

        function arm_recurring_payment_success_email_notification($user_id, $plan_id, $payment_gateway = '', $payment_mode = '', $user_subsdata = '')
        {
            global $wpdb, $ARMember, $arm_manage_communication;
            $args = array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_recurring_subscription');

            $mail_sent = $arm_manage_communication->arm_user_plan_status_action_mail($args);
        }

    }

}
global $arm_manage_communication;
$arm_manage_communication = new ARM_manage_communication();
