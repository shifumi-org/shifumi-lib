<?php
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
if (isset($_REQUEST['arm_default_private_content_save'])) {
	do_action('arm_save_default_private_content', $_REQUEST);
}
?>

<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.ColVis_Button{display:none;}
</style>

<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready( function () {
    arm_load_private_content_list_grid(false);
});

function arm_load_private_content_list_filtered_grid()
{
    jQuery('#armember_datatable').dataTable().fnDestroy();
    arm_load_private_content_list_grid();
}

function show_grid_loader() {
    jQuery('.arm_loading_grid').show();
}

function arm_load_private_content_list_grid(is_filtered){

	var __ARM_Showing = '<?php echo addslashes(__('Showing','ARMember')); ?>';
    var __ARM_Showing_empty = '<?php echo addslashes(__('Showing 0 to 0 of 0 entries','ARMember')); ?>';
    var __ARM_to = '<?php echo addslashes(__('to','ARMember')); ?>';
    var __ARM_of = '<?php echo addslashes(__('of','ARMember')); ?>';
    var __ARM_Entries = ' <?php echo addslashes(__('entries','ARMember')); ?>';
    var __ARM_Show = '<?php echo addslashes(__('Show','ARMember')); ?> ';
    var __ARM_NO_FOUND = '<?php echo addslashes(__('No user private content found.','ARMember')); ?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(__('No matching records found.','ARMember')); ?>';

	var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
	var table = jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"sProcessing": show_grid_loader(),
        "oLanguage": {
            "sProcessing": show_grid_loader(),
            "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_Entries,
            "sInfoEmpty": __ARM_Showing_empty,
           
            "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_Entries,
            "sEmptyTable": __ARM_NO_FOUND,
            "sZeroRecords": __ARM_NO_MATCHING,
        },
        "language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
        "bProcessing": false,
        "bServerSide": true,
        "sAjaxSource": ajax_url,
		"bJQueryUI": true,
		"bPaginate": true,
		"sServerMethod": "POST",
		"bAutoWidth" : false,
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "sClass": 'center', "aTargets": [0,1,2,3]},
			{ "bSortable": false, "aTargets": [2,3] }
		],

		"iCookieDuration": 60 * 60,
        "sCookiePrefix": "arm_datatable_",
        "aLengthMenu": [10, 25, 50, 100, 150, 200],
        "fnStateSave": function (oSettings, oData) {
            oData.aaSorting = [];
            oData.abVisCols = [];
            oData.aoSearchCols = [];
            this.oApi._fnCreateCookie(
                oSettings.sCookiePrefix + oSettings.sInstance,
                this.oApi._fnJsonString(oData),
                oSettings.iCookieDuration,
                oSettings.sCookiePrefix,
                oSettings.fnCookieCallback
            );
        },
		"fnPreDrawCallback": function () {
            show_grid_loader();
        },
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            jQuery(nRow).find('.arm_grid_action_wrapper').each(function () {
                jQuery(this).parent().addClass('armGridActionTD');
                jQuery(this).parent().attr('data-key', 'armGridActionTD');
            });
        },
        "fnStateLoadParams": function (oSettings, oData) {
            oData.iLength = 10;
            oData.iStart = 0;
        },
		"fnServerParams": function (aoData) {
            aoData.push({'name': 'action', 'value': 'get_private_content_data'});
        },
		"fnDrawCallback":function(){
			jQuery('.arm_loading_grid').hide();
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
}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}


// ]]>
</script>

<?php
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();

global $wpdb, $ARMember, $arm_global_settings;
$user_table = $ARMember->tbl_arm_members;
$user_meta_table = $wpdb->usermeta;


