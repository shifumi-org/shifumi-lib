<?php
global $wpdb, $armPrimaryStatus, $ARMember, $arm_members_class, $arm_global_settings, $arm_subscription_plans,$arm_member_forms;
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type');
$dbProfileFields = $arm_member_forms->arm_get_db_form_fields();

$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<?php 
		//Handle Import/Export Process
		do_action('arm_handle_import_export', $_REQUEST);
		?>
		<div class="arm_import_export_container">
			<div class="arm_import_export_left_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php _e('User Export', 'ARMember');?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php _e('Membership Plans','ARMember');?></th>
							<td class="arm-form-table-content">
								<select name="subscription_plan[]" id="subscription_plan_select" class="arm_chosen_selectbox" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember');?>" multiple style="width: 320px;">
									<?php
									if (!empty($all_plans)) {
                                        foreach ($all_plans as $plan) {
											echo '<option value="'.$plan['arm_subscription_plan_id'].'">' . stripslashes($plan['arm_subscription_plan_name']) . '</option>';
										}
									}
									?>
								</select>
								<div class="armclear" style="max-height: 1px;"></div>
								<span class="arm_info_text">(<?php _e('Leave blank for all plans.', 'ARMember')?>)</span>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><?php _e('Member Status','ARMember');?></th>
							<td class="arm-form-table-content">
								<input type="hidden" id="arm_primary_status" name="primary_status" value="" />
								<dl class="arm_selectbox column_level_dd">
									<dt style="width: 300px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_primary_status">
											<li data-label="<?php _e('All Status','ARMember');?>" data-value=""><?php _e('All Status','ARMember');?></li>
											<?php 
											if(!empty($armPrimaryStatus)){
												foreach ($armPrimaryStatus as $key => $label){
													echo '<li data-label="' . $label . '" data-value="' . $key . '">' . $label . '</li>';
												}
											}
											?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><?php _e('Joining Date','ARMember');?></th>
							<td class="arm-form-table-content arm_import_export_date_fields">
								<input type="text" name="start_date" placeholder="<?php _e('Start Date', 'ARMember');?>" class="arm_admin_import_export_datepicker" data-date_format="<?php echo $arm_common_date_format; ?>">
								<input type="text" name="end_date" placeholder="<?php _e('End Date', 'ARMember');?>" class="arm_admin_import_export_datepicker" data-date_format="<?php echo $arm_common_date_format; ?>">
							</td>
						</tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"></th>
                            <td class="arm-form-table-content arm_import_export_date_fields">
                                <button id="arm_user_meta_to_export" class="armemailaddbtn" name="arm_action" value="select_meta" onClick="arm_open_user_meta_popup();" type="button" style="min-width: 120px;"><?php _e('Select Meta', 'ARMember');?></button>
                            </td>
                            <?php
                            $defaultMetas = array();
                            if (!empty($dbProfileFields['default'])) {
                                foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                                    if(!in_array($fieldMetaKey , array('user_login','user_email'))){
                                        continue;
                                    }
                                    array_push($defaultMetas,$fieldMetaKey);
                                }
                            }
                            $defaultMetas = implode(',',$defaultMetas);
                            ?>
                            <input type="hidden" name="arm_user_metas_to_export" value="<?php echo $defaultMetas; ?>" />
                        </tr>
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
								<button id="arm_user_export_btn_csv" class="armemailaddbtn" name="arm_action" value="user_export_csv" type="submit" style="min-width: 120px;"><?php _e('Export as csv', 'ARMember');?></button>
								<button id="arm_user_export_btn_xml" class="armemailaddbtn" name="arm_action" value="user_export_xml" type="submit" style="min-width: 120px;"><?php _e('Export as xml', 'ARMember');?></button>
								<span class="arm_info_text">(<?php _e("User having role 'administrator' will not be exported.", 'ARMember');?>)
								</span>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="arm_import_export_right_box">
				<form method="post" action="#" id="arm_import_user_form"  class="arm_admin_form arm_import_user_form" enctype="multipart/form-data">
					<div class="page_title"><?php _e('User Import', 'ARMember');?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php _e('Upload File','ARMember');?></th>
							<td class="arm-form-table-content">
								<input type="file" name="import_user" id="arm_import_user" data-msg-required="<?php _e('Please select a file.', 'ARMember'); ?>" class="armImportUpload" accept=".csv,.xml">
								<input class="arm_file_url" type="hidden" name="import_user" value="">
								<div class="arm_info_text"><?php _e('Only .csv and .xml files are allowed.', 'ARMember');?></div>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th class="arm-form-table-label">
								<label for="arm_plan_id"><?php _e('Assign Plan To User','ARMember'); ?></label>
							</th>
							<td class="arm-form-table-content">
								<input type="hidden" id="arm_plan_id" name="plan_id" value="" data-msg-required="<?php _e('Please select atleast one plan.', 'ARMember');?>" required/>
								<dl class="arm_selectbox column_level_dd">
									<dt style="width: 300px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_plan_id">
											<li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
											<?php 
											if (!empty($all_plans)) {
												foreach ($all_plans as $p) {
													$p_id = $p['arm_subscription_plan_id'];
													if ($p['arm_subscription_plan_status'] == '1') {
														?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']);?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name']));?></li><?php
													}
												}
											}
											?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
                                                                <input type="hidden" name="arm_user_metas_to_import" id="arm_user_metas_to_import" value="" />
								<button id="arm_user_import_btn" class="armemailaddbtn" name="arm_action" value="user_import" type="submit"><?php _e('Import', 'ARMember');?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_loader_img_import_user" style="position:relative;top:8px;display:none;" width="24" height="24" />
							</td>
						</tr>
                        <tr class="form-field">
                            <td colspan="2">
								<span class="">
									<?php _e("Please download sample csv", 'ARMember');?>&nbsp;<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=import_export&arm_action=download_sample');?>" class="arm_download_sample_csv_link" target="_blank"><?php _e('here', 'ARMember');?></a>.
								</span>
								<?php wp_nonce_field( 'arm_wp_nonce' );?>
							</td>
						</tr>

					</table>
				</form>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_divider"></div>
		<div class="arm_import_export_container">
			<div class="arm_import_export_left_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php _e('Export Settings', 'ARMember');?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<td colspan="2" class="arm_export_settings_container">
								<label>
									<input type="checkbox" name="global_options" value="1" class="arm_icheckbox"/>
									<?php _e('General Options','ARMember');?>
									<?php 
									$gen_opt_note = __('All general options will be exported.', 'ARMember');
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $gen_opt_note;?>"></i>
								</label>
								<div class="armclear"></div>
								<label>
									<input type="checkbox" name="block_options" value="1" class="arm_icheckbox"/>
									<?php _e('Security Options','ARMember');?>
									<?php 
									$blk_opt_note = __('Export all security options.', 'ARMember');
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $blk_opt_note;?>"></i>
								</label>
								
								<div class="armclear"></div>
								<label>
									<input type="checkbox" name="common_messages" value="1" class="arm_icheckbox"/>
									<?php _e('Common Messages','ARMember');?>
									<?php 
									$com_msg_note = __('Export all common messages.', 'ARMember');
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $com_msg_note;?>"></i>
								</label>
								<div class="armclear"></div>
							</td>
						</tr>
						<tr class="form-field">
							<td class="arm-form-table-content" colspan="2">
								<button id="arm_settings_export_btn" class="armemailaddbtn" name="arm_action" value="settings_export" type="submit"><?php _e('Export', 'ARMember');?></button>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="arm_import_export_right_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php _e('Import Settings', 'ARMember');?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php _e('Import Settings','ARMember');?></th>
							<td class="arm-form-table-content">
								<textarea name="settings_import_text" id="settings_import_text" rows="8" cols="30" style="min-width: 100%;" required></textarea>
							</td>
						</tr>
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
								<button id="arm_settings_import_btn" class="armemailaddbtn" name="arm_action" value="settings_import" type="submit"><?php _e('Import', 'ARMember');?></button>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
	</div>
	<div class="arm_import_user_list_detail_container"></div>
