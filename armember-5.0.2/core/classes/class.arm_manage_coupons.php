<?php
if (!class_exists('ARM_manage_coupons'))
{
    class ARM_manage_coupons
    {
        var $isCouponFeature;
        function __construct()
        {
            global $wpdb, $ARMember, $arm_slugs;
            $is_coupon_feature = get_option('arm_is_coupon_feature', 0);
            $this->isCouponFeature = ($is_coupon_feature == '1') ? true : false;

            add_action('wp_ajax_arm_generate_code', array($this, 'arm_generate_code'));
            add_action('arm_admin_save_coupon_details', array($this, 'arm_admin_save_coupon_details'));
            add_action('wp_ajax_arm_apply_coupon_code', array($this, 'arm_apply_coupon_code'));
            add_action('wp_ajax_nopriv_arm_apply_coupon_code', array($this, 'arm_apply_coupon_code'));
            add_action('wp_ajax_arm_delete_single_coupon', array($this, 'arm_delete_single_coupon'));
            add_action('wp_ajax_arm_delete_bulk_coupons',array($this,'arm_delete_bulk_coupons'));
            add_action('wp_ajax_arm_update_coupons_status', array($this, 'arm_update_coupons_status'));
            add_action('wp_ajax_arm_get_coupon_members_data', array($this, 'arm_get_coupon_members_data_func'));

            //Load coupon data with ajax
            add_action('wp_ajax_arm_get_coupon_data', array($this, 'arm_load_coupon_data'));

            add_action( 'wp_ajax_arm_get_paid_post_item_coupon_options', array( $this, 'arm_get_paid_post_item_coupon_options' ) );
        }


        function arm_load_coupon_data()
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_slugs, $arm_payment_gateways, $arm_subscription_plans;

            $date_format = $arm_global_settings->arm_get_wp_date_format();

            $offset = isset( $_POST['iDisplayStart'] ) ? $_POST['iDisplayStart'] : 0;
            $limit = isset( $_POST['iDisplayLength'] ) ? $_POST['iDisplayLength'] : 10;

            $search_term = ( isset( $_POST['sSearch'] ) && '' != $_POST['sSearch'] ) ? true : false;

            $search_query = '';
            if( $search_term ){
                $search_query = "AND (arm_coupon_code LIKE '%".$_POST['sSearch']."%' )";
            }


            $sortOrder = isset( $_POST['sSortDir_0'] ) ? $_POST['sSortDir_0'] : 'DESC';

            $orderBy = 'ORDER BY  arm_coupon_id ' . $sortOrder;
            if( !empty( $_POST['iSortCol_0'] ) ){
                if( $_POST['iSortCol_0'] == 0 ){
                    $orderBy = 'ORDER BY arm_coupon_id ' . $sortOrder;
                }else if($_POST['iSortCol_0'] == 1){
                    $orderBy = 'ORDER BY arm_coupon_code ' . $sortOrder;
                }
            }

            $get_coupons = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` WHERE 1=1 {$search_query} {$orderBy}  LIMIT {$offset}, {$limit}");

            $total_coupons_query = "SELECT COUNT(arm_coupon_id) AS total FROM {$ARMember->tbl_arm_coupons} {$orderBy}";
            $total_coupons_result = $wpdb->get_results( $total_coupons_query );
            $total_coupons = $total_coupons_result[0]->total;

            $grid_data = array();
            $ai = 0;


            if( !empty( $get_coupons ))
            {
                $current_timestamp = current_time('timestamp');
                foreach ($get_coupons as $key => $coupon_val) 
                {
                    if( !isset($grid_data[$ai]) || !is_array( $grid_data[$ai] ) ){
                        $grid_data[$ai] = array();
                    }

                    $couponID = $coupon_val->arm_coupon_id;
                    $edit_link = admin_url('admin.php?page='.$arm_slugs->coupon_management.'&action=edit_coupon&coupon_eid='.$couponID);

                    
                    /**/
                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                    $grid_data[$ai][] = '<tr class="arm_coupons_tr_'.$couponID.' row_'.$couponID.'"><td class="center"><input class="chkstanard arm_bulk_select_single" type="checkbox" value="'.$couponID.'" name="item-action[]"></td>';

                    $grid_data[$ai][] = '<td>'.$coupon_val->arm_coupon_label.'</td>';

                    $grid_data[$ai][] = '<td><a href="'.$edit_link.'">'.stripslashes($coupon_val->arm_coupon_code).'</a></td>';

                    $grid_data[$ai][] = '<td class="center">'.$arm_payment_gateways->arm_amount_set_separator($global_currency, $coupon_val->arm_coupon_discount) . (($coupon_val->arm_coupon_discount_type != 'percentage') ? " " .$global_currency : "%").'</td>';
		    
		    $filter_data = "";
                    $filter_data = apply_filters('arm_add_new_coupon_field_body', $filter_data, $coupon_val);
                    if(!empty($filter_data))
                    {
                        $grid_data[$ai][] = $filter_data;
                    }

                    $arm_coupon_expire_date_class = "";
                    if($coupon_val->arm_coupon_period_type == 'daterange') {
                        if(strtotime($coupon_val->arm_coupon_expire_date) < $current_timestamp) {
                            $arm_coupon_expire_date_class = "arm_coupon_date_expire";
                        }
                        $arm_coupon_expire_date_val = date_i18n($date_format,strtotime($coupon_val->arm_coupon_expire_date));
                    }else {
                        $arm_coupon_expire_date_val = __('Unlimited', 'ARMember');
                    }

                    $grid_data[$ai][] = date_i18n($date_format,strtotime($coupon_val->arm_coupon_start_date));
                    $grid_data[$ai][] = '<td><span class="'.$arm_coupon_expire_date_class.'">'.$arm_coupon_expire_date_val.'</span></td>';

                    $switchChecked = ($coupon_val->arm_coupon_status == '1') ? 'checked="checked"' : '';
                    $grid_data[$ai][] = '<td class="center"><div class="armswitch"><input type="checkbox" class="armswitch_input arm_coupon_status_action" id="arm_coupon_status_input_'.$couponID.'" value="1" data-item_id="'.$couponID.'" '.$switchChecked.'><label class="armswitch_label" for="arm_coupon_status_input_'.$couponID.'"></label><span class="arm_status_loader_img"></span></div></td>';

                    $subs_plan_title = '';
                    $arm_coupon_type = isset($coupon_val->arm_coupon_type) ? $coupon_val->arm_coupon_type : 1;
                    $arm_coupon_subscription_plans = !empty($coupon_val->arm_coupon_subscription) ? @explode(',', $coupon_val->arm_coupon_subscription) : array();
                    $arm_coupon_paid_posts = !empty($coupon_val->arm_coupon_paid_posts) ? @explode(',', $coupon_val->arm_coupon_paid_posts) : array();

                    if($arm_coupon_type == 1)
                    {
                        if(!empty($arm_coupon_subscription_plans))
                        {
                            $exclude_paid_posts = 1;
                            $subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_subscription_plans, $exclude_paid_posts);
                            $subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '--';
                        }
                        else{
                            $subs_plan_title = __('All Membership Plans', 'ARMember');
                        }
                    }
                    else if($arm_coupon_type == 2)
                    {
                        if(!empty($arm_coupon_paid_posts))
                        {
                            $exclude_paid_posts = 0;
                            $subs_plan_title_data = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_paid_posts, $exclude_paid_posts);
                            $subs_plan_title = (!empty($subs_plan_title_data)) ? $subs_plan_title_data : '';
                        }
                        else
                        {
                            $subs_plan_title = __('All Paid Posts', 'ARMember');
                        }
                    }
                    else
                    {
                        if(empty($arm_coupon_subscription_plans) && empty($arm_coupon_paid_posts))
                        {
                            $subs_plan_title .= __('All Membership Plans and paid posts', 'ARMember');
                        }
                        else if(!empty($arm_coupon_subscription_plans) && empty($arm_coupon_paid_posts))
                        {
                            $exclude_paid_posts = 1;
                            $subs_plan_title_data = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_subscription_plans, $exclude_paid_posts);
                            $subs_plan_title .= (!empty($subs_plan_title_data)) ? $subs_plan_title_data : '';
                            
                            $subs_plan_title .= "<br>";
                            $subs_plan_title .= __('All Paid Posts', 'ARMember');
                        }
                        else if(empty($arm_coupon_subscription_plans) && !empty($arm_coupon_paid_posts))
                        {
                            $subs_plan_title .= __('All Membership Plans', 'ARMember');
                            $subs_plan_title .= "<br>";
                            $exclude_paid_posts = 0;
                            $subs_plan_title_data = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_paid_posts, $exclude_paid_posts);
                            $subs_plan_title .= (!empty($subs_plan_title_data)) ? $subs_plan_title_data : '--';
                        }
			else if(!empty($arm_coupon_subscription_plans) && !empty($arm_coupon_paid_posts))
                        {
                            $subs_plan_title_data = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_subscription_plans, $exclude_paid_posts);
                            $subs_plan_title .= (!empty($subs_plan_title_data)) ? $subs_plan_title_data : '';
                            $subs_plan_title .= "<br>";
                            $exclude_paid_posts = 0;
                            $subs_plan_title_data = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($arm_coupon_paid_posts, $exclude_paid_posts);
                            $subs_plan_title .= (!empty($subs_plan_title_data)) ? $subs_plan_title_data : '';
                        }
                    }

                    $grid_data[$ai][] = $subs_plan_title;

                    $used_coupon_cnt = $coupon_val->arm_coupon_used;
                    if($coupon_val->arm_coupon_used > 0) 
                    {
                        $used_coupon_cnt = '<a class="arm_coupon_members_list_detail" href="javascript:void(0);" data-list_id="'.$couponID.'">'.$coupon_val->arm_coupon_used.'</a>';
                    }
                    $grid_data[$ai][] = '<td>'.$used_coupon_cnt.'</td>';

                    $grid_data[$ai][] = '<td class="form_entries">'.(($coupon_val->arm_coupon_allowed_uses == 0) ? __('Unlimited', 'ARMember') : $coupon_val->arm_coupon_allowed_uses).'</td>';

                    $gridActionData = '<td class="armGridActionTD">';
                    $gridActionData .= '<div class="arm_grid_action_btn_container">';
                    $gridActionData .= '<a href="' . $edit_link . '"><img src="'.MEMBERSHIP_IMAGES_URL.'/grid_edit.png" onmouseover=\'this.src="'.MEMBERSHIP_IMAGES_URL.'/grid_edit_hover.png";\' class="armhelptip" title="'.__('Edit Coupon','ARMember').'" onmouseout=\'this.src="'.MEMBERSHIP_IMAGES_URL.'/grid_edit.png";\' /></a>';
                    $gridActionData .= '<a href="javascript:void(0)" onclick="showConfirmBoxCallback('.$couponID.');"><img src="'.MEMBERSHIP_IMAGES_URL.'/grid_delete.png" class="armhelptip" title="'.__('Delete','ARMember').'" onmouseover=\'this.src="'.MEMBERSHIP_IMAGES_URL.'/grid_delete_hover.png";\' onmouseout=\'this.src="'.MEMBERSHIP_IMAGES_URL.'/grid_delete.png";\' /></a>';
                    $gridActionData .= $arm_global_settings->arm_get_confirm_box($couponID, __("Are you sure you want to delete this coupon?", 'ARMember'), 'arm_coupon_delete_btn');
                    $gridActionData .= '</div>';

                    $grid_data[$ai][] = '<div class="arm_grid_action_wrapper">'.$gridActionData.'</div></tr>';


                    $ai++;
                }
            }

            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $after_filter = $total_coupons;
            if( $search_term ){
                $after_filter = $ai;
            }

            $response = array(
                'sColumns' => implode(',',array('Coupon Label','Coupon Code','Discount','Start Date', 'Expire Date', 'Active', 'Subscription', 'Used', 'Allowed Uses')),
                'sEcho' => $sEcho,
                'iTotalRecords' => $total_coupons,
                'iTotalDisplayRecords' => $after_filter,
                'aaData' => $grid_data
            );

            echo json_encode( $response );
            die;
        }




        function arm_apply_coupon_code($coupon_code = '', $plan_id = null, $setup_id = 0, $payment_cycle = 0 , $arm_user_old_plan = array())
        {
            global $wpdb, $ARMember, $arm_subscription_plans, $arm_global_settings, $arm_payment_gateways, $arm_membership_setup;
            $return = array(
                'status' => 'error',
                'message' => __('You can not redeem this coupon code right now.', 'ARMember'),
                'validity' => 'invalid_coupon',
                'coupon_amt' => 0,
                'total_amt' => 0,
                'discount' => 0,
                'discount_type' => '',
            );
            $err_empty_coupon = !empty($arm_global_settings->common_message['arm_empty_coupon']) ? $arm_global_settings->common_message['arm_empty_coupon'] : __('Please enter the coupon code', 'ARMember');

            $err_invalid_coupon = !empty($arm_global_settings->common_message['arm_invalid_coupon']) ? $arm_global_settings->common_message['arm_invalid_coupon'] : __('Coupon code is not valid', 'ARMember');

            $err_invalid_coupon_plan = !empty($arm_global_settings->common_message['arm_invalid_coupon_plan']) ? $arm_global_settings->common_message['arm_invalid_coupon_plan'] : __('Coupon code is not valid for the selected plan', 'ARMember');

            $err_coupon_expire = !empty($arm_global_settings->common_message['arm_coupon_expire']) ? $arm_global_settings->common_message['arm_coupon_expire'] : __('Coupon code has expired', 'ARMember');

            $success_coupon = !empty($arm_global_settings->common_message['arm_success_coupon']) ? $arm_global_settings->common_message['arm_success_coupon'] : __('Coupon has been successfully applied', 'ARMember');
     
            $gateway = (isset($_REQUEST['gateway']) && !empty($_REQUEST['gateway'])) ? sanitize_text_field($_REQUEST['gateway']) : '';
            $payment_mode = (isset($_REQUEST['payment_mode']) && !empty($_REQUEST['payment_mode'])) ? sanitize_text_field($_REQUEST['payment_mode']) : '';
            if ($this->isCouponFeature) {
                $reqCoupon = (isset($_REQUEST['coupon_code']) && !empty($_REQUEST['coupon_code'])) ? sanitize_text_field($_REQUEST['coupon_code']) : '';
                $reqPlanID = (isset($_REQUEST['plan_id']) && !empty($_REQUEST['plan_id'])) ? intval($_REQUEST['plan_id']) : 0;
                $reqSetupID = (isset($_REQUEST['setup_id']) && !empty($_REQUEST['setup_id'])) ? intval($_REQUEST['setup_id']) : 0;
                $reqUserOldPlan = (isset($_REQUEST['user_old_plan']) && !empty($_REQUEST['user_old_plan'])) ? explode(",",$_REQUEST['user_old_plan']) : 0;
                $paymentCycle = (isset($_REQUEST['payment_cycle']) && !empty($_REQUEST['payment_cycle']))? intval($_REQUEST['payment_cycle']) : 0;
                $coupon_code = (!empty($coupon_code)) ? $coupon_code : $reqCoupon;
                $couponData = $this->arm_get_coupon($coupon_code);
                $setupid = (!empty($setup_id)) ? $setup_id : $reqSetupID;
                $arm_user_old_plan =  !empty($arm_user_old_plan) ? $arm_user_old_plan : $reqUserOldPlan; 
                $payment_cycle = ($payment_cycle!= '') ? $payment_cycle : $paymentCycle;
                $is_used_as_invitation_code = false;
                $planAmt = 0;
                if($setupid != 0)
                {
                    $setup_data = $arm_membership_setup->arm_get_membership_setup($setupid);
                    
                     if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                         $setup_modules = $setup_data['setup_modules'];
                         $is_used_as_invitation_code= (isset($setup_modules['modules']['coupon_as_invitation']) && $setup_modules['modules']['coupon_as_invitation'] == 1) ? true : false;
                     }
                }
                if (!empty($couponData)) {
                    
                    $plan_id = ( null === $plan_id ) ? $reqPlanID : $plan_id;
                    if (is_object($plan_id)) {
                        $planObj = $plan_id;
                    } else {
                        $planObj = new ARM_Plan($plan_id);
                    }
                    if ($planObj->exists()) {
                        $plans = $couponData['arm_coupon_subscription'];
                        $allow_plan_ids = explode(',', $plans);
                        $paid_posts = $couponData['arm_coupon_paid_posts'];
                        $allow_post_ids = explode(',', $paid_posts);
                        $allowOnTrial = $couponData['arm_coupon_allow_trial'];
                        $user_count = $couponData['arm_coupon_used'];
                        $allowed_uses = $couponData['arm_coupon_allowed_uses'];
                        $arm_coupon_type = $couponData['arm_coupon_type'];
                        $arm_isPaidPost = (isset($planObj->isPaidPost) && $planObj->isPaidPost != 0 ) ? 1 : 0 ;
                        
                        if ($couponData['arm_coupon_status'] != '1') {
                            $return['message'] = $err_invalid_coupon;
                            $return['validity'] = 'invalid_coupon';
                        } elseif ($allowed_uses != 0 && $allowed_uses <= $user_count) {
                            $return['message'] = $err_coupon_expire;
                            $return['validity'] = 'expired';
                        } elseif ($couponData['arm_coupon_period_type'] == 'daterange' && time() < strtotime($couponData['arm_coupon_start_date'])) {
                            $return['message'] = $err_invalid_coupon;
                            $return['validity'] = 'invalid_coupon';
                        } elseif ($couponData['arm_coupon_period_type'] == 'daterange' && time() > strtotime($couponData['arm_coupon_expire_date'])) {
                            $return['message'] = $err_coupon_expire;
                            $return['validity'] = 'expired';
                        }elseif ($arm_coupon_type == 1 && (!empty($plans) && !in_array($planObj->ID, $allow_plan_ids) || $arm_isPaidPost == 1)) {
                            $return['message'] = $err_invalid_coupon_plan;
                            $return['validity'] = 'invalid_plan';
                        }elseif ($arm_coupon_type == 2 && (!empty($paid_posts) && !in_array($planObj->ID, $allow_post_ids) || $arm_isPaidPost == 0)) {
                            $return['message'] = $err_invalid_coupon_plan;
                            $return['validity'] = 'invalid_plan';
                        }elseif ($arm_coupon_type == 0 && $arm_isPaidPost == 0 && (!empty($plans) && !in_array($planObj->ID, $allow_plan_ids))){
                            $return['message'] = $err_invalid_coupon_plan;
                            $return['validity'] = 'invalid_plan';
                        }elseif ($arm_coupon_type == 0 && $arm_isPaidPost == 1 && (!empty($paid_posts) && !in_array($planObj->ID, $allow_post_ids))){
                            $return['message'] = $err_invalid_coupon;
                            $return['validity'] = 'invalid_coupon';
                        }else{
                            $arm_coupon_not_allowed_on_trial = 0;
                            
                            if($planObj->is_recurring()) {
                                if(isset($planObj->options['payment_cycles']) && !empty($planObj->options['payment_cycles'])) {
                                    $planAmt = str_replace(',','',$planObj->options['payment_cycles'][$payment_cycle]['cycle_amount']);
                                }
                                else {
                                    $planAmt = str_replace(',','',$planObj->amount);
                                }
                                $planAmt = str_replace(',','',$planAmt);
                            }
                            else {
                                $planAmt = str_replace(',','',$planObj->amount);
                            }
			    $planAmt = apply_filters('arm_modify_plan_amount_for_coupon', $planAmt, $planObj, $paymentCycle );
                            
                            if ($planObj->has_trial_period() && (empty($arm_user_old_plan) || $arm_user_old_plan == 0)) {
                                if ($allowOnTrial == '1') {
                                    $planAmt = !empty($planObj->options['trial']['amount']) ? $planObj->options['trial']['amount'] : 0;
                                }
                                else {
                                    $planAmt = 0;
                                    $arm_coupon_not_allowed_on_trial = 1;
                                }
                            }

                            if ((!empty($planAmt) && $planAmt != 0 && $arm_coupon_not_allowed_on_trial == 0) || (!empty($couponData['arm_coupon_on_each_subscriptions']) && $arm_coupon_not_allowed_on_trial == 0)) {
                                do_action('arm_before_apply_coupon_code', $coupon_code, $planObj->ID);
                                $couponAmt = $couponData['arm_coupon_discount'];
                                if ($couponData['arm_coupon_discount_type'] == 'percentage') {
                                    $couponAmt = ($planAmt * $couponAmt) / 100;
                                }
                                $discount_amount = floatval(str_replace(',','',$planAmt));
                                if (!empty($couponAmt) && $couponAmt > 0) {
                                    if($couponAmt > $discount_amount){
                                        $couponAmt = $planAmt;
                                        $discount_amount = '0';
                                    } else {
                                        $discount_amount = $discount_amount - $couponAmt;
                                    }
                                }
                                
				                //Group Membership addon Discount calculate if the selected child user not empty.
                                if(!empty($_REQUEST['arm_selected_child_users'])){
                                    $_REQUEST['armgm'] = $_REQUEST['arm_selected_child_users'];
                                }

                                $discount_amount = apply_filters('arm_modify_coupon_pricing', $discount_amount, $planObj, $planAmt, $couponAmt);

                                
                                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                $couponAmt = $arm_payment_gateways->arm_amount_set_separator($global_currency, $couponAmt, true);
                                $discount_amount = $arm_payment_gateways->arm_amount_set_separator($global_currency, $discount_amount);
                                $final_amount = $discount_amount . ' ' . $global_currency;
                                $return = array (
                                    'status' => 'success', 'message' => $success_coupon,
                                    'coupon_amt' => $couponAmt, 'total_amt' => $discount_amount,
                                    'discount_type' => $couponData['arm_coupon_discount_type'],
                                    'discount' => $couponData['arm_coupon_discount'],
                                    'arm_coupon_on_each_subscriptions' => $couponData['arm_coupon_on_each_subscriptions'],
                                );
                                do_action('arm_after_apply_coupon_code', $coupon_code, $planObj->ID);
                            } else {
                                if(($planAmt == 0 && $is_used_as_invitation_code == true) || (!empty($couponData['arm_coupon_on_each_subscriptions']) && $arm_coupon_not_allowed_on_trial == 0) )
                                {
                                    $couponAmt = $couponData['arm_coupon_discount'];
                                    $discount_amount = $planAmt;
                                    $return = array (
                                    'status' => 'success', 'message' => $success_coupon,
                                    'discount_type' => $couponData['arm_coupon_discount_type'],
                                    'discount' => $couponData['arm_coupon_discount'],
                                    'arm_coupon_on_each_subscriptions' => $couponData['arm_coupon_on_each_subscriptions'],
                                    );
                                }
                                else {
                                    $return['message'] = $err_invalid_coupon_plan;
                                    $return['validity'] = 'invalid_plan';
                                }
                            }
                        }
                    } else {
                        $return['message'] = $err_invalid_coupon;
                        $return['validity'] = 'invalid_coupon';
                    }
                } else {
                    $return['message'] = $err_invalid_coupon;
                    $return['validity'] = 'invalid_coupon';
                }

                $planObj = isset($planObj) ? $planObj : '';
                if(isset($planObj->type) && 'recurring' != $planObj->type){
                    $payment_mode = "";
                }

                /* Modify Coupon Code outside from plugin */
                $return = apply_filters('arm_change_coupon_code_outside_from_'.$gateway,$return,$payment_mode,$couponData,$planAmt,$planObj);
            }

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_apply_coupon_code') {
                do_action('arm_restrict_specific_coupon_code', $coupon_code);
                echo json_encode($return);
                exit;
            }

            return $return;
        }
        function arm_redeem_coupon_html($content = '', $labels = array(), $plan_data = array(), $btn_style_class = '', $is_used_as_invitation_code = false , $setupRandomID = '',$formPosition = 'left')
        {
            global $wpdb, $ARMember, $arm_subscription_plans, $arm_global_settings;
            if ($this->isCouponFeature) {
                $coupon_code = (!empty($_REQUEST['arm_coupon_code'])) ? $_REQUEST['arm_coupon_code'] : '';
                $plan_id = (isset($plan_data['arm_subscription_plan_id'])) ? $plan_data['arm_subscription_plan_id'] : 0;
                $check_coupon = $this->arm_apply_coupon_code($coupon_code, $plan_id);
                
                $err_empty_coupon = !empty($arm_global_settings->common_message['arm_empty_coupon']) ? $arm_global_settings->common_message['arm_empty_coupon'] : __('Please enter the coupon code', 'ARMember');

            $err_invalid_coupon = !empty($arm_global_settings->common_message['arm_invalid_coupon']) ? $arm_global_settings->common_message['arm_invalid_coupon'] : __('Coupon code is not valid', 'ARMember');

            $err_invalid_coupon_plan = !empty($arm_global_settings->common_message['arm_invalid_coupon_plan']) ? $arm_global_settings->common_message['arm_invalid_coupon_plan'] : __('Coupon code is not valid for the selected plan', 'ARMember');

            $err_coupon_expire = !empty($arm_global_settings->common_message['arm_coupon_expire']) ? $arm_global_settings->common_message['arm_coupon_expire'] : __('Coupon code has expired', 'ARMember');

            $success_coupon = !empty($arm_global_settings->common_message['arm_success_coupon']) ? $arm_global_settings->common_message['arm_success_coupon'] : __('Coupon has been successfully applied', 'ARMember');
            
                $couponMessages = '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($err_empty_coupon) . '</div>';
                $couponMessages .= '<div data-ng-message="invalid_coupon" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($err_invalid_coupon) . '</div>';
                $couponMessages .= '<div data-ng-message="invalid_plan" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($err_invalid_coupon_plan) . '</div>';
                $couponMessages .= '<div data-ng-message="expired" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($err_coupon_expire) . '</div>';
                
                $coupon_code_message = '';
                if ($check_coupon['status'] == 'success' && $check_coupon['plan_type'] != 'free') {
                    $coupon_code_message = '<span class="success notify_msg">' . $check_coupon['message'] . '</span>';
                } else {
                    $coupon_code = '';
                }
                $title_text = (!empty($labels['title'])) ? stripslashes_deep($labels['title']) : __('Have a coupon code?', 'ARMember');
                $button_text = (!empty($labels['button'])) ? $labels['button'] : __('Apply', 'ARMember');
                $content = apply_filters('arm_before_redeem_coupon_section', $content);
                $couponBoxID = arm_generate_random_code(20);
                
                switch($formPosition){
                    case 'left':
                        $coupon_style = $coupon_submit_style = "float:left;";
                        break;
                    case 'center':
                        $coupon_style = "float:none;margin:0 auto -6px !important;";
                        $coupon_submit_style = "float:none;";
                        break;
                    case 'right':
                        $coupon_style = $coupon_submit_style = "float:right;";
                        break;
                }
                
                $content .= '<div class="arm_apply_coupon_container arm_position_'.$formPosition.'" id="'.$couponBoxID.'">';
                    $coupon_style = "";
                    $coupon_submit_style = "";
                        $content .= '<div class="arm_coupon_field_wrapper arm_form_field_container arm_form_field_container_text" style="'.$coupon_style.'">';
                            $content .= '<div class="arm_form_input_wrapper">';
                                $content .= '<div class="arm_form_input_container arm_form_input_container_coupon_code">';
                                    $content .= '<md-input-container class="md-block" flex-gt-sm="">';
                                        if($is_used_as_invitation_code == true){
                                            $couponInputAttr = ' data-ng-required="isCouponRequired(\'#arm_setup_form_'.$setupRandomID.'\')" data-isRequiredCoupon="true" ';
                                        } else {
                                            $couponInputAttr = ' data-isRequiredCoupon="false" ';
                                        }
                                        $content .= '<label class="arm_material_label">' . $title_text . '</label>';
                                        $content .= '<input type="text" name="arm_coupon_code" data-ng-model="arm_form.coupon_code_val" value="'.$coupon_code.'" class="field_coupon_code arm_coupon_code" data-checkcouponcode="1" '.$couponInputAttr.' >';
                                        $content .= '<div data-ng-cloak data-ng-messages="arm_form.arm_coupon_code.$error" class="arm_error_msg_box ng-scope">';
                                            $content .= $couponMessages;
                                        $content .= '</div>';
                                    $content .= '</md-input-container>';
                                    $content .= $coupon_code_message;
                                $content .= '</div>';
                            $content .= '</div>';
                        $content .= '</div>';
                        $content .= '<div class="arm_coupon_submit_wrapper arm_form_field_container arm_form_field_container_submit" style="'.$coupon_submit_style.'">';
                            $content .= '<div class="arm_form_input_wrapper">';
                                $content .= '<div class="arm_form_input_container_submit arm_form_input_container" id="arm_setup_coupon_button_container">';
                                $content .= '<md-button type="button" class="arm_apply_coupon_btn arm_form_field_submit_button arm_form_field_container_button arm_form_input_box arm_material_input '.$btn_style_class.'"><span class="arm_spinner">'.file_get_contents(MEMBERSHIP_IMAGES_DIR."/loader.svg").'</span>' . esc_html(stripslashes($button_text)) . '</md-button>';
                                $content .= '</div>';
                            $content .= '</div>';
                        $content .= '</div>';
                    $content .= '</div>';
                $content = apply_filters('arm_after_redeem_coupon_section', $content);
            }
            return $content;
        }
        function arm_generate_coupon_code()
        {
            $couponCode = '';
            if (function_exists('arm_generate_random_code')) {
                $couponCode = arm_generate_random_code(8);
            } else {
                $coupon_char = array();
                $coupon_char[] = array('count' => 6, 'char' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
                $coupon_char[] = array('count' => 2, 'char' => '0123456789');
                $temp_array = array();
                foreach ($coupon_char as $char_set) {
                    for ($i = 0; $i < $char_set['count']; $i++) {
                        $temp_array[] = $char_set['char'][rand(0, strlen($char_set['char']) - 1)];
                    }
                }
                shuffle($temp_array);
                $couponCode = implode('', $temp_array);
            }
            return $couponCode;
        }
        function arm_generate_code()
        {
            global $wpdb, $ARMember, $arm_slugs, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $arm_code = $this->arm_generate_coupon_code();

            $old_coupon =  $this->arm_get_coupon($arm_code);
            if (!empty($old_coupon) && is_array($old_coupon)) {
                $this->arm_generate_code();
            } else {
                $response = array('arm_coupon_code' => $arm_code);
                echo json_encode($response);
            }
            die();
        }
        function arm_admin_save_coupon_details($coupon_data = array()) {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            $op_type = $coupon_data['op_type'];
            
            $coupon_code = (isset($coupon_data['arm_coupon_code']) && !empty($coupon_data['arm_coupon_code'])) ? sanitize_text_field($coupon_data['arm_coupon_code']) : '';
            $coupon_discount = (isset($coupon_data['arm_coupon_discount'])) ? $coupon_data['arm_coupon_discount'] : '';
            $arm_coupon_on_each_subscriptions = (isset($coupon_data['arm_coupon_on_each_subscriptions'])) ? intval($coupon_data['arm_coupon_on_each_subscriptions']) : 0;
            $coupon_discount_type = (isset($coupon_data['arm_discount_type']) && !empty($coupon_data['arm_discount_type'])) ? sanitize_text_field($coupon_data['arm_discount_type']) : '';
            $coupon_label = (isset($coupon_data['arm_coupon_label']) && !empty($coupon_data['arm_coupon_label'])) ? sanitize_text_field($coupon_data['arm_coupon_label']) : '';
            $coupon_period_type = (isset($coupon_data['arm_coupon_period_type']) && !empty($coupon_data['arm_coupon_period_type'])) ? sanitize_text_field($coupon_data['arm_coupon_period_type']) : 'daterange';
            $coupon_start = (isset($coupon_data['arm_coupon_start_date']) && !empty($coupon_data['arm_coupon_start_date'])) ? $coupon_data['arm_coupon_start_date'] : date('Y-m-d');
            $coupon_expire = (isset($coupon_data['arm_coupon_expire_date']) && !empty($coupon_data['arm_coupon_expire_date'])) ? $coupon_data['arm_coupon_expire_date'] : date('Y-m-d');
            $coupon_status = (isset($coupon_data['arm_coupon_status']) && !empty($coupon_data['arm_coupon_status'])) ? intval($coupon_data['arm_coupon_status']) : 0;
            $coupon_allow_trial = (isset($coupon_data['arm_coupon_allow_trial']) && !empty($coupon_data['arm_coupon_allow_trial'])) ? intval($coupon_data['arm_coupon_allow_trial']) : 0;
            $coupon_subscription = (isset($coupon_data['arm_subscription_coupons']) && !empty($coupon_data['arm_subscription_coupons'])) ? $coupon_data['arm_subscription_coupons'] : array();                  

            $paid_post_coupon_subscription = (isset( $coupon_data['arm_paid_post_item_id'] ) && !empty($coupon_data['arm_paid_post_item_id']) )  ? $coupon_data['arm_paid_post_item_id'] : array();

            $arm_coupon_type = isset($coupon_data['arm_coupon_type']) ? $coupon_data['arm_coupon_type'] : 0;
            
            $coupon_subscription = (!empty($coupon_subscription)) ? @implode(',', $coupon_subscription) : '';
            $paid_post_coupon_subscription = (!empty($paid_post_coupon_subscription)) ? @implode(',', $paid_post_coupon_subscription) : '';
            $coupon_allowed_uses = (!empty($coupon_data['arm_allowed_uses']) && is_numeric($coupon_data['arm_allowed_uses'])) ? $coupon_data['arm_allowed_uses'] : 0;
            $coupon_apply_to = (isset($coupon_data['arm_coupon_apply_to']) && !empty($coupon_data['arm_coupon_apply_to'])) ? $coupon_data['arm_coupon_apply_to'] : '';
            $coupon_start_date = date('Y-m-d H:i:s', strtotime($coupon_start));
            $coupon_expire_date = date('Y-m-d 23:59:59', strtotime($coupon_expire));
            if ($coupon_period_type == 'unlimited') {
                $coupon_start_date = date('Y-m-d H:i:s');
                $coupon_expire_date = date('Y-m-d 23:59:59');
            }

            $c_where = '';
            if ($op_type == 'edit' && !empty($coupon_data['arm_edit_coupon_id']) && $coupon_data['arm_edit_coupon_id'] != 0) {
                $c_where = " AND `arm_coupon_id` != '" . $coupon_data['arm_edit_coupon_id'] . "' ";
            }
            $old_coupon =  $this->arm_get_coupon($coupon_code, $c_where);
            $check_status = 0;
            if (!empty($old_coupon) && is_array($old_coupon)) {
                $check_status = 1;
            }

            $coupons_values = array(
                'arm_coupon_code' => $coupon_code,
                'arm_coupon_label' => $coupon_label,
                'arm_coupon_discount' => $coupon_discount,
                'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                'arm_coupon_discount_type' => $coupon_discount_type,
                'arm_coupon_period_type' => $coupon_period_type,
                'arm_coupon_start_date' => $coupon_start_date,
                'arm_coupon_expire_date' => $coupon_expire_date,
                'arm_coupon_subscription' => $coupon_subscription,
                'arm_coupon_paid_posts' => $paid_post_coupon_subscription,
                'arm_coupon_allow_trial' => $coupon_allow_trial,
                'arm_coupon_allowed_uses' => $coupon_allowed_uses,
                'arm_coupon_status' => $coupon_status,
                'arm_coupon_type' => $arm_coupon_type,
                'arm_coupon_added_date' => date('Y-m-d H:i:s')
            );
            $coupons_values = apply_filters( 'arm_before_admin_save_coupon', $coupons_values, $coupon_data );
            if($op_type == 'bulk_add' && isset($coupon_data['arm_coupon_code_type']) && !empty($coupon_data['arm_coupon_code_type']) && isset($coupon_data['arm_coupon_quantity']) && !empty($coupon_data['arm_coupon_quantity']) && isset($coupon_data['arm_coupon_code_length']) && !empty($coupon_data['arm_coupon_code_length'])){
                for($c=0;$c<$coupon_data['arm_coupon_quantity'];$c++) {
                     $arm_coupon_code=$this->arm_bulk_generate_code($coupon_data['arm_coupon_code_length'],$coupon_data['arm_coupon_code_type']); 
                     $coupons_values['arm_coupon_code']=$arm_coupon_code;
                     $ins = $wpdb->insert($ARMember->tbl_arm_coupons, $coupons_values);
                }
                
            }
            if ($op_type == 'add') {
                if ($check_status != 1) {
                    $ins = $wpdb->insert($ARMember->tbl_arm_coupons, $coupons_values);
                    if ($ins) {
                        $message = __('Coupon Added Successfully.', 'ARMember');
                        $status = 'success';
                        $coupon_id = $wpdb->insert_id;
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $coupon_id);
                    } else {
                        $message = __('Error Adding Coupons, Please Again Try Again.', 'ARMember');
                        $status = 'failed';
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=add_coupon');
                    }
                } else {
                    $message = __('Could Not Perform The Operation, Because Coupon Code Already Exists.', 'ARMember');
                    $status = 'failed';
                    $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=add_coupon');
                }
            }else if ($op_type == 'bulk_add') {
                if ($check_status != 1) {
                    $message = __('Coupons Added Successfully.', 'ARMember');
                    $status = 'success';
                    $edit_coupon_link = admin_url('admin.php?page='.$arm_slugs->coupon_management);
                }    
            } else {

                $c_id = $coupon_data['arm_edit_coupon_id'];
                if ($check_status != 1) {
                    $where = array('arm_coupon_id' => $c_id);
                    $up = $wpdb->update($ARMember->tbl_arm_coupons, $coupons_values, $where);
                    if ($up) {
                        $message = __('Coupon Updated Successfully.', 'ARMember');
                        $status = 'success';
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                    } else {
                        $message = __('Error Updating Coupons, Please Again Try Again.', 'ARMember');
                        $status = 'failed';
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                    }
                } else {
                    $message = __('Could Not Perform The Operation, Because Coupon Code Already Exists.', 'ARMember');
                    $status = 'failed';
                    $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                }
            }

            $ARMember->arm_set_message($status, __($message, 'ARMember'));
            if (!empty($edit_coupon_link)) {
                wp_redirect($edit_coupon_link);
                exit;
            }
        }
        function arm_op_coupons()
        {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            $op_type = $_REQUEST['op_type'];
            $coupon_code = (isset($_POST['arm_coupon_code']) && !empty($_POST['arm_coupon_code'])) ? $_POST['arm_coupon_code'] : '';
            $coupon_discount = (isset($_POST['arm_coupon_discount']) && !empty($_POST['arm_coupon_discount'])) ? $_POST['arm_coupon_discount'] : '';                
            $coupon_discount_type = (isset($_POST['arm_discount_type']) && !empty($_POST['arm_discount_type'])) ? $_POST['arm_discount_type'] : '';
            $coupon_period_type = (isset($_POST['arm_coupon_period_type']) && !empty($_POST['arm_coupon_period_type'])) ? $_POST['arm_coupon_period_type'] : 'daterange';                               
            $coupon_start = (isset($_POST['arm_coupon_start_date']) && !empty($_POST['arm_coupon_start_date'])) ? $_POST['arm_coupon_start_date'] : date('Y-m-d');
            $coupon_expire = (isset($_POST['arm_coupon_expire_date']) && !empty($_POST['arm_coupon_expire_date'])) ? $_POST['arm_coupon_expire_date'] : date('Y-m-d');          
            $coupon_status = (isset($_POST['arm_coupon_status']) && !empty($_POST['arm_coupon_status'])) ? $_POST['arm_coupon_status'] : 0;
            $coupon_allow_trial = (isset($_POST['arm_coupon_allow_trial']) && !empty($_POST['arm_coupon_allow_trial'])) ? $_POST['arm_coupon_allow_trial'] : 0;
            $coupon_subscription = (isset($_POST['arm_subscription_coupons']) && !empty($_POST['arm_subscription_coupons'])) ? $_POST['arm_subscription_coupons'] : '';
            $coupon_subscription = (!empty($coupon_subscription)) ? @implode(',', $coupon_subscription) : '';
            $coupon_allowed_uses = (!empty($_POST['arm_allowed_uses']) && is_numeric($_POST['arm_allowed_uses'])) ? $_POST['arm_allowed_uses'] : 0;
            $coupon_apply_to = (isset($_POST['arm_coupon_apply_to']) && !empty($_POST['arm_coupon_apply_to'])) ? $_POST['arm_coupon_apply_to'] : '';
            $coupon_start_date = date('Y-m-d H:i:s', strtotime($coupon_start));
            $coupon_expire_date = date('Y-m-d 23:59:59', strtotime($coupon_expire));
            if ($coupon_period_type == 'unlimited') {
                $coupon_start_date = date('Y-m-d H:i:s');
                $coupon_expire_date = date('Y-m-d 23:59:59');
            }
            
            $c_where = '';
            if ($op_type == 'edit' && !empty($_REQUEST['arm_edit_coupon_id']) && $_REQUEST['arm_edit_coupon_id'] != 0) {
                $c_where = " AND `arm_coupon_id` != '" . $_REQUEST['arm_edit_coupon_id'] . "'";
            }
            $old_coupon =  $this->arm_get_coupon($coupon_code, $c_where);
            $check_status = 0;
            if (!empty($old_coupon) && is_array($old_coupon)) {
                $check_status = 1;
            }
            
            $coupons_values = array(
                'arm_coupon_code' => $coupon_code,
                'arm_coupon_discount' => $coupon_discount,
                'arm_coupon_discount_type' => $coupon_discount_type,
                'arm_coupon_period_type' => $coupon_period_type,
                'arm_coupon_start_date' => $coupon_start_date,
                'arm_coupon_expire_date' => $coupon_expire_date,
                'arm_coupon_subscription' => $coupon_subscription,
                'arm_coupon_allow_trial' => $coupon_allow_trial,
                'arm_coupon_allowed_uses' => $coupon_allowed_uses,
                'arm_coupon_status' => $coupon_status,
                'arm_coupon_added_date' => date('Y-m-d H:i:s')
            );
            
            if ($op_type == 'add')
            {
                if ($check_status != 1) {
                    $ins = $wpdb->insert($ARMember->tbl_arm_coupons, $coupons_values);
                    if ($ins) {
                        $message = __('Coupon Added Successfully.', 'ARMember');
                        $status = 'success';
                        $coupon_id = $wpdb->insert_id;
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $coupon_id);                                       
                    } else {
                        $message = __('Error Adding Coupons, Please Again Try Again.', 'ARMember');
                        $status = 'failed';
                        $edit_coupon_link = '';
                    }               
                } else {
                    $message = __('Could Not Perform The Operation, Because Coupon Code Already Exists.', 'ARMember');
                    $status = 'failed';
                    $edit_coupon_link = '';
                }
            } else {
                $c_id = $_REQUEST['arm_edit_coupon_id'];
                if ($check_status != 1) {                   
                    $where = array('arm_coupon_id' => $c_id);
                    $up = $wpdb->update($ARMember->tbl_arm_coupons, $coupons_values, $where);
                    if ($up) {
                        $message = __('Coupon Updated Successfully.', 'ARMember');
                        $status = 'success';
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                    } else {
                        $message = __('Error Updating Coupons, Please Again Try Again.', 'ARMember');
                        $status = 'failed';
                        $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                    }
                } else {
                    $message = __('Could Not Perform The Operation, Because Coupon Code Already Exists.', 'ARMember');
                    $status = 'failed';
                    $edit_coupon_link = admin_url('admin.php?page=' . $arm_slugs->coupon_management . '&action=edit_coupon&coupon_eid=' . $c_id);
                }   
            }
            $response = array('status' => $status, 'message' => $message, 'url' => $edit_coupon_link);
            echo json_encode($response);
            die();
        }
        function arm_update_coupons_status()
        {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $response = array('type'=>'error', 'msg'=>__('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['arm_coupon_id']) && $_POST['arm_coupon_id'] != 0)
            {
                $coupon_id = intval($_POST['arm_coupon_id']);
                $arm_coupon_status = (!empty($_POST['arm_coupon_status'])) ? intval($_POST['arm_coupon_status']) : 0;
                $coupons_values = array(
                    'arm_coupon_status' => $arm_coupon_status,
                );
                $update_temp = $wpdb->update($ARMember->tbl_arm_coupons, $coupons_values, array('arm_coupon_id' => $coupon_id));
                $response = array('type'=>'success', 'msg'=>__('Coupon Updated Successfully.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }
        function arm_get_coupon($coupon_code = '', $where_condition='')
        {
            global $wpdb, $ARMember, $arm_slugs;
            $coupon_detail = FALSE;
            if (!empty($coupon_code)) {
                //$coupon_detail = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` WHERE `arm_coupon_code` LIKE '$coupon_code'", ARRAY_A);
                $coupon_details = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` WHERE `arm_coupon_code`='$coupon_code' {$where_condition}", ARRAY_A);
                if (empty($coupon_details)) {
                    $coupon_detail = FALSE;
                } else {
                    $ismatchedCoupon = FALSE;
                    foreach($coupon_details as $coupon_detail) {
                        $couponCodeDB = $coupon_detail['arm_coupon_code'];
                        if( $couponCodeDB == $coupon_code  ){
                            $ismatchedCoupon = TRUE;
                            break;
                        }
                    }
                    if($ismatchedCoupon==FALSE)
                    {
                        $coupon_detail = FALSE;
                    }
                }
            }
            return $coupon_detail;
        }

        function arm_get_coupon_by_id($coupon_id = '')
        {
            global $wpdb, $ARMember;
            $coupon_detail = FALSE;
            if (!empty($coupon_id)) {
                $coupon_detail = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` WHERE `arm_coupon_id` = $coupon_id", ARRAY_A);
                if (!empty($coupon_detail)) {
                    $couponIdDB = $coupon_detail['arm_coupon_id'];
                    if( $couponIdDB != $coupon_id  ){
                        $coupon_detail = FALSE;
                    }
                }
            }
            return $coupon_detail;
        }
        function arm_update_coupon_used_count($coupon_code = '')
        {
            global $wpdb, $ARMember, $arm_slugs;
            if (!empty($coupon_code)) {
                $arm_check_coupon_details = $this->arm_get_coupon($coupon_code);
                if(!empty($arm_check_coupon_details) && is_array($arm_check_coupon_details))
                {
                    $coupon_id = $arm_check_coupon_details['arm_coupon_id'];
                    $used_coupons = $wpdb->get_results("UPDATE `" . $ARMember->tbl_arm_coupons . "` SET `arm_coupon_used` = `arm_coupon_used`+1 WHERE `arm_coupon_id` = '$coupon_id' ");
                    return $used_coupons;
                }

            }
            return FALSE;
            
        }
        function arm_get_coupon_amount($coupon_code, $payment_amount = 0, $plan_id = 0)
        {
            global $wpdb, $ARMember, $arm_slugs;
            $coupon_amount = 0;
            if ($this->isCouponFeature) {
                $coupon_detail = $this->arm_get_coupon($coupon_code);
                if ($coupon_detail !== FALSE) {
                    if ($coupon_detail['arm_coupon_discount'] != 0) {
                        $plans = $coupon_detail['arm_coupon_subscription'];
                        $allow_plan_ids = explode(',', $plans);
                        //$user_count = $this->arm_get_used_coupon_count($coupon_code);
                        $user_count = $coupon_detail['arm_coupon_used'];
                        $allowed_uses = $coupon_detail['arm_coupon_allowed_uses'];
                        if ($coupon_detail['arm_coupon_status'] != '1') {
                            $coupon_amount = 0;
                        } elseif ($allowed_uses != 0 && $allowed_uses <= $user_count) {
                            $coupon_amount = 0;
                        } elseif ($coupon_detail['arm_coupon_period_type'] == 'daterange' && time() < strtotime($coupon_detail['arm_coupon_start_date'])) {
                            $coupon_amount = 0;
                        } elseif ($coupon_detail['arm_coupon_period_type'] == 'daterange' && time() > strtotime($coupon_detail['arm_coupon_expire_date'])) {
                            $coupon_amount = 0;
                        } elseif (!empty($plans) && !in_array($plan_id, $allow_plan_ids)) {
                            $coupon_amount = 0;
                        } else {
                            $coupon_amount = $coupon_detail['arm_coupon_discount'];
                            if ($coupon_detail['arm_coupon_discount_type'] == 'percentage') {
                                $coupon_amount = ($payment_amount * $coupon_amount) / 100;
                            }
                            $coupon_amount = number_format((float) $coupon_amount, 2);
                        }
                    }
                }
            }
            return $coupon_amount;
        }
        function arm_get_used_coupon_count($coupon_code = '')
        {
            global $wpdb, $ARMember, $arm_slugs;
            $used_count = 0;
            $coupon_detail = $this->arm_get_coupon($coupon_code);
            if(!empty($coupon_detail) && is_array($coupon_detail))
            {
                $used_count = $coupon_detail['arm_coupon_used'];
            }
            return $used_count;
        }
        function arm_get_all_coupons()
        {
            global $wpdb, $ARMember, $arm_slugs;
            return $row = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` ORDER BY `arm_coupon_id` DESC");
        }
        function arm_total_coupons()
        {
            global $wpdb, $ARMember;
            $coupon_count = $wpdb->get_var("SELECT COUNT(`arm_coupon_id`) FROM `" . $ARMember->tbl_arm_coupons . "`");
            return $coupon_count;
        }
        function arm_delete_single_coupon()
        {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $action = sanitize_text_field($_POST['act']);
            $id = intval($_POST['id']);
            if( $action == 'delete' )
            {
                if (empty($id)) {
                    $errors[] = __('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_coupons')) {
                        $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                    } else {
                        $res_var = $wpdb->delete($ARMember->tbl_arm_coupons, array('arm_coupon_id' => $id));
                        if ($res_var) {
                            $message = __('Coupon has been deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }
        function arm_delete_bulk_coupons()
        {
            if (!isset($_POST)) {
                return;
            }
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $bulkaction = $arm_global_settings->get_param('action1');
            if ($bulkaction == -1) {
                $bulkaction = $arm_global_settings->get_param('action2');
            }
            $ids = $arm_global_settings->get_param('item-action', '');
            if (empty($ids))
            {
                $errors[] = __('Please select one or more records.', 'ARMember');
            } else {
                if (!current_user_can('arm_manage_coupons')) {
                    $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                } else {
                    if (!is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    if (is_array($ids)) {
                        if ($bulkaction == 'delete_coupon') {
                            foreach ($ids as $coupon_id) {
                                $res_var = $wpdb->delete($ARMember->tbl_arm_coupons, array('arm_coupon_id' => $coupon_id));
                            }
                            if ($res_var) {
                                $message = __('Coupon(s) has been deleted successfully.', 'ARMember');
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

        function arm_coupon_apply_to_subscription($user_ID, $log_detail,$pgateway,$userPlanData)
        {
            global $wp, $wpdb, $ARMember, $arm_manage_coupons;

            $log_id = isset($log_detail->arm_log_id) ? $log_detail->arm_log_id : $log_detail;
                
            if(!empty($log_id))
            {
                
                $armLogTable = $ARMember->tbl_arm_payment_log;
                
                
                $arm_current_plan_detail = !empty($userPlanData['arm_current_plan_detail']) ? $userPlanData['arm_current_plan_detail'] : '';
                if(!empty($arm_current_plan_detail))
                {
                    if(MEMBERSHIP_DEBUG_LOG == true) {
                        $ARMember->arm_write_response("ARMember COUPON LOG 1 : arm_coupon_apply_to_subscription plan detail : ".maybe_serialize($arm_current_plan_detail));
                    }
                    $arm_subscription_plan_type = isset($arm_current_plan_detail['arm_subscription_plan_type']) ? $arm_current_plan_detail['arm_subscription_plan_type'] : '';
                    $arm_subscription_plan_id = isset($arm_current_plan_detail['arm_subscription_plan_id']) ? $arm_current_plan_detail['arm_subscription_plan_id'] : '';
                    $arm_subscription_plan_options = isset($arm_current_plan_detail['arm_subscription_plan_options']) ? maybe_unserialize($arm_current_plan_detail['arm_subscription_plan_options']) : '';
                    if($arm_subscription_plan_type=='recurring')
                    {
                        if(MEMBERSHIP_DEBUG_LOG == true) {
                            $ARMember->arm_write_response("ARMember COUPON LOG 2 : arm_coupon_apply_to_subscription inside recurring plan");
                        }
                        $user_subscription_payment_cycle = get_user_meta($user_ID, 'payment_cycle_'.$arm_subscription_plan_id, true);
                        $userPlanData = get_user_meta($user_ID, 'arm_user_plan_'.$arm_subscription_plan_id, true);
                        
                        if($user_subscription_payment_cycle=='') {
                            $user_subscription_payment_cycle = 0;
                        }
                        $arm_subscription_plan_amount = $arm_current_plan_detail['arm_subscription_plan_amount'];
                        if(isset($arm_current_plan_detail['arm_subscription_plan_amount_original']) && !empty($arm_current_plan_detail['arm_subscription_plan_amount_original']) )
                        {
                            $arm_subscription_plan_amount = $arm_current_plan_detail['arm_subscription_plan_amount_original'];
                        }

                        $log_details = $wpdb->get_row("SELECT * FROM `{$armLogTable}` WHERE `arm_log_id`='{$log_id}'");
                        if(!empty($log_details))
                        {
                            if(MEMBERSHIP_DEBUG_LOG == true) {
                                $ARMember->arm_write_response("ARMember COUPON LOG 3 : arm_coupon_apply_to_subscription log details : ". maybe_serialize($log_details));
                            }
                            $arm_coupon_discount = $log_details->arm_coupon_discount;
                            $arm_coupon_discount_type = $log_details->arm_coupon_discount_type;
                            $arm_coupon_code = $log_details->arm_coupon_code;
                            $arm_coupon_on_each_subscriptions = $log_details->arm_coupon_on_each_subscriptions;
                            if(!empty($arm_coupon_on_each_subscriptions))
                            {
                                if(MEMBERSHIP_DEBUG_LOG == true) {
                                    $ARMember->arm_write_response("ARMember COUPON LOG 4 : arm_coupon_apply_to_subscription each recurring true");
                                }
                                if(!empty($arm_coupon_code))
                                {
                                    $arm_user_payment_cycles = $arm_subscription_plan_options['payment_cycles'];
                                    if(MEMBERSHIP_DEBUG_LOG == true) {
                                        $ARMember->arm_write_response("ARMember COUPON LOG 5 : arm_coupon_apply_to_subscription arm_user_payment_cycles".maybe_serialize($arm_user_payment_cycles));
                                    }
                                    if(count($arm_user_payment_cycles)>0)
                                    {
                                        foreach ($arm_user_payment_cycles as $arm_user_payment_cycle_key => $arm_user_payment_cycle_value) {

                                            if(!isset($arm_user_payment_cycle_value['cycle_amount_original']))
                                            {
                                                $arm_user_payment_cycle_amount = $arm_user_payment_cycle_value['cycle_amount'];
                                            }
                                            else {
                                                $arm_user_payment_cycle_amount = $arm_user_payment_cycle_value['cycle_amount_original'];
                                            }

                                            $arm_couponApply_plan = $arm_manage_coupons->arm_apply_coupon_code($arm_coupon_code, $arm_subscription_plan_id, 0, $arm_user_payment_cycle_key);
                                            $arm_coupon_discount_type = isset($arm_couponApply_plan['discount_type']) ? $arm_couponApply_plan['discount_type'] : '';
                                            $arm_coupon_discount = isset($arm_couponApply_plan['discount']) ? $arm_couponApply_plan['discount'] : 0;
                                            if($arm_coupon_discount_type=='percentage')
                                            {
                                                $arm_subscription_plan_amount_couponed = ($arm_user_payment_cycle_amount * $arm_coupon_discount) / 100;
                                                $arm_subscription_plan_amount_couponed = $arm_user_payment_cycle_amount - $arm_subscription_plan_amount_couponed;
                                            }
                                            else {
                                                $arm_subscription_plan_amount_couponed = $arm_user_payment_cycle_amount-$arm_coupon_discount;
                                            }

                                            if($arm_subscription_plan_amount_couponed<0)
                                            {
                                                $arm_subscription_plan_amount_couponed = 0;
                                            }

                                            if(MEMBERSHIP_DEBUG_LOG == true) {
                                                $ARMember->arm_write_response("ARMember COUPON LOG 5.1 : arm_subscription_plan_amount_couponed=".maybe_serialize($arm_subscription_plan_amount_couponed));
                                            }

                                            if($user_subscription_payment_cycle==$arm_user_payment_cycle_key)
                                            {
                                                if(!isset($userPlanData['arm_current_plan_detail']['arm_subscription_plan_amount_original']))
                                                {
                                                    $userPlanData['arm_current_plan_detail']['arm_subscription_plan_amount_original'] = $arm_user_payment_cycle_amount;
                            $user_activity = $wpdb->get_row("SELECT arm_activity_id, arm_content FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_user_id`='$user_ID' AND `arm_action` = 'new_subscription' AND `arm_item_id`='$arm_subscription_plan_id' ORDER BY `arm_activity_id` DESC LIMIT 1", ARRAY_A);

                                                if(!empty($user_activity)) {

                                                    $user_activity_content = maybe_unserialize( $user_activity['arm_content'] );

                                                    if(isset($user_activity_content['plan_amount'])) {
                                                        $user_activity_content['plan_amount'] = $arm_subscription_plan_amount_couponed;
                                                    }

                                                    if (isset($user_activity_content['plan_detail']['arm_subscription_plan_amount'])) {
                                                        $user_activity_content['plan_detail']['arm_subscription_plan_amount'] = $arm_subscription_plan_amount_couponed;
                                                    }

                                                    if (isset($user_activity_content['plan_detail']['arm_subscription_plan_options'])) {

                                                        $arm_subscription_plan_options = maybe_unserialize($user_activity_content['plan_detail']['arm_subscription_plan_options']);

                                                        if(isset($arm_subscription_plan_options['payment_cycles'][0]['cycle_amount'])) {

                                                            $arm_subscription_plan_options['payment_cycles'][0]['cycle_amount'] = $arm_subscription_plan_amount_couponed;

                                                            $user_activity_content['plan_detail']['arm_subscription_plan_options'] = maybe_serialize($arm_subscription_plan_options);
                                                        }
                                                    }

                                                    if(!empty($user_activity_content['plan_text'])) {
                                                        $plan_text = $user_activity_content['plan_text'];

                                                        $first_part_ind = strpos($plan_text, 'arm_plan_amount_span');

                                                        $second_part_ind = strrpos($plan_text, '</span>');

                                                        if($first_part_ind !== false && $second_part_ind !== false) {

                                                            $first_part = substr($plan_text, 0, ($first_part_ind + 22));

                                                            $second_part = substr($plan_text, $second_part_ind);

                                                            $user_activity_content['plan_text'] = $first_part . number_format((float)$arm_subscription_plan_amount_couponed, 2) . $second_part;
                                                        }
                                                    }

                                                    $user_activity_content = maybe_serialize($user_activity_content);

                                                    $user_activity_id = $user_activity['arm_activity_id'];

                                                    if(!empty($user_activity_id)) {
                                                        $user_activity_update = $wpdb->update($ARMember->tbl_arm_activity, array('arm_content' => $user_activity_content), array('arm_activity_id' => $user_activity_id));
                                                    }
                                                }
                                                }
                                                $userPlanData['arm_current_plan_detail']['arm_subscription_plan_amount'] = $arm_subscription_plan_amount_couponed;
                                            }
                                            
                                            if(!isset($arm_subscription_plan_options['payment_cycles'][$arm_user_payment_cycle_key]['cycle_amount_original']))
                                            {
                                                $arm_subscription_plan_options['payment_cycles'][$arm_user_payment_cycle_key]['cycle_amount_original'] =$arm_subscription_plan_options['payment_cycles'][$arm_user_payment_cycle_key]['cycle_amount'];
                                            }
                                            $arm_subscription_plan_options['payment_cycles'][$arm_user_payment_cycle_key]['cycle_amount'] = $arm_subscription_plan_amount_couponed;
                                        }

                                        $userPlanData['arm_current_plan_detail']['arm_subscription_plan_options'] = maybe_serialize($arm_subscription_plan_options);

                                        if(MEMBERSHIP_DEBUG_LOG == true) {
                                            $ARMember->arm_write_response("ARMember COUPON LOG 6 : arm_coupon_apply_to_subscription userPlanData".maybe_serialize($userPlanData));
                                        }

                                        update_user_meta($user_ID, 'arm_user_plan_'.$arm_subscription_plan_id, $userPlanData);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        function arm_coupon_form_html($c_discount,$c_type,$period_type,$sdate_status,$edit_mode,$c_sdate,$c_edate,$c_allow_trial,$c_allowed_uses,$c_label,$c_coupon_on_each_subscriptions,$coupon_status,$c_subs,$c_data, $arm_coupon_type = 1, $arm_paid_posts = array()){

            global $arm_payment_gateways, $arm_subscription_plans, $arm_global_settings,$arm_pay_per_post_feature,$ARMember,$wpdb;

            $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

            $global_currency = $arm_payment_gateways->arm_get_global_currency();
            $arm_coupon_form_html='';
            $c_discount=(isset($c_discount)) ? $c_discount : '';
            $c_post_subs = !empty($arm_paid_posts) ? array_filter($arm_paid_posts) : array();
            $c_subs = (!empty($c_subs)) ? $c_subs : array();

            $period_type_section=($period_type == 'daterange') ? '' : 'hidden_section';
            $arm_rtl_style=(is_rtl()) ? 'margin-left: 10px;' : 'margin-right: 10px;';
            $arm_coupon_form_html .='<div class="arm_paid_post_items_list_container" id="arm_paid_post_items_list_container"></div>';
            $arm_coupon_form_html .='<tr class="form-field form-required">
                                        <th><label>'.esc_html__('Discount', 'ARMember').'</label></th>
                                        <td>
                                            <input type="text" id="arm_coupon_discount" value="'.$c_discount.'" onkeypress="return ArmNumberValidation(event, this)" name="arm_coupon_discount" class="arm_coupon_input_fields arm_coupon_discount_input arm_no_paste" data-msg-required="'. esc_html__('Please add discount amount.', 'ARMember').'" required style="'.$arm_rtl_style.'"/>
                                            <input type="hidden" id="arm_discount_type" name="arm_discount_type" value="'.$c_type.'"/>
                                            <dl class="arm_selectbox arm_coupon_discount_select column_level_dd">
                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_discount_type">
                                                        <li data-label="'.esc_html__('Fixed', 'ARMember').' ('.$global_currency.')" data-value="fixed">'.esc_html__('Fixed', 'ARMember').' ('.$global_currency.')</li>
                                                        <li data-label="'.esc_html__('Percentage', 'ARMember').' (%)" data-value="percentage">'.esc_html__('Percentage', 'ARMember').' (%)</li>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </td>
                                    </tr>';
            $daterange_chk=($period_type=='daterange')? "checked='checked'" :"";
            $unlimited_chk=($period_type=='unlimited')? "checked='checked'" :"";
            $arm_coupon_form_html .='<tr class="form-field form-required">
                                        <th><label>'.esc_html__('Period Type', 'ARMember').'</label></th>
                                        <td>
                                            <div class="arm_coupon_period_box">
                                                <span class="arm_period_types_container" id="arm_period_types_container">
                                                    <input type="radio" class="arm_iradio" '.$daterange_chk.' value="daterange" name="arm_coupon_period_type" id="period_type_daterange" >
                                                    <label for="period_type_daterange">'.esc_html__('Date Range', 'ARMember').'</label>
                                                    <input type="radio" class="arm_iradio" '.$unlimited_chk.' value="unlimited" name="arm_coupon_period_type" id="period_type_unlimited" >
                                                    <label for="period_type_unlimited">'.esc_html__('Unlimited', 'ARMember').'</label>
                                                </span>
                                                <div class="armclear"></div>
                                            </div> 
                                        </td>
                                    </tr>';
            
            $arm_coupon_form_html .='<tr class="form-field form-required coupon_period_options '.$period_type_section.'">
                                        <th><label>'.esc_html__('Start Date', 'ARMember').'</label></th>
                                        <td class="arm_position_relative">
                                            <input type="text" id="arm_coupon_start_date" '.$sdate_status.' value="'.(!empty($c_sdate) ? date($arm_common_date_format, strtotime($c_sdate)) : '').'" name="arm_coupon_start_date" data-date_format="'.$arm_common_date_format.'" class="arm_coupon_input_fields '.(!empty($sdate_status) ? '' : 'arm_datepicker_coupon' ).'" data-msg-required="'.esc_html__('Please select start date.', 'ARMember').'" required />';
                                            if ($edit_mode == TRUE && $sdate_status != '') {
                                                $arm_coupon_form_html .='<i class="arm_helptip_icon armfa armfa-question-circle" title="'.esc_html__("Date Can't Be Changed, Because coupon usage has been started.", 'ARMember').'"></i>';
                                            }
            $arm_coupon_form_html .='   </td>
                                    </tr>';

            $edit_mode=($edit_mode) ? '1' : '0';
            $arm_coupon_form_html .='<tr class="form-field form-required coupon_period_options '.$period_type_section.'">
                                        <th><label>'.esc_html__('Expire Date', 'ARMember').'</label></th>
                                        <td class="arm_position_relative">
                                            <input type="text" id="arm_coupon_expire_date" value="'.(!empty($c_edate) ? date($arm_common_date_format, strtotime($c_edate)) : '').'" name="arm_coupon_expire_date" data-date_format="'.$arm_common_date_format.'" class="arm_coupon_input_fields arm_datepicker_coupon" data-editmode="'.$edit_mode.'" data-msg-required="'.esc_html__('Please select expire date.', 'ARMember').'" data-armgreaterthan-msg="'.esc_html__('Expire date can not be earlier than start date', 'ARMember').'" required />
                                        </td>
                                    </tr>';

            if(!empty($arm_pay_per_post_feature->isPayPerPostFeature)){

            $arm_membership_plan_chk = ($arm_coupon_type == 1) ? "checked='checked'" : "";
            $arm_paid_post_chk = ($arm_coupon_type == 2) ? "checked='checked'" : "";
            $arm_both_chk = ($arm_coupon_type == 0) ? "checked='checked'" : "";

            $arm_coupon_form_html.= '<tr class="form-field form-required">
                                        <th><label>' . esc_html__('Coupon Type', 'ARMember').'<label></th>
                                        <td>
                                            <div class="arm_coupon_period_box">
                                                <span class="arm_coupon_types_container" id="arm_coupon_types_container">
                                                    <input type="radio" class="arm_iradio" '.$arm_membership_plan_chk.' value="1" name="arm_coupon_type" id="coupon_type_membership_plan" >
                                                    <label for="coupon_type_membership_plan">'.esc_html__('Membership Plan', 'ARMember').'</label>

                                                    <input type="radio" class="arm_iradio" '.$arm_paid_post_chk.' value="2" name="arm_coupon_type" id="coupon_type_paid_post">
                                                    <label for="coupon_type_paid_post">'.esc_html__('Paid Post', 'ARMember').'</label>

                                                    <input type="radio" class="arm_iradio" '.$arm_both_chk.' value="0" name="arm_coupon_type" id="coupon_type_both" >
                                                    <label for="coupon_type_both">'.esc_html__('Both', 'ARMember').'</label>
                                                </span>
                                                <div class="armclear"></div>
                                            </div> 
                                        </td>
                                    </tr>';
            }

            $arm_display_membership_plan_class = "";
            if(!empty($arm_pay_per_post_feature->isPayPerPostFeature)){   
                $arm_display_membership_plan_class = ($arm_coupon_type == 2) ? " hidden_section" : '';
            }

            $arm_coupon_form_html .='<tr class="form-field form-required coupon_type_membership_plan '.$arm_display_membership_plan_class.'">
                                        <th><label>'.esc_html__('Membership Plan', 'ARMember').'</label></th>
                                        <td>
                                            <select name="arm_subscription_coupons[]" id="arm_subscription_coupons" class="arm_chosen_selectbox arm_coupons_select_box_sub arm_coupon_input_fields" data-placeholder="'.esc_html__('Select Plan(s)..', 'ARMember').'" multiple>';
                                                
                                                $subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type');
                                                if (!empty($subs_data)) {
                                                    $c_subs = (!empty($c_subs)) ? $c_subs : array();
                                                    foreach ($subs_data as $sd) {
                                                        $selected_sub_plan=(in_array($sd['arm_subscription_plan_id'], $c_subs)) ? 'selected="selected"' : "";
                                                            $arm_coupon_form_html .='<option value="' . $sd['arm_subscription_plan_id'] . '" ' .$selected_sub_plan. '>' . esc_html(stripslashes($sd['arm_subscription_plan_name'])) . '</option>';
                                                    }
                                                }
                                
            $arm_coupon_form_html .='       </select>
                                            <span class="arm_coupon_blank_field_warning">'.__('Leave blank for apply coupon to all plan(s)', 'ARMember').'</span>
                                        </td>
                                    </tr>';
            if(!empty($arm_pay_per_post_feature->isPayPerPostFeature)){   
            $arm_display_paid_post_class = ($arm_coupon_type == 1) ? " hidden_section" : '';

            $arm_coupon_form_html.= '<tr class="form-field form-required coupon_type_paid_post'.$arm_display_paid_post_class.'">
                                        <th><label>' . esc_html__('Paid Posts', 'ARMember').'<label></th>
                                        <td>
                                            <div class="arm_text_align_center arm_width_100_pct" ><img src="'.MEMBERSHIP_IMAGES_URL.'/arm_loader.gif" id="arm_loader_img_paid_post_items" class="arm_loader_img_paid_post_items" style="display: none;" width="20" height="20" /></div>

                                            <input id="arm_coupon_paid_post_items_input" type="text" value="" placeholder="'. esc_html__( 'Search by paid post title...', 'ARMember').'" />
                                            <span class="arm_coupon_blank_field_warning">'.__('Leave blank for apply coupon to all paid post(s)', 'ARMember').'</span>
                                            <div class="arm_paid_post_items" id="arm_paid_post_items" style="'.(empty($c_post_subs) ? 'display:none' : '').'">';

                                            if( !empty( $c_post_subs) ) {
                                                $arm_plan_name ='';
                                                foreach ($c_post_subs as $key => $arm_paid_post_id_val) {       
						    $arm_subscription_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($arm_paid_post_id_val);

                                                    $arm_coupon_form_html .= '<div class="arm_paid_post_itembox arm_paid_post_itembox_'.$arm_paid_post_id_val.'">';
                                                    $arm_coupon_form_html .='<input type="hidden" name="arm_paid_post_item_id['.$arm_paid_post_id_val.']" value="'.$arm_paid_post_id_val.'" />';
                                                    $arm_coupon_form_html .='<label style="color:#FFF">'.$arm_subscription_plan_name.'<span class="arm_remove_selected_itembox">x</span></label>';
                                                    $arm_coupon_form_html .='</div>';
                                                }
                                            }
                $arm_coupon_form_html .=  '</div>
                                        </td>
                                    </tr>';
            }


            $c_allow_trial_chk=($c_allow_trial=='1')?"checked='checked'":"";                                   
            $arm_coupon_form_html .='<tr class="form-field form-required">
                                        <th><label>'.esc_html__('Allow this coupon with trial period amount?', 'ARMember').'</label></th>
                                        <td valign="middle">
                                            <input type="checkbox" class="arm_coupon_input_fields arm_icheckbox" value="1" name="arm_coupon_allow_trial" '.$c_allow_trial_chk.'>
                                        </td>
                                    </tr>';
            $arm_coupon_form_html .='<tr class="form-field form-required">
                                        <th><label>'.esc_html__('No. of time uses allowed', 'ARMember').'</label></th>
                                        <td valign="middle">
                                            <input type="text" onkeypress="javascript:return isNumber(event)" id="arm_allowed_uses" value="'.(!empty($c_allowed_uses) ? $c_allowed_uses : 0).'" name="arm_allowed_uses" class="arm_coupon_input_fields"/>
                                            <i class="arm_helptip_icon armfa armfa-question-circle" title="'.esc_html__("Leave blank or '0' for unlimited uses.", 'ARMember').'"></i>
                                        </td>
                                    </tr>';
            $arm_coupon_form_html .='<tr class="form-field form-required">
                                        <th><label>'.esc_html__('Coupon Label', 'ARMember').'</label></th>
                                        <td valign="middle">
                                            <input type="text"  id="arm_coupon_label" value="'.(isset($c_label) ? stripslashes_deep($c_label) : '').'" name="arm_coupon_label" class="arm_coupon_input_fields"/>
                                           
                                        </td>
                                    </tr>';
            $c_coupon_on_each_subscriptions_chk=($c_coupon_on_each_subscriptions=='1')?"checked='checked'":"";
            $arm_coupon_form_html .='<tr class="form-field">
                                        <th><label>'.esc_html__('For Recurring Plan Apply to Entire Duration', 'ARMember').'</label></th>
                                        <td valign="middle">
                                            <input type="checkbox" class="arm_coupon_input_fields arm_icheckbox" value="1" name="arm_coupon_on_each_subscriptions" '.$c_coupon_on_each_subscriptions_chk.'>
                                        </td>
                                    </tr>
                                    <input type="hidden" name="arm_coupon_status" value="'.$coupon_status.'"/>';
            
            $arm_coupon_form_html = apply_filters('arm_add_field_after_coupon_form',$arm_coupon_form_html,$c_data);

            return $arm_coupon_form_html;
        }
        function arm_bulk_generate_code($length,$type)
        {   $return_code='';
            global $wpdb, $ARMember, $arm_slugs, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $arm_code = $this->arm_bulk_generate_coupon_code($length,$type);
            $old_coupon =  $this->arm_get_coupon($arm_code);
            if (!empty($old_coupon) && is_array($old_coupon)) {
                $this->arm_bulk_generate_code();
            } else {
                $return_code=$arm_code;
            }
            return $return_code;
        }
        function arm_bulk_generate_coupon_code($length,$type)
        {   
            
            $couponCode = '';
            $coupon_char = array();
            if($type=='alphanumeric'){
                $length_second=$length*40/100;
                $length_first=$length-$length_second;
                $coupon_char[] = array('count' => $length_first, 'char' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
                $coupon_char[] = array('count' => $length_second, 'char' => '0123456789');
            }else if($type=='alphabetical'){
                $coupon_char[] = array('count' => $length, 'char' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            }else if($type=='numeric'){
                $coupon_char[] = array('count' => $length, 'char' => '0123456789');
            }    
            
            $temp_array = array();
            foreach ($coupon_char as $char_set) {
                for ($i = 0; $i < $char_set['count']; $i++) {
                    $temp_array[] = $char_set['char'][rand(0, strlen($char_set['char']) - 1)];
                }
            }
            shuffle($temp_array);
            $couponCode = implode('', $temp_array);
            
            return $couponCode;
        }
    
       function arm_save_coupon_in_usermeta($user_id=0, $coupon='', $plan_id=0) {
            if( 0 != $user_id && '' != $coupon ) {
                update_user_meta($user_id, 'arm_used_invite_coupon_'.$plan_id , $coupon);
            }
        }

        function arm_get_coupon_members($coupon_id = 0) {
            global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_transaction;
            $couponMembers = array();
            
            $couponData = $this->arm_get_coupon_by_id($coupon_id);
            if (!empty($couponData)) {
                $nowTime = strtotime(current_time('mysql'));
                $coupon_id = $couponData['arm_coupon_id'];
                $coupon_code = $couponData['arm_coupon_code'];
                $discount_type = $couponData['arm_coupon_discount_type'];
                $rule_type = $couponData['arm_coupon_period_type'];
                

                $rule_post_data = array();
                if (!empty($post_id)) {
                    $rule_post_data = get_post($post_id);
                }

                $rule_post_date = '';

                if (!empty($rule_post_data)) {

                    $rule_post_date = isset($rule_post_data->post_date) ? $rule_post_data->post_date : '';
                    $rule_post_modify_date = isset($rule_post_data->post_modified) ? $rule_post_data->post_modified : '';
                }

                if (!empty($coupon_code)) {

                    $log_data = $wpdb->get_results($wpdb->prepare("SELECT arm_user_id,arm_plan_id,arm_coupon_code FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_coupon_code`=%s", $coupon_code), ARRAY_A);

                    $Members_arr = array();
                    $arm_used_invite_coupon_already_added = "";
                    if(!empty($log_data)) {
                        foreach ($log_data as $log_data_val) {
                            if($coupon_code==$log_data_val['arm_coupon_code'])
                            {
                                $Member = array();
                                $arm_used_invite_coupon_already_added .= " AND `meta_key` NOT LIKE 'arm_used_invite_coupon_".$log_data_val['arm_plan_id']."' ";

                                $user_info = get_userdata($log_data_val['arm_user_id']);
                                if(!empty($user_info)) {
                                    $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $user_info->data->ID);
                                    $Member['user_id'] = $user_info->data->ID;
                                    $Member['user_login'] = $user_info->data->user_login;
                                    $Member['user_email'] = $user_info->data->user_email;
                                    $Member['coupon_id'] = $coupon_id;
                                    $Member['coupon_code'] = $coupon_code;
                                    $Member['view_detail'] = htmlentities("<center><a class='arm_openpreview' href='{$view_link}'>" . esc_html__('View Detail', 'ARMember') . "</a></center>");
                                    array_push($Members_arr, $Member);
                                }
                            }
                        }
                    }

                    $log_data2 = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM `" . $wpdb->usermeta . "` WHERE `meta_key` LIKE 'arm_used_invite_coupon_%' AND `meta_value` = %s ".$arm_used_invite_coupon_already_added, $coupon_code), ARRAY_A);

                    if(!empty($log_data2)) {
                        foreach ($log_data2 as $meta_value) {
                            if($coupon_code==$meta_value['meta_value'])
                            {
                                $Member = array();
                                $user_info = get_userdata($meta_value['user_id']);
                                if(!empty($user_info)) {
                                    $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $user_info->data->ID);
                                    $Member['user_id'] = $user_info->data->ID;
                                    $Member['user_login'] = $user_info->data->user_login;
                                    $Member['user_email'] = $user_info->data->user_email;
                                    $Member['coupon_id'] = $coupon_id;
                                    $Member['coupon_code'] = $coupon_code;
                                    $Member['view_detail'] = htmlentities("<center><a class='arm_openpreview' href='{$view_link}'>" . esc_html__('View Detail', 'ARMember') . "</a></center>");
                                    
                                    array_push($Members_arr, $Member);
                                }
                            }
                        }
                    }

                    $couponMembers = $Members_arr;

                }
            }


            return $couponMembers;
        }

        function arm_get_coupon_members_data_func() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');
            $couponID = isset($_REQUEST['coupon_id']) ? $_REQUEST['coupon_id'] : 0;
            $response = array('status' => 'error', 'data' => array());
            if(0 != $couponID) {
                $membersDatasDefault = array();
                $response['status'] = "success";
                $response['data'] = $membersDatasDefault;

                global $arm_manage_coupons;
                $couponMembers = array();
                $couponAllowMembers = $arm_manage_coupons->arm_get_coupon_members($couponID);
                $couponRulesMembers[$couponID] = $couponAllowMembers;
                if(!empty($couponRulesMembers)) {
                    foreach($couponRulesMembers as $couponID => $members) {
                        if (!empty($members)) {
                            $membersData = array();
                            foreach($members as $mData){
                              
                                $membersDatas = array();
                                
                                $membersDatas['username'] = $mData['user_login'];
                                $membersDatas['user_email'] = $mData['user_email'];
                                $membersDatas['coupon_code'] = $mData['coupon_code'];
                                $membersDatas['view_detail'] = html_entity_decode($mData['view_detail']);
                                $membersData[] = array_values($membersDatas); 
                            }
                            $response['status'] = "success";
                            $response['data'] = $membersData;
                        }
                    }
                }


            }
            echo json_encode($response);
            die;
        }
        function arm_get_paid_post_item_coupon_options() {

            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_coupons'], '1');

            $search_key = isset( $_POST['search_key'] ) ? $_POST['search_key'] : '';

            if( $search_key != '' ){
                $postQuery = $wpdb->get_results( $wpdb->prepare( "SELECT p.arm_subscription_plan_id, p.arm_subscription_plan_name FROM {$ARMember->tbl_arm_subscription_plans} p  WHERE p.arm_subscription_plan_post_id != %d AND p.arm_subscription_plan_is_delete = %d AND p.arm_subscription_plan_name LIKE %s LIMIT 0,10",0,0,'%' . $wpdb->esc_like( $search_key ) . '%') );
            } else {
                $postQuery = $wpdb->get_results( $wpdb->prepare( "SELECT p.arm_subscription_plan_id, p.arm_subscription_plan_name FROM {$ARMember->tbl_arm_subscription_plans} p  WHERE p.arm_subscription_plan_post_id != %d AND p.arm_subscription_plan_is_delete = %d LIMIT 0,10",0,0) );
            }

            $ppData = array();
            if( isset( $postQuery ) && !empty( $postQuery ) ){
                foreach( $postQuery as $k => $postData ){
                    $isEnablePaidPost = get_post_meta( $postData->ID, 'arm_is_paid_post', true );
                    if( 0 == $isEnablePaidPost || empty($isEnablePaidPost) ){
                        $ppData[] = array(
                            'id' => $postData->arm_subscription_plan_id,
                            'value' => $postData->arm_subscription_plan_name,
                            'label' => $postData->arm_subscription_plan_name
                        );
                    }
                }
            }

            $response = array('status' => 'success', 'data' => $ppData);
            echo json_encode($response);
            die;

        }
	
    }

    
}
global $arm_manage_coupons;
$arm_manage_coupons = new ARM_manage_coupons();