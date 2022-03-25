<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_subscription_plans, $arm_payment_gateways,$arm_pay_per_post_feature;
$date_format = $arm_global_settings->arm_get_wp_date_format();
$actions['delete_setup'] = __('Delete', 'ARMember');
$addNewSetupLink = admin_url('admin.php?page='.$arm_slugs->membership_setup.'&action=new_setup');

if ($total_setups < 1) {
	wp_redirect($addNewSetupLink);
	exit;
}
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.delete_box{float: <?php echo (is_rtl()) ? 'right' : 'left';?>;}
	.ColVis_Button{display:none;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
function ChangeID(id){
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_membership_setup_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_membership_setup_container" id="content_wrapper">
		<div class="page_title">
			<?php _e('Configure Plan + Signup Page','ARMember');?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_new_form_btn" href="<?php echo $addNewSetupLink;?>"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php _e('Add New Setup', 'ARMember');?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_manage_forms_content arm_membership_setups_list armPageContainer">
			<div class="arm_form_content_box">
				<div class="arm_form_list_container">
					<table class="form-table">
						<tbody>
							<tr class="arm_form_list_header">
								<td></td>
								<td class="arm_form_title_col setup_name"><?php _e('Setup Name','ARMember');?></td>
								<?php if($arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php')):?> 
								<td class="arm_form_title_col setup_type"><?php _e('Setup Type','ARMember');?></td>
								<?php endif; ?>
								<td><?php _e('Plans','ARMember');?></td>
								<td><?php _e('Gateways','ARMember');?></td>
								<td><?php _e('Member Form','ARMember');?></td>
								<td><?php _e('Shortcode','ARMember');?></td>
								<td class="arm_form_action_col"><?php _e('Action','ARMember');?></td>
								<td></td>
							</tr>
						<?php 
						$setup_result = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name`, `arm_setup_modules`, `arm_created_date`,`arm_setup_type` FROM `".$ARMember->tbl_arm_membership_setup."` ORDER BY `arm_setup_id` DESC");
						?>
						<?php if(!empty($setup_result)): ?>
							<?php foreach($setup_result as $val): ?>
								<?php $setupID = $val->arm_setup_id;?>
								<tr class="row_<?php echo $setupID;?>">
									<td></td>
									<td class="arm_form_title_col setup_name"><?php 
									$edit_link = admin_url('admin.php?page='.$arm_slugs->membership_setup.'&action=edit_setup&id='.$setupID);
									echo '<a href="'.$edit_link.'">'. stripslashes($val->arm_setup_name) .'</a> ';
									?></td>
									<?php if($arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php')):?> 
									<td class="arm_form_shortcode_col">
									<?php 
										$arm_setup_type = $val->arm_setup_type;

										if($arm_setup_type==0){
											echo _e('Membership Plan','ARMember');
												
										}elseif($arm_setup_type==1){
											echo _e('Paid Post','ARMember');
										} elseif($arm_setup_type==2) {
											echo _e('Gift','ARMember');
										}
									?>
									</td>
									<?php endif; ?>
									<td class="arm_form_shortcode_col"><?php 
									$val->setup_modules = maybe_unserialize($val->arm_setup_modules);
									$module_plans = (isset($val->setup_modules['modules']['plans'])) ? $val->setup_modules['modules']['plans'] : array();
									$plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($module_plans);
									echo (!empty($plan_title)) ? stripslashes_deep($plan_title) : '--';
									?></td>
									<td class="arm_form_shortcode_col"><?php 
									$module_gateways = (isset($val->setup_modules['modules']['gateways'])) ? $val->setup_modules['modules']['gateways'] : array();
									$gateway_title = '--';
                                    
                                    if (!empty($module_gateways)) {
                                        $gateway_title = '';
                                        foreach ($module_gateways as $key => $gateway) {
                                            $gateway_title .= $arm_payment_gateways->arm_gateway_name_by_key($gateway).', ';
                                        }
                                    }
                                    echo rtrim($gateway_title,', ');
									?></td>
									<td class="arm_form_shortcode_col"><?php 
									$module_plans = (isset($val->setup_modules['modules']['forms'])) ? $val->setup_modules['modules']['forms'] : 0;
                                    
									$module_form = new ARM_Form('id', $module_plans);
									if ($module_form->exists()) {
										echo $module_form->form_detail['arm_form_label'];
									} else {
										echo '--';
									}
									?></td>
									<td class="arm_form_shortcode_col">
										<?php $shortCode = '[arm_setup id="'.$setupID.'"]';?>
										<div class="arm_shortcode_text arm_form_shortcode_box">
											<span class="armCopyText"><?php echo esc_attr($shortCode);?></span>
											<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr($shortCode);?>"><?php _e('Click to copy', 'ARMember');?></span>
											<span class="arm_copied_text"><img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/copied_ok.png" alt="ok"/><?php _e('Code Copied', 'ARMember');?></span>
										</div>
									</td>
									<td class="arm_form_action_col">
										<div class="arm_form_action_btns">
											<a href="<?php echo $edit_link;?>" class="arm_get_form_link" data-form_id="<?php echo $setupID;?>">
												<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon_hover.png';" class="armhelptip" title="<?php _e('Edit Form','ARMember');?>" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/edit_icon.png';" />
											</a>
											<a href="javascript:void(0)" onclick="showConfirmBoxCallback(<?php echo $setupID;?>);" data-form_id="<?php echo $setupID;?>">
												<img src="<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png" class="armhelptip" title="<?php _e('Delete Setup','ARMember');?>" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete_hover.png';" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL;?>/delete.png';" style='cursor:pointer'/>
											</a>
											<?php
											echo $arm_global_settings->arm_get_confirm_box($setupID, __("Are you sure you want to delete this setup?", 'ARMember'), 'arm_setup_delete_btn');
											?>
										</div>
									</td>
									<td></td>
								</tr>
							<?php endforeach;?>
						<?php endif;?>
						</tbody>
					</table>
					<?php wp_nonce_field( 'arm_wp_nonce' );?>
				</div>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>

<?php
    echo $ARMember->arm_get_need_help_html_content('configure-membership-setup--list');
?>