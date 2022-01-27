<?php
global $wpdb, $ARMember, $arm_slugs, $arm_member_forms, $arm_global_settings, $arm_social_feature;
$global_settings = $arm_global_settings->global_settings;
$social_settings = $arm_social_feature->arm_get_social_settings();
$social_options = (!empty($social_settings['options'])) ? $social_settings['options'] : array();

if (!empty($social_networks)) {
    $i = 0;
    foreach ($social_options as $ksn => $activesn) {
	if (isset($formSocialNetworksSettings[$ksn]) && !empty($formSocialNetworksSettings[$ksn])) {
	    if (isset($formSocialNetworksSettings[$ksn]['icon'])) {
		$social_options[$ksn]['icon'] = $formSocialNetworksSettings[$ksn]['icon'];
	    }
	}
	if (in_array($ksn, $social_networks) && isset($social_options[$ksn]['status']) && $social_options[$ksn]['status'] == '1') {
	    $social_options[$ksn]['chk_status'] = 1;
	} else {
	    
	}


	$i++;
    }
}
foreach ($social_options as $ksn => $activesn) {
    if (isset($social_options[$ksn]['status']) && $social_options[$ksn]['status'] == '1') {
	$social_options[$ksn]['active'] = 1;
    } else {
	
    }
}
$newSocialOptions = array();
if (!empty($social_networks_order)) {
    foreach ($social_networks_order as $sn_key) {
        $newSocialOptions[$sn_key] = $social_options[$sn_key];
    }
    $social_options = $newSocialOptions;
}

$browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']);
$browser_name = $browser_info['name'];
$browser_version = $browser_info['version'];
$icon_upload_dir = MEMBERSHIP_UPLOAD_DIR . '/social_icon/';
if (!is_dir($icon_upload_dir)) {
	wp_mkdir_p($icon_upload_dir);
}
$armUploadDir = wp_upload_dir();
?>
<div class="arm_save_social_network_wrapper popup_wrapper">
	<form method="post" action="#" id="arm_save_social_network_wrapper_frm" class="arm_admin_form arm_save_social_network_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_save_social_network_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php _e('Social Network Options','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr>
							<th><?php _e('Select Network', 'ARMember');?></th>
							<td class="arm_social_network_sortable_wrapper">
                                <ul class="arm_social_network_list_ul">
                                    <?php 
                                    foreach ($social_options as $k => $v) {
                                        switch($k) {
                                            case 'facebook':
                                                $fb_status = isset($social_options['facebook']['chk_status']) ? $social_options['facebook']['chk_status'] : 0;
                                                $fb_active_status = isset($social_options['facebook']['active']) ? $social_options['facebook']['active'] : 0;
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_facebook" data-sn_type="facebook">
                                                    <?php 
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="facebook" value="1" name="arm_social_settings[options][facebook][status]" id="arm_sn_fb_status" <?php checked($fb_status, 1)?> <?php echo ($fb_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('Facebook', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'twitter':
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_twitter" data-sn_type="twitter">
                                                    <?php 
                                                    $tw_status = isset($social_options['twitter']['chk_status']) ? $social_options['twitter']['chk_status'] : 0;
                                                    $tw_active_status = isset($social_options['twitter']['active']) ? $social_options['twitter']['active'] : 0;
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="twitter" value="1" name="arm_social_settings[options][twitter][status]" id="arm_sn_tw_status" <?php checked($tw_status, 1)?> <?php echo ($tw_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('Twitter', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'linkedin':
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_linkedin" data-sn_type="linkedin">
                                                    <?php 
                                                    $li_status = isset($social_options['linkedin']['chk_status']) ? $social_options['linkedin']['chk_status'] : 0;
                                                    $li_active_status = isset($social_options['linkedin']['active']) ? $social_options['linkedin']['active'] : 0;
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="linkedin" value="1" name="arm_social_settings[options][linkedin][status]" id="arm_sn_li_status" <?php checked($li_status, 1)?> <?php echo ($li_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('LinkedIn', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'google':
                                                $google_status = isset($social_options['google']['chk_status']) ? $social_options['google']['chk_status'] : 0;
                                                $google_active_status = isset($social_options['google']['active']) ? $social_options['google']['active'] : 0;
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_google" data-sn_type="google">
                                                    <?php 
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="google" value="1" name="arm_social_settings[options][google][status]" id="arm_sn_google_status" <?php checked($google_status, 1)?> <?php echo ($google_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('Google', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'vk':
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_vk" data-sn_type="vk">
                                                    <?php 
                                                    $vk_status = isset($social_options['vk']['chk_status']) ? $social_options['vk']['chk_status'] : 0;
                                                    $vk_active_status = isset($social_options['vk']['active']) ? $social_options['vk']['active'] : 0;
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="vk" value="1" name="arm_social_settings[options][vk][status]" id="arm_sn_vk_status" <?php checked($vk_status, 1)?> <?php echo ($vk_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('VK', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'insta':
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_insta" data-sn_type="insta">
                                                    <?php 
                                                    $insta_status = isset($social_options['insta']['chk_status']) ? $social_options['insta']['chk_status'] : 0;
                                                    $insta_active_status = isset($social_options['insta']['active']) ? $social_options['insta']['active'] : 0;
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="insta" value="1" name="arm_social_settings[options][insta][status]" id="arm_sn_insta_status" <?php checked($insta_status, 1)?> <?php echo ($insta_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('Instagram', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>
                                                    
                                                </li>
                                                <?php
                                                break;
                                            case 'tumblr':
                                                ?>
                                                <li class="arm_social_network_list_li arm_social_network_list_li_tumblr" data-sn_type="tumblr">
                                                    <?php 
                                                    $tu_status = isset($social_options['tumblr']['chk_status']) ? $social_options['tumblr']['chk_status'] : 0;
                                                    $tu_active_status = isset($social_options['tumblr']['active']) ? $social_options['tumblr']['active'] : 0;
                                                    ?>
                                                    <div class="arm_sn_heading_wrapper">
                                                        <label>
                                                            <input type="checkbox" class="arm_icheckbox arm_sn_active_checkbox" data-sn_type="tumblr" value="1" name="arm_social_settings[options][tumblr][status]" id="arm_sn_tu_status" <?php checked($tu_status, 1)?> <?php echo ($tu_active_status != 1) ? 'disabled="disabled"' : '';?>>
                                                            <span><?php _e('Tumblr', 'ARMember');?></span>
                                                        </label>
                                                        <div class="arm_list_sortable_icon"></div>
                                                    </div>                                                    
                                                </li>
                                                <?php
                                            break;                                                         
                                            default:
                                            break;
                                        } 
                                    }
                                    ?>
                                </ul>
                                <span class="arm_info_text"><?php _e('Please configure social networks in', 'ARMember');?> <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=social_options'); ?>" target="_blank"><?php _e('General Settings', 'ARMember');?></a></span>
							</td>
						</tr>
						<tr>
							<th><?php _e('Change Skin', 'ARMember');?></th>
                            <td class="arm_social_network_icons_wrapper">
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('Facebook Icon', 'ARMember');?></label>
                                    <?php 
									$fb_icons = $arm_social_feature->arm_get_social_network_icons('facebook');
                                    $fbCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('facebook');
									$social_options['facebook']['icon'] = (!empty($social_options)) ? ((!empty($social_options['facebook']['icon'])) ? $social_options['facebook']['icon'] : 'fb_1.png') : 'fb_1.png';
									?>
                                    <?php if(!empty($fb_icons)):?>
                                    <?php foreach($fb_icons as $icon => $url):?>
										<div class="arm_social_login_icon_container">
											<label>
												<input type="radio" class="arm_iradio arm_input_fb arm_icon_facebook" name="arm_social_settings[options][facebook][icon]" value="<?php echo $icon;?>" <?php checked($social_options['facebook']['icon'], $icon);?> data-url="<?php echo $url;?>">
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                        
                                                    ?>                                                                        
												<img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
											</label>
                                            <?php if (in_array($icon, array_keys($fbCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="facebook" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
										</div>
									<?php endforeach;?>
                                    <?php endif;?>
									<div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                        <!--<div class="arm_social_login_label_container">
<input type="radio" class="arm_iradio arm_input_fb arm_icon_facebook" name="arm_social_settings[options][facebook][icon]" value="custom" <?php checked($social_options['facebook']['icon'], 'custom'); ?>>
                                           
                                        </div>-->
                                        <?php
                                        $fb_icon_type = 'type="file"';
                                        $fb_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $fb_icon_type = 'type="text" data-iframe="arm_fbs_icon"';
                                            $fb_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_fbs_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_fbs_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        if ($social_options['facebook']['icon'] == 'custom' && !empty($social_options['facebook']['custom_icon'])) {
                                            $isIconExists = !empty($social_options['facebook']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['facebook']['custom_icon'])) ? true : false;
                                            if ($isIconExists) {
                                                $fb_icon_upload_btn_display = "style='display:none;'";
                                                $fb_icon_remove_btn_display = "style='display:block;'";
                                                $fb_icon_file_exists = 1;
                                            } else {
                                                $fb_icon_upload_btn_display = "style='display:block;'";
                                                $fb_icon_remove_btn_display = "style='display:none;'";
                                                $fb_icon_file_exists = 0;
                                            }
                                        } else {
                                            $fb_icon_upload_btn_display = "style='display:block;'";
                                            $fb_icon_remove_btn_display = "style='display:none;'";
                                            $fb_icon_file_exists = 0;
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_fbs_icon">
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $fb_icon_type; ?> id="arm_social_login_fb_custom_icon" class="arm_input_fb armFileUpload <?php echo $fb_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
<!--                                            <div class="armFileRemoveContainer" <?php echo $fb_icon_remove_btn_display;?>>
                                                <div class="armFileRemove-icon"></div><?php _e('Remove', 'ARMember');?>
                                            </div>-->
                                            <div class="arm_old_uploaded_file"><?php
                                                if ($social_options['facebook']['icon'] == 'custom' && $fb_icon_file_exists == 1) {
                                                    echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['facebook']['custom_icon'] . '" id="arm_facebook_custom_icon"/>';
                                                }
                                            ?></div>
<!--                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar" style="width:0%;"></div>
                                            </div>-->
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0" ></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_fb_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['facebook']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['facebook']['custom_icon'])) ? $social_options['facebook']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember');?>" data-icon="" value="<?php echo $social_options['facebook']['custom_icon'];?>" name="arm_social_settings[options][facebook][custom_icon]">
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('Twitter Icon', 'ARMember');?></label>
                                    <?php 
									$tw_icons = $arm_social_feature->arm_get_social_network_icons('twitter');
                                    $twCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('twitter');
									$social_options['twitter']['icon'] = (!empty($social_options['twitter']['icon'])) ? $social_options['twitter']['icon'] : 'tw_1.png';
									?>
                                    <?php if(!empty($tw_icons)):?>
                                    <?php foreach($tw_icons as $icon => $url):?>
										<div class="arm_social_login_icon_container">
											<label>
												<input type="radio" class="arm_iradio arm_input_tw arm_icon_twitter" name="arm_social_settings[options][twitter][icon]" value="<?php echo $icon;?>" <?php checked($social_options['twitter']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                    ?>  
												<img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
											</label>
                                            <?php if (in_array($icon, array_keys($twCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="twitter" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
										</div>
									<?php endforeach;?>
                                    <?php endif;?>
									<div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                      <!--   <div class="arm_social_login_label_container">
                                           <label>
                                                <input type="radio" class="arm_iradio arm_input_tw arm_icon_twitter" name="arm_social_settings[options][twitter][icon]" value="custom" <?php checked($social_options['twitter']['icon'], 'custom'); ?>>
                                            </label>
                                        </div>   -->                                                                     
                                        <?php
                                        $tw_icon_type = 'type="file"';
                                        $tw_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $tw_icon_type = 'type="text" data-iframe="arm_twitters_icon"';
                                            $tw_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_twitters_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_twitters_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_twitters_icon">   
                                            <?php
                                            if ($social_options['twitter']['icon'] == 'custom' && !empty($social_options['twitter']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['twitter']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['twitter']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $tw_icon_upload_btn_display = "style='display:none;'";
                                                    $tw_icon_remove_btn_display = "style='display:block;'";
                                                    $tw_icon_file_exists = 1;
                                                } else {
                                                    $tw_icon_upload_btn_display = "style='display:block;'";
                                                    $tw_icon_remove_btn_display = "style='display:none;'";
                                                    $tw_icon_file_exists = 0;
                                                }
                                            } else {
                                                $tw_icon_upload_btn_display = "style='display:block;'";
                                                $tw_icon_remove_btn_display = "style='display:none;'";
                                                $tw_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $tw_icon_type; ?> id="arm_social_login_tw_custom_icon" class="arm_input_tw armFileUpload <?php echo $tw_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['twitter']['icon'] == 'custom' && $tw_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['twitter']['custom_icon'] . '" id="arm_twitter_custom_icon"/>';
                                            }
                                            ?></div>
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0" ></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_tw_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['twitter']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['twitter']['custom_icon'])) ? $social_options['twitter']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember');?>" data-icon="" value="<?php echo $social_options['twitter']['custom_icon']; ?>" name="arm_social_settings[options][twitter][custom_icon]" >
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('LinkedIn Icon', 'ARMember');?></label>
                                    <?php 
									$li_icons = $arm_social_feature->arm_get_social_network_icons('linkedin');
                                    $liCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('linkedin');
									$social_options['linkedin']['icon'] = (!empty($social_options['linkedin']['icon'])) ? $social_options['linkedin']['icon'] : 'li_1.png';
									?>
                                    <?php if(!empty($li_icons)):?>
									<?php foreach($li_icons as $icon => $url):?>
										<div class="arm_social_login_icon_container">
											<label>
												<input type="radio" class="arm_iradio arm_input_li arm_icon_linkedin" name="arm_social_settings[options][linkedin][icon]" value="<?php echo $icon;?>" <?php checked($social_options['linkedin']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                    ?>  
												<img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
											</label>
                                            <?php if (in_array($icon, array_keys($liCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="linkedin" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
										</div>
									<?php endforeach;?>
                                    <?php endif;?>
									<div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                       <!--  <div class="arm_social_login_label_container">
                                           <label>
                                                <input type="radio" class="arm_iradio arm_input_li arm_icon_linkedin" name="arm_social_settings[options][linkedin][icon]" value="custom" <?php checked($social_options['linkedin']['icon'], 'custom'); ?>>
                                            </label>
                                        </div>-->
                                        <?php
                                        $li_icon_type = 'type="file"';
                                        $li_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $li_icon_type = 'type="text" data-iframe="arm_linkedins_icon"';
                                            $li_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_linkedins_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_linkedins_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_linkedins_icon">   
                                            <?php
                                            if ($social_options['linkedin']['icon'] == 'custom' && !empty($social_options['linkedin']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['linkedin']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['linkedin']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $li_icon_upload_btn_display = "style='display:none;'";
                                                    $li_icon_remove_btn_display = "style='display:block;'";
                                                    $li_icon_file_exists = 1;
                                                } else {
                                                    $li_icon_upload_btn_display = "style='display:block;'";
                                                    $li_icon_remove_btn_display = "style='display:none;'";
                                                    $li_icon_file_exists = 0;
                                                }
                                            } else {
                                                $li_icon_upload_btn_display = "style='display:block;'";
                                                $li_icon_remove_btn_display = "style='display:none;'";
                                                $li_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $li_icon_type; ?> id="arm_social_login_li_custom_icon" class="arm_input_li armFileUpload <?php echo $li_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['linkedin']['icon'] == 'custom' && $li_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['linkedin']['custom_icon'] . '" id="arm_linkedin_custom_icon"/>';
                                            }
                                            ?></div>
                                            
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0"></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_li_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['linkedin']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['linkedin']['custom_icon'])) ? $social_options['linkedin']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember'); ?>" data-icon="" value="<?php echo $social_options['linkedin']['custom_icon']; ?>" name="arm_social_settings[options][linkedin][custom_icon]">
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('Google Icon', 'ARMember');?></label>
                                    <?php 
                                    $google_icons = $arm_social_feature->arm_get_social_network_icons('google');
                                    $googleCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('google');
                                    $social_options['google']['icon'] = (!empty($social_options['google']['icon'])) ? $social_options['google']['icon'] : 'google_1.png';
                                    ?>
                                    <?php if(!empty($google_icons)):?>
                                    <?php foreach($google_icons as $icon => $url):?>
                                        <div class="arm_social_login_icon_container">
                                            <label>
                                                <input type="radio" class="arm_iradio arm_input_google arm_icon_google" name="arm_social_settings[options][google][icon]" value="<?php echo $icon;?>" <?php checked($social_options['google']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                    ?>  
                                                <img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
                                            </label>
                                            <?php if (in_array($icon, array_keys($googleCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="google" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
                                        </div>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                    <div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                       <!--  <div class="arm_social_login_label_container">
                                           <label>
                                                <input type="radio" class="arm_iradio arm_input_google arm_icon_google" name="arm_social_settings[options][google][icon]" value="custom" <?php checked($social_options['google']['icon'], 'custom'); ?>>
                                            </label>
                                        </div>-->
                                        <?php
                                        $google_icon_type = 'type="file"';
                                        $google_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $google_icon_type = 'type="text" data-iframe="arm_googles_icon"';
                                            $google_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_googles_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_googles_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_googles_icon">   
                                            <?php
                                            if ($social_options['google']['icon'] == 'custom' && !empty($social_options['google']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['google']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['google']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $google_icon_upload_btn_display = "style='display:none;'";
                                                    $google_icon_remove_btn_display = "style='display:block;'";
                                                    $google_icon_file_exists = 1;
                                                } else {
                                                    $google_icon_upload_btn_display = "style='display:block;'";
                                                    $google_icon_remove_btn_display = "style='display:none;'";
                                                    $google_icon_file_exists = 0;
                                                }
                                            } else {
                                                $google_icon_upload_btn_display = "style='display:block;'";
                                                $google_icon_remove_btn_display = "style='display:none;'";
                                                $google_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $google_icon_type; ?> id="arm_social_login_google_custom_icon" class="arm_input_google armFileUpload <?php echo $google_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['google']['icon'] == 'custom' && $google_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['google']['custom_icon'] . '" id="arm_google_custom_icon"/>';
                                            }
                                            ?></div>
                                            
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0"></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_google_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['google']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['google']['custom_icon'])) ? $social_options['google']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember'); ?>" data-icon="" value="<?php echo $social_options['google']['custom_icon']; ?>" name="arm_social_settings[options][google][custom_icon]">
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('VK Icon', 'ARMember');?></label>
                                    <?php 
									$vk_icons = $arm_social_feature->arm_get_social_network_icons('vk');
                                    $vkCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('vk');
									$social_options['vk']['icon'] = (!empty($social_options['vk']['icon'])) ? $social_options['vk']['icon'] : 'vk_1.png';
									?>
                                    <?php if(!empty($vk_icons)):?>
									<?php foreach($vk_icons as $icon => $url):?>
										<div class="arm_social_login_icon_container">
											<label>
												<input type="radio" class="arm_iradio arm_input_vk arm_icon_vk" name="arm_social_settings[options][vk][icon]" value="<?php echo $icon;?>" <?php checked($social_options['vk']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                <?php
                                                if(file_exists(strstr($url, "//"))){
                                                        $url_icon =strstr($url, "//");
                                                    }else if(file_exists($url)){
                                                       $url_icon = $url;
                                                    }else{
                                                         $url_icon = $url;
                                                    }
                                                ?>
												<img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
											</label>
                                            <?php if (in_array($icon, array_keys($vkCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="vk" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
										</div>
									<?php endforeach;?>
                                    <?php endif;?>
									<div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
<!--                                        <div class="arm_social_login_label_container">
                                            <label>
                                                <input type="radio" class="arm_iradio arm_input_vk arm_icon_vk" name="arm_social_settings[options][vk][icon]" value="custom" <?php checked($social_options['vk']['icon'], 'custom');?>>
                                            </label>
                                        </div>-->
                                        <?php
                                        $vk_icon_type = 'type="file"';
                                        $vk_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $vk_icon_type = 'type="text" data-iframe="arm_vks_icon"';
                                            $vk_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_vks_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_vks_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>      
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_vks_icon">   
                                            <?php
                                            if ($social_options['vk']['icon'] == 'custom' && !empty($social_options['vk']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['vk']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['vk']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $vk_icon_upload_btn_display = "style='display:none;'";
                                                    $vk_icon_remove_btn_display = "style='display:block;'";
                                                    $vk_icon_file_exists = 1;
                                                } else {
                                                    $vk_icon_upload_btn_display = "style='display:block;'";
                                                    $vk_icon_remove_btn_display = "style='display:none;'";
                                                    $vk_icon_file_exists = 0;
                                                }
                                            } else {
                                                $vk_icon_upload_btn_display = "style='display:block;'";
                                                $vk_icon_remove_btn_display = "style='display:none;'";
                                                $vk_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $vk_icon_type; ?> id="arm_social_login_vk_custom_icon" class="arm_input_vk armFileUpload <?php echo $vk_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['vk']['icon'] == 'custom' && $vk_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['vk']['custom_icon'] . '" id="arm_vk_custom_icon"/>';
                                            }
                                            ?></div>
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0" ></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_vk_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['vk']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['vk']['custom_icon'])) ? $social_options['vk']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember'); ?>" data-icon="" value="<?php echo $social_options['vk']['custom_icon'];?>" name="arm_social_settings[options][vk][custom_icon]">
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('Instagram', 'ARMember');?></label>
                                    <?php 
                                    $insta_icons = $arm_social_feature->arm_get_social_network_icons('insta');
                                    $instaCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('insta');
                                    $social_options['insta']['icon'] = (!empty($social_options['insta']['icon'])) ? $social_options['insta']['icon'] : 'gp_1.png';
                                    ?>
                                    <?php if(!empty($insta_icons)):?>
                                    <?php foreach($insta_icons as $icon => $url):?>
                                        <div class="arm_social_login_icon_container">
                                            <label>
                                                <input type="radio" class="arm_iradio arm_input_insta arm_icon_insta" name="arm_social_settings[options][insta][icon]" value="<?php echo $icon;?>" <?php checked($social_options['insta']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                    ?>  
                                                <img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
                                            </label>
                                            <?php if (in_array($icon, array_keys($instaCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="insta" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
                                        </div>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                    <div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                        <?php
                                        $insta_icon_type = 'type="file"';
                                        $insta_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $insta_icon_type = 'type="text" data-iframe="arm_linkedins_icon"';
                                            $insta_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_instas_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_instas_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_instas_icon">   
                                            <?php
                                            if ($social_options['insta']['icon'] == 'custom' && !empty($social_options['insta']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['insta']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['insta']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $insta_icon_upload_btn_display = "style='display:none;'";
                                                    $insta_icon_remove_btn_display = "style='display:block;'";
                                                    $insta_icon_file_exists = 1;
                                                } else {
                                                    $insta_icon_upload_btn_display = "style='display:block;'";
                                                    $insta_icon_remove_btn_display = "style='display:none;'";
                                                    $insta_icon_file_exists = 0;
                                                }
                                            } else {
                                                $insta_icon_upload_btn_display = "style='display:block;'";
                                                $insta_icon_remove_btn_display = "style='display:none;'";
                                                $insta_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $insta_icon_type; ?> id="arm_social_login_insta_custom_icon" class="arm_input_insta armFileUpload <?php echo $insta_icon_type; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['insta']['icon'] == 'custom' && $insta_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['insta']['custom_icon'] . '" id="arm_insta_custom_icon"/>';
                                            }
                                            ?></div>
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0"></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_insta_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['insta']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['insta']['custom_icon'])) ? $social_options['insta']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember'); ?>" data-icon="" value="<?php echo $social_options['insta']['custom_icon']; ?>" name="arm_social_settings[options][insta][custom_icon]">
                                        </div>
                                    </div>
                                </div>
                                <div class="arm_social_network_icons_block">
                                    <label><?php _e('Tumblr Icon', 'ARMember');?></label>
                                    <?php 
                                    $tu_icons = $arm_social_feature->arm_get_social_network_icons('tumblr');
                                    $tuCustomIcons = $arm_social_feature->arm_get_social_network_custom_icons('tumblr');
                                    $social_options['tumblr']['icon'] = (!empty($social_options['tumblr']['icon'])) ? $social_options['tumblr']['icon'] : 'tu_1.png';
                                    ?>
                                    <?php if(!empty($tu_icons)):?>
                                    <?php foreach($tu_icons as $icon => $url):?>
                                        <div class="arm_social_login_icon_container">
                                            <label>
                                                <input type="radio" class="arm_iradio arm_input_tu arm_icon_tumblr" name="arm_social_settings[options][tumblr][icon]" value="<?php echo $icon;?>" <?php checked($social_options['tumblr']['icon'], $icon); ?> data-url="<?php echo $url;?>">
                                                    <?php
                                                    if(file_exists(strstr($url, "//"))){
                                                            $url_icon =strstr($url, "//");
                                                        }else if(file_exists($url)){
                                                           $url_icon = $url;
                                                        }else{
                                                             $url_icon = $url;
                                                        }
                                                    ?>  
                                                <img class="arm_social_login_image" src="<?php echo ($url_icon);?>"/>
                                            </label>
                                            <?php if (in_array($icon, array_keys($tuCustomIcons))): ?>
                                            <a href="javascript:void(0)" class="arm_remove_social_network_icon armhelptip" title="<?php _e('Delete Icon', 'ARMember');?>" data-sn_type="tumblr" data-file_name="<?php echo $icon;?>" data-file_url="<?php echo $url;?>" ><i class="armfa armfa-remove armfa-1x"></i></a>
                                            <?php endif;?>
                                        </div>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                    <div class="armclear"></div>
                                    <label class="arm_custom_image_label"><?php _e('Custom Image', 'ARMember');?></label>
                                    <div class="arm_social_login_icon_container1">
                                      <!--   <div class="arm_social_login_label_container">
                                           <label>
                                                <input type="radio" class="arm_iradio arm_input_tu arm_icon_tumblr" name="arm_social_settings[options][twitter][icon]" value="custom" <?php checked($social_options['tumblr']['icon'], 'custom'); ?>>
                                            </label>
                                        </div>   -->                                                                     
                                        <?php
                                        $tu_icon_type = 'type="file"';
                                        $tu_icon_class = '';
                                        if ($browser_name == 'Internet Explorer' && $browser_version <= 9) {
                                            $tw_icon_type = 'type="text" data-iframe="arm_tumblrs_icon"';
                                            $tw_icon_class = ' armIEFileUpload';
                                            echo '<div id="arm_tumblrs_icon_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="arm_tumblrs_icon_iframe" src="' . MEMBERSHIP_VIEWS_URL . '/iframeupload.php"></iframe></div>';
                                        }
                                        ?>
                                        <div class="armFileUploadWrapper arm_social_login_custom_icon_container" data-iframe="arm_tumblrs_icon">   
                                            <?php
                                            if ($social_options['tumblr']['icon'] == 'custom' && !empty($social_options['tumblr']['custom_icon'])) {
                                                $isIconExists = !empty($social_options['tumblr']['custom_icon']) && file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_icon/'.basename($social_options['tumblr']['custom_icon'])) ? true : false;
                                                if ($isIconExists) {
                                                    $tu_icon_upload_btn_display = "style='display:none;'";
                                                    $tu_icon_remove_btn_display = "style='display:block;'";
                                                    $tu_icon_file_exists = 1;
                                                } else {
                                                    $tu_icon_upload_btn_display = "style='display:block;'";
                                                    $tu_icon_remove_btn_display = "style='display:none;'";
                                                    $tu_icon_file_exists = 0;
                                                }
                                            } else {
                                                $tu_icon_upload_btn_display = "style='display:block;'";
                                                $tu_icon_remove_btn_display = "style='display:none;'";
                                                $tu_icon_file_exists = 0;
                                            }
                                            ?>
                                            <div class="armFileUploadContainer">
                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember');?>
                                                <input <?php echo $tu_icon_type; ?> id="arm_social_login_tu_custom_icon" class="arm_input_tu armFileUpload <?php echo $tu_icon_class; ?>" data-file_type="social_icon" data-file_size="2" accept=".jpg,.jpeg,.png,.gif,.bmp"/>
                                            </div>
                                            <div class="arm_old_uploaded_file"><?php
                                            if ($social_options['tumblr']['icon'] == 'custom' && $tw_icon_file_exists == 1) {
                                                echo '<img class="arm_social_login_custom_image arm_fildrag_file" src="' . $social_options['tumblr']['custom_icon'] . '" id="arm_tumblr_custom_icon"/>';
                                            }
                                            ?></div>
                                            <div class="armFileUploadProgressBar" style="display: none;">
                                                <div class="armbar arm_width_0" ></div>
                                            </div>
                                            <div class="armFileUploadProgressInfo"></div>
                                            <div id="armFileUploadMsg_arm_social_login_tu_custom_icon" class="armFileMessages"></div>
                                            <div class="arm_old_file"></div>
                                            <?php 
                                             $social_options['tumblr']['custom_icon'] = (!empty($social_options)) ? ((!empty($social_options['tumblr']['custom_icon'])) ? $social_options['tumblr']['custom_icon'] : '') : '';
                                            ?>
                                            <input class="arm_file_url" type="hidden" data-file_type="social_icon" data-msg-invalid="Invalid file selected" data-msg-required="<?php _e('Please Select Icon.', 'ARMember');?>" data-icon="" value="<?php echo $social_options['tumblr']['custom_icon']; ?>" name="arm_social_settings[options][tumblr][custom_icon]" >
                                        </div>
                                    </div>
                                </div>
							</td>
						</tr>
					</table>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img_save_social_network" class="arm_loader_img arm_loader_img_save_social_network" style="position: relative;top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;display: none;" width="20" height="20" />
						<button class="arm_save_btn arm_save_social_network_ok_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_save_social_network_close_btn" type="button"><?php _e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>