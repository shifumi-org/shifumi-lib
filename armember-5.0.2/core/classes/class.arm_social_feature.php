<?php

if (!class_exists('ARM_social_feature')) {

    class ARM_social_feature {

        var $social_settings;
        var $isSocialFeature;
        var $isSocialLoginFeature;

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            $is_social_feature = get_option('arm_is_social_feature', 0);
            $this->isSocialFeature = ($is_social_feature == '1') ? true : false;
            $is_social_login_feature = get_option('arm_is_social_login_feature', 0);
            $this->isSocialLoginFeature = ($is_social_login_feature == '1') ? true : false;
            if ($is_social_login_feature == '1') {
                $this->social_settings = $this->arm_get_social_settings();
                /* Handle Social Logins */
                add_action('wp_ajax_arm_social_login_callback', array($this, 'arm_social_login_callback'));
                add_action('wp_ajax_nopriv_arm_social_login_callback', array($this, 'arm_social_login_callback'));
                /* Handle Twitter Response */
                /*add_action('wp', array($this, 'arm_twitter_login_callback'), 5);
                add_action('wp', array($this, 'arm_login_with_twitter'), 1);
                add_action('wp', array($this, 'arm_login_with_linkedin'), 1);*/
                add_action('wp_ajax_arm_linkedin_login_callback', array($this, 'arm_linkedin_login_callback'));
                add_action('wp_ajax_nopriv_arm_linkedin_login_callback', array($this, 'arm_linkedin_login_callback'));
                add_shortcode('arm_social_login', array($this, 'arm_social_login_shortcode_func'));
            }

            add_action('wp_ajax_arm_update_social_settings', array($this, 'arm_update_social_settings_func'));
            add_action('wp_ajax_arm_update_social_network_from_form', array($this, 'arm_update_social_network_from_form_func'));




            add_action('wp_ajax_arm_install_free_plugin', array($this, 'arm_install_free_plugin'));


            add_action('wp_ajax_arm_install_plugin', array($this, 'arm_plugin_install'), 10);
            add_action('wp_ajax_arm_active_plugin', array($this, 'arm_activate_plugin'), 10);
            add_action('wp_ajax_arm_deactive_plugin', array($this, 'arm_deactivate_plugin'), 10);

            add_filter('plugins_api_args', array($this, 'arm_plugin_api_args'), 100000, 2);
            add_filter('plugins_api', array($this, 'arm_plugin_api'), 100000, 3);
            add_filter('plugins_api_result', array($this, 'arm_plugins_api_result'), 100000, 3);
            add_filter('upgrader_package_options', array($this, 'arm_upgrader_package_options'), 100000);
        }

        function arm_upgrader_package_options($options) {
            $options['is_multi'] = false;
            return $options;
        }

        function arm_deactivate_plugin() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_feature_settings'], '1');
            $plugin = $_POST['slug'];
            $silent = false;
            $network_wide = false;
            if (is_multisite())
                $network_current = get_site_option('active_sitewide_plugins', array());
            $current = get_option('active_plugins', array());
            $do_blog = $do_network = false;


            $plugin = plugin_basename(trim($plugin));


            $network_deactivating = false !== $network_wide && is_plugin_active_for_network($plugin);

            if (!$silent) {
                do_action('deactivate_plugin', $plugin, $network_deactivating);
            }

            if (false != $network_wide) {
                if (is_plugin_active_for_network($plugin)) {
                    $do_network = true;
                    unset($network_current[$plugin]);
                } elseif ($network_wide) {
                    
                }
            }

            if (true != $network_wide) {
                $key = array_search($plugin, $current);
                if (false !== $key) {
                    $do_blog = true;
                    unset($current[$key]);
                }
            }

            if (!$silent) {
                do_action('deactivate_' . $plugin, $network_deactivating);
                do_action('deactivated_plugin', $plugin, $network_deactivating);
            }


            if ($do_blog)
                update_option('active_plugins', $current);
            if ($do_network)
                update_site_option('active_sitewide_plugins', $network_current);

            $response = array(
                'type' => 'success'
            );
            echo json_encode($response);
            die();
        }

        function arm_activate_plugin() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_feature_settings'], '1');
            $plugin = $_POST['slug'];
            $plugin = plugin_basename(trim($plugin));
            $network_wide = false;
            $silent = false;
            $redirect = '';
            if (is_multisite() && ( $network_wide || is_network_only_plugin($plugin) )) {
                $network_wide = true;
                $current = get_site_option('active_sitewide_plugins', array());
                $_GET['networkwide'] = 1; // Back compat for plugins looking for this value.
            } else {
                $current = get_option('active_plugins', array());
            }

            $valid = validate_plugin($plugin);
            if (is_wp_error($valid))
                return $valid;

            if (( $network_wide && !isset($current[$plugin]) ) || (!$network_wide && !in_array($plugin, $current) )) {
                if (!empty($redirect))
                    wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect)); // we'll override this later if the plugin can be included without fatal error
                ob_start();
                wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $plugin);
                $_wp_plugin_file = $plugin;
                include_once( WP_PLUGIN_DIR . '/' . $plugin );
                $plugin = $_wp_plugin_file; // Avoid stomping of the $plugin variable in a plugin.

                if (!$silent) {
                    do_action('activate_plugin', $plugin, $network_wide);
                    do_action('activate_' . $plugin, $network_wide);
                }

                if ($network_wide) {
                    $current = get_site_option('active_sitewide_plugins', array());
                    $current[$plugin] = time();
                    update_site_option('active_sitewide_plugins', $current);
                } else {
                    $current = get_option('active_plugins', array());
                    $current[] = $plugin;
                    sort($current);
                    update_option('active_plugins', $current);
                }

                if (!$silent) {
                    do_action('activated_plugin', $plugin, $network_wide);
                }
                $response = array();
                if (ob_get_length() > 0) {
                    $response = array(
                        'type' => 'error'
                    );
                    echo json_encode($response);
                    die();
                } else {
                    $response = array(
                        'type' => 'success'
                    );
                    echo json_encode($response);
                    die();
                }
            }
        }

        function arm_plugin_install() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_feature_settings'], '1');
            if (empty($_POST['slug'])) {
                wp_send_json_error(array(
                    'slug' => '',
                    'errorCode' => 'no_plugin_specified',
                    'errorMessage' => __('No plugin specified.', 'ARMember'),
                ));
            }

            $status = array(
                'install' => 'plugin',
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
            );

            if (!current_user_can('install_plugins')) {
                $status['errorMessage'] = __('Sorry, you are not allowed to install plugins on this site.', 'ARMember');
                wp_send_json_error($status);
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php')) {
                include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/plugin-install.php'))
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            $api = plugins_api('plugin_information', array(
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
                'fields' => array(
                    'sections' => false,
                ),
            ));

            if (is_wp_error($api)) {
                $status['errorMessage'] = $api->get_error_message();
                wp_send_json_error($status);
            }

            $status['pluginName'] = $api->name;

            $skin = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);

            $result = $upgrader->install($api->download_link);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $status['debug'] = $skin->get_upgrade_messages();
            }

            if (is_wp_error($result)) {
                $status['errorCode'] = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error($status);
            } elseif (is_wp_error($skin->result)) {
                $status['errorCode'] = $skin->result->get_error_code();
                $status['errorMessage'] = $skin->result->get_error_message();
                wp_send_json_error($status);
            } elseif ($skin->get_errors()->get_error_code()) {
                $status['errorMessage'] = $skin->get_error_messages();
                wp_send_json_error($status);
            } elseif (is_null($result)) {
                global $wp_filesystem;

                $status['errorCode'] = 'unable_to_connect_to_filesystem';
                $status['errorMessage'] = __('Unable to connect to the filesystem. Please confirm your credentials.', 'ARMember');

                if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
                    $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
                }

                wp_send_json_error($status);
            }
            $install_status = $this->arm_install_plugin_install_status($api);


            if (current_user_can('activate_plugins') && is_plugin_inactive($install_status['file'])) {
                $status['activateUrl'] = add_query_arg(array(
                    '_wpnonce' => wp_create_nonce('activate-plugin_' . $install_status['file']),
                    'action' => 'activate',
                    'plugin' => $install_status['file'],
                        ), network_admin_url('plugins.php'));
            }

            if (is_multisite() && current_user_can('manage_network_plugins')) {
                $status['activateUrl'] = add_query_arg(array('networkwide' => 1), $status['activateUrl']);
            }
            $status['pluginFile'] = $install_status['file'];

            wp_send_json_success($status);
        }

        function arm_plugin_api_args($args, $action) {
            return $args;
        }

        function arm_plugin_api($res, $action, $args) {
            global $ARMember;
            $ARMember->arm_session_start();
            if (isset($_SESSION['arm_member_addon']) && !empty($_SESSION['arm_member_addon'])) {
                $armember_addons = $_SESSION['arm_member_addon'];
                $obj = array();
                foreach ($armember_addons as $slug => $armember_addon) {
                    if (isset($slug) && isset($args->slug)) {
                        if ($slug != $args->slug) {
                            continue;
                        } else {
                            $obj['name'] = $armember_addon['full_name'];
                            $obj['slug'] = $slug;
                            $obj['version'] = $armember_addon['plugin_version'];
                            $obj['download_link'] = $armember_addon['install_url'];
                            return (object) $obj;
                        }
                    } else {
                        continue;
                    }
                }
            }
            return $res;
        }

        function arm_plugins_api_result($res, $action, $args) {
            global $ARMember;
            return $res;
        }

        function arm_get_social_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $social_settings = get_option('arm_social_settings');
            $social_settings = maybe_unserialize($social_settings);
            if (!empty($social_settings['options'])) {
                $options = $social_settings['options'];
                $options['facebook']['label'] = __('Facebook', 'ARMember');
                $options['twitter']['label'] = __('Twitter', 'ARMember');
                $options['linkedin']['label'] = __('LinkedIn', 'ARMember');
                $options['vk']['label'] = __('VK', 'ARMember');
                $options['tumblr']['label'] = __('Tumblr', 'ARMember');
                $options['insta']['label'] = __('Instagram', 'ARMember');
                $options['google']['label'] = __('Google', 'ARMember');
                $social_settings['options'] = $options;
            }
            $social_settings = apply_filters('arm_get_social_settings', $social_settings);
            return $social_settings;
        }

        function arm_get_active_social_options() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $social_options = isset($this->social_settings['options']) ? $this->social_settings['options'] : array();
            $active_opts = array();
            if (!empty($social_options)) {
                foreach ($social_options as $key => $opt) {
                    if (isset($opt['status']) && $opt['status'] == '1') {
                        $active_opts[$key] = $opt;
                    }
                }
            }
            $active_opts = apply_filters('arm_get_active_social_options', $active_opts);
            return $active_opts;
        }

        function arm_update_social_settings_func() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $post_data = $_POST;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            if (isset($post_data['s_action']) && $post_data['s_action'] == 'arm_update_social_settings') {
                $social_settings = $post_data['arm_social_settings'];
                $social_settings = arm_array_map($social_settings);
                update_option('arm_social_settings', $social_settings);
                $response = array('type' => 'success', 'msg' => __('Social Setting(s) has been Saved Successfully.', 'ARMember'));
            } else {
                $response = array('type' => 'error', 'msg' => __('There is a error while updating settings, please try again.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }

        function arm_update_social_network_from_form_func() {
            $response = array('type' => 'error', 'msg' => __('There is a error while updating settings, please try again.', 'ARMember'), 'old_settings' => '');
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_update_social_network_from_form') {
                $socialOptions = isset($_POST['arm_social_settings']['options']) ? $_POST['arm_social_settings']['options'] : array();
                if (!empty($socialOptions)) {
                    foreach ($socialOptions as $snk => $snv) {
                        if (!empty($snv)) {
                            $icons = get_option('arm_social_icons_' . $snk, array());
                            $icons = maybe_unserialize($icons);
                            if (!empty($snv['custom_icon'])) {
                                foreach ($snv['custom_icon'] as $custom_icon) {
                                    $baseName = basename($custom_icon);
                                    if (isset($snv['icon']) && $snv['icon'] == 'custom') {
                                        $snv['icon'] = $baseName;
                                    }
                                    $icons[$baseName] = $custom_icon;
                                    update_option('arm_social_icons_' . $snk, $icons);
                                }
                            }
                        }
                    }
                }
                $response = array('type' => 'success', 'msg' => __('Social Setting(s) has been Saved Successfully.', 'ARMember'), 'old_settings' => maybe_serialize($socialOptions));
            }
            echo json_encode($response);
            die();
        }

        function arm_get_social_network_icons($type = '', $icon = '') {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_social_feature;
            if($arm_social_feature->isSocialLoginFeature)
            {
                $networkIcons = array();
                /* Query Monitor Change */
                $iconName = "";
                if( $icon != '' ){
                    $last_pos = strrpos($icon,'/');
                    $iconName = substr($icon,($last_pos + 1),strlen($icon));
                }
                $is_custom_icon = false;
                if (!empty($type)) {
                    switch ($type) {
                        case 'facebook':
                            $fb_icons = array('fb_1.png', 'fb_2.png', 'fb_3.png', 'fb_4.png', 'fb_5.png', 'fb_6.png', 'fb_7.png');
                            if( !in_array($iconName,$fb_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($fb_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                        case 'twitter':
                            $tw_icons = array('tw_1.png', 'tw_2.png', 'tw_3.png', 'tw_4.png', 'tw_5.png', 'tw_6.png', 'tw_7.png');
                            if( !in_array($iconName,$tw_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($tw_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                        case 'linkedin':
                            $li_icons = array('li_1.png', 'li_2.png', 'li_3.png', 'li_4.png', 'li_5.png', 'li_6.png', 'li_7.png');
                            if( !in_array($iconName,$li_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($li_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                        case 'google':
                            $google_icons = array('google_1.png', 'google_2.png', 'google_3.png', 'google_4.png', 'google_5.png', 'google_6.png', 'google_7.png');
                            if( !in_array($iconName,$google_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($google_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                        case 'vk':
                            $vk_icons = array('vk_1.png', 'vk_2.png', 'vk_3.png', 'vk_4.png', 'vk_5.png', 'vk_6.png', 'vk_7.png');
                            if( !in_array($iconName,$vk_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($vk_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                        case 'insta':
                            $insta_icons = array('insta_1.png', 'insta_2.png', 'insta_3.png', 'insta_4.png', 'insta_5.png', 'insta_6.png', 'insta_7.png');
                            if( !in_array($iconName,$insta_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($insta_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;
                         case 'tumblr':
                          $tu_icons = array('tu_1.png', 'tu_2.png', 'tu_3.png', 'tu_4.png', 'tu_5.png', 'tu_6.png', 'tu_7.png');
                            if( !in_array($iconName,$tu_icons) ){
                                $is_custom_icon = true;
                            }
                            foreach ($tu_icons as $icon) {
                                if (file_exists(MEMBERSHIP_IMAGES_DIR . '/social_icons/' . $icon)) {
                                    $networkIcons[$icon] = MEMBERSHIP_IMAGES_URL . '/social_icons/' . $icon;
                                }
                            }
                            break;    
                        default:
                            break;
                    }
                    /* Query Monitor Change */

                    if( true == $is_custom_icon ){
                        $networkCustomIcons = $this->arm_get_social_network_custom_icons($type);
                    } else {
                        $networkCustomIcons = array();
                    }
                    
                    $networkIcons = array_merge($networkIcons, $networkCustomIcons);
                }
                return $networkIcons;
            }
        }

        function arm_get_social_network_custom_icons($type = '') {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms;
            $networkIcons = array();
            if (!empty($type)) {
                $networkIcons = get_option('arm_social_icons_' . $type, array());
                $networkIcons = maybe_unserialize($networkIcons);
                if (!empty($networkIcons)) {
                    $isDeleted = false;
                    foreach ($networkIcons as $icon => $url) {
                        if (!file_exists(MEMBERSHIP_UPLOAD_DIR . '/social_icon/' . basename($url))) {
                            unset($networkIcons[$icon]);
                            $isDeleted = true;
                        }
                    }
                    if ($isDeleted) {
                        update_option('arm_social_icons_' . $type, $networkIcons);
                    }
                }
            }
            return $networkIcons;
        }

        function arm_get_user_id_by_meta($meta_key = '', $meta_value = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms;
            $user_id = 0;
            if (!empty($meta_key) && !empty($meta_value)) {
                $user_id = $wpdb->get_var("SELECT `user_id` FROM `$wpdb->usermeta` WHERE `meta_key`='$meta_key' AND `meta_value`='$meta_value'");
            }
            return $user_id;
        }

        function arm_social_login_shortcode_func($atts, $content, $tag) {
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            $defaults = array(
                'redirect_to' => ARM_HOME_URL,
                'network' => '',
                'icon' => '',
                'form_network_options' => '',
            );


            $args = shortcode_atts($defaults, $atts, $tag);
            extract($args);

            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings;
            if (is_user_logged_in()) {
                return '';
            }
            $args['network'] = (isset($args['network']) && !empty($args['network'])) ? preg_replace('/\s+/', '', $args['network']) : 'all';
            $args['form_network_options'] = (!empty($args['form_network_options'])) ? stripslashes_deep($args['form_network_options']) : '';
            $args['icon'] = (isset($args['icon']) && !empty($args['icon'])) ? $args['icon'] : '';
            $displayNewwork = explode(',', $args['network']);
            $social_settings = $this->arm_get_social_settings();
            $social_options = $this->arm_get_active_social_options();
            
            $enable_one_click_signup = ( isset($social_settings['options']['arm_one_click_social_signup']) && $social_settings['options']['arm_one_click_social_signup'] == 1 ) ? true : false;
            $content_js = '';
            do_action('arm_before_render_form', 0, $atts);
            if (!empty($social_options)) {
                $formSNOptions = maybe_unserialize($args['form_network_options']);
                $new_social_options = array();
                if (!empty($displayNewwork) && !in_array('all', $displayNewwork)) {
                    foreach ($displayNewwork as $dsnk) {
                        if (in_array($dsnk, array_keys($social_options))) {
                            $new_social_options[$dsnk] = $social_options[$dsnk];
                            if (isset($formSNOptions[$dsnk])) {
                                if (isset($formSNOptions[$dsnk]['icon'])) {
                                    $new_social_options[$dsnk]['icon'] = $formSNOptions[$dsnk]['icon'];
                                }
                            }
                        }
                    }
                    $social_options = $new_social_options;
                }
                $content = apply_filters('arm_before_social_login_shortcode_content', $content, $args);
                $content .= "<div class='arm_social_login_content_wrapper'>";
                $content .= "<div class='arm_social_login_main_container'>";
                if (!empty($social_options)) {
                    foreach ($social_options as $sk => $so) {
                        if (!is_array($so)) {
                            continue;
                        }
                        $a_tag_attr = '';
                        /* Query Monitor - Pass extra argument */
                        $icons = $this->arm_get_social_network_icons($sk,$icon);
                        if (!empty($displayNewwork) && !in_array('all', $displayNewwork) && count($displayNewwork) == 1) {
                            if (!empty($args['icon'])) {
                                $so['icon'] = basename($args['icon']);
                                $icons[$so['icon']] = $args['icon'];
                            }
                        }
                        if (isset($icons[$so['icon']])) {
                            if (file_exists(strstr($icons[$so['icon']], "//"))) {
                                $icons[$so['icon']] = strstr($icons[$so['icon']], "//");
                            } else if (file_exists($icons[$so['icon']])) {
                                $icons[$so['icon']] = $icons[$so['icon']];
                            } else {
                                $icons[$so['icon']] = $icons[$so['icon']];
                            }
                            $icon_img = '<img src="' . ($icons[$so['icon']]) . '" alt="' . $so['label'] . '" class="arm_social_login_custom_image">';
                        } else {
                            $icon = array_slice($icons, 0, 1);
                            $icon_url = array_shift($icon);
                            if (file_exists(strstr($icon_url, "//"))) {
                                $icon_url = strstr($icon_url, "//");
                            } else if (file_exists($icon_url)) {
                                $icon_url = $icon_url;
                            } else {
                                $icon_url = $icon_url;
                            }
                            $icon_img = '<img src="' . ($icon_url) . '" alt="' . $so['label'] . '" class="arm_social_login_custom_image">';
                        }
                        $content .= '<div class="arm_social_link_container arm_social_'.$sk.'_container" id="arm_social_'.$sk.'_container">';
                        $redirect_to = isset($redirect_to) ? $redirect_to : '';


                        $content .= '<input type="hidden" data-id="arm_social_login_redirect_to" value="' . $redirect_to . '">';

                        $link_class = 'arm_social_link_' . $sk;
                        $link_id = '';
                        switch ($sk) {
                            case 'facebook':
                                $content_js .= "jQuery(document).ready(function () {FacebookInit('" . $so['app_id'] . "');});";
                                $a_tag_attr = ' href="javascript:void(0)" onclick="FacebookLoginInit();" title="' . __('Login With Facebook', 'ARMember') . '" ';
                                break;
                            case 'linkedin':
                                $authUrl = $arm_global_settings->add_query_arg('action', 'arm_login_with_linkedin', ARM_HOME_URL);
                                $arm_linkedin_client_id = $so['client_id'];
                                
                                $a_tag_attr = ' href="javascript:void(0)" onclick="LinkedInLoginInit();" title="' . __('Login With LinkedIn', 'ARMember') . '" ';
                                
                                $content .= '<input type="hidden" data-id="arm_social_linkedin_client_id" value="' .
                                 $arm_linkedin_client_id . '">';
                                 $content .= '<input type="hidden" id="arm_social_linkedin_access_token" value="">';
                                $content .= '<input type="hidden" data-id="arm_social_linkedin_login_auth_url" value="' . $authUrl . '">';
                                break;
                            case 'twitter':
                                $authUrl = get_the_permalink();
                                $authUrl = $arm_global_settings->add_query_arg('redirect_to', ARM_HOME_URL, $authUrl);
                                $authUrl = $arm_global_settings->add_query_arg('page', 'arm_login_with_twitter', $authUrl);
                                $a_tag_attr = ' href="#" data-url="' . $authUrl . '" title="' . __('Login With Twitter', 'ARMember') . '" ';
                                break;
                            case 'pinterest':
                                $content_js .= "jQuery(document).ready(function () {PinterestInit('" . $so['app_id'] . "');});";
                                $a_tag_attr = ' href="javascript:void(0)" onclick="PinterestLoginInit();" title="' . __('Login With Pinterest+', 'ARMember') . '"';
                                break;
                            case 'vk':
                                $content .= '<input type="hidden" name="arm_vk_user_data" id="arm_vk_user_data" value="" />';
                                $content_js .= "
                                function VKAuthRequest() {
                                        var domain = window.location.hostname;
                                        var client_id = '" . $so['app_id'] . "';
                                        var client_secret = '" . $so['app_secret'] . "';
                                        var site_redirect_url = '" . MEMBERSHIP_VIEWS_URL . "/callback/vk_callback.php';
                                        var redirect_url = 'https://oauth.vk.com/authorize?client_id='+client_id+'&scope=email&response_type=code&redirect_uri='+site_redirect_url;
                                        vk_auth = window.open(redirect_url, '', 'width=800,height=300,scrollbars=yes');
                                        redirect_uri = '';
                                        setCookie('arm_vk_client_id', client_id, '/', domain, false, vk_auth.document);
                                        setCookie('arm_vk_client_secret', client_secret, '/', domain, false, vk_auth.document);
                                        setCookie('arm_vk_redirect_uri', site_redirect_url, '/', domain, false, vk_auth.document);
                                        var interval = setInterval(function() {            
                                            if (vk_auth.closed) {
                                                clearInterval(interval);
                                                /* if user close the popup than do stuff here */
                                                return; 
                                            }
                                        }, 500);
                                    }";
                                $content_js .= '';
                                $a_tag_attr = ' href="javascript:void(0)" id="arm_social_link_vk" onclick="VKAuthRequest();" title="' . __('Login With vkontakte', 'ARMember') . '" ';
                                break;
                            case 'insta':
                                require_once (MEMBERSHIP_LIBRARY_DIR . '/instagram/src/Client.php');
                                $INSTAGRAM_GET_TYPE = 'iType';
                                $INSTAGRAM_GET_RESPONSE = 'iResponse';
                                $INSTAGRAM_CLIENT_ID = $so["client_id"];
                                $INSTAGRAM_CLIENT_SECRET = $so["client_secret"];
                                $API_CONFIG_callbackUrl = MEMBERSHIP_VIEWS_URL . "/callback/insta_callback.php";
                                $OBJ_INSTAGRAM1 = new Andreyco\Instagram\Client(array (
                                    'apiKey'      => $INSTAGRAM_CLIENT_ID,
                                    'apiSecret'   => $INSTAGRAM_CLIENT_SECRET,
                                    'apiCallback' => $API_CONFIG_callbackUrl,
                                    'scope'      => array('basic'),
                                ));
                                $state = md5(time());
                                $INSTAGRAM_URL_AUTH = $OBJ_INSTAGRAM1->getLoginUrl(array('basic'), $state);
                                $content .= '<input type="hidden" name="arm_insta_user_data" id="arm_insta_user_data" value="" />';
                                $content_js .= "
                                function InstagramLoginInit() {
                                    var domain = window.location.hostname;
                                    var client_id = '" . $INSTAGRAM_CLIENT_ID . "';
                                    var client_secret = '" . $INSTAGRAM_CLIENT_SECRET . "';
                                    var site_redirect_url = '". $API_CONFIG_callbackUrl ."';
                                    insta_auth = window.open('".$INSTAGRAM_URL_AUTH."','', 'width=500,height=350');
                                    setCookie('arm_insta_client_id', client_id, '/', domain, false, insta_auth.document);
                                    setCookie('arm_insta_client_secret', client_secret, '/', domain, false, insta_auth.document);
                                    setCookie('arm_insta_redirect_uri', site_redirect_url, '/', domain, false, insta_auth.document);
                                }";
                                $a_tag_attr = ' href="javascript:void(0)" onclick="InstagramLoginInit();" title="' . __('Login With Instagram', 'ARMember') . '" ';
                                break;
                                case 'google':
                                $google_random_code_id = arm_generate_random_code();
                                $link_id = "arm_google_signin_btn_".$google_random_code_id;
                                $content_js .= 'jQuery(document).ready(function () { gapi.load("auth2", function(){ auth2 = gapi.auth2.init({ client_id: "'.$so['client_id'].'", cookiepolicy: "single_host_origin",}); ARMGoogleSignInCallBack(document.getElementById("'.$link_id.'")); }); });';
                                $a_tag_attr = ' href="javascript:void(0)" title="' . __('Login With Google', 'ARMember') . '" ';
                                global $arm_version;
                                wp_enqueue_script('arm_google_social_login_script','https://apis.google.com/js/api:client.js',array(),$arm_version);

                                break;
                                case 'tumblr':
                                $authUrl = get_the_permalink();
                                $authUrl = $arm_global_settings->add_query_arg('redirect_to', ARM_HOME_URL, $authUrl);
                                $authUrl = $arm_global_settings->add_query_arg('page', 'arm_login_with_tumblr', $authUrl);
                                $a_tag_attr = ' href="#" data-url="' . $authUrl . '" title="' . __('Login With Tumblr', 'ARMember') . '" ';
                                break;
                            default:
                                break;
                        }
                        $content_link_id = '';
                        if(!empty($link_id))
                        {
                            $content_link_id = ' id="'.$link_id.'" ';
                        }
                        $content .= '<a '.$content_link_id.'class="arm_social_link ' . $link_class . ' " data-type="' . $sk . '" ' . $a_tag_attr . '>';
                        $content .= (!empty($icon_img)) ? $icon_img : $so['label'];
                        $content .= '</a>';
                        $content .= '</div>';
                    }
                }
                if (!empty($content_js)) {
                    $content .= '<script data-cfasync="false" type="text/javascript">' . $content_js . '</script>';
                }
                $content .= "</div>";
                $content .= "<div class='arm_social_connect_loader' id='arm_social_connect_loader' style='display:none;'>";
                $content .= '<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="30px" height="30px" viewBox="0 0 128 128" xml:space="preserve"><g><linearGradient id="linear-gradient"><stop offset="0%" stop-color="#ffffff"/><stop offset="100%" class="arm_social_connect_svg" /></linearGradient><path d="M63.85 0A63.85 63.85 0 1 1 0 63.85 63.85 63.85 0 0 1 63.85 0zm.65 19.5a44 44 0 1 1-44 44 44 44 0 0 1 44-44z" fill="url(#linear-gradient)" fill-rule="evenodd"/><animateTransform attributeName="transform" type="rotate" from="0 64 64" to="360 64 64" dur="1080ms" repeatCount="indefinite"></animateTransform></g></svg>';
                $content .= "</div>";
                $content .= "</div>";
                $content = apply_filters('arm_after_social_login_shortcode_content', $content, $args);
            }
            $ARMember->arm_check_font_awesome_icons($content);
            return do_shortcode($content);
        }

        function arm_social_login_callback($posted_data = array()) {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms, $arm_case_types, $wp_filesystem, $arm_subscription_plans;

            $social_settings = $this->arm_get_social_settings();
            $social_options = $this->arm_get_active_social_options();
            $posted_data = (!empty($posted_data)) ? $posted_data : $_POST;
            $fail_msg = (!empty($arm_global_settings->common_message['social_login_failed_msg'])) ? $arm_global_settings->common_message['social_login_failed_msg'] : __('Login Failed, please try again.', 'ARMember');
            $fail_msg = (!empty($fail_msg)) ? $fail_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
            $return = array('status' => 'error', 'message' => $fail_msg);
            if (!empty($posted_data) && $posted_data['action'] == 'arm_social_login_callback') {
                $posted_data = apply_filters('arm_social_login_callback_detail', $posted_data);
                $action_type = $posted_data['action_type'];
                do_action('arm_before_social_login_callback', $posted_data);
                if (!empty($action_type)) {
                    do_action('arm_before_social_login_callback_' . $action_type, $posted_data);

                    $user_login = (!empty($posted_data['user_login'])) ? sanitize_user($posted_data['user_login']) : (isset($posted_data['user_email']) ? sanitize_email($posted_data['user_email']) : '');
                    $social_id = $posted_data['id'];
                    $user_data = array(
                        'user_login' => $user_login,
                        'user_email' => isset($posted_data['user_email']) ? sanitize_email($posted_data['user_email']) : '',
                        'first_name' => isset($posted_data['first_name']) ? sanitize_text_field($posted_data['first_name']) : '',
                        'last_name' => isset($posted_data['last_name']) ? sanitize_text_field($posted_data['last_name']) : '',
                        'display_name' => isset($posted_data['display_name']) ? sanitize_text_field($posted_data['display_name']) : '',
                        'birthday' => isset($posted_data['birthday']) ? sanitize_text_field($posted_data['birthday']) : '',
                        'gender' => isset($posted_data['gender']) ? sanitize_text_field($posted_data['gender']) : '',
                        'arm_' . $action_type . '_id' => $social_id,
                        'picture' => isset($posted_data['picture']) ? sanitize_text_field($posted_data['picture']) : '',
                        'user_profile_picture' => isset($posted_data['user_profile_picture']) ? sanitize_text_field($posted_data['user_profile_picture']) : '',
                        'userId' => isset($posted_data['userId']) ? $posted_data['userId'] : '',
                    );
                    $redirect_to = (isset($posted_data['redirect_to']) && $posted_data['redirect_to'] != '' ) ? $posted_data['redirect_to'] : ARM_HOME_URL;
                    if (!empty($posted_data['picture'])) {
                        $user_data[$action_type . '_picture'] = $posted_data['picture'];
                    }
                    $user_data = apply_filters('arm_change_user_social_detail_before_login', $user_data, $action_type);
                    $user_id = $this->arm_social_login_process($user_data, $action_type);
                    if (!empty($user_id) && $user_id != 0) {

                        $arm_default_redirection_settings = get_option('arm_redirection_settings');
                        $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                        $login_redirection_rules_options = $arm_default_redirection_settings['social'];

                        if ($login_redirection_rules_options['type'] == 'page') {

                            $form_redirect_id = (!empty($login_redirection_rules_options['page_id'])) ? $login_redirection_rules_options['page_id'] : '0';
                            if ($form_redirect_id == 0) {
                                $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                                $page_settings = $all_global_settings['page_settings'];
                                $form_redirect_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
                            }
                            $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                        } else {
                            $redirect_to = (!empty($login_redirection_rules_options['url'])) ? $login_redirection_rules_options['url'] : ARM_HOME_URL;
                        }
                        $user_info = get_userdata($user_id);
                        $username = $user_info->user_login;

                        $redirect_to = str_replace('{ARMCURRENTUSERNAME}', $username, $redirect_to);
                        $redirect_to = str_replace('{ARMCURRENTUSERID}', $user_id, $redirect_to);
                        wp_set_auth_cookie($user_id);
                        $current_user = $this->arm_set_current_member($user_id);
                        $return = array('status' => 'success', 'type' => 'redirect', 'message' => $redirect_to);
                    } else {
                        /* Redirect User To Registration Page. */
                        $redirect_opt = $social_settings['registration'];

                        $social_setting_options = isset($social_settings['options']) ? $social_settings['options'] : array();

                        if (empty($social_setting_options)) {
                            $one_click_signup = 0;
                        } else {
                            $one_click_signup = isset($social_setting_options['arm_one_click_social_signup']) ? $social_setting_options['arm_one_click_social_signup'] : 0;
                        }
                        if ($one_click_signup == 1 && $user_data['user_email'] != '') {

                            $reg_form = NULL;

                            if (!empty($posted_data['user_profile_picture'])) {

                                if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {

                                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                                    $random_no = rand();
                                    $file = MEMBERSHIP_UPLOAD_DIR . '/arm_' . $action_type . '_' . $random_no . '.jpg';
                                    if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                                        if (false === ($creds = request_filesystem_credentials($file, '', false, false) )) {
                                            return true;
                                        }
                                        if (!WP_Filesystem($creds)) {
                                            request_filesystem_credentials($file, $method, true, false);
                                            return true;
                                        }
                                    }
                                    $arm_social_avtar_option = isset($social_settings['options']['social_avatar']) ? $social_settings['options']['social_avatar'] : 0;
                                    if (ini_get('allow_url_fopen') && $arm_social_avtar_option == 1) {

                                        @$img = $wp_filesystem->get_contents($user_data['user_profile_picture']);
                                        @$write_file = $wp_filesystem->put_contents($file, $img, FS_CHMOD_FILE);
                                        $avtar_url = MEMBERSHIP_UPLOAD_URL . '/arm_' . $action_type . '_' . $random_no . '.jpg';
                                        $user_data[$action_type . '_picture'] = $avtar_url;
                                        $user_data['avatar'] = $avtar_url;
                                    }
                                }
                            }

                            if (isset($social_setting_options['assign_default_plan']) && $arm_subscription_plans->isFreePlanExist($social_setting_options['assign_default_plan'])) {
                                $user_data['subscription_plan'] = $social_setting_options['assign_default_plan'];
                            }

                            $user_id = $arm_member_forms->arm_register_new_member($user_data, $reg_form, 'social_signup');
                            if (is_numeric($user_id) && !is_array($user_id)) {
                                wp_set_auth_cookie($user_id);
                                $this->arm_set_current_member($user_id, $user_login);
                                update_user_meta($user_id, 'arm_last_login_date', date('Y-m-d H:i:s'));
                                $ip_address = $ARMember->arm_get_ip_address();
                                update_user_meta($user_id, 'arm_last_login_ip', $ip_address);
                                $user_to_pass = wp_get_current_user();
                                //$userObj = new WP_User($user_id);
                                $arm_login_from_registration = 1;
                                do_action('wp_login', $user_login, $user_to_pass);

                                $arm_default_redirection_settings = get_option('arm_redirection_settings');
                                $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                                $login_redirection_rules_options = $arm_default_redirection_settings['social'];

                                if ($login_redirection_rules_options['type'] == 'page') {

                                    $form_redirect_id = (!empty($login_redirection_rules_options['page_id'])) ? $login_redirection_rules_options['page_id'] : '0';
                                    if ($form_redirect_id == 0) {
                                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                                        $page_settings = $all_global_settings['page_settings'];
                                        $form_redirect_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
                                    }
                                    $redirect_to = $arm_global_settings->arm_get_permalink('', $form_redirect_id);
                                } else {
                                    $redirect_to = (!empty($login_redirection_rules_options['url'])) ? $login_redirection_rules_options['url'] : ARM_HOME_URL;
                                }
                                $user_info = get_userdata($user_id);
                                $username = $user_info->user_login;

                                $redirect_to = str_replace('{ARMCURRENTUSERNAME}', $username, $redirect_to);
                                $redirect_to = str_replace('{ARMCURRENTUSERID}', $user_id, $redirect_to);

                                $return = array('status' => 'success', 'type' => 'redirect', 'message' => $redirect_to);
                            }
                        } else {
                            if (!empty($redirect_opt)) {
                                if (!empty($redirect_opt['form_page'])) {
                                    $redirect_url = get_permalink($redirect_opt['form_page']);
                                    $social_reg_form = $redirect_opt['form'];
                                    $reg_form = new ARM_Form('id', $social_reg_form);
                                    $query_string = "";
                                    if ($reg_form->exists() && !empty($reg_form->fields)) {
                                        if (!empty($fieldValue)) {
                                            $redirect_url = $arm_global_settings->add_query_arg($fieldMeta, $fieldValue, $redirect_url);
                                        }
                                        foreach ($reg_form->fields as $regfield) {
                                            $fieldId = $regfield['arm_form_field_id'];
                                            $fieldMeta = isset($regfield['arm_form_field_option']['meta_key']) ? $regfield['arm_form_field_option']['meta_key'] : '';
                                            if ($fieldMeta == 'first_name') {
                                                if (isset($regfield['arm_form_field_option']['hide_firstname'])) {
                                                    if ($regfield['arm_form_field_option']['hide_firstname'] == 1) {
                                                        continue;
                                                    }
                                                }
                                            } else if ($fieldMeta == 'last_name') {
                                                if (isset($regfield['arm_form_field_option']['hide_lastname'])) {
                                                    if ($regfield['arm_form_field_option']['hide_lastname'] == 1) {
                                                        continue;
                                                    }
                                                }
                                            } else if ($fieldMeta == 'user_login') {
                                                if (isset($regfield['arm_form_field_option']['hide_username'])) {
                                                    if ($regfield['arm_form_field_option']['hide_username'] == 1) {
                                                        continue;
                                                    }
                                                }
                                            }
                                            $fieldValue = '';
                                            if (isset($posted_data[$fieldMeta]) && !empty($posted_data[$fieldMeta])) {
                                                $fieldValue = $posted_data[$fieldMeta];
                                                $redirect_url = $arm_global_settings->add_query_arg($fieldMeta, $fieldValue, $redirect_url);
                                            }
                                        }
                                    }
                                    /* Add Social User Info In URL */
                                    $redirect_url = $arm_global_settings->add_query_arg('arm_' . $action_type . '_id', $social_id, $redirect_url);
                                    $redirect_url = $arm_global_settings->add_query_arg('social_form', $social_reg_form, $redirect_url);

                                    if( !empty($user_data["display_name"]) && $action_type == "insta" ||  $action_type == "tumblr"  ) {
                                        $redirect_url = $arm_global_settings->add_query_arg('display_name', $user_data["display_name"], $redirect_url);
                                    }
                                    if (!empty($posted_data['user_profile_picture'])) {


                                        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {

                                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                                            $random_no = rand();
                                            $file = MEMBERSHIP_UPLOAD_DIR . '/arm_' . $action_type . '_' . $random_no . '.jpg';
                                            if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                                if (false === ($creds = request_filesystem_credentials($file, '', false, false) )) {
                                                    return true;
                                                }
                                                if (!WP_Filesystem($creds)) {
                                                    request_filesystem_credentials($file, $method, true, false);
                                                    return true;
                                                }
                                            }
                                            $arm_social_avtar_option = isset($social_settings['options']['social_avatar']) ? $social_settings['options']['social_avatar'] : 0;
                                            if (ini_get('allow_url_fopen') && $arm_social_avtar_option == 1) {

                                                @$img = $wp_filesystem->get_contents($user_data['user_profile_picture']);
                                                @$write_file = $wp_filesystem->put_contents($file, $img, FS_CHMOD_FILE);
                                                $avtar_url = MEMBERSHIP_UPLOAD_URL . '/arm_' . $action_type . '_' . $random_no . '.jpg';
                                                $redirect_url = $arm_global_settings->add_query_arg($action_type . '_picture', $avtar_url, $redirect_url);
                                                $redirect_url = $arm_global_settings->add_query_arg('avatar', $avtar_url, $redirect_url);
                                            }
                                        }
                                    }
                                    $return = array('status' => 'success', 'type' => 'redirect', 'message' => $redirect_url);
                                }
                            }
                        }
                    }
                    /* Return Responce For Twitter & Tumblr Login. */
                    if ($action_type == 'twitter' ||  $action_type == 'tumblr' ) {
                        return $return;
                    }
                }
                do_action('arm_after_social_login_callback', $posted_data);
            } else {
                if (MEMBERSHIP_DEBUG_LOG == true) {
                    $arm_case_types['shortcode']['protected'] = true;
                    $arm_case_types['shortcode']['type'] = 'login_via_social_button';
                    $arm_case_types['shortcode']['message'] = __('Couldn\'t login with social network', 'ARMember');
                    $ARMember->arm_debug_response_log('arm_twitter_login_callback', $arm_case_types, $posted_data, $wpdb->last_query, false);
                }
            }
            echo json_encode($return);
            exit;
        }

        function arm_social_login_process($login_data = array(), $action_type = '') {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms;
            $user_id = 0;
            if (!empty($login_data)) {
                $social_key = 'arm_' . $action_type . '_id';
                $user_id = $this->arm_get_user_id_by_meta($social_key, $login_data[$social_key]);
                if (empty($user_id) || $user_id == 0) {
                    $email = $login_data['user_email'];
                    $user = get_user_by('email', $email);
                    if (!empty($user)) {
                        $user_id = $user->ID;
                        update_user_meta($user_id, $social_key, $login_data[$social_key]);
                    }
                }
            }
            return $user_id;
        }

        function arm_login_with_twitter() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_social_feature;
            if (isset($_GET['page']) && in_array($_GET['page'], array('arm_login_with_twitter')) && !empty($arm_social_feature->isSocialFeature) ) {
                $ARMember->arm_session_start();
                $social_options = $this->arm_get_active_social_options();
                $customer_key = $social_options['twitter']['customer_key'];
                $customer_secret = $social_options['twitter']['customer_secret'];
                require_once (MEMBERSHIP_LIBRARY_DIR . '/twitter/twitteroauth.php');
                $Twitter = new TwitterOAuth($customer_key, $customer_secret);
                $redirect_to = $_GET['redirect_to'];
                $CALLBACK_URL = $arm_global_settings->add_query_arg('page', 'arm_twitter_return', rtrim($redirect_to, '/') . '/');
                $request_token = $Twitter->getRequestToken($CALLBACK_URL);
                /* Saving them into the session */
                $request_token['oauth_token'] = isset($request_token['oauth_token']) ? $request_token['oauth_token'] : '';
                $request_token['oauth_token_secret'] = isset($request_token['oauth_token_secret']) ? $request_token['oauth_token_secret'] : '';
                $_SESSION['arm_tw_oauth_token'] = $request_token['oauth_token'];
                $_SESSION['arm_tw_oauth_token_secret'] = $request_token['oauth_token_secret'];
                $auth_url = $Twitter->getAuthorizeURL($request_token['oauth_token']);
                wp_redirect($auth_url);
                die();
            }
        }

        function arm_twitter_login_callback() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms, $arm_case_types, $arm_social_feature;
            $posted_data = $_POST;
            $slc_return = array();
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], array('arm_twitter_return')) && !empty($arm_social_feature->isSocialFeature)) {
                $ARMember->arm_session_start();
                $post_data = array();
                $social_options = $this->arm_get_active_social_options();
                $tw_conf = $social_options['twitter'];
                require_once (MEMBERSHIP_LIBRARY_DIR . '/twitter/twitteroauth.php');
                $_SESSION['arm_tw_oauth_token'] = isset($_SESSION['arm_tw_oauth_token']) ? $_SESSION['arm_tw_oauth_token'] : '-';
                $_SESSION['arm_tw_oauth_token_secret'] = isset($_SESSION['arm_tw_oauth_token_secret']) ? $_SESSION['arm_tw_oauth_token_secret'] : '-';
                $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : '';
                $twitteroauth = new TwitterOAuth($tw_conf['customer_key'], $tw_conf['customer_secret'], $_SESSION['arm_tw_oauth_token'], $_SESSION['arm_tw_oauth_token_secret']);
                /* Let's request the access token */
                $access_token = $twitteroauth->getAccessToken($oauth_verifier);
                /* Save it in a session var */
                $_SESSION['access_token'] = $access_token;
                /* Let's get the user's info */
                $params = array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true');
                $user_info = $twitteroauth->get('account/verify_credentials', $params);
                
                if (isset($user_info->error) || !isset($user_info->id) || empty($oauth_verifier)) {
                    if (MEMBERSHIP_DEBUG_LOG == true) {
                        $arm_case_types['shortcode']['protected'] = true;
                        $arm_case_types['shortcode']['type'] = 'login_via_twitter';
                        $arm_case_types['shortcode']['message'] = __('Couldn\'t login with twitter', 'ARMember');
                        $ARMember->arm_debug_response_log('arm_twitter_login_callback', $arm_case_types, $user_info, $wpdb->last_query, false);
                    }
                    echo "<script data-cfasync='false'>alert('" . __('There is an error while connecting twitter, Please try again.', 'ARMember') . "');window.close();</script>";
                    exit;
                } else {
                    $full_name = explode(' ', $user_info->name);
                    $user_info->id = (isset($user_info->id) ? $user_info->id : '');
                    $post_data = array(
                        'action' => 'arm_social_login_callback',
                        'action_type' => 'twitter',
                        'id' => $user_info->id,
                        'user_login' => (isset($user_info->screen_name) ? $user_info->screen_name : ''),
                        'user_email' => (isset($user_info->email) ? $user_info->email : ''),
                        'first_name' => $full_name[0],
                        'last_name' => (isset($full_name[1]) ? $full_name[1] : ''),
                        'display_name' => (isset($user_info->name) ? $user_info->name : ''),
                        'oauth_verifier' => $oauth_verifier,
                    );
                    $post_data['picture'] = $user_info->profile_image_url;
                    $post_data['user_profile_picture'] = $user_info->profile_image_url;
                    $user_id = $this->arm_get_user_id_by_meta('arm_twitter_id', $user_info->id);
                    if (!empty($user_id) && $user_id != 0) {
                        $user_detail = new WP_User($user_id);
                        $post_data['user_email'] = $user_detail->user_email;
                    } else {
                        /* Needs to create new user info */
                    }
                    /* Send User Data to Social Process Function. */
                    $slc_return = $this->arm_social_login_callback($post_data);
                }
                /* Unset Session Details. */
                unset($_SESSION['customer_key']);
                unset($_SESSION['customer_secret']);
                unset($_SESSION['access_token']);
                if ($slc_return['status'] == 'success') {
                    if ($slc_return['type'] == 'redirect') {
                        $redirect_url = $slc_return['message'];
                    } else {
                        $redirect_url = ARM_HOME_URL;
                    }
                    echo "<script data-cfasync='false'>
                    window.opener.document.getElementById('arm_social_twitter_container').style.display = 'none';
                    window.opener.document.getElementById('arm_social_connect_loader').style.display = 'block';
                    window.opener.location.href='" . $redirect_url . "';window.close();
                    </script>";
                    exit;
                } else {
                    $fail_msg = (!empty($arm_global_settings->common_message['social_login_failed_msg'])) ? $arm_global_settings->common_message['social_login_failed_msg'] : __('Login Failed, please try again.', 'ARMember');
                    $fail_msg = (!empty($fail_msg)) ? $fail_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
                    echo "<script data-cfasync='false'>alert('" . $fail_msg . "');window.close();</script>";
                    exit;
                }
            }
            return;
        }

        function arm_login_with_linkedin() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_social_feature;
            
            if (isset($_GET['action']) && in_array($_GET['action'], array('arm_login_with_linkedin')) && !empty($arm_social_feature->isSocialFeature) ) {
                $social_options = $this->arm_get_active_social_options();
                $client_id = $social_options['linkedin']['client_id'];
                $client_secret = $social_options['linkedin']['client_secret'];
                $access_token = $_GET['code'];
                
                $access_token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
                $arm_redirect_url = $arm_global_settings->add_query_arg('action', $_GET['action'], ARM_HOME_URL);
                $api_params = array(
                    'grant_type' => 'authorization_code',
                    'code' => $access_token,
                    'redirect_uri' => $arm_redirect_url,
                    'client_id' => $client_id,
                    'client_secret' =>$client_secret
                );
                
                $arm_linkedin_auth_response = wp_remote_post($access_token_url,array(
                    'timeout' => 15,
                    'sslverify' => false,
                    'body' => $api_params)
                );
                
                if( !is_wp_error($arm_linkedin_auth_response) && isset($arm_linkedin_auth_response['response']) && $arm_linkedin_auth_response['response']['code'] == '200' && isset($arm_linkedin_auth_response['body']) ){
                    $response_body = json_decode($arm_linkedin_auth_response['body'],true);

                    $access_token = $response_body['access_token'];
                    $expire_time = $response_body['expires_in'];

                     echo "<script type='text/javascript'>
                            if( window.opener.document.getElementById('arm_social_linkedin_access_token') != null ){
                                window.opener.document.getElementById('arm_social_linkedin_access_token').value = '". $access_token ."';
                            } 
                            window.close();
                        </script>";
                    
                }
                else
                {
                    echo "<script data-cfasync='false'>alert('" . __('There is an error while connecting linkedin, Please try again.', 'ARMember') . "');window.close();</script>";
                }
                die();
            }
        }

        function arm_linkedin_login_callback() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms, $arm_case_types, $arm_social_feature;
            if( !empty($arm_social_feature->isSocialFeature) )
            {
                $posted_data = $_POST;
                $access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token'] : '';
                $arm_linkedin_profile_response = array();
                $param = array(
                    'oauth2_access_token' => $access_token
                );

                $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
                $raw_response = wp_remote_get($url,array(
                    'timeout' => 15,
                    'sslverify' => false,
                    'body' => $param,
                    'headers' => array(
                        'X-Restli-Protocol-Version' => '2.0.0'
                    ),
                ));

                $arm_linkedin_profile_response['email_response'] = json_decode($raw_response['body']);
                
                $arm_linkedin_get_url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';

                $arm_linkedin_profile_resp = wp_remote_get($arm_linkedin_get_url,array(
                    'timeout' => 15,
                    'sslverify' => false,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$access_token,
                        'X-RestLi-Protocol-Version' =>'2.0.0'
                    ),
                ));

                
                if( !is_wp_error($arm_linkedin_profile_resp) && isset($arm_linkedin_profile_resp['response']) && $arm_linkedin_profile_resp['response']['code'] == '200' ){

                    $arm_linkedin_profile_response['profile_response'] = json_decode($arm_linkedin_profile_resp['body']);
                    echo json_encode($arm_linkedin_profile_response);
                    
                } else {
                    echo json_encode(array('error'=> true));
                }
            }
            die;
        }
        function arm_login_with_tumblr() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_social_feature;
                
            if (isset($_GET['page']) && in_array($_GET['page'], array('arm_login_with_tumblr')) && !empty($arm_social_feature->isSocialFeature) ) {

                $ARMember->arm_session_start();
                $social_options = $this->arm_get_active_social_options();
                $consumer_key = $social_options['tumblr']['consumer_key'];
                $consumer_secret = $social_options['tumblr']['consumer_secret'];
                require_once (MEMBERSHIP_LIBRARY_DIR . '/tumblr/tumblroauth.php');
                $Tumblr = new TumblrOAuth($consumer_key, $consumer_secret);
                $redirect_to = $_GET['redirect_to'];
                $CALLBACK_URL = $arm_global_settings->add_query_arg('page', 'arm_tumblr_return', rtrim($redirect_to, '/') 
                    . '/'); 
                $request_token = $Tumblr->Request_Token($CALLBACK_URL);                
               /* Saving them into the session */
                $request_token['oauth_token'] = isset($request_token['oauth_token']) ? $request_token['oauth_token'] : '';
                $request_token['oauth_token_secret'] = isset($request_token['oauth_token_secret']) ? $request_token['oauth_token_secret'] : '';
                $_SESSION['oauth_token'] = $request_token['oauth_token'];
                $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
                $auth_url ='https://tumblr.com/oauth/authorize?oauth_token='.$request_token['oauth_token'];
                wp_redirect($auth_url);
                die();                
            }
        }
        
        function arm_tumblr_login_callback() {
            global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_member_forms, $arm_social_feature; 

            $posted_data = $_POST;
            $slc_return = array();
                        
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], array('arm_tumblr_return')) && !empty($arm_social_feature->isSocialFeature) ) {
                $ARMember->arm_session_start();                
                $post_data = array();
                $social_options = $this->arm_get_active_social_options();                
                $tu_conf = $social_options['tumblr'];
                require_once (MEMBERSHIP_LIBRARY_DIR . '/tumblr/tumblroauth.php');                
                $_SESSION['oauth_token'] = isset($_SESSION['oauth_token']) ? $_SESSION['oauth_token'] : ' ';
                $_SESSION['oauth_token_secret'] = isset($_SESSION['oauth_token_secret']) ? $_SESSION['oauth_token_secret'] : '';
                $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : '';                                 
                $tumblroauth = new TumblrOAuth($tu_conf['consumer_key'], $tu_conf['consumer_secret'] );
                /* Let's request the access token */                           
                $access_token = $tumblroauth->getAccessToken($_SESSION['oauth_token'],$_SESSION['oauth_token_secret'],$oauth_verifier);            
                /* Let's request the user information */
                if(isset($access_token['oauth_token']) && $access_token['oauth_token_secret']){             
                    $user_info = $tumblroauth->get($access_token['oauth_token'],$access_token['oauth_token_secret']);
                }else {                    
                    echo "<script data-cfasync='false'>alert('" . __('There is an error while connecting tumblr, Please try again.', 'ARMember') . "');window.close();</script>";
                }                
                if(isset($user_info->errors) && !isset($user_info->meta)) {
                    if (MEMBERSHIP_DEBUG_LOG == true) {
                        $arm_case_types['shortcode']['protected'] = true;
                        $arm_case_types['shortcode']['type'] = 'login_via_tumblr';
                        $arm_case_types['shortcode']['message'] = __('Couldn\'t login with tumblr', 'ARMember');
                        $ARMember->arm_debug_response_log('arm_tumblr_login_callback', $arm_case_types, $user_info, $wpdb->last_query, false);
                    }
                    echo "<script data-cfasync='false'>alert('" . __('There is an error while connecting tumblr, Please try again.', 'ARMember') . "');window.close();</script>";
                
                } elseif(isset($user_info->meta) && $user_info->meta->status == 200) {
                    
                    $user_response = $user_info->response;
                    $full_name = isset($user_response->user->name) ? $user_response->user->name : '';                            
                    $user_id = isset($user_response->user->blogs[0]->uuid) ? $user_response->user->blogs[0]->uuid : '';
                    $profile_url = isset($user_response->user->blogs[0]->theme->header_image) ? $user_response->user->blogs[0]->theme->header_image : '';
                    $post_data = array(
                        'action' => 'arm_social_login_callback',
                        'action_type' => 'tumblr',
                        'id' => $user_id,
                        'first_name' => $full_name,
                        'display_name' => $full_name,
                        'redirect_to' => ARM_HOME_URL,
                        'user_profile_picture'=> $profile_url,
                        'picture' => $profile_url,
                        'user_login' => $full_name,                        
                    );                                                                                                  
                    
                    $slc_return = $this->arm_social_login_callback($post_data);                                  
                }
                /* Unset Session Details. */                

                unset($_SESSION['oauth_token']);
                unset($_SESSION['oauth_token_secret']);

                if (isset($slc_return['status']) && $slc_return['status'] == 'success') {
                    if ($slc_return['type'] == 'redirect') {
                        $redirect_url = $slc_return['message'];
                    } else {
                        $redirect_url = ARM_HOME_URL;
                    }
                    echo "<script data-cfasync='false'>
                    window.opener.document.getElementById('arm_social_tumblr_container').style.display = 'none';
                    window.opener.document.getElementById('arm_social_connect_loader').style.display = 'block';
                    window.opener.location.href='" . $redirect_url . "';window.close();
                    </script>";
                    exit;
                } else {
                    $fail_msg = (!empty($arm_global_settings->common_message['social_login_failed_msg'])) ? $arm_global_settings->common_message['social_login_failed_msg'] : __('Login Failed, please try again.', 'ARMember');
                    $fail_msg = (!empty($fail_msg)) ? $fail_msg : __('Sorry, Something went wrong. Please try again.', 'ARMember');
                    echo "<script data-cfasync='false'>alert('" . $fail_msg . "');window.close();</script>";
                    exit;
                }
            }
            return; 
        }

        function get_rand_alphanumeric($length) {
            if ($length > 0) {
                $rand_id = "";
                for ($i = 1; $i <= $length; $i++) {
                    mt_srand((double) microtime() * 1000000);
                    $num = mt_rand(1, 36);
                    $rand_id .= $this->assign_rand_value($num);
                }
            }
            return $rand_id;
        }

        function assign_rand_value($num) {
            switch ($num) {
                case "1" : $rand_value = "a";
                    break;
                case "2" : $rand_value = "b";
                    break;
                case "3" : $rand_value = "c";
                    break;
                case "4" : $rand_value = "d";
                    break;
                case "5" : $rand_value = "e";
                    break;
                case "6" : $rand_value = "f";
                    break;
                case "7" : $rand_value = "g";
                    break;
                case "8" : $rand_value = "h";
                    break;
                case "9" : $rand_value = "i";
                    break;
                case "10" : $rand_value = "j";
                    break;
                case "11" : $rand_value = "k";
                    break;
                case "12" : $rand_value = "l";
                    break;
                case "13" : $rand_value = "m";
                    break;
                case "14" : $rand_value = "n";
                    break;
                case "15" : $rand_value = "o";
                    break;
                case "16" : $rand_value = "p";
                    break;
                case "17" : $rand_value = "q";
                    break;
                case "18" : $rand_value = "r";
                    break;
                case "19" : $rand_value = "s";
                    break;
                case "20" : $rand_value = "t";
                    break;
                case "21" : $rand_value = "u";
                    break;
                case "22" : $rand_value = "v";
                    break;
                case "23" : $rand_value = "w";
                    break;
                case "24" : $rand_value = "x";
                    break;
                case "25" : $rand_value = "y";
                    break;
                case "26" : $rand_value = "z";
                    break;
                case "27" : $rand_value = "0";
                    break;
                case "28" : $rand_value = "1";
                    break;
                case "29" : $rand_value = "2";
                    break;
                case "30" : $rand_value = "3";
                    break;
                case "31" : $rand_value = "4";
                    break;
                case "32" : $rand_value = "5";
                    break;
                case "33" : $rand_value = "6";
                    break;
                case "34" : $rand_value = "7";
                    break;
                case "35" : $rand_value = "8";
                    break;
                case "36" : $rand_value = "9";
                    break;
            }
            return $rand_value;
        }

        function CheckpluginStatus($mypluginsarray, $pluginname, $attr, $purchase_addon, $plugin_type, $install_url, $compatible_version, $armember_version, $is_config = false, $config_url = '') {
            foreach ($mypluginsarray as $pluginarr) {
                $response = "";
                if ($pluginname == $pluginarr[$attr]) {
                    if ($pluginarr['is_active'] == 1) {
                        $response = "ACTIVE";
                        $actionurl = $pluginarr["deactivation_url"];
                        break;
                    } else {
                        $response = "NOT ACTIVE";
                        $actionurl = $pluginarr["activation_url"];
                        break;
                    }
                } else {
                    if ($plugin_type == "free") {
                        $response = "NOT INSTALLED FREE";
                        $actionurl = $install_url;
                    } else if ($plugin_type == "paid") {
                        $response = "NOT INSTALLED PAID";
                        $actionurl = $install_url;
                    }
                }
            }
            $myicon = "";
            $divclassname = "";
            $arm_plugin_name = explode('/', $pluginname);
            if( isset( $is_config ) && true == $is_config ){
                $deactivate_btn_class = 'arm_feature_deactivate_btn';
                $config_button_html = '<a href="'.$config_url.'" class="arm_feature_configure_btn">Configure</a>';
            } else {
                $deactivate_btn_class = 'arm_feature_deactivate_btn arm_no_config_feature_btn';
                $config_button_html = '';
            }
            if ($response == "NOT INSTALLED FREE") {
                $myicon = '<div class="arm_feature_button_activate_wrapper arm_install_btn "><a id="arm_free_addon" href="javascript:void(0);"  class="arm_feature_activate_btn" data-name=' . $purchase_addon . ' data-plugin=' . $arm_plugin_name[0] . '  data-href="javascript:void(0);" data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="free_addon">Install</a><span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
                    $myicon .= '<div class="arm_feature_button_activate_wrapper hidden_section">
                        <a class="arm_feature_activate_btn arm_active_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="activate_addon">Activate</a><span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
                    $myicon .= '<div class="arm_feature_button_deactivate_wrapper hidden_section"><a id="arm_feature_deactivate_btn" class="'.$deactivate_btn_class.' arm_deactive_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="deactivate_addon">Deactivate</a>'.$config_button_html.'<span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
            } else if ($response == "NOT INSTALLED PAID") {
                $myicon = '<div class="arm_feature_button_activate_wrapper "><a class="arm_feature_activate_btn" href=javascript:void(0); data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="paid_addon" data-href="'.$actionurl.'"> Get It</a></div>';
            } else if ($response == "ACTIVE") {
                
                $myicon = '<div class="arm_feature_button_deactivate_wrapper"><a id="arm_feature_deactivate_btn" class="'.$deactivate_btn_class.' arm_deactive_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="deactivate_addon">Deactivate</a>'.$config_button_html.'<span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
                $myicon .= '<div class="arm_feature_button_activate_wrapper hidden_section">
                        <a class="arm_feature_activate_btn arm_active_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="activate_addon">Activate</a><span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
            } else if ($response == "NOT ACTIVE") {
                $myicon = '<div class="arm_feature_button_activate_wrapper "><a class="arm_feature_activate_btn arm_active_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="activate_addon">Activate</a><span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
                $myicon .= '<div class="arm_feature_button_deactivate_wrapper hidden_section"><a id="arm_feature_deactivate_btn" class="'.$deactivate_btn_class.' arm_deactive_addon" data-file="' . $pluginname . '" href="javascript:void(0);"  data-version="'.$compatible_version.'" data-arm_version="'.$armember_version.'" data-type ="deactivate_addon">Deactivate</a>'.$config_button_html.'<span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span></div>';
            }
            return $myicon;
        }

        function addons_page() {
	       $armember_addons_page = get_transient("arm_addons_listing_data_page");
           if(false === $armember_addons_page) {
                $plugins = get_plugins();
                $installed_plugins = array();
                foreach ($plugins as $key => $plugin) {
                    $is_active = is_plugin_active($key);
                    $installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
                    $installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
                    $installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

                    $installed_plugins[] = $installed_plugin;
                }

                global $arm_version;
                $bloginformation = array();
                $str = $this->get_rand_alphanumeric(10);

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
                $encodedval = base64_encode($valstring);

                $urltopost = 'https://www.armemberplugin.com/armember_addons/addon_list.php';

                $raw_response = wp_remote_post($urltopost, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array('wpversion' => $encodedval),
                    'cookies' => array()
                        )
                );

                if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                    return "0|^^|<div class='error_message' style='margin-top:100px; padding:20px;'>" . __("Add-On listing is currently unavailable. Please try again later.", 'ARMember') . "</div>";
                } else {
                    set_transient("arm_addons_listing_data_page", $raw_response['body'], DAY_IN_SECONDS);
                    return "1|^^|" . $raw_response['body'];
                }
            } else {
                return "1|^^|" . $armember_addons_page;
            }
        }

        function arm_install_plugin_install_status($api, $loop = false) {
            // This function is called recursively, $loop prevents further loops.
            if (is_array($api))
                $api = (object) $api;

            // Default to a "new" plugin
            $status = 'install';
            $url = false;
            $update_file = false;

            /*
             * Check to see if this plugin is known to be installed,
             * and has an update awaiting it.
             */
            $version = '';
            $update_plugins = get_site_transient('update_plugins');
            if (isset($update_plugins->response)) {
                foreach ((array) $update_plugins->response as $file => $plugin) {
                    if ($plugin->slug === $api->slug) {
                        $status = 'update_available';
                        $update_file = $file;
                        $version = $plugin->new_version;
                        if (current_user_can('update_plugins'))
                            $url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $update_file), 'upgrade-plugin_' . $update_file);
                        break;
                    }
                }
            }

            if ('install' == $status) {
                if (is_dir(WP_PLUGIN_DIR . '/' . $api->slug)) {
                    $installed_plugin = get_plugins('/' . $api->slug);
                    if (empty($installed_plugin)) {
                        if (current_user_can('install_plugins'))
                            $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
                    } else {
                        $key = array_keys($installed_plugin);
                        $key = reset($key); //Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
                        $update_file = $api->slug . '/' . $key;
                        if (version_compare($api->version, $installed_plugin[$key]['Version'], '=')) {
                            $status = 'latest_installed';
                        } elseif (version_compare($api->version, $installed_plugin[$key]['Version'], '<')) {
                            $status = 'newer_installed';
                            $version = $installed_plugin[$key]['Version'];
                        } else {
                            //If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
                            if (!$loop) {
                                delete_site_transient('update_plugins');
                                wp_update_plugins();
                                return arm_install_plugin_install_status($api, true);
                            }
                        }
                    }
                } else {
                    // "install" & no directory with that slug
                    if (current_user_can('install_plugins'))
                        $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
                }
            }
            if (isset($_GET['from']))
                $url .= '&amp;from=' . urlencode(wp_unslash($_GET['from']));

            $file = $update_file;
            return compact('status', 'url', 'version', 'file');
        }

        function arm_set_current_member($user_id, $username='')
        {
            return wp_set_current_user($user_id, $username);
        }

    }

}
global $arm_social_feature;
$arm_social_feature = new ARM_social_feature();

