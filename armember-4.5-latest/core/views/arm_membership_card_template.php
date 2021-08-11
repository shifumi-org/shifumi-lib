<?php
$view_type=!empty($_REQUEST['view_type']) ? $_REQUEST['view_type'] : '';
$arm_mcard_id = !empty($_REQUEST['arm_mcard_id']) ? $_REQUEST['arm_mcard_id'] : '';
$plan_id = !empty($_REQUEST['plan_id']) ? $_REQUEST['plan_id'] : '';
$iframe_id = !empty($_REQUEST['iframe_id']) ? $_REQUEST['iframe_id'] : '';
$user_id = !empty($_REQUEST['member_id']) ? $_REQUEST['member_id'] : 0;

$arm_card_html_view='';
if(empty($view_type)){
    $arm_card_html_view .='<script type="text/javascript">function arm_print_membership_card_content() {window.print();}</script>';
}


if(isset($arm_mcard_id) && isset($plan_id) && isset($iframe_id))
{

            if(is_user_logged_in() && !empty($arm_mcard_id) || ($view_type=="ARMPDF" && !empty($arm_mcard_id) && !empty($user_id))) 
            {
                if($user_id==0) {
                    $user_id = get_current_user_id();        
                }
            
            $user_info = get_user_meta($user_id);
            if(!empty($user_info["arm_user_plan_ids"])) {
                $user_plans = $user_info["arm_user_plan_ids"][0];
                if(!empty($user_plans)) {
                    $user_plans = maybe_unserialize($user_plans);
                    global $wpdb, $ARMember, $arm_member_forms, $arm_members_directory;
                    $temps = $wpdb->get_results("SELECT arm_options FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE arm_id = {$arm_mcard_id} AND arm_type = 'arm_card' ", ARRAY_A);
                    
                    if(!empty($temps)) {
                        $card_opts = array_column($temps, "arm_options");
                        $card_opts = maybe_unserialize($card_opts[0]);
                        $card_opts["arm_mcard_id"] = !empty($arm_mcard_id) ? $arm_mcard_id : 0;
                        if(!empty($card_opts["plans"])) {
                            $user_plans = array_intersect($card_opts["plans"], $user_plans);
                        }
                        if(!empty($user_plans)) {
                            $print_icon = $card_css = $arm_card_ttl_font = $arm_card_lbl_font = $arm_card_content_font = "";
                            if(!empty($card_opts['arm_card'])) {

                                $company_logo = !empty($card_opts["other_opts"]["company_logo"]) ? $card_opts["other_opts"]["company_logo"] :'';

                                $card_background = !empty($card_opts["other_opts"]["card_background"]) ? $card_opts["other_opts"]["card_background"] :'';

                                $display_avatar = (isset($card_opts["display_avatar"]) && ''!=$card_opts["display_avatar"]) ? $card_opts["display_avatar"] : 0;

                                $arm_card_ttl_font_family = !empty($card_opts["title_font"]["font_family"]) ? $card_opts["title_font"]["font_family"] : "Roboto";
				$arm_card_ttl_font_family = ($arm_card_ttl_font_family == 'inherit') ? '' : $arm_card_ttl_font_family;
                                if (!empty($arm_card_ttl_font_family)) {
                                $tempFontFamilys = array($arm_card_ttl_font_family);
                                $gFontUrl = $arm_member_forms->arm_get_google_fonts_url($tempFontFamilys);
                                if(empty($gFontUrl)) {
                                    $gFontUrl = $arm_member_forms->arm_get_google_fonts_url(array("Roboto"));
                                }
                        	if($view_type=="ARMPDF"){
                            		$card_css_file_request = wp_remote_get($gFontUrl);
                            		$card_css_file_response = wp_remote_retrieve_body( $card_css_file_request );
                            		$card_css_file_response=str_replace('@charset "utf-8";','', $card_css_file_response);
                            		$card_css .=$card_css_file_response; 
                        	}else{
					wp_enqueue_style( 'google-font-ttl-'.$card_opts['arm_card'], $gFontUrl, array(), MEMBERSHIP_VERSION );
				}
                                /*$arm_card_ttl_font = "<link id='google-font-ttl-". $card_opts['arm_card'] ."' rel='stylesheet' type='text/css' href='".$gFontUrl."' />";
                                $card_css .= $arm_card_ttl_font;*/
                                }
                                $arm_card_lbl_font_family = !empty($card_opts["label_font"]["font_family"]) ? $card_opts["label_font"]["font_family"] : "Roboto";
				$arm_card_lbl_font_family = ($arm_card_lbl_font_family == 'inherit') ? '' : $arm_card_lbl_font_family;
                                if (!empty($arm_card_lbl_font_family)) {
                                $tempFontFamilys = array($arm_card_lbl_font_family);
                                $gFontUrl = $arm_member_forms->arm_get_google_fonts_url($tempFontFamilys);
                                if(empty($gFontUrl)) {
                                    $gFontUrl = $arm_member_forms->arm_get_google_fonts_url(array("Roboto"));
                                }
                        if($view_type=="ARMPDF"){
                            $card_css_file_request = wp_remote_get($gFontUrl);
                            $card_css_file_response = wp_remote_retrieve_body( $card_css_file_request );
                            $card_css_file_response=str_replace('@charset "utf-8";','', $card_css_file_response);
                            $card_css .=$card_css_file_response; 
                        }else{
				wp_enqueue_style( 'google-font-lbl-' . $card_opts['arm_card'], $gFontUrl, array(), MEMBERSHIP_VERSION );
				}
				
                                /*$arm_card_lbl_font = "<link id='google-font-lbl-". $card_opts['arm_card'] ."' rel='stylesheet' type='text/css' href='".$gFontUrl."' />";
                                $card_css .= $arm_card_lbl_font;*/
                                }
                                $card_opts_title_font = !empty($card_opts["title_font"]["font_family"]) && ($card_opts["title_font"]["font_family"] != 'inherit') ? "font-family: ".$card_opts["title_font"]["font_family"].";" : "";
                                $card_opts_label_font = !empty($card_opts["label_font"]["font_family"]) && ($card_opts["label_font"]["font_family"] != 'inherit') ? "font-family: ".$card_opts["label_font"]["font_family"].";" : "";
                                $card_opts_content_font = !empty($card_opts["content_font"]["font_family"]) && ($card_opts["content_font"]["font_family"] != 'inherit') ? "font-family: ".$card_opts["content_font"]["font_family"].";" : "";

                                $arm_card_content_font_family = !empty($card_opts["content_font"]["font_family"]) ? $card_opts["content_font"]["font_family"] : "Roboto";
				$arm_card_content_font_family = ($arm_card_content_font_family == 'inherit') ? '' : $arm_card_content_font_family;
                                if (!empty($arm_card_content_font_family)) {
                                $tempFontFamilys = array($arm_card_content_font_family);
                                $gFontUrl = $arm_member_forms->arm_get_google_fonts_url($tempFontFamilys);
                                if(empty($gFontUrl)) {
                                    $gFontUrl = $arm_member_forms->arm_get_google_fonts_url(array("Roboto"));
                                }
                        if($view_type=="ARMPDF"){
                            $card_css_file_request = wp_remote_get($gFontUrl);
                            $card_css_file_response = wp_remote_retrieve_body( $card_css_file_request );
                            $card_css_file_response=str_replace('@charset "utf-8";','', $card_css_file_response);
                            //$card_css_file_response="@import url('".$gFontUrl."')";

                            $card_css .=$card_css_file_response; 
                        }else{
				wp_enqueue_style( 'google-font-cnt-' . $card_opts['arm_card'], $gFontUrl, array(), MEMBERSHIP_VERSION );
				}
                                /*$arm_card_content_font = "<link id='google-font-cnt-". $card_opts['arm_card'] ."' rel='stylesheet' type='text/css' href='".$gFontUrl."' />";
                                $card_css .= $arm_card_content_font;*/
                                }
                                $card_css_file = MEMBERSHIP_VIEWS_URL.'/templates/'.$card_opts['arm_card'].'.css';
                        if($view_type=="ARMPDF"){
                            $card_css_file_request = wp_remote_get($card_css_file);
                            $card_css_file_response = wp_remote_retrieve_body( $card_css_file_request );
                            $card_css_file_response=str_replace('@charset "utf-8";','', $card_css_file_response);
                            $card_css .=$card_css_file_response; 
                        }else{
            			     $card_css .= "<style type='text/css'>";		
            			}	
                        $card_css .=".{$card_opts['arm_card']}.arm_membership_card_template_wrapper.arm_card_".$arm_mcard_id." {
                                    background-color:".(!empty($card_opts["custom"]["bg_color"]) ? $card_opts["custom"]["bg_color"] : "#0073c6").";
                                    border:1px solid ".(!empty($card_opts["custom"]["bg_color"]) ? $card_opts["custom"]["bg_color"] : "#0073c6").";
                                }
                                .{$card_opts['arm_card']}.arm_card_".$arm_mcard_id." .arm_card_title {
                                    color:".(!empty($card_opts["custom"]["title_color"]) ? $card_opts["custom"]["title_color"] : "#ffffff").";
                                    font-size:".(!empty($card_opts["title_font"]["font_size"]) ? $card_opts["title_font"]["font_size"] : "30")."px;
                                    ".$card_opts_title_font."
                                    font-weight:".(!empty($card_opts["title_font"]["font_bold"]) ? "bold" : "normal").";
                                    font-style:".(!empty($card_opts["title_font"]["font_italic"]) ? "italic" : "normal").";
                                    text-decoration:".(!empty($card_opts["title_font"]["font_decoration"]) ? $card_opts["title_font"]["font_decoration"] : "none").";
                                }
                                .{$card_opts['arm_card']}.arm_card_".$arm_mcard_id." .arm_card_label {
                                    color:".(!empty($card_opts["custom"]["label_color"]) ? $card_opts["custom"]["label_color"] : "#ffffff").";
                                    font-size:".(!empty($card_opts["label_font"]["font_size"]) ? $card_opts["label_font"]["font_size"] : "16")."px;
                                    line-height:".(!empty($card_opts["label_font"]["font_size"]) ? ($card_opts["label_font"]["font_size"] + 4) : "16")."px;
                                    ".$card_opts_label_font."
                                    font-weight:".(!empty($card_opts["label_font"]["font_bold"]) ? "bold" : "normal").";
                                    font-style:".(!empty($card_opts["label_font"]["font_italic"]) ? "italic" : "normal").";
                                    text-decoration:".(!empty($card_opts["label_font"]["font_decoration"]) ? $card_opts["label_font"]["font_decoration"] : "none").";
                                }
                                .{$card_opts['arm_card']}.arm_card_".$arm_mcard_id." .arm_card_value {
                                    color:".(!empty($card_opts["custom"]["font_color"]) ? $card_opts["custom"]["font_color"] : "#ffffff").";
                                    font-size:".(!empty($card_opts["content_font"]["font_size"]) ? $card_opts["content_font"]["font_size"] : "16")."px;
                                    line-height:".(!empty($card_opts["content_font"]["font_size"]) ? ($card_opts["content_font"]["font_size"] + 4) : "16")."px;
                                    ".$card_opts_content_font."
                                    font-weight:".(!empty($card_opts["content_font"]["font_bold"]) ? "bold" : "normal").";
                                    font-style:".(!empty($card_opts["content_font"]["font_italic"]) ? "italic" : "normal").";
                                    text-decoration:".(!empty($card_opts["content_font"]["font_decoration"]) ? $card_opts["content_font"]["font_decoration"] : "none").";
                                }";

                                if($card_opts["arm_card"] == "membershipcard1") {
                                    $card_css .= ".membershipcard1.arm_card_".$arm_mcard_id." .arm_card_title{border-bottom:1px solid ".(!empty($card_opts["custom"]["bg_color"]) ? $card_opts["custom"]["bg_color"] : "#0073c6").";}";
                                }
                        if($view_type=="ARMPDF"){
		                    $card_css .= !empty($card_opts['custom_css']) ? $card_opts['custom_css']: '';

                        }else{
                                $card_css .= "</style>";
                                $card_css .= "<link rel='stylesheet' type='text/css' id='arm_membership_card_template_style_".$card_opts['arm_card']."-css' href='".$card_css_file."'/>";
                                
                                $card_css .= !empty($card_opts['custom_css']) ?  "<style>".$card_opts['custom_css']."</style>" : '';
                                echo $card_css;
				}
                            }
                            else {
                        if($view_type=="ARMPDF"){
                            $card_css_file= MEMBERSHIP_VIEWS_URL."/templates/membershipcard1.css";
                            $card_css_file_request = wp_remote_get($card_css_file);
                            $card_css_file_response = wp_remote_retrieve_body( $card_css_file_request );
                            $card_css .=str_replace('@charset "utf-8";','', $card_css_file_response);

                        }else{
                            echo '<link rel="stylesheet" type="text/css" id="arm_membership_card_template_style_'.$card_opts["arm_card"].'-css" href="'. MEMBERSHIP_VIEWS_URL.'/templates/membershipcard1.css" />';
                        }    
                                
                            
                            }
                            $plan_info = maybe_unserialize($user_info["arm_user_plan_" . $plan_id][0]);
                            //$iframe = $arm_card_ttl_font . "" . $arm_card_lbl_font . "" . $arm_card_content_font . "<link rel='stylesheet' type='text/css' id='arm_membership_card_template_style_".$card_opts['arm_card']."-css' href='".$card_css_file."'/>" . $card_css . $arm_members_directory->arm_get_membership_card_view($card_opts['arm_card'], $card_opts, $user_id, $user_info, $plan_info, '', false, 0);

                    $arm_card_html_view .=$arm_members_directory->arm_get_membership_card_view($card_opts['arm_card'], $card_opts, $user_id, $user_info, $plan_info, $company_logo, false, $iframe_id, $display_avatar, $card_background,'1');
                    if($view_type!="ARMPDF"){
                       echo $arm_card_html_view;   
                    }   
                        }
                    }
                }
            }
            }
        }
        ?>