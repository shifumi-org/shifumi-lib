<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_manage_coupons, $arm_payment_gateways, $arm_subscription_plans, $arm_transaction;

$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$nowDate = current_time('mysql');
$filter_gateway = (!empty($_POST['gateway'])) ? $_POST['gateway'] : '0';
$filter_ptype = (!empty($_POST['ptype'])) ? $_POST['ptype'] : '0';
$filter_pmode = (!empty($_POST['pmode'])) ? $_POST['pmode'] : '0';
$filter_pstatus = (!empty($_POST['pstatus'])) ? $_POST['pstatus'] : '0';
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
$default_hide = array(
    'arm_transaction_id' => 'Transaction ID',
    'arm_invoice_id' => 'Invoice ID',
    'arm_user_fname' => 'First Name',
    'arm_user_lname' => 'Last Name',
    'arm_user_id' => 'User',
    'arm_user_email' => 'User Email',
    'arm_plan_id' => 'Membership',
    'arm_payment_gateway' => 'Gateway',
    'arm_payment_type' => 'Payment Type',
    'arm_payer_email' => 'Payer Email',
    'arm_transaction_status' => 'Transaction Status',
    'arm_created_date' => 'Payment Date',
    'arm_amount' => 'Amount',
    'arm_cc_number' => 'Credit Card Number',
);
$user_id = get_current_user_id();
$transaction_show_hide_column = maybe_unserialize(get_user_meta($user_id, 'arm_transaction_paid_post_hide_show_columns', true));

$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

$i = 1;
$column_hide = "";
if(!empty($transaction_show_hide_column)) {
    foreach ($transaction_show_hide_column as $value) {
        if ($value != 1) {
            $column_hide = $column_hide . $i . ',';
        }
        $i++;
    }
} else {
    $column_hide = '3,4';
}


