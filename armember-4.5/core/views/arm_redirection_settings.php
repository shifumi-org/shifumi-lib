<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_drip_rules, $arm_subscription_plans, $arm_member_forms, $arm_social_feature,$arm_pay_per_post_feature;


$redirection_settings = get_option('arm_redirection_settings');
$redirection_settings = maybe_unserialize($redirection_settings);

$arm_forms = $arm_member_forms->arm_get_member_forms_and_fields_by_type('registration', 'arm_form_id, arm_form_type, arm_form_label', false);

$arm_edit_profile_forms = $arm_member_forms->arm_get_member_forms_and_fields_by_type('edit_profile', 'arm_form_id, arm_form_type, arm_form_label', false);

$arm_redirection_login_type_main = (isset($redirection_settings['login']['main_type']) && !empty($redirection_settings['login']['type'])) ? $redirection_settings['login']['main_type'] : 'fixed';
$arm_redirection_login_type = (isset($redirection_settings['login']['type']) && !empty($redirection_settings['login']['type'])) ? $redirection_settings['login']['type'] : 'page';
$arm_redirection_signup_redirection_type = (isset($redirection_settings['signup']['redirect_type']) && !empty($redirection_settings['signup']['redirect_type'])) ? $redirection_settings['signup']['redirect_type'] : 'common';
$arm_redirection_signup_type = (isset($redirection_settings['signup']['type']) && !empty($redirection_settings['signup']['type'])) ? $redirection_settings['signup']['type'] : 'page';

$arm_redirection_edit_profile_redirection_type = (isset($redirection_settings['edit_profile']['redirect_type']) && !empty($redirection_settings['edit_profile']['redirect_type'])) ? $redirection_settings['edit_profile']['redirect_type'] : 'message';
$arm_redirection_edit_profile_type = (isset($redirection_settings['edit_profile']['type']) && !empty($redirection_settings['edit_profile']['type'])) ? $redirection_settings['edit_profile']['type'] : 'page';


$arm_redirection_social_type = (isset($redirection_settings['social']['type']) && !empty($redirection_settings['social']['type'])) ? $redirection_settings['social']['type'] : 'page';
$arm_default_signup_url = (isset($redirection_settings['signup']['default']) && !empty($redirection_settings['signup']['default'])) ? $redirection_settings['signup']['default'] : ARM_HOME_URL;

$arm_default_edit_profile_url = (isset($redirection_settings['edit_profile']['default']) && !empty($redirection_settings['edit_profile']['default'])) ? $redirection_settings['edit_profile']['default'] : ARM_HOME_URL;

$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$page_settings = $arm_all_global_settings['page_settings'];

$edit_profile_page_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
$arm_redirection_login_page_id = (isset($redirection_settings['login']['page_id']) && !empty($redirection_settings['login']['page_id'])) ? $redirection_settings['login']['page_id'] : 0;
$arm_redirection_login_url = (isset($redirection_settings['login']['url']) && !empty($redirection_settings['login']['url'])) ? $redirection_settings['login']['url'] : '';
$arm_redirection_login_refferel = (isset($redirection_settings['login']['refferel']) && !empty($redirection_settings['login']['refferel'])) ? $redirection_settings['login']['refferel'] : '';
$arm_redirection_login_conditional = (isset($redirection_settings['login']['conditional_redirect']) && !empty($redirection_settings['login']['conditional_redirect'])) ? $redirection_settings['login']['conditional_redirect'] : array();
$arm_redirection_signup_conditional = (isset($redirection_settings['signup']['conditional_redirect']) && !empty($redirection_settings['signup']['conditional_redirect'])) ? $redirection_settings['signup']['conditional_redirect'] : array();

$arm_redirection_edit_profile_conditional = (isset($redirection_settings['edit_profile']['conditional_redirect']) && !empty($redirection_settings['edit_profile']['conditional_redirect'])) ? $redirection_settings['edit_profile']['conditional_redirect'] : array();



$arm_redirection_setup_signup_type = (isset($redirection_settings['setup_signup']['type']) && !empty($redirection_settings['setup_signup']['type'])) ? $redirection_settings['setup_signup']['type'] : 'page';
$arm_redirection_setup_signup_page_id = (isset($redirection_settings['setup_signup']['page_id']) && !empty($redirection_settings['setup_signup']['page_id'])) ? $redirection_settings['setup_signup']['page_id'] : 0;
$arm_redirection_setup_signup_url = (isset($redirection_settings['setup_signup']['url']) && !empty($redirection_settings['setup_signup']['url'])) ? $redirection_settings['setup_signup']['url'] : ARM_HOME_URL;
$arm_redirection_setup_signup_conditional_redirect = (isset($redirection_settings['setup_signup']['conditional_redirect']) && !empty($redirection_settings['setup_signup']['conditional_redirect'])) ? $redirection_settings['setup_signup']['conditional_redirect'] : array();

$arm_redirection_setup_paid_post_type=(isset($redirection_settings['setup_paid_post']['type']) && !empty($redirection_settings['setup_paid_post']['type'])) ? $redirection_settings['setup_paid_post']['type'] : '0';
$arm_redirection_setup_paid_post_page_id = (isset($redirection_settings['setup_paid_post']['page_id']) && !empty($redirection_settings['setup_paid_post']['page_id'])) ? $redirection_settings['setup_paid_post']['page_id'] : 0;

$arm_redirection_setup_change_type = (isset($redirection_settings['setup_change']['type']) && !empty($redirection_settings['setup_change']['type'])) ? $redirection_settings['setup_change']['type'] : 'page';
$arm_redirection_setup_change_page_id = (isset($redirection_settings['setup_change']['type']) && !empty($redirection_settings['setup_change']['page_id'])) ? $redirection_settings['setup_change']['page_id'] : 0;
$arm_redirection_setup_change_url = (isset($redirection_settings['setup_change']['url']) && !empty($redirection_settings['setup_change']['url'])) ? $redirection_settings['setup_change']['url'] : ARM_HOME_URL;


$arm_redirection_setup_renew_type = (isset($redirection_settings['setup_renew']['type']) && !empty($redirection_settings['setup_renew']['type'])) ? $redirection_settings['setup_renew']['type'] : 'page';
$arm_redirection_setup_renew_page_id = (isset($redirection_settings['setup_renew']['type']) && !empty($redirection_settings['setup_renew']['page_id'])) ? $redirection_settings['setup_renew']['page_id'] : 0;
$arm_redirection_setup_renew_url = (isset($redirection_settings['setup_renew']['url']) && !empty($redirection_settings['setup_renew']['url'])) ? $redirection_settings['setup_renew']['url'] : ARM_HOME_URL;
$arm_default_setup_url = (isset($redirection_settings['setup']['default']) && !empty($redirection_settings['setup']['default'])) ? $redirection_settings['setup']['default'] : ARM_HOME_URL;

$arm_redirection_signup_page_id = (isset($redirection_settings['signup']['page_id']) && !empty($redirection_settings['signup']['page_id'])) ? $redirection_settings['signup']['page_id'] : 0;
$arm_redirection_signup_url = (isset($redirection_settings['signup']['url']) && !empty($redirection_settings['signup']['url'])) ? $redirection_settings['signup']['url'] : '';

$arm_redirection_edit_profile_page_id = (isset($redirection_settings['edit_profile']['page_id']) && !empty($redirection_settings['edit_profile']['page_id'])) ? $redirection_settings['edit_profile']['page_id'] : 0;
$arm_redirection_edit_profile_url = (isset($redirection_settings['edit_profile']['url']) && !empty($redirection_settings['edit_profile']['url'])) ? $redirection_settings['edit_profile']['url'] : '';

$arm_redirection_social_page_id = (isset($redirection_settings['social']['page_id']) && !empty($redirection_settings['social']['page_id'])) ? $redirection_settings['social']['page_id'] : 0;
$arm_redirection_social_url = (isset($redirection_settings['social']['url']) && !empty($redirection_settings['social']['url'])) ? $redirection_settings['social']['url'] : '';

$arm_redirection_oneclick = (isset($redirection_settings['oneclick']['redirect_to']) && !empty($redirection_settings['oneclick']['redirect_to'])) ? $redirection_settings['oneclick']['redirect_to'] : 0;

$arm_default_redirection_rules = (isset($redirection_settings['default_access_rules']) && !empty($redirection_settings['default_access_rules'])) ? $redirection_settings['default_access_rules'] : array();

$arm_non_logged_in_type = $arm_logged_in_type = $arm_drip_type = $arm_blocked_type = $arm_pending_type = 'home'; 
$arm_non_logged_in_redirect_to = $arm_logged_in_redirect_to = $arm_drip_redirect_to = $arm_blocked_redirect_to = $arm_pending_redirect_to = 0;



