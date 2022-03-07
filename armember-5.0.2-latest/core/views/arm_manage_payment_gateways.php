<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_payment_gateways;
$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$arm_general_settings = $arm_all_global_settings['general_settings'];
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$all_currency = $arm_payment_gateways->arm_get_all_currencies();
$global_currency_symbol = $all_currency[strtoupper($global_currency)];
$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
$arm_paypal_currency = $arm_payment_gateways->currency['paypal'];
$arm_stripe_currency = $arm_payment_gateways->currency['stripe'];
$arm_authorize_net_currency = $arm_payment_gateways->currency['authorize_net'];
$arm_2checkout_currency = $arm_payment_gateways->currency['2checkout'];
$arm_bank_transafer_currency = $arm_payment_gateways->currency['bank_transfer'];
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content" id="content_wrapper">
		<form method="post" action="#" id="arm_payment_geteway_form" class="arm_payment_geteway_form arm_admin_form">
		<?php $i=0;foreach ($payment_gateways as $gateway_name => $gateway_options): ?>
			<?php 
			$gateway_options['status'] = isset($gateway_options['status']) ? $gateway_options['status'] : 0;
			$arm_status_switchChecked = ($gateway_options['status'] == '1') ? 'checked="checked"' : '';
			$disabled_field_attr = ($gateway_options['status']=='1') ? '' : 'disabled="disabled"';
			$readonly_field_attr = ($gateway_options['status']=='1') ? '' : 'readonly="readonly"';
			?>
			<?php if ($i != 0): ?><div class="arm_solid_divider"></div><?php endif;?>
			<?php $i++;?>
			<div class="page_sub_title">
				<?php echo $gateway_options['gateway_name'];?> 
				<?php 
				$titleTooltip = '';
				$apiCallbackUrlInfo = '';
				switch ($gateway_name) {
					case 'paypal':
						$titleTooltip = __('Click below links for more details about how to get API Credentials:', 'ARMember').'<br><a href="https://developer.paypal.com/tools/sandbox/accounts/#create-a-business-sandbox-account" target="_blank">'.__('Sandbox API Detail', 'ARMember').'</a>, <a href="https://developer.paypal.com/docs/archive/paypal-here/sdk-dev/going-live/" target="_blank">'.__('Live API Detail', 'ARMember').'</a>';
						break;
					case 'stripe':
						$titleTooltip = __('Your API keys are located in your', 'ARMember').' <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">'.__('account settings', 'ARMember').'</a>. '.__('To get more details, please refer this', 'ARMember').' <a href="https://support.stripe.com/questions/where-do-i-find-my-api-keys" target="_blank">'.__('document', 'ARMember').'</a>';
						$apiCallbackUrl = $arm_global_settings->add_query_arg("arm-listener", "arm_stripe_api", ARM_HOME_URL . "/");
                                                
						$apiCallbackUrlInfo = __('Please make sure you have set following callback URL in your Stripe account.', 'ARMember');
                        $callbackTooltip = __('To get more information about how to set Web Hook URL from your Stripe account, please refer this', 'ARMember').' <a href="'.MEMBERSHIP_DOCUMENTATION_PAYMENT_GATEWAY_STRIPE_URL.'" target="_blank">'.__('document', 'ARMember').'</a>';
						$apiCallbackUrlInfo .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'.htmlentities($callbackTooltip).'"></i>';
						$apiCallbackUrlInfo .= '<br/><b>' . $apiCallbackUrl . '</b>';
						break;
					case 'authorize_net':
						$titleTooltip = __('You can find your Login ID & Transaction Key from your authorize.net account. To get more details, Please refer this', 'ARMember').' <a href="https://support.authorize.net/authkb/index?page=content&id=A576" target="_blank">'.__('document', 'ARMember').'.</a>';
						$apiCallbackUrl = $arm_global_settings->add_query_arg("arm-listener", "arm_authorizenet_api", ARM_HOME_URL . "/");
                        $apiCallbackUrlInfo = __('Please make sure you have set following callback URL in your Authorize.net account.', 'ARMember');
                        $callbackTooltip = __('To get more information about how to set Silent Post URL from your Authorize.net account, please refer this', 'ARMember').' <a href="'.MEMBERSHIP_DOCUMENTATION_PAYMENT_GATEWAY_AUTHORIZE_URL.'" target="_blank">'.__('document', 'ARMember').'</a>';
						$apiCallbackUrlInfo .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'.htmlentities($callbackTooltip).'"></i>';
						$apiCallbackUrlInfo .= '<br/><b>' . $apiCallbackUrl . '</b>';
						break;
					case '2checkout':
						$titleTooltip = __('You can find Username, Password, Secret Key etc details', 'ARMember').' <a href="http://help.2checkout.com/articles/FAQ/Where-do-I-set-up-the-Secret-Word/" target="_blank">'.__('here', 'ARMember').'.</a>';
						$apiCallbackUrl = $arm_global_settings->add_query_arg("action", "arm_2checkout_api", ARM_HOME_URL . "/");
						$apiListenerUrl = $arm_global_settings->add_query_arg("arm-listener", "arm_2checkout_api", ARM_HOME_URL ."/");
						$apiCallbackUrlInfo = __('Please make sure you have set following Approved URL in your 2Checkout account.', 'ARMember');
                        $callbackTooltip = __('To get more information about how to set INS(Web Hook) URL AND Approved URL from your 2Checkout account, please refer this', 'ARMember').' <a href="'.MEMBERSHIP_DOCUMENTATION_PAYMENT_GATEWAY_2CHECKOUT_URL.'" target="_blank">'.__('document', 'ARMember').'</a>';
						$apiCallbackUrlInfo .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'.htmlentities($callbackTooltip).'"></i>';
						$apiCallbackUrlInfo .= '<br/><b>' . $apiCallbackUrl . '</b>';
						
						$apiCallbackUrlInfo .=  __('<br/><br/>In case of Recurring Billing, Please make sure you have set following URL in the Global URL field in your 2Checkout account.', 
							'ARMember');
						$globalfieldTooltip = __('To get more information about how to set Global URL Field from your 2Checkout account, please refer this', 'ARMember').' <a href="'.MEMBERSHIP_DOCUMENTATION_PAYMENT_GATEWAY_2CHECKOUT_URL.'" target="_blank">'.__('document', 'ARMember').'</a>';
						$apiCallbackUrlInfo .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'.htmlentities($globalfieldTooltip).'"></i>';
						$apiCallbackUrlInfo .= '<br/><b>'.$apiListenerUrl.'</b>';

						break;
					default:
						break;
				}
				$titleTooltip = apply_filters('arm_change_payment_gateway_tooltip', $titleTooltip, $gateway_name, $gateway_options);
				$apiCallbackUrlInfo = apply_filters('arm_gateway_callback_info', $apiCallbackUrlInfo, $gateway_name, $gateway_options);
				if (!empty($titleTooltip)) {
					?><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo htmlentities($titleTooltip);?>"></i><?php
				}
				?>
			</div>			
			<div class="armclear"></div>
			<table class="form-table arm_active_payment_gateways">
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Active', 'ARMember');?></label></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_payment_setting_switch">
							<input type="checkbox" id="arm_<?php echo strtolower($gateway_name);?>_status" <?php echo $arm_status_switchChecked;?> value="1" class="armswitch_input armswitch_payment_input" name="payment_gateway_settings[<?php echo strtolower($gateway_name);?>][status]" data-payment="<?php echo strtolower($gateway_name);?>"/>
							<label for="arm_<?php echo strtolower($gateway_name);?>_status" class="armswitch_label"></label>
						</div>
					</td>
				</tr>
				<?php
				switch (strtolower($gateway_name))
				{
					case 'paypal':
						$gateway_options['paypal_payment_mode'] = (!empty($gateway_options['paypal_payment_mode'])) ? $gateway_options['paypal_payment_mode'] : 'sandbox';
						$globalSettings = $arm_global_settings->global_settings;
						$ty_pageid = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
						$cp_page_id = isset($globalSettings['cancel_payment_page_id']) ? $globalSettings['cancel_payment_page_id'] : 0;
						$default_return_url = $arm_global_settings->arm_get_permalink('', $ty_pageid);
						$default_cancel_url = $arm_global_settings->arm_get_permalink('', $cp_page_id);
						$return_url = (!empty($gateway_options['return_url'])) ? $gateway_options['return_url'] : $default_return_url;
						$cancel_url = (!empty($gateway_options['cancel_url'])) ? $gateway_options['cancel_url'] : $default_cancel_url;
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Merchant Email', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_merch_email" type="text" name="payment_gateway_settings[paypal][paypal_merchant_email]" value="<?php echo (!empty($gateway_options['paypal_merchant_email']) ? $gateway_options['paypal_merchant_email'] : "" );?>" data-msg-required="<?php _e('Merchant Email can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Payment Mode', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div class="arm_paypal_mode_container" id="arm_paypal_mode_container">
									<input id="arm_payment_gateway_mode_sand" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="sandbox" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked($gateway_options['paypal_payment_mode'], 'sandbox');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_payment_gateway_mode_sand"><?php _e('Sandbox', 'ARMember');?></label>
									<input id="arm_payment_gateway_mode_pro" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="live" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked($gateway_options['paypal_payment_mode'], 'live');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_payment_gateway_mode_pro"><?php _e('Live', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<!--**********./Begin Paypal Sandbox Details/.**********-->
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Username', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_username]" value="<?php echo (!empty($gateway_options['sandbox_api_username']) ? $gateway_options['sandbox_api_username'] : "" );?>" data-msg-required="<?php _e('API Username can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Password', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_password]" value="<?php echo (!empty($gateway_options['sandbox_api_password']) ? $gateway_options['sandbox_api_password'] : "" );?>" data-msg-required="<?php _e('API Password can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_sandbox_fields <?php echo ($gateway_options['paypal_payment_mode']=='sandbox') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Sandbox API Signature', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_signature]" value="<?php echo (!empty($gateway_options['sandbox_api_signature']) ? $gateway_options['sandbox_api_signature'] : "" );?>" data-msg-required="<?php _e('API Signature can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<!--**********./End Paypal Sandbox Details/.**********-->
						<!--**********./Begin Paypal Live Details/.**********-->
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Username', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_username]" value="<?php echo (!empty($gateway_options['live_api_username']) ? $gateway_options['live_api_username'] : "" );?>" data-msg-required="<?php _e('API Username can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Password', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_password]" value="<?php echo (!empty($gateway_options['live_api_password']) ? $gateway_options['live_api_password'] : "" );?>" data-msg-required="<?php _e('API Password can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_paypal_live_fields <?php echo ($gateway_options['paypal_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php _e('Live API Signature', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][live_api_signature]" value="<?php echo (!empty($gateway_options['live_api_signature']) ? $gateway_options['live_api_signature'] : "" );?>" data-msg-required="<?php _e('API Signature can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<!--**********./End Paypal Live Details/.**********-->
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Unsuccessful / Cancel Url', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" type="text" name="payment_gateway_settings[paypal][cancel_url]" value="<?php echo $cancel_url;?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Language', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<?php $arm_paypal_language = $arm_payment_gateways->arm_paypal_language(); ?>
								<input type='hidden' id='arm_paypal_language' name="payment_gateway_settings[paypal][language]" value="<?php echo (!empty($gateway_options['language'])) ? $gateway_options['language'] : 'en_US'; ?>" />
								<dl class="arm_selectbox arm_active_payment_<?php echo strtolower($gateway_name);?>" <?php echo $disabled_field_attr; ?>>
									<dt <?php echo ($gateway_options['status']=='1') ? '' : 'style="border:1px solid #DBE1E8"'; ?>>
										<span></span>
										<input type="text" style="display:none;" value="<?php _e('English/United States ( en_US )', 'ARMember'); ?>" class="arm_autocomplete"/>
										<i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_paypal_language">
											<?php foreach ($arm_paypal_language as $key => $value): ?>
												<li data-label="<?php echo $value . " ( $key ) ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo $value . " ( $key ) ";?></li>
											<?php endforeach;?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<?php 
						break;
					case 'stripe':
						$gateway_options['stripe_payment_mode'] = (!empty($gateway_options['stripe_payment_mode'])) ? $gateway_options['stripe_payment_mode'] : 'test';
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Payment Mode', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div id="arm_stripe_mode_container" class="arm_stripe_mode_container">
									<input id="arm_stripe_mode_test" class="arm_general_input arm_stripe_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="test" name="payment_gateway_settings[stripe][stripe_payment_mode]" <?php checked($gateway_options['stripe_payment_mode'], 'test');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_stripe_mode_test"><?php _e('Test', 'ARMember');?></label>
									<input id="arm_stripe_mode_live" class="arm_general_input arm_stripe_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="live" name="payment_gateway_settings[stripe][stripe_payment_mode]" <?php checked($gateway_options['stripe_payment_mode'], 'live');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_stripe_mode_live"><?php _e('Live', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<tr class="form-field arm_pay_gate_stripe_live_mode <?php echo ($gateway_options['stripe_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><label><?php _e('Live Secret Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_live_secrate_key" type="text" name="payment_gateway_settings[stripe][stripe_secret_key]" value="<?php echo (!empty($gateway_options['stripe_secret_key']) ? $gateway_options['stripe_secret_key'] : "" );?>" data-msg-required="<?php _e('Live Secret Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_pay_gate_stripe_live_mode <?php echo ($gateway_options['stripe_payment_mode']=='live') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><label><?php _e('Live Publishable Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_live_pub_key" type="text" name="payment_gateway_settings[stripe][stripe_pub_key]" value="<?php echo (!empty($gateway_options['stripe_pub_key']) ? $gateway_options['stripe_pub_key'] : "" );?>" data-msg-required="<?php _e('Live Publishable Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>

						<tr class="form-field arm_pay_gate_stripe_test_mode <?php echo ($gateway_options['stripe_payment_mode']=='test') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><label><?php _e('Test Secret Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_test_secrate_key" type="text" name="payment_gateway_settings[stripe][stripe_test_secret_key]" value="<?php echo (!empty($gateway_options['stripe_test_secret_key']) ? $gateway_options['stripe_test_secret_key'] : "" );?>" data-msg-required="<?php _e('Test Secret Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_pay_gate_stripe_test_mode <?php echo ($gateway_options['stripe_payment_mode']=='test') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><label><?php _e('Test Publishable Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_test_pub_key" type="text" name="payment_gateway_settings[stripe][stripe_test_pub_key]" value="<?php echo (!empty($gateway_options['stripe_test_pub_key']) ? $gateway_options['stripe_test_pub_key'] : "" );?>" data-msg-required="<?php _e('Test Publishable Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>

						<tr class="form-field">
							<th class="arm-form-table-label">
								<label><?php _e('Select Payment Method', 'ARMember');?></label>
							</th>
							<td class="arm-form-table-content">
								<?php
									if( !isset( $gateway_options['stripe_payment_method'] ) || ( isset( $gateway_options['stripe_payment_method'] ) && '' ==  $gateway_options['stripe_payment_method']) ){
										$gateway_options['stripe_payment_method'] = 'fields';
									}
								?>
								<div class="arm_stripe_payment_method_container" id="arm_stripe_payment_method_container">
									<input id="arm_stripe_payment_method_fields" class="arm_general_input arm_stripe_payment_method_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name); ?>" type="radio" value="fields" name="payment_gateway_settings[stripe][stripe_payment_method]" <?php checked($gateway_options['stripe_payment_method'], 'fields'); ?> <?php echo $disabled_field_attr;?> />
									<label for="arm_stripe_payment_method_fields"><?php _e('Built-In Form Fields','ARMember'); ?></label>
									<input id="arm_stripe_payment_method_popup" class="arm_general_input arm_stripe_payment_method_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name); ?>" type="radio" value="popup" name="payment_gateway_settings[stripe][stripe_payment_method]"  <?php checked($gateway_options['stripe_payment_method'], 'popup'); ?> <?php echo $disabled_field_attr;?> />
									<label for="arm_stripe_payment_method_popup"><?php _e('SCA Compliant (popup)','ARMember'); ?></label>
								</div>
							</td>
						</tr>

						<tr class="form-field arm_stripe_payment_method_popup_fields <?php echo ($gateway_options['stripe_payment_method']=='popup') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php esc_html_e('Popup Title', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_popup_title_lbl" type="text" name="payment_gateway_settings[stripe][stripe_popup_title]" value="<?php echo (!empty($gateway_options['stripe_popup_title']) ? $gateway_options['stripe_popup_title'] : "" );?>" data-msg-required="<?php esc_html_e('Popup Title can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="{arm_selected_plan_title} : <?php _e("This shortcode will be replaced with the user selected plan name.", 'ARMember');?>"></i>
							</td>
						</tr>
						<tr class="form-field arm_stripe_payment_method_popup_fields <?php echo ($gateway_options['stripe_payment_method']=='popup') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php esc_html_e('Payment Button Label', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_popup_button_lbl" type="text" name="payment_gateway_settings[stripe][stripe_popup_button_lbl]" value="<?php echo (!empty($gateway_options['stripe_popup_button_lbl']) ? $gateway_options['stripe_popup_button_lbl'] : "" );?>" data-msg-required="<?php esc_html_e('Payment Button Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field arm_stripe_payment_method_popup_fields <?php echo ($gateway_options['stripe_payment_method']=='popup') ? '' : 'hidden_section';?>">
							<th class="arm-form-table-label"><label><?php esc_html_e('Popup Logo', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div class="arm_opt_content_wrapper arm_stripe_popup_icon_container">
									<div class="arm_stripe_popup_icon_wrapper <?php echo (!empty($gateway_options['stripe_popup_icon']) ? "hidden_section" : ""); ?>">
										<span><?php esc_html_e('Upload', 'ARMember'); ?></span>
										<input type="file" class="arm_stripe_popup_icon arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_stripe_popup_icon_input" data-arm_clicked="not" data-arm_stripe_popup_icon="arm_stripe_popup_icon" />
									</div>
									<div class="arm_stripe_icon_error" id="arm_stripe_icon_error"></div>
									<div class="arm_status_loader_img" id="arm_stripe_popup_icon_upload"></div>
									<script type='text/javascript'> 
									var ARM_MCARD_LOGO_ERROR_MSG = '<?php esc_html_e('Invalid File', 'ARMember');?>';
									</script>
									<input type="hidden" class="arm_stripe_popup_icon_file_url" name="payment_gateway_settings[stripe][stripe_popup_icon]" value="<?php echo (!empty($gateway_options['stripe_popup_icon']) ? $gateway_options['stripe_popup_icon'] : ""); ?>" />
									<div class="arm_remove_default_cover_photo_wrapper arm_stripe_popup_icon_remove <?php echo (empty($gateway_options['stripe_popup_icon']) ? "hidden_section" : ""); ?>">
										<span><?php esc_html_e('Remove','ARMember'); ?></span>
									</div>
									<div class="arm_stripe_popup_icon_selected_img <?php echo (empty($gateway_options['stripe_popup_icon']) ? "hidden_section" : ""); ?>"><img src="<?php echo (!empty($gateway_options['stripe_popup_icon']) ? $gateway_options['stripe_popup_icon'] : ""); ?>" class="<?php echo (empty($gateway_options['stripe_popup_icon']) ? "hidden_section" : ""); ?>" /></div>
									<div class="arm_font_size_12 arm_margin_top_5" >(<?php esc_html_e('Recommended logo size 70X70 px','ARMember'); ?>)</div>
								</div>
							</td>
						</tr>
						
						<?php 
						break;
					case 'authorize_net':
						$gateway_options['autho_mode'] = (!empty($gateway_options['autho_mode'])) ? $gateway_options['autho_mode'] : 'sandbox';
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Payment Mode', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div class="arm_authorize_net_mode_container" id="arm_authorize_net_mode_container">
									<input id="arm_autho_mode_sand" class="arm_general_input arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="sandbox" name="payment_gateway_settings[authorize_net][autho_mode]" <?php checked($gateway_options['autho_mode'], 'sandbox');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_autho_mode_sand"><?php _e('Sandbox', 'ARMember');?></label>
									<input id="arm_autho_mode_live" class="arm_general_input arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="live" name="payment_gateway_settings[authorize_net][autho_mode]" <?php checked($gateway_options['autho_mode'], 'live');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_autho_mode_live"><?php _e('Live', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('API Login ID', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_autho_api_key" type="text" name="payment_gateway_settings[authorize_net][autho_api_login_id]" value="<?php echo (!empty($gateway_options['autho_api_login_id']) ? $gateway_options['autho_api_login_id'] : "" );?>" data-msg-required="<?php _e('API Login ID can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Transaction Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_autho_transaction_key" type="text" name="payment_gateway_settings[authorize_net][autho_transaction_key]" value="<?php echo (!empty($gateway_options['autho_transaction_key']) ? $gateway_options['autho_transaction_key'] : "" );?>" data-msg-required="<?php _e('Transaction Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>						
						<?php
						break;
					case '2checkout':
						$gateway_options['payment_mode'] = (!empty($gateway_options['payment_mode'])) ? $gateway_options['payment_mode'] : 'sandbox';
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Payment Mode', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<div class="arm_2checkout_mode_container" id="arm_2checkout_mode_container">
									<input class="arm_general_input arm_2checkout_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="sandbox" id="arm_2checkout_mode_sandbox" name="payment_gateway_settings[2checkout][payment_mode]" <?php checked($gateway_options['payment_mode'], 'sandbox');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_2checkout_mode_sandbox"><?php _e('Sandbox', 'ARMember');?></label>
									<input class="arm_general_input arm_2checkout_mode_radio arm_iradio arm_active_payment_<?php echo strtolower($gateway_name);?>" type="radio" value="live" id="arm_2checkout_mode_live" name="payment_gateway_settings[2checkout][payment_mode]" <?php checked($gateway_options['payment_mode'], 'live');?> <?php echo $disabled_field_attr;?>>
									<label for="arm_2checkout_mode_live"><?php _e('Live', 'ARMember');?></label>
								</div>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('API Username', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_username" type="text" name="payment_gateway_settings[2checkout][username]" value="<?php echo (!empty($gateway_options['username']) ? $gateway_options['username'] : "" );?>" data-msg-required="<?php _e('API Username can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('API Password', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_password" type="text" name="payment_gateway_settings[2checkout][password]" value="<?php echo (!empty($gateway_options['password']) ? $gateway_options['password'] : "" );?>" data-msg-required="<?php _e('API Password can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Seller Id (account number)', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_sellerid" type="text" name="payment_gateway_settings[2checkout][sellerid]" value="<?php echo (!empty($gateway_options['sellerid']) ? $gateway_options['sellerid'] : "" );?>" data-msg-required="<?php _e('SellerId can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Private Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_private_key" type="text" name="payment_gateway_settings[2checkout][private_key]" value="<?php echo (!empty($gateway_options['private_key']) ? $gateway_options['private_key'] : "" );?>" data-msg-required="<?php _e('Private Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('API Secret Key', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_api_secret_key" type="text" name="payment_gateway_settings[2checkout][api_secret_key]" value="<?php echo (!empty($gateway_options['api_secret_key']) ? $gateway_options['api_secret_key'] : "" );?>" data-msg-required="<?php _e('API Secret Key can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Secret Word', 'ARMember');?> *</label></th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_2checkout_secret_word" type="text" name="payment_gateway_settings[2checkout][secret_word]" value="<?php echo (!empty($gateway_options['secret_word']) ? $gateway_options['secret_word'] : "" );?>" data-msg-required="<?php _e('Secret Word can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Language', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<?php $arm_2checkout_language = $arm_payment_gateways->arm_2checkout_language(); ?>
								<input type='hidden' id='arm_2checkout_language' name="payment_gateway_settings[2checkout][language]" value="<?php echo (!empty($gateway_options['language'])) ? $gateway_options['language'] : 'en';?>" />
								<dl class="arm_selectbox arm_active_payment_<?php echo strtolower($gateway_name);?>" <?php echo $disabled_field_attr; ?>>
									<dt <?php echo ($gateway_options['status']=='1') ? '' : 'style="border:1px solid #DBE1E8"'; ?>>
										<span></span>
										<input type="text" style="display:none;" value="<?php _e('English ( en )', 'ARMember'); ?>" class="arm_autocomplete"/>
										<i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_2checkout_language">
											<?php foreach ($arm_2checkout_language as $key => $value): ?>
												<li data-label="<?php echo $value . " ( $key ) ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo $value . " ( $key ) ";?></li>
											<?php endforeach;?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<?php
						break;
					case 'bank_transfer':
						$gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] = isset($gateway_options['arm_bank_transfer_do_not_allow_pending_transaction']) ? $gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] : 0;
						$arm_bank_transfer_allow_switchChecked = ($gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] == '1') ? 'checked="checked"' : "" ;
						?>
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_bank_transfer_note"><?php _e('Note/Description', 'ARMember');?></label></th>
							<td class="arm-form-table-content">
								<?php 
								wp_editor(
									stripslashes((isset($gateway_options['note'])) ? $gateway_options['note'] : ''),
									'arm_bank_transfer_note',
									array('textarea_name' => 'payment_gateway_settings[bank_transfer][note]', 'textarea_rows' => 6)
								);
								?>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label><?php _e('Fields to be included in payment form', 'ARMember');?></label></th>
							<td class="arm-form-table-content armBankTransferFields">
								<label>
                                        <?php $gateway_options['fields']['transaction_id'] = isset($gateway_options['fields']['transaction_id']) ? $gateway_options['fields']['transaction_id'] : ''; ?>
                                        <input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name); ?>" type="checkbox" id="bank_transfer_transaction_id" name="payment_gateway_settings[bank_transfer][fields][transaction_id]" value="1" <?php checked($gateway_options['fields']['transaction_id'],1); ?> <?php echo $disabled_field_attr; ?> >
									<span><?php _e('Transaction ID', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['bank_name'] = (isset($gateway_options['fields']['bank_name'])) ? $gateway_options['fields']['bank_name'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_bank_name" name="payment_gateway_settings[bank_transfer][fields][bank_name]" value="1" <?php checked($gateway_options['fields']['bank_name'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Bank Name', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['account_name'] = (isset($gateway_options['fields']['account_name'])) ? $gateway_options['fields']['account_name'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_account_name" name="payment_gateway_settings[bank_transfer][fields][account_name]" value="1" <?php checked($gateway_options['fields']['account_name'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Account Holder Name', 'ARMember');?></span>
								</label>
                                <label>
									<?php $gateway_options['fields']['additional_info'] = (isset($gateway_options['fields']['additional_info'])) ? $gateway_options['fields']['additional_info'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_additional_info" name="payment_gateway_settings[bank_transfer][fields][additional_info]" value="1" <?php checked($gateway_options['fields']['additional_info'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Additional Info/Note', 'ARMember');?></span>
								</label>
								<label>
									<?php $gateway_options['fields']['transfer_mode'] = (isset($gateway_options['fields']['transfer_mode'])) ? $gateway_options['fields']['transfer_mode'] : ''; ?>
									<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_mode" name="payment_gateway_settings[bank_transfer][fields][transfer_mode]" value="1" <?php checked($gateway_options['fields']['transfer_mode'], 1);?> <?php echo $disabled_field_attr;?>>
									<span><?php _e('Payment Mode', 'ARMember');?></span>
								</label>
								<?php 
								global $arm_payment_gateways;
								$arm_transfer_mode = $arm_payment_gateways->arm_get_bank_transfer_mode_options();
								$transfer_mode_style = (!empty($gateway_options['fields']['transfer_mode']) && $gateway_options['fields']['transfer_mode'] == 1 ) ? 'style="display:block;"' : '';
								?>
								<div class="arm_transfer_mode_main_container" <?php echo $transfer_mode_style; ?>>
								<?php
									$bank_transfer_mode_option = (isset($gateway_options['fields']['transfer_mode_option'])) ? $gateway_options['fields']['transfer_mode_option'] : array();
									
									foreach ($arm_transfer_mode as $key => $transfer_mode) { 
										$is_checked_option = '';
										if(in_array($key, $bank_transfer_mode_option)) {
											$is_checked_option = 'checked="checked"';
										}
										
										$transfer_mode_val = isset($gateway_options['fields']['transfer_mode_option_label'][$key]) ? $gateway_options['fields']['transfer_mode_option_label'][$key] : $transfer_mode;
								?>
										<div class="arm_transfer_mode_list_container">
										<label>
											<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower($gateway_name);?>" type="checkbox" id="bank_transfer_mode_option" name="payment_gateway_settings[bank_transfer][fields][transfer_mode_option][]" value="<?php echo $key; ?>" <?php echo $is_checked_option; ?> <?php echo $disabled_field_attr;?> data-msg-required="<?php esc_html_e('Please select Payment Mode option.', 'ARMember');?>">
										</label>
										<input class="arm_bank_transfer_mode_option_label" type="text" name="payment_gateway_settings[bank_transfer][fields][transfer_mode_option_label][<?php echo $key; ?>]" value="<?php echo $transfer_mode_val; ?>" >
										</div>
								<?php
									}
								?>
								</div>
							</td>
						</tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Transaction ID Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_transaction_id_label" type="text" name="payment_gateway_settings[bank_transfer][transaction_id_label]" value="<?php echo (!empty($gateway_options['transaction_id_label']) ? esc_html(stripslashes($gateway_options['transaction_id_label'])) : __('Transaction ID', 'ARMember'));?>" data-msg-required="<?php _e('Transaction ID Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Bank Name Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_bank_name_label" type="text" name="payment_gateway_settings[bank_transfer][bank_name_label]" value="<?php echo (!empty($gateway_options['bank_name_label']) ? esc_html(stripslashes($gateway_options['bank_name_label'])) : __('Bank Name', 'ARMember'));?>" data-msg-required="<?php _e('Bank Name Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Account Holder Name Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_account_name_label" type="text" name="payment_gateway_settings[bank_transfer][account_name_label]" value="<?php echo (!empty($gateway_options['account_name_label']) ? esc_html(stripslashes($gateway_options['account_name_label'])) : __('Account Holder Name', 'ARMember'));?>" data-msg-required="<?php _e('Account Holder Name Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Additional Info/Note Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_additional_info_label" type="text" name="payment_gateway_settings[bank_transfer][additional_info_label]" value="<?php echo (!empty($gateway_options['additional_info_label']) ? esc_html(stripslashes($gateway_options['additional_info_label'])) : __('Additional Info/Note', 'ARMember'));?>" data-msg-required="<?php _e('Additional Info/Note Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Payment Method Label', 'ARMember');?></label></th>
                            <td class="arm-form-table-content"><input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_bank_transfer_payment_mode_label" type="text" name="payment_gateway_settings[bank_transfer][transfer_mode_label]" value="<?php echo (!empty($gateway_options['transfer_mode_label']) ? esc_html__(stripslashes($gateway_options['transfer_mode_label'])) : esc_html__('Payment Mode', 'ARMember'));?>" data-msg-required="<?php esc_html_e('Payment Mode Label can not be left blank.', 'ARMember');?>" <?php echo $readonly_field_attr;?>></td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Do not allow user to submit transaction data more than on time', 'ARMember');?></label></th>
                            <td class="arm-form-table-content">
                            	<div class="armswitch arm_payment_setting_switch arm_payment_<?php echo $gateway_name; ?>_display_switch">
									<input type="checkbox" id="arm_<?php echo $gateway_name; ?>_do_not_allow_pending_transaction_switch_status" <?php echo $arm_bank_transfer_allow_switchChecked;?> value="1" class="armswitch_input arm_active_payment_<?php echo $gateway_name; ?>" name="payment_gateway_settings[<?php echo $gateway_name; ?>][arm_bank_transfer_do_not_allow_pending_transaction]" <?php echo $disabled_field_attr;?>/>
									<label for="arm_<?php echo $gateway_name; ?>_do_not_allow_pending_transaction_switch_status" class="armswitch_label arm_active_payment_<?php echo $gateway_name; ?>" <?php echo $readonly_field_attr;?>></label>
								</div>
                            </td>
                        </tr>
						<?php
						break;
					default:
						break;
				}
				do_action('arm_after_payment_gateway_listing_section', $gateway_name, $gateway_options);
				$pgHasCCFields = apply_filters('arm_payment_gateway_has_ccfields', false, $gateway_name, $gateway_options);
				if (in_array($gateway_name, array('stripe','authorize_net')) || $pgHasCCFields){

					$is_hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $gateway_name, $gateway_options );

					$hidden_cls = '';
					if( true == $is_hide_cc_fields ){
						$hidden_cls = 'hidden_section';
					}

				?>
					<?php
						$arm_card_holder_label_filter = apply_filters('arm_payment_card_holder_filter', $allowed_arr = array(), $gateway_name);

						if($gateway_name == 'stripe' || $gateway_name == 'paypal_pro' || $gateway_name == 'online_worldpay' || in_array($gateway_name, $arm_card_holder_label_filter)) {
					?>
						<tr class="form-field <?php echo $hidden_cls; ?>">
							<th class="arm-form-table-label">
								<label><?php _e('Card Holder Name Label', 'ARMember');?></label>
							</th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label_name" data-id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][card_holder_name]" value="<?php echo (!empty($gateway_options['card_holder_name']) ? esc_html(stripslashes($gateway_options['card_holder_name'])) : __('Card Holder Name', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>

						<tr class="form-field <?php echo $hidden_cls; ?>">
							<th class="arm-form-table-label">
								<label><?php _e('Card Holder Name Description', 'ARMember');?></label>
							</th>
							<td class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label_desc" data-id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][card_holder_name_description]" value="<?php echo (!empty($gateway_options['card_holder_name_description']) ? esc_html(stripslashes($gateway_options['card_holder_name_description'])) : "");?>" <?php echo $readonly_field_attr;?>>
							</td>
						</tr>
					<?php							
						}
					?>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Credit Card Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label_creadit_card" data-id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cc_label]" value="<?php echo (!empty($gateway_options['cc_label']) ? esc_html(stripslashes($gateway_options['cc_label'])) : __('Credit Card Number', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at frontend membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Credit Card Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cc_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cc_desc]" value="<?php echo (!empty($gateway_options['cc_desc']) ? esc_html(stripslashes($gateway_options['cc_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Expire Month Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_em_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][em_label]" value="<?php echo (!empty($gateway_options['em_label']) ? esc_html(stripslashes($gateway_options['em_label'])) : __('Expiration Month', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at frontend membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Expire Month Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_em_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][em_desc]" value="<?php echo (!empty($gateway_options['em_desc']) ? esc_html(stripslashes($gateway_options['em_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Expire Year Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_ey_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][ey_label]" value="<?php echo (!empty($gateway_options['ey_label']) ? esc_html(stripslashes($gateway_options['ey_label'])) : __('Expiration Year', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at frontend membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('Expire Year Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_ey_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][ey_desc]" value="<?php echo (!empty($gateway_options['ey_desc']) ? esc_html(stripslashes($gateway_options['ey_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('CVV Label', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cvv_label" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cvv_label]" value="<?php echo (!empty($gateway_options['cvv_label']) ? esc_html(stripslashes($gateway_options['cvv_label'])) : __('CVV Code', 'ARMember'));?>" <?php echo $readonly_field_attr;?>>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("This label will be displayed at frontend membership setup wizard page while payment.", 'ARMember');?>"></i>
						</td>
					</tr>
					<tr class="form-field arm_payment_gateway_<?php echo $gateway_name ?>_cc_field_wrapper <?php echo $hidden_cls; ?>">
						<th class="arm-form-table-label"><label><?php _e('CVV Description', 'ARMember');?></label></th>
						<td class="arm-form-table-content">
							<input class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_payment_gateway_<?php echo $gateway_name;?>_cvv_desc" type="text" name="payment_gateway_settings[<?php echo $gateway_name;?>][cvv_desc]" value="<?php echo (!empty($gateway_options['cvv_desc']) ? esc_html(stripslashes($gateway_options['cvv_desc'])) : "" );?>" <?php echo $readonly_field_attr;?>>
						</td>
					</tr>
					<?php
						if($gateway_name == "stripe" || $gateway_name == "authorize_net" || $gateway_name == "paypal_pro" || $gateway_name == "online_worldpay") {
						
						$gateway_options['enable_debug_mode'] = isset($gateway_options['enable_debug_mode']) ? $gateway_options['enable_debug_mode'] : 0;
						$arm_debug_mode_switchChecked = ($gateway_options['enable_debug_mode'] == '1') ? 'checked="checked"' : "" ;
					?>
					<tr class="form-field">
						<th class="arm-form-table-label">
							<label><?php _e('Display actual error returned from payment gateway', 'ARMember');?></label>
						</th>
						<td class="arm-form-table-content">
							<div class="armswitch arm_payment_setting_switch arm_payment_<?php echo $gateway_name; ?>_display_switch">
								<input type="checkbox" id="arm_<?php echo $gateway_name; ?>_debug_mode_switch_status" <?php echo $arm_debug_mode_switchChecked;?> value="1" class="armswitch_input arm_active_payment_<?php echo $gateway_name; ?>" name="payment_gateway_settings[<?php echo $gateway_name; ?>][enable_debug_mode]" <?php echo $disabled_field_attr;?>/>
								<label for="arm_<?php echo $gateway_name; ?>_debug_mode_switch_status" class="armswitch_label arm_active_payment_<?php echo $gateway_name; ?>" <?php echo $readonly_field_attr;?>></label>
							</div>
							
						</td>
					</tr>
					<?php 
						}
				}
				do_action('arm_payment_gateway_add_ccfields', $gateway_name, $gateway_options, $readonly_field_attr);
                                ?>
                <?php
					
					$arm_is_mycred_feature = get_option('arm_is_mycred_feature');
        			$arm_ismyCREDFeature = ($arm_is_mycred_feature == '1') ? true : false;
					if($arm_ismyCREDFeature && $gateway_name == "mycred") {
						$point_exchange = 1;
		                if(!empty($gateway_options['point_exchange'])) {
		                    $point_exchange = $gateway_options['point_exchange'];
		                }
		                $point_exchange = number_format((float)$point_exchange, 3, '.', '');
				?>
					<tr class="form-field">
		                <th class="arm-form-table-label"><label><?php echo sprintf(__('%d Point', 'ARMember'), 1);?> = </label></th>
		                <td class="arm-form-table-content">
		                    <input type="text" class="arm_active_payment_<?php echo strtolower($gateway_name);?>" id="arm_mycred_point_exchange" name="payment_gateway_settings[mycred][point_exchange]" value="<?php echo $point_exchange; ?>">
		                </td>
		            </tr>
				<?php							
					} ?>
				<tr class="form-field">
					<th class="arm-form-table-label"><label><?php _e('Currency', 'ARMember');?></label></th>
					<td class="arm-form-table-content">
						<label class="arm_payment_gateway_currency_label"><?php echo $global_currency;?><?php echo ' ( '.$global_currency_symbol.' ) ';?></label>
						<a class="arm_payment_gateway_currency_link arm_ref_info_links" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings.'#changeCurrency'); ?>"><?php _e('Change currency', 'ARMember'); ?></a>
					</td>
				</tr>
				<?php if (!empty($apiCallbackUrlInfo)): ?>
				<tr>
					<td colspan="2">
						<span class="arm_info_text"><?php echo $apiCallbackUrlInfo;?></span>
					</td>
				</tr>
				<?php endif;?>
			</table>
		<?php endforeach;?>
			<div class="arm_submit_btn_container">
				<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_pay_gate_settings_btn" type="submit" name="arm_pay_gate_settings_btn"><?php _e('Save', 'ARMember') ?></button>
				<?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
		</form>
	</div>
</div>