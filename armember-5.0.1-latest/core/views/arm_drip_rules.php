<?php
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_drip_rules;
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');

?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_drip_content_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_drip_content_container" id="content_wrapper">
		<div class="page_title">
			<?php _e('Drip Content','ARMember');
                        if(!empty($all_plans))
                        {
                        ?>
                    
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_drip_rule_link" href="javascrip:void(0)"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add Rule', 'ARMember') ?></span></a>
			</div>
                        <?php } ?>
                        
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div id="arm_drip_rules_grid_container" class="arm_drip_rules_grid_container">
			<?php 
			if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules_list_records.php')) {
				include( MEMBERSHIP_VIEWS_DIR.'/arm_drip_rules_list_records.php');
			}
			?>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php 
/* **********./Begin Bulk Delete Drip Rules Popup/.********** */
$bulk_delete_drip_rules_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to delete this rule(s)?",'ARMember' ).'</span>';
$bulk_delete_drip_rules_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
$bulk_delete_drip_rules_popup_arg = array(
	'id' => 'delete_bulk_drip_rules_message',
	'class' => 'delete_bulk_drip_rules_message',
	'title' => 'Delete Rule(s)',
	'content' => $bulk_delete_drip_rules_popup_content,
	'button_id' => 'arm_bulk_delete_drip_rules_ok_btn',
	'button_onclick' => "arm_delete_bulk_drip_rules('true');",
);
echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_drip_rules_popup_arg);
/* **********./End Bulk Delete Drip Rules Popup/.********** */
?>
<!--./******************** Add New Drip Rule Form ********************/.-->
<div class="arm_drip_rule_items_list_container" id="arm_drip_rule_items_list_container"></div>
<div class="arm_add_new_drip_rule_wrapper popup_wrapper" >
	<form method="post" action="#" id="arm_add_new_drip_rule_wrapper_frm" class="arm_admin_form arm_add_new_drip_rule_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_drip_rule_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Add New Drip Rule','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr>
							<th><?php _e('Content Type', 'ARMember');?></th>
							<td>
								<input type="hidden" id="arm_add_rule_item_type" class="arm_rule_item_type_input" name="item_type" data-type="" value="page"/>
								<dl class="arm_selectbox column_level_dd arm_width_100_pct">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_add_rule_item_type">
											<?php 
											if (!empty($dripContentTypes)) {
												foreach ($dripContentTypes as $key => $val) {
													?><li data-label="<?php echo $val;?>" data-value="<?php echo $key;?>" data-type="<?php echo $val;?>"><?php echo $val;?></li><?php
												}
											}
											?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr class="arm_drip_post_type_opts">
							<th><?php _e('Select', 'ARMember');?> <span class="arm_rule_item_type_text"><?php _e('Page', 'ARMember')?></span></th>
							<td class="arm_required_wrapper">
								<div class="arm_text_align_center arm_width_100_pct" >
								<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_drip_rule_items" class="arm_loader_img_drip_rule_items" style="display: none;" width="20" height="20" />
								</div>
								<input id="arm_drip_rule_items_input" type="text" value="" placeholder="<?php _e('Search by title...', 'ARMember');?>" required data-msg-required="<?php _e('Please select atleast one page/post.', 'ARMember');?>" class="arm_max_width_100_pct arm_width_100_pct">
								<div class="arm_drip_rule_items arm_required_wrapper" id="arm_drip_rule_items" style="display: none;"></div>
							</td>
						</tr>
						<tr class="arm_drip_custom_content_opts" style="display:none;">
							<th><?php _e('Shortcode', 'ARMember');?></th>
							<td>
								<div class="arm_drip_custom_content_shortcode">
									<pre>[arm_drip_content id='xx']</pre>
									<pre>    <?php _e('Put Your Drip Content Here.', 'ARMember');?></pre>
									<pre>[/arm_drip_content]</pre>
								</div>
							</td>
						</tr>
						<tr>
							<th><?php _e('Membership Plans', 'ARMember');?></th>
							<td class="arm_required_wrapper">
								<select id="arm_drip_rule_plans" class="arm_chosen_selectbox arm_width_500" data-msg-required="<?php _e('Please select atleast one plan.', 'ARMember');?>" name="rule_plans[]" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember');?>" multiple="multiple" >
									<?php if (!empty($all_plans)):?>
										<?php foreach ($all_plans as $plan): ?>
											<option class="arm_message_selectbox_op" value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></option>
										<?php endforeach;?>
									<?php else: ?>
										<option value=""><?php _e('No Subscription Plans Available', 'ARMember');?></option>
									<?php endif;?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Drip Type', 'ARMember');?></th>
							<td>
								<input type="hidden" class="arm_drip_type_input" id="arm_add_drip_type" name="rule_type" value="instant"/>
								<dl class="arm_selectbox column_level_dd arm_width_100_pct">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_add_drip_type">
											<?php foreach($drip_types as $key => $val):?>
											<li data-label="<?php echo $val;?>" data-value="<?php echo $key;?>"><?php echo $val;?></li>
											<?php endforeach;?>
										</ul>
									</dd>
								</dl>
								<div class="armclear"></div>
								<div class="arm_drip_type_options_wrapper arm_drip_type_options_days hidden_section arm_required_wrapper" id="arm_drip_type_options_days">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_drip_type_days" name="rule_options[days]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>
								<div class="arm_drip_type_options_wrapper arm_drip_type_options_post_publish hidden_section arm_required_wrapper" id="arm_drip_type_options_post_publish">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_edit_drip_type_post_publish_add" name="rule_options[post_publish]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>
								<div class="arm_drip_type_options_wrapper arm_drip_type_options_post_modify hidden_section arm_required_wrapper" id="arm_drip_type_options_post_modify">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_edit_drip_type_post_modify_add" name="rule_options[post_modify]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>
								<div class="arm_drip_type_options_wrapper arm_drip_type_options_dates hidden_section arm_required_wrapper" id="arm_drip_type_options_dates">
									<div class="arm_drip_type_options_container">
										<label><?php _e('From Date', 'ARMember');?></label>
										<input type="text" id="arm_drip_type_date_from" class="arm_datepicker" name="rule_options[from_date]" value="<?php echo date('m/d/Y');?>" data-default_value="<?php echo date('m/d/Y');?>" data-msg-required="<?php _e('Please select from date.', 'ARMember');?>"/>
									</div>
									<div class="arm_drip_type_options_container">
										<label><?php _e('To Date', 'ARMember');?><span>(<?php _e('optional', 'ARMember');?>)</span></label>
										<input type="text" id="arm_drip_type_date_to" class="arm_datepicker" name="rule_options[to_date]" value=""/>
										<span>(<?php _e('Leave blank for never expiring', 'ARMember');?>)</span>
									</div>
								</div>
							</td>
						</tr>
						<?php /* ?>
						<tr class="arm_drip_enable_post_type_opts">
							<th><input type="checkbox" class="arm_icheckbox arm_drip_rule_enable_opts" value="1" name="rule_options[arm_drip_enable_before_subscription][enable_before_subscription]" id="arm_add_drip_rule_enable_opts" >
							<label for="arm_add_drip_rule_enable_opts"><?php _e('Enable', 'ARMember');?> <span class="arm_drip_rule_enable_type_text"><?php _e('Page', 'ARMember')?></span> <?php _e('Published Before Subscription', 'ARMember');?></label></th>
							<td class="arm_required_wrapper">
								<div class="arm_drip_enable_type_options_container hidden_section">
									<label for="arm_add_drip_enable_before_subscription_days"><?php _e('Show Before Subscription', 'ARMember');?></label>
									<input type="number" style="width:80px; min-width: 80px" id="arm_add_drip_enable_before_subscription_days" name="rule_options[arm_drip_enable_before_subscription][before_days]" min="0" value="0" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
									<label for="arm_add_drip_enable_before_subscription_days"><?php _e('day(s)', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<?php */ ?>
					</table>
					<input type="hidden" id="arm_drip_rule_status" name="rule_status" value="1"/>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_add_drip_rule" class="arm_loader_img arm_submit_btn_loader"  style="top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;display: none;" width="20" height="20" />
						<button class="arm_save_btn arm_new_drip_rule_button" type="submit" data-type="add"><?php _e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_new_drip_rule_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Edit Drip Rule Form ********************/.-->
