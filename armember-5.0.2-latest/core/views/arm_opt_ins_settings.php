<?php
global $wpdb, $ARMember, $arm_members_class, $arm_global_settings, $arm_email_settings;
$all_email_settings = $arm_email_settings->arm_get_all_email_settings();

$arm_aweber_redirect_url = ARM_HOME_URL.'/?arm_redirect_aweber=1';
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<form  method="post" action="#" id="arm_opt_ins_options" class="arm_opt_ins_options arm_admin_form" onsubmit="return false;">
			<?php $emailTools = (!empty($all_email_settings['arm_email_tools'])) ? $all_email_settings['arm_email_tools'] : array();?>
			<table class="form-table">
				<?php 
				$aweber_api_key = (!empty($emailTools['aweber']['api_key'])) ? $emailTools['aweber']['api_key'] : '';
				$aweber_status = (!empty($emailTools['aweber']['status'])) ? $emailTools['aweber']['status'] : 0;
				$aweberList = (!empty($emailTools['aweber']['list'])) ? $emailTools['aweber']['list'] : array();
				if (empty($aweberList)) {
					$emailTools['aweber']['list_id'] = '';
				}
				?>
				<tr class="form-field">
					<td colspan="2">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL?>/aweber.png" alt="AWeber">
						<input type="hidden" name="arm_email_tools[aweber][status]" id="arm_aweber_status" value="<?php echo $aweber_status;?>">
					</td>
				</tr>
				<tr class="form-field arm_aweber_api_key_fields" style="<?php echo ($aweber_status == 1) ? 'display:none;' : '';?>">
					<th></th>
					<td>
						<input id="arm_aweber_consumer_key" type="hidden" name="arm_email_tools[aweber][consumer_key]" value="<?php echo MEMBERSHIP_AWEBER_CONSUMER_KEY; ?>" >
						<input id="arm_aweber_consumer_secret" type="hidden" name="arm_email_tools[aweber][consumer_secret]" value="<?php echo MEMBERSHIP_AWEBER_CONSUMER_SECRET; ?>" >

						<button class="armemailaddbtn" type="button" name="continue" onclick="aweber_continue('<?php echo $arm_aweber_redirect_url; ?>');"><?php _e('Authorize Account', 'ARMember') ?></button>
					</td>
				</tr>
				<tr class="form-field arm_aweber_api_lists" style="<?php echo ($aweber_status == 1) ? '' : 'display:none;';?>">
					<th class="arm-form-table-label"><label><?php _e('List ID', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<div class="arm_aweber_api_list_wrapper">
							<input type="hidden" id="aweber_list_name" name="arm_email_tools[aweber][list_id]" value="<?php echo (!empty($emailTools['aweber']['list_id'])) ? $emailTools['aweber']['list_id'] : ''; ?>" />
							<dl class="arm_selectbox column_level_dd" id="arm_aweber_dl" <?php echo (empty($aweberList))? 'disabled="disabled"' : '';?>>
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="aweber_list_name" id="arm_aweber_list">
										<?php if(!empty($aweberList)):?>
										<?php foreach($aweberList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
										<?php endif;?>
									</ul>
								</dd>
							</dl>
						</div>
						<span id="arm_aweber_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span id="arm_aweber_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_aweber_action_link"  class="arlinks arm_padding_left_5 arm_margin_top_10">					
							<span id="arm_mailchimp_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'aweber');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_mailchimp_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'aweber');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/mailchimp.png" alt="AWeber"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php 
						$mailchimp_api_key = (!empty($emailTools['mailchimp']['api_key'])) ? $emailTools['mailchimp']['api_key'] : '';
						$mailchimp_status = (!empty($emailTools['mailchimp']['status'])) ? $emailTools['mailchimp']['status'] : 0;
						$mailchimpList = (!empty($emailTools['mailchimp']['list'])) ? $emailTools['mailchimp']['list'] : array();
                                                $mailchimp_double_opt_in = (!empty($emailTools['mailchimp']['enable_double_opt_in'])) ? $emailTools['mailchimp']['enable_double_opt_in'] : 0;
                                                
						if (empty($mailchimpList)) {
							$emailTools['mailchimp']['list_id'] = '';
						}
						?>
						<input id="arm_mailchimp_api_key" type="text" name="arm_email_tools[mailchimp][api_key]" value="<?php echo $mailchimp_api_key;?>" onkeyup="show_email_tool_verify_btn('mailchimp');">
						<span id="arm_mailchimp_link" <?php if ($mailchimp_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('mailchimp', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_mailchimp_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span>    
						<span id="arm_mailchimp_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_mailchimp_api_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<input type="hidden" name="arm_email_tools[mailchimp][status]" id="arm_mailchimp_status" value="<?php echo $mailchimp_status;?>">
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('List ID', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="mailchimp_list_name" name="arm_email_tools[mailchimp][list_id]" value="<?php echo (!empty($emailTools['mailchimp']['list_id'])) ? $emailTools['mailchimp']['list_id'] : ''; ?>" />
						<dl id="arm_mailchimp_dl" class="arm_selectbox column_level_dd <?php if ($mailchimp_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="mailchimp_list_name" id="arm_mailchimp_list">
									<?php if(!empty($mailchimpList)) :?>
										<?php foreach($mailchimpList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_mailchimp_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_mailchimp_action_link " class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($mailchimp_status == 0) { ?>display:none;<?php } ?>" class="arlinks">					
							<span id="arm_mailchimp_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'mailchimp');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_mailchimp_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'mailchimp');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
                                <tr class="form-field">
                                    <th></th>
                                    <td><input type="checkbox" name="arm_email_tools[mailchimp][enable_double_opt_in]" id="arm_mailchimp_enable_double_opt_in" class="arm_icheckbox" <?php checked($mailchimp_double_opt_in, 1, true);?> value="1"><label for="arm_mailchimp_enable_double_opt_in"><?php _e('Enable double opt-in', 'ARMember'); ?></label></td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/constant-contact.png" alt="AWeber"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php 
						$constant_api_key = (!empty($emailTools['constant']['api_key'])) ? $emailTools['constant']['api_key'] : '';
						$constant_access_token = (!empty($emailTools['constant']['access_token'])) ? $emailTools['constant']['access_token'] : '';
						$constant_status = (!empty($emailTools['constant']['status'])) ? $emailTools['constant']['status'] : 0;
						$constantList = (!empty($emailTools['constant']['list'])) ? $emailTools['constant']['list'] : array();
						if (empty($constantList)) {
							$emailTools['constant']['list_id'] = '';
						}
						?>
						<input id="arm_constant_api_key" type="text" name="arm_email_tools[constant][api_key]" value="<?php echo $constant_api_key;?>" onkeyup="show_email_tool_verify_btn('constant');">
						<span class="error arm_invalid" id="arm_constant_api_key_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Access Token', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input id="arm_constant_access_token" type="text" name="arm_email_tools[constant][access_token]" value="<?php echo $constant_access_token;?>" onkeyup="show_email_tool_verify_btn('constant');">
						<span id="arm_constant_link" <?php if ($constant_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('constant', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_constant_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span> 
						<span id="arm_constant_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_constant_access_token_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<input type="hidden" name="arm_email_tools[constant][status]" id="arm_constant_status" value="<?php echo $constant_status;?>">
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('List Name', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="constant_list_name" name="arm_email_tools[constant][list_id]" value="<?php echo (!empty($emailTools['constant']['list_id'])) ? $emailTools['constant']['list_id'] : ''; ?>" />
						<dl id="arm_constant_dl" class="arm_selectbox column_level_dd <?php if ($constant_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="constant_list_name" id="arm_constant_list">
									<?php if(!empty($constantList)) :?>
										<?php foreach($constantList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_constant_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_constant_action_link" class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($constant_status == 0) { ?>display:none;<?php } ?>" class="arlinks">
							<span id="arm_constant_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'constant');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_constant_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'constant');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
			</table>
                        <div class="arm_solid_divider"></div>
                        <table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/getresponse.png" alt="Get Response"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php
						$getresponse_api_key = (!empty($emailTools['getresponse']['api_key'])) ? $emailTools['getresponse']['api_key'] : '';
						$getresponse_status = (!empty($emailTools['getresponse']['status'])) ? $emailTools['getresponse']['status'] : 0;
						$getresponseList = (!empty($emailTools['getresponse']['list'])) ? $emailTools['getresponse']['list'] : array();
						if (empty($getresponseList)) {
							$emailTools['getresponse']['list_id'] = '';
						}
						?>
						<input id="arm_getresponse_api_key" type="text" name="arm_email_tools[getresponse][api_key]" value="<?php echo $getresponse_api_key;?>" onkeyup="show_email_tool_verify_btn('getresponse');">
                                                <span id="arm_getresponse_link" <?php if ($getresponse_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('getresponse', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_getresponse_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span> 
						<span id="arm_getresponse_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_getresponse_api_key_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
                                                <input type="hidden" name="arm_email_tools[getresponse][status]" id="arm_getresponse_status" value="<?php echo $getresponse_status;?>">
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Campaign Name', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="getresponse_list_name" name="arm_email_tools[getresponse][list_id]" value="<?php echo (!empty($emailTools['getresponse']['list_id'])) ? $emailTools['getresponse']['list_id'] : ''; ?>" />
						<dl id="arm_getresponse_dl" class="arm_selectbox column_level_dd <?php if ($getresponse_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="getresponse_list_name" id="arm_getresponse_list">
									<?php if(!empty($getresponseList)) :?>
										<?php foreach($getresponseList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_getresponse_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_getresponse_action_link" class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($getresponse_status == 0) { ?>display:none;<?php } ?>" class="arlinks">					
							<span id="arm_getresponse_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'getresponse');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_getresponse_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'getresponse');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/madmimi.png" alt="Mad Mimi"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Email', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php 
						$madmimi_api_key = (!empty($emailTools['madmimi']['api_key'])) ? $emailTools['madmimi']['api_key'] : '';
						$madmimi_email = (!empty($emailTools['madmimi']['email'])) ? $emailTools['madmimi']['email'] : '';
						$madmimi_status = (!empty($emailTools['madmimi']['status'])) ? $emailTools['madmimi']['status'] : 0;
						$madmimiList = (!empty($emailTools['madmimi']['list'])) ? $emailTools['madmimi']['list'] : array();
						if (empty($madmimiList)) {
							$emailTools['madmimi']['list_id'] = '';
						}
						?>
						<input id="arm_madmimi_email" type="text" name="arm_email_tools[madmimi][email]" value="<?php echo $madmimi_email;?>" onkeyup="show_email_tool_verify_btn('madmimi');">
						<span class="error arm_invalid" id="arm_madmimi_email_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input id="arm_madmimi_api_key" type="text" name="arm_email_tools[madmimi][api_key]" value="<?php echo $madmimi_api_key;?>" onkeyup="show_email_tool_verify_btn('madmimi');">
						<span class="error arm_invalid" id="arm_madmimi_api_key_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<span id="arm_madmimi_link" <?php if ($madmimi_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('madmimi', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_madmimi_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span> 
						<span id="arm_madmimi_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_madmimi_access_token_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<input type="hidden" name="arm_email_tools[madmimi][status]" id="arm_madmimi_status" value="<?php echo $madmimi_status;?>">
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('List Name', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="madmimi_list_name" name="arm_email_tools[madmimi][list_id]" value="<?php echo (!empty($emailTools['madmimi']['list_id'])) ? $emailTools['madmimi']['list_id'] : ''; ?>" />
						<dl id="arm_madmimi_dl" class="arm_selectbox column_level_dd <?php if ($madmimi_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="madmimi_list_name" id="arm_madmimi_list">
									<?php if(!empty($madmimiList)) :?>
										<?php foreach($madmimiList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_madmimi_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_madmimi_action_link" class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($madmimi_status == 0) { ?>display:none;<?php } ?>" class="arlinks">					
							<span id="arm_madmimi_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'madmimi');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_madmimi_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'madmimi');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/mailer_lite.png" alt="Mailer Lite"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php 
						$mailerlite_api_key = (!empty($emailTools['mailerlite']['api_key'])) ? $emailTools['mailerlite']['api_key'] : '';
						$mailerlite_status = (!empty($emailTools['mailerlite']['status'])) ? $emailTools['mailerlite']['status'] : 0;
						$mailerliteGroups = (!empty($emailTools['mailerlite']['list'])) ? $emailTools['mailerlite']['list'] : array();
						if (empty($mailerliteGroups)) {
							$emailTools['mailerlite']['list_id'] = '';
						}
						?>
						<input id="arm_mailerlite_api_key" type="text" name="arm_email_tools[mailerlite][api_key]" value="<?php echo $mailerlite_api_key;?>" onkeyup="show_email_tool_verify_btn('mailerlite');">
						<span class="error arm_invalid" id="arm_mailerlite_api_key_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<span id="arm_mailerlite_link" <?php if ($mailerlite_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('mailerlite', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_mailerlite_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span> 
						<span id="arm_mailerlite_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_mailerlite_access_token_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<input type="hidden" name="arm_email_tools[mailerlite][status]" id="arm_mailerlite_status" value="<?php echo $mailerlite_status;?>">
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Group Name', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="mailerlite_list_name" name="arm_email_tools[mailerlite][list_id]" value="<?php echo (!empty($emailTools['mailerlite']['list_id'])) ? $emailTools['mailerlite']['list_id'] : ''; ?>" />
						<dl id="arm_mailerlite_dl" class="arm_selectbox column_level_dd <?php if ($mailerlite_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="mailerlite_list_name" id="arm_mailerlite_list">
									<?php if(!empty($mailerliteGroups)) :?>
										<?php foreach($mailerliteGroups as $mailerliteGroupslist):?>
										<li data-label="<?php echo $mailerliteGroupslist['name'];?>" data-value="<?php echo $mailerliteGroupslist['id'];?>"><?php echo $mailerliteGroupslist['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_mailerlite_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_mailerlite_action_link" class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($mailerlite_status == 0) { ?>display:none;<?php } ?>" class="arlinks">					
							<span id="arm_mailerlite_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'mailerlite');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_mailerlite_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'mailerlite');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
			</table>
			<?php 
			if (is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php')) { 
				$mailster_double_opt_in = (!empty($emailTools['mailster']['enable_double_opt_in'])) ? $emailTools['mailster']['enable_double_opt_in'] : 0;
			?>
			<div class="arm_solid_divider"></div>
			<table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/mailster.png" alt="Mailster"></td>
				</tr>

                <tr class="form-field">
                    <th></th>
                    <td><input type="checkbox" name="arm_email_tools[mailster][enable_double_opt_in]" id="arm_mailster_enable_double_opt_in" class="arm_icheckbox" <?php checked($mailster_double_opt_in, 1, true);?> value="1"><label for="arm_mailster_enable_double_opt_in"><?php _e('Enable double opt-in', 'ARMember'); ?></label></td>
				</tr>
			</table>
			<?php } ?>


			<div class="arm_solid_divider"></div>
		    <table class="form-table">
				<tr class="form-field">
					<td colspan="2"><img src="<?php echo MEMBERSHIP_IMAGES_URL?>/sendinblue.png" alt="<?php _e('Sendinblue', 'ARMember'); ?>"></td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('API Key', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<?php 
						$sendinblue_api_key = (!empty($emailTools['sendinblue']['api_key'])) ? $emailTools['sendinblue']['api_key'] : '';
						$sendinblue_status = (!empty($emailTools['sendinblue']['status'])) ? $emailTools['sendinblue']['status'] : 0;
						$sendinblueList = (!empty($emailTools['sendinblue']['list'])) ? $emailTools['sendinblue']['list'] : array();
                                                
						if (empty($sendinblueList)) {
							$emailTools['sendinblue']['list_id'] = '';
						}
						?>
						<input id="arm_sendinblue_api_key" type="text" name="arm_email_tools[sendinblue][api_key]" value="<?php echo $sendinblue_api_key;?>" onkeyup="show_email_tool_verify_btn('sendinblue');">
						<span id="arm_sendinblue_link" <?php if ($sendinblue_status == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_email_tool('sendinblue', '0');"><?php _e('Verify', 'ARMember'); ?></a></span>
						<span id="arm_sendinblue_verify" class="arm_success_msg" style="display:none;"><?php _e('Verified', 'ARMember'); ?></span>    
						<span id="arm_sendinblue_error" class="arm_error_msg" style="display:none;"><?php _e('Not Verified', 'ARMember'); ?></span>
						<span class="error arm_invalid" id="arm_sendinblue_api_error" style="display: none;"><?php _e('This field cannot be blank.', 'ARMember');?></span>
						<input type="hidden" name="arm_email_tools[sendinblue][status]" id="arm_sendinblue_status" value="<?php echo $sendinblue_status;?>">
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('List ID', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="hidden" id="sendinblue_list_name" name="arm_email_tools[sendinblue][list_id]" value="<?php echo (!empty($emailTools['sendinblue']['list_id'])) ? $emailTools['sendinblue']['list_id'] : ''; ?>" />
						<dl id="arm_sendinblue_dl" class="arm_selectbox column_level_dd <?php if ($sendinblue_status == 0) { ?>disabled<?php } ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="sendinblue_list_name" id="arm_sendinblue_list">
									<?php if(!empty($sendinblueList)) :?>
										<?php foreach($sendinblueList as $list):?>
										<li data-label="<?php echo $list['name'];?>" data-value="<?php echo $list['id'];?>"><?php echo $list['name'];?></li>
										<?php endforeach;?>
									<?php endif;?>
								</ul>
							</dd>
						</dl>
						<span id="arm_sendinblue_refresh" class="arm_success_msg" style="display:none;"><?php _e('Refreshed', 'ARMember'); ?></span>
						<div id="arm_sendinblue_action_link" class="arm_padding_left_5 arm_margin_top_10" style="<?php if ($sendinblue_status == 0) { ?>display:none;<?php } ?>" class="arlinks">					
							<span id="arm_sendinblue_link_refresh"><a href="javascript:void(0);" onclick="refresh_email_tool('refresh', 'sendinblue');"><?php _e('Refresh List', 'ARMember'); ?></a></span>
							&nbsp;	&nbsp;	&nbsp;	&nbsp;
							<span id="arm_sendinblue_link_delete"><a href="javascript:void(0);" onclick="refresh_email_tool('delete', 'sendinblue');"><?php _e('Delete Configuration', 'ARMember'); ?></a></span>
						</div>
					</td>
				</tr>
            </table> 
			
			<?php

            do_action('arm_add_new_optins');
                        
			$customEmailTools = apply_filters('arm_add_new_optin_settings', '', $emailTools);
			echo $customEmailTools;
			?>
			
			
			<table class="form-table"><tr><td colspan="2">&nbsp;</td></tr></table>
			<div class="arm_submit_btn_container">
				<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_opt_ins_options_btn" type="submit" id="arm_opt_ins_options_btn" name="arm_opt_ins_options_btn"><?php _e('Save', 'ARMember') ?></button>
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
		<div class="armclear"></div>
	</div>
</div>