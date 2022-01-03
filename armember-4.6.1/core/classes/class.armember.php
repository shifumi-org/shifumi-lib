<?php

$arm_geoip_file = MEMBERSHIP_LIBRARY_DIR.'/geoip/autoload.php';
if( file_exists($arm_geoip_file) ){
    include $arm_geoip_file;
}
use GeoIp2\Database\Reader;

if (!class_exists('ARMember')) {
   class ARMember {

    var $arm_slugs;
    var $tbl_arm_activity;
    var $tbl_arm_auto_message;
    var $tbl_arm_coupons;
    var $tbl_arm_email_templates;
    var $tbl_arm_entries;
    var $tbl_arm_fail_attempts;
    var $tbl_arm_forms;
    var $tbl_arm_form_field;
    var $tbl_arm_lockdown;
    var $tbl_arm_members;
    var $tbl_arm_membership_setup;
    var $tbl_arm_payment_log;
    var $tbl_arm_bank_transfer_log;
    var $tbl_arm_subscription_plans;
    var $tbl_arm_termmeta;
    var $tbl_arm_member_templates;
    var $tbl_arm_drip_rules;
    var $tbl_arm_badges_achievements;
    var $tbl_arm_login_history;
    var $tbl_arm_debug_payment_log;
    var $tbl_arm_debug_general_log;


    function __construct() {
        global $wp, $wpdb, $arm_db_tables, $arm_access_rules, $arm_capabilities_global;

        $arm_db_tables = array(
            'tbl_arm_activity' => $wpdb->prefix . 'arm_activity',
            'tbl_arm_auto_message' => $wpdb->prefix . 'arm_auto_message',
            'tbl_arm_coupons' => $wpdb->prefix . 'arm_coupons',
            'tbl_arm_email_templates' => $wpdb->prefix . 'arm_email_templates',
            'tbl_arm_entries' => $wpdb->prefix . 'arm_entries',
            'tbl_arm_fail_attempts' => $wpdb->prefix . 'arm_fail_attempts',
            'tbl_arm_forms' => $wpdb->prefix . 'arm_forms',
            'tbl_arm_form_field' => $wpdb->prefix . 'arm_form_field',
            'tbl_arm_lockdown' => $wpdb->prefix . 'arm_lockdown',
            'tbl_arm_members' => $wpdb->prefix . 'arm_members',
            'tbl_arm_membership_setup' => $wpdb->prefix . 'arm_membership_setup',
            'tbl_arm_payment_log' => $wpdb->prefix . 'arm_payment_log',
            'tbl_arm_bank_transfer_log' => $wpdb->prefix . 'arm_bank_transfer_log',
            'tbl_arm_subscription_plans' => $wpdb->prefix . 'arm_subscription_plans',
            'tbl_arm_termmeta' => $wpdb->prefix . 'arm_termmeta',
            'tbl_arm_member_templates' => $wpdb->prefix . 'arm_member_templates',
            'tbl_arm_drip_rules' => $wpdb->prefix . 'arm_drip_rules',
            'tbl_arm_badges_achievements' => $wpdb->prefix . 'arm_badges_achievements',
            'tbl_arm_login_history' => $wpdb->prefix . 'arm_login_history',
            'tbl_arm_debug_payment_log' => $wpdb->prefix . 'arm_debug_payment_log',
            'tbl_arm_debug_general_log' => $wpdb->prefix . 'arm_debug_general_log',
        );
        /* Set Database Table Variables. */
        foreach ($arm_db_tables as $key => $table) {
            $this->$key = $table;
        }

        /* Set Page Slugs Global */
        $this->arm_slugs = $this->arm_page_slugs();
        /* Set Page Capabilities Global */
        $arm_capabilities_global = array(
            'arm_manage_members' => 'arm_manage_members',
            'arm_manage_plans' => 'arm_manage_plans',
            'arm_manage_setups' => 'arm_manage_setups',
            'arm_manage_forms' => 'arm_manage_forms',
            'arm_manage_access_rules' => 'arm_manage_access_rules',
            'arm_manage_drip_rules' => 'arm_manage_drip_rules',
            'arm_manage_transactions' => 'arm_manage_transactions',
            'arm_manage_email_notifications' => 'arm_manage_email_notifications',
            'arm_manage_communication' => 'arm_manage_communication',
            'arm_manage_member_templates' => 'arm_manage_member_templates',
            'arm_manage_general_settings' => 'arm_manage_general_settings',
            'arm_manage_private_content' => 'arm_manage_private_content',
            'arm_manage_pay_per_post' => 'arm_manage_pay_per_post',
            'arm_manage_feature_settings' => 'arm_manage_feature_settings',
            'arm_manage_block_settings' => 'arm_manage_block_settings',
            'arm_manage_coupons' => 'arm_manage_coupons',
            'arm_manage_payment_gateways' => 'arm_manage_payment_gateways',
            'arm_import_export' => 'arm_import_export',
            'arm_badges' => 'arm_badges',
            'arm_report_analytics' => 'arm_report_analytics',
        );

        register_activation_hook(MEMBERSHIP_DIR.'/armember.php', array('ARMember', 'install'));
        register_activation_hook(MEMBERSHIP_DIR.'/armember.php', array('ARMember', 'armember_check_network_activation'));

        /* Load Language TextDomain */
        


        /* Add 'Addon' link in plugin list */
        add_filter('plugin_action_links', array($this, 'armPluginActionLinks'), 10, 2);
        /* Hide Update Notification */
        //add_action('admin_init', array($this, 'arm_hide_update_notice'), 1);
        /* Init Hook */
        //add_action('init', array($this, 'arm_init_action'));
        //add_action('init', array($this, 'wpdbfix'));
        add_action('switch_blog', array($this, 'wpdbfix'));
	//Query monitor
        //add_action('admin_init', array($this, 'arm_install_plugin_data'), 1000);
        


        /*add_action('admin_body_class', array($this, 'arm_admin_body_class'));
        add_action('admin_menu', array($this, 'arm_menu'), 27);
        add_action('admin_menu', array($this, 'arm_set_last_menu'), 50);
        add_action('admin_bar_menu', array($this, 'arm_add_debug_bar_menu'), 999);
        add_action('admin_enqueue_scripts', array($this, 'set_css'), 11);
        add_action('admin_enqueue_scripts', array($this, 'set_js'), 10);
        add_action('admin_enqueue_scripts', array($this, 'set_global_javascript_variables'), 10);*/

        /* Front end css and js */
        /*add_action('wp_head', array($this, 'set_front_css'), 1);
        add_action('wp_head', array($this, 'set_front_js'), 1);
        add_action('wp_head', array($this, 'set_global_javascript_variables'));*/

        /* Add Document Video For First Time */
        //add_action('admin_footer', array($this, 'arm_add_document_video'), 1);
        add_action('wp_ajax_arm_do_not_show_video', array($this, 'arm_do_not_show_video'), 1);

        /* Add what's new popup */
        //add_action('admin_footer', array($this, 'arm_add_new_version_release_note'), 1);
        add_action('wp_ajax_arm_dont_show_upgrade_notice', array($this, 'arm_dont_show_upgrade_notice'), 1);

        /* For Admin Menus. */
        /*add_action('adminmenu', array($this, 'arm_set_adminmenu'));
        add_action('wp_logout', array($this, 'ARM_EndSession'));
        add_action('wp_login', array($this, 'ARM_EndSession'));*/

        add_action('arm_admin_messages', array($this, 'arm_admin_messages_init'));
        /* Include All Class Files. */
	
	//Query Monitor
        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }
        if (is_plugin_active('js_composer/js_composer.php') && file_exists(MEMBERSHIP_CORE_DIR . '/vc/class_vc_extend.php')) {
            require_once(MEMBERSHIP_CORE_DIR . '/vc/class_vc_extend.php');
            global $arm_vcextend;
            $arm_vcextend = new ARM_VCExtend();
        }

        if( is_plugin_active('wp-rocket/wp-rocket.php') && !is_admin() ) {
            add_filter('script_loader_tag', array($this, 'arm_prevent_rocket_loader_script'), 10, 2);
        }

        if( !is_admin() ){
        	add_filter('script_loader_tag', array($this, 'arm_prevent_rocket_loader_script_clf'),10,2);
        }

        /* Register Element for Cornerstone */
        /* add_action('wp_enqueue_scripts',array($this,'armember_cs_enqueue'));
          add_action('cornerstone_register_elements',array($this,'armember_cs_register_element'));
          add_filter('cornerstone_icon_map',array($this,'armember_cs_icon_map')); */
        /* Register Element for Cornerstone */
        //add_action('wp_footer', array($this, 'arm_set_js_css_conditionally'), 11);
        
        add_action('wp_ajax_arm_perform_update',array($this,'arm_perform_update_function'),1);
        add_action('arm_before_last_menu',array($this,'arm_update_plugin_to_new_version'),51);

        //add_action('admin_init',array($this,'arm_redirect_to_update_page') );
        add_filter( 'heartbeat_received', array($this, 'arm_receive_heartbeat_func'), 10, 2 );
        add_filter( 'heartbeat_settings', array($this,'arm_heartbeat_settings') );


        add_action('admin_notices', array($this, 'arm_addon_version_admin_notices'));

        add_action('arm_payment_log_entry', array($this, 'arm_write_payment_log'), 10, 6);

        add_action('arm_general_log_entry', array( $this, 'arm_write_general_log'), 10, 4);
    }


    function arm_addon_version_admin_notices()
    {
        $class = 'notice notice-error is-dismissible';

        $arm_plugin_list = "";
        if( file_exists(WP_PLUGIN_DIR.'/armembermultisite/armembermultisite.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armembermultisite/armembermultisite.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.1' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }


        if( file_exists(WP_PLUGIN_DIR.'/armemberdirectlogins/armemberdirectlogins.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberdirectlogins/armemberdirectlogins.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.8' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }



        if( file_exists(WP_PLUGIN_DIR.'/armemberdigitaldownload/armemberdigitaldownload.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberdigitaldownload/armemberdigitaldownload.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.7' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }


        if( file_exists(WP_PLUGIN_DIR.'/armembercommunity/armembercommunity.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armembercommunity/armembercommunity.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.5' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }


        if( file_exists(WP_PLUGIN_DIR.'/armemberaffiliate/armemberaffiliate.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberaffiliate/armemberaffiliate.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '3.2' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }


        if(!empty($arm_plugin_list)) {
            $arm_plugin_list = rtrim(trim($arm_plugin_list),',');
            printf( '<div class="%1$s" style="display: block !important;"><p><b>One or more add-on of ARMember must be updated with latest version</b> (%2$s).</p></div>', esc_attr( $class ), esc_html( $arm_plugin_list ) ); 
        }

        $arm_is_dismiss_notice = get_option('arm-stripe-dismiss-admin-notice');
        $arm_allowed_slugs = (array) $this->arm_slugs;
        if($arm_is_dismiss_notice && in_array($_REQUEST['page'], $arm_allowed_slugs))
        {
            $gateway_options = get_option('arm_payment_gateway_settings');
            $pgoptions = maybe_unserialize($gateway_options);
            if( !empty($pgoptions['stripe']['status']) )
            {
                printf("<div class='{$class} arm_dismiss_stripe_webhook_notice' data-arm_confirm='".__('Are you sure you have added subscription_schedule.canceled Webhook Event at stripe Account?', 'ARMember')."' style='display:block;background: #ffbfc0;color:#a01a1b;'><p>". __('Please add <b>subscription_schedule.canceled</b> event for added Stripe Webhook at your stripe.com Account. To add this event please Login to your Stripe Account -> Developers -> Webhooks page -> Edit Webhook and select the event for the "Events to send" option.', 'ARMember')."</p></div>");
            }
        }
    }

    function wpdbfix() {
        global $wpdb, $arm_db_tables, $ARMember;
        $wpdb->arm_termmeta = $ARMember->tbl_arm_termmeta;
    }

    function arm_init_action() {
        global $wp, $wpdb, $arm_db_tables;
        $this->arm_slugs = $this->arm_page_slugs();
        /**
         * Start Session
         */
        //session_start();
        ob_start();
        /**
         * Plugin Hook for `Init` Actions
         */
        do_action('arm_init', $this);
    }

    /**
     * Include All File From Directory
     */
    function arm_include_class_files($dir_path = '') {

    }

    /**
     * Hide WordPress Update Notifications In Plugin's Pages
     */
    function arm_hide_update_notice() {
        global $wp, $wpdb, $arm_errors, $current_user, $ARMember, $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('network_admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag');
            remove_action('network_admin_notices', 'maintenance_nag');
            remove_action('admin_notices', 'site_admin_notice');
            remove_action('network_admin_notices', 'site_admin_notice');
            remove_action('load-update-core.php', 'wp_update_plugins');
            add_filter('pre_site_transient_update_core', array($this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_plugins', array($this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_themes', array($this, 'arm_remove_core_updates'));
            /* Remove BuddyPress Admin Notices */
            remove_action('bp_admin_init', 'bp_core_activation_notice', 1010);
            if (!in_array($_REQUEST['page'], array($arm_slugs->manage_forms))) {
                add_action('admin_notices', array($this, 'arm_admin_notices'));
            }
            global $arm_drip_rules, $arm_social_feature, $arm_manage_coupons, $arm_members_badges, $arm_private_content_feature, $arm_pay_per_post_feature;
            if ($_REQUEST['page'] == $arm_slugs->drip_rules && !$arm_drip_rules->isDripFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_drip_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->profiles_directories, $arm_slugs->badges_achievements)) && !$arm_social_feature->isSocialFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_social_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->private_content)) && !$arm_private_content_feature->isPrivateContentFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_private_content_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if ($_REQUEST['page'] == $arm_slugs->coupon_management && !$arm_manage_coupons->isCouponFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_coupon_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->pay_per_post)) && !$arm_pay_per_post_feature->isPayPerPostFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_pay_per_pst_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
        }
    }

    function arm_admin_notices() {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_global_settings;
        $notice_html = '';
        $notices = array();
        $notices = apply_filters('arm_display_admin_notices', $notices);
        
        if (!empty($notices)) {
            $notice_html .= '<div class="arm_admin_notices_container">';
            $notice_html .= '<ul class="arm_admin_notices">';
            foreach ($notices as $notice) {
                $notice_html .= '<li class="arm_notice arm_notice_' . $notice['type'] . '">' . $notice['message'] . '</li>';
            }
            $notice_html .= '</ul>';
            $notice_html .= '<div class="armclear"></div></div>';
        }
        
        $arm_get_php_version = (function_exists('phpversion')) ? phpversion() : 0;
        if(version_compare($arm_get_php_version, '5.6', '<')) {
            $notice_html .= '<div class="notice notice-warning" style="display:block;">';
            $notice_html .= '<p>'.esc_html__('ARMember recommend to use Minimum PHP version 5.6 or greater.', 'ARMember').'</p>';
            $notice_html .= '</div>';
        }
        if(!empty($arm_global_settings->global_settings['enable_crop'])) {
            if (!function_exists('gd_info')) {
                $notice_html .= '<div class="notice notice-error" style="display:block;">';
                $notice_html .= '<p>'.esc_html__("ARMember requires PHP GD Extension module at the server. And it seems that it's not installed or activated. Please contact your hosting provider for the same.", "ARMember").'</p>';
                $notice_html .= '</div>';
            }
        }

        echo $notice_html;
    }

    function arm_set_message($type = 'error', $message = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow;
        if (!empty($message)) {
            $ARMember->arm_session_start();
            $_SESSION['arm_message'][] = array(
                'type' => $type,
                'message' => $message,
            );
        }
        return;
    }

    function arm_remove_core_updates() {
        global $wp_version;
        return(object) array('last_checked' => time(), 'version_checked' => $wp_version,);
    }

    function arm_set_adminmenu() {
        global $menu, $submenu, $parent_file, $ARMember;
        $ARMember->arm_session_start();
        if(isset($_SESSION['arm_admin_menus']))
        {
            unset($_SESSION['arm_admin_menus']);
        }
        $_SESSION['arm_admin_menus'] = array('main_menu' => $menu, 'submenu' => $submenu);
        if (isset($submenu['arm_manage_members']) && !empty($submenu['arm_manage_members'])) {
            $armAdminMenuScript = '<script type="text/javascript">';
            $armAdminMenuScript .= 'jQuery(document).ready(function ($) {';
            $armAdminMenuScript .= 'jQuery("#toplevel_page_arm_manage_members").find("ul li").each(function(){
					var thisLI = jQuery(this);
					thisLI.addClass("arm-submenu-item");
					var thisLinkHref = thisLI.find("a").attr("href");
					if(thisLinkHref != "" && thisLinkHref != undefined){
						var thisLinkClass = thisLinkHref.replace("admin.php?page=","");
						thisLI.addClass(thisLinkClass);
					}
				});
				jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("target", "_blank");';

            $docLink = MEMBERSHIP_DOCUMENTATION_URL;
            $armAdminMenuScript .= 'jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("href", "' . $docLink . '");';

            $armAdminMenuScript .= '});';

            $armAdminMenuScript .= '</script>';
            $armAdminMenuScript .= '<style type="text/css">';
            global $arm_drip_rules, $arm_social_feature, $arm_manage_coupons, $arm_members_badges, $arm_private_content_feature,$arm_global_settings, $arm_pay_per_post_feature;
            $arm_all_block_settings = $arm_global_settings->arm_get_all_block_settings();
            if (!$arm_private_content_feature->isPrivateContentFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_manage_private_content{display:none;}';
            }
            if (!$arm_drip_rules->isDripFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_drip_rules{display:none;}';
            }
            if (!$arm_social_feature->isSocialFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_profiles_directories{display:none;}';
            }
            if (!$arm_manage_coupons->isCouponFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_coupon_management{display:none;}';
            }
            if (!$arm_social_feature->isSocialFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.badges_achievements{display:none;}';
            }
            if(empty($arm_all_block_settings['track_login_history'])){
                $armAdminMenuScript .= '.arm-submenu-item.arm_member_login_report_analytics{display:none;}';
            }
            if (!$arm_pay_per_post_feature->isPayPerPostFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_manage_pay_per_post{display:none;}';
            }
            $armAdminMenuScript .= '.arm-submenu-item.arm_feature_settings a{color:#ffff00 !important;}';
            $armAdminMenuScript .= '</style>';
            echo $armAdminMenuScript;
        }
    }

    function ARM_EndSession() {
        @session_destroy();
    }

   

    /* Setting Capabilities for user */

    function arm_capabilities() {
        $cap = array(
            'arm_manage_members' => __('Manage Members', 'ARMember'),
            'arm_manage_plans' => __('Manage Plans', 'ARMember'),
            'arm_manage_setups' => __('Manage Setups', 'ARMember'),
            'arm_manage_forms' => __('Manage Form Settings', 'ARMember'),
            'arm_manage_access_rules' => __('Manage Access Rules', 'ARMember'),
            'arm_manage_drip_rules' => __('Manage Drip Rules', 'ARMember'),
            'arm_manage_transactions' => __('Manage Transactions', 'ARMember'),
            'arm_manage_email_notifications' => __('Manage Email Notifications', 'ARMember'),
            'arm_manage_communication' => __('Manage Communication', 'ARMember'),
            'arm_manage_member_templates' => __('Manage Member Templates', 'ARMember'),
            'arm_manage_general_settings' => __('Manage General Settings', 'ARMember'),
            'arm_manage_feature_settings' => __('Manage Feature Settings', 'ARMember'),
            'arm_manage_private_content' => __('Manage Private Content', 'ARMember'),
            'arm_manage_pay_per_post' => __('Manage Paid Posts', 'ARMember'),
            'arm_manage_license' => __('Manage License', 'ARMember'),
            'arm_manage_block_settings' => __('Manage Block Settings', 'ARMember'),
            'arm_manage_coupons' => __('Manage coupons', 'ARMember'),
            'arm_manage_payment_gateways' => __('Manage Payment Gateways', 'ARMember'),
            'arm_import_export' => __('Manage Import/Export', 'ARMember'),
            'arm_badges' => __('Badge And Achievements Management', 'ARMember'),
            'arm_report_analytics' => __('Reports', 'ARMember'),
        );
        return $cap;
    }

    function arm_page_slugs() {
        global $ARMember, $arm_slugs;
        $arm_slugs = new stdClass;
        /* Admin-Pages-Slug */
        $arm_slugs->main = 'arm_manage_members';
        $arm_slugs->manage_members = 'arm_manage_members';
        $arm_slugs->manage_plans = 'arm_manage_plans';
        $arm_slugs->membership_setup = 'arm_membership_setup';
        $arm_slugs->manage_forms = 'arm_manage_forms';
        $arm_slugs->access_rules = 'arm_access_rules';
        $arm_slugs->drip_rules = 'arm_drip_rules';
        $arm_slugs->transactions = 'arm_transactions';
        $arm_slugs->email_notifications = 'arm_email_notifications';
        $arm_slugs->coupon_management = 'arm_coupon_management';
        $arm_slugs->general_settings = 'arm_general_settings';
        $arm_slugs->feature_settings = 'arm_feature_settings';
        $arm_slugs->licensing = 'arm_manage_license';
        $arm_slugs->documentation = 'arm_documentation';
        $arm_slugs->profiles_directories = 'arm_profiles_directories';
        $arm_slugs->private_content = 'arm_manage_private_content';
        $arm_slugs->pay_per_post = 'arm_manage_pay_per_post';
        $arm_slugs->badges_achievements = 'badges_achievements';
        $arm_slugs->report_analytics = 'arm_report_analytics';

        return $arm_slugs;
    }

    /**
     * Setting Menu Position
     */
    function get_free_menu_position($start, $increment = 0.1) {
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = floatval($key);
        }
        if (!in_array($start, $menus_positions)) {
            $start = strval($start);
            return $start;
        } else {
            $start += $increment;
        }
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        $start = strval($start);
        return $start;
    }

    function armPluginActionLinks($links, $file) {
        global $wp, $wpdb, $ARMember, $arm_slugs;
        if ($file == plugin_basename(MEMBERSHIP_DIR.'/armember.php')) {
            $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings);
            $link = '<a title="' . __('Add-ons', 'ARMember') . '" href="' . esc_url($armAddonsLink) . '">' . __('Add-ons', 'ARMember') . '</a>';
            array_unshift($links, $link); /* Add Link To First Position */
        }
        return $links;
    }

    function arm_admin_body_class($classes) {
        global $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            $classes .= ' arm_wpadmin_page ';
        }
        return $classes;
    }

    /**
     * Adding Membership Admin Menu(s)
     */
    function arm_menu() {
        global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_slugs, $arm_global_settings, $arm_social_feature, $arm_membership_setup, $arm_manage_coupons;

        $place = $this->get_free_menu_position(26.1, 0.3);
        if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
            echo "<style type='text/css'>.toplevel_page_arm_manage_members .wp-menu-image img{margin-top:-4px !important;}.toplevel_page_arm_manage_members .wp-menu-image .wp-menu-name{padding-left:30px !important;;}</style>";
        }
        $arm_menu_hook = add_menu_page('ARMember', __('ARMember', 'ARMember'), 'arm_manage_members', $arm_slugs->main, array($this, 'route'), MEMBERSHIP_IMAGES_URL . '/armember_menu_icon.png', $place);
        $admin_menu_items = array(
            $arm_slugs->manage_members => array(
                'name' => __('Manage Members', 'ARMember'),
                'title' => __('Manage Members', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
            $arm_slugs->manage_plans => array(
                'name' => __('Manage Plans', 'ARMember'),
                'title' => __('Manage Plans', 'ARMember'),
                'capability' => 'arm_manage_plans'
            ),
            $arm_slugs->membership_setup => array(
                'name' => __('Configure Plan + Signup Page', 'ARMember'),
                'title' => __('Configure Plan + Signup Page', 'ARMember'),
                'capability' => 'arm_manage_setups'
            ),
            $arm_slugs->manage_forms => array(
                'name' => __('Manage Forms', 'ARMember'),
                'title' => __('Manage Forms', 'ARMember'),
                'capability' => 'arm_manage_forms'
            ),
            $arm_slugs->access_rules => array(
                'name' => __('Content Access Rules', 'ARMember'),
                'title' => __('Content Access Rules', 'ARMember'),
                'capability' => 'arm_manage_access_rules'
            ),
            $arm_slugs->drip_rules => array(
                'name' => __('Drip Content', 'ARMember'),
                'title' => __('Drip Content', 'ARMember'),
                'capability' => 'arm_manage_drip_rules'
            ),
            $arm_slugs->transactions => array(
                'name' => __('Payment History', 'ARMember'),
                'title' => __('Payment History', 'ARMember'),
                'capability' => 'arm_manage_transactions'
            ),
            $arm_slugs->email_notifications => array(
                'name' => __('Email Notifications', 'ARMember'),
                'title' => __('Email Notifications', 'ARMember'),
                'capability' => 'arm_manage_email_notifications'
            ),
            $arm_slugs->coupon_management => array(
                'name' => __('Coupon Management', 'ARMember'),
                'title' => __('Coupon Management', 'ARMember'),
                'capability' => 'arm_manage_coupons'
            ),
            $arm_slugs->profiles_directories => array(
                'name' => __('Profiles & Directories', 'ARMember'),
                'title' => __('Profiles & Directories', 'ARMember'),
                'capability' => 'arm_manage_member_templates'
            ),
            $arm_slugs->private_content => array(
                'name' => __('User Private Content', 'ARMember'),
                'title' => __('User Private Content', 'ARMember'),
                'capability' => 'arm_manage_private_content'
            ),
            $arm_slugs->pay_per_post => array(
                'name' => __('Manage Paid Posts', 'ARMember'),
                'title' => __('Manage Paid Posts', 'ARMember'),
                'capability' => 'arm_manage_pay_per_post'
            ),
            $arm_slugs->badges_achievements => array(
                'name' => __('Badges & Achievements', 'ARMember'),
                'title' => __('Badges & Achievements', 'ARMember'),
                'capability' => 'arm_badges'
            ),
            $arm_slugs->general_settings => array(
                'name' => __('General Settings', 'ARMember'),
                'title' => __('General Settings', 'ARMember'),
                'capability' => 'arm_manage_general_settings'
            ),
            $arm_slugs->report_analytics => array(
                'name' => __('Reports', 'ARMember'),
                'title' => __('Reports', 'ARMember'),
                'capability' => 'arm_report_analytics'
            ),
        );
        foreach ($admin_menu_items as $slug => $menu) {
            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array($this, 'route'));
        }
        do_action('arm_before_last_menu');
    }

    function arm_update_plugin_to_new_version(){
        global $arm_slugs;
        $arm_current_version = get_option('arm_version');
        $arm_new_version = get_option('arm_new_version');
        $arm_to_update = get_option('arm_update_to_new_version');

        if( $arm_to_update == true && $arm_new_version != '' ){
            if( version_compare($arm_current_version, $arm_new_version, '<') && $arm_new_version == '2.0' ){
                add_submenu_page('arm_manage_members','ARMember Update','','read','arm_update_page',array($this,'arm_update_page_function'));
            }
        }
    }
	
	function arm_update_badges($attempts=0){
		
		global $wp_version;
        $lidata = "";
        $badge_desc = get_option("armSortOrder");
		
		if($badge_desc != "")
		{	
			$urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/update_arm_badge.php";
			$response = wp_remote_post($urltopost, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('verifycode' => $badge_desc,'attempts' => $attempts),
				'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
				'cookies' => array()
					)
			);

			if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
				$responsemsg = $response["body"];
			else
				$responsemsg = "";

			if ($responsemsg != "") {
				$responsemsg = explode("|^|", $responsemsg);
				if (is_array($responsemsg) && count($responsemsg) > 0) {

					if (isset($responsemsg[0]) && $responsemsg[0] != "") {
						$msg = $responsemsg[0];
					} else {
						$msg = "";
					}
					
					if (isset($responsemsg[1]) && $responsemsg[1] != "") {
						$info = $responsemsg[1];
					} else {
						$info = "";
					}

					if ($msg == "1") {
						update_option("armSortOrder", $info);
						update_option("armBadgeUpdated", $info);
						delete_option("arm_badgeupdaterequired");
					}
				}
			}
		}
    }
	
    function arm_update_page_function(){
        require MEMBERSHIP_VIEWS_DIR.'/arm_update_page.php';
    }

    function arm_redirect_to_update_page(){
        $arm_current_version = get_option('arm_version');
        $arm_new_version = get_option('arm_new_version');
        $arm_to_update = get_option('arm_update_to_new_version');

        if( $arm_to_update == true && $arm_new_version != '' ){
            if( version_compare($arm_current_version, $arm_new_version, '<') && $arm_new_version == '2.0' ){
                $url = admin_url('admin.php?page=arm_update_page');
                if( isset($_REQUEST['page']) && $_REQUEST['page'] != 'arm_update_page' ){
                    wp_redirect($url);
                }
            }
        }
    }

    function arm_set_last_menu() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_membership_setup;
        $admin_menu_items = array(
            $arm_slugs->feature_settings => array(
                'name' => __('Add-ons', 'ARMember'),
                'title' => __('Add-ons', 'ARMember'),
                'capability' => 'arm_manage_feature_settings'
            ),
            $arm_slugs->licensing => array(
                'name' => __('Licensing', 'ARMember'),
                'title' => __('Licensing', 'ARMember'),
                'capability' => 'arm_manage_license'
            ),
            $arm_slugs->documentation => array(
                'name' => __('Documentation', 'ARMember'),
                'title' => __('Documentation', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
        );
        foreach ($admin_menu_items as $slug => $menu) {
            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array($this, 'route'));
        }
    }

    function arm_add_debug_bar_menu($wp_admin_bar) {
        /* Admin Bar Menu */
        if (!current_user_can('administrator') || MEMBERSHIP_DEBUG_LOG == false) {
            return;
        }
        $args = array(
            'id' => 'arm_debug_menu',
            'title' => __('ARMember Debug', 'ARMember'),
            'parent' => 'top-secondary',
            'href' => '#',
            'meta' => array(
                'class' => 'armember_admin_bar_debug_menu'
            )
        );
        echo "<style type='text/css'>";
        echo ".armember_admin_bar_debug_menu{
				background:#ff9a8d !Important;
			}";
        echo "</style>";
        $wp_admin_bar->add_menu($args);
    }

    /**
     * Display Admin Page View
     */
    function route() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings;
        if (isset($_REQUEST['page'])) {
            $pageWrapperClass = '';
            if (is_rtl()) {
                $pageWrapperClass = 'arm_page_rtl';
            }
            echo '<div class="arm_page_wrapper ' . $pageWrapperClass . '" id="arm_page_wrapper">';
            $requested_page = $_REQUEST['page'];
            do_action('arm_admin_messages', $requested_page);
            switch ($requested_page) {
                case $arm_slugs->main:
                case $arm_slugs->manage_members:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit_member', 'view_member'))) {
                        if ($_GET['action'] == 'view_member' && !empty($_GET['id']) && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_view_member.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_view_member.php');
                        } elseif (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_member_add.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_member_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_members_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_members_list.php');
                        }
                    }
                    break;
                case $arm_slugs->manage_plans:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit_plan'))) {
                        if ($_GET['action'] == 'edit_plan' && !isset($_GET['id']) && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        } elseif (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_add.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        }
                    }
                    break;
                case $arm_slugs->membership_setup:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new_setup', 'edit_setup', 'new_setup_old'))) {
                        if ($_GET['action'] == 'new_setup_old') {
                            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add_old.php')) {
                                include( MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add_old.php');
                            }
                        } elseif ($_GET['action'] == 'edit_setup' && isset($_REQUEST['id']) && !empty($_REQUEST['id']) && $_REQUEST['id'] != 0) {
                            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        } else {
                            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        }
                    } else {
                        global $arm_membership_setup;
                        $total_setups = $arm_membership_setup->arm_total_setups();
                        if ($total_setups < 1 && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_add.php');
                        } else if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_membership_setup_list.php');
                        }
                    }
                    break;
                case $arm_slugs->manage_forms:
                    if (isset($_GET['action']) && ($_GET['action'] == 'edit_form' || $_GET['action'] == 'new_form' || $_GET['action'] == 'duplicate_form') && is_numeric($_GET['form_id']) && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_form_editor.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_form_editor.php');
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_manage_forms.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_manage_forms.php');
                        }
                    }
                    break;
                case $arm_slugs->access_rules:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_access_rules.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_access_rules.php');
                    }
                    break;
                case $arm_slugs->drip_rules:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules.php');
                    }
                    break;
                case $arm_slugs->transactions:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit_payment'))) {
                        if ($_GET['action'] == 'edit_payment' && !isset($_GET['id']) && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_transactions.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_transactions.php');
                        } elseif (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_transactions_add.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_transactions_add.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_transactions.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_transactions.php');
                        }
                    }
                    break;
                case $arm_slugs->email_notifications:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_email_notification.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_email_notification.php');
                    }
                    break;
                case $arm_slugs->coupon_management:
                    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_coupon') {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php');
                        }
                    } else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_coupon' && $_REQUEST['coupon_eid'] != '') {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php');
                        }
                    } else {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_manage_coupons.php');
                    }
                    break;
                case $arm_slugs->general_settings:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_general_settings.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_general_settings.php');
                    }
                    break;
                case $arm_slugs->feature_settings:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_feature_settings.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_feature_settings.php');
                    }
                    break;
                case $arm_slugs->licensing:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_license_activation.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_license_activation.php');
                    }
                    break;
                case $arm_slugs->documentation:

                    wp_redirect(MEMBERSHIP_DOCUMENTATION_URL);
                    die();
                    break;
                case $arm_slugs->profiles_directories:
                    if (isset($_GET['action']) && ($_GET['action'] == 'add_profile' || $_GET['action'] == 'edit_profile' || $_GET['action'] == "duplicate_profile") && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_profile_editor.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_profile_editor.php');
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_profiles_directories.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_profiles_directories.php');
                        }
                    }
                    break;
                case $arm_slugs->private_content:
                    if (isset($_GET['action']) && ($_GET['action'] == 'add_private_content' || $_GET['action'] == 'edit_private_content') && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_add.php')) {

                        include( MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_add.php');
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_list.php');
                        }
                    }
                    break;
                case $arm_slugs->badges_achievements:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_badges.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_badges.php');
                    }
                    break;
                case $arm_slugs->report_analytics:
                    

                    if (isset($_GET['action']) && in_array($_GET['action'], array('member_report', 'payment_report', 'pay_per_post_report','coupon_report'))) {
                      
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics_summary.php')) {
                          include( MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics_summary.php');
                        }
                    } else if(isset($_GET['action']) && in_array($_GET['action'], array('login_history'))) {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_login_history.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_report_login_history.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics.php');
                        }
                    }
                    break;
                case $arm_slugs->pay_per_post:

                    if( isset($_GET['action'] ) && in_array( $_GET['action'], array( 'edit_paid_post', 'add_paid_post' ) ) ){
                        if( file_exists( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_form.php' ) ){
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_form.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_list.php');
                        }
                    }
                    break;
                default:
                    break;
            }
            echo '</div>';
        } else {
            /* No Action */
        }
    }

    /* Setting Admin CSS  */

    function set_css() {
        global $arm_slugs;
        /* Plugin Style */
        wp_register_style('arm_admin_css', MEMBERSHIP_URL . '/css/arm_admin.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIP_URL . '/css/arm_form_style.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm-font-awesome-css', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);

        /* For chosen select box */
        wp_register_style('arm_chosen_selectbox', MEMBERSHIP_URL . '/css/chosen.css', array(), MEMBERSHIP_VERSION);

        /* For bootstrap datetime picker */

        wp_register_style('arm_bootstrap_all_css', MEMBERSHIP_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIP_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? $_GET['page'] : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_style( 'datatables' );
            wp_dequeue_style( 'datatables' );
            
            wp_register_style( 'datatables', MEMBERSHIP_URL . '/datatables/media/css/datatables.css', array(), MEMBERSHIP_VERSION );
        }
        
        /* Add Style for menu icon image. */
        echo '<style type="text/css"> .toplevel_page_armember .wp-menu-image img, .toplevel_page_arm_manage_members .wp-menu-image img{padding: 5px !important;} .arm_vc_icon{background-image:url(' . MEMBERSHIP_IMAGES_URL . '/armember_menu_icon.png) !important;}</style>';
        /* Add CSS file only for plugin pages. */
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_style('arm_admin_css');
            wp_enqueue_style('arm_form_style_css');
            wp_enqueue_style('arm-font-awesome-css');
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_plans, $arm_slugs->email_notifications, $arm_slugs->coupon_management, $arm_slugs->badges_achievements, $arm_slugs->drip_rules, $arm_slugs->profiles_directories, $arm_slugs->private_content, $arm_slugs->pay_per_post,$arm_slugs->access_rules,$arm_slugs->transactions))) {
                wp_enqueue_style('arm_chosen_selectbox');
                wp_enqueue_style('datatables');
                
                
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_members, $arm_slugs->coupon_management, $arm_slugs->drip_rules, $arm_slugs->transactions, $arm_slugs->private_content, $arm_slugs->report_analytics, $arm_slugs->pay_per_post))) {
                wp_enqueue_style('arm_bootstrap_all_css');
            }
            if($_REQUEST['page'] == $arm_slugs->manage_members && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view_member') && (isset($_REQUEST['view_type']) && $_REQUEST['view_type'] == 'popup')) {
                $inline_style = "html.wp-toolbar { padding-top: 0px !important; }
                #wpcontent{ margin-left: 0 !important; }
                #wpadminbar { display: none !important; }
                #adminmenumain { display: none !important; }
                .arm_view_member_wrapper { max-width: inherit !important; }";
                wp_add_inline_style('arm_admin_css', $inline_style);
            }
        }
        if (is_rtl()) {
            wp_register_style('arm_admin_css-rtl', MEMBERSHIP_URL . '/css/arm_admin_rtl.css', array(), MEMBERSHIP_VERSION);
            wp_enqueue_style('arm_admin_css-rtl');
        }
    }

    /* Setting Admin JavaScript */
    function set_js() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_ajaxurl;

            

                

        /* Plugin JS */
        wp_register_script('arm_admin_js', MEMBERSHIP_URL . '/js/arm_admin.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_common_js', MEMBERSHIP_URL . '/js/arm_common.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_jeditable', MEMBERSHIP_URL . '/js/jquery.jeditable.mini.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_icheck-js', MEMBERSHIP_URL . '/js/icheck.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_colpick-js', MEMBERSHIP_URL . '/js/colpick.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_codemirror-js', MEMBERSHIP_URL . '/js/arm_codemirror.js', array('jquery'), MEMBERSHIP_VERSION);
        /* Tooltip JS */
        wp_register_script('arm_tipso', MEMBERSHIP_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIP_VERSION);
        /* Form Validation */
        wp_register_script('arm_validate', MEMBERSHIP_URL . '/js/jquery.validate.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_tojson', MEMBERSHIP_URL . '/js/jquery.json.js', array('jquery'), MEMBERSHIP_VERSION);
        /* For chosen select box */
        wp_register_script('arm_chosen_jq_min', MEMBERSHIP_URL . '/js/chosen.jquery.min.js', array(), MEMBERSHIP_VERSION);
        /* File Upload JS */
        wp_register_script('arm_filedrag_import_user_js', MEMBERSHIP_URL . '/js/filedrag/filedrag_import_user.js', array(), MEMBERSHIP_VERSION);

	wp_register_script('arm_file_upload_js',MEMBERSHIP_URL . '/js/arm_file_upload_js.js',array('jquery'), MEMBERSHIP_VERSION);
    wp_register_script('arm_admin_file_upload_js',MEMBERSHIP_URL . '/js/arm_admin_file_upload_js.js',array('jquery'), MEMBERSHIP_VERSION);
       
        /* For bootstrap datetime picker js */
        wp_register_script('arm_bootstrap_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIP_VERSION);
        
        wp_register_script('arm_bootstrap_datepicker_with_locale', MEMBERSHIP_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIP_VERSION);

        wp_register_script('arm_highchart', MEMBERSHIP_URL . '/js/highcharts.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_admin_chart', MEMBERSHIP_URL . '/js/arm_admin_chart.js', array(), MEMBERSHIP_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? $_GET['page'] : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_script('datatables');
            wp_dequeue_script( 'datatables' );

            wp_deregister_script('buttons-colvis');
            wp_dequeue_script( 'buttons-colvis' );

            wp_deregister_script('fixedcolumns');
            wp_dequeue_script( 'fixedcolumns' );

            wp_deregister_script('fourbutton');
            wp_dequeue_script( 'fourbutton' );

            wp_register_script('datatables', MEMBERSHIP_URL . '/datatables/media/js/datatables.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('buttons-colvis', MEMBERSHIP_URL . '/datatables/media/js/buttons.colVis.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('fixedcolumns', MEMBERSHIP_URL . '/datatables/media/js/FixedColumns.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('fourbutton', MEMBERSHIP_URL . '/datatables/media/js/four_button.js', array(), MEMBERSHIP_VERSION);
        }
        
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('arm_tojson');
            wp_enqueue_script('arm_icheck-js');
            wp_enqueue_script('arm_validate');
            /* Main Plugin Back-End JS */
            wp_enqueue_script('arm_bpopup');
            wp_enqueue_script('arm_tipso');
            wp_enqueue_script('arm_admin_js');
            wp_enqueue_script('arm_common_js');

            /* For the Datatable Design. */
            $dataTablePages = array(
                $arm_slugs->main,
                $arm_slugs->manage_members,
                $arm_slugs->manage_plans,
                $arm_slugs->private_content,
                $arm_slugs->membership_setup,
                $arm_slugs->access_rules,
                $arm_slugs->drip_rules,
                $arm_slugs->transactions,
                $arm_slugs->email_notifications,
                $arm_slugs->coupon_management,
                $arm_slugs->badges_achievements,
                $arm_slugs->pay_per_post,
            );
            if (in_array($_REQUEST['page'], $dataTablePages)) {
                wp_enqueue_script('datatables');
                wp_enqueue_script('buttons-colvis');
                wp_enqueue_script('fixedcolumns');
                wp_enqueue_script('fourbutton');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->membership_setup, $arm_slugs->manage_forms, $arm_slugs->profiles_directories, $arm_slugs->private_content))) {
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_forms, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_jeditable');
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->membership_setup, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
                wp_enqueue_script('arm_codemirror-js');
                wp_enqueue_style('arm_codemirror-css', MEMBERSHIP_URL . '/css/arm_codemirror.css', array(), MEMBERSHIP_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->drip_rules))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_forms, $arm_slugs->profiles_directories, $arm_slugs->badges_achievements))) {
                
                wp_enqueue_script('arm_admin_file_upload_js');
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_plans, $arm_slugs->email_notifications, $arm_slugs->coupon_management, $arm_slugs->badges_achievements, $arm_slugs->profiles_directories, $arm_slugs->drip_rules, $arm_slugs->private_content))) {
                wp_enqueue_script('arm_chosen_jq_min');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_members, $arm_slugs->coupon_management, $arm_slugs->drip_rules, $arm_slugs->transactions, $arm_slugs->private_content, $arm_slugs->report_analytics))) {
                wp_enqueue_script('arm_bootstrap_js');
                wp_enqueue_script('arm_bootstrap_datepicker_with_locale');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings))) {
                wp_enqueue_script('arm_filedrag_import_user_js');
                wp_enqueue_script('sack');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_members))) {
                wp_enqueue_script('arm_admin_file_upload_js');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->transactions))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->report_analytics)) ) {
                wp_enqueue_script('arm_highchart');
                wp_enqueue_script('arm_admin_chart');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->pay_per_post,$arm_slugs->coupon_management))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }
        }
    }
    
    
    /* Setting global javascript variables */
    
    function set_global_javascript_variables(){
        
        
        global $arm_ajaxurl, $arm_pay_per_post_feature;
        echo '<script type="text/javascript" data-cfasync="false">';
            echo '__ARMAJAXURL = "'.$arm_ajaxurl.'";';
            //echo '__ARMURL = "'.MEMBERSHIP_URL.'";';
            echo '__ARMVIEWURL = "'.MEMBERSHIP_VIEWS_URL.'";';
            echo '__ARMIMAGEURL = "'.MEMBERSHIP_IMAGES_URL.'";';
            echo '__ARMISADMIN = ['.is_admin().'];';
	    echo '__ARMSITEURL = "'.ARM_HOME_URL.'";';
            echo 'loadActivityError = "'.__("There is an error while loading activities, please try again.", 'ARMember').'";';
            echo 'pinterestPermissionError = "'. __("The user chose not to grant permissions or closed the pop-up", 'ARMember').'";';
            echo 'pinterestError = "'. __("Oops, there was a problem getting your information", 'ARMember').'";';
            echo 'clickToCopyError = "'. __("There is a error while copying, please try again", 'ARMember').'";';
            echo 'fbUserLoginError = "'. __("User cancelled login or did not fully authorize.", 'ARMember').'";';
            echo 'closeAccountError = "'. __("There is a error while closing account, please try again.", 'ARMember').'";';
            echo 'invalidFileTypeError = "'. __("Sorry, this file type is not permitted for security reasons.", 'ARMember').'";';
            echo 'fileSizeError = "'. __("File is not allowed bigger than {SIZE}.", 'ARMember').'";';
            echo 'fileUploadError = "'. __("There is an error in uploading file, Please try again.", 'ARMember').'";';
            echo 'coverRemoveConfirm = "'. __("Are you sure you want to remove cover photo?", 'ARMember').'";';
            echo 'profileRemoveConfirm = "'. __("Are you sure you want to remove profile photo?", 'ARMember').'";';
            echo 'errorPerformingAction = "'. __("There is an error while performing this action, please try again.", 'ARMember').'";';
            echo 'userSubscriptionCancel = "'. __("User's subscription has been canceled", 'ARMember').'";';
            
            echo 'ARM_Loding = "'. __("Loading..", 'ARMember').'";';
            echo 'Post_Publish ="'.__("After certain time of post is published", 'ARMember').'";';
            echo 'Post_Modify ="'.__("After certain time of post is modified", 'ARMember').'";';
            
            echo 'wentwrong ="'. __("Sorry, Something went wrong. Please try again.", 'ARMember').'";';
            echo 'bulkActionError = "'. __("Please select valid action.", 'ARMember').'";';
            echo 'bulkRecordsError ="'. __("Please select one or more records.", 'ARMember').'";';
            echo 'clearLoginAttempts ="'. __("Login attempts cleared successfully.", 'ARMember').'";';
            echo 'clearLoginHistory ="'. __("Login History cleared successfully.", 'ARMember').'";';
            echo 'nopasswordforimport ="'. __("Password can not be left blank.", 'ARMember').'";';
            echo 'delBadgeSuccess ="'. __("Badge has been deleted successfully.", 'ARMember').'";';
            echo 'delBadgeError ="'. __("There is a error while deleting Badge, please try again.", 'ARMember').'";';
            echo 'delAchievementBadgeSuccess ="'. __("Achievement badges has been deleted successfully.", 'ARMember').'";';
            echo 'delAchievementBadgeError ="'. __("There is a error while deleting achievement badges, please try again.", 'ARMember').'";';
            echo 'addUserAchievementSuccess ="'. __("User Achievement Added Successfully.", 'ARMember').'";';
            echo 'delUserBadgeSuccess ="'. __("User badge has been deleted successfully.", 'ARMember').'";';
            echo 'delUserBadgeError ="'. __("There is a error while deleting user badge, please try again.", 'ARMember').'";';
            echo 'delPlansSuccess ="'. __("Plan(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delPlansError ="'. __("There is a error while deleting Plan(s), please try again.", 'ARMember').'";';
            echo 'delPlanError ="'. __("There is a error while deleting Plan, please try again.", 'ARMember').'";';
            echo 'stripePlanIDWarning ="'. __("If you leave this field blank, stripe will not be available in setup for recurring plan(s).", 'ARMember').'";';
            echo 'delSetupsSuccess ="'. __("Setup(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delSetupsError ="'. __("There is a error while deleting Setup(s), please try again.", 'ARMember').'";';
            echo 'delSetupSuccess ="'. __("Setup has been deleted successfully.", 'ARMember').'";';
            echo 'delSetupError ="'. __("There is a error while deleting Setup, please try again.", 'ARMember').'";';
            echo 'delFormSetSuccess ="'. __("Form Set Deleted Successfully.", 'ARMember').'";';
            echo 'delFormSetError ="'. __("There is a error while deleting form set, please try again.", 'ARMember').'";';
            echo 'delFormSuccess ="'. __("Form deleted successfully.", 'ARMember').'";';
            echo 'delFormError ="'. __("There is a error while deleting form, please try again.", 'ARMember').'";';
            echo 'delRuleSuccess ="'. __("Rule has been deleted successfully.", 'ARMember').'";';
            echo 'delRuleError ="'. __("There is a error while deleting Rule, please try again.", 'ARMember').'";';
            echo 'delRulesSuccess ="'. __("Rule(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delRulesError ="'. __("There is a error while deleting Rule(s), please try again.", 'ARMember').'";';
            echo 'prevTransactionError ="'. __("There is a error while generating preview of transaction detail, Please try again.", 'ARMember').'";';
            echo 'invoiceTransactionError ="'. __("There is a error while generating invoice of transaction detail, Please try again.", 'ARMember').'";';
            echo 'prevMemberDetailError ="'. __("There is a error while generating preview of members detail, Please try again.", 'ARMember').'";';
            echo 'prevMemberActivityError ="'. __("There is a error while displaying members activities detail, Please try again.", 'ARMember').'";';
            echo 'prevCustomCssError ="'. __("There is a error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember').'";';
            echo 'prevImportMemberDetailError ="'. __("Please upload appropriate file to import users.", 'ARMember').'";';
            echo 'delTransactionSuccess ="'. __("Transaction has been deleted successfully.", 'ARMember').'";';
            echo 'delTransactionsSuccess ="'. __("Transaction(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessageSuccess ="'. __("Message has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessageError ="'. __("There is a error while deleting Message, please try again.", 'ARMember').'";';
            echo 'delAutoMessagesSuccess ="'. __("Message(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delAutoMessagesError ="'. __("There is a error while deleting Message(s), please try again.", 'ARMember').'";';
            echo 'delCouponSuccess ="'. __("Coupon has been deleted successfully.", 'ARMember').'";';
            echo 'delCouponError ="'. __("There is a error while deleting Coupon, please try again.", 'ARMember').'";';
            echo 'delCouponsSuccess ="'. __("Coupon(s) has been deleted successfully.", 'ARMember').'";';
            echo 'delCouponsError ="'. __("There is a error while deleting Coupon(s), please try again.", 'ARMember').'";';
            echo 'saveSettingsSuccess ="'. __("Settings has been saved successfully.", 'ARMember').'";';
            echo 'saveSettingsError ="'. __("There is a error while updating settings, please try again.", 'ARMember').'";';
            echo 'saveDefaultRuleSuccess ="'. __("Default Rules Saved Successfully.", 'ARMember').'";';
            echo 'saveDefaultRuleError ="'. __("There is a error while updating rules, please try again.", 'ARMember').'";';
            echo 'saveOptInsSuccess ="'. __("Opt-ins Settings Saved Successfully.", 'ARMember').'";';
            echo 'saveOptInsError ="'. __("There is a error while updating opt-ins settings, please try again.", 'ARMember').'";';
            echo 'delOptInsConfirm ="'. __("Are you sure to delete configuration?", 'ARMember').'";';
            echo 'delMemberActivityError ="'. __("There is a error while deleting member activities, please try again.", 'ARMember').'";';
            echo 'noTemplateError ="'. __("Template not found.", 'ARMember').'";';
            echo 'saveTemplateSuccess ="'. __("Template options has been saved successfully.", 'ARMember').'";';
            echo 'saveTemplateError ="'. __("There is a error while updating template options, please try again.", 'ARMember').'";';
            echo 'prevTemplateError ="'. __("There is a error while generating preview of template, Please try again.", 'ARMember').'";';
            echo 'addTemplateSuccess ="'. __("Template has been added successfully.", 'ARMember').'";';
            echo 'addTemplateError ="'. __("There is a error while adding template, please try again.", 'ARMember').'";';
            echo 'delTemplateSuccess ="'. __("Template has been deleted successfully.", 'ARMember').'";';
            echo 'delTemplateError ="'. __("There is a error while deleting template, please try again.", 'ARMember').'";';
            echo 'saveEmailTemplateSuccess ="'. __("Email Template Updated Successfully.", 'ARMember').'";';
            echo 'saveAutoMessageSuccess ="'. __("Message Updated Successfully.", 'ARMember').'";';
            echo 'saveBadgeSuccess ="'. __("Badges Updated Successfully.", 'ARMember').'";';
            echo 'addAchievementSuccess ="'. __("Achievements Added Successfully.", 'ARMember').'";';
            echo 'saveAchievementSuccess ="'. __("Achievements Updated Successfully.", 'ARMember').'";';
            echo 'addDripRuleSuccess ="'. __("Rule Added Successfully.", 'ARMember').'";';
            echo 'saveDripRuleSuccess ="'. __("Rule updated Successfully.", 'ARMember').'";';
            echo 'pastDateError ="'. __("Cannot Set Past Dates.", 'ARMember').'";';
            echo 'pastStartDateError ="'. __("Start date can not be earlier than current date.", 'ARMember').'";';
            echo 'pastExpireDateError ="'. __("Expire date can not be earlier than current date.", 'ARMember').'";';
            echo 'couponExpireDateError ="'. __("Expire date can not be earlier than start date.", 'ARMember').'";';
            echo 'uniqueformsetname ="'. __("This Set Name is already exist.", 'ARMember').'";';
            echo 'uniquesignupformname ="'. __("This Form Name is already exist.", 'ARMember').'";';
            echo 'installAddonError ="'. __('There is an error while installing addon, Please try again.', 'ARMember').'";';
            echo 'installAddonSuccess ="'. __('Addon installed successfully.', 'ARMember').'";';
            echo 'activeAddonError ="'. __('There is an error while activating addon, Please try again.', 'ARMember').'";';
            echo 'activeAddonSuccess ="'. __('Addon activated successfully.', 'ARMember').'";';
            echo 'deactiveAddonSuccess ="'. __('Addon deactivated successfully.', 'ARMember').'";';
            echo 'confirmCancelSubscription ="'. __('Are you sure you want to cancel subscription?', 'ARMember').'";';
            echo 'errorPerformingAction ="'. __("There is an error while performing this action, please try again.", 'ARMember').'";';
            echo 'arm_nothing_found ="'. __('Oops, nothing found.', 'ARMember').'";';
            echo 'delPaidPostSuccess ="'. __("Paid Post has been deleted successfully.", 'ARMember').'";';
            echo 'delPaidPostError ="'. __("There is a error while deleting Paid Post, please try again.", 'ARMember').'";';
            echo 'armEditCurrency ="'.__('Edit', 'ARMember').'";';
            echo 'armCustomCurrency ="'.__('Custom Currency', 'ARMember').'";';
            
            echo 'armEnabledPayPerPost ="'.$arm_pay_per_post_feature->isPayPerPostFeature.'";';
            echo 'REMOVEPAIDPOSTMESSAGE = "'.__('You cannot remove all paid post.', 'ARMember').'";';

            echo 'ARMCYCLELABEL = "'.__('Label', 'ARMember').'";';
            echo 'LABELERROR = "'.__('Label should not be blank', 'ARMember').'"';

            
            
            
            echo '</script>';
    }
    

    /* Setting Frond CSS */

    function set_front_css($isFrontSection = false) {
        global $wp, $wpdb, $wp_query, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_directory;
        /* Main Plugin CSS */
        wp_register_style('arm_front_css', MEMBERSHIP_URL . '/css/arm_front.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIP_URL . '/css/arm_form_style.css', array(), MEMBERSHIP_VERSION);
        /* Font Awesome CSS */
        wp_register_style('arm_fontawesome_css', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
        /* For bootstrap datetime picker */
        wp_register_style('arm_bootstrap_all_css', MEMBERSHIP_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIP_VERSION);
        /* Check Current Front-Page is Membership Page. */
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        $is_arm_form_in_page = $this->is_arm_form_page();
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection) && !is_admin()) {
            wp_enqueue_style('arm_front_css');
            if ($is_arm_form_in_page || $isFrontSection || $isEnqueueAll == '1') {
                wp_enqueue_style('arm_form_style_css');
				wp_enqueue_style('arm_fontawesome_css');
            }
            wp_enqueue_style('arm_bootstrap_all_css');

            /* Print Custom CSS in Front-End Pages (Required `arm_front_css` handle to add inline css) */
            $arm_add_custom_css_flag = "";
            if ( isset($_GET['_locale']) && $_GET['_locale']=='user' && $this->arm_is_gutenberg_active() ) {
                $arm_add_custom_css_flag = "1";
            }
            
            if(empty($arm_add_custom_css_flag)) {
                $this->arm_set_global_css();
            }
            /**
             * Directory & Profile Templates Style
             */
            if ($isEnqueueAll == '1') {
                wp_enqueue_style('arm_form_style_css');

                $templates = $arm_members_directory->arm_default_member_templates();
                if (!empty($templates)) {
                    foreach ($templates as $tmp) {
                        if (is_file(MEMBERSHIP_VIEWS_DIR . '/templates/' . $tmp['arm_slug'] . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tmp['arm_slug'], MEMBERSHIP_VIEWS_URL . '/templates/' . $tmp['arm_slug'] . '.css', array(), MEMBERSHIP_VERSION);
                        }
                    }
                }
            } else { 
                $found_matches = array();
                $pattern = '\[(\[?)(arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                $posts = $wp_query->posts;
                if (is_array($posts)) {
                    foreach ($posts as $post) {
                        if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                            $found_matches[] = $matches;
                        }
                    }
                    $tempids = array();
                    if (is_array($found_matches) && count($found_matches) > 0) {
                        foreach ($found_matches as $mat) {
                            if (is_array($mat) and count($mat) > 0) {
                                foreach ($mat as $k => $v) {
                                    foreach ($v as $key => $val) {
                                        $parts = explode("id=", $val);
                                        if ($parts > 0 && isset($parts[1])) {
                                            if (stripos(@$parts[1], ']') !== false) {
                                                $partsnew = explode("]", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            } else if (stripos(@$parts[1], ' ') !== false) {
                                                $partsnew = explode(" ", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($tempids) && count($tempids) > 0) {
                    $tempids = $this->arm_array_unique($tempids);
                    foreach ($tempids as $tid) {
                        $tid = trim($tid);
                        /* Query Monitor Change */
			
			
                        if( isset($GLOBALS['arm_profile_template']) && isset($GLOBALS['arm_profile_template'][$tid])){
                            $tempSlug = $GLOBALS['arm_profile_template'][$tid];
                        } else {
                            $tempSlug = $wpdb->get_var("SELECT `arm_slug` FROM `" . $this->tbl_arm_member_templates . "` WHERE `arm_id`='{$tid}' AND `arm_type` != 'profile'");
                            if( !isset($GLOBALS['arm_profile_template']) ){
                                $GLOBALS['arm_profile_template'] = array();
                            }
                            $GLOBALS['arm_profile_template'][$tid] = $tempSlug;
                        }
                        
                        if (is_file(MEMBERSHIP_VIEWS_DIR . '/templates/' . $tempSlug . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tempSlug, MEMBERSHIP_VIEWS_URL . '/templates/' . $tempSlug . '.css', array(), MEMBERSHIP_VERSION);
                        }
                    }
                }
            }
        }
    }

    /**
     * Set global css for related pages shortcode + widget
     */
    function arm_set_global_css($is_echo = true) {
        global $is_globalcss_added, $arm_global_settings;
        $return_global_css = '';
        if (!$is_globalcss_added) {
            $global_custom_css = $arm_global_settings->arm_get_single_global_settings('global_custom_css');
            if (!empty($global_custom_css)) {
                $return_global_css .= '<style type="text/css">';
                $return_global_css .= stripslashes_deep($global_custom_css);
                $return_global_css .= '</style>';
            }
            $is_globalcss_added = true;
        }
        if ($is_echo) {
            echo $return_global_css;
        } else {
            return $return_global_css;
        }
    }

    /* Setting Front Side JavaScript */

    function set_front_js($isFrontSection = false) {
        global $wp, $wpdb, $post, $wp_scripts, $ARMember, $arm_ajaxurl, $arm_slugs, $arm_global_settings;
        /* Check Current Front-Page is Membership Page. */
       
        
        
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection) && !is_admin()) {
            if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
                wp_deregister_script('jquery');
                wp_dequeue_script('jquery');
                wp_enqueue_script('jquery', MEMBERSHIP_URL . '/js/jquery_1.12.4.js', array(), MEMBERSHIP_VERSION);
            } else {
                wp_enqueue_script('jquery');
            }
            
           
            
            /* Main Plugin Front-End JS */
            $arm_common_js_dependencies = array( 'jquery' );
            if (wp_script_is('heartbeat') && is_user_logged_in() )
            {
                $arm_common_js_dependencies[] = 'heartbeat';
            }
            wp_register_script('arm_common_js', MEMBERSHIP_URL . '/js/arm_common.js', $arm_common_js_dependencies, MEMBERSHIP_VERSION);
            wp_register_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);
            /* Tooltip JS */
            wp_register_script('arm_tipso_front', MEMBERSHIP_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIP_VERSION);
            /* File Upload JS */
            wp_register_script('arm_file_upload_js', MEMBERSHIP_URL . '/js/arm_file_upload_js.js', array('jquery'), MEMBERSHIP_VERSION);
            
            /* For bootstrap datetime picker js */
            wp_register_script('arm_bootstrap_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIP_VERSION);

            wp_register_script('arm_bootstrap_datepicker_with_locale_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIP_VERSION);
           
            /* Enqueue Javascripts */
            wp_enqueue_script('jquery-ui-core');
            if (!wp_script_is('arm_bpopup', 'enqueued')) {
                wp_enqueue_script('arm_bpopup');
            }
            
            if (!wp_script_is('arm_bootstrap_js', 'enqueued')) {
                wp_enqueue_script('arm_bootstrap_js');
            }

            if ($isEnqueueAll == '1') {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }

            if (!wp_script_is('arm_common_js', 'enqueued')) {
                wp_enqueue_script('arm_common_js');
            }
            /* Load Angular Assets */
            if ($isEnqueueAll == '1') {
                $this->enqueue_angular_script();
            }
        }

      
    }

    function enqueue_angular_script($include_card_validation = false) {
        global $wp, $wpdb, $post, $arm_errors, $ARMember, $arm_ajaxurl;
        /* Angular Design CSS */
        wp_register_style('arm_angular_material_css', MEMBERSHIP_URL . '/css/arm_angular_material.css', array(), MEMBERSHIP_VERSION);
        wp_enqueue_style('arm_angular_material_css');
        /* Angular JS */
        $angularJSFiles = array(
            'arm_angular_with_material' => MEMBERSHIP_URL . '/js/angular/arm_angular_with_material.js',
            'arm_form_angular' => MEMBERSHIP_URL . '/js/angular/arm_form_angular.js',
        );
        foreach ($angularJSFiles as $handle => $src) {
            if (!wp_script_is($handle, 'registered')) {
                wp_register_script($handle, $src, array(), MEMBERSHIP_VERSION, true);
            }
            if (!wp_script_is($handle, 'enqueued')) {
                wp_enqueue_script($handle);
            }
        }

        if($include_card_validation){
            if (!wp_script_is('arm_angular_credit_card', 'registered')) {
                    wp_register_script('arm_angular_credit_card', MEMBERSHIP_URL . '/js/angular/arm_angular_credit_card.js', array(), MEMBERSHIP_VERSION, true);
            }
            if (!wp_script_is('arm_angular_credit_card', 'enqueued')) {
                    wp_enqueue_script('arm_angular_credit_card');
            }
        }
        
        
    }

    /**
     * Check front page has plugin content.
     */
    function is_arm_front_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm.*)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    function is_arm_setup_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_setup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check if front page content has plugin shortcode and has form.
     */
    function is_arm_form_page() {
        global $wp, $wpdb, $wp_query, $post, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_form|arm_edit_profile|arm_close_account|arm_setup|arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts = $wp_query->posts;
            if (is_array($posts) && !empty($posts)) {
                foreach ($posts as $key => $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }

            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return true;
            }
        }
        return FALSE;
    }

    /*
     * Trim Array Values.
     */

    function arm_array_trim($array) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arm_array_trim($value);
                } else {
                    $array[$key] = trim($value);
                }
                if (empty($array[$key]))
                    unset($array[$key]);
            }
        } else {
            $array = trim($array);
        }
        return $array;
    }

    /**
     * Removes duplicate values from multidimensional array 
     */
    function arm_array_unique($array) {
        $result = array_map("unserialize", array_unique(array_map("serialize", $array)));
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = $this->arm_array_unique($value);
                }
            }
        }
        return $result;
    }

    /**
    * Check is gutenberg active or not function start
    */
    function arm_is_gutenberg_active() {
        //Check Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

        //Version Check Block editor since 5.0.
        $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

        if ( ! $gutenberg && ! $block_editor ) {
            return false;
        }

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
            return true;
        }

        $use_block_editor = get_option( 'classic-editor-replace' ) === 'no-replace';

        return $use_block_editor;
    }
    /**
    * Check is gutenberg active or not function end
    */

     function arm_check_is_gutenberg_page()
     {
        if(function_exists('is_gutenberg_page'))
        {
            if(is_gutenberg_page())
            {
                return true;
            }
        }
        else {
            if ( function_exists( 'get_current_screen' )) {
                $arm_get_current_screen = get_current_screen();
                if(is_object($arm_get_current_screen))
                {
                    if ( isset($arm_get_current_screen->base) && $arm_get_current_screen->base==='post' && $this->arm_is_gutenberg_active() ) {
                        return true;
                    }
                }
            }
        }
        return false;
     }


    /**
     * Restrict Network Activation
     */
    public static function armember_check_network_activation($network_wide) {
        if (!$network_wide)
            return;

        deactivate_plugins(plugin_basename(MEMBERSHIP_DIR.'/armember.php'), TRUE, TRUE);

        header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
        exit;
    }

    public static function install() {


        global $ARMember, $arm_version;
        $armemberlite_exists = 0;
        if(file_exists(WP_PLUGIN_DIR.'/armember-membership/armember-membership.php')){
            $armemberlite_exists = 1;
        }
        $armemberlite_version = get_option('armlite_version', '');

      // if armemberlite folder exists and activated once ( even though currently not activated )
        if( $armemberlite_version != '' && $armemberlite_exists == 1){  

     
            global $ARMember, $arm_version;

            $_version = get_option('arm_version');
            if (empty($_version) || $_version == '') {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                @set_time_limit(0);
                global $wpdb, $arm_version, $arm_global_settings;
                $arm_global_settings->arm_set_ini_for_access_rules();
                $charset_collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset)) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (!empty($wpdb->collate)) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('arm_version', $arm_version);
                update_option('arm_plugin_activated', 1);
                update_option('arm_show_document_video', 1);
           
                update_option('arm_is_social_login_feature', 0);
                update_option('arm_is_drip_content_feature', 0);
                update_option('arm_is_opt_ins_feature', 0);
                update_option('arm_is_coupon_feature', 0);
                update_option('arm_is_buddypress_feature', 0);
                update_option('arm_is_woocommerce_feature', 0);
                update_option('arm_is_multiple_membership_feature', 0);
                update_option('arm_is_mycred_feature', 0);


                update_option('arm_is_invoice_tax_feature', 0);
                $arm_hide_admin_rand_no = wp_rand();
                update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);

                $arm_dbtbl_create = array();
                
                /* Table structure for `auto message` */
                $tbl_arm_auto_message = $wpdb->prefix . 'arm_auto_message';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_auto_message}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_auto_message}`(
                    `arm_message_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_message_type` VARCHAR(50) NOT NULL,
                    `arm_message_period_unit` INT(11) DEFAULT NULL,
                    `arm_message_period_type` VARCHAR(50) DEFAULT NULL,
                    `arm_message_subscription` VARCHAR(255) NOT NULL,
                    `arm_message_subject` TEXT NOT NULL,
                    `arm_message_content` LONGTEXT NOT NULL,
                    `arm_message_status` INT(1) NOT NULL DEFAULT '1',
                                    `arm_message_send_copy_to_admin` INT(1) NOT NULL DEFAULT '0',
                                    `arm_message_send_diff_msg_to_admin` INT(1) NOT NULL DEFAULT '0',
                    `arm_message_admin_message` LONGTEXT,
                    PRIMARY KEY (`arm_message_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_auto_message] = dbDelta($sql_table);

                /* Table structure for `restricted urls` */
                $tbl_arm_coupons = $wpdb->prefix . 'arm_coupons';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_coupons}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_coupons}`(
                    `arm_coupon_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_coupon_code` VARCHAR(255) NOT NULL,
                                    `arm_coupon_label` VARCHAR(255),
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_period_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_coupon_start_date` datetime NOT NULL,
                    `arm_coupon_expire_date` datetime NOT NULL,
                    `arm_coupon_type` TINYINT(1) DEFAULT '0',
                    `arm_coupon_subscription` TEXT,
                    `arm_coupon_paid_posts` TEXT,
                    `arm_coupon_allow_trial` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_allowed_uses` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_used` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_coupon_added_date` datetime NOT NULL,
                    PRIMARY KEY (`arm_coupon_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_coupons] = dbDelta($sql_table);

                

                /* Table structure for `Drip Rules` */
                $tbl_arm_drip_rules = $wpdb->prefix . 'arm_drip_rules';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_drip_rules}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_drip_rules}`(
                    `arm_rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `arm_item_type` varchar(50) DEFAULT NULL,
                    `arm_rule_type` varchar(50) DEFAULT NULL,
                    `arm_show_old_items` INT(11) NOT NULL DEFAULT '0',
                    `arm_rule_options` longtext,
                    `arm_rule_plans` text,
                    `arm_rule_status` int(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_rule_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_drip_rules] = dbDelta($sql_table);

                $tbl_arm_badges_achievements = $wpdb->prefix . 'arm_badges_achievements';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_badges_achievements}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_badges_achievements}`(
                    `arm_badges_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_badges_parent` int(11) NOT NULL DEFAULT '0',
                    `arm_badges_name` varchar(255) DEFAULT NULL,
                    `arm_badges_type` varchar(50) DEFAULT NULL,
                    `arm_badges_icon` TEXT,
                    `arm_badges_achievement` LONGTEXT,
                    `arm_badges_achievement_type` varchar(50) DEFAULT NULL,
                    `arm_badges_tooltip` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`arm_badges_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_badges_achievements] = dbDelta($sql_table);


                $tbl_arm_debug_payment_log = $wpdb->prefix . 'arm_debug_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
                    `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
                    `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_payment_log_status` TINYINT(1) DEFAULT '1',
                    `arm_payment_log_raw_data` TEXT,
                    `arm_payment_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_payment_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

                $tbl_arm_debug_general_log = $wpdb->prefix . 'arm_debug_general_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
                    `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_general_log_event` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_name` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_general_log_raw_data` TEXT,
                    `arm_general_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_general_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);


                if(version_compare($armemberlite_version, '2.1', '<')) 
                {
                    global $wpdb, $wp, $ARMember,$arm_member_forms, $arm_global_settings;
                    
                    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
                    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
                    $arm_bank_table_log_flag=get_option('arm_bank_table_log_flag');

                    $arm_old_plan_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_old_plan_id'");
                    if(empty($arm_old_plan_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");
                    }    

                    $arm_payment_cycle_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_payment_cycle'");
                    if(empty($arm_payment_cycle_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`");
                    }

                    $arm_bank_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_bank_name'");
                    if(empty($arm_bank_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_bank_name` VARCHAR(255) DEFAULT NULL AFTER `arm_payment_cycle`");
                    }

                    $arm_account_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_account_name'");
                    if(empty($arm_account_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_account_name` VARCHAR(255) DEFAULT NULL AFTER `arm_bank_name`");
                    }

                    $arm_additional_info_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_additional_info'");
                    if(empty($arm_additional_info_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_additional_info` LONGTEXT AFTER `arm_account_name`");
                    }

                    $arm_first_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_first_name'");
                    if(empty($arm_first_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_first_name` VARCHAR(255) DEFAULT NULL AFTER `arm_user_id`");
                    }

                    $arm_last_name_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_last_name'");
                    if(empty($arm_last_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_last_name` VARCHAR(255) DEFAULT NULL AFTER `arm_first_name`");
                    }

                    $arm_payment_transfer_mode_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_payment_transfer_mode'");
                    if(empty($arm_payment_transfer_mode_row)) {
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_transfer_mode` VARCHAR( 255 ) NULL AFTER `arm_additional_info`");
                    }

                    if(empty($arm_bank_table_log_flag)){
                        
                        update_option('arm_bank_table_log_flag','1');

                        $btquery = "SELECT * FROM `" . $bt_log_table . "`";
                        $bt_payment_log = $wpdb->get_results($btquery, ARRAY_A);
                        if(count($bt_payment_log)>0){
                            foreach ($bt_payment_log as $bt_payment_log_data) {
                                $arm_first_name=get_user_meta($bt_payment_log_data["arm_user_id"],'first_name',true);
                                $arm_last_name=get_user_meta($bt_payment_log_data["arm_user_id"],'last_name',true);
                                $arm_payment_mode=(!empty($bt_payment_log_data["arm_payment_mode"]))? $bt_payment_log_data["arm_payment_mode"]:'one_time';
                                $arm_payment_type=(!empty($bt_payment_log_data["arm_payment_mode"]) && $bt_payment_log_data["arm_payment_mode"]=='manual_subscription')?'subscription':'one_time';
                                $bt_insert_result=$wpdb->insert($arm_pt_log_table, array(
                                    'arm_invoice_id' => $bt_payment_log_data["arm_invoice_id"],
                                    'arm_user_id' => $bt_payment_log_data["arm_user_id"],
                                    'arm_first_name' => $arm_first_name,
                                    'arm_last_name' => $arm_last_name,
                                    'arm_plan_id' => $bt_payment_log_data["arm_plan_id"],
                                    'arm_old_plan_id' =>$bt_payment_log_data["arm_old_plan_id"],
                                    'arm_payer_email' => $bt_payment_log_data["arm_payer_email"],
                                    'arm_transaction_id' => $bt_payment_log_data["arm_transaction_id"],
                                    'arm_transaction_payment_type'=>$arm_payment_type,
                                    'arm_payment_mode' => $arm_payment_mode,
                                    'arm_payment_type' => $arm_payment_type,
                                    'arm_payment_gateway' => 'bank_transfer',
                                    'arm_payment_cycle' => $bt_payment_log_data["arm_payment_cycle"],
                                    'arm_bank_name' => $bt_payment_log_data["arm_bank_name"],
                                    'arm_account_name' => $bt_payment_log_data["arm_account_name"],
                                    'arm_additional_info' => $bt_payment_log_data["arm_additional_info"],
                                    'arm_amount' => $bt_payment_log_data["arm_amount"],
                                    'arm_currency' => $bt_payment_log_data["arm_currency"],
                                    'arm_extra_vars' => $bt_payment_log_data["arm_extra_vars"],
                                    'arm_coupon_code' => $bt_payment_log_data["arm_coupon_code"],
                                    'arm_coupon_discount' => $bt_payment_log_data["arm_coupon_discount"],
                                    'arm_coupon_discount_type' => $bt_payment_log_data["arm_coupon_discount_type"],
                                    'arm_coupon_on_each_subscriptions' => $bt_payment_log_data["arm_coupon_on_each_subscriptions"],
                                    'arm_transaction_status' => $bt_payment_log_data["arm_status"],
                                    'arm_is_trial' => $bt_payment_log_data["arm_is_trial"],
                                    'arm_display_log' => $bt_payment_log_data["arm_display_log"],
                                    'arm_payment_date' => $bt_payment_log_data["arm_created_date"],
                                    'arm_created_date'=> $bt_payment_log_data["arm_created_date"],
                                ));
                            }
                        }
                    }
                }
                if(version_compare($armemberlite_version, '2.4', '<')) 
                {
                    global $wpdb, $wp, $ARMember;

                    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
                    $arm_entries_table = $ARMember->tbl_arm_entries;
                    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
                    $arm_activity_table = $ARMember->tbl_arm_activity;
                    $arm_membership_setup_table = $ARMember->tbl_arm_membership_setup;

                    $arm_add_payment_log_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_is_post_payment'");
                    if(empty($arm_add_payment_log_col)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_is_trial`");
                    }
                    
                    $arm_add_payment_log_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_pt_log_table."' AND column_name = 'arm_paid_post_id'");
                    if(empty($arm_add_payment_log_col)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_payment`");
                    }
                    
                    $arm_add_entries_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_entries_table."' AND column_name = 'arm_is_post_entry'");
                    if(empty($arm_add_entries_col)){
                        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");
                    }
                    
                    $arm_add_entries_col = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_entries_table."' AND column_name = 'arm_paid_post_id'");
                    if(empty($arm_add_entries_col)){
                        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_entry`");
                    }

                    $arm_add_subscription_plans = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_subscription_plans_table."' AND column_name = 'arm_subscription_plan_post_id'");
                    if(empty($arm_add_subscription_plans)){
                        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_role`");
                    }

                    $arm_add_activity_post_id = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_activity_table."' AND column_name = 'arm_paid_post_id'");
                    if(empty($arm_add_activity_post_id)){
                        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_item_id`");
                    }

                    $arm_add_setup_type = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$arm_membership_setup_table."' AND column_name = 'arm_setup_type'");
                    if(empty($arm_add_setup_type)){
                        $wpdb->query("ALTER TABLE `{$arm_membership_setup_table}` ADD `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_setup_name`");
                    }
                }
                
                $buddypress_settings_array = array('avatar_map'=> 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
                $serialized_buddypress_options = $buddypress_settings_array;
                update_option('arm_buddypress_options', $serialized_buddypress_options);
                
                /* Plugin Action Hook After Install Process */
                do_action('arm_after_activation_hook');
                do_action('arm_after_install');

                
                
                global $arm_members_activity;
                $arm_members_activity->getwpversion();
            } else {
                
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
            $args = array(
                'role' => 'administrator',
                'fields' => 'id'
            );
            $users = get_users($args);
            if (count($users) > 0) {
                foreach ($users as $key => $user_id) {
                    $armroles = $ARMember->arm_capabilities();
                    $userObj = new WP_User($user_id);
                    foreach ($armroles as $armrole => $armroledescription) {
                        $userObj->add_cap($armrole);
                    }
                    unset($armrole);
                    unset($armroles);
                    unset($armroledescription);
                }
            }

        }
        else{
              
            global $ARMember, $arm_version, $arm_access_rules;

            $_version = get_option('arm_version');
            if (empty($_version) || $_version == '') {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                @set_time_limit(0);
                global $wpdb, $arm_version, $arm_global_settings;
                $arm_global_settings->arm_set_ini_for_access_rules();
                $charset_collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset)) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (!empty($wpdb->collate)) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('arm_version', $arm_version);
                update_option('arm_plugin_activated', 1);
                update_option('arm_show_document_video', 1);
                update_option('arm_is_social_feature', 0);
                update_option('arm_is_user_private_content_feature', 0);
                update_option('arm_is_social_login_feature', 0);
                update_option('arm_is_drip_content_feature', 0);
                update_option('arm_is_opt_ins_feature', 0);
                update_option('arm_is_coupon_feature', 0);
                update_option('arm_is_buddypress_feature', 0);
                update_option('arm_is_woocommerce_feature', 0);
                update_option('arm_is_multiple_membership_feature', 0);
                update_option('arm_is_mycred_feature', 0);

                update_option('arm_is_invoice_tax_feature', 0); // 11-1-2018
                $arm_hide_admin_rand_no = wp_rand();
                update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);

                $arm_dbtbl_create = array();
                /* Table structure for `Members activity` */
                $tbl_arm_members_activity = $wpdb->prefix . 'arm_activity';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members_activity}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members_activity}`(
                    `arm_activity_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_type` VARCHAR(50) NOT NULL,
                    `arm_action` VARCHAR(50) NOT NULL,
                    `arm_content` LONGTEXT NOT NULL,
                    `arm_item_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_link` VARCHAR(255) DEFAULT NULL,
                    `arm_ip_address` VARCHAR(50) NOT NULL,
                    `arm_date_recorded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_activity_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members_activity] = dbDelta($sql_table);

                /* Table structure for `auto message` */
                $tbl_arm_auto_message = $wpdb->prefix . 'arm_auto_message';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_auto_message}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_auto_message}`(
                    `arm_message_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_message_type` VARCHAR(50) NOT NULL,
                    `arm_message_period_unit` INT(11) DEFAULT NULL,
                    `arm_message_period_type` VARCHAR(50) DEFAULT NULL,
                    `arm_message_subscription` VARCHAR(255) NOT NULL,
                    `arm_message_subject` TEXT NOT NULL,
                    `arm_message_content` LONGTEXT NOT NULL,
                    `arm_message_status` INT(1) NOT NULL DEFAULT '1',
                                    `arm_message_send_copy_to_admin` INT(1) NOT NULL DEFAULT '0',
                                    `arm_message_send_diff_msg_to_admin` INT(1) NOT NULL DEFAULT '0',
                    `arm_message_admin_message` LONGTEXT,
                    PRIMARY KEY (`arm_message_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_auto_message] = dbDelta($sql_table);

                /* Table structure for `restricted urls` */
                $tbl_arm_coupons = $wpdb->prefix . 'arm_coupons';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_coupons}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_coupons}`(
                    `arm_coupon_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_coupon_code` VARCHAR(255) NOT NULL,
                                    `arm_coupon_label` VARCHAR(255),
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_period_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_coupon_start_date` datetime NOT NULL,
                    `arm_coupon_expire_date` datetime NOT NULL,
                    `arm_coupon_type` TINYINT(1) DEFAULT '0',
                    `arm_coupon_subscription` TEXT,
                    `arm_coupon_paid_posts` TEXT,
                    `arm_coupon_allow_trial` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_allowed_uses` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_used` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_coupon_added_date` datetime NOT NULL,
                    PRIMARY KEY (`arm_coupon_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_coupons] = dbDelta($sql_table);

                /* Table structure for `email settings` */
                $tbl_arm_email_settings = $wpdb->prefix . 'arm_email_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_email_settings}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_email_settings}`(
                    `arm_template_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_template_name` VARCHAR(255) NOT NULL,
                    `arm_template_slug` VARCHAR(255) NOT NULL ,
                    `arm_template_subject` VARCHAR(255) NOT NULL,
                    `arm_template_content` longtext NOT NULL,
                    `arm_template_status` INT(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`arm_template_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_email_settings] = dbDelta($sql_table);

                /* Table structure for `Entries` */
                $tbl_arm_entries = $wpdb->prefix . 'arm_entries';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_entries}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_entries}` (
                    `arm_entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_entry_email` varchar(255) DEFAULT NULL,
                    `arm_name` varchar(255) DEFAULT NULL,
                    `arm_description` LONGTEXT,
                    `arm_ip_address` text,
                    `arm_browser_info` text,
                    `arm_entry_value` LONGTEXT,
                    `arm_form_id` int(11) DEFAULT NULL,
                    `arm_user_id` bigint(20) DEFAULT NULL,
                    `arm_plan_id` int(11) DEFAULT NULL,
                    `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_entry_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_entries] = dbDelta($sql_table);

                /* Table structure for `failed login` */
                $tbl_arm_fail_attempts = $wpdb->prefix . 'arm_fail_attempts';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_fail_attempts}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_fail_attempts}`(
                    `arm_fail_attempts_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_fail_attempts_detail` text,
                    `arm_fail_attempts_ip` varchar(200) DEFAULT NULL,
                    `arm_is_block` int(1) NOT NULL DEFAULT '0',
                    `arm_fail_attempts_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_fail_attempts_release_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_fail_attempts_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_fail_attempts] = dbDelta($sql_table);

                /* Table structure for `arm_forms` */
                $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_forms}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_forms}` (
                    `arm_form_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_label` VARCHAR(255) DEFAULT NULL,
                    `arm_form_title` VARCHAR(255) DEFAULT NULL,
                    `arm_form_type` VARCHAR(100) DEFAULT NULL,
                    `arm_form_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_is_default` INT(1) NOT NULL DEFAULT '0',
                    `arm_set_name` VARCHAR(255) DEFAULT NULL,
                    `arm_set_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_is_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_ref_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_settings` LONGTEXT,
                    `arm_form_updated_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_form_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_form_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_forms] = dbDelta($sql_table);

                /* Table structure for `arm_form_field` */
                $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_form_field}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_form_field}`(
                    `arm_form_field_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_field_form_id` INT(11) NOT NULL,
                    `arm_form_field_order` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_form_field_option` LONGTEXT,
                                    `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_form_field_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_form_field_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_form_field] = dbDelta($sql_table);

                /* Table structure for `lockdown` */
                $tbl_arm_lockdown = $wpdb->prefix . 'arm_lockdown';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_lockdown}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_lockdown}`(
                    `arm_lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_lockdown_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_release_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_lockdown_IP` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY  (`arm_lockdown_ID`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_lockdown] = dbDelta($sql_table);

                /* Table structure for `arm_members` */
                $tbl_arm_members = $wpdb->prefix . 'arm_members';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members}` (
                  `arm_member_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `arm_user_id` bigint(20) unsigned NOT NULL,
                  `arm_user_login` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_pass` VARCHAR(64) NOT NULL DEFAULT '',
                  `arm_user_nicename` VARCHAR(50) NOT NULL DEFAULT '',
                  `arm_user_email` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_url` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `arm_user_activation_key` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_status` INT(11) NOT NULL DEFAULT '0',
                  `arm_display_name` VARCHAR(250) NOT NULL DEFAULT '',
                  `arm_user_type` int(1) NOT NULL DEFAULT '0',
                  `arm_primary_status` int(1) NOT NULL DEFAULT '1',
                  `arm_secondary_status` int(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`arm_member_id`),
                  KEY `arm_user_login_key` (`arm_user_login`),
                  KEY `arm_user_nicename` (`arm_user_nicename`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members] = dbDelta($sql_table);

                /* Table structure for `Membership Setup Wizard` */
                $tbl_arm_membership_setup = $wpdb->prefix . 'arm_membership_setup';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_membership_setup}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_membership_setup}`(
                    `arm_setup_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_setup_name` VARCHAR(255) NOT NULL,
                    `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_setup_modules` LONGTEXT,
                    `arm_setup_labels` LONGTEXT,
                    `arm_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_setup_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_membership_setup] = dbDelta($sql_table);

                /* Table structure for `Payment Log` */
                $tbl_arm_payment_log = $wpdb->prefix . 'arm_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_payment_log}`(
                    `arm_log_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_invoice_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_first_name` VARCHAR(255) DEFAULT NULL,
                    `arm_last_name` VARCHAR(255) DEFAULT NULL,
                    `arm_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_payment_gateway` VARCHAR(50) NOT NULL,
                    `arm_payment_type` VARCHAR(50) NOT NULL,
                    `arm_token` TEXT,
                    `arm_payer_email` VARCHAR(255) DEFAULT NULL,
                    `arm_receiver_email` VARCHAR(255) DEFAULT NULL,
                    `arm_transaction_id` TEXT,
                    `arm_transaction_payment_type` VARCHAR(100) DEFAULT NULL,
                    `arm_transaction_status` TEXT,
                    `arm_payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `arm_payment_mode` VARCHAR(255),
                    `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0',
                    `arm_bank_name` VARCHAR(255) DEFAULT NULL,
                    `arm_account_name` VARCHAR(255) DEFAULT NULL,
                    `arm_additional_info` LONGTEXT,
                    `arm_payment_transfer_mode` VARCHAR(255) DEFAULT NULL,
                    `arm_amount` double NOT NULL DEFAULT '0',
                    `arm_currency` VARCHAR(50) DEFAULT NULL,
                    `arm_extra_vars` LONGTEXT,
                    `arm_coupon_code` VARCHAR(255) DEFAULT NULL,
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) DEFAULT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_trial` INT(1) NOT NULL DEFAULT '0',
                    `arm_display_log` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_payment_log] = dbDelta($sql_table);

                
                /* Table structure for `arm_subscription_plans` */
                $tbl_arm_subscription_plans = $wpdb->prefix . 'arm_subscription_plans';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_subscription_plans}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_subscription_plans}`(
                    `arm_subscription_plan_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_subscription_plan_name` VARCHAR(255) NOT NULL,
                    `arm_subscription_plan_description` TEXT,
                    `arm_subscription_plan_type` VARCHAR(50) NOT NULL,
                    `arm_subscription_plan_options` LONGTEXT,
                    `arm_subscription_plan_amount` double NOT NULL DEFAULT '0',
                    `arm_subscription_plan_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_subscription_plan_role` VARCHAR(100) DEFAULT NULL,
                    `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_is_delete` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_subscription_plan_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_subscription_plans] = dbDelta($sql_table);

                /* Table structure for `Taxonomy Term Meta` */
                $tbl_arm_termmeta = $wpdb->prefix . 'arm_termmeta';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_termmeta}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_termmeta}`(
                    `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(255) DEFAULT NULL,
                    `meta_value` longtext,
                    PRIMARY KEY (`meta_id`),
                    KEY `arm_term_id` (`arm_term_id`),
                    KEY `meta_key` (`meta_key`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_termmeta] = dbDelta($sql_table);

                /* Table structure for `Member Templates` */
                $tbl_arm_member_templates = $wpdb->prefix . 'arm_member_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_member_templates}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_member_templates}`(
                    `arm_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_title` text,
                    `arm_slug` varchar(255) DEFAULT NULL,
                    `arm_type` varchar(50) DEFAULT NULL,
                    `arm_default` int(1) NOT NULL DEFAULT '0',
                                    `arm_subscription_plan` text NULL,
                    `arm_core` int(1) NOT NULL DEFAULT '0',
                                    `arm_template_html` longtext,
                                    `arm_ref_template` int(11) NOT NULL DEFAULT '0',
                    `arm_options` longtext,
                    `arm_html_before_fields` longtext,
                    `arm_html_after_fields` longtext,
                    `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_member_templates] = dbDelta($sql_table);

                /* Table structure for `Drip Rules` */
                $tbl_arm_drip_rules = $wpdb->prefix . 'arm_drip_rules';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_drip_rules}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_drip_rules}`(
                    `arm_rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `arm_item_type` varchar(50) DEFAULT NULL,
                    `arm_rule_type` varchar(50) DEFAULT NULL,
                    `arm_show_old_items` INT(11) NOT NULL DEFAULT '0',
                    `arm_rule_options` longtext,
                    `arm_rule_plans` text,
                    `arm_rule_status` int(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_rule_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_drip_rules] = dbDelta($sql_table);

                $tbl_arm_badges_achievements = $wpdb->prefix . 'arm_badges_achievements';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_badges_achievements}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_badges_achievements}`(
                    `arm_badges_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_badges_parent` int(11) NOT NULL DEFAULT '0',
                    `arm_badges_name` varchar(255) DEFAULT NULL,
                    `arm_badges_type` varchar(50) DEFAULT NULL,
                    `arm_badges_icon` TEXT,
                    `arm_badges_achievement` LONGTEXT,
                    `arm_badges_achievement_type` varchar(50) DEFAULT NULL,
                    `arm_badges_tooltip` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`arm_badges_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_badges_achievements] = dbDelta($sql_table);

                $tbl_arm_login_history = $wpdb->prefix . 'arm_login_history';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_login_history}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_login_history}`(
                    `arm_history_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) NOT NULL,
                    `arm_logged_in_ip` varchar(255) NOT NULL,
                    `arm_logged_in_date` DATETIME NOT NULL,
                    `arm_logout_date` DATETIME NOT NULL,
                    `arm_login_duration` TIME NOT NULL,
                    `arm_history_browser` VARCHAR(255) NOT NULL,
                    `arm_history_session` VARCHAR(255) NOT NULL,
                    `arm_login_country` VARCHAR(255) NOT NULL,
                                    `arm_user_current_status` int(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`arm_history_id`)
                ){$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_login_history] = dbDelta($sql_table);


                $tbl_arm_debug_payment_log = $wpdb->prefix . 'arm_debug_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
                    `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
                    `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_payment_log_status` TINYINT(1) DEFAULT '1',
                    `arm_payment_log_raw_data` TEXT,
                    `arm_payment_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_payment_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

                $tbl_arm_debug_general_log = $wpdb->prefix . 'arm_debug_general_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
                    `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_general_log_event` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_name` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_general_log_raw_data` TEXT,
                    `arm_general_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`arm_general_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);

                

                /* Install Default Template Forms & Fields */
                $ARMember->install_default_templates();
                $wpdb->query("ALTER TABLE `{$tbl_arm_forms}` AUTO_INCREMENT = 101");
                /* Install Default Member Forms & Fields. */
                $ARMember->install_member_form_fields();
                /* Install Default Pages. */
                $ARMember->install_default_pages();
                /* Update Page in default template */
                $ARMember->update_default_pages_for_templates();
                /* Create Custom User Role & Capabilities. */
                $ARMember->add_user_role_and_capabilities();

                 $arm_access_rules->install_redirection_settings();
                
                $buddypress_settings_array = array('avatar_map'=> 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
                $serialized_buddypress_options = maybe_serialize($buddypress_settings_array);
                update_option('arm_buddypress_options', $serialized_buddypress_options);
                
                /* Plugin Action Hook After Install Process */
                do_action('arm_after_activation_hook');
                do_action('arm_after_install');

                
                
                global $arm_members_activity;
                $arm_members_activity->getwpversion();
            } else {
                
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
            $args = array(
                'role' => 'administrator',
                'fields' => 'id'
            );
            $users = get_users($args);
            if (count($users) > 0) {
                foreach ($users as $key => $user_id) {
                    $armroles = $ARMember->arm_capabilities();
                    $userObj = new WP_User($user_id);
                    foreach ($armroles as $armrole => $armroledescription) {
                        $userObj->add_cap($armrole);
                    }
                    unset($armrole);
                    unset($armroles);
                    unset($armroledescription);
                }
            }
        }


        
    }

    function install_default_templates() {
        include(MEMBERSHIP_CLASSES_DIR . '/templates.arm_member_forms_templates.php');
    }

    function update_default_pages_for_templates() {
        global $wpdb, $ARMember;
        $global_settings = get_option('arm_global_settings');
        $arm_settings = maybe_unserialize($global_settings);
        $page_settings = $arm_settings['page_settings'];
        $template_slugs_query = " WHERE (`arm_form_slug` LIKE 'template-login%' OR `arm_form_slug` LIKE 'template-registration%' OR `arm_form_slug` LIKE 'template-forgot%' OR `arm_form_slug` LIKE 'template-change%') AND arm_is_template = 1";
        $forms = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` {$template_slugs_query}");
        if (count($forms) > 0) {
            foreach ($forms as $key => $value) {
                $form_id = $value->arm_form_id;
                $form_settings = maybe_unserialize($value->arm_form_settings);
                $form_settings['redirect_page'] = $page_settings['edit_profile_page_id'];
                $form_settings['registration_link_type_page'] = $page_settings['register_page_id'];
                $form_settings['forgot_password_link_type_page'] = $page_settings['forgot_password_page_id'];
                $form_settings = maybe_serialize($form_settings);
                $formData = array('arm_form_settings' => $form_settings);
                $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
            }
        }
    }

    function arm_install_plugin_data() {
        global $wp, $wpdb, $arm_members_directory, $arm_access_rules, $arm_email_settings, $arm_subscription_plans, $arm_members_badges;
        $is_activate = get_option('arm_plugin_activated', 0);
        if ($is_activate == '1') {
            delete_option('arm_plugin_activated');
            /**
             * Install Plugin Default Data For The First Time.
             */
            /* Create Free Plan. */
            $arm_subscription_plans->arm_insert_sample_subscription_plan();
            /* Install default templates */
            $arm_email_settings->arm_insert_default_email_templates();
            /* Install Default Profile Template */
            $arm_members_directory->arm_insert_default_member_templates();
            /* Install default badges */
            $arm_members_badges->arm_insert_default_badges();
            /* Install Default Rules */
            $arm_access_rules->install_rule_data();
            
           
        }


    

    }

    /**
     * Add Custom User Role & Capabilities
     */
    function add_user_role_and_capabilities() {
        global $wp, $wpdb, $wp_roles, $ARMember, $arm_members_class, $arm_global_settings;
        $role_name = "ARMember";
        $role_slug = sanitize_title($role_name);
        $basic_caps = array(
            $role_slug => true,
            'read' => true,
            'level_0' => true,
        );

        $wp_roles->add_role($role_slug, $role_name, $basic_caps);
        $arm_user_role = $wp_roles->get_role($role_slug);

        $wpdb->query("DELETE FROM `$ARMember->tbl_arm_members`");

        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;
        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = "SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = '{$capability_column}'";
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users";
        }
        $allMembers = $wpdb->get_results($query_to_get_remainig_users);
        $chunk_size = 100;
        if (!empty($allMembers)) {

            $arm_total_users = count($allMembers);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $i = 0;
            $chunked_values = '';
            foreach ($allMembers as $member) {
                $i++;
                $user_id = $member->ID;
                $arm_user_id = $user_id;
                $arm_user_login = $member->user_login;
                $arm_user_pass = $member->user_pass;
                $arm_user_nicename = $member->user_nicename;
                $arm_user_email = $member->user_email;
                $arm_user_url = $member->user_url;
                $arm_user_registered = $member->user_registered;
                $arm_user_activation_key = $member->user_activation_key;
                $arm_user_status = $member->user_status;
                $arm_display_name = $member->display_name;
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && (!empty($chunked_values) || $chunked_values != '')) {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
                    $i = 0;
                    $chunked_values = '';
                }
            }
            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
            }
        }
    }

    /**
     * Check and Add Custom User Role & Capabilities for new users - after plugin reactivation
     */
    
    function check_new_users_after_plugin_reactivation() {

        global $wpdb, $ARMember;
        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;

        $get_all_armembers = $wpdb->get_results("select * from $ARMember->tbl_arm_members", ARRAY_A);
        $push_user_ids = array();
        $where = "WHERE 1=1";
        $where1 = '';
        foreach ($get_all_armembers as $new_user_id) {
            $push_user_ids[] = $new_user_id['arm_user_id'];
        }
        if (!empty($push_user_ids)) {
            if (is_multisite()) {
                $where1 = " AND u.ID NOT IN (" . implode(", ", $push_user_ids) . ") ";
            } else {
                $where .= " AND `ID` NOT IN (" . implode(", ", $push_user_ids) . ") ";
            }
        }

        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = "SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = '{$capability_column}' {$where1}";
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users {$where}";
        }
        
        $list_to_include_new_users = $wpdb->get_results($query_to_get_remainig_users, ARRAY_A);

        if (!empty($list_to_include_new_users)) {

            $arm_total_users = count($list_to_include_new_users);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $chunked_values = '';
            $i = 0;
            foreach ($list_to_include_new_users as $key => $new_users_data) {
                $i++;
                $arm_user_id = $new_users_data['ID'];
                $arm_user_login = $new_users_data['user_login'];
                $arm_user_pass = $new_users_data['user_pass'];
                $arm_user_nicename = $new_users_data['user_nicename'];
                $arm_user_email = $new_users_data['user_email'];
                $arm_user_url = $new_users_data['user_url'];
                $arm_user_registered = $new_users_data['user_registered'];
                $arm_user_activation_key = $new_users_data['user_activation_key'];
                $arm_user_status = $new_users_data['user_status'];
                $arm_display_name = $new_users_data['display_name'];
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_pass . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && $chunked_values != '') {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
                    $i = 0;
                    $chunked_values = '';
                }
            }


            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_pass,arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);
            }
        }
    }

    /**
     * Install Default Member Forms & thier fields into Database
     */
    function install_member_form_fields() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Add Default Preset Fields */
        $defaultFields = $arm_member_forms->arm_default_preset_user_fields();
        unset($defaultFields['social_fields']);
        $defaultPresetFields = array('default' => $defaultFields);
        update_option('arm_preset_form_fields', $defaultPresetFields);
        /* Add Default Forms */
        $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
        $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';

        $default_member_forms_data = $arm_member_forms->arm_default_member_forms_data();
        $insertedFields = array();
        foreach ($default_member_forms_data as $key => $val) {
            $arm_set_id = 0;
            $arm_set_name = '';
            if (in_array($key, array('login', 'forgot_password', 'change_password'))) {
                $arm_set_name = __('Default Set', 'ARMember');
                $arm_set_id = 1;
            }
            $form_data = array(
                'arm_form_label' => $val['name'],
                'arm_form_title' => $val['name'],
                'arm_form_type' => $key,
                'arm_form_slug' => sanitize_title($val['name']),
                'arm_is_default' => '1',
                'arm_set_name' => $arm_set_name,
                'arm_set_id' => $arm_set_id,
                'arm_ref_template' => '1',
                'arm_form_updated_date' => date('Y-m-d H:i:s'),
                'arm_form_created_date' => date('Y-m-d H:i:s'),
                'arm_form_settings' => maybe_serialize($val['settings'])
            );
            /* Insert Form Data */
            $wpdb->insert($tbl_arm_forms, $form_data);
            $form_id = $wpdb->insert_id;
            if (!empty($val['fields'])) {
                $i = 1;
                foreach ($val['fields'] as $field) {
                    $fid = isset($field['id']) ? $field['id'] : $field['meta_key'];
                    if ($fid == 'repeat_pass') {
                        $field['ref_field_id'] = $insertedFields[$key]['user_pass'];
                    }
                    $form_field_data = array(
                        'arm_form_field_form_id' => $form_id,
                        'arm_form_field_order' => $i,
                        'arm_form_field_slug' => isset($field['meta_key']) ? $field['meta_key'] : '',
                        'arm_form_field_created_date' => date('Y-m-d H:i:s'),
                        'arm_form_field_option' => maybe_serialize($field)
                    );
                    /* Insert Form Fields. */
                    $wpdb->insert($tbl_arm_form_field, $form_field_data);
                    $insert_field_id = $wpdb->insert_id;
                    $insertedFields[$key][$fid] = $insert_field_id;
                    $i++;
                }
            }
        }
    }

    /**
     * Install Default Plugin Pages into Database
     */
    function install_default_pages() {
        global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Default Global Settings */
        $arm_settings = $arm_global_settings->arm_default_global_settings();
        /* Default Pages */
        $arm_pages = $arm_global_settings->arm_default_pages_content();
        if (!empty($arm_pages)) {
            foreach ($arm_pages as $pageIDKey => $page) {
                $page_id = wp_insert_post($page);
                if ($page_id != 0) {
                    $arm_settings['page_settings'][$pageIDKey] = $page_id;
                }
            }
        }
        /* Store Global Setting into DB */
        if (!empty($arm_settings)) {
            $new_global_settings = maybe_serialize($arm_settings);
            update_option('arm_global_settings', $new_global_settings);
            /**
             * Update Redirection pages in member forms
             */
            $allForms = $arm_member_forms->arm_get_all_member_forms('`arm_form_id`, `arm_form_type`, `arm_form_settings`');
            if (!empty($allForms)) {
                foreach ($allForms as $form) {
                    $form_id = $form['arm_form_id'];
                    $form_settings = $form['arm_form_settings'];
                    $isFormUpdate = false;
                    switch ($form['arm_form_type']) {
                        case 'registration':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            break;
                        case 'login':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            $form_settings['registration_link_type'] = 'page';
                            $form_settings['registration_link_type_page'] = $arm_settings['page_settings']['register_page_id'];
                            $form_settings['forgot_password_link_type_page'] = $arm_settings['page_settings']['forgot_password_page_id'];
                            break;
                    }
                    if ($isFormUpdate) {
                        $formData = array('arm_form_settings' => maybe_serialize($form_settings));
                        $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
                    }
                }
            }
        }
        /* Update Security Settings */
        $securitySettings = $arm_global_settings->arm_get_all_block_settings();
        update_option('arm_block_settings', $securitySettings);
    }

    public static function uninstall() {
        global $wpdb;
        $armember_uninstall = false;
        if(!file_exists(WP_PLUGIN_DIR.'/armember-membership/armember-membership.php')){
            $armember_uninstall = true;
        }
        else{
            $armemberlite_version = get_option('armlite_version'); 
            if (empty($armemberlite_version) || $armemberlite_version == '') {
                $armember_uninstall = true;
            }
        }
        if (is_multisite()) {
            $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
            if ($blogs) {
                foreach ($blogs as $blog) {
                    switch_to_blog($blog['blog_id']);
                    delete_option('arm_version');
                    if($armember_uninstall){
                        self::arm_uninstall();
                    }

                    delete_option('arm_is_user_private_content_feature');
                    delete_option('arm_is_social_login_feature');
                    delete_option('arm_is_drip_content_feature');
                    delete_option('arm_is_opt_ins_feature');
                    delete_option('arm_is_coupon_feature');
                    delete_option('arm_is_buddypress_feature');
                    delete_option('arm_is_invoice_tax_feature');
                    delete_option('arm_is_multiple_membership_feature');
                    delete_option('arm_is_mycred_feature');
                    delete_option('arm_is_woocommerce_feature');
                    delete_option('arm_is_pay_per_post_feature');
                    
                }
                restore_current_blog();
            }
        } else {
            if($armember_uninstall){
                        self::arm_uninstall();
                    }
            delete_option('arm_is_user_private_content_feature');
            delete_option('arm_is_social_login_feature');
            delete_option('arm_is_drip_content_feature');
            delete_option('arm_is_opt_ins_feature');
            delete_option('arm_is_coupon_feature');
            delete_option('arm_is_buddypress_feature');
            delete_option('arm_is_invoice_tax_feature');
            delete_option('arm_is_multiple_membership_feature');
            delete_option('arm_is_mycred_feature');
            delete_option('arm_is_woocommerce_feature');
            delete_option('arm_is_pay_per_post_feature');
        }
        /* Plugin Action Hook After Uninstall Process */
        do_action('arm_after_uninstall');
    }

    public static function arm_uninstall() {
        global $wpdb, $arm_members_class;
        /**
         * To Cancel User's Recurring Subscription from Payment Gateway
         */

        $select_member_users = "SELECT arm_user_id FROM ". $wpdb->prefix . 'arm_members';
        $query_member_users = $wpdb->get_results($select_member_users);
        if(!empty($query_member_users))
        {
            foreach ($query_member_users as $query_member_user) {
                $chk_subscription_arm_user_id = $query_member_user->arm_user_id;
                $arm_members_class->arm_before_delete_user_action($chk_subscription_arm_user_id);
            }
        }


        /**
         * Delete Meta Values
         */
        $wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  '%arm\_%'");
        $wpdb->query("DELETE FROM `" . $wpdb->postmeta . "` WHERE  `meta_key` LIKE  '%arm\_%'");
        $wpdb->query("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  '%arm\_%'");



        delete_option("armIsSorted");
        delete_option("armSortOrder");
        delete_option("armSortId");
        delete_option("armSortInfo");
		delete_option("armBadgeUpdated");
		delete_option("armIsBadgeUpdated");
		delete_option('arm_badgeupdaterequired');
        delete_option("arm_new_version_installed");

        delete_site_option("armIsSorted");
        delete_site_option("armSortOrder");
        delete_site_option("armSortId");
        delete_site_option("armSortInfo");
		delete_site_option("armBadgeUpdated");
		delete_site_option("armIsBadgeUpdated");
		delete_site_option('arm_badgeupdaterequired');		
        delete_site_option("arm_version_1_7_installed");

        /**
         * Delete Plugin DB Tables
         */
        $blog_tables = array(
            $wpdb->prefix . 'arm_activity',
            $wpdb->prefix . 'arm_auto_message',
            $wpdb->prefix . 'arm_coupons',
            $wpdb->prefix . 'arm_email_templates',
            $wpdb->prefix . 'arm_entries',
            $wpdb->prefix . 'arm_fail_attempts',
            $wpdb->prefix . 'arm_forms',
            $wpdb->prefix . 'arm_form_field',
            $wpdb->prefix . 'arm_lockdown',
            $wpdb->prefix . 'arm_members',
            $wpdb->prefix . 'arm_membership_setup',
            $wpdb->prefix . 'arm_payment_log',
            $wpdb->prefix . 'arm_payment_log_temp',
            $wpdb->prefix . 'arm_bank_transfer_log',
            $wpdb->prefix . 'arm_subscription_plans',
            $wpdb->prefix . 'arm_termmeta',
            $wpdb->prefix . 'arm_member_templates',
            $wpdb->prefix . 'arm_drip_rules',
            $wpdb->prefix . 'arm_badges_achievements',
            $wpdb->prefix . 'arm_login_history'
        );
        foreach ($blog_tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table ");
        }
        return true;
    }

    /**
     * Get Current Browser Info
     */
    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $ub = "";
        /* First get the platform? */
        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        /* Next get the name of the useragent yes seperately and for good reason */
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "OPR";
        } elseif (@preg_match('/Edg/i', $u_agent)) {
            $bname = 'Microsoft Edge';
            $ub = "Edg";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (@preg_match('/Trident/', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "rv";
        }
        /* finally get the correct version number */
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            /* we have no matching number just continue */
        }

        /* see how many we have */
        $i = count($matches['browser']);
        if ($i != 1) {
            /* we will have two since we are not using 'other' argument yet */
            /* see if version is before or after the name */
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        /* check if we have a number */
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /**
     * Get Current IP Address of User/Guest
     */
    function arm_get_ip_address() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        /* For Public IP Address. */
        /* $publicIP = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com")); */
        return $ipaddress;
    }

    function arm_write_response($response_data, $file_name = '') {
        global $wp, $wpdb, $wp_filesystem;
        if (!empty($file_name)) {
            $file_path = MEMBERSHIP_DIR . '/log/' . $file_name;
        } else {
            $file_path = MEMBERSHIP_DIR . '/log/response.txt';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($file_path, '', false, false) )) {
                /**
                 * if we get here, then we don't have credentials yet,
                 * but have just produced a form for the user to fill in,
                 * so stop processing for now
                 */
                return true; /* stop the normal page form from displaying */
            }
            /* now we have some credentials, try to get the wp_filesystem running */
            if (!WP_Filesystem($creds)) {
                /* our credentials were no good, ask the user for them again */
                request_filesystem_credentials($file_path, $method, true, false);
                return true;
            }
            @$file_data = $wp_filesystem->get_contents($file_path);
            $file_data .= $response_data;
            $file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $file_data = str_ireplace($breaks, "\r\n", $file_data);
            
            @$write_file = $wp_filesystem->put_contents($file_path, $file_data, 0755);
            if (!$write_file) {
                /* _e('Error Saving Log.', 'ARMember'); */
            }
        }
        return;
    }


    function arm_write_payment_log($arm_log_payment_gateway, $arm_log_event, $arm_log_event_from = 'armember', $arm_payment_log_raw_data = '', $arm_ref_id = 0, $arm_log_status = 1)
    {
        global $wpdb, $ARMember, $arm_payment_gateways, $arm_debug_payment_log_id, $arm_capabilities_global, $arm_payment_gateways_data_logs, $arm_payment_gateways_data_logs_flag;

        if(empty($arm_payment_gateways_data_logs) && empty($arm_payment_gateways_data_logs_flag) )
        {
            $arm_payment_gateways_data_logs = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
            $arm_payment_gateways_data_logs_flag = 1;
        }
        $inserted_id = 0;
        if( !empty($arm_payment_gateways_data_logs[$arm_log_payment_gateway]['payment_debug_logs']) )
        {
            $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;

            if($arm_ref_id==NULL) { $arm_ref_id = 0; }
            $arm_database_log_data = array(
                'arm_payment_log_ref_id' => $arm_ref_id,
                'arm_payment_log_gateway' => $arm_log_payment_gateway,
                'arm_payment_log_event' => $arm_log_event,
                'arm_payment_log_event_from' => $arm_log_event_from,
                'arm_payment_log_status' => $arm_log_status,
                'arm_payment_log_raw_data' => maybe_serialize(stripslashes_deep($arm_payment_log_raw_data)),
                'arm_payment_log_added_date' => current_time('mysql'),
            );
            
            //If reference id empty then insert log.
            $wpdb->insert($tbl_arm_debug_payment_log, $arm_database_log_data);
            $inserted_id = $wpdb->insert_id;
            if(empty($arm_ref_id))
            {
                $arm_ref_id = $inserted_id;
            }
        }
        $arm_debug_payment_log_id = $arm_ref_id;

        return $inserted_id;
    }



    function arm_write_general_log($arm_log_event, $arm_log_event_name, $arm_log_event_from = 'armember', $arm_payment_log_raw_data = '')
    {
        global $wpdb, $ARMember, $arm_debug_general_log_id, $arm_capabilities_global, $arm_email_settings, $arm_is_cron_log_enabled, $arm_is_cron_log_check_flag, $arm_is_opt_ins_log_enabled, $arm_is_opt_ins_log_check_flag;

        if ($arm_log_event == 'cron') 
        {
            if ( empty($arm_is_cron_log_enabled) && empty($arm_is_cron_log_check_flag) ) 
            {
                $arm_is_cron_log_enabled = get_option('arm_cron_debug_log');
                $arm_is_cron_log_check_flag = 1;
            }
            $arm_is_log_enabled = $arm_is_cron_log_enabled;
        } 
        else 
        {
            if ($arm_email_settings->isOptInsFeature && empty($arm_is_opt_ins_log_enabled) && empty($arm_is_opt_ins_log_check_flag)) 
            {
                $arm_is_opt_ins_log_enabled = get_option('arm_optins_debug_log');
                $arm_is_opt_ins_log_check_flag = 1;
            }
            $arm_is_log_enabled = $arm_is_opt_ins_log_enabled;
        }
        
        $inserted_id = 0;
        if ( !empty($arm_is_log_enabled) ) 
        {
            $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
            $arm_database_log_data = array(
                'arm_general_log_event' => $arm_log_event,
                'arm_general_log_event_name' => $arm_log_event_name,
                'arm_general_log_event_from' => $arm_log_event_from,
                'arm_general_log_raw_data' => maybe_serialize(stripslashes_deep($arm_payment_log_raw_data)),
                'arm_general_log_added_date' => current_time('mysql'),
            );
            
            $wpdb->insert($tbl_arm_debug_general_log, $arm_database_log_data);
            $inserted_id = $wpdb->insert_id;
        }
        
        return $inserted_id;
    }

    /**
     * Function for Write Degug Log
     */
    function arm_debug_response_log($callback = '', $arm_restricted_cases = array(), $query_obj = array(), $executed_query = '', $is_mail_log = false) {
        global $wp, $wpdb, $wp_filesystem;
        if (!defined('MEMBERSHIP_DEBUG_LOG') || MEMBERSHIP_DEBUG_LOG == false) {
            return;
        }
        $arm_restricted_cases_filtered = "";
        if ($executed_query == "") {
            $executed_query = $wpdb->last_query;
        }
        $arm_restriction_type = 'redirect';
        if (!empty($arm_restricted_cases)) {
            foreach ($arm_restricted_cases as $key => $restricted_case) {
                if ($restricted_case['protected'] == true) {
                    $arm_restricted_cases_filtered = $arm_restricted_cases[$key]["message"];
                    $arm_restriction_type = $arm_restricted_cases[$key]['type'];
                }
            }
        }
        $arm_debug_file_path = MEMBERSHIP_DIR . '/log/restriction_response.txt';
        $date = "[ " . date(get_option('date_format') . ' ' . get_option('time_format')) . " ]";
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($arm_debug_file_path, '', false, false) )) {
                return true;
            }
            if (!WP_Filesystem($creds)) {
                request_filesystem_credentials($arm_debug_file_path, $method, true, false);
                return true;
            }
            $debug_log_type = MEMBERSHIP_DEBUG_LOG_TYPE;
            $content = " Date: " . $date . "\r\n";
            $content .= "\r\n Function :" . $callback . "\r\n";
            if ($is_mail_log == true) {
                $content .= "\r\n Log Type : Mail Notification Log \r\n";
                $content .= "\r\n Mail Content : " . $arm_restricted_cases_filtered . " \r\n";
            } else {
                $content .= "\r\n Log Type : " . $debug_log_type . "\r\n";
                $content .= "\r\n Content : " . $arm_restricted_cases_filtered . "\r\n";
                
            }
            $content .= "\r\n Last Executed Query:" . $executed_query . "\r\n";
            $arm_debug_file_data = $wp_filesystem->get_contents($arm_debug_file_path);
            $arm_debug_file_data .= $content;
            $arm_debug_file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $arm_debug_file_data = str_ireplace($breaks, "\r\n", $arm_debug_file_data);
            
            @$write_file = $wp_filesystem->put_contents($arm_debug_file_path, $arm_debug_file_data, 0755);
            if (!$write_file) {
                /* _e('Error Saving Log.', 'ARMember'); */
            }
        }
    }

    function arm_admin_messages_init($page = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_slugs;
        $success_msgs = '';
        $error_msgs = '';
        $ARMember->arm_session_start();
        if (isset($_SESSION['arm_message']) && !empty($_SESSION['arm_message'])) {
            foreach ($_SESSION['arm_message'] as $snotice) {
                if ($snotice['type'] == 'success') {
                    $success_msgs .= $snotice['message'];
                } else {
                    $error_msgs .= $snotice['message'];
                }
            }
            if (!empty($success_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; ?>', 'success');
                    });</script>
                <?php
            } elseif (!empty($error_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; ?>', 'error');
                    });</script>
                <?php
            }
            unset($_SESSION['arm_message']);
        }
        ?>
        <div class="armclear"></div>
        <div class="arm_message arm_success_message" id="arm_success_message">
            <div class="arm_message_text"><?php echo $success_msgs; ?></div>
        </div>
        <div class="arm_message arm_error_message" id="arm_error_message">
            <div class="arm_message_text"><?php echo $error_msgs; ?></div>
        </div>
        <div class="armclear"></div>
        <div class="arm_toast_container" id="arm_toast_container"></div>
        <div class="arm_loading" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
        <?php
    }

    function arm_do_not_show_video() {
        global $wp, $wpdb, $ARMember, $pagenow;
        $isShow = (isset($_POST['isShow']) && $_POST['isShow'] == '0') ? 0 : 1;
        $now = strtotime(current_time('mysql'));
        $time = strtotime('+10 day', $now);
        update_option('arm_show_document_video', $isShow);
        update_option('arm_show_document_video_on', $time);
        exit;
    }

    function arm_add_document_video() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
        $popupData = '';
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            $now = strtotime(current_time('mysql'));
            $show_document_video = get_option('arm_show_document_video', 0);
            $show_document_video_on = get_option('arm_show_document_video_on', strtotime(current_time('mysql')));
            if ($show_document_video == '0') {
                return;
            }
            if ($show_document_video_on > $now) {
                return;
            }
            /* Document Video Popup */
            $popupData = '<div id="arm_document_video_popup" class="popup_wrapper arm_document_video_popup"><div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<span class="popup_close_btn arm_popup_close_btn" onclick="armHideDocumentVideo();"></span>';
            $popupData .= '<span class="popup_header_text">' . __('Help Tutorial', 'ARMember') . '</span>';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $popupData .= '<iframe src="' . MEMBERSHIP_VIDEO_URL . '" allowfullscreen="" frameborder="0"> </iframe> ';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            $popupData .= '<label><input type="checkbox" id="arm_do_not_show_video" class="arm_do_not_show_video arm_icheckbox"><span>' . __('Do not show again.', 'ARMember') . '</span></label>';
            $popupData .= '<div class="popup_content_btn_wrapper">';
            $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="armHideDocumentVideo();" type="button">' . __('Close', 'ARMember') . '</button>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				var v_width = jQuery( window ).width();
				if(v_width <= "1350")
		        {
		          var poup_width = "720";
		          var poup_height = "400";
		          jQuery("#arm_document_video_popup").css("width","760");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		          
		        }
		        if(v_width > "1350" && v_width <= "1600")
		        {
		          var poup_width = "750";
		          var poup_height = "430";

		          jQuery("#arm_document_video_popup").css("width","790");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
		        if(v_width > "1600")
		        {
		          var poup_width = "800";
		          var poup_height = "450";
		          jQuery("#arm_document_video_popup").css("width","840");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
				jQuery("#arm_document_video_popup").bPopup({
					modalClose: false,
					closeClass: "popup_close_btn",
					onClose: function(){
               			 jQuery(this).find(".popup_wrapper_inner .popup_content_text").html("");
         			},
				});
			});</script>';
            echo $popupData;
        }
    }

    function arm_add_new_version_release_note() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
        $popupData = '';
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {

            $show_document_video = get_option('arm_new_version_installed', 0);

            if ($show_document_video == '0') {
                return;
            }


            /*changes for dynamic addon listing */
            //$plugins = get_plugins();
            /*$installed_plugins = array();
            foreach ($plugins as $key => $plugin) {
                $is_active = is_plugin_active($key);
                $installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
                $installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
                $installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

                $installed_plugins[] = $installed_plugin;
            }*/

            /*global $arm_version, $arm_social_feature;
            $bloginformation = array();
            $str = $arm_social_feature->get_rand_alphanumeric(10);

            if (is_multisite())
                $multisiteenv = "Multi Site";
            else
                $multisiteenv = "Single Site";

            $addon_listing = 1;

            $bloginformation[] = get_bloginfo('name');
            $bloginformation[] = get_bloginfo('description');
            $bloginformation[] = ARM_HOME_URL;
            $bloginformation[] = get_bloginfo('admin_email');
            $bloginformation[] = get_bloginfo('version');
            $bloginformation[] = get_bloginfo('language');
            $bloginformation[] = $arm_version;
            $bloginformation[] = $_SERVER['REMOTE_ADDR'];
            $bloginformation[] = $str;
            $bloginformation[] = $multisiteenv;
            $bloginformation[] = $addon_listing;

            $valstring = implode("||", $bloginformation);
            $encodedval = base64_encode($valstring);*/

            $urltopost = 'https://www.armemberplugin.com/armember_addons/addon_whatsnew_list.php';

            $raw_response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                //'body' => array('plugins' => urlencode(serialize($installed_plugins)), 'wpversion' => $encodedval),
                'cookies' => array()
                    )
            );
            $addon_list_html = "";
            if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                $addon_list_html .= "<div class='error_message' style='margin-top:100px; padding:20px;'>" . __("Add-On listing is currently unavailable. Please try again later.", 'ARMember') . "</div>";
            } else {
                $addon_list = json_decode($raw_response['body']);
                $addon_count = count($addon_list);
                $arm_whtsnew_wrapper_width = $addon_count * 141;
                foreach ( $addon_list as $list) {

                    $addon_list_html .= '<div class="arm_add_on"><a href="'.$list->addon_url.'" target="_blank"><img src="' . $list->addon_icon_url . '" /></a><div class="arm_add_on_text"><a href="'.$list->addon_url.'" target="_blank">'.$list->addon_name.'</a></div></div>';
                }
            }
            /*complete changes*/

            $popupData = '<div id="arm_update_note" class="popup_wrapper arm_update_note">'
                    . '<div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/logo_addon.png" />';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $i = 1;
            $major_changes = false;
            $change_log = $this->arm_new_version_changelog();

            if (isset($change_log) && !empty($change_log)) {



                $arm_show_critical_change_title = isset($change_log['show_critical_title']) ? $change_log['show_critical_title'] : 0;
                $arm_critical_title = isset($change_log['critical_title']) ? $change_log['critical_title'] : '';
                $arm_critical_changes = (isset($change_log['critical']) && !empty($change_log['critical'])) ? $change_log['critical'] : array();

                $arm_show_major_change_title = isset($change_log['show_major_title']) ? $change_log['show_major_title'] : 0;
                $arm_major_title = isset($change_log['major_title']) ? $change_log['major_title'] : '';
                $arm_major_changes = (isset($change_log['major']) && !empty($change_log['major'])) ? $change_log['major'] : array();

                $arm_show_other_change_title = isset($change_log['show_other_title']) ? $change_log['show_other_title'] : 0;
                $arm_other_title = isset($change_log['other_title']) ? $change_log['other_title'] : '';
                $arm_other_changes = (isset($change_log['other']) && !empty($change_log['other'])) ? $change_log['other'] : array();


                if (!empty($arm_critical_changes)) {
                    if ($arm_show_critical_change_title == 1) {
                        $popupData .= '<div class="arm_critical_change_title">' . __($arm_critical_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_critical_change_list"><ul>';
                    foreach ($arm_critical_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_major_changes)) {
                    if ($arm_show_major_change_title == 1) {
                        $popupData .= '<div class="arm_major_change_title">' . __($arm_major_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_major_change_list"><ul>';
                    foreach ($arm_major_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_other_changes)) {
                    if ($arm_show_other_change_title == 1) {
                        $popupData .= '<div class="arm_other_change_title">' . __($arm_other_title, 'ARMember') . '</div>';
                    }
                    $popupData .= '<div class="arm_other_change_list"><ul>';
                    foreach ($arm_other_changes as $value) {
                        $popupData .='<li>' . __($value, 'ARMember') . '</li>';
                    }
                    $popupData .= '</ul></div>';
                }
            }

            $popupData .= '</div>';
            $popupData .= '<div class="arm_addons_list_title">' . __('Available Add-ons', 'ARMember') . '</div>';

            
            $popupData .= '<div class="arm_addons_list_div">';
            $popupData .= '<div class="arm_addons_list" style="width:'.$arm_whtsnew_wrapper_width.'px;">';
            $popupData .= $addon_list_html;
            $popupData .= '</div>';
            $popupData .= '</div>';



            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            if (!empty($arm_critical_changes)) {
                $popupData .= '<label><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox"><span>' . __('I agree', 'ARMember') . '</span></label>';
                $popupData .= '<div class="popup_content_btn_wrapper">';
                $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="arm_hide_update_notice();" type="button">' . __('Close', 'ARMember') . '</button>';
                $popupData .= '</div>';
                $popupData .= '<div class="armclear"></div>';
            } else {
                $popupData .= '<div style="display: none;"><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox" value="1" checked="checked"></div>';
            }
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				
				jQuery("#arm_update_note").bPopup({
					modalClose: false,  
escClose : false                                        
				});

			});
                                                        function arm_hide_update_notice()
{
    var ishide = 0;
    if (jQuery("#arm_hide_update_notice").is(":checked")) {
	var ishide = 1;                   
	    jQuery("#arm_update_note").bPopup().close(); 
    }else{
        return;
    }
    jQuery.ajax({
	type: "POST",
	url: __ARMAJAXURL,
	data: "action=arm_dont_show_upgrade_notice&is_hide=" + ishide,
	success: function (res) {

            return false;
            
	}
    });
    return false;
}
</script>';
            echo $popupData;
        }
    }

    /*
     * for red color note `|^|Use coupon for invitation link`
     * Add important note to `major`
     * Add normal changelog to `other`  
     */

    function arm_new_version_changelog() {
        $arm_change_log = array();
        global $arm_payment_gateways, $arm_global_settings, $arm_slugs;
        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

        $arm_change_log = array(
            'show_critical_title' => 1,
            'critical_title' =>'Version 4.6.1 Changes',
            'critical' =>array(
                        'Minor bug fixes.',
                        ),
            'show_major_title' => 0,
            'major_title' =>'Major Changes',
            'major' => array( ),
            'show_other_title' =>0,
            'other_title' => 'Other Changes',
            'other' => array(
               
            )
        );
        return $arm_change_log;
    }

    function arm_dont_show_upgrade_notice() {
        global $wp, $wpdb, $ARMember, $pagenow;
        $is_hide = (isset($_POST['is_hide']) && $_POST['is_hide'] == '1') ? 1 : 0;
        if ($is_hide == 1) {
            
            delete_option('arm_new_version_installed');
        }
        die();
    }

    /* Cornerstone Methods */

    function arm_front_alert_messages() {
        $alertMessages = array(
            'loadActivityError' => __("There is an error while loading activities, please try again.", 'ARMember'),
            'pinterestPermissionError' => __("The user chose not to grant permissions or closed the pop-up", 'ARMember'),
            'pinterestError' => __("Oops, there was a problem getting your information", 'ARMember'),
            'clickToCopyError' => __("There is a error while copying, please try again", 'ARMember'),
            'fbUserLoginError' => __("User cancelled login or did not fully authorize.", 'ARMember'),
            'closeAccountError' => __("There is a error while closing account, please try again.", 'ARMember'),
            'invalidFileTypeError' => __("Sorry, this file type is not permitted for security reasons.", 'ARMember'),
            'fileSizeError' => __("File is not allowed bigger than {SIZE}.", 'ARMember'),
            'fileUploadError' => __("There is an error in uploading file, Please try again.", 'ARMember'),
            'coverRemoveConfirm' => __("Are you sure you want to remove cover photo?", 'ARMember'),
            'profileRemoveConfirm' => __("Are you sure you want to remove profile photo?", 'ARMember'),
            'errorPerformingAction' => __("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => __("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => __("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => __("Loading..", 'ARMember')
        );
        return $alertMessages;
    }

    function arm_alert_messages() {
        $alertMessages = array(
            'wentwrong' => __("Sorry, Something went wrong. Please try again.", 'ARMember'),
            'bulkActionError' => __("Please select valid action.", 'ARMember'),
            'bulkRecordsError' => __("Please select one or more records.", 'ARMember'),
            'clearLoginAttempts' => __("Login attempts cleared successfully.", 'ARMember'),
            'clearLoginHistory' => __("Login History cleared successfully.", 'ARMember'),
            'nopasswordforimport' => __("Password can not be left blank.", 'ARMember'),
            'delBadgeSuccess' => __("Badge has been deleted successfully.", 'ARMember'),
            'delBadgeError' => __("There is a error while deleting Badge, please try again.", 'ARMember'),
            'delAchievementBadgeSuccess' => __("Achievement badges has been deleted successfully.", 'ARMember'),
            'delAchievementBadgeError' => __("There is a error while deleting achievement badges, please try again.", 'ARMember'),
            'addUserAchievementSuccess' => __("User Achievement Added Successfully.", 'ARMember'),
            'delUserBadgeSuccess' => __("User badge has been deleted successfully.", 'ARMember'),
            'delUserBadgeError' => __("There is a error while deleting user badge, please try again.", 'ARMember'),
            'delPlansSuccess' => __("Plan(s) has been deleted successfully.", 'ARMember'),
            'delPlansError' => __("There is a error while deleting Plan(s), please try again.", 'ARMember'),
            'delPlanSuccess' => __("Plan has been deleted successfully.", 'ARMember'),
            'delPlanError' => __("There is a error while deleting Plan, please try again.", 'ARMember'),
            'stripePlanIDWarning' => __("If you leave this field blank, stripe will not be available in setup for recurring plan(s).", 'ARMember'),
            'delSetupsSuccess' => __("Setup(s) has been deleted successfully.", 'ARMember'),
            'delSetupsError' => __("There is a error while deleting Setup(s), please try again.", 'ARMember'),
            'delSetupSuccess' => __("Setup has been deleted successfully.", 'ARMember'),
            'delSetupError' => __("There is a error while deleting Setup, please try again.", 'ARMember'),
            'delFormSetSuccess' => __("Form Set Deleted Successfully.", 'ARMember'),
            'delFormSetError' => __("There is a error while deleting form set, please try again.", 'ARMember'),
            'delFormSuccess' => __("Form deleted successfully.", 'ARMember'),
            'delFormError' => __("There is a error while deleting form, please try again.", 'ARMember'),
            'delRuleSuccess' => __("Rule has been deleted successfully.", 'ARMember'),
            'delRuleError' => __("There is a error while deleting Rule, please try again.", 'ARMember'),
            'delRulesSuccess' => __("Rule(s) has been deleted successfully.", 'ARMember'),
            'delRulesError' => __("There is a error while deleting Rule(s), please try again.", 'ARMember'),
            'prevTransactionError' => __("There is a error while generating preview of transaction detail, Please try again.", 'ARMember'),
            'invoiceTransactionError' => __("There is a error while generating invoice of transaction detail, Please try again.", 'ARMember'),
            'prevMemberDetailError' => __("There is a error while generating preview of members detail, Please try again.", 'ARMember'),
            'prevMemberActivityError' => __("There is a error while displaying members activities detail, Please try again.", 'ARMember'),
            'prevCustomCssError' => __("There is a error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember'),
            'prevImportMemberDetailError' => __("Please upload appropriate file to import users.", 'ARMember'),
            'delTransactionSuccess' => __("Transaction has been deleted successfully.", 'ARMember'),
            'delTransactionsSuccess' => __("Transaction(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessageSuccess' => __("Message has been deleted successfully.", 'ARMember'),
            'delAutoMessageError' => __("There is a error while deleting Message, please try again.", 'ARMember'),
            'delAutoMessagesSuccess' => __("Message(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessagesError' => __("There is a error while deleting Message(s), please try again.", 'ARMember'),
            'delCouponSuccess' => __("Coupon has been deleted successfully.", 'ARMember'),
            'delCouponError' => __("There is a error while deleting Coupon, please try again.", 'ARMember'),
            'delCouponsSuccess' => __("Coupon(s) has been deleted successfully.", 'ARMember'),
            'delCouponsError' => __("There is a error while deleting Coupon(s), please try again.", 'ARMember'),
            'saveSettingsSuccess' => __("Settings has been saved successfully.", 'ARMember'),
            'saveSettingsError' => __("There is a error while updating settings, please try again.", 'ARMember'),
            'saveDefaultRuleSuccess' => __("Default Rules Saved Successfully.", 'ARMember'),
            'saveDefaultRuleError' => __("There is a error while updating rules, please try again.", 'ARMember'),
            'saveOptInsSuccess' => __("Opt-ins Settings Saved Successfully.", 'ARMember'),
            'saveOptInsError' => __("There is a error while updating opt-ins settings, please try again.", 'ARMember'),
            'delOptInsConfirm' => __("Are you sure to delete configuration?", 'ARMember'),
            'delMemberActivityError' => __("There is a error while deleting member activities, please try again.", 'ARMember'),
            'noTemplateError' => __("Template not found.", 'ARMember'),
            'saveTemplateSuccess' => __("Template options has been saved successfully.", 'ARMember'),
            'saveTemplateError' => __("There is a error while updating template options, please try again.", 'ARMember'),
            'prevTemplateError' => __("There is a error while generating preview of template, Please try again.", 'ARMember'),
            'addTemplateSuccess' => __("Template has been added successfully.", 'ARMember'),
            'addTemplateError' => __("There is a error while adding template, please try again.", 'ARMember'),
            'delTemplateSuccess' => __("Template has been deleted successfully.", 'ARMember'),
            'delTemplateError' => __("There is a error while deleting template, please try again.", 'ARMember'),
            'saveEmailTemplateSuccess' => __("Email Template Updated Successfully.", 'ARMember'),
            'saveAutoMessageSuccess' => __("Message Updated Successfully.", 'ARMember'),
            'saveBadgeSuccess' => __("Badges Updated Successfully.", 'ARMember'),
            'addAchievementSuccess' => __("Achievements Added Successfully.", 'ARMember'),
            'saveAchievementSuccess' => __("Achievements Updated Successfully.", 'ARMember'),
            'addDripRuleSuccess' => __("Rule Added Successfully.", 'ARMember'),
            'saveDripRuleSuccess' => __("Rule updated Successfully.", 'ARMember'),
            'pastDateError' => __("Cannot Set Past Dates.", 'ARMember'),
            'pastStartDateError' => __("Start date can not be earlier than current date.", 'ARMember'),
            'pastExpireDateError' => __("Expire date can not be earlier than current date.", 'ARMember'),
            'couponExpireDateError' => __("Expire date can not be earlier than start date.", 'ARMember'),
            'uniqueformsetname' => __("This Set Name is already exist.", 'ARMember'),
            'uniquesignupformname' => __("This Form Name is already exist.", 'ARMember'),
            'installAddonError' => __('There is an error while installing addon, Please try again.', 'ARMember'),
            'installAddonSuccess' => __('Addon installed successfully.', 'ARMember'),
            'activeAddonError' => __('There is an error while activating addon, Please try agina.', 'ARMember'),
            'activeAddonSuccess' => __('Addon activated successfully.', 'ARMember'),
            'deactiveAddonSuccess' => __('Addon deactivated successfully.', 'ARMember'),
            'pwdstrength_vweak' => __('Strength: Very Weak', 'ARMember'),
            'pwdstrength_weak' => __('Strength: Weak', 'ARMember'),
            'pwdstrength_good' => __('Strength: Good', 'ARMember'),
            'pwdstrength_vgood' => __('Strength: Strong', 'ARMember'),
            'confirmCancelSubscription' => __('Are you sure you want to cancel subscription?', 'ARMember'),
            'errorPerformingAction' => __("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => __("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => __("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => __("Loading..", 'ARMember'),
            'arm_nothing_found' => __('Oops, nothing found.', 'ARMember')
        );
        $frontMessages = $this->arm_front_alert_messages();
        $alertMessages = array_merge($alertMessages, $frontMessages);
        return $alertMessages;
    }

    function arm_prevent_rocket_loader_script($tag, $handle) {        
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/armember)|(wp\-includes\/js)|(apis\.google\.com)/';
        preg_match($pattern2,$script,$match_script);

        /* Check if current script is loaded from ARMember only */
        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern,$tag,$matches);
        if( !is_array($matches) ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=' ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && empty($matches[2]) ) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arm_prevent_rocket_loader_script_clf( $tag, $handle ){
    	$script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/armember)|(wp\-includes\/js)|(apis\.google\.com)/';
        preg_match($pattern2,$script,$match_script);

        /* Check if current script is loaded from ARMember only */
        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern, $tag, $matches);

        $pattern3 = '/type\=(\'|")[a-zA-Z0-9]+\-(text\/javascript)(\'|")/';
        preg_match_all($pattern3, $tag, $match_tag);

        if( !isset( $match_tag[0] ) || '' == $match_tag[0] ){
            return $tag;
        }

        if (!is_array($matches)) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=') {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && empty($matches[2])) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arm_set_js_css_conditionally() {
        global $arm_datepicker_loaded, $arm_avatar_loaded, $arm_file_upload_field, $bpopup_loaded, $arm_load_tipso, $arm_load_icheck, $arm_font_awesome_loaded, $arm_grecaptcha_is_enqueue, $arm_global_settings;
        if (!is_admin()) {
            if ($arm_datepicker_loaded == 1) {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
            }
            if ($arm_avatar_loaded == 1 || $arm_file_upload_field == 1) {
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
            }
            if ($bpopup_loaded == 1) {
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
            }
            if ($arm_load_tipso == 1) {
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }
            if ($arm_font_awesome_loaded == 1) {
                wp_enqueue_style('arm_fontawesome_css');
            }
            if($arm_grecaptcha_is_enqueue)
            {
                $all_global_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
                $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                $arm_recaptcha_lang = !empty($all_global_settings['arm_recaptcha_lang']) ? $all_global_settings['arm_recaptcha_lang'] : 'en';

                if(!empty($arm_recaptcha_site_key) && !empty($arm_recaptcha_private_key)) 
                {
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
            }
        }
    }

    function arm_check_font_awesome_icons($content) {
        global $arm_font_awesome_loaded;

        $fa_class = "/armfa|arm_user_social_icons|arm_user_social_fields/";
        $matches = array();
        preg_match_all($fa_class, $content, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            $arm_font_awesome_loaded = 1;
        }

        return $content;
    }

    function arm_perform_update_function(){
        @set_time_limit(0);
        global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_members_class, $arm_payment_gateways, $arm_member_forms, $arm_members_directory,$arm_newdbversion,$arm_subscription_plans;
        $date = date('Y-m-d H:i:s');
        update_option('armember_update_started', $date);

        /* 1) Whole badge image url was stored in database, now only image name will be stored in database */
        $sql = "SELECT * FROM `" . $ARMember->tbl_arm_badges_achievements . "` WHERE `arm_badges_type` = 'badge' ORDER BY `arm_badges_id` DESC";
        $results = $wpdb->get_results($sql);

        $arm_db_logs = "";

        $first_step_data = array();
        if( $wpdb->last_error != "" ){
        	$first_step_data['info_1'] = 'selecting all images of badges from database';
        	$first_step_data['query_1'] = $wpdb->last_query;
        	$first_step_data['error_1'] = $wpdb->last_error;
        }

        if (!empty($results)) {
            $badge_data = array();
            foreach ($results as $badge) {
                $badge_arr = explode('/', $badge->arm_badges_icon);
                $badgeicon = end($badge_arr);
                $badges_data = array('arm_badges_icon' => $badgeicon);
                $where = array('arm_badges_id' => $badge->arm_badges_id);
                $wpdb->update($ARMember->tbl_arm_badges_achievements, $badges_data, $where);

                if( $wpdb->last_error != "" ){
                	$first_step_data['info_2'] = 'Whole badge image url was stored in database, now only image name will be stored in database';
                	$first_step_data['query_2'] = $wpdb->last_query;
                	$first_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        $arm_db_logs .= " Step 1 => ". stripslashes_deep(json_encode($first_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);
        /* 2) In bank transfer payment log table two columns are added :
         * a) arm_extra_vars ( to show trial amount )
         * b) arm_invoice_id
         *  */
        $second_step_data = array();
        $arm_tbl_bank_transfer_log = $ARMember->tbl_arm_bank_transfer_log;
        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}` ADD `arm_extra_vars` LONGTEXT NULL AFTER `arm_currency`");
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_1'] = "adding new column `arm_extra_vars` in bank_transfer table";
        	$second_step_data['query_1'] = $wpdb->last_query;
        	$second_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}`  ADD `arm_invoice_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_log_id`");
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_2'] = "adding new column `arm_invoice_id` in bank_transfer table";
        	$second_step_data['query_2'] = $wpdb->last_query;
        	$second_step_data['error_2'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}`  ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`");
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_3'] = "adding new column `arm_payment_cycle` in bank_transfer table";
        	$second_step_data['query_3'] = $wpdb->last_query;
        	$second_step_data['error_3'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 2 => ". stripslashes_deep(json_encode($second_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 3) In payment log table one columns is added :
         * a) arm_invoice_id
         */
        $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}`  ADD `arm_invoice_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_log_id`");

        $third_step_data = array();

        if( $wpdb->last_error != "" ){
	        $third_step_data['info_1'] = 'adding new column `arm_invoid_id` in payment_log table';
        	$third_step_data['query_1'] = $wpdb->last_query;
            $third_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 3 => ". stripslashes_deep(json_encode($third_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 4) New column 'arm_form_field_bp_field_id' is added in  'arm_form_field table' and all old mapped fields are stored in this column */

        $arm_tbl_arm_form_field = $ARMember->tbl_arm_form_field;
        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_form_field}`  ADD `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_form_field_option`");
        $fourth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $fourth_step_data['info_1'] = 'adding new column `arm_form_field_bp_field_id` in arm_form_field table';
        	$fourth_step_data['query_1'] = $wpdb->last_query;
        	$fourth_step_data['error_1'] = $wpdb->last_error;
        }

        $form_field_data = $wpdb->get_results("SELECT `arm_form_field_option`,`arm_form_field_id` FROM " . $ARMember->tbl_arm_form_field);

        if (!empty($form_field_data)) {
            foreach ($form_field_data as $form_field_option) {
                $arm_form_field_option = maybe_unserialize($form_field_option->arm_form_field_option);
                $arm_buddypress_map_field = (isset($arm_form_field_option['mapfield']) && !empty($arm_form_field_option['mapfield'])) ? $arm_form_field_option['mapfield'] : 0;
                $wpdb->update(
                        $ARMember->tbl_arm_form_field, array(
                            'arm_form_field_bp_field_id' => $arm_buddypress_map_field, // string
                        ), array('arm_form_field_id' => $form_field_option->arm_form_field_id), array('%d'), array('%d')
                );

                if( $wpdb->last_error != "" ){
                	$fourth_step_data['info_2'] = 'store old mapped fields in columns';
                	$fourth_step_data['query_2'] = $wpdb->last_query;
                	$fourth_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        
        $arm_db_logs .= " Step 4 => ". stripslashes_deep(json_encode($fourth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 5) Buddypress profile cover , avatar and mapped profile page option is added in database with default value */
        $buddypress_settings_array = array('avatar_map' => 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
        $serialized_buddypress_options = maybe_serialize($buddypress_settings_array);
        update_option('arm_buddypress_options', $serialized_buddypress_options);
        $fifth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $fifth_step_data['info_1'] = 'store BuddyPress profile cover, avatar and mapped profile page option in database with default value';
        	$fifth_step_data['query_1'] = $wpdb->last_query;
        	$fifth_step_data['error_1'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 5 => ". stripslashes_deep(json_encode($fifth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 6) Default invoice template is added in database option */
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
        $arm_default_invoice_template .= '#arm_invoice_div h2 { text-align: right; padding: 0 10px 0 0;margin:0 auto; }';
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

        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
        $all_general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
        if (!empty($all_general_settings)) {
            $all_global_settings['general_settings']['arm_invoice_template'] = $arm_default_invoice_template;
            $arm_all_glbal_settings_updated = maybe_serialize($all_global_settings);
            update_option('arm_global_settings', $all_global_settings);
            $sixth_step_data = array();
            if( $wpdb->last_error != "" ){
	            $sixth_step_data['info_1'] = 'Installing new Invoice template in database';
            	$sixth_step_data['query_1'] = $wpdb->last_query;
            	$sixth_step_data['error_1'] = $wpdb->last_error;
            }
        }

        /* Creating Usermeta backup for meta 'arm_primary_status', 'arm_completed_recurring_PLAN_ID' and 'arm_next_due_payment_PLAN_ID' */

        $temp_backup_table = $wpdb->prefix.'arm_temp_usermeta';
        $original_meta_table = $wpdb->prefix.'usermeta';

        $wpdb->query("CREATE TABLE ".$temp_backup_table." LIKE ".$original_meta_table);
        if( $wpdb->last_error != "" ){
        	$sixth_step_data['info_2'] = 'creating backup for user meta';
        	$sixth_step_data['query_2'] = $wpdb->last_query;
        	$sixth_step_data['error_2'] = $wpdb->last_error;
        }

        $wpdb->query($wpdb->prepare("INSERT INTO ".$temp_backup_table." (SELECT * FROM ".$original_meta_table." um WHERE um.meta_key = %s OR um.meta_key LIKE %s OR um.meta_key LIKE %s )",'arm_primary_status','%arm_completed_recurring_%','%arm_next_due_payment_%'));

        if( $wpdb->last_error != "" ){
        	$sixth_step_data['info_3'] = 'Copy users meta data into new table';
        	$sixth_step_data['query_3'] = $wpdb->last_query;
        	$sixth_step_data['error_3'] = $wpdb->last_error;
        }

        /* 7) Update inactive users to active who have secondary status in (suspended, expired, user cancelled, failed payment ) and add user's plan in suspended
         *  update completed_recurrence and next due meta in case payment failed
         *  add failed payment history in case of manual subscription
         */

        $args = array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'arm_primary_status',
                    'value' => '2',
                    'compare' => '='
                ),
                array(
                    'key' => 'arm_secondary_status',
                    'value' => array('2', '3', '4', '5'),
                    'compare' => 'IN'
                )
            )
        );
        $amTotalUsers = get_users($args);
        $seventh_step_data = array();
        if( $wpdb->last_error != "" ){
	        $seventh_step_data['info_1'] = 'Fetching all inactive users from database';
        	$seventh_step_data['query_1'] = $wpdb->last_query;
        	$seventh_step_data['error_1'] = $wpdb->last_error;
        }
        if (!empty($amTotalUsers)) {
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_secondary_status = get_user_meta($user_id, 'arm_secondary_status', true);
                arm_set_member_status($user_id, 1, $arm_secondary_status); // activate user

                if( $wpdb->last_error != "" ){
                	$seventh_step_data['info_2'] = 'Updating inactive users to active who have secondary status in (suspended, expired, user cancelled, failed payment )';
                	$seventh_step_data['query_2'] = $wpdb->last_query;
                	$seventh_step_data['error_2'] = $wpdb->last_error;
                }

                if (in_array($arm_secondary_status, array('2', '5'))) { //if user payment is failed or suspended status 
                    $arm_user_plan_id = get_user_meta($user_id, 'arm_user_plan', true);
                    if (!empty($arm_user_plan_id)) {

                        //Update suspended meta id 
                        $suspended_plan_ids = array($arm_user_plan_id);
                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids);

                        if( $wpdb->last_error != "" ){
                        	$seventh_step_data['info_3'] = 'Add suspended plan into users who have failed or suspended status';
                        	$seventh_step_data['query_3'] = $wpdb->last_query;
                        	$seventh_step_data['error_3'] = $wpdb->last_error;
                        }
                                
                        $plan_expire_date = get_user_meta($user_id, 'arm_expire_plan_' . $arm_user_plan_id, true);
                        $now = current_time('mysql');


                        if (empty($plan_expire_date) || $plan_expire_date > strtotime($now)) { //if plan is not expired
                            $arm_next_due_date = get_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan_id, true);
                            $arm_completed_recurring = get_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan_id, true);
                            $arm_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                            $arm_current_plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
                            $plan_data = maybe_unserialize($arm_current_plan_detail);
                            $plan_amount = $plan_data['arm_subscription_plan_amount'];
                            $user_detail = get_userdata($user_id);
                            $payer_email = $user_detail->user_email;
                            $extraParam = array();
                            $extraParam['manual_by'] = 'Paid By system';
                            if ($arm_payment_mode == 'manual_subscription') {
                                while ($arm_next_due_date < strtotime($now)) {

                                    $payment_data = array(
                                        'arm_user_id' => $user_id,
                                        'arm_first_name'=>$user_detail->first_name,
                                        'arm_last_name'=>$user_detail->last_name,
                                        'arm_plan_id' => $arm_user_plan_id,
                                        'arm_payment_gateway' => 'manual',
                                        'arm_payment_type' => 'subscription',
                                        'arm_token' => '-',
                                        'arm_payer_email' => $payer_email,
                                        'arm_transaction_payment_type' => 'subscription',
                                        'arm_transaction_status' => 'failed',
                                        'arm_payment_mode' => 'manual_subscription',
                                        'arm_payment_date' => date('Y-m-d H:i:s', $arm_next_due_date),
                                        'arm_amount' => $plan_amount,
                                        'arm_extra_vars' => maybe_serialize($extraParam),
                                    );
                                    $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);


                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_6'] = 'save payment log';
                                    	$seventh_step_data['query_6'] = $wpdb->last_query;
                                    	$seventh_step_data['error_6'] = $wpdb->last_error;
                                    }


                                    $arm_completed_recurring++;
                                    update_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan_id, $arm_completed_recurring);

                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_4'] = 'Updating user meta for auto complete recurring';
                                    	$seventh_step_data['query_4'] = $wpdb->last_query;
                                    	$seventh_step_data['error_4'] = $wpdb->last_error;
                                    }

                                    $arm_next_due_date = $arm_members_class->arm_get_next_due_date_old($user_id, $arm_user_plan_id, false);
                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_5'] = 'next due date calculation'.$arm_next_due_date.'---------user_id: '.$user_id;
                                    	$seventh_step_data['query_5'] = $wpdb->last_query;
                                    	$seventh_step_data['error_5'] = $wpdb->last_error;
                                    }
                                    if (empty($arm_next_due_date)) {
                                        break;
                                    }
                                }

                                if (!empty($arm_next_due_date)) {
                                    update_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan_id, $arm_next_due_date);

                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_6'] = 'Updating user meta for next due payment';
                                    	$seventh_step_data['query_6'] = $wpdb->last_query;
                                    	$seventh_step_data['error_6'] = $wpdb->last_error;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $arm_db_logs .= " Step 7 => ". stripslashes_deep(json_encode($seventh_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 8) All old users invoice ID are updated and last invoice ID stored in wp-option table in `arm_last_invoice_id` meta */
        $arm_payment_log_data = $wpdb->get_results("SELECT `arm_log_id` FROM " . $arm_tbl_arm_payment_log);
        $i = 0;
        $eighth_step_data = array();
        if (!empty($arm_payment_log_data)) {
            foreach ($arm_payment_log_data as $log_data) {
                $i++;
                $arm_log_id = $log_data->arm_log_id;
                $wpdb->update(
                        $arm_tbl_arm_payment_log, array(
                    		'arm_invoice_id' => $i, // string
                        ), array('arm_log_id' => $arm_log_id), array('%d'), array('%d')
                );
                if( $wpdb->last_error != "" ){
                    $eighth_step_data['info_1_'.$i] = 'Update old users invoice ID and store last invoice id in wp-option table with `arm_last_invoice_id` meta';
                	$eighth_step_data['query_1_'.$i] = $wpdb->last_query;
                	$eighth_step_data['error_1_'.$i] = $wpdb->last_error;
                }
            }
        }

        $arm_bank_log_data = $wpdb->get_results("SELECT `arm_log_id` FROM " . $arm_tbl_bank_transfer_log);

        if (!empty($arm_bank_log_data)) {

            foreach ($arm_bank_log_data as $bank_log_data) {
                $i++;
                $arm_bank_log_id = $bank_log_data->arm_log_id;
                $wpdb->update(
                        $arm_tbl_bank_transfer_log, array(
                    'arm_invoice_id' => $i, // string
                        ), array('arm_log_id' => $arm_bank_log_id), array('%d'), array('%d')
                );

                if( $wpdb->last_error != "" ){
                	$eighth_step_data['info_2'] = 'Update old users invoice ID and store last invoice id in wp-option table with `arm_last_invoice_id` meta for bank transfer log';
                	$eighth_step_data['query_2'] = $wpdb->last_query;
                	$eighth_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        update_option('arm_last_invoice_id', $i);

                
        if( $wpdb->last_error != "" ){
        	$eighth_step_data['info_3'] = 'updating option `arm_last_invoice_id` ';
        	$eighth_step_data['query_3'] = $wpdb->last_query;
        	$eighth_step_data['error_3'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 8 => ". stripslashes_deep(json_encode($eighth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 9) All old plan related user metas are stored in new meta  */

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'arm_user_plan',
                    'value' => 0,
                    'compare' => '>'
                ),
            )
        );

        $amTotalUsers = get_users($args);
        $ninth_step_data = array();
        if (!empty($amTotalUsers)) {
            $cn = 1;
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_user_plan = get_user_meta($user_id, 'arm_user_plan', true);
                /*$arm_current_plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
                $arm_start_plan = get_user_meta($user_id, 'arm_start_plan_' . $arm_user_plan, true);
                $arm_expire_plan = get_user_meta($user_id, 'arm_expire_plan_' . $arm_user_plan, true);
                $arm_is_user_in_trial = get_user_meta($user_id, 'arm_is_plan_trial_' . $arm_user_plan, true);
                $arm_trial_start = get_user_meta($user_id, 'arm_trial_start_date', true);
                $arm_trial_end = get_user_meta($user_id, 'arm_expire_plan_trial', true);
                $arm_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                $arm_payment_cycle = get_user_meta($user_id, 'arm_selected_payment_cycle', true);
                $arm_is_user_in_grace = get_user_meta($user_id, 'arm_is_grace_period', true);
                $arm_grace_period_end = get_user_meta($user_id, 'arm_grace_period_end', true);
                $arm_grace_period_action = get_user_meta($user_id, 'arm_grace_period_action', true);
                $arm_subscr_effective = get_user_meta($user_id, 'arm_subscr_effective', true);
                $arm_change_plan_to = get_user_meta($user_id, 'arm_change_plan_to', true);
                $arm_user_gateway = get_user_meta($user_id, 'arm_using_gateway_' . $arm_user_plan, true);
                $arm_gateway = get_user_meta($user_id, 'arm_' . $arm_user_gateway . '_' . $arm_user_plan, true);
                $arm_subscr_id = get_user_meta($user_id, 'arm_subscr_id_' . $arm_user_plan, true);
                $arm_next_due_payment = get_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan, true);
                $arm_completed_recurring = get_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan, true);
                $arm_sent_msgs = get_user_meta($user_id, 'arm_sent_msgs_' . $arm_user_plan, true);
                $arm_cancelled_plan = get_user_meta($user_id, 'arm_cencelled_plan_' . $arm_user_plan, true);*/

                /*$arm_user_plan_array['arm_current_plan_detail'] = maybe_unserialize($arm_current_plan_detail);
                $arm_user_plan_array['arm_start_plan'] = $arm_start_plan;
                $arm_user_plan_array['arm_expire_plan'] = $arm_expire_plan;
                $arm_user_plan_array['arm_is_trial_plan'] = $arm_is_user_in_trial;
                $arm_user_plan_array['arm_trial_start'] = $arm_trial_start;
                $arm_user_plan_array['arm_trial_end'] = $arm_trial_end;
                $arm_user_plan_array['arm_payment_mode'] = $arm_payment_mode;
                $arm_user_plan_array['arm_payment_cycle'] = $arm_payment_cycle;
                $arm_user_plan_array['arm_is_user_in_grace'] = $arm_is_user_in_grace;
                $arm_user_plan_array['arm_grace_period_end'] = $arm_grace_period_end;
                $arm_user_plan_array['arm_grace_period_action'] = $arm_grace_period_action;
                $arm_user_plan_array['arm_subscr_effective'] = $arm_subscr_effective;
                $arm_user_plan_array['arm_change_plan_to'] = $arm_change_plan_to;
                $arm_user_plan_array['arm_user_gateway'] = $arm_user_gateway;
                $arm_user_plan_array['arm_' . $arm_user_gateway] = $arm_gateway;
                $arm_user_plan_array['arm_subscr_id'] = $arm_subscr_id;
                $arm_user_plan_array['arm_next_due_payment'] = $arm_next_due_payment;
                $arm_user_plan_array['arm_completed_recurring'] = $arm_completed_recurring;
                $arm_user_plan_array['arm_sent_msgs'] = maybe_unserialize($arm_sent_msgs);
                $arm_user_plan_array['arm_cencelled_plan'] = $arm_cancelled_plan;*/
                

                $arm_user_plan_array = array();
                $arm_user_plan_id_array = array();

                $arm_user_plan_id_array[] = $arm_user_plan;

                $default_plan_array = array(
                    'arm_current_plan_detail' => array(),
                    'arm_start_plan' => '',
                    'arm_expire_plan' => '',
                    'arm_is_trial_plan' => 0,
                    'arm_trial_start' => '',
                    'arm_trial_end' => '',
                    'arm_payment_mode' => '',
                    'arm_payment_cycle' => '',
                    'arm_is_user_in_grace' => 0,
                    'arm_grace_period_end' => '',
                    'arm_grace_period_action' => '',
                    'arm_subscr_effective' => '',
                    'arm_change_plan_to' => '',
                    'arm_user_gateway' => '',
                    'arm_subscr_id' => '',
                    'arm_next_due_payment' => '',
                    'arm_completed_recurring' => '',
                    'arm_sent_msgs' => '',
                    'arm_cencelled_plan' => '',
                    'arm_authorize_net' => '',
                    'arm_2checkout' => '',
                    'arm_stripe' => '',
                    'payment_detail' => array(),
                );

                $arm_user_metas = $wpdb->get_results("SELECT meta_key,meta_value FROM ".$wpdb->usermeta." WHERE meta_key IN ('arm_user_plan',  'arm_current_plan_detail',  'arm_start_plan_{$arm_user_plan}',  'arm_expire_plan_{$arm_user_plan}',  'arm_is_plan_trial_{$arm_user_plan}',  'arm_trial_start_date',  'arm_expire_plan_trial',  'arm_selected_payment_mode',  'arm_selected_payment_cycle',  'arm_is_grace_period',  'arm_grace_period_end', 'arm_grace_period_action',  'arm_subscr_effective',  'arm_change_plan_to',  'arm_using_gateway_{$arm_user_plan}',  'arm_subscr_id_{$arm_user_plan}',  'arm_next_due_payment_{$arm_user_plan}',  'arm_completed_recurring_{$arm_user_plan}',  'arm_sent_msgs_{$arm_user_plan}',  'arm_cencelled_plan_{$arm_user_plan}') AND user_id = {$user_id}");

                foreach($arm_user_metas as $key => $user_meta){
                    $meta_key = $user_meta->meta_key;
                    $meta_value = $user_meta->meta_value;
                    switch ($meta_key) {
                        case 'arm_user_plan':
                            break;
                        case 'arm_current_plan_detail':
                            $arm_user_plan_array['arm_current_plan_detail'] = maybe_unserialize($meta_value);
                            break;
                        case 'arm_start_plan_'.$arm_user_plan:
                            $arm_user_plan_array['arm_start_plan'] = $meta_value;
                            break;
                        case 'arm_expire_plan_'.$arm_user_plan:
                            $arm_user_plan_array['arm_expire_plan'] = $meta_value;
                            break;
                        case 'arm_is_plan_trial_'.$arm_user_plan:
                            $arm_user_plan_array['arm_is_trial_plan'] = $meta_value;
                            break;
                        case 'arm_trial_start_date':
                            $arm_user_plan_array['arm_trial_start'] = $meta_value;
                            break;
                        case 'arm_expire_plan_trial':
                            $arm_user_plan_array['arm_trial_end'] = $meta_value;
                            break;
                        case 'arm_selected_payment_mode':
                            $arm_user_plan_array['arm_payment_mode'] = $meta_value;
                            break;
                        case 'arm_selected_payment_cycle':
                            $arm_user_plan_array['arm_payment_cycle'] = $meta_value;
                            break;
                        case 'arm_is_grace_period':
                            $arm_user_plan_array['arm_is_user_in_grace'] = $meta_value;
                            break;
                        case 'arm_grace_period_end':
                            $arm_user_plan_array['arm_grace_period_end'] = $meta_value;
                            break;
                        case 'arm_grace_period_action':
                            $arm_user_plan_array['arm_grace_period_action'] = $meta_value;
                            break;
                        case 'arm_subscr_effective':
                            $arm_user_plan_array['arm_subscr_effective'] = $meta_value;
                            break;
                        case 'arm_change_plan_to':
                            $arm_user_plan_array['arm_change_plan_to'] = $meta_value;
                            break;
                        case 'arm_using_gateway_'.$arm_user_plan:
                            $arm_user_plan_array['arm_user_gateway'] = $meta_value;
                            $arm_user_gateway = $meta_value;
                            $arm_user_plan_array['arm_'.$arm_user_gateway] = get_user_meta($user_id,'arm_'.$arm_user_gateway.'_'.$arm_user_plan,true);
                            break;
                        case 'arm_subscr_id_'.$arm_user_plan:
                            $arm_user_plan_array['arm_subscr_id'] = $meta_value;
                            break;
                        case 'arm_next_due_payment_'.$arm_user_plan:
                            $arm_user_plan_array['arm_next_due_payment'] = $meta_value;
                            break;
                        case 'arm_completed_recurring_'.$arm_user_plan:
                            $arm_user_plan_array['arm_completed_recurring'] = $meta_value;
                            break;
                        case 'arm_sent_msgs_'.$arm_user_plan:
                            $arm_user_plan_array['arm_sent_msgs'] = maybe_unserialize($meta_value);
                            break;
                        case 'arm_cencelled_plan_'.arm_user_plan:
                            $arm_user_plan_array['arm_cencelled_plan'] = $meta_value;
                            break;
                        default:
                            break;
                    }
                }

                $arm_user_plan_array = shortcode_atts($default_plan_array,$arm_user_plan_array);

                update_user_meta($user_id, 'arm_user_plan_ids', $arm_user_plan_id_array);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_1'] = 'Updating arm_user_plan_ids';
                	$ninth_step_data['query_' . $cn . '_1'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_1'] = $wpdb->last_error;
                }

                update_user_meta($user_id, 'arm_user_last_plan', $arm_user_plan);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_2'] = 'Updating arm_user_last_plan';
                	$ninth_step_data['query_' . $cn . '_2'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_2'] = $wpdb->last_error;
                }

                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan, $arm_user_plan_array);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_3'] = 'Updating arm_user_plan_' . $arm_user_plan;
                	$ninth_step_data['query_' . $cn . '_3'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_3'] = $wpdb->last_error;
                }
                $cn++;
            }
            $arm_db_logs .= " Step 9 => ". json_encode($ninth_step_data). " === ";
            update_option('armember_update_logs',$arm_db_logs);
        }


        /* 10) add manual user activation email notification in default email notifications */

        $arm_manual_email_notification_content = $wpdb->get_row("SELECT `arm_message_content`, `arm_message_subject`, `arm_message_status` FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_type`='on_menual_activation' LIMIT 1", ARRAY_A);
        if (isset($arm_manual_email_notification_content) && !empty($arm_manual_email_notification_content)) {

            $email_content = $arm_manual_email_notification_content['arm_message_content'];

            $email_content = str_replace('{ARM_MESSAGE_BLOGNAME}', '{ARM_BLOGNAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_BLOGURL}', '{ARM_BLOG_URL}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERNAME}', '{ARM_USERNAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USER_ID}', '{ARM_USER_ID}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_RESET_PASSWORD_LINK}', '{ARM_RESET_PASSWORD_LINK}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERFIRSTNAME}', '{ARM_FIRST_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERLASTNAME}', '{ARM_LAST_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERNICENAME}', '{ARM_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERDISPLAYNAME}', '{ARM_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_EMAIL}', '{ARM_EMAIL}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_SUBSCRIPTIONNAME}', '{ARM_PLAN}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}', '{ARM_PLAN_EXPIRE}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_CURRENCY}', '{ARM_CURRENCY}', $email_content);



            $arm_manual_email_notification = array(
                'arm_template_name' => 'Manual User Activation',
                'arm_template_slug' => 'on-menual-activation',
                'arm_template_subject' => $arm_manual_email_notification_content['arm_message_subject'],
                'arm_template_content' => $email_content,
                'arm_template_status' => $arm_manual_email_notification_content['arm_message_status']
            );
        } else {
            $arm_manual_email_notification = array(
                'arm_template_name' => 'Manual User Activation',
                'arm_template_slug' => 'on-menual-activation',
                'arm_template_subject' => 'Your account has been activated at {ARM_BLOGNAME}',
                'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Account has been activated.</p><br><p> Please click on following link:</p><br><p>{ARM_BLOG_URL}</p><br><p>Have a nice day!</p>',
                'arm_template_status' => 1
            );
        }


        $arm_email_template_formate = array('%s', '%s', '%s', '%s', '%d');
        $wpdb->insert($ARMember->tbl_arm_email_templates, $arm_manual_email_notification, $arm_email_template_formate);

        $tenth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $tenth_step_data['info_1'] = 'add manual user activation email notification in default email notifications';
	        $tenth_step_data['query_1'] = $wpdb->last_query;
	        $tenth_step_data['error_1'] = $wpdb->last_error;
        }


        $wpdb->delete($ARMember->tbl_arm_auto_message,array('arm_message_type' => 'on_menual_activation'));
        if( $wpdb->last_error != "" ){
	        $tenth_step_data['info_2'] = 'deleting on manual activation mail from `arm_auto_message` table';
	        $tenth_step_data['query_2'] = $wpdb->last_query;
	        $tenth_step_data['error_2'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 10 => ". json_encode($tenth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 11) Login form redirections, sign up form redirections, setup redirection, default redirection are moved to General Settings->Redirection Rules tab

          Login - redirection settings of Login form which is used in mapped login page will be saved
          sign up - form wise redirection will be saved
          setup - thank you page

         */

        $arm_redirection_settings = array();
        /* ----------------sign up  ------------------------ */
        $page_settings = $all_global_settings['page_settings'];

        $page_settings['register_page_id'] = isset($page_settings['register_page_id']) ? $page_settings['register_page_id'] : 0;
        $is_valid_reg_page = $arm_global_settings->arm_shortcode_exist_in_page('registration', $page_settings['register_page_id']);
        $register_page_id = $page_settings['register_page_id'];

        if ($is_valid_reg_page) {
            $registration_form_id = $arm_global_settings->arm_registration_form_shortcode_exist_in_page('registration', $register_page_id);
        } else {
            $registration_form_id = $arm_member_forms->arm_get_default_form_id('registration');
        }

        if (!empty($registration_form_id)) {
            $armform = new ARM_Form('id', $registration_form_id);
            $form_type = $armform->type;
            $form_settings = $armform->settings;
            $arm_redirection_settings['signup']['redirect_type'] = 'formwise';


            if ($form_settings['redirect_type'] == 'page') {
                $arm_redirection_settings['signup']['type'] = 'page';
                $registration_redirect_id = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : '0';
                $arm_redirection_settings['signup']['page_id'] = $registration_redirect_id;
                $arm_redirection_settings['signup']['url'] = '';
                $arm_redirection_settings['signup']['refferel'] ='';
            } 
            else if ($form_settings['redirect_type'] == 'referral') {
                $arm_redirection_settings['signup']['type'] = 'referral';
                $registration_redirect_url = (!empty($form_settings['referral_url'])) ? $form_settings['referral_url'] : ARM_HOME_URL;
                $arm_redirection_settings['signup']['page_id'] = '';
                $arm_redirection_settings['signup']['url'] = '';
                $arm_redirection_settings['signup']['refferel'] = $registration_redirect_url;
            
            }  else {
                $arm_redirection_settings['signup']['type'] = 'url';
                $registration_redirect_url = (!empty($form_settings['redirect_url'])) ? $form_settings['redirect_url'] : ARM_HOME_URL;
                $arm_redirection_settings['signup']['page_id'] = '';
                $arm_redirection_settings['signup']['url'] = $registration_redirect_url;
                $arm_redirection_settings['signup']['refferel'] = '';
            }

            $registration_forms = $wpdb->get_results("SELECT `arm_form_id`, `arm_form_label`, `arm_form_slug`, `arm_is_default`, `arm_form_updated_date` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='registration' ORDER BY `arm_form_id` DESC", ARRAY_A);

            if (!empty($registration_forms)) {

                foreach ($registration_forms as $_form) {
                    $_fid = $_form['arm_form_id'];
                    if (!empty($_fid)) {
                        $regarmform = new ARM_Form('id', $_fid);
                        $regform_type = $regarmform->type;
                        $regform_settings = $regarmform->settings;
                        if ($regform_settings['redirect_type'] == 'page') {
                            $registration_redirect_id_new = (!empty($regform_settings['redirect_page'])) ? $regform_settings['redirect_page'] : '';
                            if (empty($registration_redirect_id_new) || $registration_redirect_id_new == 0) {
                                continue;
                            } else {
                                $arm_redirection_settings['signup']['conditional_redirect'][] = array('form_id' => $_fid,
                                    'url' => $registration_redirect_id_new);
                            }
                        } else {
                            $registration_redirect_url_new = (!empty($regform_settings['redirect_url'])) ? $regform_settings['redirect_url'] : '';
                                            if(isset($registration_redirect_url_new) && !empty($registration_redirect_url_new) && $registration_redirect_url_new != ''){
                                                    $registration_redirect_id_reg_new = url_to_postid($registration_redirect_url_new);
                                                $arm_redirection_settings['signup']['conditional_redirect'][] = array('form_id' => $_fid,
                                                    'url' => $registration_redirect_id_reg_new);
                                            }
                                            else{
                                                    continue;
                                            }
                        }
                    }
                }
            }
        }

        /* ---------------- log in  ------------------------ */

        $page_settings['login_page_id'] = isset($page_settings['login_page_id']) ? $page_settings['login_page_id'] : 0;
        $is_valid_login_page = $arm_global_settings->arm_shortcode_exist_in_page('login', $page_settings['login_page_id']);
        $login_page_id = $page_settings['login_page_id'];

        if ($is_valid_login_page) {
            $login_form_id = $arm_global_settings->arm_registration_form_shortcode_exist_in_page('login', $login_page_id);
        } else {
            $login_form_id = $arm_member_forms->arm_get_default_form_id('login');
        }

        if (!empty($login_form_id)) {
            $armform = new ARM_Form('id', $login_form_id);
            $form_type = $armform->type;
            $form_settings = $armform->settings;
            if ($form_settings['redirect_type'] == 'page') {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'page';
                $login_redirect_id = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : '0';
                $arm_redirection_settings['login']['page_id'] = $login_redirect_id;
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = '';
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            } else if ($form_settings['redirect_type'] == 'referral') {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'referral';
                $login_redirect_url = (!empty($form_settings['referral_url'])) ? $form_settings['referral_url'] : ARM_HOME_URL;
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = $login_redirect_url;
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            } else if ($form_settings['redirect_type'] == 'conditional_redirect') {
                $arm_redirection_settings['login']['main_type'] = 'conditional_redirect';
                $arm_redirection_settings['login']['type'] = 'conditional_redirect';
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = '';

                $login_default_redirect_url = (!empty($form_settings['conditional_redirect_url'])) ? $form_settings['conditional_redirect_url'] : ARM_HOME_URL;
                $login_redirection_conditions = (isset($form_settings['conditional_redirects']) && !empty($form_settings['conditional_redirects'])) ? $form_settings['conditional_redirects'] : array();

                if (!empty($login_redirection_conditions) && is_array($login_redirection_conditions)) {
                    foreach ($login_redirection_conditions as $login_redirection_condition) {
                        $conditional_plan_id = $login_redirection_condition['plan_id'];
                        $conditional_redirect = url_to_postid($login_redirection_condition['redirect']);
                        if (!empty($conditional_redirect)) {
                            $arm_redirection_settings['login']['conditional_redirect'][] = array('plan_id' => $conditional_plan_id,
                                'condition' => '',
                                'expire' => 0,
                                'url' => $conditional_redirect);
                        }
                    }
                } else {
                    $arm_redirection_settings['login']['conditional_redirect'][] = array('plan_id' => 0,
                        'condition' => '',
                        'expire' => 0,
                        'url' => ARM_HOME_URL);
                }
                $arm_redirection_settings['login']['conditional_redirect']['default'] = $login_default_redirect_url;
            } else {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'url';
                $login_redirect_url = (!empty($form_settings['redirect_url'])) ? $form_settings['redirect_url'] : ARM_HOME_URL;
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = $login_redirect_url;
                $arm_redirection_settings['login']['refferel'] = '';
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            }
        }

        /* ---------------- setup  ------------------------ */
        $globalSettings = $arm_global_settings->global_settings;
        $ty_pageid = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
        $arm_redirection_settings['setup_signup']['type'] = $arm_redirection_settings['setup_change']['type'] = $arm_redirection_settings['setup_renew']['type'] = 'page';
        $arm_redirection_settings['setup_signup']['page_id'] = $arm_redirection_settings['setup_change']['page_id'] = $arm_redirection_settings['setup_renew']['page_id'] = $ty_pageid;
        $arm_redirection_settings['setup_signup']['url'] = $arm_redirection_settings['setup_change']['url'] = $arm_redirection_settings['setup_renew']['url'] = ARM_HOME_URL;
        $redirection_settings['setup']['default'] = ARM_HOME_URL;

        /* ----------------social connect  ------------------------ */
        $arm_redirection_settings['social']['type'] = 'page';
        $edit_profile_page_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
        $arm_redirection_settings['social']['page_id'] = $edit_profile_page_id;
        $arm_redirection_settings['social']['url'] = ARM_HOME_URL;

        /* ---------------- default redirection rules  ------------------------ */

        $default_rules = $arm_access_rules->arm_get_default_access_rules();
        $arm_redirection_settings['default_access_rules']['logged_in']['type'] = (!empty($default_rules['redirect_logged_in_user']['type'])) ? $default_rules['redirect_logged_in_user']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['blocked']['type'] = (!empty($default_rules['redirect_blocked_user']['type'])) ? $default_rules['redirect_blocked_user']['type'] : 'home';
        //$arm_redirection_settings['default_access_rules']['pending']['type'] = (!empty($default_rules['redirect_pending_user']['type'])) ? $default_rules['redirect_pending_user']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['drip']['type'] = (!empty($default_rules['redirect_drip']['type'])) ? $default_rules['redirect_drip']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['non_logged_in']['type'] = (!empty($default_rules['redirect']['type'])) ? $default_rules['redirect']['type'] : 'home';

        $arm_redirection_settings['default_access_rules']['non_logged_in']['redirect_to'] = $default_rules['redirect']['page_id'];
        $arm_redirection_settings['default_access_rules']['logged_in']['redirect_to'] = $default_rules['redirect_logged_in_user']['page_id'];
        $arm_redirection_settings['default_access_rules']['blocked']['redirect_to'] = $default_rules['redirect_blocked_user']['page_id'];
        //$arm_redirection_settings['default_access_rules']['pending']['redirect_to'] = $default_rules['redirect_pending_user']['page_id'];
        $arm_redirection_settings['default_access_rules']['drip']['redirect_to'] = isset($default_rules['redirect_drip']['page_id']) ? $default_rules['redirect_drip']['page_id'] : '';

        $arm_redirection_settings = maybe_serialize($arm_redirection_settings);
        update_option('arm_redirection_settings', $arm_redirection_settings);
        $eleventh_step_data = array();

        if( $wpdb->last_error != "" ){
        	$eleventh_step_data['info_1'] = 'updating redirection settings';
            $eleventh_step_data['redirection_data'] = $arm_redirection_settings;
            $eleventh_step_data['query_1'] = $wpdb->last_query;
            $eleventh_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 11 => ". json_encode($eleventh_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 12) Change 'arm_payment_date' column type from 'text' to 'datetime' of arm_payment_log' table.
          for that first create temporary table
         */

        if ($wpdb->has_cap('collation')) {

            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";
        }

        $backup_table = $wpdb->prefix . 'arm_payment_log_temp';
        $original_tab = $wpdb->prefix . 'arm_payment_log';

        $wpdb->query("CREATE TABLE ".$backup_table." LIKE ".$original_tab);

        $twelth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_1'] = 'Creating Temporary Backup';
	        $twelth_step_data['query_1'] = $wpdb->last_query;
	        $twelth_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("INSERT INTO ".$backup_table." SELECT * FROM ".$original_tab);
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_2'] = 'Copy whole table data into new table';
	        $twelth_step_data['query_2'] = $wpdb->last_query;
	        $twelth_step_data['error_2'] = $wpdb->last_error;
	    }
        /* alter table */

        $wpdb->query("ALTER TABLE `" . $arm_tbl_arm_payment_log . "` MODIFY `arm_payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_3'] = 'setting default value for payment_date column';
	        $twelth_step_data['query_3'] = $wpdb->last_query;
	        $twelth_step_data['error_3'] = $wpdb->last_error;
	    }

        if (!empty($paypal_pro_array)) {
            foreach ($paypal_pro_array as $log_id => $payment_date) {
                $new_payment_date = substr($payment_date, 4, 4) . "-" . substr($payment_date, 0, 2) . "-" . substr($payment_date, 2, 2) . " 00:00:00";
                $wpdb->update($arm_tbl_arm_payment_log, array('arm_payment_date' => $new_payment_date), array('arm_log_id' => $log_id), array('%s'), array('%d'));
            }
        }

        $arm_db_logs .= " Step 12 => ". json_encode($twelth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);


        /* 13) add arm_comleted_recurring and arm_next_due_date meta in case of auto_debit_subscription */

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'arm_user_plan_ids',
                    'value' => '',
                    'compare' => '!='
                ),
            )
        );

        $amTotalUsers = get_users($args);
        if (!empty($amTotalUsers)) {
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_user_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $arm_user_plan_ids = !empty($arm_user_plan_ids) ? $arm_user_plan_ids : array();
                if (!empty($arm_user_plan_ids)) {
                    foreach ($arm_user_plan_ids as $arm_user_plan_id) {
                        $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, true);

                        $planDetail = $user_plan_data['arm_current_plan_detail'];
                        if (!empty($planDetail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $planDetail);
                        } else {
                            $plan = new ARM_Plan($arm_user_plan_id);
                        }

                        if ($plan->is_recurring()) {
                            $arm_user_payment_mode = $user_plan_data['arm_payment_mode'];
                            $arm_user_payment_gateway = $user_plan_data['arm_user_gateway'];
                            if ($arm_user_payment_mode == 'auto_debit_subscription') {

                                $actual_trial_start_date = isset($user_plan_data['arm_trial_start']) ? $user_plan_data['arm_trial_start'] : '';
                                $actual_trial_end_date = isset($user_plan_data['arm_trial_end']) ? $user_plan_data['arm_trial_end'] : '';
                                $actual_plan_start_date = isset($user_plan_data['arm_start_plan']) ? $user_plan_data['arm_start_plan'] : '';

                                $plan_start_date = '';
                                if (!empty($actual_plan_start_date)) {
                                    $plan_start_date = strtotime("-30 Minutes", $actual_plan_start_date);
                                    $plan_start_date = date('Y-m-d H:i:s', $plan_start_date);
                                }


                                $trial_start_date = '';
                                if (!empty($actual_trial_start_date)) {
                                    $trial_start_date = strtotime("-30 Minutes", $actual_trial_start_date);
                                    $trial_start_date = date('Y-m-d H:i:s', $trial_start_date);
                                }

                                $arm_user_payment_cycle = $user_plan_data['arm_payment_cycle'];

                                if (!empty($actual_trial_start_date) && !empty($actual_trial_end_date)) {

                                    $total_completed_recurrence = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`arm_log_id`) FROM `" . $arm_tbl_arm_payment_log . "` WHERE `arm_payment_date` >= %s AND `arm_user_id` = %d AND `arm_payment_gateway`=%s AND `arm_display_log`=%d", $trial_start_date, $user_id, $arm_user_payment_gateway, 1));
                                    $total_completed_recurrence--;
                                    if ($arm_user_payment_gateway == 'stripe') {
                                        $recurring_data = $plan->prepare_recurring_data($arm_user_payment_cycle);
                                        $trial_amount = $recurring_data['trial']['amount'];
                                        if ($trial_amount > 0) {
                                            $total_completed_recurrence--;
                                        }
                                    }

                                    $allow_trial = true;
                                } else {
                                    $total_completed_recurrence = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`arm_log_id`) FROM `" . $arm_tbl_arm_payment_log . "` WHERE `arm_payment_date` >= %s AND `arm_user_id` = %d AND `arm_payment_gateway`=%s AND `arm_display_log`=%d", $plan_start_date, $user_id, $arm_user_payment_gateway, 1));
                                    $allow_trial = false;
                                }
                                $user_plan_data['arm_completed_recurring'] = $total_completed_recurrence;
                                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, $user_plan_data);

                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $arm_user_plan_id, $allow_trial, $arm_user_payment_cycle);
                                $user_plan_data['arm_next_due_payment'] = $arm_next_payment_date;
                                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, $user_plan_data);
                            }
                        }
                    }
                }
            }
        }
        $thirteenth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $thirteenth_step_data['info_1'] = ' add arm_comleted_recurring and arm_next_due_date meta in case of auto_debit_subscription';
	        $thirteenth_step_data['query_1'] = $wpdb->last_query;
	        $thirteenth_step_data['error_1'] = $wpdb->last_error;
	    }
        
        $arm_db_logs .= " Step 13 => ". json_encode($thirteenth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

                
        /* 14) Add arm_subscription plan, arm_template_html, arm_ref_template, arm_html_before_fields, arm_html_after_fields, arm_enable_admin_profile column in arm_member_templates table */

        $arm_member_template_tbl = $ARMember->tbl_arm_member_templates;

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_subscription_plan` TEXT NULL AFTER `arm_default`");
        $fourtinth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_1'] = 'adding column `arm_subscription_plan` in member_template_table';
	        $fourtinth_step_data['query_1'] = $wpdb->last_query;
	        $fourtinth_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_template_html` longtext AFTER `arm_subscription_plan`");
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_2'] = 'adding column `arm_template_html` in member_template_table';
	        $fourtinth_step_data['query_2'] = $wpdb->last_query;
	        $fourtinth_step_data['error_2'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_ref_template` int(11) NOT NULL DEFAULT '0' AFTER `arm_default`");
        
        if( $wpdb->last_error != "" ){       
	        $fourtinth_step_data['info_3'] = 'adding column `arm_ref_template` in member_template_table';
	        $fourtinth_step_data['query_3'] = $wpdb->last_query;
	        $fourtinth_step_data['error_3'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_html_before_fields` longtext AFTER `arm_options`");
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_4'] = 'adding column `arm_html_before_fields` in member_template_table';
	        $fourtinth_step_data['query_4'] = $wpdb->last_query;
	        $fourtinth_step_data['error_4'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_html_after_fields` longtext AFTER `arm_html_before_fields`");
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_5'] = 'adding column `arm_html_after_fields` in member_template_table';
	        $fourtinth_step_data['query_5'] = $wpdb->last_query;
	        $fourtinth_step_data['error_5'] = $wpdb->last_error;
	    }
                

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0' AFTER `arm_html_after_fields`");
        
        if( $wpdb->last_error != "" ){
        	$fourtinth_step_data['info_6'] = 'adding column `arm_enable_admin_profile` in member_template_table';
        	$fourtinth_step_data['query_6'] = $wpdb->last_query;
        	$fourtinth_step_data['error_6'] = $wpdb->last_error;
        }
                
        $arm_db_logs .= " Step 14 => ". json_encode($fourtinth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 15) update existing all user's meta 'arm_firsttime_login' for first time login */
        $fiftinth_step_data = array();
        $users = get_users(array('fields' => 'ID'));
        if (!empty($users)) {
            $un = 1;
            foreach ($users as $user) {
                $user_update = update_user_meta($user, 'arm_firsttime_login', 1);
                if( $wpdb->last_error != "" ){
	                $fiftinth_step_data['info_1_' . $un] = 'update existing all users meta `arm_firsttime_login` for first time login';
	                $fiftinth_step_data['query_1_' . $un] = $wpdb->last_query;
	                $fiftinth_step_data['error_1_' . $un] = $wpdb->last_error;
	            }
                $un++;
            }
        }
        $arm_db_logs .= " Step 15 => ". json_encode($fiftinth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);


        /* 16) set log file permission 

        $log_response_file = MEMBERSHIP_DIR . '/log/response.txt';
        @chmod($log_response_file, 0755);
        $log_dron_file = MEMBERSHIP_DIR . '/log/cron_log.txt';
        @chmod($log_dron_file, 0755);
        $log_payment_response_file = MEMBERSHIP_DIR . '/log/payment_response.txt';
        @chmod($log_payment_response_file, 0755);
        $log_restriction_response_file = MEMBERSHIP_DIR . '/log/restriction_response.txt';
        @chmod($log_restriction_response_file, 0755);*/

        $arm_db_logs .= " Step 16 => no any database query executed === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 17) add troubleshoot link for disabling rename wp-admin */
        $arm_hide_admin_rand_no = wp_rand();
        update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);
        $seventeen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $seventeen_step_data['info_1'] = 'add troubleshoot link for disabling rename wp-admin';
	        $seventeen_step_data['query_1'] = $wpdb->last_query;
	        $seventeen_step_data['error_1'] = $wpdb->last_error;
	    }
        $arm_db_logs .= " Step 17 => ". json_encode($seventeen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 16) add 'arm_show_old_items' column in drip rule table */
        //$arm_tbl_arm_drip_rule = $ARMember->tbl_arm_drip_rules;
        //$wpdb->query("ALTER TABLE `" . $arm_tbl_arm_drip_rule . "` ADD `arm_show_old_items` INT(11) NOT NULL DEFAULT '0' AFTER `arm_rule_type`");

        /* 18) update user profile field labels in profile template options in database */
        $profile_data = $wpdb->get_row($wpdb->prepare('SELECT `arm_options` FROM `' . $arm_member_template_tbl . '` WHERE arm_id = %d', 1));
        $options = maybe_unserialize($profile_data->arm_options);
        $profile_fields = $options['profile_fields'];
        $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
        $labels = array();
        foreach ($profile_fields as $k => $v) {
            $labels[$k] = isset($dbProfileFields[$k]) ? $dbProfileFields[$k]['label'] : '';
        }
        $options['label'] = $labels;
        $wpdb->update($arm_member_template_tbl, array('arm_options' => maybe_serialize($options)), array('arm_id' => 1));
        $eighteen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $eighteen_step_data['info_1'] = 'update user profile field labels in profile template options in database';
	        $eighteen_step_data['query_1'] = $wpdb->last_query;
	        $eighteen_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 18 => ". json_encode($eighteen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 19) Migrate for display admin users field */
        $profile_temp_data = $wpdb->get_row($wpdb->prepare("SELECT `arm_options`,`arm_slug` FROM `" . $arm_member_template_tbl . "` WHERE arm_id = %d", 1));
        $options = maybe_unserialize($profile_temp_data->arm_options);
        $profileTemplateOptions = $options;
        $templateSlug = $profile_temp_data->arm_slug;
        $display_admin_users = isset($options['show_admin_users']) ? $options['show_admin_users'] : 0;
        if ($profileTemplateOptions['default_cover'] != '') {
            $profileTemplateOptions['default_cover_photo'] = 1;
        }
        $profileTemplateOptions['hide_empty_profile_fields'] = 1;
        $wpdb->update($arm_member_template_tbl, array('arm_options' => maybe_serialize($profileTemplateOptions)), array('arm_id' => 1));
        $ninteen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $ninteen_step_data['info_1'] = 'pdate user profile field options in profile template options in database';
	        $ninteen_step_data['query_1'] = $wpdb->last_query;
	        $ninteen_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->update($arm_member_template_tbl, array('arm_enable_admin_profile' => $display_admin_users), array('arm_id' => 1));

        if( $wpdb->last_error != "" ){
	        $ninteen_step_data['info_2'] = 'update show_admin_profile option in database';
	        $ninteen_step_data['query_2'] = $wpdb->last_query;
	        $ninteen_step_data['error_2'] = $wpdb->last_error;
	    }
                
        $arm_db_logs .= " Step 19 => ". json_encode($ninteen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 20) Update profile template for default template */
        if ($templateSlug == 'profiletemplate1') {
            $arm_template_html = '<div class="arm_profile_defail_container arm_profile_tabs_container">
        <div class="arm_profile_detail_wrapper">
          <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
              <div class="arm_user_avatar">{ARM_Profile_Avatar_Image}</div>
              <div class="arm_profile_separator"></div>
              <div class="arm_profile_header_info"> <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                {ARM_Profile_Badges}
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="social_profile_fields">
                  {ARM_Profile_Social_Icons}
                </div>
              </div>
            </div>
              {ARM_Cover_Upload_Button}
          </div>
          <div class="armclear"></div>
          {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
          <div class="arm_profile_field_before_content_wrapper"></div>
          <div class="arm_profile_tab_detail" data-tab="general">
            <div class="arm_general_info_container">
              <table class="arm_profile_detail_tbl">
                <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
              </table>
            </div>
          </div>
          <div class="arm_profile_field_after_content_wrapper"></div>
          {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div>
        <div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate2') {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="armclear"></div>
                <div class="arm_profile_header_info arm_profile_header_bottom_box">
                    <span class="arm_profile_name_link">
                        {ARM_Profile_User_Name}
                    </span>
                    {ARM_Profile_Badges}
                </div>
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="armclear"></div>
                <div class="arm_user_social_icons_all social_profile_fields arm_mobile">
                    {ARM_Profile_Social_Icons_Mobile}
                </div>
                <div class="arm_profile_header_top_box">
                    <div class="arm_user_social_icons_left arm_desktop">
                        {ARM_Profile_Social_Icons_Left}
                    </div>
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_user_social_icons_right arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Right}
                               
                    </div>
                </div>
            </div>
            {ARM_Cover_Upload_Button}
        </div>
        <div class="arm_profile_defail_container arm_profile_tabs_container">
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($profileTemplateOptions['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $profileTemplateOptions['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_after_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate3') {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="arm_profile_header_info">
                    <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                        {ARM_Profile_Badges}
                    <div class="armclear"></div>
                    <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                </div>
                <div class="social_profile_fields">
                    {ARM_Profile_Social_Icons}
                </div>
                <div class="armclear"></div>
            </div><div class="arm_user_avatar">
                {ARM_Profile_Avatar_Image}
            </div> {ARM_Cover_Upload_Button}</div><div class="arm_profile_defail_container arm_profile_tabs_container">
                {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
                <div class="arm_profile_field_before_content_wrapper"></div>
                <div class="arm_profile_tab_detail" data-tab="general">
                    <div class="arm_general_info_container">
                        <table class="arm_profile_detail_tbl">
                            <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                        </table>
                    </div>
                </div>
                <div class="arm_profile_field_after_content_wrapper"></div>
                {ARM_PROFILE_FIELDS_AFTER_CONTENT}
            </div>
        </div><div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate4') {
            $arm_template_html = '<div class="arm_profile_defail_container arm_profile_tabs_container">
        <div class="arm_profile_detail_wrapper">
            <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">

                <div class="arm_profile_picture_block_inner">
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_profile_separator"></div>
                    <div class="arm_profile_header_info">
                        <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                                
                            {ARM_Profile_Badges}
                               
                        <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                        <div class="social_profile_fields">
                            {ARM_Profile_Social_Icons}
                        </div>
                    </div>
                </div>
                {ARM_Cover_Upload_Button}
            </div>
            <div class="armclear"></div>
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_before_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        } else {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="armclear"></div>
                <div class="arm_profile_header_info arm_profile_header_bottom_box">
                    <span class="arm_profile_name_link">
                        {ARM_Profile_User_Name}
                    </span>
                    {ARM_Profile_Badges}
                </div>
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="armclear"></div>
                <div class="arm_user_social_icons_all social_profile_fields arm_mobile">
                    {ARM_Profile_Social_Icons_Mobile}
                </div>
                <div class="arm_profile_header_top_box">
                    <div class="arm_user_social_icons_left arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Left}
                               
                    </div>
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_user_social_icons_right arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Right}
                               
                    </div>
                </div>
            </div>
            {ARM_Cover_Upload_Button}
        </div>
        <div class="arm_profile_defail_container arm_profile_tabs_container">
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($profileTemplateOptions['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $profileTemplateOptions['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_after_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        }

        $wpdb->update($arm_member_template_tbl, array('arm_template_html' => $arm_template_html), array('arm_id' => 1));
        $twenty_step_data = array();
        if( $wpdb->last_error != "" ){
	        $twenty_step_data['info_1'] = 'update profile template html in database';
	        $twenty_step_data['query_1'] = $wpdb->last_query;
	        $twenty_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 20 => ". json_encode($twenty_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        $date = date('Y-m-d H:i:s');
        update_option('armember_update_end', $date);

        delete_option('arm_update_to_new_version');
        delete_option('arm_new_version');

        update_option('arm_new_version_installed', 1);
        update_option('arm_version', '2.0');
        $arm_newdbversion = '2.0';

        die();
    }

    function arm_check_user_cap($arm_capabilities = '', $is_ajax_call='')
    {
        global $arm_global_settings;

        $errors = array();
        $message = "";
        if($is_ajax_call==true || $is_ajax_call=='1' || $is_ajax_call==1)
        {
            if (!current_user_can($arm_capabilities)) 
            {
                $errors[] = __('Sorry, You do not have permission to perform this action.', 'ARMember');
                $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
                $return_array['message'] = $return_array['msg'];

                echo json_encode($return_array);
                exit;
            }
        }
        
        $wpnonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        $arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );
        if(!$arm_verify_nonce_flag && !empty($wpnonce))
        {
            $errors[] = __('Sorry, Your request can not process due to security reason.', 'ARMember');
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            $return_array['message'] = $return_array['msg'];
            echo json_encode($return_array);
            exit;
        }
    }
    function arm_get_country_from_ip($logged_in_ip="")
    {
        global $ARMember;
        
        if( '' == $logged_in_ip ){
            return '';
        }

        $country = "";
        try{
            $country_reader = new Reader(MEMBERSHIP_LIBRARY_DIR.'/geoip/inc/GeoLite2-Country.mmdb');
            $record = $country_reader->country($logged_in_ip);
            $country = $record->country->name;
        } catch(Exception $e){
            $country = "";
        }
        
        return $country;
    }

    function arm_session_start( $force = false ) {
        /**
         * Start Session
         */
        $arm_session_id = session_id();
        if( empty($arm_session_id) || $force == true ) {
            @session_start();
        }
    }

    function arm_receive_heartbeat_func($response, $data) {
        if ( empty( $data['arm_update_user_logout_status'] ) ) {
            return $response;
        }
        
        if( is_user_logged_in() ) {
            global $wpdb, $ARMember;
            $user_id = get_current_user_id();

            if (empty($user_id) || user_can($user_id, 'administrator')) {
                return;
            }
            
            $login_date = "0000-00-00 00:00:00";
            $login_history_id = 0;

            if(!empty($_COOKIE['arm_cookie_' . $user_id])) {
                $stored_cookie = $_COOKIE['arm_cookie_' . $user_id];
                $inserted_id = explode('||', $stored_cookie);
                $session_id = trim($inserted_id[0]);
                $wp_insert_id = trim($inserted_id[1]);

                $login_date_res = $wpdb->get_row("SELECT `arm_history_id`, `arm_logged_in_date` FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_history_id`=".$wp_insert_id, ARRAY_A);
                //$login_date_res = $wpdb->get_row("SELECT `arm_history_id`, `arm_logged_in_date` FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_user_id`=".$user_id." AND arm_user_current_status=1 ORDER BY arm_history_id desc", ARRAY_A);
                
                if(!empty($login_date_res)) {
                    $login_date = $login_date_res['arm_logged_in_date'];
                    $login_history_id = $login_date_res['arm_history_id'];

                    if($login_history_id != 0 && $login_date != "0000-00-00 00:00:00") {

                        //$arm_current_time = date('Y-m-d H:i:s');
                        $arm_current_time = current_time('timestamp');
                        $arm_current_time_date_time = date('Y-m-d H:i:s',$arm_current_time);


                        $login_duration = $arm_current_time - strtotime($login_date);
                        $arm_login_duration = date('H:i:s', $login_duration);

                        $wpdb->update(
                            $ARMember->tbl_arm_login_history,
                            array('arm_logout_date'=>$arm_current_time_date_time, 'arm_login_duration'=>$arm_login_duration),
                            array('arm_history_id'=>$login_history_id)
                        );
                        $response['arm_user_logout_status_updated'] = '1';

                    }
                }
            }
            
        }
        
        return $response;
    }
    function arm_heartbeat_settings( $settings ) {
        $settings['interval'] = 30;
        return $settings;
    }
}
}