</div>
<div class="arm_import_user_list_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper" style="width:900px;">
    <form method="GET" id="arm_add_import_user_form" class="arm_admin_form" onsubmit="return arm_add_import_user_form_action();">
        <div class="popup_wrapper_inner" style="overflow: hidden;">
            <div class="popup_header">
                <span class="popup_close_btn arm_popup_close_btn arm_import_user_list_detail_close_btn"></span>
                <span class="add_rule_content"><?php _e('Import User Details', 'ARMember'); ?></span>
            </div>
            <div class="popup_content_text arm_import_user_list_detail_popup_text">
                <div class="arm_import_processing_loader">
                    <div class="arm_import_processing_text"><?php _e('Processing','ARMember'); ?></div>
                </div>
            </div>
            <div class="popup_content_btn popup_footer">
                <div class="arm_user_import_password_section">
                    <input type="radio" id="arm_user_password_fixed" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="set_fix" checked="checked"  onchange="arm_set_user_password('set_fix')" >
                <label for="arm_user_password_fixed" class="arm_user_import_type"><?php _e('Set fix password', 'ARMember'); ?></label>

                    <input type="radio" id="arm_user_password_dynamically" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="create_dynamic"  onchange="arm_set_user_password('create_dynamic')">
                    <label for="arm_user_password_dynamically"  class="arm_user_import_type"><?php _e('Generate dynamically', 'ARMember'); ?></label>
                
                    <div class="arm_import_user_send_mail_wrapper" id="arm_user_password_fixed_div" style="display:block;">
                         <input type="text" id="arm_import_user_fix_password" name="arm_import_user_fix_password" class="arm_fixed_password"/>
                    </div>
                
                    <div class="arm_import_user_send_mail_wrapper" id="arm_user_password_dynamically_div">
                        <input type="checkbox" checked="checked" id="arm_send_mail_to_imported_users" class="arm_send_mail_to_imported_users chkstanard"/><label for="arm_send_mail_to_imported_users"><?php _e('Send Reset Password Link by email.','ARMember'); ?></label>
                    </div>
                    
                    <input type="radio" id="arm_user_password_from_csv" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="from_csv"  onchange="arm_set_user_password('from_csv')" style="display: none;" >
                    <label for="arm_user_password_from_csv" class="arm_user_import_type arm_user_password_from_csv_label" style="display:none;"><?php _e('Import Password from csv / xml', 'ARMember'); ?></label>
                    <div class="arm_import_user_send_mail_wrapper" id="arm_user_password_from_csv_div">
                        <input type="radio" class="arm_form_field_settings_field_input arm_iradio" checked="checked" name="arm_password_type" id="arm_is_hashed_password" value="hashed"/><label for="arm_is_hashed_password"><?php _e('Your password is hashed.','ARMember'); ?></label>
                        
                         <input type="radio" class="arm_form_field_settings_field_input arm_iradio" name="arm_password_type" id="arm_is_plain_password" value="plain"/><label for="arm_is_plain_password"><?php _e('Your password is plain text.','ARMember'); ?></label>
                    </div>
                 </div>
                        <div class="armclear"></div>
                <div class="arm_import_progressbar">
                    <div class="arm_import_progressbar_inner"></div>
                </div>
                <div class="popup_content_btn_wrapper">
                    <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif'; ?>" class="arm_loader_img" style="position: relative;top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;" width="20" height="20"/>
                    <button class="arm_cancel_btn arm_import_user_list_detail_previous_btn" type="button"><?php _e('Previous', 'ARMember'); ?></button>
                    <button class="arm_submit_btn arm_add_import_user_submit_btn" type="submit"><?php _e('Add', 'ARMember'); ?></button>
                    <button class="arm_cancel_btn arm_import_user_list_detail_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                    <?php wp_nonce_field( 'arm_wp_nonce' );?>
                </div>
            </div>
            <div class="armclear"></div>
        </div>
    </form>
