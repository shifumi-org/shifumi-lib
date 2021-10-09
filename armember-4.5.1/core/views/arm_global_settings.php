<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_email_settings, $arm_payment_gateways, $arm_access_rules, $arm_subscription_plans, $arm_member_forms, $arm_social_feature, $arm_drip_rules;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();


/*
if(!empty($_REQUEST['arm_update_payment_history']))
{
    global $wpdb, $wp, $ARMember,$arm_member_forms, $arm_global_settings;

    $pt_log_table = $ARMember->tbl_arm_payment_log;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $ptquery = "SELECT arm_log_id,arm_user_id FROM `" . $pt_log_table . "` ";
    $pt_payment_log = $wpdb->get_results($ptquery, ARRAY_A);

    $payment_log_user_det_arr = array();
    if(count($pt_payment_log)>0)
    {
        foreach($pt_payment_log as $pt_payment_log_data)
        {
            $payment_log_user_id = $pt_payment_log_data["arm_user_id"];
            $payment_log_id = $pt_payment_log_data["arm_log_id"];
            if(!isset($payment_log_user_det_arr[$payment_log_user_id]))
            {
                $meta_value_first_name = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = {$payment_log_user_id} AND meta_key = 'first_name'");
                $meta_value_last_name = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = {$payment_log_user_id} AND meta_key = 'last_name'");

                $payment_log_user_det_arr[$payment_log_user_id]['first_name'] = $meta_value_first_name;
                $payment_log_user_det_arr[$payment_log_user_id]['last_name'] = $meta_value_last_name;
            }
            else
            {
                $meta_value_first_name = $payment_log_user_det_arr[$payment_log_user_id]['first_name'];
                $meta_value_last_name = $payment_log_user_det_arr[$payment_log_user_id]['last_name'];
            }

            if( !empty($meta_value_first_name) || !empty($meta_value_last_name) )
            {
                $wpdb->update($pt_log_table, 
                                array('arm_first_name' => $meta_value_first_name,'arm_last_name'=>$meta_value_last_name), 
                                array('arm_log_id' => $payment_log_id)
                            );
            }
        }
    }
}
*/



$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$is_permalink = $arm_global_settings->is_permalink();
$general_settings = $all_global_settings['general_settings'];

$page_settings = $all_global_settings['page_settings'];

$general_settings['hide_feed'] = isset($general_settings['hide_feed']) ? $general_settings['hide_feed'] : 0;
$general_settings['disable_wp_login_style'] = isset($general_settings['disable_wp_login_style']) ? $general_settings['disable_wp_login_style'] : 0;
$general_settings['arm_invoice_template'] = isset($general_settings['arm_invoice_template']) ? $general_settings['arm_invoice_template'] : '';
$all_plans_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true);
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules = $arm_access_rules->arm_get_default_access_rules();
$default_schedular_settings = $arm_global_settings->arm_default_global_settings();
$all_roles = $arm_global_settings->arm_get_all_roles();

$currencies = array_merge($arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['stripe'], $arm_payment_gateways->currency['authorize_net'], $arm_payment_gateways->currency['2checkout']);


global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
$is_debug_enable = 0;
?>
<style>
    .purchased_info{
        color:#7cba6c;
        font-weight:bold;
        font-size: 15px;
    }
	#license_success{
		color:#8ccf7a !important;
	}
	#license_error{
		color:red !important;
	}
	.arperrmessage{color:red;}
    #armresetlicenseform {
        border-radius:0px;
        text-align:center;
        width:700px;
        height:500px;
        left:35%;
        border:none;
		background:#ffffff !important;
		padding-top:15px;
    }
	.arfnewmodalclose
    {
        font-size: 15px;
        font-weight: bold;
        height: 19px;
        position: absolute;
        right: 3px;
        top:5px;
        width: 19px;
        cursor:pointer;
        color:#D1D6E5;
    }
	#licenseactivatedmessage {
    height:22px;
    color:#FFFFFF;
    font-size:17px;
    font-weight:bold;
    letter-spacing:0.5;
    margin-left:0px;
    display:block;
    border-radius:3px;
    -moz-border-radius:3px;
    -webkit-border-radius:3px;
    -o-border-radius:3px;

    padding:7px 5px 5px 0px;
    font-family:'open_sansregular', Arial, Helvetica, Verdana, sans-serif;
    background-color:#8ccf7a;
    margin-top:15px !important;
    margin-bottom:10px !important;
    text-align:center;
	}
	.red_remove_license_btn {
    -moz-box-sizing: content-box;
    background: #e95a5a; 
    border:none;
    box-shadow: 0 4px 0 0 #d23939;
    color: #FFFFFF !important;
    cursor: pointer;
    font-size: 16px !important;
    font-style: normal;
    font-weight: bold;
    height: 30px;
    min-width: 90px;
    width: auto;
    outline: none;
    padding: 0px 10px;
    text-shadow: none;
    text-transform: none;
    vertical-align:middle;
    text-align:center;
    margin-bottom:15px;
}
.red_remove_license_btn:hover {
    background: #d23939;
    box-shadow: 0 4px 0 0 #b83131;
}
	.newform_modal_title { font-size:25px; line-height:25px; margin-bottom: 10px; }
	.newmodal_field_title { font-size: 16px;
    line-height: 16px;
    margin-bottom: 10px; }
