<?php
if (!class_exists('ARM_Admin_mycred_feature'))
{
    class ARM_Admin_mycred_feature 
    {
        var $ismyCREDFeature;
        function __construct()
        {
            global $wpdb, $ARMember;
            $arm_admin_mycred_feature = get_option('arm_is_mycred_feature');
            $this->ismyCREDFeature = ($arm_admin_mycred_feature == '1') ? true : false;
            add_action('mycred_deactivation',array($this, 'arm_mycred_deactivation'));

            if($this->ismyCREDFeature == true) {

                add_filter('arm_get_payment_gateways', array($this, 'arm_add_mycred_payment_gateways'));
                add_filter('arm_get_payment_gateways_in_filters', array($this, 'arm_add_mycred_payment_gateways'));
                add_filter('arm_filter_gateway_names', array($this, 'arm_filter_gateway_names_func'), 10);
                add_filter('arm_allowed_payment_gateways', array($this, 'arm_payment_allowed_gateways'), 10, 3);
                add_filter('arm_not_display_payment_mode_setup', array($this, 'arm_not_display_payment_mode_setup_func'), 10, 1);
                add_action('arm_payment_gateway_validation_from_setup', array($this, 'arm_payment_gateway_form_submit_action_mycred'), 10, 4);
                add_action('arm_payment_related_common_message', array($this, 'arm_payment_related_common_message_func'), 10, 1);
            }
        }
        function arm_mycred_deactivation()
        {
            update_option('arm_is_mycred_feature', 0);
        }

        function arm_payment_related_common_message_func($common_messages) {
            
            $mycred_fail_payment_msg = (isset($common_messages['arm_payment_fail_mycred'])) ? $common_messages['arm_payment_fail_mycred'] : esc_html__('Sorry something went wrong while processing payment with myCred.', 'ARMember');
            $mycred_not_enough_point_msg = (isset($common_messages['arm_not_enough_mycred_point'])) ? $common_messages['arm_not_enough_mycred_point'] : esc_html__('You have not enough myCred Point(s).', 'ARMember');
        ?>
            <tr class="form-field">
                <th class="arm-form-table-label"><label for="arm_payment_fail_stripe"><?php esc_html_e('Payment Fail (myCred)', 'ARMember') ?></label></th>
                <td class="arm-form-table-content">
                    <input type="text" name="arm_common_message_settings[arm_payment_fail_mycred]" id="arm_payment_fail_stripe" value="<?php echo $mycred_fail_payment_msg; ?>"/>
                </td>
            </tr>

            <tr class="form-field">
                <th class="arm-form-table-label"><label for="arm_payment_fail_stripe"><?php esc_html_e('Not enough  myCred points', 'ARMember') ?></label></th>
                <td class="arm-form-table-content">
                    <input type="text" name="arm_common_message_settings[arm_not_enough_mycred_point]" id="arm_payment_fail_stripe" value="<?php echo $mycred_not_enough_point_msg; ?>"/>
                </td>
            </tr>

        <?php }

        function arm_add_mycred_payment_gateways($default_payment_gateways) {
            global $arm_payment_gateways;
            $default_payment_gateways['mycred']['gateway_name'] = 'myCred';
            return $default_payment_gateways;
        }

        function arm_filter_gateway_names_func($pgname) {
            $pgname['mycred'] = __('myCred', 'ARMember');
            return $pgname;
        }

        function arm_payment_allowed_gateways($allowed_gateways, $plan_obj, $plan_options) {
            $allowed_gateways['mycred'] = "1";
            return $allowed_gateways;
        }

        function arm_not_display_payment_mode_setup_func($doNotDisplayPaymentMode) {
            array_push($doNotDisplayPaymentMode, 'mycred');
            return $doNotDisplayPaymentMode;
        }

        function arm_after_payment_gateway_listing_section_func($gateway_name, $gateway_options) { 
            if( 'mycred' == $gateway_name) {
                $point_exchange = 1;
                if(!empty($gateway_options['point_exchange'])) {
                    $point_exchange = $gateway_options['point_exchange'];
                }
                $point_exchange = number_format((float)$point_exchange, 3, '.', '');
        ?>

            <tr class="form-field">
                <th class="arm-form-table-label"><label><?php echo sprintf(__('%d Point', 'ARMember'), 1);?> = </label></th>
                <td class="arm-form-table-content">
                    <input type="text" class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_mycred_point_exchange" name="payment_gateway_settings[mycred][point_exchange]" value="<?php echo $point_exchange; ?>">
                </td>
            </tr>
        <?php
            }
        }

        function arm_payment_gateway_form_submit_action_mycred($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0) {

            global $wpdb, $ARMember, $arm_membership_setup, $arm_subscription_plans, $arm_manage_coupons, $payment_done, $arm_payment_gateways, $is_free_manual, $arm_mycred_feature, $arm_global_settings;
            
            $is_free_manual = false;

            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            $currency = $arm_payment_gateways->arm_get_global_currency();
            if ($payment_gateway == 'mycred' && isset($all_payment_gateways['mycred']) && !empty($all_payment_gateways['mycred'])) 
            {

                $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
                
                $gateway_options = get_option('arm_payment_gateway_settings');
                $pgoptions = maybe_unserialize($gateway_options);

                $current_payment_gateway = (isset($posted_data['payment_gateway'])) ? $posted_data['payment_gateway'] : '';
                if ($current_payment_gateway == '') 
                {
                    $current_payment_gateway = (isset($posted_data['_payment_gateway'])) ? $posted_data['_payment_gateway'] : '';
                }
                
                if (!empty($entry_data) && $current_payment_gateway == $payment_gateway) 
                {
                    $payment_mode_ = !empty($posted_data['arm_payment_mode']['mycred']) ? $posted_data['arm_payment_mode']['mycred'] : 'both';

                    $recurring_payment_mode = 'manual_subscription';
                    
                    if ($payment_mode_ == 'both') 
                    {
                        $recurring_payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                    } else {
                        $recurring_payment_mode = $payment_mode_;
                    }

                    $form_id = $entry_data['arm_form_id'];
                    $user_id = $entry_data['arm_user_id'];
                    
                    $entry_values = $entry_data['arm_entry_value'];
                    $payment_cycle = $entry_values['arm_selected_payment_cycle']; 

                    $tax_percentage =  isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0;
                    $user_country = get_user_meta($user_id, 'country', true );
                    
                    $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",",$entry_values['arm_user_old_plan']) : array();
                    $setup_id = (isset($entry_values['setup_id']) && !empty($entry_values['setup_id'])) ? $entry_values['setup_id'] : 0 ;
                    $user_email_add = $entry_data['arm_entry_email'];
                    if (is_user_logged_in()) {
                        $user_obj = get_user_by( 'ID', $user_id);
                        $user_name = $user_obj->first_name." ".$user_obj->last_name;
                        $user_email_add = $user_obj->user_email;
                    }else { 
                        $user_name = $entry_data['arm_entry_value']['first_name']." ".$entry_data['arm_entry_value']['last_name'];
                    }
                    
                    $plan_id = (!empty($posted_data['subscription_plan'])) ? $posted_data['subscription_plan'] : 0;
                    if ($plan_id == 0) {
                        $plan_id = (!empty($posted_data['_subscription_plan'])) ? $posted_data['_subscription_plan'] : 0;
                    }
                    
                    $plan_action = 'new_subscription';
                    $oldPlanIdArray = (isset($posted_data['old_plan_id']) && !empty($posted_data['old_plan_id'])) ? explode(",", $posted_data['old_plan_id']) : 0;
                    $plan = new ARM_Plan($plan_id);
                    
                    $plan_id = $plan->ID;
                    $plan_payment_type = $plan->payment_type;
                    $is_recurring = $plan->is_recurring();

                    if ($is_recurring) 
                    {
                        $setup_id = $posted_data['setup_id'];
                        $payment_mode_ = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                            if(isset($posted_data['arm_payment_mode']['mycred'])){
                                $payment_mode_ = !empty($posted_data['arm_payment_mode']['mycred']) ? $posted_data['arm_payment_mode']['mycred'] : 'manual_subscription';
                            }
                            else{
                                $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
                                if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                                    $setup_modules = $setup_data['setup_modules'];
                                    $modules = $setup_modules['modules'];
                                    $payment_mode_ = $modules['payment_mode']['mycred'];
                                }
                            }


                            $payment_mode = 'manual_subscription';
                            if ($payment_mode_ == 'both') {
                                $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                            } else {
                                $payment_mode = $payment_mode_;
                            }
                        $payment_mode = 'manual_subscription';
                    }
                    else{
                        $payment_mode = '';
                    }
                    
                    if (!empty($oldPlanIdArray)) {
                        if (in_array($plan_id, $oldPlanIdArray)) {
                            $plan_action = 'renew_subscription';
                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                            if($is_recurring_payment){
                                $plan_action = 'recurring_payment';
                                $planData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                if (!empty($oldPlanDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $oldPlanDetail);
                                }
                            }
                        }
                        else{
                            $plan_action = 'change_subscription';
                        }
                    }
                   
                    $plan_name = !empty($plan->name) ? $plan->name : "Plan Name";
                    $recurring_data = '';
                    if($plan->is_recurring()) {
                        $recurring_data = $plan->prepare_recurring_data($payment_cycle);
                        $amount = $recurring_data['amount'];
                    } else {
                        $amount = !empty($plan->amount) ? $plan->amount : 0;
                    }

                    $amount = str_replace(",", "", $amount);
                    $amount = number_format((float)$amount, 2, '.','');
                    
                    $iscouponfeature = false;
                    $arm_is_trial = '0';
                    $extraParam = array();
                    
                    if ($plan_action == 'new_subscription' || 'change_subscription' == $plan_action) {
                        $is_trial = false;
                        $allow_trial = true;
                        if (is_user_logged_in()) {
                            $user_id = get_current_user_id();
                            $user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            //echo "<br>reputelog | user_plans : <pre>";print_r($user_plan);echo "</pre>";
                            /*if(!empty($user_plan)) {
                                $allow_trial = false;
                            }*/
                            if(!empty($user_plan) && in_array($plan_id, $user_plan)) {
                                //echo "<br>reputelog not_allow-2";
                                $allow_trial = false;
                            }
                        }
                        
                        if ($plan->has_trial_period() && $allow_trial) {
                            
                            $trial_period = $plan->options['trial']['type'];
                            $trial_type = "";
                            $trial_interval = "";
                            switch ($trial_period) {
                                case 'D':
                                    $trial_type = "Day";
                                    $trial_interval = $plan->options['trial']['days'];
                                    break;
                                case 'M':
                                    $trial_type = "Month";
                                    $trial_interval = $plan->options['trial']['months'];
                                    break;
                                case 'Y':
                                    $trial_type = "Year";
                                    $trial_interval = $plan->options['trial']['years'];
                                    break;
                            }
                            $is_trial = true;
                            $arm_is_trial = '1';
                            $amount = $plan->options['trial']['amount'];
                            /*$trial_period = $plan->options['trial']['period'];
                            $trial_interval = $plan->options['trial']['interval'];*/
                            $extraParam['trial'] = array(
                                    'amount' => $amount,
                                    'period' => $trial_period,
                                    'interval' => $trial_interval,
                                    'type' => $trial_type,
                                );
                        }
                    }
                    $extraParam['plan_amount'] = $amount;
                    $arm_coupon_discount_type = '';
                    $arm_coupon_discount = 0;
                    $discount_amt = $amount;
                    
                    $arm_coupon_on_each_subscriptions = 0;
                    $arm_coupon_discount = 0;
                    $arm_coupon_discount_type = "";
                    if ($arm_manage_coupons->isCouponFeature && isset($posted_data['arm_coupon_code']) && !empty($posted_data['arm_coupon_code'])) {

                        $couponApply = $arm_manage_coupons->arm_apply_coupon_code($posted_data['arm_coupon_code'], $plan, $setup_id, $payment_cycle, $arm_user_old_plan);

                        if($couponApply["status"] == "success") {
                            $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                            $discount_amt = isset($couponApply['total_amt']) ? $couponApply['total_amt'] : $amount;
                            $arm_coupon_discount = (isset($couponApply['discount']) && !empty($couponApply['discount'])) ? $couponApply['discount'] : 0;
                            $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $currency : "%";
                            $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : '0';
                            
                            $extraParam['coupon'] = array(
                                'coupon_code' => $posted_data['arm_coupon_code'],
                                'amount' => $coupon_amount,
                                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions
                            );
                        }
                    } else {
                        $posted_data['arm_coupon_code'] = '';
                    }
                    
                    $discount_amt = str_replace(",", "", $discount_amt);
                    
                    if($tax_percentage > 0){
                        /*$tax_amount =($amount*$tax_percentage)/100;
                        $tax_amount = number_format((float)$tax_amount, 2, '.','');
                        $amount = $amount+$tax_amount;*/

                        $tax_discount_amt =($discount_amt*$tax_percentage)/100;
                        $tax_discount_amt = number_format((float)$tax_discount_amt, 2, '.','');
                        $discount_amt = $discount_amt+$tax_discount_amt;
                        $extraParam['tax_percentage'] = $tax_percentage;
                        $extraParam['tax_amount'] = $tax_discount_amt;
                    }
                    //$amount = number_format((float)$amount, 2, '.','');
                    $discount_amt = number_format((float)$discount_amt, 2, '.','');
                    $discount_amt = (float) $discount_amt;
                        
                    $extraParam['paid_amount'] = $discount_amt;

                    $mycred_current_balance = $arm_mycred_feature->arm_get_mycred_points_by_user($user_id);
                    $exchange_point = !empty($payment_gateway_options['point_exchange']) ? $payment_gateway_options['point_exchange'] : 0;

                    $mycred_exchange_rate = $arm_mycred_feature->arm_convert_amount_to_points($discount_amt, $exchange_point);
                    
                    if ((($discount_amt <= 0 || $discount_amt == '0') && $recurring_payment_mode == 'manual_subscription' && $plan->is_recurring()) || (!$plan->is_recurring() && ($discount_amt <= 0 || $discount_amt == '0')) || ($mycred_exchange_rate <= 0 || $discount_amt == '0'))
                    {

                        $mycred_response = array();
                        $current_user_id = 0;
                        if (is_user_logged_in()) {
                            $current_user_id = get_current_user_id();
                        }

                        $mycred_response['arm_user_id'] = $current_user_id;
                        $mycred_response['arm_plan_id'] = $plan->ID;
                        $mycred_response['arm_payment_gateway'] = 'mycred';
                        $mycred_response['arm_payment_type'] = $plan->payment_type;
                        $mycred_response['arm_token'] = '-';
                        $mycred_response['arm_payer_email'] = $user_email_add;
                        $mycred_response['arm_receiver_email'] = '';
                        $mycred_response['arm_transaction_id'] = '-';
                        $mycred_response['arm_transaction_payment_type'] = $plan->payment_type;
                        $mycred_response['arm_transaction_status'] = 'completed';
                        $mycred_response['arm_payment_mode'] = 'manual_subscription';
                        $mycred_response['arm_payment_date'] = current_time('mysql');
                        $mycred_response['arm_amount'] = $discount_amt;
                        $mycred_response['arm_currency'] = $currency;
                        $mycred_response['arm_coupon_code'] = $posted_data['arm_coupon_code'];
                        $mycred_response['arm_extra_vars'] = maybe_serialize($extraParam);
                        $mycred_response['arm_is_trial'] = $arm_is_trial;
                        $mycred_response['arm_created_date'] = current_time('mysql');
                        $mycred_response['arm_display_log'] = '1';
                        $mycred_response['arm_coupon_discount'] = $arm_coupon_discount;
                        $mycred_response['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                        $mycred_response['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subscriptions;

                        $payment_log_id = $arm_payment_gateways->arm_save_payment_log($mycred_response);
                        $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                        $is_free_manual = true;

                        if($arm_manage_coupons->isCouponFeature && !empty($posted_data['arm_coupon_code']) && !empty($arm_coupon_on_each_subscriptions)) {
                                $payment_done["coupon_on_each"] = TRUE;
                                $payment_done["trans_log_id"] = $payment_log_id;
                        }
                        
                        return $payment_done;
                    } else {
                        if($mycred_current_balance > 0 && $mycred_current_balance > $mycred_exchange_rate) {
                            $point_status = $arm_mycred_feature->arm_update_mycred_points_by_user($user_id, $mycred_exchange_rate, $plan_id);
                            if($point_status == true) {

                                $arm_redirecturl = $entry_values['setup_redirect'];
                                if (empty($arm_redirecturl)) {
                                    $arm_redirecturl = ARM_HOME_URL;
                                }
                                
                                /*$arm_payumoney_webhookurl = '';
                                $arm_payumoney_webhookurl = $arm_global_settings->add_query_arg("arm-listener", "arm_payumoney_api", get_home_url() . "/");*/                            
                                
                                $payment_data = array(
                                    'arm_user_id' => $user_id,
                                    'arm_plan_id' => $plan->ID,
                                    'arm_payment_gateway' => 'mycred',
                                    'arm_payment_type' => $plan->payment_type,
                                    'arm_payer_email' => $user_email_add,
                                    'arm_receiver_email' => '',
                                    'arm_transaction_id' => '-',
                                    'arm_token' => '',
                                    'arm_transaction_payment_type' => $plan->payment_type,
                                    'arm_transaction_status' => 'completed',
                                    'arm_payment_mode' => 'manual_subscription',
                                    'arm_payment_date' => current_time('mysql'),
                                    'arm_amount' => floatval($discount_amt),
                                    'arm_is_trial' => $arm_is_trial,
                                    'arm_currency' => $currency,
                                    'arm_coupon_code' => isset($posted_data['arm_coupon_code']) ? $posted_data['arm_coupon_code'] : '',
                                    'arm_coupon_discount' => $arm_coupon_discount,
                                    'arm_coupon_discount_type' => $arm_coupon_discount_type,
                                    'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                    'arm_created_date' => current_time('mysql'),
                                    'arm_display_log' => '1'
                                );

                                $payment_data['arm_extra_vars'] = maybe_serialize($extraParam);
                                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);

                                if (isset($posted_data['action']) && in_array($posted_data['action'], array('arm_shortcode_form_ajax_action', 'arm_membership_setup_form_ajax_action'))) {

                                    $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);

                                    return $payment_done;
                                } else {
                                    $err_msg = isset($arm_global_settings->common_message['arm_payment_fail_mycred']) ? $arm_global_settings->common_message['arm_payment_fail_mycred'] : esc_html__('You have not enough myCred Point(s).', 'ARMember');
                                    $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                    return $payment_done;
                                }
                            } else {
                                
                                $err_msg = isset($arm_global_settings->common_message['arm_not_enough_mycred_point']) ? $arm_global_settings->common_message['arm_not_enough_mycred_point'] : esc_html__('You have not enough myCred Point(s).', 'ARMember');
                                $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                return $payment_done;
                            }
                        } else {
                            $err_msg = isset($arm_global_settings->common_message['arm_not_enough_mycred_point']) ? $arm_global_settings->common_message['arm_not_enough_mycred_point'] : esc_html__('You have not enough myCred Point(s).', 'ARMember');
                            $payment_done = array('status' => FALSE, 'error' => $err_msg);
                            return $payment_done;
                        }
                    }                           
                }
            } 
        }

        function arm_is_mycred_active() {
            $return = false;
            if(is_plugin_active( 'mycred/mycred.php' )) {
                $arm_is_mycred_feature = get_option('arm_is_mycred_feature');
                $return = ($arm_is_mycred_feature == '1') ? true : false;
            }
            return $return;
        }

    }
}
global $arm_admin_mycred_feature, $arm_is_mycred_feature_active;
$arm_admin_mycred_feature = new ARM_Admin_mycred_feature();
$arm_is_mycred_active = get_option('arm_is_mycred_feature');
$arm_is_mycred_feature_active = 0;
if(!empty($arm_is_mycred_active) && $arm_is_mycred_active==1)
{
    $arm_is_mycred_feature_active = 1;
    add_filter('mycred_setup_hooks','arm_mycred_hook');
    function arm_mycred_hook($arm_mycred_installed)
    {
        $arm_mycred_installed['arm_mycred'] = array(
            'title' => __('ARMember Membership', 'ARMember'),
            'description' => __('ARMember Premium Plugin - Buy Membership Plan Hook', 'ARMember'),
            'callback' => array('ARM_mycred_feature')
            );
        return $arm_mycred_installed;
    }
    add_action('mycred_load_hooks','arm_mycred_custom_hook');
    function arm_mycred_custom_hook()
    {
        if(file_exists(MEMBERSHIP_CLASSES_DIR . "/class.arm_mycred_feature.php")){
            require_once( MEMBERSHIP_CLASSES_DIR . "/class.arm_mycred_feature.php");
        }
    }
}