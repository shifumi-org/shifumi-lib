<?php

if (!class_exists('ARM_drip_rules')) {

    class ARM_drip_rules {

        var $isDripFeature;

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            $this->isDripFeature = false;
            if (get_option('arm_is_drip_content_feature') == '1') {
                $this->isDripFeature = true;
                add_shortcode('arm_drip_content', array($this, 'arm_drip_content_shortcode_func'));
		add_filter('arm_email_notification_shortcodes_outside', array($this, 'arm_email_notification_shortcodes_outside_func'));
            	add_filter('arm_admin_email_notification_shortcodes_outside', array($this, 'arm_admin_email_notification_shortcodes_outside_func'));
            }
            add_action('wp_ajax_arm_add_drip_rule', array($this, 'arm_add_drip_rule'));
            add_action('wp_ajax_arm_update_drip_rule', array($this, 'arm_update_drip_rule'));
            add_action('wp_ajax_arm_update_drip_rule_status', array($this, 'arm_update_drip_rule_status'));
            add_action('wp_ajax_arm_delete_single_drip_rule', array($this, 'arm_delete_single_drip_rule'));
            add_action('wp_ajax_arm_delete_bulk_drip_rules', array($this, 'arm_delete_bulk_drip_rules'));
            add_action('wp_ajax_arm_edit_drip_rule_data', array($this, 'arm_edit_drip_rule_data'));
            add_action('wp_ajax_arm_get_drip_rule_items', array($this, 'arm_get_drip_rule_items'));
            add_action('wp_ajax_arm_get_drip_rule_item_options', array($this, 'arm_get_drip_rule_item_options'));
            add_action('wp_ajax_arm_filter_drip_rules_list', array($this, 'arm_filter_drip_rules_list'));

            add_filter('arm_is_allow_access', array($this, 'arm_filter_drip_access'), 20, 2);
            //add_action('deleted_post', array($this, 'arm_delete_post_drip_rules'), 20);

            add_filter('arm_notification_add_message_types', array($this, 'arm_notification_add_message_types_func'));

            add_action('wp_ajax_arm_get_drip_rule_members_data', array($this, 'arm_get_drip_rule_members_data_func'));
        }

        function arm_notification_add_message_types_func($message_types = array()) {
            if ($this->isDripFeature) {
                $message_types['before_dripped_content_available'] = __('Before Dripped Content Available', 'ARMember');
            }
            return $message_types;
        }

        function arm_email_notification_shortcodes_outside_func($arm_other_custom_shortcode_arr = array())
        {
            $arm_other_custom_shortcode_arr['dripped_content_url']['title_on_hover'] = __("To Display dripped URL for Member.", 'ARMember');
            $arm_other_custom_shortcode_arr['dripped_content_url']['shortcode'] = "{ARM_MESSAGE_DRIP_CONTENT_URL}";
            $arm_other_custom_shortcode_arr['dripped_content_url']['shortcode_label'] = __("Member Dripped Content URL", 'ARMember');
	    $arm_other_custom_shortcode_arr['dripped_content_url']['shortcode_class'] = "arm_before_dripped_content_available_url";
            
            return $arm_other_custom_shortcode_arr;
        }

        function arm_admin_email_notification_shortcodes_outside_func($arm_other_custom_shortcode_arr = array())
        {
            $arm_other_custom_shortcode_arr['admin_dripped_content_url']['title_on_hover'] = __("To Display dripped URL for Member.", 'ARMember');
            $arm_other_custom_shortcode_arr['admin_dripped_content_url']['shortcode'] = "{ARM_MESSAGE_DRIP_CONTENT_URL}";
            $arm_other_custom_shortcode_arr['admin_dripped_content_url']['shortcode_label'] = __("Member Dripped Content URL", 'ARMember');
            $arm_other_custom_shortcode_arr['admin_dripped_content_url']['shortcode_class'] = "arm_before_dripped_content_available_url";

            return $arm_other_custom_shortcode_arr;
        }

        function arm_filter_drip_access($allowed = true, $extraVars = array()) {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_access_rules;
            if (!$allowed) {


                if ($this->isDripFeature && is_user_logged_in()) {


                    if (in_array('special-page', array_keys($extraVars))) {
                        /* Need to set rule for special pages */
                    }elseif(in_array('post_type', array_keys($extraVars))){


                        $user_id = get_current_user_id();
                        $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        $user_plans = apply_filters('arm_assign_plan_data', $user_plans, $user_id);
                        $user_plans = !empty($user_plans) ? $user_plans : array();
                        $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                        $suspended_plan_ids = apply_filters('arm_assign_suspended_plan_data', $suspended_plan_ids, $user_id);
                        $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                        if (!empty($user_plans) && is_array($user_plans)) {
                            foreach ($user_plans as $cp) {
                                if (in_array($cp, $suspended_plan_ids)) {
                                    unset($user_plans[array_search($cp, $user_plans)]);
                                }
                            }
                        }



                            $arm_primary_status = arm_get_member_status($user_id);
                            if($arm_primary_status == 3){
                                $user_plans = array(-5);
                            }


                        // for checking single post is restricted.
                        $post_id = isset($extraVars['post_id']) ? $extraVars['post_id'] : 0;
                        if (!empty($user_plans) && is_array($user_plans)) {


                            foreach ($user_plans as $user_plan) {
                                $post_drip_rule = $this->arm_get_post_drip_rule($user_id, $user_plan, $post_id);
                                if ($post_drip_rule) {

                                    $is_dripped = $this->arm_is_dripped($post_drip_rule, $user_id, $user_plan); 
                                    $allowed = ($is_dripped) ? false : true;
                                    if ($allowed) {
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $allowed;
        }

        function arm_drip_posts_where($where, $obj) {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember;
            if ($this->isDripFeature && is_user_logged_in() && !$obj->is_singular) {
                $user_id = get_current_user_id();
                $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array();
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                if (!empty($current_user_plan) && is_array($current_user_plan)) {
                    foreach ($current_user_plan as $cp) {
                        if (in_array($cp, $suspended_plan_ids)) {
                            unset($current_user_plan[array_search($cp, $current_user_plan)]);
                        }
                    }
                }



                            $arm_primary_status = arm_get_member_status($user_id);
                            if($arm_primary_status == 3){
                                $current_user_plan = array(-5);
                            }

          // no need to plas -2 in blank array because in arm_restriction post where condition allready passed

                if (!empty($current_user_plan)) {
                    $openPosts = array();
                    $post_type = (isset($obj->post_type) && !empty($obj->post_type)) ? $obj->post_type : '';
                    if (empty($post_type) && isset($obj->query_vars['post_type']) && !empty($obj->query_vars['post_type'])) {
                        $post_type = $obj->query_vars['post_type'];
                    }
                    if (!empty($post_type)) {
                        $openPosts = $this->arm_get_user_dripped_post_ids($current_user->ID, $current_user_plan, $post_type);
                    } else {
                        $openPosts = $this->arm_get_user_dripped_post_ids($current_user->ID, $current_user_plan, 'post');
                    }
                    if (!empty($openPosts)) {
                        $wherePost = implode(',', $openPosts);
                        $where .= " OR {$wpdb->posts}.ID IN ({$wherePost}) ";
                    }
                }
            }
            return $where;
        }

        function arm_get_user_dripped_post_ids($user_id = 0, $plan_id = array(), $post_type = 'post') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $postIds = array();
            if (!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0) {
                if (is_array($plan_id)) {
                    foreach ($plan_id as $pid) {

                        $where = "WHERE FIND_IN_SET({$pid}, `arm_rule_plans`) AND `arm_rule_status`='1' ";
                        if (!empty($post_type) && $post_type != 'any') {

                            if (is_array($post_type)) {
                                $post_type1 = implode("','", $post_type);
                                $where .= " AND `arm_item_type` IN ('{$post_type1}') ";
                            } else {
                                $where .= " AND `arm_item_type`='{$post_type}' ";
                            }
                        }
                        $userDripRule = $wpdb->get_results("SELECT `arm_rule_id`, `arm_item_id`, `arm_item_type`, `arm_rule_type`, `arm_rule_options`, `arm_rule_plans` FROM `" . $ARMember->tbl_arm_drip_rules . "` {$where} ORDER BY `arm_rule_id` DESC", ARRAY_A);
                        if (!empty($userDripRule)) {
                            foreach ($userDripRule as $udr) {
                                $is_dripped = $this->arm_is_dripped($udr, $user_id, $pid);
                                if (!$is_dripped) {
                                    $postIds[] = $udr['arm_item_id'];
                                }
                            }
                        }
                    }
                }
            }
            return $postIds;
        }

        /**
         * Remove restricted pages from widgets
         */
        function arm_widget_pages_args($args) {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember;
            if (!is_admin() && !current_user_can('administrator')) {
                if ($this->isDripFeature && is_user_logged_in()) {

                    if (!empty($args['exclude'])) {


                        $restrict_pages = explode(',', $args['exclude']);
                        foreach ($restrict_pages as $key => $pageID) {
                            $user_id = get_current_user_id();
                            $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            if (!empty($user_plans) && is_array($user_plans)) {


                                foreach ($user_plans as $user_plan) {
                                    $post_drip_rule = $this->arm_get_post_drip_rule($user_id, $user_plan, $pageID);


                                    if ($post_drip_rule) {




                                        $is_dripped = $this->arm_is_dripped($post_drip_rule, $user_id, $user_plan);
                                        if (!$is_dripped) {
                                            unset($restrict_pages[$key]);
                                        }
                                    }
                                }
                            }
                        }
                        $args['exclude'] = implode(',', $restrict_pages);
                    }
                }
            }
            return $args;
        }



        function arm_widget_posts_args($args) {

            
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember;
            if (!is_admin() && !current_user_can('administrator')) {
                if ($this->isDripFeature && is_user_logged_in()) {

                    if (!empty($args['post__not_in'])) {


                        $restrict_pages =  $args['post__not_in'];
                        foreach ($restrict_pages as $key => $pageID) {
                            $user_id = get_current_user_id();
                            $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            if (!empty($user_plans) && is_array($user_plans)) {


                                foreach ($user_plans as $user_plan) {
                                    $post_drip_rule = $this->arm_get_post_drip_rule($user_id, $user_plan, $pageID);

                                    if ($post_drip_rule) {
                                        $is_dripped = $this->arm_is_dripped($post_drip_rule, $user_id, $user_plan);
                                        if (!$is_dripped) {
                                            unset($restrict_pages[$key]);
                                        }
                                    }
                                }
                            }
                        }
                        $args['post__not_in'] = $restrict_pages;
                    }
                }
            }

           
            return $args;
        }

        function arm_is_dripped($post_drip_rule = array(), $user_id = 0, $plan_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $isDripped = true;
            if (!empty($post_drip_rule)) {
                $nowTime = strtotime(current_time('mysql'));
                $rule_type = isset($post_drip_rule['arm_rule_type']) ? $post_drip_rule['arm_rule_type'] : '';
                $item_type = isset($post_drip_rule['arm_item_type']) ? $post_drip_rule['arm_item_type'] : '';
                $rule_options = maybe_unserialize($post_drip_rule['arm_rule_options']);
                $rule_item_id = $post_drip_rule['arm_item_id'];
                $rule_post_data = array();
                if (!empty($rule_item_id)) {
                    $rule_post_data = get_post($rule_item_id);
                }

                $rule_post_date = '';

                if (!empty($rule_post_data)) {

                    $rule_post_date = isset($rule_post_data->post_date) ? $rule_post_data->post_date : '';
                    $rule_post_modify_date = isset($rule_post_data->post_modified) ? $rule_post_data->post_modified : '';
                }

                if (!empty($rule_type)) {
                    switch ($rule_type) {
                        case 'instant':
                            $isDripped = false;
                            break;
                        case 'days':
                            $rule_days = isset($rule_options['days']) ? $rule_options['days'] : 0;
                            $activity_content_serialized = $wpdb->get_var("SELECT `arm_content` FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_action`= 'new_subscription' AND `arm_user_id`='$user_id' AND `arm_item_id`='$plan_id' ORDER BY `arm_activity_id` DESC");

                            $activity_content = maybe_unserialize($activity_content_serialized);

                            $startPlanDate = $activity_content['start'];
                            $datediff = $nowTime - $startPlanDate;
                            $subDays = floor($datediff / (60 * 60 * 24));
                            if ($subDays >= $rule_days) {
                                $isDripped = false;
                                break;
                            }
                            break;
                        case 'post_publish':
                            $rule_days = isset($rule_options['post_publish']) ? $rule_options['post_publish'] : 0;
                            $subDays = 0;
                            if (!empty($rule_post_date)) {
                                $datediff = $nowTime - strtotime($rule_post_date);
                                $subDays = floor($datediff / (60 * 60 * 24));
                            }


                            if ($subDays >= $rule_days) {
                                $isDripped = false;
                                break;
                            }
                            break;
                        case 'post_modify':
                            $rule_days = isset($rule_options['post_modify']) ? $rule_options['post_modify'] : 0;
                            $subDays = 0;
                            if (!empty($rule_post_modify_date)) {
                                $datediff = $nowTime - strtotime($rule_post_modify_date);
                                $subDays = floor($datediff / (60 * 60 * 24));
                            }
                            if ($subDays >= $rule_days) {
                                $isDripped = false;
                                break;
                            }
                            break;
                        case 'dates':
                            $rule_from_date = isset($rule_options['from_date']) ? $rule_options['from_date'] : '';
                            $rule_to_date = isset($rule_options['to_date']) ? $rule_options['to_date'] : '';
                            if (!empty($rule_from_date)) {
                                $rule_from_date = date('Y-m-d 00:00:00', strtotime($rule_from_date));
                                if ($nowTime > strtotime($rule_from_date)) {
                                    $isDripped = false;
                                }
                            }
                            if (!empty($rule_to_date)) {
                                $rule_to_date = date('Y-m-d 23:59:59', strtotime($rule_to_date));
                                if ($nowTime > strtotime($rule_to_date)) {
                                    $isDripped = true;
                                }
                            }


                            break;
                        default:
                            break;
                    }
                    $isDripped = apply_filters('arm_is_dripped', $isDripped, $rule_type, $rule_options);
                }

                if($isDripped==false && $rule_type!='days' && $item_type!='custom_content')
                {

                    $arm_drip_enable_before_subscription = isset($rule_options['arm_drip_enable_before_subscription']) ? $rule_options['arm_drip_enable_before_subscription'] : array();
                    if(isset($arm_drip_enable_before_subscription) && !empty($arm_drip_enable_before_subscription['enable_before_subscription']) )
                    {
                        $before_days = $before_days_default = isset($arm_drip_enable_before_subscription['before_days']) ? $arm_drip_enable_before_subscription['before_days'] : 0;

                        $activity_content_serialized = $wpdb->get_var("SELECT `arm_content` FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_action`= 'new_subscription' AND `arm_user_id`='$user_id' AND `arm_item_id`='$plan_id' ORDER BY `arm_activity_id` DESC");

                        $activity_content = maybe_unserialize($activity_content_serialized);

                        $startPlanDate = $activity_content['start'];
                        $datediff = $nowTime - $startPlanDate;
                        $subDays = floor($datediff / (60 * 60 * 24));

                        if(is_numeric($subDays))
                        {
                            $before_days = $before_days_default + $subDays;
                        }

                        $datediff = $nowTime - strtotime($rule_post_date);
                        $subDays = floor($datediff / (60 * 60 * 24));
                        if ($before_days>=$subDays) {
                            $isDripped = false;
                        }
                        else {
                            $isDripped = true;
                        }
                        
                    }
                }

            }
            return $isDripped;
        }

        function arm_drip_content_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* Always Display Content For Admins */
            if (current_user_can('administrator')) {
                return do_shortcode($content);
            }
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            $defaults = array(
                'id' => 0, /* Drip Rule ID */
                'message' => '',
            );
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts($defaults, $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $hasaccess = false;
            if ($this->isDripFeature && is_user_logged_in() && !empty($id) && $id != 0) {
                $user_id = get_current_user_id();
                $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plans = !empty($user_plans) ? $user_plans : array();
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                if (!empty($user_plans) && is_array($user_plans)) {
                    foreach ($user_plans as $cp) {
                        if (in_array($cp, $suspended_plan_ids)) {
                            unset($user_plans[array_search($cp, $user_plans)]);
                        }
                    }
                }


                            $arm_primary_status = arm_get_member_status($user_id);
                            if($arm_primary_status == 3){
                                $user_plans = array(-5);
                            }

                // no need to pass -2 because we not provide functionality for users haveing no plan for custom drip content


                if (!empty($user_plans) && is_array($user_plans)) {
                    foreach ($user_plans as $user_plan) {
                        $userDripRule = $wpdb->get_row("SELECT `arm_rule_id`, `arm_item_id`, `arm_item_type`, `arm_rule_type`, `arm_rule_options`, `arm_rule_plans` FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_rule_id`='{$id}' AND `arm_item_type`='custom_content' AND `arm_rule_status`='1' AND FIND_IN_SET({$user_plan}, `arm_rule_plans`)", ARRAY_A);
                        if (!empty($userDripRule)) {
                            $is_dripped = $this->arm_is_dripped($userDripRule, $user_id, $user_plan);
                            if (!$is_dripped) {
                                $hasaccess = true;
                            }
                        }
                    }
                }
            }
            $hasaccess = apply_filters('arm_drip_content_shortcode_hasaccess', $hasaccess, $opts);
        
            if($hasaccess) {
                return do_shortcode($content);
            } else {
                return do_shortcode($message);
            }
        }

        function arm_check_post_have_drip_rule($post_id = 0, $post_type = 'post') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $post_have_drip = false;
            if (!empty($post_id) && $post_id != 0) {
                $postRule = $wpdb->get_row("SELECT `arm_rule_id` FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_item_id` IN ({$post_id},0) AND `arm_item_type` = '{$post_type}' AND `arm_rule_status`='1' ORDER BY `arm_rule_id` DESC", ARRAY_A);
                if (!empty($postRule)) {
                    $post_have_drip = true;
                }
            }
            return $post_have_drip;
        }

        function arm_get_post_drip_rule($user_id = 0, $plan_id = 0, $post_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $user_id = (!empty($user_id) && $user_id != 0) ? $user_id : get_current_user_id();
            $postRule = false;
            if (!empty($post_id) && !empty($plan_id) && $plan_id != 0) {





                $postRule = $wpdb->get_row("SELECT `arm_rule_id`, `arm_item_id`, `arm_item_type`, `arm_rule_type`, `arm_rule_options`, `arm_rule_plans` FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_item_id`='{$post_id}' AND FIND_IN_SET({$plan_id}, `arm_rule_plans`) AND `arm_rule_status`='1' ORDER BY `arm_rule_id` DESC", ARRAY_A);
                if (!empty($postRule)) {
                    $postRule['arm_rule_status'] = $postRule['rule_status'] = 1;
                    $postRule['arm_rule_options'] = (!empty($postRule['arm_rule_options'])) ? maybe_unserialize($postRule['arm_rule_options']) : array();
                    $postRule['rule_options'] = $postRule['arm_rule_options'];
                }
            }
            return $postRule;
        }

        function arm_drip_rule_types() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $drTypes = array(
                'instant' => __('Immediately', 'ARMember'),
                'days' => __('After certain time of subscription', 'ARMember'),
                'dates' => __('Specific date onwards', 'ARMember'),
                'post_publish' => __('After certain time of post is published', 'ARMember'),
                'post_modify' => __('After certain time of post is last modified', 'ARMember'),
            );
            return apply_filters('arm_drip_rule_types', $drTypes);
        }

        function arm_get_drip_rule($rule_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (is_numeric($rule_id) && $rule_id != 0) {
                $rule_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_rule_id`='" . $rule_id . "'", ARRAY_A);
                if (!empty($rule_data)) {
                    $rule_data['arm_rule_options'] = (!empty($rule_data['arm_rule_options'])) ? maybe_unserialize($rule_data['arm_rule_options']) : array();
                    $rule_data['rule_options'] = $rule_data['arm_rule_options'];
                }
                return $rule_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_active_drip_rule_by_post_id($post_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (is_numeric($post_id) && $post_id != 0) {
                $rule_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_item_id`='" . $post_id . "' AND `arm_rule_status`='1'", ARRAY_A);
                if (!empty($rule_data)) {
                    $rule_data['arm_rule_options'] = (!empty($rule_data['arm_rule_options'])) ? maybe_unserialize($rule_data['arm_rule_options']) : array();
                    $rule_data['rule_options'] = $rule_data['arm_rule_options'];
                }
                return $rule_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_custom_drip_rules() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $rule_data = array();
            $results = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_item_type`='custom_content' AND `arm_rule_status`='1' ORDER BY `arm_rule_id` DESC", ARRAY_A);
            if (!empty($results)) {
                foreach ($results as $rule) {
                    $ruleID = $rule['arm_rule_id'];
                    $rule['arm_rule_options'] = (!empty($rule['arm_rule_options'])) ? maybe_unserialize($rule['arm_rule_options']) : array();
                    $rule['rule_options'] = $rule['arm_rule_options'];
                    $rule_data[$ruleID] = $rule;
                }
            }
            return $rule_data;
        }

        function arm_get_drip_rules($orderby = '', $order = '', $object_type = ARRAY_A) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $object_type = !empty($object_type) ? $object_type : ARRAY_A;
            $orderby = (!empty($orderby)) ? $orderby : 'arm_rule_id';
            $order = (!empty($order) && $order == 'ASC') ? 'ASC' : 'DESC';
            $results = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_drip_rules . "` ORDER BY `" . $orderby . "` " . $order . "", $object_type);
            if (!empty($results)) {
                $rule_data = array();
                foreach ($results as $rule) {
                    if ($object_type == OBJECT) {
                        $ruleID = $rule->arm_rule_id;
                        $rule->arm_rule_options = (!empty($rule->arm_rule_options)) ? maybe_unserialize($rule->arm_rule_options) : array();
                        $rule->rule_options = $rule->arm_rule_options;
                    } else {
                        $ruleID = $rule['arm_rule_id'];
                        $rule['arm_rule_options'] = (!empty($rule['arm_rule_options'])) ? maybe_unserialize($rule['arm_rule_options']) : array();
                        $rule['rule_options'] = $rule['arm_rule_options'];
                    }
                    $rule_data[$ruleID] = $rule;
                }
                return $rule_data;
            }
            return false;
        }

        function arm_get_drip_rule_member_count($ruleID = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $totalMember = 0;
            if (!empty($ruleID) && $ruleID != 0) {
                $ruleMembers = $this->arm_get_drip_rule_members($ruleID);
                $totalMember = count($ruleMembers);
            }
            return $totalMember;
        }

        function arm_get_drip_rule_members($rule_id = 0) {
            global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $ruleMembers = array();
            $ruleData = $this->arm_get_drip_rule($rule_id);
            if (!empty($ruleData)) {
                $nowTime = strtotime(current_time('mysql'));
                $rule_id = $ruleData['arm_rule_id'];
                $post_id = $ruleData['arm_item_id'];
                $post_type = $ruleData['arm_item_type'];
                $rule_type = $ruleData['arm_rule_type'];
                $item_type = isset($ruleData['arm_item_type']) ? $ruleData['arm_item_type'] : '';
                $planIDs = (!empty($ruleData['arm_rule_plans'])) ? @explode(',', $ruleData['arm_rule_plans']) : array();

                $rule_post_data = array();
                if (!empty($post_id)) {
                    $rule_post_data = get_post($post_id);
                }

                $rule_post_date = '';

                if (!empty($rule_post_data)) {

                    $rule_post_date = isset($rule_post_data->post_date) ? $rule_post_data->post_date : '';
                    $rule_post_modify_date = isset($rule_post_data->post_modified) ? $rule_post_data->post_modified : '';
                }

                $ruleOptions = maybe_unserialize($ruleData['arm_rule_options']);
                if (!empty($planIDs)) {
                    $user_arg = array(
                        'meta_query' => array(
                            array(
                                'key' => 'arm_user_plan_ids',
                                'value' => '',
                                'compare' => '!='
                            )
                        )
                    );
                    $resultUM = get_users($user_arg);
                    if (!empty($resultUM)) {
                        $planMembers = array();
                        foreach ($resultUM as $um) {
                            $puid = $um->ID; 
                            $pids = get_user_meta($puid, 'arm_user_plan_ids', true);
                            $pids = !empty($pids) ? $pids : array();
                            $suspended_plan_ids = get_user_meta($puid, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            if (!empty($pids) && is_array($pids)) {
                                foreach ($pids as $cp) {
                                    if (in_array($cp, $suspended_plan_ids)) {
                                        unset($pids[array_search($cp, $pids)]);
                                    }
                                }
                            }

                            $arm_primary_status = arm_get_member_status($puid);
                            if($arm_primary_status == 3){
                                $pids = array(-5);
                            }

                            // function use for count and display who is able to access the content. no need to pass -2 because we not provide functionality for users haveing no plan for drip content


                            $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $puid);
                            if (!empty($pids) && is_array($pids)) {
                                $psarray = array();

                                foreach ($pids as $pid) {
                                    if (in_array($pid, $planIDs)) {

                                        $activity_content_serialized = $wpdb->get_var("SELECT `arm_content` FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_action`= 'new_subscription' AND `arm_user_id`='$puid' AND `arm_item_id`='$pid' ORDER BY `arm_activity_id` DESC");


                                        $activity_content = maybe_unserialize($activity_content_serialized);


                                        $planStart = $activity_content['start'];

                                        $subDays = 0;
                                        if (!empty($planStart)) {
                                            $datediff = $nowTime - $planStart;
                                            $subDays = floor($datediff / (60 * 60 * 24));
                                        }
                                        $psarray[$pid] = $subDays;
                                    }
                                }


                                $return_array = array_intersect($pids, $planIDs);
                                if (!empty($return_array)) {
                                    $planMembers[$puid] = array(
                                        'username' => $um->user_login,
                                        'user_email' => $um->user_email,
                                        'plan_array' => $psarray,
                                        'view_detail' => htmlentities("<center><a class='arm_openpreview' href='{$view_link}'>" . __('View Detail', 'ARMember') . "</a></center>"),
                                    );
                                }
                            }
                        }


                        if (!empty($planMembers)) {
                            if ($rule_type == 'instant') {
                                $ruleMembers = $planMembers;
                            } elseif ($rule_type == 'days') {
                                foreach ($planMembers as $user_id => $member) {
                                    $rule_days = isset($ruleOptions['days']) ? $ruleOptions['days'] : 0;
                                    $member_plan_array = $member['plan_array'];



                                    $parray = array();
                                    if (!empty($member_plan_array)) {
                                        foreach ($member_plan_array as $plan_id => $subDays) {
                                            if ($subDays >= $rule_days) {
                                                $parray[$plan_id] = $subDays;
                                            }
                                        }
                                        if (!empty($parray)) {
                                            $member['plan_array'] = $parray;
                                            $ruleMembers[$user_id] = $member;
                                        }
                                    }
                                }
                            } elseif ($rule_type == 'dates') {
                                $rule_from_date = isset($ruleOptions['from_date']) ? $ruleOptions['from_date'] : '';
                                $rule_to_date = isset($ruleOptions['to_date']) ? $ruleOptions['to_date'] : '';
                                if (!empty($rule_from_date)) {
                                    $rule_from_date = date('Y-m-d 00:00:00', strtotime($rule_from_date));
                                    if ($nowTime > strtotime($rule_from_date)) {
                                       
                                        $ruleMembers = $planMembers;
                                    }
                                }
                                if (!empty($rule_to_date)) {
                                    $rule_to_date = date('Y-m-d 23:59:59', strtotime($rule_to_date));
                                    if ($nowTime > strtotime($rule_to_date)) {

                                        $ruleMembers = array();
                                    }
                                }
                            }
                            elseif ($rule_type == 'post_publish') {

                                    $rule_days = isset($ruleOptions['post_publish']) ? $ruleOptions['post_publish'] : 0;
                                    $subDays = 0;
                                    if (!empty($rule_post_date)) {
                                        $datediff = $nowTime - strtotime($rule_post_date);
                                        $subDays = floor($datediff / (60 * 60 * 24));
                                    }
                                    if ($subDays >= $rule_days) {
                                        $ruleMembers = $planMembers;
                                    }
                            }
                            elseif ($rule_type == 'post_modify') {

                                    $rule_days = isset($ruleOptions['post_modify']) ? $ruleOptions['post_modify'] : 0;
                                    $subDays = 0;
                                    if (!empty($rule_post_modify_date)) {
                                        $datediff = $nowTime - strtotime($rule_post_modify_date);
                                        $subDays = floor($datediff / (60 * 60 * 24));
                                    }
                                    if ($subDays >= $rule_days) {
                                        $ruleMembers = $planMembers;
                                    }
                            }

                            if(!empty($ruleMembers) && $rule_type!='days' && $item_type!='custom_content')
                            {
                                $arm_drip_enable_before_subscription = isset($ruleOptions['arm_drip_enable_before_subscription']) ? $ruleOptions['arm_drip_enable_before_subscription'] : array();
                                if(isset($arm_drip_enable_before_subscription) && !empty($arm_drip_enable_before_subscription['enable_before_subscription']) )
                                {
                                    $before_days = $before_days_default = isset($arm_drip_enable_before_subscription['before_days']) ? $arm_drip_enable_before_subscription['before_days'] : 0;

                                    $activity_content_serialized = $wpdb->get_var("SELECT `arm_content` FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_action`= 'new_subscription' AND `arm_user_id`='$puid' AND `arm_item_id`='$pid' ORDER BY `arm_activity_id` DESC");

                                    $activity_content = maybe_unserialize($activity_content_serialized);

                                    
                                    $startPlanDate = $activity_content['start'];
                                    $datediff = $nowTime - $startPlanDate;
                                    $subDays = floor($datediff / (60 * 60 * 24));

                                    if(is_numeric($subDays))
                                    {
                                        $before_days = $before_days_default + $subDays;
                                    }

                                    $datediff = $nowTime - strtotime($rule_post_date);
                                    $subDays = floor($datediff / (60 * 60 * 24));
                                    if ($before_days>=$subDays) {
                                    }
                                    else {
                                        $ruleMembers = array();
                                    }
                                    
                                }
                            }
                            
                            /* End `elseif ($rule_type == 'dates')` */
                        }/* End `if (!empty($planMembers))` */
                    }/* End `if (!empty($planIDs))` */
                }
            }


            return $ruleMembers;
        }

        function arm_get_members_for_before_dripped_reminder($rule_id = 0, $reminder_unit = 'day', $reminder_type = 0) {


            global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $ruleMembers = array();
            $ruleData = $this->arm_get_drip_rule($rule_id);

            if (!empty($ruleData)) {
                $nowTime = strtotime(current_time('mysql'));
                $rule_id = $ruleData['arm_rule_id'];
                $post_id = $ruleData['arm_item_id'];
                $post_type = $ruleData['arm_item_type'];
                $rule_type = $ruleData['arm_rule_type'];
                $planIDs = (!empty($ruleData['arm_rule_plans'])) ? @explode(',', $ruleData['arm_rule_plans']) : array();
                $ruleOptions = maybe_unserialize($ruleData['arm_rule_options']);


                $rule_post_data = array();
                if (!empty($post_id)) {
                    $rule_post_data = get_post($post_id);
                }

                $rule_post_date = '';

                if (!empty($rule_post_data)) {

                    $rule_post_date = isset($rule_post_data->post_date) ? $rule_post_data->post_date : '';
                    $rule_post_modify_date = isset($rule_post_data->post_modified) ? $rule_post_data->post_modified : '';
                }

                if (!empty($planIDs)) {

                    if ($reminder_unit == 'day') {
                        $email_days = $reminder_type;
                    } else if ($reminder_unit == 'week') {
                        $email_days = $reminder_type * 7;
                    } else if ($reminder_unit == 'month') {
                        $email_days = $reminder_type * 30;
                    } else if ($reminder_unit == 'year') {
                        $email_days = $reminder_type * 365;
                    }

                    $user_arg = array(
                        'meta_query' => array(
                            array(
                                'key' => 'arm_user_plan_ids',
                                'value' => '',
                                'compare' => '!='
                            )
                        )
                    );
                    $resultUM = get_users($user_arg);

                    if (!empty($resultUM)) {
                        $planMembers = array();

                        foreach ($resultUM as $um) {
                            $psarray = array();
                            $puid = $um->ID;
                            $pids = get_user_meta($puid, 'arm_user_plan_ids', true);

                            $suspended_plan_ids = get_user_meta($puid, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            if (!empty($pids) && is_array($pids)) {
                                foreach ($pids as $cp) {
                                    if (in_array($cp, $suspended_plan_ids)) {
                                        unset($pids[array_search($cp, $pids)]);
                                    }
                                }
                            }

                            $arm_primary_status = arm_get_member_status($puid);
                            if($arm_primary_status == 3){
                                $pids = array(-5);
                            }


                            
                            if (!empty($pids) && is_array($pids)) {
                                foreach ($pids as $pid) {

                                    if (in_array($pid, $planIDs)) {
                                        $planData = get_user_meta($puid, 'arm_user_plan_' . $pid, true);
                                        $planStart = '';
                                        if (!empty($planData)) {
                                            $planStart = $planData['arm_start_plan'];
                                        }
                                        $subDays = 0;

                                        if (!empty($planStart)) {
                                            $datediff = $nowTime - $planStart;
                                            $subDays = floor($datediff / (60 * 60 * 24));
                                        }
                                        $psarray[] = array('plan_id' => $pid, 'subscription_days' => $subDays);
                                    }
                                }
                            }
                            $return_array = array_intersect($pids, $planIDs);
                            if (!empty($return_array)) {
                                $planMembers[$puid] = array(
                                    'user_id' => $um->ID,
                                    'user_email' => $um->user_email,
                                    'email_days' => $email_days,
                                    'plan_array' => $psarray,
                                    'arm_item_id' => $post_id,
                                );
                            }
                        }



                        if (!empty($planMembers)) {
                            if ($rule_type == 'instant') {
                                $ruleMembers = $planMembers;
                            } elseif ($rule_type == 'days') {

                                foreach ($planMembers as $user_id => $member) {
                                    $rule_days = isset($ruleOptions['days']) ? $ruleOptions['days'] : 0;

                                    $member_plan_array = $member['plan_array'];
                                    if (!empty($member_plan_array)) {
                                        $parray = array();
                                        foreach ($member_plan_array as $member_plan_key => $member_plan_id) {

                                            $subDays = $member_plan_id['subscription_days'];

                                            if ($subDays <= $rule_days && $subDays >= ($rule_days - $email_days)) {
                                                $parray[] = array('plan_id' => $member_plan_id['plan_id'], 'subscription_days' => $subDays);
                                            }
                                        }
                                        if (!empty($parray)) {
                                            $member['plan_array'] = $parray;
                                            $ruleMembers[$user_id] = $member;
                                        }
                                    }
                                }
                            } elseif ($rule_type == 'dates') {
                                $rule_from_date = isset($ruleOptions['from_date']) ? $ruleOptions['from_date'] : '';
                                $rule_to_date = isset($ruleOptions['to_date']) ? $ruleOptions['to_date'] : '';
                                if (!empty($rule_from_date)) {
                                    $rule_from_date = date('Y-m-d 00:00:00', strtotime($rule_from_date));

                                    if ($nowTime >= strtotime("$rule_from_date-$email_days day")) {
                                        $ruleMembers = $planMembers;
                                    }
                                }
                            }
                            elseif ($rule_type == 'post_publish') {

                                    $rule_days = isset($ruleOptions['post_publish']) ? $ruleOptions['post_publish'] : 0;
                                    $subDays = 0;
                                    if (!empty($rule_post_date)) {
                                        $datediff = $nowTime - strtotime($rule_post_date);
                                        $subDays = floor($datediff / (60 * 60 * 24));
                                    }
                                    if ($subDays >= $rule_days) {
                                        $ruleMembers = $planMembers;
                                    }
                            }
                            elseif ($rule_type == 'post_modify') {

                                    $rule_days = isset($ruleOptions['post_modify']) ? $ruleOptions['post_modify'] : 0;
                                    $subDays = 0;
                                    if (!empty($rule_post_modify_date)) {
                                        $datediff = $nowTime - strtotime($rule_post_modify_date);
                                        $subDays = floor($datediff / (60 * 60 * 24));
                                    }
                                    if ($subDays >= $rule_days) {
                                        $ruleMembers = $planMembers;
                                    }
                            }
                        }
                    }
                }
            }
            return $ruleMembers;
        }

        function arm_get_drip_rule_items($post_type = 'page') {
            global $wpdb, $ARMember, $arm_global_settings, $arm_restriction;
            $drItems = '';
            if (!empty($_POST['action']) && $_POST['action'] == 'arm_get_drip_rule_items') {
                $post_type = isset($_POST['arm_post_type']) ? $_POST['arm_post_type'] : '';
                $response = array('status' => 'error', 'data' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            }
            if (!empty($post_type)) {
                $post_type_obj = get_post_type_object($post_type);
                if (!empty($post_type_obj)) {
                    $drpArgs = array(
                        'post_type' => $post_type,
                        'posts_per_page' => 10,
                    );
                    if ($post_type == 'page') {
                        $arm_pages = $arm_global_settings->arm_get_single_global_settings('page_settings');
                        /* Remove Member Directory Page */
                        unset($arm_pages['member_profile_page_id']);
                        unset($arm_pages['thank_you_page_id']);
                        unset($arm_pages['cancel_payment_page_id']);
                        $arm_pages = array_values(array_filter($arm_pages));
                        $drpArgs['post__not_in'] = $arm_pages;
                    }
                    $items = get_posts($drpArgs);
                    if (!empty($items)) {
                        $drItems .= '<ul>';
                        foreach ($items as $apost) {
                            $drItems .= '<li>';
                            $drItems .= '<input type="checkbox" class="arm_icheckbox" name="item_id[]" value="' . $apost->ID . '" id="arm_drip_rule_item_chk_' . $apost->ID . '">';
                            $drItems .= '<label class="arm_drip_rule_item_chk" for="arm_drip_rule_item_chk_' . $apost->ID . '">' . $apost->post_title . '</label>';
                            $drItems .= '</li>';
                        }
                        $drItems .= '</ul>';
                    } else {
                        /* Display Message if no post found! */
                    }
                }
            }
            if (!empty($_POST['action']) && $_POST['action'] == 'arm_get_drip_rule_items') {
                $response = array('status' => 'success', 'data' => $drItems);
                echo json_encode($response);
                exit;
            }
            return $drItems;
        }

        function arm_get_drip_rule_item_options($post_type = 'page') {
            global $wpdb, $ARMember, $arm_global_settings, $arm_restriction, $arm_capabilities_global;
            $drItems = $search_key = '';
            $drData = array();
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            if (!empty($_POST['action']) && $_POST['action'] == 'arm_get_drip_rule_item_options') {
                $post_type = isset($_POST['arm_post_type']) ? $_POST['arm_post_type'] : '';
                $search_key = isset($_POST['search_key']) ? $_POST['search_key'] : '';
                $response = array('status' => 'error', 'data' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            }
            if (!empty($post_type)) {

                $post_type_obj = get_post_type_object($post_type);
                if (!empty($post_type_obj)) {
                    $drpArgs = array();

                    $arm_sel_post_title = $wpdb->prepare( "SELECT ID from $wpdb->posts WHERE post_type=%s AND post_title like %s LIMIT 0,10", $post_type, $wpdb->esc_like($search_key).'%' );
                    $arm_get_result_titles = $wpdb->get_results($arm_sel_post_title);
                    if(!empty($arm_get_result_titles))
                    {
                        $arm_search_post_ids = array();
                        foreach($arm_get_result_titles as $arm_get_result_title)
                        {
                            $arm_search_post_ids[] .= $arm_get_result_title->ID;
                        }
                        if(!empty($arm_search_post_ids))
                        {
                            $drpArgs['post__in'] = $arm_search_post_ids;
                        }
                    }

                    $drpArgs['post_type'] = $post_type;
                    $drpArgs['s'] = $search_key;
                    $drpArgs['posts_per_page'] = 10;

                    if ($post_type == 'page') {
                        $arm_pages = $arm_global_settings->arm_get_single_global_settings('page_settings');
                        /* Remove Member Directory Page */
                        unset($arm_pages['member_profile_page_id']);
                        unset($arm_pages['thank_you_page_id']);
                        unset($arm_pages['cancel_payment_page_id']);
                        $arm_pages = array_values(array_filter($arm_pages));
                        $drpArgs['post__not_in'] = $arm_pages;
                    }

                    $items = get_posts($drpArgs);
                    if (!empty($items)) {

                        if ($post_type == 'reply') {
                            foreach ($items as $apost) {

                                $posts_sql1 = "SELECT `post_title`  FROM `" . $wpdb->posts . "` WHERE `ID` = " . $apost->post_parent;
                                $post_result = $wpdb->get_row($posts_sql1);
                                $post_reply_title = $post_result->post_title;

                                $post_title = __('Reply To:', 'ARMember') . $post_reply_title . " (<i>#" . $apost->ID . "</i>)";

                                $drData[] = array(
                                    'id' => $apost->ID,
                                    'value' => $post_title,
                                    'label' => $post_title
                                );
                                $drItems .= '<li class="active-result arm_drip_rule_item_box arm_drip_rule_item_box_' . $apost->post_parent . '" data-id="' . $apost->post_parent . '">';
                                $drItems .= '<input type="hidden" name="item_id[]" value="' . $apost->post_parent . '" data-id="' . $apost->post_parent . '">';
                                $drItems .= '<label class="arm_drip_rule_item_chk">' . $post_title . '</label>';
                                $drItems .= '</li>';
                            }
                        } else {
                            foreach ($items as $apost) {
                                $drData[] = array(
                                    'id' => $apost->ID,
                                    'value' => $apost->post_title,
                                    'label' => $apost->post_title,
                                );
                                $drItems .= '<li class="active-result arm_drip_rule_item_box arm_drip_rule_item_box_' . $apost->ID . '" data-id="' . $apost->ID . '">';
                                $drItems .= '<input type="hidden" name="item_id[]" value="' . $apost->ID . '" data-id="' . $apost->ID . '">';
                                $drItems .= '<label class="arm_drip_rule_item_chk">' . $apost->post_title . '</label>';
                                $drItems .= '</li>';
                            }
                        }
                    } else {
                        /* Display Message if no post found! */
                    }
                }
            }
            if (!empty($_POST['action']) && $_POST['action'] == 'arm_get_drip_rule_item_options') {
                $response = array('status' => 'success', 'data' => $drData);
                echo json_encode($response);
                exit;
            }
            return $drItems;
        }

        function arm_add_drip_rule($posted_data = array()) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_slugs,$arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $posted_data = (isset($_POST) && !empty($_POST)) ? $_POST : $posted_data;
            $response = array('status' => 'error', 'message' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $item_type = isset($posted_data['item_type']) ? sanitize_text_field($posted_data['item_type']) : 'post';
            $item_ids = (isset($posted_data['item_id'])) ? $posted_data['item_id'] : array();
            $rule_plans = (isset($posted_data['rule_plans'])) ? $posted_data['rule_plans'] : array();
            $rule_plans_array = $rule_plans;
            $rule_status = (isset($posted_data['rule_status'])) ? intval($posted_data['rule_status']) : 1;
            if ($item_type == 'custom_content') {
                $item_ids = array(0);
            }
            if (!empty($item_ids) && !empty($rule_plans)) {
                $rule_plans = trim(implode(',', $rule_plans), ',');
                $rule_options = maybe_serialize($posted_data['rule_options']);
                $is_duplicate = false;
                if($item_type!='custom_content')
                {
                    foreach ($item_ids as $id) {
                            foreach ($rule_plans_array as $rp) {

                                $userDripRule = $wpdb->get_var("SELECT `arm_rule_id` FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE  FIND_IN_SET({$rp}, `arm_rule_plans`) AND FIND_IN_SET({$id}, `arm_item_id`) AND `arm_item_type` = '{$item_type}'");
                                if (!empty($userDripRule)) {

                                    $is_duplicate = true;
                                    break;
                                }
                            }
                    }
                }

                if ($is_duplicate) {
                    $message = __('Duplicate Rules cannot be added.', 'ARMember');
                    $status = 'error';
                } else {
                    foreach ($item_ids as $id) {
                        $ruleData = array(
                            'arm_item_id' => $id,
                            'arm_item_type' => $item_type,
                            'arm_rule_type' => isset($posted_data['rule_type']) ? $posted_data['rule_type'] : 'instant',
                            'arm_rule_options' => $rule_options,
                            'arm_rule_plans' => $rule_plans,
                            'arm_rule_status' => $rule_status,
                            'arm_created_date' => date('Y-m-d H:i:s'),
                        );
                        $wpdb->insert($ARMember->tbl_arm_drip_rules, $ruleData);

                        $check_exists_post_meta = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total FROM `".$wpdb->prefix."postmeta` WHERE post_id = %d AND meta_key = %s AND meta_value = %d",$id,'arm_access_plan','0'));
                        if( $check_exists_post_meta[0]->total == 0 ){
                            update_post_meta($id, 'arm_access_plan', '0');
                        }
                        do_action('arm_update_access_plan_for_drip_rules',$id);
                    }
                    $message = __('Rules has been added successfully.', 'ARMember');
                    $status = 'success';
                }




                $response = array('status' => $status, 'message' => $message);
            } else {
                if (empty($item_ids)) {
                    $message = __('Please select atleast one page/post.', 'ARMember');
                    $status = 'error';
                    $response = array('status' => 'error', 'message' => __('Please select atleast one page/post.', 'ARMember'));
                } elseif (empty($rule_plans)) {
                    $message = __('Please select atleast one plan.', 'ARMember');
                    $status = 'error';
                    $response = array('status' => 'error', 'message' => __('Please select atleast one plan.', 'ARMember'));
                }
            }
            if (isset($posted_data['action']) && $posted_data['action'] == 'arm_add_drip_rule') {

                if ($status == 'success') {
                    $ARMember->arm_set_message($status, $message);
                }
                $drip_rule_link = admin_url('admin.php?page=' . $arm_slugs->drip_rules);

                $response['redirect_to'] = $drip_rule_link;
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        }

        function arm_edit_drip_rule_data() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $return = array('status' => 'error');
            if (isset($_POST['action']) && isset($_POST['rule_id']) && $_POST['rule_id'] != '') {
                $rule_id = intval($_POST['rule_id']);

                $ruleData = $this->arm_get_drip_rule($rule_id);
                if ($ruleData) {
                    $postTypeObj = get_post_type_object($ruleData['arm_item_type']);
                    if ($postTypeObj) {
                        $postTypeName = (!empty($postTypeObj->labels->singular_name)) ? $postTypeObj->labels->singular_name : $postTypeObj->label;
                    } else {
                        $postTypeName = __('Post', 'ARMember');
                    }
                    $item_title = '';
                    if ($ruleData['arm_item_type'] == 'custom_content') {
                        $item_title = "<div class='arm_drip_post_type_label'>" . __('Shortcode', 'ARMember') . "</div>";
                        $item_title .= "<div class='arm_drip_custom_content_shortcode'>
							<pre>[arm_drip_content id='{$rule_id}']</pre>
							<pre>    " . __('Put Your Drip Content Here.', 'ARMember') . "</pre>
							<pre>[/arm_drip_content]</pre>
						</div>";
                    } else {
                        $item_title = "<span class='arm_drip_post_type_label'>" . $postTypeName . " " . __('Name', 'ARMember') . "</span>";
                        if ($ruleData['arm_item_id'] == 0) {
                            $item_title .= "<span class='arm_drip_item_name_label'>" . __('All ' . $postTypeName . 's', 'ARMember') . "</span>";
                        } else {

                            $item_title .= "<span class='arm_drip_item_name_label'>" . get_the_title($ruleData['arm_item_id']) . "</span>";
                        }
                    }
                    $ruleData['rule_options'] = maybe_unserialize($ruleData['arm_rule_options']);
                    $arm_drip_rules_enable_subscription = !empty($ruleData['rule_options']['arm_drip_enable_before_subscription']) ? $ruleData['rule_options']['arm_drip_enable_before_subscription'] : array();
                    $enable_before_subscription = !empty($arm_drip_rules_enable_subscription['enable_before_subscription']) ? $arm_drip_rules_enable_subscription['enable_before_subscription'] : 0;
                    $before_days = !empty($arm_drip_rules_enable_subscription['before_days']) ? $arm_drip_rules_enable_subscription['before_days'] : 0;
                    $rule_plans = $ruleData['arm_rule_plans'];
                    $dr_plans = @explode(',', $rule_plans);
                    $return = array(
                        'status' => 'success',
                        'rule_id' => $rule_id,
                        'item_id' => $ruleData['arm_item_id'],
                        'item_type' => $ruleData['arm_item_type'],
                        'rule_type' => $ruleData['arm_rule_type'],
                        'rule_days' => isset($ruleData['rule_options']['days']) ? $ruleData['rule_options']['days'] : 10,
                        'rule_post_publish' => isset($ruleData['rule_options']['post_publish']) ? $ruleData['rule_options']['post_publish'] : 10,
                        'rule_post_modify' => isset($ruleData['rule_options']['post_modify']) ? $ruleData['rule_options']['post_modify'] : 10,
                        'rule_from_date' => isset($ruleData['rule_options']['from_date']) ? $ruleData['rule_options']['from_date'] : date('Y-m-d'),
                        'rule_to_date' => isset($ruleData['rule_options']['to_date']) ? $ruleData['rule_options']['to_date'] : '',
                        'rule_options' => $ruleData['rule_options'],
                        'rule_plans' => $dr_plans,
                        'rule_status' => $ruleData['arm_rule_status'],
                        'item_title' => $item_title,
                        'enable_before_subscription' => $enable_before_subscription,
                        'before_days' => $before_days,
                        'enable_page_type_text' => $postTypeName
                    );
                }
            }
            echo json_encode($return);
            exit;
        }

        function arm_update_drip_rule() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $message = __('Sorry, Something went wrong. Please try again.', 'ARMember');
            $status = 'error';
            $response = array('status' => 'error', 'message' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['action']) && $_POST['action'] == 'arm_update_drip_rule') {
                $rule_plans_array = (isset($_POST['rule_plans'])) ? $_POST['rule_plans'] : array();
                if (!empty($rule_plans_array)) {
                    $rule_id = intval($_POST['rule_id']);
                    $item_id = intval($_POST['item_id']);
                    $item_type = sanitize_text_field($_POST['item_type']);
                    $rule_plans = trim(implode(',', $rule_plans_array), ',');
                    $rule_options = maybe_serialize($_POST['rule_options']);



                    $is_duplicate = false;
                    if($item_type!='custom_content')
                    {
                        foreach ($rule_plans_array as $rp) {
                            $userDripRule = $wpdb->get_var("SELECT `arm_rule_id` FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE  FIND_IN_SET({$rp}, `arm_rule_plans`) AND `arm_item_id` = {$item_id} AND `arm_item_type` = '{$item_type}' AND `arm_rule_id` != {$rule_id}");
                            if (!empty($userDripRule)) {

                                $is_duplicate = true;
                                break;
                            }
                        }
                    }

                    

                    if ($is_duplicate) {
                        $message = __('Duplicate Rule Found.', 'ARMember');
                        $status = 'error';
                    } else {

                        $ruleData = array(
                            'arm_rule_type' => isset($_POST['rule_type']) ? $_POST['rule_type'] : 'instant',
                            'arm_rule_options' => $rule_options,
                            'arm_rule_plans' => $rule_plans,
                        );




                        $wpdb->update($ARMember->tbl_arm_drip_rules, $ruleData, array('arm_rule_id' => $rule_id));
                        $message = __('Rule Updated Successfully.', 'ARMember');
                        $status = 'success';
                    }


                    $response = array('status' => $status, 'message' => $message);
                } else {
                    $message = __('Please select atleast one plan.', 'ARMember');
                    $status = 'error';
                    $response = array('status' => 'error', 'message' => __('Please select atleast one plan.', 'ARMember'));
                }
            }
            $ARMember->arm_set_message($status, $message);
            $drip_rule_link = admin_url('admin.php?page=' . $arm_slugs->drip_rules);
            if ($status == 'success') {
                $response['redirect_to'] = $drip_rule_link;
            }
            echo json_encode($response);
            die();
        }

        function arm_update_drip_rule_status() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['rule_id']) && $_POST['rule_id'] != 0) {
                $rule_id = intval($_POST['rule_id']);
                $rule_status = (!empty($_POST['rule_status'])) ? intval($_POST['rule_status']) : 0;
                $wpdb->update($ARMember->tbl_arm_drip_rules, array('arm_rule_status' => $rule_status), array('arm_rule_id' => $rule_id));
                $response = array('type' => 'success', 'msg' => __('Rule Updated Successfully.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }

        function arm_delete_single_drip_rule() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $action = $_POST['act'];
            $id = intval($_POST['id']);
            if ($action == 'delete') {
                if (empty($id)) {
                    $errors[] = __('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_drip_rules')) {
                        $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                    } else {
                        $res_var = $wpdb->delete($ARMember->tbl_arm_drip_rules, array('arm_rule_id' => $id));
                        if ($res_var) {
                            $message = __('Rule has been deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_delete_bulk_drip_rules() {
            if (!isset($_POST)) {
                return;
            }
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $bulkaction = $arm_global_settings->get_param('action1');
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            if ($bulkaction == -1) {
                $bulkaction = $arm_global_settings->get_param('action2');
            }
            $ids = $arm_global_settings->get_param('item-action', '');
            if (empty($ids)) {
                $errors[] = __('Please select one or more records.', 'ARMember');
            } else {
                if (!current_user_can('arm_manage_drip_rules')) {
                    $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                } else {
                    if (!is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    if (is_array($ids)) {
                        if ($bulkaction == 'delete_drip_rule') {
                            foreach ($ids as $rule_id) {
                                $res_var = $wpdb->delete($ARMember->tbl_arm_drip_rules, array('arm_rule_id' => $rule_id));
                            }
                            if ($res_var) {
                                $message = __('Rule(s) has been deleted successfully.', 'ARMember');
                            }
                        } else {
                            $errors[] = __('Please select valid action.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            $ARMember->arm_set_message('success', $message);
            echo json_encode($return_array);
            exit;
        }

        function arm_delete_post_drip_rules($postID) {
            global $wpdb, $post, $pagenow, $ARMember, $arm_global_settings, $arm_subscription_plans;
            if (!empty($postID) && $postID != 0) {
                $res_var = $wpdb->delete($ARMember->tbl_arm_drip_rules, array('arm_item_id' => $postID));
            }
        }

        function arm_filter_drip_rules_list() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules_list_records.php')) {
                include( MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules_list_records.php');
            }
            die();
        }

        function arm_get_drip_rule_members_data_func() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_drip_rules'], '1');
            $ruleID = isset($_REQUEST['rule_id']) ? $_REQUEST['rule_id'] : 0;
            $response = array('status' => 'error', 'data' => array());
            if(0 != $ruleID) {
                $membersDatasDefault = array();
                $response['status'] = "success";
                $response['data'] = $membersDatasDefault;

                global $arm_drip_rules;
                $dripRulesMembers = array();
                $dripAllowMembers = $arm_drip_rules->arm_get_drip_rule_members($ruleID);
                $dripRulesMembers[$ruleID] = $dripAllowMembers;
                if(!empty($dripRulesMembers)) {
                    foreach($dripRulesMembers as $ruleID => $members) {
                        if (!empty($members)) {
                            $membersData = array();
                            foreach($members as $mData){
                                $subDays = '';
                                $plan_array = $mData['plan_array'];
                                if(!empty($plan_array) && is_array($plan_array)){
                                    $subDays = '<ul>';
                                    foreach($plan_array as $plan_id => $sub_days){
                                        $plan_obj = new ARM_Plan($plan_id);
                                        $plan_name = $plan_obj->name;
                                        if($sub_days < 0) {
                                            $sub_days = 0;
                                        }
                                        $subDays .= "<li>{$plan_name} : {$sub_days}</li>";
                                    }
                                    $subDays .= '</ul>';
                                }
                              
                                $membersDatas = array();
                                
                                $membersDatas['username'] = $mData['username'];
                                $membersDatas['user_email'] = $mData['user_email'];
                                $membersDatas['subscription_days'] = "<center>{$subDays}</center>";
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

    }

}

global $arm_drip_rules;
$arm_drip_rules = new ARM_drip_rules();
