<?php 
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_class, $arm_manage_coupons, $arm_payment_gateways, $arm_subscription_plans,$arm_pay_per_post_feature, $arm_gift_version, $arm_manage_gift;
$currencies = $arm_payment_gateways->arm_get_all_currencies();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$all_members = $arm_members_class->arm_get_all_members_without_administrator(0,0);
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type');
if (isset($_POST['action']) && $_POST['action'] == 'add_payment_history') {
	do_action('arm_save_manual_payment', $_POST);
}
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_add_edit_payment_history_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_add_edit_payment_history_content" id="content_wrapper">
		<div class="page_title"><?php _e('Add Manual Payment','ARMember'); ?></div>
		<div class="armclear"></div>
		<form  method="post" id="arm_add_edit_payment_history_form" class="arm_add_edit_payment_history_form arm_admin_form">
			<input type="hidden" name="action" value="add_payment_history">
			<div class="arm_admin_form_content">
				<table class="form-table">
					<tr class="form-field form-required">
						<td colspan="2"><div class="arm-note-message --warning arm_width_95_pct"><p><?php _e('Important Note', 'ARMember');?>:</p><span><?php _e('The only purpose of this form is to add missed payment records of users for keeping track of their all payments. So, it doesn\'t mean that, when you add paymnet from here for any plan, it will renew next payment cycle or any plan will be assigned to user.', 'ARMember'); ?></span></div>
						</td>
					</tr>
					<tr class="form-field form-required arm_auto_user_field">
						<th>
							<label for="arm_user_id"><?php _e('Member','ARMember'); ?></label>
						</th>
						<td>
							<input id="arm_user_auto_selection" type="text" name="arm_user_ids" value="" placeholder="<?php _e('Search by username or email...', 'ARMember');?>" data-msg-required="<?php _e('Please select user.', 'ARMember');?>" required>
							<input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="0">
                            <div class="arm_users_items arm_required_wrapper" id="arm_users_items" style="display: none;"></div>
							<?php /*?><input type='hidden' id='arm_user_id' name="manual_payment[user_id]" value="" data-msg-required="<?php _e('Please select atleast one member', 'ARMember');?>" required/>
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_payment_transaction_users"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="arm_user_id">
										<li data-label="<?php _e('Type username to select user','ARMember'); ?>" data-value=""><?php _e('Type username to select user','ARMember'); ?></li>
									</ul>
								</dd>
							</dl><?php */?>
						</td>
					</tr>
					<?php 
						if($arm_pay_per_post_feature->isPayPerPostFeature || $arm_gift_version)
						{
					?> 
					<tr class="form-field form-required">
						<th>
							<label for="arm_plan_id"><?php _e('Select Plan Type','ARMember'); ?></label>
						</th>
						<td>
							<div class="arm_transaction_option_input arm_plan_type_enable_radios">
							    <input type="radio" class="arm_iradio arm_plan_type_chk" name="plan_type" value="0"  id="arm_plan_type_plan" checked>
							    <label for="arm_plan_type_plan"><?php _e('Membership Plan', 'ARMember');?></label>
							    <?php if($arm_pay_per_post_feature->isPayPerPostFeature) { ?>
							    <input type="radio" class="arm_iradio arm_plan_type_chk" name="plan_type" value="1"  id="arm_paid_post_plan_type">
							    <label for="arm_paid_post_plan_type"><?php _e('Paid Post', 'ARMember');?></label>
								<?php } ?>
								<?php if($arm_gift_version) { ?>
							    <input type="radio" class="arm_iradio arm_plan_type_chk" name="plan_type" value="2"  id="arm_gift_plan_type">
							    <label for="arm_gift_plan_type"><?php _e('Gift', 'ARMember');?></label>
								<?php } ?>
							</div>
						</td>
					</tr>
					<?php 
						}
						else 
						{ 
					?> 
                                <input type="hidden" name="plan_type" id="arm_plan_type_plan" value="0">
                    <?php  
                		}
                    ?>
					<tr class="form-field form-required arm_transaction_membership_plan_wrapper">
						<th>
							<label for="arm_plan_id"><?php _e('Select Membership Plan','ARMember'); ?></label>
						</th>
						<td>
							<input type="hidden" id="arm_plan_id" name="manual_payment[plan_id]" value="" data-msg-required="<?php _e('Please select atleast one membership', 'ARMember');?>"/>
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="arm_plan_id">
										<li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
										<?php 
										if (!empty($all_plans)) {
											foreach ($all_plans as $p) {
												$p_id = $p['arm_subscription_plan_id'];
												if ($p['arm_subscription_plan_status'] == '1' && $p['arm_subscription_plan_type'] != 'free') {
													?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']);?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name']));?></li><?php
												}
											}
										}
										?>
									</ul>
								</dd>
							</dl>
						</td>
					</tr>
					<?php if($arm_pay_per_post_feature->isPayPerPostFeature) { ?> 
					<tr class="form-field arm_auto_paid_post_field arm_transaction_paid_post_plan_wrapper hidden_section">
						<th>
							<label for="arm_paid_post_plan_auto_selection"><?php _e('Select Paid Post Plan','ARMember'); ?></label>
						</th>
						<td>
							<input id="arm_paid_post_plan_auto_selection" type="text" name="arm_paid_post_plan_ids" value="" placeholder="<?php _e('Search by paid post plan...', 'ARMember');?>" data-msg-required="<?php _e('Please select paid post plan.', 'ARMember');?>">
							<div class="arm_paid_post_plan_items arm_required_wrapper" id="arm_paid_post_plan_items" style="display: none;"></div>
						</td>
					</tr>
				<?php 
					} 
					if($arm_gift_version) { 
						$all_gifts = $arm_manage_gift->arm_get_all_subscription_gift_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type');
				?> 
						<tr class="form-field form-required arm_transaction_membership_gift_wrapper hidden_section">
							<th>
								<label for="arm_gift_id"><?php _e('Select Gift','ARMember'); ?></label>
							</th>
							<td>
								<input type="hidden" id="arm_gift_id" name="manual_payment[gift_id]" value="" data-msg-required="<?php _e('Please select atleast one membership', 'ARMember');?>"/>
								<dl class="arm_selectbox column_level_dd">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_gift_id">
											<li data-label="<?php _e('Select Gift', 'ARMember'); ?>" data-value=""><?php _e('Select Gift', 'ARMember'); ?></li>
											<?php 
											if (!empty($all_gifts)) {
												foreach ($all_gifts as $p) {
													$p_id = $p['arm_subscription_plan_id'];
													if ($p['arm_subscription_plan_status'] == '1' && $p['arm_subscription_plan_type'] != 'free') {
														?><li data-label="<?php echo stripslashes($p['arm_subscription_plan_name']);?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name']));?></li><?php
													}
												}
											}
											?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
				<?php
					}
				?>

					<tr class="form-field form-required">
						<th>
							<label for=""><?php _e('Status','ARMember'); ?></label>
						</th>
						<td>
							<input type="hidden" id="transaction_status" name="manual_payment[transaction_status]" value="success" />
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="transaction_status">
										<li data-label="<?php _e('Success', 'ARMember'); ?>" data-value="success"><?php _e('Success', 'ARMember'); ?></li>
										<li data-label="<?php _e('Pending', 'ARMember'); ?>" data-value="pending"><?php _e('Pending', 'ARMember'); ?></li>
										<li data-label="<?php _e('Cancelled', 'ARMember'); ?>" data-value="canceled"><?php _e('Cancelled', 'ARMember'); ?></li>
										<li data-label="<?php _e('Failed', 'ARMember'); ?>" data-value="failed"><?php _e('Failed', 'ARMember'); ?></li>
										<li data-label="<?php _e('Expired', 'ARMember'); ?>" data-value="expired"><?php _e('Expired', 'ARMember'); ?></li>
									</ul>
								</dd>
							</dl>
						</td>
					</tr>
					<tr class="form-field form-required">
						<th>
							<label for=""><?php _e('Amount','ARMember'); ?></label>
						</th>
						<td>
							<input type="text" name="manual_payment[amount]" value="0" onkeypress="javascript:return ArmNumberValidation(event,this)" class="arm_no_paste">
						</td>
					</tr>
					<tr class="form-field form-required">
						<th>
							<label for=""><?php _e('Currency','ARMember'); ?></label>
						</th>
						<td>
							<?php $currencies = apply_filters('arm_available_currencies', $currencies);?>
							<input type='hidden' id="transaction_currency" name="manual_payment[currency]" value="<?php echo $global_currency;?>"/>
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="transaction_currency">
										<?php foreach ($currencies as $key => $value): ?>
										<li data-label="<?php echo $key . " ( $value ) ";?>" data-value="<?php echo $key;?>"><?php echo $key . " ( $value ) ";?></li>
										<?php endforeach;?>
									</ul>
								</dd>
							</dl>
						</td>
					</tr>
					
					<tr class="form-field form-required">
						<th>
							<label for=""><?php _e('Note','ARMember'); ?></label>
						</th>
						<td>
							<textarea name="manual_payment[note]" rows="5" cols="40"></textarea>
						</td>
					</tr>
				</table>
				<div class="arm_submit_btn_container">
					<button class="arm_save_btn" type="submit" name="manualPaymentSubmit"><?php _e('Save', 'ARMember') ?></button>
					<a class="arm_cancel_btn" href="<?php echo admin_url('admin.php?page='.$arm_slugs->transactions);?>"><?php _e('Close', 'ARMember') ?></a>
				</div>
				<div class="armclear"></div>
			</div>
			<?php wp_nonce_field( 'arm_wp_nonce' ); ?>
		</form>
		<div class="armclear"></div>
	</div>
</div>
<div id="arm_all_users" style="display:none;visibility: hidden;opacity: 0;">
	<?php echo json_encode($all_members); ?>
</div>
<script type="text/javascript">
	__SELECT_USER = '<?php echo addslashes( __('Type username to select user','ARMember')); ?>';
</script>
<?php
	echo $ARMember->arm_get_need_help_html_content('member-payment-history-add');
?>