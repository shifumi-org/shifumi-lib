<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings,$arm_members_badges,$arm_email_settings,$arm_manage_coupons;
            
$profileTemplate = $ARMember->tbl_arm_member_templates;
$templateOptions = $wpdb->get_var($wpdb->prepare("SELECT `arm_options` FROM {$profileTemplate} WHERE arm_type = %s", 'profile'));
$display_admin_user = 0;
if (!empty($templateOptions)) {
    $templateOptions = maybe_unserialize($templateOptions);
    $display_admin_user = isset($templateOptions['show_admin_users']) ? $templateOptions['show_admin_users'] : 0;
}

if($display_admin_user == 1)
{
    $all_members = $arm_members_class->arm_get_all_members_with_administrators(0,0);
}
else
{
    $all_members = $arm_members_class->arm_get_all_members_without_administrator(0,0);
} 



$users_data = array();
$badges_list = $arm_members_badges->arm_get_all_badges();
$global_settings = $arm_global_settings->global_settings;
$badge_width = !empty($global_settings['badge_width']) ? $global_settings['badge_width'] : 30;
$badge_height = !empty($global_settings['badge_height']) ? $global_settings['badge_height'] : 30;
$badgeIconStyle = "width:" . $badge_width . "px; height:" . $badge_height . "px;";
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.delete_box{float:left;}
	.row-actions{text-align: center;}
	.ColVis_Button{ display: none !important;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
    jQuery(document).ready(function () {
        arm_user_achievements();
    });
    function show_grid_loader() {
        jQuery('.arm_loading_grid').show();
    }
    function arm_load_user_achievement_filter() {
        jQuery('#armember_datatable').dataTable().fnDestroy();
        arm_user_achievements();
    }
    function arm_user_achievements() {
        //action=get_user_achievements
        var search_term = jQuery("#armmanagesearch_new").val();
        var ajax_url = '<?php echo admin_url('admin-ajax.php?') ?>';
        var _wpnonce = jQuery('input[name="_wpnonce"]').val();
        jQuery('#armember_datatable').dataTable({
            "bProcessing": false,
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sEmptyTable": "No any user badge found.",
                "sZeroRecords": "No matching records found."
            },
            "columnDefs": [
                { "width": "30%", "aTargets": [0] },
     
                 { "width": "70%", "aTargets": [2] },
                {"bSortable": false, "aTargets": [2,3]},
               
              ],
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod":"POST",
            "fnServerParams":function(aoData){
                aoData.push({"name":"action","value":"get_user_achievements"});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
                
            },
            "bRetrieve": false,
            "sDom": '<"H"Cfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 15, 20, 25, 50, 100],
            "iDisplayLength": 15,
            "sScrollX": "100%",
            colVis: {
                exclude: [0, 1, 2, 3]
            },
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                oData.iStart = 0;
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                    );
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 15;
                oData.iStart = 0;
            },
            "fnPreDrawCallback": function () {
                jQuery('.arm_loading_grid').show();
                jQuery("#armmanagesearch").remove();
                jQuery(".ColVis_collectionBackground").remove();
                jQuery('.ColVis_collection').remove();
                jQuery('.ColVis_catcher').remove();
            },
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            "fnDrawCallback": function () {
                jQuery('.arm_loading_grid').hide();
                jQuery("#armmanagesearch").remove();
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
            }
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('div#armember_datatable_filter').parent().append(filter_box);
        jQuery('#arm_filter_wrapper').remove();
        jQuery('#armmanagesearch_new').on('keyup', function (e) {
            e.stopPropagation();
            if (e.keyCode == 13) {

                arm_load_user_achievement_filter();
                return false;
            }
        });
    }
// ]]>				
</script>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">	
        <div class="arm_add_new_item_box" style="margin: 0 0 20px 0;">			
            <a class="greensavebtn arm_add_user_badges_btn" href="javascript:void(0);" style="margin-right:20px;"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add User Badges', 'ARMember');?></span></a>
        </div>
        <form method="GET" id="achive_badges_list_form" class="data_grid_list arm_user_badges_grid_form">
            <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
            <input type="hidden" name="armaction" value="list" />
            <div id="armmainformnewlist">
                <div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
                <table cellpadding="0" cellspacing="0" border="0" class="display arm_achievements_list_grid" id="armember_datatable">
                    <thead>
                        <tr>
                            <th><?php _e('Username', 'ARMember'); ?></th>
                            <th style="padding-left: 10px;text-align: left;"><?php _e('Email Address', 'ARMember'); ?></th>
                            <th style="padding-left: 10px;text-align: left;min-width:250px;"><?php _e('Badges', 'ARMember'); ?></th>
                            <th data-key="armGridActionTD" class="armGridActionTD" style="display: none;"></th>
                        </tr>
                    </thead>

                </table>
                <div class="armclear"></div>
                <input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search','ARMember');?>"/>
                <input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('members','ARMember');?>"/>
                <input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show','ARMember');?>"/>
                <input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing','ARMember');?>"/>
                <input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to','ARMember');?>"/>
                <input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of','ARMember');?>"/>
                <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching records found.','ARMember');?>"/>
                <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('No any user badge found.','ARMember');?>"/>
                <input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from','ARMember');?>"/>
                <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total','ARMember');?>"/>
                <?php wp_nonce_field( 'arm_wp_nonce' );?>
            </div>
            <div class="footer_grid"></div>
        </form>
        <div class="armclear"></div>
	</div>
