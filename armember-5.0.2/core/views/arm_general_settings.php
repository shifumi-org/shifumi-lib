<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_slugs, $arm_social_feature, $arm_buddypress_feature, $arm_invoice_tax_feature,$arm_pay_per_post_feature, $arm_api_service_feature;
$active = 'arm_general_settings_tab_active';
$arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);

$g_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "general_settings";
if(!$arm_email_settings->isOptInsFeature && $g_action == 'opt_ins_options'){
    $g_action = 'general_settings';
}
if(!$arm_social_feature->isSocialLoginFeature && $g_action == 'social_options'){
    $g_action = 'general_settings';
}

$additional_tabs = array();
$additional_tabs = apply_filters("arm_general_settings_tabs_outside", $additional_tabs);
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
        <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
    <div class="content_wrapper arm_global_settings_content" id="content_wrapper">
        <div class="page_title"><?php _e('General Settings', 'ARMember'); ?></div>
        <div class="armclear"></div>
        <div class="armember_general_settings_wrapper">
            <div class="arm_general_settings_tab_wrapper">
                <a class="arm_general_settings_tab <?php echo ($g_action == 'general_settings') ? $active : ""; ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings); ?>"><?php _e('General Options', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'payment_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options'); ?>"><?php _e('Payment Gateways', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'page_setup' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup'); ?>"><?php _e('Page Setup', 'ARMember'); ?></a>
                <?php if($arm_email_settings->isOptInsFeature): ?>
                    <a class="arm_general_settings_tab <?php echo ($g_action == 'opt_ins_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=opt_ins_options'); ?>"><?php _e('Opt-ins Configuration', 'ARMember'); ?></a>
                <?php endif; ?>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'access_restriction' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=access_restriction'); ?>"><?php _e('Default Restriction Rules', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'block_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=block_options'); ?>"><?php _e('Security Options', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'import_export' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=import_export'); ?>"><?php _e('Import / Export', 'ARMember'); ?></a>
                
                <?php if($arm_social_feature->isSocialLoginFeature): ?>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'social_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=social_options'); ?>"><?php _e('Social Connect', 'ARMember'); ?></a>
                <?php endif; ?>
                <?php if($arm_buddypress_feature->isBuddypressFeature): 
                    $check_buddyp_buddyb = $arm_buddypress_feature->arm_check_buddypress_buddyboss();
                ?>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'buddypress_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action='.$check_buddyp_buddyb['arm_action'].''); ?>"><?php _e($check_buddyp_buddyb['arm_title'], 'ARMember'); ?></a>
                <?php endif; ?>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'redirection_options' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options'); ?>"><?php _e('Redirection Rules', 'ARMember'); ?></a>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'common_messages' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=common_messages'); ?>"><?php _e('Common Messages', 'ARMember'); ?></a>
                <?php
                    if($arm_invoice_tax_feature == 1) {
                ?>
                    <a class="arm_general_settings_tab <?php echo ($g_action == 'invoice_setting' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=invoice_setting'); ?>"><?php _e('Invoice Template', 'ARMember'); ?></a>
                    <div class="armclear"></div>
                <?php

                } if($arm_pay_per_post_feature->isPayPerPostFeature):?>
                <a class="arm_general_settings_tab <?php echo ($g_action == 'pay_per_post_setting' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=pay_per_post_setting'); ?>"><?php _e('Paid Post Settings', 'ARMember'); ?></a>
                <div class="armclear"></div>
                <?php endif; 
                    if($arm_api_service_feature->isAPIServiceFeature) {
                ?>
                    <a class="arm_general_settings_tab <?php echo ($g_action == 'api_service_feature' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=api_service_feature'); ?>"><?php _e('API Services Settings', 'ARMember'); ?></a>
                    <div class="armclear"></div>
                <?php } ?>

                <a class="arm_general_settings_tab <?php echo ($g_action == 'debug_logs' ? $active : ""); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=debug_logs'); ?>"><?php _e('Debug Log Settings', 'ARMember'); ?></a>
                
                <?php
                if (!empty($additional_tabs)) {
                    foreach ($additional_tabs as $tab_slug => $tab_info) { ?>
                        <a class="arm_general_settings_tab <?php echo ($g_action == $tab_slug ? $active : ''); ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action='.$tab_slug); ?>"><?php _e($tab_info['tab_title'], 'ARMember'); ?></a>
                    <?php } ?>
                    <div class="armclear"></div>
                <?php }
                ?>
            </div>
            <div class="arm_settings_container">
                <?php
                    /* if you add any new tab than reset the min height of the box other wise last menu not display in page setup page. */
                    $arm_setting_tooltip = '';
                    if (empty($g_action)) {
                        $g_action = "general_settings";
                        $arm_setting_title = __('General Options', 'ARMember');
                        $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_global_settings.php';
                    }
                    switch ($g_action)
                    {
                            case 'general_settings':
                                $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_global_settings.php';
                                $arm_setting_title = __('General Options', 'ARMember');
                                break;
                            case 'payment_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_payment_gateways.php';
                                    $arm_setting_title = __('Payment Gateways', 'ARMember');
                                    break;
                            case 'page_setup':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_page_setup.php';
                                    $arm_setting_title = __('Page Setup', 'ARMember');
                                    break;
                            case 'opt_ins_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_opt_ins_settings.php';
                                    $arm_setting_title = __('Opt-ins Configuration', 'ARMember');
                                    break;
                            case 'block_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_block_settings.php';
                                    $arm_setting_title = __('Security Options', 'ARMember');
                                    break;
                            case 'import_export':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_import_export.php';
                                    $arm_setting_title = __('Import / Export', 'ARMember');
                                    break;
                            case 'debug_logs':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_debug_logs.php';
                                    $arm_setting_title = __('Debug Log Settings', 'ARMember');
                                    break;
                            case 'social_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_social_settings.php';
                                    $arm_setting_title = __('Social Connect', 'ARMember');
                                    break;
                            case 'buddypress_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_buddypress_settings.php';
                                    $arm_setting_title = __('BuddyPress', 'ARMember');
                                    break;
                            case 'buddyboss_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_buddypress_settings.php';
                                    $arm_setting_title = __('BuddyBoss', 'ARMember');
                                    break;
                            case 'redirection_options':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_redirection_settings.php';
                                    $arm_setting_title = __('Page/Post Redirection Rules', 'ARMember');
                                    break;
                            case 'common_messages':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_common_messages_settings.php';
                                    $arm_setting_title = __('Common Messages', 'ARMember');
                                    break;
                            case 'invoice_setting':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_invoice_settings.php';
                                    $arm_setting_title = __('Invoice Template', 'ARMember');
                                    $arm_setting_tooltip = '<i class="arm_helptip_icon armfa armfa-question-circle" title="'.__("Here you can set template of invoice that will be downloaded by users", 'ARMember').'"></i>';
                                    break;
                            case 'access_restriction':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_access_restriction_settings.php';
                                    $arm_setting_title = __('General Restriction Options', 'ARMember');
                                    break;
                            case 'pay_per_post_setting':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_settings.php';
                                    $arm_setting_title = __('Paid Post Settings', 'ARMember');
                                    break;
                            case 'api_service_feature':
                                    $file_path = MEMBERSHIP_VIEWS_DIR . '/arm_api_service_feature.php';
                                    $arm_setting_title = __('API Services Settings', 'ARMember');
                                    break;
                            default:
			    	$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_global_settings.php';
                                $arm_setting_title = __('General Options', 'ARMember');
                                if (!empty($additional_tabs)) {
                                    foreach ($additional_tabs as $tab_slug => $tab_info) {
                                        if ($g_action == $tab_slug) {
                                            $file_path = $tab_info['file_path'];
                                            $arm_setting_title = $tab_info['tab_title'];
                                        } ?>
                                    <?php }
                                }
				break;
                    }
                    if (file_exists($file_path)) {
                            ?>
                            <div class="arm_settings_title_wrapper">
                                <div class="arm_setting_title"><?php echo $arm_setting_title." ".$arm_setting_tooltip; ?></div>
                                <?php if($g_action == 'invoice_setting'){ ?>
                                <div class="arm_invoice_reset_btn_div" style="display: inline-block;float: none; width: 90%;">
                                    <button id="arm_invoice_reset_btn" class="arm_save_btn" name="arm_invoice_reset_btn" type="button"><?php _e('Reset To Default', 'ARMember') ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_reset_ionvoice_loader_img" class="arm_submit_btn_loader arm_margin_right_20" style="top:15px;display:none;float:right;" width="24" height="24" />
                                </div>
                                <?php } ?>
                            </div>
                            <?php
                            include($file_path);
                    }
                ?>
            </div>
        </div>
        <div class="armclear"></div>
    </div>
</div>
<?php
    echo $ARMember->arm_get_need_help_html_content('arm_'.$g_action);
?>