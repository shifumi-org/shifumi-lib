<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
$common_messages = $arm_global_settings->arm_get_all_common_message_settings();
$default_common_messages = $arm_global_settings->arm_default_common_messages();
if (!empty($common_messages)) {
	foreach ($common_messages as $key => $value) {
		$common_messages[$key] = esc_html(stripslashes($value));
	}
}
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<form  method="post" action="#" id="arm_common_message_settings" class="arm_common_message_settings arm_admin_form">
			<div class="page_sub_title"><?php _e('Login Related Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">								
				<tr class="form-field">
					<th class="arm-form-table-label">
						<label for="arm_user_not_exist"><?php _e('Incorrect Username/Email', 'ARMember'); ?></label>
						
					</th>
					<td class="arm-form-table-content" style="vertical-align: top;">
						<input type="text" name="arm_common_message_settings[arm_user_not_exist]" id="arm_user_not_exist" value="<?php echo (!empty($common_messages['arm_user_not_exist'])) ? $common_messages['arm_user_not_exist'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_password_login"><?php _e('Incorrect Password', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_password_login]" id="arm_invalid_password_login" value="<?php echo (!empty($common_messages['arm_invalid_password_login'])) ? $common_messages['arm_invalid_password_login'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_attempts_many_login_failed"><?php echo sprintf(__('Too Many Failed Login Attempts%sTemporary%s', 'ARMember'), '(',')'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_attempts_many_login_failed]" id="arm_attempts_many_login_failed" value="<?php echo (!empty($common_messages['arm_attempts_many_login_failed'])) ? $common_messages['arm_attempts_many_login_failed'] : ''; ?>"/>
						<br>
                        <span class="remained_login_attempts_notice">
                        <?php _e('To display the duration of locked account, use','ARMember'); ?><b> [LOCKDURATION] </b><?php _e('shortcode in a message.','ARMember'); ?>
                        </span>
					</td>
				</tr>
                <?php 

            		$arm_permanent_locked_message = (!isset($common_messages['arm_permanent_locked_message'])) ? $default_common_messages['arm_permanent_locked_message'] : $common_messages['arm_permanent_locked_message'];
                	
                ?>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_permanent_locked_message"><?php echo sprintf(__('Too Many Failed Login Attempts%sPermanent%s', 'ARMember'), '(',')'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_permanent_locked_message]" id="arm_permanent_locked_message" value="<?php echo $arm_permanent_locked_message; ?>"/>
						<br>
                        <span class="remained_login_attempts_notice">
                        <?php _e('To display the duration of locked account, use','ARMember'); ?><b> [LOCKDURATION] </b><?php _e('shortcode in a message.','ARMember'); ?>
                        </span>
					</td>
				</tr>

				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_attempts_login_failed"><?php _e('Remained Login Attempts Warning', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_attempts_login_failed]" id="arm_attempts_login_failed" value="<?php echo (!empty($common_messages['arm_attempts_login_failed'])) ? $common_messages['arm_attempts_login_failed'] : ''; ?>"/>
                                                <br>
                                                <span class="remained_login_attempts_notice">
                                                <?php _e('To display the number of remaining attempts use','ARMember'); ?>
                                                <b>[ATTEMPTS]</b>
                                                <?php _e('shortcode in a message.','ARMember'); ?>
                                                </span>
                                        </td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_armif_already_logged_in"><?php _e('User Already Logged In Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_armif_already_logged_in]" id="arm_armif_already_logged_in" value="<?php echo (!empty($common_messages['arm_armif_already_logged_in'])) ? $common_messages['arm_armif_already_logged_in'] : ''; ?>"/>
                                                <br/><span class="remained_login_attempts_notice"><?php _e('User already logged in message for modal forms ( Navigation Popup )','ARMember'); ?></span>
					</td>
				</tr>
                                
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_spam_msg"><?php _e('System Detected Spam Robots', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_spam_msg]" id="arm_spam_msg" value="<?php echo (!empty($common_messages['arm_spam_msg'])) ? $common_messages['arm_spam_msg'] : ''; ?>"/>
					</td>
				</tr>				
				<tr class="form-field">
					<th class="arm-form-table-label armember_general_setting_lbl"><label for="social_login_failed_msg"><?php _e('Login Failed Message for Social Connect', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[social_login_failed_msg]" id="social_login_failed_msg" value="<?php echo (!empty($common_messages['social_login_failed_msg'])) ? esc_html(stripslashes($common_messages['social_login_failed_msg'])) : '';?>"/>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Forgot Password Messages', 'ARMember'); ?></div>
			<table class="form-table">				
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_no_registered_email"><?php _e('Incorrect Username/Email', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_no_registered_email]" id="arm_no_registered_email" value="<?php echo (!empty($common_messages['arm_no_registered_email'])) ? $common_messages['arm_no_registered_email'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_reset_pass_not_allow"><?php _e('Password Reset Not Allowed', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_reset_pass_not_allow]" id="arm_reset_pass_not_allow" value="<?php echo (!empty($common_messages['arm_reset_pass_not_allow'])) ? $common_messages['arm_reset_pass_not_allow'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_email_not_sent"><?php _e('Email Not Sent', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_email_not_sent]" id="arm_email_not_sent" value="<?php echo (!empty($common_messages['arm_email_not_sent'])) ? $common_messages['arm_email_not_sent'] : ''; ?>"/>
					</td>
				</tr>				
			</table>
			<div class="armclear"></div>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Change Password Messages', 'ARMember'); ?></div>
			<table class="form-table">				                                
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_password_reset"><?php _e('Your password has been reset', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_password_reset]" id="arm_password_reset" value="<?php echo (!empty($common_messages['arm_password_reset'])) ? $common_messages['arm_password_reset'] : ''; ?>"/>
                                                <br>
                                                <span class="remained_login_attempts_notice">
                                                <?php _e('To display Login link use','ARMember'); ?>
                                                <b>[LOGINLINK]<?php _e('Login link label', 'ARMember');?>[/LOGINLINK]</b>
                                                <?php _e('shortcode in message.','ARMember'); ?>
                                                </span>
                                                <span class="arm_info_text">(<?php _e('This message will be used only when password is changed from password reset link sent in mail', 'ARMember');?>)</span>
                                        </td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_password_enter_new_pwd"><?php _e('Please Enter New Password', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_password_enter_new_pwd]" id="arm_password_enter_new_pwd" value="<?php echo (!empty($common_messages['arm_password_enter_new_pwd'])) ? $common_messages['arm_password_enter_new_pwd'] : ''; ?>"/>
                                                <span class="arm_info_text">(<?php _e('This message will be displayed in reset password form where user comes by clicking on reset password link', 'ARMember');?>)</span>
                                        </td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_password_reset_pwd_link_expired"><?php _e('Reset Password Link is invalid.', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_password_reset_pwd_link_expired]" id="arm_password_reset_pwd_link_expired" value="<?php echo (!empty($common_messages['arm_password_reset_pwd_link_expired'])) ? $common_messages['arm_password_reset_pwd_link_expired'] : ''; ?>"/>
                                                <span class="arm_info_text">(<?php _e('This message will be displayed on page where user comes by clicking expired reset password link', 'ARMember');?>)</span>
                                        </td>
				</tr>
			</table>
			<div class="armclear"></div>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Close Account Messages', 'ARMember'); ?></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_form_title_close_account"><?php _e('Form Title', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_form_title_close_account]" id="arm_form_title_close_account" value="<?php echo (!empty($common_messages['arm_form_title_close_account'])) ? $common_messages['arm_form_title_close_account'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_form_description_close_account"><?php _e('Form Description', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_form_description_close_account]" id="arm_form_description_close_account" value="<?php echo (!empty($common_messages['arm_form_description_close_account'])) ? $common_messages['arm_form_description_close_account'] : ''; ?>"/>
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_password_label_close_account"><?php _e('Password Field Label', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_password_label_close_account]" id="arm_password_label_close_account" value="<?php echo (!empty($common_messages['arm_password_label_close_account'])) ? $common_messages['arm_password_label_close_account'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_submit_btn_close_account"><?php _e('Submit Button Label', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_submit_btn_close_account]" id="arm_submit_btn_close_account" value="<?php echo (!empty($common_messages['arm_submit_btn_close_account'])) ? $common_messages['arm_submit_btn_close_account'] : ''; ?>"/>
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_blank_password_close_account"><?php _e('Empty Password Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_blank_password_close_account]" id="arm_blank_password_close_account" value="<?php echo (!empty($common_messages['arm_blank_password_close_account'])) ? $common_messages['arm_blank_password_close_account'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_password_close_account"><?php _e('Invalid Password Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_password_close_account]" id="arm_invalid_password_close_account" value="<?php echo (!empty($common_messages['arm_invalid_password_close_account'])) ? $common_messages['arm_invalid_password_close_account'] : ''; ?>"/>
					</td>
				</tr>
			</table>
			<div class="armclear"></div>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Registration / Edit Profile Labels', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_user_not_created"><?php _e('User Not Created', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_user_not_created]" id="arm_user_not_created" value="<?php echo (!empty($common_messages['arm_user_not_created'])) ? $common_messages['arm_user_not_created'] : ''; ?>"/>
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_username_exist"><?php _e('Username Already Exist', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_username_exist]" id="arm_username_exist" value="<?php echo (!empty($common_messages['arm_username_exist'])) ? $common_messages['arm_username_exist'] : ''; ?>"/>
					</td>
				</tr>

				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_email_exist"><?php _e('Email Already Exist', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_email_exist]" id="arm_email_exist" value="<?php echo (!empty($common_messages['arm_email_exist'])) ? $common_messages['arm_email_exist'] : ''; ?>"/>
					</td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_avtar_label"><?php _e('Avatar Field Label( Edit Profile )', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_avtar_label]" id="arm_email_exist" value="<?php echo (isset($common_messages['arm_avtar_label'])) ? $common_messages['arm_avtar_label'] : __('Avatar', 'ARMember'); ?>"/>
					</td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_profile_cover_label"><?php _e('Profile Cover Field Label( Edit Profile )', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_profile_cover_label]" id="arm_email_exist" value="<?php echo (isset($common_messages['arm_profile_cover_label'])) ? $common_messages['arm_profile_cover_label'] :  __('Profile Cover', 'ARMember'); ?>"/>
					</td>
				</tr>
                              
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_last_name_invalid"><?php _e('Minlength', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_minlength_invalid]" id="arm_minlength_invalid" value="<?php echo (!empty($common_messages['arm_minlength_invalid'])) ? $common_messages['arm_minlength_invalid'] : ''; ?>"/>
                                                <br>
                                                <span class="remained_login_attempts_notice">
                                                <?php _e('To display allowed minimum characters use','ARMember'); ?>
                                                <b>[MINVALUE]</b>
                                                <?php _e('shortcode in message.','ARMember'); ?>
                                                </span>
					</td>
				</tr>
                                </tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_last_name_invalid"><?php _e('Maxlength', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_maxlength_invalid]" id="arm_maxlength_invalid" value="<?php echo (!empty($common_messages['arm_maxlength_invalid'])) ? $common_messages['arm_maxlength_invalid'] : ''; ?>"/>
                                                <br>
                                                <span class="remained_login_attempts_notice">
                                                <?php _e('To display allowed maximum characters','ARMember'); ?>
                                                <b>[MAXVALUE]</b>
                                                <?php _e('shortcode in message.','ARMember'); ?>
                                                </span>
					</td>
				</tr>
			</table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Account Related Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_expire_activation_link"><?php _e('Expire Activation Link', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_expire_activation_link]" id="arm_expire_activation_link" value="<?php echo (!empty($common_messages['arm_expire_activation_link'])) ? $common_messages['arm_expire_activation_link'] : ''; ?>"/>
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_already_active_account"><?php _e('Account Activated', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_already_active_account]" id="arm_already_active_account" value="<?php echo (!empty($common_messages['arm_already_active_account'])) ? $common_messages['arm_already_active_account'] : ''; ?>"/>
					</td>
				</tr>
                
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_account_pending"><?php _e('Account Pending', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_account_pending]" id="arm_account_pending" value="<?php echo (!empty($common_messages['arm_account_pending'])) ? $common_messages['arm_account_pending'] : ''; ?>"/>
					</td>
				</tr>
				
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_already_inactive_account"><?php _e('Account Inactivated', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_account_inactive]" id="arm_already_inactive_account" value="<?php echo (!empty($common_messages['arm_account_inactive'])) ? $common_messages['arm_account_inactive'] : ''; ?>"/>
					</td>
				</tr>
				
			</table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Payment Related Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_payment_fail_stripe"><?php _e('Payment Fail (Stripe)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_payment_fail_stripe]" id="arm_payment_fail_stripe" value="<?php echo (!empty($common_messages['arm_payment_fail_stripe'])) ? $common_messages['arm_payment_fail_stripe'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_payment_fail_authorize_net"><?php _e('Payment Fail (Authorize.net)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_payment_fail_authorize_net]" id="arm_payment_fail_authorize_net" value="<?php echo (!empty($common_messages['arm_payment_fail_authorize_net'])) ? $common_messages['arm_payment_fail_authorize_net'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_payment_fail_2checkout"><?php _e('Payment Fail (2Checkout)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_payment_fail_2checkout]" id="arm_payment_fail_2checkout" value="<?php echo (!empty($common_messages['arm_payment_fail_2checkout'])) ? $common_messages['arm_payment_fail_2checkout'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_credit_card"><?php _e('Invalid Credit Card Detail', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_credit_card]" id="arm_invalid_credit_card" value="<?php echo (!empty($common_messages['arm_invalid_credit_card'])) ? $common_messages['arm_invalid_credit_card'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_unauthorized_credit_card"><?php _e('Credit Card Not Authorized', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_unauthorized_credit_card]" id="arm_unauthorized_credit_card" value="<?php echo (!empty($common_messages['arm_unauthorized_credit_card'])) ? $common_messages['arm_unauthorized_credit_card'] : ''; ?>"/>
                                                <br/><span class="remained_login_attempts_notice">(<?php _e('in case of Authorize.net payment gateway only.','ARMember'); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_credit_card_declined"><?php _e('Credit Card Declined', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_credit_card_declined]" id="arm_credit_card_declined" value="<?php echo (!empty($common_messages['arm_credit_card_declined'])) ? $common_messages['arm_credit_card_declined'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_blank_expire_month"><?php _e('Blank Expiry Month', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_blank_expire_month]" id="arm_blank_expire_month" value="<?php echo (!empty($common_messages['arm_blank_expire_month'])) ? $common_messages['arm_blank_expire_month'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_blank_expire_year"><?php _e('Blank Expiry Year', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_blank_expire_year]" id="arm_blank_expire_year" value="<?php echo (!empty($common_messages['arm_blank_expire_year'])) ? $common_messages['arm_blank_expire_year'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_blank_cvc_number"><?php _e('Blank CVC Number', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_blank_cvc_number]" id="arm_blank_cvc_number" value="<?php echo (!empty($common_messages['arm_blank_cvc_number'])) ? $common_messages['arm_blank_cvc_number'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_blank_credit_card_number"><?php _e('Blank Credit Card Number', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_blank_credit_card_number]" id="arm_blank_credit_card_number" value="<?php echo (!empty($common_messages['arm_blank_credit_card_number'])) ? $common_messages['arm_blank_credit_card_number'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_plan_select"><?php _e('Invalid Plan Selected', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_plan_select]" id="arm_invalid_plan_select" value="<?php echo (!empty($common_messages['arm_invalid_plan_select'])) ? $common_messages['arm_invalid_plan_select'] : ''; ?>"/>
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label armember_general_setting_lbl"><label for="arm_no_select_payment_geteway"><?php _e('No Gateway Selected For Paid Plan', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_no_select_payment_geteway]" id="arm_no_select_payment_geteway" value="<?php echo (!empty($common_messages['arm_no_select_payment_geteway'])) ? $common_messages['arm_no_select_payment_geteway'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_inactive_payment_gateway"><?php _e('Payment Gateway Inactive', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_inactive_payment_gateway]" id="arm_inactive_payment_gateway" value="<?php echo (!empty($common_messages['arm_inactive_payment_gateway'])) ? $common_messages['arm_inactive_payment_gateway'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_do_not_allow_pending_payment_bank_transfer"><?php _e('Do not allow pending payment (Bank Transfer)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_do_not_allow_pending_payment_bank_transfer]" id="arm_do_not_allow_pending_payment_bank_transfer" value="<?php echo (!empty($common_messages['arm_do_not_allow_pending_payment_bank_transfer'])) ? $common_messages['arm_do_not_allow_pending_payment_bank_transfer'] : esc_html(stripslashes($default_common_messages['arm_do_not_allow_pending_payment_bank_transfer']));; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_payment_gateway_subscription_failed_error_msg"><?php _e('Payment Gateway Subscription Failed Error', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_payment_gateway_subscription_failed_error_msg]" id="arm_payment_gateway_subscription_failed_error_msg" value="<?php echo (!empty($common_messages['arm_payment_gateway_subscription_failed_error_msg'])) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : __('Error of payment subscription failed','ARMember').' ' ; ?>"/>
					</td>
				</tr>
		<?php do_action('arm_payment_related_common_message',$common_messages); ?>
			</table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Coupon Related Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_success_coupon"><?php _e('Coupon Applied Successfully', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_success_coupon]" id="arm_success_coupon" value="<?php echo (!empty($common_messages['arm_success_coupon'])) ? $common_messages['arm_success_coupon'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_empty_coupon"><?php _e('Coupon Empty', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_empty_coupon]" id="arm_empty_coupon" value="<?php echo (!empty($common_messages['arm_empty_coupon'])) ? $common_messages['arm_empty_coupon'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_coupon_expire"><?php _e('Coupon Expired', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_coupon_expire]" id="arm_coupon_expire" value="<?php echo (!empty($common_messages['arm_coupon_expire'])) ? $common_messages['arm_coupon_expire'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_coupon"><?php _e('Invalid Coupon', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_coupon]" id="arm_invalid_coupon" value="<?php echo (!empty($common_messages['arm_invalid_coupon'])) ? $common_messages['arm_invalid_coupon'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_invalid_coupon_plan"><?php _e('Invalid Coupon For Plan', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_invalid_coupon_plan]" id="arm_invalid_coupon_plan" value="<?php echo (!empty($common_messages['arm_invalid_coupon_plan'])) ? $common_messages['arm_invalid_coupon_plan'] : ''; ?>"/>
					</td>
				</tr>
			</table>
                        <div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Profile/Directory Related Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				 <tr class="form-field">
					<th class="arm-form-table-label"><label for="profile_directory_upload_cover_photo"><?php _e('Upload Cover Photo', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[profile_directory_upload_cover_photo]" id="profile_directory_upload_cover_photo" value="<?php echo (!empty($common_messages['profile_directory_upload_cover_photo'])) ? $common_messages['profile_directory_upload_cover_photo'] : ''; ?>"/>
					</td>
				</tr>
                                <tr class="form-field">
					<th class="arm-form-table-label"><label for="profile_directory_remove_cover_photo"><?php _e('Remove Cover Photo', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[profile_directory_remove_cover_photo]" id="profile_directory_remove_cover_photo" value="<?php echo (!empty($common_messages['profile_directory_remove_cover_photo'])) ? $common_messages['profile_directory_remove_cover_photo'] : ''; ?>"/>
					</td>
				</tr>
                                <tr class="form-field">
                    <th class="arm-form-table-label"><label for="profile_template_upload_profile_photo"><?php _e('Upload Profile Photo', 'ARMember'); ?></label></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="arm_common_message_settings[profile_template_upload_profile_photo]" id="profile_template_upload_profile_photo" value="<?php echo (!empty($common_messages['profile_template_upload_profile_photo'])) ? $common_messages['profile_template_upload_profile_photo'] : ''; ?>"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><label for="profile_template_remove_profile_photo"><?php _e('Remove Profile Photo', 'ARMember'); ?></label></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="arm_common_message_settings[profile_template_remove_profile_photo]" id="profile_template_remove_profile_photo" value="<?php echo (!empty($common_messages['profile_template_remove_profile_photo'])) ? $common_messages['profile_template_remove_profile_photo'] : ''; ?>"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><label for="profile_template_search_filter_title"><?php _e('Search Filter Title', 'ARMember'); ?></label></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="arm_common_message_settings[profile_template_search_filter_title]" id="profile_template_search_filter_title" value="<?php echo (!empty($common_messages['profile_template_search_filter_title'])) ? $common_messages['profile_template_search_filter_title'] : __('Search Members', 'ARMember'); ?>"/>
                    </td>
                </tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="directory_sort_by_field"><?php _e('Sort By (Directory Filter)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[directory_sort_by_field]" id="directory_sort_by_field" value="<?php echo (!empty($common_messages['directory_sort_by_field'])) ? $common_messages['directory_sort_by_field'] : __('Sort By', 'ARMember'); ?>"/>
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="directory_sort_by_alphabatically"><?php _e('Alphabatically (Directory Filter)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[directory_sort_by_alphabatically]" id="directory_sort_by_alphabatically" value="<?php echo (!empty($common_messages['directory_sort_by_alphabatically'])) ? $common_messages['directory_sort_by_alphabatically'] : ''; ?>"/>
					</td>
				</tr> 
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="directory_sort_by_recently_joined"><?php _e('Recently Joined (Directory Filter)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[directory_sort_by_recently_joined]" id="directory_sort_by_recently_joined" value="<?php echo (!empty($common_messages['directory_sort_by_recently_joined'])) ? $common_messages['directory_sort_by_recently_joined'] : ''; ?>"/>
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_profile_member_since"><?php _e('Member Since', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_profile_member_since]" id="arm_profile_member_since" value="<?php echo (isset($common_messages['arm_profile_member_since'])) ? $common_messages['arm_profile_member_since'] : __('Member Since', 'ARMember'); ?>"/>
					</td>
				</tr>
                <tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_profile_view_profile"><?php _e('View Profile', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_profile_view_profile]" id="arm_profile_view_profile" value="<?php echo (isset($common_messages['arm_profile_view_profile'])) ? $common_messages['arm_profile_view_profile'] : __('View Profile', 'ARMember'); ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_directory_search_placeholder"><?php _e('Directory Search Placeholder', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_directory_search_placeholder]" id="arm_directory_search_placeholder" value="<?php echo (isset($common_messages['arm_directory_search_placeholder'])) ? $common_messages['arm_directory_search_placeholder'] : __('Search', 'ARMember'); ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_directory_search_button"><?php _e('Directory Search Button', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_directory_search_button]" id="arm_directory_search_button" value="<?php echo (isset($common_messages['arm_directory_search_button'])) ? $common_messages['arm_directory_search_button'] : __('Search', 'ARMember'); ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_directory_reset_button"><?php _e('Directory Reset Button', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_directory_reset_button]" id="arm_directory_reset_button" value="<?php echo (isset($common_messages['arm_directory_reset_button'])) ? $common_messages['arm_directory_reset_button'] : __('Reset', 'ARMember'); ?>"/>
					</td>
				</tr>
            </table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php _e('Miscellaneous Messages', 'ARMember'); ?></div>
			<div class="armclear"></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_general_msg"><?php _e('General Message', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_general_msg]" id="arm_general_msg" value="<?php echo (!empty($common_messages['arm_general_msg'])) ? $common_messages['arm_general_msg'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_search_result_found"><?php _e('No Search Result Found', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_search_result_found]" id="arm_search_result_found" value="<?php echo (!empty($common_messages['arm_search_result_found'])) ? $common_messages['arm_search_result_found'] : ''; ?>"/>
					</td>
				</tr>
				
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_armif_invalid_argument"><?php _e('Invalid Arguments (ARM If Shortcode)', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_armif_invalid_argument]" id="arm_armif_invalid_argument" value="<?php echo (!empty($common_messages['arm_armif_invalid_argument'])) ? $common_messages['arm_armif_invalid_argument'] : ''; ?>"/>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><label for="arm_recptcha_invalid"><?php _e('Invalid reCAPTCHA', 'ARMember'); ?></label></th>
					<td class="arm-form-table-content">
						<input type="text" name="arm_common_message_settings[arm_recptcha_invalid]" id="arm_recptcha_invalid" value="<?php echo (!empty($common_messages['arm_recptcha_invalid'])) ? $common_messages['arm_recptcha_invalid'] : 'Google reCAPTCHA Invalid or Expired. Please reload page and try again.'; ?>"/>
					</td>
				</tr>                    
            </table>
                       
			<?php do_action('arm_after_common_messages_settings_html', $common_messages);?>
			<div class="arm_submit_btn_container">
				<button class="arm_save_btn arm_common_message_settings_btn" type="submit" id="arm_common_message_settings_btn" name="arm_common_message_settings_btn"><?php _e('Save', 'ARMember');?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" style="position:relative;top:8px;display:none;" width="24" height="24" />
			</div>
			<?php wp_nonce_field( 'arm_wp_nonce' );?>
		</form>
		<div class="armclear"></div>
	</div>
</div>