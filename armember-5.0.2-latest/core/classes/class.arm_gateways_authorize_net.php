<?php

if (!class_exists('ARM_authorize_net')) {

    class ARM_authorize_net {

        function __construct() {
            add_action('arm_payment_gateway_validation_from_setup', array($this, 'arm_payment_gateway_form_submit_action'), 10, 4);
            add_action('arm_cancel_subscription_gateway_action', array($this, 'arm_cancel_authorize_net_subscription'), 10, 2);
            //add_action('wp', array($this, 'arm_authorize_net_api_handle_response'), 5);

            add_filter('arm_payment_gateway_trial_allowed', array($this, 'arm_autho_trial_allowed_or_not'), 10, 5);

            add_action('arm_on_expire_cancel_subscription', array($this, 'arm_cancel_subscription_instant'), 10, 4);
        }

        function arm_autho_trial_allowed_or_not($trial_not_allowed, $arm_plan_id, $payment_gateway, $payment_gateway_options, $posted_data)
        {
            if($payment_gateway == "authorize_net"  && (!empty($posted_data['arm_selected_payment_mode']) && $posted_data['arm_selected_payment_mode'] == "auto_debit_subscription") )
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
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;

            $plan_id = isset($plan->ID) ? $plan->ID : 0;
            $arm_cancel_subscription_data = array();
            $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, 'authorize_net', 'subscription_id', '', '');


            $arm_debug_log_data = array(
                'user_id' => $user_id,
                'plan' => $plan,
                'cancel_plan_action' => $cancel_plan_action,
                'planData' => $planData,
                'cancel_subscription_data' => $arm_cancel_subscription_data,
            );
            do_action('arm_payment_log_entry', 'authorize_net', 'on expire cancel subscription', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

            $arm_plan_data = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();
            $arm_user_payment_gateway = !empty($arm_plan_data['arm_user_gateway']) ? $arm_plan_data['arm_user_gateway'] : '';
            if(!empty($arm_cancel_subscription_data) && (!empty($arm_user_payment_gateway) && strtolower($arm_user_payment_gateway) == "authorize_net"))
            {
                $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                $arm_subscr_id = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : '';
                $arm_customer_id = !empty($arm_cancel_subscription_data['arm_customer_id']) ? $arm_cancel_subscription_data['arm_customer_id'] : '';
                $arm_transaction_id = !empty($arm_cancel_subscription_data['arm_transaction_id']) ? $arm_cancel_subscription_data['arm_transaction_id'] : '';
            
                $this->arm_cancel_authorize_net_subscription_immediately($arm_subscr_id, $user_id, $plan_id, $arm_plan_data);
            }
        }

        function arm_cancel_authorize_net_subscription_immediately($subscr_id, $user_id, $plan_id, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;

            $response = "";
            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            $autho_options = $all_payment_gateways['authorize_net'];

            $arm_debug_log_data = array(
                'subscr_id' => $subscr_id,
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'planData' => $planData,
            );
            do_action('arm_payment_log_entry', 'authorize_net', 'cancel subscription request', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

            self::arm_LoadAuthorizeNetLibrary($autho_options);
            if (class_exists('AuthorizeNetARB')) 
            {   
                try
                {
                    $request = new AuthorizeNetARB();

                    $refId = 'ref' . time();
                    $request->setRefId($refId);

                    $response = $request->cancelSubscription($subscr_id);
                    if ($response->isOk()) 
                    {
                        $planData['subscription_id'] = '';
                        update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                    }
                }
                catch(Exception $e)
                {
                    do_action('arm_payment_log_entry', 'authorize_net', 'cancel subscription error', 'armember', $e->getMessage(), $arm_debug_payment_log_id);
                    $arm_enable_debug_mode = isset($autho_options['enable_debug_mode']) ? $autho_options['enable_debug_mode'] : 0;
                    if($arm_enable_debug_mode)
                    {
                        $arm_subscription_cancel_msg = __("Error in cancel subscription from Authorize.net.", "ARMember")." ".$e->getMessage();
                    }
                    else
                    {
                        $common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                        $arm_subscription_cancel_msg = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel. Please contact the site administrator.", 'ARMember');
                    }
                }
            }
            return $response;
        }

        function arm_LoadAuthorizeNetLibrary($config = array()) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways;
            if (!empty($config)) {
                if (file_exists(MEMBERSHIP_DIR . "/lib/autoload.php")) {
                    require_once (MEMBERSHIP_DIR . "/lib/autoload.php"); //Load Authorize.Net lib
                }
                $autho_options_mode = ($config['autho_mode'] == 'sandbox') ? true : false;
                $currency = $arm_payment_gateways->arm_get_global_currency();
                @define("AUTHORIZENET_API_LOGIN_ID", $config['autho_api_login_id']);
                @define("AUTHORIZENET_TRANSACTION_KEY", $config['autho_transaction_key']);
                @define("AUTHORIZENET_SANDBOX", $autho_options_mode);
                @define("AUTHORIZENET_CURRENCY", $currency);
            }
        }

        function arm_payment_gateway_form_submit_action($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $authorize_net_auth, $payment_done, $paid_trial_stripe_payment_done, $arm_manage_coupons, $arm_transaction, $arm_members_class, $arm_manage_communication, $arm_debug_payment_log_id;

            if ($payment_gateway == 'authorize_net') {
                $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
                
                if (!empty($entry_data) && !empty($posted_data[$payment_gateway])) {
                    $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                    if (isset($all_payment_gateways['authorize_net']) && !empty($all_payment_gateways['authorize_net'])) {
                        $user_email_add = $entry_data['arm_entry_email'];
                        $user_id = $entry_data['arm_user_id'];
                        
                        if(!empty($user_id)){
                            $user_info = get_userdata($user_id);
                            $user_firstname = $user_info->first_name;
                            $user_lastname = $user_info->last_name;
                        }else{
                            $user_firstname = $posted_data['first_name'];
                            $user_lastname = $posted_data['last_name'];
                        }
                        
                        $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                        $payment_cycle = $entry_values['arm_selected_payment_cycle']; 
                         $tax_percentage = isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0; 
                        $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",",$entry_values['arm_user_old_plan']) : array();
                        $setup_id = (isset($entry_values['setup_id']) && !empty($entry_values['setup_id'])) ? $entry_values['setup_id'] : 0 ; 

                        $plan_id = (!empty($posted_data['subscription_plan'])) ? $posted_data['subscription_plan'] : 0;
                        if ($plan_id == 0) {
                            $plan_id = (!empty($posted_data['_subscription_plan'])) ? $posted_data['_subscription_plan'] : 0;
                        }

                        $plan = new ARM_Plan($plan_id);

                        // Filter for calculate submitted data
                        //--------------------------------------
                        $arm_return_data = array();
                        $arm_return_data = apply_filters('arm_calculate_payment_gateway_submit_data', $arm_return_data, $payment_gateway, $payment_gateway_options, $posted_data, $entry_id);

                        do_action('arm_payment_log_entry', 'authorize_net', 'form submit log', 'armember', $arm_return_data, $arm_debug_payment_log_id);

                        //--------------------------------------

                        $payment_mode = '';
                        if ($plan->is_recurring()) {
                            $payment_mode = !empty($arm_return_data['arm_payment_mode']) ? $arm_return_data['arm_payment_mode'] : 'manual_subscription';
                        } else {
                            $payment_mode = '';
                        }

                        $plan_action = !empty($arm_return_data['arm_plan_action']) ? $arm_return_data['arm_plan_action'] : 'new_subscription';

                        $plan_payment_type = $plan->payment_type;
                        if($plan->is_recurring())
                        {
                            $amount = !empty($arm_return_data['arm_recurring_data']) ? $arm_return_data['arm_recurring_data']['amount'] : 0;
                        }
                        else{
                            $amount = !empty($plan->amount) ? $plan->amount : 0;
                        }
                        
                        $amount = str_replace(",", "", $amount);

                        $amount = number_format((float)$amount, 2, '.','');
                   
                        $autho_card_detail = $posted_data[$payment_gateway];
                        $card_number = $autho_card_detail['card_number'];
                        $exp_month = $autho_card_detail['exp_month'];
                        $exp_year = $autho_card_detail['exp_year'];
                        $cvc = $autho_card_detail['cvc'];
                        $payment_data = array();
                        $first_payment_data = array();
                        $arm_is_trial = '0';
                        $arm_is_first_trial = '0';

                        $autho_options = $all_payment_gateways['authorize_net'];
                        $arm_authorise_enable_debug_mode = isset($autho_options['enable_debug_mode']) ? $autho_options['enable_debug_mode'] : 0;
                        $arm_help_link = '<a href="https://developer.authorize.net/api/reference/features/errorandresponsecodes.html" target="_blank">'.__('Click Here', 'ARMember').'</a>';
                        self::arm_LoadAuthorizeNetLibrary($autho_options);
                        $maskCCNum = $arm_transaction->arm_mask_credit_card_number($card_number);

                        $extraParam = array('card_number' => $maskCCNum, 'plan_amount' => $amount, 'paid_amount' => $amount, 'tax_percentage' => $tax_percentage);
                        $extraFirstParam = array('card_number' => $maskCCNum, 'plan_amount' => $amount, 'paid_amount' => $amount, 'tax_percentage' => $tax_percentage);

                        // Coupon Details 
                        $discount_amt = $coupon_amount = $arm_coupon_discount = 0;
                        $arm_coupon_discount_type = $coupon_code = '';
                        if ($arm_manage_coupons->isCouponFeature && isset($posted_data['arm_coupon_code']) && !empty($posted_data['arm_coupon_code']) && !empty($arm_return_data['arm_coupon_data'])) {

                            $coupon_amount = !empty($arm_return_data['arm_coupon_data']['coupon_amt']) ? $arm_return_data['arm_coupon_data']['coupon_amt'] : 0;
                            $coupon_amount = str_replace(",", "", $coupon_amount);

                            $discount_amt = isset($arm_return_data['arm_coupon_data']['total_amt']) ? $arm_return_data['arm_coupon_data']['total_amt'] : $amount;
                            $discount_amt = str_replace(",", "", $discount_amt);

                            $arm_coupon_discount = $arm_return_data['arm_coupon_data']['discount'];

                            $arm_coupon_on_each_subscriptions = isset($arm_return_data['arm_coupon_data']['arm_coupon_on_each_subscriptions']) ? $arm_return_data['arm_coupon_data']['arm_coupon_on_each_subscriptions'] : '0';

                            $global_currency = $arm_payment_gateways->arm_get_global_currency();

                            $arm_coupon_discount_type = ($arm_return_data['arm_coupon_data']['discount_type'] != 'percentage') ? $global_currency : "%";

                            if ( isset($arm_return_data['arm_coupon_data']['status']) && $arm_return_data['arm_coupon_data']['status'] == "success" ) {
                                $coupon_code = $posted_data['arm_coupon_code'];
                            }
                            if (!empty($coupon_amount) && $coupon_amount > 0) {
                                $extraFirstParam['coupon'] = array(
                                    'coupon_code' => $posted_data['arm_coupon_code'],
                                    'amount' => $coupon_amount,
                                    'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                );
                                if(!empty($arm_coupon_on_each_subscriptions))
                                {
                                    $extraParam['coupon'] = array(
                                        'coupon_code' => $posted_data['arm_coupon_code'],
                                        'paid_amount' => $arm_return_data['arm_coupon_data']['arm_coupon_amount_on_each_subs'],
                                        'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                    );
                                }
                            }
                        } else {
                            $posted_data['arm_coupon_code'] = '';
                        }

                        $payment_done = array();
                        $tax_amount  = 0;

                        if ($plan->is_recurring() && $payment_mode == 'auto_debit_subscription') {

                            if (!($plan->is_support_authorize_net($payment_cycle))) {

                                $err_msg = __('Payment through Authorize.Net is not supported for selected plan.', 'ARMember');
                                return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                            }

                            //======================= Second Payment Start ====================================//
                            if (class_exists('AuthorizeNet_Subscription')) {
                                $recur_period = $arm_return_data['arm_recurring_data']['period'];
                                $recur_interval = $arm_return_data['arm_recurring_data']['interval'];
                                $recur_cycles = $arm_return_data['arm_recurring_data']['cycles'];
                                if ($plan_action == 'new_subscription') {
                                    if (!empty($recurring_data['trial'])) {
                                        $recur_cycles = (!empty($recur_cycles) && $recur_cycles != 'infinite') ? ($recur_cycles + 1) : $recur_cycles;
                                    }
                                }
                                $recur_cycles = (!empty($recur_cycles) && $recur_cycles != 'infinite') ? ($recur_cycles - 1) : 9999;

                                $recurring_type = (!empty($recur_period)) ? $recur_period : 'days';
                                if ($recurring_type == "D" || $recurring_type == 'days') {
                                    $recurring_type = "days";
                                } else if ($recurring_type == "M") {
                                    $recurring_type = "months";
                                } else if ($recurring_type == "Y") {
                                    $recurring_type = "years";
                                }
                                $arm_startdate_with_trial = false;
                                if ($plan_action == 'new_subscription') {
                                    $arm_startdate_with_trial = true;
                                }
                                $startDate = date('Y-m-d', $arm_members_class->arm_get_start_date_for_auto_debit_plan($plan_id, $arm_startdate_with_trial, $payment_cycle, $plan_action, $user_id));

                                $subscription = new AuthorizeNet_Subscription;
                                $subscription->name = substr($plan->name, 0, 30);

                                if($tax_percentage > 0){
                                    $tax_amount = !empty($arm_return_data['arm_tax_data']['tax_amount']) ? $arm_return_data['arm_tax_data']['tax_amount'] : 0;

                                    $amount = !empty($arm_return_data['arm_tax_data']['tax_final_amount']) ? $arm_return_data['arm_tax_data']['tax_final_amount'] : ($amount + $tax_amount);
                                    /*$tax_amount = ($tax_percentage * $amount)/100;
                                    $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                    $amount = $amount+$tax_amount;
                                    $amount = number_format((float)$amount, 2, '.', '');*/
                                    $extraParam['paid_amount'] = $amount;
                                }

                                $subscription->amount = trim($amount);
                                $subscription->intervalLength = $recur_interval;
                                $subscription->intervalUnit = $recurring_type;
                                $subscription->startDate = $startDate;
                                $subscription->totalOccurrences = $recur_cycles;
                                $subscription->setCustomFields = $recur_cycles;

                                if(!empty($arm_return_data['arm_recurring_data']['trial']))
                                {
                                    //If trial enable then enable it for subscription.
                                    $arm_trial_amount = number_format((float)$arm_return_data['arm_recurring_data']['trial']['amount'], 2, '.', '');
                                    $subscription->trialAmount = $arm_trial_amount;

                                    $arm_subs_trial_interval = $arm_return_data['arm_recurring_data']['trial']['interval'];
                                    $subscription->trialOccurrences = $arm_subs_trial_interval;
                                }

                                if (strlen(trim($exp_year)) == 2) {
                                    $exp_year = "20" . trim($exp_year);
                                }

                                $subscription->creditCardCardNumber = trim($card_number);
                                $subscription->creditCardExpirationDate = $exp_year . "-" . $exp_month;
                                $subscription->creditCardCardCode = $cvc;
                                $subscription->billToFirstName = (!empty($user_firstname)) ? trim($user_firstname) : sanitize_user($user_email_add);
                                $subscription->billToLastName = (!empty($user_lastname)) ? trim($user_lastname) : sanitize_user($user_email_add);

                                $request = new AuthorizeNetARB;
                                $response = $request->createSubscription($subscription);
                              
                                if ($response->isOk()) {
                                    $subscription_id = $response->getSubscriptionId();
                                    $extraParam['tax_amount'] = $tax_amount;
                                    $payment_data = array(
                                        'arm_user_id' => $user_id,
                                        'arm_first_name'=>$user_firstname,
                                        'arm_last_name'=>$user_lastname,
                                        'arm_plan_id' => $plan->ID,
                                        'arm_payment_gateway' => 'authorize_net',
                                        'arm_payment_type' => $plan_payment_type,
                                        'arm_payer_email' => $user_email_add,
                                        'arm_receiver_email' => '',
                                        'arm_transaction_id' => $subscription_id,
                                        'arm_token' => $subscription_id,
                                        'arm_transaction_payment_type' => $plan_payment_type,
                                        'arm_payment_mode' => $payment_mode,
                                        'arm_transaction_status' => 'completed',
                                        'arm_payment_date' => current_time('mysql'),
                                        'arm_amount' => $amount,
                                        'arm_currency' => AUTHORIZENET_CURRENCY,
                                        'arm_coupon_code' => '',
                                        'arm_is_trial' => $arm_is_trial,
                                        'arm_created_date' => current_time('mysql'),
                                        'arm_coupon_on_each_subscriptions' => @$arm_coupon_on_each_subscriptions,
                                    );

                                //======================= First Payment Start ====================================//
                                    if (class_exists('AuthorizeNetAIM')) {
                                        $trial_amount = $amount;
                                        $is_first_trial = $tax_again = false;

                                        if ($plan_action == 'new_subscription') {
                                            if (!empty($arm_return_data['arm_recurring_data']['trial'])) {
                                                $is_first_trial = $tax_again = true;
                                                $arm_is_first_trial = '1';
                                                $trial_amount = $arm_return_data['arm_recurring_data']['trial']['amount'];
                                                $trial_period = $arm_return_data['arm_recurring_data']['trial']['period'];
                                                $trial_interval = $arm_return_data['arm_recurring_data']['trial']['interval'];

                                                $extraFirstParam['trial'] = array(
                                                    'amount' => $trial_amount,
                                                    'period' => $trial_period,
                                                    'interval' => $trial_interval,
                                                );
                                            }
                                        }

                                        if (!empty($coupon_amount) && $coupon_amount > 0) {
                                            $trial_amount = $discount_amt;
                                            if ($is_first_trial) {
                                                $extraFirstParam['coupon'] = array(
                                                    'coupon_code' => $posted_data['arm_coupon_code'],
                                                    'amount' => $coupon_amount,
                                                );
                                            } else {
                                                $is_first_trial = true;
                                                $trial_interval = 1;
                                            }

                                            $tax_again = true;
                                           
                                            $extraFirstParam['paid_amount'] = $trial_amount;
                                        }

                                        if (strlen(trim($exp_year)) == 4) {
                                            $exp_year = substr(trim($exp_year), 2);
                                        }
                                        $sale = new AuthorizeNetAIM;
                                        $sale->card_num = trim($card_number);
                                        $sale->exp_date = $exp_month . $exp_year;
                                        $sale->first_name = (!empty($user_firstname)) ? trim($user_firstname) : sanitize_user($user_email_add);
                                        $sale->last_name = (!empty($user_lastname)) ? trim($user_lastname) : sanitize_user($user_email_add);

                                        $trial_amount = str_replace(",", "", $trial_amount);
                                        if($tax_percentage > 0 && $tax_again) {
                                            $tax_amount = ($tax_percentage * $trial_amount)/100;
                                            $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                            $trial_amount = $trial_amount+$tax_amount;
                                            $trial_amount = number_format((float)$trial_amount, 2, '.', '');
                                            $extraFirstParam['paid_amount'] = $trial_amount;
                                        }

                                        $trial_amount = number_format((float)$trial_amount, 2, '.','');
                                        
                                        $sale->amount = trim($trial_amount);
                                        if (!is_null($cvc)) {
                                            $sale->card_code = trim($cvc);
                                        }
                                        $sale->description = substr($plan->name, 0, 250);
                                        $class_methods = get_class_methods($sale);

                                        if ($trial_amount > 0) {
                                            $first_response = $sale->authorizeAndCapture();
                                            if ($first_response->approved) {
                                                 $extraFirstParam['tax_amount'] = $tax_amount;
                                                $first_payment_data = array(
                                                    'arm_user_id' => $user_id,
                                                    'arm_first_name'=>$user_firstname,
                                                    'arm_last_name'=>$user_lastname,
                                                    'arm_plan_id' => $plan->ID,
                                                    'arm_payment_gateway' => 'authorize_net',
                                                    'arm_payment_type' => $plan_payment_type,
                                                    'arm_payer_email' => $user_email_add,
                                                    'arm_receiver_email' => '',
                                                    'arm_transaction_id' => $first_response->transaction_id,
                                                    'arm_token' => $subscription_id,
                                                    'arm_transaction_payment_type' => $plan_payment_type,
                                                    'arm_transaction_status' => 'completed',
                                                    'arm_payment_mode' => $payment_mode,
                                                    'arm_payment_date' => current_time('mysql'),
                                                    'arm_amount' => floatval($trial_amount),
                                                    'arm_currency' => AUTHORIZENET_CURRENCY,
                                                    'arm_coupon_code' => isset($posted_data['arm_coupon_code']) ? $posted_data['arm_coupon_code'] : '',
                                                    'arm_coupon_discount' => @$arm_coupon_discount,
                                                    'arm_coupon_discount_type' => @$arm_coupon_discount_type,
                                                    'arm_is_trial' => $arm_is_first_trial,
                                                    'arm_created_date' => current_time('mysql'),
                                                    'arm_coupon_on_each_subscriptions' => @$arm_coupon_on_each_subscriptions,
                                                );
                                            } else {

                                                // ================ Cancel above subsctription =================== //
                                                $refId = 'ref' . time();
                                                $request->setRefId($refId);
                                                $response = $request->cancelSubscription($subscription_id);

                                                $err_msg = $arm_global_settings->common_message['arm_payment_fail_authorize_net'];
                                                $actual_error = isset($response->response_reason_text) ? $response->response_reason_text : '' ;
                                                $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                                                $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                                $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry something went wrong while processing payment with Authorize.Net.', 'ARMember');
						$err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                                $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                            }
                                        } else {
                                            try {
                                                $first_response = $sale->authorizeOnly($amount, trim($card_number), $exp_month . $exp_year);

                                                if ($first_response->approved) {

                                                    $first_payment_data = array(
                                                        'arm_user_id' => $user_id,
                                                        'arm_first_name'=>$user_firstname,
                                                        'arm_last_name'=>$user_lastname,
                                                        'arm_plan_id' => $plan->ID,
                                                        'arm_payment_gateway' => 'authorize_net',
                                                        'arm_payment_type' => $plan_payment_type,
                                                        'arm_payer_email' => $user_email_add,
                                                        'arm_receiver_email' => '',
                                                        'arm_transaction_id' => $first_response->transaction_id,
                                                        'arm_token' => $subscription_id,
                                                        'arm_transaction_payment_type' => $plan_payment_type,
                                                        'arm_transaction_status' => 'completed',
                                                        'arm_payment_mode' => $payment_mode,
                                                        'arm_payment_date' => current_time('mysql'),
                                                        'arm_amount' => floatval($trial_amount),
                                                        'arm_currency' => AUTHORIZENET_CURRENCY,
                                                        'arm_coupon_code' => isset($posted_data['arm_coupon_code']) ? $posted_data['arm_coupon_code'] : '',
                                                        'arm_coupon_discount' => @$arm_coupon_discount,
                                                        'arm_coupon_discount_type' => @$arm_coupon_discount_type,
                                                        'arm_is_trial' => $arm_is_first_trial,
                                                        'arm_created_date' => current_time('mysql'),
                                                        'arm_coupon_on_each_subscriptions' => @$arm_coupon_on_each_subscriptions,
                                                    );
                                                } else {
                                                    // ================ Cancel above subsctription =================== //
                                                    $refId = 'ref' . time();
                                                    $request->setRefId($refId);
                                                    $response = $request->cancelSubscription($subscription_id);

                                                    $err_msg = $arm_global_settings->common_message['arm_invalid_credit_card'];

                                                    $actual_error = isset($response->response_reason_text) ? $response->response_reason_text : '';
                                                    $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                                                    $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                                    $err_msg = (!empty($err_msg)) ? $err_msg : __('Please enter correct card details.', 'ARMember');
						    $err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                                    return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                                }
                                            } catch (Exception $e) {

                                                // ================ Cancel above subsctription =================== //
                                                $refId = 'ref' . time();
                                                $request->setRefId($refId);
                                                $response = $request->cancelSubscription($subscription_id);

                                                $err_msg = $arm_global_settings->common_message['arm_unauthorized_credit_card'];

                                                $error_msg = $e->getJsonBody();
                                                $actual_error = isset($error_msg['error']['message']) ? $error_msg['error']['message'] : '';
                                                $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';

                                                $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                                $err_msg = (!empty($err_msg)) ? $err_msg : __('Card details could not be authorized, please use other card detail.', 'ARMember');
						$err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                                return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                            }
                                        }
                                    } else {
                                        // ================ Cancel above subsctription =================== //

                                        
                                        $refId = 'ref' . time();
                                        $request->setRefId($refId);
                                        $response = $request->cancelSubscription($subscription_id);

                                        $err_msg = $arm_global_settings->common_message['arm_payment_fail_authorize_net'];

                                        $actual_error = isset($response->response_reason_text) ? $response->response_reason_text : '';
                                        $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                                        $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                        $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry something went wrong while processing payment with Authorize.Net.', 'ARMember');
					$err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                        $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                    }
                               //======================= First Payment Done ====================================//
                                } else {

                                    $err_msg = $arm_global_settings->common_message['arm_payment_fail_authorize_net'];
                                    
                                    if(!empty($response->xml->messages->resultCode[0]) && $response->xml->messages->resultCode[0]=='Error')
                                    {
                                        $actual_error_code = !empty($response->xml->messages->message->code[0]) ? $response->xml->messages->message->code[0] : '' ;
                                        $actual_error = !empty($response->xml->messages->message->text[0]) ? $response->xml->messages->message->text[0] : '' ;
                                    }
                                    else
                                    {
                                        $actual_error_code = isset($response->response_reason_code) ? $response->response_reason_code : '';
                                        $actual_error = isset($response->response_reason_text) ? $response->response_reason_text : '';
                                    }

                                    $actual_error = !empty($actual_error) ? $actual_error_code.' '.$actual_error.' '.$arm_help_link : '';
                                    $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                    $ARMember->arm_write_response('reputelog authorize.net response1=> '.$actualmsg);
                                    $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry something went wrong while processing payment with Authorize.Net.', 'ARMember');
                                    $err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                    $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                }
                                //======================= Second Payment Done ====================================//
                            }

                            if (!empty($payment_data) && !empty($first_payment_data)) {

                                $first_payment_data['arm_extra_vars'] = maybe_serialize($extraFirstParam);
                                $payment_data['arm_extra_vars'] = maybe_serialize($extraParam);

                                //============ save First payment ========================//

                                
                            
                                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($first_payment_data);
                                if ($payment_log_id) {
                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                }
                            } else {
                                $err_msg = $arm_global_settings->common_message['arm_payment_fail_authorize_net'];
                                
                                if(!empty($response->xml->messages->resultCode[0]) && $response->xml->messages->resultCode[0]=='Error')
                                {
                                    $actual_error_code = !empty($response->xml->messages->message->code[0]) ? $response->xml->messages->message->code[0] : '' ;
                                    $actual_error = !empty($response->xml->messages->message->text[0]) ? $response->xml->messages->message->text[0] : '' ;
                                }
                                else if(!empty($first_response) && isset($first_response->approved) && isset($first_response->declined) && $first_response->approved==0 && ($first_response->declined==1 || $first_response->error==1) )
                                {
                                    $actual_error_code = isset($first_response->response_reason_code) ? $first_response->response_reason_code: '';
                                    $actual_error = isset($first_response->response_reason_text) ? $first_response->response_reason_text: '';
                                    $actual_error .= !empty($first_response->description) ? ' ('. $first_response->description.')' : '';
                                }
                                else
                                {
                                    $actual_error_code = isset($response->response_reason_code) ? $response->response_reason_code: '';
                                    $actual_error = isset($response->response_reason_text) ? $response->response_reason_text: '';
                                }
                                
                                $actual_error = !empty($actual_error) ? $actual_error_code.' '.$actual_error.' '.$arm_help_link : '';
                                $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                $ARMember->arm_write_response('reputelog authorize.net response2=> '.$actualmsg);
                                
                                $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry something went wrong while processing payment with Authorize.Net.', 'ARMember');
                                $err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
                                $payment_done = array('status' => FALSE, 'error' => $err_msg);
                            }
                        } else {
                            if (class_exists('AuthorizeNetAIM')) {
                                if (strlen(trim($exp_year)) == 4) {
                                    $exp_year = substr(trim($exp_year), 2);
                                }

                                
                                $is_trial = false;
                                if ($plan->is_recurring() && $payment_mode == 'manual_subscription') {
                                    $recurring_data = $plan->prepare_recurring_data($payment_cycle);
                                    //Recurring Options
                                    
                                    $recur_period = $recurring_data['period'];
                                    $recur_interval = $recurring_data['interval'];
                                    $recur_cycles = $recurring_data['cycles'];
                                    $recur_cycles = (!empty($recur_cycles) && $recur_cycles != 'infinite') ? $recur_cycles : 9999;
                                    $recurring_type = (!empty($recur_period)) ? $recur_period : 'days';
                                    if ($recurring_type == "D" || $recurring_type == 'days') {
                                        $recurring_type = "days";
                                    } else if ($recurring_type == "M") {
                                        $recurring_type = "months";
                                    } else if ($recurring_type == "Y") {
                                        $recurring_type = "years";
                                    }
                                    //Trial Period Options
                                    $trial_interval = 0;
                                    $allow_trial = true;
                                    if (is_user_logged_in()) {
                                        $user_id = get_current_user_id();
                                        $user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                     
                                        if (!empty($user_plan)) {
                                            $allow_trial = false;
                                        }
                                    }
                                    if (!empty($recurring_data['trial']) && $allow_trial) {
                                        $is_trial = true;
                                        $arm_is_trial = '1';
                                        $trial_amount = $recurring_data['trial']['amount'];
                                        $trial_period = $recurring_data['trial']['period'];
                                        $trial_interval = $recurring_data['trial']['interval'];

                                        $extraParam['trial'] = array(
                                            'amount' => $trial_amount,
                                            'period' => $trial_period,
                                            'interval' => $trial_interval,
                                        );
                                    }
                                    if ($is_trial) {
                                        $amount = $trial_amount;
                                    }
                                }

                                if (!empty($coupon_amount) && $coupon_amount > 0) {
                                    $amount = $discount_amt;
                                }

                                if($tax_percentage > 0 && $is_trial){
                                    $amount = $arm_return_data['arm_tax_data']['final_trial_amount'];
                                    $extraParam['tax_amount'] = $arm_return_data['arm_tax_data']['trial_tax_amount'];
                                } else if($tax_percentage > 0 && !$is_trial){
                                    $amount = $arm_return_data['arm_tax_data']['tax_final_amount'];
                                    $extraParam['tax_amount'] = $arm_return_data['arm_tax_data']['tax_amount'];
                                }
                                
                                $amount = str_replace(",", "", $amount);
                                $amount = number_format((float)$amount, '2', '.', '');

                                if (($plan->is_recurring() && $payment_mode == 'manual_subscription' && ($amount == 0 || $amount == '0.00')) || ($amount == 0 || $amount == '0.00')) {
                                    $return_array = array();
                                    unset($extraParam['card_number']);
                                    if (is_user_logged_in()) {
                                        $current_user_id = get_current_user_id();
                                        $return_array['arm_user_id'] = $current_user_id;
                                    }
                                    $return_array['arm_first_name']=$user_firstname;
                                    $return_array['arm_last_name']=$user_lastname;
                                    $return_array['arm_plan_id'] = $plan->ID;
                                    $return_array['arm_payment_gateway'] = 'authorize_net';
                                    $return_array['arm_payment_type'] = $plan->payment_type;
                                    $return_array['arm_token'] = '-';
                                    $return_array['arm_payer_email'] = $user_email_add;
                                    $return_array['arm_receiver_email'] = '';
                                    $return_array['arm_transaction_id'] = '-';
                                    $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                                    $return_array['arm_transaction_status'] = 'completed';
                                    $return_array['arm_payment_mode'] = '';
                                    $return_array['arm_payment_date'] = date('Y-m-d H:i:s');
                                    $return_array['arm_amount'] = 0;
                                    $return_array['arm_currency'] = 'USD';
                                    $return_array['arm_coupon_code'] = @$coupon_code;
                                    $return_array['arm_extra_vars'] = maybe_serialize($extraParam);
                                    $return_array['arm_is_trial'] = $arm_is_trial;
                                    $return_array['arm_created_date'] = current_time('mysql');
                                    $return_array['arm_coupon_on_each_subscriptions'] = @$arm_coupon_on_each_subscriptions;
                                    $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
                                    $payment_data = $return_array;
                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                    if( $plan->is_recurring() && $payment_mode == 'manual_subscription' ){
                                        do_action('arm_after_authorize_net_free_manual_payment',$plan,$payment_log_id,$arm_is_trial,@$coupon_code,$extraParam);
                                    } else {
                                        do_action('arm_after_authorize_net_free_payment',$plan,$payment_log_id,$arm_is_trial,@$coupon_code,$extraParam);
                                    }
                                    return $payment_done;
                                } 
                                else {
                                    $extraParam['paid_amount'] = $amount;
                                    // card is valid
                                    $sale = new AuthorizeNetAIM;
                                    $sale->card_num = trim($card_number);
                                    $sale->exp_date = $exp_month . $exp_year;
                                    $sale->amount = trim($amount);
                                    if (!is_null($cvc)) {
                                        $sale->card_code = trim($cvc);
                                    }
                                    $sale->description = substr($plan->name, 0, 250);
                                    $class_methods = get_class_methods($sale);
                                    $sale->first_name = (!empty($user_firstname)) ? trim($user_firstname) : sanitize_user($user_email_add);
                                    $sale->last_name = (!empty($user_lastname)) ? trim($user_lastname) : sanitize_user($user_email_add);

                                    if (!empty($authorize_net_auth['transaction_id'])) {
                                        $sale->trans_id = $authorize_net_auth['transaction_id'];
                                        $response = $sale->priorAuthCapture();
                                    } else {
                                        $response = $sale->authorizeAndCapture();
                                    }

                                    if ($plan->is_recurring()) {
                                        $arm_payment_mode = $payment_mode;
                                    } else {
                                        $arm_payment_mode = '';
                                    }
                                    if ($response->approved) {
                                        $payment_data = array(
                                            'arm_user_id' => $user_id,
                                            'arm_first_name'=>$user_firstname,
                                            'arm_last_name'=>$user_lastname,
                                            'arm_plan_id' => $plan->ID,
                                            'arm_payment_gateway' => 'authorize_net',
                                            'arm_payment_type' => $plan_payment_type,
                                            'arm_payer_email' => $user_email_add,
                                            'arm_receiver_email' => '',
                                            'arm_transaction_id' => $response->transaction_id,
                                            'arm_token' => $response->transaction_id,
                                            'arm_transaction_payment_type' => $plan_payment_type,
                                            'arm_transaction_status' => 'completed',
                                            'arm_payment_mode' => $arm_payment_mode,
                                            'arm_payment_date' => current_time('mysql'),
                                            'arm_amount' => floatval($response->amount),
                                            'arm_currency' => AUTHORIZENET_CURRENCY,
                                            'arm_coupon_code' => isset($posted_data['arm_coupon_code']) ? $posted_data['arm_coupon_code'] : '',
                                            'arm_coupon_discount' => @$arm_coupon_discount,
                                            'arm_coupon_discount_type' => @$arm_coupon_discount_type,
                                            'arm_is_trial' => $arm_is_trial,
                                            'arm_created_date' => current_time('mysql'),
                                            'arm_coupon_on_each_subscriptions' => @$arm_coupon_on_each_subscriptions,
                                        );
                                    }
                                }
                            }
                            if (!empty($payment_data)) {
                                $payment_data['arm_extra_vars'] = maybe_serialize($extraParam);
                            
                                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);
                                $payment_done = array();
                                if ($payment_log_id) {
                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                    /*
                                    if($plan_action=='recurring_payment')
                                    {
                                        do_action('arm_after_recurring_payment_success_outside',$user_id,$plan->ID,'authorize.net',$payment_mode,$user_subsdata);
                                    }
                                    */
                                }
                            } else {
                                $err_msg = $arm_global_settings->common_message['arm_payment_fail_authorize_net'];

                                if(!empty($response->xml->messages->resultCode[0]) && $response->xml->messages->resultCode[0]=='Error')
                                {
                                    $actual_error_code = !empty($response->xml->messages->message->code[0]) ? $response->xml->messages->message->code[0] : '' ;
                                    $actual_error = !empty($response->xml->messages->message->text[0]) ? $response->xml->messages->message->text[0] : '' ;
                                }
                                else
                                {
                                    $actual_error_code = isset($response->response_reason_code) ? $response->response_reason_code : '';
                                    $actual_error = isset($response->response_reason_text) ? $response->response_reason_text : '';
                                }
                                $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                                $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error_code.' '.$actual_error : $err_msg;
                                $ARMember->arm_write_response('reputelog authorize.net response3=> '.$actualmsg);

                                $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry something went wrong while processing payment with Authorize.Net.', 'ARMember');
                                $err_msg = (!empty($actualmsg)) ? $actualmsg : $err_msg;
				$payment_done = array('status' => FALSE, 'error' => $err_msg);
                            }
                        }
                    }
                }
            }
        }

        

        function arm_cancel_authorize_net_subscription($user_id, $plan_id){
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;

            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            if (isset($all_payment_gateways['authorize_net']) && !empty($all_payment_gateways['authorize_net'])) {
                if (!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0) {
                    $arm_cancel_subscription_data = array();
                    $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, 'authorize_net', 'subscription_id', '', '');

                    do_action('arm_payment_log_entry', 'authorize_net', 'cancel subscription request', 'armember', $arm_cancel_subscription_data, $arm_debug_payment_log_id);

                    $arm_plan_data = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();
                    $arm_user_payment_gateway = !empty($arm_plan_data['arm_user_gateway']) ? $arm_plan_data['arm_user_gateway'] : '';
                    if(strtolower($arm_user_payment_gateway) == "authorize_net")
                    {
                        $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                        $arm_subscr_id = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : '';
                        $arm_customer_id = !empty($arm_cancel_subscription_data['arm_customer_id']) ? $arm_cancel_subscription_data['arm_customer_id'] : '';
                        $arm_transaction_id = !empty($arm_cancel_subscription_data['arm_transaction_id']) ? $arm_cancel_subscription_data['arm_transaction_id'] : '';

                        $arm_cancel_amount = !empty($arm_cancel_subscription_data['arm_cancel_amount']) ? $arm_cancel_subscription_data['arm_cancel_amount'] : 0;

                        if($arm_payment_mode == "auto_debit_subscription"){
                            $response = $this->arm_cancel_authorize_net_subscription_immediately($arm_subscr_id, $user_id, $plan_id, $arm_plan_data);
                        }
                        
                        if(!empty($arm_subscription_cancel_msg))
                        {
                            return;
                        }

                        do_action('arm_cancel_subscription_payment_log_entry', $user_id, $plan_id, 'authorize_net', $arm_subscr_id, $arm_subscr_id, $arm_customer_id, $arm_payment_mode, $arm_cancel_amount);
                    }
                }
            }
        }

        function arm_authorize_net_api_handle_response() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_members_class, $arm_debug_payment_log_id;
            
            if (isset($_REQUEST['arm-listener']) && in_array($_REQUEST['arm-listener'], array('arm_authorizenet_api', 'arm_authorizenet_notify'))) {

                do_action('arm_payment_log_entry', 'authorize_net', 'webhook', 'payment_gateway', $_REQUEST, $arm_debug_payment_log_id);
                
                $subscription_id = 0;
                $response_code = (int) $_POST['x_response_code']; 
                // Get the response code. 1 is success, 2 is decline, 3 is error
                
                
                $reason_code = (int) $_POST['x_response_reason_code']; 
                // Get the reason code. 8 is expired card.

                $arm_subscription_field_name = "";
                $arm_token_field_name = "";
                $arm_transaction_id_field_name = "";
               
                $response_reason_text = $_POST['x_response_reason_text'];
                $payment_type = $_POST['x_type'];
                if (isset($_POST['x_MD5_Hash']) && isset($_POST['x_subscription_id']) && isset($_POST['x_response_code'])) {
                    $subscription_id = (int) $_POST['x_subscription_id'];
                    $subscription_paynum = (int) $_POST['x_subscription_paynum']; 
                    // Subscription Payment Number, Starts at 1 for the first payment.
                    $arm_subscription_field_name = $arm_token_field_name = $arm_transaction_id_field_name = "x_subscription_id";
                } else if (isset($_POST['x_MD5_Hash']) && isset($_POST['x_response_code']) && !empty($_POST['x_cust_id'])) {
                    $subscription_id = $_POST['x_cust_id'];
                    $arm_subscription_field_name = $arm_token_field_name = $arm_transaction_id_field_name = "x_subscription_id";
                }

                if(!empty($subscription_id)){
                    $arm_webhook_save_membership_data = array();
                    $arm_webhook_save_membership_data = apply_filters('arm_modify_payment_webhook_data', $arm_webhook_save_membership_data, $_POST, 'authorize_net', $subscription_id, $subscription_id, 0, $subscription_id, $arm_subscription_field_name, $arm_token_field_name, $arm_transaction_id_field_name);

                    $user_subsdata = !empty($arm_webhook_save_membership_data['arm_subs_data']['user_subs_data']) ? $arm_webhook_save_membership_data['arm_subs_data']['user_subs_data'] : array();
                    $payment_mode = !empty($arm_webhook_save_membership_data['arm_subs_data']['payment_mode']) ? $arm_webhook_save_membership_data['arm_subs_data']['payment_mode'] : '';

                    $arm_paylog_data = !empty($arm_webhook_save_membership_data['arm_subs_data']['paylog_data']) ? $arm_webhook_save_membership_data['arm_subs_data']['paylog_data'] : '';
                    $user_id = !empty($arm_paylog_data->arm_user_id) ? $arm_paylog_data->arm_user_id : 0;

                    $plan_id = !empty($arm_webhook_save_membership_data['arm_subs_data']['paylog_plan_id']) ? $arm_webhook_save_membership_data['arm_subs_data']['paylog_plan_id'] : 0;

                    $arm_extra_vars_data = !empty($arm_webhook_save_membership_data['arm_subs_data']['extra_vars']) ? $arm_webhook_save_membership_data['arm_subs_data']['extra_vars'] : array();

                    $amount = !empty($arm_extra_vars_data) ? $arm_extra_vars_data['plan_amount'] : 0;
                    if($response_code == 1){
                        do_action('arm_after_recurring_payment_success_outside',$user_id,$plan_id,'authorize.net',$payment_mode,$user_subsdata);
                    } else if ($response_code == 2 || $response_code == 3 || $response_code == 4) {
                        if($response_code == 2 || $response_code == 3){
                            $arm_debug_log_data = array(
                                'response_code' => $response_code,
                                'user_id' => $user_id,
                                'plan_id' => $plan_id,
                            );
                            do_action('arm_payment_log_entry', 'authorize_net', 'webhook failed notification', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);
                            $_POST['payment_status'] = 'failed';
                            $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'failed_payment'), true);
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'failed_payment'));
                            
                            
                            do_action('arm_after_recurring_payment_failed_outside',$user_id,$plan_id,'authorize.net',$payment_mode,$user_subsdata);
                        }else if($response_code == 4){
                            $arm_debug_log_data = array(
                                'response_code' => $response_code,
                                'user_id' => $user_id,
                                'plan_id' => $plan_id,
                            );
                            do_action('arm_payment_log_entry', 'authorize_net', 'webhook transaction held notification', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);
                            /* Transaction is held for review */
                            do_action('arm_handle_authorize_net_review_transaction',$user_id,$plan_id,$payment_mode,$user_subsdata);
                        }
                    } else {
                        /* Other error */
                        do_action('arm_handle_authorize_net_unknown_error_from_outside',$user_id,$plan_id,$response_code,$reason_code);
                    }


                    if (!empty($arm_paylog_data) && !empty($user_id) && $user_id != 0) {
                        $extraVars['paid_amount'] = $amount;
                        $payment_data = array(
                            'arm_user_id' => $user_id,
                            'arm_first_name'=>$arm_paylog_data->arm_first_name,
                            'arm_last_name'=>$arm_paylog_data->arm_last_name,
                            'arm_plan_id' => $plan_id,
                            'arm_payment_gateway' => 'authorize_net',
                            'arm_payment_type' => $payment_type,
                            'arm_token' => $subscription_id,
                            'arm_payer_email' => $arm_paylog_data->arm_payer_email,
                            'arm_receiver_email' => '',
                            'arm_transaction_id' => $subscription_id,
                            'arm_transaction_payment_type' => $payment_type,
                            'arm_transaction_status' => $_POST['payment_status'],
                            'arm_payment_mode' => $payment_mode,
                            'arm_payment_date' => current_time('mysql'),
                            'arm_amount' => $amount,
                            'arm_currency' => $arm_paylog_data->arm_currency,
                            'arm_coupon_code' => '',
                            'arm_extra_vars' => maybe_serialize($extraVars),
                            'arm_is_trial' => '0',
                            'arm_created_date' => current_time('mysql')
                        );

                        do_action('arm_payment_log_entry', 'authorize_net', 'webhook payment log', 'payment_gateway', $payment_data, $arm_debug_payment_log_id);

                        $arm_payment_gateways->arm_save_payment_log($payment_data);
                    }
                }

            }
            return;
        }

    }

}
global $arm_authorize_net;
$arm_authorize_net = new ARM_authorize_net();
