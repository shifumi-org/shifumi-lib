<?php
if (!class_exists('ARM_Report_Analytics')) 
{
	class ARM_Report_Analytics 
	{
		public function __construct()
		{
			add_action('wp_ajax_armupdatecharts', array($this, "armupdatecharts"));
			add_action('wp_ajax_armupdatereportgrid', array( $this, 'arf_update_report_grid_data'));
			//add_action('admin_footer', array( $this, 'arm_set_reports_submenu') );
			add_action('wp_ajax_arm_login_history_page_search_action', array($this, 'arm_all_user_login_history_page_paging_action'));
			add_action('wp_ajax_arm_all_user_login_history_page_paging_action', array($this, 'arm_all_user_login_history_page_paging_action'));
		}

		public function armupdatecharts()
		{
			global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_report_analytics'], '1');

			$type = $_POST['type'];
		    $graph_type = $_POST['graph_type'];
		    $is_export_to_csv = isset($_POST['is_export_to_csv']) ? $_POST['is_export_to_csv'] : false;
		    $is_pagination = false;
		    require_once(MEMBERSHIP_VIEWS_DIR . '/arm_graph_ajax.php');

		    die();
		}

		public function arf_update_report_grid_data()
		{
			global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_report_analytics'], '1');
			
			$type = $_POST['type'];
		    $graph_type = $_POST['graph_type'];
		    $is_pagination = true;
		    require_once(MEMBERSHIP_VIEWS_DIR . '/arm_graph_ajax.php');

		    die();
		}

		public function arm_set_reports_submenu()
		{
			global $arm_slugs, $arm_pay_per_post_feature;
			$member_url = admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=member_report');
			$payment_url = admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=payment_report');
			$login_history_url = admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=login_history');
			$pay_per_post_url = admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=pay_per_post_report');			
			$coupon_url = admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=coupon_report');

			$member_page = $payment_page = $login_history_page = $pay_per_post_page = $coupon_page='';

			if( isset( $_GET['action'] ) && $_GET['action'] == 'member_report' ){
				$member_page = ' arm-current-menu ';
			} else if( isset( $_GET['action'] ) && $_GET['action'] == 'payment_report' ){
				$payment_page = ' arm-current-menu ';
			} else if( isset( $_GET['action'] ) && $_GET['action'] == 'login_history' ){
				$login_history_page = ' arm-current-menu ';
			} else if( isset( $_GET['action'] ) && $_GET['action'] == 'pay_per_post_report' ){
				$pay_per_post_page = ' arm-current-menu ';
			} else if( isset( $_GET['action'] ) && $_GET['action'] == 'coupon_report' ){
				$coupon_page = ' arm-current-menu ';
			}

			$current_color = get_user_option( 'admin_color' );			

			$script = "<script>";
				$script .= "jQuery(document).ready(function(){";
					$script .= "var parent = jQuery('.arm-submenu-item.arm_report_analytics');";					
					$script .= "var child1 = jQuery('<li class=\"arm-submenu-item ".$member_page." arm_member_report_analytics\"><a href=\"".$member_url."\">".esc_html__('Membership Reports', 'ARMember')."</a></li>');";
					$script .= "var child2 = jQuery('<li class=\"arm-submenu-item ".$payment_page." arm_member_report_analytics\"><a href=\"".$payment_url."\">".esc_html__('Payments Reports', 'ARMember')."</a></li>');";
					$script .= "var child3 = jQuery('<li class=\"arm-submenu-item ".$login_history_page." arm_member_report_analytics arm_member_login_report_analytics\"><a href=\"".$login_history_url."\">".esc_html__('Login Reports', 'ARMember')."</a></li>');";
					$script .= "var child4 = jQuery('<li class=\"arm-submenu-item ".$pay_per_post_page." arm_manage_pay_per_post arm_member_report_analytics\"><a href=\"".$pay_per_post_url."\">".esc_html__('Paid Post Reports', 'ARMember')."</a></li>');";
					$script .= "var child5 = jQuery('<li class=\"arm-submenu-item ".$coupon_page."  arm_member_report_analytics\"><a href=\"".$coupon_url."\">".esc_html__('Coupon Reports', 'ARMember')."</a></li>');";

					$script .= "var child_wrapper = jQuery('<ul class=\"wp-submenu wp-submenu-wrap arm-submenu-wrapper\"></ul>');";
					$script .= "parent.append( child_wrapper );";
					$script .= "child_wrapper.append( child1 );";
					$script .= "child_wrapper.append( child2 );";
					$script .= "child_wrapper.append( child3 );";
					$script .= "child_wrapper.append( child4 );";
					$script .= "child_wrapper.append( child5 );";
				$script .= "});";				
			$script .= "</script>";

			$hover_color = "#00b9eb";
			$normal_color = "rgba(240,245,250,.7)";
			$active_color = "#ffffff";		

			if( !isset( $current_color ) || $current_color == '' ){
				$current_color = 'fresh';
			}

			if( 'fresh' == $current_color ){
				$hover_color = "#00b9eb";
				$normal_color = "rgba(240,245,250,.7)";
				$active_color = "#ffffff";		
			} else if( 'light' == $current_color ){
				$normal_color = '#686868';
				$hover_color = "#04a4cc";
				$active_color = "#333";
			} else if( 'blue' == $current_color ){
				$normal_color = '#e2ecf1';
				$hover_color = "#fff";
				$active_color = "#fff";
			} else if( 'coffee' == $current_color ){
				$normal_color = '#cdcbc9';
				$hover_color = "#c7a589";
				$active_color = "#fff";
			} else if( 'ectoplasm' == $current_color ){
				$normal_color = '#cbc5d3';
				$hover_color = "#a3b745";
				$active_color = "#fff";
			} else if( 'midnight' == $current_color ){
				$normal_color = '#c3c4c5';
				$hover_color = "#e14d43";
				$active_color = "#fff";
			} else if( 'ocean' == $current_color ){
				$normal_color = '#d5dde0';
				$hover_color = "#9ebaa0";
				$active_color = "#fff";
			} else if( 'sunrise' == $current_color ){
				$normal_color = '#f1c8c7';
				$hover_color = "#f7e3d3";
				$active_color = "#fff";
			}

			$script .= "<style type='text/css'>";
				$script .= ".arm-submenu-item.arm_report_analytics{position:relative !important;}";
				$script .= "li.toplevel_page_arm_manage_members.wp-not-current-submenu .arm-submenu-item.arm_report_analytics ul.arm-submenu-wrapper{display:none !important;}";
				$script .= "li.toplevel_page_arm_manage_members.wp-menu-open .arm-submenu-item.arm_report_analytics ul.arm-submenu-wrapper{display:block !important;}";

				$script .= "li.toplevel_page_arm_manage_members.opensub .arm-submenu-item.arm_report_analytics:hover ul.arm-submenu-wrapper{display:block !important;margin-top:0 !important;}";
				$script .= ".arm-submenu-wrapper li a{padding-left: 25px !important; font-weight:normal !important;color:{$normal_color} !important;}";
				$script .= ".arm-submenu-wrapper li a:hover{ color:{$hover_color} !important; }";
				$script .= ".arm-submenu-wrapper li.arm-current-menu a{ color: {$active_color} !important; font-weight:600 !important; }";
				$script .= ".arm-submenu-wrapper li.arm-current-menu a:hover{ color: {$hover_color} !important; font-weight:600 !important; }";
			$script .= "</style>";

			echo $script;
		}

		function arm_get_all_user_for_login_history_page($current_page = 1, $perPage = 10, $post_data = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_default_user_details_text;
            $arm_all_block_settings = $arm_global_settings->block_settings;
            
            $arm_log_history_search_user = !empty($post_data['arm_login_history_page_search_user']) ? $post_data['arm_login_history_page_search_user'] : '';
            $arm_failed_login_filter = !empty($post_data['arm_failed_login_filter']) ? $post_data['arm_failed_login_filter'] : '';
            $historyHtml = '';

            $perPage = (!empty($perPage) && is_numeric($perPage)) ? $perPage : 10;
            $offset = 0;
            $current_time = current_time('timestamp');
            if (is_multisite()) {
                $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
            } else {
                $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
            }
            if (!empty($current_page) && $current_page > 1) {
                $offset = ($current_page - 1) * $perPage;
            }
            
            if(!empty($arm_failed_login_filter) && $arm_failed_login_filter == 'fail_login_history') {
            	$historyRecords = $this->arm_get_login_history_data($arm_failed_login_filter, $arm_log_history_search_user, $perPage, $offset);
            	
	            $historyHtml .= '<div class="arm_all_loginhistory_wrapper">';
	            $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table arm_member_login_history_data_table" width="100%">';
				$historyHtml .= '<thead>';
	            $historyHtml .= '<tr>';
	            $historyHtml .= '<td>' . __('Member', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Failed Attempt Date', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Failed Login IP', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Browser Name', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Country', 'ARMember') . '</td>';
	            //$historyHtml .= '<td>' . __('Logged Out Date', 'ARMember') . '</td>';
	            $historyHtml .= '</tr>';
				$historyHtml .= '</thead>';
	            if (!empty($historyRecords['records'])) {
	                $i = 0;
	                foreach ($historyRecords['records'] as $mh) {
	                    $i++;
	                    $arm_failed_login_date = "-";
	                    if (!empty($mh['arm_fail_attempts_datetime']) && $mh['arm_fail_attempts_datetime'] != "0000-00-00 00:00:00") {
	                        $arm_failed_login_date = date_i18n($wp_date_time_format, strtotime($mh['arm_fail_attempts_datetime']));
	                    } 

	                    $arm_country = "-";
	                    if(!empty($mh['arm_fail_attempts_ip'])) {
	                    	$arm_country = $ARMember->arm_get_country_from_ip($mh['arm_fail_attempts_ip']);
	                    }

	                    $arm_browser = "-";
	                    if(!empty($mh['arm_fail_attempts_detail'])) {
	                    	$browse_info = maybe_unserialize($mh['arm_fail_attempts_detail']);
	                    	if(!empty($browse_info['server']['HTTP_USER_AGENT'])) {
	                    		$browse_info = $ARMember->getBrowser($browse_info['server']['HTTP_USER_AGENT']);
	                    		$arm_browser = $browse_info['name']." (".$browse_info['version'].")";
	                    	}
	                    }

	                    $userlogin = (!empty($mh['user_login'])) ? "<a class='arm_openpreview_popup' href='javascript:void(0)' data-id='".$mh['ID']."'>".$mh['user_login']."</a>" : $arm_default_user_details_text;

	                    $historyHtml .= '<tr class="arm_member_last_subscriptions_data all_user_login_history_tr">';
	                    $historyHtml .= '<td>' . $userlogin . '</td>';
	                    $historyHtml .= '<td>' . $arm_failed_login_date . '</td>';
	                    $historyHtml .= '<td>' . $mh['arm_fail_attempts_ip'] . '</td>';
	                    $historyHtml .= '<td>' . $arm_browser . '</td>';
	                    $historyHtml .= '<td>' . $arm_country . '</td>';
	                    //$historyHtml .= '<td>' . $arm_logged_out_date . '</td>';
	                    $historyHtml .= '</tr>';
	                }
	            } else {
	                $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
	                $historyHtml .= '<td colspan="5" class="arm_text_align_center">' . __('No Failed Login History Found.', 'ARMember') . '</td>';
	                $historyHtml .= '</tr>';
	            }

	            $historyHtml .= '</table>';
	            $historyHtml .= '<div class="arm_membership_history_pagination_block">';
	            $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $historyRecords['record_count'], $perPage, '');
	            $historyHtml .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
	            $historyHtml .= '</div>';
	            $historyHtml .= '</div>';

            } else {
            	$historyRecords = $this->arm_get_login_history_data($arm_failed_login_filter, $arm_log_history_search_user, $perPage, $offset);
            	
	            $historyHtml .= '<div class="arm_all_loginhistory_wrapper">';
	            $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table arm_member_login_history_data_table" width="100%">';
				$historyHtml .= '<thead>';
	            $historyHtml .= '<tr>';
	            $historyHtml .= '<td>' . __('Member', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Logged In Date', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Logged In IP', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Browser Name', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Country', 'ARMember') . '</td>';
	            $historyHtml .= '<td>' . __('Logged Out Date', 'ARMember') . '</td>';
	            $historyHtml .= '</tr>';
				$historyHtml .= '</thead>';
	            if (!empty($historyRecords['records'])) {
	                $i = 0;
	                foreach ($historyRecords['records'] as $mh) {
	                    $i++;
	                    $logout_date = date_create($mh['arm_logout_date']);
	                    $login_date = date_create($mh['arm_logged_in_date']);
	                    if (isset($mh['arm_user_current_status']) && $mh['arm_user_current_status'] == 1 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
	                        $arm_logged_out_date = __('Currently Logged In', 'ARMember');
	                    } else {
	                        if ($mh['arm_user_current_status'] == 0 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
	                            $arm_logged_out_date = "-";
	                        } else {
	                        	$arm_login_date = strtotime($mh['arm_logged_in_date']);
	                        	$arm_logout_date = strtotime($mh['arm_logout_date']);
	                        	$arm_time_difference = $current_time - $arm_logout_date;
	                        	if(($arm_time_difference<300 && $mh['arm_user_current_status'] == 1) || ($mh['arm_user_current_status']==1 && $arm_login_date==$arm_logout_date) )
	                        	{
	                            	$arm_logged_out_date = __('Currently Logged In', 'ARMember');
	                            }
	                            else {
	                            	$arm_logged_out_date = date_i18n($wp_date_time_format, $arm_logout_date);
	                            }
	                        }
	                    }
	                    $userlogin = (!empty($mh['user_login'])) ? "<a class='arm_openpreview_popup' href='javascript:void(0)' data-id='".$mh['ID']."'>".$mh['user_login']."</a>" : $arm_default_user_details_text;
	                    $historyHtml .= '<tr class="arm_member_last_subscriptions_data all_user_login_history_tr">';
	                    $historyHtml .= '<td>' . $userlogin . '</td>';
	                    $historyHtml .= '<td>' . date_i18n($wp_date_time_format, strtotime($mh['arm_logged_in_date'])) . '</td>';
	                    $historyHtml .= '<td>' . $mh['arm_logged_in_ip'] . '</td>';
	                    $historyHtml .= '<td>' . $mh['arm_history_browser'] . '</td>';
	                    $historyHtml .= '<td>' . $mh['arm_login_country'] . '</td>';
	                    $historyHtml .= '<td>' . $arm_logged_out_date . '</td>';
	                    $historyHtml .= '</tr>';
	                }
	            } else {
	                $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
	                $historyHtml .= '<td colspan="6" class="arm_text_align_center">' . __('No Login History Found.', 'ARMember') . '</td>';
	                $historyHtml .= '</tr>';
	            }

	            $historyHtml .= '</table>';
	            $historyHtml .= '<div class="arm_membership_history_pagination_block">';
	            $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $historyRecords['record_count'], $perPage, '');
	            $historyHtml .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
	            $historyHtml .= '</div>';
	            $historyHtml .= '</div>';
            }
            return $historyHtml;
        }

        function arm_all_user_login_history_page_paging_action() 
        {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways,$arm_capabilities_global;
            if (isset($_POST['action']) && ($_POST['action'] == 'arm_all_user_login_history_page_paging_action' || $_POST['action'] == 'arm_login_history_page_search_action')) 
            {
                $current_page = isset($_POST['page']) ? $_POST['page'] : 1;
                $per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 10;
                
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_report_analytics'], '1');
                echo $this->arm_get_all_user_for_login_history_page($current_page, $per_page, $_POST);
            }
            exit;
        }

        function arm_get_login_history_data($login_history_type="login_history", $arm_log_history_search_user="", $perPage="", $offset="") {
        	global $wpdb, $ARMember;
        	$user_table = $wpdb->users;
        	$data = array();
        	if($login_history_type == "fail_login_history") {
        		$history_where = "";
	            if(!empty($arm_log_history_search_user))
	            {
	               $history_where .= ' AND u.user_login LIKE "%'.$arm_log_history_search_user.'%" ';
	            }
        		$historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";
        		$historyRecords1 = "SELECT u.ID, u.user_login, l.arm_user_id, l.arm_fail_attempts_ip, l.arm_fail_attempts_datetime, l.arm_fail_attempts_detail FROM `{$user_table}` u INNER JOIN `" . $ARMember->tbl_arm_fail_attempts . "` l ON u.ID = l.arm_user_id where 1 = 1  $history_where ORDER BY l.arm_fail_attempts_id DESC ";

            	$historyRecords2 = $historyRecords1." {$historyLimit}";

            	$historyRecords =  $wpdb->get_results($historyRecords2, ARRAY_A);
	            
	            $totalRecord = $wpdb->get_results($historyRecords1);
            	$totalRecord = count($totalRecord);
            	
            	$data['records'] = $historyRecords;
            	$data['record_count'] = $totalRecord;

            } else {
            	$history_where = "";
	            if(!empty($arm_log_history_search_user))
	            {
	               $history_where .= ' AND u.user_login LIKE "%'.$arm_log_history_search_user.'%" ';
	            }
            	$historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";
            	$historyRecords1 = "SELECT u.ID, u.user_login, l.arm_user_current_status, l.arm_user_id,l.arm_logged_in_ip, l.arm_logged_in_date, l.arm_logout_date, l.arm_history_browser, l.arm_login_country FROM `{$user_table}` u INNER JOIN `" . $ARMember->tbl_arm_login_history . "` l ON u.ID = l.arm_user_id where 1 = 1  $history_where ORDER BY l.arm_history_id DESC ";

	            $historyRecords2 = $historyRecords1." {$historyLimit}";

            	$historyRecords =  $wpdb->get_results($historyRecords2, ARRAY_A);
	            
	            $totalRecord = $wpdb->get_results($historyRecords1);
            	$totalRecord = count($totalRecord);

            	$data['records'] = $historyRecords;
            	$data['record_count'] = $totalRecord;

            }
            return $data;
        }

        function arm_all_user_login_history_page_export_func($post_data=array()) {
        	$login_history_type = isset($post_data['arm_login_history_type']) ? $post_data['arm_login_history_type'] : '';
        	$arm_log_history_search_user = isset($post_data['arm_log_history_search_user']) ? $post_data['arm_log_history_search_user'] : '';
        	$perPage=''; $offset='';

        	$history_data = $this->arm_get_login_history_data($login_history_type, $arm_log_history_search_user, $perPage, $offset);
        	if(!empty($history_data['records'])) {
        		$this->arm_export_login_history_to_csv($history_data['records'], $login_history_type);	
        	}
        	
        }

        function arm_export_login_history_to_csv($historyRecords=array(), $login_history_type="login_history") 
        {
        	if(!empty($historyRecords)) {
        		global $ARMember;
		        $arm_history_tmp = array();
		        $final_log = array();
		        $wp_date_time_format = "";
		        $export_report_type = "";
		        if (is_multisite()) {
		            $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
		        } else {
		            $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
		        }

		        if($login_history_type == 'fail_login_history') {   
		            $export_report_type = "-failed-login-history";
		            $arm_history_tmp = array (
		                "Member" => '',
		                "Failed_Attempt_Date" => '',
		                "Failed_Login_IP" => '',
		                "Browser_Name" => '',
		                "Country" => ''
		            );
		            
		            foreach ($historyRecords as $row) {
		                $arm_failed_login_date = "-";
	                    if (!empty($row['arm_fail_attempts_datetime']) && $row['arm_fail_attempts_datetime'] != "0000-00-00 00:00:00") {
	                        $arm_failed_login_date = date_i18n($wp_date_time_format, strtotime($row['arm_fail_attempts_datetime']));
	                    } 

	                    $arm_country = "-";
	                    if(!empty($row['arm_fail_attempts_ip'])) {
	                    	$arm_country = $ARMember->arm_get_country_from_ip($row['arm_fail_attempts_ip']);
	                    }

	                    $arm_browser = "-";
	                    if(!empty($row['arm_fail_attempts_detail'])) {
	                    	$browse_info = maybe_unserialize($row['arm_fail_attempts_detail']);
	                    	if(!empty($browse_info['server']['HTTP_USER_AGENT'])) {
	                    		$browse_info = $ARMember->getBrowser($browse_info['server']['HTTP_USER_AGENT']);
	                    		$arm_browser = $browse_info['name']." (".$browse_info['version'].")";
	                    	}
	                    }

		                $tmp["Member"] = $row['user_login'];
		                $tmp["Failed_Attempt_Date"] = $arm_failed_login_date;
		                $tmp["Failed_Login_IP"] = $row['arm_fail_attempts_ip'];
		                $tmp["Browser_Name"] = $arm_browser;
		                $tmp["Country"] = $arm_country;
		                array_push($final_log, $tmp);
		            }
		        } else if($login_history_type == 'login_history') {
		            $export_report_type = "-login-history";
		            $arm_history_tmp = array (
		                "Username" => '',
		                "Logged_In_Date" => '',
		                "Logged_In_IP" => '',
		                "Browser_Name" => '',
		                "Country" => '',
		                "Logged_Out_Date" => ''
		            );
		            
		            foreach ($historyRecords as $row) {
		              	$loggedin_date = date_i18n($wp_date_time_format, strtotime($row['arm_logged_in_date']));
		              	$loggedout_date = date_i18n($wp_date_time_format, strtotime($row['arm_logged_in_date']));
		                $arm_registered_date = date_i18n($wp_date_time_format, strtotime($row['user_registered']));
		                $tmp["Username"] = $row['user_login'];
		                $tmp["Logged_In_Date"] = $loggedin_date;
		                $tmp["Logged_In_IP"] = $row['arm_logged_in_ip'];
		                $tmp["Browser_Name"] = $row['arm_history_browser'];
		                $tmp["Country"] = $row['arm_login_country'];
		                $tmp["Logged_Out_Date"] = $loggedout_date;
		                array_push($final_log, $tmp);
		            }
		        }

		        ob_clean();
		        ob_start();
		        $now = gmdate("D, d M Y H:i:s");
		        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		        header("Last-Modified: {$now} GMT");
		        header("Content-Type: application/force-download");
		        header("Content-Type: application/octet-stream");
		        header("Content-Type: application/download");
		        header("Content-Disposition: attachment;filename=ARMember-export".$export_report_type.".csv");
		        header("Content-Transfer-Encoding: binary");
		        $df = fopen("php://output", 'w');
		        fputcsv($df, array_keys($arm_history_tmp));
		        if(!empty($final_log)) {
		            foreach ($final_log as $row) {
		                fputcsv($df, $row);
		            }
		        }
		        fclose($df);
		        exit;
		    }
        }

	    function arm_makeDayArray($startDate, $endDate)
	    {
	        $startDate = strtotime($startDate);
	        $endDate   = strtotime($endDate);
	        
	        $currDate  = $startDate;
	        $dayArray  = array();
	        
	        do {
	            $dayArray[] = date('Y-m-d', $currDate);
	            $currDate = strtotime('+1 day', $currDate);
	        } while ($currDate <= $endDate);
	        return $dayArray;
	    }

	    function arm_export_report_to_csv($historyRecords = array(), $report_type = 'member_report') 
	    {	
	    	global $arm_global_settings,$ARMember;	
	    	$date_format = $arm_global_settings->arm_get_wp_date_format();
	    	
	        if(!empty($historyRecords)) 
	        {
	        	global $arm_subscription_plans, $arm_default_user_details_text,$arm_global_settings;

	            $arm_history_tmp = array();
	            $final_log = array();
	            $wp_date_time_format = "";
	            $export_report_type = "";
	            if (is_multisite()) {
	                $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
	            } else {
	                $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
	            }

	            if($report_type == 'payment_report') {   
	                $export_report_type = "-payment-report";
	                $arm_history_tmp = array (
	                    "Invoice_ID" => '',
	                    "Member" => '',
	                    "Paid_By" => '',
	                    "Plan" => '',
	                    "Paid Amount" => '',
	                    "Payment_Gateway" => '',
	                    "Payment_Date" => ''
	                );
	                
					$arm_plan_default_arr = array();
	                foreach ($historyRecords as $row) {
	                    $payment_gateway = ucfirst( str_replace('_', ' ', $row['arm_payment_gateway'] ) );
	                    $arm_created_date = date_i18n($wp_date_time_format, strtotime($row['arm_created_date']));
	                    $user_login = !empty($row['user_login']) ? $row['user_login'] : $arm_default_user_details_text ;
	                    $paid_by = !empty($row['arm_payer_email']) ? $row['arm_payer_email'] : '-' ;
					    if(!empty($arm_plan_default_arr[$row['arm_plan_id']]))
					    {
					    	$plan_name = $arm_plan_default_arr[$row['arm_plan_id']];
					    }
					    else {
		                        	$plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($row['arm_plan_id']);       
						$plan_name = !empty($plan_name) ? $plan_name : '-' ;
						$arm_plan_default_arr[$row['arm_plan_id']] = $plan_name;
					    }
            		    $invoice_id = $arm_global_settings->arm_manipulate_invoice_id($row['arm_invoice_id']);		
	                    $invoice_id = !empty($invoice_id) ? $invoice_id : '-' ; 

	                    $tmp["Invoice_ID"] = $invoice_id;
	                    $tmp["Member"] = $user_login;
	                    $tmp["Paid_By"] = $paid_by;
	                    $tmp["Plan"] = $plan_name;
	                    $tmp["Paid Amount"] = number_format($row['arm_amount'],2)." ".$row['arm_currency'];
	                    $tmp["Payment_Gateway"] = $payment_gateway;
	                    $tmp["Payment_Date"] = $arm_created_date;
	                    array_push($final_log, $tmp);
	                }
	            } else if($report_type == 'member_report') {
	                $export_report_type = "-member-report";
	                $arm_history_tmp = array (
	                    "Member" => '',
	                    "Email" => '',
	                    "Plan" => '',
	                    "Next_Recurring_Date" => '',
	                    "Plan_Expire_Plan_Date" =>'',	                    
	                    "Join_Date" => '',	                    
	                );
	                
	                $arm_plan_default_arr = array();
	                foreach ($historyRecords as $row) {
	                    $plan_arr = get_user_meta($row['ID'], "arm_user_plan_ids", true);

	                    $arm_gift_ids = get_user_meta($row['ID'], 'arm_user_gift_ids', true);
		                if(!empty($arm_gift_ids))
		                {
		                    foreach($plan_arr as $arm_plan_key => $arm_plan_val)
		                    {
		                        if(in_array($arm_plan_val, $arm_gift_ids))
		                        {
		                            unset($plan_arr[$arm_plan_key]);
		                        }
		                    }
		                }
		                
	                    $postIDs = get_user_meta($row['ID'], "arm_user_post_ids", true);

	                    $plan_name = "";
	                    $arm_expire_date = "";
	                    $arm_next_recurring_date = "";

	                    if(!empty($plan_arr)) {

	                        foreach ($plan_arr as $key => $plan) {
	                        	if(empty($postIDs[$plan])) {
	                        		if(!empty($arm_plan_default_arr[$plan])) {
	                        			$plan_name .= $arm_plan_default_arr[$plan];
	                        		} else {
	                        			$arm_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan);
	                        			$plan_name .= !empty($arm_plan_name) ? $arm_plan_name : '-' ;
	                        			$arm_plan_default_arr[$plan] = $arm_plan_name;
	                        		}

		                            $plan_name .= ", ";

		                            $plan_data=get_user_meta($row['ID'], "arm_user_plan_".$plan, true);               
			                        $arm_expire = !empty($plan_data['arm_expire_plan']) ? $plan_data['arm_expire_plan'] : '';
			                        $arm_next_recurring = !empty($plan_data['arm_next_due_payment']) ? $plan_data['arm_next_due_payment'] : '';

			                        if(!empty($arm_expire)) {
			                            $arm_expire_date.= date_i18n($date_format,$arm_expire);
			                            $arm_expire_date .= ", ";  
			                        } else {
			                            $arm_expire_date.= __('Never Expire', 'ARMember');
			                               $arm_expire_date .= ", ";  
			                        }                    
			                        if(!empty($arm_next_recurring)) {
			                           $arm_next_recurring_date.=  date_i18n($date_format,$arm_next_recurring);
			                           $arm_next_recurring_date.= ", ";
			                        } else {                            
			                           $arm_next_recurring_date.=  "-";
			                           $arm_next_recurring_date.= ", ";
			                        }		                                                    	

	                        	}
	                        }
	                    } else {
	                        $plan_name = "-";
	                    }
	                    $plan_name = rtrim($plan_name, ", ");
	                    $arm_next_recurring_date = rtrim($arm_next_recurring_date,", ");
                		$arm_expire_date =rtrim($arm_expire_date,", "); 

	                    $arm_registered_date = date_i18n($wp_date_time_format, strtotime($row['user_registered']));
	                    $tmp["Member"] = $row['user_login'];
	                    $tmp["Email"] = $row['user_email'];
	                    $tmp["Plan"] = $plan_name;
	                    $tmp["Next_Recurring_Date"] = (!empty($arm_next_recurring_date)) ? $arm_next_recurring_date : "-" ;
	                    $tmp["Expire_Plan_Date"] = (!empty($arm_expire_date)) ? $arm_expire_date : "-" ;
	                    $tmp["Join_Date"] = $arm_registered_date;
	                    array_push($final_log, $tmp);
	                }
	            } else if($report_type == 'pay_per_post_report') {
	                $export_report_type = "-pay-per-post-report";
	                $arm_history_tmp = array (
	                    "Invoice_ID" => '',
	                    "Member" => '',
	                    "Paid_By" => '',
	                    "Plan" => '',
	                    "Paid Amount" => '',
	                    "Payment_Gateway" => '',
	                    "Payment_Date" => ''
	                );
			$arm_plan_default_arr = array();	
	                foreach ($historyRecords as $row) {
	                    $payment_gateway = ucfirst( str_replace('_', ' ', $row['arm_payment_gateway'] ) );
	                    $arm_created_date = date_i18n($wp_date_time_format, strtotime($row['arm_created_date']));
	                    $user_login = !empty($row['user_login']) ? $row['user_login'] : $arm_default_user_details_text ;
	                    $paid_by = !empty($row['arm_payer_email']) ? $row['arm_payer_email'] : '-' ;
			    if(!empty($arm_plan_default_arr[$row['arm_plan_id']])) {
			    	$plan_name = $arm_plan_default_arr[$row['arm_plan_id']];
			    } else {
			    	$plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($row['arm_plan_id']);
				$plan_name = !empty($plan_name) ? $plan_name : '-' ;
				$arm_plan_default_arr[$row['arm_plan_id']] = $plan_name;
			    }
			    $invoice_id = $arm_global_settings->arm_manipulate_invoice_id($row['arm_invoice_id']);		
	                    $invoice_id = !empty($invoice_id) ? $invoice_id : '-' ;                       

	                    $tmp["Invoice_ID"] = $invoice_id;
	                    $tmp["Member"] = $user_login;
	                    $tmp["Paid_By"] = $paid_by;
	                    $tmp["Plan"] = $plan_name;
	                    $tmp["Paid Amount"] = number_format($row['arm_amount'],2)." ".$row['arm_currency'];;
	                    $tmp["Payment_Gateway"] = $payment_gateway;
	                    $tmp["Payment_Date"] = $arm_created_date;
	                    array_push($final_log, $tmp);
	                }
	            }else if($report_type == 'coupon_report') {   
	                $export_report_type = "-coupon-report";
	                $arm_history_tmp = array (
	                    "Coupon Code" => '',
	                    "Coupon Discount" => '',
	                    "Member" => '',
	                    "Plan" => '',
	                    "Paid Amount" => '',	                    
	                    "Payment Gateway" => '',              
	                    "Payment Date" => ''
	                );
	                
	                foreach ($historyRecords as $row) {
	                    $payment_gateway = ucfirst( str_replace('_', ' ', $row['arm_payment_gateway'] ) );
	                    $arm_created_date = date_i18n($wp_date_time_format, strtotime($row['arm_created_date']));
	                    $user_login = !empty($row['user_login']) ? $row['user_login'] : $arm_default_user_details_text ;
	                    $plan_name = !empty($row['arm_subscription_plan_name']) ? $row['arm_subscription_plan_name'] : '-' ;
	                    if(empty($plan_name) || $plan_name == "-") {
	                    	$plan_id = !empty($row['arm_plan_id']) ? $row['arm_plan_id'] : "";
		                    if(!empty($plan_id)) {
		                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
		                    } else {
		                        $plan_name = "-";
		                    }
		                    $plan_name = rtrim($plan_name, ", ");
	                    }
	                    $coupon_code = !empty($row['arm_coupon_code']) ? $row['arm_coupon_code'] : '-' ;
	                    $coupon_discount = !empty($row['arm_coupon_discount']) ? $row['arm_coupon_discount'] : '-' ;                    
	                    $coupon_discount_type = !empty($row['arm_coupon_discount_type']) ? $row['arm_coupon_discount_type'] : '' ;

	                    $tmp["Coupon Code"] = $coupon_code;
	                    $tmp["Coupon Discount"] = number_format($coupon_discount,2)." ".$coupon_discount_type;
	                    $tmp["Member"] = $user_login;
	                    $tmp["Plan"] = $plan_name;
	                    $tmp["Amount"] = number_format($row['arm_amount'],2)." ".$row['arm_currency'];
	                    $tmp["Payment_Gateway"] = $payment_gateway;
	                    $tmp["Payment_Date"] = $arm_created_date;
	                    array_push($final_log, $tmp);
	                }
	            }

	            ob_clean();
	            ob_start();
	            $now = gmdate("D, d M Y H:i:s");
	            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	            header("Last-Modified: {$now} GMT");
	            header("Content-Type: application/force-download");
	            header("Content-Type: application/octet-stream");
	            header("Content-Type: application/download");
	            header("Content-Disposition: attachment;filename=ARMember-export".$export_report_type.".csv");
	            header("Content-Transfer-Encoding: binary");
	            $df = fopen("php://output", 'w');
	            fputcsv($df, array_keys($arm_history_tmp));
	            if(!empty($final_log)) {
	                foreach ($final_log as $row) {
	                    fputcsv($df, $row);
	                }
	            }
	            fclose($df);
	            exit;
	        }
	    }
	
	}
	global $arm_report_analytics;
	$arm_report_analytics = new ARM_Report_Analytics();
}

?>