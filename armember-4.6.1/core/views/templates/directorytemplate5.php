<?php
global $arm_member_forms, $arm_members_directory;
if (isset($user) && !empty($user))
{
    $tempopt = $templateOpt['arm_options'];
    
    $separator_class = ($n % 3 == 0) ? 'arm_directorytemplate5_last_field' : '';
    $first_class = ($f == 0 || ($f % 3 == 0) ) ? 'arm_first_user_block' : '';
	$fileContent .= '<div class="arm_user_block '.$separator_class.' '.$first_class.'" >';
		$fileContent .= '<a href="' . $user['user_link'] . '" class="arm_dp_user_link"><div class="arm_user_avatar">';
            $fileContent .= $user['profile_picture'] ;
            $fileContent .= "<div class='arm_badges_detail'>";
            $fileContent .= $user['arm_badges_detail'];
            $fileContent .= "</div>";
        $fileContent .= '</div></a>';
        $fileContent .= '<div class="armclear"></div>';
		$fileContent .= '<a class="arm_user_link" href="' . $user['user_link'] . '">' . $user['full_name'];
		$fileContent .= '</a>';
        /*if(isset($tempopt['show_joining']) && $tempopt['show_joining'] == true || $arm_show_joining_date)
        {
        $fileContent .= '<div class="arm_last_active_text">'. $arm_member_since_label . ' ' .$user['user_join_date'].'</div>';
        }
		$fileContent .= '<div class="armclear"></div>';*/
        $fileContent .= $arm_members_directory->arm_template_display_member_details($tempopt,$user,1);
		//$fileContent .= '<div class="arm_view_profile_btn_wrapper"><a href="' . $user['user_link'] . '" class="arm_view_profile_user_link">' . $arm_view_profile_label . '</a></div>';
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
		$fileContent .= '</div>';
	$fileContent .= '</div>';
}