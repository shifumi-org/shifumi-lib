<?php $hostname = $_SERVER["SERVER_NAME"];
global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
$is_debug_enable = 0;
?>
<style>
    .purchased_info{
        color:#7cba6c;
        font-weight:bold;
        font-size: 15px;
    }
    #license_success{
        color:#8ccf7a !important;
    }
    #license_error{
        color:red !important;
    }
    .arperrmessage{color:red;}
    #armresetlicenseform {
        border-radius:0px;
        text-align:center;
        width:700px;
        height:500px;
        left:35%;
        border:none;
        background:#ffffff !important;
        padding-top:15px;
    }
    .arfnewmodalclose
    {
        font-size: 15px;
        font-weight: bold;
        height: 19px;
        position: absolute;
        right: 3px;
        top:5px;
        width: 19px;
        cursor:pointer;
        color:#D1D6E5;
    }
    #licenseactivatedmessage {
    height:22px;
    color:#FFFFFF;
    font-size:17px;
    font-weight:bold;
    letter-spacing:0.5;
    margin-left:0px;
    display:block;
    border-radius:3px;
    -moz-border-radius:3px;
    -webkit-border-radius:3px;
    -o-border-radius:3px;

    padding:7px 5px 5px 0px;
    font-family:'open_sansregular', Arial, Helvetica, Verdana, sans-serif;
    background-color:#8ccf7a;
    margin-top:15px !important;
    margin-bottom:10px !important;
    text-align:center;
    }
    .red_remove_license_btn {
    -moz-box-sizing: content-box;
    background: #e95a5a; 
    border:none;
    box-shadow: 0 4px 0 0 #d23939;
    color: #FFFFFF !important;
    cursor: pointer;
    font-size: 16px !important;
    font-style: normal;
    font-weight: bold;
    height: 30px;
    min-width: 90px;
    width: auto;
    outline: none;
    padding: 0px 10px;
    text-shadow: none;
    text-transform: none;
    vertical-align:middle;
    text-align:center;
    margin-bottom:15px;
}
.red_remove_license_btn:hover {
    background: #d23939;
    box-shadow: 0 4px 0 0 #b83131;
}
    .newform_modal_title { font-size:25px; line-height:25px; margin-bottom: 10px; }
    .newmodal_field_title { font-size: 16px;
    line-height: 16px;
    margin-bottom: 10px; }
