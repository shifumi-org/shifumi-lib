<?php
global $wpdb, $wp_roles, $ARMember, $arm_subscription_plans, $arm_global_settings, $arm_payment_gateways, $arm_member_forms;
/**
 * Process Submited Form.
 */
if (isset($_POST['action']) && in_array($_POST['action'], array('add', 'update'))) {
  
	do_action('arm_save_subscription_plans', $_POST);
}
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type, arm_subscription_plan_options');
$plan_id = 0;
$plan_name = $plan_description = '';
$plan_status = 1;
$user_roles = $arm_global_settings->arm_get_all_roles();
$plan_role = ($wp_roles->is_role('armember')) ? 'armember' : get_option('default_role');
$plan_data = $plan_options = array();
$subscription_type = 'free';
$expiry_type = "joined_date_expiry";

$plan_options = array(
    'access_type' => 'lifetime',
    'payment_type' => 'one_time',
    'recurring' => array('type' => 'D'),
    'trial' => array('type' => 'D'),
    'eopa' => array('type' => 'D'),
    'pricetext' => '',
    'expity_type' => 'joined_date_expiry',
    'expiry_date' => date('Y-m-d 23:59:59'),
    'upgrade_action' => 'immediate',
    'downgrade_action' => 'on_expire',
    'cancel_action' => 'block',
    'cancel_plan_action' => 'immediate',
    'eot' => 'block',
    'payment_failed_action' => 'block',
);
$form_mode = __("Add Membership Plan", 'ARMember');
$action = 'add';
$edit_mode = 0;
if (isset($_GET['action']) && $_GET['action'] == 'edit_plan' && isset($_GET['id']) && !empty($_GET['id'])) {
    $edit_mode = 1;
    $plan_id = intval($_GET['id']);
    $plan_data = $arm_subscription_plans->arm_get_subscription_plan($plan_id);
    $plan = new ARM_Plan($plan_id); 
    if ($plan_data !== FALSE && !empty($plan_data)) {
	$action = 'update';
	$form_mode = __("Edit Membership Plan", 'ARMember');
	$plan_name = esc_html(stripslashes($plan_data['arm_subscription_plan_name']));
	$plan_description = $plan_data['arm_subscription_plan_description'];
	$plan_status = $plan_data['arm_subscription_plan_status'];
	$plan_role = $plan_data['arm_subscription_plan_role'];
	$subscription_type = $plan_data['arm_subscription_plan_type'];

	if (!empty($plan_data['arm_subscription_plan_options'])) {
	    $plan_options = $plan_data['arm_subscription_plan_options'];
	    $plan_options["payment_type"] = !empty($plan_options["payment_type"]) ? $plan_options["payment_type"] : 'one_time';
	    $plan_options["recurring"]["type"] = !empty($plan_options["recurring"]["type"]) ? $plan_options["recurring"]["type"] : 'D';
	    $plan_options["trial"]["type"] = !empty($plan_options["trial"]["type"]) ? $plan_options["trial"]["type"] : 'D';
	}
    } else {
        $plan_id = 0;
    }
    $plan_options["access_type"] = !empty($plan_options["access_type"]) ? $plan_options["access_type"] : 'lifetime';
    $plan_options["payment_type"] = !empty($plan_options["payment_type"]) ? $plan_options["payment_type"] : 'one_time';
    $plan_options["recurring"]["type"] = !empty($plan_options["recurring"]["type"]) ? $plan_options["recurring"]["type"] : 'D';
    $plan_options["trial"]["type"] = !empty($plan_options["trial"]["type"]) ? $plan_options["trial"]["type"] : 'D';
    $plan_options["eopa"]["type"] = !empty($plan_options["eopa"]["type"]) ? $plan_options["eopa"]["type"] : 'D';
    $expiry_type = (isset($plan_options['expiry_type']) && !empty($plan_options["expiry_type"])) ? $plan_options["expiry_type"] : 'joined_date_expiry';
    $plan_options["expiry_date"] = !empty($plan_options["expiry_date"]) ? $plan_options["expiry_date"] : date('Y-m-d 23:59:59');
}


?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();

$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));


