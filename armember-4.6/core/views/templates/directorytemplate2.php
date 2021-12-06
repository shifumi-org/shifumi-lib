<?php 
global $arm_member_forms, $arm_members_directory;
if (isset($user) && !empty($user))
{
    $tempopt = $templateOpt['arm_options'];
    
	$fileContent .= '<div class="arm_user_block">';
		$fileContent .= '<div class="arm_user_block_left">';
		$fileContent .= '<a href="' . $user['user_link'] . '" class="arm_dp_user_link"><div class="arm_user_avatar">' . $user['profile_picture'] . '</div></a>';
		$fileContent .= '</div>';
		$fileContent .= '<div class="arm_user_block_right">';
			$fileContent .= '<a class="arm_user_link" href="' . $user['user_link'] . '">' . $user['full_name'] . '</a>';
			$fileContent .= $user['arm_badges_detail'];
			/*$fileContent .= '<div class="armclear"></div>';
                        if(isset($tempopt['show_joining']) && $tempopt['show_joining'] == true)
                        {
                        $fileContent .= '<div class="arm_last_active_text">'. $arm_member_since_label . ' ' .$user['user_join_date'].'</div>';
                        }*/
            $fileContent .= $arm_members_directory->arm_template_display_member_details($tempopt,$user,1);
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
		$fileContent .= '<div class="armclear"></div>';
	$fileContent .= '</div>';
}