</div>
<!--./******************** Add User Badges Form ********************/.-->
<div class="add_new_user_badges_wrapper popup_wrapper" style="width: 650px;margin-top: 40px;">
	<form method="post" action="#" id="arm_add_user_badges_wrapper_frm" class="arm_admin_form arm_add_user_badges_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="arm_add_user_badges_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Add User Badges','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">
                        <tr class="form-field">
							<th><?php _e('Select Badge Icon','ARMember'); ?></th>
                            <td>
                                <div class="arm_badge_icon_lists arm_required_wrapper">
								<?php 
								foreach ($badges_list as $badge) {
                            if(file_exists(strstr($badge->arm_badges_icon, "//"))){
                                $badge->arm_badges_icon =strstr($badge->arm_badges_icon, "//");
                            }else if(file_exists($badge->arm_badges_icon)){
                               $badge->arm_badges_icon = $badge->arm_badges_icon;
                            }else{
                                $badge->arm_badges_icon = $badge->arm_badges_icon;
                            }
									echo '<span class="arm_add_user_badges armhelptip_front" data-badge_id="'.$badge->arm_badges_id.'" title="'.$badge->arm_badges_name.'"><img src="' . ($badge->arm_badges_icon) . '" alt="" style="'.$badgeIconStyle.'" /></span>';
								}
								?>
								<input type="hidden" id="arm_add_badges_id" value="" name="arm_user_badge_id" data-msg-required="<?php _e('Please select a badge icon.', 'ARMember');?>" />
                                </div>
							</td>
						</tr>
                        <tr class="form-field">
							<th><?php _e('Select Users','ARMember'); ?></th>
                            <td class="arm_required_wrapper arm_multiauto_user_field">
                                <input id="arm_user_multi_auto_selection" type="text" name="arm_user_ids_text" value="" placeholder="<?php _e('Search by username or email...', 'ARMember');?>" data-msg-required="<?php _e('Please select user.', 'ARMember');?>" required>
                                <div class="arm_users_multiauto_items arm_required_wrapper" id="arm_users_multiauto_items" style="display: none;"></div>
                                <input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="<?php echo $display_admin_user;?>">
								<?php /*?><select id="arm_user_ids_select" class="arm_chosen_selectbox arm_user_badges_add_achievement" data-msg-required="<?php _e('Please select atleast one user.', 'ARMember');?>" name="arm_user_ids[]" data-placeholder="<?php _e('Select User(s)..', 'ARMember');?>" multiple="multiple" style="width:500px;">
									<?php if (!empty($all_members)):?>
										<?php foreach ($all_members as $user): ?>
											<option class="arm_message_selectbox_op" value="<?php echo $user->ID;?>"><?php echo $user->user_login;?></option>
										<?php endforeach;?>
									<?php else: ?>
										<option value=""><?php _e('No User(s) Available', 'ARMember');?></option>
									<?php endif;?>
								</select><?php */?>
							</td>
						</tr>
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_loader_img" style="position: relative;top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<button class="arm_save_btn arm_add_user_badges_save" type="submit" data-type="add"><?php _e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn arm_add_user_badges_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
                        <?php wp_nonce_field( 'arm_wp_nonce' );?>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<script type="text/javascript">
    __NO_USER = '<?php echo addslashes( __('No users(s) Available','ARMember')); ?>';
    __SELECT_USER = '<?php echo addslashes( __('Select user','ARMember')); ?>';
    __USER_BADGE_DELETED = '<?php echo addslashes( __('User badge has been deleted successfully.','ARMember')); ?>';
    __USER_BADGE_DELETE_ERROR = '<?php echo addslashes( __('There is problem while deleting badges.Please try again letter.','ARMember')); ?>';

</script>
<div id="arm_user_lists_chosen" style="display:none;visibility: hidden;opacity:0;">
    <?php
        echo json_encode($all_members);
    ?>
</div>