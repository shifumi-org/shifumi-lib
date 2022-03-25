<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_slugs, $arm_subscription_plans, $arm_manage_communication;

$arm_all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$template_list = $arm_email_settings->arm_get_all_email_template();
$messages = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_auto_message."` ORDER BY `arm_message_id` DESC");
$message_types = array(
    'on_new_subscription' => __('On New Subscription', 'ARMember'),
    'on_change_subscription' => __('On Change Subscription', 'ARMember'),
    'on_change_subscription_by_admin' => __('On Change Subscription By Admin', 'ARMember'),
    'on_renew_subscription' => __('On Renew Subscription', 'ARMember'),
    'on_recurring_subscription' => __('On Recurring Subscription', 'ARMember'),
    'on_cancel_subscription' => __('On Cancel Membership', 'ARMember'),
    'before_expire' => __('Before Membership Expired', 'ARMember'),
    'manual_subscription_reminder' => __('Before Semi Automatic Subscription Payment due','ARMember'),
    'automatic_subscription_reminder' => __('Before Automatic Subscription Payment due','ARMember'),
    'on_close_account' => __('On Close User Account', 'ARMember'),
    'on_login_account' => __('On User Login', 'ARMember'),
    
    'on_expire' => __('On Membership Expired', 'ARMember'),
    'on_failed' => __('On Failed Payment', 'ARMember'),
    'on_next_payment_failed' => __('On Semi Automatic Subscription Failed Payment', 'ARMember'),
    'trial_finished' => __('Trial Finished', 'ARMember'),
    
    
);

$message_types = apply_filters('arm_notification_add_message_types',$message_types);

$form_id = 'arm_add_message_wrapper_frm';
$mid = 0;
$edit_mode = false;
$msg_type = 'on_new_subscription';
?>
<style type="text/css" title="currentStyle">
    .paginate_page a{display:none;}
    #poststuff #post-body {margin-top: 32px;}
	.delete_box{float:left;}
    .ColVis_Button{ display: none !important;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready(function () {
	jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
                "oLanguage": {
                    "sEmptyTable": "No any email template found.",
                    "sZeroRecords": "No matching records found."
                },
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aaSorting": [],
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [1]}
		],
		"language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
		"oColVis": {
			"aiExclude": [0]
		},
		"iDisplayLength": 50,
	});
        
        arm_load_communication_messages_list_grid();
      
});

function arm_load_communication_list_filtered_grid(data)
{
    var tbl = jQuery('#armember_datatable_1').dataTable(); 
        
        tbl.fnDeleteRow(data);
      
        jQuery('#armember_datatable_1').dataTable().fnDestroy();
        arm_load_communication_messages_list_grid();
}

