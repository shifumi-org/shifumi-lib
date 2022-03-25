<?php
if (!class_exists('ARM_member_forms')) {

    class ARM_member_forms {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            add_action('wp_ajax_save_member_forms', array($this, 'save_member_forms')); 
            add_action('wp_ajax_add_new_member_form', array($this, 'arm_add_new_member_form'));
            add_action('wp_ajax_check_unique_set_name', array($this, 'arm_check_unique_set_name'));
            add_action('wp_ajax_arm_delete_form', array($this, 'arm_delete_form'));
            add_action('wp_ajax_arm_delete_form_field', array($this, 'arm_delete_form_field'));
            add_action('wp_ajax_arm_create_new_field', array($this, 'arm_create_new_field'));
            add_action('wp_ajax_arm_get_updated_social_profile_fields_html', array($this, 'arm_get_updated_social_profile_fields_html'));
            add_action('wp_ajax_arm_get_updated_field_html', array($this, 'arm_get_updated_field_html'));
            add_action('wp_ajax_arm_roles_field_options', array($this, 'arm_roles_field_options'));
            add_action('wp_ajax_arm_prefix_suffix_field_html', array($this, 'arm_prefix_suffix_field_html'));
            add_action('wp_ajax_arm_ajax_generate_form_styles', array($this, 'arm_ajax_generate_form_styles'));
            /* Member Forms Shortcode Ajax Action */
            add_action('wp_ajax_arm_shortcode_form_ajax_action', array($this, 'arm_shortcode_form_ajax_action'));
            add_action('wp_ajax_nopriv_arm_shortcode_form_ajax_action', array($this, 'arm_shortcode_form_ajax_action'));
            /* Check Already Exist Field Value */
            add_action('wp_ajax_arm_check_exist_field', array($this, 'arm_check_exist_field'));
            add_action('wp_ajax_nopriv_arm_check_exist_field', array($this, 'arm_check_exist_field'));
            /* Remove Uploaded File */
            add_action('wp_ajax_arm_remove_uploaded_file', array($this, 'arm_remove_uploaded_file'));
            add_action('wp_ajax_nopriv_arm_remove_uploaded_file', array($this, 'arm_remove_uploaded_file'));

            add_action('wp_ajax_arm_get_all_preset_fields', array($this, 'arm_get_all_preset_fields'));

            /* Shortcode For Member Forms */
            add_shortcode('arm_form', array($this, 'arm_form_shortcode_func'));
            add_shortcode('arm_edit_profile', array($this, 'arm_edit_profile_shortcode_func'));
            add_shortcode('arm_logout', array($this, 'arm_logout_shortcode_func'));
            add_shortcode('arm_cancel_membership', array($this, 'arm_cancel_membership_shortcode_func'));
            add_shortcode('arm_profile_detail', array($this, 'arm_profile_detail_shortcode_func') );

            add_filter('arm_change_field_options', array($this, 'arm_filter_form_field_options'));
            add_action('arm_before_render_form', array($this, 'arm_check_form_include_js_css'), 10, 2);

            add_action('arm_member_update_meta', array($this, 'arm_member_update_meta_details'), 10, 2);
            add_action('arm_admin_save_member_details', array($this, 'arm_admin_save_member_details'));

            add_action('wp_ajax_arm_get_spf_in_tinymce', array($this, 'arm_get_spf_in_tinymce'));
            /* Insert Login History When user logged in */
            add_action('set_logged_in_cookie', array($this, 'arm_add_login_history_for_set_logged_in_cookie'), 10, 5);
            /* Update Logout Entery  */
            add_action('clear_auth_cookie', array($this, 'arm_update_login_history'), 10);
            /* Get User Login History in admin */
            add_action('wp_ajax_arm_get_login_history', array($this, 'arm_get_login_history_func'));
            add_filter('registration_errors', array($this, 'armforceError'), 10, 1);
            /* Reinitialize session for spam filter if any error occured while submit the form. for e.g. wrong password */
            add_action('wp_ajax_arm_reinit_session', array($this, 'arm_reinit_session_filter_var'));
            add_action('wp_ajax_nopriv_arm_reinit_session', array($this, 'arm_reinit_session_filter_var'));
            add_filter('arm_change_popup_form_content', array($this, 'arm_change_content_after_display_form_function'), 10, 4);
            add_action('arm_remove_third_party_error', array($this, 'arm_remove_bot_error'), 10, 1);
            //add_action('init', array($this, 'arm_auto_lock_shared_account'));
            add_filter('send_password_change_email', array($this, 'arm_send_change_password_default_email'), 10, 3);
            add_filter('send_email_change_email', array($this, 'arm_send_change_password_default_email'), 10, 3);
            add_filter('the_content', array($this, 'arm_the_filtered_content'));

            add_action('wp_ajax_arm_update_preset_form_fields', array($this, 'arm_update_preset_form_fields'));

            add_action('wp_ajax_get_field_option', array($this, 'arm_get_field_option_by_meta'), 10, 1);

            add_action('wp_ajax_add_new_profile_form', array($this, 'arm_add_new_profile_form'));

            add_filter( 'get_user_metadata', array($this,'armember_update_user_data'), 10, 4 );
        }

        function arm_get_field_option_by_meta($meta_key = '', $form_id = 0) {
            global $wpdb, $ARMember;
            
            $meta = (isset($_GET['meta'])) ? sanitize_text_field($_GET['meta']) : $meta_key;

            $qry_str = "SELECT `arm_form_field_option` FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_slug` = '$meta'";

            if( $form_id != 0 ) {
                $qry_str .= " AND arm_form_field_form_id = " . $form_id;
            }

            $opts_arr = $wpdb->get_results($qry_str, ARRAY_A);
            $opts = array();
            if( !empty($opts_arr) ) {
                $opts = array_column($opts_arr, "arm_form_field_option");
                $opts = maybe_unserialize($opts[0]);
            }
            if(isset($_GET['meta'])) {
                $opts = $opts['options'];
                echo json_encode($opts);
                exit;
            }
            else {
                return $opts;
            }
        }

        function arm_update_preset_form_fields(){
            global $arm_member_forms, $ARMember, $arm_capabilities_global;
            $response = array('type'=>'error');

            
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_update_preset_form_fields') {
               $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $arm_posted_data = isset($_POST['preset_fields']) ? $_POST['preset_fields']: array();
                    $presetFormFields = get_option('arm_preset_form_fields', '');
                    $dbFormFields = maybe_unserialize($presetFormFields);
                    $new_options = array();

                    if (isset($arm_posted_data) && !empty($arm_posted_data)) {
                        foreach ($arm_posted_data as $key => $arm_field_key) {
                            $label = isset($arm_field_key['label']) ? $arm_field_key['label'] : '';
                            $options = isset($arm_field_key['options']) ? $arm_field_key['options'] : '';

                            if(!empty($options)){
                                $options = array_map('trim', explode("\n", $options));
                                $new_options = $options;
                                if (is_array($options)) {
                                    $new_options = array();
                                    foreach ($options as $data) {
                                        if ($data != '') {
                                            $new_options[] = stripslashes($data);
                                        }
                                    }
                                }
                            }

                            if(isset($dbFormFields['default'][$key])){
                                    if(isset($dbFormFields['default'][$key]['label'])){
                                        $dbFormFields['default'][$key]['label'] = $label;
                                    }
                                    if(in_array($dbFormFields['default'][$key]['type'], array('radio', 'checkbox', 'select'))){
                                            if(isset($dbFormFields['default'][$key]['options'])){
                                                $dbFormFields['default'][$key]['options'] = $new_options;
                                            }
                                    }
                                    
                            }
                            else if(isset($dbFormFields['other'][$key])){
                                    if(isset($dbFormFields['other'][$key]['label'])){
                                        $dbFormFields['other'][$key]['label'] = $label;
                                    }
                                    if(in_array($dbFormFields['other'][$key]['type'], array('radio', 'checkbox', 'select'))){
                                            if(isset($dbFormFields['other'][$key]['options'])){
                                                $dbFormFields['other'][$key]['options'] = $new_options;
                                            }
                                    }
                            }
                        }
                    }

                    
                    update_option('arm_preset_form_fields', $dbFormFields);
                    $response = array('type'=>'success');
            }
            echo json_encode($response);
            die();
        }

        function arm_get_all_preset_fields(){
            global $arm_member_forms, $ARMember, $arm_capabilities_global;
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_get_all_preset_fields') {
                $content = '';
                $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                if (!empty($dbFormFields)) {
                    $content .= '<table class="arm_preset_field_table" cellpadding="0" cellspacing="0">';
                    $content .= '<tr>';
                                            $content .= '<th style="width: 250px;">';
                                                    $content .= __('Field Key', 'ARMember');
                                            $content .= '</th>';

                                            $content .= '<th style="width: 290px;">';
                                                    $content .= __('Field Label', 'ARMember');
                                            $content .= '</th>';

                                            $content .= '<th>';
                                                $content .= __('Field Options', 'ARMember');
                                            $content .= '</th>';
                                    $content .= '</tr>';
                    
                    foreach ($dbFormFields as $meta_key => $field) {
                      
                        $field_options = maybe_unserialize($field);
                        $field_options = apply_filters('arm_change_field_options', $field_options);
                        $exclude_keys = array('repeat_pass',
                            'arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section',
                            'repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover'
                        );
                        $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                        $label = isset($field_options['label']) ? $field_options['label'] : '';
                        $options = isset($field_options['options']) ? $field_options['options'] : array();
                        $type = isset($field_options['type']) ? $field_options['type'] : array();
                        $old_options = '';
                        if (!empty($options)) {
                            foreach ($options as $key => $opt) {
                                $opt = stripslashes($opt);
                                $old_options .= "$opt\n";
                            }
                        }
                       
                        if (!in_array($meta_key, $exclude_keys) && !in_array($type, array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
                            
                                    $content .= '<tr>';
                                            $content .= '<td>';
                                                    $content .= $meta_key;
                                            $content .= '</td>';

                                            $content .= '<td>';
                                                    $content .= '<input type="text" name="preset_fields['.trim($meta_key).'][label]" value="'.$label.'" class="arm_preset_form_field">';
                                            $content .= '</td>';

                                            $content .= '<td>';
                                                if(in_array($type, array('radio', 'checkbox', 'select'))){
                                                    $content .= '<textarea class="arm_preset_form_field" name="preset_fields['.trim($meta_key).'][options]">'.$old_options.'</textarea>
                                                        <p class="description">';
                                                $content .= __('You should place each option on a new line.', 'ARMember'); 
                                                      $content .= '<br/>';
                                                $content .= __('Separate values format should be label:value.', 'ARMember');
                                                if(trim($meta_key)=='country')
                                                {
                                                        $content .= '<br/>';
                                                        $content .=  '<font color="red">'.__('Please don\'t change value of country ID. For example: :1,:2,:3', 'ARMember').'</font>';
                                                }
                                                     $content .= '</p>';
                                                       //  $content .= '<textarea name="">fdsfdsf</textarea>';  
                                                }
                                            $content .= '</td>';
                                    $content .= '</tr>';
                            
                        }
                    }
                     $content .= '<table>';   
                }
                echo $content;
                die(); 
            }
        }

        function arm_the_filtered_content($content) {

            if (isset($_GET['arm-key']) && !empty($_GET['arm-key'])) {
                
                $chk_key = stripslashes_deep(sanitize_text_field($_GET['arm-key']));
                $user_email = stripslashes_deep(sanitize_email($_GET['email']));
                
                $arm_message = $this->arm_verify_user_activation_for_front($user_email, $chk_key);
                $message = '';
                if ($arm_message['status'] == 'error') {
                    $message .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_account_verify_error_container"><div class="arm_error_msg"><ul><li>';
                    $message .= $arm_message['message'];
                    $message .= '</li></ul></div></div>';
                } else {
                    $message .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_account_verify_success_container"><div class="arm_success_msg"><ul><li>';
                    $message .= $arm_message['message'];
                    $message .= '</li></ul></div></div>';
                }
                $content = $message . $content;
            }
            return $content;
        }
        
        
        
        
        

        function arm_send_change_password_default_email($return, $user, $userdata) {
            $return = false;
            return $return;
        }

        function arm_remove_bot_error($arm_errors) {
            if (isset($arm_errors->errors['bot_error'])) {
                unset($arm_errors->errors['bot_error']);
            }
            return $arm_errors;
        }

        function arm_change_content_after_display_form_function($content, $form, $atts, $formRandomID="" ) {

            global $arm_global_settings;
            if (isset($form) && !empty($form)) {
                if (is_user_logged_in()) {

                    $already_logged_in_msg = isset($arm_global_settings->common_message['arm_armif_already_logged_in']) ? $arm_global_settings->common_message['arm_armif_already_logged_in'] : '';
                    if (in_array($form->type, array('login', 'signin', 'logout', 'log-out', 'signout', 'sign-out'))) {
                        $already_logged_in_message = (isset($atts['logged_in_message']) && !empty($atts['logged_in_message'])) ? $atts['logged_in_message'] : $already_logged_in_msg;
                        return $already_logged_in_message_div = '<div class="arm_already_logged_in_message_popup" id="arm_already_logged_in_message_popup">' . $already_logged_in_message . '</div>';
                    }
                    if (!is_admin() && in_array($form->type, array('registration', 'forgot_password', 'lostpassword', 'retrievepassword'))) {

                        $already_logged_in_message = (isset($atts['logged_in_message']) && !empty($atts['logged_in_message'])) ? $atts['logged_in_message'] : $already_logged_in_msg;
                        return $already_logged_in_message_div = '<div class="arm_already_logged_in_message_popup" id="arm_already_logged_in_message_popup">' . $already_logged_in_message . '</div>';
                    }
                }
            }
            return $content;
        }

        function armforceError($errors) {
            if (!empty($errors->errors)) {

                if (count($errors->errors) == 1 && isset($errors->errors['dm_ec_force_error'])) {

                    unset($errors->errors['dm_ec_force_error']);
                }
            }
            return $errors;
        }

        function arm_remove_uploaded_file() {
            global $wpdb, $ARMember, $arm_slugs;

            $denyExts = array("php", "php3", "php4", "php5", "pl", "py", "jsp", "asp", "exe", "cgi", "css", "js", "html", "htm");

            if (!empty($_POST['file_name'])) 
            {
                $file_name = basename( sanitize_text_field($_POST['file_name']) );
                $file_name_arm = substr($file_name, 0,3);

                $checkext = explode(".", $file_name);
                $ext = strtolower( $checkext[count($checkext) - 1] );

                if(!empty($ext) && !in_array($ext, $denyExts) && !empty($file_name) && $file_name_arm=='arm') 
                {
                    if (isset($_POST['type']) && $_POST['type'] == 'badges') 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/social_badges/' . sanitize_text_field($file_name);
                    } 
                    elseif (isset($_POST['type']) && $_POST['type'] == 'social_icon') 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/social_icon/' . sanitize_text_field($file_name);
                    } 
                    else 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/' . sanitize_text_field($file_name);
                    }

                    if (file_exists($file_path)) 
                    {

                        @unlink($file_path);

                        if (is_user_logged_in()) 
                        {
                            if (isset($_POST['type']) && $_POST['type'] == 'profile_cover') 
                            {
                                delete_user_meta(get_current_user_id(), 'profile_cover');
                                do_action('arm_remove_bp_profile_cover', get_current_user_id());
                                exit;
                            }

                            if (isset($_POST['type']) && $_POST['type'] == 'profile_pic') 
                            {
                                do_action('arm_remove_bp_avatar', get_current_user_id());
                                delete_user_meta(get_current_user_id(), 'avatar');

                                $avatar = get_avatar(wp_get_current_user()->user_email, '200');
                                preg_match_all("/src='([^']+)/", $avatar, $images);

                                $avatar_url = isset($images[1][0]) ? $images[1][0] : '';
                                echo $avatar_url;
                                exit;
                            }
                        }
                    }
                }
            }

            if (!empty($_POST['file_url'])) 
            {
                $file_name = basename( sanitize_text_field($_POST['file_url']) );
                $file_name_arm = substr($file_name, 0,3);

                $checkext = explode(".", $file_name);
                $ext = strtolower( $checkext[count($checkext) - 1] );

                if(!empty($ext) && !in_array($ext, $denyExts) &&  !empty($file_name) && $file_name_arm=='arm') 
                {
                    if (isset($_POST['type']) && $_POST['type'] == 'badges') 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/social_badges/' . basename(sanitize_text_field($file_name));
                    } 
                    else if (isset($_POST['type']) && $_POST['type'] == 'social_icon') 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/social_icon/' . basename(sanitize_text_field($file_name));
                    }
                    else 
                    {
                        $file_path = MEMBERSHIP_UPLOAD_DIR . '/' . basename(sanitize_text_field($file_name));
                    }

                    if (file_exists($file_path)) 
                    {
                        unlink($file_path);
                    }
                }
                echo '1';
                exit;
            }
        }

        /**
         * `[arm_logout]` shortcode function
         */
        function arm_logout_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'label' => __('Logout', 'ARMember'),
                'type' => 'link',
                'user_info' => true,
                'redirect_to' => '',
                'link_css' => '',
                'link_hover_css' => '',
                    ), $atts, $tag);
            $atts['user_info'] = ($atts['user_info'] === 'false') ? false : true;
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $arm_slugs, $ARMember, $arm_global_settings;
            $redirect_to = (!empty($atts['redirect_to']) && $atts['redirect_to'] != '') ? $atts['redirect_to'] : ARM_HOME_URL;
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                $user_identity = '';
                if ($user->exists()) {
                    $user_identity = $user->first_name . ' ' . $user->last_name;
                    if (empty($user->first_name) && empty($user->last_name)) {
                        $user_identity = $user->user_login;
                    }
                }
                //$logout_url = wp_logout_url($redirect_to);
                $logout_url = add_query_arg(array("arm_action" => "logout", "redirect_to" => $redirect_to), ARM_HOME_URL);
                $logout_url = wp_nonce_url($logout_url);
                $logoutWrapper = arm_generate_random_code();
                $content = apply_filters('arm_before_logout_shortcode_content', $content, $atts);
                $content .= '<div class="arm_logout_form_container" id="arm_logout_' . $logoutWrapper . '">';
                $btnStyle = '';
                if (!empty($atts['link_css'])) {
                    $btnStyle .= '#arm_logout_' . $logoutWrapper . ' .arm_logout_btn{' . esc_html($atts['link_css']) . '}';
                }
                if (!empty($atts['link_hover_css'])) {
                    $btnStyle .= '#arm_logout_' . $logoutWrapper . ' .arm_logout_btn:hover{' . esc_html($atts['link_hover_css']) . '}';
                }
                if (!empty($btnStyle)) {
                    $content .= '<style type="text/css">' . $btnStyle . '</style>';
                }
                if ($atts['user_info']) {
                    $content .= '<span class="arm-logged-in-as">' . __('Logged in as', 'ARMember') . ' <a href="' . get_edit_user_link() . '">' . $user_identity . '</a>.</span>';
                    $atts['label'] = $atts['label'] . '?';
                }
                if ($atts['type'] == 'button') {
                    $content .= '<form method="post" class="arm_logout" name="arm_logout" action="' . $logout_url . '" enctype="multipart/form-data">';
                    $content .= '<button type="submit" class="arm_logout_btn arm_logout_button">' . $atts['label'] . '</button>';
                    $content .= '</form>';
                } else {
                    $content .= '<a href="' . $logout_url . '" title="' . __('Log out of this account?', 'ARMember') . '" class="arm_logout_btn arm_logout_link">' . $atts['label'] . '</a>';
                }
                $content .= '</div>';
                $content = apply_filters('arm_after_logout_shortcode_content', $content, $atts);
            }
            $ARMember->arm_check_font_awesome_icons($content);

            return do_shortcode($content);
        }

        /**
         * `[arm_cancel_membership]` shortcode function
         */
        function arm_cancel_membership_shortcode_func($atts, $content, $tag) {

            return '';
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'label' => __('Cancel Subscription', 'ARMember'),
                'type' => 'link',
                'link_css' => '',
                'link_hover_css' => '',
                    ), $atts, $tag);
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $ARMember, $arm_subscription_plans;
            if (is_user_logged_in()) {

                $user = wp_get_current_user();
                $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);


                if (!empty($plan_ids) && is_array($plan_ids)) {
                    $content = apply_filters('arm_before_cancel_membership_shortcode_content', $content, $atts);

                    $content .= '<div class="arm_cancel_payment_table_div"><table class="form-table arm_cancel_payment_table" border="1">';

                    foreach ($plan_ids as $plan_id) {
                        $planData = get_user_meta($user->ID, 'arm_user_plan_' . $plan_id, true);
                        if (!empty($planData)) {
                            $curPlanDetail = $planData['arm_current_plan_detail'];
                            $is_plan_cancelled = $planData['arm_cencelled_plan'];
                            if (!empty($curPlanDetail)) {
                                $plan_detail = new ARM_Plan(0);
                                $plan_detail->init((object) $curPlanDetail);
                            } else {
                                $plan_detail = new ARM_Plan($plan_id);
                            }

                            if ($plan_detail->exists()) {
                                if ($plan_detail->is_paid() && !$plan_detail->is_lifetime() && $plan_detail->is_recurring()) {


                                    $content .= '<tr class="form-field">
                            <th class="arm-form-table-label">' . $plan_detail->name . '</th>
                            <td class="arm-form-table-content">';


                                    if (isset($is_plan_cancelled) && $is_plan_cancelled == 'yes') {
                                        $expire_strtime = $planData['arm_expire_plan'];
                                        $expire_time = date_i18n(get_option('date_format'), $expire_strtime);

                                        $success_msg = __('Your Subscription will be cancelled on', 'ARMember') . ' ' . $expire_time . ".";
                                        $content .= $success_msg;
                                    } else {

                                        $content .= '<style type="text/css">';
                                        if (!empty($atts['link_css'])) {
                                            $link_style = esc_html($atts['link_css']);
                                            $content .= '.arm_cancel_membership_btn{' . $link_style . '}';
                                        }
                                        if (!empty($atts['link_hover_css'])) {
                                            $link_hover_style = esc_html($atts['link_hover_css']);
                                            $content .= '.arm_cancel_membership_btn:hover{' . $link_hover_style . '}';
                                        }
                                        $content .= '</style>';

                                        $content .= '<div class="arm_cancel_membership_form_container">';
                                        $content .= '<div class="arm_form_message_container arm_cancel_membership_message_container_' . $plan_id . ' arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"></div>';
                                        $content .= '<div class="armclear"></div>';
                                        $content .= '<span class="cancel-membership-in-as">';
                                        if ($atts['type'] == 'button') {
                                            $content .= '<form method="post" class="arm_cancel_membership arm_cancel_membership_form_' . $plan_id . '" name="arm_cancel_membership" action="#" enctype="multipart/form-data">';
                                            $content .= '<button type="submit" class="arm_cancel_membership_btn arm_cancel_membership_button arm_cancel_membership_button_link_' . $plan_id . '" data-plan_id = "' . $plan_id . '">' . $atts['label'] . '</button>';
                                            $content .= '</form>';
                                        } else {
                                            $content .= sprintf('<a href="%1$s" title="Cancel Subscription" class="arm_cancel_membership_btn arm_cancel_membership_link arm_cancel_membership_button_link_' . $plan_id . '" data-plan_id = "' . $plan_id . '">' . $atts['label'] . '</a>', '#');
                                        }
                                        $content .= '</span><img class="arm_cancel_membership_plan_' . $plan_id . ' arm_cancel_membership_plan_loader" src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" alt="' . __('Cancelling Subscription', 'ARMember') . '" style="display:none; width: 20px;
    margin-left: 10px;position: absolute;"></div>';
                                    }

                                    $content .= '</td>
                        </tr>';
                                }
                            }
                        }
                    }

                    $content .= '</table></div>';

                    $content = apply_filters('arm_after_cancel_membership_shortcode_content', $content, $atts);
                }
            }
            $ARMember->enqueue_angular_script();
            $ARMember->arm_check_font_awesome_icons($content);

            return do_shortcode($content);
        }

        /**
         * `[arm_edit_profile]` shortcode function
         * Default: `[arm_edit_profile title="" message="Your profile has been updated successfully."]`
         */
        function arm_edit_profile_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'title' => '',
                'form_id' => '',
                'submit_text' => __('Update Profile', 'ARMember'),
                'message' => '',
                'class' => '',
                'form_position' => 'center',
                'social_fields' => '',
                'avatar_field' => 'yes',
                'profile_cover_field' => 'yes',
                'view_profile' => false,
                'view_profile_link' => __('View Profile', 'ARMember'),
                'profile_cover_title' => '',
                'profile_cover_placeholder' => __('Drop file here or click to select', 'ARMember'),
                    ), $atts, $tag);
            $atts['view_profile'] = ($atts['view_profile'] === 'true' || $atts['view_profile'] == '1') ? true : false;
            $atts['view_profile_link'] = (!empty($atts['view_profile_link'])) ? $atts['view_profile_link'] : __('View Profile', 'ARMember');
            $atts['message'] = (!empty($atts['message'])) ? $atts['message'] : __('Your profile has been updated successfully.', 'ARMember');
            $atts['type'] = 'edit_profile';
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $content = '';
            $formRandomID = '';
            if (is_user_logged_in()) {
                $default_form_id = $this->arm_get_default_form_id('registration');
                $user_id = get_current_user_id();
                if (isset($atts['form_id']) && !empty($atts['form_id'])) {
                    $user_form_id = $atts['form_id'];
                } else {
                    $user_form_id = get_user_meta($user_id, 'arm_form_id', true);
                }
                $form = new ARM_Form('id', $user_form_id);
                if (!$form->exists() || ( $form->type != 'registration' && 'edit_profile' != $form->type ) ) {
                    $form = new ARM_Form('id', $default_form_id);
                }
                $form = apply_filters('arm_form_data_before_edit_profile_shortcode', $form, $atts);
                do_action('arm_before_render_edit_profile_form', $form, $atts);
                do_action('arm_before_render_form', $form, $atts);
                if ($form->exists() && !empty($form->fields)) {
                    $form_id = $form->ID;
                    $form_settings = $form->settings;
                    $ref_template = $form->form_detail['arm_ref_template'];
                    $form_style = $form_settings['style'];
                    $form_color_scheme = !empty($form_style['color_scheme']) ? $form_style['color_scheme'] : 'default';
                    /* Form Classes */
                    $form_style['button_position'] = (!empty($form_style['button_position'])) ? $form_style['button_position'] : 'left';
                    $formRandomID = $form_id . '_' . arm_generate_random_code();
                    $form_style_class = ' arm_form_' . $form_id;
                    $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];
                    $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                    $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                    $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                    $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                    $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                    if (is_rtl()) {
                        $form_style_class .= ' arm_rtl_site';
                    }
                    $form_style_class .= ' ' . $atts['class'];
                    $form_attr = ' name="arm_form" id="arm_form' . $formRandomID . '"';
                    $form_attr .= ' data-ng-controller="ARMCtrl" data-ng-cloak="" data-ng-id="' . $form_id . '" data-ng-submit="armFormSubmit(arm_form.$valid, \'arm_form' . $formRandomID . '\', $event);" onsubmit="return false;"';
                    if ($form->type != 'change_password') {
                        $form_attr .= ' data-random-id="' . $formRandomID . '" ';
                    }
                    /* Add Form Style on front page. */
                    if (!empty($form_style['form_layout']) && $form_style['form_layout'] != '') {
                        $form_style_class .= ' arm_form_style_' . $form_color_scheme;
                    }
                    if(!empty($form_style['validation_type']) && $form_style['validation_type'] == 'standard') {
                        $form_style_class .= " arm_standard_validation_type ";
                        $validation_pos = "bottom";
                    }
                    $form_css = $this->arm_ajax_generate_form_styles($form_id, $form_settings, $atts, $ref_template);
                    /* Form Inner Content */
                    $field_position = !empty($form_style['field_position']) ? $form_style['field_position'] : 'left';
                    $validation_type = !empty($form_style['validation_type']) ? $form_style['validation_type'] : 'modern';
                    $validation_pos = !empty($form_style['validation_position']) ? $form_style['validation_position'] : 'bottom';
                    $content = apply_filters('arm_change_content_before_display_form', $content, 0, $atts);
                    $content .= $form_css['arm_link'];
                    $content .= '<style type="text/css" id="arm_form_style_' . $form_id . '">' . $form_css['arm_css'] . '</style>';
                    $content .= '<div class="arm_member_form_container">';
                    $content .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . $form_id . '"></div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<form method="post" class="arm_form arm_shortcode_form arm_form_edit_profile ' . $form_style_class . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
                    $content .= '<div class="arm_form_inner_container arm_msg_pos_' . $validation_pos . '">';
                    /* 20aug2016 */

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

                    global $arm_is_enable_crop;
                    if($enable_crop && empty($arm_is_enable_crop)){
                        $arm_is_enable_crop = 1;
                        $content .='<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="'.$formRandomID.'">';
                        $content .='<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_div" class="arm_crop_div" data_id="'.$formRandomID.'"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="'.$formRandomID.'"/></div>';
                        $content .='<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
                        $content .='<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="zoom" data-option="0.1" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="zoom" data-option="-0.1" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="'.$formRandomID.'">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and <br/>use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';


                        $content .='<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="'.$formRandomID.'">';
                        $content .='<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="'.$formRandomID.'"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="'.$formRandomID.'" /></div>';
                        $content .='<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr arm_inht_front_usr_profile_cvr">';
                        $content .='<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="'.$formRandomID.'" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="'.$formRandomID.'" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button data_id="'.$formRandomID.'" id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';
                    }

                    $content .= '<div class="arm_form_wrapper_container arm_form_wrapper_container_edit_profile arm_field_position_' . $field_position . '" data-form_id="edit_profile">';
                    if ($atts['view_profile']) {
                        $profile_link = $arm_global_settings->arm_get_user_profile_url($user_id, '1');
                        $content .= '<div class="arm_view_profile_link_container">';
                        $content .= '<a href="' . $profile_link . '" class="arm_view_profile_link">' . $atts['view_profile_link'] . '</a>';
                        $content .= '</div>';
                    }
                    if (!empty($atts['title'])) {
                        $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                        $content .= '<div class="arm_form_heading_container arm_add_other_style armalign' . $form_title_position . '">';
                        $content .= '<span class="arm_form_field_label_wrapper_text">' . $atts['title'] . '</span>';
                        $content .= '</div>';
                    }
                    $content .= $this->arm_member_form_get_single_form_fields($form, $atts, $formRandomID);
                    $content .= '<div class="armclear"></div>';
                    if (isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
                        if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                            foreach ($form_settings['hidden_fields'] as $hiddenF) {
                                $hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title($hiddenF['title']);
                                $hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
                                $hiddenValue = (!empty($hiddenValue)) ? $hiddenValue : $hiddenF['value'];
                                $content .= '<input type="hidden" name="' . $hiddenMetaKey . '" value="' . $hiddenValue . '"/>';
                            }
                        }
                    }
                    $content .= '</div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<input type="hidden" name="arm_action" value="edit_profile"/>';
                    $content .= '<input type="hidden" name="isAdmin" value="' . ((is_admin()) ? '1' : '0') . '"/>';
                    $content .= '<input type="hidden" name="arm_parent_form_id" value="' . $form_id . '"/>';
                    $content .= '<input type="hidden" name="arm_success_message" value="' . $atts['message'] . '"/>';

                    $content .= '<input type="hidden" name="id" value="' . $user_id . '"/>';
                    $content .= do_shortcode('[armember_spam_filters]');
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '<div class="armclear"></div>';

                    global $arm_members_activity, $arm_version;
                    $arm_request_version = get_bloginfo('version');
                    $setact = 0;
                    global $check_version;
                    $setact = $arm_members_activity->$check_version();

                    if ($setact != 1) {
                        $content .= "<div><span style='color:#FF0000; margin-top:10px; font-size:12px !important; text-align:center; display:block !important;'>Powered by <a href='https://www.armemberplugin.com/redirect.php?rdt=t2&arm_version=$arm_version&arm_request_version=$arm_request_version' target='_blank'>ARMember</a></span></div>";
                        $content .= "<div><span style='color:#FF0000; font-size:12px !important; text-align:center; display:block !important;'>&nbsp;&nbsp;(Unlicensed)</span></div>";
                    }

                    $content .= '</div>';
                    $content = apply_filters('arm_change_content_after_display_form', $content, $form, $atts, $formRandomID);
                }
            } else {
                $default_login_form_id = $this->arm_get_default_form_id('login');

                $arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

                $page_settings = $arm_all_global_settings['page_settings'];
                $general_settings = $arm_all_global_settings['general_settings'];

                $login_page_id = (isset($page_settings['login_page_id']) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
                if ($login_page_id == 0) {

                    if ($general_settings['hide_wp_login'] == 1) {
                        $login_page_url = ARM_HOME_URL;
                    } else {
                        $referral_url = wp_get_current_page_url();
                        $referral_url = (!empty($referral_url) && $referral_url != '') ? $referral_url : wp_get_current_page_url();
                        $login_page_url = wp_login_url($referral_url);
                    }
                } else {
                    $login_page_url = get_permalink($login_page_id) . '?arm_redirect=' . urlencode(wp_get_current_page_url());
                }
                if (is_home()) {
                    return '';
                } else {
                    if (preg_match_all('/arm_redirect/', $login_page_url, $matche) < 2) {
                        wp_redirect($login_page_url);
                    }
                }
            }
            $ARMember->enqueue_angular_script();
            $ARMember->arm_check_font_awesome_icons($content);
			
			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
			if($isEnqueueAll == '1'){
				$content .= '<script type="text/javascript" data-cfasync="false">
									jQuery(document).ready(function (){
										arm_do_bootstrap_angular();
									});';
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

            return $content.$hiddenvalue;
        }

        function arm_profile_detail_shortcode_func( $atts, $content, $tag ){
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page){
                return;
            }

            $atts = shortcode_atts(
                array(
                    'id' => '',
                    'form_id' => '',
                ),
                $atts,
                $tag
            );

            if( '' == $atts['id'] && '' == $atts['form_id'] ){
                return;
            }

            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;

            $content = '';
            $formRandomID = '';

            if( is_user_logged_in() ){
                $default_form_id = $atts['id'];
                $user_id = get_current_user_id();
                if (!empty($atts['id'])) {
                    $user_form_id = $atts['id'];
                }
                else if (!empty($atts['form_id'])) {
                    $user_form_id = $atts['form_id'];
                    
                } 

                $form = new ARM_Form('id', $user_form_id);
                
                if (!$form->exists() || $form->type != 'edit_profile') {
                    return;
                }

                $success_message = isset( $form->settings['edit_success_message'] ) ? $form->settings['edit_success_message'] : esc_html__('Your profile updated successfully', 'ARMember');
                
                $form = apply_filters('arm_form_data_before_edit_profile_shortcode', $form, $atts);
                do_action('arm_before_render_edit_profile_form', $form, $atts);
                do_action('arm_before_render_form', $form, $atts);
                if ($form->exists() && !empty($form->fields)) {
                    $form_id = $form->ID;
                    $form_settings = $form->settings;
                    $ref_template = $form->form_detail['arm_ref_template'];
                    $form_style = $form_settings['style'];
                    $form_color_scheme = !empty($form_style['color_scheme']) ? $form_style['color_scheme'] : 'default';
                    /* Form Classes */
                    $form_style['button_position'] = (!empty($form_style['button_position'])) ? $form_style['button_position'] : 'left';
                    $formRandomID = $form_id . '_' . arm_generate_random_code();
                    $form_style_class = ' arm_form_' . $form_id;
                    $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];
                    $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                    $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                    $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                    $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                    $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                    if (is_rtl()) {
                        $form_style_class .= ' arm_rtl_site';
                    }
                    //$form_style_class .= ' ' . $atts['class'];
                    $form_attr = ' name="arm_form" id="arm_form' . $formRandomID . '"';
                    $form_attr .= ' data-ng-controller="ARMCtrl" data-ng-cloak="" data-ng-id="' . $form_id . '" data-ng-submit="armFormSubmit(arm_form.$valid, \'arm_form' . $formRandomID . '\', $event);" onsubmit="return false;"';
                    if ($form->type != 'change_password') {
                        $form_attr .= ' data-random-id="' . $formRandomID . '" ';
                    }
                    /* Add Form Style on front page. */
                    if (!empty($form_style['form_layout']) && $form_style['form_layout'] != '') {
                        $form_style_class .= ' arm_form_style_' . $form_color_scheme;
                    }
                    if(!empty($form_style['validation_type']) && $form_style['validation_type'] == 'standard') {
                        $form_style_class .= " arm_standard_validation_type ";
                        $validation_pos = "bottom";
                    }
                    $form_css = $this->arm_ajax_generate_form_styles($form_id, $form_settings, $atts, $ref_template);
                    /* Form Inner Content */
                    $field_position = !empty($form_style['field_position']) ? $form_style['field_position'] : 'left';
                    $validation_type = !empty($form_style['validation_type']) ? $form_style['validation_type'] : 'modern';
                    $validation_pos = !empty($form_style['validation_position']) ? $form_style['validation_position'] : 'bottom';
                    $content = apply_filters('arm_change_content_before_display_form', $content, 0, $atts);
                    $content .= $form_css['arm_link'];
                    $content .= '<style type="text/css" id="arm_form_style_' . $form_id . '">' . $form_css['arm_css'] . '</style>';
                    $content .= '<div class="arm_member_form_container">';
                    $content .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . $form_id . '"></div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<form method="post" class="arm_form arm_shortcode_form arm_form_edit_profile ' . $form_style_class . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
                    $content .= '<div class="arm_form_inner_container arm_msg_pos_' . $validation_pos . '">';
                    /* 20aug2016 */

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

                    global $arm_is_enable_crop;
                    if($enable_crop && empty($arm_is_enable_crop)){
                        $arm_is_enable_crop = 1;
                        $content .='<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="'.$formRandomID.'">';
                        $content .='<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_div" class="arm_crop_div" data_id="'.$formRandomID.'"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="'.$formRandomID.'"/></div>';
                        $content .='<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
                        $content .='<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="zoom" data-option="0.1" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="zoom" data-option="-0.1" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="'.$formRandomID.'">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and <br/>use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';


                        $content .='<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="'.$formRandomID.'">';
                        $content .='<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="'.$formRandomID.'"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="'.$formRandomID.'" /></div>';
                        $content .='<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr arm_inht_front_usr_profile_cvr">';
                        $content .='<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="'.$formRandomID.'" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="'.$formRandomID.'" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" data_id="'.$formRandomID.'" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button data_id="'.$formRandomID.'" id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';
                    }

                    $content .= '<div class="arm_form_wrapper_container arm_form_wrapper_container_edit_profile arm_field_position_' . $field_position . '" data-form_id="edit_profile">';

                    if ( isset( $form_settings['view_profile_link'] ) && '1' == $form_settings['view_profile_link']) {
                        $profile_link = $arm_global_settings->arm_get_user_profile_url($user_id, '1');
                        $content .= '<div class="arm_view_profile_link_container">';
                        $content .= '<a href="' . $profile_link . '" class="arm_view_profile_link">' . $form_settings['arm_view_profile_link_label'] . '</a>';
                        $content .= '</div>';
                    }

                    if (!empty($form->name) && empty($form->settings['hide_title'])) {
                        $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                        $content .= '<div class="arm_form_heading_container arm_add_other_style armalign' . $form_title_position . '">';
                        $content .= '<span class="arm_form_field_label_wrapper_text">' . $form->name . '</span>';
                        $content .= '</div>';
                    }
                    $content .= $this->arm_member_form_get_single_form_fields($form, $atts, $formRandomID);
                    $content .= '<div class="armclear"></div>';
                    if (isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
                        if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                            foreach ($form_settings['hidden_fields'] as $hiddenF) {
                                $hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title($hiddenF['title']);
                                $hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
                                $hiddenValue = (!empty($hiddenValue)) ? $hiddenValue : $hiddenF['value'];
                                $content .= '<input type="hidden" name="' . $hiddenMetaKey . '" value="' . $hiddenValue . '"/>';
                            }
                        }
                    }
                    $content .= '</div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<input type="hidden" name="arm_action" value="edit_profile"/>';
                    $content .= '<input type="hidden" name="isAdmin" value="' . ((is_admin()) ? '1' : '0') . '"/>';
                    $content .= '<input type="hidden" name="arm_parent_form_id" value="' . $form_id . '"/>';
                    $content .= '<input type="hidden" name="arm_success_message" value="' . $success_message . '"/>';

                    $content .= '<input type="hidden" name="id" value="' . $user_id . '"/>';
                    $content .= do_shortcode('[armember_spam_filters]');
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '<div class="armclear"></div>';

                    global $arm_members_activity, $arm_version;
                    $arm_request_version = get_bloginfo('version');
                    $setact = 0;
                    global $check_version;
                    $setact = $arm_members_activity->$check_version();

                    if ($setact != 1) {
                        $content .= "<div><span style='color:#FF0000; margin-top:10px; font-size:12px !important; text-align:center; display:block !important;'>Powered by <a href='https://www.armemberplugin.com/redirect.php?rdt=t2&arm_version=$arm_version&arm_request_version=$arm_request_version' target='_blank'>ARMember</a></span></div>";
                        $content .= "<div><span style='color:#FF0000; font-size:12px !important; text-align:center; display:block !important;'>&nbsp;&nbsp;(Unlicensed)</span></div>";
                    }

                    $content .= '</div>';
                    $content = apply_filters('arm_change_content_after_display_form', $content, $form, $atts, $formRandomID);
                }

            } else {
                $default_login_form_id = $this->arm_get_default_form_id('login');

                $arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

                $page_settings = $arm_all_global_settings['page_settings'];
                $general_settings = $arm_all_global_settings['general_settings'];

                $login_page_id = (isset($page_settings['login_page_id']) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
                if ($login_page_id == 0) {

                    if ($general_settings['hide_wp_login'] == 1) {
                        $login_page_url = ARM_HOME_URL;
                    } else {
                        $referral_url = wp_get_current_page_url();
                        $referral_url = (!empty($referral_url) && $referral_url != '') ? $referral_url : wp_get_current_page_url();
                        $login_page_url = wp_login_url($referral_url);
                    }
                } else {
                    $login_page_url = get_permalink($login_page_id) . '?arm_redirect=' . urlencode(wp_get_current_page_url());
                }
                if (is_home()) {
                    return '';
                } else {
                    if (preg_match_all('/arm_redirect/', $login_page_url, $matche) < 2) {
                        wp_redirect($login_page_url);
                    }
                }
            }
            $ARMember->enqueue_angular_script();
            $ARMember->arm_check_font_awesome_icons($content);

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

            return $content.$hiddenvalue;
        }

        function arm_verify_user_activation_for_front($user_email, $key) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_global_settings;
            $arm_message = array();
            if (!isset($user_email) || empty($user_email)) {
                $err_msg = isset($arm_global_settings->common_message['arm_user_not_exist']) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __('User does not exist.', 'ARMember');
                $arm_message = array('status' => 'error', 'message' => $err_msg);
            }


            //Get user data.
            $user_data = get_user_by('email', $user_email);
            $activation_key = '';
            if (isset($user_data) && !empty($user_data)) {
                $activation_key = get_user_meta($user_data->ID, 'arm_user_activation_key', true);
            }

             


            if (!empty($user_data) && (empty($activation_key) || $activation_key == '')) {

            	
                $err_msg = isset($arm_global_settings->common_message['arm_already_active_account']) ? $arm_global_settings->common_message['arm_already_active_account'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __('Your account has been activated.', 'ARMember');
                $arm_message = array('status' => 'success', 'message' => $err_msg);
            } else if ($activation_key == $key) {
            	
                /* Update Activation Status */
                arm_set_member_status($user_data->ID, 1);

                $total_user_plans = get_user_meta($user_data->ID, 'arm_user_plan_ids', true);
                $total_user_plans = (isset($total_user_plans) && !empty($total_user_plans)) ? $total_user_plans : array();

                if (!empty($total_user_plans)) {
                    $total_user_suspended_plans = get_user_meta($user_data->ID, 'arm_user_suspended_plan_ids', true);
                    $total_user_suspended_plans = (isset($total_user_suspended_plans) && !empty($total_user_suspended_plans)) ? $total_user_suspended_plans : array();
                    foreach ($total_user_plans as $tp) {
                        if (in_array($tp, $total_user_suspended_plans)) {
                            unset($total_user_suspended_plans[array_search($tp, $total_user_suspended_plans)]);
                        }
                    }
                    update_user_meta($user_data->ID, 'arm_user_suspended_plan_ids', $total_user_suspended_plans);
                }

                /* Send New User Notification Mail */
                armMemberSignUpCompleteMail($user_data);
                /* Send Account Verify Notification Mail */
                armMemberAccountVerifyMail($user_data);
                /* Activation Success Message */
                 $err_msg = (!empty($arm_global_settings->common_message['arm_already_active_account'])) ? $arm_global_settings->common_message['arm_already_active_account'] : __('Your account has been activated, please login to view your profile.', 'ARMember'); 
                $arm_message = array('status' => 'success', 'message' => $err_msg);
            } else {


                $err_msg = (!empty($arm_global_settings->common_message['arm_expire_activation_link'])) ? $arm_global_settings->common_message['arm_expire_activation_link'] : __('Activation link is expired or invalid.', 'ARMember');
                $arm_message = array('status' => 'error', 'message' => $err_msg);
            }

            return $arm_message;
        }

        function arm_verify_reset_password_link($user_email, $key) {
            global $arm_global_settings;
            $arm_message = array();

            if (!isset($user_email) || empty($user_email)) {
                $err_msg = isset($arm_global_settings->common_message['arm_user_not_exist']) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __('User does not exist.', 'ARMember');
                $arm_message = array('status' => 'error', 'message' => $err_msg);
            }

            $user = check_password_reset_key($key, $user_email);


            if (!$user || is_wp_error($user)) {

                if ($user && $user->get_error_code() === 'expired_key') {
                    $err_msg = (!empty($arm_global_settings->common_message['arm_password_reset_pwd_link_expired'])) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : __('Reset Password Link is expired.', 'ARMember');
                    $arm_message = array('status' => 'error', 'message' => $err_msg);
                } else {
                    $err_msg = (!empty($arm_global_settings->common_message['arm_password_reset_pwd_link_expired'])) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : __('Reset Password Link is invalid.', 'ARMember');
                    $arm_message = array('status' => 'error', 'message' => $err_msg);
                }
            } else {
                $err_msg = (!empty($arm_global_settings->common_message['arm_password_enter_new_pwd'])) ? $arm_global_settings->common_message['arm_password_enter_new_pwd'] : __('Please enter new password.', 'ARMember');
                $arm_message = array('status' => 'success', 'message' => $err_msg);
            }

            return $arm_message;
        }

        /**
         * `[arm_form]` shortcode function
         */
        function arm_form_shortcode_func($atts, $content, $tag) {
            global $bpopup_loaded, $arm_members_class, $arm_global_settings, $ARMSPAMFILEURL, $arm_inner_form_modal, $arm_subscription_plans, $ARMember, $arm_pay_per_post_feature;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            $ARMember->arm_session_start();
            /* ====================/.Begin Set Shortcode Attributes./==================== */

            $short_atts = array(
                'id' => 0,
                'class' => '',
                'popup' => false, /* Form will be open in popup box when options is true */
                'link_type' => 'link',
                'link_class' => '', /* Possible Options:- `link`, `button` */
                'link_title' => __('Click here to open form', 'ARMember'), /* Default to form name */
                'popup_height' => '',
                'popup_width' => '',
                'overlay' => '0.6',
                'modal_bgcolor' => '#000000',
                'redirect_to' => '',
                'setup' => false,
                'widget' => false,
                'link_css' => '',
                'link_hover_css' => '',
                'is_referer' => '0',
                'preview' => false,
                'nav_menu' => 0,
                'form_position' => 'center',
                'assign_default_plan' => 0,
                'logged_in_message' => '',
                'setup_form_id' => '',
            );

            $short_atts = apply_filters('arm_add_register_dynamic_atts', $short_atts, $tag);

            $atts = shortcode_atts($short_atts, $atts, $tag);

            $atts['popup'] = ($atts['popup'] === 'true' || $atts['popup'] == '1') ? true : false;
            $atts['setup'] = ($atts['setup'] === 'true' || $atts['setup'] == '1') ? true : false;

            if ($atts['popup'] && !$atts['setup']) {
                $atts['form_position'] = 'center';
                $bpopup_loaded = 1;
            }
            $atts['widget'] = ($atts['widget'] === 'true' || $atts['widget'] == '1') ? true : false;
            $isPreview = ($atts['preview'] === 'true' || $atts['preview'] == '1') ? true : false;
            $is_nav_menu = ($atts['nav_menu'] === '1' || $atts['nav_menu'] == 1 ) ? 1 : 0;
            /* For Social Form Check */
            $social_form = (isset($_GET['social_form']) && !empty($_GET['social_form'])) ? intval($_GET['social_form']) : 0;
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $ARMember, $arm_slugs, $arm_global_settings, $arm_social_feature;
            if (empty($atts['id']) || $atts['id'] == 0 || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpseo_filter_shortcodes')) {
                return '';
            } else {
                if (is_admin()) {
                    $_REQUEST['page'] = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
                    $current_url = admin_url('admin.php?page=' . $_REQUEST['page']);
                    $redirect_to = admin_url('admin.php?page=' . $arm_slugs->manage_members);
                } else {
                    $redirect_to = !empty($atts['redirect_to']) ? $atts['redirect_to'] : ARM_HOME_URL;
                }
                $form = new ARM_Form('id', $atts['id']);
                $form_slug = $form->slug;
                if ($form->type == 'registration' && $isPreview) {
                    
                } else {
                    if (is_user_logged_in()) {
                        /* Check for login form shortcodes */

                        if ($atts['popup'] === false) {
                            if (in_array($form->type, array('login', 'signin', 'logout', 'log-out', 'signout', 'sign-out'))) {

                                if (!isset($_GET['arm-key']) && empty($_GET['arm-key'])) {
                                    $already_logged_in_message = (isset($atts['logged_in_message']) && !empty($atts['logged_in_message'])) ? $atts['logged_in_message'] : '';
                                    $already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
                                    return $already_logged_in_message_div;
                                } else {
                                    return '';
                                }
                            }
                            if (!is_admin() && in_array($form->type, array('registration', 'forgot_password', 'lostpassword', 'retrievepassword'))) {
                                $already_logged_in_message = (isset($atts['logged_in_message']) && !empty($atts['logged_in_message'])) ? $atts['logged_in_message'] : '';
                                $already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
                                return $already_logged_in_message_div;
                                if ($atts['widget'] == false) {
                                    wp_redirect($redirect_to);
                                    exit;
                                } else {
                                    $already_logged_in_message = (isset($atts['logged_in_message']) && !empty($atts['logged_in_message'])) ? $atts['logged_in_message'] : '';
                                    $already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
                                    return $already_logged_in_message_div;
                                }
                            }
                        }
                    } else {
                        if (!is_admin() && in_array($form->type, array('edit_profile', 'update_profile', 'change_password'))) {
                            if ($form->type == 'change_password' && isset($_GET['key']) && isset($_GET['action']) && $_GET['action'] == 'armrp' && isset($_GET['login']) && !empty($_GET['login'])) {

                                $chk_key = rawurldecode($_GET['key']);
                                $user_email = rawurldecode($_GET['login']);
                                $arm_message1 = array();


                                if(isset($_GET['varify_key']) && !empty($_GET['varify_key'])){

                                     $user_data_array = get_user_by('login', $user_email);


                                    $this->arm_verify_user_activation_for_front($user_data_array->user_email, rawurldecode($_GET['varify_key']));
                                }

                                
                                $arm_message1 = $this->arm_verify_reset_password_link($user_email, $chk_key);


                                if ($arm_message1['status'] == 'error') {
                                    $default_forgot_password_form_id = $this->arm_get_default_form_id('forgot_password');
                                    return do_shortcode("[arm_form id='$default_forgot_password_form_id']");
                                }
                            } else {
                                $default_login_form_id = $this->arm_get_default_form_id('login');
                                $arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

                                $page_settings = $arm_all_global_settings['page_settings'];
                                $general_settings = $arm_all_global_settings['general_settings'];

                                $login_page_id = (isset($page_settings['login_page_id']) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
                                $armCurPage_url = wp_get_current_page_url();
                                if ($login_page_id == 0) {
                                    if ($general_settings['hide_wp_login'] == 1) {
                                        $login_page_url = ARM_HOME_URL;
                                    } else {
                                        $armCurPage_url = wp_get_current_page_url();
                                        $login_page_url = wp_login_url($armCurPage_url);
                                    }
                                } else {
                                    $login_page_url = get_permalink($login_page_id) . '?arm_redirect=' . urlencode(wp_get_current_page_url());
                                }
                                if ($is_nav_menu == 1) {
                                    return do_shortcode("[arm_form id='$default_login_form_id' is_referer='1' nav_menu='1']");
                                } else {
                                    if ($atts['widget'] == false) {
                                        if (preg_match_all('/arm_redirect/', $login_page_url, $matche) < 2) {
                                            wp_redirect($login_page_url);
                                            return '';
                                        }
                                    } else {
                                        return '';
                                    }
                                }
                            }
                        }
                    }
                }
                $form_settings = array(
                    'style' => $this->arm_default_form_style(),
                    'custom_css' => ''
                );
                $form = apply_filters('arm_form_data_before_form_shortcode', $form, $atts);
                do_action('arm_before_render_form', $form, $atts);
                if ($form->exists() && !empty($form->fields)) {
                    $form_id = $form->ID;
                    $form_settings = $form->settings;
                    $ref_template = $form->form_detail['arm_ref_template'];
                    $atts['hide_title'] = (isset($form_settings['hide_title']) && $form_settings['hide_title'] == '1') ? true : false;
                    $form_style = $form_settings['style'];
                    $form_color_scheme = !empty($form_style['color_scheme']) ? $form_style['color_scheme'] : 'default';
                    if (isset($form_settings['redirect_type']) && $form_settings['redirect_type'] != 'message') {
                        if ($form_settings['redirect_type'] == 'page') {
                            $form_redirect_id = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : '0';
                            $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                            $arm_redirect_type = '';
                        } else if ($form_settings['redirect_type'] == 'referral') {
                            $redirect_to = wp_get_referer();
                            $default_redirect = (!empty($form_settings['referral_url'])) ? $form_settings['referral_url'] : wp_get_current_page_url();
                            $arm_redirect_type = '';
                        } else if ($form_settings['redirect_type'] == 'conditional_redirect') {


                            $arm_redirect_type = 'conditional_redirects';
                            $redirect_to = '';
                        } else {
                            $redirect_to = (!empty($form_settings['redirect_url'])) ? $form_settings['redirect_url'] : $redirect_to;
                            $arm_redirect_type = '';
                        }
                    }
                    /* Form Classes */
                    $form_style['button_position'] = (!empty($form_style['button_position'])) ? $form_style['button_position'] : 'left';
                    $form_style_class = ' arm_form_' . $form_id;
                    $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];
                    $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                    $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                    $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                    $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                    $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                    if (is_rtl()) {
                        $form_style_class .= ' arm_rtl_site';
                    }
                    $form_style_class .= ' ' . $atts['class'];
                    if(empty($atts['setup_form_id'])) {
                        $formRandomID = $form_id . '_' . arm_generate_random_code();
                    }
                    else {
                        $formRandomID = $atts['setup_form_id'];
                    }
                    $loginFormLinks = $modalForms = $socialBtns = $socialBtnSeparator = '';
                    $enable_social_login = (isset($form_settings['enable_social_login'])) ? $form_settings['enable_social_login'] : 0;
                    $social_btn_position = (isset($form_style['social_btn_position'])) ? $form_style['social_btn_position'] : 'bottom';
                    if ($form->type == 'login') {
                        $reg_link_label = (isset($form_settings['registration_link_label'])) ? stripslashes($form_settings['registration_link_label']) : __('Register', 'ARMember');
                        $fp_link_label = (isset($form_settings['forgot_password_link_label'])) ? stripslashes($form_settings['forgot_password_link_label']) : __('Forgot Password', 'ARMember');
                        $show_fp_link = (isset($form_settings['show_forgot_password_link'])) ? $form_settings['show_forgot_password_link'] : 0;
                        if ($show_fp_link == '1') {
                            if (isset($form_settings['forgot_password_link_type']) && $form_settings['forgot_password_link_type'] == 'page') {
                                $fpLinkPageID = (isset($form_settings['forgot_password_link_type_page'])) ? $form_settings['forgot_password_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('forgot_password_page_id', 0);
                                $fpLinkHref = $arm_global_settings->arm_get_permalink('', $fpLinkPageID);
                                $fp_link_label = $this->arm_parse_login_links($fp_link_label, $fpLinkHref);

                                $loginFormLinks .= '<div class="arm_form_field_container arm_form_field_container_forgot_link arm_forgot_password_above_link arm_forgotpassword_link">';
                                $loginFormLinks .= $fp_link_label;
                                $loginFormLinks .= '</div>';
                            } else {
                                $fp_id = $wpdb->get_var("SELECT `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='forgot_password' AND `arm_set_id`='" . $form->set_id . "'");
                                $fp_id = (!empty($fp_id) && $fp_id != 0) ? $fp_id : $this->arm_get_default_form_id('forgot_password');
                                $fpIdClass = 'arm_login_form_fp_link_' . $form_id . '_' . $fp_id . '_' . $formRandomID;
                                $modalForms .= do_shortcode("[arm_form id='$fp_id' popup='true' link_title=' ' link_class='arm_login_form_other_links $fpIdClass']");
                                $fp_link_label = $this->arm_parse_login_links($fp_link_label, 'javascript:void(0)', 'arm_login_popup_form_links arm_form_popup_ahref', 'data-form_id="' . $fpIdClass . '" data-toggle="armmodal" data-modal_bg="' . $atts['modal_bgcolor'] . '" data-overlay="' . $atts['overlay'] . '"');
                                $loginFormLinks .= '<div class="arm_form_field_container arm_form_field_container_forgot_link arm_forgot_password_below_link arm_forgotpassword_link">';
                                $loginFormLinks .= $fp_link_label;
                                $loginFormLinks .= '</div>';
                            }
                        }
                        $isSeparator = (isset($form_style['enable_social_btn_separator'])) ? $form_style['enable_social_btn_separator'] : 0;
                        if ($arm_social_feature->isSocialLoginFeature && $enable_social_login == '1') {
                            $form_network_options = (isset($form_settings['social_networks_settings'])) ? stripslashes_deep($form_settings['social_networks_settings']) : '';
                            if ($isSeparator == '1') {
                                $separatorText = (isset($form_style['social_btn_separator'])) ? $form_style['social_btn_separator'] : '';
                                $socialBtnSeparator = '<div class="arm_social_btn_separator_wrapper">' . $separatorText . '</div>';
                            }
                            $social_btn_type = (!empty($form_style['social_btn_type'])) ? $form_style['social_btn_type'] : 'horizontal';
                            $social_btn_align = (!empty($form_style['social_btn_align'])) ? $form_style['social_btn_align'] : 'left';
                            $socialBtns .= '<div class="arm_social_login_btns_wrapper arm_' . $social_btn_type . ' arm_align_' . $social_btn_align . '">';
                            if ((isset($form_settings['social_networks']) && !empty($form_settings['social_networks']))) {
                                $socialBtns .= do_shortcode("[arm_social_login network='{$form_settings['social_networks']}' form_network_options='{$form_network_options}']");
                            }
                            $socialBtns .= "</div>";
                        }
                        $loginFormLinks .= '<div class="arm_login_links_wrapper arm_login_options arm_socialicons_bottom">';
                        $loginFormLinks .= ($social_btn_position == 'bottom') ? $socialBtnSeparator : '';
                        $loginFormLinks .= ($social_btn_position == 'bottom') ? $socialBtns : '';
                        $show_reg_link = (isset($form_settings['show_registration_link'])) ? $form_settings['show_registration_link'] : 0;
                        if ($show_reg_link == '1') {
                            if (isset($form_settings['registration_link_type']) && $form_settings['registration_link_type'] == 'modal') {
                                $default_rf_id = $this->arm_get_default_form_id('registration');
			    	            $rf_id = (isset($form_settings['registration_link_type_modal'])) ? $form_settings['registration_link_type_modal'] : $default_rf_id;
                                $regIdClass = 'arm_login_form_reg_link_' . $formRandomID;
                                $rf_type = (isset($form_settings['registration_link_type_modal_form_type'])) ? $form_settings['registration_link_type_modal_form_type'] : 'arm_form';
                                if($rf_type == 'arm_setup') {
                                    $modalForms .= do_shortcode("[arm_setup id='$rf_id' popup='true'  link_title=' ' popup_width='800' link_type='link' link_class='".$regIdClass."']");
                                } else {
                                   $modalForms .= do_shortcode("[arm_form id='$rf_id' popup='true' link_title=' ' link_class='arm_login_form_other_links $regIdClass'] ");
                                }
                                $reg_link_label = $this->arm_parse_login_links($reg_link_label, 'javascript:void(0)', 'arm_login_popup_form_links arm_form_popup_ahref', 'data-form_id="' . $regIdClass . '" data-toggle="armmodal" data-modal_bg="' . $atts['modal_bgcolor'] . '" data-overlay="' . $atts['overlay'] . '"');
                                $loginFormLinks .= '<span class="arm_registration_link">' . $reg_link_label . '</span>';
                            } else {
                                $regLinkPageID = (isset($form_settings['registration_link_type_page'])) ? $form_settings['registration_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('register_page_id', 0);
                                $regLinkHref = $arm_global_settings->arm_get_permalink('', $regLinkPageID);
                                $reg_link_label = $this->arm_parse_login_links($reg_link_label, $regLinkHref);
                                $loginFormLinks .= '<span class="arm_registration_link">' . $reg_link_label . '</span>';
                            }
                        }
                        $loginFormLinks .= '<div class="armclear"></div>';
                        $loginFormLinks .= "</div>";
                        $loginFormLinks .= '<div class="armclear"></div>';
                    }

                    $form_attr = ' name="arm_form" id="arm_form' . $formRandomID . '"';
                    $form_attr .= ' data-ng-controller="ARMCtrl" data-ng-cloak="" data-ng-id="' . $form_id . '" data-ng-submit="armFormSubmit(arm_form.$valid, \'arm_form' . $formRandomID . '\', $event);" onsubmit="return false;"';
                    if (!empty($form_style['form_layout']) && $form_style['form_layout'] != '') {
                        $form_style_class .= ' arm_form_style_' . $form_color_scheme;
                    }
                    $form_css = $this->arm_ajax_generate_form_styles($form_id, $form_settings, $atts, $ref_template);
                    $form_content = $form_css['arm_link'];
                    $form_content .= '<style type="text/css" id="arm_form_style_' . $form_id . '">' . $form_css['arm_css'] . '</style>';
                    /* Form Inner Content */
                    $field_position = !empty($form_style['field_position']) ? $form_style['field_position'] : 'left';
                    $validation_type = !empty($form_style['validation_type']) ? $form_style['validation_type'] : 'modern';
                    $validation_pos = !empty($form_style['validation_position']) ? $form_style['validation_position'] : 'bottom';
                    if (isset($atts['popup']) && $atts['popup'] !== false) {
                        $validation_pos = 'bottom';
                        $form_style['form_width'] = (!empty($form_style['form_width'])) ? $form_style['form_width'] : '600';
                        if (isset($atts['popup_width']) && $atts['popup_width'] < $form_style['form_width']) {
                            $form_attr .= ' style="width: 100%;"';
                        }
                    }
                    if(!empty($form_style['validation_type']) && $form_style['validation_type'] == 'standard') {
                        $form_style_class .= " arm_standard_validation_type ";
                        $validation_pos = "bottom";
                    }

                    $form_content .= '<div class="arm_form_inner_container arm_msg_pos_' . $validation_pos . '">';
                    $form_content .= '<div class="arm_form_wrapper_container arm_form_wrapper_container_' . $form_id . ' arm_field_position_' . $field_position . ' arm_front_side_form"  data-form_id="' . $form_id . '">';
                    if ($form->type == 'login' && $social_btn_position == 'top') {
                        $form_content .= '<div class="arm_login_links_wrapper arm_socialicons_top">';
                        $form_content .= $socialBtns . $socialBtnSeparator;
                        $form_content .= '</div>';
                    }
                    if ($atts['hide_title'] == false && $atts['popup'] === false) {
                        $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                        $form_content .= '<div class="arm_form_heading_container arm_add_other_style armalign' . $form_title_position . '">';
                        $form_content .= '<span class="arm_form_field_label_wrapper_text">' . $form->name . '</span>';
                        $form_content .= '</div>';
                    }
                    if ($form->type == 'forgot_password') {
                        if (isset($form_settings['description'])) {
                            $form_content .= '<div class="arm_forgot_password_description">';
                            $form_content .= stripslashes($form_settings['description']);
                            $form_content .= '</div>';
                        }
                    }
                    $form_content .= $this->arm_member_form_get_single_form_fields($form, $atts, $formRandomID);
                    $form_content .= '<div class="armclear"></div>';
                    if (isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
                        if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                            foreach ($form_settings['hidden_fields'] as $hiddenF) {
                                $hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title($hiddenF['title']);
                                $form_content .= '<input type="hidden" name="' . $hiddenMetaKey . '" value="' . $hiddenF['value'] . '"/>';
                            }
                        }
                    }
                    $form_content .= '</div>';

                    if($arm_pay_per_post_feature->isPayPerPostFeature)
                    {
                        $arm_paid_post_success_url = (!empty($_POST['arm_paid_post_success_url'])) ? $_POST['arm_paid_post_success_url'] : '';
                        $arm_paid_post_success_url = (isset($_GET['arm_success_url'])) ? $_GET['arm_success_url'] : $arm_paid_post_success_url;
                        if(!empty($arm_paid_post_success_url))
                        {
                            $form_content .= '<input type="hidden" name="arm_paid_post_success_url" value="'.$arm_paid_post_success_url.'">';
                        }
                    }

                    $form_content .= '<input type="hidden" name="arm_action" value="' . $form_slug . '"/>';
                    $form_content .= '<input type="hidden" name="redirect_to" value="' . $redirect_to . '"/>';
                    $form_content .= '<input type="hidden" name="isAdmin" value="' . ((is_admin()) ? '1' : '0') . '"/>';
                    
                    $arm_default_redirection_settings = get_option('arm_redirection_settings');
                    $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                    $login_redirection_rules_options = $arm_default_redirection_settings['login'];
                    $signup_redirection_rules_options = $arm_default_redirection_settings['signup'];
                    
                    if ($atts['is_referer'] == '1' || 
                        (isset($login_redirection_rules_options['type'] ) && $login_redirection_rules_options['type'] == 'referral') || 
                        (isset($signup_redirection_rules_options['type'] ) && $signup_redirection_rules_options['type'] == 'referral')) {

                        if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] != '') {
                            
                            $referral_url1 = urldecode(sanitize_text_field($_REQUEST['redirect']));
                        }
                        else if(isset($_REQUEST['arm_redirect']) && $_REQUEST['arm_redirect'] != ''){
                            $referral_url1 = urldecode(sanitize_text_field($_REQUEST['arm_redirect']));
                        }else {
                           
                            if ($atts['popup'] !== false) {
                                global $arm_restriction;
                                $referral_url1 = $arm_restriction->curPageURL();
                            } else {
                                $referral_url1 = wp_get_referer();
                            }
                        }
                        if (isset($_SESSION['arm_restricted_page_url']) && !empty($_SESSION['arm_restricted_page_url'])) {
                            /* if referrel page is restricted, then below is used */
                            global $arm_restriction;
                            $check_curr_page_url = $arm_restriction->curPageURL();
                            $arm_url_components = parse_url($check_curr_page_url);

                            //$arm_url_components = parse_url($referral_url1);
                            if(isset($arm_url_components['query'])) 
                            {
                                parse_str($arm_url_components['query'], $arm_chk_res_params);
                                if(isset($arm_chk_res_params['restricted']))
                                {
                                    $referral_url1 = $_SESSION['arm_restricted_page_url'];
                                }
                            }      
                        } 
                    }
                    
                    $default_redirect = (!empty($login_redirection_rules_options['refferel'])) ? $login_redirection_rules_options['refferel'] : wp_get_current_page_url();
                    if(preg_match('/signup|registration/', $form_slug) > 0)
                    {
                        $default_redirect = (!empty($signup_redirection_rules_options['refferel'])) ? $signup_redirection_rules_options['refferel'] : wp_get_current_page_url();   
                    }
                        
                    $referral_url = !empty($referral_url1) ? $referral_url1 : $default_redirect;
                    $form_content .= '<input type="hidden" name="referral_url" value="' . $referral_url . '"/>';
                    if (is_admin() && isset($_REQUEST['id'])) {
                        $form_content .= '<input type="hidden" name="id" value="' . intval($_REQUEST['id']) . '"/>';
                    }
                    if ($form->type == 'registration') {

                        /* For User Avatar Cropper */
                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                        $general_settings = $all_global_settings['general_settings'];
                        $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

                        global $arm_is_enable_crop;
                        if($enable_crop && empty($arm_is_enable_crop)){
                            $arm_is_enable_crop = 1;
                            $form_content .='<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="'.$formRandomID.'">';
                            $form_content .='<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                            $form_content .='<div id="arm_crop_div" class="arm_crop_div" data_id="'.$formRandomID.'"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="'.$formRandomID.'"/></div>';
                            $form_content .='<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
                            $form_content .='<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                            $form_content .='<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                            $form_content .='<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="'.$formRandomID.'" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                            $form_content .='<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="'.$formRandomID.'" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                            $form_content .='<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                            $form_content .='<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                            $form_content .='<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="'.$formRandomID.'">' . __('Done', 'ARMember') . '</button>';
                            $form_content .='</div>';
                            $form_content .='<p class="arm_discription">' . __('(Use Cropper to set image and <br/>use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                            $form_content .='</div>';
                        }
                        /* For User Avatar Cropper */
                        $form_content .= '<input type="hidden" name="arm_form_id" value="' . $form_id . '"/>';
                        if (isset($atts['assign_default_plan']) && $arm_subscription_plans->isFreePlanExist($atts['assign_default_plan'])) {
                            $form_content .= '<input type="hidden" name="subscription_plan" id="arm_assign_default_plan" value="' . $atts['assign_default_plan'] . '"/>';
                        }

                        if (!$atts['setup']) {
                            /*Add login link in signup form*/
                            $login_link_label = (isset($form_settings['login_link_label'])) ? stripslashes($form_settings['login_link_label']) : __('Login', 'ARMember');

                            $form_content .= '<div class="arm_reg_links_wrapper arm_reg_options arm_reg_login_links">';
                            $show_login_link = (isset($form_settings['show_login_link'])) ? $form_settings['show_login_link'] : 0;
                            
                            if($show_login_link == '1' && !is_user_logged_in()) {
                                global $arm_login_form_popup_ids_arr;
                                /**/

                                if (isset($form_settings['login_link_type']) && $form_settings['login_link_type'] == 'modal') {
                                    $default_lf_id = $this->arm_get_default_form_id('login');
                                    $lf_id = (isset($form_settings['login_link_type_modal'])) ? $form_settings['login_link_type_modal'] : $default_lf_id;

                                    if(array_key_exists($lf_id, $arm_login_form_popup_ids_arr))
                                    {
                                        $formRandomID = $arm_login_form_popup_ids_arr[$lf_id];
                                    }

                                    $loginIdClass = 'arm_reg_form_login_link_' . $formRandomID;
                                    $form_content .= '<input type="hidden" name="arm_signup_login_form" value="'. $loginIdClass .'">';

                                    if(!array_key_exists($lf_id, $arm_login_form_popup_ids_arr))
                                    {
                                        $arm_login_form_popup_ids_arr[$lf_id] = $formRandomID;
                                        $modalForms .= do_shortcode("[arm_form id='".$lf_id."' popup='true' link_title=' ' link_class='arm_reg_form_other_links ".$loginIdClass."']");
                                    }
                                    else
                                    {
                                        $modalForms .= "[arm_form id='".$lf_id."' popup='true' link_title=' ' link_class='arm_reg_form_other_links ".$loginIdClass."']";
                                    }


                                    $login_link_label = $this->arm_parse_login_links($login_link_label, 'javascript:void(0)', 'arm_reg_popup_form_links arm_form_popup_ahref', 'data-form_id="' . $loginIdClass . '" data-toggle="armmodal" data-modal_bg="' . $atts['modal_bgcolor'] . '" data-overlay="' . $atts['overlay'] . '"');
                                    $form_content .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
                                } else {
                                    $loginLinkPageID = (isset($form_settings['login_link_type_page'])) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('login_page_id', 0);
                                    $loginLinkHref = $arm_global_settings->arm_get_permalink('', $loginLinkPageID);
                                    $login_link_label = $this->arm_parse_login_links($login_link_label, $loginLinkHref);
                                    $form_content .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
                                }
                            }
                            $form_content .= '<div class="armclear"></div>';
                            $form_content .= "</div>";
                            $form_content .= '<div class="armclear"></div>';
                        }



                        /*                         * ----------- Add Social Ids If User comes from social login -----------* */
                        foreach (array('facebook', 'twitter', 'linkedin', 'vk', 'insta','tumblr') as $social_type) {
                            $social_id = (isset($_REQUEST['arm_' . $social_type . '_id'])) ? $_REQUEST['arm_' . $social_type . '_id'] : '';
                            $social_picture = (isset($_REQUEST[$social_type . '_picture'])) ? $_REQUEST[$social_type . '_picture'] : '';
                            if (!empty($social_id)) {
                                $form_content .= '<input type="hidden" name="arm_' . $social_type . '_id" value="' . $social_id . '"/>';
                            }
                            if($social_type == "insta") {
                                if(isset($_REQUEST["display_name"]) && !empty($_REQUEST["display_name"])) {
                                    $form_content .= '<input type="hidden" name="display_name" value="' . $_REQUEST["display_name"] . '"/>';
                                }
                                else if(isset($_REQUEST["full_name"]) && !empty($_REQUEST["full_name"])) {
                                    $form_content .= '<input type="hidden" name="display_name" value="' . $_REQUEST["full_name"] . '"/>';
                                }
                            }
                            $arm_is_form_have_avatar_field = false;
                            foreach ($form->fields as $arm_field) {
                                if( (isset($arm_field['arm_form_field_slug']) && $arm_field['arm_form_field_slug'] == 'avatar') || (isset($arm_field['arm_form_field_option']['meta_key']) && $arm_field['arm_form_field_option']['meta_key'] == 'avatar') ){
                                    $arm_is_form_have_avatar_field = true;
                                }
                            }
                            if (!empty($social_picture) && $arm_is_form_have_avatar_field == false) {
                                $form_content .= '<input type="hidden" name="' . $social_type . '_picture" value="' . $social_picture . '"/>';
                                $form_content .= '<input type="hidden" name="avatar" value="' . $social_picture . '"/>';
                            }
                        }
                        /*                         * ----------------------------------------------------------------------* */
                    }
                    $form_content .= '<div class="armclear"></div>';
                    $form_content .= do_shortcode('[armember_spam_filters]');
                    $form_content .= $loginFormLinks;
                    $form_content .= '</div>';
                    /* Prepare Form HTML */
                    $content = apply_filters('arm_change_content_before_display_form', $content, $form, $atts);
                    if ($atts['setup']) {
                        $content .= '<div class="arm_form arm_shortcode_form ' . $form_style_class . '">';
                        $content .= $form_content;
                        $content .= '</div>';
                        $content .= '<div class="armclear"></div>';
                    } else {
                        $content .= '<div class="arm_member_form_container">';
                        if ($atts['popup'] !== false) {
                            $content .= '<div class="arm_form_message_container"></div>';
                        } else {
                            if (isset($_GET['key']) && !empty($_GET['key']) && isset($_GET['action']) && $_GET['action'] == 'armrp' && isset($_GET['login']) && !empty($_GET['login']) && !is_user_logged_in()) {

                                if ($form->type == 'change_password') {
                                    $chk_key = rawurldecode($_GET['key']);
                                    $user_email = rawurldecode($_GET['login']);
                                    $arm_message1 = $this->arm_verify_reset_password_link($user_email, $chk_key);

                                    if ($arm_message1['status'] == 'error') {
                                        $content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm_error_msg"><ul><li>';
                                        $content .= $arm_message1['message'];
                                        $content .= '</li></ul></div></div>';
                                    } else {
                                        $content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm_success_msg1"><ul><li>';
                                        $content .= $arm_message1['message'];
                                        $content .= '</li></ul></div></div>';
                                    }
                                }

                                if ($form->type == 'forgot_password') {
                                    $chk_key = rawurldecode($_GET['key']);
                                    $user_email = rawurldecode($_GET['login']);
                                    $arm_message = $this->arm_verify_reset_password_link($user_email, $chk_key);

                                    if ($arm_message['status'] == 'error') {
                                        $content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm_error_msg"><ul><li>';
                                        $content .= $arm_message['message'];
                                        $content .= '</li></ul></div></div>';
                                    }
                                }
                            }
                            $content .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . $form_id . '"></div>';
                        }
                        $content .= '<div class="armclear"></div>';
                        $captcha_code = arm_generate_captcha_code();
                        if (!isset($_SESSION['ARM_FILTER_INPUT'])) {
                            $_SESSION['ARM_FILTER_INPUT'] = array();
                        }
                        if (isset($_SESSION['ARM_FILTER_INPUT'][$formRandomID])) {
                            unset($_SESSION['ARM_FILTER_INPUT'][$formRandomID]);
                        }
                        $_SESSION['ARM_FILTER_INPUT'][$formRandomID] = $captcha_code;
                        $_SESSION['ARM_VALIDATE_SCRIPT'] = true;
                        if ($form->type != 'change_password') {
                            $form_attr .= ' data-random-id="' . $formRandomID . '" ';
                        }
                        if (in_array($form->type, array('registration', 'forgot_password'))) {
                            $form_attr .= ' data-submission-key="' . $captcha_code . '" ';
                        }
                        $content .= '<form method="post" class="arm_form arm_shortcode_form ' . $form_style_class . ' armAngularInit arm_cl_'.$formRandomID.'" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
                        if ($form->type != 'change_password' && $form->type != 'login') {
                            $content .= "<input type='text' name='arm_filter_input' arm_register='true' data-random-key='{$formRandomID}' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
                            $arm_random_key = rand();
                        }
                        $content .= $form_content;
                        $content .= '</form>';

                        $content .= '<div class="armclear">&nbsp;</div>';
                        $content .= $modalForms;
                        if ($atts['popup'] !== false) {
                            $content .= '</div>';
                            $popup_content = '<div class="arm_form_popup_container">';
                            $link_title = (!empty($atts['link_title'])) ? $atts['link_title'] : $form->name;
                            $link_style = $link_hover_style = '';
                            $popup_content .= '<style type="text/css">';
                            $pformRandomID = $form->ID . '_' . arm_generate_random_code();
                            if (!empty($atts['link_css'])) {
                                $link_style = esc_html($atts['link_css']);
                                $popup_content .= '.arm_form_popup_link_' . $pformRandomID . '{' . $link_style . '}';
                            }
                            if (!empty($atts['link_hover_css'])) {
                                $link_hover_style = esc_html($atts['link_hover_css']);
                                $popup_content .= '.arm_form_popup_link_' . $pformRandomID . ':hover{' . $link_hover_style . '}';
                            }
                            $popup_content .= '</style>';
                            $popupLinkID = 'arm_form_popup_link_' . $form->ID;

                            //Condition for check that if already same form_id popup exist then not creating duplicate popup of that.
                            global $arm_form_popup_ids_arr;
                            if(array_key_exists($form->ID, $arm_form_popup_ids_arr))
                            {
                                $pformRandomID = $arm_form_popup_ids_arr[$form->ID];
                            }

                            $popupLinkClass = 'arm_form_popup_link arm_form_popup_link_' . $form->ID . ' arm_form_popup_link_' . $pformRandomID;
                            if (!empty($atts['link_class'])) {
                                $popupLinkClass.=" " . esc_html($atts['link_class']);
                            }
                            $popupLinkAttr = 'data-form_id="' . $pformRandomID . '" data-toggle="armmodal"  data-modal_bg="' . $atts['modal_bgcolor'] . '" data-overlay="' . $atts['overlay'] . '"';
                            if( !empty($atts['link_type']) && strtolower($atts['link_type']) == 'onload'){
                                $popup_content .= '<button type="button" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_onload_popup arm_onload_popup_'.$popupLinkID.' arm_form_popup_button" ' . $popupLinkAttr . ' style="display:none;">' . $link_title . '</button>';
                                $popup_content .= "<script type='text/javascript' data-cfasync='false'>";
                                $popup_content .= "jQuery(document).ready(function(){jQuery(\".arm_onload_popup.arm_onload_popup_{$popupLinkID}\").trigger('click'); });";
                                $popup_content .= "</script>";
                            } else if (!empty($atts['link_type']) && strtolower($atts['link_type']) == 'button') {
                                $popup_content .= '<button type="button" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_form_popup_button" ' . $popupLinkAttr . '>' . $link_title . '</button>';
                            } else {
                                $popup_content .= '<a href="javascript:void(0)" id="' . $popupLinkID . '" class="' . $popupLinkClass . ' arm_form_popup_ahref" ' . $popupLinkAttr . '>' . $link_title . '</a>';
                            }
                            $popup_style = $popup_content_height = '';
                            $popupHeight = 'auto';
                            $popupWidth = '500';
                            if (!empty($atts['popup_height'])) {
                                if ($atts['popup_height'] == 'auto') {
                                    $popup_style .= 'height: auto;';
                                } else {
                                    $popup_style .= 'overflow: hidden;height: ' . $atts['popup_height'] . 'px;';
                                    $popupHeight = ($atts['popup_height'] - 70) . 'px';
                                    $popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ($atts['popup_height'] - 70) . 'px;';
                                }
                            }
                            if (!empty($atts['popup_width'])) {
                                if ($atts['popup_width'] == 'auto') {
                                    $popup_style .= '';
                                } else {
                                    $popupWidth = $atts['popup_width'];
                                    $popup_style .= 'width: ' . $atts['popup_width'] . 'px;';
                                }
                            }
                            $popup_modal_content = "";
                            $popup_modal_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_form arm_popup_member_form_' . $form->ID . ' arm_popup_member_form_' . $pformRandomID . '" style="' . $popup_style . '" data-width="' . $popupWidth . '"><div class="popup_wrapper_inner">';
                            $popup_modal_content .= '<div class="popup_header">';
                            $popup_modal_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
                            $popup_modal_content .= '<div class="popup_header_text arm_form_heading_container">';
                            if ($atts['hide_title'] == false) {
                                $popup_modal_content .= '<span class="arm_form_field_label_wrapper_text">' . $form->name . '</span>';
                            }
                            $popup_modal_content .= '</div>';
                            $popup_modal_content .= '</div>';
                            $popup_modal_content .= '<div class="popup_content_text" style="' . $popup_content_height . 'min-height: 100px;" data-height="' . $popupHeight . '">';
                            $popup_modal_content .= apply_filters('arm_change_popup_form_content', $content, $form, $atts, $pformRandomID);
                            $popup_modal_content .= '</div>';
                            $popup_modal_content .= '<div class="armclear"></div>';
                            $popup_modal_content .= '</div></div>';
                            $content = $popup_content;
                            //Condition for check that if already same form_id popup exist then not creating duplicate popup of that.
                            if(!array_key_exists($form->ID, $arm_form_popup_ids_arr))
                            {
                                $arm_form_popup_ids_arr[$form->ID] = $pformRandomID;
                                array_push($arm_inner_form_modal, $popup_modal_content);
                            }
                            if ($social_form == $form->ID) {
                                $content .= '<script data-cfasync="false" type="text/javascript">jQuery(window).on("load", function(){jQuery(".arm_form_popup_link_' . $form->ID . '").trigger("click")});</script>';
                            }
                            $content .= '<div class="armclear">&nbsp;</div>';
                        }
                        if ($form->type == 'registration' || $form->type == 'forgot_password' || $form->type == 'change_password') {
                            global $arm_members_activity, $arm_version;
                            $arm_request_version = get_bloginfo('version');
                            $setact = 0;
                            global $check_version;
                            $setact = $arm_members_activity->$check_version();

                            if ($setact != 1) {
                                $content .= "<div><span style='color:#FF0000; margin-top:10px; font-size:12px !important; text-align:center; display:block !important;'>Powered by <a href='https://www.armemberplugin.com/redirect.php?rdt=t2&arm_version=$arm_version&arm_request_version=$arm_request_version' target='_blank'>ARMember</a></span></div>";
                                $content .= "<div><span style='color:#FF0000; font-size:12px !important; text-align:center; display:block !important;'>&nbsp;&nbsp;(Unlicensed)</span></div>";
                            }
                        }

                        $content .= '</div>';
                    }

                    $content = apply_filters('arm_change_content_after_display_form', $content, $form, $atts, $formRandomID);
                    $ARMember->enqueue_angular_script();
                }
            }
            $ARMember->arm_check_font_awesome_icons($content);
            if(!empty($form_content))
            {
                $ARMember->arm_check_font_awesome_icons($form_content);
            }
			
			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
			if($isEnqueueAll == '1'){
				$content .= '<script type="text/javascript" data-cfasync="false">
									jQuery(document).ready(function (){
										arm_do_bootstrap_angular();
									});';
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

        function arm_parse_login_links($linkLabel, $url = '#', $class = '', $attrs = '') {
            if (strpos(strtoupper($linkLabel), 'ARMLINK')) {
                if (strpos(strtoupper($linkLabel), '[ARMLINK]') && strpos(strtoupper($linkLabel), '[/ARMLINK]') === false) {
                    $linkLabel = str_replace('[ARMLINK]', '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>', $linkLabel);
                    $linkLabel = str_replace('[armlink]', '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>', $linkLabel);
                    $linkLabel = $linkLabel . '</a>';
                } elseif (strpos(strtoupper($linkLabel), '[/ARMLINK]') && strpos(strtoupper($linkLabel), '[ARMLINK]') === false) {
                    $linkLabel = '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>' . $linkLabel;
                    $linkLabel = str_replace('[/ARMLINK]', '</a>', $linkLabel);
                    $linkLabel = str_replace('[/armlink]', '</a>', $linkLabel);
                } else {
                    $linkLabel = str_replace('[ARMLINK]', '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>', $linkLabel);
                    $linkLabel = str_replace('[/ARMLINK]', '</a>', $linkLabel);
                    $linkLabel = str_replace('[armlink]', '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>', $linkLabel);
                    $linkLabel = str_replace('[/armlink]', '</a>', $linkLabel);
                }
            } else {
                $linkLabel = '<a href="' . $url . '" class="' . $class . '" ' . $attrs . '>' . $linkLabel . '</a>';
            }
            return $linkLabel;
        }

        function arm_member_form_get_single_form_fields($form, $atts = array(), $formRandomID = '') {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings, $arm_social_feature, $arm_grecaptcha_is_enqueue;
            $form_id = $form->ID;
            $field_content = $submit_field = "";
            if (!empty($form)) {
                if (!empty($form->fields)) {
                    $isAvatarField = false;
                    $isSocialField = false;
                    $field_content = apply_filters('arm_change_content_before_field', $field_content, $form);
                    $is_hide_username = 0;

                    foreach ($form->fields as $field) {
                        if(isset($field["arm_form_field_slug"]) && (
                            ($field["arm_form_field_slug"] == "user_login" && !empty($field['arm_form_field_option']['hide_username'])) ||
                            ($field["arm_form_field_slug"] == "first_name" && !empty($field['arm_form_field_option']['hide_firstname'])) ||
                            ($field["arm_form_field_slug"] == "last_name" && !empty($field['arm_form_field_option']['hide_lastname'])))
                        ) {
                            continue;
                        }

                        $field_options = maybe_unserialize($field['arm_form_field_option']);
                        if (!in_array($field_options['type'], array('html', 'hidden'))) {
                            $field_options = apply_filters('arm_change_field_options', $field_options);
                        }
                        if (isset($field_options['meta_key'])) {
                            if ($field_options['meta_key'] == 'user_login') {
                                if (isset($field_options['hide_username']) && $field_options['hide_username'] == 1) {
                                    $is_hide_username = 1;
                                }
                            }
                            if ($field_options['meta_key'] == 'first_name') {
                                if (isset($field_options['hide_firstname']) && $field_options['hide_firstname'] == 1) {
                                    $is_hide_firstname = 1;
                                }
                            }
                            if ($field_options['meta_key'] == 'last_name') {
                                if (isset($field_options['hide_lastname']) && $field_options['hide_lastname'] == 1) {
                                    $is_hide_lastname = 1;
                                }
                            }
                        }
                    }


                    foreach ($form->fields as $field) {
                        if(isset($field["arm_form_field_slug"]) &&  (($field["arm_form_field_slug"] == "user_login" && !empty($field['arm_form_field_option']['hide_username'])) ||
                            ($field["arm_form_field_slug"] == "first_name" && !empty($field['arm_form_field_option']['hide_firstname'])) ||
                            ($field["arm_form_field_slug"] == "last_name" && !empty($field['arm_form_field_option']['hide_lastname']))))
                        {
                            continue;
                        }
                        $form_field_id = $field['arm_form_field_id'];
                        $field_options = maybe_unserialize($field['arm_form_field_option']);
                        if (!in_array($field_options['type'], array('html', 'hidden'))) {
                            $field_options = apply_filters('arm_change_field_options', $field_options);
                        }


                        if ( ( isset($atts['type']) && $atts['type'] == 'edit_profile' ) || 'edit_profile' == $form->type ) {
                            
                            if ($field_options['type'] == 'password') {
                                $field_options['required'] = "";
                            }
                            else if ($field_options['type'] == 'repeat_pass') {
                                $field_options['required'] = "";
                            }
                            else if ($field_options['meta_key'] == 'user_login') {
                                $field_options['disabled'] = '1';
                            }
                        }
                        if (function_exists('extract')) {
                            extract($field_options);
                        } else {
                            $id = $field_options['id'];
                            $label = $field_options['label'];
                            $placeholder = $field_options['placeholder'];
                            $type = $field_options['type'];
                            $value = $field_options['value'];
                            $options = $field_options['options'];
                            $bg_color = $field_options['bg_color'];
                            $readonly = $field_options['readonly'];
                            $class = $field_options['class'];
                            $padding = $field_options['padding'];
                            $margin = $field_options['margin'];
                            $allow_ext = $field_options['allow_ext'];
                            $file_size_limit = $field_options['file_size_limit'];
                            $max_date = $field_options['max_date'];
                            $required = $field_options['required'];
                            $hide_username = $field_options['hide_username'];
                            $hide_firstname = $field_options['hide_firstname'];
                            $hide_lastname = $field_options['hide_lastname'];
                            $blank_message = $field_options['blank_message'];
                            $invalid_message = $field_options['invalid_message'];
                            $invalid_username = $field_options['invalid_username'];
                            $invalid_firstname = $field_options['invalid_firstname'];
                            $invalid_lastname = $field_options['invalid_lastname'];
                            $validation_type = $field_options['validation_type'];
                            $regular_expression = $field_options['regular_expression'];
                            $default_field = $field_options['default_field'];
                            $mapfield = $field_options['mapfield'];
                            $ref_field_id = $field_options['ref_field_id'];
                            $enable_repeat_field = $field_options['enable_repeat_field'];
                        }
                        $class = !empty($class) ? ' '.$class :''; 

                        $prefix_name = 'arm_field[' . $form_id . ']';
                        if ($type == 'avatar' || $field_options['meta_key'] == 'avatar') {
                            $isAvatarField = true;
                        }
                        if ($type == 'submit') {
                            if (isset($atts['type']) && $atts['type'] == 'edit_profile') {
                                $field_options['label'] = (isset($atts['submit_text']) && !empty($atts['submit_text'])) ? $atts['submit_text'] : __('Update Profile', 'ARMember');
                                if ($arm_social_feature->isSocialFeature) {
                                    /*                                     * *
                                     * Social Fields
                                     *
                                     * * */
                                    if (!$isSocialField) {
                                        $field_content .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_social_fields" id="arm_form_field_container_' . $form_field_id . '" data-field_id="' . $form_field_id . '">';
                                        if (!empty($atts['social_fields']) && isset($atts['social_fields'])) {
                                            $extraFields = explode(',', rtrim($atts['social_fields'], ','));
                                        } else {
                                            $extraFields = array();
                                        }
                                        /**
                                         * `$extraFields` -- This variable need to get from `Edit Profile` Shortcode argument.
                                         * e.g. $extraFields = array('youtube', 'pinterest');
                                         */
                                        $field_content .= $this->arm_social_profile_field_options_html($form_id, $form_field_id, $field_options, 'active', $form, $extraFields);
                                        $field_content .= '</div>';
                                    }

                                    $common_messages = $arm_global_settings->arm_get_all_common_message_settings();

                                    if( isset($atts['avatar_field']) && $atts['avatar_field'] == 'yes' ) {
                                        $arm_avtar_label = (isset($arm_global_settings->common_message['arm_avtar_label']) && $arm_global_settings->common_message['arm_avtar_label'] != '' ) ? $arm_global_settings->common_message['arm_avtar_label'] : __('Avatar', 'ARMember');
                                        $arm_profile_cover_label = (isset($arm_global_settings->common_message['arm_profile_cover_label']) && $arm_global_settings->common_message['arm_profile_cover_label'] != '' ) ? $arm_global_settings->common_message['arm_profile_cover_label'] : __('Profile Cover', 'ARMember');
                                        if (!$isAvatarField) {
                                            /**
                                             * User Avatar Field
                                             */
                                            $avatar_field_id = 'avatar_' . arm_generate_random_code();
                                            $avatarOptions = array(
                                                'id' => 'avatar',
                                                'label' => $arm_avtar_label,
                                                'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                                                'type' => 'avatar',
                                                'value' => '',
                                                'allow_ext' => '',
                                                'file_size_limit' => '2',
                                                'meta_key' => 'avatar',
                                                'required' => 0,
                                                'blank_message' => __('Please select avatar.', 'ARMember'),
                                                'invalid_message' => __('Invalid image selected.', 'ARMember'),
                                            );
                                            $avatarOptions = apply_filters('arm_change_field_options', $avatarOptions);
                                            $submit_field .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_avatar" id="arm_form_field_container_' . $avatar_field_id . '" data-field_id="' . $avatar_field_id . '">';
                                            $submit_field .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_avatar">';
                                            $submit_field .= '<div class="arm_member_form_field_label">';
                                            if ($required == 1) {
                                                $submit_field .= '<span class="required_tag required_tag_' . $avatar_field_id . '">*</span>';
                                            }
                                            $submit_field .= '<div class="arm_form_field_label_text">' . __('Avatar', 'ARMember') . '</div>';
                                            $submit_field .= '</div>';
                                            $submit_field .= '</div>';
                                            $submit_field .= '<div class="arm_label_input_separator"></div>';
                                            $submit_field .= '<div class="arm_form_input_wrapper">';
                                            $submit_field .= $this->arm_member_form_get_fields_by_type($avatarOptions, $avatar_field_id, $form_id, 'active', $form);
                                            $submit_field .= '</div>';
                                            $submit_field .= '</div>';
                                        }
                                    }
                                    /**
                                     * Profile Cover Field
                                     */
                                    if( isset($atts['profile_cover_field']) && $atts['profile_cover_field'] == 'yes' ) {
                                        $profile_cover_field_id = 'profile_cover_' . arm_generate_random_code();
                                        $arm_profile_cover_label = (isset($arm_global_settings->common_message['arm_profile_cover_label']) && $arm_global_settings->common_message['arm_profile_cover_label'] != '' ) ? $arm_global_settings->common_message['arm_profile_cover_label'] : __('Profile Cover', 'ARMember');
                                        $profileCoverOptions = array(
                                            'id' => 'profile_cover',
                                            'label' => (isset($atts['profile_cover_title']) && !empty($atts['profile_cover_title'])) ? $atts['profile_cover_title'] : $arm_profile_cover_label,
                                            'placeholder' => isset($atts['profile_cover_placeholder']) ? $atts['profile_cover_placeholder'] : __('Drop file here or click to select.', 'ARMember'),
                                            'type' => 'avatar',
                                            'value' => '',
                                            'allow_ext' => '',
                                            'file_size_limit' => '10',
                                            'meta_key' => 'profile_cover',
                                            'required' => 0,
                                            'blank_message' => __('Please select profile cover.', 'ARMember'),
                                            'invalid_message' => __('Invalid image selected.', 'ARMember'),
                                        );
                                        $profileCoverOptions = apply_filters('arm_change_field_options', $profileCoverOptions);
                                        $submit_field .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_profile_cover" id="arm_form_field_container_' . $profile_cover_field_id . '" data-field_id="' . $profile_cover_field_id . '">';
                                        $submit_field .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_profile_cover">';
                                        $submit_field .= '<div class="arm_member_form_field_label">';
                                        if ($required == 1) {
                                            $submit_field .= '<span class="required_tag required_tag_' . $profile_cover_field_id . '">*</span>';
                                        }
                                        $submit_field .= '<div class="arm_form_field_label_text">' . $profileCoverOptions["label"] . '</div>';
                                        $submit_field .= '</div>';
                                        $submit_field .= '</div>';
                                        $submit_field .= '<div class="arm_label_input_separator"></div>';
                                        $submit_field .= '<div class="arm_form_input_wrapper">';
                                        $submit_field .= $this->arm_member_form_get_fields_by_type($profileCoverOptions, $profile_cover_field_id, $form_id, 'active', $form);
                                        $submit_field .= '</div>';
                                        $submit_field .= '</div>';
                                    }
                                }
                            }
                            if (empty($atts['setup'])) {
                                $submit_field .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_' . $type . '" id="arm_form_field_container_' . $form_field_id . '" data-field_id="' . $form_field_id . '">';
                                $submit_field .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_' . $type . '"></div>';
                                $submit_field .= '<div class="arm_label_input_separator"></div>';
                                $submit_field .= '<div class="arm_form_input_wrapper">';
                                $submit_field .= $this->arm_member_form_get_fields_by_type($field_options, $form_field_id, $form_id, 'active', $form);
                                $submit_field .= '</div>';
                                $submit_field .= '</div>';
                            }
                        } elseif ($type == 'social_fields') {
                            $isSocialField = true;
                            if ($arm_social_feature->isSocialFeature) {
                                $field_content .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_social_fields" id="arm_form_field_container_' . $form_field_id . '" data-field_id="' . $form_field_id . '">';
                                if (!empty($atts['social_fields']) && isset($atts['social_fields'])) {
                                    $extraFields = explode(',', rtrim($atts['social_fields'], ','));
                                } else {
                                    $extraFields = array();
                                }
                                /**
                                 * `$extraFields` -- This variable need to get from `Edit Profile` Shortcode argument.
                                 * e.g. $extraFields = array('youtube', 'pinterest');
                                 */
                                $field_content .= $this->arm_social_profile_field_options_html($form_id, $form_field_id, $field_options, 'active', $form, $extraFields);
                                $field_content .= '</div>';
                            }
                        } elseif ($type == 'hidden') {
                            $field_content .= '<div class="arm_form_field_container' . $class . ' hidden_field_hide arm_form_field_container_' . $type . '" id="arm_form_field_container_' . $form_field_id . '" data-field_id="' . $form_field_id . '">';
                            $field_content .= $this->arm_member_form_get_fields_by_type($field_options, $form_field_id, $form_id, 'active', $form);
                            $field_content .= '</div>';
                        } else {
                            $fieldBoxStyle = '';
                            $show_rememberme = (isset($form->settings['show_rememberme'])) ? $form->settings['show_rememberme'] : 0;
                            if ($type == 'rememberme' && $show_rememberme != 1) {
                                $fieldBoxStyle = 'display:none;';
                            }
                            $fieldContClass = '';
                            if ($type == 'section') {
                                $fieldContClass = ' arm_section_fields_wrapper';
                                $margin = !empty($margin) ? $margin : array();
                                $margin['top'] = (isset($margin['top']) && is_numeric($margin['top'])) ? $margin['top'] : 20;
                                $margin['bottom'] = (isset($margin['bottom']) && is_numeric($margin['bottom'])) ? $margin['bottom'] : 20;
                                $fieldBoxStyle .= 'margin-top:' . $margin['top'] . 'px !important;';
                                $fieldBoxStyle .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
                            }


                            if ($type == 'text') {
                                $arm_form_type_check = (isset($atts['type'])) ? $atts['type'] : '';
                                if ($field_options['meta_key'] == 'first_name' && $hide_firstname == 1 && $arm_form_type_check != 'edit_profile') {
                                    $fieldBoxStyle .= 'display: none;';
                                } else if ($field_options['meta_key'] == 'last_name' && $hide_lastname == 1 && $arm_form_type_check != 'edit_profile') {
                                    $fieldBoxStyle .= 'display: none;';
                                } else if ($field_options['meta_key'] == 'user_login' && $hide_username == 1) {
                                    $fieldBoxStyle .= 'display: none;';
                                }
                            }


                            $field_content .= '<div class="arm_form_field_container' . $class . ' arm_form_field_container_' . $type . $fieldContClass . '" id="arm_form_field_container_' . $form_field_id . '" data-field_id="' . $form_field_id . '" style="' . $fieldBoxStyle . '">';
                            if (!in_array($field_options['type'], array('html', 'section')) && !empty($label) ) {
                                $field_content .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_' . $type . '">';
                                if (!in_array($type, array('submit', 'hidden', 'rememberme'))) {
                                    $field_content .= '<div class="arm_member_form_field_label">';
                                    if ($required == 1) {
                                        $field_content .= '<span class="required_tag required_tag_' . $form_field_id . '">*</span>';
                                    }
                                    $field_content .= '<div class="arm_form_field_label_text">';
                                    $field_content .= html_entity_decode(stripslashes($label));
                                    $field_content .= '</div>';
                                    $field_content .= '</div>';
                                }
                                $field_content .= '</div>';
                                $field_content .= '<div class="arm_label_input_separator"></div>';
                            }
                            $field_content .= '<div class="arm_form_input_wrapper">';
                            $field = $this->arm_member_form_get_fields_by_type($field_options, $form_field_id, $form_id, 'active', $form, $formRandomID);
                            if (!($field_options['type'] == 'html' && preg_match("/\bid=\"" . $form_id . "\"/", $field, $match) && preg_match("/\[arm_form\b/", $field, $match))) {
                                $field_content .= $field;
                            }
                            $field_content .= '</div>';
                            $field_content .= '</div>';
                        }
                    }
                    $show_other_form_captcha_field=(isset($form->settings['show_other_form_captcha_field'])) ? $form->settings['show_other_form_captcha_field'] : 0;
                    $arm_recaptcha_v3_status=(isset($form->settings['arm_recaptcha_v3_status'])) ? $form->settings['arm_recaptcha_v3_status'] : 0;
                   
                    if ($show_other_form_captcha_field == 1 || $arm_recaptcha_v3_status==1) {
                        $field_content .='';
                        $dsize = 'normal';
                        $all_global_settings = $arm_global_settings->global_settings;
                        $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                        $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                        $arm_recaptcha_theme = !empty($all_global_settings['arm_recaptcha_theme']) ? $all_global_settings['arm_recaptcha_theme'] : '';
                        $arm_recaptcha_lang = !empty($all_global_settings['arm_recaptcha_lang']) ? $all_global_settings['arm_recaptcha_lang'] : '';
                        
                        if(!empty($arm_recaptcha_site_key) && !empty($arm_recaptcha_private_key)) 
                        {
                            $arm_grecaptcha_is_enqueue = 1;
                            $arm_google_recaptcha_url = add_query_arg(
                                array(
                                    'hl'=> $arm_recaptcha_lang,
                                    'render' => $arm_recaptcha_site_key,
                                    'onload'=>'render_arm_captcha_v3',
                                ),
                                'https://www.google.com/recaptcha/api.js'
                            );

                            wp_enqueue_script('arm-google-recaptcha',$arm_google_recaptcha_url, array('jquery'), MEMBERSHIP_VERSION);
                        }
                        $arm_g_recaptcha_response = !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
                        $field_content .='<input name="arm_settings_recaptcha_site_key" type="hidden" data-id="arm_settings_recaptcha_site_key" class="arm_settings_recaptcha_site_key" value="' . ($arm_recaptcha_site_key) . '"  />';
                        $field_content .='<input name="arm_settings_recaptcha_theme" type="hidden" data-id="arm_settings_recaptcha_theme" class="arm_settings_recaptcha_theme" value="' . ($arm_recaptcha_theme) . '"  />';
                        $field_content .= '<input name="arm_captcha_'.$formRandomID.'" type="hidden" value="' . esc_attr($arm_g_recaptcha_response) . '"  id="arm_captcha_'.$formRandomID.'" />';
                       $field_content .= '<script type="text/javascript" data-cfasync="false">
                            window.addEventListener("DOMContentLoaded", function() { (function($) {
                                jQuery(document).ready(function (){
                            if( !window["arm_recaptcha_v3"] ){
                                window["arm_recaptcha_v3"] = {};
                            }
                            
                            window["arm_recaptcha_v3"]["arm_captcha_'.$formRandomID.'"] = {
                                size : "' . $dsize . '"
                            };
                            
                            }); })(jQuery); });';
                        $field_content .= '</script>';
                    }
                    $field_content = apply_filters('arm_change_content_after_field', $field_content, $form);
                    $field_content .= $submit_field;
                }
            }
            return do_shortcode($field_content);
        }

        function arm_member_form_get_field_html($form_id = 0, $form_field_id = 0, $field_options = array(), $form_type = 'inactive', $form = '', $isEditProfile = false) {

            global $wp, $wpdb, $current_user, $arm_slugs, $ARMember, $arm_subscription_plans, $arm_global_settings, $arm_buddypress_feature;
            $field_options = maybe_unserialize($field_options);
            $field_options = apply_filters('arm_change_field_options', $field_options);
            if (function_exists('extract')) {
                extract($field_options);
            } else {
                $id = $field_options['id'];
                $label = $field_options['label'];
                $placeholder = $field_options['placeholder'];
                $type = $field_options['type'];
                $meta_key = strtolower($field_options['meta_key']);
                $sub_type = $field_options['sub_type'];
                $value = $field_options['value'];
                $bg_color = $field_options['bg_color'];
                $readonly = $field_options['readonly'];
                $class = $field_options['class'];
                $padding = $field_options['padding'];
                $margin = $field_options['margin'];
                $options = $field_options['options'];
                $allow_ext = $field_options['allow_ext'];
                $file_size_limit = $field_options['file_size_limit'];
                $max_date = $field_options['max_date'];
                $required = $field_options['required'];
                $blank_message = $field_options['blank_message'];
                $invalid_username = $field_options['invalid_username'];
                $invalid_firstname = $field_options['invalid_firstname'];
                $invalid_lastname = $field_options['invalid_lastname'];
                $validation_type = $field_options['validation_type'];
                $regular_expression = $field_options['regular_expression'];
                $invalid_message = $field_options['invalid_message'];
                $default_field = $field_options['default_field'];
                $mapfield = $field_options['mapfield'];
                $ref_field_id = $field_options['ref_field_id'];
                $enable_repeat_field = $field_options['enable_repeat_field'];
            }
            $prefix_name = 'arm_forms[' . $form_id . ']';
            $material_class = '';
            if ($type == 'social_fields') {
                echo $this->arm_social_profile_field_options_html($form_id, $form_field_id, $field_options, $form_type, $form);
            } else {
                if (isset($form->settings['style']) && $form->settings['style']['form_layout'] == 'writer' && !in_array($type, array('radio', 'checkbox', 'rememberme', 'file', 'avatar', 'profile_cover'))) {
                    $material_class = 'layout-gt-sm="row"';
                }
                ?>
                <div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_<?php echo $type ?>">
                <?php if (!in_array($type, array('submit', 'hidden', 'html', 'section', 'rememberme'))) { ?>
                        <div class="arm_member_form_field_label"> <span class="required_tag required_tag_<?php echo $form_field_id; ?>">
                    <?php
                    if ($required == 1) {
                        echo '*';
                    }
                    ?>
                            </span>
                            <div class="arm_form_field_label_wrapper_text arm_form_field_label_text"><?php echo html_entity_decode(stripslashes($label)); ?></div>
                        </div>
                <?php } ?>
                <?php if ($form_type == 'inactive'): ?>
                        <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][id]" value="<?php echo $id; ?>"/>
                        <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][type]" value="<?php echo $type; ?>"/>
                        <input type="hidden" class="arm_is_default_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][default_field]" value="<?php echo $default_field; ?>"/>
                <?php endif; ?>
                </div>
                <div class="arm_label_input_separator"></div>
                <div class="arm_form_input_wrapper" <?php echo $material_class; ?>> <?php echo $this->arm_member_form_get_fields_by_type($field_options, $form_field_id, $form_id, $form_type, $form); ?> </div>
                <?php if ($form_type == 'inactive') { ?>
                    <div class="arm_form_settings_icon">
                    <?php if ($type != 'submit') { ?>
                            <a href="javascript:void(0)" class="arm_form_member_settings_icon armhelptip" title="<?php _e('Edit Field Options', 'ARMember'); ?>" data-field_id="<?php echo $form_field_id; ?>" data-field_type="<?php echo $type; ?>"> <img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_setting.png" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_setting_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_setting.png';" style='cursor:pointer'/> </a>
                        <?php if ($default_field != 1 && !in_array($type, array('repeat_email', 'repeat_pass'))) { ?>
                                <a href="javascript:void(0)" class="arm_form_member_delete_icon armhelptip" data-field_id="<?php echo $form_field_id; ?>" data-field_type="<?php echo $type; ?>" title="<?php _e('Delete Field', 'ARMember'); ?>" onclick="showConfirmBoxCallback(<?php echo $form_field_id; ?>);"> <img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_delete.png" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_delete_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_delete.png';" style='cursor:pointer'/> </a>
                        <?php } ?>
                            <a href="javascript:void(0)" class="arm_form_member_sortable_icon armhelptip" title="<?php _e('Sort Field Order', 'ARMember'); ?>"> <img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_drag.png" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_drag_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/fe_drag.png';" style='cursor:pointer'/> </a>
                    <?php } ?>
                    </div>
                    <?php
                        if ($default_field != 1) {
                            echo $gridAction = $arm_global_settings->arm_get_confirm_box($form_field_id, __("Are you sure you want to delete this field?", 'ARMember'), 'arm_field_delete_ok_btn', $type);
                        }
                    ?>
                            <div class="arm_form_field_settings_menu_wrapper arm_slider_box arm_form_field_settings_menu_wrapper_<?php echo $form_field_id; ?>" data-field_id="<?php echo $form_field_id; ?>" data-ftype="<?php echo $type; ?>">
                                <div class="arm_form_field_settings_menu arm_slider_box_container">
                                    <div class="arm_form_field_settings_menu_arrow arm_slider_box_arrow"></div>
                                        <div class="arm_slider_box_heading">
                                            <?php _e('Custom Setting', 'ARMember'); ?>
                                        </div>
                                        <div class="arm_slider_box_body">
                                            <?php
                                                if (!in_array($type, array('hidden', 'html', 'section', 'rememberme'))): ?>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                            <?php _e('Field Label', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][label]" class="arm_form_field_label_wrapper_value field_label_text" value="<?php echo esc_html($label); ?>"/>
                                                        </div>
                                                    </div>
                                            <?php
                                                endif;
                                            ?>
                                            <div class="arm_form_field_settings_menu_inner">
                                                <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Description', 'ARMember'); ?>
                                                </div>
                                                <div class="arm_form_field_settings_field_val">
                                                    <input type="text" class="arm_form_field_description_wrapper_value arm_form_field_settings_field_val_input field_description_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][description]" value="<?php echo stripslashes_deep($description); ?>" />
                                                </div>
                                            </div>
                                            <div class="arm_form_field_settings_menu_inner">
                                                <div class="arm_form_field_settings_field_label">
                                                    <?php esc_html_e('CSS Class','ARMember'); ?>
                                                </div>
                                                <div class="arm_form_field_settings_field_val">
                                                    <input type="text" class="arm_form_field_description_wrapper_value arm_form_field_settings_field_val_input field_class_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name.'['.$form_field_id.'][class]'; ?>" value="<?php echo stripslashes_deep($class); ?>" />
                                                </div>
                                            </div>
                                            <?php
                                                if( $isEditProfile ){

                                            ?>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                            <?php esc_html_e( 'Read Only', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <label>
                                                                <input type="checkbox" name="<?php echo $prefix_name.'['.$form_field_id.'][readonly]'; ?>" <?php isset($readonly) ? checked($readonly, 1) : ''; ?> class="arm_icheckbox" value="1" />
                                                            </label>
                                                        </div>
                                                    </div>
                                            <?php
                                                }
                                            $enable_repeat_field = (isset($field_options['enable_repeat_field'])) ? $field_options['enable_repeat_field'] : 0;
                                            switch ($type) {
                                                case 'checkbox':
                                                case 'radio':
                                                case 'select':
                                                    $old_options = '';
                                                    if (!empty($options)) {
                                                        foreach ($options as $key => $opt) {
                                                            $opt = stripslashes($opt);
                                                            $old_options .= "$opt\n";
                                                        }
                                                    }
                                                    ?>
                                                                <div class="arm_form_field_settings_menu_inner">
                                                                    <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Options', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_form_field_settings_field_val">
                                                                        <textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options]"><?php echo $old_options; ?></textarea>
                                                                        <p class="description">
                                                                <?php _e('You should place each option on a new line.', 'ARMember'); ?>
                                                                            <br/>
                                                                <?php _e('Separate values format should be label:value.', 'ARMember'); ?>
                                                                <?php
                                                                    if(trim($meta_key)=='country')
                                                                    {
                                                                ?>
                                                                        <br/>
                                                                <?php   
                                                                        echo '<font color="red">'.__('Please don\'t change value of country ID. For example: :1,:2,:3', 'ARMember').'</font>';

                                                                    }
                                                                ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                    <?php
                                                    break;
                                                case 'role':
                                                case 'roles':
                                                ?>
                                                <div class="arm_form_field_settings_menu_inner arm_roles_field_options_type">
                                                    <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Field Type', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="radio" id="role_sub_type_select" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][sub_type]" class="arm_form_field_settings_field_input field_options_text arm_iradio" data-ftype="<?php echo $type; ?>" value="select" <?php checked($sub_type, 'select'); ?>>
                                                        <label for="role_sub_type_select">
                                                            <?php _e('Select', 'ARMember'); ?>
                                                        </label>
                                                        <input type="radio" id="role_sub_type_radio" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][sub_type]" class="arm_form_field_settings_field_input field_options_text arm_iradio" data-ftype="<?php echo $type; ?>" value="radio" <?php checked($sub_type, 'radio'); ?>>
                                                        <label for="role_sub_type_radio">
                                                            <?php _e('Radio', 'ARMember'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="arm_form_field_settings_menu_inner arm_roles_field_options_type">
                                                    <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Select roles to display at front-end.', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val arm_role_field_options">
                                                            <?php
                                                                $allRoles = $arm_global_settings->arm_get_all_roles();
                                                                if (!empty($allRoles)) {
                                                                    foreach ($allRoles as $roleK => $roleN) {
                                                                        $options[$roleK] = isset($options[$roleK]) ? $options[$roleK] : '';
                                                                        ?>
                                                                    <label>
                                                                        <input type="checkbox" value="<?php echo $roleN; ?>" <?php checked($options[$roleK], $roleN); ?> class="arm_icheckbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][<?php echo $roleK; ?>]" />
                                                                        <span class="arm_form_field_settings_notice"><?php echo $roleN; ?></span></label>
                                                                    <?php
                                                                    }
                                                                }
                                                            ?>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                case 'file':
                                case 'profile_cover':
                                case 'avatar':
                                    $placeholder = (!empty($placeholder)) ? $placeholder : __('Drop file here or click to select.', 'ARMember');
                                    ?>
                                    <div class="arm_form_field_settings_menu_inner">
                                        <div class="arm_form_field_settings_field_label">
                                                <?php _e('Placeholder', 'ARMember'); ?>
                                        </div>
                                        <div class="arm_form_field_settings_field_val">
                                            <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="file_placeholder_text" value="<?php echo $placeholder; ?>"/>
                                        </div>
                                    </div>
                                    <?php
                                        if ($type != 'avatar' && $type != 'profile_cover') {
                                    ?>
                                            <div class="arm_form_field_settings_menu_inner">
                                                <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Allowed File Extension', 'ARMember'); ?>
                                                </div>
                                                <div class="arm_form_field_settings_field_val">
                                                    <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][allow_ext]" class="allow_ext arm_form_field_settings_field_input" value="<?php echo $allow_ext; ?>"/>
                                                    <p class="description">
                                                        <?php _e('You should place comma separated list of file extensions.', 'ARMember'); ?>
                                                        <br/>
                                                        <?php _e('Leave blank for allow all file types.', 'ARMember'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label">
                                                <?php _e('File Size Limit', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][file_size_limit]" class="file_size_limit arm_form_field_settings_field_input" value="<?php echo $file_size_limit; ?>" style="width: 60px;text-align: center;"/>
                                                        <span>MB</span>
                                                        <p class="description" style="color: #F00">
                                                        <?php
                                                        $max_upload = (int) (ini_get('upload_max_filesize'));
                                                        $max_post = (int) (ini_get('post_max_size'));
                                                        $memory_limit = (int) (ini_get('memory_limit'));
                                                        $upload_mb = min($max_upload, $max_post, $memory_limit);
                                                        _e('PHP Maximum Upload Size: ' . $upload_mb . 'MB', 'ARMember');
                                                        ?>
                                                        </p>
                                                    </div>
                                                </div>
                                    <?php
                                    break;
                                case 'hidden':
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label">
                                                            <?php _e('Hidden Value', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][value]"><?php echo $value; ?></textarea>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'html':
                                                ?>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label arm_html_field_options">
                                    <?php _e('Html Text', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val arm_html_field_options">
                                                        <textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][value]"><?php echo stripcslashes($value); ?></textarea>
                                                    </div>
                                                </div>
                                                            <?php
                                                            break;
                                                        case 'section':
                                                            ?>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label">
                                    <?php _e('Section Heading', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo $type; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][value]"><?php echo stripcslashes($value); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Section Margin', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <div class="arm_button_margin_inputs_container">
                                    <?php
                                    $margin = !empty($margin) ? $margin : array();
                                    $margin['top'] = (isset($margin['top']) && is_numeric($margin['top'])) ? $margin['top'] : 20;
                                    $margin['bottom'] = (isset($margin['bottom']) && is_numeric($margin['bottom'])) ? $margin['bottom'] : 20;
                                    ?>
                                                            <div class="arm_button_margin_inputs">
                                                                <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][margin][top]" data-type="<?php echo $type; ?>" class="arm_section_margin_opt arm_section_margin_top" value="<?php echo $margin['top']; ?>" onkeydown="javascript:return checkNumber(event)" min="0" maxlength="3"/>
                                                                <br />
                                                        <?php _e('Top', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_button_margin_inputs">
                                                                <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][margin][bottom]" data-type="<?php echo $type; ?>" class="arm_section_margin_opt arm_section_margin_bottom" value="<?php echo $margin['bottom']; ?>" onkeydown="javascript:return checkNumber(event)" min="0" maxlength="3"/>
                                                                <br />
                                                <?php _e('Bottom', 'ARMember'); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                    <?php
                                    break;
                                case 'date':
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
                                                    <div class="arm_form_field_settings_field_label">
                                    <?php _e('Placeholder', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="placeholder_text" value="<?php echo $placeholder; ?>"/>
                                                    </div>
                                                </div>
                                    <?php
                                    break;
                                case 'rememberme':
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label">
                                                            <?php _e('Label', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][label]" class="field_options_text" data-ftype="rememberme" value="<?php echo esc_html($label); ?>"/>
                                                    </div>
                                                </div>
                                    <?php
                                    break;
                                case 'password':
                                    $options['minlength'] = (isset($options['minlength'])) ? $options['minlength'] : '';
                                    $options['maxlength'] = (isset($options['maxlength'])) ? $options['maxlength'] : '';
                                    $options['strength_meter'] = (isset($options['strength_meter'])) ? $options['strength_meter'] : 0;
                                    $options['strong_password'] = (isset($options['strong_password'])) ? $options['strong_password'] : 0;
                                    $options['special'] = (isset($options['special'])) ? $options['special'] : 0;
                                    $options['numeric'] = (isset($options['numeric'])) ? $options['numeric'] : 0;
                                    $options['uppercase'] = (isset($options['uppercase'])) ? $options['uppercase'] : 0;
                                    $options['lowercase'] = (isset($options['lowercase'])) ? $options['lowercase'] : 0;
                                    $options['veryweaktext'] = (!empty($options['veryweaktext'])) ? stripslashes($options['veryweaktext']) : __('Strength: Very Weak', 'ARMember');
                                    $options['weaktext'] = (!empty($options['weaktext'])) ? stripslashes($options['weaktext']) : __('Strength: Weak', 'ARMember');
                                    $options['goodtext'] = (!empty($options['goodtext'])) ? stripslashes($options['goodtext']) : __('Strength: Good', 'ARMember');
                                    $options['strongtext'] = (!empty($options['strongtext'])) ? stripslashes($options['strongtext']) : __('Strength: Strong', 'ARMember');
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
                                                    <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Placeholder', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="placeholder_text" value="<?php echo $placeholder; ?>"/>
                                                    </div>
                                                </div>
                                                <?php if ($meta_key == 'repeat_pass' || $id == 'repeat_pass') : ?>
                                                <?php else: ?>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                        <?php _e('Min Length', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="number" value="<?php echo $options['minlength']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][minlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
                                                        </div>
                                                    </div>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Max Length', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="number" value="<?php echo $options['maxlength']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][maxlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
                                                        </div>
                                                    </div>
                                                    <?php if ($form->type != 'login'): ?>
                                                        <div class="arm_form_field_settings_menu_inner">
                                                            <div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
                                                        <?php _e('Display Strength Meter?', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                            <?php $is_strength_meter = isset($options['strength_meter']) ? $options['strength_meter'] : 0; ?>
                                                                <label style="margin-left: -4px;">
                                                                    <input type="checkbox" value="1" <?php checked($is_strength_meter, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_strength_meter_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][strength_meter]" />
                                                                </label>
                                                                <p class="description">
                                            <?php _e('It will not visible in editor / preview. Please check at front-end.', 'ARMember'); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="arm_form_field_settings_menu_inner arm_form_field_strength_text <?php echo ($is_strength_meter != 1) ? 'hidden_section' : ''; ?>">                         
                                                            <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Very Weak Text', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                                            <input type="textbox" value="<?php echo $options['veryweaktext']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][veryweaktext]"  />
                                                            </div>
                                                            <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Weak Text', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                                                <input type="textbox" value="<?php echo $options['weaktext']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][weaktext]" />
                                                            </div>
                                                            <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Good Text', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                                                <input type="textbox" value="<?php echo $options['goodtext']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][goodtext]"  />
                                                            </div>
                                                            <div class="arm_form_field_settings_field_label">
                                                        <?php _e('Strong Text', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                                                <input type="textbox" value="<?php echo $options['strongtext']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][strongtext]" />
                                                            </div>
                                                        </div>                                                           
                                                        <div class="arm_form_field_settings_menu_inner">
                                                            <div class="arm_form_field_settings_field_label">
                                                                <?php _e('Strong Password?', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                            <?php $is_strong_password = isset($options['strong_password']) ? $options['strong_password'] : 0; ?>
                                                                <label style="margin-left: -4px;">
                                                                    <input type="checkbox" value="1" <?php checked($is_strong_password, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_strong_password_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][strong_password]"/>
                                                                    <span class="arm_form_field_settings_notice">
                                            <?php _e('Enable Strong Password?', 'ARMember'); ?>
                                                                    </span></label>
                                                                <div class="arm_strong_password_options <?php echo ($is_strong_password != 1) ? 'hidden_section' : ''; ?>">
                                                                    <label>
                                                                        <input type="checkbox" value="1" <?php checked($options['special'], 1); ?> class="arm_icheckbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][special]" />
                                                                        <span class="arm_form_field_settings_notice">
                                            <?php _e('Require Special Charecter?', 'ARMember'); ?>
                                                                        </span></label>
                                                                    <label>
                                                                        <input type="checkbox" value="1" <?php checked($options['numeric'], 1); ?> class="arm_icheckbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][numeric]" />
                                                                        <span class="arm_form_field_settings_notice">
                                                                <?php _e('Require Numeric Value?', 'ARMember'); ?>
                                                                        </span></label>
                                                                    <label>
                                                                        <input type="checkbox" value="1" <?php checked($options['uppercase'], 1); ?> class="arm_icheckbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][uppercase]" />
                                                                        <span class="arm_form_field_settings_notice">
                                            <?php _e('Require Uppercase Character?', 'ARMember'); ?>
                                                                        </span></label>
                                                                    <label>
                                                                        <input type="checkbox" value="1" <?php checked($options['lowercase'], 1); ?> class="arm_icheckbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][lowercase]" />
                                                                        <span class="arm_form_field_settings_notice">
                                            <?php _e('Require Lowercase Character?', 'ARMember'); ?>
                                                                        </span></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                        <?php endif; ?>
                                                            <?php if ($form->type == 'registration' || $form->type == 'edit_profile'): ?>
                                                        <div class="arm_form_field_settings_menu_inner">
                                                            <div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
                                            <?php _e('Enable Confirm Password?', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_form_field_settings_field_val">
                                                                <label style="margin-left: -4px;">
                                                                    <input type="checkbox" value="1" <?php checked($enable_repeat_field, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_enable_repeat_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][enable_repeat_field]" data-field_id="<?php echo $form_field_id; ?>" data-field_type="repeat_pass"/>
                                                                </label>
                                                            </div>
                                                        </div>
                                                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php
                                    break;
                                case 'email':
                                    ?>
                                                <div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
                                                    <div class="arm_form_field_settings_field_label">
                                    <?php _e('Placeholder', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="placeholder_text" value="<?php echo $placeholder; ?>"/>
                                                    </div>
                                                </div>
                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
                                    <?php _e('Enable Confirm Email Address?', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <label style="margin-left: -4px;">
                                                            <input type="checkbox" value="1" <?php checked($enable_repeat_field, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_enable_repeat_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][enable_repeat_field]" data-field_id="<?php echo $form_field_id; ?>" data-field_type="repeat_email"/>
                                                        </label>
                                                    </div>
                                                </div>
                                                        <?php
                                                        break;
                                                    case 'repeat_pass':
                                                    case 'repeat_email':
                                                        ?>
                                                <input type="hidden" value="<?php echo $required; ?>" class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][required]"/>
                                                <div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
                                                    <div class="arm_form_field_settings_field_label">
                                                <?php _e('Placeholder', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="placeholder_text" value="<?php echo $placeholder; ?>"/>
                                                    </div>
                                                </div>
                                    <?php
                                    break;
                                case 'submit':
                                    break;
                                default:

                                    $options['minlength'] = (isset($options['minlength'])) ? $options['minlength'] : '';
                                    $options['maxlength'] = (isset($options['maxlength'])) ? $options['maxlength'] : '';
                                    ?>
                                                        <?php /* -------------------- Form Field Settings  ---------------------------------------------------------------- */ ?>  

                                    <?php /* ------------------------- Placeholder ----------------------------- */ ?>             
                                                <div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
                                                    <div class="arm_form_field_settings_field_label">
                                    <?php _e('Placeholder', 'ARMember'); ?>
                                                    </div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][placeholder]" class="placeholder_text" value="<?php echo $placeholder; ?>"/>
                                                    </div>
                                                </div>

                                                <?php /* ------------------------- MinLength & Maxlength  ----------------------------- */ ?>                     
                                                <?php if ($type != 'email') { ?>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                            <?php _e('Min Length', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="number" value="<?php echo $options['minlength']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][minlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
                                                        </div>
                                                    </div>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Max Length', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="number" value="<?php echo $options['maxlength']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][options][maxlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                break;
                            }
                            ?>

                            <?php /* ------------ Hide Username, Firts Name & LastName -------------------------------- */ ?>        
                            <?php
                            if (!in_array($type, array('submit'))) {

                                    if ($default_field == 1 && in_array($meta_key, array('user_login')) && $form->type == 'registration') {
                                        ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Hide username field and assign username with email', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <label style="margin-left: -4px;">
                                            <input type="checkbox" value="1" <?php checked($hide_username, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][hide_username]" />
                                        </label>
                                    </div>
                                </div>
                    <?php
                }

                if ($default_field == 1 && in_array($meta_key, array('first_name')) && $form->type == 'registration') {
                    ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                    <?php _e('Hide First Name field', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <label style="margin-left: -4px;">
                                            <input type="checkbox" value="1" <?php checked($hide_firstname, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][hide_firstname]" />
                                        </label>
                                    </div>
                                </div>
                                <?php
                            }

                            if (in_array($meta_key, array('last_name')) && $form->type == 'registration') {
                                ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                        <?php _e('Hide Last Name field', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <label style="margin-left: -4px;">
                                            <input type="checkbox" value="1" <?php checked($hide_lastname, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][hide_lastname]" />
                                        </label>
                                    </div>
                                </div>
                                <?php
                            }

                            /* --------------------------- Required Checkbox ---------------------------------- */

                            if(in_array($form->type, array('login', 'change_password')) && in_array($meta_key, array('user_login', 'user_email', 'user_pass')))
                            {
                                ?>
                                <input type="checkbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][required]" value="1" checked="checked" class="arm_form_field_settings_required_field" style="display: none;"/>
                                        <?php

                            } else {
                                if ($default_field == 1 && in_array($meta_key, array('first_name', 'last_name', 'user_login', 'user_email')))  {
                                ?>
                                <input type="checkbox" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][required]" value="1" checked="checked" class="arm_form_field_settings_required_field" style="display: none;"/>
                                        <?php
                                    } else {
                                        if (!in_array($type, array('hidden', 'html', 'section', 'rememberme', 'repeat_pass', 'repeat_email'))) {
                                            ?>
                                    <div class="arm_form_field_settings_menu_inner">
                                        <div class="arm_form_field_settings_field_label">
                        <?php _e('Required', 'ARMember'); ?>
                                        </div>
                                        <div class="arm_form_field_settings_field_val">
                                            <label style="margin-left: -4px;">
                                                <input type="checkbox" value="1" <?php checked($required, 1); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][required]" />
                                            </label>
                                        </div>
                                    </div>
                                            <?php
                                        }
                                    }
                            }
                            ?>


                <?php
                /* -------------------------- Validation ----------------------------------------- */

                if ($type == 'text' && !in_array($meta_key, array('user_login', 'user_email', 'user_pass'))) {
                    ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Validation', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                <?php $validation_type = isset($validation_type) ? $validation_type : 'custom_validation_none'; ?>
                                        <input id="arm_form_field_settings_validation_type_<?php echo $form_field_id; ?>" field_id = "<?php echo $form_field_id; ?>" type='hidden' name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][validation_type]" class="arm_form_field_settings_field_input arm_form_field_settings_validation_type" value="<?php echo $validation_type; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_210">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_form_field_settings_validation_type_<?php echo $form_field_id; ?>">
                                                    <li data-value="custom_validation_none" data-label="<?php _e('None', 'ARMember'); ?>"><?php _e('None', 'ARMember'); ?></li>
                                                    <li  data-value="customvalidationalpha" data-label="<?php _e('Only Alphabets', 'ARMember'); ?>"><?php _e('Only Alphabets', 'ARMember'); ?></li>
                                                    <li data-value="customvalidationnumber" data-label="<?php _e('Only Numbers', 'ARMember'); ?>"><?php _e('Only Numbers', 'ARMember'); ?></li>
                                                    <li data-value="customvalidationalphanumber" data-label="<?php _e('Only Alphabets & Numbers', 'ARMember'); ?>"><?php _e('Only Alphabets & Numbers', 'ARMember'); ?></li>
                                                    <li data-value="customvalidationregex" data-label="<?php _e('Regular Expression', 'ARMember'); ?>"><?php _e('Regular Expression', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                <?php
                                $disabled_validation_msg = '';
                                $disabled_regular_expression = '';
                                if ($validation_type == 'custom_validation_none') {
                                    $disabled_validation_msg = 'disabled="disabled"';
                                    $disabled_regular_expression = 'disabled="disabled"';
                                }
                                if ($validation_type != 'customvalidationregex') {
                                    $disabled_regular_expression = 'disabled="disabled"';
                                }
                                ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                    <?php _e('Validation message', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_validation_msg" <?php echo $disabled_validation_msg; ?> name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][invalid_message]" value="<?php echo stripcslashes($invalid_message); ?>" id="arm_form_field_settings_validation_msg_<?php echo $form_field_id; ?>"/>

                                    </div>
                                </div>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                    <?php _e('Regular Expression', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" value="<?php echo $regular_expression; ?>" <?php echo $disabled_regular_expression; ?> class="arm_form_field_settings_field_input arm_form_field_settings_regular_expression" id="arm_form_field_settings_regular_expression_<?php echo $form_field_id; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][regular_expression]"/>
                                        <br/>
                                        <span><?php _e('e.g.', 'ARMember'); ?>  <b>/^.+@.+\..+$/</b></span>
                                    </div>
                                </div> <?php
                }
                ?>


                            <?php
                            /* --------------------------------- Metakey ------------------------------------- */

                            if ($default_field != 1 && !in_array($type, array('avatar', 'profile_cover', 'roles', 'html', 'section', 'rememberme', 'repeat_pass', 'repeat_email', 'password'))) {
                                ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Meta Key', 'ARMember');
                                echo $type; ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][meta_key]" value="<?php echo strtolower($meta_key); ?>" class="arm_form_field_settings_field_input arm_form_field_settings_meta_key"/>
                                    </div>
                                </div>
                    <?php } else {
                    ?>
                                <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][meta_key]" value="<?php echo strtolower($meta_key); ?>" class="arm_form_field_settings_field_input arm_form_field_settings_meta_key"/>
                <?php } ?>


                <?php
                /* ---------------------------------- Blank Field Message & Invalid Field Message  ----------------------------- */

                if (!in_array($type, array('hidden', 'html', 'section', 'rememberme'))) {
                    ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Blank field message', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" value="<?php echo stripcslashes($blank_message); ?>" class="arm_form_field_settings_field_input arm_form_field_settings_blank_msg" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][blank_message]"/>
                                    </div>
                                </div>
                                <?php if (!in_array($type, array('text', 'password', 'select', 'checkbox', 'radio', 'textarea'))) { ?>
                                    <div class="arm_form_field_settings_menu_inner">
                                        <div class="arm_form_field_settings_field_label">
                                            <?php _e('Invalid field message', 'ARMember'); ?>
                                        </div>
                                        <div class="arm_form_field_settings_field_val">
                                            <input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_msg" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][invalid_message]" value="<?php echo stripcslashes($invalid_message); ?>"/>
                                        </div>
                                    </div>
                                <?php
                                }
                            }

                            if ($default_field == 1 && in_array($meta_key, array('user_login')) && $form->type == 'registration') {
                                ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Invalid field message', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_username" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][invalid_username]" value="<?php echo stripcslashes($invalid_username); ?>"/>
                                    </div>
                                </div>
                                        <?php
                                    }

                                    if ($default_field == 1 && in_array($meta_key, array('first_name')) && $form->type == 'registration') {
                                        ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Invalid field message', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_firstname" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][invalid_firstname]" value="<?php echo stripcslashes($invalid_firstname); ?>"/>
                                    </div>
                                </div>
                    <?php
                }

                if (in_array($meta_key, array('last_name')) && $form->type == 'registration') {
                    ?>
                                <div class="arm_form_field_settings_menu_inner">
                                    <div class="arm_form_field_settings_field_label">
                                <?php _e('Invalid field message', 'ARMember'); ?>
                                    </div>
                                    <div class="arm_form_field_settings_field_val">
                                        <input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_lastname" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][invalid_lastname]" value="<?php echo stripcslashes($invalid_lastname); ?>"/>
                                    </div>
                                </div>
                    <?php
                }
                ?>

                            <?php
                            /* =============================./Begin Iconic Support Options/.============================= */
                            if (in_array($type, array('text', 'email', 'repeat_email', 'password', 'repeat_pass', 'url', 'date'))) {
                                ?>
                                <div class="arm_member_form_iconic_options">
                                    <div class="arm_form_field_settings_menu_inner">
                                        <div class="arm_form_field_settings_field_label">
                                <?php _e('Add Icon', 'ARMember'); ?>
                                        </div>
                                        <div class="arm_form_field_settings_field_val">
                                            <div class="arm_field_prefix_suffix_wrapper" id="arm_field_prefix_suffix_wrapper_<?php echo $form_field_id; ?>">
                                                <div class="arm_prefix_wrapper arm_ps_icons_opt_wraper" style="width: 60px;">
                                                    <div class="arm_prefix_suffix_container_wrapper" data-type="prefix" data-field_id="<?php echo $form_field_id; ?>" id="arm_edit_prefix_<?php echo $form_field_id; ?>" data-toggle="armmodal">
                                                        <div class="arm_prefix_container" id="arm_select_prefix_<?php echo $form_field_id; ?>">
                                <?php
                                if (!empty($field_options['prefix'])) {
                                    echo '<i class="armfa ' . $field_options['prefix'] . '"></i>';
                                } else {
                                    _e('No Icon', 'ARMember');
                                }
                                ?>
                                                        </div>
                                                        <input type="hidden" id="arm_prefix_<?php echo $form_field_id; ?>" value="<?php echo $field_options['prefix']; ?>">
                                                        <div class="arm_prefix_suffix_action_container">
                                                            <div class="arm_prefix_suffix_action" title="Change Icon"><i class="armfa armfa-caret-down armfa-lg"></i></div>
                                                        </div>
                                                    </div>
                                                    <div class="arm_prefix_suffix_icons_container arm_slider_box"></div>
                                                    <div class="armclear"></div>
                                                    <div class="howto">
                                <?php _e('Prefix', 'ARMember'); ?>
                                                    </div>
                                                    <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][prefix]" value="<?php echo $field_options['prefix']; ?>" id="arm_field_prefix_<?php echo $form_field_id; ?>"/>
                                                </div>
                                                <div class="arm_suffix_wrapper arm_ps_icons_opt_wraper" style="width: 60px;<?php echo (is_rtl()) ? 'margin-right: 15px;' : 'margin-left: 15px;'; ?>">
                                                    <div class="arm_prefix_suffix_container_wrapper" data-type="suffix" data-field_id="<?php echo $form_field_id; ?>" id="arm_edit_suffix_<?php echo $form_field_id; ?>" data-toggle="armmodal">
                                                        <div class="arm_suffix_container" id="arm_select_suffix_<?php echo $form_field_id; ?>">
                    <?php
                    if (!empty($field_options['suffix'])) {
                        echo '<i class="armfa ' . $field_options['suffix'] . '"></i>';
                    } else {
                        _e('No Icon', 'ARMember');
                    }
                    ?>
                                                        </div>
                                                        <input type="hidden" id="arm_suffix_<?php echo $form_field_id; ?>" value="<?php echo $field_options['suffix']; ?>">
                                                        <div class="arm_prefix_suffix_action_container">
                                                            <div class="arm_prefix_suffix_action" title="Change Icon"><i class="armfa armfa-caret-down armfa-lg"></i></div>
                                                        </div>
                                                    </div>
                                                    <div class="arm_prefix_suffix_icons_container arm_slider_box"></div>
                                                    <div class="armclear"></div>
                                                    <div class="howto">
                    <?php _e('Suffix', 'ARMember'); ?>
                                                    </div>
                                                    <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][suffix]" value="<?php echo $field_options['suffix']; ?>" id="arm_field_suffix_<?php echo $form_field_id; ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                <?php } ?>

                                    <?php
                                    $field_options['mapfield'] = (isset($field_options['mapfield']) && !empty($field_options['mapfield'])) ? $field_options['mapfield'] : 0;
                                    ?>
                                                <input type='hidden' id="arm_map_buddypress_field_<?php echo $form_field_id; ?>" class="arm_map_buddypress_field" value="<?php echo $field_options['mapfield']; ?>" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][mapfield]" />


                                                                            <?php
                                                                            if (in_array($type, array('date'))) {
                                                                                $cal_locales = array(
                                                                                    '' => __('English/Western', 'ARMember'),
                                                                                    'af' => __('Afrikaans', 'ARMember'),
                                                                                    'sq' => __('Albanian', 'ARMember'),
                                                                                    'ar' => __('Arabic', 'ARMember'),
                                                                                    'hy-am' => __('Armenian', 'ARMember'),
                                                                                    'az' => __('Azerbaijani', 'ARMember'),
                                                                                    'eu' => __('Basque', 'ARMember'),
                                                                                    'bs' => __('Bosnian', 'ARMember'),
                                                                                    'bg' => __('Bulgarian', 'ARMember'),
                                                                                    'ca' => __('Catalan', 'ARMember'),
                                                                                    'zh-CN' => __('Chinese Simplified', 'ARMember'),
                                                                                    'zh-TW' => __('Chinese Traditional', 'ARMember'),
                                                                                    'hr' => __('Croatian', 'ARMember'),
                                                                                    'cs' => __('Czech', 'ARMember'),
                                                                                    'da' => __('Danish', 'ARMember'),
                                                                                    'nl' => __('Dutch', 'ARMember'),
                                                                                    'en-GB' => __('English/UK', 'ARMember'),
                                                                                    'eo' => __('Esperanto', 'ARMember'),
                                                                                    'et' => __('Estonian', 'ARMember'),
                                                                                    'fo' => __('Faroese', 'ARMember'),
                                                                                    'fa' => __('Farsi/Persian', 'ARMember'),
                                                                                    'fi' => __('Finnish', 'ARMember'),
                                                                                    'fr' => __('French', 'ARMember'),
                                                                                    'fr-CH' => __('French/Swiss', 'ARMember'),
                                                                                    'de' => __('German', 'ARMember'),
                                                                                    'el' => __('Greek', 'ARMember'),
                                                                                    'he' => __('Hebrew', 'ARMember'),
                                                                                    'hu' => __('Hungarian', 'ARMember'),
                                                                                    'is' => __('Icelandic', 'ARMember'),
                                                                                    'it' => __('Italian', 'ARMember'),
                                                                                    'ja' => __('Japanese', 'ARMember'),
                                                                                    'ko' => __('Korean', 'ARMember'),
                                                                                    'lv' => __('Latvian', 'ARMember'),
                                                                                    'lt' => __('Lithuanian', 'ARMember'),
                                                                                    'nb' => __('Norwegian', 'ARMember'),
                                                                                    'pl' => __('Polish', 'ARMember'),
                                                                                    'pt-BR' => __('Portuguese/Brazilian', 'ARMember'),
                                                                                    'ro' => __('Romanian', 'ARMember'),
                                                                                    'ru' => __('Russian', 'ARMember'),
                                                                                    'sr' => __('Serbian', 'ARMember'),
                                                                                    'sr-SR' => __('Serbian', 'ARMember'),
                                                                                    'sk' => __('Slovak', 'ARMember'),
                                                                                    'sl' => __('Slovenian', 'ARMember'),
                                                                                    'es' => __('Spanish', 'ARMember'),
                                                                                    'sv' => __('Swedish', 'ARMember'),
                                                                                    'ta' => __('Tamil', 'ARMember'),
                                                                                    'th' => __('Thai', 'ARMember'),
                                                                                    'tr' => __('Turkish', 'ARMember'),
                                                                                    'uk' => __('Ukrainian', 'ARMember'),
                                                                                    'vi' => __('Vietnamese', 'ARMember')
                                                                                );
                                                                                ?>
                                                    <div class="arm_form_field_settings_menu_inner">
                                                        <div class="arm_form_field_settings_field_label">
                                                    <?php _e('Calendar Localization', 'ARMember'); ?>
                                                        </div>
                                                        <div class="arm_form_field_settings_field_val">
                                                            <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][cal_localization]" class="arm_form_field_cal_localization_wrapper_value field_cal_localization_text" id="arm_cal_localization" value="<?php echo esc_html($cal_localization); ?>"/>
                                                            <dl class="arm_selectbox column_level_dd" >
                                                                <dt style="border: 1px solid #dbe1e8; width: 197px; height: 22px; border-radius: 5px; padding: 3px 5px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="arm_cal_localization" class="arm_conditional_plans_li" style="height:88px;">
                                                    <?php
                                                    if (!empty($cal_locales)) {
                                                        foreach ($cal_locales as $lan_key => $lan_val) {
                                                            ?><li data-label="<?php echo $lan_val; ?>" data-value="<?php echo $lan_key; ?>"><?php echo $lan_val; ?></li><?php
                                                        }
                                                    }
                                                    ?>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                <?php } ?>


                                                <div class="arm_form_field_settings_menu_inner">
                                                    <div class="arm_form_field_settings_field_label"></div>
                                                    <div class="arm_form_field_settings_field_val">
                                                        <input type="hidden" name="<?php echo $prefix_name; ?>[<?php echo $form_field_id; ?>][ref_field_id]" value="<?php echo $ref_field_id; ?>" class="arm_form_field_ref_field_id arm_form_field_ref_field_<?php echo $ref_field_id; ?>"/>
                                                        <button class="arm_save_btn arm_form_field_settings_field_val_ok_btn" type="button" name="arm_settings_form_addnew_form_btn" field_id='<?php echo $form_field_id; ?>'>
                                                        <?php _e('Ok', 'ARMember'); ?>
                                                        </button>
                                                        <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" class="arm_field_loader_img" style="display:none;" width="24" height="24" /> </div>
                                                </div>
                                    <?php
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="armclear"></div>
                <?php
            }
        }

        function arm_social_profile_field_options_html($form_id = 0, $form_field_id = 0, $field_options = array(), $form_type = 'inactive', $form = '', $extraFields = array()) {
            global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings;
            $socialProfileFields = $this->arm_social_profile_field_types();
            $activeSocialFields = isset($field_options['options']) && !empty($field_options['options']) ? $field_options['options'] : array();
            if (!empty($extraFields)) {
                foreach ($extraFields as $sftype) {
                    if (!in_array($sftype, $activeSocialFields)) {
                        $activeSocialFields[] = $sftype;
                    }
                }
            }
            $field_class = !empty( $field_options['class'] ) ? ' '.$field_options['class'] : '';
            $prefix_name = 'arm_forms[' . $form_id . ']';
            $socialFieldsHtml = '<div class="arm_form_social_profile_fields_wrapper">';
            $selectedSPFOpt = '';
            $selectedSPFOpt .= '<input type="hidden" name="' . $prefix_name . '[' . $form_field_id . '][id]" value="' . $field_options['id'] . '"/><input type="hidden" name="' . $prefix_name . '[' . $form_field_id . '][type]" value="social_fields"/><input type="hidden" name="' . $prefix_name . '[' . $form_field_id . '][label]" value="' . $field_options['label'] . '"/><input type="hidden" name="' . $prefix_name . '[' . $form_field_id . '][meta_key]" value="' . $field_options['meta_key'] . '"/><input type="hidden" class="arm_is_default_field" name="' . $prefix_name . '[' . $form_field_id . '][default_field]" value="0"/>';
            if (!empty($activeSocialFields)) {
                $class = apply_filters('arm_form_field_class', '');
                $class .= ' arm_form_input_box arm_form_input_box_' . $form_field_id . ' ';
                $formSettings = (!empty($form) && !empty($form->settings)) ? $form->settings : array();
                $formStyles = (!empty($form) && isset($formSettings['style']) && !empty($formSettings['style'])) ? $formSettings['style'] : array();
                if (isset($formStyles['form_layout']) && $formStyles['form_layout'] == 'writer') {
                    $class .= ' arm_material_input';
                }
                foreach ($socialProfileFields as $spfKey => $spfLabel) {
                    if (in_array($spfKey, $activeSocialFields)) {
                        $spfMetaKey = 'arm_social_field_' . $spfKey;
                        $spfMetaValue = '';
                        if (isset($formStyles['form_layout']) && $formStyles['form_layout'] != 'writer') {
                            $inputPlaceholder = ' data-placeholder="' . $spfLabel . '"';
                            $inputPlaceholder .= ' placeholder="' . $spfLabel . '"';
                        } else {
                            $inputPlaceholder = '';
                        }
                        if (is_user_logged_in()) {
                            /**
                             * In case of admin edit member page -- `$user_id` will be replaced with requested user id from url.
                             */
                            $user_id = get_current_user_id();
                            $spfMetaValue = get_user_meta($user_id, $spfMetaKey, true);
                        }
                        $selectedSPFOpt .= '<input type="hidden" class="arm_selected_social_profile_fields" name="' . $prefix_name . '[' . $form_field_id . '][options][]" value="' . $spfKey . '"/>';
                        $socialFieldsHtml .= '<div class="arm_form_field_container' . $field_class . ' arm_form_field_container_text" id="arm_form_field_container_' . $form_field_id . '_' . $spfKey . '" data-field_id="' . $form_field_id . '">';
                        $socialFieldsHtml .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_social_fields">';
                        $socialFieldsHtml .= '<div class="arm_member_form_field_label">';
                        $socialFieldsHtml .= '<div class="arm_form_field_label_text">' . $spfLabel . '</div>';
                        $socialFieldsHtml .= '</div>';
                        $socialFieldsHtml .= '</div>';
                        $socialFieldsHtml .= '<div class="arm_label_input_separator"></div>';
                        $socialFieldsHtml .= '<div class="arm_form_input_wrapper">';
                        $socialFieldsHtml .= '<div class="arm_form_input_container_social_fields arm_form_input_container" id="arm_form_input_container_' . $form_field_id . '">';
                        $socialFieldsHtml .= '<md-input-container class="md-block" flex-gt-sm="">';
                        $socialFieldsHtml .= '<label class="arm_material_label">' . $spfLabel . '</label>';
                        $socialFieldsHtml .= '<input name="' . $spfMetaKey . '" type="text" value="' . $spfMetaValue . '" class="' . esc_attr($class) . ' arm_form_input_box_' . $spfKey . ' arm_social_field_input" data-ng-model="arm_form.' . esc_attr($spfMetaKey) . '_' . $form_field_id . '" ' . $inputPlaceholder . '>';
                        $socialFieldsHtml .= '</md-input-container>';
                        $socialFieldsHtml .= '</div>';
                        $socialFieldsHtml .= '</div>';
                        $socialFieldsHtml .= '<div class="armclear"></div>';
                        $socialFieldsHtml .= '</div>';
                    }
                }
            }
            $socialFieldsHtml .= '</div>';
            if ($form_type == 'inactive') {
                $socialFieldsHtml .= $selectedSPFOpt;
                $socialFieldsHtml .= '<div class="arm_form_settings_icon">';
                $socialFieldsHtml .= '<a href="javascript:void(0)" class="arm_form_member_settings_icon armhelptip" title="' . __('Edit Field Options', 'ARMember') . '" data-field_id="' . $form_field_id . '" data-field_type="social_fields">';
                $socialFieldsHtml .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/fe_setting.png" onmouseover="this.src=\'' . MEMBERSHIP_IMAGES_URL . '/fe_setting_hover.png\';" onmouseout="this.src=\'' . MEMBERSHIP_IMAGES_URL . '/fe_setting.png\';" style="cursor:pointer;"/>';
                $socialFieldsHtml .= '</a>';
                $socialFieldsHtml .= '<a href="javascript:void(0)" class="arm_form_member_delete_icon armhelptip" title="' . __('Delete Field', 'ARMember') . '" data-field_id="' . $form_field_id . '" data-field_type="social_fields" onclick="showConfirmBoxCallback(' . $form_field_id . ');">';
                $socialFieldsHtml .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/fe_delete.png" onmouseover="this.src=\'' . MEMBERSHIP_IMAGES_URL . '/fe_delete_hover.png\';" onmouseout="this.src=\'' . MEMBERSHIP_IMAGES_URL . '/fe_delete.png\';" style="cursor:pointer;"/>';
                $socialFieldsHtml .= '</a>';
                $socialFieldsHtml .= '</div>';
                $socialFieldsHtml .= $arm_global_settings->arm_get_confirm_box($form_field_id, __("Are you sure you want to delete this field?", 'ARMember'), 'arm_field_delete_ok_btn', 'social_fields');
            }
            return $socialFieldsHtml;
        }

        function arm_generate_field_fa_icon($field_id = 0, $id = '', $type = '', $color = '') {
            if (empty($id) || $id == 'undefined') {
                return '';
            }
            $icon = "";
            $iconStyle = "";
            if (!empty($color)) {
                $iconStyle = "color:" . $color;
            }
            if ($type == 'prefix') {
                $icon .= '<span class="arm_editor_prefix arm_field_fa_icons" id="arm_editor_prefix_' . $field_id . '" style="' . $iconStyle . '"><i class="armfa ' . $id . '"></i></span>';
            } elseif ($type == 'suffix') {
                $icon .= '<span class="arm_editor_suffix arm_field_fa_icons" id="arm_editor_suffix_' . $field_id . '" style="' . $iconStyle . '"><i class="armfa ' . $id . '"></i></span>';
            }
            return $icon;
        }

        function arm_member_form_get_fields_by_type($field_options, $field_id = 0, $form_id = 0, $form_type = 'inactive', $form = '', $formRandomID = '') {
            global $wp, $wpdb, $arm_slugs, $current_user, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $value = $field_options;
            $meta_key = $value['meta_key'];
            $ffield_type = $value['type'];
            $name = "no_field";

            $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
            
            if ($form_type == 'active') {
                $name = (!empty($meta_key)) ? $meta_key : $value['id'];
                if (!empty($meta_key) && isset($_REQUEST[$meta_key]) && !empty($_REQUEST[$meta_key])) {
                    $value['value'] = $_REQUEST[$meta_key];
                }
            }
            $ng_model = 'data-ng-model="arm_form.' . esc_attr($name) . '_' . $field_id . '"';
            $value['id'] = "arm_" . $value['id'] . "_" . $form_id;
            $class = apply_filters('arm_form_field_class', '');
            $class .= ' arm_form_input_box_' . $field_id . ' ';
            $class .= ' arm_form_input_box arm_cl_'.$meta_key.'_'.$formRandomID.' ';
            $value['label'] = !empty($value['label']) ? stripslashes($value['label']) : '';
            $value['placeholder'] = !empty($value['placeholder']) ? stripslashes($value['placeholder']) : '';
            $ffield_label = (!empty($value['placeholder'])) ? $value['placeholder'] : '';
            $placeholder = isset($value['placeholder']) ? ' placeholder="' . esc_attr($value['placeholder']) . '"' : '';
            $formSettings = (!empty($form) && !empty($form->settings)) ? $form->settings : array();
            $formStyles = (!empty($form) && isset($formSettings['style']) && !empty($formSettings['style'])) ? $formSettings['style'] : array();
            $suffix_eye_icon_cls = "arm_visible_password";
            if (isset($formStyles['form_layout']) && $formStyles['form_layout'] == 'writer') {
                $placeholder = '';
                $ffield_label = $value['label'];
                $class = ' arm_form_input_box arm_form_input_box_' . $field_id . ' arm_material_input arm_cl_'.$meta_key.'_'.$formRandomID;
                $suffix_eye_icon_cls = " arm_visible_password_material ";
            }
            $validate_msgs = '';

            $required_star = (!empty($value['required'])) ? ' required="required" ' : "";
            if(in_array($ffield_type, array('repeat_email'))){
                $required_star = ' required="required" ';
            }

            $required = (!empty($value['required'])) ? ' required="required" ' : "";
            if (!empty($value['hide_username']) && $value['hide_username'] == 1) {
                $required = '';
            }
            if (!empty($value['hide_firstname']) && $value['hide_firstname'] == 1) {
                $required = '';
            }
            if (!empty($value['hide_lastname']) && $value['hide_lastname'] == 1) {
                $required = '';
            }

            if(in_array($ffield_type, array('repeat_email'))){
                $required = 'data-ng-required="arm_form.user_pass_'.$value['ref_field_id'].' != NULL"';
            }

            $disabled = (!empty($value['disabled'])) ? ' disabled="disabled"" ' : "";
            $readonly = '';
            $ngreadonly = '';
            if( !is_admin() ){
                $readonly = (!empty($value['readonly']) && 1 == $value['readonly'] ) ? ' readonly="readonly"' : '';
                $ngreadonly = (!empty($value['readonly']) && 1 == $value['readonly'] ) ? ' ng-disabled="true" ' : '';
            }
            $blank_message = (!empty($value['blank_message'])) ? ' data-msg-required="' . stripcslashes($value['blank_message']) . '" ' : "";
            $invalid_username = (!empty($value['invalid_username'])) ? $value['invalid_username'] : "";
            $invalid_firstname = (!empty($value['invalid_firstname'])) ? $value['invalid_firstname'] : "";
            $invalid_lastname = (!empty($value['invalid_lastname'])) ? $value['invalid_lastname'] : "";
            $validation_type = (!empty($value['validation_type'])) ? $value['validation_type'] : "custom_validation_none";
            $regular_expression = (!empty($value['regular_expression'])) ? $value['regular_expression'] : "";
            $invalid_message = (!empty($value['invalid_message'])) ? ' data-msg-invalid="' . stripcslashes($value['invalid_message']) . '" ' : "";
            $validation_data = $required . $blank_message . $invalid_message;
            $validation_data .= (!empty($value['options']['minlength'])) ? ' minlength="' . ((int) $value['options']['minlength']) . '"' : '';
            $validation_data .= (!empty($value['options']['maxlength'])) ? ' maxlength="' . ((int) $value['options']['maxlength']) . '"' : '';
            if ($form_type != 'active') {
                $validation_data = $validate_msgs = $required = '';
            }
            $onchange = (!empty($value['onchange'])) ? 'onchange="' . $value['onchange'] . '"' : '';
            $onchange = apply_filters( 'arm_form_field_onchange', $formRandomID, $meta_key, $ffield_type, $onchange );
            /* Set Value Variable */
            $field_desc = (isset($value['description'])) ? $value['description'] : '';
            $field_val = (isset($value['value'])) ? $value['value'] : '';
            $prefix_icon = (!empty($value['prefix'])) ? $this->arm_generate_field_fa_icon($field_id, $value['prefix'], 'prefix') : '';
            $suffix_icon = (!empty($value['suffix'])) ? $this->arm_generate_field_fa_icon($field_id, $value['suffix'], 'suffix') : '';

            //$visible_pass_cls = ($suffix_icon != "") ? " arm_right_space_visible_pass " : "";
            $visible_pass_cls = ($suffix_icon != "") ? " " : "";
            
            $suffix_eye_icon = '<span class="arm_editor_suffix arm_field_fa_icons '.$suffix_eye_icon_cls . $visible_pass_cls.'" id="" style=""><i class="armfa armfa-eye"></i></span>';
            
            $class .= (!empty($prefix_icon) || !empty($suffix_icon) || !empty($suffix_eye_icon)) ? ' arm_prefix_suffix_icon' : '';
            $class .= (!empty($suffix_icon) || !empty($suffix_eye_icon)) ? ' arm_has_suffix_icon' : '';
            $class .= (!empty($prefix_icon)) ? ' arm_has_prefix_icon' : '';

            $return_html = $output = $psm = '';
            $field_attr = $ng_model . ' ' . $placeholder . ' ' . $required . ' ' . $disabled . ' ' .$readonly;
            if (!empty($value['blank_message'])) {
                $validate_msgs .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['blank_message']) . '</div>';
            }
            if (!empty($value['invalid_message'])) {
                $validate_msgs .= '<div data-ng-message="invalid" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
            }
            if (!empty($value['options']['minlength'])) {
                $field_attr .= ' data-ng-minlength="' . ((int) $value['options']['minlength']) . '"';
                $minlength_invalid_message = (isset($common_messages['arm_minlength_invalid']) && $common_messages['arm_minlength_invalid'] != '') ? str_replace('[MINVALUE]', ((int) $value['options']['minlength']), $common_messages['arm_minlength_invalid']) : __('Please enter at least', 'ARMember') . " " . ((int) $value['options']['minlength']) .' '. __('characters.', 'ARMember');
                $validate_msgs .= '<div data-ng-message="minlength" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $minlength_invalid_message . '</div>';
            }
            if (!empty($value['options']['maxlength'])) {
                $field_attr .= ' data-ng-maxlength="' . ((int) $value['options']['maxlength']) . '"';
                $maxlength_invalid_message = (isset($common_messages['arm_maxlength_invalid']) && $common_messages['arm_maxlength_invalid'] != '') ? str_replace('[MAXVALUE]', ((int) $value['options']['maxlength']), $common_messages['arm_maxlength_invalid']) : __('Maximum', 'ARMember') . " " . ((int) $value['options']['minlength']) .' '. __('characters allowed.', 'ARMember');
                $validate_msgs .= '<div data-ng-message="maxlength" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $maxlength_invalid_message . '</div>';
            }
            if( !empty($ffield_label) ) {
                if( $required_star != '') {
                    $ffield_label = '<label class="arm_material_label"> * ' . html_entity_decode(stripslashes($ffield_label)) . '</label>';
                } else {
                    $ffield_label = '<label class="arm_material_label"> ' . html_entity_decode(stripslashes($ffield_label)) . '</label>';
                }
            }
            $field_class = isset( $field_options['class'] ) ? $field_options['class'] : '';
            switch ($ffield_type) {
                /* Text Field */
                case 'text':
                case 'repeat_email':
                case 'email':
                case 'url':
                    $field_attr .= ' data-ng-trim="false"';
                    if ($ffield_type == 'text' && $validation_type != 'custom_validation_none' && $validation_type != 'customvalidationregex') {
                        $class .= " " . $validation_type;
                        $validate_msgs .= '<div data-ng-message="' . $validation_type . '" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
                    }
                    if ($ffield_type == 'text' && $validation_type == 'customvalidationregex' && !empty($regular_expression)) {
                        $field_attr .= ' data-ng-pattern="' . $regular_expression . '"';
                        $validate_msgs .= '<div data-ng-message-exp="[\'' . $ffield_type . '\', \'pattern\']" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
                    }
                    if ($ffield_type == 'email' || $ffield_type == 'repeat_email') {
                        $field_attr .= ' data-ng-pattern="/^.+@.+\..+$/"';
                        $validate_msgs .= '<div data-ng-message-exp="[\'email\', \'pattern\']" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
                        if ($ffield_type == 'repeat_email') {
                            $refFieldID = (isset($value['ref_field_id']) && $value['ref_field_id'] != 0) ? $value['ref_field_id'] : 0;
                            if (isset($value['ref_field_id']) && $value['ref_field_id'] != 0) {
                                $psm = '';
                                $class .= ' armRepeatEmailInput ';
                                $field_attr .= ' data-compare="arm_compare_' . $value['ref_field_id'] . '"';
                                $invalid_message = (!empty($value['invalid_message'])) ? stripcslashes($value['invalid_message']) : __('Please enter email address again.', 'ARMember');
                                $validate_msgs .= ' <div data-ng-message="compare" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $invalid_message . '</div>';
                            }
                        }
                        $ffield_type = 'email';
                    }
                    if ($ffield_type == 'url') {
                        $validate_msgs .= '<div data-ng-message="url" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
                    }
                    if ($form_type == 'active' && !empty($form) && $form->type == 'registration') {
                        if (in_array($name, array('first_name', 'last_name'))) {
                            $class .= " flnamecheck";
                            $namecheck_msg = '';
                            if ($name == 'first_name') {
                                $namecheck_msg = $invalid_firstname;
                            }
                            if ($name == 'last_name') {
                                $namecheck_msg = $invalid_lastname;
                            }
                            $validate_msgs .= '<div data-ng-message="flnamecheck" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($namecheck_msg) . '</div>';
                        }
                        if ($name == 'user_login') {
                            $class .= " usernamecheck existcheck";
                            $exist_msg = isset($arm_global_settings->common_message['arm_username_exist']) ? $arm_global_settings->common_message['arm_username_exist'] : '';
                            $exist_msg = (!empty($exist_msg)) ? $exist_msg : __('This username is already registered, please choose another one.', 'ARMember');
                            if (is_multisite()) {
                                $class .= " arm_multisite_validate ";
                            }
                            $validate_msgs .= '<div data-ng-message="existcheck" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($exist_msg) . '</div>';
                            $validate_msgs .= '<div data-ng-message="usernamecheck" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($invalid_username) . '</div>';
                        }
                        if ($name == 'user_email') {
                            $class .= " existcheck";
                            $exist_msg = isset($arm_global_settings->common_message['arm_email_exist']) ? $arm_global_settings->common_message['arm_email_exist'] : '';
                            $exist_msg = (!empty($exist_msg)) ? $exist_msg : __('This email is already registered, please choose another one.', 'ARMember');
                            $validate_msgs .= '<div data-ng-message="existcheck" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($exist_msg) . '</div>';
                        }
                    }
                    $output .= '<md-input-container class="md-block" flex-gt-sm="">';
                    $output .= $prefix_icon;
                    $output .= $ffield_label;
                    $output .= '<input name="' . $name . '" type="' . $ffield_type . '" value="' . esc_attr($field_val) . '" class="' . esc_attr($class) . '" ' . $field_attr . ' '.$onchange.'>';
                    $output .= $suffix_icon;
                    if ($form_type == 'active') {
                        $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-cloak">';
                        $output .= $validate_msgs;
                        $output .= '</div>';
                    }
                    $output .= '</md-input-container>';
                    if ($ffield_type == 'email' && $form_type == 'active') {
                        $output .= '<input type="hidden" data-id="arm_compare_' . $field_id . '" class="arm_compare_' . $field_id . '" ng-model="arm_form.arm_compare_' . $field_id . '" value="{{ arm_form.' . esc_attr($name) . '_' . $field_id . ' }}">';
                    }
                    break;
                /* Password */
                case 'repeat_pass':
                case 'password':
                    $pass_attr = '';
                    $options = $value['options'];
                    if (!empty($options) && $form_type == 'active') {
                        if (isset($options['strong_password']) && $options['strong_password'] == '1') {
                            $pass_attr .= ' armstrongpassword="1"';
                            $validate_char = array('lowercase' => __('lowercase', 'ARMember'),
                                'uppercase' => __('uppercase', 'ARMember'),
                                'numeric' => __('numeric', 'ARMember'),
                                'special' => __('special', 'ARMember')
                            );
                            foreach ($validate_char as $v => $v_lbl) {
                                if (isset($options[$v]) && $options[$v] == '1') {
                                    $pass_attr .= ' arm' . $v . '="1"';
                                    $validate_msgs .= ' <div data-ng-message="arm' . $v . '" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please use atleast one', 'ARMember') . ' ' . $v_lbl . ' ' . __('character.', 'ARMember') . '</div>';
                                }
                            }
                        }
                        if (!is_admin()) {
                            if (isset($options['strength_meter']) && $options['strength_meter'] == '1') {
                                $veryweaktext= (!empty($options['veryweaktext'])) ? stripslashes($options['veryweaktext']) : __('Strength: Very Weak', 'ARMember');
                                $weaktext = (!empty($options['weaktext'])) ? stripslashes($options['weaktext']) : __('Strength: Weak', 'ARMember');
                                $goodtext = (!empty($options['goodtext'])) ? stripslashes($options['goodtext']) : __('Strength: Good', 'ARMember');
                                $strongtext = (!empty($options['strongtext'])) ? stripslashes($options['strongtext']) : __('Strength: Strong', 'ARMember');
                                  
                                $class .= ' arm_strength_meter_input';
                                $psm .= '<div class="arm_pass_strength_meter">';
                                $psm .= '<ul class="arm_strength_meter_block_container" check-strength="arm_form.' . esc_attr($name) . '_' . $field_id . '" field-name="' . esc_attr($name) . '" data-field-veryweak = "'.$veryweaktext.'" data-field-weak = "'.$weaktext.'" data-field-good = "'.$goodtext.'" data-field-strong = "'.$strongtext.'" ></ul>';                                                            
                                $psm .= '<span class="arm_strength_meter_label">' . $veryweaktext . '</span>'; 
                                $psm .= '<div class="armclear"></div>';
                                $psm .= '</div>';
                            }
                        }
                    }
                    if ($ffield_type == 'repeat_pass' && $form_type == 'active') {
                        $refFieldID = (isset($value['ref_field_id']) && $value['ref_field_id'] != 0) ? $value['ref_field_id'] : 0;
                        if (isset($value['ref_field_id']) && $value['ref_field_id'] != 0) {
                            $psm = '';
                            $class .= ' armRepeatPasswordInput ';
                            $pass_attr = ' data-compare="arm_compare_' . $value['ref_field_id'] . '"';
                            $invalid_message = (!empty($value['invalid_message'])) ? stripcslashes($value['invalid_message']) : __('Passwords don\'t match.', 'ARMember');
                            $validate_msgs .= ' <div data-ng-message="compare" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $invalid_message . '</div>';
                        }
                    }

                    $output .= '<md-input-container class="md-block" flex-gt-sm="">';
                    $output .= $prefix_icon;
                    $output .= $ffield_label;
                    $output .= '<input name="' . $name . '" type="password" autocomplete="off" value="' . esc_attr($field_val) . '" class=" ' . esc_attr($class) . '" ' . $field_attr . ' ' . $pass_attr . '>';

                    $output .= $suffix_eye_icon;
                    //$output .= $suffix_icon;
                    if ($form_type == 'active') {
                        $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                        $output .= $validate_msgs;
                        $output .= '</div>';
                    }
                    $output .= '</md-input-container>';
                    if ($ffield_type != 'repeat_pass' && $form_type == 'active') {
                        $output .= '<input type="hidden" data-id="arm_compare_' . $field_id . '" class="arm_compare_' . $field_id . '" ng-model="arm_form.arm_compare_' . $field_id . '" value="{{ arm_form.' . esc_attr($name) . '_' . $field_id . ' }}">';
                    }
                    break;
                /* Date Field */
                case 'date':
                    $formDateFormat = 'd/m/Y';
                    $dateFormatTypes = array(
                        'm/d/Y' => 'MM/DD/YYYY',
                        'd/m/Y' => 'DD/MM/YYYY',
                        'Y/m/d' => 'YYYY/MM/DD',
                        'M d, Y' => 'MMM DD, YYYY',
                        'd M, Y' => 'DD MMM, YYYY',
                        'Y, M d' => 'YYYY, MMM DD',
                        'F d, Y' => 'MMMM DD, YYYY',
                        'd F, Y' => 'DD MMMM, YYYY',
                        'Y, F d' => 'YYYY, MMMM DD',
                        'Y-m-d'  => 'YYYY-MM-DD'
                    );

                   
                    $showTimePicker = '0';
                    if (!empty($form) && !empty($formSettings['date_format'])) {
                        $formDateFormat = $formSettings['date_format'];
                    }
                    $dateFormat = $dateFormatTypes[$formDateFormat];
                    if (!empty($form) && !empty($formSettings['show_time'])) {
                        $showTimePicker = $formSettings['show_time'];
                    }
                    /*
                    if (!empty($field_val)) {
                        if(!empty($form) && !empty($formSettings['show_time'])) {
                            $formDateFormat .= ' h:i A';
                        }
                        if(preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $field_val, $match)) {
                            try {
                                $date = new DateTime($field_val);
                            } catch (Exception $e) {
                                $date1_ = str_replace('/', '-', $field_val);
                                $date = new DateTime($date1_);
                            }
                            $field_val = $date->format($formDateFormat);
                        } else {
                            $field_val = date($formDateFormat, strtotime($field_val));
                        }
                    }
                    */
                    $calLocalization = '';
                    if (!empty($form) && isset($value['cal_localization'])) {
                        $calLocalization = $value['cal_localization'];
                    }
                    $output .= '<md-input-container class="md-block arm_date_field_' . $form_id . '" flex-gt-sm="">';
                    $output .= $prefix_icon;
                    $output .= $ffield_label;
                    if ($form_type == 'active') {
                        $class .= ' arm_datepicker arm_datepicker_front ';
                    }
                    $output .= '<input name="' . $name . '" type="text" autocomplete="off" value="' . esc_attr($field_val) . '" class="' . esc_attr($class) . '" ' . $field_attr . ' data-dateformat="' . $dateFormat . '" data-date_field="arm_date_field_' . $form_id . '" data-show_timepicker="' . $showTimePicker . '" data-cal_localization="' . $calLocalization . '" '.$onchange.'>';
                    $output .= $suffix_icon;
                    if ($form_type == 'active') {
                        $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                        $output .= $validate_msgs;
                        $output .= '</div>';
                    }
                    $output .= '</md-input-container>';
                    global $arm_datepicker_loaded;
                    $arm_datepicker_loaded = 1;

                    break;
                /* File Upload Field */
                case 'file':
                case 'avatar':
                case 'profile_cover':
                    global $arm_file_upload_field;
                    $arm_file_upload_field = 1;
                    $accept = (!empty($value['allow_ext'])) ? 'accept="' . $value['allow_ext'] . '"' : '';
                    if ($ffield_type == 'avatar') {
                        $accept = 'accept=".jpg,.jpeg,.png,.bmp,.ico"';
                    }
                    $file_size_limit = (!empty($value['file_size_limit'])) ? (int) $value['file_size_limit'] : 2;
                    $file_size_limit = (!empty($file_size_limit)) ? $file_size_limit : 2;
                    $display_file = !empty($field_val) && file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($field_val)) ? true : false;
                    $file_name = $fileUrl = '';
                    if ($display_file) {
                        $file_name = basename($field_val);
                        if ($field_val != '') {
                            $exp_val = explode("/", $field_val);
                            $filename = $exp_val[count($exp_val) - 1];
                            $file_extension = explode('.', $filename);
                            $file_ext = $file_extension[count($file_extension) - 1];
                            if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF'))) {
                                $fileUrl = $field_val;
                            } else {
                                $fileUrl = MEMBERSHIP_IMAGES_URL . '/file_icon.png';
                            }
                        }
                    } else {
                        $field_val = '';
                    }
                    $uploaderRandomID = $field_id . $form_id . arm_generate_random_code();
                    $file_placeholder = (isset($value['placeholder']) && !empty($value['placeholder'])) ? $value['placeholder'] : __('Drop file here or click to select.', 'ARMember');
                    $output = $ffield_label;

                    $output .= '<div class="armFileUploadWrapper file-field input-field" data-iframe="' . esc_attr($value['id']) . $uploaderRandomID . '">';
                    $browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);
                    $inputType = 'type="file"';

                    $browser_check = 1;
                    $isIE = false;
                    if (isset($browser_info) and $browser_info != "") {
                        if ($browser_info['name'] == 'Internet Explorer' || $browser_info['name'] == 'Apple Safari') {
                            if ($browser_info['name'] == 'Apple Safari') {
                                $class .= ' armSafariFileUpload';
                                $browser_check = 0;
                            } elseif ($browser_info['name'] == 'Internet Explorer' && $browser_info['version'] <= '9') {
                                $isIE = true;
                                $browser_check = 0;
                                $inputType = 'type="text" data-iframe="' . esc_attr($value['id']) . $uploaderRandomID . '"';
                                $class .= ' armIEFileUpload';
                                $output .= '<div id="' . esc_attr($value['id']) . $uploaderRandomID . '_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="' . esc_attr($value['id']) . $uploaderRandomID . '_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                            }
                        }
                    }
                    if (is_admin() && isset($_GET['page']) && in_array($_GET['page'], array($arm_slugs->manage_members))) {
                        $arm_avatar_type = '';

                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                        $general_settings = $all_global_settings['general_settings'];
                        $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

                        if($enable_crop){
                            if ($value['meta_key'] == 'profile_cover') {
                                $arm_avatar_type = ' data-avatar-type="cover"  data-update-meta="no"  ';


                                $output .='<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="'.$formRandomID.'">';
                                $output .='<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                                $output .='<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="'.$formRandomID.'"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="'.$formRandomID.'"  data-rotate="0" /></div>';
                                $output .='<div class="arm_skip_cvr_crop_button_wrapper_admn">';
                                $output .='<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                                $output .='<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                                $output .='<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                                $output .='<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                                $output .='<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                                $output .='<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                                $output .='<button id="arm_skip_cvr_crop_nav_admn" data_id="'.$formRandomID.'" class="arm_cvr_done_front">' . __('Done', 'ARMember') . '</button>';
                                $output .='</div>';

                                $output .='<p class="arm_discription">' . __('(Use Cropper to set image and use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                                $output .='</div>';
                            } else if ($value['meta_key'] == 'avatar') {
                                $arm_avatar_type = ' data-avatar-type="profile"  data-update-meta="no"  ';

                                $output .='<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="'.$formRandomID.'">';
                                $output .='<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                                $output .='<div id="arm_crop_div" class="arm_crop_div" data_id="'.$formRandomID.'"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="'.$formRandomID.'"  data-rotate="0" /></div>';
                                $output .='<div class="arm_skip_avtr_crop_button_wrapper_admn">';
                                $output .='<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                                $output .='<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                                $output .='<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="'.$formRandomID.'" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                                $output .='<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="'.$formRandomID.'" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                                $output .='<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                                $output .='<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="'.$formRandomID.'" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                                $output .='<button id="arm_skip_avtr_crop_nav_admn" class="arm_avtr_done_front" data_id="'.$formRandomID.'">' . __('Done', 'ARMember') . '</button>';
                                $output .='</div>';
                                $output .='<p class="arm_discription">' . __('(Use Cropper to set image and <br/>use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                                $output .='</div>';
                            }
                        }
                        /**
                         * For Admin Side Only
                         */
                        $output .= '<div class="armFileUploadContainer" style="' . (($display_file) ? 'display:none;' : '') . '">';
                        $output .= '<div class="armFileUpload-icon"></div>' . __('Upload', 'ARMember');
                        $output .= '<input id="' . esc_attr($value['id']) . $uploaderRandomID . '" ' . $accept . ' class="armFileUpload ' . esc_attr($class) . '" name="' . esc_attr($name) . '" ' . $inputType . ' ' . $onchange . ' value="' . $field_val . '" data-file_size="' . $file_size_limit . '"  ' . $arm_avatar_type . '/>';
                        $output .= '</div>';
                        if ($display_file) {
                            if (preg_match("@^http@", $field_val)) {
                                $temp_data = explode("://", $field_val);
                                $field_val = '//' . $temp_data[1];
                            }

                            if (file_exists(strstr($fileUrl, "//"))) {
                                $fileUrl = strstr($fileUrl, "//");
                            } else if (file_exists($fileUrl)) {
                                $fileUrl = $fileUrl;
                            } else {
                                $fileUrl = $fileUrl;
                            }

                            $output .= '<div class="arm_old_uploaded_file"><a href="' . $field_val . '" target="__blank"><img alt="" src="' . ($fileUrl) . '" width="100px"/></a></div>';
                        }


                        $output .= '<div class="armFileRemoveContainer" style="' . (($display_file) ? 'display: inline-block;' : '') . '"><div class="armFileRemove-icon"></div>' . __('Remove', 'ARMember') . '</div>';
                        $output .= '<div class="armFileUploadProgressBar" style="display: none;"><div class="armbar" style="width:0%;"></div></div>';
                        $output .= '<div class="armFileUploadProgressInfo"></div>';
                        $output .= '<div class="armFileMessages" id="armFileUploadMsg_' . esc_attr($value['id']) . $uploaderRandomID . '"></div>';
                        $output .= '<input class="arm_file_url" type="hidden" name="' . esc_attr($name) . '" value="' . $field_val . '" ' . $validation_data . ' ' . $arm_avatar_type . '>';
                    } else {
                        $output .= '<md-input-container class="md-block" flex-gt-sm="">';
                        $output .= '<div class="armNormalFileUpload">';
                        if ($browser_check != 1) {
                            $arm_avatar_type = '';
                            if ($value['meta_key'] == 'profile_cover') {
                                $arm_avatar_type = ' data-avatar-type="cover"  data-update-meta="no" ';
                            } elseif ($value['meta_key'] == 'avatar') {
                                $arm_avatar_type = ' data-avatar-type="profile"  data-update-meta="no" ';
                            }
                            $output .= '<div class="armFileUploadContainer" for="' . esc_attr($value['id']) . $uploaderRandomID . '" style="' . (($display_file) ? 'display:none;' : '') . '">';
                            $output .= '<div class="armFileUpload-icon"></div>' . __('Upload', 'ARMember');
                            $output .= '<input armfileuploader id="' . esc_attr($value['id']) . $uploaderRandomID . '" ' . $accept . ' class="armFileUpload ' . esc_attr($class) . '" ' . $inputType . ' name="' . esc_attr($name) . '" data-ng-model="arm_form.' . esc_attr($name) . '_' . $form_id . '" ' . $validation_data . ' ' . $onchange . ' value="' . $field_val . '" data-file_size="' . $file_size_limit . '" aria-label="' . $value['label'] . '" ' . $arm_avatar_type . '/>';
                            $output .= '</div>';
                            $output .= '<div class="armFileRemoveContainer" style="' . (($display_file) ? 'display: inline-block; ' : '') . '"><div class="armFileRemove-icon"></div></div>';
                            if ($display_file) {
                                $output .= '<div class="arm_old_file"><img alt="" src="' . $fileUrl . '" width="100px"/></div>';
                            }
                            $output .= '<div class="armFileUploadProgressBar" style="display: none;"><div class="armbar" style="width:0%;"></div></div>';
                            $output .= '<div class="armFileUploadProgressInfo"></div>';
                            $output .= '<div class="armclear"></div>';
                        } else {

                            $arm_avatar_type = '';
                            if ($value['meta_key'] == 'profile_cover') {
                                $arm_avatar_type = ' data-avatar-type="cover" data-update-meta="no" ';
                                global $arm_avatar_loaded, $bpopup_loaded;
                                $arm_avatar_loaded = 1;
                                $bpopup_loaded = 1;
                            } elseif ($value['meta_key'] == 'avatar') {
                                global $arm_avatar_loaded, $bpopup_loaded;
                                $arm_avatar_loaded = 1;
                                $bpopup_loaded = 1;
                                $arm_avatar_type = ' data-avatar-type="profile"  data-update-meta="no" ';
                            }
                            $output .= '<div class="armFileDragArea">';
                            $output .= '<div class="arm_old_file arm_field_file_display">';
                            if ($display_file) {
                                $output .= '<div class="arm_uploaded_file_info"><img alt="" src="' . $fileUrl . '"/></div>';
                                $output .= '<div class="armFileRemoveContainer">x</div>';
                            }
                            $output .= '</div>';
                            $output .= '<div class="armbar" style="width:0%;"></div>';
                            $output .= '<label class="armFileDragAreaText" for="' . esc_attr($value['id']) . $uploaderRandomID . '" style="' . (($display_file) ? 'display:none;' : '') . '">';
                            $output .= '<div class="armFileUploaderWrapper armFileUploaderPlaceholder" id="armFileUploaderWrapper' . $uploaderRandomID . '" data-id="' . esc_attr($value['id']) . $uploaderRandomID . '">' . $file_placeholder . '</div>';
                            $output .= '</label>';
                            $output .= '<input armfileuploader id="' . esc_attr($value['id']) . $uploaderRandomID . '" ' . $accept . ' class="armFileUpload ' . esc_attr($class) . '" ' . $inputType . ' name="' . esc_attr($name) . '" data-ng-model="arm_form.' . esc_attr($name) . '_' . $form_id . '" ' . $validation_data . ' ' . $onchange . ' value="' . $field_val . '" data-file_size="' . $file_size_limit . '" aria-label="' . $value['label'] . '"  ' . $arm_avatar_type . ' data-form-id="'.$formRandomID.'"/>';
                            $output .= '</div>';
                            $output .= '<div class="armclear"></div>';
                        }
                        $output .= '</div>';
                        if ($form_type == 'active') {
                            $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-cloak">';
                            $output .= $validate_msgs;
                            $output .= '<div data-ng-message="accept" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . stripcslashes($value['invalid_message']) . '</div>';
                            $output .= '</div>';
                            $output .= '<div class="armFileMessages arm_error_msg_box" id="armFileUploadMsg_' . esc_attr($value['id']) . $uploaderRandomID . '"></div>';
                        }
                        $output .= '</md-input-container>';
                        $output .= '<input class="arm_file_url" type="hidden" name="' . esc_attr($name) . '" value="' . $field_val . '" tabindex="-1">';
                    }
                    $output .= '</div>';
                    break;
                /* Textarea */
                case 'textarea':
                    $rows = '5';
                    $cols = '40';
                    if (isset($value['settings']['rows'])) {
                        $custom_rows = $value['settings']['rows'];
                        if (is_numeric($custom_rows)) {
                            $rows = $custom_rows;
                        }
                    }
                    $output .= '<md-input-container class="md-block" flex-gt-sm="">';
                    $output .= $ffield_label;
                    $output .= '<textarea class="arm_textarea ' . esc_attr($class) . '" name="' . esc_attr($name) . '" rows="' . $rows . '" cols="' . $cols . '" ' . $field_attr . ' data-ng-init="arm_form.' . esc_attr($name) . '_' . $field_id . '=\'' . esc_attr(addslashes($field_val)) . '\'" '.$onchange.'>' . stripslashes($field_val) . '</textarea>';
                    if ($form_type == 'active') {
                        $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                        $output .= $validate_msgs;
                        $output .= '</div>';
                    }
                    $output .= '</md-input-container>';
                    break;
                /* Select Box */
                case 'select':
                    if (empty($field_val) && !empty($value['default_val'])) {
                        $field_val = $value['default_val'];
                    }
                    if (is_admin() && isset($_GET['page']) && in_array($_GET['page'], array($arm_slugs->manage_members))) {
                        /**
                         * For Admin Side Only
                         */
                        $field_options = "";
                        if (!empty($value['options'])) {
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $new_data = explode(':', $data);
                                $option = $key = isset($new_data[0]) ? $new_data[0] : $data;
                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                if ($value['meta_key'] == "country" && isset($new_data[1]) && !empty($field_val) && !is_numeric($field_val)) {
                                    if ($field_val == $new_data[0]) {
                                        $field_val = $new_data[1];
                                    }
                                }
                                $field_options .= '<li data-label="' . esc_html($option) . '" data-value="' . esc_attr($key) . '">' . esc_html($option) . '</li>';
                            }
                        } else {
                            $field_options .= '<li data-label="' . __('Choose your option', 'ARMember') . '" data-value="">' . __('Choose your option', 'ARMember') . '</li>';
                        }

                        $output .= '<input type="hidden" id="' . esc_attr($value['id']) . '" name="' . esc_attr($name) . '" value="' . $field_val . '" ' . $validation_data . ' ' . $onchange . '/>';
                        $output .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">';
                        $output .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $output .= '<dd><ul data-id="' . esc_attr($value['id']) . '">';
                        $output .= $field_options;
                        $output .= '';
                        $output .= '</ul></dd>';
                        $output .= '</dl>';
                    } else {
                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                        $general_settings = $all_global_settings['general_settings'];
                        $ng_change_func = "";

                        if(isset($general_settings['enable_tax']) && isset($general_settings["country_tax_field"]) && $general_settings['enable_tax'] == 1 && $name == $general_settings["country_tax_field"] ) {
                            $ng_change_func = "ng-change = armSetTax('arm_setup_form".$formRandomID."')";
                        }
                        $output .= '<md-input-container class="md-block" flex-gt-sm="">';
                        if($required_star != '' && $formStyles['form_layout'] == 'writer'){ 
                            $output .= '<label class="arm_material_label"> * ' . $value['label'] . '</label>';
                        } else {
                            $output .= '<label class="arm_material_label">' . $value['label'] . '</label>';  
                        }
                        $ngModelSelect = esc_attr($name);
                        if ($form_type != 'active') {
                            $ngModelSelect = 'default_val_' . $field_id;
                        }
                        $field_attr = 'name="' . esc_attr($name) . '" data-ng-model="' . esc_attr($ngModelSelect) . '" ' . $placeholder . ' ' . $required . ' ' . $disabled;
                        $field_attr .= ' aria-label="' . $value['label'] . '"';
                        

                        if( isset( $field_options['readonly'] ) && 1 == $field_options['readonly']  ){
                            $field_attr .= ' ng-disabled="true" ';
                        }
                        $output .= '<md-select class="' . esc_attr($class) . '" ' . $field_attr . $ng_change_func .' '.$onchange.'>';
                        $writter_class = (!empty($form) && isset($formStyles['rtl']) && $formStyles['rtl'] == '1') ? 'armSelectOptionRTL' : 'armSelectOptionLTR';
                        if (!empty($value['options'])) {
                            $allOptions = array();
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $new_data = explode(':', $data);
                                $option = $key = isset($new_data[0]) ? $new_data[0] : $data;
                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                if ($value['meta_key'] == "country" && isset($new_data[1]) && !empty($field_val) && !is_numeric($field_val)) {
                                    if ($field_val == $new_data[0]) {
                                        $field_val = $new_data[1];
                                    }
                                }
                                $selected_val = (strtolower($field_val) == strtolower($key)) ? 'selected' : '';
                                if (array_key_exists($key, $allOptions)) {
                                    continue;
                                }
                                $allOptions[$key] = $option;
                                $output .= '<md-option class="armMDOption armSelectOption' . $form_id . ' ' . $writter_class . '" ' . $selected_val . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</md-option>';
                            }
                        }
                        $output .= '</md-select>';
                        if ($form_type == 'active') {
                            $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                            $output .= $validate_msgs;
                            $output .= '</div>';
                        }
                        $output .= '</md-input-container>';
                        if ($form_type != 'active') {
                            $field_name = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
                        } else {
                            $field_name = $name;
                        }
                        $output .= '<input type="hidden" name="' . esc_attr($field_name) . '" value="{{ ' . esc_attr($ngModelSelect) . ' }}">';
                    }
                    break;
                /* Radio Box */
                case "radio":
                    global $arm_slugs;
                    if ($field_val == '' && $value['default_val']) {
                        $field_val = $value['default_val'];
                    }
                    if (!empty($value['options'])) {
                        if (is_admin() && isset($_GET['page']) && in_array($_GET['page'], array($arm_slugs->manage_members))) {
                            /**
                             * For Admin Side Only
                             */
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $new_data = explode(':', $data);
                                $option = $key = isset($new_data[0]) ? $new_data[0] : $data;
                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                $output .= '<input class="arm_iradio ' . esc_attr($class) . '" type="radio" name="' . esc_attr($name) . '" id="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '" value="' . esc_attr($key) . '" ' . checked(strtolower($field_val), strtolower($key), false) . ' ' . $validation_data . '/>';
                                $output .= '<label class="arm_radio_label" for="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '">' . esc_html($option) . '</label>';
                                $validation_data = '';
                            }
                        } else {
                            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                            $general_settings = $all_global_settings['general_settings'];
                            $ng_change_func = "";
                            if(isset($general_settings['enable_tax']) && isset($general_settings["country_tax_field"]) && $general_settings['enable_tax'] == 1 && $name == $general_settings["country_tax_field"] ) {
                                $ng_change_func = "ng-change = armSetTax('arm_setup_form".$formRandomID."')";
                            }
                            if ($form_type != 'active') {
                                $field_name = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
                            } else {
                                $field_name = $name;
                            }
                            $field_attr = 'data-ng-model="arm_form.' . esc_attr($name) . '_' . $field_id . '" ' . $disabled . $required;
                            if ($required) {
                                $field_attr .= ' data-ng-required="true"';
                            }
                            $output .= '<md-input-container '.$ngreadonly.' class="md-block" flex-gt-sm="">';

                            $default_radio_temp_value = false;
                            $radio_controls = "";
                            
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $new_data = explode(':', $data);
                                $option = $key = isset($new_data[0]) ? $new_data[0] : $data;
                                

                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                if( $field_val == $key ){
                                    $default_radio_temp_value = true;
                                }
                                
                                $radio_controls .= '<md-radio-button '.$ngreadonly.' data-ng-value="\'' . esc_attr($key) . '\'" value="' . esc_attr($key) . '" '.$onchange.'>' . esc_html($option) . '</md-radio-button>';

                            }

                            if(is_admin()) {
                                
                                $radio_controls .= '<span  class="arm_cancel_btn arm_form_reset_btn arm_reset_radio_field" ng-click="armresetradiofield(\'no_field_'.$field_id.'\')"><i class="armfa armfa-rotate-right"></i></span>';     
                            }
                            
                            $ng_init = ( $default_radio_temp_value ) ? 'data-ng-init="arm_form.' . esc_attr($name) . '_' . $field_id . '=\'' . esc_attr($field_val) . '\'"' : "";

                            $output .= '<md-radio-group name="' . esc_attr($name) . '" '.$ng_init.' class="' . esc_attr($class) . '" ' . $field_attr . $ng_change_func . '>';

                                $output .= $radio_controls;

                            $output .= '</md-radio-group>';
                            if ($form_type == 'active') {
                                $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                                $output .= $validate_msgs;
                                $output .= '</div>';
                            }
                            $output .= '</md-input-container>';
                            $output .= '<input type="hidden" name="' . esc_attr($field_name) . '" value="{{ arm_form.' . esc_attr($name) . '_' . $field_id . ' }}">';
                        }
                    }
                    break;
                /* Checkbox */
                case "checkbox":
                    $fname = $name;
                    $fhname = $name.'_arm_hidden';
                    if (!empty($value['options']) && count($value['options']) > 1) {
                        $fname = $name . '[]';
                        $fhname = $name . '_arm_hidden[]';
                    }
                    if ($field_val == '' && $value['default_val'] != '') {
                        $field_val = $value['default_val'];
                    }
                    global $arm_slugs;

                    if (!empty($value['options'])) {
                        if (is_admin() && isset($_GET['page']) && in_array($_GET['page'], array($arm_slugs->manage_members))) {
                            /**
                             * For Admin Side Only
                             */
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $data_default = $data;
                                //$new_data = explode(':', $data);
                                $new_data = explode(':', strip_tags($data));
                                $option = $key = isset($new_data[0]) ? $new_data[0] : $data;

                                if(count($new_data)>1)
                                {
                                    $value_data = end($new_data);
                                    $labeldata = str_replace(':'.$value_data, '', $data_default);
                                    $option = $labeldata;
                                }
                                else {
                                    $option = $data_default;
                                }

                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                if (is_array($field_val)) {
                                    $chked = (in_array($key, $field_val)) ? 'checked="checked"' : '';
                                    $hidden_key = (in_array($key, $field_val)) ? esc_attr($key) : '';
                                } else {
                                    $chked = (strtolower($field_val) == strtolower($key)) ? 'checked="checked"' : '';
                                    $hidden_key = (strtolower($field_val) == strtolower($key)) ? esc_attr($key) : '';
                                }
                                $output .= '<input type="hidden" name="' . esc_attr($fhname) . '" id="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '_arm_hidden" value="' . $hidden_key . '">';

                                $output .= '<input class="arm_icheckbox arm_hidden_checkbox ' . esc_attr($class) . '" type="checkbox" name="' . esc_attr($fname) . '" id="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '" value="' . esc_attr($key) . '"  data-id="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '_arm_hidden" ' . $chked . ' ' . $validation_data . '/>';
                                $output .= '<label class="arm_checkbox_label" for="' . esc_attr($value['id']) . '_' . esc_attr($key) . '_' . $form_id . '">' . $option . '</label>';
                            }
                        }
                        else 
                        {
                            $chkInputs = '';
                            $output .= '<md-input-container '.$ngreadonly.' class="md-block" flex-gt-sm="">';
                            
                            $arm_field_chkbox_checkName = 'arm_form__'. esc_attr($name) . '_' . $field_id;
                            $arm_field_checkboxes_arr = $value['options'];
                            $arm_field_checkboxes_arr_count = count($value['options']);
                            $arm_field_chkbox_ng_required = '';
                            $arm_field_chkbox_counter = 1;

                            foreach($arm_field_checkboxes_arr as $arm_field_checkboxe)
                            {
                                if($arm_field_chkbox_counter==1)
                                {
                                    $arm_field_chkbox_ng_required = '!('.$arm_field_chkbox_checkName.'__' . $arm_field_chkbox_counter;
                                }
                                else 
                                {
                                    $arm_field_chkbox_ng_required .= ' || '.$arm_field_chkbox_checkName.'__' . $arm_field_chkbox_counter;
                                }

                                if($arm_field_checkboxes_arr_count==$arm_field_chkbox_counter)
                                    $arm_field_chkbox_ng_required .= ')';
                                
                                $arm_field_chkbox_counter++;
                            }

                            $arm_form_chkbox_counter = 1;
                            foreach ($value['options'] as $data) {
                                $data = stripslashes($data);
                                $data_default = $data;
                                $new_data = explode(':', strip_tags($data));
                                if(count($new_data)>1)
                                {
                                    $value_data = end($new_data);
                                    $labeldata = str_replace(':'.$value_data, '', $data_default);
                                    $option = $labeldata;
                                }
                                else {
                                    $option = $data_default;
                                }
                                
                                $key = isset($new_data[0]) ? $new_data[0] : $data;
                                if (isset($new_data[1]) && $new_data[1] != '') {
                                    $key = $new_data[1];
                                }
                                $checkName = esc_attr($name) . '_' . $field_id;

                                $ngModelCheck = 'arm_form__'. esc_attr($name) . '_' . $field_id . '__' . $arm_form_chkbox_counter;

                                $field_val_arr = stripslashes_deep($field_val);
                                if (is_array($field_val)) {
                                    
                                    $key1 = str_replace('"',"&quot;",$key);
                                    $chked = (in_array($key, $field_val_arr)) ? 'data-ng-init="' . $ngModelCheck . '=\'' . $key1 . '\'"' : '';
                                } else {
                                    $unserialized_val = maybe_unserialize($field_val);
                                    if (is_array($unserialized_val)) {
                                        $chked = (in_array($key, $unserialized_val)) ? 'data-ng-init="' . $ngModelCheck . '=\'' . $key . '\'"' : '';
                                    } else {
                                        $chked = (strtolower($field_val) == strtolower($key)) ? 'data-ng-init="' . $ngModelCheck . '=\'' . $key . '\'"' : '';
                                    }
                                }
				
                                $field_attr = ' name="' . esc_attr($name) . '" data-ng-model="' . $ngModelCheck . '" ' . $disabled;
                                if (!empty($required)) {
                                    $field_attr .= ' data-ng-required="'.$arm_field_chkbox_ng_required.'"';
                                }
                                $output .= '<md-checkbox '.$ngreadonly.' aria-label="' . esc_html($key) . '" ' . $chked . ' value="' . esc_attr($key) . '" data-ng-true-value="\'' . esc_html($key) . '\'" data-ng-false-value="null" class="' . esc_attr($class) . '" ' . $field_attr . ' id="' . $ngModelCheck . '" '.$onchange.'>';
                                
                                $output .= $option;
                                if ($form_type != 'active') {
                                    $field_name = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val][]';
                                } else {
                                    $field_name = $fname;
                                }
                                $chkInputs .= '<input type="hidden" name="' . esc_attr($field_name) . '" class="arm_cl_chk_'.$name.'_'.$formRandomID.'" value="{{ ' . $ngModelCheck . ' }}">';
                                $output .= '</md-checkbox>';

                                $arm_form_chkbox_counter++;
                            }
                            if ($form_type == 'active') {
                                $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                                $output .= $validate_msgs;
                                $output .= '</div>';
                            }
                            $output .= '</md-input-container>';
                            /**
                             * md-input-container must have only one input control,
                             * so move all hidden fields to outside of md-input-container.
                             */
                            $output .= $chkInputs;
                        }
                    }
                    break;
                /* Remember Me */
                case 'rememberme':
                    $inputName = ($form_type == 'active') ? 'rememberme' : 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
                    if (empty($field_val) && !empty($value['default_val']) && $value['default_val'] == 'forever') {
                        $field_val = 'forever';
                    }
                    $chked = (strtolower($field_val) == 'forever') ? 'data-ng-init="arm_form.rememberme_forever=\'forever\'"' : '';
                    $field_attr = 'data-ng-model="arm_form.rememberme_forever" ' . $required . ' ' . $disabled;
                    $output .= '<md-checkbox aria-label="forever" ' . $chked . ' value="forever" data-ng-true-value="\'forever\'" data-ng-false-value="null" class="' . esc_attr($class) . '" ' . $field_attr . '>';
                    $output .= '<label class="arm_form_field_label_wrapper_value">' . $value['label'] . '</label>';
                    $output .= '<input type="hidden" data-ng-model="arm_form.rememberme" name="' . $inputName . '" value="{{ arm_form.rememberme_forever }}">';
                    $output .= '</md-checkbox>';
                    break;
                /* Hidden Text Field */
                /* Roles Box */
                case 'roles':
                    $sub_type = $value['sub_type'];
                    $fieldRoles = (isset($value['options']) && !empty($value['options'])) ? $value['options'] : array();
                    if ($field_val == '' && $value['default_val'] != '') {
                        $field_val = $value['default_val'];
                    }
                    if(is_array($field_val) && count($field_val)==0)
                    {
                        $field_val = "";
                    }
                    if ($form_type != 'active') {
                        $field_name = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
                    } else {
                        $field_name = $name;
                    }

                    if ($sub_type == 'radio') {
                        if($required_star != '' && $formStyles['form_layout'] == 'writer'){ 
                            $output .= '<label class="arm_material_label"> * ' . $value['label'] . '</label>';
                        } else {
                            $output .= '<label class="arm_material_label">' . $value['label'] . '</label>';
                        }
                    }
                    $output .= '<md-input-container '.$ngreadonly.' class="md-block" flex-gt-sm="">';
                    if ($sub_type == 'radio') {
                        $output .= '<md-radio-group '.$ngreadonly.' name="' . esc_attr($name) . '" data-ng-init="arm_form.' . esc_attr($name) . '_' . $field_id . '=\'' . esc_attr($field_val) . '\'" class="' . esc_attr($class) . '" ' . $field_attr . '>';
                        foreach ($fieldRoles as $key => $option) {
                            $output .= '<md-radio-button data-ng-value="\'' . esc_attr($key) . '\'" value="' . esc_attr($key) . '">' . esc_html($option) . '</md-radio-button>';
                        }
                        $output .= '</md-radio-group>';
                    } else {
                        if($required_star != '' && $formStyles['form_layout'] == 'writer'){ 
                            $output .= '<label class="arm_material_label"> * ' . $value['label'] . '</label>';
                        } else {
                            $output .= '<label class="arm_material_label">' . $value['label'] . '</label>';
                        }
                        $field_attr .= ' aria-label="' . $value['label'] . '"';
                        $writter_class = (!empty($form) && isset($formStyles['rtl']) && $formStyles['rtl'] == '1') ? 'armSelectOptionRTL' : 'armSelectOptionLTR';
                        $output .= '<md-select '.$ngreadonly.' name="' . esc_attr($name) . '" class="' . esc_attr($class) . '" ' . $field_attr . '>';
                        foreach ($fieldRoles as $key => $option) {
                            if(is_array($field_val)){
                                $field_val = array_shift($field_val);
                            }


                            $selected_val = (strtolower($field_val) == strtolower($key)) ? 'selected' : '';
                            $output .= '<md-option class="armSelectOption' . $form_id . ' ' . $writter_class . '" ' . $selected_val . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</md-option>';
                        }
                        $output .= '</md-select>';
                    }
                    if ($form_type == 'active') {
                        $output .= '<div data-ng-cloak data-ng-messages="arm_form.' . esc_attr($name) . '.$error" data-ng-show="arm_form.' . esc_attr($name) . '.$touched" class="arm_error_msg_box ng-scope">';
                        $output .= $validate_msgs;
                        $output .= '</div>';
                    }
                    $output .= '</md-input-container>';
                    $output .= '<input type="hidden" name="' . esc_attr($field_name) . '" value="{{ arm_form.' . esc_attr($name) . '_' . $field_id . ' }}">';
                    break;
                case 'hidden':
                    if ($form_type != 'active') {
                        $output .= __('Hidden Field Area', 'ARMember');
                    }
                    $output .= '<input name="' . esc_attr($name) . '" type="hidden" class="' . esc_attr($class) . '" value="' . esc_attr($field_val) . '" ' . $field_attr . '/>';
                    break;
                /* Info Block */
                case "info":
                    $id = '';
                    if (isset($value['id'])) {
                        $id = 'id="' . esc_attr($value['id']) . '" ';
                    }
                    if (isset($value['type'])) {
                        $class .= ' section-' . $value['type'];
                    }
                    if (isset($value['class'])) {
                        $class .= ' ' . $value['class'];
                    }
                    $output .= '<div ' . $id . 'class="' . esc_attr($class) . '">' . "\n";
                    if (isset($name)) {
                        $output .= '<h4 class="heading">' . esc_html($value['name']) . '</h4>' . "\n";
                    }
                    if (isset($value['description'])) {
                        $output .= $value['description'] . "\n";
                    }
                    $output .= '</div>' . "\n";
                    break;
                /* Submit */
                case "submit":
                    $buttonStyle = (isset($formStyles['button_style']) && !empty($formStyles['button_style'])) ? $formStyles['button_style'] : 'flat';
                    $submit_attr = '';
                    $submit_class = 'arm_btn_style_' . $buttonStyle;
                    $submit_class .= esc_attr($class);
                    if ($form_type == 'active') {
                        $submit_class .= ' arm_form_input_box_' . $field_id;
                        $submit_attr .= ' type="submit"';
                        $output .= '<md-button class="arm_form_field_submit_button arm_form_field_container_button ' . $submit_class . '" ' . $submit_attr . ' name="armFormSubmitBtn" ng-click="armSubmitBtnClick($event)"><span class="arm_spinner">' . file_get_contents(MEMBERSHIP_IMAGES_DIR . "/loader.svg") . '</span>' . html_entity_decode(stripslashes($value['label'])) . '</md-button>';
                    } else {
                        $submit_class .= ' arm_form_input_box_' . $field_id;
                        $submit_attr .= ' type="button" id="' . esc_attr($value['id']) . '" name="arm_forms[' . $form_id . '][' . $field_id . '][submit]" ';
                        $output .= '<div class="arm_form_field_submit_button arm_form_field_container_button arm_editable_input_button ' . $submit_class . '" ' . $submit_attr . '><div class="arm_form_field_label_wrapper_text arm_editable_input_button_inner">' . html_entity_decode(stripslashes($value['label'])) . '</div><a href="javascript:void(0)" class="arm_form_btn_editable_link">&nbsp;</a></div>';

                        $arm_show_login_link = !empty($form->settings['show_login_link']) ? 1 : 0;

                        if($form->type == "registration")
                        {
                            $arm_default_link_label = "<center><span>";
                            $arm_default_link_label .= __('Already have an account?', 'ARMember');
                            $arm_default_link_label .= ' ';
                            $arm_default_link_label .= '<a href="#">';
                            $arm_default_link_label .= __('LOGIN', 'ARMember');
                            $arm_default_link_label .= '</a>';
                            $arm_default_link_label .= '</center>';

                            $arm_login_link_label = !empty($form->settings['login_link_label']) ? $form->settings['login_link_label'] : $arm_default_link_label;
                            $arm_login_link_label = str_replace('[ARMLINK]', '<a href="#">', $arm_login_link_label);
                            $arm_login_link_label = str_replace('[/ARMLINK]', '</a>', $arm_login_link_label);

                            $arm_css_attr = (!$arm_show_login_link) ? 'display: none;' : '';
                            $output .= '<div class="arm_reg_login_links" style="margin-top: 1rem; '.$arm_css_attr.'">'.$arm_login_link_label.'</div>';
                        }
                    }
                    break;
                /* Html Area */
                case 'html':
                    if ($value['value'] != '') {
                        $output .= stripcslashes($value['value']);
                    }
                    $output .= "\n";
                    break;
                case 'section':
                    if ($value['value'] != '') {
                        $output .= stripcslashes($value['value']);
                    }
                    $output .= "\n";
                    break;
                case 'social_fields':

                    break;
                default:
                    break;
            }

            $output .= '<div class="arm_member_form_field_description">';
            $output .= '<div class="arm_form_field_description_wrapper_text arm_form_field_description_text">';
            $output .= stripslashes_deep(nl2br($field_desc)) . '</div></div>';

            if (!empty($output)) {
                $return_html = '<div class="arm_form_input_container_' . $value['type'] . ' arm_form_input_container" id="arm_form_input_container_' . $field_id . '">' . $output . '</div>';
                $return_html .= $psm;
            }
            return $return_html;
        }

        function arm_admin_save_member_details($member_data = array()) {
            global $wp, $wpdb, $current_user, $arm_slugs, $arm_errors, $ARMember, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $arm_buddypress_feature, $arm_manage_communication, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_subscription_cancel_msg, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1');

            $redirect_to = admin_url('admin.php?page=' . $arm_slugs->manage_members);
            if (!empty($member_data['action']) && in_array($member_data['action'], array('add_member', 'update_member'))) {
                if (preg_match('/\s/', $member_data['user_pass'])) {
                    unset($member_data);
                    $message = __("Space not allowed in password field", 'ARMember');
                    $arm_errors->add('arm_reg_error', $message);
                    return $arm_errors;
                }
                if ($member_data['action'] == 'add_member') {
                    $user_login = $member_data['user_login'];
                    $user_email = sanitize_email($member_data['user_email']);
                    $user_pass = $member_data['user_pass'];

                    $sanitized_user_login = sanitize_user($user_login);
                    $chk_user_login = $arm_members_class->arm_validate_username($user_login);
                    /* Check the username */
                    if (!empty($chk_user_login)) {
                        $arm_errors->add('arm_reg_error', $chk_user_login);
                        $sanitized_user_login = '';
                    }
                    /* Check the e-mail address */
                    $user_email = apply_filters('user_registration_email', $user_email);
                    $chk_user_email = $arm_members_class->arm_validate_email($user_email);
                    if (!empty($chk_user_email)) {
                        $arm_errors->add('arm_reg_error', $chk_user_email);
                        $user_email = '';
                    }
                    /* Check Member password */
                    if (empty($user_pass)) {
                        $user_pass = apply_filters('arm_member_registration_pass', wp_generate_password(12, false));
                    }

                    do_action('register_post', $sanitized_user_login, $user_email, $arm_errors);

                    remove_all_filters('registration_errors');
                    $arm_errors = apply_filters('registration_errors', $arm_errors, $sanitized_user_login, $user_email);

                    do_action('arm_remove_third_party_error', $arm_errors);
                    if (!empty($arm_errors)) {
                        if ($arm_errors->get_error_code()) {
                            return $arm_errors;
                        }
                    }
                    $user_ID = wp_create_user($sanitized_user_login, $user_pass, $user_email);
                    if (!$user_ID) {
                        $link_tag = '<a href="mailto:' . get_option('admin_email') . '">' . __('webmaster', 'ARMember') . '</a>';
                        $err_msg = isset($arm_global_settings->common_message['arm_user_not_created']) ? $arm_global_settings->common_message['arm_user_not_created'] :'';
                        $err_msg = (!empty($err_msg)) ? $err_msg : __("Couldn't register you... please contact the", 'ARMember') . ' ' . $link_tag;
                        $arm_errors->add('arm_reg_error', $err_msg);
                        return $arm_errors;
                    }
                    $update_data['ID'] = $user_ID;
                    $update_data['user_email'] = $user_email;
                    if (!empty($member_data['user_nicename'])) {
                        $update_data['user_nicename'] = sanitize_text_field($member_data['user_nicename']);
                    }
                    if (!empty($member_data['user_url'])) {
                        $update_data['user_url'] = sanitize_text_field($member_data['user_url']);
                    }
                    $display_name = isset($member_data['display_name']) ? sanitize_text_field($member_data['display_name']) : '';
                    $member_data['first_name'] = isset($member_data['first_name']) ? trim(sanitize_text_field($member_data['first_name'])) : '';
                    $member_data['last_name'] = isset($member_data['last_name']) ? trim(sanitize_text_field($member_data['last_name'])) : '';
                    if (empty($display_name)) {
                        if ($member_data['first_name'] && $member_data['last_name']) {
                            /* translators: 1: first name, 2: last name */
                            $display_name = $member_data['first_name'] . ' ' . $member_data['last_name'];
                        } elseif ($member_data['first_name']) {
                            $display_name = $member_data['first_name'];
                        } elseif ($member_data['last_name']) {
                            $display_name = $member_data['last_name'];
                        } else {
                            $display_name = $user_login;
                        }
                    }
                    $update_data['display_name'] = $display_name;

                    $user_ID = wp_update_user($update_data);
                    if(!empty($member_data['form'])){
                        update_user_meta($user_ID, 'arm_form_id',$member_data['form']);
                    }
                    $success_message = __('New member has been added successfully.', 'ARMember');
                    $ARMember->arm_set_message('success', $success_message);
                    $redirect_to = $arm_global_settings->add_query_arg("action", "edit_member", $redirect_to);
                    $redirect_to = $arm_global_settings->add_query_arg("id", $user_ID, $redirect_to);
                } elseif ($member_data['action'] == 'update_member' && !empty($member_data['id']) && $member_data['id'] != 0) {
                    $member_id = intval($member_data['id']);
                    $up_user = get_userdata($member_id);
                    $user_email = apply_filters('user_registration_email', $member_data['user_email']);
                    $update_data = array(
                        'ID' => $member_id,
                        'user_email' => $user_email
                    );
                    /* Check the e-mail address */
                    if (strtolower($user_email) != strtolower($up_user->user_email)) {
                        $chk_user_email = $arm_members_class->arm_validate_email($user_email);
                        if (!empty($chk_user_email)) {
                            $arm_errors->add('arm_profile_error', $chk_user_email);
                            unset($update_data['user_email']);
                        }
                    }
                    if ($arm_errors->get_error_code()) {
                        return $arm_errors;
                    }
                    if (!empty($member_data['user_url'])) {
                        $update_data['user_url'] = sanitize_text_field($member_data['user_url']);
                    }
                    $display_name = isset($member_data['display_name']) ? sanitize_text_field($member_data['display_name']) : '';
                    $member_data['first_name'] = isset($member_data['first_name']) ? trim(sanitize_text_field($member_data['first_name'])) : '';
                    $member_data['last_name'] = isset($member_data['last_name']) ? trim(sanitize_text_field($member_data['last_name'])) : '';
                    if (empty($display_name)) {
                        if ($member_data['first_name'] && $member_data['last_name']) {
                            /* translators: 1: first name, 2: last name */
                            $display_name = $member_data['first_name'] . ' ' . $member_data['last_name'];
                        } elseif ($member_data['first_name']) {
                            $display_name = $member_data['first_name'];
                        } elseif ($member_data['last_name']) {
                            $display_name = $member_data['last_name'];
                        } else {
                            $display_name = $up_user->user_login;
                        }
                    }
                    $update_data['display_name'] = $display_name;
                    if (!empty($member_data['user_pass'])) {
                        $update_data['user_pass'] = $member_data['user_pass'];
                    }
                    $user_ID = wp_update_user($update_data);

                    if(!empty($arm_subscription_cancel_msg))
                    {
			$common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                    	$arm_subscription_error = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __("Membership plan couldn't cancel due to not canceled subscription from payment gateway.", 'ARMember');
                        $arm_errors->add('arm_profile_error', $arm_subscription_error);
                        return $arm_errors;
                    }

                    if (is_wp_error($user_ID)) {
                        /* There was an error, probably that user doesn't exist. */
                        $usernotexist = __("User doesn't exist.", 'ARMember');
                        $arm_errors->add('arm_profile_error', $usernotexist);
                        return $arm_errors;
                    }

                    $arm_plan_id = $member_data['arm_user_plan'];
                    $planObj = new ARM_Plan($arm_plan_id);

                    $arm_user_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                    $arm_user_plan_ids = !empty($arm_user_plan_ids) ? $arm_user_plan_ids : array();

                    $arm_member_plan_data = !empty($member_data['arm_user_plan']) ? $member_data['arm_user_plan'] : array();

                    $arm_cancelled_plan_arr = array();

                    if($is_multiple_membership_feature->isMultipleMembershipFeature){
                        if(!empty($arm_user_plan_ids) && is_array($arm_member_plan_data)){
                            foreach($arm_user_plan_ids as $arm_plan_key => $arm_plan_val){
                                if(!in_array($arm_plan_val, $arm_member_plan_data)){
                                    array_push($arm_cancelled_plan_arr, $arm_plan_val);
                                }
                            }
                        }
                    }else{
                        if(!empty($arm_user_plan_ids)){
                            foreach($arm_user_plan_ids as $arm_plan_key => $arm_plan_val){
                                if($arm_plan_val != $arm_member_plan_data){
                                    array_push($arm_cancelled_plan_arr, $arm_plan_val);
                                }
                            }
                        }
                    }


                    if(!empty($arm_cancelled_plan_arr)){
                        foreach($arm_cancelled_plan_arr as $arm_cancel_plan_key => $arm_cancel_plan_val){
                            do_action('arm_cancel_subscription_gateway_action', $user_ID, $arm_cancel_plan_val);
                        }
                    }

                    $ARMember->arm_set_message('success', __('Member detail has been updated successfully.', 'ARMember'));
                    $redirect_to = $arm_global_settings->add_query_arg("action", "edit_member", $redirect_to);
                    $redirect_to = $arm_global_settings->add_query_arg("id", $user_ID, $redirect_to);
                }

                if (!empty($user_ID)) {
                    $old_primary_status = arm_get_member_status($user_ID);
                    $old_secondary_status = arm_get_member_status($user_ID, 'secondary');
                    $is_status_change = false;
                    if ($old_primary_status != 3) {
                        if (isset($member_data['arm_primary_status']) && $member_data['arm_primary_status'] == '1') {
                            $member_data['arm_primary_status'] = '1';
                            $member_data['arm_secondary_status'] = '0';
                        } else {
                            $member_data['arm_primary_status'] = '2';
                            if ($old_secondary_status != 1) {
                                $secondary_status = 0;
                                $member_data['arm_secondary_status'] = $secondary_status;

                                $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                                if (!empty($old_plan_ids) && is_array($old_plan_ids)) {
                                    foreach ($old_plan_ids as $old_plan_id) {
                                        $planData = get_user_meta($user_ID, 'arm_user_plan_' . $old_plan_id, true);
                                        if (!empty($planData)) {
                                            $plan_detail = $planData['arm_current_plan_detail'];
                                            if (!empty($plan_detail)) {
                                                $old_plan = new ARM_Plan(0);
                                                $old_plan->init((object) $plan_detail);
                                            } else {
                                                $old_plan = new ARM_Plan($old_plan_id);
                                            }
                                            if ($old_plan->is_paid() && !$old_plan->is_lifetime() && $old_plan->is_recurring()) {

                                                if (isset($member_data['arm_user_stop_user_plan']) && $member_data['arm_user_stop_user_plan'] == '1') {
                                                    $secondary_status = 6;
                                                    do_action('arm_before_update_user_subscription', $user_ID, '0');

                                                    do_action('arm_cancel_subscription', $user_ID, $old_plan_id);

                                                    $arm_subscription_plans->arm_add_membership_history($user_ID, $old_plan_id, 'cancel_subscription');
                                                    $arm_subscription_plans->arm_clear_user_plan_detail($user_ID, $old_plan_id);
                                                }
                                            }
                                        }
                                    }
                                    if (isset($member_data['arm_user_stop_user_plan']) && $member_data['arm_user_stop_user_plan'] == '1') {
                                        unset($member_data['arm_user_plan']);
                                        $member_data['arm_secondary_status'] = $secondary_status;
                                    }
                                }
                            }
                        }
                    } else {
                        if (isset($member_data['arm_primary_status']) && $member_data['arm_primary_status'] == '1') {
                            $is_status_change = true;
                            $member_data['arm_primary_status'] = '1';
                            $member_data['arm_secondary_status'] = '0';
                        }
                    }
                    unset($member_data['arm_user_stop_user_plan']);
                    $old_plan_id = 0;
                    $old_plan_data = array();
                    $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                    $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();

                    $arm_user_plan2_data=array();
                    if(!empty($member_data['arm_user_plan2'])){
                        $member_data['arm_user_plan2']=array_filter($member_data['arm_user_plan2']);
                        $arm_user_plan2_data=$member_data['arm_user_plan2'];
                    }

                    if (!isset($member_data['arm_user_plan'])) {
                        $member_data['arm_user_plan'] = 0;
                    } else {
                        if (is_array($member_data['arm_user_plan'])) {
                            foreach ($member_data['arm_user_plan'] as $key => $mpid) {
                                if (empty($mpid)) {
                                    unset($member_data['arm_user_plan'][$key]);
                                } else {
                                    $member_data['arm_subscription_start_' . $mpid] = isset($member_data['arm_subscription_start_date'][$key]) ? $member_data['arm_subscription_start_date'][$key] : '';
                                }
                            }
                            unset($member_data['arm_subscription_start_date']);
                            $member_data['arm_user_plan'] = array_values($member_data['arm_user_plan']);
                            $member_data['arm_user_plan'] = array_unique($member_data['arm_user_plan']);


                            if(!empty($member_data['arm_user_plan2']) && is_array($member_data['arm_user_plan2']))
                            {
                                $arm_membership_plan_ids = $member_data['arm_user_plan'];
                                foreach($arm_membership_plan_ids as $arm_member_key => $arm_member_value)
                                {
                                    if(!in_array($arm_member_value, $member_data['arm_user_plan2']))
                                    {
                                        array_push($member_data['arm_user_plan2'], $arm_member_value);
                                    }
                                }

                                foreach ($member_data['arm_user_plan2'] as $key => $mpid) {
                                    if (empty($mpid)) {
                                        unset($member_data['arm_user_plan2'][$key]);
                                    } else {
                                        if (empty($member_data['arm_subscription_start_' . $mpid])) {
                                            $member_data['arm_subscription_start_' . $mpid] = isset($member_data['arm_subscription_start_date2'][$key]) ? $member_data['arm_subscription_start_date2'][$key] : '';
                                        }
                                    }
                                }
                                unset($member_data['arm_subscription_start_date']);
                                $member_data['arm_user_plan'] = array_values($member_data['arm_user_plan2']);
                                $member_data['arm_user_plan'] = array_unique($member_data['arm_user_plan2']);
                                $member_data['arm_paid_post_request'] = 1;
                            }
                        }
                        else{
                            if(!empty($member_data['arm_user_plan2']) && is_array($member_data['arm_user_plan2']))
                            {
                                $arm_membership_plan_id = $member_data['arm_user_plan'];
                                array_push($member_data['arm_user_plan2'], $arm_membership_plan_id);
                                $arm_membership_plan_start_date = $member_data['arm_subscription_start_date'];
                                array_push($member_data['arm_subscription_start_date2'], $arm_membership_plan_start_date);

                                foreach ($member_data['arm_user_plan2'] as $key => $mpid) {
                                    if (empty($mpid)) {
                                        unset($member_data['arm_user_plan2'][$key]);
                                    } else {
                                        $member_data['arm_subscription_start_' . $mpid] = isset($member_data['arm_subscription_start_date2'][$key]) ? $member_data['arm_subscription_start_date2'][$key] : '';
                                    }
                                }
                                unset($member_data['arm_subscription_start_date']);
                                $member_data['arm_user_plan'] = array_values($member_data['arm_user_plan2']);
                                $member_data['arm_user_plan'] = array_unique($member_data['arm_user_plan2']);
                                $member_data['arm_paid_post_request'] = 1;
                            }
                        }
                    }
                    if (!isset($member_data['roles'])) {
                        $member_data['roles'] = '';
                    }

                    $arm_user_suspended_plan_ids = isset($member_data['arm_user_suspended_plan']) ? $member_data['arm_user_suspended_plan'] : array();
                    update_user_meta($user_ID, 'arm_user_suspended_plan_ids', $arm_user_suspended_plan_ids);   


                    unset($member_data['arm_user_suspended_plan']);
                    
                    do_action('arm_member_update_meta', $user_ID, $member_data);


                    if (!empty($member_data['arm_user_plan'])) {
                        $arm_changed_expiry_date_plan = get_user_meta($user_ID, 'arm_changed_expiry_date_plans', true);
                        $arm_changed_expiry_date_plan = !empty($arm_changed_expiry_date_plan) ? $arm_changed_expiry_date_plan : array();
                        if (is_array($member_data['arm_user_plan'])) {
                            foreach ($member_data['arm_user_plan'] as $key => $mpid) {

                                if (isset($member_data['arm_subscription_expiry_date_' . $mpid]) && !empty($member_data['arm_subscription_expiry_date_' . $mpid])) {
                                    $user_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $mpid, true);

                                    if ($user_plan_data['arm_expire_plan'] != strtotime($member_data['arm_subscription_expiry_date_' . $mpid])) {
                                        if (!in_array($mpid, $arm_changed_expiry_date_plan)) {
                                            $arm_changed_expiry_date_plan[] = $mpid;
                                        }
                                    }

                                    $arm_subscription_expiry_datetime = strtotime($member_data['arm_subscription_expiry_date_' . $mpid]);

                                    $user_plan_data['arm_expire_plan'] = $arm_subscription_expiry_datetime;
                                    update_user_meta($user_ID, 'arm_user_plan_' . $mpid, $user_plan_data);
                                    update_user_meta($user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan);

                                    $this->arm_update_activity_expire_plan_date($user_ID, $mpid, $arm_subscription_expiry_datetime);
                                }
                            }
                        } else {
                            if (isset($member_data['arm_subscription_expiry_date_' . $member_data['arm_user_plan']]) && !empty($member_data['arm_subscription_expiry_date_' . $member_data['arm_user_plan']])) {
                                $user_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $member_data['arm_user_plan'], true);

                                if ($user_plan_data['arm_expire_plan'] != strtotime($member_data['arm_subscription_expiry_date_' . $member_data['arm_user_plan']])) {
                                    if (!in_array($member_data['arm_user_plan'], $arm_changed_expiry_date_plan)) {
                                        $arm_changed_expiry_date_plan[] = $member_data['arm_user_plan'];
                                    }
                                }

                                $arm_subscription_expiry_datetime = strtotime($member_data['arm_subscription_expiry_date_' . $member_data['arm_user_plan']]);

                                update_user_meta($user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan);
                                $user_plan_data['arm_expire_plan'] = $arm_subscription_expiry_datetime;
                                update_user_meta($user_ID, 'arm_user_plan_' . $member_data['arm_user_plan'], $user_plan_data);

                                $this->arm_update_activity_expire_plan_date($user_ID, $member_data['arm_user_plan'], $arm_subscription_expiry_datetime);
                            }
                        }
                    }
                    
                    if (!empty($member_data['arm_user_future_plan'])) {
                        $arm_changed_expiry_date_plan = get_user_meta($user_ID, 'arm_changed_expiry_date_plans', true);
                        $arm_changed_expiry_date_plan = !empty($arm_changed_expiry_date_plan) ? $arm_changed_expiry_date_plan : array();
                         if (is_array($member_data['arm_user_future_plan'])) {
                            foreach ($member_data['arm_user_future_plan'] as $fkey => $fmpid) {
                                if (isset($member_data['arm_subscription_expiry_date_' . $fmpid]) && !empty($member_data['arm_subscription_expiry_date_' . $fmpid])) {
                                    $user_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $fmpid, true);

                                    if ($user_plan_data['arm_expire_plan'] != strtotime($member_data['arm_subscription_expiry_date_' . $fmpid])) {
                                        if (!in_array($fmpid, $arm_changed_expiry_date_plan)) {
                                            $arm_changed_expiry_date_plan[] = $fmpid;
                                        }
                                    }

                                    $arm_subscription_expiry_datetime = strtotime($member_data['arm_subscription_expiry_date_' . $fmpid]);

                                    $user_plan_data['arm_expire_plan'] = $arm_subscription_expiry_datetime;
                                    update_user_meta($user_ID, 'arm_user_plan_' . $fmpid, $user_plan_data);
                                    update_user_meta($user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan);

                                    $this->arm_update_activity_expire_plan_date($user_ID, $mpid, $arm_subscription_expiry_datetime);
                                }
                            }
                        }
                    }
                    
                    
                    $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  'arm_subscription_expiry_date\_%'");
                    



                    if (!empty($old_plan_ids) && is_array($old_plan_ids)) {

                        $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                        $old_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $old_plan_id, true);

                        $extend_renewal_date_plan_ids = array();
                        $count = 0;
                        foreach ($old_plan_ids as $old_pid) {
                            $old_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $old_pid, true);
                            if (!empty($old_plan_data)) {
                                $oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
                                if (!empty($oldPlanDetail)) {
                                    $planObj = new ARM_Plan(0);
                                    $planObj->init((object) $oldPlanDetail);
                                } else {
                                    $planObj = new ARM_Plan($old_pid);
                                }

                                $arm_selected_payment_mode = $old_plan_data['arm_payment_mode'];

                                if ($planObj->is_recurring() && $arm_selected_payment_mode == 'manual_subscription' || $oldPlanDetail['arm_subscription_plan_type']=='paid_finite') {
                                    $count++;
                                    $extend_renewal_date_plan_ids[] = $old_pid;
                                }
                            }
                        }
                        if (!empty($extend_renewal_date_plan_ids) && is_array($extend_renewal_date_plan_ids)) {
                            $user_suspended_plans_ids_array = get_user_meta($user_ID, 'arm_user_suspended_plan_ids', true);
                            $removed_suspended_plans = 0;
                            foreach ($extend_renewal_date_plan_ids as $extend_renewal_date_plan_id) {


                                $old_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, true);

                                if (isset($member_data['arm_user_grace_plus_' . $extend_renewal_date_plan_id]) && $member_data['arm_user_grace_plus_' . $extend_renewal_date_plan_id] !== 0) {
                                    $arm_old_next_payment_due_date = $old_plan_data['arm_next_due_payment'];
                                    $payment_cycle = $old_plan_data['arm_payment_cycle'];
                                    $grace_period = $member_data['arm_user_grace_plus_' . $extend_renewal_date_plan_id];
                                    /* if next due date meta is not there than calculate it */

                                    $arm_plan_expire = $old_plan_data['arm_expire_plan'];
                                    if (isset($arm_old_next_payment_due_date) && $arm_old_next_payment_due_date === '') {
                                        $arm_old_next_payment_due_date = $arm_members_class->arm_get_next_due_date($user_ID, $extend_renewal_date_plan_id, false, $payment_cycle);
                                    }

                                    $arm_next_payment_due_date = strtotime(date('Y-m-d', strtotime("+$grace_period days", $arm_old_next_payment_due_date)));

                                    $old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;

                                    $oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
                                    if (!empty($oldPlanDetail)) {
                                        $planObj = new ARM_Plan(0);
                                        $planObj->init((object) $oldPlanDetail);
                                    } else {
                                        $planObj = new ARM_Plan($extend_renewal_date_plan_id);
                                    }

                                    $recurringData = $planObj->prepare_recurring_data($payment_cycle);
                                    $total_recurrence = $recurringData['rec_time'];
                                    $completed_rec = $old_plan_data['arm_completed_recurring'];

                                    if ($total_recurrence == $completed_rec) {

                                        $old_plan_data['arm_expire_plan'] = strtotime(date('Y-m-d', strtotime("+$grace_period days", $arm_plan_expire)));
                                    }

                                    update_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data);
                                }

                                $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  'arm_user_grace_plus\_%'");
                                if (isset($member_data['arm_skip_next_renewal_' . $extend_renewal_date_plan_id]) && $member_data['arm_skip_next_renewal_' . $extend_renewal_date_plan_id] == 1) {
                                    if($old_plan_data['arm_payment_mode'] == 'manual_subscription')
                                    {
                                        $complete_recuring = $old_plan_data['arm_completed_recurring'];
                                        $payment_cycle = $old_plan_data['arm_payment_cycle'];
                                        $old_next_due_date = $old_plan_data['arm_next_due_payment'];

                                        $now = current_time('mysql');

                                         $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_ID, $extend_renewal_date_plan_id, $now));  


                                        if (strtotime($now) < $old_next_due_date) {

                                            
                                            if ($arm_last_payment_status != 'failed') {
                                                if ($complete_recuring !== '') {
                                                $old_plan_data['arm_completed_recurring'] = ++$complete_recuring;
                                                } else {
                                                    $old_plan_data['arm_completed_recurring'] = 1;
                                                }
                                            update_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data);

                                            $arm_next_payment_due_date = $arm_members_class->arm_get_next_due_date($user_ID, $extend_renewal_date_plan_id, false, $payment_cycle);
                                            $old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;
                                            $old_plan_data['arm_user_gateway'] = 'manual';

                                            $old_plan_data['arm_is_user_in_grace'] = 0;
                                            $old_plan_data['arm_grace_period_end'] = '';
                                            $old_plan_data['arm_grace_period_action'] = '';


                                            update_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data);
                                            }
                                        } else {

                                            

                                            if ($complete_recuring !== '') {
                                                $old_plan_data['arm_completed_recurring'] = ++$complete_recuring;
                                            } else {
                                                $old_plan_data['arm_completed_recurring'] = 1;
                                            }
                                            update_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data);

                                            $arm_next_payment_due_date = $arm_members_class->arm_get_next_due_date($user_ID, $extend_renewal_date_plan_id, false, $payment_cycle);
                                            $old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;
                                            $old_plan_data['arm_user_gateway'] = 'manual';

                                            $old_plan_data['arm_is_user_in_grace'] = 0;
                                            $old_plan_data['arm_grace_period_end'] = '';
                                            $old_plan_data['arm_grace_period_action'] = '';

                                            update_user_meta($user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data);
                                        }

                                        if(!empty($user_suspended_plans_ids_array)){
                                            if(in_array($extend_renewal_date_plan_id, $user_suspended_plans_ids_array)){
                                                unset($user_suspended_plans_ids_array[array_search($extend_renewal_date_plan_id, $user_suspended_plans_ids_array)]);
                                                $removed_suspended_plans = 1;
                                            }
                                        }
					$arm_members_class->arm_add_manual_user_payment($user_ID, $extend_renewal_date_plan_id, $member_data);
                                    }
                                    else if(isset($old_plan_data['arm_current_plan_detail']['arm_subscription_plan_type']) && $old_plan_data['arm_current_plan_detail']['arm_subscription_plan_type']=='paid_finite' && (isset($member_data['arm_skip_next_renewal_'.$extend_renewal_date_plan_id]) && $member_data['arm_skip_next_renewal_'.$extend_renewal_date_plan_id]==1))
                                    {
                                        $member_data['payment_type'] = "manual";
                                        $arm_members_class->arm_add_manual_user_payment($user_ID, $extend_renewal_date_plan_id, $member_data);
                                        $arm_subscription_plans->arm_update_user_subscription_for_manual($user_ID, $extend_renewal_date_plan_id, 'manual', 0, 'success');
                                    }
                                }
                                $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  'arm_skip_next_renewal\_%'");
                            }
                            if($removed_suspended_plans == 1){
                                update_user_meta($user_ID, 'arm_user_suspended_plan_ids', array_values($user_suspended_plans_ids_array));
                            }
                        }
                    }

                    if ($arm_buddypress_feature->isBuddypressFeature) {
                        do_action('arm_buddypress_xprofile_field_save', $user_ID, $member_data, 'update');
                    }
                    if ($member_data['action'] == 'add_member') {
                        $wpdb->update($ARMember->tbl_arm_members, array('arm_user_type' => 1), array('arm_user_id' => $user_ID));

                        $arm_send_email = (isset($member_data["arm_send_email"]) && $member_data["arm_send_email"] == "1") ? '1' : '2';

                        arm_new_user_notification($user_ID, $user_pass, $arm_send_email);

                        do_action("arm_after_add_new_user", $user_ID, $member_data);

                        if (isset($member_data['arm_user_plan']) && !empty($member_data['arm_user_plan'])) {

                            $is_paid_post_req = ( !empty( $member_data['arm_paid_post_request'] ) && 1 == $member_data['arm_paid_post_request'] ) ? true : false;

                            if ( is_array($member_data['arm_user_plan']) && ( $is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post_req )  ) {
                                foreach ($member_data['arm_user_plan'] as $plan_id) {

                                    $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $plan_id);
                                    do_action('arm_after_user_plan_change_by_admin', $user_ID, $plan_id);
                                }
                            } else {
                                $arm_manage_communication->membership_communication_mail('on_change_subscription_by_admin', $user_ID, $member_data['arm_user_plan']);
                                do_action('arm_after_user_plan_change_by_admin', $user_ID, $member_data['arm_user_plan']);
                            }
                        }
                    } elseif ($member_data['action'] == 'update_member') {


                        // do not forget to change in arm_user_plan_action()

                        if ($is_status_change) {
                            $user_data = get_user_by('id', $user_ID);
                            /* Send Account Verify Notification Mail */
                            armMemberAccountVerifyMail($user_data);
                        }
                        if (isset($member_data['arm_user_plan']) && !empty($member_data['arm_user_plan'])) {
                            if( !empty( $member_data['arm_paid_post_request'] ) && 1 == $member_data['arm_paid_post_request'] ){  
                                $old_plan_ids1 = array_intersect($member_data['arm_user_plan'], $old_plan_ids);
                                foreach ($member_data['arm_user_plan'] as $plan_id) {
                                    if (!in_array($plan_id, $old_plan_ids1) && in_array($plan_id,$arm_user_plan2_data)) {
                                        $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $plan_id);
                                        do_action('arm_after_user_plan_change_by_admin', $user_ID, $plan_id);
                                    }
                                }
                            }  
                            if ( is_array($member_data['arm_user_plan']) && 1==$is_multiple_membership_feature->isMultipleMembershipFeature){ 
                                $old_plan_ids1 = array_intersect($member_data['arm_user_plan'], $old_plan_ids);
                                foreach ($member_data['arm_user_plan'] as $plan_id) {
                                    if (!in_array($plan_id, $old_plan_ids1) && !in_array($plan_id,$arm_user_plan2_data)) {
                                        $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $plan_id);
                                        do_action('arm_after_user_plan_change_by_admin', $user_ID, $plan_id);
                                    }
                                }
                            } else {
                                if ($old_plan_id != 0 && $old_plan_id != '') {
                                    
                                    if(is_array($member_data['arm_user_plan'])){
                                        $old_plan_ids1 = array_intersect($member_data['arm_user_plan'], $old_plan_ids);
                                        foreach ($member_data['arm_user_plan'] as $plan_id) {
                                            if (!in_array($plan_id, $old_plan_ids1) && !in_array($plan_id,$arm_user_plan2_data)) {
                                                $arm_manage_communication->membership_communication_mail('on_change_subscription_by_admin', $user_ID, $plan_id);
                                            }
                                        }

                                    }else if ($old_plan_id != $member_data['arm_user_plan']) {
                                        $arm_manage_communication->membership_communication_mail('on_change_subscription_by_admin', $user_ID, $member_data['arm_user_plan']);
                                    } else if($old_plan_id == $member_data['arm_user_plan'] && (isset($member_data['arm_skip_next_renewal_'.$old_plan_id]) && $member_data['arm_skip_next_renewal_'.$old_plan_id] == 1 )) {

                                        $arm_manage_communication->membership_communication_mail('on_recurring_subscription', $user_ID, $old_plan_id);
                                        //on_renew_subscription
                                    }
                                } else {
                                    $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $member_data['arm_user_plan']);
                                }
                                do_action('arm_after_user_plan_change_by_admin', $user_ID, $member_data['arm_user_plan']);
                            }
                        }


                        if(!empty($old_plan_id) && isset($member_data['arm_user_plan']) && empty($member_data['arm_user_plan'])) {
                            $arm_manage_communication->membership_communication_mail('on_cancel_subscription', $user_ID, $old_plan_id);
                        }
                        else if($is_multiple_membership_feature->isMultipleMembershipFeature && !empty($member_data['arm_user_plan']))
                        {
                            foreach ($old_plan_ids as $old_plan_id_val)
                            {
                                if(!in_array($old_plan_id_val, $member_data['arm_user_plan'])){
                                    $arm_manage_communication->membership_communication_mail('on_cancel_subscription', $user_ID, $old_plan_id_val);
                                }
                            }
                        }
                        do_action('arm_after_update_user_profile', $user_ID, $member_data);

                        // do not forget to change in arm_user_plan_action()
                    }
                    if (!empty($redirect_to)) {
                        wp_redirect($redirect_to);
                        exit;
                    }
                }
            }
        }

        function arm_update_activity_expire_plan_date($user_id, $plan_id, $arm_subscription_expiry_datetime)
        {
            global $ARMember, $wpdb;

            $arm_get_activity_details = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`='membership' AND `arm_user_id`='$user_id' AND `arm_item_id`='$plan_id' AND `arm_action` != 'recurring_subscription' ORDER BY `arm_activity_id` DESC", ARRAY_A);
            if(!empty($arm_get_activity_details['arm_activity_id']))
            {
                $arm_activity_id = $arm_get_activity_details['arm_activity_id'];
                $arm_content = $arm_get_activity_details['arm_content'];
                if(!empty($arm_content))
                {
                    $arm_content_arr = maybe_unserialize($arm_content);
                    if(!empty($arm_content_arr['expire']))
                    {
                        $arm_content_arr['expire'] = $arm_subscription_expiry_datetime;
                        $arm_content_arr_str = maybe_serialize($arm_content_arr);
                        $field_status_update = $wpdb->update($ARMember->tbl_arm_activity, array('arm_content' => $arm_content_arr_str), array('arm_activity_id' => $arm_activity_id, 'arm_user_id' => $user_id, 'arm_item_id' => $plan_id ));
                    }
                }
            }
        }

        function arm_shortcode_form_ajax_action() {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_email_settings, $arm_pay_per_post_feature;
            $all_errors = array();
            $err_msg = isset($arm_global_settings->common_message['arm_general_msg']) ? $arm_global_settings->common_message['arm_general_msg'] : '';
            $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
            $return = array('status' => 'error', 'type' => 'message', 'message' => $err_msg);
            $current_url = $arm_global_settings->add_query_arg($wp->query_string, '', home_url($wp->request));
            $redirect_to = !empty($_REQUEST['redirect_to']) ? sanitize_text_field($_REQUEST['redirect_to']) : ARM_HOME_URL;
            if (isset($_POST) && !empty($_POST['arm_action'])) {
                /* Process submitted data. */
                $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
                $posted_data = $_POST;
                $form = $posted_data['arm_action'];
                if ($form == 'edit_profile') {
                    $form_type = 'edit_profile';
                    $form_id = intval($posted_data['arm_parent_form_id']);
                    $success_message = !empty($posted_data['arm_success_message']) ? sanitize_text_field($posted_data['arm_success_message']) : '';
                    unset($posted_data['arm_parent_form_id']);
                    unset($posted_data['arm_success_message']);
                    $armform = new ARM_Form('id', $form_id);
                    $armform->type = 'edit_profile';
                } else {
                    $form_id = (isset($posted_data['arm_form_id'])) ? intval($posted_data['arm_form_id']) : '';
                    $armform = new ARM_Form('slug', $form);
                    $form_type = $armform->type;
                    $form_settings = $armform->settings;
                }

                $arm_form_fields = $armform->fields;
                $field_options = array();
                $is_hide_username = 0;
                foreach ($arm_form_fields as $fields) {
                    if ($fields['arm_form_field_slug'] == 'user_login') {
                        $field_options = $fields['arm_form_field_option'];
                        if (isset($field_options['hide_username']) && $field_options['hide_username'] == 1) {
                            $posted_data['user_login'] = sanitize_email($posted_data['user_email']);
                            $is_hide_username = 1;
                        }
                    }
                }
                $posted_data['form_type'] = $form_type;
                do_action('arm_before_form_submit_action', $armform, $posted_data);
                $all_errors = $this->arm_member_validate_meta_details($armform, $posted_data);
                if ($all_errors === TRUE) {
                    do_action('arm_after_form_validate_action', $armform, $posted_data);
                    switch ($form_type) {
                        case 'registration' :
                        case 'register' :
                            $posted_data['form'] = $form;
                            $user_id = $this->arm_register_new_member($posted_data, $armform);
                            $referral_url = isset($posted_data['referral_url']) ? esc_url_raw($posted_data['referral_url']) : '';
                            global $arm_login_from_registration;
                            if (is_numeric($user_id) && !is_array($user_id)) {

                                $arm_default_redirection_settings = get_option('arm_redirection_settings');
                                $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                                $login_redirection_rules_options = $arm_default_redirection_settings['signup'];
                                
                                if($login_redirection_rules_options['redirect_type'] == 'formwise'){
                                    
                                    $signup_redirection_conditions = $login_redirection_rules_options['conditional_redirect'];
                                    $arm_signup_condition_array = array();
                                    if (!empty($signup_redirection_conditions)) {
                                        foreach ($signup_redirection_conditions as $signup_conditions_key => $signup_conditions) {
                                            if (is_array($signup_conditions)) {
                                                $arm_signup_condition_array[$signup_conditions_key] = isset($signup_conditions['form_id']) ? $signup_conditions['form_id'] : 0;
                                            }
                                        }
                                    }
                                    
                                    $arm_intersect_form_ids = array_intersect($arm_signup_condition_array, array($form_id, -2));
                                    
                                    if(!empty($arm_intersect_form_ids)){
                                        foreach($arm_intersect_form_ids as $arm_signup_condition_key => $arm_signup_condition_val){
                                            $arm_setup_redirection_page_id = $signup_redirection_conditions[$arm_signup_condition_key]['url']; 
                                            $redirect_to = $arm_global_settings->arm_get_permalink('', $arm_setup_redirection_page_id);
                                        }
                                    }
                                    else{
                                        $redirect_to = (isset($login_redirection_rules_options['default']) && !empty($login_redirection_rules_options['default'])) ? $login_redirection_rules_options['default'] : ARM_HOME_URL;
                                    }
                                }
                                else{
                                    if ($login_redirection_rules_options['type'] == 'page') {
                                        $form_redirect_id = (!empty($login_redirection_rules_options['page_id'])) ? $login_redirection_rules_options['page_id'] : '0';
                                        $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                    
                                    } else if ($login_redirection_rules_options['type'] == 'referral') {
                                        $default_redirect = (!empty($login_redirection_rules_options['refferel'])) ? $login_redirection_rules_options['refferel'] : ARM_HOME_URL;
                                        $redirect_to = (!empty($referral_url)) ? $referral_url : $default_redirect;
                                        
                                    } else {
                                        $redirect_to = (!empty($login_redirection_rules_options['url'])) ? $login_redirection_rules_options['url'] : ARM_HOME_URL;
                                        $user_info = get_userdata($user_id);
                                        $username = $user_info->user_login;
                                        $redirect_to = str_replace('{ARMCURRENTUSERNAME}', $username, $redirect_to);
                                        $redirect_to = str_replace('{ARMCURRENTUSERID}', $user_id, $redirect_to);
                                    }
                                }
                                $register_message = $redirect_to;
                                $arm_return_script = '';
                                $return['script'] = apply_filters('arm_after_register_submit_sucess_outside',$arm_return_script);
                                $return['status'] = 'success';
                                $return['type'] = 'redirect';
                                $return['message'] = $register_message;
                            } else {
                                $all_errors = $arm_errors->get_error_messages('arm_reg_error');
                            }
                            break;

                        case 'edit_profile':
                        case 'update_profile':
                            if ($is_hide_username == 1) {
                                $posted_data['hide_username'] = 1;
                            } else {
                                $posted_data['hide_username'] = 0;
                            }
                            $user_id = $this->arm_update_member_profile($posted_data);
                            if (is_numeric($user_id) && !is_array($user_id)) {
                              
                                update_user_meta($user_id, 'arm_form_id', $form_id);

                                $arm_default_redirection_settings = get_option('arm_redirection_settings');
                                $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                                $profile_redirection_rules_options = $arm_default_redirection_settings['edit_profile'];
                                
                                if ( isset($profile_redirection_rules_options['redirect_type']) && $profile_redirection_rules_options['redirect_type'] == 'common' ) {
                                    if ($profile_redirection_rules_options['type'] == 'page') {
                                        $form_redirect_id = (!empty($profile_redirection_rules_options['page_id'])) ? $profile_redirection_rules_options['page_id'] : '0';
                                        $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);    
                                    } else if ($profile_redirection_rules_options['type'] == 'url') {
                                        $redirect_to = (!empty($profile_redirection_rules_options['url'])) ? $profile_redirection_rules_options['url'] : ARM_HOME_URL;
                                    } else {
                                        $redirect_to = ARM_HOME_URL;
                                    }
                                    
                                    $user = get_user_by('id', $user_id);
                                    
                                    $redirect_to = str_replace('{ARMCURRENTUSERNAME}', $user->data->user_login, $redirect_to);
                                    $redirect_to = str_replace('{ARMCURRENTUSERID}', $user->data->ID, $redirect_to);
                                    $success_message = $redirect_to;
                                    $return['status'] = 'success';
                                    $return['type'] = 'redirect';
                                    $return['message'] = $success_message;    

                                } else if ( isset($profile_redirection_rules_options['redirect_type']) && $profile_redirection_rules_options['redirect_type'] == 'formwise' ) {
                                    $profile_redirection_conditions = (isset($profile_redirection_rules_options['conditional_redirect']) && !empty($profile_redirection_rules_options['conditional_redirect'])) ? $profile_redirection_rules_options['conditional_redirect'] : array();
                                    
                                    $redirect_to = "";
                                    foreach ($profile_redirection_conditions as $redirection_condition) {
                                        if($redirection_condition['form_id'] == $form_id) {
                                            $redirect_to = $arm_global_settings->arm_get_permalink('', $redirection_condition['url']);
                                        } else if($redirection_condition['form_id'] == "-2") {
                                            $redirect_to = $arm_global_settings->arm_get_permalink('', $redirection_condition['url']);
                                        }
                                    }
                                    
                                    $success_message = $redirect_to;
                                    $return['status'] = 'success';
                                    $return['type'] = 'redirect';
                                    $return['message'] = $success_message;

                                } else {
                                    //$success_message = $profile_redirection_rules_options['message'];
                                    $return['status'] = 'success';
                                    $return['message'] = $success_message;
                                }

                            } else {
                                $all_errors = $arm_errors->get_error_messages('arm_profile_error');
                            }
                            break;
                        case 'login' :
                        case 'signin' :
                            $ARMember->arm_session_start();
                            //if (!is_user_logged_in()) {
                                $login_data['user_login'] = isset($posted_data['user_login']) ? sanitize_text_field($posted_data['user_login']) : '';
                                $login_data['user_password'] = isset($posted_data['user_pass']) ? $posted_data['user_pass'] : '';
                                $login_data['remember'] = isset($posted_data['rememberme']) ? sanitize_text_field($posted_data['rememberme']) : '';
                                $referral_url = isset($posted_data['referral_url']) ? esc_url_raw($posted_data['referral_url']) : '';
                                if (is_multisite()) {
                                    $user = get_user_by('login', $login_data['user_login']);
                                    $is_deleted = get_user_meta($user->ID, 'arm_site_' . $GLOBALS['blog_id'] . '_deleted', true);
                                    if ($is_deleted != '' && $is_deleted == 1) {
                                        $all_errors = array(__('User is deleted from current site. Please Contact Administrator.', 'ARMember'));
                                        $return['status'] = 'error';
                                        $return['type'] = 'message';
                                        $return['message'] = __('User is deleted from current site. Please Contact Administrator.', 'ARMember');
                                        break;
                                    }
                                }
                                global $browser_session_id;
                                $browser_session_id = session_id();

                                $user = wp_signon($login_data, false);

                                if (is_wp_error($user)) {

                                    $login_error = $user->get_error_message();
                                    $all_errors = array($login_error);
                                }
                                if (is_a($user, 'WP_User')) {
                                    wp_set_current_user($user->ID, $user->user_login);
                                    $remember = ( isset($posted_data['rememberme']) && $posted_data['rememberme'] != '' ) ? true : false;
                                    wp_set_auth_cookie($user->ID, $remember);

                                    if (is_user_logged_in()) {
                                        if (in_array('administrator', $user->roles)) {
                                            $redirect_to = get_admin_url();
                                        } else {
                                            $arm_default_redirection_settings = get_option('arm_redirection_settings');
                                            $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                                            $login_redirection_rules_options = $arm_default_redirection_settings['login'];

                                            if ( isset($login_redirection_rules_options['main_type']) && $login_redirection_rules_options['main_type'] == 'fixed' ) 
                                            {
                                                if ($login_redirection_rules_options['type'] == 'page') {
                                                    $form_redirect_id = (!empty($login_redirection_rules_options['page_id'])) ? $login_redirection_rules_options['page_id'] : '0';
                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                } else if ($login_redirection_rules_options['type'] == 'referral') {
                                                    $default_redirect = (!empty($login_redirection_rules_options['refferel'])) ? $login_redirection_rules_options['refferel'] : ARM_HOME_URL;
                                                    $redirect_to = (!empty($referral_url)) ? $referral_url : $default_redirect;
                                                } else {
                                                    $redirect_to = (!empty($login_redirection_rules_options['url'])) ? $login_redirection_rules_options['url'] : ARM_HOME_URL;
                                                }
                                            } 
                                            else if ($login_redirection_rules_options['main_type'] == 'conditional_redirect') 
                                            {
                                                $login_redirection_conditions = (isset($login_redirection_rules_options['conditional_redirect']) && !empty($login_redirection_rules_options['conditional_redirect'])) ? $login_redirection_rules_options['conditional_redirect'] : array();
                                                $default_redirect = (isset($login_redirection_conditions['default']) && !empty($login_redirection_conditions['default'])) ? $login_redirection_conditions['default'] : ARM_HOME_URL;
                                                $arm_user_plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                                                $arm_user_plan_ids = apply_filters('arm_allow_specific_user_restricted_access', $arm_user_plan_ids, $user->ID);
                                                $arm_user_plan_ids = !empty($arm_user_plan_ids) ? $arm_user_plan_ids : array();
                                                if(!empty($arm_user_plan_ids)){
                                                    $arm_user_plan_ids[] = -3; 
                                                }else{
                                                    $arm_user_plan_ids[] = -2;
                                                }

                                                $arm_login_condition_plans_array = array();
                                                if (!empty($login_redirection_conditions)) {
                                                    foreach ($login_redirection_conditions as $login_conditions_key => $login_conditions) {
                                                        if (is_array($login_conditions)) {
                                                            $arm_login_condition_plans_array[$login_conditions_key] = isset($login_conditions['plan_id']) ? $login_conditions['plan_id'] : 0;
                                                        }
                                                    }
                                                }
                                                
                                                $arm_intersect_plan_ids = array_intersect($arm_login_condition_plans_array, $arm_user_plan_ids);

                                                if (empty($arm_intersect_plan_ids)) {
                                                    $redirect_to = $default_redirect;
                                                } else {
                                                    $redirect_to = $default_redirect;

                                                    foreach ($arm_intersect_plan_ids as $arm_intersect_plan_id_key => $arm_intersect_plan_id) {
                                                        $condition = $login_redirection_conditions[$arm_intersect_plan_id_key]['condition'];

                                                        if ($condition == '') {
                                                            $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                            $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                            break;
                                                        } else {
                                                            if ($condition == 'first_time') {
                                                                $arm_login_first_time = get_user_meta($user->ID, 'arm_firsttime_login', true);
                                                                if ($arm_login_first_time == '') {
                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                    break;
                                                                }
                                                            } else if ($condition == 'in_trial') {
                                                                $user_plan_data = get_user_meta($user->ID, 'arm_user_plan_' . $arm_intersect_plan_id, true);
                                                                $arm_trial_end_date = isset($user_plan_data['arm_trial_end']) ? $user_plan_data['arm_trial_end'] : '';
                                                                if ($arm_trial_end_date != '') {
                                                                    $now = current_time('timestamp');
                                                                    if ($now < $arm_trial_end_date) {
                                                                        $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                        $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                        break;
                                                                    }
                                                                }
                                                            } else if ($condition == 'in_grace') {
                                                                $user_plan_data = get_user_meta($user->ID, 'arm_user_plan_' . $arm_intersect_plan_id, true);
                                                                $arm_user_in_grace = isset($user_plan_data['arm_is_user_in_grace']) ? $user_plan_data['arm_is_user_in_grace'] : 0;
                                                                if ($arm_user_in_grace == 1) {
                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                    break;
                                                                }
                                                            } else if ($condition == 'failed_payment') {
                                                                $arm_user_status = arm_get_member_status($user->ID, 'secondary');
                                                                $user_suspended_plan_ids = get_user_meta($user->ID, 'arm_user_suspended_plan_ids', true);
                                                                $user_suspended_plan_ids = !empty($user_suspended_plan_ids) ? $user_suspended_plan_ids : array();
                                                                if ($arm_user_status == 5) {
                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                    break;
                                                                } else if (in_array($arm_intersect_plan_id, $user_suspended_plan_ids)) {
                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                    break;
                                                                }
                                                            } else if ($condition == 'pending') {
                                                                $arm_user_status = arm_get_member_status($user->ID);

                                                                if ($arm_user_status == 3) {
                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';
                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                    break;
                                                                }
                                                            } else if ($condition == 'before_expire') {
                                                                $condition_plan_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['plan_id']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['plan_id'] : 0;
                                                                if ($condition_plan_id == $arm_intersect_plan_id) {
                                                                    $expiration_days = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['expire']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['expire'] : 0;
                                                                    $now = current_time('timestamp');
                                                                    
                                                                    $end_time = strtotime(date("Y-m-d H:i:s", $now). " +" . $expiration_days . " days");
                                                                    
                                                                    if(!empty($arm_user_plan_ids)) {
                                                                        foreach ($arm_user_plan_ids as $key => $user_plan_id) {
                                                                        
                                                                            $user_plan_data = get_user_meta($user->ID, 'arm_user_plan_' . $user_plan_id, true);     

                                                                            $arm_plan_end_date = isset($user_plan_data['arm_expire_plan']) ? $user_plan_data['arm_expire_plan'] : '';
                                                                    
                                                                            
                                                                            if (!empty($arm_plan_end_date)) {
                                                                                $arm_plan_end_date = strtotime(date('Y-m-d 23:59:59', $arm_plan_end_date));
                                                                                $end_time = strtotime(date('Y-m-d 23:59:59', $end_time));


                                                                                if ($now <= $arm_plan_end_date && $end_time >= $arm_plan_end_date) {

                                                                                    $form_redirect_id = isset($login_redirection_conditions[$arm_intersect_plan_id_key]['url']) ? $login_redirection_conditions[$arm_intersect_plan_id_key]['url'] : '0';

                                                                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                                                                    
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $redirect_to = $default_redirect;
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $redirect_to = (!empty($login_redirection_rules_options['url'])) ? $login_redirection_rules_options['url'] : ARM_HOME_URL;
                                            }
                                            $redirect_to = str_replace('{ARMCURRENTUSERNAME}', $user->data->user_login, $redirect_to);
                                            $redirect_to = str_replace('{ARMCURRENTUSERID}', $user->data->ID, $redirect_to);
                                        }                                   

                                        update_user_meta( $user->ID, 'arm_firsttime_login', 1);
                                        $login_message = $redirect_to;
                                        $return['status'] = 'success';
                                        $return['type'] = 'redirect';
                                        $return['message'] = $login_message;
                                        unset($_SESSION['arm_restricted_page_url']);

                                        if(empty($arm_user_plan_ids))
                                        {
                                            $arm_user_plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                                            $arm_user_plan_ids = apply_filters('arm_allow_specific_user_restricted_access', $arm_user_plan_ids, $user->ID);
                                            $arm_user_plan_ids = !empty($arm_user_plan_ids) ? $arm_user_plan_ids : array();
                                        }
                                        $user_plan_id = !empty($arm_user_plan_ids) ? $arm_user_plan_ids[0] : 0;
                                        
                                        global $arm_manage_communication;
                                        $args = array('plan_id' => $user_plan_id, 'user_id' => $user->ID, 'action' => 'on_login_account');
                                        $arm_manage_communication->arm_user_plan_status_action_mail($args);
                                    }
                                }
                                

                            //}
                            break;

                        case 'lostpassword' :
                        case 'retrievepassword' :
                        case 'forgot_password' :
                            if ($http_post) {
                                $fp = $this->arm_retrieve_password();



                                if ($fp && empty($arm_errors->errors)) {
                                    $rp_success_msg = !empty($form_settings['message']) ? $form_settings['message'] : __('We have sent you a password reset link, Please check your mail.', 'ARMember');
                                    $return['status'] = 'success';
                                    $return['message'] = $rp_success_msg;
                                } else {
                                    $all_errors = $arm_errors->get_error_messages();
                                }
                            }
                            break;

                        case 'change_password' :
                            $newPass = isset($_POST['user_pass']) ? $_POST['user_pass'] : '';
                            $repeatPass = isset($_POST['repeat_pass']) ? $_POST['repeat_pass'] : '';
                            if (!empty($newPass) && !empty($repeatPass)) {
                                if ($newPass != $repeatPass) {
                                    $err_msg = __('The passwords do not match.', 'ARMember');
                                    $all_errors = array($err_msg);
                                } else {
                                    if (is_user_logged_in()) {
                                        $user = wp_get_current_user();

                                        $this->arm_reset_password($user, $newPass);
                                        /* Reset Auth Cookies */
                                        wp_cache_delete($user->ID, 'users');
                                        wp_cache_delete($user->user_login, 'userlogins');
                                        global $arm_is_change_password_form_for_logout, $arm_is_change_password_form_for_login;

                                        $arm_is_change_password_form_for_login = 1;
                                        $arm_is_change_password_form_for_logout = 1;

                                        wp_logout();
                                        wp_signon(array('user_login' => $user->user_login, 'user_password' => $newPass), false);
                                        $arm_global_settings->arm_mailer($arm_email_settings->templates->change_password_user, $user->ID);
                                        $cp_success_msg = !empty($form_settings['message']) ? $form_settings['message'] : __('Your password has been changed.', 'ARMember');
                                        $return['status'] = 'success';
                                        $return['message'] = $cp_success_msg;
                                        $return['is_action'] = '';
                                    } else if (isset($_POST['key2']) && isset($_POST['action2']) && $_POST['action2'] == 'armrp' && isset($_POST['login2']) && !empty($_POST['login2'])) {

                                        $user = get_user_by('login', $_POST['login2']);

                                        if (isset($user) && !empty($user)) {
                                            if ($user->ID != '') {
                                                $this->arm_reset_password($user, $newPass);
                                                update_user_meta($user->ID, 'arm_reset_password_key', '');

                                                $login_page_id = isset($arm_global_settings->global_settings['login_page_id']) ? $arm_global_settings->global_settings['login_page_id'] : 0;
                                                if ($login_page_id == 0) {
                                                    $rp_link = wp_login_url();
                                                } else {

                                                    $arm_login_page_url = $arm_global_settings->arm_get_permalink('', $login_page_id);
                                                    $rp_link = $arm_login_page_url;
                                                }

                                                $err_msg = isset($arm_global_settings->common_message['arm_password_reset']) ? $arm_global_settings->common_message['arm_password_reset'] : '';
                                                $loginlink = "<a href='" . $rp_link . "'>";

                                                $err_msg = (!empty($err_msg)) ? $err_msg : __('Your password has been reset.', 'ARMember') . ' <a href="' . $rp_link . '">Log in</a>';
                                                $err_msg = str_replace("[LOGINLINK]", $loginlink, $err_msg);
                                                $err_msg = str_replace("[/LOGINLINK]", "</a>", $err_msg);
                                                $cp_success_msg = __('Your password has been reset.', 'ARMember');
                                                $return['status'] = 'success';
                                                $return['message'] = $err_msg;
                                                $return['is_action'] = 'armrp';
                                            } else {
                                                $err_msg = __('User does not exists.', 'ARMember');
                                                $all_errors = array($err_msg);
                                            }
                                        } else {
                                            $err_msg = __('User does not exists.', 'ARMember');
                                            $all_errors = array($err_msg);
                                        }
                                    }
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
                if (!empty($all_errors) && $all_errors !== TRUE) {
                    $return['status'] = 'error';
                    $return['type'] = 'message';
                    $return['message'] = '<div class="arm_error_msg"><ul>';
                    foreach ($all_errors as $err) {
                        $return['message'] .= '<li>' . $err . '<i class="armfa armfa-times"></i></li>';
                    }
                    $return['message'] .= '</ul></div>';
                } else {
                    $return['status'] = 'success';
                    if (isset($return['type']) && $return['type'] == 'redirect') {

                        $return['message'] = $return['message'];
                    } else {
                        $return['type'] = 'message';
                        $return['message'] = '<div class="arm_success_msg"><ul><li>' . $return['message'] . '</li></ul></div>';
                        $return['is_action'] = isset($return['is_action']) ? $return['is_action'] : '';
                    }
                }
                do_action('arm_after_form_submit_action', $armform, $posted_data);
            }
            echo json_encode($return);
            exit;
        }

        function arm_member_validate_meta_details($armform, $posted_data = array()) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_members_class, $arm_global_settings, $arm_case_types;
            $return = TRUE;
            if (!empty($posted_data) && is_object($armform) && !empty($armform->ID)) {

                $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
                /* Check Spam Filters */
                $formRandomKey = isset($posted_data['form_random_key']) ? sanitize_text_field($posted_data['form_random_key']) : '';
                $validate = TRUE;
                $is_check_spam = true;
                if (in_array($armform->type, array('edit_profile', 'change_password', 'login'))) {
                    $is_check_spam = false;
                }
                if (MEMBERSHIP_DEBUG_LOG == true) {
                    $arm_case_types['shortcode']['protected'] = false;
                    $arm_case_types['shortcode']['message'] = " Need to check spam filter => " . $is_check_spam;
                    $arm_case_types['shortcode']['type'] = "spam_filter";
                    $ARMember->arm_debug_response_log('arm_member_validate_meta_details', $arm_case_types, array(), $wpdb->last_query);
                }
                if ($is_check_spam) {
                    $validate = apply_filters('armember_validate_spam_filter_fields', $validate, $formRandomKey);
                }
                if (!$validate) {
                    $return = array();
                    $err_msg = isset($arm_global_settings->common_message['arm_spam_msg']) ? $arm_global_settings->common_message['arm_spam_msg'] : '';
                    $return['spam'] = (!empty($err_msg)) ? $err_msg : __('Spam detected', 'ARMember');
                } else {
                    $block_list = $arm_global_settings->block_settings;
                    $all_global_settings = $arm_global_settings->global_settings;
                    $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                    $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                    $show_other_form_captcha_field=(isset($armform->settings['show_other_form_captcha_field'])) ? $armform->settings['show_other_form_captcha_field'] : 0;
                    $arm_recaptcha_v3_status=(isset($armform->settings['arm_recaptcha_v3_status'])) ? $armform->settings['arm_recaptcha_v3_status'] : 0;
                    $form_type = $armform->type;

                    $is_hide_username = 0;
                    $is_hide_firstname = 0;
                    $is_hide_lastname = 0;
                    $invalid_username = '';
                    $invalid_email = '';
                    if (!empty($armform->fields)) {
                        foreach ($armform->fields as $field) {
                            $form_field_option = $field['arm_form_field_option'];
                            $field_name = (!empty($form_field_option['meta_key'])) ? $form_field_option['meta_key'] : $form_field_option['id'];


                            if ($field_name == 'user_login') {
                                if (isset($form_field_option['hide_username'])) {
                                    $is_hide_username = $form_field_option['hide_username'];
                                }
                                if (isset($form_field_option['invalid_username'])) {
                                    $invalid_username = $form_field_option['invalid_username'];
                                }
                            } else if ($field_name == 'first_name') {
                                if (isset($form_field_option['hide_firstname'])) {
                                    $is_hide_firstname = $form_field_option['hide_firstname'];
                                }
                            } else if ($field_name == 'last_name') {
                                if (isset($form_field_option['hide_lastname'])) {
                                    $is_hide_lastname = $form_field_option['hide_lastname'];
                                }
                            } else if ($field_name == 'user_email') {
                                if (isset($form_field_option['invalid_message'])) {
                                    $invalid_email = $form_field_option['invalid_message'];
                                }
                            }
                            if (isset($posted_data[$field_name]) && isset($form_field_option['required']) && $form_field_option['required'] == 1) {
                                if (empty($posted_data[$field_name]) && $posted_data[$field_name] == '') {
                                    if (($field_name == 'user_pass' || $field_name == 'repeat_pass') && $posted_data['form_type'] == 'edit_profile') {
                                        continue;
                                    } else if ($field_name == 'first_name' && $is_hide_firstname == 1) {
                                        continue;
                                    } else if ($field_name == 'last_name' && $is_hide_lastname == 1) {
                                        continue;
                                    }

                                    $blank_message = (!empty($form_field_option['blank_message'])) ? $form_field_option['blank_message'] : $form_field_option['label'] . ' can not be left blank';
                                    $errors[$field_name] = $blank_message;
                                } elseif ($form_field_option['type'] == 'email' && ($form_field_option['required'] != 0)) {
                                    /* Input Type Email Validation */
                                    if (!is_email($posted_data[$field_name])) {
                                        $invalid_message = (!empty($form_field_option['invalid_message'])) ? $form_field_option['invalid_message'] : $form_field_option['label'] . ' is not valid';
                                        $errors[$field_name] = $invalid_message;
                                    }
                                }
                            }
                            
                            if (in_array($form_type, array('registration'))) {

                                if ($field_name == 'user_login' && $is_hide_username == 0) {
                                    $sanitized_user_login = sanitize_user($posted_data['user_login']);
                                    /* Check Abusive Words In Username */
                                    $bad_usernames = (isset($block_list['arm_block_usernames'])) ? $block_list['arm_block_usernames'] : array();
                                    if (!empty($bad_usernames) && preg_match_all('/(' . implode('|', $bad_usernames) . ')/i', $sanitized_user_login, $matches) > 0) {
                                        $bad_username_msg = !empty($block_list['arm_block_usernames_msg']) ? $block_list['arm_block_usernames_msg'] : __('Username should not contain bad words.', 'ARMember');
                                        $errors[$field_name] = $bad_username_msg;
                                    } else {
                                        $chk_user_login = $arm_members_class->arm_validate_username($sanitized_user_login, $invalid_username);
                                        /* Check the username */
                                        if (!empty($chk_user_login)) {
                                            $errors[$field_name] = $chk_user_login;
                                        }
                                    }
                                }
                                if ($field_name == 'user_email') {
                                    $user_email = apply_filters('user_registration_email', $posted_data['user_email']);
                                    /* Check Abusive Words In Email Address */
                                    $bad_emails = (isset($block_list['arm_block_emails'])) ? $block_list['arm_block_emails'] : array();
                                    if (!empty($bad_emails) && preg_match_all('/(' . implode('|', $bad_emails) . ')/i', $user_email, $matches) > 0) {
                                        $bad_email_msg = !empty($block_list['arm_block_emails_msg']) ? $block_list['arm_block_emails_msg'] : __('Email should not contain bad words.', 'ARMember');
                                        $errors[$field_name] = $bad_email_msg;
                                    } else {
                                        $chk_user_email = $arm_members_class->arm_validate_email($user_email, $invalid_email);
                                        if (!empty($chk_user_email)) {
                                            $errors[$field_name] = $chk_user_email;
                                        }
                                    }
                                }
                            } elseif (in_array($form_type, array('edit_profile', 'update_profile'))) {
                                $member_id = get_current_user_id();
                                $current_user = get_userdata($member_id);
                                if ($field_name == 'user_email') {
                                    $user_email = apply_filters('user_registration_email', $posted_data['user_email']);
                                    if (strtolower($user_email) != strtolower($current_user->user_email)) {
                                        $bad_emails = (isset($block_list['arm_block_emails'])) ? $block_list['arm_block_emails'] : array();
                                        if (!empty($bad_emails) && preg_match_all('/(' . implode('|', $bad_emails) . ')/i', $user_email, $matches) > 0) {
                                            $bad_email_msg = !empty($block_list['arm_block_emails_msg']) ? $block_list['arm_block_emails_msg'] : __('Email should not contain bad words.', 'ARMember');
                                            $errors[$field_name] = $bad_email_msg;
                                        } else {
                                            $chk_user_email = $arm_members_class->arm_validate_email($user_email, $invalid_email);
                                            if (!empty($chk_user_email)) {
                                                $errors[$field_name] = $chk_user_email;
                                            }
                                        }
                                    }
                                }
                            } elseif (in_array($form_type, array('login'))) {

                            }
                            /* Check if there is file upload */
                            if ($form_field_option['type'] == 'file' || $form_field_option['type'] == 'avatar' || 'profile_cover' == $form_field_option['type']) {
                                $phpFileUploadErrors = array(
                                    0 => __('There is no error, the file uploaded with success.', 'ARMember'),
                                    1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'ARMember'),
                                    2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'ARMember'),
                                    3 => __('The uploaded file was only partially uploaded.', 'ARMember'),
                                    4 => __('No file was uploaded.', 'ARMember'),
                                    6 => __('Missing a temporary folder.', 'ARMember'),
                                    7 => __('Failed to write file to disk.', 'ARMember'),
                                    8 => __('A PHP extension stopped the file upload.', 'ARMember')
                                );

                                if (isset($_FILES[$field_name]) && ($_FILES[$field_name]['error'] === UPLOAD_ERR_OK)) {
                                    $uploads = wp_upload_dir();
                                    if (FALSE !== $uploads['error']) {
                                        $errors['uploads_error'] = $uploads['error'];
                                    }
                                    /* Valid File. */
                                    if ($form_field_option['type'] == 'avatar') {
                                        $allow_ext = '.jpg,.jpeg,.png,.bmp';
                                    } else {
                                        $allow_ext = $form_field_option['allow_ext'];
                                    }
                                    if (!empty($allow_ext)) {
                                        $allowed_ext = explode(',', $allow_ext);
                                        $file_extension = explode('.', $_FILES[$field_name]['name']);
                                        $extension = $file_extension[count($file_extension) - 1];
                                        if (!in_array($extension, $allowed_ext)) {
                                            $errors[$field_name] = __('File type is not allowed.', 'ARMember');
                                        }
                                    }
                                } else {
                                    if (!empty($form_field_option['required']) && $form_field_option['required'] == 1) {
                                        if (empty($posted_data[$field_name]) && $posted_data[$field_name] == '') {
                                            $blank_message = (!empty($form_field_option['blank_message'])) ? $form_field_option['blank_message'] : __('Please upload file.', 'ARMember');
                                            $errors[$field_name] = $blank_message;
                                        }
                                    }
                                }
                            }
                        }

                        if(!empty($arm_recaptcha_site_key) && !empty($arm_recaptcha_private_key) && ($show_other_form_captcha_field==1 || $arm_recaptcha_v3_status==1)){
                               
                            $arm_form_random_key=$posted_data['form_random_key'];
                            if(isset($posted_data['arm_captcha_'.$arm_form_random_key])){
                                require_once(MEMBERSHIP_LIBRARY_DIR . '/recaptchalib/recaptchalib.php');
                                $arm_recaptcha = new ARMember_ReCaptcha($arm_recaptcha_private_key);
                                $arm_recaptcha_response = $arm_recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $posted_data['arm_captcha_'.$arm_form_random_key]);
                                if ($arm_recaptcha_response->success!=1 && !empty($arm_recaptcha_response->errorCodes)) {
                                    $arm_recptcha_invalid_message = (isset($common_messages['arm_recptcha_invalid']) && $common_messages['arm_recptcha_invalid'] != '') ? $common_messages['arm_recptcha_invalid'] : __('Google reCAPTCHA Invalid or Expired. Please reload page and try again.', 'ARMember');
                                    $errors['arm_captcha'] =$arm_recptcha_invalid_message;
                                }
                            }
                        }
                    }
                    if (!empty($errors)) {
                        $return = array();
                        $return = $errors;
                    }
                }
            }
            $return = apply_filters('arm_validate_field_value_before_form_submission', $return, $armform, $posted_data);
            return $return;
        }

        /**
         * Register New User.
         */
        function arm_register_new_member($posted_data = array(), $armform = NULL, $social_signup = '', $is_mail_sent = 1) {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_global_settings, $arm_buddypress_feature, $arm_subscription_plans, $payment_done, $arm_email_settings, $arm_login_from_registration, $arm_manage_communication;
            $arm_errors = new WP_Error();
            $posted_data = apply_filters('arm_before_member_register', $posted_data);
            $user_login = (isset($posted_data['user_login']) && !empty($posted_data['user_login'])) ? $posted_data['user_login'] : $posted_data['user_email'];
            $user_email = (isset($posted_data['user_email'])) ? $posted_data['user_email'] : '';

            if ($social_signup == 'social_signup') {
                $user_pass = wp_generate_password();
            } else {
                $user_pass = (isset($posted_data['user_pass'])) ? $posted_data['user_pass'] : '';
            }

            /* Check the e-mail address */
            $user_email = apply_filters('user_registration_email', $user_email);
            $chk_user_email = $arm_members_class->arm_validate_email($user_email);
            if (!empty($chk_user_email)) {
                $arm_errors->add('arm_reg_error', $chk_user_email);
                $user_email = '';
            }

            $sanitized_user_login = sanitize_user($user_login);
            $chk_user_login = $arm_members_class->arm_validate_username($user_login);
            /* Check the username */
            if (!empty($chk_user_login)) {
                $arm_errors->add('arm_reg_error', $chk_user_login);
                $sanitized_user_login = '';
            }
            /* Check Member password */
            if (empty($user_pass)) {
                $user_pass = apply_filters('arm_member_registration_pass', wp_generate_password(12, false));
            }

            do_action('register_post', $sanitized_user_login, $user_email, $arm_errors);
            remove_all_filters('registration_errors');
            $arm_errors = apply_filters('registration_errors', $arm_errors, $sanitized_user_login, $user_email);

            do_action('arm_remove_third_party_error', $arm_errors);

            if (!empty($arm_errors)) {
                if ($arm_errors->get_error_code()) {
                    return $arm_errors;
                }
            }

            $user_id = wp_create_user($sanitized_user_login, $user_pass, $user_email);
            if (!$user_id) {
                $link_tag = '<a href="mailto:' . get_option('admin_email') . '">' . __('webmaster', 'ARMember') . '</a>';
                $err_msg = isset($arm_global_settings->common_message['arm_user_not_created']) ? $arm_global_settings->common_message['arm_user_not_created'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __("Couldn't register you... please contact the", 'ARMember') . ' ' . $link_tag;
                $arm_errors->add('arm_reg_error', $err_msg);
                return $arm_errors;
            }
            $update_data['ID'] = $user_id;
            $update_data['user_email'] = $user_email;
            if (!empty($posted_data['user_nicename'])) {
                $update_data['user_nicename'] = sanitize_text_field($posted_data['user_nicename']);
            }
            if (!empty($posted_data['user_url'])) {
                $update_data['user_url'] = sanitize_text_field($posted_data['user_url']);
            }
            $display_name = isset($posted_data['display_name']) ? sanitize_text_field($posted_data['display_name']) : '';
            $posted_data['first_name'] = isset($posted_data['first_name']) ? trim(sanitize_text_field($posted_data['first_name'])) : '';
            $posted_data['last_name'] = isset($posted_data['last_name']) ? trim(sanitize_text_field($posted_data['last_name'])) : '';
            if (empty($display_name)) {
                if ($posted_data['first_name'] && $posted_data['last_name']) {
                    /* translators: 1: first name, 2: last name */
                    $display_name = $posted_data['first_name'] . ' ' . $posted_data['last_name'];
                } elseif ($posted_data['first_name']) {
                    $display_name = $posted_data['first_name'];
                } elseif ($posted_data['last_name']) {
                    $display_name = $posted_data['last_name'];
                } else {
                    $display_name = $user_login;
                }
            }
            $update_data['display_name'] = $display_name;
            $pgateway = isset($posted_data['payment_gateway']) ? sanitize_text_field($posted_data['payment_gateway']) : '';

            if ($pgateway == '') {
                $pgateway = isset($posted_data['_payment_gateway']) ? sanitize_text_field($posted_data['_payment_gateway']) : '';
            }


            $user_id = wp_update_user($update_data);
            /* Set Member Status */
            $new_member_status = $arm_global_settings->arm_get_single_global_settings('arm_new_signup_status', 1);
            arm_set_member_status($user_id, $new_member_status);
            /* Store User Meta Data */

            do_action('arm_member_update_meta', $user_id, $posted_data);
            if ($arm_buddypress_feature->isBuddypressFeature) {
                do_action('arm_buddypress_xprofile_field_save', $user_id, $posted_data, 'add');
            }

            $wpdb->update($ARMember->tbl_arm_members, array('arm_user_type' => 1), array('arm_user_id' => $user_id));
            $userData = array('firstname' => $posted_data['first_name'], 'lastname' => $posted_data['last_name'], 'email' => $user_email);

            /**
             * Add Registration Activity Log.
             */
            $plan_ID = isset($posted_data['subscription_plan']) ? intval($posted_data['subscription_plan']) : 0;
            if ($plan_ID == 0) {
                $plan_ID = isset($posted_data['_subscription_plan']) ? intval($posted_data['_subscription_plan']) : 0;
            }

            $register_activity = array(
                'user_id' => $user_id,
                'type' => 'register',
                'item_id' => $plan_ID,
            );
            do_action('arm_record_activity', $register_activity);

            if($is_mail_sent)
            {
                /* Send User Notification */
                arm_new_user_notification($user_id, $user_pass);
            }


            if ($pgateway != 'bank_transfer' && $plan_ID > 0 && $is_mail_sent) {
                /**
                 * Send Email Notification for Successful Payment
                 */
                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_ID, 'user_id' => $user_id, 'action' => 'new_subscription'));
            }
            /* Login new user if form option is enable */
            if ($armform != NULL) {
                $form_settings = $armform->settings;
                $member_status = arm_get_member_status($user_id);
                $is_free_plan = $arm_subscription_plans->isFreePlanExist($plan_ID);
                //$user_pending_pgway = array('bank_transfer', 'paypal', '2checkout');
                $user_pending_pgway = array('paypal', '2checkout');
                $user_pending_pgway = apply_filters('arm_change_pending_gateway_outside', $user_pending_pgway, $plan_ID, $user_id);
                if ((isset($form_settings['auto_login']) && $form_settings['auto_login'] == '1') && $member_status == '1' && (!in_array($pgateway, $user_pending_pgway) || $is_free_plan )) {

                    wp_set_auth_cookie($user_id);
                    wp_set_current_user($user_id, $user_login);
                    update_user_meta($user_id, 'arm_last_login_date', date('Y-m-d H:i:s'));
                    $ip_address = $ARMember->arm_get_ip_address();
                    update_user_meta($user_id, 'arm_last_login_ip', $ip_address);
                    $user_to_pass = wp_get_current_user();
                    $arm_login_from_registration = 1;
                    do_action('wp_login', $user_id, $user_to_pass);
                }
            }
            if (($armform != NULL || $social_signup == 'social_signup') && $user_email != '') {
                global $arm_email_settings, $armemail, $armfname, $armlname, $form_id, $arm_is_social_signup, $arm_social_feature;
                $arm_is_social_signup = false;
                if ($social_signup == 'social_signup') {
                    $arm_is_social_signup = true;
                    $email_tools = array();
                    $social_settings = $arm_social_feature->arm_get_social_settings();
                    if (isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] != '') {
                        $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
                        if (!empty($etool_name)) {
                            $email_tools[$etool_name]['status'] = 1;
                            $email_tools[$etool_name]['list_id'] = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : 0;
                        }
                    }
                } else {
                    $email_tools = $arm_email_settings->arm_get_optin_settings();
                }
                $arm_email_ci = true;
                
                //$arm_opt_ins_cl_mode = $form_settings["form_detail"]["arm_form_settings"]["arm_opt_ins_cl_mode"];
                $arm_opt_ins_cl_mode = isset($form_settings["arm_opt_ins_cl_mode"]) ? $form_settings["arm_opt_ins_cl_mode"] : '';
                if($arm_opt_ins_cl_mode)
                {
                    $arm_email_ci = false;
                    //$arm_opt_ins_cl_form_field = $form_settings["form_detail"]["arm_form_settings"]["arm_opt_ins_cl_form_field"];
                    $arm_opt_ins_cl_form_field = $form_settings["arm_opt_ins_cl_form_field"];
                    $arm_opt_ins_cl_optr = $form_settings["arm_opt_ins_cl_optr"];
                    $arm_opt_ins_cl_val = $form_settings["arm_opt_ins_cl_val"];
                    $arm_submited_val = isset($posted_data[$arm_opt_ins_cl_form_field]) ? maybe_unserialize($posted_data[$arm_opt_ins_cl_form_field]) : '';

                    if(!is_array($arm_submited_val))
                    {
                        if( $arm_opt_ins_cl_optr == "equal" && $arm_submited_val == $arm_opt_ins_cl_val ) {
                            $arm_email_ci = true;
                        }
                        else if($arm_opt_ins_cl_optr == "not_equal" && $arm_submited_val != $arm_opt_ins_cl_val) {
                            $arm_email_ci = true;
                        }
                        else if($arm_opt_ins_cl_optr == "greater" && is_numeric($arm_submited_val) && $arm_submited_val > $arm_opt_ins_cl_val) {
                            $arm_email_ci = true;
                        }
                        else if($arm_opt_ins_cl_optr == "less" && is_numeric($arm_submited_val) && $arm_submited_val < $arm_opt_ins_cl_val) {
                            $arm_email_ci = true;
                        }
                    }
                    else 
                    {
                        if( $arm_opt_ins_cl_optr == "equal" && in_array($arm_opt_ins_cl_val, $arm_submited_val) ) {
                            $arm_email_ci = true;
                        }
                        else if($arm_opt_ins_cl_optr == "not_equal" && !in_array($arm_opt_ins_cl_val, $arm_submited_val)) {
                            $arm_email_ci = true;
                        }
                        else if($arm_opt_ins_cl_optr == "greater") {
                            foreach($arm_submited_val as $arm_submited_value)
                            {
                                if(is_numeric($arm_submited_value) && $arm_submited_value > $arm_opt_ins_cl_val)
                                {
                                    $arm_email_ci = true;
                                    break;
                                }
                            }
                        }
                        else if($arm_opt_ins_cl_optr == "less") {
                            foreach($arm_submited_val as $arm_submited_value)
                            {
                                if(is_numeric($arm_submited_value) && $arm_submited_value < $arm_opt_ins_cl_val)
                                {
                                    $arm_email_ci = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                if (!empty($email_tools) && $arm_email_settings->isOptInsFeature && $arm_email_ci) {
                    $armemail = $user_email;
                    $armfname = trim($posted_data['first_name']);
                    $armlname = trim($posted_data['last_name']);
                    $fetStatus = 0;
                    /* Add User Into Email Tool */
                    foreach ($email_tools as $etool => $et) {
                        if ($social_signup == 'social_signup') {
                            $fetStatus = $et['status'];
                        } else if (isset($form_settings['email'][$etool])) {
                            $form_id = $armform->ID;
                            $fetStatus = (isset($form_settings['email'][$etool]['status'])) ? $form_settings['email'][$etool]['status'] : 0;
                        }
                        if ($fetStatus == '1') {
                            switch ($etool) {
                                case 'aweber':
                                    require_once(MEMBERSHIP_LIBRARY_DIR . '/aweber/arm_addsubscriber_api.php');
                                    break;
                                case 'mailchimp':
                                    require_once(MEMBERSHIP_LIBRARY_DIR . '/mailchimp/store-address.php');
                                    break;
                                case 'constant':
                                    require_once(MEMBERSHIP_LIBRARY_DIR . '/constant_contact/addOrUpdateContact.php');
                                    break;
                                case 'getresponse':
                                    $this->arm_register_getresponse_user();
                                    break;
                                case 'madmimi':
                                    require_once(MEMBERSHIP_LIBRARY_DIR . '/madmimi/madmimi_send_contact.php');
                                    break;
                                case 'mailerlite':
                                    require_once(MEMBERSHIP_LIBRARY_DIR . '/mailerlite/mailerlite_send_contact.php');
                                    break;
                                case 'sendinblue':
                                    $this->arm_register_sendinblue_user();
                                    break;     
                                default:
                                    do_action('armember_send_to_optin', $etool, $et, $posted_data);
                                    break;
                            }
                        }/* END `(isset($form_settings['email'][$etool]) && $fetStatus == '1')` */
                    }
                }/* END `(!empty($email_tools))` */

                if ($arm_email_settings->isOptInsFeature && ( is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php') ) && $arm_email_ci) {

                    $arm_all_general_settings = !empty($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
                    $mailster_enable_double_opt_in = !empty($arm_all_general_settings['mailster']['enable_double_opt_in']) ? $arm_all_general_settings['mailster']['enable_double_opt_in'] : 0;
                    $list = array();
                    $mymail_version = get_option('mymail_version');
                    $mailster_version = get_option('mailster_version');
                    if ($mymail_version >= "2.0.20" || $mailster_version >= "2.2") {
                        $list_id = 0;
                        if (isset($form_settings['email']['mymail'])) {
                            $list_id = (isset($form_settings['email']['mymail']['list_id']) && !empty($form_settings['email']['mymail']['list_id'])) ? $form_settings['email']['mymail']['list_id'] : 0;
                        }
                        if ($social_signup == 'social_signup' && isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'mymail') {
                            $list_id = isset($social_settings['options']['mymail']['list_id']) ? $social_settings['options']['mymail']['list_id'] : '0';
                        }
                        if ($list_id != 0) {
                            $list[] = $list_id;
                        }
                        if(version_compare($mailster_version, '2.3','<'))
                        {
                            mymail_subscribe($user_email, $userData, $list, $mailster_enable_double_opt_in);    
                        }
                        else
                        {
                            mailster_subscribe($user_email, $userData, $list, $mailster_enable_double_opt_in);
                        }
                        
                    }
                }
            }

            /* move this action to default in switch case above */

            /* For affiliateWP insert referral */
            $posted_data['arform_object'] = $armform;
            $posted_data['user_data'] = $userData;
            do_action("arm_after_add_new_user", $user_id, $posted_data);

            return $user_id;
        }

        /**
         * Update Member Details.
         */
        function arm_update_member_profile($posted_data = array()) {


            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_global_settings, $arm_buddypress_feature, $arm_email_settings;
            $arm_errors = new WP_Error();


            $user_ID = get_current_user_id();
            if (is_user_logged_in()) {
                $current_user = get_userdata($user_ID);
                $user_login = isset($posted_data['user_login']) ? sanitize_text_field($posted_data['user_login']) : '';
                unset($posted_data['user_login']);
                $user_email = sanitize_email($posted_data['user_email']);
                $user_email = apply_filters('user_registration_email', $posted_data['user_email']);
                $update_data = array(
                    'ID' => $user_ID,
                    //'user_email' => $user_email
                );

                if (!empty($user_email)) {
                    $update_data['user_email'] = $user_email;
                }
                
                if (isset($posted_data['user_pass']) && !empty($posted_data['user_pass'])) {
                    $update_data['user_pass'] = $posted_data['user_pass'];
                }

                /* Check the e-mail address */
                if (!empty($user_email) && strtolower($user_email)!=strtolower($current_user->user_email)) {


                    $chk_user_email = $arm_members_class->arm_validate_email($user_email);
                    if (!empty($chk_user_email)) {
                        $arm_errors->add('arm_profile_error', $chk_user_email);
                        unset($update_data['user_email']);
                    }
                }


                if ($arm_errors->get_error_code()) {
                    return $arm_errors;
                }

                if (!empty($posted_data['user_url'])) {
                    $update_data['user_url'] = sanitize_text_field($posted_data['user_url']);
                }
                $display_name = isset($posted_data['display_name']) ? sanitize_text_field($posted_data['display_name']) : '';
                if(isset($posted_data['first_name'])) {
                    $posted_data['first_name'] = trim(sanitize_text_field($posted_data['first_name']));
                }
                if(isset($posted_data['last_name'])) {
                    $posted_data['last_name'] = trim(sanitize_text_field($posted_data['last_name']));
                }
                
                if (empty($display_name)) {
                    if ($posted_data['first_name'] && $posted_data['last_name']) {
                        /* translators: 1: first name, 2: last name */
                        $display_name = $posted_data['first_name'] . ' ' . $posted_data['last_name'];
                    } elseif ($posted_data['first_name']) {
                        $display_name = $posted_data['first_name'];
                    } elseif ($posted_data['last_name']) {
                        $display_name = $posted_data['last_name'];
                    } else {
                        $display_name = $user_login;
                    }
                }
                $update_data['display_name'] = $display_name;
                global $arm_is_update_password_form_edit_profile_login, $arm_is_update_password_form_edit_profile_logout;

                $arm_is_update_password_form_edit_profile_logout = 1;
                $arm_is_update_password_form_edit_profile_login = 1;

                $user_ID = wp_update_user($update_data);
                /* For updating username */
                if (is_wp_error($user_ID)) {
                    /* There was an error, probably that user doesn't exist. */
                    $err_msg = isset($arm_global_settings->common_message['arm_user_not_exist']) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
                    $err_msg = (!empty($err_msg)) ? $err_msg : __("User doesn't exist.", 'ARMember');
                    $arm_errors->add('arm_profile_error', $err_msg);
                    return $arm_errors;
                }
                do_action('arm_member_update_meta', $user_ID, $posted_data);
                if ($arm_buddypress_feature->isBuddypressFeature) {
                    do_action('arm_buddypress_xprofile_field_save', $user_ID, $posted_data, 'update');
                }
                /**
                 * Add Update Profile Activity Log.
                 */
                $edit_profile_activity = array(
                    'user_id' => $user_ID,
                    'type' => 'update_profile',
                );
                do_action('arm_record_activity', $edit_profile_activity);

                /*
                * For update user profile action from outside
                */
                do_action('arm_update_profile_external', $user_ID, $posted_data);

                /* Send User Notification */
                wp_update_user_notification($user_ID, $posted_data);
                if (isset($posted_data['user_pass']) && !empty($posted_data['user_pass'])) {
                    if (!wp_check_password($posted_data['user_pass'], $current_user->user_pass, $user_ID))
                    {
                        /*
                        * if password updated in profile, password action from outside
                        */
                        do_action('arm_change_password_external',$user_ID,$posted_data['user_pass']);
                        $arm_global_settings->arm_mailer($arm_email_settings->templates->change_password_user, $user_ID);
                    }
                }
            } else {
                $user_ID = 0;
            }
            return $user_ID;
        }

        function arm_member_update_meta_details($user_ID, $posted_data = array()) {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_subscription_plans, $payment_done, $arm_members_class, $is_multiple_membership_feature, $arm_pay_per_post_feature,$arm_members_activity, $arm_capabilities_global;

            $arm_errors = new WP_Error();
            if(!empty($posted_data['arm_subscription_start_date']) && (empty($posted_data['arm_user_import'] != true) || $posted_data['arm_user_import'] != true)){
                $posted_data['arm_subscription_start_date'] = $posted_data['arm_subscription_start_date']." ".current_time('H:i:s');
            }
            $posted_data = apply_filters('arm_change_user_meta_before_save', $posted_data, $user_ID);

            $payment_gateway = isset($posted_data['pgateway']) ? sanitize_text_field($posted_data['pgateway']) : '';
            $start_time = isset($posted_data['start_time']) ? sanitize_text_field($posted_data['start_time']) : '';
            $plan_cycle = isset($posted_data['arm_selected_payment_cycle']) ? $posted_data['arm_selected_payment_cycle'] : 0;
	    $is_paid_post = (isset($posted_data['arm_paid_post_request']) && !empty($posted_data['arm_paid_post_request'])) ? 1 : 0;
             /* Unset default member fields. */
            $action = isset($posted_data['action']) ? sanitize_text_field($posted_data['action']) : '';
            $unser_array = array('id', 'form', 'user_login', 'user_email', 'repeat_email', 'user_pass',
                'password', 'repeat_pass', 'user_url', 'display_name', 'isAdmin', 'action', 'redirect_to',
                'arm_action', 'page_id', 'form_filter_kp', 'form_filter_st', 'nonce_check', 'arm_plan_type',
                'armFormSubmitBtn', 'arm_subscription_start_date', 'arm_update_user_from_profile', 'arm_total_payable_amount',
                'arm_front_gateway_skin_type', 'arm_front_plan_skin_type', 'arm_user_selected_payment_mode','start_time',
                'arm_user_old_plan', 'arm_is_user_logged_in_flag', 'pgateway',
                'arm_user_payment_mode', 'arm_payment_mode', 'arm_selected_payment_mode', 'arm_selected_payment_cycle'
            );
            foreach ($unser_array as $key) {
                if (isset($posted_data[$key])) {
                    unset($posted_data[$key]);
                }
            }

            
            if (!empty($user_ID) && !empty($posted_data)) {
                $user = new WP_User($user_ID);
                $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                $old_plan = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                $new_plan = $old_plan;
                $planObj = new ARM_Plan($new_plan);

                foreach ($posted_data as $key => $val) {
                    if ($key == 'first_name' || $key == 'last_name') {
                        $val = trim(sanitize_text_field($val));
                    } else if ($key == 'role' || $key == 'roles') {
                        if (!$is_multiple_membership_feature->isMultipleMembershipFeature) {
                            $all_plan_roles = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
                            if (!empty($all_plan_roles) && is_array($all_plan_roles)) {
                                foreach ($all_plan_roles as $key => $value) {
                                    $plan_role = $value['arm_subscription_plan_role'];
                                    if (!empty($plan_role)) {
                                        $user->remove_role($plan_role);
                                    }
                                }
                            }
                        }
                        if (isset($val) && is_array($val) && !empty($val)) {
                            $count = 0;
                            foreach ($val as $v) {
                                if ($count == 0) {
                                    $user->set_role($v);
                                } else {
                                    $user->add_role($v);
                                }
                                $count++;
                            }
                        } else {
                            $user->set_role($val);
                        }
                    } else if ($key == 'arm_user_plan') {
                        
                        $primary_status = arm_get_member_status($user_ID);

                        if ( ( is_array($val) && ( $is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post ) ) ) {
                            if (!empty($val)) {
                                $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                                $old_plan_ids = array_intersect($val, $old_plan_ids);
                                $plan_cycles = $plan_cycle;
                                $plan_roles  = array();
                                foreach ($val as $pid) {
                                    $new_plan = $pid;
                                    $plan_cycle = ( isset($plan_cycles['arm_plan_cycle_'.$new_plan]) && !empty($plan_cycles['arm_plan_cycle_'.$new_plan]) ) ? $plan_cycles['arm_plan_cycle_'.$new_plan] : 0;
                                    if (!empty($new_plan)) {
                                        $planObj = new ARM_Plan($new_plan);
                                        $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                                        $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                                        $old_plan_ids_to_be_removed = array_diff($old_plan_ids, $val);
                                        $old_plan_ids = array_intersect($val, $old_plan_ids);
                                            
                                        $all_plan_roles = $arm_subscription_plans->arm_get_plan_role_by_id($val);
                                        if (!empty($all_plan_roles)) {
                                            foreach ($all_plan_roles as $all_plan_role) {
                                                $plan_roles[] = $all_plan_role['arm_subscription_plan_role'];
                                            }
                                        }
                                        array_unique($plan_roles);

                                        if (!empty($old_plan_ids_to_be_removed)) {
                                            $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids_to_be_removed);
                                            if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                                foreach ($plan_id_role_array as $key => $value) {
                                                    $plan_role      = $value['arm_subscription_plan_role'];
                                                    $remove_plan_id = $value['arm_subscription_plan_id'];

                                                    if ($user->has_cap("armember_access_plan_{$remove_plan_id}")) {
                                                        $user->remove_cap("armember_access_plan_{$remove_plan_id}");
                                                    }

                                                    if (!empty($plan_role) && !in_array($plan_role, $plan_roles)) {
                                                        $user->remove_role($plan_role);
                                                    }
                                                    delete_user_meta($user_ID, 'arm_user_plan_' . $remove_plan_id);
                                                }
                                            }
                                        }



                                        if (!in_array($new_plan, $old_plan_ids)) {

                                            $user->add_cap('armember_access_plan_' . $new_plan);
                                            $old_plan_ids[] = $new_plan;

                                            if ($payment_gateway != 'bank_transfer') {
                                                update_user_meta($user_ID, 'arm_user_plan_ids', array_values($old_plan_ids));
                                                 
                                                    update_user_meta($user_ID, 'arm_user_last_plan', $new_plan);
                                                if($start_time <= strtotime(current_time('mysql'))){
                                                   
                                                    if (!empty($planObj->plan_role)) {

                                                        $user->add_role($planObj->plan_role);
                                                    }
                                                }
                                               

                                                $arm_subscription_plans->arm_add_membership_history($user_ID, $new_plan, 'new_subscription');
                                            }

                                            if ($action == 'update_member' || $action == 'add_member') {
                                                $arm_members_class->arm_manual_update_user_data($user_ID, $new_plan, $posted_data, $plan_cycle);

                                            }
                                        } else {
                                            if ($payment_gateway != 'bank_transfer') {
                                                if($start_time <= strtotime(current_time('mysql'))){
                                                    update_user_meta($user_ID, 'arm_user_plan_ids', array_values($old_plan_ids));
                                                }
                                                else{
                                                    $user_future_plan_arrays[] = $new_plan;
                                                }
                                            }
                                        }
                                    } else {
                                        if (!empty($old_plan_ids)) {
                                            foreach ($old_plan_ids as $opid) {
                                                delete_user_meta($user_ID, 'arm_user_plan_' . $opid);
                                            }

                                            $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
                                            if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                                foreach ($plan_id_role_array as $key => $value) {
                                                    $plan_role = $value['arm_subscription_plan_role'];
                                                    if (!empty($plan_role)) {
                                                        $user->remove_role($plan_role);
                                                        $arm_default_wordpress_role = get_option('default_role','subscriber');
                                                        $user->set_role($arm_default_wordpress_role);
                                                    }
                                                }
                                            }
                                        }
                                        //delete_user_meta($user_ID, 'arm_user_post_ids');
                                        $this->arm_update_paid_post_meta($user_ID);
                                        delete_user_meta($user_ID, 'arm_user_plan_ids');
                                        delete_user_meta($user_ID, 'arm_user_last_plan');
                                    }
                                }
                                
                            } else {
                                if (!empty($old_plan_ids)) {
                                    foreach ($old_plan_ids as $opid) {
                                        if ($user->has_cap("armember_access_plan_{$opid}")) {
                                            $user->remove_cap("armember_access_plan_{$opid}");
                                        }
                                        delete_user_meta($user_ID, 'arm_user_plan_' . $opid);
                                    }
                                    $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
                                    if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                        foreach ($plan_id_role_array as $key => $value) {
                                            $plan_role = $value['arm_subscription_plan_role'];
                                            if (!empty($plan_role)) {
                                                $user->remove_role($plan_role);
                                                $arm_default_wordpress_role = get_option('default_role','subscriber');
                                                $user->set_role($arm_default_wordpress_role);
                                            }
                                        }
                                    }
                                }
                                //delete_user_meta($user_ID, 'arm_user_post_ids');
                                $this->arm_update_paid_post_meta($user_ID);
                                delete_user_meta($user_ID, 'arm_user_plan_ids');
                                delete_user_meta($user_ID, 'arm_user_last_plan');
                            }
                        } else {
                            $new_plan = $val;
                            if (!empty($new_plan)) {
                                $planObj = new ARM_Plan($new_plan);
                                if (!in_array($new_plan, $old_plan_ids)) {

                                    /* Update Last Subscriptions Log Detail */
                                    $user->add_cap('armember_access_plan_' . $new_plan);

                                    if ($is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post) {
                                       
                                        $old_plan_ids[] = $new_plan;
                                        if ($payment_gateway != 'bank_transfer') {
                                            
                                            update_user_meta($user_ID, 'arm_user_plan_ids', array_values($old_plan_ids));

                                            update_user_meta($user_ID, 'arm_user_last_plan', $new_plan);
                                            
                                            
                                            if($start_time <= strtotime(current_time('mysql'))){
                                            
                                             if (!empty($planObj->plan_role)) {

                                                    $user->add_role($planObj->plan_role);
                                                }
                                            
                                            }
                                            
                                           
                                        }
                                    } else {
                                       
                                        do_action('arm_before_update_user_subscription', $user_ID, $new_plan);
                                        $arm_user_old_post_ids = get_user_meta($user_ID, 'arm_user_post_ids', true);                                       
                                        $arm_user_old_gift_ids = get_user_meta($user_ID, 'arm_user_gift_ids', true);

                                        if(!empty($arm_user_old_post_ids)) {
                                            $arm_check_plan_var = 1;
                                            foreach($arm_user_old_post_ids as $arm_old_post_key => $arm_old_post_val) {
                                                if($old_plan == $arm_old_post_key) {
							$arm_check_plan_var = 0;
							break;
                                                }
                                            }
					    if($arm_check_plan_var==1)
					    {
						$user->remove_cap('armember_access_plan_' . $old_plan);
                                                delete_user_meta($user_ID, 'arm_user_plan_'.$old_plan);
					    }
                                        }
					else 
					{
						$user->remove_cap('armember_access_plan_' . $old_plan);
                                                delete_user_meta($user_ID, 'arm_user_plan_'.$old_plan);
					}
                                        if ($payment_gateway != 'bank_transfer') {
                                            $arm_new_update_plan_arr = array();
                                            array_push($arm_new_update_plan_arr, $new_plan);

                                            if(!empty($arm_user_old_post_ids))
                                            {
                                                foreach($arm_user_old_post_ids as $arm_old_post_key => $arm_old_post_val)
                                                {
                                                    if(!in_array($arm_old_post_key, $arm_new_update_plan_arr))
                                                    {
                                                        array_push($arm_new_update_plan_arr, $arm_old_post_key);
                                                    }
                                                }
                                            }

                                            if(!empty($arm_user_old_gift_ids)) {
                                                foreach($arm_user_old_gift_ids as $arm_old_gift_key => $arm_old_gift_val)
                                                {
                                                    if(!in_array($arm_old_gift_val, $arm_new_update_plan_arr))
                                                    {
                                                        array_push($arm_new_update_plan_arr, $arm_old_gift_val);
                                                    }
                                                }
                                            }   
                                            update_user_meta($user_ID, 'arm_user_plan_ids', $arm_new_update_plan_arr); 
                                            
                                            update_user_meta($user_ID, 'arm_user_last_plan', $new_plan);
                                            if($start_time <= strtotime(current_time('mysql'))){
                                                 
                                                
                                                if (!empty($planObj->plan_role)) {
                                                    //$user->set_role($planObj->plan_role);
                                                    $user->add_role($planObj->plan_role);
                                                }
                                             }
                                            
                                        }
                                    }

                                    if ($payment_gateway != 'bank_transfer') {
                                        $arm_subscription_plans->arm_add_membership_history($user_ID, $new_plan, 'new_subscription');
                                    }
                                    if ($action == 'update_member' || $action == 'add_member') {
                                        $arm_members_class->arm_manual_update_user_data($user_ID, $new_plan, $posted_data, $plan_cycle);
                                    }
                                } else {
                                    if ($payment_gateway != 'bank_transfer') {
                                        update_user_meta($user_ID, 'arm_user_plan_ids', array_values($old_plan_ids));
                                    }
                                }



                           
                            } else {
                                if (!empty($old_plan_ids)) {
                                    foreach ($old_plan_ids as $opid) {
                                        if ($user->has_cap("armember_access_plan_{$opid}")) {
                                            $user->remove_cap("armember_access_plan_{$opid}");
                                        }
                                        delete_user_meta($user_ID, 'arm_user_plan_' . $opid);
                                    }
                                    $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
                                    if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                        foreach ($plan_id_role_array as $key => $value) {
                                            $plan_role = $value['arm_subscription_plan_role'];
                                            if (!empty($plan_role)) {
                                                $user->remove_role($plan_role);
                                                $arm_default_wordpress_role = get_option('default_role','subscriber');
                                                $user->set_role($arm_default_wordpress_role);
                                            }
                                        }
                                    }
                                }
                                //delete_user_meta($user_ID, 'arm_user_post_ids');
                                $this->arm_update_paid_post_meta($user_ID);
                                delete_user_meta($user_ID, 'arm_user_plan_ids');
                                delete_user_meta($user_ID, 'arm_user_last_plan');
                            }
                        }
                        if (!empty($val)) {
                            
                            $current_user_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                            $current_user_plan_ids = !empty($current_user_plan_ids) ? $current_user_plan_ids : array(); 
                            
                            if ( ( is_array($val) && ( $is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post ) ) ) {
                                
                                $user_future_plan_arrays = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                                $user_future_plan_arrays = !empty($user_future_plan_arrays) ? $user_future_plan_arrays : array(); 
                                $i = 0;
                                foreach($val as $plan_key => $plan_val){
                                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                    $userPlanDatameta = get_user_meta($user_ID, 'arm_user_plan_' . $plan_val, true);
                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                    $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                    $is_user_in_trial = isset($planData['arm_is_trial_plan']) ? $planData['arm_is_trial_plan'] : 0;
                                    //$plan_start_date = $planData['arm_start_plan'];
                                    $plan_start_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : $planData['arm_start_plan'];
                                    if(!empty($plan_start_date)){
                                        if($plan_start_date > strtotime(current_time('mysql'))){
                                            if(in_array($plan_val, $current_user_plan_ids)){
                                                if($is_user_in_trial != 1){
                                                    $i++;
                                                    unset($current_user_plan_ids[array_search($plan_val, $current_user_plan_ids)]);
                                                    $user_future_plan_arrays[] = $plan_val; 
                                                }
                                            }
                                        }
                                    }

                                }

                                if($i>0){

                                       
                                    update_user_meta($user_ID, 'arm_user_plan_ids', array_values($current_user_plan_ids)); 
                                    
                                    update_user_meta($user_ID, 'arm_user_future_plan_ids', array_values($user_future_plan_arrays));
                                }

                            }else{

                                $user_future_plan_arrays = array(); 
                                $user_future_plan_arrays2 = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                                if(!is_array($user_future_plan_arrays2)){
                                    $user_future_plan_arrays2 = explode(',', $user_future_plan_arrays2);
                                }
                                foreach ($user_future_plan_arrays2 as $user_future_plan_key => $user_future_plan_value) {
                                    $future_user_plan_data = get_user_meta($user_ID, 'arm_user_plan_'.$user_future_plan_value, true);
                                    if (!empty($future_user_plan_data)) {
                                        if (isset($future_user_plan_data['arm_current_plan_detail']['arm_subscription_plan_post_id']) && !empty($future_user_plan_data['arm_current_plan_detail']['arm_subscription_plan_post_id'])) {
                                            $user_future_plan_arrays[] = $user_future_plan_value;
                                        }
                                    }
                                }
                            
                                $current_user_plan_data = get_user_meta($user_ID, 'arm_user_plan_'.$val, true);

                                if(!empty($current_user_plan_data)) {
                                    if(isset($current_user_plan_data['arm_current_plan_detail']['arm_subscription_plan_type']) && $current_user_plan_data['arm_current_plan_detail']['arm_subscription_plan_type'] == 'paid_finite') {
                                        $start_time = $current_user_plan_data['arm_started_plan_date'];
                                    }
                                }

                                if(!empty($start_time)){
                                    if($start_time > strtotime(current_time('mysql'))){
                                        if(in_array($val, $current_user_plan_ids)){

                                            $is_user_in_trial = isset($current_user_plan_data['arm_is_trial_plan']) ? $current_user_plan_data['arm_is_trial_plan'] : 0;
                                            if($is_user_in_trial != 1){
                                                unset($current_user_plan_ids[array_search($val, $current_user_plan_ids)]);
                                                $user_future_plan_arrays[] = $val; 
                                            }
                                            
                                        }
                                    }
                                }
    
                                update_user_meta($user_ID, 'arm_user_future_plan_ids', array_values($user_future_plan_arrays));
                                
                                update_user_meta($user_ID, 'arm_user_plan_ids', array_values($current_user_plan_ids)); 
                               
                            }
                            
                        }
                        
                        continue;
                    } else if ($key == 'arm_primary_status') {
                        if ($val == 1) {
                            $secondary_status = 0;
                        } else {
                            $secondary_status = arm_get_member_status($user_ID, 'secondary');
                        }
                        arm_set_member_status($user_ID, $val, $secondary_status);
                    } else if($key == 'arm_user_future_plan'){
                        if (!empty($val)) {
                            $future_user_plan_ids = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                            $future_user_plan_ids = !empty($future_user_plan_ids) ? $future_user_plan_ids : array();
                            if(!empty($future_user_plan_ids)){
                                $common_future_plans = array_intersect($future_user_plan_ids, $val);
                                $common_future_plans = !empty($common_future_plans) ? $common_future_plans : array(); 
                                update_user_meta($user_ID, 'arm_user_future_plan_ids', array_values($common_future_plans));
                            }
                            $diff_future_plans = array_diff($future_user_plan_ids, $val);
                            if(!empty($diff_future_plans)){
                                foreach($diff_future_plans as $diff_fp){
                                    delete_user_meta($user_ID, 'arm_user_plan_'.$diff_fp);
                                }
                            }
                        }
                        continue;
                    }
                    $pattern1 = '/^((.*)\_arm\_hidden)/';
                    if (preg_match($pattern1, $key)) {
                        $key = str_replace('_arm_hidden', '', $key);
                        unset($posted_data[$key]);
                    }
                    update_user_meta($user_ID, $key, $val);
                }
            }
        }

        function arm_retrieve_password() {
            global $wp, $wpdb, $wp_hasher, $current_user, $current_site, $arm_errors, $ARMember, $arm_email_settings, $arm_global_settings;
            $arm_errors = new WP_Error();
            $is_user_data_exist = 0;
            if (empty($_POST['user_login'])) {
                $err_msg = __('Enter a username or e-mail address.', 'ARMember');
                $arm_errors->add('empty_username', $err_msg);
            } else if (strpos($_POST['user_login'], '@')) {
                $user_data = get_user_by('email', trim(sanitize_email($_POST['user_login'])));
                $is_user_data_exist = 1;
                if (empty($user_data)) {
                    $err_msg = isset($arm_global_settings->common_message['arm_no_registered_email']) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
                    $err_msg = (!empty($err_msg)) ? $err_msg : __('There is no user registered with that email address.', 'ARMember');
                    $arm_errors->add('invalid_email', $err_msg);
                }
            } else {
                $login = trim(sanitize_text_field($_POST['user_login']));
                $user_data = get_user_by('login', $login);
				if (empty($user_data)) {
                    $err_msg = isset($arm_global_settings->common_message['arm_no_registered_email']) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
                    $err_msg = (!empty($err_msg)) ? $err_msg : __('There is no user registered with that username.', 'ARMember');
                    $arm_errors->add('invalid_username', $err_msg);
                }
                $is_user_data_exist = 1;
            }
			
			if( !empty($user_data) && empty($arm_errors) )
			{
            	$user_data_param = ($is_user_data_exist == 1) ? $user_data : false;
            	do_action('lostpassword_post', $arm_errors, $user_data_param);
			}

            if ($arm_errors->get_error_code())
                return $arm_errors;

            if (!$user_data) {
                $err_msg = isset($arm_global_settings->common_message['arm_no_registered_email']) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __('Invalid username or e-mail.', 'ARMember');
                $arm_errors->add('invalidcombo', $err_msg);
                return $arm_errors;
            }

            /* redefining user_login ensures we return the right case in the email */
            $user_id = $user_data->ID;
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;
            /**
             * Add patch for WordPress 4.4+
             */
            if (function_exists('get_password_reset_key')) {
                $key = get_password_reset_key($user_data);
                if (is_wp_error($key)) {

                    $arm_errors = new WP_Error();
                     $err_msg = isset($arm_global_settings->common_message['arm_reset_pass_not_allow']) ? $arm_global_settings->common_message['arm_reset_pass_not_allow'] : '';
                    $err_msg = (!empty($err_msg)) ? $err_msg : __('Password reset is not allowed for this user.', 'ARMember');
                    $arm_errors->add('no_password_reset', $err_msg);
                    return $key;
                }

               
            } else {

                
                do_action('retreive_password', $user_login);  /* Misspelled and deprecated */
                do_action('retrieve_password', $user_login);

                $allow = apply_filters('allow_password_reset', true, $user_data->ID);

                if (!$allow) {
                    $err_msg = isset($arm_global_settings->common_message['arm_reset_pass_not_allow']) ? $arm_global_settings->common_message['arm_reset_pass_not_allow'] : '';
                    $err_msg = (!empty($err_msg)) ? $err_msg : __('Password reset is not allowed for this user.', 'ARMember');
                    return new WP_Error('no_password_reset', $err_msg);
                } else if (is_wp_error($allow)) {
                    return $allow;
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
                    return new WP_Error('no_password_key_update', __('Could not save password reset key to database.', 'ARMember'));
                }
            }
            update_user_meta($user_id, 'arm_reset_password_key', $key);
            $change_password_page_id = isset($arm_global_settings->global_settings['change_password_page_id']) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
            if ($change_password_page_id == 0) {
                $rp_link = network_site_url("wp-login.php?action=armrp&key=" . rawurlencode($key) . "&login=" . rawurlencode($user_login), 'login');
            } else {

                $arm_change_password_page_url = $arm_global_settings->arm_get_permalink('', $change_password_page_id);

                $arm_change_password_page_url = $arm_global_settings->add_query_arg('action', 'armrp', $arm_change_password_page_url);
                $arm_change_password_page_url = $arm_global_settings->add_query_arg('key', rawurlencode($key), $arm_change_password_page_url);
                $arm_change_password_page_url = $arm_global_settings->add_query_arg('login', rawurlencode($user_login), $arm_change_password_page_url);

                $rp_link = $arm_change_password_page_url;
            }


            $varification_key = get_user_meta($user_id, 'arm_user_activation_key', true);
            $user_status = arm_get_member_status($user_id);
            if($user_status == 3){
                $rp_link =  $arm_global_settings->add_query_arg('varify_key', rawurlencode($varification_key), $rp_link);
            }


            /* Now Create Password Reset Link */

            if (is_multisite()) {
                $blogname = $current_site->site_name;
            } else {
                /* The blogname option is escaped with esc_html on the way into the database in sanitize_option */
                /* we want to reverse this for the plain text arena of emails. */
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            }
            $temp_detail = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->forgot_passowrd_user);
            if ($temp_detail->arm_template_status == '1') {
                $title = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_subject, $user_id, 0);

                $message = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_content, $user_id, 0, 0, $key);

                $message = str_replace('{ARM_RESET_PASSWORD_LINK}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
                $message = str_replace('{VAR1}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
            } else {
                $title = $blogname . ' ' . __('Password Reset', 'ARMember');
                $message = __('Someone requested that the password be reset for the following account:', 'ARMember') . "\r\n\r\n";
                $message .= network_home_url('/') . "\r\n\r\n";
                $message .= __('Username', 'ARMember') . ": " . $user_login . "\r\n\r\n";
                $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'ARMember') . "\r\n\r\n";
                $message .= __('To reset your password, visit the following address:', 'ARMember') . " " . $rp_link . "\r\n\r\n";
            }

            remove_all_filters('retrieve_password_message');
            remove_all_filters('retrieve_password_title');
            $title = apply_filters('retrieve_password_title', $title, $user_data->ID);
            $message = apply_filters('retrieve_password_message', $message, $key, $user_data->user_login, $user_data);
            $send_mail = $arm_global_settings->arm_wp_mail('', $user_email, $title, $message);
            
            if ($message && !$send_mail) {
                $err_msg = isset($arm_global_settings->common_message['arm_email_not_sent']) ? $arm_global_settings->common_message['arm_email_not_sent'] : '';
                $err_msg = (!empty($err_msg)) ? $err_msg : __('The e-mail could not be sent.', 'ARMember') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...', 'ARMember');
                return new WP_Error('no_password_reset', $err_msg);
            }
            return true;
        }

        function arm_reset_password($user, $new_pass) {
            global $wp, $wpdb, $current_user, $ARMember;

            do_action('password_reset', $user, $new_pass);

            wp_set_password($new_pass, $user->ID);

            do_action_ref_array('arm_user_password_changed', array(&$user));

            do_action('arm_change_password_external',$user->ID,$new_pass);
        }

        function arm_check_exist_field() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_check_exist_field') {
                $return = array('status' => 'success', 'check' => 1);
                switch ($_REQUEST['field']) {
                    case 'user_login':
                        if (username_exists(sanitize_user($_REQUEST['value']))) {
                            $return = array('status' => 'error', 'check' => 0);
                        } else {
                            $return = array('status' => 'success', 'check' => 1);
                        }
                        break;
                    case 'user_email':
                        if (is_user_logged_in()) {
                            $current_user = wp_get_current_user();
                            if (strtolower($current_user->user_email) == strtolower(sanitize_email($_REQUEST['value']))) {
                                $return = array('status' => 'success', 'check' => 1);
                                echo json_encode($return);
                                exit;
                            }
                        }
                        if (email_exists($_REQUEST['value'])) {
                            $return = array('status' => 'error', 'check' => 0);
                        } else {
                            $return = array('status' => 'success', 'check' => 1);
                        }
                        break;
                    default:
                        break;
                }
                echo json_encode($return);
                exit;
            }
        }

        function arm_filter_form_field_options($field_options = array()) {
            global $wp, $wpdb, $current_user, $ARMember;
            if (!empty($field_options['type'])) {
                $type = $field_options['type'];
            } else {
                $type = 'text';
            }
            $field_id = isset($field_options['meta_key']) ? $field_options['meta_key'] : '';
            if (empty($field_id)) {
                $field_id = $type . "_" . wp_generate_password(5, false, false);
            }
            if ($type == "password") {
                $field_id = "user_pass";
            }
            
            $default_options = array(
                'id' => $field_id,
                'label' => '',
                'placeholder' => '',
                'type' => $type,
                'sub_type' => '',
                'value' => '',
                'bg_color' => '',
                'readonly' => 0,
                'class' => '',
                'padding' => array(),
                'margin' => array(),
                'options' => array(),
                'allow_ext' => '',
                'file_size_limit' => 2,
                'meta_key' => $field_id,
                'required' => 0,
                'hide_username' => 0,
                'hide_firstname' => 0,
                'hide_lastname' => 0,
                'blank_message' => __('This field can not be left blank.', 'ARMember'),
                'validation_type' => 'custom_validation_none',
                'regular_expression' => '',
                'invalid_message' => __('Please enter valid data.', 'ARMember'),
                'invalid_username' => __('This username is invalid. Please enter a valid username.', 'ARMember'),
                'invalid_firstname' => __('This first name is invalid. Please enter a valid first name.', 'ARMember'),
                'invalid_lastname' => __('This last name is invalid. Please enter a valid last name.', 'ARMember'),
                'default_field' => 0,
                'cal_localization' => '',
                'description' => '',
                'prefix' => '',
                'suffix' => '',
                '_builtin' => 0,
                'default_val' => array(),
                'mapfield' => 0,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $field_options = shortcode_atts($default_options, $field_options);
            $field_options['label'] = isset($field_options['label']) ? stripslashes($field_options['label']) : '';
            $field_options['placeholder'] = isset($field_options['placeholder']) ? stripslashes($field_options['placeholder']) : '';
            $field_options['blank_message'] = isset($field_options['blank_message']) ? stripslashes($field_options['blank_message']) : '';
            $field_options['invalid_message'] = isset($field_options['invalid_message']) ? stripslashes($field_options['invalid_message']) : '';
            if (in_array($field_options['type'], array('radio')) && empty($field_options['default_val']) && !empty($field_options['options'])) {
                $fieldOptValues = array_values($field_options['options']);
                $firstVal = array_shift($fieldOptValues);
                reset($field_options['options']);
                $firstVal = stripslashes($firstVal);
                $new_data = explode(':', $firstVal);
                $key = isset($new_data[0]) ? $new_data[0] : $firstVal;
                if (isset($new_data[1]) && $new_data[1] != '') {
                    $key = $new_data[1];
                }
                $field_options['default_val'] = $key;
            }
            if (empty($field_options['meta_key'])) {
                $field_options['meta_key'] = $field_options['id'];
            }
            /* Set Field Values. */
            $cur_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
            if (isset($_REQUEST['arm_setup_preview'])) {
                return $field_options;
            }
            if (is_user_logged_in() && !in_array($cur_page, array('arm_form_settings', 'arm_manage_forms'))) {
                if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_member' && is_admin()) {
                    $user_info = get_userdata(intval($_REQUEST['id']));
                }
                if (!is_admin()) {
                    $user_info = wp_get_current_user();
                }
                if (!empty($user_info) && !in_array($field_options['type'], array('submit', 'password', 'section', 'html'))) {
                    switch ($field_options['meta_key']) {
                        case 'user_login':
                        case 'username':
                            $field_options['value'] = $user_info->user_login;
                            break;
                        case 'user_email':
                        case 'email':
                            $field_options['value'] = $user_info->user_email;
                            break;
                        case 'first_name':
                        case 'firstname':
                        case 'fname':
                        case 'user_firstname':
                            $field_options['value'] = $user_info->first_name;
                            break;
                        case 'lastname':
                        case 'last_name':
                        case 'lname':
                        case 'user_lastname':
                            $field_options['value'] = $user_info->last_name;
                            break;
                        case 'display_name':
                        case 'full_name':
                            $field_options['value'] = $user_info->display_name;
                            break;
                        case 'user_url':
                        case 'website':
                            $field_options['value'] = $user_info->user_url;
                            break;
                        case 'arm_primary_status':
                            $field_options['value'] = arm_get_member_status($user_info->ID);
                            break;
                        case 'arm_secondary_status':
                            $field_options['value'] = arm_get_member_status($user_info->ID, 'secondary');
                            break;
                        case 'html':
                            break;
                        default:
                            $field_options['value'] = get_user_meta($user_info->ID, $field_options['meta_key'], true);
                            break;
                    }
                }
            }
            return $field_options;
        }

        function arm_default_field_options() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $role_options = $arm_global_settings->arm_get_all_roles();
            $fields = array(
                'text' => array(
                    'label' => __('Textbox', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'text',
                    'required' => 0,
                    'blank_message' => __('Text field can not be left blank.', 'ARMember'),
                ),
                'password' => array(
                    'label' => __('Password', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'password',
                    'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                    'required' => 0,
                    'blank_message' => __('Password can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Please enter valid password.', 'ARMember'),
                ),
                'textarea' => array(
                    'label' => __('Textarea', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'textarea',
                    'required' => 0,
                    'blank_message' => __('This Field can not be left blank.', 'ARMember'),
                ),
                'checkbox' => array(
                    'label' => __('Checkbox', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'checkbox',
                    'required' => 0,
                    'options' => array('checkbox1' => 'Checkbox1', 'checkbox2' => 'Checkbox2'),
                    'blank_message' => __('Please check atleast one option.', 'ARMember'),
                ),
                'radio' => array(
                    'label' => __('Radio Button', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'radio',
                    'required' => 0,
                    'options' => array('radio1' => 'Radio1', 'radio2' => 'Radio2'),
                    'blank_message' => __('Please select one option.', 'ARMember'),
                ),
                'select' => array(
                    'label' => __('Dropdown', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'select',
                    'required' => 0,
                    'options' => array('' => 'Select Option', 'option1' => 'Option1'),
                    'blank_message' => __('Please select atleast one option.', 'ARMember'),
                ),
                'date' => array(
                    'label' => __('Date', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'date',
                    'required' => 0,
                    'value' => '',
                    'blank_message' => __('Please select date.', 'ARMember'),
                    'invalid_message' => __('Invalid Date.', 'ARMember'),
                    'cal_localization' => '',
                ),
                'file' => array(
                    'label' => __('File Upload', 'ARMember'),
                    'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                    'type' => 'file',
                    'required' => 0,
                    'value' => '',
                    'allow_ext' => '',
                    'file_size_limit' => '2',
                    'blank_message' => __('Please select file.', 'ARMember'),
                    'invalid_message' => __('Invalid file selected.', 'ARMember'),
                ),
                'avatar' => array(
                    'label' => __('Avatar', 'ARMember'),
                    'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                    'type' => 'avatar',
                    'required' => 0,
                    'value' => '',
                    'meta_key' => 'avatar',
                    'allow_ext' => '',
                    'file_size_limit' => '2',
                    'blank_message' => __('Please select file.', 'ARMember'),
                    'invalid_message' => __('Invalid file selected.', 'ARMember'),
                ),
                'profile_cover' => array(
                    'label' => esc_html__( 'Profile Cover', 'ARMember' ),
                    'placeholder' => esc_html__( 'Drop file here or click to select.', 'ARMember' ),
                    'type' => 'profile_cover',
                    'required' => 0,
                    'value' => '',
                    'meta_key' => 'profile_cover',
                    'allow_ext' => '',
                    'file_size_limit' => '2',
                    'blank_message' => esc_html__( 'Please select file.', 'ARMember' ),
                    'invalid_message' => esc_html__( 'Drop file here or click to select.', 'ARMember')
                ),
                'roles' => array(
                    'label' => __('Roles', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'roles',
                    'options' => $role_options,
                    'sub_type' => 'select',
                    'meta_key' => 'roles',
                    'required' => 0,
                    'blank_message' => __('Please select atleast one role.', 'ARMember'),
                ),
                'hidden' => array(
                    'label' => __('Hidden Field', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'hidden',
                    'required' => 0,
                    'blank_message' => '',
                ),
                'html' => array(
                    'label' => __('Html Area', 'ARMember'),
                    'value' => __('Html Text', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'html',
                    'required' => 0,
                    'blank_message' => '',
                ),
                'section' => array(
                    'label' => __('Divider', 'ARMember'),
                    'value' => __('Section', 'ARMember') . '<hr/>',
                    'bg_color' => '#F9F9F9',
                    'padding' => array(),
                    'margin' => array(),
                    'placeholder' => '',
                    'type' => 'section',
                    'options' => array(),
                    'required' => 0,
                    'blank_message' => '',
                ),
                'rememberme' => array(
                    'id' => 'rememberme',
                    'label' => __('Remember me', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'rememberme',
                    'meta_key' => 'rememberme',
                    'required' => 0,
                ),
                'repeat_pass' => array(
                    '_builtin' => 1,
                    'id' => 'repeat_pass',
                    'label' => __('Confirm Password', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'repeat_pass',
                    'options' => array('strength_meter' => 0, 'strong_password' => 0, 'minlength' => 0, 'maxlength' => '', 'special' => 0, 'numeric' => 0, 'uppercase' => 0, 'lowercase' => 0),
                    'meta_key' => 'repeat_pass',
                    'required' => 1,
                    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Passwords don\'t match.', 'ARMember'),
                ),
                'repeat_email' => array(
                    '_builtin' => 1,
                    'id' => 'repeat_email',
                    'label' => __('Confirm Email Address', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'repeat_email',
                    'meta_key' => 'repeat_email',
                    'required' => 1,
                    'blank_message' => __('Confirm Email Address can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Please enter email address again.', 'ARMember'),
                ),
            );

            $preset_fields = $this->arm_get_db_form_fields(true);
            if (!empty($preset_fields)) {
                $fields = array_merge($preset_fields, $fields);
            }
            return $fields;
        }

        function arm_social_profile_field_types() {
            $socialProfileFields = array(
                'facebook' => __('Facebook', 'ARMember'),
                'twitter' => __('Twitter', 'ARMember'),
                'linkedin' => __('LinkedIn', 'ARMember'),
                'vk' => __('VK', 'ARMember'),
                'instagram' => __('Instagram', 'ARMember'),
                'pinterest' => __('Pinterest', 'ARMember'),
                'youtube' => __('Youtube', 'ARMember'),
                'dribbble' => __('Dribbble', 'ARMember'),
                'delicious' => __('Delicious', 'ARMember'),
                'tumblr' => __('Tumblr', 'ARMember'),
                'vine' => __('Vine', 'ARMember'),
                'skype' => __('Skype', 'ARMember'),
                'whatsapp' => __('WhatsApp', 'ARMember'),
                'tiktok' => __('Tiktok', 'ARMember')
            );
            return $socialProfileFields;
        }

        function arm_default_preset_user_fields() {
            global $wp, $wpdb, $current_user, $ARMember, $arm_social_feature;
            $countries = $this->arm_get_countries();
            $countries = array_merge(array('0' => 'Country/Region'), $countries);
            foreach ($countries as $key => $country) {
                $countries[$key] = "{$country}:{$key}";
            }
            $defaultPresetFields = array(
                'first_name' => array(
                    '_builtin' => 1,
                    'id' => 'first_name',
                    'label' => __('First Name', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'text',
                    'meta_key' => 'first_name',
                    'required' => 0,
                    'hide_firstname' => 0,
                    'invalid_firstname' => __('This first name is invalid. Please enter a valid first name.', 'ARMember'),
                ),
                'last_name' => array(
                    '_builtin' => 1,
                    'id' => 'last_name',
                    'label' => __('Last Name', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'text',
                    'meta_key' => 'last_name',
                    'required' => 0,
                    'hide_lastname' => 0,
                    'invalid_lastname' => __('This last name is invalid. Please enter a valid last name.', 'ARMember'),
                ),
                'display_name' => array(
                    '_builtin' => 1,
                    'id' => 'display_name',
                    'type' => 'text',
                    'label' => __('Profile Display Name', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => 'display_name',
                    'required' => 0
                ),
                'user_login' => array(
                    '_builtin' => 1,
                    'id' => 'user_login',
                    'label' => __('Username', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'text',
                    'meta_key' => 'user_login',
                    'required' => 1,
                    'hide_username' => 0,
                    'blank_message' => __('Username can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Please enter valid username.', 'ARMember'),
                ),
                'user_email' => array(
                    '_builtin' => 1,
                    'id' => 'user_email',
                    'label' => __('Email Address', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'email',
                    'options' => array('is_confirm_email' => 0),
                    'meta_key' => 'user_email',
                    'required' => 1,
                    'blank_message' => __('Email Address can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Please enter valid email address.', 'ARMember'),
                ),
                'user_pass' => array(
                    '_builtin' => 1,
                    'id' => 'user_pass',
                    'label' => __('Password', 'ARMember'),
                    'placeholder' => '',
                    'type' => 'password',
                    'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1, 'is_confirm_pass' => 0),
                    'meta_key' => 'user_pass',
                    'required' => 1,
                    'blank_message' => __('Password can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Please enter valid password.', 'ARMember'),
                ),
                'gender' => array(
                    '_builtin' => 1,
                    'id' => 'gender',
                    'type' => 'radio',
                    'label' => __('Gender', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => 'gender',
                    'required' => 0,
                    'options' => array('male' => 'Male', 'female' => 'Female'),
                    'blank_message' => __('Please select one.', 'ARMember'),
                ),
                'user_url' => array(
                    '_builtin' => 1,
                    'id' => 'user_url',
                    'type' => 'url',
                    'label' => __('Website (URL)', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => 'user_url',
                    'required' => 0,
                    'blank_message' => __('Website (URL) can not be left blank.', 'ARMember'),
                    'invalid_message' => __('Invalid URL', 'ARMember'),
                ),
                'country' => array(
                    '_builtin' => 1,
                    'id' => 'country',
                    'type' => 'select',
                    'label' => __('Country/Region', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => 'country',
                    'required' => 0,
                    'options' => $countries, /* array('' => 'Country/Region', 'option1' => 'Option1'), */
                    'blank_message' => __('Please select atleast one option.', 'ARMember'),
                ),
                'description' => array(
                    '_builtin' => 1,
                    'id' => 'description',
                    'type' => 'textarea',
                    'label' => __('Biography', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => 'description',
                    'required' => 0,
                    'blank_message' => __('Biography can not be left blank.', 'ARMember'),
                ),
                'social_fields' => array(
                    '_builtin' => 1,
                    'id' => 'social_fields',
                    'type' => 'social_fields',
                    'label' => __('Social Profile Fields', 'ARMember'),
                    'placeholder' => '',
                    'meta_key' => '',
                    'required' => 0,
                    'options' => array('facebook', 'twitter', 'linkedin'),
                    'blank_message' => '',
                ),
            );
            return $defaultPresetFields;
        }

        function arm_get_db_form_fields($merge = false) {
            global $wp, $wpdb, $current_user, $ARMember;
            $presetFormFields = get_option('arm_preset_form_fields', '');
            $dbFormFields = maybe_unserialize($presetFormFields);
            if(!is_array($dbFormFields))
            {
                if(!empty($dbFormFields))
                {
                    $dbFormFields = maybe_unserialize($dbFormFields);
                    if(!is_array($dbFormFields))
                    {
                        $dbFormFields = maybe_unserialize($dbFormFields);
                        if(!is_array($dbFormFields))
                        {
                            $dbFormFields = array();
                        }
                        else {
                            $arm_update_preset_form_field_option = 1;
                        }
                    }
                    else {
                        $arm_update_preset_form_field_option = 1;
                    }
                }
                else {
                    $dbFormFields = array();
                }
            }

            if( is_array($dbFormFields) && (!empty($arm_update_preset_form_field_option) || empty($dbFormFields['default']) ) )
            {
                if(empty($dbFormFields['default']))
                {
                    $arm_default_FormFields = $this->arm_default_preset_user_fields();
                    $dbFormFields['default'] = $arm_default_FormFields;
                }
                update_option('arm_preset_form_fields', $dbFormFields);
            }
            if ($merge) {
                $dbFormFields['default'] = isset($dbFormFields['default']) ? $dbFormFields['default'] : array();
                $dbFormFields['other'] = isset($dbFormFields['other']) ? $dbFormFields['other'] : array();
                $dbFormFields = array_merge($dbFormFields['default'], $dbFormFields['other']);
            }
            if (array_key_exists("arm_captcha",$dbFormFields)){
                unset($dbFormFields['arm_captcha']);
            }    
            return $dbFormFields;
        }

        function arm_db_add_preset_form_field($field = array(), $field_id = 0) {
            $field['meta_key'] = (isset($field['meta_key']) && !empty($field['meta_key'])) ? $field['meta_key'] : str_replace(' ', '_', $field_id);
            $field['label'] = (isset($field['label']) && !empty($field['label'])) ? $field['label'] : $field_id;
            $field['type'] = (isset($field['type']) && !empty($field['type'])) ? $field['type'] : 'text';
            $this->arm_db_add_form_field($field);
        }

        function arm_db_add_form_field($field = array(), $field_id = 0, $form_id = 0) {
            global $wp, $wpdb, $current_user, $ARMember;
            $defaultPresetFields = $this->arm_default_preset_user_fields();
            $oldFormFields = $this->arm_get_db_form_fields();
            $fieldMetaKey = (isset($field['meta_key']) && !empty($field['meta_key'])) ? $field['meta_key'] : '';
            $fieldType = (isset($field['type']) && !empty($field['type'])) ? $field['type'] : '';
            $fieldMap = (isset($field['mapfield']) && !empty($field['mapfield'])) ? $field['mapfield'] : '';
            if (!empty($fieldMetaKey) && !in_array($fieldMetaKey, array_keys($defaultPresetFields))) {
                if (!isset($oldFormFields['other'][$fieldMetaKey]) && !in_array($fieldType, array('hidden', 'html', 'section', 'info', 'rememberme', 'repeat_pass', 'repeat_email', 'social_fields'))) {
                    $core_options = array(
                        'db_field_id' => $field_id,
                        'db_form_id' => $form_id,
                        'id' => $fieldMetaKey,
                        'label' => '',
                        'placeholder' => '',
                        'type' => $fieldType,
                        'sub_type' => '',
                        'value' => '',
                        'options' => array(),
                        'allow_ext' => '',
                        'file_size_limit' => 2,
                        'meta_key' => $fieldMetaKey,
                        'blank_message' => __('This field can not be left blank.', 'ARMember'),
                        'invalid_username' => __('TThis username is invalid. Please enter a valid username.', 'ARMember'),
                        'invalid_firstname' => __('This first name is invalid. Please enter a valid first name.', 'ARMember'),
                        'invalid_lastname' => __('This last name is invalid. Please enter a valid last name.', 'ARMember'),
                        'invalid_message' => __('Please enter valid detail.', 'ARMember'),
                        'prefix' => '',
                        'suffix' => '',
                        'default_val' => array(),
                        'mapfield' => $fieldMap
                    );
                    $field_options = shortcode_atts($core_options, $field);
                    $field_options['default_field'] = $field_options['_builtin'] = $field_options['required'] = 0;
                    $oldFormFields['other'][$fieldMetaKey] = $field_options;
                    update_option('arm_preset_form_fields', $oldFormFields);
                }/* End `(!isset($oldFormFields['other'][$fieldMetaKey]))` */
            }/* End `(!empty($fieldMetaKey) && !in_array($fieldMetaKey, array_keys($defaultPresetFields)))` */
            return;
        }

        function arm_create_add_new_field($form_id, $field_options) {
            global $wp, $wpdb, $current_user, $ARMember;
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_slug' => $field_options['meta_key'],
                'arm_form_field_created_date' => date('Y-m-d H:i:s'),
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '2',
            );
            /* Insert Form Fields. */
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
            $form_field_id = $wpdb->insert_id;
            return $form_field_id;
        }

        function arm_get_updated_field_html() {
            global $wp, $wpdb, $current_user, $ARMember,$arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $form_id = intval($_POST['form_id']);
            $form = new ARM_Form('id', $form_id);
            $isEditProfile = ( $form->type == 'edit_profile' ) ? true : false;
            $form_field_id = intval($_POST['field_id']);
            $field_options = $_POST['arm_forms'][$form_id][$form_field_id];
            $options = array_map('trim', explode("\n", $field_options['options']));
            $new_options = $options;
            if (is_array($options)) {
                $new_options = array();
                foreach ($options as $data) {
                    if ($data != '') {
                        $new_options[] = stripslashes($data);
                    }
                }
            }
            $field_options['options'] = $new_options;
            /* Filter Form Field Options. */
            $field_options = apply_filters('arm_change_field_options', $field_options);
            $liStyle = $sortable_class = '';
            $ref_field_id = (isset($field_options['ref_field_id']) && $field_options['ref_field_id'] != 0) ? $field_options['ref_field_id'] : 0;
            if ($field_options['type'] == 'section') {
                $sortable_class .= ' arm_section_fields_wrapper';
                $margin = isset($field_options['margin']) ? $field_options['margin'] : array();
                $margin['top'] = (isset($margin['top']) && is_numeric($margin['top'])) ? $margin['top'] : 20;
                $margin['bottom'] = (isset($margin['bottom']) && is_numeric($margin['bottom'])) ? $margin['bottom'] : 20;
                $liStyle .= 'margin-top:' . $margin['top'] . 'px !important;';
                $liStyle .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
            }
            /* Generate Field HTML */
            ?>
<li class="arm_form_field_container arm_form_field_sortable arm_form_field_container_<?php echo $field_options['type']; ?> <?php echo $sortable_class; ?>" id="arm_form_field_container_<?php echo $form_field_id; ?>" data-field_id="<?php echo $form_field_id; ?>" data-type="<?php echo $field_options['type']; ?>" data-meta_key="<?php echo strtolower($field_options['meta_key']); ?>" data-ref_field="<?php echo $ref_field_id; ?>" style="<?php echo $liStyle; ?>">
<?php
$this->arm_member_form_get_field_html($form_id, $form_field_id, $field_options, 'inactive', $form,$isEditProfile);
?>
</li>
<?php
exit;
}

        function arm_create_new_field($form_id = 0, $type = '', $refFieldID = 0) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $field_type_options = $this->arm_default_field_options();
            $field_type_options = maybe_unserialize($field_type_options);
            $form_id = (!empty($form_id) && $form_id != 0) ? $form_id : intval($_POST['form_id']);
            $type = (!empty($type)) ? $type : sanitize_text_field($_POST['type']);
            $refFieldID = (!empty($refFieldID)) ? $refFieldID : (isset($_POST['ref_field_id']) ? intval($_POST['ref_field_id']) : 0);
            $form = new ARM_Form('id', $form_id);
            $field_options = $field_type_options[$type];
            $ref_field_id = (!empty($refFieldID) && $refFieldID != 0) ? $refFieldID : 0;
            $field_options['ref_field_id'] = $ref_field_id;
            $total_fields = isset($_POST['current_total_fields']) ? intval($_POST['current_total_fields']) : rand(99, 999);
            /* Filter Form Field Options. */
            $field_options = apply_filters('arm_change_field_options', $field_options);
            $temp_form_id = ($form_id * 100);
            $arm_form_type = $wpdb->get_row( $wpdb->prepare("SELECT arm_form_type FROM `". $ARMember->tbl_arm_forms ."` WHERE arm_form_id = %d", $form_id) );
            $isEditProfile = ( $arm_form_type->arm_form_type == 'edit_profile' ) ? true : false;
            $form_field_id = ((int) $temp_form_id + (int) $total_fields);
            /* Generate Field HTML */
            $liStyle = $sortable_class = '';
            if ($field_options['type'] == 'section') {
                $sortable_class .= ' arm_section_fields_wrapper';
                $margin = isset($field_options['margin']) ? $field_options['margin'] : array();
                $margin['top'] = (isset($margin['top']) && is_numeric($margin['top'])) ? $margin['top'] : 20;
                $margin['bottom'] = (isset($margin['bottom']) && is_numeric($margin['bottom'])) ? $margin['bottom'] : 20;
                $liStyle .= 'margin-top:' . $margin['top'] . 'px !important;';
                $liStyle .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
            }
            ?>
            <li class="arm_form_field_container arm_form_field_sortable arm_form_field_container_<?php echo $field_options['type']; ?> <?php echo $sortable_class; ?>" id="arm_form_field_container_<?php echo $form_field_id; ?>" data-field_id="<?php echo $form_field_id; ?>" data-type="<?php echo $field_options['type']; ?>" data-meta_key="<?php echo strtolower($field_options['meta_key']); ?>" data-ref_field="<?php echo $ref_field_id; ?>" style="<?php echo $liStyle; ?>">
            <?php
            $this->arm_member_form_get_field_html($form_id, $form_field_id, $field_options, 'inactive', $form, $isEditProfile);
            ?>
            </li>
            <?php
            exit;
        }

        function arm_get_updated_social_profile_fields_html() {
            global $wp, $wpdb, $current_user, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $field_type_options = $this->arm_default_preset_user_fields();
            $field_type_options = maybe_unserialize($field_type_options);
            $field_options = $field_type_options['social_fields'];
            $form_id = intval($_POST['form_id']);
            $form = new ARM_Form('id', $form_id);
            if (isset($_POST['field_id']) && $_POST['field_id'] != 0) {
                $form_field_id = intval($_POST['field_id']);
            } else {
                $total_fields = isset($_POST['current_total_fields']) ? intval($_POST['current_total_fields']) : rand(99, 999);
                $temp_form_id = ($form_id * 100);
                $form_field_id = ((int) $temp_form_id + (int) $total_fields);
            }
            $arm_form_type = $wpdb->get_row( $wpdb->prepare("SELECT arm_form_type FROM `". $ARMember->tbl_arm_forms ."` WHERE arm_form_id = %d", $form_id) );
            $isEditProfile = ( $arm_form_type->arm_form_type == 'edit_profile' ) ? true : false;
            $field_options['options'] = $_POST['arm_social_fields'];
            /* Filter Form Field Options. */
            $field_options = apply_filters('arm_change_field_options', $field_options);
            ?>
            <li class="arm_form_field_container arm_form_field_container_social_fields" id="arm_form_field_container_<?php echo $form_field_id; ?>" data-type="social_fields" data-field_id="<?php echo $form_field_id; ?>">
            <?php
            $this->arm_member_form_get_field_html($form_id, $form_field_id, $field_options, 'inactive', $form,$isEditProfile);
            ?>
            </li>
            <?php
            exit;
        }

        function arm_prefix_suffix_field_html() {
            global $wp, $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $icon = $_POST['icon'];
            $iconColor = isset($_POST['color']) ? $_POST['color'] : '';
            if (!empty($icon)) {
                echo $this->arm_generate_field_fa_icon($_POST['field_id'], $icon, $_POST['type'], $iconColor);
            } else {
                echo "";
            }
            exit;
        }

        function arm_roles_field_options() {
            global $wp, $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $field_type_options = $this->arm_default_field_options();
            $field_type_options = maybe_unserialize($field_type_options);
            $form_id = intval($_POST['form_id']);
            $field_id = intval($_POST['field_id']);
            $field_options = $_POST['arm_forms'][$form_id][$field_id];
            $roles_field = $field_type_options['roles'];
            $roles_field['sub_type'] = isset($field_options['sub_type']) ? $field_options['sub_type'] : 'select';
            $roles_field['options'] = isset($field_options['options']) ? $field_options['options'] : array();
            /* Filter Form Field Options. */
            $roles_field = apply_filters('arm_change_field_options', $roles_field);
            echo $this->arm_member_form_get_fields_by_type($roles_field, $field_id, $form_id);
            exit;
        }

        function armGetFormFieldKeysForDelete($form_id = 0) {
            global $wp, $wpdb, $ARMember;
            $otherFormFieldKeys = array();
            $field_result = $wpdb->get_results("SELECT `arm_form_field_slug` FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`!='" . $form_id . "' AND `arm_form_field_status` != '2' ORDER BY `arm_form_field_order` ASC", ARRAY_A);
            if (!empty($field_result)) {
                foreach ($field_result as $val) {
                    if (!empty($val['arm_form_field_slug'])) {
                        $otherFormFieldKeys[$val['arm_form_field_slug']] = $val['arm_form_field_slug'];
                    }
                }
            }
            return $otherFormFieldKeys;
        }

        function arm_delete_form() {
            global $wp, $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $form_id = isset($_POST['form_id']) ? $_POST['form_id'] : 0;
            $set_id = isset($_POST['set_id']) ? $_POST['set_id'] : 0;
            $response = array('type' => 'error', 'msg' => __('There is a error while deleting form, please try again.', 'ARMember'));
            $deletedFormFields = array();
            if (!empty($form_id) && $form_id != 0) {
                $form_delete = $wpdb->delete($ARMember->tbl_arm_forms, array('arm_form_id' => $form_id));
                if ($form_delete) {
                    $isFieldDelete = isset($_POST['field_delete']) ? $_POST['field_delete'] : 0;
                    if ($isFieldDelete == '1') {
                        $presetFields = $this->arm_get_db_form_fields();
                        $_fields = $this->arm_get_member_forms_fields($form_id, 'arm_form_field_slug');
                        if (!empty($_fields)) {
                            $otherFormFields = $this->armGetFormFieldKeysForDelete($form_id);
                            foreach ($_fields as $ff) {
                                $fieldMetaKey = $ff['arm_form_field_slug'];
                                if (!empty($fieldMetaKey) && !in_array($fieldMetaKey, array_values($otherFormFields))) {
                                    $deletedFormFields[$fieldMetaKey] = $fieldMetaKey;
                                    unset($presetFields['other'][$fieldMetaKey]);
                                }
                            }
                            update_option('arm_preset_form_fields', $presetFields);
                        }
                    }
                    $fields_delete = $wpdb->delete($ARMember->tbl_arm_form_field, array('arm_form_field_form_id' => $form_id));
                    $response = array('type' => 'success', 'msg' => __('Form deleted Successfully.', 'ARMember'));
                }
            }
            if (!empty($set_id) && $set_id != 0) {
                $setForms = $wpdb->get_results("SELECT `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_set_id`='" . $set_id . "'", ARRAY_A);
                if (!empty($setForms)) {
                    foreach ($setForms as $_form) {
                        $form_delete = $wpdb->delete($ARMember->tbl_arm_forms, array('arm_form_id' => $_form['arm_form_id']));
                        if ($form_delete) {
                            $fields_delete = $wpdb->delete($ARMember->tbl_arm_form_field, array('arm_form_field_form_id' => $_form['arm_form_id']));
                        }
                    }
                    $response = array('type' => 'success', 'msg' => __('Form Set Deleted Successfully.', 'ARMember'));
                }
            }
            $response['deleted_fields'] = $deletedFormFields;
            echo json_encode($response);
            die();
        }

        function arm_delete_form_field() {
            global $wp, $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            $field_id = intval($_POST['field_id']);
            $field_type = sanitize_text_field($_POST['field_type']);
            $response = array('type' => 'error', 'msg' => 'There is a error while deleting field, please try again.');
            if (!empty($field_id)) {
                $old_field = $wpdb->get_row("SELECT `arm_form_field_slug`, `arm_form_field_status`, `arm_form_field_option` FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_id`='{$field_id}' LIMIT 1", ARRAY_A);
                $old_field_status = $old_field['arm_form_field_status'];
                $field_options = maybe_unserialize($old_field['arm_form_field_option']);
                if ($old_field_status == 2) {
                    $field_status_update = $wpdb->delete($ARMember->tbl_arm_form_field, array('arm_form_field_id' => $field_id));
                } else {
                    $field_status_update = $wpdb->update($ARMember->tbl_arm_form_field, array('arm_form_field_status' => 0), array('arm_form_field_id' => $field_id));
                }
                $response = array('type' => 'success', 'msg' => 'Field deleted Successfully.');
            }
            echo json_encode($response);
            die();
        }

        /*
         * Default forms & their fields.
         */

        function arm_default_member_forms_data() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            $first_name = array(
                'id' => 'first_name',
                'label' => __('First Name', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'first_name',
                'required' => 1,
                'hide_firstname' => 0,
                'blank_message' => __('First Name can not be left blank.', 'ARMember'),
                'invalid_firstname' => __('This first name is invalid. Please enter a valid first name.', 'ARMember'),
                'default_field' => 1,
            );
            $last_name = array(
                'id' => 'last_name',
                'label' => __('Last Name', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'last_name',
                'required' => 1,
                'hide_lastname' => 0,
                'blank_message' => __('Last Name can not be left blank.', 'ARMember'),
                'invalid_Lastname' => __('This last name is invalid. Please enter a valid last name.', 'ARMember'),
                'default_field' => 1,
            );
            $user_login = array(
                'id' => 'user_login',
                'label' => __('Username', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'user_login',
                'required' => 1,
                'hide_username' => 0,
                'blank_message' => __('Username can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid username.', 'ARMember'),
                'invalid_username' => __('This username is invalid. Please enter a valid username.', 'ARMember'),
                'default_field' => 1
            );
            $user_login_forgot_password = array(
                'id' => 'user_login',
                'label' => __('Username OR Email Address', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'user_login',
                'required' => 1,
                'blank_message' => __('Username can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid username.', 'ARMember'),
                'default_field' => 1
            );
            $user_email = array(
                'id' => 'user_email',
                'label' => __('Email Address', 'ARMember'),
                'placeholder' => '',
                'type' => 'email',
                'meta_key' => 'user_email',
                'required' => 1,
                'blank_message' => __('Email Address can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid email address.', 'ARMember'),
                'default_field' => 1,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $user_pass_reg = array(
                'id' => 'user_pass',
                'label' => __('Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                'meta_key' => 'user_pass',
                'required' => 1,
                'blank_message' => __('Password can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid password.', 'ARMember'),
            );
            $user_pass_login = array(
                'id' => 'user_pass',
                'label' => __('Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array('strength_meter' => 0, 'strong_password' => 0, 'minlength' => 1, 'maxlength' => '', 'special' => 0, 'numeric' => 0, 'uppercase' => 0, 'lowercase' => 0),
                'meta_key' => 'user_pass',
                'required' => 1,
                'blank_message' => __('Password can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid password.', 'ARMember'),
                'default_field' => 1
            );
            $new_user_pass = array(
                'id' => 'user_pass',
                'label' => __('New Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                'meta_key' => 'user_pass',
                'required' => 1,
                'blank_message' => __('Password can not be left blank.', 'ARMember'),
                'invalid_message' => __('Please enter valid password.', 'ARMember'),
                'default_field' => 1,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $repeat_pass = array(
                'id' => 'repeat_pass',
                'label' => __('Confirm Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'repeat_pass',
                'options' => array('strength_meter' => 0, 'strong_password' => 0, 'minlength' => 0, 'maxlength' => '', 'special' => 0, 'numeric' => 0, 'uppercase' => 0, 'lowercase' => 0),
                'meta_key' => 'repeat_pass',
                'required' => 1,
                'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
                'invalid_message' => __('Passwords don\'t match.', 'ARMember'),
                'default_field' => 1,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $remember_me = array(
                'id' => 'rememberme',
                'type' => 'rememberme',
                'label' => __('Remember me', 'ARMember'),
                'meta_key' => 'rememberme',
                'required' => 0,
                'default_field' => 1,
            );
            $submit = array(
                'id' => 'submit',
                'label' => __('Submit', 'ARMember'),
                'type' => 'submit',
                'default_field' => 1
            );
            $loginSubmit = array(
                'id' => 'submit',
                'label' => __('LOGIN', 'ARMember'),
                'type' => 'submit',
                'default_field' => 1
            );
            $default_form_style = $this->arm_default_form_style();
            /* Set Form Details. */
            $globalSettings = $arm_global_settings->global_settings;
            $register_page_id = isset($globalSettings['register_page_id']) ? $globalSettings['register_page_id'] : 0;
            $forgot_password_page_id = isset($globalSettings['forgot_password_page_id']) ? $globalSettings['forgot_password_page_id'] : 0;
            $reg_redirect_id = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
            $login_redirect_id = isset($globalSettings['edit_profile_page_id']) ? $globalSettings['edit_profile_page_id'] : 0;
            $forms['registration'] = array(
                'name' => __('Please Signup', 'ARMember'),
                'settings' => array('style' => $default_form_style, 'redirect_type' => 'page', 'redirect_page' => $reg_redirect_id, 'auto_login' => '1'),
                'fields' => array($user_login, $first_name, $last_name, $user_email, $user_pass_reg, $submit)
            );
            
            $default_login_form_style = $this->arm_default_form_style_login();
            $loginSettings = array(
                'registration_link_type' => 'page',
                'registration_link_type_page' => $register_page_id,
                'forgot_password_link_type' => 'modal',
                'forgot_password_link_type_page' => $forgot_password_page_id,
                'redirect_type' => 'page',
                'redirect_page' => $login_redirect_id,
                'style' => $default_login_form_style,
                'show_rememberme' => '1',
                'show_registration_link' => '1',
                'registration_link_label' => '<center>Dont have account? [ARMLINK]SIGNUP[/ARMLINK]</center>',
                'show_forgot_password_link' => '1',
                'forgot_password_link_label' => __('Lost Your Password', 'ARMember'),
                'forgot_password_link_margin' => array(
                    'bottom' => '0',
                    'top' => '-132',
                    'left' => '315',
                    'right' => '0'
                ),
                'registration_link_margin' => array(
                    'top' => '0',
                    'left' => '0',
                    'right' => '0',
                    'bottom' => '0'
                ),
                'custom_css' => ''
            );
            $forms['login'] = array(
                'name' => __('Please Login', 'ARMember'),
                'settings' => $loginSettings,
                'fields' => array($user_login, $user_pass_login, $remember_me, $loginSubmit)
            );
            $forms['forgot_password'] = array(
                'name' => __('Forgot Password', 'ARMember'),
                'settings' => array('style' => $default_login_form_style, 'redirect_type' => 'message', 'message' => __('We have sent you a password reset link, Please check your mail.', 'ARMember'), 'description' => __('Please enter your email address or username below.', 'ARMember')),
                'fields' => array($user_login_forgot_password, $submit)
            );
            $forms['change_password'] = array(
                'name' => __('Change Password', 'ARMember'),
                'settings' => array('style' => $default_login_form_style, 'redirect_type' => 'message', 'message' => __('Your password changed successfully.', 'ARMember')),
                'fields' => array($new_user_pass, $repeat_pass, $submit)
            );
            
            $profile_first_name = array(
                'id' => 'first_name',
                'label' => esc_html__('First Name', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'first_name',
                'required' => 1,
                'hide_firstname' => 0,
                'blank_message' => esc_html__('First Name can not be left blank.', 'ARMember'),
                'invalid_firstname' => esc_html__('This first name is invalid. Please enter a valid first name.', 'ARMember'),
                'default_field' => 0,
            );
            $profile_last_name = array(
                'id' => 'last_name',
                'label' => esc_html__('Last Name', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'last_name',
                'required' => 1,
                'hide_lastname' => 0,
                'blank_message' => esc_html__('Last Name can not be left blank.', 'ARMember'),
                'invalid_Lastname' => esc_html__('This last name is invalid. Please enter a valid last name.', 'ARMember'),
                'default_field' => 0,
            );
            $profile_user_login = array(
                'id' => 'user_login',
                'label' => esc_html__('Username', 'ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'user_login',
                'required' => 1,
                'hide_username' => 0,
                'blank_message' => esc_html__('Username can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid username.', 'ARMember'),
                'invalid_username' => esc_html__('This username is invalid. Please enter a valid username.', 'ARMember'),
                'default_field' => 0
            );

            $profile_user_email = array(
                'id' => 'user_email',
                'label' => esc_html__('Email Address', 'ARMember'),
                'placeholder' => '',
                'type' => 'email',
                'meta_key' => 'user_email',
                'required' => 1,
                'blank_message' => esc_html__('Email Address can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid email address.', 'ARMember'),
                'default_field' => 0,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $profile_user_pass = array(
                'id' => 'user_pass',
                'label' => esc_html__('Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                'meta_key' => 'user_pass',
                'required' => 1,
                'blank_message' => esc_html__('Password can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid password.', 'ARMember'),
            );
            $profile_submit = array(
                'id' => 'submit',
                'label' => esc_html__('Update Profile', 'ARMember'),
                'type' => 'submit',
                'default_field' => 1
            );
            $forms['edit_profile'] = array(
                'name' => esc_html__('Edit Profile', 'ARMember'),
                'settings' => array(
                    'style' => $default_form_style,
                ),
                'fields' => array( $profile_user_login, $profile_first_name, $profile_last_name, $profile_user_email, $profile_user_pass, $profile_submit )
            );
            return $forms;
        }

        function arm_check_unique_set_name() {
            global $wp, $wpdb, $ARMember, $arm_slugs,$arm_capabilities_global;
            $posted_data = $_POST;
            /* Check For unique set name starts */
            if (isset($_POST['arm_set_name'])) {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
                $setform_name = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_set_name` LIKE '" . $_POST['arm_set_name'] . "' GROUP BY arm_set_id ORDER BY arm_form_id DESC Limit 0,1");
                if (!empty($setform_name) && count($setform_name) > 0) {
                    echo "false";
                } else {
                    echo "true";
                }
            }
            /* Check For unique set name ends */
            /* Check For unique Signup form name starts */
            if (isset($_POST['arm_form_name'])) {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
                $setform_name = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_label` LIKE '" . $_POST['arm_form_name'] . "' and `arm_form_type` = 'registration' GROUP BY arm_form_id ORDER BY arm_form_id DESC Limit 0,1");
                if (!empty($setform_name) && count($setform_name) > 0) {
                    echo "false";
                } else {
                    echo "true";
                }
            }
            /* Check For unique Signup form name ends */
            die();
        }

        function arm_add_new_member_form() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_capabilities_global;
            $default_member_forms_data = $this->arm_default_member_forms_data();
            $posted_data = $_POST;
            $form_data = $posted_data['arm_new_form'];

            $response = array('type' => 'error');
            $arm_set_name = isset($form_data['arm_set_name']) ? $form_data['arm_set_name'] : '';
            if (isset($posted_data['arm_form_template']) && $posted_data['arm_form_template'] < 0) {
                $form_data['arm_form_type'] = 'template-login';
            }

            if (isset($posted_data['existing_type']) && $posted_data['existing_type'] == 'template' && $form_data['arm_form_type'] == 'registration') {
                $posted_data['arm_form_template'] = 'default_template';
                $form_data['arm_form_type'] = 'registration';
                $temp_registration = 'template-registration';
            }

            $arm_form_type = isset($form_data['arm_form_type']) ? $form_data['arm_form_type'] : '';
            if (isset($posted_data['arm_form_template']) && $posted_data['arm_form_template'] == 'default_template') {
                if (!empty($posted_data['action']) && $posted_data['action'] == 'add_new_member_form' && !empty($form_data['arm_form_type'])) {
                    $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
                    if ($arm_form_type == 'login') {
                        $get_row = $wpdb->get_row("SELECT arm_form_id FROM " . $ARMember->tbl_arm_forms . " WHERE arm_form_type='" . $arm_form_type . "' AND arm_set_id='1'");
                        $form_id = $get_row->arm_form_id;
                        $edit_form_link = admin_url('admin.php?page=' . $arm_slugs->manage_forms . '&action=new_form&form_id=' . $form_id . '&arm_set_name=' . $arm_set_name);
                        $response = array('type' => 'success', 'url' => $edit_form_link);
                    } else if ($arm_form_type == 'registration') {
                        if (isset($posted_data['template_form_registration']) && $posted_data['template_form_registration'] !== '') {
                            $form_id = $posted_data['template_form_registration'];
                            $form_fields = $wpdb->get_results("SELECT * FROM " . $ARMember->tbl_arm_form_field . " WHERE arm_form_field_form_id = " . $form_id);
                            $installed_fields = array();
                            if (count($form_fields) > 0) {
                                foreach ($form_fields as $key => $value) {
                                    $field_options = maybe_unserialize($value->arm_form_field_option);
                                    if ($field_options['id'] !== 'submit') {
                                        array_push($installed_fields, $value->arm_form_field_slug);
                                    }
                                }
                            }

                            $new_meta_fields = array();
                            if (isset($posted_data['arm_meta_fields_for_template']) && $posted_data['arm_meta_fields_for_template'] !== '') {
                                $meta_fields = $posted_data['specific_fields'];
                                foreach ($meta_fields as $key => $fields) {
                                    if (!in_array($fields, $installed_fields) && $fields != 'submit') {
                                        array_push($new_meta_fields, $fields);
                                    }
                                }
                            }

                            $metaFields = $this->arm_get_db_form_fields(true);

                            $field_array = array();
                            foreach ($metaFields as $field => $array) {
                                if (in_array($field, $new_meta_fields)) {
                                    array_push($field_array, $field);
                                }
                            }
                            $edit_form_link = admin_url('admin.php?page=' . $arm_slugs->manage_forms . '&action=new_form&form_id=' . $form_id . '&arm_set_name=' . $form_data['arm_form_label'] . '&form_meta_fields=' . implode(',', $field_array));
                            $response = array('type' => 'success', 'url' => $edit_form_link);
                            if (!empty($field_array)) {
                                $response['meta_fields'] = implode(',', $field_array);
                            }
                        } else {
                            $response = array('type' => 'error');
                        }
                    }
                }
            } else {
                if (isset($posted_data['existing_type']) && $posted_data['existing_type'] == 'form') {
                    $form_id = $posted_data['existing_form_registration'];
                    $form_row = $wpdb->get_row("SELECT arm_form_label FROM " . $ARMember->tbl_arm_forms . " WHERE arm_form_id = " . $form_id);
                    $arm_set_name = $form_data['arm_form_label'];
                } else {
                    $get_row = $wpdb->get_row("SELECT arm_form_id FROM " . $ARMember->tbl_arm_forms . " WHERE arm_form_slug LIKE '" . $form_data['arm_form_type'] . "%' AND arm_set_id='" . $posted_data['arm_form_template'] . "'");
                    
                    $form_id = $get_row->arm_form_id;
                }
                $edit_form_link = admin_url('admin.php?page=' . $arm_slugs->manage_forms . '&action=new_form&form_id=' . $form_id . '&arm_set_name=' . $arm_set_name . '&is_clone=1');
                $response = array('type' => 'success', 'url' => $edit_form_link);
            }
            $response['form_type'] = $arm_form_type;

            echo json_encode($response);
            die();
        }

        function arm_add_new_profile_form(){
             global $wp, $wpdb, $ARMember, $arm_slugs, $arm_capabilities_global;
            $default_member_forms_data = $this->arm_default_member_forms_data();
            $posted_data = $_POST;
            $form_data = $posted_data['arm_new_profile_form'];

            $response = array('type' => 'error');
            $arm_set_name = isset($form_data['arm_set_name']) ? $form_data['arm_set_name'] : '';

            if (isset($posted_data['arm_form_template']) && $posted_data['arm_form_template'] < 0) {
                $form_data['arm_form_type'] = 'profile';
            }

            if (isset($form_data['existing_type']) && $form_data['existing_type'] == 'template' && $form_data['arm_form_type'] == 'profile') {
                $posted_data['arm_form_template'] = 'default_template';
                $form_data['arm_form_type'] = 'profile';
                $temp_registration = 'template-profile';
            }

            $arm_form_type = isset($form_data['arm_form_type']) ? $form_data['arm_form_type'] : '';
            
            if (isset($posted_data['arm_form_template']) && $posted_data['arm_form_template'] == 'default_template') {
                if (!empty($posted_data['action']) && $posted_data['action'] == 'add_new_profile_form' && !empty($form_data['arm_form_type'])) {
                    $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');

                    if( 'profile' == $arm_form_type ){
                        if (isset($posted_data['template_form_edit_profile']) && $posted_data['template_form_edit_profile'] !== '') {
                            $form_id = $posted_data['template_form_edit_profile'];
                            $form_fields = $wpdb->get_results("SELECT * FROM " . $ARMember->tbl_arm_form_field . " WHERE arm_form_field_form_id = " . $form_id);
                            $installed_fields = array();
                            if (count($form_fields) > 0) {
                                foreach ($form_fields as $key => $value) {
                                    $field_options = maybe_unserialize($value->arm_form_field_option);
                                    if ($field_options['id'] !== 'submit') {
                                        array_push($installed_fields, $value->arm_form_field_slug);
                                    }
                                }
                            }

                            $new_meta_fields = array();
                            if (isset($posted_data['arm_meta_profile_fields_for_template']) && $posted_data['arm_meta_profile_fields_for_template'] !== '') {
                                $meta_fields = $posted_data['specific_fields'];
                                foreach ($meta_fields as $key => $fields) {
                                    if ( $fields != 'submit') {
                                        array_push($new_meta_fields, $fields);
                                    }
                                }
                            }

                           /* $metaFields = $this->arm_get_db_form_fields(false);

                            $field_array = array();
                            foreach ($metaFields as $field => $array) {
                                if (in_array($field, $new_meta_fields)) {
                                    array_push($field_array, $field);
                                }
                            }*/

                            $edit_form_link = admin_url('admin.php?page=' . $arm_slugs->manage_forms . '&action=new_form&form_id=' . $form_id . '&arm_form_type=edit_profile&arm_set_name=' . $form_data['arm_form_label'] . '&form_meta_fields=' . implode(',', $new_meta_fields));
                            $response = array('type' => 'success', 'url' => $edit_form_link);
                            if (!empty($new_meta_fields)) {
                                $response['meta_fields'] = implode(',', $new_meta_fields);
                            }
                        } else {
                            $response = array('type' => 'error');
                        }
                    }
                }
            } else {
                if (isset($form_data['existing_type']) && $form_data['existing_type'] == 'form') {
                    $form_id = $posted_data['existing_form_profile_form'];
                    $form_row = $wpdb->get_row("SELECT arm_form_label FROM " . $ARMember->tbl_arm_forms . " WHERE arm_form_id = " . $form_id);
                    $arm_set_name = $form_data['arm_form_label'];
                } else {
                    $get_row = $wpdb->get_row("SELECT arm_form_id FROM " . $ARMember->tbl_arm_forms . " WHERE arm_form_slug LIKE '" . $form_data['arm_form_type'] . "%' AND arm_set_id='" . $posted_data['existing_form_profile_form'] . "'");
            
                    $form_id = $get_row->arm_form_id;
                }
                $form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT arm_form_field_option FROM `" . $ARMember->tbl_arm_form_field . "` WHERE arm_form_field_form_id = %d", $form_id ) );
                $meta_fields = array();
                foreach( $form_fields as $fkey => $fvalue ){
                    $foptions = maybe_unserialize( $fvalue->arm_form_field_option );
                    $meta_fields[] = $foptions['meta_key'];
                }
                $edit_form_link = admin_url('admin.php?page=' . $arm_slugs->manage_forms . '&action=new_form&form_id=' . $form_id . '&arm_set_name=' . $arm_set_name . '&is_clone=1&arm_form_type=edit_profile&form_meta_fields=' . implode(',', $meta_fields));
                $response = array('type' => 'success', 'url' => $edit_form_link);
            }
            $response['form_type'] = $arm_form_type;
            echo json_encode($response);
            die;
        }

        /*
         * Get all form data with form fields.
         */

        function arm_get_default_form_id($type = '') {
            global $wp, $wpdb, $ARMember;
            $default_form_id = 0;
            if (!empty($type)) {
                /* Query Monitor Change */
                if( isset($GLOBALS['arm_form_default_id']) && isset($GLOBALS['arm_form_default_id'][$type]) ){
                    $arm_form_id = $GLOBALS['arm_form_default_id'][$type];
                } else {
                    $arm_form_id = $wpdb->get_var("SELECT `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='$type' AND `arm_is_default`='1'");
                    if( !isset($GLOBALS['arm_form_default_id']) ){
                        $GLOBALS['arm_form_default_id'] = array();
                    }
                    $GLOBALS['arm_form_default_id'][$type] = $arm_form_id;
                }
                /* Query Monitor Change */
                $default_form_id = (!empty($arm_form_id) && $arm_form_id != 0) ? $arm_form_id : 0;
            }
            return $default_form_id;
        }

        function arm_get_default_form_id_by_label($type = '', $label = '') {
            global $wp, $wpdb, $ARMember;
            $default_form_id = 0;

            if (!empty($type) && !empty($label)) {
                $arm_form_id = $wpdb->get_var("SELECT `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='$type' AND BINARY `arm_form_label`='$label'");
                $default_form_id = (!empty($arm_form_id) && $arm_form_id != 0) ? $arm_form_id : 0;
            }
            return $default_form_id;
        }

        function arm_get_default_form_label($type = '') {
            global $wp, $wpdb, $ARMember;
            $default_form_label = '';
            if (!empty($type)) {
                $arm_form_label = $wpdb->get_var("SELECT `arm_form_label` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='$type' AND `arm_is_default`='1'");
                $default_form_label = (!empty($arm_form_label) && $arm_form_label != '') ? $arm_form_label : '';
            }
            return $default_form_label;
        }

        function arm_get_single_member_forms($form_id = 0, $fields = 'all', $isFormFields = true) {
            global $wp, $wpdb, $current_user, $ARMember;
            $forms_data = array();
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            if (!empty($form_id) && $form_id != 0) {
                $forms_data = $wpdb->get_row("SELECT {$selectFields}, `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "' ORDER BY `arm_form_id` ASC LIMIT 1", ARRAY_A);
                if (!empty($forms_data)) {
                    $forms_data['arm_form_label'] = (!empty($forms_data['arm_form_label'])) ? stripslashes($forms_data['arm_form_label']) : '';
                    $forms_data['arm_form_settings'] = (!empty($forms_data['arm_form_settings'])) ? maybe_unserialize($forms_data['arm_form_settings']) : array();
                    if ($isFormFields) {
                        /* Get Form Fields */
                        $forms_data['fields'] = $this->arm_get_member_forms_fields($forms_data['arm_form_id']);
                    }
                }
            }
            return $forms_data;
        }

        function arm_get_other_member_forms($set_id = 0) {
            global $wp, $wpdb, $current_user, $ARMember;
            $forms_data = array();
            if (!empty($set_id) && $set_id != 0) {
                $form_result = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_set_id`='" . $set_id . "' ORDER BY `arm_form_id` ASC", ARRAY_A);
                if (!empty($form_result)) {
                    foreach ($form_result as $form) {
                        $id = $form['arm_form_id'];
                        /* Get Form Fields */
                        $form['arm_form_label'] = (!empty($form['arm_form_label'])) ? stripslashes($form['arm_form_label']) : '';
                        $form['arm_form_settings'] = (!empty($form['arm_form_settings'])) ? maybe_unserialize($form['arm_form_settings']) : array();
                        $login_regex = "/template-login(.*?)/";
                        $register_regex = "/template-registration(.*?)/";
                        $forgot_regex = "/template-forgot-password(.*?)/";
                        $changepass_regex = "/template-change-password(.*?)/";
                        
                        
                        preg_match($login_regex, $form['arm_form_slug'], $match_login);
                        preg_match($register_regex, $form['arm_form_slug'], $match_register);
                        preg_match($forgot_regex, $form['arm_form_slug'], $match_forgot);
                        preg_match($changepass_regex, $form['arm_form_slug'], $match_changepass);
                        

                        if (isset($match_login[0]) && !empty($match_login[0])) {
                            $form['arm_form_type'] = 'login';
                        } else if (isset($match_register[0]) && !empty($match_register[0])) {
                            $form['arm_form_type'] = 'registration';
                        } else if (isset($match_forgot[0]) && !empty($match_forgot[0])) {
                            $form['arm_form_type'] = 'forgot_password';
                        } else if (isset($match_changepass[0]) && !empty($match_changepass[0])) {
                            $form['arm_form_type'] = 'change_password';
                        }

                        $form['fields'] = $this->arm_get_member_forms_fields($id);
                        $forms_data[$id] = $form;
                    }
                }
            }
            return $forms_data;
        }

        function arm_get_member_form_sets() {
            global $wp, $wpdb, $current_user, $ARMember;
            $set_data = array();
            $form_result = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_set_id`!='0' AND `arm_form_type` != 'edit_profile' AND `arm_is_template` = '0' ORDER BY `arm_set_id` DESC", ARRAY_A);            
            if (!empty($form_result)) {
                foreach ($form_result as $form) {
                    $id = $form['arm_form_id'];
                    $set_id = $form['arm_set_id'];
                    /* Get Form Fields */
                    $form['arm_form_label'] = (!empty($form['arm_form_label'])) ? stripslashes($form['arm_form_label']) : '';
                    $form['arm_form_settings'] = (!empty($form['arm_form_settings'])) ? maybe_unserialize($form['arm_form_settings']) : array();
                    $set_data[$set_id][$id] = $form;
                }
            }
            return $set_data;
        }

        function arm_get_member_forms_by_type($type = '', $isFormFields = true) {
            global $wp, $wpdb, $current_user, $ARMember;
            $forms_data = array();
            if (!empty($type) && $type != '') {
                $form_result = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='" . $type . "' ORDER BY `arm_form_id` DESC", ARRAY_A);
                if (!empty($form_result)) {
                    foreach ($form_result as $form) {
                        $id = $form['arm_form_id'];
                        /* Get Form Fields */
                        $form['arm_form_label'] = (!empty($form['arm_form_label'])) ? stripslashes($form['arm_form_label']) : '';
                        $form['arm_form_settings'] = (!empty($form['arm_form_settings'])) ? maybe_unserialize($form['arm_form_settings']) : array();
                        if ($isFormFields) {
                            $form['fields'] = $this->arm_get_member_forms_fields($id);
                        }
                        $forms_data[$id] = $form;
                    }
                }
            }
            return $forms_data;
        }
        
        function arm_get_member_forms_and_fields_by_type($type = '', $fields = 'all', $isFormFields = true) {
            global $wp, $wpdb, $current_user, $ARMember;
            $forms_data = array();
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            if (!empty($type) && $type != '') {
                $form_result = $wpdb->get_results("SELECT {$selectFields} FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='" . $type . "' ORDER BY `arm_form_id` DESC", ARRAY_A);
                if (!empty($form_result)) {
                    foreach ($form_result as $form) {
                        $id = $form['arm_form_id'];
                        /* Get Form Fields */
                        $form['arm_form_label'] = (!empty($form['arm_form_label'])) ? stripslashes($form['arm_form_label']) : '';
                        $form['arm_form_settings'] = (!empty($form['arm_form_settings'])) ? maybe_unserialize($form['arm_form_settings']) : array();
                        if ($isFormFields) {
                            $form['fields'] = $this->arm_get_member_forms_fields($id);
                        }
                        $forms_data[$id] = $form;
                    }
                }
            }
            return $forms_data;
        }

        function arm_get_all_member_forms($fields = 'all', $isFormFields = false) {
            global $wp, $wpdb, $current_user, $ARMember;
            $forms_data = array();
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            $form_result = $wpdb->get_results("SELECT {$selectFields}, `arm_form_id` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` NOT LIKE 'template' ORDER BY `arm_form_id` DESC", ARRAY_A);
            if (!empty($form_result)) {
                foreach ($form_result as $form) {
                    $id = $form['arm_form_id'];
                    $form['arm_form_label'] = (!empty($form['arm_form_label'])) ? stripslashes($form['arm_form_label']) : '';
                    $form['arm_form_settings'] = (!empty($form['arm_form_settings'])) ? maybe_unserialize($form['arm_form_settings']) : array();
                    if ($isFormFields) {
                        /* Get Form Fields */
                        $form['fields'] = $this->arm_get_member_forms_fields($id);
                    }
                    $forms_data[$id] = $form;
                }
            }
            return $forms_data;
        }

        /*
         * Get Form Fields by form id.
         */

        function arm_get_member_forms_fields($form_id = '', $columns = 'all') {
            global $wp, $wpdb, $current_user, $ARMember;
            $fields = array();
            $selectColumns = '*';
            if (!empty($columns)) {
                if ($columns != 'all' && $columns != '*') {
                    $selectColumns = $columns;
                }
            }
            if (!empty($form_id) && $form_id != 0) {
                $field_result = $wpdb->get_results("SELECT {$selectColumns}, `arm_form_field_id`, `arm_form_field_form_id` FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`='" . $form_id . "' AND `arm_form_field_status` != '2' AND arm_form_field_slug !='arm_captcha' ORDER BY `arm_form_field_order` ASC", ARRAY_A);
                foreach ($field_result as $field) {
                    $field['arm_form_field_option'] = (isset($field['arm_form_field_option'])) ? maybe_unserialize($field['arm_form_field_option']) : array();
                    $fields[] = $field;
                }
            }
            return $fields;
        }

        function save_member_forms() {
            global $wp, $wpdb, $current_user, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_forms'], '1');
            unset($_POST['no_field']);
            $posted_data = $_POST;
            $arm_action = sanitize_text_field($posted_data['arm_action']);
            $arm_form_ids = (isset($posted_data['arm_login_form_ids']) && $posted_data['arm_login_form_ids'] !== '' ) ? explode(',', $posted_data['arm_login_form_ids']) : '';
            $arm_ref_form = isset($posted_data['arm_ref_template']) ? intval($posted_data['arm_ref_template']) : 0;
            unset($posted_data['arm_ignore']);
            $i = 0;
            foreach ($posted_data['arm_forms'] as $tmp_form_id => $tmp_form) {
                if ($arm_action == 'edit_form' && ( $tmp_form['arm_form_type'] == 'registration' || $tmp_form['arm_form_type'] == 'edit_profile' ) ) {
                    $new_form_id = $posted_data['arm_form_id'];
                    $wpdb->query("DELETE FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_form_id` = " . $new_form_id);
                }
                if ($arm_action == 'edit_form' && ( $tmp_form['arm_form_type'] != 'registration' && $tmp_form['arm_form_type'] != 'edit_profile') ) {
                    $wpdb->query("DELETE FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_form_id` = " . $arm_form_ids[$i]);
                }
                $i++;
            }
            unset($i);

            /* Save form & field settings option. */
            if (!empty($posted_data['arm_forms'])) {
                $arm_form_settings = array();
                if (!empty($posted_data['arm_form_settings']) && !empty($posted_data['arm_form_settings'])) {
                    $arm_form_settings = $posted_data['arm_form_settings'];
                    unset($arm_form_settings['change_password']);
                    unset($arm_form_settings['forgot_password']);
                }
                if ($arm_action == 'new_form' || $arm_action == 'duplicate_form') {
                    $max_set_id = $wpdb->get_row("SELECT MAX(arm_set_id) as arm_set_id FROM " . $ARMember->tbl_arm_forms);
                    $set_id = ((int) $max_set_id->arm_set_id + 1);
                } else {
                    $set_id = isset($posted_data['form_set_id']) ? intval($posted_data['form_set_id']) : 0;
                }
                $x = 0;
                $login_form_ids = array();

                foreach ($posted_data['arm_forms'] as $form_id => $form_data) {
                    $formType = $form_data['arm_form_type'];
                    if (!in_array($formType, array('registration', 'edit_profile', 'login'))) {
                        if ($formType == 'change_password') {
                            unset($arm_form_settings['forgot_password']);
                            $arm_form_settings['redirect_type'] = 'message';
                            $arm_form_settings['message'] = isset($posted_data['arm_form_settings']['change_password']['message']) ? sanitize_text_field($posted_data['arm_form_settings']['change_password']['message']) : '';
                        }
                        if ($formType == 'forgot_password') {
                            unset($arm_form_settings['change_password']);
                            $arm_form_settings['redirect_type'] = 'message';
                            $arm_form_settings['message'] = isset($posted_data['arm_form_settings']['forgot_password']['message']) ? sanitize_text_field($posted_data['arm_form_settings']['forgot_password']['message']) : '';
                            $arm_form_settings['description'] = isset($posted_data['arm_form_settings']['forgot_password']['description']) ? $posted_data['arm_form_settings']['forgot_password']['description'] : '';
                        }
                    }
                    if (isset($arm_form_settings['hidden_fields']) && !empty($arm_form_settings['hidden_fields'])) {
                        foreach ($arm_form_settings['hidden_fields'] as $hkey => $hiddenField) {
                            $hiddenField['meta_key'] = (isset($hiddenField['meta_key']) && !empty($hiddenField['meta_key'])) ? sanitize_text_field($hiddenField['meta_key']) : sanitize_title('arm_hidden_' . $hiddenField['title']);
                            $arm_form_settings['hidden_fields'][$hkey] = $hiddenField;
                            if (empty($hiddenField['title']) && empty($hiddenField['value'])) {
                                unset($arm_form_settings['hidden_fields'][$hkey]);
                            }
                        }
                    }
                    $update_form_data = array(
                        'arm_form_label' => $form_data['arm_form_label'],
                        'arm_form_title' => $form_data['arm_form_title'],
                        'arm_form_type' => $formType,
                        'arm_ref_template' => $arm_ref_form,
                        'arm_set_id' => $set_id,
                        'arm_form_settings' => maybe_serialize($arm_form_settings),
                        'arm_form_updated_date' => date('Y-m-d H:i:s'),
                    );
                    /* Insert Form Data */
                    if ($arm_action == 'edit_form') {
                        if ($formType == 'registration' || 'edit_profile' == $formType) {
                            $form_update = $wpdb->update($ARMember->tbl_arm_forms, $update_form_data, array('arm_form_id' => $new_form_id));
                        } else {
                            $frm_id = $arm_form_ids[$x];
                            $form_update = $wpdb->update($ARMember->tbl_arm_forms, $update_form_data, array('arm_form_id' => $frm_id));
                            array_push($login_form_ids, $frm_id);
                        }
                    } else {
                        $new_form_slug = sanitize_title($form_data['arm_form_title']);
                        $check_form = new ARM_Form('slug', $new_form_slug);
                        $new_form_slug = $new_form_slug . '-' . arm_generate_random_code(3);
                        $update_form_data['arm_form_slug'] = $new_form_slug;
                        $update_form_data['arm_set_name'] = sanitize_text_field($posted_data['arm_new_set_name']);
                        if ($formType == 'registration' || $formType == 'edit_profile') {
                            $update_form_data['arm_set_id'] = 0;
                            $update_form_data['arm_form_label'] = sanitize_text_field($posted_data['arm_new_set_name']);
                        }
                        $form_update = $wpdb->insert($ARMember->tbl_arm_forms, $update_form_data);
                        $form_id = $wpdb->insert_id;
                        array_push($login_form_ids, $form_id);
                    }

                    /* Unset Form Detail after update. */
                    unset($form_data['arm_form_label']);
                    unset($form_data['arm_form_title']);
                    unset($form_data['arm_form_type']);
                    unset($form_data['arm_form_slug']);
                    unset($form_data['arm_form_settings']);
                    if (false === $form_update) {
                        /* Error in saving details. */
                    } else {
                        $i = 1;
                        /* Delete Fields which is remove from editor */
                        $deleted_fields = $wpdb->delete($ARMember->tbl_arm_form_field, array('arm_form_field_status' => 0));
                        foreach ($form_data as $field_id => $field_data) {
                            if (isset($field_data['type']) && in_array($field_data['type'], array('checkbox', 'radio', 'select'))) {
                                $options = array_map('trim', explode("\n", $field_data['options']));
                                $new_options = array();
                                foreach ($options as $data) {
                                    if ($data != '') {
                                        $new_options[] = $data;
                                    }
                                }
                                $field_data['options'] = $new_options;
                            }
                            /* Make Lowercase meta key */
                            $field_data['label'] = isset($field_data['label']) ? esc_attr($field_data['label']) : '';
                            $field_data['meta_key'] = isset($field_data['meta_key']) ? sanitize_title(strtolower($field_data['meta_key'])) : '';
                            $field_data['readonly'] = isset($field_data['readonly']) ? sanitize_title(strtolower($field_data['readonly'])) : '';
                            $field_data['regular_expression'] = isset($field_data['regular_expression']) ? stripslashes_deep($field_data['regular_expression']) : ''; 

                            if($field_data['type'] == 'password' &&  in_array($formType, array('registration', 'edit_profile', 'change_password'))) { 
                                $field_data['options']['veryweaktext'] = isset($field_data['options']['veryweaktext']) ? esc_attr($field_data['options']['veryweaktext']) : '';
                                $field_data['options']['weaktext'] = isset($field_data['options']['weaktext']) ? esc_attr($field_data['options']['weaktext']) : '';
                                $field_data['options']['goodtext'] = isset($field_data['options']['goodtext']) ? esc_attr($field_data['options']['goodtext']) : '';
                                $field_data['options']['strongtext'] = isset($field_data['options']['strongtext']) ? esc_attr($field_data['options']['strongtext']) : '';
                            }

                            $save_field_data = array(
                                'arm_form_field_order' => $i,
                                'arm_form_field_slug' => $field_data['meta_key'],
                                'arm_form_field_option' => maybe_serialize($field_data),
                                'arm_form_field_bp_field_id' => (isset($field_data['mapfield']) && $field_data['mapfield'] != '') ? $field_data['mapfield'] : 0,
                                'arm_form_field_status' => 1,
                                'arm_form_field_created_date' => date('Y-m-d H:i:s')
                            );
                            if ($formType == 'registration' || $formType == 'edit_profile') {
                                $save_field_data['arm_form_field_form_id'] = ($arm_action == 'edit_form') ? intval($posted_data['arm_form_id']) : $form_id;
                            } else {
                                $save_field_data['arm_form_field_form_id'] = $login_form_ids[$x];
                            }

                            $wpdb->insert($ARMember->tbl_arm_form_field, $save_field_data);
                            $field_id = $wpdb->insert_id;
                            if ($formType == 'registration' || $formType == 'edit_profile' ) {
                                if ($arm_action == 'edit_form') {
                                    $this->arm_db_add_form_field($field_data, $field_id, $new_form_id);
                                } else {
                                    $this->arm_db_add_form_field($field_data, $field_id, $form_id);
                                }
                            }
                            $i++;
                        }
                    }
                    $x++;
                }
            }
            if ($formType == 'registration' || $formType == 'edit_profile' || $formType == 'change_password') {
                $form_fields_stored = $this->arm_get_member_forms_fields($form_id);
                if (count($form_fields_stored) > 0) {
                    global $password_field_id, $email_field_id;
                    foreach ($form_fields_stored as $key => $field_data) {
                        $enable_repeat_field = isset($field_data['arm_form_field_option']['enable_repeat_field']) ? $field_data['arm_form_field_option']['enable_repeat_field'] : '0';
                        if ($field_data['arm_form_field_option']['type'] == 'email' && $enable_repeat_field == '1') {
                            $email_field_id[$field_data['arm_form_field_order']] = $field_data['arm_form_field_id'];
                        }
                        if ($field_data['arm_form_field_option']['type'] == 'password' && ( $enable_repeat_field == '1' || $formType == 'change_password')) {
                            $password_field_id[$field_data['arm_form_field_order']] = $field_data['arm_form_field_id'];
                        }
                    }
                    foreach ($form_fields_stored as $key => $field_data) {
                        if ($field_data['arm_form_field_option']['type'] == 'repeat_pass') {
                            $field_id = $field_data['arm_form_field_id'];
                            $field_order = $field_data['arm_form_field_order'];
                            $field_data['arm_form_field_option']['ref_field_id'] = isset($password_field_id[$field_order - 1]) ? $password_field_id[$field_order - 1] : 0;
                            $field_options = maybe_serialize($field_data['arm_form_field_option']);
                            $wpdb->update($ARMember->tbl_arm_form_field, array('arm_form_field_option' => $field_options), array('arm_form_field_id' => $field_id));
                        }
                        if ($field_data['arm_form_field_option']['type'] == 'repeat_email') {
                            $field_id = $field_data['arm_form_field_id'];
                            $field_order = $field_data['arm_form_field_order'];
                            $field_data['arm_form_field_option']['ref_field_id'] = isset($email_field_id[$field_order - 1]) ? $email_field_id[$field_order - 1] : 0;
                            $field_options = maybe_serialize($field_data['arm_form_field_option']);
                            $wpdb->update($ARMember->tbl_arm_form_field, array('arm_form_field_option' => $field_options), array('arm_form_field_id' => $field_id));
                        }
                    }
                }
            }

            $final_response = array('message' => 'success', 'form_id' => $form_id, 'form_type' => $formType);
            $final_response['arm_form_set'] = $set_id;
            if ($formType != 'registration' && $formType != 'edit_profile') {
                $final_response['form_ids'] = implode(',', $login_form_ids);
            }
            echo json_encode($final_response);
            die();
        }

        /**
         * Default Form Style
         */
        function arm_default_form_style() {
            return array(
                "form_bg" => "",
                "form_width" => "600",
                "form_width_type" => "px",
                "form_border_width" => "0",
                "form_border_radius" => "8",
                "form_border_style" => "solid",
                "form_layout" => "writer",
                "form_opacity" => '1',
                "form_padding_top" => "30",
                "form_padding_right" => "30",
                "form_padding_bottom" => "30",
                "form_padding_left" => "30",
                "form_title_font_family" => "Helvetica",
                "form_title_font_size" => "28",
                "form_title_font_bold" => "1",
                "form_title_font_italic" => "0",
                "form_title_font_decoration" => "",
                "form_title_position" => "center",
                "form_position" => "center",
                "validation_type" => "modern",
                "validation_position" => "bottom",
                "rtl" => 0,
                "color_scheme" => "blue",
                "main_color" => '#005AEE',
                "form_title_font_color" => '#1A2538',
                "lable_font_color" => '#1A2538',
                'field_font_color' => '#2F3F5C',
                "field_border_color" => '#D3DEF0',
                "field_focus_color" => '#637799',
                "field_bg_color" => '#ffffff',
                "button_back_color" => '#005AEE',
                "button_back_color_gradient" => "#363795",
                "button_font_color" => '#FFFFFF',
                "button_hover_color" => '#0D54C9',
                "button_hover_color_gradient" => "#363795",
                "button_hover_font_color" => '#ffffff',
                "login_link_font_color" => '#005AEE',
                "register_link_font_color" => '#005AEE',
                "form_bg_color" => "#FFFFFF",
                "form_border_color" => "#DCE5F2",
                "prefix_suffix_color" => '#bababa',
                "error_font_color" => '#FF3B3B',
                "error_field_border_color" => '#FF3B3B',
                "error_field_bg_color" => '#ffffff',
                "field_width" => "100",
                "field_width_type" => "%",
                "field_height" => "33",
                "field_spacing" => "15",
                "field_border_width" => "1",
                "field_border_radius" => "0",
                "field_border_style" => "solid",
                "field_position" => "left",
                "field_font_family" => "Helvetica",
                "field_font_size" => "14",
                "field_font_bold" => "0",
                "field_font_italic" => "0",
                "field_font_decoration" => "",
                "label_width" => "250",
                "label_width_type" => "px",
                "label_position" => "block",
                "label_align" => "left",
                "label_hide" => "0",
                "label_font_family" => "Helvetica",
                "label_font_size" => "16",
                "description_font_size" => "16",
                "label_font_bold" => "0",
                "label_font_italic" => "0",
                "label_font_decoration" => "",
                "button_width" => "350",
                "button_width_type" => "px",
                "button_height" => "45",
                "button_height_type" => "px",
                "button_border_radius" => "50",
                "button_style" => "border",
                "button_font_family" => "Helvetica",
                "button_font_size" => "18",
                "button_font_bold" => "1",
                "button_font_italic" => "0",
                "button_font_decoration" => "",
                "button_margin_top" => "10",
                "button_margin_right" => "0",
                "button_margin_bottom" => "0",
                "button_margin_left" => "0",
                "button_position" => "center",
                "enable_social_btn_separator" => '',
                "social_btn_separator" => '<center>' . __('OR', 'ARMember') . '</center>',
                "social_btn_position" => "bottom",
                "social_btn_type" => "horizontal",
                "social_btn_align" => "center",
            );
        }

        function arm_default_form_style_login() {
            $defaultLoginFormStyle = $this->arm_default_form_style();
            $defaultLoginFormStyle['form_width'] = '550';
            $defaultLoginFormStyle['form_width_type'] = 'px';
            $defaultLoginFormStyle['form_border_width'] = '0';
            return $defaultLoginFormStyle;
        }

        function arm_form_color_schemes() {
            $mainColors = array(
                'blue' => array(
                    "main_color" => '#005AEE',
                    "form_title_font_color" => '#1A2538',
                    "lable_font_color" => '#1A2538',
                    'field_font_color' => '#2F3F5C',
                    "field_border_color" => '#D3DEF0',
                    "field_focus_color" => '#637799',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#005AEE',
                    "button_back_color_gradient" => "#363795",
                    "button_font_color" => '#FFFFFF',
                    "button_hover_color" => '#0D54C9',
                    "button_hover_color_gradient" => "#363795",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#005AEE',
                    "register_link_font_color" => '#005AEE',
                    "form_bg_color" => "#FFFFFF",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#FF3B3B',
                    "error_field_bg_color" => '#FF3B3B',
                ),
                'bright_cyan' => array(
                    "main_color" => '#23b7e5',
                    "form_title_font_color" => '#555555',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#23b7e5',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#23b7e5',
                    "button_back_color_gradient" => "#5691c8",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#25c0f0',
                    "button_hover_font_color" => '#ffffff',
                    "button_hover_color_gradient" => "#5691c8",
                    "login_link_font_color" => '#23b7e5',
                    "register_link_font_color" => '#23b7e5',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'green' => array(
                    "main_color" => '#27c24c',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#27c24c',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#27c24c',
                    "button_back_color_gradient" => "#8DC26F",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#29cc50',
                    "button_hover_color_gradient" => "#8DC26F",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#27c24c',
                    "register_link_font_color" => '#27c24c',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'red' => array(
                    "main_color" => '#fd4343',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#fd4343',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#fd4343',
                    "button_back_color_gradient" => "#FF512F",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#fc3535',
                    "button_hover_color_gradient" => "#FF512F",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#fd4343',
                    "register_link_font_color" => '#fd4343',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'purple' => array(
                    "main_color" => '#6164c1',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#6164c1',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#6164c1',
                    "button_back_color_gradient" => "#348AC7",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#8072cc',
                    "button_hover_color_gradient" => "#348AC7",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#6164c1',
                    "register_link_font_color" => '#6164c1',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'orange' => array(
                    "main_color" => '#ff8400',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#ff8400',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#ff8400',
                    "button_back_color_gradient" => "#ffc500",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#fd901c',
                    "button_hover_color_gradient" => "#ffc500",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#ff8400',
                    "register_link_font_color" => '#ff8400',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),                
                'yellow' => array(
                    "main_color" => '#ffce3a',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#ffb400',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#ffb400',
                    "button_back_color_gradient" => "#EDDE5D",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#fdbc20',
                    "button_hover_color_gradient" => "#EDDE5D",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#ffb400',
                    "register_link_font_color" => '#ffb400',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'pink' => array(
                    "main_color" => '#eb3573',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#eb3573',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#eb3573',
                    "button_back_color_gradient" => "#ff5858",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#f8387a',
                    "button_hover_color_gradient" => "#ff5858",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#eb3573',
                    "register_link_font_color" => '#eb3573',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'strong_cyan' => array(
                    "main_color" => '#00c9b6',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#00c9b6',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#00c9b6',
                    "button_back_color_gradient" => "#185a9d",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#01d7c3',
                    "button_hover_color_gradient" => "#185a9d",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#00c9b6',
                    "register_link_font_color" => '#00c9b6',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'gray' => array(
                    "main_color" => '#858585',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#858585',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#858585',
                    "button_back_color_gradient" => "#859398",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#919191',
                    "button_hover_color_gradient" => "#859398",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#858585',
                    "register_link_font_color" => '#858585',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'dark_purple' => array(
                    "main_color" => '#5a5779',
                    "form_title_font_color" => '#313131',
                    "lable_font_color" => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#c7c7c7',
                    "field_focus_color" => '#5a5779',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#5a5779',
                    "button_back_color_gradient" => "#F8CDDA",
                    "button_font_color" => '#ffffff',
                    "login_link_font_color" => '#5a5779',
                    "register_link_font_color" => '#5a5779',
                    "button_hover_color" => '#636086',
                    "button_hover_color_gradient" => "#F8CDDA",
                    "button_hover_font_color" => '#ffffff',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
                'black' => array(
                    "main_color" => '#1a1a1a',
                    'form_title_font_color' => '#313131',
                    'lable_font_color' => '#919191',
                    'field_font_color' => '#242424',
                    "field_border_color" => '#404040',
                    "field_focus_color" => '#000000',
                    "field_bg_color" => '#ffffff',
                    "button_back_color" => '#000000',
                    "button_back_color_gradient" => "#414345",
                    "button_font_color" => '#ffffff',
                    "button_hover_color" => '#2c2c2c',
                    "button_hover_color_gradient" => "#414345",
                    "button_hover_font_color" => '#ffffff',
                    "login_link_font_color" => '#000000',
                    "register_link_font_color" => '#000000',
                    "form_bg_color" => "#ffffff",
                    "prefix_suffix_color" => '#bababa',
                    "error_font_color" => '#ffffff',
                    "error_field_border_color" => '#f05050',
                    "error_field_bg_color" => '#e6594d',
                ),
            );
            return $mainColors;
        }

        function arm_ajax_generate_form_styles($form_id = 0, $form_settings = array(), $atts = array(), $ref_form_id = 0) {
            global $ARMember, $wpdb;
            $form_id = (isset($_POST['form_id'])) ? intval($_POST['form_id']) : $form_id;
            $form_set_id = (isset($_POST['form_set_id'])) ? intval($_POST['form_set_id']) : 0;
            $ref_form_id = (isset($_POST['arm_ref_template'])) ? intval($_POST['arm_ref_template']) : $ref_form_id;
            $container = '.arm_form_' . $form_id;
            $popup_container = '.arm_popup_member_form_' . $form_id;
            $form_settings = isset($_POST['arm_form_settings']) ? $_POST['arm_form_settings'] : $form_settings;
            $isViewProfileLink = (isset($atts['view_profile']) && $atts['view_profile'] == true) ? true : false;
            $new_style_css = '';
            $arm_default_fields_array = array();
            $arm_form_id_array = array();
            if ($form_set_id != 0) {
                $arm_form_ids = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_set_id`='{$form_set_id}'", ARRAY_A);
                foreach ($arm_form_ids as $arm_form_id) {
                    $arm_form_id_array[] = $arm_form_id['arm_form_id'];
                }
                $arm_new_form_ids = implode(',', $arm_form_id_array);
            } else {
                $arm_new_form_ids = $form_id;
            }
            if (!empty($form_id) && $form_id == 'close_account') {
                $arm_default_fields_array = array();
            } else {
	    
                /* Query Monitor Change */
                if( isset($GLOBALS['arm_form_style']) && isset($GLOBALS['arm_form_style'][$arm_new_form_ids])){
                    $arm_form_field_results = $GLOBALS['arm_form_style'][$arm_new_form_ids];
                } else {
                    $arm_form_field_results = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_form_id` in ({$arm_new_form_ids})", ARRAY_A);
                    $GLOBALS['arm_form_style'] = array();
                    $GLOBALS['arm_form_style'][$arm_new_form_ids] = $arm_form_field_results;
                }
                if (!empty($arm_form_field_results)) {
                    foreach ($arm_form_field_results as $arm_field_result) {
                        $fieldID = $arm_field_result['arm_form_field_id'];
                        $fieldSlug = $arm_field_result['arm_form_field_slug'];
                        $fieldIdOptions = maybe_unserialize($arm_field_result['arm_form_field_option']);
                        if (isset($fieldIdOptions['prefix']) && $fieldIdOptions['prefix'] != '') {
                            $fieldPrefix = $fieldIdOptions['prefix'];
                        } else {
                            $fieldPrefix = '';
                        }
                        if ($fieldSlug != '')
                            $arm_default_fields_array[] = array('id' => $fieldID, 'type' => $fieldSlug, 'prefix_type' => $fieldPrefix, 'no_icon_label' => __('No Icon', 'ARMember'));
                    }
                }
            }

            if (!empty($form_settings['style'])) {
                $default_form_style = $this->arm_default_form_style();
                $new_style = $form_settings['style'];
                $form_layout = $new_style['form_layout'];
                $form_settings['custom_css'] = isset($form_settings['custom_css']) ? stripslashes($form_settings['custom_css']) : '';
                $fp_link_margin = (isset($form_settings['forgot_password_link_margin'])) ? $form_settings['forgot_password_link_margin'] : array();
                $fp_link_margin['left'] = (isset($fp_link_margin['left']) && is_numeric($fp_link_margin['left'])) ? $fp_link_margin['left'] : 0;
                $fp_link_margin['top'] = (isset($fp_link_margin['top']) && is_numeric($fp_link_margin['top'])) ? $fp_link_margin['top'] : 0;
                $fp_link_margin['right'] = (isset($fp_link_margin['right']) && is_numeric($fp_link_margin['right'])) ? $fp_link_margin['right'] : 0;
                $fp_link_margin['bottom'] = (isset($fp_link_margin['bottom']) && is_numeric($fp_link_margin['bottom'])) ? $fp_link_margin['bottom'] : 0;
                $reg_link_margin = (isset($form_settings['registration_link_margin'])) ? $form_settings['registration_link_margin'] : array();
                $reg_link_margin['left'] = (isset($reg_link_margin['left']) && is_numeric($reg_link_margin['left'])) ? $reg_link_margin['left'] : 0;
                $reg_link_margin['top'] = (isset($reg_link_margin['top']) && is_numeric($reg_link_margin['top'])) ? $reg_link_margin['top'] : 0;
                $reg_link_margin['right'] = (isset($reg_link_margin['right']) && is_numeric($reg_link_margin['right'])) ? $reg_link_margin['right'] : 0;
                $reg_link_margin['bottom'] = (isset($reg_link_margin['bottom']) && is_numeric($reg_link_margin['bottom'])) ? $reg_link_margin['bottom'] : 0;
                $new_style = shortcode_atts($default_form_style, $new_style);
                $formBGImage = '';
                if (isset($new_style['form_bg']) && !empty($new_style['form_bg'])) {
                    if (file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($new_style['form_bg']))) {
                        $formBGImage = "url({$new_style['form_bg']})";
                    }
                }
                $formBGColor = $new_style['form_bg_color'];
                if (isset($new_style['form_opacity']) && $new_style['form_opacity'] < 1) {
                    $FrmBgOpacity = isset($new_style['form_opacity']) ? $new_style['form_opacity'] : 1;
                    $FrmBgRgba = $this->armHexToRGB($formBGColor);
                    $FrmBgRgbaRed = (!empty($FrmBgRgba['r'])) ? $FrmBgRgba['r'] : 0;
                    $FrmBgRgbaBlue = (!empty($FrmBgRgba['b'])) ? $FrmBgRgba['b'] : 0;
                    $FrmBgRgbaGreen = (!empty($FrmBgRgba['g'])) ? $FrmBgRgba['g'] : 0;
                    $formBGColor = "rgba({$FrmBgRgbaRed},{$FrmBgRgbaGreen},{$FrmBgRgbaBlue},{$FrmBgOpacity})";
                }
                $date_picker_color = $new_style['field_focus_color'];
                $date_picker_color_scheme = $new_style['color_scheme'];
                if ($new_style['field_focus_color'] == '') {
                    $date_picker_color = '#0c7cd5';
                    $date_picker_color_scheme = 'blue';
                }
                $new_style['form_title_font_bold'] = ($new_style['form_title_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
                $new_style['form_title_font_italic'] = ($new_style['form_title_font_italic'] == '1') ? "font-style: italic;" : "font-style: normal;";
                $new_style['form_title_font_decoration'] = (!empty($new_style['form_title_font_decoration'])) ? "text-decoration: " . $new_style['form_title_font_decoration'] . ";" : "text-decoration: none;";
                $new_style['field_font_bold'] = ($new_style['field_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
                $new_style['field_font_italic'] = ($new_style['field_font_italic'] == '1') ? "font-style: italic;" : "font-style: normal;";
                $new_style['field_font_decoration'] = (!empty($new_style['field_font_decoration'])) ? "text-decoration: " . $new_style['field_font_decoration'] . ";" : "text-decoration: none;";
                $new_style['label_font_bold'] = ($new_style['label_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
                $new_style['label_font_italic'] = ($new_style['label_font_italic'] == '1') ? "font-style: italic;" : "font-style: normal;";
                $new_style['label_font_decoration'] = (!empty($new_style['label_font_decoration'])) ? "text-decoration: " . $new_style['label_font_decoration'] . ";" : "text-decoration: none;";
                $new_style['button_font_bold'] = ($new_style['button_font_bold'] == '1') ? "font-weight: bold;" : "font-weight: normal;";
                $new_style['button_font_italic'] = ($new_style['button_font_italic'] == '1') ? "font-style: italic;" : "font-style: normal;";
                $new_style['button_font_decoration'] = (!empty($new_style['button_font_decoration'])) ? "text-decoration: " . $new_style['button_font_decoration'] . ";" : "text-decoration: none;";
                $new_style['button_margin_top'] = (is_numeric($new_style['button_margin_top'])) ? intval($new_style['button_margin_top']) : 5;
                $new_style['button_margin_right'] = (is_numeric($new_style['button_margin_right'])) ? intval($new_style['button_margin_right']) : 0;
                $new_style['button_margin_bottom'] = (is_numeric($new_style['button_margin_bottom'])) ? intval($new_style['button_margin_bottom']) : 0;
                $new_style['button_margin_left'] = (is_numeric($new_style['button_margin_left'])) ? intval($new_style['button_margin_left']) : 0;

                $new_style['form_padding_top'] = (is_numeric($new_style['form_padding_top'])) ? intval($new_style['form_padding_top']) : 20;
                $new_style['form_padding_right'] = (is_numeric($new_style['form_padding_right'])) ? intval($new_style['form_padding_right']) : 20;
                $new_style['form_padding_bottom'] = (is_numeric($new_style['form_padding_bottom'])) ? intval($new_style['form_padding_bottom']) : 20;
                $new_style['form_padding_left'] = (is_numeric($new_style['form_padding_left'])) ? intval($new_style['form_padding_left']) : 20;

                if (!empty($atts) && isset($atts['form_position']) && $atts['form_position'] !== '') {
                    $new_style['form_position'] = $atts['form_position'];
                } else {
                    $new_style['form_position'] = (isset($new_style['form_position'])) ? $new_style['form_position'] : 'center';
                }

                $borderRGB = $this->armHexToRGB($new_style['field_border_color']);
                $borderRGB['r'] = (!empty($borderRGB['r'])) ? $borderRGB['r'] : 0;
                $borderRGB['g'] = (!empty($borderRGB['g'])) ? $borderRGB['g'] : 0;
                $borderRGB['b'] = (!empty($borderRGB['b'])) ? $borderRGB['b'] : 0;

                $borderFocusRGB = $this->armHexToRGB($new_style['field_focus_color']);
                $borderFocusRGB['r'] = (!empty($borderFocusRGB['r'])) ? $borderFocusRGB['r'] : 0;
                $borderFocusRGB['g'] = (!empty($borderFocusRGB['g'])) ? $borderFocusRGB['g'] : 0;
                $borderFocusRGB['b'] = (!empty($borderFocusRGB['b'])) ? $borderFocusRGB['b'] : 0;

                $new_style['form_width'] = (!empty($new_style['form_width'])) ? intval($new_style['form_width']) : '600';
                $new_style['button_width'] = (!empty($new_style['button_width'])) ? intval($new_style['button_width']) : '150';
                $new_style['button_height'] = (!empty($new_style['button_height'])) ? intval($new_style['button_height']) : '35';
                $new_style['button_style'] = (!empty($new_style['button_style'])) ? $new_style['button_style'] : 'flat';
                $armSpinnerStyle = "";
                $armSpinnerHoverStyle = "";
                if ($ref_form_id > 0 && in_array($ref_form_id, array(3))) {
                    $button_back_color = $new_style['button_back_color'];
                    $button_back_color_gradient = $new_style['button_back_color_gradient'];
                    $button_hover_color = $new_style['button_hover_color'];
                    $button_hover_color_gradient = $new_style['button_hover_color_gradient'];

                    $buttonStyle = "background:" . $button_back_color . ";";
                    $buttonStyle .= "background-color:" . $button_back_color_gradient . ";";
                    $buttonStyle .= "background-image:-moz-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                    $buttonStyle .= "background-image:-webkit-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                    $buttonStyle .= "background-image:-webkit-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                    $buttonStyle .= "background-image:-o-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                    $buttonStyle .= "background-image:linear-gradient(to left," . $button_back_color . "," . $button_back_color_gradient . ");";
                    $buttonStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradientType=0);";
                    $buttonStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradeintType=0);";


                    $buttonHoverStyle = "background:" . $button_hover_color . " !important;";
                    $buttonHoverStyle .= "background-color:" . $button_hover_color_gradient . " !important;";
                    $buttonHoverStyle .= "background-image:-moz-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                    $buttonHoverStyle .= "background-image:-webkit-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                    $buttonHoverStyle .= "background-image:-webkit-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                    $buttonHoverStyle .= "background-image:-o-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                    $buttonHoverStyle .= "background-image:linear-gradient(to left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                    $buttonHoverStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradientType=0) !important;";
                    $buttonHoverStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradeintType=0) !important;";
                } else {
                    $buttonStyle = "background: " . $new_style['button_back_color'] . ";border: 1px solid " . $new_style['button_back_color'] . ";color: " . $new_style['button_font_color'] . " !important;";
                    $armSpinnerStyle = "fill:" . $new_style['button_font_color'];
                    $buttonHoverStyle = "background-color: " . $new_style['button_hover_color'] . " !important;border: 1px solid " . $new_style['button_hover_color'] . " !important;color: " . $new_style['button_hover_font_color'] . " !important;";
                    $armSpinnerStyle = "fill:" . $new_style['button_font_color'] . ";";
                    $armSpinnerHoverStyle = "fill:" . $new_style['button_hover_font_color'] . ";";
                }

                if ($new_style['button_style'] == 'border') {

                    $buttonStyle = "background-color: transparent;border: 2px solid " . $new_style['button_back_color'] . ";color: " . $new_style['button_back_color'] . ";";
                    $armSpinnerStyle = "fill:" . $new_style['button_back_color'] . ";";
                    if ($ref_form_id > 0 && in_array($ref_form_id, array(3))) {
                        $buttonHoverStyle = "background:" . $button_hover_color . " !important;";
                        $buttonHoverStyle .= "background-color:" . $button_hover_color_gradient . " !important;";
                        $buttonHoverStyle .= "background-image:-moz-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                        $buttonHoverStyle .= "background-image:-webkit-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                        $buttonHoverStyle .= "background-image:-webkit-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                        $buttonHoverStyle .= "background-image:-o-linear-gradient(left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                        $buttonHoverStyle .= "background-image:linear-gradient(to left," . $button_hover_color . "," . $button_hover_color_gradient . ") !important;";
                        $buttonHoverStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradientType=0) !important;";
                        $buttonHoverStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradeintType=0) !important;";
                        $buttonHoverStyle .= "color: " . $new_style['button_hover_font_color'] . " !important;";
                        $buttonHoverStyle .= "border: none !important;";
                    } else {
                        $buttonHoverStyle = "background-color: " . $new_style['button_hover_color'] . " !important;border: 2px solid " . $new_style['button_hover_color'] . " !important;color: " . $new_style['button_hover_font_color'] . " !important;";
                    }
                    $armSpinnerHoverStyle = "fill:" . $new_style['button_hover_font_color'] . " !important;";
                } elseif ($new_style['button_style'] == 'reverse_border') {

                    if ($ref_form_id > 0 && in_array($ref_form_id, array(3))) {
                        $buttonStyle = "background:" . $button_back_color . ";";
                        $buttonStyle .= "background-color:" . $button_back_color_gradient . ";";
                        $buttonStyle .= "background-image:-moz-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                        $buttonStyle .= "background-image:-webkit-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                        $buttonStyle .= "background-image:-webkit-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                        $buttonStyle .= "background-image:-o-linear-gradient(left," . $button_back_color . "," . $button_back_color_gradient . ");";
                        $buttonStyle .= "background-image:linear-gradient(to left," . $button_back_color . "," . $button_back_color_gradient . ");";
                        $buttonStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradientType=0);";
                        $buttonStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradeintType=0);";
                        $buttonStyle .= "border:none !important;color:" . $new_style['button_font_color'] . " !important;";
                    } else {
                        $buttonStyle = "background: " . $new_style['button_back_color'] . ";border: 2px solid " . $new_style['button_back_color'] . ";color: " . $new_style['button_font_color'] . " important;";
                    }
                    $armSpinnerStyle = "fill:" . $new_style['button_font_color'] . ";";

                    if ($ref_form_id > 0 && in_array($ref_form_id, array(3))) {
                        $buttonHoverStyle = "background-color: transparent !important;background:transparent !important;background-image:transparent !important;";
                    } else {
                        $buttonHoverStyle = "background-color: transparent !important;";
                    }
                    $buttonHoverStyle .= "border: 2px solid " . $new_style['button_hover_color'] . " !important;color: " . $new_style['button_hover_color'] . " !important;";
                    $armSpinnerHoverStyle = "fill:" . $new_style['button_hover_color'];
                } else {
                    $armSpinnerStyle = "fill:" . $new_style['button_font_color'] . ";";
                }

                $form_title_font_family = ($new_style['form_title_font_family'] != 'inherit') ? "font-family: ".$new_style['form_title_font_family'].", sans-serif, 'Trebuchet MS';" : '';
                $label_font_family = ($new_style['label_font_family'] != 'inherit') ? "font-family: ".$new_style['label_font_family'].", sans-serif, 'Trebuchet MS';" : '';
                $field_font_family = ($new_style['field_font_family'] != 'inherit') ? "font-family: ".$new_style['field_font_family'].", sans-serif, 'Trebuchet MS';" : '';
                $button_font_family = ($new_style['button_font_family'] != 'inherit') ? "font-family: ".$new_style['button_font_family'].", sans-serif, 'Trebuchet MS';" : '';

                $formFonts = array($new_style['field_font_family'], $new_style['form_title_font_family'], $new_style['label_font_family'], $new_style['button_font_family']);
                $gFontUrl = $this->arm_get_google_fonts_url($formFonts);
                if (!empty($gFontUrl)) {
	    		if(is_admin())
			{
	                    $new_style_css1 = '<link id="google-font-' . $form_id . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
			}
			else {
                    		wp_enqueue_style( 'google-font-'. $form_id, $gFontUrl, array(), MEMBERSHIP_VERSION );
			}
                }
                $new_style_css = "
						$container .arm_editor_form_fileds_wrapper,
						$container .arm_form_inner_container{
						   padding-top: " . $new_style['form_padding_top'] . "px !important;
						   padding-bottom: " . $new_style['form_padding_bottom'] . "px !important;
						   padding-right: " . $new_style['form_padding_right'] . "px !important;
						   padding-left: " . $new_style['form_padding_left'] . "px !important;
						}
                                                

                                                .arm_popup_member_form_" . $form_id . " .arm_form_message_container{
                                                    max-width: 100%;
                                                    width: " . $new_style['form_width'] . $new_style['form_width_type'] . "; 
                                                    margin: 0 auto;
                                                }
                                                    
						.arm_popup_member_form_" . $form_id . " .arm_form_heading_container,
						$container .arm_form_heading_container,
                        $container .arm_update_card_form_heading_container .arm_form_field_label_wrapper_text,
                        $container .arm_form_heading_container,
	                    $container .arm_form_heading_container .arm_form_field_label_wrapper_text{
							color: " . $new_style['form_title_font_color'] . ";
							" . $form_title_font_family . "
							font-size: " . $new_style['form_title_font_size'] . "px;
							" . $new_style['form_title_font_bold'] . $new_style['form_title_font_italic'] . $new_style['form_title_font_decoration'] . "
						}
						$container .arm_registration_link,
						$container .arm_forgotpassword_link{
							color: " . $new_style['lable_font_color'] . ";
							" . $label_font_family . "
							font-size: " . $new_style['label_font_size'] . "px;
							" . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
						}
	                    $container .arm_pass_strength_meter{
	                        color: " . $new_style['lable_font_color'] . ";
							" . $label_font_family . "
	                    }
	                    $container .arm_registration_link a,
						$container .arm_forgotpassword_link a{
							color: " . $new_style['login_link_font_color'] . " !important;
	                    }
                        $container .arm_reg_login_links a{
                            color: " . $new_style['register_link_font_color'] . " !important;
                        }
	                    $container .arm_form_field_container .arm_registration_link,
	                    $container .arm_form_field_container.arm_registration_link,
	                    $container .arm_registration_link{
	                        margin: " . $reg_link_margin['top'] . "px " . $reg_link_margin['right'] . "px " . $reg_link_margin['bottom'] . "px " . $reg_link_margin['left'] . "px !important;
	                    }
	                    $container .arm_form_field_container .arm_forgotpassword_link,
	                    $container .arm_form_field_container.arm_forgotpassword_link,
	                    $container .arm_forgotpassword_link{
	                        margin: " . $fp_link_margin['top'] . "px " . $fp_link_margin['right'] . "px " . $fp_link_margin['bottom'] . "px " . $fp_link_margin['left'] . "px !important;                     
	                    }";
                if (!is_admin()) {
                    $new_style_css .= "$container .arm_form_field_container .arm_forgotpassword_link,
	                    $container .arm_form_field_container.arm_forgotpassword_link,
	                    $container .arm_forgotpassword_link{
	                        z-index:2;
	                    }";
                }
                if (is_admin()) {
                    $new_style_css .= ".arm_form_field_container[data-type='select'] .arm_form_input_wrapper{
                        z-index:3 !important;
                    }";

                    $new_style_css .= ".arm_form_input_wrapper{
                        z-index:2 !important;
                    }";
                }

                $arm_background_form_field_style = "";
                if($form_layout!='writer')
                {
                    $arm_background_form_field_style = "background-color: " . $new_style['field_bg_color'] . " !important;";
                }
                $new_style_css .= "
	                    $container .arm_close_account_message,
						$container .arm_forgot_password_description {
							color: " . $new_style['lable_font_color'] . ";
							" . $label_font_family . "
							font-size: " . ($new_style['label_font_size'] + 1) . "px;
						}
						$container .arm_form_field_container{
							margin-bottom: " . $new_style['field_spacing'] . "px !important;
						}
						$container .arm_form_input_wrapper{
							max-width: 100%;
							width: 62%;
							width: " . $new_style['field_width'] . $new_style['field_width_type'] . ";
						}
	                    .arm_form_message_container.arm_editor_form_fileds_container.arm_editor_form_fileds_wrapper,
                            .arm_form_message_container1.arm_editor_form_fileds_container.arm_editor_form_fileds_wrapper {
	                        border: none !important;
	                    } 
						.arm_module_forms_container $container,
						.arm_member_form_container $container, 
                        .arm_update_card_form_container $container, .arm_editor_form_fileds_container,.arm_editor_form_fileds_container $container{
							max-width: 100%;
							width: " . $new_style['form_width'] . $new_style['form_width_type'] . ";
							margin: 0 auto;
						}
                                                
                                                .popup_wrapper.arm_popup_wrapper.arm_popup_member_form" . $popup_container . "{
                                                        background: " . $formBGImage . " " . $formBGColor . "!important;
							background-repeat: no-repeat;
							background-position: top left;
							
                                                }
                                                
                                                
                                                
						.arm_module_forms_container $container,
						.arm_member_form_container $container,
                        .arm_update_card_form_container $container, .arm_editor_form_fileds_wrapper{
							background: " . $formBGImage . " " . $formBGColor . ";
							background-repeat: no-repeat;
							background-position: top left;
							border: " . $new_style['form_border_width'] . "px " . $new_style['form_border_style'] . " " . $new_style['form_border_color'] . ";
							border-radius: " . $new_style['form_border_radius'] . "px;
							-webkit-border-radius: " . $new_style['form_border_radius'] . "px;
							-moz-border-radius: " . $new_style['form_border_radius'] . "px;
							-o-border-radius: " . $new_style['form_border_radius'] . "px;
							float: " . $new_style['form_position'] . ";
						}
                                                
                                                .popup_wrapper.arm_popup_wrapper.arm_popup_member_form" . $popup_container . " .arm_module_forms_container $container,
						.popup_wrapper.arm_popup_wrapper.arm_popup_member_form" . $popup_container . " .arm_member_form_container $container, .arm_update_card_form_container $container{
                                                        background: none !important;
							
							
                                                }
                                                
	                    .arm_form_msg.arm_member_form_container, .arm_form_msg .arm_form_message_container,
                            .arm_form_msg.arm_member_form_container, .arm_form_msg .arm_form_message_container1{
	                        float: " . $new_style['form_position'] . ";
	                        width: " . $new_style['form_width'] . $new_style['form_width_type'] . ";    
	                    }
						$container .arm_form_label_wrapper{
							max-width: 100%;
							width: 30%;
							width: " . $new_style['label_width'] . $new_style['label_width_type'] . ";
						}
	                    $container md-input-container.md-input-invalid.md-input-focused label,
						$container md-input-container.md-default-theme:not(.md-input-invalid).md-input-focused label,
	                    $container md-input-container.md-default-theme.md-input-invalid.md-input-focused label,
						$container md-input-container:not(.md-input-invalid).md-input-focused label,
						$container .arm_form_field_label_text,
						$container .arm_member_form_field_label .arm_form_field_label_text,
                                                $container .arm_member_form_field_description .arm_form_field_description_text,
						$container .arm_form_label_wrapper .required_tag,
						$container .arm_form_input_container label,
                        $container md-input-container:not(.md-input-invalid) md-select .md-select-value.md-select-placeholder,
						$container md-input-container:not(.md-input-invalid).md-input-has-value label
                                                    {
							color: " . $new_style['lable_font_color'] . ";
							" . $label_font_family . "
							font-size: " . $new_style['label_font_size'] . "px;
							cursor: pointer;
							margin: 0px !important;
                                                        line-height : " . ($new_style['label_font_size'] + 12) . "px;
							" . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
						}
                                                $container .arm_member_form_field_description .arm_form_field_description_text
                                                    { 
                                                        font-size: " . $new_style['description_font_size'] . "px; 
                                                        line-height: " . $new_style['description_font_size'] . "px; 
                                                    }
                        md-select-menu.md-default-theme md-content md-option:not([disabled]):focus, md-select-menu md-content md-option:not([disabled]):focus, md-select-menu.md-default-theme md-content md-option:not([disabled]):hover, md-select-menu md-content md-option:not([disabled]):hover {
                            background-color : ". $new_style['field_focus_color'] . " ;
                            color : #ffffff;
                        }
	                    .armSelectOption" . $form_id . ", .arm_payment_transfer_mode_option_".$form_id." {
							" . $field_font_family . "
							font-size: " . $new_style['label_font_size'] . "px;
							" . $new_style['field_font_bold'] . $new_style['field_font_italic'] . $new_style['field_font_decoration'] . "
						}
						$container .arm_form_input_container.arm_form_input_container_section{
							color: " . $new_style['lable_font_color'] . ";
	                        " . $label_font_family . "
	                    }
						$container md-radio-button, $container md-checkbox{
							color:" . $new_style['lable_font_color'] . ";
							" . $label_font_family . "
							font-size: " . $new_style['label_font_size'] . "px;
							cursor: pointer;
							" . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
						}
						md-select-menu.md-default-theme md-option.armSelectOption" . $form_id . "[selected],
						md-select-menu md-option.armSelectOption" . $form_id . "[selected]{
							font-weight: bold;
							color:" . $new_style['field_font_color'] . ";
						}
	                    $container .arm_form_input_container input{
	                        height: " . $new_style['field_height'] . "px;
	                    }
                        $container.arm_standard_validation_type.arm_form_layout_iconic .arm_editor_prefix, 
                        $container.arm_standard_validation_type.arm_form_layout_iconic .arm_editor_suffix, 
                        $container.arm_standard_validation_type.arm_form_layout_rounded .arm_editor_prefix, 
                        $container.arm_standard_validation_type.arm_form_layout_rounded .arm_editor_suffix {
                            height: " . ($new_style['field_height'] + 5) . "px;
                        }

	                    $container .arm_apply_coupon_container .arm_coupon_submit_wrapper .arm_apply_coupon_btn{
	                        min-height: " . ($new_style['field_height'] + 2) . "px;
	                        margin: 0;
	                    }
						$container .arm_form_input_container input,
						$container .arm_form_input_container textarea,
						$container .arm_form_input_container select,
						$container .arm_form_input_container md-select md-select-value{

	                        ".$arm_background_form_field_style."
							border: " . $new_style['field_border_width'] . "px " . $new_style['field_border_style'] . " " . $new_style['field_border_color'] . ";
							border-color: " . $new_style['field_border_color'] . ";
							border-radius: " . $new_style['field_border_radius'] . "px !important;
							-webkit-border-radius: " . $new_style['field_border_radius'] . "px !important;
							-moz-border-radius: " . $new_style['field_border_radius'] . "px !important;
							-o-border-radius: " . $new_style['field_border_radius'] . "px !important;
							color:" . $new_style['field_font_color'] . ";
							" . $field_font_family . "
							font-size: " . $new_style['field_font_size'] . "px;
							" . $new_style['field_font_bold'] . $new_style['field_font_italic'] . $new_style['field_font_decoration'] . "
							height: " . $new_style['field_height'] . "px;
						}
						$container .armFileUploadWrapper .armFileDragArea{
							border-color: " . $new_style['field_border_color'] . ";
						}
						$container .armFileUploadWrapper .armFileDragArea.arm_dragover{
							border-color: " . $new_style['field_focus_color'] . ";
						}
						$container md-checkbox.md-default-theme.md-checked .md-ink-ripple,
						$container md-checkbox.md-checked .md-ink-ripple{
							color: rgba(" . $borderRGB['r'] . ", " . $borderRGB['g'] . ", " . $borderRGB['b'] . ", 0.87);
						}
						$container md-radio-button.md-default-theme.md-checked .md-off,
						$container md-radio-button.md-default-theme .md-off,
						$container md-radio-button.md-checked .md-off,
						$container md-radio-button .md-off,
						$container md-checkbox.md-default-theme .md-icon, 
						$container md-checkbox .md-icon{
							border-color: " . $new_style['field_border_color'] . ";
						}
						$container md-radio-button.md-default-theme .md-on,
						$container md-radio-button .md-on,
						$container md-checkbox.md-default-theme.md-checked .md-icon,
						$container md-checkbox.md-checked .md-icon{
							background-color: " . $new_style['field_focus_color'] . ";
						}
						md-option.armSelectOption" . $form_id . " .md-ripple.md-ripple-placed,
						md-option.armSelectOption" . $form_id . " .md-ripple.md-ripple-scaled,
						$container .md-ripple.md-ripple-placed,
						$container .md-ripple.md-ripple-scaled{
							background-color: rgba(" . $borderFocusRGB['r'] . ", " . $borderFocusRGB['g'] . ", " . $borderFocusRGB['b'] . ", 0.87) !important;
						}
						$container .md-button .md-ripple.md-ripple-placed,
						$container .md-button .md-ripple.md-ripple-scaled{
							background-color: rgb(255, 255, 255) !important;
						}
						$container md-checkbox.md-focused:not([disabled]):not(.md-checked) .md-container:before{
							background-color: rgba(" . $borderFocusRGB['r'] . ", " . $borderFocusRGB['g'] . ", " . $borderFocusRGB['b'] . ", 0.12) !important;
						}
						$container md-radio-group.md-default-theme.md-focused:not(:empty) .md-checked .md-container:before,
						$container md-radio-group.md-focused:not(:empty) .md-checked .md-container:before,
						$container md-checkbox.md-default-theme.md-checked.md-focused .md-container:before,
						$container md-checkbox.md-checked.md-focused .md-container:before{
							background-color: rgba(" . $borderFocusRGB['r'] . ", " . $borderFocusRGB['g'] . ", " . $borderFocusRGB['b'] . ", 0.26) !important;
						}
						$container.arm_form_layout_writer .arm_form_wrapper_container .select-wrapper input.select-dropdown,
						$container.arm_form_layout_writer .arm_form_wrapper_container .file-field input.file-path{
							border-color: " . $new_style['field_border_color'] . ";
							border-width: 0 0 " . $new_style['field_border_width'] . "px 0 !important;
						}
						$container.arm_form_layout_writer .arm_form_input_box.select-wrapper{border:0 !important;}
						$container .arm_form_input_container input:focus,
						$container .arm_form_input_container textarea:focus,
						$container .arm_form_input_container select:focus,
						$container .arm_form_input_container md-select:focus md-select-value,
						$container .arm_form_input_container md-select[aria-expanded='true'] + md-select-value{
                            color: ". $new_style['field_font_color'] .";
							border: " . $new_style['field_border_width'] . "px " . $new_style['field_border_style'] . " " . $new_style['field_focus_color'] . ";
							border-color: " . $new_style['field_focus_color'] . ";
						}
						$container .arm_uploaded_file_info .armbar{
							background-color: " . $new_style['field_focus_color'] . ";
						}
						$container .arm_form_input_box.arm_error_msg,
						$container .arm_form_input_box.arm_invalid,
						$container .arm_form_input_box.ng-invalid:not(.ng-untouched) md-select-value,
						$container md-input-container .md-input.ng-invalid:not(.ng-untouched){
							border: " . $new_style['field_border_width'] . "px " . $new_style['field_border_style'] . " " . $new_style['error_field_border_color'] . ";
							border-color: " . $new_style['error_field_border_color'] . " !important;
						}
						$container .arm_form_message_container .arm_success_msg,
						$container .arm_form_message_container .arm_error_msg,
                                                $container .arm_form_message_container1 .arm_success_msg,
                                                $container .arm_form_message_container1 .arm_success_msg1,
						$container .arm_form_message_container1 .arm_error_msg,
                                                    $container .arm_form_message_container .arm_success_msg a{
							" . $label_font_family . "
	                        text-decoration: none !important;
						}
                        $container .arm_coupon_field_wrapper .success.notify_msg{
                            " . $label_font_family . "
                            text-decoration: none !important;
                        }
						$container md-select.md-default-theme.ng-invalid.ng-dirty .md-select-value,
						$container md-select.ng-invalid.ng-dirty .md-select-value{
							color: " . $new_style['field_font_color'] . " !important;
							border-color: " . $new_style['error_field_border_color'] . " !important;
						}
	                    $container.arm_form_layout_writer .arm_form_input_container textarea{
	                        -webkit-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
	                        -moz-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_border_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 0 100%;
							max-height:150px;
	                                        }
						$container.arm_form_layout_writer .arm_form_input_container input,
						$container.arm_form_layout_writer .arm_form_input_container select,
						$container.arm_form_layout_writer .arm_form_input_container md-select md-select-value{
							-webkit-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_border_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, " . $new_style['field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 0 100%;
						}
						$container.arm_form_layout_writer .arm_form_input_container input:focus,
						$container.arm_form_layout_writer .arm_form_input_container select:focus,
						$container.arm_form_layout_writer .arm_form_input_container md-select:focus md-select-value,
						$container.arm_form_layout_writer .arm_form_input_container md-select[aria-expanded='true'] + md-select-value{
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
						}
	                    $container .arm_editor_form_fileds_container .arm_form_input_box.arm_error_msg,
						$container .arm_editor_form_fileds_container .arm_form_input_box.arm_invalid,
						$container .arm_editor_form_fileds_container .arm_form_input_box.ng-invalid:not(.ng-untouched) md-select-value,
						$container .arm_editor_form_fileds_container md-input-container .md-input.ng-invalid:not(.ng-untouched){
							border: " . $new_style['field_border_width'] . "px " . $new_style['field_border_style'] . " " . $new_style['field_border_color'] . ";
							border-color: " . $new_style['field_border_color'] . " !important;
						}
	                    $container .arm_editor_form_fileds_container .arm_form_input_container input:focus,
	                    $container .arm_editor_form_fileds_container md-input-container .md-input.ng-invalid:not(.ng-untouched):focus,
						$container .arm_editor_form_fileds_container .arm_form_input_container textarea:focus,
						$container .arm_editor_form_fileds_container .arm_form_input_container select:focus,
						$container .arm_editor_form_fileds_container .arm_form_input_container md-select:focus md-select-value,
						$container .arm_editor_form_fileds_container .arm_form_input_container md-select[aria-expanded='true'] + md-select-value{
							border: " . $new_style['field_border_width'] . "px " . $new_style['field_border_style'] . " " . $new_style['field_focus_color'] . ";
							border-color: " . $new_style['field_focus_color'] . " !important;
						}
	                    $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_box.arm_error_msg:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_box.arm_invalid:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_box.ng-invalid:not(.ng-untouched):focus md-select-value,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container md-input-container .md-input.ng-invalid:not(.ng-untouched):focus,
	                    $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_container input:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_container select:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_container md-select:focus md-select-value,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm_form_input_container md-select[aria-expanded='true'] + md-select-value{
	                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
	                        border-color: " . $new_style['field_focus_color'] . " !important;
	                    }
	                    $container.arm_form_layout_writer .arm_form_input_container textarea:focus{
	                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_focus_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_focus_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
	                    }
	                    $container.arm_form_layout_writer textarea.arm_form_input_box.arm_error_msg:focus,
	                    $container.arm_form_layout_writer textarea.arm_form_input_box.arm_invalid:focus,
	                    $container.arm_form_layout_writer textarea.arm_form_input_box.ng-invalid:not(.ng-untouched):focus md-select-value,
	                    $container.arm_form_layout_writer .arm_form_input_container_textarea md-input-container .md-input.ng-invalid:not(.ng-untouched):focus{
	                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['error_field_border_color'] . " 4%);
	                        background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, " . $new_style['error_field_border_color'] . " 4%);
	                        background-repeat: no-repeat;
	                        background-position: 0 0;
	                        background-size: 100% 100%;
	                    }
						$container.arm_form_layout_writer .arm_form_input_box.arm_error_msg:focus,
						$container.arm_form_layout_writer .arm_form_input_box.arm_invalid:focus,
						$container.arm_form_layout_writer .arm_form_input_box.ng-invalid:not(.ng-untouched):focus md-select-value,
						$container.arm_form_layout_writer md-input-container .md-input.ng-invalid:not(.ng-untouched):focus{
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['error_field_border_color'] . " 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, " . $new_style['error_field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
						}
						$container.arm_form_layout_iconic:not(.arm_standard_validation_type) .arm_error_msg_box .arm_error_msg,
						$container.arm_form_layout_rounded:not(.arm_standard_validation_type) .arm_error_msg_box .arm_error_msg,
						$container:not(.arm_standard_validation_type) .arm_error_msg_box .arm_error_msg{
							color: " . $new_style['error_font_color'] . ";
							background: " . $new_style['error_field_bg_color'] . ";
	                        " . $label_font_family . "
							font-size: 14px;
	                        font-size: " . $new_style['label_font_size'] . "px;
							padding-left: 5px;
							padding-right: 5px;
	                        text-decoration: none !important;
						}
                        $container.arm_standard_validation_type .arm_error_msg_box .arm_error_msg{
                            color: " . $new_style['error_font_color'] . ";
                            " . $label_font_family . "
                            font-size: " . $new_style['label_font_size'] . "px;
                        }
						$container .arm_msg_pos_right .arm_error_msg_box .arm_error_box_arrow:after{border-right-color: " . $new_style['error_field_bg_color'] . " !important;} 
						$container .arm_msg_pos_left .arm_error_msg_box .arm_error_box_arrow:after{border-left-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_msg_pos_top .arm_error_msg_box .arm_error_box_arrow:after{border-top-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_msg_pos_bottom .arm_error_msg_box .arm_error_box_arrow:after{border-bottom-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_writer_error_msg_box{
							color: " . $new_style['error_font_color'] . ";
							font-size: " . $new_style['field_font_size'] . "px;
							font-size: 14px;
						}
						$container .arm_form_field_submit_button.md-button .md-ripple-container{
							border-radius: " . $new_style['button_border_radius'] . "px;
							-webkit-border-radius: " . $new_style['button_border_radius'] . "px;
							-moz-border-radius: " . $new_style['button_border_radius'] . "px;
							-o-border-radius: " . $new_style['button_border_radius'] . "px;
						}
						$container .arm_form_field_submit_button.md-button,
						$container .arm_form_field_submit_button{
							border-radius: " . $new_style['button_border_radius'] . "px;
							-webkit-border-radius: " . $new_style['button_border_radius'] . "px;
							-moz-border-radius: " . $new_style['button_border_radius'] . "px;
							-o-border-radius: " . $new_style['button_border_radius'] . "px;
							width: auto;
							max-width: 100%;
							width: " . $new_style['button_width'] . $new_style['button_width_type'] . ";
							min-height: 35px;
							min-height: " . $new_style['button_height'] . $new_style['button_height_type'] . ";
							padding: 0 10px;
							" . $button_font_family . "
							font-size: " . $new_style['button_font_size'] . "px;
							margin: " . $new_style['button_margin_top'] . "px " . $new_style['button_margin_right'] . "px " . $new_style['button_margin_bottom'] . "px " . $new_style['button_margin_left'] . "px;
							" . $new_style['button_font_bold'] . $new_style['button_font_italic'] . $new_style['button_font_decoration'] . "
							text-transform: none;
	                        " . $buttonStyle . "
						}
	                    .arm_form_field_submit_button.arm_form_field_container_button.arm_editable_input_button{
	                        height: " . $new_style['button_height'] . $new_style['button_height_type'] . ";
	                    }
	                    $container .arm_setup_submit_btn_wrapper .arm_form_field_submit_button.md-button,
	                    $container .arm_setup_submit_btn_wrapper .arm_form_field_submit_button{
	                        " . $buttonStyle . "
	                    }
                        $container .arm_form_field_submit_button.md-button #arm_form_loader,
						$container .arm_form_field_submit_button #arm_form_loader{
                            " . $armSpinnerStyle . "
                            }
						/*$container button:hover,*/
						$container .arm_form_field_submit_button:hover,
						$container .arm_form_field_submit_button.md-button:hover,
						$container .arm_form_field_submit_button.md-button:not([disabled]):hover,
						$container .arm_form_field_submit_button.md-button.md-default-theme:not([disabled]):hover,
						$container.arm_form_layout_writer .arm_form_wrapper_container .arm_form_field_submit_button.btn:hover,
						$container.arm_form_layout_writer .arm_form_wrapper_container .arm_form_field_submit_button.btn-large:hover{
							" . $buttonHoverStyle . "
						}
                        $container .arm_form_field_submit_button:hover #arm_form_loader,
						$container .arm_form_field_submit_button.md-button:hover #arm_form_loader,
						$container .arm_form_field_submit_button.md-button:not([disabled]):hover #arm_form_loader,
						$container .arm_form_field_submit_button.md-button.md-default-theme:not([disabled]):hover #arm_form_loader,
						$container.arm_form_layout_writer .arm_form_wrapper_container .arm_form_field_submit_button.btn:hover #arm_form_loader,
						$container.arm_form_layout_writer .arm_form_wrapper_container .arm_form_field_submit_button.btn-large:hover #arm_form_loader{
                            " . $armSpinnerHoverStyle . "
                        }
	                    $container .arm_form_wrapper_container .armFileUploadWrapper .armFileBtn,
						$container .arm_form_wrapper_container .armFileUploadContainer{
							border: 1px solid " . $new_style['button_back_color'] . ";
							background-color: " . $new_style['button_back_color'] . ";
							color: " . $new_style['button_font_color'] . ";
						}
						$container .arm_form_wrapper_container .armFileUploadWrapper .armFileBtn:hover,
						$container .arm_form_wrapper_container .armFileUploadContainer:hover{
	                        background-color: " . $new_style['button_hover_color'] . " !important;
							border-color: " . $new_style['button_hover_color'] . " !important;
							color: " . $new_style['button_hover_font_color'] . " !important;
	                    }
						$container .arm_field_fa_icons{color: " . $new_style['prefix_suffix_color'] . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.today:before{border: 3px solid " . $date_picker_color . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.active,
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.active:hover{
							color: " . $date_picker_color . " !important;
							background: url(" . MEMBERSHIP_IMAGES_URL . "/bootstrap_datepicker_" . $date_picker_color_scheme . ".png) no-repeat !important;
						}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td span:hover{border-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td span.active{background-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .arm_cal_header{background-color: " . $date_picker_color . " !important;}
						.arm_date_field_$form_id .arm_cal_month{
							background-color: " . $date_picker_color . " !important;
							border-bottom: 1px solid " . $date_picker_color . ";
						}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.day:hover {
							background: url(" . MEMBERSHIP_IMAGES_URL . "/bootstrap_datepicker_hover.png) no-repeat;
						}
						.arm_date_field_$form_id .arm_cal_hour:hover, .arm_date_field_$form_id .arm_cal_minute:hover{border-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .timepicker-picker .btn-primary{
							background-color: " . $date_picker_color . ";
							border-color: " . $date_picker_color . ";
						}
						.arm_date_field_$form_id .armglyphicon-time:before,
						.arm_date_field_$form_id .armglyphicon-calendar:before,
						.arm_date_field_$form_id .armglyphicon-chevron-up:before,
						.arm_date_field_$form_id .armglyphicon-chevron-down:before{color: " . $date_picker_color . ";}
						" . stripslashes_deep($form_settings['custom_css']) . "
					";
                    $new_style_css .= $container." stop.arm_social_connect_svg { stop-color:".$new_style['button_back_color']."; } ";
                if ($isViewProfileLink) {
                    global $arm_global_settings;
                    $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
                    $linkFonts = isset($frontfontstyle['frontOptions']['link_font']) ? $frontfontstyle['frontOptions']['link_font'] : '';
                    $new_style_css .= "
	                        .arm_shortcode_form .arm_view_profile_link_container a,
	                        .arm_shortcode_form .arm_view_profile_link_container a.arm_view_profile_link{
	                            {$linkFonts['font']}
	                        }
	                    ";
                    if (isset($frontfontstyle['google_font_url']) && !empty($frontfontstyle['google_font_url'])) {
                        if(is_admin())
			{
				$new_style_css1 .= '<link id="google-font-' . $form_id . '" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />';
			}
                        else {
				wp_enqueue_style( 'google-font-'. $form_id, $frontfontstyle['google_font_url'], array(), MEMBERSHIP_VERSION );
			}
                    }
                }
            }
	    if(empty($new_style_css1))
	    {
	    	$new_style_css1 = '';
	    }
            $arm_response = array('arm_link' => $new_style_css1, 'arm_css' => $new_style_css, 'field_array' => $arm_default_fields_array);
            if (isset($_POST['action']) && $_POST['action'] == 'arm_ajax_generate_form_styles') {
                echo json_encode($arm_response);
                exit;
            }
            return $arm_response;
        }

        function armHexToRGB($hex = '#000000') {
            $rgb = array();
            if (!empty($hex)) {
                list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
                $rgb = array(
                    'r' => $r,
                    'g' => $g,
                    'b' => $b,
                );
            }
            return $rgb;
        }

        function arm_fonts_list() {
            global $wp, $wpdb, $ARMember;
            $default_fonts = array('inherit', 'Arial', 'Helvetica', 'sans-serif', 'Lucida Grande', 'Lucida Sans Unicode', 'Tahoma', 'Times New Roman', 'Courier New', 'Verdana', 'Geneva', 'Courier', 'Monospace', 'Times', 'Open Sans Semibold', 'Open Sans Bold' );
            /* Default Fonts */
            $fonts_li = '<ol class="arm_selectbox_heading"> Default Fonts</ol>';
            foreach ($default_fonts as $font) {
                if ($font == 'inherit') {
                    $fonts_li .= '<li data-value="' . $font . '" data-label="' . __('Inherit Fonts', 'ARMember') . '">' . __('Inherit Fonts', 'ARMember') . '</li>';
                } else {
                    $fonts_li .= '<li data-value="' . $font . '" data-label="' . $font . '">' . $font . '</li>';
                }
            }
            /* Google Fonts */
            $g_fonts = $this->arm_google_fonts_list();
            $fonts_li .= '<ol class="arm_selectbox_heading"> ' . __('Google Fonts', 'ARMember') . '</ol>';
            foreach ($g_fonts as $font) {
                $fonts_li .= '<li data-value="' . $font . '" data-label="' . $font . '">' . $font . '</li>';
            }
            return $fonts_li;
        }

        function arm_google_fonts_list() {
            global $wp, $wpdb, $ARMember;
            $google_fonts = array( 'ABeeZee', 'Abel', 'Abhaya Libre', 'Abril Fatface', 'Aclonica', 'Acme', 'Actor', 'Adamina', 'Advent Pro', 'Aguafina Script', 'Akronim', 'Aladin', 'Aldrich', 'Alef', 'Alegreya', 'Alegreya SC', 'Alegreya Sans', 'Alegreya Sans SC', 'Alex Brush', 'Alfa Slab One', 'Alice', 'Alike', 'Alike Angular', 'Allan', 'Allerta', 'Allerta Stencil', 'Allura', 'Almendra', 'Almendra Display', 'Almendra SC', 'Amarante', 'Amaranth', 'Amatic SC', 'Amethysta', 'Amiko', 'Amiri', 'Amita', 'Anaheim', 'Andada', 'Andika', 'Angkor', 'Annie Use Your Telescope', 'Anonymous Pro', 'Antic', 'Antic Didone', 'Antic Slab', 'Anton', 'Arapey', 'Arbutus', 'Arbutus Slab', 'Architects Daughter', 'Archivo', 'Archivo Black', 'Archivo Narrow', 'Aref Ruqaa', 'Arima Madurai', 'Arimo', 'Arizonia', 'Armata', 'Arsenal', 'Artifika', 'Arvo', 'Arya', 'Asap', 'Asap Condensed', 'Asar', 'Asset', 'Assistant', 'Astloch', 'Asul', 'Athiti', 'Atma', 'Atomic Age', 'Aubrey', 'Audiowide', 'Autour One', 'Average', 'Average Sans', 'Averia Gruesa Libre', 'Averia Libre', 'Averia Sans Libre', 'Averia Serif Libre', 'Bad Script', 'Bahiana', 'Bai Jamjuree', 'Baloo', 'Baloo Bhai', 'Baloo Bhaijaan', 'Baloo Bhaina', 'Baloo Chettan', 'Baloo Da', 'Baloo Paaji', 'Baloo Tamma', 'Baloo Tammudu', 'Baloo Thambi', 'Balthazar', 'Bangers', 'Barlow', 'Barlow Condensed', 'Barlow Semi Condensed', 'Barrio', 'Basic', 'Battambang', 'Baumans', 'Bayon', 'Belgrano', 'Bellefair', 'Belleza', 'BenchNine', 'Bentham', 'Berkshire Swash', 'Bevan', 'Bigelow Rules', 'Bigshot One', 'Bilbo', 'Bilbo Swash Caps', 'BioRhyme', 'BioRhyme Expanded', 'Biryani', 'Bitter', 'Black And White Picture', 'Black Han Sans', 'Black Ops One', 'Bokor', 'Bonbon', 'Boogaloo', 'Bowlby One', 'Bowlby One SC', 'Brawler', 'Bree Serif', 'Bubblegum Sans', 'Bubbler One', 'Buda', 'Buenard', 'Bungee', 'Bungee Hairline', 'Bungee Inline', 'Bungee Outline', 'Bungee Shade', 'Butcherman', 'Butterfly Kids', 'Cabin', 'Cabin Condensed', 'Cabin Sketch', 'Caesar Dressing', 'Cagliostro', 'Cairo', 'Calligraffitti', 'Cambay', 'Cambo', 'Candal', 'Cantarell', 'Cantata One', 'Cantora One', 'Capriola', 'Cardo', 'Carme', 'Carrois Gothic', 'Carrois Gothic SC', 'Carter One', 'Catamaran', 'Caudex', 'Caveat', 'Caveat Brush', 'Cedarville Cursive', 'Ceviche One', 'Chakra Petch', 'Changa', 'Changa One', 'Chango', 'Charmonman', 'Chathura', 'Chau Philomene One', 'Chela One', 'Chelsea Market', 'Chenla', 'Cherry Cream Soda', 'Cherry Swash', 'Chewy', 'Chicle', 'Chivo', 'Chonburi', 'Cinzel', 'Cinzel Decorative', 'Clicker Script', 'Coda', 'Coda Caption', 'Codystar', 'Coiny', 'Combo', 'Comfortaa', 'Coming Soon', 'Concert One', 'Condiment', 'Content', 'Contrail One', 'Convergence', 'Cookie', 'Copse', 'Corben', 'Cormorant', 'Cormorant Garamond', 'Cormorant Infant', 'Cormorant SC', 'Cormorant Unicase', 'Cormorant Upright', 'Courgette', 'Cousine', 'Coustard', 'Covered By Your Grace', 'Crafty Girls', 'Creepster', 'Crete Round', 'Crimson Text', 'Croissant One', 'Crushed', 'Cuprum', 'Cute Font', 'Cutive', 'Cutive Mono', 'Damion', 'Dancing Script', 'Dangrek', 'David Libre', 'Dawning of a New Day', 'Days One', 'Dekko', 'Delius', 'Delius Swash Caps', 'Delius Unicase', 'Della Respira', 'Denk One', 'Devonshire', 'Dhurjati', 'Didact Gothic', 'Diplomata', 'Diplomata SC', 'Do Hyeon', 'Dokdo', 'Domine', 'Donegal One', 'Doppio One', 'Dorsa', 'Dosis', 'Dr Sugiyama', 'Duru Sans', 'Dynalight', 'EB Garamond', 'Eagle Lake', 'East Sea Dokdo', 'Eater', 'Economica', 'Eczar', 'El Messiri', 'Electrolize', 'Elsie', 'Elsie Swash Caps', 'Emblema One', 'Emilys Candy', 'Encode Sans', 'Encode Sans Condensed', 'Encode Sans Expanded', 'Encode Sans Semi Condensed', 'Encode Sans Semi Expanded', 'Engagement', 'Englebert', 'Enriqueta', 'Erica One', 'Esteban', 'Euphoria Script', 'Ewert', 'Exo', 'Exo 2', 'Expletus Sans', 'Fahkwang', 'Fanwood Text', 'Farsan', 'Fascinate', 'Fascinate Inline', 'Faster One', 'Fasthand', 'Fauna One', 'Faustina', 'Federant', 'Federo', 'Felipa', 'Fenix', 'Finger Paint', 'Fira Mono', 'Fira Sans', 'Fira Sans Condensed', 'Fira Sans Extra Condensed', 'Fjalla One', 'Fjord One', 'Flamenco', 'Flavors', 'Fondamento', 'Fontdiner Swanky', 'Forum', 'Francois One', 'Frank Ruhl Libre', 'Freckle Face', 'Fredericka the Great', 'Fredoka One', 'Freehand', 'Fresca', 'Frijole', 'Fruktur', 'Fugaz One', 'GFS Didot', 'GFS Neohellenic', 'Gabriela', 'Gaegu', 'Gafata', 'Galada', 'Galdeano', 'Galindo', 'Gamja Flower', 'Gentium Basic', 'Gentium Book Basic', 'Geo', 'Geostar', 'Geostar Fill', 'Germania One', 'Gidugu', 'Gilda Display', 'Give You Glory', 'Glass Antiqua', 'Glegoo', 'Gloria Hallelujah', 'Goblin One', 'Gochi Hand', 'Gorditas', 'Gothic A1', 'Goudy Bookletter 1911', 'Graduate', 'Grand Hotel', 'Gravitas One', 'Great Vibes', 'Griffy', 'Gruppo', 'Gudea', 'Gugi', 'Gurajada', 'Habibi', 'Halant', 'Hammersmith One', 'Hanalei', 'Hanalei Fill', 'Handlee', 'Hanuman', 'Happy Monkey', 'Harmattan', 'Headland One', 'Heebo', 'Henny Penny', 'Herr Von Muellerhoff', 'Hi Melody', 'Hind', 'Hind Guntur', 'Hind Madurai', 'Hind Siliguri', 'Hind Vadodara', 'Holtwood One SC', 'Homemade Apple', 'Homenaje', 'IBM Plex Mono', 'IBM Plex Sans', 'IBM Plex Sans Condensed', 'IBM Plex Serif', 'IM Fell DW Pica', 'IM Fell DW Pica SC', 'IM Fell Double Pica', 'IM Fell Double Pica SC', 'IM Fell English', 'IM Fell English SC', 'IM Fell French Canon', 'IM Fell French Canon SC', 'IM Fell Great Primer', 'IM Fell Great Primer SC', 'Iceberg', 'Iceland', 'Imprima', 'Inconsolata', 'Inder', 'Indie Flower', 'Inika', 'Inknut Antiqua', 'Irish Grover', 'Istok Web', 'Italiana', 'Italianno', 'Itim', 'Jacques Francois', 'Jacques Francois Shadow', 'Jaldi', 'Jim Nightshade', 'Jockey One', 'Jolly Lodger', 'Jomhuria', 'Josefin Sans', 'Josefin Slab', 'Joti One', 'Jua', 'Judson', 'Julee', 'Julius Sans One', 'Junge', 'Jura', 'Just Another Hand', 'Just Me Again Down Here', 'K2D', 'Kadwa', 'Kalam', 'Kameron', 'Kanit', 'Kantumruy', 'Karla', 'Karma', 'Katibeh', 'Kaushan Script', 'Kavivanar', 'Kavoon', 'Kdam Thmor', 'Keania One', 'Kelly Slab', 'Kenia', 'Khand', 'Khmer', 'Khula', 'Kirang Haerang', 'Kite One', 'Knewave', 'KoHo', 'Kodchasan', 'Kosugi', 'Kosugi Maru', 'Kotta One', 'Koulen', 'Kranky', 'Kreon', 'Kristi', 'Krona One', 'Krub', 'Kumar One', 'Kumar One Outline', 'Kurale', 'La Belle Aurore', 'Laila', 'Lakki Reddy', 'Lalezar', 'Lancelot', 'Lateef', 'Lato', 'League Script', 'Leckerli One', 'Ledger', 'Lekton', 'Lemon', 'Lemonada', 'Libre Barcode 128', 'Libre Barcode 128 Text', 'Libre Barcode 39', 'Libre Barcode 39 Extended', 'Libre Barcode 39 Extended Text', 'Libre Barcode 39 Text', 'Libre Baskerville', 'Libre Franklin', 'Life Savers', 'Lilita One', 'Lily Script One', 'Limelight', 'Linden Hill', 'Lobster', 'Lobster Two', 'Londrina Outline', 'Londrina Shadow', 'Londrina Sketch', 'Londrina Solid', 'Lora', 'Love Ya Like A Sister', 'Loved by the King', 'Lovers Quarrel', 'Luckiest Guy', 'Lusitana', 'Lustria', 'M PLUS 1p', 'M PLUS Rounded 1c', 'Macondo', 'Macondo Swash Caps', 'Mada', 'Magra', 'Maiden Orange', 'Maitree', 'Mako', 'Mali', 'Mallanna', 'Mandali', 'Manuale', 'Marcellus', 'Marcellus SC', 'Marck Script', 'Margarine', 'Markazi Text', 'Marko One', 'Marmelad', 'Martel', 'Martel Sans', 'Marvel', 'Mate', 'Mate SC', 'Maven Pro', 'McLaren', 'Meddon', 'MedievalSharp', 'Medula One', 'Meera Inimai', 'Megrim', 'Meie Script', 'Merienda', 'Merienda One', 'Merriweather', 'Merriweather Sans', 'Metal', 'Metal Mania', 'Metamorphous', 'Metrophobic', 'Michroma', 'Milonga', 'Miltonian', 'Miltonian Tattoo', 'Mina', 'Miniver', 'Miriam Libre', 'Mirza', 'Miss Fajardose', 'Mitr', 'Modak', 'Modern Antiqua', 'Mogra', 'Molengo', 'Molle', 'Monda', 'Monofett', 'Monoton', 'Monsieur La Doulaise', 'Montaga', 'Montez', 'Montserrat', 'Montserrat Alternates', 'Montserrat Subrayada', 'Moul', 'Moulpali', 'Mountains of Christmas', 'Mouse Memoirs', 'Mr Bedfort', 'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Mrs Sheppards', 'Mukta', 'Mukta Mahee', 'Mukta Malar', 'Mukta Vaani', 'Muli', 'Mystery Quest', 'NTR', 'Nanum Brush Script', 'Nanum Gothic', 'Nanum Gothic Coding', 'Nanum Myeongjo', 'Nanum Pen Script', 'Neucha', 'Neuton', 'New Rocker', 'News Cycle', 'Niconne', 'Niramit', 'Nixie One', 'Nobile', 'Nokora', 'Norican', 'Nosifer', 'Notable', 'Nothing You Could Do', 'Noticia Text', 'Noto Sans', 'Noto Sans JP', 'Noto Sans KR', 'Noto Serif', 'Noto Serif JP', 'Noto Serif KR', 'Nova Cut', 'Nova Flat', 'Nova Mono', 'Nova Oval', 'Nova Round', 'Nova Script', 'Nova Slim', 'Nova Square', 'Numans', 'Nunito', 'Nunito Sans', 'Odor Mean Chey', 'Offside', 'Old Standard TT', 'Oldenburg', 'Oleo Script', 'Oleo Script Swash Caps', 'Open Sans', 'Open Sans Condensed', 'Oranienbaum', 'Orbitron', 'Oregano', 'Orienta', 'Original Surfer', 'Oswald', 'Over the Rainbow', 'Overlock', 'Overlock SC', 'Overpass', 'Overpass Mono', 'Ovo', 'Oxygen', 'Oxygen Mono', 'PT Mono', 'PT Sans', 'PT Sans Caption', 'PT Sans Narrow', 'PT Serif', 'PT Serif Caption', 'Pacifico', 'Padauk', 'Palanquin', 'Palanquin Dark', 'Pangolin', 'Paprika', 'Parisienne', 'Passero One', 'Passion One', 'Pathway Gothic One', 'Patrick Hand', 'Patrick Hand SC', 'Pattaya', 'Patua One', 'Pavanam', 'Paytone One', 'Peddana', 'Peralta', 'Permanent Marker', 'Petit Formal Script', 'Petrona', 'Philosopher', 'Piedra', 'Pinyon Script', 'Pirata One', 'Plaster', 'Play', 'Playball', 'Playfair Display', 'Playfair Display SC', 'Podkova', 'Poiret One', 'Poller One', 'Poly', 'Pompiere', 'Pontano Sans', 'Poor Story', 'Poppins', 'Port Lligat Sans', 'Port Lligat Slab', 'Pragati Narrow', 'Prata', 'Preahvihear', 'Press Start 2P', 'Pridi', 'Princess Sofia', 'Prociono', 'Prompt', 'Prosto One', 'Proza Libre', 'Puritan', 'Purple Purse', 'Quando', 'Quantico', 'Quattrocento', 'Quattrocento Sans', 'Questrial', 'Quicksand', 'Quintessential', 'Qwigley', 'Racing Sans One', 'Radley', 'Rajdhani', 'Rakkas', 'Raleway', 'Raleway Dots', 'Ramabhadra', 'Ramaraja', 'Rambla', 'Rammetto One', 'Ranchers', 'Rancho', 'Ranga', 'Rasa', 'Rationale', 'Ravi Prakash', 'Redressed', 'Reem Kufi', 'Reenie Beanie', 'Revalia', 'Rhodium Libre', 'Ribeye', 'Ribeye Marrow', 'Righteous', 'Risque', 'Roboto', 'Roboto Condensed', 'Roboto Mono', 'Roboto Slab', 'Rochester', 'Rock Salt', 'Rokkitt', 'Romanesco', 'Ropa Sans', 'Rosario', 'Rosarivo', 'Rouge Script', 'Rozha One', 'Rubik', 'Rubik Mono One', 'Ruda', 'Rufina', 'Ruge Boogie', 'Ruluko', 'Rum Raisin', 'Ruslan Display', 'Russo One', 'Ruthie', 'Rye', 'Sacramento', 'Sahitya', 'Sail', 'Saira', 'Saira Condensed', 'Saira Extra Condensed', 'Saira Semi Condensed', 'Salsa', 'Sanchez', 'Sancreek', 'Sansita', 'Sarala', 'Sarina', 'Sarpanch', 'Satisfy', 'Sawarabi Gothic', 'Sawarabi Mincho', 'Scada', 'Scheherazade', 'Schoolbell', 'Scope One', 'Seaweed Script', 'Secular One', 'Sedgwick Ave', 'Sedgwick Ave Display', 'Sevillana', 'Seymour One', 'Shadows Into Light', 'Shadows Into Light Two', 'Shanti', 'Share', 'Share Tech', 'Share Tech Mono', 'Shojumaru', 'Short Stack', 'Shrikhand', 'Siemreap', 'Sigmar One', 'Signika', 'Signika Negative', 'Simonetta', 'Sintony', 'Sirin Stencil', 'Six Caps', 'Skranji', 'Slabo 13px', 'Slabo 27px', 'Slackey', 'Smokum', 'Smythe', 'Sniglet', 'Snippet', 'Snowburst One', 'Sofadi One', 'Sofia', 'Song Myung', 'Sonsie One', 'Sorts Mill Goudy', 'Source Code Pro', 'Source Sans Pro', 'Source Serif Pro', 'Space Mono', 'Special Elite', 'Spectral', 'Spectral SC', 'Spicy Rice', 'Spinnaker', 'Spirax', 'Squada One', 'Sree Krushnadevaraya', 'Sriracha', 'Srisakdi', 'Stalemate', 'Stalinist One', 'Stardos Stencil', 'Stint Ultra Condensed', 'Stint Ultra Expanded', 'Stoke', 'Strait', 'Stylish', 'Sue Ellen Francisco', 'Suez One', 'Sumana', 'Sunflower', 'Sunshiney', 'Supermercado One', 'Sura', 'Suranna', 'Suravaram', 'Suwannaphum', 'Swanky and Moo Moo', 'Syncopate', 'Tajawal', 'Tangerine', 'Taprom', 'Tauri', 'Taviraj', 'Teko', 'Telex', 'Tenali Ramakrishna', 'Tenor Sans', 'Text Me One', 'The Girl Next Door', 'Tienne', 'Tillana', 'Timmana', 'Tinos', 'Titan One', 'Titillium Web', 'Trade Winds', 'Trirong', 'Trocchi', 'Trochut', 'Trykker', 'Tulpen One', 'Ubuntu', 'Ubuntu Condensed', 'Ubuntu Mono', 'Ultra', 'Uncial Antiqua', 'Underdog', 'Unica One', 'UnifrakturCook', 'UnifrakturMaguntia', 'Unkempt', 'Unlock', 'Unna', 'VT323', 'Vampiro One', 'Varela', 'Varela Round', 'Vast Shadow', 'Vesper Libre', 'Vibur', 'Vidaloka', 'Viga', 'Voces', 'Volkhov', 'Vollkorn', 'Vollkorn SC', 'Voltaire', 'Waiting for the Sunrise', 'Wallpoet', 'Walter Turncoat', 'Warnes', 'Wellfleet', 'Wendy One', 'Wire One', 'Work Sans', 'Yanone Kaffeesatz', 'Yantramanav', 'Yatra One', 'Yellowtail', 'Yeon Sung', 'Yeseva One', 'Yesteryear', 'Yrsa', 'Zeyada', 'Zilla Slab', 'Zilla Slab Highlight');
            return $google_fonts;
        }

        function arm_load_google_fonts($type = 'wp') {
            global $wp, $wpdb, $ARMember;
            /* Google Font Lists */
            $g_fonts = $this->arm_google_fonts_list();
            $diff = count($g_fonts) / 2;
            $google_fonts_one = $g_fonts;
            $google_fonts_two = $g_fonts;
            array_splice($google_fonts_one, $diff);
            array_splice($google_fonts_two, 0, -$diff);
            $google_fonts_string_one = implode('|', $google_fonts_one);
            $google_fonts_string_two = implode('|', $google_fonts_two);
            $google_font_url_one = $google_font_url_two = "";
            if (is_ssl()) {
                $google_font_url_one = "https://fonts.googleapis.com/css?family=" . $google_fonts_string_one;
                $google_font_url_two = "https://fonts.googleapis.com/css?family=" . $google_fonts_string_two;
            } else {
                $google_font_url_one = "http://fonts.googleapis.com/css?family=" . $google_fonts_string_one;
                $google_font_url_two = "http://fonts.googleapis.com/css?family=" . $google_fonts_string_two;
            }
            if ($type == 'editor') {
                add_editor_style($google_font_url_one);
                add_editor_style($google_font_url_two);
            } else {
                wp_register_style('arm_googlefonts1', $google_font_url_one, array(), MEMBERSHIP_VERSION);
                wp_register_style('arm_googlefonts2', $google_font_url_two, array(), MEMBERSHIP_VERSION);
                wp_enqueue_style('arm_googlefonts1');
                wp_enqueue_style('arm_googlefonts2');
            }
        }

        function arm_get_google_fonts_url($fontString = array()) {
            global $wp, $wpdb, $arm_slugs, $ARMember;
            $google_font_url = '';
            if (!empty($fontString)) {
                $googleFonts = array();
                $fontString = $ARMember->arm_array_unique($fontString);
                $g_fonts = $this->arm_google_fonts_list();
                foreach ($g_fonts as $font) {
                    if (in_array($font, $fontString)) {
                        $googleFonts[] = $font;
                    }
                }
                if (!empty($googleFonts)) {
                    $google_fonts_string = implode('|', $googleFonts);
                    if (is_ssl()) {
                        $google_font_url = "https://fonts.googleapis.com/css?family=" . $google_fonts_string;
                    } else {
                        $google_font_url = "http://fonts.googleapis.com/css?family=" . $google_fonts_string;
                    }
                }
            }
            return $google_font_url;
        }

        function arm_get_countries() {
            return apply_filters(
                'arm_countries',
                array(
                    1    => __("Afghanistan", "ARMember"),
                    2    => __("Albania", "ARMember"),
                    3    => __("Algeria", "ARMember"),
                    4    => __("American Samoa", "ARMember"),
                    5    => __("Andorra", "ARMember"),
                    6    => __("Angola", "ARMember"),
                    7    => __("Anguilla", "ARMember"),
                    8    => __("Antarctica", "ARMember"),
                    9    => __("Antigua and Barbuda", "ARMember"),
                    10   => __("Argentina", "ARMember"),
                    11   => __("Armenia", "ARMember"),
                    12   => __("Aruba", "ARMember"),
                    13   => __("Australia", "ARMember"),
                    14   => __("Austria", "ARMember"),
                    15   => __("Azerbaijan", "ARMember"),
                    16   => __("Bahamas", "ARMember"),
                    17   => __("Bahrain", "ARMember"),
                    18   => __("Bangladesh", "ARMember"),
                    19   => __("Barbados", "ARMember"),
                    20   => __("Belarus", "ARMember"),
                    21   => __("Belgium", "ARMember"),
                    22   => __("Belize", "ARMember"),
                    23   => __("Benin", "ARMember"),
                    24   => __("Bermuda", "ARMember"),
                    25   => __("Bhutan", "ARMember"),
                    26   => __("Bolivia", "ARMember"),
                    27   => __("Bosnia and Herzegovina", "ARMember"),
                    28   => __("Botswana", "ARMember"),
                    29   => __("Brazil", "ARMember"),
                    30   => __("Brunei", "ARMember"),
                    31   => __("Bulgaria", "ARMember"),
                    32   => __("Burkina Faso", "ARMember"),
                    33   => __("Burundi", "ARMember"),
                    34   => __("Cambodia", "ARMember"),
                    35   => __("Cameroon", "ARMember"),
                    36   => __("Canada", "ARMember"),
                    37   => __("Cape Verde", "ARMember"),
                    38   => __("Cayman Islands", "ARMember"),
                    39   => __("Central African Republic", "ARMember"),
                    40   => __("Chad", "ARMember"),
                    41   => __("Chile", "ARMember"),
                    42   => __("China", "ARMember"),
                    43   => __("Colombia", "ARMember"),
                    44   => __("Comoros", "ARMember"),
                    45   => __("Congo", "ARMember"),
                    46   => __("Costa Rica", "ARMember"),
                    47   => __("Croatia", "ARMember"),
                    48   => __("Cuba", "ARMember"),
                    49   => __("Cyprus", "ARMember"),
                    50   => __("Czech Republic", "ARMember"),
                    51   => __("Denmark", "ARMember"),
                    52   => __("Djibouti", "ARMember"),
                    53   => __("Dominica", "ARMember"),
                    54   => __("Dominican Republic", "ARMember"),
                    55   => __("East Timor", "ARMember"),
                    56   => __("Ecuador", "ARMember"),
                    57   => __("Egypt", "ARMember"),
                    58   => __("El Salvador", "ARMember"),
                    59   => __("Equatorial Guinea", "ARMember"),
                    60   => __("Eritrea", "ARMember"),
                    61   => __("Estonia", "ARMember"),
                    62   => __("Ethiopia", "ARMember"),
                    63   => __("Fiji", "ARMember"),
                    64   => __("Finland", "ARMember"),
                    65   => __("France", "ARMember"),
                    66   => __("French Guiana", "ARMember"),
                    67   => __("French Polynesia", "ARMember"),
                    68   => __("Gabon", "ARMember"),
                    69   => __("Gambia", "ARMember"),
                    70   => __("Georgia", "ARMember"),
                    71   => __("Germany", "ARMember"),
                    72   => __("Ghana", "ARMember"),
                    73   => __("Gibraltar", "ARMember"),
                    74   => __("Greece", "ARMember"),
                    75   => __("Greenland", "ARMember"),
                    76   => __("Grenada", "ARMember"),
                    77   => __("Guam", "ARMember"),
                    78   => __("Guatemala", "ARMember"),
                    79   => __("Guinea", "ARMember"),
                    80   => __("Guinea-Bissau", "ARMember"),
                    81   => __("Guyana", "ARMember"),
                    82   => __("Haiti", "ARMember"),
                    83   => __("Honduras", "ARMember"),
                    84   => __("Hong Kong", "ARMember"),
                    85   => __("Hungary", "ARMember"),
                    86   => __("Iceland", "ARMember"),
                    87   => __("India", "ARMember"),
                    88   => __("Indonesia", "ARMember"),
                    89   => __("Iran", "ARMember"),
                    90   => __("Iraq", "ARMember"),
                    91   => __("Ireland", "ARMember"),
                    92   => __("Israel", "ARMember"),
                    93   => __("Italy", "ARMember"),
                    94   => __("Jamaica", "ARMember"),
                    95   => __("Japan", "ARMember"),
                    96   => __("Jordan", "ARMember"),
                    97   => __("Kazakhstan", "ARMember"),
                    98   => __("Kenya", "ARMember"),
                    99   => __("Kiribati", "ARMember"),
                    100  => __("North Korea", "ARMember"),
                    101  => __("South Korea", "ARMember"),
                    102  => __("Kuwait", "ARMember"),
                    103  => __("Kyrgyzstan", "ARMember"),
                    104  => __("Laos", "ARMember"),
                    105  => __("Latvia", "ARMember"),
                    106  => __("Lebanon", "ARMember"),
                    107  => __("Lesotho", "ARMember"),
                    108  => __("Liberia", "ARMember"),
                    109  => __("Libya", "ARMember"),
                    110  => __("Liechtenstein", "ARMember"),
                    111  => __("Lithuania", "ARMember"),
                    112  => __("Luxembourg", "ARMember"),
                    113  => __("Macedonia", "ARMember"),
                    114  => __("Madagascar", "ARMember"),
                    115  => __("Malawi", "ARMember"),
                    116  => __("Malaysia", "ARMember"),
                    117  => __("Maldives", "ARMember"),
                    118  => __("Mali", "ARMember"),
                    119  => __("Malta", "ARMember"),
                    120  => __("Marshall Islands", "ARMember"),
                    121  => __("Mauritania", "ARMember"),
                    122  => __("Mauritius", "ARMember"),
                    123  => __("Mexico", "ARMember"),
                    124  => __("Micronesia", "ARMember"),
                    125  => __("Moldova", "ARMember"),
                    126  => __("Monaco", "ARMember"),
                    127  => __("Mongolia", "ARMember"),
                    128  => __("Montenegro", "ARMember"),
                    129  => __("Montserrat", "ARMember"),
                    130  => __("Morocco", "ARMember"),
                    131  => __("Mozambique", "ARMember"),
                    132  => __("Myanmar", "ARMember"),
                    133  => __("Namibia", "ARMember"),
                    134  => __("Nauru", "ARMember"),
                    135  => __("Nepal", "ARMember"),
                    136  => __("Netherlands", "ARMember"),
                    137  => __("New Zealand", "ARMember"),
                    138  => __("Nicaragua", "ARMember"),
                    139  => __("Niger", "ARMember"),
                    140  => __("Nigeria", "ARMember"),
                    141  => __("Norway", "ARMember"),
                    142  => __("Northern Mariana Islands", "ARMember"),
                    143  => __("Oman", "ARMember"),
                    144  => __("Pakistan", "ARMember"),
                    145  => __("Palau", "ARMember"),
                    146  => __("Palestine", "ARMember"),
                    147  => __("Panama", "ARMember"),
                    148  => __("Papua New Guinea", "ARMember"),
                    149  => __("Paraguay", "ARMember"),
                    150  => __("Peru", "ARMember"),
                    151  => __("Philippines", "ARMember"),
                    152  => __("Poland", "ARMember"),
                    153  => __("Portugal", "ARMember"),
                    154  => __("Puerto Rico", "ARMember"),
                    155  => __("Qatar", "ARMember"),
                    211  => __("Reunion Island", "ARMember"),
                    156  => __("Romania", "ARMember"),
                    157  => __("Russia", "ARMember"),
                    158  => __("Rwanda", "ARMember"),
                    159  => __("Saint Kitts and Nevis", "ARMember"),
                    160  => __("Saint Lucia", "ARMember"),
                    161  => __("Saint Vincent and the Grenadines", "ARMember"),
                    162  => __("Samoa", "ARMember"),
                    163  => __("San Marino", "ARMember"),
                    164  => __("Sao Tome and Principe", "ARMember"),
                    165  => __("Saudi Arabia", "ARMember"),
                    166  => __("Senegal", "ARMember"),
                    167  => __("Serbia and Montenegro", "ARMember"),
                    168  => __("Seychelles", "ARMember"),
                    169  => __("Sierra Leone", "ARMember"),
                    170  => __("Singapore", "ARMember"),
                    171  => __("Slovakia", "ARMember"),
                    172  => __("Slovenia", "ARMember"),
                    173  => __("Solomon Islands", "ARMember"),
                    174  => __("Somalia", "ARMember"),
                    175  => __("South Africa", "ARMember"),
                    176  => __("Spain", "ARMember"),
                    177  => __("Sri Lanka", "ARMember"),
                    178  => __("Sudan", "ARMember"),
                    179  => __("Suriname", "ARMember"),
                    180  => __("Swaziland", "ARMember"),
                    181  => __("Sweden", "ARMember"),
                    182  => __("Switzerland", "ARMember"),
                    183  => __("Syria", "ARMember"),
                    184  => __("Taiwan", "ARMember"),
                    185  => __("Tajikistan", "ARMember"),
                    186  => __("Tanzania", "ARMember"),
                    187  => __("Thailand", "ARMember"),
                    188  => __("Togo", "ARMember"),
                    189  => __("Tonga", "ARMember"),
                    190  => __("Trinidad and Tobago", "ARMember"),
                    191  => __("Tunisia", "ARMember"),
                    192  => __("Turkey", "ARMember"),
                    193  => __("Turkmenistan", "ARMember"),
                    194  => __("Tuvalu", "ARMember"),
                    195  => __("Uganda", "ARMember"),
                    196  => __("Ukraine", "ARMember"),
                    197  => __("United Arab Emirates", "ARMember"),
                    198  => __("United Kingdom", "ARMember"),
                    199  => __("United States", "ARMember"),
                    200  => __("Uruguay", "ARMember"),
                    201  => __("Uzbekistan", "ARMember"),
                    202  => __("Vanuatu", "ARMember"),
                    203  => __("Vatican City", "ARMember"),
                    204  => __("Venezuela", "ARMember"),
                    205  => __("Vietnam", "ARMember"),
                    206  => __("Virgin Islands, British", "ARMember"),
                    207  => __("Virgin Islands, U.S.", "ARMember"),
                    208  => __("Yemen", "ARMember"),
                    209  => __("Zambia", "ARMember"),
                    210  => __("Zimbabwe", "ARMember"),
                )
            );
        }

        function arm_check_form_include_js_css($form, $atts) {
            global $ARMember;
            $ARMember->set_front_css(true);
            $ARMember->set_front_js(true);
        }

        function arm_get_spf_in_tinymce() {
            global $wpdb, $ARMember;
            $form_name = isset($_REQUEST['form_name']) ? $_REQUEST['form_name'] : '';
            $is_vc = isset($_REQUEST['is_vc']) ? $_REQUEST['is_vc'] : false;
            if ($form_name === '') {
                echo json_encode(array('error' => true));
                die();
            } else {
                $content = "";
                if ($is_vc != false) {
                    $content .= "<input type='hidden' name='social_fields' class='wpb_vc_param_value' id='social_fields_hidden' value='' />";
                }
                $all_spfields = $this->arm_social_profile_field_types();
                $form_id = $form_name;
                $form_social_fields = $wpdb->get_row($wpdb->prepare("SELECT arm_form_field_option FROM `{$ARMember->tbl_arm_form_field}`  WHERE arm_form_field_form_id = %d AND arm_form_field_slug = %s ", $form_id, 'social_fields'));
                $active_spf = array();
                if (!empty($form_social_fields)) {
                    $field_options = maybe_unserialize($form_social_fields->arm_form_field_option);
                    $active_spf = $field_options['options'];
                    $content .= "<div class='arm_social_field_popup_wrapper'>";
                    foreach ($all_spfields as $SPFKey => $SPFLabel) {
                        $checked = "";
                        if (is_array($active_spf) && in_array($SPFKey, $active_spf)) {
                            $checked = 'checked="checked" disabled="disabled"';
                        }
                        if ($is_vc != true) {
                            $content .= "<div class='arm_social_profile_field_item'>";
                            $content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='{$SPFKey}' name='arm_social_fields[]' id='arm_spf_{$SPFKey}_status' {$checked} />";
                            $content .= "<label for='arm_spf_{$SPFKey}_status'>{$SPFLabel}</label>";
                            $content .= "</div>";
                        } else {
                            $content .= "<label class='arm_social_profile_field_item'>";
                            $content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt arm_spf_active_checkbox_input' value='{$SPFKey}' onchange='arm_select_social_fields()' name='arm_social_fields[]' id='arm_spf_{$SPFKey}_status' {$checked} />";
                            $content .= "<span>{$SPFLabel}</span>";
                            $content .= "</label>";
                        }
                    }
                    $content .= "</div>";
                } else {
                    $content .= "<div class='arm_social_field_popup_wrapper'>";
                    foreach ($all_spfields as $SPFKey => $SPFLabel) {
                        $checked = "";
                        if (is_array($active_spf) && in_array($SPFKey, $active_spf)) {
                            $checked = 'checked="checked" disabled="disabled"';
                        }
                        if ($is_vc != true) {
                            $content .= "<div class='arm_social_profile_field_item'>";
                            $content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='{$SPFKey}' name='arm_social_fields[]' id='arm_spf_{$SPFKey}_status' {$checked} />";
                            $content .= "<label for='arm_spf_{$SPFKey}_status'>{$SPFLabel}</label>";
                            $content .= "</div>";
                        } else {
                            $content .= "<label class='arm_social_profile_field_item'>";
                            $content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt arm_spf_active_checkbox_input' value='{$SPFKey}' onchange='arm_select_social_fields()' name='arm_social_fields[]' id='arm_spf_{$SPFKey}_status' {$checked} />";
                            $content .= "<span>{$SPFLabel}</span>";
                            $content .= "</label>";
                        }
                    }
                    $content .= "</div>";
                }
            }
            echo json_encode(array('error' => false, 'content' => stripslashes_deep($content)));
            die();
        }

        function arm_default_button_gradient_color() {
            $arm_button_gradient_color = array();
            $arm_button_gradient_color['blue'] = array(
                'button_back_color' => '#005C97',
                'button_back_color_gradient' => '#363795',
                'button_hover_color' => '#005C97',
                'button_hover_color_gradient' => '#363795'
            );
            $arm_button_gradient_color['bright_cyan'] = array(
                'button_back_color' => '#00d2ff',
                'button_back_color_gradient' => '#3afbd5',
                'button_hover_color' => '#00d2ff',
                'button_hover_color_gradient' => '#3afbd5'
            );
            $arm_button_gradient_color['green'] = array(
                'button_back_color' => '#3ca55c',
                'button_back_color_gradient' => '#b5ac49',
                'button_hover_color' => '#3ca55c',
                'button_hover_color_gradient' => '#b5ac49'
            );
            $arm_button_gradient_color['red'] = array(
                'button_back_color' => '#dd2476',
                'button_back_color_gradient' => '#ff512f',
                'button_hover_color' => '#dd2476',
                'button_hover_color_gradient' => '#ff512f'
            );
            $arm_button_gradient_color['purple'] = array(
                'button_back_color' => '#7474BF',
                'button_back_color_gradient' => '#348AC7',
                'button_hover_color' => '#7474BF',
                'button_hover_color_gradient' => '#348AC7'
            );
            $arm_button_gradient_color['orange'] = array(
                'button_back_color' => '#c21500',
                'button_back_color_gradient' => '#ffc500',
                'button_hover_color' => '#c21500',
                'button_hover_color_gradient' => '#ffc500'
            );
            $arm_button_gradient_color['yellow'] = array(
                'button_back_color' => '#F09819',
                'button_back_color_gradient' => '#EDDE5D',
                'button_hover_color' => '#F09819',
                'button_hover_color_gradient' => '#EDDE5D'
            );
            $arm_button_gradient_color['pink'] = array(
                'button_back_color' => '#f857a6',
                'button_back_color_gradient' => '#ff5858',
                'button_hover_color' => '#f857a6',
                'button_hover_color_gradient' => '#ff5858'
            );
            $arm_button_gradient_color['strong_cyan'] = array(
                'button_back_color' => '#43cea2',
                'button_back_color_gradient' => '#185a9d',
                'button_hover_color' => '#43cea2',
                'button_hover_color_gradient' => '#185a9d'
            );
            $arm_button_gradient_color['gray'] = array(
                'button_back_color' => '#283048',
                'button_back_color_gradient' => '#859398',
                'button_hover_color' => '#283048',
                'button_hover_color_gradient' => '#859398'
            );
            $arm_button_gradient_color['dark_purple'] = array(
                'button_back_color' => '#1D2B64',
                'button_back_color_gradient' => '#F8CDDA',
                'button_hover_color' => '#1D2B64',
                'button_hover_color_gradient' => '#F8CDDA'
            );
            $arm_button_gradient_color['black'] = array(
                'button_back_color' => '#232526',
                'button_back_color_gradient' => '#646668',
                'button_hover_color' => '#232526',
                'button_hover_color_gradient' => '#646668'
            );

            return apply_filters('arm_button_gradient_color', $arm_button_gradient_color);
        }

        function arm_auto_lock_shared_account() {

            if (is_user_logged_in() && !is_admin()) {
                $user_id = get_current_user_id();

                if (user_can($user_id, 'administrator')) {
                    return;
                }
                global $arm_global_settings, $ARMember, $wpdb;


                $arm_all_general_settings = $arm_global_settings->global_settings;

                $autolock_shared_account = (isset($arm_all_general_settings['autolock_shared_account'])) ? $arm_all_general_settings['autolock_shared_account'] : 0;

                if ($autolock_shared_account == 1) {

                    if (isset($_COOKIE['arm_autolock_cookie_' . $user_id]) && !empty($_COOKIE['arm_autolock_cookie_' . $user_id])) {

                        $arm_autolock_cookie = $_COOKIE['arm_autolock_cookie_' . $user_id];
                        $stored_cookie = $arm_autolock_cookie;
                        $inserted_id = explode('||', $stored_cookie);
                        $arm_session_id = $inserted_id[0];
                        $arm_history_id = $inserted_id[1];
                        //$logged_out_time = date('Y-m-d H:i:s');
                        $logged_out_time = current_time('mysql');
                        $login_history_table = $ARMember->tbl_arm_login_history;

                        $update_query = $wpdb->prepare("UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND `arm_history_session` != %s AND `arm_user_id` = %d AND `arm_user_current_status` != %d", $logged_out_time, 0, $arm_history_id, $arm_session_id, $user_id, 0);
                        $wpdb->query($update_query);
                        unset($_COOKIE['arm_autolock_cookie_' . $user_id]);
                        setcookie('arm_autolock_cookie_' . $user_id, '', time() - 3600, '/');
                    }



                    wp_destroy_other_sessions();
                }
            }
        }

        function arm_add_login_history_for_set_logged_in_cookie($auth_cookie, $expire, $expiration, $user_id, $scheme) {

            global $wpdb, $ARMember, $arm_global_settings, $arm_is_change_password_form_for_login, $arm_login_from_registration, $arm_is_update_password_form_edit_profile_login, $browser_session_id;


            $arm_all_block_settings = $arm_global_settings->block_settings;
            $tbl_login_history = $ARMember->tbl_arm_login_history;

            if (isset($arm_all_block_settings['track_login_history']) && $arm_all_block_settings['track_login_history'] != 1){     
                return;
            }

            if (empty($user_id) || user_can($user_id, 'administrator')) {
                return;
            }

            if ($arm_is_change_password_form_for_login == 1) {
                $arm_is_change_password_form_for_login = 0;
                return;
            }

            if ($arm_is_update_password_form_edit_profile_login == 1) {
                $arm_is_update_password_form_edit_profile_login = 0;
                return;
            }

            $logged_in_ip = $ARMember->arm_get_ip_address();
            $country = $ARMember->arm_get_country_from_ip($logged_in_ip);

            $current_time = current_time('timestamp');
            $logged_in_time = date('Y-m-d H:i:s', $current_time);
            $browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);
            $browser_detail = $browser_info['name'] . ' (' . $browser_info['version'] . ')';
            $user_current_status = 1;

            if(empty($browser_session_id))
            {
                $ARMember->arm_session_start();
                $browser_session_id = session_id();
            }

            $select_query = "SELECT count(*) FROM `{$tbl_login_history}` WHERE `arm_history_session` = '" . $browser_session_id . "' AND `arm_user_current_status` = 1";
            $select_result = $wpdb->get_var($select_query);

            if ($select_result > 0) {
                return;
            }

            //$last_updated_time = date( 'Y-m-d H:i:s', ($current_time-300) );

            /*$update_query = $wpdb->prepare("UPDATE `{$tbl_login_history}` SET `arm_user_current_status` = %d  WHERE `arm_user_current_status` != %d AND `arm_user_id` = %d AND (arm_logout_date<%s OR arm_logout_date='0000-00-00 00:00:00') ", 0, 0, $user_id, $last_updated_time);
            $update_result = $wpdb->query($update_query);*/

            $update_query = $wpdb->prepare("UPDATE `{$tbl_login_history}` SET `arm_user_current_status` = %d  WHERE `arm_user_current_status` != %d AND `arm_user_id` = %d AND `arm_history_browser` = %s AND `arm_logged_in_ip` = %s", 0, 0, $user_id, $browser_detail, $logged_in_ip);
            $update_result = $wpdb->query($update_query);
            $insert_query = $wpdb->prepare("INSERT INTO `{$tbl_login_history}` (`arm_user_id`,`arm_logged_in_ip`,`arm_logged_in_date`,`arm_history_browser`,`arm_history_session`,`arm_login_country`,`arm_user_current_status`,`arm_logout_date`) VALUES (%d,%s,%s,%s,%s,%s,%d,%s)", $user_id, $logged_in_ip, $logged_in_time, $browser_detail, $browser_session_id, $country, 1, $logged_in_time);
            $insert_result = $wpdb->query($insert_query);

            if ($arm_login_from_registration == 1) {
                $arm_login_from_registration = 0;
                return;
            }

            $cookie_name = 'arm_cookie_' . $user_id;
            $autolock_cookie_name = 'arm_autolock_cookie_' . $user_id;
            $cookie_value = $browser_session_id . '||' . $wpdb->insert_id;
            $cookie_exp_time = time() + 60 * 60 * 24 * 30;
            setcookie($cookie_name, $cookie_value, $cookie_exp_time, '/');
            setcookie($autolock_cookie_name, $cookie_value, $cookie_exp_time, '/');
        }

        function arm_update_login_history() {

            global $wpdb, $ARMember, $arm_global_settings, $arm_is_change_password_form_for_logout, $arm_is_update_password_form_edit_profile_logout;

            $arm_all_block_settings = $arm_global_settings->block_settings;
            $login_history_table = $ARMember->tbl_arm_login_history;
            $user_id = get_current_user_id();
            if (isset($arm_all_block_settings['track_login_history']) && $arm_all_block_settings['track_login_history'] != 1) {
                    return;
            }
            
            if (user_can($user_id, 'administrator')) {
                return;
            }
            
            /* Check for registered COOKIE When current user is logged in */
            if (isset($_COOKIE['arm_cookie_' . $user_id]) and ! empty($_COOKIE['arm_cookie_' . $user_id])) {
                $stored_cookie = $_COOKIE['arm_cookie_' . $user_id];
                $inserted_id = explode('||', $stored_cookie);
                $session_id = $inserted_id[0];
                $wp_insert_id = $inserted_id[1];
                //$logged_out_time = date('Y-m-d H:i:s');
                $logged_out_time = current_time('mysql');


                if ($arm_is_change_password_form_for_logout == 1) {

                    $arm_is_change_password_form_for_logout = 0;
                    $update_query = $wpdb->prepare("UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND  `arm_user_id` = %d AND `arm_user_current_status` = %d", $logged_out_time, 0, $wp_insert_id, $user_id, 1);
                    $update_result = $wpdb->query($update_query);

                    return;
                }


                if ($arm_is_update_password_form_edit_profile_logout == 1) {
                    $arm_is_update_password_form_edit_profile_logout = 0;
                    $update_query = $wpdb->prepare("UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND  `arm_user_id` = %d AND `arm_user_current_status` = %d", $logged_out_time, 0, $wp_insert_id, $user_id, 1);
                    $update_result = $wpdb->query($update_query);
                    return;
                }

                $get_login_time = $wpdb->get_row($wpdb->prepare("SELECT `arm_logged_in_date` FROM `{$login_history_table}` WHERE `arm_history_id` = %d AND `arm_user_id` = %d AND `arm_history_session` = %s ", $wp_insert_id, $user_id, $session_id));
                if(!empty($get_login_time)){
                $arm_login_time = $get_login_time->arm_logged_in_date;
                $login_duration = strtotime($logged_out_time) - strtotime($arm_login_time);
                $arm_login_duration = date('H:i:s', $login_duration);
                $update_query = $wpdb->prepare("UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_login_duration` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` = %d AND `arm_history_session` = %s AND `arm_user_id` = %d", $logged_out_time, $arm_login_duration, 0, $wp_insert_id, $session_id, $user_id);
                $wpdb->query($update_query);
                }
                unset($_COOKIE['arm_cookie_' . $user_id]);
                update_user_meta($user_id, 'arm_autolock_cookie', '');

            }
        }

        function arm_get_login_history_func() {
            global $wpdb, $ARMember;
            $return = array();
            $return['error'] = true;
            if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
                $return['data'] = __('User not found', 'ARMember');
            } else {
                $user_id = $_POST['user_id'];
                $table_name = $ARMember->tbl_arm_login_history;
                $get_login_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `arm_user_id` = %d ORDER BY `arm_history_id` ASC", $user_id));
                $return['error'] = false;
                $return['data'] = json_encode($get_login_history);
            }
            echo json_encode($return);
            die();
        }

        function arm_reinit_session_filter_var() {
            global $ARMember;
            $ARMember->arm_session_start();
            $form_key = sanitize_text_field($_POST['form_key']);
            $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
            $random_dots = 0;
            $random_lines = 20;

            $session_var = '';
            $i = 0;
            while ($i < 8) {
                $session_var .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
                $i++;
            }
            $_SESSION['ARM_FILTER_INPUT'][$form_key] = $session_var;
            echo json_encode(array('new_var' => $session_var));
            die();
        }

        function arm_get_avatar_opt() {
            $avatarOptions = array(
                'id' => 'avatar',
                'label' => __('Avatar', 'ARMember'),
                'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                'type' => 'avatar',
                'value' => '',
                'allow_ext' => '',
                'file_size_limit' => '2',
                'meta_key' => 'avatar',
                'required' => 0,
                'blank_message' => __('Please select avatar.', 'ARMember'),
                'invalid_message' => __('Invalid image selected.', 'ARMember'),
            );
            $avatarOptions = apply_filters('arm_change_field_options', $avatarOptions);
            return $avatarOptions;
        }

        function arm_get_profile_cover_opt() {
            $profileCoverOptions = array(
                'id' => 'profile_cover',
                'label' => __('Profile Cover', 'ARMember'),
                'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                'type' => 'avatar',
                'value' => '',
                'allow_ext' => '',
                'file_size_limit' => '10',
                'meta_key' => 'profile_cover',
                'required' => 0,
                'blank_message' => __('Please select profile cover.', 'ARMember'),
                'invalid_message' => __('Invalid image selected.', 'ARMember'),
            );
            $profileCoverOptions = apply_filters('arm_change_field_options', $profileCoverOptions);
            return $profileCoverOptions;
        }

        function arm_get_all_form_fields() {
            global $arm_member_forms;
            $arm_form_fields = array();
            $arm_form_fields = $arm_member_forms->arm_get_db_form_fields(true);
            $arm_form_fields['avatar'] = $this->arm_get_avatar_opt();
            $arm_form_fields['profile_cover'] = $this->arm_get_profile_cover_opt();
            $arm_form_fields['social_fields'] = $arm_member_forms->arm_social_profile_field_types();
            return $arm_form_fields;
        }

        

        function armember_update_user_data ($null, $user_id, $meta_key, $single) {
            if ('country' == $meta_key) {
                $country_meta_value = "";
                global $wpdb;
                $meta_value = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key = 'country'");
                if (empty($meta_value)) {
                    return null;
                }
                $old_meta_value = $meta_value;

                $presetFormFields = get_option('arm_preset_form_fields', '');
                $dbFormFields     = maybe_unserialize($presetFormFields);
                if (!empty($dbFormFields) && isset($dbFormFields['default']['country'])) {
                    $preset_country = $dbFormFields['default']['country']['options'];
                    if (!empty($preset_country)) {
                        foreach ($preset_country as $preset_country_index => $preset_country_value) {
                            $country = explode(":", $preset_country_value);
                            $country_val = isset($country[1]) ? $country[1] : $country[0];
                            if ($old_meta_value == $country_val && !empty($country_val)) {

                                $country_meta_value = $country[0];
                                break;
                            }
                        }
                    }
                }
                if(empty($country_meta_value)) {
                    $countries = $this->arm_get_countries();
                    if (isset($countries[$meta_value]) && !empty($countries[$meta_value])) {
                        $country_meta_value = $countries[$meta_value];
                    }
                }
                return !empty($country_meta_value) ? array($country_meta_value) : array($meta_value);
            }
            return null;
        }


        function arm_update_paid_post_meta($user_ID)
        {
	    global $arm_pay_per_post_feature;
	    if($arm_pay_per_post_feature->isPayPerPostFeature)
	    {
	            $arm_user_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
	            $arm_user_post_ids = get_user_meta($user_ID, 'arm_user_post_ids', true);

	            foreach($arm_user_plan_ids as $arm_plan_key => $arm_plan_value)
	            {
	                if(!empty($arm_user_post_ids[$arm_plan_value]))
	                {
	                    unset($arm_user_post_ids[$arm_plan_value]);
	                }
	            }
	            update_user_meta($user_ID, 'arm_user_post_ids', $arm_user_post_ids);
	    }
        }

        function arm_register_getresponse_user()
        {
            global $wpdb, $ARMember, $armemail, $armfname, $armlname, $form_id, $arm_social_feature, $arm_is_social_signup;
            $armemail_settings_unser = get_option('arm_email_settings');
            $arm_optins_email_settings = maybe_unserialize($armemail_settings_unser);
            $getresponseOpt = (isset($arm_optins_email_settings['arm_email_tools']['getresponse'])) ? $arm_optins_email_settings['arm_email_tools']['getresponse'] : array();
            $api_key = (isset($getresponseOpt['api_key'])) ? $getresponseOpt['api_key'] : '';
            $list_id = (isset($getresponseOpt['list_id'])) ? $getresponseOpt['list_id'] : '';
            $responder_list_id = '';
            if($arm_is_social_signup){
                $social_settings = $arm_social_feature->arm_get_social_settings();
                if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'getresponse') {
                    $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
                    $status = 1;
                    $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id ;
                }
            }
            else
            {
                $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
                $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
                $status = (isset($form_settings['email']['getresponse']['status'])) ? $form_settings['email']['getresponse']['status'] : 0;
                $responder_list_id = (isset($form_settings['email']['getresponse']['list_id'])) ? $form_settings['email']['getresponse']['list_id'] : $list_id;
            }

            if(!empty($responder_list_id) && !empty($api_key)) {

                do_action('arm_general_log_entry', 'getresponse', 'Getresponse selected list ID', 'armember', $responder_list_id);

                if ($status == '1' && !empty($responder_list_id))
                {
                    $subscriberEmail = $armemail;
                    $subscriberName =  $armfname . ' ' . $armlname;

                    $arm_get_response_base_url = "https://api.getresponse.com/v3/";
                    $arm_get_response_header = array(
                        "X-Auth-Token" => "api-key ".$api_key,
                    );
                    $arm_get_response_body_params = array(
                        'timeout' => 15,
                        'headers' => $arm_get_response_header,
                    );

                    //Fetch Campaign ID From Campaign Name
                    $arm_get_response_fetch_campaign_url = $arm_get_response_base_url."campaigns?query[name]=".$responder_list_id;

                    $arm_get_response_campaign_data = wp_remote_get($arm_get_response_fetch_campaign_url, $arm_get_response_body_params);

                    $arm_campaign_id = "";

                    if(!is_wp_error($arm_get_response_campaign_data))
                    {
                        $arm_get_response_campaign_body_data = json_decode($arm_get_response_campaign_data['body']);
                        if(is_array($arm_get_response_campaign_body_data) && !empty($arm_get_response_campaign_body_data[0]))
                        {
                            $arm_campaign_id = $arm_get_response_campaign_body_data[0]->campaignId;
                        }
                    }

                    do_action('arm_general_log_entry', 'getresponse', 'Getresponse Get Campaign ID', 'armember', $arm_campaign_id);


                    //-----------------------------------------------------------

                    //Get all Contacts and check for lowest number and assign largest number
                    $day_of_cycle = '';

                    $arm_get_response_contact_url = $arm_get_response_base_url."contacts";
                    if(!empty($arm_campaign_id))
                    {
                        $arm_get_response_contact_url .= "?query[campaignId]=".$arm_campaign_id;
                    }

                    $arm_get_response_contact_list = wp_remote_get($arm_get_response_contact_url, $arm_get_response_body_params);

                    if(!is_wp_error($arm_get_response_contact_list))
                    {
                        $arm_get_response_contact_body_data = json_decode($arm_get_response_contact_list['body']);
                        if(!empty($arm_get_response_contact_body_data))
                        {
                            $get_smallest_cycle_day = "999999";
                            foreach($arm_get_response_contact_body_data as $arm_get_response_contact_key => $arm_get_repsonse_contact_val)
                            {
                                if(!empty($arm_get_repsonse_contact_val->dayOfCycle))
                                {
                                    $get_smallest_cycle_day = $day_of_cycle = $arm_get_repsonse_contact_val->dayOfCycle;
                                }
                            }
                        }
                    }
                    //-----------------------------------------------------------
                    if(!empty($arm_campaign_id))
                    {
                        //Add Subscriber to Campaign
                        $arm_get_response_add_contact_url = $arm_get_response_base_url."contacts";

                        $arm_getresponse_body_data = array(
                            'name' => $subscriberName,
                            'email' => $subscriberEmail,
                            'campaign' => array(
                                'campaignId' => $arm_campaign_id
                            ),
                            'ipAddress' => $_SERVER['REMOTE_ADDR'],
                        );

                        if (!empty($day_of_cycle)) {
                            $arm_getresponse_body_data['dayOfCycle'] = $day_of_cycle;
                        }

                        $arm_get_response_body_params['body'] = wp_json_encode($arm_getresponse_body_data);
                        $arm_get_response_body_params['headers']['Content-Type'] = "application/json";

                        do_action('arm_general_log_entry', 'getresponse', 'subscriber parameters', 'armember', $arm_get_response_body_params);

                        $arm_get_response_create_contact_response = wp_safe_remote_post($arm_get_response_add_contact_url, $arm_get_response_body_params);

                        do_action('arm_general_log_entry', 'getresponse', 'subscriber add response', 'armember', $arm_get_response_create_contact_response);

                        if(!is_wp_error($arm_get_response_create_contact_response))
                        {
                            $arm_get_response_create_contact_body_response = json_decode($arm_get_response_create_contact_response['body']);
                        }
                    }
                }
            }
        }
        
        function arm_register_sendinblue_user(){

            global $wpdb, $ARMember, $armemail, $armfname,$armlname, $form_id,$arm_sbapi_version,$arm_social_feature, $arm_is_social_signup, $arm_sbapi_version;
            $email_settings_unser = get_option('arm_email_settings');

            $arm_optins_email_settings = maybe_unserialize($email_settings_unser);
            $sendinblueOpt = (isset($arm_optins_email_settings['arm_email_tools']['sendinblue'])) ? $arm_optins_email_settings['arm_email_tools']['sendinblue'] : array();
            $api_key = (isset($sendinblueOpt['api_key'])) ? $sendinblueOpt['api_key'] : '';
            $list_id = (isset($sendinblueOpt['list_id'])) ? $sendinblueOpt['list_id'] : '';
                
            $responder_list_id = '';
            if($arm_is_social_signup){
                $social_settings = $arm_social_feature->arm_get_social_settings();
                if(isset($social_settings['options']['optins_name']) && $social_settings['options']['optins_name'] == 'sendinblue') {
                    $etool_name = isset($social_settings['options']['optins_name']) ? $social_settings['options']['optins_name'] : '';
                    $status = 1;
                    $responder_list_id = isset($social_settings['options'][$etool_name]['list_id']) ? $social_settings['options'][$etool_name]['list_id'] : $list_id ;
                }
            }
            else
            {
                $form_settings = $wpdb->get_var("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`='" . $form_id . "'");
                $form_settings = (!empty($form_settings)) ? maybe_unserialize($form_settings) : array();
                $status = (isset($form_settings['email']['sendinblue']['status'])) ? $form_settings['email']['sendinblue']['status'] : 0;
                $responder_list_id = (isset($form_settings['email']['sendinblue']['list_id'])) ? $form_settings['email']['sendinblue']['list_id'] : $list_id;
            }                
                
            if (!empty($responder_list_id) && !empty($api_key)) {

                if ($status == '1' && !empty($responder_list_id))
                {
                    /*Validation*/
                    if (!$armemail) {
                        return "No email address provided";
                    }
                    if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $armemail)) {
                        return "Email address is invalid";
                    }                                       
                    $url = 'https://api.sendinblue.com/'.$arm_sbapi_version.'/contacts';
                    $args = array(
                        'timeout' => '10000',
                        'method'   => 'POST',
                        'headers'   => array(
                            'Content-Type' => 'application/json',
                            'api-key' => $api_key,  
                        ),
                        'body'     => json_encode(array('email' => $armemail,'attributes' => array('FIRSTNAME'=> $armfname,'LASTNAME' => $armlname) ,'listIds' => array($responder_list_id))),
                    );
                    
                    do_action('arm_general_log_entry', 'sendinblue', 'subscriber parameters', 'armember', $args);
                    $response = wp_remote_post( $url, $args );

                    do_action('arm_general_log_entry', 'sendinblue', 'subscriber add response', 'armember', $response);
                    
                    if(!is_wp_error( $response)){
                       
                        $url_add_to_id = 'https://api.sendinblue.com/'.$arm_sbapi_version.'/contacts/lists/'.$responder_list_id.'/contacts/add';
                        $args = array(
                            'timeout' => '10000',
                            'method'   => 'POST',
                            'headers'   => array(
                                'Content-Type' => 'application/json',
                                'api-key' => $api_key,
                            ),
                                'body' => json_encode(array('emails' => array($armemail))),
                        );
                        do_action('arm_general_log_entry', 'sendinblue', 'subscriber contact add list parameters', 'armember', $args);
                        
                        $response_add_to_list = wp_remote_post( $url_add_to_id, $args );                                      
                        do_action('arm_general_log_entry', 'sendinblue', 'subscriber contact add list response', 'armember', $response_add_to_list);
                    }        
                    
                }
            }
            return;
        }
    }

}
global $arm_member_forms, $arm_form_popup_ids_arr, $arm_login_form_popup_ids_arr;
$arm_member_forms = new ARM_member_forms();
$arm_form_popup_ids_arr = array();
$arm_login_form_popup_ids_arr = array();

if (!class_exists('ARM_Form')) {

    class ARM_Form {

        var $ID;
        var $name;
        var $slug;
        var $type;
        var $default;
        var $set_id;
        var $updated;
        var $created;
        var $settings;
        var $fields;
        var $form_detail;

        public function __construct($field = '', $value = '') {
            global $wp, $wpdb, $ARMember;
            $form_info = array();
            switch ($field) {
                case 'id':
                case 'form_id':
                case 'arm_form_id':
                    $key = 'arm_form_id';
                    break;
                case 'slug':
                case 'arm_form_slug':
                    $key = 'arm_form_slug';
                    break;
                case 'type':
                case 'arm_form_type':
                    $key = 'arm_form_type';
                    break;
                default:
                    $key = '';
                    break;
            }
            if (!empty($key) && $value != '') {
                $form_info = $this->get_form_by($key, $value);
                if (!empty($form_info)) {
                    $this->init($form_info);
                }
            }
        }

        public function init($data) {
            $this->ID = $data->arm_form_id;
            $this->name = stripslashes($data->arm_form_title);
            $this->slug = $data->arm_form_slug;
            $this->type = $data->arm_form_type;
            $this->ref_form_id = $data->arm_ref_template;
            $login_regex = "/template-login(.*?)/";
            $register_regex = "/template-registration(.*?)/";
            $forgot_regex = "/template-forgot-password(.*?)/";
            $changepass_regex = "/template-change-password(.*?)/";
            preg_match($login_regex, $this->slug, $match_login);
            preg_match($register_regex, $this->slug, $match_register);
            preg_match($forgot_regex, $this->slug, $match_forgot);
            preg_match($changepass_regex, $this->slug, $match_changepass);
            if (isset($match_login[0]) && !empty($match_login[0])) {
                $this->type = 'login';
            } else if (isset($match_register[0]) && is_array($match_register[0]) && !empty($match_register[0])) {
                $this->type = 'registration';
            } else if (isset($match_forgot[0]) && is_array($match_forgot[0]) && !empty($match_forgot[0])) {
                $this->type = 'forgot_password';
            } else if (isset($match_changepass[0]) && is_array($match_changepass[0]) && !empty($match_changepass[0])) {
                $this->type = 'change_password';
            }
            $this->default = ($data->arm_is_default == '1') ? true : false;
            $this->set_id = $data->arm_set_id;
            $this->updated = $data->arm_form_updated_date;
            $this->created = $data->arm_form_created_date;
            $this->settings = maybe_unserialize($data->arm_form_settings);
            $this->fields = $data->fields;
            $this->form_detail = (array) $data;
            $this->template = ($data->arm_is_template == '1') ? true : false;
        }

        public function get_form_by($field, $value) {
            global $wp, $wpdb, $ARMember;
	    
            /* Query Monitor Change */
            if( isset($GLOBALS['arm_forms']) && isset($GLOBALS['arm_forms'][$value]) ){
                $form_data = $GLOBALS['arm_forms'][$value];
            } else {
                $form_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `$field`='" . $value . "' LIMIT 1");
                $GLOBALS['arm_forms'] = array();
                $GLOBALS['arm_forms'][$value] = $form_data;
            }
            if (!empty($form_data)) {
                $form_data->arm_form_settings = (!empty($form_data->arm_form_settings)) ? maybe_unserialize($form_data->arm_form_settings) : array();
                /* Get Form Fields */
                $form_data->fields = self::get_form_fields($form_data->arm_form_id);
            }
            return $form_data;
        }

        function get_form_fields($form_id = 0) {
            global $wp, $wpdb, $ARMember;
            $fields = array();
            if (!empty($form_id) && $form_id != 0) {
	    
                /* Query Monitor Change */
                if( isset($GLOBALS['arm_form_fields']) && isset($GLOBALS['arm_form_fields'][$form_id]) ){
                    $field_result = $GLOBALS['arm_form_fields'][$form_id];
                } else {
                    $field_result = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`='" . $form_id . "' AND `arm_form_field_status` != '2' AND arm_form_field_slug !='arm_captcha' ORDER BY `arm_form_field_order` ASC", ARRAY_A);
                    $GLOBALS['arm_form_fields'] = array();
                    $GLOBALS['arm_form_fields'][$form_id] = $field_result;
                }
                $i = 1;
                foreach ($field_result as $field) {
                    $field['arm_form_field_option'] = maybe_unserialize($field['arm_form_field_option']);
                    $fields[$i] = $field;
                    $i++;
                }
            }
            return $fields;
        }

        public function exists() {
            return !empty($this->ID);
        }

        public function arm_is_form_exists($form_id) {
            global $wpdb, $ARMember;
            $table = $ARMember->tbl_arm_forms;
            if ($form_id == '' || $form_id == 0) {
                return false;
            }
            $result = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total FROM `" . $table . "` WHERE arm_form_id = %d", $form_id));
            if ($result[0]->total > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}