<?php
if (!class_exists('ARM_global_settings')) {

    class ARM_global_settings {

        private $s;
        private $sub_folder;
        private $is_subdir_mu;
        private $blog_path;
        var $global_settings;
        var $block_settings;
        var $common_message;
        var $profile_url;

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs; 
            /* ====================================/.Begin Set Global Settings For Class./==================================== */
            $this->global_settings = $this->arm_get_all_global_settings(TRUE);
            $this->block_settings = $this->arm_get_parsed_block_settings();
            $this->common_message = $this->arm_get_all_common_message_settings();

            $sub_installation = trim(str_replace(ARM_HOME_URL, '', site_url()), ' /');
            if ($sub_installation && substr($sub_installation, 0, 4) != 'http') {
                $this->sub_folder = $sub_installation . '/';
            }
            $this->is_subdir_mu = false;
            if (is_multisite()) {
                $this->is_subdir_mu = true;
                if ((defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) || (defined('VHOST') && VHOST == 'yes')) {
                    $this->is_subdir_mu = false;
                }
            }
            if (is_multisite() && !$this->sub_folder && $this->is_subdir_mu) {
                $this->sub_folder = ltrim(parse_url(trim(get_blog_option(BLOG_ID_CURRENT_SITE, 'home'), '/') . '/', PHP_URL_PATH), '/');
            }
            if (is_multisite() && !$this->blog_path && $this->is_subdir_mu) {
                global $current_blog;
                $this->blog_path = str_replace($this->sub_folder, '', $current_blog->path);
            }
            /* ====================================/.End Set Global Settings For Class./==================================== */
            add_action('wp_ajax_arm_send_test_mail', array($this, 'arm_send_test_mail'));
            add_action('wp_ajax_arm_update_global_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_block_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_redirect_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_page_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_common_message_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_invoice_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_access_restriction_settings', array($this, 'arm_update_all_settings'));
            
            add_action('wp_ajax_arm_shortcode_exist_in_page', array($this, 'arm_shortcode_exist_in_page'));
            add_action('wp_ajax_arm_social_form_exist_in_page', array($this, 'arm_social_form_exist_in_page'));
            add_action('wp_ajax_arm_update_feature_settings', array($this, 'arm_update_feature_settings'));
            add_action('wp_ajax_arm_update_pay_per_post_settings', array($this, 'arm_update_all_settings'));
            add_action('wp_ajax_arm_update_api_service_feature', array(&$this, 'arm_update_all_settings'));

            /* Apply Global Setting Action */
            //add_action('init', array($this, 'arm_apply_global_settings'), 200);
            add_action('login_init', array($this, 'arm_check_ip_block_before_login'), 1);
            add_action('login_head', array($this, 'arm_login_enqueue_assets'), 50);
            add_filter('option_users_can_register', array($this, 'arm_remove_registration_link'));
            /* Enable Shortcodes in Widgets */
            add_filter('widget_text', 'do_shortcode');
            /* Filter Post Excerpt for plugin shortcodes */
            add_filter("the_excerpt", array($this, 'arm_filter_the_excerpt'));
            add_filter("the_excerpt_rss", array($this, 'arm_filter_the_excerpt'));

            /* Rewrite Rules */
            add_action('admin_notices', array($this, 'arm_admin_notices'));
            add_action('updated_option', array($this, 'arm_updated_option'), 10, 3);

            add_filter('arm_display_admin_notices', array($this, 'arm_global_settings_notices'));
            /* Filter `get_avatar` */
            add_filter('get_avatar', array($this, 'arm_filter_get_avatar'), 20, 5);
	    /* Filter `get_avatar_url` */
            add_filter('get_avatar_url', array($this, 'arm_filter_get_avatar_url'), 20, 3);
            add_filter('arm_check_member_status_before_login', array($this, 'arm_check_member_status'), 10, 2);
            /* add_filter('arm_check_member_status_before_login', array($this, 'arm_check_block_settings'), 5, 2); */
            /* Delete Term Action Hook */
            add_action('delete_term', array($this, 'arm_after_delete_term'), 10, 4);
            /* Added From Name And Form Email Hook */
            //add_action('admin_enqueue_scripts', array($this, 'arm_add_page_label_css'), 20);
            add_filter('display_post_states', array($this, 'arm_add_set_page_label'), 999, 2);

            add_action('wp_ajax_arm_custom_css_detail', array($this, 'arm_custom_css_detail'));
            add_action('wp_ajax_arm_section_custom_css_detail', array($this, 'arm_section_custom_css_detail'));
            /* Set Global Profile URL */
            add_filter('query_vars', array($this, 'arm_user_query_vars'), 10, 1);
            add_action('wp_ajax_arm_clear_form_fields', array($this, 'arm_clear_form_fields'));
            add_action('wp_ajax_arm_failed_login_lockdown_clear', array($this, 'arm_failed_login_lockdown_clear'));

            add_action('wp_ajax_arm_failed_login_history_clear', array($this, 'arm_failed_login_history_clear'));


            /* bbpress change forum author link */
            add_filter('bbp_get_topic_author_url', array($this, 'arm_bbpress_change_topic_author_url'), 10, 2);
            add_filter('bbp_get_reply_author_url', array($this, 'arm_bbpress_change_reply_author_url'), 10, 2);

            add_action('after_switch_theme',array($this,'arm_set_permalink_for_profile_page'),10);
            add_action('permalink_structure_changed', array($this,'arm_set_session_for_permalink'));
            //add_action('admin_footer',array($this,'arm_rewrite_rules_for_profile_page'),100);

            add_filter( 'generate_rewrite_rules', array($this,'arm_generate_rewrite_rules'),10 );

            add_action('wp_ajax_arm_reset_invoice_to_default', array($this, 'arm_reset_invoice_to_default'));

            //add_action('admin_init',array($this,'arm_plugin_add_suggested_privacy_content'),20);

            add_action('wp_ajax_arm_check_setup_payment_gateway_fields', array($this, 'arm_check_setup_payment_fields'));
        }

        function arm_check_setup_payment_fields()
        {
            $arm_selected_payment_gateways = json_decode(stripslashes($_POST['arm_selected_pgs']));
            if(!empty($arm_selected_payment_gateways))
            {
                $arm_payment_gateway_options = get_option('arm_payment_gateway_settings');
                $arm_pg_validation_return['status'] = 0;
                $arm_pg_validation_return['message'] = '';

                if(in_array('stripe', $arm_selected_payment_gateways) && !empty($arm_payment_gateway_options['stripe']['status']))
                {
                    $arm_stripe_fields_validate['stripe_payment_mode'] = __("Stripe Payment Mode", "ARMember");

                    if($arm_payment_gateway_options['stripe']['stripe_payment_method'] == "popup")
                    {
                        $arm_stripe_fields_validate['stripe_popup_title'] = __("Popup Title", "ARMember");
                        $arm_stripe_fields_validate['stripe_popup_button_lbl'] = __("Popup Button Title", "ARMember");
                    }

                    if($arm_payment_gateway_options['stripe']['stripe_payment_mode'] == "test")
                    {
                        $arm_stripe_fields_validate['stripe_test_secret_key'] = __("Stripe Test Secret Key", "ARMember");
                        $arm_stripe_fields_validate['stripe_test_pub_key'] = __("Stripe Test Public Key", "ARMember");
                    }
                    else
                    {
                        $arm_stripe_fields_validate['stripe_secret_key'] = __("Stripe Live Secret Key", "ARMember");
                        $arm_stripe_fields_validate['stripe_pub_key'] = __("Stripe Live Public Key", "ARMember");
                    }

                    $arm_stripe_error_fields = array();
                    foreach($arm_stripe_fields_validate as $arm_stripe_validation_key => $arm_stripe_validation_field)
                    {
                        if(empty($arm_payment_gateway_options['stripe'][$arm_stripe_validation_key]))
                        {
                            array_push($arm_stripe_error_fields, $arm_stripe_validation_field);
                        }
                    }

                    if(!empty($arm_stripe_error_fields))
                    {
                        $arm_validation_msg = "<div class='arm_pg_error_msg'>";
                        $arm_validation_msg .= implode(',', $arm_stripe_error_fields).__(' fields required', 'ARMember');
                        $arm_validation_msg .= "</div>";

                        $arm_pg_validation_return['status'] = 1;
                        $arm_pg_validation_return['message'] = $arm_validation_msg;
                    }
                }
                
                if(in_array('paypal', $arm_selected_payment_gateways) && !empty($arm_payment_gateway_options['paypal']['status']))
                {
                    $arm_paypal_fields_validate['paypal_payment_mode'] = __("Paypal Payment Mode", "ARMember");
                    if($arm_payment_gateway_options['paypal']['paypal_payment_mode'] == "sandbox")
                    {
                        $arm_paypal_fields_validate['sandbox_api_username'] = __('Paypal Sandbox API Username', 'ARMember');
                        $arm_paypal_fields_validate['sandbox_api_password'] = __('Paypal Sandbox API Password', 'ARMember');
                        $arm_paypal_fields_validate['sandbox_api_signature'] = __('Paypal Sandbox API Signature', 'ARMember');
                    }
                    else
                    {
                        $arm_paypal_fields_validate['sandbox_api_username'] = __('Paypal Live API Username', 'ARMember');
                        $arm_paypal_fields_validate['sandbox_api_password'] = __('Paypal Live API Password', 'ARMember');
                        $arm_paypal_fields_validate['sandbox_api_signature'] = __('Paypal Live API Signature', 'ARMember');
                    }

                    $arm_paypal_error_fields = array();
                    foreach($arm_paypal_fields_validate as $arm_paypal_validate_key => $arm_paypal_validation_field)
                    {
                        if(empty($arm_payment_gateway_options['paypal'][$arm_paypal_validate_key]))
                        {
                            array_push($arm_paypal_error_fields, $arm_paypal_validation_field);
                        }
                    }

                    $arm_validation_msg = "";
                    if(!empty($arm_paypal_error_fields))
                    {
                        $arm_validation_msg .= "<div class='arm_pg_error_msg'>";
                        $arm_validation_msg .= implode(',', $arm_paypal_error_fields).__(' fields required', 'ARMember');
                        $arm_validation_msg .= "</div>";

                        $arm_pg_validation_return['status'] = 1;
                        $arm_pg_validation_return['message'] = $arm_pg_validation_return['message']." ".$arm_validation_msg;
                    }
                }


                if(in_array('authorize_net', $arm_selected_payment_gateways) && !empty($arm_payment_gateway_options['authorize_net']['status']))
                {
                    $arm_autho_fields_validate['autho_mode'] = __("Authorize.Net Payment Mode", "ARMember");
                    
                    $arm_autho_fields_validate['autho_api_login_id'] = __('Authorize.Net API Login ID', 'ARMember');

                    $arm_autho_fields_validate['autho_transaction_key'] = __('Authorize.Net Transaction Key', 'ARMember');

                    $arm_autho_error_fields = array();
                    foreach($arm_autho_fields_validate as $arm_autho_validate_key => $arm_autho_validation_field)
                    {
                        if(empty($arm_payment_gateway_options['authorize_net'][$arm_autho_validate_key]))
                        {
                            array_push($arm_autho_error_fields, $arm_autho_validation_field);
                        }
                    }

                    $arm_validation_msg = "";
                    if(!empty($arm_autho_error_fields))
                    {
                        $arm_validation_msg .= "<div class='arm_pg_error_msg'>";
                        $arm_validation_msg .= implode(',', $arm_autho_error_fields).__(' fields required', 'ARMember');
                        $arm_validation_msg .= "</div>";

                        $arm_pg_validation_return['status'] = 1;
                        $arm_pg_validation_return['message'] = $arm_pg_validation_return['message']." ".$arm_validation_msg;
                    }
                }


                if(in_array('2checkout', $arm_selected_payment_gateways) && !empty($arm_payment_gateway_options['2checkout']['status']))
                {
                    $arm_2checkout_fields_validate['payment_mode'] = __("2 Checkout Payment Mode", "ARMember");
                    
                    $arm_2checkout_fields_validate['username'] = __('2 Checkout Username', 'ARMember');

                    $arm_2checkout_fields_validate['password'] = __('2 Checkout Password', 'ARMember');

                    $arm_2checkout_fields_validate['sellerid'] = __('2 Checkout Seller ID', 'ARMember');

                    $arm_2checkout_fields_validate['private_key'] = __('2 Checkout Private Key', 'ARMember');

                    $arm_2checkout_fields_validate['publishable_key'] = __('2 Checkout Publishable Key', 'ARMember');

                    $arm_2checkout_fields_validate['secret_word'] = __('2 Checkout Secret Word', 'ARMember');

                    $arm_2checkout_error_fields = array();
                    foreach($arm_2checkout_fields_validate as $arm_2checkout_validate_key => $arm_2checkout_validation_field)
                    {
                        if(empty($arm_payment_gateway_options['2checkout'][$arm_2checkout_validate_key]))
                        {
                            array_push($arm_2checkout_error_fields, $arm_2checkout_validation_field);
                        }
                    }

                    $arm_validation_msg = "";
                    if(!empty($arm_2checkout_error_fields))
                    {
                        $arm_validation_msg .= "<div class='arm_pg_error_msg'>";
                        $arm_validation_msg .= implode(',', $arm_2checkout_error_fields).__(' fields required', 'ARMember');
                        $arm_validation_msg .= "</div>";

                        $arm_pg_validation_return['status'] = 1;
                        $arm_pg_validation_return['message'] = $arm_pg_validation_return['message']." ".$arm_validation_msg;
                    }
                }


                $arm_pg_validation_return = apply_filters('arm_configure_setup_payment_gateway_validations', $arm_pg_validation_return, $arm_selected_payment_gateways);

                echo json_encode($arm_pg_validation_return);
                exit();
            }
        }



        function arm_check_common_date_format($selected_date_format)
        {
            $return_final_date_format = '';
            if($selected_date_format == 'F j, Y' || $selected_date_format == 'Y-m-d' || $selected_date_format == 'm/d/Y' || $selected_date_format == 'j F Y' || $selected_date_format == 'j F, Y' || $selected_date_format == "Y m d")
            {
                return $selected_date_format;
            }
            else if($selected_date_format == 'd/m/Y' || $selected_date_format == 'd-m-Y' || $selected_date_format == 'd m Y' || $selected_date_format == 'j. F Y')
            {
                return 'm/d/Y';
            }
            else
            {
                $arm_supported_date_formats = array('d', 'D', 'm', 'M', 'y', 'Y', 'f', 'F', 'j', 'J');
                
                if(substr_count($selected_date_format, '-'))
                {
                    $arm_tmp_date_format_arr = explode('-', $selected_date_format);
                    $return_final_date_format = "";
                    foreach($arm_tmp_date_format_arr as $arm_key => $arm_value)
                    {
                        if(in_array($arm_value, $arm_supported_date_formats))
                        {
                            $return_final_date_format .= $return_final_date_format.'-';
                        }
                    }
                    if(in_array($arm_tmp_date_format_arr[0], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $arm_tmp_date_format_arr[0].'-';
                    }

                    if(in_array($arm_tmp_date_format_arr[1], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[1].'-';
                    }

                    if(in_array($arm_tmp_date_format_arr[2], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[2];
                    }

                    return $return_final_date_format;
                }
                else if(substr_count($selected_date_format, '/'))
                {
                    $arm_tmp_date_format_arr = explode('/', $selected_date_format);
                    $return_final_date_format = "";
                    if(in_array($arm_tmp_date_format_arr[0], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $arm_tmp_date_format_arr[0].'/';
                    }

                    if(in_array($arm_tmp_date_format_arr[1], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[1].'/';
                    }

                    if(in_array($arm_tmp_date_format_arr[2], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[2];
                    }

                    return $return_final_date_format;
                }
                else if(substr_count($selected_date_format, ' '))
                {
                    $arm_tmp_date_format_arr = explode(' ', $selected_date_format);
                    $return_final_date_format = "";
                    if(in_array($arm_tmp_date_format_arr[0], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $arm_tmp_date_format_arr[0].' ';
                    }


                    if(in_array($arm_tmp_date_format_arr[1], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[1].' ';
                    }
                    

                    if(in_array($arm_tmp_date_format_arr[2], $arm_supported_date_formats))
                    {
                        $return_final_date_format = $return_final_date_format.$arm_tmp_date_format_arr[2];
                    }

                    return $return_final_date_format;
                }
                else
                {
                    return 'm/d/Y';
                }
            }
        }

        function arm_plugin_add_suggested_privacy_content(){
            if(function_exists('wp_add_privacy_policy_content'))
            {
                $content = $this->arm_get_privacy_content();
                wp_add_privacy_policy_content( 'ARMember', $content);
            }
        }

        function arm_get_privacy_content(){
            $arm_gdpr_mode_cnt_default = '<h2>'.__('What personal data collected in ARMember','ARMember') .'</h2>'
                            . '<p>'.__('User\'s Signup Details such as Username, Password, First Name, Last Name and Custom Fields value( Address, Gender etc)','ARMember') . '</p>'
                            . '<p>'.__('User\'s IP Address Information','ARMember') . '</p>'
                            . '<p>'.__('User\'s Basic Details Sending to opt-ins such as (Email, First Name, Last Name)','ARMember') . '</p>'
                            . '<p>'.__('User\'s Logged in / Logout details','ARMember') . '</p>'
                            . '<p>'.__('User\'s Basic Social Accounts Details','ARMember') . '</p>'
                            . '<p>'.__('User\'s Basic Payment Transaction Details (Not Storing any sensitive Payment Data such as Credit/Debit Card Details.)','ARMember') . '</p>';

            return $arm_gdpr_mode_cnt_default;
        }

        function arm_reset_invoice_to_default(){
            global $ARMember, $arm_capabilities_global;
            $response = array('type'=> 'error');
            if(isset($_POST['action']) && $_POST['action'] == 'arm_reset_invoice_to_default'){
                    $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                    $all_global_settings = $this->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];

                    $arm_default_invoice_template = $this->arm_get_default_invoice_template();

                    $general_settings['arm_invoice_template'] = $arm_default_invoice_template;
                    $all_global_settings['general_settings'] = $general_settings;
                    
                    update_option('arm_global_settings', $all_global_settings);
                    $response = array('type'=> 'success');
            }

            echo json_encode($response);
            die();
            

        }

        function arm_set_permalink_for_profile_page(){
            $this->arm_user_rewrite_rules();
        }
        function arm_set_session_for_permalink(){
            global $ARMember;
            $ARMember->arm_session_start();
            $_SESSION['arm_site_permalink_is_changed'] = true;
        }
        function arm_rewrite_rules_for_profile_page(){
            global $wp_rewrite, $ARMember;
            $ARMember->arm_session_start();
            if( isset($_SESSION['arm_site_permalink_is_changed']) && $_SESSION['arm_site_permalink_is_changed'] == true ){
                $this->arm_user_rewrite_rules();
                $wp_rewrite->flush_rules(false);
                unset($_SESSION['arm_site_permalink_is_changed']);
            }
        }

        function arm_bbpress_change_topic_author_url($url, $topic_id) {
            global $arm_social_feature, $ARMember, $wpdb;
            $profileTemplate = $ARMember->tbl_arm_member_templates;
            if (!function_exists('is_plugin_active')) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            if (is_plugin_active('bbpress/bbpress.php')) {
                $all_global_settings = $this->arm_get_all_global_settings();
                $general_settings = $all_global_settings['general_settings'];
                $bbpress_profile_page_id = (isset($general_settings['bbpress_profile_page'])) ? $general_settings['bbpress_profile_page'] : 0;

                if (!empty($bbpress_profile_page_id) && $bbpress_profile_page_id != 0) {
                    $profile_page_id = isset($this->global_settings['member_profile_page_id']) ? $this->global_settings['member_profile_page_id'] : 0;

                    if ($profile_page_id == $bbpress_profile_page_id) {

                        if ($arm_social_feature->isSocialFeature) {
                            if (function_exists('bbp_get_topic_author_id')) {
                                $author_id = bbp_get_topic_author_id($topic_id);
                                if (!empty($author_id)) {
                                    $templateOptions = $wpdb->get_var($wpdb->prepare("SELECT `arm_options` FROM {$profileTemplate} WHERE arm_type = %s", 'profile'));
                                    $display_admin_user = 0;
                                    if (!empty($templateOptions)) {
                                        $templateOptions = maybe_unserialize($templateOptions);
                                        $display_admin_user = $templateOptions['show_admin_users'];
                                    }
                                    $url = $this->arm_get_user_profile_url($author_id, $display_admin_user);
                                }
                            }
                        } else {
                            $url = get_permalink($bbpress_profile_page_id);
                        }
                    } else {
                        $url = get_permalink($bbpress_profile_page_id);
                    }
                }
            }
            return $url;
        }

        function arm_bbpress_change_reply_author_url($url, $reply_id) {
            global $arm_social_feature, $ARMember, $wpdb;
            $profileTemplate = $ARMember->tbl_arm_member_templates;
            if (!function_exists('is_plugin_active')) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            if (is_plugin_active('bbpress/bbpress.php')) {
                $all_global_settings = $this->arm_get_all_global_settings();
                $general_settings = $all_global_settings['general_settings'];
                $bbpress_profile_page_id = (isset($general_settings['bbpress_profile_page'])) ? $general_settings['bbpress_profile_page'] : 0;

                if (!empty($bbpress_profile_page_id) && $bbpress_profile_page_id != 0) {
                    $profile_page_id = isset($this->global_settings['member_profile_page_id']) ? $this->global_settings['member_profile_page_id'] : 0;
                    if ($profile_page_id == $bbpress_profile_page_id) {

                        if ($arm_social_feature->isSocialFeature) {
                            if (function_exists('bbp_get_topic_author_id') && function_exists('bbp_user_has_profile')) {
                                $author_id = bbp_get_reply_author_id($reply_id);
                                $anonymous = bbp_is_reply_anonymous($reply_id);

                                if (empty($anonymous) && !empty($author_id) && $author_id != 0 && bbp_user_has_profile($author_id)) {
                                    $templateOptions = $wpdb->get_var($wpdb->prepare("SELECT `arm_options` FROM {$profileTemplate} WHERE arm_type = %s", 'profile'));
                                    $display_admin_user = 0;
                                    if (!empty($templateOptions)) {
                                        $templateOptions = maybe_unserialize($templateOptions);
                                        $display_admin_user = $templateOptions['show_admin_users'];
                                    }
                                    $url = $this->arm_get_user_profile_url($author_id, $display_admin_user);
                                }
                            }
                        } else {
                            $url = get_permalink($bbpress_profile_page_id);
                        }
                    } else {
                        $url = get_permalink($bbpress_profile_page_id);
                    }
                }
            }
            return $url;
        }

        function arm_failed_login_lockdown_clear() {
            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_block_settings'], '1');
            
            if(isset($_POST['reset_attempts_users']) && !empty($_POST['reset_attempts_users'])) {
             
                if(in_array('all', $_POST['reset_attempts_users'])) {
                   
                    $delete = $wpdb->query("TRUNCATE TABLE `$ARMember->tbl_arm_fail_attempts`");
                    $delete = $wpdb->query("TRUNCATE TABLE `$ARMember->tbl_arm_lockdown`");
                } else {
                    
                    foreach($_POST['reset_attempts_users'] as $user_id){
                        $wpdb->delete( $ARMember->tbl_arm_fail_attempts, array( 'arm_user_id' => $user_id ), array( '%d' ) );
                        $wpdb->delete( $ARMember->tbl_arm_lockdown, array( 'arm_user_id' => $user_id ), array( '%d' ) );
                    }
                }
            }
            die();
        }

        function arm_failed_login_history_clear() {
            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_block_settings'], '1');
            $armembermessage = $ARMember->arm_alert_messages();
            $message = $armembermessage['clearLoginHistory'];
            $delete = $wpdb->query("DELETE FROM `$ARMember->tbl_arm_login_history`");
            $ARMember->arm_set_message('success', $message);

            die();
        }

        function arm_clear_form_fields() {
            global $wpdb, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $arm_posted_data = isset($_POST['clear_fields']) ? $_POST['clear_fields'] : array();

            $arm_deleted_fields = array();
            $presetFormFields = get_option('arm_preset_form_fields', '');
            $dbFormFields = maybe_unserialize($presetFormFields);

            if (isset($arm_posted_data) && !empty($arm_posted_data)) {
                foreach ($arm_posted_data as $key => $arm_field_key) {
                    $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key`='" . $key . "'");
                    unset($dbFormFields['other'][$key]);
                    array_push($arm_deleted_fields, $key);
                }
            }
            update_option('arm_preset_form_fields', $dbFormFields);
            echo json_encode($arm_deleted_fields);
            die();
        }

        function arm_send_test_mail() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

            $reply_to = (isset($_POST['reply_to']) && !empty($_POST['reply_to'])) ? sanitize_email($_POST['reply_to']) : '';
            $send_to = (isset($_POST['send_to']) && !empty($_POST['send_to'])) ? sanitize_email($_POST['send_to']) : '';
            $subject = (isset($_POST['subject']) && !empty($_POST['subject'])) ? sanitize_text_field($_POST['subject']) : __('SMTP Test E-Mail', 'ARMember');
            $message = (isset($_POST['message']) && !empty($_POST['message'])) ? sanitize_textarea_field($_POST['message']) : '';
            $reply_to_name = (isset($_POST['reply_to_name']) && !empty($_POST['reply_to_name'])) ? sanitize_text_field($_POST['reply_to_name']) : '';

            $mail_authentication = (isset($_POST['mail_authentication'])) ? intval($_POST['mail_authentication']) : '1';
            $arm_mail_server = (isset($_POST['mail_server']) && !empty($_POST['mail_server'])) ? sanitize_text_field($_POST['mail_server']) : '';
            $arm_mail_port = (isset($_POST['mail_port']) && !empty($_POST['mail_port'])) ? intval($_POST['mail_port']) : '';
            $arm_mail_login_name = (isset($_POST['mail_login_name']) && !empty($_POST['mail_login_name'])) ? sanitize_text_field($_POST['mail_login_name']) : '';
            $arm_mail_password = (isset($_POST['mail_password']) && !empty($_POST['mail_password'])) ? $_POST['mail_password'] : '';
            $arm_mail_enc = (isset($_POST['mail_enc']) && !empty($_POST['mail_enc'])) ? sanitize_text_field($_POST['mail_enc']) : '';

            if (empty($send_to) || empty($reply_to) || empty($message) || empty($subject)) {
                return;
            }

            echo $this->arm_send_tedst_mail_func($reply_to, $send_to, $subject, $message, array(), $reply_to_name, $arm_mail_server, $arm_mail_port, $arm_mail_login_name, $arm_mail_password, $arm_mail_enc, $mail_authentication);
            die();
        }

        public function arm_send_tedst_mail_func($from, $recipient, $subject, $message, $attachments = array(), $reply_to_name = '', $arm_mail_server = '', $arm_mail_port = '', $arm_mail_login_name = '', $arm_mail_password = '', $arm_mail_enc = '', $mail_authentication = '1') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_email_settings, $arm_plain_text, $wp_version;
            $return = false;
            $reply_to_name = ($reply_to_name == '') ? wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) : $reply_to_name;
            $use_only_smtp_settings = false;
            $emailSettings = $arm_email_settings->arm_get_all_email_settings();
            $email_server = 'smtp_server';
            $reply_to_name = ($reply_to_name == '') ? wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) : $reply_to_name;
            $reply_to = ($from == '' or $from == '[admin_email]') ? get_option('admin_email') : $from;
            $from_name = (!empty($emailSettings['arm_email_from_name'])) ? $emailSettings['arm_email_from_name'] : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $from_email = (!empty($emailSettings['arm_email_from_email'])) ? $emailSettings['arm_email_from_email'] : get_option('admin_email');
            $content_type = (@$arm_plain_text) ? 'text/plain' : 'text/html';
            $from_name = $from_name;
            $reply_to = (!empty($from)) ? $from : $from_email;
            /* Set Email Headers */
            $headers = array();
            $header[] = 'From: "' . $reply_to_name . '" <' . $reply_to . '>';
            $header[] = 'Reply-To: ' . $reply_to;
            $headers[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';
            /* Filter Email Subject & Message */
            $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);
            $message = do_shortcode($message);
            $message = wordwrap(stripslashes($message), 70, "\r\n");
            if (@$arm_plain_text) {
                $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);
            }

            $subject = apply_filters('arm_email_subject', $subject);
            $message = apply_filters('arm_change_email_content', $message);
            $recipient = apply_filters('arm_email_recipients', $recipient);
            $headers = apply_filters('arm_email_header', $headers, $recipient, $subject);
            remove_filter('wp_mail_from', 'bp_core_email_from_address_filter');
            remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');
            
            if( version_compare( $wp_version, '5.5', '<' ) ){
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $armPMailer = new PHPMailer();
            } else {
                require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                $armPMailer = new PHPMailer\PHPMailer\PHPMailer();
            }
            
            do_action('arm_before_send_email_notification', $from, $recipient, $subject, $message, $attachments);
            /* Character Set of the message. */
            $armPMailer->CharSet = "UTF-8";

            $armPMailer->SMTPDebug = 1;
            ob_start();
            echo '<span class="arm_smtp_debug_title">';
            echo addslashes(esc_html__('The SMTP debugging output is shown below:', 'ARMember'));
            echo '</span><pre>';
            /* $armPMailer->Debugoutput = 'html'; */

            if ($email_server == 'smtp_server') {
                $armPMailer->isSMTP();
                $armPMailer->Host = isset($arm_mail_server) ? $arm_mail_server : '';
                $armPMailer->SMTPAuth = ($mail_authentication==1) ? true : false;
                $armPMailer->Username = isset($arm_mail_login_name) ? $arm_mail_login_name : '';
                $armPMailer->Password = isset($arm_mail_password) ? $arm_mail_password : '';
                if (isset($arm_mail_enc) && !empty($arm_mail_enc) && $arm_mail_enc != 'none') {
                    $armPMailer->SMTPSecure = $arm_mail_enc;
                }
                if( $arm_mail_enc == 'none' ){
                    $armPMailer->SMTPAutoTLS = false;
                }
                $armPMailer->Port = isset($arm_mail_port) ? $arm_mail_port : '';
            } else {
                $armPMailer->isMail();
            }

            $armPMailer->setFrom($reply_to, $reply_to_name);
            $armPMailer->addReplyTo($reply_to, $reply_to_name);
            $armPMailer->addAddress($recipient);
            if (isset($attachments) && !empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $armPMailer->addAttachment($attachment);
                }
            }
            $armPMailer->isHTML(true);
            $armPMailer->Subject = $subject;
            $armPMailer->Body = $message;
            if (@$arm_plain_text) {
                $armPMailer->AltBody = $message;
            }
            /* Send Email */
            if ($email_server == 'smtp_server' || $email_server == 'phpmailer') {


                if (!$armPMailer->send()) {

                    echo '</pre><span class="arm_smtp_debug_title">';
                    echo addslashes(esc_html__('The full debugging output is shown below:', 'ARMember'));
                    echo '</span>';
                    var_dump($armPMailer);
                    $smtp_debug_log = ob_get_clean();

                    $popup = '<div id="arm_smtp_debug_notices" class="popup_wrapper smtp_debug_notices" style="width:1000px;"><div class="popup_wrapper_inner">';
                    $popup .= '<div class="popup_header" >';
                    $popup .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
                    $popup .= '<span class="popup_header_text">SMTP Test Full Log</span>';
                    $popup .= '</div>';
                    $popup .= '<div class="popup_content_text"><pre>' . $smtp_debug_log . '</pre></div>';
                    $popup .= '<div class="armclear"></div>';
                    $popup .= '</div></div>';

                    echo json_encode(
                    array(
                        'success' => 'false',
                        'msg' => $armPMailer->ErrorInfo.'<span class="arm_error_full_log_container"><a class="" id="smtp_debug_notices_link" href="javascript:void(0)" >'.__('Check Full Log','ARMember').'</a></span>',
                        'log' => $popup
                        )
                    );
                    
                } else {
                    $smtp_debug_log = ob_get_clean();
                    echo json_encode(array('success' => 'true', 'msg' => ''));
                }
            } else {
                if (!wp_mail($recipient, $subject, $message, $header, $attachments)) {

                    if (!$armPMailer->send()) {

                        return false;
                    } else {

                        return true;
                    }
                } else {

                    return true;
                }
            }
        }

        function arm_change_from_email($from_email) {
            global $arm_email_settings;
            $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
            $from_email = (!empty($all_email_settings['arm_email_from_email'])) ? $all_email_settings['arm_email_from_email'] : get_option('admin_email');
            return $from_email;
        }

        function arm_change_from_name($from_name) {
            global $arm_email_settings;
            $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
            $from_name = (!empty($all_email_settings['arm_email_from_name'])) ? $all_email_settings['arm_email_from_name'] : get_option('blogname');
            return $from_name;
        }

        /* ====================================/.Begin Rename WP-ADMIN Folder Settings./==================================== */

        function arm_updated_option($option, $old_value, $value) {
            global $wp, $wpdb, $wp_rewrite, $arm_errors, $arm_slugs, $ARMember;
            if (!empty($option) && $option == 'permalink_structure') {
                if (empty($value)) {
                    $rename_wp_admin = $this->global_settings['rename_wp_admin'];
                    if (!empty($rename_wp_admin) && $rename_wp_admin == 1) {
                        $all_settings = $this->arm_get_all_global_settings();
                        $all_settings['general_settings']['rename_wp_admin'] = 0;
                        $all_settings['general_settings']['temp_wp_admin_path'] = 'wp-admin';
                        $all_settings['general_settings']['new_wp_admin_path'] = 'wp-admin';
                        update_option('arm_global_settings', $all_settings);
                        if (function_exists('save_mod_rewrite_rules')) {
                            save_mod_rewrite_rules();
                        }
                    }
                }
            }
        }

        function arm_admin_notices() {
            global $wp, $wpdb, $wp_rewrite, $arm_errors, $arm_slugs, $ARMember;
            /*             * ====================/.Begin Display Admin Notices./====================* */
            $current_cookie = str_replace(SITECOOKIEPATH, '', ADMIN_COOKIE_PATH);
            /* For non-sudomain and with paths mu: */
            if (!$current_cookie) {
                $current_cookie = 'wp-admin';
            }
            if (!trim($this->global_settings['temp_wp_admin_path'], ' /') || trim($this->global_settings['temp_wp_admin_path'], ' /') == 'wp-admin') {
                $new_admin_path = 'wp-admin';
            } else {
                $new_admin_path = trim($this->global_settings['temp_wp_admin_path'], ' /');
            }


            if(isset($_GET['page']))
            {
                $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : '';
            }

            global $current_screen, $pagenow, $arm_access_rules;
            $default_rule_link = admin_url('admin.php?page=' . $arm_slugs->access_rules );

            if ($current_screen->base == 'nav-menus' || $pagenow == 'nav-menus.php') {
                $default_access_rules = $arm_access_rules->arm_get_default_access_rules();
                $nav_rules = (isset($default_access_rules['nav_menu'])) ? $default_access_rules['nav_menu'] : '';
                if (!empty($nav_rules)) {
                    $warning_msg = '<div class="error arm_admin_notices_container" style="color: #F00;"><p>';
                    $warning_msg .= '<strong>' . __('ARMember Warning', 'ARMember') . ':</strong> ';
                    $warning_msg .= __('Please review', 'ARMember');
                    $warning_msg .= ' <a href="' . $default_rule_link . '"><strong>' . __('Content Access Rules', 'ARMember') . '</strong></a> ';
                    $warning_msg .= __('after adding new menu items. Default access rule will be applied to new menu items.', 'ARMember');
                    $warning_msg .= '</p></div>';
                    echo $warning_msg;
                }
            }
            if ($current_screen->base == 'edit-tags' || $pagenow == 'edit-tags.php') {
                if (!isset($_REQUEST['tag_ID']) || empty($_REQUEST['tag_ID'])) {
                    $taxonomy = $current_screen->taxonomy;
                    $taxo_data = get_taxonomy($taxonomy);
                    $default_access_rules = $arm_access_rules->arm_get_default_access_rules();
                    if ($taxo_data->name == 'category') {
                        $taxo_rules = (isset($default_access_rules['category'])) ? $default_access_rules['category'] : '';
                        $taxo_data->label = __('category(s)', 'ARMember');
                    } else {
                        $taxo_rules = (isset($default_access_rules['taxonomy'])) ? $default_access_rules['taxonomy'] : '';
                        $taxo_data->label = __('custom taxonomy(s)', 'ARMember');
                    }
                    if (!empty($taxo_rules)) {
                        $warning_msg = '<div class="error arm_admin_notices_container" style="color: #F00;"><p>';
                        $warning_msg .= '<strong>' . __('ARMember Warning', 'ARMember') . ':</strong> ';
                        $warning_msg .= __('Please review', 'ARMember');
                        $warning_msg .= ' <a href="' . $default_rule_link . '"><strong>' . __('Access Rules', 'ARMember') . '</strong></a> ';
                        $warning_msg .= __('after adding new', 'ARMember') . ' ' . $taxo_data->label . '. ';
                        $warning_msg .= __('Default access rule will be applied to new', 'ARMember') . ' ' . $taxo_data->label . '. ';
                        $warning_msg .= '</p></div>';
                        echo $warning_msg;
                    }
                }
            }
            /*             * ====================/.End Display Admin Notices./====================* */
        }

        function is_permalink() {
            global $wp_rewrite;
            if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
                return false;
            }
            return true;
        }

        function arm_mod_rewrite_rules($rules) {
            global $wp, $wpdb, $arm_errors, $ARMember;
            $home_root = parse_url(ARM_HOME_URL);
            if (isset($home_root['path'])) {
                $home_root = trailingslashit($home_root['path']);
            } else {
                $home_root = '/';
            }
            $rules = str_replace('(.*) ' . $home_root . '$1$2 ', '(.*) $1$2 ', $rules);
            return $rules;
        }

        function arm_wp_admin_rewrite_rules($wp_rewrite) {
            global $wp, $wpdb, $arm_errors, $ARMember;
            $rename_wp_admin = $this->global_settings['rename_wp_admin'];
            $new_non_wp_rules = array();
            if (!empty($rename_wp_admin) && $rename_wp_admin == 1) {
                $new_wp_admin_path = !empty($this->global_settings['new_wp_admin_path']) ? $this->global_settings['new_wp_admin_path'] : 'wp-admin';
                $new_wp_admin_path = (trim($new_wp_admin_path, ' /')) ? trim($new_wp_admin_path, ' /') : 'wp-admin';
                if ($new_wp_admin_path != 'wp-admin' && $this->is_permalink()) {
                    $rel_admin_path = $this->sub_folder . 'wp-admin';
                    $new_admin_path = trim($new_wp_admin_path, ' /');
                    $new_non_wp_rules[$new_admin_path . '/(.*)'] = $rel_admin_path . '/$1';
                }
                add_filter('mod_rewrite_rules', array($this, 'arm_mod_rewrite_rules'), 10, 1);
            }
            if (isset($new_non_wp_rules) && $this->is_permalink()) {
                $wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $new_non_wp_rules);
            }
            return $wp_rewrite;
        }

        function arm_replace_admin_url($url, $path = '', $scheme = 'admin') {
            global $wp, $wpdb, $arm_errors, $ARMember;
            $rename_wp_admin = $this->global_settings['rename_wp_admin'];
            if (!empty($rename_wp_admin) && $rename_wp_admin == 1) {
                $new_wp_admin_path = !empty($this->global_settings['new_wp_admin_path']) ? $this->global_settings['new_wp_admin_path'] : 'wp-admin';
                $new_wp_admin_path = (trim($new_wp_admin_path, ' /')) ? trim($new_wp_admin_path, ' /') : 'wp-admin';
                /* Replace New Admin Path */
                if ($new_wp_admin_path != 'wp-admin' && $this->is_permalink()) {
                    $url = str_replace('wp-admin/', $new_wp_admin_path . '/', $url);
                }
            }
            return $url;
        }

        /* ====================================/.End Rename WP-ADMIN Folder Settings./==================================== */

        function arm_apply_global_settings() {
            global $wp, $wpdb, $wp_rewrite, $arm_errors, $current_user, $arm_slugs, $ARMember, $arm_members_class, $arm_restriction, $arm_member_forms;
            $all_settings = $this->global_settings;

            if (isset($_REQUEST['arm_wpdisable']) && !empty($_REQUEST['arm_wpdisable'])) {
                $arm_hide_wp_admin_option = get_option('arm_hide_wp_amin_disable');
                if ($arm_hide_wp_admin_option == $_REQUEST['arm_wpdisable']) {

                    $all_saved_global_settings = maybe_unserialize(get_option('arm_global_settings'));
                    $new_wp_admin_path = $all_saved_global_settings['general_settings']['new_wp_admin_path'];

                    $home_path = $this->arm_get_home_path();
                    $rewritecode = '';





                    if (!file_exists($home_path . '.htaccess') || !is_writable($home_path . '.htaccess') || !file_exists($home_path . 'wp-config.php') || !is_writable($home_path . 'wp-config.php')) {

                        $config_error = true;
                        $htaccess_notice = true;


                        $arm_rename_wp = new ARM_rename_wp();
                        $arm_rename_wp->enable_rename_wp = 0;
                        $arm_rename_wp->new_wp_admin_name = 'wp-admin';
                        $arm_rename_wp->arm_replace = array();
                        $arm_rename_wp->armBuildRedirect();
                        $rewrite_notice = '';


                        $rewrites = array();
                        if (!empty($arm_rename_wp->arm_replace)) {
                            foreach ($arm_rename_wp->arm_replace['to'] as $key => $replace) {
                                if ($arm_rename_wp->arm_replace['rewrite'][$key]) {
                                    $rewrites[] = array(
                                        'from' => $arm_rename_wp->arm_replace['to'][$key] . '(.*)',
                                        'to' => $arm_rename_wp->arm_replace['from'][$key] . '$' . (substr_count($arm_rename_wp->arm_replace['to'][$key], '(') + 1)
                                    );
                                }
                            }
                        }

                        foreach ($rewrites as $rewrite) {

                            if (strpos($rewrite['to'], 'index.php') === false) {
                                $rewritecode .= 'RewriteRule ^' . $rewrite['from'] . ' ' . $home_root . $rewrite['to'] . " [QSA,L]<br />";
                            }
                        }

                        $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by deleting following line before \'RewriteCond %{REQUEST_FILENAME} !-f\': <br /><code>' . $rewritecode . '</code><br/>';


                        $rewritecode = "define('ADMIN_COOKIE_PATH','" . rtrim(wp_make_link_relative(network_site_url($new_wp_admin_path)), '/') . "');";
                        $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by deleting following line <code>' . $rewritecode . '</code>';

                        
                    } else {

                        $htaccess_notice = false;
                        $config_error = false;
                        require_once ABSPATH . 'wp-admin/includes/misc.php';
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        $removeTag = $all_saved_global_settings['general_settings']['new_wp_admin_path'] . '/(.*)';
                        $wp_rewrite->remove_rewrite_tag($removeTag);
                        $rewrite_notice = '';
                        if (!function_exists('save_mod_rewrite_rules')) {
                            $htaccess_notice = true;
                            $rewritecode = "RewriteRule ^{$all_saved_global_settings['general_settings']['new_wp_admin_path']}/(.*) {$home_root}wp-admin/$1 [QSA,L]";
                            $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by remove following line from your .htaccess file <br /><code>' . $rewritecode . '</code>';
                        } else {
                            if (!save_mod_rewrite_rules()) {
                                $htaccess_notice = true;
                                $rewritecode = "RewriteRule ^{$all_saved_global_settings['general_settings']['new_wp_admin_path']}/(.*) {$home_root}wp-admin/$1 [QSA,L]";
                                $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by remove following line from your .htaccess file <br /><code>' . $rewritecode . '</code>';
                            }
                        }

                        if (!$this->remove_config_file()) {
                            $config_error = true;
                            $rewritecode = "define('ADMIN_COOKIE_PATH','{$_POST['arm_general_settings']['new_wp_admin_path']}');";
                            $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by removing following line <code>' . $rewritecode . '</code>';
                        }
                    }
                    $all_saved_global_settings['general_settings']['rename_wp_admin'] = 0;
                            $all_saved_global_settings['general_settings']['new_wp_admin_path'] = 'wp-admin';
                            $all_saved_global_settings['general_settings']['temp_wp_admin_path'] = 'wp-admin';
                            update_option('arm_global_settings', $all_saved_global_settings);



                        if ($htaccess_notice == true || $config_error == true) {

                            

                            wp_die($rewrite_notice);
                        } else {


                            
                            wp_destroy_current_session();
                            wp_clear_auth_cookie();
                            $success_msg = __('
                                <b>Rename wp-admin folder</b> setting is revereted. Now you can access admin panel with /wp-admin. Return to', 'ARMember');

                            $success_msg .= ' <a href="'.ARM_HOME_URL.'">'.__('Home Page', 'ARMember').'</a>';

                            wp_die($success_msg);
                            exit;
                        }
                    
                }
            }
            /* Hide admin bar for non-admin users. */
            $allow_access_admin_roles = array();
            $hide_admin_bar = isset($all_settings['hide_admin_bar']) ? $all_settings['hide_admin_bar'] : 0;
            if ($hide_admin_bar == 1) {
                remove_all_filters('show_admin_bar');
                if(isset($all_settings['arm_exclude_role_for_hide_admin']) && is_array($all_settings['arm_exclude_role_for_hide_admin']))
                {
                    $allow_access_admin_roles = $all_settings['arm_exclude_role_for_hide_admin'];
                } else {

                    $allow_access_admin_roles = (isset($all_settings['arm_exclude_role_for_hide_admin']) && !empty($all_settings['arm_exclude_role_for_hide_admin'])) ? explode(',', $all_settings['arm_exclude_role_for_hide_admin']) : array(); 
                }
                $user_match_role = array_intersect($current_user->roles, $allow_access_admin_roles);
                if(empty($user_match_role)) {
                    if (!is_admin() && !current_user_can('administrator')) {
                        add_filter('show_admin_bar', '__return_false');
                    }
                }
                
            }/* End `($hide_admin_bar == 1)` */
            /* New User Verification */
            $user_register_verification = isset($all_settings['user_register_verification']) ? $all_settings['user_register_verification'] : 'auto';
            if ($user_register_verification != 'auto') {
                add_action('user_register', array($arm_members_class, 'arm_add_member_activation_key'));
            }
            /* Verify Member Detail Before Login */
            if(!is_admin())
            {
                add_filter('authenticate', array(&$arm_members_class, 'arm_user_register_verification'), 10, 3);
            }
            /**
             * Load Google Fonts for TinyMCE Editor
             */
        }

        function arm_get_home_path() {
            $home = get_option('home');
            $siteurl = get_option('siteurl');
            if (!empty($home) && 0 !== strcasecmp($home, $siteurl)) {
                $wp_path_rel_to_home = str_ireplace($home, '', $siteurl); /* $siteurl - $home */
                $pos = strripos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']), trailingslashit($wp_path_rel_to_home));
                $home_path = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
                $home_path = trailingslashit($home_path);
            } else {
                $home_path = ABSPATH;
            }
            return $home_path;
        }

        function arm_check_member_status($return = true, $user_id = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms;
            if (!empty($user_id) && $user_id != 0) {
                if (is_super_admin($user_id)) {
                    return true;
                }
                $primary_status = arm_get_member_status($user_id);
                $secondary_status = arm_get_member_status($user_id, 'secondary');
                switch ($primary_status) {
                    case 'pending':
                    case 3:
                        $pending_msg = (!empty($this->common_message['arm_account_pending'])) ? $this->common_message['arm_account_pending'] : '<strong>' . __('Account Pending', 'ARMember') . '</strong>: ' . __('Your account is currently not active. An administrator needs to activate your account before you can login.', 'ARMember');
                        $return = $arm_errors;
                        /* Remove other filters when there is an error */
                        remove_all_filters('arm_check_member_status_before_login');
                        break;
                    case 'inactive':
                    case 2:
                        if(($primary_status == '2' && in_array($secondary_status, array(0,1))) || $primary_status == 4){
                            $err_msg = (!empty($this->common_message['arm_account_inactive'])) ? $this->common_message['arm_account_inactive'] : '<strong>' . __('Account Inactive', 'ARMember') . '</strong>: ' . __('Your account is currently not active. Please contact the system administrator.', 'ARMember');
                            $arm_errors->add('access_denied', $err_msg);
                        }
                        $return = $arm_errors;
                            /* Remove other filters when there is an error */
                            remove_all_filters('arm_check_member_status_before_login');
                        break;
                    case 'active':
                    case 1:
                        $return = TRUE;
                        break;
                    default:
                        $return = TRUE;
                        break;
                }
            } else {
                $return = FALSE;
            }
            return $return;
        }

        function arm_check_ip_block_before_login() {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $disable_wp_login_style = isset($general_settings['disable_wp_login_style']) ? $general_settings['disable_wp_login_style'] : 0;
            if($disable_wp_login_style == 0)
            {
                /* Remove Label For Custom Style. */
                add_filter('gettext', array($this, 'remove_loginpage_label_text'), 50);
            }
            $block_list = $this->block_settings;
            /* Get Visitor's IP Address. */
            $currentr_ip = $ARMember->arm_get_ip_address();
	    $arm_block_ips = isset($block_list['arm_block_ips']) ? $block_list['arm_block_ips'] : array();
            $arm_block_ips = apply_filters('arm_restrict_user_before_login', $arm_block_ips);
            $block_ips_msg = (!empty($block_list['arm_block_ips_msg'])) ? $block_list['arm_block_ips_msg'] : '<strong>' . __('Blocked', 'ARMember') . ': </strong>' . __('Your IP has been blocked.', 'ARMember');

            if (!empty($arm_block_ips) && in_array($currentr_ip, $arm_block_ips)) {
                $arm_errors->add('blocked_ip', $block_ips_msg);
                login_header('', '', $arm_errors);
                echo '</div>';
                do_action('login_footer');
                echo '</body></html>';
                exit;
            }
            wp_enqueue_script('jquery');
        }

        function arm_global_settings_notices($notices = array()) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_slugs, $arm_social_feature;
            $default_global_settings = $this->arm_default_global_settings();
            $default_page_settings = $default_global_settings['page_settings'];
            $page_settings = $this->arm_get_single_global_settings('page_settings');
            $final_page_settings = shortcode_atts($default_page_settings, $page_settings);
            if (!empty($final_page_settings)) {
                $empty_pages = array();
                foreach ($final_page_settings as $key => $page_id) {
                    if (in_array($key, array('logout_page_id', 'guest_page_id', 'thank_you_page_id', 'cancel_payment_page_id'))) {
                        continue;
                    }
                    if ($key == 'member_profile_page_id' && !$arm_social_feature->isSocialFeature) {
                        continue;
                    }
                    if (empty($page_id) || $page_id == 0) {
                        $name = str_replace('_page_id', '', $key);
                        $name = str_replace('_', ' ', $name);
                        $name = ucfirst($name);
                        $empty_pages[] = $name;
                    }
                }
                if (!empty($empty_pages)) {
                    $empty_pages = trim(implode(', ', $empty_pages), ', ');
                    $page_settings_url = admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup');
                    $notices[] = array('type' => 'error', 'message' => __('You need to set', 'ARMember') . ' <b>\'' . $empty_pages . '\'</b> ' . __('page(s) in', 'ARMember') . ' <a href="' . $page_settings_url . '">' . __('page settings', 'ARMember') . '</a>');
                }
            }
            return $notices;
        }

        function arm_get_default_invoice_template(){
            $arm_default_invoice_template = '<div id="arm_invoice_div" class="entry-content ms-invoice">';
    $arm_default_invoice_template .= '<style>';
            $arm_default_invoice_template .= '#arm_invoice_div table, th, td { margin: 0; font-size: 14px; }';
            $arm_default_invoice_template .= '#arm_invoice_div table { padding: 0; border: 1px solid #DDD; width: 100%; background-color: #FFF; box-shadow: 0 1px 8px #F0F0F0; }';
            $arm_default_invoice_template .= '#arm_invoice_div th, td { border: 0; padding: 8px; }';
            $arm_default_invoice_template .= '#arm_invoice_div th { font-weight: bold; text-align: left; text-transform: none; font-size: 13px; }';
            $arm_default_invoice_template .= '#arm_invoice_div tr.alt { background-color: #F9F9F9; }';
            $arm_default_invoice_template .= '#arm_invoice_div tr.sep th, #arm_invoice_div tr.sep td { border-top: 1px solid #DDD; padding-top: 16px; }';
            $arm_default_invoice_template .= '#arm_invoice_div tr.space th, #arm_invoice_div tr.space td { padding-bottom: 16px; }';
            $arm_default_invoice_template .= '#arm_invoice_div tr.ms-inv-sep th,#arm_invoice_div tr.ms-inv-sep td { line-height: 1px; height: 1px; padding: 0; border-bottom: 1px solid #DDD; background-color: #F9F9F9; }';
            $arm_default_invoice_template .= '#arm_invoice_div .ms-inv-total .ms-inv-price { font-weight: bold; font-size: 18px; text-align: right; }';
            $arm_default_invoice_template .= '#arm_invoice_div h2 { text-align: right; padding: 0 10px 0 0;margin:0 auto;}';
            $arm_default_invoice_template .= '#arm_invoice_div h2 a { color: #000; }';             
            $arm_default_invoice_template .= '</style>';
$arm_default_invoice_template .= '<div class="ms-invoice-details ms-status-paid">';
                                        $arm_default_invoice_template .= '<table class="ms-purchase-table" cellspacing="0">';
                                            $arm_default_invoice_template .= '<tbody>';
                                                $arm_default_invoice_template .= '<tr class="ms-inv-title">';
                                                    $arm_default_invoice_template .= '<td colspan="2" align="right">';
                                                    $arm_default_invoice_template .= '<h2>Invoice {ARM_INVOICE_INVOICEID}</h2>';
                                                    $arm_default_invoice_template .= '<div style="text-align: right; padding: 0px 10px 10px 0px;">{ARM_INVOICE_PAYMENTDATE}</div>';
                                                $arm_default_invoice_template .= '</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                             
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-to alt space sep">';
                                                    $arm_default_invoice_template .= '<th>Invoice to</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_USERFIRSTNAME} {ARM_INVOICE_USERLASTNAME} ( {ARM_INVOICE_PAYEREMAIL} )</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                          
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-item-name space">';
                                                    $arm_default_invoice_template .= '<th>Plan Name</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONNAME}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-description alt space">';
                                                    $arm_default_invoice_template .= '<th>Description</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONDESCRIPTION}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
                                                    $arm_default_invoice_template .= '<th>Plan Amount</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_AMOUNT}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                               
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
                                                    $arm_default_invoice_template .= '<th>transaction Id</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRANSACTIONID}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
                                                    $arm_default_invoice_template .= '<th>subscription id</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_SUBSCRIPTIONID}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space alt">';
                                                    $arm_default_invoice_template .= '<th>payment gateway</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_GATEWAY}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
                                                    $arm_default_invoice_template .= '<th>trial amount</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALAMOUNT}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space alt">';
                                                    $arm_default_invoice_template .= '<th>trial period</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALPERIOD}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
                                                    $arm_default_invoice_template .= '<th>coupon code</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONCODE}</td>';
                                                $arm_default_invoice_template .= '</tr>';
                                                
                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
                                                    $arm_default_invoice_template .= '<th>coupon discount</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONAMOUNT}</td>';
                                                $arm_default_invoice_template .= '</tr>';

                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
                                                    $arm_default_invoice_template .= '<th>Tax Percentage</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXPERCENTAGE}</td>';
                                                $arm_default_invoice_template .= '</tr>';

                                                $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
                                                    $arm_default_invoice_template .= '<th>Tax Amount</th>';
                                                    $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXAMOUNT}</td>';
                                                $arm_default_invoice_template .= '</tr>';

                                               
                                                
                                                $arm_default_invoice_template .= '</tbody>';
                                            $arm_default_invoice_template .= '</table>';
                                       $arm_default_invoice_template .= '</div>';
                                    $arm_default_invoice_template .= '</div>';

                                    return $arm_default_invoice_template;
        }

        function arm_default_global_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_pay_per_post_feature;
            $default_global_settings = array();
            /* General Settings */
            
            
            $arm_default_invoice_template = $this->arm_get_default_invoice_template();
            
            $default_global_settings['general_settings'] = array(
                'hide_admin_bar' => 0,
                'arm_exclude_role_for_hide_admin' => 0,
                'restrict_admin_panel' => 0,
                'arm_exclude_role_for_restrict_admin' => 0,
                'hide_wp_login' => 0,
                'rename_wp_admin' => 0,
                'temp_wp_admin_path' => '',
                'new_wp_admin_path' => 'wp-admin',
                'hide_register_link' => 0,
                'user_register_verification' => 'auto',
                'arm_new_signup_status' => 1,
                'hide_feed' => 0,
                'disable_wp_login_style' => 0,
                'restrict_site_access' => 0,
                'arm_access_page_for_restrict_site' => 0,
                'autolock_shared_account' => 0,
                'paymentcurrency' => 'USD',
                'arm_specific_currency_position' => 'suffix',
                'custom_currency' => array(
                    'status' => 0,
                    'symbol' => '',
                    'shortname' => '',
                    'place' => 'prefix',
                ),
                'enable_tax' => 0,
                'tax_type' => 'common_tax',
                'tax_amount' => 0,
                'country_tax_field' => '',
                "arm_tax_country_name" => '',
                "arm_country_tax_val" => 0,
                "arm_country_tax_default_val" => 0,
                "invc_pre_sfx_mode" => 0,
                "invc_prefix_val" => '#',
                "invc_suffix_val" => '',
                "invc_min_digit" => 0,
                'file_upload_size_limit' => '2',
                'enable_gravatar' => 1,
                'enable_crop' => 1,
                'spam_protection'=> 1,
                'enqueue_all_js_css' => 0,
                'global_custom_css' => '',
                'badge_width' => 30,
                'badge_height' => 30,
                'profile_permalink_base' => 'user_login',
                'bbpress_profile_page' => 0,
                'arm_email_schedular_time' => 12,
                'arm_invoice_template' => $arm_default_invoice_template,
                'arm_recaptcha_site_key' => '',
                'arm_recaptcha_private_key' => '',
                'arm_recaptcha_theme' => '',
                'arm_recaptcha_lang' => '',
                'front_settings' => array(
                    'level_1_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '18',
                        'font_color' => '#32323a',
                        'font_bold' => 1,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'level_2_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '16',
                        'font_color' => '#32323a',
                        'font_bold' => 1,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'level_3_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '15',
                        'font_color' => '#727277',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'level_4_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '14',
                        'font_color' => '#727277',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'link_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '14',
                        'font_color' => '#0c7cd5',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                    'button_font' => array(
                        'font_family' => 'Open Sans',
                        'font_size' => '14',
                        'font_color' => '#FFFFFF',
                        'font_bold' => 0,
                        'font_italic' => 0,
                        'font_decoration' => '',
                    ),
                ),
                'arm_pay_per_post_buynow_var' => '',
                'arm_pay_per_post_allow_fancy_url' => '',
                'arm_pay_per_post_default_content' => '',
            );
            /* Page Settings */
            $default_global_settings['page_settings'] = array(
                'register_page_id' => 0,
                'login_page_id' => 0,
                'forgot_password_page_id' => 0,
                'edit_profile_page_id' => 0,
                'change_password_page_id' => 0,
                'member_profile_page_id' => 0,
                'logout_page_id' => 0,
                'guest_page_id' => 0,
                'thank_you_page_id' => 0,
                'cancel_payment_page_id' => 0,
            );
            if(!empty($arm_pay_per_post_feature->isPayPerPostFeature)){
                $default_global_settings['page_settings']['paid_post_page_id'] = 0;
            }


            if(!empty($arm_api_service_feature->isAPIServiceFeature)){
                $default_global_settings['api_service'] = array(
                    'arm_api_service_security_key' => '',
                    'arm_list_membership_plans' => 0,
                    'arm_membership_plan_details' => 0,
                    'arm_member_details' => 0,
                    'arm_member_memberships' => 0,
                    'arm_member_paid_posts' => 0,
                    'arm_member_payments' => 0,
                    'arm_member_paid_post_payments' => 0,
                    'arm_check_coupon_code' => 0,
                    'arm_member_add_membership' => 0,
                    'arm_member_cancel_membership' => 0,
                    'arm_check_member_membership' => 0,
                    'arm_create_transaction' => 0,
                );
            }
            
            $default_global_settings = apply_filters('arm_default_global_settings', $default_global_settings);
            return $default_global_settings;
        }

        function arm_default_pages_content() {
            global $wpdb, $ARMember, $arm_members_class, $arm_slugs, $arm_member_forms;
            $default_rf_id = $arm_member_forms->arm_get_default_form_id('registration');
            $default_lf_id = $arm_member_forms->arm_get_default_form_id('login');
            $default_ff_id = $arm_member_forms->arm_get_default_form_id('forgot_password');
            $default_cf_id = $arm_member_forms->arm_get_default_form_id('change_password');
            $default_ep_id = $arm_member_forms->arm_get_default_form_id('edit_profile');
            $logged_in_message = __('You are already logged in.', 'ARMember');
            $all_pages = array(
                'register_page_id' => array(
                    'post_title' => 'Register',
                    'post_name' => 'register',
                    'post_content' => '[arm_form id="' . $default_rf_id . '" logged_in_message="' . $logged_in_message . '"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'login_page_id' => array(
                    'post_title' => 'Login',
                    'post_name' => 'login',
                    'post_content' => '[arm_form id="' . $default_lf_id . '" logged_in_message="' . $logged_in_message . '"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'forgot_password_page_id' => array(
                    'post_title' => 'Forgot Password',
                    'post_name' => 'forgot_password',
                    'post_content' => '[arm_form id="' . $default_ff_id . '" logged_in_message="' . $logged_in_message . '"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'edit_profile_page_id' => array(
                    'post_title' => 'Edit Profile',
                    'post_name' => 'edit_profile',
                    'post_content' => '[arm_profile_detail id="' . $default_ep_id . '"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'change_password_page_id' => array(
                    'post_title' => 'Change Password',
                    'post_name' => 'change_password',
                    'post_content' => '[arm_form id="' . $default_cf_id . '"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'guest_page_id' => array(
                    'post_title' => 'Guest',
                    'post_name' => 'guest',
                    'post_content' => '<h3>' . __('Welcome Guest', 'ARMember') . ',</h3>',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'thank_you_page_id' => array(
                    'post_title' => 'Thank You',
                    'post_name' => 'thank_you',
                    'post_content' => "<h3>" . __('Thank you for payment with us, We will reach you soon.', 'ARMember') . "</h3>",
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
                'cancel_payment_page_id' => array(
                    'post_title' => 'Cancel Payment',
                    'post_name' => 'cancel_payment',
                    'post_content' => __('Your purchase has not been completed.', 'ARMember') . '<br/>' . __('Sorry something went wrong while processing your payment.', 'ARMember'),
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
                ),
            );
            return $all_pages;
        }

        function arm_default_common_messages() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $common_messages = array(
                'arm_user_not_exist' => __('No such user exists in the system.', 'ARMember'),
                'arm_invalid_password_login' => __('The password you entered is invalid.', 'ARMember'),
                'arm_attempts_login_failed' => __('Remaining Login Attempts :', 'ARMember') . '&nbsp;' . '[ATTEMPTS]',
                'arm_attempts_many_login_failed' => __('Your Account is locked for', 'ARMember').' [LOCKDURATION] '.__('minutes.', 'ARMember'),
                'arm_permanent_locked_message' => __('Your Account is locked for', 'ARMember').' [LOCKDURATION] '.__('hours.', 'ARMember'),
                'arm_not_authorized_login' => __('Your account is inactive, you are not authorized to login.', 'ARMember'),
                'arm_spam_msg' => __('Spam detected.', 'ARMember'),
                'social_login_failed_msg' => __('Login Failed, please try again.', 'ARMember'),
                'arm_no_registered_email' => __('There is no user registered with that email address/Username.', 'ARMember'),
                'arm_reset_pass_not_allow' => __('Password reset is not allowed for this user.', 'ARMember'),
                'arm_email_not_sent' => __('Email could not be sent, please contact the site admin.', 'ARMember'),
                'arm_password_reset' => __('Your Password has been reset.', 'ARMember') . ' [LOGINLINK]' . __('Log in', 'ARMember') . ' [/LOGINLINK]',
                'arm_password_enter_new_pwd' => __('Please enter new password', 'ARMember'),
                'arm_password_reset_pwd_link_expired' => __('Reset Password Link is invalid.', 'ARMember'),
                'arm_form_title_close_account' => __('Close Account', 'ARMember'),
                'arm_form_description_close_account' => __('Are you sure you want to delete your account? This will erase all of your account data from the site. To delete your account enter your password below.', 'ARMember'),
                'arm_password_label_close_account' => __('Your Password', 'ARMember'),
                'arm_submit_btn_close_account' => __('Submit', 'ARMember'),
                'arm_blank_password_close_account' => __('Password cannot be left Blank.', 'ARMember'),
                'arm_invalid_password_close_account' => __('The password you entered is invalid.', 'ARMember'),
                'arm_user_not_created' => __('Error while creating user.', 'ARMember'),
                'arm_username_exist' => __('This username is already registered, please choose another one.', 'ARMember'),
                'arm_email_exist' => __('This email is already registered, please choose another one.', 'ARMember'),
                'arm_avtar_label' => __('Avatar', 'ARMember'),
                'arm_profile_cover_label' => __('Profile Cover.', 'ARMember'),
                'arm_maxlength_invalid' => __('Maximum', 'ARMember') . ' [MAXVALUE]' . __(' characters allowed.', 'ARMember'),
                'arm_minlength_invalid' => __('Please enter at least', 'ARMember') . ' [MINVALUE]' . __(' characters.', 'ARMember'),
                'arm_expire_activation_link' => __('Activation link is expired or invalid.', 'ARMember'),
                'arm_expire_reset_password_activation_link' => __('Reset Password Link is expired.', 'ARMember'),
                'arm_email_activation_manual_pending' => __('Your account is not activated yet. Please contact site administrator.', 'ARMember'),
                'arm_already_active_account' => __('Your account has been activated.', 'ARMember'),
                'arm_account_disabled' => __('Your account is disabled. Please contact system administrator.', 'ARMember'),
                'arm_account_inactive' => __('Your account is currently not active. Please contact the system administrator.', 'ARMember'),
                'arm_account_pending' => __('Your account is currently not active. An administrator needs to activate your account before you can login.', 'ARMember'),
                'arm_account_expired' => __('Your account has expired. Please contact system administrator.', 'ARMember'),
                'arm_payment_fail_stripe' => __('Sorry something went wrong while processing payment with Stripe.', 'ARMember'),
                'arm_payment_fail_authorize_net' => __('Sorry, something went wrong while processing payment with Authorize.Net.', 'ARMember'),
                'arm_payment_fail_2checkout' => __('Sorry, something went wrong while processing payment with 2Checkout.', 'ARMember'),
                'arm_invalid_credit_card' => __('Please enter the correct card details.', 'ARMember'),
                'arm_unauthorized_credit_card' => __('Card details could not be authorized, please use other card detail.', 'ARMember'),
                'arm_credit_card_declined' => __('Your Card is declined.', 'ARMember'),
                'arm_blank_expire_month' => __('Expiry month should not be blank.', 'ARMember'),
                'arm_blank_expire_year' => __('Expiry year should not be blank.', 'ARMember'),
                'arm_blank_cvc_number' => __('CVC Number should not be blank.', 'ARMember'),
                'arm_blank_credit_card_number' => __('Card Number should not be blank.', 'ARMember'),
                'arm_invalid_plan_select' => __('Selected plan is not valid.', 'ARMember'),
                'arm_no_select_payment_geteway' => __('Your selected plan is paid, please select a payment method.', 'ARMember'),
                'arm_inactive_payment_gateway' => __('Payment gateway is not active, please contact the site administrator.', 'ARMember'),
                'arm_general_msg' => __('Sorry, something went wrong. Please contact the site administrator.', 'ARMember'),
                'arm_search_result_found' => __('No Search Result Found.', 'ARMember'),
                'arm_armif_invalid_argument' => __('Invalid conditional argument(s).', 'ARMember'),
                'arm_armif_already_logged_in' => __('You are already logged in.', 'ARMember'),
                'arm_success_coupon' => __('Coupon has been successfully applied.', 'ARMember'),
                'arm_empty_coupon' => __('Please enter the coupon code.', 'ARMember'),
                'arm_coupon_expire' => __('Coupon code has expired.', 'ARMember'),
                'arm_invalid_coupon' => __('Coupon code is not valid.', 'ARMember'),
                'arm_invalid_coupon_plan' => __('Coupon code is not valid for the selected plan.', 'ARMember'),
                'profile_directory_upload_cover_photo' => __('Upload Cover Photo', 'ARMember'),
                'profile_directory_remove_cover_photo' => __('Remove Cover Photo', 'ARMember'),
                'profile_template_upload_profile_photo' => __('Upload Profile Photo', 'ARMember'),
                'profile_template_remove_profile_photo' => __('Remove Profile Photo', 'ARMember'),
                'directory_sort_by_alphabatically' => __('Alphabetically', 'ARMember'),
                'directory_sort_by_recently_joined' => __('Recently Joined', 'ARMember'),
                'arm_profile_member_since' => __('Member Since', 'ARMember'),
                'arm_profile_view_profile' => __('View profile', 'ARMember'),
                'arm_do_not_allow_pending_payment_bank_transfer' => __('Sorry! You have already one pending payment transaction. You will be able to proceed after that transaction will be approved.', 'ARMember'),
                'arm_pay_per_post_default_content' => __('Content is Restricted. Buy this post to get access to full content!','ARMember'),
                'arm_disabled_submission' => __('Sorry! Submit Button is disable to avoid any issues because you are logged in as an administrator.', 'ARMember'),
            );
            return $common_messages;
        }

        function arm_get_social_form_page_shortcodes($page_id = 0, $selected_form = '') {
            global $wp, $wpdb, $ARMember, $arm_member_forms;
            $form_shortcodes = $setupForms = array();
            $sel_form_id = (!empty($selected_form)) ? $selected_form : '';
            $form_select_box = '';
            $error_message = true;
            $page_detail = get_post($page_id);
            $page_on_front = get_option('page_on_front');
            $page_for_posts = get_option('page_for_posts');
            if (!empty($page_detail->ID) && $page_detail->ID != 0 && !in_array($page_detail->ID, array($page_on_front, $page_for_posts))) {
                $post_content = $page_detail->post_content;
                $is_setup_shortcode = $this->arm_find_match_shortcode_func('arm_setup', $post_content);
                if ($is_setup_shortcode) {
                    $allSetups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_modules` FROM `" . $ARMember->tbl_arm_membership_setup . "` ORDER BY `arm_setup_id` DESC", ARRAY_A);
                    if (!empty($allSetups)) {
                        foreach ($allSetups as $setup) {
                            $setup_id = $setup['arm_setup_id'];
                            $setupModules = maybe_unserialize($setup['arm_setup_modules']);
                            foreach (array("'$setup_id'", $setup_id, '"' . $setup_id . '"') as $val) {
                                if (preg_match_all('/\[arm_setup(.*)id=' . $val . '(.*)\]/s', $post_content, $matches) > 0) {
                                    if (isset($setupModules['modules']['forms']) && !empty($setupModules['modules']['forms'])) {
                                        $setupForms[] = $setupModules['modules']['forms'];
                                    }
                                }
                            }
                        }
                        $setupForms = (!empty($setupForms)) ? $ARMember->arm_array_unique($setupForms) : array();
                    }
                }
                $is_shortcode = $this->arm_find_match_shortcode_func('arm_form', $post_content);
                if (!$is_shortcode) {
                    $is_shortcode = apply_filters('armember_cs_check_shortcode_in_page', $is_shortcode, 'cs_armember_cs', $post_content);
                }
                $forms = $arm_member_forms->arm_get_member_forms_by_type('registration');
                $allow_fields = array('text', 'email', 'textarea', 'hidden');
                if (!empty($forms)) {
                    foreach ($forms as $form) {
                        $form_id = $form['arm_form_id'];
                        $form_slug = $form['arm_form_slug'];
                        if (in_array($form_id, $setupForms)) {
                            $form_shortcodes['forms'][$form_id] = array(
                                'id' => $form['arm_form_id'],
                                'slug' => $form['arm_form_slug'],
                                'name' => strip_tags(stripslashes($form['arm_form_label'])),
                            );
                        }
                        if ($is_shortcode) {
                            foreach (array("'$form_id'", $form_id, '"' . $form_id . '"') as $val) {
                                if (preg_match_all('/id=' . $val . '|arm_form_registration=' . $val . '/s', $post_content, $matches) > 0) {
                                    $form_shortcodes['forms'][$form_id] = array(
                                        'id' => $form['arm_form_id'],
                                        'slug' => $form['arm_form_slug'],
                                        'name' => strip_tags(stripslashes($form['arm_form_label'])),
                                    );
                                }
                            } /* END `foreach (array("'$form_slug'", $form_slug, '"' . $form_slug . '"') as $val)` */
                        }
                    } /* END `foreach ($forms as $form)` */
                } /* END `if (!empty($forms))` */
                if (!empty($form_shortcodes['forms'])) {
                    $form_select_box = '';
                    $allFoundForms = $form_shortcodes['forms'];
                    $firstForm = array_shift($form_shortcodes['forms']);
                    $sel_form_id = (!empty($selected_form)) ? $selected_form : $firstForm['id'];
                    if (count($allFoundForms) == 1) {
                        $form_select_box .= '<input type="hidden" name="arm_social_settings[registration][form]" value="' . $firstForm['id'] . '"/>';
                        $form_select_box .= $firstForm['name'];
                    } else {
                        $form_select_box .= '<input type="hidden" name="arm_social_settings[registration][form]" id="arm_social_reg_form" class="arm_social_reg_form" value="' . $sel_form_id . '" data-msg-required="' . __('Registration form is required.', 'ARMember') . '" />';
                        $form_select_box .= '<dl class="arm_selectbox column_level_dd">';
                        $form_select_box .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $form_select_box .= '<dd><ul data-id="arm_social_reg_form">';
                        if (!empty($allFoundForms)) {
                            foreach ($allFoundForms as $reg_form) {
                                $form_select_box .= '<li data-label="' . $reg_form['name'] . '" data-value="' . $reg_form['id'] . '">' . $reg_form['name'] . '</li>';
                            }
                        }
                        $form_select_box .= '</ul></dd>';
                        $form_select_box .= '</dl>';
                    }
                }
            }
            if (empty($form_select_box)) {
                $error_message = false;
                $form_select_box .= '<input type="hidden" name="arm_social_settings[registration][form]" value="" data-msg-required="' . __('Registration form is required. Please select valid registration page.', 'ARMember') . '"/>';
            }
            $return_data = array(
                'forms' => $form_select_box,
                'form_id' => $sel_form_id,
                'status' => $error_message,
            );
            return $return_data;
        }

        function arm_social_form_exist_in_page() {
            global $wp, $wpdb, $ARMember, $arm_member_forms, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $page_id = intval($_POST['page_id']);
            $form_shortcodes = $this->arm_get_social_form_page_shortcodes($page_id);
            $forms = $form_shortcodes['forms'];
            $return = array('forms' => $forms, 'form_id' => $form_shortcodes['form_id'], 'status' => $form_shortcodes['status']);
            echo json_encode($return);
            exit;
        }
        
        
        function arm_registration_form_shortcode_exist_in_page($shortcode_type = '', $page_id = 0)
        {
                
        
            global $wp, $wpdb, $ARMember, $arm_member_forms;
                $is_exist = false;

                $page_detail = get_post($page_id);

                if (!empty($page_detail->ID) && $page_detail->ID != 0)
                {
                        $post_content = $page_detail->post_content;
                        $shortcode_text = array();
                        switch ($shortcode_type) {
                            case 'registration':
                            case 'login':
                                $is_shortcode = $this->arm_find_match_shortcode_func('arm_form', $post_content);
                                $is_cs_shortcode = $this->arm_find_match_shortcode_func('cs_armember_cs', $post_content);
                                if ($is_shortcode || $is_cs_shortcode) {
                                        $forms = $arm_member_forms->arm_get_member_forms_by_type($shortcode_type, false);
                                        if (!empty($forms)) {
                                                foreach ($forms as $form) {
                                                        $form_slug = $form['arm_form_id'];
                                                        $shortcode_text[] = "id='$form_slug'";
                                                        $shortcode_text[] = "id=$form_slug";
                                                        $shortcode_text[] = 'id="' . $form_slug . '"';
                                                        if( $shortcode_type == 'registration' ){
                                                                $shortcode_text[] = 'arm_form_registration="'.$form_slug.'"';
                                                        } else if( $shortcode_type == 'login' ){
                                                                $shortcode_text[] = 'arm_form_login="'.$form_slug.'"';
                                                        } else if( $shortcode_type == 'change_password' ){
                                                                $shortcode_text[] = 'arm_form_change_password="'.$form_slug.'"';
                                                        } else if( $shortcode_type == 'forgot_password' ){
                                                                $shortcode_text[] = 'arm_form_forgot_password="'.$form_slug.'"';
                                                        }
                                                }
                                                $is_exist = $this->arm_find_registration_match_func($shortcode_text, $post_content);
                                        }
                                }
                            break;
                            default :
                                break;    
                        }
                }
                return $is_exist; 
        }
                                
                                
                               

        function arm_shortcode_exist_in_page($shortcode_type = '', $page_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_member_forms, $arm_capabilities_global;
            $is_exist = false;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_shortcode_exist_in_page') {
                $shortcode_type = sanitize_text_field($_POST['shortcode_type']);
                $page_id = intval($_POST['page_id']);
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            }
            $page_detail = get_post($page_id);
            if (!empty($shortcode_type) && !empty($page_detail->ID) && $page_detail->ID != 0) {
                $post_content = $page_detail->post_content;
                $shortcode_text = array();
                switch ($shortcode_type) {
                    case 'registration':
                    case 'login':
                    case 'forgot_password':
                    case 'change_password':
                        $is_shortcode = $this->arm_find_match_shortcode_func('arm_form', $post_content);
                        $is_cs_shortcode = false;
                        $is_cs_shortcode = apply_filters('armember_cs_check_shortcode_in_page', $is_cs_shortcode, 'cs_armember_cs', $post_content);
                        if ($is_shortcode || $is_cs_shortcode) {
                            $forms = $arm_member_forms->arm_get_member_forms_by_type($shortcode_type, false);
                            
                             
                            if (!empty($forms)) {
                                foreach ($forms as $form) {
                                    $form_slug = $form['arm_form_id'];
                                    $shortcode_text[] = "id='$form_slug'";
                                    $shortcode_text[] = "id=$form_slug";
                                    $shortcode_text[] = 'id="' . $form_slug . '"';
                                    if ($shortcode_type == 'registration') {
                                        $shortcode_text[] = 'arm_form_registration="' . $form_slug . '"';
                                    } else if ($shortcode_type == 'login') {
                                        $shortcode_text[] = 'arm_form_login="' . $form_slug . '"';
                                    } else if ($shortcode_type == 'change_password') {
                                        $shortcode_text[] = 'arm_form_change_password="' . $form_slug . '"';
                                    } else if ($shortcode_type == 'forgot_password') {
                                        $shortcode_text[] = 'arm_form_forgot_password="' . $form_slug . '"';
                                    }
                                }
                                $is_exist = $this->arm_find_match_func($shortcode_text, $post_content);
                            }
                        }
                        /* Check Membership Setup Wizard Shortcode */
                        if ($shortcode_type == 'registration' && !$is_exist) {
                            $is_exist = $this->arm_find_match_shortcode_func('arm_setup', $post_content);
                            if (!$is_exist) {
                                $is_exist = apply_filters('armember_cs_check_shortcode_in_page', $is_exist, 'cs_armember_cs', $post_content);
                            }
                        }
                        break;
                    case 'edit_profile':
                        $is_exist = $this->arm_find_match_shortcode_func('arm_edit_profile', $post_content);
                        $is_exist_profile = $this->arm_find_match_shortcode_func('arm_profile_detail', $post_content);
                        $is_exist = (empty($is_exist) && !empty($is_exist_profile)) ? $is_exist_profile : $is_exist;
                        if (!$is_exist && !$is_exist_profile) {
                            $is_exist = apply_filters('armember_cs_check_shortcode_in_page', $is_exist, 'cs_armember_cs', $post_content);
                        }
                        break;
                    case 'members_directory':
                        $is_exist = $this->arm_find_match_shortcode_func('arm_template', $post_content);
                        break;
                    case 'arm_setup':
                        $is_exist = $this->arm_find_match_shortcode_func('arm_setup', $post_content);
                        break;
                    default :
                        $is_exist = apply_filters('arm_shortcode_exist_in_page', $is_exist, $post_content);
                        break;
                }
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_shortcode_exist_in_page') {
                echo json_encode(array('status' => $is_exist));
                exit;
            } else {
                return $is_exist;
            }
        }

        function arm_find_match_shortcode_func($key = '', $string = '') {
            $matched = false;
            $pattern = '\[' . $key . '(.*?)\]';
            if (!empty($key) && !empty($string)) {
                if (preg_match_all('/' . $pattern . '/s', $string, $matches) > 0) {
                    $matched = true;
                }
            }
            return $matched;
        }

        function arm_find_match_func($key = array(), $string = '') {
            if (!empty($key) && !empty($string)) {
                foreach ($key as $val) {
                    if (preg_match_all('/' . $val . '/s', $string, $matches) > 0) {
                        return true;
                    }
                }
               
            }
            return false;
        }
        
        function arm_find_registration_match_func($key = array(), $string = '') {
            if (!empty($key) && !empty($string)) {
                foreach ($key as $val) {
                    if (preg_match_all('/' . $val . '/s', $string, $matches) > 0) {
                        
                        $val = preg_replace('/[a-z=\'\"]/','',$val);
                        return $val;
                    }
                }
            }
            return false;
        }

        /**
         * Parse shortcodes in Feed Post Excerpt
         */
        function arm_filter_the_excerpt($content) {
            $isARMShortcode = $this->arm_find_match_shortcode_func('arm_', $content);
            $isARMIFShortcode = $this->arm_find_match_shortcode_func('armif', $content);
            if ($isARMShortcode || $isARMIFShortcode) {
                $content = do_shortcode($content);
            }
            return $content;
        }

        function arm_get_all_roles() {
            $allRoles = array();
            if (!function_exists('get_editable_roles') && file_exists(ABSPATH . '/wp-admin/includes/user.php')) {
                require_once(ABSPATH . '/wp-admin/includes/user.php');
            }
            global $wp_roles;
            $roles = get_editable_roles();
            if (!empty($roles)) {
                unset($roles['administrator']);
                foreach ($roles as $key => $role) {
                    $allRoles[$key] = $role['name'];
                }
            }


            return $allRoles;
        }

        function arm_get_all_roles_for_badges() {
            $allRoles = array();
            if (!function_exists('get_editable_roles') && file_exists(ABSPATH . '/wp-admin/includes/user.php')) {
                require_once(ABSPATH . '/wp-admin/includes/user.php');
            }
            global $wp_roles;
            $roles = get_editable_roles();
            if (!empty($roles)) {
                unset($roles['administrator']);
                foreach ($roles as $key => $role) {
                    $allRoles[$key] = $role['name'];
                }
            }

            if (is_plugin_active('bbpress/bbpress.php')) {

                if (function_exists('bbp_get_dynamic_roles')) {
                    foreach (bbp_get_dynamic_roles() as $role => $details) {
                        $allRoles[$role] = $details['name'];
                    }
                }
            }

            return $allRoles;
        }

        function arm_get_permalink($slug = '', $id = 0) {
            global $wp, $wpdb, $ARMember;
            $link = ARM_HOME_URL;
            if (!empty($slug) && $slug != '') {
                $object = $wpdb->get_results("SELECT `ID` FROM " . $wpdb->posts . " WHERE `post_name`='" . $slug . "'");
                if (!empty($object)) {
                    $link = get_permalink($object[0]->ID);
                }
            } elseif (!empty($id) && $id != 0) {
                $link = get_permalink($id);
            }
            return $link;
        }

        function arm_get_user_profile_url($userid = 0, $show_admin_users = 0) {
            global $wp, $wpdb, $ARMember, $arm_social_feature;
            if ($show_admin_users == 0) {
                if (user_can($userid, 'administrator')) {
                    return '#';
                }
            }
            $profileUrl = ARM_HOME_URL;
            if ($arm_social_feature->isSocialFeature) {
                if (isset($this->profile_url) && !empty($this->profile_url)) {
                    $profileUrl = $this->profile_url;
                } else {
                    $profile_page_id = isset($this->global_settings['member_profile_page_id']) ? $this->global_settings['member_profile_page_id'] : 0;
                    $profile_page_url = get_permalink($profile_page_id);
                    $profileUrl = (!empty($profile_page_url)) ? $profile_page_url : $profileUrl;
                    $this->profile_url = $profileUrl;
                }
                if (!empty($userid) && $userid != 0) {
                    $permalinkBase = isset($this->global_settings['profile_permalink_base']) ? $this->global_settings['profile_permalink_base'] : 'user_login';
                    $userBase = $userid;
                    if ($permalinkBase == 'user_login') {
                        $userInfo = get_userdata($userid);
                        $userBase = $userInfo->user_login;
                    }
                    if (get_option('permalink_structure')) {
                        $profileUrl = trailingslashit(untrailingslashit($profileUrl));
                        $profileUrl = $profileUrl . $userBase . '/';
                    } else {
                        $profileUrl = $this->add_query_arg('arm_user', $userBase, $profileUrl);
                    }
                }
            } else {
                if (isset($this->global_settings['edit_profile_page_id']) && $this->global_settings['edit_profile_page_id'] != 0) {
                    $profileUrl = get_permalink($this->global_settings['edit_profile_page_id']);
                }
            }
            $profileUrl = apply_filters('arm_after_get_user_profile_url', $profileUrl, $userid);
            return $profileUrl;
        }

        function arm_user_query_vars($public_query_vars) {
            $public_query_vars[] = 'arm_user';
            return $public_query_vars;
        }

        function arm_user_rewrite_rules() {
            global $wp, $wpdb, $wp_rewrite, $ARMember;
            $allGlobalSettings = $this->arm_get_all_global_settings(TRUE);
            if (isset($allGlobalSettings['member_profile_page_id']) && $allGlobalSettings['member_profile_page_id'] != 0) {
                $profile_page_id = $allGlobalSettings['member_profile_page_id'];
                $profilePage = get_post($profile_page_id);
                if (isset($profilePage->post_name)) {
                    $profileSlug = $profilePage->post_name;
                    add_rewrite_rule($profileSlug . '/([^/]+)/?$', 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]', 'top');
                }
            }
        }

        function arm_generate_rewrite_rules( $wp_rewrite ) {
            global $wp, $wpdb, $wp_rewrite, $ARMember;
            $allGlobalSettings = $this->arm_get_all_global_settings(TRUE);
            if (isset($allGlobalSettings['member_profile_page_id']) && $allGlobalSettings['member_profile_page_id'] != 0) {
                $profile_page_id = $allGlobalSettings['member_profile_page_id'];
                $profilePage = get_post($profile_page_id);
                if (isset($profilePage->post_name)) {
                    $profileSlug = $profilePage->post_name;
                    //add_rewrite_rule($profileSlug . '/([^/]+)/?$', 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]', 'top');
                    $feed_rules = array(
                      $profileSlug.'/([^/]+)/?$' => 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]',
                    );
                    
                    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
                }
            }
            return $wp_rewrite->rules;
        }

        /**
         * Create Pagination Links
         * @param Int $total Total Number Of Records
         * @param Int $per_page Number Of Records Per Page
         */
        function arm_get_paging_links($current = 1, $total = 10, $per_page = 10, $type = "") {
            global $wp, $wp_rewrite;
            $return_links = '';
            $current = (!empty($current) && $current != 0) ? $current : 1;
            $total_links = ceil($total / $per_page);
            /* Don't print empty markup if there's only one page. */
            if ($total_links < 1) {
                return;
            }
            $end_size = 1;
            $mid_size = 1;
            $page_links = array();
            $dots = false;
            if ($current && 1 < $current) {
                $page_links[] = '<a class="arm_prev arm_page_numbers" href="javascript:void(0)" data-page="' . ($current - 1) . '" data-per_page="' . $per_page . '"></a>';
            } else {
                $page_links[] = '<a class="arm_prev current arm_page_numbers" href="javascript:void(0)" data-per_page="' . $per_page . '"></a>';
            }
            for ($n = 1; $n <= $total_links; $n++) {
                if ($n == $current) {
                    $page_links[] = '<a class="current arm_page_numbers" href="javascript:void(0)" data-page="' . $current . '" data-per_page="' . $per_page . '">' . number_format_i18n($n) . '</a>';
                    $dots = true;
                } else {
                    if ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total_links - $end_size) {
                        $page_links[] = '<a class="arm_page_numbers" href="javascript:void(0)" data-page="' . $n . '" data-per_page="' . $per_page . '">' . number_format_i18n($n) . '</a>';
                        $dots = true;
                    } elseif ($dots) {
                        $page_links[] = '<span class="arm_page_numbers dots">&hellip;</span>';
                        $dots = false;
                    }
                }
            }
            if ($current && ( $current < $total_links || -1 == $total_links )) {
                $page_links[] = '<a class="arm_next arm_page_numbers" href="javascript:void(0)" data-page="' . ($current + 1) . '" data-per_page="' . $per_page . '"></a>';
            } else {
                $page_links[] = '<a class="arm_next current arm_page_numbers" href="javascript:void(0)" data-per_page="' . $per_page . '"></a>';
            }
            if (!empty($page_links)) {

                $startNum = (!empty($current) && $current > 1) ? (($current - 1) * $per_page) + 1 : 1;
                $endNum = $current * $per_page;
                $endNum = ($endNum > $total) ? $total : $endNum;
                /* Join Links */
                $links = join("\n", $page_links);
                $return_links = '<div class="arm_paging_wrapper arm_paging_wrapper_' . $type . '">';
                $return_links .= '<div class="arm_paging_info">';
                switch ($type) {
                    case 'activity':
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' ' . __('to', 'ARMember') . ' ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('total activities', 'ARMember');
                        break;
                    case 'membership_history':
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' ' . __('to', 'ARMember') . ' ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('total records', 'ARMember');
                        break;
                    case 'directory':
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' - ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('members', 'ARMember');
                        break;
                    case 'transaction':
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' - ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('transactions', 'ARMember');
                        break;
                    case 'current_membership':
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' - ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('Membership', 'ARMember');
                        break;
                    default:
                        $return_links .= __('Showing', 'ARMember') . ' ' . $startNum . ' - ' . $endNum . ' ' . __('of', 'ARMember') . ' ' . $total . ' ' . __('records', 'ARMember');
                        break;
                }
                $return_links .= '</div>';
                $return_links .= '<div class="arm_paging_links">' . $links . '</div>';
                $return_links .= '</div>';
            }
            return $return_links;
        }

        function arm_filter_get_avatar($avatar, $id_or_email, $size, $default, $alt = '') {
            global $pagenow;
            /* Do not filter if inside WordPress options page OR `enable_gravatar` set to '0' */
            if ('options-discussion.php' == $pagenow) {
                return $avatar;
            }
            $user_avatar = $this->arm_get_user_avatar($id_or_email, $size, $default, $alt);
            if (!empty($user_avatar)) {
                $avatar = $user_avatar;
            } else {
                if ($this->global_settings['enable_gravatar'] == '0') {
                    $avatar = "<img src='" . MEMBERSHIP_IMAGES_URL . "/avatar_placeholder.png' class='avatar arm_grid_avatar arm-avatar avatar-{$size}' width='{$size}' />";
                } else {
                    $avatar = str_replace('avatar-' . $size, 'arm_grid_avatar arm-avatar avatar-' . $size, $avatar);
                }
            }
            return apply_filters('arm_change_user_avatar', $avatar, $id_or_email, $size, $default, $alt);
        }

        function arm_filter_get_avatar_url($url, $id_or_email, $args){
            if (is_numeric($id_or_email)) {
                $user_id = (int) $id_or_email;
            } elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
                $user_id = $user->ID;
            } elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
                $user_id = (int) $id_or_email->user_id;
            } else {
                $user_id = 0;
            }
	    
	    if(!empty($user_id))
	    {
		$avatar_url = get_user_meta($user_id, 'avatar', true);
		if (!empty($avatar_url) && file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($avatar_url))) {
		return $avatar_url;
		}
	    }
            return $url;
        }

        function arm_get_avatar($id_or_email, $size = '96', $default = '', $alt = false) {
            global $wp, $wpdb, $ARMember;
            $user_avatar = $this->arm_get_user_avatar($id_or_email, $size, $default, $alt);
            if ($this->global_settings['enable_gravatar'] == '1' && !empty($user_avatar)) {
                $avatar = apply_filters('arm_change_user_avatar', $user_avatar, $id_or_email, $size, $default, $alt);
            } else {
                $avatar = get_avatar($id_or_email, $size, $default, $alt);
            }
            return $avatar;
        }

        function arm_get_user_avatar($id_or_email, $size = '96', $default = '', $alt = false) {
            global $wp, $wpdb, $ARMember;
            $safe_alt = (false === $alt) ? '' : esc_attr($alt);
            if (is_numeric($id_or_email)) {
                $user_id = (int) $id_or_email;
            } elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
                $user_id = $user->ID;
            } elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
                $user_id = (int) $id_or_email->user_id;
            } else {
                $user_id = 0;
            }
            $user = get_user_by('id', $user_id);
            $avatar_url = get_user_meta($user_id, 'avatar', true);
            $avatar_w_h_class = '';
            if (!empty($avatar_url) && file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($avatar_url))) {
                $avatar_detail = @getimagesize(MEMBERSHIP_UPLOAD_DIR . '/' . basename($avatar_url));
                if ($size > $avatar_detail[0]) {
                    $avatar_w_h_class = ' arm_avatar_small_width';
                }
                if ($size > $avatar_detail[1]) {
                    $avatar_w_h_class .= ' arm_avatar_small_height';
                }
            }
            $avatar_class = 'arm_grid_avatar gravatar avatar arm-avatar photo avatar-' . $size . ' ' . $avatar_w_h_class;
            if (empty($safe_alt) && $user) {
                $safe_alt = __('Profile photo of', 'ARMember') . $user->user_login;
            }
            
            if (!empty($avatar_url) && file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($avatar_url))) {
                $avatar_filesize =  @filesize(MEMBERSHIP_UPLOAD_DIR . '/' . basename($avatar_url));
                if($avatar_filesize>0)
                {
                    if (file_exists(strstr($avatar_url, "//"))) {
                        $avatar_url = strstr($avatar_url, "//");
                    } else if (file_exists($avatar_url)) {
                        $avatar_url = $avatar_url;
                    } else {
                        $avatar_url = $avatar_url;
                    }
                    $avatar = '<img src="' . ($avatar_url) . '" class="' . $avatar_class . '" width="' . $size . '" height="' . $size . '" alt="' . $safe_alt . '"/>';
                }
                else {
                    $avatar = '';
                }
            } else {
                $avatar = '';
            }
            return $avatar;
        }

        function arm_default_avatar_url($default = '') {
            global $wp, $wpdb, $ARMember;
            $avatar_default = get_option('avatar_default');
            $default = (!empty($avatar_default)) ? $avatar_default : 'mystery';
            if (is_ssl()) {
                $host = 'https://secure.gravatar.com';
            } else {
                $host = 'http://0.gravatar.com';
            }
            if ('mystery' == $default) {
                $default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}";
            } elseif ('blank' == $default) {
                $default = includes_url('images/blank.gif');
            } elseif ('gravatar_default' == $default) {
                $default = "$host/avatar/?s={$size}";
            } elseif (strpos($default, 'http://') === 0) {
                $default = add_query_arg('s', $size, $default);
            }
            return esc_url($default);
        }

        /**
         * Get Single Global Setting by option name
         */
        function arm_get_single_global_settings($option_name, $default = '') {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $all_settings = $this->global_settings;
            $option_value = $default;
            if (!empty($option_name)) {
                if (isset($all_settings[$option_name]) && !empty($all_settings[$option_name])) {
                    $option_value = $all_settings[$option_name];
                } elseif ($option_name == 'page_settings') {
                    $defaultGS = $this->arm_default_global_settings();
                    $option_value = shortcode_atts($defaultGS['page_settings'], $all_settings);
                }
            }
            return $option_value;
        }

        function arm_get_all_global_settings($merge = FALSE) {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $default_global_settings = $this->arm_default_global_settings();


            $global_settings = get_option('arm_global_settings', $default_global_settings);
           
         
            $all_global_settings = maybe_unserialize($global_settings);
            $all_global_settings = apply_filters('arm_get_all_global_settings', $all_global_settings);
            if ($merge) {
                $all_global_settings['general_settings'] = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : $default_global_settings['general_settings'];
                $all_global_settings['page_settings'] = isset($all_global_settings['page_settings']) ? $all_global_settings['page_settings'] : $default_global_settings['page_settings'];
                $arm_merge_global_settings = array_merge($all_global_settings['general_settings'], $all_global_settings['page_settings']);
                return $arm_merge_global_settings;
            }
            return $all_global_settings;
        }

        function arm_get_all_block_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $default_block_settings = array(
                'failed_login_lockdown' => 1,
                'remained_login_attempts' => 1,
                'track_login_history' => 1,
                'max_login_retries' => 5,
                'temporary_lockdown_duration' => 10,
                'permanent_login_retries' => 15,
                'permanent_lockdown_duration' => 24,
                'arm_block_ips' => '',
                'arm_block_ips_msg' => __('Account Blocked: Your IP is blocked. Please contact system administrator.', 'ARMember'),
                'arm_block_usernames' => '',
                'arm_block_usernames_msg' => __('Username should not contain bad words.', 'ARMember'),
                'arm_block_emails' => '',
                'arm_block_emails_msg' => __('Email Address should not contain bad words.', 'ARMember'),
                'arm_block_urls' => '',
                'arm_block_urls_option' => 'message',
                'arm_block_urls_option_message' => __('Account Blocked: Your account is blocked. Please contact system administrator.', 'ARMember'),
                'arm_block_urls_option_redirect' => site_url(),
            );
            $block_settings = get_option('arm_block_settings', $default_block_settings);
            $all_block_settings = maybe_unserialize($block_settings);
            if(!is_array($all_block_settings)) {
                $all_block_settings = array();
            }
            $all_block_settings['arm_block_ips_msg'] = !empty($all_block_settings['arm_block_ips_msg']) ? stripslashes($all_block_settings['arm_block_ips_msg']) : '';
            $all_block_settings['arm_block_usernames_msg'] = !empty($all_block_settings['arm_block_usernames_msg']) ? stripslashes($all_block_settings['arm_block_usernames_msg']) : '';
            $all_block_settings['arm_block_emails_msg'] = !empty($all_block_settings['arm_block_emails_msg']) ? stripslashes($all_block_settings['arm_block_emails_msg']) : '';
            $all_block_settings['arm_block_urls_option_message'] = !empty($all_block_settings['arm_block_urls_option_message']) ? stripslashes($all_block_settings['arm_block_urls_option_message']) : '';
            $all_block_settings = apply_filters('arm_get_all_block_settings', $all_block_settings);
            return $all_block_settings;
        }

        function arm_get_parsed_block_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $parsed_block_settings = $this->arm_get_all_block_settings();
            if(is_array($parsed_block_settings))
            {
                foreach ($parsed_block_settings as $type => $val) {
                    if (!empty($val) && in_array($type, array('arm_block_ips', 'arm_block_usernames', 'arm_block_emails', 'arm_block_urls', 'arm_conditionally_block_urls_options'))) {
                        if($type == 'arm_conditionally_block_urls_options') {
                            $new_val = $val;
                        }else{
                            $new_val = array_map('strtolower', array_map('trim', explode("\n", $val)));
                        }
                        $parsed_block_settings[$type] = $new_val;
                    }
                }
            }
            $parsed_block_settings = apply_filters('arm_get_parsed_block_settings', $parsed_block_settings);
            return $parsed_block_settings;
        }

        function arm_get_all_common_message_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $arm_default_common_messages = $this->arm_default_common_messages();
            $common_message_settings = get_option('arm_common_message_settings', $arm_default_common_messages);
            $all_common_message_settings = maybe_unserialize($common_message_settings);
            $all_common_message_settings = (!empty($all_common_message_settings)) ? $all_common_message_settings : array();
            if (!empty($all_common_message_settings)) {
                foreach ($all_common_message_settings as $key => $val) {
                    $all_common_message_settings[$key] = stripslashes($val);
                }
            }

            $all_common_message_settings = apply_filters('arm_get_all_common_message_settings', $all_common_message_settings);
            return $all_common_message_settings;
        }

        function arm_update_all_settings() {
            global $wpdb, $wp_rewrite, $ARMember, $arm_members_class, $arm_member_forms, $arm_email_settings, $arm_payment_gateways, $arm_access_rules, $arm_crons, $arm_capabilities_global;
            $response = array('type' => 'error', 'msg' => __('There is a error while updating settings, please try again.', 'ARMember'));
            $is_new_wp_admin_path = FALSE;
            $default_global_settings = $this->arm_default_global_settings();
            $old_global_settings = $this->arm_get_all_global_settings();
           
            
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_global_settings') {

                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

                $save_all = isset($_POST['save_all']) ? $_POST['save_all'] : '';
             
                $_POST['arm_general_settings']['hide_register_link'] = isset($_POST['arm_general_settings']['hide_register_link']) ? intval($_POST['arm_general_settings']['hide_register_link']) : 0;
                $_POST['arm_general_settings']['enable_gravatar'] = isset($_POST['arm_general_settings']['enable_gravatar']) ? intval($_POST['arm_general_settings']['enable_gravatar']) : 0;
                $_POST['arm_general_settings']['enable_crop'] = isset($_POST['arm_general_settings']['enable_crop']) ? intval($_POST['arm_general_settings']['enable_crop']) : 0;
                $_POST['arm_general_settings']['spam_protection'] = isset($_POST['arm_general_settings']['spam_protection']) ? intval($_POST['arm_general_settings']['spam_protection']) : 0;
                $_POST['arm_general_settings']['enable_tax'] = isset($_POST['arm_general_settings']['enable_tax']) ? intval($_POST['arm_general_settings']['enable_tax']) : 0;

                $_POST['arm_general_settings']['tax_type'] = isset($_POST['arm_general_settings']['tax_type']) ? sanitize_text_field($_POST['arm_general_settings']['tax_type']) : 'common_tax';

                $_POST['arm_general_settings']['country_tax_field'] = isset($_POST['arm_general_settings']['country_tax_field']) ? sanitize_text_field($_POST['arm_general_settings']['country_tax_field']) : '';

                $_POST['arm_general_settings']["arm_tax_country_name"] = isset($_POST['arm_general_settings']['arm_tax_country_name']) ? maybe_serialize($_POST['arm_general_settings']['arm_tax_country_name']) : '';

                $_POST['arm_general_settings']["arm_country_tax_val"] = isset($_POST['arm_general_settings']['arm_country_tax_val']) ? maybe_serialize($_POST['arm_general_settings']['arm_country_tax_val']) : '';

                $_POST['arm_general_settings']["arm_country_tax_default_val"] = isset($_POST['arm_general_settings']['arm_country_tax_default_val']) ? $_POST['arm_general_settings']['arm_country_tax_default_val'] : 0;

                $_POST['arm_general_settings']['invc_pre_sfx_mode'] = isset($_POST['arm_general_settings']['invc_pre_sfx_mode']) ? intval($_POST['arm_general_settings']['invc_pre_sfx_mode']) : 0;

                $_POST['arm_general_settings']['invc_prefix_val'] = isset($_POST['arm_general_settings']['invc_prefix_val']) ? sanitize_text_field($_POST['arm_general_settings']['invc_prefix_val']) : '#';

                $_POST['arm_general_settings']['invc_suffix_val'] = isset($_POST['arm_general_settings']['invc_suffix_val']) ? sanitize_text_field($_POST['arm_general_settings']['invc_suffix_val']) : '';

                $_POST['arm_general_settings']['invc_min_digit'] = isset($_POST['arm_general_settings']['invc_min_digit']) ? intval($_POST['arm_general_settings']['invc_min_digit']) : 0;

                $_POST['arm_general_settings']['arm_recaptcha_site_key'] = isset($_POST['arm_general_settings']['arm_recaptcha_site_key']) ? sanitize_text_field($_POST['arm_general_settings']['arm_recaptcha_site_key']) : '';

                $_POST['arm_general_settings']['arm_recaptcha_private_key'] = isset($_POST['arm_general_settings']['arm_recaptcha_private_key']) ? sanitize_text_field($_POST['arm_general_settings']['arm_recaptcha_private_key']) : '';

                $_POST['arm_general_settings']['arm_recaptcha_theme'] = isset($_POST['arm_general_settings']['arm_recaptcha_theme']) ? sanitize_text_field($_POST['arm_general_settings']['arm_recaptcha_theme']) : '';
                
                $_POST['arm_general_settings']['arm_recaptcha_lang'] = isset($_POST['arm_general_settings']['arm_recaptcha_lang']) ? sanitize_text_field($_POST['arm_general_settings']['arm_recaptcha_lang']) : '';
                
                $arm_general_settings = isset($_POST['arm_general_settings']) ? $_POST['arm_general_settings'] : array();
                
                $new_global_settings['general_settings'] = shortcode_atts($default_global_settings['general_settings'], $arm_general_settings);
                if ($new_global_settings['general_settings']['user_register_verification'] != 'auto') {
                    $new_global_settings['general_settings']['arm_new_signup_status'] = 3;
                }
                /* ===========================/.Rename Admin Folder Options./=========================== */
                if (!trim($this->global_settings['new_wp_admin_path'], ' /') || trim($this->global_settings['new_wp_admin_path'], ' /') == 'wp-admin') {
                    $current_wp_admin_path = 'wp-admin';
                } else {
                    $current_wp_admin_path = trim($this->global_settings['new_wp_admin_path'], ' /');
                }
                $rename_wp_admin = (isset($new_global_settings['general_settings']['rename_wp_admin'])) ? $new_global_settings['general_settings']['rename_wp_admin'] : '';
                $rename_wp_admin = empty($rename_wp_admin) ? 0 : 1;
                $new_wp_admin_path_input = (isset($new_global_settings['general_settings']['new_wp_admin_path'])) ? $new_global_settings['general_settings']['new_wp_admin_path'] : '';

                $flush_rewrite_rules = false;

                $all_saved_global_settings = maybe_unserialize(get_option('arm_global_settings'));


                $saved_rename_wp = $all_saved_global_settings['general_settings']['rename_wp_admin'];
                if (empty($saved_rename_wp)) {
                    $saved_rename_wp = 0;
                } else {
                    $saved_rename_wp = 1;
                }

                $logout = true;

                $home_root = parse_url(home_url());
                if (isset($home_root['path']))
                    $home_root = trailingslashit($home_root['path']);
                else
                    $home_root = '/';

                global $wp_rewrite;
                $config_error = false;
                $htaccess_notice = false;
                $rewritecode = '';
                $rewrite_htaccess_notice = $rewrite_config_notice = "";
                $saved_admin_path = trim($all_saved_global_settings['general_settings']['new_wp_admin_path'], '/');
                if (empty($saved_admin_path)) {
                    $saved_admin_path = 'wp-admin';
                }

                if (!trim($new_wp_admin_path_input, ' /') || trim($new_wp_admin_path_input, ' /') == 'wp-admin') {
                    $new_wp_admin_path = 'wp-admin';
                } else {
                    $new_wp_admin_path = trim($new_wp_admin_path_input, '/');
                }

                $new_wp_admin_path = !empty($new_wp_admin_path) ? $new_wp_admin_path : 'wp-admin';
                $arm_rename_wp = new ARM_rename_wp();
                $arm_rename_wp->new_wp_admin_name = $new_wp_admin_path;
                $arm_rename_wp->enable_rename_wp = $rename_wp_admin;
                $arm_rename_wp->arm_replace = array();
                $arm_rename_wp->armBuildRedirect();
                $rewrite_notice = '';

                $new_global_settings['general_settings']['new_wp_admin_path'] = $saved_admin_path;
                $new_global_settings['general_settings']['rename_wp_admin'] = $saved_rename_wp;
                $redirect_to = get_site_url($GLOBALS['blog_id'], $saved_admin_path . '/', 'admin') . 'admin.php?page=arm_general_settings';
                $home_path = $this->arm_get_home_path();
                if (!empty($rename_wp_admin) && $rename_wp_admin == 1) {
                    if ($save_all == '') {
                        if ($saved_admin_path != $new_wp_admin_path) {
                            if (!file_exists($home_path . '.htaccess') || !is_writable($home_path . '.htaccess') || !file_exists($home_path . 'wp-config.php') || !is_writable($home_path . 'wp-config.php')) {

                                $rewrites = array();
                                if (!empty($arm_rename_wp->arm_replace)) {
                                    foreach ($arm_rename_wp->arm_replace['to'] as $key => $replace) {
                                        if ($arm_rename_wp->arm_replace['rewrite'][$key]) {
                                            $rewrites[] = array(
                                                'from' => $arm_rename_wp->arm_replace['to'][$key] . '(.*)',
                                                'to' => $arm_rename_wp->arm_replace['from'][$key] . '$' . (substr_count($arm_rename_wp->arm_replace['to'][$key], '(') + 1)
                                            );
                                        }
                                    }
                                }
                                $htaccess_rewritecode = '';
                                foreach ($rewrites as $rewrite) {

                                    if (strpos($rewrite['to'], 'index.php') === false) {
                                        $htaccess_rewritecode .= 'RewriteRule ^' . $rewrite['from'] . ' ' . $home_root . $rewrite['to'] . " [QSA,L]<br />";
                                    }
                                }

                                $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by adding following line before \'RewriteCond %{REQUEST_FILENAME} !-f\': <br /><code>' . $htaccess_rewritecode . '</code><br/>';

                                $config_rewritecode = "define('ADMIN_COOKIE_PATH','" . rtrim(wp_make_link_relative(network_site_url($new_wp_admin_path)), '/') . "');";
                                $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by adding following line <code>' . $config_rewritecode . '</code>';
                                
                                $response['type'] = 'notice';
                                $response['notice_msg'] = $rewrite_notice;
                                $logout = false;
                                echo json_encode($response);
                                die();
                            }
                        }


                        if (!empty($rename_wp_admin) && $rename_wp_admin == 1) {
                            if ($saved_admin_path != $new_wp_admin_path) {
                                if (!$arm_rename_wp->arm_rewrite_rules($wp_rewrite)) {
                                    $htaccess_notice = true;
                                    $rewrites = array();
                                    if (!empty($arm_rename_wp->arm_replace)) {
                                        foreach ($arm_rename_wp->arm_replace['to'] as $key => $replace) {
                                            if ($arm_rename_wp->arm_replace['rewrite'][$key]) {
                                                $rewrites[] = array(
                                                    'from' => $arm_rename_wp->arm_replace['to'][$key] . '(.*)',
                                                    'to' => $arm_rename_wp->arm_replace['from'][$key] . '$' . (substr_count($arm_rename_wp->arm_replace['to'][$key], '(') + 1)
                                                );
                                            }
                                        }
                                    }
                                    $htaccess_rewritecode = '';
                                    foreach ($rewrites as $rewrite) {

                                        if (strpos($rewrite['to'], 'index.php') === false) {
                                            $htaccess_rewritecode .= 'RewriteRule ^' . $rewrite['from'] . ' ' . $home_root . $rewrite['to'] . " [QSA,L]<br />";
                                        }
                                    }

                                    $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by adding following line before \'RewriteCond %{REQUEST_FILENAME} !-f\': <br /><code>' . $htaccess_rewritecode . '</code><br/>';
                                }

                                if (!$this->rewrite_config_file($new_wp_admin_path)) {

                                    $config_error = true;
                                    $config_rewritecode = "define('ADMIN_COOKIE_PATH','" . rtrim(wp_make_link_relative(network_site_url($new_wp_admin_path)), '/') . "');";
                                    $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by adding following line <code>' . $config_rewritecode . '</code>';
                                }
                                if ($config_error == true || $htaccess_notice == true) {
                                    $response['type'] = 'notice';
                                    $response['notice_msg'] = $rewrite_notice;
                                    $logout = false;
                                    echo json_encode($response);
                                    die();
                                }
                            }
                        }
                    }

                    if ($save_all != 'cancel_all') {

                        $new_global_settings['general_settings']['new_wp_admin_path'] = $new_wp_admin_path;
                        $new_global_settings['general_settings']['rename_wp_admin'] = $rename_wp_admin;
                        $arm_rename_wp->enable_rename_wp = 1;
                        $arm_rename_wp->new_wp_admin_name = $new_wp_admin_path;
                        if ($saved_admin_path == $new_wp_admin_path) {
                            $logout = false;
                        }
                    } else {
                        $arm_rename_wp->enable_rename_wp = $saved_rename_wp;
                        $arm_rename_wp->new_wp_admin_name = $saved_admin_path;
                        $logout = false;
                    }
                } else {

                    if ($save_all == '') {
                        if ($new_wp_admin_path != 'wp-admin') {
                            if (!file_exists($home_path . '.htaccess') || !is_writable($home_path . '.htaccess') || !file_exists($home_path . 'wp-config.php') || !is_writable($home_path . 'wp-config.php')) {

                                $rewrites = array();
                                if (!empty($arm_rename_wp->arm_replace)) {
                                    foreach ($arm_rename_wp->arm_replace['to'] as $key => $replace) {
                                        if ($arm_rename_wp->arm_replace['rewrite'][$key]) {
                                            $rewrites[] = array(
                                                'from' => $arm_rename_wp->arm_replace['to'][$key] . '(.*)',
                                                'to' => $arm_rename_wp->arm_replace['from'][$key] . '$' . (substr_count($arm_rename_wp->arm_replace['to'][$key], '(') + 1)
                                            );
                                        }
                                    }
                                }
                                $htaccess_rewritecode = '';
                                foreach ($rewrites as $rewrite) {

                                    if (strpos($rewrite['to'], 'index.php') === false) {
                                        $htaccess_rewritecode .= 'RewriteRule ^' . $rewrite['from'] . ' ' . $home_root . $rewrite['to'] . " [QSA,L]<br />";
                                    }
                                }


                                $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by removing following line before \'RewriteCond %{REQUEST_FILENAME} !-f\': <br /><code>' . $htaccess_rewritecode . '</code><br/>';

                                $config_rewritecode = "define('ADMIN_COOKIE_PATH','" . rtrim(wp_make_link_relative(network_site_url($new_wp_admin_path)), '/') . "');";
                                $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by deleting following line <code>' . $config_rewritecode . '</code>';


                                $response['type'] = 'notice';
                                $response['notice_msg'] = $rewrite_notice;
                                $logout = false;
                                echo json_encode($response);
                                die();
                            }
                        }



                        if ($saved_admin_path != 'wp-admin') {
                            require_once ABSPATH . 'wp-admin/includes/misc.php';
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                            $removeTag = $_POST['arm_general_settings']['new_wp_admin_path'] . '/(.*)';
                            $wp_rewrite->remove_rewrite_tag($removeTag);
                            $rewrite_notice = '';
                            if (!function_exists('save_mod_rewrite_rules')) {
                                $htaccess_notice = true;
                                $rewritecode = "RewriteRule ^{$_POST['arm_general_settings']['new_wp_admin_path']}/(.*) {$home_root}wp-admin/$1 [QSA,L]";
                                $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by remove following line from your .htaccess file <br /><code>' . $rewritecode . '</code>';
                            } else {
                                if (!save_mod_rewrite_rules()) {
                                    $htaccess_notice = true;
                                    $rewritecode = "RewriteRule ^{$_POST['arm_general_settings']['new_wp_admin_path']}/(.*) {$home_root}wp-admin/$1 [QSA,L]";
                                    $rewrite_notice .= '.htaccess file is not writable. You need to update your .htaccess file by remove following line from your .htaccess file <br /><code>' . $rewritecode . '</code>';
                                }
                            }
                            $redirect_to = apply_filters('admin_url', admin_url('admin.php?page=arm_general_settings'));
                            if (!$this->remove_config_file()) {
                                $config_error = true;
                                $rewritecode = "define('ADMIN_COOKIE_PATH','{$_POST['arm_general_settings']['new_wp_admin_path']}');";
                                $rewrite_notice .= 'wp-config.php file is not writable. You need to update your wp-config.php file by removing following line <code>' . $rewritecode . '</code>';
                            }

                            if ($htaccess_notice == true || $config_error == true) {
                                $response['type'] = 'notice';
                                $response['notice_msg'] = $rewrite_notice;
                                echo json_encode($response);
                                die();
                            }
                        }
                    }



                    if ($save_all != 'cancel_all') {


                        $new_global_settings['general_settings']['new_wp_admin_path'] = 'wp-admin';
                        $new_global_settings['general_settings']['rename_wp_admin'] = 0;
                        $arm_rename_wp->enable_rename_wp = 0;
                        $arm_rename_wp->new_wp_admin_name = 'wp-admin';
                        if ($saved_admin_path == 'wp-admin') {

                            $logout = false;
                        }
                    } else {
                        $arm_rename_wp->enable_rename_wp = $saved_rename_wp;
                        $arm_rename_wp->new_wp_admin_name = $saved_admin_path;
                        $logout = false;
                    }
                }
                /* ===========================/.End Rename Admin Folder Options./=========================== */
                if (!isset($new_global_settings['general_settings']['custom_currency']['status'])) {
                    $new_global_settings['general_settings']['custom_currency'] = array(
                        'status' => 0,
                        'symbol' => '',
                        'shortname' => '',
                        'place' => 'prefix',
                    );
                }


                //$new_global_settings['arm_specific_currency_position'] = (isset($_POST['arm_prefix_suffix_val']) && !empty($_POST['arm_prefix_suffix_val'])) ? $_POST['arm_prefix_suffix_val'] : 'suffix';


                $new_global_settings['page_settings'] = $old_global_settings['page_settings'];
                $new_global_settings['api_service'] = isset($old_global_settings['api_service']) ? $old_global_settings['api_service'] : array();
                
                $arm_exclude_role_for_hide_admin = ( isset($_POST['arm_general_settings']['arm_exclude_role_for_hide_admin']) && !empty($_POST['arm_general_settings']['arm_exclude_role_for_hide_admin']) )? implode(',',$_POST['arm_general_settings']['arm_exclude_role_for_hide_admin']) : '';
                $new_general_settings['arm_exclude_role_for_hide_admin'] = $arm_exclude_role_for_hide_admin;

                // set old global setting because its updated from other page
                $new_global_settings['general_settings']['arm_invoice_template'] = $old_global_settings['general_settings']['arm_invoice_template'];
                $new_global_settings['general_settings']['arm_exclude_role_for_restrict_admin'] = isset($old_global_settings['general_settings']['arm_exclude_role_for_restrict_admin']) ? $old_global_settings['general_settings']['arm_exclude_role_for_restrict_admin'] : '';
                $new_global_settings['general_settings']['restrict_admin_panel'] = isset($old_global_settings['general_settings']['restrict_admin_panel']) ? $old_global_settings['general_settings']['restrict_admin_panel'] : 0;
                $new_global_settings['general_settings']['hide_feed'] = isset($old_global_settings['general_settings']['hide_feed']) ? $old_global_settings['general_settings']['hide_feed'] : 0;
                $new_global_settings['general_settings']['restrict_site_access'] = isset($old_global_settings['general_settings']['restrict_site_access']) ? $old_global_settings['general_settings']['restrict_site_access'] : 0;
                $new_global_settings['general_settings']['arm_pay_per_post_buynow_var'] = isset($old_global_settings['general_settings']['arm_pay_per_post_buynow_var']) ? $old_global_settings['general_settings']['arm_pay_per_post_buynow_var'] : '';
                $new_global_settings['general_settings']['arm_pay_per_post_allow_fancy_url'] = isset($old_global_settings['general_settings']['arm_pay_per_post_allow_fancy_url']) ? $old_global_settings['general_settings']['arm_pay_per_post_allow_fancy_url'] : '';
                $new_global_settings['general_settings']['arm_pay_per_post_default_content'] = isset($old_global_settings['general_settings']['arm_pay_per_post_default_content']) ? $old_global_settings['general_settings']['arm_pay_per_post_default_content'] : '';
                $new_global_settings['page_settings']['guest_page_id'] = isset($old_global_settings['page_settings']['guest_page_id']) ? $old_global_settings['page_settings']['guest_page_id'] : 0;
                $new_global_settings['page_settings']['arm_access_page_for_restrict_site'] = isset($old_global_settings['page_settings']['arm_access_page_for_restrict_site']) ? $old_global_settings['page_settings']['arm_access_page_for_restrict_site'] : '';
                
                
                $new_global_settings = apply_filters('arm_before_update_global_settings', $new_global_settings, $_POST);

                /* -------- Update Email Schedular Start ------- */
                $arm_old_general_settings = $old_global_settings['general_settings'];
                $arm_old_email_schedular = isset($arm_old_general_settings['arm_email_schedular_time']) ? $arm_old_general_settings['arm_email_schedular_time'] : 0;

                if ($arm_old_email_schedular != $new_global_settings['general_settings']['arm_email_schedular_time']) {
                    $arm_all_crons = $arm_crons->arm_get_cron_hook_names();
                    
                 
                    foreach ($arm_all_crons as $arm_cron_hook_name) {
                        $arm_crons->arm_clear_cron($arm_cron_hook_name);
                    }
                }
                /* -------- Update Email Schedular End------- */              
                
                update_option('arm_global_settings', $new_global_settings);

                $arm_email_settings->arm_update_email_settings();
                $arm_payment_gateways->arm_update_payment_gate_status();
                $response = array('type' => 'success', 'msg' => __('Global Settings Saved Successfully.', 'ARMember'));
                if (isset($redirect_to) && $redirect_to != '') {
                    if (!$logout) {
                        $response['url'] = $redirect_to;
                    } else {
                        wp_destroy_current_session();
                        wp_clear_auth_cookie();
                        $response['url'] = wp_login_url();
                    }
                } else {
                    if ($htaccess_notice) {
                        $response['notice'] = true;
                        $response['notice_msg'] = $rewrite_htaccess_notice;
                    } else if ($config_error) {
                        $response['config_notice'] = true;
                        $response['config_notice_msg'] = $rewrite_config_notice;
                    }
                }
                update_option('arm_recaptcha_notice_flag','2');
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_page_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $default_global_settings = $this->arm_default_global_settings();
                $arm_page_settings = $_POST['arm_page_settings'];
                $old_page_settings = shortcode_atts($default_global_settings['page_settings'], $old_global_settings['page_settings']);
                $new_global_settings['page_settings'] = shortcode_atts($old_page_settings, $arm_page_settings);

                if(isset($_POST['arm_page_settings']['paid_post_page_id']) && !empty($_POST['arm_page_settings']['paid_post_page_id']))
                {
                    $new_global_settings['page_settings']['paid_post_page_id'] = $_POST['arm_page_settings']['paid_post_page_id'];
                }

                $new_global_settings['api_service'] = isset($old_global_settings['api_service']) ? $old_global_settings['api_service'] : array();
                $new_global_settings['general_settings'] = $old_global_settings['general_settings'];
                $new_global_settings = apply_filters('arm_before_update_page_settings', $new_global_settings, $_POST);
                update_option('arm_global_settings', $new_global_settings);
                $this->arm_user_rewrite_rules();
                $wp_rewrite->flush_rules(false);
                $response = array('type' => 'success', 'msg' => __('Page Settings Saved Successfully.', 'ARMember'));
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_block_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $post_block_settings = $_POST['arm_block_settings'];
                $post_block_settings['failed_login_lockdown'] = isset($post_block_settings['failed_login_lockdown']) ? intval($post_block_settings['failed_login_lockdown']) : 0;
                $post_block_settings['remained_login_attempts'] = isset($post_block_settings['remained_login_attempts']) ? intval($post_block_settings['remained_login_attempts']) : 0;
                $post_block_settings['track_login_history'] = isset($post_block_settings['track_login_history']) ? intval($post_block_settings['track_login_history']) : 0;
                $arm_block_ips = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $post_block_settings['arm_block_ips']))));
                $arm_block_usernames = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $post_block_settings['arm_block_usernames']))));
                $arm_block_emails = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $post_block_settings['arm_block_emails']))));
                $arm_block_urls = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $post_block_settings['arm_block_urls']))));
                $conditionally_block_urls = isset($post_block_settings['arm_conditionally_block_urls'])?$post_block_settings['arm_conditionally_block_urls']:0;
                if($conditionally_block_urls == 1){
                    $conditionally_block_urls_options = array();
                    $condition_count = 0;
                    foreach($post_block_settings['arm_conditionally_block_urls_options'] as $condition){
                        $condition_count++;
                        $conditionally_block_urls_options[$condition_count]['plan_id'] = $condition['plan_id'];
                        $arm_block_url = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $condition['arm_block_urls']))));
                        $conditionally_block_urls_options[$condition_count]['arm_block_urls'] = $arm_block_url;
                    }
                }
                
                $is_update = true;
                if ($is_update == true) {
                    $post_block_settings['arm_block_ips'] = $arm_block_ips;
                    $post_block_settings['arm_block_usernames'] = $arm_block_usernames;
                    $post_block_settings['arm_block_emails'] = $arm_block_emails;
                    $post_block_settings['arm_block_urls'] = $arm_block_urls;
                    if($conditionally_block_urls == 1) {
                        $post_block_settings['arm_conditionally_block_urls'] = $conditionally_block_urls;
                        $post_block_settings['arm_conditionally_block_urls_options'] = $conditionally_block_urls_options;
                    }
                    
                    $post_block_settings = apply_filters('arm_before_update_block_settings', $post_block_settings, $_POST);

                    update_option('arm_block_settings', $post_block_settings);

                    $response = array('type' => 'success', 'msg' => __('Settings Saved Successfully.', 'ARMember'));
                } else {
                    $response = array('type' => 'error', 'msg' => __('Some of users are having administrator previlegs. So those cant be block.', 'ARMember'));
                }
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_redirect_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $post_redirection_settings = $_POST['arm_redirection_settings'];
                
                $default_redirection_url = $post_redirection_settings['login']['conditional_redirect']['default'];
                unset($post_redirection_settings['login']['conditional_redirect']['default']);
                
                $post_redirection_settings['login']['conditional_redirect'] = array_values($post_redirection_settings['login']['conditional_redirect']);
                $post_redirection_settings['login']['conditional_redirect']['default'] = $default_redirection_url;  
                $is_update = true;
                if ($is_update == true) {
                    $post_redirection_settings = apply_filters('arm_before_update_redirection_settings', $post_redirection_settings, $_POST);
                    update_option('arm_redirection_settings', $post_redirection_settings);
                    $response = array('type' => 'success', 'msg' => __('Settings Saved Successfully.', 'ARMember'));
                } else {
                    $response = array('type' => 'error', 'msg' => __('Some of users are having administrator previlegs. So those cant be block.', 'ARMember'));
                }
            }
          
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_common_message_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $common_message = $_POST['arm_common_message_settings'];
                $common_message = apply_filters('arm_before_update_common_message_settings', $common_message, $_POST);
                update_option('arm_common_message_settings', $common_message);
                $response = array('type' => 'success', 'msg' => __('Settings Saved Successfully.', 'ARMember'));
            }
            
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_invoice_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

                $default_global_settings = $this->arm_default_global_settings();
                $arm_invoice_template = isset($_POST['arm_general_settings']['arm_invoice_template']) ? $_POST['arm_general_settings']['arm_invoice_template'] : $old_global_settings['general_settings']['arm_invoice_template'];
                $new_general_settings = shortcode_atts($default_global_settings['general_settings'], $old_global_settings['general_settings']);
                $new_general_settings['arm_invoice_template'] = $arm_invoice_template;
                $new_global_settings['page_settings'] = $old_global_settings['page_settings'];
                $new_global_settings['general_settings'] = $new_general_settings;
                $new_global_settings = apply_filters('arm_before_update_invoice_settings', $new_global_settings, $_POST);
                $new_global_settings['api_service'] = isset($old_global_settings['api_service']) ? $old_global_settings['api_service'] : array();
                update_option('arm_global_settings', $new_global_settings);
                $response = array('type' => 'success', 'msg' => __('Global Settings Saved Successfully.', 'ARMember'));
            }
            
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_access_restriction_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $default_global_settings = $this->arm_default_global_settings();
                $restrict_admin_panel = isset($_POST['arm_general_settings']['restrict_admin_panel']) ? intval($_POST['arm_general_settings']['restrict_admin_panel']) : 0;
                $arm_exclude_role_for_restrict_admin = ( isset($_POST['arm_general_settings']['arm_exclude_role_for_restrict_admin']) && !empty($_POST['arm_general_settings']['arm_exclude_role_for_restrict_admin']) )? implode(',',$_POST['arm_general_settings']['arm_exclude_role_for_restrict_admin']) : '';
                $hide_feed = isset($_POST['arm_general_settings']['hide_feed']) ? intval($_POST['arm_general_settings']['hide_feed']) : 0;
                $restrict_site_access = isset($_POST['arm_general_settings']['restrict_site_access']) ? intval($_POST['arm_general_settings']['restrict_site_access']) : 0;
                
                $new_general_settings = shortcode_atts($default_global_settings['general_settings'], $old_global_settings['general_settings']);
                $new_general_settings['restrict_admin_panel'] = $restrict_admin_panel;
                $new_general_settings['arm_exclude_role_for_restrict_admin'] = $arm_exclude_role_for_restrict_admin;
                $new_general_settings['hide_feed'] = $hide_feed;
                $new_general_settings['restrict_site_access'] = $restrict_site_access;
                
                $new_global_settings['page_settings'] = $old_global_settings['page_settings'];
                $new_global_settings['page_settings']['guest_page_id'] = isset($_POST['arm_page_settings']['guest_page_id']) ? intval($_POST['arm_page_settings']['guest_page_id']) : 0;
                $new_global_settings['page_settings']['arm_access_page_for_restrict_site'] = (isset($_POST['arm_general_settings']['arm_access_page_for_restrict_site']) && !empty($_POST['arm_general_settings']['arm_access_page_for_restrict_site'])) ? implode(',',$_POST['arm_general_settings']['arm_access_page_for_restrict_site']) : '';
                
                $new_global_settings['general_settings'] = $new_general_settings;
                $new_global_settings = apply_filters('arm_before_update_access_restriction_settings', $new_global_settings, $_POST);
                update_option('arm_global_settings', $new_global_settings);
                $arm_access_rules->arm_update_default_access_rules();
                $response = array('type' => 'success', 'msg' => __('Global Settings Saved Successfully.', 'ARMember'));
            }

            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_pay_per_post_settings') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

                $default_global_settings = $this->arm_default_global_settings();
                $arm_pay_per_post_default_content = isset($_POST['arm_general_settings']['arm_pay_per_post_default_content']) ? $_POST['arm_general_settings']['arm_pay_per_post_default_content'] : $old_global_settings['general_settings']['arm_pay_per_post_default_content'];
                $new_general_settings = shortcode_atts($default_global_settings['general_settings'], $old_global_settings['general_settings']);

                $new_general_settings['arm_pay_per_post_buynow_var'] = isset($_POST['arm_general_settings']['arm_pay_per_post_buynow_var']) ? $_POST['arm_general_settings']['arm_pay_per_post_buynow_var'] : '';
                $new_general_settings['arm_pay_per_post_allow_fancy_url'] = isset($_POST['arm_general_settings']['arm_pay_per_post_allow_fancy_url']) ? $_POST['arm_general_settings']['arm_pay_per_post_allow_fancy_url'] : '';


                $new_general_settings['arm_pay_per_post_default_content'] = $arm_pay_per_post_default_content;
                $new_global_settings['page_settings'] = $old_global_settings['page_settings'];
                $new_global_settings['general_settings'] = $new_general_settings;
                $new_global_settings = apply_filters('arm_before_update_invoice_settings', $new_global_settings, $_POST);
                $new_global_settings['api_service'] = isset($old_global_settings['api_service']) ? $old_global_settings['api_service'] : array();
                update_option('arm_global_settings', $new_global_settings);
                $response = array('type' => 'success', 'msg' => __('Global Settings Saved Successfully.', 'ARMember'));
            }

            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_api_service_feature') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                $default_global_settings = $this->arm_default_global_settings();
                $new_general_settings = shortcode_atts($default_global_settings['general_settings'], $old_global_settings['general_settings']);
                $api_service['arm_api_service_security_key'] = isset($_POST['arm_general_settings']['arm_api_security_key']) ? $_POST['arm_general_settings']['arm_api_security_key'] : '';

                $api_service['arm_list_membership_plans'] = isset($_POST['arm_general_settings']['arm_list_membership_plans']) ? $_POST['arm_general_settings']['arm_list_membership_plans'] : 0;
                $api_service['arm_membership_plan_details'] = isset($_POST['arm_general_settings']['arm_membership_plan_details']) ? $_POST['arm_general_settings']['arm_membership_plan_details'] : 0;
                $api_service['arm_member_details'] = isset($_POST['arm_general_settings']['arm_member_details']) ? $_POST['arm_general_settings']['arm_member_details'] : 0;
                $api_service['arm_member_memberships'] = isset($_POST['arm_general_settings']['arm_member_memberships']) ? $_POST['arm_general_settings']['arm_member_memberships'] : 0;
                $api_service['arm_member_paid_posts'] = isset($_POST['arm_general_settings']['arm_member_paid_posts']) ? $_POST['arm_general_settings']['arm_member_paid_posts'] : 0;
                $api_service['arm_member_payments'] = isset($_POST['arm_general_settings']['arm_member_payments']) ? $_POST['arm_general_settings']['arm_member_payments'] : 0;
                $api_service['arm_member_paid_post_payments'] = isset($_POST['arm_general_settings']['arm_member_paid_post_payments']) ? $_POST['arm_general_settings']['arm_member_paid_post_payments'] : 0;
                $api_service['arm_check_coupon_code'] = isset($_POST['arm_general_settings']['arm_check_coupon_code']) ? $_POST['arm_general_settings']['arm_check_coupon_code'] : 0;
                $api_service['arm_member_add_membership'] = isset($_POST['arm_general_settings']['arm_member_add_membership']) ? $_POST['arm_general_settings']['arm_member_add_membership'] : 0;
                $api_service['arm_create_transaction'] = isset($_POST['arm_general_settings']['arm_create_transaction']) ? $_POST['arm_general_settings']['arm_create_transaction'] : 0;
                $api_service['arm_member_cancel_membership'] = isset($_POST['arm_general_settings']['arm_member_cancel_membership']) ? $_POST['arm_general_settings']['arm_member_cancel_membership'] : 0;
                $api_service['arm_check_member_membership'] = isset($_POST['arm_general_settings']['arm_check_member_membership']) ? $_POST['arm_general_settings']['arm_check_member_membership'] : 0;

                $new_global_settings['api_service'] = $api_service;
                $new_global_settings['page_settings'] = $old_global_settings['page_settings'];
                $new_global_settings['general_settings'] = $new_general_settings;
                update_option('arm_global_settings', $new_global_settings);
                $response = array('type' => 'success', 'msg' => __('API settings Saved Successfully.', 'ARMember'));
            }

            echo json_encode($response);
            die();
        }

        function arm_manipulate_invoice_id($org_invoice_id) {
            $invoice_id = !empty($org_invoice_id) ? $org_invoice_id : 0;
            $invc_prefix = '#';
            $invc_suffix = "";
            $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
            if($arm_invoice_tax_feature == 1 && isset($this->global_settings["invc_pre_sfx_mode"]) && $this->global_settings["invc_pre_sfx_mode"] == 1 && !empty($invoice_id)) {
                $invc_prefix = isset($this->global_settings["invc_prefix_val"]) ? $this->global_settings["invc_prefix_val"] : $invc_prefix;
                $invc_suffix = isset($this->global_settings["invc_suffix_val"]) ? $this->global_settings["invc_suffix_val"] : '';
                $invc_min_digit = isset($this->global_settings["invc_min_digit"]) ? $this->global_settings["invc_min_digit"] : 0;
                if($invc_min_digit > 0) {
                    $invoice_id = str_pad($invoice_id, $invc_min_digit, "0", STR_PAD_LEFT);
                }
            }
            $new_invoice_id = $invc_prefix . $invoice_id . $invc_suffix;
            return $new_invoice_id;
        }

        function rewrite_config_file($url) {

            global $ARMember;
            if (file_exists(ABSPATH . 'wp-config.php')) {
                $global_config_file = ABSPATH . 'wp-config.php';
            } else {
                $global_config_file = dirname(ABSPATH) . '/wp-config.php';
            }
            if (is_multisite()) {
                $line = '';
            } else {
                if (ADMIN_COOKIE_PATH <> rtrim(wp_make_link_relative(network_site_url($url)), '/')) {
                    $line = 'define( \'ADMIN_COOKIE_PATH\', \'' . rtrim(wp_make_link_relative(network_site_url($url)), '/') . '\' );';
                }
            }

            if (isset($line)) {
                if (!is_writable($global_config_file) || !$this->arm_replace_cookie_path_line('define *\( *\'ADMIN_COOKIE_PATH\'', $line, $global_config_file)) {
                    return false;
                }
            }
            return true;
        }

        function remove_config_file() {
            if (file_exists(ABSPATH . 'wp-config.php')) {
                $global_config_file = ABSPATH . 'wp-config.php';
            } else {
                $global_config_file = dirname(ABSPATH) . '/wp-config.php';
            }
            if (!is_writable($global_config_file) || !$this->arm_replace_cookie_path_line('define *\( *\'ADMIN_COOKIE_PATH\'', '', $global_config_file)) {
                return false;
            }
            return true;
        }

        function arm_replace_cookie_path_line($old, $new, $file) {
            global $ARMember;
            if (@is_file($file) == false) {
                return false;
            }

            $found = false;
            $lines = file($file);
            foreach ((array) $lines as $line) {
                if (preg_match("/$old/", $line)) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $fd = fopen($file, 'w');
                foreach ((array) $lines as $line) {
                    if (!preg_match("/$old/", $line))
                        fputs($fd, $line);
                    elseif ($new != '') {
                        fputs($fd, "$new //Added by ARMember\r\n");
                    }
                }
                fclose($fd);
                return true;
            }

            $fd = fopen($file, 'w');
            $done = false;
            foreach ((array) $lines as $line) {
                if ($done || !preg_match('/^(if\ \(\ \!\ )?define|\$|\?>/', $line)) {
                    fputs($fd, $line);
                } else {
                    if ($new != '') {
                        fputs($fd, "$new //Added by ARMember\r\n");
                    }
                    fputs($fd, $line);
                    $done = true;
                }
            }
            fclose($fd);
            return true;
        }


        function remove_loginpage_label_text($text) {
            $remove_txts = array(
                'username', 'username:', 'username *',
                'username or email', 'username or email address', 'username or email address *',
                'password', 'my password:', 'password *',
                'e-mail', 'email address *',
                'first name *',
                'last name *',
                'email'
            );
            if (in_array(strtolower($text), $remove_txts)) {
                $text = '';
            }
            if ($text == 'Remember Me') {
                $text = 'Remember';
            }
            return $text;
        }

        function arm_remove_registration_link($value) {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $pagenow;
            $hideRegister = isset($this->global_settings['hide_register_link']) ? $this->global_settings['hide_register_link'] : 0;
            if ($hideRegister == 1) {
                $action = isset($_GET['action']) ? $_GET['action'] : '';
                if ($pagenow == 'wp-login.php' && $action != 'register') {
                    $value = false;
                }
            }
            return $value;
        }

        function arm_login_enqueue_assets() {
            global $arm_global_settings, $ARMember;
            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings = $all_global_settings['general_settings'];
            $disable_wp_login_style = isset($general_settings['disable_wp_login_style']) ? $general_settings['disable_wp_login_style'] : 0;
            if($disable_wp_login_style == 0)
            {
                wp_enqueue_style('arm_wp_login', MEMBERSHIP_URL . '/css/arm_wp_login.css', array(), MEMBERSHIP_VERSION);
                if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
                    wp_enqueue_style('arm_login_css_lt_3.8', MEMBERSHIP_URL . '/css/arm_login_lt_3.8.css', array(), MEMBERSHIP_VERSION);
                }
                ?>
                <script data-cfasync="false" type="text/javascript">
                    jQuery.fn.outerHTML = function (s) {
                        return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
                    };
                    jQuery(function ($) {
                        jQuery('input[type=text]').each(function (e) {
                            var label = jQuery(this).parents('label').text().replace('*', '');
                            jQuery(this).attr('placeholder', label);
                        });
                        jQuery('input#user_login').attr('placeholder', 'Username').attr('autocomplete', 'off');
                        jQuery('input#user_email').attr('placeholder', 'E-mail').attr('autocomplete', 'off');
                        jQuery('input#user_pass').attr('placeholder', 'Password').attr('autocomplete', 'off');
                        jQuery('input[type=checkbox]').each(function () {
                            var input_box = jQuery(this).outerHTML();
                            jQuery(this).replaceWith('<span class="arm_input_checkbox">' + input_box + '</span>');
                        });
                        jQuery('input[type=checkbox]').on('change', function () {
                            if (jQuery(this).is(':checked')) {
                                jQuery(this).closest('.arm_input_checkbox').addClass('arm_input_checked');
                            } else {
                                jQuery(this).closest('.arm_input_checkbox').removeClass('arm_input_checked');
                            }
                        });
                    });
                </script>
                <?php
            }
        }

        public function add_query_arg() {
            $args = func_get_args();
            if (is_array($args[0])) {
                if (count($args) < 2 || false === $args[1]) {
                    $uri = $_SERVER['REQUEST_URI'];
                } else {
                    $uri = $args[1];
                }
            } else {
                if (count($args) < 3 || false === $args[2]) {
                    $uri = $_SERVER['REQUEST_URI'];
                } else {
                    $uri = $args[2];
                }
            }
            if ($frag = strstr($uri, '#')) {
                $uri = substr($uri, 0, -strlen($frag));
            } else {
                $frag = '';
            }

            if (0 === stripos($uri, 'http://')) {
                $protocol = 'http://';
                $uri = substr($uri, 7);
            } elseif (0 === stripos($uri, 'https://')) {
                $protocol = 'https://';
                $uri = substr($uri, 8);
            } else {
                $protocol = '';
            }

            if (strpos($uri, '?') !== false) {
                list( $base, $query ) = explode('?', $uri, 2);
                $base .= '?';
            } elseif ($protocol || strpos($uri, '=') === false) {
                $base = $uri . '?';
                $query = '';
            } else {
                $base = '';
                $query = $uri;
            }
            wp_parse_str($query, $qs);
            $qs = urlencode_deep($qs); /* This re-URL-encodes things that were already in the query string */
            if (is_array($args[0])) {
                $kayvees = $args[0];
                $qs = array_merge($qs, $kayvees);
            } else {
                $qs[$args[0]] = $args[1];
            }
            foreach ($qs as $k => $v) {
                if ($v === false) {
                    unset($qs[$k]);
                }
            }
            $ret = build_query($qs);
            $ret = trim($ret, '?');
            $ret = preg_replace('#=(&|$)#', '$1', $ret);
            $ret = $protocol . $base . $ret . $frag;
            $ret = rtrim($ret, '?');
            $ret = esc_url_raw($ret);
            return $ret;
        }

        public function handle_return_messages($errors = '', $message = '') {
            global $wpdb, $ARMember, $arm_members_class;
            $type = 'error';
            $return = '';
            if (!empty($errors)) {
                if (isset($errors) && is_array($errors) && count($errors) > 0) {
                    foreach ($errors as $error) {
                        $return .= '<div>' . stripslashes($error) . '</div>';
                    }
                }
            } elseif (isset($message) && $message != '') {
                $type = 'success';
                $return = $message;
            } else {
                $return = false;
            }
            return array('type' => $type, 'msg' => $return);
        }

        public function get_param($param, $default = '', $src = 'get') {
            if (strpos($param, '[')) {
                $params = explode('[', $param);
                $param = $params[0];
            }

            @$str = stripslashes_deep(@$_POST['form']);
            @$str = json_decode(@$str, true);

            if ($src == 'get') {
                if(isset($_POST[$param]))
                {
                    $value = stripslashes_deep($_POST[$param]);
                }
                else if(isset($str[$param]))
                {
                    $value = stripslashes_deep($str[$param]);
                }
                else if(isset($_GET[$param]))
                {
                    $value = stripslashes_deep($_GET[$param]);
                }
                else {
                    $value = $default;
                }

                if ((!isset($_POST[$param]) or ! isset($str[$param])) and isset($_GET[$param]) and ! is_array($value)) {
                    $value = urldecode($value);
                }
            } else {
                if(isset($_POST[$param])) {
                    $value = stripslashes_deep(maybe_unserialize($_POST[$param]));
                } else if(isset($str[$param])) {
                    $value = stripslashes_deep(maybe_unserialize($str[$param]));
                } else {
                    $value = $default;
                }
            }

            if (isset($params) and is_array($value) and ! empty($value)) {
                foreach ($params as $k => $p) {
                    if (!$k or ! is_array($value)) {
                        continue;
                    }
                    $p = trim($p, ']');
                    $value = (isset($value[$p])) ? $value[$p] : $default;
                }
            }
            return $value;
        }

        public function get_unique_key($name = '', $table_name = '', $column = '', $id = 0, $num_chars = 8) {
            global $wpdb;
            $key = '';
            if (!empty($name)) {
                if (function_exists('sanitize_key'))
                    $key = sanitize_key($name);
                else
                    $key = sanitize_title_with_dashes($name);
            }
            if (empty($key)) {
                $max_slug_value = pow(36, $num_chars);
                $min_slug_value = 37;
                $key = base_convert(rand($min_slug_value, $max_slug_value), 10, 36);
            }

            if (!empty($table_name)) {
                $query = "SELECT $column FROM `$table_name` WHERE `$column` = '%s' LIMIT 1";
                $key_check = $wpdb->get_var($wpdb->prepare($query, $key));
                if ($key_check or is_numeric($key_check)) {
                    $suffix = 2;
                    do {
                        $alt_post_name = substr($key, 0, 200 - (strlen($suffix) + 1)) . "$suffix";
                        $key_check = $wpdb->get_var($wpdb->prepare($query, $alt_post_name, $id));
                        $suffix++;
                    } while ($key_check || is_numeric($key_check));
                    $key = $alt_post_name;
                }
            }
            return $key;
        }

        public function armStringMatchWithWildcard($source, $pattern) {
            $pattern = preg_quote($pattern, '/');
            $pattern = str_replace('\*', '.*', $pattern);
            return preg_match('/^' . $pattern . '$/i', $source);
        }

        public function arm_find_url_match($check_url = '', $urls = array()) {
            global $wp, $wpdb, $arm_errors;
            if (!empty($check_url) && !empty($urls)) {
                if (!preg_match('#^http(s)?://#', $check_url)) {
                    $check_url = 'http://' . $check_url;
                }
                $parse_check_url = parse_url($check_url);
                $parse_check_url['path'] = (isset($parse_check_url['path'])) ? $parse_check_url['path'] : '';
                $parse_check_url['query'] = (isset($parse_check_url['query'])) ? $parse_check_url['query'] : '';
                foreach ($urls as $url) {
                    $check_wildcard = explode('*', $url);
                    $wildcard_count = substr_count($url, '*');
                    if ($wildcard_count > 0) {
                        if ($this->armStringMatchWithWildcard($check_url, $url)) {
                            return TRUE;
                        }
                        if ($this->armStringMatchWithWildcard($check_url, $url . '/')) {
                            return TRUE;
                        }
                    } else {
                        if (!preg_match('/^http(s)?:\/\//', $url)) {
                            $url = 'http://' . $url;
                        }
                        $parse_url = parse_url($url);
                        $parse_url['path'] = (isset($parse_url['path'])) ? $parse_url['path'] : '';
                        $parse_url['query'] = (isset($parse_url['query'])) ? $parse_url['query'] : '';
                        /* Compare URL Details. */
                        $diff = array_diff($parse_check_url, $parse_url);
                        if ($parse_check_url['path'] == $parse_url['path']) {
                            if (isset($parse_check_url['query']) || isset($parse_url['query'])) {
                                if ($parse_check_url['query'] == $parse_url['query']) {
                                    return TRUE;
                                } else {
                                    continue;
                                }
                            }
                            return TRUE;
                        }
                    }
                }
            }
            return FALSE;
        }

        /**
         * Set Email Content Type
         */
        public function arm_mail_content_type() {
            return 'text/html';
        }

        public function arm_mailer($temp_slug, $user_id, $admin_template_id = '', $follower_id = '') {
            global $wpdb, $ARMember, $arm_slugs, $arm_email_settings;
            if (!empty($user_id) && $user_id != 0) {
                $user_info = get_user_by('id', $user_id);
                $to_user = $user_info->user_email;
                $to_admin = get_option('admin_email');

                $all_email_settings = $arm_email_settings->arm_get_all_email_settings();

                if (!empty($temp_slug)) {
                    $template = $arm_email_settings->arm_get_email_template($temp_slug);
                    if ($template->arm_template_status == '1') {
                        $message = $this->arm_filter_email_with_user_detail($template->arm_template_content, $user_id, 0, $follower_id);
                        $subject = $this->arm_filter_email_with_user_detail($template->arm_template_subject, $user_id, 0, $follower_id);
                        /* Send Email To User */
                        $user_send_mail = $this->arm_wp_mail('', $to_user, $subject, $message);
                    }
                }
                if (!empty($admin_template_id)) {
                    $admin_template = $arm_email_settings->arm_get_single_email_template($admin_template_id);
                    if ($admin_template->arm_template_status == '1') {
                        $message_admin = $this->arm_filter_email_with_user_detail($admin_template->arm_template_content, $user_id, 0, $follower_id);
                        $subject_admin = $this->arm_filter_email_with_user_detail($admin_template->arm_template_subject, $user_id, 0, $follower_id);

                        $admin_send_mail = $this->arm_send_message_to_armember_admin_users($to_user, $subject_admin, $message_admin);
                    }
                }
            }
        }

        public function arm_send_message_to_armember_admin_users($from = '', $subject = '', $message = '',$attachments=array()) {
            global $arm_email_settings, $arm_global_settings;
            $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
            $admin_email = (!empty($all_email_settings['arm_email_admin_email'])) ? $all_email_settings['arm_email_admin_email'] : get_option('admin_email');

            $exploded_admin_email = array();
            if (strpos($admin_email, ',') !== false) {
                $exploded_admin_email = explode(",", trim($admin_email));
            }

            if (isset($exploded_admin_email) && !empty($exploded_admin_email)) {
                foreach ($exploded_admin_email as $admin_email_from_array) {
                    if ($admin_email_from_array != '') {
                        $admin_email_from_array = apply_filters('arm_admin_email', trim($admin_email_from_array));
                        
                        $admin_send_mail = $arm_global_settings->arm_wp_mail($from, $admin_email_from_array, $subject, $message,$attachments);
                    }
                }
            } else {
                if ($admin_email) {
                    $admin_email = apply_filters('arm_admin_email', $admin_email);
                    $admin_send_mail = $arm_global_settings->arm_wp_mail($from, $admin_email, $subject, $message,$attachments);
                }
            }

            return $admin_send_mail;
        }

        public function arm_wp_mail($from, $recipient, $subject, $message, $attachments = array()) {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_email_settings, $arm_plain_text, $wp_version;
            remove_all_actions('phpmailer_init');
            $return = false;
            $emailSettings = $arm_email_settings->arm_get_all_email_settings();
            $arm_mail_authentication = (isset($emailSettings['arm_mail_authentication'])) ? $emailSettings['arm_mail_authentication'] : '1';
            $email_server = (!empty($emailSettings['arm_email_server'])) ? $emailSettings['arm_email_server'] : 'wordpress_server';
            $from_name = (!empty($emailSettings['arm_email_from_name'])) ? stripslashes_deep($emailSettings['arm_email_from_name']) : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $from_email = (!empty($emailSettings['arm_email_from_email'])) ? $emailSettings['arm_email_from_email'] : get_option('admin_email');
            $content_type = (@$arm_plain_text) ? 'text/plain' : 'text/html';
            $from_name = $from_name;
            $reply_to = (!empty($from)) ? $from : $from_email;
            /* Set Email Headers */
            $headers = array();
            //$headers[] = 'From: "' . $from_name . '" <' . $reply_to . '>'; //changes from v3.0
	    $headers[] = 'From: "' . $from_name . '" <' . $from_email . '>';
            $headers[] = 'Reply-To: ' . $reply_to;
            $headers[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';
            /* Filter Email Subject & Message */
            $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);
            $message = do_shortcode($message);
            $message = wordwrap(stripslashes($message), 70, "\r\n");
            if (@$arm_plain_text) {
                $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);
            }

            $subject = apply_filters('arm_email_subject', $subject);
            $message = apply_filters('arm_change_email_content', $message);
            $recipient = apply_filters('arm_email_recipients', $recipient);
            $headers = apply_filters('arm_email_header', $headers, $recipient, $subject);
            remove_filter('wp_mail_from', 'bp_core_email_from_address_filter');
            remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');
            
            if( version_compare( $wp_version, '5.5', '<' ) ){
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $armPMailer = new PHPMailer();
            } else {
                require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                $armPMailer = new PHPMailer\PHPMailer\PHPMailer();
            }
            do_action('arm_before_send_email_notification', $from, $recipient, $subject, $message, $attachments);
            /* Character Set of the message. */
            $armPMailer->CharSet = "UTF-8";
            $armPMailer->SMTPDebug = 0;
            /* $armPMailer->Debugoutput = 'html'; */
            if ($email_server == 'smtp_server') {
                $armPMailer->isSMTP();
                $armPMailer->Host = isset($emailSettings['arm_mail_server']) ? $emailSettings['arm_mail_server'] : '';
                $armPMailer->SMTPAuth = ($arm_mail_authentication==1) ? true : false;
                $armPMailer->Username = isset($emailSettings['arm_mail_login_name']) ? $emailSettings['arm_mail_login_name'] : '';
                $armPMailer->Password = isset($emailSettings['arm_mail_password']) ? $emailSettings['arm_mail_password'] : '';
                if (isset($emailSettings['arm_smtp_enc']) && !empty($emailSettings['arm_smtp_enc']) && $emailSettings['arm_smtp_enc'] != 'none') {
                    $armPMailer->SMTPSecure = $emailSettings['arm_smtp_enc'];
                }
                if($emailSettings['arm_smtp_enc'] == 'none'){
                    $armPMailer->SMTPAutoTLS = false;
                }
                
                $armPMailer->Port = isset($emailSettings['arm_mail_port']) ? $emailSettings['arm_mail_port'] : '';
            } else {
                $armPMailer->isMail();
            }
            //$armPMailer->setFrom($reply_to, $from_name);
	    $armPMailer->setFrom($from_email, $from_name);
            $armPMailer->addReplyTo($reply_to, $from_name);
            $armPMailer->addAddress($recipient);
            if (isset($attachments) && !empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $armPMailer->addAttachment($attachment);
                }
            }
            
           
            $armPMailer->isHTML(true);
            $armPMailer->Subject = $subject;
            $armPMailer->Body = $message;
            if (@$arm_plain_text) {
                $armPMailer->AltBody = $message;
            }
            if (MEMBERSHIP_DEBUG_LOG == true) {
                if (MEMBERSHIP_DEBUG_LOG_TYPE == 'ARM_ALL' || MEMBERSHIP_DEBUG_LOG_TYPE == 'ARM_MAIL') {
                    global $arm_case_types, $wpdb;
                    $arm_case_types['mail']['protected'] = true;
                    $arm_case_types['mail']['type'] = '';
                    $arm_case_types['mail']['message'] = " Email Server : " . $email_server . " <br/> Email Recipient : " . $recipient . " <br/> Message Content : " . $message;
                    $ARMember->arm_debug_response_log('arm_wp_mail', $arm_case_types, array(), $wpdb->last_query, true);
                }
            }
            /* Send Email */
            if ($email_server == 'smtp_server' || $email_server == 'phpmailer') {
                if ($armPMailer->send()) {
                    $return = true;
                }
            } else {
                add_filter('wp_mail_content_type', array($this, 'arm_mail_content_type'));
                if (!wp_mail($recipient, $subject, $message, $headers, $attachments)) {
                    if ($armPMailer->send()) {
                        $return = true;
                    }
                } else {
                    $return = true;
                }
                remove_filter('wp_mail_content_type', array($this, 'arm_mail_content_type'));
            }
            do_action('arm_after_send_email_notification', $from, $recipient, $subject, $message, $attachments);
            return $return;
        }

        public function arm_filter_email_with_user_detail($content, $user_id = 0, $plan_id = 0, $follower_id = 0, $key = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_payment_gateways, $arm_email_settings, $arm_global_settings;
            $user_info = get_user_by('id', $user_id);
            $f_displayname = '';
            $u_plan_description = '';
            if ($follower_id != 0 && !empty($follower_id)) {
                $follower_info = get_user_by('id', $follower_id);
                $follower_name = $follower_info->first_name . ' ' . (isset($follower_info->last_name))?$follower_info->last_name:'';
                if (empty($follower_info->first_name) && empty($follower_info->last_name)) {
                    $follower_name = $follower_info->user_login;
                }
                $f_displayname = "<a href='" . $this->arm_get_user_profile_url($follower_id) . "'>" . $follower_name . "</a>";
            }
            if ($user_id != 0 && !empty($user_info)) {
                $u_email = $user_info->user_email;
                $u_displayname = $user_info->display_name;
                $u_userurl = $user_info->user_url;
                $u_username = $user_info->user_login;
                $u_fname = (isset($user_info->first_name))?$user_info->first_name:'';
                $u_lname = (isset($user_info->last_name))?$user_info->last_name:'';
                $u_grace_period_days = 0;
                $u_trial_amount = 0;
                $u_plan_discount  = 0;
                $u_payable_amount = 0;
                $now = current_time('timestamp'); // or your date as well
                
                $arm_is_user_in_grace = 0;
                $arm_user_grace_end_date = '';
                $plan_detail = array(); 
                $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plan = 0;
                $using_gateway = '';
                $payment_cycle = 0;
                if(!empty($plan_id)){
                    $user_plan = $plan_id; 
                    $planData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                    if(!empty($planData))
                    {
                        $arm_is_user_in_grace = (isset($planData['arm_is_user_in_grace']) && !empty($planData['arm_is_user_in_grace'])) ? $planData['arm_is_user_in_grace'] : 0;
                        $arm_user_grace_end_date = $planData['arm_grace_period_end'];
                        $plan_detail = $planData['arm_current_plan_detail'];
                        $using_gateway = $planData['arm_user_gateway'];
                        $payment_cycle = $planData['arm_payment_cycle'];
                        $expire_time = $planData['arm_expire_plan'];
                    }
                }
           
                if ($arm_is_user_in_grace == 1) {
                    $datediff = $arm_user_grace_end_date - $now;
                    $u_grace_period_days = floor($datediff / (60 * 60 * 24));
                }
                $activation_key = get_user_meta($user_id, 'arm_user_activation_key', true);
                $login_page_id = isset($this->global_settings['login_page_id']) ? $this->global_settings['login_page_id'] : 0;
                if ($login_page_id == 0) {
                    $arm_login_page_url = wp_login_url();
                } else {

                    $arm_login_page_url = $this->arm_get_permalink('', $login_page_id);
                }
                
                
                 $arm_login_page_url = $arm_global_settings->add_query_arg('arm-key', urlencode($activation_key), $arm_login_page_url);
                 $arm_login_page_url = $arm_global_settings->add_query_arg('email', urlencode($u_email), $arm_login_page_url);
                
                
                $validate_url = $arm_login_page_url;
                $pending = '';

                $login_url = $this->arm_get_permalink('', $login_page_id);
                $profile_link = $this->arm_get_user_profile_url($user_info->ID);
                $blog_name = get_bloginfo('name');
                $blog_url = ARM_HOME_URL;
                $arm_currency = $arm_payment_gateways->arm_get_global_currency();

                $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
                $admin_email = (!empty($all_email_settings['arm_email_admin_email'])) ? $all_email_settings['arm_email_admin_email'] : get_option('admin_email');

                $u_plan_name = '-';
                $u_plan_amount = '-';
                $u_plan_discount = '-';
                $u_payment_type = '-';
                $u_payment_gateway = '-';
                $u_transaction_id = '-';
                $plan_expire = '';
                $u_tax_percentage = '-';
                $u_tax_amount = '-';
                $u_payment_date = '-';
                $u_coupon_code = '-';

             
                if (!empty($plan_detail)) {
                    $plan_detail = maybe_unserialize($plan_detail);
                    if (!empty($plan_detail)) {
                        $planObj = new ARM_Plan(0);
                        $planObj->init((object) $plan_detail);
                    } else {
                        $planObj = new ARM_Plan($user_plan);
                    }
                    $u_plan_name = $planObj->name;
                    $u_plan_description = $planObj->description;
                    
                    if($planObj->is_recurring()){
                        $plan_data = $planObj->prepare_recurring_data($payment_cycle);
                        $u_plan_amount = $plan_data['amount'];
                        $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $u_plan_amount);
                    }
                    else{
                        $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $planObj->amount);
                    }

                    $plan_expire = __('Never Expires', 'ARMember');
                
                if (!empty($expire_time)) {
                    $date_format = $this->arm_get_wp_date_format();
                    $plan_expire = date_i18n($date_format, $expire_time);
                }
                    
                    
                   
                    if (!empty($using_gateway)) {
                        $u_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($using_gateway);
                    }
                    // if ($planObj->has_trial_period()) {
                    //     $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
                    //     $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $planTrialOpts['amount']);
                    // } 
                    if ($planObj->is_paid()) {
                        if ($planObj->is_lifetime()) {
                            $u_payment_type = __('Life Time', 'ARMember');
                        } else {
                            if ($planObj->is_recurring()) {
                                $u_payment_type = __('Subscription', 'ARMember');
                            } else {
                                $u_payment_type = __('One Time', 'ARMember');
                            }
                        }
                    } else {
                        $u_payment_type = __('Free', 'ARMember');
                    }

                    $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`, `arm_is_trial`, `arm_amount`, `arm_extra_vars`, `arm_coupon_discount`, `arm_coupon_discount_type`,`arm_payment_date`, `arm_coupon_code`';
                    $where_bt='';
                    if ($using_gateway == 'bank_transfer') {
                       $where_bt=" AND arm_payment_gateway='bank_transfer'";
                    }    

                    $armLogTable = $ARMember->tbl_arm_payment_log;
                    $selectColumns .= ', `arm_token`';
                    
                    $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_user_id`='{$user_id}' AND `arm_plan_id`='{$user_plan}' {$where_bt} ORDER BY `arm_log_id` DESC");
                    if (!empty($log_detail)) {
                        $u_transaction_id = $log_detail->arm_transaction_id;

                        $extravars = maybe_unserialize($log_detail->arm_extra_vars);


                        if ($using_gateway == 'bank_transfer') {
                            if(isset($extravars['coupon'])){
                                $u_plan_discount = isset($extravars['coupon']['amount']) ? $extravars['coupon']['amount'] : 0;
				$u_coupon_code = isset($extravars['coupon']['coupon_code']) ? $extravars['coupon']['coupon_code'] : "";
                            }
                            else{
                                $u_plan_discount = $log_detail->arm_coupon_discount.$log_detail->arm_coupon_discount_type;
				$u_coupon_code = $log_detail->arm_coupon_code;
                            }
                        }
                        else{
                            $u_plan_discount = isset($extravars['coupon']['amount']) ? $extravars['coupon']['amount'] : 0;   
			    $u_coupon_code = isset($extravars['coupon']['coupon_code']) ? $extravars['coupon']['coupon_code'] : "";
                        }


                        if(isset($extravars['tax_percentage'])){
                            $u_tax_percentage = ($extravars['tax_percentage'] != '') ? $extravars['tax_percentage'].'%': '-';
                        }

                        if(isset($extravars['tax_amount'])){
                            $u_tax_amount = ($extravars['tax_amount'] != '') ? $arm_payment_gateways->arm_amount_set_separator($arm_currency, $extravars['tax_amount']): '-';
                        }

                        
                        if (!empty($log_detail->arm_is_trial) && $log_detail->arm_is_trial == 1) {
                            $u_trial_amount= isset($extravars['trial']['amount']) ? $extravars['trial']['amount'] : 0;

                        }
                        $u_payable_amount = $log_detail->arm_amount;

                        if (!empty($log_detail->arm_payment_date)) {
                                $date_format = $this->arm_get_wp_date_format();
                                $u_payment_date = date_i18n($date_format, $log_detail->arm_payment_date);
                        }

                    }
                }


                if (empty($user_plans)) {
                    $arm_user_entry_id = get_user_meta($user_id, 'arm_entry_id', true);
                    if (isset($arm_user_entry_id) && $arm_user_entry_id != '') {
                        $armentryTable = $ARMember->tbl_arm_entries;
                        $arm_user_entry_data_ser = $wpdb->get_var("SELECT `arm_entry_value` FROM `{$armentryTable}` WHERE `arm_entry_id` = {$arm_user_entry_id}");
                        $arm_user_entry_data = maybe_unserialize($arm_user_entry_data_ser);
                        $arm_user_payment_gateway = '';

                        if (isset($arm_user_entry_data['arm_front_gateway_skin_type']) && $arm_user_entry_data['arm_front_gateway_skin_type'] == 'dropdown') {

                            $arm_user_payment_gateway = $arm_user_entry_data['_payment_gateway'];
                            $arm_plan_skin_type = $arm_user_entry_data['arm_front_plan_skin_type'];
                            if ($arm_plan_skin_type == 'skin5') {
                                $arm_subscription_plan = isset($arm_user_entry_data['_subscription_plan']) ? $arm_user_entry_data['_subscription_plan'] : '';
                            } else {
                                $arm_subscription_plan = isset($arm_user_entry_data['subscription_plan']) ? $arm_user_entry_data['subscription_plan'] : '';
                            }
                        } else if (isset($arm_user_entry_data['arm_front_gateway_skin_type']) && $arm_user_entry_data['arm_front_gateway_skin_type'] == 'radio') {

                            $arm_user_payment_gateway = $arm_user_entry_data['payment_gateway'];
                            $arm_plan_skin_type = $arm_user_entry_data['arm_front_plan_skin_type'];
                            if ($arm_plan_skin_type == 'skin5') {
                                $arm_subscription_plan = isset($arm_user_entry_data['_subscription_plan']) ? $arm_user_entry_data['_subscription_plan'] : '';
                            } else {
                                $arm_subscription_plan = isset($arm_user_entry_data['subscription_plan']) ? $arm_user_entry_data['subscription_plan'] : '';
                            }
                        }

                        if ($arm_user_payment_gateway == 'bank_transfer') {

                            $userplanObj = new ARM_Plan($arm_subscription_plan);
                            $u_plan_name = $userplanObj->name;
                            $u_plan_description = $userplanObj->description;
                            $u_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key('bank_transfer');
                            $plan_expire = '';
                            $u_trial_amount = 0;
                            $u_plan_discount  =0;
                            $u_payable_amount = 0;
                            
                            
                            if($userplanObj->is_recurring()){
                                $plan_data = $userplanObj->prepare_recurring_data($payment_cycle);
                                $u_plan_amount = $plan_data['amount'];
                                $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $u_plan_amount);
                            }
                            else{
                                $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $userplanObj->amount);
                            }

                            if ($userplanObj->has_trial_period()) {
                                $planTrialOpts = isset($userplanObj->options['trial']) ? $userplanObj->options['trial'] : array();
                                $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $planTrialOpts['amount']);
                            }

                            if ($userplanObj->is_paid()) {
                                if ($userplanObj->is_lifetime()) {
                                    $u_payment_type = __('Life Time', 'ARMember');
                                } else {
                                    if ($userplanObj->is_recurring()) {
                                        $u_payment_type = __('Subscription', 'ARMember');
                                    } else {
                                        $u_payment_type = __('One Time', 'ARMember');
                                    }
                                }
                            }

                            $selectColumns = '`arm_coupon_discount_type`, `arm_coupon_discount`, `arm_transaction_id`, `arm_extra_vars`, `arm_is_trial`, `arm_amount`';

                            $armLogTable = $ARMember->tbl_arm_payment_log;

                            $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_user_id`='{$user_id}' AND `arm_plan_id`='{$arm_subscription_plan}' AND arm_payment_gateway='bank_transfer' ORDER BY `arm_log_id` DESC");
                            if (!empty($log_detail)) {
                                $u_transaction_id = $log_detail->arm_transaction_id;
                               $u_payable_amount = $log_detail->arm_amount;

                                $extravars = maybe_unserialize($log_detail->arm_extra_vars);

                        if (!empty($log_detail->arm_coupon_discount) && $log_detail->arm_coupon_discount > 0) {
                            $u_plan_discount = isset($extravars['coupon']['amount']) ? $extravars['coupon']['amount'] : 0;

                        } 

                        if(!empty($log_detail->arm_coupon_code)) {
                            $u_coupon_code = $log_detail->arm_coupon_code;
                        } else if(isset($extravars['coupon'])) {
                            $u_coupon_code = isset($extravars['coupon']['coupon_code']) ? $extravars['coupon']['coupon_code'] : "";
                        }

                        if (!empty($log_detail->arm_is_trial) && $log_detail->arm_is_trial == 1) {
                            $u_trial_amount= isset($extravars['trial']['amount']) ? $extravars['trial']['amount'] : 0;

                        }


                            }
                        }
                    }
                }



                if ($key != '' && !empty($key)) {

                    $change_password_page_id = isset($arm_global_settings->global_settings['change_password_page_id']) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
                    if ($change_password_page_id == 0) {
                        $arm_reset_password_link = network_site_url("wp-login.php?action=armrp&key=" . rawurlencode($key) . "&login=" . rawurlencode($u_username), 'login');
                    } else {
                        $arm_change_password_page_url = $arm_global_settings->arm_get_permalink('', $change_password_page_id);
                        
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('action', 'armrp', $arm_change_password_page_url);
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('key', rawurlencode($key), $arm_change_password_page_url);
                        $arm_change_password_page_url = $arm_global_settings->add_query_arg('login', rawurlencode($u_username), $arm_change_password_page_url);
                        $arm_reset_password_link = $arm_change_password_page_url;
                    }


                    $varification_key = get_user_meta($user_id, 'arm_user_activation_key', true);
                    $user_status = arm_get_member_status($user_id);
                    if($user_status == 3){
                        $rp_link =  $arm_global_settings->add_query_arg('varify_key', rawurlencode($varification_key), $arm_reset_password_link);
                    }



                    $content = str_replace('{ARM_RESET_PASSWORD_LINK}', $arm_reset_password_link, $content);
                } else {

                    $content = str_replace('{ARM_RESET_PASSWORD_LINK}', '', $content);
                }

                $u_payable_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $u_payable_amount);

                $content = str_replace('{ARM_USER_ID}', $user_id, $content);
                $content = str_replace('{ARM_USERNAME}', $u_username, $content);
                $content = str_replace('{ARM_FIRST_NAME}', $u_fname, $content);
                $content = str_replace('{ARM_LAST_NAME}', $u_lname, $content);
                $content = str_replace('{ARM_NAME}', $u_displayname, $content);
                $content = str_replace('{ARM_EMAIL}', $u_email, $content);
                $content = str_replace('{ARM_ADMIN_EMAIL}', $admin_email, $content);
                $content = str_replace('{ARM_BLOGNAME}', $blog_name, $content);
                $content = str_replace('{ARM_BLOG_URL}', $blog_url, $content);
                $content = str_replace('{ARM_VALIDATE_URL}', $validate_url, $content);
                $content = str_replace('{ARM_CHANGE_PASSWORD_CONFIRMATION_URL}', $pending, $content);
                $content = str_replace('{ARM_PENDING_REQUESTS_URL}', $pending, $content);
                $content = str_replace('{ARM_PROFILE_FIELDS}', $pending, $content);
                $content = str_replace('{ARM_PROFILE_LINK}', $profile_link, $content);
                $content = str_replace('{ARM_LOGIN_URL}', $login_url, $content);
                $content = str_replace('{ARM_PLAN}', $u_plan_name, $content);
                $content = str_replace('{ARM_PLAN_DESCRIPTION}', $u_plan_description, $content);
                $content = str_replace('{ARM_PLAN_AMOUNT}', $u_plan_amount, $content);
                $content = str_replace('{ARM_PLAN_DISCOUNT}', $u_plan_discount, $content);
                $content = str_replace('{ARM_TRIAL_AMOUNT}', $u_trial_amount, $content);
                $content = str_replace('{ARM_PAYABLE_AMOUNT}', $u_payable_amount, $content);
                $content = str_replace('{ARM_PAYMENT_TYPE}', $u_payment_type, $content);
                $content = str_replace('{ARM_PAYMENT_GATEWAY}', $u_payment_gateway, $content);
                $content = str_replace('{ARM_TRANSACTION_ID}', $u_transaction_id, $content);
                $content = str_replace('{ARM_TAX_PERCENTAGE}', $u_tax_percentage, $content);
                $content = str_replace('{ARM_TAX_AMOUNT}', $u_tax_amount, $content);
                $content = str_replace('{ARM_GRACE_PERIOD_DAYS}', $u_grace_period_days, $content);
                $content = str_replace('{ARM_CURRENCY}',$arm_currency, $content);
                $content = str_replace('{ARM_PLAN_EXPIRE}',$plan_expire, $content);
                $content = str_replace('{ARM_USERMETA_user_url}', $u_userurl, $content);
                $content = str_replace('{ARM_PAYMENT_DATE}', $u_payment_date, $content);
                $content = str_replace('{ARM_PLAN_COUPON_CODE}', $u_coupon_code, $content);

                $networ_name = get_site_option('site_name');
                $networ_url = get_site_option('siteurl');

                $content = str_replace('{ARM_MESSAGE_NETWORKNAME}',$networ_name, $content);

                $content = str_replace('{ARM_MESSAGE_NETWORKURL}',$networ_url, $content);



                /* Content replace for user meta */
                $matches = array();
                preg_match_all("/\b(\w*ARM_USERMETA_\w*)\b/", $content, $matches, PREG_PATTERN_ORDER);
                $matches = $matches[0];
                if (!empty($matches)) {
                    foreach ($matches as $mat_var) {
                        $key = str_replace('ARM_USERMETA_', '', $mat_var);
                        $meta_val = "";
                        if (!empty($key)) {
                            $meta_val = get_user_meta($user_id, $key, TRUE);
                            if(is_array($meta_val))
                            {
                                $replace_val = "";
                                foreach ($meta_val as $key => $value) {
                                    $replace_val .= ($value != '') ? $value."," : "";   
                                }
                                $meta_val = rtrim($replace_val, ",");
                            }
                        }
                        $content = str_replace('{' . $mat_var . '}', $meta_val, $content);
                    }
                }
            }

            $content = nl2br($content);
            $content = apply_filters('arm_change_email_content_with_user_detail', $content, $user_id);
            return $content;
        }

        function arm_get_wp_pages($args = '', $columns = array()){
             $defaults = array(
                'depth' => 0, 'child_of' => 0,
                'selected' => 0, 'echo' => 1,
                'name' => 'page_id', 'id' => '',
                'show_option_none' => 'Select Page', 'show_option_no_change' => '',
                'option_none_value' => '',
                'class' => '',
                'required' => false,
                'required_msg' => false,
            );
            $arm_r = wp_parse_args($args, $defaults);
            $arm_pages = get_pages($arm_r);
            $arm_new_pages = array();
            if(!empty($arm_pages)){
                if(!empty($columns))
                {
                    $n = 0;
                    foreach($arm_pages as $page)
                    {
                        foreach($columns as $column){
                            $arm_new_pages[$n][$column] = $page->$column;
                        }
                        $n++;
                    }
                }
                else
                {
                    $arm_new_pages = $arm_pages;
                }
            }
            return $arm_new_pages;
        }
        
        function arm_wp_dropdown_pages($args = '', $dd_class = '') {
            $defaults = array(
                'depth' => 0, 'child_of' => 0,
                'selected' => 0, 'echo' => 1,
                'name' => 'page_id', 'id' => '',
                'show_option_none' => 'Select Page', 'show_option_no_change' => '',
                'option_none_value' => '',
                'class' => '',
                'required' => false,
                'required_msg' => false,
            );
            $r = wp_parse_args($args, $defaults);
            $pages = get_pages($r);
            $output = '';
            if (empty($r['id'])) {
                $r['id'] = $r['name'];
            }

            $pageIds = array();
            if (!empty($pages)) {
                $pageIds = array();
                foreach ($pages as $p) {
                    $pageIds[] = $p->ID;
                }
            }
            if (!in_array($r['selected'], $pageIds)) {
                $r['selected'] = '';
            }
   
            $required = ($r['required']) ? 'required="required"' : '';
            $required_msg = ($r['required_msg']) ? 'data-msg-required="' . $r['required_msg'] . '"' : '';
            $output .= "<input type='hidden'  name='" . esc_attr($r['name']) . "' id='" . esc_attr($r['id']) . "' class='" . $r['class'] . "' value='" . $r['selected'] . "' $required $required_msg/>";
            $output .= "<dl class='arm_selectbox column_level_dd'>";
            $output .= "<dt class='".$dd_class."'><span>" . (!empty($r['selected']) ? get_the_title($r['selected']) : 'Select Page') . "</span><input type='text' style='display:none;' value='" . (!empty($r['selected']) ? get_the_title($r['selected']) : 'Select Page') . "' class='arm_autocomplete'  /><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
            $output .= "<dd>";
            $output .= "<ul data-id='" . esc_attr($r['id']) . "'>";

            if ($r['show_option_no_change']) {

                $output .= "<li data-label='" . $r['show_option_no_change'] . "' data-value='-1'>" . $r['show_option_no_change'] . "</li>";
            }
            if ($r['show_option_none']) {
                $output .= "<li data-label='" . $r['show_option_none'] . "' data-value='" . esc_attr($r['option_none_value']) . "'>" . $r['show_option_none'] . "</li>";
            }
            if (!empty($pages)) {
                foreach ($pages as $p) {
                    $is_protected = 0;
                    $item_plans = get_post_meta($p->ID, 'arm_access_plan');
                    $item_plans = (!empty($item_plans)) ? $item_plans : array();

                    if (count($item_plans) == 0)
                        $is_protected = 0;
                    else
                        $is_protected = 1;

                    if(empty($p->post_title)) {
                        $arm_post_title = esc_html__("(no title)", "ARMember");
                    } else {
		    	$arm_post_title = $p->post_title;
                    }
		    $output .= "<li data-label='" . $arm_post_title . "' data-value='" . esc_attr($p->ID) . "' data-protected='" . $is_protected . "' >" . $arm_post_title . "</li>";
                    
                }
            }
            $output .= "</ul>";
            $output .= "</dd>";
            $output .= "</dl>";

            $html = apply_filters('arm_wp_dropdown_pages', $output);

            if ($r['echo']) {
                echo $html;
            }
            return $html;
        }

        function arm_get_wp_date_format() {
            global $wp, $wpdb;
            if (is_multisite()) {
                $wp_format_date = get_option('date_format');
            }
            else{
                $wp_format_date = get_site_option('date_format');
            }
            if (empty($wp_format_date)) {
                $date_format = 'M d, Y';
            } else {
                $date_format = $wp_format_date;
            }
            return $date_format;
        }
        
        function arm_get_wp_date_time_format() {
            global $wp, $wpdb;
            
            if (is_multisite()) {
                $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
            } else {
                $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
            }
                        
            if (empty($wp_date_time_format)) {
                $date_time_format = 'M d, Y H:i:s';
            } else {
                $date_time_format = $wp_date_time_format;
            }
            return $date_time_format;
        }

        function arm_time_elapsed($ptime) {
            $etime = current_time('timestamp') - $ptime;
            if ($etime < 1) {
                return __('now!', 'ARMember');
            }
            $a = array(12 * 30 * 24 * 60 * 60 => __('year', 'ARMember'),
                30 * 24 * 60 * 60 => __('month', 'ARMember'),
                24 * 60 * 60 => __('day', 'ARMember'),
                60 * 60 => __('hour', 'ARMember'),
                60 => __('minute', 'ARMember'),
                1 => __('second', 'ARMember')
            );
            foreach ($a as $secs => $str) {
                $d = $etime / $secs;
                if ($d >= 1) {
                    $r = round($d);
                    return $r . ' ' . $str . ($r > 1 ? 's' : '') . __(' ago', 'ARMember');
                }
            }
            return '-';
        }

        function arm_time_remaining($rtime) {
            $etime = $rtime - current_time('timestamp');
            if ($etime < 1) {
                return __('now!', 'ARMember');
            }
            $a = array(12 * 30 * 24 * 60 * 60 => __('year', 'ARMember'),
                30 * 24 * 60 * 60 => __('month', 'ARMember'),
                24 * 60 * 60 => __('day', 'ARMember'),
                60 * 60 => __('hour', 'ARMember'),
                60 => __('minute', 'ARMember'),
                1 => __('second', 'ARMember')
            );
            foreach ($a as $secs => $str) {
                $d = $etime / $secs;
                if ($d >= 1) {
                    $r = round($d);
                    return $r . ' ' . $str . ($r > 1 ? 's' : '');
                }
            }
            return '-';
        }

        function arm_get_remaining_occurrence($start_date, $end_date, $interval) {
            $dates = array();
            $now = current_time('timestamp');
            while ($start_date <= $end_date) {
                if ($now < $start_date) {
                    $dates[] = date('Y-m-d H:i:s', $start_date);
                }
                $start_date = strtotime($interval, $start_date);
            }
            return (count($dates) - 1);
        }

        function arm_get_confirm_box($item_id = 0, $confirmText = '', $btnClass = '', $deleteType = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs;
            $confirmBox = "<div class='arm_confirm_box arm_confirm_box_{$item_id}' id='arm_confirm_box_{$item_id}'>";
            $confirmBox .= "<div class='arm_confirm_box_body'>";
            $confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
            $confirmBox .= "<div class='arm_confirm_box_text'>{$confirmText}</div>";
            $confirmBox .= "<div class='arm_confirm_box_btn_container'>";
            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armok {$btnClass}' data-item_id='{$item_id}' data-type='{$deleteType}'>" . __('Delete', 'ARMember') . "</button>";
            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . __('Cancel', 'ARMember') . "</button>";
            $confirmBox .= "</div>";
            $confirmBox .= "</div>";
            $confirmBox .= "</div>";
            return $confirmBox;
        }

        function arm_get_badges_confirm_box($item_id = 0, $confirmText = '', $btnClass = '', $deleteType = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_members_badges;
            $confirmBox = "<div class='arm_confirm_box arm_confirm_box_{$item_id}' id='arm_confirm_box_{$item_id}'>";
            $confirmBox .= "<div class='arm_confirm_box_body arm_badge_confirm_box_body'>";
            $confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
            $confirmBox .= "<div class='arm_confirm_box_text arm_badge_confirm_box_text_{$item_id}'>{$confirmText}</div>";
            $user_achievements_list = $arm_members_badges->arm_get_user_achievements_badges_list($item_id);
            $user_achievements_list = !empty($user_achievements_list) ? $user_achievements_list : '--';
            $confirmBox .= $user_achievements_list;
            $confirmBox .= "<div class='arm_badge_error_red arm_badge_error_red_{$item_id}' style='display:none;'>" . __('Please select atleast one badge.', 'ARMember') . "</div>";
            $confirmBox .= "<div class='arm_confirm_box_btn_container'>";
            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armok {$btnClass}' data-item_id='{$item_id}' data-type='{$deleteType}'>" . __('Delete', 'ARMember') . "</button>";
            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . __('Cancel', 'ARMember') . "</button>";
            $confirmBox .= "</div>";
            $confirmBox .= "</div>";
            $confirmBox .= "</div>";
            return $confirmBox;
        }

        function arm_get_bpopup_html($args) {
            global $wp, $wpdb, $ARMember, $arm_slugs;
            $defaults = array(
                'id' => '',
                'class' => 'arm_bpopup_wrapper',
                'title' => '',
                'content' => '',
                'button_id' => '',
                'button_onclick' => '',
                'ok_btn_class' => '',
                'ok_btn_text' => __('Ok', 'ARMember'),
                'cancel_btn_text' => __('Cancel', 'ARMember'),
            );
            extract(shortcode_atts($defaults, $args));
            /* Generate Popup HTML */
            $popup = '<div id="' . $id . '" class="popup_wrapper ' . $class . '"><div class="popup_wrapper_inner">';
            $popup .= '<div class="popup_header">';
            $popup .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
            $popup .= '<span class="popup_header_text">' . $title . '</span>';
            $popup .= '</div>';
            $popup .= '<div class="popup_content_text">' . $content . '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '<div class="popup_footer">';
            $popup .= '<div class="popup_content_btn_wrapper">';
            $ok_btn_onclick = (!empty($button_onclick)) ? 'onclick="' . $button_onclick . '"' : '';
            $popup .= '<button type="button" class="arm_submit_btn popup_ok_btn ' . $ok_btn_class . '" id="' . $button_id . '" ' . $ok_btn_onclick . '>' . $ok_btn_text . '</button>';
            $popup .= '</div>';
            $popup .= '<div class="popup_content_btn_wrapper">';
            $popup .= '<button class="arm_cancel_btn popup_close_btn" type="button">' . $cancel_btn_text . '</button>';
            $popup .= '</div>';
            $popup .= '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '</div></div>';
            return $popup;
        }

        function arm_get_bpopup_html_payment($args) {
            global $wp, $wpdb, $ARMember, $arm_slugs;
            $defaults = array(
                'id' => '',
                'class' => 'arm_bpopup_wrapper',
                'title' => '',
                'content' => '',
                'button_id' => '',
                'button_onclick' => '',
                'ok_btn_class' => '',
                'ok_btn_text' => __('Ok', 'ARMember'),
                'cancel_btn_text' => __('Cancel', 'ARMember'),
            );
            extract(shortcode_atts($defaults, $args));
            /* Generate Popup HTML */
            $popup = '<div id="' . $id . '" class="popup_wrapper ' . $class . '"><div class="popup_wrapper_inner">';
            $popup .= '<div class="popup_header">';
            $popup .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
            $popup .= '<span class="popup_header_text">' . $title . '</span>';
            $popup .= '</div>';
            $popup .= '<div class="popup_content_text">' . $content . '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '<div class="popup_footer">';
            $popup .= '<div class="popup_content_btn_wrapper">';
            $ok_btn_onclick = (!empty($button_onclick)) ? 'onclick="' . $button_onclick . '"' : '';
            $popup .= '<button type="button" class="arm_submit_btn popup_ok_btn ' . $ok_btn_class . '" id="' . $button_id . '" ' . $ok_btn_onclick . '>' . $ok_btn_text . '</button>';
            $popup .= '</div>';

            $popup .= '</div>';
            $popup .= '<div class="armclear"></div>';
            $popup .= '</div></div>';
            return $popup;
        }

        function arm_after_delete_term($term, $tt_id, $taxonomy, $deleted_term) {
            global $wp, $wpdb, $ARMember, $arm_slugs;
            delete_arm_term_meta($term, 'arm_protection');
            delete_arm_term_meta($term, 'arm_access_plan');
        }

        /**         * **************************************************************************************
         * * String Utilities Functions
         * * ************************************************************************************* */

        /**
         * Trims deeply; alias of `trim_deep`.
         * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
         * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
         */
        public static function trim($value = '', $chars = FALSE, $extra_chars = FALSE) {
            return self::trim_deep($value, $chars, $extra_chars);
        }

        /**
         * Trims deeply; or use {@link s2Member\Utilities\self::trim()}.
         * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
         * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
         */
        public static function trim_deep($value = '', $chars = FALSE, $extra_chars = FALSE) {
            $chars = (is_string($chars)) ? $chars : " \t\n\r\0\x0B";
            $chars = (is_string($extra_chars)) ? $chars . $extra_chars : $chars;
            if (is_array($value)) {
                foreach ($value as &$r) {
                    $r = self::trim_deep($r, $chars);
                }
                return $value;
            }
            return trim((string) $value, $chars);
        }

        /**
         * Trims all single/double quote entity variations deeply.
         * This is useful on Shortcode attributes mangled by a Visual Editor.
         * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
         * @return string|array Either the input string, or the input array; after all data is trimmed up.
         */
        public static function trim_qts_deep($value = '') {
            $quote_entities_variations = array(
                '&apos;' => '&apos;',
                '&#0*39;' => '&#39;',
                '&#[xX]0*27;' => '&#x27;',
                '&lsquo;' => '&lsquo;',
                '&#0*8216;' => '&#8216;',
                '&#[xX]0*2018;' => '&#x2018;',
                '&rsquo;' => '&rsquo;',
                '&#0*8217;' => '&#8217;',
                '&#[xX]0*2019;' => '&#x2019;',
                '&quot;' => '&quot;',
                '&#0*34;' => '&#34;',
                '&#[xX]0*22;' => '&#x22;',
                '&ldquo;' => '&ldquo;',
                '&#0*8220;' => '&#8220;',
                '&#[xX]0*201[cC];' => '&#x201C;',
                '&rdquo;' => '&rdquo;',
                '&#0*8221;' => '&#8221;',
                '&#[xX]0*201[dD];' => '&#x201D;'
            );
            $qts = implode('|', array_keys($quote_entities_variations));
            return is_array($value) ? array_map('self::trim_qts_deep', $value) : preg_replace('/^(?:' . $qts . ')+|(?:' . $qts . ')+$/', '', (string) $value);
        }

        /**
         * Trims HTML whitespace.
         * This is useful on Shortcode content.
         * @param string $string Input string to trim.
         * @return string Output string with all HTML whitespace trimmed away.
         */
        public static function trim_html($string = '') {
            $whitespace = '&nbsp;|\<br\>|\<br\s*\/\>|\<p\>(?:&nbsp;)*\<\/p\>';
            return preg_replace('/^(?:' . $whitespace . ')+|(?:' . $whitespace . ')+$/', '', (string) $string);
        }

        public static function arm_set_ini_for_access_rules() {
            $memoryLimit = ini_get('memory_limit');
            if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
                if ($matches[2] == 'M') {
                    $memoryLimit = $matches[1] * 1024 * 1024;
                } else if ($matches[2] == 'K') {
                    $memoryLimit = $matches[1] * 1024;
                }
            }
            if ($memoryLimit < (256 * 1024 * 1024)) {
                /* @define('WP_MEMORY_LIMIT', '256M'); */
                @ini_set('memory_limit', '256M');
            }
            set_time_limit(0); /* Set Maximum Execution Time */
        }

        public static function arm_set_ini_for_importing_users() {
            $memoryLimit = ini_get('memory_limit');
            if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
                if ($matches[2] == 'M') {
                    $memoryLimit = $matches[1] * 1024 * 1024;
                } else if ($matches[2] == 'K') {
                    $memoryLimit = $matches[1] * 1024;
                }
            }
            if ($memoryLimit < (512 * 1024 * 1024)) {
                /* @define('WP_MEMORY_LIMIT', '256M'); */
                @ini_set('memory_limit', '512M');
            }
            set_time_limit(0); /* Set Maximum Execution Time */
        }

        function arm_add_page_label_css($hook) {
            if ('edit.php' != $hook) {
                return;
            }
            $postLabelCss = '<style type="text/css">';
            $postLabelCss .= '.arm_set_page_label, .arm_set_page_label_protected, .arm_set_page_label_drippred{display: inline-block;margin-right: 5px;padding: 3px 8px;font-size: 11px;line-height: normal;color: #fff;border-radius: 10px;-webkit-border-radius: 10px;-moz-border-radius: 10px;-o-border-radius: 10px;}';
            $postLabelCss .= ' .arm_set_page_label{background-color: #53ba73;}';
            $postLabelCss .= ' .arm_set_page_label_protected{background-color: #191111;}';
            $postLabelCss .= ' .arm_set_page_label_drippred{background-color: #e34581;}';
            $postLabelCss .= '</style>';
            echo $postLabelCss;
        }

        function arm_add_set_page_label($states, $post = null) {
            global $wpdb, $ARMember, $arm_drip_rules, $post;
            if (isset($post->ID)) {
                $str = '';
                if (get_post_type($post->ID) == 'page') {
                    $arm_page_settings = $this->arm_get_single_global_settings('page_settings');
                    if (!empty($arm_page_settings)) {
                        foreach ($arm_page_settings as $key => $value) {
                            if ($value == $post->ID) {
                                switch (strtolower($key)) {
                                    case 'register_page_id':
                                        $title_label = __('Registration page', 'ARMember');
                                        break;
                                    case 'login_page_id':
                                        $title_label = __('Login page', 'ARMember');
                                        break;
                                    case 'forgot_password_page_id':
                                        $title_label = __('Forgot Password page', 'ARMember');
                                        break;
                                    case 'edit_profile_page_id':
                                        $title_label = __('Edit Profile page', 'ARMember');
                                        break;
                                    case 'change_password_page_id':
                                        $title_label = __('Change Password page', 'ARMember');
                                        break;
                                    case 'member_profile_page_id':
                                        $title_label = __('Member Profile page', 'ARMember');
                                        break;
                                    case 'guest_page_id':
                                        $title_label = __('Guest page', 'ARMember');
                                        break;
                                }
                                if (!empty($title_label)) {
                                    $str .= '<div class="arm_set_page_label">ARMember ' . $title_label . '</div>';
                                }
                            }
                        }
                    }
                }

                $arm_protect = 0;
                $item_plans = get_post_meta($post->ID, 'arm_access_plan');
                $item_plans = (!empty($item_plans)) ? $item_plans : array();

                if (count($item_plans) == 0)
                    $arm_protect = 0;
                else
                    $arm_protect = 1;

                if (!empty($arm_protect) && $arm_protect == 1) {
                    $str .= '<div class="arm_set_page_label_protected">' . __("ARMember Protected", 'ARMember') . '</div>';
                }
                /**
                 * Check If Post Has Drip Rules
                 */
                if ($arm_drip_rules->isDripFeature) {
                    $rule_count = $wpdb->get_var("SELECT COUNT(`arm_rule_id`) FROM `" . $ARMember->tbl_arm_drip_rules . "` WHERE `arm_item_id`='" . $post->ID . "' AND `arm_rule_status`='1'");
                    if (!empty($rule_count)) {
                        $str .= '<div class="arm_set_page_label_drippred">' . __("ARMember Dripped", 'ARMember') . '</div>';
                    }
                }
                if (!empty($str)) {
                    $states[] = $str;
                }
            }
            return $states;
        }

        function arm_update_feature_settings() {
            global $wp, $wpdb, $wp_rewrite, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_feature_settings'], '1');
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['arm_features_options'])) {
                $features_options = $_POST['arm_features_options'];
                $arm_features_status = (!empty($_POST['arm_features_status'])) ? intval($_POST['arm_features_status']) : 0;

                $arm_default_module_array = array(
                    'arm_is_user_private_content_feature',
                    'arm_is_social_feature', 
                    'arm_is_opt_ins_feature', 
                    'arm_is_drip_content_feature', 
                    'arm_is_social_login_feature', 
                    'arm_is_coupon_feature', 
                    'arm_is_buddypress_feature', 
                    'arm_is_invoice_tax_feature', 
                    'arm_is_woocommerce_feature', 
                    'arm_is_multiple_membership_feature', 
                    'arm_is_mycred_feature',
                    'arm_is_pay_per_post_feature',
                    'arm_is_api_service_feature',
                );
                if(in_array($features_options, $arm_default_module_array))
                {
                    if ($arm_features_status == 1) {

                        do_action('arm_update_feature_settings', $_POST);

                        if ($features_options == 'arm_is_buddypress_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/buddypress/bp-loader.php") || file_exists( WP_PLUGIN_DIR . "/buddyboss-platform/bp-loader.php")) {
                                if (is_plugin_active('buddypress/bp-loader.php') || is_plugin_active('buddyboss-platform/bp-loader.php')) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_buddypress_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                                    echo json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'buddypress_error', 'msg' => __('Please activate BuddyPress/Buddyboss and try to active this add-on.', 'ARMember'));
                                    echo json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'buddypress_error', 'msg' => __('Please install BuddyPress/Buddyboss and try to active this add-on.', 'ARMember'));
                                echo json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_user_private_content_feature'){
                            $isPageExist = false;
                            $arm_private_content_status = (!empty($_POST['arm_features_status'])) ? intval($_POST['arm_features_status']) : 0;
                            update_option($features_options, $arm_private_content_status);
                            $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                            echo json_encode($response);
                            die();

                        } else if ($features_options == 'arm_is_social_feature') {
                            $isPageExist = false;
                            $old_member_profile_page_id = isset($this->global_settings['member_profile_page_id']) ? $this->global_settings['member_profile_page_id'] : 0;
                            if (!empty($old_member_profile_page_id) && $old_member_profile_page_id != 0) {
                                $isPageExist = true;
                                $pageData = get_post($old_member_profile_page_id);
                                if (!isset($pageData->ID) || empty($pageData->ID)) {
                                    $isPageExist = false;
                                }
                            }
                            if (!$isPageExist) {
                                $profileTemplateID = $wpdb->get_var("SELECT `arm_id` FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_type`='profile' ORDER BY `arm_id` ASC LIMIT 1");
                                $profileTemplateShortcode = (!empty($profileTemplateID)) ? '[arm_template type="profile" id="' . $profileTemplateID . '"]' : '';
                                $profilePageData = array(
                                    'post_title' => 'Profile',
                                    'post_name' => 'arm_member_profile',
                                    'post_content' => $profileTemplateShortcode,
                                    'post_status' => 'publish',
                                    'post_parent' => 0,
                                    'post_author' => 1,
                                    'post_type' => 'page',
                                );
                                $page_id = wp_insert_post($profilePageData);
                                $new_global_settings = $this->arm_get_all_global_settings();
                                $new_global_settings['page_settings']['member_profile_page_id'] = $page_id;
                                update_option('arm_global_settings', $new_global_settings);
                                $this->arm_user_rewrite_rules();
                                $wp_rewrite->flush_rules(false);
                            }
                            $arm_features_status = (!empty($_POST['arm_features_status'])) ? intval($_POST['arm_features_status']) : 0;
                            update_option($features_options, $arm_features_status);
                            $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                            echo json_encode($response);
                            die();
                        } else if($features_options == 'arm_is_multiple_membership_feature'){
                            $user_id = get_current_user_id();
                            $form_id = isset($_POST['form_id']) ? $_POST['form_id'] : '0';
                            $column_list = maybe_unserialize(get_user_meta($user_id, 'arm_members_hide_show_columns_' . $form_id, true));
                            if ($column_list != "") {
                                $total_column = count($column_list);
                                $column_list['0'] = 1;
                                $column_list['1'] = 1;
                                $column_list[$total_column - 2] = 1;
                                $column_list[$total_column - 3] = 1;
                                $members_show_hide_serialize = maybe_serialize($column_list);
                                $prev_value = maybe_unserialize(get_user_meta($user_id, 'arm_members_hide_show_columns_' . $form_id, true));
                                update_user_meta($user_id, 'arm_members_hide_show_columns_' . $form_id, $members_show_hide_serialize);
                            }
                            $arm_features_status = (!empty($_POST['arm_features_status'])) ? $_POST['arm_features_status'] : 0;
                            update_option($features_options, $arm_features_status);
                            $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                            echo json_encode($response);
                            die();
                        } else if ($features_options == 'arm_is_mycred_feature') {
                            if (file_exists(WP_PLUGIN_DIR . "/mycred/mycred.php")) {
                                if (is_plugin_active('mycred/mycred.php')) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_mycred_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                                    echo json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'mycred_error', 'msg' => __('Please activate myCRED and try to active this add-on.', 'ARMember'));
                                    echo json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'mycred_error', 'msg' => __('Please install myCRED and try to active this add-on.', 'ARMember'));
                                echo json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_pay_per_post_feature'){
                            $isPageExist = false;
                            $arm_pay_per_post_status = (!empty($_POST['arm_features_status'])) ? intval($_POST['arm_features_status']) : 0;
                            update_option($features_options, $arm_pay_per_post_status);
                            $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                            echo json_encode($response);
                            die();

                        } else {
                            $arm_features_status = (!empty($_POST['arm_features_status'])) ? $_POST['arm_features_status'] : 0;
                            update_option($features_options, $arm_features_status);
                            $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                            echo json_encode($response);
                            die();
                        }
                    } else {
                        if($_POST['arm_features_options'] == "arm_is_pay_per_post_feature")
                        {
                            $args = array(
                                'meta_query' => array(
                                    array(
                                        'key' => 'arm_user_post_ids',
                                        'value' => '',
                                        'compare' => '!='
                                    ),
                                )
                            );

                            $armDeactiveCount = 0;
                            $amTotalUsers = get_users($args);
                            if (!empty($amTotalUsers)) 
                            {
                                foreach ($amTotalUsers as $usr) 
                                {
                                    $user_id = $usr->ID;
                                    $arm_user_paid_post = get_user_meta($user_id,'arm_user_post_ids', true);

                                    if(!empty($arm_user_paid_post) && is_array($arm_user_paid_post))
                                    {
                                        $armDeactiveCount++;
                                    }
                                }
                            }

                            if($armDeactiveCount > 0)
                            {
                                $response = array('type' => 'wocommerce_error', 'msg' => __("One or more users have paid post, so addon can't be deactivated.", 'ARMember'));
                                echo json_encode($response);
                                die();
                            }                        
                        }
                        do_action('arm_deactivate_feature_settings', $_POST);
                        if($features_options == 'arm_is_invoice_tax_feature'){
                            $all_opts = maybe_unserialize(get_option('arm_global_settings'));
                            $all_opts["general_settings"]["enable_tax"] = 0;
                            $all_opts["general_settings"]["tax_amount"] = 0;
                            update_option('arm_global_settings', $all_opts);
                        } 
                        
                        
                        update_option($features_options, 0);
                        $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                        echo json_encode($response);
                        die();
                    }
                }
            } /* END `(!empty($_POST['arm_features_options']))` */
        }

        function arm_custom_css_detail() {
            global $wp, $wpdb, $arm_slugs, $current_user, $arm_errors, $ARMember, $arm_subscription_plans, $arm_drip_rules;
            $css_section = $_POST['css_section'];
            $default_select = '';
            if (!empty($css_section)) {
                if ($css_section == 'arm_general') {
                    $default_select = 'arm_account_detail';
                } else {
                    $default_select = $css_section;
                }
            }
            $membership_allow_keys = array('arm_membership_setup', 'arm_membership_setup_plans', 'arm_membership_setup_form', 'arm_membership_setup_gateways', 'arm_membership_setup_coupon', 'arm_membership_setup_summary');
            $membership_not_allow_keys = array('arm_membership_setup_plans', 'arm_membership_setup_form', 'arm_membership_setup_gateways', 'arm_membership_setup_coupon', 'arm_membership_setup_summary');
            $arm_custom_css_arr = arm_custom_css_class_info();
            if (!empty($arm_custom_css_arr)) {
                ?>
                <div class="arm_custom_css_detail_popup popup_wrapper arm_custom_css_detail_popup_wrapper">
                    <div class="popup_wrapper_inner" style="overflow: hidden;">
                        <div class="popup_header">
                            <span class="popup_close_btn arm_popup_close_btn arm_custom_css_detail_close_btn"></span>
                            <span class="add_rule_content"><?php _e('ARMember CSS Class Information', 'ARMember'); ?></span>
                        </div>
                        <div class="popup_content_text arm_custom_css_detail_popup_text">
                            <div class="arm_custom_css_detail_list">
                                <div class="arm_custom_css_detail_list_left_box">
                                    <ul>
                <?php
                foreach ($arm_custom_css_arr as $key => $css_detail) {
                    if ($css_section == 'arm_general' && in_array($key, $membership_not_allow_keys)) {
                        continue;
                    }
                    if ($css_section == 'arm_membership_setup' && !in_array($key, $membership_allow_keys)) {
                        continue;
                    }
                    ?>											
                                            <li><a class="arm_custom_css_menu_link <?php echo ($key == $default_select) ? 'active' : ''; ?>" data-custom-class="<?php echo $key; ?>"><?php echo $css_detail['section_title']['title']; ?></a></li>
                <?php } ?>
                                    </ul>
                                </div>
                                <div class="arm_custom_css_detail_list_right_box">
                <?php foreach ($arm_custom_css_arr as $key => $css_detail) { ?>
                                        <div class="arm_custom_css_detail_list_item <?php echo $key . "_section"; ?> <?php echo ($key == $default_select) ? '' : 'hidden_section'; ?>">
                                            <div class="arm_custom_css_detail_title"><?php echo $css_detail['section_title']['title']; ?></div>
                    <?php foreach ($css_detail['section_class'] as $class_detail) { ?>
                                                <div class="arm_custom_css_detail_cls"><?php echo $class_detail['class']; ?></div>
                                                <div class="arm_custom_css_detail_sub_note">
                                                    {<br><span class="arm_custom_css_detail_sub_note_text"><?php echo "// " . $class_detail['note']; ?></span><br>}
                                                </div>
                        <?php
                    }
                    ?>
                                        </div>
                    <?php
                    if ($css_section == 'arm_general' && $key == 'arm_membership_setup') {
                        foreach ($membership_not_allow_keys as $membership_not_allow) {
                            if ($membership_not_allow == 'arm_membership_setup_form') {
                                continue;
                            }
                            $setup_css_detail = $arm_custom_css_arr[$membership_not_allow];
                            ?>
                                                <div class="arm_custom_css_detail_list_item <?php echo $key . "_section"; ?> hidden_section">
                                                    <div class="arm_custom_css_detail_title"><?php echo $setup_css_detail['section_title']['title']; ?></div>
                            <?php foreach ($setup_css_detail['section_class'] as $setup_class_detail) { ?>														
                                                        <div class="arm_custom_css_detail_cls"><?php echo $setup_class_detail['class']; ?></div>
                                                        <div class="arm_custom_css_detail_sub_note">
                                                            {<br><span class="arm_custom_css_detail_sub_note_text"><?php echo "// " . $setup_class_detail['note']; ?></span><br>}
                                                        </div>
                                <?php
                            }
                            ?>
                                                </div>
                            <?php
                        }
                    }
                }
                ?>									
                                </div>								
                            </div>
                        </div>
                        <div class="armclear"></div>
                    </div>
                </div>
                                        <?php
                                    }
                                    exit;
                                }

                                function arm_section_custom_css_detail() {
                                    global $wp, $wpdb, $arm_slugs, $current_user, $arm_errors, $ARMember, $arm_subscription_plans, $arm_drip_rules;
                                    $css_section = $_POST['css_section'];
                                    $arm_custom_css_arr = arm_custom_css_class_info();
                                    if (!empty($arm_custom_css_arr[$css_section])) {
                                        $css_detail = $arm_custom_css_arr[$css_section];
                                        ?>
                <div class="arm_section_custom_css_detail_popup popup_wrapper arm_section_custom_css_detail_popup_wrapper">
                    <div class="popup_wrapper_inner" style="overflow: hidden;">
                        <div class="popup_header">
                            <span class="popup_close_btn arm_popup_close_btn arm_section_custom_css_detail_close_btn"></span>
                            <span class="add_rule_content"><?php _e('ARMember CSS Class Information', 'ARMember'); ?></span>
                        </div>
                        <div class="popup_content_text arm_section_custom_css_detail_popup_text">
                            <div class="arm_section_custom_css_detail_list">
                                <div class="arm_section_custom_css_detail_list_right_box">
                                    <div class="arm_section_custom_css_detail_list_item <?php echo $css_section . "_section"; ?>">
                                        <div class="arm_section_custom_css_detail_title"><?php echo $css_detail['section_title']['title']; ?></div>
                                    <?php foreach ($css_detail['section_class'] as $class_detail) { ?>
                                            <div class="arm_section_custom_css_detail_cls"><?php echo $class_detail['class']; ?></div>
                                            <div class="arm_section_custom_css_detail_sub_note">
                                                {<br><span class="arm_section_custom_css_detail_sub_note_text"><?php echo "// " . $class_detail['note']; ?></span><br>}
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>								
                            </div>
                        </div>
                        <div class="armclear"></div>
                    </div>
                </div>
                                    <?php
                                }
                                exit;
                            }

                            function arm_get_front_font_style() {
                                global $wp, $wpdb, $arm_slugs, $current_user, $arm_errors, $ARMember, $arm_subscription_plans, $arm_drip_rules, $arm_member_forms;
                                $frontfontstyle = array();
                                $frontFontFamilys = array();
                                $frontfontOptions = array('level_1_font', 'level_2_font', 'level_3_font', 'level_4_font', 'link_font', 'button_font');
                                $frontOptions = isset($this->global_settings['front_settings']) ? $this->global_settings['front_settings'] : array();
                                foreach ($frontfontOptions as $key) {
                                    $ffont_family = (isset($frontOptions[$key]['font_family'])) ? $frontOptions[$key]['font_family'] : "Helvetica";
				    $ffont_family = ($ffont_family == 'inherit') ? '' : $ffont_family; 
                                    $frontFontFamilys[] = $ffont_family;
                                    $ffont_size = (isset($frontOptions[$key]['font_size'])) ? $frontOptions[$key]['font_size'] : "";
                                    $ffont_color = (isset($frontOptions[$key]['font_color'])) ? $frontOptions[$key]['font_color'] : "";
                                    $ffont_bold = (isset($frontOptions[$key]['font_bold']) && $frontOptions[$key]['font_bold'] == '1') ? "font-weight: bold !important;" : "font-weight: normal !important;";
                                    $ffont_italic = (isset($frontOptions[$key]['font_italic']) && $frontOptions[$key]['font_italic'] == '1') ? "font-style: italic !important;" : "font-style: normal !important;";
                                    $ffont_decoration = (!empty($frontOptions[$key]['font_decoration'])) ? "text-decoration: " . $frontOptions[$key]['font_decoration'] . " !important;" : "text-decoration: none !important;";

                                    $front_font_family = (!empty($ffont_family)) ? "font-family: ".$ffont_family.", sans-serif, 'Trebuchet MS' !important;" : "";

                                    $frontOptions[$key]['font'] = "{$front_font_family} font-size: {$ffont_size}px !important;color: {$ffont_color} !important;{$ffont_bold}{$ffont_italic}{$ffont_decoration}";
                                }
                                $gFontUrl = $arm_member_forms->arm_get_google_fonts_url($frontFontFamilys);
                                if (!empty($gFontUrl)) {
                                    $frontfontstyle['google_font_url'] = $gFontUrl;
                                }
                                $frontfontstyle['frontOptions'] = $frontOptions;
                                return $frontfontstyle;
                            }




                            function arm_transient_set_action($arm_transient_name, $arm_transient_value, $arm_transient_time)
                            {
                                $arm_return_transient_status = 0;
                                if(!empty($arm_transient_name) && !empty($arm_transient_value) && !empty($arm_transient_time))
                                {
                                    set_transient($arm_transient_name, $arm_transient_value, $arm_transient_time);
                                    $arm_return_transient_status = 1;
                                }
                                return $arm_return_transient_status;
                            }

                            function arm_transient_get_action($arm_transient_name)
                            {
                                global $ARMember;
                                $arm_return_transient_status = 0;
                                if(!empty($arm_transient_name))
                                {
                                    $arm_get_transient_value = get_transient($arm_transient_name);
                                    if(!empty($arm_get_transient_value))
                                    {
                                        $arm_return_transient_status = 1;
                                    }
                                }
                                return $arm_return_transient_status;
                            }

                        }

                    }
                    global $arm_global_settings;
                    $arm_global_settings = new ARM_global_settings();
                    if (!function_exists('arm_generate_random_code')) {

                        function arm_generate_random_code($length = 10) {
                            $charLength = round($length * 0.8);
                            $numLength = round($length * 0.2);
                            $keywords = array(
                                array('count' => $charLength, 'char' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                                array('count' => $numLength, 'char' => '0123456789')
                            );
                            $temp_array = array();
                            foreach ($keywords as $char_set) {
                                for ($i = 0; $i < $char_set['count']; $i++) {
                                    $temp_array[] = $char_set['char'][rand(0, strlen($char_set['char']) - 1)];
                                }
                            }
                            shuffle($temp_array);
                            return implode('', $temp_array);
                        }

                    }

                    if (!function_exists('arm_generate_captcha_code')) {

                        function arm_generate_captcha_code($length = 8) {
                            $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
                            $random_dots = 0;
                            $random_lines = 20;
                            $code = '';
                            $i = 0;
                            while ($i < $length) {
                                $code .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
                                $i++;
                            }
                            return $code;
                        }

                    }

                    if (!function_exists('add_arm_term_meta')) {

                        /**
                         * Add meta data field to a term.
                         *
                         * @param int $term_id Post ID.
                         * @param string $key Metadata name.
                         * @param mixed $value Metadata value.
                         * @param bool $unique Optional, default is false. Whether the same key should not be added.
                         * @return bool False for failure. True for success.
                         */
                        function add_arm_term_meta($term_id, $meta_key, $meta_value, $unique = false) {
                            return add_metadata('arm_term', $term_id, $meta_key, $meta_value, $unique);
                        }

                    }
                    if (!function_exists('delete_arm_term_meta')) {

                        /**
                         * Remove metadata matching criteria from a term.
                         *
                         * You can match based on the key, or key and value. Removing based on key and
                         * value, will keep from removing duplicate metadata with the same key. It also
                         * allows removing all metadata matching key, if needed.
                         *
                         * @param int $term_id term ID
                         * @param string $meta_key Metadata name.
                         * @param mixed $meta_value Optional. Metadata value.
                         * @return bool False for failure. True for success.
                         */
                        function delete_arm_term_meta($term_id, $meta_key, $meta_value = '') {
                            return delete_metadata('arm_term', $term_id, $meta_key, $meta_value);
                        }

                    }
                    if (!function_exists('get_arm_term_meta')) {

                        /**
                         * Retrieve term meta field for a term.
                         *
                         * @param int $term_id Term ID.
                         * @param string $key The meta key to retrieve.
                         * @param bool $single Whether to return a single value.
                         * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
                         *  is true.
                         */
                        function get_arm_term_meta($term_id, $key, $single = false) {
                            return get_metadata('arm_term', $term_id, $key, $single);
                        }

                    }
                    if (!function_exists('update_arm_term_meta')) {

                        /**
                         * Update term meta field based on term ID.
                         *
                         * Use the $prev_value parameter to differentiate between meta fields with the
                         * same key and term ID.
                         *
                         * If the meta field for the term does not exist, it will be added.
                         *
                         * @param int $term_id Term ID.
                         * @param string $key Metadata key.
                         * @param mixed $value Metadata value.
                         * @param mixed $prev_value Optional. Previous value to check before removing.
                         * @return bool False on failure, true if success.
                         */
                        function update_arm_term_meta($term_id, $meta_key, $meta_value, $prev_value = '') {
                            return update_metadata('arm_term', $term_id, $meta_key, $meta_value, $prev_value);
                        }

                    }
                    if (!function_exists('armXML_to_Array')) {

                        /**
                         * Convert XML File Data Into Array
                         * @param type $content (xml file content)
                         */
                        function armXML_to_Array($contents, $get_attributes = 1, $priority = 'tag') {
                            if (!$contents) {
                                return array();
                            }
                            if (!function_exists('xml_parser_create')) {
                                /* print "'xml_parser_create()' function not found!"; */
                                return array();
                            }
                            /* Get the XML parser of PHP - PHP must have this module for the parser to work */
                            $parser = xml_parser_create('');
                            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
                            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                            xml_parse_into_struct($parser, trim($contents), $xml_values);
                            xml_parser_free($parser);
                            if (!$xml_values) {
                                return;
                            }
                            /* Initializations */
                            $xml_array = array();
                            $parents = array();
                            $opened_tags = array();
                            $arr = array();

                            $current = &$xml_array; /* Refference */

                            $repeated_tag_index = array(); /* Multiple tags with same name will be turned into an array */
                            foreach ($xml_values as $data) {
                                unset($attributes, $value); /* Remove existing values, or there will be trouble */
                                /**
                                 * This command will extract these variables into the foreach scope tag(string), type(string), level(int), attributes(array).
                                 */
                                extract($data);
                                $result = array();
                                $attributes_data = array();
                                if (isset($value)) {
                                    if ($priority == 'tag') {
                                        $result = $value;
                                    } else {
                                        $result['value'] = $value; /* Put the value in a assoc array if we are in the 'Attribute' mode */
                                    }
                                }
                                /* Set the attributes too. */
                                if (isset($attributes) and $get_attributes) {
                                    foreach ($attributes as $attr => $val) {
                                        if ($priority == 'tag') {
                                            $attributes_data[$attr] = $val;
                                        } else {
                                            $result['attr'][$attr] = $val; /* Set all the attributes in a array called 'attr' */
                                        }
                                    }
                                }
                                /* See tag status and do the needed. */
                                if ($type == "open") {
                                    /* The starting of the tag '<tag>' */
                                    $parent[$level - 1] = &$current;
                                    if (!is_array($current) or ( !in_array($tag, array_keys($current)))) {
                                        $current[$tag] = $result;
                                        if ($attributes_data)
                                            $current[$tag . '_attr'] = $attributes_data;
                                        $repeated_tag_index[$tag . '_' . $level] = 1;

                                        $current = &$current[$tag];
                                    } else {
                                        /* There was another element with the same tag name */
                                        if (isset($current[$tag][0])) {
                                            /* If there is a 0th element it is already an array */
                                            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                                            $repeated_tag_index[$tag . '_' . $level] ++;
                                        } else {
                                            /* This section will make the value an array if multiple tags with the same name appear together */
                                            /* This will combine the existing item and the new item together to make an array */
                                            $current[$tag] = array($current[$tag], $result);
                                            $repeated_tag_index[$tag . '_' . $level] = 2;

                                            if (isset($current[$tag . '_attr'])) {
                                                /* The attribute of the last(0th) tag must be moved as well */
                                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                                unset($current[$tag . '_attr']);
                                            }
                                        }
                                        $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                                        $current = &$current[$tag][$last_item_index];
                                    }
                                } elseif ($type == "complete") {
                                    /* Tags that ends in 1 line '<tag />' */
                                    /* See if the key is already taken. */
                                    if (!isset($current[$tag])) {
                                        $current[$tag] = $result;
                                        $repeated_tag_index[$tag . '_' . $level] = 1;
                                        if ($priority == 'tag' and $attributes_data)
                                            $current[$tag . '_attr'] = $attributes_data;
                                    } else {
                                        /* If taken, put all things inside a list(array) */
                                        if (isset($current[$tag][0]) and is_array($current[$tag])) {
                                            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                                            if ($priority == 'tag' and $get_attributes and $attributes_data) {
                                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                                            }
                                            $repeated_tag_index[$tag . '_' . $level] ++;
                                        } else {
                                            $current[$tag] = array($current[$tag], $result);
                                            $repeated_tag_index[$tag . '_' . $level] = 1;
                                            if ($priority == 'tag' and $get_attributes) {
                                                if (isset($current[$tag . '_attr'])) {
                                                    $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                                    unset($current[$tag . '_attr']);
                                                }
                                                if ($attributes_data) {
                                                    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                                                }
                                            }
                                            $repeated_tag_index[$tag . '_' . $level] ++; /* 0 and 1 index is already taken */
                                        }
                                    }
                                } elseif ($type == 'close') {
                                    /* End of tag '</tag>' */
                                    $current = &$parent[$level - 1];
                                }
                            }
                            return $xml_array;
                        }

                    }
                    if (!function_exists('arm_custom_css_class_info')) {

                        function arm_custom_css_class_info() {
                            $arm_custom_css_info = apply_filters('arm_available_css_info', array(
                                'arm_account_detail' => array(
                                    'section_title' => array(
                                        'title' => __('My Profile', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on your profile', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_account_detail_wrapper',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_account_detail_tab_content_wrapper',
                                            'note' => __('It will apply on tabs detail wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_account_detail_tab_content',
                                            'note' => __('It will apply on specific tab detail content wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_account_detail_tab_heading',
                                            'note' => __('It will apply on specific tab content heading.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_account_detail_tab_body',
                                            'note' => __('It will apply on specific tab content body wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_member_detail_action_links',
                                            'note' => __('It will apply on member action wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_member_detail_action_links a',
                                            'note' => __('It will apply on member action links.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_view_profile_wrapper',
                                            'note' => __('It will apply on profile wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_view_profile_wrapper table',
                                            'note' => __('It will apply on table of profile.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_view_profile_wrapper table tr',
                                            'note' => __('It will apply on row of profile table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_view_profile_wrapper table th',
                                            'note' => __('It will apply on header of profile table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_view_profile_wrapper table td',
                                            'note' => __('It will apply on item of profile table.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_member_transaction' => array(
                                    'section_title' => array(
                                        'title' => __('Payment Transaction', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on payment transaction', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_transactions_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transactions_heading_main',
                                            'note' => __('It will apply on heading.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transaction_form_container',
                                            'note' => __('It will apply on form container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transactions_wrapper',
                                            'note' => __('It will apply on wrapper of transactions list.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_transaction_list_table',
                                            'note' => __('It will apply on table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transaction_list_header',
                                            'note' => __('It will apply on header(tr).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transaction_list_item',
                                            'note' => __('It will apply on item(td).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_transaction_paging_container',
                                            'note' => __('It will apply on pagination wrapper.', 'ARMember'),
                                        )
                                    )
                                ),
                                'arm_current_membership' => array(
                                    'section_title' => array(
                                        'title' => __('Current Membership', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on current membership', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_front_edit_subscriptions_link',
                                            'note' => __('It will apply on change membership link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_subscriptions_wrapper',
                                            'note' => __('It will apply on membership detail wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_subscriptions_wrapper table',
                                            'note' => __('It will apply on table of membership detail.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_subscriptions_wrapper table tr',
                                            'note' => __('It will apply on row of membership table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_subscriptions_wrapper table th',
                                            'note' => __('It will apply on header of membership table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_subscriptions_wrapper table td',
                                            'note' => __('It will apply on item of membership table.', 'ARMember'),
                                        )
                                    )
                                ),
                                'arm_close_account' => array(
                                    'section_title' => array(
                                        'title' => __('Close Account', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on close account', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_close_account_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_close_account_form_container',
                                            'note' => __('It will apply on form wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_close_account',
                                            'note' => __('It will apply on form.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_inner_container',
                                            'note' => __('It will apply on form inner wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_message_container',
                                            'note' => __('It will apply on error / success message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_close_account_message',
                                            'note' => __('It will apply on message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_wrapper_container',
                                            'note' => __('It will apply on form fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_container',
                                            'note' => __('It will apply on specific field container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_label_wrapper',
                                            'note' => __('It will apply on field label wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_label_text',
                                            'note' => __('It will apply on field label text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_wrapper',
                                            'note' => __('It will apply on input field wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_box',
                                            'note' => __('It will apply on input field.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_close_account_btn',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        )
                                    ),
                                ),
                                'arm_cancel_membership' => array(
                                    'section_title' => array(
                                        'title' => __('Cancel Subscription', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on cancel Subscription', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_cancel_membership_form_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_cancel_membership_link (arm_cancel_membership_btn)',
                                            'note' => __('It will apply on submit link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_cancel_membership_button (arm_cancel_membership_btn)',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_message_container',
                                            'note' => __('It will apply on error / success message wrapper.', 'ARMember'),
                                        )
                                    ),
                                ),
                                'arm_form' => array(
                                    'section_title' => array(
                                        'title' => __('Other Forms / Edit Profile', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on other forms and edit profile', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_member_form_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_shortcode_form',
                                            'note' => __('It will apply on form.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_edit_profile',
                                            'note' => __('It will apply on edit profile form.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_inner_container',
                                            'note' => __('It will apply on form inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_message_container',
                                            'note' => __('It will apply on error / success message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_wrapper_container',
                                            'note' => __('It will apply on form fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_wrapper_container_edit_profile',
                                            'note' => __('It will apply on edit profile form fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_heading_container',
                                            'note' => __('It will apply on form title (not for popup).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_container',
                                            'note' => __('It will apply on specific field container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_container_{type}',
                                            'note' => __('It will apply on specific field type container (type: text, email, radio, checkbox, select, avatar, file, submit, etc...).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_label_wrapper',
                                            'note' => __('It will apply on field label wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.required_tag',
                                            'note' => __('It will apply on required text (*).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_label_text',
                                            'note' => __('It will apply on field label text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_material_label',
                                            'note' => __('It will apply on field label text (material).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_wrapper',
                                            'note' => __('It will apply on input field wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_container',
                                            'note' => __('It will apply on input inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_container_{type}',
                                            'note' => __('It will apply on specific input field type container (type: text, email, radio, checkbox, select, avatar, file, submit, etc...).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_box',
                                            'note' => __('It will apply on input field.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_submit_button',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg_box',
                                            'note' => __('It will apply on field error message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg',
                                            'note' => __('It will apply on field error message text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_pass_strength_meter',
                                            'note' => __('It will apply on password strength meter wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_strength_meter_block',
                                            'note' => __('It will apply on password strength meter block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_strength_meter_label',
                                            'note' => __('It will apply on password strength meter block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_popup_member_form (arm_popup_wrapper)',
                                            'note' => __('It will apply on popup form main wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.popup_wrapper_inner',
                                            'note' => __('It will apply on popup inner wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.popup_header',
                                            'note' => __('It will apply on popup header wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.popup_close_btn',
                                            'note' => __('It will apply on popup close wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.popup_header_text',
                                            'note' => __('It will apply on popup heading(title).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.popup_content_text',
                                            'note' => __('It will apply on popup body wrapper.', 'ARMember'),
                                        )
                                    ),
                                ),
                                'arm_logout' => array(
                                    'section_title' => array(
                                        'title' => __('Logout', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on logout', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_logout_form_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_logout_link (arm_logout_btn)',
                                            'note' => __('It will apply on submit link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_logout_button (arm_logout_btn)',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm-logged-in-as',
                                            'note' => __('It will apply on logged in as text wrapper.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_membership_setup' => array(
                                    'section_title' => array(
                                        'title' => __('Membership Setup Wizard', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_setup_form_container',
                                            'note' => __('It will apply on main container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_form_title',
                                            'note' => __('It will apply on heading.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_messages.arm_form_message_container',
                                            'note' => __('It will apply on message container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_membership_setup_form',
                                            'note' => __('It will apply on membership setup wizard form.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_form_inner_container',
                                            'note' => __('It will apply on membership setup wizard form inner wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_submit_btn_wrapper',
                                            'note' => __('It will apply on submit button wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_submit_btn',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_membership_setup_plans' => array(
                                    'section_title' => array(
                                        'title' => __('Plans Section', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard plan section', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_module_plans_container',
                                            'note' => __('It will apply on container of membership setup wizard plans.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_plans_ul',
                                            'note' => __('It will apply on plans list.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_column_item',
                                            'note' => __('It will apply on plans list item.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_column_item.arm_active',
                                            'note' => __('It will apply on selected plan.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_plan_option',
                                            'note' => __('It will apply on label of plans list.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_plan_name',
                                            'note' => __('It will apply on name of plan.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_plan_description',
                                            'note' => __('It will apply on description of plan.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_membership_setup_form' => array(
                                    'section_title' => array(
                                        'title' => __('Form Section', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard form section', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_shortcode_form',
                                            'note' => __('It will apply on setup form.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_inner_container',
                                            'note' => __('It will apply on setup form inner wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_message_container',
                                            'note' => __('It will apply on error / success message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_wrapper_container',
                                            'note' => __('It will apply on setup form fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_heading_container',
                                            'note' => __('It will apply on form title (not for popup).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_container',
                                            'note' => __('It will apply on specific field container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_container_{type}',
                                            'note' => __('It will apply on specific field type container (type: text, email, radio, checkbox, select, avatar, file, submit, etc...).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_label_wrapper',
                                            'note' => __('It will apply on field label wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.required_tag',
                                            'note' => __('It will apply on required text (*).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_label_text',
                                            'note' => __('It will apply on field label text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_material_label',
                                            'note' => __('It will apply on field label text (material).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_wrapper',
                                            'note' => __('It will apply on input field wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_container',
                                            'note' => __('It will apply on input inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_container_{type}',
                                            'note' => __('It will apply on specific input field type container (type: text, email, radio, checkbox, select, avatar, file, submit, etc...).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_box',
                                            'note' => __('It will apply on input field.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_submit_button',
                                            'note' => __('It will apply on submit button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg_box',
                                            'note' => __('It will apply on field error message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg',
                                            'note' => __('It will apply on field error message text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_pass_strength_meter',
                                            'note' => __('It will apply on password strength meter wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_strength_meter_block',
                                            'note' => __('It will apply on password strength meter block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_strength_meter_label',
                                            'note' => __('It will apply on password strength meter label.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_membership_setup_gateways' => array(
                                    'section_title' => array(
                                        'title' => __('Payment Gateways Section', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard payment gateways section', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_module_gateways_container',
                                            'note' => __('It will apply on container of payment gateways.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_gateways_ul',
                                            'note' => __('It will apply on payment gateways list.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_gateway_paypal',
                                            'note' => __('It will apply on paypal.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_gateway_stripe',
                                            'note' => __('It will apply on stripe.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_gateway_authorize_net',
                                            'note' => __('It will apply on authorize net.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_gateway_2checkout',
                                            'note' => __('It will apply on 2checkout.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_gateway_bank_transfer',
                                            'note' => __('It will apply on bank transfer.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_column_item',
                                            'note' => __('It will apply on payment gateways list item.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_column_item.arm_active',
                                            'note' => __('It will apply on selected payment gateway.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_gateway_option',
                                            'note' => __('It will apply on label of payment gateways list.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_check_circle',
                                            'note' => __('It will apply on checked circle of selected payment gateway.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_check_circle i',
                                            'note' => __('It will apply on checked circle icon of selected payment gateway.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_gateway_name',
                                            'note' => __('It will apply on name of payment gateway.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_module_gateway_fields',
                                            'note' => __('It will apply on payment gateway fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_cc_fields_container',
                                            'note' => __('It will apply on credit card fields container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_cc_field_wrapper',
                                            'note' => __('It will apply on credit card fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.payment-errors',
                                            'note' => __('It will apply on payment errors.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_bank_transfer_fields_container',
                                            'note' => __('It will apply on bank transfer fields container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_bt_field_wrapper',
                                            'note' => __('It will apply on bank transfer fields wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_label_wrapper',
                                            'note' => __('It will apply on field label wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_field_label_text',
                                            'note' => __('It will apply on field label text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_wrapper',
                                            'note' => __('It will apply on input field wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_form_input_container',
                                            'note' => __('It will apply on input inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_material_label',
                                            'note' => __('It will apply on field label text (material).', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg_box',
                                            'note' => __('It will apply on field error message wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_error_msg',
                                            'note' => __('It will apply on field error message text.', 'ARMember'),
                                        ),
                                    ),
                                ),
                                'arm_membership_setup_coupon' => array(
                                    'section_title' => array(
                                        'title' => __('Coupon & Amount Section', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard Coupon section', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_module_coupons_container',
                                            'note' => __('It will apply on main container of coupon section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_apply_coupon_container',
                                            'note' => __('It will apply on inner container of coupon section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_apply_coupon_container label',
                                            'note' => __('It will apply on label of coupon section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_coupon_code',
                                            'note' => __('It will apply on input field.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_apply_coupon_btn',
                                            'note' => __('It will apply on coupon apply button.', 'ARMember'),
                                        ),
                                    )
                                ),
                                'arm_membership_setup_summary' => array(
                                    'section_title' => array(
                                        'title' => __('Summary Section', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership setup wizard Summary section', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_setup_summary_text_container',
                                            'note' => __('It will apply on main container of summary section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_summary_text',
                                            'note' => __('It will apply on inner container of summary section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_setup_summary_text div',
                                            'note' => __('It will apply on text of summary section.', 'ARMember'),
                                        ),
                                    )
                                ),
                                'arm_directory' => array(
                                    'section_title' => array(
                                        'title' => __('Member Directory', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on member directory', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_template_wrapper',
                                            'note' => __('It will apply on template main wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_directorytemplate1',
                                            'note' => __('It will apply on main wrapper of directory template1.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_directorytemplate2',
                                            'note' => __('It will apply on main wrapper of directory template2.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_directorytemplate3',
                                            'note' => __('It will apply on main wrapper of directory template3.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_form_container',
                                            'note' => __('It will apply on directory form container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_filters_wrapper',
                                            'note' => __('It will apply on filters wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_search_wrapper',
                                            'note' => __('It will apply on search wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_search_box',
                                            'note' => __('It will apply on search input field.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_search_btn',
                                            'note' => __('It will apply on search submit button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_list_of_filters',
                                            'note' => __('It will apply on list of wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_search_filter_title_label',
                                            'note' => __('It will apply on Filters title.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_search_filter_fields_wrapper_top .arm_search_filter_field_item_top',
                                            'note' => __('It will apply on Filters Field width.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_search_filter_container_type_0',
                                            'note' => __('It will apply on Single Search type filter.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_search_filter_container_type_1',
                                            'note' => __('It will apply on Multiple Search type filter.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_button_search_filter_btn_div_top',
                                            'note' => __('It will apply on set position of Search and Reset button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_list_of_filters label',
                                            'note' => __('It will apply on list of label.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_list_of_filters .arm_active',
                                            'note' => __('It will apply on active list button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_list_by_filters',
                                            'note' => __('It will apply on list by wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_listby_select',
                                            'note' => __('It will apply on select box of list by user.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_listby_select option',
                                            'note' => __('It will apply on options of list by user.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_container',
                                            'note' => __('It will apply on template container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_container',
                                            'note' => __('It will apply on directory template container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_block',
                                            'note' => __('It will apply on user block of lists.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_block_left',
                                            'note' => __('It will apply on left block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_block_right',
                                            'note' => __('It will apply on left block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_dp_user_link',
                                            'note' => __('It will apply on user avatar link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_avatar',
                                            'note' => __('It will apply on user avatar section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_avatar img',
                                            'note' => __('It will apply on user avatar image.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_link',
                                            'note' => __('It will apply on username.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_badges_detail',
                                            'note' => __('It will apply on user badges section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm-user-badge',
                                            'note' => __('It will apply on user badges items lists.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm-user-badge img',
                                            'note' => __('It will apply on user badges image.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_paging_container',
                                            'note' => __('It will apply on paging container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_directory_load_more_btn',
                                            'note' => __('It will apply on load more link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_paging_wrapper_directory',
                                            'note' => __('It will apply on paging inner wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_paging_info',
                                            'note' => __('It will apply on paging info.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_paging_links',
                                            'note' => __('It will apply on paging links.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_prev',
                                            'note' => __('It will apply on paging previous link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.current',
                                            'note' => __('It will apply on paging current link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_page_numbers',
                                            'note' => __('It will apply on paging numbers link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_page_numbers.dots',
                                            'note' => __('It will apply on paging dots link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_next',
                                            'note' => __('It will apply on paging next link.', 'ARMember'),
                                        ),
                                    )
                                ),
                                'arm_profile' => array(
                                    'section_title' => array(
                                        'title' => __('Public Profile', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on public profile', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_template_wrapper',
                                            'note' => __('It will apply on template main wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_profiletemplate1',
                                            'note' => __('It will apply on main wrapper of profile template1.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_profiletemplate2',
                                            'note' => __('It will apply on main wrapper of profile template2.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_wrapper_profiletemplate3',
                                            'note' => __('It will apply on main wrapper of profile template3.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_template_container',
                                            'note' => __('It will apply on template container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_container',
                                            'note' => __('It will apply on profile template container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_detail_wrapper',
                                            'note' => __('It will apply on profile detail container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_picture_block',
                                            'note' => __('It will apply on picture block container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_picture_block_inner',
                                            'note' => __('It will apply on picture block inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_header_top_box',
                                            'note' => __('It will apply on header top block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_avatar',
                                            'note' => __('It will apply on user avatar section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_avatar img',
                                            'note' => __('It will apply on user avatar image.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_header_info',
                                            'note' => __('It will apply on header info block.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_name_link',
                                            'note' => __('It will apply on username.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_name_link a',
                                            'note' => __('It will apply on username link.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_badges_detail',
                                            'note' => __('It will apply on user badges section.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm-user-badge',
                                            'note' => __('It will apply on user badges items lists.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm-user-badge img',
                                            'note' => __('It will apply on user badges image.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_last_active_text',
                                            'note' => __('It will apply on last login detail text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_item_status_text',
                                            'note' => __('It will apply on last login status text.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_user_about_me',
                                            'note' => __('It will apply on user info.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_cover_upload_container',
                                            'note' => __('It will apply on cover upload container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.armCoverUploadBtnContainer',
                                            'note' => __('It will apply on cover upload inner container.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.armCoverUploadBtn',
                                            'note' => __('It will apply on cover upload button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.armRemoveCover',
                                            'note' => __('It will apply on cover remove button.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_defail_container',
                                            'note' => __('It will apply on main container of profile defail.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_tab_detail',
                                            'note' => __('It will apply on specific tab content wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_general_info_container',
                                            'note' => __('It will apply on profile wrapper.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_detail_tbl',
                                            'note' => __('It will apply on table of profile.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_detail_tbl tr',
                                            'note' => __('It will apply on row of profile table.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_profile_detail_tbl td',
                                            'note' => __('It will apply on item of profile table.', 'ARMember'),
                                        ),
                                    )
                                ),
                                'arm_membership_card' => array(
                                    'section_title' => array(
                                        'title' => __('Membership Card', 'ARMember'),
                                        'note' => __('Please use following css class if you want to add custom property on membership card', 'ARMember'),
                                    ),
                                    'section_class' => array(
                                        array(
                                            'class' => '.arm_card_background',
                                            'note' => __('It will apply on card background.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_card_title',
                                            'note' => __('It will apply on card title.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_card_left_logo',
                                            'note' => __('It will apply on card logo.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_card_label',
                                            'note' => __('It will apply on card label.', 'ARMember'),
                                        ),
                                        array(
                                            'class' => '.arm_card_value',
                                            'note' => __('It will apply on card value.', 'ARMember'),
                                        ),
                                    )
                                ),
                            ));
                            return $arm_custom_css_info;
                        }
                    }

                    if (!function_exists('arm_array_map')) {

                        function arm_array_map($input = array()) {
                            if (empty($input)) {
                                return $input;
                            }

                            return is_array($input) ? array_map('arm_array_map', $input) : trim($input);
                        }

                    }

                    if (!function_exists('arm_wp_date_format_to_bootstrap_datepicker')) {

                        function arm_wp_date_format_to_bootstrap_datepicker($date_format = '') {
                            if ($date_format == '') {
                                $date_format = get_option('date_format');
                            }

                            $SYMBOLS_MATCHING = array(
                                'd' => 'DD',
                                'D' => 'ddd',
                                'j' => 'D',
                                'l' => 'dddd',
                                'N' => '',
                                'S' => '',
                                'w' => '',
                                'z' => 'o',
                                'W' => '',
                                'F' => 'MMMM',
                                'm' => 'MM',
                                'M' => 'M',
                                'n' => 'm',
                                't' => '',
                                'L' => '',
                                'o' => '',
                                'Y' => 'YYYY',
                                'y' => 'y',
                                'a' => '',
                                'A' => '',
                                'B' => '',
                                'g' => '',
                                'G' => '',
                                'h' => '',
                                'H' => '',
                                'i' => '',
                                's' => '',
                                'u' => ''
                            );
                            $jqueryui_format = "";
                            $escaping = false;
                            for ($i = 0; $i < strlen($date_format); $i++) {
                                $char = $date_format[$i];
                                if ($char === '\\') { // PHP date format escaping character
                                    $i++;
                                    if ($escaping)
                                        $jqueryui_format .= $date_format[$i];
                                    else
                                        $jqueryui_format .= '\'' . $date_format[$i];
                                    $escaping = true;
                                }
                                else {
                                    if ($escaping) {
                                        $jqueryui_format .= "'";
                                        $escaping = false;
                                    }
                                    if (isset($SYMBOLS_MATCHING[$char]))
                                        $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                                    else
                                        $jqueryui_format .= $char;
                                }
                            }

                            return $jqueryui_format;
                        }

                    }

                    if (!function_exists('arm_strtounicode')) {

                        function arm_strtounicode($str = '') {
                            if ($str == '') {
                                return $str;
                            }

                            return preg_replace_callback("([\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}|[\xF8-\xFB][\x80-\xBF]{4}|[\xFC-\xFD][\x80-\xBF]{5})", function($m) {
                                $c = $m[0];
                                $out = bindec(ltrim(decbin(ord($c[0])), "1"));
                                $l = strlen($c);
                                for ($i = 1; $i < $l; $i++) {
                                    $out = ($out << 6) | bindec(ltrim(decbin(ord($c[$i])), "1"));
                                }
                                if ($out < 256)
                                    return chr($out);
                                return "&#" . $out . ";";
                            }, $str);
                        }

                    }
                    if( !function_exists('arm_check_date_format') ){

                        function arm_check_date_format($date_value,$key = 0){
                            $date_formats = array(
                                'd/m/Y',
                                'm/d/Y',
                                'Y/m/d',
                                'M d, Y',
                                'F d, Y',
                                'd M, Y',
                                'd F, Y',
                                'Y, M d',
                                'Y, F d'
                            );
                            $final_date_format = false;
                            foreach($date_formats as $k => $format){
                                if( DateTime::createFromFormat($format,$date_value) ){
                                    $final_date_format = DateTime::createFromFormat($format,$date_value);
                                    break;
                            }

                            }
                            if( $final_date_format == "" || empty($final_date_format)){
                                try{
                                    $final_date_format = new DateTime($date_value);
                                } catch(Exception $e){
                                    $date_value = str_replace('/', '-', $date_value);
                                    $final_date_format = new DateTime($date_value);
                                }
                            }
                            return $final_date_format;
                        }
                    }