<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_buddypress_feature;
$totalUsersToSync = $wpdb->get_var('SELECT COUNT(*) FROM `'.$wpdb->prefix.'users`');
$check_buddyp_buddyb = $arm_buddypress_feature->arm_check_buddypress_buddyboss();
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<div class="page_sub_title"><?php echo __('Map with','ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('Profile Fields','ARMember'); ?></div>
		<div class="armclear"></div>
		<form  method="post" action="#" id="arm_buddypress_settings" class="arm_buddypress_settings_from arm_admin_form">
                    <div class="arm_bp_field_map_div">
                        <div class="arm_bp_fields_map_wrapper">
                            <div class="arm_buddypress_settings_block">
                                <div class="arm_buddypress_settings_column arm_bp_tabel_th"><div class="arm_bp_fields_label"><?php _e('Form Fields', 'ARMember'); ?></div></div>
                                <div class="arm_buddypress_settings_column arm_bp_th"><div class="arm_bp_fields_input"><?php echo $check_buddyp_buddyb['arm_title'].' '.__('Fields', 'ARMember'); ?></div></div>
                            </div>
                            <?php 

                            $arm_get_form_fields = $wpdb->get_results("SELECT `arm_form_field_id`, `arm_form_field_form_id`, `arm_form_field_slug`, `arm_form_field_option`, `arm_form_field_bp_field_id`, `arm_form_field_status` FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_slug` !='' AND `arm_form_field_status` = 1");
                            if(!empty($arm_get_form_fields)){
                                $i = 0;
                                $j = 0;
                                $maparray = $arm_buddypress_feature->arm_map_buddypress_armember_field_types();
                                $form_object = array(); 
                                foreach($arm_get_form_fields as $arm_get_form_field){
                                    $arm_form_field_id = $arm_get_form_field->arm_form_field_id;
                                    $arm_form_field_form_id = $arm_get_form_field->arm_form_field_form_id;
                                    $arm_form_field_slug = $arm_get_form_field->arm_form_field_slug;
                                    $arm_form_field_option= maybe_unserialize($arm_get_form_field->arm_form_field_option);
                                    $arm_from_field_bp_field_id = $arm_get_form_field->arm_form_field_bp_field_id;

                                    $arm_form_field_type = $arm_form_field_option['type'];
                                    $arm_form_field_label = $arm_form_field_option['label'];
                                 
                          
                                    if(isset($form_object[$arm_form_field_form_id]) && !empty($form_object[$arm_form_field_form_id]))
                                    {
                                        
                                      $form = $form_object[$arm_form_field_form_id];
                                        
                                    }
                                    else{
                                      
                                        $form = new ARM_Form('id', $arm_form_field_form_id);
                                        $form_object[$arm_form_field_form_id] = $form;
                                    }
                                    $from_type = $form->type;
                                    $is_default_form = $form->template;
                                    if($form->type == 'registration' && $is_default_form != true){
                                        if (!in_array($arm_form_field_type, array('hidden', 'html', 'info', 'section', 'rememberme', 'submit', 'repeat_pass', 'repeat_email','avatar', 'file', 'password', 'social_fields','arm_captcha'))) {
                                            $maparray_type = isset($maparray[$arm_form_field_type]) ? $maparray[$arm_form_field_type] : '';

                                            $arm_result = $wpdb->get_results("SELECT `id`, `name` FROM `" . $wpdb->prefix . "bp_xprofile_fields` WHERE `parent_id`=0 AND `type` IN ('".implode('\',\'', $maparray_type)."')");
                                            ?>
                                                <div class="arm_buddypress_settings_block">
                                                    <div class="arm_buddypress_settings_column arm_bp_tabel_label_td">
                                                        <div class="arm_bp_fields_label"><?php echo !empty($arm_form_field_label) ? stripslashes_deep($arm_form_field_label) : '&nbsp;'; ?></div>
                                                    </div>
                                                    <div class="arm_buddypress_settings_column arm_bp_tabel_td">
                                                        <div class="arm_bp_fields_input">
                                                            <input type='hidden' id="arm_map_buddypress_field_<?php echo $i; ?>"  value="<?php echo $arm_from_field_bp_field_id; ?>" name="arm_buddypress_field_id[<?php echo $arm_form_field_id; ?>]" />
                                                            <dl class="arm_selectbox column_level_dd arm_width_200">
                                                                <dt><span><?php echo __('Select','ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('Field', 'ARMember'); ?></span>
                                                                <input type="text" style="display:none;" value="" class="arm_autocomplete"  />
                                                                <i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="arm_map_buddypress_field_<?php echo $i; ?>">
                                                                        <li data-label="<?php echo __('Select', 'ARMember') .' '. $check_buddyp_buddyb['arm_title'] .' '.__('field', 'ARMember'); ?>" data-value="">
                                                                            <?php echo __('Select', 'ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('field', 'ARMember'); ?>
                                                                        </li>
                                                                    <?php
                                                                        if(!empty($arm_result)){
                                                                            foreach ($arm_result as $a) {
                                                                    ?>
                                                                            <li data-label="<?php echo $a->name; ?>" data-value="<?php echo $a->id; ?>"><?php echo $a->name ?></li>
                                                                    <?php 
                                                                            } 
                                                                        } 
                                                                    ?>
                                                                    </ul>
                                                                </dd>
                                                            </dl>    
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            $i++;
                                            $j++;
                                        }
                                    }

                                }
                            }
                            ?>
                        </div>                        
                    </div>
                    <div class="armclear"></div>
                    
                    <table class="form-table">
				<tr>
					<th class="arm-form-table-label"><?php echo __('Map with','ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('avatar?','ARMember'); ?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="map_with_buddypress_avatar" value="1" class="armswitch_input" name="map_with_buddypress_avatar" <?php checked($arm_buddypress_feature->map_with_buddypress_avatar, 1);?>/>
							<label for="map_with_buddypress_avatar" class="armswitch_label"></label>
						</div>
                                            
					</td>
				</tr>
                                
                                <tr>
					<th class="arm-form-table-label"><?php echo __('Map with','ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('cover photo?','ARMember'); ?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="map_with_buddypress_profile_cover" value="1" class="armswitch_input" name="map_with_buddypress_profile_cover" <?php checked($arm_buddypress_feature->map_with_buddypress_profile_cover, 1);?>/>
							<label for="map_with_buddypress_profile_cover" class="armswitch_label"></label>
						</div>
                                            
					</td>
				</tr>
                                
                </table>
                    <div class="arm_solid_divider"></div> 
                    <div class="page_sub_title"><?php _e('Map ARMember Profile Page','ARMember'); ?></div>
                    <table class="form-table">   
                    <tr class="form-field">
                            <th class="arm-form-table-label"><?php echo __('Armember Profile page for','ARMember').' '. $check_buddyp_buddyb['arm_title']; ?></th>
                            <td class="arm-form-table-content">
                                <?php 
                                $arm_global_settings->arm_wp_dropdown_pages(
                                        array(
                                                'selected'              => isset($arm_buddypress_feature->show_armember_profile) ? $arm_buddypress_feature->show_armember_profile : 0,
                                                'name'                  => 'show_armember_profile',
                                                'id'                    => 'show_armember_profile',
                                                'show_option_none'      => 'Select Page',
                                                'option_none_value'     => '0',
                                        )
                                );
                                ?>
                                <?php $arm_bp_profile_tooltip = __('Select page to redirect at custom profile page instead','ARMember').' '. $check_buddyp_buddyb['arm_title'].' '.__('default profile page.', 'ARMember'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $arm_bp_profile_tooltip; ?>"></i>                                            <span class="arm_info_text arm_info_text_style" >(<?php echo __('Choose ARMember profile page to replace','ARMember').' '. $check_buddyp_buddyb['arm_title'] .' '. __('profile page.','ARMember'); ?>)</span>
                            </td>
                    </tr>   
                </table>
                    
                    <div class="arm_submit_btn_container arm_buddypress_submit_btn">
				<button class="arm_save_btn arm_buddypress_settings_btn" type="submit" id="arm_buddypress_settings_btn" name="arm_buddypress_settings_btn"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />
                    </div>
                    <?php wp_nonce_field( 'arm_wp_nonce' );?>
                </form>
                
                
                <div class="arm_solid_divider"></div> 
                   <div class="page_sub_title"><?php echo __('Sync','ARMember') . ' ' . $check_buddyp_buddyb['arm_title'] .' '. __('& ARMember','ARMember'); ?></div> 
		<table class="form-table">
                <tr>
                        <th>
                            <?php echo __('How to sync with', 'ARMember') . $check_buddyp_buddyb['arm_title'] .__('?', 'ARMember'); ?>
                        </th>
                        <td  class="arm_vertical_align_top arm_padding_top_15">
                            <input type="radio" name="arm_bp_sync" id="arm_by_sync_pull" value="pull"  class="arm_iradio"><label class="arm_width_230" for="arm_by_sync_pull" ><?php echo __('Pull Data from', 'ARMember').' '. $check_buddyp_buddyb['arm_title']; ?></label>
                            <input type="radio" name="arm_bp_sync" id="arm_by_sync_push" value="push" checked="checked" class="arm_iradio"><label for="arm_by_sync_push"><?php _e('Pull Data from ARMember', 'ARMember'); ?></label>
                            <div class="arm_submit_btn_container arm_buddypress_sync_btn_div">
				<button class="arm_save_btn arm_buddypress_sync_btn" data-total-users="<?php echo $totalUsersToSync; ?>" type="button" id="arm_buddypress_sync_btn" name="arm_buddypress_sync_btn"><?php _e('Sync', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_sync" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />
                                <div class="armclear"></div>
                                <div class="arm_buddypress_sync_progressbar">
                                    <div class="arm_buddypress_sync_progressbar_inner"></div>
                                </div>                                                            
                            </div>
                        </td>
                    </tr>
                </table>
               
                   
                
	</div>
</div>

