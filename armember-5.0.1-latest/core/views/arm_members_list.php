<?php global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans; 

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_manage_members_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper" id="content_wrapper">
		<div class="page_title">
	<?php 
			_e('Manage Members', 'ARMember');
	?>
    		
			<div class="arm_add_new_item_box">
				<a class="greensavebtn" href="<?php echo admin_url('admin.php?page='.$arm_slugs->manage_members.'&action=new');?>"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add Member', 'ARMember') ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_members_grid_container" id="arm_members_grid_container">
			<?php 
			if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_members_list_records.php')) {
				include( MEMBERSHIP_VIEWS_DIR.'/arm_members_list_records.php');
			}
			?>
		</div>
		<?php 
		global $arm_global_settings;
		/* **********./Begin Bulk Delete Member Popup/.********** */
		$bulk_delete_member_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to delete this member(s)?",'ARMember' );
		$bulk_delete_member_popup_content .= '<br/>'.__("If you will delete these member(s), their subscription will be removed.",'ARMember' ).'</span>';
		$bulk_delete_member_popup_content .= '<span class="arm_change_plan_confirm_text">'.__("This action cannot be reverted, Are you sure you want to change membership plan of selected member(s)?",'ARMember' ).'</span>';
		$bulk_delete_member_popup_content .= '<span class="arm_change_status_confirm_text">'.__( "Are you sure you want to change status of selected member(s)?", 'ARMember' ).'</span>';
		$bulk_delete_member_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_member_popup_title = '<span class="arm_confirm_text">'.__('Delete Member(s)', 'ARMember').'</span>';
		$bulk_delete_member_popup_title .= '<span class="arm_change_plan_confirm_text">'.__('Change Plan', 'ARMember').'</span>';
		$bulk_delete_member_popup_title .= '<span class="arm_change_status_confirm_text">'.__('Change Status', 'ARMember').'</span>';
		$bulk_delete_member_popup_arg = array(
			'id' => 'delete_bulk_form_message',
			'class' => 'delete_bulk_form_message',
			'title' => $bulk_delete_member_popup_title,
			'content' => $bulk_delete_member_popup_content,
			'button_id' => 'arm_bulk_delete_member_ok_btn',
			'button_onclick' => "apply_member_bulk_action('bulk_delete_flag');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_member_popup_arg);
		/* **********./End Bulk Delete Member Popup/.********** */
		?>
	</div>
</div>
<style type="text/css" title="currentStyle">
    .paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

var ARM_IMAGE_URL = "<?php echo MEMBERSHIP_IMAGES_URL; ?>";

jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});

jQuery(document).on("click","#cb-select-all-1",function () {
    jQuery('input[name="item-action[]"]').attr('checked', this.checked);
});

jQuery(document).on('click','input[name="item-action[]"]',function() {
    if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
        jQuery("#cb-select-all-1").attr("checked", "checked");
    }
    else {
        jQuery("#cb-select-all-1").removeAttr("checked");
    }
});

jQuery(document).on('click', "#armember_datatable_wrapper .ColVis_Button:not(.ColVis_MasterButton)", function () {
	var form_id = jQuery('#arm_form_filter').val();
	var column_list = "";
	var _wpnonce = jQuery('input[name="_wpnonce"]').val();

	var column_list_str = '';
	jQuery('#armember_datatable_wrapper .ColVis_Button:not(.ColVis_MasterButton)').each(function(){
		if(jQuery(this).hasClass('active'))
		{
			column_list_str += '1,';
		}
		else {
			column_list_str += '0,';
		}
		
	});
    var column_list = [[ column_list_str ]];
    if (form_id=='') { return false; }
	jQuery.ajax({
		type:"POST",
		url:__ARMAJAXURL,
		data:"action=arm_members_hide_column&form_id="+form_id+"&column_list="+column_list+"&_wpnonce="+_wpnonce,
		success: function (msg) {
			return false;
		}
	});
});
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>

<div class="arm_member_manage_plan_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>" style="width:1000px; min-height: 200px;">
    <form method="GET" id="arm_member_manage_plan_user_form" class="arm_admin_form">
        <div>
            <div class="popup_header">
                <span class="popup_close_btn arm_popup_close_btn arm_member_manage_plan_detail_close_btn"></span>
                <input type="hidden" id="arm_edit_plan_user_id" />
                <span class="add_rule_content"><?php _e('Manage Plans', 'ARMember'); ?> <span class="arm_manage_plans_username"></span></span>
            </div>
            <div class="popup_content_text arm_member_manage_plan_detail_popup_text" style="text-align:center;">
            	
            <div style="width: 100%; margin: 45px auto;">	<img src="<?php echo MEMBERSHIP_IMAGES_URL."/arm_loader.gif"; ?>"></div>

            </div>
            <div class="armclear"></div>
        </div>
    </form>
</div>

<?php
	echo $ARMember->arm_get_need_help_html_content('started-armember');
?>