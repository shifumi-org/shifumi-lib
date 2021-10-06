<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans;
$arm_all_block_settings = $arm_global_settings->arm_get_all_block_settings();
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('all', ARRAY_A, true);
$failed_login_users = $arm_members_class->arm_get_failed_login_users();
$arm_all_block_settings['failed_login_lockdown'] = isset($arm_all_block_settings['failed_login_lockdown']) ? $arm_all_block_settings['failed_login_lockdown'] : 0;
$arm_all_block_settings['max_login_retries'] = isset($arm_all_block_settings['max_login_retries']) ? $arm_all_block_settings['max_login_retries'] : 5;
$arm_all_block_settings['temporary_lockdown_duration'] = isset($arm_all_block_settings['temporary_lockdown_duration']) ? $arm_all_block_settings['temporary_lockdown_duration'] : 10;
$arm_all_block_settings['permanent_login_retries'] = isset($arm_all_block_settings['permanent_login_retries']) ? $arm_all_block_settings['permanent_login_retries'] : 15;
$arm_all_block_settings['permanent_lockdown_duration'] = isset($arm_all_block_settings['permanent_lockdown_duration']) ? $arm_all_block_settings['permanent_lockdown_duration'] : 24;
$arm_all_block_settings['remained_login_attempts'] = isset($arm_all_block_settings['remained_login_attempts']) ? $arm_all_block_settings['remained_login_attempts'] : 0;
$arm_all_block_settings['track_login_history'] = isset($arm_all_block_settings['track_login_history']) ? $arm_all_block_settings['track_login_history'] : 1;
$arm_all_block_settings['arm_block_ips'] = isset($arm_all_block_settings['arm_block_ips']) ? $arm_all_block_settings['arm_block_ips'] : '';
$arm_all_block_settings['arm_conditionally_block_urls'] = isset($arm_all_block_settings['arm_conditionally_block_urls']) ? $arm_all_block_settings['arm_conditionally_block_urls'] : 0;
$conditionally_block_urls_options = (isset($arm_all_block_settings['arm_conditionally_block_urls_options']) && $arm_all_block_settings['arm_conditionally_block_urls'] == 1) ? $arm_all_block_settings['arm_conditionally_block_urls_options'] : array('0' => array('plan_id' => '', 'arm_block_urls' => ''));
$conditionally_block_urls_options_count = isset($arm_all_block_settings['arm_conditionally_block_urls_options'])?count($conditionally_block_urls_options):1;

