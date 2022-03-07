<?php
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_drip_rules;
$dripRulesMembers = array();
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
$date_format = $arm_global_settings->arm_get_wp_date_format();
$drip_types = $arm_drip_rules->arm_drip_rule_types();

$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
$filter_dctype = (!empty($_POST['dctype'])) ? $_POST['dctype'] : '0';
$filter_plan_id = (!empty($_POST['plan_id']) && $_POST['plan_id'] != '0') ? $_POST['plan_id'] : '';
$filter_drip_type = (!empty($_POST['drip_type']) && $_POST['drip_type'] != '0') ? $_POST['drip_type'] : '0';

/* Custom Post Types */
$custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
$dripContentTypes = array('page' => __('Page', 'ARMember'), 'post' => __('Post', 'ARMember'));
if (!empty($custom_post_types)) {
	foreach ($custom_post_types as $cpt) {
		$dripContentTypes[$cpt->name] = $cpt->label;
	}
}
/* Add `Custom Content` Option */
$dripContentTypes['custom_content'] = __('Custom Content', 'ARMember');
?>
<script type="text/javascript">
// <![CDATA[
jQuery(document).ready( function ($) {
   arm_load_drip_rules_list_grid();
    
});

function arm_load_drip_rules_list_filtered_grid(data)
{
    var tbl = jQuery('#armember_datatable').dataTable(); 
        
        tbl.fnDeleteRow(data);
      
        jQuery('#armember_datatable').dataTable().fnDestroy();
        arm_load_drip_rules_list_grid();
}
function arm_load_drip_rules_list_grid() {


	var __ARM_Showing = '<?php echo addslashes(__('Showing','ARMember')); ?>';
    var __ARM_Showing_empty = '<?php echo addslashes(__('Showing 0 to 0 of 0 entries','ARMember')); ?>';
    var __ARM_to = '<?php echo addslashes(__('to','ARMember')); ?>';
    var __ARM_of = '<?php echo addslashes(__('of','ARMember')); ?>';
    var __ARM_RECORDS = '<?php echo addslashes(__('entries','ARMember')); ?>';
    var __ARM_Show = '<?php echo addslashes(__('Show','ARMember')); ?>';
    var __ARM_NO_FOUND = '<?php echo addslashes(__('No any record found.','ARMember')); ?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(__('No matching records found.','ARMember')); ?>';

	var oTables = jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
        "oLanguage": {
            "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_RECORDS,
            "sInfoEmpty": __ARM_Showing_empty,
            "sInfoFiltered": "(_FILTERES_FROM_ _MAX_ _TOTALWD__ " + __ARM_RECORDS + ")",
            "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_RECORDS,
            "sEmptyTable": __ARM_NO_FOUND,
            "sZeroRecords": __ARM_NO_MATCHING
          },
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aoColumnDefs": [
			{ "sType": "html", "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [ 0, 1, 7] }
		],
		"aLengthMenu": [10, 25, 50, 100, 150, 200],
		"fixedColumns": false,
		"bStateSave": true,
		"iCookieDuration": 60*60,
		"sCookiePrefix": "arm_datatable_",
		"fnStateSave": function (oSettings, oData) {
			oData.aaSorting = [];
			oData.abVisCols = [];
			oData.aoSearchCols = [];
			this.oApi._fnCreateCookie(
				oSettings.sCookiePrefix+oSettings.sInstance, 
				this.oApi._fnJsonString(oData), 
				oSettings.iCookieDuration, 
				oSettings.sCookiePrefix, 
				oSettings.fnCookieCallback
			);
		},
		"fnStateLoadParams": function (oSettings, oData) {
			oData.iLength = 10;
			//oData.oSearch.sSearch = "<?php echo $filter_search;?>";
		},
		"fnPreDrawCallback": function () {
            jQuery('.arm_loading_grid').show();
        },
		"fnDrawCallback":function(){
			setTimeout(function(){
				jQuery('.arm_loading_grid').hide();
				arm_show_data();
			}, 1000);
			jQuery(".cb-select-all-th").removeClass('sorting_asc');
            jQuery("#cb-select-all-1").prop("checked", false);
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
	var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('div#armember_datatable_filter').hide();
	jQuery('#arm_filter_wrapper').remove();
	jQuery('#armmanagesearch_new_drip').on('keyup', function (e) {
		e.stopPropagation();
		if (e.keyCode == 13) {
			var plan_id = jQuery('#arm_filter_dplan_id').val();
			var dctype = jQuery('#arm_filter_dctype').val();
			var search = jQuery('#armmanagesearch_new_drip').val();
			arm_reload_drip_rule_list(search, plan_id, dctype, '');
			return false;
		}
	});
        }
// ]]>
</script>
<?php if (!empty($all_plans)) { ?>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_drip_rule_bulk_action" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_200">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_drip_rule_bulk_action">
								<li data-label="<?php _e('Bulk Actions','ARMember');?>" data-value="-1"><?php _e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php _e('Delete', 'ARMember');?>" data-value="delete_drip_rule"><?php _e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go','ARMember');?>"/>
			</div>
		</div>
	<div class="arm_drip_rule_list">
		<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_drip_rule_bulk_action" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_200">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_drip_rule_bulk_action">
								<li data-label="<?php _e('Bulk Actions','ARMember');?>" data-value="-1"><?php _e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php _e('Delete', 'ARMember');?>" data-value="delete_drip_rule"><?php _e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go','ARMember');?>"/>
			</div>
		</div>
		<form method="GET" id="drip_rule_list_form" class="data_grid_list drip_rule_list_form" onsubmit="return apply_bulk_action_drip_list();">
			<input type="hidden" name="page" value="<?php echo $arm_slugs->drip_rules;?>" />
			<input type="hidden" name="armaction" value="list" />
			<div class="arm_datatable_filters">
				<div class="arm_dt_filter_block arm_datatable_searchbox">
					<label><input type="text" placeholder="<?php _e('Search', 'ARMember');?>" id="armmanagesearch_new_drip" value="<?php echo $filter_search;?>" tabindex="-1"></label>
					<!--./====================Begin Filter By Content Type Box====================/.-->
					<div class="arm_datatable_filter_item arm_filter_dctype_label">
						<input type="hidden" id="arm_filter_dctype" class="arm_filter_dctype" value="<?php echo $filter_dctype;?>" />
						<dl class="arm_selectbox column_level_dd arm_width_200">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_dctype">
									<li data-label="<?php _e('Select Content Type','ARMember');?>" data-value="0"><?php _e('Select Content Type','ARMember');?></li>
									<?php 
									if (!empty($dripContentTypes)) {
										foreach ($dripContentTypes as $key => $val) {
											?><li data-label="<?php echo $val;?>" data-value="<?php echo $key;?>"><?php echo $val;?></li><?php
										}
									}
									?>
								</ul>
							</dd>
						</dl>
					</div>
					<!--./====================End Filter By Content Type Box====================/.-->
					<!--./====================Begin Filter By Plan Box====================/.-->
					<?php if (!empty($all_plans)): ?>
					<div class="arm_datatable_filter_item arm_filter_plan_id_label">
						<input type="hidden" id="arm_filter_dplan_id" class="arm_filter_dplan_id" value="<?php echo $filter_plan_id;?>" />
						<dl class="arm_multiple_selectbox arm_width_200">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_dplan_id" data-placeholder="<?php _e('Select Plans', 'ARMember');?>">
									<?php foreach ($all_plans as $plan): ?>
									<li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); ?>" data-value="<?php echo $plan['arm_subscription_plan_id']; ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo $plan['arm_subscription_plan_id'];?>"/><?php echo stripslashes($plan['arm_subscription_plan_name']); ?></li>
									<?php endforeach;?>
								</ul>
							</dd>
						</dl>
					</div>
					<?php endif;?>
					<!--./====================End Filter By Plan Box====================/.-->
					<!--./====================Begin Filter By Drip Type====================/.-->
					<div class="arm_datatable_filter_item arm_filter_drip_type_label" style="">
						<input type="hidden" id="arm_filter_drip_type" class="arm_filter_drip_type" value="<?php echo $filter_drip_type;?>" />
						<dl class="arm_selectbox column_level_dd arm_width_230">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_drip_type">
									<li data-label="<?php _e('Select Drip Type','ARMember');?>" data-value="0"><?php _e('Select Drip Type','ARMember');?></li>
									<?php 
									if (!empty($dripContentTypes)) {
										foreach ($drip_types as $key => $val) {
											?><li data-label="<?php echo $val;?>" data-value="<?php echo $key;?>"><?php echo $val;?></li><?php
										}
									}
									?>
								</ul>
							</dd>
						</dl>
					</div>
					<!--./====================End Filter By Drip Type====================/.-->
				</div>
				<div class="arm_dt_filter_block arm_dt_filter_submit">
					<input type="button" class="armemailaddbtn" id="arm_drip_rule_grid_filter_btn" value="<?php _e('Apply','ARMember');?>"/>
				</div>
				<div class="armclear"></div>
			</div>
			<div id="armmainformnewlist" class="arm_filter_grid_list_container">
				<div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/loader.gif" alt="Loading.."></div>
				<div class="response_messages"></div>
				<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable">
					<thead>
						<tr>
							<th class="center cb-select-all-th arm_max_width_60" ><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
							<th class="arm_width_100"><?php _e('Enable','ARMember');?></th>
							<th class="arm_width_120"><?php _e('Content Type','ARMember');?></th>
							<th class="arm_width_150"><?php _e('Page/Post Name','ARMember');?></th>
							<th><?php _e('Drip Type', 'ARMember'); ?></th>
							<th><?php _e('Shortcode','ARMember');?></th>
							<th><?php _e('Plans','ARMember');?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody id="arm_drip_rules_wrapper">
					<?php 
					$all_drip_rules = $arm_drip_rules->arm_get_drip_rules();
					$where_dr = "WHERE 1=1";
					$join_clause = "";
					$group_by_clause = "";
                                        
                                        if(!empty($filter_search)){
                                        	$join_clause .= " LEFT JOIN ".$wpdb->posts." po ON po.ID=dr.arm_item_id ";
                                        	$group_by_clause .= " GROUP BY dr.arm_item_id ";
                                        	$where_dr .= " AND po.post_title LIKE '%$filter_search%' ";
                                        }
                                        
					if (!empty($filter_dctype) && $filter_dctype != '0') {
						$where_dr .= " AND dr.arm_item_type='$filter_dctype'";
					}
					if (!empty($filter_drip_type) && $filter_drip_type != '0') {
						$where_dr .= " AND dr.arm_rule_type='$filter_drip_type'";
					}
					if (!empty($filter_plan_id)) {
						$filterPlanArr = explode(',', $filter_plan_id);
						if (!empty($filterPlanArr) && !in_array('0', $filterPlanArr) && !in_array('no_plan', $filterPlanArr)) {
							foreach ($filterPlanArr as $pid) {
								$findInSet[] = " FIND_IN_SET($pid, dr.arm_rule_plans) ";
							}
							$findInSet = implode(' OR ', $findInSet);
							$where_dr .= " AND ($findInSet)";
						}
					}
					$all_drip_rules = $wpdb->get_results("SELECT * FROM ".$ARMember->tbl_arm_drip_rules." dr {$join_clause}{$where_dr}{$group_by_clause} ORDER BY dr.arm_rule_id DESC", ARRAY_A);
					if (!empty($all_drip_rules)) {
						foreach ($all_drip_rules as $dr) {
							$ruleID = $dr['arm_rule_id'];
							$dr['rule_options'] = maybe_unserialize($dr['arm_rule_options']);
							?>
							<tr class="arm_drip_rules_tr_<?php echo $ruleID;?> row_<?php echo $ruleID;?>">
								<td class="center">
									<input class="chkstanard arm_bulk_select_single" type="checkbox" value="<?php echo $ruleID; ?>" name="item-action[]">
								</td>
								<td class="center arm_min_width_80" ><?php 
									$switchChecked = ($dr['arm_rule_status'] == '1') ? 'checked="checked"' : '';
									echo '<div class="armswitch">
										<input type="checkbox" class="armswitch_input arm_drip_rule_status_action" id="arm_drip_rule_status_input_'.$ruleID.'" value="1" data-item_id="'.$ruleID.'" '.$switchChecked.'>
										<label class="armswitch_label" for="arm_drip_rule_status_input_'.$ruleID.'"></label>
										<span class="arm_status_loader_img"></span>
									</div>';
									?>
								</td>
								<td><?php 
								if(isset($dripContentTypes[$dr['arm_item_type']])){
									$dripcontenttype = $dripContentTypes[$dr['arm_item_type']];
									echo $dripContentTypes[$dr['arm_item_type']];
								} else {
									$dripcontenttype = $dr['arm_item_type'];
									echo $dr['arm_item_type'];
								}
								?></td>
								<td><?php 
								$item_title = '-';
								if($dr['arm_item_type'] != 'custom_content') {
									if (!empty($dr['arm_item_id']) && $dr['arm_item_id'] != 0) {

                                                                            
                                    if($dr['arm_item_type'] == 'reply')
                                    {
                                        $item_title = get_the_title($dr['arm_item_id'])." (<i>#".$dr['arm_item_id']."</i>)";
                                    }
                                    else
                                    {
                                    	
                                    		$item_title = get_the_title($dr['arm_item_id']);
	                                    

                                    }
									}
									else{
										
                                    		$item_title = __('All '.$dripcontenttype.'s', 'ARMember');
                                    	
                                    	
									}
								}
								echo $item_title;
								?></td>
								<td><?php 
								$rule_type = isset($dr['arm_rule_type']) ? $dr['arm_rule_type'] : '';
								$rule_type_text = '--';
								switch ($rule_type) {
									case 'instant':
										$rule_type_text = __('Immediately', 'ARMember');
										break;
									case 'days':
										$days = isset($dr['rule_options']['days']) ? $dr['rule_options']['days'] : 0;
										$rule_type_text = __('After', 'ARMember') . ' ' . $days . ' ' . __('day(s) of subscription', 'ARMember');
										break;
									case 'post_publish':
										$post_publish = isset($dr['rule_options']['post_publish']) ? $dr['rule_options']['post_publish'] : 0;
										$rule_type_text = __('After', 'ARMember') . ' ' . $post_publish . ' ' . __('day(s) of post is published', 'ARMember');
										break;
									case 'post_modify':
										$post_modify = isset($dr['rule_options']['post_modify']) ? $dr['rule_options']['post_modify'] : 0;
										$rule_type_text = __('After', 'ARMember') . ' ' . $post_modify . ' ' . __('day(s) of post is last modified', 'ARMember');
										break;
									case 'dates':
										$rule_type_text = __('On specific date', 'ARMember');
										$from_date = isset($dr['rule_options']['from_date']) ? $dr['rule_options']['from_date'] : '';
										$to_date = isset($dr['rule_options']['to_date']) ? $dr['rule_options']['to_date'] : '';
										if (!empty($from_date)) {
											$rule_type_text .= '<br/>';
											$rule_type_text .= __('From', 'ARMember') . ': ' . $from_date;
										}
										if (!empty($to_date)) {
											$rule_type_text .= ' '.__('To', 'ARMember') . ': ' . $to_date;
										}
										break;
									default:
										break;
								}
								echo apply_filters('arm_change_drip_content_in_admin', $rule_type_text, $dr);
								?></td>
								<td>
									<?php 
									$item_title = "-";
									if($dr['arm_item_type'] == 'custom_content'){
										$shortCode = "[arm_drip_content id='{$ruleID}']".__('Put Your Drip Content Here.', 'ARMember')."[/arm_drip_content]";
										$item_title = '<div class="arm_shortcode_text arm_form_shortcode_box arm_drip_shortcode_box">
											<span class="armCopyText">'.esc_attr($shortCode).'</span>
											<span class="arm_click_to_copy_text" data-code="'.esc_attr($shortCode).'">'.__('Click to copy', 'ARMember').'</span>
											<span class="arm_copied_text"><img src="'.MEMBERSHIP_IMAGES_URL.'/copied_ok.png" alt="ok"/>'.__('Code Copied', 'ARMember').'</span>';
									}
									echo $item_title;
									?>
								</td>
								<td><?php 
								$subs_plan_title = '--';
								if (!empty($dr['arm_rule_plans'])) {
									$plans_id = @explode(',', $dr['arm_rule_plans']);
									$subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
									$subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '--';
								}
								echo $subs_plan_title;
								?></td>
								<td class="armGridActionTD"><?php
								
									$gridAction = "<div class='arm_grid_action_btn_container'>";
									$gridAction .= "<a class='arm_drip_members_list_detail' href='javascript:void(0);' data-list_id='{$ruleID}' data-list_type='drip'><img src='".MEMBERSHIP_IMAGES_URL."/grid_preview.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_preview_hover.png';\" class='armhelptip' title='".__('View Members','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_preview.png';\" /></a>";
									$gridAction .= "<a class='arm_edit_drip_rule_btn' href='javascript:void(0);' data-rule_id='{$ruleID}'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".__('Edit Rule','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";
									$gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$ruleID});'><img src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png' class='armhelptip' title='".__('Delete','ARMember')."' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete_hover.png';\" onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png';\" /></a>";
								$gridAction .= $arm_global_settings->arm_get_confirm_box($ruleID, __("Are you sure you want to delete this rule?", 'ARMember'), 'arm_drip_rule_delete_btn');
									$gridAction .= "</div>";
									echo '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>';
								?></td>
							</tr>
							<?php 
						}
					}
					?>
					</tbody>
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search','ARMember');?>"/>
				<input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('rules','ARMember');?>"/>
				<input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show','ARMember');?>"/>
				<input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing','ARMember');?>"/>
				<input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to','ARMember');?>"/>
				<input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of','ARMember');?>"/>
				<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching rule found','ARMember');?>"/>
				<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('No any rule found.','ARMember');?>"/>
				<input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from','ARMember');?>"/>
				<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total','ARMember');?>"/>
			 </div>
			 <div class="footer_grid"></div>
		</form>
	</div>
<?php 
} else {
	?>
<h4 class="arm_no_access_rules_message"><?php _e('There is no any plan configured yet', 'ARMember'); ?>, <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->manage_plans . '&action=new'); ?>" class="arm_ref_info_links" target="_blank"><?php _e('Please add new plan.', 'ARMember'); ?></a></h4>
	<?php
}
?>
<script type="text/javascript">
    __ARM_Showing = '<?php _e('Showing','ARMember'); ?>';
    __ARM_Showing_empty = '<?php _e('Showing 0 to 0 of 0 members','ARMember'); ?>';
    __ARM_to = '<?php _e('to','ARMember'); ?>';
    __ARM_of = '<?php _e('of','ARMember'); ?>';
    __ARM_members = '<?php _e('members','ARMember'); ?>';
    __ARM_Show = '<?php _e('Show','ARMember'); ?>';
    __ARM_NO_FOUNT = '<?php _e('No any member found.','ARMember'); ?>';
    __ARM_NO_MATCHING = '<?php _e('No matching members found.','ARMember'); ?>';
</script>
<?php
	echo $ARMember->arm_get_need_help_html_content('manage-drip-rules');
?>