<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_restriction;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$general_settings = $all_global_settings['general_settings'];
$page_settings = $all_global_settings['page_settings'];
$general_settings['hide_feed'] = isset($general_settings['hide_feed']) ? $general_settings['hide_feed'] : 0;
$all_plans_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true);
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules = $arm_access_rules->arm_get_default_access_rules();
$all_roles = $arm_global_settings->arm_get_all_roles();

?>

<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		<form method="post" action="#" id="arm_access_restriction" class="arm_access_restriction arm_admin_form" onsubmit="return false;">
                        <?php do_action('arm_before_access_restriction_settings_html', $general_settings);?>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Restrict admin panel','ARMember');?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="restrict_admin_panel" <?php checked($general_settings['restrict_admin_panel'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[restrict_admin_panel]"/>
							<label for="restrict_admin_panel" class="armswitch_label"></label>
						</div>
						<label for="restrict_admin_panel" class="arm_global_setting_switch_label"><?php _e('Restrict admin panel for non-admin users?','ARMember');?></label>
					</td>
				</tr>
                                
                                <tr class="form-field arm_exclude_role_for_restrict_admin<?php echo ($general_settings['restrict_admin_panel'] == '1') ? '' : ' hidden_section' ; ?>">
					<th class="arm-form-table-label"><?php _e('Exclude role for restriction','ARMember'); ?></th>
                                        <td class="arm-form-table-content">
                                            <?php $arm_exclude_role_for_restrict_admin = isset($general_settings['arm_exclude_role_for_restrict_admin']) ? explode(',', $general_settings['arm_exclude_role_for_restrict_admin']) : array(); ?>
                                            <select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox arm_width_500" name="arm_general_settings[arm_exclude_role_for_restrict_admin][]" data-placeholder="<?php _e('Select Role(s)..', 'ARMember');?>" multiple="multiple" >
                                                    <?php
                                                        if (!empty($all_roles)):
                                                            foreach ($all_roles as $role_key => $role_value) {
                                                                ?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($role_key); ?>" <?php echo (in_array($role_key, $arm_exclude_role_for_restrict_admin)) ? ' selected="selected"' : ''; ?>><?php echo stripslashes($role_value);?></option><?php
                                                            }
                                                        else:
                                                    ?>
                                                            <option value=""><?php _e('No Pages Available', 'ARMember');?></option>
                                                    <?php endif;?>
                                            </select>
                                            <span class="arm_info_text arm_info_text_style" >
                                                (<?php _e('Selected roles will be able to access admin.','ARMember'); ?>)
                                            </span>
                                        </td>
				</tr>
                                
                <tr class="form-field">                     <th class="arm-
    form-table-label"><?php _e('Block RSS feeds', 'ARMember');?></th>
    <td class="arm-form-table-content">                         <div
    class="armswitch arm_global_setting_switch">
    <input type="checkbox" id="hide_feed" <?php
    checked($general_settings['hide_feed'], '1');?> value="1"
    class="armswitch_input" name="arm_general_settings[hide_feed]"/>
    <label for="hide_feed" class="armswitch_label"></label>
    </div>                         <label for="hide_feed"
    class="arm_global_setting_switch_label"><?php _e('Disable feeds access to
    everyone?','ARMember');?></label>                     </td>
    </tr>       
                                
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Restrict entire website without login','ARMember');?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch" style="display: inline-block;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;margin: <?php echo (is_rtl()) ? '7px 0 0 10px' : '7px 10px 0 0';?>;">
							<input type="checkbox" id="restrict_site_access" <?php checked($general_settings['restrict_site_access'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[restrict_site_access]"/>
							<label for="restrict_site_access" class="armswitch_label"></label>
						</div>
						<div class="restrict_site_access <?php echo ($general_settings['restrict_site_access'] == 1) ? '':'hidden_section';?>">
							<div class="arm_info_text arm_margin_bottom_20" style=""><?php _e('If website is restricted, redirect visitor to following page','ARMember');?>:</div>
							<?php
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'              => isset($page_settings['guest_page_id']) ? $page_settings['guest_page_id'] : 0,
									'name'                  => 'arm_page_settings[guest_page_id]',
									'id'                    => 'guest_page_id',
									'show_option_none'      => __('Select Page','ARMember'),
									'option_none_value'     => '0',
									'class'     => 'arm_regular_select',
								)
							);
							?>
                                                        <span id="guest_page_id_error" class="arm_error_msg guest_page_id_error" style="display:none;"><?php _e('Please select guest page.', 'ARMember');?></span>
						</div>
					</td>
				</tr>
                                
                                <tr class="form_field page_access_for_restrict_site" <?php echo ($general_settings['restrict_site_access'] != 1) ? 'style="display:none;"' : ''; ?>>
                                    <th class="arm-form-table-label"><?php _e('Exclude pages for restriction','ARMember');?></th>
                                    <td class="arm-form-table-content">
                                        <?php
                                        $defaults = array(
                                                'depth' => 0, 'child_of' => 0,
                                                'selected' => 0, 'echo' => 1,
                                                'name' => 'page_id', 'id' => '',
                                                'show_option_none' => 'Select Page', 'show_option_no_change' => '',
                                                'option_none_value' => '',
                                                'class' => '',
                                                'required' => false,
                                                'required_msg' => false,
                                        );
                                        $pages = get_pages($defaults);
                                        $arm_sel_access_page_for_restrict_site = array();
                                        if(isset($page_settings['arm_access_page_for_restrict_site']))
                                        {
                                            $arm_sel_access_page_for_restrict_site = explode(',', $page_settings['arm_access_page_for_restrict_site']);
                                        }
                                        $global_setting_page = $arm_global_settings->arm_get_single_global_settings('page_settings');
                                        $allow_page_ids = $arm_restriction->arm_filter_allow_page_ids($global_setting_page);
                                        ?>
                                        <select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox arm_width_500" name="arm_general_settings[arm_access_page_for_restrict_site][]" data-placeholder="<?php _e('Select Page(s)..', 'ARMember');?>" multiple="multiple" >
                                                <?php
                                                    if (!empty($pages)):
                                                        foreach ($pages as $p) {
                                                            if(in_array($p->ID, $allow_page_ids)){ continue; }
                                                            ?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($p->ID); ?>" <?php echo (in_array($p->ID, $arm_sel_access_page_for_restrict_site)) ? ' selected="selected"' : ''; ?>><?php echo stripslashes($p->post_title);?></option><?php
                                                        }
                                                    else:
                                                ?>
                                                        <option value=""><?php _e('No Pages Available', 'ARMember');?></option>
                                                <?php endif;?>
                                        </select>
                                        <span class="arm_info_text arm_info_text_style">
                                            (<?php _e('Selected pages will be accessible to users without login.','ARMember'); ?>)
                                        </span>
                                    </td>
                                </tr>
                                
                                <tr class="form-field">
                                        <th class="arm-form-table-label"><?php _e('Allow restricted Pages/Posts in listing', 'ARMember'); ?></th>
                                        <td class="arm-form-table-content">
                                            <div class="armswitch arm_global_setting_switch">
                                                    <input type="checkbox" id="arm_allow_content_listing" value="1" class="armswitch_input" name="arm_default_rules[arm_allow_content_listing]" <?php checked(isset($default_rules['arm_allow_content_listing']) ? $default_rules['arm_allow_content_listing'] : 0, 1);?>/>
                                                    <label for="arm_allow_content_listing" class="armswitch_label"></label>
                                            </div>
                                            <span class="arm_info_text arm_info_text_style">
                                                (<?php _e('If you enable this switch than, restricted content will be displayed in listing only.','ARMember'); ?>)
                                            </span>
                                        </td>
                                </tr>
                                
                        </table>
			<div class="arm_solid_divider"></div>
                        <div class="page_sub_title" id="arm_global_default_access_rules">
                            <?php _e('Default Access Rules for newly added Content', 'ARMember'); ?>
                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Please configure default rules to restrict any newly added page, post, category, custom post, etc for which there is no rule defined at Access Rules.", 'ARMember'); ?>"></i>
                        </div>
                        <?php
                        $ruleTypes = array(
                            'page' => __('New Pages', 'ARMember'),
                            'post' => __('New Posts', 'ARMember'),
                            'category' => __('New Categories', 'ARMember'),
                            'nav_menu' => __('New Navigation Menus', 'ARMember'),
                        );
                        if (isset($defaultRulesTypes['post_type']) && !empty($defaultRulesTypes['post_type'])) {
                            foreach ($defaultRulesTypes['post_type'] as $postType => $title) {
                                if (!in_array($postType, $ruleTypes)) {
                                    $ruleTypes[$postType] = __('New', 'ARMember'). ' '. $title;
                                }
                            }
                        }
                        if (isset($defaultRulesTypes['taxonomy']) && !empty($defaultRulesTypes['taxonomy'])) {
                            foreach ($defaultRulesTypes['taxonomy'] as $taxonomy => $title) {
                                if ($taxonomy != 'category') {
                                    $ruleTypes[$taxonomy] = __('New', 'ARMember'). ' '. $title;
                                }
                            }
                        }
                        ?>
                        <table class="form-table">
                            <?php if (!empty($ruleTypes)): ?>
                                <?php 
                                    $arm_default_ar_cntr = 0;
                                    foreach ($ruleTypes as $rtype => $rtitle):
                                        if($arm_default_ar_cntr==4) 
                                        {
                                ?>
                                            <tr class="form-field">
                                                <td colspan="">
                                                    <span class="arm_failed_login_sub_title">
                                                        <strong>
                                                            <?php _e('Custom Post type, Taxonomy, Tag', 'ARMember'); ?>
                                                        </strong>
                                                    </span>
                                                </td>
                                            </tr>
                                <?php
                                        }
                                        $default_rules[$rtype] = (!empty($default_rules[$rtype])) ? $default_rules[$rtype] : array();
                                        $arm_default_restriction_option = '';
                                        if(empty($default_rules[$rtype]))
                                        {
                                            $arm_default_restriction_option = '';
                                        }
                                        else if(is_array($default_rules[$rtype]) && in_array('-2', $default_rules[$rtype]))
                                        {
                                            $arm_default_restriction_option = '-2';
                                        }
                                        else if(!empty($default_rules[$rtype])) {
                                            $arm_default_restriction_option = '1';
                                        }
                                ?>
                                    <tr class="form-field">
                                        <th><?php echo $rtitle; ?></th>
                                        <td>
                                            <label  class="arm_min_width_100">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '');?>  data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                    <span><?php _e('Everyone','ARMember');?></span>
                                            </label>
                                            <label class="arm_min_width_150">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="-2" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '-2');?>  data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                   <span><?php _e('Only logged in member (Everyone)','ARMember');?></span>
                                            </label>
                                            <label class="arm_min_width_150">
                                                    <input type="radio" name="arm_default_restriction_option[<?php echo $rtype; ?>]" value="1" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '1');?> data-cntr="<?php echo $arm_default_ar_cntr; ?>">
                                                    <span><?php _e('Selected Plan(s) Only','ARMember');?></span><br>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="form-field arm_default_access_restrictions_row arm_default_restriction_option_<?php echo $arm_default_ar_cntr; ?>" style="<?php if($arm_default_restriction_option!=1) { ?> display: none; <?php } ?>">
                                        <th>&nbsp;</th>
                                        <td>
                                            <select name="arm_default_rules[<?php echo $rtype; ?>][]" class="arm_default_rule_select arm_chosen_selectbox" multiple data-placeholder="<?php _e('Select Plan', 'ARMember'); ?>" tabindex="-1">
                                                <?php
                                                if (!empty($all_plans_data)) {
                                                    $default_rules[$rtype] = (!empty($default_rules[$rtype])) ? $default_rules[$rtype] : array();
                                                    foreach ($all_plans_data as $plan) {
                                                        if($plan['arm_subscription_plan_id']!='-2')
                                                        {
                                                        ?><option value="<?php echo $plan['arm_subscription_plan_id']; ?>" <?php echo (in_array($plan['arm_subscription_plan_id'], $default_rules[$rtype])) ? 'selected="selected"' : ''; ?>><?php echo stripslashes($plan['arm_subscription_plan_name']); ?></option>
                                            <?php
                                                        }
                                                    }
                                                }
                                            ?>
                                            </select>
                                            <?php $da_tooltip = __("Please select plan(s) for members can access", 'ARMember') . " {$rtitle} " . __("by default.", 'ARMember'); ?>
                                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $da_tooltip; ?>"></i>
                                        </td>
                                    </tr>
                                <?php 
                                        $arm_default_ar_cntr++;
                                        endforeach; 
                                    endif; 
                                ?>
                        </table>
                        
			<?php do_action('arm_after_access_restriction_settings_html', $general_settings);?>
			<div class="arm_submit_btn_container">
				<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />&nbsp;<button id="arm_access_restriction_settings_btn" class="arm_save_btn" name="arm_access_restriction_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>
                <?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
	</div>
</div>