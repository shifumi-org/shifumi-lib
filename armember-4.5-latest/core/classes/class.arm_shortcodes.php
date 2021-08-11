<?php
if (!class_exists('ARM_shortcodes')) {

    class ARM_shortcodes {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            /* Build Shortcodes For `armif` */
            add_action('parse_request', array(&$this, 'arm_build_armif_shortcodes'), 1);
            add_action('parse_request', array(&$this, 'arm_build_armnotif_shortcodes'), 1);
            /* Build Shortcodes For Subscription Plans */
            add_shortcode('arm_plan', array(&$this, 'arm_plan_shortcode_func'));
            add_shortcode('arm_plan_not', array(&$this, 'arm_plan_not_shortcode_func'));

            add_shortcode('arm_restrict_content', array(&$this, 'arm_restrict_content_shortcode_func'));
            add_shortcode('arm_content', array(&$this, 'arm_content_shortcode_func'));
            add_shortcode('arm_not_login_content', array(&$this, 'arm_not_login_content_shortcode_func'));
            add_shortcode('arm_template', array(&$this, 'arm_template_shortcode_func'));
            add_shortcode('arm_account_detail', array(&$this, 'arm_account_detail_shortcode_func'));
            add_shortcode('arm_view_profile', array(&$this, 'arm_view_profile_shortcode_func'));
            add_shortcode('arm_subscription_detail', array(&$this, 'arm_subscription_detail_shortcode_func'));
            add_shortcode('arm_member_transaction', array(&$this, 'arm_member_transaction_func'));
            add_shortcode('arm_close_account', array(&$this, 'arm_close_account_shortcode_func'));
            add_shortcode('arm_membership', array(&$this, 'arm_membership_detail_shortcode_func'));
           
            add_shortcode('arm_conditional_redirection', array(&$this, 'arm_conditional_redirection_shortcode_func'));
            add_shortcode('arm_conditional_redirection_role', array(&$this, 'arm_conditional_redirection_by_user_role_shortcode_func'));
            add_shortcode('arm_username', array(&$this, 'arm_username_func'));
            add_shortcode('arm_userid', array(&$this, 'arm_userid_func'));
            add_shortcode('arm_displayname', array(&$this, 'arm_displayname_func'));
            add_shortcode('arm_avatar', array(&$this, 'arm_avatar_func'));
            add_shortcode('arm_if_user_in_trial', array(&$this, 'arm_if_user_in_trial_func'));
            add_shortcode('arm_not_if_user_in_trial', array(&$this, 'arm_not_if_user_in_trial_func'));
            add_shortcode('arm_firstname_lastname', array(&$this, 'arm_firstname_lastname_func'));
            add_shortcode('arm_user_plan', array(&$this, 'arm_user_plan_func'));
            add_shortcode('arm_usermeta', array(&$this, 'arm_usermeta_func'));
            add_shortcode('arm_user_badge', array(&$this, 'arm_user_badge_func'));
            add_shortcode('arm_user_planinfo', array(&$this, 'arm_user_planinfo_func'));
            add_shortcode('arm_purchased_paid_post_list', array(&$this, 'arm_membership_detail_shortcode_func'));
            add_shortcode('arm_paid_post_member_transaction', array(&$this, 'arm_member_transaction_func'));

            add_action('wp_ajax_arm_directory_paging_action', array(&$this, 'arm_directory_paging_action'));
            add_action('wp_ajax_nopriv_arm_directory_paging_action', array(&$this, 'arm_directory_paging_action'));
            add_action('wp_ajax_arm_transaction_paging_action', array(&$this, 'arm_transaction_paging_action'));
            add_action('wp_ajax_nopriv_arm_transaction_paging_action', array(&$this, 'arm_transaction_paging_action'));
            add_action('wp_ajax_arm_login_history_paging_action', array(&$this, 'arm_login_history_paging_action'));
            add_action('wp_ajax_nopriv_arm_login_history_paging_action', array(&$this, 'arm_login_history_paging_action'));
            add_action('wp_ajax_arm_close_account_form_submit_action', array(&$this, 'arm_close_account_form_action'));
            add_action('wp_ajax_nopriv_arm_close_account_form_submit_action', array(&$this, 'arm_close_account_form_action'));
            add_action('wp_ajax_arm_membership_paging_action', array(&$this, 'arm_membership_paging_action'));
            
            /* Add Buttons Into WordPress(TinyMCE) Editor */
            //add_action('admin_footer', array(&$this, 'arm_insert_shortcode_popup'));
            add_action('media_buttons', array(&$this, 'arm_insert_shortcode_button'), 20);
            //add_action('admin_init', array(&$this, 'arm_add_tinymce_styles'));
            add_action('admin_enqueue_scripts', array(&$this, 'arm_add_tinymce_styles'));
            /* Add Font Support Into WordPress(TinyMCE) Editor */
            add_filter('mce_buttons', array(&$this, 'arm_editor_mce_buttons'));
            add_filter('mce_buttons_2', array(&$this, 'arm_editor_mce_buttons_2'));
            add_filter('tiny_mce_before_init', array(&$this, 'arm_editor_font_sizes'));
            add_filter('arm_change_advanced_shortcode_names', array(&$this, 'arm_change_advanced_shortcode_functions'));
            add_filter('arm_change_armif_shortcode_before_displayed', array(&$this, 'arm_change_armif_shortcode_before_displayed_func'), 10, 2);
            add_filter('arm_change_armnotif_shortcode_before_displayed', array(&$this, 'arm_change_armnotif_shortcode_before_displayed_func'), 10, 2);
            /* Shortcode for Display Current User Login History */
        }

        function arm_change_armif_shortcode_before_displayed_func($content, $tag) {
            switch ($tag) {
                case 'is_post_type_archive':
                case 'is_page_template':
                    return "<td><code>do_shortcode('[armif <strong>" . $tag . "()</strong>] " . __('Content Goes Here', 'ARMember') . " [/armif]');</code></td>";
            }
            return $content;
        }

        function arm_change_armnotif_shortcode_before_displayed_func($content, $tag) {
            switch ($tag) {
                case 'is_post_type_archive':
                case 'is_page_template':
                    return "<td><code>do_shortcode('[armNotif <strong>" . $tag . "()</strong>] " . __('Content Goes Here', 'ARMember') . " [/armNotif]');</code></td>";
            }
            return $content;
        }

        function arm_change_advanced_shortcode_functions($function) {
            switch ($function) {
                case 'is_blog_index' :
                case 'is_home' :
                    return 'is_home';
                    break;

                case 'is_front_page':
                case 'is_home_page':
                    return 'is_front_page';
                    break;

                case 'is_single' :
                case 'is_single_post' :
                    return 'is_single';
                    break;

                case 'is_sticky' :
                case 'is_sticky_post' :
                    return 'is_sticky';
                    break;

                case 'is_author' :
                case 'author_archive' :
                    return 'is_author';
                    break;

                case 'user_can' :
                case 'is_user_can' :
                    return 'user_can';
                    break;

                case 'is_taxonomy' :
                case 'is_tax' :
                    return 'taxonomy_exists';
                    break;

                default:
                    return $function;
            }
            return $function;
        }

        function arm_build_armif_shortcodes() {
            $armif_shortcodes = array(
                'armif', '_armif', '__armif', '___armif', '____armif', '_____armif',
                '______armif', '_______armif', '________armif', '_________armif', '__________armif',
                'armIf', '_armIf', '__armIf', '___armIf', '____armIf', '_____armIf',
                '______armIf', '_______armIf', '________armIf', '_________armIf', '__________armIf',
            );
            foreach ($armif_shortcodes as $code) {
                add_shortcode($code, array(&$this, 'armif_shortcode_func'));
            }
        }

        function arm_build_armnotif_shortcodes() {
            $armnotif_shortcodes = array(
                'armNotif', '_armNotif', '__armNotif', '___armNotif', '____armNotif', '_____armNotif',
                '______armNotif', '_______armNotif', '________armNotif', '_________armNotif', '__________armNotif',
                'armNotIf', '_armNotIf', '__armNotIf', '___armNotIf', '____armNotIf', '_____armNotIf',
                '______armNotIf', '_______armNotIf', '________armNotIf', '_________armNotIf', '__________armNotIf',
            );
            foreach ($armnotif_shortcodes as $code) {
                add_shortcode($code, array(&$this, 'armnotif_shortcode_func'));
            }
        }

        function armif_shortcode_func($atts, $content, $shortcode) {
            $count = count($atts);
            $new_atts = '';
            for($i=0; $i<$count; $i++){
                $new_atts .=' '.$atts[$i];
            }
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $err_msg = $arm_global_settings->common_message['arm_armif_invalid_argument'];
            $err_msg = (!empty($err_msg)) ? $err_msg : __('Invalid Conditional argument(s).', 'ARMember');
            $shortcode_vars = array();
            $shortcode_vars = get_defined_vars();
            $arm_allow_tags = array(
                'is_home', 'is_front_page', 'is_admin', 'is_network_admin', 'is_blog_admin', 'is_user_admin', 'is_single', 'is_sticky', 'is_post_type_archive', 'is_page', 'is_page_template', 'is_category', 'is_tag', 'has_tag', 'is_tax', 'term_exists', 'is_author', 'is_archive', 'is_search', 'is_singular', 'is_main_query', 'is_feed', 'has_excerpt', 'has_nav_menu', 'in_the_loop', 'is_rtl', 'is_multisite', 'is_main_site', 'is_super_admin', 'is_user_logged_in', 'is_plugin_active', 'is_plugin_inactive', 'has_post_thumbnail', 'user_can', 'current_user_can', 'current_user_can_for_blog', 'check_user_meta', 'is_user_id', 'is_username', 'taxonomy_exists',
            );
            $arm_allow_tags = apply_filters('arm_armif_shortcode_allow_tags', $arm_allow_tags);

            $atts = $new_atts;
            $attr = $arm_global_settings->trim_qts_deep((array) $atts);
            $logicals = array();
            foreach ($attr as $attr_key => $attr_value) {
                if (preg_match('/(is_plugin_active|is_plugin_inactive)/', $attr_value)) {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                /**
                 * It's NOT possible to mix logic. You MUST stick to one type of logic or another.
                 */
                if (preg_match('/^(&&|&amp;&amp;|&#038;&#038;|AND|\|\||OR|[\!\=\<\>]+)$/i', $attr_value)) {
                    /* Stick with AND/OR. Ampersands are corrupted by the Visual Editor. */
                    $logicals[] = strtolower($attr_value);
                    unset($attr[$attr_key]); /* ^ Detect logic here. We'll use the first key #0. */
                    if (preg_match('/^[\!\=\<\>]+$/i', $attr_value)) {
                        return __('Simple Conditionals cannot process operators like', 'ARMember') . ' ( == != <> ). ' . __('Please use Advanced (PHP) Conditionals instead.', 'ARMember');
                    }
                }
            }
            if (!empty($logicals) && is_array($logicals) && count(array_unique($logicals)) > 1) {
                return do_shortcode(apply_filters('armif_shortcode_content', '', get_defined_vars()));
            }
            $conditional_logic = (!empty($logicals) && is_array($logicals) && preg_match('/^(\|\||OR)$/i', $logicals[0])) ? 'OR' : 'AND';
            if ($conditional_logic === 'AND') {
                $is_failed = FALSE;
                foreach ($attr as $attr_value) {
                    if (preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($function = $m[2]) && ($attr_args = preg_replace('/[' . "\r\n\t" . ']/', '', $m[4])) !== 'nill') {
                            if (!(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new[' . "\r\n\t" . '\s]/i', $attr_args))) {

                            if (is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) {
                                $function = apply_filters('arm_change_advanced_shortcode_names', trim($function)); 
                                if (in_array(strtolower($function), $arm_allow_tags)) {
                                    $test = ($exclamation) ? FALSE : TRUE;
                                    if (!function_exists('check_user_meta')) {

                                        function check_user_meta($meta_key = '', $meta_value = '', $operator = 'EQUALSTO_N', $user_id = '') {

                                            if (!is_user_logged_in() && $user_id == '') {
                                                return false;
                                            }
                                            if ($user_id == '') {
                                                $user_id = get_current_user_id();
                                            }

                                            if ($operator == '' || $operator == null) {
                                                $operator = 'EQUALSTO_N';
                                            }

                                            $meta_key = trim($meta_key);
                                            $meta_value = trim($meta_value);
                                            $operator = trim($operator);
                                            $user_id = trim($user_id);

                                            $meta_key = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $meta_key);
                                            $operator = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $operator);
                                            $user_id = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_id);



                                            $operator = strtolower($operator);
                                            $meta_value = ltrim($meta_value, '"');
                                            $meta_value = rtrim($meta_value, '"');
                                            $meta_value = ltrim($meta_value, "'");
                                            $meta_value = rtrim($meta_value, "'");
                                            $flag = false;

                                            if (($meta_key == '' && $meta_value == '') || $meta_key == '') {
                                                return false;
                                            }



                                            $user_meta_value = get_user_meta($user_id, $meta_key, true);

                                            if ($operator == 'equalsto_s') {
                                                if ($user_meta_value == $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'equalsto_n') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value == $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'notequalsto_n') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value != $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'notequalsto_s') {
                                                if ($user_meta_value != $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'greaterthan') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value > $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'lessthan') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value < $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'greaterthanequalsto') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value >= $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'lessthanequalsto') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value <= $meta_value) {
                                                    $flag = true;
                                                }
                                            }

                                            return $flag;
                                        }

                                    }

                                    if (!function_exists('is_user_id')) {

                                        function is_user_id($user_id = '') {

                                            $user_id = trim($user_id);

                                            $user_id = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_id);

                                            if (!is_user_logged_in() || $user_id == '') {
                                                return false;
                                            }
                                            $flag1 = false;
                                            $current_user_id = get_current_user_id();

                                            if (trim($user_id) == $current_user_id) {

                                                $flag1 = true;
                                            }

                                            return $flag1;
                                        }

                                    }
                                    if (!function_exists('is_username')) {

                                        function is_username($user_name = '') {

                                            $user_name = trim($user_name);

                                            $user_name = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_name);


                                            $user_name = ltrim($user_name, '"');

                                            if (!is_user_logged_in() || $user_name == '') {
                                                return false;
                                            }
                                            $flag1 = false;
                                            $current_user_info = wp_get_current_user();

                                            if (trim($user_name) == $current_user_info->user_login) {

                                                $flag1 = true;
                                            }

                                            return $flag1;
                                        }

                                    }
                                    if (preg_match('/^\{(.*?)\}$/', $attr_args)) {
                                        if ($test === TRUE && !call_user_func($function, $args)) {
                                            $is_failed = TRUE;
                                            break;
                                        } else if ($test === FALSE && call_user_func($function, $args)) {
                                            $is_failed = TRUE;
                                            break;
                                        }
                                    } else if (empty($args)) {
                                        if ($test === TRUE && !call_user_func($function)) {
                                            $is_failed = TRUE;
                                            break;
                                        } else if ($test === FALSE && call_user_func($function)) {
                                            $is_failed = TRUE;
                                            break;
                                        }
                                    } else if ($test === TRUE && !call_user_func_array($function, $args)) {
                                        $is_failed = TRUE;
                                        break;
                                    } else if ($test === FALSE && call_user_func_array($function, $args)) {
                                        $is_failed = TRUE;
                                        break;
                                    }
                                } else {
                                    return $err_msg;
                                }
                            } else {
                                return $err_msg;
                            }
                        } else {
                            return $err_msg;
                        }
                    } else {
                        return $err_msg;
                    }
                }
                $content = ($is_failed) ? '' : $content;
                if ($content) {
                    $content = $arm_global_settings->trim_html($content);
                }
                return do_shortcode(apply_filters('armif_shortcode_content', $content, get_defined_vars()));
            } else if ($conditional_logic === 'OR') {
                $is_succeeded = FALSE;
                foreach ($attr as $attr_value) {
                    if (preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($function = $m[2]) && ($attr_args = preg_replace('/[' . "\r\n\t" . '\s]/', '', $m[4])) !== 'nill') {
                        if (!(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new[' . "\r\n\t" . '\s]/i', $attr_args))) {
                            if (is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) {
                                if (in_array(strtolower($function), $arm_allow_tags)) {
                                    $test = ($exclamation) ? FALSE : TRUE;
                                    if (preg_match('/^\{(.*?)\}$/', $attr_args)) {
                                        if ($test === TRUE && call_user_func($function, $args)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        } else if ($test === FALSE && !call_user_func($function, $args)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        }
                                    } else if (empty($args)) {
                                        if ($test === TRUE && call_user_func($function)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        } else if ($test === FALSE && !call_user_func($function)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        }
                                    } else if ($test === TRUE && call_user_func_array($function, $args)) {
                                        $is_succeeded = TRUE;
                                        break;
                                    } else if ($test === FALSE && !call_user_func_array($function, $args)) {
                                        $is_succeeded = TRUE;
                                        break;
                                    }
                                } else {
                                    return $err_msg;
                                }
                            } else {
                                return $err_msg;
                            }
                        } else {
                            return $err_msg;
                        }
                    } else {
                        return $err_msg;
                    }
                }
                $content = ($is_succeeded) ? $content : '';
                if ($content) {
                    $content = $arm_global_settings->trim_html($content);
                }
                return do_shortcode(apply_filters('armif_shortcode_content', $content, get_defined_vars()));
            }
            return do_shortcode(apply_filters('armif_shortcode_content', '', get_defined_vars()));
        }

        function armnotif_shortcode_func($atts, $content, $shortcode) {
            $count = count($atts);
           
            $new_atts = '';
            for($i=0; $i<$count; $i++){
                $new_atts .=' '.$atts[$i];
            }
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $err_msg = $arm_global_settings->common_message['arm_armif_invalid_argument'];
            $err_msg = (!empty($err_msg)) ? $err_msg : __('Invalid Conditional argument(s).', 'ARMember');
            $shortcode_vars = array();
            $shortcode_vars = get_defined_vars();
            $arm_allow_tags = array(
                'is_home', 'is_front_page', 'is_admin', 'is_network_admin', 'is_blog_admin', 'is_user_admin', 'is_single', 'is_sticky', 'is_post_type_archive', 'is_page', 'is_page_template', 'is_category', 'is_tag', 'has_tag', 'is_tax', 'term_exists', 'is_author', 'is_archive', 'is_search', 'is_singular', 'is_main_query', 'is_feed', 'has_excerpt', 'has_nav_menu', 'in_the_loop', 'is_rtl', 'is_multisite', 'is_main_site', 'is_super_admin', 'is_user_logged_in', 'is_plugin_active', 'is_plugin_inactive', 'has_post_thumbnail', 'user_can', 'current_user_can', 'current_user_can_for_blog', 'check_user_meta', 'is_user_id', 'is_username', 'taxonomy_exists',
            );
            $arm_allow_tags = apply_filters('arm_armif_shortcode_allow_tags', $arm_allow_tags);

            $atts = $new_atts;
            $attr = $arm_global_settings->trim_qts_deep((array) $atts);
            $logicals = array();

            foreach ($attr as $attr_key => $attr_value) {
                if (preg_match('/(is_plugin_active|is_plugin_inactive)/', $attr_value)) {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                /**
                 * It's NOT possible to mix logic. You MUST stick to one type of logic or another.
                 */
                if (preg_match('/^(&&|&amp;&amp;|&#038;&#038;|AND|\|\||OR|[\!\=\<\>]+)$/i', $attr_value)) {
                    /* Stick with AND/OR. Ampersands are corrupted by the Visual Editor. */
                    $logicals[] = strtolower($attr_value);
                    unset($attr[$attr_key]); /* ^ Detect logic here. We'll use the first key #0. */
                    if (preg_match('/^[\!\=\<\>]+$/i', $attr_value)) {
                        return __('Simple Conditionals cannot process operators like', 'ARMember') . ' ( == != <> ). ' . __('Please use Advanced (PHP) Conditionals instead.', 'ARMember');
                    }
                }
            }
            if (!empty($logicals) && is_array($logicals) && count(array_unique($logicals)) > 1) {
                return do_shortcode(apply_filters('armif_shortcode_content', '', get_defined_vars()));
            }

            $conditional_logic = (!empty($logicals) && is_array($logicals) && preg_match('/^(\|\||OR)$/i', $logicals[0])) ? 'OR' : 'AND';

            if ($conditional_logic === 'AND') {
                $is_failed = FALSE;
                foreach ($attr as $attr_value) {
                    if (preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($function = $m[2]) && ($attr_args = preg_replace('/[' . "\r\n\t" . ']/', '', $m[4])) !== 'nill') {

                        if (!(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new[' . "\r\n\t" . '\s]/i', $attr_args))) {
                            if (is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) {

                                $function = apply_filters('arm_change_advanced_shortcode_names', trim($function));
                                if (in_array(strtolower($function), $arm_allow_tags)) {
                                    $test = ($exclamation) ? FALSE : TRUE;
                                    if (!function_exists('check_user_meta')) {

                                        function check_user_meta($meta_key = '', $meta_value = '', $operator = 'EQUALSTO_N', $user_id = '') {

                                            if (!is_user_logged_in() && $user_id == '') {
                                                return false;
                                            }
                                            if ($user_id == '') {
                                                $user_id = get_current_user_id();
                                            }

                                            if ($operator == '' || $operator == null) {
                                                $operator = 'EQUALSTO_N';
                                            }

                                            $meta_key = trim($meta_key);
                                            $meta_value = trim($meta_value);
                                            $operator = trim($operator);
                                            $user_id = trim($user_id);

                                            $meta_key = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $meta_key);
                                            $operator = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $operator);
                                            $user_id = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_id);



                                            $operator = strtolower($operator);
                                            $meta_value = ltrim($meta_value, '"');
                                            $meta_value = rtrim($meta_value, '"');
                                            $meta_value = ltrim($meta_value, "'");
                                            $meta_value = rtrim($meta_value, "'");
                                            $flag = false;

                                            if (($meta_key == '' && $meta_value == '') || $meta_key == '') {
                                                return false;
                                            }

                                            $user_meta_value = get_user_meta($user_id, $meta_key, true);

                                            if ($operator == 'equalsto_s') {
                                                if ($user_meta_value == $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'equalsto_n') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value == $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'notequalsto_n') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value != $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'notequalsto_s') {
                                                if ($user_meta_value != $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'greaterthan') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value > $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'lessthan') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value < $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'greaterthanequalsto') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value >= $meta_value) {
                                                    $flag = true;
                                                }
                                            } else if ($operator == 'lessthanequalsto') {
                                                if (!is_numeric($meta_value) || !is_numeric($user_meta_value)) {
                                                    return false;
                                                }

                                                if ($user_meta_value <= $meta_value) {
                                                    $flag = true;
                                                }
                                            }

                                            return $flag;
                                        }
                                    }

                                    if (!function_exists('is_user_id')) {

                                        function is_user_id($user_id = '') {

                                            $user_id = trim($user_id);

                                            $user_id = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_id);

                                            if (!is_user_logged_in() || $user_id == '') {
                                                return true;
                                            }
                                            $flag1 = false;
                                            $current_user_id = get_current_user_id();

                                            if (trim($user_id) == $current_user_id) {

                                                $flag1 = true;
                                            }

                                            return $flag1;
                                        }
                                    }

                                    if (!function_exists('is_username')) {

                                        function is_username($user_name = '') {

                                            $user_name = trim($user_name);

                                            $user_name = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $user_name);


                                            $user_name = ltrim($user_name, '"');

                                            if (!is_user_logged_in() || $user_name == '') {
                                                return true;
                                            }
                                            $flag1 = false;
                                            $current_user_info = wp_get_current_user();

                                            if (trim($user_name) == $current_user_info->user_login) {

                                                $flag1 = true;
                                            }

                                            return $flag1;
                                        }

                                    }
                                    if (preg_match('/^\{(.*?)\}$/', $attr_args)) {

                                        if ($test === TRUE && !call_user_func($function, $args)) {

                                            $is_failed = TRUE;
                                            break;
                                        } else if ($test === FALSE && call_user_func($function, $args)) {
                                            $is_failed = TRUE;
                                            break;
                                        }
                                    } else if (empty($args)) {

                                        if ($test === TRUE && !call_user_func($function)) {
                                            $is_failed = TRUE;
                                            break;
                                        } else if ($test === FALSE && call_user_func($function)) {
                                            $is_failed = TRUE;
                                            break;
                                        }
                                    } else if ($test === TRUE && !call_user_func_array($function, $args)) {

                                        $is_failed = TRUE;
                                        break;
                                    } else if ($test === FALSE && call_user_func_array($function, $args)) {
                                        $is_failed = TRUE;
                                        break;
                                    }
                                } else {
                                    return $err_msg;
                                }
                            } else {
                                return $err_msg;
                            }
                        } else {
                            return $err_msg;
                        }
                    } else {
                        return $err_msg;
                    }
                }

                $content = ($is_failed) ? $content : '';
                if ($content) {
                    $content = $arm_global_settings->trim_html($content);
                }
                return do_shortcode(apply_filters('armif_shortcode_content', $content, get_defined_vars()));
            } else if ($conditional_logic === 'OR') {
                $is_succeeded = FALSE;
                foreach ($attr as $attr_value) {
                    if (preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($function = $m[2]) && ($attr_args = preg_replace('/[' . "\r\n\t" . '\s]/', '', $m[4])) !== 'nill') {
                        if (!(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new[' . "\r\n\t" . '\s]/i', $attr_args))) {
                            if (is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) {
                                if (in_array(strtolower($function), $arm_allow_tags)) {
                                    $test = ($exclamation) ? FALSE : TRUE;
                                    if (preg_match('/^\{(.*?)\}$/', $attr_args)) {
                                        if ($test === TRUE && call_user_func($function, $args)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        } else if ($test === FALSE && !call_user_func($function, $args)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        }
                                    } else if (empty($args)) {
                                        if ($test === TRUE && call_user_func($function)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        } else if ($test === FALSE && !call_user_func($function)) {
                                            $is_succeeded = TRUE;
                                            break;
                                        }
                                    } else if ($test === TRUE && call_user_func_array($function, $args)) {
                                        $is_succeeded = TRUE;
                                        break;
                                    } else if ($test === FALSE && !call_user_func_array($function, $args)) {
                                        $is_succeeded = TRUE;
                                        break;
                                    }
                                } else {
                                    return $err_msg;
                                }
                            } else {
                                return $err_msg;
                            }
                        } else {
                            return $err_msg;
                        }
                    } else {
                        return $err_msg;
                    }
                }
                $content = ($is_succeeded) ? '' : $content;
                if ($content) {
                    $content = $arm_global_settings->trim_html($content);
                }

                return do_shortcode(apply_filters('armif_shortcode_content', $content, get_defined_vars()));
            }
            return do_shortcode(apply_filters('armif_shortcode_content', '', get_defined_vars()));
        }

        function armif_shortcode_tags_details() {
            $armif_tags = array(
                'is_blog_index' => array('desc' => __('Checks if the current page is blog posts index, then content between shortcode will be executed.', 'ARMember')),
                'is_home_page' => array('desc' => __('Checks if the current page/post is Home page of site, then content between shortcode will be executed.', 'ARMember')),
                'is_single_post' => array(
                    'desc' => __('Checks if the current post is post view/detail page, content between shortcode will be executed.', 'ARMember') . "<br/>" . __(" You can use post_id, slug as shortcode argument to check post detail/view page is executed.", 'ARMember'),
                    'args' => array(
                        'is_single_post($post_id)',
                        'is_single_post($slug)',
                    ),
                ),
                'is_sticky_post' => array('desc' => __('Checks if the current post is a Sticky Post meaning the "Stick this post to the front page" check box has been checked for the post, content between shortcode will be executed.', 'ARMember'), 'args' => array('is_sticky_post($post_id)')),
                'is_post_type_archive' => array('desc' => __('Checks if the query is for an archive page of a given post type(s), then content between shortcode will be executed.', 'ARMember'), 'args' => array('is_post_type_archive($post_types)', 'is_post_type_archive($post_types_array)')),
                'is_page' => array(
                    'desc' => __('Checks if Pages are being displayed, then content between shortcode will be executed.', 'ARMember') . "<br/>" . __("If you are using it from the theme template then make sure it should be placed BEFORE loop otherwise it wont work.", 'ARMember'),
                    'args' => array(
                        'is_page($post_id)',
                        'is_page($slug)',
                    ),
                ),
                'is_category' => array(
                    'desc' => __('Checks if a Category archive page is being displayed, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array(
                        'is_category($category_id)',
                        'is_category($slug)',
                    ),
                ),
                'is_tag' => array(
                    'desc' => __('Checks if a Tag archive page is being displayed, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array(
                        'is_tag($tag_id)',
                        'is_tag($slug)',
                    ),
                ),
                'has_tag' => array(
                    'desc' => __("Check if the current post has any of the given tags, then content between shortcode will be executed. If no tags are given, determines if post has any tags.", 'ARMember'),
                    'args' => array(
                        'has_tag($tag_id)',
                        'has_tag($slug)',
                    ),
                ),
                'is_taxonomy' => array(
                    'desc' => __('Checks if a custom taxonomy archive page is being displayed, then content between shortcode will be executed. If the $taxonomy parameter is specified, this function will additionally check if the query is for that specific taxonomy. Note that', 'ARMember') . ' is_taxonomy() returns false ' . __('on category archives and tag archives.', 'ARMember'),
                    'args' => array('is_taxonomy($taxonomy)', 'is_taxonomy($taxonomy,$term)')
                ),
                'author_archive' => array(
                    'desc' => __('Checks if an Author archive page is being displayed, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array('author_archive($author_id)', 'author_archive($nickname)')
                ),
                'is_archive' => array('desc' => __('Checks if any type of Archive page is being displayed, then content between shortcode will be executed.', 'ARMember') . "<br/>" . __('An Archive is a Category, Tag, Author or a Date based pages.', 'ARMember')),
                'is_search' => array('desc' => __('Checks if search result page archive is being displayed, then content between shortcode will be executed.', 'ARMember')),
                'is_rtl' => array('desc' => __('Checks if current locale is RTL (Right To Left script), then content between shortcode will be executed.', 'ARMember')),
                'is_multisite' => array('desc' => __('Checks if Multisite support is enabled, then content between shortcode will be executed.', 'ARMember')),
                'is_main_site' => array(
                    'desc' => __('Check if passed site is of main site, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array('is_main_site($site_id)')
                ),
                'has_post_thumbnail' => array(
                    'desc' => __('Checks if a post has a Featured Image (formerly known as Post Thumbnail) attached or not, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array('has_post_thumbnail($post_id)')
                ),
                'is_user_can' => array(
                    'desc' => __('Checks if a user has capability or role, then content between shortcode will be executed. Similar to current_user_can(), but this function takes a user ID as its first parameter.', 'ARMember'),
                    'args' => array(
                        'is_user_can($user_id,$capability)',
                    ),
                ),
                'current_user_can' => array(
                    'desc' => __('Checks if current user has capability or role, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array('current_user_can($capability)')
                ),
                'current_user_can_for_blog' => array(
                    'desc' => __('Checks if current user has a capability or role for a given blog, then content between shortcode will be executed.', 'ARMember'),
                    'args' => array('current_user_can_for_blog($blog_id,$capability)')
                ),
                'check_user_meta' => array(
                    // 'Checks if user has correct meta_value based on operator, then content between shortcode will be executed. Default operator is EQUALSTO_N.
                    'desc' => __('Checks if user has correct meta_value based on operator, then content between shortcode will be executed. Default operator is EQUALSTO_N, <br/>S = String Comparision,<br/> N = Numeric Comparision <br/> Last argument $user_id is optional argument, if you will not pass any user id then it will check for current user.', 'ARMember'),
                    'args' => array(
                        'check_user_meta($meta_key,$value,"EQUALSTO_S",$user_id)',
                        'check_user_meta($meta_key,$value,"EQUALSTO_N",$user_id)',
                        'check_user_meta($meta_key,$value,"NOTEQUALSTO_S",$user_id)',
                        'check_user_meta($meta_key,$value,"NOTEQUALSTO_N",$user_id)',
                        'check_user_meta($meta_key,$value,"GREATERTHAN",$user_id)',
                        'check_user_meta($meta_key,$value,"LESSTHAN",$user_id)',
                        'check_user_meta($meta_key,$value,"GREATERTHANEQUALSTO",$user_id)',
                        'check_user_meta($meta_key,$value,"LESSTHANEQUALSTO",$user_id)')
                ),
                'is_user_id' => array(
                    'desc' => __('Checks if the current user ID matches $user_id , then content between shortcode will be executed. Shortcode won\'t work if $user_id parameter is not given.', 'ARMember'),
                    'args' => array(
                        'is_user_id($user_id)',
                    ),
                ),
                'is_username' => array(
                    'desc' => __('Checks if the current user name matches $user_name , then content between shortcode will be executed. Shortcode won\'t work if $username parameter is not given.', 'ARMember'),
                    'args' => array(
                        'is_username($username)',
                    ),
                ),
            );

            return $armif_tags;
        }

        function arm_plan_shortcode_func($atts, $content, $tag) {
            if (current_user_can('administrator')) {
                return do_shortcode($content);
            }
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            $defaults = array(
                'id' => 0,
                'message' => '',
            );
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts($defaults, $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            if (!empty($id) && $id != 0) {
                $user_id = get_current_user_id();
                if (!empty($user_id) && $user_id != 0) {
                    $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $user_plans = !empty($user_plans) ? $user_plans : array();
                    if (in_array($id, $user_plans)) {
                        return do_shortcode($content);
                    }
                }
            }

            return $message;
        }

        function arm_plan_not_shortcode_func($atts, $content, $tag) {
            if (current_user_can('administrator')) {
                return do_shortcode($content);
            }
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            $defaults = array(
                'id' => 0,
                'message' => '',
            );
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts($defaults, $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            if (!empty($id) && $id != 0) {
                $user_id = get_current_user_id();
                if (!empty($user_id) && $user_id != 0) {
                    $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $user_plans = !empty($user_plans) ? $user_plans : array();
                    if (!in_array($id, $user_plans)) {
                        return do_shortcode($content);
                    }
                }
            }
            return $message;
        }

        function arm_restrict_content_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            $defaults = array(
                'type' => 'hide', /* Shortcode behaviour type */
                'plan' => '', /* Plan Id or comma separated plan ids. */
            );
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts($defaults, $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $main_content = $else_content = NULL;
            $else_tag = '[armelse]';
            if (strpos($content, $else_tag) !== FALSE) {
                list($main_content, $else_content) = explode($else_tag, $content, 2);
            } else {
                $main_content = $content;
            }
            /* Always Display Content For Admins */
            if (current_user_can('administrator')) {
                return do_shortcode($main_content);
            }
            $hasaccess = FALSE;
            $isLoggedIn = is_user_logged_in();
            $current_user_id = get_current_user_id();
            $arm_user_plan = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
            $arm_user_plan = !empty($arm_user_plan) ? $arm_user_plan : array();
            if(!empty($arm_user_plan)){
                $suspended_plan_ids = get_user_meta($current_user_id, 'arm_user_suspended_plan_ids', true);
                if( ! empty($suspended_plan_ids)) {
                    foreach ($suspended_plan_ids as $suspended_plan_id) {
                        if(in_array($suspended_plan_id, $arm_user_plan)) {
                            unset($arm_user_plan[array_search($suspended_plan_id, $arm_user_plan)]);
                        }
                    }
                }
            }
            if (!empty($plan)) {
                /* Plans Section */
                if (strpos($plan, ",")) {
                    $plans = explode(",", $plan);
                } else {
                    $plans = array($plan);
                }
                $plans = array_filter($plans);
                $registered = FALSE;
                if (in_array('registered', $plans)) {
                    $registered = TRUE;
                    $rkey = array_search('registered', $plans);
                    unset($plans[$rkey]);
                }
                $unregistered = FALSE;
                if (in_array('unregistered', $plans)) {
                    $unregistered = TRUE;
                    $ukey = array_search('unregistered', $plans);
                    unset($plans[$ukey]);
                }
                $return_array = array_intersect($arm_user_plan, $plans);
                if ($type == 'show') {
                    if ($isLoggedIn) {
                        if ($registered) {
                            $hasaccess = TRUE;
                        }

                        if (!empty($plans) && !empty($return_array)) {
                            $hasaccess = TRUE;
                        }

                        if(!empty($arm_user_plan) && in_array("any_plan", $plans)) {
                            $hasaccess = TRUE;
                        }
                    } else {
                        /* Show Content To Non LoggedIn Members */
                        if ($unregistered) {
                            $hasaccess = TRUE;
                        }
                    }
                } else {
                    if ($isLoggedIn) {
                        /* Need to check this condition and confirm */
                        if ($unregistered) {
                            $hasaccess = TRUE;
                        }
                        /* Need to check this condition and confirm */

                        if (!empty($plans) && empty($return_array)) {
                            $hasaccess = TRUE;
                        }

                        if(!empty($arm_user_plan) && in_array('any_plan', $plans)) {
                            $hasaccess = FALSE;
                        }
                    } else {
                        /* Hide Content From Non LoggedIn Members */
                        if (!$unregistered) {
                            $hasaccess = TRUE;
                        }
                    }
                }
            } else {
                if ($type == 'show') {
                    $hasaccess = TRUE;
                }
            }
            $hasaccess = apply_filters('arm_restrict_content_shortcode_hasaccess', $hasaccess, $opts);
            if ($hasaccess) {
                return do_shortcode($main_content);
            } else {
                return do_shortcode($else_content);
            }
        }

        function arm_if_user_in_trial_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $main_content = $content;
            $else_content = NULL;
            /* Always Display Content For Admins */
            if (current_user_can('administrator')) {
                return do_shortcode($main_content);
            }

            $hasaccess = FALSE;
            if (is_user_logged_in()) {
                $current_user_id = get_current_user_id();
                $arm_user_plans = get_user_meta($current_user_id, 'arm_user_plan_ids', true);

                $hasaccess = FALSE;
                if (!empty($arm_user_plans) && is_array($arm_user_plans)) {

                    foreach ($arm_user_plans as $arm_user_plan) {
                        /* Plans Section */
                        $planData = get_user_meta($current_user_id, 'arm_user_plan_' . $arm_user_plan, true);
                        if (!empty($planData)) {
                            $planDetail = $planData['arm_current_plan_detail'];
                            if (!empty($planDetail)) {
                                $plan_info = new ARM_Plan(0);
                                $plan_info->init((object) $planDetail);
                            } else {
                                $plan_info = new ARM_Plan($arm_user_plan);
                            }
                            if ($plan_info->is_recurring()) {
                                $arm_is_trial = $planData['arm_is_trial_plan'];
                                if ($arm_is_trial == 1) {
                                    $arm_plan_trial_expiry_date = $planData['arm_trial_end'];
                                    if ($arm_plan_trial_expiry_date != '') {
                                        $now = current_time('timestamp');
                                        if ($now <= $arm_plan_trial_expiry_date) {
                                            $hasaccess = TRUE;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $main_content = apply_filters('arm_is_user_in_trial_shortcode_content', $main_content);
            $else_content = apply_filters('arm_is_user_in_trial_shortcode_else_content', $else_content);
            $hasaccess = apply_filters('arm_is_user_in_trial_shortcode_hasaccess', $hasaccess);
            if ($hasaccess) {
                return do_shortcode($main_content);
            } else {
                return do_shortcode($else_content);
            }
        }

        function arm_not_if_user_in_trial_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $main_content = $content;
            $else_content = NULL;
            /* Always Display Content For Admins */
            if (current_user_can('administrator')) {
                return do_shortcode($main_content);
            }
            $hasaccess = FALSE;
            if (is_user_logged_in()) {
                $current_user_id = get_current_user_id();
                $arm_user_plans = get_user_meta($current_user_id, 'arm_user_plan_ids', true);

                if (!empty($arm_user_plans) && is_array($arm_user_plans)) {
                    foreach ($arm_user_plans as $arm_user_plan) {
                        $hasaccess = FALSE;
                        /* Plans Section */
                        $planData = get_user_meta($current_user_id, 'arm_user_plan_' . $arm_user_plan, true);
                        $planDetail = $planData['arm_current_plan_detail'];
                        if (!empty($planDetail)) {
                            $plan_info = new ARM_Plan(0);
                            $plan_info->init((object) $planDetail);
                        } else {
                            $plan_info = new ARM_Plan($arm_user_plan);
                        }
                        if ($plan_info->is_recurring()) {
                            $arm_is_trial = $planData['arm_is_trial_plan'];
                            if ($arm_is_trial == 1) {
                                $arm_plan_trial_expiry_date = $planData['arm_trial_end'];
                                if ($arm_plan_trial_expiry_date != '') {
                                    $now = current_time('timestamp');
                                    if ($now > $arm_plan_trial_expiry_date) {
                                        $hasaccess = TRUE;
                                    }
                                }
                            } else {
                                $hasaccess = TRUE;
                            }
                        } else {
                            $hasaccess = TRUE;
                        }

                        if ($hasaccess == FALSE) {
                            break;
                        }
                    }
                } else {
                    $hasaccess = TRUE;
                }
            }


            $main_content = apply_filters('arm_not_is_user_in_trial_shortcode_content', $main_content);
            $else_content = apply_filters('arm_not_is_user_in_trial_shortcode_else_content', $else_content);
            $hasaccess = apply_filters('arm_not_is_user_in_trial_shortcode_hasaccess', $hasaccess);
            if ($hasaccess) {
                return do_shortcode($main_content);
            } else {
                return do_shortcode($else_content);
            }
        }

        function arm_content_shortcode_func($atts, $content, $tag) {
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
                'plan' => 'all', /* Plan Id or comma separated plan ids. */
                'message' => '', /* Message for restricted area. */
            );
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts($defaults, $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $hasaccess = TRUE;
            /* Check if User is logged in */
            if (is_user_logged_in()) {
                $user_id = $current_user->ID;
                $arm_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $arm_user_plan = !empty($arm_user_plan) ? $arm_user_plan : array();
                /* Plans Section */
                if (strpos($plan, ",")) {
                    $plans = explode(",", $plan);
                } else {
                    $plans = array($plan);
                }
                $return_array = array_intersect($arm_user_plan, $plans);
                if ($plan != 'all' && (!empty($plans) && empty($return_array))) {
                    $hasaccess = FALSE;
                }
            } else {
                $hasaccess = FALSE;
            }
            $hasaccess = apply_filters('arm_content_shortcode_hasaccess', $hasaccess);
            if ($hasaccess) {
                return do_shortcode($content);
            } else {
                return $message;
            }
        }

        function arm_not_login_content_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ---------------------/.Begin Set Shortcode Attributes--------------------- */
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts(array('message' => ''), $atts, $tag);
            extract($opts);
            /* ---------------------/.End Set Shortcode Attributes--------------------- */
            if (!is_user_logged_in()) {
                $content = do_shortcode($content);
            } else {
                $content = $message;
            }
            return $content;
        }

        /**
         * Directory Template AJAX Pagination Content
         */
        function arm_directory_paging_action() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_directory_paging_action') {
                unset($_POST['action']);
                $content = '';
                if (!empty($_POST)) {
                    if (isset($_POST['temp_data']) && !empty($_POST['temp_data'])) {
                        $_POST['temp_data'] = stripslashes($_POST['temp_data']);
                    }
                    //if (isset($_POST['pagination']) && $_POST['pagination'] == 'infinite') {
                        $opts = $_POST;
                        if ($opts['id'] == 'add') {
                            $temp_data = maybe_unserialize($opts['temp_data']);
                            $temp_data = (object) $temp_data;
                        } else {
                            $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$opts['id']}' AND `arm_type`='{$opts['type']}'");
                        }

                        if (!empty($temp_data)) {
                            $temp_data->arm_options = isset($temp_data->arm_options) ? maybe_unserialize($temp_data->arm_options) : array();
                            $opts['template_options'] = $temp_data->arm_options;
                            $opts['current_page'] = (isset($opts['current_page'])) ? $opts['current_page'] : 1;
                            $opts['pagination'] = (isset($opts['template_options']['pagination'])) ? $opts['template_options']['pagination'] : 'numeric';
                            $opts['show_badges'] = (isset($opts['template_options']['show_badges']) && $opts['template_options']['show_badges'] == '1') ? true : false;
                            $opts['show_joining'] = (isset($opts['template_options']['show_joining']) && $opts['template_options']['show_joining'] == '1') ? true : false;
                            $opts['show_admin_users'] = (isset($opts['template_options']['show_admin_users']) && $opts['template_options']['show_admin_users'] == '1') ? true : false;

                            $content = $arm_members_directory->arm_get_directory_members($temp_data, $opts);
                        }
                    /*} else {
                        $shortcode_param = '';
                        foreach ($_POST as $k => $v) {
                            $shortcode_param .= "{$k}='{$v}' ";
                        }

                        $content = do_shortcode("[arm_template $shortcode_param]");
                    }*/
                    echo do_shortcode($content);
                    exit;
                }
            }
        }

        function arm_template_shortcode_func($atts, $content, $tag) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class, $arm_social_feature, $arm_member_forms, $arm_capabilities_global;
            
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            if (!$arm_social_feature->isSocialFeature) {
                return do_shortcode($content);
            }
            $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
            $alphabaticalSortByTxt = (!empty($common_messages['directory_sort_by_alphabatically'])) ? $common_messages['directory_sort_by_alphabatically'] : __('Alphabetically', 'ARMember');
            $recentlyJoinedTxt = (!empty($common_messages['directory_sort_by_recently_joined'])) ? $common_messages['directory_sort_by_recently_joined'] : __('Recently Joined', 'ARMember');
            /* ---------------------/.Begin Set Shortcode Attributes./--------------------- */
            /* Extract Shortcode Attributes */
            $opts = shortcode_atts(array(
                'id' => '',
                'type' => '',
                'user_id' => 0,
                'role' => 'all',
                'listof' => 'all',
                'search' => '',
                'orderby' => 'display_name',
                'order' => 'ASC',
                'current_page' => 1,
                'per_page' => 10,
                'pagination' => 'numeric',
                'sample' => false,
                'temp_data' => '',
                'is_preview' => 0,
                'arm_directory_field_list' => '',
                'default_search_field' => '',
                'default_search_value' => '',
                    ), $atts, $tag);
            extract($opts);
            $opts['listof'] = (!empty($opts['listof'])) ? $opts['listof'] : 'all';
            $opts['sample'] = ($opts['sample'] === 'true' || $opts['sample'] === '1') ? true : false;
            $opts['is_preview'] = ($opts['is_preview'] === 'true' || $opts['is_preview'] === '1') ? 1 : 0;
            /* ---------------------/.End Set Shortcode Attributes./--------------------- */
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $pd_templates = array();
            if (!empty($id) && !empty($type)) {
                $user_id = 0;
                if($type == 'profile'){
                    $current_user_info = false;
                    global $wp_query;
                    $reqUser = $wp_query->get('arm_user');
                    
                    if (empty($reqUser)) {
                        $reqUser = (isset($_REQUEST['arm_user']) && !empty($_REQUEST['arm_user'])) ? $_REQUEST['arm_user'] : '';
                    }
                    if (!empty($reqUser)) {
                        $permalinkBase = isset($arm_global_settings->global_settings['profile_permalink_base']) ? $arm_global_settings->global_settings['profile_permalink_base'] : 'user_login';
                        if ($permalinkBase == 'user_login') {
                            $current_user_info = get_user_by('login', urldecode($reqUser));
                        } else {
                            $current_user_info = get_user_by('id', $reqUser);
                        }
                        if ($current_user_info !== false) {
                            $user_id = $current_user_info->ID;
                        } else {
                            return do_shortcode($content);
                        }
                    } else {
                        if (is_user_logged_in()) {
                            $user_id = get_current_user_id();
                            $current_user_info = get_user_by('id', $user_id);
                        } else {
                            return do_shortcode($content);
                        }
                    }
                    if($current_user_info!=false)
                    {
                        $arm_member_statuses = $wpdb->get_row("SELECT `arm_primary_status`, `arm_secondary_status` FROM `" . $ARMember->tbl_arm_members . "` WHERE `arm_user_id`='" . $user_id . "' ");
                        $arm_member_status = '';
                        if ($arm_member_statuses != null) {
                            $arm_member_status = $arm_member_statuses->arm_primary_status;
                            $arm_member_secondary_status = $arm_member_statuses->arm_secondary_status;

                            if (($arm_member_status == '2' && in_array($arm_member_secondary_status, array(0, 1))) || $arm_member_status == 4) {
                                $current_user_info = false;
                            }
                        }
                    }
                }
                
                $is_admin_user = $display_admin_user = 0;
                if( user_can($user_id,'administrator') ){
                    $is_admin_user = 1;
                }
                
                if ($id == 'add') {
                    $temp_data = maybe_unserialize($opts['temp_data']);
                    $temp_data = (object) $temp_data;
                } else {
                    if($type == 'profile'){
                        
                        $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        if(!empty($_REQUEST['action']) && $_REQUEST['action'] == "arm_template_preview"){
                            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_profiles_directories'], '1');
                            $temp_id_admin = $_REQUEST['temp_id'];
                        }else{
                            $temp_id_admin = $wpdb->get_row($wpdb->prepare('SELECT `arm_id` FROM `'. $ARMember->tbl_arm_member_templates.'` WHERE `arm_enable_admin_profile` = %d ORDER BY `arm_id` ASC LIMIT %d',1,1));
                            $temp_id_admin = isset($temp_id_admin->arm_id) ? $temp_id_admin->arm_id : 0;
                        }

                        $admin_template_data = array();
                        if(empty($user_plans) || $is_admin_user ){
                            if( $is_admin_user && isset($temp_id_admin) && $temp_id_admin > 0 && $temp_id_admin != '' ){
                                $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$temp_id_admin}' AND `arm_type`='{$type}'");
                                $display_admin_user = 1;

                            } else {
                                $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$id}' AND `arm_type`='{$type}'");
                            }
                        }else{
                            foreach($user_plans as $user_plan){
                                $temp_count = $wpdb->get_var("SELECT count(*) FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE FIND_IN_SET(" . $user_plan . ", `arm_subscription_plan`) AND `arm_type`='{$type}'"); 
                                if($temp_count > 0){
                                    $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE FIND_IN_SET(" . $user_plan . ", `arm_subscription_plan`) AND `arm_type`='{$type}' LIMIT 0,1");  
                                    break;
                                }
                            }
                            if($temp_count == 0){
                                $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$id}' AND `arm_type`='{$type}'"); 
                            }
                        }
                        
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/templates/' . $temp_data->arm_slug.'.css')) {
                            wp_enqueue_style('arm_template_style_' . $temp_data->arm_slug, MEMBERSHIP_VIEWS_URL . '/templates/' . $temp_data->arm_slug . '.css', array(), MEMBERSHIP_VERSION);
                        }
                    }
                    else{
                       $temp_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE `arm_id`='{$id}' AND `arm_type`='{$type}'"); 
                    }
                }
                if (!empty($temp_data)) {
                 
                    $temp_data->arm_options = isset($temp_data->arm_options) ? maybe_unserialize($temp_data->arm_options) : array();
                    $opts['template_options'] = $temp_data->arm_options;
                    $opts['pagination'] = (isset($opts['template_options']['pagination'])) ? $opts['template_options']['pagination'] : 'numeric';
                    $opts['per_page'] = (isset($opts['template_options']['per_page_users'])) ? $opts['template_options']['per_page_users'] : 10;
                    if($type=='directory')
                    {
                        $opts['show_admin_users'] = (isset($opts['template_options']['show_admin_users']) && $opts['template_options']['show_admin_users'] == '1') ? true : false;
                    }
                    else{
                        $opts['show_admin_users'] = isset($display_admin_user) && $display_admin_user == 1 ? true : false;
                    }
                    $opts['show_badges'] = (isset($opts['template_options']['show_badges']) && $opts['template_options']['show_badges'] == '1') ? true : false;
                    $opts['show_joining'] = (isset($opts['template_options']['show_joining']) && $opts['template_options']['show_joining'] == '1') ? true : false;
                    $opts['profile_fields'] = (!empty($opts['template_options']['profile_fields'])) ? $opts['template_options']['profile_fields'] : array();
                    $_data = array();
                    $content = apply_filters('arm_change_content_before_display_profile_and_directory', $content, $opts);
                    $randomTempID = $id . '_' . arm_generate_random_code();
                    $content .= '<div class="arm_template_wrapper arm_template_wrapper_' . $id . ' arm_template_wrapper_' . $temp_data->arm_slug . '">';

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];


                    $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 1;

                    if($enable_crop){
                        $content .='<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper" style="display:none;">';
                        $content .='<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_div"><img id="arm_crop_image" alt="" src="" style="max-width:100%;" data-rotate="0" /></div>';
                        $content .='<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
                        $content .='<button class="arm_crop_button arm_img_setting armhelptip tipso_style" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_button arm_img_setting armhelptip tipso_style" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_button arm_img_setting armhelptip tipso_style" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and <br/>use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';


                        $content .='<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;">';
                        $content .='<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
                        $content .='<div id="arm_crop_cover_div"><img id="arm_crop_cover_image" alt="" src="" style="max-width:100%;" data-rotate="0" /></div>';
                        $content .='<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr">';
                        $content .='<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . __('Crop', 'ARMember') . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
                        $content .='<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . __('Clear', 'ARMember') . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . __('Zoom In', 'ARMember') . '"><span class="armfa armfa-search-plus"></span></button>';
                        $content .='<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . __('Zoom Out', 'ARMember') . '"><span class="armfa armfa-search-minus"></span></button>';
                        $content .='<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="' . __('Rotate', 'ARMember') . '"><span class="armfa armfa-rotate-right"></span></button>';
                        $content .='<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . __('Reset', 'ARMember') . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
                        $content .='<button id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . __('Done', 'ARMember') . '</button>';
                        $content .='</div>';
                        $content .='<p class="arm_discription">' . __('(Use Cropper to set image and use mouse scroller for zoom image.)', 'ARMember') . '</p>';
                        $content .='</div>';
                    }
                    $content .= $arm_members_directory->arm_template_style($id, $opts['template_options']);
                    $arm_profile_form_rtl = $arm_directory_form_rtl = '';
                    if (is_rtl()) {
                        $arm_profile_form_rtl = 'arm_profile_form_rtl';
                        $arm_directory_form_rtl = 'arm_directory_form_rtl';
                    }
                    if ($type == 'profile') {
                        $content .= '<div class="arm_template_container arm_profile_container ' . $arm_profile_form_rtl . '"  id="arm_template_container_' . $randomTempID . '">';
                        if (!empty($current_user_info)) {
                            $_data = array($current_user_info);
                            $_data = $arm_members_directory->arm_prepare_users_detail_for_template($_data, $opts);
                            $_data = apply_filters('arm_change_user_detail_before_display_in_profile_and_directory', $_data, $opts);
                            $content .= $arm_members_directory->arm_profile_template_blocks((array) $temp_data, $_data, $opts);
                        }
                        $content .= '</div>';
                        $content .= '</div>';                    
                    } elseif ($type == 'directory') {
                        $arm_search_position = "top";
                        $content .= '<form method="POST" class="arm_directory_form_'.$arm_search_position.' arm_directory_form_container ' . $arm_directory_form_rtl . '" data-temp="' . $id . '" onsubmit="return false;" action="#">';
                        $content .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader_template.gif" alt="Loading.."></div>';
                        /* For Filter User List */
                        $sortbox = (isset($opts['template_options']['sortbox']) && $opts['template_options']['sortbox'] == '1') ? true : false;
                        $searchbox = (isset($opts['template_options']['searchbox']) && $opts['template_options']['searchbox'] == '1') ? true : false;

                        // For New Filters
                        //-------------------------------------------------------
                        $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
                        $orderedFields = array();

                        if (!empty($opts['profile_fields'])) {
                           foreach($opts['profile_fields'] as $fieldK) {
                               if (isset($dbProfileFields[$fieldK])) {
                                    $orderedFields[$fieldK] = $dbProfileFields[$fieldK];
                                    unset($dbProfileFields[$fieldK]);
                               }
                           }
                        }

                        $arm_search_filter_title = !empty($common_messages['profile_template_search_filter_title']) ? $common_messages['profile_template_search_filter_title'] : __('Search Members', 'ARMember');

                        $tempOptions = !empty($temp_data->arm_options) ? $temp_data->arm_options : '';
                        
                        $armSearchType = !empty($tempOptions['search_type']) ? $tempOptions['search_type'] : '0';
                        $content .= '<div class="arm_search_filter_fields_wrapper arm_search_filter_fields_wrapper_'.$arm_search_position.' arm_search_filter_container_type_'.$armSearchType.'">';

                        if(!empty($armSearchType) && $searchbox)
                        {
                            $content .= '<div class="arm_search_filter_title_div"><label class="arm_search_filter_title_label">'.$arm_search_filter_title.'</label></div>';
                            foreach($orderedFields as $armSearchkey => $armSearchValue)
                            {
                                $armSearchFieldType = isset($armSearchValue['type']) ? $armSearchValue['type'] : '';
                                $armSearchFieldId = isset($armSearchValue['id']) ? $armSearchValue['id'] : '';
                                $armSearchFieldLabel = isset($armSearchValue['label']) ? stripslashes_deep($armSearchValue['label']) : '';
                                $armSearchFieldId = 'arm_'.$armSearchFieldId.'_'.$randomTempID;

                                if($armSearchFieldType=='profile_cover')
                                {
                                    continue;
                                }

                                $arm_search_field_item = '<div class="arm_search_filter_field_item_'.$arm_search_position.'" armSearchFieldType='.$armSearchFieldType.'>';
                                
                                $arm_search_field_item_label_start = '<div class="arm_dir_filter_label">';
                                $arm_search_field_item_label_end = '</div>';

                                $arm_search_field_item_input_start = '<div class="arm_dir_filter_input">';
                                $arm_search_field_item_input_end = '</div>';

                                if( $armSearchFieldType == "text" || $armSearchFieldType == "url" || $armSearchFieldType == "textarea")
                                {
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label" for="'.$armSearchFieldId.'">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;
                                    
                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<input type="text" name="arm_directory_field_list['.$armSearchkey.']" placeholder="'.$armSearchValue['placeholder'].'" id="'.$armSearchFieldId.'">'.$arm_search_field_item_input_end;
                                }
                                else if($armSearchFieldType == "date")
                                {
                                    if( !wp_script_is('arm_bootstrap_datepicker_with_locale_js','enqueued')){
                                        wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                                    }
                                    $arm_search_field_item .= '<div class="arm_datetimepicker_field">';
                                    $armform = new ARM_Form();
                                    $userRegForm = $arm_member_forms->arm_get_single_member_forms('101');
                                    $arm_exists_form = $armform->arm_is_form_exists('101');
                                    if ($arm_exists_form) {
                                        $armform->init((object) $userRegForm);
                                    }
                                    $formSettings = $armform->settings;
                                    
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
                                    $dateFormat = $dateFormatTypes[$formSettings['date_format']];
                                    $showTimePicker = '0';
                                    if (!empty($formSettings['show_time'])) {
                                        $showTimePicker = $formSettings['show_time'];
                                    }
                                    $calLocalization = '';
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label" for="'.$armSearchFieldId.'">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;

                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<input type="text" name="arm_directory_field_list['.$armSearchkey.']" class="arm_front_filter_datepicker arm_datepicker arm_datepicker_front" id="'.$armSearchFieldId.'" data-dateformat="' . $dateFormat . '" data-date_field="arm_date_field_101" data-show_timepicker="' . $showTimePicker . '" data-cal_localization="' . $calLocalization . '">'.$arm_search_field_item_input_end;
                                    $arm_search_field_item .= '</div>';
                                }
                                else if($armSearchFieldType == "email")
                                {
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;
                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<input type="email" name="arm_directory_field_list['.$armSearchkey.']" placeholder="'.$armSearchValue['placeholder'].'" id="'.$armSearchFieldId.'">'.$arm_search_field_item_input_end;
                                }
                                else if($armSearchFieldType == "radio")
                                {
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;
                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<div class="arm_search_filter_radio">';
                                    foreach($armSearchValue['options'] as $armSearchOptKey => $armSearchOptValue)
                                    {
                                        $armRadioLabelValue = $armSearchOptValue;
                                        $armRadioValue = $armSearchOptValue;
                                        if(strpos($armSearchOptValue, ':') !== false)
                                        {
                                            $armRadioValue = explode(':', $armSearchOptValue);
                                            $armRadioValue = end($armRadioValue);
                                            $armRadioLabelValue = substr($armSearchOptValue, 0, strrpos($armSearchOptValue, ':'));
                                        }
                                        $arm_search_field_item .= '<input type="radio" name="arm_directory_field_list['.$armSearchkey.']" placeholder="'.$armSearchValue['placeholder'].'" id="'.$armSearchFieldId.'_'.$armSearchOptKey.'" value="'.$armRadioValue.'">';

                                        $arm_search_field_item .= '<label class="arm_search_filter_field_radio_item_label" for="'.$armSearchFieldId.'_'.$armSearchOptKey.'">'.$armRadioLabelValue.'</label>';
                                    }
                                    $arm_search_field_item .= '</div>'.$arm_search_field_item_input_end;
                                }
                                else if($armSearchFieldType == "checkbox")
                                {
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;
                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<div class="arm_search_filter_chk">';
                                    foreach($armSearchValue['options'] as $armSearchOptKey => $armSearchOptValue)
                                    {
                                        $armChkLabelValue = $armSearchOptValue;
                                        $armChkValue = $armSearchOptValue;
                                        if(strpos($armSearchOptValue, ':') !== false)
                                        {
                                            $armChkValue = explode(':', $armSearchOptValue);
                                            $armChkValue = end($armChkValue);
                                            $armChkLabelValue = substr($armSearchOptValue, 0, strrpos($armSearchOptValue, ':'));
                                        }
                                        
                                        $arm_search_field_item .= '<div class="arm_chk_field_div">';
                                        $arm_search_field_item .= '<input type="checkbox" name="arm_directory_field_list['.$armSearchkey.'][]" id="'.$armSearchFieldId.'_'.$armSearchOptKey.'" value="'.$armChkValue.'">';

                                        $arm_search_field_item .= '<label class="arm_search_filter_field_radio_item_label" for="'.$armSearchFieldId.'_'.$armSearchOptKey.'">'.$armChkLabelValue.'</label>';
                                        $arm_search_field_item .= '</div>';
                                    }
                                    $arm_search_field_item .= '</div>'.$arm_search_field_item_input_end;
                                }
                                else if($armSearchFieldType == "select")
                                {
                                    $arm_search_field_item .= $arm_search_field_item_label_start.'<label class="arm_search_filter_field_item_label" for="'.$armSearchFieldId.'">'.$armSearchFieldLabel.'</label>'.$arm_search_field_item_label_end;
                                    $arm_search_field_item .= $arm_search_field_item_input_start.'<select name="arm_directory_field_list['.$armSearchkey.']" id="'.$armSearchFieldId.'">';
                                    foreach($armSearchValue['options'] as $armSearchOptKey => $armSearchOptValue)
                                    {
                                        $armSelectLabelValue = $armSearchOptValue;
                                        $armSelectValue = $armSearchOptValue;
                                        if(strpos($armSearchOptValue, ':') !== false)
                                        {
                                            $armSelectValue = explode(':', $armSearchOptValue);
                                            $armSelectValue = end($armSelectValue);
                                            $armSelectLabelValue = substr($armSearchOptValue, 0, strrpos($armSearchOptValue, ':'));
                                        }

                                        $armSearchOptValueArr = explode(':', $armSearchOptValue);
                                        $arm_search_field_item .= '<option value="'.$armSelectValue.'">'.$armSelectLabelValue.'</option>';
                                    }
                                    $arm_search_field_item .= '</select>'.$arm_search_field_item_input_end;
                                }
                                $arm_search_field_item .= '</div>';

                                $content .= $arm_search_field_item;
                            }
                        }
                        
                        if(!empty($armSearchType) && $searchbox)
                        {

                            $arm_directory_search_placeholder = isset($common_messages['arm_directory_search_placeholder']) ? $common_messages['arm_directory_search_placeholder'] : __('Search','ARMember');
                            $content .= '<div class="arm_directory_filters_wrapper arm_directory_filters_wrapper_'.$arm_search_position.'">';
                            if ($searchbox) {
                                $content .= '<div class="arm_directory_search_wrapper">';
                                $content .= '<input type="text" name="search" value="' . esc_attr($search) . '" class="arm_directory_search_box" placeholder="'.$arm_directory_search_placeholder.'">';
                                $content .= '</div>';
                            } else {
                                $content .= '<input type="hidden" name="search" value="">';
                            }
                            $armFilterSortByTxt = !empty($common_messages['directory_sort_by_field']) ? $common_messages['directory_sort_by_field'] : __('Sort By', 'ARMember');
                            $content .= '<input type="hidden" name="listof" value="all">';
                            if ($sortbox) {
                                $content .= '<div class="arm_directory_list_by_filters">';
                                $content .= '<select name="orderby" class="arm_directory_listby_select">';
                                $content .= '<option value="login" ' . selected($orderby, 'login', false) . '>' . $armFilterSortByTxt . '</option>';
                                $content .= '<option value="display_name" ' . selected($orderby, 'display_name', false) . '>' . $alphabaticalSortByTxt . '</option>';
                                $content .= '<option value="user_registered" ' . selected($orderby, 'user_registered', false) . '>' . $recentlyJoinedTxt . '</option>';
                                $content .= '</select>';
                                $content .= '</div>';
                            } else {
                                $content .= '<input type="hidden" name="orderby" value="display_name">';
                            }
                            $content .= '<div class="armclear"></div>';
                            $content .= '</div>';
                            $content .= '<div class="armclear"></div>';
                        }
                        else
                        {
                            $content .= '<div class="arm_directory_filters_wrapper">';
                            if ($searchbox) {
                                $content .= '<div class="arm_directory_field_list_filter">';
                                $content .= '<select name="arm_directory_field_list" class="arm_directory_fieldlistby_select">';
                                $content .= '<option value="all" ' . selected(esc_attr($arm_directory_field_list), 'all', false) . '>' . __('All', 'ARMember') . '</option>';
                                if(!empty($opts['profile_fields']) && is_array($opts['profile_fields']))
                                {
                                    $armFormFields = $arm_members_directory->arm_template_profile_fields();
                                    
                                    foreach($opts['profile_fields'] as $arm_profile_fields_key )
                                    {
                                        if(isset($armFormFields[$arm_profile_fields_key]))
                                        {
                                            $content .= '<option value="'.$arm_profile_fields_key.'" ' . selected($arm_directory_field_list, $arm_profile_fields_key, false) . '>' . $armFormFields[$arm_profile_fields_key]['label'] . '</option>';
                                        }
                                    }                                        
                                }
                                $content .= '</select>';
                                $content .= '</div>';
                                $content .= '<div class="arm_directory_search_wrapper">';
                                $content .= '<input type="text" name="search" value="' . esc_attr($search) . '" class="arm_directory_search_box">';
                                $content .= '<a class="arm_directory_search_btn"><i class="armfa armfa-search"></i></a>';
                                $content .= '</div>';
                                $content .= '<div class="arm_directory_clear_wrapper">';
                                $content .= '<a class="arm_directory_clear_btn"><img id="arm_reset_img" width="24" height="24" style="" src="' . MEMBERSHIP_IMAGES_URL . '/reset-button.png"></a>';
                                $content .= '<img id="arm_loader_img" width="24" height="24" style="position: relative; display: none; float: right; margin-left: 5px; " src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" alt="Loading..">';
                                $content .= '</div>';
                            } else {
                                $content .= '<input type="hidden" name="search" value="">';
                            }
                            $content .= '<input type="hidden" name="listof" value="all">';
                            if ($sortbox) {
                                $content .= '<div class="arm_directory_list_by_filters">';
                                $content .= '<select name="orderby" class="arm_directory_listby_select">';
                                $content .= '<option value="login" ' . selected($orderby, 'login', false) . '>' . __('Sort By', 'ARMember') . '</option>';
                                $content .= '<option value="display_name" ' . selected($orderby, 'display_name', false) . '>' . $alphabaticalSortByTxt . '</option>';
                                $content .= '<option value="user_registered" ' . selected($orderby, 'user_registered', false) . '>' . $recentlyJoinedTxt . '</option>';
                                $content .= '</select>';
                                $content .= '</div>';
                            } else {
                                $content .= '<input type="hidden" name="orderby" value="login">';
                            }
                            $content .= '<div class="armclear"></div>';
                            $content .= '</div>';
                            $content .= '<div class="armclear"></div>';
                        }

                        if(!empty($armSearchType) && $searchbox)
                        {
                            $arm_directory_search_button = isset($common_messages['arm_directory_search_button']) ? $common_messages['arm_directory_search_button'] : __('Search','ARMember');
                            $arm_directory_reset_button = isset($common_messages['arm_directory_reset_button']) ? $common_messages['arm_directory_reset_button'] : __('Reset','ARMember');
                            $content .= '<div class="arm_button_search_filter_btn_div arm_button_search_filter_btn_div_'.$arm_search_position.'">';
                            $content .= '<button type="button" class="arm_directory_search_btn"><i class="armfa armfa-search"></i> <span>'.$arm_directory_search_button.'</span></button>';
                            $content .= '<button class="arm_directory_clear_btn"><img id="arm_reset_img" width="24" height="24" style="" src="' . MEMBERSHIP_IMAGES_URL . '/reset-button.png"> <span>'.$arm_directory_reset_button.'</span></button>';
                            $content .= '<img id="arm_loader_img" width="24" height="24" src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" alt="Loading..">';
                            $content .= '</div>';
                        }

                        $content .= '</div>';
                        //Search Filter Div Ends
                        //-------------------------------------------------------

                        $content .= '<div class="arm_template_container_top arm_template_container arm_directory_container arm_directory_container_'.$arm_search_position.'" id="arm_template_container_' . $randomTempID . '">';
                        $content .= $arm_members_directory->arm_get_directory_members($temp_data, $opts);
                        /* Template Arguments Inputs */
                        foreach (array('id', 'type', 'user_id', 'role', 'order', 'per_page', 'pagination', 'sample', 'temp_data', 'is_preview', 'default_search_field', 'default_search_value') as $k) 
                        {
                            $content .= '<input type="hidden" class="arm_temp_field_' . $k . '" name="' . $k . '" value="' . esc_attr($opts[$k]) . '">';
                        }
                        
                        $content .= '</div>';
                        $content .= '</form>';
                    }
                    $content .= '<div class="armclear"></div>';
                    //$content .= '</div>';
                    $content = apply_filters('arm_change_content_after_display_profile_and_directory', $content, $opts);
                }
            }
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
            return do_shortcode($content.$hiddenvalue);
            
        }

        /**
         * Transaction AJAX Pagination Content
         */
        function arm_transaction_paging_action() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_transaction_paging_action') {
                unset($_POST['action']);
                if (!empty($_POST)) {
                    $shortcode_param = '';
                    foreach ($_POST as $k => $v) {
                        $shortcode_param .= $k . '="' . $v . '" ';
                    }

                    if($_POST['is_paid_post']==1){
                        echo do_shortcode("[arm_paid_post_member_transaction $shortcode_param]");    
                    }else{
                        echo do_shortcode("[arm_member_transaction $shortcode_param]");
                    }
                    
                    exit;
                }
            }
        }

        function arm_login_history_paging_action() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_login_history_paging_action') {

                unset($_POST['action']);
                if (!empty($_POST)) {
                    $shortcode_param = '';
                    foreach ($_POST as $k => $v) {
                        $shortcode_param .= $k . '="' . $v . '" ';
                    }
                    echo do_shortcode("[arm_login_history $shortcode_param]");
                    exit;
                }
            }
        }

        function arm_membership_paging_action() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_members_directory, $arm_members_class;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_membership_paging_action') {
                unset($_POST['action']);
                if (!empty($_POST)) {
                    $shortcode_param = '';
                    foreach ($_POST as $k => $v) {
                        $shortcode_param .= $k . '="' . $v . '" ';
                    }

                    if($_POST['is_paid_post']==1){
                        echo do_shortcode("[arm_purchased_paid_post_list $shortcode_param]");    
                    }else{
                        echo do_shortcode("[arm_membership $shortcode_param]");
                    }
                    
                    exit;
                }
            }
        }

        function arm_member_transaction_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $default_transaction_fields = __('Transaction ID', 'ARMember') . ',' . __('Invoice ID', 'ARMember') . ',' . __('Plan', 'ARMember') . ',' . __('Payment Gateway', 'ARMember') . ',' . __('Payment Type', 'ARMember') . ',' . __('Transaction Status', 'ARMember') . ',' . __('Amount', 'ARMember') . ',' . __('Used Coupon Code', 'ARMember') . ',' . __('Used Coupon Discount', 'ARMember') . ',' . __('Payment Date', 'ARMember') . ',' . __('TAX Percentage', 'ARMember') . ',' . __('TAX Amount', 'ARMember');
            $defaults = array(
                'user_id' => '',
                'title' => __('Transactions', 'ARMember'),
                'current_page' => 0,
                'per_page' => 5,
                'message_no_record' => __('There is no any Transactions found', 'ARMember'),
                'label' => 'transaction_id,invoice_id,plan,payment_gateway,payment_type,transaction_status,amount,used_coupon_code,used_coupon_discount,payment_date',
                'value' => $default_transaction_fields,
                'display_invoice_button' => 'true',
                'view_invoice_text' => __('View Invoice', 'ARMember'),
                'view_invoice_css' => '',
                'view_invoice_hover_css' => '',
                'order_id_text' => __('Order ID', 'ARMember'),
            );

            /* Extract Shortcode Attributes */
            $args = shortcode_atts($defaults, $atts, $tag);

            extract($args);
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $current_site, $arm_errors, $ARMember, $arm_transaction, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $bpopup_loaded, $arm_invoice_tax_feature;
            $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
            $bpopup_loaded = 1;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();
            $labels = explode(',', rtrim($args['label'], ','));
            $values = explode(',', rtrim($args['value'], ','));

            if (is_user_logged_in()) {
                $user_id = $args['user_id'];
                if (empty($user_id) || $user_id == 0 || $user_id == 'current') {
                    $user_id = get_current_user_id();
                }
                wp_enqueue_style('arm_form_style_css');
                $offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
		$transaction_container_class = 'arm_transactions_container';
                $is_paid_post = 0;

                if($tag=="arm_paid_post_member_transaction"){
                    $is_paid_post = 1;
		    $transaction_container_class = 'arm_paid_post_transactions_container';   
                }
		
		$trans_count = $arm_transaction->arm_get_total_transaction($user_id,$is_paid_post);
                $transactions = $arm_transaction->arm_get_all_transaction($user_id, $offset, $per_page,$is_paid_post);   

                $content = apply_filters('arm_before_member_transaction_shortcode_content', $content, $args);
                $content .= "<div class='{$transaction_container_class}' id='arm_tm_container'>";
                $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
                $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
                $content .= '<style type="text/css">';
                $transactionsWrapperClass = ".{$transaction_container_class}";

                if (empty($view_invoice_css)) {
                    $content .= " $transactionsWrapperClass .arm_transaction_list_item .arm_view_invoice_button{ text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $transactionsWrapperClass .arm_transaction_list_item .arm_view_invoice_button{" . $this->arm_br2nl($view_invoice_css) . "}";
                }

                if (empty($view_invoice_hover_css)) {
                    $content .= " $transactionsWrapperClass .arm_transaction_list_item .arm_view_invoice_button:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $transactionsWrapperClass .arm_transaction_list_item .arm_view_invoice_button:hover{" . $this->arm_br2nl($view_invoice_hover_css) . "}";
                }

                $content .= "
						$transactionsWrapperClass .arm_transactions_heading_main{
							{$frontfontstyle['frontOptions']['level_1_font']['font']}
						}
						$transactionsWrapperClass .arm_transaction_list_header th{
							{$frontfontstyle['frontOptions']['level_2_font']['font']}
						}
						$transactionsWrapperClass .arm_transaction_list_item td{
                            {$frontfontstyle['frontOptions']['level_3_font']['font']}
                        }
                        .{$transaction_container_class} .arm_paging_wrapper .arm_paging_info,
                        .{$transaction_container_class} .arm_paging_wrapper .arm_paging_links a{
							{$frontfontstyle['frontOptions']['level_4_font']['font']}
						}";
                $content .= '</style>';
                if (!empty($title)) {
                    $content .= '<div class="arm_transactions_heading_main" id="arm_tm_heading_main">' . $title . '</div>';
                    $content .= '<div class="armclear"></div>';
                }
                $content .= '<form method="POST" action="#" class="arm_transaction_form_container">';
                $content .= '<input type="hidden" name="is_paid_post" value="'.$is_paid_post.'">';
                $content .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';
                $content .= "<div class='arm_transactions_wrapper' id='arm_tm_wrapper'>";
                $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                $general_settings = $all_global_settings['general_settings'];
                $enable_tax= isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;
                if (!empty($transactions)) {
                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                    $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
                    $global_currency_sym = isset($all_currencies) ? $all_currencies[strtoupper($global_currency)] : '';
                    if (is_rtl()) {
                        $is_transaction_class_rtl = 'is_transaction_class_rtl';
                    } else {
                        $is_transaction_class_rtl = '';
                    }
                    $content .= "<div class='arm_transaction_content " . $is_transaction_class_rtl . "' id='arm_tm_content' style='overflow-x: auto;'>";
                    $content .= "<table class='arm_user_transaction_list_table arm_front_grid' id='arm_tm_table' cellpadding='0' cellspacing='0' border='0'>";
                    $content .= "<thead>";
                    $content .= "<tr class='arm_transaction_list_header' id='arm_tm_list_header'>";
                    $has_transaction_id = true;
                    $has_invoice_id = true;
                    $has_plan = true;
                    $has_payment_gateway = true;
                    $has_payment_type = true;
                    $has_transaction_status = true;
                    $has_amount = true;
                    $has_used_coupon_code = true;
                    $has_used_coupon_discount = true;
                    $has_payment_date = true;
                    $has_tax_percentage = true;
                    $has_tax_amount = true;
                    $has_action = false;

                    if($display_invoice_button == 'true' && $arm_invoice_tax_feature == 1){
                        $has_action = true;
                    }

                    if (in_array('transaction_id', $labels)) {
                        $label_key = array_search('transaction_id', $labels);
                        $l_transID = !empty($values[$label_key]) ? $values[$label_key] : __('Transaction ID', 'ARMember');
                    } else {
                        $has_transaction_id = false;
                    }
                    if (in_array('invoice_id', $labels)) {
                        $label_key = array_search('invoice_id', $labels);
                        $l_invID = !empty($values[$label_key]) ? $values[$label_key] : __('Invoice ID', 'ARMember');
                    } else {
                        $has_invoice_id = false;
                    }

                    if (in_array('plan', $labels)) {
                        $label_key = array_search('plan', $labels);
                        $l_plan = !empty($values[$label_key]) ? $values[$label_key] : __('Plan', 'ARMember');
                    } else {
                        $has_plan = false;
                    }
                    if (in_array('payment_gateway', $labels)) {
                        $label_key = array_search('payment_gateway', $labels);
                        $l_pg = !empty($values[$label_key]) ? $values[$label_key] : __('Payment Gateway', 'ARMember');
                    } else {
                        $has_payment_gateway = false;
                    }

                    if (in_array('payment_type', $labels)) {
                        $label_key = array_search('payment_type', $labels);
                        $l_pType = !empty($values[$label_key]) ? $values[$label_key] : __('Payment Type', 'ARMember');
                    } else {
                        $has_payment_type = false;
                    }
                    if (in_array('transaction_status', $labels)) {
                        $label_key = array_search('transaction_status', $labels);
                        $l_transStatus = !empty($values[$label_key]) ? $values[$label_key] : __('Transaction Status', 'ARMember');
                    } else {
                        $has_transaction_status = false;
                    }
                    if (in_array('amount', $labels)) {
                        $label_key = array_search('amount', $labels);
                        $l_amount = !empty($values[$label_key]) ? $values[$label_key] : __('Amount', 'ARMember');
                    } else {
                        $has_amount = false;
                    }
                    if (in_array('used_coupon_code', $labels)) {
                        $label_key = array_search('used_coupon_code', $labels);
                        $l_coupon = !empty($values[$label_key]) ? $values[$label_key] : __('Used Coupon Code', 'ARMember');
                    } else {
                        $has_used_coupon_code = false;
                    }
                    if (in_array('used_coupon_discount', $labels)) {
                        $label_key = array_search('used_coupon_discount', $labels);
                        $l_couponDiscount = !empty($values[$label_key]) ? $values[$label_key] : __('Used Couupon DIscount', 'ARMember');
                    } else {
                        $has_used_coupon_discount = false;
                    }
                    if (in_array('payment_date', $labels)) {
                        $label_key = array_search('payment_date', $labels);
                        $l_pDate = !empty($values[$label_key]) ? $values[$label_key] : __('Payment Date', 'ARMember');
                    } else {
                        $has_payment_date = false;
                    }
                    if (in_array('tax_percentage', $labels)) {
                        $label_key = array_search('tax_percentage', $labels);
                        $l_tPer = !empty($values[$label_key]) ? $values[$label_key] : __('TAX Percentage', 'ARMember');
                    } else {
                        $has_tax_percentage = false;
                    }
                    if (in_array('tax_amount', $labels)) {
                        $label_key = array_search('tax_amount', $labels);
                        $l_pAmt = !empty($values[$label_key]) ? $values[$label_key] : __('TAX Amount', 'ARMember');
                    } else {
                        $has_tax_amount = false;
                    }
                    if ($has_transaction_id) :
                        $content .= "<th class='arm_transaction_th' id='arm_tm_transid'>{$l_transID}</th>";
                    endif;
                    if ($has_invoice_id) :
                        $content .= "<th class='arm_transaction_th' id='arm_tm_invid'>{$l_invID}</th>";
                    endif;
                    if ($has_plan):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_plan'>{$l_plan}</th>";
                    endif;
                    if ($has_payment_gateway):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_payment_gateway'>{$l_pg}</th>";
                    endif;
                    if ($has_payment_type):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_payment_type'>{$l_pType}</th>";
                    endif;
                    if ($has_transaction_status):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_status'>{$l_transStatus}</th>";
                    endif;
                    if ($has_amount):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_amount'>{$l_amount}</th>";
                    endif;
                    if ($has_used_coupon_code):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_used_coupon_code'>{$l_coupon}</th>";
                    endif;
                    if ($has_used_coupon_discount):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_used_coupon_discount'>{$l_couponDiscount}</th>";
                    endif;
                    if ($has_payment_date):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_payment_date'>{$l_pDate}</th>";
                    endif;

                    if($enable_tax){
                    if ($has_tax_percentage):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_tax_percentage'>{$l_tPer}</th>";
                    endif;
                       
                    if ($has_tax_amount):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_tax_amount'>{$l_pAmt}</th>";
                    endif;
                    }
                    if ($has_action):
                        $content .= "<th class='arm_transaction_th' id='arm_tm_payment_action'></th>";
                    endif;

                    $content .= "</tr>";
                    $content .= "</thead>";
                    foreach ($transactions as $r) {

                        $r = (object) $r;

                        $currency = (!empty($r->arm_currency) && isset($all_currencies[strtoupper($r->arm_currency)])) ? $all_currencies[strtoupper($r->arm_currency)] : $global_currency_sym;
                        $content .="<tr class='arm_transaction_list_item' id='arm_transaction_list_item_" . $r->arm_transaction_id . "'>";
                        if ($has_transaction_id) :
                            $content .="<td data-label='{$l_transID}'>";
                            if (!empty($r->arm_transaction_id)) {
                                $content .= $r->arm_transaction_id;
                                $arm_token_transaction = $arm_transaction->arm_get_single_transaction($r->arm_log_id);
                                if(!empty($arm_token_transaction))
                                {
                                    $arm_order_id = '';
                                    if($arm_token_transaction['arm_payment_mode']=='auto_debit_subscription' && $r->arm_payment_gateway=='2checkout')
                                    {
                                        
                                        $arm_order_id = '<b>'.$order_id_text.':</b> '.$arm_token_transaction['arm_token'];
                                        $content .= '<br>'.$arm_order_id;
                                    }
                                }
                            } else {
                                $content .= __('Manual', 'ARMember');
                            }
                            $content .="</td>";
                        endif;
                        if ($has_invoice_id):
                            $log_type = ($r->arm_payment_gateway == 'bank_transfer') ? 'bt_log' : 'other';

                            $arm_invoice_id = $arm_global_settings->arm_manipulate_invoice_id(((!empty($r->arm_invoice_id)) ? $r->arm_invoice_id : 0));

                            if(($r->arm_transaction_status == 'success' || $r->arm_transaction_status==1) && $arm_invoice_tax_feature == 1 ){
                                $content .="<td data-label='{$l_invID}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'><a class='armhelptip arm_front_invoice_detail' href='javascript:void(0)' data-log_type='" . $log_type . "' data-log_id='" . $r->arm_log_id . "' title='" . __('View Invoice', 'ARMember') . "'>" . $arm_invoice_id . "</a></td>";
                            }
                            else{
                                $content .="<td data-label='{$l_invID}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>" . $arm_invoice_id . "</td>";
                            }

                            
                        endif;
                        if ($has_plan):
                            $content .="<td data-label='{$l_plan}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>" . $arm_subscription_plans->arm_get_plan_name_by_id($r->arm_plan_id) . "</td>";
                        endif;
                        if ($has_payment_gateway):
                            $content .="<td data-label='{$l_pg}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>" . $arm_payment_gateways->arm_gateway_name_by_key($r->arm_payment_gateway) . "</td>";
                        endif;
                        if ($has_payment_type):
                            $payment_type = (isset($r->arm_payment_type) && $r->arm_payment_type == 'subscription') ? __('Subscription', 'ARMember') : __('One Time', 'ARMember');
                            $arm_is_trial = (isset($r->arm_is_trial) && $r->arm_is_trial == 1) ? ' '.__('(Trial Transaction)', 'ARMember') : '';
                            $content .="<td data-label='{$l_pType}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>" . $payment_type . $arm_is_trial . "</td>";
                        endif;
                        if ($has_transaction_status):
                            $arm_transaction_status = $r->arm_transaction_status;
                            switch ($arm_transaction_status) {
                                case '0':
                                    $arm_transaction_status = 'pending';
                                    break;
                                case '1':
                                    $arm_transaction_status = 'success';
                                    break;
                                case '2':
                                    $arm_transaction_status = 'canceled';
                                    break;
                                default:
                                    $arm_transaction_status = $r->arm_transaction_status;
                                    break;
                            }
                            $arm_transaction_status_html = $arm_transaction->arm_get_transaction_status_text($arm_transaction_status);
                            $content .="<td data-label='{$l_transStatus}' id='arm_transaction_list_item_td_" . $arm_transaction_status . "'>" . $arm_transaction_status_html . "</td>";
                        endif;
                        if ($has_amount):
                            $content .="<td data-label='{$l_amount}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>";
                            $extraVars = (!empty($r->arm_extra_vars)) ? maybe_unserialize($r->arm_extra_vars) : array();
                            if (!empty($extraVars) && !empty($extraVars['plan_amount']) && $extraVars['plan_amount'] != 0 && $extraVars['plan_amount'] != $r->arm_amount) {
                                $content .= '<span class="arm_transaction_list_plan_amount">' . $arm_payment_gateways->arm_prepare_amount($r->arm_currency, $extraVars['plan_amount']) . '</span>';
                            }
                            $content .= '<span class="arm_transaction_list_paid_amount">';
                            if (!empty($r->arm_amount) && $r->arm_amount > 0) {
                                $content .= $arm_payment_gateways->arm_prepare_amount($r->arm_currency, $r->arm_amount);
                                if ($global_currency_sym == $currency && strtoupper($global_currency) != strtoupper($r->arm_currency)) {
                                    $content .= " (" . strtoupper($r->arm_currency) . ")";
                                }
                            } else {
                                $content .= $arm_payment_gateways->arm_prepare_amount($r->arm_currency, $r->arm_amount);
                            }
                            $content .= '</span>';
                            if (!empty($extraVars) && isset($extraVars['trial'])) {
                                $trialInterval = $extraVars['trial']['interval'];
                                $content .= '<span class="arm_transaction_list_trial_text">';
                                $content .= __('Trial Period', 'ARMember') . ": {$trialInterval} ";
                                if ($extraVars['trial']['period'] == 'Y') {
                                    $content .= ($trialInterval > 1) ? __('Years', 'ARMember') : __('Year', 'ARMember');
                                } elseif ($extraVars['trial']['period'] == 'M') {
                                    $content .= ($trialInterval > 1) ? __('Months', 'ARMember') : __('Month', 'ARMember');
                                } elseif ($extraVars['trial']['period'] == 'W') {
                                    $content .= ($trialInterval > 1) ? __('Weeks', 'ARMember') : __('Week', 'ARMember');
                                } elseif ($extraVars['trial']['period'] == 'D') {
                                    $content .= ($trialInterval > 1) ? __('Days', 'ARMember') : __('Day', 'ARMember');
                                }
                                $content .= '</span>';
                            }
                            $content .= "</td>";
                        endif;
                        if ($has_used_coupon_code):
                            $content .="<td data-label='{$l_coupon}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>";
                            if (!empty($r->arm_coupon_code)) {
                                $content .= $r->arm_coupon_code;
                            } else {
                                $content .= '-';
                            }
                            $content .= "</td>";
                        endif;
                        if ($has_used_coupon_discount):
                            $content .="<td data-label='{$l_couponDiscount}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>";
                            if (!empty($r->arm_coupon_code)) {
                                if (!empty($r->arm_coupon_discount) && $r->arm_coupon_discount > 0) {
                                    $content .= number_format((float) $r->arm_coupon_discount, 2);
                                    $discount_type = ($r->arm_coupon_discount_type != "percentage") ? ' ' . $r->arm_coupon_discount_type : '%';
                                    $content .= $discount_type;
                                } else {
                                    $content .= '0.00';
                                }
                            } else {
                                $content .= '-';
                            }
                            $content .= "</td>";
                        endif;
                        if ($has_payment_date):
                            $content .="<td data-label='{$l_pDate}' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>" . date_i18n($date_time_format, strtotime($r->arm_created_date)) . "</td>";
                        endif;

                        if($enable_tax){
                        if ($has_tax_percentage) {
                            $content .="<td data-label='{$l_tPer}' id='tax_percentage_".$r->arm_transaction_id."'>";
                            if (!empty($extraVars) && isset($extraVars['tax_percentage']) &&  $extraVars['tax_percentage']!= "" ) {
                                $content .= $extraVars['tax_percentage'] . "%";
                            }
    			    else{
    			    	$content .= '-';
    			    }
                                $content .="</td>";
                            }
                            if ($has_tax_amount) {
                                $content .="<td data-label='{$l_tPer}' id='tax_amount_".$r->arm_transaction_id."'>";
                                if (!empty($extraVars) && isset($extraVars['tax_amount']) && $extraVars['tax_amount'] != "" ) {
                                    $content .= $arm_payment_gateways->arm_prepare_amount($r->arm_currency, $extraVars['tax_amount']);
                                }
    			    else{
    			    	$content .= '-';
    			    }
                                $content .= "</td>";
                            }
                        }
                            if ($has_action):
                                $content .="<td data-label='".__('Payment Action', 'ARMember')."' id='arm_transaction_list_item_td_" . $r->arm_transaction_id . "'>";
                            $log_type = ($r->arm_payment_gateway == 'bank_transfer') ? 'bt_log' : 'other';
                            if($r->arm_transaction_status == 'success' || $r->arm_transaction_status == 1){
                             $view_invoice_content = '<button type="button" class= "arm_view_invoice_button arm_front_invoice_detail" data-log_id="'.$r->arm_log_id.'" data-log_type="'.$log_type.'" >' . $view_invoice_text . '</button>';
                         }
                         else{
                            $view_invoice_content = '';
                         }

                              $content .= $view_invoice_content;
                                $content .="</td>";

                            endif;
                            $content .="</tr>";
                        }
                        $content .= "</table>";

                        $content .= "</div>";
                        $transPaging = $arm_global_settings->arm_get_paging_links($current_page, $trans_count, $per_page, 'transaction');
                        $content .= "<div class='arm_transaction_paging_container " . $is_transaction_class_rtl . "'>" . $transPaging . "</div>";
                } else {
                    if (is_rtl()) {
                        $is_transaction_class_rtl = 'is_transaction_class_rtl';
                    } else {
                        $is_transaction_class_rtl = '';
                    }
                    $content .= "<div class='arm_transaction_content " . $is_transaction_class_rtl . "' style='overflow-x: auto;' >";
                    $content .= "<table class='arm_user_transaction_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0' style='border-collapse:unset;'>";
                    $content .= "<thead>";
                    $content .= "<tr class='arm_transaction_list_header'>";
                    $has_transaction_id = true;
                    $has_invoice_id = true;
                    $has_plan = true;
                    $has_payment_gateway = true;
                    $has_payment_type = true;
                    $has_transaction_status = true;
                    $has_amount = true;
                    $has_used_coupon_code = true;
                    $has_used_coupon_discount = true;
                    $has_payment_date = true;

                    if (in_array('transaction_id', $labels)) {
                        $label_key = array_search('transaction_id', $labels);
                        $l_transID = $values[$label_key];
                    } else {
                        $has_transaction_id = false;
                    }

                    if (in_array('invoice_id', $labels)) {
                        $label_key = array_search('invoice_id', $labels);
                        $l_invID = $values[$label_key];
                    } else {
                        $has_invoice_id = false;
                    }

                    if (in_array('plan', $labels)) {
                        $label_key = array_search('plan', $labels);
                        $l_plan = $values[$label_key];
                    } else {
                        $has_plan = false;
                    }
                    if (in_array('payment_gateway', $labels)) {
                        $label_key = array_search('payment_gateway', $labels);
                        $l_pg = $values[$label_key];
                    } else {
                        $has_payment_gateway = false;
                    }
                    if (in_array('payment_type', $labels)) {
                        $label_key = array_search('payment_type', $labels);
                        $l_pType = $values[$label_key];
                    } else {
                        $has_payment_type = false;
                    }
                    if (in_array('transaction_status', $labels)) {
                        $label_key = array_search('transaction_status', $labels);
                        $l_transStatus = $values[$label_key];
                    } else {
                        $has_transaction_status = false;
                    }
                    if (in_array('amount', $labels)) {
                        $label_key = array_search('amount', $labels);
                        $l_amount = $values[$label_key];
                    } else {
                        $has_amount = false;
                    }
                    if (in_array('used_coupon_code', $labels)) {
                        $label_key = array_search('used_coupon_code', $labels);
                        $l_coupon = $values[$label_key];
                    } else {
                        $has_used_coupon_code = false;
                    }
                    if (in_array('used_coupon_discount', $labels)) {
                        $label_key = array_search('used_coupon_discount', $labels);
                        $l_couponDiscount = $values[$label_key];
                    } else {
                        $has_used_coupon_discount = false;
                    }
                    if (in_array('payment_date', $labels)) {
                        $label_key = array_search('payment_date', $labels);
                        $l_pDate = $values[$label_key];
                    } else {
                        $has_payment_date = false;
                    }
                    $i = 0;
                    if ($has_transaction_id) :
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_transID}</th>";
                    endif;
                    if ($has_invoice_id) :
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_invID}</th>";
                    endif;
                    if ($has_plan):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_plan}</th>";
                    endif;
                    if ($has_payment_gateway):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_pg}</th>";
                    endif;
                    if ($has_payment_type):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_pType}</th>";
                    endif;
                    if ($has_transaction_status):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_transStatus}</th>";
                    endif;
                    if ($has_amount):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_amount}</th>";
                    endif;
                    if ($has_used_coupon_code):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_coupon}</th>";
                    endif;
                    if ($has_used_coupon_discount):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_couponDiscount}</th>";
                    endif;
                    if ($has_payment_date):
                        $i++;
                        $content .= "<th class='arm_sortable_th'>{$l_pDate}</th>";
                    endif;
                    $content .= "</tr>";
                    $content .= "</thead>";
                    $content .="<tr class='arm_transaction_list_item'>";
                    $content .="<td colspan='" . $i . "' class='arm_no_transaction'>$message_no_record</td>";
                    $content .="</tr>";
                    $content .= "</table>";
                    $content .= "</div>";
                }
                $content .= "</div>";
                $content .= "<div class='armclear'></div>";
                /* Template Arguments Inputs */
                foreach (array('user_id', 'title', 'per_page', 'message_no_record', 'label', 'value','view_invoice_text','view_invoice_css','view_invoice_hover_css') as $k) {
                    $content .= '<input type="hidden" class="arm_trans_field_' . $k . '" name="' . $k . '" value="' . $args[$k] . '">';
                }
                $content .= '</form>';
                $content .= '<script data-cfasync="false" type="text/javascript">jQuery(document).ready(function ($) { if (typeof arm_transaction_init == "function") { arm_transaction_init(); } });</script>';
                $content .= "</div><div class='arm_invoice_detail_container'></div>";
                $content = apply_filters('arm_after_member_transaction_shortcode_content', $content, $args);
            }
            return do_shortcode($content);
        }

        function arm_account_detail_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'section' => 'profile', /* Values:-> `profile,membership,transactions,close_account,logout` */
                'show_change_subscription' => false,
                'change_subscription_url' => '',
                'fields' => '',
                'social_fields' => '',
                'label' => 'first_name,last_name,user_login,user_email',
                'value' => 'First Name,Last Name,Username,Email',
                    ), $atts, $tag);
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $current_site, $ARMember, $arm_member_forms, $arm_global_settings, $arm_social_feature, $arm_members_activity;
            $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
            $profileTabTxt = __('Profile', 'ARMember');
            $membershipTabTxt = __('Membership', 'ARMember');
            $transactionTabTxt = __('Transactions', 'ARMember');
            $closeaccountTabTxt = __('Close Account', 'ARMember');

            $is_user_logged_in = is_user_logged_in();
            if ($is_user_logged_in) {
                $user_id = get_current_user_id();
                $defaultTabSettings = array(
                    'profile' => $profileTabTxt,
                    'membership' => $membershipTabTxt,
                    'transactions' => $transactionTabTxt,
                    'close_account' => $closeaccountTabTxt,
                );
                $atts['section'] = strtolower(str_replace(' ', '', $atts['section']));
                $show_subscription = ($atts['show_change_subscription'] === 'true') ? true : false;
                $sections = (!empty($atts['section'])) ? explode(',', $atts['section']) : array('profile');
                $sections = $ARMember->arm_array_trim($sections);
                $sections = $ARMember->arm_array_unique($sections);
                $displaySections = array();
                if (!empty($sections)) {
                    foreach ($defaultTabSettings as $tab => $title) {
                        if (in_array($tab, $sections)) {
                            $displaySections[] = $tab;
                        }
                    }
                } else {
                    $displaySections[] = 'profile';
                }
                $content = apply_filters('arm_change_account_details_before_display', $content, $atts);
                $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
                $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';

                $content .= '<style type="text/css">';
                $accountWrapperClass = ".arm_account_detail_wrapper";
                $content .= "
					$accountWrapperClass .arm_account_detail_tab_heading{
						{$frontfontstyle['frontOptions']['level_1_font']['font']}
					}
					$accountWrapperClass .arm-form-table-label,
					$accountWrapperClass .arm_account_link_tab a,
					$accountWrapperClass .arm_account_btn_tab a,
					$accountWrapperClass .arm_transaction_list_header th,
					$accountWrapperClass .arm_transactions_container table td:before,
					$accountWrapperClass .arm_form_field_label_text{
						{$frontfontstyle['frontOptions']['level_2_font']['font']}
					}
					$accountWrapperClass .arm-form-table-content,
					$accountWrapperClass .arm_transaction_list_item td,
					$accountWrapperClass .arm_close_account_message,
					$accountWrapperClass .arm_form_input_box{
						{$frontfontstyle['frontOptions']['level_3_font']['font']}
					}
					$accountWrapperClass .arm_details_activity,
					$accountWrapperClass .arm_time_section,
					$accountWrapperClass .arm_paging_wrapper,
					$accountWrapperClass .arm_empty_box_warning,
					$accountWrapperClass .arm_count_txt{
						{$frontfontstyle['frontOptions']['level_4_font']['font']}
					}
					$accountWrapperClass .arm_member_detail_action_links a,
					$accountWrapperClass .arm_activity_display_name a,
					$accountWrapperClass .arm_activity_other_links,	
					$accountWrapperClass .arm_activity_other_links a,
					$accountWrapperClass .arm_member_info_right a{
						{$frontfontstyle['frontOptions']['link_font']['font']}
					}
					$accountWrapperClass .arm_paging_wrapper .arm_paging_links a{
						{$frontfontstyle['frontOptions']['link_font']['font']}
					}
					
				";
                $content .= '</style>';
                if (is_rtl()) {
                    $is_account_detail_class_rtl = 'is_account_detail_class_rtl';
                } else {
                    $is_account_detail_class_rtl = '';
                }
                $content .= '<div class="arm_account_detail_wrapper ' . $is_account_detail_class_rtl . '">';
                if (count($displaySections) == 1) {
                    $content .= "<div class='arm_account_detail_tab_content_wrapper' style='border:1px solid #dee3e9;'>";
                    $content .= '<div class="arm_account_detail_tab arm_account_detail_tab_content arm_account_content_active" data-tab="' . $displaySections[0] . '">';
                    if ($tab == 'membership') {
                        $content .= $this->arm_account_detail_tab_content($displaySections[0], $user_id, $show_subscription);
                    } else {

                        $content .= $this->arm_account_detail_tab_content($displaySections[0], $user_id, false, $atts['fields'], $atts['social_fields'], array(), array(), $atts);
                    }
                    $content .= '</div>';
                    $content .= '</div>';
                } else {
                    $tabLinks = $tabContent = $tabContentActiveClass = '';
                    $i = 0;
                    foreach ($displaySections as $tab) {
                        $tabLinkClass = 'arm_account_link_tab';
                        $tabBtnClass = 'arm_account_btn_tab';
                        $tabContentActiveClass = 'arm_account_content_right';
                        if ($i == 0) {
                            $tabLinkClass .= ($i == 0) ? ' arm_account_link_tab_active' : '';
                            $tabBtnClass .= ($i == 0) ? ' arm_account_btn_tab_active' : '';
                            $tabContentActiveClass = 'arm_account_content_active';
                        }
                        $tabLinks .= '<li class="' . $tabLinkClass . '" data-tab="' . $tab . '">';
                        $tabLinks .= '<a href="javascript:void(0)">' . $defaultTabSettings[$tab] . '</a>';
                        $tabLinks .= '</li>';
                        $tabContent .= '<div class="' . $tabBtnClass . '" data-tab="' . $tab . '"><a href="javascript:void(0)">' . $defaultTabSettings[$tab] . '</a></div>';
                        $tabContent .= '<div class="arm_account_detail_tab arm_account_detail_tab_content ' . $tabContentActiveClass . '" data-tab="' . $tab . '">';
                        if ($tab == 'membership') {
                            $tabContent .= $this->arm_account_detail_tab_content($tab, $user_id, $show_subscription);
                        } else {
                            $tabContent .= $this->arm_account_detail_tab_content($tab, $user_id);
                        }
                        $tabContent .= '</div>';
                        $i++;
                    }
                    $tabLinks .= '<li class="arm_account_slider"></li>';
                    $content .= '<div class="arm_account_tabs_wrapper">';
                    $content .= '<div class="arm_account_detail_tab_links"><ul>' . $tabLinks . '</ul></div>';
                    $content .= '<div class="arm_account_detail_tab_content_wrapper">' . $tabContent . '</div>';
                    $content .= '</div>';
                }
                $content .= '</div>';
                $content = apply_filters('arm_change_account_details_after_display', $content, $atts);
            } else {
                $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');

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
                if (preg_match_all('/arm_redirect/', $login_page_url, $match) < 2) {
                    wp_redirect($login_page_url);
                }
            }
            return $content;
        }

        function arm_account_detail_tab_content($tab, $user_id = 0, $show_subscription = false, $fields = '', $social_fields = '', $renew_subscription_options = array(), $cancel_subscription_options = array(), $atts = array()) {
            global $wp, $wpdb, $current_user, $current_site, $ARMember, $arm_member_forms, $arm_global_settings;
            if (empty($renew_subscription_options)) {
                $renew_subscription_options['display_renew_btn'] = "true";
                $renew_subscription_options['renew_text'] = __('Renew', 'ARMember');
                $renew_subscription_options['renew_url'] = '';
                $renew_subscription_options['renew_css'] = '';
                $renew_subscription_options['renew_hover_css'] = '';
            }

            if (empty($cancel_subscription_options)) {
                $cancel_subscription_options['display_cancel_btn'] = "true";
                $cancel_subscription_options['cancel_text'] = __('Cancel', 'ARMember');

                $cancel_subscription_options['cancel_css'] = '';
                $cancel_subscription_options['cancel_hover_css'] = '';
            }

            $content = $tabTitle = $tabTitleLinks = $tabContent = '';
            $global_settings = $arm_global_settings->global_settings;
            switch ($tab) {
                case 'profile':
                    $tabTitle = __('Profile Detail', 'ARMember');
                    $tabContent = do_shortcode("[arm_view_profile fields='{$fields}' label='{$atts["label"]}' value='{$atts["value"]}' social_fields='{$social_fields}']");
                    if (isset($global_settings['edit_profile_page_id']) && $global_settings['edit_profile_page_id'] != 0) {
                        $editProfilePage = $arm_global_settings->arm_get_permalink('', $global_settings['edit_profile_page_id']);
                        $tabTitleLinks .= '<a href="' . $editProfilePage . '" class="arm_front_edit_member_link">' . __("Edit Profile", 'ARMember') . '</a>';
                    }
                    /* $tabTitleLinks .= do_shortcode('[arm_logout label="Logout" type="link" user_info="false" redirect_to="' . ARM_HOME_URL . '"]'); */
                    break;
                case 'membership':
                    $tabTitle = ( isset($atts['title']) && !empty($atts['title'])) ? $atts['title'] : __('Current Membership', 'ARMember');
                    $label = "label=''";
                    $value = "value=''";


                    if (isset($atts) && !empty($atts)) {
                        $label = "label='" . $atts['membership_label'] . "'";
                        $value = "value='" . $atts['membership_value'] . "'";
                    }

                    $display_renew_btn = "display_renew_button='" . $renew_subscription_options['display_renew_btn'] . "'";
                    $renew_text = "renew_text='" . $renew_subscription_options['renew_text'] . "'";
                    $renew_url = "renew_url='" . $renew_subscription_options['renew_url'] . "'";
                    $renew_css = "renew_css='" . $renew_subscription_options['renew_css'] . "'";
                    $renew_hover_css = "renew_hover_css='" . $renew_subscription_options['renew_hover_css'] . "'";


                    $display_cancel_btn = "display_cancel_button='" . $cancel_subscription_options['display_cancel_btn'] . "'";
                    $cancel_text = "cancel_text='" . $cancel_subscription_options['cancel_text'] . "'";

                    $cancel_css = "cancel_css='" . $cancel_subscription_options['cancel_css'] . "'";
                    $cancel_hover_css = "cancel_hover_css='" . $cancel_subscription_options['cancel_hover_css'] . "'";

                    $shortcode = '[arm_subscription_detail ' . $label . ' ' . $value . ' ' . $display_renew_btn . ' ' . $renew_text . ' ' . $renew_url . ' ' . $renew_css . ' ' . $renew_hover_css . ' ' . $display_cancel_btn . ' ' . $cancel_text . ' ' . $cancel_css . ' ' . $cancel_hover_css . ']';
                    $tabContent = do_shortcode($shortcode);


                    if ($show_subscription) {
                        $tabTitleLinks = '<a href="' . $change_subscription_url . '" class="arm_front_edit_subscriptions_link">' . __("Change Subscription", 'ARMember') . '</a>';
                    }

                    break;
                case 'transactions':
                    $tabTitle = __('Transaction History', 'ARMember');
                    $noRecordText = __('There is no any Transactions found', 'ARMember');
                    $tabContent = do_shortcode('[arm_member_transaction user_id="' . $user_id . '" title="" message_no_record="' . $noRecordText . '"]');
                    break;
                case 'close_account':
                    $tabTitle = __('Close Account', 'ARMember');
                    $tabContent = do_shortcode('[arm_close_account]');
                    break;
                case 'logout':
                    $tabContent = do_shortcode('[arm_logout label="Logout" type="link" user_info="false" redirect_to="' . ARM_HOME_URL . '"]');
                    break;
                default:
                    break;
            }
            if (!empty($tabTitle)) {
                $content .= '<div class="arm_account_detail_tab_heading">' . $tabTitle . '</div>';
            }
            if (!empty($tabTitleLinks)) {
                $content .= '<div class="arm_account_detail_tab_link_belt arm_member_detail_action_links">' . $tabTitleLinks . '</div>';
            }
            $content .= '<div class="arm_account_detail_tab_body arm_account_detail_tab_' . $tab . '">' . $tabContent . '</div>';

            return $content;
        }

        function arm_view_profile_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            global $arm_global_settings;
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'title' => __('', 'ARMember'),
                'label' => 'first_name,last_name,user_login,user_email',
                'fields' => '',
                'value' => 'First Name,Last Name,Username,Email',
                'social_fields' => '',
                    ), $atts, $tag);

            /* ====================/.End Set Shortcode Attributes./==================== */

            if(!empty($atts['fields'])){

            $display_fields = explode(',', rtrim($atts['fields'], ','));
            $display_fields_value = array();
            }
            else{
            $display_fields = explode(',', rtrim($atts['label'], ','));
            $display_fields_value = explode(',', rtrim($atts['value'], ','));
            }
            $date_time_format = $arm_global_settings->arm_get_wp_date_format();

            $social_fields = explode(',', rtrim($atts['social_fields'], ','));
            global $wp, $wpdb, $wp_roles, $current_user, $current_site, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_social_feature, $arm_members_directory;
            if (is_user_logged_in()) {
                $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
                $user_id = get_current_user_id();
                $user = get_user_by('id', $user_id);
                $user_metas = get_user_meta($user_id);
                $role_names = $wp_roles->get_names();
                $content = '';
                $content .= '<div class="arm_view_profile_wrapper arm_account_detail_block">';
                $content .= '<table class="form-table">';

                if (!empty($display_fields) && !empty($dbFormFields)) {

                    foreach ($dbFormFields as $fieldMeta_key => $fieldOpt) {
                        if (in_array($fieldMeta_key, $display_fields)) {

                            $key = array_search($fieldMeta_key, $display_fields);

                            $fieldMeta_value = (isset($user->$fieldMeta_key) ? $user->$fieldMeta_key : '');
                            $pattern = '/^(date\_(.*))/';

                            if(preg_match($pattern, $fieldMeta_key)){
                                $fieldMeta_value  =  date_i18n($date_time_format, strtotime($fieldMeta_value));
                            }

                            if (is_array($fieldMeta_value)) {
                                $fieldMeta_value = $ARMember->arm_array_trim($fieldMeta_value);
                                $fieldMeta_value = implode(', ', $fieldMeta_value);
                            }
                            $content .= '<tr class="form-field">';
                            $field_label = (isset($display_fields_value[$key]) && !empty($display_fields_value[$key])) ? $display_fields_value[$key] : $fieldOpt['label'];
                            $content .= '<th class="arm-form-table-label">' . stripslashes_deep($field_label)  . ' :</th>';

                            if ($fieldOpt['type'] == 'file' || $fieldOpt['type'] == 'avatar') {
                                if ($fieldMeta_value != '') {
                                    $exp_val = explode("/", $fieldMeta_value);
                                    $filename = $exp_val[count($exp_val) - 1];
                                    $file_extension = explode('.', $filename);
                                    $file_ext = $file_extension[count($file_extension) - 1];
                                    if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF'))) {
                                        $fileUrl = $fieldMeta_value;
                                    } else {
                                        $fileUrl = MEMBERSHIP_IMAGES_URL . '/file_icon.png';
                                    }
                                } else {
                                    $fileUrl = '';
                                }
                                if ($fileUrl != '') {
                                    $content .= '<td class="arm-form-table-content"><a target="__blank" href="' . $fieldMeta_value . '"><img style="max-width: 100px;height: auto;" src="' . $fileUrl . '"></a></td>';
                                } else {
                                    $content .= '<td class="arm-form-table-content">' . $fieldMeta_value . '</td>';
                                }
                            } else if($fieldOpt['type'] == 'url') {
                                $content .= '<td class="arm-form-table-content"><a href="'.$fieldMeta_value.'" target="_blank">' . $fieldMeta_value . '</a></td>';
                            } else if($fieldOpt['type'] == 'select' || $fieldOpt['type'] == 'radio') {
                                if(!empty($fieldMeta_value))
                                {
                                    $arm_tmp_select_val = !empty($fieldOpt['options']) ? $fieldOpt['options'] : '';
                                    foreach($arm_tmp_select_val as $arm_tmp_select_key => $arm_tmp_val)
                                    {
                                        $arm_tmp_select_val_arr = explode(':', $arm_tmp_val);
                                        $arm_tmp_selected_option_val = end($arm_tmp_select_val_arr);
                                        if($arm_tmp_selected_option_val == $fieldMeta_value)
                                        {
                                            $fieldMeta_value = str_replace(':'.$arm_tmp_selected_option_val, '', $arm_tmp_val);
                                            break;
                                        }
                                    }
                                }
                                $content .= "<td class='arm-form-table-content'>".$fieldMeta_value."</td>";
                            } else {
                                $content .= '<td class="arm-form-table-content">' . $fieldMeta_value . '</td>';
                            }
                            $content .= '</tr>';
                        }
                    }
                }
                $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                if (!empty($social_fields) && !empty($socialProfileFields) && $arm_social_feature->isSocialFeature) {
                    foreach ($social_fields as $sfield) {
                        if (isset($socialProfileFields[$sfield])) {
                            $spfMetaKey = 'arm_social_field_' . $sfield;
                            $sfValue = get_user_meta($user_id, $spfMetaKey, true);
                            $content .= '<tr class="form-field">';
                            $content .= '<th class="arm-form-table-label">' . $socialProfileFields[$sfield] . ' :</th>';
                            $content .= '<td class="arm-form-table-content"><a href="'.$sfValue.'" target="_blank">' . $sfValue . '</a></td>';
                            $content .= '</tr>';
                        }
                    }
                }
                $content .= '</table>';
                $content .= '</div>';
            } else {
                $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');
                return do_shortcode("[arm_form id='$default_login_form_id' is_referer='1']");
            }
            return $content;
        }

        function arm_close_account_shortcode_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'title' => __('', 'ARMember'),
                'set_id' => __('', 'ARMember'),
                'css' => __('', 'ARMember'),
                    ), $atts, $tag);


            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $wp_roles, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;

            $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
            $caFormTitle = isset($arm_global_settings->common_message['arm_form_title_close_account']) ? $arm_global_settings->common_message['arm_form_title_close_account'] : '';
            $caFormDesc = isset($arm_global_settings->common_message['arm_form_description_close_account']) ? $arm_global_settings->common_message['arm_form_description_close_account'] : '';
            $passwordFieldLabel = isset($arm_global_settings->common_message['arm_password_label_close_account']) ? $arm_global_settings->common_message['arm_password_label_close_account'] : __('Your Password', 'ARMember');
            $submitBtnTxt = isset($arm_global_settings->common_message['arm_submit_btn_close_account']) ? $arm_global_settings->common_message['arm_submit_btn_close_account'] : __('Submit', 'ARMember');
            $caBlankPassMsg = isset($arm_global_settings->common_message['arm_blank_password_close_account']) ? $arm_global_settings->common_message['arm_blank_password_close_account'] : __('Password cannot be left Blank.', 'ARMember');
            if (is_user_logged_in()) {
                do_action('arm_before_render_close_account_form', $atts);
                $user_id = get_current_user_id();
                $formRandomID = arm_generate_random_code();
                $content = apply_filters('arm_before_close_account_shortcode_content', $content, $atts);
                $validation_pos = 'bottom';
                $field_position = 'left';
                $form_style = array(
                    'form_title_position' => 'left'
                );
                if (!isset($atts['set_id']) || $atts['set_id'] == '') {
                    $setform_settings = $wpdb->get_row("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='login' AND `arm_is_default`='1' ORDER BY arm_form_id DESC LIMIT 1");
                } else {
                    $setform_settings = $wpdb->get_row("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id` = '" . $atts['set_id'] . "' AND `arm_form_type`='login' ORDER BY arm_form_id DESC LIMIT 1");
                    if (empty($setform_settings)) {
                        $setform_settings = $wpdb->get_row("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='login' AND `arm_is_default`='1' ORDER BY arm_form_id DESC LIMIT 1");
                    }
                }
                $set_style_option = maybe_unserialize($setform_settings->arm_form_settings);
                $form_style = $set_style_option['style'];
                $form_style_class = ' arm_form_close_account';
                $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];
                $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                if (is_rtl()) {
                    $form_style_class .= ' arm_form_rtl';
                    $form_style_class .= ' arm_rtl_site';
                } else {
                    $form_style_class .= ' arm_form_ltr';
                }
                $validation_pos = !empty($form_style['validation_position']) ? $form_style['validation_position'] : 'bottom';
                $field_position = !empty($form_style['field_position']) ? $form_style['field_position'] : 'left';
                $content .= $this->arm_close_account_form_style($setform_settings->arm_form_id, $formRandomID);
                if (isset($atts['css']) && $atts['css'] != '') {
                    $content .= '<style>' . $this->arm_br2nl($atts['css']) . '</style>';
                }
                if(!empty($form_style['validation_type']) && $form_style['validation_type'] == 'standard') {
                    $form_style_class .= " arm_standard_validation_type ";
                    $validation_pos = "bottom";
                }
                $content .= '<div class="arm_close_account_container arm_account_detail_block">';
                $content .= '<div class="arm_close_account_form_container arm_form_msg arm_member_form_container">';

                $content .= '<div class="arm_form_message_container">';
                $content .= '<div class="arm_error_msg" id="arm_message_text" style="display:none;"></div>';
                $content .= '<div class="arm_success_msg" id="arm_message_text" style="display:none;"></div>';
                $content .= '</div>';
                $content .= '<form method="post" name="arm_form_ca" id="arm_form' . $formRandomID . '" class="arm_form arm_shortcode_form ' . $form_style_class . '" enctype="multipart/form-data" novalidate data-ng-controller="ARMCtrl" data-ng-cloak="" data-ng-submit="armFormCloseAccountSubmit(arm_form_ca.$valid, \'arm_form' . $formRandomID . '\');" onsubmit="return false;" data-ng-id="close_account">';
                $content .= '<div class="arm_form_inner_container arm_msg_pos_' . $validation_pos . '">';
                $content .= '<div class="arm_form_wrapper_container arm_form_wrapper_container_close_account arm_field_position_' . $field_position . ' arm_front_side_form">';
                if (!empty($caFormTitle)) {
                    $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                    $content .= '<div class="arm_form_heading_container arm_add_other_style armalign' . $form_title_position . '">';
                    $content .= '<span class="arm_form_field_label_wrapper_text">' . $caFormTitle . '</span>';
                    $content .= '</div>';
                }
                if (!empty($caFormDesc)) {
                    $content .= '<div class="arm_close_account_message">' . $caFormDesc . '</div>';
                }
                $content .= '<div class="armclear"></div>';
                $content .= '<div class="arm_form_field_container arm_form_field_container_password" id="arm_form_field_container_password_ca">';
                $content .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_password">';
                $content .= '<div class="arm_member_form_field_label">';
                $content .= '<span class="required_tag">*</span>';
                $content .= '<div class="arm_form_field_label_text">' . $passwordFieldLabel . '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="arm_label_input_separator"></div>';
                $content .= '<div class="arm_form_input_wrapper">';
                $content .= '<div class="arm_form_input_container_password arm_form_input_container">';
                $content .= '<md-input-container class="md-block" flex-gt-sm="">';
                $content .= '<label class="arm_material_label">' . $passwordFieldLabel . '</label>';
                $content .= '<input data-ng-model="arm_form_ca.pass_ca" name="pass" type="password" autocomplete="off" value="" class="arm_form_input_box" required="required" data-msg-required="Password can not be left blank" data-msg-invalid="Please enter valid data">';
                $content .= '<span class="arm_editor_suffix arm_field_fa_icons  arm_visible_password_material "><i class="armfa armfa-eye"></i></span>';
                $content .= '<div data-ng-cloak data-ng-messages="arm_form_ca.pass.$error" data-ng-show="arm_form_ca.pass.$touched" class="arm_error_msg_box ng-scope">';
                $content .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $caBlankPassMsg . '</div>';
                $content .= '<div data-ng-message="invalid" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter valid password', 'ARMember') . '</div>';
                $content .= '</div>';
                $content .= '</md-input-container>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                /* ---------------------------------------------------------- */
                $content .= '<div class="arm_form_field_container arm_form_field_container_submit arm_admin_form_field_container">';
                $content .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_submit"></div>';
                $content .= '<div class="arm_form_input_wrapper">';
                $content .= '<div class="arm_form_input_container_submit arm_form_input_container">';
                $btnAttr = (current_user_can('administrator')) ? 'disabled="disabled"' : '';
                $content .= '<md-button class="arm_form_field_submit_button arm_form_field_container_button arm_close_account_btn" type="submit" ' . $btnAttr . '><span class="arm_spinner">' . file_get_contents(MEMBERSHIP_IMAGES_DIR . "/loader.svg") . '</span>' . $submitBtnTxt . '</md-button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="armclear"></div>';
                $content .= '<input type="hidden" name="arm_action" value="close_account"/>';
                $content .= '<input type="hidden" name="id" value="' . $user_id . '"/>';
                $arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
                $content .= '<input type="hidden" name="_wpnonce" value="' . $arm_wp_nonce . '"/>';
                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content = apply_filters('arm_after_close_account_shortcode_content', $content, $atts);
            } else {
                $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');

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
                if (preg_match_all('/arm_redirect/', $login_page_url, $match) < 2) {
                    wp_redirect($login_page_url);
                }
            }
            $ARMember->enqueue_angular_script();
			
			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
            if($isEnqueueAll == '1'){
                $content .= '<script type="text/javascript" data-cfasync="false">
                                    jQuery(document).ready(function (){
                                        arm_do_bootstrap_angular();
                                    });';
                $content .= '</script>';
            }
			
            return $content;
        }

        function arm_membership_detail_shortcode_func($atts, $content, $tag) {
            global $ARMember, $arm_pay_per_post_feature;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */

            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            $default_membership_fields = __('No.', 'ARMember') . ',' .__('Membership Plan', 'ARMember') . ',' .__('Plan Type', 'ARMember') . ',' . __('Starts On', 'ARMember') . ',' . __('Expires On', 'ARMember') . ',' . __('Cycle Date', 'ARMember') . ',' . __('Action', 'ARMember');
            $atts = shortcode_atts(array(
                'title' => __('Current Membership', 'ARMember'),
                'membership_label' => 'current_membership_no,current_membership_is,current_membership_recurring_profile,current_membership_started_on,current_membership_expired_on,current_membership_next_billing_date,action_button',
                'membership_value' => $default_membership_fields,
                'display_renew_button' => 'true',
                'renew_css' => '',
                'renew_hover_css' => '',
                'renew_text' => __('Renew', 'ARMember'),
                'make_payment_text' => __('Make Payment', 'ARMember'),
                'display_cancel_button' => 'true',
                'cancel_css' => '',
                'cancel_hover_css' => '',
                'cancel_text' => __('Cancel', 'ARMember'),
                'display_update_card_button' => 'true',
                'update_card_css' => '',
                'update_card_hover_css' => '',
                'update_card_text' => __('Update Card', 'ARMember'),
                'setup_id' => '',
                'trial_active' => __('trial active', 'ARMember'),
                'cancel_message' => __('Your Subscription has been cancelled.', 'ARMember'),
                'message_no_record' => __('There is no membership found.', 'ARMember'),
                'current_page' => 0,
                'per_page' => 5,
                    ), $atts, $tag);

            $atts = apply_filters('arm_membership_btn_filter', $atts);

            extract($atts);

            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $current_site, $arm_errors, $arm_member_forms, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_membership_setup;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $labels = explode(',', rtrim($atts['membership_label'], ','));
            $values = explode(',', rtrim($atts['membership_value'], ','));

            if (is_user_logged_in()) {
                $setup_plans = array();
                if (isset($setup_id) && $setup_id > 0) {
                    $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
                    $setup_plans = isset($setup_data['arm_setup_modules']['modules']['plans']) ? $setup_data['arm_setup_modules']['modules']['plans'] : array();
                } else {
                    $setup_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_membership_setup . "` ORDER BY `arm_setup_id`", ARRAY_A);
                    if (!empty($setup_data)) {
                        $setup_id = isset($setup_data['arm_setup_id']) ? $setup_data['arm_setup_id'] : 0;
                        $setup_data['arm_setup_modules'] = maybe_unserialize($setup_data['arm_setup_modules']);
                        $setup_plans = isset($setup_data['arm_setup_modules']['modules']['plans']) ? $setup_data['arm_setup_modules']['modules']['plans'] : array();
                    } else {
                        $setup_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
                    }
                }
                $user_id = get_current_user_id();
                $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plans = !empty($user_plans) ? $user_plans : array();        

                $user_posts = get_user_meta($user_id, 'arm_user_post_ids', true);
                $user_posts = !empty($user_posts) ? $user_posts : array();             
                
                $user_future_plans = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $user_future_plans = !empty($user_future_plans) ? $user_future_plans : array();
                $arm_current_membership_container_class = 'arm_current_membership_container';
                $is_paid_post = 0;
                if($tag=="arm_purchased_paid_post_list"){
                    $is_paid_post = 1;
                    $arm_current_membership_container_class = 'arm_paid_post_current_membership_container';  
                    $user_plans = !empty($user_posts) ? $user_posts : array();
                    $updated_user_plans = array();
                    if( !empty( $user_plans ) ){
                        foreach( $user_plans as $uplan_id => $upost_id ){
                            $updated_user_plans[] = $uplan_id;
                        }
                    }
                    if( !empty( $updated_user_plans ) ){
                        $user_plans = $updated_user_plans;
                    }
                }else{
                    if(!empty($user_plans) && !empty($user_posts))
                    {
                        foreach ($user_plans as $user_plans_key => $user_plans_val) {
                            if(!empty($user_posts)){
                                foreach ($user_posts as $user_post_key => $user_post_val) {
                                    if($user_post_key==$user_plans_val){
                                        unset($user_plans[$user_plans_key]);
                                    }
                                }
                            }
                        }
                    }

                    if( !empty( $user_future_plans ) && !empty($user_posts) ){
                        foreach( $user_future_plans as $f_plan_key => $f_plan_id ){
                            $paid_post_id = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $f_plan_id );
                            if( !empty( $paid_post_id[0]['arm_subscription_plan_id'] && !empty( $paid_post_id[0]['arm_subscription_plan_post_id'] ) ) ){
                                unset( $user_future_plans[$f_plan_key] );
                            }
                        }
                    }
                }
                
                $offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
                $membership_count = count($user_plans);
                $user_plans = array_slice($user_plans, $offset, $per_page);
                $content = apply_filters('arm_before_current_membership_shortcode_content', $content, $atts);
                $content .= "<div class='{$arm_current_membership_container_class}_loader_img'>";
                $content .= "</div>";
                $content .= "<div class='{$arm_current_membership_container_class}'>";
                $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
                $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
                $content .= '<style type="text/css">';
                $currentMembershipWrapperClass = ".{$arm_current_membership_container_class}";
                if (empty($renew_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button{ text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button{" . $this->arm_br2nl($renew_css) . "}";
                }

                if (empty($renew_hover_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button:hover{" . $this->arm_br2nl($renew_hover_css) . "}";
                }
                if (empty($cancel_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button{text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button{" . $this->arm_br2nl($cancel_css) . "}";
                }
                if (empty($cancel_hover_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button:hover{" . $this->arm_br2nl($cancel_hover_css) . "}";
                }

                if (empty($update_card_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style{text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style{" . $this->arm_br2nl($update_card_css) . "}";
                }

                if (empty($update_card_hover_css)) {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . "}";
                } else {
                    $content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style:hover{" . $this->arm_br2nl($update_card_hover_css) . "}";
                }

                $content .= "
                    $currentMembershipWrapperClass .arm_current_membership_heading_main{
                            {$frontfontstyle['frontOptions']['level_1_font']['font']}
                    }
                    $currentMembershipWrapperClass .arm_current_membership_list_header th{
                            {$frontfontstyle['frontOptions']['level_2_font']['font']}
                    }
                    $currentMembershipWrapperClass .arm_current_membership_list_item td{
                            {$frontfontstyle['frontOptions']['level_3_font']['font']}
                    }

                    {$currentMembershipWrapperClass} .arm_paging_wrapper .arm_paging_info,
                    {$currentMembershipWrapperClass} .arm_paging_wrapper .arm_paging_links a{
                        {$frontfontstyle['frontOptions']['level_4_font']['font']}
                    }";
                $content .= '</style>';
                if (!empty($title)) {
                    $content .= '<div class="arm_current_membership_heading_main">' . $title . '</div>';
                    $content .= '<div class="armclear"></div>';
                }
                $content .= '<form method="POST" class="arm_current_membership_form_container">';
                $content .= '<input type="hidden" name="is_paid_post" value="'.$is_paid_post.'">';
                $content .= '<div class="arm_setup_messages arm_form_message_container"></div>';
                $content .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';
                $content .= "<div class='arm_current_membership_wrapper' id='arm_tm_wrapper'>";
                $total_columns = 0;
                    $has_no = true;
                    $has_plan = true;
                    $has_start_date = true;
                    $has_end_date = true;
                    $has_trial_period = true;

                    $has_renew_date = true;
                    $has_remaining_occurence = true;
                    $has_recurring_profile = true;
                    $has_action_btn = true;

                    if (in_array('current_membership_no', $labels)) {
                        $label_key = array_search('current_membership_no', $labels);
                        $l_has_no = !empty($values[$label_key]) ? $values[$label_key] : __('No.', 'ARMember');
                    } else {
                        $has_no = false;
                    }

                    if (in_array('current_membership_is', $labels)) {
                        $label_key = array_search('current_membership_is', $labels);
                        $l_has_plan = !empty($values[$label_key]) ? $values[$label_key] : __('Membership Plan', 'ARMember');
                    } else {
                        $has_plan = false;
                    }
                    

                    if (in_array('current_membership_started_on', $labels)) {
                        $label_key = array_search('current_membership_started_on', $labels);
                        $l_start_date = !empty($values[$label_key]) ? $values[$label_key] : __('Start Date', 'ARMember');
                    } else {
                        $has_start_date = false;
                    }

                    if (in_array('current_membership_expired_on', $labels)) {
                        $label_key = array_search('current_membership_expired_on', $labels);
                        $l_end_date = !empty($values[$label_key]) ? $values[$label_key] : __('End Date', 'ARMember');
                    } else {
                        $has_end_date = false;
                    }

                    if (in_array('current_membership_recurring_profile', $labels)) {
                        $label_key = array_search('current_membership_recurring_profile', $labels);
                        $l_recurring_profile = !empty($values[$label_key]) ? $values[$label_key] : __('Recurring Profile', 'ARMember');
                    } else {
                        $has_recurring_profile = false;
                    }

                    if (in_array('current_membership_remaining_occurence', $labels)) {
                        $label_key = array_search('current_membership_remaining_occurence', $labels);
                        $l_remaining_occurence = !empty($values[$label_key]) ? $values[$label_key] : __('Remaining Occurence', 'ARMember');
                    } else {
                        $has_remaining_occurence = false;
                    }

                    if (in_array('current_membership_next_billing_date', $labels)) {
                        $label_key = array_search('current_membership_next_billing_date', $labels);
                        $l_renew_date = !empty($values[$label_key]) ? $values[$label_key] : __('Renewal On', 'ARMember');
                    } else {
                        $has_renew_date = false;
                    }

                    if (in_array('trial_period', $labels)) {
                        $label_key = array_search('trial_period', $labels);
                        $l_trial_period = !empty($values[$label_key]) ? $values[$label_key] : __('Trial Period', 'ARMember');
                    } else {
                        $has_trial_period = false;
                    }

                    if (in_array('action_button', $labels)) {
                        $label_key = array_search('action_button', $labels);
                        $l_action_btn = !empty($values[$label_key]) ? $values[$label_key] : __('Action', 'ARMember');
                    } else {
                        $has_action_btn = false;
                    }
                    if (is_rtl()) {
                        $is_current_membership_class_rtl = 'is_current_membership_class_rtl';
                    } else {
                        $is_current_membership_class_rtl = '';
                    }
                    $content .= "<div class='arm_current_membership_content " . $is_current_membership_class_rtl . "'>";
                    $content .= "<table class='arm_user_current_membership_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0'>";
                    $content .= "<thead>";
                    $content .= "<tr class='arm_current_membership_list_header' id='arm_current_membership_list_header'>";
                    
                    if ($has_no) :
                        $content .= "<th class='arm_cm_sr_no' id='arm_cm_sr_no'>{$l_has_no}</th>";
                        $total_columns++;
                    endif;
                    if ($has_plan) :
                        $content .= "<th class='arm_cm_plan_name' id='arm_cm_plan_name'>{$l_has_plan}</th>";
                        $total_columns++;
                    endif;
                    if ($has_recurring_profile):
                        $content .= "<th class='arm_cm_plan_profile' id='arm_cm_plan_profile'>{$l_recurring_profile}</th>";
                        $total_columns++;
                    endif;
                    if ($has_start_date):
                        $content .= "<th class='arm_cm_plan_start_date' id='arm_cm_plan_start_date'>{$l_start_date}</th>";
                        $total_columns++;
                    endif;
                    if ($has_end_date):
                        $content .= "<th class='arm_cm_plan_end_date' id='arm_cm_plan_end_date'>{$l_end_date}</th>";
                        $total_columns++;
                    endif;
                    if ($has_trial_period):
                        $content .= "<th class='arm_cm_plan_trial_period' id='arm_cm_plan_trial_period'>{$l_trial_period}</th>";
                        $total_columns++;
                    endif;

                    if ($has_remaining_occurence):
                        $content .= "<th class='arm_cm_plan_remaining_occurence' id='arm_cm_plan_remaining_occurence'>{$l_remaining_occurence}</th>";
                        $total_columns++;
                    endif;
                    if ($has_renew_date):
                        $content .= "<th class='arm_cm_plan_renew_date' id='arm_cm_plan_renew_date'>{$l_renew_date}</th>";
                        $total_columns++;
                    endif;

                    if ($has_action_btn):

                        if ($display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true') {


                            $content .= "<th class='arm_cm_plan_action_btn' id='arm_cm_plan_action_btn'>{$l_action_btn}</th>";
                            $total_columns++;
                        }
                    endif;

                    $content .= "</tr>";
                    $content .= "</thead>";

                    $user_all_plans = array();
                    if(!empty($user_future_plans))
                    {
                        if($is_paid_post)
                        {
                            if( !empty( $user_future_plans ) ){
                                foreach( $user_future_plans as $fPlanKey => $fPlanId ){
                                    $fPlanData = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $fPlanId );

                                    if( !empty( $fPlanData[0]['arm_subscription_plan_id'] ) && !empty( $fPlanData[0]['arm_subscription_plan_post_id'] ) ){
                                        array_push($user_all_plans, $fPlanData[0]['arm_subscription_plan_id']);
                                    }
                                }
                            }

                            $user_all_plans = array_merge($user_plans, $user_all_plans);
                        }
                        else
                        {
                            $user_all_plans = array_merge($user_plans, $user_future_plans);
                        }
                    } 
                    else 
                    {
                       $user_all_plans = $user_plans;
                    }

                    
                if (!empty($user_all_plans)) {
                    
                    $sr_no = 0;
                    $change_plan_to_array = array();
                    foreach ($user_all_plans as $user_plan) {
                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $user_plan, true);
                        $curPlanDetail = !empty($planData['arm_current_plan_detail']) ? $planData['arm_current_plan_detail'] : '';
                        $start_plan = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                        if(!empty($planData['arm_started_plan_date']) && $planData['arm_started_plan_date']<=$start_plan)
                        {
                            $start_plan = $planData['arm_started_plan_date'];
                        }
                        $expire_plan = !empty($planData['arm_expire_plan']) ? $planData['arm_expire_plan'] : '';
                        $change_plan = !empty($planData['arm_change_plan_to']) ? $planData['arm_change_plan_to'] : '';
                        $effective_from  = !empty($planData['arm_subscr_effective']) ? $planData['arm_subscr_effective'] : '';

                        if($change_plan != '' && $effective_from != '' && !empty($effective_from) && !empty($change_plan)){
                            $change_plan_to_array[$change_plan] = $effective_from;

                        }

                        $payment_mode = '';
                        $payment_cycle = '';
                        $is_plan_cancelled = '';
                        $completed = '';
                        $recurring_time = '';
                        $recurring_profile = '';
                        $next_due_date = '-';
                        $user_payment_mode = '';
                        if (!empty($curPlanDetail)) {
                            $plan_info = new ARM_Plan(0);
                            $plan_info->init((object) $curPlanDetail);
                        } else {
                            $plan_info = new ARM_Plan($user_plan);
                        }

                        $arm_plan_is_suspended = '';
                        $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                        $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                        if (!empty($suspended_plan_ids)) {
                            if (in_array($user_plan, $suspended_plan_ids)) {
                                $arm_plan_is_suspended = '<br/><span style="color: red;">(' . __('Suspended', 'ARMember') . ')</span>';
                            }
                        }

                        if ($plan_info->exists()) {
                            $sr_no++;
                            $plan_options = $plan_info->options;

                            if ($plan_info->is_recurring()) {
                                $completed = $planData['arm_completed_recurring'];
                                $is_plan_cancelled = $planData['arm_cencelled_plan'];
                                $payment_mode = $planData['arm_payment_mode'];
                                $payment_cycle = $planData['arm_payment_cycle'];
                                $recurring_plan_options = $plan_info->prepare_recurring_data($payment_cycle);
                                $recurring_time = $recurring_plan_options['rec_time'];
                                $next_due_date = $planData['arm_next_due_payment'];


                                if ($payment_mode == 'auto_debit_subscription') {
                                    $user_payment_mode= '<br/>( ' . __('Auto Debit', 'ARMember') . ' )';
                                } else {
                                    $user_payment_mode= '';
                                }
                                $arm_trial_start_date = $planData['arm_trial_start'];
                                $arm_is_user_in_trial = $planData['arm_is_trial_plan'];

                                if ($recurring_time == 'infinite' || empty($expire_plan)) {
                                    $remaining_occurence = __('Never Expires', 'ARMember');
                                } else {
                                    $remaining_occurence = $recurring_time - $completed;
                                }

                                if ($remaining_occurence > 0 || $recurring_time == 'infinite') {
                                    if (!empty($next_due_date)) {
                                        $next_due_date = date_i18n($date_format, $next_due_date);
                                    }
                                } else {
                                    $next_due_date = '';
                                }

                                $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                $arm_grace_period_end = $planData['arm_grace_period_end'];
                            } else {
                                $recurring_profile = '-';
                                $arm_trial_start_date = '';
                                $remaining_occurence = '-';
                                $arm_is_user_in_grace = 0;
                                $arm_grace_period_end = '';
                                $arm_is_user_in_trial = 0;

                            }

                            $recurring_profile = $plan_info->new_user_plan_text(false, $payment_cycle);


                            $content .="<tr class='arm_current_membership_list_item' id='arm_current_membership_tr_" . $user_plan . "'>";
                            

                            if ($has_no) :
                                $content .= "<td data-label='{$l_has_no}' class='arm_current_membership_list_item_plan_sr' id='arm_current_membership_list_item_plan_sr_" . $user_plan . "'>" . $sr_no . "</td>";
                            endif;

                            if ($has_plan) :
                                
                                $content .= "<td data-label='{$l_has_plan}' class='arm_current_membership_list_item_plan_name' id='arm_current_membership_list_item_plan_name_" . $user_plan . "'>";
                                if($is_paid_post==1){
                                    $arm_paid_post_id = !empty($plan_info->isPaidPost) ? $plan_info->isPaidPost : 0;
                                    $content .= "<a href=".get_permalink($arm_paid_post_id)." target='_blank'>" . stripslashes($plan_info->name) . "</a>";  
                                }else{

                                    $content .= stripslashes($plan_info->name);
                                }
                                $content .=  " " . $arm_plan_is_suspended . "</td>";

                            endif;
                            if ($has_recurring_profile):
                                $content .= "<td data-label='{$l_recurring_profile}' class='arm_current_membership_list_item_plan_profile' id='arm_current_membership_list_item_plan_profile_" . $user_plan . "'>";
                                /* if ($plan_info->is_recurring()) {
                                  $content .= $plan_info->user_plan_text(false, $payment_cycle);
                                  } else {
                                  $content .="--";
                                  }
                                  if ($plan_info->is_recurring()) {
                                  if ($payment_mode == 'auto_debit_subscription') {
                                  $content .= ' ( ' . __('Auto Debit Subscription', 'ARMember') . ' )';
                                  } else {
                                  $content .= ' ( ' . __('Manual Subscription', 'ARMember') . ' )';
                                  }
                                  } */

                                $content .=$recurring_profile;

                                $content .="</td>";
                            endif;
                            if ($has_start_date):
                               
                                $content .= "<td data-label='{$l_start_date}' class='arm_current_membership_list_item_plan_start' id='arm_current_membership_list_item_plan_start_" . $user_plan . "'>";
                                if(!empty($start_plan)){
                                   $content .=  date_i18n($date_format, $start_plan);
                                }
                                
                                 if (!empty($arm_trial_start_date)) {
                                    if($arm_is_user_in_trial == 1 || $arm_is_user_in_trial == '1'){

                                    if($arm_trial_start_date <  $start_plan){
                                        $content.="<br/><span class='arm_current_membership_trial_active'>(".$trial_active.")</span>";
                                    }
                                }
                                 }
                                 $content .= "</td>";   


                            endif;
                            if ($has_end_date):
                                $content .= "<td data-label='{$l_end_date}' class='arm_current_membership_list_item_plan_end' id='arm_current_membership_list_item_plan_end_" . $user_plan . "'>";

                                if ($plan_info->is_free() || $plan_info->is_lifetime() || ($plan_info->is_recurring() && $recurring_time == 'infinite')) {
                                    //$content .= __('Never Expires', 'ARMember');
                                    if($plan_info->is_recurring() && $recurring_time == 'infinite' && (isset($planData['arm_cencelled_plan']) && $planData['arm_cencelled_plan']=='yes')) 
                                    {
                                        $content .= date_i18n($date_format, $planData['arm_next_due_payment']);
                                    } else {
                                        $content .= __('Never Expires', 'ARMember');    
                                    }
                                } else {

                                    if (isset($plan_options['access_type']) && !in_array($plan_options['access_type'], array('infinite', 'lifetime'))) {
                                      
                                        if (!empty($expire_plan)) {

                                            $membership_expire_content = date_i18n($date_format, $expire_plan);
                                           
                                            $content .= $membership_expire_content;
                                        } else {
                                            $content.= '-';
                                        }
                                    } else {

                                        $content .= "-";
                                    }
                                }
                                $content.= "</td>";
                            endif;
                            if ($has_trial_period):
                                $content .= "<td data-label='{$l_trial_period}' class='arm_current_membership_list_item_plan_trial_period' id='arm_current_membership_list_item_plan_trial_period_" . $user_plan . "'>";
                                if (!empty($arm_trial_start_date)) {
                                    $content .=date_i18n($date_format, $arm_trial_start_date);
                                    $content .= " " . __('To', 'ARMember');
                                    $content .=" " . date_i18n($date_format, strtotime('-1 day', $start_plan));
                                } else {
                                    $content .= '-';
                                }

                                $content .="</td>";
                            endif;

                            if ($has_remaining_occurence):
                                $content .= "<td data-label='{$l_renew_date}' class='arm_current_membership_list_item_remaining_occurence' id='arm_current_membership_list_item_remaining_occurence_" . $user_plan . "'>";

                                /* if ($plan_info->is_recurring()) {

                                  if ($recurring_time == 'infinite') {
                                  $content .= '--';
                                  } else {
                                  if (!empty($expire_plan)) {
                                  if ($recurring_time == 'infinite') {
                                  $content .= '--';
                                  } else {

                                  if ($plan_info->has_trial_period() && $completed == 0) {
                                  $remaining = $recurring_time;
                                  $content .= $recurring_time;
                                  } else {
                                  $total_rec = $recurring_time;
                                  $remaining = $total_rec - $completed;
                                  $content .= $remaining;
                                  }
                                  }
                                  } else {
                                  $content .= '--';
                                  }
                                  }
                                  } else {
                                  $content .="--";
                                  } */

                                $content .= $remaining_occurence;
                                $content .="</td>";
                            endif;
                            if ($has_renew_date):
                                $content .= "<td data-label='{$l_renew_date}' class='arm_current_membership_list_item_renew_date' id='arm_current_membership_list_item_renew_date_" . $user_plan . "'>";

                                $content.= $next_due_date;
                                $grace_message = '';

                                $next_cycle_due = '';
                                if($plan_info->is_recurring()){

                                    if(!empty($expire_plan)){
                                        if($remaining_occurence == 0){
                                        $next_cycle_due = __('No cycles due', 'ARMember');
                                        }
                                        else{
                                            $next_cycle_due = "<br/>(". $remaining_occurence." ".__('cycles due', 'ARMember').")";
                                        }
                                    }

                                    if($arm_is_user_in_grace == "1" || $arm_is_user_in_grace == 1){
                                        $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end );
                                        $grace_message .= "<br/>( ".__('grace period expires on', 'ARMember')." ".$arm_grace_period_end." )";

                                    }
                                }
                                
                                $content .=$next_cycle_due.$grace_message.$user_payment_mode."</td>";
                            endif;
                            if ($has_action_btn):
                                $arm_disable_button = '';
                                if ($setup_id == '' || $setup_id == '0') {
                                    $arm_disable_button = 'disabled';
                                }
                                else{
                                    $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
                                    if(empty($setup_data)){
                                        $arm_disable_button = 'disabled';
                                    }
                                }

                                if ($display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true'){
                                        $content .= "<td id='arm_cm_plan_action_btn' data-label='{$l_action_btn}' class='arm_current_membership_list_item_action_btn_" . $user_plan . "'><div class='arm_current_membership_action_div'>";

                                        if(!in_array($user_plan, $user_future_plans)){
                                            if ($display_renew_button == 'true' && !$plan_info->is_lifetime() && !$plan_info->is_free() && $is_plan_cancelled != 'yes') {
                                                        $make_payment_content = '<div class="arm_cm_renew_btn_div"><button type="button" class= "arm_renew_subscription_button" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .' data-is_paid_post="'.$is_paid_post.'">' . $make_payment_text . '</button></div>';
                                                    if($change_plan == '' || $effective_from == '' || empty($effective_from) || empty($change_plan)){
                                                        $renew_content = '<div class="arm_cm_renew_btn_div"><button type="button" class= "arm_renew_subscription_button" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .  ' data-is_paid_post="'.$is_paid_post.'">' . $renew_text . '</button></div>';
                                                    }
                                                    else{
                                                        $renew_content = '';
                                                    }
                                                        if ($is_plan_cancelled == 'yes') {
                                                            $renew_content = '';
                                                        }
                                                        if ($plan_info->is_recurring()) {

                                                            if ($payment_mode == 'manual_subscription') {
                                                                if ($recurring_time == 'infinite') {
                                                                    $content .= $make_payment_content;
                                                                } else {
                                                                    if ($remaining_occurence > 0) {
                                                                        $content .= $make_payment_content;
                                                                    } else {

                                                                        $now = current_time('mysql');

                                                                      $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $user_plan, $now));


                                                                        if($arm_last_payment_status == 'failed'){

                                                                                if(!empty($expire_plan)){

                                                                                if(strtotime($now) < $expire_plan){
                                                                                        $content .= $make_payment_content;
                                                                                    }
                                                                                    else{
                                                                                        $content .= $renew_content;
                                                                                    }

                                                                            }
                                                                            else{
                                                                                    $content .= $make_payment_content; 
                                                                            }
                                                                        }
                                                                        else{
                                                                             $content .= $renew_content;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                if ($recurring_time != 'infinite') {
                                                                    if ($remaining_occurence == 0) {
                                                                        
                                                                        $now = current_time('mysql');

                                                                      $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $user_plan, $now));


                                                                      if($arm_last_payment_status == 'failed'){
                                                                        if(!empty($expire_plan)){
                                                                                if(strtotime($now) < $expire_plan){

                                                                                    $content .= $make_payment_content;
                                                                                }
                                                                                else{
                                                                                    $content .= $renew_content;
                                                                                }
                                                                            }
                                                                            else{
                                                                                $content .= $make_payment_content; 
                                                                            }
                                                                      }
                                                                      else{
                                                                        $content .= $renew_content;
                                                                      }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $content .= $renew_content;
                                                        }
                                                if((isset($display_cancel_button) && $display_cancel_button == 'true') && (isset($is_plan_cancelled) && $is_plan_cancelled != 'yes') && !$plan_info->is_recurring()) {
                                                        $content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . $user_plan . '"><button type="button" id="arm_cancel_subscription_link_' . $user_plan . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . $user_plan . '">'.$cancel_text.'</button><img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" id="arm_field_loader_img_' . $user_plan . '" style="display: none;"/></div>';
                                                    }
                                            }

                                            if($plan_info->is_lifetime() || $plan_info->is_free()) {
                                                if((isset($display_cancel_button) && $display_cancel_button == 'true') && (isset($is_plan_cancelled) && $is_plan_cancelled != 'yes')) {
                                                        $content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . $user_plan . '"><button type="button" id="arm_cancel_subscription_link_' . $user_plan . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . $user_plan . '">'.$cancel_text.'</button><img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" id="arm_field_loader_img_' . $user_plan . '" style="display: none;"/></div>';
                                                    }  
                                            }

                                            if ($plan_info->is_recurring()) 
                                            {
                                                if($display_update_card_button == 'true' && $payment_mode == 'auto_debit_subscription' && $is_plan_cancelled != 'yes')
                                                {
                                                    if($planData['arm_user_gateway']=='paypal')
                                                    {
                                                        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                                                        $pg_options = $active_gateways[$planData['arm_user_gateway']];
                                                        $sandbox = (isset($pg_options['paypal_payment_mode']) && $pg_options['paypal_payment_mode'] == 'sandbox') ? TRUE : FALSE;
                                                        if($sandbox) {
                                                            $paypal_url = 'https://www.sandbox.paypal.com/myaccount/wallet';
                                                        } else {
                                                            $paypal_url = 'https://www.paypal.com/myaccount/wallet';
                                                        }
                                                        $content .= '<div class="arm_cm_update_btn_div"><a href="'.$paypal_url.'" target="_blank"><button type="button" class= "arm_update_card_button_style">' . $update_card_text . '</button></a></div>';
                                                    }
                                                    else if($planData['arm_user_gateway']=='2checkout')
                                                    {
                                                        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

                                                        $pg_options = $active_gateways[$planData['arm_user_gateway']];
                                                        $sandbox = (isset($pg_options['payment_mode']) && $pg_options['payment_mode'] == 'sandbox') ? TRUE : FALSE;
                                                        if($sandbox) {
                                                            $two_checkout_url = 'https://sandbox.2checkout.com/sandbox/sales/customer/change_billing_method';
                                                        } else {
                                                            $two_checkout_url = 'https://www.2checkout.com/va/sales/customer/change_billing_method';
                                                        }
                                                        $content .= '<div class="arm_cm_update_btn_div"><a href="'.$two_checkout_url.'" target="_blank"><button type="button" class= "arm_update_card_button_style">' . $update_card_text . '</button></a></div>';
                                                    }
                                                    //else if($planData['arm_user_gateway']=='stripe' || $planData['arm_user_gateway']=='authorize_net' || $planData['arm_user_gateway']=='paypal_pro')
                                                    else if( $planData['arm_user_gateway']=='authorize_net' )
                                                    {
                                                        $content .= '<div class="arm_cm_update_btn_div"><button type="button" class= "arm_update_card_button arm_update_card_button_style" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .'>' . $update_card_text . '</button></div>';


                                                    } else {
                                                        if( apply_filters('arm_display_update_card_button_from_outside', false, $planData['arm_user_gateway'], $planData ) ){

                                                            $updated_card_button_outside = '';

                                                            $updated_card_button_outside = apply_filters( 'arm_render_update_card_button_from_outside', $updated_card_button_outside,  $planData['arm_user_gateway'], $planData, $user_plan, $arm_disable_button, $update_card_text  );
                                                            
                                                            $content .= $updated_card_button_outside;
                                                        } else {
                                                            $content .= '<div class="arm_cm_update_btn_div"><button type="button" class= "arm_update_card_button arm_update_card_button_style" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .'>' . $update_card_text . '</button></div>';
                                                        }
                                                    } /*else if( $planData['arm_user_gateway'] == 'stripe' ){
                                                        $stripeOpt = $arm_payment_gateways->arm_get_active_payment_gateways();
                                                        $stripe_opt = $stripeOpt['stripe'];

                                                        if( 'test' == $stripe_opt['stripe_payment_mode'] ){
                                                            $secret_key = $stripe_opt['stripe_test_secret_key'];
                                                            $stripe_publishable_key = $stripe_opt['stripe_test_pub_key'];
                                                        } else {
                                                            $secret_key = $stripe_opt['stripe_secret_key'];
                                                            $stripe_publishable_key = $stripe_opt['stripe_pub_key'];
                                                        }

                                                        $content .= '<div class="arm_cm_update_btn_div"><button type="button" class="arm_update_card_button_style arm_update_stripe_card" data-secret-key="' . base64_encode( strrev( $secret_key ) ) . '" data-plan_id="' . $user_plan . '" ' . $arm_disable_button .'>' . $update_card_text . '</button></div>';
                                                        
                                                    }*/

                                                    $arm_card_btn_default = '';

                                                    $content .= apply_filters("arm_get_gateways_update_card_detail_btn", $arm_card_btn_default, $planData, $user_plan, $update_card_text);
                                                }

                                                if ($display_cancel_button == 'true') {
                                                    if($change_plan == '' || $effective_from == '' || empty($effective_from) || empty($change_plan)){
                                                        if (isset($is_plan_cancelled) && $is_plan_cancelled == 'yes') { 

                                                            $content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . $user_plan . '"><button type="button" id="arm_cancel_subscription_link_' . $user_plan . '" class= "arm_cancel_subscription_button" data-plan_id = "' . $user_plan . '" style="cursor: default;" disabled="disabled">' . __('Cancelled', 'ARMember') .'</button></div>';
                                                        } else {
                                                            $content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . $user_plan . '"><button type="button" id="arm_cancel_subscription_link_' . $user_plan . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . $user_plan . '">'.$cancel_text.'</button><img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" id="arm_field_loader_img_' . $user_plan . '" style="display: none;"/></div>';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $content .= '</div></td>';
                            }
                            endif;
                            $content .="</tr>";
                        }
                    }



                    if(!empty($change_plan_to_array)){
                        foreach ($change_plan_to_array as $change_user_plan => $effective_from_date) {

                            if(!empty($change_user_plan) && !empty($effective_from_date)){

                                $change_plan_info = new ARM_Plan($change_user_plan);

                                if ($change_plan_info->exists()) {
                                    $sr_no++;

                                    $content .="<tr class='arm_current_membership_list_item' id='arm_current_membership_tr_" . $change_user_plan . "'>";


                                    if ($has_no) :
                                        $content .= "<td data-label='{$l_has_no}' class='arm_current_membership_list_item_plan_sr' id='arm_current_membership_list_item_plan_sr_" . $change_user_plan . "'>" . $sr_no . "</td>";
                                    endif;

                                    if ($has_plan) :
                                        $content .= "<td data-label='{$l_has_plan}' class='arm_current_membership_list_item_plan_name' id='arm_current_membership_list_item_plan_name_" . $change_user_plan . "'>" . stripslashes($change_plan_info->name) . "</td>";
                                    endif;
                                    if ($has_recurring_profile):
                                        $content .= "<td data-label='{$l_recurring_profile}' class='arm_current_membership_list_item_plan_profile' id='arm_current_membership_list_item_plan_profile_" . $change_user_plan . "'>";
                                        $recurring_profile = $change_plan_info->new_user_plan_text(false, '');

                                        $content .=$recurring_profile."</td>";
                                    endif;
                                    if ($has_start_date):

                                        $content .= "<td data-label='{$l_start_date}' class='arm_current_membership_list_item_plan_start' id='arm_current_membership_list_item_plan_start_" . $change_user_plan . "'>";
                                        if(!empty($effective_from_date)){
                                            $content .=  date_i18n($date_format, $effective_from_date);
                                        }


                                        $content .= "</td>";   


                                    endif;
                                    if ($has_end_date):
                                        $content .= "<td data-label='{$l_end_date}' class='arm_current_membership_list_item_plan_end' id='arm_current_membership_list_item_plan_end_" . $change_user_plan . "'>";


                                        $content.= "</td>";
                                    endif;
                                    if ($has_trial_period):
                                        $content .= "<td data-label='{$l_trial_period}' class='arm_current_membership_list_item_plan_trial_period' id='arm_current_membership_list_item_plan_trial_period_" . $change_user_plan . "'>";

                                        $content .="</td>";
                                    endif;

                                    if ($has_remaining_occurence):
                                        $content .= "<td data-label='{$l_renew_date}' class='arm_current_membership_list_item_remaining_occurence' id='arm_current_membership_list_item_remaining_occurence_" . $change_user_plan . "'>";


                                        $content .="</td>";
                                    endif;
                                    if ($has_renew_date):
                                        $content .= "<td data-label='{$l_renew_date}' class='arm_current_membership_list_item_renew_date' id='arm_current_membership_list_item_renew_date_" . $change_user_plan . "'>";



                                        $content .="</td>";
                                    endif;
                                    if ($has_action_btn):


                                        if ($display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true'){
                                            $content .= "<td id='arm_cm_plan_action_btn' data-label='{$l_action_btn}' class='arm_current_membership_list_item_action_btn_" . $change_user_plan . "'>";
                                            $content .= '</td>';
                                        }

                                    endif;
                                    $content .="</tr>";
                                }

                            }

                        }
                    }
                  
                }
                else{
                     $content .="<tr class='arm_current_membership_list_item' id='arm_current_membership_list_item_no_plan'>";
                    $content .="<td colspan='" . ($total_columns + 1) . "' class='arm_no_plan'>" . $message_no_record . "</td>";
                    $content .="</tr>";
                }

                $content .= "</table>";
                
                $content .= "</div>";
                $membershipPaging = $arm_global_settings->arm_get_paging_links($current_page, $membership_count, $per_page, 'current_membership');
                $content .= "<div class='arm_current_membership_paging_container'>$membershipPaging</div>";
                
                $content .= "</div>";
                $content .= "<input type='hidden' id='setup_id' name='setup_id' value='" . $setup_id . "'/>";
                $content .= "<input type='hidden' id='loader_img' name='loader_img' value='" . MEMBERSHIP_IMAGES_URL . "/arm_loader.gif'/>";
                $content .= "<input type='hidden' id='arm_form_style_css' name='arm_form_style_css' value='" . MEMBERSHIP_URL . "/css/arm_form_style.css'/>";
                $content .= "<input type='hidden' id='angular_js' name='angular_js' value='" . MEMBERSHIP_URL . "/js/angular/arm_angular_with_material.js'/>";
                $content .= "<input type='hidden' id='arm_font_awsome' name='arm_font_awsome' value='" . MEMBERSHIP_URL . "/css/arm-font-awesome.css'/>";
                $content .= "<input type='hidden' id='arm_stripe_js' name='arm_stripe_js' value='https://js.stripe.com/v2/'/>";
                $content .= "<input type='hidden' id='arm_total_current_membership_columns' name='arm_total_current_membership_columns' value='" . ($total_columns + 1) . "'/>";
                $content .= "<input type='hidden' id='arm_cancel_subscription_message' name='arm_cancel_subscription_message' value='" . $cancel_message . "'/>";
                $arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
                $content .= '<input type="hidden" name="_wpnonce" value="' . $arm_wp_nonce . '"/>';
                foreach (array('title', 'membership_label', 'membership_value', 'display_renew_button', 'renew_css', 'renew_hover_css','renew_text','make_payment_text','display_cancel_button','cancel_css','cancel_hover_css','cancel_text','display_update_card_button','update_card_css','update_card_hover_css','update_card_text','setup_id','trial_active','cancel_message','message_no_record','per_page') as $k) {
                    $content .= '<input type="hidden" class="arm_membership_field_' . $k . '" name="' . $k . '" value="' . $atts[$k] . '">';
                }
                $content .= "</form></div>";
                $content .= "<div class='armclear'></div>";
                $content = apply_filters('arm_after_current_membership_shortcode_content', $content, $atts);
            }
            else {
                $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');
                return do_shortcode("[arm_form id='$default_login_form_id' is_referer='1']");
            }
            $ARMember->enqueue_angular_script(true);
            wp_enqueue_style('arm_form_style_css');

            return do_shortcode($content);
        }

        function arm_close_account_form_style($set_id = '', $formRandomID = 0) {
            global $wp, $wpdb, $wp_roles, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;

            $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
            $labelFontFamily = isset($frontfontstyle['frontOptions']['level_3_font']['font_family']) ? $frontfontstyle['frontOptions']['level_3_font']['font_family'] : 'Helvetica';
            $labelFontSize = isset($frontfontstyle['frontOptions']['level_3_font']['font_size']) ? $frontfontstyle['frontOptions']['level_3_font']['font_size'] : '14';
            $labelFontColor = (isset($frontfontstyle['frontOptions']['level_3_font']['font_color'])) ? $frontfontstyle['frontOptions']['level_3_font']['font_color'] : "";
            $labelFontBold = (isset($frontfontstyle['frontOptions']['level_3_font']['font_bold']) && $frontfontstyle['frontOptions']['level_3_font']['font_bold'] == '1') ? 1 : 0;
            $labelFontItalic = (isset($frontfontstyle['frontOptions']['level_3_font']['font_italic']) && $frontfontstyle['frontOptions']['level_3_font']['font_italic'] == '1') ? 1 : 0;
            $labelFontDecoration = (!empty($frontfontstyle['frontOptions']['level_3_font']['font_decoration'])) ? $frontfontstyle['frontOptions']['level_3_font']['font_decoration'] : '';

            $buttonFontFamily = isset($frontfontstyle['frontOptions']['button_font']['font_family']) ? $frontfontstyle['frontOptions']['button_font']['font_family'] : 'Helvetica';
            $buttonFontSize = isset($frontfontstyle['frontOptions']['button_font']['font_size']) ? $frontfontstyle['frontOptions']['button_font']['font_size'] : '14';
            $buttonFontColor = (isset($frontfontstyle['frontOptions']['button_font']['font_color'])) ? $frontfontstyle['frontOptions']['button_font']['font_color'] : "";
            $buttonFontBold = (isset($frontfontstyle['frontOptions']['button_font']['font_bold']) && $frontfontstyle['frontOptions']['button_font']['font_bold'] == '1') ? 1 : 0;
            $buttonFontItalic = (isset($frontfontstyle['frontOptions']['button_font']['font_italic']) && $frontfontstyle['frontOptions']['button_font']['font_italic'] == '1') ? 1 : 0;
            $buttonFontDecoration = (!empty($frontfontstyle['frontOptions']['button_font']['font_decoration'])) ? $frontfontstyle['frontOptions']['button_font']['font_decoration'] : '';

            $form_settings = array();
            if (isset($set_id) && $set_id != '') {
                $setform_settings = $wpdb->get_row("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id` = '" . $set_id . "' AND `arm_form_type`='login' ORDER BY arm_form_id DESC LIMIT 1");
                $set_style_option = maybe_unserialize($setform_settings->arm_form_settings);
                if (isset($set_style_option['style'])) {
                    $form_settings['style'] = $set_style_option['style'];
                }
                if (isset($set_style_option['custom_css'])) {
                    $form_settings['custom_css'] = $set_style_option['custom_css'];
                }
                $form_css = $arm_member_forms->arm_ajax_generate_form_styles('close_account', $form_settings);
            } else {
                // Get Default style 
                $form_settings['style'] = $arm_member_forms->arm_default_form_style_login();
                $form_css = $arm_member_forms->arm_ajax_generate_form_styles('close_account', $form_settings);
            }
            $caFormStyle = '';
            if (!empty($frontfontstyle['google_font_url'])) {
                $caFormStyle .= '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />';
            }
            $closeAccountcontainer = ".arm_form_close_account";
            $caFormStyle .= "<style type='text/css'>
				/*$closeAccountcontainer .arm_close_account_message,
				$closeAccountcontainer .arm_form_input_box,
				$closeAccountcontainer .arm_form_input_container,
				$closeAccountcontainer .arm_form_input_container input{
					{$frontfontstyle['frontOptions']['level_3_font']['font']}
				}
				$closeAccountcontainer .arm_close_account_btn{
					{$frontfontstyle['frontOptions']['button_font']['font']}
				}*/
                {$form_css['arm_css']}
			</style>";
            return $caFormStyle;
        }

        function arm_close_account_form_action() {
            global $wp, $wpdb, $current_user, $current_site, $arm_errors, $ARMember, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_members_activity, $arm_subscription_plans;
            $posted_data = $_POST;
            $user = wp_get_current_user();
            $arm_capabilities = '';
            $ARMember->arm_check_user_cap($arm_capabilities);
            if (isset($posted_data['arm_action'])) {
                do_action('arm_before_close_account_form_action', $posted_data, $user);
                if (isset($posted_data['pass'])) {
                    if ($user && wp_check_password($posted_data['pass'], $user->data->user_pass, $user->ID)) {
                        arm_set_member_status($user->ID, 2, 1);
                        $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                        $stop_future_plan_ids = get_user_meta($user->ID, 'arm_user_future_plan_ids', true);
                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                        
                        if(!empty($stop_future_plan_ids) && is_array($stop_future_plan_ids)){
                            foreach($stop_future_plan_ids as $stop_future_plan_id){
                                $arm_subscription_plans->arm_add_membership_history($user->ID, $stop_future_plan_id, 'cancel_subscription', array(), 'terminate');
                                delete_user_meta($user->ID, 'arm_user_plan_' . $stop_future_plan_id);
                            }
                            delete_user_meta($user->ID, 'arm_user_future_plan_ids');
                        }

                        if (!empty($plan_ids) && is_array($plan_ids)) {
                            
                            foreach ($plan_ids as $plan_id) {
                                $planData = get_user_meta($user->ID, 'arm_user_plan_' . $plan_id, true);
                                $userPlanDatameta = !empty($planData) ? $planData : array();
                                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                $plan_detail = $planData['arm_current_plan_detail'];
                                $planData['arm_cencelled_plan'] = 'yes';
                                update_user_meta($user->ID, 'arm_user_plan_' . $plan_id, $planData);
                                if (!empty($plan_detail)) {
                                    $planObj = new ARM_Plan(0);
                                    $planObj->init((object) $plan_detail);
                                } else {
                                    $planObj = new ARM_Plan($plan_id);
                                }
                                if ($planObj->exists() && $planObj->is_recurring()) {
                                    do_action('arm_cancel_subscription_gateway_action', $user->ID, $planObj->ID);
                                }
                                $arm_subscription_plans->arm_add_membership_history($user->ID, $planObj->ID, 'cancel_subscription', array(), 'close_account');
                                do_action('arm_cancel_subscription', $user->ID, $planObj->ID);
                                $arm_subscription_plans->arm_clear_user_plan_detail($user->ID, $planObj->ID);
                            }
                        }
                        do_action('arm_after_close_account', $user->ID, $user);
                        global $arm_manage_communication;
                        $args = array('plan_id' => 0, 'user_id' => $user->ID, 'action' => 'on_close_account');
                        $arm_manage_communication->arm_user_plan_status_action_mail($args);
                        wp_cache_delete($user->ID, 'users');
                        wp_cache_delete($user->user_login, 'userlogins');

                        $res_var = wp_delete_user($user->ID, 1);

                        wp_logout();
                        $home_url = ARM_HOME_URL;
                        $response = array('type' => 'success', 'msg' => __('Your account is closed successfully.', 'ARMember'), 'url' => $home_url);
                    } else {
                        $err_msg = $arm_global_settings->common_message['arm_invalid_password_close_account'];
                        $all_errors = (!empty($err_msg)) ? $err_msg : __('Your current password is invalid.', 'ARMember');
                        $response = array('type' => 'error', 'msg' => __($all_errors, 'ARMember'));
                    }
                }
                do_action('arm_after_close_account_form_action', $posted_data, $user);
            }
            echo json_encode($response);
            die();
        }

        /**
         * Add Shortcode Button in TinyMCE Editor.
         */
        function arm_insert_shortcode_button($content) {
            /* if (!in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'post-new.php', 'page-new.php'))) {
              return;
              } */

            $allowed_pages_for_media_button = apply_filters(
                'arm_allowed_pages_for_media_buttons',
                array( 'post.php', 'post-new.php' ),
                $content
            );

            if (!in_array(basename($_SERVER['PHP_SELF']), $allowed_pages_for_media_button)) {
                return;
            }

            if( !isset($post_type) ){
                $post_type = '';
            }

            if (basename($_SERVER['PHP_SELF']) == 'post.php') {
                $post_id = $_REQUEST['post'];
                $post_type = get_post_type($post_id);
            }
            if (basename($_SERVER['PHP_SELF']) == 'post-new.php') {
                if (isset($_REQUEST['post_type'])) {
                    $post_type = $_REQUEST['post_type'];
                } else {
                    $post_type = 'post';
                }
            }


            if( '' == $post_type ){
                $post_type = apply_filters( 'arm_allowed_post_type_for_external_editors', $post_type, $content );
            }
            
            $allowed_post_types = apply_filters(
                'arm_display_shortcode_buttons_on_tinymce',
                array( 'post', 'page' ),
                $content
            );

            if (!in_array($post_type, $allowed_post_types)) {
                return;
            }
            if(isset($_REQUEST["action"]) && $_REQUEST["action"]=='elementor') {
                if(!wp_script_is( "jquery", "enqueued" )) {
                    wp_enqueue_script('jquery');
                }
                if(!wp_script_is( "arm_tinymce", "enqueued" )) {
                wp_enqueue_script('arm_tinymce', MEMBERSHIP_URL . '/js/arm_tinymce_member.js', array('jquery'), MEMBERSHIP_VERSION);
                }
                if(!wp_script_is( "arm_bpopup", "enqueued" )) {
                    wp_enqueue_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);    
                }
                if(!wp_script_is("arm_t_chosen_jq_min", "enqueued")) {
                    wp_enqueue_script('arm_t_chosen_jq_min', MEMBERSHIP_URL . '/js/chosen.jquery.min.js', array('jquery'), MEMBERSHIP_VERSION);
                }
                if(!wp_script_is("arm_colpick-js", "enqueued")) {
                    wp_enqueue_script('arm_colpick-js', MEMBERSHIP_URL . '/js/colpick.min.js', array('jquery'), MEMBERSHIP_VERSION);
                }
                if(!wp_script_is("arm_icheck-js", "enqueued")) {
                    wp_enqueue_script('arm_icheck-js', MEMBERSHIP_URL . '/js/icheck.js', array('jquery'), MEMBERSHIP_VERSION);
                }
                if(!wp_style_is( "arm_tinymce", "enqueued" )) {
                    wp_enqueue_style('arm_tinymce', MEMBERSHIP_URL . '/css/arm_tinymce.css', array(), MEMBERSHIP_VERSION);    
                }
                if(!wp_style_is( "arm_chosen_selectbox", "enqueued" )) {
                    wp_enqueue_style('arm_chosen_selectbox', MEMBERSHIP_URL . '/css/chosen.css', array(), MEMBERSHIP_VERSION);
                }
                if(!wp_style_is( "arm_colpick-css", "enqueued" )) {
                    wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
                }
                if(!wp_style_is( "arm-font-awesome", "enqueued" )) {
                    wp_enqueue_style('arm-font-awesome', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
                }

                $internal_style_for_elementor = "
                    .arm_shortcode_options_popup_wrapper .arm_shortcode_options_container .arm_selectbox dt {
                        box-sizing: content-box;
                    }
                    .arm_shortcode_options_popup_wrapper.arm_normal_wrapper input:not([type='button']), .arm_shortcode_options_popup_wrapper input:not([type='button']), .arm_shortcode_options_popup_wrapper.arm_normal_wrapper select, .arm_shortcode_options_popup_wrapper select{
                        box-sizing: content-box;
                        width: 280px;
                    }
                    .arm_member_transaction_fields .arm_member_transaction_field_list input[type='text'],
                    .arm_member_current_membership_fields .arm_member_current_membership_field_list input[type='text'] {
                        box-sizing: border-box;
                    }
                    .arm_shortcode_popup_btn_wrapper {
                        margin: 0 0 5px 0;
                    }
                ";
                wp_add_inline_style( 'arm_tinymce', $internal_style_for_elementor );
                add_action('wp_footer', array(&$this, 'arm_insert_shortcode_popup'));
            }
            
            ?>
            <div class="arm_shortcode_popup_btn_wrapper">
                <span class="arm_logo_btn"></span>
                <span class="arm_spacer"></span>
                <input type="hidden" class="arm_tinymce_editor_id" id="arm_tinymce_editor_id-<?php echo $content; ?>" value="false" />
                <a class="arm_shortcode_popup_link arm_form_shortcode_popup_link" data-editor-id="<?php echo $content ?>" onclick="arm_open_form_shortcode_popup();" href="javascript:void(0)"><?php _e('MEMBERSHIP SHORTCODES', 'ARMember'); ?></a>
                <span class="arm_spacer"></span>
                <a class="arm_shortcode_popup_link arm_restriction_shortcode_popup_link" data-editor-id="<?php echo $content; ?>" onclick="arm_open_restriction_shortcode_popup();" href="javascript:void(0)"><?php _e('RESTRICT CONTENT', 'ARMember'); ?></a>
            </div>
            <?php
        }

        /**
         * TinyMCE Editor Popup Window Content
         */
        function arm_insert_shortcode_popup() {
           
            $arm_allowed_pages_for_shortcode_popup = apply_filters(
                'arm_allowed_pages_for_shortcode_popup',
                array('post.php', 'page.php', 'post-new.php', 'page-new.php')
            );

            if ( !in_array( basename( $_SERVER['PHP_SELF'] ), $arm_allowed_pages_for_shortcode_popup ) ) {
                return;
            }

            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_tinymce_options_shortcodes.php')) {
                require ( MEMBERSHIP_VIEWS_DIR . '/arm_tinymce_options_shortcodes.php');
            }

        }

        /**
         * Add Button in TinyMCE Editor.
         */
        function arm_add_tinymce_styles() {

            $arm_enqueue_shortcode_styles = apply_filters(
                'arm_enqueue_shortcode_styles',
                array('post.php', 'page.php', 'post-new.php', 'page-new.php')
            );

            if (!in_array(basename($_SERVER['PHP_SELF']), $arm_enqueue_shortcode_styles)) {
                return;
            }
            wp_enqueue_script('jquery');
            wp_enqueue_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);
            wp_enqueue_script('arm_icheck-js', MEMBERSHIP_URL . '/js/icheck.js', array('jquery'), MEMBERSHIP_VERSION);
            wp_enqueue_script('arm_tinymce', MEMBERSHIP_URL . '/js/arm_tinymce_member.js', array('jquery'), MEMBERSHIP_VERSION);
            wp_enqueue_script('arm_colpick-js', MEMBERSHIP_URL . '/js/colpick.min.js', array('jquery'), MEMBERSHIP_VERSION);
            wp_enqueue_script('arm_t_chosen_jq_min', MEMBERSHIP_URL . '/js/chosen.jquery.min.js', array('jquery'), MEMBERSHIP_VERSION);

            wp_enqueue_style('arm-font-awesome', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
            wp_enqueue_style('arm_tinymce', MEMBERSHIP_URL . '/css/arm_tinymce.css', array(), MEMBERSHIP_VERSION);
            wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
            wp_enqueue_style('arm_chosen_selectbox', MEMBERSHIP_URL . '/css/chosen.css', array(), MEMBERSHIP_VERSION);
        }

        function arm_editor_mce_buttons($buttons) {
            global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
                $buttons = (!empty($buttons)) ? $buttons : array();
                $boldKey = array_search('bold', $buttons);
                $italicKey = array_search('italic', $buttons);
                unset($buttons[$boldKey]);
                unset($buttons[$italicKey]);
                $armMceButtons = array(
                    'fontselect',
                    'fontsizeselect',
                    'forecolor',
                    'bold',
                    'italic',
                    'underline',
                );
                $buttons = array_merge($armMceButtons, $buttons);
            }
            return $buttons;
        }

        function arm_editor_mce_buttons_2($buttons) {
            global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
                $forecolorKey = array_search('forecolor', $buttons);
                $underlineKey = array_search('underline', $buttons);
                unset($buttons[$forecolorKey]);
                unset($buttons[$underlineKey]);
            }
            return $buttons;
        }

        function arm_editor_font_sizes($initArray) {
            global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs, $arm_member_forms;
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
                $armFontFamily = $armFontSizes = "";
                for ($i = 8; $i <= 40; $i++) {
                    $armFontSizes .= "{$i}px ";
                }
                $initArray['fontsize_formats'] = trim($armFontSizes, " ");
                /**
                 * Font-Family List
                 */
                $allFonts = array('Arial', 'Helvetica', 'sans-serif', 'Lucida Grande', 'Lucida Sans Unicode', 'Tahoma', 'Times New Roman', 'Courier New', 'Verdana', 'Geneva', 'Courier', 'Monospace', 'Times', 'Open Sans Semibold', 'Open Sans Bold');
                /* $g_fonts = $arm_member_forms->arm_google_fonts_list();
                  $allFonts = array_merge($allFonts, $g_fonts); */
                foreach ($allFonts as $font) {
                    $armFontFamily .= $font . '=' . $font . ';';
                }
                $initArray['font_formats'] = trim($armFontFamily, " ");
            }
            return $initArray;
        }

        function arm_conditional_redirection_shortcode_func($atts, $content) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $ARMember, $arm_case_types, $post, $current_user;
            if (current_user_can('administrator')) {
                return;
            }
            $atts = shortcode_atts(array(
                'condition' => 'equals',
                'redirect_to' => ARM_HOME_URL,
                'plans' => '',
                    ), $atts);

            if ($atts['plans'] == '' || empty($atts['plans']))
                return;

            $condition = (isset($atts['condition']) && $atts['condition'] != '') ? $atts['condition'] : 'equals';
            $plans_array = explode(",", $atts['plans']);
            $redirect_to = (isset($atts['redirect_to']) && $atts['redirect_to'] != '') ? $atts['redirect_to'] : ARM_HOME_URL;


            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_plan_id = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plan_id = !empty($user_plan_id) ? $user_plan_id : array();
                $return_array = array_intersect($user_plan_id, $plans_array);
                if (in_array($condition, array('equals', 'having'))) {
                    if (in_array('not_logged_in', $plans_array) || empty($return_array))
                        return;
                }
                else {
                    if (!in_array('not_logged_in', $plans_array) && !empty($return_array))
                        return;
                }
            }
            else {
                if (in_array($condition, array('equals', 'having'))) {
                    if (!in_array('not_logged_in', $plans_array))
                        return;
                }
                else {
                    return;
                }
            }
            if (MEMBERSHIP_DEBUG_LOG == true) {
                if (MEMBERSHIP_DEBUG_LOG_TYPE != "ARM_ADMIN_PANEL") {
                    $arm_case_types['shortcode']['protected'] = true;
                    $arm_case_types['shortcode']['type'] = 'redirect';
                    $arm_case_types['shortcode']['message'] = @$post->post_type . ' ' . __("is protected by admin using conditional redirect shortcode", 'ARMember');
                    $ARMember->arm_debug_response_log('arm_conditional_redirection_shortcode_func', $arm_case_types, array(), $wpdb->last_query);
                }
            }
            @header("HTTP/1.1 301 Moved Permanently");
            wp_redirect($redirect_to);
        }

        function arm_conditional_redirection_by_user_role_shortcode_func($atts, $content) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            /* ====================/.Begin Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $ARMember, $arm_case_types, $post, $current_user;
            if (current_user_can('administrator')) {
                return;
            }
            $atts = shortcode_atts(array(
                'condition' => 'having',
                'redirect_to' => ARM_HOME_URL,
                'roles' => '',
                    ), $atts);

            if ($atts['roles'] == '' || empty($atts['roles']))
                return;

            $condition = (isset($atts['condition']) && $atts['condition'] != '') ? $atts['condition'] : 'having';
            $roles_array = explode(",", $atts['roles']);
            $redirect_to = (isset($atts['redirect_to']) && $atts['redirect_to'] != '') ? $atts['redirect_to'] : ARM_HOME_URL;


            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_info = get_userdata($user_id);
                $user_roles = $user_info->roles;
                $return_array = array_intersect($user_roles, $roles_array);
                if (in_array($condition, array('equals', 'having'))) {
                    if (count($return_array) == 0) {
                        return;
                    }
                } else if (in_array($condition, array('notequals', 'nothaving'))) {
                    if (count($return_array) > 0) {
                        return;
                    }
                } else {
                    return;
                }
            } else {
                return;
            }

            @header("HTTP/1.1 301 Moved Permanently");
            wp_redirect($redirect_to);
        }

        function arm_username_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $return_content = '';
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_data = wp_get_current_user($user_id);
                $return_content = $user_data->data->user_login;
            }

            return $return_content;
        }

        function arm_userid_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $return_content = '';
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $return_content = $user_id;
            }

            return $return_content;
        }

        function arm_displayname_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
           
            $return_content = '';

            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_data = wp_get_current_user($user_id);
                $return_content = $user_data->data->display_name;
            }
            return $return_content;
        }

        function arm_avatar_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $avatar = '';

            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_data = wp_get_current_user($user_id);
                $user_email = $user_data->data->user_email;

                $avatar = get_avatar($user_email);
            }
            return $avatar;
        }

        function arm_firstname_lastname_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            
            $return_content = '';
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $return_content = get_user_meta($user_id, 'first_name', true) . " " . get_user_meta($user_id, 'last_name', true);
            }
            return $return_content;
        }
        function arm_user_plan_func() {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            $user_current_plan = '';
            $user_current_plan_arr = array();
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $all_plans_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_posts = get_user_meta($user_id, 'arm_user_post_ids', true);
                if(!empty($all_plans_ids) && !empty($user_posts))
                    {
                        foreach ($all_plans_ids as $user_plans_key => $user_plans_val) {
                            if(!empty($user_posts)){
                                foreach ($user_posts as $user_post_key => $user_post_val) {
                                    if($user_post_key==$user_plans_val){
                                        unset($all_plans_ids[$user_plans_key]);
                                    }
                                }
                            }
                        }
                    }
                if( ! empty($all_plans_ids)) {
                    foreach ($all_plans_ids as $single_plans_id) {
                        $single_plan_details = get_user_meta($user_id, 'arm_user_plan_' . $single_plans_id, true);
                        if( ! empty($single_plan_details) && isset($single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name']) && $single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name'] != "" ) {
                            $plan_name = $single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name'];
                            $user_current_plan_arr[] = "<span class='arm_plan_". strtolower(str_replace(" ", "_", $plan_name))."' >" . $plan_name . "</span>";
                        }
                    }
                }
            }
            if( ! empty($user_current_plan_arr) ) {
                $user_current_plan = implode("<span class='arm_plan_divider'>, </span>", $user_current_plan_arr);
            }
            return $user_current_plan;
        }

        function arm_usermeta_func($atts, $content, $tag) {
            global $ARMember, $arm_member_forms;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            global $ARMember, $arm_global_settings;
            $return_content = '';

            if( isset($atts['id']) && $atts['id'] != '' && $atts['id'] > 0){
                $user_id = $atts['id'];
            } else if(is_user_logged_in()) {
                $user_id = get_current_user_id();
            }

            if (isset($atts['meta']) && $atts['meta'] != "") {
                $user_object = get_user_by('ID',$user_id);
                $meta_name = $atts['meta'];
                
                switch($meta_name){
                    case 'user_login':
                    case 'user_email':
                    case 'display_name':
                    case 'user_nicename':
                    case 'user_url':
                        if('user_url' == $meta_name) {
                            $return_content = "<a class='arm_user_url' href='".$user_object->data->$meta_name."' target='_blank'>".$user_object->data->$meta_name."</a>";
                        } else {
                            $return_content = $user_object->data->$meta_name;
                        }
                    break;
                    case 'avatar':
                        $return_content = get_avatar($user_object ->user_email);
                        break;
                    default:
                        $return_content = get_user_meta($user_id, $meta_name, true);
                        $arm_filed_options=$arm_member_forms->arm_get_field_option_by_meta($meta_name);
                        
                        $arm_field_type=(isset($arm_filed_options['type']) && !empty($arm_filed_options['type']))? $arm_filed_options['type']:'';
                        
                        if($arm_field_type=="date"){
                            if ($return_content != '') {
                                $date_time_format = $arm_global_settings->arm_get_wp_date_format();
                                $return_content = date_i18n($date_time_format, strtotime($return_content));
                            }    

                        }
                        else if($arm_field_type=='file'){
                            if ($return_content != '') {
                                $exp_val = explode("/", $return_content);
                                $filename = $exp_val[count($exp_val) - 1];
                                $file_extension = explode('.', $filename);
                                $file_ext = $file_extension[count($file_extension) - 1];
                                if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF'))) {
                                    $fileUrl = $return_content;
                                } else {
                                    $fileUrl = MEMBERSHIP_IMAGES_URL . '/file_icon.png';
                                }


                                if (preg_match("@^http@", $return_content)) {
                                    $temp_data = explode("://", $return_content);
                                    $return_content = '//' . $temp_data[1];
                                }

                                if (file_exists(strstr($fileUrl, "//"))) {
                                    $fileUrl = strstr($fileUrl, "//");
                                }

                                $return_content = '<div class="arm_old_uploaded_file"><a href="' . $return_content . '" target="__blank"><img alt="" src="' . ($fileUrl) . '" width="100px"/></a></div>';
                            }
                        }
                        else if($arm_field_type == 'select' || $arm_field_type == 'radio' || ($arm_field_type == 'checkbox' && !is_array($return_content) ) ){
                            if(!empty($return_content))
                            {
                                $arm_tmp_select_val = !empty($arm_filed_options['options']) ? $arm_filed_options['options'] : '';
                                foreach($arm_tmp_select_val as $arm_tmp_select_key => $arm_tmp_val)
                                {
                                    $arm_tmp_select_val_arr = explode(':', $arm_tmp_val);
                                    $arm_tmp_selected_option_val = end($arm_tmp_select_val_arr);
                                    if($arm_tmp_selected_option_val == $return_content)
                                    {
                                        $return_content = str_replace(':'.$arm_tmp_selected_option_val, '', $arm_tmp_val);
                                        break;
                                    }
                                }
                            }
                        }
                        /*
                        else if($arm_field_type == 'checkbox'){
                            if(!empty($return_content))
                            {
                                $return_content_checkbox = "";
                                $return_content_checkbox_arr = $return_content;
                                foreach ($return_content_checkbox_arr as $return_content_checkbox_arr_val) {
                                    $arm_tmp_select_val = !empty($arm_filed_options['options']) ? $arm_filed_options['options'] : '';
                                    $return_content_one_checkbox = "";
                                    foreach($arm_tmp_select_val as $arm_tmp_select_key => $arm_tmp_val)
                                    {
                                        $arm_tmp_select_val_arr = explode(':', $arm_tmp_val);
                                        $arm_tmp_selected_option_val = end($arm_tmp_select_val_arr);
                                        if($arm_tmp_selected_option_val == $return_content_checkbox_arr_val)
                                        {
                                            $return_content_checkbox .= $return_content_one_checkbox .= str_replace(':'.$arm_tmp_selected_option_val, '', $arm_tmp_val).', ';
                                            break;
                                        }
                                    }
                                    if(empty($return_content_one_checkbox))
                                    {
                                        $return_content_checkbox .= $return_content_one_checkbox .= $return_content_checkbox_arr_val.', ';
                                    }

                                }
                                if(!empty($return_content_checkbox))
                                {
                                    $return_content_checkbox = trim($return_content_checkbox);
                                    $return_content = rtrim($return_content_checkbox,',');
                                }
                            }
                        }
                        */
                    break;    
                }
                if(is_array($return_content)){
                    $return_content = $ARMember->arm_array_trim($return_content);
                    $return_content = implode(', ', $return_content);
                }
            }
            $return_content = stripslashes_deep($return_content);
            return $return_content;
        }

        function arm_user_badge_func($atts, $content, $tag) {
            global $ARMember, $arm_load_tipso;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            if (current_user_can('administrator')) {
                return;
            }
            global $arm_members_badges, $arm_global_settings;
            $user_id = 0;
            $arm_load_tipso = 1;
            if (!wp_style_is('arm_form_style_css', 'enqueued')) {
                wp_enqueue_style('arm_form_style_css');
            }
            
            if (isset($atts['user_id']) && $atts['user_id'] > 0) {
                $user_id = $atts['user_id'];
            } else if (is_user_logged_in()) {
                $user_id = get_current_user_id();
            }
            if ($user_id > 0) {
                $badge_data = $arm_members_badges->arm_get_user_achievements_detail($user_id);

                if (!empty($badge_data)) {
                    $global_settings = $arm_global_settings->global_settings;
                    $badge_width = !empty($global_settings['badge_width']) ? $global_settings['badge_width'] : 30;
                    $badge_height = !empty($global_settings['badge_height']) ? $global_settings['badge_height'] : 30;
                    $content .= '<div class="arm_user_badges_detail">';
                    foreach ($badge_data as $badge) {
                        $arm_badge_url = $badge['badge_icon'];
                        $content .= '<span class="arm-user-badge armhelptip_front" title="'. $badge['badge_title'] .'"><img src="' . $arm_badge_url . '" width="' . $badge_width . '" height="' . $badge_height . '" /></span>';
                    }
                    $content .= '</div>';
                }
            }

            return $content;
        }

        function arm_user_planinfo_func($atts, $content, $tag) {
            global $ARMember;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            
            if (current_user_can('administrator')) {
                return;
            }
            global $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways;

            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                if (isset($atts['plan_id']) && !empty($atts['plan_id'])) {
                    $plan_id = $atts['plan_id'];

                    $user_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $user_plan_ids = !empty($user_plan_ids) ? $user_plan_ids : array();
                    $date_format = $arm_global_settings->arm_get_wp_date_format();
                    if (in_array($plan_id, $user_plan_ids)) {
                        
                   
                        if (isset($atts['plan_info']) && !empty($atts['plan_info'])) {
                            $plan_info = trim($atts['plan_info']);
                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                            $userPlanDatameta = !empty($planData) ? $planData : array();
                            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                            switch ($plan_info) {
                                case 'arm_start_plan':
                                       
                                    if(!empty($planData['arm_start_plan'])){
                                        
                                        $content.= date_i18n($date_format, $planData['arm_start_plan']);
                                    }
                                    break;
                                case 'arm_expire_plan':
                                    if(!empty($planData['arm_expire_plan'])){
                                        $content.= date_i18n($date_format, $planData['arm_expire_plan']);
                                    }
                                    break;
                                case 'arm_trial_start':
                                    if(!empty($planData['arm_trial_start'])){
                                        $content.= date_i18n($date_format, $planData['arm_trial_start']);
                                    }
                                    break;
                                case 'arm_trial_end':
                                    if(!empty($planData['arm_trial_end'])){
                                        $content.= date_i18n($date_format, $planData['arm_trial_end']);
                                    }
                                    break;
                                case 'arm_grace_period_end':
                                    if(!empty($planData['arm_grace_period_end'])){
                                        $content.= date_i18n($date_format, $planData['arm_grace_period_end']);
                                    }
                                    break;
                                case 'arm_user_gateway':
                                    if(!empty($planData['arm_user_gateway'])){
                                        $content.= $arm_payment_gateways->arm_gateway_name_by_key($planData['arm_user_gateway']);
                                    }
                                    break;
                                case 'arm_completed_recurring':
                                        $content.= $planData['arm_completed_recurring'];
                                    break;
                                case 'arm_next_due_payment':
                                    if(!empty($planData['arm_next_due_payment'])){
                                        $content.= date_i18n($date_format, $planData['arm_next_due_payment']);
                                    }
                                    break;
                                case 'arm_payment_mode':
                                    if(!empty($planData['arm_payment_mode'])){
                                        if($planData['arm_payment_mode'] == 'auto_debit_subscription'){
                                           $content.= __('Automatic Subscription', 'ARMember');
                                        }else if($planData['arm_payment_mode'] == 'manual_subscription'){
                                           $content.= __('Semi Automatic Subscription', 'ARMember'); 
                                        }
                                    }
                                    break;
                                case 'arm_payment_cycle':
                                    if($planData['arm_payment_cycle'] != ''){
                                        $user_selected_payment_cycle = $planData['arm_payment_cycle']; 
                                        $plan_detail = $planData['arm_current_plan_detail'];
                                        $plan_options = maybe_unserialize($plan_detail['arm_subscription_plan_options']);
                                        $payment_cycle_data = $plan_options['payment_cycles'];
                                        
                                        if(!empty($payment_cycle_data)){
                                            if(isset($payment_cycle_data[$user_selected_payment_cycle]) && !empty($payment_cycle_data[$user_selected_payment_cycle])){
                                                $content .= $payment_cycle_data[$user_selected_payment_cycle]['cycle_label'];
                                            }
                                        }
                                    }
                                    break;
                                case 'default':
                                    break;
                            }
                        }
                    }
                }
            }
            return $content;
        }

        function arm_login_history_function($atts, $content, $tag) {

            $default_login_history_fields = __('User', 'ARMember') . ',' . __('Logged in date', 'ARMember') . ',' . __('Logged in IP', 'ARMember') . ',' . __('Logged in using', 'ARMember') . ',' . __('Logged out date', 'ARMember') . ',' . __('Login duration', 'ARMember');
            $defaults = array(
                'label' => 'user,logged_in_date,logged_in_ip,logged_in_using,logged_out_date,login_duration',
                'value' => $default_login_history_fields,
                'current_page' => 0,
            );
            /* Extract Shortcode Attributes */
            $args = shortcode_atts($defaults, $atts, $tag);
            $labels = explode(',', rtrim($args['label'], ','));
            $values = explode(',', rtrim($args['value'], ','));

            extract($args);
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $current_site, $arm_errors, $ARMember, $arm_global_settings;
            $date_format = $arm_global_settings->arm_get_wp_date_format();

            $message_no_record = __("No Login History Found", 'ARMember');
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $user_obj = new WP_User($user_id);
                $per_page = 10;
                $offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
                $login_history_table = $ARMember->tbl_arm_login_history;
                $login_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$login_history_table}` WHERE `arm_user_id` = %d ORDER BY `arm_logged_in_date` ASC ", $user_id));


                $trans_count = count($login_history);
                $transactions = array_slice($login_history, $offset, $per_page);

                $content = apply_filters('arm_before_login_history_shortcode_content', $content, $args);


                $content .= "<div class='arm_login_history_container'>";
                $frontfontstyle = $arm_global_settings->arm_get_front_font_style();
                $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
                $content .= '<style type="text/css">';
                $transactionsWrapperClass = ".arm_login_history_container";
                $content .= "
						
						$transactionsWrapperClass .arm_login_history_list_header th{
							{$frontfontstyle['frontOptions']['level_2_font']['font']}
						}
						$transactionsWrapperClass .arm_login_history_list_item td{
							{$frontfontstyle['frontOptions']['level_3_font']['font']}
						}";
                $content .= '</style>';

                $content .= '<form method="POST" class="arm_login_history_form_container">';
                $content .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';
                $content .= "<div class='arm_login_history_wrapper'>";
                if (!empty($transactions)) {

                    if (is_rtl()) {
                        $is_transaction_class_rtl = 'is_login_history_class_rtl';
                    } else {
                        $is_transaction_class_rtl = '';
                    }
                    $content .= "<div class='arm_login_history_content " . $is_transaction_class_rtl . "'>";
                    $content .= "<table class='arm_user_login_history_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0'>";
                    $content .= "<thead>";
                    $content .= "<tr class='arm_login_history_list_header'>";
                    $has_user = true;
                    $has_logged_in_date = true;
                    $has_logged_in_ip = true;
                    $has_logged_in_using = true;
                    $has_logged_out_date = true;
                    $has_login_duration = true;

                    if (in_array('user', $labels)) {

                        $label_key = array_search('user', $labels);
                        $l_user = !empty($values[$label_key]) ? $values[$label_key] : __('User', 'ARMember');
                    } else {
                        $has_user = false;
                    }
                    if (in_array('logged_in_date', $labels)) {
                        $label_key = array_search('logged_in_date', $labels);
                        $l_logged_in_date = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in date', 'ARMember');
                    } else {
                        $has_logged_in_date = false;
                    }
                    if (in_array('logged_in_ip', $labels)) {
                        $label_key = array_search('logged_in_ip', $labels);
                        $l_logged_in_ip = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in IP', 'ARMember');
                    } else {
                        $has_logged_in_ip = false;
                    }
                    if (in_array('logged_in_using', $labels)) {
                        $label_key = array_search('logged_in_using', $labels);
                        $l_logged_in_using = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in using', 'ARMember');
                    } else {
                        $has_logged_in_using = false;
                    }
                    if (in_array('logged_out_date', $labels)) {
                        $label_key = array_search('logged_out_date', $labels);
                        $l_logged_out_date = !empty($values[$label_key]) ? $values[$label_key] : __('Logged out date', 'ARMember');
                    } else {
                        $has_logged_out_date = false;
                    }
                    if (in_array('login_duration', $labels)) {
                        $label_key = array_search('login_duration', $labels);
                        $l_login_duration = !empty($values[$label_key]) ? $values[$label_key] : __('Log in duration', 'ARMember');
                    } else {
                        $has_login_duration = false;
                    }
                    if ($has_user):
                        $content .= "<th class='arm_sortable_th'>{$l_user}</th>";
                    endif;
                    if ($has_logged_in_date):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_date}</th>";
                    endif;
                    if ($has_logged_in_ip):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_ip}</th>";
                    endif;
                    if ($has_logged_in_using):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_using}</th>";
                    endif;
                    if ($has_logged_out_date):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_out_date}</th>";
                    endif;
                    if ($has_login_duration):
                        $content .= "<th class='arm_sortable_th'>{$l_login_duration}</th>";
                    endif;
                    $content .= "</tr>";
                    $content .= "</thead>";
                    foreach ($transactions as $r => $history) {

                        $content .="<tr class='arm_login_history_list_item'>";
                        if ($has_user):
                            $content .="<td data-label='{$l_user}'>" . $user_obj->data->display_name . "</td>";
                        endif;
                        if ($has_logged_in_date):
                            $content .="<td data-label='{$l_logged_in_date}'>" . $history->arm_logged_in_date . "</td>";
                        endif;
                        if ($has_logged_in_ip):
                            $content .="<td data-label='{$l_logged_in_ip}'>" . $history->arm_logged_in_ip . "</td>";
                        endif;
                        if ($has_logged_in_using):
                            $content .="<td data-label='{$l_logged_in_using}'>" . $history->arm_history_browser . "</td>";
                        endif;
                        if ($has_logged_out_date):
                            $content .="<td data-label='{$l_logged_out_date}'>" . (($history->arm_logout_date == '0000-00-00 00:00:00') ? __('Currently Logged in', 'ARMember') : $history->arm_logout_date) . "</td>";
                        endif;
                        if ($has_login_duration):
                            $content .="<td data-label='{$l_login_duration}'>" . (($history->arm_login_duration == '00:00:00') ? '-' : $history->arm_login_duration) . "</td>";
                        endif;
                        $content .="</tr>";
                    }
                    $content .= "</table>";
                    $transPaging = $arm_global_settings->arm_get_paging_links($current_page, $trans_count, $per_page, 'login_history');
                    $content .= "<div class='arm_login_history_paging_container'>$transPaging</div>";
                    $content .= "</div>";
                } else {
                    if (is_rtl()) {
                        $is_transaction_class_rtl = 'is_login_histroy_class_rtl';
                    } else {
                        $is_transaction_class_rtl = '';
                    }
                    $content .= "<div class='arm_login_history_content " . $is_transaction_class_rtl . "'>";
                    $content .= "<table class='arm_user_login_history_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0' style='border-collapse:unset;'>";
                    $content .= "<thead>";
                    $content .= "<tr class='arm_login_history_list_header'>";
                    $has_user = true;
                    $has_logged_in_date = true;
                    $has_logged_in_ip = true;
                    $has_logged_in_using = true;
                    $has_logged_out_date = true;
                    $has_login_duration = true;
                    $i = 0;
                    if (in_array('user', $labels)) {
                        $label_key = array_search('user', $labels);
                        $l_user = !empty($values[$label_key]) ? $values[$label_key] : __('User', 'ARMember');
                        $i++;
                    } else {
                        $has_user = false;
                    }
                    if (in_array('logged_in_date', $labels)) {
                        $label_key = array_search('logged_in_date', $labels);
                        $l_logged_in_date = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in date', 'ARMember');
                        $i++;
                    } else {
                        $has_logged_in_date = false;
                    }
                    if (in_array('logged_in_ip', $labels)) {
                        $label_key = array_search('logged_in_ip', $labels);
                        $l_logged_in_ip = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in IP', 'ARMember');
                        $i++;
                    } else {
                        $has_logged_in_ip = false;
                    }
                    if (in_array('logged_in_using', $labels)) {
                        $label_key = array_search('logged_in_using', $labels);
                        $l_logged_in_using = !empty($values[$label_key]) ? $values[$label_key] : __('Logged in using', 'ARMember');
                        $i++;
                    } else {
                        $has_logged_in_using = false;
                    }
                    if (in_array('logged_out_date', $labels)) {
                        $label_key = array_search('logged_out_date', $labels);
                        $l_logged_out_date = !empty($values[$label_key]) ? $values[$label_key] : __('Logged out date', 'ARMember');
                        $i++;
                    } else {
                        $has_logged_out_date = false;
                    }
                    if (in_array('login_duration', $labels)) {
                        $label_key = array_search('login_duration', $labels);
                        $l_login_duration = !empty($values[$label_key]) ? $values[$label_key] : __('Log in duration', 'ARMember');
                        $i++;
                    } else {
                        $has_login_duration = false;
                    }
                    if ($has_user):
                        $content .= "<th class='arm_sortable_th'>{$l_user}</th>";
                    endif;
                    if ($has_logged_in_date):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_date}</th>";
                    endif;
                    if ($has_logged_in_ip):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_ip}</th>";
                    endif;
                    if ($has_logged_in_using):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_in_using}</th>";
                    endif;
                    if ($has_logged_out_date):
                        $content .= "<th class='arm_sortable_th'>{$l_logged_out_date}</th>";
                    endif;
                    if ($has_login_duration):
                        $content .= "<th class='arm_sortable_th'>{$l_login_duration}</th>";
                    endif;
                    $content .= "</tr>";
                    $content .= "</thead>";


                    $content .="<tr class='arm_login_history_list_item'>";
                    $content .="<td colspan='" . $i . "' class='arm_no_login_history' style='text-align: center;'>$message_no_record</td>";
                    $content .="</tr>";

                    $content .= "</table>";

                    $content .= "</div>";
                }
                $content .= "</div>";
                $content .= "<div class='armclear'></div>";


                /* Template Arguments Inputs */
                foreach (array('label', 'value') as $k) {
                    $content .= '<input type="hidden" class="arm_login_history_field_' . $k . '" name="' . $k . '" value="' . $args[$k] . '">';
                }


                $content .= '</form>';

                $content .= "</div>";


                $content = apply_filters('arm_after_login_history_shortcode_content', $content, $args);
            }
            return do_shortcode($content);
        }

        function arm_last_login_history_function() {
            global $wpdb, $ARMember, $current_user, $arm_global_settings;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            if (current_user_can('administrator') || !is_user_logged_in()) {
                return;
            }
            $user_id = get_current_user_id();
            $user_obj = new WP_User($user_id);
            $login_history_table = $ARMember->tbl_arm_login_history;
            $get_history = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$login_history_table}` WHERE `arm_user_id` = %d AND `arm_logout_date` != %s ORDER BY arm_history_id LIMIT 1", $user_id, "0000-00-00 00:00:00"));

            $output = "";

            $output .= "<table border='0'>";
            $output .= "<tr>";
            $output .= "<th colspan='2'>";
            $output .= __("Your Last Login History", 'ARMember');
            $output .= "</th>";
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td>" . __('Last Login IP', 'ARMember') . "</td>";
            $output .= "<td>" . $get_history->arm_logged_in_ip . "<td>";
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td>" . __('Last Login Date', 'ARMember') . "</td>";
            $output .= "<td>" . date_i18n($date_format, strtotime($get_history->arm_logged_in_date)) . "</td>";
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<td>" . __('Last Login Using', 'ARMember') . "</td>";
            $output .= "<td>" . $get_history->arm_history_browser . "</td>";
            $output .= "</tr>";
            $output .= "</table>";

            echo $output;
        }

        function arm_br2nl($arm_string) {
            return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $arm_string);
        }
    }
}
global $arm_shortcodes;
$arm_shortcodes = new ARM_shortcodes();