</style>
<div class="wrap arm_page arm_feature_settings_main_wrapper">
    <div class="content_wrapper arm_feature_settings_content" id="content_wrapper">
        <div class="page_title"><?php _e('Manage ARMember License', 'ARMember'); ?></div>
        <form method="post" action="#" id="arm_global_settings" class="arm_global_settings arm_admin_form" onsubmit="return false;">
            <div class="arm_feature_settings_container arm_feature_settings_wrapper" style="margin-top:30px;">
                        <?php if ($setact != 1 && $is_debug_enable == 0) { ?>
            <div class="page_sub_title"><?php _e('ARMember License','ARMember');?></div>
            <p>In order to receive all benefits of ARMember, you need to activate your copy of the plugin. By activating ARMember license you will unlock features like - Access of all ARMember addons, automatic updates and official support.</p>
            <table class="form-table">
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Customer Name', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="li_customer_name" id="li_customer_name" value="" autocomplete="off" />
                        <div class="arperrmessage" id="li_customer_name_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARMember'); ?></div>         
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Customer Email', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="li_customer_email" id="li_customer_email" value="" autocomplete="off" />
                        <div class="arperrmessage" id="li_customer_email_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARMember'); ?></div>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Purchase Code', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <input type="text" name="li_license_key" id="li_license_key" value="" autocomplete="off" />
                        <div class="arperrmessage" id="li_license_key_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARMember'); ?></div>        
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Domain Name', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle"><?php echo $hostname; ?></label>
                        <input type="hidden" name="li_domain_name" id="li_domain_name" value="<?php echo $hostname; ?>" autocomplete="off" />        
                    </td>
                </tr>

                <input type="hidden" name="receive_updates" id="receive_updates" value="0" autocomplete="off" />
                <tr class="form-field">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content">
                        <span id="license_link"><button type="button" id="verify-purchase-code" name="continue" style="width:150px; border:0px; color:#FFFFFF; height:40px; border-radius:3px;cursor:pointer;" class="greensavebtn"><?php _e('Activate', 'ARMember'); ?></button></span>
                        &nbsp;&nbsp;<a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_blank" title="Get Your Purchase Code">Where can I find my Purchase Code?</a> <br><br>
                        Don't have direct license yet? <a href="https://codecanyon.net/item/armember-complete-wordpress-membership-system/17785056?ref=utsavinfotech" target="_blank" title="Purchase ARMember License">Purchase ARMember license.</a>
                        <span id="license_loader" style="display:none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/loading_activation.gif'; ?>" height="15" /></span>           
                        <span id="license_error" style="display:none;">&nbsp;</span>
                        <span id="license_reset" style="display:none;">&nbsp;&nbsp;<a onclick="javascript:return false;" href="#">Click here to submit RESET request</a></span>
                        <span id="license_success" style="display:none;"><?php _e('License Activated Successfully.', 'ARMember'); ?></span>
                        <input type="hidden" name="ajaxurl" id="ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>"  />        
                    </td>
                </tr>
                
            </table>
            <?php } ?>
            
        <?php
        if ($setact == 1 && $is_debug_enable == 0) {
        $get_purchased_info = get_option('armSortInfo');
		$get_purchased_val = get_option('armSortOrder');
		
                            $sortorderval = base64_decode($get_purchased_info);
                            $ordering = array();

                            $pcodeinfo = "";
                            $pcodedate = "";
                            $pcodedateexp = "";
                            $pcodelastverified = "";
                            $pcodecustemail = "";

        if (is_array($ordering)) {
            $ordering = explode("^", $sortorderval);

            if (is_array($ordering)) {
            if (isset($ordering[0]) && $ordering[0] != "") {
                $pcodeinfo = $ordering[0];
            } else {
                $pcodeinfo = "";
            }
            if (isset($ordering[1]) && $ordering[1] != "") {
                $pcodedate = $ordering[1];
            } else {
                $pcodedate = "";
            }
            if (isset($ordering[2]) && $ordering[2] != "") {
                $pcodedateexp = $ordering[2];
            } else {
                $pcodedateexp = "";
            }
            if (isset($ordering[3]) && $ordering[3] != "") {
                $pcodelastverified = $ordering[3];
            } else {
                $pcodelastverified = "";
            }
            if (isset($ordering[4]) && $ordering[4] != "") {
                $pcodecustemail = $ordering[4];
            } else {
                $pcodecustemail = "";
            }
            }
        }
        ?>
                <div class="page_sub_title"><?php _e('Product License', 'ARMember'); ?></div>
            <table class="form-table">
            <tr class="form-field">
                <th class="arm-form-table-label">&nbsp;</th>
                <td class="arm-form-table-content">
                <div id="licenseactivatedmessage" style="width:300px; vertical-align:top;"><?php echo "Your license is currently Active."; ?></div>
                </td>
            </tr>
            <tr class="form-field">
                <th class="arm-form-table-label">&nbsp;</th>
                <td class="arm-form-table-content">
                <span id="license_link"><button type="button" id="remove-license-purchase-code" name="remove_license" style="width:170px; border:0px; color:#FFFFFF; height:40px; border-radius:6px;" onclick="deactivate_license();" class="red_remove_license_btn"><?php _e('Remove License', 'ARMember'); ?></button></span>

                                    <span id="deactivate_loader" style="display:none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/loading_activation.gif'; ?>" height="15" /></span>        
                                    <span id="deactivate_error" style="display:none;"><?php _e('Invalid Request', 'ARMember'); ?></span>
                                    <span id="deactivate_success" style="display:none;"><?php _e('License Deactivated Successfully.', 'ARMember'); ?></span>
                    </td>
                </tr>
                <?php if ($get_purchased_info != "") { ?>
                <tr class="form-field">
                    <th class="arm-form-table-label"><label class="lblsubtitle" style="font-weight:bold;font-size:16px;margin-left:0px;"><?php _e('Activation Information:', 'ARMember') ?>&nbsp;&nbsp;</label></th>
                    <td class="arm-form-table-content">&nbsp;
                                
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Purchase Code:', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle"><?php echo $pcodeinfo; ?></label>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Customer Email:', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle"><?php echo $pcodecustemail; ?></label>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Purchased On:', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle"><?php echo $pcodedate; ?></label>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php _e('Support Expires On:', 'ARMember'); ?></th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle"><?php echo $pcodedateexp; ?></label>

                        

                        <?php if($pcodedateexp != "")
                        { 
                        $exp_date=strtotime($pcodedateexp);
                        $today = strtotime("today"); 

                        if($exp_date < $today)
                        {
                          ?>
                          <br><br>

                         <p>It seems <span style="color:#FF0000;">Your ARMember support period is expired.</span> To continue receiving our prompt support you need to renew your support. Please  <a href='https://codecanyon.net/item/armember-complete-wordpress-membership-system/17785056?ref=utsavinfotech' target='_blank'>click here</a> to extend support. <br/>If you already bought support extension then kindly click button below to refresh support expiry date.</p>
                        <br>
                          <span id="license_link"><button type="button" id="renew-license-purchase-code" name="renew_license" style="width:160px; border:0px; color:#FFFFFF; cursor:pointer;" class="greensavebtn"><?php _e('Renew License', 'ARMember'); ?></button></span>

                                    <span id="renew_loader" style="display:none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/loading_activation.gif'; ?>" height="15" /></span>        
                                    <span id="renew_error" style="display:none;"><?php _e('Invalid Request', 'ARMember'); ?></span>
                                    <span id="renew_error_renew" style="display:none;color:#FF0000;"><?php echo "No new purchase of support extension found. Please <a href='https://codecanyon.net/item/armember-complete-wordpress-membership-system/17785056?ref=utsavinfotech' target='_blank'>click  here</a> to buy support extension."; ?></span>
                                    <span id="renew_success" style="display:none;"><?php _e('License Renewed Successfully.', 'ARMember'); ?></span>
                                    <input type="hidden" name="li_purchase_info" id="li_purchase_info" value="<?php echo $get_purchased_info; ?>" autocomplete="off" />
                                    <?php 
                        }
                        } ?>
						
						<?php 
						$is_badge_update_required = 0;
						$is_badge_update_required = get_option('arm_badgeupdaterequired');
						
						if($is_badge_update_required > 0)
						{ ?>
							<br><br>
							<p>It seems <span style="color:#FF0000;">your Server is changed.</span> To receive regular updates, please click button below to refresh your license.</p>
                        <br>
                          <span id="license_link"><button type="button" id="renew-user-badge" name="renew_user_badge" style="width:200px; border:0px; color:#FFFFFF;" class="greensavebtn"><?php _e('Refresh License', 'ARMember'); ?></button></span>

                                    <span id="renew_user_badge_loader" style="display:none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/loading_activation.gif'; ?>" height="15" /></span>        
                                    <span id="renew_user_badge_error" style="display:none;color:#FF0000;"><?php _e('Invalid Request', 'ARMember'); ?></span>
                                    <span id="renew_user_badge_error_renew" style="display:none;color:#FF0000;"><?php echo "It seems something went wrong. <a href='https://support.arpluginshop.com/' target='_blank'>click  here</a> to contact our support staff."; ?></span>
                                    <span id="renew_user_badge_success" style="display:none;color:#459765;"><?php _e('License Refreshed Successfully.', 'ARMember'); ?></span>
                                    <input type="hidden" name="li_purchase_val" id="li_purchase_val" value="<?php echo $get_purchased_val; ?>" autocomplete="off" />
							
						<?php }
						
						?>




                    </td>
                </tr>
                
                <?php } ?>
                
            </table>
            
            <?php } ?>

	       </div>
           </form>
        </div>
        <div class="armclear"></div>
</div>
<div id="armresetlicenseform" style="display:none;">
        
        <div class="arfnewmodalclose" onclick="javascript:return false;"><img src="<?php echo MEMBERSHIP_IMAGES_URL . '/close-button.png'; ?>" align="absmiddle" /></div>
        <div class="newform_modal_title_container">
            <div class="newform_modal_title">&nbsp;RESET LICENSE</div>
        </div>
       <div class="newmodal_field_title"><?php _e('Please submit this form if you have trouble activating license.', 'ARMember'); ?></div>
        <iframe style="display:block; height:100%; width:100%; margin-top:0px;" frameborder="0" name="test" id="armresetlicframe" src="" hspace="0"></iframe>
</div>
