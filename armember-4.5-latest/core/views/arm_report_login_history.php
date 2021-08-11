<?php
global $wpdb, $ARMember, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $arm_members_activity, $check_sorting;
$setact = 0;
$setact = $arm_members_activity->$check_sorting();


$page_title = "";
if ( isset($_GET['action']) && $_GET['action'] == 'login_history' ) {
    $page_title = esc_html__("Login Reports", "ARMember");
}


if(!empty($_POST['arm_export_login_history']) && $_POST['arm_export_login_history'] == '1') {
    global $arm_report_analytics;
    $arm_report_analytics->arm_all_user_login_history_page_export_func($_POST);
    exit;
}

?>


<div class="wrap arm_page arm_report_analytics_main_wrapper">
    <div class="content_wrapper arm_report_analytics_content arm_report_login_history_content" id="content_wrapper">
        <div class="page_title">
            <?php echo $page_title; ?>
             <?php
				if ($setact != 1) {
					$admin_css_url = admin_url('admin.php?page=arm_manage_license');
					?>
			
					<div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
			<?php } ?>
            <div class="armclear"></div>

        </div>

        <div class="armclear"></div>

    <?php
    if(isset($_GET['action']) && $_GET['action'] == 'login_history') {
    ?>
        <form  method="post" action="#" id="arm_login_history_page_form" class="arm_block_settings arm_admin_form">
        <table class="form-table">
            <tr class="arm_global_settings_sub_content track_login_history" >
                <td class="arm-form-table-content" colspan="2">
                <?php
                    global $arm_report_analytics;
                    $arm_log_history_search_user = '';
                    $login_history = $arm_report_analytics->arm_get_all_user_for_login_history_page(1, 10, $arm_log_history_search_user);
                ?>
                <?php if(isset($login_history) && !empty($login_history)): ?>
                
                    <div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">
                        <div class="arm_all_loginhistory_main_wrapper" id="arm_all_loginhistory_page_main_wrapper">
                            <div class="arm_all_loginhistory_filter_wrapper arm_datatable_searchbox">
                                <table class="form-table arm_member_last_subscriptions_table arm_member_login_history_filter_table" width="100%">
                                    <tr>
                                        <td>
                                            <?php $float = (is_rtl()) ? 'float:right;' : 'float:left;'; ?>
                                            

                                            
                                            <label class="arm_log_history_search_lbl_user"><input type="text" placeholder="<?php esc_html_e('Search by username', 'ARMember'); ?>" id="arm_log_history_search_user" name="arm_log_history_search_user" value="" tabindex="-1" ></label>

                                            <div class="sltstandard" style=" <?php echo $float; ?>">
                                                <div style=" <?php echo $float; ?>margin:5px 2px;">
                                                    <a href="javascript:void(0);" class="btn_chart_type active" id="login_history" onclick="javascript:arm_change_login_hisotry_report('login_history');"><?php echo addslashes(esc_html__('Loggedin History', 'ARMember')); ?></a>
                                                </div>
                                                <div style=" <?php echo $float; ?>margin:5px 2px;">
                                                    <a href="javascript:void(0);" class="btn_chart_type" id="fail_login_history" onclick="javascript:arm_change_login_hisotry_report('fail_login_history');"><?php echo addslashes(esc_html__('Fail Login Attempt History', 'ARMember')); ?></a>
                                                </div>

                                                <input type="hidden" id="arm_login_history_type" name="arm_login_history_type" class="arm_login_history_type" value="login_history" />

                                            </div>

                                            <?php 
                                            /*
                                            <div class="arm_all_loginhistory_filter_inner">
                                                <span class="arm_manage_filter_label"><?php _e('Filter By', 'ARMember') ?></span>
                                                
                                                <dl class="arm_selectbox">
                                                    <dt style="width: 130px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_login_history_type" data-placeholder="<?php _e('Select field', 'ARMember'); ?>">
                                                        <?php
                                                        foreach ($login_filter as $key => $filter) { 
                                                        ?>
                                                                <li data-label="<?php echo $filter ?>" data-value="<?php echo $key ?>"><?php echo $filter ?></li>
                                                        <?php 
                                                        }
                                                        ?>     
                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                            */
                                            ?>
                                            <div class="arm_all_loginhistory_filter_inner">
                                                <button id="arm_login_history_page_search_btn" class="armemailaddbtn arm_login_history_page_search_btn" type="button"><?php esc_html_e('Apply', 'ARMember'); ?></button>
                                                <button id="arm_login_history_page_export_btn" class="armemailaddbtn" type="button"><?php esc_html_e('Export To CSV', 'ARMember') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>                           

                                <?php echo $login_history;?>
                        </div>
                    </div>
                <?php
                    $totalRecord = $wpdb->get_var("SELECT COUNT(`arm_history_id`) FROM `" . $ARMember->tbl_arm_login_history . "`");
                    if ($totalRecord > 0) {}
                ?>
                    
                <?php endif;?>
                                            
                </td>
            </tr>
                            
        </table>
        <input type='hidden' name='arm_export_login_history' value='0'>
        </form>
    <?php
    }
    ?>

    </div>
</div>

<div class="arm_member_view_detail_container"></div>