?>
<div class="wrap arm_page arm_subscription_plan_main_wrapper armPageContainer">
    <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
    <div class="content_wrapper arm_subscription_plan_content" id="content_wrapper">
        <div class="page_title"><?php echo $form_mode; ?></div>
        <div class="armclear"></div>

        <form  method="post" id="arm_add_edit_plan_form" class="arm_add_edit_plan_form arm_admin_form">
            <input type="hidden" name="id" id="arm_add_edit_plan_id" value="<?php echo $plan_id; ?>" />
            <input type="hidden" name="action" value="<?php echo $action ?>" />
            <div class="arm_admin_form_content">
                <table class="form-table">
                    <tr class="form-field form-required">
                        <th>
                            <label for="plan_name"><?php _e('Plan name', 'ARMember'); ?></label>
                        </th>
                        <td>
                            <input name="plan_name" id="plan_name" type="text" size="50" class="arm_subscription_plan_form_input" title="Plan name" value="<?php echo $plan_name; ?>" data-msg-required="<?php _e('Plan name can not be left blank.', 'ARMember'); ?>" required />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th>
                            <label for="plan_description"><?php _e('Plan Description', 'ARMember'); ?></label>
                        </th>
                        <td>
                            <textarea rows="8" cols="40" name="plan_description" id="plan_description"><?php echo stripslashes($plan_description); ?></textarea>
                        </td>
                    </tr>
                    <input type='hidden' name="plan_status" value='<?php echo $plan_status; ?>' />
                    <tr>
                        <th>
                            <label for="arm_plan_role"><?php _e('Member Role', 'ARMember'); ?></label>
                        </th>
                        <td>
                            <?php
                            $role_name = isset($user_roles[$plan_role]) ? $user_roles[$plan_role] : '';
                            ?>
                            <span class="arm_member_plan_role arm_member_plan_role_label role"><?php echo $role_name; ?></span>
                            <div class="arm_member_plan_role">
                                <a href="javascript:void(0)" class="arm_ms_action_btn" onclick="showPlanRoleChangeBoxCallback('member_role');"><?php _e('Change Role (Not recommended)', 'ARMember'); ?></a>
                                <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_member_role" id="arm_confirm_box_member_role">
                                    <div class="arm_confirm_box_body">
                                        <div class="arm_confirm_box_arrow"></div>
                                        <div class="arm_confirm_box_text arm_custom_currency_fields arm_text_align_left" >
                                            <input type='hidden' id="arm_plan_role" class="arm_plan_role_change_input" name="plan_role" data-old="<?php echo $plan_role; ?>" value="<?php echo $plan_role; ?>" data-type="<?php echo $role_name; ?>"/>

                                            <dl class="arm_selectbox arm_subscription_plan_form_dropdown arm_margin_right_0 arm_width_210" >
                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_plan_role">
                                                        <?php if (!empty($user_roles)): ?>
                                                            <?php foreach ($user_roles as $key => $val): ?>
                                                                <li data-label="<?php echo $val; ?>" data-value="<?php echo $key; ?>" data-type="<?php echo $val; ?>"><?php echo $val; ?></li>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class='arm_confirm_box_btn_container'>
                                            <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_member_plan_role_btn arm_margin_right_5 " ><?php _e('Ok', 'ARMember'); ?></button>
                                            <button type="button" class="arm_confirm_box_btn armcancel"onclick="hidePlanRoleChangeBoxCallback();"><?php _e('Cancel', 'ARMember'); ?></button>
                                        </div>
                                    </div>
                                </div>							
                        </td>
                    </tr>
                    <?php $total_plans = $arm_subscription_plans->arm_get_total_plan_counts(); ?>
                    <?php if (empty($action) || $action == 'add' && $total_plans > 0): ?>
                        <tr>
                            <th>
                                <label for="arm_inherit_rules"><?php _e('Inherit Access Rules Of Membership Plan', 'ARMember'); ?></label>
                            </th>
                            <td>
                                <input type="hidden" id="arm_inherit_rules" name="arm_inherit_plan_rules" value="" />
                                <dl class="arm_selectbox column_level_dd">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_inherit_rules">
                                            <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
                                            <?php
                                            if (!empty($all_plans)) {
                                                foreach ($all_plans as $p) {
                                                    $p_id = $p['arm_subscription_plan_id'];
                                                    if ($p_id != $plan_id && $p['arm_subscription_plan_status'] == '1') {
                                                        ?><li data-label="<?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); ?></li><?php
                                                        }
                                                    }
                                                }
                                                ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
                <div class="arm_solid_divider"></div>
                <div id="arm_plan_price_box_content" class="arm_plan_price_box">
                    <div class="page_sub_content">
                        <div class="page_sub_title"><?php _e('Plan Type & Price', 'ARMember'); ?></div>
                        <table class="form-table">
                            <tr class="form-field form-required">
                                <th><label><?php _e('Plan Type', 'ARMember'); ?></label></th>
                                <td>
                                    <div class="arm_plan_price_box">
                                        <span class="arm_subscription_types_container" id="arm_subscription_types_container">
                                            <input type="radio" class="arm_iradio" <?php checked($subscription_type, 'free'); ?> value="free" name="arm_subscription_plan_type" id="subscription_type_free" />
                                            <label for="subscription_type_free"><?php _e('Free Plan', 'ARMember'); ?></label>
                                            <input type="radio" class="arm_iradio" <?php checked($subscription_type, 'paid_infinite'); ?> value="paid_infinite" name="arm_subscription_plan_type" id="subscription_type_paid" />
                                            <label for="subscription_type_paid"><?php _e('Paid Plan (infinite)', 'ARMember'); ?></label>
                                            <input type="radio" class="arm_iradio" <?php checked($subscription_type, 'paid_finite'); ?> value="paid_finite" name="arm_subscription_plan_type" id="subscription_finite_type_paid" />
                                            <label for="subscription_finite_type_paid"><?php _e('Paid Plan (finite)', 'ARMember'); ?></label>
                                            <input type="radio" class="arm_iradio" <?php checked($subscription_type, 'recurring'); ?> value="recurring" name='arm_subscription_plan_type' id="subscription_recurring_type" />
                                            <label for="subscription_recurring_type"><?php _e('Subscription / Recurring Payment', 'ARMember'); ?></label>
                                            <input type="hidden" value="<?php echo $plan_options['access_type']; ?>" name="arm_subscription_plan_options[access_type]" id="arm_subscription_plan_access_type" />
                                            <input type="hidden" value="<?php echo $plan_options['payment_type']; ?>" name="arm_subscription_plan_options[payment_type]" id="arm_subscription_plan_payment_type" />
                                        </span>
                                        <div class="armclear"></div>
                                    </div>                                                            
                                </td>
                            </tr>
                            <tr class="form-field paid_subscription_options <?php echo (!in_array($subscription_type, array('free', 'recurring'))) ? '' : 'hidden_section' ?>">
                                <th><label><?php _e('Plan Amount', 'ARMember'); ?></label></th>   
                                <td>
                                    <?php
                                    $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                    $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
                                    $global_currency_sym = isset($all_currencies) ? $all_currencies[strtoupper($global_currency)] : '';
                                    $global_currency_sym_pos = $arm_payment_gateways->arm_currency_symbol_position($global_currency);
                                    $global_currency_sym_pos_pre = (!empty($global_currency_sym_pos) && $global_currency_sym_pos == 'prefix' ? '' : 'hidden_section');
                                    $global_currency_sym_pos_suf = (!empty($global_currency_sym_pos) && $global_currency_sym_pos == 'suffix' ? '' : 'hidden_section');
                                    ?>
                                    <span class="arm_prefix_currency_symbol <?php echo $global_currency_sym_pos_pre; ?>"><?php echo $global_currency_sym; ?></span>
                                    <input type="text" name="arm_subscription_plan_amount" id="arm_subscription_plan_amount" value="<?php echo (isset($plan_data['arm_subscription_plan_amount']) ? $plan_data['arm_subscription_plan_amount'] : '') ?>" data-msg-required="<?php _e('Amount should not be blank.', 'ARMember'); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)" class="arm_no_paste" />
                                    <span class="arm_suffix_currency_symbol <?php echo $global_currency_sym_pos_suf; ?>"><?php echo $global_currency_sym; ?></span>
                                </td>
                            </tr>
                            <?php 
                                $arm_add_plan_amount_html_for_paid_plan = '';
                                $plan_type = 'paid_plan';
                                $arm_payment_cycle_num = '';
                                echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_paid_plan, $plan_data, $plan_type, $arm_payment_cycle_num);
                            ?>
                          
                            <tr class="form-field paid_subscription_options_finite <?php echo ($subscription_type == 'paid_finite') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Plan Duration', 'ARMember'); ?></label></th>
                                <td>
                                    <div class="arm_paid_finite_expiry_based_on_joined_date" id="arm_paid_finite_expiry_based_on_joined_date">
                                        <div class="arm_expiry_joined_date_radio" id="arm_expiry_joined_date_radio">
                                            <input type="radio" class="arm_iradio" <?php checked($expiry_type, 'joined_date_expiry'); ?> value="joined_date_expiry" name="arm_subscription_plan_options[expiry_type]" id="arm_plan_finite_expiry_based_joined_date" />
                                            <label for="arm_plan_finite_expiry_based_joined_date"><?php _e('Based On Plan Assigned Date', 'ARMember'); ?></label>
                                            <i class="arm_helptip_icon armfa armfa-question-circle" title='<?php _e('User will be expired after certain amount of time based on plan assigned date. For example, after one year of joined, after 5 months of joined and like wise.', 'ARMember'); ?>'></i>
                                        </div>
                                        <div class="arm_expiry_joined_date_box" id="arm_expiry_joined_date_box">
                                            <div id="arm_eopa_D" class="arm_eopa_select" style="<?php echo (isset($plan_options["eopa"]["type"]) && ($plan_options["eopa"]["type"] != "D" || $plan_options["eopa"]["type"] == "")) ? "display:none;" : ''; ?>">
                                                <input type='hidden' id='arm_eopa_days' name="arm_subscription_plan_options[eopa][days]" value='<?php echo (!empty($plan_options["eopa"]["days"])) ? $plan_options["eopa"]["days"] : 1; ?>' />
                                                <dl class="arm_selectbox column_level_dd arm_width_120">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_eopa_days">
                                                            <?php for ($i = 1; $i <= 90; $i++) { ?>
                                                                <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                            <?php } ?>

                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div id="arm_eopa_W" class="arm_eopa_select" style="<?php echo (isset($plan_options["eopa"]["type"]) && $plan_options["eopa"]["type"] != "W") ? "display:none;" : ''; ?>">
                                                <input type='hidden' id='arm_eopa_weeks' name="arm_subscription_plan_options[eopa][weeks]" value="<?php echo!empty($plan_options["eopa"]["weeks"]) ? $plan_options["eopa"]["weeks"] : 1; ?>" />
                                                <dl class="arm_selectbox column_level_dd arm_width_120">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_eopa_weeks">
                                                            <?php for ($i = 1; $i <= 52; $i++) { ?>
                                                                <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                            <?php } ?>

                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div id="arm_eopa_M" class="arm_eopa_select" style="<?php echo (isset($plan_options["eopa"]["type"]) && $plan_options["eopa"]["type"] != "M") ? "display:none;" : ''; ?>">
                                                <input type='hidden' id='arm_eopa_months' name="arm_subscription_plan_options[eopa][months]" value="<?php echo!empty($plan_options["eopa"]["months"]) ? $plan_options["eopa"]["months"] : 1; ?>" />
                                                <dl class="arm_selectbox column_level_dd arm_width_120">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_eopa_months">
                                                            <?php for ($i = 1; $i <= 24; $i++) { ?>
                                                                <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                            <?php } ?>

                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div id="arm_eopa_Y" class="arm_eopa_select" style="<?php echo (isset($plan_options["eopa"]["type"]) && $plan_options["eopa"]["type"] != "Y") ? "display:none;" : ''; ?>">
                                                <input type='hidden' id='arm_eopa_years' name="arm_subscription_plan_options[eopa][years]" value="<?php echo!empty($plan_options["eopa"]["years"]) ? $plan_options["eopa"]["years"] : 1; ?>"/>
                                                <dl class="arm_selectbox column_level_dd arm_width_120">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul data-id="arm_eopa_years">
                                                            <?php for ($i = 1; $i <= 15; $i++) { ?>
                                                                <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                            <?php } ?>

                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div id="arm_eopa_type_main" class="arm_eopa_type_main" >
                                            <input type='hidden' id='arm_eopa_type' name="arm_subscription_plan_options[eopa][type]" value="<?php echo $plan_options["eopa"]['type']; ?>" onChange="arm_subscription_plan_duration_select();" />
                                            <dl class="arm_selectbox column_level_dd arm_width_120">
                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_eopa_type">
                                                        <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="D"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('Week(s)', 'ARMember'); ?>" data-value="W"><?php _e('Week(s)', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="M"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="Y"><?php _e('Year(s)', 'ARMember'); ?></li>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </div>
                                        </div>
                                    </div>
                                    
                                    <div class="arm_paid_finite_fixed_expiry_date" id="arm_paid_finite_fixed_expiry_date">
                                        <div class="arm_expiry_fix_date_radio" id="arm_expiry_fix_date_radio">
                                            <input type="radio" class="arm_iradio" <?php checked($expiry_type, 'fixed_date_expiry'); ?> value="fixed_date_expiry" name="arm_subscription_plan_options[expiry_type]" id="arm_plan_finite_expiry_fix_date" />
                                            <label for="arm_plan_finite_expiry_fix_date"><?php _e('Fix Expiration Date', 'ARMember'); ?></label>
                                            <i class="arm_helptip_icon armfa armfa-question-circle" title='<?php _e('User will be expired after the certain date selected here. No matter when he joined. For example if date is set 31 Dec, 2017 then all users having this plan will be expired on that date no matter when he registered.', 'ARMember'); ?>'></i>
                                        </div>
                                        <div class="arm_expiry_fix_date_box arm_position_relative" id="arm_expiry_fix_date_box" >
                                            <input type="hidden" name="wordpress_date_format" id="arm_finite_plan_expiry_format" value="<?php echo get_option( 'date_format' ); ?>">
                                            <input type="text" id="arm_finite_plan_expiry_date" value="<?php echo ( (isset($plan_options['expiry_date']) && !empty($plan_options['expiry_date'])) ? date($arm_common_date_format, strtotime($plan_options['expiry_date'])) : ''); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" name="arm_subscription_plan_options[expiry_date]" class="arm_finite_plan_expiry_date" data-editmode="<?php echo ($edit_mode) ? '1' : '0'; ?>" data-msg-required="<?php _e('Please select expiry date.', 'ARMember'); ?>"/>
                                        </div>
                                    </div>
                                    
                                </td>
                            </tr>
                           
                            
                            
                            <tr class="form-field paid_subscription_options_recurring_payment_cycles_main_box_tr <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Payment Cycles', 'ARMember'); ?></label></th>
                                <td>
                                    <div class="paid_subscription_options_recurring_payment_cycles_main_box">
                                    <ul class="arm_plan_payment_cycle_ul" >
                                    <?php $plan_options['payment_cycles'] = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                                    
                                    if($edit_mode == '1'){
                                        if(empty($plan_options['payment_cycles'])){
                                        
                                        $plan_amount = !empty($plan_data['arm_subscription_plan_amount']) ? $plan_data['arm_subscription_plan_amount'] : 0;
                                        $recurring_time = isset($plan_options['recurring']['time'])?$plan_options['recurring']['time']:'infinite';
                                        $recurring_type = isset($plan_options['recurring']['type'])?$plan_options['recurring']['type']:'D';
                                        switch($recurring_type){
                                            case 'D':
                                                $billing_cycle = isset($plan_options['recurring']['days'])?$plan_options['recurring']['days']:'1';
                                                break;
                                            case 'M':
                                                $billing_cycle = isset($plan_options['recurring']['months'])?$plan_options['recurring']['months']:'1';
                                                break;
                                            case 'Y':
                                                $billing_cycle = isset($plan_options['recurring']['years'])?$plan_options['recurring']['years']:'1';
                                                break;
                                            default:
                                                $billing_cycle = '1';
                                                break;
                                        }
                                        $plan_options['payment_cycles'] = array(array(
                                            'cycle_key'=>'arm0',
                                            'cycle_label' => $plan->plan_text(false, false),
                                            'cycle_amount' => $plan_amount,
                                            'billing_cycle' => $billing_cycle,
                                            'billing_type' => $recurring_type,
                                            'recurring_time' => $recurring_time,
                                            'payment_cycle_order' => 1,
                                        ));
               
                                    }
                                    }
                                        if(!empty($plan_options['payment_cycles']))
                                        {
                                            $total_inirecurring_cycle = count($plan_options['payment_cycles']); 
                                            $gi = 1;
                                             foreach($plan_options['payment_cycles'] as $arm_pc => $arm_value ){
                                                    ?>
                                                <li class="arm_plan_payment_cycle_li paid_subscription_options_recurring_payment_cycles_child_box" id="paid_subscription_options_recurring_payment_cycles_child_box<?php echo $arm_pc; ?>">
                                                
                                                    
                                                    <div class="arm_plan_payment_cycle_label">
                                                      <label class="arm_plan_payment_cycle_label_text"><?php _e('Label', 'ARMember'); ?></label>
                                                      <div class="arm_plan_payment_cycle_label_input">
                                                          <input type="hidden" name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][cycle_key]" value="<?php echo (!empty($arm_value['cycle_key'])) ? $arm_value['cycle_key'] : 'arm'.rand(); ?>"/>
                                                         <input type="text" name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][cycle_label]" value="<?php echo (!empty($arm_value['cycle_label'])) ? $arm_value['cycle_label'] : ''; ?>" class="paid_subscription_options_recurring_payment_cycle_label" data-msg-required="<?php _e('Label should not be blank.', 'ARMember'); ?>"/>
                                                      </div>
                                                    </div>


                                                    <div class="arm_plan_payment_cycle_amount">
                                                        <label class="arm_plan_payment_cycle_amount_text"><?php _e('Amount', 'ARMember'); ?></label>
                                                        <div class="arm_plan_payment_cycle_amount_input">
                                                        <span class="arm_prefix_currency_symbol <?php echo $global_currency_sym_pos_pre; ?>"><?php echo $global_currency_sym; ?></span>
                                                         <input type="text" name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][cycle_amount]" value="<?php echo (isset($arm_value['cycle_amount'])) ? $arm_value['cycle_amount'] : ''; ?>" class="paid_subscription_options_recurring_payment_cycle_amount" data-msg-required="<?php _e('Amount should not be blank.', 'ARMember'); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)" />
                                                         <span class="arm_suffix_currency_symbol <?php echo $global_currency_sym_pos_suf; ?>"><?php echo $global_currency_sym; ?></span>
                                                        </div>
                                                    </div>
                                                    <?php 
                                                        $arm_add_plan_amount_html_for_plan = '';
                                                        $plan_type = 'recurring';
                                                        echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_plan, $plan_data, $plan_type, $arm_pc);
                                                    ?>
                                                    <div class="arm_plan_payment_cycle_billing_cycle"><label class="arm_plan_payment_cycle_billing_text"><?php _e('Billing Cycle', 'ARMember'); ?></label>
                                                      <div class="arm_plan_payment_cycle_billing_input">
                                                          <input type='hidden' id='arm_ipc_billing<?php echo $arm_pc; ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][billing_cycle]" value='<?php echo (!empty($arm_value['billing_cycle'])) ? $arm_value['billing_cycle'] : 1; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_60 arm_min_width_50">
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_billing<?php echo $arm_pc; ?>">
                                                                             <?php for ($i = 1; $i <= 90; $i++) { ?>
                                                                                 <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                             <?php } ?>
                                                                         </ul>
                                                                     </dd>
                                                                 </dl>

                                                                <input type='hidden' id='arm_ipc_billing_type<?php echo $arm_pc; ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][billing_type]" value='<?php echo (!empty($arm_value['billing_type'])) ? $arm_value['billing_type'] : "D"; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_120 arm_min_width_120" >
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_billing_type<?php echo $arm_pc; ?>">

                                                                                 <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="D"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                                                 <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="M"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                                                 <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="Y"><?php _e('Year(s)', 'ARMember'); ?></li>

                                                                         </ul>
                                                                     </dd>
                                                                 </dl>
                                                      </div>
                                                    </div>


                                                    <div class="arm_plan_payment_cycle_recurring_time">
                                                          <label class="arm_plan_payment_cycle_recurring_text"><?php _e('Recurring Time', 'ARMember'); ?></label>
                                                          <input type='hidden' id='arm_ipc_recurring<?php echo $arm_pc; ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][recurring_time]" value='<?php echo (!empty($arm_value['recurring_time'])) ? $arm_value['recurring_time'] : 'infinite'; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_width_100 arm_min_width_100">
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_recurring<?php echo $arm_pc; ?>">
                                                                             <li data-label="<?php _e('Infinite', 'ARMember'); ?>" data-value="infinite"><?php _e('Infinite', 'ARMember'); ?></li>
                                                                             <?php for ($i = 2; $i <= 30; $i++) { ?>
                                                                                 <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                             <?php } ?>
                                                                         </ul>
                                                                     </dd>
                                                                 </dl>
                                                        </div>

                                                        <div class="arm_plan_payment_cycle_action_buttons">
                                                     <div class="arm_plan_cycle_plus_icon arm_helptip_icon tipso_style arm_add_plan_icon" title="<?php _e('Add Payment Cycle', 'ARMember'); ?>" id="arm_add_payment_cycle_recurring" data-field_index="<?php echo isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1; ?>" ></div>
                                                     <div class="arm_plan_cycle_minus_icon arm_helptip_icon tipso_style arm_add_plan_icon" title="<?php _e('Remove Payment Cycle', 'ARMember'); ?>" id="arm_remove_recurring_payment_cycle" data_index="<?php echo isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1; ?>"></div>
                                                        <div class="arm_plan_cycle_sortable_icon"></div>
                                                    
                                                        </div>
                                                    <input type="hidden" name="arm_subscription_plan_options[payment_cycles][<?php echo $arm_pc; ?>][payment_cycle_order]" value="<?php echo $gi; ?>" class="arm_module_payment_cycle_order">
                                            </li>
                                        <?php $gi++;
                                             }
                                         }
                                         else{
                                             ?>
                                            <li class="arm_plan_payment_cycle_li paid_subscription_options_recurring_payment_cycles_child_box" id="paid_subscription_options_recurring_payment_cycles_child_box0">
                                               
                                                    <div class="arm_plan_payment_cycle_label">
                                                      <label class="arm_plan_payment_cycle_label_text"><?php _e('Label', 'ARMember'); ?></label>
                                                      <div class="arm_plan_payment_cycle_label_input">
                                                          <input type="hidden" name="arm_subscription_plan_options[payment_cycles][0][cycle_key]" value="<?php echo 'arm0'; ?>"/>
                                                         <input type="text" name="arm_subscription_plan_options[payment_cycles][0][cycle_label]" value="" class="paid_subscription_options_recurring_payment_cycle_label" data-msg-required="<?php _e('Label should not be blank.', 'ARMember'); ?>"/>
                                                      </div>
                                                    </div>


                                                    <div class="arm_plan_payment_cycle_amount">
                                                        <label class="arm_plan_payment_cycle_amount_text"><?php _e('Amount', 'ARMember'); ?></label>
                                                        <div class="arm_plan_payment_cycle_amount_input">
                                                        <span class="arm_prefix_currency_symbol <?php echo $global_currency_sym_pos_pre; ?>"><?php echo $global_currency_sym; ?></span>
                                                         <input type="text" name="arm_subscription_plan_options[payment_cycles][0][cycle_amount]" value="" class="paid_subscription_options_recurring_payment_cycle_amount" data-msg-required="<?php _e('Amount should not be blank.', 'ARMember'); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)" />
                                                         <span class="arm_suffix_currency_symbol <?php echo $global_currency_sym_pos_suf; ?>"><?php echo $global_currency_sym; ?></span>
                                                        </div>
                                                    </div>
                                                    <?php 
                                                        $arm_add_plan_amount_html_for_plan = '';
                                                        $plan_type = 'recurring';
                                                        $arm_payment_cycle_num = 0;
                                                        echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_plan, $plan_data, $plan_type, $arm_payment_cycle_num);
                                                    ?>

                                                    <div class="arm_plan_payment_cycle_billing_cycle"><label class="arm_plan_payment_cycle_billing_text"><?php _e('Billing Cycle', 'ARMember'); ?></label>
                                                      <div class="arm_plan_payment_cycle_billing_input">
                                                          <input type='hidden' id='arm_ipc_billing0' name="arm_subscription_plan_options[payment_cycles][0][billing_cycle]" value='<?php echo (!empty($arm_value['billing_cycle'])) ? $arm_value['billing_cycle'] : 1; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_60 arm_min_width_50" >
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_billing0">
                                                                             <?php for ($i = 1; $i <= 90; $i++) { ?>
                                                                                 <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                             <?php } ?>
                                                                         </ul>
                                                                     </dd>
                                                                 </dl>

                                                                <input type='hidden' id='arm_ipc_billing_type0' name="arm_subscription_plan_options[payment_cycles][0][billing_type]" value='<?php echo (!empty($arm_value['billing_type'])) ? $arm_value['billing_type'] : "D"; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_120 arm_min_width_120" >
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_billing_type0">

                                                                                 <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="D"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                                                 <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="M"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                                                 <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="Y"><?php _e('Year(s)', 'ARMember'); ?></li>

                                                                         </ul>
                                                                     </dd>
                                                                 </dl>
                                                      </div>
                                                    </div>


                                                    <div class="arm_plan_payment_cycle_recurring_time">
                                                          <label class="arm_plan_payment_cycle_recurring_text"><?php _e('Recurring Time', 'ARMember'); ?></label>
                                                          <input type='hidden' id='arm_ipc_recurring0' name="arm_subscription_plan_options[payment_cycles][0][recurring_time]" value='<?php echo (!empty($arm_value['recurring_time'])) ? $arm_value['recurring_time'] : 'infinite'; ?>' />
                                                                 <dl class="arm_selectbox column_level_dd arm_margin_right_0 arm_width_120 arm_min_width_120" >
                                                                     <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                     <dd>
                                                                         <ul data-id="arm_ipc_recurring0">
                                                                             <li data-label="<?php _e('Infinite', 'ARMember'); ?>" data-value="infinite"><?php _e('Infinite', 'ARMember'); ?></li>
                                                                             <?php for ($i = 2; $i <= 30; $i++) { ?>
                                                                                 <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                             <?php } ?>
                                                                         </ul>
                                                                     </dd>
                                                                 </dl>
                                                        </div>

                                                        <div class="arm_plan_payment_cycle_action_buttons">
                                                     <div class="arm_plan_cycle_plus_icon" id="arm_add_payment_cycle_recurring" data-field_index="<?php echo isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1; ?>"></div>
                                                     <div class="arm_plan_cycle_minus_icon" id="arm_remove_recurring_payment_cycle" data_index="<?php echo isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1; ?>"></div>
                                                        <div class="arm_plan_cycle_sortable_icon"></div>
                                                    
                                                        </div>

 
                                                    <input type="hidden" name="arm_subscription_plan_options[payment_cycles][0][payment_cycle_order]" value="1" class="arm_module_payment_cycle_order">
                                            </li>
                                            <?php
                                         }
