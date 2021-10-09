<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings,$arm_members_badges,$arm_email_settings,$arm_manage_coupons;
$active = 'arm_general_settings_tab_active';
$b_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "manage_badges";
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
	<div class="content_wraper arm_badges_settings_content" id="content_wraper">
		<div class="page_title"><?php _e('Badges And Achievement','ARMember'); ?></div>
        <?php
    if ($setact != 1) {
       $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
		<div class="armclear"></div>
		<div class="arm_general_settings_wrapper">			
			<div class="arm_general_settings_tab_wrapper">
				<a class="arm_general_settings_tab <?php echo(in_array($b_action, array('manage_badges'))) ? $active : ""; ?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->badges_achievements); ?>">&nbsp;<?php _e('Badges', 'ARMember'); ?>&nbsp;&nbsp;</a>
                <a class="arm_general_settings_tab <?php echo (in_array($b_action, array('manage_achievements'))) ? $active : "";?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->badges_achievements . '&action=manage_achievements'); ?>">&nbsp;&nbsp;<?php _e('Achievements', 'ARMember'); ?>&nbsp;&nbsp;</a>
				<a class="arm_general_settings_tab <?php echo (in_array($b_action, array('manage_user_achievements'))) ? $active : "";?>" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->badges_achievements . '&action=manage_user_achievements'); ?>">&nbsp;&nbsp;<?php _e('User Badges', 'ARMember'); ?>&nbsp;&nbsp;</a>
				<div class="armclear"></div>
            </div>			
			<div class="arm_settings_container">
				<?php 
				$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_badges.php';
				switch ($b_action)
				{
					case 'manage_badges':
						$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_badges.php';
						break;
					case 'manage_achievements':
						$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_achievements.php';
						break;					
					case 'manage_user_achievements':
						$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_user_achievements.php';
						break;
					default:
						$file_path = MEMBERSHIP_VIEWS_DIR . '/arm_manage_badges.php';
						break;
				}
				if (file_exists($file_path)) {
					include($file_path);
				}
                ?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>