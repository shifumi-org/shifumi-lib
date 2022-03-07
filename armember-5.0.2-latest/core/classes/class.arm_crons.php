<?php

if (!class_exists('ARM_crons')) {

    class ARM_crons {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            add_filter('cron_schedules', array($this, 'arm_add_cron_schedules'));
            //add_action('init', array($this, 'arm_add_crons'), 10);

            add_action('arm_handle_change_user_plan', array($this, 'arm_handle_change_user_plan_func'));
            add_action('arm_handle_expire_subscription', array($this, 'arm_handle_expire_subscription_func'));
            add_action('arm_handle_failed_payment_for_manual_subscription', array($this, 'arm_handle_failed_payment_for_manual_subscription_func'));
            
            /* For checking if recurring payment response is not arrived in the system OR 
             * For checking grace period is completed for failed payment
             */
            add_action('arm_handle_expire_infinite_subscription', array($this, 'arm_handle_expire_infinite_subscription_func'));
            add_action('arm_handle_failed_payment_for_auto_subscription', array($this, 'arm_handle_failed_payment_for_auto_subscription_func'));
            add_action('arm_handle_before_expire_subscription', array($this, 'arm_handle_before_expire_subscription_func'));
            add_action('arm_handle_before_dripped_content_available', array($this, 'arm_handle_before_dripped_content_available_func'));
            add_action('arm_handle_trial_finished', array($this, 'arm_handle_trial_finished_func'));
            add_action('arm_update_user_achievements', array($this, 'arm_update_user_achievements_func'));
            add_action('arm_handle_renewal_reminder_of_subscription', array($this, 'arm_handle_renewal_reminder_of_subscription_func'));
            add_action('arm_handle_renewal_reminder_of_subscription', array($this, 'arm_handle_renewal_reminder_of_automatic_subscription_func'));
            
            add_action('arm_handle_failed_login_log_data_delete', array($this, 'arm_handle_failed_login_log_data_delete_func'));
        }

        function arm_handle_failed_login_log_data_delete_func()
        {
            global $wpdb, $ARMember, $arm_global_settings;
            if(!empty($arm_global_settings->block_settings['failed_login_lockdown']))
            {
                $arm_tbl_arm_failed_login_logs = $ARMember->tbl_arm_fail_attempts;
                $arm_delete_start_date = date('Y-m-d', strtotime('-30 days'));
                $arm_delete_faild_login_log_data = $wpdb->query($wpdb->prepare("DELETE FROM `{$arm_tbl_arm_failed_login_logs}` WHERE `arm_fail_attempts_datetime` <= %s", $arm_delete_start_date.""));
            }
        }

        function arm_add_cron_schedules($schedules) {
            if (!is_array($schedules)) {
                $schedules = array();
            }
            for ($i = 2; $i < 24; $i++) {
                if ($i == 12) {
                    continue;
                }
                $display_label = __('Every', 'ARMember') . ' ' . $i . ' ' . __('Hour', 'ARMember');
                $schedules['every' . $i . 'hour'] = array('interval' => HOUR_IN_SECONDS * $i, 'display' => $display_label);
            }
            return apply_filters('arm_add_cron_schedules', $schedules);
        }

        function arm_add_crons() {
            global $wpdb, $ARMember, $arm_slugs, $arm_cron_hooks_interval, $arm_global_settings;
            wp_get_schedules();
            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings = $all_global_settings['general_settings'];
            $cron_schedules_time = isset($general_settings['arm_email_schedular_time']) ? $general_settings['arm_email_schedular_time'] : 12;
            $interval = 'twicedaily';
            if ($cron_schedules_time == 24) {
                $interval = 'daily';
            } else if ($cron_schedules_time == 12) {
                $interval = 'twicedaily';
            } else if ($cron_schedules_time == 1) {
                $interval = 'hourly';
            } else {
                $interval = 'every' . $cron_schedules_time . 'hour';
            }
            $cron_hooks = $this->arm_get_cron_hook_names();
            
            
            
            foreach ($cron_hooks as $hook) {
                if (!wp_next_scheduled($hook)) {
                    wp_schedule_event(time(), $interval, $hook);
                }
            }
            do_action('arm_membership_addon_crons', $interval);
        }

        function arm_handle_expire_subscription_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication, $arm_members_class;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            $start_time = strtotime("-12 Hours", $now);
            $end_time = strtotime("+30 Minutes", $now);
            $cron_msgs = array();
            /**
             * For Expire Subscription on Today Process
             */
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $expireUsers = get_users($args);


            if (!empty($expireUsers)) {
                foreach ($expireUsers as $usr) {
                    $user_id = $usr->ID;
                    $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $plan_ids = !empty($plan_ids) ? $plan_ids : array();
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($planData)) {
                                $expireTime = isset($planData['arm_expire_plan']) ? $planData['arm_expire_plan'] : '';
                                $is_plan_cancelled = $planData['arm_cencelled_plan'];
                                $planDetail = $planData['arm_current_plan_detail'];

                                if (!empty($planDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $planDetail);
                                } else {
                                    $plan = new ARM_Plan($plan_id);
                                }

                                if (!empty($expireTime)) {
                                    if ($expireTime <= $end_time) {
                                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);

                                        /* Cancel Subscription on expiration */
                                        if (isset($is_plan_cancelled) && $is_plan_cancelled == 'yes') {
                                            if ($plan->exists()) {
                                                $cancel_plan_action = isset($plan->options['cancel_plan_action']) ? $plan->options['cancel_plan_action'] : 'immediate';
                                                if ($cancel_plan_action == 'on_expire') {
                                                    if ($plan->is_paid() && !$plan->is_lifetime() && $plan->is_recurring()) {

                                                        do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                                        $arm_subscription_plans->arm_add_membership_history($usr->ID, $plan_id, 'cancel_subscription');
                                                        do_action('arm_cancel_subscription', $usr->ID, $plan_id);
                                                        $arm_subscription_plans->arm_clear_user_plan_detail($usr->ID, $plan_id);

                                                        $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                                                        if ($arm_subscription_plans->isPlanExist($cancel_plan_act)) {

                                                            do_action('arm_general_log_entry', 'cron', 'subscription user plan assigned by system', 'armember', 'userid='.$usr->ID.', planid='.$plan_id.', plan='.$plan_name.', cancel action='.$cancel_plan_act);

                                                            $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $plan_id, $usr->ID);
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        /* Send Notification Mail */
                                        $alreadysentmsgs = $planData['arm_sent_msgs'];
                                        $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();
                                        $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $usr->ID, 'action' => 'eot'));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($cron_msgs)) {
                do_action('arm_cron_expire_subscription', $cron_msgs);
            }
        }

        function arm_handle_expire_infinite_subscription_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication, $arm_members_class;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            $start_time = strtotime("-12 Hours", $now);
            $end_time = strtotime("+30 Minutes", $now);
            $cron_msgs = array();
            /**
             * For Expire infinite Subscription on Today Process
             */
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $expireUsers = get_users($args);

            if (!empty($expireUsers)) {
                foreach ($expireUsers as $usr) {
                    $user_id = $usr->ID;
                    $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $plan_ids = !empty($plan_ids) ? $plan_ids : array();
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($planData)) {
                                $expireTime = $planData['arm_next_due_payment'];
                                $is_plan_cancelled = $planData['arm_cencelled_plan'];
                                $planDetail = $planData['arm_current_plan_detail'];
                                if (!empty($planDetail)) { 
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $planDetail);
                                } else {
                                    $plan = new ARM_Plan($plan_id);
                                }
                                
                                
                                if (!empty($expireTime) && isset($is_plan_cancelled) && $is_plan_cancelled == 'yes') {
                                    if ($expireTime <= $now) {

                                        /* Cancel Subscription on expiration for infinite  */
                                        $plan_cycle = isset($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                                        $paly_cycle_data = $plan->prepare_recurring_data($plan_cycle);

                                        if($plan->is_recurring() && $paly_cycle_data['rec_time'] == 'infinite') {
                                            if ($plan->exists()) {
                                                $cancel_plan_action = isset($plan->options['cancel_plan_action']) ? $plan->options['cancel_plan_action'] : 'immediate';
                                                if ($cancel_plan_action == 'on_expire') {
                                                    if ($plan->is_paid() && !$plan->is_lifetime() && $plan->is_recurring()) {
                                                        //Update Last Subscriptions Log Detail
                                                        do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                                        $arm_subscription_plans->arm_add_membership_history($usr->ID, $plan_id, 'cancel_subscription');
                                                        do_action('arm_cancel_subscription', $usr->ID, $plan_id);
                                                        $arm_subscription_plans->arm_clear_user_plan_detail($usr->ID, $plan_id);

                                                        do_action('arm_general_log_entry', 'cron', 'expired infinite subscription user plan', 'armember', 
                                                            'userid='.$user_id.', planid='.$plan_id.', plan='.$plan_name.', expireTime='.$expireTime.', cancelled='.$is_plan_cancelled.', cancel action='.$cancel_plan_action.', recurringtime='.$paly_cycle_data['rec_time']);

                                                        $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                                                        if ($arm_subscription_plans->isPlanExist($cancel_plan_act)) {
                                                            $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $plan_id, $usr->ID);
                                                        } else {
                                                        }
                                                    }
                                                }
                                            }
                                            $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'eot'));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        function arm_handle_failed_payment_for_manual_subscription_func() 
        {
            /* Checked */
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication, $arm_members_class;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            //$start_time = strtotime("-12 Hours", $now);
            $end_time = $now;
            $cron_msgs = array();
            /**
             * For Expire Subscription on Today Process
             */
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $expireUsers = get_users($args);
            if (!empty($expireUsers)) 
            {
                foreach ($expireUsers as $usr) 
                {
                    $user_id = $usr->ID;
                    $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    if(!empty($plan_ids) && is_array($plan_ids))
                    {
                        foreach($plan_ids as $plan_id)
                        {
                            $planData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                            if(!empty($planData))
                            {
                                $planDetail = $planData['arm_current_plan_detail'];
                                if (!empty($planDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $planDetail);
                                } else {
                                    $plan = new ARM_Plan($plan_id);
                                }   

                                $payment_mode = $planData['arm_payment_mode'];
                                if ($plan->is_recurring() && $payment_mode == 'manual_subscription') 
                                {
                                    $expireTime = $planData['arm_next_due_payment'];
                                    $arm_payment_cycle = $planData['arm_payment_cycle'];
                                    $recurring_data = $plan->prepare_recurring_data($arm_payment_cycle);
                                    $recurring_time = $recurring_data['rec_time']; 
                                    $completed = $planData['arm_completed_recurring'];   

                                    if($recurring_time != $completed || 'infinite'==$recurring_time)
                                    {
                                        if (!empty($expireTime)) 
                                        {
                                            $arm_next_due_date = strtotime("+24 Hours", $expireTime); 
                                            if ($arm_next_due_date <= $end_time) 
                                            {
                                                $suspended_plan_ids = get_user_meta($usr->ID, 'arm_user_suspended_plan_ids', true);
                                                $suspended_plan_id = (!empty($suspended_plan_ids) && is_array($suspended_plan_ids)) ? $suspended_plan_ids :  array();                                                    
                                                if(!in_array($plan_id, $suspended_plan_id))
                                                {
                                                    /* Send Notification Mail */
                                                    $alreadysentmsgs = $planData['arm_sent_msgs'];
                                                    $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();

                                                    $arm_user_complete_recurring_meta = $planData['arm_completed_recurring'];
                                                    $arm_user_complete_recurring = !empty($arm_user_complete_recurring_meta) ? $arm_user_complete_recurring_meta : 0;

                                                    $arm_is_user_in_grace = !empty($planData['arm_is_user_in_grace']) ? $planData['arm_is_user_in_grace'] : 0;
                                                    $arm_grace_period_end = $planData['arm_grace_period_end'];
                                                    
                                                    do_action('arm_general_log_entry', 'cron', 'check user plan next due date', 'armember', 'user_id='.$user_id.', plan_id='.$plan_id.', next due date='.$arm_next_due_date.', end_time='.$end_time.', in grace='.$arm_is_user_in_grace.', grace end='.$arm_grace_period_end);

                                                    if (!in_array('failed_payment_' . $plan_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs)) 
                                                    {
                                        

                                                        $alreadysentmsgs['failed_payment_'.$now] = 'failed_payment_' . $plan_id . '_' . $arm_user_complete_recurring;
                                                        $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                        update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                        do_action('arm_general_log_entry', 'cron', 'manual failed payment action', 'armember', 'user_id='.$user_id.', plan_id='.$plan_id );

                                                        $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $usr->ID, 'action' => 'failed_payment'), true); 
                                                    }
                                                    else if( !in_array('failed_payment_after_grace_' . $plan_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs) && !empty($arm_is_user_in_grace) && !empty($arm_grace_period_end) && $arm_grace_period_end < $end_time) 
                                                    {

                                                        $alreadysentmsgs['failed_payment_after_grace_'.$now] = 'failed_payment_after_grace_' . $plan_id . '_' . $arm_user_complete_recurring;
                                                        $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                        update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                        do_action('arm_general_log_entry', 'cron', 'manual subscription after grace', 'armember', 'user_id='.$user_id.', plan='. $plan_id );
                                                        
                                                        $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $usr->ID, 'action' => 'failed_payment'), true); 
                                                    }

                                                    //suspended plan id check because in above two if condition, if plan suspended then only we need to send next payment failed notification.
                                                    $suspended_plan_ids_check_for_next_payment_failed = get_user_meta($usr->ID, 'arm_user_suspended_plan_ids', true);
                                                    $suspended_plan_ids_check_for_next_payment_failed = (!empty($suspended_plan_ids_check_for_next_payment_failed) && is_array($suspended_plan_ids_check_for_next_payment_failed)) ? $suspended_plan_ids_check_for_next_payment_failed :  array();

                                                    if (in_array($plan_id, $suspended_plan_ids_check_for_next_payment_failed) && !in_array('on_next_payment_failed_' . $plan_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs)) 
                                                    {
                                                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                                                        $notify = $arm_manage_communication->membership_communication_mail('on_next_payment_failed', $usr->ID, $plan_id);
                                                        if($notify) {

                                                             /* Update User meta for notification type */
                                                            $alreadysentmsgs['on_next_payment_failed_'.$now] = 'on_next_payment_failed_' . $plan_id . '_' . $arm_user_complete_recurring;
                                                            $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                            update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                            $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for failed payment.", 'ARMember') . "({$plan_name})";

                                                            do_action('arm_general_log_entry', 'cron', 'manual next payment failed', 'armember', 'user_id='.$user_id.', plan='. $plan_id );

                                                        } else {
                                                            $cron_msgs[$usr->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $usr->ID . " " . __("for failed payment.", 'ARMember') . "({$plan_name})";
                                                        }
                                                    }
                                                    else {
                                                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                                                        $cron_msgs[$usr->ID] = __("Mail already sent to", 'ARMember') . " " . $usr->ID . " " . __("for failed payment.", 'ARMember') . "({$plan_name})";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    /* Infinite Time case */
                } /* End Foreach Loop `($expireUsers as $usr)` */
            } /* End `(!empty($expireUsers))` */
            if (!empty($cron_msgs)) {
                do_action('arm_cron_failed_payment_subscription', $cron_msgs);
            }
        }

        function arm_handle_failed_payment_for_auto_subscription_func() {
            /* checked */
            global $wp, $wpdb, $ARMember, $arm_subscription_plans, $arm_payment_gateways;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            
            $end_time = strtotime("+30 Minutes", $now);
            
            $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
            /**
             * For failed payment for auto dabit subscription
             */
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $expireUsers = get_users($args);

            if (!empty($expireUsers)) {
                foreach ($expireUsers as $usr) {
                    
                    $plan_ids = get_user_meta($usr->ID, 'arm_user_plan_ids', true);
                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $userPlanDatameta = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            if (!empty($planData) && is_array($plan_ids)) {
                                $payment_mode = $planData['arm_payment_mode'];
                                if ($payment_mode == 'auto_debit_subscription') {
                                       
                                    /* check for failed payment after 1 day of last next due payment date 
                                        if failed payment was not occured and recurring response was not arrived, then in that case we need to call failed payment action */

                                    $planDetail = $planData['arm_current_plan_detail'];

                                    if (!empty($planDetail)) {
                                        $plan = new ARM_Plan(0);
                                        $plan->init((object) $planDetail);
                                    } else {
                                        $plan = new ARM_Plan($plan_id);
                                    }       
                                    
                                    $arm_payment_cycle = $planData['arm_payment_cycle'];
                                    $recurring_data = $plan->prepare_recurring_data($arm_payment_cycle);

                                    $amount = !empty($recurring_data['amount']) ? $recurring_data['amount'] : 0;
                                    $recurring_time = !empty($recurring_data['rec_time']) ? $recurring_data['rec_time'] : '';
                                    $completed = $planData['arm_completed_recurring'];

                                    if($recurring_time != $completed || 'infinite'==$recurring_time){
                                        $actual_arm_next_due_date = $planData['arm_next_due_payment'];
                                        if(!empty($actual_arm_next_due_date)){
                                            $arm_next_due_date = strtotime("+24 Hours", $actual_arm_next_due_date); 
                                            if($now > $arm_next_due_date){
                                                
                                                $suspended_plan_ids = get_user_meta($usr->ID, 'arm_user_suspended_plan_ids', true);
                                                $suspended_plan_id = (!empty($suspended_plan_ids) && is_array($suspended_plan_ids)) ? $suspended_plan_ids :  array();

                                                if(!in_array($plan_id, $suspended_plan_id)){
                                                  
                                                    /* control will come here only if recurring payment response was not arrived. */
                                                    $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $usr->ID, 'action' => 'failed_payment'), true);

                                                    $arm_user_payment_gateway = $planData['arm_user_gateway'];
                                                    do_action('arm_general_log_entry', 'cron', 'auto subscription check user suspend plan', 'armember', 'user_id='.$usr->ID.', plan='.$plan_id.', gateway='.$arm_user_payment_gateway.', time='.$now.'>'.$arm_next_due_date );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        function arm_handle_change_user_plan_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication;
            set_time_limit(0); /* Prevanting timeout issue. */
            $now = current_time('timestamp');
            $start_time = strtotime(date('Y-m-d 00:00:00'));
            $end_time = strtotime(date('Y-m-d 23:59:59'));
            $cron_msgs = array();

            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $users = get_users($args);

            if (!empty($users)) {
                foreach ($users as $usr) {
                    $user_id = $usr->ID;
                    $plan_ids = get_user_meta($usr->ID, 'arm_user_plan_ids', true);
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $planData = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($planData) && is_array($plan_ids)) {
                                $arm_subscription_effective = $planData['arm_subscr_effective'];
                                $new_plan = $planData['arm_change_plan_to'];
                                if (!empty($arm_subscription_effective)) {
                                    if ($arm_subscription_effective <= $end_time) {
                                        if (!empty($new_plan)) {
                                            $arm_subscription_plans->arm_update_user_subscription($user_id, $new_plan, 'system', false);
                                            do_action('arm_general_log_entry', 'cron', 'user membership plan changed', 'armember', 'user_id='.$user_id.', plan='.$plan_id.', subscription effective='.$arm_subscription_effective.', change plan to='.$new_plan );
                                            /* We can send mail to user for change subscription plan */
                                            $cron_msgs[$usr->ID] = $usr->user_email . "'s " . __("membership has been changed to", 'ARMember') . " {$new_plan}.";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            
            
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_future_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    ),
                )
            );
            $users = get_users($args);

            if (!empty($users)){
                foreach ($users as $usr) {
                    $user_id = $usr->ID;
                    $plan_ids = get_user_meta($usr->ID, 'arm_user_future_plan_ids', true);
                    $current_plan_ids = get_user_meta($usr->ID, 'arm_user_plan_ids', true);
                    $current_plan_ids = !empty($current_plan_ids) ? $current_plan_ids : array(); 
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $planData = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($planData) && is_array($plan_ids)) {
                                $arm_subscription_effective = $planData['arm_start_plan'];
                                if($now >= $arm_subscription_effective){
                                    if(!in_array($plan_id, $current_plan_ids)){
                                        $arm_plan_role = $planData['arm_current_plan_detail']['arm_subscription_plan_role'];
                                        
                                        if(count($current_plan_ids) > 0){
                                            $usr->add_role($arm_plan_role);
                                        }
                                        else{
                                            $usr->set_role($arm_plan_role);
                                        }
                                        unset($plan_ids[array_search($plan_id, $plan_ids)]);
                                        
                                        $current_plan_ids[] = $plan_id;
                                        update_user_meta($usr->ID, 'arm_user_last_plan', $plan_id);
                                        do_action('arm_general_log_entry', 'cron', 'user membership future plan changed', 'armember', 
                                            'user_id='.$user_id.', plan='.$plan_id.', subscription effective='.$arm_subscription_effective);
                                    }
                                }
                            }
                        }
                        update_user_meta($usr->ID, 'arm_user_future_plan_ids', array_values($plan_ids));

                        update_user_meta($usr->ID, 'arm_user_plan_ids', array_values($current_plan_ids));
                    }
                }
            }

            if (!empty($cron_msgs)) {
                do_action('arm_cron_change_user_plan', $cron_msgs);
            }
        }

        function arm_handle_before_expire_subscription_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication,$arm_pay_per_post_feature;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            $cron_msgs = array();
            $notifications = $arm_manage_communication->arm_get_communication_messages_by('message_type', 'before_expire');
            if (!empty($notifications)) {
                foreach ($notifications as $message) {
                    $period_unit = $message->arm_message_period_unit;
                    $period_type = $message->arm_message_period_type;
                    $endtime = strtotime("+$period_unit Days", $now);
                    switch (strtolower($period_type)) {
                        case 'd':
                        case 'day':
                        case 'days':
                            $endtime = strtotime("+$period_unit Days", $now);
                            break;
                        case 'w':
                        case 'week':
                        case 'weeks':
                            $endtime = strtotime("+$period_unit Weeks", $now);
                            break;
                        case 'm':
                        case 'month':
                        case 'months':
                            $endtime = strtotime("+$period_unit Months", $now);
                            break;
                        case 'y':
                        case 'year':
                        case 'years':
                            $endtime = strtotime("+$period_unit Years", $now);
                            break;
                        default:
                            break;
                    }
                    $endtime_start = strtotime(date('Y-m-d 00:00:00', $endtime));
                    $endtime_end = strtotime(date('Y-m-d 23:59:59', $endtime));
                    $message_plans = (!empty($message->arm_message_subscription)) ? explode(',', $message->arm_message_subscription) : array();
                    $planArray = array();
                    if (empty($message_plans)) {
                        $table = $ARMember->tbl_arm_subscription_plans;
                        $all_plans = $wpdb->get_results($wpdb->prepare("SELECT `arm_subscription_plan_id` FROM `{$table}` WHERE `arm_subscription_plan_type` != %s AND `arm_subscription_plan_type` != %s AND `arm_subscription_plan_post_id` = %d", 'free', 'paid_infinite', 0));

                        if (!empty($all_plans)) {
                            foreach ($all_plans as $plan) {
                                $planId = $plan->arm_subscription_plan_id;
                                $planArray[] = $planId;
                            }
                        }
                    } else {
                        $planArray = $message_plans;
                    }

                    if (!empty($planArray)) {
                        foreach ($planArray as $plan_id) {
                            $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                            $args = array(
                                'meta_query' => array(
                                    array(
                                        'key' => 'arm_user_plan_ids',
                                        'value' => '',
                                        'compare' => '!='
                                    )
                                )
                            );
                            $users = get_users($args);
                            if (empty($users)) {
                                continue;
                            }
                            foreach ($users as $usr) {
                                $user_plan_ids = get_user_meta($usr->ID, 'arm_user_plan_ids', true);
                                $user_post_ids = get_user_meta($usr->ID, 'arm_user_post_ids', true);
                                if( empty( $user_post_ids ) ){
                                    $user_post_ids = array();
                                }
                                if (!empty($user_plan_ids) && is_array($user_plan_ids)) {
                                    if (in_array($plan_id, $user_plan_ids) && !array_key_exists($plan_id, $user_post_ids) ) {
                                        $planData = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                                        if (!empty($planData)) {
                                            $expireTime = $planData['arm_expire_plan'];
                                            if (!empty($expireTime)) {
                                                if ($expireTime > $now && $expireTime <= $endtime_end) {

                                                    $memberStatus = arm_get_member_status($usr->ID);
                                                    $payment_mode = $planData['arm_payment_mode'];
                                                    $alreadysentmsgs = $planData['arm_sent_msgs'];
                                                    $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();

                                                    if (!in_array('before_expire_' . $message->arm_message_id, $alreadysentmsgs)) {
                                                        $subject = $arm_manage_communication->arm_filter_communication_content($message->arm_message_subject, $usr->ID, $plan_id);
                                                        $mailcontent = $arm_manage_communication->arm_filter_communication_content($message->arm_message_content, $usr->ID, $plan_id);
                                                        $send_one_copy_to_admin = $message->arm_message_send_copy_to_admin;
                                                        $send_diff_copy_to_admin = $message->arm_message_send_diff_msg_to_admin;
                                                        if ($message->arm_message_admin_message != '') {
                                                            $admin_content_description = $arm_manage_communication->arm_filter_communication_content($message->arm_message_admin_message, $usr->ID, $plan_id);
                                                        } else {
                                                            $admin_content_description = '';
                                                        }

                                                        $notify = $arm_global_settings->arm_wp_mail('', $usr->data->user_email, $subject, $mailcontent);
                                                        $send_mail = 0;
                                                        if ($send_one_copy_to_admin == 1) {
                                                            if ($send_diff_copy_to_admin == 1) {
                                                                $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_content_description);
                                                            } else {
                                                                $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $mailcontent);
                                                            }
                                                        }


                                                        if ($notify) {
                                                            /* Update User meta for notification type */
                                                            $alreadysentmsgs[$now] = 'before_expire_' . $message->arm_message_id;
                                                            $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                            update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                            $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                        } else {
                                                            $cron_msgs[$usr->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                        }

                                                        if ($send_mail) {
                                                            $cron_msgs['admin_mail_for_' . $usr->ID] = __("Mail successfully sent to admin for", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                        } else {
                                                            $cron_msgs['admin_mail_for_' . $usr->ID] = __("There is an error in sending mail to admin for", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                        }

                                                        do_action('arm_general_log_entry', 'cron', 'expire subscription check plan', 'armember', 'user_id='.$usr->ID.', plan='.$plan_id.', expireTime='.$expireTime.', notify='.$notify.', message_id='.$message->arm_message_id);

                                                    } else {
                                                        $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } /* End Foreach Loop `($notifications as $message)` */
            } /* End `(!empty($notifications))` */

            if( $arm_pay_per_post_feature->isPayPerPostFeature ) {
                $now = current_time('timestamp');
                $pp_notifications = $arm_manage_communication->arm_get_communication_messages_by( 'message_type', 'before_expire_post' );
                if (!empty($notifications)) {
                    foreach ($notifications as $message) {
                        $period_unit = $message->arm_message_period_unit;
                        $period_type = $message->arm_message_period_type;
                        $endtime = strtotime("+$period_unit Days", $now);
                        switch (strtolower($period_type)) {
                            case 'd':
                            case 'day':
                            case 'days':
                                $endtime = strtotime("+$period_unit Days", $now);
                                break;
                            case 'w':
                            case 'week':
                            case 'weeks':
                                $endtime = strtotime("+$period_unit Weeks", $now);
                                break;
                            case 'm':
                            case 'month':
                            case 'months':
                                $endtime = strtotime("+$period_unit Months", $now);
                                break;
                            case 'y':
                            case 'year':
                            case 'years':
                                $endtime = strtotime("+$period_unit Years", $now);
                                break;
                            default:
                                break;
                        }
                        $endtime_start = strtotime(date('Y-m-d 00:00:00', $endtime));
                        $endtime_end = strtotime(date('Y-m-d 23:59:59', $endtime));
                        $message_plans = (!empty($message->arm_message_subscription)) ? explode(',', $message->arm_message_subscription) : array();
                        $planArray = array();
                        if (empty($message_plans)) {
                            $table = $ARMember->tbl_arm_subscription_plans;
                            $all_plans = $wpdb->get_results($wpdb->prepare("SELECT `arm_subscription_plan_id` FROM `{$table}` WHERE `arm_subscription_plan_type` != %s AND `arm_subscription_plan_type` != %s AND arm_subscription_plan_post_id > %d", 'free', 'paid_infinite', 0));

                            if (!empty($all_plans)) {
                                foreach ($all_plans as $plan) {
                                    $planId = $plan->arm_subscription_plan_id;
                                    $planArray[] = $planId;
                                }
                            }
                        } else {
                            $planArray = $message_plans;
                        }

                        if (!empty($planArray)) {
                            foreach ($planArray as $plan_id) {
                                $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                                $args = array(
                                    'meta_query' => array(
                                        array(
                                            'key' => 'arm_user_plan_ids',
                                            'value' => '',
                                            'compare' => '!='
                                        )
                                    )
                                );
                                $users = get_users($args);
                                if (empty($users)) {
                                    continue;
                                }
                                foreach ($users as $usr) {
                                    $user_plan_ids = get_user_meta($usr->ID, 'arm_user_post_ids', true);
                                    if (!empty($user_plan_ids) && is_array($user_plan_ids)) {
                                        if (array_key_exists($plan_id, $user_plan_ids)) {
                                            $planData = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                                            if (!empty($planData)) {
                                                $expireTime = $planData['arm_expire_plan'];
                                                
                                                if (!empty($expireTime)) {
                                                    if ($expireTime > $now && $expireTime <= $endtime_end) {
                                                        $memberStatus = arm_get_member_status($usr->ID);
                                                        $payment_mode = $planData['arm_payment_mode'];
                                                        $alreadysentmsgs = $planData['arm_sent_msgs'];
                                                        $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();

                                                        if (!in_array('before_expire_post_' . $message->arm_message_id, $alreadysentmsgs)) {
                                                            $subject = $arm_manage_communication->arm_filter_communication_content($message->arm_message_subject, $usr->ID, $plan_id);
                                                            $mailcontent = $arm_manage_communication->arm_filter_communication_content($message->arm_message_content, $usr->ID, $plan_id);
                                                            $send_one_copy_to_admin = $message->arm_message_send_copy_to_admin;
                                                            $send_diff_copy_to_admin = $message->arm_message_send_diff_msg_to_admin;
                                                            if ($message->arm_message_admin_message != '') {
                                                                $admin_content_description = $arm_manage_communication->arm_filter_communication_content($message->arm_message_admin_message, $usr->ID, $plan_id);
                                                            } else {
                                                                $admin_content_description = '';
                                                            }

                                                            $notify = $arm_global_settings->arm_wp_mail('', $usr->data->user_email, $subject, $mailcontent);
                                                            $send_mail = 0;
                                                            if ($send_one_copy_to_admin == 1) {
                                                                if ($send_diff_copy_to_admin == 1) {
                                                                    $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_content_description);
                                                                } else {
                                                                    $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $mailcontent);
                                                                }
                                                            }


                                                            if ($notify) {
                                                                /* Update User meta for notification type */
                                                                $alreadysentmsgs[$now] = 'before_expire_post_' . $message->arm_message_id;
                                                                $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                                update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                                $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for before expire paid post purchase.", 'ARMember') . "({$plan_name})";
                                                            } else {
                                                                $cron_msgs[$usr->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $usr->ID . " " . __("for before expire paid post purchase.", 'ARMember') . "({$plan_name})";
                                                            }

                                                            if ($send_mail) {
                                                                $cron_msgs['admin_mail_for_' . $usr->ID] = __("Mail successfully sent to admin for", 'ARMember') . " " . $usr->ID . " " . __("for before expire membership.", 'ARMember') . "({$plan_name})";
                                                            } else {
                                                                $cron_msgs['admin_mail_for_' . $usr->ID] = __("There is an error in sending mail to admin for", 'ARMember') . " " . $usr->ID . " " . __("for before expire paid post purchase.", 'ARMember') . "({$plan_name})";
                                                            }

                                                            do_action('arm_general_log_entry', 'cron', 'expire subscription check post', 'armember', 'user_id='.$usr->ID.', plan='.$plan_id.', expireTime='.$expireTime.', notify='.$notify );

                                                        } else {
                                                            $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for before expire paid post purchase.", 'ARMember') . "({$plan_name})";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } /* End Foreach Loop `($notifications as $message)` */
                }
            }
            if (!empty($cron_msgs)) {
                do_action('arm_cron_before_expire_subscription', $cron_msgs);
            }
        }

        function arm_handle_before_dripped_content_available_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication, $arm_drip_rules;
            if ($arm_drip_rules->isDripFeature) {
                set_time_limit(0); /* Preventing timeout issue. */
                $now = current_time('timestamp');
                $cron_msgs = array();
                $notifications = $arm_manage_communication->arm_get_communication_messages_by('message_type', 'before_dripped_content_available');
                $all_drip_rules = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_drip_rules . "` ORDER BY `arm_rule_id` DESC", ARRAY_A);

                $dripRulesMembers = array();



                if (!empty($notifications) && !empty($all_drip_rules)) {
                    foreach ($notifications as $message) {
                        $period_unit = $message->arm_message_period_unit;
                        $period_type = $message->arm_message_period_type;
                        $message_plans = (!empty($message->arm_message_subscription)) ? explode(',', $message->arm_message_subscription) : array();
                        if (!empty($all_drip_rules)) {
                            foreach ($all_drip_rules as $dr) {
                                $ruleID = $dr['arm_rule_id'];
                                $dripAllowMembers = $arm_drip_rules->arm_get_members_for_before_dripped_reminder($ruleID, $period_type, $period_unit);
                                $dripRulesMembers[$ruleID] = $dripAllowMembers;
                            }
                        }

                        if (empty($dripRulesMembers)) {
                            continue;
                        }
                        $planArray = array();




                        if (empty($message_plans)) {
                            $table = $ARMember->tbl_arm_subscription_plans;
                            $all_plans = $wpdb->get_results("SELECT `arm_subscription_plan_id` FROM `{$table}` ");

                            if (!empty($all_plans)) {
                                foreach ($all_plans as $plan) {
                                    $plan_id = $plan->arm_subscription_plan_id;
                                    $planArray[] = $plan_id;
                                }
                            }
                        } else {
                            $planArray = $message_plans;
                        }

                        if (!empty($planArray)) {

                            foreach ($planArray as $plan_id) {
                                $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);

                                foreach ($dripRulesMembers as $rule_id => $members) {

                                    foreach ($members as $uID => $member) {
                                        $user_id = $member['user_id'];
                                        $user_email = $member['user_email'];
                                        $arm_item_id = $member['arm_item_id'];
                                        
                                        if (!empty($member['plan_array'])) {
                                            foreach ($member['plan_array'] as $member_plan_array) {
                                                if ($member_plan_array['plan_id'] == $plan_id) {
                                                    $memberStatus = arm_get_member_status($user_id);
                                                    $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                                                    $alreadysentmsgs = $planData['arm_sent_msgs'];
                                                    $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();
                                                    if (!in_array('before_dripped_content_' . $message->arm_message_id . '_' . $rule_id, $alreadysentmsgs)) {
                                                        $ARM_MESSAGE_DRIP_CONTENT_URL = "";
                                                        if($arm_item_id > 0) {
                                                            $ARM_MESSAGE_DRIP_CONTENT_URL = get_permalink($member['arm_item_id']);
                                                        }

                                                        $subject = $arm_manage_communication->arm_filter_communication_content($message->arm_message_subject, $user_id, $plan_id);
                                                        $subject = str_replace('{ARM_MESSAGE_DRIP_CONTENT_URL}', $ARM_MESSAGE_DRIP_CONTENT_URL, $subject);
							
                                                        $mailcontent = $arm_manage_communication->arm_filter_communication_content($message->arm_message_content, $user_id, $plan_id);
                                                        $mailcontent = str_replace('{ARM_MESSAGE_DRIP_CONTENT_URL}', $ARM_MESSAGE_DRIP_CONTENT_URL, $mailcontent);
							
                                                        $send_one_copy_to_admin = $message->arm_message_send_copy_to_admin;
                                                        $send_diff_copy_to_admin = $message->arm_message_send_diff_msg_to_admin;
                                                        if ($message->arm_message_admin_message != '') {
                                                            $admin_content_description = $arm_manage_communication->arm_filter_communication_content($message->arm_message_admin_message, $user_id, $plan_id);

                                                            $admin_content_description = str_replace('{ARM_MESSAGE_DRIP_CONTENT_URL}', $ARM_MESSAGE_DRIP_CONTENT_URL, $admin_content_description);
                                                            
                                                        } else {
                                                            $admin_content_description = '';
                                                        }

                                                        $notify = $arm_global_settings->arm_wp_mail('', $user_email, $subject, $mailcontent);
                                                        $send_mail = 0;
                                                        if ($send_one_copy_to_admin == 1) {
                                                            if ($send_diff_copy_to_admin == 1) {
                                                                $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_content_description);
                                                            } else {
                                                                $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $mailcontent);
                                                            }
                                                        }

                                                        $alreadysentmsgs[$now] = 'before_dripped_content_' . $message->arm_message_id . '_' . $rule_id;
                                                        $planData['arm_sent_msgs'] = $alreadysentmsgs;

                                                        update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                                                        if ($notify) {
                                                            /* Update User meta for notification type */
                                                            $cron_msgs[$user_id] = __("Mail successfully sent to", 'ARMember') . " " . $user_id . " " . __("for before dripped content available.", 'ARMember') . "({$plan_name})";
                                                        } else {
                                                            $cron_msgs[$user_id] = __("There is an error in sending mail to", 'ARMember') . " " . $user_id . " " . __("for before dripped content available.", 'ARMember') . "({$plan_name})";
                                                        }

                                                        if ($send_mail) {
                                                            $cron_msgs['admin_mail_for_' . $user_id] = __("Mail successfully sent to admin", 'ARMember') . " for " . $user_id . " " . __("for before dripped content available.", 'ARMember') . "({$plan_name})";
                                                        } else {
                                                            $cron_msgs['admin_mail_for_' . $user_id] = __("There is an error in sending mail to admin", 'ARMember') . " for " . $user_id . " " . __("for before dripped content available.", 'ARMember') . "({$plan_name})";
                                                        }


                                                        do_action('arm_general_log_entry', 'cron', 'dripped content available and email', 'armember', 'user_id='.$user_id.', plan='.$plan_id.', rule='.$rule_id.', msg='.$cron_msgs[$user_id].', notify='.$notify);
                                                        
                                                    } else {
                                                        $cron_msgs[$user_id] = __("Mail successfully sent to", 'ARMember') . " " . $user_id . " " . __("for before dripped content available.", 'ARMember') . "({$plan_name})";
                                                    }
                                                    
                                                }
                                            }
                                        }
                                    } /* End Foreach Loop `($users as $usr)` */
                                }
                            }
                        }
                    } /* End Foreach Loop `($notifications as $message)` */
                } /* End `(!empty($notifications))` */
                if (!empty($cron_msgs)) {
                    do_action('arm_cron_before_dripped_content_available', $cron_msgs);
                }
            }
        }

        /**
         * For Trial Period Finished on Today Process
         */
        function arm_handle_trial_finished_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            $eod_time = strtotime(date('Y-m-d 23:59:59', $now));
            $cron_msgs = array();
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $trialUsers = get_users($args);
            if (!empty($trialUsers)) {
                foreach ($trialUsers as $usr) {
                    $memberStatus = arm_get_member_status($usr->ID);
                    $plan_ids = get_user_meta($usr->ID, 'arm_user_plan_ids', true);
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            $planData = get_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, true);
                            if (!empty($planData) && is_array($planData)) {
                                $is_plan_trial = $planData['arm_is_trial_plan'];
                                $expireTime = $planData['arm_trial_end'];
                                if ($expireTime <= $eod_time && $is_plan_trial == '1') {

                                    $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                                    /* Send Notification Mail */
                                    $alreadysentmsgs = $planData['arm_sent_msgs'];
                                    $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();
                                    if (!in_array('trial_finished', $alreadysentmsgs)) {
                                        $notify = $arm_manage_communication->membership_communication_mail('trial_finished', $usr->ID, $plan_id);

                                        $planData['arm_is_trial_plan'] = 0;
                                        update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                        if ($notify) {
                                            /* Update User meta for notification type */
                                            $alreadysentmsgs[$now] = 'trial_finished';
                                            $planData['arm_sent_msgs'] = $alreadysentmsgs;

                                            update_user_meta($usr->ID, 'arm_user_plan_' . $plan_id, $planData);
                                            $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for trial period finished.", 'ARMember') . "({$plan_name})";
                                        } else {
                                            $cron_msgs[$usr->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $usr->ID . " " . __("for trial period finished.", 'ARMember') . "({$plan_name})";
                                        }

                                        do_action('arm_general_log_entry', 'cron', 'trial finished email check email', 'armember', 'user_id='.$usr->ID.', plan='.$plan_id.', expireTime='.$expireTime.', msg='.$cron_msgs[$usr->ID].', notify='.$notify);

                                    } else {
                                        $cron_msgs[$usr->ID] = __("Mail successfully sent to", 'ARMember') . " " . $usr->ID . " " . __("for trial period finished.", 'ARMember') . "({$plan_name})";
                                    }
                                    
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($cron_msgs)) {
                do_action('arm_cron_trial_finished', $cron_msgs);
            }
        }

        function arm_handle_renewal_reminder_of_subscription_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication;
            set_time_limit(0);
            $now = current_time('timestamp');
            $cron_msgs = array();
            $notifications = $arm_manage_communication->arm_get_communication_messages_by('message_type', 'manual_subscription_reminder');
            $notifications = $arm_manage_communication->arm_get_communication_messages_sorted($notifications);
                
            if (!empty($notifications)) {
                foreach ($notifications as $message_key => $message) {
                    $period_unit = $message->arm_message_period_unit;
                    $period_type = $message->arm_message_period_type;
                    $endtime = strtotime("+$period_unit Days", $now);
                    switch (strtolower($period_type)) {
                        case 'd':
                        case 'day':
                        case 'days':
                            $endtime = strtotime("+$period_unit Days", $now);
                            break;
                        case 'w':
                        case 'week':
                        case 'weeks':
                            $endtime = strtotime("+$period_unit Weeks", $now);
                            break;
                        case 'm':
                        case 'month':
                        case 'months':
                            $endtime = strtotime("+$period_unit Months", $now);
                            break;
                        case 'y':
                        case 'year':
                        case 'years':
                            $endtime = strtotime("+$period_unit Years", $now);
                            break;
                        default:
                            break;
                    }
                    $endtime_start = strtotime(date('Y-m-d 00:00:00', $endtime));
                    $endtime_end = strtotime(date('Y-m-d 23:59:59', $endtime));
                    $message_plans = (!empty($message->arm_message_subscription)) ? explode(',', $message->arm_message_subscription) : array();
                    $planArray = array();

                    if (empty($message_plans)) {
                        $table = $ARMember->tbl_arm_subscription_plans;
                        $all_plans = $wpdb->get_results($wpdb->prepare("SELECT `arm_subscription_plan_id` FROM `{$table}` WHERE `arm_subscription_plan_type` != %s AND `arm_subscription_plan_type` != %s", 'free', 'paid_infinite'));
                        if (!empty($all_plans)) {
                            foreach ($all_plans as $plan) {
                                $plan_id = $plan->arm_subscription_plan_id;
                                $planArray[] = $plan_id;
                            }
                        }
                    } else {
                        $planArray = $message_plans;
                    }

                    if (!empty($planArray)) {
                        foreach ($planArray as $plan_id) {
                            $planObj = new ARM_Plan($plan_id);
                            if (!$planObj->is_recurring()) {
                                continue;
                            }
                            $this->arm_send_mail_for_subsciption_expire_reminder($message, $plan_id, $endtime_start, $endtime_end, $now, $notifications, $message_key);
                        }
                    }
                }
            }
            if (!empty($cron_msgs)) {
                do_action('arm_cron_before_send_renew_subscption', $cron_msgs);
            }
        }

        function arm_send_mail_for_subsciption_expire_reminder($message, $arm_plan_id, $endtime_start, $endtime_end, $now, $notifications=array(), $message_key=0) {

            global $wp, $wpdb, $ARMember, $arm_manage_communication, $arm_global_settings, $arm_subscription_plans;
            $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($arm_plan_id);
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $users = get_users($args);

            if (!empty($users)) {

                foreach ($users as $user) {
                    $memberStatus = arm_get_member_status($user->ID);
                    $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            if($arm_plan_id==$plan_id)
                            {
                                $planData = get_user_meta($user->ID, 'arm_user_plan_' . $plan_id, true);
                                if (!empty($planData)) {
                                    $arm_next_due_payment = $planData['arm_next_due_payment'];
                                    $payment_mode = $planData['arm_payment_mode'];
                                    if ($payment_mode == 'auto_debit_subscription') {
                                        continue;
                                    }
                                    if (!empty($arm_next_due_payment)) {
                                        if ($arm_next_due_payment > $now && $arm_next_due_payment <= $endtime_end) {
                                            $alreadysentmsgs = $planData['arm_sent_msgs'];
                                            $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();

                                            $arm_user_complete_recurring_meta = $planData['arm_completed_recurring'];
                                            $arm_user_complete_recurring = isset($arm_user_complete_recurring_meta) ? $arm_user_complete_recurring_meta : 0;

                                            if (!in_array('manual_subscription_reminder_' . $message->arm_message_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs)) {
                                                $subject = $arm_manage_communication->arm_filter_communication_content($message->arm_message_subject, $user->ID, $plan_id);
                                                $mailcontent = $arm_manage_communication->arm_filter_communication_content($message->arm_message_content, $user->ID, $plan_id);
                                                $send_one_copy_to_admin = $arm_manage_communication->arm_filter_communication_content($message->arm_message_send_copy_to_admin, $user->ID, $plan_id);

                                                $send_diff_copy_to_admin = $message->arm_message_send_diff_msg_to_admin;

                                                if ($message->arm_message_admin_message != '') {
                                                    $admin_content_description = $arm_manage_communication->arm_filter_communication_content($message->arm_message_admin_message, $user->ID, $plan_id);
                                                } else {
                                                    $admin_content_description = '';
                                                }

                                                $notify = $arm_global_settings->arm_wp_mail('', $user->data->user_email, $subject, $mailcontent);
                                                $send_mail = 0;
                                                if ($send_one_copy_to_admin == 1) {
                                                    if ($send_diff_copy_to_admin == 1) {
                                                        $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_content_description);
                                                    } else {
                                                        $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $mailcontent);
                                                    }
                                                }

                                                if ($notify) {
                                                    /* Update User meta for notification type */
                                                    $alreadysentmsgs[] = 'manual_subscription_reminder_' . $message->arm_message_id . '_' . $arm_user_complete_recurring;
                                                    for ($i=0; $i<count($notifications); $i++) {
                                                        $arm_message_period_unit = $notifications[$i]->arm_message_period_unit;
                                                        if(!empty($arm_message_period_unit))
                                                        {
                                                            $endtime_chk = strtotime("+$arm_message_period_unit Days", $now);

                                                            if ($arm_next_due_payment <= $endtime_chk) {
                                                                if(!in_array('manual_subscription_reminder_' . $notifications[$i]->arm_message_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs))
                                                                {
                                                                        $alreadysentmsgs[] = 'manual_subscription_reminder_' . $notifications[$i]->arm_message_id . '_' . $arm_user_complete_recurring;    
                                                                }
                                                            }
                                                        }
                                                    }
                                                    
                                                    $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                    update_user_meta($user->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                    $cron_msgs[$user->ID] = __("Mail successfully sent to", 'ARMember') . " " . $user->ID . " " . __("for semi autoomatic subscription reminder.", 'ARMember') . "({$plan_name})";
                                                } else {
                                                    $cron_msgs[$user->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $user->ID . " " . __("for semi autoomatic subscription reminder.", 'ARMember') . "({$plan_name})";
                                                }

                                                if ($send_mail) {
                                                    $cron_msgs['admin_mail_for_' . $user->ID] = __("Mail successfully sent to admin", 'ARMember') . " for " . $user->ID . " " . __("for semi autoomatic subscription reminder.", 'ARMember') . "({$plan_name})";
                                                } else {
                                                    $cron_msgs['admin_mail_for_' . $user->ID] = __("There is an error in sending mail to admin", 'ARMember') . " for " . $user->ID . " " . __("for semi autoomatic subscription reminder.", 'ARMember') . "({$plan_name})";
                                                }

                                                do_action('arm_general_log_entry', 'cron', 'before subscription expire reminder email sent', 'armember', 'user_id='.$user->ID.', plan='.$plan_id.', next due payment='.$arm_next_due_payment.', sentmsg='.$alreadysentmsgs.', msg='.$cron_msgs[$user->ID].', notify='.$notify );

                                            } else {
                                                $cron_msgs[$user->ID] = __("Mail successfully sent to", 'ARMember') . " " . $user->ID . " " . __("for semi autoomatic subscription reminder.", 'ARMember') . "({$plan_name})";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //Repute Log Before Automatic Subscription Payment Due Code Start
        function arm_handle_renewal_reminder_of_automatic_subscription_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication;
            set_time_limit(0);
            $now = current_time('timestamp');
            $cron_msgs = array();
            $notifications = $arm_manage_communication->arm_get_communication_messages_by('message_type', 'automatic_subscription_reminder');
            $notifications = $arm_manage_communication->arm_get_communication_messages_sorted($notifications);
            if (!empty($notifications)) {
                foreach ($notifications as $message_key => $message) {
                    $period_unit = $message->arm_message_period_unit;
                    $period_type = $message->arm_message_period_type;
                    $endtime = strtotime("+$period_unit Days", $now);
                    switch (strtolower($period_type)) {
                        case 'd':
                        case 'day':
                        case 'days':
                            $endtime = strtotime("+$period_unit Days", $now);
                            break;
                        case 'w':
                        case 'week':
                        case 'weeks':
                            $endtime = strtotime("+$period_unit Weeks", $now);
                            break;
                        case 'm':
                        case 'month':
                        case 'months':
                            $endtime = strtotime("+$period_unit Months", $now);
                            break;
                        case 'y':
                        case 'year':
                        case 'years':
                            $endtime = strtotime("+$period_unit Years", $now);
                            break;
                        default:
                            break;
                    }
                    $endtime_start = strtotime(date('Y-m-d 00:00:00', $endtime));
                    $endtime_end = strtotime(date('Y-m-d 23:59:59', $endtime));
                    $message_plans = (!empty($message->arm_message_subscription)) ? explode(',', $message->arm_message_subscription) : array();
                    $planArray = array();

                    if (empty($message_plans)) {
                        $table = $ARMember->tbl_arm_subscription_plans;
                        $all_plans = $wpdb->get_results($wpdb->prepare("SELECT `arm_subscription_plan_id` FROM `{$table}` WHERE `arm_subscription_plan_type` != %s AND `arm_subscription_plan_type` != %s", 'free', 'paid_infinite'));
                        if (!empty($all_plans)) {
                            foreach ($all_plans as $plan) {
                                $plan_id = $plan->arm_subscription_plan_id;
                                $planArray[] = $plan_id;
                            }
                        }
                    } else {
                        $planArray = $message_plans;
                    }

                    if (!empty($planArray)) {
                        foreach ($planArray as $plan_id) {
                            $planObj = new ARM_Plan($plan_id);
                            if (!$planObj->is_recurring()) {
                                continue;
                            }
                            $this->arm_send_mail_for_automatic_subsciption_expire_reminder($message, $plan_id, $endtime_start, $endtime_end, $now, $notifications, $message_key);
                        }
                    }
                }
            }
            if (!empty($cron_msgs)) {
                do_action('arm_cron_before_send_renew_subscption', $cron_msgs);
            }
        }

        function arm_send_mail_for_automatic_subsciption_expire_reminder($message, $arm_plan_id, $endtime_start, $endtime_end, $now, $notifications=array(), $message_key=0) {
            global $wp, $wpdb, $ARMember, $arm_manage_communication, $arm_global_settings, $arm_subscription_plans;
            $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($arm_plan_id);
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'arm_user_plan_ids',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $users = get_users($args);

            if (!empty($users)) {
                foreach ($users as $user) {
                    $memberStatus = arm_get_member_status($user->ID);
                    $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        foreach ($plan_ids as $plan_id) {
                            if($arm_plan_id==$plan_id)
                            {
                                $planData = get_user_meta($user->ID, 'arm_user_plan_' . $plan_id, true);
                                if (!empty($planData)) {
                                    $arm_next_due_payment = $planData['arm_next_due_payment'];
                                    $payment_mode = $planData['arm_payment_mode'];
                                    if ($payment_mode != 'auto_debit_subscription') {
                                        continue;
                                    }
                                    if (!empty($arm_next_due_payment)) {
                                        if ($arm_next_due_payment > $now && $arm_next_due_payment <= $endtime_end) {
                                            $alreadysentmsgs = $planData['arm_sent_msgs'];
                                            $alreadysentmsgs = (!empty($alreadysentmsgs)) ? $alreadysentmsgs : array();

                                            $arm_user_complete_recurring_meta = $planData['arm_completed_recurring'];
                                            $arm_user_complete_recurring = isset($arm_user_complete_recurring_meta) ? $arm_user_complete_recurring_meta : 0;
                                            if (!in_array('automatic_subscription_reminder_' . $message->arm_message_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs)) {
                                                $subject = $arm_manage_communication->arm_filter_communication_content($message->arm_message_subject, $user->ID, $plan_id);
                                                $mailcontent = $arm_manage_communication->arm_filter_communication_content($message->arm_message_content, $user->ID, $plan_id);
                                                $send_one_copy_to_admin = $arm_manage_communication->arm_filter_communication_content($message->arm_message_send_copy_to_admin, $user->ID, $plan_id);

                                                $send_diff_copy_to_admin = $message->arm_message_send_diff_msg_to_admin;

                                                if ($message->arm_message_admin_message != '') {
                                                    $admin_content_description = $arm_manage_communication->arm_filter_communication_content($message->arm_message_admin_message, $user->ID, $plan_id);
                                                } else {
                                                    $admin_content_description = '';
                                                }

                                                $notify = $arm_global_settings->arm_wp_mail('', $user->data->user_email, $subject, $mailcontent);
                                                $send_mail = 0;
                                                if ($send_one_copy_to_admin == 1) {
                                                    if ($send_diff_copy_to_admin == 1) {
                                                        $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $admin_content_description);
                                                    } else {
                                                        $send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $subject, $mailcontent);
                                                    }
                                                }

                                                if ($notify) {
                                                    /* Update User meta for notification type */
                                                    $alreadysentmsgs[] = 'automatic_subscription_reminder_' . $message->arm_message_id . '_' . $arm_user_complete_recurring;
                                                    for ($i=0; $i<count($notifications); $i++) {
                                                        $arm_message_period_unit = $notifications[$i]->arm_message_period_unit;
                                                        if(!empty($arm_message_period_unit))
                                                        {
                                                            $endtime_chk = strtotime("+$arm_message_period_unit Days", $now);

                                                            if ($arm_next_due_payment <= $endtime_chk) {
                                                                if(!in_array('automatic_subscription_reminder_' . $notifications[$i]->arm_message_id . '_' . $arm_user_complete_recurring, $alreadysentmsgs))
                                                                {
                                                                        $alreadysentmsgs[] = 'automatic_subscription_reminder_' . $notifications[$i]->arm_message_id . '_' . $arm_user_complete_recurring;    
                                                                }
                                                            }
                                                        }
                                                    }

                                                    $planData['arm_sent_msgs'] = $alreadysentmsgs;
                                                    update_user_meta($user->ID, 'arm_user_plan_' . $plan_id, $planData);
                                                    $cron_msgs[$user->ID] = __("Mail successfully sent to", 'ARMember') . " " . $user->ID . " " . __("for before autoomatic subscription due.", 'ARMember') . "({$plan_name})";
                                                } else {
                                                    $cron_msgs[$user->ID] = __("There is an error in sending mail to", 'ARMember') . " " . $user->ID . " " . __("for before autoomatic subscription due.", 'ARMember') . "({$plan_name})";
                                                }

                                                if ($send_mail) {
                                                    $cron_msgs['admin_mail_for_' . $user->ID] = __("Mail successfully sent to admin", 'ARMember') . " for " . $user->ID . " " . __("for before autoomatic subscription due.", 'ARMember') . "({$plan_name})";
                                                } else {
                                                    $cron_msgs['admin_mail_for_' . $user->ID] = __("There is an error in sending mail to admin", 'ARMember') . " for " . $user->ID . " " . __("for before autoomatic subscription due.", 'ARMember') . "({$plan_name})";
                                                }

                                                do_action('arm_general_log_entry', 'cron', 'automatic subscription expire and email sent', 'armember', 'user_id='.$user->ID.', plan='.$plan_id.', next due payment='.$arm_next_due_payment.', sentmsg='.$alreadysentmsgs.', msg='.$cron_msgs[$user->ID].', notify='.$notify);

                                            } else {
                                                $cron_msgs[$user->ID] = __("Mail successfully sent to", 'ARMember') . " " . $user->ID . " " . __("for before autoomatic subscription due.", 'ARMember') . "({$plan_name})";
                                            }
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        function arm_update_user_achievements_func() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_members_badges;
            set_time_limit(0); /* Preventing timeout issue. */
            $now = current_time('timestamp');
            $cron_msgs = array();
            $arm_members_badges->arm_add_user_achieve_by_cron();
        }

        function arm_clear_cron($name = '') {
            global $ARMember;
            if (!empty($name)) {
                wp_clear_scheduled_hook($name);
            }
        }

        function arm_get_cron_hook_names() {
            $cron_array = array(
                'arm_handle_change_user_plan',
                'arm_handle_expire_subscription',
                'arm_handle_expire_infinite_subscription',
                'arm_handle_before_expire_subscription',
                'arm_handle_before_dripped_content_available',
                'arm_handle_renewal_reminder_of_subscription',
                'arm_handle_trial_finished',
                'arm_update_user_achievements',
                'arm_handle_failed_login_log_data_delete'
            );

            $cron_array = apply_filters('arm_filter_cron_hook_name_outside', $cron_array);

            $cron_array[] = 'arm_handle_failed_payment_for_manual_subscription';
            $cron_array[] = 'arm_handle_failed_payment_for_auto_subscription';

            $cron_array = apply_filters('arm_filter_cron_hook_name_after_outside', $cron_array);

            return $cron_array;
        }

    }

}

global $arm_crons;
$arm_crons = new ARM_crons();
