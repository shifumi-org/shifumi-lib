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
    <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
    <div class="content_wrapper arm_email_settings_content" id="content_wrapper">
        <div class="page_title"><?php echo $form_mode; ?></div>
        <div class="armclear"></div>
        <?php
        $c_discount='';
        $c_sdate='';
        $c_edate='';
        $c_allowed_uses='';
        $c_label='';
        $c_data='';
        $arm_coupon_type = 1;
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
            $c_paid_posts = !empty($result->arm_coupon_paid_posts) ? $result->arm_coupon_paid_posts : array();
            $c_paid_posts = !empty($c_paid_posts) ? @explode(',', $c_paid_posts) : array();
            $c_allowed_uses = $result->arm_coupon_allowed_uses;
            $c_label= $result->arm_coupon_label;
            $coupon_status = $result->arm_coupon_status;
            $c_allow_trial = $result->arm_coupon_allow_trial;
            $form_id = 'arm_edit_coupon_wrapper_frm';
            $readonly = 'readonly = readonly';
            $period_type = (!empty($result->arm_coupon_period_type)) ? $result->arm_coupon_period_type : 'daterange';
            $arm_coupon_type = isset($result->arm_coupon_type) ? $result->arm_coupon_type : 1;
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
            $c_paid_posts = array();
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
                        <td >
                            <input type="text" <?php echo $sdate_status; ?> id="arm_coupon_code" name="arm_coupon_code" class="arm_coupon_input_fields <?php echo (($edit_mode != true) ? 'arm_coupon_code_input_field' : '') ?>" value="<?php echo (!empty($c_code) ? esc_html(stripslashes($c_code)) : ''); ?>" data-msg-required="<?php _e('Generate Coupon Code.', 'ARMember'); ?>" required />
                            <?php if ($sdate_status == '') : ?>
                                <button id="arm_generate_coupon_code" class="arm_button armemailaddbtn" onclick="generate_code()" type="button"><?php _e('Generate', 'ARMember'); ?></button>&nbsp;<img src="<?php echo MEMBERSHIP_IMAGES_URL . '/arm_loader.gif' ?>" id="arm_generate_coupon_img" class="arm_submit_btn_loader" style="top:5px;display:none;<?php echo (is_rtl()) ? 'right:5px;' : 'left:5px;'; ?>" width="20" height="20" />
                            <?php endif; ?>
                            <?php if ($edit_mode == TRUE && $sdate_status != '') { ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e("Coupon code can't be changed, Because its usage has been started.", 'ARMember'); ?>"></i>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php 
                        echo $arm_manage_coupons->arm_coupon_form_html($c_discount,$c_type,$period_type,$sdate_status,$edit_mode,$c_sdate,$c_edate,$c_allow_trial,$c_allowed_uses,$c_label,$c_coupon_on_each_subscriptions,$coupon_status,$c_subs,$c_data, $arm_coupon_type,$c_paid_posts);
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
<?php
    echo $ARMember->arm_get_need_help_html_content('member-coupon-add');
?>