<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_social_feature;
$date_format = $arm_global_settings->arm_get_wp_date_format();
$globalSettings = $arm_global_settings->global_settings;
$thank_you_page_id = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
$add_form_select = '';
$profile_form_select = '';

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_manage_forms_main_wrapper">
	<div class="content_wrapper arm_manage_forms_container" id="content_wrapper">
		<div class="page_title"><?php _e('Manage Forms','ARMember'); ?></div>
         <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
		<div class="armclear"></div>
		<div class="arm_manage_forms_content armPageContainer">
			<div class="arm_form_content_box">
				<!-- ****************************/.Registration Forms./***************************** -->
				<div class="arm_form_heading">
					<span><?php _e('Registration / Signup Forms', 'ARMember');?></span>
                    
					<a class="greensavebtn arm_add_new_form_btn" data-type="registration" href="javascript:void(0);"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Form', 'ARMember');?></span></a>
					<div class="armclear"></div>
				</div>
				<div class="armclear"></div>
				<div class="arm_form_list_container">
				<?php 
				$registration_forms = $wpdb->get_results("SELECT `arm_form_id`, `arm_form_label`, `arm_form_slug`, `arm_is_default`, `arm_form_updated_date` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='registration' ORDER BY `arm_form_id` DESC", ARRAY_A);
				
				$add_form_select .= '<input type="hidden" name="existing_form_registration" id="existing_form_registration_val" class="existing_form_select" value=""/>';
				$add_form_select .= '<dl id="existing_form_registration" class="arm_selectbox existing_form_select">';
				$add_form_select .= '<dt><span>'.__('Select Form','ARMember').'</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
				$add_form_select_style = (is_rtl()) ? 'margin-right: 35px! important;' : '';
				$add_form_select .= '<dd><ul data-id="existing_form_registration_val" style="'.$add_form_select_style.' width: 362px;">';
				$add_form_select .="<li data-label='". __('Select Form', 'ARMember')."' data-value=''>".__('Select Form', 'ARMember')."</li>";

				$profile_form_select .= '<input type="hidden" name="existing_form_profile_form" id="existing_form_profile_form_val" class="existing_form_select" value=""/>';;
				$profile_form_select .= '<dl id="existing_form_profile_form" class="arm_selectbox existing_form_select">';
				$profile_form_select .= '<dt><span>' . esc_html__( ' Select Form', 'ARMember' ) . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
				$profile_form_select .= '<dd><ul data-id="existing_form_profile_form_val" style="' . $add_form_select_style . 'width: 362px;">';
				$profile_form_select .= '<li data-label="' . esc_html__('Select Form','ARMember') . '" data-value="">' . esc_html__('Select Form', 'ARMember').'</li>';

				?>
				<table class="form-table">
					<tr class="arm_form_list_header">
						<td></td>
						<td class="arm_form_id_col"><?php _e('Form ID','ARMember');?></td>
						<td class="arm_form_title_col"><?php _e('Form Name','ARMember');?></td>
						<td class="arm_form_shortcode_col"><?php _e('Shortcode','ARMember');?></td>
						<td class="arm_form_shortcode_col"><?php _e('Last Modified','ARMember');?></td>
						<td class="arm_form_action_col"><?php _e('Action','ARMember');?></td>
						<td></td>
					</tr>
					<?php if(!empty($registration_forms)): ?>
						<?php foreach($registration_forms as $_form): ?>
							<?php 
							$_fid = $_form['arm_form_id'];
							$add_form_select .="<li data-label='". strip_tags(stripslashes($_form['arm_form_label']))."' data-value='".$_fid."' class='existing_form_li_".$_fid."'>". strip_tags(stripslashes($_form['arm_form_label']))."</li>";
							$profile_form_select .="<li data-label='". strip_tags(stripslashes($_form['arm_form_label']))."' data-value='".$_fid."' class='existing_form_li_".$_fid."'>". strip_tags(stripslashes($_form['arm_form_label']))."</li>";
							?>
							<tr class="arm_form_tr_<?php echo $_fid;?>">
								<td></td>
								<td class="arm_form_title_col"><?php echo $_fid; ?></td>
								<td class="arm_form_title_col"><a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$_fid);?>" class="arm_get_form_link" data-form_id="<?php echo $_fid;?>"><?php echo strip_tags(stripslashes($_form['arm_form_label']));?></a></td>
								<td class="arm_form_shortcode_col">
									<div class="arm_short_code_detail">
										<?php $shortCode = '[arm_form id="'.$_fid.'"]';?>
										<div class="arm_shortcode_text arm_form_shortcode_box">
											<span class="armCopyText"><?php echo esc_attr($shortCode);?></span>
											<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($shortCode);?>"><?php _e('Click to copy', 'ARMember');?></span>
											<span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
										</div>
									</div>
								</td>
								<td class="arm_form_date_col">( <?php echo date_i18n($date_format, strtotime($_form['arm_form_updated_date']));?> )</td>
								<td class="arm_form_action_col">
									<div class="arm_form_action_btns arm_reg_form_action_btns">
										<a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$_fid);?>" class="arm_get_form_link" data-form_id="<?php echo $_fid;?>">
											<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon_hover.png';" class="armhelptip" title="<?php _e('Edit Form','ARMember');?>" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png';" />
										</a>
										<?php if($_form['arm_is_default'] != '1'):?>
										<a href="javascript:void(0)" class="arm_delete_form_link" onclick="showConfirmBoxCallback(<?php echo $_fid;?>);" data-form_id="<?php echo $_fid;?>">
											<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png" class="armhelptip" title="<?php _e('Delete Form','ARMember');?>" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png';" style='cursor:pointer'/>
										</a>
										<?php
										$formDeleteHtml = __("Are you sure you want to delete this form?", 'ARMember');
										$formDeleteHtml .= '<label>';
										$formDeleteHtml .= '<input type="checkbox" class="arm_icheckbox arm_form_field_chk_' . $_fid . '" value="1">';
										$formDeleteHtml .= '<span>'.__("Delete fields of this specific form.", 'ARMember').'</span>';
										$formDeleteHtml .= '</label>';
										$formDeleteHtml .= '<span class="armnote"><em>('.__("Fields those which are used somewhere else, will not be deleted.", 'ARMember').')</em></span>';
										echo $arm_global_settings->arm_get_confirm_box($_fid, $formDeleteHtml, 'arm_delete_form_confirm_ok');
										?>
										<?php endif;?>
									</div>
								</td>
								<td></td>
							</tr>
						<?php endforeach;?>
					<?php endif;?>
					<?php $add_form_select .= '</ul></dd></dl>';?>
					<?php $profile_form_select .= '</ul></dd></dl>';?>
				</table>
				</div>
				<div class="armclear"></div>
				<!-- ********************************/.Other Forms./******************************** -->
					<div class="arm_form_heading">
						<span><?php _e('Other Forms (Login / Forgot Password / Change Password)', 'ARMember');?></span>
						<a class="greensavebtn arm_add_new_other_forms_btn" data-type="login" href="javascript:void(0);"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Form', 'ARMember');?></span></a>
						<div class="armclear"></div>
					</div>
					<div class="armclear"></div>
					<div class="arm_form_list_container arm_form_set_list_container">
					<?php $otherForms = $arm_member_forms->arm_get_member_form_sets();?>
					<table class="form-table">
						<tr class="arm_form_list_header">
							<td></td>
							<td class="arm_form_id_col"><?php _e('Set ID','ARMember');?></td>
							<td class="arm_form_title_col"><?php _e('Set Name','ARMember');?></td>
							<td class="arm_form_shortcode_col"><?php _e('Shortcode','ARMember');?></td>
							<td class="arm_form_action_col"><?php _e('Action','ARMember');?></td>
							<td></td>
						</tr>
						<?php if(!empty($otherForms)): ?>
							<?php foreach($otherForms as $setID => $formSet): ?>
							<?php if(!empty($formSet)): ?>
							<?php 
							$formSetValues = array_values($formSet);
							$firstForm = array_shift($formSetValues);
							reset($formSet);
							?>
							<tr class="arm_form_set_tr_<?php echo $setID;?>">
								<td></td>
								<td class="arm_form_id_col">
	                               <?php echo $firstForm['arm_form_id'];?>
	                            </td>
								<td class="arm_form_title_col">
	                                <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$firstForm['arm_form_id']);?>" class="arm_get_form_link" data-form_id="<?php echo $firstForm['arm_form_id'];?>"><?php echo stripslashes($firstForm['arm_set_name']);?></a>
	                                <span class="arm_form_date_col">( <?php echo date_i18n($date_format, strtotime($firstForm['arm_form_updated_date']));?> )</span>
	                            </td>
								<td class="arm_form_shortcode_col">
	                                <ul>
	                                    <?php foreach($formSet as $_form): ?>
	                                    <li>
	                                        <h4><?php 
	                                        if($_form['arm_form_type'] == 'login'){
	                                            _e('Login', 'ARMember');
	                                        } elseif($_form['arm_form_type'] == 'forgot_password'){
	                                            _e('Forgot Password', 'ARMember');
	                                        } elseif($_form['arm_form_type'] == 'change_password'){
	                                            _e('Change Password', 'ARMember');
	                                        }
	                                        ?></h4>
	                                        <div class="arm_short_code_detail">
	                                            <?php $shortCode = '[arm_form id="'.$_form['arm_form_id'].'"]';?>
	                                            <div class="arm_shortcode_text arm_form_shortcode_box">
	                                                <span class="armCopyText"><?php echo esc_attr($shortCode);?></span>
	                                                <span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($shortCode);?>"><?php _e('Click to copy', 'ARMember');?></span>
	                                                <span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
	                                            </div>
	                                        </div>
	                                    </li>
	                                    <?php endforeach;?>
	                                </ul>
								</td>
								<td class="arm_form_action_col">
									<div class="arm_form_action_btns">
	                                    <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=duplicate_form&form_id='.$firstForm['arm_form_id']);?>" class="arm_duplicate_form_icon" title="" ><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/duplicate_icon.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/duplicate_icon_hover.png';" class="armhelptip" title="<?php _e('Duplicate Form','ARMember'); ?>" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/duplicate_icon.png';" /></a>
										<a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$firstForm['arm_form_id']);?>" class="arm_get_form_link" data-form_id="<?php echo $_fid;?>"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon_hover.png';" class="armhelptip" title="<?php _e('Edit Form','ARMember');?>" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png';" /></a>
										<?php if($firstForm['arm_is_default'] != '1'):?>
										<a href="javascript:void(0)" class="arm_delete_set_link" onclick="showConfirmBoxCallback('<?php echo 'set_'.$setID;?>');"  data-set_id="<?php echo $setID;?>">
											<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png" class="armhelptip" title="<?php _e('Delete Form Set','ARMember');?>" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png';" style='cursor:pointer'/>
										</a>
										<?php
										echo $arm_global_settings->arm_get_confirm_box('set_'.$setID, __("Are you sure you want to delete this form set?", 'ARMember'), 'arm_delete_form_set_confirm_ok');
										?>
										<?php endif;?>
									</div>
								</td>
								<td></td>
							</tr>
							<?php endif;?>
							<?php endforeach;?>
						<?php endif;?>
					</table>
					</div>
				<!-- ********************************/ Edit Profile Form /************************** -->
				<div class="arm_form_heading">
					<span><?php esc_html_e( 'Profile Forms', 'ARMember' ); ?></span>
					<a class="greensavebtn arm_add_new_profile_forms_btn" data-type="profile" href="javascript:void(0);"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Form', 'ARMember');?></span></a>
					<div class="armclear"></div>
				</div>
				<div class="armclear"></div>
				<div class="arm_form_list_container arm_profile_form_container">
					<?php
						$profile_forms = $wpdb->get_results( $wpdb->prepare( "SELECT `arm_form_id`, `arm_form_label`, `arm_form_slug`, `arm_is_default`, `arm_form_updated_date` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` = %s ORDER BY `arm_form_id` DESC", 'edit_profile'), ARRAY_A );
					?>
					<table class="form-table">
						<tr class="arm_form_list_header">
							<td></td>
							<td class="arm_form_id_col"><?php _e('Form ID','ARMember');?></td>
							<td class="arm_form_title_col"><?php _e('Form Name','ARMember');?></td>
							<td class="arm_form_shortcode_col"><?php _e('Shortcode','ARMember');?></td>
							<td class="arm_form_shortcode_col"><?php _e('Last Modified','ARMember');?></td>
							<td class="arm_form_action_col"><?php _e('Action','ARMember');?></td>
							<td></td>
						</tr>
						<?php
							if( !empty( $profile_forms ) ){

								foreach( $profile_forms as $edit_form ){
									$_fid = $edit_form['arm_form_id'];
								?>
									<tr class="arm_form_tr_<?php echo $_fid;?>">
										<td></td>
										<td class="arm_form_title_col"><?php echo $_fid; ?></td>
										<td class="arm_form_title_col"><a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$_fid);?>" class="arm_get_form_link" data-form_id="<?php echo $_fid;?>"><?php echo strip_tags(stripslashes($edit_form['arm_form_label']));?></a></td>
										<td class="arm_form_shortcode_col">
											<div class="arm_short_code_detail">
												<?php $shortCode = '[arm_profile_detail id="'.$_fid.'"]';?>
												<div class="arm_shortcode_text arm_form_shortcode_box">
													<span class="armCopyText"><?php echo esc_attr($shortCode);?></span>
													<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($shortCode);?>"><?php _e('Click to copy', 'ARMember');?></span>
													<span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
												</div>
											</div>
										</td>
										<td class="arm_form_date_col">( <?php echo date_i18n($date_format, strtotime($edit_form['arm_form_updated_date']));?> )</td>
										<td class="arm_form_action_col">
											<div class="arm_form_action_btns arm_profile_form_action_btns">
												<a href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_forms.'&action=edit_form&form_id='.$_fid);?>" class="arm_get_form_link" data-form_id="<?php echo $_fid;?>">
													<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon_hover.png';" class="armhelptip" title="<?php _e('Edit Form','ARMember');?>" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png';" />
												</a>
												<?php if($edit_form['arm_is_default'] != '1'):?>
												<a href="javascript:void(0)" class="arm_delete_form_link" onclick="showConfirmBoxCallback(<?php echo $_fid;?>);" data-form_id="<?php echo $_fid;?>">
													<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png" class="armhelptip" title="<?php _e('Delete Form','ARMember');?>" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png';" style='cursor:pointer'/>
												</a>
												<?php
												$formDeleteHtml = __("Are you sure you want to delete this form?", 'ARMember');
												$formDeleteHtml .= '<label>';
												$formDeleteHtml .= '<input type="checkbox" class="arm_icheckbox arm_form_field_chk_' . $_fid . '" value="1">';
												$formDeleteHtml .= '<span>'.__("Delete fields of this specific form.", 'ARMember').'</span>';
												$formDeleteHtml .= '</label>';
												$formDeleteHtml .= '<span class="armnote"><em>('.__("Fields those which are used somewhere else, will not be deleted.", 'ARMember').')</em></span>';
												echo $arm_global_settings->arm_get_confirm_box($_fid, $formDeleteHtml, 'arm_delete_form_confirm_ok');
												endif;?>
											</div>
										</td>
									</tr>
								<?php
								}
							}
						?>
					</table>
				</div>
				<!-- ********************************/.Additional Shortcodes./******************************** -->
				<div class="arm_form_heading">
					<span><?php _e('Additional Shortcodes', 'ARMember');?></span>
					<div class="armclear"></div>
				</div>
				<div class="armclear"></div>
				<div class="arm_form_list_container arm_form_additional_shortcodes">
					<table class="form-table">
						<tr>
							<td></td>
							<td class="arm_form_title_col"><?php _e('Logout', 'ARMember');?></td>
							<td class="arm_form_shortcode_col" colspan="2">
                                <div class="arm_short_code_detail">
                                    <span class="arm_shortcode_title"><?php _e('Shortcode', 'ARMember');?>&nbsp;&nbsp;</span>
                                    <div class="arm_shortcode_text arm_form_shortcode_box">
                                        <?php $logout_code = '[arm_logout label="'.__('Logout', 'ARMember').'" type="button"]';?>
                                        <span class="armCopyText"><?php echo esc_attr($logout_code);?></span>
                                        <span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($logout_code);?>"><?php _e('Click to copy', 'ARMember');?></span>
                                        <span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
                                    </div>
								</div>
							</td>
                            <td>
								<ul>
									<li><strong><?php _e('Possible Arguments :', 'ARMember');?></strong></li>
									<li>label="<?php _e('Logout', 'ARMember');?>"</li>
									<li>type="link"</li>
									<li>user_info="true"</li>
									<li>redirect_to="<?php echo ARM_HOME_URL;?>"</li>
									<li>link_css="color: #000000;"</li>
									<li>link_hover_css="color: #ffffff;"</li>
								</ul>
                            </td>
							<td></td>
						</tr>
                        <tr>
							<td></td>
							<td class="arm_form_title_col"><?php _e('Close Account', 'ARMember');?></td>
							<td class="arm_form_shortcode_col" colspan="2">
                                <div class="arm_short_code_detail">
                                    <span class="arm_shortcode_title"><?php _e('Shortcode', 'ARMember');?>&nbsp;&nbsp;</span>
                                    <div class="arm_shortcode_text arm_form_shortcode_box">
                                        <?php $close_account_code = '[arm_close_account set_id="102"]';?>
                                        <span class="armCopyText"><?php echo esc_attr($close_account_code);?></span>
                                        <span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($close_account_code);?>"><?php _e('Click to copy', 'ARMember');?></span>
                                        <span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
                                    </div>
								</div>
							</td>
                            <td>
								<ul>
									<li><strong><?php _e('Possible Arguments :', 'ARMember');?></strong></li>
									<li>set_id="102"</li>
                                    <li><?php _e('This set_id is id of set of form created for Login, Forgot Password, Change Password forms. And according to that set, Close account form styling will be set.', 'ARMember');?></li>
								</ul>
                            </td>
							<td></td>
						</tr>
						<?php if ($arm_social_feature->isSocialLoginFeature): ?>
						<tr>
							<td></td>
							<td class="arm_form_title_col"><?php _e('Social Login', 'ARMember');?></td>
							<td class="arm_form_shortcode_col" colspan="2">
                                <div class="arm_short_code_detail">
                                    <span class="arm_shortcode_title"><?php _e('Shortcode', 'ARMember');?>&nbsp;&nbsp;</span>
                                    <div class="arm_shortcode_text arm_form_shortcode_box">
                                        <span class="armCopyText">[arm_social_login]</span>
                                        <span class="arm_click_to_copy_text" data-code="[arm_social_login]"><?php _e('Click to copy', 'ARMember');?></span>
                                        <span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
                                    </div>
								</div>
							</td>
							<td></td>
                                                        <td></td>
						</tr>
						<?php endif; ?>
					</table>
				</div>
				<div class="armclear"></div>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<!--./******************** Add New Member Form ********************/.-->
