<?php 
global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_global_settings, $arm_member_forms, $arm_subscription_plans, $arm_membership_setup, $arm_social_feature, $arm_drip_rules, $arm_members_directory,$arm_pay_per_post_feature;
$arm_forms = $arm_member_forms->arm_get_all_member_forms('arm_form_id, arm_form_label, arm_form_type');
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
$arm_all_free_plans = $arm_subscription_plans->arm_get_all_free_plans();
$all_roles = $arm_global_settings->arm_get_all_roles();
$total_setups = $arm_membership_setup->arm_total_setups();
$wrapperClass = 'arm_shortcode_options_popup_wrapper popup_wrapper arm_normal_wrapper ';
if (is_rtl()) {
	$wrapperClass .= ' arm_rtl_wrapper ';
}
?>
<!--********************/. Form Shortcodes ./********************-->
<div id="arm_form_shortcode_options_popup_wrapper" class="<?php echo $wrapperClass;?>" style="width:960px;">
	<input type="hidden" id="arm_ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>" />
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php _e('Membership Shortcodes', 'ARMember');?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
			<div class="arm_tabgroups">
				<div class="arm_tabgroup_belt">
					<ul class="arm_tabgroup_link_container">
						<li class="arm_tabgroup_link arm_active">
							<a href="#arm-forms" data-id="arm-forms"><?php _e('Forms', 'ARMember');?></a>
						</li>
						<?php if($total_setups > 0): ?>
						<li class="arm_tabgroup_link arm_tabgroup_link_setup">
							<a href="#arm-membership-setup" data-id="arm-membership-setup"><?php _e('Membership Setup Wizard', 'ARMember');?></a>
						</li>
						<?php endif;?>
						<li class="arm_tabgroup_link">
							<a href="#arm-action-buttons" data-id="arm-action-buttons"><?php _e('Action Buttons', 'ARMember');?></a>
						</li>
						<li class="arm_tabgroup_link">
							<a href="#arm-other" data-id="arm-other"><?php _e('Others', 'ARMember');?></a>
						</li>
						<li class="arm_tabgroup_link">
							<a href="#arm-conditionals" data-id="arm-conditionals"><?php _e('IF Conditions', 'ARMember');?></a>
						</li>
						<?php if ($arm_drip_rules->isDripFeature): ?>
						<li class="arm_tabgroup_link arm_tabgroup_link_setup">
							<a href="#arm-drip-content" data-id="arm-drip-content"><?php _e('Drip Content', 'ARMember');?></a>
						</li>
						<?php endif;?>
                                                <?php do_action('arm_shortcode_add_tab'); ?>
					</ul>
					<div class="armclear"></div>
				</div>
				<div class="arm_tabgroup_content_wrapper">
					<div id="arm-forms" class="arm_tabgroup_content arm_show">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Form Type', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_form_type" name="" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_form_type">
													<li data-label="<?php _e('Select Form Type','ARMember');?>" data-value=""><?php _e('Select Form Type', 'ARMember');?></li>
													<li data-label="<?php _e('Registration','ARMember');?>" data-value="registration"><?php _e('Registration', 'ARMember');?></li>
													<li data-label="<?php _e('Login','ARMember');?>" data-value="login"><?php _e('Login', 'ARMember');?></li>
													<li data-label="<?php _e('Forgot Password','ARMember');?>" data-value="forgot_password"><?php _e('Forgot Password', 'ARMember');?></li>
													<li data-label="<?php _e('Change Password','ARMember');?>" data-value="change_password"><?php _e('Change Password', 'ARMember');?></li>
													<li data-label="<?php _e('Edit Profile','ARMember');?>" data-value="edit_profile"><?php _e('Edit Profile', 'ARMember');?></li>
													
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						<form class="arm_shortcode_form_opts arm_shortcode_form_select arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr class="arm_shortcode_form_select arm_shortcode_form_main_opt">
										<th><?php _e('Select Form', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_id" name="id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper" data-id="arm_shortcode_form_id">
														<li data-label="<?php _e('Select Form','ARMember');?>" data-value=""><?php _e('Select Form', 'ARMember');?></li>
														<?php if(!empty($arm_forms)): ?>
															<?php foreach($arm_forms as $_form): ?>
                                                                <?php 
                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                ?>
																<li class="arm_shortcode_form_id_li <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															<?php endforeach;?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									
									<tr class="arm_shortcode_form_options arm_shortcode_form_popup_options arm_hidden">
										<th><?php _e('How you want to include this form into page?', 'ARMember'); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="popup" value="false" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked">
												<?php _e('Internal', 'ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="popup" value="true" class="arm_iradio arm_shortcode_form_popup_opt">
												<?php _e('External popup window', 'ARMember'); ?>
											</label>
											<div class="form_popup_options">
												<div class="form_popup_options_row">
													<span class="arm_opt_title"><?php _e('Link Type', 'ARMember'); ?>: </span>
													<input type="hidden" id="arm_shortcode_form_link_type" name="link_type" value="link" />
													<dl class="arm_selectbox column_level_dd">
														<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="arm_shortcode_form_link_type">
																<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
																<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
																<li data-label="<?php _e("On Load",'ARMember');?>" data-value="onload"><?php _e("On Load", 'ARMember'); ?></li>
															</ul>
														</dd>
													</dl>
												</div>
												<div class="form_popup_options_row">
													<span class="arm_opt_title arm_shortcode_form_link_opts"><?php _e('Link Text', 'ARMember'); ?>: </span>
													<span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?>: </span>
													<input type="text" name="link_title" value="<?php _e('Click here to open Form', 'ARMember'); ?>">
												</div>
												<div class="form_popup_options_row arm_form_background_overlay">
													<span class="arm_opt_title"><?php _e('Background Overlay', 'ARMember'); ?>: </span>
													<div>
														<input type="hidden" id="arm_shortcode_form_overlay" name="overlay" value="0.6" />
														<dl class="arm_selectbox column_level_dd">
															<dt style="width: 80px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
															<dd>
																<ul data-id="arm_shortcode_form_overlay">
																	<li data-label="0 (<?php _e('None', 'ARMember');?>)" data-value="0">0 (<?php _e('None', 'ARMember');?>)</li>
																	<?php for($i = 1; $i < 11; $i++):?>
																	
																	<li data-label="<?php echo $i*10;?>" data-value="<?php echo $i/10;?>"><?php echo $i*10;?></li>
																	<?php endfor;?>
																</ul>
															</dd>
														</dl>
													</div>
													<div><input id="arm_form_modal_bgcolor" type="text" name="modal_bgcolor" class="arm_colorpicker arm_form_modal_bgcolor" value="#000000"/><em>&nbsp;&nbsp;(<?php _e('Background Color', 'ARMember');?>)</em></div>
												</div>
												<div class="armclear"></div>
												<div class="form_popup_options_row arm_form_popup_size">
													<span class="arm_opt_title"><?php _e('Size', 'ARMember'); ?>: </span>
													<div><input type="text" name="popup_height" value="auto"><br/><?php _e('Height', 'ARMember'); ?></div>
													<span class="popup_height_suffinx">px</span>
													<div><input type="text" name="popup_width" value="700"><br/><?php _e('Width', 'ARMember'); ?></div>
													<span class="popup_width_suffinx">px</span>
												</div>
												<div class="form_popup_options_row">
                                                    <div class="arm_opt_title">
                                                        <span class="arm_opt_title arm_shortcode_form_link_opts" style="vertical-align: top;"><?php _e('Link CSS', 'ARMember'); ?>: </span>
                                                        <span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button CSS', 'ARMember'); ?>: </span>
                                                    </div>
                                                    <div class="popup_arm_opt_input_div">
                                                        <textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea><br/>
                                                        <em>e.g. color: #ffffff;</em>
                                                    </div>
												</div>
												<div class="form_popup_options_row">
                                                    <div class="arm_opt_title">
                                                        <span class="arm_opt_title arm_shortcode_form_link_opts" style="vertical-align: top;"><?php _e('Link Hover CSS', 'ARMember'); ?>: </span>
                                                        <span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button Hover CSS', 'ARMember'); ?>: </span>
                                                    </div>
                                                    <div class="popup_arm_opt_input_div">
                                                        <textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea><br/>
                                                        <em>e.g. color: #ffffff;</em>
                                                    </div>
												</div>
											</div>
										</td>
									</tr>
									<tr id="arm_form_position_opt_wrapper" class="arm_shortcode_form_options arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php _e('Form Position','ARMember'); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio" />
												<?php _e('Left','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio" checked="checked" />
												<?php _e('Center','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio" />
												<?php _e('Right','ARMember'); ?>
											</label>
											<div style="margin-left: 10px;">(<?php _e('With Respect to its container','ARMember') ?>)</div>
										</td>
									</tr>
                                                                        
                                                                        <tr id="arm_assign_default_plan_opt_wrapper" class="arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php _e('Assign Default Plan','ARMember'); ?></th>
										<td>
                                                                                    <input type="hidden" id="arm_assign_default_plan" name="assign_default_plan" value="0" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_assign_default_plan_wrapper" data-id="arm_assign_default_plan">
														<li data-label="<?php _e('Select Plan','ARMember');?>" data-value="0"><?php _e('Select Plan', 'ARMember');?></li>
														<?php if(!empty($arm_all_free_plans)): ?>
															<?php foreach($arm_all_free_plans as $plan): ?>
																<li class="arm_assign_default_plan_li <?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-label="<?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></li>
															<?php endforeach;?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                        
                                                                        <tr id="arm_logged_in_message_opt_wrapper" class="arm_shortcode_form_options arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php _e('Logged In Message','ARMember'); ?></th>
										<td>
											<input type="text" name="logged_in_message" value="<?php _e('You are already logged in.', 'ARMember') ?>" id="logged_in_message_input"><br/>
										</td>
									</tr>

									<?php
										do_action('arm_add_forms_shortcode_options');
									?>

								</table>
								<div class="armclear"></div>
							</div>

						</form>
						<form class="arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr class="arm_shortcode_form_select">
										<th><?php _e('Select Form', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_name" class="arm_shortcode_edit_profile_form" name="form_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper" data-id="arm_shortcode_form_name">
														<li data-label="<?php _e('Select Form','ARMember');?>" data-value=""><?php _e('Select Form', 'ARMember');?></li>
														<?php if(!empty($arm_forms)):
															foreach($arm_forms as $_form):
																if($_form['arm_form_type'] == 'edit_profile'){
															 		$formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
															 		?>
															    	<li class="arm_shortcode_form_id_li_edit_profile <?php echo $_form['arm_form_type'];?>" data-label="<?php echo $formTitle;?>" data-value="<?php echo $_form['arm_form_id'];?>"><?php echo $formTitle;?></li>
															     	<?php 
																}
															endforeach;
														?>
														<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th><?php _e('Form Position','ARMember'); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('Left','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php _e('Center','ARMember'); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php _e('Right','ARMember'); ?>
											</label>
											<div style="margin-left: 10px;">(<?php _e('With Respect to its container','ARMember') ?>)</div>
										</td>
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-membership-setup" class="arm_tabgroup_content">
                                                <form class="arm_shortcode_membership_setup_opts" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                    <tr class="arm_shortcode_setup_main_opt">
										<th><?php _e('Select Setup', 'ARMember');?></th>
										<td>
											<?php $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` ");?>
											<input type="hidden" id="arm_shortcode_membership_setup_id" name="id" value="<?php echo (!empty($setups[0]) ? $setups[0]->arm_setup_id : '');?>" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_membership_setup_id">
                                                                                                            <li data-label="<?php _e('Select Setup', 'ARMember');?>" data-value=""><?php _e('Select Setup', 'ARMember');?></li>
													<?php if(!empty($setups)):?>
                                                                                                           
														<?php foreach($setups as $ms):?>
														<li data-label="<?php echo stripslashes($ms->arm_setup_name);?>" data-value="<?php echo $ms->arm_setup_id;?>"><?php echo stripslashes($ms->arm_setup_name);?></li>
														<?php endforeach;?>
													<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr class="arm_shortcode_setup_main_opt">
										<th><?php _e('Hide Setup Title?', 'ARMember');?></th>
										<td>
											<label>
												<input type="radio" name="hide_title" value="true" class="arm_iradio">
												<span><?php _e('Yes', 'ARMember');?></span>
											</label>
											<label>
												<input type="radio" name="hide_title" value="false" class="arm_iradio" checked="checked">
												<span><?php _e('No', 'ARMember');?></span>
											</label>
										</td>
									</tr>
                                                                        <tr class="arm_shortcode_setup_options arm_shortcode_setup_popup_options">
										<th><?php _e('How you want to include this form into page?', 'ARMember'); ?></th>
										<td>
											<label class="setup_popup_type_radio">
												<input type="radio" name="popup" value="false" class="arm_iradio arm_shortcode_setup_popup_opt" checked="checked">
												<?php _e('Internal', 'ARMember'); ?>
											</label>
											<label class="setup_popup_type_radio">
												<input type="radio" name="popup" value="true" class="arm_iradio arm_shortcode_setup_popup_opt">
												<?php _e('External popup window', 'ARMember'); ?>
											</label>
											<div class="setup_popup_options">
												<div class="setup_popup_options_row">
													<span class="arm_opt_title"><?php _e('Link Type', 'ARMember'); ?>: </span>
													<input type="hidden" id="arm_shortcode_setup_link_type" name="link_type" value="link" />
													<dl class="arm_selectbox column_level_dd">
														<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="arm_shortcode_setup_link_type">
																<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
																<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
															</ul>
														</dd>
													</dl>
												</div>
												<div class="setup_popup_options_row">
													<span class="arm_opt_title arm_shortcode_setup_link_opts"><?php _e('Link Text', 'ARMember'); ?>: </span>
													<span class="arm_opt_title arm_shortcode_setup_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?>: </span>
													<input type="text" name="link_title" value="<?php _e('Click here to open Form', 'ARMember'); ?>">
												</div>
												<div class="setup_popup_options_row arm_setup_background_overlay">
													<span class="arm_opt_title"><?php _e('Background Overlay', 'ARMember'); ?>: </span>
													<div>
														<input type="hidden" id="arm_shortcode_form_overlay_setup" name="overlay" value="0.6" />
														<dl class="arm_selectbox column_level_dd">
															<dt style="width: 80px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
															<dd>
																<ul data-id="arm_shortcode_form_overlay_setup">
																	<li data-label="0 (<?php _e('None', 'ARMember');?>)" data-value="0">0 (<?php _e('None', 'ARMember');?>)</li>
																	<?php for($i = 1; $i < 11; $i++):?>
																	
																	<li data-label="<?php echo $i*10;?>" data-value="<?php echo $i/10;?>"><?php echo $i*10;?></li>
																	<?php endfor;?>
																</ul>
															</dd>
														</dl>
													</div>
													<div><input id="arm_form_setup_modal_bgcolor" type="text" name="modal_bgcolor" class="arm_colorpicker arm_form_modal_bgcolor" value="#000000"/><em>&nbsp;&nbsp;(<?php _e('Background Color', 'ARMember');?>)</em></div>
												</div>
												<div class="armclear"></div>
												<div class="setup_popup_options_row arm_setup_popup_size">
													<span class="arm_opt_title"><?php _e('Size', 'ARMember'); ?>: </span>
													<div><input type="text" name="popup_height" value="auto"><br/><?php _e('Height', 'ARMember'); ?></div>
													<span class="popup_height_suffinx">px</span>
													<div><input type="text" name="popup_width" value="800"><br/><?php _e('Width', 'ARMember'); ?></div>
													<span class="popup_width_suffinx">px</span>
												</div>
												<div class="setup_popup_options_row">
                                                    <div class="arm_opt_title">
                                                        <span class="arm_opt_title arm_shortcode_setup_link_opts" style="vertical-align: top;"><?php _e('Link CSS', 'ARMember'); ?>: </span>
                                                        <span class="arm_opt_title arm_shortcode_setup_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button CSS', 'ARMember'); ?>: </span>
                                                    </div>
                                                    <div class="popup_arm_opt_input_div">
                                                        <textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea><br/>
                                                        <em>e.g. color: #000000;</em>
                                                    </div>
												</div>
												<div class="setup_popup_options_row">
                                                    <div class="arm_opt_title">
                                                        <span class="arm_opt_title arm_shortcode_setup_link_opts" style="vertical-align: top;"><?php _e('Link Hover CSS', 'ARMember'); ?>: </span>
                                                        <span class="arm_opt_title arm_shortcode_setup_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button Hover CSS', 'ARMember'); ?>: </span>
                                                    </div>
                                                    <div class="popup_arm_opt_input_div">
                                                        <textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea><br/>
                                                        <em>e.g. color: #ffffff;</em>
                                                    </div>
												</div>
											</div>
										</td>
									</tr>
                                                                        <tr class="arm_shortcode_setup_main_opt">
                                                                            <th style="color: red;"><?php _e('Important Notes', 'ARMember');?></th>
                                                                            <td>
                                                                                <div style="padding-top:5px;"><?php _e('Add hide_plans="1" parameter to hide plan selection area.', 'ARMember'); ?></div>
                                                                                <div style="padding-top:5px;"><?php _e('Add subscription_plan="PLAN_ID" parameter to keep plan having PLAN_ID selected.', 'ARMember'); ?></div>
                                                                                <div style="padding-top:5px;"><?php _e('Add payment_duration="ORDER_ID" parameter to keep plan having ORDER_ID selected.This argument will work only subscription_plan argument is passed.', 'ARMember'); ?></div>
                                                                            </td>
									</tr>
                                                                        
								</table>
							</div>
						</form>
					</div>
					<div id="arm-action-buttons" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Action Type', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_action_button_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_action_button_type">
													<li data-label="<?php _e('Select Action Type','ARMember');?>" data-value=""><?php _e('Select Action Type', 'ARMember');?></li>
													<?php 
                                                                                                        $social_options = $arm_social_feature->arm_get_active_social_options();
                                                                                                        if(($arm_social_feature->isSocialLoginFeature) == 1 && !empty($social_options)): ?>
													<li data-label="<?php _e('Social Login','ARMember');?>" data-value="arm_social_login"><?php _e('Social Login', 'ARMember');?></li>
													<?php endif; ?>
													<?php
														if($arm_pay_per_post_feature->isPayPerPostFeature){ ?>
															<li data-label="<?php _e('Paid Post Buy Now Button', 'ARMember'); ?>" data-value="arm_paid_post_buy_now"><?php _e('Paid Post Buy Now Button', 'ARMember'); ?></li>
													<?php } ?>
													<li data-label="<?php _e('Logout','ARMember');?>" data-value="arm_logout"><?php _e('Logout', 'ARMember');?></li>

												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>
                        <form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_social_login arm_hidden" onsubmit="return false;">
                            <div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                    <tr>
										<th><?php _e('Network Type', 'ARMember');?></th>
										<td>
                                            <?php
                                            $social_options = $arm_social_feature->arm_get_active_social_options();
                                            $count = 0;
                                            $default_network_shown ='';
                                            foreach ($social_options as $sk => $so) {
                                                $count++;
                                                if ($count == 1) {
                                                    $default_network_shown = $sk;
                                                    ?>
                                                    <input type="hidden" id="arm_shortcode_social_login_network_type" name="network" value="<?php echo $sk; ?>" />

                                                    <?php
                                                }
                                            }
                                            ?>
                                                                                    
											
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_social_login_network_type">
							<?php
							foreach ($social_options as $sk => $so) {
                                                        ?>
                                                            <li data-label="<?php _e($so['label'],'ARMember');?>" data-value="<?php echo $sk; ?>"><?php _e($so['label'], 'ARMember');?></li>

                                                         <?php
                                                         }
                                                        ?>
													</ul>
												</dd>
											</dl>
										</td>
                                    </tr>
                                    <tr class="arm_social_login_fb_icons <?php
				    if ($default_network_shown != 'facebook') {
					echo 'arm_hidden';
				    }
				    ?>">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $fb_icons = $arm_social_feature->arm_get_social_network_icons('facebook');
                                            $i=0;
                                            ?>
                                            <?php if(!empty($fb_icons)):?>
                                            <?php foreach($fb_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_fb<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" id="fb_icon<?php echo $i; ?>" value="<?php echo $url;?>" <?php if ($i == 1 && $default_network_shown == 'facebook') { ?> checked="checked" <?php } ?>>
                        <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?>                           
                                                    <img src="<?php echo ($url_icon);?>" alt="Facebook" class="arm_social_login_icon_image" />
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>

                                    <tr class="arm_social_login_tw_icons <?php
				    if ($default_network_shown != 'twitter') {
					echo 'arm_hidden';
				    }
				    ?>">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $tw_icons = $arm_social_feature->arm_get_social_network_icons('twitter');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($tw_icons)):?>
                                            <?php foreach($tw_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_tw<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="tw_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'twitter') { ?> checked="checked" <?php } ?>>
                                                    <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?> 
                                                    <img src="<?php echo ($url_icon);?>" alt="Twitter" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    <tr class="arm_social_login_li_icons <?php
				    if ($default_network_shown != 'linkedin') {
					echo 'arm_hidden';
				    }
				    ?>">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $li_icons = $arm_social_feature->arm_get_social_network_icons('linkedin');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($li_icons)):?>
                                            <?php foreach($li_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_li<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="li_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'linkedin') { ?> checked="checked" <?php } ?>>
                                                                                <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?> 
                                                    <img src="<?php echo ($url_icon);?>" alt="Linkedin" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    
                                    <tr class="arm_social_login_vk_icons <?php
				    if ($default_network_shown != 'vk') {
					echo 'arm_hidden';
				    }
				    ?>"">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $vk_icons = $arm_social_feature->arm_get_social_network_icons('vk');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($vk_icons)):?>
                                            <?php foreach($vk_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_vk<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="vk_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'vk') { ?> checked="checked" <?php } ?>>
                                                                                                                 <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?>   
                                                    <img src="<?php echo ($url_icon);?>" alt="VK" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    <tr class="arm_social_login_insta_icons <?php
                                    if ($default_network_shown != 'insta') {
                                    	echo 'arm_hidden';
                                    }
                                    ?>"">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $insta_icons = $arm_social_feature->arm_get_social_network_icons('insta');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($insta_icons)):?>
                                            <?php foreach($insta_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_insta<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="insta_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'insta') { ?> checked="checked" <?php } ?>>
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                    	$url_icon =strstr($url, "//");
                                                    }else if(file_exists($url)){
                                                    	$url_icon = $url;
                                                    }else{
                                                    	$url_icon = $url;
                                                    }
                                                    ?>   
                                                    <img src="<?php echo ($url_icon);?>" alt="INSTA" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    <tr class="arm_social_login_gp_icons <?php
				    					if ($default_network_shown != 'google') {
										echo 'arm_hidden';
				    					}
				    					?>">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $li_icons = $arm_social_feature->arm_get_social_network_icons('google');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($li_icons)):?>
                                            <?php foreach($li_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_gp<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="gp_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'google') { ?> checked="checked" <?php } ?>>
                                                                                <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?> 
                                                    <img src="<?php echo ($url_icon);?>" alt="google" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>

                                    <tr class="arm_social_login_tu_icons <?php
								    if ($default_network_shown != 'tumblr') {
									echo 'arm_hidden';
								    }
								    ?>">
                                        <th><?php _e('Network Icon', 'ARMember'); ?></th>
                                        <td>
                                            <?php
                                            $tu_icons = $arm_social_feature->arm_get_social_network_icons('tumblr');
                                            $i = 0;
                                            ?>
                                            <?php if(!empty($tu_icons)):?>
                                            <?php foreach($tu_icons as $icon => $url): $i++;?>
                                                <div class="arm_social_login_icon_container" id="arm_social_login_tu<?php echo $i; ?>">
                                                    <input type="radio" class="arm_iradio" name="icon" value="<?php echo $url;?>" id="tu_icon<?php echo $i; ?>" <?php if ($i == 1 && $default_network_shown == 'tumblr') { ?> checked="checked" <?php } ?>>
                                                    <?php
                        if(file_exists(strstr($url, "//"))){
                                $url_icon =strstr($url, "//");
                            }else if(file_exists($url)){
                               $url_icon = $url;
                            }else{
                                 $url_icon = $url;
                            }
                        ?> 
                                                    <img src="<?php echo ($url_icon);?>" alt="Tumblr" class="arm_social_login_icon_image"/>
                                                </div>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

						</form>
						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Link Type', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_logout_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_logout_link_type">
														<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
														<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php _e('Logout', 'ARMember');?>"></td>
									</tr>
									<tr>
										<th><?php _e('Display User Info?', 'ARMember');?></th>
										<td>
											<label>
												<input type="radio" name="user_info" value="true" class="arm_iradio" checked="checked">
												<span><?php _e('Yes', 'ARMember');?></span>
											</label>
											<label>
												<input type="radio" name="user_info" value="false" class="arm_iradio">
												<span><?php _e('No', 'ARMember');?></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php _e('Redirect After Logout', 'ARMember');?></th>
										<td>
											<input type="text" name="redirect_to" value="<?php echo ARM_HOME_URL;?>">
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
										</td>
									</tr>
								</table>
							</div>
						</form>



						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_paid_post_buy_now arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th>
											<span class="arm_shortcode_pay_per_post_link_opts"><?php _e('Redirect URL (Optional)', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="redirect_url"><br /><span style="color: #f00;"><?php _e("If you don't add URL at above textbox then buynow page will be set from ARMember -> General Settings -> Page Setup page.", "ARMember"); ?></span></td>

									</tr>
									<tr>
										<th><?php _e('Link Type', 'ARMember');?></th>
										<td>
											<input type="hidden" id="arm_shortcode_pay_per_post_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_pay_per_post_link_type">
														<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
														<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_pay_per_post_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
											<span class="arm_shortcode_pay_per_post_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php _e('Buy Now', 'ARMember');?>"></td>
									</tr>
									<tr>
										<th><?php _e('Success Redirect URL (Optional)', 'ARMember'); ?></th>
										<td><input type="text" name="success_url"><br /><span style="color: #f00;"><?php _e("If you don't add URL at above textbox then after purchase paid post, page will be redirected to the URL as set at ARMember -> General Settings -> Redirection Rules page.", "ARMember"); ?></span></td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_pay_per_post_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_pay_per_post_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_pay_per_post_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_pay_per_post_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
										</td>
									</tr>
								</table>
							</div>
						</form>

						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Link Type', 'ARMember'); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_cancel_membership_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_cancel_membership_link_type">
														<li data-label="<?php _e('Link','ARMember');?>" data-value="link"><?php _e('Link', 'ARMember');?></li>
														<li data-label="<?php _e('Button','ARMember');?>" data-value="button"><?php _e('Button', 'ARMember');?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php _e('Cancel Subscription', 'ARMember'); ?>"></td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
                                                                                        <br/>
                                                                                        <em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
                                                                                        <br/>
                                                                                        <em>e.g. color: #ffffff;</em>
                                                                                </td>
                                                                                
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-other" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php _e('Select Option', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_other_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_other_type">
													<li data-label="<?php _e('Select Option','ARMember');?>" data-value=""><?php _e('Select Option', 'ARMember');?></li>
													<li data-label="<?php _e('My Profile','ARMember');?>" data-value="arm_account_detail"><?php _e('My Profile', 'ARMember');?></li>
													
													<li data-label="<?php _e('Current Membership','ARMember'); ?>" data-value="arm_current_membership"><?php _e('Current Membership','ARMember' ); ?></li>
													<li data-label="<?php _e('Payment Transactions','ARMember');?>" data-value="arm_member_transaction"><?php _e('Payment Transactions', 'ARMember');?></li>

													<?php if($arm_pay_per_post_feature->isPayPerPostFeature):?> 
                                                    <li data-label="<?php _e('Purchased Paid Post List','ARMember'); ?>" data-value="arm_paid_post_current_membership"><?php _e('Purchased Paid Post List','ARMember' ); ?></li>
                                                    <li data-label="<?php _e('Paid Post Payment Transactions','ARMember');?>" data-value="arm_paid_post_member_transaction"><?php _e('Paid Post Payment Transactions', 'ARMember');?></li>
                                                	<?php endif; ?>

                                                    <li data-label="<?php _e('Conditional Redirection(User Role)','ARMember'); ?>" data-value="arm_conditional_redirection_by_user_role"><?php _e('Conditional Redirection(User Role)','ARMember' ); ?></li>
                                                    <li data-label="<?php _e('Conditional Redirection','ARMember'); ?>" data-value="arm_conditional_redirection"><?php _e('Conditional Redirection','ARMember' ); ?></li>
                                                    <li data-label="<?php _e('Close Account','ARMember');?>" data-value="arm_close_account"><?php _e('Close Account', 'ARMember');?></li>
                                                    <li data-label="<?php _e('Current User Information','ARMember');?>" data-value="arm_greeting_message"><?php _e('Current User Information', 'ARMember');?></li>
                                                    <li data-label="<?php _e('Check If User In Trial Period','ARMember');?>" data-value="arm_check_if_user_in_trial"><?php _e('Check If User In Trial Period', 'ARMember');?></li>
                                                    <li data-label="<?php _e('User Badge','ARMember');?>" data-value="user_badge"><?php _e('User Badge', 'ARMember');?></li>
                                                    <li data-label="<?php _e('User Plan Information','ARMember');?>" data-value="arm_user_planinfo"><?php _e('User Plan Information', 'ARMember');?></li>
                                                    
                                                    <?php do_action('add_others_section_option_tinymce'); ?>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Transaction History','ARMember'); ?></th>
										<td>
											<ul class="arm_member_transaction_fields">
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_id" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_id" value="<?php _e('Transaction ID','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="invoice_id" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_invoice_id" value="<?php _e('Invoice ID','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="plan" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_plan" value="<?php _e('Plan','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_gateway" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_gateway" value="<?php _e('Payment Gateway','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_type" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_type" value="<?php _e('Payment Type','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_status" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_status" value="<?php _e('Transaction Status','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="amount" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_amount" value="<?php _e('Amount','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="used_coupon_code" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_used_coupon_code" value="<?php _e('Used coupon Code','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="used_coupon_discount" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_used_coupon_discount" value="<?php _e('Used coupon Discount','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_date" value="<?php _e('Payment Date','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="tax_percentage" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_tax_percentage" value="<?php _e('TAX Percentage','ARMember'); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="tax_amount" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_tax_amount" value="<?php _e('TAX Amount','ARMember'); ?>" />
												</li>
											</ul>
										</td>
									</tr>


									<tr>
                                        <th><?php _e('Display View Invoice Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="view_invoice_radio">
                                                <input type="radio" name="display_invoice_button" value="false" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="view_invoice_radio">
                                                <input type="radio" name="display_invoice_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>

                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('View Invoice Text','ARMember'); ?></th>
                                        <td><input type="text" name="view_invoice_text" value="<?php _e('View Invoice','ARMember'); ?>" /></td>
                                    </tr>
                                  
                                  
                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="view_invoice_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>

                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="view_invoice_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>



									<tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_transaction_opts' name="title" value="<?php _e('Transactions', 'ARMember');?>">
										</td>
									</tr>
                                                                        <tr>
										<th><?php _e('Records Per Page', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="message_no_record" value="<?php _e('There is no any Transactions found', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									
									<tr>
										<th><?php _e("Profile Fields",'ARMember'); ?></th>
										<td class="arm_view_profile_wrapper">
										<?php
                                            $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
					    if (!empty($dbProfileFields)):
						?>
    					   <ul class="arm_member_transaction_fields">
    					   
						<?php
						$i = 1;
						foreach ($dbProfileFields as $fieldMetaKey => $fieldOpt):
						    ?>
                                                <?php
                                                if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme','arm_captcha'))) {
                                                    continue;
                                                }
                                                $fchecked = '';
                                                if (in_array($fieldMetaKey, array('user_email', 'user_login', 'first_name', 'last_name'))) {
                                                    $fchecked = 'checked="checked"';
                                                }
                                                ?>



                                                
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_account_detail_fields" name="arm_account_detail_fields[]" value="<?php echo $fieldMetaKey;?>" <?php echo $fchecked;?> />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_account_detail_field_label_<?php echo $fieldMetaKey;?>" value="<?php echo stripslashes_deep($fieldOpt['label']);?>" />
												</li>
                                                 
                                                
						    <?php
						    $i++;
						endforeach;
						?>
                                            </ul>
                                            <?php endif; ?>
										</td>
									</tr>
                                    
                                    <?php if ($arm_social_feature->isSocialFeature): ?>
                                        <tr>
                                            <th><?php _e("Social Profile Fields",'ARMember'); ?></th>
                                            <td class="arm_view_profile_wrapper">
                                                <div class="arm_social_profile_fields_selection_wrapper">
                                                    <?php 
                                                    $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                                                    if (!empty($socialProfileFields)) {
                                                        foreach ($socialProfileFields as $spfKey => $spfLabel) {
                                                            ?><div class="arm_social_profile_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_spf_active_checkbox" value="<?php echo $spfKey;?>" name="social_fields[<?php echo $spfKey;?>]" id="arm_sprofile_<?php echo $spfKey;?>_status">
                                                                <label for="arm_sprofile_<?php echo $spfKey;?>_status"><?php echo $spfLabel;?></label>
                                                            </div><?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif;?>
								</table>
							</div>

						</form>
                                            <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" onsubmit="return false;">
							
                                                    
                                                    <div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                    <tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_current_membership_opts' name="title" value="<?php _e('Current Membership', 'ARMember');?>">
										</td>
									</tr>
                                                                        
                                                                        
                                                                        
                                                                        <tr>
										<th><?php _e('Select Setup', 'ARMember');?></th>
										<td>
											<?php $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` WHERE arm_setup_type=0 ");?>
											<input type="hidden" id="arm_shortcode_current_membership_setup_id" name="setup_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_current_membership_setup_id">
                                                                                                            <li data-label="<?php _e('Select Setup', 'ARMember');?>" data-value=""><?php _e('Select Setup', 'ARMember');?></li>
													<?php if(!empty($setups)):?>
														<?php foreach($setups as $ms):?>
														<li data-label="<?php echo stripslashes($ms->arm_setup_name);?>" data-value="<?php echo $ms->arm_setup_id;?>"><?php echo stripslashes($ms->arm_setup_name);?></li>
														<?php endforeach;?>
													<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                        
                                                                        
                                                                        
									<tr>
										<th><?php _e('Current Membership','ARMember'); ?></th>
										<td>
											<ul class="arm_member_current_membership_fields">

											<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_no" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_no" value="<?php _e('No.','ARMember'); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_is" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_is" value="<?php _e('Membership Plan','ARMember'); ?>" />
												</li>
												 <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_recurring_profile" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_recurring_profile" value="<?php _e('Plan Type','ARMember'); ?>" />
												</li>
                                                                                               
												 
                                                                                                <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_started_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_started_on" value="<?php _e('Starts On','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_expired_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_expired_on" value="<?php _e('Expires On','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_next_billing_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_next_billing_date" value="<?php _e('Cycle Date','ARMember'); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="action_button" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_action_button" value="<?php _e('Action','ARMember'); ?>" />
												</li>
                                                                                               
												
											</ul>
										</td>
									</tr>
									
                                                                        
                                                                        <tr>
                                        <th><?php _e('Display Renew Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Renew Text','ARMember'); ?></th>
                                        <td><input type="text" name="renew_text" value="<?php _e('Renew','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Make Payment Text','ARMember'); ?></th>
                                        <td><input type="text" name="make_payment_text" value="<?php _e('Make Payment','ARMember'); ?>" /></td>
                                    </tr>
                                  
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    
                                    
                                    <tr>
                                        <th><?php _e('Display Cancel Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked"/>
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Text','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_text" value="<?php _e('Cancel','ARMember'); ?>" /></td>
                                    </tr>
                                   
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Subscription Cancelled Message','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_message" value="<?php _e('Your subscription has been cancelled.','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Display Update Card Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Update Card Text','ARMember'); ?></th>
                                        <td><input type="text" name="update_card_text" value="<?php _e('Update Card','ARMember'); ?>" /></td>
                                    </tr>
                                    
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr>
										<th><?php _e('Trial Active Label', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="trial_active" value="<?php _e('trial active', 'ARMember');?>">
										</td>
									</tr>
                                    					<tr>
										<th><?php _e('Records Per Page', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="message_no_record" value="<?php _e('There is no membership found.', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>
                                                    
                                       

						</form>
                                      
                                            
                                            
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Select Set of Login Form', 'ARMember'); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_close_account" name="set_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
                                                                                                        <?php $setnames= $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` = 'login' GROUP BY arm_set_id ORDER BY arm_form_id ASC");?>
                                                                                                        <ul data-id="arm_shortcode_close_account" class="arm_shortcode_form_id_wrapper">
                                                                                                            <li data-label="<?php _e('Select Set','ARMember');?>" data-value=""><?php _e('Select Set', 'ARMember'); ?></li>
                                                                                                            <?php if(!empty($setnames)):?>
                                                                                                                <?php foreach($setnames as $sn): ?>
                                                                                                                    <li data-label="<?php echo stripslashes($sn->arm_set_name);?>" data-value="<?php echo $sn->arm_form_id;?>"><?php echo stripslashes($sn->arm_set_name);?></li>
                                                                                                                <?php endforeach;?>
                                                                                                            <?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
                                                                        <tr class="arm_close_account_custom_css_textarea arm_hidden">
                                                                            <th><?php _e('Custom Css', 'ARMember'); ?></th>
                                                                            <td>
                                                                                <textarea class="arm_popup_textarea" name="css" rows="4"></textarea><br/>
                                                                                <em>e.g. .classname { color: #ffffff;}</em>
                                                                            </td>
                                                                        </tr>
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('Display Information Based On', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_username_type" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_username_type">
													<li data-label="<?php _e('Select Type','ARMember');?>" data-value=""><?php _e('Select Type', 'ARMember');?></li>
													<li data-label="<?php _e('User ID','ARMember');?>" data-value="arm_userid"><?php _e('User ID', 'ARMember');?></li>
													<li data-label="<?php _e('Username','ARMember');?>" data-value="arm_username"><?php _e('Username', 'ARMember');?></li>
													<li data-label="<?php _e('Display Name','ARMember');?>" data-value="arm_displayname"><?php _e('Display Name', 'ARMember');?></li>
													<li data-label="<?php _e('Firstname Lastname','ARMember');?>" data-value="arm_firstname_lastname"><?php _e('Firstname Lastname', 'ARMember');?></li>
											<li data-label="<?php _e('User Plan','ARMember');?>" data-value="arm_user_plan"><?php _e('User Plan', 'ARMember');?></li>
													<li data-label="<?php _e('Avatar','ARMember');?>" data-value="arm_avatar"><?php _e('Avatar', 'ARMember');?></li>											
													<li data-label="<?php _e('Custom Meta','ARMember');?>" data-value="arm_usermeta"><?php _e('Custom Meta', 'ARMember');?></li>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
                                                                <tr class="arm_shortcode_other_opts_arm_greeting_message_arm_usermeta arm_hidden">
                                                                    <th><?php _e('Enter Usermeta Name', 'ARMember');?></th>
                                                                    <td>
                                                                        <input type="text" name="arm_custom_user_meta" id="arm_custom_user_meta" value="" />
                                                                    </td>
                                                                </tr>
								</table>
							</div>

						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('Display Content Based On', 'ARMember');?></th>
									<td>
										<input type="hidden" id="arm_shortcode_if_user_in_trial_or_not" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_if_user_in_trial_or_not">
													<li data-label="<?php _e('Select Type','ARMember');?>" data-value=""><?php _e('Select Type', 'ARMember');?></li>
													<li data-label="<?php _e('If User In Trial','ARMember');?>" data-value="arm_if_user_in_trial"><?php _e('If User <b>In</b> Trial Period', 'ARMember');?></li>
                                                                                                        <li data-label="<?php _e('If User Not In Trial','ARMember');?>" data-value="arm_not_if_user_in_trial"><?php _e('If User <b>Not In</b> Trial Period', 'ARMember');?></li>
													
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
								</table>
							</div>

						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_user_badge arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
									<th><?php _e('User Id', 'ARMember');?></th>
									<td>
										<input type="text" id="user_id" name="user_id" value="" class="type" />
										<span class="arm_blank_field_note"><?php esc_html_e("If User ID is empty then by default current logged in user's badges will be displayed.", "ARMember") ?></span>
									</td>
								</tr>
								</table>
							</div>

						</form>
                                            <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                        <tr>
                                                                            <th><?php _e('Select Membership Plan', 'ARMember');?></th>
                                                                            <td>
                                                                                <input type='hidden' class="arm_user_plan_change_input" name="plan_id" id="arm_user_plan_0" value=""/>
                                                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                                    <dd><ul data-id="arm_user_plan_0">
                                                                                    

<li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
 <?php    foreach ($all_plans as $p) {

                                                                                     echo '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p['arm_subscription_plan_id']. '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                                                                                        }
                                                                                        ?>
                                                                                        </ul></dd>
                                                                                </dl>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th><?php _e('Select Plan Information', 'ARMember');?></th>
                                                                            <td>
                                                                                <input type='hidden' class="arm_user_plan_change_input" name="plan_info" id="arm_user_plan_info" value="start_date"/>
                                                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                                    <dd><ul data-id="arm_user_plan_info">
                                                                                            <li data-label="<?php _e('Start Date', 'ARMember'); ?>" data-value="arm_start_plan"><?php _e('Start Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('End Date', 'ARMember'); ?>" data-value="arm_expire_plan"><?php _e('End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Trial Start Date', 'ARMember'); ?>" data-value="arm_trial_start"><?php _e('Trial Start Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Trial End Date', 'ARMember'); ?>" data-value="arm_trial_end"><?php _e('Trial End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Grace End Date', 'ARMember'); ?>" data-value="arm_grace_period_end"><?php _e('Grace End Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Paid By', 'ARMember'); ?>" data-value="arm_user_gateway"><?php _e('Paid By', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Completed Recurrence', 'ARMember'); ?>" data-value="arm_completed_recurring"><?php _e('Completed Recurrence', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Next Due Date', 'ARMember'); ?>" data-value="arm_next_due_payment"><?php _e('Next Due Date', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Payment Mode', 'ARMember'); ?>" data-value="arm_payment_mode"><?php _e('Payment Mode', 'ARMember'); ?></li>
                                                                                            <li data-label="<?php _e('Payment Cycle', 'ARMember'); ?>" data-value="arm_payment_cycle"><?php _e('Payment Cycle', 'ARMember'); ?></li>
                                                                                        </ul></dd>
                                                                                </dl>
                                                                            </td>
                                                                        </tr>
								</table>
							</div>

						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_conditional_redirection arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Condition','ARMember'); ?></th>
										<td>
										<input type="hidden" id="arm_conditional_redirection_condition" value="" name="condition"/>
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_conditional_redirection_condition">
													<li data-label="<?php _e('Select Option','ARMember');?>" data-value=""><?php _e('Select Option', 'ARMember');?></li>
													<li data-label="<?php _e('Having','ARMember');?>" data-value="having"><?php _e('Having', 'ARMember');?></li>
													<li data-label="<?php _e('Not Having','ARMember');?>" data-value="nothaving"><?php _e('Not Having', 'ARMember');?></li>
                                                                                                </ul>
											</dd>
										</dl>
                                                                            </td>
									</tr>
                                                                        <tr>
										<th><?php _e('Select Membership Plan','ARMember'); ?></th>
										<td>
											
                                                                                    <select name="arm_conditional_redirection_plans" class="arm_conditional_redirection_plans_select arm_chosen_selectbox" multiple data-placeholder="<?php _e('Select Plans', 'ARMember');?>" tabindex="-1" style="min-width: 300px;">
                                                                                            <?php 
                                                                                            if(!empty($all_plans))
                                                                                            {
                                                                                                    foreach($all_plans as $plan) {
                                                                                                            ?><option value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></option><?php
                                                                                                    }
                                                                                            }
                                                                                            ?>
                                                                                                            <option value="not_logged_in"><?php _e("Non-Logged In Users", 'ARMember'); ?></option>
                                                                                    </select>
                                                                                    <div style="margin-top: 5px;"><em>(<?php _e('Any of above selected plans.', 'ARMember'); ?>)</em></div>
										</td>
									</tr>
									<tr>
										<th><?php _e('Redirect URL', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_conditional_redirection_opts arm_conditional_redirection_redirecr_to' name="redirect_to" />
										 <br>
                                                                                    <em><?php _e('Please enter URL with http:// or https://.','ARMember'); ?></em>
                                                                                </td>
									</tr>
								</table>
							</div>
						</form>
                                                <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_conditional_redirection_by_user_role arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Condition','ARMember'); ?></th>
										<td>
										<input type="hidden" id="arm_conditional_redirection_by_user_role_condition" value="" name="condition"/>
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_conditional_redirection_by_user_role_condition">
													<li data-label="<?php _e('Select Option','ARMember');?>" data-value=""><?php _e('Select Option', 'ARMember');?></li>
													<li data-label="<?php _e('Having','ARMember');?>" data-value="having"><?php _e('Having', 'ARMember');?></li>
													<li data-label="<?php _e('Not Having','ARMember');?>" data-value="nothaving"><?php _e('Not Having', 'ARMember');?></li>
                                                                                                </ul>
											</dd>
										</dl>
                                                                            </td>
									</tr>
                                                                        <tr>
										<th><?php _e('Select User Role','ARMember'); ?></th>
										<td>
											
                                                                                    <select name="arm_conditional_redirection_by_user_role_roles" class="arm_conditional_redirection_by_user_role_roles_select arm_chosen_selectbox" multiple data-placeholder="<?php _e('Select Roles', 'ARMember');?>" tabindex="-1" style="min-width: 300px;">
                                                                                            <?php 
						if (!empty($all_roles)) {
                                                                                                    foreach($all_roles as $role_key => $role_name) {
                                                                                                            ?><option value="<?php echo $role_key;?>"><?php echo stripslashes($role_name);?></option><?php
                                                                                                    }
                                                                                            }
                                                                                            ?>
                                                                                                           
                                                                                    </select>
                                                                                    <div style="margin-top: 5px;"><em>(<?php _e('Any of above selected roles.', 'ARMember'); ?>)</em></div>
										</td>
									</tr>
									<tr>
										<th><?php _e('Redirect URL', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_conditional_redirection_opts arm_conditional_redirection_by_user_role_redirecr_to' name="redirect_to" />
										 <br>
                                                                                    <em><?php _e('Please enter URL with http:// or https://.','ARMember'); ?></em>

                                                                                </td>
									</tr>
								</table>
							</div>
						</form>
                                                 <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_login_history arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Login History','ARMember'); ?></th>
										<td>
											<ul class="arm_member_login_history_fields">
												<li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="user" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_user" value="<?php _e('User','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="logged_in_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_logged_in_date" value="<?php _e('Logged in date','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="logged_in_ip" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_logged_in_ip" value="<?php _e('Logged in IP','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="logged_in_using" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_logged_in_using" value="<?php _e('Logged in using','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="logged_out_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_logged_out_date" value="<?php _e('Logged out date','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_member_login_history_field_list">
													<label class="arm_member_login_history_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_login_history_fields" name="arm_login_history_fields[]" value="login_duration" checked="checked" />
													</label>
													<input type="text" class="arm_member_login_history_fields" name="arm_login_history_field_label_login_duration" value="<?php _e('Log in duration','ARMember'); ?>" />
												</li>
												
											</ul>
										</td>
									</tr>
									
								</table>
							</div>
                        </form>
                        <form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" onsubmit="return false;">
                        	<div class='arm_group_body'>
                        	</div>

						</form>
						
						<?php if($arm_pay_per_post_feature->isPayPerPostFeature):?> 
						<!-- start paid post current membership form -->
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_paid_post_current_membership arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
                                                                    <tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_current_membership_opts' name="title" value="<?php _e('Purchased Paid Post List', 'ARMember');?>">
										</td>
									</tr>
									<tr>
										<th><?php _e('Select Paid Post Setup', 'ARMember');?></th>
										<td>
											<?php $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` WHERE arm_setup_type=1 ");?>
											<input type="hidden" id="arm_shortcode_paid_post_current_membership_setup_id" name="setup_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_paid_post_current_membership_setup_id">
														<li data-label="<?php _e('Select Paid Post Setup', 'ARMember');?>" data-value=""><?php _e('Select Paid Post Setup', 'ARMember');?></li>
													<?php if(!empty($setups)):?>
														<?php foreach($setups as $ms):?>
														<li data-label="<?php echo stripslashes($ms->arm_setup_name);?>" data-value="<?php echo $ms->arm_setup_id;?>"><?php echo stripslashes($ms->arm_setup_name);?></li>
														<?php endforeach;?>
													<?php endif;?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>                                  
									<tr>
										<th><?php _e('Paid Post List Label(s)','ARMember'); ?></th>
										<td>
											<ul class="arm_member_paid_post_current_membership_fields">

											<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_no" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_no" value="<?php _e('No.','ARMember'); ?>" />
												</li>
												<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_is" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_is" value="<?php _e('Post Name','ARMember'); ?>" />
												</li>
												 <li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_recurring_profile" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_recurring_profile" value="<?php _e('Post Type','ARMember'); ?>" />
												</li>
												<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_started_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_started_on" value="<?php _e('Starts On','ARMember'); ?>" />
												</li>
												<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_expired_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_expired_on" value="<?php _e('Expires On','ARMember'); ?>" />
												</li>
												<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="current_membership_next_billing_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_current_membership_next_billing_date" value="<?php _e('Cycle Date','ARMember'); ?>" />
												</li>
												<li class="arm_member_paid_post_current_membership_field_list">
													<label class="arm_member_paid_post_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_fields[]" value="action_button" checked="checked" />
													</label>
													<input type="text" class="arm_member_paid_post_current_membership_fields" name="arm_paid_post_current_membership_field_label_action_button" value="<?php _e('Action','ARMember'); ?>" />
												</li>
											</ul>
										</td>
									</tr>                                
									<tr>
                                        <th><?php _e('Display Renew Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="renew_subscription_radio">
                                                <input type="radio" name="display_renew_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Renew Text','ARMember'); ?></th>
                                        <td><input type="text" name="renew_text" value="<?php _e('Renew','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Make Payment Text','ARMember'); ?></th>
                                        <td><input type="text" name="make_payment_text" value="<?php _e('Make Payment','ARMember'); ?>" /></td>
                                    </tr>
                                  
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="renew_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="renew_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    
                                    
                                    <tr>
                                        <th><?php _e('Display Cancel Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked"/>
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="cancel_subscription_radio">
                                                <input type="radio" name="display_cancel_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Text','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_text" value="<?php _e('Cancel','ARMember'); ?>" /></td>
                                    </tr>
                                   
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="cancel_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="cancel_subscription_btn_options">
                                        <th><?php _e('Subscription Cancelled Message','ARMember'); ?></th>
                                        <td><input type="text" name="cancel_message" value="<?php _e('Your subscription has been cancelled.','ARMember'); ?>" /></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Display Update Card Subscription Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="update_card_subscription_radio">
                                                <input type="radio" name="display_update_card_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Update Card Text','ARMember'); ?></th>
                                        <td><input type="text" name="update_card_text" value="<?php _e('Update Card','ARMember'); ?>" /></td>
                                    </tr>
                                    
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <tr class="update_card_subscription_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="update_card_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>
                                    <?php /*
                                    <tr style="display: none;">
										<th><?php _e('Trial Active Label', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="trial_active" value="<?php _e('trial active', 'ARMember');?>">
										</td>
									</tr>*/
									?>
                                    <tr>
										<th><?php _e('Records Per Page', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="message_no_record" value="<?php _e('There is no paid post found.', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>          
						</form>
						<!-- end paid post current membership form-->
						<!-- start paid post transactions form -->
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_paid_post_member_transaction arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php _e('Transaction History','ARMember'); ?></th>
										<td>
											<ul class="arm_paid_post_member_transaction_fields">
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_id" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_transaction_id" value="<?php _e('Transaction ID','ARMember'); ?>" />
												</li>
                                                                                                <li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="invoice_id" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_invoice_id" value="<?php _e('Invoice ID','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="plan" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_plan" value="<?php _e('Post Name','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="payment_gateway" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_payment_gateway" value="<?php _e('Payment Gateway','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="payment_type" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_payment_type" value="<?php _e('Payment Type','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_status" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_transaction_status" value="<?php _e('Transaction Status','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="amount" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_amount" value="<?php _e('Amount','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="used_coupon_code" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_used_coupon_code" value="<?php _e('Used coupon Code','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="used_coupon_discount" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_used_coupon_discount" value="<?php _e('Used coupon Discount','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="payment_date" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_payment_date" value="<?php _e('Payment Date','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="tax_percentage" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_tax_percentage" value="<?php _e('TAX Percentage','ARMember'); ?>" />
												</li>
												<li class="arm_paid_post_member_transaction_field_list">
													<label class="arm_paid_post_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_paid_post_member_transaction_fields" name="arm_transaction_fields[]" value="tax_amount" checked="checked" />
													</label>
													<input type="text" class="arm_paid_post_member_transaction_fields" name="arm_paid_post_transaction_field_label_tax_amount" value="<?php _e('TAX Amount','ARMember'); ?>" />
												</li>
											</ul>
										</td>
									</tr>


									<tr>
                                        <th><?php _e('Display View Invoice Button?','ARMember'); ?></th>
                                        <td>
                                            <label class="view_invoice_radio">
                                                <input type="radio" name="display_invoice_button" value="false" class="arm_iradio arm_shortcode_subscription_opt"  />
                                                <?php _e('No', 'ARMember'); ?>
                                            </label>
                                            <label class="view_invoice_radio">
                                                <input type="radio" name="display_invoice_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
                                                <?php _e('Yes','ARMember'); ?>
                                            </label>
                                        </td>
                                    </tr>

                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('View Invoice Text','ARMember'); ?></th>
                                        <td><input type="text" name="view_invoice_text" value="<?php _e('View Invoice','ARMember'); ?>" /></td>
                                    </tr>
                                  
                                  
                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('Button CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="view_invoice_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>

                                    <tr class="view_invoice_btn_options">
                                        <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                        <td>
                                            <textarea class="arm_popup_textarea" name="view_invoice_hover_css" rows="3"></textarea>
                                            <br/>
                                            <em>e.g. color: #ffffff;</em>
                                        </td>
                                    </tr>



									<tr>
										<th><?php _e('Title', 'ARMember');?></th>
										<td>
											<input type="text" class='arm_member_transaction_opts' name="title" value="<?php _e('Paid Post Transactions', 'ARMember');?>">
										</td>
									</tr>
                                                                        <tr>
										<th><?php _e('Records Per Page', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php _e('No Records Message', 'ARMember');?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="message_no_record" value="<?php _e('There is no any Transactions found', 'ARMember');?>">
										</td>
									</tr>
								</table>
							</div>

						</form>
						<!-- end paid post transactions form-->
						<?php endif; ?>

						<?php do_action('add_others_section_select_option_tinymce'); ?>
					</div>
					<div id="arm-conditionals" class="arm_tabgroup_content">
                                                <form class="arm_shortcode_arm_if_opts" onsubmit="return false;">
							<div class="arm_group_body">
								<?php 
								$armif_tags = $arm_shortcodes->armif_shortcode_tags_details();
								$armif_tags_desc = "";
								?>
								<?php if(!empty($armif_tags)):?>
								<table class="arm_shortcode_option_table arm_generator_armif">
									<tr>
										<td>
											<ul class="arm_shortcode_armif_tags">
												<?php $i=0;foreach($armif_tags as $tag => $tag_data):?>
												<li class="<?php echo ($i==0) ? 'activetag' : '';?>">
													<label>
														<input type="radio" name="armif" class="armif_tag_radio" data-tag="<?php echo $tag;?>" value="<?php echo $tag;?>()" <?php checked($i, 0);?>>
														<?php echo $tag;?>
													</label>
												</li>
												<?php 
												$desc_class = ($i==0) ? '' : 'arm_hide_block';
												$armif_tags_desc .= '<div class="armif_tag_desc_block armif_tag_' . $tag . ' '.$desc_class.'">';
												$armif_tags_desc .= '<table>';
												$armif_tags_desc .= '<tr>';
													$armif_tags_desc .= '<th><strong>'.__('Shortcode', 'ARMember').':</strong></th>';
                                                                                                        $armif_shortcode = '<td><code>[armif <strong>' . $tag . '()</strong>] '.__('Content Goes Here', 'ARMember').' [/armif]</code></td>';
													$armif_tags_desc .= apply_filters('arm_change_armif_shortcode_before_displayed',$armif_shortcode,$tag);
												$armif_tags_desc .= '</tr>';
                                                                                                $armif_tags_desc .= '<tr>';
													$armif_tags_desc .= '<th></th>';
                                                                                                        $armnotif_shortcode = '<td><code>[armNotif <strong>' . $tag . '()</strong>] '.__('Content Goes Here', 'ARMember').' [/armNotif]</code></td>';
													$armif_tags_desc .= apply_filters('arm_change_armnotif_shortcode_before_displayed',$armnotif_shortcode,$tag);
												$armif_tags_desc .= '</tr>';
												$armif_tags_desc .= '<tr class="armif_description">';
													$armif_tags_desc .= '<th><strong>'.__('Description', 'ARMember').': </strong></th>';
													$armif_tags_desc .= '<td><div style="padding-top:5px;">'.$tag_data['desc'].'</div></td>';
												$armif_tags_desc .= '</tr>';
												if (!empty($tag_data['args'])) {
												$armif_tags_desc .= '<tr class="armif_description">';
													$armif_tags_desc .= '<th><strong>'.__('Possible Arguments', 'ARMember').':</strong></th>';
													$armif_tags_desc .= '<td>';
														$armif_tags_desc .= '<ul>';
														$armif_tags_desc .= '<li><code>'.implode('</code></li><li><code>', $tag_data['args']).'</code></li>';
														$armif_tags_desc .= '</ul>';
													$armif_tags_desc .= '</td>';
												$armif_tags_desc .= '</tr>';
												}
												$armif_tags_desc .= '</table>';
                                                                                            $armif_tags_desc .= '<div style="text-align: right; color: red;">';
                                                                                                switch($tag)
                                                                                                {
                                                                                                    case 'is_admin':
                                                                                                    case 'is_network_admin':
                                                                                                    case 'is_blog_admin':
                                                                                                    case 'is_user_admin':
                                                                                                    case 'in_the_loop':
                                                                                                    case 'is_main_query':
                                                                                                    case 'is_post_type_archive':
                                                                                                        $armif_tags_desc .= '<span>'.__('This IF Condition can be used inside theme templates only.', 'ARMember').'</span>';
                                                                                                        break;
                                                                                                    
                                                                                                    default :
                                                                                                        $armif_tags_desc .='';
                                                                                                        break;
                                                                                                    
                                                                                                }
                                                                                                $armif_tags_desc .= '</div>'; 
												$armif_tags_desc .= '</div>';
												?>
												<?php $i++;endforeach;?>
											</ul>
											<div class="armif_tags_desc_container"><?php echo $armif_tags_desc;?></div>
										</td>
									</tr>
								</table>
								<?php endif;?>
							</div>
						</form>
					</div>
					<div id="arm-drip-content" class="arm_tabgroup_content">
						<form onsubmit="return false;" class="arm_shortcode_drip_rule_form">
							<div class="arm_group_body" style='min-height:200px;'>
								<?php $customDripRules = $arm_drip_rules->arm_get_custom_drip_rules();?>
								<?php if(!empty($customDripRules)):?>
								<div class="arm_custom_drip_rule_field">
									<label class="arm_custom_drip_rule_field_label"><?php _e('Select Drip Rule', 'ARMember');?></label>
									<div class="arm_custom_drip_rule_list_wrapper">
										<table class="arm_custom_drip_rule_list">
											<tr>
												<th style="width: 20px;"></th>
												<th style="min-width: 230px;"><?php _e('Drip Type', 'ARMember');?></th>
												<th style="min-width: 300px;"><?php _e('Plans', 'ARMember');?></th>
											</tr>
											<?php $i=0;foreach($customDripRules as $rule):?>
												<tr>
													<td><input type="radio" name="id" value="<?php echo $rule['arm_rule_id'];?>" class="arm_iradio" <?php echo ($i == 0) ? 'checked="checked"' : '';?>></td>
													<td><?php 
													$rule_type = isset($rule['arm_rule_type']) ? $rule['arm_rule_type'] : '';
													$rule_type_text = '--';
													switch ($rule_type) {
														case 'instant':
															$rule_type_text = __('Immediately', 'ARMember');
															break;
														case 'days':
															$days = isset($rule['rule_options']['days']) ? $rule['rule_options']['days'] : 0;
															$rule_type_text = __('After', 'ARMember') . ' ' . $days . ' ' . __('day(s) of subscription', 'ARMember');
															break;
														case 'dates':
															$rule_type_text = __('On specific date', 'ARMember');
															$from_date = isset($rule['rule_options']['from_date']) ? $rule['rule_options']['from_date'] : '';
															$to_date = isset($rule['rule_options']['to_date']) ? $rule['rule_options']['to_date'] : '';
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
													echo apply_filters('arm_change_drip_content_in_admin', $rule_type_text, $rule);
													?></td>
													<td><?php 
													$subs_plan_title = '--';
													if (!empty($rule['arm_rule_plans'])) {
														$plans_id = @explode(',', $rule['arm_rule_plans']);
														$subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
														$subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '--';
													}
													echo $subs_plan_title;
													?></td>
												</tr>
											<?php $i++;endforeach;?>
										</table>
									</div>
								</div>
								<div class="armclear"></div>
								<div class="arm_custom_drip_rule_field">
									<label class="arm_custom_drip_rule_field_label"><?php _e('Enter content here which will be dripped', 'ARMember');?></label>
									<?php 
									$armshortcodecontent_editor = array(
										'textarea_name' => 'armdripcontent',
										'media_buttons' => false,
										'textarea_rows' => 5,
										'default_editor' => 'html',
										'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
										'tinymce' => false,
									);
									wp_editor('', 'armdripcontent', $armshortcodecontent_editor);
									?>
								</div>
								<?php else:?>
								<div class="arm_custom_drip_rule_field">
									<label><?php _e('There is no any custom content drip rule found.', 'ARMember');?></label>
								</div>
								<?php endif;?>
							</div>
						</form>
					</div>
                                        <?php do_action('arm_shortcode_add_tab_content'); ?>
				</div>
                            
                            <!-- add form shortcode buttons -->
                            <div id="arm-forms_buttons" class="arm_tabgroup_content_buttons arm_show">
                                    <div class="arm_shortcode_form_opts arm_shortcode_form_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                                    </div>
                                            </div>
                                    </div>
                                    <div class="arm_shortcode_form_opts arm_shortcode_arm_social_login_opts arm_hidden" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" data-code="arm_social_login"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                                    </div>
                                            </div>
                                    </div>     

                                    <div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_form_select arm_hidden" style="position:relative;">
                                            <div class="popup_content_btn_wrapper">
                                                <button type="button" class="arm_shortcode_form_insert_btn arm_insrt_btn arm_shortcode_form_add_btn" id="arm_shortcode_form_select" data-code="arm_form"><?php _e('Add Shortcode', 'ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                                    <div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_edit_profile_opts" data-code="arm_edit_profile"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add setup shortcode buttons -->
                            <div id="arm-membership-setup_buttons" class="arm_tabgroup_content_buttons">      
                                    <div class="arm_group_footer" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_setup_btn arm_insrt_btn" id="arm_shortcode_membership_setup_opts" data-code="arm_setup"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add action shortcode buttons -->
                            <div id="arm-action-buttons_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember');?></a>
                                                    </div>
                                            </div>
                                    </div>                                

                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_social_login arm_hidden" style="">
                                        <div class="popup_content_btn_wrapper">
                                            <?php
                                                $disabled = '';
                                                if(empty($social_options)){
                                                    $disabled = 'disabled="disabled"';
                                                }
                                            ?>
                                            <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_social_login" data-code="arm_social_login" <?php echo $disabled; ?>><?php _e('Add Shortcode', 'ARMember');?></button>
                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                        </div>
                                    </div>                            
                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_logout" data-code="arm_logout"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_paid_post_buy_now arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_paid_post_buy_now" data-code="arm_paid_post_buy_now"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                                    <div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_action_button_opts_arm_cancel_membership" data-code="arm_cancel_membership"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add other shortcode buttons -->
                            <div id="arm-other_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_shortcode_other_opts arm_shortcode_other_opts_no_type" style="">
                                            <div class="arm_group_footer">
                                                    <div class="popup_content_btn_wrapper">
                                                            <button type="button" class="arm_insrt_btn" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                                    </div>
                                            </div>
                                    </div>     
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_member_transaction" data-code="arm_member_transaction"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>  
                                <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_user_planinfo_shortcode arm_insrt_btn" id="arm_shortcode_other_opts_arm_user_planinfo" data-code="arm_user_planinfo" disabled="disabled"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div> 
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_account_detail" data-code="arm_account_detail"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>      
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_current_membership_shortcode arm_insrt_btn" id="arm_shortcode_other_opts_arm_current_membership" data-code="arm_membership" disabled="disabled"><?php _e('Add Shortcode','ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel','ARMember'); ?></a>
                                            </div>
                                    </div>                                
                                                              
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_close_account_btn" id="arm_shortcode_other_opts_arm_close_account" data-code="arm_close_account"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>          
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_greeting_message" data-code="arm_greeting_message"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>   
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_check_if_user_in_trial" data-code="arm_if_user_in_trial_or_not"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>     
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_user_badge arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_user_badge" data-code="arm_user_badge"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>    
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_conditional_redirection arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_conditional_redirection_btn" id="arm_shortcode_other_opts_arm_conditional_redirection" data-code="arm_conditional_redirection"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>      
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_conditional_redirection_by_user_role arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_conditional_redirection_by_user_role_btn" id="arm_shortcode_other_opts_arm_conditional_redirection_by_user_role" data-code="arm_conditional_redirection_role"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div> 
                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_login_history arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_login_history" data-code="arm_login_history"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                

                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_last_login_history" data-code="arm_last_login_history"><?php _e('Add Shortcode', 'ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div> 

                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_paid_post_current_membership arm_hidden" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_paid_post_current_membership_shortcode arm_insrt_btn" id="arm_shortcode_other_opts_arm_paid_post_current_membership" data-code="arm_purchased_paid_post_list" disabled="disabled"><?php _e('Add Shortcode','ARMember'); ?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel','ARMember'); ?></a>
                                            </div>
                                    </div>  

                                    <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_paid_post_member_transaction arm_hidden">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_paid_post_member_transaction" data-code="arm_paid_post_member_transaction"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>      
                                    <?php do_action('arm_shortcode_add_other_tab_buttons'); ?>                            
                            </div>
                            <!-- add conditional shortcode buttons -->
                            <div id="arm-conditionals_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_group_footer">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_insrt_btn arm_insrt_btn_armif arm_shortcode_insert_btn" id="arm_shortcode_arm_if_opts" data-code="armif"><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>                                
                            </div>
                            <!-- add drip content shortcode buttons -->
                            <div id="arm-drip-content_buttons" class="arm_tabgroup_content_buttons">
                                    <div class="arm_group_footer arm_shortcode_drip_rule_form" style="">
                                            <div class="popup_content_btn_wrapper">
                                                    <button type="button" class="arm_shortcode_insert_drip_rule_btn arm_insrt_btn" id="arm_shortcode_drip_rule_form" data-code="arm_drip_content" <?php echo (count($customDripRules) > 0) ? '' : 'disabled="disabled"';?>><?php _e('Add Shortcode', 'ARMember');?></button>
                                                    <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
                                            </div>
                                    </div>
                            </div>
                            <?php do_action('arm_shortcode_add_tab_buttons'); ?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<!--********************/. Restrict Content Shortcodes ./********************-->
<div id="arm_restriction_shortcode_options_popup_wrapper" class="<?php echo $wrapperClass;?>">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php _e('Content Restriction Shortcode', 'ARMember');?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
                            <form onsubmit="return false;" class="arm_shortcode_rc_form">
				<div class="arm_group_body" style="padding-top: 25px;">
					<table class="arm_shortcode_option_table">
						<tr>
							<th><?php _e('Restriction Type', 'ARMember'); ?></th>
							<td>
								<input type="hidden" id="arm_restriction_type" name="type" value="hide" />
								<dl class="arm_selectbox column_level_dd">
									<dt style="width: 330px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_restriction_type">
											<li data-label="<?php _e('Hide content only for','ARMember');?>" data-value="hide"><?php _e('Hide content only for', 'ARMember');?></li>
											<li data-label="<?php _e('Show content only for','ARMember');?>" data-value="show"><?php _e('Show content only for', 'ARMember');?></li>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr>
							<th><?php _e('Target Users', 'ARMember'); ?></th>
							<td>
								<select name="plan" class="arm_chosen_selectbox" multiple data-placeholder="<?php _e('Everyone', 'ARMember');?>" tabindex="-1" style="width: 350px;">
									<option value="registered"><?php _e('Loggedin Users', 'ARMember');?></option>
									<option value="unregistered"><?php _e('Non Loggedin Users', 'ARMember');?></option>
									<?php 
									if(!empty($all_plans))
									{
										foreach($all_plans as $plan) {
											?><option value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></option><?php
										}
									}
									?>
									<option value="any_plan"><?php _e('Any Plan', 'ARMember');?></option>
								</select>
							</td>
						</tr>
<!--						<tr>
							<th><?php _e('Enter content here which will be restricted', 'ARMember'); ?></th>
							<td>
								<?php 
								$armshortcodecontent_editor = array(
									'textarea_name' => 'armshortcodecontent',
									'media_buttons' => false,
									'textarea_rows' => 5,
									'default_editor' => 'html',
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce' => true,
								);
								wp_editor('', 'armshortcodecontent', $armshortcodecontent_editor);
								?>
							</td>
						</tr>
						<tr>
							<th><?php _e('What to display when content is restricted', 'ARMember'); ?></th>
							<td>
								<?php 
								$armelse_message_editor = array(
									'textarea_name' => 'armelse_message',
									'media_buttons' => false,
									'textarea_rows' => 5,
									'default_editor' => 'html',
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce' => true,
								);
								wp_editor('', 'armelse_message', $armelse_message_editor);
								?>
							</td>
						</tr>-->
					</table>
				</div>
				<div class="arm_group_footer">
					<div class="popup_content_btn_wrapper">
						<button type="button" class="arm_shortcode_insert_rc_btn arm_insrt_btn" data-code="arm_restrict_content"><?php _e('Add Shortcode', 'ARMember');?></button>
						<a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)"><?php _e('Cancel', 'ARMember') ?></a>
					</div>
				</div>
			</form>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<script type="text/javascript">
jQuery(function($){
	if (typeof armICheckInit == "function") {
		armICheckInit();
	}
	/*For Chosen Select Boxes*/
    if (jQuery.isFunction(jQuery().chosen)) {
		jQuery(".arm_chosen_selectbox").chosen({
			no_results_text: "<?php _e('Oops, nothing found', 'ARMember');?>"
		});
    }
	if (jQuery.isFunction(jQuery().colpick))
    {
		jQuery('.arm_colorpicker').each(function (e) {
			var $arm_colorpicker = jQuery(this);
			var default_color = $arm_colorpicker.val();
			if (default_color == '') {
				default_color = '#000';
			}
			$arm_colorpicker.wrap('<label class="arm_colorpicker_label" style="background-color:' + default_color + '"></label>');
			$arm_colorpicker.colpick({
				layout: 'hex',
				submit: 0,
				colorScheme: 'dark',
				color: default_color,
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					jQuery(el).parent('.arm_colorpicker_label').css('background-color', '#' + hex);
					/*Fill the text box just if the color was set using the picker, and not the colpickSetColor function.*/
					if (!bySetColor) {
						jQuery(el).val('#' + hex);
					}
				}
			});
		});
    }
});
</script>