<div class="arm_edit_drip_rule_wrapper popup_wrapper" >
	<form method="post" action="#" id="arm_edit_drip_rule_wrapper_frm" class="arm_admin_form arm_edit_drip_rule_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="edit_drip_rule_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Edit Drip Rule','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr>
							<!--<th><?php _e('Content Type', 'ARMember');?></th>-->
							<td colspan="2">
								<div id="arm_edit_rule_item_type" class="arm_edit_rule_item_type"></div>
							</td>
						</tr>
						<tr>
							<th><?php _e('Membership Plans', 'ARMember');?></th>
							<td class="arm_required_wrapper">
								<select id="arm_edit_drip_rule_plans" class="arm_chosen_selectbox arm_width_500" data-msg-required="<?php _e('Please select atleast one plan.', 'ARMember');?>" name="rule_plans[]" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember');?>" multiple="multiple" >
									<?php if (!empty($all_plans)):?>
										<?php foreach ($all_plans as $plan): ?>
											<option class="arm_message_selectbox_op" value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></option>
										<?php endforeach;?>
									<?php else: ?>
										<option value="">No Subscription Plans Available</option>
									<?php endif;?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Drip Type', 'ARMember');?></th>
							<td>
								<input type="hidden" class="arm_drip_type_input" id="arm_edit_drip_type" name="rule_type" value="immediately"/>
								<dl class="arm_selectbox column_level_dd arm_width_100_pct">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_edit_drip_type">
											<?php foreach($drip_types as $key => $val):?>
											<li data-label="<?php echo $val;?>" data-value="<?php echo $key;?>"><?php echo $val;?></li>
											<?php endforeach;?>
										</ul>
									</dd>
								</dl>
								<div class="armclear"></div>
								<div class="arm_drip_type_options_wrapper arm_edit_drip_type_options_days hidden_section arm_required_wrapper" id="arm_edit_drip_type_options_days">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_edit_drip_type_days" name="rule_options[days]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>


								<div class="arm_drip_type_options_wrapper arm_edit_drip_type_options_post_publish hidden_section arm_required_wrapper" id="arm_edit_drip_type_options_post_publish">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_edit_drip_type_post_publish" name="rule_options[post_publish]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>

								<div class="arm_drip_type_options_wrapper arm_edit_drip_type_options_post_modify hidden_section arm_required_wrapper" id="arm_edit_drip_type_options_post_modify">
									<div class="arm_drip_type_options_container">
										<label><?php _e('Show After', 'ARMember');?></label>
										<input type="number" id="arm_edit_drip_type_post_modify" name="rule_options[post_modify]" min="0" value="10" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
										<label><?php _e('Days', 'ARMember');?></label>
									</div>
								</div>



								<div class="arm_drip_type_options_wrapper arm_edit_drip_type_options_dates hidden_section arm_required_wrapper" id="arm_edit_drip_type_options_dates">
									<div class="arm_drip_type_options_container">
										<label><?php _e('From Date', 'ARMember');?></label>
										<input type="text" id="arm_edit_drip_type_date_from" class="arm_datepicker" name="rule_options[from_date]" value="<?php echo date('Y-m-d');?>" data-msg-required="<?php _e('Please select from date.', 'ARMember');?>"/>
									</div>
									<div class="arm_drip_type_options_container">
										<label><?php _e('To Date', 'ARMember');?><span>(<?php _e('optional', 'ARMember');?>)</span></label>
										<input type="text" id="arm_edit_drip_type_date_to" class="arm_datepicker" name="rule_options[to_date]" value=""/>
										<span>(<?php _e('Leave blank for never expiring', 'ARMember');?>)</span>
									</div>
								</div>
							</td>
						</tr>
						<?php /* ?>
						<tr class="arm_drip_enable_post_type_opts">
							<th><input type="checkbox" class="arm_icheckbox arm_drip_rule_enable_opts" value="1" name="rule_options[arm_drip_enable_before_subscription][enable_before_subscription]" id="arm_edit_drip_rule_enable_opts" >
							<label for="arm_edit_drip_rule_enable_opts"><?php _e('Enable', 'ARMember');?> <span id="arm_edit_drip_rule_enable_type_text" class="arm_drip_rule_enable_type_text"><?php _e('Page', 'ARMember')?></span> <?php _e('Published Before Subscription', 'ARMember');?></label></th>
							<td class="arm_required_wrapper">
								<div class="arm_drip_enable_type_options_container hidden_section">
									<label for="arm_edit_drip_enable_before_subscription_days"><?php _e('Show Before Subscription', 'ARMember');?></label>
									<input type="number" style="width: 80px; min-width: 80px"  id="arm_edit_drip_enable_before_subscription_days" name="rule_options[arm_drip_enable_before_subscription][before_days]" min="0" value="0" data-msg-required="<?php _e('Please enter days.', 'ARMember');?>" onkeydown="javascript:return checkNumber(event)"/>
									<label for="arm_edit_drip_enable_before_subscription_days"><?php _e('day(s)', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<?php */ ?>
					</table>
					<input type="hidden" id="arm_edit_drip_rule_id" name="rule_id" value="1"/>
					<input type="hidden" id="arm_edit_drip_rule_item_id" name="item_id" value="1"/>
					<input type="hidden" id="arm_edit_drip_rule_item_type" name="item_type" value="post"/>
					<input type="hidden" id="arm_edit_drip_rule_status" name="rule_status" value="1"/>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_edit_drip_rule" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;display: none;" width="20" height="20" />
						<button class="arm_save_btn arm_update_drip_rule_button" type="submit" data-type="add"><?php _e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn edit_drip_rule_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Drip Rule Members List ********************/.-->
