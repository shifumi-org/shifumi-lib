<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $is_multiple_membership_feature, $arm_datepicker_loaded, $arm_pay_per_post_feature;
$arm_datepicker_loaded = 1;


if($arm_pay_per_post_feature->isPayPerPostFeature && !empty($_GET['action']) && ($_GET['action'] == "pay_per_post_report"))
{
    $all_active_plans = $arm_subscription_plans->arm_get_paid_post_data();
}
else
{
    $all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
}

$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();


$year = date("Y");
$month = date("m");
$month_label = "";
$to_year = $year - 12;
$yearLists = "";
$monthLists = "";
$gateways_list = "";

for ($i = 0; $i < 12; ++$i) {
    $month_num = $i+1;
    $month_name = date("F", strtotime("January +$i months"));
    $monthLists .= '<li data-label="' . $month_name . '" data-value="'.$month_num.'">' . $month_name . '</li>';
    if($month == $month_num) {
        $month_label = $month_name;
    }
}

for($i=$year; $i>$year - 12; $i--) {
    $yearLists .= '<li data-label="' . $i . '" data-value="'.$i.'">' . $i . '</li>';
}


$gateways_list = '<li data-label="' . addslashes( __('All Gateways', 'ARMember')) . '" data-value="">' . addslashes( __('All Gateways', 'ARMember') ) . '</li>';
if(!empty($payment_gateways)) {
    foreach ($payment_gateways as $key => $gateways) {
        $gateways_list .= '<li data-label="' . $gateways['gateway_name'] . '" data-value="' . $key . '">' . $gateways['gateway_name'] . '</li>';    
    }
}

$gateways_list .= "<li data-label='".esc_html__('Manual','ARMember')."' data-value='manual'>".esc_html__('Manual','ARMember')."</li>";

/*$is_wc_feature = get_option('arm_is_woocommerce_feature');
if( '1' == $is_wc_feature ){
    $gateways_list .= '<li data-label="' . addslashes( esc_html__('WooCommerce', 'ARMember') ) . '" data-value="woocommerce">'.addslashes( esc_html__('WooCommerce','ARMember') ).'</li>';
}*/

//echo "gateways_list : <br>".$gateways_list;die;
$plansLists = '<li data-label="' . addslashes( __('All Plans', 'ARMember')) . '" data-value="">' . addslashes( __('All Plans', 'ARMember') ) . '</li>';
if (!empty($all_active_plans)) {
    foreach ($all_active_plans as $p) {
        $p_id = $p['arm_subscription_plan_id'];
        $plansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
    }
}

$page_title = "";

if (isset($_GET['action']) && $_GET['action'] == 'member_report' ) {
    $page_title = esc_html__("Membership Report", "ARMember");
}
if (isset($_GET['action']) && $_GET['action'] == 'payment_report' ) {
    $page_title = esc_html__("Payments Report", "ARMember");
}
if (isset($_GET['action']) && $_GET['action'] == 'pay_per_post_report' ) {
    $page_title = esc_html__("Paid Post Report", "ARMember");
}
if (isset($_GET['action']) && $_GET['action'] == 'coupon_report' ) {
    $page_title = esc_html__("Coupon Report", "ARMember");
}

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();


if(isset($_POST["arm_export_report_data"]) && $_POST["arm_export_report_data"] == 1) {
    $type = $_POST['type'];
    $graph_type = $_POST['graph_type'];
    $arm_report_type = isset($_POST['arm_report_type']) ? $_POST['arm_report_type'] : '';
    $is_export_to_csv = isset($_POST['is_export_to_csv']) ? $_POST['is_export_to_csv'] : false;
    $is_pagination = false;
    require_once(MEMBERSHIP_VIEWS_DIR . '/arm_graph_ajax.php');
    exit;
}

?>

<?php
    $backToListingIcon = MEMBERSHIP_IMAGES_URL . '/back_to_listing_arrow.png';
    if (is_rtl()) {
        $backToListingIcon = MEMBERSHIP_IMAGES_URL . '/back_to_listing_arrow_right.png';
    }
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
            <?php echo $page_title; ?>
            <div class="armclear"></div>

        </div>

        <div class="armclear"></div>
        <form  method="post" action="#" id="arm_report_analytics_form">

