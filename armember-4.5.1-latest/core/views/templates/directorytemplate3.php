<?php 
if (isset($user) && !empty($user))
{
	global $arm_member_forms,$arm_members_directory;
    $tempopt = $templateOpt['arm_options'];
    $fileContent .= '<div class="arm_user_block">';
    if(preg_match("@^http@", $user['profile_cover'])){
    $temp_data = explode("://", $user['profile_cover']);
        $cover_url = '//' . $temp_data[1];
    }else{
        $cover_url =$user['profile_cover'];
    }
    $profile_template = $arm_members_directory->arm_get_template_by_id(1);
    $profile_template_opt = $profile_template['arm_options'];
    
    $default_cover = $profile_template_opt['default_cover'];
    $cover_img_url = ($user['profile_cover'] !== '' ) ? "<img src='" . $cover_url . "' style='width:100%;height:100%;'>" : '<img src="'.$default_cover.'" style="width:100%;height:100%;" />';
		$fileContent .= '<div class="arm_cover_bg_wrapper">'.$cover_img_url.'</div>';
		$fileContent .= '<a href="' . $user['user_link'] . '" class="arm_dp_user_link"><div class="arm_user_avatar">' . $user['profile_picture'] . '</div></a>';
		$fileContent .= '<div class="armclear"></div>';
		$fileContent .= '<a class="arm_user_link" href="' . $user['user_link'] . '"><span>' . $user['full_name'].'</span></a>';
		$fileContent .= $user['arm_badges_detail'];
		$fileContent .= $arm_members_directory->arm_template_display_member_details($tempopt,$user,1);
		$fileContent .= '<div class="armclear"></div>';
        $fileContent .= '<div class="arm_view_profile_btn_wrapper"><a href="' . $user['user_link'] . '" class="arm_view_profile_user_link">' . $arm_view_profile_label . '</a></div>';
		$fileContent .= '<div class="armclear"></div>';
		$fileContent .= "<div class='arm_user_social_blocks'>";
        $slected_social_fields = isset($tempopt['arm_social_fields']) ? $tempopt['arm_social_fields'] : array();
        if (!empty($slected_social_fields)) {
            foreach ($slected_social_fields as $skey) {
                if (isset($args['is_preview']) && $args['is_preview'] == 1) {
                    $fileContent .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='#'></a></div>";
                } else {
                    $spfMetaKey = 'arm_social_field_'.$skey;
                    if (in_array($skey, $slected_social_fields)) {
                        $skey_field = get_user_meta($user['ID'],$spfMetaKey,true);
                        if( isset($skey_field) && !empty($skey_field) ) {
                            $fileContent .= "<div class='arm_social_prof_div arm_user_social_fields arm_social_field_{$skey}'><a target='_blank' href='{$skey_field}'></a></div>";
                        }
                    }
                }
            }
        }
		$fileContent .= "</div>";
	$fileContent .= '</div>';
}