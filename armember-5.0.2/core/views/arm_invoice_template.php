<?php
    $allowed_page_global = 0;
    $armuser_id = 0;
    if(is_user_logged_in())
    {
        if (current_user_can('arm_manage_transactions')) 
        {
           $allowed_page_global = 1;
        }
        else {

            $armuser_id = get_current_user_id();
        }
    }
    else {
        exit;
    }
    ?>

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font: 12pt "Tahoma";
        }

        .page {
            width: 700px;
            min-height: 600px;
            padding: 20px;
            margin: 0 auto;
            background: white;
        }
        

        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body {-webkit-print-color-adjust: exact;}
            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>

    <script type="text/javascript">
        function arm_print_invoice_content() {
            window.print();
        }
    </script>

    <?php
        $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
        if($arm_invoice_tax_feature) {
    ?>

        <div class="arm_invoice_detail_popup arm_invoice_detail_popup_wrapper page">

            <div class="popup_wrapper_inner" style="overflow: hidden;">

                <div class="popup_content_text arm_invoice_detail_popup_text" id="arm_invoice_detail_popup_text">

                    <?php
                    global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $global_currency_sym, $arm_transaction, $arm_member_forms;
                    $log_id = $_GET['log_id'];
                    $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();
                    $log_type = $_GET['log_type'];
                    /* Get Edit Rule Form HTML */
                    if (!empty($log_id) && $log_id != 0) {

                        $log_user_id_qur = "";
                        if(!empty($armuser_id) && empty($allowed_page_global))
                        {
                            $log_user_id_qur = " AND arm_user_id='".$armuser_id."' ";
                        }
                        if ($log_type == 'bt_log') {
                            $log_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_log_id`='" . $log_id . "' ".$log_user_id_qur);
                            if (!empty($log_data)) {
                                $log_detail = array(
                                    'arm_log_id' => $log_data->arm_log_id,
                                    'arm_invoice_id' => $log_data->arm_invoice_id,
                                    'arm_user_id' => $log_data->arm_user_id,
                                    'arm_plan_id' => $log_data->arm_plan_id,
                                    'arm_payment_gateway' => 'bank_transfer',
                                    'arm_payment_type' => $log_data->arm_payment_type,
                                    'arm_token' => '',
                                    'arm_payer_email' => $log_data->arm_payer_email,
                                    'arm_receiver_email' => '',
                                    'arm_transaction_id' => $log_data->arm_transaction_id,
                                    'arm_transaction_payment_type' => '-',
                                    'arm_payment_date' => $log_data->arm_created_date,
                                    'arm_amount' => $log_data->arm_amount,
                                    'arm_currency' => $log_data->arm_currency,
                                    'arm_extra_vars' => $log_data->arm_extra_vars,
                                    'arm_coupon_code' => $log_data->arm_coupon_code,
                                    'arm_coupon_discount' => $log_data->arm_coupon_discount,
                                    'arm_coupon_discount_type' => $log_data->arm_coupon_discount_type,
                                    'arm_created_date' => $log_data->arm_created_date
                                );
                            }
                        } else {
                            //$log_detail = $arm_transaction->arm_get_single_transaction($log_id);
                            $log_detail = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_log_id`='" . $log_id . "'".$log_user_id_qur, ARRAY_A);
                        }
                        if(empty($log_detail))
                        {
                            exit;
                        }
                        do_action('arm_before_displaying_invoice_content');
                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                        $all_general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
                        $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
                        if (!empty($all_general_settings)) {
                            $content = $all_global_settings['general_settings']['arm_invoice_template'];
                            if (!empty($content)) {
                            
                                $user_info = get_userdata($log_detail['arm_user_id']);
                                $user_first_name = get_user_meta($log_detail['arm_user_id'], 'first_name', true);
                                $user_last_name = get_user_meta($log_detail['arm_user_id'], 'last_name', true);
                                $user_plan_data = get_user_meta($log_detail['arm_user_id'], 'arm_user_plan_' . $log_detail['arm_plan_id'], true);
                                $plan_detail = (isset($user_plan_data['arm_current_plan_detail']) && !empty($user_plan_data['arm_current_plan_detail'])) ? $user_plan_data['arm_current_plan_detail'] : array();
                                
                              
                                if (!empty($plan_detail)) {
                                $curPlan = new ARM_Plan(0);
                                    $curPlan->init((object) $plan_detail);
                                } else {
                                    $curPlan = new ARM_Plan($log_detail['arm_plan_id']);
                                }
                                

                                if ($log_detail['arm_payment_gateway'] == '') {
                                    $i_payment_gateway = __('Manual', 'ARMember');
                                } else {
                                    $i_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($log_detail['arm_payment_gateway']);
                                }

                                $payment_cycle = isset($user_plan_data['arm_payment_cycle']) ? $user_plan_data['arm_payment_cycle'] : 0;
                                $i_plan_description = $curPlan->description;

                                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                $t_currency = (isset($log_detail['arm_currency']) && !empty($log_detail['arm_currency'])) ? strtoupper($log_detail['arm_currency']) : strtoupper($global_currency);
                                $currency = (isset($all_currencies[$t_currency])) ? $all_currencies[$t_currency] : $global_currency_sym;
                                $transAmount = '';
                                $extraVars = (!empty($log_detail['arm_extra_vars'])) ? maybe_unserialize($log_detail['arm_extra_vars']) : array();

                                if (!empty($extraVars) && !empty($extraVars['plan_amount']) && $extraVars['plan_amount'] != 0 && $extraVars['plan_amount'] != $log_detail['arm_amount']) {
                                    $transAmount .= '<span class="arm_transaction_list_plan_amount">' . $arm_payment_gateways->arm_prepare_amount($t_currency, $extraVars['plan_amount']) . '</span>';
                                }
                                $transAmount .= '<span class="arm_transaction_list_paid_amount">';
                                if (!empty($log_detail['arm_amount']) && $log_detail['arm_amount'] > 0) {
                                    $transAmount .= $arm_payment_gateways->arm_prepare_amount($t_currency, $log_detail['arm_amount']);
                                    if ($global_currency_sym == $currency && strtoupper($global_currency) != $t_currency) {
                                        $transAmount .= ' (' . $t_currency . ')';
                                    }
                                } else {
                                    $transAmount .= $arm_payment_gateways->arm_prepare_amount($t_currency, $log_detail['arm_amount']);
                                }
                                $transAmount .= '</span>';
                                $transAmount = $arm_payment_gateways->arm_prepare_amount($t_currency, $log_detail['arm_amount']);

                                $trialInterval = '';
                                if (!empty($extraVars) && isset($extraVars['trial'])) {
                                    $trialInterval = $extraVars['trial']['interval'] . " ";

                                    if ($extraVars['trial']['period'] == 'Y') {
                                        $trialInterval .= ($trialInterval > 1) ? __('Years', 'ARMember') : __('Year', 'ARMember');
                                    } elseif ($extraVars['trial']['period'] == 'M') {
                                        $trialInterval .= ($trialInterval > 1) ? __('Months', 'ARMember') : __('Month', 'ARMember');
                                    } elseif ($extraVars['trial']['period'] == 'W') {
                                        $trialInterval .= ($trialInterval > 1) ? __('Weeks', 'ARMember') : __('Week', 'ARMember');
                                    } elseif ($extraVars['trial']['period'] == 'D') {
                                        $trialInterval .= ($trialInterval > 1) ? __('Days', 'ARMember') : __('Day', 'ARMember');
                                    }
                                }
                                $arm_trial_amount = isset($extraVars['trial']['amount']) ? $extraVars['trial']['amount'] : 0;

                                $arm_tax_amount = '-';
                                if(!empty($extraVars) && isset($extraVars['tax_amount'])){
                                    $arm_tax_amount = ($extraVars['tax_amount']!='') ? $arm_payment_gateways->arm_prepare_amount($t_currency, $extraVars['tax_amount']): '-';
                                   
                                }

                                $arm_tax_percentage = '-';
                                if(!empty($extraVars) && isset($extraVars['tax_percentage'])){
                                    $arm_tax_percentage = ($extraVars['tax_percentage']!='') ? $extraVars['tax_percentage'].'%': '-';
                                   
                                }


                                $arm_used_coupon_discount = '';
                                if (!empty($log_detail['arm_coupon_code'])) {
                                    if (!empty($log_detail['arm_coupon_discount']) && $log_detail['arm_coupon_discount'] > 0) {
                                        $arm_used_coupon_discount = number_format((float) $log_detail['arm_coupon_discount'], 2);
                                        $arm_used_coupon_discount.= ($log_detail['arm_coupon_discount_type'] != 'percentage') ? " " . $log_detail['arm_coupon_discount_type'] : "%";
                                    } else {
                                        $arm_used_coupon_discount = 0;
                                    }
                                } else {
                                    $arm_used_coupon_discount = '-';
                                };
                                
                                
                                $user_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($log_detail['arm_plan_id']);
                                
                                
                                $payer_email = '';
                                if($log_detail['arm_payer_email'] == '')
                                {
                                    $extra = maybe_unserialize($log_detail['arm_extra_vars']);
                                    if($extra != '')
                                    {
                                    if(array_key_exists('manual_by',$extra)){

                                        $payer_email = '<em>' . __($extra['manual_by'], 'ARMember') . '</em>';
                                    }
                                    }
                                }
                                else
                                {
                                    $payer_email = $log_detail['arm_payer_email'];
                                }


                                $date_format = $arm_global_settings->arm_get_wp_date_format();

                                $historyRecords = $wpdb->get_row("SELECT * FROM ".$ARMember->tbl_arm_activity." WHERE arm_type = 'membership' AND arm_user_id={$log_detail['arm_user_id']} AND arm_action != 'recurring_subscription' AND arm_item_id = {$log_detail['arm_plan_id']} ORDER BY arm_activity_id DESC LIMIT 0,1", ARRAY_A);

                                $historyContent = !empty($historyRecords['arm_content']) ? maybe_unserialize($historyRecords['arm_content']) : '';

                                $arm_paid_date = !empty($historyRecords['arm_date_recorded']) ? strtotime($historyRecords['arm_date_recorded']) : '';

                                $arm_start_plan_date = !empty($historyContent['start']) ? date_i18n($date_format, $historyContent['start']) : '';
                                $arm_end_plan_date = !empty($historyContent['expire']) ? date_i18n($date_format, $historyContent['expire']) : __('Never', 'ARMember');

                                $content = str_replace('{ARM_PLAN_START_DATE}', $arm_start_plan_date, $content);
                                $content = str_replace('{ARM_PLAN_END_DATE}', $arm_end_plan_date, $content);

                                $arm_used_coupon_code = (!empty($log_detail['arm_coupon_code'])) ? $log_detail['arm_coupon_code'] : '-';
                                $user_info_user_login = !empty($user_info->user_login) ? $user_info->user_login : '';
                                $content = str_replace('{ARM_INVOICE_USERNAME}', $user_info_user_login, $content);
                                $content = str_replace('{ARM_INVOICE_USERFIRSTNAME}', $user_first_name, $content);
                                $content = str_replace('{ARM_INVOICE_USERLASTNAME}', $user_last_name, $content);
                                $content = str_replace('{ARM_INVOICE_SUBSCRIPTIONNAME}', $user_plan_name, $content);
                                $content = str_replace('{ARM_INVOICE_SUBSCRIPTIONDESCRIPTION}', $i_plan_description, $content);
                                $content = str_replace('{ARM_INVOICE_GATEWAY}', $i_payment_gateway, $content);
                                $content = str_replace('{ARM_INVOICE_TRANSACTIONID}', $log_detail['arm_transaction_id'], $content);

                                $arm_invoice_id = $arm_global_settings->arm_manipulate_invoice_id($log_detail['arm_invoice_id']);

                                $content = str_replace('{ARM_INVOICE_INVOICEID}', $arm_invoice_id, $content);

                                $content = str_replace('{ARM_INVOICE_SUBSCRIPTIONID}', $log_detail['arm_token'], $content);
                                $content = str_replace('{ARM_INVOICE_AMOUNT}', $transAmount, $content);
                                $content = str_replace('{ARM_INVOICE_PAYMENTDATE}', date_i18n($date_time_format, strtotime($log_detail['arm_created_date'])), $content);
                                $content = str_replace('{ARM_INVOICE_PAYEREMAIL}', $payer_email, $content);
                                $content = str_replace('{ARM_INVOICE_TRIALAMOUNT}', $arm_payment_gateways->arm_prepare_amount($t_currency, $arm_trial_amount), $content);
                                $content = str_replace('{ARM_INVOICE_TRIALPERIOD}', $trialInterval, $content);
                                $content = str_replace('{ARM_INVOICE_COUPONCODE}', $arm_used_coupon_code, $content);
                                $content = str_replace('{ARM_INVOICE_COUPONAMOUNT}', $arm_used_coupon_discount, $content);
                                $content = str_replace('{ARM_INVOICE_TAXPERCENTAGE}', $arm_tax_percentage, $content);
                                $content = str_replace('{ARM_INVOICE_TAXAMOUNT}', $arm_tax_amount, $content);
                                $content = str_replace('{ARM_SUBSCRIPTION_START_DATE}', $arm_start_plan_date, $content);
                                $content = str_replace('{ARM_SUBSCRIPTION_END_DATE}', $arm_end_plan_date, $content);

                                $arm_subscription_amount = 0;
                                if(!empty($log_detail['arm_amount']) && $log_detail['arm_amount']>0)
                                {
                                    $arm_tax_amount_check = !empty($extraVars['tax_amount']) ? $extraVars['tax_amount'] : 0;
                                    $arm_amount_check = !empty($log_detail['arm_amount']) ? $log_detail['arm_amount'] : 0;
                                    $arm_subscription_amount = $arm_amount_check-$arm_tax_amount_check;
                                    if($arm_subscription_amount<0)
                                    {
                                        $arm_subscription_amount = 0;
                                    }
                                }
                                $arm_subscription_amount = $arm_payment_gateways->arm_prepare_amount($t_currency, $arm_subscription_amount);
                                $content = str_replace('{ARM_INVOICE_SUBSCRIPTIONAMOUNT}', $arm_subscription_amount, $content);

                                $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
                                foreach ($dbFormFields as $meta_key => $field) {

                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    $exclude_keys = array (
                                        'first_name', 'last_name', 'user_login', 'user_email', 'user_pass', 'repeat_pass',
                                        'arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section',
                                        'repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover', 'user_pass_', 'display_name', 'description',
                                    );
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $type = isset($field_options['type']) ? $field_options['type'] : array();
                                       
                                    if (!in_array($meta_key, $exclude_keys) && !in_array($type, array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {

                                        $custom_field_shortcode_pattern = '{ARM_INVOICE_'.trim($meta_key).'}';
                                        $custom_field_value = get_user_meta($log_detail['arm_user_id'], trim($meta_key), true);
                                        if(is_array($custom_field_value))
                                        {
                                            $custom_field_value = implode(',', $custom_field_value);
                                        }
                                        $content = str_replace($custom_field_shortcode_pattern, $custom_field_value, $content);
                                    }
                                }

                            }
                            $content = apply_filters('arm_after_display_invoice_content', $content, $log_detail, $log_id, $log_type);

                            echo stripslashes($content);
                        }
                        do_action('arm_after_displaying_invoice_content');
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    }
?>