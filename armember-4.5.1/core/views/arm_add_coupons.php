<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_payment_gateways, $arm_subscription_plans;
$globals_settings = $arm_global_settings->arm_get_all_global_settings();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$period_type = 'daterange';

if (isset($_POST['action']) && in_array($_POST['action'], array('add_coupon', 'edit_coupon')))
{
	do_action('arm_admin_save_coupon_details', $_POST);
}
$action = 'add_coupon';


if (isset($_REQUEST['action']) && isset($_REQUEST['coupon_eid']) && $_REQUEST['coupon_eid'] != '') {
    $form_mode = __('Edit Coupon', 'ARMember');
} else {
    $form_mode = __('Add Coupon', 'ARMember');
}
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_add_edit_coupon_main_wrapper">
    <div class="content_wrapper arm_email_settings_content" id="content_wrapper">
        <div class="page_title"><?php echo $form_mode; ?></div>
         <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
        <div class="armclear"></div>
        <?php
        $c_discount='';
        $c_sdate='';
        $c_edate='';
        $c_allowed_uses='';
        $c_label='';
        $c_data='';
        if (isset($_REQUEST['action']) && isset($_REQUEST['coupon_eid']) && $_REQUEST['coupon_eid'] != '') {
            $cid = $_REQUEST['coupon_eid'];
            $result = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_coupons . "` WHERE `arm_coupon_id`='" . $cid . "'");
            $c_data=$result;
            $c_id = $result->arm_coupon_id;
            $c_code = $result->arm_coupon_code;
            $c_discount = $result->arm_coupon_discount;
            $c_type = $result->arm_coupon_discount_type;
            $c_coupon_on_each_subscriptions = isset($result->arm_coupon_on_each_subscriptions) ? $result->arm_coupon_on_each_subscriptions : 0;
            $c_sdate = $result->arm_coupon_start_date;
            $c_edate = $result->arm_coupon_expire_date;
            $c_subs = $result->arm_coupon_subscription;
            $c_subs = @explode(',', $c_subs);
            $c_allowed_uses = $result->arm_coupon_allowed_uses;
            $c_label= $result->arm_coupon_label;
            $coupon_status = $result->arm_coupon_status;
            $c_allow_trial = $result->arm_coupon_allow_trial;
            $form_id = 'arm_edit_coupon_wrapper_frm';
            $readonly = 'readonly = readonly';
            $period_type = (!empty($result->arm_coupon_period_type)) ? $result->arm_coupon_period_type : 'daterange';
            $edit_mode = true;
            $today = date('Y-m-d H:i:s');
            $action = 'edit_coupon';
            if ($today > $c_sdate) {
                $sdate_status = $readonly;
            } else {
                $sdate_status = '';
            }
        } else {
            $form_id = 'arm_add_coupon_wrapper_frm';
            $c_id = 0;
            $coupon_status = 1;
            $c_allow_trial = 0;
            $c_coupon_on_each_subscriptions = 0;
            $c_type = 'fixed';
            $edit_mode = false;
            $sdate_status = '';
            $c_subs = array();
        }
        ?>
        <form  method="post" action="#" id="<?php echo $form_id; ?>" class="arm_add_edit_coupon_wrapper_frm arm_admin_form"> 
            <input type="hidden" name="arm_edit_coupon_id" value="<?php echo(!empty($c_id) ? $c_id : '') ?>" />
            <input type="hidden" name="action" value="<?php echo $action ?>">
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
            <div class="arm_admin_form_content">
                <table class="form-table">
                    <tr class="form-field form-required">
                        <th><label><?php _e('Coupon Code', 'ARMember'); ?></label></th>
                        <td>
                            <input type="text" <?php echo $sdate_status; ?> id="arm_coupon_code" name="arm_coupon_code" class="arm_coupon_input_fields" value="<?php echo (!empty($c_code) ? esc_html(stripslashes($c_code)) : ''); ?>" data-msg-required="<?php _e('Generate Coupon Code.', 'ARMember'); ?>" required />
                            <?php if ($sdate_status == '') : ?>
                                <button id="arm_generate_coupon_code" class="arm_button armemailaddbtn" onclick="generate_code()" type="button"><?php _e('Generate', 'ARMember'); ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_generate_coupon_img" style="position:relative;top:5px;display:none;<?php echo (is_rtl()) ? 'right:5px;' : 'left:5px;'; ?>" width="20" height="20" />
                            <?php endif; ?>
                            <?php if ($edit_mode == TRUE && $sdate_status != '') { ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Coupon code can't be changed, Because its usage has been started.", 'ARMember'); ?>"></i>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php /*<tr class="form-field form-required">
                        <th><label><?php _e('Discount', 'ARMember'); ?></label></th>
                        <td>
                            <input type="text" id="arm_coupon_discount" value="<?php echo(isset($c_discount) ? $c_discount : ''); ?>" onkeypress="return ArmNumberValidation(event, this)" name="arm_coupon_discount" class="arm_coupon_input_fields arm_coupon_discount_input arm_no_paste" data-msg-required="<?php _e('Please add discount amount.', 'ARMember'); ?>" required style="width: 230px;<?php echo (is_rtl()) ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?>"/>
                            <input type='hidden' id='arm_discount_type' name="arm_discount_type" value='<?php echo $c_type; ?>'/>
                            <dl class="arm_selectbox column_level_dd">
                                <dt style="width: 230px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_discount_type">
                                        <li data-label="<?php _e('Fixed', 'ARMember'); ?> (<?php echo $global_currency; ?>)" data-value="fixed"><?php _e('Fixed', 'ARMember'); ?> ( <?php echo $global_currency; ?> )</li>
                                        <li data-label="<?php _e('Percentage', 'ARMember'); ?> (%)" data-value="percentage"><?php _e('Percentage', 'ARMember'); ?> (%)</li>
                                    </ul>
                                </dd>
                            </dl>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th><label><?php _e('Period Type', 'ARMember'); ?></label></th>
                        <td>
                            <div class="arm_coupon_period_box">
                                <span class="arm_period_types_container" id="arm_period_types_container">
                                    <input type="radio" class="arm_iradio" <?php checked($period_type, 'daterange'); ?> value="daterange" name="arm_coupon_period_type" id="period_type_daterange" >
                                    <label for="period_type_daterange"><?php _e('Date Range', 'ARMember'); ?></label>
                                    <input type="radio" class="arm_iradio" <?php checked($period_type, 'unlimited'); ?> value="unlimited" name="arm_coupon_period_type" id="period_type_unlimited" >
                                    <label for="period_type_unlimited"><?php _e('Unlimited', 'ARMember'); ?></label>
                                </span>
                                <div class="armclear"></div>
                            </div> 
                        </td>
                    </tr>
                    <tr class="form-field form-required coupon_period_options <?php echo ($period_type == 'daterange') ? '' : 'hidden_section' ?>">
                        <th><label><?php _e('Start Date', 'ARMember'); ?></label></th>
                        <td style="position: relative;">
                            <input type="text" id="arm_coupon_start_date" <?php echo $sdate_status; ?> value="<?php echo(!empty($c_sdate) ? date('m/d/Y', strtotime($c_sdate)) : ''); ?>" name="arm_coupon_start_date" class="arm_coupon_input_fields <?php echo (!empty($sdate_status) ? '' : 'arm_datepicker_coupon' ); ?>" data-msg-required="<?php _e('Please select start date.', 'ARMember'); ?>" required />
                            <?php if ($edit_mode == TRUE && $sdate_status != '') { ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Date Can't Be Changed, Because coupon usage has been started.", 'ARMember'); ?>"></i>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="form-field form-required coupon_period_options <?php echo ($period_type == 'daterange') ? '' : 'hidden_section' ?>">
                        <th><label><?php _e('Expire Date', 'ARMember'); ?></label></th>
                        <td style="position: relative;">
                            <input type="text" id="arm_coupon_expire_date" value="<?php echo(!empty($c_edate) ? date('m/d/Y', strtotime($c_edate)) : ''); ?>" name="arm_coupon_expire_date" class="arm_coupon_input_fields arm_datepicker_coupon" data-editmode="<?php echo ($edit_mode) ? '1' : '0'; ?>" data-msg-required="<?php _e('Please select expire date.', 'ARMember'); ?>" data-armgreaterthan-msg="<?php _e('Expire date can not be earlier than start date', 'ARMember'); ?>" required />
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th><label><?php _e('Membership Plan', 'ARMember'); ?></label></th>
                        <td>
                            <select name="arm_subscription_coupons[]" id="arm_subscription_coupons" class="arm_chosen_selectbox arm_coupons_select_box_sub arm_coupon_input_fields" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember'); ?>" multiple>
                                <?php
                                $subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type');
                                if (!empty($subs_data)) {
                                    $c_subs = (!empty($c_subs)) ? $c_subs : array();
                                    foreach ($subs_data as $sd) {
                                            echo '<option value="' . $sd['arm_subscription_plan_id'] . '" ' . (in_array($sd['arm_subscription_plan_id'], $c_subs) ? 'selected="selected"' : "" ) . '>' . esc_html(stripslashes($sd['arm_subscription_plan_name'])) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Leave blank for all plans.", 'ARMember'); ?>"></i>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th><label><?php _e('Allow this coupon with trial period amount?', 'ARMember'); ?></label></th>
                        <td valign="middle">
                            <input type="checkbox" class="arm_coupon_input_fields arm_icheckbox" value="1" name="arm_coupon_allow_trial" <?php checked($c_allow_trial, 1) ?>>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th><label><?php _e('No. of time uses allowed', 'ARMember'); ?></label></th>
                        <td valign="middle">
                            <input type="text" onkeypress="javascript:return isNumber(event)" id="arm_allowed_uses" value="<?php echo(!empty($c_allowed_uses) ? $c_allowed_uses : 0); ?>" name="arm_allowed_uses" class="arm_coupon_input_fields"/>
                            <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Leave blank or '0' for unlimited uses.", 'ARMember'); ?>"></i>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th><label><?php _e('Coupon Label', 'ARMember'); ?></label></th>
                        <td valign="middle">
                            <input type="text"  id="arm_coupon_label" value="<?php echo (isset($c_label) ? stripslashes_deep($c_label) : ''); ?>" name="arm_coupon_label" class="arm_coupon_input_fields"/>
                           
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th><label><?php _e('For Recurring Plan Apply to Entire Duration', 'ARMember'); ?></label></th>
                        <td valign="middle">
                            <input type="checkbox" class="arm_coupon_input_fields arm_icheckbox" value="1" name="arm_coupon_on_each_subscriptions" <?php checked($c_coupon_on_each_subscriptions, 1) ?>>
                        </td>
                    </tr>
                    <input type="hidden" name="arm_coupon_status" value="<?php echo $coupon_status; ?>"/>
                    */?>
                    <?php 
                    echo $arm_manage_coupons->arm_coupon_form_html($c_discount,$c_type,$period_type,$sdate_status,$edit_mode,$c_sdate,$c_edate,$c_allow_trial,$c_allowed_uses,$c_label,$c_coupon_on_each_subscriptions,$coupon_status,$c_subs,$c_data);

                    ?>
                    
                </table>
                <div class="armclear"></div>
                <!--<div class="arm_divider"></div>-->
                <div class="arm_submit_btn_container">
                    <?php if (!$edit_mode) { ?>
                        <input type="hidden" name="op_type" id="form_type" value="add" />
                    <?php } else { ?>
                        <input type="hidden" name="op_type" id="form_type" value="edit" />
                    <?php } ?>
                    <button id="arm_coupon_operation" class="arm_save_btn" data-id="<?php echo $c_id; ?>" data-type="edit" type="submit"><?php _e('Save', 'ARMember') ?></button>
                    <a class="arm_cancel_btn" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->coupon_management); ?>"><?php _e('Close', 'ARMember') ?></a>
                </div>
                <div class="armclear"></div>
            </div>
        </form>
        <div class="armclear"></div>
    </div>
</div>