if(isset($_POST["arm_export_login_history"]) && $_POST["arm_export_login_history"] == 1) {
        $user_table = $wpdb->users;
        $arm_log_history_search_user = isset($_POST['arm_log_history_search_user']) ? $_POST['arm_log_history_search_user'] : '';
        $final_log = array();
        if (is_multisite()) {
            $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
        } else {
            $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
        }
        $arm_login_history_tmp = array (
            "Username" => '',
            "Logged_In_Date" => '',
            "Logged_In_IP" => '',
            "Browser_Name" => '',
            "Country" => '',
            "Logged_Out_Date" => ''
        );
        $history_where = "";
        if(!empty($arm_log_history_search_user))
        {
           $history_where .= ' AND u.user_login LIKE "%'.$arm_log_history_search_user.'%" ';
        }
        $historyRecords = $wpdb->get_results("SELECT u.user_login, l.arm_user_current_status, l.arm_user_id,l.arm_logged_in_ip, l.arm_logged_in_date, l.arm_logout_date, l.arm_history_browser, l.arm_login_country FROM `{$user_table}` u INNER JOIN `" . $ARMember->tbl_arm_login_history . "` l ON u.ID = l.arm_user_id where 1 = 1  $history_where ORDER BY l.arm_history_id DESC ", ARRAY_A);
        foreach ($historyRecords as $row) {
            $logout_date = !empty($row['arm_logout_date']) ? date_create($row['arm_logout_date']) : '';
            $login_date = !empty($row['arm_logged_in_date']) ? date_create($row['arm_logged_in_date']) : '';
            if (isset($row['arm_user_current_status']) && $row['arm_user_current_status'] == 1 && $row['arm_logout_date'] == "0000-00-00 00:00:00") {
                $arm_logged_out_date = __('Currently Logged In', 'ARMember');
            } else {
                if ($row['arm_user_current_status'] == 0 && $row['arm_logout_date'] == "0000-00-00 00:00:00") {
                    $arm_logged_out_date = "-";
                } else {
                    $arm_logged_out_date = date_i18n($wp_date_time_format, strtotime($row['arm_logout_date']));
                }
            }

            $tmp["Username"] = !empty($row['user_login']) ? $row['user_login'] : '-';
            $tmp["Logged_In_Date"] = !empty($row['arm_logged_in_date']) ? date_i18n($wp_date_time_format, strtotime($row['arm_logged_in_date'])) : '';
            $tmp["Logged_In_IP"] = !empty($row["arm_logged_in_ip"]) ? $row["arm_logged_in_ip"] : '';
            $tmp["Browser_Name"] = !empty($row["arm_history_browser"]) ? $row["arm_history_browser"] : '';
            $tmp["Country"] = !empty($row["arm_login_country"]) ? $row["arm_login_country"] : '';
            $tmp["Logged_Out_Date"] = $arm_logged_out_date;
            if($tmp["Logged_Out_Date"] == "-") {
                $tmp["Logged_Out_Date"] = "";
            }
            
            array_push($final_log, $tmp);
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
        header("Content-Disposition: attachment;filename=ARMember-export-login-history.csv");
        header("Content-Transfer-Encoding: binary");
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($arm_login_history_tmp));
        if(!empty($final_log)) {
            foreach ($final_log as $row) {
                fputcsv($df, $row);
            }
        }
        fclose($df);
        exit;
}
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<form  method="post" action="#" id="arm_block_settings" class="arm_block_settings arm_admin_form">
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Enable Login attempts Security','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="failed_login_lockdown" value="1" class="armswitch_input" name="arm_block_settings[failed_login_lockdown]" <?php checked($arm_all_block_settings['failed_login_lockdown'], 1);?>/>
							<label for="failed_login_lockdown" class="armswitch_label"></label>
						</div>
                        <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('Enable login security option for failed login attempts.','ARMember'); ?>)</span>
                        <span class="arm_info_text" style="margin: 10px 0 0; display:block;color: #f00;">(<?php _e('Note', 'ARMember'); ?>: <?php _e('Failed login attempt history will automatically be cleared which is older than 30 days.', 'ARMember'); ?>)</span>
					</td>
				</tr>
				<tr class="arm_global_settings_sub_content failed_login_lockdown <?php echo ($arm_all_block_settings['failed_login_lockdown'] == 1) ? '':'hidden_section';?>">
                                    <td class="arm-form-table-content" colspan="2">
                                        <table class="arm-form-table-login-security">
                                            <tr>
                                                <td><span class="arm_failed_login_before_label"><?php _e('Maximum Number of login attempts','ARMember');?></span></td>
                                                <td>
                                                <input  type="text" id="max_login_retries" value="<?php echo $arm_all_block_settings['max_login_retries'];?>" class="arm_general_input" style="width:50px !important;" name="arm_block_settings[max_login_retries]" onkeypress="return isNumber(event)" />
                                                <span class="arm_max_login_retries_error arm_error_msg" style="display:none; margin: 0 5px;"><?php _e('Please enter maximum number of login attempts.','ARMember');?> </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="arm_failed_login_before_label"><?php _e('Lock user temporarily for','ARMember');?></span></td>
                                                <td>
                                                    <input  type="text" id="temporary_lockdown_duration" value="<?php echo $arm_all_block_settings['temporary_lockdown_duration'];?>" class="arm_general_input" style="width:50px !important;" name="arm_block_settings[temporary_lockdown_duration]" onkeypress="return isNumber(event)" />
                                                    <br/>
                                                    <span class="arm_failed_login_after_label">&nbsp;<?php _e('Minutes','ARMember');?></span>
                                                    <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("After maximum failed login attempts user will be inactive for given minutes. During this time, user will not be able to login into the system.", 'ARMember');?>" style="margin-top: 0px !important;"></i>
                                                    <br/>
                                                    <span class="arm_temporary_lockdown_duration_error arm_error_msg" style="display:none; margin: 0 5px;"> <?php _e('Please enter temporarily lock user duration.','ARMember');?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="arm-form-table-login-security-title"><span class="arm_failed_login_sub_title"><strong><?php _e('Advanced Security','ARMember');?></strong></span></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td><span class="arm_failed_login_before_label"><?php _e('Permanent lock user after login attempts','ARMember');?></span></td>
                                                <td>
                                                    <input  type="text" id="permanent_login_retries" value="<?php echo $arm_all_block_settings['permanent_login_retries'];?>" class="arm_general_input" style="width:50px !important;" name="arm_block_settings[permanent_login_retries]" onkeypress="return isNumber(event)" />
                                                    <span class="arm_permanent_login_retries_error arm_error_msg" style="display:none; margin: 0 5px;"><?php _e('Please enter number of login attempts after user permanent lock.','ARMember');?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="arm_failed_login_before_label"><?php _e('Permanent lockdown Duration','ARMember');?></span></td>
                                                <td>
                                                    <input  type="text" id="permanent_lockdown_duration" value="<?php echo $arm_all_block_settings['permanent_lockdown_duration'];?>" class="arm_general_input" style="width:50px !important;" name="arm_block_settings[permanent_lockdown_duration]" onkeypress="return isNumber(event)" />
                                                    <br/><span class="arm_failed_login_after_label">&nbsp;<?php _e('Hours','ARMember');?></span>
                                                    <span class="arm_permanent_lockdown_duration_error arm_error_msg" style="display:none; margin: 0 5px;"> <?php _e('Please enter permanent lockdown duration.','ARMember');?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="arm-form-table-login-security-title">
                                                    <span class="arm_failed_login_sub_title" ><strong><?php _e('Failed Login Attempt Login History','ARMember');?></strong></span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td><span class="arm_failed_login_before_label" style="<?php echo (is_rtl()) ? 'margin-left: 115px;' : 'margin-right: 72px;';?>"><?php _e('Reset Failed Login Attempts History','ARMember');?></span></td>
                                                <td style="width:500px;">
                                                    <select id="arm_reset_login_attempts_users" class="arm_chosen_selectbox" name="arm_general_settings[arm_exclude_role_for_restrict_admin][]" data-placeholder="<?php _e('Select User(s)..', 'ARMember');?>" multiple="multiple" style="width:352px;" >
                                                        <?php
                                                            if (!empty($failed_login_users)):
                                                                ?><option class="arm_message_selectbox_op" value="all" >All Users</option><?php
                                                                foreach ($failed_login_users as $user) {
                                                                    ?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($user['ID']); ?>"><?php echo stripslashes($user['user_login']);?></option><?php
                                                                }
                                                            else:
                                                        ?><option value="" disabled="true"><?php _e('No Users Available', 'ARMember');?></option><?php endif;?>
                                                    </select>
                                                    <div class="arm_datatable_filters_options" style="position: absolute;">
                                                        <input id="doaction1" class="armbulkbtn armemailaddbtn" value="Go" type="button"  onclick="showConfirmBoxCallback('arm_clear_login_user');">
                                                        <?php echo $arm_global_settings->arm_get_confirm_box('arm_clear_login_user', __('Are you sure want to reset login attempts for the selected member?', 'ARMember'), 'arm_reset_user_login_attempts'); ?>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_reset_loader_img" style="position:relative;top:0px;display:none;" width="24" height="24" />
                                                    </div>
                                                    <span class="arm_invalid arm_reset_login_attempts_users_error" style="display:none;"> Please select user.</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            	<td>
                                            		<div style="position:relative;">
								                        <a href="javascript:void(0)" id="arm_failed_login_attempts_history" class="arm_failed_login_attempts_history armemailaddbtn"><?php _e('View Failed Login Attempts History', 'ARMember'); ?></a>
								                    </div>
                                            	</td>
                                            </tr>
                                        </table>
                    
                    
                                    </td>
				</tr>
                                <tr class="arm_global_settings_sub_content failed_login_lockdown <?php echo ($arm_all_block_settings['failed_login_lockdown'] == 1) ? '':'hidden_section';?>">
					<th class="arm-form-table-label"><?php _e('Remaining login attempt warning','ARMember');?></th>
					<td class="arm-form-table-content">						
					<?php
						$remiand_login_attempts = is_array($arm_all_block_settings['remained_login_attempts']) ? $arm_all_block_settings['remained_login_attempts'][0] : $arm_all_block_settings['remained_login_attempts'];
						?>
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="remained_login_attempts" value="1" class="armswitch_input" name="arm_block_settings[remained_login_attempts]" <?php checked($remiand_login_attempts, 1);?>/>
							<label for="remained_login_attempts" class="armswitch_label"></label>
						</div>
                                                <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('Display remaining login attempts warning message.','ARMember'); ?>)</span>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label armember_general_setting_lbl"><label for="arm_block_usernames"><?php _e('Block Username On Signup', 'ARMember'); ?>
                                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Those username(s) which are entered here, will be blocked on new user registration, that means those keywords will not be allowed to use as username upon signup. For example, if you will enter 'test' here, then all usernames which contain 'test' will be banned, like '<u>test</u>abc', 'abc<u>test</u>', 'abc<u>test</u>def'.", 'ARMember');?>"></i></label></th>
					<td class="arm-form-table-content">
						<textarea name="arm_block_settings[arm_block_usernames]" id="arm_block_usernames" rows="8" cols="40"><?php 
						$arm_block_usernames = (isset($arm_all_block_settings['arm_block_usernames'])) ? $arm_all_block_settings['arm_block_usernames'] : '';
						echo (!empty($arm_block_usernames)) ? (esc_textarea(get_magic_quotes_runtime() ? stripslashes_deep($arm_block_usernames) : $arm_block_usernames)) : '';
						?></textarea>
						<div class="arm_info_text"><?php _e('You should place each keyword on a new line.','ARMember'); ?></div>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_block_usernames_msg"><?php _e('Blocked Username Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_block_settings[arm_block_usernames_msg]" id="arm_block_usernames_msg" value="<?php echo (!empty($arm_all_block_settings['arm_block_usernames_msg'])) ? esc_html(stripslashes($arm_all_block_settings['arm_block_usernames_msg'])) : '';?>"/>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This message will be display when member tries to register with blocked username.", 'ARMember');?>"></i>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label armember_general_setting_lbl"><label for="arm_block_emails"><?php _e('Block Email Addresses On Signup', 'ARMember'); ?>
                                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Those Email Address(es) which are entered here, will be blocked on new user registration, that means those keywords will not be allowed to use as Email Address upon signup. For example, if you will enter 'abc@def.ghi' here, then exact this email address will be banned, but if you will enter 'test' only, then all email addresses which contain 'test' will be banned, like '<u>test</u>abc@abc.def', 'ab<u>test</u>@cde.efg', 'ab<u>test</u>cd@efg.ghi', 'abc@def.<u>test</u>', 'abc@<u>test</u>.def'.", 'ARMember');?>"></i></label></th>
					<td class="arm-form-table-content">
						<textarea name="arm_block_settings[arm_block_emails]" id="arm_block_emails" rows="8" cols="40"><?php 
						$arm_block_emails = (isset($arm_all_block_settings['arm_block_emails'])) ? $arm_all_block_settings['arm_block_emails'] : '';
						echo (!empty($arm_block_emails)) ? ( esc_textarea( get_magic_quotes_runtime() ? stripslashes_deep($arm_block_emails) : $arm_block_emails ) ) : '';
						?></textarea>
						<div class="arm_info_text"><?php _e('You should place each keyword on a new line.','ARMember'); ?></div>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_block_emails_msg"><?php _e('Blocked Email Addresses Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_block_settings[arm_block_emails_msg]" id="arm_block_emails_msg" value="<?php echo (!empty($arm_all_block_settings['arm_block_emails_msg'])) ? esc_html(stripslashes($arm_all_block_settings['arm_block_emails_msg'])) : '';?>"/>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This message will be display when member tries to register with blocked email address.", 'ARMember');?>"></i>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_block_ips"><?php _e('Block IP Addresses', 'ARMember'); ?></label><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Those IP Address(es) which are entered here, will not be able to access your website. Please note that IP address should exact match. For example, 0.0.0.1 will be banned if and only if IP address will exact match with user's IP address.", 'ARMember');?>"></i></th>
					<td class="arm-form-table-content">
						<textarea name="arm_block_settings[arm_block_ips]" id="arm_block_ips" rows="8" cols="40"><?php 
						$arm_block_ips = $arm_all_block_settings['arm_block_ips'];
						echo (!empty($arm_block_ips)) ? ( esc_textarea( get_magic_quotes_runtime() ? stripslashes_deep($arm_block_ips) : $arm_block_ips ) ) : '';
						?></textarea>
						<div class="arm_info_text"><?php _e('You should place each IP Address on a new line.','ARMember'); ?></div>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_block_ips_msg"><?php _e('Blocked IP Address Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_block_settings[arm_block_ips_msg]" id="arm_block_ips_msg" value="<?php echo (!empty($arm_all_block_settings['arm_block_ips_msg'])) ? esc_html(stripslashes($arm_all_block_settings['arm_block_ips_msg'])) : '';?>"/>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This message will be display when IP Address is blocked.", 'ARMember');?>"></i>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_block_urls"><?php _e('Block URLs', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
			<textarea name="arm_block_settings[arm_block_urls]" id="arm_block_urls" rows="8" cols="40"><?php echo (!empty($arm_all_block_settings['arm_block_urls'])) ? ( esc_textarea(get_magic_quotes_runtime() ? stripslashes_deep($arm_all_block_settings['arm_block_urls']) : $arm_all_block_settings['arm_block_urls'] ) ) : '';
						?></textarea>
						<div class="arm_info_text"><?php _e('You should place each URL on a new line','ARMember'); ?></div>
                                                <div class="arm_info_text"><?php _e('Entered URLs will be blocked for all users and visitors except administrator.','ARMember'); ?></div>
						<div class="arm_info_text"><?php _e('You can use wildcard(*) for specific pattern.','ARMember'); ?>(i.e. http://www.example.com/<b>*some_text*</b>/page)</div>
                                                
                                                <div class="conditionally_block_url_div">
                                                    <label for="conditionally_block_urls" class="conditionally_block_urls_lbl"><b><?php _e('Conditionally Plan<br/> Wise Block URLs', 'ARMember'); ?></b></label>
                                                    <div class="armswitch arm_global_setting_switch">
                                                            <input type="checkbox" id="conditionally_block_urls" value="1" class="armswitch_input" name="arm_block_settings[arm_conditionally_block_urls]" <?php checked($arm_all_block_settings['arm_conditionally_block_urls'] , 1);?>/>
                                                            <label for="conditionally_block_urls" class="armswitch_label"></label>
                                                            <input type="hidden" name="arm_conditional_no" id="arm_conditional_no" value="<?php echo $conditionally_block_urls_options_count; ?>" />
                                                            <input type="hidden" id="arm_plus_icon" value="<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan.png" />
                                                            <input type="hidden" id="arm_plus_icon_hover" value="<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan_hover.png" />
                                                    </div>
                                                </div>
                                                
                                                <table class="arm_conditionally_block_urls_tbl" style="<?php echo ($arm_all_block_settings['arm_conditionally_block_urls'] == 1) ? 'display:block;' : 'display:none;'; ?>">
                                                    <?php if(!empty($conditionally_block_urls_options)) : 
                                                        $condition_count = 0;
                                                        foreach($conditionally_block_urls_options as $condition):
                                                            $condition_count++;
                                                            ?>
                                                            <tr id="cond_block_url_first_row<?php echo $condition_count; ?>">
                                                                <td class="arm_conditionally_block_urls_label arm_select_plan_condtionally">
                                                                    <label for="arm_plan_"><?php _e('Select Plan', 'ARMember'); ?></label>
                                                                </td>
                                                                <td class="arm_conditionally_block_urls_content">
                                                                    <input type="hidden" id="arm_plan_<?php echo $condition_count; ?>" name="arm_block_settings[arm_conditionally_block_urls_options][<?php echo $condition_count; ?>][plan_id]" value="<?php echo isset($condition['plan_id']) ? $condition['plan_id'] : ''; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_plan_<?php echo $condition_count; ?>" class="arm_conditional_plans_li">
                                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
                                                                                <?php
                                                                                if (!empty($all_plans)) {
                                                                                    foreach ($all_plans as $p) {
                                                                                        
                                                                                            ?><li data-label="<?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?>" data-value="<?php echo $p['arm_subscription_plan_id'] ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span class="arm_invalid arm_block_url_plan_error_<?php echo $condition_count; ?>" ><?php _e('Please select plan.', 'ARMember'); ?></span>
                                                                </td>
                                                                <td class='arm_add_conditionally arm_condition_icon'>
                                                                    <a href="javascript:void(0);" class="arm_add_conditionally_block_urls">
                                                                        <img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan.png';">
                                                                    </a>
                                                                </td>
                                                                <td class='arm_condition_icon'<?php echo ($condition_count == 1) ? "style='display:none;'" : ''; ?>>
                                                                    <a href="javascript:void(0);" class="arm_remove_conditionally_block_urls" data_conditionally_id="<?php echo $condition_count; ?>">
                                                                        <img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan.png';">
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <tr id="cond_block_url_second_row<?php echo $condition_count; ?>">
                                                                <td class="arm_conditionally_block_urls_label arm_block_urls_conditionally">
                                                                    <label for="arm_block_urls"><?php _e('Block URLs', 'ARMember'); ?></label>
                                                                </td>
                                                                <td class="arm_conditionally_block_urls_content">
                                                                    <textarea name="arm_block_settings[arm_conditionally_block_urls_options][<?php echo $condition_count; ?>][arm_block_urls]" id="arm_block_urls" rows="5" cols="40"><?php echo (!empty($condition['arm_block_urls'])) ? ( esc_textarea(get_magic_quotes_runtime() ? stripslashes_deep($condition['arm_block_urls']) : $condition['arm_block_urls'] ) ) : ''; ?></textarea>
                                                                </td>
                                                                <td></td><td></td>
                                                            </tr>
                                                            <?php
                                                        endforeach;
                                                    endif; ?>
                                                </table>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Blocked URLs Options', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content" style="padding-top: 14px;">
						<?php $arm_all_block_settings['arm_block_urls_option'] = (!empty($arm_all_block_settings['arm_block_urls_option'])) ? $arm_all_block_settings['arm_block_urls_option'] : 'message';?>
						<input type="radio" id="arm_block_urls_opt_radio_message" class="arm_iradio arm_block_urls_opt_radio" name="arm_block_settings[arm_block_urls_option]" value="message" <?php checked($arm_all_block_settings['arm_block_urls_option'], 'message');?>>
						<label for="arm_block_urls_opt_radio_message"><span><?php _e('Display Message', 'ARMember'); ?></span></label>
						<input type="radio" id="arm_block_urls_opt_radio_redirect" class="arm_iradio arm_block_urls_opt_radio" name="arm_block_settings[arm_block_urls_option]" value="redirect" <?php checked($arm_all_block_settings['arm_block_urls_option'], 'redirect');?>>
						<label for="arm_block_urls_opt_radio_redirect"><span><?php _e('Redirect to url', 'ARMember'); ?></span></label>
						<div class="armclear"></div>
						<div class="arm_block_urls_option_fields arm_block_urls_option_fields_message <?php echo ($arm_all_block_settings['arm_block_urls_option']=='message') ? '':'hidden_section';?>">
							<label><input type="text" name="arm_block_settings[arm_block_urls_option_message]" value="<?php echo (!empty($arm_all_block_settings['arm_block_urls_option_message'])) ? esc_html(stripslashes($arm_all_block_settings['arm_block_urls_option_message'])) : '';?>"/>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This message will be display when requested URL or URL pattern is blocked.", 'ARMember');?>"></i></label>
						</div>
						<div class="arm_block_urls_option_fields arm_block_urls_option_fields_redirect <?php echo ($arm_all_block_settings['arm_block_urls_option']=='redirect') ? '':'hidden_section';?>">
							<label><input type="text" name="arm_block_settings[arm_block_urls_option_redirect]" value="<?php echo (!empty($arm_all_block_settings['arm_block_urls_option_redirect'])) ? esc_html(stripslashes($arm_all_block_settings['arm_block_urls_option_redirect'])) : '';?>"/>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Member will be redirect to this URL when requested URL or URL pattern is blocked.", 'ARMember');?>"></i></label>
						</div>
					</td>
				</tr>
			</table>
                        <div class="arm_solid_divider"></div>
            <table class="form-table">
				<tr class="arm_global_settings_sub_content track_login_history_div">
					<th class="arm-form-table-label"><?php _e('Record Login History?','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="track_login_history" value="1" class="armswitch_input" name="arm_block_settings[track_login_history]" <?php checked($arm_all_block_settings['track_login_history'], 1);?>/>
							<label for="track_login_history" class="armswitch_label"></label>
						</div>
                                            
					</td>
				</tr>
		
                <tr><td></td>
                    <td>
                        <div style="position:relative; float: left;">
                             <button onclick="showConfirmBoxCallback('arm_clear_login_history');" class="armemailaddbtn" type="button"><?php _e('Reset Login History', 'ARMember'); ?></button>
                            <?php echo $arm_global_settings->arm_get_confirm_box('arm_clear_login_history', __("Are you sure want to reset all user's login history?", "ARMember"), 'arm_reset_login_history', ''); ?>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_reset_history_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
                        </div>
                    </td>
                </tr>
                                
			</table>
			<div class="arm_submit_btn_container">
				<button class="arm_save_btn arm_block_settings_btn" type="submit" id="arm_block_settings_btn" name="arm_block_settings_btn"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
                <?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
		<div class="armclear"></div>
	</div>
</div>
<script>
    var ARM_CONDI_BLOCK_REQ_MSG = '<?php _e('Please select plan.', 'ARMember'); ?>';
</script>

<div class="arm_failed_login_attempts_history_popup popup_wrapper" style="width: 650px;margin-top: 40px;">
    <table cellspacing="0">
        <tr class="popup_wrapper_inner">	
            <td class="arm_failed_login_attempts_history_popup_close_btn arm_popup_close_btn"></td>
            <td class="popup_header"><?php _e('Failed Login Attempts History', 'ARMember');?></td>
            <td class="popup_content_text" style="padding:0">
                <?php 
                    $arm_failed_login_attempts_history = $arm_members_class->arm_get_failed_login_attempts_history(1, 10);
                    if(isset($arm_failed_login_attempts_history) && !empty($arm_failed_login_attempts_history))
                    {
                        ?><div class="arm_membership_history_list">
                        <?php echo $arm_failed_login_attempts_history; ?>
                        </div>
                        <?php
                    }
                ?>
                <div class="armclear"></div>
            </td>
        </tr>
    </table>
    <div class="armclear"></div>
</div>    
<script>
    var NO_USERS_AVAILABE = '<?php echo addslashes( __('No Users Available', 'ARMember')); ?>';
</script>