?>
<div class="wrap arm_page arm_private_content_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_private_content_wrapper arm_position_relative" id="content_wrapper" >
		<div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
		<div class="page_title">
			<?php esc_html_e('Manage Userwise Private Content','ARMember');?>
			
		<div class="arm_add_new_item_box">
			<a class="greensavebtn" href="<?php echo admin_url('admin.php?page='.$arm_slugs->private_content.'&action=add_private_content');?>"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php esc_html_e('Add Private Content', 'ARMember') ?></span></a>
		</div>		
			<div class="armclear"></div>
		</div>


		<div class="armclear"></div>

		<div class="arm_private_content_list arm_position_relative" style="top:10px;">
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list" onsubmit="return apply_bulk_action_subscription_plans_list();">
				<input type="hidden" name="page" value="<?php echo $arm_slugs->private_content;?>" />
				<input type="hidden" name="armaction" value="list" />

				<div id="armmainformnewlist">
					<table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display" id="armember_datatable">
						<thead>
							<tr>
								<th class="arm_min_width_50 arm_text_align_center"><?php esc_html_e('User ID','ARMember');?></th>
								<th class="arm_min_width_150 arm_text_align_center"><?php esc_html_e('Username','ARMember');?></th>
								<th class="arm_max_width_140 arm_text_align_center"><?php esc_html_e('Enable / Disable Private Content','ARMember');?></th>
								
								<th class="armGridActionTD"></th>
							</tr>
						</thead>
					</table>
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_html_e('Show / Hide columns','ARMember');?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search','ARMember');?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('plans','ARMember');?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show','ARMember');?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing','ARMember');?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to','ARMember');?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of','ARMember');?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching plans found','ARMember');?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('No any subscription plan found.','ARMember');?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from','ARMember');?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total','ARMember');?>"/>
					<?php wp_nonce_field( 'arm_wp_nonce' );?>
				</div>
				<div class="footer_grid"></div>
			</form>
		</div>
		<?php 
		/* **********./Begin Bulk Delete Plan Popup/.********** */
		$bulk_delete_plan_popup_content = '<span class="arm_confirm_text">'.esc_html__("Are you sure you want to delete this plan(s)?",'ARMember' ).'</span>';
		$bulk_delete_plan_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_plan_popup_arg = array(
			'id' => 'delete_bulk_plan_message',
			'class' => 'delete_bulk_plan_message',
            'title' => esc_html__('Delete Plan(s)', 'ARMember'),
			'content' => $bulk_delete_plan_popup_content,
			'button_id' => 'arm_bulk_delete_plan_ok_btn',
			'button_onclick' => "arm_delete_bulk_plan('true');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_plan_popup_arg);
		/* **********./End Bulk Delete Plan Popup/.********** */
		?>
		<div class="armclear"></div>
		<br>
		<div class="arm_solid_divider"></div>

		<div class="page_title arm_defualt_private_content_title">
			<?php esc_html_e('Default Private Content','ARMember');?>
			<i class="arm_helptip_icon armfa armfa-question-circle" title='<?php esc_html_e('If private content is not set for specific user and user is not in above list than default private content will be displayed to the user.', 'ARMember'); ?>'></i>
			<div class="armclear"></div>
		</div>

		<form method="post" name="default_private_content_form" id="default_private_content_form" class="arm_padding_left_45">
			<table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display" id="armember_private_content_default">
				<tr>
					<th>
						
					</th>
					<td>
						<div class="arm_default_private_content_editor">
						<?php 
							$default_private_content = get_option('arm_member_default_private_content');
							$default_private_content = !empty($default_private_content) ? stripslashes_deep($default_private_content) : '';
							$arm_message_editor = array(
								'textarea_name' => 'arm_default_private_content',
								'editor_class' => 'arm_default_private_content',
								'media_buttons' => true,
								'textarea_rows' => 10,
								'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
							);
							wp_editor($default_private_content, 'arm_default_private_content', $arm_message_editor);

						?>
						</div>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div class="arm_membership_setup_shortcode_box">
	                        <span class="arm_shortcode_label"><?php esc_html_e("Userwise Private Content Shortcode :", "ARMember"); ?></span>
	                        <span class="arm_form_shortcode arm_shortcode_text arm_form_shortcode_box arm_margin_bottom_20">
	                            <span class="armCopyText" >[arm_user_private_content]</span>
	                            <span class="arm_click_to_copy_text" data-code="[arm_user_private_content]"><?php esc_html_e("Click to Copy", "ARMember"); ?></span>
	                            <span class="arm_copied_text">
	                                <img src="<?php echo MEMBERSHIP_IMAGES_URL.'/copied_ok.png';?>">
	                                <?php esc_html_e("Code Copied", "ARMember"); ?>
	                            </span>
	                        </span>  
	                    </div>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div class="arm_submit_btn_container">
							<button class="arm_save_btn" value="" id="arm_default_private_content_save" name="arm_default_private_content_save" type="submit"><?php esc_html_e('Save', 'ARMember') ?></button>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<div class="arm_member_view_detail_container"></div>
<?php
	echo $ARMember->arm_get_need_help_html_content('users-private-content-list');
?>