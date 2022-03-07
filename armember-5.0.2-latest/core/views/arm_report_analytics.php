<?php
global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_class, $arm_subscription_plans, $arm_payment_gateways, $arm_transaction, $arm_pay_per_post_feature;

$all_members = $arm_members_class->arm_get_all_members_without_administrator(0,1);
$recent_members = $arm_members_class->arm_get_all_members_without_administrator(1,1,1);
$total_members = (!empty($all_members)) ? $all_members : 0;

$current_time = current_time('mysql');
$before_week = strtotime('-6 days', strtotime($current_time));
$before_week = date('Y-m-d 00:00:00', $before_week);
$current_date = date('Y-m-d 23:59:00', strtotime($current_time));

$recent_query_payment = "SELECT SUM(arm_amount) recent_amount FROM ".$ARMember->tbl_arm_payment_log." WHERE (arm_transaction_status='success' || arm_transaction_status='1') AND arm_payment_date >= '".$before_week."' AND arm_payment_date <= '".$current_date."' AND arm_is_post_payment = 0 AND arm_is_gift_payment = 0";

$recent_query_payment = "SELECT IFNULL((".$recent_query_payment."),0) AS recent_amount";

$total_query = "SELECT IFNULL((SELECT SUM(arm_amount) FROM ".$ARMember->tbl_arm_payment_log." WHERE (arm_transaction_status='success' || arm_transaction_status='1') AND arm_is_post_payment = 0 AND arm_is_gift_payment = 0),0) as total_amount";
$total_payment = $wpdb->get_row($total_query);
$total_payment = !empty($total_payment) ? sprintf("%.2f", $total_payment->total_amount) : sprintf("%.2f",0);

$recent_payment = $wpdb->get_row($recent_query_payment);

$recent_payment = !empty($recent_payment) ? sprintf("%.2f", $recent_payment->recent_amount) : sprintf("%.2f",0);


$currency = ""; $place = "";
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
if(!empty($all_global_settings)) {
    $general_settings = $all_global_settings['general_settings'];

    $global_currency = $arm_payment_gateways->arm_get_global_currency();
    $all_currency = $arm_payment_gateways->arm_get_all_currencies();
    $currency = $all_currency[strtoupper($global_currency)];

    //$currency = $general_settings['paymentcurrency'];
    $place = isset($general_settings['custom_currency']['place']) ? $general_settings['custom_currency']['place'] : 'prefix';
    $total_payment = ('sufix' == $place) ? $total_payment." ".$currency : $currency." ".$total_payment;
    $recent_payment = ('sufix' == $place) ? $recent_payment." ".$currency : $currency." ".$recent_payment;
}

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();

?>