if(isset($_POST["arm_export_pphistory"]) && $_POST["arm_export_pphistory"] == 1) {

    $filter_gateway = isset($_REQUEST['arm_filter_pp_gateway']) ? $_REQUEST['arm_filter_pp_gateway'] : '';
    $filter_ptype = isset($_REQUEST['arm_filter_pptype']) ? $_REQUEST['arm_filter_pptype'] : '';
    $filter_pmode = isset($_REQUEST['arm_filter_ppmode']) ? $_REQUEST['arm_filter_ppmode'] : '';
    $filter_pstatus = isset($_REQUEST['arm_filter_ppstatus']) ? $_REQUEST['arm_filter_ppstatus'] : '';
    $payment_start_date = isset($_REQUEST['arm_filter_ppstart_date']) ? $_REQUEST['arm_filter_ppstart_date'] : '';
    $payment_end_date = isset($_REQUEST['arm_filter_ppend_date']) ? $_REQUEST['arm_filter_ppend_date'] : '';
    $sSearch = isset($_REQUEST['armmanagesearch_pp_new']) ? $_REQUEST['armmanagesearch_pp_new'] : '';

    $arm_is_post_payment = isset($_REQUEST['arm_is_post_payment']) ? $_REQUEST['arm_is_post_payment'] : 0;

    $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

    $where_plog = "WHERE 1=1 AND arm_display_log = 1 ";

    if (!empty($filter_gateway) && $filter_gateway != '0') {
        $where_plog .= " AND `arm_payment_gateway` = '$filter_gateway'";
    }
    if (!empty($filter_ptype) && $filter_ptype != '0') {
        $where_plog .= " AND `arm_payment_type` = '$filter_ptype'";
    }
    if (!empty($filter_pmode) && $filter_pmode != '0') {
        $where_plog .= " AND `arm_payment_mode` = '$filter_pmode'";
    }

    if(!empty($arm_is_post_payment) && $arm_is_post_payment != '0'){

        $where_plog .= " AND `arm_is_post_payment`='$arm_is_post_payment'";
    }else{
        $where_plog .= " AND `arm_is_post_payment`='$arm_is_post_payment'";
    }
    
    if (!empty($filter_pstatus) && $filter_pstatus != '0') {
        $filter_pstatus = strtolower($filter_pstatus);
        $status_query = " AND ( LOWER(`arm_transaction_status`)='$filter_pstatus'";
        if(!in_array($filter_pstatus,array('success','pending','canceled'))) {
            $status_query .= ")";
        }
        switch ($filter_pstatus) {
            case 'success':
                $status_query .= " OR `arm_transaction_status`='1')";
                break;
            case 'pending':
                $status_query .= " OR `arm_transaction_status`='0')";
                break;
            case 'canceled':
                $status_query .= " OR `arm_transaction_status`='2')";
                break;
        }
        $where_plog .= $status_query;
    }

    $pt_where = $bt_where = "";
    if(!empty($payment_start_date)) {
        $payment_start_date = date("Y-m-d", strtotime($payment_start_date));
        $pt_where .= " WHERE `pt`.`arm_created_date` >= '$payment_start_date' ";
        $bt_where .= " WHERE `bt`.`arm_created_date` >= '$payment_start_date' ";
    }

    if(!empty($payment_end_date)) {
        $payment_end_date = date("Y-m-d", strtotime("+1 day", strtotime($payment_end_date)));
        if($pt_where != "") $pt_where .= " AND "; else $pt_where = " WHERE ";
        $pt_where .= " `pt`.`arm_created_date` < '$payment_end_date' ";

        if($bt_where != "") $bt_where .= " AND "; else $bt_where = " WHERE ";
        $bt_where .= " `bt`.`arm_created_date` < '$payment_end_date' ";
    }

    $search_ = "";
    if ($sSearch != '') {
        $search_ = " AND (`arm_payment_history_log`.`arm_transaction_id` LIKE '%{$sSearch}%' OR `arm_payment_history_log`.`arm_payer_email` LIKE '%{$sSearch}%' OR `arm_payment_history_log`.`arm_created_date` LIKE '%{$sSearch}%' OR `arm_user_email` LIKE '%{$sSearch}%' ) ";
    }

    $orderby = "ORDER BY `arm_payment_history_log`.`arm_invoice_id` DESC";
    $ctquery = "SELECT pt.arm_log_id,pt.arm_invoice_id,pt.arm_user_id,pt.arm_first_name,pt.arm_last_name,pt.arm_plan_id,pt.arm_payer_email,pt.arm_transaction_id,pt.arm_amount,pt.arm_currency,pt.arm_is_trial,pt.arm_payment_gateway,pt.arm_payment_mode,pt.arm_transaction_status,pt.arm_created_date,pt.arm_payment_type,pt.arm_extra_vars,sp.arm_subscription_plan_name,wpu.user_login as arm_user_login, wpu.user_email as arm_user_email, pt.arm_display_log as arm_display_log,pt.arm_is_post_payment as arm_is_post_payment FROM `" . $ARMember->tbl_arm_payment_log . "` pt LEFT JOIN `" . $ARMember->tbl_arm_subscription_plans . "` sp ON pt.arm_plan_id = sp.arm_subscription_plan_id LEFT JOIN `" . $wpdb->users . "` wpu ON pt.arm_user_id = wpu.ID " . $pt_where;
    $ptquery = "{$ctquery}";
        
    $payment_grid_query = "SELECT * FROM (" . $ptquery . ") AS arm_payment_history_log {$where_plog} {$search_} {$orderby}";

    $payment_log = $wpdb->get_results($payment_grid_query, ARRAY_A);

        $final_log = array();
        $tmp = array (
            "Transaction_Id" => '',
            "Invoice_Id" => '',
            "First_Name" => '',
            "Last_Name" => '',
            "User" => '',
            "User Email" => '',
            "Membership" => '',
            "Gateway" => '',
            "Payment_Type" => '',
            "Payer_Email" => '',
            "Transaction_Status" => '',
            "Payment_Date" => '',
            "Amount" => '',
            "Credit_Card_Number" => ''
        );
        foreach ($payment_log as $row) {
            $ccn = maybe_unserialize($row["arm_extra_vars"]);
            $arm_transaction_status = $row["arm_transaction_status"];
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
                    $arm_transaction_status = $row["arm_transaction_status"];
                    break;
            }
            $tmp["Transaction_Id"] = $row["arm_transaction_id"];
            if($tmp["Transaction_Id"] == "-") {
                $tmp["Transaction_Id"] = "";
            }
            $tmp["Invoice_Id"] = $row["arm_invoice_id"];
            $tmp["First_Name"] = $row["arm_first_name"];
            $tmp["Last_Name"] = $row["arm_last_name"];
            $tmp["User"] = $row["arm_user_login"];
            $tmp["User Email"] = $row["arm_user_email"];
            $tmp["Membership"] = $row["arm_subscription_plan_name"];
            $tmp["Gateway"] = $row["arm_payment_gateway"] == "" ? __('Manual', 'ARMember') : $arm_payment_gateways->arm_gateway_name_by_key($row["arm_payment_gateway"]);
            $tmp["Payment_Type"] = "";
            $tmp["Payer_Email"] = $row["arm_payer_email"];
            $tmp["Transaction_Status"] = $arm_transaction_status;
            $tmp["Payment_Date"] = date_i18n($date_time_format, strtotime($row["arm_created_date"]));
            $tmp["Amount"] = $row["arm_amount"] . " " . $row["arm_currency"];
            $tmp["Credit_Card_Number"] = isset($ccn["card_number"]) ? $ccn["card_number"] : '';
            if($tmp["Credit_Card_Number"] == "-") {
                $tmp["Credit_Card_Number"] = "";
            }

            $log_payment_mode = $row["arm_payment_type"];
            $plan_id = $row["arm_plan_id"];
            $plan_info = new ARM_Plan($plan_id);
            $payment_type_text = $user_payment_mode = "";

            if($plan_info->is_recurring()) {
                if($log_payment_mode != '') {
                    if($log_payment_mode == 'manual_subscription') {
                        $user_payment_mode .= "";
                    }
                    else {
                        $user_payment_mode .= "(" . __('Automatic','ARMember') . ")";
                    }
                }
                $payment_type = 'subscription';
            }

            if($payment_type =='one_time') {
                $payment_type_text = __('One Time', 'ARMember');
            }
            else if($payment_type == 'subscription') {
                $payment_type_text = __('Subscription', 'ARMember');
            }

            if($row["arm_is_trial"] == 1) {
                $arm_trial = "(" . __('Trial Transaction','ARMember') . ")";
            }
            else {
                $arm_trial = '';
            }

            $tmp["Payment_Type"] = $payment_type_text . " " . $user_payment_mode . " " . $arm_trial;
            array_push($final_log, $tmp);
        }

        ob_clean();
        ob_start();
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=ARMember-export-paid-post-payment-history.csv");
        header("Content-Transfer-Encoding: binary");
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($tmp));
        if(!empty($final_log)) {
            foreach ($final_log as $row) {
                fputcsv($df, $row);
            }
        }
        fclose($df);
        exit;
}
?>
<style type="text/css">
    #armmanagesearch_pp_new{ width:150px; }
    .wrap #armember_datatable_1_wrapper .dt-buttons .ColVis_Button:hover{ background: #fff; }
    @media all and (min-width:1400px) {
        #armmanagesearch_pp_new{
            width:200px;
        }
    }
    @media all and (min-width:1600px) {
        #armmanagesearch_pp_new{
            width:250px;
        }
    }
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

    jQuery(document).ready(function () {
        arm_load_paid_post_transaction_list_grid(false);
    });

    function arm_load_paid_post_trasaction_list_filtered_grid() {
        jQuery('#arm_paid_post_payment_grid_filter_btn').attr('disabled', 'disabled');
        jQuery('#armember_datatable_1').dataTable().fnDestroy();
        arm_load_paid_post_transaction_list_grid(true);
    }

    function arm_load_paid_post_transaction_list_grid(is_filtered) {
        var __ARM_Showing = '<?php echo addslashes(__('Showing','ARMember')); ?>';
        var __ARM_Showing_empty = '<?php echo addslashes(__('Showing 0 to 0 of 0 entries','ARMember')); ?>';
        var __ARM_to = '<?php echo addslashes(__('to','ARMember')); ?>';
        var __ARM_of = '<?php echo addslashes(__('of','ARMember')); ?>';
        var __ARM_transactions = '<?php _e('entries','ARMember'); ?>';
        var __ARM_Show = '<?php echo addslashes(__('Show','ARMember')); ?>';
        var __ARM_NO_FOUNT = '<?php echo addslashes(__('No any transaction found yet.','ARMember')); ?>';
        var __ARM_NO_MATCHING = '<?php echo addslashes(__('No matching transactions found.','ARMember')); ?>';
        var payment_gateway = jQuery("#arm_filter_pp_gateway").val();
        var payment_type = jQuery("#arm_filter_pptype").val();
        var payment_mode = jQuery("#arm_filter_ppmode").val();
        var payment_status = jQuery("#arm_filter_ppstatus").val();
        var search_term = jQuery("#armmanagesearch_pp_new").val();
        var payment_start_date = jQuery("#arm_filter_ppstart_date").val();
        var payment_end_date = jQuery("#arm_filter_ppend_date").val();
        var ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
        var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
        var _wpnonce = jQuery('input[name="_wpnonce"]').val();
        var oTables = jQuery('#armember_datatable_1').dataTable({
            "bProcessing": false,
            "oLanguage": {
                    "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_transactions,
                    "sInfoEmpty": __ARM_Showing_empty,
                   
                    "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_transactions,
                    "sEmptyTable": __ARM_NO_FOUNT,
                    "sZeroRecords": __ARM_NO_MATCHING
                },
            "language":{
                "searchPlaceholder": "Search",
                "search":"",
            },
            "buttons":[{
                "extend":"colvis",
                "columns":":not(.noVis)",
                "className":"ColVis_Button TableTools_Button ui-button ui-state-default ColVis_MasterButton",
                "text":"<span class=\"armshowhideicon\" style=\"background-image: url(<?php echo MEMBERSHIP_IMAGES_URL; ?>/show_hide_icon.png);background-repeat: no-repeat;background-position: 8px center;padding: 0 10px 0 30px;background-color: #FFF;\">Show / Hide columns</span>",
            }],      
            "bServerSide": true,
            "sAjaxSource": __ARMAJAXURL,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({"name": "action", "value": "arm_load_transactions"});
                aoData.push({"name": "gateway", "value": payment_gateway});
                aoData.push({"name": "payment_type", "value": payment_type});
                aoData.push({"name": "payment_status", "value": payment_status});
                aoData.push({"name": "payment_mode", "value": payment_mode});
                aoData.push({"name": "payment_start_date", "value": payment_start_date});
                aoData.push({"name": "payment_end_date", "value": payment_end_date});
                aoData.push({"name": "sSearch", "value": search_term});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
                aoData.push({"name": "arm_is_post_payment", "value": '1'});
            },
            "bRetrieve": false,
            "sDom": '<"H"CBfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "oColVis": {
                "aiExclude": [0, <?php echo count($default_hide) + 1; ?>]
            },
            "aoColumnDefs": [
                {"aTargets":[0],"sClass":"noVis"},
                {"sType": "html", "bVisible": false, "aTargets": [<?php echo $column_hide; ?>]},
                {"bSortable": false, "aTargets": [0]}
            ],
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnPreDrawCallback": function () {
                jQuery('#paid_post_transactions_list_form .arm_loading_grid').show();
            },
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            "fnDrawCallback": function () {
                arm_show_data();
                jQuery('#paid_post_transactions_list_form .arm_loading_grid').hide();
                jQuery(".cb-select-all-th").removeClass('sorting_asc');
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                if (filtered_data == true) {
                    var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
                    jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
                    jQuery('div#armember_datatable_1_filter').hide();
                }
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
                jQuery('#arm_paid_post_payment_grid_filter_btn').removeAttr('disabled');
            },
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                oData.iStart = 0;
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                    );
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
               // oData.oSearch.sSearch = search_term;
            },
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
        jQuery('div#armember_datatable_1_filter').hide();
        jQuery('#arm_filter_wrapper').remove();
        jQuery('#armmanagesearch_pp_new').on('keyup', function (e) {
            e.stopPropagation();
            if (e.keyCode == 13) {
                var gateway = jQuery('#arm_filter_pp_gateway').val();
                var ptype = jQuery('#arm_filter_pptype').val();
                var pstatus = jQuery('#arm_filter_ppstatus').val();
                var search = jQuery('#armmanagesearch_pp_new').val();
                arm_reload_log_list(gateway, ptype, pstatus, search);
                return false;
            }
        });
    }
    function ChangeID(id, type)
    {
        document.getElementById('pp_delete_id').value = id;
        document.getElementById('pp_delete_type').value = type;
    }
    function ArmPPChangeStatus(id, status)
    {
        document.getElementById('pp_log_id').value = id;
        document.getElementById('pp_log_status').value = status;
    }
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">

    <div class="arm_datatable_filters_options">
        <div class='sltstandard'>
            <input type='hidden' id='arm_transaction_bulk_action1' name="action1" value="-1" />
            <dl class="arm_selectbox column_level_dd arm_width_160">
                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                <dd>
                    <ul data-id="arm_transaction_bulk_action1">
                        <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                        <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_transaction"><?php _e('Delete', 'ARMember'); ?></li>
                    </ul>
                </dd>
            </dl>
        </div>
        <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
    </div>
