<?php
	global $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
	$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
	$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
	$arm_default_date = date_i18n($arm_common_date_format);
?>

<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<div class="arm_debug_container">
			<form id="arm_debug_form" method="POST" action="#" id="" enctype="multipart/form-data" class="arm_admin_form">
				<div class="page_sub_title"><?php _e('Payment Gateway Debug Log Settings', 'ARMember'); ?></div>
				<div class="armclear"></div>
				<table class="form-table">
					<?php
						foreach($payment_gateways as $payment_gateway_key => $payment_gateway_val)
						{
							$arm_gateway_name = $payment_gateway_val['gateway_name'];
							$arm_debug_logs = (!empty($payment_gateway_val['payment_debug_logs']) && $payment_gateway_val['payment_debug_logs'] == '1') ? 'checked="checked"' : '';
					?>
							<tr class="form-field">
								<th class="arm-form-table-label"><?php echo $arm_gateway_name; ?></th>
								<td class="arm-form-table-content">
									<div class="armswitch arm_payment_setting_switch">
										<input type="checkbox" id="arm_<?php echo strtolower($arm_gateway_name);?>_debug_log" <?php echo $arm_debug_logs;?> value="1" class="armswitch_input arm_debug_mode_switch" name="payment_gateway_settings[<?php echo strtolower($payment_gateway_key);?>][debug_log]" data-switch_key="<?php echo strtolower($payment_gateway_key);?>"/>
										<label for="arm_<?php echo strtolower($arm_gateway_name);?>_debug_log" class="armswitch_label"></label>
									</div>
									<?php 
										if(!empty($arm_debug_logs)){
									?>
											<div class="arm_debug_switch_<?php echo $payment_gateway_key; ?>  arm_debug_log_action_container" style="margin-top: 10px;">
									<?php
										} else {
									?>
											<div class="arm_debug_switch_<?php echo $payment_gateway_key; ?> arm_debug_log_action_container" style="margin-top: 10px; display: none;">
									<?php
										}
									?>
										<a href="javascript:void(0)" onclick="arm_view_payment_debug_logs('<?php echo $payment_gateway_key; ?>', '<?php echo $arm_gateway_name; ?>')"><?php _e('View Log', 'ARMember'); ?></a>
										
										<a href="javascript:void(0)" style="margin-left: 10px;" onclick="arm_download_payment_debug_logs('<?php echo $payment_gateway_key; ?>')">
											<?php _e('Download Log', 'ARMember'); ?>
										</a>
										<div class='arm_confirm_box arm_download_confirm_box' id='arm_download_confirm_box_<?php echo $payment_gateway_key; ?>'>
											<div class='arm_confirm_box_body'>
												<div class='arm_confirm_box_arrow'></div>
												<div class='arm_confirm_box_text'>
													<div class="arm_download_duration_selection">
														<label class="arm_select_duration_label"><?php _e('Select log duration to download', 'ARMember'); ?></label>
														<input type="hidden" id="arm_download_duration" name="action1" value="7" />
														<dl class="arm_selectbox column_level_dd">
															<dt style="width: 120px;">
																<span><?php _e('Last 1 Week','ARMember');?></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_download_duration">
																	<li data-label="<?php _e('Last 1 Day', 'ARMember');?>" data-value="1"><?php _e('Last 1 Day', 'ARMember');?></li>

																	<li data-label="<?php _e('Last 3 Days', 'ARMember');?>" data-value="3"><?php _e('Last 3 Days', 'ARMember');?></li>

																	<li data-label="<?php _e('Last 1 Week','ARMember');?>" data-value="7"><?php _e('Last 1 Week','ARMember');?></li>

																	<li data-label="<?php _e('Last 2 Weeks', 'ARMember');?>" data-value="15"><?php _e('Last 2 Weeks', 'ARMember');?></li>

																	<li data-label="<?php _e('Last Month', 'ARMember');?>" data-value="30"><?php _e('Last Month', 'ARMember');?></li>

																	<li data-label="<?php _e('All', 'ARMember');?>" data-value="all"><?php _e('All', 'ARMember');?></li>

																	<li data-label="<?php _e('Custom', 'ARMember');?>" data-value="custom"><?php _e('Custom', 'ARMember');?></li>
																</ul>
															</dd>
														</dl>
													</div>
													<form id="arm_download_custom_duration_<?php echo $payment_gateway_key; ?>_form">
														<div class="arm_download_custom_duration_div">
											                <div class="arm_datatable_filter_item" style="margin-left: 0;">
											                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php _e('Start Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
											                </div>
											                <div class="arm_datatable_filter_item">
											                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php _e('End Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
											                </div>
										            	</div>
										            </form>
													<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_debug_log_btn' data-selected_key='<?php echo $payment_gateway_key; ?>'><?php _e('Download', 'ARMember'); ?></button>
												</div>
											</div>
										</div>
										<a href="javascript:void(0)" class="arm_clear_debug_log" onclick="arm_clear_payment_debug_logs('<?php echo $payment_gateway_key; ?>')" style="margin-left: 10px;"><?php _e('Clear Log', 'ARMember'); ?></a>
										<?php
											$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box($payment_gateway_key, __("Are you sure you want to clear debug logs?", 'ARMember'), 'arm_clear_debug_log');
											echo $arm_debug_clear_log;
										?>
									</div>
								</td>
							</tr>
					<?php
						}

						$arm_add_new_debug_log_gateway = "";
						$arm_add_new_debug_log_gateway = apply_filters('arm_add_payment_debug_log_field', $arm_add_new_debug_log_gateway, $payment_gateways);
						echo $arm_add_new_debug_log_gateway;
					?>
				</table>
				<?php 
					if($arm_email_settings->isOptInsFeature)
					{
						$arm_is_optins_log_enabled = get_option('arm_optins_debug_log');
						$arm_optins_debug_log = ($arm_is_optins_log_enabled) ? 'checked=checked' : '';
				?>
						<br>
						<div class="page_sub_title"><?php _e('Opt-ins Debug Log Settings', 'ARMember'); ?></div>
						<div class="armclear"></div>
						<table class="form-table">
							<tr class="form-field">
								<th class="arm-form-table-label"><?php _e('Enable Opt-ins Debug Logs', 'ARMember'); ?></th>
								<td class="arm-form-table-content">
									<div class="armswitch arm_payment_setting_switch">
										<input type="checkbox" id="arm_optins_debug_log" <?php echo $arm_optins_debug_log; ?> value="1" class="armswitch_input arm_debug_mode_switch" name="arm_optins_debug_log" data-switch_key="optins"/>
										<label for="arm_optins_debug_log" class="armswitch_label"></label>
									</div>
									<div class="arm_debug_switch_optins arm_debug_log_action_container" style="margin-top: 10px; <?php if(empty($arm_optins_debug_log)) { echo "display:none;"; } ?>">

										<a href="javascript:void(0)" onclick="arm_view_general_debug_logs('optins', 'All')"><?php _e('View Log', 'ARMember'); ?></a>
										<a href="javascript:void(0)" onclick="arm_download_general_debug_logs('optins')" style="margin-left: 10px;"><?php _e('Download Log', 'ARMember'); ?></a>
										<div class='arm_confirm_box arm_general_debug_download_confirm_box' id='arm_general_debug_download_confirm_box_optins'>
											<div class='arm_confirm_box_body'>
												<div class='arm_confirm_box_arrow'></div>
												<div class='arm_confirm_box_text'>
													<div class="arm_download_duration_selection">
														<label class="arm_select_duration_label"><?php _e('Select log duration to download', 'ARMember'); ?></label>
														<input type="hidden" id="arm_general_download_duration" name="action1" value="7" />
														<dl class="arm_selectbox column_level_dd">
															<dt style="width: 120px;">
																<span><?php _e('Last 1 Week','ARMember');?></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_general_download_duration">
																	<li data-label="<?php _e('Last 1 Day', 'ARMember');?>" data-value="1"><?php _e('Last 1 Day', 'ARMember');?></li>

																	<li data-label="<?php _e('Last 3 Days', 'ARMember');?>" data-value="3"><?php _e('Last 3 Days', 'ARMember');?></li>

																	<li data-label="<?php _e('Last 1 Week','ARMember');?>" data-value="7"><?php _e('Last 1 Week','ARMember');?></li>

																	<li data-label="<?php _e('Last 2 Weeks', 'ARMember');?>" data-value="15"><?php _e('Last 2 Weeks', 'ARMember');?></li>

																	<li data-label="<?php _e('Last Month', 'ARMember');?>" data-value="30"><?php _e('Last Month', 'ARMember');?></li>

																	<li data-label="<?php _e('All', 'ARMember');?>" data-value="all"><?php _e('All', 'ARMember');?></li>

																	<li data-label="<?php _e('Custom', 'ARMember');?>" data-value="custom"><?php _e('Custom', 'ARMember');?></li>
																</ul>
															</dd>
														</dl>
													</div>
													<form id="arm_general_debug_download_custom_duration_optins_form">
														<div class="arm_download_custom_duration_div">
											                <div class="arm_datatable_filter_item" style="margin-left: 0;">
											                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php _e('Start Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
											                </div>
											                <div class="arm_datatable_filter_item">
											                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php _e('End Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
											                </div>
										            	</div>
										            </form>
													<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_general_debug_log_btn' data-selected_key='optins'><?php _e('Download', 'ARMember'); ?></button>
												</div>
											</div>
										</div>
										<a href="javascript:void(0)" class="arm_clear_debug_log" onclick="arm_clear_general_debug_logs('optins')" style="margin-left: 10px;"><?php _e('Clear Log', 'ARMember'); ?></a>
										<?php
											$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box('optins', __("Are you sure you want to clear debug logs?", 'ARMember'), 'arm_clear_debug_log');
											echo $arm_debug_clear_log;
										?>
									</div>
								</td>
							</tr>

						<?php
							$arm_general_debug_log_details = "";
							$arm_general_debug_log_details = apply_filters('arm_add_general_debug_log_details', $arm_general_debug_log_details);
						?>
						</table>
				<?php
					}
				?>
				<br>
				<div class="page_sub_title"><?php _e('Cron Debug Log Settings', 'ARMember'); ?></div>
				<div class="armclear"></div>
				<table class="form-table">
					<?php
						$arm_is_cron_log_enabled = get_option('arm_cron_debug_log');
						$arm_cron_debug_log = ($arm_is_cron_log_enabled) ? 'checked=checked' : '';
					?>
					<tr class="form-field">
						<th class="arm-form-table-label"><?php _e('Enable Cron Debug Logs', 'ARMember'); ?></th>
						<td class="arm-form-table-content">
							<div class="armswitch arm_payment_setting_switch">
								<input type="checkbox" id="arm_cron_debug_log" <?php echo $arm_cron_debug_log; ?> value="1" class="armswitch_input arm_debug_mode_switch" name="arm_cron_debug_log" data-switch_key="cron"/>
								<label for="arm_cron_debug_log" class="armswitch_label"></label>
							</div>
							<?php
								if(!empty($arm_cron_debug_log)){
							?>
									<div class="arm_debug_switch_cron arm_debug_log_action_container" style="margin-top: 10px;">
							<?php
								} else {
							?>
									<div class="arm_debug_switch_cron arm_debug_log_action_container" style="margin-top: 10px; display: none;">
							<?php
								}
							?>
								<a href="javascript:void(0)" onclick="arm_view_general_debug_logs('cron', 'Cron')"><?php _e('View Log', 'ARMember'); ?></a>
								<a href="javascript:void(0)" onclick="arm_download_general_debug_logs('cron')" style="margin-left: 10px;"><?php _e('Download Log', 'ARMember'); ?></a>
								<div class='arm_confirm_box arm_general_debug_download_confirm_box' id='arm_general_debug_download_confirm_box_cron'>
									<div class='arm_confirm_box_body'>
										<div class='arm_confirm_box_arrow'></div>
										<div class='arm_confirm_box_text'>
											<div class="arm_download_duration_selection">
												<label class="arm_select_duration_label"><?php _e('Select log duration to download', 'ARMember'); ?></label>
												<input type="hidden" id="arm_general_download_duration" name="action1" value="7" />
												<dl class="arm_selectbox column_level_dd">
													<dt style="width: 120px;">
														<span><?php _e('Last 1 Week','ARMember');?></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_general_download_duration">
															<li data-label="<?php _e('Last 1 Day', 'ARMember');?>" data-value="1"><?php _e('Last 1 Day', 'ARMember');?></li>

															<li data-label="<?php _e('Last 3 Days', 'ARMember');?>" data-value="3"><?php _e('Last 3 Days', 'ARMember');?></li>

															<li data-label="<?php _e('Last 1 Week','ARMember');?>" data-value="7"><?php _e('Last 1 Week','ARMember');?></li>

															<li data-label="<?php _e('Last 2 Weeks', 'ARMember');?>" data-value="15"><?php _e('Last 2 Weeks', 'ARMember');?></li>

															<li data-label="<?php _e('Last Month', 'ARMember');?>" data-value="30"><?php _e('Last Month', 'ARMember');?></li>

															<li data-label="<?php _e('All', 'ARMember');?>" data-value="all"><?php _e('All', 'ARMember');?></li>

															<li data-label="<?php _e('Custom', 'ARMember');?>" data-value="custom"><?php _e('Custom', 'ARMember');?></li>
														</ul>
													</dd>
												</dl>
											</div>
											<form id="arm_general_debug_download_custom_duration_cron_form">
												<div class="arm_download_custom_duration_div">
									                <div class="arm_datatable_filter_item" style="margin-left: 0;">
									                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php _e('Start Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
									                </div>
									                <div class="arm_datatable_filter_item">
									                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php _e('End Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" value="<?php echo $arm_default_date; ?>" />
									                </div>
								            	</div>
								            </form>
											<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_general_debug_log_btn' data-selected_key='cron'><?php _e('Download', 'ARMember'); ?></button>
										</div>
									</div>
								</div>
								<a href="javascript:void(0)" class="arm_clear_debug_log" onclick="arm_clear_general_debug_logs('cron')" style="margin-left: 10px;"><?php _e('Clear Log', 'ARMember'); ?></a>
								<?php
									$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box('cron', __("Are you sure you want to clear debug logs?", 'ARMember'), 'arm_clear_debug_log');
									echo $arm_debug_clear_log;
								?>
							</div>
						</td>
					</tr>		
				</table>
				<table class="form-table">
					<tr class="form-field">
						<th class="arm-form-table-label"></th>
						<td class="arm-form-table-content"></td>
					</tr>
				</table>
				<div class="arm_submit_btn_container">
					<button id="arm_save_debug_logs_btn" class="arm_save_btn" name="arm_save_debug_logs" value="arm_save_debug_logs" type="submit" style="min-width: 120px;"><?php _e('Save', 'ARMember');?></button>
				</div>
			</form>
		</div>
	</div>
</div>


<div class="arm_view_debug_payment_logs popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>">
	<div>
		<div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_view_debug_payment_logs_close_btn"></span>
            <span class="add_rule_content"><?php _e('Debug Logs', 'ARMember'); ?> (<span class="view_payment_log_key"></span>)</span>
        </div>
        <div class="popup_content_text">
        	<div class="arm_debug_payment_log_loader" style="width: 100%; margin: 45px auto; text-align: center;">
        		<img src="<?php echo MEMBERSHIP_IMAGES_URL."/arm_loader.gif"; ?>">
        	</div>
        	<div class="arm_view_payment_debug_log armPageContainer" data-arm_selected_gateway=""></div>
        	<div class="armclear"></div>
        </div>
        <div class="armclear"></div>
	</div>
</div>



<div class="arm_view_debug_general_logs popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>">
	<div>
		<div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_view_debug_general_logs_close_btn"></span>
            <span class="add_rule_content"><?php _e('Debug Logs', 'ARMember'); ?> (<span class="view_general_log_key"></span>)</span>
        </div>
        <div class="popup_content_text">
        	
        	<div class="arm_debug_general_log_loader" style="width: 100%; margin: 45px auto; text-align: center;">
        		<img src="<?php echo MEMBERSHIP_IMAGES_URL."/arm_loader.gif"; ?>">
        	</div>
        	<div class="arm_view_general_debug_log armPageContainer" data-arm_selected_gateway=""></div>
        </div>
        <div class="armclear"></div>
	</div>
</div>