/*
  wp_unslash function to remove slashes. default in wordpress 4.6
 */

if (!function_exists('wp_unslash')) {

    function wp_unslash($value) {
        return stripslashes_deep($value);
    }

}

if (!class_exists('Automatic_Upgrader_Skin')) {
    if (version_compare($GLOBALS['wp_version'], '4.6', '<'))
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    else
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

    if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {

        class Automatic_Upgrader_Skin extends WP_Upgrader_Skin {

            protected $messages = array();

            /**
             * Determines whether the upgrader needs FTP/SSH details in order to connect
             * to the filesystem.
             *
             * @since 3.7.0
             * @since 4.6.0 The `$context` parameter default changed from `false` to an empty string.
             *
             * @see request_filesystem_credentials()
             *
             * @param bool   $error                        Optional. Whether the current request has failed to connect.
             *                                             Default false.
             * @param string $context                      Optional. Full path to the directory that is tested
             *                                             for being writable. Default empty.
             * @param bool   $allow_relaxed_file_ownership Optional. Whether to allow Group/World writable. Default false.
             * @return bool True on success, false on failure.
             */
            public function request_filesystem_credentials($error = false, $context = '', $allow_relaxed_file_ownership = false) {
                if ($context) {
                    $this->options['context'] = $context;
                }
                // TODO: fix up request_filesystem_credentials(), or split it, to allow us to request a no-output version
                // This will output a credentials form in event of failure, We don't want that, so just hide with a buffer
                ob_start();
                $result = parent::request_filesystem_credentials($error, $context, $allow_relaxed_file_ownership);
                ob_end_clean();
                return $result;
            }

            /**
             * @access public
             *
             * @return array
             */
            public function get_upgrade_messages() {
                return $this->messages;
            }

            /**
             * @param string|array|WP_Error $data
             */
            public function feedback($data) {
                if (is_wp_error($data)) {
                    $string = $data->get_error_message();
                } elseif (is_array($data)) {
                    return;
                } else {
                    $string = $data;
                }
                if (!empty($this->upgrader->strings[$string]))
                    $string = $this->upgrader->strings[$string];

                if (strpos($string, '%') !== false) {
                    $args = func_get_args();
                    $args = array_splice($args, 1);
                    if (!empty($args))
                        $string = vsprintf($string, $args);
                }

                $string = trim($string);

                // Only allow basic HTML in the messages, as it'll be used in emails/logs rather than direct browser output.
                $string = wp_kses($string, array(
                    'a' => array(
                        'href' => true
                    ),
                    'br' => true,
                    'em' => true,
                    'strong' => true,
                        ));

                if (empty($string))
                    return;

                $this->messages[] = $string;
            }

            /**
             * @access public
             */
            public function header() {
                ob_start();
            }

            /**
             * @access public
             */
            public function footer() {
                $output = ob_get_clean();
                if (!empty($output))
                    $this->feedback($output);
            }

        }

    }
}

