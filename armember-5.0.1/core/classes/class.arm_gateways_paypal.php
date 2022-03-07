<?php

if (!class_exists('ARM_Paypal')) {

    class ARM_Paypal {

        function __construct() {
            add_action('arm_payment_gateway_validation_from_setup', array($this, 'arm_payment_gateway_form_submit_action'), 10, 4);
            //add_action('wp', array($this, 'arm_paypal_api_handle_response'), 5);
            add_action('arm_cancel_subscription_gateway_action', array($this, 'arm_cancel_paypal_subscription'), 10, 2);
            add_filter('arm_update_new_subscr_gateway_outside', array($this, 'arm_update_new_subscr_gateway_outside_func'), 10);
            add_filter('arm_change_pending_gateway_outside', array($this, 'arm_change_pending_gateway_outside'), 100, 3);

            add_filter('arm_payment_gateway_trial_allowed', array($this, 'arm_paypal_trial_allowed_or_not'), 10, 5);

            add_action('arm_on_expire_cancel_subscription', array($this, 'arm_cancel_subscription_instant'), 10, 4);
        }

        
        function arm_paypal_trial_allowed_or_not($trial_not_allowed, $arm_plan_id, $payment_gateway, $payment_gateway_options, $posted_data)
        {
            if($payment_gateway == "paypal" && (!empty($posted_data['arm_selected_payment_mode']) && $posted_data['arm_selected_payment_mode'] == "auto_debit_subscription") )
            {
                global $arm_subscription_plans;
                $plan = new ARM_Plan($arm_plan_id);
                if ($plan->is_recurring()) {
                    $subscription_plan_detail = $arm_subscription_plans->arm_get_subscription_plan($arm_plan_id);
                    if( !empty($subscription_plan_detail['arm_subscription_plan_options']['trial']) ) {
                        $subscr_trial_detail = $subscription_plan_detail['arm_subscription_plan_options']['trial'];
                        if(!empty($subscr_trial_detail['is_trial_period']) && $subscr_trial_detail['is_trial_period']==1) {
                            $trial_period = isset($subscr_trial_detail['days']) ? $subscr_trial_detail['days'] : 0;
                            $trial_type = isset($subscr_trial_detail['type']) ? $subscr_trial_detail['type'] : 'D';

                            if( $trial_type=='D' && $trial_period > 90 ) {
                                $trial_not_allowed = 1;
                            }
                        }
                    }
                }
            }
            return $trial_not_allowed;
        }


        function arm_cancel_subscription_instant($user_id, $plan, $cancel_plan_action, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_debug_payment_log_id;

            $plan_id = $plan->ID;
            $arm_cancel_subscription_data = array();
            $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, 'paypal', 'arm_subscr_id', '', '');
            

            $planData = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();
            $arm_user_payment_gateway = !empty($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '';
            if(strtolower($arm_user_payment_gateway) == "paypal"){

                $arm_cancel_subscr_data = array(
                    'user_id' => $user_id,
                    'plan' => $plan,
                    'cancel_plan_action' => $cancel_plan_action,
                    'planData' => $planData,
                    'arm_cancel_subscription_data' => $arm_cancel_subscription_data,
                );
                do_action('arm_payment_log_entry', 'paypal', 'on expire cancel subscription params', 'armember', $arm_cancel_subscr_data, $arm_debug_payment_log_id);

                $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                $subscr_id = !empty($_POST['subscr_id']) ? $_POST['subscr_id'] : '';
                $arm_subscr_id = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : $subscr_id;
                $arm_customer_id = !empty($arm_cancel_subscription_data['arm_customer_id']) ? $arm_cancel_subscription_data['arm_customer_id'] : '';
                $arm_transaction_id = !empty($arm_cancel_subscription_data['arm_transaction_id']) ? $arm_cancel_subscription_data['arm_transaction_id'] : '';

                $this->arm_immediate_cancel_paypal_payment($arm_subscr_id, $user_id, $plan_id, $planData);
            }   
        }


        function arm_immediate_cancel_paypal_payment($subscr_id, $user_id, $plan_id, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;

            $paypal_subscr_id = !empty($_POST['subscr_id']) ? $_POST['subscr_id'] : '';
            $subscr_id = !empty($subscr_id) ? $subscr_id : $paypal_subscr_id;

            $arm_cancel_subscr_data = array(
                'subscr_id' => $paypal_subscr_id,
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'plan_data' => $planData,
            );
            do_action('arm_payment_log_entry', 'paypal', 'immediate cancel subscription params', 'armember', $arm_cancel_subscr_data, $arm_debug_payment_log_id);
            
            try{
                $PayPal = self::arm_init_paypal();
                
                $PayPalCancelRequestData = array(
                    'MRPPSFields' => array(
                        'profileid' => $subscr_id,
                        'action' => urlencode('Cancel'),
                        'note' => __("Cancel User's Subscription.", 'ARMember')
                    )
                );
                $PayPalResult = $PayPal->ManageRecurringPaymentsProfileStatus($PayPalCancelRequestData);
                do_action('arm_payment_log_entry', 'paypal', 'immediate cancel subscription response', 'armember', $PayPalResult, $arm_debug_payment_log_id);
                if (!is_wp_error($PayPalResult) && isset($PayPalResult['ACK']) && strtolower($PayPalResult['ACK']) == 'success') {
                    $planData['arm_paypal']['arm_subscr_id'] = '';
                    update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                }
            }
            catch(Exception $e)
            {
                do_action('arm_payment_log_entry', 'paypal', 'cancel subscription error', 'armember', $e->getMessage(), $arm_debug_payment_log_id);
                if(!empty($e->getMessage()))
                {
                    $arm_subscription_cancel_msg = __("Error in cancel subscription from Paypal.", "ARMember")." ".$e->getMessage();
                }
                else
                {
                    $common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                    $arm_subscription_cancel_msg = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel. Please contact the site administrator.", 'ARMember');
                }
            }
        }

        function arm_init_paypal() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            if (file_exists(MEMBERSHIP_DIR . "/lib/paypal/paypal.class.php")) {
                require_once (MEMBERSHIP_DIR . "/lib/paypal/paypal.class.php");
            }
            /* ---------------------------------------------------------------------------- */
            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            if (isset($all_payment_gateways['paypal']) && !empty($all_payment_gateways['paypal'])) {
                $paypal_options = $all_payment_gateways['paypal'];
                //Set Paypal Currency
                $currency = $arm_payment_gateways->arm_get_global_currency();
                $sandbox = (isset($paypal_options['paypal_payment_mode']) && $paypal_options['paypal_payment_mode'] == 'sandbox') ? TRUE : FALSE;
                /** Set API Credentials */
                $developer_account_email = $paypal_options['paypal_merchant_email'];
                $api_username = $sandbox ? $paypal_options['sandbox_api_username'] : $paypal_options['live_api_username'];
                $api_password = $sandbox ? $paypal_options['sandbox_api_password'] : $paypal_options['live_api_password'];
                $api_signature = $sandbox ? $paypal_options['sandbox_api_signature'] : $paypal_options['live_api_signature'];
                /* ---------------------------------------------------------------------------- */
                $PayPalConfig = array(
                    'Sandbox' => $sandbox,
                    'APIUsername' => $api_username,
                    'APIPassword' => $api_password,
                    'APISignature' => $api_signature
                );
                $PayPal = new PayPal($PayPalConfig);
                $PayPal->ARMcurrency = $currency;
                $PayPal->ARMsandbox = $sandbox;
            } else {
                $PayPal = false;
            }
            return $PayPal;
        }

        function arm_payment_gateway_form_submit_action($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_manage_coupons, $payment_done, $arm_payment_gateways, $arm_membership_setup, $arm_debug_payment_log_id;

            if ($payment_gateway == 'paypal') {

                // New Payment method code
                //====================================
                $arm_return_data = array();
                $arm_return_data = apply_filters('arm_calculate_payment_gateway_submit_data', $arm_return_data, $payment_gateway, $payment_gateway_options, $posted_data, $entry_id);

                do_action('arm_payment_log_entry', $payment_gateway, 'payment form submit', 'armember', $arm_return_data, $arm_debug_payment_log_id);

                $plan_id = !empty($arm_return_data['arm_plan_id']) ? $arm_return_data['arm_plan_id'] : 0;
                $plan = new ARM_Plan($plan_id);
                $plan_payment_type = $plan->payment_type;

                $plan_action = !empty($arm_return_data['arm_plan_action']) ? $arm_return_data['arm_plan_action'] : 'new_subscription';

                $trial_not_allowed = !empty($arm_return_data['arm_trial_data']) ? 1 : 0;

                $payment_mode = !empty($arm_return_data['arm_payment_mode']) ? $arm_return_data['arm_payment_mode'] : '';

                if(!empty($plan_id) && !empty($entry_id))
                {
                    $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
                    $user_id = $entry_data['arm_user_id'];
                    if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                    }

                    $notify_url = $arm_return_data['arm_extra_vars']['arm_notification_url'];
                    $cancel_url = $arm_return_data['arm_extra_vars']['arm_cancel_url'];
                    $return_url = $arm_return_data['arm_extra_vars']['arm_return_url'];

                    $amount = !empty($arm_return_data['arm_payable_amount']) ? $arm_return_data['arm_payable_amount'] : 0;
                    $amount = str_replace(",", "", $amount);
                    $arm_final_trial_amount = 0;

                    $arm_is_coupon = $arm_is_tax = $arm_is_trial = $arm_tax_amount = $arm_trial_tax_amount = 0;
                    $arm_coupon_data = $arm_tax_data = $arm_trial_data = array();
                    $arm_coupon_discount_type = '';
                    $arm_coupon_on_each_subscriptions = 0;
                    $coupon_code = "";
                    $arm_coupon_discount = "";

                    if(!empty($arm_return_data['arm_recurring_data']['trial']) && ($arm_return_data['allow_trial'] || $arm_return_data['allow_trial'] == '1')){
                        $arm_is_trial = 1;
                        $arm_trial_data = $arm_return_data['arm_recurring_data']['trial'];
                        $arm_final_trial_amount = $arm_return_data['arm_trial_amount'];
                    }

                    $trial_interval = !empty($arm_trial_data) ? $arm_trial_data['interval'] : '';
                    $trial_period = !empty($arm_trial_data) ? $arm_trial_data['period'] : '';

                    $recur_interval = !empty($arm_return_data['arm_recurring_data']) ? $arm_return_data['arm_recurring_data']['interval'] : '';
                    $recur_period = !empty($arm_return_data['arm_recurring_data']) ? $arm_return_data['arm_recurring_data']['period'] : '';
                    $recur_cycles = !empty($arm_return_data['arm_recurring_data']) ? $arm_return_data['arm_recurring_data']['cycles'] : 1;
                    $remained_days = !empty($arm_return_data['arm_remained_days']) ? $arm_return_data['arm_remained_days'] : 0;

                    if(!empty($arm_return_data['arm_coupon_data'])){
                        $arm_is_coupon = 1;
                        $arm_coupon_data = $arm_return_data['arm_coupon_data'];
                        $arm_coupon_on_each_subscriptions = $arm_coupon_data['arm_coupon_on_each_subscriptions'];

                        $coupon_code = $arm_coupon_data['arm_coupon_code'];
                        $arm_coupon_discount = $arm_coupon_data['discount'];

                        $global_currency = $arm_payment_gateways->arm_get_global_currency();
                        $arm_coupon_discount_type = ($arm_coupon_data['discount_type'] != 'percentage') ? $global_currency : "%";                        
                    }

                    if(!empty($arm_return_data['arm_tax_data'])){
                        $arm_is_tax = 1;
                        $arm_tax_data = $arm_return_data['arm_tax_data'];
                        $arm_tax_amount = $arm_tax_data['tax_amount'];
                        $arm_trial_tax_amount = $arm_tax_data['trial_tax_amount'];
                    }
                    
                    if($arm_return_data['arm_trial_amount_for_not_in_plan_trial_flag'] == 1){
                        $arm_is_trial = 1;
                        $arm_final_trial_amount = $arm_return_data['arm_trial_amount_for_not_in_plan_trial'];
                        $trial_period = $recur_period;
                        $trial_interval = $recur_interval;
                    }
                    
                    $arm_user_email = $arm_return_data['arm_user_email'];


                    $custom_var = $entry_id.'|'.$arm_user_email.'|'.$plan_payment_type.'|'.$arm_tax_amount.'|'.$arm_trial_tax_amount;

                    $currency = $arm_payment_gateways->arm_get_global_currency();

                    if ($currency == 'HUF' || $currency == 'JPY' || $currency == 'TWD') {
                        $arm_final_trial_amount = number_format((float)$arm_final_trial_amount, 0, '', '');
                    }
                    else{
                        $arm_final_trial_amount = number_format((float)$arm_final_trial_amount, 2, '.','');
                    }


                    if ($currency == 'HUF' || $currency == 'JPY' || $currency == 'TWD') {
                        $amount = number_format((float)$amount, 0, '', '');
                    }
                    else{
                        $amount = number_format((float)$amount, 2, '.', '');
                    }

                    
                    $form_type = "new";
                    

                    $plan_form_data = "";
                    if ($plan->is_recurring() && $payment_mode == 'auto_debit_subscription') {
                        $cmd = "_xclick-subscriptions";
                        
                        $plan_form_data .= '<input type="hidden" name="a3" value="' . $amount . '" />';
                        $plan_form_data .= '<input type="hidden" name="p3" value="' . $recur_interval . '" />';
                        $plan_form_data .= '<input type="hidden" name="t3" value="' . $recur_period . '" />';
                        // PayPal re-attempts failed recurring payments
                        $plan_form_data .= '<input type="hidden" name="sra" value="1" />';
                        // Set recurring payments until cancelled.
                        $plan_form_data .= '<input type="hidden" name="src" value="1" />';
                        $plan_form_data .= '<input type="hidden" name="no_note" value="1" />';
                        $modify_val = ($form_type == 'modify') ? '1' : '0';
                        $plan_form_data .= '<input type="hidden" name="modify" value="' . $modify_val . '" />';
                        if ($recur_cycles > 1) {
                            //Set recurring payments to stop after X billing cycles
                            $plan_form_data .= '<input type="hidden" name="srt" value="' . $recur_cycles . '" />';
                        }
                        if(($arm_is_trial && ($plan_action == 'new_subscription') || ($plan_action == 'change_subscription' && !empty($arm_return_data['arm_coupon_data']) && $arm_coupon_discount> 0) ) || $remained_days > 0) {
                            $plan_form_data .= '<input type="hidden" name="a1" value="' . $arm_final_trial_amount . '" />';
                            $plan_form_data .= '<input type="hidden" name="p1" value="' . $trial_interval . '" />';
                            $plan_form_data .= '<input type="hidden" name="t1" value="' . $trial_period . '" />';
                        }
                    } else if ($plan->is_recurring() && $payment_mode == 'manual_subscription') {
                        $cmd = "_xclick";


                        if ($arm_is_trial && ($arm_final_trial_amount == 0 || $arm_final_trial_amount == '0.00')) {
                            $return_array = $extraParam = array();
                            if (is_user_logged_in()) {
                                $current_user_id = get_current_user_id();
                                $return_array['arm_user_id'] = $current_user_id;
                            }
                            $user_detail = get_userdata($user_id);
                            $arm_first_name = $user_detail->first_name;
                            $arm_last_name = $user_detail->last_name;
            
                            $return_array['arm_first_name'] = $arm_first_name;
                            $return_array['arm_last_name'] = $arm_last_name;
                            $return_array['arm_plan_id'] = $plan->ID;
                            $return_array['arm_payment_gateway'] = 'paypal';
                            $return_array['arm_payment_type'] = $plan->payment_type;
                            $return_array['arm_token'] = '-';
                            $return_array['arm_payer_email'] = $arm_user_email;
                            $return_array['arm_receiver_email'] = '';
                            $return_array['arm_transaction_id'] = '-';
                            $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                            $return_array['arm_transaction_status'] = 'completed';
                            $return_array['arm_payment_mode'] = 'manual_subscription';
                            $return_array['arm_payment_date'] = date('Y-m-d H:i:s');
                            $return_array['arm_amount'] = 0;
                            $return_array['arm_currency'] = $currency;
                            $return_array['arm_coupon_code'] = $coupon_code;
                            $return_array['arm_coupon_discount'] = $arm_coupon_discount;
                            $return_array['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                            $return_array['arm_extra_vars'] = '';
                            $return_array['arm_is_trial'] = $arm_is_trial;
                            $return_array['arm_created_date'] = current_time('mysql');
                            $return_array['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subscriptions;
                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
                            $is_free_manual = true;
                            do_action('arm_after_paypal_free_manual_payment', $plan, $payment_log_id, $arm_is_trial, $coupon_code, $extraParam);
                            global $payment_done;
                            $payment_done['status'] = true;
                            $payment_done['log_id'] = $payment_log_id;
                            $payment_done['entry_id'] = $entry_id;
                            $payment_done['zero_amount_paid'] = true;
                            return $payment_done;
                        }else{
                            if($arm_is_trial && $arm_final_trial_amount > 0){
                                $plan_form_data .= "<input type='hidden' name='amount' value='".$arm_final_trial_amount."' />";
                            }else{
                                if($amount == 0)
                                {
                                    $user_detail = get_userdata($user_id);
                                    $arm_first_name = $user_detail->first_name;
                                    $arm_last_name = $user_detail->last_name;
                    
                                    $return_array['arm_first_name'] = $arm_first_name;
                                    $return_array['arm_last_name'] = $arm_last_name;
                                    $return_array['arm_plan_id'] = $plan->ID;
                                    $return_array['arm_payment_gateway'] = 'paypal';
                                    $return_array['arm_payment_type'] = $plan->payment_type;
                                    $return_array['arm_token'] = '-';
                                    $return_array['arm_payer_email'] = $arm_user_email;
                                    $return_array['arm_receiver_email'] = '';
                                    $return_array['arm_transaction_id'] = '-';
                                    $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                                    $return_array['arm_transaction_status'] = 'completed';
                                    $return_array['arm_payment_mode'] = 'manual_subscription';
                                    $return_array['arm_payment_date'] = date('Y-m-d H:i:s');
                                    $return_array['arm_amount'] = 0;
                                    $return_array['arm_currency'] = $currency;
                                    $return_array['arm_coupon_code'] = $coupon_code;
                                    $return_array['arm_coupon_discount'] = $arm_coupon_discount;
                                    $return_array['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                                    $return_array['arm_extra_vars'] = '';
                                    $return_array['arm_is_trial'] = $arm_is_trial;
                                    $return_array['arm_created_date'] = current_time('mysql');
                                    $return_array['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subscriptions;
                                    $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
                                    $is_free_manual = true;
                                    do_action('arm_after_paypal_free_manual_payment', $plan, $payment_log_id, $arm_is_trial, $coupon_code, $extraParam);
                                    global $payment_done;
                                    $payment_done['status'] = true;
                                    $payment_done['log_id'] = $payment_log_id;
                                    $payment_done['entry_id'] = $entry_id;
                                    $payment_done['zero_amount_paid'] = true;
                                    return $payment_done;
                                }
                                $plan_form_data .= "<input type='hidden' name='amount' value='".$amount."' />";
                            }
                        }
                    } else {
                        $cmd = "_xclick";
                        $extraParam = array();
                        if ($amount == 0 || $amount == '0.00') {
                            $return_array = array();
                            if (is_user_logged_in()) {
                                $current_user_id = get_current_user_id();
                                $return_array['arm_user_id'] = $current_user_id;
                            }
                            $user_detail = get_userdata($user_id);
                            $arm_first_name = $user_detail->first_name;
                            $arm_last_name = $user_detail->last_name;
        
                            $return_array['arm_first_name'] = $arm_first_name;
                            $return_array['arm_last_name'] = $arm_last_name;
                            $return_array['arm_plan_id'] = $plan->ID;
                            $return_array['arm_payment_gateway'] = 'paypal';
                            $return_array['arm_payment_type'] = $plan->payment_type;
                            $return_array['arm_token'] = '-';
                            $return_array['arm_payer_email'] = $arm_user_email;
                            $return_array['arm_receiver_email'] = '';
                            $return_array['arm_transaction_id'] = '-';
                            $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                            $return_array['arm_transaction_status'] = 'completed';
                            $return_array['arm_payment_mode'] = '';
                            $return_array['arm_payment_date'] = date('Y-m-d H:i:s');
                            $return_array['arm_amount'] = 0;
                            $return_array['arm_currency'] = $currency;
                            $return_array['arm_coupon_code'] = $coupon_code;
                            $return_array['arm_coupon_discount'] = $arm_coupon_discount;
                            $return_array['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                            $return_array['arm_extra_vars'] = '';
                            $return_array['arm_is_trial'] = $arm_is_trial;
                            $return_array['arm_created_date'] = current_time('mysql');
                            $return_array['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subscriptions;
                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
                            $is_free_manual = true;
                            do_action('arm_after_paypal_free_payment', $plan, $payment_log_id, $arm_is_trial, $coupon_code, $extraParam);
                            global $payment_done;
                            $payment_done['status'] = true;
                            $payment_done['log_id'] = $payment_log_id;
                            $payment_done['entry_id'] = $entry_id;
                            $payment_done['zero_amount_paid'] = true;
                            return $payment_done;
                        }
                        $plan_form_data .= '<input type="hidden" name="amount" value="' . $amount . '" />';
                    }

                    $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                    $paypal_options = !empty($all_payment_gateways['paypal']) ? $all_payment_gateways['paypal'] : '';
                    $arm_paypal_language = isset($paypal_options['language']) ? $paypal_options['language'] : 'en_US';
                    $sandbox = (isset($paypal_options['paypal_payment_mode']) && $paypal_options['paypal_payment_mode'] == 'sandbox') ? 'sandbox.' : '';

                    $paypal_form = '<form name="_xclick" id="arm_paypal_form" action="https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr" method="post">';
                    $paypal_form .= '<input type="hidden" name="cmd" value="' . $cmd . '" />';
                    $paypal_form .= '<input type="hidden" name="business" value="' . $paypal_options['paypal_merchant_email'] . '" />';
                    $paypal_form .= '<input type="hidden" name="notify_url" value="' . esc_url($notify_url) . '" />';
                    $paypal_form .= '<input type="hidden" name="cancel_return" value="' . esc_url($cancel_url) . '" />';
                    $paypal_form .= '<input type="hidden" name="return" value="' . esc_url($return_url) . '" />';
                    $paypal_form .= '<input type="hidden" name="rm" value="2" />';
                    $paypal_form .= '<input type="hidden" name="lc" value="' . $arm_paypal_language . '" />';
                    $paypal_form .= '<input type="hidden" name="no_shipping" value="1" />';
                    $paypal_form .= '<input type="hidden" name="custom" value="' . $custom_var . '" />';
                    $paypal_form .= '<input type="hidden" name="on0" value="user_email" />';
                    $paypal_form .= '<input type="hidden" name="os0" value="' . $arm_user_email . '" />';
                    //$paypal_form .= '<input type="hidden" name="on1" value="user_plan">';
                    //$paypal_form .= '<input type="hidden" name="os1" value="' . $plan_id . '">';
                    $paypal_form .= '<input type="hidden" name="currency_code" value="' . $currency . '" />';
                    $paypal_form .= '<input type="hidden" name="page_style" value="primary" />';
                    $paypal_form .= '<input type="hidden" name="charset" value="UTF-8" />';
                    $paypal_form .= '<input type="hidden" name="item_name" value="' . $plan->name . '" />';
                    $paypal_form .= '<input type="hidden" name="item_number" value="1" />';
                    $paypal_form .= $plan_form_data;
                    $paypal_form .= '<input type="submit" style="display:none;" name="cbt" value="' . __("Click here to continue", 'ARMember') . '" />';
                    $paypal_form .= '<input type="submit" value="Pay with PayPal!" style="display:none;" />';
                    $paypal_form .= '</form>';


                    do_action('arm_payment_log_entry', $payment_gateway, 'payment form redirected data', 'armember', $paypal_form, $arm_debug_payment_log_id);

                    $paypal_form .= '<script data-cfasync="false" type="text/javascript" language="javascript">document.getElementById("arm_paypal_form").submit();</script>';

                    $return = array('status' => 'success', 'type' => 'redirect', 'message' => $paypal_form);
                    echo json_encode($return);
                    exit;

                }
            }
        }

        function arm_paypal_api_handle_response() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_class, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_manage_coupons, $payment_done, $is_multiple_membership_feature, $arm_debug_payment_log_id;

            if (isset($_REQUEST['arm-listener']) && in_array($_REQUEST['arm-listener'], array('arm_paypal_api', 'arm_paypal_notify'))) {

                $payment_gateway = 'paypal';
                do_action('arm_payment_log_entry', $payment_gateway, 'webhook data', 'payment_gateway', $_REQUEST, $arm_debug_payment_log_id);

                if (!empty($_POST['txn_id']) || !empty($_POST['subscr_id'])) {
                    $req = 'cmd=_notify-validate';
                    foreach ($_POST as $key => $value) {
                        $value = urlencode(stripslashes($value));
                        $req .= "&$key=$value";
                    }
                    $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                    if(!empty($all_payment_gateways['paypal'])){
                        $options = $all_payment_gateways['paypal'];
                        $request = new WP_Http();
                        /* For HTTP1.0 Request */
                        $requestArr = array(
                            "sslverify" => false,
                            "ssl" => true,
                            "body" => $req,
                            "timeout" => 20,
                        );
                        /* For HTTP1.1 Request */
                        $requestArr_1_1 = array(
                            "httpversion" => '1.1',
                            "sslverify" => false,
                            "ssl" => true,
                            "body" => $req,
                            "timeout" => 20,
                        );
                        $response = array();

                        if(!empty($options['paypal_payment_mode']) && $options['paypal_payment_mode'] == 'sandbox'){
                            $url = "https://www.sandbox.paypal.com/cgi-bin/webscr/";
                            $response_1_1 = $request->post($url, $requestArr_1_1);
                            if (!is_wp_error($response_1_1) && $response_1_1['body'] == 'VERIFIED') {
                                $response = $response_1_1;
                            } else {
                                $response = $request->post($url, $requestArr);
                            }
                        } else {
                            $url = "https://www.paypal.com/cgi-bin/webscr/";
                            $response_1_0 = $request->post($url, $requestArr);
                            if (!is_wp_error($response_1_0) && $response_1_0['body'] == 'VERIFIED') {
                                $response = $response_1_0;
                            } else {
                                $response = $request->post($url, $requestArr_1_1);
                            }
                        }

                        do_action('arm_payment_log_entry', $payment_gateway, 'verified webhook response', 'payment_gateway', $response, $arm_debug_payment_log_id);

                        if (!is_wp_error($response) && $response['body'] == 'VERIFIED') {
                            $paypalLog = $_POST;
                            $customs = explode('|', $_POST['custom']);
                            $entry_id = $customs[0];
                            $entry_email = $customs[1];
                            $arm_payment_type = $customs[2];
                            $arm_tax_amount = (isset($customs[3]) && $customs[3] !='') ? $customs[3] : 0;
                            $arm_trial_tax_amount = (isset($customs[4]) && $customs[4] !='') ? $customs[4] : 0;
                            $txn_id = isset($_POST['txn_id']) ? $_POST['txn_id'] : '';
                            $arm_token = isset($_POST['subscr_id']) ? $_POST['subscr_id'] : '';
                            $txn_type = isset($_POST['txn_type']) ? $_POST['txn_type'] : '';


                            $arm_subscription_field_name = "subscr_id";
                            $arm_token_field_name = "subscr_id";
                            $arm_transaction_id_field_name = "txn_id";

                            $_POST['arm_payer_email'] = !empty($_POST['payer_email']) ? $_POST['payer_email'] : $entry_email;

                            $user_id = 0;


                            $arm_paypal_log_data = array(
                                'txn_type' => $txn_type,
                                'token' => $arm_token,
                                'txn_id' => $txn_id,
                                'entry_id' => $entry_id,
                                'subscription_field_name' => $arm_subscription_field_name,
                                'token_field_name' => $arm_token_field_name,
                                'transaction_id_field_name' => $arm_transaction_id_field_name,
                            );

                            do_action('arm_payment_log_entry', $payment_gateway, 'webhook submitted data for entry', 'payment_gateway', $arm_paypal_log_data, $arm_debug_payment_log_id);

                            if(!empty($txn_id) || !empty($arm_token)){
                                switch($txn_type){
                                    case 'subscr_signup':
                                    case 'subscr_payment':
                                    case 'recurring_payment':
                                    case 'web_accept':
                                        if(!empty($arm_token) && $txn_type == "subscr_signup" && (isset($_POST['mc_amount1']) && ($_POST['mc_amount1'] == 0 || $_POST['mc_amount1'] == '0.00')))
                                        {
                                            $arm_transient_name = "arm_paypal_subs_trans_".$arm_token;
                                            $arm_transient_status = $arm_global_settings->arm_transient_get_action($arm_transient_name);
                                            if($arm_transient_status == 0)
                                            {
                                                $arm_transient_time = DAY_IN_SECONDS / 2;
                                                $arm_global_settings->arm_transient_set_action($arm_transient_name, $arm_token, $arm_transient_time);
                                            }
                                            else
                                            {
                                                die();
                                            }
                                        }
                                        else if($txn_type == "subscr_signup")
                                        {
					    //No Need this txn_type because subscr_payment sending same details with transaction ID
                                            die();
                                        }

                                        $arm_webhook_save_membership_data = array();
                                        $arm_webhook_save_membership_data = apply_filters('arm_modify_payment_webhook_data', $arm_webhook_save_membership_data, $_POST, 'paypal', $arm_token, $txn_id, $entry_id, $arm_token, $arm_subscription_field_name, $arm_token_field_name, $arm_transaction_id_field_name);
                                        break;
                                    case 'subscr_cancel':
                                    case 'recurring_payment_profile_cancel':
                                        $is_log = true;
                                        $user_id = 0;
                                        $arm_cancel_amount = 0;
                                        $arm_transaction_id = '';

                                        $arm_find_user_subs_id = $wpdb->get_row("SELECT * FROM $wpdb->usermeta WHERE meta_value LIKE '%".$arm_token."%' AND meta_key LIKE 'arm_user_plan_%'");
                                        if(!empty($arm_find_user_subs_id->user_id))
                                        {
                                            $user_id = $arm_find_user_subs_id->user_id;
                                            $userPlanDatameta = maybe_unserialize($arm_find_user_subs_id->meta_value);
                                            $planData = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                            $planDetail = $planData['arm_current_plan_detail'];
                                            $entry_plan = $planDetail['arm_subscription_plan_id'];
                                            $arm_cancel_amount = $planDetail['arm_subscription_plan_amount'];
                                        }
                                        else
                                        {
                                            $arm_transaction_id_arr = $wpdb->get_row("SELECT `arm_transaction_id`,`arm_plan_id`, `arm_user_id`, `arm_amount` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_token`='" . $arm_token . "' AND `arm_payment_gateway` = 'paypal'",ARRAY_A);

                                            $user_id = $arm_transaction_id_arr['arm_user_id'];

                                            $arm_cancel_amount = $arm_transaction_id_arr['arm_amount'];
                                            $arm_transaction_id = $arm_transaction_id_arr['arm_transaction_id'];
    										$entry_plan = $arm_transaction_id_arr['arm_plan_id'];

                                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                            $planData = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                            $planDetail = $planData['arm_current_plan_detail'];
                                        }

                                        if (!empty($planDetail)) {
                                            $plan = new ARM_Plan(0);
                                            $plan->init((object) $planDetail);
                                        } else {
                                            $plan = new ARM_Plan($entry_plan);
                                        }
                    
                                        $plan_cycle = isset($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                                        $paly_cycle_data = $plan->prepare_recurring_data($plan_cycle);

                                        $subscr_cancel_log_data = array(
                                            'cancel_rec_time' => $paly_cycle_data['rec_time'],
                                            'cancel_plan_action' => $plan->options['cancel_plan_action'],
                                            'user_id' => $user_id,
                                            'entry_plan' => $entry_plan,
                                            'arm_is_canceled' => $planData['arm_cencelled_plan'],
                                        );

                                        do_action('arm_payment_log_entry', $payment_gateway, 'webhook cancel subscription data', 'payment_gateway', $subscr_cancel_log_data, $arm_debug_payment_log_id);

                                        if(empty($user_id) || (!empty($planData['arm_paypal']['arm_subscr_id']) && $planData['arm_paypal']['arm_subscr_id'] != $arm_token) || (!empty($planData['arm_paypal']['subscr_id']) && $planData['arm_paypal']['subscr_id'] != $arm_token))
                    					{
                    						return;
                    					}

                                        if($plan->options['cancel_plan_action'] != "on_expire")
                                        {
                                            if (!empty($planData) && (!empty($planData['arm_paypal']['arm_subscr_id']) || !empty($arm_token)) && empty($planData['arm_cencelled_plan']) )
                                            {
                                                $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'cancel_subscription');
                                            
                                                do_action('arm_cancel_subscription', $user_id, $entry_plan);
                                                $arm_subscription_plans->arm_clear_user_plan_detail($user_id, $entry_plan);
                                            

                                                $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                                                if ($arm_subscription_plans->isPlanExist($cancel_plan_act)) {
                                                     $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $entry_plan, $user_id);
                                                }

                                                do_action('arm_cancel_subscription_payment_log_entry', $user_id, $entry_plan, 'paypal', $arm_token, $arm_transaction_id, '', '', $arm_cancel_amount);

                                                do_action('arm_after_recurring_payment_cancelled_outside', $user_id, $entry_plan, 'paypal');
                                            }
                                        }
                                        else if((!empty($planData['arm_paypal']['arm_subscr_id']) || !empty($arm_token)) && is_array($planData) && $plan->options['cancel_plan_action']== "on_expire") 
                                        {
                                                //Update plan canceled in usermeta
                                                $planData['arm_cencelled_plan'] = 'yes';
                                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $planData);
                                        }
                                        break;
                                    //case 'subscr_eot':
                                    //case 'recurring_payment_expired':
                                    case 'subscr_failed':
                                    case 'recurring_payment_failed':
                                    case 'recurring_payment_suspended':
                                    case 'recurring_payment_suspended_due_to_max_failed_payment':
                                        $arm_find_user_subs_id = $wpdb->get_row("SELECT * FROM $wpdb->usermeta WHERE meta_value LIKE '%".$arm_token."%' AND meta_key LIKE 'arm_user_plan_%'");

                                        if(!empty($arm_find_user_subs_id->id))
                                        {
                                            $user_id = $arm_find_user_subs_id->id;
                                        }
                                        else
                                        {
                                            $arm_transaction_id_arr = $wpdb->get_row("SELECT `arm_transaction_id`,`arm_plan_id`, `arm_user_id`, `arm_amount` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_token`='" . $arm_token . "' AND `arm_payment_gateway` = 'paypal'",ARRAY_A);
                                            $user_id = $arm_transaction_id_arr['arm_user_id'];
                                        }
                                        
                                        
                                        $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                        if (!empty($plan_ids) && is_array($plan_ids)) {
                                            foreach ($plan_ids as $plan_id) {
                                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                                if (!empty($planData)) {
                                                    $subscr_id = $planData['arm_paypal']['arm_subscr_id'];
                                                    if ($plan_id == $entry_plan && $subscr_id == $arm_token) {
                                                        $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $entry_plan, 'user_id' => $user_id, 'action' => 'failed_payment'));
                                                        $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $entry_plan, 'user_id' => $user_id, 'action' => "failed_payment"), true);
                                                        do_action('arm_after_recurring_payment_stopped_outside', $user_id, $plan_id, 'paypal');
                                                    }
                                                }
                                            }
                                        }
                                        break;
                                    default:
                                        
                                        do_action('arm_handle_paypal_unknown_error_from_outside', $entry_data['arm_user_id'], $entry_data['arm_plan_id'], $_POST['txn_type']);
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        function arm_cancel_paypal_subscription($user_id, $plan_id) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;
            if (!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0) {
                $arm_cancel_subscription_data = array();
                $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, 'paypal', 'arm_subscr_id', '', '');

                $planData = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();
                $arm_user_payment_gateway = !empty($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '';
                if(strtolower($arm_user_payment_gateway) == "paypal"){

                    do_action('arm_payment_log_entry', 'paypal', 'cancel subscription plan data', 'armember', $arm_cancel_subscription_data, $arm_debug_payment_log_id);

                    $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                    $subscr_id = !empty($_POST['subscr_id']) ? $_POST['subscr_id'] : '';
                    $arm_subscr_id = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : $subscr_id;
                    $arm_customer_id = !empty($arm_cancel_subscription_data['arm_customer_id']) ? $arm_cancel_subscription_data['arm_customer_id'] : '';
                    $arm_transaction_id = !empty($arm_cancel_subscription_data['arm_transaction_id']) ? $arm_cancel_subscription_data['arm_transaction_id'] : '';

                    $arm_cancel_amount = !empty($arm_cancel_subscription_data['arm_cancel_amount']) ? $arm_cancel_subscription_data['arm_cancel_amount'] : 0;

                    if($arm_payment_mode == "auto_debit_subscription"){
                        $this->arm_immediate_cancel_paypal_payment($arm_subscr_id, $user_id, $plan_id, $planData);
                    }

                    if(!empty($arm_subscription_cancel_msg))
                    {
                        return;
                    }

                    do_action('arm_cancel_subscription_payment_log_entry', $user_id, $plan_id, 'paypal', $arm_subscr_id, $arm_transaction_id, $arm_customer_id, $arm_payment_mode, $arm_cancel_amount);
                }
            }
        }

        function arm_update_new_subscr_gateway_outside_func($payment_gateways = array()) {
            global $payment_done;
            if (isset($payment_done['zero_amount_paid']) && $payment_done['zero_amount_paid'] == true) {
                array_push($payment_gateways, 'paypal');
            }
            return $payment_gateways;
        }

        function arm_update_user_meta_after_renew_outside_func($user_id, $log_detail, $plan_id, $payment_gateway) {
            global $payment_done;
            if (isset($payment_don['zero_amount_paid']) && $payment_done['zero_amount_paid'] == true) {
                
            }
        }

        function arm_change_pending_gateway_outside($user_pending_pgway, $plan_ID, $user_id) {
            global $is_free_manual, $ARMember;
            /*if ($is_free_manual) {
                $key = array_search('paypal', $user_pending_pgway);
                unset($user_pending_pgway[$key]);
            }*/
            $key = array_search('paypal', $user_pending_pgway);
            unset($user_pending_pgway[$key]);
            return $user_pending_pgway;
        }

    }

}
global $arm_paypal;
$arm_paypal = new ARM_Paypal();