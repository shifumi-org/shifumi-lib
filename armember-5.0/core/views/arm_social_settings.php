<?php
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_social_feature, $arm_member_forms, $arm_subscription_plans, $arm_email_settings;
$global_settings = $arm_global_settings->global_settings;
$social_settings = $arm_social_feature->arm_get_social_settings();
$default_forms_id = $arm_member_forms->arm_get_default_form_id('registration');
$defaultRegisterPage = $arm_global_settings->arm_get_single_global_settings('register_page_id', 0);
// query monitor change
$all_free_plans = $arm_subscription_plans->arm_get_all_free_plans('arm_subscription_plan_id, arm_subscription_plan_name');
if (empty($social_settings)) {
    $social_settings['registration'] = array(
        'form_page' => $defaultRegisterPage,
        'form' => $default_forms_id,
    );
}
$social_options = (!empty($social_settings['options'])) ? $social_settings['options'] : '';
$social_reg_options = (!empty($social_settings['registration'])) ? $social_settings['registration'] : '';
$social_reg_options['form_page'] = (!empty($social_reg_options['form_page'])) ? $social_reg_options['form_page'] : '';
$social_reg_options['form'] = !empty($social_reg_options['form']) ? $social_reg_options['form'] : $default_forms_id;

$arm_social_icon_browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);
$arm_social_icon_browser_name = $arm_social_icon_browser_info['name'];
$arm_social_icon_browser_version = $arm_social_icon_browser_info['version'];

