<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_slugs;
$active = 'arm_general_settings_tab_active';

$_r_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'email_notification';
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
    <div class="content_wrapper arm_global_settings_content" id="content_wrapper">
        <div class="page_title" style="margin: 0px;"><?php _e('Email Notification', 'ARMember'); ?></div>
        <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
        <div class="armclear"></div>
        <div class="arm_general_settings_wrapper">
            <div class="arm_settings_container" style="border-top: 0px;">
                <?php 
				if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_email_templates.php')) {
					include(MEMBERSHIP_VIEWS_DIR . '/arm_email_templates.php');
				}
                ?>
            </div>
        </div>
        <div class="armclear"></div>
    </div>
</div>