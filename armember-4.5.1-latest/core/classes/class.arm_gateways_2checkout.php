<?php
if (!class_exists('ARM_2checkout')) {
    class ARM_2checkout {
        function __construct() {
            add_action('arm_payment_gateway_validation_from_setup', array($this, 'arm_payment_gateway_form_submit_action'), 10, 4);
            add_action('arm_cancel_subscription_gateway_action', array($this, 'arm_cancel_2checkout_subscription'), 10, 2);

            //add_action('wp', array($this, 'arm_2checkout_ins_handle_response'), 5);
            add_filter('arm_change_pending_gateway_outside', array($this, 'arm_change_pending_gateway_outside'), 100, 3);

            add_filter('arm_payment_gateway_trial_allowed', array($this, 'arm_trial_allowed_or_not'), 10, 5);

            add_action('arm_on_expire_cancel_subscription', array($this, 'arm_cancel_subscription_instant'), 100, 4);
        }

        function arm_trial_allowed_or_not($trial_not_allowed, $arm_plan_id, $payment_gateway, $payment_gateway_options, $posted_data){
            if($payment_gateway == "2checkout"){
                $trial_not_allowed = 0;
            }
            return $trial_not_allowed;
        }

        function arm_cancel_subscription_instant($user_id, $plan, $cancel_plan_action, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_debug_payment_log_id;

            $arm_debug_log_data = array(
                'user_id' => $user_id,
                'plan' => $plan,
                'cancel_plan_action' => $cancel_plan_action,
                'planData' => $planData
            );
            do_action('arm_payment_log_entry', '2checkout', 'on expire cancel subscription', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

            $plan_id = $plan->ID;
            $arm_cancel_subscription_data = array();
            $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, '2checkout', 'sale_id', '', '');
            $arm_user_payment_gateway = !empty($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '';
            if(!empty($arm_cancel_subscription_data) && (!empty($arm_user_payment_gateway) && $arm_user_payment_gateway == "2checkout")){
                $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                $planData = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();

                $hashOrder = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : '';
                $this->arm_immediate_cancel_2checkout_payment($hashOrder, $user_id, $plan_id, $planData);
            }
        }

        function arm_immediate_cancel_2checkout_payment($hashOrder, $user_id, $plan_id, $planData)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg, $arm_debug_payment_log_id;

            $response = "";

            $arm_debug_log_data = array(
                'hashOrder' => $hashOrder,
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'planData' => $planData,
            );
            do_action('arm_payment_log_entry', '2checkout', 'immediate cancel subscription', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

            try{
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $twoco_options = $all_payment_gateways['2checkout'];
                self::arm_Load2CheckoutLibrary($twoco_options);
                if (class_exists('Twocheckout_Sale')) {
                    $response = Twocheckout_Sale::stop(array('sale_id' => $hashOrder));
                    do_action('arm_payment_log_entry', '2checkout', 'cancel subscription response', 'armember', $response, $arm_debug_payment_log_id);
                    if ($response['response_code'] == "OK") {
                        $planData['sale_id'] = "";
                        update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                    }
                    else{
                        $arm_common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                        $arm_subscription_cancel_msg = isset($arm_common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $arm_common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel. Please contact the site administrator.", 'ARMember');
                    }
                }
            }
            catch(Exception $e)
            {
                do_action('arm_payment_log_entry', '2checkout', 'cancel subscription error', 'armember', $e->getMessage(), $arm_debug_payment_log_id);
                $arm_common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                $arm_subscription_cancel_msg = isset($arm_common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $arm_common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel. Please contact the site administrator.", 'ARMember');
                if(!empty($e->getMessage()))
                {
                    $arm_subscription_cancel_msg = __("Error in cancel subscription from 2Checkout.", "ARMember")." ".$e->getMessage();
                }
            }

            return $response;
        }

        function arm_Load2CheckoutLibrary($config = array()) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways;
            if (!empty($config)) {
                if (file_exists(MEMBERSHIP_DIR . "/lib/2checkout/Twocheckout.php")) {
                    require_once (MEMBERSHIP_DIR . "/lib/2checkout/Twocheckout.php"); //Load 2Checkout lib
                    /* Set API Keys & Account */
                    Twocheckout::privateKey($config['private_key']);
                    Twocheckout::sellerId($config['sellerid']);
                    Twocheckout::username($config['username']);
                    Twocheckout::password($config['password']);
                    if ($config['payment_mode'] == 'sandbox') {
                        Twocheckout::verifySSL(false);
                        Twocheckout::sandbox(true);
                    }
                }
                $currency = $arm_payment_gateways->arm_get_global_currency();

                if (!defined('TWOCHECKOUT_SELLERID')) {
                    define("TWOCHECKOUT_SELLERID", $config['sellerid']);
                } else {
                    if (constant("TWOCHECKOUT_SELLERID") != $config['sellerid']) {
                        define("TWOCHECKOUT_SELLERID", $config['sellerid']);
                    }
                }

                if (!defined('TWOCHECKOUT_CURRENCY')) {
                    define("TWOCHECKOUT_CURRENCY", $currency);
                } else {
                    if (constant("TWOCHECKOUT_CURRENCY") != $config['$currency']) {
                        define("TWOCHECKOUT_CURRENCY", $currency);
                    }
                }
            }
        }

        function arm_payment_gateway_form_submit_action($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0){
            global $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_membership_setup, $payment_done, $arm_subscription_plans, $arm_manage_coupons, $arm_transaction, $is_free_manual, $arm_debug_payment_log_id;

            if($payment_gateway == '2checkout'){
                $arm_return_data = array();
                $arm_return_data = apply_filters('arm_calculate_payment_gateway_submit_data', $arm_return_data, $payment_gateway, $payment_gateway_options, $posted_data, $entry_id);

                do_action('arm_payment_log_entry', '2checkout', 'payment form submit', 'armember', $arm_return_data, $arm_debug_payment_log_id);

                $arm_entry_email = !empty($arm_return_data['arm_user_email']) ? $arm_return_data['arm_user_email'] : '';
                $arm_plan_id = !empty($arm_return_data['arm_plan_id']) ? $arm_return_data['arm_plan_id'] : $posted_data['_subscription_plan'];
                $plan = !empty($arm_return_data['arm_plan_obj']) ? $arm_return_data['arm_plan_obj'] : new ARM_Plan($arm_plan_id);

                $arm_user_id = !empty($arm_return_data['arm_entry_data']) ? $arm_return_data['arm_entry_data']['arm_user_id'] : 0;

                $arm_payment_mode = !empty($arm_return_data['arm_payment_mode']) ? $arm_return_data['arm_payment_mode'] : '';
                $arm_plan_action = !empty($arm_return_data['arm_plan_action']) ? $arm_return_data['arm_plan_action'] : 'new_suscription';

                $arm_payment_cycle = !empty($arm_return_data['arm_entry_data']) ? $arm_return_data['arm_entry_data']['arm_entry_value']['arm_selected_payment_cycle'] : '';

                if ($plan->is_recurring()) {
                    if ($arm_payment_mode == 'auto_debit_subscription') {
                        if (!($plan->is_support_2checkout($arm_payment_cycle, $arm_plan_action))) {
                            $err_msg = __('Payment through 2Checkout is not supported for selected plan.', 'ARMember');
                            return $payment_done = array('status' => FALSE, 'error' => $err_msg);
                        }
                    }
                }

                $arm_setup_id = $posted_data['setup_id'];

                $is_free_manual = false;
                $charge_form = $additionalVars = '';

                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $twoco_options = (isset($all_payment_gateways['2checkout'])) ? $all_payment_gateways['2checkout'] : array();
                $currency = $arm_payment_gateways->arm_get_global_currency();
                $returnURL = $arm_return_data['arm_extra_vars'] ? $arm_return_data['arm_extra_vars']['arm_return_url'] : ARM_HOME_URL;
                $form_slug = !empty($posted_data['arm_action']) ? $posted_data['arm_action'] : '';
                $form = new ARM_Form('slug', $form_slug);
                $tax_percentage = isset($arm_return_data['arm_tax_data']['tax_percentage']) ? $arm_return_data['arm_tax_data']['tax_percentage'] : 0;

                if ($plan->is_recurring()) {
                    $plan_amount = $arm_return_data['arm_recurring_data']['amount'];
                    $amount = abs(str_replace(',', '', $plan_amount));
                } else {
                    $amount = abs(str_replace(',', '', $plan->amount));
                }

                $tax_amount = 0;

                if ($currency == 'JPY') {
                    $amount = number_format((float) $amount, 0, '', '');
                } else {
                    $amount = number_format((float) $amount, 2, '.', '');
                }

                $custom_var = $entry_id . '|' . $plan->payment_type;

                $couponCode = $coupon_code = !empty($arm_return_data['arm_coupon_data']) ? $arm_return_data['arm_coupon_code'] : '';

                $discount_amt = $coupon_amount = $arm_coupon_discount = 0;
                $arm_coupon_discount_type = '';
                $arm_coupon_on_each_subscriptions = 0;
                if(!empty($arm_return_data['arm_coupon_data']))
                {
                    $arm_coupon_data = $arm_return_data['arm_coupon_data'];
                    $discount_amt = !empty($arm_coupon_data['total_amt']) ? $arm_coupon_data['total_amt'] : 0;
                    $coupon_amount = !empty($arm_coupon_data['coupon_amt']) ? $arm_coupon_data['coupon_amt'] : 0;
                    $arm_coupon_discount = !empty($arm_coupon_data['discount']) ? $arm_coupon_data['discount'] : 0;
                    $arm_coupon_discount_type = ($arm_coupon_data['discount_type']) ? $currency : "%";
                    $arm_coupon_on_each_subscriptions = !empty($arm_coupon_data['arm_coupon_on_each_subscriptions']) ? $arm_coupon_data['arm_coupon_on_each_subscriptions'] : 0;
                }


                if($plan->is_recurring() && $arm_payment_mode == "auto_debit_subscription"){
                    $arm_recurring_data = $arm_return_data['arm_recurring_data'];

                    $recur_cycles = (!empty($arm_recurring_data['cycles']) && $arm_recurring_data['cycles'] != 'infinite') ? $arm_recurring_data['cycles'] : 'infinite';

                    $arm_recurring_interval = $arm_recurring_data['interval'];

                    $arm_recurring_type = !empty($arm_recurring_data['period']) ? $arm_recurring_data['period'] : 'Day';

                    if ($arm_recurring_type == "D" || $arm_recurring_type == 'Day') {
                        $arm_recurring_type = "Day";
                    } else if ($arm_recurring_type == "W") {
                        $arm_recurring_type = "Week";
                    } else if ($arm_recurring_type == "M") {
                        $arm_recurring_type = "Month";
                    } else if ($arm_recurring_type == "Y") {
                        $arm_recurring_type = "Year";
                    }

                    $isTrial = false;

                    $trial_amount = 0;

                    if ($arm_plan_action == 'new_subscription') {
                        if (!empty($arm_recurring_data['trial'])) {
                            $trial_amount = $arm_recurring_data['trial']['amount'];
                            $trial_period = $arm_recurring_data['trial']['period'];
                            $trial_interval = $arm_recurring_data['trial']['interval'];
                            $isTrial = true;
                            $arm_is_trial = '1';
                            $extraParam['trial'] = array(
                                'amount' => $trial_amount,
                                'period' => $trial_period,
                                'interval' => $trial_interval,
                            );
                            /* Increase Billing Cycle */
                            $recur_cycles = ($recur_cycles == 'infinite') ? $recur_cycles : $recur_cycles + 1;
                        }
                    }

                    if (!empty($coupon_amount) && $coupon_amount > 0) {
                        $trial_amount = $discount_amt;
                        $isTrial = true;
                    }

                    $startup_fee = 0;
                    if ($isTrial) {


                        if($tax_percentage > 0){
                            $tax_amount = ($trial_amount * $tax_percentage)/100;
                            $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                            $trial_amount = $trial_amount + $tax_amount;
                        
                            $tax_amount1 = ($amount * $tax_percentage)/100;
                            $tax_amount1 = number_format((float)$tax_amount1, 2, '.', '');
                            $amount  = $amount + $tax_amount1;
                        }

                        $startup_fee = ($trial_amount < $amount) ? $trial_amount - $amount : -$amount;
                        $additionalVars .= '<input type="hidden" name="li_0_startup_fee" value="' . $startup_fee . '" />';
                    }

                    $recurrence = $arm_recurring_interval . ' ' . $arm_recurring_type;
                    $duration = ($recur_cycles == 'infinite') ? 'Forever' : ($recur_cycles * $arm_recurring_interval) . ' ' . $arm_recurring_type;
                    $additionalVars .= '<input type="hidden" name="li_0_recurrence" value="' . $recurrence . '" />';
                    $additionalVars .= '<input type="hidden" name="li_0_duration" value="' . $duration . '" />';
                }else if ($plan->is_recurring() && $arm_payment_mode == 'manual_subscription') {
                    $allow_trial = true;
                    if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                        $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);

                        if (!empty($user_plans)) {
                            $allow_trial = false;
                        }
                    }

                    if ($plan->has_trial_period() && $allow_trial) {
                        $trial_amount = $plan->options['trial']['amount'];
                        $trial_period = $plan->options['trial']['period'];
                        $trial_interval = $plan->options['trial']['interval'];
                        $isTrial = true;
                        $arm_is_trial = '1';
                        $extraParam['trial'] = array(
                            'amount' => $trial_amount,
                            'period' => $trial_period,
                            'interval' => $trial_interval,
                        );
                        if (!empty($coupon_amount) && $coupon_amount > 0) {
                            $trial_amount = $discount_amt;
                            $isTrial = true;
                        }
                        $amount = abs(str_replace(',', '', $trial_amount));
                    } else {
                        $allow_trial = false;
                    }
                    if (!empty($coupon_amount) && $coupon_amount > 0) {
                        $amount = abs(str_replace(',', '', $discount_amt));
                    }

                    if($tax_percentage > 0){
                        $tax_amount = number_format((float)$arm_return_data['arm_tax_data']['tax_amount'], 2, '.', '');
                        $amount = $arm_return_data['arm_tax_data']['tax_final_amount'];
                    }

                    if($tax_percentage > 0 && $allow_trial){
                        $amount = $arm_return_data['arm_tax_data']['final_trial_amount'];
                        $tax_amount = $arm_return_data['arm_tax_data']['trial_tax_amount'];
                    } else if($tax_percentage > 0 && !$allow_trial){
                        $amount = $arm_return_data['arm_tax_data']['tax_final_amount'];
                        $tax_amount = $arm_return_data['arm_tax_data']['tax_amount'];
                    }



                    if ($currency == 'JPY') {
                        $amount = number_format((float) $amount, 0, '', '');
                    } else {
                        $amount = number_format((float) $amount, 2, '.', '');
                    }

                    if ($amount == 0 || $amount == '0.00') {
                        $return_array = array();
                        if (is_user_logged_in()) {
                            $current_user_id = get_current_user_id();
                            $return_array['arm_user_id'] = $current_user_id;
                            $arm_user_info = get_userdata($current_user_id);
                            $return_array['arm_first_name']=$arm_user_info->first_name;
                            $return_array['arm_last_name']=$arm_user_info->last_name;
                        }else{
                            $return_array['arm_first_name']=(isset($request_data['first_name']))?$request_data['first_name']:'';
                            $return_array['arm_last_name']=(isset($request_data['last_name']))?$request_data['last_name']:'';
                        }
                        $return_array['arm_plan_id'] = $arm_plan_id;
                        $return_array['arm_payment_gateway'] = '2checkout';
                        $return_array['arm_payment_type'] = $plan->payment_type;
                        $return_array['arm_token'] = '-';
                        $return_array['arm_payer_email'] = $arm_entry_email;
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
                        do_action('arm_after_twocheckout_free_manual_payment', $plan, $payment_log_id, $arm_is_trial, $coupon_code, $extraParam);
                        return array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                    }
                }else {
                    if (!empty($coupon_amount) && $coupon_amount > 0) {
                        $amount = $discount_amt;
                    }
                    $amount = abs(str_replace(',', '', $amount));

                    if($tax_percentage > 0){
                        $tax_amount = number_format((float)$arm_return_data['arm_tax_data']['tax_amount'], 2, '.', '');
                        $amount = $arm_return_data['arm_tax_data']['tax_final_amount'];
                    }

                    if ($currency == 'JPY') {
                        $amount = number_format((float) $amount, 0, '', '');
                    } else {
                        $amount = number_format((float) $amount, 2, '.', '');
                    }


                    if ($amount == 0 || $amount == '0.00') {
                        $return_array = array();
                        if (is_user_logged_in()) {
                            $current_user_id = get_current_user_id();
                            $return_array['arm_user_id'] = $current_user_id;
                            $arm_user_info = get_userdata($current_user_id);
                            $return_array['arm_first_name']=$arm_user_info->first_name;
                            $return_array['arm_last_name']=$arm_user_info->last_name;
                        }else{
                            $return_array['arm_first_name']=(isset($request_data['first_name']))?$request_data['first_name']:'';
                            $return_array['arm_last_name']=(isset($request_data['last_name']))?$request_data['last_name']:'';
                        }
                        
                        $return_array['arm_plan_id'] = $arm_plan_id;
                        $return_array['arm_payment_gateway'] = '2checkout';
                        $return_array['arm_payment_type'] = $plan->payment_type;
                        $return_array['arm_token'] = '-';
                        $return_array['arm_payer_email'] = $arm_entry_email;
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
                        do_action('arm_after_twocheckout_free_payment', $plan, $payment_log_id, $arm_is_trial, $coupon_code, $extraParam);
                        return array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                    }
                }


                $arm_2checkout_demo = "";
                if ($twoco_options['payment_mode'] == 'sandbox') {
                    $arm_2checkout_demo = 'Y';
                }

                $reqUrl = 'https://www.2checkout.com/checkout/purchase';
                $arm_2checkout_language = isset($twoco_options['language']) ? $twoco_options['language'] : 'en_US';


                $charge_form .= '<form id="arm_2checkout_form" name="2Checkout" action="' . $reqUrl . '" method="post">';
                $charge_form .= '<input type="hidden" name="sid" value="' . $twoco_options['sellerid'] . '" />';
                $charge_form .= '<input type="hidden" name="mode" value="2CO" />';
                $charge_form .= '<input type="hidden" name="merchant_order_id" value="' . $entry_id . '" />';
                $charge_form .= '<input type="hidden" name="li_0_type" value="product" />';
                $charge_form .= '<input type="hidden" name="li_0_product_id" value="' . $arm_plan_id . '" />';
                $charge_form .= '<input type="hidden" name="li_0_name" value="' . $plan->name . '" />';
                $charge_form .= '<input type="hidden" name="li_0_description" value="-" />';
                $charge_form .= '<input type="hidden" name="li_0_quantity" value="1" />';
              
                $charge_form .= '<input type="hidden" name="li_0_price" value="' . $amount . '" />';

                $charge_form .= $additionalVars;
                $charge_form .= '<input type="hidden" name="li_0_tangible" value="N" />';
                $charge_form .= '<input type="hidden" name="li_0_option_0_name" value="custom" />';
                $charge_form .= '<input type="hidden" name="li_0_option_0_value" value="' . $custom_var . '" />';
                $charge_form .= '<input type="hidden" name="li_0_option_1_name" value="tax_percentage" />';
                $charge_form .= '<input type="hidden" name="li_0_option_1_value" value="' . $tax_percentage . '" />';
                $charge_form .= '<input type="hidden" name="li_0_option_0_surcharge" value="0.00" />';
                $charge_form .= '<input type="hidden" name="currency_code" value="' . $currency . '" />';
                $charge_form .= '<input type="hidden" name="email" value="' . $arm_entry_email . '" />';
                $charge_form .= '<input type="hidden" name="lang" value="' . $arm_2checkout_language . '" />';
                $charge_form .= '<input type="hidden" name="x_receipt_link_url" value="' . $returnURL . '" />';
                if(!empty($arm_2checkout_demo))
                {
                    $charge_form .= '<input type="hidden" name="demo" value="Y" />';
                }

                $charge_form .= '<input type="submit" value="Checkout" style="display:none;"/>';

                do_action('arm_payment_log_entry', '2checkout', 'payment form redirect value', 'armember', $charge_form, $arm_debug_payment_log_id);

                $charge_form .= '<script data-cfasync="false" type="text/javascript">document.getElementById("arm_2checkout_form").submit();</script>';
                $charge_form .= '</form>';

                $return = array('status' => 'success', 'type' => 'redirect', 'message' => $charge_form);
                echo json_encode($return);                        
                exit;
            }
        }

        

        function arm_cancel_2checkout_subscription($user_id, $plan_id){
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg;
            if (!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0) {
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                if (isset($all_payment_gateways['2checkout']) && !empty($all_payment_gateways['2checkout'])) {
                    $arm_cancel_subscription_data = array();
                    $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, '2checkout', 'sale_id', '', '');

                    if(!empty($arm_cancel_subscription_data)){

                        $arm_debug_log_data = array(
                            'user_id' => $user_id,
                            'plan_id' => $plan_id,
                            'cancel_subscription_data' => $arm_cancel_subscription_data,
                        );
                        do_action('arm_payment_log_entry', '2checkout', 'cancel subscription data', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
                        
                        $arm_payment_mode = !empty($arm_cancel_subscription_data['arm_payment_mode']) ? $arm_cancel_subscription_data['arm_payment_mode'] : 'manual_subscription';

                        $planData = !empty($arm_cancel_subscription_data['arm_plan_data']) ? $arm_cancel_subscription_data['arm_plan_data'] : array();

                        $arm_subscr_id = !empty($arm_cancel_subscription_data['arm_subscr_id']) ? $arm_cancel_subscription_data['arm_subscr_id'] : '';
                        $arm_customer_id = !empty($arm_cancel_subscription_data['arm_customer_id']) ? $arm_cancel_subscription_data['arm_customer_id'] : '';
                        $arm_transaction_id = !empty($arm_cancel_subscription_data['arm_transaction_id']) ? $arm_cancel_subscription_data['arm_transaction_id'] : '';

                        $arm_cancel_amount = !empty($arm_cancel_subscription_data['arm_cancel_amount']) ? $arm_cancel_subscription_data['arm_cancel_amount'] : 0;

                        if($arm_payment_mode == "auto_debit_subscription"){
                            $response = $this->arm_immediate_cancel_2checkout_payment($arm_subscr_id, $user_id, $plan_id, $planData);
                        }

                        if(!empty($arm_subscription_cancel_msg))
                        {
                            return;
                        }

                        do_action('arm_cancel_subscription_payment_log_entry', $user_id, $plan_id, 'stripe', $arm_subscr_id, $arm_subscr_id, $arm_customer_id, $arm_payment_mode, $arm_cancel_amount);
                    }
                }
            }
        }


        function ArrayExpand($array){
            $retval = "";
            for($i = 0; $i < sizeof($array); $i++){
                $size        = strlen(StripSlashes($array[$i]));  /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
                $retval    .= $size.StripSlashes($array[$i]);  /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
            }
            return $retval;
        }
        function hmac ($key, $data){
           $b = 64; // byte length for md5
           if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
           }
           $key  = str_pad($key, $b, chr(0x00));
           $ipad = str_pad('', $b, chr(0x36));
           $opad = str_pad('', $b, chr(0x5c));
           $k_ipad = $key ^ $ipad ;
           $k_opad = $key ^ $opad;
           return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
        }

        function arm_2checkout_ins_handle_response() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans, $arm_membership_setup, $arm_member_forms, $arm_manage_communication, $arm_manage_coupons, $arm_members_class, $arm_debug_payment_log_id;
            /**
             * Need to set Instant Notification Service (INS) URL like this (ie. http://sitename.com/?action=arm_2checkout_api)
             */

            if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('arm_2checkout_api', 'arm_2checkout_notify')) || isset($_REQUEST['arm-listener']) && in_array($_REQUEST['arm-listener'], array('arm_2checkout_api', 'arm_2checkout_notify'))) {

                do_action('arm_payment_log_entry', '2checkout', 'webhook data', 'payment_gateway', $_REQUEST, $arm_debug_payment_log_id);

                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                if (isset($all_payment_gateways['2checkout']) && !empty($all_payment_gateways['2checkout'])) 
                {
                    $twoco_options = $all_payment_gateways['2checkout'];

                    $arm_api_secret_key = !empty($twoco_options['api_secret_key']) ? $twoco_options['api_secret_key'] : '';

                    if(!empty($arm_api_secret_key))
                    {
                        //Condition for send success notification to 2checkout when IPN received.
                        
                        $pass = $arm_api_secret_key;
                        //For get pass use this https://knowledgecenter.2checkout.com/API-Integration/Webhooks/06Instant_Payment_Notification_(IPN)/Calculate-the-IPN-HASH-signature documentation.
                        
                        $result = "";
                        $return = "";
                        $signature = $_POST["HASH"];
                        $body = "";
                        
                        ob_start();
                        while(list($key, $val) = each($_POST)){
                            $$key=$val;
                            
                            if($key != "HASH"){
                                if(is_array($val)) $result .= $this->ArrayExpand($val);
                                else{
                                    $size = strlen(StripSlashes($val));
                                    $result .= $size.StripSlashes($val);
                                }
                            }
                        }
                        $body = ob_get_contents();
                        ob_end_flush();
                        $date_return = date("YmdHis");
                        $return = strlen($_POST["IPN_PID"][0]).$_POST["IPN_PID"][0].strlen($_POST["IPN_PNAME"][0]).$_POST["IPN_PNAME"][0];
                        $return .= strlen($_POST["IPN_DATE"]).$_POST["IPN_DATE"].strlen($date_return).$date_return;
                        
                        $hash =  $this->hmac($pass, $result); /* HASH for data received */
                        $body .= $result."\r\n\r\nHash: ".$hash."\r\n\r\nSignature: ".$signature."\r\n\r\nReturnSTR: ".$return;

                        if($hash == $signature){
                            echo "Verified OK!<br>";
                            $result_hash =  $this->hmac($pass, $return);
                            echo "<EPAYMENT>".$date_return."|".$result_hash."</EPAYMENT>";
                        }
                    }
                    
                    $insMsg = array();
                    $extraVars = array();
                    foreach ($_POST as $k => $v) {
                        $insMsg[$k] = $v;
                    }
                    # Validate the Hash



                    $arm_payment_mode = $twoco_options['payment_mode'];
                    $hashSecretWord = $twoco_options['secret_word'];
                    $hashSid = $twoco_options['sellerid'];
                    $hashInvoice = !empty($insMsg['invoice_id']) ? $insMsg['invoice_id'] : '';

                    $payLog = $wpdb->get_row("SELECT arm_log_id FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_transaction_id` = '".$hashInvoice."' ORDER BY `arm_log_id` DESC");

                    
		    if(empty($payLog))
		    {
		    	$arm_twocheckout_get_transient = get_transient("arm_2co_transaction_".$hashInvoice);
	                    if(false == $arm_twocheckout_get_transient || $arm_twocheckout_get_transient == 'false' || empty($arm_twocheckout_get_transient)){
	                        set_transient("arm_2co_transaction_".$hashInvoice, $hashInvoice, DAY_IN_SECONDS);
	                    }else{
	                        //If transient already exists then webhook further code will restrict to execute.
	                        die();
	                    }
		    }
		    else {
		    	die();
		    }

                    $arm_is_trial = '0';
                    if (isset($insMsg['message_type'])) {
                        /**
                         * For INS Notifications
                         */
                        if (isset($insMsg['md5_hash'])) {
                            $hashSid = $insMsg['vendor_id'];
                            $hashOrder = $insMsg['sale_id'];

                            $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $hashSecretWord));
                            if ($StringToHash == $insMsg['md5_hash']) {
                                $arm_token = $hashOrder;
                                $arm_transaction_id = $hashInvoice;
                                $entry_id = !empty($insMsg['vendor_order_id']) ? $insMsg['vendor_order_id'] : 0;
                                $arm_subscr_id = $hashOrder;
                                $arm_subcription_id_field_name = "sale_id";
                                $arm_token_field_name = "sale_id";
                                $arm_transaction_field_name = "invoice_id";

                                $arm_webhook_save_membership_data = array();
                                $arm_webhook_save_membership_data = apply_filters('arm_modify_payment_webhook_data', $arm_webhook_save_membership_data, $insMsg, '2checkout', $arm_token, $arm_transaction_id, $entry_id, $arm_subscr_id, $arm_subcription_id_field_name, $arm_token_field_name, $arm_transaction_field_name);
                            }
                        }
                    } else if (!empty($_POST['key']) || !empty($_GET['key'])) {
                        /**
                         * For Return Callback From 2Checkout Site
                         */

                        if(isset($_GET['key']) && !empty($_GET['key']))
                        {
                            $insMsg = array();
                            $extraVars = array();
                            foreach ($_GET as $k => $v) {
                                $insMsg[$k] = $v;
                            }
                        }

                        $pgateway = "";
                        global $is_multiple_membership_feature;
                        $hashTotal = $insMsg['total'];
                        $hashOrder = $insMsg['order_number'];

                        if($arm_payment_mode == "sandbox")
                        {
                            $tmphashOrder = 1;
                            $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $tmphashOrder . $hashTotal));    
                        }
                        else
                        {
                            $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
                        }

                        if ($StringToHash == $insMsg['key']) {
                            $customs = explode('|', $insMsg['li_0_option_0_value']);
                            $tax_percentage = isset($insMsg['li_0_option_1_value']) ? $insMsg['li_0_option_1_value'] : 0 ;
                            $entry_id = $customs[0];

                            $arm_token = $hashOrder;
                            $arm_transaction_id = $hashInvoice;
                            $arm_subscr_id = $hashOrder;
                            $arm_subcription_id_field_name = "order_number";
                            $arm_token_field_name = "order_number";
                            $arm_transaction_field_name = "invoice_id";

                            $arm_webhook_save_membership_data = array();
                            $arm_webhook_save_membership_data = apply_filters('arm_modify_payment_webhook_data', $arm_webhook_save_membership_data, $insMsg, '2checkout', $arm_token, $arm_transaction_id, $entry_id, $arm_subscr_id, $arm_subcription_id_field_name, $arm_token_field_name, $arm_transaction_field_name);
                        }
                    }
                }

                $arm_result_return_date = date("YmdHis");
                echo "Verified OK!";
                if(!empty($_REQUEST['md5_hash']))
                {
                    echo "<EPAYMENT>".$arm_result_return_date."|".$_REQUEST['md5_hash']."</EPAYMENT>";
                }
                else if(!empty($_REQUEST['HASH']))
                {
                    echo "<EPAYMENT>".$arm_result_return_date."|".$_REQUEST['HASH']."</EPAYMENT>";    
                }
                die;
            }
            
        }

        function arm_change_pending_gateway_outside($user_pending_pgway, $plan_ID, $user_id) {
            global $is_free_manual, $ARMember;
            if ($is_free_manual) {
                $key = array_search('2checkout', $user_pending_pgway);
                unset($user_pending_pgway[$key]);
            }
            return $user_pending_pgway;
        }

    }

}
global $arm_2checkout;
$arm_2checkout = new ARM_2checkout();