<div class="arm_members_list_detail_popup popup_wrapper arm_members_list_detail_popup_wrapper" >
	<div class="arm_loading_grid" id="arm_loading_grid_members" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/loader.gif" alt="Loading.."></div>
    <div class="popup_wrapper_inner" style="overflow: hidden;">
        <div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_members_list_detail_close_btn"></span>
            <span class="add_rule_content"><?php _e('Members Details', 'ARMember'); ?></span>
        </div>
        <div class="popup_content_text arm_members_list_detail_popup_text">
            <table width="100%" cellspacing="0" class="display arm_min_width_802" id="armember_datatable_1" >
                <thead>
                    <tr>
                        <th><?php _e('Username', 'ARMember'); ?></th>
                        <th><?php _e('Email', 'ARMember'); ?></th>
                        <th class="arm_width_170"><?php _e('Days Of Subscription', 'ARMember'); ?></th>
                        <th class="arm-no-sort arm_width_170" ><?php _e('View Detail', 'ARMember'); ?></th>
                    </tr>
                </thead>
            </table>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search','ARMember');?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('members','ARMember');?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show','ARMember');?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing','ARMember');?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to','ARMember');?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of','ARMember');?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching members found','ARMember');?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('There is no any member found.','ARMember');?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from','ARMember');?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total','ARMember');?>"/>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
        </div>
        <div class="armclear"></div>
    </div>