<div class="add_new_form_wrapper popup_wrapper" style="width: 650px;">
	<form method="post" id="form_arm_add_new_reg_form" class="arm_admin_form">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">
				<td class="add_new_form_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Add new form','ARMember');?></td>
				<td class="popup_content_text">
					<div class="arm_message arm_error_message arm_add_new_form_error">
						<div class="arm_message_text"><?php _e('There is a error while adding form, Please try again.', 'ARMember');?></div>
					</div>
                    <div class="arm_registration_popup_inner_content_wrapper" style="min-height: 400px; position: relative;">
					<table class="arm_table_label_on_top">
						<tr>
							<th><label><?php _e('Form Name','ARMember');?><span class="required_star">*</span></label></th>
							<td><input type="text" id="unique_form_name" name="arm_new_form[arm_form_label]" value="" required data-msg-required="<?php _e('Form name can not be left blank.', 'ARMember');?>" style="width: 422px;"></td>
						</tr>
						<tr>
							<th><label><?php _e('Form Fields','ARMember');?></label></th>
							<td>
								<div class="arm_form_existing_options">
									<label style="<?php echo (is_rtl()) ? 'margin-left: 15px;' : 'margin-right: 15px;';?>">
										<input type="radio" name="existing_type" value="form" class="arm_iradio add_new_form_existing_type" checked="checked">
										<?php echo (is_rtl()) ? '' : '&nbsp;';?><?php _e('Clone from existing forms','ARMember');?> (<?php _e('Recommend', 'ARMember');?>)<?php echo (is_rtl()) ? '&nbsp;' : '';?>
									</label>
                                    <div class="add_new_form_existing_options existing_type_form" style="margin:0 0 5px 0;">
										<?php echo $add_form_select;?>
									</div>
                                    <label style="<?php echo (is_rtl()) ? 'margin-left: 15px;' : 'margin-right: 15px;';?>">
                                        <input type="radio" name="existing_type" value="template" class="arm_iradio add_new_form_existing_type" />
                                        <?php echo (is_rtl()) ? '' : '&nbsp;'; ?> <?php _e('Select Template','ARMember'); ?><?php echo (is_rtl()) ? '&nbsp;' : '';?>
                                    </label>
                                    <div class="add_new_form_existing_options template_type_form" style="margin:0 0 5px 0;display:none;">
                                        <input id="template_form_registration_val" class="existing_form_select" type="hidden" value="" name="template_form_registration" style="display:none;" />
                                        <dl id="template_form_registration" class="arm_selectbox existing_form_select" style="display:inline-block">
                                            <dt><span><?php _e('Select Template','ARMember'); ?></span>
                                                <input type="text" class="arm_autocomplete" value="" style="display:none;" />
                                                <i class="armfa armfa-caret-down armfa-lg"></i>
                                            </dt>
                                            <dd>
                                                <ul data-id="template_form_registration_val" style="<?php echo $add_form_select_style = (is_rtl()) ? 'margin-right: 35px! important;' : ''; ?> width: 362px;">
                                                    <li data-value="" data-label="<?php _e('Select Template','ARMember'); ?>"><?php _e('Select Template','ARMember'); ?></li>
                                                    <?php
                                                        $registration_templates = $wpdb->get_results("SELECT * FROM ".$ARMember->tbl_arm_forms." WHERE arm_is_template ='1' AND arm_form_slug LIKE 'template-registration%' AND arm_form_type='template' ");
                                                        foreach( $registration_templates as $key => $template ){
                                                            ?>
                                                    <li data-value="<?php echo $template->arm_form_id ?>" data-label="<?php echo $template->arm_set_name; ?>"><?php echo $template->arm_set_name; ?></li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                        <label class="arm_template_form_registration_select_meta" >
                                            <input type="checkbox" name="arm_meta_fields_for_template" value="meta_fields" class="arm_iradio" id="select_arm_field_metas" />
                                            <?php echo (is_rtl()) ? '' : '&nbsp;';?><?php _e('Select meta fields','ARMember');?><?php echo (is_rtl()) ? '&nbsp;' : '';?>
                                        </label>
                                        <div class="existing_type_field hidden_section" id="arm_existing_type_fields" style="margin-left:60px;">
                                            <?php 
                                            $metaFields = $arm_member_forms->arm_get_db_form_fields(true);
                                            
                                            if (!empty($metaFields)) {
                                                foreach ($metaFields as $_key => $_field) {
                                                    $fAttr = '';
                                                    if (in_array($_key, array('user_email', 'user_login', 'first_name', 'last_name', 'user_pass'))) {
                                                        $fAttr = 'checked="checked" disabled="disabled"';
                                                    }

                                                    echo '<div class="arm_add_new_form_field arm_field_' . $_key . '">';
                                                    echo '<label><input type="checkbox" class="arm_icheckbox" name="specific_fields[]" value="' . $_key . '" ' . $fAttr . '> ' . $_field['label'] . '</label>';
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                            <input type="hidden" name="specific_fields[]" value="submit">
                                        </div>
                                    </div>
								</div>
							</td>
						</tr>
						
					</table>
                    </div>
                                    <div class="arm_template_preview_wrapper arm_registration_templates" >
                        <?php
                        $reg_temp_id = 1;
                        foreach ($registration_templates as $key => $template) {
                            $arm_set_id = $template->arm_form_id;
                            ?>
                            <div class="arm_image_register_placeholder_wrapper" data-template-set-id="<?php echo $arm_set_id; ?>" data-set-id="<?php echo $reg_temp_id; ?>">
                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/form_templates/arm_signup_template_' . $reg_temp_id . '.png' ?>" />
                            </div>
                            <?php
                            $reg_temp_id++;
                        }
                        ?>
                    </div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<input type="hidden" name="arm_new_form[arm_form_type]" id="add_new_form_type" value="" />
						<button class="arm_submit_btn arm_add_new_form_submit_btn" type="submit"><?php _e('Add','ARMember');?></button>
						<button class="arm_cancel_btn add_new_form_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
						<?php wp_nonce_field( 'arm_wp_nonce' );?>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Add New Other Member Forms ********************/.-->
<div class="arm_add_new_other_forms_wrapper popup_wrapper" style="width: 650px;">
	<form method="post" id="form_arm_add_new_other_member_form" class="arm_admin_form">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">
				<td class="add_new_other_forms_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Add new form','ARMember');?></td>
				<td class="popup_content_text">
					<div class="arm_message arm_error_message arm_add_new_form_error">
						<div class="arm_message_text"><?php _e('There is a error while adding form, Please try again.', 'ARMember');?></div>
					</div>
                                    <div class="arm_other_forms_popup_inner_content_wrapper">
					<table class="arm_table_label_on_top">
						<tr>
							<th><label><?php _e('Set Name','ARMember');?><span class="required_star">*</span></label></th>
							<td><input type="text" id="unique_set_name" name="arm_new_form[arm_set_name]" value="" required data-msg-required="<?php _e('Set name can not be left blank.', 'ARMember');?>"></td>
						</tr>
                        <tr>
                            <th><label><?php _e('Select Template','ARMember'); ?></label></th>
                            <td>
                                <?php
                                $first_template = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE arm_is_template = 1 and arm_form_slug LIKE 'template-login%' GROUP BY arm_set_id ORDER BY arm_form_id ASC LIMIT 1 ");
                                ?>
                                <input type="hidden" name="arm_form_template" id="arm_form_template" value="<?php echo $first_template->arm_set_id; ?>" />
                                <dl class="arm_selectbox column_level_dd">
                                    <dt>
                                        <span><?php _e('Default Set','ARMember'); ?></span>
                                        <input type="text" class="arm_autocomplete" />
                                        <i class="armfa armfa-caret-down armfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul data-id="arm_form_template">
                                            <?php
                                            $templates = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE arm_is_template = 1 and arm_form_slug LIKE 'template-login%' GROUP BY arm_set_id ORDER BY arm_form_id ASC ");
                                            $t = 1;
                                            foreach ($templates as $key => $template) {
                                            ?>
                                            <li data-value="<?php echo $template->arm_set_id; ?>" data-label="<?php echo $template->arm_set_name; ?>"><?php echo $template->arm_set_name; ?></li>
                                            <?php
                                                $t++;
                                            }
                                            ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </td>
                        </tr>
					</table>
                                    </div>
                    <div class="arm_template_preview_wrapper">
                        <?php
                        $temp_id = 1;
                        foreach ($templates as $key => $template) {
                            $template_id = $template->arm_form_id;
                            $arm_set_id = $template->arm_set_id;
                            ?>
                            <div class="arm_image_placeholder_wrapper" data-template-id="<?php echo $template_id; ?>" data-set-id="<?php echo $arm_set_id; ?>">
                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/form_templates/arm_login_template_' . $temp_id . '.png' ?>" />
                            </div>
                            <?php
                            $temp_id++;
                        }
                        ?>
                    </div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<input type="hidden" name="arm_new_form[arm_form_type]" value="login" />
						<button class="arm_submit_btn arm_add_new_form_submit_btn" type="submit"><?php _e('Add','ARMember');?></button>
						<button class="arm_cancel_btn add_new_other_forms_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
						<?php wp_nonce_field( 'arm_wp_nonce' );?>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Add New Profile Form *******************/. -->
<div class="add_new_profile_form_wrapper popup_wrapper" style="width:750px">
	<form method="post" id="arm_add_new_profile_form" class="arm_admin_form">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">
				<td class="add_new_profile_form_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Add new profile form','ARMember');?></td>
				<td class="popup_content_text">
					<div class="arm_message arm_error_message arm_add_new_form_error">
						<div class="arm_message_text"><?php esc_html_e('There is a error while adding form, Please try again.', 'ARMember');?></div>
					</div>
					<div class="arm_profile_popup_inner_content_wrapper" style="min-height: 400px; position: relative;">
						<table class="arm_table_label_on_top">
							<tr>
								<th><label><?php esc_html_e('Form Name','ARMember');?><span class="required_star">*</span></label></th>
								<td><input type="text" id="unique_profile_form_name" name="arm_new_profile_form[arm_form_label]" value="" required data-msg-required="<?php esc_html_e('Form name can not be left blank.', 'ARMember');?>" style="width: 422px;"></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e('Form Fields','ARMember'); ?></label></th>
								<td>
									<div class="arm_profile_form_existing_options">
										<label style="<?php echo (is_rtl()) ? 'margin-left: 15px;' : 'margin-right: 15px;';?>">
											<input type="radio" name="arm_new_profile_form[existing_type]" value="form" class="arm_iradio add_new_profile_form_existing_type" checked="checked" />
											<?php echo (is_rtl()) ? '' : '&nbsp;';?><?php _e('Inherit from existing signup forms','ARMember');?> (<?php _e('Recommend', 'ARMember');?>)<?php echo (is_rtl()) ? '&nbsp;' : '';?>
										</label>
										<div class="add_new_profile_form_existing_options existing_type_profile_form">
											<?php echo $profile_form_select; ?>
										</div>
										<label style="<?php echo (is_rtl()) ? 'margin-left: 15px;' : 'margin-right: 15px;';?>">
											<input type="radio" name="arm_new_profile_form[existing_type]" value="template" class="arm_iradio add_new_profile_form_existing_type" />
											<?php echo (is_rtl()) ? '' : '&nbsp;'; ?> <?php _e('Select Template','ARMember'); ?><?php echo (is_rtl()) ? '&nbsp;' : '';?>
										</label>
										<div class="add_new_profile_form_existing_options template_type_profile_form" style="margin:0 0 5px 0;display:none;">
											<input id="template_form_edit_profile_val" class="existing_form_select" type="hidden" value="" name="template_form_edit_profile" style="display:none;" />
											<dl id="template_form_edit_profile" class="arm_selectbox existing_form_select" style="display:inline-block">
												<dt>
													<span><?php _e('Select Template','ARMember'); ?></span>
	                                                <input type="text" class="arm_autocomplete" value="" style="display:none;" />
	                                                <i class="armfa armfa-caret-down armfa-lg"></i>
	                                            </dt>
	                                            <dd>
	                                            	<ul data-id="template_form_edit_profile_val" style="<?php echo $add_form_select_style = (is_rtl()) ? 'margin-right: 35px! important;' : ''; ?> width: 362px;">
	                                                    <li data-value="" data-label="<?php _e('Select Template','ARMember'); ?>"><?php _e('Select Template','ARMember'); ?></li>
	                                                    <?php
	                                                        $registration_templates = $wpdb->get_results("SELECT * FROM ".$ARMember->tbl_arm_forms." WHERE arm_is_template ='1' AND arm_form_slug LIKE 'template-registration%' AND arm_form_type='template' ");
	                                                        foreach( $registration_templates as $key => $template ){
	                                                            ?>
	                                                    <li data-value="<?php echo $template->arm_form_id ?>" data-label="<?php echo $template->arm_set_name; ?>"><?php echo $template->arm_set_name; ?></li>
	                                                    <?php
	                                                        }
	                                                    ?>
	                                                </ul>
	                                            </dd>
											</dl>
											<label class="arm_template_form_edit_profile_select_meta" >
	                                            <input type="checkbox" name="arm_meta_profile_fields_for_template" value="meta_fields" class="arm_iradio" id="select_arm_profile_field_metas" />
	                                            <?php echo (is_rtl()) ? '' : '&nbsp;';?><?php _e('Select meta fields','ARMember');?><?php echo (is_rtl()) ? '&nbsp;' : '';?>
	                                        </label>
	                                        <div class="existing_type_profile_field hidden_section" id="arm_existing_type_profile_fields" style="margin-left:60px;">
	                                        	<?php 
		                                            $metaFields = $arm_member_forms->arm_get_db_form_fields(true);
		                                            
		                                            if (!empty($metaFields)) {
		                                                foreach ($metaFields as $_key => $_field) {
		                                                    $fAttr = '';
		                                                    echo '<div class="arm_add_new_edit_profile_form_field arm_field_' . $_key . '">';
		                                                    echo '<label><input type="checkbox" class="arm_icheckbox" name="specific_fields[]" value="' . $_key . '" ' . $fAttr . '> ' . $_field['label'] . '</label>';
		                                                    echo '</div>';
		                                                }
		                                            }
		                                            ?>
		                                            <input type="hidden" name="specific_fields[]" value="submit">
	                                        </div>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<div class="arm_template_preview_wrapper arm_edit_profile_templates" >
                        <?php
                        $reg_temp_id = 1;
                        foreach ($registration_templates as $key => $template) {
                            $arm_set_id = $template->arm_form_id;
                            ?>
                            <div class="arm_image_edit_profile_placeholder_wrapper" data-template-set-id="<?php echo $arm_set_id; ?>" data-set-id="<?php echo $reg_temp_id; ?>">
                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/form_templates/arm_signup_template_' . $reg_temp_id . '.png' ?>" />
                            </div>
                            <?php
                            $reg_temp_id++;
                        }
                        ?>
                    </div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<input type="hidden" name="arm_new_profile_form[arm_form_type]" id="add_new_profile_form_type" value="" />
						<button class="arm_submit_btn arm_add_new_profile_form_submit_btn" type="submit"><?php _e('Add','ARMember');?></button>
						<button class="arm_cancel_btn add_new_profile_form_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
						<?php wp_nonce_field( 'arm_wp_nonce' );?>
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
<?php if(isset($_REQUEST['setup']) && $_REQUEST['setup'] == 'true'):?>
jQuery(window).on("load", function(){
    jQuery('.arm_add_new_form_btn').trigger('click');
});
<?php endif;?>
jQuery(function($) {
	jQuery(document).on('click',".is_specific_field_input", function () {
        var form_type = jQuery('#add_new_form_type').val();
        var form_id = jQuery('#existing_form_'+form_type+'_val').val();
        jQuery('.existing_form_fields').slideUp('slow').addClass('hidden_section');
		if (jQuery(this).is(":checked")) {
            jQuery('.existing_form_fields_'+form_id).slideDown('slow').removeClass('hidden_section');
        }
    });
	jQuery(document).on('click',".new_form_action_type", function (e) {
		e.stopPropagation();
		var opt = jQuery(this).val();
		if(opt == 'page') {
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_redirect').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_referral').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_conditional_redirect').slideUp();    
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_page').slideDown();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_conditional_redirect_info').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_referral_info').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_redirect_info').slideUp();
		} else if(opt == 'url') {
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_page').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_referral').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_conditional_redirect').slideUp();    
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_redirect').slideDown();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_redirect_info').slideDown();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_conditional_redirect_info').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_referral_info').slideUp();
		} else if(opt == 'referral' ){
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_page').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_redirect').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_conditional_redirect').slideUp();    
                    jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_referral').slideDown();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_referral_info').slideDown();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_conditional_redirect_info').slideUp();
                    jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_redirect_info').slideUp();
                }
                else if(opt == 'conditional_redirect')
                {
                   jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_page').slideUp();
                   jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_redirect').slideUp();
                   jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_referral').slideUp();
                   jQuery(this).parents('.arm_form_redirection_options').find('.add_new_form_conditional_redirect').slideDown();
                   jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_conditional_redirect_info').slideDown();
                   jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_referral_info').slideUp();
                   jQuery(this).parents('.arm_form_redirection_options').find('.login_form_action_option_redirect_info').slideUp();
                }
	});
	jQuery(document).on('change','.add_new_form_existing_type', function (e) {
		e.stopPropagation();
		var type = jQuery(this).val();
		if( type === 'form' ){
            jQuery('.add_new_form_existing_options.template_type_form').slideUp();
            jQuery('.add_new_form_existing_options.existing_type_form').slideDown();
        } else if (type === 'template') {
            jQuery('.add_new_form_existing_options.existing_type_form').slideUp();
            jQuery('.add_new_form_existing_options.template_type_form').slideDown();
        }
	});
	jQuery(document).on('change','.add_new_profile_form_existing_type', function(e) {
		e.stopPropagation();
		var type = jQuery(this).val();
		if( 'form' == type ){
			jQuery('.add_new_profile_form_existing_options.template_type_profile_form').slideUp();
	        jQuery('.add_new_profile_form_existing_options.existing_type_profile_form').slideDown();
		} else {
			jQuery('.add_new_profile_form_existing_options.existing_type_profile_form').slideUp();
	        jQuery('.add_new_profile_form_existing_options.template_type_profile_form').slideDown();
		}	
	});
});

jQuery(document).on('change','#select_arm_field_metas',function(e){
    if( jQuery(this).is(':checked') == true ){
        jQuery('#arm_existing_type_fields').slideDown();
    } else {
        jQuery('#arm_existing_type_fields').slideUp();
    }
});

jQuery(document).on('change','#select_arm_profile_field_metas', function(e){
	if( true == jQuery(this).is(':checked') ){
		jQuery('#arm_existing_type_profile_fields').slideDown();
	} else {
		jQuery('#arm_existing_type_profile_fields').slideUp();
	}
});
</script>