global $ARMember, $arm_debug_payment_log_id, $arm_debug_general_log_id;
$ARMember = new ARMember();
$arm_debug_payment_log_id = 0;
$arm_debug_general_log_id = 0;


if( !class_exists('ARM_rename_wp') ){
class ARM_rename_wp{
        
        var $enable_rename_wp;
        var $new_wp_admin_name;
        public $arm_replace;
        public $arm_rewrites;

        function __construct(){

        	if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_logout_after_rename_wp' ){
        		wp_destroy_current_session();
		        wp_clear_auth_cookie();
		        $_REQUEST['redirect_to'] = $redirect_to = wp_login_url();
		        wp_redirect($redirect_to);
		        exit;
        	}

            $global_settings = get_option('arm_global_settings');

            $all_settings = maybe_unserialize($global_settings);
            
            $all_settings = $all_settings['general_settings'];

            $this->enable_rename_wp = $all_settings['rename_wp_admin'];
            $this->new_wp_admin_name = (isset($all_settings['new_wp_admin_path']) && $all_settings['new_wp_admin_path'] != '') ? $all_settings['new_wp_admin_path'] : 'wp-admin';

            register_deactivation_hook(MEMBERSHIP_DIR.'/armember.php',array($this,'arm_flush_rules'));

            $this->armBuildRedirect();
            add_action('generate_rewrite_rules',array($this,'arm_rewrite_rules'),1);

            if( get_option('permalink_structure')){
                add_filter('admin_url',array($this,'admin_url'),1,1);
                add_filter('network_admin_url',array($this,'network_admin_url'),1,1);
                add_filter('site_url',array($this,'site_url'),1,2);
                add_filter('login_redirect',array($this,'arm_sanitize_redirect'),1,1);
                add_action('wp_logout',array($this,'arm_wp_logout'),1,1);

                $this->armHideUrl();
            }

            global $arm_ajaxurl;
            $arm_ajaxurl = admin_url('admin-ajax.php');
            
            add_action('admin_notices',array($this,'arm_license_admin_notices'));

            add_action( 'admin_enqueue_scripts', array($this, 'arm_load_script' ) );
            add_action( 'wp_ajax_dismiss_admin_notice', array($this, 'arm_dismiss_admin_notice' ) );

            if(!empty($GLOBALS['wp_version']) && version_compare( $GLOBALS['wp_version'], '5.7.2', '>' ))
            {
                add_filter('block_categories_all', array($this,'arm_gutenberg_category'), 10, 2);
            }
            else {
                add_filter('block_categories', array($this,'arm_gutenberg_category'), 10, 2);
            }

            add_action('enqueue_block_editor_assets',array($this,'arm_enqueue_gutenberg_assets'));

            add_action('activated_plugin',array($this,'arm_is_addon_activated'),11,2);

        }

        function arm_is_addon_activated($plugin,$network_activation){
            
            global $arm_social_feature,$arm_members_activity,$ARMember;
            $setact = 0;
            global $check_sorting;
            $setact = $arm_members_activity->$check_sorting();
            if( $setact != 0 ){
                return;
            }
			
			
			
            $addon_resp = $arm_social_feature->addons_page();
            $armember_addons = array();
            if ($addon_resp != "") {
                $resp = explode("|^^|", $addon_resp);
                if ($resp[0] == 1){
                    $myplugarr = array();
                    $myplugarr = unserialize(base64_decode($resp[1]));
                    if( is_array($myplugarr) && count($myplugarr) > 0 ){
                        foreach( $myplugarr as $plug){
                            $armember_addons[$plug['plugin_installer']] = $plug['full_name'];
                        }
                    }
                }
            }
            
			
			
            if( is_array($armember_addons) && count($armember_addons) > 0 && array_key_exists($plugin, $armember_addons) && $setact == 0 ){
                
				$ARMember->arm_session_start(true);
                //$_SESSION['arm_deactivate_plugin'] = $armember_addons[$plugin];
                deactivate_plugins($plugin, TRUE);
                header('Location: ' . network_admin_url('plugins.php?deactivate=true&arm_license_deactivate=true&arm_deactivate_plugin='.$armember_addons[$plugin]));
                die;
            }
			
        }

        function arm_gutenberg_category($category,$post){
            $new_category = array(
                array(
                    'slug' => 'armember',
                    'title' => 'ARMember Blocks'
                )
            );

            $final_categories = array_merge($category,$new_category);

            return $final_categories;
        }

        function arm_enqueue_gutenberg_assets(){
            global $arm_version;

            wp_register_script('arm_gutenberg_script',MEMBERSHIP_URL.'/js/arm_gutenberg_script.js',array('wp-blocks','wp-element', 'wp-i18n', 'wp-components'),$arm_version);
            wp_enqueue_script('arm_gutenberg_script');

            wp_register_style('arm_gutenberg_style',MEMBERSHIP_URL.'/css/arm_gutenberg_style.css',array(), $arm_version);
            wp_enqueue_style('arm_gutenberg_style');

        }


        function arm_load_script() {

            global $wp_scripts;

            if( $this->new_wp_admin_name != 'wp-admin' && $this->enable_rename_wp == 1){

                $arm_wp_script_data = $wp_scripts->get_data('wp-util', 'data');
                
                $arm_default_site_path=get_site_url( '', 'wp-admin/admin-ajax.php', 'relative' );
                $arm_default_site_path=wp_json_encode($arm_default_site_path);

                $remove_amr_script_var='var _wpUtilSettings = {"ajax":{"url":'.$arm_default_site_path.'}};';
                
                if(!is_array($arm_wp_script_data)) {
                    $arm_wp_script_data=str_replace($remove_amr_script_var,' ', $arm_wp_script_data);
                }
                if(empty($arm_wp_script_data)){
                    $wp_scripts->add_data('wp-util', 'data','');
                }else{
                    $wp_scripts->add_data('wp-util', 'data',$arm_wp_script_data);
                }
                
                $wp_scripts->localize(
                    'wp-util',
                    '_wpUtilSettings',
                    array(
                        'ajax' => array(
                            'url' => admin_url( 'admin-ajax.php', 'relative' ),
                        ),
                    )
                );
            }
            
            if(is_customize_preview()) return;
            
            wp_enqueue_script('arm-admin-dismissible-notices', MEMBERSHIP_URL . '/js/dismiss_admin_notice.js', array(), MEMBERSHIP_VERSION);

            wp_localize_script(
                'dismissible-notices',
                'dismissible_notice',
                array(
                    'nonce' => wp_create_nonce( 'dismissible-notice' ),
                )
            );
        }

        function arm_dismiss_admin_notice() {
            $option_name        = sanitize_text_field( $_POST['option_name'] );
            $dismissible_length = sanitize_text_field( $_POST['dismissible_length'] );
            $transient          = 0;
            if ( 'forever' != $dismissible_length ) {
                // If $dismissible_length is not an integer default to 1
                //$dismissible_length = ( 0 == absint( $dismissible_length ) ) ? 1 : $dismissible_length;
                $dismissible_length = 1;
                //$transient          = absint( $dismissible_length ) * DAY_IN_SECONDS;

                $transient          = time() + ($dismissible_length * MONTH_IN_SECONDS);
                $dismissible_length = strtotime( absint( $dismissible_length ) . ' month' );
            }
            //check_ajax_referer( 'dismissible-notice', 'nonce' );
            $return = set_site_transient( $option_name, $dismissible_length, $transient );
            wp_die();
        }

        function arm_is_admin_notice_active( $arg ) {
            $array       = explode( '-', $arg );
            $length      = array_pop( $array );
            $option_name = implode( '-', $array );
            $db_record   = get_site_transient( $option_name );
            
            if($db_record == "")
                return true;

            if ( 'forever' == $db_record ) {
                return false;
            } elseif ( absint( $db_record ) >= time() ) {
                return false;
            } else {
                return true;
            }
        }

        function arm_license_admin_notices(){
            global $ARMember,$arm_email_settings, $arm_global_settings;
            $all_email_settings = $arm_email_settings->arm_get_all_email_settings();

            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            
            $class = 'notice notice-error arf-notice-update-warning is-dismissible';
            global $arm_members_activity;
            $setact = 0;
            global $check_sorting;
            $setact = $arm_members_activity->$check_sorting();
            if($setact != 1)
            {
                if ( $this->arm_is_admin_notice_active( 'notice-one-2' ) ) {

                $admin_css_url = admin_url('admin.php?page=arm_manage_license');
                printf( '<div data-dismissible="notice-one-2" class="%1$s"><p><b>ARMember license is not activated. To receive regular updates, please activate license from <a href="%2$s">here</a></b></p></div>', esc_attr( $class ), esc_html( $admin_css_url )); 
                }
            }

            $get_purchased_info = get_option('armSortInfo');

            $sortorderval = base64_decode($get_purchased_info);
            $ordering = array();

            $pcodeinfo = "";
            $pcodedate = "";
            $pcodedateexp = "";
            $pcodelastverified = "";
            $pcodecustemail = "";

            if (is_array($ordering)) { 
                $ordering = explode("^", $sortorderval);

                if (is_array($ordering)) { 
             
                if (isset($ordering[2]) && $ordering[2] != "") {
                    $pcodedateexp = $ordering[2];
                } else {
                    $pcodedateexp = "";
                }

                if($pcodedateexp != "")
                { 
                $exp_date=strtotime($pcodedateexp);
                $today = strtotime("today"); 

                if($exp_date < $today)
                {
                    if ( $this->arm_is_admin_notice_active( 'notice-two-2' ) ) {
                    
                        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
                        printf( '<div data-dismissible="notice-two-2" class="%1$s"><p><b>It seems your ARMember support period is expired. To continue receiving our prompt support you need to renew your support. Please <a href="%2$s">click  here</a> to extend support.</b></p></div>', esc_attr( $class ), esc_html( $admin_css_url ));  
                    }
                }
                }
                }
            }
			
            //$ARMember->arm_session_start();
            if( isset($_GET['arm_license_deactivate']) && isset($_GET['arm_deactivate_plugin']) && $_GET['arm_deactivate_plugin'] != '' ){
                $arm_deactivate_plugin = $_GET['arm_deactivate_plugin'];
                echo "<div class='notice notice-error arm_auto_deactivate_addon_plugin' style='background:#dc3232;border-left-color:#dc3232;color:#fff;font-weight:bold;'><p>".sprintf(esc_html__('Please activate %s license to enable %s','ARMember'),'ARMember',$arm_deactivate_plugin)."</p></div>";
                unset($_GET['arm_deactivate_plugin']);
            }

            $aweber_settings = isset($all_email_settings['arm_email_tools']['aweber']) ? $all_email_settings['arm_email_tools']['aweber'] : '';
            $aweber_consumer_key = isset($aweber_settings['consumer_key']) ? $aweber_settings['consumer_key'] : '';
            $aweber_consumer_secret = isset($aweber_settings['consumer_secret']) ? $aweber_settings['consumer_secret'] : '';

            if( isset($aweber_settings['status']) && $aweber_settings['status'] == 1 && isset($aweber_settings['list']) && !empty($aweber_settings['list']) && $aweber_consumer_key != '' && $aweber_consumer_secret != '' && $aweber_consumer_key!=MEMBERSHIP_AWEBER_CONSUMER_KEY && $aweber_consumer_secret!=MEMBERSHIP_AWEBER_CONSUMER_SECRET ){
                echo "<div class='notice notice-warning' style='display:block !important;'>";
                    echo "<p>";
                        printf(esc_html__('Please re-authorize %s app again from %s -> General Settings -> Opt-ins Configuration page, otherwise %s will not work. You can read more about %s settings at our documentation %s.','ARMember'),'Aweber','ARMember','Aweber','Aweber','<a href="https://www.armemberplugin.com/documents/armember-opt-ins-provide-ease-of-email-marketing#ARMAweber" target="_blank">'.esc_html__('here','ARMember').'</a>');
                    echo "</p>";
                echo "</div>";
            }
            $all_global_settings = $arm_global_settings->global_settings;
            $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
            $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';

            $arm_recaptcha_update_notice_flag = get_option('arm_recaptcha_notice_flag');
            if((!empty($arm_recaptcha_site_key) || !empty($arm_recaptcha_private_key)) && $arm_recaptcha_update_notice_flag==1){
                echo "<div class='notice notice-warning' style='display:block !important;'>";
                    echo "<p>";
                        printf(esc_html__('ARMember upgraded Google ReCaptcha V3. Recommend to update Google reCAPTCHA Settings %s -> General Settings -> Google reCAPTCHA Configuratio Section, otherwise %s will not work.','ARMember'),'ARMember','Google reCAPTCHA');
                    echo "</p>";
                echo "</div>";
            }    
        }


        function arm_flush_rules(){
            global $wp_rewrite,$arm_global_settings;
            $new_settings = maybe_unserialize(get_option('arm_global_settings'));
            $arm_general_settings = $new_settings['general_settings'];
            if( $arm_general_settings['rename_wp_admin'] == 1 ){
                $new_wp_admin_name = $arm_general_settings['new_wp_admin_path'];
                $removeTag = $new_wp_admin_name.'/(.*)';
                $wp_rewrite->remove_rewrite_tag($removeTag);
                $arm_global_settings->remove_config_file();
                require_once ABSPATH . 'wp-admin/includes/misc.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $new_settings['general_settings']['rename_wp_admin'] = 0;
                $new_settings['general_settings']['new_wp_admin_path'] = 'wp-admin';
                $new_settings['general_settings']['temp_wp_admin_path'] = 'wp-admin';
                update_option('arm_global_settings',$new_settings);
                if( function_exists('save_mod_rewrite_rules')){
                    save_mod_rewrite_rules();
                }
                
                $active_plugins = get_option('active_plugins');
                $armember_plugin = 'armember/armember.php';
                if( in_array($armember_plugin,$active_plugins) ){
                    $key = array_search($armember_plugin,$active_plugins);
                    unset($active_plugins[$key]);
                    $active_plugins = array_values($active_plugins);
                    update_option('active_plugins',$active_plugins);
                }

                wp_destroy_current_session();
                wp_clear_auth_cookie();
                wp_logout();
                die();
            }
        }

        function arm_rewrite_rules_array($rules){
            global $ARMember,$wp_rewrite;
            return $rules;
        }

        function isHtaccessWritable(){
            if( is_multisite() ){
                return false;
            }

            global $wp_rewrite;

            $home_path = get_home_path();
            $htaccess_file = $home_path . '.htaccess';

            if ((!file_exists($htaccess_file) && is_writable($home_path) && $wp_rewrite->using_mod_rewrite_permalinks()) || is_writable($htaccess_file)) {
                if (got_mod_rewrite()) {
                    return true;
                }
            }

            return false;
        }

        function site_url($url,$path){

            if( $url == '' ){
                return $url;
            }

            return $url;
        }

        function arm_sanitize_redirect($redirect){
            return $redirect;
        }

        function armBuildRedirect(){
            $default_admin_url = admin_url();
            if( $this->new_wp_admin_name != 'wp-admin'){
                $this->arm_replace['to'][] = $this->new_wp_admin_name.'/';
                $this->arm_replace['from'][] = 'wp-admin/';
                $this->arm_replace['rewrite'][] = true;
            }
            return $this;
        }

        function arm_rewrite_rules($wp_rewrite){
            global $ARMember;
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $rewrites = array();
            $rewritecode = '';

            if( !empty($this->arm_replace)){
                foreach($this->arm_replace['to'] as $key => $replace ){
                    if( $this->arm_replace['rewrite'][$key]){
                        $rewrites[] = array(
                            'from' => $this->arm_replace['to'][$key].'(.*)',
                            'to' => $this->arm_replace['from'][$key].'$'.(substr_count($this->arm_replace['to'][$key], '(') + 1)
                        );
                    }
                }

                $this->arm_rewrites = array();
                $arm_non_wp_rules = array();
                foreach($rewrites as $rewrite){
                    add_rewrite_tag($rewrite['from'], $rewrite['to']);
                    $arm_non_wp_rules[$rewrite['from']] = $rewrite['to'];
                }

                $this->arm_rewrites = $rewrites;
                $wp_rewrite->non_wp_rules = $arm_non_wp_rules + $wp_rewrite->non_wp_rules;
                if( function_exists('save_mod_rewrite_rules') ){
                    if(!save_mod_rewrite_rules()){
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return true;
        }

        function admin_url($url){

            if (!defined('ADMIN_COOKIE_PATH')) {
                return $url;
            }

            if( $this->new_wp_admin_name == 'wp-admin'){
                return $url;
            }

            if( $this->enable_rename_wp == 1){
                $find = '/wp-admin/';
                $replace = '/'.$this->new_wp_admin_name.'/';

                if( strpos($url,$find) !== false ){
                    $url = str_replace($find,$replace,$url);
                }
            }
            return $url;
        }

        function network_admin_url($url){
            if (!defined('ADMIN_COOKIE_PATH')) {
                return $url;
            }

            if( $this->new_wp_admin_name == 'wp-admin'){
                return $url;
            }

            if( $this->enable_rename_wp == 1 ){
                $renameTo = $this->new_wp_admin_name;
                $renameFrom = 'wp-admin';
                $find = network_site_url($renameFrom.'/',$renameTo);
                $replace = network_site_url('/'.$renameTo.'/',$renameTo);
                if( strpos($url,$find) === 0){
                    $url = $replace.substr($url,strlen($find));
                }
            }
            return $url;
        }

        function armHideUrl(){

            if(isset($_SERVER['SERVER_NAME'])){
                $url = $_SERVER['REQUEST_URI'];
                if( $url == wp_make_link_relative(get_bloginfo('url')) . '/' . $this->new_wp_admin_name ){
                    wp_redirect(admin_url());
                    exit;
                }
            }



            if( $this->enable_rename_wp == 1 && strpos($_SERVER['REQUEST_URI'],$this->new_wp_admin_name) === false && strpos($_SERVER['REQUEST_URI'],'wp-admin') !== false){
                    wp_redirect(home_url('/404_Not_Found'));
                    exit;
            }
        }

        function arm_wp_logout(){
            
            if( !isset($_REQUEST['action']) ){
                wp_destroy_current_session();
                wp_clear_auth_cookie();
                // $_REQUEST['redirect_to'] = $redirect_to = network_site_url();
                $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : network_site_url();
                wp_redirect($redirect_to);
                die();
            }
        }

    }

}