if (!class_exists('WP_Ajax_Upgrader_Skin')) {
    if (version_compare($GLOBALS['wp_version'], '4.6', '<'))
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    else
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
    if (version_compare($GLOBALS['wp_version'], '4.6', '<')) {

        class WP_Ajax_Upgrader_Skin extends Automatic_Upgrader_Skin {

            /**
             * Holds the WP_Error object.
             *
             * @since 4.6.0
             * @access protected
             * @var null|WP_Error
             */
            protected $errors = null;

            /**
             * Constructor.
             *
             * @since 4.6.0
             * @access public
             *
             * @param array $args Options for the upgrader, see WP_Upgrader_Skin::__construct().
             */
            public function __construct($args = array()) {
                parent::__construct($args);

                $this->errors = new WP_Error();
            }

            /**
             * Retrieves the list of errors.
             *
             * @since 4.6.0
             * @access public
             *
             * @return WP_Error Errors during an upgrade.
             */
            public function get_errors() {
                return $this->errors;
            }

            /**
             * Retrieves a string for error messages.
             *
             * @since 4.6.0
             * @access public
             *
             * @return string Error messages during an upgrade.
             */
            public function get_error_messages() {
                $messages = array();

                foreach ($this->errors->get_error_codes() as $error_code) {
                    if ($this->errors->get_error_data($error_code) && is_string($this->errors->get_error_data($error_code))) {
                        $messages[] = $this->errors->get_error_message($error_code) . ' ' . esc_html(strip_tags($this->errors->get_error_data($error_code)));
                    } else {
                        $messages[] = $this->errors->get_error_message($error_code);
                    }
                }

                return implode(', ', $messages);
            }

            /**
             * Stores a log entry for an error.
             *
             * @since 4.6.0
             * @access public
             *
             * @param string|WP_Error $errors Errors.
             */
            public function error($errors) {
                if (is_string($errors)) {
                    $string = $errors;
                    if (!empty($this->upgrader->strings[$string])) {
                        $string = $this->upgrader->strings[$string];
                    }

                    if (false !== strpos($string, '%')) {
                        $args = func_get_args();
                        $args = array_splice($args, 1);
                        if (!empty($args)) {
                            $string = vsprintf($string, $args);
                        }
                    }

                    // Count existing errors to generate an unique error code.
                    $errors_count = count($errors->get_error_codes());
                    $errors_count_plus_one = $errors_count + 1;
                    $this->errors->add('unknown_upgrade_error_'.$errors_count_plus_one, $string);
                } elseif (is_wp_error($errors)) {
                    foreach ($errors->get_error_codes() as $error_code) {
                        $this->errors->add($error_code, $errors->get_error_message($error_code), $errors->get_error_data($error_code));
                    }
                }

                $args = func_get_args();
                call_user_func_array(array($this, 'parent::error'), $args);
            }

            /**
             * Stores a log entry.
             *
             * @since 4.6.0
             * @access public
             *
             * @param string|array|WP_Error $data Log entry data.
             */
            public function feedback($data) {
                if (is_wp_error($data)) {
                    foreach ($data->get_error_codes() as $error_code) {
                        $this->errors->add($error_code, $data->get_error_message($error_code), $data->get_error_data($error_code));
                    }
                }

                $args = func_get_args();
                call_user_func_array(array($this, 'parent::feedback'), $args);
            }

        }

    }
}

if (!function_exists('wp_register_plugin_realpath')) {

    function wp_register_plugin_realpath($file) {
        global $wp_plugin_paths;
        // Normalize, but store as static to avoid recalculation of a constant value
        static $wp_plugin_path = null, $wpmu_plugin_path = null;
        if (!isset($wp_plugin_path)) {
            $wp_plugin_path = wp_normalize_path(WP_PLUGIN_DIR);
            $wpmu_plugin_path = wp_normalize_path(WPMU_PLUGIN_DIR);
        }

        $plugin_path = wp_normalize_path(dirname($file));
        $plugin_realpath = wp_normalize_path(dirname(realpath($file)));

        if ($plugin_path === $wp_plugin_path || $plugin_path === $wpmu_plugin_path) {
            return false;
        }

        if ($plugin_path !== $plugin_realpath) {
            $wp_plugin_paths[$plugin_path] = $plugin_realpath;
        }
        return true;
    }

}

if (!function_exists('wp_normalize_path')) {

    function wp_normalize_path($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('|(?<=.)/+|', '/', $path);
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }
        return $path;
    }

}