<?php
if (isset($_GET['action']) && in_array($_GET['action'], array('member_report', 'payment_report', 'pay_per_post_report','coupon_report'))) {
    echo '<input type="hidden" name="arm_report_type" id="arm_report_type" value="'.$_GET['action'].'">';
    if ($_GET['action'] == 'member_report') { ?>

        <div class="arm_members_chart">
            <table border="0" align="middle" class="armtalbespacing">
                <tr>
                    <?php $float = (is_rtl()) ? 'float:right;' : 'float:left;'; ?>
                    <td align="left" class="arm_report_filters_td">
                        <div class="sltstandard" style=" <?php echo $float; ?>">
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="daily_members" onclick="javascript:arm_change_graph('daily', 'members');"><?php echo addslashes(esc_html__('Hourly', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="monthly_members" onclick="javascript:arm_change_graph('monthly', 'members');"><?php echo addslashes(esc_html__('Daily', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="yearly_members" onclick="javascript:arm_change_graph('yearly', 'members');"><?php echo addslashes(esc_html__('Monthly', 'ARMember')); ?></a>
                            </div>
                            

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_date_filter_item" class="arm_filter_status_box arm_import_export_date_fields">
                                    <input type="text" id="arm_date_filter" placeholder="Select date" class="arm_datepicker_filter" value="" autocomplete="off">
                                    
                                </div>
                            </div>


                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_month_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_month_filter" class="arm_month_filter" value="<?php echo $month; ?>">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php echo $month_label; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_month_filter" data-placeholder="Select Status">
                                                <?php echo $monthLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_year_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_year_filter" class="arm_year_filter" value="<?php echo $year; ?>">
                                    <dl class="arm_selectbox arm_width_100">
                                        <dt><span><?php echo $year; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_year_filter" data-placeholder="Select Status">
                                                <?php echo $yearLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_plan_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_plan_filter" class="arm_plan_filter" value="">
                                    <dl class="arm_selectbox arm_width_200">
                                        <dt><span><?php esc_html_e('All Plans', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_plan_filter" data-placeholder="Select Status">
                                                <?php echo $plansLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <input type="button" class="armemailaddbtn" id="arm_report_apply_filter_button" value="<?php esc_html_e('Apply','ARMember'); ?>" />
                                <input type="button" class="armemailaddbtn arm_cancel_btn" id="arm_report_export_button" value="<?php esc_html_e('Export to CSV','ARMember'); ?>">
                                <input type="hidden" value="monthly" name="armgraphval_members" id="armgraphval_members" />
                            </div>
                        </div>
                    </td>
                    <td align="left" class="arm_report_graph_buttons_td" style="<?php echo (is_rtl()) ? 'float:left;' : 'float:right;';?>">
                        <div class="armgraphtype armgraphtype_members" id="armgraphtype_members_div_bar" onclick="arm_change_graph_type('bar', 'members')">
                            <input type="radio" id="armgraphtype_members_bar" value="bar" name="armgraphtype_members">
                            <span class="armgraphtype_span">
                                <svg width="30px" height="30px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003 V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="armgraphtype armgraphtype_members selected" id="armgraphtype_members_div_line" onclick="arm_change_graph_type('line', 'members')">
                            <input type="radio"  value="line" id="armgraphtype_members_line" name="armgraphtype_members" checked>
                            <span class="armgraphtype_span">
                                <svg width="35px" height="35px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M26.835,8.673c-0.141,0-0.273-0.028-0.41-0.042l-3.493,8.709 c0.715,0.639,1.173,1.558,1.173,2.592c0,1.928-1.563,3.49-3.49,3.49s-3.49-1.563-3.49-3.49c0-0.395,0.08-0.768,0.201-1.122 l-5.351-7.229c-0.41,0.211-0.868,0.342-1.361,0.342c-0.074,0-0.143-0.017-0.215-0.022l-4.211,8.532 c0.258,0.442,0.417,0.949,0.417,1.498c0,1.652-1.339,2.991-2.991,2.991s-2.991-1.339-2.991-2.991s1.339-2.991,2.991-2.991 c0.35,0,0.68,0.071,0.992,0.182l3.957-8.021C7.986,10.557,7.621,9.79,7.621,8.933c0-1.652,1.34-2.992,2.992-2.992 s2.991,1.339,2.991,2.992c0,0.447-0.104,0.868-0.281,1.25l5.142,7.021c0.594-0.469,1.334-0.76,2.149-0.76 c0.218,0,0.429,0.026,0.636,0.064L24.6,8.01c-1.146-0.737-1.91-2.018-1.91-3.482c0-2.289,1.856-4.145,4.146-4.145 s4.146,1.856,4.146,4.145C30.98,6.817,29.124,8.673,26.835,8.673z"/>
                                </svg>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="chart_container_members">
                <div id="daily_chart" class="arm_chart_container">
                    <label class="lbltitle">Daily chart</label><br />
                    <div id="chart1_members" class="arm_chart_container_inner" ></div>
                </div>

                <div id="monthly_chart" class="arm_chart_container">
                    <label class="lbltitle">Monthly chart</label><br />
                    <div id="chart2_members" class="arm_chart_container_inner" ></div>
                </div>

                <div id="yearly_chart" class="arm_chart_container">
                    <label class="lbltitle">Yearly chart</label><br />
                    <div id="chart3_members" class="arm_chart_container_inner" ></div>
                </div>
                <span class="lbltitle next_chart" style="display: none;">Previous</span>
                <span class="lbltitle next_chart" style="display: none;">Next</span>
                <br /><br />
            </div>
            <br>
            <div class="arm_members_table_container">
                <div class="arm_all_loginhistory_wrapper">
                    <table class="form-table arm_member_last_subscriptions_table" width="100%">
                        <thead>
                            <tr>
                                <td><?php esc_html_e('Member', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Email', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Plan', 'ARMember'); ?></td>                                     
                                <td><?php esc_html_e('Next Recurring Date', 'ARMember'); ?></td>                                
                                <td><?php esc_html_e('Plan Expire Date', 'ARMember'); ?></td>                                          
                                <td><?php esc_html_e('Join Date', 'ARMember'); ?></td>
                            </tr>
                        </thead>
                        <tbody class="arm_members_table_body_content">
                            
                        </tbody>
                    </table>
                    <div class="arm_membership_history_pagination_block">
                        <div class="arm_membership_history_paging_container" id="arm_members_table_paging">
                        </div>
                    </div>
                </div>
            </div>

        </div>

<?php }
    else if ($_GET['action'] == 'payment_report') { ?>

        <div class="arm_member_payment_history_chart">
            <table border="0" align="middle" class="armtalbespacing">
                <tr>
                    <?php $float = (is_rtl()) ? 'float:right;' : 'float:left;'; ?>
                    <td align="left" class="arm_report_filters_td">
                        <div class="sltstandard" style=" <?php echo $float; ?>">
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="daily_payment_history" onclick="javascript:arm_change_graph('daily', 'payment_history');"><?php echo addslashes(esc_html__('Hourly', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="monthly_payment_history" onclick="javascript:arm_change_graph('monthly', 'payment_history');"><?php echo addslashes(esc_html__('Daily', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="yearly_payment_history" onclick="javascript:arm_change_graph('yearly', 'payment_history');"><?php echo addslashes(esc_html__('Monthly', 'ARMember')); ?></a>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_date_filter_item" class="arm_filter_status_box arm_import_export_date_fields">
                                    <input type="text" id="arm_date_filter" placeholder="Select date" class="arm_datepicker_filter" value="" autocomplete="off">  
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_month_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_month_filter" class="arm_month_filter" value="<?php echo $month; ?>">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php echo $month_label; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_month_filter" data-placeholder="Select Status">
                                                <?php echo $monthLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_year_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_year_filter" class="arm_year_filter" value="<?php echo $year; ?>">
                                    <dl class="arm_selectbox arm_width_100">
                                        <dt><span><?php echo $year; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_year_filter" data-placeholder="Select Status">
                                                <?php echo $yearLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_plan_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_plan_filter" class="arm_plan_filter" value="">
                                    <dl class="arm_selectbox arm_width_200">
                                        <dt><span><?php esc_html_e('All Plans', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_plan_filter" data-placeholder="Select Status">
                                                <?php echo $plansLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_gateway_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_gateway_filter" class="arm_gateway_filter" value="">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php esc_html_e('All Gateways', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_gateway_filter" data-placeholder="Select Status">
                                                <?php echo $gateways_list; ?>
                                                <?php ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <input type="button" class="armemailaddbtn" id="arm_report_apply_filter_button" value="<?php esc_html_e('Apply','ARMember'); ?>" />
                                <input type="button" class="armemailaddbtn arm_cancel_btn" id="arm_report_export_button" value="<?php esc_html_e('Export to CSV','ARMember'); ?>">
                                <input type="hidden" value="monthly" name="armgraphval_payment_history" id="armgraphval_payment_history" />
                            </div>
                        </div>
                    </td>
                    <td align="left" class="arm_report_graph_buttons_td" style="<?php echo (is_rtl()) ? 'float:left;' : 'float:right;';?>">
                        <div class="armgraphtype armgraphtype_payment_history" id="armgraphtype_payment_history_div_bar" onclick="arm_change_graph_type('bar', 'payment_history')">
                            <input type="radio" id="armgraphtype_payment_history_bar" value="bar" name="armgraphtype_payment_history">
                            <span class="armgraphtype_span">
                                <svg width="30px" height="30px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003 V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="armgraphtype armgraphtype_payment_history selected" id="armgraphtype_payment_history_div_line" onclick="arm_change_graph_type('line', 'payment_history')">
                            <input type="radio"  value="line" id="armgraphtype_payment_history_line" name="armgraphtype_payment_history" checked>
                            <span class="armgraphtype_span">
                                <svg width="35px" height="35px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M26.835,8.673c-0.141,0-0.273-0.028-0.41-0.042l-3.493,8.709 c0.715,0.639,1.173,1.558,1.173,2.592c0,1.928-1.563,3.49-3.49,3.49s-3.49-1.563-3.49-3.49c0-0.395,0.08-0.768,0.201-1.122 l-5.351-7.229c-0.41,0.211-0.868,0.342-1.361,0.342c-0.074,0-0.143-0.017-0.215-0.022l-4.211,8.532 c0.258,0.442,0.417,0.949,0.417,1.498c0,1.652-1.339,2.991-2.991,2.991s-2.991-1.339-2.991-2.991s1.339-2.991,2.991-2.991 c0.35,0,0.68,0.071,0.992,0.182l3.957-8.021C7.986,10.557,7.621,9.79,7.621,8.933c0-1.652,1.34-2.992,2.992-2.992 s2.991,1.339,2.991,2.992c0,0.447-0.104,0.868-0.281,1.25l5.142,7.021c0.594-0.469,1.334-0.76,2.149-0.76 c0.218,0,0.429,0.026,0.636,0.064L24.6,8.01c-1.146-0.737-1.91-2.018-1.91-3.482c0-2.289,1.856-4.145,4.146-4.145 s4.146,1.856,4.146,4.145C30.98,6.817,29.124,8.673,26.835,8.673z"/>
                                </svg>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="chart_container_payment_history">
                <div id="daily_chart" class="arm_chart_container">
                    <label class="lbltitle">Daily chart</label><br />
                    <div id="chart1_payment_history" class="arm_chart_container_inner"></div>
                </div>

                <div id="monthly_chart" class="arm_chart_container">
                    <label class="lbltitle">Monthly chart</label><br />
                    <div id="chart2_payment_history" class="arm_chart_container_inner"></div>
                </div>

                <div id="yearly_chart" class="arm_chart_container">
                    <label class="lbltitle">Yearly chart</label><br />
                    <div id="chart3_payment_history" class="arm_chart_container_inner"></div>
                </div>
                <span class="lbltitle next_chart" style="display: none;">Previous</span>
                <span class="lbltitle next_chart" style="display: none;">Next</span>
                <br /><br />
            </div>

            <br>
            <div class="arm_members_table_container">

                <div class="arm_all_loginhistory_wrapper">
                    <table class="form-table arm_member_last_subscriptions_table" width="100%">
                        <thead>
                            <tr>
                                <td><?php esc_html_e('Invoice ID', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Member' , 'ARMember'); ?></td>
                                <td><?php esc_html_e('Paid By', 'ARMember'); ?></td>                                
                                <td><?php esc_html_e('Plan', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Paid Amount', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Gateway', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Date', 'ARMember'); ?></td>
                            </tr>
                        </thead>
                        <tbody class="arm_payments_table_body_content">
                            
                        </tbody>
                    </table>
                    <div class="arm_membership_history_pagination_block">
                        <div class="arm_membership_history_paging_container" id="arm_payments_table_paging">
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="arm_invoice_detail_container"></div>

<?php }
    else if ($_GET['action'] == 'pay_per_post_report') { ?>

        <div class="arm_member_pay_per_post_report_chart">
            <table border="0" align="middle" class="armtalbespacing">
                <tr>
                    <?php $float = (is_rtl()) ? 'float:right;' : 'float:left;'; ?>
                    <td align="left" class="arm_report_filters_td">
                        <div class="sltstandard" style=" <?php echo $float; ?>">
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="daily_pay_per_post_report" onclick="javascript:arm_change_graph('daily', 'pay_per_post_report');"><?php echo addslashes(esc_html__('Hourly', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="monthly_pay_per_post_report" onclick="javascript:arm_change_graph('monthly', 'pay_per_post_report');"><?php echo addslashes(esc_html__('Daily', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="yearly_pay_per_post_report" onclick="javascript:arm_change_graph('yearly', 'pay_per_post_report');"><?php echo addslashes(esc_html__('Monthly', 'ARMember')); ?></a>
                            </div>

                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_date_filter_item" class="arm_filter_status_box arm_import_export_date_fields">
                                    <input type="text" id="arm_date_filter" placeholder="Select date" class="arm_datepicker_filter" value="" autocomplete="off">  
                                </div>
                            </div>

                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_month_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_month_filter" class="arm_month_filter" value="<?php echo $month; ?>">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php echo $month_label; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_month_filter" data-placeholder="Select Status">
                                                <?php echo $monthLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_year_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_year_filter" class="arm_year_filter" value="<?php echo $year; ?>">
                                    <dl class="arm_selectbox arm_width_100">
                                        <dt><span><?php echo $year; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_year_filter" data-placeholder="Select Status">
                                                <?php echo $yearLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_plan_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_plan_filter" class="arm_plan_filter" value="">
                                    <dl class="arm_selectbox arm_width_200">
                                        <dt><span><?php esc_html_e('All Plans', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_plan_filter" data-placeholder="Select Status">
                                                <?php echo $plansLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_gateway_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_gateway_filter" class="arm_gateway_filter" value="">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php esc_html_e('All Gateways', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_gateway_filter" data-placeholder="Select Status">
                                                <?php echo $gateways_list; ?>
                                                <?php ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div style="<?php echo $float; ?>" class="arm_filter_div">
                                <input type="button" class="armemailaddbtn" id="arm_report_apply_filter_button" value="<?php esc_html_e('Apply','ARMember'); ?>" />
                                <input type="button" class="armemailaddbtn arm_cancel_btn" id="arm_report_export_button" value="<?php esc_html_e('Export to CSV','ARMember'); ?>">
                                <input type="hidden" value="monthly" name="armgraphval_pay_per_post_report" id="armgraphval_pay_per_post_report" />
                            </div>
                        </div>
                    </td>
                    <td align="left" class="arm_report_graph_buttons_td" style="<?php echo (is_rtl()) ? 'float:left;' : 'float:right;';?>">
                        <div class="armgraphtype armgraphtype_pay_per_post_report" id="armgraphtype_pay_per_post_report_div_bar" onclick="arm_change_graph_type('bar', 'pay_per_post_report')">
                            <input type="radio" id="armgraphtype_pay_per_post_report_bar" value="bar" name="armgraphtype_pay_per_post_report">
                            <span class="armgraphtype_span">
                                <svg width="30px" height="30px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003 V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="armgraphtype armgraphtype_pay_per_post_report selected" id="armgraphtype_pay_per_post_report_div_line" onclick="arm_change_graph_type('line', 'pay_per_post_report')">
                            <input type="radio"  value="line" id="armgraphtype_pay_per_post_report_line" name="armgraphtype_pay_per_post_report" checked>
                            <span class="armgraphtype_span">
                                <svg width="35px" height="35px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M26.835,8.673c-0.141,0-0.273-0.028-0.41-0.042l-3.493,8.709 c0.715,0.639,1.173,1.558,1.173,2.592c0,1.928-1.563,3.49-3.49,3.49s-3.49-1.563-3.49-3.49c0-0.395,0.08-0.768,0.201-1.122 l-5.351-7.229c-0.41,0.211-0.868,0.342-1.361,0.342c-0.074,0-0.143-0.017-0.215-0.022l-4.211,8.532 c0.258,0.442,0.417,0.949,0.417,1.498c0,1.652-1.339,2.991-2.991,2.991s-2.991-1.339-2.991-2.991s1.339-2.991,2.991-2.991 c0.35,0,0.68,0.071,0.992,0.182l3.957-8.021C7.986,10.557,7.621,9.79,7.621,8.933c0-1.652,1.34-2.992,2.992-2.992 s2.991,1.339,2.991,2.992c0,0.447-0.104,0.868-0.281,1.25l5.142,7.021c0.594-0.469,1.334-0.76,2.149-0.76 c0.218,0,0.429,0.026,0.636,0.064L24.6,8.01c-1.146-0.737-1.91-2.018-1.91-3.482c0-2.289,1.856-4.145,4.146-4.145 s4.146,1.856,4.146,4.145C30.98,6.817,29.124,8.673,26.835,8.673z"/>
                                </svg>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="chart_container_pay_per_post_report">
                <div id="daily_chart" class="arm_chart_container">
                    <label class="lbltitle">Daily chart</label><br />
                    <div id="chart1_pay_per_post_report" class="arm_chart_container_inner"></div>
                </div>

                <div id="monthly_chart" class="arm_chart_container">
                    <label class="lbltitle">Monthly chart</label><br />
                    <div id="chart2_pay_per_post_report" class="arm_chart_container_inner"></div>
                </div>

                <div id="yearly_chart" class="arm_chart_container">
                    <label class="lbltitle">Yearly chart</label><br />
                    <div id="chart3_pay_per_post_report" class="arm_chart_container_inner"></div>
                </div>
                <span class="lbltitle next_chart" style="display: none;">Previous</span>
                <span class="lbltitle next_chart" style="display: none;">Next</span>
                <br /><br />
            </div>

            <br>
            <div class="arm_members_table_container">

                <div class="arm_all_loginhistory_wrapper">
                    <table class="form-table arm_member_last_subscriptions_table" width="100%">
                        <thead>
                            <tr>
                                <td><?php esc_html_e('Invoice ID', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Member', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Paid By', 'ARMember'); ?></td>                                
                                <td><?php esc_html_e('Paid Post', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Paid Amount', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Gateway', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Date', 'ARMember'); ?></td>
                            </tr>
                        </thead>
                        <tbody class="arm_pay_per_post_report_table_body_content">
                            
                        </tbody>
                    </table>
                    <div class="arm_membership_history_pagination_block">
                        <div class="arm_membership_history_paging_container" id="arm_payments_table_paging">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="arm_invoice_detail_container"></div>

<?php } else if ($_GET['action'] == 'coupon_report') { ?>

        <div class="arm_member_coupon_report_chart">
            <table border="0" align="middle" class="armtalbespacing">
                <tr>
                    <?php $float = (is_rtl()) ? 'float:right;' : 'float:left;'; ?>
                    <td align="left" class="arm_report_filters_td">
                        <div class="sltstandard" style=" <?php echo $float; ?>">
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="daily_coupon_report" onclick="javascript:arm_change_graph('daily', 'coupon_report');"><?php echo addslashes(esc_html__('Hourly', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="monthly_coupon_report" onclick="javascript:arm_change_graph('monthly', 'coupon_report');"><?php echo addslashes(esc_html__('Daily', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>">
                                <a href="javascript:void(0);" class="btn_chart_type" id="yearly_coupon_report" onclick="javascript:arm_change_graph('yearly', 'coupon_report');"><?php echo addslashes(esc_html__('Monthly', 'ARMember')); ?></a>
                            </div>
                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_date_filter_item" class="arm_filter_status_box arm_import_export_date_fields">
                                    <input type="text" id="arm_date_filter" placeholder="Select date" class="arm_datepicker_filter" value="" autocomplete="off">  
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_month_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_month_filter" class="arm_month_filter" value="<?php echo $month; ?>">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php echo $month_label; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_month_filter" data-placeholder="Select Status">
                                                <?php echo $monthLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_year_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_year_filter" class="arm_year_filter" value="<?php echo $year; ?>">
                                    <dl class="arm_selectbox arm_width_100">
                                        <dt><span><?php echo $year; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_year_filter" data-placeholder="Select Status">
                                                <?php echo $yearLists; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_search_coupon_filter_item" class="arm_filter_status_box arm_datatable_searchbox arm_datatable_filter_item">
                                    <input type="text" id="arm_search_coupon" class="arm_search_coupon" name="arm_search_coupon" placeholder="<?php esc_html_e('Coupon Code', 'ARMember');?>" value="">
                                </div>
                            </div>
                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <div id="arm_gateway_filter_item" class="arm_filter_status_box arm_datatable_filter_item">
                                    <input type="hidden" id="arm_gateway_filter" class="arm_gateway_filter" value="">
                                    <dl class="arm_selectbox arm_width_150">
                                        <dt><span><?php esc_html_e('All Gateways', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_gateway_filter" data-placeholder="Select Status">
                                                <?php echo $gateways_list; ?>
                                                <?php ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div style=" <?php echo $float; ?>" class="arm_filter_div">
                                <input type="button" class="armemailaddbtn" id="arm_report_apply_filter_button" value="<?php esc_html_e('Apply','ARMember'); ?>" />
                                <input type="button" class="armemailaddbtn arm_cancel_btn" id="arm_report_export_button" value="<?php esc_html_e('Export to CSV','ARMember'); ?>">
                                <input type="hidden" value="monthly" name="armgraphval_coupon_report" id="armgraphval_coupon_report" />
                            </div>
                        </div>
                    </td>
                    <td align="left" class="arm_report_graph_buttons_td" style="<?php echo (is_rtl()) ? 'float:left;' : 'float:right;';?>">
                        <div class="armgraphtype armgraphtype_coupon_report" id="armgraphtype_coupon_report_div_bar" onclick="arm_change_graph_type('bar', 'coupon_report')">
                            <input type="radio" id="armgraphtype_coupon_report_bar" value="bar" name="armgraphtype_coupon_report">
                            <span class="armgraphtype_span">
                                <svg width="30px" height="30px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003 V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="armgraphtype armgraphtype_coupon_report selected" id="armgraphtype_coupon_report_div_line" onclick="arm_change_graph_type('line', 'coupon_report')">
                            <input type="radio"  value="line" id="armgraphtype_coupon_report_line" name="armgraphtype_coupon_report" checked>
                            <span class="armgraphtype_span">
                                <svg width="35px" height="35px">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M26.835,8.673c-0.141,0-0.273-0.028-0.41-0.042l-3.493,8.709 c0.715,0.639,1.173,1.558,1.173,2.592c0,1.928-1.563,3.49-3.49,3.49s-3.49-1.563-3.49-3.49c0-0.395,0.08-0.768,0.201-1.122 l-5.351-7.229c-0.41,0.211-0.868,0.342-1.361,0.342c-0.074,0-0.143-0.017-0.215-0.022l-4.211,8.532 c0.258,0.442,0.417,0.949,0.417,1.498c0,1.652-1.339,2.991-2.991,2.991s-2.991-1.339-2.991-2.991s1.339-2.991,2.991-2.991 c0.35,0,0.68,0.071,0.992,0.182l3.957-8.021C7.986,10.557,7.621,9.79,7.621,8.933c0-1.652,1.34-2.992,2.992-2.992 s2.991,1.339,2.991,2.992c0,0.447-0.104,0.868-0.281,1.25l5.142,7.021c0.594-0.469,1.334-0.76,2.149-0.76 c0.218,0,0.429,0.026,0.636,0.064L24.6,8.01c-1.146-0.737-1.91-2.018-1.91-3.482c0-2.289,1.856-4.145,4.146-4.145 s4.146,1.856,4.146,4.145C30.98,6.817,29.124,8.673,26.835,8.673z"/>
                                </svg>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="chart_container_coupon_report">
                <div id="daily_chart" class="arm_chart_container">
                    <label class="lbltitle">Daily chart</label><br />
                    <div id="chart1_coupon_report" class="arm_chart_container_inner"></div>
                </div>

                <div id="monthly_chart" class="arm_chart_container">
                    <label class="lbltitle">Monthly chart</label><br />
                    <div id="chart2_coupon_report" class="arm_chart_container_inner"></div>
                </div>

                <div id="yearly_chart" class="arm_chart_container">
                    <label class="lbltitle">Yearly chart</label><br />
                    <div id="chart3_coupon_report" class="arm_chart_container_inner"></div>
                </div>
                <span class="lbltitle next_chart" style="display: none;">Previous</span>
                <span class="lbltitle next_chart" style="display: none;">Next</span>
                <br /><br />
            </div>

            <br>
            <div class="arm_members_table_container">

                <div class="arm_all_loginhistory_wrapper">
                    <table class="form-table arm_member_last_subscriptions_table" width="100%">
                        <thead>
                            <tr>
                                <td><?php esc_html_e('Coupon Code', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Coupon Discount', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Member', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Plan', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Paid Amount', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Gateway', 'ARMember'); ?></td>
                                <td><?php esc_html_e('Payment Date', 'ARMember'); ?></td>
                            </tr>
                        </thead>
                        <tbody class="arm_coupon_report_table_body_content">                            
                        </tbody>
                    </table>
                    <div class="arm_membership_history_pagination_block">
                        <div class="arm_membership_history_paging_container" id="arm_coupon_report_table_paging">
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="arm_invoice_detail_container"></div>
    <?php }
}
?>
            <input type='hidden' name='is_export_to_csv' value='0'>
            <input type='hidden' name='current_page' value=''>
            <input type='hidden' name='gateway_filter' value=''>
            <input type='hidden' name='date_filter' value=''>
            <input type='hidden' name='month_filter' value=''>
            <input type='hidden' name='year_filter' value=''>
            <input type='hidden' name='plan_id' value=''>
            <input type='hidden' name='plan_type' value=''>
            <input type='hidden' name='graph_type' value=''>
            <input type='hidden' name='type' value=''>
            <input type='hidden' name='action' value=''>
            <input type='hidden' name='arm_export_report_data' value='0'>
            <input type="hidden" name="arm_search_coupon" value="">

        </form>
    </div>
</div>
<?php wp_nonce_field( 'arm_wp_nonce' );?> 
<div class="arm_member_view_detail_container"></div>
<?php
    echo $ARMember->arm_get_need_help_html_content('members-report-analysis');
?>