if(!empty($arm_default_redirection_rules)){
    $arm_non_logged_in_type = (isset($arm_default_redirection_rules['non_logged_in']['type']) && !empty($arm_default_redirection_rules['non_logged_in']['type'])) ? $arm_default_redirection_rules['non_logged_in']['type'] : 'home'; 
    $arm_non_logged_in_redirect_to = (isset($arm_default_redirection_rules['non_logged_in']['redirect_to']) && !empty($arm_default_redirection_rules['non_logged_in']['redirect_to'])) ? $arm_default_redirection_rules['non_logged_in']['redirect_to'] : 0; 
    
    $arm_logged_in_type = (isset($arm_default_redirection_rules['logged_in']['type']) && !empty($arm_default_redirection_rules['logged_in']['type'])) ? $arm_default_redirection_rules['logged_in']['type'] : 'home'; 
    $arm_logged_in_redirect_to = (isset($arm_default_redirection_rules['logged_in']['redirect_to']) && !empty($arm_default_redirection_rules['logged_in']['redirect_to'])) ? $arm_default_redirection_rules['logged_in']['redirect_to'] : 0; 

    $arm_drip_type = (isset($arm_default_redirection_rules['drip']['type']) && !empty($arm_default_redirection_rules['drip']['type'])) ? $arm_default_redirection_rules['drip']['type'] : 'home'; 
    $arm_drip_redirect_to = (isset($arm_default_redirection_rules['drip']['redirect_to']) && !empty($arm_default_redirection_rules['drip']['redirect_to'])) ? $arm_default_redirection_rules['drip']['redirect_to'] : 0; 
    
    $arm_blocked_type = (isset($arm_default_redirection_rules['blocked']['type']) && !empty($arm_default_redirection_rules['blocked']['type'])) ? $arm_default_redirection_rules['blocked']['type'] : 'home'; 
    $arm_blocked_redirect_to = (isset($arm_default_redirection_rules['blocked']['redirect_to']) && !empty($arm_default_redirection_rules['blocked']['redirect_to'])) ? $arm_default_redirection_rules['blocked']['redirect_to'] : 0; 
    
    //$arm_pending_type = (isset($arm_default_redirection_rules['pending']['type']) && !empty($arm_default_redirection_rules['pending']['type'])) ? $arm_default_redirection_rules['pending']['type'] : 'home'; 
    //$arm_pending_redirect_to = (isset($arm_default_redirection_rules['pending']['redirect_to']) && !empty($arm_default_redirection_rules['pending']['redirect_to'])) ? $arm_default_redirection_rules['pending']['redirect_to'] : 0; 

}
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		
		
		<form  method="post" action="#" id="arm_redirection_settings" class="arm_admin_form">
                    <div class="page_sub_title"><?php _e('After Login Redirection Rules','ARMember'); ?></div>
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Select Redirection Type','ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[login][main_type]" value="fixed" class="arm_redirection_settings_login_radio_type arm_iradio" <?php checked($arm_redirection_login_type_main, 'fixed'); ?>>
                                        <?php _e('Fixed Redirection','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[login][main_type]" value="conditional_redirect" class="arm_redirection_settings_login_radio_type arm_iradio" <?php checked($arm_redirection_login_type_main, 'conditional_redirect');?>>
                                        <?php _e('Conditional Redirection','ARMember');?>
                                </label>
                            </td>
                        </tr>
                        <tr id="arm_redirection_login_setting_fixed" class="arm_redirection_setting_login <?php if(($arm_redirection_login_type != 'page' && $arm_redirection_login_type != 'url' && $arm_redirection_login_type != 'referral') || $arm_redirection_login_type_main != 'fixed') { echo 'hidden_section'; }?>">
                            <th class="arm-form-table-label"><?php _e('Redirect To','ARMember');?></th>
                            <td>
                                <label style="min-width: 100px;margin-bottom: 10px">
                                        <input type="radio" name="arm_redirection_settings[login][type]" value="page" class="arm_redirection_settings_login_radio arm_iradio" <?php checked($arm_redirection_login_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;margin-bottom: 10px">
                                        <input type="radio" name="arm_redirection_settings[login][type]" value="url" class="arm_redirection_settings_login_radio arm_iradio" <?php checked($arm_redirection_login_type, 'url');?>>
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                <label style="min-width: 100px;margin-bottom: 10px">
                                        <input type="radio" name="arm_redirection_settings[login][type]" value="referral" class="arm_redirection_settings_login_radio arm_iradio" <?php checked($arm_redirection_login_type, 'referral');?>>
                                        <?php _e('Referrer Page','ARMember');?><br>
                                        <span class="arm_info_text" style="margin: 0 30px;position: absolute;font-size: 13px;"><?php _e('(Original page before login.)','ARMember');?></span>
                                </label>
                            </td>
                        </tr>
                        <tr id="arm_redirection_login_settings_page" class="arm_redirection_settings_login <?php if($arm_redirection_login_type != 'page' || $arm_redirection_login_type_main != 'fixed') { echo 'hidden_section'; }?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_login_page_id,
                                                    'name' => 'arm_redirection_settings[login][page_id]',
                                                    'id' => 'arm_login_redirection_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'arm_login_redirection_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                                    ?>
                                <span class="arm_redirection_login_page_selection">
                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                </span>
                                </div>
                            </td>
                        </tr>
                        <tr id="arm_redirection_login_settings_url" class="arm_redirection_settings_login <?php if($arm_redirection_login_type != 'url' || $arm_redirection_login_type_main != 'fixed') { echo 'hidden_section'; }?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[login][url]" value="<?php echo $arm_redirection_login_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_login_redirection_url"><br/>
                                    <span class="arm_redirection_login_url_selection">
                                            <?php _e('Please enter URL.', 'ARMember'); ?>
                                        </span>
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr id="arm_redirection_login_settings_referral" class="arm_redirection_settings_login <?php if($arm_redirection_login_type != 'referral' || $arm_redirection_login_type_main != 'fixed') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Default Redirect URL', 'ARMember'); ?></span><br>
                                    <span class="arm_info_text" style="margin: 0 5px;"><?php _e('(If no referrer page.)', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[login][refferel]" value="<?php echo $arm_redirection_login_refferel; ?>" data-msg-required="<?php _e('Please Enter URL.', 'ARMember');?>" class="arm_member_form_input arm_login_redirection_referel"><br/>
                                    <span class="arm_redirection_login_referel_selection">
                                                <?php _e('Please enter URL.', 'ARMember'); ?>
                                            </span>   
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr id="arm_redirection_login_settings_conditional_redirect" class="arm_redirection_settings_login <?php if($arm_redirection_login_type_main != 'conditional_redirect') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>  
                                <div class="arm_login_conditional_redirection_main_div">
                                <span class="arm_info_text"><?php _e('Add Conditional Rules', 'ARMember'); ?></span><br/><br/>
                                    <?php
                                        $default_redirect_url = (isset($arm_redirection_login_conditional['default']) && !empty($arm_redirection_login_conditional['default'])) ? $arm_redirection_login_conditional['default'] : ARM_HOME_URL;
                                    ?>
                                    <ul class="arm_login_conditional_redirection_ul ui-sortable" style="margin-bottom: 20px;">
                                    <?php
                                    if(empty($arm_redirection_login_conditional)){
                                        $ckey = 1;
                                    $plan_id = 0;
                                    $condition = '';
                                    $url = ARM_HOME_URL;
                                    ?>
                                    <li id="arm_login_conditional_redirection_box0" class="arm_login_conditional_redirection_box_div">
                                        <div class="arm_login_redirection_condition_sortable_icon ui-sortable-handle armhelptip" title="<?php _e('Set Redirection Priority', 'ARMember'); ?>"></div>
                                        <a class="arm_remove_login_redirection_condition" href="javascript:void(0)" data-index="0">
                                            <img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" />
                                        </a>
                                        <table>
                                        <tr class="arm_login_conditional_redirection_row">
                                            <td><?php _e('If User Has', 'ARMember'); ?></td>
                                            <td id="arm_condition_redirect_login_plan_td_0">
                                                    <span class="arm_rr_login_condition_lbl"><?php _e('Membership Plan', 'ARMember'); ?></span><br/>
                                                    <input type='hidden' id='arm_conditional_redirect_plan_id_0' name="arm_redirection_settings[login][conditional_redirect][0][plan_id]" value="<?php echo $plan_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_login_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_plan_id_0">
                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value="0"><?php _e('Select Plan', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('No Plan', 'ARMember'); ?>" data-value="-2"><?php _e('No Plan', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Any Plan', 'ARMember'); ?>" data-value="-3"><?php _e('Any Plan', 'ARMember'); ?></li>
                                                                
                                                               <ol class="arm_selectbox_heading"><?php _e('Select Plans', 'ARMember'); ?></ol>
                                                               <?php  
                                                               if (!empty($all_plans)) {
                                                                   foreach ($all_plans as $p) {
                                                                       $p_id = $p['arm_subscription_plan_id'];
                                                                       ?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']); ?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                                       }
                                                                   }
                                                                   ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_redirection_settings_condition_plan_id_0">
                                                        <?php _e('Please select plan.', 'ARMember'); ?>
                                                    </span>  
                                            </td>
                                            <td width="11px" class="arm_login_redirection_and_lbl"><?php _e('&', 'ARMember'); ?></td>
                                            <td width="290px" class="arm_login_redirection_action">
                                                <span class="arm_rr_login_condition_lbl"><?php _e('Action', 'ARMember'); ?></span><br/>
                                                <input type='hidden' id='arm_conditional_redirect_condition_0' class="arm_redirection_condition_input" name="arm_redirection_settings[login][conditional_redirect][0][condition]" value='<?php echo $condition; ?>' data-key='0' />
                                                <dl class="arm_selectbox column_level_dd">
                                                     <dt class="arm_login_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                     <dd>
                                                         <ul data-id="arm_conditional_redirect_condition_0">
                                                            <li data-label="<?php _e('Any Condition', 'ARMember'); ?>" data-value=""><?php _e('Any Condition', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('First Time Logged In', 'ARMember'); ?>" data-value="first_time"><?php _e('First Time Logged In', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('In Trial', 'ARMember'); ?>" data-value="in_trial"><?php _e('In Trial', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('In Grace Period', 'ARMember'); ?>" data-value="in_grace"><?php _e('In Grace Period', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('Failed Payment(Suspended)', 'ARMember'); ?>" data-value="faled_payment"><?php _e('Failed Payment(Suspended)', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('Pending', 'ARMember'); ?>" data-value="pending"><?php _e('Pending', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('Before Expiration Of', 'ARMember'); ?>" data-value="before_expire"><?php _e('Before Expiration Of', 'ARMember'); ?></li>
                                                         </ul>
                                                     </dd>
                                                 </dl>
                                                 
                                                <div id="arm_redirection_expiration_days_0" class="arm_redirection_expiration_days <?php if($condition !='before_expire'){ echo 'hidden_section'; } ?>">
                                                
                                                    <input type='hidden' id='arm_conditional_redirect_expire_0' name="arm_redirection_settings[login][conditional_redirect][0][expire]" value='0' />
                                                    <dl class="arm_selectbox column_level_dd">
                                                         <dt style="min-width: 30px;width: 30px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                         <dd>
                                                             <ul data-id="arm_conditional_redirect_expire_0">
                                                                 <?php 
                                                                 for($i = 0; $i<=30; $i++){
                                                                    ?>
                                                                  <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                 <?php
                                                                 }
                                                                 ?>
                                                             </ul>
                                                         </dd>
                                                    </dl>
                                                <?php _e(' Days', 'ARMember'); ?>
                                                </div>
                                                <span class="arm_rsc_error arm_redirection_settings_condition_redirect_0">
                                                    <?php _e('Please select condition.', 'ARMember'); ?>
                                                </span> 
                                            </td>
                                        </tr>
                                        <tr class="arm_login_conditional_redirection_row">
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3">
                                                <span class="arm_rr_login_condition_lbl"><?php _e('Select Page', 'ARMember'); ?></span><br/>
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => 0,
                                                                'name' => 'arm_redirection_settings[login][conditional_redirect][0][url]',
                                                                'id' => 'arm_login_conditional_redirection_url_0',
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_login_conditional_redirection_page',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        ),
                                                        'arm_login_conditional_redirection_page_dd'
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_condition_url_0">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                       <?php
                                    }
                                    else{
                                        $ckey = 0;
                                     
                                        foreach($arm_redirection_login_conditional as $arm_login_conditional){
                                            if(is_array($arm_login_conditional)){
                                            $plan_id = (isset($arm_login_conditional['plan_id']) && !empty($arm_login_conditional['plan_id'])) ? $arm_login_conditional['plan_id'] : 0;
                                            $condition = (isset($arm_login_conditional['condition']) && !empty($arm_login_conditional['condition'])) ? $arm_login_conditional['condition'] : '';
                                            $expiration_days = (isset($arm_login_conditional['expire']) && !empty($arm_login_conditional['expire'])) ? $arm_login_conditional['expire'] : 0;
                                            $url = (isset($arm_login_conditional['url']) && !empty($arm_login_conditional['url'])) ? $arm_login_conditional['url'] : ARM_HOME_URL;
                                            ?>
                                    <li id="arm_login_conditional_redirection_box<?php echo $ckey; ?>" class="arm_login_conditional_redirection_box_div">
                                        <div class="arm_login_redirection_condition_sortable_icon ui-sortable-handle armhelptip" title="<?php _e('Set Redirection Priority', 'ARMember'); ?>"></div>
                                        <a class="arm_remove_login_redirection_condition" href="javascript:void(0)" data-index="<?php echo $ckey; ?>">
                                            <img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" />
                                        </a>
                                    <table>
                                        <tr class="arm_login_conditional_redirection_row">
                                            <td><?php _e('If User Has', 'ARMember'); ?></td>
                                            <td id="arm_condition_redirect_login_plan_td_<?php echo $ckey; ?>">
                                                <span class="arm_rr_login_condition_lbl"><?php _e('Membership Plan', 'ARMember'); ?></span><br/>
                                                <input type='hidden' id='arm_conditional_redirect_plan_id_<?php echo $ckey; ?>' name="arm_redirection_settings[login][conditional_redirect][<?php echo $ckey; ?>][plan_id]" value='<?php echo $plan_id; ?>' />
                                                <dl class="arm_selectbox column_level_dd">
                                                    <dt class="arm_login_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_conditional_redirect_plan_id_<?php echo $ckey; ?>">
                                                            <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value="0"><?php _e('Select Plan', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('No Plan', 'ARMember'); ?>" data-value="-2"><?php _e('No Plan', 'ARMember'); ?></li>
                                                            <li data-label="<?php _e('Any Plan', 'ARMember'); ?>" data-value="-3"><?php _e('Any Plan', 'ARMember'); ?></li>
                                                            <ol class="arm_selectbox_heading"><?php _e('Choose Plan', 'ARMember'); ?></ol>
                                                            <?php
                                                            if (!empty($all_plans)) {
                                                            foreach ($all_plans as $p) {
                                                                $p_id = $p['arm_subscription_plan_id'];
                                                                ?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']); ?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                                }
                                                            }
                                                            ?>
                                                        </ul>
                                                    </dd>
                                                </dl>
                                                <span class="arm_rsc_error arm_redirection_settings_condition_plan_id_<?php echo $ckey; ?>">
                                                    <?php _e('Please select plan.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                            <td width="11px" class="arm_login_redirection_and_lbl"><?php _e('&', 'ARMember'); ?></td>
                                            <td width="290px" class="arm_login_redirection_action">
                                                <span class="arm_rr_login_condition_lbl"><?php _e('Action', 'ARMember'); ?></span><br/>
                                                <input type='hidden' id='arm_conditional_redirect_condition_<?php echo $ckey; ?>' class="arm_redirection_condition_input" name="arm_redirection_settings[login][conditional_redirect][<?php echo $ckey; ?>][condition]" value='<?php echo $condition; ?>' data-key="<?php echo $ckey;?> " />
                                                <dl class="arm_selectbox column_level_dd">
                                                    <dt class="arm_login_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_conditional_redirect_condition_<?php echo $ckey; ?>">
                                                           <li data-label="<?php _e('Any Condition', 'ARMember'); ?>" data-value=""><?php _e('Any Condition', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('First Time Logged In', 'ARMember'); ?>" data-value="first_time"><?php _e('First Time Logged In', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('In Trial', 'ARMember'); ?>" data-value="in_trial"><?php _e('In Trial', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('In Grace Period', 'ARMember'); ?>" data-value="in_grace"><?php _e('In Grace Period', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('Failed Payment(Suspended)', 'ARMember'); ?>" data-value="failed_payment"><?php _e('Failed Payment(Suspended)', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('Pending', 'ARMember'); ?>" data-value="pending"><?php _e('Pending', 'ARMember'); ?></li>
                                                           <li data-label="<?php _e('Before Expiration Of', 'ARMember'); ?>" data-value="before_expire"><?php _e('Before Expiration Of', 'ARMember'); ?></li>
                                                        </ul>
                                                    </dd>
                                                </dl>
                                                
                                                <div id="arm_redirection_expiration_days_<?php echo $ckey; ?>" class="arm_redirection_expiration_days <?php if($condition !='before_expire'){ echo 'hidden_section'; } ?>" >
                                                    <input type='hidden' id='arm_conditional_redirect_expire_<?php echo $ckey; ?>' name="arm_redirection_settings[login][conditional_redirect][<?php echo $ckey; ?>][expire]" value='<?php echo $expiration_days; ?>' />
                                                    <dl class="arm_selectbox column_level_dd">
                                                         <dt style="min-width: 30px;width: 30px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                         <dd>
                                                             <ul data-id="arm_conditional_redirect_expire_<?php echo $ckey; ?>">
                                                                 <?php 
                                                                 for($i = 0; $i<=30; $i++){
                                                                    ?>
                                                                  <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                 <?php
                                                                 }
                                                                 ?>
                                                             </ul>
                                                         </dd>
                                                    </dl>
                                                    
                                                <?php _e(' Days', 'ARMember'); ?>
                                                </div>
                                                <span class="arm_rsc_error arm_redirection_settings_condition_redirect_<?php echo $ckey; ?>">
                                                    <?php _e('Please select condition.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        <tr class="arm_login_conditional_redirection_row">
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3">
                                                <span class="arm_rr_login_condition_lbl"><?php _e('Select Page', 'ARMember'); ?></span><br/>
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => $url,
                                                                'name' => 'arm_redirection_settings[login][conditional_redirect]['.$ckey.'][url]',
                                                                'id' => 'arm_login_conditional_redirection_url_'.$ckey,
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_login_redirection_page',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        ),
                                                        'arm_login_conditional_redirection_page_dd'
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_condition_url_<?php echo $ckey; ?>">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr></table>
                                    </li>
                                            <?php 
                                            $ckey++;
                                            }
                                        }
                                    }
                                    ?>
                                            
                                    
                                
                                    </ul>
                                    <div class="arm_login_conditional_redirection_link">
                                        <input id="arm_total_login_conditional_redirection_condition" name="arm_total_login_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <input id="arm_order_login_conditional_redirection_condition" name="arm_order_login_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <a id="arm_login_conditional_redirection_add_new_condition" class="arm_login_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php _e('Add New Condition', 'ARMember'); ?></a>
                                    </div><br/><br/>
                                    <div class="arm_default_redirection_lbl">
                                    <span>
                                        <?php _e('Default Redirect URL', 'ARMember'); ?>
                                    </span>  </div>
                                    <div class="arm_default_redirection_txt arm_default_redirection_full">
                                    <input type="text" name="arm_redirection_settings[login][conditional_redirect][default]" value="<?php echo $default_redirect_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_login_redirection_conditional_redirection">
                                    <span class="arm_redirection_login_conditional_redirection_selection">
                                            <?php _e('Please enter URL.', 'ARMember'); ?>
                                        </span>
                                    <span class="arm_info_text"><?php _e('Default Redirect to above url if any of above conditions do not match.', 'ARMember'); ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="arm_solid_divider"></div> 
                    <div class="page_sub_title"><?php _e('After Basic SignUp Redirection Rules','ARMember'); ?></div>
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Select Redirection Type', 'ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[signup][redirect_type]" value="common" class="arm_redirection_settings_signup_redirection_type arm_iradio" <?php checked($arm_redirection_signup_redirection_type, 'common');?>>
                                        <?php _e('Fixed Redirection','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[signup][redirect_type]" value="formwise" class="arm_redirection_settings_signup_redirection_type arm_iradio" <?php checked($arm_redirection_signup_redirection_type, 'formwise');?> >
                                       <?php _e('Form wise redirection','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        
                        <tr class="arm_redirection_signup_common_settings arm_redirection_settings_signup <?php if($arm_redirection_signup_redirection_type != 'common') { echo 'hidden_section'; } ?>">
                            <th class="arm-form-table-label"><?php _e('Default Redirect To','ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[signup][type]" value="page" class="arm_redirection_settings_signup_radio arm_iradio" <?php checked($arm_redirection_signup_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[signup][type]" value="url" class="arm_redirection_settings_signup_radio arm_iradio" <?php checked($arm_redirection_signup_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        <tr id="arm_redirection_signup_settings_page" class="arm_redirection_signup_common_settings arm_redirection_settings_signup arm_signup_settings_common <?php if($arm_redirection_signup_type != 'page' || $arm_redirection_signup_redirection_type != 'common' ) { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    

                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_signup_page_id,
                                                    'name' => 'arm_redirection_settings[signup][page_id]',
                                                    'id' => 'form_action_signup_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_signup_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                                    ?>
                                    <span class="arm_redirection_signup_page_selection"><?php _e('Please select Page.', 'ARMember'); ?></span> 
                                </div>
                            </td>
                        </tr>
                        <tr id="arm_redirection_signup_settings_url" class="arm_redirection_signup_common_settings arm_redirection_settings_signup arm_signup_settings_common <?php if($arm_redirection_signup_type != 'url' || $arm_redirection_signup_redirection_type != 'common') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[signup][url]" value="<?php echo $arm_redirection_signup_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_signup_redirection_url"><br/>
                                    <span class="arm_redirection_signup_url_selection"><?php _e('Please enter URL.', 'ARMember'); ?></span>           
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
                                </div>
                            </td>
                        </tr>
                        
                        
                        <tr  class="arm_redirection_signup_formwise_settings arm_redirection_settings_signup <?php if($arm_redirection_signup_redirection_type != 'formwise') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>  
                                <div class="arm_signup_conditional_redirection_main_div">
                                    <ul class="arm_signup_conditional_redirection_ul" style="margin-bottom: 20px;">
                                    <?php
                                    if(empty($arm_redirection_signup_conditional)){
                                        $ckey = 1;
                                    $plan_id = 0;
                                    $condition = '';
                                    $url = ARM_HOME_URL;
                                    ?>
                                    <li id="arm_signup_conditional_redirection_box0" class="arm_signup_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_signup_redirection_condition" href="javascript:void(0)" data-index="0"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width=135px"><?php _e('If SignUp form is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_form_id_0' name="arm_redirection_settings[signup][conditional_redirect][0][form_id]" class="arm_form_conditional_redirect" value="<?php echo $form_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_form_id_0">
                                                                
                                                                

                                                                    <li data-label="<?php _e('Select Form','ARMember');?>" data-value="0"><?php _e('Select Form', 'ARMember');?></li>
                                                                    <li data-label="<?php _e('All Forms','ARMember');?>" data-value="-2"><?php _e('All Forms', 'ARMember');?></li>
														<?php if(!empty($arm_forms)): ?>
															<?php foreach($arm_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
																<li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $_form['arm_form_label'];?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															<?php endforeach;?>
														<?php endif;?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_redirection_settings_signup_condition_form_0">
                                                        <?php _e('Please select signup form.', 'ARMember'); ?>
                                                    </span>  
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => 0,
                                                                'name' => 'arm_redirection_settings[signup][conditional_redirect][0][url]',
                                                                'id' => 'arm_signup_conditional_redirection_url_0',
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_signup_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_signup_condition_url_0">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                       <?php
                                    }
                                    else{
                                        $ckey = 0;
                                     
                                        foreach($arm_redirection_signup_conditional as $arm_signup_conditional){
                                            if(is_array($arm_signup_conditional)){
                                            $form_id = (isset($arm_signup_conditional['form_id']) && !empty($arm_signup_conditional['form_id'])) ? $arm_signup_conditional['form_id'] : 0;

                                            $url = (isset($arm_signup_conditional['url']) && !empty($arm_signup_conditional['url'])) ? $arm_signup_conditional['url'] : ARM_HOME_URL;
                                            ?>
                                    <li id="arm_signup_conditional_redirection_box<?php echo $ckey; ?>" class="arm_signup_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_signup_redirection_condition" href="javascript:void(0)" data-index="<?php echo $ckey; ?>"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width="135px"><?php _e('If SignUp form is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_form_id_<?php echo $ckey; ?>' class="arm_form_conditional_redirect" name="arm_redirection_settings[signup][conditional_redirect][<?php echo $ckey; ?>][form_id]" value="<?php echo $form_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_form_id_<?php echo $ckey; ?>">
                                                                
                                                                

                                                                    <li data-label="<?php _e('Select Form','ARMember');?>" data-value="0"><?php _e('Select Form', 'ARMember');?></li>
                                                                    <li data-label="<?php _e('All Forms','ARMember');?>" data-value="-2"><?php _e('All Forms', 'ARMember');?></li>
														<?php if(!empty($arm_forms)): ?>
															<?php foreach($arm_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
																<li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															<?php endforeach;?>
														<?php endif;?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_redirection_settings_signup_condition_form_<?php echo $ckey; ?>">
                                                        <?php _e('Please select signup form.', 'ARMember'); ?>
                                                    </span>  
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => $url,
                                                                'name' => 'arm_redirection_settings[signup][conditional_redirect]['.$ckey.'][url]',
                                                                'id' => 'arm_signup_conditional_redirection_url_'.$ckey,
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_signup_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_signup_condition_url_<?php echo $ckey; ?>">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                                                               <?php 
                                            $ckey++;
                                            }
                                        }
                                    }
                                    ?>
                                            
                                    
                                
                                    </ul>
                                    <div class="arm_signup_conditional_redirection_link">
                                        <input id="arm_total_signup_conditional_redirection_condition" name="arm_total_signup_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <input id="arm_order_signup_conditional_redirection_condition" name="arm_order_signup_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <a id="arm_signup_conditional_redirection_add_new_condition" class="arm_signup_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php _e('Add New Condition', 'ARMember'); ?></a>
                                    </div>
                                    <br/><br/>
                                    <div class="arm_default_redirection_lbl">
                                        <span><?php _e('Default Redirect URL', 'ARMember'); ?></span>   
                                    </div>
                                    <div class="arm_default_redirection_txt arm_default_redirection_full">
                                        <input type="text" name="arm_redirection_settings[signup][default]" value="<?php echo $arm_default_signup_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_signup_redirection_conditional_redirection">
                                        <span class="arm_redirection_signup_conditional_redirection_selection">
                                            <?php _e('Please enter URL.', 'ARMember'); ?>
                                        </span>   
                                        <br/>
                                        <span class="arm_info_text"><?php _e('Default Redirect to above url if any of above conditions do not match.', 'ARMember'); ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>



                    <div class="arm_solid_divider"></div> 
                    <div class="page_sub_title"><?php _e('After Edit Profile Form Redirection Rules','ARMember'); ?></div>
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Select Redirection Type', 'ARMember');?></th>
                            <td class="arm-form-table-content">                     
                                
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[edit_profile][redirect_type]" value="common" class="arm_redirection_settings_edit_profile_redirection_type arm_iradio" <?php checked($arm_redirection_edit_profile_redirection_type, 'common');?>>
                                        <?php _e('Fixed Redirection','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[edit_profile][redirect_type]" value="formwise" class="arm_redirection_settings_edit_profile_redirection_type arm_iradio" <?php checked($arm_redirection_edit_profile_redirection_type, 'formwise');?> >
                                       <?php _e('Form wise redirection','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[edit_profile][redirect_type]" value="message" class="arm_redirection_settings_edit_profile_redirection_type arm_iradio" <?php checked($arm_redirection_edit_profile_redirection_type, 'message');?>>
                                        <?php _e('Success Message','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        

                        <tr class="arm_redirection_edit_profile_common_settings arm_redirection_settings_edit_profile <?php if($arm_redirection_edit_profile_redirection_type != 'common') { echo 'hidden_section'; } ?>">
                            <th class="arm-form-table-label"><?php _e('Default Redirect To','ARMember');?></th>
                            <td class="arm-form-table-content">                     
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[edit_profile][type]" value="page" class="arm_redirection_settings_edit_profile_radio arm_iradio" <?php checked($arm_redirection_edit_profile_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[edit_profile][type]" value="url" class="arm_redirection_settings_edit_profile_radio arm_iradio" <?php checked($arm_redirection_edit_profile_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        <tr id="arm_redirection_edit_profile_settings_page" class="arm_redirection_edit_profile_common_settings arm_redirection_settings_edit_profile arm_edit_profile_settings_common <?php if($arm_redirection_edit_profile_type != 'page' || $arm_redirection_edit_profile_redirection_type != 'common' ) { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    

                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_edit_profile_page_id,
                                                    'name' => 'arm_redirection_settings[edit_profile][page_id]',
                                                    'id' => 'form_action_edit_profile_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_edit_profile_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                                    ?>
                                    <span class="arm_redirection_edit_profile_page_selection"><?php _e('Please select Page.', 'ARMember'); ?></span> 
                                </div>
                            </td>
                        </tr>
                        <tr id="arm_redirection_edit_profile_settings_url" class="arm_redirection_edit_profile_common_settings arm_redirection_settings_edit_profile arm_edit_profile_settings_common <?php if($arm_redirection_edit_profile_type != 'url' || $arm_redirection_edit_profile_redirection_type != 'common') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[edit_profile][url]" value="<?php echo $arm_redirection_edit_profile_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_edit_profile_redirection_url"><br/>
                                    <span class="arm_redirection_edit_profile_url_selection"><?php _e('Please enter URL.', 'ARMember'); ?></span>           
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
                                </div>
                            </td>
                        </tr>
                        
                        
                        <tr  class="arm_redirection_edit_profile_formwise_settings arm_redirection_settings_edit_profile <?php if($arm_redirection_edit_profile_redirection_type != 'formwise') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>  
                                <div class="arm_edit_profile_conditional_redirection_main_div">
                                    <ul class="arm_edit_profile_conditional_redirection_ul" style="margin-bottom: 20px;">
                                    <?php
                                    if(empty($arm_redirection_edit_profile_conditional)){
                                        $ckey = 1;
                                    $plan_id = 0;
                                    $condition = '';
                                    $url = ARM_HOME_URL;
                                    ?>
                                    <li id="arm_edit_profile_conditional_redirection_box0" class="arm_edit_profile_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_edit_profile_redirection_condition" href="javascript:void(0)" data-index="0"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width=135px"><?php _e('If Profile form is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_form_id_0' name="arm_redirection_settings[edit_profile][conditional_redirect][0][form_id]" class="arm_form_conditional_redirect" value="<?php echo $form_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_edit_profile_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_form_id_0">
                                                                
                                                                

                                                                    <li data-label="<?php _e('Select Form','ARMember');?>" data-value="0"><?php _e('Select Form', 'ARMember');?></li>
                                                                    <li data-label="<?php _e('All Forms','ARMember');?>" data-value="-2"><?php _e('All Forms', 'ARMember');?></li>
                                                        <?php if(!empty($arm_edit_profile_forms)): ?>
                                                            <?php foreach($arm_edit_profile_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
                                                                <li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $_form['arm_form_label'];?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
                                                            <?php endforeach;?>
                                                        <?php endif;?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_redirection_settings_edit_profile_condition_form_0">
                                                        <?php _e('Please select Edit Profile form.', 'ARMember'); ?>
                                                    </span>  
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => 0,
                                                                'name' => 'arm_redirection_settings[edit_profile][conditional_redirect][0][url]',
                                                                'id' => 'arm_edit_profile_conditional_redirection_url_0',
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_edit_profile_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_edit_profile_condition_url_0">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                       <?php
                                    }
                                    else{
                                        $ckey = 0;
                                     
                                        foreach($arm_redirection_edit_profile_conditional as $arm_edit_profile_conditional){
                                            if(is_array($arm_edit_profile_conditional)){
                                            $form_id = (isset($arm_edit_profile_conditional['form_id']) && !empty($arm_edit_profile_conditional['form_id'])) ? $arm_edit_profile_conditional['form_id'] : 0;

                                            $url = (isset($arm_edit_profile_conditional['url']) && !empty($arm_edit_profile_conditional['url'])) ? $arm_edit_profile_conditional['url'] : ARM_HOME_URL;
                                            ?>
                                    <li id="arm_edit_profile_conditional_redirection_box<?php echo $ckey; ?>" class="arm_edit_profile_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_edit_profile_redirection_condition" href="javascript:void(0)" data-index="<?php echo $ckey; ?>"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width="135px"><?php _e('If Profile form is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_form_id_<?php echo $ckey; ?>' class="arm_form_conditional_redirect" name="arm_redirection_settings[edit_profile][conditional_redirect][<?php echo $ckey; ?>][form_id]" value="<?php echo $form_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_edit_profile_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_form_id_<?php echo $ckey; ?>">
                                                                
                                                                

                                                                    <li data-label="<?php _e('Select Form','ARMember');?>" data-value="0"><?php _e('Select Form', 'ARMember');?></li>
                                                                    <li data-label="<?php _e('All Forms','ARMember');?>" data-value="-2"><?php _e('All Forms', 'ARMember');?></li>
                                                        <?php if(!empty($arm_edit_profile_forms)): ?>
                                                            <?php foreach($arm_edit_profile_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
                                                                <li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
                                                            <?php endforeach;?>
                                                        <?php endif;?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_redirection_settings_edit_profile_condition_form_<?php echo $ckey; ?>">
                                                        <?php _e('Please select Edit Profile form.', 'ARMember'); ?>
                                                    </span>  
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => $url,
                                                                'name' => 'arm_redirection_settings[edit_profile][conditional_redirect]['.$ckey.'][url]',
                                                                'id' => 'arm_edit_profile_conditional_redirection_url_'.$ckey,
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_edit_profile_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_redirection_settings_edit_profile_condition_url_<?php echo $ckey; ?>">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                                                               <?php 
                                            $ckey++;
                                            }
                                        }
                                    }
                                    ?>
                                            
                                    
                                
                                    </ul>
                                    <div class="arm_edit_profile_conditional_redirection_link">
                                        <input id="arm_total_edit_profile_conditional_redirection_condition" name="arm_total_edit_profile_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <input id="arm_order_edit_profile_conditional_redirection_condition" name="arm_order_edit_profile_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <a id="arm_edit_profile_conditional_redirection_add_new_condition" class="arm_edit_profile_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php _e('Add New Condition', 'ARMember'); ?></a>
                                    </div>
                                    <br/><br/>
                                    <div class="arm_default_redirection_lbl">
                                        <span><?php _e('Default Redirect URL', 'ARMember'); ?></span>   
                                    </div>
                                    <div class="arm_default_redirection_txt arm_default_redirection_full">
                                        <input type="text" name="arm_redirection_settings[edit_profile][default]" value="<?php echo $arm_default_edit_profile_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_edit_profile_redirection_conditional_redirection">
                                        <span class="arm_redirection_edit_profile_conditional_redirection_selection">
                                            <?php _e('Please enter URL.', 'ARMember'); ?>
                                        </span>   
                                        <br/>
                                        <span class="arm_info_text"><?php _e('Default Redirect to above url if any of above conditions do not match.', 'ARMember'); ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>    
                    
                    
                    <div class="arm_solid_divider"></div> 
                    <div class="page_sub_title"><?php _e('After Membership/Plan obtaining Redirection Rules','ARMember'); ?></div>
                    <table class="form-table">
                        
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Redirection after Membership SignUp','ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_signup][type]" value="page" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked($arm_redirection_setup_signup_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_signup][type]" value="url" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked($arm_redirection_setup_signup_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_signup][type]" value="conditional_redirect" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked($arm_redirection_setup_signup_type, 'conditional_redirect');?> >
                                       <?php _e('Conditional Redirect','ARMember');?>
                                </label>
                            </td>
                        </tr>
                        
                        
                        <tr id="arm_redirection_settings_setup_signup_page" class="arm_redirection_settings_setup_signup <?php if($arm_redirection_setup_signup_type != 'page') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_setup_signup_page_id,
                                                    'name' => 'arm_redirection_settings[setup_signup][page_id]',
                                                    'id' => 'arm_form_action_setup_signup_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_setup_signup_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                                    ?>
                                    <span class="arm_form_action_setup_signup_page_require">
                                        <?php _e('Please Select Page.', 'ARMember');?>        
                                    </span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr id="arm_redirection_settings_setup_signup_url" class="arm_redirection_settings_setup_signup <?php if($arm_redirection_setup_signup_type != 'url') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[setup_signup][url]" value="<?php echo $arm_redirection_setup_signup_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_setup_signup_redirection_url" id="arm_setup_signup_redirection_url"><br/>
                                    <span class="arm_setup_signup_redirection_url_require"><?php _e('Please enter URL.', 'ARMember');?></span>     
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
								</div>  
                            </td>
                        </tr>
                        
                        <tr  id="arm_redirection_settings_setup_signup_conditional_redirect" class="arm_redirection_settings_setup_signup <?php if($arm_redirection_setup_signup_type != 'conditional_redirect') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>  
                                <div class="arm_setup_signup_conditional_redirection_main_div">
                                    <ul class="arm_setup_signup_conditional_redirection_ul" style="margin-bottom: 20px;">
                                    <?php
                                    if(empty($arm_redirection_setup_signup_conditional_redirect)){
                                        $ckey = 1;
                                    $plan_id = 0;
                                  
                                    $url = ARM_HOME_URL;
                                    ?>
                                    <li id="arm_setup_signup_conditional_redirection_box0" class="arm_setup_signup_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_setup_signup_redirection_condition" href="javascript:void(0)" data-index="0"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width="160px"><?php _e('If User selected plan is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_setup_plan_0' name="arm_redirection_settings[setup_signup][conditional_redirect][0][plan_id]" value="<?php echo $plan_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_setup_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_setup_plan_0">
                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value="0"><?php _e('Select Plan', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Any Plan', 'ARMember'); ?>" data-value="-3"><?php _e('Any Plan', 'ARMember'); ?></li>
                                                                <?php
                                                                if (!empty($all_plans)) {
                                                                   foreach ($all_plans as $p) {
                                                                       $p_id = $p['arm_subscription_plan_id'];
                                                                       ?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']); ?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                                       }
                                                                   }
                                                                   ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_conditional_redirect_setup_plan_require_0">
                                                        <?php _e('Please select plan.', 'ARMember'); ?>
                                                    </span> 
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php 
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => 0,
                                                                'name' => 'arm_redirection_settings[setup_signup][conditional_redirect][0][url]',
                                                                'id' => 'arm_setup_signup_conditional_redirection_url_0',
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_setup_signup_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_setup_signup_conditional_redirection_url_require_0">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                       <?php
                                    }
                                    else{
                                        $ckey = 0;
                                     
                                        foreach($arm_redirection_setup_signup_conditional_redirect as $arm_setup_signup_conditional){
                                            if(is_array($arm_setup_signup_conditional)){
                                            $plan_id = (isset($arm_setup_signup_conditional['plan_id']) && !empty($arm_setup_signup_conditional['plan_id'])) ? $arm_setup_signup_conditional['plan_id'] : 0;

                                            $url = (isset($arm_setup_signup_conditional['url']) && !empty($arm_setup_signup_conditional['url'])) ? $arm_setup_signup_conditional['url'] : ARM_HOME_URL;
                                            ?>
                                    <li id="arm_setup_signup_conditional_redirection_box<?php echo $ckey; ?>" class="arm_setup_signup_conditional_redirection_box_div">
                                 
                                        <a class="arm_remove_setup_signup_redirection_condition" href="javascript:void(0)" data-index="<?php echo $ckey; ?>"><img src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png' onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';" /></a>
                                        <table>
                                        <tr>
                                            <td width="160px"><?php _e('If user selected plan is', 'ARMember'); ?></td>
                                            <td>
                                                    <input type='hidden' id='arm_conditional_redirect_setup_plan_<?php echo $ckey; ?>' name="arm_redirection_settings[setup_signup][conditional_redirect][<?php echo $ckey; ?>][plan_id]" value="<?php echo $plan_id; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt class="arm_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirect_setup_plan_<?php echo $ckey; ?>">
                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value="0"><?php _e('Select Plan', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Any Plan', 'ARMember'); ?>" data-value="-3"><?php _e('Any Plan', 'ARMember'); ?></li>

                                                                    <?php
                                                               
                                                               
                                                               if (!empty($all_plans)) {
                                                                   foreach ($all_plans as $p) {
                                                                       $p_id = $p['arm_subscription_plan_id'];
                                                                       ?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']); ?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                                       }
                                                                   }
                                                                   ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arm_rsc_error arm_conditional_redirect_setup_plan_require_<?php echo $ckey; ?>">
                                                        <?php _e('Please select plan.', 'ARMember'); ?>
                                                    </span> 
                                            </td>
                                          
                                        </tr>
                                        <tr>
                                            <td><?php _e('Then Redirect To', 'ARMember'); ?></td>
                                            <td colspan="3" width="540px">
                                                <?php
                                                $arm_global_settings->arm_wp_dropdown_pages(
                                                        array(
                                                                'selected' => $url,
                                                                'name' => 'arm_redirection_settings[setup_signup][conditional_redirect]['.$ckey.'][url]',
                                                                'id' => 'arm_setup_signup_conditional_redirection_url_'.$ckey,
                                                                'show_option_none' => __('Select Page', 'ARMember'),
                                                                'option_none_value' => 0,
                                                                'class' => 'arm_member_form_input arm_setup_signup_conditional_redirection_url',
                                                                'required' => true,
                                                                'required_msg' => __('Please select redirection page.', 'ARMember'),
                                                        )
                                                );
                                                ?>
                                                <span class="arm_rsc_error arm_setup_signup_conditional_redirection_url_require_<?php echo $ckey; ?>">
                                                    <?php _e('Please select a page.', 'ARMember'); ?>
                                                </span>  
                                            </td>
                                        </tr>
                                        </table>
                                    </li>
                                                                               <?php 
                                            $ckey++;
                                            }
                                        }
                                    }
                                    ?>
                                    </ul>
                                    <div class="arm_signup_conditional_redirection_link">
                                        <input id="arm_total_setup_signup_conditional_redirection_condition" name="arm_total_setup_signup_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <input id="arm_order_setup_signup_conditional_redirection_condition" name="arm_order_setup_signup_conditional_redirection_condition" value="<?php echo $ckey; ?>" type="hidden">
                                        <a id="arm_setup_signup_conditional_redirection_add_new_condition" class="arm_setup_signup_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php _e('Add New Condition', 'ARMember'); ?></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                    </table>   
                     <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Redirection upon Add/Change Membership', 'ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_change][type]" value="page" class="arm_redirection_settings_setup_change_radio arm_iradio" <?php checked($arm_redirection_setup_change_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_change][type]" value="url" class="arm_redirection_settings_setup_change_radio arm_iradio" <?php checked($arm_redirection_setup_change_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        
                        
                         <tr id="arm_redirection_settings_setup_change_page" class="arm_redirection_settings_setup_change <?php if($arm_redirection_setup_change_type != 'page') { echo 'hidden_section'; } ?>"">
                        
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_setup_change_page_id,
                                                    'name' => 'arm_redirection_settings[setup_change][page_id]',
                                                    'id' => 'arm_form_action_setup_change_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_setup_change_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                                    ?>
                                    <span class="arm_form_action_setup_change_page_require">
                                        <?php _e('Please Select Page.', 'ARMember');?>        
                                    </span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr id="arm_redirection_settings_setup_change_url" class="arm_redirection_settings_setup_change <?php if($arm_redirection_setup_change_type != 'url') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[setup_change][url]" value="<?php echo $arm_redirection_setup_change_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_setup_change_redirection_url" id="arm_setup_change_redirection_url"><br/>
                                    <span class="arm_form_action_setup_change_url_require"><?php _e('Please enter URL.', 'ARMember');?></span>
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
								</div>	  
                            </td>
                        </tr>
                    </table>   
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Redirection upon Membership Renewal', 'ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_renew][type]" value="page" class="arm_redirection_settings_setup_renew_radio arm_iradio" <?php checked($arm_redirection_setup_renew_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_renew][type]" value="url" class="arm_redirection_settings_setup_renew_radio arm_iradio" <?php checked($arm_redirection_setup_renew_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                        
                        
                         <tr id="arm_redirection_settings_setup_renew_page" class="arm_redirection_settings_setup_renew <?php if($arm_redirection_setup_renew_type != 'page') { echo 'hidden_section'; } ?>"">
                        
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_setup_renew_page_id,
                                                    'name' => 'arm_redirection_settings[setup_renew][page_id]',
                                                    'id' => 'arm_form_action_setup_renew_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_setup_renew_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                             
                            
                                    ?>
                                    <span class="arm_form_action_setup_renew_page_require">
                                        <?php _e('Please Select Page.', 'ARMember');?>        
                                    </span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr id="arm_redirection_settings_setup_renew_url" class="arm_redirection_settings_setup_renew <?php if($arm_redirection_setup_renew_type != 'url') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[setup_renew][url]" value="<?php echo $arm_redirection_setup_renew_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_setup_renew_redirection_url" id="arm_setup_renew_redirection_url"><br/>
                                    <span class="arm_setup_renew_redirection_url_require"><?php _e('Please enter URL.', 'ARMember');?></span>
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
								</div>	  
                            </td>
                        </tr>
                    </table>   

                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Default Redirect URL','ARMember');?></th>
                            <td class="arm-form-table-content">                     
                                <input type="text" name="arm_redirection_settings[setup][default]" value="<?php echo $arm_default_setup_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_setup_signup_default_redirection" id="arm_setup_signup_default_redirection">
                                <span class="arm_redirection_plan_signup_url_selection_require">
                                    <?php _e('Please enter URL.', 'ARMember');?>                                      
                                </span>
                                <br/>
                                <span class="arm_info_text"><?php _e('Default Redirect to above url if any of above conditions do not match.', 'ARMember'); ?></span>
                            </td>
                        </tr>
                    </table>   
                    <?php  if($arm_pay_per_post_feature->isPayPerPostFeature){ ?>
                    <div class="arm_solid_divider"></div> 
                    <div class="page_sub_title"><?php _e('After Paid Post obtaining Redirection Rules','ARMember'); ?></div>
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Redirection after Paid Post Purchase', 'ARMember');?></th>
                            <td class="arm-form-table-content"> 
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_paid_post][type]" value="0" class="arm_redirection_settings_setup_paid_post_radio arm_iradio" <?php checked($arm_redirection_setup_paid_post_type, '0');?> >
                                       <?php _e('Same page (Paid Post URL)','ARMember');?>
                                </label>                    
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[setup_paid_post][type]" value="1" class="arm_redirection_settings_setup_paid_post_radio arm_iradio" <?php checked($arm_redirection_setup_paid_post_type, '1');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                
                                
                            </td>
                        </tr>
                        
                        
                         <tr id="arm_redirection_settings_setup_paid_post_1" class="arm_redirection_settings_setup_paid_post <?php if($arm_redirection_setup_paid_post_type != '1') { echo 'hidden_section'; } ?>">
                        
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_setup_paid_post_page_id,
                                                    'name' => 'arm_redirection_settings[setup_paid_post][page_id]',
                                                    'id' => 'arm_form_action_setup_paid_post_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_setup_paid_post_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                             
                            
                                    ?>
                                    <div class="armclear"></div>
                                    <span class="arm_form_action_setup_paid_post_page_require">
                                        <?php _e('Please Select Page.', 'ARMember');?>        
                                    </span>
                                </div>
                            </td>
                        </tr>
                        
                    </table> 
                    <?php }?>
                    <div class="arm_solid_divider"></div> 
                    
                    <?php  if($arm_social_feature->isSocialLoginFeature){ ?>
                    <div class="page_sub_title"><?php _e('Social Connect Redirection( For One Click Sign up )','ARMember'); ?></div>
                    <table class="form-table">
                        <tr>
                            <th class="arm-form-table-label"><?php _e('Default Redirect To','ARMember');?></th>
                            <td class="arm-form-table-content">						
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[social][type]" value="page" class="arm_redirection_settings_social_radio arm_iradio" <?php checked($arm_redirection_social_type, 'page');?>>
                                        <?php _e('Specific Page','ARMember');?>
                                </label>
                                <label style="min-width: 100px;">
                                        <input type="radio" name="arm_redirection_settings[social][type]" value="url" class="arm_redirection_settings_social_radio arm_iradio" <?php checked($arm_redirection_social_type, 'url');?> >
                                       <?php _e('Specific URL','ARMember');?>
                                </label>
                                
                            </td>
                        </tr>
                         <tr id="arm_redirection_social_settings_page" class="arm_redirection_settings_social <?php if($arm_redirection_social_type != 'page') { echo 'hidden_section'; } ?>"">
                        
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Select Page', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <?php
                                    

                                    $arm_global_settings->arm_wp_dropdown_pages(
                                            array(
                                                    'selected' => $arm_redirection_social_page_id,
                                                    'name' => 'arm_redirection_settings[social][page_id]',
                                                    'id' => 'arm_form_action_social_page',
                                                    'show_option_none' => __('Select Page', 'ARMember'),
                                                    'option_none_value' => '',
                                                    'class' => 'form_action_social_page',
                                                    'required' => true,
                                                    'required_msg' => __('Please select redirection page.', 'ARMember'),
                                            )
                                    );
                             
                            
                                    ?>
                                    <span class="arm_redirection_social_page_selection">
                                        <?php _e('Please Select Page.', 'ARMember'); ?>
                                    </span>      
                                </div>
                            </td>
                        </tr>
                        
                        <tr id="arm_redirection_social_settings_url" class="arm_redirection_settings_social <?php if($arm_redirection_social_type != 'url') { echo 'hidden_section'; } ?>">
                            <th></th>
                            <td>
                                <div class="arm_default_redirection_lbl">
                                    <span class="arm_info_text"><?php _e('Add URL', 'ARMember'); ?></span>
                                </div>
                                <div class="arm_default_redirection_txt">
                                    <input type="text" name="arm_redirection_settings[social][url]" value="<?php echo $arm_redirection_social_url; ?>" data-msg-required="<?php _e('Please enter URL.', 'ARMember');?>" class="arm_member_form_input arm_social_redirection_url"><br/>
                                    <span class="arm_redirection_social_url_selection"><?php _e('Please enter URL.', 'ARMember'); ?></span>
                                    <span class="arm_info_text"><?php _e('Enter URL with http:// or https://.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERNAME}</strong> to add current user\'s usrename in url.', 'ARMember'); ?></span><br/>
                                    <span class="arm_info_text"><?php _e('Use <strong>{ARMCURRENTUSERID}</strong> to add current user\'s id in url.', 'ARMember'); ?></span>
							    </div>  
                            </td>
                        </tr>
                    </table>               
                    <div class="arm_solid_divider"></div> 
                    <?php } ?>
                     
                    <div class="page_sub_title" id="arm_global_default_access_rules">
                        <?php _e('Redirection Rules upon Accessing Restricted Post/Page', 'ARMember'); ?>
                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Please set default redirection rules for users when they try to access restricetd content.", 'ARMember'); ?>"></i>
                    </div>
                    <div> <!-- class="arm_sub_section" -->
                        <table class="form-table">
                                <tr class="form-field">
                                    <th>
                                    <?php _e('For non logged in users', 'ARMember'); ?> <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Set page for redirection in case when user is not loggedin & trying to access restricted page.", 'ARMember'); ?>"></i>
                                    </th>
                                    <td style="vertical-align: top;padding-top: 15px;">
                                            <input type="radio" name="arm_redirection_settings[default_access_rules][non_logged_in][type]" id="arm_redirect_restricted_home" value="home" <?php checked($arm_non_logged_in_type, 'home');?> class="arm_iradio arm_redirect_restricted_page_input"><label for="arm_redirect_restricted_home" style="min-width: 140px;"><?php _e('Home Page', 'ARMember');?></label>
                                            <input type="radio" name="arm_redirection_settings[default_access_rules][non_logged_in][type]" id="arm_redirect_restricted_specific" value="specific" <?php checked($arm_non_logged_in_type, 'specific');?> class="arm_iradio arm_redirect_restricted_page_input"><label for="arm_redirect_restricted_specific"><?php _e('Specific Page', 'ARMember');?></label>
                                            <div class="arm_redirection_access_rules_specific" style="<?php echo ($arm_non_logged_in_type == 'specific') ? '' : 'display:none';?>">
                                                    <?php 
                                                    $arm_global_settings->arm_wp_dropdown_pages(
                                                            array(
                                                                    'selected'              => (isset($arm_non_logged_in_redirect_to) ? $arm_non_logged_in_redirect_to : 0),
                                                                    'name'                  => 'arm_redirection_settings[default_access_rules][non_logged_in][redirect_to]',
                                                                    'id'                    => 'redirect_url',
                                                                    'show_option_none'      => 'Select Page',
                                                                    'option_none_value'     => '0',
                                                            )
                                                    );
                                                    ?>
                                            <span class="arm_redirection_access_rules_non_loggedin_specific_error">
                                                <?php _e('The selected page is restricted item from content access rule. Please select another page.', 'ARMember'); ?>
                                            </span>
                                            <span class="arm_redirection_access_rules_non_loggedin_specific_blank_error">
                                                <?php _e('Please Select Page.', 'ARMember'); ?>
                                            </span>
                                            </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e('For logged in users', 'ARMember'); ?> <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Set page for redirection in case when user is loggedin & trying to access restricted page.", 'ARMember'); ?>"></i>
                                    </th>
                                    <td style="vertical-align: top; padding-top: 15px;">
                                        <input type="radio" name="arm_redirection_settings[default_access_rules][logged_in][type]" id="arm_redirect_logged_in_restricted_home" value="home" <?php checked($arm_logged_in_type, 'home'); ?> class="arm_iradio arm_redirect_logged_in_restricted_page_input"><label for="arm_redirect_logged_in_restricted_home" style="min-width: 140px;"><?php _e('Home Page', 'ARMember'); ?></label>
                                        <input type="radio" name="arm_redirection_settings[default_access_rules][logged_in][type]" id="arm_redirect_logged_in_restricted_specific" value="specific" <?php checked($arm_logged_in_type, 'specific'); ?> class="arm_iradio arm_redirect_logged_in_restricted_page_input"><label for="arm_redirect_logged_in_restricted_specific"><?php _e('Specific Page', 'ARMember'); ?></label>
                                        <div class="arm_redirection_access_rules_logged_in_specific" style="<?php echo (@$arm_logged_in_type == 'specific') ? '' : 'display:none'; ?>">
                                            <?php
                                            $arm_global_settings->arm_wp_dropdown_pages(
                                                    array(
                                                        'selected' => (isset($arm_logged_in_redirect_to) ? $arm_logged_in_redirect_to : 0),
                                                        'name' => 'arm_redirection_settings[default_access_rules][logged_in][redirect_to]',
                                                        'id' => 'redirect_url_logged_in',
                                                        'show_option_none' => 'Select Page',
                                                        'option_none_value' => '0',
                                                    )
                                            );
                                            ?>
                                            <span class="arm_redirection_access_rules_loggedin_specific_error">
                                                <?php _e('The selected page is restricted item from content access rule. Please select another page.', 'ARMember'); ?>
                                            </span>
                                            <span class="arm_redirection_access_rules_loggedin_specific_blank_error">
                                                <?php _e('Please Select Page.', 'ARMember'); ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
            <?php /* ?>
            <tr>
                <th>
                    <?php _e('For pending users', 'ARMember'); ?><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e('Selected page from here will be ONLY accessible in case when any pending user is trying to access the site.', 'ARMember'); ?>"></i>
                </th>
                <td style="vertical-align: top; padding-top: 15px;">
                    <input type="radio" name="arm_redirection_settings[default_access_rules][pending][type]" id="arm_redirect_pending_restricted_home" value="home" <?php checked($arm_pending_type, 'home'); ?> class="arm_iradio arm_redirect_pending_restricted_page_input"><label for="arm_redirect_pending_restricted_home" style="min-width: 140px;"><?php _e('Home Page', 'ARMember'); ?></label>
                    <input type="radio" name="arm_redirection_settings[default_access_rules][pending][type]" id="arm_redirect_pending_restricted_specific" value="specific" <?php checked($arm_pending_type, 'specific'); ?> class="arm_iradio arm_redirect_pending_restricted_page_input"><label for="arm_redirect_pending_restricted_specific"><?php _e('Specific Page', 'ARMember'); ?></label>
                    <div class="arm_redirection_access_rules_pending_specific" style="<?php echo (@$arm_pending_type == 'specific') ? '' : 'display:none'; ?>">
                        <?php
                        $arm_global_settings->arm_wp_dropdown_pages(
                                array(
                                    'selected' => (isset($arm_pending_redirect_to) ? $arm_pending_redirect_to : 0),
                                    'name' => 'arm_redirection_settings[default_access_rules][pending][redirect_to]',
                                    'id' => 'redirect_url_pending',
                                    'show_option_none' => 'Select Page',
                                    'option_none_value' => '0',
                                )
                        );
                        ?>
                        <span class="arm_redirection_access_rules_pending_specific_error">
                            <?php _e('The selected page is restricted item from content access rule. Please select another page.', 'ARMember'); ?>
                        </span>
                        <span class="arm_redirection_access_rules_pending_specific_blank_error">
                            <?php _e('Please Select Page.', 'ARMember'); ?>
                        </span>
                    </div>
                </td>
            </tr><?php */ ?>
            <?php if($arm_drip_rules->isDripFeature): ?>
            <tr>
                <th>
                    <?php _e('For Restricted drip content', 'ARMember'); ?><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e('Set page for redirection in case when user is to access restricted drip page.', 'ARMember'); ?>"></i>
                </th>
                <td style="vertical-align: top; padding-top: 15px;">
                    <input type="radio" name="arm_redirection_settings[default_access_rules][drip][type]" id="arm_redirect_drip_restricted_home" value="home" <?php checked($arm_drip_type, 'home'); ?> class="arm_iradio arm_redirect_drip_page_input"><label for="arm_redirect_drip_restricted_home" style="min-width: 140px;"><?php _e('Home Page', 'ARMember'); ?></label>
                    <input type="radio" name="arm_redirection_settings[default_access_rules][drip][type]" id="arm_redirect_drip_restricted_specific" value="specific" <?php checked($arm_drip_type, 'specific'); ?> class="arm_iradio arm_redirect_drip_page_input"><label for="arm_redirect_drip_restricted_specific"><?php _e('Specific Page', 'ARMember'); ?></label>
                    <div class="arm_redirection_access_rules_drip_specific" style="<?php echo (@$arm_drip_type == 'specific') ? '' : 'display:none'; ?>">
                        <?php
                        $arm_global_settings->arm_wp_dropdown_pages(
                                array(
                                    'selected' => (isset($arm_drip_redirect_to) ? $arm_drip_redirect_to : 0),
                                    'name' => 'arm_redirection_settings[default_access_rules][drip][redirect_to]',
                                    'id' => 'redirect_url_drip',
                                    'show_option_none' => 'Select Page',
                                    'option_none_value' => '0',
                                )
                        );
                        ?>
                        <span class="arm_redirection_access_rules_drip_specific_error">
                            <?php _e('The selected page is restricted item from content access rule. Please select another page.', 'ARMember'); ?>
                        </span>
                        <span class="arm_redirection_access_rules_drip_specific_blank_error">
                            <?php _e('Please Select Page.', 'ARMember'); ?>
                        </span>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

           
               <div class="arm_submit_btn_container arm_redirection_submit_btn">
				<button class="arm_save_btn arm_redirection_settings_btn" type="submit" id="arm_redirection_settings_btn" name="arm_redirection_settings_btn"><?php _e('Save', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
                    </div>
                    <?php wp_nonce_field( 'arm_wp_nonce' );?>
                </form>
                    
                
	</div>
</div>

<div id="arm_all_pages" style="display:none;visibility: hidden;opacity: 0;">
<?php
$arm_page_args = array(
        'depth' => 0, 'child_of' => 0, 'selected' => 0, 'echo' => 1,
        'name' => 'arm_redirection_settings[login][page_id]',
        'id' => 'arm_login_redirection_page',
        'show_option_none' => __('Select Page', 'ARMember'),
        'show_option_no_change' => '', 'option_none_value' => '',
        'class' => 'arm_login_redirection_page'
    );
$new_pages = $arm_global_settings->arm_get_wp_pages($arm_page_args, array('ID', 'post_title'));
echo json_encode($new_pages);
?>
</div>
    
<div id="arm_all_plans" style="display:none;visibility: hidden;opacity: 0;">
                                                        <?php echo json_encode($all_plans); ?>
                                                    </div>


<div id="arm_all_signup_forms" style="display:none;visibility: hidden;opacity: 0;">
                                                        <?php echo json_encode($arm_forms); ?>
                                                    </div>

<div id="arm_all_edit_profile_forms" style="display:none;visibility: hidden;opacity: 0;">
    <?php echo json_encode($arm_edit_profile_forms); ?>
</div>
<script>
    var IF_USER_HAVE = '<?php echo addslashes( __('If User Has', 'ARMember')); ?>';
    var PLAN_AND = '<?php echo addslashes( __('&', 'ARMember')); ?>';
    var SELECT_CONDITION = '<?php echo addslashes( __('Any Condition', 'ARMember')); ?>';
    var SELECT_FORM = '<?php echo addslashes( __('Select Form', 'ARMember')); ?>';
    var ALL_FORM = '<?php echo addslashes( __('All Forms', 'ARMember')); ?>';
    var IN_TRIAL = '<?php echo addslashes( __('In Trial', 'ARMember')); ?>';
    var FAILED_PAYMENT = '<?php echo addslashes( __('Failed Payment(Suspended)', 'ARMember')); ?>';
    var GRACE = '<?php echo addslashes( __('In Grace Period', 'ARMember')); ?>';
    var BEFORE_EXPIRE = '<?php echo addslashes( __('Before Expiration Of', 'ARMember')); ?>';
    var PENDING = '<?php echo addslashes( __('Pending', 'ARMember')); ?>';
    var THEN_REDIRECT_TO = '<?php echo addslashes( __('Then Redirect To', 'ARMember')); ?>';
    var  FIRST_TIME = '<?php echo addslashes( __('First Time Logged In', 'ARMember')); ?>';
    var   HOME_URL = '<?php echo ARM_HOME_URL; ?>';
    var __SELECT_PLAN = '<?php echo addslashes( __('Select Plan', 'ARMember')); ?>';
    var __No_PLAN = '<?php echo addslashes( __('No Plan', 'ARMember')); ?>';
    var __ANY_PLAN = '<?php echo addslashes( __('Any Plan', 'ARMember')); ?>';
    var __SELECT_PAGE = '<?php echo addslashes( __('Select Page', 'ARMember')); ?>';
    var REMESSAGE = '<?php echo addslashes( __('You can not remove all Conditions', 'ARMember')); ?>';
    var DAYS = '<?php echo addslashes( __(' Days', 'ARMember')); ?>';
    var ARM_RSC_PLAN_ID = '<?php echo addslashes( __('Please select plan.', 'ARMember')); ?>';
    var ARM_RSC_REDIRECT = '<?php echo addslashes( __('Please select condition.', 'ARMember')); ?>';
    var ARM_RSC_URL = '<?php echo addslashes( __('Please enter URL.', 'ARMember')); ?>';
    var ARM_RSC_PAGE = '<?php echo addslashes( __('Please select a page.', 'ARMember')); ?>';
    var IF_FORM_IS = '<?php echo addslashes( __('If SignUp Form is', 'ARMember')); ?>';
    var IF_EDIT_PROFILE_FORM_IS = '<?php echo addslashes( __('If Profile Form is', 'ARMember')); ?>';
    var IF_PLAN_IS = '<?php echo addslashes( __('If user selected plan is', 'ARMember')); ?>';
    var ARM_RSC_FORM_ID = '<?php echo addslashes( __('Please select signup form.', 'ARMember')); ?>';
    var ARM_EDIT_PROFILE_FORM_ID = '<?php echo addslashes( __('Please select Edit Profile form.', 'ARMember')); ?>';
    var ARM_MEMBERSHIP_PLAN = '<?php echo addslashes( __('Membership Plan', 'ARMember')); ?>';
    var ARM_ACTION = '<?php echo addslashes( __('Action', 'ARMember')); ?>';
    var ARM_SET_REDIRECTION_PRIORITY = '<?php echo addslashes( __('Set Redirection Priority', 'ARMember')); ?>';
    
    var ARM_RR_CLOSE_IMG = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';
    var ARM_RR_CLOSE_IMG_HOVER = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';
    var CHOOSEPLAN =  '<?php echo addslashes( __('Choose Plan', 'ARMember')); ?>';
</script>