<div class="wrap arm_page arm_report_analytics_main_wrapper">
    <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_report_analytics_content" id="content_wrapper">
		<div class="page_title">
			<?php _e('Reports','ARMember');?>
			<div class="armclear"></div>
		</div>

		<div class="armclear"></div>

		<div class="arm_report_member_summary">
            <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=member_report');?>" class="welcome-icon">
                <div class="arm_total_members arm_member_summary">
                    <div class="arm_member_summary_count"><?php echo $total_members;?></div>
                    <div class="arm_member_summary_label"><?php _e('Total Members', 'ARMember');?></div>
                </div>
            </a>
            <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=member_report');?>" class="welcome-icon">
                <div class="arm_active_members arm_member_summary">
                    <div class="arm_member_summary_count"><?php echo $recent_members;?></div>
                    <div class="arm_member_summary_label"><?php echo sprintf(esc_html__('Recent Members %slast week%s', 'ARMember'),'(',')');?></div>
                </div>
            </a>
            <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=payment_report');?>" class="welcome-icon">
                <div class="arm_inactive_members arm_member_summary">
                    <div class="arm_member_summary_count"><?php echo $total_payment;?></div>
                    <div class="arm_member_summary_label"><?php _e('Total Payments', 'ARMember');?></div>
                </div>
            </a>
            <a href="<?php echo admin_url('admin.php?page='.$arm_slugs->report_analytics.'&action=payment_report');?>" class="welcome-icon">
                <div class="arm_membership_plans arm_member_summary">
                    <div class="arm_member_summary_count"><?php echo $recent_payment;?></div>
                    <div class="arm_member_summary_label"><?php echo sprintf(esc_html__('Recent Payments %slast week%s', 'ARMember'),'(',')');?></div>
                </div>
            </a>
        </div>
        <div class="arm_half_section">
            <div class="page_title">
                <?php _e('Recent Members','ARMember');?>
            </div>
            <?php
                $recent_entries_display_numberof='5';
                $type = 'monthly';
                $graph_type = 'line';
                $is_export_to_csv = false;
                $is_pagination = false;

                if(empty($_REQUEST))
                {
                    $_REQUEST = array();
                }
                $_REQUEST['type'] ='monthly';
                $_REQUEST['graph_type'] ='line';
                $_REQUEST['plan_type'] = 'members';
                $_REQUEST['plan_id'] = '';
                $_REQUEST['year_filter'] = date('Y', strtotime($current_time));
                $_REQUEST['month_filter'] = date('m', strtotime($current_time));
                $_REQUEST['date_filter'] = '';
                $_REQUEST['gateway_filter'] =''; 
                $_REQUEST['current_page'] = 1;

                $arm_disable_next_prev_btn = 1;

                include(MEMBERSHIP_VIEWS_DIR . '/arm_graph_ajax.php');
            
            $arm_recent_members = array();
            $user_arg = array(
                'orderby' => 'ID',
                'order'   => 'DESC',
                'number'  => $recent_entries_display_numberof,
                'role__not_in' => 'administrator',
                'date_query'=>array('year'  =>$new_month_year,'month' =>$new_month)
            );
            $arm_recent_members = get_users($user_arg);
            
                ?>
                <div class="armclear"></div>
                <div class="arm_recent_members_container arm_report_analytics_inner_content">
                    <table cellpadding="0" cellspacing="0" border="0" id="arm_recent_members_table" class="display">
                        <thead>
                            <tr>
                                <th align="left" width="30%"><?php _e('User Name', 'ARMember');?></th>
                                <th align="left" width="40%"><?php _e('Email', 'ARMember');?></th>
                                <th align="left" width="30%"><?php _e('Membership', 'ARMember');?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if (!empty($arm_recent_members)) {
                            foreach($arm_recent_members as $recent_member):?>
                                <tr>
                                    <td width="30%"><a href="javascript:void(0)" class="arm_openpreview_popup" data-id="<?php echo $recent_member->ID;?>"><?php echo $recent_member->user_login;?></a></td>
                                    <td width="40%"><?php echo $recent_member->user_email;?></td>
                                    <td width="30%"><?php 
                                    $plan_ids = get_user_meta($recent_member->ID, 'arm_user_plan_ids', true);
                                    $paid_post_ids = get_user_meta($recent_member->ID, 'arm_user_post_ids', true);
                				    if(!empty($paid_post_ids))
                				    {
    	                            	foreach($plan_ids as $key => $val)
    	                                {
                    						if(!empty($paid_post_ids[$val]))
                    						{
                    						    unset($plan_ids[$key]);
                    						}
    	                                }
                				    }
                                    $arm_gift_ids = get_user_meta($recent_member->ID, 'arm_user_gift_ids', true);
                                    if(!empty($arm_gift_ids))
                                    {
                                        foreach($plan_ids as $arm_plan_key => $arm_plan_val)
                                        {
                                            if(in_array($arm_plan_val, $arm_gift_ids))
                                            {
                                                unset($plan_ids[$arm_plan_key]);
                                            }
                                        }
                                    }
                                    $plan_name = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plan_ids);
                                    echo (!empty($plan_name)) ? $plan_name : '<span class="arm_empty">--</span>';
                                    ?></td>
                                </tr>
                            <?php endforeach;
                        }else {
                            ?>
                            <tr><td colspan="3"><?php _e('There is no any recent members found.', 'ARMember');?></td></tr>
                            <?php
                        }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="armclear"></div>
        </div>
        <div class="arm_half_section">
            <div class="page_title">
                <?php _e('Recent Member Payments','ARMember');?>
            </div>

            <?php
                $type = 'monthly';
                $graph_type = 'line';
                $is_export_to_csv = false;
                $is_pagination = false;

                if(empty($_REQUEST))
                {
                    $_REQUEST = array();
                }
                $_REQUEST['type'] ='monthly';
                $_REQUEST['graph_type'] ='line';
                $_REQUEST['plan_type'] = 'payment_history';
                $_REQUEST['plan_id'] = '';
                $_REQUEST['year_filter'] = date('Y', strtotime($current_time));
                $_REQUEST['month_filter'] = date('m', strtotime($current_time));
                $_REQUEST['date_filter'] = '';
                $_REQUEST['gateway_filter'] =''; 
                $_REQUEST['current_page'] = 1;

                $arm_disable_next_prev_btn = 1;

                include(MEMBERSHIP_VIEWS_DIR . '/arm_graph_ajax.php');
                
                $payment_log = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_payment_log."` WHERE YEAR( arm_created_date ) = ".$new_month_year." AND MONTH( arm_created_date ) = ".$new_month."  ORDER BY `arm_created_date` DESC", ARRAY_A);
                
                $transactions = array();
                if (!empty($payment_log)) {
                    $i = 0;
                    foreach ($payment_log as $log) {
                        $date = strtotime($log['arm_created_date']);
                        if (isset($newLog[$date]) && !empty($newLog[$date])) {
                            $date += $i;
                            $transactions[$date] = $log;
                        } else {
                            $transactions[$date] = $log;
                        }
                        $i++;
                    }
                    krsort($transactions);
                    
                }
                
                    ?>
                    <div class="armclear"></div>
                    <div class="arm_recent_transactions_content arm_report_analytics_inner_content">
                        <table cellpadding="0" cellspacing="0" border="0" id="arm_recent_transactions_table" class="display">
                            <thead>
                                <tr>
                                    <th align="left" width="30%"><?php _e('User', 'ARMember');?></th>
                                    <th align="left" width="30%"><?php _e('Membership', 'ARMember');?></th>
                                    <th align="center" width="20%"><?php _e('Amount', 'ARMember');?></th>
                                    <th align="center" width="20%"><?php _e('Status', 'ARMember');?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            if (!empty($transactions))
                            {
                                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                                $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
                                $global_currency_sym = $all_currencies[strtoupper($global_currency)];

                                $j = 1;foreach($transactions as $recent_transaction): $recent_transaction = (object) $recent_transaction;?>
                                <?php 
                                if ($j > $recent_entries_display_numberof) {
                                    continue;
                                }
                                $j++;
                                ?>
                                <tr>
                                    <td width="30%"><a href="javascript:void(0)" class="arm_openpreview_popup" data-id="<?php echo $recent_transaction->arm_user_id;?>"><?php 
                                    $data = get_userdata($recent_transaction->arm_user_id);
                                    if (!empty($data)) {
                                        echo $data->user_login;
                                    }
                                    ?></a></td>
                                    <td width="30%"><?php 
                                    $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($recent_transaction->arm_plan_id);
                                    echo (!empty($plan_name)) ? $plan_name : '<span class="arm_empty">--</span>';
                                    ?></td>
                                    <td class="arm_center" width="20%"><?php 
                                    if (!empty($recent_transaction->arm_amount) && $recent_transaction->arm_amount > 0 ) {
                                        $t_currency = isset($recent_transaction->arm_currency) ? strtoupper($recent_transaction->arm_currency) : strtoupper($global_currency);
                                        $currency = (isset($all_currencies[$t_currency])) ? $all_currencies[$t_currency] : $global_currency_sym;
                                        echo $arm_payment_gateways->arm_prepare_amount($recent_transaction->arm_currency, $recent_transaction->arm_amount);
                                        if ($global_currency_sym == $currency && strtoupper($global_currency) != $t_currency) {
                                                echo ' ('.$t_currency.')';
                                        }
                                    } else {
                                        echo $arm_payment_gateways->arm_prepare_amount($recent_transaction->arm_currency, $recent_transaction->arm_amount);
                                    }
                                    $arm_transaction_status = $recent_transaction->arm_transaction_status;
                                    switch ($arm_transaction_status) {
                                        case '0':
                                            $arm_transaction_status = 'pending';
                                            break;
                                        case '1':
                                            $arm_transaction_status = 'success';
                                            break;
                                        case '2':
                                            $arm_transaction_status = 'canceled';
                                            break;
                                        default:
                                            $arm_transaction_status = $recent_transaction->arm_transaction_status;
                                            break;
                                    }
                                    ?></td>
                                    <td class="arm_center" width="20%"><?php echo $arm_transaction->arm_get_transaction_status_text($arm_transaction_status);?></td>
                                </tr>
                            <?php endforeach;
                            }else {
                            ?>
                                <tr><td colspan="4"><?php _e('There is no any recent transactions found.', 'ARMember');?></td></tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                        
                    </div>
                    <div class="armclear"></div>
                <?php wp_nonce_field( 'arm_wp_nonce' );?>              
        </div>    
    </div>
</div>
<div class="arm_member_view_detail_container"></div>
<?php
    echo $ARMember->arm_get_need_help_html_content('members-report-analysis');
?>