$icon_upload_dir = MEMBERSHIP_UPLOAD_DIR . '/social_icon/';
if (!is_dir($icon_upload_dir)) {
	wp_mkdir_p($icon_upload_dir);
}
?>
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		<form method="post" id="arm_social_settings_form" class="arm_social_settings_form arm_admin_form" enctype="multipart/form-data">
			<div class="arm_hide_show_social_setting">
                                <!-- <div class="arm_solid_divider"></div> -->
				<div class="armclear"></div>
				<!-- *****************************/.Social Registration Settings./***************************** -->
				<div class="page_sub_title">
					<?php _e('Social Signup Configuration', 'ARMember'); ?>
				</div>
				<table class="form-table">
                                         <tr class="form-field">
                                            <th class="arm-form-table-label"><?php _e('Enable One Click Sign Up?', 'ARMember'); ?></th>
                                            <td class="arm-form-table-content">
                                                <div class="armswitch arm_global_setting_switch">
                                                    <?php
                                                    $arm_one_click_social_signup = (!empty($social_options)) ? (isset($social_options['arm_one_click_social_signup']) ? $social_options['arm_one_click_social_signup'] : 0) : 0;
                                                    if($arm_one_click_social_signup == 0)
                                                    {
                                                        $arm_social_connect_registration_page = '';
                                                        $arm_social_connect_redirection_page = 'hidden_section';
                                                    }
                                                    else{
                                                        $arm_social_connect_registration_page = 'hidden_section';
                                                        $arm_social_connect_redirection_page = '';
                                                    }
                                                    ?>
                                                    <input id="arm_one_click_social_signup" class="armswitch_input arm_one_click_social_signup" type="checkbox" name="arm_social_settings[options][arm_one_click_social_signup]" value="1" data-stype="fb" <?php checked($arm_one_click_social_signup, 1); ?>>

                                                    <label for="arm_one_click_social_signup" class="armswitch_label"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <?php  
                                            $email_tools = $arm_email_settings->arm_get_optin_settings();
                                            $email_tools = apply_filters('arm_opt_ins_details', $email_tools);
                                            if((!empty($email_tools) && $arm_email_settings->isOptInsFeature) || is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php')):
                                                $arm_options_name = isset($social_options['optins_name']) ? $social_options['optins_name'] : 0;
                                                $list_html = '';
                                        ?>
                                        <tr class="form-field arm_one_click_redirection_page <?php echo $arm_social_connect_redirection_page; ?>">
						<th class="arm-form-table-label"><?php _e('Opt-Ins', 'ARMember'); ?></th>
						<td class="arm-form-table-content">
                                                    <input type="hidden" id="arm_optins_on_click_signup" name="arm_social_settings[options][optins_name]" value="<?php echo $arm_options_name; ?>" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd>
                                                                    <ul class="arm_optins_on_click_signup" data-id="arm_optins_on_click_signup">
                                                                            <li data-label="<?php _e('Select Optins','ARMember');?>" data-value="0"><?php _e('Select Optins', 'ARMember');?></li>
                                                                                    <?php 
                                                                                    foreach ($email_tools as $etool => $etsetting) {
                                                                                        if (!isset($etsetting['status']) || $etsetting['status'] != 1) { continue; }
                                                                                        $etoolName = isset($etsetting['optins_name']) ? $etsetting['optins_name'] : '';
                                                                                        if ($etool == 'aweber') { $etoolName = __('Aweber', 'ARMember'); }
                                                                                        else if ($etool == 'mailchimp') { $etoolName = __('MailChimp', 'ARMember'); }
                                                                                        else if ($etool == 'constant') { $etoolName = __('Constant Contact', 'ARMember'); }
                                                                                        else if ($etool == 'getresponse') { $etoolName = __('GetResponse', 'ARMember'); }
                                                                                        else if ($etool == 'madmimi') { $etoolName = __('Mad Mimi', 'ARMember'); }
                                                                                        else if ($etool == 'mailerlite') { $etoolName = __('Mailer Lite', 'ARMember'); }
                                                                                         else if ($etool == 'sendinblue') { $etoolName = __('Sendinblue', 'ARMember'); }
                                                                                        
                                                                                        echo '<li class="arm_optins_li '.stripslashes($etool).'" data-label="'.stripslashes($etoolName).'" data-value="'.$etool.'">'.stripslashes($etoolName).'</li>';
                                                                                        
                                    /********** display list begins ********/
                                    if (!isset($etsetting['status']) || $etsetting['status'] != 1 || empty($etsetting['list']))
                                        { continue; }
                                        $sc_list_id = (isset($etsetting['list_id'])) ? $etsetting['list_id'] : '';
                                        $lists = (isset($etsetting['list'])) ? $etsetting['list'] : array();
                                        $sc_style = 'style="display:none;"';
                                        if($arm_options_name == $etool && $arm_options_name != '0') {
                                            $sc_list_id = isset($social_options[$arm_options_name]['list_id'])  ? $social_options[$arm_options_name]['list_id'] : $sc_list_id;
                                            $sc_style = '';
                                        }
                                        $list_html .= '<tr class="form-field arm_one_click_redirection_page '.$arm_social_connect_redirection_page .'--'.$arm_options_name.'--'.$etool.' arm_email_tool_list '.$etool.'_list_name '.$arm_social_connect_redirection_page.'" '.$sc_style.'>';
                                        $list_html .= '<th class="arm-form-table-label">'.__('List Name', 'ARMember').'</th>';
                                        $list_html .= '<td class="arm-form-table-content">';
                                        $list_html .= '<input type="hidden" id="'.$etool.'_list_name" name="arm_social_settings[options]['.$etool.'][list_id]" value="'.$sc_list_id.'"/>';
                                        $list_html .= '<dl class="arm_selectbox column_level_dd">';
                                        $list_html .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                                        $list_html .= '<dd>';
                                        $list_html .= '<ul data-id="'.$etool.'_list_name" id="arm_'.$etool.'_list">';
                                        if (!empty($lists)) {
                                            foreach ($lists as $list) {
                                                $list_html .= '<li data-label="'.$list['name'].'" data-value="'.$list['id'].'">'.$list['name'].'</li>';
                                            }
                                        }
                                        $list_html .= '</ul>';
                                        $list_html .= '</dd>';
                                        $list_html .= '</dl>';
                                        $list_html .= '</td>';
                                        $list_html .= '</tr>';
                                    /********** display list end ***********/
                                        
                                                                                    }
                                                                                    if( is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php') ){
                                                                                        $mymail_lbl = is_plugin_active('mailster/mailster.php') ? 'Mailster' : 'MyMail';
                                                                                        echo '<li data-label="' . $mymail_lbl . '" data-value="mymail">' . $mymail_lbl . '</li>';
                                                                                    }?>
                                                                    </ul>
                                                            </dd>
                                                    </dl>
                                                </td>
                                        </tr>
                                        
                                        <?php echo $list_html; ?>
                                        
                                        <?php
                                         if (is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php')) {
                                            $mymail_version = get_option('mymail_version');
                                            $mailster_version = get_option('mailster_version');
                                            
                                            if ($mymail_version >= "2.0.20" || $mailster_version >= "2.2") {
                                                if(version_compare($mailster_version, '2.3','<'))
                                                {
                                                    $all_mymail_lists = mymail('lists')->get();
                                                }
                                                else
                                                {
                                                    $all_mymail_lists = mailster('lists')->get();
                                                }

                                                $mymail_list_id = (isset($social_options['mymail']['list_id'])) ? $social_options['mymail']['list_id'] : '';
                                                if(empty($mymail_list_id) && !empty($all_mymail_lists))
                                                {
                                                    $mymail_list_id = (isset($all_mymail_lists[0]->ID)) ? $all_mymail_lists[0]->ID : '';
                                                }

                                                //$mymailStatus = (isset($social_options['mymail']['status'])) ? $social_options['mymail']['status'] : 0;    
                                                ?>
                                                <tr class="form-field arm_one_click_redirection_page <?php echo $arm_social_connect_redirection_page; ?> arm_email_tool_list <?php echo 'mymail_list_name '.$arm_social_connect_redirection_page; ?>" <?php echo ($arm_options_name == 'mymail' && $arm_options_name != '0') ? '' : 'style="display: none;"'; ?>>
                                                    <th class="arm-form-table-label"><?php _e('List Name', 'ARMember'); ?></th>
                                                    <td class="arm-form-table-content">
                                                        <input type="hidden" id="mymail_list_name" name="arm_social_settings[options][mymail][list_id]" value="<?php echo $mymail_list_id; ?>"/>
                                                        <dl class="arm_selectbox column_level_dd">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd>
                                                                <ul data-id="mymail_list_name" id="arm_mymail_list">
                                                                    <?php if (!empty($all_mymail_lists)) { ?>
                                                                        <?php foreach ($all_mymail_lists as $key => $format) { ?>
                                                                            <li data-label="<?php echo $format->name; ?>" data-value="<?php echo $format->ID; ?>"><?php echo $format->name; ?></li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </td>
                                                </tr>    
                                                <?php
                                            }
                                         } ?>
                                            
                                        <?php endif; ?>
                                        <tr class="form-field arm_one_click_redirection_page <?php echo $arm_social_connect_redirection_page; ?>">
						<th class="arm-form-table-label"><?php _e('Assign Default Plan', 'ARMember'); ?></th>
						<td class="arm-form-table-content">
                                                        <input type="hidden" id="arm_assign_default_plan" name="arm_social_settings[options][assign_default_plan]" value="<?php echo isset($social_options['assign_default_plan']) ? $social_options['assign_default_plan'] : '0'; ?>" />
                                                        <dl class="arm_selectbox column_level_dd">
                                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                        <ul class="arm_assign_default_plan_wrapper" data-id="arm_assign_default_plan">
                                                                                <li data-label="<?php _e('Select Plan','ARMember');?>" data-value="0"><?php _e('Select Plan', 'ARMember');?></li>
                                                                                <?php if(!empty($all_free_plans)): /* query monitor change */ ?>
                                                                                        <?php foreach($all_free_plans as $plan): ?>
                                                                                                <li class="arm_assign_default_plan_li <?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-label="<?php echo stripslashes($plan['arm_subscription_plan_name']);?>" data-value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></li>
                                                                                        <?php endforeach;?>
                                                                                <?php endif;?>
                                                                        </ul>
                                                                </dd>
                                                        </dl>	
                                                </td>
                                        </tr>
                                        
                                        
					<tr class="form-field arm_social_connect_registration_page <?php echo $arm_social_connect_registration_page; ?>">
						<th class="arm-form-table-label">
                            <?php _e('Registration Form Page', 'ARMember'); ?>
                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Map your registration page & form for new member registration using social media options.", 'ARMember');?>" style="margin-top: -3px !important;"></i>                  
                        </th>
						<td class="arm-form-table-content">
							<?php
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected' => $social_reg_options['form_page'],
									'name' => 'arm_social_settings[registration][form_page]',
									'id' => 'register_form_page_id',
									'show_option_none' => __('Select Page', 'ARMember'),
									'option_none_value' => '',
									'class' => 'arm_social_form_page_input',
									'required_msg' => __('Please select atleast one registration page.', 'ARMember'),
								)
							);
							$page_shortcode_data = $arm_global_settings->arm_get_social_form_page_shortcodes($social_reg_options['form_page'], $social_reg_options['form']);
							$shortcode_forms = $page_shortcode_data['forms'];
							?>
							<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
							<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							<span class="arm_error_msg <?php echo ($page_shortcode_data['status']) ? 'arm_no_error' : ''; ?>"><?php _e('Shortcode of Registration Form not found on selected page. Please add shortcode there Or please select the page having registration form of ARMember', 'ARMember');?></span>
						</td>
					</tr>
					<tr class="form-field arm_social_connect_registration_form <?php echo $arm_social_connect_registration_page; ?>">
						<th class="arm-form-table-label"><?php _e('Registration Form', 'ARMember'); ?></th>
						<td class="arm-form-table-content">
							<div class="arm_social_form_container" style="margin-top: -8px;">
								<?php echo $shortcode_forms;?>
							</div>						
						</td>
					</tr>
                                        <tr class="form-field">
                                            <th class="arm-form-table-label"><?php _e('Get Social Avatar', 'ARMember'); ?></th>
                                            <td class="arm-form-table-content">
                                                <div class="armswitch arm_global_setting_switch">
                                                    <?php
                                                    $social_avatar = (!empty($social_options)) ? (isset($social_options['social_avatar']) ? $social_options['social_avatar'] : 0) : 0;
                                                    ?>
                                                    <input id="arm_get_social_avatar" class="armswitch_input arm_get_social_avatar" type="checkbox" name="arm_social_settings[options][social_avatar]" value="1" data-stype="fb" <?php checked($social_avatar, 1); ?>>

                                                    <label for="arm_get_social_avatar" class="armswitch_label"></label>
                                                </div>
                                                <span class="arm_info_text arm_social_info_text" >(<?php echo __('Get social avatar while authenticate with social connect buttons','ARMember'); ?>)</span>
                                                <span class="arm_info_text arm_social_info_text" >(<?php echo __('This options will only works if','ARMember').' <b>allow_url_fopen</b> '.__(' setting is ON in your server configuration','ARMember'); ?>)</span>
                                            </td>
                                        </tr>
                                       
				</table>
				<div class="arm_solid_divider"></div>
				<div class="armclear"></div>
				<div class="page_sub_title"><?php _e('Select Your Social Network', 'ARMember'); ?></div>
				<div class="arm_sub_section">
					<!-- *****************************/.Facebook Settings./***************************** -->
					<div class="page_sub_title"><?php _e('Facebook', 'ARMember'); ?></div>
					<?php 
					$fb_status = (!empty($social_options)) ? (isset($social_options['facebook']['status']) ? $social_options['facebook']['status'] : 0) : 0;
					$fbDisabledAttr = ($fb_status == 0) ? 'disabled="disabled"' : '';
					$fbReadonlyAttr = ($fb_status == 0) ? 'readonly="readonly"' : '';
					?>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_facebook_status"><?php _e('Active', 'ARMember'); ?></label></th>
							<td class="arm-form-table-content">
								<div class="armswitch arm_global_setting_switch">
									<input id="arm_facebook_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][facebook][status]" value="1" data-stype="fb" <?php checked($fb_status, 1);?>>
									<label for="arm_facebook_status" class="armswitch_label"></label>
                                    <?php $arm_sc_fb_icon = (!empty($social_options)) ? ((!empty($social_options['facebook']['icon'])) ? $social_options['facebook']['icon'] : 'fb_1.png') : 'fb_1.png';
                                    $arm_sc_fb_custom_icon = (!empty($social_options)) ? ((!empty($social_options['facebook']['custom_icon'])) ? $social_options['facebook']['custom_icon'] : '') : '';?>
                                    <input type="hidden" name="arm_social_settings[options][facebook][icon]" value="<?php echo $arm_sc_fb_icon;?>">
								</div>
							</td>
						</tr>
						
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_facebook_app_id"><?php _e('App ID', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][facebook][app_id]" class="arm_input_fb" id="arm_facebook_app_id" value="<?php echo (!empty($social_options) && !empty($social_options['facebook']['app_id'])) ? $social_options['facebook']['app_id'] : '';?>" data-msg-required="<?php _e('App ID can not be left blank.', 'ARMember'); ?>" <?php echo $fbReadonlyAttr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_facebook_app_secret"><?php _e('App Secret', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][facebook][app_secret]" class="arm_input_fb" id="arm_facebook_app_secret" value="<?php echo (!empty($social_options) && !empty($social_options['facebook']['app_secret'])) ? $social_options['facebook']['app_secret'] : '';?>" data-msg-required="<?php _e('App Secret can not be left blank.', 'ARMember'); ?>" <?php echo $fbReadonlyAttr;?>>
							</td>
						</tr>
					</table>
					<div class="arm_divider"></div>
					<div class="armclear"></div>
					<!-- *****************************/.Twitter Settings./***************************** -->
					<div class="page_sub_title"><?php _e('Twitter', 'ARMember'); ?></div>
					<?php 
					$tw_status = (!empty($social_options) && isset($social_options['twitter']['status'])) ? $social_options['twitter']['status'] : 0;
					$twDisabledAttr = ($tw_status == 0) ? 'disabled="disabled"' : '';
					$twReadonlyAttr = ($tw_status == 0) ? 'readonly="readonly"' : '';
					?>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_twitter_status"><?php _e('Active', 'ARMember'); ?></label></th>
							<td class="arm-form-table-content">
								<div class="armswitch arm_global_setting_switch">
									<input id="arm_twitter_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][twitter][status]" value="1" data-stype="tw" <?php checked($tw_status, 1);?>>
									<label for="arm_twitter_status" class="armswitch_label"></label>
                                                                        <?php $arm_sc_tw_icon = (!empty($social_options)) ? ((!empty($social_options['twitter']['icon'])) ? $social_options['twitter']['icon'] : 'tw_1.png') : 'tw_1.png'; 
                                                                        $arm_sc_tw_custom_icon = (!empty($social_options)) ? ((!empty($social_options['twitter']['custom_icon'])) ? $social_options['twitter']['custom_icon'] : '') : '';?>
                                                                        <input type="hidden" name="arm_social_settings[options][twitter][icon]" value="<?php echo $arm_sc_tw_icon;?>">
								</div>
							</td>
						</tr>
						 
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_twitter_customer_key"><?php _e('Customer Key', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][twitter][customer_key]" class="arm_input_tw" id="arm_twitter_customer_key" value="<?php echo (!empty($social_options) && !empty($social_options['twitter']['customer_key'])) ? $social_options['twitter']['customer_key'] : '';?>" data-msg-required="<?php _e('Customer Key can not be left blank.', 'ARMember'); ?>" <?php echo $twReadonlyAttr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_twitter_customer_secret"><?php _e('Customer Secret', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][twitter][customer_secret]" class="arm_input_tw" id="arm_twitter_customer_secret" value="<?php echo (!empty($social_options) && !empty($social_options['twitter']['customer_secret'])) ? $social_options['twitter']['customer_secret'] : '';?>" data-msg-required="<?php _e('Customer Secret can not be left blank.', 'ARMember'); ?>" <?php echo $twReadonlyAttr;?>>
							</td>
						</tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Callback URL', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <span class="arm_info_text"><?php echo ARM_HOME_URL; ?>/</span><br><span class="arm_font_size_13"><em><?php _e('Callback URLs that you should add in Twitter Application -> Settings.', 'ARMember'); ?></em></span>
                            </td>
                        </tr>
					</table>
					<div class="arm_divider"></div>
					<div class="armclear"></div>
					<!-- *****************************/.LinkedIn Settings./***************************** -->
					<div class="page_sub_title"><?php _e('LinkedIn', 'ARMember'); ?></div>
					<?php 
					$li_status = (!empty($social_options) && isset($social_options['linkedin']['status'])) ? $social_options['linkedin']['status'] : 0;
					$liDisabledAttr = ($li_status == 0) ? 'disabled="disabled"' : '';
					$liReadonlyAttr = ($li_status == 0) ? 'readonly="readonly"' : '';
					?>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_linkedin_status"><?php _e('Active', 'ARMember'); ?></label></th>
							<td class="arm-form-table-content">
								<div class="armswitch arm_global_setting_switch">
									<input id="arm_linkedin_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][linkedin][status]" value="1" data-stype="li" <?php checked($li_status, 1);?>>
									<label for="arm_linkedin_status" class="armswitch_label"></label>
                                                                        <?php $arm_sc_li_icon = (!empty($social_options)) ? ((!empty($social_options['linkedin']['icon'])) ? $social_options['linkedin']['icon'] : 'li_1.png') : 'li_1.png';
                                                                        $arm_sc_li_custom_icon = (!empty($social_options)) ? ((!empty($social_options['linkedin']['custom_icon'])) ? $social_options['linkedin']['custom_icon'] : '') : '';?>
                                                                        <input type="hidden" name="arm_social_settings[options][linkedin][icon]" value="<?php echo $arm_sc_li_icon;?>">
								</div>
							</td>
						</tr>
						
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_linkedin_client_id"><?php _e('Client ID', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][linkedin][client_id]" class="arm_input_li" id="arm_linkedin_client_id" value="<?php echo (!empty($social_options) && !empty($social_options['linkedin']['client_id'])) ? $social_options['linkedin']['client_id'] : '';?>" data-msg-required="<?php _e('Client ID can not be left blank.', 'ARMember'); ?>" <?php echo $liReadonlyAttr;?>>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_linkedin_client_secret"><?php _e('Client Secret', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][linkedin][client_secret]" class="arm_input_li" id="arm_linkedin_client_secret" value="<?php echo (!empty($social_options) && !empty($social_options['linkedin']['client_secret'])) ? $social_options['linkedin']['client_secret'] : '';?>" data-msg-required="<?php _e('Client Secret can not be left blank.', 'ARMember'); ?>" <?php echo $liReadonlyAttr;?>>
							</td>
						</tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Callback URL', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <span class="arm_info_text"><?php echo ARM_HOME_URL; ?></span><br><span class="arm_font_size_13"><em><?php _e('Callback URLs that you should add in LinkedIn Application', 'ARMember'); ?></em></span>
                            </td>
                        </tr>
					</table>
					<div class="arm_divider"></div>
					<div class="armclear"></div>
                    <!-- *****************************/.Google Settings./***************************** -->
                    <div class="page_sub_title"><?php _e('Google SignIn', 'ARMember'); ?></div>
                    <?php 
                    $google_status = (!empty($social_options)) ? (isset($social_options['google']['status']) ? $social_options['google']['status'] : 0) : 0;
                    $googleDisabledAttr = ($google_status == 0) ? 'disabled="disabled"' : '';
                    $googleReadonlyAttr = ($google_status == 0) ? 'readonly="readonly"' : '';
                    ?>
                    <table class="form-table">
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_google_status"><?php _e('Active', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <div class="armswitch arm_global_setting_switch">
                                    <input id="arm_google_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][google][status]" value="1" data-stype="google" <?php checked($google_status, 1);?>>
                                    <label for="arm_google_status" class="armswitch_label"></label>
                                    <?php $arm_sc_google_icon = (!empty($social_options)) ? ((!empty($social_options['google']['icon'])) ? $social_options['google']['icon'] : 'google_1.png') : 'google_1.png';
                                    $arm_sc_google_custom_icon = (!empty($social_options)) ? ((!empty($social_options['google']['custom_icon'])) ? $social_options['google']['custom_icon'] : '') : '';?>
                                    <input type="hidden" name="arm_social_settings[options][google][icon]" value="<?php echo $arm_sc_google_icon;?>">
                                </div>
                            </td>
                        </tr>
                        
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_google_client_id"><?php _e('Client ID', 'ARMember'); ?> *</label></th>
                            <td class="arm-form-table-content">
                                <input type="text" name="arm_social_settings[options][google][client_id]" class="arm_input_google" id="arm_google_client_id" value="<?php echo (!empty($social_options) && !empty($social_options['google']['client_id'])) ? $social_options['google']['client_id'] : '';?>" data-msg-required="<?php _e('Client ID can not be left blank.', 'ARMember'); ?>" <?php echo $googleReadonlyAttr;?>>
                            </td>
                        </tr>

                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_vk_app_secret"><?php _e('Authorised JavaScript origins', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <span class="arm_info_text"><?php echo ARM_HOME_URL; ?></span><br><span class="arm_font_size_13"><em><?php _e(' Set Authorised JavaScript origins URL in your Google Web Application settings.', 'ARMember'); ?></em></span>
                            </td>
                        </tr>
                    </table>
                    <div class="arm_divider"></div>
                    <div class="armclear"></div>
					<!-- *****************************/.VK Settings./***************************** -->
					<div class="page_sub_title"><?php _e('VK', 'ARMember'); ?></div>
					<?php 
					$vk_status = (!empty($social_options) && isset($social_options['vk']['status'])) ? $social_options['vk']['status'] : 0;
					$vkDisabledAttr = ($vk_status == 0) ? 'disabled="disabled"' : '';
					$vkReadonlyAttr = ($vk_status == 0) ? 'readonly="readonly"' : '';
                                        $titleTooltip = __('To get more information about how to set vk redirect url. Please refer this', 'ARMember').' <a href="'.MEMBERSHIP_DOCUMENTATION_URL.'" target="_blank">'.__('document', 'ARMember').'</a>. ';
					?>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_vk_status"><?php _e('Active', 'ARMember'); ?></label></th>
							<td class="arm-form-table-content">
								<div class="armswitch arm_global_setting_switch">
									<input id="arm_vk_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][vk][status]" value="1" data-stype="vk" <?php checked($vk_status, 1);?>>
									<label for="arm_vk_status" class="armswitch_label"></label>
                                                                        <?php $arm_sc_vk_icon = (!empty($social_options)) ? ((!empty($social_options['vk']['icon'])) ? $social_options['vk']['icon'] : 'vk_1.png' ) : 'vk_1.png';
                                                                        $arm_sc_vk_custom_icon = (!empty($social_options)) ? ((!empty($social_options['vk']['custom_icon'])) ? $social_options['vk']['custom_icon'] : '') : '';?>
                                                                        <input type="hidden" name="arm_social_settings[options][vk][icon]" value="<?php echo $arm_sc_vk_icon;?>">
								</div>
							</td>
						</tr>
						
						<tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_vk_app_id"><?php _e('App ID', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][vk][app_id]" class="arm_input_vk" id="arm_vk_app_id" value="<?php echo (!empty($social_options) && !empty($social_options['vk']['app_id'])) ? $social_options['vk']['app_id'] : '';?>" data-msg-required="<?php _e('App ID can not be left blank.', 'ARMember'); ?>" <?php echo $vkReadonlyAttr;?> onkeydown="javascript:return checkNumber(event)">
							</td>
						</tr>
                                                <tr class="form-field">
							<th class="arm-form-table-label"><label for="arm_vk_app_secret"><?php _e('App Secure Key', 'ARMember'); ?> *</label></th>
							<td class="arm-form-table-content">
								<input type="text" name="arm_social_settings[options][vk][app_secret]" class="arm_input_vk" id="arm_vk_app_secret" value="<?php echo (!empty($social_options) && !empty($social_options['vk']['app_secret'])) ? $social_options['vk']['app_secret'] : '';?>" data-msg-required="<?php _e('App secure key can not be left blank.', 'ARMember'); ?>" <?php echo $vkReadonlyAttr;?>>
							</td>
						</tr>
                                                <tr class="form-field">
                                                        <th class="arm-form-table-label"><label for="arm_vk_app_secret"><?php _e('Authorized redirect URI', 'ARMember'); ?></label></th>
							<td class="arm-form-table-content">
                                                            <span class="arm_info_text"><?php echo MEMBERSHIP_VIEWS_URL.'/callback/vk_callback.php'; ?></span><br><span class="arm_font_size_13"><em><?php _e('Redirect URI that you should add in VK API.', 'ARMember'); ?></em></span>
							</td>
                                                </tr>
					</table>

                    <!-- Instagram Module -->
                    <div class="arm_divider"></div>
                    <div class="armclear"></div>
                    <div class="page_sub_title"><?php _e('Instagram', 'ARMember'); ?></div>
                    <?php 
                    $insta_status = (!empty($social_options) && isset($social_options['insta']['status'])) ? $social_options['insta']['status'] : 0;
                    $instaDisabledAttr = ($insta_status == 0) ? 'disabled="disabled"' : '';
                    $instaReadonlyAttr = ($insta_status == 0) ? 'readonly="readonly"' : '';
                    ?>
                    <table class="form-table">
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_insta_status"><?php _e('Active', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <div class="armswitch arm_global_setting_switch">
                                    <input id="arm_insta_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][insta][status]" value="1" data-stype="insta" <?php checked($insta_status, 1);?>>

                                    <label for="arm_insta_status" class="armswitch_label"></label>

                                    <?php $arm_sc_insta_icon = (!empty($social_options)) ? ((!empty($social_options['insta']['icon'])) ? $social_options['insta']['icon'] : 'insta_1.png' ) : 'insta_1.png';
                                    $arm_sc_insta_custom_icon = (!empty($social_options)) ? ((!empty($social_options['insta']['custom_icon'])) ? $social_options['insta']['custom_icon'] : '') : '';?>
                                    <input type="hidden" name="arm_social_settings[options][insta][icon]" value="<?php echo $arm_sc_insta_icon;?>">
                                </div>
                            </td>
                        </tr>
                        
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_insta_client_id"><?php _e('Client ID', 'ARMember'); ?> *</label></th>
                            <td class="arm-form-table-content">
                                <input type="text" name="arm_social_settings[options][insta][client_id]" class="arm_input_insta" id="arm_insta_client_id" value="<?php echo (!empty($social_options) && !empty($social_options['insta']['client_id'])) ? $social_options['insta']['client_id'] : '';?>" data-msg-required="<?php _e('Client ID can not be left blank.', 'ARMember'); ?>" <?php echo $instaReadonlyAttr;?>">
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_insta_client_secret"><?php _e('Client Secure Key', 'ARMember'); ?> *</label></th>
                            <td class="arm-form-table-content">
                                <input type="text" name="arm_social_settings[options][insta][client_secret]" class="arm_input_insta" id="arm_insta_client_secret" value="<?php echo (!empty($social_options) && !empty($social_options['insta']['client_secret'])) ? $social_options['insta']['client_secret'] : '';?>" data-msg-required="<?php _e('App secure key can not be left blank.', 'ARMember'); ?>" <?php echo $instaReadonlyAttr;?>>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_insta_client_secret"><?php _e('Valid redirect URIs', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <span class="arm_info_text"><?php echo MEMBERSHIP_VIEWS_URL.'/callback/insta_callback.php'; ?></span><br><span class="arm_font_size_13"><em><?php _e('Redirect URI that you should add in Instagram API.', 'ARMember'); ?></em></span>
                            </td>
                        </tr>
                    </table>
                    <!-- Instagram Module Over -->
                    <!-- Tumblr module -->
                    <div class="arm_divider"></div>
                    <div class="armclear"></div>
                    <div class="page_sub_title"><?php _e('Tumblr', 'ARMember'); ?></div>
                    <?php 
                    $tu_status = (!empty($social_options) && isset($social_options['tumblr']['status'])) ? $social_options['tumblr']['status'] : 0;
                    $tuDisabledAttr = ($tu_status == 0) ? 'disabled="disabled"' : '';
                    $tuReadonlyAttr = ($tu_status == 0) ? 'readonly="readonly"' : '';

                    ?>

                    <table class="form-table">
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_tumblr_status"><?php _e('Active', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <div class="armswitch arm_global_setting_switch">
                                    <input id="arm_tumblr_status" class="armswitch_input arm_social_network_status" type="checkbox" name="arm_social_settings[options][tumblr][status]" value="1" data-stype="tu" <?php checked($tu_status, 1);?>>
                                    <label for="arm_tumblr_status" class="armswitch_label"></label>
                                                                        <?php $arm_sc_tu_icon = (!empty($social_options)) ? ((!empty($social_options['tumblr']['icon'])) ? $social_options['tumblr']['icon'] : 'tu_1.png') : 'tu_1.png'; 
                                                                        $arm_sc_tu_custom_icon = (!empty($social_options)) ? ((!empty($social_options['tumblr']['custom_icon'])) ? $social_options['tumblr']['custom_icon'] : '') : '';?>
                                                                        <input type="hidden" name="arm_social_settings[options][tumblr][icon]" value="<?php echo $arm_sc_tu_icon;?>">
                                </div>
                            </td>
                        </tr>
                        
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_tumblr_consumer_key"><?php _e('Consumer Key', 'ARMember'); ?> *</label></th>
                            <td class="arm-form-table-content">
                                <input type="text" name="arm_social_settings[options][tumblr][consumer_key]" class="arm_input_tu" id="arm_tumblr_consumer_key" value="<?php echo (!empty($social_options) && !empty($social_options['tumblr']['consumer_key'])) ? $social_options['tumblr']['consumer_key'] : '';?>" data-msg-required="<?php _e('Consumer Key can not be left blank.', 'ARMember'); ?>" <?php echo $tuReadonlyAttr;?>>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label for="arm_tumblr_consumer_secret"><?php _e('Consumer Secret', 'ARMember'); ?> *</label></th>
                            <td class="arm-form-table-content">
                                <input type="text" name="arm_social_settings[options][tumblr][consumer_secret]" class="arm_input_tu" id="arm_tumblr_consumer_secret" value="<?php echo (!empty($social_options) && !empty($social_options['tumblr']['consumer_secret'])) ? $social_options['tumblr']['consumer_secret'] : '';?>" data-msg-required="<?php _e('Customer Secret can not be left blank.', 'ARMember'); ?>" <?php echo $tuReadonlyAttr;?>>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th class="arm-form-table-label"><label><?php _e('Callback URL', 'ARMember'); ?></label></th>
                            <td class="arm-form-table-content">
                                <span class="arm_info_text"><?php echo ARM_HOME_URL; ?>/</span><br><span class="arm_font_size_13"><em><?php _e('Callback URLs that you should add in Tumblr Application -> Settings.', 'ARMember'); ?></em></span>
                            </td>
                        </tr>
                    </table>
                    <!-- Tumblr module over -->
                  
				</div>
			</div>
			<div class="armclear"></div>
			<div class="arm_submit_btn_container">
				<input type="hidden" name="s_action" value="arm_update_social_settings">
				<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn" type="submit" id="arm_social_settings_btn" name="arm_social_settings_btn"><?php _e('Save', 'ARMember');?></button>
                <?php wp_nonce_field( 'arm_wp_nonce' );?>
			</div>
        </form>
        <div class="armclear"></div>
	</div>
</div>