</div>
<div class="arm_transactions_list">
    <div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
        <div class="arm_datatable_filters_options">
            <div class='sltstandard'>
                <input type='hidden' id='arm_transaction_bulk_action1' name="action1" value="-1" />
                <dl class="arm_selectbox column_level_dd arm_width_160">
                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                    <dd>
                        <ul data-id="arm_transaction_bulk_action1">
                            <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                            <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_transaction"><?php _e('Delete', 'ARMember'); ?></li>
                        </ul>
                    </dd>
                </dl>
            </div>
            <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
        </div>
    </div>
    <form method="GET" id="paid_post_transactions_list_form" class="data_grid_list" onsubmit="return arm_paid_post_transactions_list_form_bulk_action();">
        <input type="hidden" name="page" value="<?php echo $arm_slugs->transactions; ?>" />
        <input type="hidden" name="armaction" value="list" />
        <div class="arm_datatable_filters">
            <div class="arm_dt_filter_block arm_datatable_searchbox">
                <label><input type="text" placeholder="<?php _e('Search', 'ARMember'); ?>" id="armmanagesearch_pp_new" value="<?php echo $filter_search; ?>" tabindex="-1" ></label>
                <?php if (!empty($payment_gateways)) : ?>
                    <!--./====================Begin Filter By Payment Gateway Box====================/.-->
                    <div class="arm_datatable_filter_item arm_filter_pp_gateway_label">
                        <input type="hidden" id="arm_filter_pp_gateway" class="arm_filter_pp_gateway" value="<?php echo $filter_gateway; ?>" />
                        <dl class="arm_selectbox arm_width_160">
                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_filter_pp_gateway">
                                    <li data-label="<?php _e('Gateway', 'ARMember'); ?>" data-value="0"><?php _e('Gateway', 'ARMember'); ?></li>
                                    <li data-label="<?php _e('Manual', 'ARMember'); ?>" data-value="<?php _e('manual', 'ARMember'); ?>"><?php _e('Manual', 'ARMember'); ?></li>
                                    <?php foreach ($payment_gateways as $key => $pg): ?>
                                        <li data-label="<?php echo $pg['gateway_name']; ?>" data-value="<?php echo $key; ?>"><?php echo $pg['gateway_name']; ?></li>                                                                                
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                    <!--./====================End Filter By Payment Gateway Box====================/.-->
                <?php endif; ?>
                <!--./====================Begin Filter By Payment Type Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_pptype_label">
                    <input type="hidden" id="arm_filter_pptype" class="arm_filter_pptype" value="<?php echo $filter_ptype; ?>" />
                    <dl class="arm_selectbox arm_width_160 arm_min_width_60">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_pptype">
                                <li data-label="<?php _e('Payment Type', 'ARMember'); ?>" data-value="0"><?php _e('Payment Type', 'ARMember'); ?></li>
                                <li data-label="<?php _e('One Time', 'ARMember'); ?>" data-value="one_time"><?php _e('One Time', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Recurring', 'ARMember'); ?>" data-value="subscription"><?php _e('Recurring', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Type Box====================/.-->
                <!--./====================Begin Filter By Payment Mode Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_ppmode_label">
                    <input type="hidden" id="arm_filter_ppmode" class="arm_filter_ppmode" value="<?php echo $filter_pmode; ?>" />
                    <dl class="arm_selectbox arm_width_160 arm_min_width_80">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_ppmode">
                                <li data-label="<?php _e('Subscription', 'ARMember'); ?>" data-value="0"><?php _e('Subscription', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Automatic Subscription', 'ARMember'); ?>" data-value="auto_debit_subscription"><?php _e('Automatic Subscription', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Semi Automatic Subscription', 'ARMember'); ?>" data-value="manual_subscription"><?php _e('Semi Automatic Subscription', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Mode Box====================/.-->
                <!--./====================Begin Filter By Payment Status Box====================/.-->
                <div class="arm_datatable_filter_item arm_filter_ppstatus_label">
                    <input type="hidden" id="arm_filter_ppstatus" class="arm_filter_ppstatus" value="<?php echo $filter_pstatus; ?>" />
                    <dl class="arm_selectbox arm_width_160 arm_min_width_60">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_ppstatus">
                                <li data-label="<?php _e('Status', 'ARMember'); ?>" data-value="0"><?php _e('Status', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Success', 'ARMember'); ?>" data-value="success"><?php _e('Success', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Pending', 'ARMember'); ?>" data-value="pending"><?php _e('Pending', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Cancelled', 'ARMember'); ?>" data-value="canceled"><?php _e('Cancelled', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Failed', 'ARMember'); ?>" data-value="failed"><?php _e('Failed', 'ARMember'); ?></li>
                                <li data-label="<?php _e('Expired', 'ARMember'); ?>" data-value="expired"><?php _e('Expired', 'ARMember'); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <!--./====================End Filter By Payment Status Box====================/.-->
            </div>
            <div>
                <!--./====================Begin Filter By Date====================/.-->
                <div class="arm_datatable_filter_item arm_filter_ppstatus_label arm_margin_left_0" >
                    <input type="text" id="arm_filter_ppstart_date"  placeholder="<?php _e('Start Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" />
                </div>
                <div class="arm_datatable_filter_item arm_filter_ppstatus_label">
                    <input type="text" id="arm_filter_ppend_date" placeholder="<?php _e('End Date', 'ARMember'); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" />
                </div>
                <!--./====================End Begin Filter By Date====================/.-->
                <div class="arm_dt_filter_block arm_dt_filter_submit arm_payment_history_filter_submit">
                    <input type="button" class="armemailaddbtn" id="arm_paid_post_payment_grid_filter_btn" value="<?php _e('Filter', 'ARMember'); ?>" onClick="arm_load_paid_post_trasaction_list_filtered_grid()"/>
                    <input type="button" class="armemailaddbtn arm_cancel_btn" id="arm_paid_post_payment_grid_export_btn" class="" value="<?php _e('Export To CSV', 'ARMember'); ?>"/>
                </div>
            </div>
            <div class="armclear"></div>
        </div>
        <div id="armmainformnewlist" class="arm_filter_grid_list_container">
            <div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
            <div class="response_messages"></div>
            <table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable_1">
                <thead>
                    <tr>
                        <th class="center cb-select-all-th arm_max_width_60" ><input id="pp-cb-select-all-1" type="checkbox" class="chkstanard"></th>
                        <th><?php _e('Transaction ID', 'ARMember'); ?></th>
                        <th><?php _e('Invoice ID', 'ARMember'); ?></th>
                        <th><?php _e('First Name', 'ARMember'); ?></th>
                        <th><?php _e('Last Name','ARMember'); ?></th>
                        <th><?php _e('User', 'ARMember'); ?></th>
                        <th><?php _e('User Email', 'ARMember'); ?></th>
                        <th class="arm_min_width_150"><?php _e('Post Title', 'ARMember'); ?></th>
                        <th><?php _e('Payment Gateway', 'ARMember'); ?></th>
                        <th><?php _e('Payment Type', 'ARMember'); ?></th>
                        <th><?php _e('Payer Email', 'ARMember'); ?></th>
                        <th class="center"><?php _e('Transaction Status', 'ARMember'); ?></th>
                        <th class="center arm_min_width_150" ><?php _e('Payment Date', 'ARMember'); ?></th>
                        <th class="center"><?php _e('Amount', 'ARMember'); ?></th>
                        <th class="center arm_min_width_150" ><?php _e('Credit Card Number', 'ARMember'); ?></th>
                        <th data-key="armGridActionTD" class="armGridActionTD noVis" style="display: none;"></th>
                    </tr>
                </thead>
            </table>
            <div class="armclear"></div>
            <input type="hidden" name="show_hide_columns" id="paid_post_show_hide_columns" value="<?php _e('Show / Hide columns', 'ARMember'); ?>"/>
            <input type="hidden" name="search_grid" id="paid_post_search_grid" value="<?php _e('Search', 'ARMember'); ?>"/>
            <input type="hidden" name="entries_grid" id="paid_post_entries_grid" value="<?php _e('transactions', 'ARMember'); ?>"/>
            <input type="hidden" name="show_grid" id="paid_post_show_grid" value="<?php _e('Show', 'ARMember'); ?>"/>
            <input type="hidden" name="showing_grid" id="paid_post_showing_grid" value="<?php _e('Showing', 'ARMember'); ?>"/>
            <input type="hidden" name="to_grid" id="paid_post_to_grid" value="<?php _e('to', 'ARMember'); ?>"/>
            <input type="hidden" name="of_grid" id="paid_post_of_grid" value="<?php _e('of', 'ARMember'); ?>"/>
            <input type="hidden" name="no_match_record_grid" id="paid_post_no_match_record_grid" value="<?php _e('No matching transactions found', 'ARMember'); ?>"/>
            <input type="hidden" name="no_record_grid" id="paid_post_no_record_grid" value="<?php _e('No any transaction found yet.', 'ARMember'); ?>"/>
            <input type="hidden" name="filter_grid" id="paid_post_filter_grid" value="<?php _e('filtered from', 'ARMember'); ?>"/>
            <input type="hidden" name="totalwd_grid" id="paid_post_totalwd_grid" value="<?php _e('total', 'ARMember'); ?>"/>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
        </div>
        <div class="footer_grid"></div>
    </form>
</div>

<div class="arm_member_view_detail_container"></div>