</style>
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		<form method="post" action="#" id="arm_global_settings" class="arm_global_settings arm_admin_form" onsubmit="return false;">
			<?php do_action('arm_before_global_settings_html', $general_settings);?>
            
			<div class="page_sub_title"><?php _e('General Settings','ARMember');?></div>
            
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide admin bar','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_admin_bar" <?php checked($general_settings['hide_admin_bar'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_admin_bar]"/>
							<label for="hide_admin_bar" class="armswitch_label"></label>
						</div>
						<label for="hide_admin_bar" class="arm_global_setting_switch_label"><?php _e('Hide admin bar for non-admin users?','ARMember');?></label>
					</td>
				</tr>
				<tr class="form-field arm_exclude_role_for_hide_admin<?php echo ($general_settings['hide_admin_bar'] == '1') ? '' : ' hidden_section' ; ?>">
                    <th class="arm-form-table-label"><?php _e('Exclude role for hide admin bar','ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <?php $arm_exclude_role_for_hide_admin = array();
                        if(isset($general_settings['arm_exclude_role_for_hide_admin']) && is_array($general_settings['arm_exclude_role_for_hide_admin']))
                        {
                            $arm_exclude_role_for_hide_admin = $general_settings['arm_exclude_role_for_hide_admin'];
                        } else {
                            $arm_exclude_role_for_hide_admin = isset($general_settings['arm_exclude_role_for_hide_admin']) ? explode(',', $general_settings['arm_exclude_role_for_hide_admin']) : array(); 
                        }
                        ?>
                        <select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox" name="arm_general_settings[arm_exclude_role_for_hide_admin][]" data-placeholder="<?php _e('Select Role(s)..', 'ARMember');?>" multiple="multiple" style="width:500px;">
                                <?php
                                    if (!empty($all_roles)):
                                        foreach ($all_roles as $role_key => $role_value) {
                                            ?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($role_key); ?>" <?php echo (in_array($role_key, $arm_exclude_role_for_hide_admin)) ? ' selected="selected"' : ''; ?>><?php echo stripslashes($role_value);?></option><?php
                                        }
                                    else:
                                ?>
                                        <option value=""><?php _e('No Roles Available', 'ARMember');?></option>
                                <?php endif;?>
                        </select>
                        <span class="arm_info_text" style="margin: 10px 0 0; display:block;">
                            (<?php _e('Admin bar will be displayed to selected roles.','ARMember'); ?>)
                        </span>
                    </td>
                </tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Rename','ARMember');?> wp-admin</th>
					<td class="arm-form-table-content">
						<?php
                    $is_disabled = "";

                    if(( $general_settings['rename_wp_admin'] != 1 && strpos(admin_url(),'wp-admin') === false ) || is_multisite()){
                        $is_disabled = 'disabled="disabled"';
                    }
                    ?>
                        <div class="armswitch arm_global_setting_switch" <?php echo (!$is_permalink || $is_disabled != '') ? 'disabled' : ''; ?>>
                            <input type="checkbox" id="rename_wp_admin" <?php checked($general_settings['rename_wp_admin'], '1'); echo $is_disabled ?> value="1" class="armswitch_input" name="arm_general_settings[rename_wp_admin]"/>
                            <label for="rename_wp_admin" class="armswitch_label"></label>
                        </div>

                        <label for="<?php echo (!$is_permalink) ? 'no_rename_wp_admin' : 'rename_wp_admin'; ?>" class="arm_global_setting_switch_label"><?php _e('Rename', 'ARMember'); ?> <strong>wp-admin</strong> <?php _e('folder?', 'ARMember'); ?></label>
                        <?php if (!$is_permalink): ?>
                            <span class="arm_warning_text"><em><?php _e('Change permalink structure to enable this option', 'ARMember'); ?></em></span>
                        <?php endif;
                       

                        if(is_multisite()){ ?>
<br/><span class="arm_warning_text"><em> <?php _e('You cannot rename','ARMember'); ?> wp-admin <?php _e('in multisite environment', 'ARMember'); ?>.</em></span>
                   <?php     }
                   else if($is_disabled != '' ){ ?>
                       <br/><span class="arm_warning_text"><em> wp-admin <?php _e('is already renamed from other plugin. If you want to rename admin directory from', 'ARMember'); ?> ARMember <?php _e('than, please remove admin directory renaming settings from other plugin','ARMember'); ?>.</em></span>
                   <?php }
                    ?>
					</td>
				</tr>
				<?php if($is_permalink):?>
				<tr class="arm_global_settings_sub_content rename_wp_admin <?php echo ($general_settings['rename_wp_admin'] == 1) ? '':'hidden_section';?>">
					<th class="arm-form-table-label"><?php _e('New','ARMember');?> wp-admin <?php _e('Path','ARMember');?>*</th>
					<td class="arm-form-table-content">
						<input  type="text" id="new_wp_admin_path" value="<?php echo $general_settings['new_wp_admin_path'];?>" class="arm_general_input" name="arm_general_settings[new_wp_admin_path]" placeholder="wp-admin"/>
						<br/>
						<em>(<?php _e('EXPERIMENTAL', 'ARMember');?>) Change "/wp-admin" (e.g. panel, cp).</em>
						<br/>
						<br/>
						<span class="arm_warning_text" style="margin:0;"><em><?php _e('Do Not change permalink structure to default in order to work this option. if you set permalink structure to default, You will need to DELETE or comment (//) line which start with', 'ARMember');?>: <code>define("ADMIN_COOKIE_PATH","...</code></em></span>

                     <?php   $arm_get_hide_wp_admin_option = get_option('arm_hide_wp_amin_disable');
                if (!empty($arm_get_hide_wp_admin_option)) {
                    ?>

                    <br/><span class="arm_warning_text" style="margin:0;">
                        
                       <em> <?php _e('If you can\'t login after renaming wp-admin, run below URL and all changes are rollback to default :', 'ARMember'); ?></em><br/>
                   <div class="arm_shortcode_text arm_form_shortcode_box">
                       <span class="armCopyText" style="font-size: 13px;"><?php echo home_url().'?arm_wpdisable='.$arm_get_hide_wp_admin_option; ?></span>
											<span class="arm_click_to_copy_text" data-code="<?php echo home_url().'?arm_wpdisable='.$arm_get_hide_wp_admin_option; ?>"><?php _e('Click to copy', 'ARMember');?></span>
											<span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"><?php _e('Code Copied', 'ARMember');?></span>
                   </div>
                        
                        
                       
                    <?php
                }
                    ?>
                        
					</td>
				</tr>
				<?php endif;?>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide','ARMember');?> wp-login.php <?php _e('page','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_wp_login" <?php checked($general_settings['hide_wp_login'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_wp_login]"/>
							<label for="hide_wp_login" class="armswitch_label"></label>
						</div>
						<label for="hide_wp_login" class="arm_global_setting_switch_label"><?php _e('Hide', 'ARMember');?> <strong>wp-login.php</strong> <?php _e('page for all users?','ARMember');?></label>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Hide register link','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_register_link" <?php checked($general_settings['hide_register_link'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[hide_register_link]"/>
							<label for="hide_register_link" class="armswitch_label"></label>
						</div>
						<label for="hide_register_link" class="arm_global_setting_switch_label"><?php _e('Hide register link on', 'ARMember');?> <strong>wp-login.php</strong> <?php _e('page?','ARMember');?></label>
					</td>
				</tr>
                                 
                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Disable ARMember styling on wp-login page', 'ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="disable_wp_login_style" <?php checked($general_settings['disable_wp_login_style'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[disable_wp_login_style]"/>
							<label for="disable_wp_login_style" class="armswitch_label"></label>
						</div>
					</td>
				</tr>
                                
                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Auto Lock Shared Account','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
                                                    <?php $general_settings['autolock_shared_account'] = (isset($general_settings['autolock_shared_account'])) ? $general_settings['autolock_shared_account'] : 0; ?>
							<input type="checkbox" id="autolock_shared_account" <?php checked($general_settings['autolock_shared_account'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[autolock_shared_account]"/>
							<label for="autolock_shared_account" class="armswitch_label"></label>
						</div>
                                                <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('By enabling this feature, you can prevent simultaneous multiple logins using same login details','ARMember'); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Enable Gravatars?','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="enable_gravatar" <?php checked($general_settings['enable_gravatar'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[enable_gravatar]"/>
							<label for="enable_gravatar" class="armswitch_label"></label>
						</div>
                                                <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('if buddyPress plugin is active then use buddyPress avtars','ARMember'); ?>)</span>
					</td>
				</tr>
                
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Allow image cropping','ARMember');?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                         <?php $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0; ?>
                            <input type="checkbox" id="enable_crop" <?php checked($enable_crop, '1');?> value="1" class="armswitch_input" name="arm_general_settings[enable_crop]"/>
                            <label for="enable_crop" class="armswitch_label"></label>
                        </div>
                        <label for="enable_crop" class="arm_global_setting_switch_label"><?php _e('Allow avatar and cover photo cropping', 'ARMember');?> </label>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php echo esc_html('Enable spam protection','ARMember');?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                         <?php $spam_protection = isset($general_settings['spam_protection']) ? $general_settings['spam_protection'] : 0; ?>
                            <input type="checkbox" id="spam_protection" <?php checked($spam_protection, '1');?> value="1" class="armswitch_input" name="arm_general_settings[spam_protection]"/>
                            <label for="spam_protection" class="armswitch_label"></label>
                        </div>
                        <label for="spam_protection" class="arm_global_setting_switch_label"><?php _e('Enable hidden spam protection mechanism in signup/login forms
', 'ARMember');?> 
                    </td>
                </tr>
				<tr class="form-field" id="changeCurrency">
					<th class="arm-form-table-label"><?php _e('New user approval','ARMember');?></th>
					<td class="arm-form-table-content">
                                            <?php $general_settings['user_register_verification'] = isset($general_settings['user_register_verification']) ? $general_settings['user_register_verification'] : ''; ?>
						<input type='hidden' id='arm_new_user_approval' name="arm_general_settings[user_register_verification]" value="<?php echo $general_settings['user_register_verification'];?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_new_user_approval">
									<li data-label="<?php _e('Automatic approve','ARMember');?>" data-value="auto"><?php _e('Automatic approve', 'ARMember');?></li>
									<li data-label="<?php _e('Email verified approve','ARMember');?>" data-value="email"><?php _e('Email verified approve', 'ARMember');?></li>
									<li data-label="<?php _e('Manual approve by admin','ARMember');?>" data-value="manual"><?php _e('Manual approve by admin', 'ARMember');?></li>
								</ul>
							</dd>
						</dl>
					</td>
				</tr>
				<tr class="form-field" id="profilePermalinkBase">
					<th class="arm-form-table-label"><?php _e('Default currency','ARMember');?></th>
					<td class="arm-form-table-content">
						<?php 
						$currencies = apply_filters('arm_available_currencies', $currencies);
						$paymentcurrency = $general_settings['paymentcurrency'];
						$custom_currency_status = isset($general_settings['custom_currency']['status']) ? $general_settings['custom_currency']['status'] : '';
						$custom_currency_symbol = isset($general_settings['custom_currency']['symbol']) ? $general_settings['custom_currency']['symbol'] : '';
						$custom_currency_shortname = isset($general_settings['custom_currency']['shortname']) ? $general_settings['custom_currency']['shortname'] : '';
						$custom_currency_place = isset($general_settings['custom_currency']['place']) ? $general_settings['custom_currency']['place'] : '';
						?>
						<input type='hidden' id='arm_payment_currency' name="arm_general_settings[paymentcurrency]" value="<?php echo $paymentcurrency;?>" />
						<dl class="arm_selectbox column_level_dd arm_default_currency_box <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_payment_currency">
									<?php foreach ($currencies as $key => $value): ?>
									<li data-label="<?php echo $key . " ( $value ) ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo $key . " ( $value ) ";?></li>
									<?php endforeach;?>
								</ul>
							</dd>
						</dl>

                        <?php
                            $arm_specific_currency_position = isset($general_settings['arm_specific_currency_position']) ? $general_settings['arm_specific_currency_position'] : 'suffix';
                        ?>

                        <div class="arm_currency_prefix_suffix_display" <?php echo ($paymentcurrency != "EUR") ? "style='display: none;'" : ""; ?>>
                            <div>
                                <input type="radio" id="default_currency_prefix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked($arm_specific_currency_position, 'prefix');?> value="prefix" <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?> />
                                <label class="default_currency_prefix_suffix_lbl" for="default_currency_prefix_val" <?php echo ($custom_currency_status == 1) ? 'style="cursor: no-drop;"' : '';?>><?php _e('Prefix','ARMember');?></label>
                            </div>
                            <div>
                                <input type="radio" id="default_currency_suffix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked($arm_specific_currency_position, 'suffix');?> value="suffix" <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?> />
                                <label class="default_currency_prefix_suffix_lbl" for="default_currency_suffix_val" <?php echo ($custom_currency_status == 1) ? 'style="cursor: no-drop;"' : '';?>><?php _e('Suffix','ARMember');?></label>
                            </div>
                        </div>


						<div class="armclear"></div>
						<span style="width: 220px;text-align: center;display: inline-block;font-weight: bold;font-size: 16px;margin: 15px 0 1px;"><?php _e('OR', 'ARMember');?></span>
						<div class="armclear"></div>
						<div class="armGridActionTD arm_custom_currency_options_container">
							<input type="hidden" class="custom_currency_symbol" name="arm_general_settings[custom_currency][symbol]" value="<?php echo $custom_currency_symbol;?>">
							<input type="hidden" class="custom_currency_shortname" name="arm_general_settings[custom_currency][shortname]" value="<?php echo $custom_currency_shortname;?>">
							<input type="hidden" class="custom_currency_place" name="arm_general_settings[custom_currency][place]" value="<?php echo $custom_currency_place;?>">
							<div class="armclear"></div>
							<label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_custom_currency_checkbox arm_icheckbox" value="1" name="arm_general_settings[custom_currency][status]" <?php checked($custom_currency_status, 1)?>><span><?php _e('Set Custom Currency', 'ARMember');?></span></label>
							<div class="arm_confirm_box_custom_currency arm_no_hide" id="arm_confirm_box_custom_currency">
								<div class="arm_confirm_box_body" style="max-width: 100%;">
									<div class="arm_confirm_box_arrow"></div>
									<div class="arm_confirm_box_text arm_custom_currency_fields" style="text-align: left;">
										<table>
											<tr>
												<th><?php _e('Currency Symbol', 'ARMember');?></th>
												<td>
													<input type="text" id="custom_currency_symbol" value="<?php echo (!empty($custom_currency_symbol)) ? "$custom_currency_symbol" : ''; ?>">
													<span class="arm_error_msg symbol_error" style="display:none;"><?php _e('Please enter symbol.', 'ARMember');?></span>
													<span class="arm_error_msg invalid_symbol_error" style="display:none;"><?php _e('Please enter valid symbol.', 'ARMember');?></span>
												</td>
											</tr>
											<tr>
												<th><?php _e('Currency Shortname', 'ARMember');?></th>
												<td>
													<input type="text" id="custom_currency_shortname" value="<?php echo (!empty($custom_currency_shortname)) ? "$custom_currency_shortname" : ''; ?>">
													<span class="arm_error_msg shortname_error" style="display:none;"><?php _e('Please enter shortname.', 'ARMember');?></span>
													<span class="arm_error_msg invalid_shortname_error" style="display:none;"><?php _e('Please enter valid shortname.', 'ARMember');?></span>
												</td>
											</tr>
											<tr>
												<th><?php _e('Symbol will be display as', 'ARMember');?></th>
												<td>
													<input type="hidden" id="custom_currency_place" value="<?php echo (!empty($custom_currency_place)) ? $custom_currency_place : 'prefix'; ?>"/>
													<dl class="arm_selectbox column_level_dd">
														<dt style="width: 110px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="custom_currency_place">
																<li data-label="<?php _e('Prefix', 'ARMember');?>" data-value="prefix"><?php _e('Prefix', 'ARMember');?></li>
																<li data-label="<?php _e('Suffix', 'ARMember');?>" data-value="suffix"><?php _e('Suffix', 'ARMember');?></li>
															</ul>
														</dd>
													</dl>
												</td>
											</tr>
										</table>
									</div>
									<div class='arm_confirm_box_btn_container'>
										<button type="button" class="arm_confirm_box_btn armemailaddbtn" id="arm_custom_currency_ok_btn" style="margin-right: 5px;"><?php _e('Add', 'ARMember');?></button>
										<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideCustomCurrencyBox();"><?php _e('Cancel', 'ARMember');?></button>
									</div>
								</div>
							</div>
                            <div class="armclear"></div>
                            <span class="arm_custom_currency_text"><?php 
                            if (!empty($custom_currency_symbol) && !empty($custom_currency_shortname)) {
                                $currency_name = $custom_currency_shortname . " ( $custom_currency_symbol )";
                                echo '<span>'.__('Custom Currency', 'ARMember') . ": <strong>$currency_name</strong><a href='javascript:void(0)' class='arm_custom_currency_edit'>".__('Edit', 'ARMember')."</a></span>";
                            }
                            ?></span>
						</div>
						<div class="armclear"></div>
						<?php 
						if($custom_currency_status == 1){
							$paymentcurrency = $custom_currency_shortname;
						}
						$currency_warring = $arm_payment_gateways->arm_check_currency_status($paymentcurrency);
						?>
						<span class="arm_global_setting_currency_warring" style="<?php echo (empty($currency_warring)) ? 'display:none;' : '';?>"><?php echo $currency_warring;?></span>
					</td>
				</tr>

                <tr class="form-field" style="<?php echo (!$arm_social_feature->isSocialFeature) ? 'display:none;' : '';?>">
					<th class="arm-form-table-label"><?php _e('Profile Permalink Base','ARMember');?></th>
					<td class="arm-form-table-content">
                        <?php 
                        $permalink_base = (isset($general_settings['profile_permalink_base'])) ? $general_settings['profile_permalink_base'] : 'user_login';
                        if ($is_permalink) {
                            $profileUrl = trailingslashit(untrailingslashit($arm_global_settings->profile_url));
                            $profileUrl_user_login = $profileUrl . '<b>username</b>/';
                            $profileUrl_user_id = $profileUrl . '<b>user_id</b>/';
                        } else {
                            $profileUrl = $arm_global_settings->add_query_arg('arm_user', 'arm_base_slug', $arm_global_settings->profile_url);
                            $profileUrl_user_login = str_replace('arm_base_slug', '<b>username</b>', $profileUrl);
                            $profileUrl_user_id = str_replace('arm_base_slug', '<b>user_id</b>', $profileUrl);
                        }
                        ?>
						<input type='hidden' id="arm_profile_permalink_base" name="arm_general_settings[profile_permalink_base]" value="<?php echo $permalink_base;?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_profile_permalink_base">
									<li data-label="<?php _e('Username','ARMember');?>" data-value="user_login"><?php _e('Username', 'ARMember');?></li>
									<li data-label="<?php _e('User ID','ARMember');?>" data-value="user_id"><?php _e('User ID', 'ARMember');?></li>
									
								</ul>
							</dd>
						</dl>
                        <div class="armclear"></div>
                        <span class="arm_info_text arm_profile_user_login" style="<?php echo ($permalink_base == 'user_login')? '' : 'display: none;';?>">e.g. <?php echo $profileUrl_user_login;?></span>
                        <span class="arm_info_text arm_profile_user_id" style="<?php echo ($permalink_base == 'user_id')? '' : 'display: none;';?>">e.g. <?php echo $profileUrl_user_id;?></span>
					</td>
				</tr>
                                <?php 
			if (is_plugin_active('bbpress/bbpress.php')) {
                                    ?>
                               
                                
                                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('ARMember Profile page for bbPress','ARMember');?></th>
					<td class="arm-form-table-content">
                                            <?php 
                                            $arm_global_settings->arm_wp_dropdown_pages(
                                                    array(
                                                            'selected'              => (isset($general_settings['bbpress_profile_page']) ? $general_settings['bbpress_profile_page'] : 0),
                                                            'name'                  => 'arm_general_settings[bbpress_profile_page]',
                                                            'id'                    => 'bbpress_profile_page',
                                                            'show_option_none'      => 'Select Page',
                                                            'option_none_value'     => '0',
                                                    )
                                            );
                                            ?>
                                            <span class="arm_info_text" style="margin: 10px 0 0; display:block;">(<?php _e('Choose ARMember profile page to replace bbPress profile page.','ARMember'); ?>)</span>
					</td>
				</tr>
                                <?php
                                }
                                ?>
                                
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Load JS & CSS in all pages','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch" style="margin-top: 5px;">
							<input type="checkbox" id="arm_enqueue_all_js_css" <?php checked($general_settings['enqueue_all_js_css'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[enqueue_all_js_css]"/>
							<label for="arm_enqueue_all_js_css" class="armswitch_label"></label>
						</div>
						<span class="arm_info_text" style="margin: 10px 0 0;display: block;">(<strong><?php _e('Not recommended', 'ARMember');?></strong> - <?php _e('If you have any js/css loading issue in your theme, only in that case you should enable this settings', 'ARMember');?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Badge icon size', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<div style="display: inline-block;margin-top: -5px;">
							<span style="display: inline-block; min-width: 60px;"><?php _e('Width', 'ARMember'); ?></span>
							<input id="arm_badge_width" type="text" name="arm_general_settings[badge_width]" value="<?php echo (!empty($general_settings['badge_width']) ? $general_settings['badge_width'] : 30 ); ?>" style="width:80px;"><span> (px)</span>
						</div>
						<div style="margin-top: 5px;">
							<span style="display: inline-block; min-width: 60px;"><?php _e('Height', 'ARMember'); ?></span>
							<input id="arm_badge_height" type="text" name="arm_general_settings[badge_height]" value="<?php echo (!empty($general_settings['badge_height']) ? $general_settings['badge_height'] : 30 ); ?>" style="width:80px;"><span> (px)</span>
						</div>
					</td>
				</tr>
            </table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Email Settings','ARMember');?></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('From/Reply to name', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_name" type="text" name="arm_email_from_name" value="<?php echo (!empty($all_email_settings['arm_email_from_name']) ? stripslashes($all_email_settings['arm_email_from_name']) : get_option('blogname') ); ?>" >
                        <span id="email_from_name_error" class="arm_error_msg email_from_name_error" style="display:none;"><?php _e('Please enter From Name.', 'ARMember');?></span>
                         <span id="invalid_email_from_name_error" class="arm_error_msg invalid_email_from_name_error" style="display:none;"><?php _e('Please enter valid From Name.', 'ARMember');?></span>         
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('From/Reply to email', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_email" type="email" name="arm_email_from_email" value="<?php echo (!empty($all_email_settings['arm_email_from_email']) ? $all_email_settings['arm_email_from_email'] : get_option('admin_email') ); ?>" >
                                                <span id="email_from_email_error" class="arm_error_msg email_from_email_error" style="display:none;"><?php _e('Please enter From Email ID.', 'ARMember');?></span>
                                        <span id="invalid_email_from_email_error" class="arm_error_msg invalid_email_from_email_error" style="display:none;"><?php _e('Please enter valid From Email ID.', 'ARMember');?></span>
					</td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Admin email', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_admin_email" type="email" name="arm_email_admin_email" value="<?php echo (!empty($all_email_settings['arm_email_admin_email']) ? $all_email_settings['arm_email_admin_email'] : get_option('admin_email') ); ?>" >
                                                <span id="email_admin_email_error" class="arm_error_msg email_admin_email_error" style="display:none;"><?php _e('Please enter Admin Email ID.', 'ARMember');?></span>
                                        <span id="invalid_email_admin_email_error" class="arm_error_msg invalid_email_admin_email_error" style="display:none;"><?php _e('Please enter valid Admin Email ID.', 'ARMember');?></span>
					<?php $ae_tooltip = __("You can add multiple Admin email address separated by comma in case of you want to send email to more than one email address.", 'ARMember'); ?>
                                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $ae_tooltip; ?>"></i>
                                        </td>
				</tr>
				<tr class="form-field">
					<th class="arm_email_settings_content_label"><?php _e('Email notification','ARMember');?></th>
					<td class="arm_email_settings_content_text" style="vertical-align: top;padding: 10px;">
						<div class="arm_email_settings_select_text">
							<div class="arm_email_settings_select_text_inner">
							<?php $all_email_settings['arm_email_server'] = (isset($all_email_settings['arm_email_server'])) ? $all_email_settings['arm_email_server'] : 'wordpress_server';?>
								<input type="radio" id="arm_email_server_ws" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'wordpress_server');?> name="arm_email_server" value="wordpress_server" />
								<label for="arm_email_server_ws" class="arm_email_settings_help_text"><?php _e('Wordpress Server','ARMember');?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_smtps" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'smtp_server');?> name="arm_email_server" value="smtp_server" />
								<label for="arm_email_server_smtps" class="arm_email_settings_help_text"><?php _e('SMTP Server','ARMember');?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_phpm" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked($all_email_settings['arm_email_server'], 'phpmailer');?> name="arm_email_server" value="phpmailer" />
								<label for="arm_email_server_phpm" class="arm_email_settings_help_text"><?php _e('PHP Mailer','ARMember');?></label>
							</div>
						</div>
						<div class="arm_smtp_slide_form">
							<table class="form-sub-table" width="100%">

                                <tr>
                                    <th class="arm_email_settings_content_label" style="min-width: 100px;"><?php _e('Authentication','ARMember');?></th>
                                    <td class="arm_email_settings_content_text">
                                        <?php $arm_mail_authentication = (isset($all_email_settings['arm_mail_authentication'])) ? $all_email_settings['arm_mail_authentication'] : '1'; ?>
                                        
                                        
                                        <label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_icheckbox" value="1" id="arm_mail_authentication" name="arm_mail_authentication" onchange="arm_mail_authentication_func(this.value);" <?php checked($arm_mail_authentication, 1)?>><span><?php _e('Enable SMTP authentication', 'ARMember');?></span></label>
                                    </td>
                                </tr>
								<tr>
									<th class="arm_email_settings_content_label" style="min-width: 100px;"><?php _e('Mail Server','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_server = (isset($all_email_settings['arm_mail_server'])) ? $all_email_settings['arm_mail_server'] : ''; ?>
										<input type="text" id="arm_mail_server" name="arm_mail_server" value="<?php echo (isset($all_email_settings['arm_mail_server'])) ? $all_email_settings['arm_mail_server'] : '';?>" class="arm_mail_server_input" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_server_error" style="display: none;"><?php _e('Mail Server can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php _e('Port','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_port = (isset($all_email_settings['arm_mail_port'])) ? $all_email_settings['arm_mail_port'] : ''; ?>
										<input type="text" id="arm_port" name="arm_mail_port" value="<?php echo (isset($all_email_settings['arm_mail_port'])) ? $all_email_settings['arm_mail_port'] : '';?>" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_port_error" style="display: none;"><?php _e('Port can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_login_name_main" style="<?php if(empty($arm_mail_authentication)){ echo "display:none;"; }?>">
									<th class="arm_email_settings_content_label"><?php _e('Login Name','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_login_name = (isset($all_email_settings['arm_mail_login_name'])) ? $all_email_settings['arm_mail_login_name'] : ''; ?>
										<input type="text" id="arm_login_name" value="<?php echo (isset($all_email_settings['arm_mail_login_name'])) ? $all_email_settings['arm_mail_login_name'] : '';?>" name="arm_mail_login_name" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_login_name_error" style="display: none;"><?php _e('Login Name can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_password_main" style="<?php if(empty($arm_mail_authentication)){ echo "display:none;"; }?>">
									<th class="arm_email_settings_content_label"><?php _e('Password','ARMember');?> *</th>
									<td class="arm_email_settings_content_text">
                                                                            <?php $arm_mail_pssword = (isset($all_email_settings['arm_mail_password'])) ? $all_email_settings['arm_mail_password'] : ''; ?>
										<input type="password" id="arm_password" value="<?php echo (isset($all_email_settings['arm_mail_password'])) ? $all_email_settings['arm_mail_password'] : '';?>" name="arm_mail_password" style="width: 390px;"/>
										<span class="error arm_invalid" id="arm_mail_password_error" style="display: none;"><?php _e('Password can not be left blank.', 'ARMember');?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php _e('Encryption','ARMember');?></th>
									<td class="arm_email_settings_content_text">
										<div class="arm_email_settings_select_text">     	
											<div id="arm_first_enc" class="arm_email_settings_select_text_inner">
<?php
$selected_enc = (isset($all_email_settings['arm_smtp_enc'])) ? (($all_email_settings['arm_smtp_enc'] == 'ssl' || $all_email_settings['arm_smtp_enc'] == 'tls') ? '1' : '0' ) : '0';
												$all_email_settings['arm_smtp_enc'] = (isset($all_email_settings['arm_smtp_enc'])) ? $all_email_settings['arm_smtp_enc'] : '0';
												?>
												<input type="radio" id="arm_smtp_enc_none" class="arm_general_input arm_iradio" <?php checked( $selected_enc, '0' );?>  name="arm_smtp_enc" value="none" />
                                                                                                <label for="arm_smtp_enc_none" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('None','ARMember');?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_ssl" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'ssl' );?> name="arm_smtp_enc" value="ssl" />
												<label for="arm_smtp_enc_ssl" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('SSL','ARMember');?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_tls" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'tls' );?> name="arm_smtp_enc" value="tls" />
												<label for="arm_smtp_enc_tls" class="arm_email_settings_help_text" style="margin-right: 0px;"><?php _e('TLS','ARMember');?></label>
											</div>
										</div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"><b><?php _e('Send Test E-mail</b>', 'ARMember'); ?></b></th>
                                    <td class="arm_email_settings_content_text">
                                        <label id="arm_success_test_mail" style="font-size:14px; display:none; float:left; width:100%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#4c9738;"><?php _e('Your test mail is successfully sent.', 'ARMember'); ?></label>
                                        <label id="arm_error_test_mail" style="font-size:14px; display:none; float:left; width:100%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#ff0000;"><?php _e('Your test mail is not sent for some reason, Please check your SMTP setting.', 'ARMember'); ?></label>
                                    </td>
                                </tr> 
                                <tr>
                                    <th class="arm_email_settings_content_label"><?php _e('To', 'ARMember'); ?> *</th>
                                    <td class="arm_email_settings_content_text">
                                        <input type="text" id="arm_test_email_to" name="arm_test_email_to" value="" style="width: 390px;"/>
                                        <span class="error arm_invalid" id="arm_test_email_to_error" style="display: none;"><?php _e('To can not be left blank.', 'ARMember'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"><?php _e('Message', 'ARMember'); ?> *</th>
                                    <td class="arm_email_settings_content_text">
                                        <textarea id="arm_test_email_msg" value="" name="arm_test_email_msg" style="width: 390px;"></textarea>
                                        <span class="error arm_invalid" id="arm_test_email_msg_error" style="display: none;"><?php _e('Message can not be left blank.', 'ARMember'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="arm_email_settings_content_label"></th>
                                    <td class="arm_email_settings_content_text">
                                        
                                        <button type="button" class="arm_save_btn" id="arm_send_test_mail"><?php _e('Send test mail', 'ARMember');?></button><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif'; ?>" id="arm_send_test_mail_loader" width="24" height="24" style="position: relative; top: 8px; display: none;" /><br/><span style="font-style:italic;">(<?php _e('Test e-mail works only after configure SMTP server settings', 'ARMember'); ?>)</span>
                                    </td>
								 </tr>
							</table>
						</div>
					</td>
				</tr>
			</table>

            <?php
            if($arm_invoice_tax_feature == 1) {
                $enable_tax = isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;
                $tax_type = isset($general_settings['tax_type']) ? $general_settings['tax_type'] : 'common_tax';
                $tax_amount = isset($general_settings['tax_amount']) ? $general_settings['tax_amount'] : 0;
                $country_tax_default_val = isset($general_settings['arm_country_tax_default_val']) ? $general_settings['arm_country_tax_default_val'] : 0;
                $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';
                $country_tax_selected_opts = (isset($general_settings['arm_tax_country_name']) && $country_tax_field != '') ? maybe_unserialize($general_settings['arm_tax_country_name']) : array('');
                $country_tax_val_arr = (isset($general_settings['arm_country_tax_val']) && $country_tax_field != '') ? maybe_unserialize($general_settings['arm_country_tax_val']) : array('0');
                $total_selected_country = ($country_tax_field != '') ? count($country_tax_selected_opts) : 1;
                $country_tax_field_li = "<li data-label='".__('Select Country Field','ARMember')."' data-value=''>".__('Select Country Field', 'ARMember')."</li>";
                $country_tax_selected_field_opt_li = "<li data-label='".__('Select Country','ARMember')."' data-value=''>".__('Select Country', 'ARMember')."</li>";
                $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
                if(!empty($dbFormFields)) {
                    $all_fields = array();
                    foreach ($dbFormFields as $meta_key => $opts) {
                        if($opts["type"] == "radio" || $opts["type"] == "select") {
                            $all_fields[] = $meta_key;
                        }
                    }
                    if(!empty($all_fields)) {
                        $form_field_arr = $wpdb->get_results("SELECT `arm_form_field_option` FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_slug` IN('".implode("','", $all_fields)."')", ARRAY_A);
                        if(!empty($form_field_arr)) {
                            $form_field_arr = array_column($form_field_arr, "arm_form_field_option");
                            foreach ($form_field_arr as $key => $value) {
                                $arm_unsrlz_opts = maybe_unserialize($value);
                                $country_tax_field_li .= "<li data-label='".$arm_unsrlz_opts['label']."' data-value='".$arm_unsrlz_opts['meta_key']."'>".$arm_unsrlz_opts['label']."</li>";
                                if($country_tax_field != '' && $country_tax_field == $arm_unsrlz_opts['meta_key'] && !empty($arm_unsrlz_opts['options'])) {
                                    $country_tax_selected_field_opt_li = '';
                                    for($t = 0; $t < count($arm_unsrlz_opts['options']); $t++) {
                                        $li_label = $li_value = $arm_unsrlz_opts['options'][$t];
                                        if(strpos($arm_unsrlz_opts['options'][$t], ":") !== false) {
                                            $li_label = substr($arm_unsrlz_opts['options'][$t], 0, strpos($arm_unsrlz_opts['options'][$t], ":"));
                                            $li_value = substr($arm_unsrlz_opts['options'][$t], (strpos($arm_unsrlz_opts['options'][$t], ":")+1), strlen($arm_unsrlz_opts['options'][$t]));
                                        }
                                        $country_tax_selected_field_opt_li .= "<li data-label='".$li_label."' data-value='".$li_value."'>".$li_label."</li>";
                                    }
                                }
                            }
                        }
                        else {
                            $country_tax_field = '';
                            $country_tax_selected_opts = array('');
                        }
                    }
                    else {
                        $country_tax_field = '';
                        $country_tax_selected_opts = array('');
                    }
                }
                else {
                    $country_tax_field = '';
                    $country_tax_selected_opts = array('');
                }
            ?>
            <div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Sales Tax Settings','ARMember');?></div>
            <table class="form-table">
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Sales Tax','ARMember');?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                            <input type="checkbox" id="enable_tax" <?php checked($enable_tax, '1');?> value="1" class="armswitch_input" name="arm_general_settings[enable_tax]"/>
                            <label for="enable_tax" class="armswitch_label"></label>
                        </div>
                        <label for="enable_tax" class="arm_global_setting_switch_label"><?php _e('Enable tax module on plan purchase?','ARMember');?></label>
                        <input type="hidden" id="arm_selected_tax_type" value="<?php echo $tax_type; ?>">
                    </td>
                </tr>

                <tr class="form-field arm_enable_tax <?php echo ($enable_tax == '1') ? '' : 'hidden_section'; ?>">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content">
                        <input type="radio" id="arm_common_tax" class="arm_general_input arm_tax_type_radio arm_iradio" name="arm_general_settings[tax_type]" value="common_tax" <?php echo $tax_type == 'common_tax' ? 'checked' : ''?> />
                        <label for="arm_common_tax" class="arm_email_settings_help_text"><?php _e('Common Tax','ARMember');?>&nbsp;(<?php _e('for all countries','ARMember');?>)</label>
                    </td>
                </tr>
                <tr class="form-field arm_enable_tax arm_enable_common_tax <?php echo ($enable_tax == '1' && $tax_type == 'common_tax') ? '' : 'hidden_section'; ?>">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content arm_common_tax_td">
                        <input type="text" name="arm_general_settings[tax_amount]" id="tax_amount" value="<?php echo $tax_amount; ?>" placeholder="0"  onkeypress="return isNumber(event)"><b> %</b>

                        <span class="arm_info_text">
                            (<?php _e('Percentage tax will be applied on Final Payable Amount on plan+signup page.','ARMember'); ?>)
                        </span>
                    </td>
                </tr>

                <tr class="form-field arm_enable_tax <?php echo ($enable_tax == '1') ? '' : 'hidden_section'; ?>">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content">
                        <input type="radio" id="arm_country_tax" class="arm_general_input arm_tax_type_radio arm_iradio" name="arm_general_settings[tax_type]" value="country_tax" <?php echo $tax_type == 'country_tax' ? 'checked' : ''?> />
                        <label for="arm_country_tax" class="arm_email_settings_help_text"><?php _e('Countrywise Tax','ARMember');?></label>
                    </td>
                </tr>
                <tr class="form-field arm_enable_tax arm_enable_country_tax <?php echo ($enable_tax == '1' && $tax_type == 'country_tax') ? '' : 'hidden_section'; ?>">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content arm_country_tax_td">
                        <?php _e('Select Country Field','ARMember');?>
                        <input type='hidden' id='arm_country_tax_field' name="arm_general_settings[country_tax_field]" value="<?php echo $country_tax_field;?>" />
                        <dl class="arm_selectbox column_level_dd arm_country_tax_field_list">
                            <dt class="arm_country_tax_field_list_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_country_tax_field"><?php echo $country_tax_field_li; ?></ul>
                            </dd>
                            <span class="arm_country_field_err error arm_invalid hidden_section"><?php _e("Please Select Country Field", "ARMember"); ?></span>
                        </dl>
                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("select country field of signup form to be mapped, so tax can be applied upon country selection.", 'ARMember'); ?>"></i>
                        <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif'; ?>" class="hidden_section" id="arm_country_tax_loader" />
                    </td>
                </tr>
                <?php for($x = 0; $x < $total_selected_country; $x++) { ?>
                <tr class="form-field arm_enable_tax arm_enable_country_tax arm_country_tr <?php echo ($enable_tax == '1' && $tax_type == 'country_tax') ? '' : 'hidden_section'; ?>" data-ttl-tr='<?php echo $total_selected_country; ?>'>
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content arm_country_tax_td">
                        <div class="arm_tax_country_list_wrapper">
                            <input type='hidden' class="arm_country_tax_field_inpt" id='arm_country_tax_field_list_<?php echo $x?>' name="arm_general_settings[arm_tax_country_name][]" value="<?php echo !empty($country_tax_selected_opts[$x]) ? $country_tax_selected_opts[$x] : ''; ?>" />
                            <dl class="arm_selectbox column_level_dd arm_tax_country_list_dl">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_country_tax_field_list_<?php echo $x?>"><?php echo $country_tax_selected_field_opt_li ?></ul>
                                </dd>
                            </dl>

                            <input type="text" name="arm_general_settings[arm_country_tax_val][]" class="arm_country_tax_val_inpt" id="arm_country_tax_val_<?php echo $x?>" value="<?php echo !empty($country_tax_val_arr[$x]) ? $country_tax_val_arr[$x] : '0'; ?>" style="max-width: 100px;"  onkeypress="return isNumber(event)"><b> %</b>
                            <div class="arm_country_tax_action_buttons">
                                <div class="arm_country_tax_plus_icon arm_helptip_icon tipso_style" title="<?php _e('Add Country', 'ARMember'); ?>"></div>
                                <div class="arm_country_tax_minus_icon arm_helptip_icon tipso_style" title="<?php _e('Remove Country', 'ARMember'); ?>"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <tr class="form-field arm_enable_tax arm_enable_country_tax <?php echo ($enable_tax == '1' && $tax_type == 'country_tax') ? '' : 'hidden_section'; ?>">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content arm_country_tax_td">
                        <?php _e('Default Tax','ARMember');?>
                        
                        <input type="text" name="arm_general_settings[arm_country_tax_default_val]" id="arm_country_tax_default_val" value="<?php echo $country_tax_default_val?>" style="max-width: 100px;" placeholder="0"  onkeypress="return isNumber(event)"><b> %</b>
                        <i class="arm_helptip_icon armfa armfa-question-circle arm_helptip_icon_dtax" title="<?php _e("For any other country which is not in above list.", 'ARMember'); ?>"></i>
                    </td>
                </tr>
            </table>
            <?php
            
            $invc_pre_sfx_mode = isset($general_settings['invc_pre_sfx_mode']) ? $general_settings['invc_pre_sfx_mode'] : 0;
            $invc_prefix_val = isset($general_settings['invc_prefix_val']) ? $general_settings['invc_prefix_val'] : '#';
            $invc_suffix_val = isset($general_settings['invc_suffix_val']) ? $general_settings['invc_suffix_val'] : '';
            $invc_min_digit = isset($general_settings['invc_min_digit']) ? $general_settings['invc_min_digit'] : 0;
            ?>
            <div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Invoice Prefix/Suffix Settings','ARMember');?></div>
            <table class="form-table">
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Invoice Prefix/Suffix','ARMember');?></th>
                    <td class="arm-form-table-content" colspan="3">
                        <div class="armswitch arm_global_setting_switch">
                            <input type="checkbox" id="invc_pre_sfx_mode" <?php checked($invc_pre_sfx_mode, '1');?> value="1" class="armswitch_input" name="arm_general_settings[invc_pre_sfx_mode]"/>
                            <label for="invc_pre_sfx_mode" class="armswitch_label"></label>
                        </div>
                        <label for="invc_pre_sfx_mode" class="arm_global_setting_switch_label"><?php _e('Enable Invoice Prefix/Suffix ?','ARMember');?></label>
                    </td>
                </tr>
                <tr class="form-field arm_invc_pre_sfx_tr<?php echo ($invc_pre_sfx_mode == '1') ? '' : ' hidden_section' ; ?>">
                    <th class="arm-form-table-label"><?php _e('Enter Invoice Prefix', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <div style="display: inline-block;margin-top: -5px;">
                            <input type="text" name="arm_general_settings[invc_prefix_val]" id="arm_invc_prefix_val" value="<?php echo $invc_prefix_val; ?>" style="max-width: 80px;">
                        </div>
                    </td>
                </tr>
                <tr class="form-field arm_invc_pre_sfx_tr<?php echo ($invc_pre_sfx_mode == '1') ? '' : ' hidden_section' ; ?>">
                    <th class="arm-form-table-label"><?php _e('Enter Invoice Suffix', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <div style="display: inline-block;margin-top: -5px;">
                            <input type="text" name="arm_general_settings[invc_suffix_val]" id="arm_invc_suffix_val" value="<?php echo $invc_suffix_val; ?>" style="max-width: 80px;">
                        </div>
                    </td>
                </tr>
                <tr class="form-field arm_invc_pre_sfx_tr<?php echo ($invc_pre_sfx_mode == '1') ? '' : ' hidden_section' ; ?>">
                    <th class="arm-form-table-label"><?php _e('Enter Minimum Invoice Digit(s)', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <div style="display: inline-block;margin-top: -5px;">
                            <input type="number" name="arm_general_settings[invc_min_digit]" id="arm_invc_min_digit" value="<?php echo $invc_min_digit; ?>" style="max-width: 80px;">
                        </div>
                    </td>
                </tr>
            </table>
            <?php
            }
            ?>
			<div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Google reCAPTCHA Configuration', 'ARMember'); ?>
            <?php 
                $arm_recaptcha_tooltip = __("reCAPTCHA requires an API key, consisting of a 'site' and a 'private' key. You can sign up for a", 'ARMember').' <a href="https://www.google.com/recaptcha/admin" target="_blank">'.__('free reCAPTCHA key.', 'ARMember').'</a>';
            ?>
            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo htmlentities($arm_recaptcha_tooltip);?>" ></i>
            </div>
            <table class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th><?php _e('Site Key', 'ARMember'); ?> </th>
                        <td>
                            <?php $arm_recaptcha_site_key = isset($general_settings['arm_recaptcha_site_key']) ? $general_settings['arm_recaptcha_site_key'] : ''; ?>
                            <input type="text" name="arm_general_settings[arm_recaptcha_site_key]" id="arm_recaptcha_site_key" value="<?php echo $arm_recaptcha_site_key ?>" />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th><?php _e('Private Key', 'ARMember'); ?> </th>
                        <td>
                            <?php $arm_recaptcha_private_key = isset($general_settings['arm_recaptcha_private_key']) ? $general_settings['arm_recaptcha_private_key'] : ''; ?>
                            <input type="text" name="arm_general_settings[arm_recaptcha_private_key]" id="arm_recaptcha_private_key" value="<?php echo $arm_recaptcha_private_key ?>" />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th><?php _e('reCAPTCHA Theme', 'ARMember'); ?> </th>
                        <td>
                            <?php $arm_recaptcha_theme = !empty($general_settings['arm_recaptcha_theme']) ? $general_settings['arm_recaptcha_theme'] : 'light'; ?>
                            <input type='hidden' id='arm_recaptcha_theme' name="arm_general_settings[arm_recaptcha_theme]" value="<?php echo $arm_recaptcha_theme;?>" />
                            <dl class="arm_selectbox column_level_dd">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_recaptcha_theme">
                                        <li data-label="<?php _e('Light','ARMember');?>" data-value="light"><?php _e('Light', 'ARMember');?></li>
                                        <li data-label="<?php _e('Dark','ARMember');?>" data-value="dark"><?php _e('Dark', 'ARMember');?></li>
                                    </ul>
                                </dd>
                            </dl>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th><?php _e('reCAPTCHA Language', 'ARMember'); ?> </th>
                        <td>
                            <?php
                            $arm_rc_lang_list_option = '';
                            $arm_rc_selected_list_id = 'en';
                            $arm_rc_selected_list_label = __('English (US)', 'ARMember');
                            $arm_rc_lang = array();
                            $arm_rc_lang['en'] = __('English (US)', 'ARMember');
                            $arm_rc_lang['ar'] = __('Arabic', 'ARMember');
                            $arm_rc_lang['bn'] = __('Bengali', 'ARMember');
                            $arm_rc_lang['bg'] = __('Bulgarian', 'ARMember');
                            $arm_rc_lang['ca'] = __('Catalan', 'ARMember');
                            $arm_rc_lang['zh-CN'] = __('Chinese(Simplified)', 'ARMember');
                            $arm_rc_lang['zh-TW'] = __('Chinese(Traditional)', 'ARMember');
                            $arm_rc_lang['hr'] = __('Croatian', 'ARMember');
                            $arm_rc_lang['cs'] = __('Czech', 'ARMember');
                            $arm_rc_lang['da'] = __('Danish', 'ARMember');
                            $arm_rc_lang['nl'] = __('Dutch', 'ARMember');
                            $arm_rc_lang['en-GB'] = __('English (UK)', 'ARMember');
                            $arm_rc_lang['et'] = __('Estonian', 'ARMember');
                            $arm_rc_lang['fil'] = __('Filipino', 'ARMember');
                            $arm_rc_lang['fi'] = __('Finnish', 'ARMember');
                            $arm_rc_lang['fr'] = __('French', 'ARMember');
                            $arm_rc_lang['fr-CA'] = __('French (Canadian)', 'ARMember');
                            $arm_rc_lang['de'] = __('German', 'ARMember');
                            $arm_rc_lang['gu'] = __('Gujarati', 'ARMember');
                            $arm_rc_lang['de-AT'] = __('German (Autstria)', 'ARMember');
                            $arm_rc_lang['de-CH'] = __('German (Switzerland)', 'ARMember');
                            $arm_rc_lang['el'] = __('Greek', 'ARMember');
                            $arm_rc_lang['iw'] = __('Hebrew', 'ARMember');
                            $arm_rc_lang['hi'] = __('Hindi', 'ARMember');
                            $arm_rc_lang['hu'] = __('Hungarian', 'ARMember');
                            $arm_rc_lang['id'] = __('Indonesian', 'ARMember');
                            $arm_rc_lang['it'] = __('Italian', 'ARMember');
                            $arm_rc_lang['ja'] = __('Japanese', 'ARMember');
                            $arm_rc_lang['kn'] = __('Kannada', 'ARMember');
                            $arm_rc_lang['ko'] = __('Korean', 'ARMember');
                            $arm_rc_lang['lv'] = __('Latvian', 'ARMember');
                            $arm_rc_lang['lt'] = __('Lithuanian', 'ARMember');
                            $arm_rc_lang['ms'] = __('Malay', 'ARMember');
                            $arm_rc_lang['ml'] = __('Malayalam', 'ARMember');
                            $arm_rc_lang['mr'] = __('Marathi', 'ARMember');
                            $arm_rc_lang['no'] = __('Norwegian', 'ARMember');
                            $arm_rc_lang['fa'] = __('Persian', 'ARMember');
                            $arm_rc_lang['pl'] = __('Polish', 'ARMember');
                            $arm_rc_lang['pt'] = __('Portuguese', 'ARMember');
                            $arm_rc_lang['pt-BR'] = __('Portuguese (Brazil)', 'ARMember');
                            $arm_rc_lang['pt-PT'] = __('Portuguese (Portugal)', 'ARMember');
                            $arm_rc_lang['ro'] = __('Romanian', 'ARMember');
                            $arm_rc_lang['ru'] = __('Russian', 'ARMember');
                            $arm_rc_lang['sr'] = __('Serbian', 'ARMember');
                            $arm_rc_lang['sk'] = __('Slovak', 'ARMember');
                            $arm_rc_lang['sl'] = __('Slovenian', 'ARMember');
                            $arm_rc_lang['es'] = __('Spanish', 'ARMember');
                            $arm_rc_lang['es-149'] = __('Spanish (Latin America)', 'ARMember');
                            $arm_rc_lang['sv'] = __('Swedish', 'ARMember');
                            $arm_rc_lang['ta'] = __('Tamil', 'ARMember');
                            $arm_rc_lang['te'] = __('Telugu', 'ARMember');
                            $arm_rc_lang['th'] = __('Thai', 'ARMember');
                            $arm_rc_lang['tr'] = __('Turkish', 'ARMember');
                            $arm_rc_lang['uk'] = __('Ukrainian', 'ARMember');
                            $arm_rc_lang['ur'] = __('Urdu', 'ARMember');
                            $arm_rc_lang['vi'] = __('Vietnamese', 'ARMember');
                            ?>
                            <?php
                            foreach ($arm_rc_lang as $lang => $lang_name) {
                                if (isset($general_settings['arm_recaptcha_lang']) && $general_settings['arm_recaptcha_lang'] == $lang) {
                                    $arm_rc_selected_list_id = esc_attr($lang);
                                    $arm_rc_selected_list_label = $lang_name;
                                }
                                $arm_rc_lang_list_option .= '<li data-label="' . $lang_name . '" data-value="' . esc_attr($lang) . '" >' . $lang_name . '</li>';
                            }
                            ?>
                            <input type='hidden' id='arm_recaptcha_lang' name="arm_general_settings[arm_recaptcha_lang]" value="<?php echo $arm_rc_selected_list_id;?>" />
                            <dl class="arm_selectbox column_level_dd">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_recaptcha_lang">
                                        <?php echo $arm_rc_lang_list_option; ?>
                                    </ul>
                                </dd>
                            </dl>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Manage Preset Form Fields', 'ARMember'); ?></div>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                        <div class="arm_manage_preset_fields">
                        <div class="arm_manage_preset_fields_btn">
                         <input type="button" value="<?php _e('Edit Preset Form Fields', 'ARMember'); ?>" onclick="arm_open_edit_field_popup();" id="arm_edit_form_fields" class="armemailaddbtn" title="" style="width: 200px;">
                         </div>
                         <div class="arm_manage_preset_fields_text">
                            <span class="arm_info_text"><?php _e('To edit specific form preset fields, click on this button, popup opens, edit fields which you want to update and click on update button.','ARMember'); ?></span>
                            </div>
                         </div>
                         <div class="arm_manage_preset_fields" style="margin-top: 30px;">
                         <div class="arm_manage_preset_fields_btn">
                            <input type="button" value="<?php _e('Clear Preset Form Fields', 'ARMember'); ?>" onclick="arm_open_clear_field_popup();" id="arm_clear_form_fields" class="armemailaddbtn"  style="width: 200px;">
                            </div>
                            <div class="arm_manage_preset_fields_text">
                                <span class="arm_info_text"><?php _e('To remove specific form fields with its value, click on this button, popup opens, select fields which you want to remove from everywhere.','ARMember'); ?></span>
                                </div>
                            </div>
                          
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="arm_solid_divider"></div>
            <div class="page_sub_title"><?php _e('Email notification scheduler setting', 'ARMember'); ?>
            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("when you change value from below dropdown and save it then it will set new schedular and remove previous one.", 'ARMember'); ?>"></i>
            </div>
            <table class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th><?php _e('Schedule Every', 'ARMember'); ?> </th>
                        <td>
                            <?php $arm_email_schedular_time = isset($general_settings['arm_email_schedular_time']) ? $general_settings['arm_email_schedular_time'] : 12; ?>
                            <input type="hidden" name="arm_general_settings[arm_email_schedular_time]" id="arm_email_schedular_time" value="<?php echo $arm_email_schedular_time ?>" />
                            <dl class="arm_selectbox column_level_dd">
                                <dt style="min-width:40px;max-width:40px;">
                                <span></span>
                                <input type="text" style="display:none;" value="" class="arm_autocomplete"  />
                                <i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
                                <dd>
                                    <ul data-id="arm_email_schedular_time" style="display:none;">
                                        <?php
                                        for ($ct = 1; $ct <= 24; $ct++) {
                                            echo "<li data-value='{$ct}' data-label='{$ct}'>{$ct}</li>";
                                        }
                                        ?>
                                    </ul>
                                </dd>
                            </dl>
                            <span><?php _e('Hours','ARMember'); ?></span>
                        </td>
                    </tr>
                    <?php do_action('arm_cron_schedular_from_outside'); ?>
                </tbody>
            </table>
            <div class="arm_solid_divider"></div>
            <?php
            $frontfontOptions = array(
                'level_1_font' => __('Level 1', 'ARMember'),
                'level_2_font' => __('Level 2', 'ARMember'),
                'level_3_font' => __('Level 3', 'ARMember'),
                'level_4_font' => __('Level 4', 'ARMember'),
                'link_font' => __('Links', 'ARMember'),
                'button_font' => __('Buttons', 'ARMember'),
            );
            $frontfontOptions = apply_filters('arm_front_font_settings_type', $frontfontOptions);
            ?>
            <?php if (!empty($frontfontOptions)): ?>
                <div class="page_sub_title"><?php _e('Front End Font Settings', 'ARMember'); ?></div>
                <table class="form-table">
                    <?php
                    $frontOptHtml = '';
                    $frontOptions = isset($general_settings['front_settings']) ? $general_settings['front_settings'] : array();
                    foreach ($frontfontOptions as $key => $title) {
                        $fontVal = ((!empty($frontOptions[$key])) ? $frontOptions[$key] : array());
                        $font_bold = (isset($fontVal['font_bold']) && $fontVal['font_bold'] == '1') ? 1 : 0;
                        $font_italic = (isset($fontVal['font_italic']) && $fontVal['font_italic'] == '1') ? 1 : 0;
                        $font_decoration = (isset($fontVal['font_decoration'])) ? $fontVal['font_decoration'] : '';
                        $frontOptHtml .= '<tr class="form-field">';
                        $frontOptHtml .= '<th class="arm-form-table-label">' . $title;
                        if ($key == 'level_1_font') {
                            $tooltip_title = __("Font settings of Level 1 will be applied to main heading of frontend shortcodes. Like Transaction listing heading and like wise.", 'ARMember');
                        } elseif ($key == 'level_2_font') {
                            $tooltip_title = __("Font settings of Level 2 will be applied to sub heading ( Main Labels ) of frontend shortcodes. For example table heading of trasanction listing.", 'ARMember');
                        } elseif ($key == 'level_3_font') {
                            $tooltip_title = __("Font settings of Level 3 will be applied to sub labels of frontend shortcodes. For example table content of trasanction listing.", 'ARMember');
                        } elseif ($key == 'level_4_font') {
                            $tooltip_title = __("Font settings of Level 4 will be applied to very small labels of frontend shortcodes. For member listing etc.", 'ARMember');
                        } elseif ($key == 'link_font') {
                            $tooltip_title = __("Font settings of Links will be applied to links of frontend shortcodes. For example edit profile, logout link and profile links etc.", 'ARMember');
                        } elseif ($key == 'button_font') {
                            $tooltip_title = __("Font settings of Buttons will be applied to buttons of frontend shortcodes output. For example Renew button, Cancel Button, Make Payment Button etc.", 'ARMember');
                        }
                        $frontOptHtml .= ' <i class="arm_helptip_icon armfa armfa-question-circle" title="' . $tooltip_title . '"></i></th>';
                        $frontOptHtml .= '<td>';
                        $frontOptHtml .= '<input type="hidden" id="arm_front_font_family_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_family]" value="' . ((!empty($fontVal['font_family'])) ? $fontVal['font_family'] : 'Helvetica') . '"/>';
                        $frontOptHtml .= '<dl class="arm_selectbox column_level_dd">';
                        $frontOptHtml .= '<dt style="width:160px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $frontOptHtml .= '<dd><ul data-id="arm_front_font_family_' . $key . '">';
                        $frontOptHtml .= $arm_member_forms->arm_fonts_list();
                        $frontOptHtml .= '</ul></dd>';
                        $frontOptHtml .= '</dl>';
                        $frontOptHtml .= '<input type="hidden" id="arm_front_font_size_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_size]" value="' . (!empty($fontVal['font_size']) ? $fontVal['font_size'] : '14') . '"/>';
                        $frontOptHtml .= '<dl class="arm_selectbox column_level_dd">';
                        $frontOptHtml .= '<dt style="width:70px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                        $frontOptHtml .= '<dd><ul data-id="arm_front_font_size_' . $key . '">';
                        for ($i = 8; $i < 41; $i++) {
                            $frontOptHtml .= '<li data-label="' . $i . ' px" data-value="' . $i . '">' . $i . ' px</li>';
                        }
                        $frontOptHtml .= '</ul></dd>';
                        $frontOptHtml .= '</dl>';
                        $frontOptHtml .= '<div class="arm_front_font_color">';
                        $frontOptHtml .= '<input type="text" id="arm_front_font_color_' . $key . '" name="arm_general_settings[front_settings][' . $key . '][font_color]" class="arm_colorpicker" value="' . (!empty($fontVal['font_color']) ? $fontVal['font_color'] : '#000000') . '">';
                        $frontOptHtml .= '</div>';
                        $frontOptHtml .= '<div class="arm_font_style_options arm_front_font_style_options">';
                        $frontOptHtml .= '<label class="arm_font_style_label ' . (($font_bold == '1') ? 'arm_style_active' : '') . '" data-value="bold" data-field="arm_front_font_bold_' . $key . '"><i class="armfa armfa-bold"></i></label>';
                        $frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . $key . '][font_bold]" id="arm_front_font_bold_' . $key . '" class="arm_front_font_bold_' . $key . '" value="' . $font_bold . '" />';
                        $frontOptHtml .= '<label class="arm_font_style_label ' . (($font_italic == '1') ? 'arm_style_active' : '') . '" data-value="italic" data-field="arm_front_font_italic_' . $key . '"><i class="armfa armfa-italic"></i></label>';
                        $frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . $key . '][font_italic]" id="arm_front_font_italic_' . $key . '" class="arm_front_font_italic_' . $key . '" value="' . $font_italic . '" />';

									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='underline')? 'arm_style_active' : '').'" data-value="underline" data-field="arm_front_font_decoration_'.$key.'"><i class="armfa armfa-underline"></i></label>';
									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label '.(($font_decoration=='line-through')? 'arm_style_active' : '').'" data-value="line-through" data-field="arm_front_font_decoration_'.$key.'"><i class="armfa armfa-strikethrough"></i></label>';
									$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings]['.$key.'][font_decoration]" id="arm_front_font_decoration_'.$key.'" class="arm_front_font_decoration_'.$key.'" value="'.$font_decoration.'" />';
								$frontOptHtml .= '</div>';
							$frontOptHtml .= '</td>';
						$frontOptHtml .= '</tr>';
					}
					echo $frontOptHtml;
					?>	
				</table>
				<div class="arm_solid_divider"></div>
			<?php endif;?>
			<div class="page_sub_title"><?php _e('Global CSS','ARMember');?>
				<i class="arm_helptip_icon armfa armfa-question-circle arm_fix_toltip" title="<?php _e("The css you have entered here, will be applied to all the frontend pages which contains ARMember short code.", 'ARMember');?>"></i>
			</div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Custom CSS','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="arm_custom_css_wrapper">
                                                    <?php $general_settings['global_custom_css'] = isset($general_settings['global_custom_css']) ? $general_settings['global_custom_css'] : ''; ?>
							<textarea class="arm_codemirror_field" name="arm_general_settings[global_custom_css]" rows="9" cols="40"><?php echo stripslashes_deep($general_settings['global_custom_css']);?></textarea>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"></th>
					<td class="arm-form-table-content">
						<span class="arm_global_setting_custom_css_eg">(e.g.)&nbsp;&nbsp; .arm_form_field_submit_button{color:#000000;}</span>
						<span class="arm_global_setting_custom_css_section">
							<a class="arm_custom_css_detail arm_custom_css_detail_link" href="javascript:void(0)" data-section="arm_general"><?php _e('CSS Class Information', 'ARMember');?></a>
						</span>
					</td>
				</tr>
			</table>
			<?php do_action('arm_after_global_settings_html', $general_settings);?>
			<div class="arm_submit_btn_container">
				<button id="arm_global_settings_btn" class="arm_save_btn" name="arm_global_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
			</div>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
		</form>
	</div>
	<div class="armclear"></div>
	<div class="arm_custom_css_detail_container"></div>
    <div class="arm_edit_form_fields_popup_div popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>" style="width:1000px; min-height: 200px;">
            <form method="GET" id="arm_edit_preset_fields_form" class="arm_admin_form">
                <div>
                    <div class="popup_header">
                        <span class="popup_close_btn arm_popup_close_btn arm_edit_preset_fields_close_btn"></span>
                        
                        <span class="add_rule_content"><?php _e('Edit Preset Fields', 'ARMember'); ?></span>
                    </div>
                    <div class="popup_content_text arm_edit_form_fields_popup_text" style="text-align:center;">
                            <div style="width: 100%; margin: 45px auto;">   <img src="<?php echo MEMBERSHIP_IMAGES_URL."/arm_loader.gif"; ?>">
                            </div>
                    </div>
                    <div class="popup_content_btn popup_footer">
                        <div class="arm_preset_field_updated_msg">
                                <span class="arm_success_msg"><?php _e('Preset Fields are updated successfully.', 'ARMember'); ?></span>
                                <span class="arm_error_msg"><?php _e('Sorry, something went wrong while updating prest fields.', 'ARMember'); ?></span>
                        </div>
                        <div class="popup_content_btn_wrapper">
                            <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_loader_img_preset_update_field" class="arm_loader_img" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
                            <button class="arm_save_btn arm_edit_preset_fields_button" type="button"><?php _e('Update', 'ARMember') ?></button>
                            <button class="arm_cancel_btn arm_edit_preset_fields_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                        </div>
                    </div>
                    <div class="armclear"></div>
                </div>
            </form>
    </div>
    <div id='arm_clear_form_fields_popup_div' class="popup_wrapper">
        <form method="post" action="#" id="arm_clear_form_fields_frm" class="arm_admin_form">
            <table  cellspacing="0">
                <tr>
                    <td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
                    <td class="popup_header"><?php _e('Clear Form Fields', 'ARMember'); ?></td>
                    <td class="popup_content_text arm_clear_field_wrapper">
                        <?php
                        global $arm_member_forms;
                        $dbProfileFields = $arm_member_forms->arm_get_db_form_fields();
                        
               
                        
                        if (!empty($dbProfileFields['default'])) {

                            foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                    continue;
                                }
                                ?>
                                <label class = "account_detail_radio arm_account_detail_options">
                                    <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>"  checked="checked" disabled="disabled" />
                                    <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo stripslashes_deep($fieldOpt['label']); ?></label>
                                    <div class="arm_list_sortable_icon"></div>
                                </label>
                                <?php
                            }
                        }


                        if (!empty($dbProfileFields['other'])) {

                            foreach ($dbProfileFields['other'] as $fieldMetaKey => $fieldOpt) {
                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                    continue;
                                }
                                $fchecked = '';
                                if ($wpdb->get_var("SELECT count(`arm_form_field_slug`) FROM `" . $ARMember->tbl_arm_form_field . "` WHERE `arm_form_field_slug`='" . $fieldMetaKey . "'") > 0) {
                                    $fchecked = ' checked="checked" disabled="disabled" ';
                                }
                                ?>
                                <label class = "account_detail_radio arm_account_detail_options">
                                    <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>" <?php echo $fchecked;
                                ?>/>
                                    <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo stripslashes_deep($fieldOpt['label']); ?></label>
                                    <?php
                                    if ($fchecked == '' && $wpdb->get_var("SELECT count(`meta_key`) FROM `" . $wpdb->prefix . "usermeta` WHERE `meta_key`='" . $fieldMetaKey . "'") > 0) {
                                        ?><span style="color:red;"><?php _e('(Entry Exists)', 'ARMember'); ?></span>
                                    <?php }
                                    ?>
                                    <div class="arm_list_sortable_icon"></div>
                                </label>
                                <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="popup_content_btn popup_footer">
                        <div class="popup_content_btn_wrapper">
                            <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_loader_img_clear_field" class="arm_loader_img" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
                            <button class="arm_save_btn arm_clear_form_fields_button" type="submit" data-type="add"><?php _e('Ok', 'ARMember') ?></button>
                            <button class="arm_cancel_btn arm_clear_field_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<div id="armresetlicenseform" style="display:none;">
		
		<div class="arfnewmodalclose" onclick="javascript:return false;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/close-button.png'; ?>" align="absmiddle" /></div>
        <div class="newform_modal_title_container">
        	<div class="newform_modal_title">&nbsp;RESET LICENSE</div>
    	</div>
       <div class="newmodal_field_title"><?php _e('Please submit this form if you have trouble activating license.', 'ARMember'); ?></div>
        <iframe style="display:block; height:100%; width:100%; margin-top:0px;" frameborder="0" name="test" id="armresetlicframe" src="" hspace="0"></iframe>
</div> 

<div id='arm_rename_wp_admin_popup_div' class="popup_wrapper" style="width: 800px;">    
                <table  cellspacing="0">
                    <tr>
                        <td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
                        <td class="popup_header"><?php _e('Important Notes for Rename', 'ARMember'); ?> wp-admin</td>
                        <td class="popup_content_text arm_rename_wpadmin_wrapper" style="">
                            <ol>
                                <li>
                                    <?php _e('Do Not change permalink structure to default in order to work this option. if you set permalink structure to default, You will need to DELETE or comment (//) line which start with', 'ARMember');?>: <code>define("ADMIN_COOKIE_PATH","...</code>
                                </li>

                                <?php   
                                $arm_get_hide_wp_admin_option = get_option('arm_hide_wp_amin_disable');
                                if (!empty($arm_get_hide_wp_admin_option)) {
                                ?>
                                <li>
                                    <?php _e('If you can\'t login after renaming wp-admin, run below URL and all changes are rollback to default :', 'ARMember'); ?>
                                    <div class="arm_shortcode_text arm_form_shortcode_box">
										<span class="armCopyText"><?php echo home_url().'?arm_wpdisable='.$arm_get_hide_wp_admin_option; ?></span>
										<span class="arm_click_to_copy_text" data-code="<?php echo home_url().'?arm_wpdisable='.$arm_get_hide_wp_admin_option; ?>"><?php _e('Click to copy', 'ARMember');?></span>
										<span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"><?php _e('Code Copied', 'ARMember');?></span>
                                    </div>
                                </li>
                                <?php } ?>
                            </ol>
                        </td>
                    </tr>    
                </table>                          
</div>
<div id='arm_rename_wp_admin_popup_div_notice' class="popup_wrapper">
    <table cellspacing="0">
        <tr>
            
            <td class="popup_header"><?php _e('Error renaming','ARMember'); ?> wp-admin</td>
            <td class="popup_content_text arm_rename_wpadmin_wrapper" id="arm_rename_wpadmin_notice_text"></td>
           
            <td class="popup_footer">
            <div class='arm_rewrite_button_div'>
            <input type='submit' name='arm_save_global_settings' id='arm_save_global_settings' style='min-width:auto;' class='arm_save_btn' value='<?php _e('Okey, I did It!','ARMember'); ?>' />

            <input type='submit' name='arm_cancel_global_settings' id='arm_cancel_global_settings' style='min-width:auto; background-color: #d54e21; border: 1px solid #d54e21;' class='arm_save_btn' value='<?php _e('Abort Renaming','ARMember'); ?>' />
            </div>
            </td>
        </tr>
    </table>
</div>

<div id='arm_rename_wp_admin_popup_div_config_notice' class="popup_wrapper">
    <table cellspacing="0">
        <tr>
            <td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
            <td class="popup_header"><?php _e('Error renaming','ARMember'); ?> wp-config.php</td>
            <td class="popup_content_text arm_rename_wpadmin_wrapper" id="arm_rename_wpadmin_config_notice_text">
            <br/><a class="btn primary-btn" href="<?php wp_login_url(); ?>">I did it! Move me to the new admin</a>
            </td>
        </tr>
    </table>
</div>
<div class="arm_smtp_debug_detail_popup popup_wrapper arm_smtp_debug_detail_popup_wrapper" style="width:700px;">
    <div class="popup_wrapper_inner" style="overflow: hidden;">
        <div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_section_custom_css_detail_close_btn"></span>
            <span class="add_rule_content"><?php _e('Full Test Debug', 'ARMember'); ?></span>
        </div>
        <div class="popup_content_text arm_smtp_debug_detail_popup_text">
            
        </div>
        <div class="armclear"></div>
    </div>
</div>
<input id="is_reload_page" value="0" type="hidden">
<div class="arm_smtp_detail_container"></div>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
var ARM_IMAGE_URL = "<?php echo MEMBERSHIP_IMAGES_URL; ?>";
var ARM_UPDATE_LABEL = "<?php _e('Update', 'ARMember'); ?>";
var EMESSAGE = "<?php _e('Minimum one country is required.', 'ARMember'); ?>";
var ARM_COUNTRY_DEFAULT_OPT = "<?php _e('Select Country', 'ARMember'); ?>";
var ARM_COUNTRY_NAME_ERROR = "<?php _e('Please Select Country', 'ARMember'); ?>";
</script>