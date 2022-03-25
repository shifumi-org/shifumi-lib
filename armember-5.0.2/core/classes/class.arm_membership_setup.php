<?php
if (!class_exists('ARM_membership_setup')) {

    class ARM_membership_setup {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            add_action('wp_ajax_arm_delete_single_setup', array($this, 'arm_delete_single_setup'));
            add_action('wp_ajax_arm_refresh_setup_items', array($this, 'arm_refresh_setup_items'));
            add_action('wp_ajax_arm_update_plan_form_gateway_selection', array($this, 'arm_update_plan_form_gateway_selection'));
            add_action('wp_ajax_arm_setup_shortcode_preview', array($this, 'arm_setup_shortcode_preview_func'));
            /* Membership Setup Wizard Form Shortcode Ajax Action */
            add_action('wp_ajax_arm_membership_setup_form_ajax_action', array($this, 'arm_membership_setup_form_ajax_action'));
            add_action('wp_ajax_nopriv_arm_membership_setup_form_ajax_action', array($this, 'arm_membership_setup_form_ajax_action'));
            add_action('arm_save_membership_setups', array($this, 'arm_save_membership_setups_func'));
            add_shortcode('arm_setup', array($this, 'arm_setup_shortcode_func'));
            add_shortcode('arm_setup_internal', array($this, 'arm_setup_shortcode_func_internal'));

            add_action('arm_before_render_membership_setup_form', array($this, 'arm_check_include_js_css'), 10, 2);
            add_action('wp_ajax_arm_renew_plan_action', array($this, 'arm_renew_update_plan_action_func'));
            add_action('wp_ajax_arm_update_card_action', array($this, 'arm_update_card_action_func'));
            add_action('wp_ajax_arm_membership_update_card_form_ajax_action', array($this, 'arm_membership_update_card_form_ajax_action'));

            //For configure plan preview data save
            add_action('wp_ajax_arm_save_configure_preview_data', array($this, 'arm_save_configure_signup_preview_data'));

            
            //add_action('wp', array($this, 'arm_membership_setup_preview_func'));
            add_action('arm_cancel_subscription_gateway_action', array($this, 'arm_cancel_bank_transfer_subscription'), 10, 2);


            // New hooks of payment functions
            add_filter('arm_calculate_payment_gateway_submit_data', array($this, 'arm_calculate_membership_data'), 10, 5);

            add_action('arm_webhook_update_next_recurring_data', array($this, 'arm_webhook_next_recurring_update_data'), 10, 3);

            add_filter('arm_modify_payment_webhook_data', array($this, 'arm_modify_payment_webhook_data'), 10, 10);
        } 
        function arm_save_configure_signup_preview_data(){
            global $ARMember, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_setups'], '0');

            $arm_transient_uniq_id = $_REQUEST['_wpnonce'];
            $arm_transient_form_data = maybe_serialize($_REQUEST);

            set_transient("arm_preview_transient_".$arm_transient_uniq_id, $arm_transient_form_data, 86400);
        }

        function arm_renew_update_plan_action_func() {
            global $ARMember, $arm_pay_per_post_feature;
            $arm_capabilities = '';
            $ARMember->arm_check_user_cap($arm_capabilities, '0');

            $plan_id = intval($_POST['plan_id']);
            $setup_id = intval($_POST['setup_id']);
            $paid_post = false;
            if( !empty( $arm_pay_per_post_feature->isPayPerPostFeature ) ){
                $paid_post_data = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $plan_id );
                if( !empty( $paid_post_data[0]['arm_subscription_plan_post_id'] ) ){
                    $paid_post = true;
                }
            }
            if(is_user_logged_in()) {
                $paid_post_attr = ' is_arm_paid_post="0" ';
                if( true == $paid_post ){
                    $paid_post_attr = ' is_arm_paid_post="1" paid_post_id="' . $paid_post_data[0]['arm_subscription_plan_post_id'] . '"';
                }
                echo do_shortcode('[arm_setup_internal id="' . $setup_id . '" hide_plans="1" '.$paid_post_attr.' subscription_plan="' . $plan_id . '"]');
            } else {
              global $arm_member_forms, $ARMember;
              $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');
              echo do_shortcode("[arm_form id='$default_login_form_id' is_referer='1']");
              $ARMember->enqueue_angular_script(true);
            }
            die;
        }

        function arm_cancel_bank_transfer_subscription($user_id, $plan_id) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_transaction, $arm_payment_gateways, $arm_manage_communication, $arm_debug_payment_log_id;



            if (!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0) {
                $arm_cancel_subscription_data = array();
                $arm_cancel_subscription_data = apply_filters('arm_gateway_cancel_subscription_data', $arm_cancel_subscription_data, $user_id, $plan_id, 'bank_transfer', '', '', '');

                if(!empty($arm_cancel_subscription_data)){

                    $arm_debug_log_data = array(
                        'user_id' => $user_id,
                        'plan_id' => $plan_id,
                        'cancel_subscription_data' => $arm_cancel_subscription_data,
                    );
                    do_action('arm_payment_log_entry', 'bank_transfer', 'bank transfer cancel subscription', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
                    
                    $arm_cancel_amount = !empty($arm_cancel_subscription_data['arm_cancel_amount']) ? $arm_cancel_subscription_data['arm_cancel_amount'] : 0;
                    do_action('arm_cancel_subscription_payment_log_entry', $user_id, $plan_id, 'bank_transfer', '', '', '', $arm_cancel_amount);
                }
            }
        }

        function arm_membership_setup_preview_func() {
            global $wpdb, $ARMember;
            if (isset($_REQUEST['arm_setup_preview']) && $_REQUEST['arm_setup_preview'] == '1') {
                if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_preview.php')) {
                    include(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_preview.php');
                }
                exit;
            }
        }

        function arm_membership_setup_form_ajax_action($setup_id = 0, $post_data = array()) {
            global $wp, $wpdb, $current_user, $arm_slugs, $arm_errors, $ARMember, $arm_member_forms, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans, $payment_done, $authorize_net_auth, $arm_manage_communication, $arm_transaction, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_debug_payment_log_id;
            $authorize_net_auth = array();
            $post_data = (!empty($_POST)) ? $_POST : $post_data;
            $setup_id = (!empty($post_data['setup_id']) && $post_data['setup_id'] != 0) ? intval($post_data['setup_id']) : $setup_id;
            $err_msg = $arm_global_settings->common_message['arm_general_msg'];
            $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
            $response = array('status' => 'error', 'type' => 'message', 'message' => $err_msg);
            $validate = true;
            $validate_msgs = array();
            if (!empty($setup_id) && $setup_id != 0 && !empty($post_data) && $post_data['setup_action'] == 'membership_setup') {
                do_action('arm_before_setup_form_action', $setup_id, $post_data);

                if (is_user_logged_in()) 
                {
                    $user_ID = get_current_user_id();
                    do_action('arm_modify_content_on_plan_change', $post_data, $user_ID);
                }

                /* Unset unused variables. */
                unset($post_data['ARMSETUPNEXT']);
                unset($post_data['ARMSETUPSUBMIT']);
                unset($post_data['setup_action']);
                $setup_data = $this->arm_get_membership_setup($setup_id);
                
                $setup_data = apply_filters('arm_setup_data_before_submit', $setup_data, $post_data);

                if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                    $form_slug = isset($post_data['arm_action']) ? sanitize_text_field($post_data['arm_action']) : '';
                    $form = new ARM_Form('slug', $form_slug);
                    $form_id = 0;

                    $plan_id = isset($post_data['subscription_plan']) ? intval($post_data['subscription_plan']) : 0;
                    if ($plan_id == 0) {
                        $plan_id = isset($post_data['_subscription_plan']) ? intval($post_data['_subscription_plan']) : 0;
                    }

                    $plan = new ARM_Plan($plan_id);
                    $plan_type = $plan->type;
                    $payment_gateway = isset($post_data['payment_gateway']) ? sanitize_text_field($post_data['payment_gateway']) : '';
                    if ($payment_gateway == '') {
                        $payment_gateway = isset($post_data['_payment_gateway']) ? sanitize_text_field($post_data['_payment_gateway']) : '';
                    }

                    if($plan->is_recurring()){
                        $payment_mode_ = !empty($post_data['arm_selected_payment_mode']) ? sanitize_text_field($post_data['arm_selected_payment_mode']) : 'manual_subscription';
                        if(isset($post_data['arm_payment_mode'][$payment_gateway])){
                            $payment_mode_ = !empty($post_data['arm_payment_mode'][$payment_gateway]) ? sanitize_text_field($post_data['arm_payment_mode'][$payment_gateway]) : 'manual_subscription';
                        }
                        else{
                            //$setup_data = $this->arm_get_membership_setup($setup_id);
                            //if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                                $setup_modules = $setup_data['setup_modules'];
                                $modules = $setup_modules['modules'];
                                $payment_mode_ = $modules['payment_mode'][$payment_gateway];
                            //}
                        }

                        $payment_mode = 'manual_subscription';
                        $c_mpayment_mode = "";
                        if(isset($post_data['arm_pay_thgough_mpayment']) && $post_data['arm_plan_type']=='recurring' && is_user_logged_in())
                        {
                            $current_user_id = get_current_user_id();
                            $current_user_plan_ids = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                            $current_user_plan_ids = !empty($current_user_plan_ids) ? $current_user_plan_ids : array();
                            $Current_M_PlanData = get_user_meta($current_user_id, 'arm_user_plan_' . $plan_id, true);
                            $Current_M_PlanDetails = $Current_M_PlanData['arm_current_plan_detail'];
                            if (!empty($current_user_plan_ids)) {
                                if(in_array($plan_id, $current_user_plan_ids) && !empty($Current_M_PlanDetails))
                                {
                                    $arm_cmember_paymentcycle = $Current_M_PlanData['arm_payment_cycle'];
                                    $arm_cmember_completed_recurrence = $Current_M_PlanData['arm_completed_recurring'];
                                    $arm_cmember_plan = new ARM_Plan(0);
                                    $arm_cmember_plan->init((object) $Current_M_PlanDetails);
                                    $arm_cmember_plan_data = $arm_cmember_plan->prepare_recurring_data($arm_cmember_paymentcycle);
                                    $arm_cmember_TotalRecurring = $arm_cmember_plan_data['rec_time'];
                                    if ($arm_cmember_TotalRecurring == 'infinite' || ($arm_cmember_completed_recurrence !== '' && $arm_cmember_completed_recurrence != $arm_cmember_TotalRecurring)) {
                                        $c_mpayment_mode = 1;
                                    }
                                }
                            }
                        }
                        if(empty($c_mpayment_mode))
                        {
                            if ($payment_mode_ == 'both') {
                            $payment_mode = !empty($post_data['arm_selected_payment_mode']) ? sanitize_text_field($post_data['arm_selected_payment_mode']) : 'manual_subscription';
                            } else {
                                $payment_mode = $payment_mode_;
                            }
                        }
                    }
                    else{
                        $payment_mode = '';
                    }

                    if ($payment_gateway == 'bank_transfer' && $plan->is_recurring()) {
                        $payment_mode = 'manual_subscription';
                    }
                    $post_data['arm_selected_payment_mode'] = $payment_mode;

                    $payment_cycle = 0;
                    if ($plan->is_recurring()) {
                        $payment_cycle = isset($post_data['payment_cycle_' . $plan_id]) ? intval($post_data['payment_cycle_' . $plan_id]) : 0;
                    }
                    $post_data['arm_selected_payment_cycle'] = $payment_cycle;

                    //To modify setup form data before submit it.
                    do_action('arm_before_submit_form_data', $post_data);

                    $user_info = wp_get_current_user();
                    $current_user_plan = array();
                    $user_id = $user_info->ID;
                    if (!empty($user_info->ID)) {
                        $entry_email = $user_info->user_email;
                        $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array();
                    } else {
                        $entry_email = sanitize_email($post_data['user_email']);
                    }

                    $setup_redirect = ARM_HOME_URL;
                    
                    
                    $redirection_settings = get_option('arm_redirection_settings');
                    $redirection_settings = maybe_unserialize($redirection_settings);
                    $arm_default_setup_url = (isset($redirection_settings['setup']['default']) && !empty($redirection_settings['setup']['default'])) ? $redirection_settings['setup']['default'] : ARM_HOME_URL;
                    
                    if (is_user_logged_in()) {
                        // IF same plan already exists in arm_user_plan_ids
                        if (in_array($plan_id, $current_user_plan)) {
                            
                            //renew or recurring
                            $PlanData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($PlanData)) {
                                $PlanDetail = isset($PlanData['arm_current_plan_detail']) ? $PlanData['arm_current_plan_detail'] : array();
                                if (!empty($PlanData)) {
                                    $same_old_plan = new ARM_Plan(0);
                                    $same_old_plan->init((object) $PlanDetail);
                                } else {
                                    $same_old_plan = new ARM_Plan($plan_id);
                                }

                                if ($same_old_plan->is_recurring()) {
                                    $oldPaymentMode = $PlanData['arm_payment_mode'];
                                    if ($oldPaymentMode == 'manual_subscription') {

                                        $oldPaymentCycle = $PlanData['arm_payment_cycle'];
                                        $completed_recurrence = $PlanData['arm_completed_recurring'];

                                        $same_plan_data = $same_old_plan->prepare_recurring_data($oldPaymentCycle);
                                        $oldPlanTotalRecurring = $same_plan_data['rec_time'];

                                        if ($oldPlanTotalRecurring == 'infinite' || ($completed_recurrence !== '' && $completed_recurrence < $oldPlanTotalRecurring)) {
                                            $payment_cycle = $oldPaymentCycle;
                                            $post_data['arm_selected_payment_cycle'] = $oldPaymentCycle;

                                            $payment_mode = $oldPaymentMode;
                                            $post_data['arm_selected_payment_mode'] = $oldPaymentMode;

                                            $plan = $same_old_plan;
                                        }
                                    } else if($oldPaymentMode == 'auto_debit_subscription' && !$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post) {
                                        //If old payment mode is auto-debit and same plan purchase then old subscription will canceled.
                                        do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                    }
                                }
                            }
                            
                            $arm_redirection_setup_change_type = (isset($redirection_settings['setup_renew']['type']) && !empty($redirection_settings['setup_renew']['type'])) ? $redirection_settings['setup_renew']['type'] : 'page';
                            if($arm_redirection_setup_change_type == 'page'){
                               $arm_redirection_setup_signup_page_id = (isset($redirection_settings['setup_renew']['page_id']) && !empty($redirection_settings['setup_renew']['page_id'])) ? $redirection_settings['setup_renew']['page_id'] : 0;
                               if(!empty($arm_redirection_setup_signup_page_id)){
                                   $setup_redirect = $arm_global_settings->arm_get_permalink('', $arm_redirection_setup_signup_page_id);
                               }
                               else{
                                   $setup_redirect = $arm_default_setup_url;
                               }
                            }
                            else if($arm_redirection_setup_change_type == 'url'){
                                $setup_redirect = (isset($redirection_settings['setup_renew']['url']) && !empty($redirection_settings['setup_renew']['url'])) ? $redirection_settings['setup_renew']['url'] : $arm_default_setup_url;
                            }
                            
                        }
                        else{
                           //change
                            $arm_redirection_setup_change_type = (isset($redirection_settings['setup_change']['type']) && !empty($redirection_settings['setup_change']['type'])) ? $redirection_settings['setup_change']['type'] : 'page';
                            if($arm_redirection_setup_change_type == 'page'){
                               $arm_redirection_setup_signup_page_id = (isset($redirection_settings['setup_change']['page_id']) && !empty($redirection_settings['setup_change']['page_id'])) ? $redirection_settings['setup_change']['page_id'] : 0;
                               if(!empty($arm_redirection_setup_signup_page_id)){
                                   $setup_redirect = $arm_global_settings->arm_get_permalink('', $arm_redirection_setup_signup_page_id);
                               }
                               else{
                                   $setup_redirect = $arm_default_setup_url;
                               }
                            }
                            else if($arm_redirection_setup_change_type == 'url'){
                                $setup_redirect = (isset($redirection_settings['setup_change']['url']) && !empty($redirection_settings['setup_change']['url'])) ? $redirection_settings['setup_change']['url'] : $arm_default_setup_url;
                            }
                        }
                    }
                    else{
                        $arm_redirection_setup_signup_type = (isset($redirection_settings['setup_signup']['type']) && !empty($redirection_settings['setup_signup']['type'])) ? $redirection_settings['setup_signup']['type'] : 'page';
                        if($arm_redirection_setup_signup_type == 'page'){
                           $arm_redirection_setup_signup_page_id = (isset($redirection_settings['setup_signup']['page_id']) && !empty($redirection_settings['setup_signup']['page_id'])) ? $redirection_settings['setup_signup']['page_id'] : 0;
                           if(!empty($arm_redirection_setup_signup_page_id)){
                               $setup_redirect = $arm_global_settings->arm_get_permalink('', $arm_redirection_setup_signup_page_id);
                           }
                           else{
                               $setup_redirect = $arm_default_setup_url;
                           }
                        }
                        else if($arm_redirection_setup_signup_type == 'url'){
                            $setup_redirect = (isset($redirection_settings['setup_signup']['url']) && !empty($redirection_settings['setup_signup']['url'])) ? $redirection_settings['setup_signup']['url'] : $arm_default_setup_url;
                        }
                        else{
                            $signup_redirection_conditions = (isset($redirection_settings['setup_signup']['conditional_redirect']) && !empty($redirection_settings['setup_signup']['conditional_redirect'])) ? $redirection_settings['setup_signup']['conditional_redirect'] : array();
                            $arm_signup_condition_array = array();
                            if (!empty($signup_redirection_conditions)) {
                                foreach ($signup_redirection_conditions as $signup_conditions_key => $signup_conditions) {
                                    if (is_array($signup_conditions)) {
                                        $arm_signup_condition_array[$signup_conditions_key] = isset($signup_conditions['plan_id']) ? $signup_conditions['plan_id'] : 0;
                                    }
                                }
                            }

                            $arm_intersect_form_ids = array_intersect($arm_signup_condition_array, array($plan_id, -3));

                            if(!empty($arm_intersect_form_ids)){
                                foreach($arm_intersect_form_ids as $arm_signup_condition_key => $arm_signup_condition_val){
                                    $arm_setup_redirection_page_id = isset($signup_redirection_conditions[$arm_signup_condition_key]['url']) ? $signup_redirection_conditions[$arm_signup_condition_key]['url'] : 0;
                                    $setup_redirect = $arm_global_settings->arm_get_permalink('', $arm_setup_redirection_page_id);
                                    if($arm_signup_condition_val == $plan_id){
                                        break;
                                    }
                                }
                            }
                            else{
                                $setup_redirect = $arm_default_setup_url;
                            }
                        }
                    }

                    $arm_plan_details = !empty($plan->plan_detail) ? $plan->plan_detail : '' ;  
                    $arm_modify_setup_urls = array('default_setup_url' => $arm_default_setup_url, 'setup_redirect' => $setup_redirect);
                    $arm_modify_setup_urls = apply_filters('arm_modify_setup_ajax_default_setup_url', $arm_modify_setup_urls, $redirection_settings, $arm_plan_details);
                    if(!empty($arm_modify_setup_urls) && is_array($arm_modify_setup_urls)){
                        if($arm_modify_setup_urls['default_setup_url']){
                            $arm_default_setup_url = $arm_modify_setup_urls['default_setup_url'];
                        }

                        if(!empty($arm_modify_setup_urls['setup_redirect'])){
                            $setup_redirect = $arm_modify_setup_urls['setup_redirect'];
                        }
                    }

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $enable_tax= isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;

                    if ($plan->is_recurring()) {
                        $planData = $plan->prepare_recurring_data($payment_cycle);
                        $amount = !empty($planData['amount']) ? $planData['amount'] : 0;
                    } else {
                        $amount = !empty($plan->amount) ? $plan->amount : 0;
                    }
                    $amount = str_replace(',', '', $amount);
                    $tax_percentage = 0 ;
                    $tax_values = '';
                    if($enable_tax == 1){
                        //$tax_percentage = isset($general_settings['tax_amount']) ? $general_settings['tax_amount'] : 0;
                        $tax_values = $this->arm_get_sales_tax($general_settings, $post_data, $user_id, $form->ID);
                        $tax_percentage = !empty($tax_values["tax_percentage"]) ? $tax_values["tax_percentage"] : '0';
                    }

                    $planOptions = $plan->options;

                    if ($plan_type == 'paid_finite') {
                        $plan_expiry_type = (isset($planOptions['expiry_type']) && $planOptions['expiry_type'] != '') ? $planOptions['expiry_type'] : 'joined_date_expiry';
                        $plan_expiry_date = (isset($planOptions['expiry_date']) && $planOptions['expiry_date'] != '') ? $planOptions['expiry_date'] : date('Y-m-d 23:59:59');
                    }

                    $now = current_time('timestamp');

                    $setup_name = $setup_data['setup_name'];
                    $modules = $setup_data['setup_modules']['modules'];
                    $coupon_as_invitation = isset($setup_data['setup_modules']['modules']['coupon_as_invitation']) ? $setup_data['setup_modules']['modules']['coupon_as_invitation'] : 0 ;

                    if(!is_user_logged_in() && $coupon_as_invitation == 1 && empty($post_data['arm_coupon_code'])){
                        $response['status'] = 'error';
                        $response['type'] = 'message';
                        $response['message'] = '<div class="arm_error_msg">'.__('Please enter the coupon code.', 'ARMember').'</div>';

                        echo json_encode($response);
                        exit();
                    }
                   
                    $module_order = array(
                        'plans' => 1,
                        'forms' => 2,
                        'gateways' => 3,
                        'coupons' => 4,
                    );
                    $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                    /* ====================/.Begin Module section validation./==================== */
                    foreach ($module_order as $module => $order) {
                        if (!empty($modules[$module])) {
                            if ($module == 'forms' && !empty($form_slug)) {
                                $form_id = $form->ID;
                                $arm_form_fields = $form->fields;
                                $field_options = array();

                                foreach ($arm_form_fields as $fields) {
                                    if ($fields['arm_form_field_slug'] == 'user_login') {
                                        $field_options = $fields['arm_form_field_option'];
                                        if (isset($field_options['hide_username']) && $field_options['hide_username'] == 1) {
                                            $post_data['user_login'] = sanitize_email($post_data['user_email']);
                                        }
                                    }
                                }

                                $all_errors = $arm_member_forms->arm_member_validate_meta_details($form, $post_data);

                                if ($all_errors !== TRUE) {
                                    $validate = false;
                                    $validate_msgs += $all_errors;
                                }
                            }
                            if ($module == 'plans') {
                                if ($plan->exists() && $plan->is_active()) {
                                    if ($plan->is_paid() && empty($payment_gateway)) {

                                        if ($plan->is_recurring() && $plan->has_trial_period() && $payment_mode == 'manual_subscription' && $planOptions['trial']['amount'] < 1) {
                                            
                                        } else {

                                            $validate = false;
                                            $err_msg = $arm_global_settings->common_message['arm_no_select_payment_geteway'];
                                            $validate_msgs['subscription_plan'] = (!empty($err_msg)) ? $err_msg : __('Your selected plan is paid, please select payment method.', 'ARMember');
                                        }
                                    }

                                    if ($plan_type == 'paid_finite' && $plan_expiry_type == 'fixed_date_expiry') {
                                        if (strtotime($plan_expiry_date) <= $now) {
                                            $validate = false;
                                            $err_msg = $arm_global_settings->common_message['arm_invalid_plan_select'];
                                            $validate_msgs['subscription_plan'] = (!empty($err_msg)) ? $err_msg : __('Selected plan is not valid.', 'ARMember');
                                        }
                                    }
                                } else {
                                    $validate = false;
                                    $err_msg = $arm_global_settings->common_message['arm_invalid_plan_select'];
                                    $validate_msgs['subscription_plan'] = (!empty($err_msg)) ? $err_msg : __('Selected plan is not valid.', 'ARMember');
                                }
                            }
                            if ($module == 'gateways' && $plan->is_paid() && !empty($payment_gateway)) {
                                $gateway_options = $all_payment_gateways[$payment_gateway];

                                $payment_mode_bt = "";
                                if ($plan->is_recurring()) {
                                    $payment_mode_bt = 'manual_subscription';
                                } else {
                                    $payment_mode_bt = 'auto_debit_subscription';
                                }
                                if ($payment_gateway == 'bank_transfer' && $payment_mode_bt == '') {
                                    $validate = false;
                                    $validate_msgs['bank_transfer'] = __('Selected plan is not valid for bank transfer.', 'ARMember');
                                } else {
                                    $pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $payment_gateway, $gateway_options);
                                    if (in_array($payment_gateway, array('stripe', 'authorize_net')) || $pgHasCCFields) {
                                        $cc_error = array();
                                        if (empty($post_data[$payment_gateway]['card_number'])) {
                                            $err_msg = $arm_global_settings->common_message['arm_blank_credit_card_number'];
                                        }
                                        if (empty($post_data[$payment_gateway]['exp_month'])) {
                                            $err_msg = $arm_global_settings->common_message['arm_blank_expire_month'];
                                        }
                                        if (empty($post_data[$payment_gateway]['exp_year'])) {
                                            $err_msg = $arm_global_settings->common_message['arm_blank_expire_year'];
                                        }
                                        if (empty($post_data[$payment_gateway]['cvc'])) {
                                            $err_msg = $arm_global_settings->common_message['arm_blank_cvc_number'];
                                        }
                                        if (!empty($cc_error)) {
                                            $validate = false;
                                            $validate_msgs['card_number'] = implode('<br/>', $cc_error);
                                        }
                                    }
                                    if ($validate && empty($validate_msgs) && $payment_gateway == 'authorize_net' && !$plan->is_recurring() && $amount > 0) {
                                        $autho_options = $all_payment_gateways['authorize_net'];
                                        $arm_authorise_enable_debug_mode = isset($autho_options['enable_debug_mode']) ? $autho_options['enable_debug_mode'] : 0;
                                        $arm_help_link = '<a href="https://developer.authorize.net/api/reference/features/errorandresponsecodes.html" target="_blank">'.__('Click Here', 'ARMember').'</a>';
                                        global $arm_authorize_net;
                                        $arm_authorize_net->arm_LoadAuthorizeNetLibrary($autho_options);
                                        $auth = new AuthorizeNetAIM;
                                        $card_number = trim($post_data[$payment_gateway]['card_number']);
                                        $exp_month = $post_data[$payment_gateway]['exp_month'];
                                        $exp_year = $post_data[$payment_gateway]['exp_year'];
                                        $cvc = $post_data[$payment_gateway]['cvc'];
                                        
                                        if (is_user_logged_in()) { 
                                          $user_id = get_current_user_id();
                                          $user_info = get_userdata($user_id);
                                          $user_firstname = $user_info->first_name;
                                          $user_lastname = $user_info->last_name;
                                        } else {
                                          $user_firstname = isset($post_data['first_name']) ? trim($post_data['first_name']) : ( isset($post_data['user_email']) ? sanitize_user($post_data['user_email']) : '');
                                          $user_lastname = isset($post_data['last_name']) ? trim($post_data['last_name']) : ( isset($post_data['user_email']) ? sanitize_user($post_data['user_email']) : '');
                                        }

                                        if($tax_percentage > 0){
                                            $tax_amount = ($tax_percentage * $amount)/100;
                                            $tax_amount = number_format((float)$tax_amount, 2, '.', '');
                                            $amount = $amount+$tax_amount;                                           
                                        }

                                        if (strlen(trim($exp_year)) == 4) {
                                            $exp_year = substr(trim($exp_year), 2);
                                        }
                                        try {
                                            $authFields = array(
                                                'amount' => $amount,
                                                'card_num' => $card_number,
                                                'exp_date' => $exp_month . $exp_year,
                                                'card_code' => $cvc,
                                                'first_name' => $user_firstname,
                                                'last_name' => $user_lastname
                                            );
                                            if (!is_null($cvc)) {
                                                $authFields['card_code'] = $cvc;
                                            }
                                            $auth->setFields($authFields);
                                            $auth_response = $auth->authorizeOnly($amount, $card_number, $exp_month . $exp_year);
                                     
                                            if ($auth_response->approved) {
                                                /* Card is valid */
                                                $authorize_net_auth['authorization_code'] = $auth_response->authorization_code;
                                                $authorize_net_auth['transaction_id'] = $auth_response->transaction_id;
                                            } else {
                                                /* Card is Invalid */
                                                $validate = false;
                                                
                                                if(!empty($auth_response->xml->messages->resultCode[0]) && $auth_response->xml->messages->resultCode[0]=='Error')
                                                {
                                                    $actual_error_code = !empty($auth_response->xml->messages->message->code[0]) ? $auth_response->xml->messages->message->code[0] : '' ;
                                                    $actual_error = !empty($auth_response->xml->messages->message->text[0]) ? $auth_response->xml->messages->message->text[0] : '' ;
                                                }
                                                else
                                                {
                                                    $actual_error_code = isset($auth_response->response_reason_code) ? $auth_response->response_reason_code : '';
                                                    $actual_error = isset($auth_response->response_reason_text) ? $auth_response->response_reason_text : '';
                                                }
                                                
                                                $actual_error = !empty($actual_error) ? $actual_error_code.' '.$actual_error.' '.$arm_help_link : '';
                                                $err_msg = $arm_global_settings->common_message['arm_invalid_credit_card'];
                                                $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;
                                                $validate_msgs['card_number'] = (!empty($err_msg)) ? $err_msg : __('Please enter correct card details.', 'ARMember');
                                                $validate_msgs['card_number'] = (!empty($actualmsg)) ? $actualmsg : $validate_msgs['card_number'];
                                            }
                                        } catch (Exception $e) {
                                            $validate = false;

                                            $err_msg = $arm_global_settings->common_message['arm_unauthorized_credit_card'];

                                            $error_msg = $e->getJsonBody();
                                            $ARMember->arm_write_response('reputelog authorize.net response5=> '.maybe_serialize($error_msg));
                                            $actual_error = isset($error_msg['error']['message']) ? $error_msg['error']['message'] : '';
                                            $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                                            
                                            $actualmsg = ($arm_authorise_enable_debug_mode == '1') ? $actual_error : $err_msg;

                                            $validate_msgs['card_number'] = (!empty($err_msg)) ? $err_msg : __('Card details could not be authorized, please use other card detail.', 'ARMember');
                                            $validate_msgs['card_number'] = (!empty($actualmsg)) ? $actualmsg : $validate_msgs['card_number'];
                                        }
                                    }
                                    $pg_errors = apply_filters('arm_validate_payment_gateway_fields', true, $post_data, $payment_gateway, $gateway_options);
                                    if ($pg_errors !== true) {
                                        $validate = false;
                                        $validate_msgs[$payment_gateway] = $pg_errors;
                                    }
                                }
                            }
                            if ($module == 'coupons' && !empty($post_data['arm_coupon_code'])) {
                                if ($arm_manage_coupons->isCouponFeature) {
                                    $post_data['arm_coupon_code'] = trim($post_data['arm_coupon_code']);
                                    $check_coupon = $arm_manage_coupons->arm_apply_coupon_code($post_data['arm_coupon_code'], $plan_id, $setup_id, $payment_cycle, $current_user_plan);
                                    if ($check_coupon['status'] == 'error') {
                                        $validate = false;
                                        $validate_msgs['coupon_code'] = $check_coupon['message'];
                                    } else if ('success' == $check_coupon['status']) { 
                                        if(isset($plan) && $plan->is_free()) {
                                            $arm_manage_coupons->arm_update_coupon_used_count($post_data['arm_coupon_code']);
                                        }
                                    }
                                } else {
                                    $post_data['arm_coupon_code'] = $_REQUEST['arm_coupon_code'] = $_POST['arm_coupon_code'] = '';
                                }
                            }
                        }
                    }
                    /* ====================/.End Module section validation./==================== */
                    if ($validate && empty($validate_msgs)) {
                        do_action('arm_after_setup_form_validate_action', $setup_id, $post_data);
                        $entry_id = 0;
                        $ip_address = $ARMember->arm_get_ip_address();
                        $description = maybe_serialize(array('browser' => $_SERVER['HTTP_USER_AGENT'], 'http_referrer' => @$_SERVER['HTTP_REFERER']));

                        $entry_post_data = $post_data;
                        if (is_user_logged_in()) {
                            $user_information = wp_get_current_user();
                            $user_id_info = $user_information->ID;
                            $username_info = $user_information->user_login;

                            $setup_redirect = str_replace('{ARMCURRENTUSERNAME}', $username_info, $setup_redirect);
                            $setup_redirect = str_replace('{ARMCURRENTUSERID}', $user_id_info, $setup_redirect);
                        }
                        
                        $entry_post_data['setup_redirect'] = $setup_redirect;
                        foreach ($all_payment_gateways as $k => $data) {
                            if (isset($entry_post_data[$k]) && isset($entry_post_data[$k]['card_number'])) {
                                $cc_no = $entry_post_data[$k]['card_number'];
                                unset($entry_post_data[$k]);
                                if (!empty($cc_no)) {
                                    $entry_post_data[$k]['card_number'] = $arm_transaction->arm_mask_credit_card_number($cc_no);
                                }
                            }
                        }
                        
                        $entry_post_data['tax_percentage'] = $tax_percentage;
                        $entry_post_data = apply_filters('arm_modify_submission_entry_post_data', $entry_post_data, $plan);
                        $entry_post_data = apply_filters('arm_add_arm_entries_value', $entry_post_data);
                        $new_entry = array(
                            'arm_entry_email' => $entry_email,
                            'arm_name' => $setup_name,
                            'arm_description' => $description,
                            'arm_ip_address' => $ip_address,
                            'arm_browser_info' => $_SERVER['HTTP_USER_AGENT'],
                            'arm_entry_value' => maybe_serialize($entry_post_data),
                            'arm_form_id' => $form_id,
                            'arm_user_id' => $user_id,
                            'arm_plan_id' => $plan_id,
                            'arm_created_date' => date('Y-m-d H:i:s')
                        );
                        $arm_is_paid_post = false;
                        if( isset( $post_data['arm_paid_post'] ) && '' != $post_data['arm_paid_post'] ){
                            $arm_paid_post_id = $post_data['arm_paid_post'];

                            $get_paid_post_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) as total_pp_plan FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d AND `arm_subscription_plan_id` = %d", $post_data['arm_paid_post'], $plan_id  ) );

                            if( $get_paid_post_count > 0 ){
                                $new_entry['arm_is_post_entry'] = 1;
                                $new_entry['arm_paid_post_id'] = $arm_paid_post_id;
                                $arm_is_paid_post = true;
                
                                $arm_setup_redirect_url = get_permalink($arm_paid_post_id);

                                $setup_redirect = $arm_setup_redirect_url;
                                $arm_redirection_setup_paid_post_type = (isset($redirection_settings['setup_paid_post']['type']) && !empty($redirection_settings['setup_paid_post']['type'])) ? $redirection_settings['setup_paid_post']['type'] : '0';
                                
                                if($arm_redirection_setup_paid_post_type == '1'){
                                   $arm_redirection_setup_paid_post_page_id = (isset($redirection_settings['setup_paid_post']['page_id']) && !empty($redirection_settings['setup_paid_post']['page_id'])) ? $redirection_settings['setup_paid_post']['page_id'] : 0;
                                   
                                   if(!empty($arm_redirection_setup_paid_post_page_id)){
                                       $setup_redirect = $arm_global_settings->arm_get_permalink('', $arm_redirection_setup_paid_post_page_id);
                                   }
                                }
                                $entry_post_data['setup_redirect'] = $setup_redirect;
                                $new_entry['arm_entry_value'] = maybe_serialize($entry_post_data);
                            }

                        }

                        $arm_is_gift = (!empty($plan->isGiftPlan) && ($plan->isGiftPlan == 1)) ? 1 : 0;
                        
                        $new_entry = apply_filters('arm_modify_entry_data_before_submit', $new_entry);
			
                        $new_entry_results = $wpdb->insert($ARMember->tbl_arm_entries, $new_entry);
                        $entry_id = $wpdb->insert_id;

                        if (!empty($entry_id) && $entry_id != 0) {
                            $post_data['arm_entry_id'] = $entry_id;
                            $payment_gateway_options = isset($all_payment_gateways[$payment_gateway]) ? $all_payment_gateways[$payment_gateway] : array();
                            if (is_user_logged_in()) {
                                if (!empty($modules['plans'])) {

                                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                    $post_data['old_plan_id'] = (isset($current_user_plan) && !empty($current_user_plan)) ? implode(",", $current_user_plan) : 0;
                                    $old_plan_id = isset($current_user_plan[0]) ? $current_user_plan[0] : 0;
                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                    $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                    $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                    $oldPlanDetail = isset($oldPlanData['arm_current_plan_detail']) ? $oldPlanData['arm_current_plan_detail'] : array();
                                    if (!empty($oldPlanDetail)) {
                                        $old_plan = new ARM_Plan(0);
                                        $old_plan->init((object) $oldPlanDetail);
                                    } else {
                                        $old_plan = new ARM_Plan($old_plan_id);
                                    }

                                    $is_update_plan = true;
                                    
                                    $now = current_time('mysql');
                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $plan_id, $now)); 
                                    /* If plan is being renewd */
                                    if (in_array($plan_id, $current_user_plan)) {

                                        /* if plan is recurring and old payment mode is auto debit, then if payment is done using 2checkout, old plan need to be canceled and plan renew date will be today date
                                         * In other payment gateway, plan renew date will be old ecpiry date
                                         */

                                        if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post) {
                                            if ($old_plan->is_recurring()) {
                                                if ($payment_mode == 'auto_debit_subscription') {
                                                    $need_to_cancel_payment_gateway_array = $arm_payment_gateways->arm_need_to_cancel_old_subscription_gateways();
                                                    $need_to_cancel_payment_gateway_array = !empty($need_to_cancel_payment_gateway_array) ? $need_to_cancel_payment_gateway_array : array();
                                                    if (in_array($payment_gateway, $need_to_cancel_payment_gateway_array)) {
                                                        do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                                    }   
                                                }
                                            }
                                        } else {
                                            if ($plan->is_recurring()) {
                                                if ($payment_mode == 'auto_debit_subscription') {
                                                    $need_to_cancel_payment_gateway_array = $arm_payment_gateways->arm_need_to_cancel_old_subscription_gateways();
                                                    $need_to_cancel_payment_gateway_array = !empty($need_to_cancel_payment_gateway_array) ? $need_to_cancel_payment_gateway_array : array();
                                                    if (in_array($payment_gateway, $need_to_cancel_payment_gateway_array)) {
                                                        do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                      
                                        /* if plan is being changed. */
                                        /* check if upgrade downgrade action is applied
                                         * if it is immmediately then, cancel old subscription if plan is recurring immediately */

                                        if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_paid_post && !$arm_is_gift) {
                                            if ($old_plan->exists()) {
                                                if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $plan->is_recurring())) {
                                                    $is_update_plan = true;
                                                } else {
                                                    $change_act = 'immediate';
                                                    if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                        if (!empty($old_plan->downgrade_plans) && in_array($plan->ID, $old_plan->downgrade_plans)) {
                                                            $change_act = $old_plan->downgrade_action;
                                                        }
                                                        if (!empty($old_plan->upgrade_plans) && in_array($plan->ID, $old_plan->upgrade_plans)) {
                                                            $change_act = $old_plan->upgrade_action;
                                                        }
                                                    }
                                                    $subscr_effective = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : '';
                                                    //$subscr_effective = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $oldPlanData['arm_next_due_payment'];
                                                    if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                        $is_update_plan = false;
                                                        $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                        $oldPlanData['arm_change_plan_to'] = $plan_id;
                                                        update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                                    }
                                                }
                                                if ($is_update_plan && $old_plan->is_recurring()) {
                                                  
                                                    do_action('arm_cancel_subscription_gateway_action', $user_id, $old_plan_id);
                                                }
                                            }
                                        }
                                    }

                                    if (!$plan->is_free()) {
                                        if (!empty($payment_gateway_options)) {
                                            if ($payment_gateway == 'bank_transfer') {
                                                $arm_bank_transfer_do_not_allow_pending_transaction = isset($payment_gateway_options['arm_bank_transfer_do_not_allow_pending_transaction']) ? $payment_gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] : 0;
                                                $arm_debug_log_data = array(
                                                    'do_not_allow_pending_transaction' => $arm_bank_transfer_do_not_allow_pending_transaction,
                                                    'is_recurring' => $plan->is_recurring(),
                                                    'user_id' => $user_id,
                                                    'plan_id' => $plan_id,
                                                    'entry_id' => $entry_id,
                                                    'post_data' => $post_data,
                                                );
                                                do_action('arm_payment_log_entry', 'bank_transfer', 'Submit form details', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
                                                $payment_mode_bt = '';
                                                if ($plan->is_recurring()) {
                                                    $payment_mode_bt = "manual_subscription";
                                                }
                                                
                                                $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array(); 
                                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;
                                          
                                                update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $userPlanData);    
                                                update_user_meta($user_id, 'arm_entry_id', $entry_id);

                                                if (!$plan->is_recurring() || $payment_mode_bt == 'manual_subscription') {
                                                    
                                                    if($arm_bank_transfer_do_not_allow_pending_transaction == '1')
                                                    {
                                                        $arm_last_bank_transfer_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND arm_payment_gateway=%s AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $plan_id,'bank_transfer', $now));
                                                        do_action('arm_payment_log_entry', 'bank_transfer', 'Last bank transfer payment status', 'armember', $arm_last_bank_transfer_payment_status, $arm_debug_payment_log_id);
                                                        if(isset($arm_last_bank_transfer_payment_status) && $arm_last_bank_transfer_payment_status==0)
                                                        {
                                                            $arm_bank_transfer_err_msg = $arm_global_settings->common_message['arm_do_not_allow_pending_payment_bank_transfer'];

                                                            $validate_msgs['payment_failed'] = (!empty($arm_bank_transfer_err_msg)) ? $arm_bank_transfer_err_msg : __('Sorry! You have already one pending payment transaction. You will be able to proceed after that transaction will be approved.', 'ARMember');
                                                        }
                                                        else
                                                        {
                                                            $arm_payment_gateways->arm_bank_transfer_payment_gateway_action($payment_gateway, $payment_gateway_options, $post_data, $entry_id);
                                                            global $payment_done;
                                                            
                                                            $response['status'] = 'success';
                                                            $response['type'] = 'redirect';
                                                            
                                                           
                                                            $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $arm_payment_gateways->arm_bank_transfer_payment_gateway_action($payment_gateway, $payment_gateway_options, $post_data, $entry_id);
                                                        global $payment_done;
                                                        
                                                        $response['status'] = 'success';
                                                        $response['type'] = 'redirect';
                                                        
                                                       
                                                        $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                                    }
                                                    
                                                } else {
                                                    $validate_msgs['payment_failed'] = __('Selected plan is not valid for bank transfer.', 'ARMember');
                                                }
                                            } else {
                                                $post_data = apply_filters('arm_change_posted_data_before_payment_outside', $post_data, $payment_gateway, $payment_gateway_options, $entry_id);

                                                $arm_plan_id = (!empty($post_data['subscription_plan'])) ? $post_data['subscription_plan'] : 0;
                                                if ($arm_plan_id == 0) {
                                                    $arm_plan_id = (!empty($post_data['_subscription_plan'])) ? $post_data['_subscription_plan'] : 0;
                                                }

                                                $trial_not_allowed = 0;
                                                $trial_not_allowed = apply_filters('arm_payment_gateway_trial_allowed', $trial_not_allowed, $arm_plan_id, $payment_gateway, $payment_gateway_options, $post_data);

                                                if($trial_not_allowed){
                                                    $err_msg = esc_html__('The Trial Period you have selected for this plan is not supported with', 'ARMember')." ".$payment_gateway;
                                                    $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                                }
                                                else
                                                {                                               
                                                    do_action('arm_payment_gateway_validation_from_setup', $payment_gateway, $payment_gateway_options, $post_data, $entry_id);
                                                }

                                                global $payment_done;
                                                if (isset($payment_done['status']) && $payment_done['status'] === FALSE) {
                                                    $validate_msgs['payment_failed'] = $payment_done['error'];
                                                } else {
                                                    
                                                    
                                                    $pgs_arrays = apply_filters('arm_update_new_subscr_gateway_outside', array('stripe', 'authorize_net', '2checkout'));
                                                    $log_id = $payment_done['log_id'];
                                                    $log_detail = $wpdb->get_row("SELECT `arm_log_id`, `arm_user_id`, `arm_token`, `arm_transaction_id`, `arm_extra_vars` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_log_id`='$log_id'");
                                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);

                                                    $userPlanData['arm_user_gateway'] = $payment_gateway;
                                                    $arm_user_old_plan_details = (isset($userPlanData['arm_current_plan_detail']) && !empty($userPlanData['arm_current_plan_detail'])) ? $userPlanData['arm_current_plan_detail'] : array(); 
                                                    $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
                                                    $userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;
                                                    
                                                    if ($plan->is_recurring()) {
                                                        $userPlanData['arm_payment_mode'] = $payment_mode;
                                                        $userPlanData['arm_payment_cycle'] = $payment_cycle;
                                                    } else {
                                                        $userPlanData['arm_payment_mode'] = '';
                                                        $userPlanData['arm_payment_cycle'] = '';
                                                    }

                                                    if ($payment_gateway == 'authorize_net') {
                                                        $pg_subsc_data = array('subscription_id' => $log_detail->arm_token);
                                                        $userPlanData['arm_authorize_net'] = $pg_subsc_data;
                                                        $userPlanData['arm_stripe'] = '';
                                                        $userPlanData['arm_2checkout'] = '';
                                                    } elseif ($payment_gateway == 'stripe') {
                                                        $pg_subsc_data = array('customer_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                                                        $arm_extra_vars_data = !empty($log_detail->arm_extra_vars) ? maybe_unserialize($log_detail->arm_extra_vars) : '';
                                                        if(!empty($arm_extra_vars_data) && !empty($arm_extra_vars_data['arm_stripe_customer_id']))
                                                        {
                                                            $pg_subsc_data['customer_id'] = $arm_extra_vars_data['arm_stripe_customer_id'];
                                                            $pg_subsc_data['transaction_id'] = $log_detail->arm_token;
                                                        }
                                                        $userPlanData['arm_stripe'] = $pg_subsc_data;
                                                        $userPlanData['arm_authorize_net'] = '';
                                                        $userPlanData['arm_2checkout'] = '';
                                                    } elseif ($payment_gateway == '2checkout') {
                                                        $pg_subsc_data = array('sale_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                                                        $userPlanData['arm_2checkout'] = $pg_subsc_data;
                                                        $userPlanData['arm_authorize_net'] = '';
                                                        $userPlanData['arm_stripe'] = '';
                                                    }
                                                    update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $userPlanData);
                                                    do_action('arm_update_user_meta_after_renew_outside', $user_id, $log_detail, $plan_id, $payment_gateway);

                                                    if ($is_update_plan) {
                                                        
                                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $plan_id, '', true, $arm_last_payment_status);
                                                    } else {
                                                        $arm_subscription_plans->arm_add_membership_history($user_id, $plan_id, 'change_subscription');
                                                    }

                                                    if($plan->is_recurring())
                                                    {
                                                        $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $log_detail, $payment_gateway, $userPlanData);
                                                    }
                                                    $response['status'] = 'success';
                                                    $response['type'] = 'redirect';
                                                    $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                                }
                                            }
                                        } else {
                                            $err_msg = $arm_global_settings->common_message['arm_inactive_payment_gateway'];
                                            $validate_msgs['payment_gateway'] = (!empty($err_msg)) ? $err_msg : __('Payment gateway is not active, please contact site administrator.', 'ARMember');
                                            $payment_done = array('status' => FALSE);
                                        }
                                    } else {
                                        if ($is_update_plan) {
                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $plan_id);
                                        } else {
                                            $arm_subscription_plans->arm_add_membership_history($user_id, $plan_id, 'change_subscription');
                                        }
                                        $log_data=array('arm_transaction_status'=>'success','arm_user_id'=>$user_id,'arm_plan_id'=>$plan_id);
                                        do_action('arm_after_add_free_plan_transaction', $log_data);
                                        $response['status'] = 'success';
                                        $response['type'] = 'redirect';
                                        $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                    }
                                }
                            } else {
                                if (!empty($modules['plans']) && $plan->is_paid()) {
                                    if (!empty($payment_gateway_options)) {
                                        if ($payment_gateway == 'bank_transfer') {
                                            $payment_mode_bt = "manual_subscription";
                                            if ($plan->is_recurring()) {
                                                $payment_mode_bt = "manual_subscription";
                                            }
                                            if (!$plan->is_recurring() || $payment_mode == 'manual_subscription') {
                                                $arm_payment_gateways->arm_bank_transfer_payment_gateway_action($payment_gateway, $payment_gateway_options, $post_data, $entry_id);
                                                global $payment_done;
                                                $payment_log_id = '';
                                                if($payment_done['status']==1)
                                                {
                                                    $payment_log_id = $payment_done['log_id'];
                                                }
                                                
                                                $response['status'] = 'success';
                                                $response['type'] = 'redirect';
                                                $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                            } else {
                                                $validate_msgs['payment_failed'] = __('Selected plan is not valid for bank transfer.', 'ARMember');
                                            }
                                        } else {
                                            $post_data = apply_filters('arm_change_posted_data_before_payment_outside', $post_data, $payment_gateway, $payment_gateway_options, $entry_id);

                                            $arm_extra_vars['entry_id'] = $entry_id;
                                            
                                            $arm_plan_id = (!empty($post_data['subscription_plan'])) ? $post_data['subscription_plan'] : 0;
                                            if ($arm_plan_id == 0) {
                                                $arm_plan_id = (!empty($post_data['_subscription_plan'])) ? $post_data['_subscription_plan'] : 0;
                                            }

                                            //Check trial supported or not at payment gateway side.
                                            $trial_not_allowed = 0;
                                            $trial_not_allowed = apply_filters('arm_payment_gateway_trial_allowed', $trial_not_allowed, $arm_plan_id, $payment_gateway, $payment_gateway_options, $post_data);

                                            if($trial_not_allowed){
                                                $err_msg = esc_html__('The Trial Period you have selected for this plan is not supported with', 'ARMember').' '. $payment_gateway;
                                                $payment_done = array('status' => FALSE, 'error' => $err_msg);
                                            }
                                            else
                                            {
                                                do_action('arm_payment_gateway_validation_from_setup', $payment_gateway, $payment_gateway_options, $post_data, $entry_id);
                                            }



                                            global $payment_done;
                                            if (isset($payment_done['status']) && $payment_done['status'] === FALSE) {
                                                $validate_msgs['payment_failed'] = $payment_done['error'];
                                            }
                                        }
                                    } else {
                                        if ($plan->is_recurring() && $plan->has_trial_period() && $payment_mode == 'manual_subscription' && $planOptions['trial']['amount'] == 0) {
                                            $payment_data = array(
                                                'arm_user_id' => '0',
                                                'arm_first_name'=>(isset($post_data['first_name'])) ? $post_data['first_name'] : '',
                                                'arm_last_name'=>(isset($post_data['last_name'])) ? $post_data['last_name'] : '',
                                                'arm_plan_id' => (!empty($plan_id) ? $plan_id : 0),
                                                'arm_payment_gateway' => 'paypal',
                                                'arm_payment_type' => $plan->payment_type,
                                                'arm_token' => '-',
                                                'arm_payer_email' => (isset($post_data['user_email'])) ? sanitize_email($post_data['user_email']) : '',
                                                'arm_receiver_email' => '',
                                                'arm_transaction_id' => '-',
                                                'arm_transaction_payment_type' => $plan->payment_type,
                                                'arm_transaction_status' => 'completed',
                                                'arm_payment_mode' => $payment_mode,
                                                'arm_payment_date' => date('Y-m-d H:i:s'),
                                                'arm_amount' => 0,
                                                'arm_currency' => 'USD',
                                                'arm_coupon_code' => '',
                                                'arm_extra_vars' => '',
                                                'arm_created_date' => current_time('mysql')
                                            );
                                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);
                                            $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                                        } else {
                                            $err_msg = $arm_global_settings->common_message['arm_inactive_payment_gateway'];
                                            $validate_msgs['payment_gateway'] = (!empty($err_msg)) ? $err_msg : __('Payment gateway is not active, please contact site administrator.', 'ARMember');
                                            $payment_done = array('status' => FALSE);
                                        }
                                    }
                                } else {
                                    $payment_done = array('status' => TRUE);
                                }

                                if (!empty($modules['forms']) && $payment_done['status'] == TRUE) {
                                    if (in_array($form->type, array('registration'))) {
                                        $post_data['arm_update_user_from_profile'] = 0;
                                        $user_id = $arm_member_forms->arm_register_new_member($post_data, $form);
                                        if (is_numeric($user_id) && !is_array($user_id)) {

                                            if($plan->is_free()){
                                                $log_data=array('arm_transaction_status'=>'success','arm_user_id'=>$user_id,'arm_plan_id'=>$plan_id);
                                                do_action('arm_after_add_free_plan_transaction', $log_data);
                                            }

                                            if(!empty($payment_log_id))
                                            {
                                                $armLogTable = $ARMember->tbl_arm_payment_log;
                                                $chk_log_detail = $wpdb->get_row("SELECT `arm_log_id`, `arm_amount` FROM `{$armLogTable}` WHERE `arm_log_id`='{$payment_log_id}'");
                                                if (!empty($chk_log_detail)) {
                                                    $user_register_verification = isset($arm_global_settings->global_settings['user_register_verification']) ? $arm_global_settings->global_settings['user_register_verification'] : 'auto';
                                                    if($chk_log_detail->arm_amount==0 && $user_register_verification == 'auto')
                                                    {
                                                        $arm_transaction->arm_change_bank_transfer_status($payment_log_id, '1', 0);
                                                    }
                                                }
                                            }
                                            
                                            if ($module == 'coupons' && !empty($post_data['arm_coupon_code'])) {
                                                if ($arm_manage_coupons->isCouponFeature) {
                                                    $post_data['arm_coupon_code'] = trim($post_data['arm_coupon_code']);
                                                    $check_coupon = $arm_manage_coupons->arm_apply_coupon_code($post_data['arm_coupon_code'], $plan_id, $setup_id, $payment_cycle, $current_user_plan);
                                                    if ('success' == $check_coupon['status']) { 
                                                        if( 1 == $coupon_as_invitation ) {
                                                            $arm_manage_coupons->arm_save_coupon_in_usermeta($user_id, $post_data['arm_coupon_code'], $plan_id);
                                                            
                                                        }
                                                    }
                                                }
                                            }

                                            if(isset($payment_done["coupon_on_each"]) && $payment_done["coupon_on_each"] == TRUE && isset($payment_done["trans_log_id"]) && $payment_done["trans_log_id"] != 0)
                                            {
                                                $log_detail = $wpdb->get_row("SELECT `arm_log_id`, `arm_user_id`, `arm_token`, `arm_transaction_id`, `arm_extra_vars` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_log_id`='".$payment_done["trans_log_id"]."'");
                                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                                $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $log_detail, $payment_gateway, $planData);
                                            }
                                            else if(isset($payment_done["log_id"]) && !empty($payment_done["log_id"]))
                                            {
                                                $log_detail = $wpdb->get_row("SELECT `arm_log_id`, `arm_user_id`, `arm_token`, `arm_transaction_id`, `arm_extra_vars` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_log_id`='".$payment_done["log_id"]."'");
                                                $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);

                                                $arm_extra_vars_data = maybe_unserialize($log_detail->arm_extra_vars);

                                                if(!empty($planData['arm_stripe']) && !empty($planData['arm_stripe']['customer_id']) && !empty($planData['arm_stripe']['transaction_id']))
                                                {
                                                    //Update User Meta for Stripe
                                                    $arm_stripe_customer_id = !empty($arm_extra_vars_data['arm_stripe_customer_id']) ? $arm_extra_vars_data['arm_stripe_customer_id'] : '';

                                                    $planData['arm_stripe']['customer_id'] = $arm_stripe_customer_id;
                                                    $planData['arm_stripe']['transaction_id'] = $log_detail->arm_token;
                                                    update_user_meta($user_id, 'arm_user_plan_'.$plan_id, $planData);
                                                }


                                                $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $log_detail, $payment_gateway, $planData);
                                            }
                                            $response['status'] = 'success';
                                            $response['type'] = 'redirect';

                                            $user_info = get_userdata($user_id);
                                            $username = $user_info->user_login;

                                            $setup_redirect = str_replace('{ARMCURRENTUSERNAME}', $username, $setup_redirect);
                                            $setup_redirect = str_replace('{ARMCURRENTUSERID}', $user_id, $setup_redirect);

                                            $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
                                        } else {
                                            $validate_msgs['register_error'] = $arm_errors->get_error_messages('arm_reg_error');
                                        }
                                    }
                                }
                            }
                        } else {
                            $err_msg = $arm_global_settings->common_message['arm_general_msg'];
                            $validate_msgs['entry_message'] = (!empty($err_msg)) ? $err_msg : __('Sorry, Something went wrong. Please contact to site administrator.', 'ARMember');
                        }
                    }

                    if (!empty($validate_msgs)) {
                        $response['status'] = 'error';
                        $response['type'] = 'message';
                        $response['message'] = '<div class="arm_error_msg"><ul>';
                        foreach ($validate_msgs as $err) {
                            if (is_array($err)) {
                                foreach ($err as $key => $err_msg) {
                                    $response['message'] .= '<li>' . $err_msg . '</li>';
                                }
                            } else {
                                $response['message'] .= '<li>' . $err . '</li>';
                            }
                        }
                        $response['message'] .= '</ul></div>';
                    } else {

                        $response['status'] = 'success';
                        if (isset($response['type']) && $response['type'] == 'redirect') {
                            $response['message'] = $response['message'];
                        } else {
                            $response['type'] = 'message';
                            $response['message'] = '<div class="arm_success_msg"><ul><li>' . $response['message'] . '</li></ul></div>';
                        }
                    }
                }
                do_action('arm_after_setup_form_action', $setup_id, $post_data);
                $response = apply_filters('arm_after_setup_form_action', $response, $setup_id, $post_data);
            }

            $arm_return_script = '';
            $response['script'] = apply_filters('arm_after_setup_submit_sucess_outside',$arm_return_script);
            if ($post_data['action'] == 'arm_membership_setup_form_ajax_action') {
                if($arm_pay_per_post_feature->isPayPerPostFeature && !empty($_POST['arm_paid_post_success_url']) && $response['status'] == "success")
                {
                    $response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="'.$_POST['arm_paid_post_success_url'].'"</script>';
                }

                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        }

        function arm_setup_shortcode_func_internal($atts, $content = "") {
            global $wp, $wpdb, $current_user, $ARMember, $arm_member_forms, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans, $bpopup_loaded, $ARMSPAMFILEURL,$arm_pay_per_post_feature;
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $defaults = array(
                'id' => 0, /* Membership Setup Wizard ID */
                'hide_title' => false,
                'class' => '',
                'popup' => false, /* Form will be open in popup box when options is true */
                'link_type' => 'link',
                'link_class' => '', /* /* Possible Options:- `link`, `button` */
                'link_title' => __('Click here to open Set up form', 'ARMember'), /* Default to form name */
                'popup_height' => '',
                'popup_width' => '',
                'overlay' => '0.6',
                'modal_bgcolor' => '#000000',
                'redirect_to' => '',
                'link_css' => '',
                'link_hover_css' => '',
                'is_referer' => '0',
                'preview' => false,
                'setup_data' => '',
                'subscription_plan' => 0,
                'hide_plans' => 0,
                'is_arm_paid_post' => 0,
                'paid_post_id' => '',
                'your_current_membership_text' =>__('Your Current Membership', 'ARMember'),
            );

            $ARMember->arm_session_start();
            /* Extract Shortcode Attributes */
            $args = shortcode_atts($defaults, $atts, 'arm_setup');
            extract($args);
            $args['hide_title'] = ($args['hide_title'] === 'true' || $args['hide_title'] == '1') ? true : false;
            $args['popup'] = ($args['popup'] === 'true' || $args['popup'] == '1') ? true : false;
            $isPreview = ($args['preview'] === 'true' || $args['preview'] == '1') ? true : false;
            if ($args['popup']) {
                $bpopup_loaded = 1;
            }
            $completed_recurrence = '';
            $total_recurring = '';
            if ((!empty($args['id']) && $args['id'] != 0) || ($isPreview && !empty($args['setup_data']))) {

                $setupID = $args['id'];
                if ($isPreview && !empty($args['setup_data'])) {
                    $setup_data = maybe_unserialize($args['setup_data']);
                    $setup_data['arm_setup_labels'] = $setup_data['setup_labels'];
                } else {
                    $setup_data = $this->arm_get_membership_setup($setupID);
                }
                $setup_data = apply_filters('arm_setup_data_before_setup_shortcode', $setup_data, $args);
                do_action('arm_before_render_membership_setup_form', $setup_data, $args);

                if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {

                    $setupRandomID = $setupID . '_' . arm_generate_random_code();
                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                    $current_user_id = get_current_user_id();
                    $current_user_plan_ids = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                    $current_user_plan_ids = !empty($current_user_plan_ids) ? $current_user_plan_ids : array();

                    $user_posts = get_user_meta($current_user_id, 'arm_user_post_ids', true);
                    $user_posts = !empty($user_posts) ? $user_posts : array();    

                    if(!empty($current_user_plan_ids) && !empty($user_posts))
                    {
                        foreach ($current_user_plan_ids as $user_plans_key => $user_plans_val) {
                            if(!empty($user_posts)){
                                foreach ($user_posts as $user_post_key => $user_post_val) {
                                    if($user_post_key==$user_plans_val){
                                        unset($current_user_plan_ids[$user_plans_key]);
                                    }
                                }
                            }
                        }
                    }

                    $current_user_plan_ids = apply_filters('arm_modify_plan_ids_externally', $current_user_plan_ids, $current_user_id);

                    $current_user_plan = '';
                    $current_plan_data = array();
                    if (!empty($current_user_plan_ids)) {
                        $current_user_plan = current($current_user_plan_ids);
                        $current_plan_data = get_user_meta($current_user_id, 'arm_user_plan_' . $current_user_plan, true);
                    }
                    $setup_name = (!empty($setup_data['setup_name'])) ? stripslashes($setup_data['setup_name']) : '';
                    $button_labels = $setup_data['setup_labels']['button_labels'];
                    $submit_btn = (!empty($button_labels['submit'])) ? $button_labels['submit'] : __('Submit', 'ARMember');
                    $setup_modules = $setup_data['setup_modules'];
                    $user_selected_plan = isset($setup_modules['selected_plan']) ? $setup_modules['selected_plan'] : "";
                    $modules = $setup_modules['modules'];
                    $setup_style = isset($setup_modules['style']) ? $setup_modules['style'] : array();
                    $formPosition = (isset($setup_style['form_position']) && !empty($setup_style['form_position'])) ? $setup_style['form_position'] : 'left';
                    $plan_selection_area = (isset($setup_style['plan_area_position']) && !empty($setup_style['plan_area_position'])) ? $setup_style['plan_area_position'] : 'before';
                    

                    $fieldPosition = 'left';
                    $custom_css = isset($setup_modules['custom_css']) ? $setup_modules['custom_css'] : '';
                    $modules['step'] = (!empty($modules['step'])) ? $modules['step'] : array(-1);

                    if ($plan_selection_area == 'before') {
                        $module_order = array(
                            'plans' => 1,
                            'forms' => 2,
                            'note' => 3,
                            'payment_cycle' => 4,
                            'gateways' => 5,
                            'order_detail' => 6,
                        );
                    } else {
                        $module_order = array(
                            'forms' => 1,
                            'plans' => 2,
                            'note' => 3,
                            'payment_cycle' => 4,
                            'gateways' => 5,
                            'order_detail' => 6,
                        );
                    }
                    $modules['forms'] = (!empty($modules['forms']) && $modules['forms'] != 0) ? $modules['forms'] : 0;
                    $step_one_modules = $step_two_modules = '';
                    /* Check `GET` or `POST` Data */
                    /* first check if user have selected any plan than select that plan otherwise set value from options of setup */
                    if ($current_user_plan != '') {
                        $selected_plan_id = $current_user_plan;
                    } else {
                        $selected_plan_id = $user_selected_plan;
                    }
                    if (!empty($_REQUEST['subscription_plan']) && $_REQUEST['subscription_plan'] != 0) {
                        $selected_plan_id = intval($_REQUEST['subscription_plan']);
                    }
                    if (!empty($args['subscription_plan']) && $args['subscription_plan'] != 0) {
                        $selected_plan_id = $args['subscription_plan'];
                    }
                    $isHidePlans = false;
                    if (!empty($selected_plan_id) && $selected_plan_id != 0) {
                        if (!empty($_REQUEST['hide_plans']) && $_REQUEST['hide_plans'] == 1) {
                            $isHidePlans = true;
                        }
                        if (!empty($args['hide_plans']) && $args['hide_plans'] == 1) {
                            $isHidePlans = true;
                        }
                    }

                    $is_hide_plan_selection_area = false;
                    if (isset($setup_style['hide_plans']) && $setup_style['hide_plans'] == 1) {
                        $is_hide_plan_selection_area = true;
                    }

                    if (is_user_logged_in()) {
                        global $current_user;
                        if (!empty($current_user->data->arm_primary_status)) {
                            $current_user_status = $current_user->data->arm_primary_status;
                        } else {
                            $current_user_status = arm_get_member_status($current_user_id);
                        }
                    }
                    $selected_plan_data = array();
                    $module_html = $formStyle = $setupGoogleFonts = '';
                    $errPosCCField = 'right';
                    if (is_rtl()) {
                        $is_form_class_rtl = 'arm_form_rtl';
                    } else {
                        $is_form_class_rtl = 'arm_form_ltr';
                    }
                    $form_style_class = ' arm_shortcode_form arm_form_0 arm_form_layout_writer armf_label_placeholder armf_alignment_left armf_layout_block armf_button_position_left ' . $is_form_class_rtl;
                    $btn_style_class = ' arm_btn_style_flat ';
                    if (!empty($modules['forms'])) {
                        $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $modules['forms'] . "'");
                        $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
                    }
                    $plan_payment_cycles = array();
                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $enable_tax= isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;
                    $tax_percentage = 0;
                    if($enable_tax == 1) {
                        $post_data = isset($_POST) ? $_POST : array();
                        $tax_values = $this->arm_get_sales_tax($general_settings, $post_data, $current_user_id, $modules['forms']);
                        $tax_percentage = !empty($tax_values["tax_percentage"]) ? $tax_values["tax_percentage"] : '0';
                    }

                    foreach ($module_order as $module => $order) {
                        $module_content = '';
                        $arm_user_id = 0;
                        $arm_user_old_plan = 0;
                        $plan_id_array = array();
                        $arm_user_selected_payment_mode = 0;
                        $arm_user_selected_payment_cycle = 0;
                        $arm_last_payment_status = 'success';
                        switch ($module) {
                            case 'plans':
                                    if (is_user_logged_in()) {
                                      global $current_user;
                                        $arm_user_id = $current_user->ID;

                                        $user_firstname = $current_user->user_firstname;
                                        $user_lastname = $current_user->user_lastname;
                                        $user_email = $current_user->user_email;
                                        if($user_firstname != '' && $user_lastname != ''){
                                          $arm_user_firstname_lastname = $user_firstname. ' '.$user_lastname;
                                        }
                                        else{
                                          $arm_user_firstname_lastname = $user_email;
                                        }

                                        if (!empty($current_user_plan_ids)) {
                                            $plan_name_array = array();
                                            foreach ($current_user_plan_ids as $plan_id) {
                                                $planData = get_user_meta($arm_user_id, 'arm_user_plan_' . $plan_id, true);
                                                $arm_user_selected_payment_mode = $planData['arm_payment_mode'];
                                                $arm_user_current_plan_detail = $planData['arm_current_plan_detail'];
                                                $plan_name_array[] = isset($arm_user_current_plan_detail['arm_subscription_plan_name']) ? stripslashes($arm_user_current_plan_detail['arm_subscription_plan_name']) : '';
                                                $plan_id_array[] = $plan_id;

                                                $curPlanDetail = $planData['arm_current_plan_detail'];
                                                $completed_recurrence = $planData['arm_completed_recurring'];
                                                if (!empty($curPlanDetail)) {
                                                    $arm_user_old_plan_info = new ARM_Plan(0);
                                                    $arm_user_old_plan_info->init((object) $curPlanDetail);
                                                } else {
                                                    $arm_user_old_plan_info = new ARM_Plan($arm_user_old_plan);
                                                }
                                                $total_recurring = '';
                                                $arm_user_old_plan_options = $arm_user_old_plan_info->options;
                                                if ($arm_user_old_plan_info->is_recurring()) {
                                                    $arm_user_selected_payment_cycle = $planData['arm_payment_cycle'];
                                                    $arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data($arm_user_selected_payment_cycle);
                                                    $total_recurring = $arm_user_old_plan_data['rec_time'];

                                                    $now = current_time('mysql');
                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $arm_user_id, $plan_id, $now)); 
                                                }



                                                $module_content .= '<input type="hidden" data-id="arm_user_firstname_lastname" value="' . $arm_user_firstname_lastname . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_last_payment_status_' . $plan_id . '" value="' . $arm_last_payment_status . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_done_payment_' . $plan_id . '" value="' . $completed_recurrence . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_old_plan_total_cycle_' . $plan_id . '" value="' . $total_recurring . '">';

                                                $module_content .= '<input type="hidden" data-id="arm_user_selected_payment_cycle_' . $plan_id . '" value="' . $arm_user_selected_payment_cycle . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_selected_payment_mode_' . $plan_id . '" value="' . $arm_user_selected_payment_mode . '">';
                                            }
                                        }
                                        $arm_is_user_logged_in_flag = 1;
                                    } else {
                                        $arm_is_user_logged_in_flag = 0;
                                    }

                                    if (!empty($plan_id_array)) {
                                        $arm_user_old_plan = implode(",", $plan_id_array);
                                    }

                                    $module_content .= '<input type="hidden" data-id="arm_user_old_plan" name="arm_user_old_plan" value="' . $arm_user_old_plan . '">';
                                    $module_content .= '<input type="hidden" name="arm_is_user_logged_in_flag" data-id="arm_is_user_logged_in_flag" value="' . $arm_is_user_logged_in_flag . '">';

                                    $all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans_and_posts();
                                    $plans = array_keys($all_active_plans);
                                    if (!empty($plans)) {

                                        $is_hide_class = '';
                                        if ($isHidePlans == true || $is_hide_plan_selection_area == true) {
                                            $is_hide_class = 'style="display:none;"';
                                        }
                                        $form_no = '';

                                        $form_layout = '';
                                        if (!empty($modules['forms']) && $modules['forms'] != 0) {
                                            if (!empty($form_settings)) {
                                                $form_no = 'arm_form_' . $modules['forms'];
                                                $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                            }
                                        }
                                        if (!empty($current_user_plan_ids)) {

                                            $module_content .= '<div class="arm_current_user_plan_info">' . $your_current_membership_text . ': <span>' . implode(", ", $plan_name_array) . '</span></div>';
                                        }
                                        $module_content .= '<div class="arm_module_plans_container arm_module_box ' . $form_no . ' ' . $form_layout . '" ' . $is_hide_class . '>';

                                        
                                        $column_type = (!empty($setup_modules['plans_columns'])) ? $setup_modules['plans_columns'] : '1';
                                        $module_content .= '<input type="hidden" name="arm_front_plan_skin_type" data-id="arm_front_plan_skin_type" value="' . $setup_style['plan_skin'] . '">';
                                        $allowed_payment_gateways = array();
                                        if ($setup_style['plan_skin'] == 'skin5') {
                                            $planSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $planSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $planSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_plan_dropdown_skin1" style="' . $planSkinFloat . '">';
                                            $module_content .= '<div class="payment_plan_dropdown_skin">';
                                            $module_content .= '<md-select name="subscription_plan" class="arm_module_plan_input select_skin" data-ng-model="subscription_plan" aria-label="plan" ng-change="armPlanChange(\'arm_setup_form' . $setupRandomID . '\')">';
                                            $i = 0;

                                            foreach ($plans as $plan_id) {
                                                if (isset($all_active_plans[$plan_id])) {

                                                    $plan_data = $all_active_plans[$plan_id];
                                                    $planObj = new ARM_Plan(0);
                                                    $planObj->init((object) $plan_data);
                                                    $plan_type = $planObj->type;
                                                    $planText = $planObj->setup_plan_text();
                                                    if ($planObj->exists()) {
                                                        /* Checked Plan Radio According Settings. */
                                                        $plan_checked = $plan_checked_class = '';
                                                        if (!empty($selected_plan_id) && $selected_plan_id != 0 && in_array($selected_plan_id, $plans)) {
                                                            if ($selected_plan_id == $plan_id) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'selected="selected"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        } else {
                                                            if ($i == 0) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'selected="selected"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        }

                                                        /* Check Recurring Details */
                                                        $plan_options = $planObj->options;

                                                        if (is_user_logged_in()) {
                                                            if ($arm_user_old_plan == $plan_id) {
                                                                $arm_user_payment_cycles = (isset($arm_user_old_plan_options['payment_cycles']) && !empty($arm_user_old_plan_options['payment_cycles'])) ? $arm_user_old_plan_options['payment_cycles'] : array();
                                                                if (empty($arm_user_payment_cycles)) {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($arm_user_old_plan_options['recurring']['time']) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($arm_user_old_plan_options['recurring']['type']) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['days']) ? $arm_user_old_plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['months']) ? $arm_user_old_plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['years']) ? $arm_user_old_plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                } else {
                                                                    if ( ($completed_recurrence == $total_recurring && $total_recurring!="infinite" ) || ($completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription')) {
                                                                        $arm_user_new_payment_cycles = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_new_payment_cycles;
                                                                    } else {
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_payment_cycles;
                                                                    }
                                                                }
                                                            } else {
                                                                if ($planObj->is_recurring()) {
                                                                    if (!empty($plan_options['payment_cycles'])) {
                                                                        $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                    } else {

                                                                        $plan_amount = $planObj->amount;
                                                                        $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                        $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                        switch ($recurring_type) {
                                                                            case 'D':
                                                                                $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                                break;
                                                                            case 'M':
                                                                                $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                                break;
                                                                            case 'Y':
                                                                                $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                                break;
                                                                            default:
                                                                                $billing_cycle = '1';
                                                                                break;
                                                                        }
                                                                        $payment_cycles = array(array(
                                                                                'cycle_label' => $planObj->plan_text(false, false),
                                                                                'cycle_amount' => $plan_amount,
                                                                                'billing_cycle' => $billing_cycle,
                                                                                'billing_type' => $recurring_type,
                                                                                'recurring_time' => $recurring_time,
                                                                                'payment_cycle_order' => 1,
                                                                        ));
                                                                        $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if ($planObj->is_recurring()) {
                                                                if (!empty($plan_options['payment_cycles'])) {
                                                                    $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                } else {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                }
                                                            }
                                                        }

                                                        $payment_type = $planObj->payment_type;
                                                        $is_trial = '0';
                                                        $trial_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, 0);
                                                        if ($planObj->is_recurring()) {
                                                            $stripePlans = (isset($modules['stripe_plans']) && !empty($modules['stripe_plans'])) ? $modules['stripe_plans'] : array();

                                                            if ($planObj->has_trial_period()) {
                                                                $is_trial = '1';
                                                                $trial_amount = !empty($plan_options['trial']['amount']) ?
                                                                        $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                                if (is_user_logged_in()) {
                                                                    if (!empty($current_user_plan_ids)) {
                                                                        if (in_array($planObj->ID, $current_user_plan_ids)) {
                                                                            $is_trial = '0';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if (!$planObj->is_free()) {
                                                            $trial_amount = !empty($plan_options['trial']['amount']) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                        }

                                                        $allowed_payment_gateways_['paypal'] = "1";
                                                        $allowed_payment_gateways_['stripe'] = "1";
                                                        $allowed_payment_gateways_['bank_transfer'] = "1";
                                                        $allowed_payment_gateways_['2checkout'] = "1";
                                                        $allowed_payment_gateways_['authorize_net'] = "1";
                                                        $allowed_payment_gateways_ = apply_filters('arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options);

                                                        $data_allowed_payment_gateways = json_encode($allowed_payment_gateways_);

                                                        $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, $planObj->amount);

                                                        $arm_plan_amount = $planObj->amount = apply_filters('arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data);

                                                        $planInputAttr = ' data-type="' . $plan_type . '" data-plan_name="' . $planObj->name . '" data-amt="' . $arm_plan_amount . '" data-recurring="' . $payment_type . '" data-is_trial="' . $is_trial . '" data-trial_amt="' . $trial_amount . '" data-allowed_gateways=\'' . $data_allowed_payment_gateways . '\' data-plan_text="' . htmlentities($planText) . '"';


                                                        $count_total_cycle = 0;
                                                        if($planObj->is_recurring()){

                                                          $count_total_cycle = count($plan_payment_cycles[$plan_id]);
                                                          $planInputAttr .= '  " data-cycle="'.$count_total_cycle .'"';
                                                        }
                                                        else{
                                                          $planInputAttr .= " data-cycle='0'";
                                                        }

                                                        
                                                        
                                                        $planInputAttr .= " data-tax='".$tax_percentage."'";

                                                       
                                                        $module_content .='<md-option value="' . $plan_id . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $planInputAttr . ' ' . $plan_checked . '>' . $planObj->name . ' (' . $planObj->plan_price(false) . ')</md-option>';
                                                        $i++;
                                                    }
                                                }
                                            }
                                            $module_content .= '</md-select>';
                                            $module_content .= '</div></div>';
                                        } 
                                        else{

                                            $module_content .= '<ul class="arm_module_plans_ul arm_column_' . $column_type . '" style="text-align:' . $formPosition . ';">';
                                            $i = 0;
                                            foreach ($plans as $plan_id) {
                                                if (isset($all_active_plans[$plan_id])) {
                                                    $plan_data = $all_active_plans[$plan_id];
                                                    $planObj = new ARM_Plan(0);
                                                    $planObj->init((object) $plan_data);
                                                    $plan_type = $planObj->type;
                                                    $planText = $planObj->setup_plan_text();
                                                    if ($planObj->exists()) {
                                                        /* Checked Plan Radio According Settings. */
                                                        $plan_checked = $plan_checked_class = '';
                                                        if (!empty($selected_plan_id) && $selected_plan_id != 0 && in_array($selected_plan_id, $plans)) {
                                                            if ($selected_plan_id == $plan_id) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'checked="checked"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        } else {
                                                            if ($i == 0) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'checked="checked"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        }
                                                        /* Check Recurring Details */
                                                        $plan_options = $planObj->options;

                                                        if (is_user_logged_in()) {
                                                            if ($arm_user_old_plan == $plan_id) {
                                                                $arm_user_payment_cycles = (isset($arm_user_old_plan_options['payment_cycles']) && !empty($arm_user_old_plan_options['payment_cycles'])) ? $arm_user_old_plan_options['payment_cycles'] : array();
                                                                if (empty($arm_user_payment_cycles)) {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($arm_user_old_plan_options['recurring']['time']) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($arm_user_old_plan_options['recurring']['type']) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['days']) ? $arm_user_old_plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['months']) ? $arm_user_old_plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['years']) ? $arm_user_old_plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                } else {

                                                                    if (($completed_recurrence == $total_recurring && $total_recurring!="infinite" ) || ($completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription')) {

                                                                        $arm_user_new_payment_cycles = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_new_payment_cycles;
                                                                    } else {
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_payment_cycles;
                                                                    }
                                                                }
                                                            } else {
                                                                if ($planObj->is_recurring()) {
                                                                    if (!empty($plan_options['payment_cycles'])) {
                                                                        $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                    } else {

                                                                        $plan_amount = $planObj->amount;
                                                                        $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                        $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                        switch ($recurring_type) {
                                                                            case 'D':
                                                                                $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                                break;
                                                                            case 'M':
                                                                                $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                                break;
                                                                            case 'Y':
                                                                                $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                                break;
                                                                            default:
                                                                                $billing_cycle = '1';
                                                                                break;
                                                                        }
                                                                        $payment_cycles = array(array(
                                                                                'cycle_label' => $planObj->plan_text(false, false),
                                                                                'cycle_amount' => $plan_amount,
                                                                                'billing_cycle' => $billing_cycle,
                                                                                'billing_type' => $recurring_type,
                                                                                'recurring_time' => $recurring_time,
                                                                                'payment_cycle_order' => 1,
                                                                        ));
                                                                        $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if ($planObj->is_recurring()) {
                                                                if (!empty($plan_options['payment_cycles'])) {
                                                                    $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                } else {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                }
                                                            }
                                                        }

                                                        $payment_type = $planObj->payment_type;

                                                        $is_trial = '0';
                                                        $trial_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, 0);

                                                        if ($planObj->is_recurring()) {
                                                            $stripePlans = (isset($modules['stripe_plans']) && !empty($modules['stripe_plans'])) ? $modules['stripe_plans'] : array();

                                                            if ($planObj->has_trial_period()) {
                                                                $is_trial = '1';
                                                                $trial_amount = !empty($plan_options['trial']['amount']) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                                if (is_user_logged_in()) {
                                                                    if (!empty($current_user_plan_ids)) {
                                                                        if (in_array($planObj->ID, $current_user_plan_ids)) {
                                                                            $is_trial = '0';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        $allowed_payment_gateways_['paypal'] = "1";
                                                        $allowed_payment_gateways_['stripe'] = "1";
                                                        $allowed_payment_gateways_['bank_transfer'] = "1";
                                                        $allowed_payment_gateways_['2checkout'] = "1";
                                                        $allowed_payment_gateways_['authorize_net'] = "1";
                                                        $allowed_payment_gateways_ = apply_filters('arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options);
                                                        $data_allowed_payment_gateways = json_encode($allowed_payment_gateways_);
                                                        $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, $planObj->amount);
                                                        $arm_plan_amount = $planObj->amount = apply_filters('arm_modify_secondary_amount_outside', $arm_plan_amount,  $plan_data);
                                                        $planInputAttr = ' data-type="' . $plan_type . '" data-plan_name="' . $planObj->name . '" data-amt="' . $arm_plan_amount . '" data-recurring="' . $payment_type . '" data-is_trial="' . $is_trial . '" data-trial_amt="' . $trial_amount . '"  data-allowed_gateways=\'' . $data_allowed_payment_gateways . '\' data-plan_text="' . htmlentities($planText) . '"';


                                                        $count_total_cycle = 0;
                                                        if($planObj->is_recurring()){

                                                          $count_total_cycle = count($plan_payment_cycles[$plan_id]);
                                                          $planInputAttr .= '  " data-cycle="'.$count_total_cycle .'"';
                                                        }
                                                        else{
                                                          $planInputAttr .= " data-cycle='0'";
                                                        }

                                                       
                                                        $planInputAttr .= " data-tax='".$tax_percentage."'";

                                                        
                                                        if ($setup_style['plan_skin'] == '') {
                                                            $module_content .= '<li class="arm_plan_default_skin arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        } else if ($setup_style['plan_skin'] == 'skin1') {
                                                            $module_content .= '<li class="arm_plan_skin1 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        } else if ($setup_style['plan_skin'] == 'skin3') {
                                                            $module_content .= '<li class="arm_plan_skin3 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<div class="arm_plan_name_box"><span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        }
                                                        else {
                                                            $module_content .= '<li class="arm_plan_skin2 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        }
                                                        $i++;
                                                    }
                                                }
                                            }
                                            $module_content .= '</ul>';
                                        }
                                        $module_content .= '</div>';
                                        $module_content = apply_filters('arm_after_setup_plan_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '<input type="hidden" data-ng-model="arm_form.arm_plan_type" name="arm_plan_type" value="' . ((!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'free' : 'paid') . '">';
                                    }

                                break;
                            case 'forms':

                                if (!empty($modules['forms']) && $modules['forms'] != 0) {
                                    if (!empty($form_settings)) {
                                        $form_style_class = 'arm_shortcode_form arm_form_' . $modules['forms'];
                                        $form_style_class .= ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                        $form_style_class .= ($form_settings['style']['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                                        $form_style_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
                                        $form_style_class .= ' armf_layout_' . $form_settings['style']['label_position'];
                                        $form_style_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
                                        $form_style_class .= ($form_settings['style']['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                                        $errPosCCField = !empty($form_settings['style']['validation_position']) ? $form_settings['style']['validation_position'] : 'bottom';
                                        $buttonStyle = (isset($form_settings['style']['button_style']) && !empty($form_settings['style']['button_style'])) ? $form_settings['style']['button_style'] : 'flat';
                                        $btn_style_class = ' arm_btn_style_' . $buttonStyle;

                                        $fieldPosition = !empty($form_settings['style']['field_position']) ? $form_settings['style']['field_position'] : 'left';
                                    }
                                    if (is_user_logged_in() && !$isPreview) {

                                      $form = new ARM_Form('id', $modules['forms']);
                                      $ref_template = $form->form_detail['arm_ref_template'];
                                        $form_css = $arm_member_forms->arm_ajax_generate_form_styles($modules['forms'], $form_settings, array(), $ref_template);
                                        $formStyle .= $form_css['arm_css'];
                                        $modules['forms'] = 0;
                                    } else {
                                        $formAttr = '';
                                        if ($isPreview) {
                                            $formAttr = 'preview="true"';
                                        }
                                        $module_content .= '<div class="arm_module_forms_container arm_module_box" data-ng-cloak="">';
                                        $module_content .= do_shortcode('[arm_form id="' . $modules['forms'] . '" setup="true" form_position="' . $formPosition . '" ' . $formAttr . ']');
                                        $module_content .= '</div>';
                                        $module_content = apply_filters('arm_after_setup_reg_form_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear"></div>';
                                    }
                                } else {
                                    if (!$isPreview) {
                                        /* Hide Setup Form for non-logged in users when there is no form configured */
                                        return '';
                                    }
                                }
                                break;
                            case 'note':
                                if (isset($setup_modules['note']) && !empty($setup_modules['note'])) {
                                    $module_content .= '<div class="arm_module_note_container arm_module_box">';
                                    $module_content .= apply_filters('the_content', stripslashes($setup_modules['note']));
                                    $module_content .= '</div>';
                                }
                                break;

                            case 'payment_cycle':
                                $form_layout = '';
                                if (!empty($form_settings)) {
                                    $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                }
                                $payment_mode = "both";

                                $module_content .= '<div class="arm_setup_paymentcyclebox_wrapper arm_hide">';

                                if (!empty($plan_payment_cycles)) {

                                    foreach ($plan_payment_cycles as $payment_cycle_plan_id => $plan_payment_cycle_data) {

                                        $arm_user_selected_payment_cycle = 0;
                                        if (!empty($current_plan_data)) {
                                            $arm_user_selected_payment_cycle = $current_plan_data['arm_payment_cycle'];
                                        }

                                        if (!empty($plan_payment_cycle_data)) {
                                            $module_content .= '<div class="arm_module_payment_cycle_container arm_module_box arm_payment_cycle_box_' . $payment_cycle_plan_id . ' arm_form_' . $setup_modules['modules']['forms'] . ' ' . $form_layout . ' arm_hide">';
                                            if (isset($setup_data['setup_labels']['payment_cycle_section_title']) && !empty($setup_data['setup_labels']['payment_cycle_section_title'])) {
                                                $module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . stripslashes_deep($setup_data['setup_labels']['payment_cycle_section_title']) . '</div>';
                                            } else {
                                                $module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . __('Select Payment Cycle', 'ARMember') . '</div>';
                                            }
                                            $column_type = (!empty($setup_modules['cycle_columns'])) ? $setup_modules['cycle_columns'] : '1';

                                            if (is_array($plan_payment_cycle_data)) {
                                                if (count($plan_payment_cycle_data) <= $arm_user_selected_payment_cycle) {
                                                    $arm_user_selected_payment_cycle_no = 0;
                                                } else {
                                                    $arm_user_selected_payment_cycle_no = $arm_user_selected_payment_cycle;
                                                }

                                                $module_content .= '<input type="hidden" name="arm_payment_cycle_plan_'.$payment_cycle_plan_id.'" data-id="arm_payment_cycle_plan_'.$payment_cycle_plan_id.'" value="'.$arm_user_selected_payment_cycle_no.'" data-ng-model="rm_payment_cycle_plan_'.$payment_cycle_plan_id.'">';
                                              }



                                            if($setup_style['plan_skin'] == 'skin5'){
                                              if (is_array($plan_payment_cycle_data)) {

                                                $paymentSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $paymentSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $paymentSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_gateway_dropdown_skin1" style="' . $paymentSkinFloat . '">';
                                            $module_content .= '<div class="payment_gateway_dropdown_skin">';

                                               $module_content .= '<md-select name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input select_skin" data-ng-model="payment_cycle_'.$payment_cycle_plan_id.'" ng-change="armPaymentCycleChange('.$payment_cycle_plan_id.', \'arm_setup_form' . $setupRandomID . '\')">';
                                               
                                              $i = 0; 


                                              foreach ($plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data) {

                                                    $pc_checked = $pc_checked_class = '';
                                                    if ($i == $arm_user_selected_payment_cycle_no) {
                                                        $pc_checked = 'selected="selected""';
                                                        $pc_checked_class = 'arm_active';
                                                    }


                                                    $arm_paymentg_cycle_amount = (isset($arm_cycle_data['cycle_amount'])) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $arm_cycle_data['cycle_amount']) : 0;
                                                    $arm_paymentg_cycle_amount = apply_filters('arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data);
                                                    $arm_paymentg_cycle_label = (isset($arm_cycle_data['cycle_label'])) ? $arm_cycle_data['cycle_label'] : '';


                                                    $module_content .='<md-option value="' . $arm_cycle_data_key  . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $pc_checked . '  data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '" >' . $arm_paymentg_cycle_label . '</md-option>';


                                                    $i++;
                                                }
                                                  $module_content .= '</md-select></div></div>';
                                              
                                            }


                                            }else{
                                                $module_content .= '<ul class="arm_module_payment_cycle_ul arm_column_' . $column_type . '">';
                                            $i = 0;

                                            if (is_array($plan_payment_cycle_data)) {
                                                foreach ($plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data) {

                                                    $pc_checked = $pc_checked_class = '';
                                                    if ($i == $arm_user_selected_payment_cycle_no) {
                                                        $pc_checked = 'checked="checked"';
                                                        $pc_checked_class = 'arm_active';
                                                    }


                                                    $arm_paymentg_cycle_amount = (isset($arm_cycle_data['cycle_amount'])) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $arm_cycle_data['cycle_amount']) : 0;
                                                    $arm_paymentg_cycle_amount = apply_filters('arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data);

                                                    $arm_paymentg_cycle_label = (isset($arm_cycle_data['cycle_label'])) ? $arm_cycle_data['cycle_label'] : '';

                                                    $pc_content = '<label class="arm_module_payment_cycle_option">';
                                                    $pc_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                    $pc_content .= '<input type="radio" name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input" value="' . ( $arm_cycle_data_key ) . '" ' . $pc_checked . ' data-ng-model="arm_form.payment_cycle" data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '">';
                                                    $pc_content .= '<div class="arm_module_payment_cycle_name"><span class="arm_module_payment_cycle_span">' . $arm_paymentg_cycle_label . '</span></div>';
                                                    $pc_content .= '</label>';

                                                    $module_content .= '<li class="arm_setup_column_item arm_payment_cycle_' . ( $arm_cycle_data_key ) . ' ' . $pc_checked_class . '"  data-plan_id="' . $payment_cycle_plan_id . '">';
                                                    $module_content .= $pc_content;
                                                    $module_content .= '</li>';
                                                    $i++;
                                                }
                                            }
                                            $module_content .= '</ul>';
                                            } 
                                            $module_content .= '</div>';
                                        }
                                    }
                                }
                                $module_content = apply_filters('arm_after_setup_payment_cycle_section', $module_content, $setupID, $setup_data);
                                $module_content .= '</div>';
                                break;
                            case 'gateways':

                                $form_layout = '';

                                if (!empty($form_settings)) {

                                    $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                }

                                $payment_mode = "both";
                                    $payment_gateway_skin = (isset($setup_style['gateway_skin']) && $setup_style['gateway_skin'] != '' ) ? $setup_style['gateway_skin'] : 'radio';
                                    $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                                    
                                   
                                    $gateways = array_keys($active_gateways);
                                    if (!empty($gateways)) {
                                        $is_display_pg = (!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'display:none;' : '';
                                        $module_content .= '<div class="arm_setup_gatewaybox_wrapper" style="' . $is_display_pg . '">';
                                        if (isset($setup_data['setup_labels']['payment_section_title']) && !empty($setup_data['setup_labels']['payment_section_title'])) {
                                            $module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . stripslashes_deep($setup_data['setup_labels']['payment_section_title']) . '</div>';
                                        }
                                        $module_content .= '<input type="hidden" name="arm_front_gateway_skin_type" data-id="arm_front_gateway_skin_type" value="' . $payment_gateway_skin . '">';
                                        $module_content .= '<div class="arm_module_gateways_container arm_module_box arm_form_' . $setup_modules['modules']['forms'] . ' ' . $form_layout . '">';

                                        $column_type = (!empty($setup_modules['gateways_columns'])) ? $setup_modules['gateways_columns'] : '1';

                                        $doNotDisplayPaymentMode = array('bank_transfer');
                                        $doNotDisplayPaymentMode = apply_filters('arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode);

                                        $pglabels = isset($setup_data['arm_setup_labels']['payment_gateway_labels']) ? $setup_data['arm_setup_labels']['payment_gateway_labels'] : array();

                                        if ($payment_gateway_skin == 'radio') {

                                            $module_content .= '<ul class="arm_module_gateways_ul arm_column_' . $column_type . '" style="text-align:' . $formPosition . ';">';
                                            $i = 0;
                                            $pg_fields = $selectedKey = '';

                                            foreach ($gateways as $pg) {
                                                if (in_array($pg, array_keys($active_gateways))) {
                                                    if (isset($selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period']) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription') {
                                                        if ($selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0) {
                                                            // continue;
                                                        }
                                                    }
                                                    if (!in_array($pg, $doNotDisplayPaymentMode)) {
                                                        $payment_mode = $modules['payment_mode'][$pg];
                                                    } else {
                                                        $payment_mode = 'manual_subscription';
                                                    }

                                                    $pg_options = $active_gateways[$pg];
                                                    $pg_checked = $pg_checked_class = '';
                                                    $display_block = 'arm_hide';
                                                    if ($i == 0) {
                                                        $pg_checked = 'checked="checked"';
                                                        $pg_checked_class = 'arm_active';
                                                        $display_block = '';
                                                        $selectedKey = $pg;
                                                    }
                                                    $pg_content = '<label class="arm_module_gateway_option">';
                                                    $pg_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                    $pg_content .= '<input type="radio" name="payment_gateway" class="arm_module_gateway_input" value="' . $pg . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '" data-ng-model="arm_form.payment_gateway">';
                                                    if (!empty($pglabels)) {
                                                      if(isset($pglabels[$pg])){
                                                        $pg_options['gateway_name'] = $pglabels[$pg];
                                                      }
                                                    }
                                                    $pg_content .= '<div class="arm_module_gateway_name"><span class="arm_module_gateway_span">' . stripslashes_deep($pg_options['gateway_name']) . '</span></div>';
                                                    $pg_content .= '</label>';
                                                    switch ($pg) {
                                                        case 'paypal':
                                                            break;
                                                        case 'stripe':
                                                            $hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $pg, $pg_options );
                                                            if( false == $hide_cc_fields ){
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_stripe ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('stripe', $column_type, $fieldPosition, $errPosCCField);
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                        case 'authorize_net':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_authorize_net ' . $display_block . ' arm_member_form_container">';
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('authorize_net', $column_type, $fieldPosition, $errPosCCField);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        case '2checkout':
                                                            break;
                                                        case 'bank_transfer':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . $display_block . ' arm_member_form_container">';
                                                            if (isset($pg_options['note']) && !empty($pg_options['note'])) {
                                                                $pg_fields .= '<div class="arm_bank_transfer_note_container">' . stripslashes($pg_options['note']) . '</div>';
                                                            }
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form($pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms']);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        default:
                                                            $gateway_fields = apply_filters('arm_membership_setup_gateway_option', '', $pg, $pg_options);
                                                            $pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $pg, $pg_options);
                                                            if ($pgHasCCFields) {
                                                                $gateway_fields .= $arm_payment_gateways->arm_get_credit_card_box($pg, $column_type, $fieldPosition, $errPosCCField);
                                                            }
                                                            if (!empty($gateway_fields)) {
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . $pg . ' ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $gateway_fields;
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                    }
                                                    $module_content .= '<li class="arm_setup_column_item arm_gateway_' . $pg . ' ' . $pg_checked_class . '">';
                                                    $module_content .= $pg_content;
                                                    $module_content .= '</li>';
                                                    $i++;
                                                    $module_content .= "<input type='hidden' name='arm_payment_mode[$pg]'  value='{$payment_mode}' />";
                                                }
                                            }
                                            $module_content .= '</ul>';
                                        } else {
                                            $paymentSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $paymentSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $paymentSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_gateway_dropdown_skin1" style="' . $paymentSkinFloat . '">';
                                            $module_content .= '<div class="payment_gateway_dropdown_skin">';
                                            $module_content .= '<md-select name="payment_gateway" class="arm_module_gateway_input select_skin" data-ng-model="payment_gateway" aria-label="gateway" ng-change="armPaymentGatewayChange(\'arm_setup_form' . $setupRandomID . '\')">';
                                            $i = 0;
                                            $pg_fields = $selectedKey = '';
                                            foreach ($gateways as $pg) {
                                                if (in_array($pg, array_keys($active_gateways))) {
                                                    $payment_gateway_name = $pg;
                                                    if (isset($selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period']) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription') {
                                                        if ($selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0) {
                                                            // continue;
                                                        }
                                                    }

                                                    if (!in_array($pg, $doNotDisplayPaymentMode)) {
                                                        $payment_mode = $modules['payment_mode'][$pg];
                                                    } else {
                                                        $payment_mode = 'manual_subscription';
                                                    }


                                                    $pg_options = $active_gateways[$pg];
                                                    $pg_checked = $pg_checked_class = '';
                                                    $display_block = 'arm_hide';
                                                    if ($i == 0) {
                                                        $pg_checked = 'selected="selected"';
                                                        $pg_checked_class = 'arm_active';
                                                        $display_block = '';
                                                        $selectedKey = $pg;
                                                    }

                                                    switch ($pg) {
                                                        case 'paypal':
                                                            break;
                                                        case 'stripe':
                                                            $hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $pg, $pg_options );
                                                            if( false == $hide_cc_fields ){
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_stripe ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('stripe', $column_type, $fieldPosition, $errPosCCField);
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                        case 'authorize_net':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_authorize_net ' . $display_block . ' arm_member_form_container">';
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('authorize_net', $column_type, $fieldPosition, $errPosCCField);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        case '2checkout':
                                                            break;
                                                        case 'bank_transfer':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . $display_block . ' arm_member_form_container">';
                                                            if (isset($pg_options['note']) && !empty($pg_options['note'])) {
                                                                $pg_fields .= '<div class="arm_bank_transfer_note_container">' . stripslashes($pg_options['note']) . '</div>';
                                                            }
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form($pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms']);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        default:
                                                            $gateway_fields = apply_filters('arm_membership_setup_gateway_option', '', $pg, $pg_options);
                                                            $pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $pg, $pg_options);
                                                            if ($pgHasCCFields) {
                                                                $gateway_fields .= $arm_payment_gateways->arm_get_credit_card_box($pg, $column_type, $fieldPosition, $errPosCCField);
                                                            }
                                                            if (!empty($gateway_fields)) {
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . $pg . ' ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $gateway_fields;
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                    }


                                                    
                                                     if (!empty($pglabels)) {
                                                      if(isset($pglabels[$pg])){
                                                        $pg_options['gateway_name'] = stripslashes_deep($pglabels[$pg]);
                                                      }
                                                    }


                                                    
                                                    $module_content .='<md-option value="' . $payment_gateway_name . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . ' arm_gateway_' . $payment_gateway_name . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '">' . $pg_options['gateway_name'] . '</md-option>';
                                                    $i++;
                                                    $module_content .= "<input type='hidden' name='arm_payment_mode[$pg]'  value='{$payment_mode}' />";
                                                }
                                            }
                                            $module_content .= '</md-select>';
                                            $module_content .= '</div></div>';
                                        }

                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= $pg_fields;
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '</div>';
                                        $module_content = apply_filters('arm_after_setup_gateway_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear" data-ng-init="armSetDefaultPaymentGateway(\'' . $selectedKey . '\');"></div>';
                                        $module_content .= '</div>';
                                        /* Payment Mode Module */


                                        $arm_automatic_sub_label = (isset($setup_data['setup_labels']['automatic_subscription']) && !empty($setup_data['setup_labels']['automatic_subscription'])) ? stripslashes_deep($setup_data['setup_labels']['automatic_subscription']) : __('Auto Debit Payment', 'ARMember');
                                        $arm_semi_automatic_sub_label = (isset($setup_data['setup_labels']['semi_automatic_subscription']) && !empty($setup_data['setup_labels']['semi_automatic_subscription'])) ? stripslashes_deep($setup_data['setup_labels']['semi_automatic_subscription']) : __('Manual Payment', 'ARMember');
                                        $module_content .= "<div class='arm_payment_mode_wrapper' id='arm_payment_mode_wrapper' style='text-align:{$formPosition};'>";
                                        $setup_data['setup_labels']['payment_mode_selection'] = (isset($setup_data['setup_labels']['payment_mode_selection']) && !empty($setup_data['setup_labels']['payment_mode_selection'])) ? $setup_data['setup_labels']['payment_mode_selection'] : __('How you want to pay?', 'ARMember');
                                        $module_content .= "<div class='arm_setup_section_title_wrapper arm_payment_mode_selection_wrapper' >" . stripslashes_deep($setup_data['setup_labels']['payment_mode_selection']) . "</div>";
                                        $module_content .= "<div class='arm_radio_outer_wrapper'><div class='arm_radio_wrapper'><input type='radio' checked='checked' name='arm_selected_payment_mode' value='auto_debit_subscription' class='arm_selected_payment_mode' id='arm_selected_payment_mode_auto_{$setupRandomID}'/><span></span></div><label for='arm_selected_payment_mode_auto_{$setupRandomID}' style='cursor:pointer;'>&nbsp;<span class='arm_payment_mode_label'>" . $arm_automatic_sub_label . "</span></label></div>";
                                        $module_content .= "<div class='arm_radio_outer_wrapper' style='margin-left: 30px;'><div class='arm_radio_wrapper'><input type='radio'  name='arm_selected_payment_mode' value='manual_subscription' class='arm_selected_payment_mode' id='arm_selected_payment_mode_semi_auto_{$setupRandomID}'/><span></span></div><label for='arm_selected_payment_mode_semi_auto_{$setupRandomID}' style='cursor:pointer;'>&nbsp;<span class='arm_payment_mode_label'>" . $arm_semi_automatic_sub_label . "</span></label></div>";
                                        $module_content .= "";
                                        $module_content .= "</div>";
                                    }
                               
                                break;
                            case 'order_detail':
                                if (!empty($modules['plans'])) {
                                    /* $module_content .= '<div class="arm_order_description arm_module_box"></div>'; */
                                    if ($arm_manage_coupons->isCouponFeature && !empty($modules['coupons']) && $modules['coupons'] == '1') {
                                        $labels = array(
                                            'title' => (!empty($button_labels['coupon_title'])) ? $button_labels['coupon_title'] : '',
                                            'button' => (!empty($button_labels['coupon_button'])) ? $button_labels['coupon_button'] : '',
                                        );
                                        $is_used_as_invitation_code = (isset($setup_modules['modules']['coupon_as_invitation']) && $setup_modules['modules']['coupon_as_invitation'] == 1) ? true : false;

                                        $module_content .= '<div class="arm_setup_couponbox_wrapper">';
                                        if (isset($button_labels['coupon_title']) && !empty($button_labels['coupon_title'])) {
                                            $module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . $button_labels['coupon_title'] . '</div>';
                                        }
                                        $module_content .= '<div class="arm_module_coupons_container arm_module_box">';
                                        $is_display_coupons = (!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'display:none;' : '';
                                        $module_content .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                        $module_content .= '<div class="arm_form_inner_container arm_msg_pos_' . $errPosCCField . '" style="padding: 0 !important;">';
                                        $module_content .= '<div class="arm_coupon_fields arm_form_wrapper_container">';
                                        $module_content .= $arm_manage_coupons->arm_redeem_coupon_html('', $labels, $selected_plan_data, $btn_style_class, $is_used_as_invitation_code, $setupRandomID, $formPosition);

                                        $module_content .= '</div>';
                                        $module_content .= '</div>';
                                        $module_content .= '</div>';
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '</div>';
                                        $module_content .= '</div>';
                                    }
                                    $module_content = apply_filters('arm_after_setup_order_detail', $module_content, $setupID, $setup_data);
                                    if (isset($setup_data['setup_labels']['summary_text']) && !empty($setup_data['setup_labels']['summary_text'])) {
                                        $setupSummaryText = stripslashes($setup_data['setup_labels']['summary_text']);
                                        $setupSummaryText = str_replace('[PLAN_NAME]', '<span class="arm_plan_name_text"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PLAN_CYCLE_NAME]', '<span class="arm_plan_cycle_name_text"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PLAN_AMOUNT]', '<span class="arm_plan_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TAX_AMOUNT]', '<span class="arm_tax_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TAX_PERCENTAGE]', '<span class="arm_tax_percentage_text">'.$tax_percentage.'</span>%', $setupSummaryText);
                                        $setupSummaryText = str_replace('[DISCOUNT_AMOUNT]', '<span class="arm_discount_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PAYABLE_AMOUNT]', '<span class="arm_payable_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TRIAL_AMOUNT]', '<span class="arm_trial_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $module_content .= "<div class='arm_setup_summary_text_container arm_module_box' style='text-align:{$formPosition};'>";
                                        $module_content .= '<input type="hidden" name="arm_total_payable_amount" data-id="arm_total_payable_amount" ng-model="arm_total_payable_amount" value=""/>';
                                        $module_content .= '<input type="hidden" name="arm_zero_amount_discount" data-id="arm_zero_amount_discount" ng-model="arm_zero_amount_discount" value="' . $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency) . '"/>';
                                        $module_content .= '<div class="arm_setup_summary_text">' . nl2br($setupSummaryText) . '</div>';
                                        $module_content .= '</div>';
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                        $module_html .= $module_content;
                    }

                    $content = apply_filters('arm_before_setup_form_content', $content, $setupID, $setup_data);
                    $content .= '<div class="arm_setup_form_container">';
                    $content .= '<style type="text/css" id="arm_setup_style_' . $args['id'] . '">';
                    if (!empty($setup_style)) {
                        $sfontFamily = isset($setup_style['font_family']) ? $setup_style['font_family'] : '';
                        $gFontUrl = $arm_member_forms->arm_get_google_fonts_url(array($sfontFamily));
                        if (!empty($gFontUrl)) {
                            //$setupGoogleFonts .= '<link id="google-font-' . $setupID . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
                            wp_enqueue_style( 'google-font-'.$setupID, $gFontUrl, array(), MEMBERSHIP_VERSION );
                        }
                        $content .= $this->arm_generate_setup_style($setupID, $setup_style);
                    }
                    if (!empty($formStyle)) {
                        $content .= $formStyle;
                    }
                    if (!empty($custom_css)) {
                        $content .= $custom_css;
                    }
                    $content .= '</style>';
                    $content .= $setupGoogleFonts;
                    $content .= '<div class="arm_setup_messages arm_form_message_container"></div>';
                    $form_attr = ' data-ng-controller="ARMCtrl" data-ng-submit="armSetupFormSubmit(arm_form.$valid, \'arm_setup_form' . $setupRandomID . '\', $event);" onsubmit="return false;"';
                    $is_form_class_rtl = '';
                    if (is_rtl()) {
                        $is_form_class_rtl = 'is_form_class_rtl';
                    }
                    $captcha_code = arm_generate_captcha_code();
                    if (!isset($_SESSION['ARM_FILTER_INPUT'])) {
                        $_SESSION['ARM_FILTER_INPUT'] = array();
                    }
                    if (isset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID])) {
                        unset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID]);
                    }
                    $_SESSION['ARM_FILTER_INPUT'][$setupRandomID] = $captcha_code;
                    $_SESSION['ARM_VALIDATE_SCRIPT'] = true;
                    $form_attr .= ' data-submission-key="' . $captcha_code . '" ';
                    $content .= '<form method="post" name="arm_form" id="arm_setup_form' . $setupRandomID . '" class="arm_setup_form_' . $setupID . ' arm_membership_setup_form arm_form_' . $modules['forms'] . ' ' . $is_form_class_rtl . '" enctype="multipart/form-data" data-random-id="' . $setupRandomID . '" novalidate ' . $form_attr . '>';
                    if ($args['hide_title'] == false && $args['popup'] == false) {
                        $content .= '<h3 class="arm_setup_form_title">' . $setup_name . '</h3>';
                    }
                    $content .= '<input type="hidden" name="setup_id" value="' . $setupID . '" data-id="arm_setup_id"/>';
                    $content .= '<input type="hidden" name="setup_action" value="membership_setup"/>';
                    $content .= "<input type='text' name='arm_filter_input' data-random-key='{$setupRandomID}' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
                    $content .= '<div class="arm_setup_form_inner_container">';
                    $content .= '<input type="hidden" class="arm_global_currency" value="' . $global_currency . '"/>';
                    // $currency_separators = $arm_payment_gateways->get_currency_separators_standard();
                    // $currency_separators = json_encode($currency_separators);
                    $currency_separators = $arm_payment_gateways->get_currency_wise_separator($global_currency);
                    $currency_separators = (!empty($currency_separators)) ? json_encode($currency_separators) : '';
                    $content .= "<input type='hidden' class='arm_global_currency_separators' value='" . $currency_separators . "'/>";
                    $content .= '<input type="hidden" class="arm_pay_thgough_mpayment" name="arm_pay_thgough_mpayment" value="1"/>';

                    /* tax values */
                    if($enable_tax == 1 && !empty($tax_values)) {
                        $content .= "<input type='hidden' name='arm_tax_type' value='".$tax_values["tax_type"]."'/>";
                        if($tax_values['tax_type'] =='country_tax') {
                            $content .= "<input type='hidden' name='arm_country_tax_field' value='".$tax_values["country_tax_field"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_field_opts' value='".$tax_values["country_tax_field_opts_json"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_amount' value='".$tax_values["country_tax_amount_json"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_default_val' value='".$tax_values["tax_percentage"]."'/>";
                        }
                        else {
                            $content .= "<input type='hidden' name='arm_common_tax_amount' value='".$tax_values["tax_percentage"]."'/>";
                        }
                    }
                    /* tax values over */

                    $content .= $module_html;
                    $content .= '<div class="armclear"></div>';
                    $content .= '<div class="arm_setup_submit_btn_wrapper ' . $form_style_class . '" data-ng-cloak="">';
                    $content .= '<div class="arm_form_field_container arm_form_field_container_submit">';
                    $content .= '<div class="arm_label_input_separator"></div>';
                    $content .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_submit"></div>';
                    $content .= '<div class="arm_form_input_wrapper">';
                    $content .= '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_setup_form_input_container' . $setupID . '">';
                    $ngClick = 'ng-click="armSubmitBtnClick($event)"';
                    if (current_user_can('administrator')) {
                        $ngClick = 'onclick="return false;"';
                    }
                    $content .= '<md-button type="submit" name="ARMSETUPSUBMIT" class="arm_setup_submit_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input ' . $btn_style_class . '" ' . $ngClick . '><span class="arm_spinner">' . file_get_contents(MEMBERSHIP_IMAGES_DIR . "/loader.svg") . '</span>' . html_entity_decode(stripslashes($submit_btn)) . '</md-button>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</form></div>';

                    if ($args['popup'] !== false) {
                        $popup_content = '<div class="arm_setup_form_popup_container">';
                        $link_title = (!empty($args['link_title'])) ? $args['link_title'] : $setup_name;
                        $link_style = $link_hover_style = '';
                        $popup_content .= '<style type="text/css">';
                        if (!empty($args['link_css'])) {
                            $link_style = esc_html($args['link_css']);
                            $popup_content .= '.arm_setup_form_popup_link_' . $setupID . '{' . $link_style . '}';
                        }
                        if (!empty($args['link_hover_css'])) {
                            $link_hover_style = esc_html($args['link_hover_css']);
                            $popup_content .= '.arm_setup_form_popup_link_' . $setupID . ':hover{' . $link_hover_style . '}';
                        }
                        $popup_content .= '</style>';
                        $pformRandomID = $setupID . '_popup_' . arm_generate_random_code();
                        $popupLinkID = 'arm_setup_form_popup_link_' . $setupID;
                        $popupLinkClass = 'arm_setup_form_popup_link arm_setup_form_popup_link_' . $setupID;
                        if (!empty($args['link_class'])) {
                            $popupLinkClass.=" " . esc_html($args['link_class']);
                        }
                        $popupLinkAttr = 'data-form_id="' . $pformRandomID . '" data-toggle="armmodal"  data-modal_bg="' . $args['modal_bgcolor'] . '" data-overlay="' . $args['overlay'] . '"';
                        if (!empty($args['link_type']) && strtolower($args['link_type']) == 'button') {
                            $popup_content .= '<button type="button" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_setup_form_popup_button" ' . $popupLinkAttr . '>' . $link_title . '</button>';
                        } else {
                            $popup_content .= '<a href="javascript:void(0)" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_setup_form_popup_ahref" ' . $popupLinkAttr . '>' . $link_title . '</a>';
                        }
                        $popup_style = $popup_content_height = '';
                        $popupHeight = 'auto';
                        $popupWidth = '500';
                        if (!empty($args['popup_height'])) {
                            if ($args['popup_height'] == 'auto') {
                                $popup_style .= 'height: auto;';
                            } else {
                                $popup_style .= 'overflow: hidden;height: ' . $args['popup_height'] . 'px;';
                                $popupHeight = ($args['popup_height'] - 70) . 'px';
                                $popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ($args['popup_height'] - 70) . 'px;';
                            }
                        }
                        if (!empty($args['popup_width'])) {
                            if ($args['popup_width'] == 'auto') {
                                $popup_style .= '';
                            } else {
                                $popupWidth = $args['popup_width'];
                                $popup_style .= 'width: ' . $args['popup_width'] . 'px;';
                            }
                        }
                        $popup_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_setup_form arm_popup_member_setup_form_' . $setupID . ' arm_popup_member_setup_form_' . $pformRandomID . '" style="' . $popup_style . '" data-width="' . $popupWidth . '"><div class="popup_setup_inner_container popup_wrapper_inner">';
                        $popup_content .= '<div class="popup_header">';
                        $popup_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
                        $popup_content .= '<div class="popup_header_text arm_setup_form_heading_container">';
                        if ($args['hide_title'] == false) {
                            $popup_content .= '<span class="arm_setup_form_field_label_wrapper_text">' . $setup_name . '</span>';
                        }
                        $popup_content .= '</div>';
                        $popup_content .= '</div>';
                        $popup_content .= '<div class="popup_content_text" style="' . $popup_content_height . '" data-height="' . $popupHeight . '">';
                        $popup_content .= $content;
                        $popup_content .= '</div><div class="armclear"></div>';
                        $popup_content .= '</div></div>';
                        $popup_content .= '</div>';
                        $content = $popup_content;
                        $content .= '<div class="armclear">&nbsp;</div>';
                    }
                    $content = apply_filters('arm_after_setup_form_content', $content, $setupID, $setup_data);
                }
            }
            $ARMember->arm_check_font_awesome_icons($content);
            $ARMember->enqueue_angular_script();
            return do_shortcode($content);
        }

        function arm_setup_shortcode_func($atts, $content = "") {
            global $wp, $wpdb, $current_user, $ARMember, $arm_member_forms, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans, $bpopup_loaded, $ARMSPAMFILEURL;
            $ARMember->arm_session_start();
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $defaults = array(
                'id' => 0, /* Membership Setup Wizard ID */
                'hide_title' => false,
                'class' => '',
                'popup' => false, /* Form will be open in popup box when options is true */
                'link_type' => 'link',
                'link_class' => '', /* /* Possible Options:- `link`, `button` */
                'link_title' => __('Click here to open Set up form', 'ARMember'), /* Default to form name */
                'popup_height' => '',
                'popup_width' => '',
                'overlay' => '0.6',
                'modal_bgcolor' => '#000000',
                'redirect_to' => '',
                'link_css' => '',
                'link_hover_css' => '',
                'is_referer' => '0',
                'preview' => false,
                'setup_data' => '',
                'subscription_plan' => 0,
                'hide_plans' => 0,
                'payment_duration' => 0,
                'setup_form_id' => '',
                'your_current_membership_text' =>__('Your Current Membership', 'ARMember'),
            );
            /* Extract Shortcode Attributes */
            $args = shortcode_atts($defaults, $atts, 'arm_setup');
            extract($args);
            $args['hide_title'] = ($args['hide_title'] === 'true' || $args['hide_title'] == '1') ? true : false;
            $args['popup'] = ($args['popup'] === 'true' || $args['popup'] == '1') ? true : false;
            $isPreview = ($args['preview'] === 'true' || $args['preview'] == '1') ? true : false;
            if ($args['popup']) {
                $bpopup_loaded = 1;
            }
            $completed_recurrence = '';
            $total_recurring = '';
            /* ====================/.End Set Shortcode Attributes./==================== */
            if ((!empty($args['id']) && $args['id'] != 0) || ($isPreview && !empty($args['setup_data']))) {

                $setupID = $args['id'];
                if ($isPreview && !empty($args['setup_data'])) {
                    $setup_data = maybe_unserialize($args['setup_data']);
                    $setup_data['arm_setup_labels'] = $setup_data['setup_labels'];
                } else {
                    $setup_data = $this->arm_get_membership_setup($setupID);
                }
                $setup_data = apply_filters('arm_setup_data_before_setup_shortcode', $setup_data, $args);
                do_action('arm_before_render_membership_setup_form', $setup_data, $args);


                if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $setupRandomID = $setupID . '_' . arm_generate_random_code();
                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                    $current_user_id = get_current_user_id();
                    $current_user_plan_ids = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                    $current_user_plan_ids = !empty($current_user_plan_ids) ? $current_user_plan_ids : array();

                    $user_posts = get_user_meta($current_user_id, 'arm_user_post_ids', true);
                    $user_posts = !empty($user_posts) ? $user_posts : array();    

                    if(!empty($current_user_plan_ids) && !empty($user_posts))
                    {
                        foreach ($current_user_plan_ids as $user_plans_key => $user_plans_val) {
                            if(!empty($user_posts)){
                                foreach ($user_posts as $user_post_key => $user_post_val) {
                                    if($user_post_key==$user_plans_val){
                                        unset($current_user_plan_ids[$user_plans_key]);
                                    }
                                }
                            }
                        }
                    }

                    $current_user_plan_ids = apply_filters('arm_modify_plan_ids_externally', $current_user_plan_ids, $current_user_id);

                    $current_user_plan = '';
                    $current_plan_data = array();
                    if (!empty($current_user_plan_ids)) {
                        $current_user_plan = current($current_user_plan_ids);
                        $current_plan_data = get_user_meta($current_user_id, 'arm_user_plan_' . $current_user_plan, true);
                    }
                    $setup_name = (!empty($setup_data['setup_name'])) ? stripslashes($setup_data['setup_name']) : '';
                    $button_labels = $setup_data['setup_labels']['button_labels'];
                    $submit_btn = (!empty($button_labels['submit'])) ? $button_labels['submit'] : __('Submit', 'ARMember');
                    $setup_modules = $setup_data['setup_modules'];
                    $user_selected_plan = isset($setup_modules['selected_plan']) ? $setup_modules['selected_plan'] : "";
                    $modules = $setup_modules['modules'];
                    $setup_style = isset($setup_modules['style']) ? $setup_modules['style'] : array();
                    $setup_type = (!empty($setup_data['setup_type'])) ? stripslashes($setup_data['setup_type']) : 0;

                    $tax_percentage = 0;
                    $enable_tax = isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;
                    if($enable_tax == 1) {
                        $tax_values = $this->arm_get_sales_tax($general_settings, '', $current_user_id, $modules['forms']);
                        $tax_percentage = !empty($tax_values["tax_percentage"]) ? $tax_values["tax_percentage"] : '0';
                    }

                    $formPosition = (isset($setup_style['form_position']) && !empty($setup_style['form_position'])) ? $setup_style['form_position'] : 'left';
                    $plan_selection_area = (isset($setup_style['plan_area_position']) && !empty($setup_style['plan_area_position'])) ? $setup_style['plan_area_position'] : 'before';

                    $hide_current_plans = isset($setup_style['hide_current_plans']) ? $setup_style['hide_current_plans'] : 0;
                    $previuos_button_label = (isset($button_labels['previous']) && !empty($button_labels['previous'])) ? stripslashes_deep($button_labels['previous']) : __('Previous', 'ARMember');
                    $next_button_label = (isset($button_labels['next']) && !empty($button_labels['next']) )? stripslashes_deep($button_labels['next']) : __('Next', 'ARMember');

                    $two_step = (isset($setup_style['two_step'])) ? $setup_style['two_step'] : 0;
                    


                    $fieldPosition = 'left';
                    $custom_css = isset($setup_modules['custom_css']) ? $setup_modules['custom_css'] : '';
                    $modules['step'] = (!empty($modules['step'])) ? $modules['step'] : array(-1);

                    if ($plan_selection_area == 'before' || $two_step == 1) {
                        $module_order = array(
                            'plans' => 1,
                            'payment_cycle' =>2,
                            'note' => 3,
                            'forms' => 4,
                            'gateways' => 5,
                            'order_detail' => 6,
                        );
                    } else {


                        $module_order = array(
                            'forms' => 1,
                            'plans' => 2,
                            'payment_cycle' => 3,
                            'note' => 4,
                            'gateways' => 5,
                            'order_detail' => 6,
                        );
                    }

                    $modules['forms'] = (!empty($modules['forms']) && $modules['forms'] != 0) ? $modules['forms'] : 0;
                    $step_one_modules = $step_two_modules = '';
                    /* Check `GET` or `POST` Data */
                    /* first check if user have selected any plan than select that plan otherwise set value from options of setup */
                    if ($current_user_plan != '') {
                        $selected_plan_id = $current_user_plan;
                    } else {
                        $selected_plan_id = $user_selected_plan;
                    }
                    if (!empty($_REQUEST['subscription_plan']) && $_REQUEST['subscription_plan'] != 0) {
                        $selected_plan_id = $_REQUEST['subscription_plan'];
                    }

                    
                    $selected_payment_duration = 1;
                    if (!empty($_REQUEST['payment_duration']) && $_REQUEST['payment_duration'] != 0) {
                        $selected_payment_duration = $_REQUEST['payment_duration'];
                    }
                    if (!empty($args['subscription_plan']) && $args['subscription_plan'] != 0) {
                        $selected_plan_id = $args['subscription_plan'];
                        if (!empty($args['payment_duration']) && $args['payment_duration'] != 0) {
                            $selected_payment_duration = $args['payment_duration'];
                        }
                    }

                    $isHidePlans = false;
                    if (!empty($selected_plan_id) && $selected_plan_id != 0) {
                        if (!empty($_REQUEST['hide_plans']) && $_REQUEST['hide_plans'] == 1) {
                            $isHidePlans = true;
                        }
                        if (!empty($args['hide_plans']) && $args['hide_plans'] == 1) {
                            $isHidePlans = true;
                        }
                    }

                    $is_hide_plan_selection_area = false;
                    if (isset($setup_style['hide_plans']) && $setup_style['hide_plans'] == 1) {
                        $is_hide_plan_selection_area = true;
                    }

                    $arm_two_step_class = '';
                    if($two_step){
                      if ($isHidePlans == true || $is_hide_plan_selection_area == true) {

                      }
                      else{
                        $arm_two_step_class = ' arm_hide';
                      }
                    }

                    
                    if (is_user_logged_in()) {
                        global $current_user;
                        if (!empty($current_user->data->arm_primary_status)) {
                            $current_user_status = $current_user->data->arm_primary_status;
                        } else {
                            $current_user_status = arm_get_member_status($current_user_id);
                        }
                    }
                    
                    $selected_plan_data = array();
                    $module_html = $formStyle = $setupGoogleFonts = '';
                    $errPosCCField = 'right';
                    if (is_rtl()) {
                        $is_form_class_rtl = 'arm_form_rtl';
                    } else {
                        $is_form_class_rtl = 'arm_form_ltr';
                    }
                    $form_style_class = ' arm_shortcode_form arm_form_0 arm_form_layout_writer armf_label_placeholder armf_alignment_left armf_layout_block armf_button_position_left ' . $is_form_class_rtl;
                    $btn_style_class = ' arm_btn_style_flat ';
                    if (!empty($modules['forms'])) {
                        /* Query Monitor Change */
                        if( isset($GLOBALS['arm_setup_form_settings']) && isset($GLOBALS['arm_setup_form_settings'][$modules['forms']])){
                            $form_settings = $GLOBALS['arm_setup_form_settings'][$modules['forms']];
                        } else {
                            $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $modules['forms'] . "'");
                            if( !isset($GLOBALS['arm_setup_form_settings']) ){
                                $GLOBALS['arm_setup_form_settings'] = array();
                            }
                            $GLOBALS['arm_setup_form_settings'][$modules['forms']] = $form_settings;
                        }
                        $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
                    }
                    $plan_payment_cycles = array();

                    foreach ($module_order as $module => $order) {
                        $module_content = '';
                        $arm_user_id = 0;
                        $arm_user_old_plan = 0;
                        $plan_id_array = array();
                        $arm_user_selected_payment_mode = 0;
                        $arm_user_selected_payment_cycle = 0;
                        $arm_last_payment_status = 'success';

                        switch ($module) {
                            case 'plans':
                                if (!empty($modules['plans'])) {
                                    if (is_user_logged_in()) {
                                        global $current_user;
                                        $arm_user_id = $current_user->ID;

                                        $user_firstname = $current_user->user_firstname;
                                        $user_lastname = $current_user->user_lastname;
                                        $user_email = $current_user->user_email;
                                        if($user_firstname != '' && $user_lastname != ''){
                                          $arm_user_firstname_lastname = $user_firstname. ' '.$user_lastname;
                                        }
                                        else{
                                          $arm_user_firstname_lastname = $user_email;
                                        }
                                        

                                        if (!empty($current_user_plan_ids)) {
                                            $plan_name_array = array();
                                            foreach ($current_user_plan_ids as $plan_id) {
                                                $planData = get_user_meta($arm_user_id, 'arm_user_plan_' . $plan_id, true);
                                                $arm_user_selected_payment_mode = $planData['arm_payment_mode'];
                                                $arm_user_current_plan_detail = $planData['arm_current_plan_detail'];

                                                $plan_name_array[] = isset($arm_user_current_plan_detail['arm_subscription_plan_name']) ? stripslashes($arm_user_current_plan_detail['arm_subscription_plan_name']) : '';
                                                $plan_id_array[] = $plan_id;

                                                $curPlanDetail = $planData['arm_current_plan_detail'];
                                                $completed_recurrence = $planData['arm_completed_recurring'];
                                                if (!empty($curPlanDetail)) {
                                                    $arm_user_old_plan_info = new ARM_Plan(0);
                                                    $arm_user_old_plan_info->init((object) $curPlanDetail);
                                                } else {
                                                    $arm_user_old_plan_info = new ARM_Plan($arm_user_old_plan);
                                                }
                                                $total_recurring = '';
                                                $arm_user_old_plan_options = $arm_user_old_plan_info->options;
                                                if ($arm_user_old_plan_info->is_recurring()) {
                                                    $arm_user_selected_payment_cycle = $planData['arm_payment_cycle'];
                                                    $arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data($arm_user_selected_payment_cycle);
                                                    $total_recurring = $arm_user_old_plan_data['rec_time'];

                                                    $now = current_time('mysql');
                                                    
                                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $arm_user_id, $plan_id, $now)); 

                                                }


                                                $module_content .= '<input type="hidden" data-id="arm_user_firstname_lastname" value="' . $arm_user_firstname_lastname . '">';

                                                $module_content .= '<input type="hidden" data-id="arm_user_last_payment_status_' . $plan_id . '" value="' . $arm_last_payment_status . '">';

                                                $module_content .= '<input type="hidden" data-id="arm_user_done_payment_' . $plan_id . '" value="' . $completed_recurrence . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_old_plan_total_cycle_' . $plan_id . '" value="' . $total_recurring . '">';

                                                $module_content .= '<input type="hidden" data-id="arm_user_selected_payment_cycle_' . $plan_id . '" value="' . $arm_user_selected_payment_cycle . '">';
                                                $module_content .= '<input type="hidden" data-id="arm_user_selected_payment_mode_' . $plan_id . '" value="' . $arm_user_selected_payment_mode . '">';
                                            }
                                        }
                                        $arm_is_user_logged_in_flag = 1;
                                    } else {
                                        $arm_is_user_logged_in_flag = 0;
                                    }

                                    if (!empty($plan_id_array)) {
                                        $arm_user_old_plan = implode(",", $plan_id_array);
                                    }

                                    $module_content .= '<input type="hidden" data-id="arm_user_old_plan" name="arm_user_old_plan" value="' . $arm_user_old_plan . '">';
                                    $module_content .= '<input type="hidden" name="arm_is_user_logged_in_flag" data-id="arm_is_user_logged_in_flag" value="' . $arm_is_user_logged_in_flag . '">';
                                    $planOrders = (isset($modules['plans_order']) && !empty($modules['plans_order'])) ? $modules['plans_order'] : array();
                                    if (!empty($planOrders)) {
                                        asort($planOrders);
                                    }
                                    $plans = $this->armSortModuleOrders($modules['plans'], $planOrders);
                                    if (!empty($plans)) {
                                        $all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
                                        $all_active_plans = apply_filters('arm_filter_active_plans_for_setup', $all_active_plans, $setup_type);
                                        
                                        $is_hide_class = '';
                                        if ($isHidePlans == true || $is_hide_plan_selection_area == true) {
                                            $is_hide_class = 'style="display:none;"';
                                        }
                                        $form_no = '';

                                        $form_layout = '';
                                        if (!empty($modules['forms']) && $modules['forms'] != 0) {
                                            
                                            if (!empty($form_settings)) {
                                                $form_no = 'arm_form_' . $modules['forms'];
                                                $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                            }
                                        }

                                        if (!empty($current_user_plan_ids)) {

                                            $module_content .= '<div class="arm_current_user_plan_info">' . $your_current_membership_text . ': <span>' . implode(", ", $plan_name_array) . '</span></div>';
                                        }
                                       
                                        $module_content .= '<div class="arm_module_plans_main_container"><div class="arm_module_plans_container arm_module_box ' . $form_no . ' ' . $form_layout . '" ' . $is_hide_class . '>';

                                        
                                        $column_type = (!empty($setup_modules['plans_columns'])) ? $setup_modules['plans_columns'] : '1';
                                        $module_content .= '<input type="hidden" name="arm_front_plan_skin_type" data-id="arm_front_plan_skin_type" value="' . $setup_style['plan_skin'] . '">';
                                        $allowed_payment_gateways = array();

                                        if($hide_current_plans == 1){
                                            if(!empty($current_user_plan_ids)){
                                                $plans = array_diff($plans, $current_user_plan_ids);
                                            }
                                        }
                                        if ($setup_style['plan_skin'] == 'skin5') {
                                            $planSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $planSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $planSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_plan_dropdown_skin1" style="' . $planSkinFloat . '">';
                                            $module_content .= '<div class="payment_plan_dropdown_skin">';
                                            $module_content .= '<md-select name="subscription_plan" class="arm_module_plan_input select_skin" data-ng-model="subscription_plan" aria-label="plan" ng-change="armPlanChange(\'arm_setup_form' . $setupRandomID . '\')">';
                                            
                                            $i = 0;

                                            if(empty($plans)){
                                                return;
                                            }

                                            foreach ($plans as $plan_id) {
                                                if (isset($all_active_plans[$plan_id])) {

                                                    $plan_data = $all_active_plans[$plan_id];
                                                    $planObj = new ARM_Plan(0);
                                                    $planObj->init((object) $plan_data);
                                                    $plan_type = $planObj->type;
                                                    $planText = $planObj->setup_plan_text();
                                                    if ($planObj->exists()) {
                                                        /* Checked Plan Radio According Settings. */
                                                        $plan_checked = $plan_checked_class = '';
                                                        if (!empty($selected_plan_id) && $selected_plan_id != 0 && in_array($selected_plan_id, $plans)) {
                                                            if ($selected_plan_id == $plan_id) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'selected="selected"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        } else {
                                                            if ($i == 0) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'selected="selected"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        }

                                                        /* Check Recurring Details */
                                                        $plan_options = $planObj->options;

                                                        if (is_user_logged_in()) {
                                                            if ($arm_user_old_plan == $plan_id) {
                                                                $arm_user_payment_cycles = (isset($arm_user_old_plan_options['payment_cycles']) && !empty($arm_user_old_plan_options['payment_cycles'])) ? $arm_user_old_plan_options['payment_cycles'] : array();
                                                                if (empty($arm_user_payment_cycles)) {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($arm_user_old_plan_options['recurring']['time']) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($arm_user_old_plan_options['recurring']['type']) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['days']) ? $arm_user_old_plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['months']) ? $arm_user_old_plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['years']) ? $arm_user_old_plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                } else {
                                                                    if (($completed_recurrence == $total_recurring && $total_recurring!="infinite" ) || ($completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription')) {
                                                                        $arm_user_new_payment_cycles = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_new_payment_cycles;
                                                                    } else {
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_payment_cycles;
                                                                    }
                                                                }
                                                            } else {
                                                                if ($planObj->is_recurring()) {
                                                                    if (!empty($plan_options['payment_cycles'])) {
                                                                        $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                    } else {

                                                                        $plan_amount = $planObj->amount;
                                                                        $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                        $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                        switch ($recurring_type) {
                                                                            case 'D':
                                                                                $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                                break;
                                                                            case 'M':
                                                                                $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                                break;
                                                                            case 'Y':
                                                                                $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                                break;
                                                                            default:
                                                                                $billing_cycle = '1';
                                                                                break;
                                                                        }
                                                                        $payment_cycles = array(array(
                                                                                'cycle_label' => $planObj->plan_text(false, false),
                                                                                'cycle_amount' => $plan_amount,
                                                                                'billing_cycle' => $billing_cycle,
                                                                                'billing_type' => $recurring_type,
                                                                                'recurring_time' => $recurring_time,
                                                                                'payment_cycle_order' => 1,
                                                                        ));
                                                                        $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if ($planObj->is_recurring()) {
                                                                if (!empty($plan_options['payment_cycles'])) {
                                                                    $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                } else {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                }
                                                            }
                                                        }

                                                        $payment_type = $planObj->payment_type;
                                                        $is_trial = '0';
                                                        $trial_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, 0);
                                                        if ($planObj->is_recurring()) {
                                                            $stripePlans = (isset($modules['stripe_plans']) && !empty($modules['stripe_plans'])) ? $modules['stripe_plans'] : array();

                                                            if ($planObj->has_trial_period()) {
                                                                $is_trial = '1';
                                                                $trial_amount = !empty($plan_options['trial']['amount']) ?
                                                                        $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                                if (is_user_logged_in()) {
                                                                    if (!empty($current_user_plan_ids)) {
                                                                        if (in_array($planObj->ID, $current_user_plan_ids)) {
                                                                            $is_trial = '0';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if (!$planObj->is_free()) {
                                                            $trial_amount = !empty($plan_options['trial']['amount']) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                        }

                                                        $allowed_payment_gateways_['paypal'] = "1";
                                                        $allowed_payment_gateways_['stripe'] = "1";
                                                        $allowed_payment_gateways_['bank_transfer'] = "1";
                                                        $allowed_payment_gateways_['2checkout'] = "1";
                                                        $allowed_payment_gateways_['authorize_net'] = "1";
                                                        $allowed_payment_gateways_ = apply_filters('arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options);

                                                        $data_allowed_payment_gateways = json_encode($allowed_payment_gateways_);

                                                        $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, $planObj->amount);
                                                        $arm_plan_amount = $planObj->amount = apply_filters('arm_modify_secondary_amount_outside',  $arm_plan_amount, $plan_data);
                                                        $planInputAttr = ' data-type="' . $plan_type . '" data-plan_name="' . $planObj->name . '" data-amt="' . $arm_plan_amount . '" data-recurring="' . $payment_type . '" data-is_trial="' . $is_trial . '" data-trial_amt="' . $trial_amount . '" data-allowed_gateways=\'' . $data_allowed_payment_gateways . '\' data-plan_text="' . htmlentities($planText) . '"';

                                                        $count_total_cycle = 0;
                                                        if($planObj->is_recurring()){

                                                          $count_total_cycle = count($plan_payment_cycles[$plan_id]);
                                                          $planInputAttr .= '  " data-cycle="'.$count_total_cycle .'" data-cycle_label="'. $plan_payment_cycles[$plan_id][0]['cycle_label'] .'"';
                                                        }
                                                        else{
                                                          $planInputAttr .= " data-cycle='0' data-cycle_label=''";
                                                        }
                                                        
                                                        $planInputAttr .= " data-tax='".$tax_percentage."'";

                                                        
                                                        $module_content .='<md-option value="' . $plan_id . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $planInputAttr . ' ' . $plan_checked . '>' . $planObj->name . ' (' . $planObj->plan_price(false) . ')</md-option>';
                                                        $i++;
                                                    }
                                                }
                                            }
                                            $module_content .= '</md-select>';
                                            $module_content .= '</div></div>';
                                        } else {

                                          if($setup_style['plan_skin']  != 'skin6') {
                                            $module_content .= '<ul class="arm_module_plans_ul arm_column_' . $column_type . '" style="text-align:' . $formPosition . ';">';
                                          }
                                          else{
                                            $module_content .= '<ul class="arm_module_plans_ul arm_column_1" style="text-align:' . $formPosition . ';">';
                                          }

                                              if(empty($plans)){
                                              return;
                                             }
                                            $i = 0;
                                            foreach ($plans as $plan_id) {
                                                if (isset($all_active_plans[$plan_id])) {
                                                    $plan_data = $all_active_plans[$plan_id];
                                                    $planObj = new ARM_Plan(0);
                                                    $planObj->init((object) $plan_data);
                                                    $plan_type = $planObj->type;
                                                    $planText = $planObj->setup_plan_text();
                                                    if ($planObj->exists()) {
                                                        /* Checked Plan Radio According Settings. */
                                                        $plan_checked = $plan_checked_class = '';
                                                        if (!empty($selected_plan_id) && $selected_plan_id != 0 && in_array($selected_plan_id, $plans)) {
                                                            if ($selected_plan_id == $plan_id) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'checked="checked"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        } else {
                                                            if ($i == 0) {
                                                                $plan_checked_class = 'arm_active';
                                                                $plan_checked = 'checked="checked"';
                                                                $selected_plan_data = $plan_data;
                                                            }
                                                        }
                                                        /* Check Recurring Details */
                                                        $plan_options = $planObj->options;

                                                        if (is_user_logged_in()) {
                                                            if ($arm_user_old_plan == $plan_id) {
                                                                $arm_user_payment_cycles = (isset($arm_user_old_plan_options['payment_cycles']) && !empty($arm_user_old_plan_options['payment_cycles'])) ? $arm_user_old_plan_options['payment_cycles'] : array();
                                                                if (empty($arm_user_payment_cycles)) {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($arm_user_old_plan_options['recurring']['time']) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($arm_user_old_plan_options['recurring']['type']) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['days']) ? $arm_user_old_plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['months']) ? $arm_user_old_plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($arm_user_old_plan_options['recurring']['years']) ? $arm_user_old_plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                } else {

                                                                    if (($completed_recurrence == $total_recurring && $total_recurring!="infinite" ) || ($completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription')) {

                                                                        $arm_user_new_payment_cycles = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_new_payment_cycles;
                                                                    } else {
                                                                        $plan_payment_cycles[$plan_id] = $arm_user_payment_cycles;
                                                                    }
                                                                }
                                                            } else {
                                                                if ($planObj->is_recurring()) {
                                                                    if (!empty($plan_options['payment_cycles'])) {
                                                                        $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                    } else {

                                                                        $plan_amount = $planObj->amount;
                                                                        $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                        $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                        switch ($recurring_type) {
                                                                            case 'D':
                                                                                $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                                break;
                                                                            case 'M':
                                                                                $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                                break;
                                                                            case 'Y':
                                                                                $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                                break;
                                                                            default:
                                                                                $billing_cycle = '1';
                                                                                break;
                                                                        }
                                                                        $payment_cycles = array(array(
                                                                                'cycle_label' => $planObj->plan_text(false, false),
                                                                                'cycle_amount' => $plan_amount,
                                                                                'billing_cycle' => $billing_cycle,
                                                                                'billing_type' => $recurring_type,
                                                                                'recurring_time' => $recurring_time,
                                                                                'payment_cycle_order' => 1,
                                                                        ));
                                                                        $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if ($planObj->is_recurring()) {
                                                                if (!empty($plan_options['payment_cycles'])) {
                                                                    $plan_payment_cycles[$plan_id] = $plan_options['payment_cycles'];
                                                                } else {
                                                                    $plan_amount = $planObj->amount;
                                                                    $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                                                                    $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                                                                    switch ($recurring_type) {
                                                                        case 'D':
                                                                            $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                                                            break;
                                                                        case 'M':
                                                                            $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                                                            break;
                                                                        case 'Y':
                                                                            $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                                                            break;
                                                                        default:
                                                                            $billing_cycle = '1';
                                                                            break;
                                                                    }
                                                                    $payment_cycles = array(array(
                                                                            'cycle_label' => $planObj->plan_text(false, false),
                                                                            'cycle_amount' => $plan_amount,
                                                                            'billing_cycle' => $billing_cycle,
                                                                            'billing_type' => $recurring_type,
                                                                            'recurring_time' => $recurring_time,
                                                                            'payment_cycle_order' => 1,
                                                                    ));
                                                                    $plan_payment_cycles[$plan_id] = $payment_cycles;
                                                                }
                                                            }
                                                        }

                                                        $payment_type = $planObj->payment_type;

                                                        $is_trial = '0';
                                                        $trial_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, 0);

                                                        if ($planObj->is_recurring()) {
                                                            $stripePlans = (isset($modules['stripe_plans']) && !empty($modules['stripe_plans'])) ? $modules['stripe_plans'] : array();

                                                            if ($planObj->has_trial_period()) {
                                                                $is_trial = '1';
                                                                $trial_amount = !empty($plan_options['trial']['amount']) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $plan_options['trial']['amount']) : $trial_amount;
                                                                if (is_user_logged_in()) {
                                                                    if (!empty($current_user_plan_ids)) {
                                                                        if (in_array($planObj->ID, $current_user_plan_ids)) {
                                                                            $is_trial = '0';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        $allowed_payment_gateways_['paypal'] = "1";
                                                        $allowed_payment_gateways_['stripe'] = "1";
                                                        $allowed_payment_gateways_['bank_transfer'] = "1";
                                                        $allowed_payment_gateways_['2checkout'] = "1";
                                                        $allowed_payment_gateways_['authorize_net'] = "1";
                                                        $allowed_payment_gateways_ = apply_filters('arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options);
                                                        $data_allowed_payment_gateways = json_encode($allowed_payment_gateways_);
                                                        $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, $planObj->amount);
                                                        $arm_plan_amount = $planObj->amount = apply_filters('arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data);

                                                        $planInputAttr = ' data-type="' . $plan_type . '" data-plan_name="' . $planObj->name . '" data-amt="' . $arm_plan_amount . '" data-recurring="' . $payment_type . '" data-is_trial="' . $is_trial . '" data-trial_amt="' . $trial_amount . '"  data-allowed_gateways=\'' . $data_allowed_payment_gateways . '\' data-plan_text="' . htmlentities($planText) . '"';

                                                        $count_total_cycle = 0;
                                                        if($planObj->is_recurring()){

                                                          $count_total_cycle = count($plan_payment_cycles[$plan_id]);
                                                          $planInputAttr .= '  " data-cycle="'.$count_total_cycle .'"';
                                                        }
                                                        else{
                                                          $planInputAttr .= " data-cycle='0'";
                                                        }

                                                        
                                                        $planInputAttr .= " data-tax='".$tax_percentage."'";


                                                        if ($setup_style['plan_skin'] == '') {
                                                            $module_content .= '<li class="arm_plan_default_skin arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        }else if ($setup_style['plan_skin'] == 'skin6') {
                                                            $module_content .= '<li class="arm_plan_skin6 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<div class="arm_plan_skin6_left_box"><div class="arm_plan_name_box">';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span></div>';
                                                           if(!empty($planObj->description)){
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
}
                                                             $module_content .= '</div>';


                                                             $module_content .= '<div class="arm_plan_skin6_right_box"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        }   else if ($setup_style['plan_skin'] == 'skin1') {
                                                            $module_content .= '<li class="arm_plan_skin1 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        } else if ($setup_style['plan_skin'] == 'skin3') {
                                                            $module_content .= '<li class="arm_plan_skin3 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            $module_content .= '<div class="arm_plan_name_box"><span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        } else {
                                                            $module_content .= '<li class="arm_plan_skin2 arm_setup_column_item ' . $plan_checked_class . '">';
                                                            $module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . $plan_id . '">';
                                                            
                                                            $module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . $plan_id . '" class="arm_module_plan_input" value="' . $plan_id . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
                                                            $module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
                                                            $module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price(false) . '</span></div>';
                                                            $module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
                                                            /* $module_content .= $setup_info; */
                                                            $module_content .= '</label>';
                                                            $module_content .= '</li>';
                                                        }
                                                        $i++;
                                                    }
                                                }
                                            }
                                            $module_content .= '</ul>';
                                        }
                                        $module_content .= '</div></div>';
                                        $module_content = apply_filters('arm_after_setup_plan_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '<input type="hidden" data-ng-model="arm_form.arm_plan_type" name="arm_plan_type" value="' . ((!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'free' : 'paid') . '">';
                                    }
                                }

                                break;
                            case 'forms':

                                if (!empty($modules['forms']) && $modules['forms'] != 0) {
                                    
                                    if (!empty($form_settings)) {
                                        $form_style_class = 'arm_shortcode_form arm_form_' . $modules['forms'];
                                        $form_style_class .= ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                        $form_style_class .= ($form_settings['style']['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                                        $form_style_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
                                        $form_style_class .= ' armf_layout_' . $form_settings['style']['label_position'];
                                        $form_style_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
                                        $form_style_class .= ($form_settings['style']['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                                        $errPosCCField = !empty($form_settings['style']['validation_position']) ? $form_settings['style']['validation_position'] : 'bottom';
                                        $buttonStyle = (isset($form_settings['style']['button_style']) && !empty($form_settings['style']['button_style'])) ? $form_settings['style']['button_style'] : 'flat';
                                        $btn_style_class = ' arm_btn_style_' . $buttonStyle;

                                        $fieldPosition = !empty($form_settings['style']['field_position']) ? $form_settings['style']['field_position'] : 'left';
                                    }


                                      if($two_step){
                                  $module_content .= '<div class="arm_setup_submit_btn_wrapper ' . $form_style_class . ' arm_setup_two_step_next_wrapper" '.$is_hide_class.'>';
                                  $module_content .=   '<div class="arm_form_field_container arm_form_field_container_submit">';
                                 
                                  $module_content .=   '<div class="arm_form_input_wrapper">';
                                  $module_content .=  '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_setup_form_input_container' . $setupID . '">';
                              
                                
                                  $module_content .=   '<md-button type="button" class="arm_form_field_submit_button arm_material_input ' . $btn_style_class . '" data-id="arm_setup_two_step_next">' . html_entity_decode(stripslashes($next_button_label)) . '</md-button>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';


                                  $module_content .= '<div class="arm_setup_submit_btn_wrapper ' . $form_style_class . ' arm_setup_two_step_previous_wrapper arm_hide" '.$is_hide_class.'>';
                                  $module_content .=   '<div class="arm_form_field_container arm_form_field_container_submit">';
                                 
                                  $module_content .=   '<div class="arm_form_input_wrapper">';
                                  $module_content .=  '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_setup_form_input_container' . $setupID . '">';
                              
                                
                                  $module_content .=   '<md-button type="button" class="arm_form_field_submit_button arm_material_input ' . $btn_style_class . '" data-id="arm_setup_two_step_previous">' . html_entity_decode(stripslashes($previuos_button_label)) . '</md-button>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';
                                  $module_content .=  '</div>';
                                }



                                    if (is_user_logged_in() && !$isPreview) {
                                      $form = new ARM_Form('id', $modules['forms']);
                                      $ref_template = $form->form_detail['arm_ref_template'];
                                        $form_css = $arm_member_forms->arm_ajax_generate_form_styles($modules['forms'], $form_settings, array(), $ref_template);
                                        $formStyle .= $form_css['arm_css'];
                                        $modules['forms'] = 0;
                                        $setupGoogleFonts .= $form_css['arm_link'];

                                        $module_content = apply_filters('arm_before_setup_reg_form_section', $module_content, $setupID, $setup_data, $setupRandomID);
                                        
                                    } else {
                                        $formAttr = '';
                                        if ($isPreview) {
                                            $formAttr = 'preview="true"';
                                        }

                                        
                                        $module_content = apply_filters('arm_before_setup_reg_form_section', $module_content, $setupID, $setup_data, $setupRandomID);
                                        $module_content .= '<div class="arm_module_forms_main_container'.$arm_two_step_class.'"><div class="arm_module_forms_container arm_module_box" data-ng-cloak="">';
                                        $module_content .= do_shortcode('[arm_form id="' . $modules['forms'] . '" setup="true" form_position="' . $formPosition . '" ' . $formAttr . ' setup_form_id="'.$setupRandomID.'"]');
                                        $module_content .= '</div>';
                                        $module_content = apply_filters('arm_after_setup_reg_form_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear"></div></div>';
                                    }
                                } else {
                                    if (!$isPreview) {
                                        /* Hide Setup Form for non-logged in users when there is no form configured */
                                        return '';
                                    }
                                }
                                break;
                            case 'note':
                                if (isset($setup_modules['note']) && !empty($setup_modules['note'])) {
                                    $module_content .= '<div class="arm_module_note_main_container'.$arm_two_step_class.'"><div class="arm_module_note_container arm_module_box">';
                                    $module_content .= apply_filters('the_content', stripslashes($setup_modules['note']));
                                    $module_content .= '</div></div>';
                                }
                                break;
                            case 'payment_cycle':
                                $form_layout = '';
                                if (!empty($form_settings)) {
                                    $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                }
                                $payment_mode = "both";

                                $is_hide_class = '';
                                if ($isHidePlans == true || $is_hide_plan_selection_area == true) {
                                    $is_hide_class = 'style="display:none;"';
                                }
                                $module_content .= '<div class="arm_setup_paymentcyclebox_main_wrapper" '.$is_hide_class.'><div class="arm_setup_paymentcyclebox_wrapper arm_hide">';

                                if (!empty($plan_payment_cycles)) {

                                    foreach ($plan_payment_cycles as $payment_cycle_plan_id => $plan_payment_cycle_data) {

                                        $arm_user_selected_payment_cycle = 0;

                                        if($selected_plan_id == $payment_cycle_plan_id){
                                           $arm_user_selected_payment_cycle = $selected_payment_duration -1;
                                        }

                                        if (in_array($payment_cycle_plan_id, $current_user_plan_ids) ) {
                                          $current_plan_data = get_user_meta($current_user_id, 'arm_user_plan_' . $payment_cycle_plan_id, true);

                                            $arm_user_selected_payment_cycle = (isset($current_plan_data['arm_payment_cycle']) && !empty($current_plan_data['arm_payment_cycle'])) ? $current_plan_data['arm_payment_cycle'] : 0;
                                        }

                                        
                                        if (!empty($plan_payment_cycle_data)) {
                                            $module_content .= '<div class="arm_module_payment_cycle_container arm_module_box arm_payment_cycle_box_' . $payment_cycle_plan_id . ' arm_form_' . $setup_modules['modules']['forms'] . ' ' . $form_layout .' arm_hide">';
                                            if (isset($setup_data['setup_labels']['payment_cycle_section_title']) && !empty($setup_data['setup_labels']['payment_cycle_section_title'])) {
                                                $module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . stripslashes_deep($setup_data['setup_labels']['payment_cycle_section_title']) . '</div>';
                                            } else {
                                                $module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . __('Select Payment Cycle', 'ARMember') . '</div>';
                                            }
                                            $column_type = (!empty($setup_modules['cycle_columns'])) ? $setup_modules['cycle_columns'] : '1';

                                            if (is_array($plan_payment_cycle_data)) {
                                                if (count($plan_payment_cycle_data) <= $arm_user_selected_payment_cycle) {
                                                    $arm_user_selected_payment_cycle_no = 0;
                                                } else {
                                                    $arm_user_selected_payment_cycle_no = $arm_user_selected_payment_cycle;
                                                }

                                                $module_content .= '<input type="hidden" name="arm_payment_cycle_plan_'.$payment_cycle_plan_id.'" data-id="arm_payment_cycle_plan_'.$payment_cycle_plan_id.'" value="'.$arm_user_selected_payment_cycle_no.'">';
                                              }

                                            if($setup_style['plan_skin'] == 'skin5'){
                                              if (is_array($plan_payment_cycle_data)) {

                                                $paymentSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $paymentSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $paymentSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_gateway_dropdown_skin1" style="' . $paymentSkinFloat . '">';
                                            $module_content .= '<div class="payment_gateway_dropdown_skin">';

                                               $module_content .= '<md-select name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input select_skin" data-ng-model="payment_cycle_'.$payment_cycle_plan_id.'" ng-change="armPaymentCycleChange('.$payment_cycle_plan_id.', \'arm_setup_form' . $setupRandomID . '\')">';
                                               
                                              $i = 0;

                                              foreach ($plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data) {

                                                    $pc_checked = $pc_checked_class = '';
                                                    if ($i == $arm_user_selected_payment_cycle_no) {
                                                        $pc_checked = 'selected="selected""';
                                                        $pc_checked_class = 'arm_active';
                                                    }


                                                    $arm_paymentg_cycle_amount = (isset($arm_cycle_data['cycle_amount'])) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $arm_cycle_data['cycle_amount']) : 0;
                                                    $arm_paymentg_cycle_amount = apply_filters('arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data);
                                                    $arm_paymentg_cycle_label = (isset($arm_cycle_data['cycle_label'])) ? $arm_cycle_data['cycle_label'] : '';

                                                    

                                                    $planCycleInputAttr = " data-tax='".$tax_percentage."'";



                                                    $module_content .='<md-option value="' . $arm_cycle_data_key  . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $pc_checked . ' data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '" '.$planCycleInputAttr.' '.$planCycleInputAttr.' data-cycle_label = "'. $arm_paymentg_cycle_label .'">' . $arm_paymentg_cycle_label . '</md-option>';


                                                    $i++;
                                                }
                                                  $module_content .= '</md-select></div></div>';
                                              
                                            }


                                            }else{
                                                $module_content .= '<ul class="arm_module_payment_cycle_ul arm_column_' . $column_type . '">';
                                            $i = 0;

                                            if (is_array($plan_payment_cycle_data)) {
                                                
                                                foreach ($plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data) {

                                                    $pc_checked = $pc_checked_class = '';
                                                    if ($i == $arm_user_selected_payment_cycle_no) {
                                                        $pc_checked = 'checked="checked"';
                                                        $pc_checked_class = 'arm_active';
                                                    }


                                                    $arm_paymentg_cycle_amount = (isset($arm_cycle_data['cycle_amount'])) ? $arm_payment_gateways->arm_amount_set_separator($global_currency, $arm_cycle_data['cycle_amount']) : 0;
                                                    
                                                    $arm_paymentg_cycle_amount = apply_filters('arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data);

                                                    $arm_paymentg_cycle_label = (isset($arm_cycle_data['cycle_label'])) ? $arm_cycle_data['cycle_label'] : '';

                                                   
                                                    $planCycleInputAttr = " data-tax='".$tax_percentage."'";

                                                    $pc_content = '<label class="arm_module_payment_cycle_option">';
                                                    $pc_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                    $pc_content .= '<input type="radio" name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input" value="' . ( $arm_cycle_data_key ) . '" ' . $pc_checked . ' data-ng-model="arm_form.payment_cycle" data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '" '.$planCycleInputAttr.'>';
                                                    
                                                    $pc_content .= '<div class="arm_module_payment_cycle_name"><span class="arm_module_payment_cycle_span">' . $arm_paymentg_cycle_label . '</span></div>';
                                                    $pc_content .= '</label>';

                                                    $module_content .= '<li class="arm_setup_column_item arm_payment_cycle_' . ( $arm_cycle_data_key ) . ' ' . $pc_checked_class . '"  data-plan_id="' . $payment_cycle_plan_id . '">';
                                                    $module_content .= $pc_content;
                                                    $module_content .= '</li>';
                                                    $i++;
                                                }
                                            }
                                            $module_content .= '</ul>';
                                            } 
                                            $module_content .= '</div>';
                                        }
                                    }
                                }
                                $module_content .= '</div></div>';
                                $module_content = apply_filters('arm_after_setup_payment_cycle_section', $module_content, $setupID, $setup_data);

                                

                                break;
                            case 'gateways':

                                $form_layout = '';

                                if (!empty($form_settings)) {

                                    $form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'];
                                }

                                $payment_mode = "both";
                                if (!empty($modules['gateways'])) {
                                    $payment_gateway_skin = (isset($setup_style['gateway_skin']) && $setup_style['gateway_skin'] != '' ) ? $setup_style['gateway_skin'] : 'radio';
                                    $gatewayOrders = array();
                                    $gatewayOrders = (isset($modules['gateways_order']) && !empty($modules['gateways_order'])) ? $modules['gateways_order'] : array();
                                    if (!empty($gatewayOrders)) {
                                        asort($gatewayOrders);
                                    }
                                    $gateways = $this->armSortModuleOrders($modules['gateways'], $gatewayOrders);
                                    if (!empty($gateways)) {
                                        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                                        $is_display_pg = (!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'display:none;' : '';
                                        $module_content .= '<div class="arm_setup_gatewaybox_main_wrapper'.$arm_two_step_class.'"><div class="arm_setup_gatewaybox_wrapper" style="' . $is_display_pg . '">';
                                        if (isset($setup_data['setup_labels']['payment_section_title']) && !empty($setup_data['setup_labels']['payment_section_title'])) {
                                            $module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . stripslashes_deep($setup_data['setup_labels']['payment_section_title']) . '</div>';
                                        }
                                        $module_content .= '<input type="hidden" name="arm_front_gateway_skin_type" data-id="arm_front_gateway_skin_type" value="' . $payment_gateway_skin . '">';
                                        $module_content .= '<div class="arm_module_gateways_container arm_module_box arm_form_' . $setup_modules['modules']['forms'] . ' ' . $form_layout . '">';

                                        $column_type = (!empty($setup_modules['gateways_columns'])) ? $setup_modules['gateways_columns'] : '1';

                                        $doNotDisplayPaymentMode = array('bank_transfer');
                                        $doNotDisplayPaymentMode = apply_filters('arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode);

                                        $pglabels = isset($setup_data['arm_setup_labels']['payment_gateway_labels']) ? $setup_data['arm_setup_labels']['payment_gateway_labels'] : array();

                                        if ($payment_gateway_skin == 'radio') {

                                            $module_content .= '<ul class="arm_module_gateways_ul arm_column_' . $column_type .'" style="text-align:' . $formPosition . ';">';
                                            $i = 0;
                                            $pg_fields = $selectedKey = '';

                                            foreach ($gateways as $pg) {
                                                if (in_array($pg, array_keys($active_gateways))) {
                                                    if (isset($selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period']) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription') {
                                                        
                                                        if ($selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0) {
                                                            // continue;
                                                        }
                                                    }
                                                    if (!in_array($pg, $doNotDisplayPaymentMode)) {
                                                        $payment_mode = $modules['payment_mode'][$pg];
                                                    } else {
                                                        $payment_mode = 'manual_subscription';
                                                    }

                                                    $pg_options = $active_gateways[$pg];
                                                    $pg_checked = $pg_checked_class = '';
                                                    $display_block = 'arm_hide';
                                                    if ($i == 0) {
                                                        $pg_checked = 'checked="checked"';
                                                        $pg_checked_class = 'arm_active';
                                                        $display_block = '';
                                                        $selectedKey = $pg;
                                                    }
                                                    $pg_content = '<label class="arm_module_gateway_option">';
                                                    $pg_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
                                                    $pg_content .= '<input type="radio" name="payment_gateway" class="arm_module_gateway_input" value="' . $pg . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '" data-ng-model="arm_form.payment_gateway">';
                                                    if (!empty($pglabels)) {
                                                      if(isset($pglabels[$pg])){
                                                        $pg_options['gateway_name'] = $pglabels[$pg];
                                                      }
                                                    }
                                                    
                                                    $pg_content .= '<div class="arm_module_gateway_name"><span class="arm_module_gateway_span">' . stripslashes_deep($pg_options['gateway_name']) . '</span></div>';
                                                    $pg_content .= '</label>';
                                                    switch ($pg) {
                                                        case 'paypal':
                                                            break;
                                                        case 'stripe':
                                                            
                                                            $hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $pg, $pg_options );
                                                            if( false == $hide_cc_fields ){
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_stripe ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('stripe', $column_type, $fieldPosition, $errPosCCField);
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                        case 'authorize_net':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_authorize_net ' . $display_block . ' arm_member_form_container">';
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('authorize_net', $column_type, $fieldPosition, $errPosCCField);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        case '2checkout':
                                                            break;
                                                        case 'bank_transfer':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . $display_block . ' arm_member_form_container">';
                                                            if (isset($pg_options['note']) && !empty($pg_options['note'])) {
                                                                $pg_fields .= '<div class="arm_bank_transfer_note_container">' . stripslashes(nl2br($pg_options['note'])) . '</div>';
                                                            }
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form($pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms']);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        default:
                                                            $gateway_fields = apply_filters('arm_membership_setup_gateway_option', '', $pg, $pg_options);
                                                            $pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $pg, $pg_options);
                                                            if ($pgHasCCFields) {
                                                                $gateway_fields .= $arm_payment_gateways->arm_get_credit_card_box($pg, $column_type, $fieldPosition, $errPosCCField);
                                                            }
                                                            if (!empty($gateway_fields)) {
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . $pg . ' ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $gateway_fields;
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                    }
                                                    $module_content .= '<li class="arm_setup_column_item arm_gateway_' . $pg . ' ' . $pg_checked_class . '">';
                                                    $module_content .= $pg_content;
                                                    $module_content .= '</li>';
                                                    $i++;
                                                    $module_content .= "<input type='hidden' name='arm_payment_mode[$pg]'  value='{$payment_mode}' />";
                                                }
                                            }
                                            $module_content .= '</ul>';
                                        } else {
                                            $paymentSkinFloat = "float:none;";
                                            switch ($formPosition) {
                                                case 'left':
                                                    $paymentSkinFloat = "";
                                                    break;
                                                case 'right':
                                                    $paymentSkinFloat = "float:right;";
                                                    break;
                                            }
                                            $module_content .= '<div class="arm_form_input_container arm_container payment_gateway_dropdown_skin1" style="' . $paymentSkinFloat . '">';
                                            $module_content .= '<div class="payment_gateway_dropdown_skin">';
                                            $module_content .= '<md-select name="payment_gateway" class="arm_module_gateway_input select_skin" data-ng-model="payment_gateway" aria-label="gateway" ng-change="armPaymentGatewayChange(\'arm_setup_form' . $setupRandomID . '\')">';
                                            $i = 0;
                                            $pg_fields = $selectedKey = '';
                                            foreach ($gateways as $pg) {
                                                if (in_array($pg, array_keys($active_gateways))) {
                                                    $payment_gateway_name = $pg;
                                                    if (isset($selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period']) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription') {
                                                        
                                                        if ($selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0) {
                                                            // continue;
                                                        }
                                                    }

                                                    if (!in_array($pg, $doNotDisplayPaymentMode)) {
                                                        $payment_mode = $modules['payment_mode'][$pg];
                                                    } else {
                                                        $payment_mode = 'manual_subscription';
                                                    }


                                                    $pg_options = $active_gateways[$pg];
                                                    $pg_checked = $pg_checked_class = '';
                                                    $display_block = 'arm_hide';
                                                    if ($i == 0) {
                                                        $pg_checked = 'selected="selected"';
                                                        $pg_checked_class = 'arm_active';
                                                        $display_block = '';
                                                        $selectedKey = $pg;
                                                    }

                                                    switch ($pg) {
                                                        case 'paypal':
                                                            break;
                                                        case 'stripe':
                                                            $hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $pg, $pg_options );
                                                            if( false == $hide_cc_fields ){
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_stripe ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('stripe', $column_type, $fieldPosition, $errPosCCField);
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                        case 'authorize_net':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_authorize_net ' . $display_block . ' arm_member_form_container">';
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_credit_card_box('authorize_net', $column_type, $fieldPosition, $errPosCCField);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        case '2checkout':
                                                            break;
                                                        case 'bank_transfer':
                                                            $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . $display_block . ' arm_member_form_container">';
                                                            if (isset($pg_options['note']) && !empty($pg_options['note'])) {
                                                                $pg_fields .= '<div class="arm_bank_transfer_note_container">' . stripslashes(nl2br($pg_options['note'])) . '</div>';
                                                            }
                                                            $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                            $pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form($pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms']);
                                                            $pg_fields .= '</div>';
                                                            $pg_fields .= '</div>';
                                                            break;
                                                        default:
                                                            $gateway_fields = apply_filters('arm_membership_setup_gateway_option', '', $pg, $pg_options);
                                                            $pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $pg, $pg_options);
                                                            if ($pgHasCCFields) {
                                                                $gateway_fields .= $arm_payment_gateways->arm_get_credit_card_box($pg, $column_type, $fieldPosition, $errPosCCField);
                                                            }
                                                            if (!empty($gateway_fields)) {
                                                                $pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . $pg . ' ' . $display_block . ' arm_member_form_container">';
                                                                $pg_fields .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                                                $pg_fields .= $gateway_fields;
                                                                $pg_fields .= '</div>';
                                                                $pg_fields .= '</div>';
                                                            }
                                                            break;
                                                    }


                                                    
                                                    if (!empty($pglabels)) {
                                                      if(isset($pglabels[$pg])){
                                                        $pg_options['gateway_name'] = stripslashes_deep($pglabels[$pg]);
                                                      }
                                                    }
                                                    $module_content .='<md-option value="' . $payment_gateway_name . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . ' arm_gateway_' . $payment_gateway_name . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '">' . $pg_options['gateway_name'] . '</md-option>';
                                                    $i++;
                                                    $module_content .= "<input type='hidden' name='arm_payment_mode[$pg]'  value='{$payment_mode}' />";
                                                }
                                            }
                                            $module_content .= '</md-select>';
                                            $module_content .= '</div></div>';
                                        }

                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= $pg_fields;
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '</div>';
                                        $module_content = apply_filters('arm_after_setup_gateway_section', $module_content, $setupID, $setup_data);
                                        $module_content .= '<div class="armclear" data-ng-init="armSetDefaultPaymentGateway(\'' . $selectedKey . '\');"></div>';
                                        $module_content .= '</div></div>';
                                        /* Payment Mode Module */

                                        $arm_automatic_sub_label = (isset($setup_data['setup_labels']['automatic_subscription']) && !empty($setup_data['setup_labels']['automatic_subscription'])) ? stripslashes_deep($setup_data['setup_labels']['automatic_subscription']) : __('Auto Debit Payment', 'ARMember');
                                        $arm_semi_automatic_sub_label = (isset($setup_data['setup_labels']['semi_automatic_subscription']) && !empty($setup_data['setup_labels']['semi_automatic_subscription'])) ? stripslashes_deep($setup_data['setup_labels']['semi_automatic_subscription']) : __('Manual Payment', 'ARMember');
                                        $module_content .= "<div class='arm_payment_mode_main_wrapper".$arm_two_step_class."'><div class='arm_payment_mode_wrapper' id='arm_payment_mode_wrapper' style='text-align:{$formPosition};'>";
                                        $setup_data['setup_labels']['payment_mode_selection'] = (isset($setup_data['setup_labels']['payment_mode_selection']) && !empty($setup_data['setup_labels']['payment_mode_selection'])) ? $setup_data['setup_labels']['payment_mode_selection'] : __('How you want to pay?', 'ARMember');
                                        $module_content .= "<div class='arm_setup_section_title_wrapper arm_payment_mode_selection_wrapper' >" . stripslashes_deep($setup_data['setup_labels']['payment_mode_selection']) . "</div>";
                                        $module_content .= "<div class='arm_radio_outer_wrapper'><div class='arm_radio_wrapper'><input type='radio' checked='checked' name='arm_selected_payment_mode' value='auto_debit_subscription' class='arm_selected_payment_mode' id='arm_selected_payment_mode_auto_{$setupRandomID}'/><span></span></div><label for='arm_selected_payment_mode_auto_{$setupRandomID}' style='cursor:pointer;'>&nbsp;<span class='arm_payment_mode_label'>" . $arm_automatic_sub_label . "</span></label></div>";
                                        $module_content .= "<div class='arm_radio_outer_wrapper' style='margin-left: 30px;'><div class='arm_radio_wrapper'><input type='radio'  name='arm_selected_payment_mode' value='manual_subscription' class='arm_selected_payment_mode' id='arm_selected_payment_mode_semi_auto_{$setupRandomID}'/><span></span></div><label for='arm_selected_payment_mode_semi_auto_{$setupRandomID}' style='cursor:pointer;'>&nbsp;<span class='arm_payment_mode_label'>" . $arm_semi_automatic_sub_label . "</span></label></div>";
                                        $module_content .= "";
                                        $module_content .= "</div></div>";
                                    }
                                }
                                break;
                            case 'order_detail':
                                if (!empty($modules['plans'])) {
                                    if ($arm_manage_coupons->isCouponFeature && !empty($modules['coupons']) && $modules['coupons'] == '1') {
                                        $labels = array(
                                            'title' => (!empty($button_labels['coupon_title'])) ? $button_labels['coupon_title'] : '',
                                            'button' => (!empty($button_labels['coupon_button'])) ? $button_labels['coupon_button'] : '',
                                        );
                                        $is_used_as_invitation_code = (isset($setup_modules['modules']['coupon_as_invitation']) && $setup_modules['modules']['coupon_as_invitation'] == 1) ? true : false;

                                        $module_content .= '<div class="arm_setup_couponbox_main_wrapper'.$arm_two_step_class.'"><div class="arm_setup_couponbox_wrapper">';
                                        if (isset($button_labels['coupon_title']) && !empty($button_labels['coupon_title'])) {
                                            $module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . stripslashes_deep($button_labels['coupon_title']) . '</div>';
                                        }
                                        $module_content .= '<div class="arm_module_coupons_container arm_module_box">';
                                        $is_display_coupons = (!empty($selected_plan_data['arm_subscription_plan_type']) && $selected_plan_data['arm_subscription_plan_type'] == 'free') ? 'display:none;' : '';
                                        $module_content .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                                        $module_content .= '<div class="arm_form_inner_container arm_msg_pos_' . $errPosCCField . '" style="padding: 0 !important;">';
                                        $module_content .= '<div class="arm_coupon_fields arm_form_wrapper_container">';
                                        $module_content .= $arm_manage_coupons->arm_redeem_coupon_html('', $labels, $selected_plan_data, $btn_style_class, $is_used_as_invitation_code, $setupRandomID, $formPosition);

                                        $module_content .= '</div>';
                                        $module_content .= '</div>';
                                        $module_content .= '</div>';
                                        $module_content .= '<div class="armclear"></div>';
                                        $module_content .= '</div>';
                                        $module_content .= '</div></div>';
                                    }
                                    $module_content = apply_filters('arm_after_setup_order_detail', $module_content, $setupID, $setup_data);
                                    if (isset($setup_data['setup_labels']['summary_text']) && !empty($setup_data['setup_labels']['summary_text'])) {
                                        $setupSummaryText = stripslashes($setup_data['setup_labels']['summary_text']);
                                        $setupSummaryText = str_replace('[PLAN_NAME]', '<span class="arm_plan_name_text"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PLAN_CYCLE_NAME]', '<span class="arm_plan_cycle_name_text"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PLAN_AMOUNT]', '<span class="arm_plan_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[DISCOUNT_AMOUNT]', '<span class="arm_discount_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[PAYABLE_AMOUNT]', '<span class="arm_payable_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TAX_AMOUNT]', '<span class="arm_tax_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TAX_PERCENTAGE]', '<span class="arm_tax_percentage_text">'.$tax_percentage.'</span>%', $setupSummaryText);
                                        $setupSummaryText = str_replace('[TRIAL_AMOUNT]', '<span class="arm_trial_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText);
                                        $module_content .= "<div class='arm_setup_summary_text_main_container".$arm_two_step_class."'><div class='arm_setup_summary_text_container arm_module_box' style='text-align:{$formPosition};'>";
                                        $module_content .= '<input type="hidden" name="arm_total_payable_amount" data-id="arm_total_payable_amount" ng-model="arm_total_payable_amount" value=""/>';
                                        $module_content .= '<input type="hidden" name="arm_zero_amount_discount" data-id="arm_zero_amount_discount" ng-model="arm_zero_amount_discount" value="' . $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency) . '"/>';

                                        $setupSummaryText = apply_filters('arm_summary_text_filter', $setupSummaryText);

                                        $module_content .= '<div class="arm_setup_summary_text">' . nl2br($setupSummaryText) . '</div>';
                                        $module_content .= '</div></div>';
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                        $module_html .= $module_content;
                    }

                    $content = apply_filters('arm_before_setup_form_content', $content, $setupID, $setup_data);
                    $content .= '<div class="arm_setup_form_container">';
                    $content .= '<style type="text/css" id="arm_setup_style_' . $args['id'] . '">';
                    if (!empty($setup_style)) {
                        $sfontFamily = isset($setup_style['font_family']) ? $setup_style['font_family'] : '';
                        $gFontUrl = $arm_member_forms->arm_get_google_fonts_url(array($sfontFamily));
                        if (!empty($gFontUrl)) {
                            //$setupGoogleFonts .= '<link id="google-font-' . $setupID . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
                            wp_enqueue_style( 'google-font-'.$setupID, $gFontUrl, array(), MEMBERSHIP_VERSION );
                        }
                        $content .= $this->arm_generate_setup_style($setupID, $setup_style);
                    }
                    if (!empty($formStyle)) {
                        $content .= $formStyle;
                    }
                    if (!empty($custom_css)) {
                        $content .= $custom_css;
                    }
                    $content .= '</style>';
                    $content .= $setupGoogleFonts;
                    $content .= '<div class="arm_setup_messages arm_form_message_container"></div>';
                    $form_attr = ' data-ng-controller="ARMCtrl" data-ng-submit="armSetupFormSubmit(arm_form.$valid, \'arm_setup_form' . $setupRandomID . '\', $event);" onsubmit="return false;"';
                    $is_form_class_rtl = '';
                    if (is_rtl()) {
                        $is_form_class_rtl = 'is_form_class_rtl';
                    }
                    $captcha_code = arm_generate_captcha_code();
                    if (!isset($_SESSION['ARM_FILTER_INPUT'])) {
                        $_SESSION['ARM_FILTER_INPUT'] = array();
                    }
                    if (isset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID])) {
                        unset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID]);
                    }
                    $_SESSION['ARM_FILTER_INPUT'][$setupRandomID] = $captcha_code;
                    $_SESSION['ARM_VALIDATE_SCRIPT'] = true;
                    $form_attr .= ' data-submission-key="' . $captcha_code . '" ';
                    $content .= '<form method="post" name="arm_form" id="arm_setup_form' . $setupRandomID . '" class="arm_setup_form_' . $setupID . ' arm_membership_setup_form arm_form_' . $modules['forms'] . ' ' . $is_form_class_rtl . '" enctype="multipart/form-data" data-random-id="' . $setupRandomID . '" novalidate ' . $form_attr . '>';
                    if ($args['hide_title'] == false && $args['popup'] == false) {
                        $content .= '<h3 class="arm_setup_form_title">' . $setup_name . '</h3>';
                    }
                    $content .= '<input type="hidden" name="setup_id" value="' . $setupID . '" data-id="arm_setup_id"/>';
                    $content .= '<input type="hidden" name="setup_action" value="membership_setup"/>';
                    $content .= "<input type='text' name='arm_filter_input' data-random-key='{$setupRandomID}' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
                    $content .= '<div class="arm_setup_form_inner_container">';
                    $content .= '<input type="hidden" class="arm_global_currency" value="' . $global_currency . '"/>';
                    //$currency_separators = $arm_payment_gateways->get_currency_separators_standard();
                    $currency_separators = $arm_payment_gateways->get_currency_wise_separator($global_currency);
                    $currency_separators = (!empty($currency_separators)) ? json_encode($currency_separators) : '';
                    
                    $content .= "<input type='hidden' class='arm_global_currency_separators' value='" . $currency_separators . "'/>";

                    /* tax values */
                    if($enable_tax == 1 && !empty($tax_values)) {
                        $content .= "<input type='hidden' name='arm_tax_type' value='".$tax_values["tax_type"]."'/>";
                        if($tax_values["tax_type"] =='country_tax') {
                            $content .= "<input type='hidden' name='arm_country_tax_field' value='".$tax_values["country_tax_field"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_field_opts' value='".$tax_values["country_tax_field_opts_json"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_amount' value='".$tax_values["country_tax_amount_json"]."'/>";
                            $content .= "<input type='hidden' name='arm_country_tax_default_val' value='".$tax_values["tax_percentage"]."'/>";
                        }
                        else {
			    $content .= "<input type='hidden' name='arm_common_tax_amount' value='".$tax_values["tax_percentage"]."'/>";
                        }
                    }
                    /* tax values over */

                    $content .= $module_html;
                    
                    $content .= '<div class="arm_setup_submit_btn_main_wrapper'.$arm_two_step_class.'"><div class="arm_setup_submit_btn_wrapper ' . $form_style_class . '" data-ng-cloak="">';
                    $content .= '<div class="arm_form_field_container arm_form_field_container_submit">';
                    $content .= '<div class="arm_label_input_separator"></div>';
                    $content .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_submit"></div>';
                    $content .= '<div class="arm_form_input_wrapper">';
                    $content .= '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_setup_form_input_container' . $setupID . '">';
                    $ngClick = 'ng-click="armSubmitBtnClick($event)"';
                    if (current_user_can('administrator')) {
                        $ngClick = 'onclick="return false;"';
                    }
                    $content .= '<md-button type="submit" name="ARMSETUPSUBMIT" class="arm_setup_submit_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input ' . $btn_style_class . '" ' . $ngClick . '><span class="arm_spinner">' . file_get_contents(MEMBERSHIP_IMAGES_DIR . "/loader.svg") . '</span>' . html_entity_decode(stripslashes($submit_btn)) . '</md-button>';

                    if (current_user_can('administrator')) {
                        $arm_default_common_messages = $arm_global_settings->arm_default_common_messages();
                        $content .= '<div class="arm_disabled_submission_container">';
                            $content .= '<div class="arm_setup_messages arm_form_message_container">
                                            <div class="arm_error_msg">
                                                <ul><li>'.$arm_default_common_messages['arm_disabled_submission'].'</li></ul>
                                            </div>
                                        </div>';
                        $content .= '</div>';
                    }

                    $content .= '</div>';
                    $content .= '</div>';

                    /*Add login link in signup form*/
                    $login_link_label = (isset($form_settings['login_link_label'])) ? stripslashes($form_settings['login_link_label']) : __('Login', 'ARMember');

                    
                    $show_login_link = (isset($form_settings['show_login_link'])) ? $form_settings['show_login_link'] : 0;
                    
                    if( $show_login_link == '1' && !is_user_logged_in() ) {
		    	$content .= '<div class="arm_reg_links_wrapper arm_reg_options arm_reg_login_links">';
                        global $arm_login_form_popup_ids_arr, $arm_member_forms;

                        if (isset($form_settings['login_link_type']) && $form_settings['login_link_type'] == 'modal') {
                            $default_lf_id = $arm_member_forms->arm_get_default_form_id('login');
                            $lf_id = (isset($form_settings['login_link_type_modal'])) ? $form_settings['login_link_type_modal'] : $default_lf_id;
                            
                            if(array_key_exists($lf_id, $arm_login_form_popup_ids_arr))
                            {
                                $setupRandomID = $arm_login_form_popup_ids_arr[$lf_id];
                            }

                            $loginIdClass = 'arm_reg_form_login_link_' . $setupRandomID;
                            $content .= '<input type="hidden" name="arm_signup_login_form" value="'. $loginIdClass .'">';

                            if(!array_key_exists($lf_id, $arm_login_form_popup_ids_arr))
                            {
                                $arm_login_form_popup_ids_arr[$lf_id] = $setupRandomID;
                                $content .= do_shortcode("[arm_form id='".$lf_id."' popup='true' link_title=' ' link_class='arm_reg_form_other_links ".$loginIdClass."']");
                            }
                            else
                            {
                                $content .= "[arm_form id='".$lf_id."' popup='true' link_title=' ' link_class='arm_reg_form_other_links ".$loginIdClass."']";
                            }


                            $login_link_label = $arm_member_forms->arm_parse_login_links($login_link_label, 'javascript:void(0)', 'arm_reg_popup_form_links arm_form_popup_ahref', 'data-form_id="' . $loginIdClass . '" data-toggle="armmodal"');
                            $content .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
                        } else {
                            $loginLinkPageID = (isset($form_settings['login_link_type_page'])) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('login_page_id', 0);
                            $loginLinkHref = $arm_global_settings->arm_get_permalink('', $loginLinkPageID);
                            $login_link_label = $arm_member_forms->arm_parse_login_links($login_link_label, $loginLinkHref);
                            $content .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
                        }
			$content .= '<div class="armclear"></div>';
			$content .= "</div>";
			$content .= '<div class="armclear"></div>';
                    }
                    

                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div></div>';
                    $content .= '</form></div>';

                    if ($args['popup'] !== false) {
                        $popup_content = '<div class="arm_setup_form_popup_container">';
                        $link_title = (!empty($args['link_title'])) ? $args['link_title'] : $setup_name;
                        $link_style = $link_hover_style = '';
                        $popup_content .= '<style type="text/css">';
                        if (!empty($args['link_css'])) {
                            $link_style = esc_html($args['link_css']);
                            $popup_content .= '.arm_setup_form_popup_link_' . $setupID . '{' . $link_style . '}';
                        }
                        if (!empty($args['link_hover_css'])) {
                            $link_hover_style = esc_html($args['link_hover_css']);
                            $popup_content .= '.arm_setup_form_popup_link_' . $setupID . ':hover{' . $link_hover_style . '}';
                        }
                        $popup_content .= '</style>';
                        $pformRandomID = $setupID . '_popup_' . arm_generate_random_code();
                        $popupLinkID = 'arm_setup_form_popup_link_' . $setupID;
                        $popupLinkClass = 'arm_setup_form_popup_link arm_setup_form_popup_link_' . $setupID;
                        if (!empty($args['link_class'])) {
                            $popupLinkClass.=" " . esc_html($args['link_class']);
                        }
                        $popupLinkAttr = 'data-form_id="' . $pformRandomID . '" data-toggle="armmodal"  data-modal_bg="' . $args['modal_bgcolor'] . '" data-overlay="' . $args['overlay'] . '"';
                        if (!empty($args['link_type']) && strtolower($args['link_type']) == 'button') {
                            $popup_content .= '<button type="button" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_setup_form_popup_button" ' . $popupLinkAttr . '>' . $link_title . '</button>';
                        } else {
                            $popup_content .= '<a href="javascript:void(0)" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_setup_form_popup_ahref" ' . $popupLinkAttr . '>' . $link_title . '</a>';
                        }
                        $popup_style = $popup_content_height = '';
                        $popupHeight = 'auto';
                        $popupWidth = '500';
                        if (!empty($args['popup_height'])) {
                            if ($args['popup_height'] == 'auto') {
                                $popup_style .= 'height: auto;';
                            } else {
                                $popup_style .= 'overflow: hidden;height: ' . $args['popup_height'] . 'px;';
                                $popupHeight = ($args['popup_height'] - 70) . 'px';
                                $popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ($args['popup_height'] - 70) . 'px;';
                            }
                        }
                        if (!empty($args['popup_width'])) {
                            if ($args['popup_width'] == 'auto') {
                                $popup_style .= '';
                            } else {
                                $popupWidth = $args['popup_width'];
                                $popup_style .= 'width: ' . $args['popup_width'] . 'px;';
                            }
                        }
                        $popup_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_setup_form arm_popup_member_setup_form_' . $setupID . ' arm_popup_member_setup_form_' . $pformRandomID . '" style="' . $popup_style . '" data-width="' . $popupWidth . '"><div class="popup_setup_inner_container popup_wrapper_inner">';
                        $popup_content .= '<div class="popup_header">';
                        $popup_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
                        $popup_content .= '<div class="popup_header_text arm_setup_form_heading_container">';
                        if ($args['hide_title'] == false) {
                            $popup_content .= '<span class="arm_setup_form_field_label_wrapper_text">' . $setup_name . '</span>';
                        }
                        $popup_content .= '</div>';
                        $popup_content .= '</div>';
                        $popup_content .= '<div class="popup_content_text" style="' . $popup_content_height . '" data-height="' . $popupHeight . '">';
                        $popup_content .= $content;
                        $popup_content .= '</div><div class="armclear"></div>';
                        $popup_content .= '</div></div>';
                        $popup_content .= '</div>';
                        $content = $popup_content;
                        $content .= '<div class="armclear">&nbsp;</div>';
                    }
                    $content = apply_filters('arm_after_setup_form_content', $content, $setupID, $setup_data);
                }
            }
            $ARMember->arm_check_font_awesome_icons($content);
            $ARMember->enqueue_angular_script(true);
            
            $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
            if($isEnqueueAll == '1'){
                $content .= '<script type="text/javascript" data-cfasync="false">
                                    jQuery(document).ready(function (){
                            
                                    setTimeout(function () {
                                        jQuery(".arm_setup_form_container").show();
                                    }, 100);
                                    
                                    setTimeout(function () {
                                        arm_current_membership_init();
                                        arm_transaction_init();
                                        arm_tooltip_init();
                                        arm_set_plan_width();
                                        arm_do_bootstrap_angular();
                                        arm_equal_hight_setup_plan();
                                        jQuery("input.arm_module_plan_input").trigger("change");
                                    }, 500);                        
                                }); ';
                                    
                $content .= '</script>';
            }
                        
            $inbuild = '';
            $hiddenvalue = '';
            global $arm_members_activity, $arm_version;
            $arm_request_version = get_bloginfo('version');
            $setact = 0;
            global $check_version;
            $setact = $arm_members_activity->$check_version();

            if($setact != 1)
                $inbuild = " (U)";

            $hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option('arm_version') . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';

            return do_shortcode($content.$hiddenvalue);
        }

        function arm_get_sales_tax($general_settings, $post_data = '', $user_id = 0, $form_id = 0) {

            $return_arr = array(
                "tax_type" => 'common_tax',
                "country_tax_field" => '',
                "country_tax_field_opts_json" => '',
                "country_tax_amount_json" => '',
                "tax_percentage" => '',
            );

            $tax_type = isset($general_settings['tax_type']) ? $general_settings['tax_type'] : 'common_tax';
            $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';

            if($tax_type == 'country_tax') {

                $tax_percentage = !empty($general_settings['arm_country_tax_default_val']) ? $general_settings['arm_country_tax_default_val'] : 0;

                if(!empty($general_settings['arm_tax_country_name']) && $country_tax_field != '') {

                    $country_tax_field_opts = isset($general_settings['arm_tax_country_name']) ? $general_settings['arm_tax_country_name'] : '';

                    if(!empty($country_tax_field_opts)) {
                        $country_tax_amount = isset($general_settings['arm_country_tax_val']) ? $general_settings['arm_country_tax_val'] : '';
                        if(!empty($country_tax_amount)) {
                            $country_tax_amount = maybe_unserialize($country_tax_amount);
                            $country_tax_field_opts = maybe_unserialize($country_tax_field_opts);
                            $return_arr["tax_type"] = $tax_type;
                            $return_arr["country_tax_field"] = $country_tax_field;
                            $return_arr["country_tax_field_opts_json"] = json_encode($country_tax_field_opts);
                            $return_arr["country_tax_amount_json"] = json_encode($country_tax_amount);

                            if(is_user_logged_in() && !empty($user_id)) {
                                global $wpdb;
                                $user_country = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key = '".$country_tax_field."'");
                                if(!empty($user_country) && in_array($user_country, $country_tax_field_opts)) {
                                    $opt_index = array_search($user_country, $country_tax_field_opts);
                                    $tax_percentage = $country_tax_amount[$opt_index];
                                }
                            }
                            else if(!empty($post_data) && isset($post_data[$country_tax_field]) && in_array($post_data[$country_tax_field], $country_tax_field_opts)) {
                                $opt_index = array_search($post_data[$country_tax_field], $country_tax_field_opts);
                                $tax_percentage = $country_tax_amount[$opt_index];
                            }
                            else if(!empty($form_id) && ctype_digit($form_id)) {
                                global $arm_member_forms;
                                $form_field_opt = $arm_member_forms->arm_get_field_option_by_meta($country_tax_field, $form_id);
                                if(!empty($form_field_opt) && !empty($form_field_opt["default_val"]) ) {
                                    $default_opt = $form_field_opt["default_val"];
                                    if(in_array($default_opt, $country_tax_field_opts)) {
                                        $opt_index = array_search($user_country, $country_tax_field_opts);
                                        $tax_percentage = $country_tax_amount[$opt_index];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else {
                $tax_percentage = isset($general_settings['tax_amount']) ? $general_settings['tax_amount'] : 0;
            }

            $return_arr["tax_percentage"] = $tax_percentage;

            return $return_arr;
        }

        function arm_generate_setup_style($setupid = 0, $setup_style = array()) {
            $defaultStyle = array(
                'content_width' => '800',
                'plan_skin' => '',
                'font_family' => 'Helvetica',
                'title_font_size' => 24,
                'title_font_bold' => 1,
                'title_font_italic' => '',
                'title_font_decoration' => '',
                'description_font_size' => 16,
                'description_font_bold' => 0,
                'description_font_italic' => '',
                'description_font_decoration' => '',
                'price_font_size' => 30,
                'price_font_bold' => 1,
                'price_font_italic' => '',
                'price_font_decoration' => '',
                'summary_font_size' => 15,
                'summary_font_bold' => 0,
                'summary_font_italic' => '',
                'summary_font_decoration' => '',
                'plan_title_font_color' => '#504f51',
                'plan_desc_font_color' => '#504f51',
                'price_font_color' => '#504f51',
                'summary_font_color' => '#504f51',
                'bg_active_color' => '#39a5ff',
                'selected_plan_title_font_color' => '#000000',
                'selected_plan_desc_font_color' => '#000000',
                'selected_price_font_color' => '#000000',
            );
            $setup_style = shortcode_atts($defaultStyle, $setup_style);

            $summary_font_style = (isset($setup_style['summary_font_bold']) && $setup_style['summary_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
            $summary_font_style .= (isset($setup_style['summary_font_italic']) && $setup_style['summary_font_italic'] == '1') ? "font-style: italic;" : "";
            $summary_font_style .= (isset($setup_style['summary_font_decoration']) && !empty($setup_style['summary_font_decoration'])) ? "text-decoration: " . $setup_style['summary_font_decoration'] . ";" : "";

            $title_font_style = (isset($setup_style['title_font_bold']) && $setup_style['title_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
            $title_font_style .= (isset($setup_style['title_font_italic']) && $setup_style['title_font_italic'] == '1') ? "font-style: italic;" : "";
            $title_font_style .= (isset($setup_style['title_font_decoration']) && !empty($setup_style['title_font_decoration'])) ? "text-decoration: " . $setup_style['title_font_decoration'] . ";" : "";

            $description_font_style = (isset($setup_style['description_font_bold']) && $setup_style['description_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
            $description_font_style .= (isset($setup_style['description_font_italic']) && $setup_style['description_font_italic'] == '1') ? "font-style: italic;" : "";
            $description_font_style .= (isset($setup_style['description_font_decoration']) && !empty($setup_style['description_font_decoration'])) ? "text-decoration: " . $setup_style['description_font_decoration'] . ";" : "";
            $price_font_style = (isset($setup_style['price_font_bold']) && $setup_style['price_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
            $price_font_style .= (isset($setup_style['price_font_italic']) && $setup_style['price_font_italic'] == '1') ? "font-style: italic;" : "";
            $price_font_style .= (isset($setup_style['price_font_decoration']) && !empty($setup_style['price_font_decoration'])) ? "text-decoration: " . $setup_style['price_font_decoration'] . ";" : "";
            $setup_content_width = ($setup_style['content_width'] == 0 && $setup_style['content_width'] != '') ? '800' : $setup_style['content_width'];
            $setup_content_width = ($setup_content_width == '') ? 'auto' : $setup_content_width.'px';
            $setup_font_family = ($setup_style['font_family'] != 'inherit') ? 'font-family: '.$setup_style['font_family'].', sans-serif, \'Trebuchet MS\';' : '';
            $setup_css = '
                    .arm_setup_form_' . $setupid . '{
                        width: ' . $setup_content_width . ';
                        margin: 0 auto;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_form_title,
                    .arm_setup_form_' . $setupid . ' .arm_setup_section_title_wrapper{
                        ' . $setup_font_family . '
                        font-size: 20px !important;
                        font-size: ' . ($setup_style['title_font_size'] + 2) . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ';
                        font-weight: normal;
                    }
                    
                    .arm_setup_form_' . $setupid . ' .arm_payment_mode_label{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['description_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_desc_font_color'] . ';
                        font-weight : normal;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_gateway_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_payment_cycle_name{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['title_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ' !important;
                        ' . $title_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_name{
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_price{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['price_font_size'] . 'px !important;
                        color: ' . $setup_style['price_font_color'] . ' !important;
                        ' . $price_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_price{
                        color: ' . $setup_style['selected_price_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_description{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['description_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_desc_font_color'] . ';
                        ' . $description_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_description,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_description{
                        color: ' . $setup_style['selected_plan_desc_font_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_summary_text_container .arm_setup_summary_text{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['summary_font_size'] . 'px !important;
                        color: ' . $setup_style['summary_font_color'] . ';
                        ' . $summary_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_gateway_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_gateway_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_payment_cycle_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_payment_cycle_option{
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_gateway_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_payment_cycle_name{
                        font-size: ' . $setup_style['title_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ';
                        ' . $title_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_default_skin.arm_setup_column_item.arm_active .arm_module_plan_option{
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_default_skin.arm_setup_column_item.arm_active .arm_module_plan_option{
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price_type,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type {
                        transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price{
                        color: ' . $setup_style['selected_price_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }

                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                        color: '.$setup_style['selected_plan_title_font_color'].' !important;
                    }

                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_description,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_description{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                       
                        color: '.$setup_style['selected_plan_title_font_color']. ' !important;
                    }


                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name{
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_check_circle{
                        border-color: ' . $setup_style['bg_active_color'] . ' !important;
                        color: ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_radio_wrapper input[type=radio] + span { border-color: ' . $setup_style['bg_active_color'] . ' !important; }
                    .arm_setup_form_' . $setupid . ' .arm_radio_wrapper input[type=radio]:checked + span:before { background: ' . $setup_style['bg_active_color'] . ' !important; }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle i,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option:hover .arm_setup_check_circle i{
                        color:  ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin5 .arm_module_plan_option .arm_setup_check_circle{
                        border-color: ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_module_gateways_container .arm_module_gateway_fields{
                        ' . $setup_font_family . '
                    }
                ';
            return $setup_css;
        }

        function armSortModuleOrders(Array $array, Array $orderArray) {
            $ordered = array();
            if (!empty($array) && !empty($orderArray)) {
                foreach ($array as $key => $val) {
                    if (array_key_exists($val, $orderArray)) {
                        $ordered[$orderArray[$val]] = $val;
                        unset($array[$key]);
                    }
                }
            } else {
                $ordered = $array;
            }
            if (!empty($ordered)) {
                ksort($ordered);
            }
            return $ordered;
        }

        function arm_sort_module_by_order($items = array(), $item_order = array()) {
            $new_items = array();
            if (!empty($items)) {
                if (!empty($item_order)) {
                    asort($item_order);
                    foreach ($item_order as $key => $order) {
                        if (!empty($items[$key])) {
                            $new_items[$key] = $items[$key];
                            unset($items[$key]);
                        }
                    }
                    $new_items = $new_items + $items;
                } else {
                    $new_items = $items;
                }
            }
            return $new_items;
        }

        function arm_refresh_setup_items() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans;
            $module_items = '';
            if (!empty($_POST['module'])) {
                $module_items = $this->arm_get_module_items_box($_POST['module']);
            }
            echo $module_items;
            exit;
        }

        function arm_get_module_items_box($module_type = 'plans', $options = array()) {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans;
            $module_box = '';
            $alertMessages = $ARMember->arm_alert_messages();
            $add_plan_link = admin_url('admin.php?page=' . $arm_slugs->manage_plans . '&action=new');
            $manage_gateway_link = admin_url('admin.php?page=' . $arm_slugs->general_settings . '&&action=payment_options');
            $selected_items = (!empty($options['selected'])) ? $options['selected'] : array();
            $items_order = (!empty($options['items_order'])) ? $options['items_order'] : array();
            $column_type = (!empty($options['column'])) ? $options['column'] : 1;
            $options['coupons'] = (!empty($options['coupons']) && $options['coupons'] == 1) ? $options['coupons'] : 0;
            $input_prefix = 'setup_data[setup_modules]';
            if ($module_type == 'plans') {
                $active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
                $filtered_items = $this->arm_sort_module_by_order($active_plans, $items_order);
                $module_box .= '<div class="arm_setup_items_box_plans">';
                if (!empty($filtered_items)) {
                    $module_box .= '<div class="arm_setup_module_column_layout_types arm_setup_plans_column_layout_types">';
                    $module_box .= '<div class="arm_column_layout_types_container">';
                    $column1_class = ($column_type == 1) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column1_class . '"><span class="single_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="1" class="arm_column_layout_type_radio" ' . checked($column_type, '1', false) . ' data-module="plans"><span>' . __('Single Column', 'ARMember') . '</span></label>';
                    $column2_class = ($column_type == 2) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column2_class . '"><span class="two_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="2" class="arm_column_layout_type_radio" ' . checked($column_type, '2', false) . ' data-module="plans"><span>' . __('Two Column', 'ARMember') . '</span></label>';
                    $column3_class = ($column_type == 3) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column3_class . '"><span class="three_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="3" class="arm_column_layout_type_radio" ' . checked($column_type, '3', false) . ' data-module="plans"><span>' . __('Three Column', 'ARMember') . '</span></label>';
                    $module_box .= '<div class="armclear"></div></div>';
                    $module_box .= '</div>';
                    $module_box .= '<ul class="arm_setup_plans_ul arm_membership_setup_sub_ul arm_column_' . $column_type . '">';
                    $pi = 1;
                    foreach ($filtered_items as $plan) {
                        $planObj = new ARM_Plan(0);
                        $planObj->init((object) $plan);
                        $plan_id = $planObj->ID;
                        $plan_options = $planObj->options;
                        /* Check Recurring Details */
                        $bank_allow = ($planObj->is_recurring()) ? '0' : '1';
                        $auth_allow = $planObj->is_support_authorize_net();
                        $auth_allow = ($auth_allow) ? '1' : '0';
                        $twoco_allow = $planObj->is_support_2checkout();
                        $twoco_allow = ($twoco_allow) ? '1' : '0';
                        $planInputAttr = ' data-plan_name="' . $planObj->name . '" data-plan_type="' . $planObj->type . '" data-payment_type="' . $planObj->payment_type . '" data-auth_allow="' . $auth_allow . '" data-bank_allow="' . $bank_allow . '" data-twoco_allow="' . $twoco_allow . '"';
                        $plan_checked = (!empty($selected_items) && in_array($plan_id, $selected_items)) ? 'checked="checked"' : '';
                        $module_box .= '<li class="arm_membership_setup_plans_li arm_membership_setup_sub_li">';
                        $module_box .= '<div class="arm_membership_setup_sortable_icon"></div>';
                        $module_box .= '<label id="label_plan_chk_' . $plan_id . '">';
                        $module_box .= '<input type="checkbox" name="' . $input_prefix . '[modules][plans][]" value="' . $plan_id . '" id="plan_chk_' . $plan_id . '" class="arm_icheckbox plans_chk_inputs plans_chk_inputs_' . $planObj->type . '" ' . $planInputAttr . ' ' . $plan_checked . ' data-msg-required="' . __('Please select atleast one plan.', 'ARMember') . '"/>';
                        $module_box .= '<span>' . $planObj->name . '</span>';
                        $module_box .= '</label>';
                        $module_box .= '<input type="hidden" name="' . $input_prefix . '[modules][plans_order][' . $plan_id . ']" value="' . $pi . '" class="arm_module_options_order">';
                        $module_box .= '</li>';
                        $pi++;
                    }
                    $module_box .= '</ul>';
                } else {
                    $module_box .= '<span class="arm_setup_plan_error_msg error" style="display: none;">' . __('Please select atleast one plan.', 'ARMember') . '</span>';
                    $module_box .= '<a href="javascript:void(0)" class="arm_setup_module_refresh" data-module="plans" title="' . __('Reload Plan List', 'ARMember') . '"><i class="armfa armfa-refresh"></i></a>';
                    $module_box .= '<div class="arm_setup_items_empty_msg">' . __('There is no any plan configured yet.', 'ARMember');
                    $module_box .= ' <a href="' . $add_plan_link . '" target="_blank">' . __('Please click here to add plan.', 'ARMember') . '</a>';
                    $module_box .= ' ' . __('After adding plan, click on refresh button', 'ARMember');
                    $module_box .= ' (<a style="float: none;padding: 3px;" href="javascript:void(0)" class="arm_setup_module_refresh" data-module="plans"><i class="armfa armfa-refresh"></i></a>) ' . __('to get added plans.', 'ARMember');
                    $module_box .= '</div>';
                }
                $module_box .= '</div>';
            } elseif ($module_type == 'gateways') {
                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $filtered_gatways = $this->arm_sort_module_by_order($active_gateways, $items_order);
                $module_box .= '<div class="arm_setup_items_box_gateways">';
                if (!empty($filtered_gatways)) {
                    $module_box .= '<div class="arm_setup_module_column_layout_types arm_setup_gatways_column_layout_types">';
                    $module_box .= '<div class="arm_column_layout_types_container">';
                    $column1_class = ($column_type == 1) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column1_class . '"><span class="single_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="1" class="arm_column_layout_type_radio" ' . checked($column_type, '1', false) . ' data-module="gateways"><span>' . __('Single Column', 'ARMember') . '</span></label>';
                    $column2_class = ($column_type == 2) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column2_class . '"><span class="two_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="2" class="arm_column_layout_type_radio" ' . checked($column_type, '2', false) . ' data-module="gateways"><span>' . __('Two Column', 'ARMember') . '</span></label>';
                    $column3_class = ($column_type == 3) ? 'arm_active_label' : '';
                    $module_box .= '<label class="' . $column3_class . '"><span class="three_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="3" class="arm_column_layout_type_radio" ' . checked($column_type, '3', false) . ' data-module="gateways"><span>' . __('Three Column', 'ARMember') . '</span></label>';
                    $module_box .= '<div class="armclear"></div></div>';
                    $module_box .= '</div>';
                    $module_box .= '<ul class="arm_setup_gateways_ul arm_membership_setup_sub_ul arm_column_' . $column_type . '">';
                    $gi = 1;
                    /* --------------------Strip Plan Box------------------------------------ */
                    $stripePlanIDWarning = $alertMessages['stripePlanIDWarning'];
                    $selected_plan = isset($options['selected_plan']) ? $options['selected_plan'] : array();
                    $stripe_plans = isset($options['stripe_plans']) ? $options['stripe_plans'] : array();
                    $stripe_plan_options = '';
                    $stripeNotPlans = $bankNotPlans = $authNotPlans = $twocoNotPlans = array();
                    $isStripeWarning = $isBTWarning = $isAuthorizeNetWarning = $is2CheckoutWarning = false;
                    if (!empty($selected_plan)) {
                        foreach ($selected_plan as $pID) {
                            $pddata = $arm_subscription_plans->arm_get_subscription_plan($pID, 'arm_subscription_plan_name, arm_subscription_plan_type, arm_subscription_plan_options');
                            $s_plan_name = $pddata['arm_subscription_plan_name'];
                            $plan_type = $pddata['arm_subscription_plan_type'];
                            $plan_options = maybe_unserialize($pddata['arm_subscription_plan_options']);
                            $payment_type = isset($plan_options['payment_type']) ? $plan_options['payment_type'] : '';
                            if ($plan_type == 'paid' && $payment_type == 'subscription') {
                                if (in_array('bank_transfer', $selected_items)) {
                                    $isBTWarning = true;
                                    $bankNotPlans[$pID] = $s_plan_name;
                                }
                                if (!empty($stripe_plans)) {
                                    $stripe_pID = (!empty($stripe_plans[$pID])) ? $stripe_plans[$pID] : '';
                                    $stripe_plan_options .= '<label class="arm_stripe_plans arm_stripe_plan_label_' . $pID . '"><span>' . stripslashes($pddata['arm_subscription_plan_name']) . '</span>';
                                    $stripe_plan_options .= '<input type="text" name="' . $input_prefix . '[modules][stripe_plans][' . $pID . ']" value="' . $stripe_pID . '" placeholder="' . __('Stripe price ID', 'ARMember') . '">';
                                    //$stripe_plan_options .= '<span class="arm_stripe_planid_warning">' . $stripePlanIDWarning . '</span>';
                                    $stripe_plan_options .= '</label>';
                                }
                                switch ($plan_options['recurring']['type']) {
                                    case 'D':
                                        if (in_array('2checkout', $selected_items)) {
                                            $is2CheckoutWarning = true;
                                            $twocoNotPlans[$pID] = $s_plan_name;
                                        }
                                        if (in_array('authorize_net', $selected_items) && $plan_options['recurring']['days'] < 7) {
                                            $isAuthorizeNetWarning = true;
                                            $authNotPlans[$pID] = $s_plan_name;
                                        }
                                        break;
                                    case 'M':
                                        if (in_array('authorize_net', $selected_items) && $plan_options['recurring']['months'] > 12) {
                                            $isAuthorizeNetWarning = true;
                                            $authNotPlans[$pID] = $s_plan_name;
                                        }
                                        break;
                                    default:
                                        break;
                                }
                                $trialOptions = isset($plan_options['trial']) ? $plan_options['trial'] : array();
                                if (isset($trialOptions['is_trial_period']) && $trialOptions['is_trial_period'] == 1) {
                                    if (in_array('stripe', $selectedGateways)) {

                                        if ($trialOptions['amount'] > 0) {
                                            $isStripeWarning = true;
                                            $stripeNotPlans[$pID] = $s_plan_name;
                                        }
                                    }

                                    if (in_array('2checkout', $selected_items)) {
                                        $is2CheckoutWarning = true;
                                        $twocoNotPlans[$pID] = $s_plan_name;
                                    }
                                    if (in_array('authorize_net', $selected_items)) {
                                        $isAuthorizeNetWarning = true;
                                        $authNotPlans[$pID] = $s_plan_name;
                                    }
                                }
                            }
                        }
                    }
                    /* -------------------------------------------------------- */
                    foreach ($filtered_gatways as $key => $pg) {
                        $pgname = $pg['gateway_name'];
                        $gateway_checked = (!empty($selected_items) && in_array($key, $selected_items)) ? 'checked="checked"' : '';
                        $module_box .= '<li class="arm_membership_setup_gateways_li arm_membership_setup_sub_li">';
                        $module_box .= '<div class="arm_membership_setup_sortable_icon"></div>';
                        $module_box .= '<label>';
                        $module_box .= '<input type="checkbox" name="' . $input_prefix . '[modules][gateways][]" value="' . $key . '" id="gateway_chk_' . $key . '" class="arm_icheckbox gateways_chk_inputs" ' . $gateway_checked . ' data-pg_name="' . $pgname . '" data-msg-required="' . __('Please select atleast one payment gateway.', 'ARMember') . '"/>';
                        $module_box .= '<span>' . $pgname . '</span>';
                        $module_box .= '</label>';
                        $module_box .= '<input type="hidden" name="' . $input_prefix . '[modules][gateways_order][' . $key . ']" value="' . $gi . '" class="arm_module_options_order">';
                        if ($key == 'stripe') {
                            $stripe_plan_display = (!empty($stripe_plan_options)) ? 'display:block;' : '';
                            $module_box .= '<div class="arm_stripe_plan_container" style="' . $stripe_plan_display . '">';
                            $stripe_title = __("You must need to add 'Product' for recurring plans", 'ARMember') . "<br/>" . __("You can view / create product easily via the", 'ARMember') . " <a href='https://dashboard.stripe.com/subscriptions/products'>" . __('Products', 'ARMember') . "</a> " . __("page of the Stripe dashboard. After that you need to click on created 'Product' and then at 'Pricing' section Add/Edit Price(s) and after that you can get 'ID' of stripe price", 'ARMember');
                            $module_box .= '<h4>' . __('Stripe Prices', 'ARMember') . '<i class="arm_helptip_icon armfa armfa-question-circle" title="' . $stripe_title . '"></i></h4>';
                            $module_box .= $stripe_plan_options;
                            $module_box .= '</div>';
                        }
                        $module_box .= '</li>';
                        $gi++;
                    }
                    $module_box .= '</ul>';
                    $module_box .= '<div class="armclear"></div>';
                    $module_box .= '<div class="arm_payment_gateway_warnings">';
                    $module_box .= '<span class="arm_invalid" id="arm_2checkout_warning" style="' . ($is2CheckoutWarning ? '' : 'display:none;') . '"><span class="arm_2checkout_not_support_plans">' . (implode(',', $twocoNotPlans)) . '</span> ' . __("plan's configuration is not supported by 2Checkout. So it will be hide as a payment option when user will select this plan(s).", 'ARMember') . '</span>';
                    $module_box .= '<span class="arm_invalid" id="arm_stripe_warning" style="' . ($isStripeWarning ? '' : 'display:none;') . '"><span class="arm_stripe_not_support_plans">' . (implode(',', $stripeNotPlans)) . '</span> ' . __("plan's configuration is not supported by Stripe. So it will be hide as a payment option when user will select this plan(s).", 'ARMember') . '</span>';

                    $module_box .= '<span class="arm_invalid" id="arm_authorize_net_warning" style="' . ($isAuthorizeNetWarning ? '' : 'display:none;') . '"><span class="arm_authorize_net_not_support_plans">' . (implode(',', $authNotPlans)) . '</span> ' . __("plan's configuration is not supported by Authorize.Net. So it will be hide as a payment option when user will select this plan(s).", 'ARMember') . '</span>';
                    $module_box .= '<span class="arm_invalid" id="arm_bank_transfer_warning" style="' . ($isBTWarning ? '' : 'display:none;') . '"><span class="arm_bank_transfer_not_support_plans">' . (implode(',', $bankNotPlans)) . '</span> ' . __("plan's configuration is not supported by Bank Transfer. So it will be hide as a payment option when user will select this plan(s).", 'ARMember') . '</span>';
                    $module_box .= '</div>';
                    $module_box .= '<div class="armclear"></div>';
                } else {
                    $module_box .= '<span class="arm_setup_gateway_error_msg error" style="display: none;">' . __('Payment gateway is required for paid plans.', 'ARMember') . '</span>';
                    $module_box .= '<a href="javascript:void(0)" class="arm_setup_module_refresh" data-module="gateways" title="' . __('Reload Payment Gateway List', 'ARMember') . '"><i class="armfa armfa-refresh"></i></a>';
                    $module_box .= '<div class="arm_setup_items_empty_msg">' . __('There is no any payment gateway configured yet.', 'ARMember');
                    $module_box .= ' <a href="' . $manage_gateway_link . '" target="_blank">' . __('Please click here to add payment method.', 'ARMember') . '</a>';
                    $module_box .= ' ' . __('After setup payment gateway, click on refresh button', 'ARMember');
                    $module_box .= ' (<a style="float: none;padding: 3px;" href="javascript:void(0)" class="arm_setup_module_refresh" data-module="gateways"><i class="armfa armfa-refresh"></i></a>) ' . __('to get added payment gateways.', 'ARMember');
                    $module_box .= '</div>';
                }
                $module_box .= '</div>';
            }
            return $module_box;
        }

        function arm_update_plan_form_gateway_selection() {
            global $wp, $wpdb, $ARMember, $arm_payment_gateways, $arm_subscription_plans, $arm_capabilities_global;
            $returnArr = array(
                'plans' => '',
                'plan_layout_list' => '',
                'forms' => $this->arm_setup_form_list_options(),
                'gateways' => '',
            );
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_setups'], '1');
            $totalPlans = isset($_POST['total_plans']) ? intval($_POST['total_plans']) : 0;
            $totalGateways = isset($_POST['total_gateways']) ? intval($_POST['total_gateways']) : 0;
            $selectedPlans = (isset($_POST['selected_plans']) && !empty($_POST['selected_plans'])) ? explode(',', $_POST['selected_plans']) : array();
            $plansOrder = (isset($_POST['setup_data']['setup_modules']['modules']['plans_order'])) ? $_POST['setup_data']['setup_modules']['modules']['plans_order'] : array();
            $selectedGateways = (isset($_POST['selected_gateways']) && !empty($_POST['selected_gateways'])) ? explode(',', $_POST['selected_gateways']) : array();
            $user_selected_plan = (isset($_POST['default_selected_plan'])) ? intval($_POST['default_selected_plan']) : '';
            $activePlanCounts = $arm_subscription_plans->arm_get_total_active_plan_counts();
            if ($activePlanCounts == 0) {
                $returnArr['plans'] = "<span style='display:none;'></span>";
            } else if ($activePlanCounts != $totalPlans) {
                $allPlans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
                $returnArr['plans'] = $this->arm_setup_plan_list_options($selectedPlans, $allPlans);
                $returnArr['plan_layout_list'] = $this->arm_setup_plan_layout_list_options($plansOrder, $selectedPlans, $user_selected_plan);
            }
            $activeGateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            if (count($activeGateways) == 0) {
                $returnArr['gateways'] = "<span style='display:none;'></span>";
            } else if (count($activeGateways) != $totalGateways) {
                $activeGateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $returnArr['gateways'] = $this->arm_setup_gateway_list_options($selectedGateways, $activeGateways);
            }

            $returnArr = apply_filters('arm_modify_update_plan_form_gateway_selection', $returnArr, $_POST);

            echo json_encode($returnArr);
            exit;
        }

        function arm_setup_plan_list_options($selectedPlans = array(), $allPlans = array()) {
            global $wp, $wpdb, $ARMember, $arm_subscription_plans;
            $planList = '';
            
            if (!empty($allPlans)) {
                foreach ($allPlans as $plan) {
                    $planObj = new ARM_Plan(0);
                    $planObj->init((object) $plan);
                    $plan_id = $planObj->ID;
                    $plan_options = $planObj->options;
                    $arm_show_plan_payment_cycles = (isset($plan_options['show_payment_cycle']) && $plan_options['show_payment_cycle'] == '1') ? 1 : 0;

                    $plan_checked = (in_array($plan_id, $selectedPlans) ? 'checked="checked"' : '');
                    $planInputAttr = $plan_checked . ' data-plan_name="' . $planObj->name . '" data-plan_type="' . $planObj->type . '" data-payment_type="' . $planObj->payment_type . '" data-show_payment_cycle="' . $arm_show_plan_payment_cycles . '" ';
                    $planList .= '<div id="label_plan_chk_' . $plan_id . '" class="arm_setup_plan_opt_wrapper">';
                    $planList .= '<input type="checkbox" name="setup_data[setup_modules][modules][plans][]" value="' . $plan_id . '" id="plan_chk_' . $plan_id . '" class="arm_icheckbox plans_chk_inputs plans_chk_inputs_' . $planObj->type . '" ' . $planInputAttr . ' data-msg-required="' . __('Please select atleast one plan.', 'ARMember') . '"/>';
                    $planList .= '<label for="plan_chk_' . $plan_id . '">' . $planObj->name . '</label>';
                    $planList .= '</div>';
                }
            }
            return $planList;
        }

        function arm_setup_plan_layout_list_options($planOrders = array(), $selectedPlans = array(), $user_selected_plan = '') {
            global $wp, $wpdb, $ARMember, $arm_subscription_plans;
            $planOrderList = '';
            $allPlans = $arm_subscription_plans->arm_get_all_subscription_plans();
	    
            $allPlans = apply_filters('arm_modify_all_plans_arr_for_setup', $allPlans);
	    
            $orderPlans = $this->arm_sort_module_by_order($allPlans, $planOrders);
            $user_selected_plan = (isset($user_selected_plan)) ? $user_selected_plan : '';

            if (!empty($orderPlans)) {
                $pi = 1;
                foreach ($orderPlans as $plan) {
                    $plan_id = $plan['arm_subscription_plan_id'];
                    $add_class = 'arm_setup_subscription_plan_li';

                    $add_class = apply_filters('arm_add_class_filter_for_setup', $add_class, $plan_id);

                    /* if no plan selected than set first plan default selected */
                    if ($pi == 1 && $user_selected_plan == '') {
                        $user_selected_plan = $plan_id;
                    }

                    $planClass = 'arm_membership_setup_plans_li_' . $plan_id;
                    $planClass .= (!in_array($plan_id, $selectedPlans) ? ' hidden_section ' : '');
                    $planOrderList .= '<li class="arm_membership_setup_sub_li arm_membership_setup_plans_li ' . $planClass . ' '.$add_class.'">';
                    $planOrderList .= '<div class="arm_membership_setup_sortable_icon"></div>';
                    $planOrderList .= '<input type="radio" class="arm_iradio arm_default_user_selected_plan" name="setup_data[setup_modules][selected_plan]" value="' . $plan_id . '" ' . checked($user_selected_plan, $plan_id, false) . ' id="arm_setup_plan_' . $plan_id . '">';
                    $planOrderList .= '<label for="arm_setup_plan_' . $plan_id . '" class="arm_setup_plan_label">' . $plan['arm_subscription_plan_name'] . '</label>';
                    $planOrderList .= '<input type="hidden" name="setup_data[setup_modules][modules][plans_order][' . $plan_id . ']" value="' . $pi . '" class="arm_module_options_order arm_plan_order_inputs" data-plan_id="' . $plan_id . '">';
                    $planOrderList .= '</li>';
                    $pi++;
                }
            }
            return $planOrderList;
        }

        function arm_setup_form_list_options() {
            global $wp, $wpdb, $ARMember, $arm_member_forms;
            $registerForms = $arm_member_forms->arm_get_member_forms_by_type('registration', false);
            $formList = '<li data-label="' . __('Select Form', 'ARMember') . '" data-value="">' . __('Select Form', 'ARMember') . '</li>';
            if (!empty($registerForms)) {
                foreach ($registerForms as $form) {
                    $formList .= '<li data-label="' . strip_tags(stripslashes($form['arm_form_label'])) . '" data-value="' . $form['arm_form_id'] . '">' . strip_tags(stripslashes($form['arm_form_label'])) . '</li>';
                }
            }
            return $formList;
        }

        function arm_setup_gateway_list_options($selectedGateways = array(), $activeGateways = array(), $selectedPaymentModes = array(), $selectedPlans = array(), $plan_object_array = array()) {
            global $wp, $wpdb, $ARMember, $arm_payment_gateways, $arm_subscription_plans;
            $gatewayList = '';
            
            $arm_display_payment_mode_box = 'display: none;';

            if (!empty($selectedPlans) && count($selectedPlans) > 0) {
                foreach ($selectedPlans as $plan) {
                     $planObj = isset($plan_object_array[$plan]) ? $plan_object_array[$plan] : '';  
                    
                     if(is_object($planObj)){
                    $plan_type = $planObj->type;
                    $plan_options = $planObj->options;
                    $arm_show_plan_payment_cycles = (isset($plan_options['show_payment_cycle']) && $plan_options['show_payment_cycle'] == '1') ? 1 : 0;
                    if ($planObj->is_recurring() || ($plan_type == 'paid_finite' && $arm_show_plan_payment_cycles == 1)) {

                        $arm_display_payment_mode_box = 'display: block;';
                    }
                  }
                }
            }

            if (!empty($activeGateways)) {
                $doNotDisplayPaymentMode = array('bank_transfer');
                $doNotDisplayPaymentMode = apply_filters('arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode);
                foreach ($activeGateways as $key => $pg) {
                    
                    $selectedPaymentModes[$key] = isset($selectedPaymentModes[$key]) ? $selectedPaymentModes[$key] : 'both';
                    $checked_auto = ($selectedPaymentModes[$key] == 'auto_debit_subscription') ? 'checked="checked"' : '';
                    $checked_manual = ($selectedPaymentModes[$key] == 'manual_subscription') ? 'checked="checked"' : '';
                    $checked_both = ($selectedPaymentModes[$key] == 'both') ? 'checked="checked"' : '';

                    $gatewayChecked = in_array($key, $selectedGateways) ? 'checked="checked"' : '';
                    if (in_array($key, $selectedGateways)) {
                        $display_payment_mode = 'display: block;';
                    } else {
                        $display_payment_mode = 'display: none;';
                    }
                    $gatewayList .= '<div class="arm_setup_gateway_opt_wrapper" id="arm_setup_gateway_opt_wrapper_id">';
                    $gatewayList .= '<input type="checkbox" name="setup_data[setup_modules][modules][gateways][]" value="' . $key . '" id="gateway_chk_' . $key . '" class="arm_icheckbox gateways_chk_inputs" data-pg_name="' . $pg['gateway_name'] . '" ' . $gatewayChecked . ' data-msg-required="' . __('Please select atleast one payment gateway.', 'ARMember') . '"/>';
                    $gatewayList .= '<label for="gateway_chk_' . $key . '">' . $pg['gateway_name'] . '</label>';

                    if (!in_array($key, $doNotDisplayPaymentMode)) {
                        $gateway_note = '';
                        $gateway_note = apply_filters('arm_setup_show_payment_gateway_notice', $gateway_note, $key);
                        $gatewayList .= '<div class="arm_gateway_payment_mode_box" style="' . $arm_display_payment_mode_box . '"><div class="' . $key . '_gateway_payment_mode_class" id="arm_gateway_payment_mode_box" style="' . $display_payment_mode . '">
                           <label>' . __('In case of subscription plan selected', 'ARMember') . '</label>
                               <br/>
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . $key . ']" value="auto_debit_subscription" type="radio" class="arm_iradio arm_' . $key . '_gateway_payment_mode_input" ' . $checked_auto . ' id="arm_' . $key . '_auto_mode">
                                        <label for="arm_' . $key . '_auto_mode">' . __('Allow Auto debit method only', 'ARMember') . '</label><br>
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . $key . ']" value="manual_subscription" type="radio" class="arm_iradio arm_' . $key . '_gateway_payment_mode_input" ' . $checked_manual . ' id= "arm_' . $key . '_manual_mode">
                                        <label for="arm_' . $key . '_manual_mode">' . __('Allow Semi Automatic(manual) method only', 'ARMember') . '</label><br>
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . $key . ']" value="both" type="radio" class="arm_iradio arm_' . $key . '_gateway_payment_mode_input" ' . $checked_both . ' id="arm_' . $key . '_both_mode">
                                        <label for="arm_' . $key . '_both_mode">' . __('Both (allow user to select payment method)', 'ARMember') . '</label><br>' . $gateway_note . '
                                    </div></div>';
                    }
                    if (in_array($key, $doNotDisplayPaymentMode)) {
                        $gatewayList .= '<input name="setup_data[setup_modules][modules][payment_mode][' . $key . ']" value="manual_subscription" type="hidden" class="arm_iradio arm_' . $key . '_gateway_payment_mode_input" id= "arm_' . $key . '_manual_mode">';
                    }
                    $gatewayList .= '</div>';
                }
            }
            return $gatewayList;
        }

        function arm_total_setups() {
            global $wpdb,$ARMember;
            $setup_count = $wpdb->get_var("SELECT COUNT(`arm_setup_id`) FROM `" . $ARMember->tbl_arm_membership_setup . "`");
            return $setup_count;
        }

        function arm_get_membership_setup($setup_id = 0) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            if (is_numeric($setup_id) && $setup_id != 0) {
                /* Query Monitor Change */
                if( isset($GLOBALS['arm_setup_data']) && isset($GLOBALS['arm_setup_data'][$setup_id]) ){
                  $setup_data = $GLOBALS['arm_setup_data'][$setup_id];
                } else {
                  $setup_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_membership_setup . "` WHERE `arm_setup_id`='" . $setup_id . "'", ARRAY_A);
                  if( !isset($GLOBALS['arm_setup_data']) ){
                    $GLOBALS['arm_setup_data'] = array();
                  }
                  $GLOBALS['arm_setup_data'][$setup_id] = $setup_data;
                }
                if (!empty($setup_data)) {
                    $setup_data['arm_setup_name'] = (!empty($setup_data['arm_setup_name'])) ? stripslashes($setup_data['arm_setup_name']) : '';
                    $setup_data['arm_setup_modules'] = maybe_unserialize($setup_data['arm_setup_modules']);
                    $setup_data['arm_setup_labels'] = maybe_unserialize($setup_data['arm_setup_labels']);
                    $setup_data['setup_name'] = $setup_data['arm_setup_name'];
                    $setup_data['setup_modules'] = $setup_data['arm_setup_modules'];
                    $setup_data['setup_labels'] = $setup_data['arm_setup_labels'];
                    $setup_data['setup_type'] = $setup_data['arm_setup_type'];
                }
                return $setup_data;
            } else {
                return FALSE;
            }
        }

        function arm_save_membership_setups_func($posted_data = array()) {
            global $wp, $wpdb, $current_user, $arm_slugs, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_stripe;
            $redirect_to = admin_url('admin.php?page=' . $arm_slugs->membership_setup);
            if (isset($posted_data) && !empty($posted_data) && in_array($posted_data['form_action'], array('add', 'update'))) {
                $setup_data = $posted_data['setup_data'];
                if (!empty($setup_data)) {
                    $setup_name = (!empty($setup_data['setup_name'])) ? $setup_data['setup_name'] : __('Untitled Setup', 'ARMember');
                    $setup_modules = (!empty($setup_data['setup_modules'])) ? $setup_data['setup_modules'] : array();
                    $setup_labels = (!empty($setup_data['setup_labels'])) ? $setup_data['setup_labels'] : array();
                    $setup_type = (!empty($setup_data['setup_type'])) ? $setup_data['setup_type'] : 0;
                    $payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
                    foreach ($payment_gateways as $pgkey => $gateway) {
                        if ($setup_labels['payment_gateway_labels'][$pgkey] == '') {
                            $setup_labels['payment_gateway_labels'][$pgkey] = $gateway['gateway_name'];
                        }
                    }
                    if (!empty($setup_modules['modules']['module_order'])) {
                        asort($setup_modules['modules']['module_order']);
                    }

                    if( 1 == $setup_type ){
                        foreach( $setup_modules['modules']['payment_mode'] as $k => $v ){
                            if( 'bank_transfer' != $k && 'manual_subscription' != $v  ){
                                $setup_modules['modules']['payment_mode'][$k] = 'manual_subscription';
                            }
                        }
                    }

                    if(isset($setup_modules['modules']['gateways']) && in_array("stripe", $setup_modules['modules']['gateways']) && isset($setup_modules['modules']['payment_mode']['stripe']) && $setup_modules['modules']['payment_mode']['stripe'] != "manual_subscription")
                    {
                        //Assign stripe recurring plans to array
                        $arm_subscribe_plan_data = $setup_modules['modules']['plans'];

                        //Plan loop for check that if any plan is recurring then and only then it will check
                        foreach($arm_subscribe_plan_data as $arm_subscribe_plan_key => $arm_subscribe_plan_val)
                        {
                            $plan = new ARM_Plan($arm_subscribe_plan_val);
                            if($plan->is_recurring())
                            {
                                $arm_subscribe_plan_keys = array_keys($setup_modules['modules']['stripe_plans'][$arm_subscribe_plan_val]);

                                $arm_stripe_plan_vals = $arm_stripe->arm_stripe_get_stripe_plan($arm_subscribe_plan_val);

                                for($arm_i=0;$arm_i<count($arm_subscribe_plan_keys);$arm_i++)
                                {
                                    $setup_modules['modules']['stripe_plans'][$arm_subscribe_plan_val][$arm_subscribe_plan_keys[$arm_i]] = $arm_stripe_plan_vals[$arm_i];
                                }
                            }
                        }
                    }
                    $db_data = array(
                        'arm_setup_name' => $setup_name,
                        'arm_setup_modules' => maybe_serialize($setup_modules),
                        'arm_setup_labels' => maybe_serialize($setup_labels),
                        'arm_setup_type' => $setup_type
                    );
                    if ($posted_data['form_action'] == 'add') {
                        $db_data['arm_status'] = 1;
                        $db_data['arm_created_date'] = date('Y-m-d H:i:s');
                        /* Insert Form Fields. */
                        $wpdb->insert($ARMember->tbl_arm_membership_setup, $db_data);
                        $setup_id = $wpdb->insert_id;
                        /* Action After Adding Setup Details */
                        do_action('arm_saved_membership_setup', $setup_id, $db_data);
                        $ARMember->arm_set_message('success', __('Membership setup wizard has been added successfully.', 'ARMember'));
                        $redirect_to = $arm_global_settings->add_query_arg("action", "edit_setup", $redirect_to);
                        $redirect_to = $arm_global_settings->add_query_arg("id", $setup_id, $redirect_to);
                        wp_redirect($redirect_to);
                        exit;
                    } elseif ($posted_data['form_action'] == 'update' && !empty($posted_data['id']) && $posted_data['id'] != 0) {
                        $setup_id = $posted_data['id'];
                        $field_update = $wpdb->update($ARMember->tbl_arm_membership_setup, $db_data, array('arm_setup_id' => $setup_id));
                        /* Action After Updating Setup Details */
                        do_action('arm_saved_membership_setup', $setup_id, $db_data);
                        $ARMember->arm_set_message('success', __('Membership setup wizard has been updated successfully.', 'ARMember'));
                        $redirect_to = $arm_global_settings->add_query_arg("action", "edit_setup", $redirect_to);
                        $redirect_to = $arm_global_settings->add_query_arg("id", $setup_id, $redirect_to);
                        wp_redirect($redirect_to);
                        exit;
                    }
                }
            }
            return;
        }

        function arm_delete_single_setup() {
            global $wp, $wpdb, $current_user, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;
            $action = $_POST['act'];
            $id = intval($_POST['id']);
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_setups'], '1');
            if ($action == 'delete') {
                if (empty($id)) {
                    $errors[] = __('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_setups')) {
                        $errors[] = __('Sorry, You do not have permission to perform this action', 'ARMember');
                    } else {
                        $res_var = $wpdb->delete($ARMember->tbl_arm_membership_setup, array('arm_setup_id' => $id));
                        if ($res_var) {
                            $message = __('Setup has been deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_setup_shortcode_preview_func() {
            global $wpdb, $ARMember, $arm_capabilities_global;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_setup_shortcode_preview' & isset($_POST['setup_data'])) {
                $user = wp_get_current_user();
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_setups'], '1');
                $setup_id = intval($_POST['id']);
                $setupData = $_POST['setup_data'];
                ?>
                <div class="popup_wrapper arm_preview_setup_shortcode_popup_wrapper" style="width:70%;">
                    <div class="popup_wrapper_inner" style="overflow: hidden;">
                        <div class="popup_header">
                            <span class="popup_close_btn arm_popup_close_btn arm_preview_setup_shortcode_close_btn"></span>
                            <span class="add_rule_content"><?php _e('Preview', 'ARMember'); ?></span>
                        </div>
                        <div class="popup_content_text">
                <?php echo $this->arm_generate_setup_shortcode_preview($setupData, $setup_id); ?>
                        </div>
                        <div class="armclear"></div>
                    </div>
                </div>
                <?php
            }
            exit;
        }

        function arm_generate_setup_shortcode_preview($setupData = array(), $setupID = 0) {
            $setupForm = '';
            if (!empty($args['setup_data'])) {
                $setupForm .= '';
                $setupForm .= '';
                $setupForm .= '';
            }
            return $setupForm;
        }

        function arm_check_include_js_css($setup_data, $atts) {
            global $ARMember;
            $ARMember->set_front_css(true);
            $ARMember->set_front_js(true);
        }

        function arm_setup_skin_default_color_array() {
            $font_colors = array(
                'skin1' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                        'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                    'arm_setup_selected_plan_title_font_color' => '#005AEE',
                        'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
                    'arm_setup_selected_price_font_color' => '#FFFFFF',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
                'skin2' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                        'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                    'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
                        'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
                    'arm_setup_selected_price_font_color' => '#005AEE',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
                'skin3' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                        'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                        'arm_setup_selected_plan_title_font_color' => '#005AEE',
                        'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
                        'arm_setup_selected_price_font_color' => '#005AEE',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
                'skin4' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                        'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                    'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
                    'arm_setup_selected_plan_desc_font_color' => '#FFFFFF',
                    'arm_setup_selected_price_font_color' => '#FFFFFF',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
                'skin5' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                        'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                    'arm_setup_selected_plan_title_font_color' => '#005AEE',
                        'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
                    'arm_setup_selected_price_font_color' => '#FFFFFF',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
                'skin6' => array(
                        'arm_setup_plan_title_font_color' => '#2C2D42',
                    'arm_setup_plan_desc_font_color' => '#555F70',
                        'arm_setup_price_font_color' => '#2C2D42',
                        'arm_setup_summary_font_color' => '#555F70',
                    'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
                    'arm_setup_selected_plan_desc_font_color' => '#FFFFFF',
                    'arm_setup_selected_price_font_color' => '#FFFFFF',
                    'arm_setup_bg_active_color' => '#005AEE'
                ),
            );

            return apply_filters('arm_membership_setup_skin_colors', $font_colors);
        }

        function arm_update_card_action_func()
        {
            if(is_user_logged_in()) {
                global $wpdb, $ARMember, $arm_member_forms, $arm_transaction, $arm_payment_gateways;
                $arm_capabilities = '';
                $ARMember->arm_session_start();
                $ARMember->arm_check_user_cap($arm_capabilities, '0');
                $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : '';
                $setup_id = isset($_POST['setup_id']) ? intval($_POST['setup_id']) : '';
                $arm_user_id = get_current_user_id();
                $setup_data = $this->arm_get_membership_setup($setup_id);

                $form_in_setup = !empty($setup_data['setup_modules']['modules']['forms']) ? $setup_data['setup_modules']['modules']['forms'] : '';

                $user_form_id = !empty($form_in_setup) ? $form_in_setup : get_user_meta($arm_user_id, 'arm_form_id', true);
            
                $form = new ARM_Form('id', $user_form_id);

                if (!$form->exists() || $form->type != 'registration') {
                    $default_form_id = $arm_member_forms->arm_get_default_form_id('registration');
                    $form = new ARM_Form('id', $default_form_id);
                }

                if ($form->exists() && !empty($form->fields)) 
                {
                    $form_id = $form->ID;
                    $form_settings = $form->settings;
                    $ref_template = $form->form_detail['arm_ref_template'];
                    $form_style = $form_settings['style'];
                    
                    /* Form Classes */
                    $form_style['button_position'] = (!empty($form_style['button_position'])) ? $form_style['button_position'] : 'left';
                    $form_css = $arm_member_forms->arm_ajax_generate_form_styles($form_id, $form_settings, array(), $ref_template);
                    
                    $form_style_class = 'arm_shortcode_form arm_form_' . $form_id;
                    $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];
                    $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                    $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                    $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                    $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                    $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                    if (is_rtl()) {
                        $form_style_class .= ' arm_rtl_site';
                    }
                    $atts['class'] = !isset($atts['class']) ? $atts['class'] : '';
                    $form_style_class .= ' ' . $atts['class'];
                    //$btn_style_class = ' arm_btn_style_flat ';
                
                    $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                    
                    $planData = get_user_meta($arm_user_id, 'arm_user_plan_' . $plan_id, true);
                    
                    $arm_user_payment_gateway = $planData['arm_user_gateway'];
                    
                    $pg_options = $active_gateways[$arm_user_payment_gateway];

                    $setup_modules = $setup_data['setup_modules'];
                    $modules = $setup_modules['modules'];
                    $modules['forms'] = (!empty($modules['forms']) && $modules['forms'] != 0) ? $modules['forms'] : 0;
                    
                    $column_type = (!empty($setup_modules['gateways_columns'])) ? $setup_modules['gateways_columns'] : '1';
                    //$button_labels = $setup_data['setup_labels']['button_labels'];
                    //echo $button_labels['submit'];
                    $submit_btn = !empty($_POST['btn_text']) ? $_POST['btn_text'] : __('Update Card', 'ARMember');
                    
                    
                    if (!empty($form_settings)) {
                    $errPosCCField = !empty($form_settings['style']['validation_position']) ? $form_settings['style']['validation_position'] : 'bottom';
                    $fieldPosition = !empty($form_settings['style']['field_position']) ? $form_settings['style']['field_position'] : 'left';
                    $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                    $buttonStyle = (isset($form_settings['style']['button_style']) && !empty($form_settings['style']['button_style'])) ? $form_settings['style']['button_style'] : 'flat';
                    $btn_style_class = ' arm_btn_style_' . $buttonStyle;
                    }

                    $setupRandomID = $setup_id . '_' . arm_generate_random_code();

                    $form_attr = ' data-ng-controller="ARMCtrl" data-ng-cloak="" data-ng-id="' . $form_id . '" data-ng-submit="armUpdateCardFormSubmit(arm_form.$valid, \'arm_update_card_form' . $setupRandomID . '\', $event);" onsubmit="return false;"';

                    
                    $is_form_class_rtl = '';
                    if (is_rtl()) {
                        $is_form_class_rtl = 'is_form_class_rtl';
                    }
                    $captcha_code = arm_generate_captcha_code();
                    if (!isset($_SESSION['ARM_FILTER_INPUT'])) {
                        $_SESSION['ARM_FILTER_INPUT'] = array();
                    }
                    if (isset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID])) {
                        unset($_SESSION['ARM_FILTER_INPUT'][$setupRandomID]);
                    }
                    $_SESSION['ARM_FILTER_INPUT'][$setupRandomID] = $captcha_code;
                    $_SESSION['ARM_VALIDATE_SCRIPT'] = true;
                    $form_attr .= ' data-submission-key="' . $captcha_code . '" ';
                    

                    $arm_update_card_form = '';

                    $arm_update_card_form .= $form_css['arm_link'];
                    $arm_update_card_form .= '<style type="text/css" id="arm_update_card_form_style_' . $form_id . '">' . $form_css['arm_css'] . '</style>';
                    $arm_update_card_form .= '<div class="arm_member_form_container arm_update_card_form_container arm_form_' . $form_id . '" >';

                    $arm_update_card_form .= '<div class="arm_setup_messages arm_form_message_container"></div>';
                    $arm_update_card_form .= '<div class="armclear"></div>';
                    $arm_update_card_form .= '<form method="post" name="arm_form" id="arm_update_card_form' . $setupRandomID . '" class="arm_setup_form_' . $setup_id . ' arm_update_card_form arm_form_' . $modules['forms'] . ' ' . $is_form_class_rtl . '" enctype="multipart/form-data" data-random-id="' . $setupRandomID . '"  novalidate ' . $form_attr . '>';
                    //if($arm_user_payment_gateway=='stripe' || $arm_user_payment_gateway=='authorize_net' || $arm_user_payment_gateway=='paypal_pro')

                    $arm_allow_gateway = apply_filters("arm_allow_gateways_update_card_detail", false, $arm_user_payment_gateway);

                    if($arm_user_payment_gateway=='stripe' || $arm_user_payment_gateway=='authorize_net' || $arm_allow_gateway)
                    {
                        /*if ($pg_options['stripe_payment_mode'] == 'live') 
                        {
                            $apikey = $pg_options['stripe_pub_key'];
                        } else {
                                $apikey = $pg_options['stripe_test_pub_key'];
                        }*/
                        //$arm_update_card_form .= '<script data-cfasync="false">Stripe.setPublishableKey("' . $apikey . '");</script>';
                        $arm_update_card_form .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_'.$arm_user_payment_gateway.' arm_member_form_container">';
                        $arm_update_card_form .= '<div class="' . $form_style_class . '" data-ng-cloak="">';
                        $arm_update_card_form .= '<div class="arm_update_card_form_heading_container armalign' . $form_title_position . '">';
                        $arm_update_card_form .= '<span class="arm_form_field_label_wrapper_text">'.__('Update Card Details', 'ARMember').'</span>';
                        $arm_update_card_form .= '</div>';
                        //$arm_update_card_form .= '<h3 class="arm_setup_form_title">Update Card Details</h3>';
                        $arm_update_card_form .= $arm_payment_gateways->arm_get_credit_card_box($arm_user_payment_gateway, $column_type, $fieldPosition, $errPosCCField);
                        
                        $arm_update_card_form .= '<div class="armclear"></div>';
                        $arm_update_card_form .= '<div class="arm_form_field_container arm_form_field_container_submit">';
                        $arm_update_card_form .= '<div class="arm_label_input_separator"></div>';
                        $arm_update_card_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_submit"></div>';
                        $arm_update_card_form .= '<div class="arm_form_input_wrapper">';
                        $arm_update_card_form .= '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_update_card_form_input_container' . $setup_id . '">';

                        $ngClick = 'ng-click="armSubmitBtnClick($event)"';
                        if (current_user_can('administrator')) {
                            $ngClick = 'onclick="return false;"';
                        }

                        $arm_update_card_form .= '<md-button type="submit" name="ARMUPDATECARDSUBMIT" class="arm_update_card_submit_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input ' . $btn_style_class . '" ' . $ngClick . '><span class="arm_spinner">' . file_get_contents(MEMBERSHIP_IMAGES_DIR . "/loader.svg") . '</span>' . html_entity_decode(stripslashes($submit_btn)) . '</md-button>';

                        $arm_update_card_form .= '<md-button type="button" name="ARMUPDATECARDCANCEL" class="arm_cancel_update_card_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input ' . $btn_style_class . '">' . __('Cancel', 'ARMember') . '</md-button>';

                        if (current_user_can('administrator')) {
                            $arm_default_common_messages = $arm_global_settings->arm_default_common_messages();
                            $content .= '<div class="arm_disabled_submission_container">';
                                $content .= '<div class="arm_setup_messages arm_form_message_container">
                                                <div class="arm_error_msg">
                                                    <ul><li>'.$arm_default_common_messages['arm_disabled_submission'].'</li></ul>
                                                </div>
                                            </div>';
                            $content .= '</div>';
                        }

                        //$arm_update_card_form .= '<button type="button" class="arm_cancel_update_card_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input '.$btn_style_class.'">'.__('Cancel', 'ARMember').'</button>';

                        $arm_update_card_form .= '</div>';
                        $arm_update_card_form .= '</div>';
                        $arm_update_card_form .= '</div>';
                        
                        $arm_update_card_form .= '<div class="armclear" data-ng-init="armSetDefaultPaymentGateway(\'' . $arm_user_payment_gateway . '\');"></div>';

                        $arm_update_card_form .= '</div>';
                        $arm_update_card_form .= '</div>';
                        $arm_update_card_form .= '<input type="hidden" name="arm_user_plan_id" id="arm_user_plan_id" value="' . $plan_id . '">';
                        $arm_update_card_form .= '<input type="hidden" name="arm_user_setup_id" id="arm_user_setup_id" value="' . $setup_id . '">';
                        $arm_update_card_form .= '<input type="hidden" name="arm_user_payment_gatway" id="arm_user_payment_gatway" value="' . $arm_user_payment_gateway . '">';
                    }
                    $arm_update_card_form .= '</form>';
                    $arm_update_card_form .= '</div>';
                }
                echo $arm_update_card_form;
                $ARMember->enqueue_angular_script(true);
                $ARMember->set_front_css();
            }
            die;
        }
        function arm_membership_update_card_form_ajax_action($setup_id = 0, $post_data = array())
        {
            global $wp, $wpdb, $arm_slugs, $arm_errors, $ARMember, $arm_payment_gateways, $arm_authorize_net, $arm_global_settings, $arm_stripe;

            if(is_user_logged_in()) 
            {
                $err_msg = $arm_global_settings->common_message['arm_general_msg'];
                $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
                $response = array('status' => 'error', 'type' => 'message', 'message' => $err_msg);
                $success_msg = __("Your card details have been updated!", 'ARMember');
                $arm_user_id = get_current_user_id();
                $post_data = (!empty($_POST)) ? $_POST : $post_data;
                $setup_id = (!empty($post_data['arm_user_setup_id']) && $post_data['arm_user_setup_id'] != 0) ? intval($post_data['arm_user_setup_id']) : $setup_id;
                $plan_id = (!empty($post_data['arm_user_plan_id']) && $post_data['arm_user_plan_id'] != 0) ? intval($post_data['arm_user_plan_id']) : 0;

                $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                $planData = get_user_meta($arm_user_id, 'arm_user_plan_' . $plan_id, true);
                $arm_user_payment_gateway = $planData['arm_user_gateway'];
                $arm_user_payment_mode = $planData['arm_payment_mode'];
                $pg_options = $active_gateways[$arm_user_payment_gateway];

                $card_holder_name = (!empty($post_data[$arm_user_payment_gateway]['card_holder_name'])) ? $post_data[$arm_user_payment_gateway]['card_holder_name'] : '';

                $card_number = (!empty($post_data[$arm_user_payment_gateway]['card_number'])) ? $post_data[$arm_user_payment_gateway]['card_number'] : '';

                $exp_month = (!empty($post_data[$arm_user_payment_gateway]['exp_month'])) ? $post_data[$arm_user_payment_gateway]['exp_month'] : '' ;

                $exp_year = (!empty($post_data[$arm_user_payment_gateway]['exp_year'])) ? $post_data[$arm_user_payment_gateway]['exp_year'] : '' ;

                $cvc = (!empty($post_data[$arm_user_payment_gateway]['cvc'])) ? $post_data[$arm_user_payment_gateway]['cvc'] : '' ;
                if($arm_user_payment_mode=='auto_debit_subscription')
                {
                    if($arm_user_payment_gateway=='stripe')
                    {
                        if ($pg_options['stripe_payment_mode'] == 'live') 
                        {
                            $stripe_secret_key = $pg_options['stripe_secret_key'];
                            $stripe_pub_key = $pg_options['stripe_pub_key'];
                        } else {
                            $stripe_secret_key = $pg_options['stripe_test_secret_key'];
                            $stripe_pub_key = $pg_options['stripe_test_pub_key'];
                        }
                        
                        $arm_user_plan_stripe_details = $planData['arm_stripe'];
                        if(!empty($arm_user_plan_stripe_details['customer_id']))
                        {
                           $arm_user_stripe_customer_id =  $arm_user_plan_stripe_details['customer_id'];
                        } elseif(!empty($arm_user_plan_stripe_details['transaction_id'])) {
                            if (strpos($arm_user_plan_stripe_details['transaction_id'],'cus_') !== false) {
                                $arm_user_stripe_customer_id = $arm_user_plan_stripe_details['transaction_id'];
                            }
                        }
                        if( file_exists( MEMBERSHIP_DIR . "/lib/Stripe/vendor/autoload.php" ) ){
                            require_once( MEMBERSHIP_DIR . "/lib/Stripe/vendor/autoload.php" );
                        }
                        
                        $card_data = array( "number" => $card_number,
                                            "exp_month" => $exp_month,
                                            "exp_year" => $exp_year,
                                            "cvc" => $cvc,
                                            'name' => $card_holder_name,
                                        );
                        try {
                            $stripe_client = new \Stripe\StripeClient($stripe_secret_key);
                            Stripe\Stripe::setApiVersion($arm_stripe->arm_stripe_api_version);

                            if(empty($arm_user_stripe_customer_id))
                            {
                                $arm_stripe_subscription_id = !empty($arm_user_plan_stripe_details['transaction_id']) ? $arm_user_plan_stripe_details['transaction_id'] : '';
                                if(!empty($arm_stripe_subscription_id))
                                {
                                    //Get Subscription
                                    $arm_subscription_obj = $stripe_client->subscriptions->subscriptions($arm_stripe_subscription_id);
                                    if(!empty($arm_subscription_obj))
                                    {
                                        $arm_user_stripe_customer_id = $arm_subscription_obj->customer;
                                    }
                                }
                            }
                            
                            $token = $stripe_client->tokens->create(array("card" => $card_data));
                            $request_token = $token->id;
                            
                            $customer_id = $arm_user_stripe_customer_id;
                            $customer = $stripe_client->customers->retrieve($customer_id);
                            $customer->source = $request_token; // obtained with Checkout
                            $customer->save();
                            
                            $response = array('status' => 'success', 'type' => 'message', 'message' => $success_msg);
                        }
                        catch (Exception $e) 
                        {
                            $StripeAcion = $e;
                            $error_msg = $StripeAcion->getJsonBody();
                            $arm_help_link = '<a href="https://stripe.com/docs/error-codes" target="_blank">'.__('Click Here', 'ARMember').'</a>';
                            $actual_error = isset($error_msg['error']['message']) ? $error_msg['error']['message'] : '';
                            $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                            $response = array('status' => 'error', 'type' => 'message', 'message' => $actual_error);
                        }
                    }
                    else if($arm_user_payment_gateway=='authorize_net')
                    {
                        try {
                            $arm_authorize_net->arm_LoadAuthorizeNetLibrary($pg_options);
                            $arm_auth_subscription = new AuthorizeNet_Subscription;

                            $arm_auth_subscription->creditCardCardNumber = trim($card_number);
                            $arm_auth_subscription->creditCardExpirationDate = $exp_year . "-" . $exp_month;
                            $arm_auth_subscription->creditCardCardCode = $cvc;

                            $arm_user_plan_auth_details = $planData['arm_authorize_net'];
                            $AuthSubscriptionID =  !empty($arm_user_plan_auth_details['subscription_id']) ? $arm_user_plan_auth_details['subscription_id'] : '';
                            if(!empty($AuthSubscriptionID))
                            {
                                $request = new AuthorizeNetARB;
                                $atuh_response = $request->updateSubscription($AuthSubscriptionID, $arm_auth_subscription);
                                if ($atuh_response->isOk()) {
                                    $response = array('status' => 'success', 'type' => 'message', 'message' => $success_msg);
                                }
                            }
                        } 
                        catch (Exception $e) 
                        {
                            $arm_err_msg = $arm_global_settings->common_message['arm_unauthorized_credit_card'];
                            $gateway_error_msg = $e->getJsonBody();
                            $arm_help_link = '<a href="https://developer.authorize.net/api/reference/features/errorandresponsecodes.html" target="_blank">'.__('Click Here', 'ARMember').'</a>';
                            $ARMember->arm_write_response('reputelog authorize.net response6=>'.maybe_serialize($gateway_error_msg));
                            $actual_error = isset($gateway_error_msg['error']['message']) ? $gateway_error_msg['error']['message'] : $arm_err_msg;
                            $actual_error = !empty($actual_error) ? $actual_error.' '.$arm_help_link : '';
                            
                            $response = array('status' => 'error', 'type' => 'message', 'message' => $actual_error);
                        }
                    }
		    else {
	                    $response = apply_filters("arm_submit_gateways_updated_card_detail", $err_msg, $success_msg, $arm_user_payment_gateway, $pg_options, $card_holder_name, $card_number, $exp_month, $exp_year, $planData, $response,$cvc);
		    }
                }
                echo json_encode($response);
                exit;
            }
        }


        function arm_calculate_membership_data($arm_return_data, $payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_manage_coupons, $payment_done, $arm_payment_gateways, $arm_membership_setup, $arm_slugs, $is_free_manual, $arm_debug_payment_log_id;

            $arm_debug_log_data = array(
                'payment_gateway_options' => $payment_gateway_options,
                'posted_data' => $posted_data,
                'entry_id' => $entry_id,
            );
            do_action('arm_payment_log_entry', $payment_gateway, 'calculate membership data from common ', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

            $arm_return_data = apply_filters('arm_before_prepare_payment_data', $arm_return_data, $payment_gateway, $posted_data, $entry_id);

            $arm_return_data['arm_recurring_data'] = '';
            $arm_return_data['arm_trial_data'] = '';
            $arm_return_data['arm_trial_amount'] = '';
            $arm_return_data['arm_coupon_data'] = '';
            $arm_return_data['arm_coupon_amount'] = '';
            $arm_return_data['arm_payment_mode'] = '';
            $arm_return_data['arm_payable_amount'] = '';
            $arm_return_data['arm_remained_days'] = 0;
            $arm_return_data['arm_plan_action'] = 'new_subscription';
            $arm_return_data['arm_tax_data'] = '';
            $arm_return_data['arm_plan_start_date'] = current_time("mysql");
            $arm_return_data['arm_extra_vars'] = array();

            $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
            $arm_return_data['arm_entry_data'] = $entry_data;

            $plan_id = (!empty($posted_data['subscription_plan'])) ? $posted_data['subscription_plan'] : 0;
            if ($plan_id == 0) {
                $plan_id = (!empty($posted_data['_subscription_plan'])) ? $posted_data['_subscription_plan'] : 0;
            }


            $plan = new ARM_Plan($plan_id);
            $plan_action = 'new_subscription';

            $arm_return_data['arm_plan_id'] = $plan_id;
            $arm_return_data['arm_plan_obj'] = $plan;

            $plan_expiry_date = "now";
            $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';

            $oldPlanIdArray = (isset($posted_data['old_plan_id']) && !empty($posted_data['old_plan_id'])) ? explode(",", $posted_data['old_plan_id']) : 0;
            if (!empty($oldPlanIdArray)) {
                if (in_array($plan_id, $oldPlanIdArray)) {
                    $plan_action = 'renew_subscription';
                    $arm_return_data['arm_plan_action'] = "renew_subscription";

                    $user_id = !empty($entry_data['arm_user_id']) ? $entry_data['arm_user_id'] : 0;

                    $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                    $user_plan_data = !empty($user_plan_data) ? $user_plan_data : array();
                    $plan_expiry_date = (isset($user_plan_data['arm_expire_plan']) && !empty($user_plan_data['arm_expire_plan'])) ? $user_plan_data['arm_expire_plan'] : "now";
                    $plan_action = 'renew_subscription';
                    $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                    if ($is_recurring_payment) {
                        $plan_action = 'recurring_payment';
                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                        $oldPlanDetail = $planData['arm_current_plan_detail'];
                        $user_subsdata = $planData['arm_stripe'];
                        if (!empty($oldPlanDetail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $oldPlanDetail);
                        }
                    }
                } else {
                    $plan_action = 'change_subscription';
                    $arm_return_data['arm_plan_action'] = "change_subscription";
                }
            }

            $arm_return_data['arm_plan_expiry_date'] = $plan_expiry_date;


            $trial_not_allowed = 0;
            if ($plan->is_recurring()) 
            {
                $setup_id = $posted_data['setup_id'];
                $payment_mode_ = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                if(isset($posted_data['arm_payment_mode'][$payment_gateway])){
                    $payment_mode_ = !empty($posted_data['arm_payment_mode'][$payment_gateway]) ? $posted_data['arm_payment_mode'][$payment_gateway] : 'manual_subscription';
                }
                else{
                    $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
                    if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                        $setup_modules = $setup_data['setup_modules'];
                        $modules = $setup_modules['modules'];
                        $payment_mode_ = $modules['payment_mode'][$payment_gateway];
                    }
                }

                $subscription_plan_detail = $arm_subscription_plans->arm_get_subscription_plan($plan_id);

                
                if(!empty($subscription_plan_detail['arm_subscription_plan_options']['trial']))
                {
                    $arm_return_data['arm_trial_data'] = $subscription_plan_detail['arm_subscription_plan_options']['trial'];
                }

                $payment_mode = 'manual_subscription';
                $c_mpayment_mode = "";
                if(isset($posted_data['arm_pay_thgough_mpayment']) && $posted_data['arm_plan_type']=='recurring' && is_user_logged_in())
                {
                    $current_user_id = get_current_user_id();
                    $current_user_plan_ids = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                    $current_user_plan_ids = !empty($current_user_plan_ids) ? $current_user_plan_ids : array();
                    $Current_M_PlanData = get_user_meta($current_user_id, 'arm_user_plan_' . $plan_id, true);
                    $Current_M_PlanDetails = $Current_M_PlanData['arm_current_plan_detail'];
                    if (!empty($current_user_plan_ids)) {
                        if(in_array($plan_id, $current_user_plan_ids) && !empty($Current_M_PlanDetails))
                        {
                            $arm_cmember_paymentcycle = $Current_M_PlanData['arm_payment_cycle'];
                            $arm_cmember_completed_recurrence = $Current_M_PlanData['arm_completed_recurring'];
                            $arm_cmember_plan = new ARM_Plan(0);
                            $arm_cmember_plan->init((object) $Current_M_PlanDetails);
                            $arm_cmember_plan_data = $arm_cmember_plan->prepare_recurring_data($arm_cmember_paymentcycle);
                            $arm_cmember_TotalRecurring = $arm_cmember_plan_data['rec_time'];
                            if ($arm_cmember_TotalRecurring == 'infinite' || ($arm_cmember_completed_recurrence !== '' && $arm_cmember_completed_recurrence != $arm_cmember_TotalRecurring)) {
                                $c_mpayment_mode = 1;
                            }
                        }
                    }
                }
                if(empty($c_mpayment_mode))
                {
                    if ($payment_mode_ == 'both') {
                        $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
                    } else {
                        $payment_mode = $payment_mode_;
                    }
                }
            }
            else{
                $payment_mode = '';
            }

            $arm_return_data['arm_payment_mode'] = $payment_mode;

            $coupon_code = (!empty($posted_data['arm_coupon_code'])) ? trim($posted_data['arm_coupon_code']) : '';
            $setup_id = $posted_data['setup_id'];

            $is_free_manual = false;
            if (!empty($plan_id) && $plan_id != 0 && !empty($entry_id) && $entry_id != 0) {
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                if (isset($all_payment_gateways[$payment_gateway]) && !empty($all_payment_gateways[$payment_gateway])) {
                    $arm_payment_gateway_options = $all_payment_gateways[$payment_gateway];

                    $arm_payment_gateway_notification_url = ARM_HOME_URL."/";
                    if(strstr($arm_payment_gateway_notification_url, '?')){
                        $arm_payment_gateway_notification_url = $arm_payment_gateway_notification_url.'&arm-listener=arm_'.$payment_gateway.'_api';
                    }else{
                        $arm_payment_gateway_notification_url = $arm_payment_gateway_notification_url.'?arm-listener=arm_'.$payment_gateway.'_api';
                    }

                    $arm_return_data['arm_extra_vars']['arm_notification_url'] = $arm_payment_gateway_notification_url;

                    $globalSettings = $arm_global_settings->global_settings;
                    $cp_page_id = isset($globalSettings['cancel_payment_page_id']) ? $globalSettings['cancel_payment_page_id'] : 0;
                    $default_cancel_url = $arm_global_settings->arm_get_permalink('', $cp_page_id);

                    $cancel_url = (!empty($paypal_options['cancel_url'])) ? $paypal_options['cancel_url'] : $default_cancel_url;
                    if ($cancel_url == '' || empty($cancel_url)) {
                        $cancel_url = ARM_HOME_URL;
                    }

                    $arm_return_data['arm_extra_vars']['arm_cancel_url'] = $cancel_url;

                    $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
                    if(!empty($entry_data))
                    {
                        $user_email = $entry_data['arm_entry_email'];
                        $arm_return_data['arm_user_email'] = $user_email;
                        $form_id = $entry_data['arm_form_id'];
                        $user_id = $entry_data['arm_user_id'];
                        $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                        $return_url = $entry_values['setup_redirect'];

                        if (empty($return_url)) {
                            $return_url = ARM_HOME_URL;
                        }

                        $arm_return_data['arm_extra_vars']['arm_return_url'] = $return_url;

                        $arm_user_selected_payment_cycle = $entry_values['arm_selected_payment_cycle'];
                        $tax_percentage =  isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0;

                        $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                        $arm_is_trial = '0';

                        $currency = $arm_payment_gateways->arm_get_global_currency();

                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                        $planDataDefault = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $planData = !empty($userPlanDatameta) ? $userPlanDatameta : $planDataDefault;

                        if ($plan_action == 'renew_subscription' && $plan->is_recurring()) {
                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                            if ($is_recurring_payment) {
                                $plan_action = 'recurring_payment';
                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                if (!empty($oldPlanDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $oldPlanDetail);
                                }
                            }
                        }

                        $plan_payment_type = $plan->payment_type;

                        if ($plan->is_recurring()) {
                            $plan_data = $plan->prepare_recurring_data($arm_user_selected_payment_cycle);
                            $plan_data = apply_filters('arm_modify_recurring_data_outside', $plan_data, $plan, $plan->amount, $entry_id, $arm_user_selected_payment_cycle);

                            $amount = $plan_data['amount'];
                        } else {
                            $amount = $plan->amount;
                            $amount = apply_filters('arm_modify_subscription_plan_amount_outside',  $amount, $plan, $entry_id, $arm_user_selected_payment_cycle);
                        }

                        $amount = str_replace(",", "", $amount);
                        $main_amount = $amount;

                        $discount_amt = $coupon_amount = $arm_coupon_discount = 0;$trial_amount = 0;
                        $arm_coupon_discount_type = '';
                        $arm_coupon_discount_type_default ='';
                        $arm_coupon_on_each_subscriptions = '0';

                        if ($arm_manage_coupons->isCouponFeature && !empty($coupon_code)) {
                            $couponApply = $arm_manage_coupons->arm_apply_coupon_code($coupon_code, $plan, $setup_id, $arm_user_selected_payment_cycle, $arm_user_old_plan);
                            $couponApply['arm_coupon_code'] = $coupon_code;
                            $arm_return_data['arm_coupon_data'] = $couponApply;
                            $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                            $coupon_amount = str_replace(",", "", $coupon_amount);
                            $discount_amt = isset($couponApply['total_amt']) ? $couponApply['total_amt'] : $amount;
                            $arm_return_data['arm_coupon_amount'] = $discount_amt;
                            $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : '0';
                            $arm_coupon_discount_type_default = isset($couponApply['discount_type']) ? $couponApply['discount_type'] : "";

                            $arm_coupon_discount = isset($couponApply['discount']) ? $couponApply['discount'] : 0;
                            $global_currency = $arm_payment_gateways->arm_get_global_currency();
                            if (isset($couponApply['discount_type'])) {
                                $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                            } else {
                                $arm_coupon_discount_type = '';
                            }
                        }

                        $plan_form_data = '';

                        $allow_trial = false;

                        $discount_amt_next = $amount;
                        if ($plan->is_recurring()){

                            //if ($discount_amt > 0 && isset($arm_coupon_on_each_subscriptions) && !empty($arm_coupon_on_each_subscriptions)) {
                            if ($coupon_amount > 0 && isset($arm_coupon_on_each_subscriptions) && !empty($arm_coupon_on_each_subscriptions)) {
                                if($arm_coupon_discount_type_default=='percentage')
                                {
                                    $discount_amt_next = ($amount * $arm_coupon_discount) / 100;
                                    $discount_amt_next = $amount-$discount_amt_next;
                                }
                                else if($arm_coupon_discount_type_default=='fixed')
                                {
                                    $discount_amt_next = $amount - $arm_coupon_discount;
                                }

                                if($discount_amt_next<0)
                                {
                                   $discount_amt_next = 0; 
                                }
                                $arm_return_data['arm_coupon_amount_on_each_subs'] = $discount_amt;
                            }

                            $recurring_data = $plan->prepare_recurring_data($arm_user_selected_payment_cycle);

                            $recurring_data = apply_filters('arm_modify_recurring_data_outside', $recurring_data, $plan, $amount, $entry_id, $arm_user_selected_payment_cycle);
                            $arm_return_data['arm_recurring_data'] = $recurring_data;

                            $recur_period = $recurring_data['period'];
                            $recur_interval = $recurring_data['interval'];
                            $recur_cycles = $recurring_data['cycles'];

                            $is_trial = false;
                            $allow_trial = true;
                            if (is_user_logged_in()) {
                                $user_id = get_current_user_id();
                                $user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                if (!empty($user_plan)) {
                                    $allow_trial = false;
                                }
                            }

                            if (!empty($recurring_data['trial']) && $allow_trial) {
                                $arm_return_data['arm_trial_data'] = $recurring_data['trial'];
                                $is_trial = true;
                                $arm_is_trial = '1';
                                $trial_amount = $recurring_data['trial']['amount'];
                                $trial_period = $recurring_data['trial']['period'];
                                $trial_interval = $recurring_data['trial']['interval'];
                                $trial_amount = str_replace(",", "", $trial_amount);
                            }


                            if (!empty($coupon_amount) && $coupon_amount > 0) {
                                //$amount = $discount_amt_next = $discount_amt = $amount - $coupon_amount;
                                $discount_amt = $amount - $coupon_amount;
                                if (!$is_trial) {
                                    $recur_cycles = ($recur_cycles > 1) ? $recur_cycles - 1 : 1;
                                    $is_trial = true;
                                    $plan_action = 'new_subscription';
                                    $trial_interval = $recur_interval;
                                    $trial_period = $recur_period;

                                    $arm_return_data['arm_coupon_amount'] = $discount_amt;
                                }
                                else{
                                    $arm_return_data['arm_coupon_amount'] = $trial_amount;
                                    $trial_amount = $trial_amount - $coupon_amount;
                                    $trial_amount = str_replace(",", "", $trial_amount);
                                }
                            } else {
                                if(!empty($arm_coupon_on_each_subscriptions) && (empty($coupon_amount) || $coupon_amount == '0.00' || $coupon_amount == 0) && ($is_trial) && (empty($trial_amount) || $trial_amount == '0.00' || $trial_amount == 0) && !empty($arm_return_data['arm_coupon_data']))
                                {
                                    //If plan has free trial and all recurring coupon applied with plan then calculate discount amount.
                                    $arm_discount_amt = $arm_return_data['arm_coupon_data']['discount'];
                                    if($arm_coupon_discount_type == '%'){
                                        $arm_calculated_discounted_amt = $amount * ($arm_discount_amt / 100);
                                    } else {
                                        $arm_calculated_discounted_amt = $amount - $arm_discount_amt;
                                    }
                                    $arm_calculated_discounted_amt = number_format($arm_calculated_discounted_amt, 2);
                                    $arm_return_data['arm_coupon_data']['coupon_amt'] = $arm_calculated_discounted_amt;
                                }
                            }

                            $remained_days = 0;
                            if ($plan_action == 'renew_subscription') {
                                $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                $plan_expiry_date = $user_plan_data['arm_expire_plan'];
                                $now = strtotime(current_time('mysql'));

                                $remained_days = ceil(abs($plan_expiry_date - $now) / 86400);
                                $arm_return_data['arm_remained_days'] = $remained_days;

                                if ($remained_days > 0) {
                                    $trial_amount = 0;
                                    $trial_interval = $remained_days;
                                    $trial_period = 'D';
                                    
                                }
                            }
                        }
                        else{
                            if (!empty($coupon_amount) && $coupon_amount > 0) {
                                $amount = $discount_amt_next = $discount_amt = $amount - $coupon_amount;
                                $arm_return_data['arm_coupon_amount'] = $discount_amt;
                            }
                        }

                        $amount = str_replace(",", "", $discount_amt_next);
                       
                        $final_trial_amount = $trial_amount;
                        $final_amount = $amount;
                        if($tax_percentage > 0){
                            $trial_tax_amount =($trial_amount*$tax_percentage)/100;
                            $trial_tax_amount = number_format((float)$trial_tax_amount, 2, '.','');
                            $final_trial_amount = $trial_amount+$trial_tax_amount;

                            $tax_amount =($amount*$tax_percentage)/100;
                            $tax_amount = number_format((float)$tax_amount, 2, '.','');

                            $final_amount = $amount+$tax_amount;
                            
                            $arm_return_data_tmp = array(
                                'trial_tax_amount' => $trial_tax_amount,
                                'final_trial_amount' => $final_trial_amount,
                                'tax_amount' => $tax_amount,
                                'tax_final_amount' => $final_amount,
                                'tax_percentage' => $tax_percentage,
                            );

                            //Calculate tax for discounted amount
                            $discount_amt = $discount_amt + ($discount_amt * ($tax_percentage / 100));
                            $arm_return_data['arm_tax_data'] = $arm_return_data_tmp;
                        }

                        $zero_demial_currencies = $arm_payment_gateways->arm_stripe_zero_decimal_currency_array();
                        if(in_array($currency, $zero_demial_currencies))
                        {
                            $final_trial_amount= number_format((float) $final_trial_amount, 0, '', '');
                            $final_amount = number_format((float) $final_amount, 0, '', '');
                        }
                        else
                        {
                            $final_trial_amount = number_format((float)$final_trial_amount, 2, '.','');
                            $final_amount = number_format((float)$final_amount, 2, '.','');
                        }

                        /*if($is_trial){
                            $final_amount = $final_trial_amount;
                        }*/

                        $arm_return_data['allow_trial'] = $allow_trial;
                        $arm_return_data['arm_trial_amount'] = $final_trial_amount;
                        $arm_return_data['arm_payable_amount'] = $final_amount;
                        $arm_return_data['arm_trial_amount_for_not_in_plan_trial'] = ($arm_is_trial) ? $final_trial_amount : $discount_amt;
                        $arm_return_data['arm_trial_amount_for_not_in_plan_trial_flag'] = 0;

                        if($plan->is_recurring() && !$arm_is_trial && empty($arm_coupon_on_each_subscriptions) && $arm_manage_coupons->isCouponFeature && !empty($coupon_code)){
                            $arm_return_data['arm_trial_amount_for_not_in_plan_trial_flag'] = 1;
                        }
                    }
                }
            }

            $arm_return_data = apply_filters('arm_after_prepare_payment_data', $arm_return_data, $payment_gateway, $posted_data, $entry_id);

            return $arm_return_data;
        }


        function arm_calculate_webhook_coupon_data($arm_payment_log_data){
            global $ARMember, $wpdb, $arm_subscription_plans, $arm_manage_coupons, $arm_payment_gateways;
            $arm_return_coupon_data = array();
            $global_currency = $arm_payment_gateways->arm_get_global_currency();

            if(!empty($arm_payment_log_data) && is_object($arm_payment_log_data)){
                $user_id = !empty($arm_payment_log_data->arm_user_id) ? $arm_payment_log_data->arm_user_id : 0;
                if(!empty($user_id))
                {
                    $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $plan_ids = !empty($plan_ids) ? $plan_ids : array();
                    $plan_id = $arm_payment_log_data->arm_plan_id;

                    $new_plan = new ARM_Plan($plan_id);

                    $arm_coupon_code = !empty($arm_payment_log_data->arm_coupon_code) ? $arm_payment_log_data->arm_coupon_code : '';
                    $arm_coupon_discount = !empty($arm_payment_log_data->arm_coupon_discount) ? $arm_payment_log_data->arm_coupon_discount : '';
                    $arm_coupon_discount_type = !empty($arm_payment_log_data->arm_coupon_discount_type) ? $arm_payment_log_data->arm_coupon_discount_type : '';
                    $arm_coupon_on_each_subscriptions = !empty($arm_payment_log_data->arm_coupon_on_each_subscriptions) ? $arm_payment_log_data->arm_coupon_on_each_subscriptions : '';

                    if(!empty($arm_coupon_code)){
                        $couponApply = $arm_manage_coupons->arm_apply_coupon_code($arm_coupon_code);

                        $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                        $coupon_amount = str_replace(',', '', $coupon_amount);
                        
                        $discount_amt = isset($couponApply['total_amt']) ? $couponApply['total_amt'] : $amount;
                        $discount_amt = str_replace(',', '', $discount_amt);

                        if ($coupon_amount != 0) {
                            $arm_coupon_discount = $couponApply['discount'];
                            $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                            $arm_return_coupon_data['coupon_data'] = array(
                                'coupon_code' => $arm_coupon_code,
                                'amount' => $coupon_amount,
                                'arm_coupon_discount_type' => $couponApply['discount_type'],
                                'arm_coupon_discount_unit' => $arm_coupon_discount,
                            );

                            if($new_plan->is_recurring()) {
                                $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;
                                $arm_return_coupon_data['coupon_data']["arm_coupon_on_each_subscriptions"] = $arm_coupon_on_each_subscriptions;
                            }
                        }
                    }
                }
            }

            return $arm_return_coupon_data;
        }


        function arm_calculate_webhook_tax_data($arm_payment_log_data, $plan_amount){
            global $ARMember, $wpdb, $arm_subscription_plans;
            $arm_return_tax_data = array();
            if(!empty($arm_payment_log_data) && !empty($plan_amount)){
                $user_id = !empty($arm_payment_log_data->arm_user_id) ? $arm_payment_log_data->arm_user_id : 0;
                if(!empty($user_id)){
                    $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $plan_ids = !empty($plan_ids) ? $plan_ids : array();
                    $plan_id = $arm_payment_log_data->arm_plan_id;
                    $extraVars = $arm_payment_log_data->arm_extra_vars;
                    $tax_percentage = $tax_amount = 0;

                    if(isset($extraVars) && !empty($extraVars)){
                        $unserialized_extravars = maybe_unserialize($extraVars);
                        $tax_percentage = (isset($unserialized_extravars['tax_percentage']) && $unserialized_extravars['tax_percentage'] != '' )? $unserialized_extravars['tax_percentage'] : 0;
                        $arm_return_tax_data['tax_data']['tax_percentage'] = $tax_percentage;
                    }


                    if($tax_percentage > 0 && $plan_amount != ''){
                        $tax_amount = ($tax_percentage*$plan_amount)/100;
                        $tax_amount = number_format((float)$tax_amount , 2, '.', '');
                        $arm_return_tax_data['tax_data']['tax_amount'] = $tax_amount;
                        $amount = $amount +$tax_amount;
                        $arm_return_tax_data['tax_data']['plan_amount'] = $amount;
                    }
                }
            }

            return $arm_return_tax_data;
        }



        function arm_modify_payment_webhook_data($arm_webhook_save_membership_data, $arm_response_data, $arm_payment_gateway, $arm_token = '', $arm_transaction_id = '', $entry_id = 0, $arm_subscr_id = "", $arm_subscription_id_field_name = "", $arm_token_field_name = "", $arm_transaction_id_field_name = "")
        {
            global $ARMember, $wpdb, $arm_global_settings, $arm_subscription_plans, $arm_debug_payment_log_id;

            $arm_debug_log_data = array(
                'response_data' => $arm_response_data,
                'arm_payment_gateway' => $arm_payment_gateway,
                'arm_token' => $arm_token,
                'arm_transaction_id' => $arm_transaction_id,
                'entry_id' => $entry_id,
                'arm_subscr_id' => $arm_subscr_id,
                'arm_subscription_id_field_name' => $arm_subscription_id_field_name,
                'arm_token_field_name' => $arm_token_field_name,
                'arm_transaction_id_field_name' => $arm_transaction_id_field_name,
            );

            do_action('arm_payment_log_entry', $arm_payment_gateway, 'modify webhook request', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

            if(!empty($arm_transaction_id) && !empty($arm_payment_gateway))
            {
                $arm_transient_name = "arm_".$arm_payment_gateway."_trans_".$arm_transaction_id;
                $arm_transient_status = $arm_global_settings->arm_transient_get_action($arm_transient_name);
                if($arm_transient_status == 0)
                {
                    $arm_transient_time = DAY_IN_SECONDS;
                    $arm_global_settings->arm_transient_set_action($arm_transient_name, $arm_transaction_id, $arm_transient_time);
                }
                else
                {
                    do_action('arm_payment_log_entry', $arm_payment_gateway, 'Duplicate payment transaction id received', 'payment_gateway', $arm_transaction_id, $arm_debug_payment_log_id);
                    die();
                }
            }

            $arm_current_datetime = current_time('mysql');
            $arm_return_data = array();
            if(!empty($arm_subscr_id) || !empty($entry_id)){
                $subscription_id = $arm_subscr_id;
                if (!empty($subscription_id)) {
                    $arm_find_user_subs_id = $wpdb->get_row("SELECT * FROM $wpdb->usermeta WHERE meta_value LIKE '%".$subscription_id."%'");

                    if(!empty($arm_find_user_subs_id))
                    {
                        $user_id = !empty($arm_find_user_subs_id->user_id) ? $arm_find_user_subs_id->user_id : 0;

                        do_action('arm_payment_log_entry', $arm_payment_gateway, 'modify webhook user meta details', 'payment_gateway', $arm_find_user_subs_id, $arm_debug_payment_log_id);
                    }
                    else
                    {
                        $payLog = $wpdb->get_row("SELECT `arm_log_id`, `arm_user_id`, `arm_plan_id`, `arm_token`, `arm_amount`, `arm_currency`, `arm_payer_email`, `arm_extra_vars`, arm_first_name, arm_last_name, arm_coupon_code, arm_coupon_discount, arm_coupon_discount_type, arm_coupon_on_each_subscriptions FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_token`= '".$subscription_id."' ORDER BY `arm_log_id` DESC");

                        if(empty($payLog))
                        {
                            $payLog = apply_filters('arm_get_recurring_payment_log_data', $payLog, $subscription_id, $arm_payment_gateway, $arm_response_data);
                        }

                        do_action('arm_payment_log_entry', $arm_payment_gateway, 'modify webhook payment log details', 'payment_gateway', $payLog, $arm_debug_payment_log_id);

                        $arm_return_data['arm_subs_data']['paylog_data'] = $payLog;
                        $user_id = !empty($payLog->arm_user_id) ? $payLog->arm_user_id : 0;
                    }

                    if(!empty($user_id)){
                        //This condition will execute if any existing subscription found.
                        $arm_payment_log_id = $this->arm_add_user_and_transaction($entry_id, $user_id, $arm_response_data, $arm_payment_gateway, $arm_subscription_id_field_name, $arm_token_field_name, $arm_transaction_id_field_name);
                    } else if(empty($user_id) && !empty($entry_id)) {
                        //This condition will execute if no existing subscription or user found.
                        $arm_payment_log_id = $this->arm_add_user_and_transaction($entry_id, $user_id, $arm_response_data, $arm_payment_gateway, $arm_subscription_id_field_name, $arm_token_field_name, $arm_transaction_id_field_name);
                    }
                } else if(!empty($entry_id)) {
                    $arm_log_id = "";
                    $arm_user_id = 0;
                    if(!empty($arm_transaction_id)){
                        $arm_get_payment_log_data = $wpdb->get_row("SELECT * FROM `$ARMember->tbl_arm_payment_log` WHERE arm_token = '".$arm_transaction_id."'", ARRAY_A);

                        $arm_log_id = !empty($arm_get_payment_log_data['arm_log_id']) ? $arm_get_payment_log_data['arm_log_id'] : '';

                        $entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' ", ARRAY_A);

                        $arm_debug_log_data = array(
                            'trans_paylog_data' => $arm_get_payment_log_data,
                            'entry_data' => $entry_data,
                        );

                        do_action('arm_payment_log_entry', $arm_payment_gateway, 'modify payment webhook transaction details', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

                        $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                        $entry_email = isset($entry_data['arm_entry_email']) ? $entry_data['arm_entry_email'] : '';

                        if(!empty($entry_data['arm_user_id']) ) {
                            $arm_user_id = $entry_data['arm_user_id'];
                        }
                        else {
                            $user_info = get_user_by('email', $entry_email);
                            $arm_user_id = isset($user_info->ID) ? $user_info->ID : 0;
                        }
                    }

                    $arm_debug_log_data = array(
                        'log_id' => $arm_log_id,
                        'user_id' => $arm_user_id,
                    );
                    do_action('arm_payment_log_entry', $arm_payment_gateway, 'modify payment webhook transaction details', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

                    if($arm_log_id == ''){
                        $arm_created_user_id = $this->arm_add_user_and_transaction($entry_id, $arm_user_id, $arm_response_data, $arm_payment_gateway, $arm_subscription_id_field_name, $arm_token_field_name, $arm_transaction_id_field_name);
                    }else if(!empty($arm_user_id) || $arm_user_id != 0){
                        $arm_created_user_id = $this->arm_add_user_and_transaction($entry_id, $arm_user_id, $arm_response_data, $arm_payment_gateway, $arm_subscription_id_field_name, $arm_token_field_name, $arm_transaction_id_field_name);
                    }
                }
            }

            $arm_webhook_save_membership_data = $arm_return_data;
            return $arm_webhook_save_membership_data;
        }


        function arm_add_user_and_transaction($arm_entry_id = 0, $arm_user_id = 0, $arm_payment_gateway_response = array(), $arm_payment_gateway = '', $arm_subscription_id_field_name = '', $arm_token_field_name = '', $arm_transaction_id_field_name = ''){
            global $wpdb, $square, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_manage_coupons, $payment_done, $paid_trial_square_payment_done, $arm_members_class,$arm_transaction,$arm_membership_setup, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_debug_payment_log_id;

            $arm_debug_log_data = array(
                'arm_entry_id' => $arm_entry_id,
                'arm_user_id' => $arm_user_id,
                'gateway_response' => $arm_payment_gateway_response,
                'arm_payment_gateway' => $arm_payment_gateway,
                'subscription_id_field_name' => $arm_subscription_id_field_name,
                'token_field_name' => $arm_token_field_name,
                'transaction_id_field_name' => $arm_transaction_id_field_name,
            );
            do_action('arm_payment_log_entry', $arm_payment_gateway, 'add user and transaction data params', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

            $arm_current_datetime = current_time('mysql');

            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            if (isset($all_payment_gateways[$arm_payment_gateway]) && !empty($all_payment_gateways[$arm_payment_gateway])) {
                $options = $all_payment_gateways[$arm_payment_gateway];
                if($arm_payment_gateway == "2checkout"){
                    $arm_payment_gateway_mode = $options["payment_mode"];
                }else{
                    $arm_payment_gateway_mode = $options[$arm_payment_gateway."_payment_mode"];
                }

                $is_sandbox_mode = $arm_payment_gateway_mode == "sandbox" ? 1 : 0;
                $currency = $arm_payment_gateways->arm_get_global_currency();
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();

                if (!empty($arm_entry_id) ) {
                    $entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $arm_entry_id . "' ", ARRAY_A);
                    if(empty($arm_user_id) && !empty($entry_data['arm_user_id'])){
                        $arm_user_id = $entry_data['arm_user_id'];
                    }

                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                    $entry_email = isset($entry_data['arm_entry_email']) ? $entry_data['arm_entry_email'] : '';

                    if(empty($arm_payment_gateway_response['arm_payer_email']))
                    {
                        $arm_payment_gateway_response['arm_payer_email'] = $entry_email;
                    }

                    $arm_log_plan_id = $entry_data['arm_plan_id'];
                    $arm_log_amount = isset($entry_values['arm_total_payable_amount']) ? $entry_values['arm_total_payable_amount'] : '';

                    $plan = new ARM_Plan($arm_log_plan_id);
                    $entry_id = $arm_entry_id;
                    $form_id = $entry_data['arm_form_id'];
                    $armform = new ARM_Form('id', $form_id);

                    $extraParam = array();
                    $tax_percentage = isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0;
                    $arm_payment_gateway_extraParam['tax_percentage'] = $tax_percentage;
                    $payment_mode = $entry_values['arm_selected_payment_mode'];
                    $payment_cycle = !empty($entry_values['arm_selected_payment_cycle']) ? $entry_values['arm_selected_payment_cycle'] : 0;

                    $arm_user_old_plan = (isset($entry_values['arm_user_old_plan']) && !empty($entry_values['arm_user_old_plan'])) ? explode(",", $entry_values['arm_user_old_plan']) : array();
                    $setup_id = $entry_values['setup_id'];
                    $entry_plan = $entry_data['arm_plan_id'];
                    $new_plan = new ARM_Plan($entry_plan);

                    $arm_is_amount_load_from_usermeta = 0;
                    $arm_plan_amount = $new_plan->amount;
                    if($new_plan->is_recurring())
                    {
                        $arm_plan_options = $new_plan->options;
                        $arm_plan_amount = !empty($arm_plan_options) ? $arm_plan_options['payment_cycles'][$payment_cycle]['cycle_amount'] : $arm_plan_amount;
                    }

                    if(!empty($arm_user_id) && !empty($entry_plan)){
                        //$arm_is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($arm_user_id, $entry_plan, $payment_mode);
                        $arm_is_recurring_payment = $this->arm_is_recurring_payment($payment_mode, $arm_user_id, $entry_plan, $arm_payment_gateway_response[$arm_token_field_name]);
                        if($arm_is_recurring_payment){
                            $armUserPlanData = get_user_meta($arm_user_id, 'arm_user_plan_'.$entry_plan, true);
                            if(!empty($armUserPlanData)){
                                $armCurrentPlanDetails = !empty($armUserPlanData['arm_current_plan_detail']) ? $armUserPlanData['arm_current_plan_detail'] : array();
                                if(!empty($armCurrentPlanDetails)){
                                    $armCurrentPlanOptions = !empty($armUserPlanData['arm_subscription_plan_options']) ? maybe_unserialize($armUserPlanData['arm_subscription_plan_options']) : array();
                                    if(!empty($armCurrentPlanOptions) && isset($armCurrentPlanOptions['payment_cycles'][$payment_cycle])){
                                        $arm_plan_amount = $armCurrentPlanOptions['payment_cycles'][$payment_cycle]['cycle_amount'];
                                        $arm_is_amount_load_from_usermeta = 1;
                                    }
                                }
                            }
                        }
                    }

                    $arm_display_log = '1';

                    $arm_payment_gateway_extraParam['plan_amount'] = $arm_plan_amount;
                    $discount_amt = $arm_plan_amount;
                    $discount_amt = apply_filters('arm_modify_webhook_discount_amount', $discount_amt, $entry_plan, $entry_id, $entry_values, $arm_payment_gateway);

                    $arm_coupon_discount = 0;
                    $amount = $arm_plan_amount;
                    $amount = apply_filters('arm_modify_webhook_discount_amount', $amount, $entry_plan, $entry_id, $entry_values, $arm_payment_gateway);

                    $discount_amt = apply_filters('arm_modify_subscription_plan_amount_outside', $amount, $new_plan, $entry_id, $payment_cycle);

                    $arm_coupon_on_each_subscriptions = 0;
                    $couponCode = isset($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                    if(!empty($couponCode)){
                        $couponApply = $arm_manage_coupons->arm_apply_coupon_code($couponCode, $new_plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                        if($couponApply["status"] == "success") {
                            $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                            $coupon_amount = str_replace(',', '', $coupon_amount);

                            $discount_amt = isset($couponApply['total_amt']) ? $couponApply['total_amt'] : $amount;
                            $discount_amt = str_replace(',', '', $discount_amt);

                            $arm_payment_gateway_extraParam['coupon'] = array(
                                'coupon_code' => $couponCode,
                                'amount' => $coupon_amount,
                            );

                            $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : 0;

                            $arm_coupon_discount = str_replace(",", "", $couponApply['discount']);
                            $arm_coupon_discount_type = ($couponApply['discount_type'] != 'percentage') ? $currency : "%";
                            $arm_payment_gateway_response['coupon_code'] = $couponCode;
                            $arm_payment_gateway_response['arm_coupon_discount'] = $arm_coupon_discount;
                            $arm_payment_gateway_response['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                            $arm_payment_gateway_response['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subscriptions;
                        }
                    }

                    $arm_token = !empty($arm_payment_gateway_response[$arm_token_field_name]) ? $arm_payment_gateway_response[$arm_token_field_name] : '';
                    $arm_trans_id = !empty($arm_payment_gateway_response[$arm_transaction_id_field_name]) ? $arm_payment_gateway_response[$arm_transaction_id_field_name] : '';
                    $arm_subs_id = !empty($arm_payment_gateway_response[$arm_subscription_id_field_name]) ? $arm_payment_gateway_response[$arm_subscription_id_field_name] : '';

                    $arm_payment_gateway_response['arm_payment_gateway_trans_id'] = $arm_trans_id;
                    $arm_payment_gateway_response['arm_payment_gateway_token'] = $arm_token;
                    $arm_payment_gateway_response['arm_payment_gateway_subs_id'] = $arm_subs_id;

                    $amount_for_tax = $discount_amt;

                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
		    
                    $arm_is_recurring_payment = $this->arm_is_recurring_payment($payment_mode, $arm_user_id, $entry_plan, $arm_token);

                    if(!$arm_is_recurring_payment)
                    {
                        if (!empty($recurring_data['trial'])) {
                            $arm_payment_gateway_extraParam['trial'] = array(
                                'amount' => $recurring_data['trial']['amount'],
                                'period' => $recurring_data['trial']['period'],
                                'interval' => $recurring_data['trial']['interval'],
                            );
                            $arm_payment_gateway_extraParam['arm_is_trial'] = '1';

                            $amount_for_tax = $recurring_data['trial']['amount'];
                        }
                    }


                    if( $arm_coupon_discount > 0){
                       $amount_for_tax = $discount_amt;
                    }

                    $amount_for_tax = str_replace(",", "", $amount_for_tax);

                    $tax_amount = 0;
                    if($tax_percentage > 0){
                        $tax_amount =($amount_for_tax*$tax_percentage)/100;
                        $tax_amount = number_format((float)$tax_amount, 2, '.','');

                        $amount_for_tax = $amount_for_tax+$tax_amount;
                    }

                    $amount_for_tax = number_format((float)$amount_for_tax, 2, '.','');

                    $arm_payment_type = $plan->payment_type;
                    $payment_status = 'success';

                    $user_info = get_user_by('email', $entry_email);
                    $user_id = isset($user_info->ID) ? $user_info->ID : 0;
                        
                    $user_detail_first_name = get_user_meta( $user_id, 'first_name', true);
                    $user_detail_last_name = get_user_meta( $user_id, 'last_name', true);

                    $arm_payment_gateway_response['arm_coupon_code'] = !empty($entry_values['arm_coupon_code']) ? $entry_values['arm_coupon_code'] : '';
                    $arm_payment_gateway_response['arm_payment_type'] = $arm_payment_type;
                    $arm_payment_gateway_response['payment_type'] = $arm_payment_type;
                    $arm_payment_gateway_response['payment_status'] = $payment_status;
                    $arm_payment_gateway_response['payer_email'] = $entry_email;
                    $arm_payment_gateway_response['arm_first_name'] = $user_detail_first_name;
                    $arm_payment_gateway_response['arm_last_name'] = $user_detail_last_name;
                    $arm_payment_gateway_response['arm_payment_mode'] = $payment_mode;

                    $arm_payment_gateway_extraParam['payment_type'] = $arm_payment_gateway;
                    $arm_payment_gateway_extraParam['payment_mode'] = $arm_payment_gateway_mode;
                    $arm_payment_gateway_extraParam['arm_is_trial'] = '0';
                    $arm_payment_gateway_extraParam['subs_id'] = $arm_payment_gateway_response['arm_payment_gateway_subs_id'];
                    $arm_payment_gateway_extraParam['trans_id'] = $arm_payment_gateway_response['arm_payment_gateway_trans_id'];
                    $arm_payment_gateway_extraParam['date'] = current_time('mysql');
                    $arm_payment_gateway_extraParam['card_number'] = !empty($arm_payment_gateway_response['arm_payment_card_number']) ? $arm_payment_gateway_response['arm_payment_card_number'] : '';

                    $arm_payment_gateway_response['currency'] = $currency;
                    $arm_payment_gateway_response['payment_amount'] = $discount_amt;
                    $arm_payment_gateway_response['plan_action'] = 'new_subscription';

                    if (empty($arm_user_id) && !$user_info) {
                        $payment_log_id = 0;
                        if (in_array($armform->type, array('registration'))) {
                            
                            $arm_payment_gateway_extraParam['tax_amount'] = $tax_amount;
                            $arm_payment_gateway_extraParam['paid_amount'] = $amount_for_tax;
                            $arm_payment_gateway_response['payment_amount'] = $amount_for_tax;

                            $entry_values['payment_done'] = '1';
                            $entry_values['arm_entry_id'] = $entry_id;
                            $entry_values['arm_update_user_from_profile'] = 0;
                            $user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform, '', 0);

                            $arm_payment_gateway_response = apply_filters('arm_modify_subscription_outside', $arm_payment_gateway_response, $arm_payment_gateway, $user_id, $entry_plan);

                            $payment_log_id = $this->arm_store_payment_log($arm_payment_gateway_response, $user_id, $entry_plan, $arm_payment_gateway_extraParam, $arm_display_log, $arm_payment_gateway);

                            do_action('arm_after_register_and_store_log_data', $payment_log_id, $entry_id, $user_id, $entry_plan, $arm_payment_gateway, $arm_payment_gateway_response, $arm_payment_gateway_extraParam);

                            $user_email = !empty($entry_values['user_email']) ? $entry_values['user_email'] : '';
                            if(!empty($user_id) && !empty($user_email))
                            {
                                arm_new_user_notification($user_id);
                            }
			    
                            $arm_debug_log_data = array(
                                'user_id' => $user_id,
                                'payment_log_id' => $payment_log_id,
                            );

                            do_action('arm_payment_log_entry', $arm_payment_gateway, 'adding new user data', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

                            $payment_done = array();
                            if ($payment_log_id) {
                                $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                            }


                            if (is_numeric($user_id) && !is_array($user_id)) {
                                //if ($arm_payment_type == 'subscription') {

                                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);
                                $userPlanDataDefault = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                $userPlanData = !empty($userPlanDatameta) ? $userPlanDatameta : $userPlanDataDefault;

                                if(empty($userPlanData["arm_".$arm_payment_gateway]))
                                {
                                    $userPlanData["arm_".$arm_payment_gateway] = array();
                                }

                                $userPlanData["arm_".$arm_payment_gateway]['transaction_id'] = $arm_token;
                                $userPlanData["arm_".$arm_payment_gateway][$arm_subscription_id_field_name] = $arm_token;
                                $userPlanData["arm_".$arm_payment_gateway][$arm_transaction_id_field_name] = $arm_trans_id;
                                $userPlanData["arm_cencelled_plan"] = "";
                                update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanData);
                                //}
                                update_user_meta($user_id, 'arm_entry_id', $entry_id);

                                /**
                                 * Send Email Notification for Successful Payment
                                 */
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $entry_plan, 'user_id' => $user_id, 'action' => 'new_subscription'));
                            }
                        }

                        return $payment_log_id;
                    }
                    else if(!empty($arm_user_id)){
                        
                        if(!empty($arm_payment_gateway_response['arm_payment_gateway_trans_id']))
                        {
                            //If already added transaction ID data received then stop execution.
                            $arm_existing_transaction_data = $wpdb->get_row("SELECT arm_log_id FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_transaction_id`='".$arm_payment_gateway_response['arm_payment_gateway_trans_id']."' and `arm_transaction_id`!='' ORDER BY `arm_log_id` ASC", ARRAY_A);
                            if(!empty($arm_existing_transaction_data))
                            {
                                $arm_debug_log_data = array(
                                    'transaction_id' => $arm_payment_gateway_response['arm_payment_gateway_trans_id'],
                                    'user_id' => $arm_user_id,
                                    'existing_transaction_data' => $arm_existing_transaction_data,
                                );
                                do_action('arm_payment_log_entry', $arm_payment_gateway, 'transaction data for already existing user', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

                                die();
                            }
                        }


                        $user_info = get_user_by('id', $arm_user_id);
                        $user_id = $arm_user_id;

                        $arm_subscr_id = (!empty($arm_payment_gateway_response) && !empty($arm_payment_gateway_response[$arm_subscription_id_field_name])) ? $arm_payment_gateway_response[$arm_subscription_id_field_name] : '';

                        $payLog = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_token` = '".$arm_subscr_id."' and `arm_token`!='' ORDER BY `arm_log_id` ASC", ARRAY_A);

                        do_action('arm_payment_log_entry', $arm_payment_gateway, 'existing user subscription data', 'payment_gateway', $payLog, $arm_debug_payment_log_id);

                        if(!empty($user_id) && !empty($user_info)){
                            $arm_is_post_entry = !empty($payLog['arm_is_post_entry']) ? $payLog['arm_is_post_entry'] : 0;
                            $arm_paid_post_id = !empty($payLog['arm_paid_post_id']) ? $payLog['arm_paid_post_id'] : 0;
                            $arm_is_allow_multiple_purchase = apply_filters('arm_is_allow_purchase_with_multiple_plans', $payLog);

                            if(!empty($payLog) && !empty($arm_subscr_id)){
                                $entry_plan = !empty($payLog['arm_plan_id']) ? $payLog['arm_plan_id'] : 0;
                            }
                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $entry_plan, true);

                            $userPlanDataDefault = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : $userPlanDataDefault;

                            //if(!empty($userPlanDatameta['arm_payment_mode']))
                            if(empty($payment_mode) && !empty($userPlanDatameta['arm_payment_mode']))
                            {
                                $payment_mode = $userPlanDatameta['arm_payment_mode'];
                            }
                            $arm_user_selected_payment_cycle = ($userPlanDatameta['arm_payment_cycle'] != "") ? $userPlanDatameta['arm_payment_cycle'] : $payment_cycle;
                            $arm_token = $arm_subscr_id;

                            if (!$is_multiple_membership_feature->isMultipleMembershipFeature && !$arm_is_post_entry && !$arm_is_allow_multiple_purchase ){

                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                if($arm_pay_per_post_feature->isPayPerPostFeature)
                                {
	                                $user_post_ids = get_user_meta($user_id, 'arm_user_post_ids', true);
	                                if(!empty($user_post_ids))
	                                {
	                                    foreach($old_plan_ids as $arm_plan_key => $arm_plan_val)
	                                    {
	                                        if(isset($user_post_ids[$arm_plan_val]) && in_array($user_post_ids[$arm_plan_val], $user_post_ids))
	                                        {
	                                            unset($old_plan_ids[$arm_plan_key]);
	                                        }
	                                    }
	                                }
				                }

                                $old_plan_ids = apply_filters('arm_modify_plan_ids_externally', $old_plan_ids, $user_id);
				
                                $old_plan_id = !empty($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                $oldPlanDetail = array();
                                $old_subscription_id = '';
                                if(!empty($old_plan_id))
                                {

                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_'.$old_plan_id, true);
                                    $subscr_effective = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $oldPlanData['arm_next_due_payment'];
                                }
                                if(!empty($old_plan_id) && in_array($entry_plan, $old_plan_ids)){
                                    $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                    //$oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                    $old_subscription_id = !empty($oldPlanData["arm_".$arm_payment_gateway][$arm_subscription_id_field_name]) ? $oldPlanData["arm_".$arm_payment_gateway][$arm_subscription_id_field_name] : '';
                                    if(empty($old_subscription_id))
                                    {
                                        $arm_is_payment_recurring = $wpdb->get_row("SELECT * FROM ".$ARMember->tbl_arm_payment_log." WHERE arm_token = '".$arm_token."' AND arm_plan_id = ".$entry_plan."");
                                        if(!empty($arm_is_payment_recurring))
                                        {
                                            $old_subscription_id = $arm_token;
                                        }
                                    }
                                }

                                $userPlanDatameta['arm_is_user_in_grace'] = 0;
                                $userPlanDatameta['arm_grace_period_end'] = '';
                                $userPlanDatameta["arm_cencelled_plan"] = "";

                                $arm_user_old_plan_details = (isset($userPlanDatameta['arm_current_plan_detail']) && !empty($userPlanDatameta['arm_current_plan_detail'])) ? $userPlanDatameta['arm_current_plan_detail'] : array();
                                $arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanDatameta['arm_payment_mode'];


                                if(!empty($old_subscription_id) && $payment_mode == 'auto_debit_subscription' && $old_subscription_id == $arm_token)
                                {
                                    $arm_payment_gateway_response['plan_action'] = 'recurring_subscription';

                                    $arm_payment_gateway_extraParam['tax_amount'] = $tax_amount;
                                    $arm_payment_gateway_extraParam['paid_amount'] = $amount_for_tax;
                                    $arm_payment_gateway_response['payment_amount'] = $amount_for_tax;
                                    
                                    //For update next recurring of existing recurring subscription
                                    $this->arm_webhook_next_recurring_update_data($entry_plan, $user_id, $arm_payment_gateway);
                                }
                                else
                                {
                                    
                                    $now = current_time('mysql');
                                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_payment_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now)); 

                                    $userPlanDatameta['arm_current_plan_detail'] = $arm_user_old_plan_details;
                                    
                                    $userPlanDatameta['arm_payment_mode'] = $payment_mode;
                                    $userPlanDatameta['arm_payment_cycle'] = $arm_user_selected_payment_cycle;

                                    $userPlanDatameta["arm_".$arm_payment_gateway][$arm_subscription_id_field_name] = $arm_token;
                                    $userPlanDatameta["arm_".$arm_payment_gateway][$arm_transaction_id_field_name] = $arm_trans_id;

                                    if (!empty($oldPlanDetail)) {
                                        $old_plan = new ARM_Plan(0);
                                        $old_plan->init((object) $oldPlanDetail);
                                    } else {
                                        $old_plan = new ARM_Plan($old_plan_id);
                                    }
                                    $is_update_plan = true;
                                    /* Coupon Details */

                                    $arm_payment_gateway_extraParam['tax_amount'] = $tax_amount;
                                    $arm_payment_gateway_extraParam['paid_amount'] = $amount_for_tax;
                                    $arm_payment_gateway_response['payment_amount'] = $amount_for_tax;
                                    if ($old_plan->exists()) {
                                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                            $is_update_plan = true;
                                        } else {
                                            $change_act = 'immediate';
                                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                    $change_act = $old_plan->downgrade_action;
                                                }
                                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                    $change_act = $old_plan->upgrade_action;
                                                }
                                            }
                                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                                $is_update_plan = false;
                                                $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                                $oldPlanData['arm_change_plan_to'] = $entry_plan;
                                                update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                            }
                                        }
                                    }

                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                    $userPlanDatameta['arm_user_gateway'] = $arm_payment_gateway;

                                    if (!empty($arm_token)) {
                                        $userPlanDatameta["arm_".$arm_payment_gateway]['transaction_id'] = $arm_token;
                                    }

                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanDatameta);

                                    $arm_payment_gateway_response = apply_filters('arm_modify_subscription_outside', $arm_payment_gateway_response, $arm_payment_gateway, $user_id, $entry_plan);

                                    if ($is_update_plan) {
                                        $arm_payment_gateway_response['plan_action'] = 'renew_subscription';

                                        $payment_log_id = $this->arm_store_payment_log($arm_payment_gateway_response, $user_id, $entry_plan, $arm_payment_gateway_extraParam, $arm_display_log, $arm_payment_gateway);

                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);

                                    } else {
                                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                    }
                                }
                            }
                            else{
                                $now = current_time('mysql');
                                $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_payment_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, $now));
                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                if($arm_pay_per_post_feature->isPayPerPostFeature)
                                {
                                    $user_post_ids = get_user_meta($user_id, 'arm_user_post_ids', true);
                                    if(!empty($user_post_ids))
                                    {
                                        foreach($old_plan_ids as $arm_plan_key => $arm_plan_val)
                                        {
                                            if(isset($user_post_ids[$arm_plan_val]) && in_array($user_post_ids[$arm_plan_val], $user_post_ids))
                                            {
                                                unset($old_plan_ids[$arm_plan_key]);
                                            }
                                        }
                                    }
                                }
                                $oldPlanDetail = array();
                                $old_subscription_id = '';

                                if(!empty($old_plan_ids) && in_array($entry_plan, $old_plan_ids))
                                {
                                    $oldPlanData = get_user_meta($user_id, 'arm_user_plan_'.$entry_plan, true);
                                    $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                    $oldPlanDetail = $oldPlanData['arm_current_plan_detail'];
                                    $subscr_effective = $oldPlanData['arm_expire_plan'];
                                    $old_subscription_id = !empty($oldPlanData["arm_".$arm_payment_gateway][$arm_subscription_id_field_name]) ? $oldPlanData["arm_".$arm_payment_gateway][$arm_subscription_id_field_name] : '';
                                    if(empty($old_subscription_id))
                                    {
                                        $arm_is_payment_recurring = $wpdb->get_row("SELECT `arm_log_id` FROM ".$ARMember->tbl_arm_payment_log." WHERE arm_token = '".$arm_token."' AND arm_plan_id = ".$entry_plan."");
                                        if(!empty($arm_is_payment_recurring))
                                        {
                                            $old_subscription_id = $arm_token;
                                        }
                                    }
                                }

                                $userPlanDatameta['arm_is_user_in_grace'] = 0;
                                $userPlanDatameta['arm_grace_period_end'] = '';
                                $userPlanDatameta["arm_cencelled_plan"] = "";

                                if(!empty($old_subscription_id) && $payment_mode == 'auto_debit_subscription' && $old_subscription_id == $arm_token)
                                {
                                    $arm_payment_gateway_response['plan_action'] = 'recurring_subscription';
                                    
                                    $arm_payment_gateway_extraParam['tax_amount'] = $tax_amount;
                                    $arm_payment_gateway_extraParam['paid_amount'] = $amount_for_tax;
                                    $arm_payment_gateway_response['payment_amount'] = $amount_for_tax;

                                    //For update next recurring of existing recurring subscription
                                    $this->arm_webhook_next_recurring_update_data($entry_plan, $user_id, $arm_payment_gateway);
                                }
                                else
                                {
                                    $userPlanDatameta['arm_payment_mode'] = $payment_mode;
                                    $userPlanDatameta['arm_payment_cycle'] = $arm_user_selected_payment_cycle;

                                    $is_update_plan = true;
                                    /* Coupon Details */

                                    $recurring_data = $new_plan->prepare_recurring_data($payment_cycle);
                                    if (!empty($recurring_data['trial']) && empty($arm_user_old_plan)) {
                                        $arm_payment_gateway_extraParam['trial'] = array(
                                            'amount' => $recurring_data['trial']['amount'],
                                            'period' => $recurring_data['trial']['period'],
                                            'interval' => $recurring_data['trial']['interval'],
                                        );
                                        $amount_for_tax = $recurring_data['trial']['amount']; 
                                    }

                                    $arm_payment_gateway_extraParam['tax_amount'] = $tax_amount;
                                    $arm_payment_gateway_extraParam['paid_amount'] = $amount_for_tax;
                                    $arm_payment_gateway_response['payment_amount'] = $amount_for_tax;

                                    update_user_meta($user_id, 'arm_entry_id', $entry_id);
                                    $userPlanDatameta['arm_user_gateway'] = $arm_payment_gateway;

                                    if (!empty($arm_token)) {
                                        $userPlanDatameta["arm_".$arm_payment_gateway]['transaction_id'] = $arm_token;
                                    }

                                    update_user_meta($user_id, 'arm_user_plan_' . $entry_plan, $userPlanDatameta);
				    
				    $arm_payment_gateway_response = apply_filters('arm_modify_subscription_outside', $arm_payment_gateway_response, $arm_payment_gateway, $user_id, $entry_plan);
				    
                                    if ($is_update_plan) {
                                        $arm_payment_gateway_response['plan_action'] = 'renew_subscription';
                                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan,'', true, $arm_last_payment_status);
                                    } else {
                                       $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                    }
                                }
                            }


                            $payment_log_id = $this->arm_store_payment_log($arm_payment_gateway_response, $user_id, $entry_plan, $arm_payment_gateway_extraParam, $arm_display_log, $arm_payment_gateway);
                        }
                    }

                }
            }
        }


        function arm_store_payment_log($arm_payment_gateway_response = array(), $user_id = 0, $plan_id = 0, $arm_payment_gateway_extra_vars = array(), $arm_display_log = '1', $arm_payment_gateway = ''){
            global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms, $arm_payment_gateways, $arm_debug_payment_log_id, $arm_subscription_plans, $arm_manage_coupons;

            $arm_debug_log_data = array(
                'gateway_response' => $arm_payment_gateway_response,
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'extra_vars' => $arm_payment_gateway_extra_vars,
                'payment_gateway' => $arm_payment_gateway,
            );
            do_action('arm_payment_log_entry', $arm_payment_gateway, 'store payment log params', 'payment_gateway', $arm_debug_log_data, $arm_debug_payment_log_id);

            $payment_log_table = $ARMember->tbl_arm_payment_log;

            $transaction = $wpdb->get_row($wpdb->prepare("SELECT arm_token,arm_transaction_id,arm_extra_vars,arm_payer_email,arm_transaction_payment_type FROM `{$payment_log_table}` WHERE `arm_transaction_id`=%s AND `arm_transaction_id`!=%s ORDER BY `arm_created_date` DESC LIMIT 0,1", $arm_payment_gateway_response['arm_payment_gateway_trans_id'],'') );

            do_action('arm_payment_log_entry', $arm_payment_gateway, 'store payment log transaction data', 'payment_gateway', $transaction, $arm_debug_payment_log_id);

            if(!empty($arm_payment_gateway_response) && empty($transaction)){
                $arm_user_details = get_user_by('id', $user_id);

                $arm_user_email = !empty($arm_user_details->data->user_email) ? $arm_user_details->data->user_email : '';
                if(!empty($arm_payment_gateway_response['arm_payer_email'])){
                    $arm_user_email = $arm_payment_gateway_response['arm_payer_email'];
                }

                if(!empty($user_id))
                {
                    $user_detail_first_name = get_user_meta($user_id, 'first_name', true);
                    $user_detail_last_name = get_user_meta($user_id, 'last_name', true);
                }
                else
                {
                    $user_detail_first_name = !empty($arm_payment_gateway_response['arm_first_name']) ? $arm_payment_gateway_response['arm_first_name'] : '';
                    $user_detail_last_name = !empty($arm_payment_gateway_response['arm_last_name']) ? $arm_payment_gateway_response['arm_last_name'] : '';
                }

                $payment_data = array(
                    'arm_user_id' => $user_id,
                    'arm_first_name'=> $user_detail_first_name,
                    'arm_last_name'=> $user_detail_last_name,
                    'arm_plan_id' => (!empty($plan_id) ? $plan_id : 0),
                    'arm_payment_gateway' => $arm_payment_gateway,
                    'arm_payment_type' => $arm_payment_gateway_response['arm_payment_type'],
                    'arm_token' => $arm_payment_gateway_response['arm_payment_gateway_token'],
                    'arm_payer_email' => $arm_user_email,
                    'arm_receiver_email' => '',
                    'arm_transaction_id' => $arm_payment_gateway_response['arm_payment_gateway_trans_id'],
                    'arm_transaction_payment_type' => $arm_payment_gateway_response['payment_type'],
                    'arm_transaction_status' => $arm_payment_gateway_response['payment_status'],
                    'arm_payment_date' => current_time('mysql'),
                    'arm_payment_mode' => $arm_payment_gateway_response['arm_payment_mode'],
                    'arm_amount' => $arm_payment_gateway_response['payment_amount'],
                    'arm_currency' => $arm_payment_gateway_response['currency'],
                    'arm_coupon_code' => $arm_payment_gateway_response['arm_coupon_code'],
                    'arm_coupon_discount' => (isset($arm_payment_gateway_response['arm_coupon_discount']) && !empty($arm_payment_gateway_response['arm_coupon_discount'])) ? $arm_payment_gateway_response['arm_coupon_discount'] : 0,
                    'arm_coupon_discount_type' => isset($arm_payment_gateway_response['arm_coupon_discount_type']) ? $arm_payment_gateway_response['arm_coupon_discount_type'] : '',
                    'arm_extra_vars' => maybe_serialize($arm_payment_gateway_extra_vars),
                    'arm_is_trial' => isset($arm_payment_gateway_response['arm_is_trial']) ? $arm_payment_gateway_response['arm_is_trial'] : 0,
                    'arm_display_log' => $arm_display_log,
                    'arm_created_date' => current_time('mysql'),
                    'arm_coupon_on_each_subscriptions' => !empty($arm_payment_gateway_response['arm_coupon_on_each_subscriptions']) ? $arm_payment_gateway_response['arm_coupon_on_each_subscriptions'] : 0,
                );

                do_action('arm_payment_log_entry', $arm_payment_gateway, 'store payment log save data', 'payment_gateway', $payment_data, $arm_debug_payment_log_id);

                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);


                $plan_payment_mode = !empty($arm_payment_gateway_response['arm_payment_mode']) ? $arm_payment_gateway_response['arm_payment_mode'] : 'manual_subscription';
                //$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $plan_payment_mode);
                $is_recurring_payment = $this->arm_is_recurring_payment($arm_payment_gateway_response['arm_payment_mode'], $user_id, $plan_id, $arm_payment_gateway_response['arm_payment_gateway_token']);

                $plan_action = !empty($arm_payment_gateway_response['plan_action']) ? $arm_payment_gateway_response['plan_action'] : 'new_subscription';

                if($is_recurring_payment){
                    do_action('arm_after_recurring_payment_success_outside', $user_id, $plan_id, $arm_payment_gateway, $plan_payment_mode);
                }else{
                    if($arm_payment_gateway_response['arm_coupon_on_each_subscriptions'] == '1')
                    {
                        $userPlanData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                        $arm_manage_coupons->arm_coupon_apply_to_subscription($user_id, $payment_log_id, $arm_payment_gateway, $userPlanData);
                    }
                }

                return $payment_log_id;
            }
            return false;
        }


        function arm_webhook_next_recurring_update_data($arm_plan_id, $arm_user_id, $payment_gateway){
            global $wpdb, $arm_members_class, $arm_subscription_plans, $ARMember;
            if(!empty($arm_plan_id) && !empty($arm_user_id) && !empty($payment_gateway))
            {
                $plan_id = $arm_plan_id;
                $user_id = $arm_user_id;

                $plan = new ARM_Plan($plan_id);
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();

                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                $planDataDefault = shortcode_atts($defaultPlanData, $userPlanDatameta);

                $planData = !empty($userPlanDatameta) ? $userPlanDatameta : $planDataDefault;


                $arm_next_due_payment_date = $planData['arm_next_due_payment'];

                if(!empty($arm_next_due_payment_date)){
                    $total_completed_recurrence = $planData['arm_completed_recurring'];
                    $total_completed_recurrence++;
                    $planData['arm_completed_recurring'] = $total_completed_recurrence;

                    update_user_meta($user_id, 'arm_user_plan_'.$plan_id, $planData);
                    $payment_cycle = $planData['arm_payment_cycle'];

                    if($payment_gateway == "stripe")
                    {
                        $arm_subscription_id = $planData['arm_stripe']['transaction_id'];
                        if(substr($arm_subscription_id, 0, 3) != "sub"){
                            $arm_subscription_id = $planData['arm_stripe']['customer_id'];
                        }
                        if(!empty($arm_subscription_id))
                        {
                            global $arm_stripe;
                            $arm_next_due_payment = $arm_stripe->arm_retrieve_next_recurring_date_from_subs_id($arm_subscription_id);

                            if(empty($arm_next_due_payment)){
                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $plan_id, false, $payment_cycle);
                            }
                            else{
                                $arm_next_payment_date = $arm_next_due_payment; 
                            }
                        }
                    }
                    else{
                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $plan_id, false, $payment_cycle);
                    }
                    
                    $planData['arm_next_due_payment'] = $arm_next_payment_date;
                    $planData["arm_cencelled_plan"] = "";
                    update_user_meta($user_id, 'arm_user_plan_'.$plan_id, $planData);

                    //delete this flag if exists while failed payment status.
                    delete_user_meta($user_id, 'arm_user_failed_payment_plan_status_'.$plan_id);
                }
                
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids :  array(); 

                if(in_array($plan_id, $suspended_plan_id)){
                     unset($suspended_plan_id[array_search($plan_id,$suspended_plan_id)]);
                     update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                }
            }
        }

        function arm_is_recurring_payment($payment_mode, $arm_user_id, $entry_plan, $arm_token = ''){
            global $wpdb, $ARMember, $arm_subscription_plans;
            $arm_is_recurring_payment = 0;
            if($payment_mode == "manual_subscription" && !empty($arm_user_id) && !empty($entry_plan)){
                $arm_is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($arm_user_id, $entry_plan, $payment_mode);
            } else if($payment_mode == "auto_debit_subscription"){
                $arm_is_payment_recurring = $wpdb->get_row("SELECT * FROM ".$ARMember->tbl_arm_payment_log." WHERE arm_token='".$arm_token."' AND arm_token!='' ");
                $arm_is_recurring_payment = !empty($arm_is_payment_recurring) ? 1 : 0;
            }

            return $arm_is_recurring_payment;
        }
    }
}

global $arm_membership_setup;
$arm_membership_setup = new ARM_membership_setup();