</div>
<script type="text/javascript">
    __PROCESSING = '<?php __('Processing','ARMember'); ?>';
</script>

<div id='arm_select_user_meta_for_export' class="popup_wrapper">
    <form method="post" action="#" id="arm_select_user_meta_for_export_form" class="arm_admin_form">
        <table  cellspacing="0">
            <tr>
                <td class="arm_select_user_meta_close_btn arm_popup_close_btn"></td>
                <td class="popup_header"><?php _e('Select User Meta Fields', 'ARMember'); ?></td>
                <td class="popup_content_text arm_select_user_meta_wrapper">
                    <?php

                    if (!empty($dbProfileFields['default'])) {

                        foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                            if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme','arm_captcha'))) {
                                continue;
                            }
                            $checkedDefault = " checked='checked' disabled='disabled' ";
                            if( !in_array($fieldMetaKey,array('user_login','user_email')) ){
                                $checkedDefault = "";
                            }
                            ?>
                            <label class = "account_detail_radio arm_account_detail_options">
                                <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "export_user_meta[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>" <?php echo $checkedDefault; ?> />
                                <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo stripslashes_deep($fieldOpt['label']); ?></label>
                                <div class="arm_list_sortable_icon"></div>
                            </label>
                            <?php
                        }
                    }


                    if (!empty($dbProfileFields['other'])) {

                        foreach ($dbProfileFields['other'] as $fieldMetaKey => $fieldOpt) {
                            if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme','arm_captcha'))) {
                                continue;
                            }
                            
                            ?>
                            <label class = "account_detail_radio arm_account_detail_options">
                                <input type = "checkbox" value = "<?php echo $fieldMetaKey; ?>" class = "arm_icheckbox arm_account_detail_fields" name = "export_user_meta[<?php echo $fieldMetaKey; ?>]" id = "arm_profile_field_input_<?php echo $fieldMetaKey; ?>"/>
                                <label for="arm_profile_field_input_<?php echo $fieldMetaKey; ?>"><?php echo stripslashes_deep($fieldOpt['label']); ?></label>
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
                        <button class="arm_save_btn arm_user_meta_to_export" id="arm_select_metas_to_export" type="button" data-type="add"><?php _e('Ok', 'ARMember') ?></button>
                        <button class="arm_cancel_btn arm_select_user_meta_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id='arm_select_user_meta_for_import' class="popup_wrapper">
    <form method="post" action="#" id="arm_select_user_meta_for_import_form" class="arm_admin_form">
        <table  cellspacing="0">
            <tr>
                <td class="arm_select_user_meta_close_btn arm_popup_close_btn"></td>
                <td class="popup_header"><?php _e('Select User Meta Fields', 'ARMember'); ?></td>
                <td class="popup_content_text popup_header">
                    <span class="arm_info_text" style="margin:0;">
                        <?php _e(" Note that if you will select new meta then new meta will be set as", 'ARMember'); ?>
                        <strong> <?php _e('Preset Fields', 'ARMember'); ?> </strong>
                        <?php _e("and the field type will be", 'ARMember'); ?>   
                        <strong> <?php _e('Textbox.', 'ARMember'); ?> </strong>
                        <br/>
                    </span>
                </td>
                <td class="popup_content_text arm_select_user_meta_wrapper" id="arm_select_user_meta_wrapper"> </td>
                <td class="popup_content_btn popup_footer">
                    <div class="popup_content_btn_wrapper">
                        <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_loader_img_clear_field" class="arm_loader_img" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
                        <button class="arm_save_btn arm_user_meta_to_import_next" id="arm_user_meta_to_import_next" type="button" data-type="add"><?php _e('Next', 'ARMember') ?></button>
                        <button class="arm_cancel_btn arm_select_user_meta_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>