<?php

if (!class_exists('ARM_payment_gateways')) {

    class ARM_payment_gateways {

        var $currency;

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs;
            add_action('wp_ajax_arm_update_pay_gate_settings', array($this, 'arm_update_pay_gate_settings'));
            add_filter('arm_change_user_meta_before_save', array($this, 'arm_filter_form_posted_plan_data'), 10, 2);
            add_action('wp_ajax_arm_check_currency_status', array($this, 'arm_check_currency_status'));
            $this->currency = array(
                'paypal' => $this->arm_paypal_currency_symbol(),
                'stripe' => $this->arm_stripe_currency_symbol(),
                'authorize_net' => $this->arm_authorize_net_currency_symbol(),
                '2checkout' => $this->arm_2checkout_currency_symbol(),
                'bank_transfer' => $this->arm_bank_transfer_currency_symbol(),
            );

            add_action('wp_ajax_arm_view_payment_debug_log', array($this, 'arm_view_payment_debug_log'));
            add_action('wp_ajax_arm_view_general_debug_log', array($this, 'arm_view_general_debug_log'));

            add_action('wp_ajax_arm_download_payment_debug_log', array($this, 'arm_download_payment_debug_log'));
            add_action('wp_ajax_arm_download_general_debug_log', array($this, 'arm_download_general_debug_log'));
            
        }

        function arm_debug_log_download_file(){
            if( !empty( $_REQUEST['arm_action'] ) && 'download_log' == $_REQUEST['arm_action'] ){
                global $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
                
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

                $filename = !empty($_REQUEST['file']) ? basename($_REQUEST['file']) : '';
                if(!empty($filename))
                {
                    $file_path = MEMBERSHIP_UPLOAD_DIR . '/' . $filename;

                    $allowexts = array( "txt", "zip" );

                    $file_name_arm = substr($filename, 0,3);

                    $checkext = explode(".", $filename);
                    $ext = strtolower( $checkext[count($checkext) - 1] );

                    if(!empty($ext) && in_array($ext, $allowexts) && !empty($filename) && file_exists($file_path))
                    {
                        ignore_user_abort();
                        $now = gmdate("D, d M Y H:i:s");
                        header("Expires: Tue, 03 Jul 2020 06:00:00 GMT");
                        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
                        header("Last-Modified: {$now} GMT");
                        header("Content-Type: application/force-download");
                        header("Content-Type: application/octet-stream");
                        header("Content-Type: application/download");
                        header("Content-Disposition: attachment;filename={$filename}");
                        header("Content-Transfer-Encoding: binary");

                        readfile($file_path);

                        unlink( $file_path );

                        $arm_txt_file_name = str_replace('.zip', '.txt', $filename);
                        $arm_txt_file_path = MEMBERSHIP_UPLOAD_DIR . '/' . $arm_txt_file_name;
                        if(file_exists($arm_txt_file_path))
                        {
                            unlink($arm_txt_file_path);
                        }

                        die;
                    }
                }
            }
        }

        function arm_download_general_debug_log(){
            global $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $arm_download_url = '';
            if(!empty($_POST['arm_download_key']))
            {
                $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
                $arm_download_key = $_POST['arm_download_key'];
                $arm_selected_download_duration = !empty($_POST['selected_download_duration']) ? $_POST['selected_download_duration'] : 'all';

                $arm_custom_duration_start_date = !empty($_POST['arm_filter_pstart_date']) ? $_POST['arm_filter_pstart_date'] : '';
                $arm_custom_duration_end_date = !empty($_POST['arm_filter_pend_date']) ? $_POST['arm_filter_pend_date'] : '';

                $arm_debug_general_log_where_cond = "";
                if(!empty($arm_custom_duration_start_date) && !empty($arm_custom_duration_end_date) && $arm_selected_download_duration == "custom")
                {
                    $arm_custom_duration_start_date = date('Y-m-d 00:00:00', strtotime($arm_custom_duration_start_date));

                    $arm_custom_duration_end_date = date('Y-m-d 23:59:59', strtotime($arm_custom_duration_end_date));

                    $arm_debug_general_log_where_cond = " AND (arm_general_log_added_date >= '".$arm_custom_duration_start_date."' AND arm_general_log_added_date <= '".$arm_custom_duration_end_date."')";
                }
                else if(!empty($arm_selected_download_duration) && $arm_selected_download_duration != "custom")
                {
                    $arm_last_selected_days = date('Y-m-d', strtotime('-'.$arm_selected_download_duration.' days'));

                    $arm_debug_general_log_where_cond = " AND (arm_general_log_added_date >= '".$arm_last_selected_days."')";
                }


                $arm_general_debug_log_selector_search = "";
                if($arm_download_key!='cron') 
                {
                    $arm_general_debug_log_selector_search = " `arm_general_log_event`!='cron' ";
                }
                else 
                {
                    $arm_general_debug_log_selector_search = " `arm_general_log_event` ='".$arm_download_key."' ";
                }

                $total_log_data = $wpdb->get_var($wpdb->prepare("SELECT count(arm_general_log_id) as total_records FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ".$arm_debug_general_log_where_cond." ORDER BY arm_general_log_id DESC") );
                
                /*if($total_log_data>10000)
                {
                   $total_log_data = 10000; 
                }*/

                $arm_debug_log_data = $arm_debug_log_arr = array();
                $chunk_data_var = 1000;
                if(!empty($total_log_data))
                {
                    $totaloccurance = ceil($total_log_data/$chunk_data_var);
                    $startfrom = 0-$chunk_data_var;
                    $endto = 0;
                    for($cntr=0;$cntr<$totaloccurance;$cntr++)
                    {
                        $arm_debug_log_arr = array();
                        $startfrom += $chunk_data_var;
                        $endto += $chunk_data_var;

                        //echo '<br>==>'.$startfrom.', '.$endto.'|';

                        //echo "<pre><br>SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC LIMIT ".$startfrom.", ".$endto;

                        $arm_debug_log_arr = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC LIMIT %d,%d", $startfrom, $endto ), ARRAY_A);

                        $arm_debug_log_data = array_merge($arm_debug_log_data,$arm_debug_log_arr);
                    }
                }

                $arm_download_data = json_encode($arm_debug_log_data);

                if( !function_exists('WP_Filesystem' ) )
                {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                WP_Filesystem();
                global $wp_filesystem;

                $arm_debug_log_file_name = "arm_debug_logs_" . $arm_download_key."_".$arm_selected_download_duration ;
                $result = $wp_filesystem->put_contents( MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_download_data, 0777 );

                $debug_log_file_name  = '';

                if(class_exists('ZipArchive'))
                {
                    $zip = new ZipArchive();
                    $zip->open(MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".zip", ZipArchive::CREATE);
                    $zip->addFile(MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_debug_log_file_name.".txt");
                    $zip->close();

                    $arm_download_url = MEMBERSHIP_UPLOAD_URL."/".$arm_debug_log_file_name.".zip";

                    $debug_log_file_name = $arm_debug_log_file_name . ".zip";
                } else {
                    $arm_download_url = MEMBERSHIP_UPLOAD_URL."/".$arm_debug_log_file_name.".txt";
                    $debug_log_file_name = $arm_debug_log_file_name . ".txt";
                }
            }

            echo admin_url( 'admin.php?page=arm_general_settings&action=debug_logs&arm_action=download_log&file=' . $debug_log_file_name );
            exit();
        }

        function arm_download_payment_debug_log()
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $arm_download_url = '';
            if(!empty($_POST['arm_download_key']))
            {
                $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;
                $arm_download_key = $_POST['arm_download_key'];

                $arm_debug_payment_log_where_cond = "";

                $arm_selected_download_duration = !empty($_POST['selected_download_duration']) ? $_POST['selected_download_duration'] : 'all';

                $arm_custom_duration_start_date = !empty($_POST['arm_filter_pstart_date']) ? $_POST['arm_filter_pstart_date'] : '';
                $arm_custom_duration_end_date = !empty($_POST['arm_filter_pend_date']) ? $_POST['arm_filter_pend_date'] : '';
                if(!empty($arm_custom_duration_start_date) && !empty($arm_custom_duration_end_date) && $arm_selected_download_duration == "custom")
                {
                    $arm_custom_duration_start_date = date('Y-m-d 00:00:00', strtotime($arm_custom_duration_start_date));

                    $arm_custom_duration_end_date = date('Y-m-d 23:59:59', strtotime($arm_custom_duration_end_date));

                    $arm_debug_payment_log_where_cond = " AND (arm_payment_log_added_date >= '".$arm_custom_duration_start_date."' AND arm_payment_log_added_date <= '".$arm_custom_duration_end_date."')";
                }
                else if(!empty($arm_selected_download_duration) && $arm_selected_download_duration != "custom")
                {
                    $arm_last_selected_days = date('Y-m-d', strtotime('-'.$arm_selected_download_duration.' days'));
                    $arm_debug_payment_log_where_cond = " AND (arm_payment_log_added_date >= '".$arm_last_selected_days."')";
                }

                $arm_debug_payment_log_query = "SELECT * FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = '".$arm_download_key."' AND `arm_payment_log_status` = 1 ".$arm_debug_payment_log_where_cond." ORDER BY arm_payment_log_id DESC";


                $arm_payment_debug_log_data = $wpdb->get_results($arm_debug_payment_log_query, ARRAY_A);

                $arm_download_data = json_encode($arm_payment_debug_log_data);

                if( !function_exists('WP_Filesystem' ) )
                {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                WP_Filesystem();
                global $wp_filesystem;

                $arm_debug_log_file_name = "arm_debug_logs_".$arm_download_key."_".$arm_selected_download_duration;
                $result = $wp_filesystem->put_contents( MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_download_data, 0777 );

                $debug_log_file_name  = '';

                if(class_exists('ZipArchive'))
                {
                    $zip = new ZipArchive();
                    $zip->open(MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".zip", ZipArchive::CREATE);
                    $zip->addFile(MEMBERSHIP_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_debug_log_file_name.".txt");
                    $zip->close();

                    $arm_download_url = MEMBERSHIP_UPLOAD_URL."/".$arm_debug_log_file_name.".zip";

                    $debug_log_file_name = $arm_debug_log_file_name . ".zip";
                } else {
                    $arm_download_url = MEMBERSHIP_UPLOAD_URL."/".$arm_debug_log_file_name.".txt";
                    $debug_log_file_name = $arm_debug_log_file_name . ".txt";
                }
            }

            echo admin_url( 'admin.php?page=arm_general_settings&action=debug_logs&arm_action=download_log&file=' . $debug_log_file_name );
            exit();
        }

        function arm_view_payment_debug_log()
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $arm_payment_debug_log_html = '';
            if(!empty($_POST) && !empty($_POST['arm_debug_log_selector']))
            {
                $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;

                $arm_payment_debug_log_selector = $_POST['arm_debug_log_selector'];
                $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

                $current_page = !empty($_POST['page']) ? $_POST['page'] : 1;
                $perPage = !empty($_POST['per_page']) ? intval($_POST['per_page']) : 25;

                $offset = 0;
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }

                $totalRecord = $wpdb->get_var("SELECT COUNT('arm_payment_log_id') FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = '".$arm_payment_debug_log_selector."' AND `arm_payment_log_status`= 1 ORDER BY arm_payment_log_id DESC");

                $armPaymentDebugLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";

                $arm_payment_debug_log_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = %s AND `arm_payment_log_status`=%d ORDER BY arm_payment_log_id DESC {$armPaymentDebugLimit}", $arm_payment_debug_log_selector, 1), ARRAY_A);
                    $arm_payment_debug_log_html .= '<div class="arm_payment_debug_log_container">';
                $arm_payment_debug_log_html .= '<table class="form-table arm_member_last_subscriptions_table">';
                $arm_payment_debug_log_html .= '<tr>';
                $arm_payment_debug_log_html .= '<td><b>'.__('Log ID', 'ARMember').'</b></td>';
                $arm_payment_debug_log_html .= '<td><b>'.__('Log Name', 'ARMember').'</b></td>';
                $arm_payment_debug_log_html .= '<td class="arm_debug_log_raw_data"><b>'.__('Log Data', 'ARMember').'</b></td>';
                $arm_payment_debug_log_html .= '<td><b>'.__('Log Added Date', 'ARMember').'</b></td>';
                $arm_payment_debug_log_html .= '</tr>';


                foreach($arm_payment_debug_log_data as $arm_payment_debug_log_key => $arm_payment_debug_log_val)
                {
                    $arm_payment_debug_log_html .= '<tr>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_id'].'</td>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event'].'</td>';

                    //$arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_gateway'].'</td>';

                    /*$arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event'].'</td>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event_from'].'</td>';*/

                    $arm_payment_debug_log_html .= '<td class="arm_debug_log_raw_data">'.htmlspecialchars(utf8_encode($arm_payment_debug_log_val['arm_payment_log_raw_data'])).'</td>';

                    $arm_created_date = date_i18n($date_time_format, strtotime($arm_payment_debug_log_val['arm_payment_log_added_date']));
                    $arm_payment_debug_log_html .= '<td>'.$arm_created_date.'</td>';

                    $arm_payment_debug_log_html .= '</tr>';
                }

                if($totalRecord <= 0)
                {
                    $total_column = 4;
                    $arm_payment_debug_log_html .= '<tr>';
                    $arm_payment_debug_log_html .= '<td colspan="'.$total_column.'" style="text-align: center;">' . __('No debug logs found.', 'ARMember') . '</td>';
                    $arm_payment_debug_log_html .= '</tr>';
                }

                $arm_payment_debug_log_html .= '</table>';
                $arm_payment_debug_log_html .= '</div>';

                $arm_payment_debug_log_html .= '<div class="arm_membership_history_pagination_block">';
                $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage);
                $arm_payment_debug_log_html .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
                $arm_payment_debug_log_html .= '</div>';

            }
            echo $arm_payment_debug_log_html;
            exit();
        }

        function arm_view_general_debug_log()
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
            $arm_general_debug_log_html = '';
            if(!empty($_POST) && !empty($_POST['arm_debug_log_selector']))
            {
                $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;

                $arm_general_debug_log_selector = $_POST['arm_debug_log_selector'];

                $arm_general_debug_log_selector_search = "";
                if($arm_general_debug_log_selector!='cron') {
                    $arm_general_debug_log_selector_search = " `arm_general_log_event`!='cron' ";
                }
                else {
                    $arm_general_debug_log_selector_search = " `arm_general_log_event` ='".$arm_general_debug_log_selector."' ";
                }
                
                $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

                $current_page = !empty($_POST['page']) ? $_POST['page'] : 1;
                $perPage = !empty($_POST['per_page']) ? intval($_POST['per_page']) : 25;

                $offset = 0;
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }

                $totalRecord = $wpdb->get_var("SELECT COUNT('arm_general_log_id') FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC");

                $arm_general_debug_log_html .= '<div class="arm_general_debug_log_container">';
                $arm_general_debug_log_html .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
                $arm_general_debug_log_html .= '<tr>';
                    $arm_general_debug_log_html .= '<td><b>'.__('Log ID', 'ARMember').'</b></td>';
                    $arm_general_debug_log_html .= '<td><b>'.__('Log Name', 'ARMember').'</b></td>';
                    $arm_general_debug_log_html .= '<td class="arm_debug_log_raw_data"><b>'.__('Log Data', 'ARMember').'</b></td>';
                    $arm_general_debug_log_html .= '<td><b>'.__('Log Added Date', 'ARMember').'</b></td>';
                $arm_general_debug_log_html .= '</tr>';
                
                if($totalRecord>0)
                {
                    $armgeneralDebugLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";

                    $arm_general_debug_log_data = $wpdb->get_results($wpdb->prepare( "SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC {$armgeneralDebugLimit}" ), ARRAY_A);

                    
                    


                    foreach($arm_general_debug_log_data as $arm_general_debug_log_key => $arm_general_debug_log_val)
                    {
                        $arm_general_debug_log_html .= '<tr>';

                            $arm_general_debug_log_html .= '<td>'.$arm_general_debug_log_val['arm_general_log_id'].'</td>';
                            $arm_general_debug_log_html .= '<td>'.$arm_general_debug_log_val['arm_general_log_event_name'].'</td>';
                            $arm_general_debug_log_html .= '<td class="arm_debug_log_raw_data">'.htmlspecialchars(utf8_encode($arm_general_debug_log_val['arm_general_log_raw_data'])).'</td>';

                            $arm_created_date = date_i18n($date_time_format, strtotime($arm_general_debug_log_val['arm_general_log_added_date']));
                            $arm_general_debug_log_html .= '<td>'.$arm_created_date.'</td>';

                        $arm_general_debug_log_html .= '</tr>';
                    }

                }
                else 
                {
                    $total_column = 4;
                    $arm_general_debug_log_html .= '<tr>';
                        $arm_general_debug_log_html .= '<td colspan="'.$total_column.'" style="text-align: center;">' . __('No debug logs found.', 'ARMember') . '</td>';
                    $arm_general_debug_log_html .= '</tr>';
                }

                $arm_general_debug_log_html .= '</table>';
                $arm_general_debug_log_html .= '</div>';

                $arm_general_debug_log_html .= '<div class="arm_membership_history_pagination_block">';
                    $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage);
                    $arm_general_debug_log_html .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
                $arm_general_debug_log_html .= '</div>';

            }
            echo $arm_general_debug_log_html;
            exit();
        }
        
        function arm_need_to_cancel_old_subscription_gateways(){
            return apply_filters('arm_need_to_cancel_old_subscription_gateways', array('2checkout'));
        }

        function arm_get_all_payment_gateways() {
            global $wpdb, $ARMember;
            $pay_get_settings_user = get_option('arm_payment_gateway_settings', array());
            $pay_get_settings = maybe_unserialize($pay_get_settings_user);
            /* General Settings */
            $default_payment_gateway = array(
                'paypal' => array('gateway_name' => $this->arm_gateway_name_by_key('paypal')),
                'stripe' => array('gateway_name' => $this->arm_gateway_name_by_key('stripe')),
                'authorize_net' => array('gateway_name' => $this->arm_gateway_name_by_key('authorize_net')),
                '2checkout' => array('gateway_name' => $this->arm_gateway_name_by_key('2checkout')),
                'bank_transfer' => array('gateway_name' => $this->arm_gateway_name_by_key('bank_transfer')),
            );
            $payment_gateways = apply_filters('arm_get_payment_gateways_in_filters', $default_payment_gateway);
            foreach ($payment_gateways as $pgKey => $pgVal) {
                if (isset($pay_get_settings[$pgKey])) {
                    $payment_gateways[$pgKey] = array_merge($pgVal, $pay_get_settings[$pgKey]);
                }
            }
            return $payment_gateways;
        }

        function arm_get_all_payment_gateways_for_setup() {
            global $wpdb, $ARMember;
            $pay_get_settings_unser = get_option('arm_payment_gateway_settings', array());
            $pay_get_settings = maybe_unserialize($pay_get_settings_unser);
            /* General Settings */
            $default_payment_gateway = array(
                'paypal' => array('gateway_name' => $this->arm_gateway_name_by_key('paypal')),
                'stripe' => array('gateway_name' => $this->arm_gateway_name_by_key('stripe')),
                'authorize_net' => array('gateway_name' => $this->arm_gateway_name_by_key('authorize_net')),
                '2checkout' => array('gateway_name' => $this->arm_gateway_name_by_key('2checkout')),
                'bank_transfer' => array('gateway_name' => $this->arm_gateway_name_by_key('bank_transfer')),
            );
            $payment_gateways = apply_filters('arm_get_payment_gateways', $default_payment_gateway);
            foreach ($payment_gateways as $pgKey => $pgVal) {
                if (isset($pay_get_settings[$pgKey])) {
                    $payment_gateways[$pgKey] = array_merge($pgVal, $pay_get_settings[$pgKey]);
                }
            }
            return $payment_gateways;
        }

        function arm_gateway_name_by_key($gateway_key = '') {
            $gatewayNames = array(
                'paypal' => __('Paypal', 'ARMember'),
                'stripe' => __('Stripe', 'ARMember'),
                'authorize_net' => __('Authorize.net', 'ARMember'),
                '2checkout' => __('2Checkout', 'ARMember'),
                'bank_transfer' => __('Bank Transfer', 'ARMember'),
                'manual' => __('Manual', 'ARMember'),
            );
            $gatewayNames = apply_filters('arm_filter_gateway_names', $gatewayNames);
            $pgName = (isset($gatewayNames[$gateway_key])) ? $gatewayNames[$gateway_key] : $gateway_key;
            return apply_filters('arm_gateway_name_by_key', $pgName, $gateway_key);
        }

        function arm_get_active_payment_gateways() {
            global $wpdb, $ARMember;
            $payment_gateways = array();
            $pay_get_settings_unser = $this->arm_get_all_payment_gateways();
            $pay_get_settings = maybe_unserialize($pay_get_settings_unser);
            if (!empty($pay_get_settings)) {
                foreach ($pay_get_settings as $key => $pg) {
                    if (isset($pg['status']) && $pg['status'] == 1) {
                        $payment_gateways[$key] = $pg;
                    }
                }
            }
            return $payment_gateways;
        }

        function arm_get_all_currencies() {
            global $wpdb, $ARMember, $arm_global_settings;
            $paypal_cur = $this->arm_paypal_currency_symbol();
            $stripe_cur = $this->arm_stripe_currency_symbol();
            $authorize_net_cur = $this->arm_authorize_net_currency_symbol();
            $twocheckout_cur = $this->arm_2checkout_currency_symbol();
            $bank_transfer_cur = $this->arm_bank_transfer_currency_symbol();
            $all_currencies = array_merge($paypal_cur, $stripe_cur, $authorize_net_cur, $twocheckout_cur, $bank_transfer_cur);
            /* Add Custom Currency */
            $global_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : $arm_global_settings->arm_get_all_global_settings(true);
            $custom_currency = isset($global_settings['custom_currency']) ? $global_settings['custom_currency'] : array();
            if (isset($custom_currency['status']) && $custom_currency['status'] == 1) {
                $all_currencies[strtoupper($custom_currency['shortname'])] = $custom_currency['symbol'];
            }
            return apply_filters('arm_add_currency_in_default_list', $all_currencies);
        }

        function arm_get_global_currency() {
            global $wpdb, $ARMember, $arm_global_settings;
            $global_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : $arm_global_settings->arm_get_all_global_settings(true);
            $global_currency = $global_settings['paymentcurrency'];
            $custom_currency = isset($global_settings['custom_currency']) ? $global_settings['custom_currency'] : array();
            if (isset($custom_currency['status']) && $custom_currency['status'] == 1) {
                $global_currency = $custom_currency['shortname'];
            }
            return $global_currency;
        }

        /**
         * Get Currency Symbol Position From Currency Code
         */
        function arm_currency_symbol_position($currency = '') {
            global $wpdb, $ARMember, $arm_global_settings;
            $symbol_position = array(
                'prefix' => array('USD', 'AUD', 'BRL', 'CAD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NZD', 'PHP', 'GBP', 'RUB', 'SGD', 'CHF', 'TWD', 'THB', 'TRY', 'INR', 'GHS', 'NGN', 'ZAR', 'HKD', 'BTC', 'BTN', 'CUC', 'CUP', 'GGP', 'IMP', 'JEP', 'KPW', 'RMB', 'SDG', 'SSP', 'VEF', 'VES'),

                'suffix' => array('CZK', 'SEK', 'DKK', 'NOK', 'PLN', 'BHD', 'BYR', 'BYN', 'ERN', 'IQD', 'IRR', 'IRT', 'JOD', 'KWD', 'LYD', 'MRU', 'OMR', 'PRB', 'STN', 'TMT', 'TND'),
            );

            $current_symbol_pos = 'suffix';
            //$global_settings = $arm_global_settings->arm_get_all_global_settings(true);
            $global_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : $arm_global_settings->arm_get_all_global_settings(true);
            
            $custom_currency = isset($global_settings['custom_currency']) ? $global_settings['custom_currency'] : array();
            if (isset($custom_currency['status']) && $custom_currency['status'] == 1 && $custom_currency['place']) {
                $current_symbol_pos = $custom_currency['place'];
            }
            else if (in_array(strtoupper($currency), $symbol_position['prefix'])) {
                $current_symbol_pos = 'prefix';
            } elseif (in_array(strtoupper($currency), $symbol_position['suffix'])) {
                $current_symbol_pos = 'suffix';
            } elseif ($currency == 'EUR'){
                $current_symbol_pos = isset($global_settings['arm_specific_currency_position']) ? $global_settings['arm_specific_currency_position'] : 'suffix';
            }
            return $current_symbol_pos;
        }

        /**
         * Get Currency Symbol Position With Amount From Currency Code And Amount
         */
        function arm_prepare_amount($currency = '', $amount = 0) {
            $new_amount = $amount;
            if (!empty($currency) && !empty($amount) && $amount > 0) {
                $all_currencies = $this->arm_get_all_currencies();
                $symbol = isset($all_currencies[strtoupper($currency)]) ? $all_currencies[strtoupper($currency)] : " ";
                if ($this->arm_currency_symbol_position($currency) == 'prefix') {
                    $new_amount = $symbol . '' . $this->arm_amount_set_separator($currency, $amount);
                } else {
                    $new_amount = $this->arm_amount_set_separator($currency, $amount) . '' . $symbol;
                }
            }
            else if(!empty($currency)){
                $all_currencies = $this->arm_get_all_currencies();
                $symbol = isset($all_currencies[strtoupper($currency)]) ? $all_currencies[strtoupper($currency)] : " ";
                if ($this->arm_currency_symbol_position($currency) == 'prefix') {
                    $new_amount = $symbol . '' . $this->arm_amount_set_separator($currency, $amount);
                } else {
                    $new_amount = $this->arm_amount_set_separator($currency, $amount) . '' . $symbol;
                }
            }
            return $new_amount;
        }

        function arm_update_pay_gate_settings() {
            global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_email_settings, $arm_global_settings, $arm_capabilities_global, $arm_subscription_plans, $arm_stripe;

            $old_pay_gate_settings = get_option('arm_payment_gateway_settings');

            $pay_gate_settings = array();
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_payment_gateways'], '1');
            if (is_array($_POST['payment_gateway_settings']) && !empty($_POST['payment_gateway_settings'])) {
                foreach ($_POST['payment_gateway_settings'] as $key => $pg_setting) {
                    $pay_gate_settings[$key] = isset($_POST['payment_gateway_settings'][$key]) ? $pg_setting : "";
                    $pay_gate_settings[$key]['payment_debug_logs'] = !empty($old_pay_gate_settings[$key]['payment_debug_logs']) ? $old_pay_gate_settings[$key]['payment_debug_logs'] : 0;
                }
            }
            $pay_gate_settings = apply_filters('arm_save_payment_gateway_settings', $pay_gate_settings, $_POST);
            $pay_gate_settings = arm_array_map($pay_gate_settings);
            update_option('arm_payment_gateway_settings', $pay_gate_settings);
            $this->arm_update_payment_gate_status();

            if( (!empty($old_pay_gate_settings['stripe']) && is_array($old_pay_gate_settings['stripe']) ) && ( ($old_pay_gate_settings['stripe']['status'] != $_POST['payment_gateway_settings']['stripe']['status'] && $_POST['payment_gateway_settings']['stripe']['status']==1 ) 
                || ($old_pay_gate_settings['stripe']['stripe_payment_mode'] != $_POST['payment_gateway_settings']['stripe']['stripe_payment_mode']) 
                || ($old_pay_gate_settings['stripe']['stripe_test_secret_key'] != $_POST['payment_gateway_settings']['stripe']['stripe_test_secret_key']) 
                || ($old_pay_gate_settings['stripe']['stripe_test_pub_key'] != $_POST['payment_gateway_settings']['stripe']['stripe_test_pub_key']) 
                || ($old_pay_gate_settings['stripe']['stripe_secret_key'] != $_POST['payment_gateway_settings']['stripe']['stripe_secret_key']) 
                || ($old_pay_gate_settings['stripe']['stripe_pub_key'] != $_POST['payment_gateway_settings']['stripe']['stripe_pub_key'])
                )
              )
            {
                $arm_all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id');
                foreach($arm_all_plans as $arm_plan_key => $arm_plan_val)
                {
                    $arm_plan_id = $arm_plan_val['arm_subscription_plan_id'];
                    $arm_plan_recurring = new ARM_Plan($arm_plan_id);
                    if($arm_plan_recurring->is_recurring())
                    {
                        $arm_stripe_plan_keys = array();
                        $plan_options = $arm_plan_recurring->options;
                        $arm_plan_keys = $plan_options['payment_cycles'];
                        foreach($arm_plan_keys as $arm_keys => $arm_values)
                        {
                            array_push($arm_stripe_plan_keys, $arm_values['cycle_key']);
                        }


                        //Table name variable
                        $arm_membership_setup_tbl = $ARMember->tbl_arm_membership_setup;

                        //Fetch New Stripe Plan Array from Plan Id
                        $arm_fetch_new_stripe_plan_arr = $arm_stripe->arm_stripe_get_stripe_plan($arm_plan_id);

                        $arm_plan_setup_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$arm_membership_setup_tbl} WHERE arm_status = %d", 1));

                        foreach($arm_plan_setup_data as $arm_setup_data_key => $arm_setup_data_val)
                        {
                            $arm_setup_modules_data = maybe_unserialize($arm_setup_data_val->arm_setup_modules);
                            if((!empty($arm_setup_modules_data['modules']['plans']) && !empty($arm_setup_modules_data['modules']['gateways']) && !empty($arm_setup_modules_data['modules']['payment_mode']['stripe'])) && (in_array($arm_plan_id, $arm_setup_modules_data['modules']['plans']) && in_array('stripe', $arm_setup_modules_data['modules']['gateways']) && $arm_setup_modules_data['modules']['payment_mode']['stripe'] != "manual_subscription"))
                            {
                                if(!empty($arm_setup_modules_data['modules']['stripe_plans'][$arm_plan_id]))
                                {
                                    $arm_new_setup_data = array();
                                    for($arm_new_plans = 0;$arm_new_plans < count($arm_plan_keys);$arm_new_plans++)
                                    {
                                        //Modify plan cycle data with stripe plan id.
                                        $arm_new_setup_data[$arm_stripe_plan_keys[$arm_new_plans]] = $arm_fetch_new_stripe_plan_arr[$arm_new_plans];
                                    }

                                    $arm_setup_modules_data['modules']['stripe_plans'][$arm_plan_id] = $arm_new_setup_data;

                                    $arm_setup_new_modules_data = maybe_serialize($arm_setup_modules_data);
                                    $arm_setup_modules_data_update = $wpdb->query($wpdb->prepare("UPDATE {$arm_membership_setup_tbl} SET `arm_setup_modules` = '{$arm_setup_new_modules_data}' WHERE arm_setup_id = %d", $arm_setup_data_val->arm_setup_id));
                                }
                            }
                        }
                    }
                }
            }

            $response = array('message' => 'success');
            echo json_encode($response);
            die();
        }

        function arm_update_payment_gate_status() {
            global $wpdb, $ARMember, $arm_global_settings;
            $global_currency = $this->arm_get_global_currency();
            $not_allow_payment = $this->arm_check_currency_status_for_gateways($global_currency);
            if (!empty($not_allow_payment)) {
                $pg_settings = get_option('arm_payment_gateway_settings', array());
                $new_pg_settings = maybe_unserialize($pg_settings);
                foreach ($not_allow_payment as $payment) {
                    if (isset($new_pg_settings[$payment])) {
                        $new_pg_settings[$payment]['status'] = 0;
                    }
                }
                update_option('arm_payment_gateway_settings', $new_pg_settings);
            }
            return;
        }

        function arm_filter_form_posted_plan_data($posted_data, $user_ID) {

           
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $payment_done, $paid_trial_stripe_payment_done, $arm_transaction, $arm_members_class, $arm_membership_setup,$arm_manage_coupons;


            
            if (!empty($posted_data) && is_array($posted_data) && (!empty($posted_data['subscription_plan']) || !empty($posted_data['_subscription_plan']) || !empty($posted_data['arm_user_plan']))) {
                /* Set User Plan Values */

                if (isset($posted_data['arm_update_user_from_profile']) && $posted_data['arm_update_user_from_profile'] == 0) {
                    $arm_update_user_from_profile = 0;
                } else {
                    $arm_update_user_from_profile = 1;
                }
            
                if (!empty($posted_data['arm_user_plan'])) {
                    $subscription_plan = $posted_data['arm_user_plan'];
                } else {
                    $subscription_plan = (!empty($posted_data['subscription_plan'])) ? intval($posted_data['subscription_plan']) : 0;
                    if ($subscription_plan == 0) {
                        $subscription_plan = (!empty($posted_data['_subscription_plan'])) ? intval($posted_data['_subscription_plan']) : 0;
                    }
                }
                
                
                $setup_id = isset($posted_data['setup_id']) ? intval($posted_data['setup_id']) : 0;

                $entry_id = isset($posted_data['arm_entry_id']) ? $posted_data['arm_entry_id'] : 0;
                $entry_values = array();
                if(!empty($entry_id))
                {
                    $entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $entry_id . "' ", ARRAY_A);
                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                }
                
                $pgateway = isset($posted_data['payment_gateway']) ? sanitize_text_field($posted_data['payment_gateway']) : '';
                if ($pgateway === '') {
                    $pgateway = isset($posted_data['_payment_gateway']) ? sanitize_text_field($posted_data['_payment_gateway']) : '';
                }
                
                $gateway = (!empty($pgateway)) ? $pgateway : 'manual';
                
                $action = isset($posted_data['action']) ? sanitize_text_field($posted_data['action']) : '';
            
                
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $all_active_pgs = $this->arm_get_active_payment_gateways();
                foreach ($all_active_pgs as $k => $data) {
                    if (isset($posted_data[$k])) {
                        unset($posted_data[$k]);
                    }
                }
                $posted_data['arm_user_plan'] = $subscription_plan;
                if(!empty($subscription_plan)){
                    if(is_array($subscription_plan)){
                        foreach($subscription_plan as $pid){
                            if(!empty($pid)){ 
                                $userPlanDatameta = get_user_meta($user_ID, 'arm_user_plan_'.$pid, true);
                                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                                $update = false;
                                $plan = new ARM_Plan($pid);

                                if ($plan->is_free()) {
                                    $pgateway = '';
                                }

                                $posted_data['pgateway'] = $pgateway;
                                
                                $payment_mode = '';
                                $payment_cycle = '';

                                if ($plan->is_recurring()) { 

                                        $payment_mode_ = !empty($posted_data['arm_selected_payment_mode']) ? sanitize_text_field($posted_data['arm_selected_payment_mode']) : 'manual_subscription';
                                        if(isset($posted_data['arm_payment_mode'][$gateway])){
                                            $payment_mode_ = !empty($posted_data['arm_payment_mode'][$gateway]) ? sanitize_text_field($posted_data['arm_payment_mode'][$gateway]) : 'manual_subscription';
                                        }
                                        else{
                                            $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
                                            if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
                                                $setup_modules = $setup_data['setup_modules'];
                                                $modules = $setup_modules['modules'];
                                                $payment_mode_ = $modules['payment_mode'][$gateway];
                                            }
                                        }



                                        $payment_mode = 'manual_subscription';
                                        if ($payment_mode_ == 'both') {
                                            $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? sanitize_text_field($posted_data['arm_selected_payment_mode']) : 'manual_subscription';
                                        } else {
                                            $payment_mode = $payment_mode_;
                                        }
                                    
                                    $payment_cycle = isset($posted_data['arm_selected_payment_cycle']) ? $posted_data['arm_selected_payment_cycle'] : 0;
                                }

                            /**  1) At import time if user with old date imported ( `arm_subscription_start_date`  set in csv ) than set that start date else assign current date . At import time don't allow trial period.
                             * 2) if user add from admin or updated from admin than don't allow trial period.
                             * */
                                if (isset($posted_data['arm_subscription_start_date']) && $posted_data['arm_subscription_start_date'] != '' || (isset($posted_data['arm_user_import']) && $posted_data['arm_user_import'] == true || $posted_data['action'] == 'add_member' || $posted_data['action'] == 'update_member')) {

                                    if (isset($posted_data['arm_subscription_start_date']) && $posted_data['arm_subscription_start_date'] != '') {
                                        $nowMysql = strtotime($posted_data['arm_subscription_start_date']);
                                    } else {
                                        if(isset($posted_data['arm_subscription_start_'.$pid]) && !empty($posted_data['arm_subscription_start_'.$pid])){
                                            $nowMysql = strtotime($posted_data['arm_subscription_start_'.$pid]);
                                            unset($posted_data['arm_subscription_start_'.$pid]);
                                        }
                                        else{
                                            $nowMysql = strtotime(current_time('mysql'));
                                        }

                                        if ($plan->is_recurring() && isset($payment_cycle) && !empty($payment_cycle) && is_array($payment_cycle)) {
                                            if(isset($payment_cycle['arm_plan_cycle_'.$pid])){
                                                $payment_cycle = $payment_cycle['arm_plan_cycle_'.$pid];
                                            }
                                        }
                                    }
                                    $start_time = $nowMysql;
                                    $posted_data['start_time'] = $start_time;
                                } else {
                                    $nowMysql = strtotime(current_time('mysql'));
                                    $posted_data['start_time'] = $nowMysql;
                                     if ($pgateway != 'bank_transfer') {
                                        $trial_and_sub_start_date = $plan->arm_trial_and_plan_start_date($nowMysql, $payment_mode, true, $payment_cycle);
                                        $start_time = isset($trial_and_sub_start_date['subscription_start_date']) ? $trial_and_sub_start_date['subscription_start_date'] : '';

                                        if (isset($trial_and_sub_start_date['arm_trial_start_date']) && $trial_and_sub_start_date['arm_trial_start_date'] != '') {

                                            $userPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
                                            if (isset($trial_and_sub_start_date['arm_expire_plan_trial']) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '') {
                                                $userPlanData['arm_is_trial_plan'] = '1';
                                                $userPlanData['arm_trial_end'] =  $trial_and_sub_start_date['arm_expire_plan_trial'];
                                                $arm_is_trial = true;
                                            }
                                        }
                                    }
                                }
                                
                                $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array(); 
                                $expire_time = false;
                                if ($pgateway == 'bank_transfer') {
                                    if ($plan->is_recurring()) {
                                        $payment_mode = 'manual_subscription';
                                        $userPlanData['arm_payment_mode'] = $payment_mode;
                                        $userPlanData['arm_payment_cycle'] = $payment_cycle;
                                        $update = false;
                                    }
                                    else{
                                        $userPlanData['arm_payment_mode'] = '';
                                        $userPlanData['arm_payment_cycle'] = '';
                                    }
                                } else {
                                    $plan_options = $plan->options;
                                    $expire_time = $plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);

                                    if(in_array($plan->ID, $old_plan_ids)){
                                            $expired = $userPlanData['arm_expire_plan'];
                                            if ($arm_update_user_from_profile == 0) {
                                                $expire_time = $plan->arm_plan_expire_time_for_renew_action($start_time);
                                                
                                                if ($plan->is_recurring()){ 
                                                    $completed_rec = $userPlanData['arm_completed_recurring'];
                                                    $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                                    $userPlanData['arm_completed_recurring'] = $completed_rec + 1;
                                                    $userPlanData['arm_payment_mode'] = $payment_mode;
                                                    $userPlanData['arm_payment_cycle'] = $payment_cycle;

                                                }
                                            } else {
                                                if ($plan->is_recurring()/* && $old_payment_mode == 'manual_subscription' */) {
                                                    $completed_rec = $userPlanData['arm_completed_recurring'];
                                                    if ($completed_rec === '') {
                                                        $userPlanData['arm_completed_recurring'] = 1;
                                                    }
                                                }
                                            }
                                            if (!empty($expired) && $start_time > $expired) {
                                                $update = true;
                                            }
                                        }
                                     else {

                                        if ($plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/) {
                                            if (!$plan->has_trial_period()) {
                                                $userPlanData['arm_completed_recurring'] = 1;
                                            }
                                            else
                                            {
                                                
                                                    if ($arm_update_user_from_profile == 0){
                                                        $userPlanData['arm_completed_recurring'] = 0;
                                                    }
                                                    else{
                                                        $userPlanData['arm_completed_recurring'] = 1;
                                                    }
                                                
                                            }

                                            $userPlanData['arm_payment_mode'] = $payment_mode;
                                            $userPlanData['arm_payment_cycle'] = $payment_cycle;
                                        }
                                        $update = true;
                                    }

                                }
                                
                                if ($update) {
                                    $posted_data['roles'] = (isset($posted_data['roles']) && !empty($posted_data['roles'])) ? $posted_data['roles'] : array();
                                    if (!empty($plan->plan_role)) {
                                        array_push($posted_data['roles'], $plan->plan_role);
                                    }

                                    $userPlanData['arm_start_plan'] = $start_time;
                                    if ($expire_time != false) {
                                        $userPlanData['arm_expire_plan'] = $expire_time;
                                    }
                                    $userPlanData['arm_user_gateway'] = (!empty($pgateway)) ? $pgateway : 'manual';
                                    /* Set Current Plan Detail */
                                    $curPlanDetail = (array) $plan->plan_detail;
                                    $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                                    $arm_meta_plan_options = maybe_unserialize($curPlanDetail['arm_subscription_plan_options']);

                                    $arm_subscription_plan_amount = !empty($arm_meta_plan_options['payment_cycles'][$payment_cycle]) ? $arm_meta_plan_options['payment_cycles'][$payment_cycle]['cycle_amount'] : $curPlanDetail['arm_subscription_plan_amount'];
                                    $arm_modify_subscription_plan_amount = apply_filters('arm_modify_webhook_discount_amount', $arm_subscription_plan_amount, $plan->ID, $entry_id, $entry_values, $pgateway);
                                    $curPlanDetail['arm_subscription_plan_amount'] = $arm_modify_subscription_plan_amount;
                                    $arm_meta_plan_options['payment_cycles'][$payment_cycle]['cycle_amount'] = $arm_modify_subscription_plan_amount;
                                    $curPlanDetail['arm_subscription_plan_options'] =  maybe_serialize($arm_meta_plan_options);

                                    $userPlanData['arm_current_plan_detail'] = $curPlanDetail;
                                }
                                update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                                if(!in_array($plan->ID, $old_plan_ids)){
                                    if($plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ )
                                    {
                                        $allow_trial = true;

                                        if($action=='add_member'|| $action=='update_member')
                                        {
                                            $allow_trial = false;
                                        }
                                        $arm_next_payment_due_date = $arm_members_class->arm_get_next_due_date($user_ID, $plan->ID, $allow_trial, $payment_cycle, $start_time);  
                                        $userPlanData['arm_next_due_payment'] = $arm_next_payment_due_date;
                                        update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                                    }
                                }

                                /* Unset unsued details. */
                                if (!empty($payment_done) && $payment_done['status'] === TRUE) {
                                    $log_id = (!empty($payment_done['log_id'])) ? $payment_done['log_id'] : 0;
                                    $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';
                                    
                                    $armLogTable = $ARMember->tbl_arm_payment_log;
                                    $selectColumns .= ', `arm_token`, `arm_extra_vars`';
                                    
                                    $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`='{$log_id}'");
                                    if (!empty($log_detail)) {
                                        $arm_first_name=(isset($posted_data['first_name']))?$posted_data['first_name']:'';
                                        $arm_last_name=(isset($posted_data['last_name']))?$posted_data['last_name']:'';
                                        if($user_ID){
                                            $user_detail = get_userdata($user_ID);
                                            if(empty($arm_first_name)){
                                                $arm_first_name=$user_detail->first_name;
                                            }
                                            if(empty($arm_last_name)){
                                                $arm_last_name=$user_detail->last_name;
                                            }    
                                        }
                                        $upData = array('arm_user_id' => $user_ID);
                                        $upData['arm_first_name']=$arm_first_name;
                                        $upData['arm_last_name']=$arm_last_name;
                                        if ($pgateway != 'bank_transfer') {
                                            $extra_vars = maybe_unserialize($log_detail->arm_extra_vars);
                                            if (isset($extra_vars['card_number']) && !empty($extra_vars['card_number'])) {
                                                $extra_vars['card_number'] = $extra_vars['card_number'];
                                            } else {
                                                $extra_vars['card_number'] = isset($posted_data[$pgateway]['card_number']) ? $posted_data[$pg]['card_number'] : '-';
                                            }
                                            $upData['arm_extra_vars'] = maybe_serialize($extra_vars);
                                        }
                                        $wpdb->update($armLogTable, $upData, array('arm_log_id' => $log_id));
                                        if ($pgateway == 'stripe') {
                                            $userPlanData['arm_stripe'] = array('customer_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                                        }
                                        if ($pgateway == 'authorize_net') {
                                            $userPlanData['arm_authorize_net'] = array('subscription_id' => $log_detail->arm_token);
                                        }
                                        if ($pgateway == '2checkout') {
                                            $userPlanData['arm_2checkout'] = array('sale_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                                        }
                                        $userPlanData = apply_filters('arm_membership_update_user_meta_from_outside', $userPlanData, $user_ID, $plan, $log_detail, $pgateway);
                                    }
                                    $posted_data['arm_entry_id'] = (!empty($payment_done['entry_id'])) ? $payment_done['entry_id'] : 0;

                                    update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                                    $arm_manage_coupons->arm_coupon_apply_to_subscription($user_ID, $log_detail, $pgateway, $userPlanData);
                                }

                                if (!empty($paid_trial_stripe_payment_done) && $paid_trial_stripe_payment_done['status'] === TRUE && $paid_trial_stripe_payment_done['gateway'] == $pgateway) {
                                    $log_id = (!empty($paid_trial_stripe_payment_done['log_id'])) ? $paid_trial_stripe_payment_done['log_id'] : 0;
                                    $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';
                                    if ($pgateway == 'stripe') {
                                        $armLogTable = $ARMember->tbl_arm_payment_log;
                                        $selectColumns .= ', `arm_token`, `arm_extra_vars`';
                                    }
                                    $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`='{$log_id}'");
                                    if (!empty($log_detail)) {
                                        $arm_first_name=(isset($posted_data['first_name']))?$posted_data['first_name']:'';
                                        $arm_last_name=(isset($posted_data['last_name']))?$posted_data['last_name']:'';
                                        if($user_ID){
                                            $user_detail = get_userdata($user_ID);
                                            if(empty($arm_first_name)){
                                                $arm_first_name=$user_detail->first_name;
                                            }
                                            if(empty($arm_last_name)){
                                                $arm_last_name=$user_detail->last_name;
                                            }    
                                        }
                                        $upData = array('arm_user_id' => $user_ID);
                                        $upData['arm_first_name']=$arm_first_name;
                                        $upData['arm_last_name']=$arm_last_name;
                                        if ($pgateway == 'stripe') {
                                            $extra_vars = maybe_unserialize($log_detail->arm_extra_vars);
                                            if (isset($extra_vars['card_number']) && !empty($extra_vars['card_number'])) {
                                                $extra_vars['card_number'] = $extra_vars['card_number'];
                                            } else {
                                                $extra_vars['card_number'] = isset($posted_data[$pgateway]['card_number']) ? $posted_data[$pg]['card_number'] : '-';
                                            }
                                            $upData['arm_extra_vars'] = maybe_serialize($extra_vars);
                                        }
                                        $wpdb->update($armLogTable, $upData, array('arm_log_id' => $log_id));
                                    }
                                    update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                                }
                            }
                        }
                    }
                    else{
                        $userPlanDatameta = get_user_meta($user_ID, 'arm_user_plan_'.$subscription_plan, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                        $update = false;

                        $plan = new ARM_Plan($subscription_plan);

                        if ($plan->is_free()) {
                            $pgateway = '';
                        }
                        
                        $posted_data['pgateway'] = $pgateway;

                        $payment_mode = '';
                        $payment_cycle = '';
                         
                        if($plan->is_recurring()){
                            $payment_mode_ = isset($posted_data['arm_payment_mode'][$gateway]) ? sanitize_text_field($posted_data['arm_payment_mode'][$gateway]) : 'both';
                            $payment_mode = 'manual_subscription';
                            if ($payment_mode_ == 'both') {
                                $payment_mode = !empty($posted_data['arm_selected_payment_mode']) ? sanitize_text_field($posted_data['arm_selected_payment_mode']) : 'manual_subscription';
                            } else {
                                $payment_mode = $payment_mode_;
                            }
                            $payment_cycle = isset($posted_data['arm_selected_payment_cycle']) ? $posted_data['arm_selected_payment_cycle'] : 0;
                        }
                        $start_time = strtotime(current_time('mysql'));

                        /*                 *  1) At import time if user with old date imported ( `arm_subscription_start_date`  set in csv ) than set that start date else assign current date . At import time don't allow trial period.
                         * 2) if user add from admin or updated from admin than don't allow trial period.
                         * */
                        if (isset($posted_data['arm_subscription_start_date']) && $posted_data['arm_subscription_start_date'] != '' || (isset($posted_data['arm_user_import']) && $posted_data['arm_user_import'] == true || $action == 'add_member' || $action == 'update_member')) {

                            if (isset($posted_data['arm_subscription_start_date']) && $posted_data['arm_subscription_start_date'] != '') {
                                $nowMysql = strtotime($posted_data['arm_subscription_start_date']);
                              
                            } else {
                                $nowMysql = strtotime(current_time('mysql'));
                            }
                            $start_time = $nowMysql;
                            $posted_data['start_time'] = $nowMysql;
                        } else {
                            $nowMysql = strtotime(current_time('mysql'));
                            $posted_data['start_time'] = $nowMysql;
                             if ($pgateway != 'bank_transfer') {
                                $trial_and_sub_start_date = $plan->arm_trial_and_plan_start_date($nowMysql, $payment_mode, true, $payment_cycle);
                                $start_time = isset($trial_and_sub_start_date['subscription_start_date']) ? $trial_and_sub_start_date['subscription_start_date'] : '';

                                if (isset($trial_and_sub_start_date['arm_trial_start_date']) && $trial_and_sub_start_date['arm_trial_start_date'] != '') {

                                    $userPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
                                    if (isset($trial_and_sub_start_date['arm_expire_plan_trial']) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '') {

                                        $userPlanData['arm_is_trial_plan'] = '1';

                                        $userPlanData['arm_trial_end'] =  $trial_and_sub_start_date['arm_expire_plan_trial'];
                                        $arm_is_trial = true;
                                    }
                                }
                             }

                        }

                        
                        $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                        $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array(); 
                        
                      
                        $expire_time = false;
                        if ($pgateway == 'bank_transfer') {

                            
                            if ($plan->is_recurring()) {
                                $payment_mode = 'manual_subscription';
                                $update = false;
                                $userPlanData['arm_payment_mode'] = $payment_mode;
                                $userPlanData['arm_payment_cycle'] = $payment_cycle;
                            }
                            else{
                                $userPlanData['arm_payment_mode'] = '';
                                $userPlanData['arm_payment_cycle'] = '';
                            }

                        } else {
                            $plan_options = $plan->options;
                            $expire_time = $plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);

                            if(in_array($plan->ID, $old_plan_ids)){

                                    $expired = $userPlanData['arm_expire_plan'];
                                    if ($arm_update_user_from_profile == 0) {
                                        $expire_time = $plan->arm_plan_expire_time_for_renew_action($start_time);
                                        $completed_rec = $userPlanData['arm_completed_recurring'];
                                        $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                        if ($plan->is_recurring()) {
                                            $userPlanData['arm_completed_recurring'] = $completed_rec + 1;
                                            $userPlanData['arm_payment_mode'] = $payment_mode;
                                            $userPlanData['arm_payment_cycle'] = $payment_cycle;
                                        }
                                    } else {
                                        if ($plan->is_recurring() /*&& $old_payment_mode == 'manual_subscription'*/) {
                                            $completed_rec = $userPlanData['arm_completed_recurring'];
                                            if ($completed_rec === '') {
                                                $userPlanData['arm_completed_recurring'] = 1;
                                            }
                                        }
                                    }
                                    if (!empty($expired) && $start_time > $expired) {
                                        $update = true;
                                    }
                                }
                             else {
                                if ($plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/) {
                                    if (!$plan->has_trial_period()) {
                                        $userPlanData['arm_completed_recurring'] = 1;
                                    }
                                    else
                                    {
                                        if ($arm_update_user_from_profile == 0){
                                            $userPlanData['arm_completed_recurring'] = 0;
                                        }
                                        else{
                                            $userPlanData['arm_completed_recurring'] = 1;
                                        }
                                    }
                                    $userPlanData['arm_payment_mode'] = $payment_mode;
                                    $userPlanData['arm_payment_cycle'] = $payment_cycle;
                                }
                                $update = true;
                            }

                    }
                    if ($update) {


                        /* Assign Membership Plan Role To User if member form has no role field */
                        if (empty($posted_data['roles']) && !empty($plan->plan_role)) {
                            $posted_data['roles'] = $plan->plan_role;
                        }

                        $posted_data['arm_user_plan'] = $subscription_plan;
                        $userPlanData['arm_start_plan'] = $start_time;
                        if ($expire_time != false) {
                            $userPlanData['arm_expire_plan'] = $expire_time;
                        }
                        $userPlanData['arm_user_gateway'] = (!empty($pgateway)) ? $pgateway : 'manual';
                        /* Set Current Plan Detail */

                        $curPlanDetail = (array) $plan->plan_detail;
                        $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                        $arm_meta_plan_options = maybe_unserialize($curPlanDetail['arm_subscription_plan_options']);

                        $arm_subscription_plan_amount = !empty($arm_meta_plan_options['payment_cycles'][$payment_cycle]) ? $arm_meta_plan_options['payment_cycles'][$payment_cycle]['cycle_amount'] : $curPlanDetail['arm_subscription_plan_amount'];

                        $arm_modify_subscription_plan_amount = apply_filters('arm_modify_webhook_discount_amount', $arm_subscription_plan_amount, $plan->ID, $entry_id, $entry_values, $pgateway);
                        $curPlanDetail['arm_subscription_plan_amount'] = $arm_modify_subscription_plan_amount;
                        $arm_meta_plan_options['payment_cycles'][$payment_cycle]['cycle_amount'] = $arm_modify_subscription_plan_amount;
                        $curPlanDetail['arm_subscription_plan_options'] =  maybe_serialize($arm_meta_plan_options);

                        $userPlanData['arm_current_plan_detail'] = $curPlanDetail;

                    }
                    update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                    if(!in_array($plan->ID, $old_plan_ids)){
                        if($plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ )
                        {
                            $allow_trial = true;

                            if($action=='add_member'|| $action=='update_member')
                            {
                                $allow_trial = false;
                            }


                            
                            $arm_next_payment_due_date = $arm_members_class->arm_get_next_due_date($user_ID, $plan->ID, $allow_trial, $payment_cycle, $start_time);  
                            $userPlanData['arm_next_due_payment'] = $arm_next_payment_due_date;


                            update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                        }
                    }
                    
                    /* Unset unsued details. */
                    if (!empty($payment_done) && $payment_done['status'] === TRUE) {
                        $log_id = (!empty($payment_done['log_id'])) ? $payment_done['log_id'] : 0;
                        $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';
                        
                        $armLogTable = $ARMember->tbl_arm_payment_log;
                        $selectColumns .= ', `arm_token`, `arm_extra_vars`';
                        
                        $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`='{$log_id}'");
                        if (!empty($log_detail)) {
                            $arm_first_name=(isset($posted_data['first_name']))?$posted_data['first_name']:'';
                            $arm_last_name=(isset($posted_data['last_name']))?$posted_data['last_name']:'';
                            if($user_ID){
                                $user_detail = get_userdata($user_ID);
                                if(empty($arm_first_name)){
                                    $arm_first_name=$user_detail->first_name;
                                }
                                if(empty($arm_last_name)){
                                    $arm_last_name=$user_detail->last_name;
                                }    
                            }
                            $upData = array('arm_user_id' => $user_ID);
                            $upData['arm_first_name']=$arm_first_name;
                            $upData['arm_last_name']=$arm_last_name;
                            if ($pgateway != 'bank_transfer') {
                                $extra_vars = maybe_unserialize($log_detail->arm_extra_vars);
                                if (isset($extra_vars['card_number']) && !empty($extra_vars['card_number'])) {
                                    $extra_vars['card_number'] = $extra_vars['card_number'];
                                } else {
                                    $extra_vars['card_number'] = isset($posted_data[$pgateway]['card_number']) ? $posted_data[$pg]['card_number'] : '-';
                                }
                                $upData['arm_extra_vars'] = maybe_serialize($extra_vars);
                            }
                            $wpdb->update($armLogTable, $upData, array('arm_log_id' => $log_id));
                            if ($pgateway == 'stripe') {
                                $userPlanData['arm_stripe'] = array('customer_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                            }
                            if ($pgateway == 'authorize_net') {
                                $userPlanData['arm_authorize_net'] = array('subscription_id' => $log_detail->arm_token);
                            }
                            if ($pgateway == '2checkout') {
                                $userPlanData['arm_2checkout'] = array('sale_id' => $log_detail->arm_token, 'transaction_id' => $log_detail->arm_transaction_id);
                            }
                            $userPlanData = apply_filters('arm_membership_update_user_meta_from_outside', $userPlanData, $user_ID, $plan, $log_detail, $pgateway);
                            
                        }
                        $posted_data['arm_entry_id'] = (!empty($payment_done['entry_id'])) ? $payment_done['entry_id'] : 0;

                        update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                        $arm_manage_coupons->arm_coupon_apply_to_subscription($user_ID, $log_detail, $pgateway, $userPlanData);
                    }

                    if (!empty($paid_trial_stripe_payment_done) && $paid_trial_stripe_payment_done['status'] === TRUE && $paid_trial_stripe_payment_done['gateway'] == $pgateway) {
                    $log_id = (!empty($paid_trial_stripe_payment_done['log_id'])) ? $paid_trial_stripe_payment_done['log_id'] : 0;
                    $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';
                    if ($pgateway == 'stripe') {
                        $armLogTable = $ARMember->tbl_arm_payment_log;
                        $selectColumns .= ', `arm_token`, `arm_extra_vars`';
                    }
                    $log_detail = $wpdb->get_row("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`='{$log_id}'");
                    if (!empty($log_detail)) {
                        $arm_first_name=(isset($posted_data['first_name']))?$posted_data['first_name']:'';
                        $arm_last_name=(isset($posted_data['last_name']))?$posted_data['last_name']:'';
                        if($user_ID){
                            $user_detail = get_userdata($user_ID);
                            if(empty($arm_first_name)){
                                $arm_first_name=$user_detail->first_name;
                            }
                            if(empty($arm_last_name)){
                                $arm_last_name=$user_detail->last_name;
                            }    
                        }
                        $upData = array('arm_user_id' => $user_ID);
                        $upData['arm_first_name']=$arm_first_name;
                        $upData['arm_last_name']=$arm_first_name;
                        if ($pgateway == 'stripe') {
                            $extra_vars = maybe_unserialize($log_detail->arm_extra_vars);
                            if (isset($extra_vars['card_number']) && !empty($extra_vars['card_number'])) {
                                $extra_vars['card_number'] = $extra_vars['card_number'];
                            } else {
                                $extra_vars['card_number'] = isset($posted_data[$pgateway]['card_number']) ? $posted_data[$pg]['card_number'] : '-';
                            }
                            $upData['arm_extra_vars'] = maybe_serialize($extra_vars);
                        }
                        $wpdb->update($armLogTable, $upData, array('arm_log_id' => $log_id));
                    }
                    update_user_meta($user_ID, 'arm_user_plan_'.$plan->ID, $userPlanData);
                }
                }
                }
                unset($posted_data['payment_done']);
                unset($posted_data['subscription_plan']);
                unset($posted_data['_subscription_plan']);
                unset($posted_data['payment_gateway']);
                unset($posted_data['_payment_gateway']);
            }
            
            if (!empty($posted_data['arm_user_future_plan'])) {
                $subscription_future_plan = $posted_data['arm_user_future_plan'];
            } else {
                $subscription_future_plan = array();
            }

            if(!empty($posted_data['role']))
            {
                $meta_key = "role";
                $arm_form_id = isset($posted_data['arm_form_id']) ? intval($posted_data['arm_form_id']) : '';
                
                $user_form_id = !empty($arm_form_id) ? $arm_form_id : get_user_meta($user_ID, 'arm_form_id', true);

                $form = new ARM_Form('id', $user_form_id);

                if (!$form->exists() || $form->type != 'registration') {
                    $user_form_id = $default_form_id = $arm_member_forms->arm_get_default_form_id('registration');
                    $form = new ARM_Form('id', $default_form_id);
                }

                if ($form->exists() && !empty($form->fields)) 
                {
                    $arm_role_field_options = $arm_member_forms->arm_get_field_option_by_meta($meta_key, $user_form_id);
                    $arm_role_field_option = isset($arm_role_field_options['options']) ? $arm_role_field_options['options'] : '';
                    if(!empty($arm_role_field_option))
                    {
                        if (is_array($posted_data['role'])) {
                            $count = 0;
                            foreach ($posted_data['role'] as $posted_data_role) {
                                if(!array_key_exists($posted_data_role,$arm_role_field_option))
                                {
                                    if (($arm_role_key = array_search($posted_data_role, $posted_data['role'])) !== false) {
                                        unset($posted_data['role'][$arm_role_key]);
                                    }
                                }
                            }
                        }
                        else {
                            if(!array_key_exists($posted_data['role'],$arm_role_field_option))
                            {
                                //unset($posted_data['role']);
                                $posted_data['role'] = "";
                            }
                        }
                    }
                    else {
                        //unset($posted_data['role']);
                        if(is_array($posted_data['role']))
                        {
                            if(in_array('administrator', $posted_data['role']))
                            {
                                if (($arm_role_key = array_search('administrator', $posted_data['role'])) !== false) {
                                    unset($posted_data['role'][$arm_role_key]);
                                }
                            }
                        }
                        else {
                            if(!empty($posted_data['role']) && $posted_data['role']=='administrator')
                            {
                                $posted_data['role'] = "";
                            }
                        }
                    }
                }
                else {
                    //unset($posted_data['role']);
                    if(is_array($posted_data['role']))
                    {
                        if(in_array('administrator', $posted_data['role']))
                        {
                            if (($arm_role_key = array_search('administrator', $posted_data['role'])) !== false) {
                                unset($posted_data['role'][$arm_role_key]);
                            }
                        }
                    }
                    else {
                        if(!empty($posted_data['role']) && $posted_data['role']=='administrator')
                        {
                            $posted_data['role'] = "";
                        }
                    }
                }
            }

            if(!empty($posted_data['roles']))
            {
                $meta_key = "roles";
                $arm_form_id = isset($posted_data['arm_form_id']) ? $posted_data['arm_form_id'] : '';
                
                $user_form_id = !empty($arm_form_id) ? $arm_form_id : get_user_meta($user_ID, 'arm_form_id', true);

                $form = new ARM_Form('id', $user_form_id);

                if (!$form->exists() || $form->type != 'registration') {
                    $user_form_id = $default_form_id = $arm_member_forms->arm_get_default_form_id('registration');
                    $form = new ARM_Form('id', $default_form_id);
                }

                if ($form->exists() && !empty($form->fields)) 
                {
                    $arm_role_field_options = $arm_member_forms->arm_get_field_option_by_meta($meta_key, $user_form_id);
                    $arm_role_field_option = isset($arm_role_field_options['options']) ? $arm_role_field_options['options'] : '';
                    if(!empty($arm_role_field_option))
                    {
                        if (is_array($posted_data['roles'])) {
                            $count = 0;
                            foreach ($posted_data['roles'] as $posted_data_role) {
                                if(!array_key_exists($posted_data_role,$arm_role_field_option))
                                {
                                    if (($arm_role_key = array_search($posted_data_role, $posted_data['roles'])) !== false) {
                                        unset($posted_data['roles'][$arm_role_key]);
                                    }
                                }
                            }
                        }
                        else {
                            if(!array_key_exists($posted_data['roles'],$arm_role_field_option))
                            {
                                //unset($posted_data['roles']);
                                $posted_data['roles'] = "";
                            }
                        }
                    }
                    else {
                        //unset($posted_data['roles']);
                        //$posted_data['roles'] = "";
                        if(is_array($posted_data['roles']))
                        {
                            if(in_array('administrator', $posted_data['roles']))
                            {
                                if (($arm_role_key = array_search('administrator', $posted_data['roles'])) !== false) {
                                    unset($posted_data['roles'][$arm_role_key]);
                                }
                            }
                        }
                        else {
                            if(!empty($posted_data['roles']) && $posted_data['roles']=='administrator')
                            {
                                $posted_data['roles'] = "";
                            }
                        }
                    }
                }
                else {
                    //unset($posted_data['roles']);
                    if(is_array($posted_data['roles']))
                    {
                        if(in_array('administrator', $posted_data['roles']))
                        {
                            if (($arm_role_key = array_search('administrator', $posted_data['roles'])) !== false) {
                                unset($posted_data['roles'][$arm_role_key]);
                            }
                        }
                    }
                    else {
                        if(!empty($posted_data['roles']) && $posted_data['roles']=='administrator')
                        {
                            $posted_data['roles'] = "";
                        }
                    }
                }
            }
            
            $posted_data = array_merge(array('arm_user_future_plan' => $subscription_future_plan), $posted_data);
            
            return $posted_data;
        }

        function arm_save_payment_log($log_data = array()) {
            global $wp, $wpdb, $ARMember, $arm_subscription_plans, $arm_manage_coupons, $arm_transaction;
            $payment_log_id = $arm_transaction->arm_add_transaction($log_data);
            return $payment_log_id;
        }

        function arm_bank_transfer_payment_gateway_action($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0) {
            global $wpdb, $ARMember, $arm_global_settings, $payment_done, $arm_membership_setup, $arm_manage_coupons, $arm_subscription_plans, $arm_transaction, $arm_debug_payment_log_id;
            if ($payment_gateway == 'bank_transfer') {
                do_action('arm_payment_log_entry', 'bank_transfer', 'Bank Transfer Posted Data', 'armember', $posted_data, $arm_debug_payment_log_id);
                $entry_data = $this->arm_get_entry_data_by_id($entry_id);
                if (!empty($entry_data)) {
                    $posted_data = apply_filters('arm_handle_bank_transfer_before_payment_from_outside',$posted_data,$entry_data);
                    
                    $user_id = $entry_data['arm_user_id'];
                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                    do_action('arm_payment_log_entry', 'bank_transfer', 'Bank Transfer Entry values', 'armember', $entry_values, $arm_debug_payment_log_id);
                    $payment_cycle = $entry_values['arm_selected_payment_cycle']; 
                    $tax_percentage = isset($entry_values['tax_percentage']) ? $entry_values['tax_percentage'] : 0; 
                  
                    $setup_id = (isset($entry_values['setup_id']) && !empty($entry_values['setup_id'])) ? $entry_values['setup_id'] : 0 ; 
                    $plan_id = (!empty($posted_data['subscription_plan'])) ? intval($posted_data['subscription_plan']) : 0;
                    if ($plan_id == 0) {
                        $plan_id = (!empty($posted_data['_subscription_plan'])) ? intval($posted_data['_subscription_plan']) : 0;
                    }
                    $plan = new ARM_Plan($plan_id);
                    
                    $payment_mode = 'one_time';
                    if ($plan->is_recurring()) {
                        $payment_mode = "manual_subscription";
                    }
                   
                    $arm_user_old_plan = (isset($posted_data['old_plan_id']) && !empty($posted_data['old_plan_id'])) ? explode(",", $posted_data['old_plan_id']) : array();
                    if (!empty($arm_user_old_plan)) {
                        if (in_array($plan_id, $arm_user_old_plan)) {
                            $is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $payment_mode);
                            if($is_recurring_payment){
                                $planData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                                $oldPlanDetail = $planData['arm_current_plan_detail'];
                                if (!empty($oldPlanDetail)) {
                                    $plan = new ARM_Plan(0);
                                    $plan->init((object) $oldPlanDetail);
                                }
                            }
                        }
                    }
                    
                    if($plan->is_recurring())
                    {
                        $recurring_data = $plan->prepare_recurring_data($payment_cycle);
                        $amount = $recurring_data['amount'];
                    }
                    else{
                         $amount = $plan->amount;
                    }
                    $amount = str_replace(',','', $amount);
                    $arm_extra_vars = array();
                   $arm_extra_vars['plan_amount'] = $amount;
                    
                    if (!$plan->is_recurring() || $payment_mode == "manual_subscription") {
                
                        $bank_info = isset($posted_data['bank_transfer']) ? $posted_data['bank_transfer'] : array();
                        $arm_is_trial = '0';
                        
                        
                        $arm_user_old_plan = (!empty($arm_user_old_plan)) ? $arm_user_old_plan : array(); 
                        if ($plan->is_recurring() && $plan->has_trial_period() && empty($arm_user_old_plan)) {
                            
                            $arm_is_trial = '1';
                            $arm_extra_vars['trial'] = $recurring_data['trial'];
                            $arm_extra_vars['arm_is_trial'] = $arm_is_trial;
                            
                            
                            $amount = $plan->options['trial']['amount'];
                        }
                        $coupon_code = (!empty($posted_data['arm_coupon_code'])) ? $posted_data['arm_coupon_code'] : '';
                        $coupon_amount = 0;
                        $couponType = '';
                        $couponDiscount = 0;
                        $arm_coupon_on_each_subscriptions=0;
                        if ($arm_manage_coupons->isCouponFeature && !empty($coupon_code)) {
                            $couponApply = $arm_manage_coupons->arm_apply_coupon_code($coupon_code, $plan, $setup_id, $payment_cycle, $arm_user_old_plan);
                            $global_currency = $this->arm_get_global_currency();
                            $couponType = ($couponApply['discount_type'] != 'percentage') ? $global_currency : "%";
                            $couponDiscount = $couponApply['discount'];
                            $coupon_amount = isset($couponApply['coupon_amt']) ? $couponApply['coupon_amt'] : 0;
                            $discount_amt = isset($couponApply['total_amt']) ? $couponApply['total_amt'] : $amount;
                            $discount_amt = str_replace(',','', $discount_amt);
                            $arm_coupon_on_each_subscriptions = isset($couponApply['arm_coupon_on_each_subscriptions']) ? $couponApply['arm_coupon_on_each_subscriptions'] : '0';

                                                            $arm_extra_vars['coupon'] = array(
                                    'coupon_code' => $posted_data['arm_coupon_code'],
                                    'amount' => $coupon_amount,
                                    'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                                );
                                                            
                        }

                        if (!empty($coupon_amount) && $coupon_amount > 0) {
                            $amount = $discount_amt;
                        }

                        if($tax_percentage > 0){
                            $tax_amount = ($amount*$tax_percentage)/100;
                            $tax_amount = number_format((float)$tax_amount , 2, '.', '');
                            $amount = $amount + $tax_amount;
                            $arm_extra_vars['tax_amount'] = $tax_amount;
                            $arm_extra_vars['tax_percentage'] = $tax_percentage;
                        }
                        $amount = number_format((float)$amount , 2, '.', '');
                        $arm_extra_vars['paid_amount'] = $amount;
                        $arm_first_name='';
                        if(isset($posted_data['first_name']) && isset($posted_data['last_name'])){
                            $arm_first_name=$posted_data['first_name'];
                            $arm_last_name=$posted_data['last_name'];
                        }else if(!empty($user_id)){
                            $user_detail = get_userdata($user_id);
                            $arm_first_name=$user_detail->first_name;
                            $arm_last_name=$user_detail->last_name;
                        }
                        $payment_data = array(
                            'arm_user_id' => $user_id,
                            'arm_first_name'=>$arm_first_name,
                            'arm_last_name'=>$arm_last_name,
                            'arm_plan_id' => $plan->ID,
                            'arm_old_plan_id' => isset($posted_data['old_plan_id']) ? $posted_data['old_plan_id'] : 0,
                            'arm_payer_email' => $entry_data['arm_entry_email'],
                            'arm_transaction_id' => (isset($bank_info['transaction_id']) && $amount > 0 ) ? $bank_info['transaction_id'] : '-',
                            'arm_bank_name' => (isset($bank_info['bank_name'])) ? $bank_info['bank_name'] : '',
                            'arm_account_name' => (isset($bank_info['account_name'])) ? $bank_info['account_name'] : '',
                            'arm_additional_info' => (isset($bank_info['additional_info'])) ? $bank_info['additional_info'] : '',
                            'arm_payment_transfer_mode' => (isset($bank_info['transfer_mode'])) ? $bank_info['transfer_mode'] : '',
                            'arm_amount' => $amount,
                            'arm_payment_gateway'=>'bank_transfer',
                            'arm_payment_type' => ($payment_mode=='manual_subscription')?'subscription':'one_time',
                            'arm_transaction_payment_type' => ($payment_mode=='manual_subscription')?'subscription':'one_time',
                            'arm_payment_mode' => $payment_mode,
                            'arm_payment_cycle' => $payment_cycle,
                            'arm_currency' => $this->arm_get_global_currency(),
                            'arm_extra_vars' => maybe_serialize($arm_extra_vars),
                            'arm_coupon_code' => $coupon_code,
                            'arm_coupon_discount' => $couponDiscount,
                            'arm_coupon_discount_type' => $couponType,
                            'arm_transaction_status' => 0,
                            'arm_is_trial' => $arm_is_trial,
                            'arm_created_date' => current_time('mysql'),
                            'arm_payment_date' => current_time('mysql'),
                            'arm_coupon_on_each_subscriptions' => $arm_coupon_on_each_subscriptions,
                        );
                        do_action('arm_after_bank_transfer_payment',$plan,$payment_mode,$amount,$coupon_code,$arm_is_trial);
                        do_action('arm_before_add_transaction', $payment_data);
                        
                        $arm_last_invoice_id = get_option('arm_last_invoice_id', 0);
                        $arm_last_invoice_id++;
                        $payment_data['arm_invoice_id'] = $arm_last_invoice_id;
                        
                        do_action('arm_payment_log_entry', 'bank_transfer', 'Save payment log data', 'armember', $payment_data, $arm_debug_payment_log_id);

                        $payment_log = $wpdb->insert($ARMember->tbl_arm_payment_log, $payment_data);
                        $payment_log_id = $wpdb->insert_id;

                        $payment_data['arm_log_id'] = $payment_log_id;
                        
                        do_action('arm_after_add_transaction', $payment_data);

                        
                        $payment_done = array();
                        if ($payment_log_id) {
                            update_option('arm_last_invoice_id', $arm_last_invoice_id);
                            if ($coupon_code != '') {

                                $arm_manage_coupons->arm_update_coupon_used_count($coupon_code);
                            }
                            $payment_done = array('status' => TRUE, 'log_id' => $payment_log_id, 'entry_id' => $entry_id);
                        }
                    } else {
                        $err_msg =  __('Selected plan is not valid for bank transfer.', 'ARMember');
                        $payment_done = array('status' => FALSE, 'error' => $err_msg);
                    }
                }
            }
        }

        function arm_get_entry_data_by_id($entry_id = 0) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $entry_data = array();
            if (!empty($entry_id) && $entry_id != 0) {
                $entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id` = '" . $entry_id . "' LIMIT 1", ARRAY_A);
                if (!empty($entry_data)) {
                    $entry_data['arm_description'] = maybe_unserialize($entry_data['arm_description']);
                    $entry_data['arm_entry_value'] = maybe_unserialize($entry_data['arm_entry_value']);
                }
            }
            return $entry_data;
        }

        function arm_get_credit_card_box($type = 'stripe', $column_type = '1', $fieldPosition = 'left', $errPos = 'right') {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $type = (!empty($type)) ? $type : 'no_gateway';
            $gateways = $this->arm_get_all_payment_gateways();
            $gateways_opts = $gateways[$type];
            $cc_html = '';
            $ccFieldsHtml = '';
            $ccFieldsHtml .= apply_filters('arm_add_before_cc_field_filter', $ccFieldsHtml);

            $arm_card_holder_label_filter = apply_filters('arm_payment_card_holder_filter', $allowed_arr = array(), $type);

            foreach (array('card_holder_name', 'card_number', 'exp_month', 'exp_year', 'cvc') as $key) {
                $fieldLabel = $fieldClass = $fieldAttr = $validation = $fieldDesc = '';
                switch ($key) {
                    case 'card_holder_name':
                        if($type == 'stripe' || $type == 'paypal_pro' || $type == 'online_worldpay' || in_array($type, $arm_card_holder_label_filter)) {
                            $fieldLabel = !empty($gateways_opts['card_holder_name']) ? stripslashes($gateways_opts['card_holder_name']) : __('Card Holder Name', 'ARMember');
                            $fieldDesc = !empty($gateways_opts['card_holder_name_description']) ? stripslashes($gateways_opts['card_holder_name_description']) : '';
                            $fieldAttr = 'name="' . $type . '[' . $key . ']"';
                        }
                        break;
                    case 'card_number':
                        $fieldLabel = !empty($gateways_opts['cc_label']) ? stripslashes($gateways_opts['cc_label']) : __('Credit Card Number', 'ARMember');
                        $fieldDesc = !empty($gateways_opts['cc_desc']) ? stripslashes($gateways_opts['cc_desc']) : '';
                        $fieldAttr = 'name="' . $type . '[' . $key . ']" data-ng-model="arm_form.card_number' . $type . '" data-ng-minlength="isminlengthField(\'' . $type . '\')" data-ng-maxlength="19" minlength="isminlengthField(\'' . $type . '\')" maxlength="19" onkeydown="armvalidatenumber(event);"';
                        $fieldAttr .= ' data-ng-required="isPaymentGatewayField(\'' . $type . '\')" data-msg-required="' . __('This field can not be left blank', 'ARMember') . '" cc-number cc-eager-type';
                        $fieldClass = ' cardNumber';
                        $validation .= '<div data-ng-cloak data-ng-messages="arm_form[\'' . $type . '[' . $key . ']\'].$error" data-ng-show="arm_form[\'' . $type . '[' . $key . ']\'].$touched" class="arm_error_msg_box ng-cloak">';
                        $err_msg = $arm_global_settings->common_message['arm_blank_credit_card_number'];
                        $cc_error = (!empty($err_msg)) ? $err_msg : __('Card Number should not be blank.', 'ARMember');
                        $validation .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $cc_error . '</div>';
                        $validation .= '<div data-ng-message="minlength" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter at least 13 digits.', 'ARMember') . '</div>';
                        $validation .= '<div data-ng-message="maxlength" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Maximum 16 digits allowed.', 'ARMember') . '</div>';
                        $err_msg = $arm_global_settings->common_message['arm_invalid_credit_card'];
                        $ec_error = (!empty($err_msg)) ? $err_msg : __('Please enter correct card details.', 'ARMember');
                        
                       
		       $validation .= '<div data-ng-show="arm_form[\'' . $type . '[' . $key . ']\'].$invalid && !arm_form[\'' . $type . '[' . $key . ']\'].$error.required && !arm_form[\'' . $type . '[' . $key . ']\'].$error.minlength" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $ec_error . '</div>';
                        $validation .= '</div>';
                        break;
                    case 'cvc':
                        $fieldLabel = !empty($gateways_opts['cvv_label']) ? stripslashes($gateways_opts['cvv_label']) : __('CVV Code', 'ARMember');
                        $fieldDesc = !empty($gateways_opts['cvv_desc']) ? stripslashes($gateways_opts['cvv_desc']) : '';
                        $fieldAttr = 'name="' . $type . '[' . $key . ']" data-ng-model="arm_form.cvc' . $type . '" onkeydown="armvalidatenumber(event);" maxlength="4"';
                        $fieldAttr .= ' data-ng-required="isPaymentGatewayField(\'' . $type . '\')" data-msg-required="' . __('This field can not be left blank', 'ARMember') . '"';
                        $fieldClass = ' cardCVC';
                        $validation .= '<div data-ng-cloak data-ng-messages="arm_form[\'' . $type . '[' . $key . ']\'].$error" data-ng-show="arm_form[\'' . $type . '[' . $key . ']\'].$touched" class="arm_error_msg_box ng-cloak">';
                        $err_msg = $arm_global_settings->common_message['arm_blank_cvc_number'];
                        $cvc_error = (!empty($err_msg)) ? $err_msg : __('CVC Number should not be blank.', 'ARMember');
                        $validation .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $cvc_error . '</div>';
                        $validation .= '</div>';
                        break;
                    case 'exp_month':
                        $fieldLabel = !empty($gateways_opts['em_label']) ? stripslashes($gateways_opts['em_label']) : __('Expiration Month', 'ARMember');
                        $fieldDesc = !empty($gateways_opts['em_desc']) ? stripslashes($gateways_opts['em_desc']) : '';
                        $fieldAttr = 'name="' . $type . '[' . $key . ']" data-ng-model="arm_form.exp_month' . $type . '" onkeydown="armvalidatenumber(event);" maxlength="2" size="2"';
                        $fieldAttr .= ' data-ng-required="isPaymentGatewayField(\'' . $type . '\')" data-msg-required="' . __('This field can not be left blank', 'ARMember') . '"';
                        $fieldClass = ' card-expiry-month';
                        $validation .= '<div data-ng-cloak data-ng-messages="arm_form[\'' . $type . '[' . $key . ']\'].$error" data-ng-show="arm_form[\'' . $type . '[' . $key . ']\'].$touched" class="arm_error_msg_box ng-cloak">';
                        $err_msg = $arm_global_settings->common_message['arm_blank_expire_month'];
                        $em_error = (!empty($err_msg)) ? $err_msg : __('Expiry month should not be blank.', 'ARMember');
                        $validation .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $em_error . '</div>';
                        $validation .= '</div>';
                        break;
                    case 'exp_year':
                        $fieldLabel = !empty($gateways_opts['ey_label']) ? stripslashes($gateways_opts['ey_label']) : __('Expiration Year', 'ARMember');
                        $fieldDesc = !empty($gateways_opts['ey_desc']) ? stripslashes($gateways_opts['ey_desc']) : '';
                        $fieldAttr = 'name="' . $type . '[' . $key . ']" data-ng-model="arm_form.exp_year' . $type . '" onkeydown="armvalidatenumber(event);" maxlength="4" size="4"';
                        $fieldAttr .= ' data-ng-required="isPaymentGatewayField(\'' . $type . '\')" data-msg-required="' . __('This field can not be left blank', 'ARMember') . '"';
                        $fieldClass = ' card-expiry-year';
                        $validation .= '<div data-ng-cloak data-ng-messages="arm_form[\'' . $type . '[' . $key . ']\'].$error" data-ng-show="arm_form[\'' . $type . '[' . $key . ']\'].$touched" class="arm_error_msg_box ng-cloak">';
                        $err_msg = $arm_global_settings->common_message['arm_blank_expire_year'];
                        $ey_error = (!empty($err_msg)) ? $err_msg : __('Expiry year should not be blank.', 'ARMember');
                        $validation .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . $ey_error . '</div>';
                        $validation .= '</div>';
                        break;
                    default:
                        break;
                }
                if($type == 'authorize_net' && $key == "card_holder_name") {
                }else{
                    $ccFieldsHtml .= '<div class="arm_cc_field_wrapper arm_form_field_container arm_form_field_container_text">';
                    $ccFieldsHtml .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                    $ccFieldsHtml .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $fieldLabel . '</div></div>';
                    $ccFieldsHtml .= '</div>';
                    $ccFieldsHtml .= '<div class="arm_label_input_separator"></div>';
                    $ccFieldsHtml .= '<div class="arm_form_input_wrapper">';
                    $ccFieldsHtml .= '<div class="arm_form_input_container arm_form_input_container_' . $key . '">';
                    $ccFieldsHtml .= '<md-input-container class="md-block" flex-gt-sm="">';
                    $ccFieldsHtml .= '<label class="arm_material_label">' . $fieldLabel . '</label>';
                    $ccFieldsHtml .= '<input type="text" value="" class="field_' . $type . ' ' . $fieldClass . '" ' . $fieldAttr . '>';
                    $ccFieldsHtml .= $validation;
                    $ccFieldsHtml .= '</md-input-container>';
                    $ccFieldsHtml .= '</div>';
                    if (!empty($fieldDesc)) {
                        $ccFieldsHtml .= '<span>' . $fieldDesc . '</span>';
                    }
                    $ccFieldsHtml .= '</div>';
                    $ccFieldsHtml .= '</div>';
                }
            }
                        $ccFieldsHtml = apply_filters('arm_add_cc_fields_outside', $ccFieldsHtml, $type, $gateways_opts);
            $cc_html .= '<div class="arm_form_inner_container arm_msg_pos_' . $errPos . '">';
            $cc_html .= '<div class="arm_cc_fields_container arm_' . $type . '_fields arm_form_wrapper_container arm_field_position_' . $fieldPosition . '">';
            $cc_html .= '<span class="payment-errors"></span>';
            $cc_html .= $ccFieldsHtml;
            $cc_html .= '</div>';
            $cc_html .= '<div class="armclear"></div>';
            $cc_html .= '</div>';
            return $cc_html;
        }

        function arm_get_bank_transfer_form($options = array(), $fieldPosition = 'left', $errPos = 'right', $form_id = 0) {
            $gateways = $this->arm_get_all_payment_gateways();
            $gateways_opts = $gateways['bank_transfer'];
            $bt_fields = isset($options['fields']) ? $options['fields'] : array();
            $bt_form = '<div class="arm_form_inner_container arm_msg_pos_' . $errPos . '">';
            $bt_form .= '<div class="arm_bank_transfer_fields_container arm_form_wrapper_container arm_field_position_' . $fieldPosition . '">';
            /* Transaction ID Input */
            $transaction_id_field_label = !empty($gateways_opts['transaction_id_label']) ? stripslashes($gateways_opts['transaction_id_label']) : __('Transaction ID', 'ARMember');

            if (isset($bt_fields['transaction_id']) && $bt_fields['transaction_id'] = '1') {
                $bt_form .= '<div class="arm_bt_field_wrapper arm_form_field_container arm_form_field_container_text">';
                $bt_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                $bt_form .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $transaction_id_field_label . '</div></div>';
                $bt_form .= '</div>';
                $bt_form .= '<div class="arm_label_input_separator"></div>';
                $bt_form .= '<div class="arm_form_input_wrapper">';
                $bt_form .= '<div class="arm_form_input_container arm_form_input_container_transaction_id">';
                $bt_form .= '<md-input-container class="md-block" flex-gt-sm="">';
                $bt_form .= '<label class="arm_material_label">' . $transaction_id_field_label . '</label>';
                $bt_form .= '<input type="text" name="bank_transfer[transaction_id]" value="" class="field_bank_transfer arm_bank_transaction_id " data-ng-model="arm_form.transaction_idbank_transfer" data-ng-required="isPaymentGatewayField(\'bank_transfer\')" >';
                $bt_form .= '<div data-ng-cloak data-ng-messages="arm_form[\'bank_transfer[transaction_id]\'].$error" data-ng-show="arm_form[\'bank_transfer[transaction_id]\'].$touched" class="arm_error_msg_box ng-cloak">';
                $bt_form .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter', 'ARMember') . " " . $transaction_id_field_label . '.</div>';
                $bt_form .= '</div>';
                $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . __('Please enter', 'ARMember') . " " . $transaction_id_field_label . '.</span>';
                $bt_form .= '</md-input-container>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
            }


            if (isset($bt_fields['bank_name']) && $bt_fields['bank_name'] = '1') {
                /* Bank Name Input */
                $bank_name_field_label = !empty($gateways_opts['bank_name_label']) ? stripslashes($gateways_opts['bank_name_label']) : __('Bank Name', 'ARMember');
                $bt_form .= '<div class="arm_bt_field_wrapper arm_form_field_container arm_form_field_container_text">';
                $bt_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                $bt_form .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $bank_name_field_label . '</div></div>';
                $bt_form .= '</div>';
                $bt_form .= '<div class="arm_label_input_separator"></div>';
                $bt_form .= '<div class="arm_form_input_wrapper">';
                $bt_form .= '<div class="arm_form_input_container arm_form_input_container_transaction_id">';
                $bt_form .= '<md-input-container class="md-block" flex-gt-sm="">';
                $bt_form .= '<label class="arm_material_label">' . $bank_name_field_label . '</label>';
                $bt_form .= '<input type="text" name="bank_transfer[bank_name]" value="" class="field_bank_transfer" data-ng-model="arm_form.bank_namebank_transfer" data-ng-required="isPaymentGatewayField(\'bank_transfer\')">';
                $bt_form .= '<div data-ng-cloak data-ng-messages="arm_form[\'bank_transfer[bank_name]\'].$error" data-ng-show="arm_form[\'bank_transfer[bank_name]\'].$touched" class="arm_error_msg_box ng-cloak">';
                $bt_form .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter', 'ARMember') . " " . $bank_name_field_label . '.</div>';
                $bt_form .= '</div>';
                $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . __('Please enter', 'ARMember') . " " . $bank_name_field_label . '.</span>';
                $bt_form .= '</md-input-container>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
            }
            if (isset($bt_fields['account_name']) && $bt_fields['account_name'] = '1') {
                /* Account Name Input */
                $account_name_field_label = !empty($gateways_opts['account_name_label']) ? stripslashes($gateways_opts['account_name_label']) : __('Account Holder Name', 'ARMember');
                $bt_form .= '<div class="arm_bt_field_wrapper arm_form_field_container arm_form_field_container_text">';
                $bt_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                $bt_form .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $account_name_field_label . '</div></div>';
                $bt_form .= '</div>';
                $bt_form .= '<div class="arm_label_input_separator"></div>';
                $bt_form .= '<div class="arm_form_input_wrapper">';
                $bt_form .= '<div class="arm_form_input_container arm_form_input_container_transaction_id">';
                $bt_form .= '<md-input-container class="md-block" flex-gt-sm="">';
                $bt_form .= '<label class="arm_material_label">' . $account_name_field_label . '</label>';
                $bt_form .= '<input type="text" name="bank_transfer[account_name]" value="" class="field_bank_transfer" data-ng-model="arm_form.account_namebank_transfer" data-ng-required="isPaymentGatewayField(\'bank_transfer\')">';
                $bt_form .= '<div data-ng-cloak data-ng-messages="arm_form[\'bank_transfer[account_name]\'].$error" data-ng-show="arm_form[\'bank_transfer[account_name]\'].$touched" class="arm_error_msg_box ng-cloak">';
                $bt_form .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter', 'ARMember') . " " . $account_name_field_label . '.</div>';
                $bt_form .= '</div>';
                $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . __('Please enter', 'ARMember') . " " . $account_name_field_label . '.</span>';
                $bt_form .= '</md-input-container>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
            }
            if (isset($bt_fields['additional_info']) && $bt_fields['additional_info'] = '1') {
                /* Additional Note Input */
                $additional_info_field_label = !empty($gateways_opts['additional_info_label']) ? stripslashes($gateways_opts['additional_info_label']) : __('Additional Note', 'ARMember');
                $bt_form .= '<div class="arm_bt_field_wrapper arm_form_field_container arm_form_field_container_text">';
                $bt_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                $bt_form .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $additional_info_field_label . '</div></div>';
                $bt_form .= '</div>';
                $bt_form .= '<div class="arm_label_input_separator"></div>';
                $bt_form .= '<div class="arm_form_input_wrapper">';
                $bt_form .= '<div class="arm_form_input_container arm_form_input_container_transaction_id">';
                $bt_form .= '<md-input-container class="md-block" flex-gt-sm="">';
                $bt_form .= '<label class="arm_material_label">' . $additional_info_field_label . '</label>';
                $bt_form .= '<textarea class="arm_textarea field_bank_transfer" name="bank_transfer[additional_info]" data-ng-model="arm_form.additional_infobank_transfer" data-ng-required="isPaymentGatewayField(\'bank_transfer\')" rows="3" cols="10"></textarea>';
                $bt_form .= '<div data-ng-cloak data-ng-messages="arm_form[\'bank_transfer[additional_info]\'].$error" data-ng-show="arm_form[\'bank_transfer[additional_info]\'].$touched" class="arm_error_msg_box ng-cloak">';
                $bt_form .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please enter', 'ARMember') . " " . $additional_info_field_label . '.</div>';
                $bt_form .= '</div>';
                $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . __('Please enter', 'ARMember') . " " . $additional_info_field_label . '.</span>';
                $bt_form .= '</md-input-container>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
            }
            if(isset($bt_fields['transfer_mode']) && $bt_fields['transfer_mode'] = '1') {
                /* Payment transfer mode Input */
                $transfer_mode = $this->arm_get_bank_transfer_mode_options();
                $transfer_mode_li = '<md-option class="armMDOption armSelectOption arm_payment_transfer_mode_option_'.$form_id.'" role="option" aria-selected="false" value="">' . esc_html__('Select Payment Mode', 'ARMember') . '</md-option>';
                $transfer_mode_option_arr = !empty($gateways_opts['fields']['transfer_mode_option']) ? $gateways_opts['fields']['transfer_mode_option'] : array();
                foreach ($transfer_mode as $key => $value) {
                    if(in_array($key, $transfer_mode_option_arr)) {
                        $transfer_mode_option_val = !empty($gateways_opts['fields']['transfer_mode_option_label'][$key]) ? $gateways_opts['fields']['transfer_mode_option_label'][$key] : $value;
                        $transfer_mode_li .= '<md-option class="armMDOption armSelectOption arm_payment_transfer_mode_option_'.$form_id.'" role="option" aria-selected="false" value="' . esc_attr($key) . '">' . $transfer_mode_option_val . '</md-option>';    
                    }
                }

                $ng_change_func = "";
                $name = "bank_transfer[transfer_mode]";
                $ngModelSelect = esc_attr($name);
                $required = ' required="required" data-ng-required="isPaymentGatewayField(\'bank_transfer\')" ';
                $field_attr = 'name="' . esc_attr($name) . '" data-ng-model="'.$ngModelSelect.'" ' . $required;
                $field_attr .= ' aria-label=""';
                $writter_class = (!empty($form) && isset($formStyles['rtl']) && $formStyles['rtl'] == '1') ? 'armSelectOptionRTL' : 'armSelectOptionLTR';
                $payment_mode_field_label = !empty($gateways_opts['transfer_mode_label']) ? stripslashes($gateways_opts['transfer_mode_label']) : __('Payment Mode', 'ARMember');


                $bt_form .= '<div class="arm_bt_field_wrapper arm_form_field_container arm_form_field_container_text">';
                $bt_form .= '<div class="arm_form_label_wrapper arm_form_field_label_wrapper arm_form_member_field_text">';
                $bt_form .= '<div class="arm_member_form_field_label"><div class="arm_form_field_label_text">' . $payment_mode_field_label . '</div></div>';
                $bt_form .= '</div>';

                $bt_form .= '<div class="arm_label_input_separator"></div>';

                $bt_form .= '<div class="arm_form_input_wrapper">';
                $bt_form .= '<div class="arm_form_input_container arm_form_input_container_payment_mode">';
                $bt_form .= '<md-input-container class="md-block" flex-gt-sm="">';
                $bt_form .= '<label class="arm_material_label">' . esc_html__('Select Payment Mode', 'ARMember') . '</label>';
                $bt_form .= '<md-select class="" ' . $field_attr . $ng_change_func .'>';
		//need to check empty class
                
                $bt_form .= $transfer_mode_li;

                $bt_form .= '</md-select>';
                
                $bt_form .= '<div data-ng-cloak data-ng-messages="arm_form[\'bank_transfer[transfer_mode]\'].$error" data-ng-show="arm_form[\'bank_transfer[transfer_mode]\'].$touched" class="arm_error_msg_box ng-cloak">';
                $bt_form .= '<div data-ng-message="required" class="arm_error_msg"><div class="arm_error_box_arrow"></div>' . __('Please select', 'ARMember') . " " . $payment_mode_field_label . '.</div>';
                $bt_form .= '</div>';
                $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . __('Please select', 'ARMember') . " " . $payment_mode_field_label . '.</span>';
                $bt_form .= '</md-input-container>';
                $bt_form .= '<input type="hidden" name="' . esc_attr($name) . '" value="{{ ' . esc_attr($ngModelSelect) . ' }}">';
                $bt_form .= '</div>';
                $bt_form .= '</div>';
                $bt_form .= '</div>';

            }

            $bt_form .= '</div>';
            $bt_form .= '<div class="armclear"></div>';
            $bt_form .= '</div>';
            return $bt_form;
        }

        /**
         * Get Currency Name/Label From Currency Code For Paypal
         */
        function arm_paypal_currency_symbol() {
            
            /* 26 currency */
            $currency_symbol = array(
                
                'AUD' => '$',
                'BRL' => 'R$',
                'CAD' => '$',
                'CZK' => '&#75;&#269;',
                'DKK' => '&nbsp;&#107;&#114;',
                'EUR' => '&#128;',
                'HKD' => '&#36;',
                'HUF' => '&#70;&#116;',
                'ILS' => '&#8362;',
                'JPY' => '&#165;',
                'MYR' => '&#82;&#77;',
                'MXN' => '&#36;',
                'TWD' => '&#36;',
                'NZD' => '&#36;',
                'NOK' => '&nbsp;&#107;&#114;',
                'PHP' => '&#8369;',
                'PLN' => '&#122;&#322;',
                'GBP' => '&#163;',
                'RUB' => '&#1088;&#1091;',
                'SGD' => '&#36;',
                'SEK' => '&nbsp;&#107;&#114;',
                'CHF' => '&#67;&#72;&#70;',
                'THB' => '&#3647;',
                'USD' => '$',
                'TRY' => '&#89;&#84;&#76;',
                'INR' => '&#8377;',
            );
            return $currency_symbol;
        }

        /**
         * Get Currency Name/Label From Currency Code For Bank Transfer - Total 168
         */
        function arm_bank_transfer_currency_symbol()
        {
            $currency_symbol = array(
                'AED' => '&#x62f;&#x2e;&#x625;',
                'AFN' => '&#1547;',
                'ALL' => 'L',
                'AMD' => '&#1423;',
                'ANG' => '$',
                'AOA' => 'Kz',
                'ARS' => '&#36;',
                'AUD' => '$',
                'AWG' => '&#x192;',
                'AZN' => 'maH',
                'BAM' => '&#x4b;&#x4d',
                'BBD' => 'Bds&#36;',
                'BDT' => '&#2547;',
                'BGN' => '&#1074;',
                'BHD' => '.&#x62f;.&#x628;',
                'BIF' => 'FBu',
                'BMD' => 'BD$',
                'BND' => 'B$',
                'BOB' => 'Bs.',
                'BRL' => 'R$',
                'BSD' => '&#x0024;',
                'BTC' => '&#3647;',
                'BTN' => 'Nu.',
                'BWP' => 'P',
                'BYN' => 'Br',
                'BYR' => 'Br',
                'BZD' => 'BZ$',
                'CAD' => '$',
                'CDF' => 'FC',
                'CHF' => '&#67;&#72;&#70;',
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'CUC' => '&#36;',
                'CUP' => '&#36;',
                'CVE' => 'Esc',
                'CZK' => '&#75;&#269;',
                'DJF' => 'Fdj',
                'DKK' => '&nbsp;&#107;&#114;',
                'DOP' => 'RD$',
                'DZD' => 'DA',
                'EGP' => 'E&#163;',
                'ERN' => 'Nfk',
                'ETB' => 'Br',
                'EUR' => '&#128;',
                'FJD' => 'FJ$',
                'FKP' => '&#xa3;',
                'GBP' => '&#163;',
                'GEL' => '&#x20be;',
                'GGP' => '&pound;',
                'GHS' => '&#x20b5;',
                'GIP' => '&#xa3;',
                'GMD' => 'D',
                'GNF' => 'FG',
                'GTQ' => 'Q',
                'GYD' => '&#x24;',
                'HKD' => '&#36;',
                'HNL' => 'L',
                'HRK' => 'kn',
                'HTG' => 'G',
                'HUF' => '&#70;&#116;',
                'IDR' => 'Rp',
                'ILS' => '&#8362;',
                'IMP' => '&pound;',
                'INR' => '&#8377;',
                'IQD' => '&#x639;.&#x62f;',
                'IRR' => '&#xfdfc;',
                'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
                'ISK' => '&nbsp;&#x6b;&#x72;',
                'JEP' => '&pound;',
                'JMD' => 'J$',
                'JOD' => '&#x62f;.&#x627;',
                'JPY' => '&#165;',
                'KES' => 'KSh',
                'KGS' => '&#x43b;&#x432;',
                'KHR' => '&#x17db;',
                'KMF' => 'CF',
                'KPW' => '&#x20a9;',
                'KRW' => '&#8361;',
                'KWD' => '&#x62f;.&#x643;',
                'KYD' => '$',
                'KZT' => '&#8376;',
                'LAK' => '&#8365;',
                'LBP' => 'L&#163;',
                'LKR' => '&#8360;',
                'LRD' => 'L$',
                'LSL' => 'L',
                'LYD' => '&#x644;.&#x62f;',
                'MAD' => '&#x2e;&#x62f;&#x2e;&#x645;',
                'MDL' => 'MDL',
                'MGA' => 'Ar',
                'MKD' => '&#x434;&#x435;&#x43d;',
                'MMK' => 'K',
                'MNT' => '&#x20ae;',
                'MOP' => 'P',
                'MRO' => 'UM',
                'MRU' => 'UM',
                'MUR' => '&#8360;',
                'MVR' => 'Rf',
                'MWK' => 'MK',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => '&#x4d;&#x54;',
                'NAD' => '$',
                'NGN' => '&#x20a6;',
                'NIO' => 'C$',
                'NOK' => '&nbsp;&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'OMR' => '&#x631;.&#x639;.',
                'PAB' => 'B/.',
                'PEN' => 'S/.',
                'PGK' => 'K',
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PRB' => '&#x440;.',
                'PYG' => 'Gs',
                'QAR' => '&#65020;',
                'RMB' => '&yen;',
                'RON' => 'L',
                'RSD' => '&#x414;&#x438;&#x43d;&#x2e;',
                'RUB' => '&#1088;&#1091;',
                'RWF' => 'RF',
                'SAR' => '&#65020;',
                'SBD' => 'SI$',
                'SCR' => '&#8360;',
                'SDG' => '&pound;SD',
                'SEK' => '&nbsp;&#107;&#114;',
                'SGD' => '&#36;',
                'SGF' => 'S$',
                'SHP' => '&#xa3;',
                'SYP' => 'S&#163;',
                'TJS' => 'TSh',
                'SLL' => 'Le',
                'SOS' => 'S',
                'SRD' => '$',
                'SSP' => '&pound;',
                'STD' => 'Db',
                'STN' => 'Db',
                'SVC' => '$',
                'SZL' => 'SZL',
                'THB' => '&#3647;',
                'TMT' => 'm',
                'TND' => '&#x62f;.&#x62a;',
                'TOP' => 'T$',
                'TRY' => '&#89;&#84;&#76;',
                'TTD' => 'TT$',
                'TWD' => '&#36;',
                'TZS' => 'x',
                'UAH' => '&#8372;',
                'UGX' => 'USh',
                'USD' => '$',
                'UYU' => '$U',
                'UZS' => '&#x43b;&#x432;',
                'VEF' => 'Bs F',
                'VES' => 'Bs.S',
                'VND' => '&#8363;',
                'VUV' => 'VT',
                'WST' => 'T',
                'XAF' => 'FCFA',
                'XCD' => 'EC$',
                'XOF' => 'CFA',
                'XPF' => 'F',
                'YER' => '&#65020;',
                'ZAR' => 'R',
                'ZMW' => 'ZK',               
            );
            return $currency_symbol;
        }

        /**
         * Get Currency Name/Label From Currency Code For Stripe
         */
        function arm_stripe_currency_symbol() {
            $currency_symbol = array(
                'AED' => '&#x62f;&#x2e;&#x625;',
                'AFN' => '&#1547;',
                'ALL' => 'L',
                'AMD' => '&#1423;',
                'ANG' => '$',
                'AOA' => 'Kz',
                'ARS' => '&#36;',
                'AUD' => '$',
                'AWG' => '&#x192;',
                'AZN' => 'maH',
                'BAM' => '&#x4b;&#x4d',
                'BBD' => 'Bds&#36;',
                'BDT' => '&#2547;',
                'BGN' => '&#1074;',
                'BIF' => 'FBu',
                'BMD' => 'BD$',
                'BND' => 'B$',
                'BOB' => 'Bs.',
                'BRL' => 'R$',
                'BSD' => '&#x0024;',
                'BWP' => 'P',
                'BZD' => 'BZ$',
                'CAD' => '$',
                'CDF' => 'FC',
                'CHF' => '&#67;&#72;&#70;',
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'CVE' => 'Esc',
                'CZK' => '&#75;&#269;',
                'DJF' => 'Fdj',
                'DKK' => '&nbsp;&#107;&#114;',
                'DOP' => 'RD$',
                'DZD' => 'DA',
                'EGP' => 'E&#163;',
                'ETB' => 'Br',
                'EUR' => '&#128;',
                'FJD' => 'FJ$',
                'FKP' => '&#xa3;',
                'GBP' => '&#163;',
                'GEL' => '&#x20be;',
                'GHS' => '&#x20b5;',
                'GIP' => '&#xa3;',
                'GMD' => 'D',
                'GNF' => 'FG',
                'GTQ' => 'Q',
                'GYD' => '&#x24;',
                'HKD' => '&#36;',
                'HNL' => 'L',
                'HRK' => 'kn',
                'HTG' => 'G',
                'HUF' => '&#70;&#116;',
                'IDR' => 'Rp',
                'ILS' => '&#8362;',
                'INR' => '&#8377;',
                'ISK' => '&nbsp;&#x6b;&#x72;',
                'JMD' => 'J$',
                'JPY' => '&#165;',
                'KES' => 'KSh',
                'KGS' => '&#x43b;&#x432;',
                'KHR' => '&#x17db;',
                'KMF' => 'CF',
                'KRW' => '&#8361;',
                'KYD' => '$',
                'KZT' => '&#8376;',
                'LAK' => '&#8365;',
                'LBP' => 'L&#163;',
                'LKR' => '&#8360;',
                'LRD' => 'L$',
                'LSL' => 'L',
                'MAD' => '&#x2e;&#x62f;&#x2e;&#x645;',
                'MDL' => 'MDL',
                'MGA' => 'Ar',
                'MKD' => '&#x434;&#x435;&#x43d;',
                'MMK' => 'K',
                'MNT' => '&#x20ae;',
                'MOP' => 'P',
                'MRO' => 'UM',
                'MUR' => '&#8360;',
                'MVR' => 'Rf',
                'MWK' => 'MK',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => '&#x4d;&#x54;',
                'NAD' => '$',
                'NGN' => '&#x20a6;',
                'NIO' => 'C$',
                'NOK' => '&nbsp;&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'PAB' => 'B/.',
                'PEN' => 'S/.',
                'PGK' => 'K',
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PYG' => 'Gs',
                'QAR' => '&#65020;',
                'RON' => 'L',
                'RSD' => '&#x414;&#x438;&#x43d;&#x2e;',
                'RUB' => '&#1088;&#1091;',
                'RWF' => 'RF',
                'SAR' => '&#65020;',
                'SBD' => 'SI$',
                'SCR' => '&#8360;',
                'SEK' => '&nbsp;&#107;&#114;',
                'SGD' => '&#36;',
                'SHP' => '&#xa3;',
                'TJS' => 'TSh',
                'SLL' => 'Le',
                'SOS' => 'S',
                'SRD' => '$',
                'STD' => 'Db',
                'SVC' => '$',
                'SZL' => 'SZL',
                'THB' => '&#3647;',
                'TOP' => 'T$',
                'TRY' => '&#89;&#84;&#76;',
                'TTD' => 'TT$',
                'TWD' => '&#36;',
                'TZS' => 'x',
                'UAH' => '&#8372;',
                'UGX' => 'USh',
                'USD' => '$',
                'UYU' => '$U',
                'UZS' => '&#x43b;&#x432;',
                'VND' => '&#8363;',
                'VUV' => 'VT',
                'WST' => 'T',
                'XAF' => 'FCFA',
                'XCD' => 'EC$',
                'XOF' => 'CFA',
                'XPF' => 'F',
                'YER' => '&#65020;',
                'ZAR' => 'R',
                'ZMW' => 'ZK'               
            );
            return $currency_symbol;
        }
        
        
        function arm_stripe_zero_decimal_currency_array(){
            return apply_filters('arm_stripe_zero_demial_currency', array('BIF', 'DJF','JPY', 'KRW','PYG', 'VND', 'XAF', 'XPF', 'CLP', 'GNF', 'KMF', 'MGA', 'RWF', 'VUV', 'XOF', 'UGX' ) );
        }

        /**
         * Get Currency Name/Label From Currency Code For Authorize.Net
         */
        function arm_authorize_net_currency_symbol() {
            $currency_symbol = array(
                'USD' => '$',
                'GBP' => '&#163;',
                'EUR' => '&#128;',
            );
            return $currency_symbol;
        }

        /**
         * Get Currency Name/Label From Currency Code For 2Checkout
         */
        function arm_2checkout_currency_symbol() {
            $currency_symbol = array(
                /* 88 currency */
                
                'AFN' => '&#1547;',
                'ALL' => 'L',
                'ARS' => '&#36;',
                'AZN' => 'maH',
                'DZD' => 'DA',
                'AUD' => '$',
                'BSD' => '&#x0024;',
                'BDT' => '&#2547;',
                'BBD' => 'Bds&#36;',
                'BZD' => 'BZ$',
                
                'BMD' => 'BD$',
                'BOB' => 'Bs.',
                'BWP' => 'P',
                'BRL' => 'R$',
                'BND' => 'B$',
                'BGN' => '&#1074;',
                'GBP' => '&#163;',
                'USD' => '$',
                'CAD' => '$',
                'CZK' => '&#75;&#269;',
                
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'HRK' => 'kn',
                'DKK' => '&nbsp;&#107;&#114;',
                'DOP' => 'RD$',
                'XCD' => 'EC$',
                'EGP' => 'E&#163;',
                'EUR' => '&#128;',
                
                'FJD' => 'FJ$',
                'GTQ' => 'Q',
                'HKD' => '&#36;',
                'HNL' => 'L',
                'HUF' => '&#70;&#116;',
                'ILS' => '&#8362;',
                'INR' => '&#8377;',
                'IDR' => 'Rp',
                'JMD' => 'J$',
                'JPY' => '&#165;',
                
                'KZT' => '&#8376;',
                'KES' => 'KSh',
                'LAK' => '&#8365;',
                'MMK' => 'K',
                'LBP' => 'L&#163;',
                'LRD' => 'L$',
                'MOP' => 'P',
                'MYR' => '&#82;&#77;',
                'MVR' => 'Rf',
                'MRO' => 'UM',
                
                'MUR' => '&#8360;',
                'MXN' => '&#36;',
                'MAD' => '&#x2e;&#x62f;&#x2e;&#x645;',
                'NPR' => '&#8360;',
                'TWD' => '&#36;',
                'NZD' => '&#36;',
                'NIO' => 'C$',
                'NOK' => '&nbsp;&#107;&#114;',
                'PKR' => '&#8360;',
                'PGK' => 'K',
                
                'PEN' => 'S/.',
                'PHP' => '&#8369;',
                'PLN' => '&#122;&#322;',
                'QAR' => '&#65020;',
                'RON' => 'L',
                'WST' => 'T',
                'SAR' => '&#65020;',
                'SCR' => '&#8360;',
                'SGF' => 'S$',
                'SBD' => 'SI$',
                
                'ZAR' => 'R',
                'KRW' => '&#8361;',
                'LKR' => '&#8360;',
                'SEK' => '&nbsp;&#107;&#114;',
                'CHF' => '&#67;&#72;&#70;',
                'SYP' => 'S&#163;',
                'TOP' => 'T$',
                'TTD' => 'TT$',
                'THB' => '&#3647;',
                'UAH' => '&#8372;',
                
                'AED' => '&#x62f;&#x2e;&#x625;',
                'VUV' => 'VT',
                'VND' => '&#8363;',
                'XOF' => 'CFA',
                'YER' => '&#65020;',
                'RUB' => '&#1088;&#1091;',
                'TRY' => '&#89;&#84;&#76;',
                
                'SGD' => '&#36;',
                
            );
            return $currency_symbol;
        }

        function arm_check_currency_status($arm_currency = 'USD') {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_membership_setup, $arm_capabilities_global;
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $currency = (isset($_REQUEST['arm_currency']) && !empty($_REQUEST['arm_currency'])) ? sanitize_text_field($_REQUEST['arm_currency']) : $arm_currency;
            $message = '';
            $notAllow = $notAllowSetups = array();
            if (!empty($currency)) {
                if (!array_key_exists($currency, $this->currency['paypal'])) {
                    $notAllow[] = 'paypal';
                }
                if (!array_key_exists($currency, $this->currency['stripe'])) {
                    $notAllow[] = 'stripe';
                }
                if (!array_key_exists($currency, $this->currency['authorize_net'])) {
                    $notAllow[] = 'authorize_net';
                }
                if (!array_key_exists($currency, $this->currency['2checkout'])) {
                    $notAllow[] = '2checkout';
                }
                $notAllow = apply_filters('arm_currency_support', $notAllow, $currency);
                if (!empty($notAllow)) {
                    $message = __('This currency is not supported by', 'ARMember');
                    $message .= ' ' . implode(', ', $notAllow) . ' ';
                    $message .= __('payment gateway(s). If you will save this settings, than those payment gateway(s) will be disable.', 'ARMember');
                    $setups = $wpdb->get_results("SELECT `arm_setup_name`, `arm_setup_modules` FROM `" . $ARMember->tbl_arm_membership_setup . "` ORDER BY `arm_setup_id` DESC", ARRAY_A);
                    if (!empty($setups)) {
                        foreach ($setups as $setup) {
                            $setupModules = maybe_unserialize($setup['arm_setup_modules']);
                            if (isset($setupModules['modules']['gateways']) && !empty($setupModules['modules']['gateways'])) {
                                $diffPG = array_diff($setupModules['modules']['gateways'], $notAllow);
                                if (empty($diffPG) || count($diffPG) < 1) {
                                    $notAllowSetups[] = $setup['arm_setup_name'];
                                }
                            }
                        }
                        $notAllowSetups = $ARMember->arm_array_unique($notAllowSetups);
                    }
                    if (!empty($notAllowSetups)) {
                        $message .= ' ' . __('As well as following setup(s) will not work properly', 'ARMember');
                        $message .= ': ' . implode(', ', $notAllowSetups) . '.';
                    }
                }
                $response = array('type' => 'success', 'msg' => $message);
            }
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_check_currency_status') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_payment_gateways'], '1');
                echo json_encode($response);
                exit;
            }
            return $message;
        }

        function arm_check_currency_status_for_gateways($arm_currency = 'USD') {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_membership_setup, $arm_capabilities_global;
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $currency = (isset($_REQUEST['arm_currency']) && !empty($_REQUEST['arm_currency'])) ? sanitize_text_field($_REQUEST['arm_currency']) : $arm_currency;
            $message = '';
            $notAllow = $notAllowSetups = array();

            if (!empty($currency)) {
                if (!array_key_exists($currency, $this->currency['paypal'])) {
                    $notAllow[] = 'paypal';
                }
                if (!array_key_exists($currency, $this->currency['stripe'])) {
                    $notAllow[] = 'stripe';
                }
                if (!array_key_exists($currency, $this->currency['authorize_net'])) {
                    $notAllow[] = 'authorize_net';
                }
                if (!array_key_exists($currency, $this->currency['2checkout'])) {
                    $notAllow[] = '2checkout';
                }
                $notAllow = apply_filters('arm_currency_support', $notAllow, $currency);
                if (!empty($notAllow)) {
                    $message = __('This currency is not supported by', 'ARMember');
                    $message .= ' ' . implode(', ', $notAllow) . ' ';
                    $message .= __('payment gateway(s). If you will save this settings, than those payment gateway(s) will be disable.', 'ARMember');
                    $setups = $wpdb->get_results("SELECT `arm_setup_name`, `arm_setup_modules` FROM `" . $ARMember->tbl_arm_membership_setup . "` ORDER BY `arm_setup_id` DESC", ARRAY_A);
                    if (!empty($setups)) {
                        foreach ($setups as $setup) {
                            $setupModules = maybe_unserialize($setup['arm_setup_modules']);
                            if (isset($setupModules['modules']['gateways']) && !empty($setupModules['modules']['gateways'])) {
                                $diffPG = array_diff($setupModules['modules']['gateways'], $notAllow);
                                if (empty($diffPG) || count($diffPG) <= 1) {
                                    $notAllowSetups[] = $setup['arm_setup_name'];
                                }
                            }
                        }
                        $notAllowSetups = $ARMember->arm_array_unique($notAllowSetups);
                    }
                    if (!empty($notAllowSetups)) {
                        $message .= ' ' . __('As well as following setup(s) will not work properly', 'ARMember');
                        $message .= ': ' . implode(', ', $notAllowSetups) . '.';
                    }
                }
                $response = array('type' => 'success', 'msg' => $message);
            }
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_check_currency_status') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_payment_gateways'], '1');
                echo json_encode($response);
                exit;
            }
            return $notAllow;
        }

        function arm_paypal_language() {
            $currency_symbol = array(
                'ar_EG' => __('Arab/Egypt', 'ARMember'),
                'da_DK' => __('Danish/Denmark', 'ARMember'),
                'nl_NL' => __('Dutch/Netherlands', 'ARMember'),
                'zh_XC' => __('Chinese', 'ARMember'),
                'zh_CN' => __('Chinese/China', 'ARMember'),
                'zh_HK' => __('Chino/Hong Kong', 'ARMember'),
                'zh_TW' => __('Chinese/Taiwan', 'ARMember'),
                'en_AU' => __('English/Australia', 'ARMember'),
                'en_GB' => __('English/United Kingdom', 'ARMember'),
                'en_US' => __('English/United States', 'ARMember'),
                'es_XC' => __('Eskimo Aleut/Anguilla', 'ARMember'),
                'fr_CA' => __('Francais/Canada', 'ARMember'),
                'fr_XC' => __('French/Angola', 'ARMember'),
                'fr_FR' => __('French/France', 'ARMember'),
                'de_DE' => __('German/Germany', 'ARMember'),
                'he_IL' => __('Hebrew/Israel', 'ARMember'),
                'id_ID' => __('Indonesian/Indonesia', 'ARMember'),
                'it_IT' => __('Italian/Italy', 'ARMember'),
                'ja_JP' => __('Japanese/Japan', 'ARMember'),
                'ko_KR' => __('Korean/South Korea', 'ARMember'),
                'no_NO' => __('Norwegian/Norway', 'ARMember'),
                'pl_PL' => __('Polish/Poland', 'ARMember'),
                'pt_BR' => __('Portugues/Brasil', 'ARMember'),
                'pt_PT' => __('Portuguese/Portugal', 'ARMember'),
                'ru_RU' => __('Rusia/Rusia', 'ARMember'),
                'sk_SK' => __('Slovakia/Slovakia', 'ARMember'),
                'es_ES' => __('Spanish/Spain', 'ARMember'),
                'sv_SE' => __('Swedish/Sweden', 'ARMember'),
                'th_TH' => __('Thai/Thailand', 'ARMember'),
                
                
                
            );
            return $currency_symbol;
        }
        
        function arm_2checkout_language() {
            $currency_symbol = array(
                'zh' => __('Chinese', 'ARMember'),
                'da' => __('Danish', 'ARMember'),
                'nl' => __('Dutch', 'ARMember'),
                'en' => __('English', 'ARMember'),
                'fr' => __('French', 'ARMember'),
                'gr' => __('German', 'ARMember'),
                'el' => __('Greek', 'ARMember'),
                'it' => __('Italian', 'ARMember'),
                'jp' => __('Japanese', 'ARMember'),
                'no' => __('Norwegian', 'ARMember'),
                'pt' => __('Portuguese', 'ARMember'),
                'sl' => __('Slovenian', 'ARMember'),
                'es_ib' => __('Spanish-European', 'ARMember'),
                'es_la' => __('Spanish-Latin American', 'ARMember'),
                'sv' => __('Swedish', 'ARMember'),
                
            );
            return $currency_symbol;
        }
        
        function arm_amount_set_separator($arm_currency = 'USD', $arm_amount = '0', $is_coupon_amount = false, $get_currency_wise_seperator = false) {

            $arm_currency = strtoupper($arm_currency);
            $currency_separators = $this->get_other_seperator_currencies();
            
            $separator = $this->get_currency_separators_standard();
            $arm_main_amount = (float)str_replace(',', '', $arm_amount);

            if($get_currency_wise_seperator == true && !empty($currency_separators[$arm_currency])) {
                $separator = array();
                $separator[$arm_currency] = $currency_separators[$arm_currency];
                //$arm_main_amount = (float)str_replace($separator[$arm_currency]['thousand'], '', $arm_amount);
            }
            
            
            if(isset($separator[$arm_currency]['decimal']) && $separator[$arm_currency]['decimal'] != '')
            {
                return number_format((float) $arm_main_amount , 2, $separator[$arm_currency]['decimal'], $separator[$arm_currency]['thousand']);
            }
            else if( isset($separator[$arm_currency]['decimal']) && $separator[$arm_currency]['decimal'] == '')
            {
                
                
                $floar_amount = floor($arm_main_amount);
                $after_decimal_point_val = $arm_main_amount - $floar_amount;
                if($is_coupon_amount && $after_decimal_point_val == '0.50')
                {
                    return number_format((float) floor($arm_main_amount));
                }
                else
                {
                    return number_format((float) $arm_main_amount,0,'',$separator[$arm_currency]['thousand']);
                }
            }
            else
            {
                return number_format((float) $arm_main_amount , 2, '.', ',');
            }
            
        }

        function arm_get_bank_transfer_mode_options() {
            $transfer_mode = array(
                    'bank_transfer' => esc_html__('Digital Transfer', 'ARMember'), 
                    'cheque' => esc_html__('Cheque', 'ARMember'), 
                    'cash' => esc_html__('Cash', 'ARMember')
                    );
            return $transfer_mode;
        }

        function get_currency_separators_standard() {
            $separator = array( "USD"=> array( 'decimal' => '.', 'thousand' => ',' ), "AUD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "BRL"=> array( 'decimal' => '.', 'thousand' => ',' ), "CAD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "CZK"=> array( 'decimal' => '.', 'thousand' => ',' ), "DKK"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "EUR"=> array( 'decimal' => '.', 'thousand' => ',' ), "HKD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "ILS"=> array( 'decimal' => '.', 'thousand' => ',' ), "ZMW"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "MYR"=> array( 'decimal' => '.', 'thousand' => ',' ), "UZS"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "MXN"=> array( 'decimal' => '.', 'thousand' => ',' ), "NOK"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "NZD"=> array( 'decimal' => '.', 'thousand' => ',' ), "PHP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "PLN"=> array( 'decimal' => '.', 'thousand' => ',' ), "GBP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "RUB"=> array( 'decimal' => '.', 'thousand' => ',' ), "SGD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "SEK"=> array( 'decimal' => '.', 'thousand' => ',' ), "CHF"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "THB"=> array( 'decimal' => '.', 'thousand' => ',' ), "UGX"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "TRY"=> array( 'decimal' => '.', 'thousand' => ',' ), "AFN"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "ALL"=> array( 'decimal' => '.', 'thousand' => ',' ), "ARS"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "AZN"=> array( 'decimal' => '.', 'thousand' => ',' ), "DZD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "BSD"=> array( 'decimal' => '.', 'thousand' => ',' ), "BDT"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "BBD"=> array( 'decimal' => '.', 'thousand' => ',' ), "BZD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "BMD"=> array( 'decimal' => '.', 'thousand' => ',' ), "BOB"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "BWP"=> array( 'decimal' => '.', 'thousand' => ',' ), "BND"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "BGN"=> array( 'decimal' => '.', 'thousand' => ',' ), "SHP"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "CNY"=> array( 'decimal' => '.', 'thousand' => ',' ), "COP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "CRC"=> array( 'decimal' => '.', 'thousand' => ',' ), "HRK"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "DOP"=> array( 'decimal' => '.', 'thousand' => ',' ), "XCD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "EGP"=> array( 'decimal' => '.', 'thousand' => ',' ), "FJD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "GTQ"=> array( 'decimal' => '.', 'thousand' => ',' ), "HNL"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "INR"=> array( 'decimal' => '.', 'thousand' => ',' ), "IDR"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "JMD"=> array( 'decimal' => '.', 'thousand' => ',' ), "KZT"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "KES"=> array( 'decimal' => '.', 'thousand' => ',' ), "LAK"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "MMK"=> array( 'decimal' => '.', 'thousand' => ',' ), "LBP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "LRD"=> array( 'decimal' => '.', 'thousand' => ',' ), "MOP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "MVR"=> array( 'decimal' => '.', 'thousand' => ',' ), "MRO"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "MUR"=> array( 'decimal' => '.', 'thousand' => ',' ), "MAD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "NPR"=> array( 'decimal' => '.', 'thousand' => ',' ), "NIO"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "PKR"=> array( 'decimal' => '.', 'thousand' => ',' ), "PGK"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "PEN"=> array( 'decimal' => '.', 'thousand' => ',' ), "QAR"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "RON"=> array( 'decimal' => '.', 'thousand' => ',' ), "WST"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "SAR"=> array( 'decimal' => '.', 'thousand' => ',' ), "SCR"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "SGF"=> array( 'decimal' => '.', 'thousand' => ',' ), "SBD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "ZAR"=> array( 'decimal' => '.', 'thousand' => ',' ), "MKD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "LKR"=> array( 'decimal' => '.', 'thousand' => ',' ), "SYP"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "TOP"=> array( 'decimal' => '.', 'thousand' => ',' ), "TTD"=> array( 'decimal' => '.', 'thousand' => ',' ), 
                                "UAH"=> array( 'decimal' => '.', 'thousand' => ',' ), "AED"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "YER"=> array( 'decimal' => '.', 'thousand' => ',' ), "ETB"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "AMD"=> array( 'decimal' => '.', 'thousand' => ',' ), "ANG"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "AOA"=> array( 'decimal' => '.', 'thousand' => ',' ), "AWG"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "CDF"=> array( 'decimal' => '.', 'thousand' => ',' ), "CVE"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "FKP"=> array( 'decimal' => '.', 'thousand' => ',' ), "GEL"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "GIP"=> array( 'decimal' => '.', 'thousand' => ',' ), "GMD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "GYD"=> array( 'decimal' => '.', 'thousand' => ',' ), "KYD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "HTG"=> array( 'decimal' => '.', 'thousand' => ',' ), "ISK"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "KGS"=> array( 'decimal' => '.', 'thousand' => ',' ), "KHR"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "LSL"=> array( 'decimal' => '.', 'thousand' => ',' ), "MDL"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "MNT"=> array( 'decimal' => '.', 'thousand' => ',' ), "MWK"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "MZN"=> array( 'decimal' => '.', 'thousand' => ',' ), "NAD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "NGN"=> array( 'decimal' => '.', 'thousand' => ',' ), "PAB"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "RSD"=> array( 'decimal' => '.', 'thousand' => ',' ), "UYU"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "TJS"=> array( 'decimal' => '.', 'thousand' => ',' ), "SLL"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "SOS"=> array( 'decimal' => '.', 'thousand' => ',' ), "SRD"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "STD"=> array( 'decimal' => '.', 'thousand' => ',' ), "SVC"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "SZL"=> array( 'decimal' => '.', 'thousand' => ',' ), "TZS"=> array( 'decimal' => '.', 'thousand' => ',' ),
                                "BAM"=> array( 'decimal' => '.', 'thousand' => ',' ), "BIF"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "KRW"=> array( 'decimal' => '', 'thousand' => ',' ), "MGA"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "RWF"=> array( 'decimal' => '', 'thousand' => ',' ), "CLP"=> array( 'decimal' => '', 'thousand' => ',' ), 
                                "XAF"=> array( 'decimal' => '', 'thousand' => ',' ), "TWD"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "XPF"=> array( 'decimal' => '', 'thousand' => ',' ), "JPY"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "HUF"=> array( 'decimal' => '', 'thousand' => ',' ), "PYG"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "VUV"=> array( 'decimal' => '', 'thousand' => ',' ), "VND"=> array( 'decimal' => '', 'thousand' => ',' ), 
                                "XOF"=> array( 'decimal' => '', 'thousand' => ',' ), "DJF"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "GNF"=> array( 'decimal' => '', 'thousand' => ',' ), "KMF"=> array( 'decimal' => '', 'thousand' => ',' ),
                                "GHS"=> array( 'decimal' => '.','thousand' => ',' ),"BHD"=> array( 'decimal' => '.', 'thousand' => ','),
                                "BTC" => array('decimal' => '.','thousand' => ','), "BTN"=> array( 'decimal' => '.', 'thousand' => ','),
                                "BYR" => array('decimal' => '.','thousand' => ','),"BYN" => array( 'decimal' => '.', 'thousand' => ','),
                                "CUC" => array('decimal' => '.','thousand' => ','),"CUP" => array( 'decimal' => '.', 'thousand' => ','),
                                "ERN" => array('decimal' => '.','thousand' => ','), "GGP"=> array( 'decimal' => '.', 'thousand' => ','),
                                "IMP" => array('decimal' => '.','thousand' => ','), "IQD" => array( 'decimal' => '.', 'thousand' => ','),
                                "IRR" => array('decimal' => '.','thousand' => ','), "IRT" => array( 'decimal' => '.', 'thousand' => ','),
                                "JEP" => array('decimal' => '.','thousand' => ','), "JOD" => array( 'decimal' => '.', 'thousand' => ','),
                                "KPW" => array('decimal' => '.','thousand' => ','), "KWD" => array( 'decimal' => '.', 'thousand' => ','),
                                "LYD" => array('decimal' => '.','thousand' => ','), "MRU" => array( 'decimal' => '.', 'thousand' => ','),
                                "OMR" => array('decimal' => '.','thousand' => ','), "PRB" => array( 'decimal' => '.', 'thousand' => ','),
                                "RMB" => array('decimal' => '.','thousand' => ','), "SDG" => array( 'decimal' => '.', 'thousand' => ','),
                                "SSP" => array('decimal' => '.','thousand' => ','), "STN" => array( 'decimal' => '.', 'thousand' => ','),
                                "TMT" => array('decimal' => '.','thousand' => ','), "TND" => array( 'decimal' => '.', 'thousand' => ','),
                                "VEF" => array('decimal' => '.','thousand' => ','), "VES" => array( 'decimal' => '.', 'thousand' => ','),
                );
            return $separator;
        }

        function get_currency_wise_separator($arm_currency) {
            $separators = array();
            $arm_currency = strtoupper($arm_currency);
            $currency_separators = $this->get_other_seperator_currencies();

            if(!empty($arm_currency) && !empty($currency_separators[$arm_currency])) {
                $separators = $currency_separators[$arm_currency];
            }
            return $separators;
        }

        function get_other_seperator_currencies() {
            $currency_separators = array( "BRL" => array( 'decimal' => ',', 'thousand' => '.' ),
                                        "BYR" => array('decimal' => ',', 'thousand' => ''),
                                        "BYN" => array('decimal' => ',', 'thousand' => ''),
                                        "CUC" => array('decimal' => ',', 'thousand' => '.'),
                                        "CUP" => array('decimal' => ',', 'thousand' => '.'),
                                        "TMT" => array('decimal' => ',', 'thousand' => ''),
                                        "TND" => array('decimal' => ',', 'thousand' => '.'),
                                        "VEF" => array('decimal' => ',', 'thousand' => '.'), 
                                        "VES" => array('decimal' => ',', 'thousand' => '.'),
                );
            return $currency_separators;
        }
    }

}
global $arm_payment_gateways;
$arm_payment_gateways = new ARM_payment_gateways();