?>
                                    </ul>
                                    <div class="paid_subscription_options_recurring_payment_cycles_link">
                                            <input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles_order" value="2"/>
                                            <input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles" value="<?php echo isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1; ?>"/>
                                            <input type="hidden" name="arm_total_recurring_plan_cycles_counter" id="arm_total_recurring_plan_cycles_counter" value="<?php echo isset( $total_inirecurring_cycle ) ? $total_inirecurring_cycle : 1; ?>" />
                                            
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Trial Period', 'ARMember'); ?></label></th>
                                <td>
                                    <?php $is_trial_period = (isset($plan_options["trial"]['is_trial_period'])) ? $plan_options["trial"]['is_trial_period'] : 0; ?>
                                    <div class="armswitch arm_global_setting_switch">
                                        <input type="checkbox" id="trial_period" name="arm_subscription_plan_options[trial][is_trial_period]" value="1" class="armswitch_input trial_period_chk" onclick="arm_hide_show_trial_options(this);" <?php checked($is_trial_period, '1'); ?> />
                                        <label for="trial_period" class="armswitch_label arm_min_width_40" ></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="form-field trial_period_options <?php echo ($subscription_type == 'recurring' && $is_trial_period == '1') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Trial amount', 'ARMember'); ?></label></th>
                                <td>
                                    <span class="arm_prefix_currency_symbol <?php echo $global_currency_sym_pos_pre; ?>"><?php echo $global_currency_sym; ?></span>
                                    <input type="text" name="arm_subscription_plan_options[trial][amount]" id="trial_amount" onkeypress="javascript:return ArmNumberValidation(event, this);" value="<?php echo (!empty($plan_options["trial"]['amount'])) ? $plan_options["trial"]['amount'] : 0; ?>" class="arm_no_paste arm_width_235" >
                                    <span class="arm_suffix_currency_symbol <?php echo $global_currency_sym_pos_suf; ?>"><?php echo $global_currency_sym; ?></span>
                                </td>
                            </tr>
                            <tr class="form-field trial_period_options <?php echo ($subscription_type == 'recurring' && $is_trial_period == '1') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Trial Period Duration', 'ARMember'); ?></label></th>
                                <td>
                                    <div id="arm_plan_trial_recurring_days_main" class="arm_trial_select" style="<?php echo (isset($plan_options["trial"]["type"]) && ($plan_options["trial"]["type"] != "D" || $plan_options["trial"]["type"] == "")) ? "display:none;" : ''; ?>">
                                        <input type='hidden' id='arm_trial_days' name="arm_subscription_plan_options[trial][days]" value="<?php echo!empty($plan_options["trial"]["days"]) ? $plan_options["trial"]["days"] : 1; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_trial_days">
                                                    <?php for ($i = 1; $i <= 365; $i++) { ?>
                                                        <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div id="arm_plan_trial_recurring_months_main" class="arm_trial_select" style="<?php echo (isset($plan_options["trial"]["type"]) && $plan_options["trial"]["type"] != "M") ? "display:none;" : ''; ?>">
                                        <input type='hidden' id='arm_trial_months' name="arm_subscription_plan_options[trial][months]" value="<?php echo!empty($plan_options["trial"]["months"]) ? $plan_options["trial"]["months"] : 1; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_trial_months">
                                                    <?php for ($i = 1; $i <= 24; $i++) { ?>
                                                        <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                    <?php } ?>

                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div id="arm_plan_trial_recurring_years_main" class="arm_trial_select" style="<?php echo (isset($plan_options["trial"]["type"]) && $plan_options["trial"]["type"] != "Y") ? "display:none;" : ''; ?>">
                                        <input type='hidden' id='arm_trial_years' name="arm_subscription_plan_options[trial][years]" value="<?php echo!empty($plan_options["trial"]["years"]) ? $plan_options["trial"]["years"] : 1; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_trial_years">
                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                        <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                    <?php } ?>

                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div id="arm_plan_recurring_type_main" class="arm_plan_recurring_type_main" >
                                        <input type='hidden' id='arm_plan_trial_recurring_type' name="arm_subscription_plan_options[trial][type]" value="<?php echo $plan_options["trial"]['type']; ?>" onChange="arm_multiple_subscription_paypal_trial_recurring_type_select();" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_plan_trial_recurring_type">
                                                    <li data-label="<?php _e('Day(s)', 'ARMember'); ?>" data-value="D"><?php _e('Day(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Month(s)', 'ARMember'); ?>" data-value="M"><?php _e('Month(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php _e('Year(s)', 'ARMember'); ?>" data-value="Y"><?php _e('Year(s)', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </td>
                            </tr>
                            <tr class="form-field arm_subscription_payment_mode <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Billing Cycle Starts From', 'ARMember'); ?></label><br/><span class="arm_font_size_13">(<?php _e('Possible only in the case of semi-automatic / manual subscription','ARMember'); ?>)</span></th>
                                <td>
                                    <input type='hidden' id='arm_manual_subscription_start_from' name="arm_subscription_plan_options[recurring][manual_billing_start]" value="<?php echo !empty($plan_options['recurring']['manual_billing_start']) ? $plan_options['recurring']['manual_billing_start'] : 'transaction_day'; ?>" />
                                    <dl class="arm_selectbox column_level_dd arm_width_250">
                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_manual_subscription_start_from">
                                                <li data-label="<?php echo __('From Transaction Day', 'ARMember'); ?>" data-value="transaction_day"><?php echo __('From Transaction Day', 'ARMember'); ?></li>
                                                <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                    <?php
                                                    $dprefix = 'th';
                                                    if (in_array($i, array(1, 21, 31))) {
                                                        $dprefix = 'st';
                                                    }
                                                    if (in_array($i, array(2, 22))) {
                                                        $dprefix = 'nd';
                                                    }
                                                    if (in_array($i, array(3, 23))) {
                                                        $dprefix = 'rd';
                                                    }
                                                    ?>
                                                    <li data-label="<?php echo $i . $dprefix . ' ' . __('day of month', 'ARMember'); ?>" data-value="<?php echo $i; ?>"><?php echo $i . $dprefix . ' ' . __('day of month', 'ARMember'); ?></li>
                                                <?php } ?>

                                            </ul>
                                        </dd>
                                    </dl>
                                </td>
                            </tr>
                            
                            <?php
                                $freePlans = array();
                                $cancel_eot_options = '';
                                //$cancel_eot_options = '<li data-label="' . __('Remove this plan from user', 'ARMember') . '" data-value="block">' . __('Remove this plan from user', 'ARMember') . '</li>';
                                if (!empty($all_plans)) {
                                    foreach ($all_plans as $p) {
                                        $p_id = $p['arm_subscription_plan_id'];
                                        if ($p_id != $plan_id && $p['arm_subscription_plan_status'] == '1') {
                                            $freePlans[] = $p_id;
                                            $data_label = __('Give access to', 'ARMember') . ' ' . esc_html(stripslashes($p['arm_subscription_plan_name']));
                                            $cancel_eot_options .= '<li data-label="' . esc_attr($data_label) . '" data-value="' . $p_id . '">' . $data_label . '</li>';
                                        }
                                    }
                                }
                                ?>
                               <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Cancel Subscription Action', 'ARMember'); ?><br>(<?php _e('By User', 'ARMember'); ?>)</label></th>
                                <td class="arm_vertical_align_top">
                                    <?php
                                    $cancel_action = (!empty($plan_options["cancel_action"])) ? $plan_options["cancel_action"] : 'block';
                                    if ($cancel_action != 'block') {
                                        if (!in_array($cancel_action, $freePlans)) {
                                            $cancel_action = 'block';
                                        }
                                    }
                                    ?>
                                    <div>
                                        <input type='hidden' id='arm_plan_cancel_action' name="arm_subscription_plan_options[cancel_action]" value="<?php echo $cancel_action; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_370">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_plan_cancel_action"><?php echo '<li data-label="' . __('Remove this plan from user', 'ARMember') . '" data-value="block">' . __('Remove this plan from user', 'ARMember') . '</li>'.$cancel_eot_options; ?></ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <span class="arm_end_of_term_action_note"><?php _e('Action to be performed when user cancels membership from front end.', 'ARMember'); ?></span>
                                </td>
                            </tr>
                            
                            <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th></th>
                                <td>
                                    <?php $cancel_plan_action = (isset($plan_options["cancel_plan_action"])) ? $plan_options["cancel_plan_action"] : 'immediate'; ?>
                                    <span class="arm_margin_bottom_5"><?php _e("When user's subscription plan should be cancelled", 'ARMember'); ?></span>
                                    <div class="arm_clear"></div>
                                    <label class="arm_cancel_action_on_expire">
                                        <input type="radio" class="arm_iradio arm_cancel_action_radio" name="arm_subscription_plan_options[cancel_plan_action]" value="on_expire" <?php checked($cancel_plan_action, 'on_expire') ?>/>
                                        <span><?php _e('Do not cancel subscription until plan expired', 'ARMember'); ?></span>

                                    </label>
                                    <span class="arm-note-message --warning arm_badge_size_field_label arm_margin_top_10"><?php _e('In case of infinite subscription plan cancelled, then that plan will be cancelled after current cycle completes.', 'ARMember'); ?></span>
                                    <br/><br/>
                                    <label class="arm_cancel_action_immediate">
                                        <input type="radio" class="arm_iradio arm_cancel_action_radio" name="arm_subscription_plan_options[cancel_plan_action]" value="immediate" <?php checked($cancel_plan_action, 'immediate') ?>/>
                                        <span><?php _e('Cancel Subscription Immediately', 'ARMember'); ?></span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="form-field paid_subscription_upgrad_downgrade <?php echo ($subscription_type == 'paid_finite' || $subscription_type == 'recurring') ? '' : 'hidden_section'; ?>" >
                                
                                <th><label><?php _e('End Of Term Action', 'ARMember'); ?></label></th>
                                <td>
                                    <?php
                                    $eot_action = (!empty($plan_options["eot"])) ? $plan_options["eot"] : 'block';
                                    if ($eot_action != 'block') {
                                        if (!in_array($eot_action, $freePlans)) {
                                            $eot_action = 'block';
                                        }
                                    }
                                    ?>
                                    <input type='hidden' id='arm_end_of_term_action' name="arm_subscription_plan_options[eot]" value="<?php echo $eot_action; ?>" />
                                    <dl class="arm_selectbox column_level_dd arm_subscription_plan_options_eot">
                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_end_of_term_action"><?php echo '<li data-label="' . __('Remove this plan from user', 'ARMember') . '" data-value="block">' . __('Remove this plan from user', 'ARMember') . '</li>'.$cancel_eot_options; ?></ul>
                                        </dd>
                                    </dl>
                                    <span class="arm_end_of_term_action_note"><?php _e('Action to be performed after plan duration is finished.', 'ARMember'); ?></span>
                                    <?php
                                    global $arm_global_settings;

                                    $payment_gateway_notices = "<span class='arm_invalid arm_margin_bottom_25 arm_font_size_16' >" . __("Please consider following limitations of various payment gateways while configuring automatic subscription plan.", 'ARMember') . "</span>";
                                    $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . __('Paypal (if paypal payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . __('If your plan is Automatic Subscription and you have enabled Coupon Module then.', 'ARMember') . "<br/>" . __('- Due to paypal limitation it would be considered as a Trial Period of first installment.', 'ARMember') . "<br/>" . __('So, it would affect ( Your Recurring Time MINUS 1 ) number of occurance unless you have infinite duration.', 'ARMember') . " " . __('So, Please make sure you have set proper recurring time ( occurance ) in such case.', 'ARMember') . "</li><li>".__('Paypal supports maximum 90 days of trial duration for Auto Debit Subscription method.', 'ARMember')."</li></ol></span>";
                                    
                                    $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . __('Stripe (if Stripe payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . __('Stripe payment gateway supports only "Days" in Trial Duration Unit.', 'ARMember') . "</li></ol></span>";
                                    $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . __('Authorize.net (if Authorize.net payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . __('Authorize.net does not support billing cycle less than 7 Days. Also you can not set "Year" in billing cycle, as it is not supported in authorize.net.', 'ARMember') . "</li></ol></span>";
                                    $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . __('2Checkout (if 2Checkout payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . __('2Checkout does not support "Day" in billing cycle and free trial.', 'ARMember') . "<li>" . __('2checkout supports only first occurence of billing cycle as trial duration. So if you want to give trial,then set same parameters in "Billing cycle" and "Trial Period Duration", otherwise plan expiration willnot work properly with 2checkout.', 'ARMember') . "</li><li>" . __('In Case of Automatic subscription, If total payable amount will be 0 (Zero), then 2checkout gateway will not work.', 'ARMember') . "</li></ol></span>";


                                    $payment_gateway_notices = apply_filters('arm_set_gateway_warning_in_plan_with_recurring', $payment_gateway_notices);
                                    $payment_gateway_notices_popup_arg = array(
                                        'id' => 'arm_payment_gateway_notices',
                                        'class' => 'payment_gateway_notices',
                                        'title' => 'Important Notes',
                                        'content' => $payment_gateway_notices,
                                        'button_id' => 'payment_gateway_notices_ok_btn',
                                        'button_onclick' => "payment_gateway_notices('true');",
                                    );
                                    echo $arm_global_settings->arm_get_bpopup_html_payment($payment_gateway_notices_popup_arg);
                                    ?>
                                    <a class="arm_add_new_item_box arm_page_title_link arm_ref_info_links arm_pg_important_note" id="arm_payment_gateway_notices_link" href="#"><?php _e('Important note on payment integration with automatic subscription', 'ARMember'); ?></a>
                                </td>
                            </tr>
                         
                            <tr class="form-field paid_subscription_upgrad_downgrade <?php echo ($subscription_type == 'recurring' || $subscription_type=='paid_finite' ) ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Grace Period End of Term', 'ARMember'); ?></label></th>
                                <td>
                                    <?php
                                    $grace_period_eot = (!empty($plan_options["grace_period"]['end_of_term'])) ? $plan_options["grace_period"]['end_of_term'] : '0';
                                    ?>
                                    <div>
                                        <input type='hidden' id='arm_plan_grace_period_eot' name="arm_subscription_plan_options[grace_period][end_of_term]" value="<?php echo $grace_period_eot; ?>" />
                                        <dl class="arm_selectbox column_level_dd" data-id="arm_plan_grace_period_eot" <?php //echo $style_arm_plan_grace_period_eot; ?>>
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_text_align_left" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_plan_grace_period_eot">
                                                    <?php
                                                    for ($p = 0; $p <= 90; $p++) {
                                                        ?>
                                                        <li data-value="<?php echo $p; ?>" data-label="<?php echo $p; ?>"><?php echo $p; ?></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                        <span><?php _e('Days', 'ARMember');?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th></th>
                                <td></td>
                            </tr>
                            <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Payment Failed Action', 'ARMember'); ?></label></th>
                                <td>
                                    <?php
                                    $payment_failed_action = (!empty($plan_options["payment_failed_action"])) ? $plan_options["payment_failed_action"] : 'block';
                                    if ($payment_failed_action != 'block') {
                                        if (!in_array($payment_failed_action, $freePlans)) {
                                            $payment_failed_action = 'block';
                                        }
                                    }
                                    ?>
                                    <div>
                                        <input type='hidden' id='arm_plan_payment_failed_action' name="arm_subscription_plan_options[payment_failed_action]" value="<?php echo $payment_failed_action; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_370">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_plan_payment_failed_action"><?php echo '<li data-label="' . __('Block all access of this plan', 'ARMember') . '" data-value="block">' . __('Block all access of this plan', 'ARMember') . '</li>'.$cancel_eot_options; ?></ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <span class="arm_end_of_term_action_note"><?php _e('Action to be performed when payment has been failed due to any reason.', 'ARMember'); ?></span>
                                </td>
                            </tr>
                             <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th><label><?php _e('Grace Period Failed Payment', 'ARMember'); ?></label></th>
                                <td>
                                    <?php
                                    $grace_period_faild_payment = (isset($plan_options["grace_period"]['failed_payment'])) ? $plan_options["grace_period"]['failed_payment'] : '2';
                                    ?>
                                    <div>
                                        <input type='hidden' id='arm_plan_grace_period_failed_payment' name="arm_subscription_plan_options[grace_period][failed_payment]" value="<?php echo $grace_period_faild_payment; ?>" />
                                        <dl class="arm_selectbox column_level_dd arm_width_75 arm_min_width_75">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg arm_text_align_left"></i></dt>
                                            <dd>
                                                <ul data-id="arm_plan_grace_period_failed_payment">
                                                    <?php
                                                    for ($p = 0; $p <= 31; $p++) {
                                                        ?>
                                                        <li data-value="<?php echo $p; ?>" data-label="<?php echo $p; ?>"><?php echo $p; ?></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                        <span><?php _e('Days', 'ARMember'); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="form-field paid_subscription_options_recurring <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <th></th>
                                <td></td>
                            </tr>
                            <tr class="form-field paid_subscription_upgrad_downgrade <?php echo ($subscription_type == 'paid_finite' || $subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
                                <?php
                                $enable_up_down_action = (isset($plan_options["enable_upgrade_downgrade_action"])) ? $plan_options["enable_upgrade_downgrade_action"] : 0;
                                $upgrade_plans = (isset($plan_options["upgrade_plans"])) ? $plan_options["upgrade_plans"] : array();
                                $upgrade_action = (isset($plan_options["upgrade_action"])) ? $plan_options["upgrade_action"] : 'immediate';
                                $downgrade_plans = (isset($plan_options["downgrade_plans"])) ? $plan_options["downgrade_plans"] : array();
                                $downgrade_action = (isset($plan_options["downgrade_action"])) ? $plan_options["downgrade_action"] : 'immediate';
                                ?>
                                <th><label><?php _e('Enable Upgrade / Downgrade Action', 'ARMember'); ?></label></th>
                                <td>
                                    <div class="armclear"></div>
                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle" >
                                        <input type="checkbox" id="enable_upgrade_downgrade_action" <?php checked($enable_up_down_action, 1); ?> value="1" class="armswitch_input" name="arm_subscription_plan_options[enable_upgrade_downgrade_action]"/>
                                        <label for="enable_upgrade_downgrade_action" class="armswitch_label arm_min_width_40" ></label>
                                    </div>&nbsp;<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e('Upgrade / Downgrade action will be applied when users will change their plan from frontend. Select appropriate plan level which is higher/lower than current plan and action will be performed accordingly.', 'ARMember'); ?>"></i>
                                    <span style="float:left;width:100%;position:relative;top:5px;left:5px;"><?php _e('Action to be performed when user upgrade / downgrade membership from current plan.', 'ARMember'); ?></span>
                                    <div class="armclear"></div>
                                    <br/>
                                    <div class="arm_enable_up_down_action <?php echo ($enable_up_down_action != '1') ? 'hidden_section' : ''; ?>">
                                        <span><strong><?php _e('Upgrade Plan', 'ARMember'); ?></strong></span>
                                        <table width="100%">
                                            <tr>
                                                <td>
                                                    <span><?php _e('Select plan(s) which level is higher than current plan', 'ARMember'); ?></span><br/>
                                                    <select name="arm_subscription_plan_options[upgrade_plans][]" class="arm_chosen_selectbox arm_upgrade_plans_selectbox" multiple tabindex="-1" data-placeholder="<?php _e('Select higher plan(s)..', 'ARMember'); ?>">
                                                        <?php
                                                        $isURecSelected = false;
                                                        if (!empty($all_plans)) {
                                                            foreach ($all_plans as $plan) {
                                                                $isRecurring = '0';
                                                                $planOpts = $plan['arm_subscription_plan_options'];
                                                                if ($plan['arm_subscription_plan_type'] != 'free') {
                                                                    if ($planOpts['access_type'] == 'finite' && $planOpts['payment_type'] == 'subscription') {
                                                                        $isRecurring = '1';
                                                                        if (in_array($plan['arm_subscription_plan_id'], $upgrade_plans)) {
                                                                            $upgrade_action = 'immediate';
                                                                            $isURecSelected = true;
                                                                        }
                                                                    }
                                                                }
                                                                if ($plan_id != $plan['arm_subscription_plan_id']) {
                                                                    ?><option value="<?php echo $plan['arm_subscription_plan_id']; ?>" <?php echo (in_array($plan['arm_subscription_plan_id'], $upgrade_plans)) ? 'selected="selected"' : ''; ?> data-recurring="<?php echo $isRecurring; ?>"><?php echo esc_html(stripslashes($plan['arm_subscription_plan_name'])); ?></option><?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span><?php _e('What action should be performed while upgrading to other plan', 'ARMember'); ?></span><br/>
                                                    <label style="<?php echo ($isURecSelected) ? 'display:none;' : ''; ?>" class="arm_upgrade_action_on_expire">
                                                        <input type="radio" class="arm_iradio arm_upgrade_action_radio" name="arm_subscription_plan_options[upgrade_action]" value="on_expire" <?php checked($upgrade_action, 'on_expire') ?>/>
                                                        <span><?php _e('Upgrade to other plan after current plan expiration ( After End Of Term)', 'ARMember'); ?></span>
                                                    </label>
                                                    <label class="arm_upgrade_action_immediate">
                                                        <input type="radio" class="arm_iradio arm_upgrade_action_radio" name="arm_subscription_plan_options[upgrade_action]" value="immediate" <?php checked($upgrade_action, 'immediate') ?>/>
                                                        <span><?php _e('Immediately upgrade to other plan', 'ARMember'); ?></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="armclear"></div>
                                        <span><strong><?php _e('Downgrade Plan', 'ARMember'); ?></strong></span>
                                        <table width="100%">
                                            <tr>
                                                <td>
                                                    <span><?php _e('Select plan(s) which level is lower than current plan', 'ARMember'); ?></span><br/>
                                                    <select name="arm_subscription_plan_options[downgrade_plans][]" class="arm_chosen_selectbox arm_downgrade_plans_selectbox" multiple tabindex="-1" data-placeholder="<?php _e('Select lower plan(s)..', 'ARMember'); ?>">
                                                        <?php
                                                        $isDRecSelected = false;
                                                        if (!empty($all_plans)) {
                                                            foreach ($all_plans as $plan) {
                                                                $isRecurring = '0';
                                                                $planOpts = $plan['arm_subscription_plan_options'];
                                                                if ($plan['arm_subscription_plan_type'] != 'free') {
                                                                    if ($planOpts['access_type'] == 'finite' && $planOpts['payment_type'] == 'subscription') {
                                                                        $isRecurring = '1';
                                                                        if (in_array($plan['arm_subscription_plan_id'], $downgrade_plans)) {
                                                                            $downgrade_action = 'immediate';
                                                                            $isDRecSelected = true;
                                                                        }
                                                                    }
                                                                }
                                                                if ($plan_id != $plan['arm_subscription_plan_id']) {
                                                                    ?><option value="<?php echo $plan['arm_subscription_plan_id']; ?>" <?php echo (in_array($plan['arm_subscription_plan_id'], $downgrade_plans)) ? 'selected="selected"' : ''; ?> data-recurring="<?php echo $isRecurring; ?>"><?php echo esc_html(stripslashes($plan['arm_subscription_plan_name'])); ?></option><?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span><?php _e('What action should be performed while downgrading to other plan', 'ARMember'); ?></span><br/>
                                                    <label style="<?php echo ($isDRecSelected) ? 'display:none;' : ''; ?>" class="arm_downgrade_action_on_expire">
                                                        <input type="radio" class="arm_iradio arm_downgrade_action_radio" name="arm_subscription_plan_options[downgrade_action]" value="on_expire" <?php checked($downgrade_action, 'on_expire') ?>/>
                                                        <span><?php _e('Downgrade to other plan after current plan expiration ( After End Of Term)', 'ARMember'); ?></span>
                                                    </label>
                                                    <label class="arm_downgrade_action_immediate">
                                                        <input type="radio" class="arm_iradio arm_downgrade_action_radio" name="arm_subscription_plan_options[downgrade_action]" value="immediate" <?php checked($downgrade_action, 'immediate') ?>/>
                                                        <span><?php _e('Immediately downgrade to other plan', 'ARMember'); ?></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                           
                            

                        </table>
                    </div>
                </div>
                <?php
                $totalPlanMembers = $arm_subscription_plans->arm_get_total_members_in_plan($plan_id);
                if (isset($_GET['action']) && $_GET['action'] == 'edit_plan' && $totalPlanMembers > 0) {
                    ?><div class="arm_submit_btn_container arm_margin_0" style="padding:20px 0px 0px 275px;">
                        <span class="arm_current_plan_warning error arm_padding_left_0" ><?php _e('One or more members has already subscribed to this plan. Any changes made to plan type & price will be applied (affect) to new users but not existing ones.', 'ARMember'); ?></span>
                    </div><?php
                }
                do_action('arm_display_field_add_membership_plan', $plan_options);
                ?>
                <!--<div class="arm_divider"></div>-->
                <div class="arm_submit_btn_container">
                    <button class="arm_save_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>
                    <a class="arm_cancel_btn" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->manage_plans); ?>"><?php _e('Close', 'ARMember'); ?></a>
                </div>
                <div class="armclear"></div>
            </div>
            <?php wp_nonce_field( 'arm_wp_nonce' ); ?>
        </form>
        <div class="armclear"></div>
    </div>
</div>
    <script>
        var CYCLEAMOUNT = "<?php _e('Amount', 'ARMember'); ?>";
        var BILLINGCYCLE = "<?php _e('Billing Cycle', 'ARMember'); ?>";
        var ARMCYCLELABEL = "<?php _e('Label', 'ARMember'); ?>";
        var RECURRINGTIME = "<?php _e('Recurring Time', 'ARMember'); ?>";
        var AMOUNTERROR = "<?php _e('Amount should not be blank.', 'ARMember'); ?>";
        var LABELERROR = "<?php _e('Label should not be blank.', 'ARMember'); ?>";
        var DAY = "<?php _e('Day(s)', 'ARMember'); ?>";
        var MONTH = "<?php _e('Month(s)', 'ARMember'); ?>";
        var YEAR = "<?php _e('Year(s)', 'ARMember'); ?>";
        var INFINITE = "<?php _e('Infinite', 'ARMember'); ?>";
        var EMESSAGE = "<?php _e('You cannot remove all payment cycles.', 'ARMember'); ?>";
        var ARMREMOVECYCLE = "<?php _e('Remove Cycle', 'ARMember'); ?>";
        var CURRENCYPREF = "<?php echo $global_currency_sym_pos_pre; ?>";
        var CURRENCYSUF = "<?php echo $global_currency_sym_pos_suf; ?>";
        var CURRENCYSYM = "<?php echo $global_currency_sym; ?>";
        var ARM_RR_CLOSE_IMG = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon.png';
        var ARM_RR_CLOSE_IMG_HOVER = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_close_icon_hover.png';
        var ADDCYCLE = "<?php _e('Add Payment Cycle', 'ARMember'); ?>";
        var REMOVECYCLE = "<?php _e('Remove Payment Cycle', 'ARMember'); ?>";
        </script>

<?php
    echo $ARMember->arm_get_need_help_html_content('membership-plan-add');
?>