function arm_load_communication_messages_list_grid() {
	
	
	jQuery('#armember_datatable_1').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
                "oLanguage": {
                    "sEmptyTable": "No any automated email message found.",
                    "sZeroRecords": "No matching records found."
                  },
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aaSorting": [],
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [0, 2, 5]}
		],
		"language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
		"oColVis": {
			"aiExclude": [0, 5]
		},
                "fnDrawCallback": function () {
                    jQuery("#cb-select-all-1").prop("checked", false);
                },
	});
        
         var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
         
	jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
	jQuery('#arm_filter_wrapper').remove(); 
	
    }
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<div class="arm_email_notifications_main_wrapper">
	<div class="page_sub_content">
		<div class="page_sub_title" style="float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" ><?php _e('Standard Email Responses','ARMember');?></div>
		<?php if(empty($messages)):?>
		<div class="arm_add_new_item_box" style="margin: 0 0 20px 0;">			
			<a class="greensavebtn arm_add_new_message_btn arm_margin_right_20" href="javascript:void(0);" ><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Response', 'ARMember') ?></span></a>
		</div>
		<?php endif;?>
		<div class="armclear"></div>
		<div class="arm_email_templates_list">
		<form method="GET" id="email_templates_list_form" class="data_grid_list arm_email_settings_wrapper">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
			<input type="hidden" name="armaction" value="list" />
			<div id="armmainformnewlist">
				<div class="response_messages"></div>
				<div class="armclear"></div>
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable">
					<thead>
						<tr>
							<!--<th class="center"><?php _e('ID', 'ARMember');?></th>-->
							<th><?php _e('Template Name', 'ARMember'); ?></th>
							<th class="arm_text_align_center arm_width_100" ><?php _e('Active', 'ARMember'); ?></th>
							<th class="arm_padding_left_10" style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php _e('Subject', 'ARMember'); ?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($template_list)): ?>
						<?php foreach ($template_list as $key => $email_template) { ?>
							<?php
							if ($email_template->arm_template_slug == 'follow-notification' || $email_template->arm_template_slug == 'unfollow-notification') {
								if (!$arm_social_feature->isSocialFeature) {
									continue;
								}
							}
							if ($email_template->arm_template_slug == 'email-verify-user' || $email_template->arm_template_slug == 'account-verified-user') {
								$user_register_verification = $arm_global_settings->arm_get_single_global_settings('user_register_verification');
								if ($user_register_verification != 'email') {
									continue;
								}
							}
							$tempID = $email_template->arm_template_id;
							$edit_link = admin_url('admin.php?page=' . $arm_slugs->email_notifications . '&action=edit_template&template_id=' . $tempID);
							?>
							<tr class="member_row_<?php echo $tempID; ?>">
								<!--<td class="center"><?php echo $tempID;?></td>-->
								<td><a class="arm_edit_template_btn" href="javascript:void(0);" data-temp_id="<?php echo $tempID;?>" data-href="<?php echo $edit_link;?>"><?php echo $email_template->arm_template_name;?></a></td>
								<td class="center"><?php 
									$switchChecked = ($email_template->arm_template_status == 1) ? 'checked="checked"' : '';
									echo '<div class="armswitch">
										<input type="checkbox" class="armswitch_input arm_email_status_action" id="arm_email_status_input_'.$tempID.'" value="1" data-item_id="'.$tempID.'" '.$switchChecked.'>
										<label class="armswitch_label" for="arm_email_status_input_'.$tempID.'"></label>
										<span class="arm_status_loader_img"></span>
									</div>';
								?></td>
								<td id="arm_email_template_subject_<?php echo $tempID; ?>"><?php echo esc_html(stripslashes($email_template->arm_template_subject));?></td>
								<td class="armGridActionTD"><?php
									$gridAction = "<div class='arm_grid_action_btn_container'>";
									$gridAction .= "<a class='arm_edit_template_btn' href='javascript:void(0);' data-temp_id='".$tempID."'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".__('Edit Message','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";
									$gridAction .= "</div>";
									echo '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>';
								?></td>
							</tr>
						<?php } ?>
						<?php endif;?>
					</tbody>
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php _e('Show / Hide columns', 'ARMember'); ?>"/>
				<input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search', 'ARMember'); ?>"/>
				<input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('messages', 'ARMember'); ?>"/>
				<input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show', 'ARMember'); ?>"/>
				<input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing', 'ARMember'); ?>"/>
				<input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to', 'ARMember'); ?>"/>
				<input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of', 'ARMember'); ?>"/>
				<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching templates found.', 'ARMember'); ?>"/>
				<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('No any email template found.', 'ARMember'); ?>"/>
				<input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from', 'ARMember'); ?>"/>
				<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total', 'ARMember'); ?>"/>
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
			<div class="footer_grid"></div>
		</form>
		<div class="armclear"></div>
		</div>
	</div>
<?php if(!empty($messages)):?>
	<div class="arm_solid_divider"></div>
        <div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_communication_bulk_action1" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_250">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_communication_bulk_action1">
								<li data-label="<?php _e('Bulk Actions','ARMember');?>" data-value="-1"><?php _e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php _e('Delete', 'ARMember');?>" data-value="delete_communication"><?php _e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go','ARMember');?>"/>
			</div>
		</div>
	<div class="page_sub_content">
		<div class="page_sub_title" style="float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" ><?php _e('Automated Email Messages','ARMember');?></div>
		<div class="arm_add_new_item_box" style="margin: 0 0 20px 0;">			
			<a class="greensavebtn arm_add_new_message_btn arm_margin_right_40" href="javascript:void(0);" ><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Response', 'ARMember') ?></span></a>
		</div>
		<div class="armclear"></div>
		<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_communication_bulk_action1" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_120">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_communication_bulk_action1">
								<li data-label="<?php _e('Bulk Actions','ARMember');?>" data-value="-1"><?php _e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php _e('Delete', 'ARMember');?>" data-value="delete_communication"><?php _e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go','ARMember');?>"/>
			</div>
		</div>
		<form method="GET" id="communication_list_form" class="data_grid_list arm_email_settings_wrapper" onsubmit="return apply_bulk_action_communication_list();return false;">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
			<input type="hidden" name="armaction" value="list" />
			<div id="armmainformnewlist">
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable_1">
					<thead>
						<tr>
							<th class="center cb-select-all-th arm_max_width_60" ><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php _e('Message Subject', 'ARMember');?></th>
							<th class="arm_width_100 arm_text_align_center"><?php _e('Active', 'ARMember');?></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php _e('Subscription', 'ARMember');?></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php _e('Type', 'ARMember');?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody id="">
					<?php if(!empty($messages)):?>
					<?php 
					foreach ($messages as $key => $rc) {
						$messageID = $rc->arm_message_id;
						$edit_link = admin_url('admin.php?page=' . $arm_slugs->email_notifications . '&action=edit_communication&message_id=' . $messageID);
						?>
						<tr class="arm_message_tr_<?php echo $messageID;?> row_<?php echo $messageID;?>">
							<td class="arm_padding_left_17">
								<input class="chkstanard arm_bulk_select_single" type="checkbox" value="<?php echo $messageID;?>" name="item-action[]">
							</td>
							<td>
								<a class="arm_edit_message_btn" href="javascript:void(0);" data-message_id="<?php echo $messageID;?>"><?php echo esc_html(stripslashes($rc->arm_message_subject));?></a>
							</td>
							<td class="center"><?php 
								$switchChecked = ($rc->arm_message_status == '1') ? 'checked="checked"' : '';
								echo '<div class="armswitch">
									<input type="checkbox" class="armswitch_input arm_communication_status_action" id="arm_communication_status_input_'.$messageID.'" value="1" data-item_id="'.$messageID.'" '.$switchChecked.'>
									<label class="armswitch_label" for="arm_communication_status_input_'.$messageID.'"></label>
									<span class="arm_status_loader_img"></span>
								</div>';
							?></td>
							<?php
							$subs_plan_title = '';
							if(!empty($rc->arm_message_subscription)){
								$plans_id = @explode(',', $rc->arm_message_subscription);
								$subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
								$subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '--';
							} else {
								$subs_plan_title = __('All Membership Plans', 'ARMember');
							}
							?>
							<td class=""><?php echo $subs_plan_title;?></td>
							<td><?php 
							$msge_type = '';
							switch ($rc->arm_message_type)
							{
								case 'on_new_subscription':
									$msge_type = __('On New Subscription', 'ARMember');
									break;
                                                                case 'on_menual_activation':
									$msge_type = __('On Manual User Activation', 'ARMember');
									break;
								case 'on_change_subscription':
									$msge_type = __('On Change Subscription', 'ARMember');
									break;
                                                                case 'on_renew_subscription':
									$msge_type = __('On Renew Subscription', 'ARMember');
									break;
								case 'on_failed':
									$msge_type = __('On Failed Payment', 'ARMember');
									break;
                                                                case 'on_next_payment_failed':
									$msge_type = __('On Semi Automatic Subscription Failed Payment', 'ARMember');
									break;
								case 'trial_finished':
									$msge_type = __('Trial Finished', 'ARMember');
									break;
								case 'on_expire':
									$msge_type = __('On Membership Expired', 'ARMember');
									break;
								case 'before_expire':
									$msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = $msge_per_unit . ' ' . $msge_per_type . '(s) ' . __('Before Membership Expired', 'ARMember');
									break;
	                            case 'manual_subscription_reminder':
	                                    $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = __('Semi Automatic Subscription Payment due', 'ARMember');
                                    $msge_type.= "(BeFore ".$msge_per_unit . ' ' . $msge_per_type . "(s))";
									break;
								case 'automatic_subscription_reminder':
	                                    $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = __('Automatic Subscription Payment due', 'ARMember');
                                    $msge_type.= "(BeFore ".$msge_per_unit . ' ' . $msge_per_type . "(s))";
									break;
                                case 'on_change_subscription_by_admin':
                                         $msge_type = __('On Change Subscription By Admin', 'ARMember');
									break;
                                case 'before_dripped_content_available':
                                        $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = $msge_per_unit . ' ' . $msge_per_type . '(s) ' . __('Before Dripped Content Available', 'ARMember');
									break;
                                case 'on_cancel_subscription':
									$msge_type = __('On Cancel Membership', 'ARMember');
									break;
								case 'on_recurring_subscription':
                                    $msge_type = __('On Recurring Subscription', 'ARMember');
									break;
								case 'on_close_account':
                                    $msge_type = __('On Close User Account', 'ARMember');
									break;
								case 'on_login_account':
                                    $msge_type = __('On User Login', 'ARMember');
									break;
								case 'on_new_subscription_post':
                                    $msge_type = __('On new paid post purchase', 'ARMember');
									break;	
								case 'on_recurring_subscription_post':
                                    $msge_type = __('On recurring paid post purchase', 'ARMember');
									break;
								case 'on_renew_subscription_post':
                                    $msge_type = __('On renew paid post purchase', 'ARMember');
									break;
								case 'on_cancel_subscription_post':
                                    $msge_type = __('On cancel paid post', 'ARMember');
									break;
								case 'before_expire_post':
                                    $msge_type = __('Before paid post expire', 'ARMember');
									break;
								case 'on_expire_post':
                                    $msge_type = __('On Expire paid post', 'ARMember');
									break;
								default:
                                    $msge_type = apply_filters('arm_notification_get_list_msg_type',$rc->arm_message_type);
									break;
							}
							echo $msge_type;
							?></td>
							<td class="armGridActionTD"><?php
								
								$gridAction = "<div class='arm_grid_action_btn_container'>";
								$gridAction .= "<a class='arm_edit_message_btn' href='javascript:void(0);' data-message_id='".$messageID."'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".__('Edit Message','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";
								$gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$messageID});'><img src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png' class='armhelptip' title='".__('Delete','ARMember')."' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete_hover.png';\" onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png';\" /></a>";
								$gridAction .= $arm_global_settings->arm_get_confirm_box($messageID, __("Are you sure you want to delete this message?", 'ARMember'), 'arm_communication_delete_btn');
								$gridAction .= "</div>";
								echo '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>';
							?></td>
						</tr>
						<?php } ?>  
					<?php endif;?>
					</tbody>
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="search_grid" id="automated_search_grid" value="<?php _e('Search', 'ARMember');?>"/>
				<input type="hidden" name="entries_grid" id="automated_entries_grid" value="<?php _e('messages', 'ARMember');?>"/>
				<input type="hidden" name="show_grid" id="automated_show_grid" value="<?php _e('Show', 'ARMember');?>"/>
				<input type="hidden" name="showing_grid" id="automated_showing_grid" value="<?php _e('Showing', 'ARMember');?>"/>
				<input type="hidden" name="to_grid" id="automated_to_grid" value="<?php _e('to', 'ARMember');?>"/>
				<input type="hidden" name="of_grid" id="automated_of_grid" value="<?php _e('of', 'ARMember');?>"/>
				<input type="hidden" name="no_match_record_grid" id="automated_no_match_record_grid" value="<?php _e('No matching messages found', 'ARMember');?>"/>
				<input type="hidden" name="no_record_grid" id="automated_no_record_grid" value="<?php _e('There is no any communication message found.', 'ARMember');?>"/>
				<input type="hidden" name="filter_grid" id="automated_filter_grid" value="<?php _e('filtered from', 'ARMember');?>"/>
				<input type="hidden" name="totalwd_grid" id="automated_totalwd_grid" value="<?php _e('total', 'ARMember');?>"/>
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
			<div class="footer_grid"></div>
		</form>
		<div class="armclear"></div>
		<?php 
		/* **********./Begin Bulk Delete Communication Popup/.********** */
		$bulk_delete_message_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to delete this message(s)?",'ARMember' ).'</span>';
		$bulk_delete_message_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_message_popup_arg = array(
			'id' => 'delete_bulk_communication_message',
			'class' => 'delete_bulk_communication_message',
			'title' => 'Delete Communication Message(s)',
			'content' => $bulk_delete_message_popup_content,
			'button_id' => 'arm_bulk_delete_message_ok_btn',
			'button_onclick' => "arm_delete_bulk_communication('true');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_message_popup_arg);
		/* **********./End Bulk Delete Communication Popup/.********** */
		?>
		<div class="armclear"></div>
	</div>
<?php endif;?>
</div>
<!--./******************** Add New Member Form ********************/.-->
<div class="add_new_message_wrapper popup_wrapper" >
	<form method="post" action="#" id="<?php echo $form_id;?>" class="arm_admin_form arm_communication_message_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_message_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Add New Response','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr class="">
							<th><?php _e('Message To Be Sent', 'ARMember');?></th>
							<td>
								<div class="arm_message_period_post">
									<input type='hidden' id='arm_message_type' class="arm_message_select_box" name="arm_message_type" value='<?php echo $msg_type;?>' />
									<dl class="arm_selectbox column_level_dd arm_width_512">
										<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
										<dd>
											<ul data-id="arm_message_type">
											<?php foreach($message_types as $type => $label):?>
												<li data-label="<?php echo $label;?>" data-value="<?php echo $type;?>"><?php echo $label;?></li>
											<?php endforeach;?>
											</ul>
										</dd>
									</dl>
								</div>
								<div class="arm_message_period_section arm_margin_top_10" >
								<span class=""><?php _e('Send Message before', 'ARMember'); ?></span>
                                                                        <div class="arm_message_periodunit_type arm_margin_left_10" >
                                                                            <input type='hidden' id="arm_message_period_unit" class="arm_message_select_box_unit" name="arm_message_period_unit" value="1" />
                                                                            <dl class="arm_selectbox column_level_dd arm_width_100">
                                                                                <dt><span id="arm_message_period_unit_span"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                                <dd>
                                                                                    <ul data-id="arm_message_period_unit">
                                                                                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                                                            <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                                        <?php } ?>
                                                                                    </ul>
                                                                                </dd>
                                                                            </dl>
                                                                        </div>
									<div class="arm_message_periodunit_type">
										<input type='hidden' id="arm_message_period_type" class="arm_message_select_box_type" name="arm_message_period_type" value="day" />
										<dl class="arm_selectbox column_level_dd arm_width_120">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_message_period_type">
													<li data-label="<?php _e('Day(s)', 'ARMember');?>" data-value="day"><?php _e('Day(s)', 'ARMember');?></li>
													<li data-label="<?php _e('Week(s)', 'ARMember');?>" data-value="week"><?php _e('Week(s)', 'ARMember');?></li>
													<li data-label="<?php _e('Month(s)', 'ARMember');?>" data-value="month"><?php _e('Month(s)', 'ARMember');?></li>
													<li data-label="<?php _e('Year(s)', 'ARMember');?>" data-value="year"><?php _e('Year(s)', 'ARMember');?></li>
												</ul>
											</dd>
										</dl>
									</div>
								</div>
                                                                <div class="arm_message_period_section_form_manual_subscription arm_margin_top_10" >
                                                                <span><?php _e('Send Message Before', 'ARMember'); ?></span>
                                    <div class="arm_message_periodunit_type arm_margin_left_10" >
                                        <input type='hidden' id="arm_message_period_unit_manual_subscription" class="arm_message_select_box_unit_manual_subscription" name="arm_message_period_unit_manual_subscription" value="1" />
                                        <dl class="arm_selectbox column_level_dd arm_width_100">
                                            <dt><span id="arm_message_period_unit_span_manual_subscription"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_unit_manual_subscription">
                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                        <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arm_message_periodunit_type">
                                        <input type='hidden' id="arm_message_period_type_manual_subscription" class="arm_message_select_box_type_manual_subscription" name="arm_message_period_type_manual_subscription" value="day" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_type_manual_subscription">
                                                    <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="day"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Week(s)', 'ARMember'); ?>" data-value="week"><?php _e('Week(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="month"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="year"><?php _e('Year(s)', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                                            <div class="arm_message_period_section_for_dripped_content arm_margin_top_10" >
                                                            <span><?php _e('Send Message Before', 'ARMember'); ?></span>
                                    <div class="arm_message_periodunit_type arm_margin_left_10" >
                                        <input type='hidden' id="arm_message_period_unit_dripped_content" class="arm_message_select_box_unit_dripped_content" name="arm_message_period_unit_dripped_content" value="1" />
                                        <dl class="arm_selectbox column_level_dd arm_width_100">
                                            <dt><span id="arm_message_period_unit_span_dripped_content"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_unit_dripped_content">
                                                    <?php for ($i = 0; $i <= 5; $i++) { ?>
                                                        <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arm_message_periodunit_type">
                                        <input type='hidden' id="arm_message_period_type_dripped_content" class="arm_message_select_box_type_dripped_content" name="arm_message_period_type_dripped_content" value="day" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_type_dripped_content">
                                                    <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="day"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Week(s)', 'ARMember'); ?>" data-value="week"><?php _e('Week(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="month"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="year"><?php _e('Year(s)', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
							</td>
						</tr>
						<tr class="arm_membership_plan_selection_row">
							<th><?php _e('Select Membership Plan', 'ARMember');?></th>
							<td>
								<select id="arm_message_subscription" class="arm_chosen_selectbox arm_width_532" data-msg-required="<?php _e('Subscription Plan Required', 'ARMember');?>" name="arm_message_subscription[]" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember');?>" multiple="multiple"  required>
								<?php $subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');?>
								<?php if (!empty($subs_data)): $c_subs = (!empty($c_subs)) ? $c_subs : array('-1');?>
									<?php foreach ($subs_data as $sd): ?>
										<option class="arm_message_selectbox_op" value="<?php echo $sd['arm_subscription_plan_id'];?>" <?php echo(in_array($sd['arm_subscription_plan_id'], $c_subs) ? 'selected="selected"' : "" );?>><?php echo stripslashes($sd['arm_subscription_plan_name']);?></option>
									<?php endforeach;?>
								<?php endif;?>
								</select>
								<div class="armclear" style="max-height: 1px;"></div>
								<span class="arm_info_text">(<?php _e('Leave blank for all plans.', 'ARMember')?>)</span>
							</td>
						</tr>
						<tr class="">
							<th><?php _e('Subject', 'ARMember');?></th>
							<td>
								<input id="arm_message_subject" type="text" data-msg-required="<?php _e('Message Subject Required', 'ARMember');?>" name="arm_message_subject" value="" >
							</td>
						</tr>
						<tr class="">
							<th><?php _e('Message', 'ARMember');?></th>
							<td>
								<div class="arm_email_content_area_left">
									<?php 
									$arm_message_editor = array('textarea_name' => 'arm_message_content',
										'editor_class' => 'arm_message_content',
										'media_buttons' => false,
										'textarea_rows' => 5,
										'default_editor' => 'html',
										'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
									);
									wp_editor('', 'arm_message_content', $arm_message_editor);
									?>
									<span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php _e('Content Cannot Be Empty.', 'ARMember');?></span>
								</div>
								<div class="arm_email_content_area_right">
									<span class="arm_sec_head"><?php _e('Template Tags', 'ARMember');?></span>
									<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Admin Email", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_ADMIN_EMAIL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Admin Email", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Blogname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGNAME}"><?php _e("Blog Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display BlogURL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGURL}" ><?php _e("Blog URL", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Login URL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_LOGIN_URL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Login URL", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Username", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNAME}" ><?php _e("Username", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User ID", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USER_ID}" ><?php _e("User ID", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip arm_communication_email_code_password_reset" title="<?php _e("Reset Password Link", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_RESET_PASSWORD_LINK}" ><?php _e("Reset Password Link", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Firstname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERFIRSTNAME}" ><?php _e("First Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Lastname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERLASTNAME}" ><?php _e("Last Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Nickname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNICENAME}" ><?php _e("Nickname", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Displayname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERDISPLAYNAME}" ><?php _e("Display Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Email Address", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_EMAIL}" ><?php _e("User Email Address", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display NetworkName", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKNAME}" ><?php _e("Network Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display NetworkURL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKURL}" ><?php _e("Network URL", 'ARMember'); ?></span>
					</div>
					

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Membership Plan Name", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONNAME}" ><?php _e("Plan Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Description", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONDESCRIPTION}" ><?php _e("Plan Description", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Expire Date", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}" ><?php _e("Plan Expire Date", 'ARMember'); ?></span>
					</div>
                                        <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription Next Due Date", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}" ><?php _e("Plan Next Due Date", 'ARMember'); ?></span>
					</div>
					
                    <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Amount", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}" ><?php _e("Plan Amount", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Code", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_CODE}" ><?php _e("Coupon Code", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Discount", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_DISCOUNT}" ><?php _e("Coupon Discount", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Trial Amount", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRIAL_AMOUNT}" ><?php _e("Trial Amount", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Tax Percentage", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_PERCENTAGE}" ><?php _e("Tax Percentage", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Tax Amount", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_AMOUNT}" ><?php _e("Tax Amount", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Final Payable Amount", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYABLE_AMOUNT}" ><?php _e("Payable Amount", 'ARMember'); ?></span>
					</div>




					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Currency", 'ARMember'); ?>">
                                            <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_CURRENCY}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Currency', 'ARMember'); ?> </span>
                                            
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Type", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_TYPE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Type", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Gateway", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_GATEWAY}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Gateway", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Transaction ID", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRANSACTION_ID}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Transaction ID", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Date", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_DATE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Date", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Profile Link", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("User Profile Link", 'ARMember'); ?></span>
					</div>




					<div class="arm_shortcode_row armhelptip" title="<?php echo __("To Display User's meta field value.", 'ARMember') . ' (' . __("Where", 'ARMember') . ' `meta_key` ' . __("is meta field name.", 'ARMember') . ')'; ?>">
						<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_USERMETA_meta_key}"><?php _e("User Meta Key", 'ARMember');?></span>
					</div>

					<?php
						$arm_other_custom_shortcode_arr = array();
						$arm_other_custom_shortcode_arr = apply_filters('arm_email_notification_shortcodes_outside', $arm_other_custom_shortcode_arr);
						if(count($arm_other_custom_shortcode_arr)>0)
						{
							foreach ($arm_other_custom_shortcode_arr as $arm_other_custom_shortcode_key => $arm_other_custom_shortcode_value) {
								if(is_array($arm_other_custom_shortcode_value))
								{
									$arm_en_title_on_hover = isset($arm_other_custom_shortcode_value['title_on_hover']) ? $arm_other_custom_shortcode_value['title_on_hover'] : '';
									$arm_en_shortcode = isset($arm_other_custom_shortcode_value['shortcode']) ? $arm_other_custom_shortcode_value['shortcode'] : '';
									$arm_en_shortcode_label = isset($arm_other_custom_shortcode_value['shortcode_label']) ? $arm_other_custom_shortcode_value['shortcode_label'] : '';
									$arm_en_shortcode_class = isset($arm_other_custom_shortcode_value['shortcode_class']) ? ' '.$arm_other_custom_shortcode_value['shortcode_class'].' ' : '';

									echo '<div class="arm_shortcode_row armhelptip'.$arm_en_shortcode_class.'" title="'.$arm_en_title_on_hover.'">';
										echo '<span class="arm_variable_code arm_communication_email_code" data-code="'.$arm_en_shortcode.'">'.$arm_en_shortcode_label.'</span>';
									echo '</div>';
								}
							}
							
						}
						
					?>
									</div>
								</div>
                            </td>
                        </tr>

                        <tr>
                        	<th></th>
                        	<td>
                        		<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); ?></span>
							</td>
						</tr>
                                                <tr>
                                                    <th></th>
                                                    <td>
                                                        <div class="arm_email_content_area_left">
                                                            <div class="arm_send_one_copy_to_admin_div arm_float_left arm_width_280" >
                                                                <?php _e('Send email to admin for this event', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_send_one_copy_to_admin_right arm_float_left" style="margin-top: 3px; ">
                                                                <div class="armswitch">
										<input type="checkbox" class="armswitch_input arm_email_send_to_admin" id="arm_email_send_to_admin" name="arm_email_send_to_admin">
										<label class="armswitch_label" for="arm_email_send_to_admin"></label>
										<span class="arm_status_loader_img"></span>
									</div>
                                                        </div>
                                                            </div>
                                                        
                                                    </td>
                                                </tr>
                                                <tr class="arm_seperate_email_content_for_admin_switch hidden_section">
                                                    <th></th>
                                                    <td>
                                                        <div class="arm_email_content_area_left">
                                                            <div class="arm_send_one_copy_to_admin_div arm_float_left arm_width_280" >
                                                                <?php _e('Set different email content for admin?', 'ARMember'); ?>
                                                            </div>
                                                            <div class="arm_send_one_copy_to_admin_right arm_float_left" style="margin-top: 3px;">
                                                                <div class="armswitch">
										<input type="checkbox" class="armswitch_input arm_email_different_content_for_admin" id="arm_email_different_content_for_admin" name="arm_email_different_content_for_admin">
										<label class="armswitch_label" for="arm_email_different_content_for_admin"></label>
										<span class="arm_status_loader_img"></span>
									</div>
                                                        </div>
                                                            </div>
                                                        
                                                    </td>
                                                </tr>
                                                <tr class="arm_seperate_email_content_for_admin hidden_section" >
							<th><?php _e('Message For Admin', 'ARMember');?></th>
							<td>
								<div class="arm_email_content_area_left">
									<?php 
									$arm_admin_message_editor = array('textarea_name' => 'arm_admin_message_content',
										'editor_class' => 'arm_admin_message_content',
										'media_buttons' => false,
										'textarea_rows' => 5,
										'default_editor' => 'html',
										'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
									);
									wp_editor('', 'arm_admin_message_content', $arm_admin_message_editor);
									?>
									<span id="arm_comm_wp_validate_admin_msg" class="error" style="display:none;"><?php _e('Message for admin Cannot Be Empty.', 'ARMember');?></span>
								</div>
								<div class="arm_email_content_area_right">
									<span class="arm_sec_head"><?php _e('Template Tags', 'ARMember');?></span>
									<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Admin Email", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_ADMIN_EMAIL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Admin Email", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Blogname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_BLOGNAME}"><?php _e("Blog Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display BlogURL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_BLOGURL}" ><?php _e("Blog URL", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Login URL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_LOGIN_URL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Login URL", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Username", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERNAME}" ><?php _e("Username", 'ARMember'); ?></span>
					</div>
								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User ID", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USER_ID}" ><?php _e("User ID", 'ARMember'); ?></span>
								</div>
					<div class="arm_shortcode_row armhelptip arm_communication_email_code_password_reset" title="<?php _e("Reset Password Link", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_RESET_PASSWORD_LINK}" ><?php _e("Reset Password Link", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Firstname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERFIRSTNAME}" ><?php _e("First Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Lastname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERLASTNAME}" ><?php _e("Last Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Nickname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERNICENAME}" ><?php _e("Nickname", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Displayname", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERDISPLAYNAME}" ><?php _e("Display Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Email Address", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_EMAIL}" ><?php _e("User Email Address", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display NetworkName", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_NETWORKNAME}" ><?php _e("Network Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display NetworkURL", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_NETWORKURL}" ><?php _e("Network URL", 'ARMember'); ?></span>
					</div>


					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription Name", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONNAME}" ><?php _e("Plan Name", 'ARMember'); ?></span>
					</div>
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription Expire Date", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}" ><?php _e("Plan Expire Date", 'ARMember'); ?></span>
					</div>
								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription Next Due Date", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}" ><?php _e("Plan Next Due Date", 'ARMember'); ?></span>
								</div>

								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription Amount", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}" ><?php _e("Plan Amount", 'ARMember'); ?></span>
								</div>

								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Code", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_CODE}" ><?php _e("Coupon Code", 'ARMember'); ?></span>
								</div>

								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Discount", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_COUPON_DISCOUNT}" ><?php _e("Coupon Discount", 'ARMember'); ?></span>
								</div>
								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Trial Amount", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_TRIAL_AMOUNT}" ><?php _e("Trial Amount", 'ARMember'); ?></span>
								</div>

								<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Final Payable Amount", 'ARMember'); ?>">
								    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYABLE_AMOUNT}" ><?php _e("Payable Amount", 'ARMember'); ?></span>
								</div>



								
								

								
								
					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Currency", 'ARMember'); ?>">
                                            <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_CURRENCY}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Currency', 'ARMember'); ?> </span>
                                            
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Type", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_TYPE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Type", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Gateway", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_GATEWAY}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Gateway", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Transaction ID", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_TRANSACTION_ID}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Transaction ID", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Date", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_DATE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("Payment Date", 'ARMember'); ?></span>
					</div>

					<div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Profile Link", 'ARMember'); ?>">
					    <span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e("User Profile Link", 'ARMember'); ?></span>
					</div>

				

					

					<div class="arm_shortcode_row armhelptip" title="<?php echo __("To Display User's meta field value.", 'ARMember') . ' (' . __("Where", 'ARMember') . ' `meta_key` ' . __("is meta field name.", 'ARMember') . ')'; ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_USERMETA_meta_key}"><?php _e("User Meta Key", 'ARMember');?></span>
										</div>

										<?php
											$arm_other_custom_shortcode_arr = array();
											$arm_other_custom_shortcode_arr = apply_filters('arm_admin_email_notification_shortcodes_outside', $arm_other_custom_shortcode_arr);
											if(count($arm_other_custom_shortcode_arr)>0)
											{
												foreach ($arm_other_custom_shortcode_arr as $arm_other_custom_shortcode_key => $arm_other_custom_shortcode_value) {
													if(is_array($arm_other_custom_shortcode_value))
													{
														$arm_en_title_on_hover = isset($arm_other_custom_shortcode_value['title_on_hover']) ? $arm_other_custom_shortcode_value['title_on_hover'] : '';
														$arm_en_shortcode = isset($arm_other_custom_shortcode_value['shortcode']) ? $arm_other_custom_shortcode_value['shortcode'] : '';
														$arm_en_shortcode_label = isset($arm_other_custom_shortcode_value['shortcode_label']) ? $arm_other_custom_shortcode_value['shortcode_label'] : '';
														$arm_en_shortcode_class = isset($arm_other_custom_shortcode_value['shortcode_class']) ? ' '.$arm_other_custom_shortcode_value['shortcode_class'].' ' : '';

														echo '<div class="arm_shortcode_row armhelptip'.$arm_en_shortcode_class.'" title="'.$arm_en_title_on_hover.'">';
															echo '<span class="arm_variable_code arm_admin_communication_email_code" data-code="'.$arm_en_shortcode.'">'.$arm_en_shortcode_label.'</span>';
														echo '</div>';
													}
												}
												
											}
										?>
									</div>        
								</div>
							</td>
						</tr>
						<tr class="arm_seperate_email_content_for_admin hidden_section">
                        	<th></th>
                        	<td>
                        		<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); ?></span>
							</td>
						</tr>
						<?php 
						$arm_automated_field_html='';
						$arm_automated_field_html=apply_filters('arm_add_automated_email_template_field_html',$arm_automated_field_html);
						echo $arm_automated_field_html;
						?>
						<input type="hidden" id="arm_message_status" name="arm_message_status" value="1"/>
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<input type="hidden" id="arm_message_id_box" name="edit_id" value="<?php echo $mid;?>" />
						<button class="arm_save_btn arm_button_manage_message" type="submit" data-type="add"><?php _e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_new_message_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<div class="add_edit_message_wrapper_container"></div>
<div class="edit_email_template_wrapper popup_wrapper" >
	<form method="post" id="arm_edit_email_temp_frm" class="arm_admin_form arm_responses_message_wrapper_frm" action="#" onsubmit="return false;">
		<input type='hidden' name="arm_template_id" id="arm_template_id" value="0"/>
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="edit_template_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Edit Email Template','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr class="">
							<th><?php _e('Subject', 'ARMember'); ?></th>
							<td>
								<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php _e('Email Subject Required.', 'ARMember');?>"/>
							</td>
						</tr>
						<tr class="form-field">
							<th><?php _e('Message', 'ARMember'); ?></th>
							<td>
								<div class="arm_email_content_area_left">
								<?php 
								$email_setting_editor = array(
									'textarea_name' => 'arm_template_content',
									'editor_class' => 'arm_message_content',
									'media_buttons' => false,
									'textarea_rows' => 5,
									'default_editor' => 'html',
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
								);
								wp_editor('', 'arm_template_content', $email_setting_editor);
								?>
									<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php _e('Content Cannot Be Empty.', 'ARMember');?></span>
								</div>
								<div class="arm_email_content_area_right">
									<span class="arm_sec_head"><?php _e('Template Tags', 'ARMember');?></span>
									<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Admin Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the admin email that users can contact you at. You can configure it under Mail settings.", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Blog Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays blog name', 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Blog URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays blog URL', 'ARMember'); ?>"></i>
										</div>
										<!--									<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_ADMIN}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Blog Admin', 'ARMember'); ?></span><i class="arm_email_helptip_icon fa fa-question-circle" title="<?php _e('Displays blog WP-admin URL', 'ARMember'); ?>"></i>
										</div>-->
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Login URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the ARM login page', 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Username', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the Username of user', 'ARMember'); ?>"></i>
										</div>
                                        					<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('User ID', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the User ID of user', 'ARMember'); ?>"></i>
										</div>
                                        					<div class="arm_shortcode_row arm_email_code_reset_password">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Reset Password Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the Reset Password Link for user', 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('First Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the user first name', 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Last Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the user last name', 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Display Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the user display name or public name", 'ARMember'); ?>"></i>
										</div>                                        
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the E-mail address of user", 'ARMember'); ?>"></i>
										</div>                                        
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('User Profile Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the User Profile address", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Validation URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("The account validation URL that user receives after signing up (If you enable e-mail validation feature)", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('User Meta Key', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo __("To Display User's meta field value.", 'ARMember').' ('.__("Where", 'ARMember').' `meta_key` '.__("is meta field name.", 'ARMember').')';?>"></i>
										</div>
										
										<div class="arm_shortcode_row arm_email_code_plan_name">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Plan Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the plan name of user', 'ARMember'); ?>"></i>
										</div>	
										<div class="arm_shortcode_row arm_email_code_plan_desc">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Plan Description', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e('Displays the plan description of user', 'ARMember'); ?>"></i>
										</div>	

										<div class="arm_shortcode_row arm_email_code_plan_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Plan Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the plan amount of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_plan_discount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_COUPON_CODE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Coupon Code', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the used coupon code by user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_plan_discount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DISCOUNT}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Coupon Discount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the plan discount of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_trial_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Trial Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the trial amount of plan", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_tax_percentage">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_PERCENTAGE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Tax Percentage', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays tax percentage", 'ARMember'); ?>"></i>
										</div>

										<div class="arm_shortcode_row arm_email_code_tax_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_AMOUNT}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Tax Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays tax amount", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payable_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Payable Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the Final Payable Amount of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payment_type">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Payment Type', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the payment type of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payment_gateway">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Payment Gateway', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the payment gateway of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_transaction_id">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Transaction Id', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the payment transaction Id of user", 'ARMember'); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payment_date">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_DATE}" title="<?php _e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php _e('Payment Date', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php _e("Displays the payment date of user", 'ARMember'); ?>"></i>
										</div>
                                                                                <?php do_action("arm_email_notification_template_shortcode"); ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
                        	<th></th>
                        	<td>	
                        		<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); ?></span>
							</td>
						</tr>
						
           				<?php 
						$arm_field_html='';
						$arm_field_html=apply_filters('arm_add_standard_email_template_field_html',$arm_field_html);
						echo $arm_field_html;
						?>
					</table>
					<input type=hidden name="arm_template_status" id="arm_template_status" value=""/>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_temp" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<button class="arm_save_btn" id="arm_email_template_submit" type="submit"><?php _e('Save', 'ARMember');?></button>
						<button class="arm_cancel_btn edit_template_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<script type="text/javascript">
    __ARM_ADDNEWRESPONSE = '<?php _e('Add New Response','ARMember'); ?>';
    __ARM_VALUE = '<?php _e('Value','ARMember'); ?>';
</script>