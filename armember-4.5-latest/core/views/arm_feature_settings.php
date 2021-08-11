<?php 
global $wpdb, $ARMember, $arm_slugs, $arm_social_feature,$myplugarr, $arm_admin_mycred_feature;
$ARMember->arm_session_start();
$user_private_content = get_option('arm_is_user_private_content_feature');
$social_feature = get_option('arm_is_social_feature');
$social_login_feature = get_option('arm_is_social_login_feature');
$drip_content_feature = get_option('arm_is_drip_content_feature');
$opt_ins_feature = get_option('arm_is_opt_ins_feature');
$coupon_feature = get_option('arm_is_coupon_feature');
$buddypress_feature = get_option('arm_is_buddypress_feature');
$invoice_tax_feature = get_option('arm_is_invoice_tax_feature');
$multiple_membership_feature = get_option('arm_is_multiple_membership_feature');
$arm_is_mycred_active = get_option('arm_is_mycred_feature');
$woocommerce_feature = get_option('arm_is_woocommerce_feature');
$arm_pay_per_post = get_option('arm_is_pay_per_post_feature');

$featureActiveIcon = MEMBERSHIP_IMAGES_URL . '/feature_active_icon.png';
if (is_rtl()) {
	$featureActiveIcon = MEMBERSHIP_IMAGES_URL . '/feature_active_icon_rtl.png';
}
?>
<?php
$hostname = $_SERVER["SERVER_NAME"];
global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
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
	.arperrmessage{color:red;}
    #arfactlicenseform {
        border-radius:0px;
        text-align:center;
        width:570px;
        height:350px;
        left:35%;
        border:none;
		background:#ffffff !important;
		padding:30px 20px;
    }
    #wpcontent{
        background: #EEF2F8;
    }
	#arfactlicenseform .form-table th{ text-align:right; }
	#arfactlicenseform .form-table td{ text-align:left; }
	#license_error { color:red;}
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
<div class="wrap arm_page arm_feature_settings_main_wrapper">
    <div class="content_wrapper arm_feature_settings_content" id="content_wrapper">
        <div class="page_title"><?php _e('Additional Membership Modules', 'ARMember'); ?></div>
        <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
        <div class="armclear"></div>
        <div class="arm_feature_settings_wrapper">            
            <div class="arm_feature_settings_container">
                <div class="arm_feature_list social_enable <?php echo ($social_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Social Feature','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("With this feature, enable social activities like Member Directory/Public Profile, Membership Cards and User Badges etc.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($social_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($social_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } ?>
                        
						<div class="arm_feature_button_deactivate_wrapper <?php echo ($social_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="social"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->profiles_directories);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/brief-of-social-features/"><?php _e('More Info', 'ARMember'); ?></a>
				</div>
				<div class="arm_feature_list opt_ins_enable <?php echo ($opt_ins_feature == 1) ? 'active':'';?>">
					<div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Opt-ins','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("build you subscription list with external list builder like Aweber, Mailchimp while user registration.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($opt_ins_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($opt_ins_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="opt_ins"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } ?>
						<div class="arm_feature_button_deactivate_wrapper <?php echo ($opt_ins_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="opt_ins"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=opt_ins_options');?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/armember-opt-ins-provide-ease-of-email-marketing/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <div class="arm_feature_list drip_content_enable <?php echo ($drip_content_feature == 1) ? 'active':'';?>">
					<div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Drip Content','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("Publish your site content based on different time intervals by enabling this feature.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($drip_content_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($drip_content_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="drip_content"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } ?>
						<div class="arm_feature_button_deactivate_wrapper <?php echo ($drip_content_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="drip_content"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->drip_rules);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/enable-drip-content-for-your-site/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
				<div class="arm_feature_list social_login_enable <?php echo ($social_login_feature == 1) ? 'active':'';?>">
					<div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php _e('Social Connect','ARMember'); ?></div>
						<div class="arm_feature_text"><?php _e("Allow users to sign up / login with their social accounts by enabling this feature.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($social_login_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($social_login_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="social_login"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } ?>
						<div class="arm_feature_button_deactivate_wrapper <?php echo ($social_login_feature == 1) ? '':'hidden_section';?>">
							<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="social_login"><?php _e('Deactivate','ARMember'); ?></a>
							<a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=social_options');?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
					</div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/basic-information-for-social-login/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>

                <!-- rpt_log new module -->
                <div class="arm_feature_list pay_per_post_enable <?php echo ($arm_pay_per_post == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Pay Per Post','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("With this feature, you can sell post separately without creating plan(s).", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_pay_per_post == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="pay_per_post"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else {  ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_pay_per_post == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="pay_per_post"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($arm_pay_per_post == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch arm_no_config_feature_btn" data-feature_val="0" data-feature="pay_per_post"><?php _e('Deactivate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/pay-per-post/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <!-- rpt_log new module -->

                <div class="arm_feature_list coupon_enable <?php echo ($coupon_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Coupon','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Let users get benefit of discounts coupons while making payment with your site.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($coupon_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($coupon_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="coupon"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($coupon_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="coupon"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->coupon_management);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/how-to-do-coupon-management/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <div class="arm_feature_list invoice_tax_enable <?php echo ($invoice_tax_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Invoice and Tax','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Enable facility to send Invoice and apply Sales Tax on membership plans.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($invoice_tax_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($invoice_tax_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="invoice_tax"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($invoice_tax_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="invoice_tax"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/invoice-and-tax"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <!-- rpt_log new module -->
                <div class="arm_feature_list user_private_content_enable <?php echo ($user_private_content == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('User Private Content','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("With this feature, you can set different content for different user.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($user_private_content == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="user_private_content"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else {  ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($user_private_content == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="user_private_content"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($user_private_content == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="user_private_content"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->private_content);?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/user-private-content/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <!-- rpt_log new module -->
                <div class="arm_feature_list multiple_membership_enable <?php echo ($multiple_membership_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Multiple Membership/Plans','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Allow members to subscribe multiple plans simultaneously.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($multiple_membership_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="multiple_membership"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($multiple_membership_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="multiple_membership"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($multiple_membership_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_no_config_feature_btn arm_feature_settings_switch" data-feature_val="0" data-feature="multiple_membership"><?php _e('Deactivate','ARMember'); ?></a>
                    
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/single-vs-multiple-membership/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                <div class="arm_feature_list buddypress_enable <?php echo ($buddypress_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Buddypress/Buddyboss Integration','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Integrate BuddyPress or BuddyBoss with ARMember.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ($buddypress_feature == 1) ? 'hidden_section':'';?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
						</div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($buddypress_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="buddypress"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($buddypress_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="buddypress"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=buddypress_options');?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/buddypress-support/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                
                <div class="arm_feature_list woocommerce_enable <?php echo ($woocommerce_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('Woocommerce Integration','ARMember'); ?></div>
                        <div class="arm_feature_text" style=" min-height: 0;"><?php _e("Integrate Woocommerce with ARMember.", 'ARMember');?></div>
                        <div class="arm_feature_text" style=" font-size: 15px; font-weight: bold;  color: #969699; min-height: 0;"><?php _e('Min Required Woocommerce Ver.: 3.0.2', 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($woocommerce_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="woocommerce"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($woocommerce_feature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="woocommerce"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($woocommerce_feature == 1) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch arm_no_config_feature_btn" data-feature_val="0" data-feature="woocommerce"><?php _e('Deactivate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/woocommerce-support/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>


                <div class="arm_feature_list mycred_enable <?php echo ($arm_admin_mycred_feature->ismyCREDFeature == true) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php _e('myCRED Integration','ARMember'); ?></div>
                        <div class="arm_feature_text"><?php _e("Integrate myCRED adaptive points management system with ARMember.", 'ARMember');?></div>
                        <?php if ($setact != 1) { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_admin_mycred_feature->ismyCREDFeature == 1) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="social"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } else { ?>
                        <div class="arm_feature_button_activate_wrapper <?php echo ($arm_admin_mycred_feature->ismyCREDFeature == true) ? 'hidden_section':'';?>">
                            <a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch" data-feature_val="1" data-feature="mycred"><?php _e('Activate','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                        <?php } ?>
                        <div class="arm_feature_button_deactivate_wrapper <?php echo ($arm_admin_mycred_feature->ismyCREDFeature == true) ? '':'hidden_section';?>">
                            <a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch" data-feature_val="0" data-feature="mycred"><?php _e('Deactivate','ARMember'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=mycred-hooks');?>" class="arm_feature_configure_btn"><?php _e('Configure','ARMember'); ?></a>
                            <span class="arm_addon_loader">
                                <svg class="arm_circular" viewBox="0 0 60 60">
                                    <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/mycred-integration/"><?php _e('More Info', 'ARMember'); ?></a>
                </div>
                
                <?php echo do_action('arm_add_new_custom_add_on'); ?>
            </div>
            
            <div class="arm_feature_settings_container" style="margin-top:30px;">
				<?php
				global $arm_social_feature;
				global $arm_version;
				$addon_resp = "";
				$addon_resp = $arm_social_feature->addons_page();

				$plugins = get_plugins();
				$installed_plugins = array();
				foreach ($plugins as $key => $plugin) {
					$is_active = is_plugin_active($key);
					$installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
					$installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
					$installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

					$installed_plugins[] = $installed_plugin;
				}

		if ($addon_resp != "") {
		    $resp = explode("|^^|", $addon_resp);
		    if ($resp[0] == 1) {
			$myplugarr = array();
			$myplugarr = unserialize(base64_decode($resp[1]));
			$is_active = 0;

		
			if (is_array($myplugarr) && count($myplugarr) > 0) {
			    ?><div class="page_title"><?php _e('More ARMember Add-Ons', 'ARMember'); ?></div><?php
			    foreach ($myplugarr as $plug) {
				$is_active_plugin = is_plugin_active($plug['plugin_installer']);
                $is_config = ( isset( $plug['display_config'] ) && 'yes' == $plug['display_config'] ) ? true : false;
                $config_url = isset( $plug['config_args'] ) ? admin_url( $plug['config_args'] ) : '';
				?>
				<div class="arm_feature_list <?php echo $plug['short_name']; ?>_enable <?php echo ($is_active_plugin == 1) ? 'active' : ''; ?>">
				    <div class="arm_feature_icon" style="background-image:url(<?php echo $plug['icon']; ?>);"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
				    <div class="arm_feature_content">
					<div class="arm_feature_title"><?php echo $plug['full_name']; ?></div>
					<div class="arm_feature_text"><?php echo $plug['description']; ?></div>
                    
					<?php if ($setact != 1) { ?>
		    			<div class="arm_feature_button_activate_wrapper ">
		    			    <a href="javascript:void(0)" style="width:auto !important;padding:0 15px !important;" class="arm_feature_activate_btn arm_feature_activation_license" data-feature_val="1" data-feature="<?php echo $plug['short_name']; ?>"><?php _e('Activate License', 'ARMember'); ?></a>
		    			    <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
		    			</div>
					    <?php
					} else {
				?>
						<div class="arm_feature_button_activate_wrapper ">
						    <?php echo $arm_social_feature->CheckpluginStatus($installed_plugins, $plug['plugin_installer'], 'plugin', $plug['short_name'], $plug['plugin_type'], $plug['install_url'], $plug['armember_version'], $arm_version, $is_config, $config_url); ?>
						    <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" class="arm_addon_loader_img" width="24" height="24" />
						</div>
					<?php } ?>
				    </div>
                    <?php if(!empty($plug['armember_version']) && $plug['armember_version']>$arm_version) { ?>
                            <div class="arm_feature_text arm_feature_vesrion_compatiblity" style=" font-size: 15px; font-weight: bold; color: #ff0000;"><?php _e('Minimum Required ARMember Version:', 'ARMember'); echo " ".$plug['armember_version']?></div>
                    <?php } ?>
				    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="<?php echo $plug['detail_url']; ?>"><?php _e('More Info', 'ARMember'); ?></a>
				</div>
				<?php
			    }
			}
		    }
			else if(!empty($resp[1])) {
				echo $resp[1];
			}
		}
		?>
	    </div>
        </div>
        <?php wp_nonce_field( 'arm_wp_nonce' );?>
        <div class="armclear"></div>
    </div>
</div>

<?php
$addon_content = '<span class="arm_confirm_text">'.__("You need to have ARMember version 1.6 OR higher to install this addon.",'ARMember' ).'</span>';
		$addon_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$addon_content_popup_arg = array(
			'id' => 'addon_message',
			'class' => 'adddon_message',
                        'title' => __('Confirmation','ARMember'),
			'content' => $addon_content,
			'button_id' => 'addon_ok_btn',
			'button_onclick' => "addon_message();",
		);
		echo $arm_global_settings->arm_get_bpopup_html($addon_content_popup_arg); ?>


<div id="arfactnotcompatible" style="display:none; background:white; padding:15px; border-radius:3px; width:400px; height:100px;">
		
		<div class="arfactnotcompatiblemodalclose" style="float:right;text-align:right;cursor:pointer; position:absolute;right:10px; " onclick="javascript:return false;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/close-button.png'; ?>" align="absmiddle" /></div>
        
       <table class="form-table">
            <tr class="form-field">
                <th class="arm-form-table-label" style="font-size:16px;">You need to have ARMember version 1.6 OR higher to install this addon.</th>
            </tr>				
		</table>
</div>
<div id="arfactlicenseform" style="display:none;">
		
		<div class="arfnewactmodalclose" style="float:right;text-align:right;cursor:pointer;" onclick="javascript:return false;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/close-button.png'; ?>" align="absmiddle" /></div>
        <div class="newform_modal_title_container">
        	<div class="newform_modal_title">&nbsp;Product License</div>
    	</div>
       <table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Customer Name', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input type="text" name="li_customer_name" id="li_customer_name" value="" autocomplete="off" />
                        <div class="arperrmessage" id="li_customer_name_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARMember'); ?></div>         
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Customer Email', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input type="text" name="li_customer_email" id="li_customer_email" value="" autocomplete="off" />
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Purchase Code', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<input type="text" name="li_license_key" id="li_license_key" value="" autocomplete="off" />
                        <div class="arperrmessage" id="li_license_key_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARMember'); ?></div>        
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><?php _e('Domain Name', 'ARMember'); ?></th>
					<td class="arm-form-table-content">
						<label class="lblsubtitle"><?php echo $hostname; ?></label>
                        <input type="hidden" name="li_domain_name" id="li_domain_name" value="<?php echo $hostname; ?>" autocomplete="off" />        
					</td>
				</tr>
                <input type="hidden" name="receive_updates" id="receive_updates" value="0" autocomplete="off" />
                <tr class="form-field">
					<th class="arm-form-table-label">&nbsp;</th>
					<td class="arm-form-table-content">
						<span id="license_link"><button type="button" id="verify-purchase-code-addon" name="continue" style="width:150px; cursor:pointer; background-color:#53ba73; border:0px; color:#FFFFFF; height:40px; border-radius:3px;" class="greensavebtn"><?php _e('Activate', 'ARMember'); ?></button></span>
                        <span id="license_loader" style="display:none;position:absolute;margin-top:12px; padding-left:10px;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/loading_activation.gif'; ?>" height="15" /></span> 
                        <span id="license_error" style="display:none;position:absolute;margin-top:12px; padding-left:10px;">&nbsp;</span>
                        <span id="license_success" style="display:none;position:absolute;margin-top:12px;"><?php _e('License Activated Successfully.', 'ARMember'); ?></span>
                        <input type="hidden" name="ajaxurl" id="ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>"  />        
					</td>
				</tr>
				
			</table>
</div> 
<script type="text/javascript">
    var ADDON_NOT_COMPATIBLE_MESSAGE = "<?php _e('This Addon is not compatible with current ARMember version. Please update ARMember to latest version.','ARMember'); ?>";
    </script>
    
<?php
$_SESSION['arm_member_addon'] = $myplugarr;