</div>
<style type="text/css" title="currentStyle">
	.ColVis_Button, .paginate_page a{display:none;}
	.wrap table.dataTable thead tr th, .wrap table.dataTable thead tr td,
	.wrap #armember_datatable_wrapper tr td{width: auto;}
</style>
<script type="text/javascript" charset="utf-8">

// <![CDATA[
jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});
jQuery(document).ready( function ($) {
	jQuery(document).on('click', '.arm_remove_selected_itembox', function () {
		jQuery(this).parents('.arm_drip_rule_itembox').remove();
		if(jQuery('#arm_drip_rule_items .arm_drip_rule_itembox').length == 0) {
			jQuery('#arm_drip_rule_items_input').attr('required', 'required');
			jQuery('#arm_drip_rule_items').hide();
		}
		return false;
	});
	if (jQuery.isFunction(jQuery().autocomplete))
	{
		jQuery('#arm_drip_rule_items_input').autocomplete({
			minLength: 0,
			delay: 500,
			appendTo: "#arm_drip_rule_items_list_container",
			source: function (request, response) {
				var post_type = jQuery('#arm_add_rule_item_type').val();
				var _wpnonce = jQuery('input[name="_wpnonce"]').val();
				jQuery.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: 'json',
					data: "action=arm_get_drip_rule_item_options&arm_post_type=" + post_type + "&search_key="+request.term + "&_wpnonce=" + _wpnonce,
					beforeSend: function () {},
					success: function (res) {
						response(res.data);
					}
				});
			},
			focus: function() {return false;},
			select: function(event, ui) {
				var itemData = ui.item;
				jQuery("#arm_drip_rule_items_input").val('');
				if(jQuery('#arm_drip_rule_items .arm_drip_rule_itembox_'+itemData.id).length > 0) {
				} else {
					var itemHtml = '<div class="arm_drip_rule_itembox arm_drip_rule_itembox_'+itemData.id+'">';
					itemHtml += '<input type="hidden" name="item_id['+itemData.id+']" value="'+itemData.id+'"/>';
					itemHtml += '<label>'+itemData.label+'<span class="arm_remove_selected_itembox">x</span></label>';
					itemHtml += '</div>';
					jQuery("#arm_drip_rule_items").append(itemHtml);
					jQuery('#arm_drip_rule_items_input').removeAttr('required');
				}
				jQuery('#arm_drip_rule_items').show();
				return false;
			},
		}).data('uiAutocomplete')._renderItem = function (ul, item) {
			var itemClass = 'ui-menu-item';
			if(jQuery('#arm_drip_rule_items .arm_drip_rule_itembox_'+item.id).length > 0) {
				itemClass += ' ui-menu-item-selected';
			}
			var itemHtml = '<li class="'+itemClass+'" data-value="'+item.value+'" data-id="'+item.id+'" ><a>' + item.label + '</a></li>';
			return jQuery(itemHtml).appendTo(ul);
		};
	}
});


// ]]>
</script>