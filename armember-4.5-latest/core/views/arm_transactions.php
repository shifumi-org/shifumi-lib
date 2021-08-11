<?php global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons,$arm_payment_gateways,$arm_subscription_plans, $arm_pay_per_post_feature;?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_transactions_main_wrapper">
	<div class="content_wrapper arm_transactions_container" id="content_wrapper">
		<div class="page_title">
			<?php _e('Payment History','ARMember');?>
            <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn" href="<?php echo admin_url('admin.php?page='.$arm_slugs->transactions.'&action=new');?>"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add Manual Payment', 'ARMember') ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_transactions_grid_container" id="arm_transactions_grid_container">
			<?php 
			if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_transactions_list_records.php')) {
				include( MEMBERSHIP_VIEWS_DIR.'/arm_transactions_list_records.php');
			}
			?>
		</div>
		<?php 
		/* **********./Begin Change Transaction Status Popup/.********** */
		$change_transaction_status_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to change transaction status?",'ARMember' ).'</span>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_id"/>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_status"/>';
		$change_transaction_status_popup_arg = array(
			'id' => 'change_transaction_status_message',
			'class' => 'change_transaction_status_message',
            'title' => __('Change Transaction Status', 'ARMember'),
			'content' => $change_transaction_status_popup_content,
			'button_id' => 'arm_change_transaction_status_ok_btn',
			'button_onclick' => "arm_change_bank_transfer_status_func();",
		);
		echo $arm_global_settings->arm_get_bpopup_html($change_transaction_status_popup_arg);
		/* **********./End Change Transaction Status Popup/.********** */
		/* **********./Begin Bulk Delete Transaction Popup/.********** */
		$bulk_delete_transaction_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to delete this transaction(s)?",'ARMember' ).'</span>';
		$bulk_delete_transaction_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_transaction_popup_arg = array(
			'id' => 'delete_bulk_transactions_message',
			'class' => 'delete_bulk_transactions_message',
            'title' => __('Delete Transaction(s)', 'ARMember'),
			'content' => $bulk_delete_transaction_popup_content,
			'button_id' => 'arm_bulk_delete_transactions_ok_btn',
			'button_onclick' => "apply_transactions_bulk_action('bulk_delete_flag');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_transaction_popup_arg);
		/* **********./End Bulk Delete Transaction Popup/.********** */
		?>
                <div class="arm_invoice_detail_container"></div>
		<div class="arm_preview_log_detail_container"></div>
		<div class="arm_preview_failed_log_detail_container"></div>
	</div>
	<div class="armclear"></div>
	<br>
	<?php if($arm_pay_per_post_feature->isPayPerPostFeature){ ?>
	<div class="arm_solid_divider"></div>
	<div class="content_wrapper arm_transactions_container" id="content_wrapper">
		<div class="page_title">
			<?php _e('Paid Post Payment History','ARMember');?>
            <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_paid_post_transactions_grid_container" id="arm_paid_post_transactions_grid_container">
			<?php 
			if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_paid_post_transaction_list_records.php')) {
				include( MEMBERSHIP_VIEWS_DIR.'/arm_paid_post_transaction_list_records.php');
			}
			?>
		</div>
		<?php 
		/* **********./Begin Change Transaction Status Popup/.********** */
		$change_transaction_status_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to change transaction status?",'ARMember' ).'</span>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="pp_log_id"/>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="pp_log_status"/>';
		$change_transaction_status_popup_arg = array(
			'id' => 'change_pp_transaction_status_message',
			'class' => 'change_transaction_status_message',
            'title' => __('Change Transaction Status', 'ARMember'),
			'content' => $change_transaction_status_popup_content,
			'button_id' => 'arm_change_transaction_status_ok_btn',
			'button_onclick' => "arm_change_bank_transfer_status_func();",
		);
		echo $arm_global_settings->arm_get_bpopup_html($change_transaction_status_popup_arg);
		/* **********./End Change Transaction Status Popup/.********** */
		/* **********./Begin Bulk Delete Transaction Popup/.********** */
		$bulk_delete_transaction_popup_content = '<span class="arm_confirm_text">'.__("Are you sure you want to delete this transaction(s)?",'ARMember' ).'</span>';
		$bulk_delete_transaction_popup_content .= '<input type="hidden" value="false" id="bulk_pp_delete_flag"/>';
		$bulk_delete_transaction_popup_arg = array(
			'id' => 'delete_bulk_pp_transactions_message',
			'class' => 'delete_bulk_transactions_message',
            'title' => __('Delete Transaction(s)', 'ARMember'),
			'content' => $bulk_delete_transaction_popup_content,
			'button_id' => 'arm_bulk_pp_delete_pp_transactions_ok_btn',
			'button_onclick' => "apply_pp_transactions_bulk_action('bulk_pp_delete_flag');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_transaction_popup_arg);
		/* **********./End Bulk Delete Transaction Popup/.********** */
		?>
                <div class="arm_invoice_detail_container"></div>
		<div class="arm_preview_log_detail_container"></div>
		<div class="arm_preview_failed_log_detail_container"></div>
	</div>
	<?php } ?>
</div>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.arm_status_filter_label, .arm_status_filter_label select{min-width:120px;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});
jQuery(document).on('click', ".ColVis_Button:not(.ColVis_MasterButton)", function () {
	var form_id = jQuery('#arm_form_filter').val();
	var column_list = "";
	jQuery(this).parent('.ColVis_collection').find('button').each(function (x) {
		if (jQuery(this).find('input').is(':checked'))
		{
			column_list = column_list + 1 + ',';
		} else {
			column_list = column_list + 0 + ',';
		}
	});
	column_list = column_list.substr(0, column_list.length - 1);
	var _wpnonce = jQuery('input[name="_wpnonce"]').val();
	jQuery.ajax({
		type:"POST",
		url:__ARMAJAXURL,
		data:"action=arm_transaction_hide_show_columns&column_list="+column_list+"&_wpnonce="+_wpnonce,
		success: function (msg) {
			return false;
		}
	});
});
// ]]>
</script>